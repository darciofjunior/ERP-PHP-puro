<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos_new.php');
//require('../../../../lib/genericas.php');
session_start('funcionarios');
if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}
$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if($passo == 1) {
	$sql = "Update pacs_vs_pis set qtde = '$txt_qtde3' where id_pac_pi = '$id_pac_pi' limit 1";
	bancos::sql($sql);
	$valor = 1;
/*Atualização do Funcionário que alterou os dados no custo*/
	$data_sys = date('Y-m-d H:i:s');
	$sql = "Update produtos_acabados_custos set id_funcionario = '$id_funcionario', data_sys = '$data_sys' where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1";
	bancos::sql($sql);
}
//Busca de um valor para fator custo para etapa 3
$sql = "SELECT valor 
		FROM `variaveis` 
		WHERE `id_variavel` = '12' LIMIT 1 ";
$campos = bancos::sql($sql);
$fator_custo3 = $campos[0]['valor'];

//Seleciona a qtde de itens que existe do produto acabado na etapa 3
$sql = "SELECT COUNT(pp.id_pac_pi) AS qtde_itens 
		FROM `pacs_vs_pis` pp 
		INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = pp.id_produto_insumo 
		WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.id_pac_pi ASC ";
$campos = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
$sql = "SELECT pp.id_pac_pi, g.referencia, pi.id_produto_insumo, pi.discriminacao, pp.qtde, u.sigla 
		FROM `pacs_vs_pis` pp 
		INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = pp.id_produto_insumo 
		INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
		INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
		WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.id_pac_pi ASC ";
if(empty($posicao)) $posicao = $qtde_itens;
$campos = bancos::sql($sql, ($posicao - 1), $posicao);
$id_produto_insumo 			= $campos[0]['id_produto_insumo'];//Aqui é o PI da etapa 3 ...
?>
<html>
<head>
<title>.:: Alterar Produto Insumo ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript'>
function calculo_etapa3() {
	var fator_custo = eval('<?=$fator_custo3;?>')
	var qtde = eval(strtofloat(document.form.txt_qtde3.value))
	var preco_unitario_rs = eval(strtofloat(document.form.txt_preco_unitario_rs3.value))
	document.form.txt_total3.value = qtde * preco_unitario_rs * fator_custo

	if(isNaN(document.form.txt_total3.value)) {
		document.form.txt_total3.value = ''
	}else {
		document.form.txt_total3.value = arred(document.form.txt_total3.value, 2, 1)
	}
}

