<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos/qualidade_aco_vs_composicao/consultar.php', '../../../../');
session_start('funcionarios');

//Busco todos os campos de Composi��o da Qualidade de A�o passada por par�metro ...
$sql = "SELECT * 
		FROM `qualidades_acos_vs_composicoes` 
		WHERE `id_qualidade_aco` = '$_GET[id_qualidade_aco]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Detalhes de Composi��o(�es) p/ Qualidade de A�o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table border="0" width="780" align="center" cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			Detalhes de Composi��o(�es) p/ Qualidade de A�o 
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
				$campos_qualidade = bancos::sql($sql);
				echo '<b>'.$campos_qualidade[0]['nome'].' - '.number_format($campos_qualidade[0]['valor_perc'], 2, ',', '.').' %</b>';
			?>
			</font>
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Carbono:</td>
		<td>
			De: 
			<input type="text" name="txt_carbono1" value='<?if($campos[0]['carbono1'] != '0.000') echo number_format($campos[0]['carbono1'], 3, ',', '.');?>' title="Carbono 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_carbono2" value='<?if($campos[0]['carbono2'] != '0.000') echo number_format($campos[0]['carbono2'], 3, ',', '.');?>'title="Carbono 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Sil�cio:</td>
		<td>
			De: 
			<input type="text" name="txt_silicio1" value='<?if($campos[0]['silicio1'] != '0.000') echo number_format($campos[0]['silicio1'], 3, ',', '.');?>' title="Sil�cio 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_silicio2" value='<?if($campos[0]['silicio2'] != '0.000') echo number_format($campos[0]['silicio2'], 3, ',', '.');?>'title="Sil�cio 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>	
	<tr class="linhanormal">
		<td>Mangan�s:</td>
		<td>
			De: 
			<input type="text" name="txt_manganes1" value='<?if($campos[0]['manganes1'] != '0.000') echo number_format($campos[0]['manganes1'], 3, ',', '.');?>' title="Mangan�s 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_manganes2" value='<?if($campos[0]['manganes2'] != '0.000') echo number_format($campos[0]['manganes2'], 3, ',', '.');?>' title="Mangan�s 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>F�sforo:</td>
		<td>
			De: 
			<input type="text" name="txt_fosforo1" value='<?if($campos[0]['fosforo1'] != '0.000') echo number_format($campos[0]['fosforo1'], 3, ',', '.');?>' title="F�sforo 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_fosforo2" value='<?if($campos[0]['fosforo2'] != '0.000') echo number_format($campos[0]['fosforo2'], 3, ',', '.');?>' title="F�sforo 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Enxofre:</td>
		<td>
			De: 
			<input type="text" name="txt_enxofre1" value='<?if($campos[0]['enxofre1'] != '0.000') echo number_format($campos[0]['enxofre1'], 3, ',', '.');?>' title="Enxofre 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_enxofre2" value='<?if($campos[0]['enxofre2'] != '0.000') echo number_format($campos[0]['enxofre2'], 3, ',', '.');?>' title="Enxofre 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Cromo:</td>
		<td>
			De: 
			<input type="text" name="txt_cromo1" value='<?if($campos[0]['cromo1'] != '0.000') echo number_format($campos[0]['cromo1'], 3, ',', '.');?>' title="Cromo 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_cromo2" value='<?if($campos[0]['cromo2'] != '0.000') echo number_format($campos[0]['cromo2'], 3, ',', '.');?>' title="Cromo 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>		
	<tr class="linhanormal">
		<td>N�quel:</td>
		<td>
			De: 
			<input type="text" name="txt_niquel1" value='<?if($campos[0]['niquel1'] != '0.000') echo number_format($campos[0]['niquel1'], 3, ',', '.');?>' title="N�quel 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_niquel2" value='<?if($campos[0]['niquel2'] != '0.000') echo number_format($campos[0]['niquel2'], 3, ',', '.');?>' title="N�quel 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>				
	<tr class="linhanormal">
		<td>Molibd�nio:</td>
		<td>
			De: 
			<input type="text" name="txt_molibdenio1" value='<?if($campos[0]['molibdenio1'] != '0.000') echo number_format($campos[0]['molibdenio1'], 3, ',', '.');?>' title="Molibd�nio 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_molibdenio2" value='<?if($campos[0]['molibdenio2'] != '0.000') echo number_format($campos[0]['molibdenio2'], 3, ',', '.');?>' title="Molibd�nio 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Tungst�nio:</td>
		<td>
			De: 
			<input type="text" name="txt_tungstenio1" value='<?if($campos[0]['tungstenio1'] != '0.000') echo number_format($campos[0]['tungstenio1'], 3, ',', '.');?>' title="Tungst�nio 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_tungstenio2" value='<?if($campos[0]['tungstenio2'] != '0.000') echo number_format($campos[0]['tungstenio2'], 3, ',', '.');?>' title="Tungst�nio 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Tit�nio:</td>
		<td>
			De: 
			<input type="text" name="txt_titanio1" value='<?if($campos[0]['titanio1'] != '0.000') echo number_format($campos[0]['titanio1'], 3, ',', '.');?>' title="Tit�nio 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_titanio2" value='<?if($campos[0]['titanio2'] != '0.000') echo number_format($campos[0]['titanio2'], 3, ',', '.');?>' title="Tit�nio 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Van�dio:</td>
		<td>
			De: 
			<input type="text" name="txt_vanadio1" value='<?if($campos[0]['vanadio1'] != '0.000') echo number_format($campos[0]['vanadio1'], 3, ',', '.');?>' title="Van�dio 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_vanadio2" value='<?if($campos[0]['vanadio2'] != '0.000') echo number_format($campos[0]['vanadio2'], 3, ',', '.');?>' title="Van�dio 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Cobre:</td>
		<td>
			De: 
			<input type="text" name="txt_cobre1" value='<?if($campos[0]['cobre1'] != '0.000') echo number_format($campos[0]['cobre1'], 3, ',', '.');?>' title="Cobre 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_cobre2" value='<?if($campos[0]['cobre2'] != '0.000') echo number_format($campos[0]['cobre2'], 3, ',', '.');?>' title="Cobre 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Alum�nio:</td>
		<td>
			De: 
			<input type="text" name="txt_aluminio1" value='<?if($campos[0]['aluminio1'] != '0.000') echo number_format($campos[0]['aluminio1'], 3, ',', '.');?>' title="Alum�nio 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_aluminio2" value='<?if($campos[0]['aluminio2'] != '0.000') echo number_format($campos[0]['aluminio2'], 3, ',', '.');?>' title="Alum�nio 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
	<tr class="linhanormal">
		<td>Cobalto:</td>
		<td>
			De: 		
			<input type="text" name="txt_cobalto1" value='<?if($campos[0]['cobalto1'] != '0.000') echo number_format($campos[0]['cobalto1'], 3, ',', '.');?>' title="Cobalto 1" size="9" maxlength='7' class='textdisabled' disabled> %
			� 
			<input type="text" name="txt_cobalto2" value='<?if($campos[0]['cobalto2'] != '0.000') echo number_format($campos[0]['cobalto2'], 3, ',', '.');?>' title="Cobalto 2" size="9" maxlength='7' class='textdisabled' disabled> %
		</td>
	</tr>
    <tr class="linhacabecalho" align="center">
		<td colspan="2">
			&nbsp;
		</td>
	</tr>
</table>
</body>
</html>