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
	$sql = "Update pacs_vs_pis_trat set fator = '$txt_fator_tt5', peso_aco = '$txt_peso_aco5', peso_aco_manual = '$chkt_peso_aco_manual', lote_minimo_fornecedor='$chkt_lote_minimo' where id_pac_pi_trat = '$id_pac_pi_trat' limit 1";
	bancos::sql($sql);
	$valor = 1;
/*Atualização do Funcionário que alterou os dados no custo*/
	$data_sys = date('Y-m-d H:i:s');
	$sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$id_funcionario', `data_sys` = '$data_sys' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
	bancos::sql($sql);
}

/*Nessa parte o sistema já deixa preparado o valor do peso aço da etapa 2, caso o
usuário venha tirar do modo manual e desejar colocar do modo automático*/
$sql = "SELECT id_produto_acabado, id_produto_insumo, comprimento_1, comprimento_2, qtde_lote 
		FROM `produtos_acabados_custos` 
		WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
$campos_etapa2 = bancos::sql($sql);
$id_produto_acabado	= $campos_etapa2[0]['id_produto_acabado'];
$id_produto_insumo	= $campos_etapa2[0]['id_produto_insumo'];
$comprimento_a		= $campos_etapa2[0]['comprimento_1'];
$qtde_lote			= $campos_etapa2[0]['qtde_lote'];
$comprimento_b		= $campos_etapa2[0]['comprimento_2'];
$comprimento_total	= ($comprimento_a + $comprimento_b) / 1000;

//Vou utilizar essa variável + abaixo no JavaScript ...
$produto_etapa_2 = $id_produto_insumo;

//Aqui eu verifico se o "PA Principal" do Custo que foi acessado é do Tipo ESP ...
$sql = "SELECT referencia 
		FROM `produtos_acabados` 
		WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos_pa 		= bancos::sql($sql);
$referencia_pa 	= $campos_pa[0]['referencia'];

$sql = "Select pia.densidade_aco 
		from produtos_insumos pi 
		inner join produtos_insumos_vs_acos pia on pia.id_produto_insumo = pi.id_produto_insumo 
		where pi.id_produto_insumo = '$id_produto_insumo' limit 1 ";
$campos2 = bancos::sql($sql);
if(count($campos2) == 1) {
	$densidade = $campos2[0]['densidade_aco'];
}else {
	$densidade = 1;
}
$peso_aco_kg = $densidade * $comprimento_total;
/*******************************************************************************/
//Busca de um valor para fator custo para etapa 5
$sql = "SELECT valor 
		FROM `variaveis` 
		WHERE `id_variavel` = '10' LIMIT 1 ";
$campos 		= bancos::sql($sql);
$fator_custo5 	= $campos[0]['valor'];

//Seleciona a qtde de itens que existe do produto acabado na etapa 5
$sql = "SELECT COUNT(ppt.id_pac_pi_trat) AS qtde_itens 
		FROM `pacs_vs_pis_trat` ppt 
		INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ppt.id_produto_insumo 
		INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
		WHERE ppt.id_produto_acabado_custo = '$id_produto_acabado_custo' ";
$campos 	= bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado 
passado por parâmetro*/
$sql = "Select ppt.id_pac_pi_trat, u.sigla, pi.id_produto_insumo, pi.discriminacao, ppt.fator AS fator_tt, ppt.peso_aco, ppt.peso_aco_manual, ppt.lote_minimo_fornecedor 
		from pacs_vs_pis_trat ppt 
		inner join produtos_insumos pi on pi.id_produto_insumo = ppt.id_produto_insumo 
		inner join unidades u on u.id_unidade = pi.id_unidade 
		where ppt.id_produto_acabado_custo = '$id_produto_acabado_custo' order by ppt.id_pac_pi_trat asc ";
if(empty($posicao)) $posicao = $qtde_itens;

$campos = bancos::sql($sql, ($posicao - 1), $posicao);
$id_produto_insumo = $campos[0]['id_produto_insumo'];//Aqui eu já me refiro ao PI da etapa 5 mesmo ...

