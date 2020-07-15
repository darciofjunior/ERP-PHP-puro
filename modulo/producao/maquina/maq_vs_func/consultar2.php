<?
require('../../../../lib/segurancas.php');
require('../../../../lib/biblioteca.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/maquina/maq_vs_func/consultar.php', '../../../../');

echo 'ESSE ARQUIVO ESTÁ SENDO USADO ???? DÁRCIO ';

$sql     = "select funcionarios.*, ufs.id_uf, ufs.sigla from funcionarios, ufs where id_funcionario = '$id_func' and funcionarios.id_uf = ufs.id_uf  limit 1";
$campos = bancos::sql($sql);
$sigla = $campos[0]["sigla"];
$id_uf = $campos[0]["id_uf"];
$linhas  = count($campos);
?>
<html>
<head>
<title>.:: Detalhes Funcionários ::.</title>
<meta http-equiv='content-type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type=text/css rel=stylesheet>
<Script language='JavaScript' src='../../../../js/geral.js'></script>
</head>
<?
	if($campos[0]["data_admissao"] == "0000-00-00"){
		$resultado = "&nbsp;";
	}else{
		$resultado = $campos[0]["data_admissao"];
		$resultado = data::datetodata($resultado,"/");
	}
	if($campos[0]["data_demissao"] == "0000-00-00"){
		$resultado2 = "&nbsp;";
	}else{
		$resultado2 = $campos[0]["data_demissao"];
		$resultado2 = $data->datetodata($resultado2,"/");
	}
//Validação do Sálario Fixo
	if($campos[0]["salario_fixo"] == "0.00"){
		$resultado3 = "&nbsp;";
	}else{
		$resultado3 = $campos[0]["salario_fixo"];
		$resultado3 = str_replace(".",",",$resultado3);
	}
 //Validação do Sálario Comissão
	if ($campos[0]["salario_comissao"] == "0.00"){
		$resultado4 ="&nbsp;";
	}else{
		$resultado4 = $campos[0]["salario_comissao"];
		$resultado4 = str_replace(".",",",$resultado4);
	}
 //Validação do Sálario Hora
	if ($campos[0]["salario_hora"] == "0.00"){
		$resultado5 ="&nbsp;";
	}else{
		$resultado5 = $campos[0]["salario_hora"];
		$resultado5 = str_replace(".",",",$resultado5);
	}
 //Validação do DDD Celular
	if($campos[0]["ddd_celular"] == "0"){
		$resultado6 ="&nbsp;";
	}else{
		 $resultado6 = $campos[0]["ddd_celular"];
	}
//Validação do telefone Residencial
        $resultado7 = $campos[0]['telefone_residencial'];
//Validação do Telefone Celular
	if($campos[0]["telefone_celular"] == "0"){
		$resultado8 ="";
	}else{
		$resultado8 = $campos[0]["telefone_celular"];
	}

?>
<body bgcolor='#FFFFFF' text='#000000'>
<form name='form' method='post' onsubmit='return validar()' action='<?echo $PHP_SELF.'?passo=3'?>'>
  <table width='600' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhacabecalho">
		<td colspan='2'>
			<div align='center'><font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='#FFFFFF'>Detalhes do Funcionário</font></div>
		</td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Nome</b></td>
      <td width='300'><b>Sexo</b></td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'>
        <?echo $campos[0]['nome'];?>
	  </td>
      <td  width='300'>
        <?
			if ($campos[0]['sexo'] == 'M') {
				echo "Masculino";
			}else {
				echo "Feminino";
			}
		?>
      </td>
	</tr>
	<tr class="linhanormal">
		<td width='300'><b>Nacionalidade</b></td>
		<td width='300'><b>Estado Civil</b></td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'>
		<?
			$nacionalidade = $campos[0]['id_nacionalidade'];
			$sql = "select nacionalidade from nacionalidades where id_nacionalidade = '$nacionalidade' LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]["nacionalidade"];
		?>
	  </td>
      <td  width='300'>
		<?
			$civil = $campos[0]['id_civil'];
			$sql = "select estado_civil from estados_civils where id_civil = $civil and ativo=1 LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]["estado_civil"];
		?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Naturalidade</b></td>
	  <td width='300'><b>Nível Acadêmico</b></td>
	</tr>
	<tr class="linhanormal">
 		<td width='300'>
        	<?echo $campos[0]['naturalidade'];?>
		</td>
		<td width='300'>
 		<?
			$academico = $campos[0]['id_academico'];
			$sql = "select nivel from niveis_academicos where id_academico = $academico and ativo=1 LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]["nivel"];
		?>
		</td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'><b>Tipo de Sangue</b></td>
      <td  width='300'><b>Data de Nascimento</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
		<?
			$sangue = $campos[0]['id_sangue'];
			$sql = "select sangue from tipos_sangues where id_sangue = $sangue and ativo=1 LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]["sangue"];
		?>
	  </td>
	  <td width='300'>
        <?=data::datetodata($campos[0]['data_nascimento'],"/");?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'><b>RG</b></td>
      <td  width='300'><b>Orgão Expedidor</b></td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'>
        <?echo $campos[0]['rg'];?>
	  </td>
      <td width='300'>
        <?echo $campos[0]['orgao_expedidor'];?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'><b>Data de Emissão</b></td>
      <td  width='300'><b>Código do Funcionário</b></td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'>
<?
		if($campos[0]['data_emissao'] == '0000-00-00') {
			echo '&nbsp;';
		}else {
			echo data::datetodata($campos[0]['data_emissao'],"/");
		}
