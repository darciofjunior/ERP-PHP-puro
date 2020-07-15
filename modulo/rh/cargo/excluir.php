<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>CARGO EXCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='atencao'>ALGUM(NS) REGISTRO(S) NÃO PODE(M) SER(EM) APAGADO(S) POIS CONSTA EM USO POR OUTRO CADASTRO.</font>";

if($passo == 1) {
    foreach ($_POST['chkt_cargo'] as $id_cargo) {
        if(cascate::consultar('id_cargo', 'funcionarios', $id_cargo)) {
            $valor = 2;
        }else {
            $sql = "UPDATE `cargos` SET `ativo` = '0' WHERE `id_cargo` = '$id_cargo' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 1;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
    $sql = "SELECT * 
            FROM `cargos`
            WHERE `ativo` = '1' ORDER BY cargo ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '../../../html/index.php?valor=4'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Cargo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='60%' border="0" cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)" align='center'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='5'>
            Excluir Cargo(s)
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            Cargo
        </td>
        <td>
            <label for='todos'>Todos </label>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
    </tr>
 <?
		for ($i = 0; $i < $linhas; $i++) {
 ?>
    <tr onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" class="linhanormal" align='center'>
        <td align="left">
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_cargo[]' value="<?=$campos[$i]['id_cargo'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
		}
?>
    <tr class="linhacabecalho" align='center'>
        <td colspan='5'>
            <input type="submit" name="cmd_excluir" value="Excluir" title="Excluir" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
	}
}
?>