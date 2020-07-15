<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/variaveis/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ATRASO / FALTA EXCLUÍDO COM SUCESSO.</font>";

//Criei esse array p/ facilitar na Visualização mais abaixo ...
$motivos = array('Entrada', 'Saída', 'Falta');

if($passo == 1) {
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$txt_data_ocorrencia 	= $_POST['txt_data_ocorrencia'];
		$cmb_motivo 			= $_POST['cmb_motivo'];
		$cmb_chefe 				= $_POST['cmb_chefe'];
	}else {
		$txt_data_ocorrencia 	= $_GET['txt_data_ocorrencia'];
		$cmb_motivo 			= $_GET['cmb_motivo'];
		$cmb_chefe 				= $_GET['cmb_chefe'];
	}
	if($cmb_chefe == '') $cmb_chefe = '%';
//Data de Ocorrência ...
	if(!empty($txt_data_ocorrencia)) {
		$txt_data_ocorrencia = data::datatodate($txt_data_ocorrencia, '-');
		$condicao = " and substring(fa.data_ocorrencia, 1, 10) like '$txt_data_ocorrencia%' "; 
	}
//Motivo ...
	if($cmb_motivo != '') $condicao2 = " and fa.motivo = '$cmb_motivo' ";
/*Só exibe p/ exclusão, os funcionários que são subordinados ao id_funcionario logado 
no Sistema que é 'chefe' e que ainda está na Fase de 'Chefia Liberar'*/
	$sql = "SELECT f.id_funcionario, f.id_funcionario_superior, f.nome, f.rg, f.codigo_barra, f.ddd_residencial, f.telefone_residencial, e.nomefantasia, c.cargo, fa.* 
			FROM `funcionarios` f 
			INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
			INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
			INNER JOIN `funcionarios_acompanhamentos` fa ON fa.id_funcionario_acompanhado = f.id_funcionario and fa.observacao like '%$txt_observacao%' $condicao $condicao2 and fa.registro_portaria = 'S' and fa.status_andamento = '0' AND fa.id_funcionario_registrou = '$_SESSION[id_funcionario]' 
			WHERE f.`id_funcionario_superior` LIKE '$cmb_chefe' 
			AND f.nome LIKE '%$txt_nome%' ORDER BY fa.data_ocorrencia DESC, f.nome ";
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
		<Script Language = 'Javascript'>
			window.location = 'excluir.php?valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Excluir Atraso / Falta / Saída ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar_checkbox('form','SELECIONE UMA OPÇÃO !')">
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
	<tr></tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan='8'>
			<font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='-1'>
				Excluir Atraso / Falta / Saída - 
				<font color='yellow'>
					Somente Chefia Liberar
				</font>
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
			<font color='#FFFFFF' size='-1'>
				Código
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Nome
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Cargo
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Chefe
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Empresa
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Data e Hora <br>Ocorrência
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Motivo
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				<label for='todos'>Todos </label>
				<input type="checkbox" name="chkt" title='Selecionar todos' onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" id='todos' class="checkbox">
			</font>
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td align="center">
			<?=$campos[$i]['codigo_barra'];?>
		</td>
		<td align="left">
			<?=$campos[$i]['nome'];?>
		</td>
		<td>
			<?=$campos[$i]['cargo'];?>
		</td>
		<td align="left">
		<?
