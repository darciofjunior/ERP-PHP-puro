<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/listagem_separacao/consultar.php', '../../../../');
?>
<html>
<head>
<title>.:: Listagem de Separação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function fechar() {
    var mensagem = confirm('DESEJA MARCAR ESTE(S) ITEM(NS) SELECIONADO(S) COMO JÁ LIDO(S) ?\n\nOBSERVAÇÃO: CASO SIM, ENTÃO O(S) MESMO(S) NÃO APARECERÁ(ÃO) MAIS NA MESMA FORMA DE BUSCA !')
    if(mensagem == false) {
        return false
    }else {
        opener.document.form.id_pedido_venda_separacao.value = '<?=implode(',', $_POST['chkt_pedido_venda_separacao']);?>'
        opener.document.form.passo.onclick()
        window.close()
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='70%' border='1' align='center' cellspacing='0' cellpadding='0'>
    <tr class='linhanormal' align='center'>
        <td colspan='4'>
            <b>Listagem de Separação</b>
        </td>
    </tr>
    <tr class="linhanormal" align="center">
        <td bgcolor='#FFFFFF'>
            <b>Qtde Sep</b>
        </td>
        <td bgcolor='#FFFFFF'>
            <b>Qtde Vale</b>
        </td>
        <td bgcolor='#FFFFFF'>
            <b>Referência * Discriminação</b>
        </td>
        <td bgcolor='#FFFFFF'>
            <b>Cliente</b>
        </td>
    </tr>
<?
	for($i = 0; $i < count($_POST['chkt_pedido_venda_separacao']); $i++) {
            $sql = "SELECT DISTINCT(pvs.id_pedido_venda_separacao), pvs.id_pedido_venda, pvs.qtde_vale, pvs.data_sys, pvs.qtde_separado, pvs.data_sys, pa.id_produto_acabado, pa.referencia, pa.discriminacao 
                    FROM `pedidos_vendas_separacoes` pvs 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvs.id_produto_acabado AND pa.`ativo` = '1' 
                    WHERE pvs.`id_pedido_venda_separacao` = '".$_POST['chkt_pedido_venda_separacao'][$i]."' ORDER BY pa.referencia, pa.discriminacao, pvs.data_sys LIMIT 1 ";
            $campos = bancos::sql($sql);
?>
    <tr class="linhanormal" align='center'>
        <td bgcolor='#FFFFFF'>
            <?=segurancas::number_format($campos[0]['qtde_separado'], 2, '.');?>
        </td>
        <td bgcolor='#FFFFFF'>
            <?=segurancas::number_format($campos[0]['qtde_vale'], 2, '.');?>
        </td>
        <td bgcolor='#FFFFFF' align='left'>
            <?=$campos[0]['referencia'].' * '.$campos[0]['discriminacao'];?>
        </td>
        <td bgcolor='#FFFFFF' align='left'>
        <?
//Busca da razão social
            $sql = "SELECT nomefantasia, razaosocial 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                    WHERE pv.`id_pedido_venda` = '".$campos[0]['id_pedido_venda']."' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            if(!empty($campos_cliente[0]['nomefantasia'])) {
                echo $campos_cliente[0]['nomefantasia'];
            }else {
                echo $campos_cliente[0]['razaosocial'];
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhanormal' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title="Imprimir" onclick="window.print()" class="botao">
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar()" style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>