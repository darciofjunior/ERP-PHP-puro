<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>J� FORAM EMITIDO(S) VALE(S) TRANSPORTE(S) P/ ESSA EMPRESA NESSA DATA DE HOLERITH.</font>";
$mensagem[2] = "<font class='atencao'>N�O EXISTE NENHUM FUNCION�RIO CADASTRADO NESSA EMPRESA.</font>";
$mensagem[3] = "<font class='confirmacao'>VALE TRANSPORTE INCLUIDO COM SUCESSO.</font>";

$data_emissao   = date('Y-m-d');

if($passo == 1) {
//Aqui nesse loop eu disparo todos os funcion�rios da Empresa selecionada ...
    for($i = 0; $i < count($_POST['hdd_funcionario']); $i++) {
//Gravando na Tabela de Vales o Valor PD ...
        if($_POST['txt_6_vlr_salario_pd'][$i] > 0) {//S� valores positivos ...
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$hdd_funcionario[$i]', '7', '$txt_6_vlr_salario_pd[$i]', '$_POST[cmb_data_holerith]', '$data_emissao', 'PD', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
//Gravando na Tabela de Vales o Valor PF ...
        if($_POST['txt_6_vlr_salario_pf'][$i] > 0) {//S� valores positivos ...
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$hdd_funcionario[$i]', '7', '$txt_6_vlr_salario_pf[$i]', '$_POST[cmb_data_holerith]', '$data_emissao', 'PF', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?cmb_data_holerith=<?=$_POST['cmb_data_holerith'];?>&valor=3'
    </Script>
<?
}else {
//Se tiver uma Empresa selecionada, ent�o listo todos os funcion�rios daquela Empresa ...
	if(!empty($cmb_empresa)) {
/****************************************************************************************************/
/*Busca de pelo menos 1 funcion�rio da Empresa selecionada p/ ver se esse j� possui algum vale na 
Data de Holerith especificada pelo Usu�rio ...*/
		$sql = "SELECT id_funcionario 
			FROM `funcionarios` 
			WHERE `id_empresa` = '$cmb_empresa' 
			AND `status` < '3' 
			AND `retira_vale_transporte` = 'S' 
			AND `conducao_propria` = 'N' 
			AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY nome LIMIT 1 ";
		$campos = bancos::sql($sql);
		$id_func_emp_selec = $campos[0]['id_funcionario'];
/*Verifico se j� existe no Sistema pelo menos 1 funcion�rio com Vale Transporte nessa Data de Holerith 
selecionada pelo usu�rio ...*/
		$sql = "SELECT `id_vale_dp` 
			FROM `vales_dps` 
			WHERE `tipo_vale` = '7' 
			AND `id_funcionario` = '$id_func_emp_selec' 
			AND `data_debito` = '$cmb_data_holerith' LIMIT 1 ";
		$campos = bancos::sql($sql);
		if(count($campos) == 1) {//Encontrou um funcion�rio ...
?>
                    <Script Language = 'Javascript'>
                        window.location = 'incluir.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=1'
                    </Script>
<?
                    exit;
		}
/****************************************************************************************************/
/*Listagem de Funcion�rios referente a Empresa selecionada, que ainda est�o trabalhando, 
que est�o com a marca��o de Retirar Vale Transporte e que n�o possuem condu��o Pr�pria ...*/
/*S� n�o exibo os funcion�rios Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes n�o s�o funcion�rios, simplesmente s� possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
		$sql = "SELECT id_funcionario, tipo_salario, salario_pd, salario_pf, salario_premio, nome 
			FROM `funcionarios` 
			WHERE `id_empresa` = '$cmb_empresa' 
			AND `status` < '3' 
			AND `retira_vale_transporte` = 'S' 
			AND `conducao_propria` = 'N' 
			AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY nome ";
		$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
		$linhas = count($campos);
		if($linhas == 0) {//N�o encontrou nenhum funcion�rio com essa marca��o ...
?>
			<Script Language = 'Javascript'>
				window.location = 'incluir.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=2'
			</Script>
<?
			exit;
		}
	}
//Busca da Qtde de Dias p/ Pgto. de Passes, que vai estar sendo utilizado + abaixo p/ os c�lculos em PHP ...
	$sql = "SELECT id_vale_data, qtde_dias_passes 
		FROM `vales_datas` 
		WHERE `data` = '$cmb_data_holerith' LIMIT 1 ";
	$campos_data_hol    = bancos::sql($sql);
	$id_vale_data       = $campos_data_hol[0]['id_vale_data'];
	$qtde_dias_passes   = $campos_data_hol[0]['qtde_dias_passes'];

//Enquanto esse campo de Qtde de Dias p/ Pgto. de Passes n�o estiver preenchido, o Usu�rio n�o pode gerar a Impress�o do Pedido
	if($qtde_dias_passes == 0) {
?>
		<Script Language = 'JavaScript'>
			alert('A QTDE DE DIAS P/ PGTO. DE PASSES = 0 !\nCADASTRE UMA QTDE DE DIAS P/ PGTO. DE PASSES PARA ESSA DATA DE HOLERITH !')
			window.close()
		</Script>
<?
		exit;
	}
?>
<html>
<head>
<title>.:: Incluir Vale(s) - Vale Transporte ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
	document.form.passo.value = 1
//Tratamento com o restante dos Objetos ...
	var elementos = document.form.elements
	var objetos_inicio = 4//Qtde de objetos antes do loop
	var objetos_linha = 6//Qtde de objetos que eu tenho por linha
	var objetos_fim = 4//Qtde de objetos ap�s do loop
//Tratamento nos objetos 6% VT PD e 6% VT PF p/ gravar os objetos no BD ...
	for (var i = objetos_inicio; i < (elementos.length) - objetos_fim; i+=objetos_linha) {
		elementos[i + 2].disabled = false//Habilita a caixa p/ poder gravar no BD ...
		elementos[i + 2].value = strtofloat(elementos[i + 2].value)
		elementos[i + 4].value = strtofloat(elementos[i + 4].value)
	}
}

function recarregar_objetos() {
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
	document.form.submit()
}

function atualizar() {
	document.form.passo.value = 0
	document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF;?>' onsubmit="return validar()">
<!--Esse hidden � um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo' onclick="atualizar()">
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'>
		<td colspan="6">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="6">
			<font color='#FFFFFF' size='-1'>
				Incluir Vale(s) - Vale Transporte
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho">
		<td colspan="6"> Empresa:
			<select name="cmb_empresa" onchange="recarregar_objetos()" class="combo">
			<?
				$sql = "Select id_empresa, nomefantasia 
					from empresas 
					where ativo = '1' order by nomefantasia ";
				echo combos::combo($sql, $cmb_empresa);
			?>
			</select>
			&nbsp;-&nbsp;
			<font color="yellow">
				Data de Holerith: 
			</font>
			<?=data::datetodata($cmb_data_holerith, '/');?>
			&nbsp;-&nbsp;
			<font color="yellow">
				Qtde de Dias p/ Pgto. de Passes: 
			</font>
			<?=$qtde_dias_passes;?>
		</td>
	</tr>
<?
//Se n�o tiver nenhuma Empresa selecionada, ent�o eu exibo esse bot�o de Voltar p/ a Tela Principal de Vales
	if(empty($cmb_empresa)) {
?>
	<tr>
		<td></td>
	</tr>
	<tr align='center'>
		<td colspan="6">
			<font color='#FFFFFF' size='-1'>
				<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../itens/incluir.php'" class="botao">
			</font>
		</td>
	</tr>
	<tr>
		<td><pre><b><font color="red">Observa��o:</font></b><pre><font color="darkblue">
* Os funcion�rios que est�o de f�rias n�o aparecem neste Tipo de Vale.
		</font></pre></pre></td>
	</tr>
<?
//Se tiver uma Empresa selecionada, ent�o listo todos os funcion�rios daquela Empresa ...
	}else {
?>
	<tr class='linhadestaque' align='center'>
            <td width='50%'>Funcion�rio</td>
            <td>Total de VT R$</td>
            <td>Sal PD</td>
            <td>6% VT PD</td>
            <td>Sal PF + Pr�mio</td>
            <td>6% VT PF + Pr�mio</td>
	</tr>
<?
		$cont = 0;
		for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o
			$url = "javascript:nova_janela('../../funcionario/detalhes.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
//Aqui eu limpo as vari�veis p/ que n�o fique com o Valor Acumulado do Loop Anterior ...
			$comissao_pd = 0;
			$comissao_pf = 0;
			$dsr_pd = 0;
			$dsr_pf = 0;
//Aqui eu busco a Comiss�o do Funcion�rio referente ao M�s Corrente da Data de Holerith ...
			$sql = "SELECT comissao_alba, comissao_tool, comissao_grupo, dsr_alba, dsr_tool, dsr_grupo 
				FROM `funcionarios_vs_holeriths` 
				WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
				AND `id_vale_data` = '$id_vale_data' ";
			$campos_com_dsr = bancos::sql($sql);
			if(count($campos_com_dsr) == 1) {//Se encontrar alguma ...
				if($cmb_empresa == 1) {//Se a Empresa = Albafer ...
					$comissao_pd = $campos_com_dsr[0]['comissao_alba'];
					$comissao_pf = $campos_com_dsr[0]['comissao_tool'] + $campos_com_dsr[0]['comissao_grupo'];
					$dsr_pd = $campos_com_dsr[0]['dsr_alba'];
					$dsr_pf = $campos_com_dsr[0]['dsr_tool'] + $campos_com_dsr[0]['dsr_grupo'];
				}else if($cmb_empresa == 2) {//Se a Empresa = Tool Master ...
					$comissao_pd = $campos_com_dsr[0]['comissao_tool'];
					$comissao_pf = $campos_com_dsr[0]['comissao_alba'] + $campos_com_dsr[0]['comissao_grupo'];
					$dsr_pd = $campos_com_dsr[0]['dsr_tool'];
					$dsr_pf = $campos_com_dsr[0]['dsr_alba'] + $campos_com_dsr[0]['dsr_grupo'];
				}else if($cmb_empresa == 4) {//Se a Empresa = Grupo ...
					$comissao_pd = 0;//N�o � Registrado, ah !! n�o tem carteira assinada, sem direito ...
					$comissao_pf = $campos_com_dsr[0]['comissao_alba'] + $campos_com_dsr[0]['comissao_tool'] + $campos_com_dsr[0]['comissao_grupo'];
					$dsr_pd = 0;//N�o � Registrado, ah !! n�o tem carteira assinada, sem direito ...
					$dsr_pf = $campos_com_dsr[0]['dsr_alba'] + $campos_com_dsr[0]['dsr_tool'] + $campos_com_dsr[0]['dsr_grupo'];
				}
			}else {//N�o encontrou comiss�o nenhuma p/ o Funcion�rio ...
				$comissao_pd = 0;
				$comissao_pf = 0;
				$dsr_pd = 0;
				$dsr_pf = 0;
			}
//C�lculo do Sal�rio ...
			if($campos[$i]['tipo_salario'] == 1) {//Horista
                            $vlr_salario_pd = 220 * $campos[$i]['salario_pd'];
                            $vlr_salario_pf = 220 * ($campos[$i]['salario_pf'] + $campos[$i]['salario_premio']);
			}else {//Mensalista
                            $vlr_salario_pd = $campos[$i]['salario_pd'];
                            $vlr_salario_pf = ($campos[$i]['salario_pf'] + $campos[$i]['salario_premio']);
			}
//Ir� mostrar em titles, quando o funcion�rio tiver comiss�o ...
			$vlr_salario_pd_title = $vlr_salario_pd;
			$vlr_salario_pf_title = $vlr_salario_pf;
/*Junto do sal�rio, eu somo o valor das comiss�es do Vendedor Tamb�m referente ao M�s Corrente 
da Data de Holerith ... - Parou de entrar em vigor a Partir do dia 21/09/2012 por orienta��o da Contabilidade - Marcos �damo */
			//$vlr_salario_pd+= $comissao_pd;
			//$vlr_salario_pf+= $comissao_pf;
//Se a Empresa = Grupo, s� se recebe no PF ...
			if($cmb_empresa == 4) {
				$class = 'textdisabled';//Layout de Desabilitado ...
				$disabled = 'disabled';//Caixa desabilitada ...
//6 % do Sal�rio PD + 6 % do Sal�rio PF ...
				$seis_perc_salario_pd = 0;
				$seis_perc_salario_pf = (0.06 * $vlr_salario_pd) + (0.06 * $vlr_salario_pf);
			}else {
				$class = 'caixadetexto';//Layout de Habilitado ...
				$disabled = '';//Caixa habilitada ...
//6 % do Sal�rio PD e 6 % do Sal�rio PF ...
				$seis_perc_salario_pd = 0.06 * $vlr_salario_pd;
				$seis_perc_salario_pf = 0.06 * $vlr_salario_pf;
			}
?>
	<tr class="linhanormal" align='center'>
		<td align="left">
			<a href="#" onclick="<?=$url;?>" title="Detalhes Funcion�rio" class="link">
				<?=$campos[$i]['nome'];?>
			</a>
		</td>
		<td>
		<?
//Busco o Valor Total que � consumido em VT pelo Funcion�rio mensalmente ...
			$sql = "SELECT SUM(fvt.qtde_vale * vt.valor_unitario * $qtde_dias_passes) AS total_vt_rs 
				FROM `funcionarios_vs_vales_transportes` fvt 
				INNER JOIN `vales_transportes` vt ON vt.id_vale_transporte = fvt.id_vale_transporte AND vt.`ativo` = '1' 
				WHERE fvt.`id_funcionario` = ".$campos[$i]['id_funcionario']." ";
			$campos_vt_por_mes  = bancos::sql($sql);
			$total_vt_rs        = $campos_vt_por_mes[0]['total_vt_rs'];
		?>
			<input type="text" name="txt_total_vt_rs[]" value="<?=number_format($total_vt_rs, 2, ',', '.');?>" title="Total de VT R$" size="10" class="textdisabled" disabled>
		</td>
		<td align="left">
			<input type="text" name="txt_vlr_salario_pd[]" value="<?=number_format($vlr_salario_pd, 2, ',', '.');?>" title="Valor do Sal�rio PD" size="10" class="textdisabled" disabled>
			<?
//Se existir comiss�o p/ o Funcion�rio "Vendedor", ent�o eu apresento est� na Tela ...
                            if($comissao_pd != 0) echo '<font title="Sal�rio PD => R$ '.number_format($vlr_salario_pd_title, 2, ',', '.').' - Comiss�o PD => R$ '.number_format($comissao_pd, 2, ',', '.').'" style="cursor:help"><b>* Obs</b>';
			?>
		</td>
		<td>
		<?
//Se o Sal�rio PD for maior que o Valor Total, ent�o ...
			if($seis_perc_salario_pd > $total_vt_rs) {
                            $novo_vlr_salario_pd = $total_vt_rs;
			}else {
                            $novo_vlr_salario_pd = $seis_perc_salario_pd;
			}
		?>
			<input type="text" name="txt_6_vlr_salario_pd[]" value="<?=number_format($novo_vlr_salario_pd, 2, ',', '.');?>" title="6% do Vale Transporte PD" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="10" class="<?=$class;?>" <?=$disabled;?>>
		</td>
		<td align="left">
			<input type="text" name="txt_vlr_salario_pf[]" value="<?=number_format($vlr_salario_pf, 2, ',', '.');?>" title="Valor do Sal�rio PF" size="10" class="textdisabled" disabled>
			<?
//Se existir comiss�o p/ o Funcion�rio "Vendedor", ent�o eu apresento est� na Tela ...
				if($comissao_pf != 0) {
					echo '<font title="Sal�rio PF => R$ '.number_format($vlr_salario_pf_title, 2, ',', '.').' - Comiss�o PF => R$ '.number_format($comissao_pf, 2, ',', '.').'" style="cursor:help"><b>* Obs</b>';
				}
			?>
		</td>
		<td>
		<?
//Se a Empresa = Grupo, s� se recebe no PF, 
			if($cmb_empresa == 4) {//Se o PF > que o Valor Total, ent�o ...
				if($seis_perc_salario_pf > $total_vt_rs) {
					$novo_vlr_salario_pf = $total_vt_rs;
				}else {
					$novo_vlr_salario_pf = $seis_perc_salario_pf;
				}
			}else {//Se a Empresa for Alba ou Tool Master ...
				if(($total_vt_rs - $seis_perc_salario_pd) > $seis_perc_salario_pf) {
					$novo_vlr_salario_pf = $seis_perc_salario_pf;
				}else {
					$novo_vlr_salario_pf = $total_vt_rs - $seis_perc_salario_pd;
				}
//Nunca que esse c�lculo poder� ser Zero nessa conta ...
				if($novo_vlr_salario_pf < 0) {
					$novo_vlr_salario_pf = 0;
				}
			}
		?>
			<input type="text" name="txt_6_vlr_salario_pf[]" value="<?=number_format($novo_vlr_salario_pf, 2, ',', '.');?>" title="6% do Vale Transporte PF" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="10" class="caixadetexto">
			&nbsp;
			<input type="hidden" name="hdd_funcionario[]" value="<?=$campos[$i]['id_funcionario'];?>" size="10">
		</td>
	</tr>
<?
			$cont++;
		}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan="6">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../itens/incluir.php'" class="botao">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="document.form.reset()" style="color:#ff9900;" class="botao">
			<?
//Se n�o tiver cadastro a Qtde de Dias p/ Pgto. de Passes da Data de Holerith, ent�o desabilito o bot�o de Salvar
				if($qtde_dias_passes == 0) {
					$disabled_botao = 'disabled';
				}
			?>
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao" <?=$disabled_botao;?>>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class="botao">
		</td>
	</tr>
	<tr>
		<td colspan="6"><pre><b><font color="red">Observa��o:</font></b><pre><font color="darkblue">
* Os funcion�rios que est�o de f�rias n�o aparecem neste Tipo de Vale.
		</font></pre></pre></td>
	</tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
	}
?>
</body>
</html>
<?}?>