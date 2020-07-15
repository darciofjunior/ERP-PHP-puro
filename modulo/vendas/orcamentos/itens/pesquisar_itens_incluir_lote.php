<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php');

$mensagem[1] = '<font class="atencao">SUA CONSULTA N&Atilde;O RETORNOU NENHUM RESULTADO.</font>';

//Tratamento com as variáveis que vem por parâmetro ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hdd_checkbox_mostrar_esp           = $_POST['hdd_checkbox_mostrar_esp'];
    $hdd_checkbox_mostrar_comprados     = $_POST['hdd_checkbox_mostrar_comprados'];
    $hdd_checkbox_mostrar_especiais     = $_POST['hdd_checkbox_mostrar_esp'];
    $txt_referencia 			= $_POST['txt_referencia'];
    $txt_discriminacao 			= $_POST['txt_discriminacao'];
    $txt_codigo_produto_cliente         = $_POST['txt_codigo_produto_cliente'];
    $id_orcamento_venda			= $_POST['id_orcamento_venda'];
}else {
    $hdd_checkbox_mostrar_esp           = $_GET['hdd_checkbox_mostrar_esp'];
    $hdd_checkbox_mostrar_comprados     = $_GET['hdd_checkbox_mostrar_comprados'];
    $hdd_checkbox_mostrar_especiais     = $_POST['hdd_checkbox_mostrar_esp'];
    $txt_referencia 			= $_GET['txt_referencia'];
    $txt_discriminacao 			= $_GET['txt_discriminacao'];
    $txt_codigo_produto_cliente         = $_GET['txt_codigo_produto_cliente'];
    $id_orcamento_venda			= $_GET['id_orcamento_venda'];
}

//Se o usuário digitar ML ou MR na referência do Filtro, ofereço também p/ ele a opção dos Machos Heinz ...
if(strpos(strtoupper($txt_referencia), 'ML') !== false || strpos(strtoupper($txt_referencia), 'MR') !== false) {
    if(strpos($txt_referencia, '-') !== false) {//Se tivermos o caractér - ...
        $txt_referencia = substr($txt_referencia, 0, 2).'%'.substr($txt_referencia, 2, strlen($txt_referencia));
        /**********************************************************************/
        /*****Caso inexplicável, o sistema se perde com a referência e tem que 
        buscar o valor da própria variável $parametro que fica na memória******/
        /**********************************************************************/
    }else {//O usuário digitou a referência sem o caractér - ...
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            if(strpos($parametro, 'txt_referencia') !== false) {//Já clicou pelo menos uma vez na paginação ...
                $posicao_referencia = strpos($parametro, 'txt_referencia');
                unset($txt_referencia);//Destrói o valor armazenado na memória que foi digitado pelo usuário ...

                $concatenar_referencia = 'N';
                for($i = $posicao_referencia; $i < strlen($parametro); $i++) {
                    if($concatenar_referencia == 'S') $txt_referencia.= substr($parametro, $i, 1);

                    if(substr($parametro, $i, 1) == '=') {
                        $concatenar_referencia = 'S';
                    }else if(substr($parametro, $i, 1) == '&') {
                        break;
                    }
                }
                $txt_referencia = str_replace('%25', '%', $txt_referencia);
                $txt_referencia = str_replace('&', '', $txt_referencia);
            }else {//Significa que ainda não foi clicada na paginação ...
                $txt_referencia = substr($txt_referencia, 0, 2).'%'.substr($txt_referencia, 2, strlen($txt_referencia));
            }
        }else {
            $txt_referencia = substr($txt_referencia, 0, 2).'%'.substr($txt_referencia, 2, strlen($txt_referencia));
        }
    }
}

//Se essa opção estiver desmarcada, então eu só mostro os P.A(s) que são do Tipo Normal de Linha ...
if($hdd_checkbox_mostrar_esp == 0) $condicao_esp = " AND pa.`referencia` <> 'ESP' ";

//Não me lembro com que intuito que o Luís criou essa regra abaixo na Época ...
if($_SESSION['id_funcionario'] == 98 && isset($_SESSION['id_orcamento_vendas_luis_sessao'])) {
    $condicao_luis = "AND pa.`id_produto_acabado` IN (".$_SESSION['id_orcamento_vendas_luis_sessao'].") ";
}

