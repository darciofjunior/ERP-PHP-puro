<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>BANCO EXCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>HÁ AGENCIAS USANDO ESSE BANCO.</font>";

if(!empty($_POST['chkt_banco'])) {
    foreach($_POST['chkt_banco'] as $id_banco) {
        //Verifico se este banco está sendo utilizado por alguma agência do Sistema antes de excluí-lo ...
        $sql = "SELECT id_agencia 
                FROM `agencias` 
                WHERE `id_banco` = '$id_banco' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//Não está sendo usado, sendo assim posso excluí-lo normalmente ...
            $sql = "UPDATE `bancos` SET `ativo` = '0' WHERE `id_banco` = '$id_banco' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 1;
        }else {//Está sendo utilizado, então pode excluir ...
            $valor = 2;
        }
    }
}

//Aqui eu busco todos os Bancos cadastrados no sistema ...
$sql = "SELECT * 
        FROM `bancos` 
        WHERE `ativo` = '1' ORDER BY banco ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
    window.location = '../../../html/index.php?valor=4'
    </Script>
<?
    exit;
}
?>
<html>
<head>
<title>.:: Excluir Bancos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='60%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='3'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='3'>
            Excluir Banco(s)
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            Banco
        </td>
        <td>
            Página Web
        </td>
        <td>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' id='todos' class="checkbox">
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas;$i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td>
            <?=$campos[$i]['pagweb'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_banco[]' value="<?=$campos[$i]['id_banco'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
    }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            <input type='submit' name='cmd_excluir' value="Excluir" title="Excluir" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>