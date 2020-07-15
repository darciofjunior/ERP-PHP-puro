<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca de Faturamentos ...
require('../../../../../lib/custos.php');
require('../../../../../lib/data.php');
require('../../../../../lib/estoque_acabado.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/vendas.php');//Essa biblioteca é utilizada dentro da Biblioteca de Custos ...
require('../../../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');

//custos::custo_auto_pi_industrializado();
$mensagem[1] = 'ITEM INCLUIDO COM SUCESSO !';
$mensagem[2] = 'ITEM(NS) ATUALIZADO(S) COM SUCESSO !';
$mensagem[3] = 'ITEM(NS) MANIPULADO(S) COM SUCESSO !';
$mensagem[4] = 'ITEM DESTRAVADO COM SUCESSO !';

//Exclusão dos Itens do Pedido ...
if($passo == 1) {
    //Transforma o parâmetro de itens em vetor
    $vetor_pedido_venda_item = explode(',', $id_pedido_venda_item);
    
    if($mover_para_pendencia == 1) {//1 Significa que sim ...
        for($i = 0; $i < count($vetor_pedido_venda_item); $i++) estoque_acabado::mover_para_pendencia($vetor_pedido_venda_item[$i], $gerar_relatorio);
    }else if($separar == 1) {//1 Significa q sim ...
        for($i = 0; $i < count($vetor_pedido_venda_item); $i++) estoque_acabado::separar_tudo($vetor_pedido_venda_item[$i]);
    }
?>
    <Script Language = 'JavaScript'>
        window.parent.itens.document.location = 'itens.php?id_pedido_venda=<?=$id_pedido_venda;?>&id_cliente=<?=$id_cliente;?>&valor=3'
    </Script>
<?
}else if($passo == 2) {//Passo para destravar o Item selecionado pelo Estoquista
    $sql = "UPDATE `estoques_acabados` SET `status` = '0' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        parent.itens.document.location = 'itens.php?id_pedido_venda=<?=$id_pedido_venda;?>&id_cliente=<?=$id_cliente;?>&valor=4'
    </Script>
<?
}else {
    $cliente_com_credito_devedor    = 0;//Aqui até então, representa que o cliente está em ordem com relação a Empresa ...
    //Aqui é a Busca da Variável de Vendas
    $fator_desc_maximo_venda = genericas::variavel(19);
    if(!empty($id_cliente)) {
        //Busca o nome do Cliente e o id_uf do Cliente ...
        $sql = "SELECT `id_cliente`, IF(`nomefantasia` = '', `razaosocial`, `nomefantasia`) AS cliente, 
                `id_pais`, `id_uf`, `cidade`, `tipo_faturamento` 
                FROM `clientes` 
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    }
    if(!empty($id_pedido_venda)) {
        //Busca o nome do Cliente e o id_uf do Cliente ...
        $sql = "SELECT c.`id_cliente`, IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, 
                c.`id_pais`, c.`id_uf`, c.`cidade`, c.`tipo_faturamento` 
                FROM `pedidos_vendas` pv 
                INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                WHERE pv.id_pedido_venda = '$id_pedido_venda' LIMIT 1 ";
    }
    $campos                     = bancos::sql($sql);
    $id_cliente                 = $campos[0]['id_cliente'];//Aqui pego o id_cliente, por causa do id_pedido
    $cliente                    = $campos[0]['cliente'];
    $retorno_analise_credito    = faturamentos::analise_credito_cliente($id_cliente);//Analisa p/ V c ele tem débito e pode comprar devido seu limite ou crédito
    $credito			= $retorno_analise_credito['credito'];
    $credito_comprometido       = $retorno_analise_credito['credito_comprometido'];
    $tolerancia_cliente		= $retorno_analise_credito['tolerancia_cliente'];
    $id_pais                    = $campos[0]['id_pais'];
    $id_uf_cliente              = $campos[0]['id_uf'];
    $cidade                     = $campos[0]['cidade'];
    $tipo_faturamento		= $campos[0]['tipo_faturamento'];
    
    //Dias à mais à partir de hoje para considerar item como faturável ...
    $dias_a_mais            = genericas::variavel(85);
    $data_atual_mais_dias   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), $dias_a_mais), '-');
    
    //Verifico se o Cliente é do Tipo Nacional / Internacional ...
    $tipo_moeda = ($id_pais != 31) ? 'U$ ' : 'R$ ';
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function carregar_tela() {
    var credito = '<?=$credito;?>'
    if(credito == 'C' || credito == 'D') alert('CLIENTE COM CRÉDITO '+credito+' !\nNÃO É PERMITIDO A EMISSÃO DE NOTA FISCAL PARA ESTE CLIENTE !')
}
    
/*Aqui serve para controlar qual foi o último checkbox selecionado aonde acarretará efeito na Paginação do 
Pop-Up de alterar itens de Pedido*/
function controlar_clique(indice) {
    var elementos = document.form.elements
    var checkbox_selecionados = 0
//Significa que essa tela foi carregada com 1 item ...
    if(typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') {
        if(elementos['chkt_pedido_venda_item[]'].checked == true) {//Se estiver checando o Elemento ...
            indice = 0
            checkbox_selecionados = 1
        }
//Significa que está tela foi carregada com vários itens ...
    }else {
        if(elementos['chkt_pedido_venda_item[]'][indice].checked == true) {//Se estiver checando o elemento ...
            checkbox_selecionados = 1;
        }else {//Se estiver desmarcando, verifico se tem algum outro que está selecionado ...
            var contador = 0;
            for(i = 0; i < elementos.length; i++) {//Ignoro o Primeiro Checkbox e verifico o 1º que está checado ...
                if(elementos[i].name == 'chkt_pedido_venda_item[]') {//Verifico se existe algum checkbox selecionado ...
                    if(elementos[i].checked == true) {
                        indice = contador
                        checkbox_selecionados = 1;
                        break//Faço assim p/ sair do loop ...
                    }
                    contador++
                }
            }
        }
    }
//Se tiver algum checkbox selecionado, então eu atribuo o índice do último checkbox selecionado ...
    if(checkbox_selecionados == 1) {
        document.form.posicao.value = eval(indice) + 1//Somo mais um pq estou ignorando o 1º Checkbox Principal ...
    }else {//Se não tiver nenhum checkbox selecionado, então eu zero a variável posição Zero ... 
        document.form.posicao.value = ''
    }
}

function desbloquear_item(id_produto_acabado) {
    return false;  //retornando falso pq esta função não terá mais funcionalidade depois eu apago esta função
    var resposta = confirm('VOCÊ DESEJA DESBLOQUEAR ESSE ITEM ?')
    if(resposta == true) {//Aqui vai para o passo de desbloquear o Item
        window.location = 'itens.php?passo=2&id_pedido_venda=<?=$id_pedido_venda;?>&id_cliente=<?=$id_cliente;?>&id_produto_acabado='+id_produto_acabado
    }else {
        return false
    }
}

