<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/sistema/criar_menu/criar_menu.php', '../../../');

$mensagem[1] = '<font class="confirmacao">SUB-ITEM DE MENU ALTERADO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">SUB-ITEM DE MENU J� EXISTENTE PARA ESSA URL.</font>';

if(!empty($_POST['txt_sub_item_menu'])) {
    if(!empty($_POST['txt_url'])) {
        $url = segurancas::tratar_path($_POST['txt_url']);
    }else if($_POST['chkt_retirar'] == 1) {
        $url == '';
    }else {
        $url = $_POST['caminho'];
    }
    /*Verifica se existe um Sub-Item com esse nome dentro desse Menu passado por par�metro, diferente do atual que 
    est� sendo alterado ...*/
    $sql = "SELECT item 
            FROM `menus_itens` 
            WHERE `id_menu` = '$_POST[id_menu]' 
            AND SUBSTRING(`nivel`, 1, 6) = '$_POST[nivel]' 
            AND `item` = '$_POST[txt_sub_item_menu]' 
            AND `id_menu_item` <> '$_POST[id_menu_item]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "UPDATE `menus_itens` SET `item` = '$_POST[txt_sub_item_menu]', `endereco` = '$url' WHERE `id_menu_item` = '$_POST[id_menu_item]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
} 

//Procedimento normal quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cod_modulo     = $_POST['cod_modulo'];
    $id_menu        = $_POST['id_menu'];
    $id_menu_item   = $_POST['id_menu_item'];
}else {
    $cod_modulo     = $_GET['cod_modulo'];
    $id_menu        = $_GET['id_menu'];
    $id_menu_item   = $_GET['id_menu_item'];
}

//Busca o M�dulo ...
$sql = "SELECT modulo 
        FROM `modulos` 
        WHERE `id_modulo` = '$cod_modulo' ";
$campos = bancos::sql($sql);

//Busca o Sub-Item ...
$sql = "SELECT id_menu_item, item, endereco, SUBSTRING(nivel, 1, 6) AS nivel 
        FROM `menus_itens` 
        WHERE `id_menu_item` = '$id_menu_item' LIMIT 1 ";
$campos_sub_item = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Sub-Item(ns) do Menu(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!texto('form', 'txt_sub_item_menu', '3', 'qwertyuiop�lkjhgfdsazxcvbnm�������������� QWERTYUIOP�LKJHGFDSAZXCVBNM��������������1234567890\/���������\;][=-|<>.,:?}{+_!@#$%�&*()', 'SUB-ITEM', '2')) {
        return false
    }
//Aqui � para n�o atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.location = 'criar_menu.php?cmb_modulo=<?=$cod_modulo;?>'
}
</Script>
</head>
<body onload='document.form.txt_sub_item_menu.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='cod_modulo' value='<?=$cod_modulo;?>'>
<input type='hidden' name='id_menu' value='<?=$id_menu;?>'>
<input type='hidden' name='id_menu_item' value='<?=$id_menu_item;?>'>
<input type='hidden' name='caminho' value="<?=$campos_sub_item[0]['endereco'];?>">
<input type='hidden' name='nivel' value="<?=$campos_sub_item[0]['nivel'];?>">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Sub-Item(ns) do Menu(s) 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>M�dulo: </b><?=$campos[0]['modulo'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Sub-Item:</b>
        </td>
        <td>
            URL:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_sub_item_menu' value='<?=$campos_sub_item[0]['item'];?>' title='Digite o Sub-Item' size='40' maxlength='45' class='caixadetexto'>
        </td>
        <td>
            <input type='file' name='txt_url2' value='<?=$campos_sub_item[0]['endereco'];?>' title='Digite o nome da URL' size='25' class='caixadetexto'>
            <input type='text' name="txt_url" value='<?=$campos_sub_item[0]['endereco'];?>' title='Digite o nome da URL' size='25' class='caixadetexto'>
        </td>
    </tr>
<?
    if(!empty($campos_sub_item[0]['endereco'])) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Endere�o Atual: </b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?=$campos_sub_item[0]['endereco'];?>
            <input type='checkbox' name='chkt_retirar' value='1' class='checkbox'> Retirar Endere�o
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.txt_sub_item_menu.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>