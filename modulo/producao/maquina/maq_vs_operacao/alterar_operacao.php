<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/producao/maquina/maq_vs_operacao/maquina_vs_operacao.php', '../../../../');
$mensagem[1] = "<font class='confirmacao'>OPERAÇÃO(ÕES) ALTERADA(S) COM SUCESSO.</font>";

if($passo == 1) {
	//Verifico se essa Operação já foi cadastrado anteriormente para a mesma Máquina ...
	$sql = "SELECT id_maquina_operacao 
			FROM `maquinas_vs_operacoes` 
			WHERE `operacao` = '$_POST[txt_operacao]' 
			AND `id_maquina` = '$_POST[hdd_maquina]' 
			AND `id_maquina_operacao` <> '$_POST[hdd_maquina_operacao]' LIMIT 1 ";
	$campos = bancos::sql($sql);
	if(count($campos) == 0) {
		$sql = "UPDATE `maquinas_vs_operacoes` SET `operacao` = '$_POST[txt_operacao]' WHERE `id_maquina_operacao` = '$_POST[hdd_maquina_operacao]' LIMIT 1 ";
		bancos::sql($sql);
	}
?>
	<Script Language = 'Javascript'>
		window.location = 'alterar_operacao.php?id_maquina_operacao=<?=$_POST['hdd_maquina_operacao'];?>&valor=1'
	</Script>
<?
}else {
	$sql = "SELECT m.nome, mo.id_maquina, mo.operacao 
			FROM `maquinas_vs_operacoes` mo 
			INNER JOIN `maquinas` m ON m.id_maquina = mo.id_maquina 
			WHERE mo.`id_maquina_operacao` = '$_GET[id_maquina_operacao]' LIMIT 1 ";
	$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Operação(ões) de Máquina ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	if(document.form.txt_operacao.value == '') {
		alert('DIGITE A OPERAÇÃO DA MÁQUINA !')
		document.form.txt_operacao.focus()
		return false
	}
}
</Script>
</head>
<body>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1;'?>" onsubmit="return validar()">
<input type="hidden" name="hdd_maquina_operacao" value="<?=$_GET['id_maquina_operacao'];?>">
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
				Alterar Operação(ões) da Máquina: 
				<font color='yellow'>
					<?=$campos[0]['nome'];?>
				</font>
			</font>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<b>Operação:</b> 
		</td>
		<td>
			<input type="text" name="txt_operacao" value="<?=$campos[0]['operacao'];?>" title="Digite a Operação" size="65" maxlength="60" class="caixadetexto">
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'maquina_vs_operacao.php?passo=1&id_maquina=<?=$campos[0]['id_maquina'];?>'" class="botao">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>