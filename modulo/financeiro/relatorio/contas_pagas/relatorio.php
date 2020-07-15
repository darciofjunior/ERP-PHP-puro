<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
session_start('funcionarios');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Relatório de Contas Pagas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
	if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
			return false
	}
//Data Final
	if(!data('form', 'txt_data_final', '4000', 'FIM')) {
		return false
	}
//Empresa
	if (!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
		return false
	}
	var data_inicial 	= document.form.txt_data_inicial.value
	var data_final 		= document.form.txt_data_final.value
	data_inicial 		= data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
	data_final 			= data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
	data_inicial 		= eval(data_inicial)
	data_final 			= eval(data_final)
	
	if(data_final < data_inicial) {
		alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
	var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
	if(dias > 365) {
		alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
}
</Script>
</head>
<body>
<form name="form" method="post" action="<?=$PHP_SELF;?>" onsubmit="return validar()">
<table border="0" width="980" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan='2'>
			Relatório de Contas Pagas
		</td>
	</tr>
	<tr class='linhadestaque'>
		<td colspan='2'>
			<?
				if(empty($_POST['txt_data_inicial'])) {
					$datas 			= genericas::retornar_data_relatorio(1);
					$data_inicial 	= $datas['data_inicial'];
					$data_final 	= $datas['data_final'];
				}else {
					$data_inicial 	= $_POST['txt_data_inicial'];
					$data_final 	= $_POST['txt_data_final'];
				}
			?>
			<p>Data Inicial: 
			<input type="text" name="txt_data_inicial" value="<?=$data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
			<img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
			Data Final:
			<input type="text" name="txt_data_final" value="<?=$data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
			<img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
			&nbsp;
			Empresa: 
			<?
				if($_POST['cmb_empresa'] == 1) {
					$selecteda = 'selected';
				}else if($_POST['cmb_empresa'] == 2) {
					$selectedt = 'selected';
				}else if($_POST['cmb_empresa'] == 4) {
					$selectedg = 'selected';
				}else {
					$selectedtt = 'selected';
				}
			?>
			<select name="cmb_empresa" title="Selecione a Empresa" class="combo">
				<option value='%' style='color:red' <?=$selectedtt;?>>TODAS EMPRESAS</option>
				<option value='1' <?=$selecteda;?>>ALBAFER</option>
				<option value='2' <?=$selectedt;?>>TOOL MASTER</option>
				<option value='4' <?=$selectedg;?>>GRUPO</option>
			</select>
			&nbsp;
			<input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
		</td>
	</tr>
<?
	if(empty($_POST['cmb_empresa'])) {
?>
	<tr class="erro" align='center'>
		<td>
			CLIQUE EM CONSULTAR PARA GERAR O RELATÓRIO.
		</td>
	</tr>
<?
	}else {
		$data_inicial 	= data::datatodate($data_inicial, '-');
		$data_final 	= data::datatodate($data_final, '-');
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan='2'>
			GRUPO TIPO FIXO
		</td>
	</tr>
<?
		$sql = "SELECT id_grupo, nome 
				FROM `grupos` 
				WHERE `tipo_custo` = 'F' 
				AND `ativo` = '1' 
				ORDER BY nome ";//Traz Custos do Tipo Fixo ...
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" align='center'>
		<td>
			<?=$campos[$i]['nome'];?>
		</td>
		<td align='right'>
		<?
			$sql = "SELECT SUM(caq.valor) AS total_pago 
					FROM `contas_apagares_quitacoes` caq 
					INNER JOIN `contas_apagares` ca ON ca.id_conta_apagar = caq.id_conta_apagar AND ca.id_empresa LIKE '$_POST[cmb_empresa]' AND ca.id_grupo = '".$campos[$i]['id_grupo']."' 
					WHERE `caq`.data BETWEEN '$data_inicial' AND '$data_final' ";
			$campos_pago = bancos::sql($sql);
			echo number_format($campos_pago[0]['total_pago'], 2, ',', '.');
		?>
		</td>
	</tr>
<?			
		}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan='2'>
			GRUPO TIPO VARIÁVEL
		</td>
	</tr>
<?
		$sql = "SELECT id_grupo, nome 
				FROM `grupos` 
				WHERE `tipo_custo` = 'V' 
				AND `ativo` = '1' 
				ORDER BY nome ";//Traz Custos do Tipo Variável ...
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" align='center'>
		<td>
			<?=$campos[$i]['nome'];?>
		</td>
		<td align='right'>
		<?
			$sql = "SELECT SUM(caq.valor) AS total_pago 
					FROM `contas_apagares_quitacoes` caq 
					INNER JOIN `contas_apagares` ca ON ca.id_conta_apagar = caq.id_conta_apagar AND ca.id_empresa LIKE '$_POST[cmb_empresa]' AND ca.id_grupo = '".$campos[$i]['id_grupo']."' 
					WHERE `caq`.data BETWEEN '$data_inicial' AND '$data_final' ";
			$campos_pago = bancos::sql($sql);
			echo number_format($campos_pago[0]['total_pago'], 2, ',', '.');
		?>
		</td>
	</tr>
<?			
		}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan='2'>
			GRUPO TIPO PROCESSO
		</td>
	</tr>
<?
		$sql = "SELECT id_grupo, nome 
				FROM `grupos` 
				WHERE `tipo_custo` = 'P' 
				AND `ativo` = '1' 
				ORDER BY nome ";//Traz Custos do Tipo Processo ...
		$campos = bancos::sql($sql);
		$linhas = count($campos);
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" align='center'>
		<td>
			<?=$campos[$i]['nome'];?>
		</td>
		<td align='right'>
		<?
			$sql = "SELECT SUM(caq.valor) AS total_pago 
					FROM `contas_apagares_quitacoes` caq 
					INNER JOIN `contas_apagares` ca ON ca.id_conta_apagar = caq.id_conta_apagar AND ca.id_empresa LIKE '$_POST[cmb_empresa]' AND ca.id_grupo = '".$campos[$i]['id_grupo']."' 
					WHERE `caq`.data BETWEEN '$data_inicial' AND '$data_final' ";
			$campos_pago = bancos::sql($sql);
			echo number_format($campos_pago[0]['total_pago'], 2, ',', '.');
		?>
		</td>
	</tr>
<?			
		}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan='2'>
			&nbsp;
		</td>
	</tr>
<?
	}
?>
</table>
</form>
</body>
</html>