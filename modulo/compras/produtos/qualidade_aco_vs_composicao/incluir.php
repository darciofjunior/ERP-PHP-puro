<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>COMPOSI��O(�ES) INCLU�DA(S) COM SUCESSO.</font>";

if($passo == 1) {
	switch($opt_opcao) {
		case 1:
			//Aqui trago todas as Qualidades de A�o que n�o possuem Composi��o ...
			$sql = "SELECT id_qualidade_aco, nome, valor_perc 
					FROM `qualidades_acos` 
					WHERE `nome` LIKE '%$txt_consultar%' 
					AND `ativo` = '1' 
					AND `id_qualidade_aco` 
					NOT IN (SELECT qa.id_qualidade_aco 
							FROM `qualidades_acos` qa 
							INNER JOIN `qualidades_acos_vs_composicoes` qac ON qac.id_qualidade_aco = qa.id_qualidade_aco) 
					ORDER BY nome ";
		break;
		default:
			//Aqui trago todas as Qualidades de A�o que n�o possuem Composi��o ...
			$sql = "SELECT id_qualidade_aco, nome, valor_perc 
					FROM `qualidades_acos` 
					WHERE `ativo` = '1' 
					AND `id_qualidade_aco` 
					NOT IN (SELECT qa.id_qualidade_aco 
							FROM `qualidades_acos` qa 
							INNER JOIN `qualidades_acos_vs_composicoes` qac ON qac.id_qualidade_aco = qa.id_qualidade_aco) 
					ORDER BY nome ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
		<Script Language = 'JavaScript'>
			window.location = 'incluir.php?valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Incluir Composi��o(�es) p/ Qualidade de A�o ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<table width='780' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
	<tr align='center'>
		<td colspan='3'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
				<b><?=$mensagem[$valor];?></b>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<font color='#FFFFFF' size='-1'>
				Incluir Composi��o(�es) p/ Qualidade de A�o
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan='2'>
			<font color='#FFFFFF' size='-1'>
				Nome
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Percentual
			</font>
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
			$url = "incluir.php?passo=2&id_qualidade_aco=".$campos[$i]['id_qualidade_aco'];
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td width='10' onclick="window.location = '<?=$url;?>'">
			<a href="<?=$url;?>" class="link">
				<img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
			</a>
		</td>
		<td onclick="window.location = '<?=$url;?>'">
			<a href="<?=$url;?>" class="link">
				<?=$campos[$i]['nome'];?>
			</a>
		</td>
		<td>
			<?=number_format($campos[$i]['valor_perc'], 2, ',', '.').' %';?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='3'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class="botao">
		</td>
	</tr>
</table>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if($passo == 2) {
?>
<html>
<head>
<title>.:: Incluir Composi��o(�es) p/ Qualidade de A�o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	var elementos 	= document.form.elements
	var preenchido	= 0
	//Verifico se existe pelo menos 1 item que foi preenchido ...
	for(i = 0; i < elementos.length; i++) {
		if(elementos[i].type == 'text') {
			if(elementos[i].value != '') {//Existe preenchido ...
				preenchido = 1
				break
			}
		}
	}
	//For�o o preenchimento de alguma composi��o qu�mica ...
	if(preenchido == 0) {
		alert('DIGITE ALGUMA COMPOSI��O QU�MICA !')
		elementos[1].focus()
		return false
	}
	//Tratamento com as caixas de texto para poder gravar no BD ...
	for(i = 0; i < elementos.length; i++) {
		if(elementos[i].type == 'text') elementos[i].value = elementos[i].value.replace(',', '.')
	}
}
</Script>
</head>
<body onload="document.form.elements[1].focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<!--Controles de Tela-->
<input type='hidden' name='id_qualidade_aco' value='<?=$_GET['id_qualidade_aco'];?>'>
<!--*****************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			Incluir Composi��o(�es) p/ Qualidade de A�o 
		</td>
	</tr>
	<tr class="linhanormal">
		<td>
			<font color="darkblue">
				<b>QUALIDADE DO A�O:</b>
			</font>
		</td>
		<td>
			<font color="darkblue" size="2">
			<?
				//Sql para pegar o nome do a�o ...
				$sql = "SELECT `nome`, `valor_perc` 
						FROM `qualidades_acos` 
						WHERE `id_qualidade_aco` = '$_GET[id_qualidade_aco]' 
						AND `ativo` = '1' ORDER BY `nome` LIMIT 1 ";
				$campos = bancos::sql($sql);
				echo '<b>'.$campos[0]['nome'].' - '.number_format($campos[0]['valor_perc'], 2, ',', '.').' %</b>';
			?>
			</font>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Carbono:</td>
		<td>
			De: 
			<input type="text" name="txt_carbono1" title="Digite o Carbono 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_carbono2" title="Digite o Carbono 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Sil�cio:</td>
		<td>
			De: 
			<input type="text" name="txt_silicio1" title="Digite o Sil�cio 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_silicio2" title="Digite o Sil�cio 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>	
	<tr class="linhanormal">
		<td>Mangan�s:</td>
		<td>
			De: 
			<input type="text" name="txt_manganes1" title="Digite o Mangan�s 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_manganes2" title="Digite o Mangan�s 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>F�sforo:</td>
		<td>
			De: 
			<input type="text" name="txt_fosforo1" title="Digite o F�sforo 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_fosforo2" title="Digite o F�sforo 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Enxofre:</td>
		<td>
			De: 
			<input type="text" name="txt_enxofre1" title="Digite o Enxofre 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_enxofre2" title="Digite o Enxofre 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Cromo:</td>
		<td>
			De: 
			<input type="text" name="txt_cromo1" title="Digite o Cromo 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_cromo2" title="Digite o Cromo 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>		
	<tr class="linhanormal">
		<td>N�quel:</td>
		<td>
			De: 
			<input type="text" name="txt_niquel1" title="Digite o N�quel 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_niquel2" title="Digite o N�quel 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>				
	<tr class="linhanormal">
		<td>Molibd�nio:</td>
		<td>
			De: 
			<input type="text" name="txt_molibdenio1" title="Digite o Molibd�nio 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_molibdenio2" title="Digite o Molibd�nio 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Tungst�nio:</td>
		<td>
			De: 
			<input type="text" name="txt_tungstenio1" title="Digite o Tungst�nio 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_tungstenio2" title="Digite o Tungst�nio 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Tit�nio:</td>
		<td>
			De: 
			<input type="text" name="txt_titanio1" title="Digite o Tit�nio 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_titanio2" title="Digite o Tit�nio 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Van�dio:</td>
		<td>
			De: 
			<input type="text" name="txt_vanadio1" title="Digite o Van�dio 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_vanadio2" title="Digite o Van�dio 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Cobre:</td>
		<td>
			De: 
			<input type="text" name="txt_cobre1" title="Digite o Cobre 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_cobre2" title="Digite o Cobre 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Alum�nio:</td>
		<td>
			De: 
			<input type="text" name="txt_aluminio1" title="Digite o Alum�nio 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_aluminio2" title="Digite o Alum�nio 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Cobalto:</td>
		<td>
			De: 
			<input type="text" name="txt_cobalto1" title="Digite o Cobalto 1" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
			� 
			<input type="text" name="txt_cobalto2" title="Digite o Cobalto 2" size="9" maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
		</td>
	</tr>																																			
    <tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
			<input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.elements[1].focus()" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
	$sql = "INSERT INTO `qualidades_acos_vs_composicoes` (id_qualidade_aco_composicao, id_qualidade_aco, carbono1, carbono2, silicio1, silicio2, manganes1, manganes2, fosforo1, fosforo2, enxofre1, enxofre2, cromo1, cromo2, niquel1, niquel2,
			molibdenio1, molibdenio2, tungstenio1, tungstenio2, titanio1, titanio2, vanadio1, vanadio2, cobre1, cobre2, aluminio1, aluminio2, cobalto1, cobalto2) VALUES (null, '$_POST[id_qualidade_aco]', '$_POST[txt_carbono1]', '$_POST[txt_carbono2]',
			'$_POST[txt_silicio1]', '$_POST[txt_silicio2]', '$_POST[txt_manganes1]', '$_POST[txt_manganes2]', '$_POST[txt_fosforo1]', '$_POST[txt_fosforo2]', '$_POST[txt_enxofre1]', '$_POST[txt_enxofre2]', '$_POST[txt_cromo1]', '$_POST[txt_cromo2]',
			'$_POST[txt_niquel1]', '$_POST[txt_niquel2]', '$_POST[txt_molibdenio1]', '$_POST[txt_molibdenio2]', '$_POST[txt_tungstenio1]', '$_POST[txt_tungstenio2]', '$_POST[txt_titanio1]', '$_POST[txt_titanio2]', '$_POST[txt_vanadio1]', '$_POST[txt_vanadio2]', '$_POST[txt_cobre1]', '$_POST[txt_cobre2]', 
			'$_POST[txt_aluminio1]', '$_POST[txt_aluminio2]', '$_POST[txt_cobalto1]', '$_POST[txt_cobalto2]')";
	bancos::sql($sql);
?>
	<Script Language = 'JavaScript'>
		window.location = 'incluir.php?valor=2'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Composi��o(�es) p/ Qualidade de A�o ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
	if(document.form.opcao.checked == true) {
		document.form.opt_opcao.disabled = true
		document.form.txt_consultar.disabled = true
		document.form.txt_consultar.value = ''
	}else {
		document.form.opt_opcao.disabled = false
		document.form.txt_consultar.disabled = false
		document.form.txt_consultar.value = ''
                document.form.txt_consultar.focus()
	}
}

function validar() {
//Consultar
	if(document.form.txt_consultar.disabled == false) {
		if(document.form.txt_consultar.value == '') {
			alert('DIGITE O CAMPO CONSULTAR !')
			document.form.txt_consultar.focus()
			return false
		}
	}
}
</Script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho'>
		<td colspan="2" align='center'>
			<font color='#FFFFFF' size='-1'>
				Incluir Composi��o(�es) p/ Qualidade de A�o
			</font>
		</td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="1" title="Consultar Qualidade A�o por: Nome" onclick="document.form.txt_consultar.focus()" id='label' checked>
			<label for="label">Nome</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' value='1' title="Consultar todas as taxas desconto financeiro" onclick='limpar()' id='label2' class="checkbox">
			<label for="label2">Todos os registros</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>