?>
		</td>
		<td  width='300'>
			<?echo $campos[0]['codigo_barra'];?>
		</td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Número da Carteira</b></td>
      <td width='300'><b>Série</b></td>
	</tr>
	<tr class="linhanormal">
		<td width='300'>
			<?echo $campos[0]['carteira_profissional'];?>
		</td>
		<td width='300'>
			<?echo $campos[0]['serie_profissional'];?>
		</td>
	</tr>
	<tr class="linhanormal">
		<td  width='300'><b>PIS</b></td>
		<td  width='300'><b>Título de Eleitor</b></td>
	</tr>
	<tr class="linhanormal">
		<td  width='300'>
			<?echo $campos[0]['pis'];?>
		</td>
		<td width='300'>
			<?echo $campos[0]['titulo_eleitor'];?>
		</td>
	</tr>
	<tr class="linhanormal">
		<td width='35%'><b>CPF</b></td>
		<td width='35%'><b>Empresa</b></td>
	</tr>
	<tr class="linhanormal">
		<td  width='35%'>
			<?echo $campos[0]['cpf'];?>
		</td>
		<td  width='35%'>
		<?
			$sql = "select nomefantasia from empresas where id_empresa = ".$campos[0]['id_empresa']." and ativo=1 LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]["nomefantasia"];
		?>
		</td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Número da Carteira de Habilitação</b></td>
      <td width='300'><b>Categoria da Carteira de Habilitação</b></td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'>
        <?echo $campos[0]['habilitacao'];?>
	  </td>
      <td width='300'>
		<?
			$categoria = $campos[0]['id_categoria'];
			$sql = "select categoria from categorias_cartas where id_categoria = $categoria and ativo=1 LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			if(count($campos2) == 0) {
		?>
			&nbsp;
		<?
			} else {
				echo $campos2[0]["categoria"];
			}
		?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'><b>Endereço</b></td>
      <td  width='300'><b>Número</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
        <?echo $campos[0]['endereco'];?>
	  </td>
      <td width='300'>
        <?echo $campos[0]['numero'];?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Complemento</b></td>
      <td  width='300'><b>Bairro</b></td>
	</tr>
	<tr class="linhanormal">
      <td  width='300'>
        <?echo $campos[0]['complemento'];?>
	  </td>
      <td  width='300'>
        <?echo $campos[0]['bairro'];?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Cidade</b></td>
      <td width='300'><b>Cep</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
        <?echo $campos[0]['cidade'];?>
	  </td>
      <td width='300'>
         <?echo $campos[0]['cep'];?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>País</b></td>
      <td width='300'><b>Unidade Federal</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
		<?
			$id_pais = $campos[0]['id_pais'];
			$sql = "select pais from paises where id_pais = $id_pais LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]["pais"];
		?>
	  </td>
      <td  width='300'>
		<?
			$uf = $campos[0]['id_uf'];
			$sql  = "select sigla from ufs where id_uf = $uf LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]["sigla"];
		?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>DDD Residencial</b></td>
      <td width='300'><b>Telefone Residencial</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
        <?echo $campos[0]["ddd_residencial"];?>
	  </td>
      <td width='300'>
        <?echo $campos[0]["telefone_residencial"];?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>DDD Celular</b></td>
      <td width='300'><b>Telefone Celular</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
        <?echo $resultado6;?>
	  </td>
      <td width='300'>
        <?echo $resultado8;?>
	  </td>
	</tr>
	<tr class="linhanormal">
	  <td width='300'><b>Email</b></td>
	  <td width='300'><b>Departamento</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
        <?echo $campos[0]['email'];?>
	  </td>
	  <td width='300'>
		<?
			$depto  = $campos[0]['id_departamento'];
			$sql = "select departamento from departamentos where id_departamento = $depto LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]["departamento"];
		?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Cargo</b></td>
	  <td width='300'><b>Sálario Fixo</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
		<?
			$cargo = $campos[0]['id_cargo'];
			$sql = "select cargo from cargos where id_cargo = $cargo LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			echo $campos2[0]["cargo"];
		?>
	  </td>
	  <td width='300'>
		<?echo $resultado3;?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Sálario Comissão</b></td>
	  <td width='300'><b>Sálario Hora</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
        <?echo $resultado4;?>
      </td>
	  <td width='300'>
        <?echo $resultado5;?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Data de Admissão</b></td>
      <td width='300'><b>Data de Demissão</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
        <?echo $resultado;?>
	</td>
      <td width='300'>
		<?
			echo $resultado2;
		?>
	  </td>
	</tr>
	<tr class="linhanormal">
      <td width='300'><b>Status</b></td>
      <td width='300'><b>Descrição</b></td>
	</tr>
	<tr class="linhanormal">
      <td width='300'>
	<?
		$matriz[0] = 'Férias';
		$matriz[1] = 'Ativo';
		$matriz[2] = 'Afastado';
		$matriz[3] = 'Demissionario';

		$valor = $campos[0]['status'];
		echo $matriz[$valor];
	?>
	  </td>
      <td width='300'>
        <?echo $campos[0]['descricao'];?>
	  </td>
	</tr>
	<tr class="linhacabecalho">
		<td colspan='2'><div align='center'>
			<input type='button' name='cmdFechar' style="color:red" value='Fechar' title='Fechar' onclick='window.close()' class="botao">
		</div></td>
	</tr>
</table>
</form>
</body>
</html>