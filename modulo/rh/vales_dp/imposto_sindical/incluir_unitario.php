<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>VALE IMPOSTO SINDICAL INCLUIDO COM SUCESSO.</font>";

if($passo == 1) {
//Traz todos funcionários - menos do cargo AUTONÔMO
	switch($opt_opcao) {
//Listagem de Funcionários que ainda estão trabalhando ...
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
		case 1:
			$sql = "Select f.id_funcionario, f.id_funcionario_superior, f.nome, f.codigo_barra, e.nomefantasia, c.cargo, d.departamento 
				from funcionarios f, empresas e, cargos c, departamentos d 
				where f.nome like '%$txt_consultar%' 
				and f.status < 3 
				and f.id_funcionario not in (1, 2, 62, 66, 68, 91, 114) 
				and f.id_empresa = e.id_empresa 
				and f.id_cargo = c.id_cargo 
				and f.id_departamento = d.id_departamento order by f.nome ";
		break;
		default:
			$sql = "Select f.id_funcionario, f.id_funcionario_superior, f.nome, f.codigo_barra, e.nomefantasia, c.cargo, d.departamento 
				from funcionarios f, empresas e, cargos c, departamentos d 
				and f.status < 3 
				and f.id_funcionario not in (1, 2, 62, 66, 68, 91, 114) 
				and f.id_empresa = e.id_empresa 
				and f.id_cargo = c.id_cargo 
				and f.id_departamento = d.id_departamento order by f.nome ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
		<Script Language = 'Javascript'>
			window.location = 'incluir_unitario.php?valor=1'
		</Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Incluir Vale(s) - Imposto Sindical Unitário ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	var x, mensagem = '', valor = false, elementos = document.form.elements
	for (x = 0; x < elementos.length; x ++) {
		if (elementos[x].type == 'checkbox')  {
			if (elementos[x].checked == true) {
				valor = true
			}
		}
	}

	if(valor == false) {
		window.alert('SELECIONE UMA OPÇÃO !')
		return false
	}else {
		return true
	}
}
</Script>
</head>
<body>
<form name='form'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
	<tr align='center'>
		<td colspan='6'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class="linhacabecalho" align='center'>
		<td colspan='6'>
			<font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='-1'>
				Consultar Funcionário(s)
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan="2">
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
				Depto.
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Cargo
			</font>
		</td>
		<td>
			<font color='#FFFFFF' size='-1'>
				Empresa
			</font>
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
			$url = "incluir_unitario.php?passo=2&id_funcionario_loop=".$campos[$i]['id_funcionario']."&cmb_data_holerith=$cmb_data_holerith";
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF');window.location = '<?=$url;?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
		<td width="10">
                    <a href="<?=$url;?>">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td align="center">
			<?=$campos[$i]['codigo_barra'];?>
		</td>
		<td>
			<?=$campos[$i]['nome'];?>
		</td>
		<td>
			<?=$campos[$i]['departamento'];?>
		</td>
		<td>
			<?=$campos[$i]['cargo'];?>
		</td>
		<td>
			<?=$campos[$i]['nomefantasia'];?>
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan='6'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir_unitario.php?passo=0&cmb_data_holerith=<?=$cmb_data_holerith;?>'" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class="botao">
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
	$sql = "Select id_funcionario, id_empresa, nome, tipo_salario, salario_pd 
		from funcionarios 
		where id_funcionario = '$id_funcionario_loop' limit 1";
	$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Incluir Vale(s) - Imposto Sindical Unitário ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tratamento no objeto Vlr Fatura p/ gravar os objetos no BD ...
	document.form.txt_vlr_fatura.value = strtofloat(document.form.txt_vlr_fatura.value)
//Desabilita este campo p/ poder gravar no BD ...
	document.form.txt_vlr_fatura.disabled = false
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onsubmit="return validar()">
<!--Aqui eu renomeio essa variável $id_funcionario para $id_funcionario_loop para não dar conflito com 
a variável da Sessão "$id_funcionario"-->
<input type="hidden" name="id_funcionario_loop" value="<?=$id_funcionario_loop;?>">
<!--Esse hidden é um controle de Tela-->
<input type="hidden" name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<table border="0" width='70%' align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho'>
		<td colspan="2" align='center'>
			<font color='#FFFFFF' size='-1'>
				Incluir Vale(s) - Imposto Sindical Unitário 
			</font>
		</td>
	</tr>
	<tr class="linhadestaque">
		<td colspan="6">
			<font color="yellow">
				Data de Holerith: 
			</font>
			<?=data::datetodata($cmb_data_holerith, '/');?>
		</td>
	</tr>
	<tr class="linhanormal">
		<td width='20%'><b>Funcionário:</b>
		<td width='80%'>
		<?
//Controle com o Pop-Up ... 
			$url = "javascript:nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$id_funcionario_loop."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
		?>
			<a href="#" onclick="<?=$url;?>" title="Detalhes Funcionário" class="link">
				<?=$campos[0]['nome'];?>
			</a>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Empresa:</b>
		<td>
			<?=genericas::nome_empresa($campos[0]['id_empresa']);?>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Tipo de Salário:</b>
		<td>
		<?
			if($campos[0]['tipo_salario'] == 1) {//Horista
				echo 'HORISTA';
			}else {//Mensalista
				echo 'MENSALISTA';
			}
		?>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Salário PD:</b>
		<?
			if($campos[0]['tipo_salario'] == 1) {//Horista
				$salario_pd = 220 * $campos[0]['salario_pd'];
			}else {//Mensalista
				$salario_pd = $campos[0]['salario_pd'];
			}
		?>
		<td>
			<?=number_format($salario_pd, 2, ',', '.');?>
		</td>
	</tr>
	<tr class="linhanormal">
		<td><b>Vlr Fatura:</b>
		<?
//Calculando o Imposto Sindical ...
			$imposto_sindical = $salario_pd / 30;//Divide pela qtde de Dias ...
			$vlr_fatura = $imposto_sindical;
		?>
		<td>
			<input type='text' name='txt_vlr_fatura' value='<?=number_format($vlr_fatura, 2, ',', '.');?>' title='Valor da Fatura' size="10" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='2'>
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir_unitario.php<?=$parametro;?>'" class="botao">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR')" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
//Tratamento com os campos p/ poder gravar no BD ...
    $data_emissao = date('Y-m-d');
    $data_sys = date('Y-m-d H:i:s');
