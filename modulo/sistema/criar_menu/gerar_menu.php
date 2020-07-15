<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/sistema/criar_menu/criar_menu.php', '../../../');

$mensagem[1] = '<font class="confirmacao">MENU INCLUIDO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">MENU J¡ EXISTENTE.</font>';

if(!empty($_POST['txt_menu'])) {
    if(!empty($_POST['txt_url'])) $url = segurancas::tratar_path($_POST['txt_url']);

    //Verifica se j· existe um Menu com esse nome dentro do MÛdulo passado por par‚metro...
    $sql = "SELECT menu 
            FROM `menus` 
            WHERE `id_modulo` = '$_POST[cod_modulo]' 
            AND `menu` = '$_POST[txt_menu]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "INSERT INTO `menus` (`id_modulo`, `menu`, `endereco`) VALUES ('$_POST[cod_modulo]', '$_POST[txt_menu]', '$url')";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cod_modulo = $_POST['cod_modulo'];
}else {
    $cod_modulo = $_GET['cod_modulo'];
}

/*Busco o nome do MÛdulo atravÈs do $id_modulo passado por par‚metro p/ que o usu·rio saiba onde que ele 
est· criando o Menu ...*/
$sql = "SELECT modulo 
        FROM `modulos` 
        WHERE `id_modulo` = '$cod_modulo' ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Gerar Menu(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!texto('form', 'txt_menu', '3', 'qwertyuiopÁlkjhgfdsazxcvbnm·ÈÌÛ˙„ı‡‚ÍÓÙ˚¸ QWERTYUIOP«LKJHGFDSAZXCVBNM¡…Õ”⁄√’¿¬ Œ‘€‹1234567890\/™∫∞π≤≥£¢¨\;][=-|<>.,:?}{+_!@#$%®&*()', 'MENU', '2')) {
        return false
    }
//Aqui È para n„o atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0)         parent.location = 'criar_menu.php?cmb_modulo=<?=$cod_modulo;?>'
}
</Script>
</head>
<body onload='document.form.txt_menu.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='cod_modulo' value="<?=$cod_modulo;?>">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gera Menu(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>MÛdulo: </b><?=$campos[0]['modulo'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Menu:</b>
        </td>
        <td>
            URL:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_menu' title='Digite o Nome do Menu' size='25' maxlength='25' class='caixadetexto'>
        </td>
        <td>
            <input type='file' name='txt_url2' title='Digite o Nome da URL' size='25' class='caixadetexto'>
            <input type='text' name='txt_url' title='Digite o nome da URL' size='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_menu.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>