<?
require('../../../../lib/segurancas.php');
//Se esse arquivo foi acessado do Custo de Revenda, não puxa o Menu ...
if(empty($pop_up)) require('../../../../lib/menu/menu.php');
require('../../../../lib/custos_new.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/producao.php');

session_start('funcionarios');

//Esse parâmetro é porque essa tela também é puxada de lá da tela de Orçamentos, e daí tem conflito de sessão
//if(empty($ignorar_sessao)) {
	if($tela == 1) {//Veio da tela de Todos os P.A.
            segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_todos.php', '../../../../');
        }else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
            segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
        }
//}

//Função q verifica se os produtos insumos é de valor 0 no estoque
function estoque_insumo_zero($id_produto_insumo) {
	$sql = "SELECT qtde 
			FROM `estoques_insumos` 
			WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
	$campos = bancos::sql($sql);
	return $campos[0]['qtde'];
}

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CUSTO NÃO PODE SER LIBERADO.</font>";
$mensagem[3] = "<font class='atencao'>ESTE ITEM NÃO PODE SER EXCLUÍDO ! DEVIDO EXISTIR ITEM(NS) NA 5ª ETAPA DEPENDENTE(S) DESSE ITEM.</font>";

//Entra só 1 vez nessa parte, pois é passado o id do produto acabado por parâmetro
if(!empty($id_produto_acabado)) {
	if($tela == 1 || $tela == 2) {//Significa que veio da tela de P.A. Industrial
		$condicao = ' operacao_custo = 0';
		$operacao_custo = 0;
	}else {//Significa que veio da tela de P.A. Revenda
		$condicao = ' operacao_custo = 1';
		$operacao_custo = 1;
	}
	$sql = "SELECT id_produto_acabado_custo 
			FROM `produtos_acabados_custos` 
			WHERE `id_produto_acabado` = '$id_produto_acabado' 
			AND $condicao limit 1 ";
	$campos = bancos::sql($sql);
	if(count($campos) == 0) {
		$data_sys = date('Y-m-d H:i:s');
		$sql = "INSERT INTO `produtos_acabados_custos` (`id_produto_acabado`, `qtde_lote`, `comprimento_2`, `operacao_custo`, `data_sys`) values ('$id_produto_acabado', '1', '6.0', '$operacao_custo', '$data_sys') ";
		bancos::sql($sql);
		$id_produto_acabado_custo = bancos::id_registro();
	}else {
		$id_produto_acabado_custo = $campos[0]['id_produto_acabado_custo'];
	}
}

if($desvio == 1) {
//Exclui os itens da etapa conforme o valor passado por parâmetro
	if(!empty($valor)) {
		if($valor == 1) {//Etapa 1
//Busca do Produto Insumo atual antes da Exclusão ...
			$sql = "SELECT id_produto_insumo 
					FROM `pas_vs_pis_embs` 
					WHERE `id_pa_pi_emb` = '$id_pa_pi_emb_item' LIMIT 1 ";
			$campos_pi = bancos::sql($sql);
			$id_produto_insumo_custo = $campos_pi[0]['id_produto_insumo'];//Produto Insumo Atual ...
//Se houve exclusão, então eu chamo a Função ...
			producao::verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado, $id_produto_insumo_custo, 0, 1);
			$sql = "DELETE FROM `pas_vs_pis_embs` WHERE id_pa_pi_emb = '$id_pa_pi_emb_item' LIMIT 1 ";
			bancos::sql($sql);
		}else if($valor == 2) {
/*Antes de excluir o Custo na Etapa 2, eu verifico se existe algum Peso de Aço da Etapa 5, 
que está sem a marcação no checkbox, caso exista, não posso estar excluindo devido esses 
Itens da Etapa 5 depender dos que estão na Etapa 2 ...*/
			$sql = "Select id_produto_acabado_custo 
					from pacs_vs_pis_trat 
					where id_produto_acabado_custo = '$id_produto_acabado_custo' 
					and peso_aco_manual = 0 limit 1 ";
			$campos = bancos::sql($sql);
			if(count($campos) == 0) {//Não existem itens ...
//Busca do Produto Insumo atual antes da Exclusão ...
				$sql = "Select id_produto_insumo 
						from `produtos_acabados_custos` 
						where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1 ";
				$campos_pi = bancos::sql($sql);
				$id_produto_insumo_custo = $campos_pi[0]['id_produto_insumo'];//Produto Insumo Atual ...
//Se houve exclusão, então eu chamo a Função ...
				producao::verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado, $id_produto_insumo_custo, 0, 2);
//Etapa 2 - Exclui o Produto dessa Etapa e registra o Funcionário q fez alt. desses dados ...
				$sql = "UPDATE `produtos_acabados_custos` SET `id_produto_insumo` = '$hdd_produto_insumo', `id_funcionario` = '$_SESSION[id_funcionario]', `qtde_lote` = '0', `peso_kg` = '0.0', `peca_corte`= '0', `comprimento_1`= '0', `comprimento_2` = '0', `observacao` = '', `data_sys` = '$data_sys' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
				bancos::sql($sql);
//Exclui o Peso Aço da Etapa 5, mas somente quando o peso estiver no modo manual = 0
				$sql = "Update pacs_vs_pis_trat set `peso_aco` = '0.0000' where id_produto_acabado_custo = '$id_produto_acabado_custo' and peso_aco_manual = 0 ";
				bancos::sql($sql);
			}else {//Ainda existe pelo menos 1 item da 5ª Etapa que depende de algum da 2ª ...
				$frase = 3;
			}
		}else if($valor == 3) {//Etapa 3
//Busca do Produto Insumo atual antes da Exclusão ...
			$sql = "Select id_produto_insumo 
					from `pacs_vs_pis` 
					where id_pac_pi = '$id_pac_pi_item' limit 1 ";
			$campos_pi = bancos::sql($sql);
			$id_produto_insumo_custo = $campos_pi[0]['id_produto_insumo'];//Produto Insumo Atual ...
//Se houve exclusão, então eu chamo a Função ...
			producao::verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado, $id_produto_insumo_custo, 0, 3);
			$sql = "Delete from pacs_vs_pis where id_pac_pi = '$id_pac_pi_item' limit 1 ";
			bancos::sql($sql);
		}elseif($valor == 4) {//Etapa 4
			$sql = "Delete from pacs_vs_maquinas where id_pac_maquina = '$id_pac_maquina_item' limit 1";
			bancos::sql($sql);
		}elseif($valor == 5) {//Etapa 5
			$sql = "Delete from pacs_vs_pis_trat where id_pac_pi_trat = '$id_pac_pi_trat_item' limit 1";
			bancos::sql($sql);
		}elseif($valor == 6) {//Etapa 6
			$sql = "Delete from pacs_vs_pis_usis where id_pac_pi_usi = '$id_pac_pi_usi_item' limit 1";
			bancos::sql($sql);
		}elseif($valor == 7) {//Etapa 7
			$sql = "Delete from pacs_vs_pas where id_pac_pa = '$id_pac_pa_item' limit 1";
			bancos::sql($sql);
		}
	}
/*******************************************************************************************/
	$data_sys = date('Y-m-d H:i:s');
	//Registra a Data e Hora do Funcionário que fez a última alteração no Custo ...
	$lote_minimo_ignora_faixa_orcavel = (!empty($_POST['chkt_lote_minimo'])) ? 'S' : 'N';
	$sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '$data_sys', `lote_minimo` = '$lote_minimo_ignora_faixa_orcavel' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
	bancos::sql($sql);
/*******************************************************************************************/
	$sql = "Select pa.id_produto_acabado, pa.referencia, pa.operacao_custo, pa.status_custo 
			from produtos_acabados_custos pac 
			inner join produtos_acabados pa on pa.id_produto_acabado = pac.id_produto_acabado 
			where pac.id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1 ";
	$campos = bancos::sql($sql);
//Atualização do custo liberado para o produto acabado
	if(!empty($chkt_custo_liberado)) {//Selecionado
		$acao = 'SIM';
//Antes de cair na função que já faz tudo automático, tem uma condição antes só para o caso o PA ser 'ESP'
		if($campos[0]['referencia'] == 'ESP') {
/*Listagem de Todos os Orçamento(s) que estão em Aberto, q não estão congelados, que contém esse Item
em que o prazo de Entrega seja igual a Imediato*/
			$sql = "SELECT ovi.id_orcamento_venda_item 
					FROM `orcamentos_vendas_itens` ovi 
					INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
					WHERE ovi.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' 
					AND ovi.`prazo_entrega_tecnico` = '0.0' 
					AND ov.`congelar` = 'N' LIMIT 1 ";
			$campos2 = bancos::sql($sql);
			//Se encontrar algum item que tenha o prazo de entrega técnico como zerado, então não pode liberar o custo ...
			if(count($campos2) == 1) $acao = 'NAO';
		}
	}else {//Não selecionado
		$acao = 'NAO';
	}
	custos::liberar_desliberar_custo($id_produto_acabado_custo, $acao);
//Aqui eu mudo o status desse P.A. q foi migrado, p/ 0, p/ dizer q este já foi atualizado
	$sql = "UPDATE `produtos_acabados` SET `pa_migrado` = '0' WHERE `id_produto_acabado` = ".$campos[0]['id_produto_acabado']." LIMIT 1 ";
	bancos::sql($sql);
//Aqui é só para retornar as mensagens

	if($frase != 3) {
		if(!empty($chkt_custo_liberado)) {//Selecionado p/ liberar o Custo
			if($campos[0]['status_custo'] == 1) {//Significa está liberado
				$frase = 1;//Retorno da Frase
			}else {//Não está liberado
				$frase = ($acao == 'NAO') ? 2 : 1;
			}
		}else {//Não está Selecionado pra liberar o Custo
			$frase = 1;
		}
	}
