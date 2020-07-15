<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos_new.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
//require('../../../../lib/genericas.php');
session_start('funcionarios');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}
$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if($passo == 1) {
	$sql = "Update pacs_vs_pas set qtde = '$txt_qtde7' where id_pac_pa = '$id_pac_pa' limit 1";
	bancos::sql($sql);
	$valor = 1;
/*Atualização do Funcionário que alterou os dados no custo*/
	$data_sys = date('Y-m-d H:i:s');
	$sql = "Update produtos_acabados_custos set id_funcionario = '$id_funcionario', data_sys = '$data_sys' where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
	bancos::sql($sql);
}
//Busca de um valor para fator custo para etapa 7
$sql = "Select valor from variaveis where id_variavel = '12' limit 1";
$campos = bancos::sql($sql);
//$fator_custo7 = $campos[0]['valor'];
$fator_custo7 = 1;

//Seleciona a qtde de itens que existe do produto acabado na etapa 7
$sql = "select count(pp.id_pac_pa) qtde_itens ";
$sql.= "from pacs_vs_pas pp, produtos_acabados pa, unidades u ";
$sql.= "where pp.id_produto_acabado_custo = '$id_produto_acabado_custo' and pp.id_produto_acabado = pa.id_produto_acabado and pa.id_unidade = u.id_unidade ";
$campos = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
$sql = "Select pa.referencia, pa.id_produto_acabado, pa.discriminacao, pa.operacao_custo, pa.preco_unitario, pp.id_pac_pa, pp.qtde, u.sigla ";
$sql.= "from pacs_vs_pas pp, produtos_acabados pa, unidades u ";
$sql.= "where pp.id_produto_acabado_custo = '$id_produto_acabado_custo' and pp.id_produto_acabado = pa.id_produto_acabado and pa.id_unidade = u.id_unidade order by pp.id_pac_pa asc ";
if(empty($posicao)) {
	$posicao = $qtde_itens;
}
$campos = bancos::sql($sql, ($posicao - 1), $posicao);
?>
<html>
<head>
<title>.:: Alterar Produto Acabado / Componente ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript'>
function calculo_etapa7() {
	var fator_custo = eval('<?=$fator_custo7;?>')
	var qtde = eval(strtofloat(document.form.txt_qtde7.value))
	var preco_unitario_rs = eval(strtofloat(document.form.txt_preco_unitario_rs7.value))
	//document.form.txt_total7.value = qtde * preco_unitario_rs * fator_custo
	document.form.txt_total7.value = qtde * preco_unitario_rs

	if(isNaN(document.form.txt_total7.value)) {
		document.form.txt_total7.value = ''
	}else {
		document.form.txt_total7.value = arred(document.form.txt_total7.value, 2, 1)
	}
}

function validar(posicao, verificar) {
//Força o Preenchimento da Quantidade p/ ser Digitada ...
	if(document.form.txt_qtde7.value == '') {
		alert('DIGITE A QUANTIDADE !')
		document.form.txt_qtde7.focus()
		document.form.txt_qtde7.select()
		return false
	}

	var quantidade = eval(strtofloat(document.form.txt_qtde7.value))
//Se a quantidade for igual a Zero ...
	if(quantidade == 0) {
		var resposta = confirm('A QUANTIDADE DIGITADA É IGUAL A ZERO !!!\n TEM CERTEZA QUE DESEJA CONTINUAR ?')
		if(resposta == false) {
			document.form.txt_qtde7.focus()
			document.form.txt_qtde7.select()
			return false
		}
	}
//Tratamento para gravar no Banco de Dados ...
	limpeza_moeda('form', 'txt_qtde7, ')
//Recupera a posição corrente no hidden, para não dar erro de paginação
	document.form.posicao.value = posicao;
//Aqui é para não atualizar o frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
	atualizar_abaixo()
//Submetendo o Formulário
	document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
	if(document.form.nao_atualizar.value == 0) {
		window.opener.document.form.submit()
	}
}

