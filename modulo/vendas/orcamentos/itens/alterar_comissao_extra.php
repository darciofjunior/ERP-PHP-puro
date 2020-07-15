<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

if(!empty($_POST['id_orcamento_venda_item'])) {
//Atualizo o item do Orçamento com o Novo Preço digitado pelo usuário ...
    $sql = "UPDATE `orcamentos_vendas_itens` SET `comissao_extra` = '$_POST[txt_comissao_extra]' WHERE `id_orcamento_venda_item` = '$_POST[id_orcamento_venda_item]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script language = 'JavaScript'>
        alert('COMISSÃO EXTRA ALTERADA COM SUCESSO !')
        parent.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$_POST['id_orcamento_venda'];?>'
    </Script>
<?
    exit;
}

//Significa q está sendo acessado do Mód. de Compras, então só mostra P.A. do Tipo Componentes ...
$sql = "SELECT pa.`referencia`, pa.`discriminacao`, ovi.`id_orcamento_venda`, ovi.`comissao_extra` 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
        WHERE ovi.`id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Comissão Extra ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(!texto('form', 'txt_comissao_extra', '1', '1234567890,', 'COMISSÃO EXTRA %', '1')) {
        return false
    }
    return limpeza_moeda('form', 'txt_comissao_extra, ')
}
</Script>
</head>
<body onload='document.form.txt_comissao_extra.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='id_orcamento_venda_item' value='<?=$_GET['id_orcamento_venda_item'];?>'>
<input type='hidden' name='id_orcamento_venda' value='<?=$campos[0]['id_orcamento_venda'];?>'>
<!--*************************************************************************-->
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Comissão Extra
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow'>
                <b>Ref: </b>
            </font>
            <?=$campos[0]['referencia'];?>
        </td>
        <td>
            <font color='yellow'>
                <b>Discriminação: </b>
            </font>
            <?=$campos[0]['discriminacao'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Comissão Extra:</b>
        </td>
        <td>
            <input type='text' name='txt_comissao_extra' value='<?=number_format($campos[0]['comissao_extra'], 2, ',', '.');?>' title='Digite a Comissão Extra' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size='6' maxlength='5' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'> 
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>