/*************************************************************************/
?>
	<Script Language = 'JavaScript'>
		window.location = 'prod_acabado_componente2.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&parametro_velho=<?=urlencode($parametro_velho);?>&frase=<?=$frase;?>'
	</Script>
<?
}else {
	$fator_custo_2 		= genericas::variavel(11);
	//Essa variável vai estar sendo acionada para o caso de o usuário digitar na qtde um valor maior do que 1000 ...
	$fator_custo_2_new 	= genericas::variavel(18);
	$fator_custo_5_6 	= genericas::variavel(10);

	$sql = "SELECT ed.razaosocial, gpa.nome, pa.id_produto_acabado, pa.id_gpa_vs_emp_div, pa.referencia, pa.discriminacao, pa.operacao_custo, pa.operacao_custo_sub, pa.`desenho_para_op`, pa.observacao as observacao_produto, pac.operacao_custo as operacao_custo_pac, pa.status_custo, pac.id_funcionario AS func, pac.lote_minimo, CONCAT(DATE_FORMAT(SUBSTRING(pac.data_sys, 1, 10), '%d/%m/%Y'), SUBSTRING(pac.data_sys, 11, 9)) AS data_atualizacao 
			FROM `produtos_acabados_custos` pac 
			INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
			INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
			INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
			INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
			WHERE pac.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
	$campos                 = bancos::sql($sql);
	$razaosocial		= $campos[0]['razaosocial'];
	$nome                   = $campos[0]['nome'];
	$id_produto_acabado     = $campos[0]['id_produto_acabado'];
	$id_gpa_vs_emp_div 	= $campos[0]['id_gpa_vs_emp_div'];
	$referencia 		= $campos[0]['referencia'];
	$discriminacao 		= $campos[0]['discriminacao'];
	$desenho_para_op        = $campos[0]['desenho_para_op'];
	$observacao_produto = $campos[0]['observacao_produto'];
	$operacao_custo_pac = $campos[0]['operacao_custo_pac'];
	if($campos[0]['operacao_custo'] == 0) {//Industrialização
		$operacao_custo_rotulo = 'Industrialização';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
		if($campos[0]['operacao_custo_sub'] == 0) {
			$operacao_custo_rotulo.= ' <font color="yellow">(Industrial)</font>';
		}else if($campos[0]['operacao_custo_sub'] == 1) {
			$operacao_custo_rotulo.= ' <font color="yellow">(Revenda)</font>';
		}else {
			$operacao_custo_rotulo.= ' <font color="yellow">(???)</font>';
		}
	}else {//Revenda
		$operacao_custo_rotulo = 'Revenda';
	}
	$func 								= $campos[0]['func'];
	$lote_minimo_ignora_faixa_orcavel 	= $campos[0]['lote_minimo'];
	$data_atualizacao 					= $campos[0]['data_atualizacao'];
	$status_custo 						= $campos[0]['status_custo'];
	//Essa variável estará sendo utilizada no meio das etapas 2 e 3
	$preco_custo_zero 					= 0;
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id, valor) {
	var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
	if(mensagem == false) {
		return false
	}else {
		if(valor == 1) {
			document.form.id_pa_pi_emb_item.value = id
			document.form.valor.value = 1
		}else if(valor == 2) {
			document.form.hdd_produto_insumo.value = ''
			document.form.valor.value = 2
		}else if(valor == 3) {
			document.form.id_pac_pi_item.value = id
			document.form.valor.value = 3
		}else if(valor == 4) {
			document.form.id_pac_maquina_item.value = id
			document.form.valor.value = 4
		}else if(valor == 5) {
			document.form.id_pac_pi_trat_item.value = id
			document.form.valor.value = 5
		}else if(valor == 6) {
			document.form.id_pac_pi_usi_item.value = id
			document.form.valor.value = 6
		}else if(valor == 7) {
			document.form.id_pac_pa_item.value = id
			document.form.valor.value = 7
		}
		document.form.desvio.value = 1
		document.form.submit()
	}
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
	if(typeof(window.opener.document.form.resposta) == 'object') {
		window.opener.document.form.resposta.value = false
//Aqui é para não dar Update na tela de baixo e gravar o valor das caixas abaixo
		window.opener.document.form.ignorar_update.value = 1
		window.opener.document.form.submit()
	}
	window.close()
}

function alterar_produto_acabado(id_produto_acabado) {
//Pop-UP 1 - significa que esta tela está sendo aberta como Pop-UP ...
	nova_janela('../../cadastros/produto_acabado/alterar.php?passo=1&id_produto_acabado='+id_produto_acabado+'&pop_up=1', 'CONSULTAR', '', '', '', '', '450', '780', 'c', 'c', '', '', 's', 's', '', '', '')
}

function liberar_depto_tecnico(tela, id_produto_acabado_custo) {
	var resposta = confirm('ALTERE OS TEMPOS DE MÁQUINA ANTES DE ALTERAR A QUANTIDADE DO LOTE !\n\nSE O LOTE FOR ALTERADO ANTES, O CUSTO DE "DEPTO TÉCNICO" SERÁ AUTOMATICAMENTE LIBERADO PARA O ORÇAMENTO !\n\nDESEJA ALTERAR OS TEMPOS DE MÁQUINA AGORA ?')
//Se falar que não, significa que deseja alterar a quantidade do lote
	if(resposta == false) {
		nova_janela('alterar_quantidade_lote.php?tela='+tela+'&id_produto_acabado_custo='+id_produto_acabado_custo, 'CONSULTAR', '', '', '', '', '500', '780', 'c', 'c', '', '', 's', 's', '', '', '')
		//nova_janela('liberar_depto_tecnico.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo?>', 'CONSULTAR', '', '', '', '', '1', '1', 'c', 'c', '', '')
//Se falar que sim, significa que deseja alterar os tempos de máquina primeiro
	}
}

function confirmar_lote_minimo() {
    if(document.form.chkt_lote_minimo.checked) {//Se checou ...
        var resposta = confirm('TEM CERTEZA DE QUE DESEJA MARCAR LOTE MÍNIMO PARA ESSE CUSTO ?')
        if(resposta == true) {
            document.form.desvio.value = 1
            document.form.submit()
        }else {
            document.form.chkt_lote_minimo.checked = false
        }
    }else {//Se deschecou ...
        document.form.desvio.value = 1
        document.form.submit()
    }
}

function ativar_loading() {
	document.form.submit()
}
</Script>
</head>
<body bgcolor='#FFFFFF' text='#000000' link='#6473D4' vlink='#6473D4' alink='#6473D4'>
<form name='form' method='post' action=''>
<input type='hidden' name='id_pa_pi_emb_item'>
<input type='hidden' name='id_pac_pi_item'>
<input type='hidden' name='id_pac_maquina_item'>
<input type='hidden' name='id_pac_pi_trat_item'>
<input type='hidden' name='id_pac_pi_usi_item'>
<input type='hidden' name='id_pac_pa_item'>
<input type='hidden' name='valor'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<?
//Vai entrar aqui somente na primeira em que carregar a tela, //Demais vezes ...
	$parametro_velho = (empty($parametro_velho)) ? $parametro : $parametro_velho;
?>
<input type="hidden" name="parametro_velho" value="<?=$parametro_velho;?>">
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align='center'>
		<td colspan='8'>
			<b><?=$mensagem[$frase];?></b>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="8">
			<font color="#00FF00" size="2">
				<b>CUSTO INDUSTRIAL</b>
			</font>
			<?
				$checked = ($status_custo == 1) ? 'checked' : '';
				//Se o País = Brasil, então eu travo esse campo que bloqueia e desbloqueia o Custo - ordens do Roberto ...
				if($id_pais == 31) $disabled = 'disabled';
			?>
			<input type="checkbox" name="chkt_custo_liberado" value="1" id="checar" title="Custo Liberado" onclick="confirmar_liberacao()" <?=$checked;?> <?=$disabled;?> class="checkbox">
			<label for="checar">
				<b>LIBERADO</b>
			</label>
		</td>
	</tr>
 	<tr class="linhadestaque" align="left">
		<td colspan="8">
			<font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" title="Produto Acabado / Componente" size="2">
				<b><font color="yellow">PA/C: </font></b>
				<?
//Aqui verifico a qual família que pertence esse PA
					$sql = "Select gp.id_familia 
							from gpas_vs_emps_divs ged 
							inner join grupos_pas gp on gp.id_grupo_pa = ged.id_grupo_pa 
							where ged.id_gpa_vs_emp_div = '$id_gpa_vs_emp_div' ";
					$campos2 = bancos::sql($sql);
//Se a família desse PA, for pertencente a família de Componentes, então mostra outro caminho.
					if($campos2[0]['id_familia'] == 23) {
						$url = '../../../classes/producao/alterar_prazo_entrega_normal.php?';
					}else {
						if($referencia == 'ESP') {//Se for Especial
							$url = '../../../classes/producao/alterar_prazo_entrega_esp.php?';
						}else {
							$url = '../../../classes/producao/alterar_prazo_entrega_normal.php?';
						}
					}
				?>
				<?=' * '.intermodular::pa_discriminacao($id_produto_acabado);?>
				&nbsp;&nbsp;<img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Produto Acabado" alt="Alterar Produto Acabado" onClick="alterar_produto_acabado('<?=$id_produto_acabado;?>')">
			</font>
			&nbsp;
			<a href="javascript:nova_janela('../../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class="link">
				<img src="../../../../imagem/visualizar_detalhes.png" title="Visualizar Pedidos - Últimos 6 meses" alt="Visualizar Pedidos - Últimos 6 meses" border="0">
			</a>
			&nbsp;
			<a href="javascript:nova_janela('../../../vendas/relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class="link">
				<img src="../../../../imagem/propriedades.png" title="Visualizar Orçamentos - Últimos 6 meses" alt="Visualizar Orçamentos - Últimos 6 meses" border="0">
			</a>
			&nbsp;-&nbsp;
			<font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" size="2">
				<b title="Operação de Custo" style="cursor:help"><font color="yellow">O.C.:</font></b>
				<?=$operacao_custo_rotulo;?>
			</font>
		</td>
	</tr>
	<tr class="linhadestaque">
		<td colspan="8">
			<font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" size="2">
				<font color="yellow">Grupo PA:</font>
				<?=$nome;?>
			</font>
			&nbsp;-&nbsp;
			<font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" size="2">
				<font color="yellow">Empresa Divisão</font>
				<?=$razaosocial;?>
			</font>
		</td>
	</tr>
