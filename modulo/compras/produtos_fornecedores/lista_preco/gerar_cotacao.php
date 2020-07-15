<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../');

if(!empty($_POST['chkt_produto_insumo'])) {
    //Gerando uma Nova Cotação ...
    $sql = "INSERT INTO `cotacoes` (`id_cotacao`, `id_funcionario`, `data_sys`) VALUES (NULL, '$_SESSION[id_funcionario]', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    $id_cotacao = bancos::id_registro();

    foreach($_POST['chkt_produto_insumo'] as $i => $id_produto_insumo) {
        $sql = "INSERT INTO `cotacoes_itens` (`id_cotacao_item`, `id_cotacao`, `id_produto_insumo`, `qtde_pedida`) values (NULL, '$id_cotacao', '$id_produto_insumo', '".$_POST['txt_qtde'][$i]."') ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../classes/cotacao/vincular_fornecedor.php?id_cotacao=<?=$id_cotacao;?>'
    </Script>
<?
}

if(!empty($_GET['id_produtos_insumos'])) $condicao_produtos = " AND fpi.`id_produto_insumo` IN ($_GET[id_produtos_insumos]) ";

$sql = "SELECT f.razaosocial, g.referencia, pi.discriminacao, pi.credito_icms, fpi.* 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` AND f.`id_fornecedor` = '$_GET[id_fornecedor]' 
        WHERE fpi.`ativo` = '1' $condicao_produtos ORDER BY pi.discriminacao ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Gerar Cotação - Lista de Preço ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'controle.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) return false
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Gerar Cotação - Lista de Preço
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Todos' id="todos" class="checkbox">
        </td>
        <td>
            Qtde
        </td>
        <td>
            Referência
        </td>
        <td>
            Discrimina&ccedil;&atilde;o
        </td>
        <td>
            Preço Fat. Nac. R$ 
        </td>
        <td>
            Prazo Pgto dias
        </td>
        <td>
            Desc A/V %
        </td>
        <td>
            Desc SGD %
        </td>
        <td>
            IPI %
        </td>
        <td>
            ICMS %
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <input type='checkbox' name='chkt_produto_insumo[]' id='chkt_produto_insumo<?=$i?>' value='<?=$campos[$i]['id_produto_insumo'];?>' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$i?>' value='1,00' maxlength='10' size='12' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['preco_faturado'] != '0.00') echo number_format($campos[$i]['preco_faturado'], 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['prazo_pgto_ddl'] != '0.0') echo number_format($campos[$i]['prazo_pgto_ddl'], 1, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['desc_sgd'] != '0.0') echo number_format($campos[$i]['desc_sgd'], 1, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['desc_vista'] != '0.0') echo number_format($campos[$i]['desc_vista'], 1, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['ipi'] != 0) echo $campos[$i]['ipi'];
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['icms'] != 0) echo $campos[$i]['icms'];
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='submit' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>