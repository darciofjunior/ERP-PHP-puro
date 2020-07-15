<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$fator_desc_maximo_venda    = genericas::variavel(19); //Aqui é a Busca da Variável de Vendas

$caracteres_invalidos       = 'àáéíóúãõâêîôûçÀÁÉÍÓÚÃÕÂÊÎÔÛÇª°º"§';
$caracteres_validos         = 'aaeiouaoaeioucAAEIOUAOAEIOUC     ';

//Função que serve para Retornar o Lote Mínimo de Compra ...
function retornar_lote_minimo_compra($id_produto_acabado, $sem_texto='') {
//Através do P.A. eu busco quem é o P.I. ...
    $sql = "SELECT `id_produto_insumo` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `id_produto_insumo` > '0' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_produto_insumo = $campos[0]['id_produto_insumo'];
//Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado
    $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1); //busco somente o id_fornecedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
    if($id_fornecedor_setado == 0) {//Se não existir Fornecedor Default para esse P.I. então ñ retorno nada ...
        $retorno = '<font title="Sem Fornecedor Default" color="red">S/ Forn</font>';
    }else {//Como encontrei um Fornecedor Default p/ o P.I., então eu busco o `lote_minimo_pa_rev` deles
        $sql = "SELECT `lote_minimo_pa_rev` 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_fornecedor`= '$id_fornecedor_setado' 
                AND `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos = bancos::sql($sql);
/*Fiz essa adaptação porque mais abaixo eu só preciso desse valor p/ programação - Dárcio*/
        if($sem_texto == 1) {
            $retorno = $campos[0]['lote_minimo_pa_rev'];
        }else {
            $retorno = '<font title="Lote Mínimo p/ Compra" color="red">L.Mín. '.$campos[0]['lote_minimo_pa_rev'].'</font>';
        }
    }
    return $retorno;
}

//Verifico o Tipo de País do Cliente ...
$sql = "SELECT c.`credito`, c.`id_cliente`, c.`id_pais`, c.`id_uf`, c.`id_cliente_tipo`, c.`trading`, 
        c.`tributar_ipi_rev`, c.`tipo_suframa`, c.`cod_suframa`, ov.`id_funcionario`, ov.`id_login`, ov.`finalidade`, 
        ov.`tipo_frete`, ov.`artigo_isencao`, ov.`nota_sgd`, ov.`valor_dolar`, 
        ov.`incluir_novos_pas`, ov.`congelar`, ov.`data_sys` 
        FROM `orcamentos_vendas` ov 
        INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
        WHERE ov.`id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$id_cliente             = $campos[0]['id_cliente'];
$id_uf_cliente          = $campos[0]['id_uf'];
$id_cliente_tipo        = $campos[0]['id_cliente_tipo'];
$tipo_moeda             = ($campos[0]['id_pais'] != 31) ? 'U$ ' : 'R$ ';//Se o Cliente for Intern. a Moeda é Dólar ou Real

//Busca dos Itens do Orçamento ...
$sql = "SELECT ovi.*, DATE_FORMAT(ged.`data_limite`, '%d/%m/%Y') AS data_limite, 
        ged.`id_empresa_divisao`, ged.`margem_lucro_minima`, ged.`desc_base_a_nac`, ged.`desc_base_b_nac`, 
        ged.`acrescimo_base_nac`, ged.`realcar`, pa.`operacao`, pa.`operacao_custo`, 
        pa.`operacao_custo_sub`, pa.`referencia`, pa.`discriminacao`, 
        pa.`preco_unitario`, pa.`peso_unitario`, pa.`mmv`, 
        pa.`observacao` AS observacao_produto, pa.`status_top`, pa.`qtde_queima_estoque`, 
        CONCAT(' ', u.sigla, ' ') AS sigla 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        WHERE `id_orcamento_venda` = '$id_orcamento_venda' ORDER BY `id_orcamento_venda_item` ";
$campos_itens = bancos::sql($sql, 0, 150, 'sim', $pagina);
$linhas_itens = count($campos_itens);
?>
<html>
<table width='100%' border='0' cellspacing='1' cellpadding='1' class='table_pontilhada' onmouseover='total_linhas(this)'>
<?
        if(!empty($campos[0]['incluir_novos_pas'])) {//Se existir(em) PA(s) em que o Depto. Técnico precisa cadastrar, mostra a msn abaixo ...
?>
    <tr class='linhanormal'>
        <td colspan='23' bgcolor='#FFFFDF'><b>
            <font color='red' size='2'>
                RELA&Ccedil;&Atilde;O DE NOVO(S) PA(S) ESP QUE PRECISA(M) SER INCLU&Iacute;DO(S) PELO DEPTO. T&Eacute;CNICO: <br>
            </font>
            <font size='2'>
                <?=$campos[0]['incluir_novos_pas'];?>
            </font>
            </b>
        </td>
    </tr>
<?
        }
    
	if($linhas_itens == 0) {//Se não existir nenhum Item de Orçamento ...
?>
    <tr align='center'>
        <td colspan='23'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='red'>
                <b>N&atilde;o existem Itens or&ccedil;ados.</b>
            </font>
        </td>
    </tr>
<?
	}else {//Se existir pelo menos 1 Item ...
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='23'>
            <font face='Verdana' color='#FFFFFF' size='-1'><b>
                &Uacute;ltima altera&ccedil;&atilde;o realizada por Funcion&aacute;rio: 
                <font face='Verdana' color='yellow'>
                <?
                    if($campos[0]['id_funcionario'] > 0) {//99% dos casos, serão os funcionários da Albafer que irão acessar nosso sistema ...
                        $sql = "SELECT l.`login` 
                                FROM `funcionarios` f 
                                INNER JOIN `logins` l ON l.id_funcionario = f.`id_funcionario` 
                                WHERE f.`id_funcionario` = '".$campos[0]['id_funcionario']."' LIMIT 1 ";
                    }else {//No demais representantes ...
                        $sql = "SELECT `login` 
                                FROM `logins` 
                                WHERE `id_login` = '".$campos[0]['id_login']."' LIMIT 1 ";
                    }
                    $campos_login = bancos::sql($sql);
                    echo $campos_login[0]['login'];
                ?>
                </font>
            </b></font>
            &nbsp;-&nbsp;
            <font face='Verdana' color='#FFFFFF' size='-1'><b>
                Data e Hora de Atualiza&ccedil;&atilde;o:
                <font face='Verdana' color='yellow'>
                    <?=data::datetodata(substr($campos[0]['data_sys'], 0, 10), '/').' - '.substr($campos[0]['data_sys'], 11, 8);?>
                </font>
            </b></font>
        </td>
    </tr>
    <?
            if($campos[0]['congelar'] == 'N') {
    ?>
    <tr align='center'>
        <td colspan='23' bgcolor='black'>
            <font color='yellow' face='Courier New, Courier, mono'>
                <marquee>
                    <b>Para garantir estes pre&ccedil;os, n&atilde;o se esque&ccedil;a de congelar este or&ccedil;amento.</b>
                </marquee>
            </font>
        </td>
    </tr>
<?
            }else {
                echo '<tr><td></td></tr>';
            }
?>
    <tr class='linhanormal' valign='center'>
        <td colspan='23' bgcolor='#CECECE'>
            <font color='darkblue'>
                <b>CR&Eacute;DITO DO CLIENTE: 
            </font>
            <?
                $calculo_total_impostos = calculos::calculo_impostos(0, $id_orcamento_venda, 'OV');
                $font                   = ($campos[0]['credito'] == 'A' || $campos[0]['credito'] == 'B') ? '<font color="green">' : '<font color="red">';

                /*Somente os Usuários Roberto "62" ou Dárcio "98 porque programa" que podem 
                estar trocando o Crédito do Cliente por aqui p/ facilitar programação ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
            ?>
            <a href = "javascript:nova_janela('../../../financeiro/cadastro/credito_cliente/detalhes.php?id_cliente=<?=$id_cliente;?>&pop_up=1', 'POP', '', '', '', '', 450, 780, 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Cr&eacute;dito do Cliente'>
            <?
                }
                echo $font.$campos[0]['credito'].'<br>';
                /*Somente os Usuários Roberto "62" ou Dárcio "98 porque programa" que podem 
                estar trocando o Crédito do Cliente por aqui p/ facilitar programação ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) echo '</a>';

                for ($i = 0; $i < $linhas_itens; $i++) {
                    $peso_total_orcamento+= $campos_itens[$i]['peso_unitario'] * $campos_itens[$i]['qtde'];
                    $campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'].'<br>';
                }
                $icms_st_ipi_perc                       = 20;//Aqui estamos estimando que esses impostos em uma NF dariam aí no máximo 20% ...
                $valor_produto_todos_itens_com_impostos = $calculo_total_impostos['valor_total_produtos'] * (1 + $icms_st_ipi_perc / 100);

                if($campos[0]['credito'] == 'B') {
                    $retorno_analise_credito = faturamentos::analise_credito_cliente($campos[0]['id_cliente']);
                    echo str_replace('\n', '<br>', $retorno_analise_credito['historico_cliente']);
/*Se o Crédito Comprometido + os Itens do Orçamento for maior do a Tolerância do Cliente, então eu exibo 
essa imagem p/ o Vendedor poder mandar um e-mail p/ o Financeiro solicitando um aumento de crédito ...*/
                    if(($retorno_analise_credito['credito_comprometido'] + $calculo_total_impostos['valor_total_produtos']) > $retorno_analise_credito['tolerancia_cliente']) {
            ?>
            &nbsp;<img src="/erp/albafer/imagem/novo_email.jpeg" title='Solicitar Libera&ccedil;ão de Crédito do Cliente p/ Financeiro' alt='Solicitar Libera&ccedil;ão de Crédito do Cliente p/ Financeiro' width='30' height='20' onclick="html5Lightbox.showLightbox(7, '/erp/albafer/modulo/financeiro/cadastro/credito_cliente/enviar_email_solic_credito.php?id_cliente=<?=$id_cliente;?>&valor_total_itens_faturar=<?=$valor_produto_todos_itens_com_impostos;?>&peso_total_faturar=<?=$peso_total_orcamento;?>')" style='cursor:help'>
            <?
                    }
                }else if($campos[0]['credito'] == 'C') {
                    echo ' - CRÉDITO SUSPENSO';
                }else if($campos[0]['credito'] == 'D') {
                    echo ' - À ANALISAR';
                }
            ?>
            </b>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
        <?
//Nessa coluna, o rótulo será dinâmico de acordo com o Cliente ...
            if($campos[0]['id_pais'] == 31) {//Cliente Nacional ...
                $title      = 'Observa&ccedil;&atilde;o do Produto';
                $descricao  = 'Obs. Prod.';
            }else {//Cliente Internacional ...
                $title      = 'Desconto s/ Lista';
                $descricao  = 'Desc. s/ Lista';
            }
        ?>
            <font title="<?=$title;?>" style='cursor:help'>
                <b><?=$descricao;?></b> - 
            </font>	
            <b>Op&ccedil;&otilde;es</b>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Quantidade'>
                <b>Qtde<br/>
                <?
                    //Só mostra para o Roberto ou Dárcio ...
                    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
                ?>
                <font color='darkblue'>M.M.V.</font>
                <?
                    }
                ?>
                </b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Estoque Dispon&iacute;vel' style='cursor:help'>
                <b>E.D.</b>
            </font>
            <!--<font title='Estoque do Fornecedor / Estoque do Porto' style='cursor:help'>
                <br/><b>E Forn/E Porto</b>
            </font>-->
            <font title='Estoque Disponível Atrelado / MMV Atrelado' style='cursor:help'>
                <br/><b>ED Atrel/MMV Atrel</b>
            </font>
            <font title='Pe&ccedil;as por Embalagem' style='cursor:help'>
                <br/><b>Pc/Emb</b>
            </font>
            <font color='darkblue' title='Prazo de Entrega do Or&ccedil;amento' style='cursor:help'>
                <br/><b>P.Ent.Orc</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <b>Produto</b>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Informa&ccedil;&otilde;es' style='cursor:help'>
                <b>Info</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Pre&ccedil;o L&iacute;quido Faturado' style='cursor:help'>
                <b>Pre&ccedil;o L.F.<br><?=$tipo_moeda;?>/P&ccedil;</b>
            </font>
        </td>
        <td bgcolor='#CECECE' width='8%'>
            <font title='Desconto SGD/ICMS' style='cursor:help'>
                <b>Descontos % </b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Acrescimo Extra' style='cursor:help'>
                <b>Acr&eacute;sc.<br>Extra %</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Comiss&atilde;o <?=$tipo_moeda;?>' style='cursor:help'>
            <?
                //Somente p/ os funcionários -> Roberto "62", Dárcio "98" e Netto "147" ...
                if(($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) && strtoupper($campos[0]['congelar']) == 'S') {
            ?>
                    <a href="javascript:html5Lightbox.showLightbox(7, '../../orcamentos/itens/alterar_comissao.php?id_orcamento_venda=<?=$id_orcamento_venda;?>')" class='link'>
            <?
                }
            ?>
                <b>Comiss&atilde;o</b>
            </font>
        </td>
        <td bgcolor='#CECECE' width='2%'>
            <font title='Pre&ccedil;o Liquido Final em <?=$tipo_moeda;?>' style='cursor:help'>
                <b>Pre&ccedil;o L.<br>Final <?=$tipo_moeda;?></b>
                <?
                    $vetor_logins_com_acesso_margens_lucro = vendas::logins_com_acesso_margens_lucro();
                    
                    if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                ?>
                <img src = '../../../../imagem/margem_lucro.png' title='Margem de Lucro Mínima em Lote' onclick="nova_janela('/erp/albafer/modulo/vendas/orcamentos/itens/alterar_margem_lucro.php?id_orcamento_venda=<?=$id_orcamento_venda;?>', 'MARGEM_LUCRO', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" style='cursor:help'>
                <?
                    }
                ?>
            </font>
        </td>
        <td bgcolor='#CECECE' width='2%'>
            <b>Total Lote<br><?=$tipo_moeda;?> s/ IPI</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>TOTAL <br>IPI</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>TOTAL <br>ICMS <br>ST</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>ICMS</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>P. Min. Crise</b>
        </td>
    </tr>
