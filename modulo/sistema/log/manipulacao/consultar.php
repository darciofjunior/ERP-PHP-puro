<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');
?>
<html>
<head>
<title>.:: Consultar Log(s) de Manipulação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'DATA INICIAL')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'DATA FINAL')) {
        return false
    }
//Comparação com as Datas ...
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value

    data_inicial = data_inicial.substr(6,4) + data_inicial.substr(3,2) + data_inicial.substr(0,2)
    data_final = data_final.substr(6,4) + data_final.substr(3,2) + data_final.substr(0,2)
    data_inicial = eval(data_inicial)
    data_final = eval(data_final)
//A Data Final jamais pode ser menor do que a Data Inicial ...
    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }

    ano_inicial = eval(document.form.txt_data_inicial.value.substr(6,4))
    ano_final = eval(document.form.txt_data_final.value.substr(6,4))
//Nunca que os Anos das Datas poderão ser diferentes ...
    if(ano_inicial != ano_final) {
        alert('DATA FINAL INVÁLIDA !!! O ANO DA DATA FINAL É DIFERENTE DO ANO DA DATA INICIAL !\nO SISTEMA SÓ PERMITE FAZER FILTRAGEM DE LOG(S) QUE SEJAM DO MESMO ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<body onload="document.form.txt_data_inicial.focus()">
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'>
		<td colspan='4'>
			<?=$mensagem[$valor];?>
		</td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan='4'>
			Consultar Log(s) de Manipulação
		</td>
	</tr>
	<tr class="linhanormal"> 
		<td><b>Data Inicial: </b>
			<input type="text" name="txt_data_inicial" size="12" maxlength="10" class="caixadetexto" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this,'data','','',event)"> 
			<img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> 
		</td>
		<td><b>Data Final: </b>
			<input type="text" name="txt_data_final" size="12" maxlength="10" class="caixadetexto" value="<?=$txt_data_final;?>" onKeyUp="verifica(this,'data','','',event)"> 
			<img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> 
		</td>
		<td>Login: 
			<select name="cmb_login" onChange="combo_falso('form', '3', '')" class="combo"> 
			<?
				$sql_login = "SELECT l.id_login, l.login, e.nomefantasia 
                                            FROM `logins` l 
                                            INNER JOIN `funcionarios` f ON f.id_funcionario = l.id_funcionario 
                                            INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa AND e.ativo = '1' 
                                            ORDER BY l.login ";
				$campos = bancos::sql($sql_login);
				$linhas = count($campos);
				echo "<option value='' class='destaquecombo' selected>Indiferente</option>";
				for($i = 0; $i < $linhas; $i++) {
					$selected = ($cmb_login == $campos[$i]['id_login']) ? 'selected' : '';
					echo "<option value='".$campos[$i]['id_login']."' $selected>".$campos[$i]['login']." (".$campos[$i]['nomefantasia'].")"."</option>";
				}
			?>
			</select>
		</td>
		<td height="14">Tipo: 
			<select name="cmb_comando" onChange="combo_falso('form', '6', '')" class="combo">
		  		<option value='' class="destaquecombo">Indiferente</option>
				<?		  
					if($cmb_comando == 1) {
						$selected1 = 'selected';
					}else if($cmb_comando == 2) {
						$selected2 = 'selected';
					}else if($cmb_comando == 3) {
						$selected3 = 'selected';
					}
				?>
				<option value='1' <?=$selected1;?>>Inclusão</option>
				<option value='2' <?=$selected2;?>>Altera&ccedil;&atilde;o</option>
				<option value='3' <?=$selected3;?>>Exclus&atilde;o</option>
			</select>
		</td>
	</tr>
	<tr class="linhanormal"> 
		<td valign='center'>
			Complemento de SQL: 
			<textarea name='txt_complemento_sql' cols='40' rows='3' class='caixadetexto'><?=$txt_complemento_sql;?></textarea>
		</td>
		<td align="right" >Tabela(s):</td>
		<td height="23" colspan="2">
			<select name="cmb_tabela[]" size="10" multiple onChange="combo_maximo('form', '2', '3', 'NÚMERO MAXIMO DE SELEÇÃO 3 !')" class="combo">
				<?=combos::listar_tabelas();?>
			</select>
		</td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan="4">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="document.form.txt_data_inicial.focus()" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
		</td>
	</tr>
</table>
<?
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' || !empty($pagina)) {
	if(!empty($cmb_tabela)) {
		$condicao_tabelas = " AND (";
		foreach($cmb_tabela as $id_tabela) $condicao_tabelas.= " `sql` LIKE '%$id_tabela%' OR ";
		$condicao_tabelas = substr($condicao_tabelas, 0, strlen($condicao_tabelas) - 4).') ';
	}
	if(!empty($txt_complemento_sql)) 		$condicao_tabelas.= " AND `sql` LIKE '%$txt_complemento_sql%' ";
	if(empty($cmb_login)) 	$cmb_login 		= '%';
	if(empty($cmb_comando)) $cmb_comando 	= '%';
/******************************************Intervalo******************************************/
//Aqui eu verifico a partir de qual Intervalo que eu vou fazer a busca de Dados ...
/*********************************************************************************************/
//Intervalo Inicial ...
	$dia_inicial = substr($txt_data_inicial, 0, 2);
	$mes_inicial = substr($txt_data_inicial, 3, 2);
	$ano_inicial = substr($txt_data_inicial, 6, 4);
//Intervalo Final ...
	$dia_final = substr($txt_data_final, 0, 2);
	$mes_final = substr($txt_data_final, 3, 2);
//Através do Ano, eu já sei em qual Base de Dados que eu vou me conectar ...
	$database 	= 'logs_'.$ano_inicial;
	/*****************************************************************************************/
	//Nova Conexão com o Banco de Dados de Logs ...
	$host = mysql_connect($_SERVER['SERVER_ADDR'], 'root', 'w1l50n');
	mysql_select_db($database, $host);
	unset($sql);
	/*****************************************************************************************/
	$vetor_meses = array('1_janeiro', '2_fevereiro', '3_março', '4_abril', '5_maio', '6_junho', '7_julho', '8_agosto', '9_setembro', '10_outubro', '11_novembro', '12_dezembro');
	for($i = ($mes_inicial - 1); $i < $mes_final; $i++) {
            //Só irá entrar dentro desse laço a partir da Segunda Vez ...
            if(!empty($sql)) $union = " UNION ";
//Quando 
            if($i == ($mes_inicial - 1)) {//Só quando for o primeiro Registro ...
                $where = " WHERE DAY(`data`) >= '$dia_inicial' AND `id_login` LIKE '$cmb_login' $condicao_tabelas AND `comando` LIKE '$cmb_comando' $condicao_complemento ";
            }else if(($i + 1) == $mes_final) {//Só quando for o último Registro ...
                $where = " WHERE DAY(`data`) <= '$dia_final' AND `id_login` LIKE '$cmb_login' $condicao_tabelas AND `comando` LIKE '$cmb_comando' $condicao_complemento ";
            }else {//Durante os outros Meses é Vazio ...
                $where = " WHERE `id_funcionario` = '$cmb_login' AND `comando` LIKE '$cmb_comando' $condicao_tabelas $condicao_complemento ";
            }
//Aqui eu estou montando a Estrutura do SQL no período de Meses solicitados pelo usuário ...
            $sql.= $union."SELECT * FROM $database.logs_manipulacao_".$vetor_meses[(int)$i].$where;
	}
	$sql.= " ORDER BY data DESC ";
	$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class='linhanormal' align='center'>
		<td colspan='6'>
			<font color='red'>
				SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.
			</font>
		</td>
	</tr>
</table>
<?
	}else {
?>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhadestaque" align='center'>
		<td>Login</td>
		<td>Data</td>
		<td>Hora</td>
		<td>IP</td>
		<td>SQL</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" align='center'>
		<td>
		<?
			$sql = "SELECT login 
					FROM `logins` 
					WHERE `id_login` = ".$campos[$i]['id_login']." LIMIT 1 ";
			$campos_login = bancos::sql($sql);
			echo $campos_login[0]['login'];
		?>
		</td>
		<td>
			<?=data::datetodata(substr($campos[$i]['data'], 0, 10), '/');?>
		</td>
		<td>
			<?=substr($campos[$i]['data'], 11, 8);?>
		</td>
		<td>
			<?=$campos[$i]['ip'];?>
		</td>
		<td align="left">
			<?=$campos[$i]['sql'];?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan="5">
			&nbsp;
		</td>
	</tr>
	<tr align='center'>
		<td colspan="5">
			<?=paginacao::print_paginacao('sim');?>
		</td>
	</tr>
</table>
<?
	}
}
?>
</form>
</body>
</html>