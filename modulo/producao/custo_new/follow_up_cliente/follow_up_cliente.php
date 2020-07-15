<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/financeiros.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CLIENTE EXCLUIDO COM SUCESSO.</font>";

if($passo == 1) {
	switch($opt_opcao) {
		case 1:
			$sql ="select * ";
			$sql.="from clientes ";
			$sql.="where nomefantasia like '%$txt_consultar%' and ativo=1 order by nomefantasia";
		break;
		case 2:
			$sql="select * ";
			$sql.="from clientes ";
			$sql.="where razaosocial like'%$txt_consultar%' and ativo=1 order by razaosocial";
		break;
		case 3:
			$txt_consultar = str_replace('.','',$txt_consultar);
			$txt_consultar = str_replace('-','',$txt_consultar);
			$txt_consultar = str_replace('/','',$txt_consultar);
			$sql="select * ";
			$sql.="from clientes ";
			$sql.="where `cnpj_cpf` like '%$txt_consultar%' and ativo=1 order by `cnpj_cpf` ";
		break;
		default:
			$sql= "select * ";
			$sql.="from clientes ";
			$sql.="where ativo=1 order by razaosocial";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas < 1) {
?>
		<Script Language = 'Javascript'>
			window.location = 'follow_up_cliente.php?passo=0&valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Registrar Follow-Up(s) do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name="form">
<table width='900' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
	<tr>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="7">
			<font color='#FFFFFF' size='-1'>
				Registrar Follow-Up(s) do Cliente
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
			<font color='#FFFFFF' size='-1'>
				Razão Social
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Nome Fantasia
			</font>
		</td>
		<td>
			Tp
		</td>
		<td>
			Tel Com
		</td>
		<td>
			Cr
		</td>
		<td>
                    CNPJ / CPF
		</td>
		<td width='25'>
			<img src="../../../../imagem/propriedades.png" width="16" height="16" border="0">
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
			$id_cliente = $campos[$i]['id_cliente'];
			$credito = financeiros::controle_credito($campos[$i]['id_cliente']);
?>
	<tr onclick="return cor_clique_celula(this, '#C6E2FF')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" class="linhanormal" align='center'>
		<td align="left">
			<?=$campos[$i]['razaosocial'];?>
			&nbsp;
			<img src = "../../../../imagem/menu/incluir.png" border='0' title="Registrar Follow-UP" alt="Registrar Follow-UP" onClick="javascript:nova_janela('../../../classes/cliente/follow_up.php?identificacao=<?=$campos[$i]['id_cliente'];?>&origem=8', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')">
		</td>
		<td align="left">
			<?=$campos[$i]['nomefantasia'];?>
		</td>
		<td>
		<?
			if($campos[$i]['tipo_cliente'] == 0) {
				echo 'RA';
			}else if($campos[$i]['tipo_cliente'] == 1) {
				echo 'RI';
			}else if($campos[$i]['tipo_cliente'] == 2) {
				echo 'CO';
			}else if($campos[$i]['tipo_cliente'] == 3) {
				echo 'ID';
			}else if($campos[$i]['tipo_cliente'] == 4) {
				echo 'AT';
			}else if($campos[$i]['tipo_cliente'] == 5) {
				echo 'DT';
			}else if($campos[$i]['tipo_cliente'] == 6) {
				echo 'IT';
			}else if($campos[$i]['tipo_cliente'] == 7) {
				echo 'FN';
			}
		?>
		</td>
		<td align="left">
		<?
			if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) {
				echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
			}
			if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) {
				echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
			}
			if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) {
				echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
			}
			if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) {
				echo $campos[$i]['telcom'];
			}
		?>
		</td>
		<td>
			<font color="blue"><?=$credito;?></font>
		</td>
		<td>
		<?
                        if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                            if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                                echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                            }else {//CNPJ ...
                                echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                            }
                        }
		?>
		</td>
		<td>
		<?
/*Eu criei essa variável $chamar_segurancas, para que a tela de detalhes do cliente seja a mesma tanto 
para a consulta deste através do módulo de Produção, como através de Vendas, porque sendo assim 
a manutenção é única*/
			$url = "javascript:nova_janela('../../../classes/cliente/detalhes.php?chamar_segurancas=1&id_cliente=".$id_cliente."', 'POP', '', '', '', '', '450', '700', 'c', 'c', '', '', 's', 's', '', '', '')";
		?>
			<a href="<?=$url;?>" title="Detalhes do Cliente" class="link">
				<img src="../../../../imagem/propriedades.png" border="0" alt="Detalhes do Cliente" title="Detalhes do Cliente" width="16" height="16">
			</a>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan="7">
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'follow_up_cliente.php?passo=0'" class="botao">
		</td>
	</tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Legenda dos Tipos de Cliente:</b></font>

 <font color="blue"><b>RA</b></font> -> Revenda Ativa
 <font color="blue"><b>RI</b></font> -> Revenda Inativa
 <font color="blue"><b>CO</b></font> -> Cooperado
 <font color="blue"><b>ID</b></font> -> Indústria
 <font color="blue"><b>AT</b></font> -> Atacadista
 <font color="blue"><b>DT</b></font> -> Distribuidor
 <font color="blue"><b>IT</b></font> -> Internacional
 <font color="blue"><b>FN</b></font> -> Fornecedor
</pre>
<?
	}
}else {
?>
<html>
<head>
<title>.:: Registrar Follow-Up(s) do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
	if(document.form.opcao.checked == true) {
		for(i = 0; i < 3; i ++) {
			document.form.opt_opcao[i].disabled = true
		}
		document.form.txt_consultar.disabled = true
		document.form.txt_consultar.value = ''
	}else {
		for(i = 0; i < 3;i ++) {
			document.form.opt_opcao[i].disabled = false
		}
		document.form.txt_consultar.disabled = false
		document.form.txt_consultar.value = ''
		document.form.txt_consultar.focus()
	}
}

function iniciar() {
	document.form.txt_consultar.focus()
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
<body onLoad="iniciar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()";>
<input type="hidden" value="1" name="passo">
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Registrar Follow-Up(s) do Cliente
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
			<input type="radio" name="opt_opcao" value="1" title="Consultar cliente por: Nome Fantasia" onclick="return iniciar()" id="opt1">
			<label for="opt1">Nome Fantasia</label>
		</td>
		<td width="20%">
			<input type="radio" name="opt_opcao" value="2" title="Consultar cliente por: Razão Social" onclick="return iniciar()" id="opt2" checked>
			<label for="opt2">Razão Social</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%">
                    <input type="radio" name="opt_opcao" value="3" title="Consultar cliente por: CNPJ / CPF" onclick="return iniciar()" id="opt3">
                    <label for="opt3">CNPJ / CPF</label>
		</td>
		<td width="20%">
                    <input type='checkbox' name='opcao' onclick='limpar()'  value='1' tabindex='3' title="Consultar todos os clientes" id="todos" class="checkbox">
                    <label for="todos">Todos os registros
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>