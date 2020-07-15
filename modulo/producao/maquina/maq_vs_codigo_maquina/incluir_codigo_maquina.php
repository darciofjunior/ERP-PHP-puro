<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/producao/maquina/maq_vs_codigo_maquina/maquina_vs_codigo_maquina.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CÓDIGO(S) DE MÁQUINA INCLUIDO COM SUCESSO.</font>";

if($passo == 1) {
	foreach($_POST['txt_codigo_maquina'] as $codigo_maquina) {
		//Verifico se esse código já foi cadastrado anteriormente para a mesma Máquina ...
		$sql = "SELECT id_maquina_codigo_maquina 
				FROM `maquinas_vs_codigos_maquinas` 
				WHERE `id_maquina` = '$_POST[id_maquina]' 
				AND `codigo_maquina` = '$codigo_maquina' LIMIT 1 ";
		$campos = bancos::sql($sql);
		if(count($campos) == 0) {
			$sql = "INSERT INTO `maquinas_vs_codigos_maquinas` (`id_maquina_codigo_maquina`, `id_maquina`, `codigo_maquina`) VALUES (NULL, '$_POST[id_maquina]', '$codigo_maquina') ";
			bancos::sql($sql);
		}
	}
?>
	<Script Language = 'Javascript'>
		window.location = 'incluir_codigo_maquina.php?id_maquina=<?=$_POST['id_maquina'];?>&valor=1'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Código(s) para Máquina ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Variável Global ...
var qtde_codigos_maquina = 0

function validar() {
	var elementos = document.form.elements
	if(typeof(elementos['txt_codigo_maquina[]']) != 'undefined') {//Se existir pelo menos 1 Código de Máquina ...
		//Prepara a Tela p/ poder gravar no BD ...
		if(typeof(elementos['txt_codigo_maquina[]'][0]) == 'undefined') {
			var linhas = 1//Existe apenas 1 único elemento ...
		}else {
			var linhas = (elementos['txt_codigo_maquina[]'].length)
		}
		for(i = 0; i < linhas; i++) {
			//Se o Código de Máquina não estiver preenchido, então forço o preenchimento do Código de Máquina ...
			if(document.getElementById('txt_codigo_maquina'+i).value == '') {
				alert('DIGITE O CÓDIGO DE MÁQUINA !')
				document.getElementById('txt_codigo_maquina'+i).focus()
				return false
			}
		}
	}else {
		alert('INSIRA PELO MENOS UM CÓDIGO DE MÁQUINA !')
		return false
	}
	document.form.submit()
}

function incluir_codigo_maquina() {
	var elementos = document.form.elements
	if(typeof(elementos['txt_codigo_maquina[]']) != 'undefined') {//Se existir pelo menos 1 Código de Máquina ...
		//Prepara a Tela p/ poder gravar no BD ...
		if(typeof(elementos['txt_codigo_maquina[]'][0]) == 'undefined') {
			var linhas = 1//Existe apenas 1 único elemento ...
		}else {
			var linhas = (elementos['txt_codigo_maquina[]'].length)
		}
		for(i = 0; i < linhas; i++) {
			//Se o Cliente não estiver preenchido, então forço o preenchimento do Cliente ...
			if(document.getElementById('txt_codigo_maquina'+i).value == '') {
				alert('DIGITE O CÓDIGO DE MÁQUINA !')
				document.getElementById('txt_codigo_maquina'+i).focus()
				return false
			}
		}
	}
	qtde_codigos_maquina++
	ajax('div_codigos_maquina.php?qtde_codigos_maquina='+qtde_codigos_maquina, 'div_codigo_maquina')
}

function excluir_codigo_maquina() {
	qtde_codigos_maquina--
	ajax('div_codigos_maquina.php?qtde_codigos_maquina='+qtde_codigos_maquina, 'div_codigo_maquina')
}
</Script>
</head>
<body onload="incluir_codigo_maquina()">
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
			Incluir Código(s) para Máquina: 
			<font color='yellow'>
			<?
				$sql = "SELECT nome 
						FROM `maquinas` 
						WHERE `id_maquina` = '$_GET[id_maquina]' LIMIT 1 ";
				$campos = bancos::sql($sql);
				echo $campos[0]['nome'];
			?>
			</font>
			<img src = "../../../../imagem/menu/adicao.jpeg" border='0' title="Incluir Código para Máquina" alt="Incluir Código para Máquina" width="16" height="16" onClick="incluir_codigo_maquina()">
		</td>
	</tr>
	<tr class="linhanormal">
		<td colspan="2">
			<div id="div_codigo_maquina"></div>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'maquina_vs_codigo_maquina.php?passo=1&id_maquina=<?=$_GET['id_maquina'];?>'" class="botao">
			<input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR')" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" onclick="return validar()" style="color:green" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>