//Primeiro apaga-se o vale do Tipo Imposto Sindical gerado p/ o Funcionário ...
    $sql = "DELETE FROM `vales_dps` 
            WHERE `id_funcionario` = '$id_funcionario_loop' 
            AND `tipo_vale` = '12' 
            AND `data_debito` = '$cmb_data_holerith' ";
    bancos::sql($sql);
//Se o Valor do vale <> 0, então eu gero vale para esse funcionário ...
    if($txt_vlr_fatura != 0.00) {
        $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$id_funcionario_loop', '12', '$txt_vlr_fatura', '$cmb_data_holerith', '$data_emissao', 'PD', '$data_sys') ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_unitario.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Vale(s) - Imposto Sindical Unitário ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
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
//Aqui é para não atualizar os frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='nao_atualizar'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				Consultar Funcionário(s)
			</font>
		</td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
			Consultar <input type="text" name="txt_consultar" title="Consultar Funcionário" size="45" maxlength="45" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="20%"><input type="radio" name="opt_opcao" value="1" title="Consultar Funcionário por: Nome" onclick="document.form.txt_consultar.focus()" id='label' checked>
			<label for="label">Nome</label>
		</td>
		<td width="20%">
			<input type='checkbox' name='opcao' value='2' title="Consultar todos os funcionários" onclick='limpar()' id='label2' class="checkbox">
			<label for="label2">Todos os registros</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../itens/incluir.php'" class="botao">
			<input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>