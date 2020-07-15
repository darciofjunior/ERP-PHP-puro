<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');
?>
<html>
<head>
<title>.:: Imprimir Requisição de Materiais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../css/layout.css'>
<Script Language = 'Javascript' src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function imprimir() {
    if(document.form.cmb_tipo_cabecalho.value == '') {
        alert('SELECIONE UM TIPO DE CABEÇALHO PARA IMPRESSÃO !')
        document.form.cmb_tipo_cabecalho.focus()
        return false
    }
    var tipo_cabecalho = document.form.cmb_tipo_cabecalho.value
/*Quer dizer, além de eu levar todos esses Quinhentos e poucos parâmetros q já vem das telas anteriores, 
eu ainda tenho que levar mais esse agora que é o Tipo de Cabeçalho*/
    window.close()
    nova_janela('relatorio_pdf/requisicao.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_item_pedido=<?=$_GET['chkt_item_pedido'];?>&txt_qtde=<?=$_GET['txt_qtde'];?>&obs_requisicao=<?=$_GET['obs_requisicao'];?>&tipo_cabecalho='+tipo_cabecalho, 'IMPRIMIR', 'F')
}
</Script>
</head>
<body>
<form name='form' method='post' target='IMPRIMIR'>
<table width='80%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Imprimir Requisição de Materiais
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Tipo de Cabeçalho:</b>
            <select name='cmb_tipo_cabecalho' title='Selecione o Tipo de Cabeçalho' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1'>ALBAFER</option>
                <option value='2'>TOOL MASTER</option>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_imprimir" value="Imprimir" title="Imprimir" onclick="return imprimir()" class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>