<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='confirmacao'>TIPO DE VALE TRANSPORTE EXCLUÍDO COM SUCESSO.</font>";

if($passo == 1) {
    foreach($_POST['chkt_vale_transporte'] as $id_vale_transporte) {
        $sql = "UPDATE `vales_transportes` SET `ativo` = '0' WHERE `id_vale_transporte` = '$id_vale_transporte' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php?valor=1'
    </Script>
<?
}else {
    $sql = "SELECT * 
            FROM `vales_transportes` 
            WHERE `ativo` = '1' ORDER BY tipo_vt ";
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
<title>.:: Excluir Vale(s) Transporte(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='70%' border="0" cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)" align='center'>
    <tr align='center'>
        <td colspan='3'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            Excluir Vale(s) Transporte(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Tipo de VT
        </td>
        <td>
            Valor Unitário
        </td>
        <td>
            <label for='todos'>Todos </label>
            <input type="checkbox" name="chkt" onclick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
    </tr>
 <?
        for ($i = 0; $i < $linhas; $i++) {
 ?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align="center">
		<td align="left">
			<?=$campos[$i]['tipo_vt'];?>
		</td>
		<td align="right">
			<?='R$ '.number_format($campos[$i]['valor_unitario'], 2, ',', '.');?>
		</td>
		<td>
			<input type='checkbox' name='chkt_vale_transporte[]' value="<?=$campos[$i]['id_vale_transporte'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
		</td>
	</tr>
<?
        }
?>
	<tr class="linhacabecalho" align="center">
            <td colspan='3'>
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