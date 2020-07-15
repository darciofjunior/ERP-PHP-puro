<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/sistema/criar_menu/criar_menu.php', '../../../');

$mensagem[1] = '<font class="confirmacao">M”DULO ALTERADO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">M”DULO J¡ EXISTENTE.</font>';

if(!empty($_POST['txt_modulo'])) {
    //Verifico se existe outro MÛdulo com mesmo Nome cadastrado no sistema diferente do que foi passado por par‚metro...
    $sql = "SELECT modulo 
            FROM `modulos` 
            WHERE `modulo` = '$_POST[txt_modulo]' 
            AND `id_modulo` <> '$_POST[id_mod]' ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//MÛdulo n„o cadastrado, posso alterar ...
        $sql = "UPDATE `modulos` SET `modulo` = '$_POST[txt_modulo]' WHERE `id_modulo` = '$_POST[id_mod]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//MÛdulo n„o cadastrado, posso alterar ...
        $valor = 2;
    }
}

//Procedimento normal de quando se carrega a Tela ...
$id_mod = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_mod'] : $_GET['id_mod'];
        
//Aqui eu trago dados do MÛdulo passado por par‚metro ...
$sql = "SELECT modulo 
        FROM `modulos` 
        WHERE `id_modulo` = '$id_mod' ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar MÛdulo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!texto('form', 'txt_modulo', '3', 'qwertyuiopÁlkjhgfdsazxcvbnm·ÈÌÛ˙„ı‡‚ÍÓÙ˚¸ QWERTYUIOP«LKJHGFDSAZXCVBNM¡…Õ”⁄√’¿¬ Œ‘€‹1234567890\/™∫∞π≤≥£¢¨\;][=-|<>.,:?}{+_!@#$%®&*()', 'M”DULO', '2')) {
        return false
    }
//Aqui È para n„o atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.location = 'criar_menu.php'
}
</Script>
</head>
<body onload="document.form.txt_modulo.focus()" onunload="atualizar_abaixo()">
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Tem que passar o nome do Par‚metro como id_mod, para n„o dar conflito com a vari·vel id_modulo da sess„o-->
<input type='hidden' name='id_mod' value="<?=$id_mod;?>">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar MÛdulo(s)
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type='text' name='txt_modulo' value='<?=$campos[0]['modulo'];?>' title='Digite o MÛdulo' size='25' maxlength='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_modulo.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>