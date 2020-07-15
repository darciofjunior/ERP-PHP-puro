<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/etiquetas/etiquetas.php', '../../../');
?>
<html>
<head>
<title>.:: Perfil de Etiqueta ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//O usuário é obrigado a selecionar pelo menos uma opção ...
    if(document.form.opt_opcao_perfil[0].checked == false && document.form.opt_opcao_perfil[1].checked == false) {
        alert('SELECIONE UM PERFIL DE ETIQUETA !')
        document.form.opt_opcao_perfil[0].focus()
        return false
    }
    document.form.action = '<?=$_POST['cmb_impressora'];?>'+'/'+document.form.cmb_tipo_etiqueta.value
}
</Script>
</head>
<body onload='document.form.opt_opcao_perfil[0].focus()'>
<form name='form' method='post' onsubmit='return validar()'>
<!--**********************Controle de Tela**********************-->
<?
//Aqui eu busco todos os objetos do formulário anterior ...
foreach($HTTP_POST_VARS as $name => $value) {
?>
    <input type='hidden' name='<?=$name?>' value='<?=$value;?>'>
<?
}
?>
<!--************************************************************-->
<table width='85%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Perfil de Etiqueta
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao_perfil' value='1' title='Perfil Seriado (Modelo Antigo)' id='label1'>
            <label for='label1'>Perfil Seriado (Modelo Antigo)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao_perfil' value='2' title='Perfil Completo (Modelo Novo)' id='label2'>
            <label for='label2'>Perfil Completo (Modelo Novo)</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.opt_opcao_perfil[0].focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>