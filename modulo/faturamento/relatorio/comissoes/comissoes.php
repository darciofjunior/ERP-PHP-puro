<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/genericas.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/data.php');
session_start('funcionarios');
/*Significa que essa Tela, foi acessada de algum outro lugar, fora do Menu, ent�o eu n�o 
verifico se o usu�rio tem essa permiss�o na Sess�o ...*/
if($veio_outra_tela != 1) segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1]        = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$valor_dolar_dia    = genericas::moeda_dia('dolar');
?>
<html>
<head>
<title>.:: Relat�rio de Comiss�es ::.</title>
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
	if(!data('form', 'txt_data_inicial', '4000', 'IN�CIO')) {
			return false
	}
//Data Final
	if(!data('form', 'txt_data_final', '4000', 'FIM')) {
		return false
	}
//Representante ...
//Aqui eu pergunto se deseja gerar comiss�o p/todos os representantes da Empresa ...
	if(document.form.cmb_representante.value == '') {
		var resposta = confirm('DESEJA GERAR COMISS�O P/ TODO(S) O(S) REPRESENTANTE(S) ?\n\nATEN��O: ISTO IMPLICAR� NA VELOCIDADE DO SISTEMA E NOS C�LCULOS FINANCEIROS ')
		if(resposta == true) {//Se desejar gerar a Comiss�o ...
//Tenho que passar as Datas no formato Americano p/ n�o dar erro l� no PDF ...
			var data_inicial = document.form.txt_data_inicial.value
			var data_final = document.form.txt_data_final.value
			data_inicial = data_inicial.substr(6,4) + '-' + data_inicial.substr(3,2) + '-' + data_inicial.substr(0,2)
			data_final = data_final.substr(6,4) + '-' + data_final.substr(3,2) + '-' + data_final.substr(0,2)
			var cmb_representante = ''
//			var cmb_empresa = document.form.cmb_empresa.value
//			nova_janela('relatorio_pdf/relatorio.php?data_inicial='+data_inicial+'&data_final='+data_final+'&cmb_representante='+cmb_representante+'&cmb_empresa='+cmb_empresa, 'CONSULTAR', 'F')
			nova_janela('relatorio_pdf/relatorio.php?data_inicial='+data_inicial+'&data_final='+data_final+'&cmb_representante='+cmb_representante, 'CONSULTAR', 'F')
			return false
		}else {//Se n�o quiser gerar a Comiss�o p/ todo(s) o(s) representante(s) ent�o ...
			if(!combo('form', 'cmb_representante', '', 'SELECIONE UM REPRESENTANTE !')) {
				return false
			}
		}
	}
////Empresa
//	if (!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
//		return false
//	}
	var data_inicial = document.form.txt_data_inicial.value
	var data_final = document.form.txt_data_final.value
	data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
	data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
	data_inicial = eval(data_inicial)
	data_final = eval(data_final)
	
	if(data_final < data_inicial) {
		alert('DATA FINAL INV�LIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
/**Verifico se o intervalo entre Datas � > do que 5 anos. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
	var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
	if(dias > (365 * 5)) {
		alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A CINCO ANOS !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
/*Aqui eu habilito essa combo pois tem vezes que a mesma vem travada devido ter vindo de 
acesso por outra tela ...*/
 	document.form.cmb_representante.disabled = false
}
</Script>
</head>
<?
/*Se essa op��o estiver marcada, ent�o eu fa�o o sistema dar um clique disparado no bot�o 
de Consultar, mas somente na Primeira vez em que se carrega a Tela ...*/
    if(empty($cmd_consultar)) if($veio_outra_tela == 1) $onload = "document.form.cmd_consultar.click()";
    $onload = ";alert('ESSE RELAT�RIO J� N�O � MAIS UTILIZADO !!! ESSE � O ANTIGO RELAT�RIO DE COMISS�O ! ')";
?>
<body onload="<?=$onload;?>">
<form name="form" method="post" action='' onsubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<!--Se essa op��o estiver marcada, ent�o eu tenho que manter a combo desabilitada
Tamb�m significa que essa Tela, foi acessada de algum outro lugar, fora do Menu-->
<input type='hidden' name='veio_outra_tela' value='<?=$veio_outra_tela;?>'>
<table border="0" width="980" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='9'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="9">
			<?
				if($cmb_empresa == 1) {
					$empresa = 'ALBAFER';
				}else if($cmb_empresa == 2) {
					$empresa = 'TOOL MASTER';
				}else if($cmb_empresa == 4) {
					$empresa = 'GRUPO';
				}else {
					$empresa = 'TODAS EMPRESAS';
				}
			?>
			Relat&oacute;rio de Comiss&otilde;es - 
			<font color="yellow">
				<?=$empresa;?>
			</font>
			<font color='#FFFFFF' size='-1'>&nbsp;</font> 
		</td>
	</tr>
	<tr class='linhadestaque'>
		<td colspan='9'>
			<?
				if(empty($txt_data_inicial)) {
					$datas = genericas::retornar_data_relatorio(1);
					$txt_data_inicial = $datas['data_inicial'];
					$txt_data_final = $datas['data_final'];
				}
				$data_inicial=data::datatodate($txt_data_inicial,"-");
				$data_final=data::datatodate($txt_data_final,"-");
			?>
			<p>Data Inicial: 
			<input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
			<img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
			Data Final:
			<input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
			<img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
			Relat�rio por:
			<?
//Significa que essa Tela, foi acessada de algum outro lugar, fora do Menu ...
				if($veio_outra_tela == 1) {
					$class = 'textdisabled';
					$disabled = 'disabled';
				}else {
					$class = 'combo';
					$disabled = '';
				}
			?> 
			<select name="cmb_representante" title="Selecione o Representante" class="<?=$class;?>" <?=$disabled;?>>
			<?
				$sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
						FROM `representantes` 
						WHERE `ativo` = '1' ORDER BY nome_fantasia ";
				echo combos::combo($sql, $cmb_representante);
			?>
			</select>
			<!-- N�o posso habilitar por causa da pr�mia��o do vendedor que � somanda por todas as empresa
			Empresa: 
			<select name="cmb_empresa" title="Selecione a Empresa" class="combo">
			<option value="t">TODAS EMPRESAS</option>
			<? if($cmb_empresa==1) { $selected="selected"; } else { $selected=""; } ?>
			<option value="1" <?=$selected;?>>ALBAFER</option>
			<? if($cmb_empresa==2) { $selected="selected"; } else { $selected=""; } ?>
			<option value="2" <?=$selected;?>>TOOL MASTER</option>
			<? if($cmb_empresa==4) { $selected="selected"; } else { $selected=""; } ?>
			<option value="4" <?=$selected;?>>GRUPO</option>
			</select>
			-->
			&nbsp;
			<input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
		</td>
	</tr>
<? 
	switch(1) {
		case 1://Divis�o
			require('rel_comissoes.php');
		break;
	}
?>
</table>
</form>
</body>
</html>