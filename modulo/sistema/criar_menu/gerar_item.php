<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/sistema/criar_menu/criar_menu.php', '../../../');

$mensagem[1] = '<font class="confirmacao">ITEM INCLUIDO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">ITEM J¡ EXISTENTE.</font>';

if(!empty($_POST['txt_menu_item'])) {
    if(!empty($_POST['txt_url'])) $url = segurancas::tratar_path($_POST['txt_url']);
    //Gerando o Sub-Sub-item do Menu ...
    $sql = "INSERT INTO `menus_itens` (`id_menu_item`, `id_menu`, `item`, `nivel`, `endereco`) VALUES (NULL, '".(int)$_POST['id_menu']."', '$_POST[txt_menu_item]', '$_POST[txt_nivel]', '$url') ";
    bancos::sql($sql);
    $valor = 1;
}

//Procedimento quando carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cod_modulo = $_POST['cod_modulo'];
    $id_menu    = $_POST['id_menu'];
}else {
    $cod_modulo = $_GET['cod_modulo'];
    $id_menu    = $_GET['id_menu'];
}
$id_menu = segurancas::controle_casas_decimais($id_menu);

//SomatÛria de NÌveis ...
$sql = "SELECT SUBSTRING(`nivel`, 4, 3) AS valor_nivel 
        FROM `menus_itens` 
        WHERE SUBSTRING(`nivel`, 1, 3) = '$id_menu' 
        AND SUBSTRING(`nivel`, 4, 3) <> '000' 
        ORDER BY SUBSTRING(`nivel`, 4, 3) DESC ";
$campos = bancos::sql($sql);
if($campos[0]['valor_nivel'] == 0) {
    $id_menu_item = '001';
}else {
    $id_menu_item = (int)$campos[0]['valor_nivel'] + 1;
    $id_menu_item = segurancas::controle_casas_decimais($id_menu_item);
}

//Busca o MÛdulo
$sql = "SELECT modulo 
        FROM `modulos` 
        WHERE `id_modulo` = '$cod_modulo' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Gerar Item(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!texto('form', 'txt_menu_item', '3', 'qwertyuiopÁlkjhgfdsazxcvbnm·ÈÌÛ˙„ı‡‚ÍÓÙ˚¸ QWERTYUIOP«LKJHGFDSAZXCVBNM¡…Õ”⁄√’¿¬ Œ‘€‹1234567890\/™∫∞π≤≥£¢¨\;][=-|<>.,:?}{+_!@#$%®&*()', 'ITEM', '2')) {
        return false
    }
//Aqui È para n„o atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.location = 'criar_menu.php?cmb_modulo=<?=$cod_modulo;?>'
}
</Script>
</head>
<body onload="document.form.txt_menu_item.focus()" onunload="atualizar_abaixo()">
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='cod_modulo' value='<?=$cod_modulo;?>'>
<input type='hidden' name='id_menu' value='<?=$id_menu;?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerar Item(ns)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>MÛdulo: </b><?=$campos[0]['modulo'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Item: </b>
        </td>
        <td>
            URL:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_menu_item' title='Digite o nome do Menu' size='25' maxlength='25' class='caixadetexto'>
        </td>
        <td>
            <input type='file' name='txt_url2' title='Digite o nome da URL' size='25' class='caixadetexto'>
            <input type='text' name='txt_url' title='Digite o nome da URL' size='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            NÌvel:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?=$id_menu.$id_menu_item.'000000';?>
            <input type='hidden' name='txt_nivel' value='<?=$id_menu.$id_menu_item.'000000';?>' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_menu_item.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>