<?
		$tx_financeira                          = custos::calculo_taxa_financeira($id_orcamento_venda);
                $vetor_prazos_entrega                   = vendas::prazos_entrega();
                
                //Isso aqui é uma adaptação, já que não existe id_empresa em Orçamento ...
                $id_empresa = ($campos[0]['nota_sgd'] == 'N') ? 1 : 4;
                
		for ($i = 0; $i < $linhas_itens; $i++) {
                    /*Esse controle é de extrema importância porque em casos de "Gato por Lebre", preciso pegar 
                    os impostos do Gato ...
                    
                    Ex: o cliente comprou MRH-042 "Gato", mas estamos enviando o MRT-042 "Lebre ou substituto" ...*/
                    $id_produto_acabado_utilizar = (!empty($campos_itens[$i]['id_produto_acabado_discriminacao'])) ? $campos_itens[$i]['id_produto_acabado_discriminacao'] : $campos_itens[$i]['id_produto_acabado'];
                    
                    //Essas variáveis serão utilizadas mais abaixo ...
                    $dados_produto      = intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf_cliente, $id_cliente, $id_empresa, $campos[0]['finalidade']);

                    $cor_fundo_linha    = (strpos($campos_itens[$i]['discriminacao'], 'XXX') !== false) ? 'orange' : '';
                    $cor_fundo_linha    = ($campos_itens[$i]['realcar'] == 'S') ? '#EEB4B4' : '';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
            <?
                    //Nessa coluna, o rótulo será dinâmico de acordo com o Cliente ...
                    if($campos[0]['id_pais'] == 31) {//Cliente Nacional ...
                            if(!empty($campos_itens[$i]['observacao_produto'])) {
                                    echo "<img width='22'  height='18' title='".$campos_itens[$i]['observacao_produto']."' src='../../../../imagem/olho.jpg'>";
                            }
                            //Aqui verifica se tem pelo menos 1 Follow Up Registrado
                            $sql = "SELECT observacao 
                                    FROM `produtos_acabados_follow_ups` 
                                    WHERE `id_produto_acabado` = ".$campos_itens[$i]['id_produto_acabado']." ORDER BY data_sys DESC LIMIT 1 ";
                            $campos_follow_up = bancos::sql($sql);
                            if(count($campos_follow_up) > 0) {
                                    $title = '&Uacute;ltima Ocorr&ecirc;ncia: '.$campos_follow_up[0]['observacao'];
                                    $color = 'red';
                            }else {
                                    $title = 'N&atilde;o h&aacute; nenhum Follow-Up Registrado p/ este Produto Acabado';
                                    $color = '#6473D4';
                            }
            ?>
                    <a href="javascript:html5Lightbox.showLightbox(7, '../../../producao/cadastros/produto_acabado/follow_up.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>')" class='link'>
                        <font face='Verdana, Arial, Helvetica, sans-serif' color='<?=$color;?>'>
                            OBS
                        </font>
                    </a>
            <?
                    }else {//Cliente Internacional ...
                        $desconto_sem_lista = (1 - $campos_itens[$i]['preco_liq_final']) * 100;
                        echo number_format($desconto_sem_lista, 2, ',', '.').' %';
                    }
                    if($campos_itens[$i]['status'] == 0) {//Pendência Total
                        if(strtoupper($campos[0]['congelar']) == 'N') {//Se o Orçamento estiver descongelado então ...
?>
                        <br/><img src = '../../../../imagem/menu/alterar.png' border='0' onclick="alterar_item('<?=$i + 1;?>')" alt='Alterar Item' title='Alterar Item'>
<?
                        }else {
                            echo '<br><b>&nbsp;-&nbsp;</b>';
                        }
                    }else {
?>
                        <a href="javascript:html5Lightbox.showLightbox(7, '../../../classes/pedido_vendas/pedido_vendas.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>')" class='link'>
<?
                        if($campos_itens[$i]['status'] == 1) {//Pendência Parcial
                            echo '<br><font title="PARCIAL - Visualizar Pedidos" style="cursor:help" color="blue">P</font>';
                        }else {//Item Concluído
                            echo '<br><font title="TOTAL - Visualizar Pedidos" style="cursor:help" color="red">T</font>';
                        }
                    }