function validar(posicao, verificar) {
	var quantidade = eval(strtofloat(document.form.txt_qtde3.value))
	if(quantidade == 0 || typeof(quantidade) == 'undefined') {
		alert('QUANTIDADE INVÁLIDA ! \nVALOR IGUAL A ZERO OU ESTÁ VÁZIO !')
		document.form.txt_qtde3.focus()
		document.form.txt_qtde3.select()
		return false
	}
	limpeza_moeda('form', 'txt_qtde3, ')
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
	window.location = 'incluir_produto_insumo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4' onload="document.form.txt_qtde3.focus()" onunload="atualizar_abaixo()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar('<?=$posicao;?>', 1)">
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='id_pac_pi' value="<?=$campos[0]['id_pac_pi'];?>">
<input type='hidden' name='nao_atualizar'>
<table width='780' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'> 
		<td colspan='2'> <b> 
			<?=$mensagem[$valor];?>
		</b> </td>
	</tr>
	<tr class='linhacabecalho' align='center'> 
		<td colspan="2"> 3&ordf; Etapa: Alterar Produto Insumo </td>
	</tr>
	<tr class='linhadestaque'> 
		<td height="20" colspan="2">
			<font color='#FFFFFF' size='-1'> <font color="#FFFF00">Ref.:</font> 
				<?=$campos[0]['referencia'];?>
				- <font color="#FFFF00">Unid.:</font> 
				<?=$campos[0]['sigla'];?>
				- <font color="#FFFF00">Discrim.:</font> 
				<?=$campos[0]['discriminacao'];?>
				</font> <font color='#FFFFFF' size='-1'>&nbsp; </font> <font color='#FFFFFF' size='-1'>&nbsp; 
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td width="126"> Estoque: </td>
		<td width="417"> 
			<?
				$sql = "Select qtde as qtde_estoque from estoques_insumos where id_produto_insumo = ".$campos[0]['id_produto_insumo']." limit 1";
				$campos2 = bancos::sql($sql);
				if(count($campos2) == 1) {
					$qtde_estoque = number_format($campos2[0]['qtde_estoque'], 2, ',', '.');
				}else {
					$qtde_estoque = '0,00';
				}
			?>
			<input type="text" name="txt_estoque3" value="<?=$qtde_estoque;?>" id="txt_estoque3" size="12" class="disabled" disabled> 
		</td>
	</tr>
	<tr class='linhanormal'> 
		<td> Quantidade: </td>
		<td>
			<input type="text" name="txt_qtde3" value="<?=number_format($campos[0]['qtde'], 1, ',', '.');?>" size="12" onKeyUp="verifica(this, 'moeda_especial', '1', '', event);calculo_etapa3()" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'> 
		<td>
			<?
				$dados_pi 	= custos::preco_custo_pi($campos[0]['id_produto_insumo']);
				$preco_pi 	= $dados_pi['preco_comum'];
				$icms 		= $dados_pi['icms'];
			?>
			P.Unit.R$ - ICMS c/Red
		</td>
		<td> 
			<input type="text" name="txt_preco_unitario_rs3" value="<?=number_format($preco_pi, 2, ',', '.');?>" size="12" class="disabled" disabled>
			/ <?=$campos[0]['sigla'];?><font color="red"><b> - <?=number_format($icms, 2, ',', '.');?> %</b></font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Tot.R$ s/ICMS
		</td>
		<td>
			<input type="text" name="txt_total3" value="<?=number_format($campos[0]['qtde'] * $preco_pi * (100 - $icms) / 100, 2, ',', '.');?>" id="txt_total3" size="12" class="disabled" disabled>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_adicionar_novo" value="Adicionar Novo" title="Adicionar Novo" onclick="validar('<?=$posicao;?>');adicionar_novo()" class="botao">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');calculo_etapa3()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
			<input type="button" name="cmd_alterar_fornecedores" value="Alterar Fornecedores" title="Alterar Fornecedores" onClick="showHide('alterar_fornecedores'); return false" style="color:black" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao"> 
		</td>
	</tr>
	<tr align="center"> 
		<td colspan="2">&nbsp;</td>
	</tr>
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
<!--Agora sempre irá mostrar esse Iframe-->
<table width='870' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr>
		<td height="18" align="center">
			<font color="yellow" size="2">&nbsp;
				
			</font>
		</td>
		<td align="right">
			&nbsp;
			<span id="statusalterar_fornecedores"></span>
			<span id="statusalterar_fornecedores"></span>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<iframe src='../../../classes/produtos_insumos/marcar_fornecedor_default.php?id_produto_insumo=<?=$id_produto_insumo;?>' name="alterar_fornecedores" id="alterar_fornecedores" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="260" width="100%" scrolling="auto"></iframe>
		</td>
	</tr>
</table>
<!--Controle para saber se vai estar mostrando este Iframe para o Usuário-->
<?
//Verifico se esse PI corrente está em algum Pedido de Compras ...
	$sql = "SELECT id_item_pedido 
			FROM `itens_pedidos` 
			WHERE `id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' LIMIT 1 ";
	$campos_pedidos = bancos::sql($sql);
	if(count($campos_pedidos) == 0) {//Como não está, exibo essa Tela com Todos os Fornecedores desse PI ...
?>
<Script Language = 'JavaScript'>
/*Idéia de Onload

Na primeira vez em que carregar essa Tela, caso venha existir algum Pedido de Compras para esse PI, então 
eu disparo por meio do JavaScript essa função para que já venha mostrar esse iframe ...*/
	showHide('alterar_fornecedores')
</Script>
<?
	}
?>
</form>
</body>
</html>