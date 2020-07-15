<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/producao/maquina/maq_vs_codigo_maquina/maquina_vs_codigo_maquina.php', '../../../../');
$mensagem[1] = "<font class='confirmacao'>CÓDIGO(S) DE MÁQUINA ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
	//Verifico se esse código já foi cadastrado anteriormente para a mesma Máquina ...
	$sql = "SELECT id_maquina_codigo_maquina 
			FROM `maquinas_vs_codigos_maquinas` 
			WHERE `codigo_maquina` = '$_POST[txt_codigo_maquina]' 
			AND `id_maquina` = '$_POST[hdd_maquina]' 
			AND `id_maquina_codigo_maquina` <> '$_POST[hdd_maquina_codigo_maquina]' LIMIT 1 ";
	$campos = bancos::sql($sql);
	if(count($campos) == 0) {
		$sql = "UPDATE `maquinas_vs_codigos_maquinas` SET `codigo_maquina` = '$_POST[txt_codigo_maquina]' WHERE `id_maquina_codigo_maquina` = '$_POST[hdd_maquina_codigo_maquina]' LIMIT 1 ";
		bancos::sql($sql);
	}
?>
	<Script Language = 'Javascript'>
		window.location = 'alterar_codigo_maquina.php?id_maquina_codigo_maquina=<?=$_POST['hdd_maquina_codigo_maquina'];?>&valor=1'
	</Script>
<?
}else {
	$sql = "SELECT m.nome, mcm.id_maquina, mcm.codigo_maquina 
			FROM `maquinas_vs_codigos_maquinas` mcm 
			INNER JOIN `maquinas` m ON m.id_maquina = mcm.id_maquina 
			WHERE mcm.`id_maquina_codigo_maquina` = '$_GET[id_maquina_codigo_maquina]' LIMIT 1 ";
	$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Código(s) de Máquina ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	if(document.form.txt_codigo_maquina.value == '') {
		alert('DIGITE O CÓDIGO DA MÁQUINA !')
		document.form.txt_codigo_maquina.focus()
		return false
	}
}
</Script>
</head>
<body>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1;'?>" onsubmit="return validar()">
<input type="hidden" name="hdd_maquina_codigo_maquina" value="<?=$_GET['id_maquina_codigo_maquina'];?>">
<input type="hidden" name="hdd_maquina" value="<?=$campos[0]['id_maquina'];?>">
<table border="0" width="750" align="center" cellspacing ='1' cellpadding='1'>
	<tr class="atencao" align='center'>
		<td colspan='2'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<?=$mensagem[$valor];?>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Alterar Código(s) da Máquina: 
				<font color='yellow'>
					<?=$campos[0]['nome'];?>
				</font>
			</font>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<b>C&oacute;digo de M&aacute;quina:</b> 
		</td>
		<td>
			<input type="text" name="txt_codigo_maquina" value="<?=$campos[0]['codigo_maquina'];?>" title="Digite o C&oacute;digo de M&aacute;quina" size="45" maxlength="35" class="caixadetexto">
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'maquina_vs_codigo_maquina.php?passo=1&id_maquina=<?=$campos[0]['id_maquina'];?>'" class="botao">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>