?>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
                <font face='Verdana, Arial, Helvetica, sans-serif'>
                <?
                    echo number_format($campos_itens[$i]['qtde'], 1, ',', '.');
                    echo '<br><font title="Peso Total sobre o Peso" style="cursor:help">'.number_format($campos_itens[$i]['peso_unitario'] * $campos_itens[$i]['qtde'], 2, ',', '.').' kgs';
                    /******************************Tratamento de Caminho pelo Lote Mínimo******************************/
                    //A idéia é oferecer ao Cliente uma Proposta p/ Lote Mínimo Ideal ...
                    if($campos_itens[$i]['referencia'] == 'ESP' && $campos_itens[$i]['operacao_custo'] == 0) {
                        $lote_minimo_corrigido	= $campos_itens[$i]['lote_minimo_corrigido'];

                        $taxa_financeira_vendas = genericas::variavel(16) / 100 + 1;
                        $total_indust           = custos::todas_etapas($campos_itens[$i]['id_produto_acabado'], $campos_itens[$i]['operacao_custo']);
                        $preco_custo_max        = $total_indust * $taxa_financeira_vendas / $fator_desc_maximo_venda;

                        //Busca do Lote Minimo em R$ do Grupo ...
                        $sql = "SELECT gpa.lote_min_producao_reais 
                                FROM `produtos_acabados` pa 
                                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                                WHERE pa.id_produto_acabado = '".$campos_itens[$i]['id_produto_acabado']."' LIMIT 1 ";
                        $campos_lote_min 			= bancos::sql($sql);
                        $lote_min_producao_reais 	= $campos_lote_min[0]['lote_min_producao_reais'];

                        //Significa que está indo pelo LM e o usuário realmente quer q siga esse caminho caso o Sys caia aqui ...
                        if(($campos_itens[$i]['qtde'] * $preco_custo_max) < $lote_min_producao_reais && $campos_itens[$i]['ignorar_lote_minimo_do_grupo_faixa_orcavel'] == 'N') {
                            if($preco_custo_max > 0) {//Se = 0, significa que nao foi feito Custo p/ este PA ...
                                echo '<br><font color="red"><b>Lt.Min.Ideal='.(intval($lote_minimo_corrigido) + 1).$campos_itens[$i]['sigla'].'</b></font>';
                            }
                        }else {
                            if($preco_custo_max > 0) {//Se = 0, significa que nao foi feito Custo p/ este PA ...
                                if($campos_itens[$i]['ignorar_lote_minimo_do_grupo_faixa_orcavel'] == 'N') {
                                    echo '<br><font color="red"><b>Lote=OK</b></font>';
                                }else {
                                    echo '<br><font color="orange" title="Ignorar Lote M&iacute;nimo do Grupo Faixa Orçável" style="cursor:help"><b>(Ign. L.M)</b></font>';
                                }
                            }
                        }
                    }
                    /**************************************************************************************************/
                    //Só mostra para o Roberto ou Dárcio ...
                    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) echo '<br><font color="darkblue"><b>MMV='.number_format($campos_itens[$i]['mmv'], 0, ',', '.');
                ?>
                </font>
            </td>
            <td class='td_pontilhada' bgcolor='<?=$cor_fundo_linha;?>'>
                <?
                    /****Comentário da Queima****/

                    /*A partir do dia 01/08/2014 o Roberto pediu p/ comentar a função 
                    de queima, porque a ML Estimada e a Taxa de Estocagem substitui
                    essa função ...
                    if($campos_itens[$i]['qtde_queima_estoque'] > 0) {
                ?>
                    <img src='../../../../imagem/bloco_vermelho.gif' title='Qtde de Excesso de Estoque => <?=number_format($campos_itens[$i]['qtde_queima_estoque'], 2, ',', '.');?>' style='cursor:help' width='8' height='8' border='0' style='cursor:help'>
                <?
                    }*/
                    /**************************************************************************************/
                    /*Aqui eu verifico a qtde disponível desse item em Estoque e a qtde dele em Produção*/
                    $estoque_produto                = estoque_acabado::qtde_estoque($campos_itens[$i]['id_produto_acabado']);
                    $qtde_disponivel                = $estoque_produto[3];
                    $racionado                      = $estoque_produto[5];
                    $qtde_pa_possui_item_faltante   = $estoque_produto[9];
                    /*$est_fornecedor                 = $estoque_produto[12];
                    $est_porto                      = $estoque_produto[13];*/
                ?>
                <a href="javascript:html5Lightbox.showLightbox(7, '../../../classes/estoque/visualizar_estoque.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>')" class='link'>
                <?
                    if($racionado == 1) {
                        echo "<font color='red' size='-3'><b>Racionado</b></font>";
                    }else {
                        echo number_format($qtde_disponivel, 0, ',', '.');
                    }
                    if($qtde_pa_possui_item_faltante > 0) echo '<br/><font color="red" title="Produto Incompleto (Faltando Item)" style="cursor:help"><b>'.$qtde_pa_possui_item_faltante.' F.I</b></font>';
                ?>
                </a>
                <?
                    //echo '<br/>'.number_format($est_fornecedor, 0, ',', '.').'/'.number_format($est_porto, 0, ',', '.');
                
                    $retorno_pas_atrelados = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_itens[$i]['id_produto_acabado']);
                    echo '<br/>'.number_format($retorno_pas_atrelados['total_ed_pas_atrelados'], 0, ',', '.').'/'.number_format($retorno_pas_atrelados['total_mmv_pas_atrelados'], 0, ',', '.');

                    //Traz a quantidade de peças por embalagem da embalagem principal daquele produto ...
                    $sql = "SELECT pecas_por_emb 
                            FROM `pas_vs_pis_embs` 
                            WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' 
                            AND `embalagem_default` = '1' LIMIT 1 ";
                    $campos_pecas_emb = bancos::sql($sql);
                    if(count($campos_pecas_emb) == 1) {
                        echo '<br/>'.number_format($campos_pecas_emb[0]['pecas_por_emb'], 3, ',', '.');
                    }else {
                        echo '<br/>0';
                    }
                    //Verifico qual é o Prazo do Item do Orçamento p/ Printar na Tela de Itens ...
                    foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
                        if($campos_itens[$i]['prazo_entrega'] == $indice) {//Se igual
                            echo '<br/><font color="darkblue"><b>'.ucfirst(strtolower($prazo_entrega)).'</b></font>';
                            break;//Para sair fora do Loop ...
                        }
                    }
                ?>
            </td>
            <td class='td_pontilhada' bgcolor='<?=$cor_fundo_linha;?>' align='left'>
            <?
                //Aqui eu busco o código do PA do Cliente na Base ...
                $sql = "SELECT `cod_cliente` 
                        FROM `pas_cod_clientes` 
                        WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' 
                        AND `id_cliente` = '$id_cliente' LIMIT 1 ";
                $campos_cod_cliente = bancos::sql($sql);
                $cProdCliente       = (count($campos_cod_cliente) == 1) ? '-cProdCliente:"'.$campos_cod_cliente[0]['cod_cliente'].'"' : '';

                echo strtr(intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, 1, 1, $campos_itens[$i]['id_produto_acabado_discriminacao']), $caracteres_invalidos, $caracteres_validos).'"'.$cProdCliente;'&nbsp;';
                if($campos_itens[$i]['status_top'] == 1) {
                    echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'> (TopA)</font>";
                }else if($campos_itens[$i]['status_top'] == 2) {
                    echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'> (TopB)</font>";
                }