<?
//Significa que já teve alguma alteração do usuário em relação a esse custo
	if($func != 0) {
		$sql = "Select nome 
				from funcionarios 
				where id_funcionario = '$func' limit 1 ";
		$campos2 = bancos::sql($sql);
		$nome = $campos2[0]['nome'];
?>
	<tr class="linhadestaque" align="center">
		<td colspan="8">
			<b><font color="#FFFF00">Última alteração realizada por:</font></b>
			<?=$nome;?>
			&nbsp;-&nbsp; <b><font color="#FFFF00">Data e Hora de Atualização:</font></b>
			<?=$data_atualizacao;?>
		</td>
	</tr>
<?
	}
//Se for Especial exibe para Alterar o Prazo Técnico
	if($referencia == 'ESP') {
?>
	<tr><td></td></tr>
	<tr class="iframe" onClick="showHide('alterar_prazo_entrega_tecnico'); return false" style="cursor:pointer;">
		<td colspan="8" height="22" align="left">
			Alterar Prazo de Entrega Sugerido pelo Depto. Técnico
			<span id="statusalterar_prazo_entrega_tecnico">&nbsp;</span>
			<span id="statusalterar_prazo_entrega_tecnico">&nbsp;</span>
		</td>
	</tr>
	<tr>
		<td colspan="8">
<!--Eu passo a origem por parâmetro também para não dar erro de URL na parte de detalhes da conta e de cheque-->
			<iframe src="<?=$url.'id_produto_acabado='.$id_produto_acabado;?>" name="alterar_prazo_entrega_tecnico" id="alterar_prazo_entrega_tecnico" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="185" width="100%" scrolling="auto"></iframe>
		</td>
	</tr>
<?
	}
?>
	<tr align="left">
		<td colspan="8" bgcolor="#CCCCCC">
			<font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" size="2">
				<b>Observação do Produto: </b>
				<a href="javascript:nova_janela('observacao_produto.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', '250', '780', 'c', 'c', '', '', 's', 's', '', '', '')" alt="Alterar Observação do Produto" title="Alterar Observação do Produto" class="link">
				<?
					$observacao_produto = trim($observacao_produto);
					if(empty($observacao_produto)) {
						echo 'SEM OBSERVAÇÃO';
					}else {
						echo $observacao_produto;
					}
				?>
				</a>
				<?
/*Se existir algum desenho anexado p/ essa P.A., então eu exibo essa palavra de desenho 
junto desse ícone de Impressora ...*/
					if(!empty($desenho_para_op)) {
				?>
					<font face="Verdana, Geneva, Arial, Helvetica, sans-serif" title="Existe desenho anexado p/ este P.A" style="cursor:help" color="darkgreen" size="1">
                                            - <b>DESENHO</b>
					</font>
					<img src="../../../../imagem/impressora.gif" border="0" title="Existe desenho anexado p/ este P.A" alt="Existe desenho anexado p/ este P.A" style="cursor:pointer">
				<?
					}
				?>
			</font>
		</td>
	</tr>
	<tr align="left">
		<td colspan="4" bgcolor="#CCCCCC">
			<font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" size="2">
				<b>Follow-Up do Produto Acabado (Vendedores e Depto. Técnico): </b>
			</font>
		</td>
		<td colspan="4" bgcolor="#CCCCCC">
			<font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" size="2">
				<a href="javascript:nova_janela('../../cadastros/produto_acabado/follow_up.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', '500', '780', 'c', 'c', '', '', 's', 's', '', '', '')" alt="Registrar Follow_up(s) do Produto" title="Registrar Follow_up(s) do Produto" class="link">
				<?
					$sql = "Select count(id_produto_acabado_follow_up) as total_follow_ups from produtos_acabados_follow_ups where id_produto_acabado = '$id_produto_acabado'";
					$campos = bancos::sql($sql);
					$total_follow_ups = $campos[0]['total_follow_ups'];
					if($total_follow_ups == 0) {
						echo 'NÃO HÁ FOLLOW-UP(S) REGISTRADO(S)';
					}else {
						echo '<font color="red"><marquee width="280">'.$total_follow_ups.' FOLLOW-UP(S) REGISTRADO(S)</marquee></font>';
					}
				?>
				</a>
			</font>
		</td>
	</tr>
 <?
/*Aqui eu fiz uma antecipação de sql da etapa 2, antes mesmo da etapa 1 porque
o campo quantidade de lote se encontra aki antes do loop da etapa 1*/
	$sql = "Select id_produto_insumo, qtde_lote, peso_kg, peca_corte, comprimento_1, comprimento_2 
			from produtos_acabados_custos 
			where id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1 ";
	$campos2 = bancos::sql($sql);
	$id_produto_insumo = $campos2[0]['id_produto_insumo'];
//Qtde Lote
	$qtde_lote 			= $campos2[0]['qtde_lote'];
	$qtde_lote_alert 	= $campos2[0]['qtde_lote'];//Usado para segurança em JavaScript + abaixo ...
/*Aqui verifica se a quantidade do lote é > 1000, porque caso isso aconteça então
sofrerá alterações no valor do fator de custo da Etapa 2*/
	if($qtde_lote > 1000) $fator_custo_2 = $fator_custo_2_new;
//Peso Kg
	$peso_aco_kg = $campos2[0]['peso_kg'];// *1.05 esta parte e no JS
//Peça Corte
	if($campos2[0]['peca_corte'] == 0) {
		$pecas_corte = 1;
	}else {
		$pecas_corte = $campos2[0]['peca_corte'];
	}
//Comprimento A
	$comprimento_a = $campos2[0]['comprimento_1'];
//Comprimento B
	$comprimento_b = $campos2[0]['comprimento_2'];
/*Aqui eu trago o produto acabado do produto acabado custo que está
armazenado em um hidden*/
	$sql = "Select pa.id_produto_acabado, pa.operacao_custo 
			from produtos_acabados_custos pac 
			inner join produtos_acabados pa on pa.id_produto_acabado = pac.id_produto_acabado 
			where pac.id_produto_acabado_custo = '$id_produto_acabado_custo' limit 1 ";
	$campos = bancos::sql($sql);
	$id_produto_acabado = $campos[0]['id_produto_acabado'];
	//Essa já prepara as variáveis para o cálculo das etapas do custo
	$taxa_financeira_vendas = genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
	custos::todas_etapas($id_produto_acabado, $operacao_custo_pac);
?>
	<tr class="linhacabecalho">
		<td>
			<font color="#00FF00" size="2">
				<b><i>Quantidade do Lote => </i></b></font>
			</font>
			<?
				//Verifica se esse id_produto_acabado está em algum Orçamento e se ele tem o seu valor igual a DEPTO TÉCNICO
				$sql = "Select ov.id_orcamento_venda, ovi.id_orcamento_venda_item 
						from orcamentos_vendas_itens ovi 
						inner join orcamentos_vendas ov on ov.id_orcamento_venda = ovi.id_orcamento_venda and ov.congelar = 'N' 
						where ovi.id_produto_acabado = '$id_produto_acabado' 
						and ovi.preco_liq_fat_disc = 'DEPTO TÉCNICO' LIMIT 1 ";
				$campos = bancos::sql($sql);
				$linhas = count($campos);