function prosseguir(credito, cliente_com_credito_devedor, qtde_nf_abertas, valor_total_itens_faturar, id_pedido_venda) {
//Se o Crédito do Cliente for C, D ou B e o Crédito Comprometido for Maior do que Zero então ...
    if(credito == 'C' || credito == 'D' || (credito == 'B' && cliente_com_credito_devedor == 1)) {
        if(credito == 'C' || credito == 'D') alert('CLIENTE COM CRÉDITO '+credito+' !\nNÃO É PERMITIDO A EMISSÃO DE NOTA FISCAL PARA ESTE CLIENTE !')
        nova_janela('../../../../financeiro/cadastro/credito_cliente/enviar_email_solic_credito.php?id_cliente=<?=$id_cliente;?>&valor_total_itens_faturar='+valor_total_itens_faturar, 'ENVIAR_EMAIL_SOLIC_CRED', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {
        if(qtde_nf_abertas > 0) {//O Cliente possui alguma NF em aberto
            alert('ESTE CLIENTE POSSUI '+qtde_nf_abertas+' NF(S) EM ABERTO(S) E QUE ESTÁ(ÃO) SEM ITEM(NS) !\nOBS: NÃO TEM COMO INFORMAR O N.º DA(S) NF(S), PORQUE NEM TODA(S) POSSUEM N.º !')
        }else {//O Cliente não possui nenhuma NF em aberto, portanto pode continuar
            parent.location = '../../../../faturamento/nota_saida/incluir.php?passo=1&id_pedido_venda='+id_pedido_venda
        }
    }
}
</Script>
</head>
<body onload='carregar_tela()'>
<form name='form' action='' method='post'>
<?
    if(!empty($id_cliente)) {
        $sql = "SELECT pvi.`id_representante`, pvi.`id_oe`, 
                (pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale` - pvi.`qtde_faturada`) AS separada, 
                pvi.`qtde_faturada`, pvi.`margem_lucro`, pvi.`margem_lucro_estimada`, 
                ovi.`id_orcamento_venda_item`, ovi.`id_orcamento_venda`, 
                ovi.`id_produto_acabado_discriminacao`, ovi.`queima_estoque`, pv.`id_pedido_venda`, pv.`id_empresa`, 
                pv.`num_seu_pedido`, pv.`faturar_em`, pv.`condicao_faturamento`, pv.`data_emissao`, pv.`vencimento1`, pv.`vencimento2`, 
                pv.`vencimento3`, pv.`vencimento4`, pv.`prazo_medio`, pv.`expresso`, pv.`livre_debito`, pv.`liberado`, 
                pvi.`id_pedido_venda_item`, pvi.`preco_liq_final`, pvi.`status_estoque`, pvi.`qtde`, 
                pvi.`qtde_pendente`, pvi.`vale`, pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, 
                pa.`discriminacao`, pa.`peso_unitario`, pa.`pecas_por_jogo`, pa.`mmv`, pa.`observacao`, 
                c.`id_cliente`, c.`razaosocial` 
                FROM `clientes` c 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` AND pv.`status` < '2' 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                WHERE c.`id_cliente` = '$id_cliente' ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` "; //nao pode tirar o pvi.id_pedido_venda_item, pois da erro de indexação
    }
    if(!empty($id_pedido_venda)) {
        /*Seleção de Todos os Pedidos Pendentes que contém o mesmo Item de Produto em aberto e somente dos 
        itens que não estejam faturados totalmente <- 'pvi.status < 2'*/
        $sql = "SELECT pvi.`id_representante`, pvi.`id_oe`, 
                (pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale` - pvi.`qtde_faturada`) AS separada, 
                pvi.`qtde_faturada`, pvi.`margem_lucro`, pvi.`margem_lucro_estimada`, 
                ovi.`id_orcamento_venda_item`, ovi.`id_orcamento_venda`, ovi.`id_produto_acabado_discriminacao`, 
                ovi.`queima_estoque`, pv.`id_pedido_venda`, pv.`num_seu_pedido`, pv.`liberado`, 
                pv.`faturar_em`, pv.`data_emissao`, pv.`condicao_faturamento`, 
                pv.`id_empresa`, pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, 
                pv.`prazo_medio`, pv.`livre_debito`, pv.`expresso`, pvi.`id_pedido_venda_item`, 
                pvi.`preco_liq_final`, pvi.`status_estoque`, pvi.`qtde`, 
                pvi.`qtde_pendente`, pvi.`vale`, pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, 
                pa.`discriminacao`, pa.`peso_unitario`, pa.`pecas_por_jogo`, pa.`mmv`, pa.`observacao`, 
                c.`id_cliente`, c.`razaosocial` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' 
                AND pvi.`status` < '2' ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` "; //nao pode tirar o pvi.id_pedido_venda_item, pois da erro de indexação
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    //Verifica se tem pelo menos um item no pedido
    if($linhas > 0) {
        /***************************Aqui eu pego o Total a Faturar de Todas as Empresas, de todos os Pedidos do Determinado Cliente***************************/
        for ($i = 0;  $i < $linhas; $i++) {
            if($campos[$i]['faturar_em'] <= $data_atual_mais_dias) {
                $resultado  = ($campos[$i]['qtde_pendente'] >= $vetor[3]) ? $vetor[3] : $campos[$i]['qtde_pendente'];
                $valor_total_itens_faturar+= ($campos[$i]['separada'] + $campos[$i]['vale'] + $resultado) * ($campos[$i]['preco_liq_final']);
            }
        }
        if(isset($valor_total_itens_faturar)) {
            $icms_st_ipi_perc = 20;//Aqui estamos estimando que esses impostos em uma NF dariam aí no máximo 20% ...
            $valor_total_itens_faturar*= (1 + $icms_st_ipi_perc / 100);
        }
        /*Não posso incluir uma nova NF p/ o Cliente, caso este esteja com o Saldo devedor 
        a partir de Crédito B ...*/
        if($credito != 'A' && ($credito_comprometido + $valor_total_itens_faturar) > $tolerancia_cliente) $cliente_com_credito_devedor = 1;
        /*****************************************************************************************************************************************************/
    ///////////////////////////////////////////////////////////////////////////////////////////
    //Aki verifico todas as NFs daquele cliente contém pelo menos 1 item, e q não sejam canceladas
        $sql = "SELECT DISTINCT(nfs.`id_nf`) 
                FROM `nfs` 
                RIGHT JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                WHERE nfs.`id_cliente` = '$id_cliente' 
                AND nfs.`status` < '5' ";
        $campos2 = bancos::sql($sql);
        $linhas2 = count($campos2);
//Dispara outro For
        $id_nfs = '';
        for($j = 0; $j < $linhas2; $j++) $id_nfs.= $campos2[$j]['id_nf'].', ';
        //Esse macete é para forçar a entrar no sql da linha 211
        if(strlen($id_nfs) == 0) $id_nfs = '0, ';
        $id_nfs = substr($id_nfs, 0, strlen($id_nfs) - 2);
        //Se foi encontrado mais de 2 notas, caso isso vai refletir na condição do Sql mais abaixo
        if(strpos($id_nfs, ',') > 0) {//Se existir vírgula, então significa q tem no mínimo 2 notas
            $tipo_comparacao = ' NOT IN ('.$id_nfs.') ';
        }else {
            $tipo_comparacao = ' <> '.$id_nfs;
        }
//Aki verifico todas as NFs daquele cliente q não tenham nenhum item e q estejam numa situação < q faturadas
        $sql = "SELECT `id_nf_num_nota` 
                FROM `nfs` 
                WHERE `id_cliente` = '$id_cliente' 
                AND `status` <= '2' 
                AND `id_nf` $tipo_comparacao ORDER BY `id_nf` LIMIT 5 ";
        $campos_nf_abertas  = bancos::sql($sql);
        $qtde_nf_abertas    = count($campos_nf_abertas);
?>
<table width='90%' border='1' align='center' cellspacing='0' cellpadding='0' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            <a href="javascript:nova_janela('../../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$id_cliente;?>&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')"> 
                <font color='yellow' size='-1'>
                    Cliente:
                    <font color='#FFFFFF' size='-1'>
                    <?
                        echo $cliente.' / '.$cidade;
                        
                        if($id_uf_cliente > 0) {
                            $sql = "SELECT `sigla` 
                                    FROM `ufs`
                                    WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
                            $campos_uf = bancos::sql($sql);
                            echo ' / '.$campos_uf[0]['sigla'];
                        }else {
                            $sql = "SELECT `pais` 
                                    FROM `paises` 
                                    WHERE `id_pais` = '$id_pais' LIMIT 1 ";
                            $campos_pais = bancos::sql($sql);
                            echo ' / '.$campos_pais[0]['pais'];
                        }
                    ?>
                    </font>
                </font>
                <img src = "../../../../../imagem/propriedades.png" title="Detalhes de Cliente" alt="Detalhes de Cliente" style="cursor:pointer" border="0">
            </a>
            &nbsp;-&nbsp;
            <font color='yellow' size='-1'>
                Crédito:
                <font color='#FFFFFF' size='-1'>
                    <?=$credito;?>
                </font>
            </font>
            &nbsp;-&nbsp;
            <font color='yellow' size='-1'>
                Tipo de Faturamento:
                <font color='#FFFFFF' size='-1'>
                <?
                    if($tipo_faturamento == 1) {//Significa que o Cliente fatura tudo pela Albafér ...
                        echo 'TUDO PELA ALBAFER';
                    }else if($tipo_faturamento == 2) {//Significa que o Cliente fatura tudo pela Tool Master ...
                        echo 'TUDO PELA TOOL MASTER';
                    }else if($tipo_faturamento == 'Q') {//Significa que o Cliente fatura por Ambas Empresas - Indiferente ...
                        echo 'QUALQUER EMPRESA';
                    }else if($tipo_faturamento == 'S') {//Significa que o Cliente fatura por Ambas Empresas - apenas itens da empresa escolhida ...
                        echo 'SEPARADAMENTE';
                    }
                ?>
                </font>
            </font>
            -
            <a href="javascript:nova_janela('../../../../classes/pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$id_cliente;?>', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Relatório de Pendências' class='link'>
                <font color='#48FF73' size='-1'>
                    Pendências
                </font>
            </a>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='17'>
            <iframe name='detalhes' id='detalhes' src = '../../../../classes/follow_ups/detalhes.php?id_cliente=<?=$id_cliente;?>&origem=3' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
<?
/*******************************************************************************************/
/*Aqui eu listo todas as NFs do cliente que ainda estão aqui na Empresa, nas situações de 
Em aberto, Liberadas p/ Faturar, Faturadas, Empacotas e Despachadas na Portaria ...*/	
        $sql = "SELECT c.`nomefantasia`, c.`razaosocial`, c.`credito`, nfs.`id_nf`, nfs.`id_empresa`, nfs.`finalidade`, nfs.`frete_transporte`, 
                nfs.`data_emissao`, nfs.`vencimento1`, nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, nfs.`prazo_medio`, 
                nfs.`status`, nfs.`livre_debito`, nfs.`tipo_despacho`, t.`nome` AS transportadora 
                FROM `nfs` 
                INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND c.`ativo` = '1' 
                WHERE nfs.`id_cliente` = '$id_cliente' 
                AND nfs.`ativo` = '1' 
                AND (nfs.`status` IN (0, 1, 2, 3) OR (nfs.`status` = '4' AND nfs.`tipo_despacho` = '1')) GROUP BY nfs.`id_nf` ORDER BY nfs.`id_nf` DESC ";
        $campos_nfs = bancos::sql($sql);
        $linhas_nfs = count($campos_nfs);
        if($linhas_nfs > 0) {
?>
</table>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr>
        <td></td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            <font color='#FFFFFF' size='-1'>
                Total de Nota(s) Fiscal(is): 
                <font color='yellow'>
                    <b><?=$linhas_nfs;?></b>
                </font>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2' bgcolor='#CECECE'>
            <b>N.&ordm; Nota Fiscal</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data Em.</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Transportadora</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Status da NF</b>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Empresa / Finalidade / Frete / Prazo Médio / LD' style='cursor:help'>
                <b>Emp / Finalidade <br/>Frete / Pz Médio / LD</b>
            </font>
        </td>
    </tr>
<?
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
            $vetor_nf               = array_sistema::nota_fiscal();
            $vetor_tipos_despacho   = faturamentos::tipos_despacho();
            for ($i = 0;  $i < $linhas_nfs; $i++) {
                $url = "javascript:nova_janela('../../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=".$campos_nfs[$i]['id_nf']."', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <a href="#" class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>" width='80'>
            <a href="#" class='link'>
                <?=faturamentos::buscar_numero_nf($campos_nfs[$i]['id_nf'], 'S');?>
            </a>
        </td>
        <td>
        <?
            if($campos_nfs[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos_nfs[$i]['data_emissao'], '/');
        ?>
        </td>
        <td>
            <?=$campos_nfs[$i]['transportadora'];?>
        </td>
        <td align='left'>
        <?
            echo $vetor_nf[$campos_nfs[$i]['status']];
            if($campos_nfs[$i]['status'] == 0) {//Se a NF estiver em Aberto ...
//Se o cadastro do Cliente estiver inválido, então este tem que ser corrigido, antes de qualquer outra coisa
                $cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($id_cliente);
                if(cadastro_cliente_incompleto == 1) {//Está incompleto
                    $onclick = "alert('O CADASTRO DESTE CLIENTE ESTÁ INCOMPLETO !\nCORRIJA O MESMO PARA CONTINUAR COM ESTE PROCEDIMENTO NORMALMENTE !')";
                }else {//Está tudo OK
/*Menu 1, representa o Menu Abertas / Liberadas, gerenciar = 1, significa que essa Tela
foi acessada de dentro da opção do menu de Gerenciar ...*/
                    $onclick = "nova_janela('../../../../faturamento/nota_saida/itens/incluir.php?id_nf=".$campos_nfs[$i]['id_nf']."&seguranca=1&gerenciar=1', 'INCLUIR_ITENS', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')";
                }
        ?>
                &nbsp;
                <img src = '../../../../../imagem/menu/incluir.png' border='0' title='Incluir Item(ns)' alt='Incluir Item(ns)' onclick="<?=$onclick;?>">
        <?
            }else if($campos_nfs[$i]['status'] == 4) {
                echo ' ('.$vetor_tipos_despacho[$campos[$i]['tipo_despacho']].')';
            }
        ?>
        </td>
        <td align='left'>
        <?
            echo '<font title="'.genericas::nome_empresa($campos_nfs[$i]['id_empresa']).'" style="cursor:help">'.substr(genericas::nome_empresa($campos_nfs[$i]['id_empresa']), 0, 1).'</font> / ';
            
            if($campos[$i]['finalidade'] == 'C') {
                echo '<font title="Consumo" style="cursor:help">C</font> / ';
            }else if($campos[$i]['finalidade'] == 'I') {
                echo '<font title="Industrialização" style="cursor:help">I</font> / ';
            }else {
                echo '<font title="Revenda" style="cursor:help">R</font> / ';
            }

            if($campos[$i]['frete_transporte'] == 'C') {
                echo 'CIF / ';
            }else {
                echo 'FOB / ';
            }

            echo '<font title="Vencimentos: '.$campos[$i]['vencimento1'].'" style="cursor:help">'.$campos[$i]['prazo_medio'].'</font>';

            if($campos[$i]['livre_debito'] == 'S') echo '<font style="Livre de Débito" cursor="help"> / LD</font>';
        
//Aqui verifica se a NF contém pelo menos 1 item
            $sql = "SELECT `id_nfs_item` 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = ".$campos_nfs[$i]['id_nf']." LIMIT 1 ";
            $campos_nfs_item    = bancos::sql($sql);
            $qtde_itens_nf      = count($campos_nfs_item);
            if($qtde_itens_nf == 0) echo ' <font color="red">(S/ ITENS)</font>';
        ?>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            &nbsp;
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
</table>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
<?
        }
/**************************************Obs dos Pedidos**************************************/
        $id_pedido_venda_antigo = '';//Variável para controle das Observações dos Pedidos ...
    //Aqui eu só exibo as observações dos Pedidos ...
        for($i = 0;  $i < $linhas; $i++) {
            if($id_pedido_venda_antigo != $campos[$i]['id_pedido_venda']) {
    //Aki significa que mudou para outro N. de Pedido e vai exibir uma nova sequência desses mesmos
                $id_pedido_venda_antigo = $campos[$i]['id_pedido_venda'];

                //Aqui eu busco a observação do Follow-UP deste Pedido que possui a marcação Exibir no PDF ...
                $sql = "SELECT `observacao` 
                        FROM `follow_ups` 
                        WHERE `identificacao` = '$id_pedido_venda_antigo' 
                        AND `origem` = '2' 
                        AND `exibir_no_pdf` = 'S' LIMIT 1 ";
                $campos_follow_up   = bancos::sql($sql);
                if(count($campos_follow_up) == 1) {
?>
    <tr class='linhanormal'>
        <td colspan='17'>
            <img src = '../../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
            <font size='3'>
                <b>Obs. Ped. (<?=$id_pedido_venda_antigo;?>): 
                <font color='red'>
                    <?=$campos_follow_up[0]['observacao'];?>
                </font>
                </b>
            </font>
            <img src = '../../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
        </td>
    </tr>
<?
                }
            }
        }
        /**********************************************************************/
        /*************************Observação do Cliente************************/
        /**********************************************************************/
        //Busco a observação do Cliente que foi registrada no Follow-Up ...
        $sql = "SELECT `observacao` 
                FROM `follow_ups` 
                WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
                AND `origem` = '15' LIMIT 1 ";
        $campos_follow_up = bancos::sql($sql);
        if(count($campos_follow_up) == 1) {
?>
        <tr class='linhanormal' align='center'>
            <td colspan='17' bgcolor='yellow'>
                <img src = '../../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
                <font size='3'>
                    <b>Obs. do Cliente: 
                    <font color='blue'>
                        <?=$campos_follow_up[0]['observacao'];?>
                    </font>
                    </b>
                </font>
                <img src = '../../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
            </td>
        </tr>
<?
        }
        /**********************************************************************/
/*************************************Itens dos Pedidos*************************************/
?>
</table>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CECECE'>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Produto' style='cursor:help'>
                <b>Produto</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Peso Unitário</b>
        </td>
        <td colspan='5' bgcolor='#CECECE'>
            <font title='Dados do Pedido' color='#0000FF' style='cursor:help'>
                <b>Dados do Pedido </b>
            </font>
        </td>
        <td colspan='4' bgcolor='#CECECE'>
            <font color='green'>
                <b>Dados do P.A.</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Empresa / Finalidade / Frete / Prazo Médio' style='cursor:help'>
                <b>Emp / Finalidade <br/>Frete / Pz Médio / LD</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>
                <font title='Data de Emissão' style='cursor:help'>
                    Data Emis
                </font>
                <p/>
                <font title='Faturar em' style='cursor:help'>
                    Fat. em
                </font>
            </b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Representante / Condição de Faturamento' style='cursor:help'>
                <b>Rep / Cond. Fat</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='N.º Nosso Pedido / N.º Seu Pedido' style='cursor:help'>
                <b>
                    N.º N/ Pedido<br/>
                    N.º S/ Pedido
                </b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Preço Líquido Final <?=$tipo_moeda;?>' style='cursor:help'>
                <b>P. L.<br>Final <?=$tipo_moeda;?></b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Valor Total <?=$tipo_moeda;?>' style='cursor:help'>
                <b>Vlr Tot <?=$tipo_moeda;?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <font color='#0000FF' title='Quantidade Pedida' style='cursor:help'>
                <b>Ped.</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font color='#0000FF' title='Quantidade Faturada' style='cursor:help'>
                <b>Fat.</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font color='#0000FF' title='Quantidade Separada' style='cursor:help'>
                <b>Sep.</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font color='#0000FF' title='Quantidade Pendente' style='cursor:help'>
                <b>Pend.</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font color='#0000FF' title='Vale' style='cursor:help'>
                <b>Vale</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font color='green' title='Média Mensal de Vendas' style='cursor:help'>
                <b>MMV</b>
            </font>
            <br>
            <font color='green' title='Estoque p/ x Meses' style='cursor:help'>
                <b>Qt. Meses</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font color='green' title='Estoque Disponível / Estoque Real / Estoque Comprometido' style='cursor:help'>
                <b>ED/ER/EC</b>
            </font>
            <font color='green' title='Estoque Disponível Atrelado / Estoque Real Atrelado / Estoque Comprometido Atrelado' style='cursor:help'>
                <br/><b>E.D. Atrel/E.R. Atrel/E.C. Atrel</b>
            </font>
            <!--<font color='green' title='Estoque do Fornecedor / Estoque do Porto' style='cursor:help'>
                <br/><b>E Forn/E Porto</b>
            </font>-->
            <font color='green' title='Entrada Antecipada' style='cursor:help'>
                <br/><b>E.Ant.</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font color='green'>
                <b>Compra<br/>Produ&ccedil;&atilde;o</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font color='green' title='Prazo de Entrega' style='cursor:help'>
                <b>Prazo de <br>Entrega</b>
            </font>
        </td>
    </tr>
<?
        $vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();
        $id_pedido_venda_antigo                 = '';//Variável para controle das cores no Pedido
        $id_empresa_corrente                    = $campos[0]['id_empresa'];
        
        for($i = 0;  $i < $linhas; $i++) {
            $id_produto_acabado             = $campos[$i]['id_produto_acabado'];
            
            $estoque_produto                = estoque_acabado::qtde_estoque($id_produto_acabado);
            $qtde_estoque                   = $estoque_produto[0];
            $qtde_producao                  = $estoque_produto[2];
            $qtde_disponivel                = $estoque_produto[3];
            $racionado                      = $estoque_produto[5];
            $est_comprometido               = $estoque_produto[8];
            $qtde_pa_possui_item_faltante   = $estoque_produto[9];
            $est_fornecedor                 = $estoque_produto[12];
            $est_porto                      = $estoque_produto[13];
            $entrada_antecipada             = $estoque_produto[15];

            $compra                 = estoque_acabado::compra_producao($id_produto_acabado);
            $qtde_oe_em_aberto      = ($qtde_producao > 0) ? '<br><font color="purple"><b>(OE='.number_format($estoque_produto[11], 0, '', '.').')</b></font>' : '';
            $compra_producao        = number_format($qtde_producao + $compra, 2, ',', '.');

            $pedido_liberado        = ($campos[$i]['liberado'] == 0) ? 'N' : 'S';
?>
    <tr class='linhanormal' onclick="checkbox('<?=$i;?>', '#E8E8E8');controlar_clique('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_pedido_venda_item[]' id='chkt_pedido_venda_item<?=$i;?>' value='<?=$campos[$i]['id_pedido_venda_item'];?>' onclick="checkbox('<?=$i;?>', '#E8E8E8')" class='checkbox'>
            <!--************Esse campo será utilizado quando o usuário tentar Mandar no Vale ...************-->
            <input type='hidden' name='hdd_pedido_liberado[]' id='hdd_pedido_liberado<?=$i;?>' value='<?=$pedido_liberado;?>'>
            <?
                //Verifico se o id_pedido_venda_item está em algum Packing List ...
                $sql = "SELECT `qtde` 
                        FROM `packings_lists_itens` 
                        WHERE `id_pedido_venda_item` = '".$campos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
                $campos_packing_list_item = bancos::sql($sql);
                if(count($campos_packing_list_item) == 1) {
                    $esta_em_packing_list = 'S';
                    echo '<font color="red" title="Packing List" style="cursor:help"><b>(PL '.$campos_packing_list_item[0]['qtde'].')</b></font>';
                }else {
                    $esta_em_packing_list = 'N';
                }
        ?>
            <input type='hidden' name='hdd_esta_em_packing_list' id='hdd_esta_em_packing_list<?=$i;?>' value='<?=$esta_em_packing_list;?>'>
            <br/>
        <?
            $tx_financeira          = custos::calculo_taxa_financeira($campos[$i]['id_orcamento_venda']);
            $margem                 = custos::margem_lucro($campos[$i]['id_orcamento_venda_item'], $tx_financeira, $campos[$i]['id_uf'], $campos[$i]['preco_liq_final']);

            $cor_instantanea        = ($margem[0] < 0) ? 'red' : '#E8E8E8';//$margem[0] valor real da margem
            $cor_gravada            = ($campos[$i]['margem_lucro'] < 0) ? 'red': '#E8E8E8';
            $cor_estimada           = ($campos[$i]['margem_lucro_estimada'] < 0) ? 'red': '#E8E8E8';
            
            $fator_margem_lucro = genericas::variavel(22);
            $custo_bancario     = genericas::variavel(66);
            $fator_ml_min_crise = genericas::variavel(74);

            //Zero essas variáveis p/ não herdar Valores do Loop Anterior ...
            $custo_ml_zero      = 0;
            $custo_ml_zero_nac  = 0;
            $custo_ml_zero_inter= 0;

            /*Se os Pedidos tiverem sua Data de Emissão menor que esta Data 10/11/2015, sigo esse 
            caminho abaixo porque foi a partir dessa Data que aumentamos os preços dos Bits 
            TM e UL ...*/
            if($campos[$i]['data_emissao'] < '2015-11-10') {
                $valores_preco_venda_media = vendas::calculo_preco_venda_medio_nf_sp_30ddl_rs($campos[$i]['id_pedido_venda_item'], 'PVI');

                if($campos[$i]['operacao_custo'] == 0) {//Industrial, trago o Preço de Outra forma ...
                    /*Nesse caso chamei a função "todas_etapas" sem fazer a Soma da Etapa 1 porque ??? 

                    Se eu passo o parâmetro para somar a Etapa 1 que é a Embalagem nessa função, então 
                    o que acontece é que essa função não só apenas soma a Etapa1, mas além disso 
                    é adicionado o cálculo de Taxa de Estocagem que não nos interessa nesse momento 
                    porque são Produtos que a empresa não irá estocar e estamos pegando o Preço 
                    sem Custo Bancário também, utilizamos 30% porque consideramos uma Margem 
                    de Lucro Mínima mesmo nessa fase de Crise do País ...*/
                    $total_indust           = custos::todas_etapas($campos[$i]['id_produto_acabado'], $campos[$i]['operacao_custo'], 0);
                    $fator_custo_etapa_1_3  = genericas::variavel(12);
                    $etapa1                 = custos::etapa1($id_produto_acabado, $fator_custo_etapa_1_3);

                    $custo_ml_zero          = ($etapa1 + $total_indust) / $fator_margem_lucro;
                }else {//Revenda ...
                    $valores                = custos::preco_custo_pa($campos[$i]['id_produto_acabado'], '', 'S');

                    /*Nesse caso eu trago o "preco_venda_fat_nac_min_rs" do Custo que já está incluso o 
                    Custo bancário e com a fórmula abaixo, estou desembutindo esse Custo Bancário ...*/
                    if($valores['preco_venda_fat_nac_min_rs'] == 0) {
                        $custo_ml_zero = $valores['preco_venda_fat_inter_min_rs'] / (1 + $custo_bancario / 100) / $fator_margem_lucro;
                    }else {
                        if($valores['preco_venda_fat_inter_min_rs'] == 0) {
                            $custo_ml_zero = $valores['preco_venda_fat_nac_min_rs'] / (1 + $custo_bancario / 100) / $fator_margem_lucro;
                        }else {
                            /*Nesse caminho em específico o Fornecedor trabalha com 2 preços 
                            Nacional e Exportação, por isso dessa comparação com ambos (deve servir só 
                            pra Hispania ???) ...*/
                            $custo_ml_zero_nac    = $valores['preco_venda_fat_nac_min_rs'] / (1 + $custo_bancario / 100) / $fator_margem_lucro;
                            $custo_ml_zero_inter  = $valores['preco_venda_fat_inter_min_rs'] / (1 + $custo_bancario / 100) / $fator_margem_lucro;
                        }
                    }
                }

                if($custo_ml_zero_nac > 0 && $custo_ml_zero_inter > 0) {
                    $fator_ml_nac   = $valores_preco_venda_media['preco_NF_SP_30_ddl'] / $custo_ml_zero_nac;
                    $ml_nac         = ($fator_ml_nac - 1) * 100;

                    $fator_ml_inter = $valores_preco_venda_media['preco_NF_SP_30_ddl'] / $custo_ml_zero_inter;
                    $ml_inter       = ($fator_ml_inter - 1) * 100;

                    if($fator_ml_nac >= $fator_ml_min_crise) {
                        echo '<font color="darkgreen" title="ML= '.number_format($ml_nac, 1, ',', '.').' %" style="cursor:help"><b>OK NAC</b></font><br/>';
                    }else {
                        echo '<font color="darkred" title="ML= '.number_format($ml_nac, 1, ',', '.').' %" style="cursor:help"><b>Fora ML Crise NAC</b></font><br/>';
                    }
                    if($fator_ml_inter >= $fator_ml_min_crise) {
                        echo '<font color="darkgreen" title="ML= '.number_format($ml_inter, 1, ',', '.').' %" style="cursor:help"><b>OK EXP</b></font>';
                    }else {
                        echo '<font color="darkred" title="ML= '.number_format($ml_inter, 1, ',', '.').' %" style="cursor:help"><b>Fora ML Crise EXP</b></font>';
                    }
                }else {
                    $fator_ml   = $valores_preco_venda_media['preco_NF_SP_30_ddl'] / $custo_ml_zero;
                    $ml         = ($fator_ml - 1) * 100;

                    if($fator_ml >= $fator_ml_min_crise) {
                        echo '<font color="darkgreen" title="ML= '.number_format($ml, 1, ',', '.').' %" style="cursor:help"><b>OK</b></font><br/>';
                    }else {
                        echo '<font color="darkred" title="ML= '.number_format($ml, 1, ',', '.').' %" style="cursor:help"><b>Fora ML Crise</b></font>';
                    }
                }
            }else {//Maior ou igual à 10/11/2015 ...
                $ml_instantanea = str_replace(',', '.', $margem[1]);
                $ml_min_crise   = ($fator_ml_min_crise - 1) * 100;

                if($ml_instantanea >= $ml_min_crise) {
                    echo '<font color="darkgreen" title="DT.Emis= '.data::datetodata($campos[$i]['data_emissao'], '/').' - ML= '.number_format($ml_instantanea, 1, ',', '.').' %" style="cursor:help">
                            <img src = "../../../../../imagem/bloco_negro.gif" width="8" height="8" border="0">
                          <b>OK</b></font><br/>';
                }else {
                    echo '<font color="darkred" title="DT.Emis= '.data::datetodata($campos[$i]['data_emissao'], '/').' - ML= '.number_format($ml_instantanea, 1, ',', '.').' %" style="cursor:help">
                            <img src = "../../../../../imagem/bloco_negro.gif" width="8" height="8" border="0">
                          <b>Fora ML Crise</b></font><br/>';
                }
            }
        ?>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <a href="javascript:nova_janela('../../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Estoque" class='link'>
                    <?=intermodular::pa_discriminacao($id_produto_acabado, 0, '', '', $campos[$i]['id_produto_acabado_discriminacao']);?>
                </a>
                &nbsp;
                <a href="javascript:nova_janela('../../../../vendas/orcamentos/itens/ultima_venda_cliente.php?id_orcamento_venda_item=<?=$campos[$i]['id_orcamento_venda_item'];?>', 'ULTIMA_VENDA', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes da Última Venda' class='link'>
                    <img src = '../../../../../imagem/detalhes_ultima_venda.png' title='Detalhes da Última Venda' alt='Detalhes da Última Venda' width='30' height='22' border='0'>
                </a>
                <?
                    if($campos[$i]['queima_estoque'] == 'S') echo '&nbsp;<img src="../../../../../imagem/queima_estoque.png" title="Excesso de Estoque" alt="Excesso de Estoque" border="0">';
                ?>
            </font>
        <?
//Verifico se o PA está racionado
            $sql = "SELECT `racionado` 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_racionado = bancos::sql($sql);
            if(count($campos_racionado) == 1) {
                if($campos_racionado[0]['racionado'] == 1) {
        ?>
                &nbsp;-&nbsp;
                <font color='red' title="Racionado" style='cursor:help'>
                    <b>(R)</b>
                </font>
        <?
                }
            }

            if($campos[$i]['id_oe'] > 0) echo '<font color="purple"><b>(OE)</b></font>';
        ?>
            &nbsp;
            <a href = "javascript:nova_janela('../../../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1', 'POP', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <img src = '../../../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <a href = "javascript:nova_janela('../../../../vendas/relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1', 'POP', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <img src = '../../../../../imagem/propriedades.png' title='Visualizar Orçamentos - Últimos 6 meses' alt='Visualizar Orçamentos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <img src = '../../../../../imagem/carrinho_compras.png' border='0' title='Compra + Produção' alt='Compra + Produção' width='25' height='16' onclick="nova_janela('../../../../classes/producao/visualizar_compra_producao.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'POP', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;
            <?
                $url = '../../../../vendas/estoque_acabado/manipular_estoque/consultar.php?passo=1';
                /*Mudança feita em 17/05/2016 - Antigamente os detalhes da consulta só eram feitos pela 
                referência independente de ser normal de Linha, eu supus que fosse assim porque temos PA(s) 
                que são similares em seu cadastro na parte de referência, por exemplo ML: 
                ML-001, ML-001A, ML-001AS, ML-001D, ML-001S, ML-001T, ML-001U, mas para ESP fica inviável 
                vindo todos os ESP´s do Sistema e trazendo informações que não tinham nada haver ...*/
                if($campos[$i]['referencia'] == 'ESP') {//Aqui quero ver detalhes do PA ESP em específico ...
                    $url.= '&id_produto_acabado='.$campos[$i]['id_produto_acabado'].'&pop_up=1';
                }else {//PA normal de Linha, quero ver detalhes de todos os PA(s) semelhantes a este da Referência ...
                    $url.= '&txt_referencia='.$campos[$i]['referencia'].'&pop_up=1';
                }
            ?>
            <img src = '../../../../../imagem/baixas_manipulacoes.png' border='0' title='Baixas / Manipulações' alt='Baixas / Manipulações' width='22' height='20' onclick="nova_janela('<?=$url;?>', 'POP', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;
            <img src = '../../../../../imagem/ferramenta.png' border='0' title='Substituir Estoque' style='cursor:pointer' onclick="nova_janela('../../../../classes/produtos_acabados/substituir_estoque_pa.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'POP', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;
            <img src = '../../../../../imagem/estornar_entrada_antecipada.png' border='0' title='Retorno de Entrada Antecipada' alt='Retorno de Entrada Antecipada' width='22' height='20' onclick="nova_janela('../../../../../modulo/vendas/estoque_acabado/retorno_entrada_antecipada.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'RETORNO_ENTRADA_ANTECIPADA', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;
            <img src = '../../../../../imagem/caneta.png' border='0' title='Incluir Inventário' alt='Incluir Inventário' width='22' height='20' onclick="nova_janela('../../../../../modulo/vendas/estoque_acabado/inventario/incluir.php?id_produto_acabado=<?=$id_produto_acabado;?>&pop_up=1', 'INCLUIR_INVENTARIO', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')">
        </td>
        <td>
            <!--Esses parâmetros tela1 serve para o pop-up fazer a atualização na tela de baixo-->
            <a href="javascript:nova_janela('../../../../classes/produtos_acabados/alterar_peso_unitario.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&tela1=window.opener', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Atualizar Peso do Produto' class='link'>
                <?=number_format($campos[$i]['peso_unitario'], 8, ',', '.');?>
            </a>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=segurancas::number_format($campos[$i]['qtde'], 1, '.');?>
            </font>
        </td>
        <td>
        <?
            //Só aparecerá o Link do que já foi faturado, se tiver pelo menos 1 item q já está em NF
            if($campos[$i]['qtde_faturada'] > 0) {
        ?>
                <a href="javascript:nova_janela('../../../../classes/faturamento/faturado.php?id_pedido_venda_item=<?=$campos[$i]['id_pedido_venda_item'];?>', 'FATURADO', '', '', '', '', 350, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Faturamento' class='link'>
                    <?=segurancas::number_format($campos[$i]['qtde_faturada'], 1, '.');?>
                </a>
        <?
            }else {
                echo segurancas::number_format($campos[$i]['qtde_faturada'], 1, '.');
            }
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['separada'], 1, '.');?>
        </td>
        <td>
        <?
            if($campos[$i]['qtde_pendente'] > ($qtde_disponivel + $qtde_oe_em_aberto)) {
                echo '<font color="red" title="Qtde Pendente > (Qtde Disponível + Qtde de OE em Aberto)" style="cursor:help"><b>'.segurancas::number_format($campos[$i]['qtde_pendente'], 0, '.').'</b></font>';
            }else {
                echo segurancas::number_format($campos[$i]['qtde_pendente'], 1, '.');
            }
        ?>
        </td>
        <td>
        <?
            //Aqui eu verifico se tem existe algum histórico do "Vale de Venda" que foi enviado ...
            $sql = "SELECT `id_vale_venda_item` 
                    FROM `vales_vendas_itens` 
                    WHERE `id_pedido_venda_item` = '".$campos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
            $campos_vale_venda_item = bancos::sql($sql);
            if(count($campos_vale_venda_item) == 1) {//Existe Histórico ...
        ?>
            <a href="javascript:nova_janela('detalhes_vales_vendas.php?id_pedido_venda_item=<?=$campos[$i]['id_pedido_venda_item'];?>', 'DETALHES_VALES_VENDAS', '', '', '', '', 380, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Vales de Vendas' class='link'>
                <?=number_format($campos[$i]['vale'], 1, ',', '.');?>
            </a>
        <?
            }else {//Não existe histórico ...
                echo segurancas::number_format($campos[$i]['vale'], 1, '.');
            }
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($campos[$i]['mmv'], 2, '.');
        ?>
            <font color='red' face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                if($campos[$i]['mmv'] > 0) {
                    if(($est_comprometido / $campos[$i]['mmv']) > 0) {
                        echo '<br>'.number_format($est_comprometido / $campos[$i]['mmv'], 1, ',', '.').' mês';
                    }else {
                        echo '<br>0,0 mês';
                    }
                }else {
                    echo '<br>0,0 mês';
                }
            ?>
            </font>
        </td>
        <td>
        <?
            echo number_format($qtde_disponivel, 1, ',', '.').'/';//Estoque Disponível ...

            //Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" ...
            $sql = "SELECT `qtde` 
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `status` = '0' LIMIT 1 ";
            $campos_excedente = bancos::sql($sql);
            if($campos_excedente[0]['qtde'] > 0) {//Se existir Estoque Excedente, exibo um link p/ ver Detalhes
        ?>
                <a href="javascript:nova_janela('../../../../vendas/estoque_acabado/excedente/alterar.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Estoque Excedente' style='cursor:help' class='link'>
        <?
            }
            echo number_format($qtde_estoque, 1, ',', '.').'/';//Estoque Real ...
            echo number_format($est_comprometido, 1, ',', '.');//Estoque Comprometido ...
        ?>
                </a>
        <?
            $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($id_produto_acabado);
            
            $font_ed_atrelado       = ($retorno_pas_atrelados['total_ed_pas_atrelados'] < 0) ? 'red' : 'black';
            $font_er_atrelado       = ($retorno_pas_atrelados['total_er_pas_atrelados'] < 0) ? 'red' : 'black';
            $font_ec_atrelado       = ($retorno_pas_atrelados['total_ec_pas_atrelados'] < 0) ? 'red' : 'black';
            
            echo '<br/><font color="'.$font_ed_atrelado.'" title="Estoque Disponível Atrelado" style="cursor:help">'.number_format($retorno_pas_atrelados['total_ed_pas_atrelados'] / $campos[$i]['pecas_por_jogo'], 0, '', '.').'</font>/';
            echo '<font color="'.$font_er_atrelado.'" title="Estoque Real Atrelado" style="cursor:help">'.number_format($retorno_pas_atrelados['total_er_pas_atrelados'] / $campos[$i]['pecas_por_jogo'], 0, '', '.').'</font>/';
            echo '<font color="'.$font_ec_atrelado.'" title="Estoque Comprometido Atrelado" style="cursor:help">'.number_format($retorno_pas_atrelados['total_ec_pas_atrelados'] / $campos[$i]['pecas_por_jogo'], 0, '', '.').'</font>';
            
            //echo '<br/>'.number_format($est_fornecedor, 1, ',', '.').'/'.number_format($est_porto, 0, ',', '.');
            echo '<br/>'.number_format($entrada_antecipada, 2, ',', '.');//Entrada Antecipada ...
        ?>
            <font color='green'>
                <br/><b>(Disp=<?=number_format($qtde_disponivel - $entrada_antecipada, 2, ',', '.');?>)</b>
            </font>
        </td>
        <td>
        <?
            echo $compra_producao;
            $font_compra_producao_atrelado  = ($retorno_pas_atrelados['total_compra_producao_pas_atrelados'] < -$retorno_pas_atrelados['total_ec_pas_atrelados']) ? 'red' : 'black';
            echo '<br><font color="'.$font_compra_producao_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">Atrel = '.number_format($retorno_pas_atrelados['total_compra_producao_pas_atrelados'] / $campos[$i]['pecas_por_jogo'], 0, '', '.').'</font>';
            echo $qtde_oe_em_aberto;
        ?>
        </td>
        <td style='cursor:help' align='center'>
            <iframe src = '../../../../classes/produtos_acabados/prazo_entrega.php?id_produto_acabado=<?=$id_produto_acabado;?>&operacao_custo=<?=$campos[$i]['operacao_custo'];?>&operacao_custo_sub=<?=$campos[$i]['operacao_custo_sub'];?>' name='prazo_entrega' id='prazo_entrega' marginwidth='0' marginheight='0' frameborder='0' height='45' width='100%' scrolling='auto'></iframe>
        </td>
        <td align='left'>
        <?
            $dados_faturamento  = '<font title="'.genericas::nome_empresa($campos[$i]['id_empresa']).'" style="cursor:help">'.substr(genericas::nome_empresa($campos[$i]['id_empresa']), 0, 1).'</font> / ';
            
            $preco_total_lote           = $campos[$i]['preco_liq_final'] * ($campos[$i]['qtde'] - $campos[$i]['qtde_faturada']);
        
            if($campos[$i]['finalidade'] == 'C') {
                $dados_faturamento.= '<font title="Consumo" style="cursor:help">C</font> / ';
            }else if($campos[$i]['finalidade'] == 'I') {
                $dados_faturamento.= '<font title="Industrialização" style="cursor:help">I</font> / ';
            }else {
                $dados_faturamento.= '<font title="Revenda" style="cursor:help">R</font> / ';
            }

            if($campos[$i]['tipo_frete'] == 'C') {
                $dados_faturamento.= 'CIF / ';
            }else {
                $dados_faturamento.= 'FOB / ';
            }
            
            if($campos[$i]['vencimento1'] == 0) {
                $dados_vencimento = 'À vista';
            }else {
                $dados_vencimento = $campos[$i]['vencimento1'];
                if($campos[$i]['vencimento2'] > 0) $dados_vencimento.= ' / '.$campos[$i]['vencimento2'];
                if($campos[$i]['vencimento3'] > 0) $dados_vencimento.= ' / '.$campos[$i]['vencimento3'];
                if($campos[$i]['vencimento4'] > 0) $dados_vencimento.= ' / '.$campos[$i]['vencimento4'];
            }
            
            $dados_faturamento.= '<font title="Vencimentos: '.$dados_vencimento.'" style="cursor:help">'.$campos[$i]['prazo_medio'].'</font>';
            echo $dados_faturamento;

            if($campos[$i]['livre_debito'] == 'S') echo '<font style="Livre de Débito" cursor="help"> / LD</font>';
        ?>
        </td>
        <td>
        <?
            echo data::datetodata($campos[$i]['data_emissao'], '/').'</p>';
        
            if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                if($campos[$i]['faturar_em'] > $data_atual_mais_dias) {
                    echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }else {
                    echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }
            }
        ?>
        </td>
        <td>
        <?
            //O id_pais será muito útil mais abaixo ...
            $sql = "SELECT `id_pais`, `nome_fantasia` 
                    FROM `representantes` 
                    WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            if(count($campos_representante) > 0) {
        ?>
            <a href="javascript:nova_janela('../../../../vendas/representante/alterar2.php?passo=1&id_representante=<?=$campos[$i]['id_representante'];?>&pop_up=1', 'POP', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
        <?
                /*Se existir país no cadastro desse Representante, então representa que o mesmo é externo, 
                consequentemente apresento o seu Supervisor ...*/
                if(!is_null($campos_representante[0]['id_pais'])) {
                    //Busca do Supervisor ...
                    $sql = "SELECT r.`nome_fantasia` AS supervisor 
                            FROM `representantes_vs_supervisores` rs 
                            INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                            WHERE rs.`id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
                    $campos_supervisor  = bancos::sql($sql);
                    $title              = 'Supervisor => '.$campos_supervisor[0]['supervisor'];
                }else {
                    $title              = '';
                }
            ?>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' title='<?=$title;?>' style='cursor:help' <?=$color;?>>
                    <?=$campos_representante[0]['nome_fantasia'];?>
                </font>
            </a>
        <?
            }
        ?>
            <br/>
            <a href="javascript:nova_janela('alterar_cond_faturamento.php?id_pedido_venda_item=<?=$campos[$i]['id_pedido_venda_item'];?>', 'CONSULTAR', '', '', '', '', '180', '780', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Condição de Faturamento' class='link'>
            <?
                $condicao_faturamento = array_sistema::condicao_faturamento();
                echo $condicao_faturamento[$campos[$i]['condicao_faturamento']];
            ?>
            </a>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes de Pedido' class='link'>
        <?
                if($id_pedido_venda_antigo != $campos[$i]['id_pedido_venda']) {
//Aki significa que mudou para outro N. de Pedido e vai exibir uma nova sequência desses mesmos
                    $id_pedido_venda_antigo = $campos[$i]['id_pedido_venda'];
        ?>
                    <font color='red'>
                        <?=$campos[$i]['id_pedido_venda'];?>
                    </font>
        <?
                    if($campos[$i]['expresso'] == 'S') echo '<br><font color="brown">(EXPRESSO)</font>';
//Ainda são os mesmos Pedidos
                }else {
                    echo $campos[$i]['id_pedido_venda'];
                    if($campos[$i]['expresso'] == 'S') echo '<br><font color="brown">(EXPRESSO)</font>';
                }
        ?>
            </a>
        <?
//Se existir a opção de Seu Número de Pedido, então printa essa linha ...
            if(!empty($campos[$i]['num_seu_pedido'])) {
        ?>
                <br/>
                <font color='darkblue' title='N.º do Pedido do Cliente <?=$campos[$i]['id_pedido_venda'];?>' style='cursor:help'>
                    <?=$campos[$i]['num_seu_pedido'];?>
                </font>
        <?
                if($campos[$i]['liberado'] == 0) echo "<br/><font color='red' title='Não Liberado' style='cursor:help'><b>Ñ LIB</b></font>";
            }
        ?>
            <input type='button' name='cmd_incluir_nf' value='Incluir NF' title='Incluir NF' onclick="prosseguir('<?=$credito;?>', '<?=$cliente_com_credito_devedor;?>', '<?=$qtde_nf_abertas;?>', '<?=$valor_total_itens_faturar;?>', '<?=$campos[$i]['id_pedido_venda'];?>')" class='botao'>
        </td>
        <td align='right'>
        <?
            echo number_format($campos[$i]['preco_liq_final'], 2, ',', '.').'<br/>';
        
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
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
            
            <font color="<?=$cor_instantanea;?>" id='id_ml_instantanea<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'><br/>
                <?='ML='.$margem[1];//Valor Descritivo da Margem ...?>
            </font>
            <font color="<?=$cor_gravada;?>" id='id_ml_gravada<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'>
                <br/><?='MLG='.number_format($campos[$i]['margem_lucro'], 2, ',', '.');?>
            </font>
            <font color="<?=$cor_estimada;?>" id='id_ml_estimada<?=$i;?>' title='Margem de Lucro Estimada' style='cursor:help'>
                <br/><?='MLEst='.number_format($campos[$i]['margem_lucro_estimada'], 2, ',', '.');?>
            </font>
        <?
            }
        ?>
        </td>
        <td align='right'>
        <?
            $valor_total_item = ($campos[$i]['qtde'] - $campos[$i]['qtde_faturada']) * $campos[$i]['preco_liq_final'];
            echo number_format($valor_total_item, 2, ',', '.');
        ?>
        </td>
    </tr>
    <?