/*********************************************************************************************************/
/************************************************Marçações************************************************/
/*********************************************************************************************************/
                //Aqui eu verifico se existe alguma Promoção ...
                if($campos_itens[$i]['promocao'] == 'A') {
                    $title = 'Promoç&atilde;o A';
                    echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(PA)</b></font>";
                }else if($campos_itens[$i]['promocao'] == 'B') {
                    $title = 'Promoç&atilde;o B';
                    echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(PB)</b></font>";
                }else if($campos_itens[$i]['promocao'] == 'C') {
                    $title = 'Promoç&atilde;o C';
                    echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(PC)</b></font>";
                }else if($campos_itens[$i]['promocao'] == 1) {
                    $title = 'Promoç&atilde;o Antiga';
                    echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(P)</b></font>";
                }
/*********************************************************************************************************/
            ?>
            &nbsp;
            <a href="javascript:html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/orcamentos/itens/ultima_venda_cliente.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>')" class='link'>
                <img src = '../../../../imagem/detalhes_ultima_venda.png' title='Detalhes da Última Venda' alt='Detalhes da Última Venda' width='30' height='22' border='0'>
            </a>
            &nbsp;
            <!--Esse parâmetro de pop_up = 1, significa que essa Tela estará sendo aberta como Pop-UP-->
            <a href="javascript:html5Lightbox.showLightbox(7, '../../relatorio/concorrentes/relatorio.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>&id_uf_cliente=<?=$id_uf_cliente?>&nota_sgd=<?=$campos[0]['nota_sgd'];?>&pop_up=1')" class='link'>
                <img src = '../../../../imagem/concorrencia.jpeg' title='Relatório de Concorrentes' alt='Relatório de Concorrentes' width='16' height='16' border='0'>
            </a>
            <?
                if($campos_itens[$i]['queima_estoque'] == 'S') echo '&nbsp;<img src="../../../../imagem/queima_estoque.png" title="Excesso de Estoque" alt="Excesso de Estoque" border="0">';
            ?>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
            <?
                if($campos_itens[$i]['operacao_custo'] == 0) {
                    echo "<font title='Opera&ccedil;&atilde;o de Custo' style='cursor:help'>OC</font>=<font title='Industrialização' style='cursor:help'>I</font>";
                    if($campos_itens[$i]['operacao_custo_sub'] == 0) {
                        echo "<font title='Sub-Opera&ccedil;&atilde;o Industrial' style='cursor:help'>I</font>";
                    }else {
                        echo "<font title='Sub-Opera&ccedil;&atilde;o Revenda' style='cursor:help'>R</font>";
                    }
                }else {
                    echo "<font title='Opera&ccedil;&atilde;o de Custo' style='cursor:help'>OC</font>=<font title='Revenda' style='cursor:help'>R</font>";
                }

                if($campos[0]['tributar_ipi_rev'] == 'S') {//Marcação Realizada no Cadastro do Cliente "TRIBUTAR o PA OC=Revenda COMO INDUSTRIAL" ...
                    //A cor azul representa que é uma Operação Falsa ...
                    $color          = 'darkblue';
                    $operacao       = ($dados_produto['operacao'] == 0) ? 'I' : 'R';
                    $title_operacao = ($dados_produto['operacao'] == 0) ? 'Revenda' : 'Industrial';//A apresentação que eu faço aqui, realmente é a Oposta ...
                }else {
                    //A cor vermelha representa que não é trambicagem ...
                    $color          = 'red';
                    $operacao       = ($dados_produto['operacao'] == 0) ? 'I' : 'R';
                    $title_operacao = ($dados_produto['operacao'] == 0) ? 'Industrial' : 'Revenda';
                }
                echo '<br/>OF=<font color="'.$color.'" title="Opera&ccedil;&atilde;o Real de Faturamento => '.$title_operacao.'" style="cursor:help"><b>'.$operacao.'</b></font>';
            ?>
                <font title='Classifica&ccedil;&atilde;o Fiscal => <?=$dados_produto['classific_fiscal'];?>' style='cursor:help'>
                    <br/>CF=<?=$dados_produto['id_classific_fiscal'];?>
                </font>
                <font title='Origem / Situa&ccedil;&atilde;o Tribut&aacute;ria' style='cursor:help'>
                    <br/>CST=<?=$dados_produto['cst'];?>
                </font>
                <font>
                    <br/>CFOP=<?=$dados_produto['cfop'];?>
                </font>
            </td>
            <td class='td_pontilhada' bgcolor='<?=$cor_fundo_linha;?>'>
                <font face='Verdana, Arial, Helvetica, sans-serif'>
                <?
                    if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {

                        if($campos_itens[$i]['operacao_custo'] == 0) {//Industrial
                ?>
                            <a href="javascript:html5Lightbox.showLightbox(7, '../../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>&tela=2&pop_up=1')" title='Visualizar Custo Industrial' class='link'>
                <?
                        }else {
                ?>
                            <a href="javascript:html5Lightbox.showLightbox(7, '../../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>')" title='Visualizar Custo Industrial' class='link'>
                <?
                        }
                    }

                    if(empty($campos_itens[$i]['preco_liq_fat_disc'])) {
                        echo number_format($campos_itens[$i]['preco_liq_fat'], 2, ',', '.');
                    }else {
                        if($campos_itens[$i]['preco_liq_fat_disc'] == 'Orçar') {
                            echo "<font color='red'>".$campos_itens[$i]['preco_liq_fat_disc']."</font>";
                        }else {
                            echo "<font color='blue'>".$campos_itens[$i]['preco_liq_fat_disc']."</font>";
                        }
                    }

                    //Nunca poderemos ter um Item 'ESP' que esteja sem Pzo. Técnico ...
                    if($campos_itens[$i]['referencia'] == 'ESP' && $campos_itens[$i]['prazo_entrega_tecnico'] == '0.0') {
                        echo '<p/><font color="red" title="Sem Prazo de Entrega do Depto. T&eacute;cnico" style="cursor:help"><b>Pz.Ent.DT</b></font>';
                    }
                ?>
                </font>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
                    Cliente=
            <?
                    echo number_format($campos_itens[$i]['desc_cliente'], 2, ',', '.');
                    if($campos_itens[$i]['desc_cliente'] < 0) echo '<font color="red"><b> (Acr)</b></font>';

                    echo '<br>';
            ?>
                    Extra=
            <?
                    $coeficiente = (1 - $campos_itens[$i]['desc_cliente'] / 100) * (1 - $campos_itens[$i]['desc_extra'] / 100) * (1 + $campos_itens[$i]['acrescimo_extra'] / 100) * (1 - $tx_financeira / 100);
                    $desconto_total 	= (1 - $coeficiente) * 100;
            ?>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' title="Desc.Total p/comissao = <?=number_format($desconto_total, 2, ',', '.');?>% - Levamos em conta a dif. do ICMS p/UF = SP x desc.ICMS/SGD e a dif.da tx.fin.p/30 ddl x tx.fin.pz.medio do ORC" style='cursor:help'>
                            <?=number_format($campos_itens[$i]['desc_extra'], 2, ',', '.').'<br>';?>
                    </font>
                    <?if($nota_sgd == 'S') {echo 'SGD=';}else {echo 'ICMS=';}?>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                            <?=number_format($campos_itens[$i]['desc_sgd_icms'], 2, ',', '.');?>
                    </font>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
                    <font face='Verdana, Arial, Helvetica, sans-serif'>
                            <?=number_format($campos_itens[$i]['acrescimo_extra'], 2, ',', '.');?>
                    </font>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
            <?
                    //O id_pais será muito útil mais abaixo ...
                    $sql = "SELECT `id_pais`, `nome_fantasia` 
                            FROM `representantes` 
                            WHERE `id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
                    $campos_representante = bancos::sql($sql);
                    //Só exibe para esse respectivos Funcionários: Roberto 62 e Dárcio 98 porque programa ...
                    if(($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) && strtoupper($campos[0]['congelar']) == 'S') {
            ?>
                    <a href="javascript:html5Lightbox.showLightbox(7, '../../orcamentos/itens/alterar_representante.php?id_cliente=<?=$id_cliente;?>&id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>')" class='link'>
            <?
                    }else {
            ?>
                    <a href="javascript:html5Lightbox.showLightbox(7, '../../representante/alterar2.php?passo=1&id_representante=<?=$campos_itens[$i]['id_representante'];?>&pop_up=1')" class='link'>
            <?
                    }
                    
                    /*Se existir país no cadastro desse Representante, então representa que o mesmo é externo, 
                    consequentemente apresento o seu Supervisor ...*/
                    if(!is_null($campos_representante[0]['id_pais'])) {
                        //Busca do Supervisor ...
                        $sql = "SELECT r.`nome_fantasia` AS supervisor 
                                FROM `representantes_vs_supervisores` rs 
                                INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                                WHERE rs.`id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
                        $campos_supervisor  = bancos::sql($sql);
                        $title              = 'Supervisor => '.$campos_supervisor[0]['supervisor'];
                    }else {
                        $title              = '';
                    }
            ?>
                        <font title='<?=$title;?>' style='cursor:help'>
                            <?=$campos_representante[0]['nome_fantasia'];?>
                        </font>
                    </a>
            <?
                    $preco_total_lote = $campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'];
                    /************************************************************************************************/
                    /*********************************Logística Nova Margem de Lucro*********************************/
                    /************************************************************************************************/		
                    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {//Link exibido somente p/ Roberto 62 e Dárcio 98 porque programa ...
                        if($campos[0]['congelar'] == 'S' && $campos_itens[$i]['status'] == 0) {//Só quando o Orçamento estiver congelado e em Pendência Total que mostro o Link ...
            ?>
                    <a href="javascript:html5Lightbox.showLightbox(7, '../../orcamentos/itens/alterar_comissao_extra.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>')" title='Alterar Comissão Extra' class='link'>
            <?
                        }
                    }
                    //Aqui eu entro com a Margem de Lucro dentro da Tabela da Nova Margem de Lucro ...
                    echo '<br/><font color="brown" title="Comiss&atilde;o Extra => '.number_format($campos_itens[$i]['comissao_extra'], '2', ',', '.').'" style="cursor:help">'.number_format($campos_itens[$i]['comissao_new'] + $campos_itens[$i]['comissao_extra'], 2, ',', '.').'% ';
                    
                    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {//Link exibido somente p/ Roberto 62 e Dárcio 98 porque programa ...
                        if($campos[0]['congelar'] == 'S' && $campos_itens[$i]['status'] == 0) {//Só quando o Orçamento estiver congelado e em Pendência Total que mostro o Link ...
            ?>
                    </a>
            <?
                        }
                    }
                    echo '<br>'.$tipo_moeda.number_format(vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new'] + $campos_itens[$i]['comissao_extra']), 2, ',', '.');
                    /************************************************************************************************/
            ?>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>" align='right'>
                <?
                    $calculo_impostos_item  = calculos::calculo_impostos($campos_itens[$i]['id_orcamento_venda_item'], $id_orcamento_venda, 'OV');
                    /*Quando existir Frete Despesas Acessórias e o Frete = 'CIF (POR NOSSA CONTA)',
                    apresento o texto abaixo ...*/
                    if($calculo_impostos_item['frete_despesas_acessorias'] > 0 && $campos[0]['tipo_frete'] == 'C') echo '<font title="Frete por Pe&ccedil;a R$ '.number_format($calculo_impostos_item['frete_despesas_acessorias'] / $campos_itens[$i]['qtde'], 4, ',', '.').'" style="cursor:help"><b>Frete+ </b></font>';
                ?>
                    <font face='Verdana, Arial, Helvetica, sans-serif'>
                    <?
                            //Cálculo do Preço por Kilo, faço um Tratamento p/ não dar erro de Divisão por Zero ...
                            $preco_por_kilo = ($campos_itens[$i]['peso_unitario'] != 0) ? $campos_itens[$i]['preco_liq_final'] / round($campos_itens[$i]['peso_unitario'], 4) : $campos_itens[$i]['preco_liq_final'];
                            $preco_por_kilo = number_format($preco_por_kilo, 2, ',', '.');
/***************************************************************************************/
                            echo "<font title = 'Pre&ccedil;o $tipo_moeda $preco_por_kilo / Kg' style='cursor:help'>";
                            echo number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.');
//Se o P.A. = 'ESP' e a O.C. = Revenda, então eu chamo uma função p/ retornar o Lote Mínimo p/ Compra
                            if($campos_itens[$i]['referencia'] == 'ESP' && $campos_itens[$i]['operacao_custo'] == 1) {
                                echo '<br>'.retornar_lote_minimo_compra($campos_itens[$i]['id_produto_acabado']);
                            }

                            /******************************************************************************************************************************/
                            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                                $margem                         = custos::margem_lucro($campos_itens[$i]['id_orcamento_venda_item'], $tx_financeira, $id_uf_cliente, $campos_itens[$i]['preco_liq_final']);
                                $custo_margem_lucro_zero        = $margem[2];//preco_custo_zero
                                $soma_margem_lucro_zero+=       $custo_margem_lucro_zero * $campos_itens[$i]['qtde'];
                                $soma_margem_lucro_g_zero+=     ($campos_itens[$i]['qtde'] * $campos_itens[$i]['preco_liq_final']) / (1 + $campos_itens[$i]['margem_lucro'] / 100);
                                $soma_margem_lucro_est_zero+=   ($campos_itens[$i]['qtde'] * $campos_itens[$i]['preco_liq_final']) / (1 + $campos_itens[$i]['margem_lucro_estimada'] / 100);

                                $valores                = vendas::calcular_ml_min_pa_vs_cliente($campos_itens[$i]['id_produto_acabado'], $id_cliente);
                                $rotulo_ml_min          = $valores['rotulo_ml_min'];
                                $rotulo_preco           = $valores['rotulo_preco'];
                                $margem_lucro_minima    = $valores['margem_lucro_minima'];
                    ?>
                            <a href="javascript:html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/orcamentos/itens/alterar_margem_lucro.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>')" title='Compras' class='link'>
                                    <font color='#E8E8E8' title='Margem de Lucro M&iacute;nima' style='cursor:help'><br>
                                        <?=$rotulo_ml_min.number_format($margem_lucro_minima, 1, ',', '');?>
                                    </font>
                    <?	
                                    if((double)strtr($margem[1], ',%', '. ') < $margem_lucro_minima) {//Se a ML Instantânea do ORC < ML do Grupo ...
                    ?>
                                    <font color='#E8E8E8' title='Pre&ccedil;o Ideal' style='cursor:help'><br>
                                    <?	
                                        $pco_ideal = round($campos_itens[$i]['preco_liq_final'] / ((double)strtr($margem[1], ',%', '. ') / 100 + 1) * ($margem_lucro_minima / 100 + 1), 2);
                                        echo $rotulo_preco.number_format($pco_ideal, 2, ',', '');
                                    ?>
                                    </font>
                    <?
                                    }
                    ?>
                            </a>
                    <?
                            }
                            //Essa informação já mostro independente do Funcionário Logado ...
                            /******************************************************************************************************************************/
                            $fora_custo = vendas::verificar_orcamento_item_fora_custo($campos_itens[$i]['id_orcamento_venda_item']);
                            if($fora_custo == 'S') {
                    ?>
                                <font color='red'>
                                    <b>Fora Custo</b>
                                </font>
                    <?
                            }
                            /******************************************************************************************************************************/
                            //Somente quando o Cliente for do Tipo -> "Órgão Governamental" que faço o cálculo abaixo ...
                            if($id_cliente_tipo == 15) {
                                $percentagem_est_icms_st    = ($calculo_impostos_item['valor_icms_st'] * 100) / $preco_total_lote;
                                /*O cálculo Pró-Rata do Frete que vem da Função -> "calculo_impostos" é feito baseado no Peso de cada Peça, ou seja, 
                                quanto mais pesada a peça mais caro o Frete dela, independente do valor de Venda ...*/

                                /*Roberto jogar o Custo do Frete na função Margem de Lucro / Comissão ...

                                 Precisamos ver o que o Pedido herdará do Orc no quesito Frete / Preço Orgão Governamental ...

                                 Retirar os Preços em Verdes, Descontos ... Confirmar com Nishi ...*/
                                $preco_orgao_governamental  = ($campos_itens[$i]['preco_liq_final'] + $calculo_impostos_item['frete_despesas_acessorias'] / $campos_itens[$i]['qtde']) * (1 + $dados_produto['ipi'] / 100 + $percentagem_est_icms_st / 100);
                                echo '<font color="purple"><b>Pr.Org.Gov='.number_format($preco_orgao_governamental, 2, ',', '.').'</b></font>';
                            }
                    ?>
                    </font>
            </td>
            <td class='td_pontilhada' bgcolor='<?=$cor_fundo_linha;?>' align='right'>
            <?
                    echo number_format($preco_total_lote, 2, ',', '.');

                    if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                        $cor_instantanea    = ($margem[0] < 0) ? 'red' : '#E8E8E8';//$margem[0] valor real da margem
                        $cor_gravada        = ($campos_itens[$i]['margem_lucro'] < 0) ? 'red': '#E8E8E8';
                        $cor_estimada       = ($campos_itens[$i]['margem_lucro_estimada'] < 0) ? 'red': '#E8E8E8';
            ?>
                        <!--************************************************************************-->
                        <!--A folha de estilo fica aqui dentro do Loop porque as cores de fonte 
                        dos IDs se comportaram de acordo com o que foi definido acima pelo PHP ...-->
                        <style type='text/css'>
                            #id_ml_instantanea<?=$i;?>::-moz-selection {
                                background:#A9A9A9;
                                color:<?=$cor_instantanea;?>
                            }
                            #id_ml_gravada<?=$i;?>::-moz-selection {
                                background:#A9A9A9;
                                color:<?=$cor_gravada;?>
                            }
                            #id_ml_estimada<?=$i;?>::-moz-selection {
                                background:#A9A9A9;
                                color:<?=$cor_estimada;?>
                            }
                        </style>
                        <!--************************************************************************-->
                        <a href="javascript:html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/orcamentos/itens/alterar_margem_lucro.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>&preco_liq_final=<?=number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.');?>&margem_lucro=<?=$margem[1];?>')" title='Compras' class='link'>
                            <font color="<?=$cor_instantanea;?>" id='id_ml_instantanea<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'><br>
                                <?='ML='.$margem[1];//Valor Descritivo da Margem ...?>
                            </font>
                            <font color="<?=$cor_gravada;?>" id='id_ml_gravada<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'>
                                <?='MLG='.number_format($campos_itens[$i]['margem_lucro'], 2, ',', '.');?>
                            </font>
                            <font color="<?=$cor_estimada;?>" id='id_ml_estimada<?=$i;?>' title='Margem de Lucro Estimada' style='cursor:help'>
                                <?='MLEst='.number_format($campos_itens[$i]['margem_lucro_estimada'], 2, ',', '.');?>
                            </font>
                        </a>
            <?
                    }
            ?>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
            <?
                if($dados_produto['ipi'] > 0) {
                    echo number_format($dados_produto['ipi'], 2, ',', '.').' %';
                    echo '<br><b>'.$tipo_moeda.number_format($preco_total_lote * ($dados_produto['ipi'] / 100), 2, ',', '.').'</b>';
                }else {
                    echo 'S/IPI';
                }
            ?>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
            <?
                //Aqui eu verifico se existe IVA ...
                if($campos_itens[$i]['iva'] > 0) {
                    echo '<font title="ICMS INTRAESTADUAL '.number_format($dados_produto['icms_intraestadual'], 2, ',', '.').' - IVA AJUSTADO '.number_format($calculo_impostos_item['iva_ajustado'] * 100, 2, ',', '.').'%" style="cursor:help">'.number_format($campos_itens[$i]['iva'], 2, ',', '.').' %</font>';

                    echo '<br>ST=<font color="blue"><b>'.number_format(($calculo_impostos_item['valor_icms_st'] * 100) / $preco_total_lote, 2, ',', '.').'%</b></font>';
                    echo '<br><b>'.$tipo_moeda.number_format($calculo_impostos_item['valor_icms_st'], 2, ',', '.').'</b></font>';
                }else {
                    echo 'S/IVA';
                }
            ?>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
            <?
                if($dados_produto['icms'] > 0) {
                    echo number_format($dados_produto['icms'], 2, ',', '.').' %';
                    if($dados_produto['reducao'] > 0) echo '<br><font color="blue"><b>RED '.number_format($dados_produto['reducao'], 2, ',', '.').'%</b></font>';
                }else {
                    echo 'S/ICMS';
                }
            ?>
            </td>
            <td class='td_pontilhada' bgcolor="<?=$cor_fundo_linha;?>">
                &nbsp;
            </td>
    </tr>