/*Significa q encontrou 1 item no orc. = a DEPTO TÉCNICO, então tem q informar ao usuário para q ele
alterar primeiro os tempos de máquina*/
				if(empty($tela)) $tela = 0;//Aqui só zero a variável p/ não dar erro de JavaScript nessa função abaixo
				if($linhas == 1) {
					$url = "javascript:liberar_depto_tecnico($tela, $id_produto_acabado_custo)";
				}else {//Não encontrou nenhum item = a DEPTO TÉCNICO
					$url = "javascript:nova_janela('alterar_quantidade_lote.php?tela=$tela&id_produto_acabado_custo=$id_produto_acabado_custo', 'CONSULTAR', '', '', '', '', '160', '800', 'c', 'c', '', '', 's', 's', '', '', '')";
				}
			?>
			<a href="<?=$url;?>" alt="Alterar Quantidade do Lote" title="Alterar Quantidade do Lote" class="link">
				<font color="#FFFFFF" size="2">
					<b><i><?=$qtde_lote;?></i></b>
				</font>
			</a>
		</td>
		<td colspan='7' align='right'>
			<a href="javascript:nova_janela('valor_custo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>', 'POP', '', '', '', '', 450, 700, 'c', 'c')" title="Valor Real do Custo" class="link">
				<font color="#00FF00" size="2">
					<b><i>Total do Custo s/ ICMS R$</i></b></font>
					<font color="#FFFFFF" size="2">
						<b><i><?=number_format($GLOBALS['total_sem_impostos_sem_fator'], 2, ',', '.');?></i></b>
					</font>
				</font>
			</a>
			<?$valor_custo_sem_taxa_financeira = round($GLOBALS['total_sem_impostos_sem_fator'], 2);?>
		</td>
	</tr>
	<tr class="linhadestaque" align="left">
		<td colspan='5'>
			<a href="javascript:nova_janela('incluir_embalagem.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')" title="Atrelar Embalagem">
				<font color="#FFFF00">
					<b><i>1&ordf; Etapa: Atrelar Embalagem(ns)</i></b>
				</font>
			</a>
		</td>
		<td colspan='3' align='right'>
			<font color="#00FF00"><b><i>Total s/ ICMS R$</i></b></font>
				<font color="#FFFFFF">
					<b><i><?=number_format($GLOBALS['etapa1_sem_impostos_sem_fator'], 2, ',', '.');?></i></b>
				</font>
			</font>
			<font color='black'>
                            <?=' | '.number_format($GLOBALS['etapa1_sem_impostos_sem_fator'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                        </font>
		</td>
	</tr>
 <?
/*Aqui traz todas as embalagens que estão relacionadas ao produto acabado
passado por parâmetro*/
	$sql = "Select ppe.id_pa_pi_emb, ppe.pecas_por_emb, ppe.embalagem_default, pi.id_produto_insumo, pi.discriminacao, pi.unidade_conversao, u.sigla 
			from pas_vs_pis_embs ppe 
			inner join produtos_insumos pi on pi.id_produto_insumo = ppe.id_produto_insumo 
			inner join unidades u on u.id_unidade = pi.id_unidade 
			where ppe.id_produto_acabado = '$id_produto_acabado' order by ppe.id_pa_pi_emb ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {
?>
	<tr class="linhanormaldestaque" align="center">
		<td colspan="2">
			<b><i>Ref. Emb - Discriminação</i></b>
		</td>
		<td>
			<b><i>E.P.</i></b>
		</td>
		<td>
			<b><i>Pçs / Emb</i></b>		
		</td>
		<td>
			<b><i>P.Unit.R$ - ICMS c/Red</i></b>
		</td>
		<td>
			<b><i>Tot.R$ s/ICMS</i></b>
		</td>
		<td>&nbsp; </td>
		<td>&nbsp; </td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" align="center">
		<td colspan="2" align="left">
			<?=$campos[$i]['sigla'].' - '.$campos[$i]['discriminacao'];?>
			<a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Compras" class="link">
				<font color='red' style='cursor:help' title='Compra'><b>(Compras)</b></font>
			</a>
		</td>
		<td>
<?
			if($campos[$i]['embalagem_default'] == 1) {//Principal
				echo '<img src="../../../../imagem/certo.gif">';
			}else {
				echo '&nbsp;';
			}
?>
		</td>
		<td>
<?
			if($campos[$i]['unidade_conversao'] > 0.00) {
				echo number_format($campos[$i]['pecas_por_emb'], 3, ',', '.').' / '.number_format($campos[$i]['unidade_conversao'], 2, ',', '.').' ('.number_format(1 / ($campos[$i]['pecas_por_emb'] * $campos[$i]['unidade_conversao']), 2, ',', '.').') ';
			}else {
				echo number_format($campos[$i]['pecas_por_emb'], 3, ',', '.').' / <font color="red" title="Sem Conversão">S. C.</font>';
			}
?>
		</td>
		<?
			$dados_pi 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
			$preco_pi 	= $dados_pi['preco_comum'];
			$icms 		= $dados_pi['icms'];
		?>
		<td>
			<?=number_format($preco_pi, 2, ',', '.').'<font color="red"><b> - '.number_format($icms, 2, ',', '.').' %</b></font>';?>
		</td>
		<td>
<?
			$unidade_conversao = $campos[$i]['unidade_conversao'];
//Para não dar erro de divisão no cálculo abaixo
			if($unidade_conversao == 0) $unidade_conversao = 1;
			$total = ((1 / $unidade_conversao) / $campos[$i]['pecas_por_emb']) * $preco_pi;
			echo number_format($total * (100 - $icms) / 100, 2, ',', '.');
?>
		</td>
		<td>
			<img src="../../../../imagem/menu/alterar.png" border='0' onClick="html5Lightbox.showLightbox(7, 'alterar_etapa1.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao=<?=$i + 1;?>')" alt="Alterar Embalagem(ns)" title="Alterar Embalagem(ns)">
		</td>
		<td>
			<img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_pa_pi_emb'];?>', '1')">
		</td>
		<input type="hidden" name="id_pa_pi_emb[]" value="<?=$campos[$i]['id_pa_pi_emb'];?>">
	</tr>
<?
		}
	}