/*Se a Data de Programação for até a Data de Hoje + "X dias, por exemplo + 3 dias" e o Pedido estiver 
liberado, então é faturável ...*/
            if($campos[$i]['faturar_em'] <= $data_atual_mais_dias && $campos[$i]['liberado'] == 1) {
                if($campos[$i]['qtde_pendente'] >= $qtde_disponivel) {
                    $resultado = $qtde_disponivel;
                }else {
                    $resultado = $campos[$i]['qtde_pendente'];
                }
                $valor_faturavel_por_empresa+= ($campos[$i]['separada'] + $campos[$i]['vale'] + $resultado) * $campos[$i]['preco_liq_final'];
            }
            
            if($campos[$i]['faturar_em'] != '0000-00-00') {//Se existe Pedidos programados então ...
                if($campos[$i]['faturar_em'] > $data_atual_mais_dias) $valor_programado_por_empresa+= $valor_total_item;
            }
            
            $valor_separado_por_empresa+= $campos[$i]['separada'] * $campos[$i]['preco_liq_final'];
            $valor_total_por_empresa+=  $preco_total_lote;
            
            if($id_empresa_corrente != $campos[$i + 1]['id_empresa']) {
    ?>
    <tr class='linhadestaque'>
        <td colspan='18'>
            <font color='yellow'>
                Valor Faturável:
            </font>
            <?
                echo 'R$ '.number_format($valor_faturavel_por_empresa, 2, ',', '.');
                if($cliente_com_credito_devedor == 1) echo ' <font color="red" size="2"><b>(SEM CRÉDITO)</b></font>';
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;
            
            <font color='yellow'>
                Valor Programado: 
            </font>
            <?='R$ '.number_format($valor_programado_por_empresa, 2, ',', '.');?>
            
            &nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;
            
            <font color='yellow'>
                Valor Separado:
            </font>
            <?='R$ '.number_format($valor_separado_por_empresa, 2, ',', '.');?>
            
            &nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;
            
            <font color='yellow'>
                Valor Total:
            </font>
            <?=' => '.$tipo_moeda.number_format($valor_total_por_empresa, 2, ',', '.');?>
        </td>
    </tr>
<?
                $id_empresa_corrente            = $campos[$i + 1]['id_empresa'];
                
                $valor_faturavel_todas_empresas+=   $valor_faturavel_por_empresa;
                $valor_programado_todas_empresas+=  $valor_programado_por_empresa;
                $valor_separado_todas_empresas+=    $valor_separado_por_empresa;
                $valor_total_todas_empresas+=       $valor_total_por_empresa;

                //Zero essas variáveis p/ não dar conflito com os valores do próximo loop ...
                $valor_faturavel_por_empresa        = 0;
                $valor_programado_por_empresa       = 0;
                $valor_separado_por_empresa         = 0;
                $valor_total_por_empresa            = 0;
            }
            /*Essa variável "$vetor_pas_atrelados" é processada dentro da função -> 
            intermodular::calculo_producao_mmv_estoque_pas_atrelados(), e esse unset serve para eliminar 
            o acúmulo de ID_PAs que fica de um loop para o outro

            Exemplo: Primeiro Loop 9 Registros, Segundo Loop 8 Registros, mas me retorna 17 acumulando com os
            9 do Primeiro Loop, o mais ideal seria de jogar esse unset dentro da função, mais não funciona 
            agora já não sei se é por causa do Global, vai entender, tive que fazer esse Macete ...

            02/06/2016 ...*/
            unset($vetor_pas_atrelados);
        }
