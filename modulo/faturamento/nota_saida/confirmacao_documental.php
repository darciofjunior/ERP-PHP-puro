<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');

if(!empty($_POST['id_nf'])) {
    $sql = "UPDATE `nfs` SET `id_funcionario_confirm_doc` = '$_SESSION[id_funcionario]', `trading_confirmacao` = '$_POST[txt_confirmacao_documental]' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('CONFIRMAÇÃO DOCUMENTAL ALTERADA COM SUCESSO !')
        opener.document.location = opener.document.location.href
        window.close()
    </Script>
<?
}

//Aqui traz os dados da Nota Fiscal
$sql = "SELECT trading_confirmacao 
        FROM `nfs` 
        WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Confirmação Documental (Trading / Suframa) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<body onload='document.form.txt_confirmacao_documental.focus()'>
<form name='form' method='post' action=''>
<!--*******************Controle de Tela*******************-->
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf'];?>'>
<!--******************************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Confirmação Documental (Trading / Suframa)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Confirmação Documental (Trading / Suframa):</td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <textarea name='txt_confirmacao_documental' cols='75' rows='4' maxlength='300' class='caixadetexto'><?=$campos[0]['trading_confirmacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_confirmacao_documental.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>