?>
</table>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhadestaque" align="left">
		<td colspan='4'>
			<a href="javascript:nova_janela('consultar_produto_insumo.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>', 'CONSULTAR', '', '', '', '', '300', '720', 'c', 'c', '', '', 's', 's', '', '', '')" title="Custo A&ccedil;o / Outros Metais">
				<font color="#FFFF00">
					<b><i>2&ordf; Etapa: Custo A&ccedil;o / Outros Metais</i></b>
				</font>
			</a>
		</td>
		<td colspan='3' align='right'>
			<font color="#00FF00"><b><i>Total s/ ICMS R$</i></b></font>
				<font color="#FFFFFF">
					<b><i><?=number_format($GLOBALS['etapa2_sem_impostos_sem_fator'], 2, ',', '.');?></i></b>
				</font>
			</font>
			<font color='black'>
                            <?=' | '.number_format($GLOBALS['etapa2_sem_impostos_sem_fator'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                        </font>
		</td>
	</tr>
<?
	if($id_produto_insumo != 0) if(estoque_insumo_zero($id_produto_insumo) < 2) $total_estoque_insumo_zero_2 ++;

	$sql = "Select pi.id_produto_insumo, pi.discriminacao, u.sigla 
			from produtos_insumos pi 
			inner join produtos_insumos_vs_acos pia on pia.id_produto_insumo = pi.id_produto_insumo 
			inner join unidades u on u.id_unidade = pi.id_unidade 
			where pi.ativo = 1 
			and pi.id_produto_insumo = '$id_produto_insumo' order by pi.discriminacao ";
	$campos = bancos::sql($sql);
	if(!empty($campos[0]['discriminacao'])) {
/*Coloquei esse nome para o hidden porque aki era um antigo combo, e também
por causa de outras variáveis q possuem esse nome e estão espalhadas nesse arquivo*/
		//Traz o preço custo e a densidade do produto insumo que está selecionado na combo
		$sql = "Select pia.densidade_aco 
				from produtos_insumos pi 
				inner join produtos_insumos_vs_acos pia on pia.id_produto_insumo = pi.id_produto_insumo 
				where pi.id_produto_insumo = ".$campos[0]['id_produto_insumo']." limit 1 ";
		$campos2 = bancos::sql($sql);
		if(count($campos2) == 1) {
			$dados_pi 	= custos::preco_custo_pi($campos[0]['id_produto_insumo']);
			$preco_pi 	= $dados_pi['preco_comum'];
			$icms 		= $dados_pi['icms'];
			//Significa que encontrou um produto com o Preço 0, sendo assim acrescenta na variável
			if($preco_pi == 0) $preco_custo_zero++;
			$densidade = $campos2[0]['densidade_aco'];
		}else {
			$densidade = '';
		}
		//Traz a quantidade em estoque do produto insumo
		$sql = "Select qtde as qtde_estoque 
				from estoques_insumos 
				where id_produto_insumo = ".$campos[0]['id_produto_insumo']." limit 1 ";
		$campos2 = bancos::sql($sql);
		if(count($campos2) == 1) {
			$qtde_estoque = number_format($campos2[0]['qtde_estoque'], 2, ',', '.');
			$qtde_estoque2 = number_format(($campos2[0]['qtde_estoque'] / $densidade), 2, ',', '.');
		}else {
			$qtde_estoque = '0,00';
			$qtde_estoque2 = '0,00';
		}
?>
	<tr class="linhanormaldestaque" align="center">
		<td>
			<b><i>Ref - Discriminação</i></b>
		</td>
		<td>
			<b><i>Estoque</i></b>
		</td>
		<td>
			<b><i>Peso / KG + 5%</i></b>
		</td>
		<td>
			<b><i>P.Unit.R$ - ICMS c/Red</i></b>
		</td>
		<td>
			<b><i>Tot.R$ s/ICMS</i></b>
		</td>
		<td>
			<img src="../../../../imagem/menu/alterar.png" border='0' onClick="html5Lightbox.showLightbox(7, 'alterar_etapa2.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')" alt="Alterar Custo A&ccedil;o / Outros Metais" title="Alterar Custo A&ccedil;o / Outros Metais">
		</td>
		<td>
			<img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item(0, '2')" alt="Excluir Custo A&ccedil;o / Outros Metais" title="Excluir Custo A&ccedil;o / Outros Metais"> 
		</td>
	</tr>
	<tr class="linhanormal" align="center">
		<td align="left">
		<?/*Coloquei esse nome para o hidden porque aki era um antigo combo, e também
			por causa de outras variáveis q possuem esse nome e estão espalhadas nesse arquivo*/
		?>
			<input type="hidden" name="hdd_produto_insumo" value="<?=$campos[0]['id_produto_insumo'];?>">
			<?=$campos[0]['sigla'].' - '.$campos[0]['discriminacao'];?>
			<a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[0]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Compras" class="link">
				<font color='red' style='cursor:help' title='Compra'><b>(Compras)</b></font>
			</a>
		</td>
		<td>
			<?=$qtde_estoque;?> kg / <?=$qtde_estoque2;?> m
		</td>
		<td>
		<?
			$comprimento_total = ($comprimento_a + $comprimento_b) / 1000;
			$peso_aco_kg = $densidade * $comprimento_total * 1.05;
			echo number_format($peso_aco_kg, 3, ',', '.');
		?>
		</td>
		<td>
			<?=number_format($preco_pi, 2, ',', '.').'<font color="red"><b> - '.number_format($icms, 2, ',', '.').' %</b></font>';?>
		</td>
		<td>
			<?=number_format($GLOBALS['etapa2_sem_impostos_sem_fator'], 2, ',', '.');?>
		</td>
		<td colspan="2">
			&nbsp;
		</td>
	</tr>
	<tr class="linhanormal" align="center">
		<td>
			<b><i>Qtde necessária p/ o Lote</i></b>
		</td>
		<td colspan='2'>
			<b><i>Comp. + Corte = Comp. Total</i></b>
		</td>
		<td>
			<b><i>Peças / Corte</i></b>
		</td>
		<td colspan="3">
			<b><i>Densidade Kg / M</i></b>
		</td>
	</tr>
	<tr class="linhanormal" align="center">
		<td>
		<?
			$lote_custo_calculo1 = $peso_aco_kg * $qtde_lote;
			$lote_custo_calculo2 = $lote_custo_calculo1 / $densidade;
			echo number_format($lote_custo_calculo1, 3, ',', '.').' Kg / '.number_format($lote_custo_calculo2, 3, ',', '.').' m';
		?>
		</td>
		<td colspan='2'>
			<?=$comprimento_a.' mm + '.$comprimento_b.' mm = '.number_format($comprimento_total, 3, ',', '.').' m';?>
		</td>
		<td>
			<?=$pecas_corte;?>
		</td>
		<td colspan="3">
			<?=number_format($densidade, 3, ',', '.');?>
		</td>
	</tr>
</table>
<?
	}
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhadestaque" align="left">
		<td colspan='4'>
			<a href="javascript:nova_janela('incluir_produto_insumo.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')" title="Atrelar Produto Insumo">
				<font color="#FFFF00">
					<b><i>3&ordf; Etapa: Atrelar Produto Insumo</i></b>
				</font>
			</a>
		</td>
		<td colspan='3' align='right'>
			<font color="#00FF00"><b><i>Total s/ ICMS R$</i></b></font>
				<font color="#FFFFFF">
					<b><i><?=number_format($GLOBALS['etapa3_sem_impostos_sem_fator'], 2, ',', '.');?></i></b>
				</font>
			</font>
			<font color='black'>
                            <?=' | '.number_format($GLOBALS['etapa3_sem_impostos_sem_fator'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                        </font>
		</td>
	</tr>
 <?
/*Aqui traz todos os produtos insumos que estão relacionado ao produto acabado
passado por parâmetro*/
	$sql = "Select pp.id_pac_pi, g.referencia, pi.id_produto_insumo, pi.discriminacao, pp.qtde, u.sigla 
			from pacs_vs_pis pp 
			inner join produtos_insumos pi on pi.id_produto_insumo = pp.id_produto_insumo 
			inner join grupos g on g.id_grupo = pi.id_grupo 
			inner join unidades u on u.id_unidade = pi.id_unidade 
			where pp.id_produto_acabado_custo = '$id_produto_acabado_custo' order by pp.id_pac_pi asc ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {
?>
	<tr class="linhanormaldestaque" align="center">
		<td>
			<b><i>Ref - Discriminação</i></b>
		</td>
		<td>
			<b><i>Estoque</i></b>
		</td>
		<td>
			<b><i>Qtd</i></b>
		</td>
		<td>
			<b><i>P.Unit.R$ - ICMS c/Red</i></b>
		</td>
		<td>
			<b><i>Tot.R$ s/ICMS</i></b>
		</td>
		<td>&nbsp; </td>
		<td>&nbsp; </td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
			if(estoque_insumo_zero($campos[$i]['id_produto_insumo']) < 2) {
				$total_estoque_insumo_zero_3 ++;
			}
?>
	<tr class="linhanormal" align="center">
		<td align="left">
			<?=$campos[$i]['sigla'].' - '.$campos[$i]['referencia'];?>
			-
			<?=$campos[$i]['discriminacao'];?>
			<a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Compras" class="link">
				<font color='red' style='cursor:help' title='Compra'><b>(Compras)</b></font>
			</a>
		</td>
<?
//Traz a quantidade em estoque do produto insumo que está selecionado na combo
			$sql = "Select qtde as qtde_estoque 
					from estoques_insumos 
					where id_produto_insumo = ".$campos[$i]['id_produto_insumo']." limit 1 ";
			$campos2 = bancos::sql($sql);
			if(count($campos2) == 1) {
				$qtde_estoque = number_format($campos2[0]['qtde_estoque'], 2, ',', '.');
			}else {
				$qtde_estoque = '0,00';
			}
?>
		<td>
			<?=$qtde_estoque;?>
		</td>
		<td>
			<?=number_format($campos[$i]['qtde'], 1, ',', '.');?>
		</td>
		<td>
		<?
			$dados_pi 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
			$preco_pi 	= $dados_pi['preco_comum'];
			$icms 		= $dados_pi['icms'];
			if($preco_pi == 0) $preco_custo_zero++;
			echo number_format($preco_pi, 2, ',', '.').'<font color="red"><b> - '.number_format($icms, 2, ',', '.').' %</b></font>';
		?>
		</td>
		<td>
			<?=number_format($campos[$i]['qtde'] * $preco_pi * (100 - $icms) / 100, 2, ',', '.');?>
		</td>
		<td>
			<img src="../../../../imagem/menu/alterar.png" border='0' onClick="javascript:nova_janela('alterar_etapa3.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao=<?=$i + 1;?>', 'CONSULTAR', '', '', '', '', '450', '900', 'c', 'c', '', '', 's', 's', '', '', '')" alt="Alterar Produto Insumo" title="Alterar Produto Insumo">
		</td>
		<td>
			<img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_pac_pi'];?>', '3')">
		</td>
		<input type="hidden" name="id_pac_pi[]" value="<?=$campos[$i]['id_pac_pi'];?>">
	</tr>
<?
		}
	}
?>
</table>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhadestaque" align="left">
		<td colspan='3'>
			<a href="javascript:nova_janela('incluir_maquina.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')" title="Atrelar Máquina">
				<font color="#FFFF00">
					<b><i>4&ordf; Etapa: Atrelar Custo M&aacute;quina</i></b>
				</font>
			</a>
		</td>
		<td colspan='3' align='right'>
			<font color="#00FF00"><b><i>Total s/ ICMS R$</i></b></font>
				<font color="#FFFFFF">
					<b><i><?=number_format($GLOBALS['etapa4'], 2, ',', '.');?></i></b>
				</font>
			</font>
			<font color='black'>
                            <?=' | '.number_format($GLOBALS['etapa4'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                        </font>
		</td>
	</tr>
<?
/*Aqui traz todas as máquinas que estão relacionadas ao produto acabado
passado por parâmetro*/
	$sql = "SELECT pm.id_pac_maquina, m.id_maquina, m.nome, m.custo_h_maquina, pm.tempo_hs 
			FROM `pacs_vs_maquinas` pm 
			INNER JOIN `maquinas` m ON m.id_maquina = pm.id_maquina 
			WHERE pm.id_produto_acabado_custo = '$id_produto_acabado_custo' ORDER BY pm.id_pac_maquina ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {
?>
	<tr class="linhanormaldestaque" align="center">
		<td>
			<b><i>Nome da Máquina</i></b>
		</td>
		<td>
			<b><i>Tempo (Hs)</i></b>
		</td>
		<td>
			<b><i>R$ / h</i></b>
		</td>
		<td>
			<b><i>Total R$ </i></b>
		</td>
		<td>&nbsp; </td>
		<td>&nbsp; </td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" align='center'>
            <td align='left'>
            <?
                echo $campos[$i]['nome'];
                //Aqui tem esse tratamento, para não dar erro de divisão por zero nas etapas 4 e 5
                if($qtde_lote == 0) $qtde_lote = 1;
                $total_rs   = ($campos[$i]['tempo_hs'] * $campos[$i]['custo_h_maquina'] * $fator_custo_4) / $qtde_lote;

                //Só irá mostrar essa Conta quando a Máquina for "Tx Financ Estocagem" ...
                if($campos[$i]['id_maquina'] == 40) {
                    $preco_maximo_custo_fat_rs = custos::preco_custo_pa($id_produto_acabado);
                    echo '&nbsp;&nbsp;<font color="red"><b>('.number_format($total_rs / ($preco_maximo_custo_fat_rs - $total_rs) * 100, 1, ',', '.').'%)</b></font>';
                }
            ?>
            </td>
            <td>
                <?=number_format($campos[$i]['tempo_hs'], 1, ',', '.');?>
            </td>
            <td>
                <?=number_format($campos[$i]['custo_h_maquina'], 2, ',', '.');?>
            </td>
            <td>
                <?=number_format($total_rs, 2, ',', '.');?>
            </td>
            <td>
                <img src="../../../../imagem/menu/alterar.png" border='0' onclick="nova_janela('alterar_etapa4.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao=<?=$i + 1;?>', 'CONSULTAR', '', '', '', '', '450', '900', 'c', 'c', '', '', 's', 's', '', '', '')" alt="Alterar Custo M&aacute;quina" title="Alterar Custo M&aacute;quina">
            </td>
            <td>
                <img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_pac_maquina'];?>', '4')">
            </td>
            <input type="hidden" name="id_pac_maquina[]" value="<?=$campos[$i]['id_pac_maquina'];?>">
	</tr>
<?
		}
	}
?>
</table>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhadestaque" align="left">
		<td colspan='4'>
			<a href="javascript:nova_janela('incluir_tratamento_termico.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')" title="Atrelar Tratamento Térmico">
				<font color="#FFFF00">
					<b><i>5&ordf; Etapa: Atrelar Custo de Trat. T&eacute;rmico / Galvanoplastia</i></b>
				</font>
			</a>
		</td>
		<td colspan='3' align='right'>
			<font color="#00FF00"><b><i>Total s/ ICMS R$</i></b></font>
				<font color="#FFFFFF">
					<b><i><?=number_format($GLOBALS['etapa5_sem_impostos_sem_fator'], 2, ',', '.');?></i></b>
				</font>
			</font>
			<font color='black'>
                            <?=' | '.number_format($GLOBALS['etapa5_sem_impostos_sem_fator'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                        </font>
		</td>
	</tr>
<?
/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
	$sql = "SELECT ppt.id_pac_pi_trat, u.sigla, pi.id_produto_insumo, pi.discriminacao, ppt.fator AS fator_tt, ppt.peso_aco, ppt.peso_aco_manual, ppt.lote_minimo_fornecedor 
			FROM `pacs_vs_pis_trat` ppt 
			INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ppt.id_produto_insumo 
			INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
			WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY ppt.id_pac_pi_trat ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {
?>
	<tr class="linhanormaldestaque" align="center">
		<td>
			<b><i>Ref. Trat - Discriminação</i></b>
		</td>
		<td>
			<b><i>Fator T.T.</i></b>
		</td>
		<td>
			<b><i>P.Unit.R$ - ICMS c/Red</i></b>
		</td>
		<td>
			<b><i>Peso p/ T.T.</i></b>
		</td>
		<td>
			<b><i>Tot.R$ s/ICMS</i></b>
		</td>
		<td>&nbsp; </td>
		<td>&nbsp; </td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" align="center">
		<td height="20" align="left">
			<?=$campos[$i]['sigla'];?>
			-
			<?=$campos[$i]['discriminacao'];?>
		</td>
		<td height="20">
			<?=number_format($campos[$i]['fator_tt'], 2, ',', '.');?>
		</td>
		<td>
		<?
			$dados_pi 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
			$preco_pi 	= $dados_pi['preco_comum'];
			$icms 		= $dados_pi['icms'];
			echo number_format($preco_pi, 2, ',', '.').'<font color="red"><b> - '.number_format($icms, 2, ',', '.').' %</b></font>';
		?>
		</td>
		<td>
		<?
//Peso Aço Manual está checado
			if($campos[$i]['peso_aco_manual'] == 1) {
				echo number_format($campos[$i]['peso_aco'], 3, ',', '.');
			}else {
				echo number_format($campos[$i]['peso_aco'] * $campos[$i]['fator_tt'], 3, ',', '.');
			}
//Peso Aço Manual está checado
			if($campos[$i]['peso_aco_manual'] == 1) echo ' <font color="green"><b>REAL</b></font>';
		?>
		</td>
		<td>
		<?
			//Ignora a multiplicação pelo fator_tt
			if($campos[$i]['peso_aco_manual'] == 1) {//Marcado no Checkbox que estamos usando o Peso Real da Peça ...
				$peso_aco_kg_temp 		= round($campos[$i]['peso_aco'], 3);
				$custo_normal_etapa5	= ($peso_aco_kg_temp * $preco_pi);//O ICMS é abatido + abaixo ...
			}else {//Desmarcado no Checkbox, ou seja é um peso Teórico da Peça baseado na Qtde de Aço utilizado * Fator TT ...		
				$peso_aco_kg_temp 		= round($peso_aco_kg / 1.05, 3);		
				$custo_normal_etapa5	= ($peso_aco_kg_temp * $preco_pi * $campos[$i]['fator_tt']);//O ICMS é abatido + abaixo ...
			}		
			if($campos[$i]['lote_minimo_fornecedor'] == 1) {//Se estiver setado ou 1 acionar o calculo abaixo de lote minimo por fornecedor default por pedido ...
				$id_fornecedor_default 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo'], 0, 1);
                            	//Busco na Lista de Preços o Lote Mínimo em R$ do Fornecedor e do PI na Lista de Preços ...
				$sql = "SELECT lote_minimo_reais 
				    	FROM `fornecedores_x_prod_insumos` 
				    	WHERE `id_fornecedor` = '$id_fornecedor_default' 
				    	AND `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
				$campos_lista		= bancos::sql($sql);
				$lote_minimo_reais	= $campos_lista[0]['lote_minimo_reais'];//lote minimo do fornecedor default
				$preco_peca_corte	= $lote_minimo_reais / $qtde_lote;
				//Se qtde_orcamento	= 0, usamos a Qtde do Lote do Custo senão a Qtde do Lote do Orçamento ...
				$lote_custo		= ($qtde_orcamento == 0) ? $qtde_lote : $qtde_orcamento;
				$custo_lote_minimo_etapa5 	= $lote_minimo_custo_tt / $lote_custo;
				if($custo_normal_etapa5 < $custo_lote_minimo_etapa5) $custo_normal_etapa5 = $custo_lote_minimo_etapa5;
			}
			echo number_format($custo_normal_etapa5 * (100 - $icms) / 100, 2, ',', '.');
			//Também se tiver marcada a opção de lote mínimo, eu mostro essa Mensagem ...
			if($campos[$i]['lote_minimo_fornecedor'] == 1) echo ' <font color="red" title="Cálculo por Lote Mínimo" style="cursor:help"><b>LTM</b></font>';
		?>
		</td>
		<td>
			<img src="../../../../imagem/menu/alterar.png" border='0' onClick="javascript:nova_janela('alterar_etapa5.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao=<?=$i + 1;?>', 'CONSULTAR', '', '', '', '', '450', '900', 'c', 'c', '', '', 's', 's', '', '', '')" alt="Alterar Custo de Trat. T&eacute;rmico / Galvanoplastia" title="Alterar Custo de Trat. T&eacute;rmico / Galvanoplastia">
		</td>
		<td>
			<img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_pac_pi_trat'];?>', '5')">
		</td>
		<input type="hidden" name="id_pac_pi_trat[]" value="<?=$campos[$i]['id_pac_pi_trat'];?>">
	</tr>
<?
		}
	}
?>
</table>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhadestaque" align="left">
		<td colspan='3'>
			<a href="javascript:nova_janela('incluir_usinagem.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')" title="Atrelar Usinagem">
				<font color="#FFFF00">
					<b><i>6&ordf; Etapa: Atrelar Custo de Usinagem Externo</i></b>
				</font>
			</a>
		</td>
		<td colspan='3' align='right'>
			<font color="#00FF00"><b><i>Total s/ ICMS R$</i></b></font>
				<font color="#FFFFFF">
					<b><i><?=number_format($GLOBALS['etapa6_sem_impostos_sem_fator'], 2, ',', '.');?></i></b>
				</font>
			</font>
			<font color='black'>
                            <?=' | '.number_format($GLOBALS['etapa6_sem_impostos_sem_fator'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                        </font>
		</td>
	</tr>
<?
/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
	$sql = "Select ppu.id_pac_pi_usi, ppu.qtde, u.sigla, pi.id_produto_insumo, pi.discriminacao 
			from pacs_vs_pis_usis ppu 
			inner join produtos_insumos pi on pi.id_produto_insumo = ppu.id_produto_insumo 
			inner join unidades u on u.id_unidade = pi.id_unidade 
			where ppu.id_produto_acabado_custo = '$id_produto_acabado_custo' order by ppu.id_pac_pi_usi asc ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {
?>
	<tr class="linhanormaldestaque" align="center">
		<td>
			<b><i>Ref. Usi - Discriminação</i></b>
		</td>
		<td>
			<b><i>Qtd</i></b>
		</td>
		<td>
			<b><i>P.Unit.R$ - ICMS c/Red</i></b>
		</td>
		<td>
			<b><i>Tot.R$ s/ICMS</i></b>
		</td>
		<td>&nbsp; </td>
		<td>&nbsp; </td>
	</tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" align="center">
		<td height="20" align="left">
			<?=$campos[$i]['sigla'];?>
			-
			<?=$campos[$i]['discriminacao'];?>
			<a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Compras" class="link">
				<font color='red' style='cursor:help' title='Compra'><b>(Compras)</b></font>
			</a>
		</td>
		<td>
			<?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
		</td>
		<td>
		<?
			$dados_pi 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
			$preco_pi 	= $dados_pi['preco_comum'];
			$icms 		= $dados_pi['icms'];
			echo number_format($preco_pi, 2, ',', '.').'<font color="red"><b> - '.number_format($icms, 2, ',', '.').' %</b></font>';
		?>
		</td>
		<td>
			<?=number_format($campos[$i]['qtde'] * $preco_pi * (100 - $icms) / 100, 2, ',', '.');?>
		</td>
		<td>
			<img src="../../../../imagem/menu/alterar.png" border='0' onClick="javascript:nova_janela('alterar_etapa6.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao=<?=$i + 1;?>', 'CONSULTAR', '', '', '', '', '450', '900', 'c', 'c', '', '', 's', 's', '', '', '')" alt="Atrelar Produto Acabado / Componente" title="Atrelar Produto Acabado / Componente">
		</td>
		<td>
			<img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_pac_pi_usi'];?>', '6')">
		</td>
		<input type="hidden" name="id_pac_pi_usi[]" value="<?=$campos[$i]['id_pac_pi_usi'];?>">
	</tr>
<?
		}
	}
?>
</table>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr class="linhadestaque" align="left">
		<td colspan='4'>
			<a href="javascript:nova_janela('incluir_produto_acabado.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')" title="Atrelar Máquina">
				<font color="#FFFF00">
					<b><i>7&ordf; Etapa: Atrelar Produto Acabado / Componente</i></b>
				</font>
			</a>
		</td>
		<td colspan='4' align='right'>
			<font color="#00FF00"><b><i>Total s/ ICMS R$</i></b></font>
				<font color="#FFFFFF">
					<b><i><?=number_format($GLOBALS['etapa7_sem_impostos_sem_fator'], 2, ',', '.');?></i></b>
				</font>
			</font>
			<font color='black'>
                            <?=' | '.number_format($GLOBALS['etapa7_sem_impostos_sem_fator'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                        </font>
		</td>
	</tr>
<?
/*Aqui traz todos produtos acabados componentes que estão relacionadas ao produto acabado
passado por parâmetro*/
	$sql = "SELECT pa.referencia, pa.id_produto_acabado, pa.discriminacao, pa.operacao_custo, pa.operacao_custo_sub, pa.preco_unitario, pa.status_custo, pp.id_pac_pa, pp.qtde, u.sigla 
			FROM `pacs_vs_pas` pp 
			INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pp.id_produto_acabado 
			INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
			WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.id_pac_pa ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {
?>
	<tr class="linhanormaldestaque" align="center">
		<td>
			<b><i>Ref. PA - Discriminação</i></b>
		</td>
		<td>
			<font title="Operação de Custo">
				<b><i>O.C.</i></b>
			</font>
		</td>
		<td>
			<font title="Estoque Real">
				<b><i>Est Real</i></b>
			</font>
		</td>
		<td>
			<b><i>Qtd</i></b>
		</td>
		<td>
			<b><i>P. Unit. R$ <br>(s/ICMS c/Red + s/Emb)</i></b>
		</td>
		<td>
			<b><i>Tot.R$ s/ICMS</i></b>
		</td>
		<td>&nbsp; </td>
		<td>&nbsp; </td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
			$id_produto_acabado_loop = $campos[$i]['id_produto_acabado'];
			//Aqui é para a 7 etapa
			//Só entra aqui caso seja o produto do tipo Especial e  do Tipo Industrializado
			if($campos[$i]['referencia'] == 'ESP' && $campos[$i]['operacao_custo'] == 0) {
				$sql = "SELECT id_produto_insumo 
						FROM `produtos_acabados_custos` 
						WHERE `id_produto_acabado` = '$id_produto_acabado_loop' 
						AND `operacao_custo` = '0' LIMIT 1 ";
				$campos_produto_insumo = bancos::sql($sql);
				if(estoque_insumo_zero($campos_produto_insumo[0]['id_produto_insumo']) < 2) $total_estoque_insumo_zero_7 ++;
			}
?>
	<tr class="linhanormal" align="center">
		<td align="left">
		<?
/********************************************************************/
//Verificação p/ ver qual caminho caminho que o link deverá seguir ...
				if($campos[$i]['operacao_custo'] == 0) {//Industrial
					$url_custo = "../../../producao/custo/prod_acabado_componente/prod_acabado_componente2.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&tela=2&ignorar_sessao=1";
					$url_prazo_entrega = "../../../classes/estoque/alterar_prazo_entrega_industrial.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&operacao_custo=".$operacao_custo;
				}else {//Revenda
					$url_custo = "../../../producao/custo/pa_componente_revenda/custo_revenda.php?id_produto_acabado=".$campos[$i]['id_produto_acabado'];
					$url_prazo_entrega = "../../../classes/estoque/alterar_prazo_entrega_revenda.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&operacao_custo=".$operacao_custo;
				}
		?>
			<a href="javascript:nova_janela('<?=$url_custo;?>', 'DETALHES_CUSTO', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo" class="link">
		<?
/********************************************************************/
				$title = ($campos[$i]['status_custo'] == 1) ? 'Custo Liberado' : 'Custo não Liberado';
				$color = ($campos[$i]['status_custo'] == 1) ? '' : 'red';
		?>
				<font title="<?=$title;?>" color="<?=$color;?>">
					<?=$campos[$i]['sigla'].' - '.$campos[$i]['referencia'].' - '.$campos[$i]['discriminacao'];?>
				</font>
			</a>
			&nbsp;
			<a href="javascript:nova_janela('<?=$url_prazo_entrega;?>', 'PRAZO_ENTREGA', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Alterar Prazo de Entrega" class="link">
				<font color='red' style='cursor:help' title='Alterar Prazo Entrega'><b>(Prazo de Entrega)</b></font>
			</a>
		</td>
		<td>
		<?
			if($campos[$i]['operacao_custo'] == 0) {
				echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
				if($campos[$i]['operacao_custo_sub'] == 0) {
					echo '-I';
				}else if($campos[$i]['operacao_custo_sub'] == 1) {
					echo '-R';
				}else {
					echo '-';
				}
			}else if($campos[$i]['operacao_custo'] == 1) {
				echo 'R';
			}else {
				echo '-';
			}
		?>
		</td>
<?
//Traz a quantidade em estoque do produto acabado
		$estoque_produto = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], '1');
		$estoque_real = number_format($estoque_produto[0], 2, ',', '.');
?>
		<td>
			<?=$estoque_real;?>
		</td>
		<td>
			<?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
		</td>
		<td>
		<?
			if($campos[$i]['operacao_custo'] == 0) {//Industrialização
				$preco_custo 		= custos::todas_etapas($campos[$i]['id_produto_acabado'], 0, 0);	
				
				
				/*$dados_pa 			= intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado']);
				$icms 				= $dados_pa['icms'];
				$reducao			= $dados_pa['reducao'];
				$icms_com_red 		= $icms * (1 - $reducao / 100);*/
			}else {			
				$fator_margem_lucro = genericas::variavel(22);//margem de Lucro PA Industrial
				$preco_custo 		= custos::pipa_revenda($campos[$i]['id_produto_acabado'], 0) / (genericas::variaveis('taxa_financeira_vendas') / 100 + 1);
				$preco_custo 		= ($preco_custo / $GLOBALS['fator_margem_lucro_pa']) * $fator_margem_lucro;//Este calculo é pq tem um PA(Rev) atrelado na 7ª etapa de um PA(Ind)
				
				/*Como esse PA é de Revenda, com certeza, ele é um PI e sendo assim eu busco qual é o seu correspondente ...
				$sql = "SELECT id_produto_insumo 
                                        FROM `produtos_acabados` 
                                        WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
				$campos_pi 			= bancos::sql($sql);
				$id_fornecedor 		= custos::preco_custo_pi($campos_pi[0]['id_produto_insumo'], 0, 1);
				//Aqui eu busco o ICMS da própria lista do Fornecedor Default desse PI ...
				$sql = "SELECT `icms` 
						FROM `fornecedores_x_prod_insumos` 
						WHERE `id_fornecedor` = '$id_fornecedor' 
						AND `id_produto_insumo` = '".$campos_pi[0]['id_produto_insumo']."' LIMIT 1 ";
				$campos_lista 		= bancos::sql($sql);
				$icms_com_red 		= $campos_lista[0]['icms'];//O próprio ICMS já é abastecido com a Red pelo pessoal de Compras ...*/
			}
			echo number_format($preco_custo, 2, ',', '.');
		?>
		</td>
		<td width="106">
		<?
			//Se a Operação de Custo for Industrial, retiro os Impostos Federais também ...
			if($campos[$i]['operacao_custo'] == 0) {//Industrial ...
				$total = ($campos[$i]['qtde'] * $preco_custo);
			}else {//Revenda ...
				$total = $campos[$i]['qtde'] * $preco_custo * (1 - $icms_com_red / 100);
			}
			echo number_format($total, 2, ',', '.');
		?>
		</td>
		<td width="16">
			<img src="../../../../imagem/menu/alterar.png" border='0' onClick="javascript:nova_janela('alterar_etapa7.php?tela=<?=$tela;?>&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao=<?=$i + 1;?>', 'CONSULTAR', '', '', '', '', '450', '900', 'c', 'c', '', '', 's', 's', '', '', '')" alt="Alterar Custo de Usinagem Externo" title="Alterar Custo de Usinagem Externo">
		</td>
		<td width="16">
			<img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_pac_pa'];?>', '7')">
		</td>
		<input type="hidden" name="id_pac_pa[]" value="<?=$campos[$i]['id_pac_pa'];?>">
	</tr>
<?
		}
	}
?>
	<tr class='linhanormal' align='center'>
		<td colspan='9'>
			<?$checked_lote_minimo = ($lote_minimo_ignora_faixa_orcavel == 'S') ? 'checked' : '';?>
			<input type="checkbox" name="chkt_lote_minimo" value="S" id="lote_minimo" title="Lote Mínimo" onclick="confirmar_lote_minimo()" <?=$checked_lote_minimo;?> class="checkbox">
			<label for="lote_minimo">
				<b>LOTE M&Iacute;NIMO (IGNORA A FAIXA OR&Ccedil;&Aacute;VEL)</b>
			</label>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="9">
		<?
//Esse parâmetro é porque essa tela também é puxada de lá da tela de Orçamentos, e daí tem conflito de sessão
			if(empty($ignorar_sessao)) {
/*Exibi o botão voltar, aqui tem esse controle porque essa tela aparece também
em outro lugar*/
				if($tela == 1) {//Volta p/ a tela de Todos os P.A.
					$url = 'pa_componente_todos.php';
				}else if($tela == 2) {//Volta p/ a tela dos P.A. do Tipo Esp.
					$url = 'pa_componente_esp.php';
				}
		?>
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onClick="window.location = '<?=$url.$parametro_velho;?>'" class="botao">
			<input type="button" name="cmd_clonar" value="Clonar Custo" title="Clonar Custo" style="color:black" onClick="javascript:nova_janela('clonagem_custo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&tela=<?=$tela;?>', 'POP', '', '', '', '', 500, 750, 'c', 'c', '', '', 's', 's', '', '', '')" class="botao">
			<input type="button" name="cmd_incluir_pa_orc" value="Incluir PA no ORC" title="Incluir PA no Orçamento" style="color:black" onClick="javascript:nova_janela('incluir_produto_acabado_orcamento.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'POP', '', '', '', '', 500, 750, 'c', 'c', '', '', 's', 's', '', '', '')" class="botao">
		<?
			}else {
		?>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onClick="window.close()" style="color:red" class="botao">
		<?
			}
//Aparecerá somente na tela de P.A. Revenda
			if(empty($tela)) {
		?>
			<input type="button" name="cmd_fechar" value="Fechar e Atualizar" title="Fechar e Atualizar" onClick="atualizar_abaixo()" style="color:red" class="botao">
		<?
			}
		?>
		</td>
	</tr>
</table>
<input type="hidden" name="id_produto_acabado_custo" value="<?=$id_produto_acabado_custo;?>">
<input type="hidden" name="id_produto_acabado" value="<?=$id_produto_acabado;?>">
<input type="hidden" name="tela" value="<?=$tela;?>">
<input type="hidden" name="desvio">
</form>
</body>
</html>
<?}

//Se a Operação de Custo do PA = 'Revenda', então tem que fazer esse Tratamento p/ Margem de Lucro
if($operacao_custo_rotulo == 'Revenda') {
/***************************Tratamento de Margem de Lucro**************************/
//Busca do $id_fornecedor_prod_insumo
	$sql = "SELECT id_produto_insumo 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
	$campos2            = bancos::sql($sql);
//Em primeiro verifico quem é o id_produto_insumo
	$id_produto_insumo  = $campos2[0]['id_produto_insumo'];
//Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado
	$id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, "", "", 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
//Em terceiro qual é o id_fornecedor_prod_insumo, agora já tenho $id_produto_insumo e o $id_fornecedor_setado
	$sql = "Select id_fornecedor_prod_insumo 
			from fornecedores_x_prod_insumos 
			where id_produto_insumo = '$id_produto_insumo' 
			and id_fornecedor = '$id_fornecedor_setado' limit 1 ";
	$campos2 = bancos::sql($sql);
	$id_fornecedor_prod_insumo = $campos2[0]['id_fornecedor_prod_insumo'];
	
/***************************Tratamento de Margem de Lucro**************************/
	$sql = "Select fator_margem_lucro_pa 
			from fornecedores_x_prod_insumos 
			where id_fornecedor_prod_insumo = '$id_fornecedor_prod_insumo' limit 1 ";
	$campos2 = bancos::sql($sql);
//Agora se a M.L. for = 0, eu também não posso liberar o Custo do PA
	$fator_margem_lucro_pa = ($campos2[0]['fator_margem_lucro_pa'] == '0.00') ? 0 : 1;
//Se a Operação de Custo do PA = 'Industrial', então não preciso fazer esse Tratamento p/ Margem de Lucro
}else {
	$fator_margem_lucro_pa = 1;
}
/******************************************************************************************/

//Controle que serve para essa função de JavaScript -> confirmar_liberacao(), mais abaixo
if($referencia == 'ESP') {
/*Listagem de Todos os Orçamento(s) que estão em Aberto, q não estão congelados, que contém esse Item
em que o prazo de Entrega seja igual a Imediato*/
	$sql = "Select ovi.id_orcamento_venda_item 
			from orcamentos_vendas_itens ovi 
			inner join orcamentos_vendas ov on ov.id_orcamento_venda = ovi.id_orcamento_venda and ov.congelar = 'N' 
			where ovi.id_produto_acabado = '$id_produto_acabado' 
			and ovi.prazo_entrega_tecnico = '0.0' limit 1 ";
	$campos2 = bancos::sql($sql);
//Se encontrar algum item que tenha o prazo de entrega técnico como zerado, então não pode liberar o custo
	if(count($campos2) == 1) {
		$custo_nao_pode_liberar = 1;
	}else {
		$custo_nao_pode_liberar = 0;
	}
}else {//Se for Industrial
	$custo_nao_pode_liberar = 0;
}
?>
<pre>
<b><font color="blue">Variáveis:</font></b>
<pre>
<b><font color="green">* Taxa Financeira de Vendas: </font><?=number_format((($taxa_financeira_vendas-1)*100), 2, ',', '.');?> %</b><br>
</pre>
<!--Joguei essa function aki em baixo, devido a variável de preço $preco_custo_zero em PHP, que
foi sendo tratada no meio das etapas 2 e 3 e da variável $custo_nao_pode_liberar-->
<Script Language = 'JavaScript'>
function confirmar_liberacao() {
/*Essa variável é iniciada com o valor 0 no início do código, caso esta seje > do q 0, então
significa que foi encontrado algum preço de pi_insumo como sendo 0.00, sendo assim não pode
ser liberado o custo*/
	var preco_custo_zero = '<?=$preco_custo_zero;?>'
	var status_custo = '<?=$status_custo;?>'
//Não encontrou nenhum pi com o valor de pi como sendo 0.00
	if(preco_custo_zero == 0) {
		if(document.form.chkt_custo_liberado.checked == true) {//Vai liberar o custo
/***************************Tratamento de Margem de Lucro**************************/
//Agora se a M.L. for = 0, eu também não posso liberar o Custo do PA
			var fator_margem_lucro_pa = eval('<?=$fator_margem_lucro_pa;?>')
			if(fator_margem_lucro_pa == 0) {
				alert('ESSE CUSTO NÃO PODE SER LIBERADO !\nO FATOR MARGEM DE LUCRO DESSA P.A. É = 0,00 !')
				document.form.chkt_custo_liberado.checked = false
				return false
			}
/**********************************************************************************/
/*Na hora de liberar o custo, o Sistema verifica se o Depto. Técnico já deu o prazo para este Item
do Orçamento do PA atrelado, se isso ainda não aconteceu, não posso liberar o custo*/
			var custo_nao_pode_liberar = eval('<?=$custo_nao_pode_liberar;?>')
			if(custo_nao_pode_liberar == 1) {
				alert('ESSE CUSTO NÃO PODE SER LIBERADO, PREENCHA O PRAZO DE ENTREGA DO P.A. !')
				document.form.chkt_custo_liberado.checked = false
				showHide('alterar_prazo_entrega_tecnico')
				return false
			}
/**********************************************************************************/
//Se a Qtde do Lote for = 0, eu também não posso liberar o Custo do PA
			var qtde_lote			= eval('<?=$qtde_lote_alert;?>')
			if(qtde_lote == 0) {
				alert('ESSE CUSTO NÃO PODE SER LIBERADO !\nA QUANTIDADE DO LOTE DESSE CUSTO É = 0,00 !')
				document.form.chkt_custo_liberado.checked = false
				return false
			}
			var mensagem = confirm('DESEJA LIBERAR O CUSTO ?')
			if(mensagem == false) {
				document.form.chkt_custo_liberado.checked = false
				return false
			}else {
				document.form.desvio.value = 1
				document.form.submit()
			}
		}else {//Vai bloquear o custo
			var mensagem = confirm('DESEJA BLOQUEAR O CUSTO ?')
			if(mensagem == false) {
				document.form.chkt_custo_liberado.checked = true
				return false
			}else {
				document.form.desvio.value = 1
				document.form.submit()
			}
		}
	}else {
/*Aqui verifica se já trouxe do BD o custo como liberado ou bloqueado, então se eu quiser bloquear 
o custo, posso fazer normalmente, o que eu não posso fazer é o inverso*/
		if(status_custo == 1) {//Custo já estava liberado
			document.form.desvio.value = 1
			document.form.submit()
		}else {//Custo ainda está para ser liberado
			alert('ESSE CUSTO NÃO PODE SER LIBERADO !\n EXISTE(M) ITEM(NS) CUJO O PREÇO DE CUSTO NAS ETAPAS 2 OU 3 É DE VALOR R$ 0,00 !')
			document.form.chkt_custo_liberado.checked = false
			return false
		}
	}
}

/*Joguei essa function aki em baixo, devido a variável de preço $estoque_insumo_zero_2, 3 e 7
 em PHP, que foi sendo tratada no meio das etapas 2,3 e 7*/
 function verificar_estoque_insumo_zero() {
 /*Mudei a lógica temporariamente - Dárcio
 	var tela = '<?=$tela;?>'
	if(tela == 2) {
*/
//Essa function só serve para produtos do Tipo Especial
	var referencia = '<?=$referencia;?>'
	if(referencia == 'ESP') {
		var total_estoque_insumo_zero_2 = '<?=$total_estoque_insumo_zero_2;?>'
		//var total_estoque_insumo_zero_3 = eval('<?=$total_estoque_insumo_zero_3;?>')
		var total_estoque_insumo_zero_7 = '<?=$total_estoque_insumo_zero_7;?>'
//Não achou
		if(total_estoque_insumo_zero_2 == '') {
			total_estoque_insumo_zero_2 = 0
		}
		if(total_estoque_insumo_zero_7 == '') {
			total_estoque_insumo_zero_7 = 0
		}
//Comparação - Aqui significa que foi encontrado um produto pelo menos q tem o estoque menor q 2
		if(total_estoque_insumo_zero_2 > 0) {
			alert('ATENÇÃO !!!\n SEU PRODUTO INSUMO NÃO POSSUE ESTOQUE SUFICIENTE NA 2º ETAPA !')
		}
	
		if(total_estoque_insumo_zero_7 > 0) {
			alert('ATENÇÃO !!!\n SEU PRODUTO INSUMO NÃO POSSUE ESTOQUE SUFICIENTE NA 7º ETAPA !')
		}
	}
}

//Aqui chama a função
verificar_estoque_insumo_zero()
</Script>