function adicionar_novo() {
	document.form.nao_atualizar.value = 1
	window.location = 'incluir_produto_acabado.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4' onload="document.form.txt_qtde7.focus()" onunload="atualizar_abaixo()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar('<?=$posicao;?>', 1)">
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='id_pac_pa' value="<?=$campos[0]['id_pac_pa'];?>">
<input type='hidden' name='nao_atualizar'>
<table width='780' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'> 
      <td colspan='2'> <b>
        <?=$mensagem[$valor];?>
        </b> </td>
    </tr>
    <tr class='linhacabecalho' align='center'> 
      <td colspan="2"> 7&ordf; Etapa: Alterar Produto Acabado / Componente </td>
    </tr>
    <tr class='linhadestaque'>
      <td colspan="2"> <font color='#FFFFFF' size='-1'> <font color="#FFFF00">Ref.:</font>
        <?=$campos[0]['referencia'];?>
        -<font color="#FFFF00"> Unid.:</font>
        <?=$campos[0]['sigla'];?>
        - <font color="#FFFF00">Discrim.: </font>
        <?=$campos[0]['discriminacao'];?>
        </font> <font color='#FFFFFF' size='-1'>&nbsp; </font> </td>
    </tr>
    <tr class='linhanormal'>
		<td> O.C.: </td>
		<td>
        <?
			if($campos[0]['operacao_custo'] == 0) {//Industrialização
				echo 'Industrialização';
			}else {//Revenda
				echo 'Revenda';
			}
		?>
      </td>
    </tr>
    <tr class='linhanormal'> 
      <td> Estoque Real: </td>
      <td> 
<?
//Traz a quantidade em estoque do produto acabado
		$estoque_produto = estoque_acabado::qtde_estoque($campos[0]['id_produto_acabado'], '1');
		$estoque_real = number_format($estoque_produto[0], 2, ',', '.');
?>
        <input type="text" name="txt_qtde_estoque7" value="<?=$estoque_real;?>" id="txt_qtde_estoque7" size="12" class="disabled" disabled>
      </td>
    </tr>
    <tr class='linhanormal'>
      <td>Quantidade do Lote: </td>
      <td>
	<?
		$sql = "Select qtde_lote from produtos_acabados_custos where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
		$campos2 = bancos::sql($sql);
	?>
      	<input type="text" name="txt_qtde_lote7" value="<?=$campos2[0]['qtde_lote'];?>" id="txt_qtde_lote7" size="12" class="disabled" disabled>
      </td>
    </tr>
    <tr class='linhanormal'>
      <td> Quantidade: </td>
      <td> <input type="text" name="txt_qtde7" value="<?=number_format($campos[0]['qtde'], 2, ',', '.');?>" id="txt_qtde7" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event);calculo_etapa7()" size="12" class="caixadetexto">
      </td>
    </tr>
    <tr class='linhanormal'>
		<td>P. Unit. R$ (s/ICMS c/Red + s/Emb)</td>
		<?
			//custos::custo_auto_pi_industrializado();//tem q ser antes das chamadas dos metodos todas_etapas(PA); tempo q gasta é quase zero
			if($campos[0]['operacao_custo'] == 0) {//Industrialização
				$preco_custo = custos::todas_etapas($campos[0]['id_produto_acabado'],0);
			}else {
				$preco_custo = custos::pipa_revenda($campos[0]['id_produto_acabado'])/(genericas::variaveis('taxa_financeira_vendas')/100+1);
			}
		?>
      <td> <input type="text" name="txt_preco_unitario_rs7" value="<?=number_format($preco_custo, 2, ',', '.');?>" id="txt_preco_unitario_rs7" onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" size="12" class="disabled" disabled>
        &nbsp;
        <?=$campos[0]['sigla'];?>
      </td>
    </tr>
    <tr class='linhanormal'>
		<td>Tot.R$ s/ICMS</td>	
		<td>
			<input type="text" name="txt_total7" value="<?=number_format($campos[0]['qtde'] * $preco_custo, 2, ',', '.');?>" id="txt_total7" size="12" class="disabled" disabled>
		</td>
    </tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_adicionar_novo" value="Adicionar Novo" title="Adicionar Novo" onclick="validar('<?=$posicao;?>');adicionar_novo()" class="botao">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');calculo_etapa7()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao"> 
		</td>
    </tr>
    <tr class="linhacabecalho" align="center">
		<td colspan="2">
			PRECISA ARRUMAR O CUSTO PA REVENDA E REAVALIAR A 7ª ETAPA DO CUSTO INDUSTRIAL
		</td>
	</tr>
    <tr align="center"> 
      <td colspan="2">&nbsp; </td>
    </tr>
    <?
?>
    <tr align="center">
      <td colspan="2"> 
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
	if($posicao > 1) {
		echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
	}
	for($i = 1; $i <= $qtde_itens; $i++) {
		if($i == $posicao) {
			echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
		} else {
			echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
		}
	}
	if($posicao < $qtde_itens) {
		echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
      </td>
    </tr>
  </table>
</form>
</body>
</html>