<?
                    $comissao_por_item_rs = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new']);
                    $total_comissoes_dos_itens_rs+= $comissao_por_item_rs;

                    $comissao_extra_por_item_rs = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_extra']);
                    $total_comissoes_extras_dos_itens_rs+= $comissao_extra_por_item_rs;
                    
                    /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
                    encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
                    da Biblioteca de Custos, na função pas_atrelados ...*/
                    unset($vetor_pas_atrelados);
		}
?>
</table>
<table width='100%' border='0' cellspacing='1' cellpadding='0' align='center'>
    <tr></tr>
    <tr></tr>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>
                    C&Aacute;LCULO DO IMPOSTO
                </font>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color="yellow">VALOR DO ICMS ST: </font>
                <br>R$ 
                <?
                    echo number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.');
                    
                    if($calculo_total_impostos['fecp'] > 0) {
                        $base_calculo_fecp  = $calculo_total_impostos['base_calculo_icms_st'];
                        $valor_fecp         = number_format(round($base_calculo_fecp * ($calculo_total_impostos['fecp'] / 100), 2), 2, '.', '');
                        $total_icms_st      = round($calculo_total_impostos['valor_icms_st'] + $valor_fecp, 2);

                        echo '<br/><font color="#49D2FF">FECP = R$ '.number_format($valor_fecp, 2, ',', '.').'&nbsp;&nbsp;-&nbsp;&nbsp;TOTAL ICMS ST = R$ '.number_format($total_icms_st, 2, ',', '.').'</font>';
                    }
                ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color="yellow">VALOR DO IPI: </font>
                <br><?=$tipo_moeda.number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color="yellow">VALOR TOTAL DOS PRODUTOS: </font>
                <br/><?=$tipo_moeda.number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.');?></td>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color="yellow">VALOR TOTAL DO OR&Ccedil;AMENTO: </font>
                <br/>
                <?
                    echo $tipo_moeda.number_format($calculo_total_impostos['valor_icms_st'] + $calculo_total_impostos['valor_ipi'] + $calculo_total_impostos['valor_total_produtos'], 2, ',', '.');
                    //Sempre que carregar essa tela guarda esse valor Total do Orçamento ...
                    $sql = "UPDATE `orcamentos_vendas` SET `valor_orc` = '".($calculo_total_impostos['valor_icms_st'] + $calculo_total_impostos['valor_ipi'] + $calculo_total_impostos['valor_total_produtos'])."' WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
                    bancos::sql($sql);
                ?>
            </font>
        </td>
    </tr>