/*Os únicos funcionários que podem incluir PA´s que são Componentes e Mão de Obra no Sistema são: 
Rivaldo 27, Rodrigo Soares 54, Roberto Cambria 62, Fabio Petroni 64, Dárcio 98 porque desenvolvem o Sistema e 
Rodrigo Bispo 125 ...*/
$vetor_funcionarios_com_permissao = array(27, 54, 62, 64, 98, 125);

if(!in_array($_SESSION['id_funcionario'], $vetor_funcionarios_com_permissao)) $condicao_componente = " AND gpa.`id_familia` NOT IN (23, 24, 25) ";

//Aqui eu pego o id_cliente pois irei precisa mais embaixo para lista a qtde comprada nos ultimos 5 anos.
$sql = "SELECT ov.`id_cliente` 
        FROM `orcamentos_vendas` ov 
        INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
        WHERE `id_orcamento_venda` = $id_orcamento_venda";
$campos_cliente = bancos::sql($sql);

if($hdd_checkbox_mostrar_comprados == 1) {
    $inner_join_pedidos_vendas = "
            INNER JOIN pedidos_vendas_itens pvi ON pvi.id_produto_acabado = pa.id_produto_acabado 
            INNER JOIN pedidos_vendas pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pv.id_cliente = '".$campos_cliente[0]['id_cliente']."' AND YEAR(pv.`data_emissao`) >= '".(date('Y') - 5)."' ";
}

//Aqui eu trago todos os PAs de acordo com o "Código do Produto" filtrado do Respectivo Cliente ...
if(!empty($txt_codigo_produto_cliente)) {
    $sql = "SELECT `id_produto_acabado` 
            FROM `pas_cod_clientes` 
            WHERE `id_cliente` = '".$campos_cliente[0]['id_cliente']."' 
            AND `cod_cliente` LIKE '$txt_codigo_produto_cliente%' ";
    $campos_codigo_clientes = bancos::sql($sql);
    $linhas_codigo_clientes = count($campos_codigo_clientes);
    if($linhas_codigo_clientes > 0) {
        for($i = 0; $i < $linhas_codigo_clientes; $i++) $id_produtos_acabados.= $campos_codigo_clientes[$i]['id_produto_acabado'].', ';
        $id_produtos_acabados = substr($id_produtos_acabados, 0, strlen($id_produtos_acabados) - 2);

        $condicao_produtos_acabados = " AND pa.`id_produto_acabado` IN ($id_produtos_acabados) ";
    }
}