?>
    <tr class='linhacabecalho'>
        <td colspan='18'>
            <font color='yellow'>
                Valor Faturável Geral: 
            </font>
            <?=$tipo_moeda.number_format($valor_faturavel_todas_empresas, 2, ',', '.');?>
            &nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;
            
            <font color='yellow'>
                Valor Programado Geral: 
            </font>
            <?=$tipo_moeda.number_format($valor_programado_todas_empresas, 2, ',', '.');?>
            
            &nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='yellow'>
                Valor Separado Geral: 
            </font>
            <?=$tipo_moeda.number_format($valor_separado_todas_empresas, 2, ',', '.');?>
            
            &nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='yellow'>
                Valor Total Geral: 
            </font>
            <?=$tipo_moeda.number_format($valor_total_todas_empresas, 2, ',', '.');?>
        </td>
    </tr>
</table>
<!-- ******************************************** -->
<input type='hidden' name='posicao'>
<input type='hidden' name='passo'>
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente?>'>
<!--Até então serve somente para armazenar o valor das mensagens-->
<input type='hidden' name='valor'>
<!-- ******************************************** -->
</form>
</body>
</html>
<?
        if(!empty($valor)) {
?>
            <Script Language = 'JavaScript'>
                alert('<?=$mensagem[$valor];?>')
            </Script>
<?
        }
    }else {
?>
<html>
<body>
<form name='form'>
<table width='90%' border='0' align='center'>
    <tr class='atencao' align='center'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='#FF0000'>
            <?
                if(!empty($id_pedido_venda)) {
            ?>
            <b>Pedido
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='blue'>
                <?=$id_pedido_venda;?>
            </font>
            n&atilde;o cont&eacute;m item(ns) cadastrado(s).</b>
            <?
                }

                if(!empty($id_cliente)) {
            ?>
            <b>Cliente
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='blue'>
                <?=$cliente;?>
            </font>
            n&atilde;o cont&eacute;m pedido(s) cadastrado(s).</b>
            <?
                }
            ?>
            </font>
        </td>
    </tr>
</table>
<input type='hidden' name='passo'>
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente?>'>
</form>
</body>
</html>
<?
    }
}
?>