$dados_pi 	= custos::preco_custo_pi($campos[0]['id_produto_insumo']);
$preco_pi 	= $dados_pi['preco_comum'];
$icms 		= $dados_pi['icms'];
?>
<html>
<head>
<title>.:: Alterar Custo de Trat. Térmico / Galvanoplastia ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript'>
function calcular(usuario_digitando, desmarcar_peso_aco_manual) {
//Tratamento com as variáveis ...
	var fator_custo = eval('<?=$fator_custo5;?>')
	var preco_kg_rs = eval(strtofloat(document.form.txt_preco_unitario_kg_rs5.value))
	var peso_aco = (document.form.txt_peso_aco5.value != '') ? eval(strtofloat(document.form.txt_peso_aco5.value)) : 0
	var fator_tt = (document.form.txt_fator_tt5.value != '') ? eval(strtofloat(document.form.txt_fator_tt5.value)) : 0
/****************************************************************************/
/***************************Início com os Cálculos***************************/ 
/****************************************************************************/
/********* Parte Nova de Código de Adaptação do Roberto **********/

//1) Peso / Peça corrigido pelo Fator
	if(document.form.chkt_peso_aco_manual.checked == true) {
		document.form.txt_peso_peca_corrigo.value = peso_aco
	}else {//Aqui só iguala as caixas
//Aqui é o cálculo para o novo campo que apareceu peso de peça corrigido
		document.form.txt_peso_peca_corrigo.value = fator_tt * peso_aco
	}
	document.form.txt_peso_peca_corrigo.value = arred(document.form.txt_peso_peca_corrigo.value, 3, 1)

//2) Peso do Aço
	var produto_etapa_2 = eval('<?=$produto_etapa_2;?>')
	if(produto_etapa_2 == 0) {//Significa que não existe Produto Insumo atrelado na Etapa 2 ...
		if(desmarcar_peso_aco_manual == 1) {//Caso o usuário tenha desmarcado o checkbox ...
			if(document.form.chkt_peso_aco_manual.checked == false) {
				alert('ESSA OPÇÃO NÃO PODE SER DESMARCADA, DEVIDO NÃO EXISTIR ITEM NA 2ª ETAPA !')
			}
		}
		document.form.txt_fator_tt5.disabled = true
		document.form.txt_fator_tt5.value = '0,00'
		document.form.txt_fator_tt5.style.color = 'gray'
		document.form.txt_fator_tt5.style.background = '#FFFFE1'
		document.form.chkt_peso_aco_manual.checked = true
		document.form.txt_peso_aco5.disabled = false
//Se o usuário não está digitando, então redefini esse valor com o Peso da 5ª Etapa ...
		if(usuario_digitando == 0) {//onload ...
			document.form.txt_peso_aco5.value = "<?=number_format($campos[0]['peso_aco'], 3, ',', '.');?>"
		}
		document.form.txt_peso_aco5.style.color = 'Brown'
		document.form.txt_peso_aco5.style.background = '#FFFFFF'
		document.form.txt_peso_aco5.focus()
	}else {//Quando existir algum PI, então ...
//Verifico qual é a unidade do Produto Insumo
		var unidade_insumo = "<?=$campos[0]['sigla'];?>"
//Quando for Diferente de Unidade Realiza o Cálculo Normalmente
		if(unidade_insumo != 'UN') {//Se for Kg, ...
			if(document.form.chkt_peso_aco_manual.checked == true) {//Checkbox Habilitado
				document.form.txt_fator_tt5.disabled = true
				document.form.txt_fator_tt5.value = '0,00'
				document.form.txt_fator_tt5.style.color = 'gray'
				document.form.txt_fator_tt5.style.background = '#FFFFE1'
				document.form.txt_peso_aco5.disabled = false
//Se o usuário não está digitando, então redefini esse valor com o Peso da 5ª Etapa ...
				if(usuario_digitando == 0) {
					document.form.txt_peso_aco5.value = "<?=number_format($campos[0]['peso_aco'], 3, ',', '.');?>"
				}
				document.form.txt_peso_aco5.style.color = 'Brown'
				document.form.txt_peso_aco5.style.background = '#FFFFFF'
				document.form.txt_peso_aco5.focus()
			}else {//Checkbox Desabilitado
				document.form.txt_peso_aco5.disabled = true
				if(desmarcar_peso_aco_manual == 1) {//Caso o usuário tenha desmarcado o checkbox ...
					document.form.txt_fator_tt5.value = '0,00'
				}else {
//Iguala com o Peso da 2ª Etapa ...
					document.form.txt_peso_aco5.value = "<?=number_format($peso_aco_kg, 3, ',', '.');?>"
				}
				document.form.txt_peso_aco5.style.color = 'gray'
				document.form.txt_peso_aco5.style.background = '#FFFFE1'
				document.form.txt_fator_tt5.disabled = false
				document.form.txt_fator_tt5.style.color = 'Brown'
				document.form.txt_fator_tt5.style.background = '#FFFFFF'
				document.form.txt_fator_tt5.focus()
			}
//Quando for unidade, tem essa particularização para alguns produtos
		}else {//Aqui o sistema sugere 1 no Peso do Aço
			if(document.form.chkt_peso_aco_manual.checked == true) {//Checkbox Habilitado
				document.form.txt_fator_tt5.disabled = true
				document.form.txt_fator_tt5.value = '0,00'
				document.form.txt_fator_tt5.style.color = 'gray'
				document.form.txt_fator_tt5.style.background = '#FFFFE1'
				document.form.txt_peso_aco5.disabled = false
//Se o usuário não está digitando, então redefini esse valor com o Peso da 5ª Etapa ...
				if(usuario_digitando == 0) {
					var peso_aco = eval("<?=$campos[0]['peso_aco'];?>")
				}
				if(peso_aco != 1) {
					document.form.txt_peso_aco5.value = peso_aco
				}else {
					document.form.txt_peso_aco5.value = 1
				}
				document.form.txt_peso_aco5.value = arred(document.form.txt_peso_aco5.value, 3, 1)
				document.form.txt_peso_aco5.style.color = 'Brown'
				document.form.txt_peso_aco5.style.background = '#FFFFFF'
				document.form.txt_peso_aco5.focus()
			}else {//Checkbox Desabilitado
				document.form.txt_peso_aco5.disabled = true
				if(desmarcar_peso_aco_manual == 1) {//Caso o usuário tenha desmarcado o checkbox ...
					document.form.txt_fator_tt5.value = '0,00'
				}else {
//Iguala com o Peso da 2ª Etapa ...
					document.form.txt_peso_aco5.value = "<?=number_format($peso_aco_kg, 3, ',', '.');?>"	
				}
				document.form.txt_peso_aco5.style.color = 'gray'
				document.form.txt_peso_aco5.style.background = '#FFFFE1'
				document.form.txt_fator_tt5.disabled = false
				document.form.txt_fator_tt5.style.color = 'Brown'
				document.form.txt_fator_tt5.style.background = '#FFFFFF'
				document.form.txt_fator_tt5.focus()
			}
		}
	}
//3) Total em R$ ...
	if(document.form.chkt_peso_aco_manual.checked == true) {
		var custo_normal_etapa5 = (preco_kg_rs * peso_aco)
	}else {
		var custo_normal_etapa5 = (fator_tt * preco_kg_rs * peso_aco)
	}
/*Se a opção calculo por lote mínimo, estiver marcada, então aciono esse calculo abaixo de 
lote minimo por fornecedor default por pedido*/
	if(document.form.chkt_lote_minimo.checked == true) {//Lote Mínimo marcado ...
		var lote_minimo_custo_tt 		= eval(strtofloat(document.form.txt_lote_minimo.value))
		var custo_lote_minimo_etapa5 	= lote_minimo_custo_tt / eval(<?=$qtde_lote;?>)
		var tot_rs_sem_icms 			= eval(strtofloat(document.form.txt_total5.value))
		if(custo_normal_etapa5 < custo_lote_minimo_etapa5) custo_normal_etapa5 = custo_lote_minimo_etapa5
	}
	document.form.txt_total5.value = custo_normal_etapa5 * (100 - '<?=$icms;?>') / 100
	document.form.txt_total5.value = arred(document.form.txt_total5.value, 2, 1)
}