/*Aqui eu transformo o | em % porque é o caractér padrão que o Mysql utiliza p/ fazer os Filtros, 
só não lista os PA(s) que são Componentes ...

* PA(s) com referência BE eu não mostro porque não vendemos BE, são produtos comprados de fora da qual fazemos um retrabalho em cima deste ...
 
* PA(s) com discriminação BLANK eu não mostro porque não vendemos BLANK de Machos e neste momento estão como 
Família Machos e não Componentes ...

Também não mostro o(s) PA(s) que estão com a marcação "Não Produzido Temporariamente" ... */
$sql = "SELECT DISTINCT(pa.`id_produto_acabado`), ged.`realcar`, gpa.`id_familia`, pa.`referencia`, 
        pa.`discriminacao`, pa.`operacao_custo`, pa.`status_top`, pa.`qtde_promocional`, 
        pa.`preco_promocional`, pa.`qtde_queima_estoque`, u.`sigla` 
        FROM `produtos_acabados` pa 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` $condicao_componente 
        $inner_join_pedidos_vendas 
        WHERE (pa.`referencia` LIKE '%".str_replace('|', '%', $txt_referencia)."%' AND pa.`referencia` NOT LIKE 'BE%') 
        AND (pa.`discriminacao` LIKE '%".str_replace('|', '%', $txt_discriminacao)."%' AND pa.`discriminacao` NOT LIKE 'BLANK%') 
        AND pa.`status_nao_produzir` = '0' 
        AND pa.`ativo` = '1' 
        $condicao_esp 
        $condicao_luis 
        $condicao_produtos_acabados 
        ORDER BY pa.`referencia`, pa.`discriminacao` ";
$campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina, 'ajax', 'pesquisar_itens_incluir_lote');
$linhas = count($campos);
if($linhas == 0) {//Caso não encontrou nenhum Item ...
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
                <?=utf8_encode($mensagem[1]);?>
            </font>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <?
        if($hdd_checkbox_mostrar_comprados == 1) {
    ?>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            <a href="#" onclick="document.form.chkt_mostrar_comprados.checked = false;controlar_hdd_checkbox_comprados();pesquisar_itens_incluir_lote()" style="border-style:solid; border-width:1px; text-decoration:none">
                <font face='Verdana, Arial, Helvetica, sans-serif' color='red' size='3'>
                    &nbsp;CLIQUE AQUI P/ MOSTRAR TODO(S) O(S) ITEM(NS) DO SISTEMA&nbsp;
                </font>
            </a>
            <p>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue' size='-2'>
                INCLUSIVE O(S) ITEM(NS) N&Atilde;O ADQUIRIDO(S) POR ESTE CLIENTE NOS &Uacute;LTIMO(S) 5 ANO(S)
            </font>
        </td>
    </tr>
    <?
        }else {
    ?>
    <tr class="atencao" align='center'>
        <td colspan="3">
            <a href="#" onclick="provisionar_novo_pa_esp()" style="border-style:solid; border-width:1px; text-decoration:none">
                <font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue' size='-1'>
                    &nbsp;PROVISIONAR NOVO PA ESP&nbsp;
                </font>
            </a>
        </td>
    </tr>
    <?
        }
    ?>    
</table>
<!--**************Esses objetos serão submetidos**************-->
<input type='hidden' name='id_orcamento_venda' value='<?=$_POST['id_orcamento_venda'];?>'>
<input type='hidden' name='hdd_referencia' value='<?=$_POST['txt_referencia'];?>'>
<input type='hidden' name='hdd_discriminacao' value='<?=$_POST['txt_discriminacao'];?>'>
<input type='hidden' name='hdd_quantidade'>
<!--**********************************************************-->
</body>
</html>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <?/*
        if($hdd_checkbox_mostrar_especiais == 1) {
    ?>
    <tr class="atencao" align='center'>
        <td colspan="3">
            <a href="#" onclick="provisionar_novo_pa_esp()" style="border-style:solid; border-width:1px; text-decoration:none">
                <font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue' size='-1'>
                    &nbsp;PROVISIONAR NOVO PA ESP&nbsp;
                </font>
            </a>
        </td>
    </tr>
    <?
        }*/
    ?>        
	<tr>
		<td>
			<fieldset>
				<legend>
					<span>
						<font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
							<b>OR&Ccedil;AR PRODUTOS ACABADOS</b>
						</font>
					</span>
				</legend>
				<table width='100%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
					<tr class="linhacabecalho" align='center'>
						<td>
                                                    <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onClick="selecionar_tudo_incluir(totallinhas, '#E8E8E8')" title='Selecionar Tudo' class="checkbox">
						</td>
						<td>
                                                    Produto
						</td>
						<td>
                                                    Top
						</td>                                                
						<td>
                                                    P&ccedil;s /<br>Emb.
						</td>
						<td>
                                                    Qtde
						</td>
						<td>
                                                    E.D.
						</td>
                                                <td>
                                                    E Forn
                                                </td>
                                                <td>
                                                    E Porto
                                                </td>
						<td>
                                                    Qtde <br/>Produ&ccedil;&atilde;o
						</td>
                                                <?
                                                    for($ano = (date('Y') - 4); $ano <= date('Y'); $ano++) {
                                                ?>
                                                <td>
							<?=$ano;?>
						</td>
                                                <?
                                                    }
                                                ?>
                                                <td>
                                                    Sugest&atilde;o<br/>Comp Mensal
						</td>                                                
					</tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            //Busca de alguns dados de Estoque do PA ...
            $estoque_produto    = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
            $qtde_producao      = $estoque_produto[2];
            $qtde_disponivel    = $estoque_produto[3];
            $racionado          = $estoque_produto[5];
            $est_fornecedor     = $estoque_produto[12];
            $est_porto          = $estoque_produto[13];
                
            //Busca da Qtde de Pçs por Embalagem do PA ...
            $sql = "SELECT `pecas_por_emb` 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `embalagem_default` = '1' LIMIT 1 ";
            $campos_pcs_embalagem = bancos::sql($sql);
            $pecas_por_emb	 = (count($campos_pcs_embalagem) == 1) ? $campos_pcs_embalagem[0]['pecas_por_emb'] : 0;
            
//Os valores retornados das funções serão utilizados mais abaixo ...
            $retorno        = vendas::buscar_qtde($campos[$i]['id_produto_acabado']);
            $compra         = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
            $compra_producao = $campos_estoque[0]['qtde_producao'] + $compra;
            
/*Aqui é uma verificação para saber se o Lote do Custo da Etapa 5, está habilitado, caso isso aconteça
não pode ser alterado a quantidade*/
            $sql = "SELECT ppt.`lote_minimo_fornecedor` 
                    FROM `produtos_acabados_custos` pac 
                    INNER JOIN `pacs_vs_pis_trat` ppt ON ppt.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` AND ppt.`lote_minimo_fornecedor` = '1' 
                    WHERE pac.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' 
                    AND pac.`operacao_custo` = ".$campos[$i]['operacao_custo']." LIMIT 1 ";
            $campos_lote_minimo_fornec = bancos::sql($sql);
            if(count($campos_lote_minimo_fornec) == 1 && $campos_lote_minimo_fornec[0]['lote_minimo_fornecedor'] > 0) {
                $vetor_lote_minimo_fornecedor.= $i.',';//Vetor de controle p/ o JavaScript ...
            }
            //Se esse item estiver com uma marcação de Realce, então mudo a Cor da Linha ...
            $cor_fundo_linha = ($campos[$i]['realcar'] == 'S') ? '#EEB4B4' : '';
