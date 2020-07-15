<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/lista_preco/lista_preco.php', '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>LISTA DE PREÇO NACIONAL ATUALIZADA COM SUCESSO.</font>";

$fator_margem_lucro = genericas::variavel(22);//margem de Lucro PA
$fator_desc_max_vendas = genericas::variavel(19);//Fator Desc Máx. de Vendas

if($passo == 1) {
    if(!empty($chkt_preco_promocional))         $condicao = " AND pa.preco_promocional_simulativa <> 0 ";
    if(!empty($chkt_todos_produtos_zerados))    $condicao_produtos_zerados = "and pa.preco_unitario=0.00 ";

    if(empty($cmb_empresa_divisao)) $cmb_empresa_divisao = '%';
    if(empty($cmb_grupo_pa))        $cmb_grupo_pa = '%';
    if(empty($cmb_familia))         $cmb_familia = '%';
    if(empty($cmb_order_by))        $cmb_order_by = 'pa.referencia';
	
//Aqui traz todos os grupos com exceção dos que são pertencentes a Família de Componentes
    $sql = "SELECT pa.id_produto_acabado, pa.operacao_custo, pa.referencia, pa.discriminacao, pa.preco_unitario, pa.qtde_promocional_simulativa, pa.preco_promocional_simulativa, pa.qtde_promocional_simulativa_b, pa.preco_promocional_simulativa_b, pa.preco_unitario_simulativa, pa.preco_promocional_simulativa, pa.mmv, pa.status_custo, ed.razaosocial, gpa.nome, ged.* 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao AND ed.id_empresa_divisao LIKE '$cmb_empresa_divisao' 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_grupo_pa LIKE '$cmb_grupo_pa' AND gpa.id_familia LIKE '$cmb_familia' AND gpa.id_familia <> '23' 
            WHERE pa.`referencia` LIKE '%$txt_referencia%' 
            AND pa.discriminacao LIKE '%$txt_discriminacao%' 
            AND pa.referencia <> 'ESP' 
            AND pa.status_nao_produzir = '0' 
            AND pa.ativo = '1' 
            $condicao $condicao_produtos_zerados ORDER BY $cmb_order_by ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'lista_preco_promocional.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Nova Lista de Preço Promocional ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Essa função calcula o Preço do PA. quando estou digitando a Margem de Lucro ...
function calcular_preco(indice) {
	var elementos = document.form.elements
	var objetos_linha = 16
	if(indice == 0) {
		posicao = 0
	}else if(indice == 1) {
		posicao = objetos_linha
	}else if(indice > 1) {
		posicao = eval(indice) * objetos_linha
	}
	var preco_liq_fat_rs = eval(strtofloat(elementos[posicao + 12].value))

//Cálculo do Preço A
	fator_a = (elementos[posicao + 1].value == '') ? 0 : eval(strtofloat(elementos[posicao + 1].value))
	elementos[posicao + 2].value = fator_a * preco_liq_fat_rs
	elementos[posicao + 2].value = arred(elementos[posicao + 2].value, 2, 1)
	
//Cálculo do Preço B
	fator_b = (elementos[posicao + 5].value == '') ? 0 : eval(strtofloat(elementos[posicao + 5].value))
	elementos[posicao + 6].value = fator_b * preco_liq_fat_rs
	elementos[posicao + 6].value = arred(elementos[posicao + 6].value, 2, 1)
	
//Cálcula a Margem de Lucro ...
	calcular_margem_lucro(indice)
}

//Calcula o Preço do PA quando copiada a Margem de Lucro da Primeira Linha p/ os demais Itens ...
function calcular_preco_pela_margem(indice) {
	var elementos = document.form.elements
	var objetos_linha = 16
	if(indice == 0) {
		posicao = 0
	}else if(indice == 1) {
		posicao = objetos_linha
	}else if(indice > 1) {
		posicao = eval(indice) * objetos_linha
	}
	var margem_lucro_zero = eval(elementos[posicao + 14].value)
//Cálculo do Preço A
	margem_lucro_a = (elementos[posicao + 3].value == '') ? 0 : eval(strtofloat(elementos[posicao + 3].value))
	elementos[posicao + 2].value = margem_lucro_zero * (1 + margem_lucro_a / 100)//Vlr Novo Pço na Lista ...
	elementos[posicao + 2].value = arred(elementos[posicao + 2].value, 2, 1)
	
//Cálculo do Preço B
	margem_lucro_b = (elementos[posicao + 7].value == '') ? 0 : eval(strtofloat(elementos[posicao + 5].value))
	elementos[posicao + 6].value = margem_lucro_zero * (1 + margem_lucro_b / 100)//Vlr Novo Pço na Lista ...
	elementos[posicao + 6].value = arred(elementos[posicao + 6].value, 2, 1)
}

//Essa função realiza o Processo inverso de calcular a Margem de Lucro quando estou digitando o Preço do PA.
function calcular_fator(indice) {
	var elementos = document.form.elements
	var objetos_linha = 16
	if(indice == 0) {
		posicao = 0
	}else if(indice == 1) {
		posicao = objetos_linha
	}else if(indice > 1) {
		posicao = eval(indice) * objetos_linha
	}
	var preco_liq_fat_rs = eval(strtofloat(elementos[posicao + 12].value))

//Cálculo do Fator A
	preco_a = (elementos[posicao + 2].value == '') ? 0 : eval(strtofloat(elementos[posicao + 2].value))
	if(preco_a != 0) {
		elementos[posicao + 1].value = preco_a / preco_liq_fat_rs
		elementos[posicao + 1].value = arred(elementos[posicao + 1].value, 4, 1)
	}
	
//Cálculo do Fator B
	preco_b = (elementos[posicao + 6].value == '') ? 0 : eval(strtofloat(elementos[posicao + 6].value))
	if(preco_b != 0) {
		elementos[posicao + 5].value = preco_b / preco_liq_fat_rs
		elementos[posicao + 5].value = arred(elementos[posicao + 5].value, 4, 1)
	}
	
//Cálcula a Margem de Lucro ...
	calcular_margem_lucro(indice)
}

//Essa função calcula a Margem de Lucro do PA, independente de eu estar digitando o Preço do PA ou o Fator ...
function calcular_margem_lucro(indice) {
	var elementos = document.form.elements
	var objetos_linha = 16
	if(indice == 0) {
		posicao = 0
	}else if(indice == 1) {
		posicao = objetos_linha
	}else if(indice > 1) {
		posicao = eval(indice) * objetos_linha
	}

//Cálculo da Margem de Lucro A
	preco_margem_lucro_zero = elementos[posicao + 14].value
	preco_a = (elementos[posicao + 2].value == '') ? 0 : eval(strtofloat(elementos[posicao + 2].value))
	if(preco_a != '') {
		elementos[posicao + 3].value = (preco_a / preco_margem_lucro_zero - 1) * 100
		elementos[posicao + 3].value = arred(elementos[posicao + 3].value, 2, 1)
	}
	
//Cálculo da Margem de Lucro B
	preco_b = (elementos[posicao + 6].value == '') ? 0 : eval(strtofloat(elementos[posicao + 6].value))
	if(preco_b != 0) {
		elementos[posicao + 7].value = (preco_b / preco_margem_lucro_zero - 1) * 100
		elementos[posicao + 7].value = arred(elementos[posicao + 7].value, 2, 1)
	}
}

function copiar_valores(indice_coluna) {
	var elementos = document.form.elements
	if(indice_coluna == 1) {//Índice de Coluna que Equivale a Coluna Fator A
		if(typeof(elementos['txt_fator_a[]'][0]) == 'undefined') {
			var linhas = 1//Existe apenas 1 único elemento ...
		}else {
			var linhas = (elementos['txt_fator_a[]'].length)
		}
		for(var i = 1; i < linhas; i++) {
			document.getElementById('txt_fator_a'+i).value = document.getElementById('txt_fator_a0').value
			calcular_preco(i)
		}
	}else if(indice_coluna == 3) {//Índice de Coluna que Equivale a Coluna M.L.A
		if(typeof(elementos['txt_margem_lucro_a[]'][0]) == 'undefined') {
			var linhas = 1//Existe apenas 1 único elemento ...
		}else {
			var linhas = (elementos['txt_margem_lucro_a[]'].length)
		}
		for(var i = 1; i < linhas; i++) {
			document.getElementById('txt_margem_lucro_a'+i).value = document.getElementById('txt_margem_lucro_a0').value
			calcular_preco_pela_margem(i)
			calcular_fator(i)
		}
	}else if(indice_coluna == 5) {//Índice de Coluna que Equivale a Coluna Fator B
		if(typeof(elementos['txt_fator_b[]'][0]) == 'undefined') {
			var linhas = 1//Existe apenas 1 único elemento ...
		}else {
			var linhas = (elementos['txt_fator_b[]'].length)
		}
		for(var i = 1; i < linhas; i++) {
			document.getElementById('txt_fator_b'+i).value = document.getElementById('txt_fator_b0').value
			calcular_preco(i)
		}
	}else if(indice_coluna == 7) {//Índice de Coluna que Equivale a Coluna M.L.B
		if(typeof(elementos['txt_margem_lucro_b[]'][0]) == 'undefined') {
			var linhas = 1//Existe apenas 1 único elemento ...
		}else {
			var linhas = (elementos['txt_margem_lucro_b[]'].length)
		}
		for(var i = 1; i < linhas; i++) {
			document.getElementById('txt_margem_lucro_b'+i).value = document.getElementById('txt_margem_lucro_b0').value
			calcular_preco_pela_margem(i)
			calcular_fator(i)
		}
	}
}

function calcular_fatores() {
	var elementos = document.form.elements
	if(typeof(elementos['txt_fator_a[]'][0]) == 'undefined') {
		var linhas = 1//Existe apenas 1 único elemento ...
	}else {
		var linhas = (elementos['txt_fator_a[]'].length)
	}
	for(var i = 0; i < linhas; i++) {
		calcular_fator(i)
	}
}

function validar() {
	var elementos = document.form.elements
	var objetos_linha = 16
	var objetos_fim = 3
//Prepara no formato moeda antes de submeter para o BD
	for(i = 0; i < (elementos.length - objetos_fim); i+=objetos_linha) {
		elementos[i].value = strtofloat(elementos[i].value)
		elementos[i + 2].value = strtofloat(elementos[i + 2].value)
		elementos[i + 4].value = strtofloat(elementos[i + 4].value)
		elementos[i + 6].value = strtofloat(elementos[i + 6].value)
	}
}
</Script>
</head>
<body onload='calcular_fatores()'>
<form name='form' action="<?=$PHP_SELF.'?passo=2';?>" method='post' onsubmit="return validar()">
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
	<tr align='center'>
            <td colspan='17'>
                <?=$mensagem[$valor];?>
            </td>
	</tr>
	<tr class='linhacabecalho' align='center'>
            <td colspan='17'>
                Nova Lista de Preço Promocional
            </td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td rowspan="2" bgcolor='#CECECE'>
			<font size='-2'>
				<b>MMV</b>
			</font>
		</td>
		<td rowspan="2" bgcolor='#CECECE'>
			<font size='-2'>
				<b>Produto</b>
			</font>
		</td>
		<td colspan='8' bgcolor='#CECECE'>
			<font size='-2'>
				<b>Condições Promocionais R$</b>
			</font>
		</td>
		<td colspan="2" bgcolor='#CECECE'>
			<font size='-2'>
				<b>Preço Bruto Fat. R$</b>
			</font>
		</td>
		<td colspan="3" rowspan="2" bgcolor='#CECECE'>
			<font size='-2' title="Desconto A / Desconto B / Acréscimo Grupo P.A">
				<b>Desc. A / B / Ac. <br>Grupo P.A.</b>
			</font>
		</td>
		<td rowspan="2" bgcolor='#CECECE'>
			<font size='-2'>
				<b>P. Líq. <br>Fat. R$</b>
			</font>
		</td>
		<td rowspan="2" bgcolor='#CECECE'>
			<font size='-2'>
				<b>Preço Máx. <br>Custo <br>Fat. R$</b>
			</font>
		</td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td bgcolor='#CECECE'>
			<font size='-2'>
				<b>Qtde A</b>
			</font>
		</td>
		<td bgcolor='#CECECE'>
                    <a href = 'alterar_fator.php?indice_coluna=1' style='cursor:help' class='html5lightbox'>
                        <b>Fator A</b>
                    </a>
		</td>
		<td bgcolor='#CECECE'>
			<font size='-2'>
				<b>Pço A</b>
			</font>
		</td>
		<td bgcolor='#CECECE'>
                    <font size='-2' title='Margem de Lucro A' style='cursor:help'>
                        <a href = 'alterar_margem_lucro.php?indice_coluna=3' style='cursor:help' class='html5lightbox'>
                            <b>M. L. A</b>
                        </a>
                    </font>
		</td>
		<td bgcolor='#CECECE'>
			<font size='-2'>
				<b>Qtde B</b>
			</font>
		</td>
		<td bgcolor='#CECECE'>
                    <font size='-2' title='Fator B de Desconto' style='cursor:help'>
                        <a href = 'alterar_fator.php?indice_coluna=5' style='cursor:help' class='html5lightbox'>
                            <b>Fator B</b>
                        </a>
                    </font>
		</td>
		<td bgcolor='#CECECE'>
			<font size='-2'>
				<b>Pço B</b>
			</font>
		</td>
		<td bgcolor='#CECECE'>
			<font size='-2' title='Margem de Lucro B' style='cursor:help'>
				<a href="javascript:nova_janela('alterar_margem_lucro.php?indice_coluna=7', 'POP', '', '', '', '', 200, 750, 'c', 'c', '', '', 's', 's', '', '', '')" alt="Alterar Margem de Lucro B" title="Alterar Margem de Lucro B" style="cursor:help" class='link'>
					<b>M. L. B</b>
				</a>
			</font>
		</td>
		<td bgcolor='#CECECE'>
			<font size='-2'>
				<b>Atual</b>
			</font>
		</td>
		<td bgcolor='#CECECE'>
			<font title="Margem de Lucro já c/ 20% Desc" size='-2'>
				<b>M. L. já c/ 20% Desc</b>
			</font>
		</td>
	</tr>
<?
//Aqui instância as sub-funções
		for ($i = 0;  $i < $linhas; $i++) {
/*********Todo esse código, vai estar me auxiliando para a Função em JavaScript*********/
//Fórmula do Preço Máximo Custo Fat. R$ - esse campo está aqui, mais ele é printado + abaixo
			$preco_maximo_custo_fat_rs = custos::preco_custo_pa($campos[$i]['id_produto_acabado']) / $fator_desc_max_vendas;
			//Forço o arred. para 2 casas para não dar erro na fórmula por causa do JavaScript -> Dárcio
			$preco_maximo_custo_fat_rs = round($preco_maximo_custo_fat_rs, 2);
//Busca da Qtde de Peças por Embalagem do Produto Acabado que será sugerida nas Qtdes Promocionais ...
			$sql = "Select ppe.pecas_por_emb 
                                from pas_vs_pis_embs ppe 
                                inner join produtos_acabados pa on pa.id_produto_acabado = ppe.id_produto_acabado and pa.operacao_custo = '".$campos[$i]['operacao_custo']."' 
                                where ppe.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' 
                                and ppe.embalagem_default = '1' limit 1 ";
			$campos_pcs_embalagem = bancos::sql($sql);
			if(count($campos_pcs_embalagem) == 1) {
				$pecas_embalagem = $campos_pcs_embalagem[0]['pecas_por_emb'];
			}else {
				$pecas_embalagem = 1;
			}
/***************************************************************************************/
?>
		<tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
			<td align="right">
				<?=number_format($campos[$i]['mmv'], 2, ',', '.');?>
			</td>
			<td align="left">
				<font title="Grupo P.A. (E. D.): <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>" size='-2'>
					<?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
				</font>
			</td>
			<td>
			<?
//Se a Qtde for vazia na Base de Dados, então eu sugiro a Qtde de Peças Promocional p/ este campo ...
				if(empty($campos[$i]['qtde_promocional_simulativa'])) {
					$qtde_promocional_simulativa = $pecas_embalagem;
				}else {
					$qtde_promocional_simulativa = $campos[$i]['qtde_promocional_simulativa'];
				}
			?>
				<input type="text" name="txt_qtde_promocional_a[]" value="<?=$qtde_promocional_simulativa;?>" maxlength="7" size="6" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class="caixadetexto">
			</td>
			<?
//Utilizada em alguns campos + abaixo ...
				$preco_margem_lucro_zero = $preco_maximo_custo_fat_rs / ($fator_margem_lucro / $fator_desc_max_vendas);
			?>
			<td>
				<input type="text" name="txt_fator_a[]" id="txt_fator_a<?=$i;?>" maxlength="9" size="8" onkeyup="verifica(this, 'moeda_especial', '4', '', event);calcular_preco('<?=$i;?>')" class="caixadetexto">
				<?
					if($i == 0) {//Só irá mostrar na Primeira Linha ...
				?>
				<img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_valores(1)">
				<?
					}
				?>
			</td>
			<td>
				<input type="text" name="txt_preco_promocional_a[]" value="<?=number_format($campos[$i]['preco_promocional_simulativa'], 2, ',', '.');?>" maxlength="7" size="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_fator('<?=$i;?>')" class="caixadetexto">
			</td>
			<td>
			<?
//Aqui eu faço o cálculo quando carregar a Tela no início ...
				if($campos[$i]['preco_promocional_simulativa'] != '' && $campos[$i]['preco_promocional_simulativa'] != '0.00') {
					if(empty($fator_desc_max_vendas) || $fator_desc_max_vendas=="0.00") { $fator_desc_max_vendas=1; }
					$margem_lucro = (($campos[$i]['preco_promocional_simulativa']/$preco_margem_lucro_zero)-1)*100;
					$margem_lucro = number_format($margem_lucro, 2, ',', '.');
				}else {
					$margem_lucro = '';
				}
			?>
				<input type="text" name="txt_margem_lucro_a[]" id="txt_margem_lucro_a<?=$i;?>" value="<?=$margem_lucro;?>" maxlength="7" size="7" class="textdisabled" disabled>
				<?
					if($i == 0) {//Só irá mostrar na Primeira Linha ...
				?>
				<img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_valores(3)">
				<?
					}
				?>
			</td>
			<td>
			<?
//Se a Qtde B for vazia na Base de Dados, então eu sugiro a Qtde de Peças Promocional p/ este campo ...
				if(empty($campos[$i]['qtde_promocional_simulativa_b'])) {
					$qtde_promocional_simulativa_b = $pecas_embalagem;
				}else {
					$qtde_promocional_simulativa_b = $campos[$i]['qtde_promocional_simulativa_b'];
				}
			?>
				<input type="text" name="txt_qtde_promocional_b[]" value="<?=$qtde_promocional_simulativa_b;?>" maxlength="7" size="6" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class="caixadetexto">
			</td>
			<td>
				<input type="text" name="txt_fator_b[]" id="txt_fator_b<?=$i;?>" maxlength="9" size="8" onkeyup="verifica(this, 'moeda_especial', '4', '', event);calcular_preco('<?=$i;?>')" class="caixadetexto">
				<?
					if($i == 0) {//Só irá mostrar na Primeira Linha ...
				?>
				<img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_valores(5)">
				<?
					}
				?>
			</td>
			<td>
				<input type="text" name="txt_preco_promocional_b[]" value="<?=number_format($campos[$i]['preco_promocional_simulativa_b'], 2, ',', '.');?>" maxlength="7" size="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_fator('<?=$i;?>')" class="caixadetexto">
			</td>
			<td>
			<?
//Aqui eu faço o cálculo quando carregar a Tela no início ...
				if($campos[$i]['preco_promocional_simulativa_b'] != '' && $campos[$i]['preco_promocional_simulativa_b'] != '0.00') {
					if(empty($fator_desc_max_vendas) || $fator_desc_max_vendas=="0.00") { $fator_desc_max_vendas=1; }
					$margem_lucro = (($campos[$i]['preco_promocional_simulativa_b']/$preco_margem_lucro_zero)-1)*100;
					$margem_lucro = number_format($margem_lucro, 2, ',', '.');
				}else {
					$margem_lucro = '';
				}
			?>
				<input type="text" name="txt_margem_lucro_b[]" id="txt_margem_lucro_b<?=$i;?>" value="<?=$margem_lucro;?>" maxlength="7" size="7" class="textdisabled" disabled>
				<?
					if($i == 0) {//Só irá mostrar na Primeira Linha ...
				?>
				<img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_valores(7)">
				<?
					}
				?>
			</td>
			<td>
				<input type="text" name="txt_preco_bruto_fat_rs[]" value="<?=number_format($campos[$i]['preco_unitario'], 2, ',', '.');?>" maxlength="7" size="6" class="textdisabled" disabled>
			</td>
			<td>
			<?
//Fórmula do Preço Líquido Fat. R$ - Impressa mais abaixa ...
				$preco_liq_fat_rs = $campos[$i]['preco_unitario'] * (1 - $campos[$i]['desc_base_a_nac'] / 100) * (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100);
//Cálculo da Margem de Lucro já com 20 Desconto, p/ não dar erro de divisão por Zero ...
				if($preco_margem_lucro_zero == 0) $preco_margem_lucro_zero = 1;
				$margem_lucro_ja_c_20_desc = (($preco_liq_fat_rs * $fator_desc_max_vendas) / $preco_margem_lucro_zero - 1) * 100;
				$margem_lucro_ja_c_20_desc = round($margem_lucro_ja_c_20_desc, 2);
				echo segurancas::number_format($margem_lucro_ja_c_20_desc, 2, '.');
			?>
			</td>
			<td colspan="3">
				<font color="green" size='-2'>
					<?=number_format($campos[$i]['desc_base_a_nac'], 2, ',', '.');?>
				</font>
				<input type="hidden" name="txt_desconto_a_grupoa[]" value="<?=number_format($campos[$i]['desc_base_a_nac'], 2, ',', '.');?>" maxlength="7" size="6" class="caixadetexto" disabled>
				/ 
				<font color="green" size='-2'>
					<?=number_format($campos[$i]['desc_base_b_nac'], 2, ',', '.');?>
				</font>
				<input type="hidden" name="txt_desconto_b_grupoa[]" value="<?=number_format($campos[$i]['desc_base_b_nac'], 2, ',', '.');?>" maxlength="7" size="6" class="caixadetexto" disabled>
				/ 
				<font color="green" size='-2'>
					<?=number_format($campos[$i]['acrescimo_base_nac'], 2, ',', '.');?>
				</font>
				<input type="hidden" name="txt_acrescimo_base_nac[]" value="<?=number_format($campos[$i]['acrescimo_base_nac'], 2, ',', '.');?>" maxlength="7" size="6" class="caixadetexto" disabled>
			</td>
			<td>
<?
//Forço o arred. para 2 casas para não dar erro na fórmula por causa do PHP -> Dárcio
				$preco_liq_fat_rs = round($preco_liq_fat_rs, 2);
?>
				<input type="text" name="txt_preco_liq_fat_rs[]" value="<?=number_format($preco_liq_fat_rs, 2, ',', '.');?>" maxlength="7" size="6" class="textdisabled" disabled>
			</td>
			<?
				if($campos[$i]['status_custo'] == 1) {//Custo Liberado
//Comparação
//Preço Máx. Custo Fat. R$ maior do q P. Líq. Fat. R$
					if($preco_maximo_custo_fat_rs > $preco_liq_fat_rs) {
//Preço Máx. Custo Fat. R$ menor do q P. Líq. Fat. R$
						$color = 'background:red;color:white';
					}else {
						$color = 'background:#FFFFE1;color:gray';
					}
					$printar = number_format($preco_maximo_custo_fat_rs, 2, ',', '.');
				}else {//Custo não Liberado
					$color = 'background:#FFFFE1;color:gray';
					$printar = 'Orçar';
				}
			?>
			<td>
				<input type="text" name="txt_preco_max_custo_fat_rs[]" value="<?=$printar;?>" maxlength="7" size="6" class="caixadetexto" style="<?=$color;?>" disabled>
				<input type="hidden" name="hdd_preco_margem_lucro_zero[]" value="<?=$preco_margem_lucro_zero;?>">
				<input type="hidden" name="hdd_produto_acabado[]" value="<?=$campos[$i]['id_produto_acabado'];?>">
			</td>
		</tr>
<?
			}
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan='17'>
			<input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'lista_preco_promocional.php'" class='botao'>
			<input type="button" name="cmd_redefinir" value="Redefinir" title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class="botao">
			<input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class='botao'>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
}else if($passo == 2) {//Passo que serve p/ fazer a Atualização dos Preços ...
//Aqui é a parte de atualização dos Produtos Acabados
    for($i = 0; $i < count($_POST['hdd_produto_acabado']); $i++) {
        $sql = "UPDATE `produtos_acabados` SET `qtde_promocional_simulativa` = '".$_POST['txt_qtde_promocional_a'][$i]."', `preco_promocional_simulativa` = '".$_POST['txt_preco_promocional_a'][$i]."', `qtde_promocional_simulativa_b` = '".$_POST['txt_qtde_promocional_b'][$i]."', `preco_promocional_simulativa_b` = '".$_POST['txt_preco_promocional_b'][$i]."' WHERE `id_produto_acabado` = '".$_POST['hdd_produto_acabado'][$i]."' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'lista_preco_promocional.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Nova Lista de Preço Promocional ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function submeter() { 
	document.form.target = '_self'
	document.form.action = "<?=$PHP_SELF.'?passo=1';?>"
}
</Script>
</head>
<body onLoad="document.form.txt_referencia.focus()">
<form name="form" method="post">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
	<tr align='center'>
		<td colspan='2' width="750">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho'>
		<td colspan="2" align='center'>
			<font color='#FFFFFF' size='-1'>
				Consultar Produto Acabado - Nova Lista de Preço Promocional
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Referência
		</td>
		<td>
			<input type="text" name="txt_referencia" title="Digite a Referência" size="40" maxlength="35" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Discriminação
		</td>
		<td>
			<input type="text" name="txt_discriminacao" title="Digite a Discriminação" size="40" maxlength="35" class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Empresa Divisão
		</td>
		<td>
			<select name="cmb_empresa_divisao" title="Consultar Produto Acabado por: Empresa Divisão" class="combo">
			<?
				$sql = "Select id_empresa_divisao, razaosocial 
						from empresas_divisoes 
						where ativo = 1 order by razaosocial asc ";
				echo combos::combo($sql);
			?>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Grupo P.A.
		</td>
		<td>
			<select name="cmb_grupo_pa" title="Consultar Produto Acabado por: Grupo P.A." class="combo">
			<?
//Aqui traz todos os grupos com exceção dos que são pertencentes a Família de Componentes
				$sql = "Select id_grupo_pa, nome 
						from grupos_pas 
						where ativo = 1 
						and id_familia <> 23 order by nome asc ";
				echo combos::combo($sql);
			?>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Família
		</td>
		<td>
			<select name="cmb_familia" title="Consultar Produto Acabado por: Família" class="combo">
			<?
				$sql = "Select id_familia, nome 
						from familias 
						where ativo = 1 order by nome asc ";
				echo combos::combo($sql);
			?>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			Ordenar por
		</td>
		<td colspan="2">
			<select name="cmb_order_by" title="Ordernar" class="combo">
				<option value="pa.referencia" selected>Referência</option>
				<option value="pa.discriminacao">Discriminação</option>
			</select>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<input type='checkbox' name='chkt_preco_promocional' value='1' title='Preço Promocional <> 0' id='preco_promocional' class='checkbox'>
			<label for='preco_promocional'>Preço Promocional <> 0</label>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<input type='checkbox' name='chkt_todos_produtos_zerados' value='1' title='Todos os Produtos Zerados' id='todos' class='checkbox'>
			<label for='todos'>Todos os Produtos Zerados</label>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'lista_preco.php'" class='botao'>
			<input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' onclick="return submeter()" class="botao">
		</td>
	</tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* Não exibe os P.A(s) que estão com a marcação (ÑP) - Não Produzir.
</pre>
<?}?>