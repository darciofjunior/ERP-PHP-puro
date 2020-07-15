<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/producao/maquina/maq_vs_operacao/maquina_vs_operacao.php', '../../../../');
$mensagem[1] = "<font class='confirmacao'>OPERAÇÃO(ÕES) INCLUIDO COM SUCESSO.</font>";

if($passo == 1) {
	foreach($_POST['txt_operacao'] as $operacao) {
		//Verifico se essa Operação já foi cadastrada anteriormente para a mesma Máquina ...
		$sql = "SELECT id_maquina_operacao 
				FROM `maquinas_vs_operacoes` 
				WHERE `id_maquina` = '$_POST[id_maquina]' 
				AND `operacao` = '$operacao' LIMIT 1 ";
		$campos = bancos::sql($sql);
		if(count($campos) == 0) {
			$sql = "INSERT INTO `maquinas_vs_operacoes` (`id_maquina_operacao`, `id_maquina`, `operacao`) VALUES (NULL, '$_POST[id_maquina]', '$operacao') ";
			bancos::sql($sql);
		}
	}
?>
	<Script Language = 'Javascript'>
		window.location = 'incluir_operacao.php?id_maquina=<?=$_POST['id_maquina'];?>&valor=1'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Operação(ões) para Máquina ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Variável Global ...
var qtde_operacoes = 0

function validar() {
	var elementos = document.form.elements
	if(typeof(elementos['txt_operacao[]']) != 'undefined') {//Se existir pelo menos 1 Operação ...
		//Prepara a Tela p/ poder gravar no BD ...
		if(typeof(elementos['txt_operacao[]'][0]) == 'undefined') {
			var linhas = 1//Existe apenas 1 único elemento ...
		}else {
			var linhas = (elementos['txt_operacao[]'].length)
		}
		for(i = 0; i < linhas; i++) {
			//Se a Operação não estiver preenchido, então forço o preenchimento do Operação ...
			if(document.getElementById('txt_operacao'+i).value == '') {
				alert('DIGITE A OPERAÇÃO !')
				document.getElementById('txt_operacao'+i).focus()
				return false
			}
		}
	}else {
		alert('INSIRA PELO MENOS UMA OPERAÇÃO !')
		return false
	}
	document.form.submit()
}

function incluir_operacao() {
	var elementos = document.form.elements
	if(typeof(elementos['txt_operacao[]']) != 'undefined') {//Se existir pelo menos 1 Operação ...
		//Prepara a Tela p/ poder gravar no BD ...
		if(typeof(elementos['txt_operacao[]'][0]) == 'undefined') {
			var linhas = 1//Existe apenas 1 único elemento ...
		}else {
			var linhas = (elementos['txt_operacao[]'].length)
		}
		for(i = 0; i < linhas; i++) {
			//Se o Cliente não estiver preenchido, então forço o preenchimento do Cliente ...
			if(document.getElementById('txt_operacao'+i).value == '') {
				alert('DIGITE A OPERAÇÃO !')
				document.getElementById('txt_operacao'+i).focus()
				return false
			}
		}
	}
	qtde_operacoes++
	ajax('div_operacoes.php?qtde_operacoes='+qtde_operacoes, 'div_operacao')
}

function excluir_operacao() {
	qtde_operacoes--
	ajax('div_operacoes.php?qtde_operacoes='+qtde_operacoes, 'div_operacao')
}
</Script>
</head>
<body onLoad="incluir_operacao()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1;'?>" onsubmit="return false">
<input type="hidden" name="id_maquina" value="<?=$_GET['id_maquina'];?>">
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
			Incluir Operação(ões) para Máquina: 
			<font color='yellow'>
			<?
				$sql = "SELECT nome 
						FROM `maquinas` 
						WHERE `id_maquina` = '$_GET[id_maquina]' LIMIT 1 ";
				$campos = bancos::sql($sql);
				echo $campos[0]['nome'];
			?>
			</font>
			<img src = "../../../../imagem/menu/adicao.jpeg" border='0' title="Incluir Operação" alt="Incluir Operação" width="16" height="16" onClick="incluir_operacao()">
		</td>
	</tr>
	<tr class="linhanormal">
		<td colspan="2">
			<div id="div_operacao"></div>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'maquina_vs_operacao.php?passo=1&id_maquina=<?=$_GET['id_maquina'];?>'" class="botao">
			<input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR')" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" onclick="return validar()" style="color:green" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>