?>
					<tr class='linhanormal' onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8');calcular_estoque_real('<?=$i;?>', '<?=$qtde_disponivel;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
						<td bgcolor='<?=$cor_fundo_linha;?>'>
                                                    <input type='checkbox' name='chkt_produto_acabado[]' id='chkt_produto_acabado<?=$i;?>' value='<?=$campos[$i]['id_produto_acabado'];?>' onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8');calcular_estoque_real('<?=$i;?>', '<?=$qtde_disponivel;?>')" class='checkbox'>
						</td>
						<td bgcolor='<?=$cor_fundo_linha;?>' align='left'>
                                                    <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
						</td>
                                                <td bgcolor='<?=$cor_fundo_linha;?>'>
                                                <?
                                                    if($campos[$i]['status_top'] == 1) {
                                                        echo '<font color="red"><b>A</b></font>';
                                                        $top = 3;
                                                    }else if($campos[$i]['status_top'] == 2) {
                                                        echo '<font color="red"><b>B</b></font>';
                                                        $top = 2;
                                                    }else {
                                                        $top = 1;
                                                    }
                                                ?>
                                                </td>
                                                <td bgcolor='<?=$cor_fundo_linha;?>'>
						<?
                                                    //Traz a quantidade de peças por embalagem da embalagem principal daquele produto ...
                                                    $sql = "SELECT `pecas_por_emb` 
                                                            FROM `pas_vs_pis_embs` 
                                                            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                                                            AND `embalagem_default` = '1' LIMIT 1 ";
                                                    $campos_pecas_emb   = bancos::sql($sql);
                                                    $pecas_embalagem    = (count($campos_pecas_emb) == 1) ? $campos_pecas_emb[0]['pecas_por_emb'] : 0;
                                                    echo number_format($pecas_embalagem, 0, ',', '.');
						?>
                                                    <input type='hidden' id='hdd_pecas_emb<?=$i;?>' value='<?=$pecas_embalagem;?>'>
						</td>
						<td bgcolor='<?=$cor_fundo_linha;?>'>
                                                <?
                                                    /************************************************************/
                                                    /****Tratamento com as Casas Decimais do campo Quantidade****/
                                                    /************************************************************/
                                                    if($campos[$i]['sigla'] == 'KG') {//Essa é a única sigla que permite trabalharmos com Qtde Decimais ...
                                                        $onkeyup            = "verifica(this, 'moeda_especial', 1, '', event) ";
                                                        $qtde_apresentar    = number_format($retorno['qtde'], 1, ',', '.');
                                                    }else {
                                                        $onkeyup            = "verifica(this, 'aceita', 'numeros', '', event) ";
                                                        $qtde_apresentar    = (integer)$retorno['qtde'];
                                                    }
                                                    /************************************************************/
                                                ?>
                                                    <input type='text' name='txt_quantidade[]' id='txt_quantidade<?=$i;?>' value='<?=$qtde_apresentar;?>' title="<?=$retorno['title'];?>" onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8');focos(this)" onkeyup="<?=$onkeyup;?>;calcular_estoque_real('<?=$i;?>', '<?=$qtde_disponivel;?>');validar_itens(event)" maxlength='5' size='5' class='textdisabled' disabled>
                                                    <input type='hidden' id='hdd_qtde_lote<?=$i;?>' value='<?=$retorno['qtde'];?>'>
						</td>
						<td bgcolor='<?=$cor_fundo_linha;?>'>
                                                <?//Esse cálculo é feito somente na primeira vez em que carrega a Tela ...
                                                    $estoque_disponivel = ($qtde_disponivel > $retorno['qtde']) ? $qtde_disponivel - $retorno['qtde'] : 0;
                                                    if($campos_estoque[0]['racionado'] == 1) echo '<font color="red" title="Racionado" style="cursor:help"><b>(R)</b></font>';
                                                    $type = ($campos_estoque[0]['racionado'] == 1) ? 'hidden' : 'text';
                                                ?>
                                                    <input type='<?=$type;?>' name='txt_estoque_disponivel[]' id='txt_estoque_disponivel<?=$i;?>' value="<?=intval($estoque_disponivel);?>" title='Estoque Dispon&iacute;vel => <?=$qtde_disponivel;?>' maxlength='8' size='5' class='textdisabled' disabled>
                                                <?
                                                    /**************************************************************************************/
                                                    /**********************************Excesso de Estoque**********************************/
                                                    /**************************************************************************************/
                                                    if($campos[$i]['qtde_queima_estoque'] > 0) {
                                                ?>
                                                    <img src='../../../../imagem/bloco_vermelho.gif' title='Qtde de Excesso de Estoque => <?=number_format($campos[$i]['qtde_queima_estoque'], 2, ',', '.');?>' style='cursor:help' width='8' height='8' border='0' style='cursor:help'>
                                                <?
                                                    }
                                                    /**************************************************************************************/
                                                ?>
						</td>
                                                <td bgcolor='<?=$cor_fundo_linha;?>' align='right'>
                                                    <?=number_format($est_fornecedor, 0, ',', '.');?>
                                                </td>
                                                <td bgcolor='<?=$cor_fundo_linha;?>' align='right'>
                                                    <?=number_format($est_porto, 0, ',', '.');?>
                                                </td>    
						<td bgcolor='<?=$cor_fundo_linha;?>'>
                                                    <input type='text' name='txt_qtde_producao[]' id='txt_qtde_producao<?=$i;?>' value='<?=intval($compra_producao);?>' title='Qtde de Produção' maxlength='8' size='5' class='textdisabled' disabled>
                                                    <?