function validar(posicao, verificar) {
//Fator T.T.
	if(document.form.txt_fator_tt5.disabled == false) {
		var fator_tt = eval(strtofloat(document.form.txt_fator_tt5.value))
		if(fator_tt == 0 || typeof(fator_tt) == 'undefined') {
			alert('FATOR T.T. INVÁLIDO ! \nVALOR IGUAL A ZERO OU ESTÁ VÁZIO !')
			document.form.txt_fator_tt5.focus()
			document.form.txt_fator_tt5.select()
			return false
		}
//Se o Peso do Aço Manual estiver desmarcado, então ...
		if(document.form.chkt_peso_aco_manual.disabled == false) {
			if(fator_tt > 1) {
				alert('FATOR T.T. INVÁLIDO !\nVALOR MAIOR DO QUE 1 !')
				document.form.txt_fator_tt5.focus()
				document.form.txt_fator_tt5.select()
				return false
			}
		}
	}
//Peso p/ TT ou Peso do Aço ...
	if(document.form.txt_peso_aco5.disabled == false) {
		var rotulo = document.form.txt_rotulo.value.toUpperCase()
		var peso_aco5 = eval(strtofloat(document.form.txt_peso_aco5.value))
		if(peso_aco5 == 0 || typeof(peso_aco5) == 'undefined') {
			alert(rotulo+' INVÁLIDO ! \nVALOR IGUAL A ZERO OU ESTÁ VÁZIO !')
			document.form.txt_peso_aco5.focus()
			document.form.txt_peso_aco5.select()
			return false
		}
	}
//Desabilita os campos p/ poder gravar no BD ...
	document.form.txt_fator_tt5.disabled = false
	document.form.txt_peso_aco5.disabled = false
	limpeza_moeda('form', 'txt_fator_tt5, txt_peso_aco5, ')
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

//Para não dar problema no início da tela, na hora em q carregar o body
function retornar_foco() {
	if(document.form.txt_fator_tt5.disabled == false) {
		document.form.txt_fator_tt5.focus()
	}
}

function verificar_pa() {
	referencia_pa = '<?=$referencia_pa;?>'
//Se o Produto for normal de Linha, então eu não posso fazer o cálculo por Lote Mínimo ...
	if(referencia_pa != 'ESP') {
		if(document.form.chkt_lote_minimo.checked == true) {
			alert('ESSE PRODUTO É NORMAL DE LINHA !!!\nPORTANTO ESSA OPÇÃO NÃO PODE SER MARCADA ! ')
			document.form.chkt_lote_minimo.checked = false
			return false
		}
	}
}

function adicionar_novo() {
	document.form.nao_atualizar.value = 1
	window.location = 'incluir_tratamento_termico.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'
}

function controlar_rotulo() {
	if(document.form.chkt_peso_aco_manual.checked == true) {//Checkbox Habilitado
		document.form.txt_rotulo.value = 'Peso p/ TT '
	}else {
		document.form.txt_rotulo.value = 'Peso do Aço '
	}
}
</Script>
</head>
<body onload="calcular();controlar_rotulo();retornar_foco()" onunload="atualizar_abaixo()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar('<?=$posicao;?>', 1)">
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='id_pac_pi_trat' value="<?=$campos[0]['id_pac_pi_trat'];?>">
<input type='hidden' name='nao_atualizar'>
<table width='780' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr onClick="showHide('calculo_fator_tt'); return false">
		<td height="18" align="center">
			<font color="yellow" size="2">
				&nbsp;
			</font>
		</td>
		<td align="right">
			&nbsp;
			<span id="statuscalculo_fator_tt"></span>
			<span id="statuscalculo_fator_tt"></span>
		</td>
	</tr>
	<tr onClick="showHide('calculo_fator_tt'); return false">
		<td colspan="2">
			<iframe src="calculo_fator_tt.php?tela=<?=$tela;?>" name="calculo_fator_tt" id="calculo_fator_tt" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="260" width="100%" scrolling="auto"></iframe>
		</td>
	</tr>
</table>
<table width='650' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'>
		<td colspan='2'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			5&ordf; Etapa - Alterar Custo de Trat. Térmico / Galvanoplastia
		</td>
	</tr>
	<tr class='linhadestaque'>
		<td colspan="2">
			<font color='#FFFFFF' size='-1'>
				<font color="#FFFF00">Ref.:</font>
					Trat - <font color="#FFFF00">Und.:</font>
					<?=$campos[0]['sigla'];?> - <font color="#FFFF00">Discrim.:</font>
					<?=$campos[0]['discriminacao'];?>
				</font>
			<font color='#FFFFFF' size='-1'>&nbsp; </font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td> Fator T.T.: </td>
		<td>
			<input type="text" name="txt_fator_tt5" value="<?=number_format($campos[0]['fator_tt'], 2, ',', '.');?>" id="txt_fator_tt5" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular(1)" size="8" class="caixadetexto">
			<img src="../../../../imagem/icones/calculadora3.jpg" width="16" height="17" onClick="showHide('calculo_fator_tt'); return false" title="Cálculo p/ Fator de Tratamento Térmico" alt="Cálculo p/ Fator de Tratamento Térmico">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>Fornecedor Default</td>
		<td>
		<?
			$id_fornecedor_default = custos::preco_custo_pi($campos[0]['id_produto_insumo'], 0, 1);
			//Aqui eu pego o Lote Mínimo do Fornecedor Default encontrado através do PI na lista de Preço de Compras ...
			$sql = "SELECT f.razaosocial, fpi.lote_minimo_reais 
                                FROM `fornecedores` f 
                                INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.id_fornecedor = f.id_fornecedor AND fpi.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' 
                                WHERE f.`id_fornecedor` = '$id_fornecedor_default' LIMIT 1 ";
			$campos_fornec = bancos::sql($sql);
			echo $campos_fornec[0]['razaosocial'];
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>Valor do Lote M&iacute;nimo R$</td>
		<td>
			<input type="text" name="txt_lote_minimo" value="<?=number_format($campos_fornec[0]['lote_minimo_reais'], 2, ',', '.');?>" size="12" id="txt_lote_minimo" class="disabled" disabled>
			<?
				$checked_lote_minimo = ($campos[0]['lote_minimo_fornecedor'] == 1) ? 'checked' : '';
				//Se o Produto for normal de Linha, então eu não posso fazer o cálculo por Lote Mínimo ...
				if($referencia_pa != 'ESP') {
					//Se o checkbox estava desmarcado, então já desabilito essa opção p/ evitar futuros erros ...
					if($checked_lote_minimo == '') $disabled = 'disabled';
				}
			?>
			<input type="checkbox" name="chkt_lote_minimo" value="1" id="chkt_lote_minimo" onClick="verificar_pa();calcular()" <?=$checked_lote_minimo;?> class="checkbox" <?=$disabled;?>>
			<label for="chkt_lote_minimo">C&aacute;lculo por Lote Mínimo</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			P.Unit.R$ - ICMS c/Red
		</td>
		<td>
			<input type="text" name="txt_preco_unitario_kg_rs5" value="<?=number_format($preco_pi, 2, ',', '.');?>" size="12" class="disabled" disabled>
			/ <?=$campos[0]['sigla'];?><font color="red"><b> - <?=number_format($icms, 2, ',', '.');?> %</b></font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type='text' name='txt_rotulo' style='color:#000000' class='caixadetexto2' disabled>
		</td>
		<td>
			<?
                            /*Sempre que a sigla da Unidade = UN a opção de Fator T.T. será igual a 0,0 e a opção Alterar - 
                            Peso / Peça ficará selecionada, agora o porque eu não me lembro e nem o Roberto ... rsrs*/
                            if($campos[0]['sigla'] == 'UN') {//Quando for = a Unidade
                                $checked = 'checked';
                            }else {//Quando for != Unidade ...
                                $checked = ($campos[0]['peso_aco_manual'] == 1) ? 'checked' : '';
                            }
			?>
			<input type="text" name="txt_peso_aco5" value="<?=number_format($campos[0]['peso_aco'], 3, ',', '.');?>" onKeyUp="verifica(this, 'moeda_especial', '3', '', event);calcular(1)" id="txt_peso_aco5" size="7" class="disabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>&nbsp;</td>
		<td>
			<input type="checkbox" name="chkt_peso_aco_manual" value="1" id="alterar" onclick="calcular(1, 1);controlar_rotulo()" <?=$checked;?> class="checkbox">
			<label for="alterar">Alterar</label>
			&nbsp;-&nbsp;Peso / Peça corrigido pelo Fator
			<input type="text" name="txt_peso_peca_corrigo" id="txt_peso_peca_corrigo" size="7" class="disabled" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>Tot.R$ s/ICMS</td>
		<td>
			<input type="text" name="txt_total5" value="<?=number_format($custo_normal_etapa5 * (100 - $icms) / 100, 2, ',', '.');?>" id="txt_total5" size="12" class="disabled" disabled>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			<input type="button" name="cmd_adicionar_novo" value="Adicionar Novo" title="Adicionar Novo" onclick="validar('<?=$posicao;?>');adicionar_novo()" class="botao">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');calcular();retornar_foco()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
			<input type="button" name="cmd_alterar_fornecedores" value="Alterar Fornecedores" title="Alterar Fornecedores" onClick="showHide('alterar_fornecedores'); return false" style="color:black" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao"> 
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="2">
			CRIAR UMA FUNÇÃO PARA CALCULAR A QUINTA ETAPA USANDO AJAX NESSE POP-UP<BR>
		</td>
	</tr>
	<tr align="center"> 
		<td colspan="2">
			&nbsp;
		</td>
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
				}else {
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
<pre>
* Sempre que a sigla da Unidade = <font color='darkblue'><b>'UN'</b></font> a opção de Fator T.T. será igual a 0,0 e a opção <b>Alterar - 
Peso / Peça</b> ficará selecionada.
</pre>
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
	//Verifico se esse PI corrente está em algum Pedido de Compras ...
	$sql = "SELECT id_item_pedido 
                FROM `itens_pedidos` 
                WHERE `id_produto_insumo = '$id_produto_insumo' limit 1 ";
	$campos_pi = bancos::sql($sql);
	if(count($campos_pi) == 0) {//Como não está, exibo essa Tela com Todos os Fornecedores desse PI ...
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