</table>
<table width='100%' border='0' cellspacing='1' cellpadding='1' class='table_pontilhada' onmouseover='total_linhas(this)'>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <font color='yellow'>
                Taxa Financeira:
            </font>
            <?=number_format($tx_financeira, 2, ',', '.');?>
            <font color='yellow'>
            <br/>D&oacute;lar do Dia:
            </font>
            <?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?>
        </td>
        <td colspan='9'>
            <font color='yellow'>
                Comiss&atilde;o M&eacute;dia %:
            </font>
            <?
//Aqui nessa parte do cálculo eu pego a comissão média e divido pela qtde de Itens do Pedido de Venda 
//Esse desvio para não dar erro de divisão por Zero, Dárcio
            if($calculo_total_impostos['valor_total_produtos'] > 0) {
                $comissao_media         = round(($total_comissoes_dos_itens_rs / $calculo_total_impostos['valor_total_produtos']) * 100, 2);
                $comissao_media_extra   = round(($total_comissoes_extras_dos_itens_rs / $calculo_total_impostos['valor_total_produtos']) * 100, 2);
            }else {
                $comissao_media         = round($total_comissoes_dos_itens_rs * 100, 2);
                $comissao_media_extra   = round($total_comissoes_extras_dos_itens_rs * 100, 2);
            }
            echo '<font title="Comiss&atilde;o = '.number_format($comissao_media, 2, ',', '.').'% + Extra = '.number_format($comissao_media_extra, 2, ',', '.').'%" style="cursor:help">'.number_format($comissao_media + $comissao_media_extra, 2, ',', '.');
            /*****************************************************************************************/
               
            if(!empty($campos[0]['cod_suframa'])) {//Aki pego se tem suframa
                if($campos[0]['tipo_suframa'] == 1) {//Área de Livre ...
                    echo "Desconto de ICMS = 7 % <font color='yellow'>(A ser concedido na Emiss&atilde;o da Nota Fiscal)<br></font></font></font>";
                }else if($campos[0]['tipo_suframa'] == 2) {//Zona Franca de Manaus ...
                    echo "Desconto de PIS + Cofins = ".number_format((genericas::variavel(20)+genericas::variavel(21)), 2, ',', '.')." %  e ICMS = 7 % <font color='yellow'>(A ser concedido na Emiss&atilde;o da Nota Fiscal)<br></font></font></font>";
                }
            }
            if(!empty($campos[0]['artigo_isencao'])) {//Printa se existir Artigo de Isenção ...
                echo 'SUSPENSO IPI, CONF.ART.29, PARÁGRAFO 1, ALÍNEA A E B, LEI 10637/02.<br>';
            }
            if(!empty($campos[0]['trading'])) {//Aqui printa se existir trading para este cliente
                echo 'COMERCIAL EXPORTADOR (TRADING).';
//Nova Regra implantada no dia 18/02/2008 de acordo com o Roberto q disse q não está muito bem (rs) ...
                $data_atual = date('Y-m-d');
                if($data_atual >= '2008-02-18') {
                    echo '<br>M.Lucro descontado ICMS de SP + PIS + Cofins';
                }
            }
        ?>
        </td>
        <td colspan='3' align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                Peso Total:
            </font>
        </td>
        <td colspan='2' align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <a href="javascript:html5Lightbox.showLightbox(7, 'http://www2.correios.com.br/sistemas/precosPrazos/')" title='Consultar Sedex (Correios)' class='link'>
                    <font color='#000000'>
                        <?=number_format($peso_total_orcamento, 3, ',', '.').' (Kg)';?>
                    </font>
                </a>
            </font>
        </td>
        <td colspan='3' align="right">
        <?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                //Faço esse tratamento p/ não dar erro de divisão por 0
                if($soma_margem_lucro_zero == 0 || empty($soma_margem_lucro_zero) || (integer)($soma_margem_lucro_zero) == 0) $soma_margem_lucro_zero = 1;
                echo "<font color='#83B2E3'>M.L.M=".number_format(((($calculo_total_impostos['valor_total_produtos'] / round($soma_margem_lucro_zero, 1)) - 1) * 100), 1, ',', '.')." %</font>";
                echo "<br><font color='#83B2E3'>M.L.M.G=".number_format(($calculo_total_impostos['valor_total_produtos'] / $soma_margem_lucro_g_zero - 1) * 100, 1, ',', '.')." %</font>";
                echo "<br><font color='#83B2E3'>M.L.Est.M=".number_format(($calculo_total_impostos['valor_total_produtos'] / $soma_margem_lucro_est_zero - 1) * 100, 1, ',', '.')." %</font>";
            }
        ?>
        </td>
    </tr>
    <tr class='erro' align='center'>
        <td colspan='20'>
            O(s) item(ns) na cor laranja s&atilde;o PA(s) que precisa(m) ser trocado(s), pois j&aacute; existe(m) similare(s) cadastrado(s).
        </td>
    </tr>
    <tr align='center'>
        <td colspan='20'>
            <font size='-2' color='#0066ff' face='verdana, arial, helvetica, sans-serif'>
                <?=utf8_encode(paginacao::print_paginacao('sim'));?>
            </font>
        </td>
    </tr>
<?
	}
?>
</table>
</html>