//Busca a qtde de peças ...
                                                        $sql = "SELECT `lote_minimo`, `peca_corte` 
                                                                FROM `produtos_acabados_custos` 
                                                                WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                                                                AND `operacao_custo` = ".$campos[$i]['operacao_custo']." LIMIT 1 ";
                                                        $campos_pecas_corte = bancos::sql($sql);
                                                        $pecas_corte        = ($campos_pecas_corte[0]['peca_corte'] == 0) ? 1 : $campos_pecas_corte[0]['peca_corte'];
                                                    ?>
<!--Essas caixas são para a comparação da qtde com q qtde de pçs / corte -->
                                                    <input type='hidden' id='hdd_referencia<?=$i;?>' value='<?=$campos[$i]['referencia'];?>'>
                                                    <input type='hidden' id='hdd_discriminacao<?=$i;?>' value='<?=$campos[$i]['discriminacao'];?>'>
                                                    <input type='hidden' id='hdd_familia<?=$i;?>' value='<?=$campos[$i]['id_familia'];?>'>
                                                    <input type='hidden' id='hdd_pecas_corte<?=$i;?>' value='<?=$pecas_corte;?>'>
                                                    <input type='hidden' id='hdd_operacao_custo<?=$i;?>' value='<?=$campos[$i]['operacao_custo'];?>'>
                                                    <input type='hidden' id='hdd_lote_minimo_ignora_faixa_orcavel<?=$i;?>' value='<?=$campos_pecas_corte[0]['lote_minimo'];?>'>
						</td>
                                                <?
                                                    //Busco tudo o que foi faturado do Cliente, p/ o determinado PA sempre nos últimos 4 anos ...
                                                    $sql = "SELECT SUM(nfsi.qtde - nfsi.qtde_devolvida) AS qtde_anual, YEAR(nfs.data_emissao) AS ano 
                                                            FROM `nfs` 
                                                            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' 
                                                            WHERE nfs.`id_cliente` = '".$campos_cliente[0]['id_cliente']."' AND YEAR(nfs.`data_emissao`) >= '".(date('Y') - 4)."' 
                                                            GROUP BY ano, nfsi.id_produto_acabado ";
                                                    $campos_qtde_anual = bancos::sql($sql);
                                                    $linhas_qtde_anual = count($campos_qtde_anual);
                                                    
                                                    for($j = 0; $j < $linhas_qtde_anual; $j++) $vetor_compra_anual[$campos_qtde_anual[$j]['ano']] = $campos_qtde_anual[$j]['qtde_anual'];
                                                    for($ano = (date('Y') - 4); $ano <= date('Y'); $ano++) {
                                                ?>
                                                <td bgcolor='<?=$cor_fundo_linha;?>'>
                                                    <?
                                                        echo number_format($vetor_compra_anual[$ano], 0, ',', '.');
                                                        $total_vendas_do_pa_5_anos += $vetor_compra_anual[$ano];
                                                        unset($vetor_compra_anual[$ano]);
                                                    ?>
						</td>
                                                <?
                                                    }
                                                ?>
                                                <td bgcolor='<?=$cor_fundo_linha;?>'>
                                                    <?
                                                        $diferenca_data = data::diferenca_data(date('Y-01-01'), date('Y-m-d'));
                                                        $qtde_meses_ano_atual = $diferenca_data[0] / 30;
                                                        $sugestao_compra = $total_vendas_do_pa_5_anos / (48 + $qtde_meses_ano_atual) * $top;
                                                        echo number_format($sugestao_compra, 1, ',', '.');
                                                        unset($total_vendas_do_pa_5_anos);
                                                    ?>
                                                </td>
					</tr>