//Busca do Nome e Status do Chefe do Funcionário ...
			$sql = "Select nome, status 
					from funcionarios 
					where `id_funcionario` = ".$campos[$i]['id_funcionario_superior']." limit 1 ";
			$campos_chefe = bancos::sql($sql);
			echo $campos_chefe[0]['nome'];
			if($campos_chefe[0]['status'] == 0) {
				echo '<font color="red"><b> (Férias)</b></font>';
			}
		?>
		</td>
		<td align="center">
			<?=$campos[$i]['nomefantasia'];?>
		</td>
		<td align="center">
		<?
			echo data::datetodata(substr($campos[$i]['data_ocorrencia'], 0, 10), '/');
			if($campos[$i]['motivo'] != 2) {//Se for diferente de Falta ... 
				echo ' - '.substr($campos[$i]['data_ocorrencia'], 11, 8);
			}
		?>
		</td>
		<td align="center">
			<?=$motivos[$campos[$i]['motivo']];?>
		</td>
		<td align="center">
			<input type="checkbox" name="chkt_funcionario_acompanhamento[]" value="<?=$campos[$i]['id_funcionario_acompanhamento'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='8'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
			<input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
		</td>
	</tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if($passo == 2) {
/****************************************************/
/***********************Email************************/
/****************************************************/
	foreach ($_POST['chkt_funcionario_acompanhamento'] as $id_funcionario_acompanhamento) {
//Busca do Nome do Funcionário e de mais alguns dados p/ enviar por e-mail ...
		$sql = "Select f.id_funcionario_superior, f.nome, e.nomefantasia, c.cargo, fa.* 
				from funcionarios_acompanhamentos fa 
				inner join funcionarios f on f.id_funcionario = fa.id_funcionario_acompanhado 
				inner join empresas e on e.id_empresa = f.id_empresa 
				inner join cargos c on c.id_cargo = f.id_cargo 
				where fa.id_funcionario_acompanhamento = '$id_funcionario_acompanhamento' limit 1 ";
		$campos = bancos::sql($sql);
//Busca do Chefe do Funcionário ...
		$sql = "Select nome 
				from funcionarios 
				where id_funcionario = ".$campos[0]['id_funcionario_superior'];
		$campos_chefe = bancos::sql($sql);
		$chefe_funcionario = $campos_chefe[0]['nome'];
//Dados do Funcionário a serem enviados por e-mail ...
		$nome_funcionario = $campos[0]['nome'];
		$cargo = $campos[0]['cargo'];
		$empresa = $campos[0]['nomefantasia'];
		$data_hora = data::datetodata(substr($campos[0]['data_ocorrencia'], 0, 10), '/').' - '.substr($campos[0]['data_ocorrencia'], 11, 8);
		$motivo = $motivos[$campos[0]['motivo']];
//Busca do nome do Chefe que irá receber o do e-mail que irá receber ...
		$corpo_email.= '<b>Funcionário: </b>'.$nome_funcionario.' - <b>Cargo: </b>'.$cargo.' - <b>Chefe: </b>'.$chefe_funcionario.' <br><b>Empresa: </b>'.$empresa.' - <b>Data e Hora da Ocorrência: </b>'.$data_hora.' - <b>Motivo: </b>'.$motivo.'<br><br>';
//Deletando a Ocorrência ...
		$sql = "Delete from `funcionarios_acompanhamentos` WHERE `id_funcionario_acompanhamento` = '$id_funcionario_acompanhamento' limit 1 ";
		bancos::sql($sql);
	}
/************************E-mail************************/
//-Aqui eu busco o login de quem está excluindo os Atraso(s) / Falta(s) / Saída(s) ...*/
	$sql = "Select login 
			from logins 
			where id_login = '$id_login' limit 1 ";
	$campos2 = bancos::sql($sql);
	$login_excluindo = $campos2[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
	$cabecalho_email = 'Foi(ram) excluido(s) o(s) seguinte(s) Atraso(s) / Falta(s) / Saída(s): <br><br>';
	$rodape_email = '<b>Login: </b>'.$login_excluindo.' às '.date('d/m/Y H:i:s').'<br>'.$PHP_SELF;
	$mensagem = $cabecalho_email.$corpo_email.$rodape_email;
	$txt_justificativa.= '<br><b>Existem Atraso(s) / Falta(s) / Saída(s) a ser(em) resolvido(s) de seus funcionário(s).</b><br>Atenciosamente<br><br>Depto. Pessoal';
	$destino = $roberto_email.','.$sandra_email.','.$wilson_email;
	comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Atraso(s) / Falta(s) / Saída(s) - Excluída(s)', $mensagem);
/******************************************************/
?>
	<Script Language = 'Javascript'>
		window.location = 'excluir.php?valor=2'
	</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Atraso / Falta / Saída ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
</head>
<body onLoad="document.form.txt_nome.focus()">
<form name="form" method="post" action="<?=$GLOBALS['PHP_SELF'];?>">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Excluir Atraso / Falta / Saída
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Nome
		</td>
		<td>
			<input type="text" name="txt_nome" title="Digite o Nome" size="45" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Data da Ocorrência
		</td>
		<td>
			<input type="text" name="txt_data_ocorrencia" title="Digite a Data da Ocorrência" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class="caixadetexto">
			<img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_ocorrencia&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> Calendário
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Motivo
		</td>
		<td>
			<select name="cmb_motivo" title="Selecione o Motivo" class="combo">
				<option value="">SELECIONE</option>
				<option value="0">Entrada</option>
				<option value="1">Saída</option>
				<option value="2">Falta</option>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Chefe
		</td>
		<td>
			<select name="cmb_chefe" title="Selecione o Chefe" class="combo">
			<?
//Listagem de todos os Funcionários que são Chefes na Empresa ...
				$sql = "SELECT DISTINCT(id_funcionario_superior) AS id_funcionario_superior 
                                        FROM `funcionarios` 
                                        WHERE `id_funcionario_superior` <> '0' ";
				$campos_chefes = bancos::sql($sql);
				$linhas_chefes = count($campos_chefes);
				for($i = 0; $i < $linhas_chefes; $i++) {
					$id_funcs_chefe.= $campos_chefes[$i]['id_funcionario_superior'].', ';
				}
//Significa que não carregou essa variável no Loop ...
				if(strlen($id_funcs_chefe) == 0) {
					$id_funcs_chefe = 0;
				}else {
					$id_funcs_chefe = substr($id_funcs_chefe, 0, strlen($id_funcs_chefe) - 2);
				}
//Busca do nome dos Chefes ...
				$sql = "Select id_funcionario, nome 
						from funcionarios 
						where `id_funcionario` in ($id_funcs_chefe) order by nome ";
				echo combos::combo($sql, $cmb_chefe);
			?>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Observação
		</td>
		<td>
			<input type="text" name="txt_observacao" title="Digite a Observação" size="35" class="caixadetexto">
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_nome.focus()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>