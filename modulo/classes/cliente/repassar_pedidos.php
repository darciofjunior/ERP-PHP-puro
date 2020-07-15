<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/clientes_vs_representantes.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>REPRESENTANTE(S) ALTERADO(S) COM SUCESSO.</font>";

if($passo == 1) {
/*Verifico se o Representante Atual foi substituido por Outro na Combo para o mesmo Cliente ...
Se sim, eu transfiro todos os Orc e Pedidos q não estejam Concluídos do respectivo Cliente e Representante p/ 
o novo Representante ...*/
    $sql = "UPDATE `orcamentos_vendas_itens` ovi 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`id_cliente` = '$_POST[id_cliente]' 
            SET ovi.`id_representante` = '$_POST[cmb_representante_novo]' WHERE ovi.`id_representante` = '$_POST[cmb_representante_atual]' AND ovi.`status` < '2' ";
    bancos::sql($sql);

    $sql = "UPDATE `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`id_cliente` = '$_POST[id_cliente]' 
            SET pvi.`id_representante` = '$_POST[cmb_representante_novo]' WHERE pvi.`id_representante` = '$_POST[cmb_representante_atual]' AND pvi.`status` < '2' ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        window.location = 'repassar_pedidos.php?id_cliente=<?=$_POST['id_cliente'];?>&cmb_representante_atual=<?=$_POST['cmb_representante_atual'];?>&valor=1'
    </Script>
<?
}else {
    //Seleção dos Dados do Cliente ...
    $sql = "SELECT if(razaosocial = '', nomefantasia, razaosocial) as cliente 
            FROM `clientes` 
            WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Repassar Pedido(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Representante Atual ...
    if(!combo('form', 'cmb_representante_atual', '', 'SELECIONE O REPRESENTANTE ATUAL !')) {
        return false
    }
//Novo Representante ...
    if(!combo('form', 'cmb_representante_novo', '', 'SELECIONE O NOVO REPRESENTANTE !')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.cmb_representante_novo.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onSubmit='return validar()'>
<!--**********************************Controles de Tela**********************************-->
<input type='hidden' name='id_cliente' value='<?=$_GET[id_cliente];?>'>
<!--*************************************************************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Repassar Pedido(s)
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            <b>Cliente:</b>
        </td>
        <td>
            <?=$campos[0]['cliente'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante Atual:</b>
        </td>
        <td>
            <select name="cmb_representante_atual" title="Selecione o Representante Atual" class='combo'>
            <?
                $sql = "SELECT id_representante, nome_fantasia 
                        FROM representantes 
                        WHERE ativo = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, $_GET['cmb_representante_atual']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Novo Representante:</b>
        </td>
        <td>
            <select name="cmb_representante_novo" title="Selecione o Novo Representante" class='combo'>
            <?
                $sql = "SELECT id_representante, nome_fantasia 
                        FROM representantes 
                        WHERE ativo = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_representante_novo.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>