<?
	}
?>
                    <tr class='linhacabecalho' align='center'>
                        <td colspan='15'>
                            <input type='button' name='cmd_incluir' value='Incluir' title='Incluir' onclick='return validar()' style='color:green' class='botao'>
                            <input type='button' name='cmd_incluir_permanecer' value='Incluir e Permanecer' title='Incluir e Permanecer' style='color:black' onclick='document.form.hdd_incluir_permanecer.value=1;return validar()' class='botao'>
                        </td>
                    </tr>
                    <tr align='center'>
                        <td colspan='15'>
                            <?=paginacao::print_paginacao('sim');?>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<?$vetor_lote_minimo_fornecedor = substr($vetor_lote_minimo_fornecedor, 0, strlen($vetor_lote_minimo_fornecedor) - 1);?>
<input type='hidden' name='vetor_lote_minimo_fornecedor' value='<?=$vetor_lote_minimo_fornecedor;?>'>
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<!--Esse hidden serve para controlar o retorno nessa própria tela mesmo assim em que se acaba 
de Incluir os Itens de Orçamento-->
<input type='hidden' name='hdd_incluir_permanecer'>
</body>
</html>
<pre>
<font color="red"><b>O bot&atilde;o Incluir &eacute; o &uacute;nico que vai diretamente p/ a Tela de alterar p/ que seja colocado o Pre&ccedil;o do Produto na mesma hora.</b></font>
<font color="red"><b>Sugest&atilde;o Compra = M&eacute;dia de compra dos ultimos 5 anos * (Top A = 3; Top B = 2; 1).</b></font>
</pre>
<?}?>