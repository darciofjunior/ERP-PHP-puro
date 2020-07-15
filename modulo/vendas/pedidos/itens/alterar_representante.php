<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');

if(!empty($_POST['cmb_representante'])) {
    //Atualizo os itens do Pedido de Venda com o Novo Representante ...
    $sql = "UPDATE `pedidos_vendas_itens` SET `id_representante` = '$_POST[cmb_representante]' WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' ";
    bancos::sql($sql);
    
    //Atualizo os itens do Orçamento de Venda correspondente a este Pedido com o Novo Representante ...
    $sql = "UPDATE `orcamentos_vendas_itens` SET `id_representante` = '$_POST[cmb_representante]' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' ";
    bancos::sql($sql);
?>
    <Script language = 'JavaScript'>
        alert('REPRESENTANTE(S) ALTERADO(S) COM SUCESSO !')
        window.opener.parent.itens.document.form.submit()
        window.close()
    </Script>
<?
    exit;
}

//Busco o Representante atual do Pedido p/ apresentar na Tela ...
$sql = "SELECT DISTINCT(pvi.`id_representante`) AS id_representante, ovi.`id_orcamento_venda`, r.`nome_fantasia` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `representantes` r ON r.`id_representante` = pvi.`id_representante` 
        WHERE pvi.`id_pedido_venda` = '$_GET[id_pedido_venda]' ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Representante ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Representante ...
    if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.cmb_representante.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Controle de Tela-->
<input type='hidden' name='id_pedido_venda' value='<?=$_GET[id_pedido_venda];?>'>
<input type='hidden' name='id_orcamento_venda' value='<?=$campos[0]['id_orcamento_venda'];?>'>
<!--****************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Representante
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante atual:</b>
        </td>
        <td>
            <?=$campos[0]['nome_fantasia'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante:</b>
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>