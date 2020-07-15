<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos_new.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
session_start('funcionarios');

if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_revenda_esp.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo_new/custo_industrial/pa_componente_esp.php', '../../../../');
}
$mensagem[1] = 'ATUALIZADO COM SUCESSO !';

//Aqui são as variáveis para o cálculo do custo
$valor_moeda_dolar_custo 	= genericas::variavel(7);
$valor_moeda_euro_custo 	= genericas::variavel(8);
$fator_importacao			= genericas::variaveis('fator_importacao');
$taxa_financeira_vendas		= genericas::variaveis('taxa_financeira_vendas');

$sql = "Select id_pais 
		from fornecedores 
		where id_fornecedor = '$id_fornecedor' limit 1 ";
$campos = bancos::sql($sql);
$id_pais = $campos[0]['id_pais'];

if($passo == 1) {
	$data_sys = date('Y-m-d H:i:s');
/*Essa variável é um hidden que eu tenho nesse arquivo aqui mesmo mais abaixo, e quem controla ele é o 
Pop-Up de Lista de Preço, se essa variável for = 1, então ele ignora o Update*/
	if($ignorar_update != 1) {//Aqui ignora o Update, caso = 1
//Transformação em vetor
		$vetor_fornecedor_prod_insumo = explode(',', $chkt_fornecedor_prod_insumo);
		for($i = 0; $i < count($vetor_fornecedor_prod_insumo); $i++) {
			$sql = "Update fornecedores_x_prod_insumos set fator_margem_lucro_pa = '$txt_fator_margem_lucro_pa[$i]', data_sys ='$data_sys' where id_fornecedor_prod_insumo = '$vetor_fornecedor_prod_insumo[$i]' limit 1";
			bancos::sql($sql);
		}
	}
?>
	<Script Language = 'JavaScript'>
		window.location = 'itens.php?id_fornecedor=<?=$id_fornecedor;?>&razaosocial=<?=$razaosocial;?>&chkt_fornecedor_prod_insumo=<?=$chkt_fornecedor_prod_insumo;?>&resposta=<?=$resposta?>&id_produto_acabado=<?=$id_produto_acabado;?>&atalho=<?=$atalho;?>&valor=1'
	</Script>
<?
}else {
	if(!empty($valor)) {
?>
	<Script Language = 'JavaScript'>
		alert('<?=$mensagem[$valor]?>')
	</Script>
<?}?>
<html>
<head>
<title>.:: Produto(s) Acabado(s) do Fornecedor <?=$razaosocial;?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function calcular(valor, limite) {
	var elementos = document.form.elements, x = 0
	var resultado1 = 0, resultado2 = 0
	var taxa_financeira_vendas	= '<?=$taxa_financeira_vendas;?>'
	var taxa_financeira_vendas	= ((taxa_financeira_vendas / 100) + 1)
	var fator_importacao		= '<?=$fator_importacao;?>'
	var valor_moeda_dolar_custo	= '<?=$valor_moeda_dolar_custo;?>'
	var valor_moeda_euro_custo	= '<?=$valor_moeda_euro_custo;?>'
	var id_pais			= '<?=$id_pais;?>'

	if(limite == '') {
		limite = 1
	}

	var linha = eval(limite)
	if(linha == '') {
		linha = 1;
	}

	for(l = 0; l < linha; l++) {
		if(valor == '') {
			valor_aux = l * 9
		}else {
			valor_aux = valor * 9
		}
		for(x = valor_aux; x < (valor_aux + 9); x +=9) {
			if(elementos[x].type == 'text') {
				//Atribuições das caixas de texto para as variáveis
				var preco_faturado_nac = elementos[x].value
				if(typeof(preco_faturado_nac) == 'undefined' || preco_faturado_nac == '') {
					preco_faturado_nac = 0
				} else {
					preco_faturado_nac = eval(strtofloat(preco_faturado_nac))
				}

				var prazo_pgto_dias = elementos[x + 1].value
				if(typeof(prazo_pgto_dias) == 'undefined' || prazo_pgto_dias == '') {
					prazo_pgto_dias = 0
				}else {
					prazo_pgto_dias = eval(strtofloat(prazo_pgto_dias))
				}
				
				var fator_margem_lucro =  elementos[x + 2].value
				if(typeof(fator_margem_lucro) == 'undefined' || fator_margem_lucro == '') {
					fator_margem_lucro = 0
				}else {
					fator_margem_lucro = eval(strtofloat(fator_margem_lucro))
				}
				
				var custo_pa_indust =  elementos[x + 3].value
				if(typeof(custo_pa_indust) == 'undefined' || custo_pa_indust == '') {
					custo_pa_indust = 0
				}else {
					custo_pa_indust = eval(strtofloat(custo_pa_indust))
				}

				var preco_faturado_estrang =  elementos[x + 5].value
				if(typeof(preco_faturado_estrang) == 'undefined' || preco_faturado_estrang == '') {
					preco_faturado_estrang = 0
				}else {
					preco_faturado_estrang = eval(strtofloat(preco_faturado_estrang))
				}

				var valor_moeda_compra =  elementos[x + 6].value
				if(typeof(valor_moeda_compra) == 'undefined' || valor_moeda_compra == '') {
					valor_moeda_compra = 0
				}else {
					valor_moeda_compra = eval(strtofloat(valor_moeda_compra))
				}

				var id_tipo_moeda =  elementos[x + 8].value
				if(typeof(id_tipo_moeda) == 'undefined' || id_tipo_moeda == '') {
					id_tipo_moeda = 0
				}else {
					id_tipo_moeda = eval(strtofloat(id_tipo_moeda))
				}
				if(id_pais == 31) {//nacional multiplicar pelo moeda compra no caso q compra em moda estrangeira
					resultado1 = fator_margem_lucro * preco_faturado_nac * taxa_financeira_vendas
					if(id_tipo_moeda == 1) { //Dólar
						resultado2 = fator_margem_lucro * preco_faturado_estrang * taxa_financeira_vendas * valor_moeda_compra
					}else if(id_tipo_moeda == 2) { //Euro
						resultado2 = fator_margem_lucro * preco_faturado_estrang * taxa_financeira_vendas * valor_moeda_compra
					}else {
						resultado2 = 0
					}
//Aqui também já está somando o Custo P.A. Industrial
					elementos[x + 4].value = resultado1 + custo_pa_indust
					elementos[x + 7].value = resultado2 + custo_pa_indust
				} else { //se for estrangeiro multiplicar pelo moeda custo
					resultado1 = 0
					if(id_tipo_moeda == 1) { //Valor Dólar
						resultado2 = fator_margem_lucro * preco_faturado_estrang * taxa_financeira_vendas * valor_moeda_dolar_custo * fator_importacao
					}else if(id_tipo_moeda == 2) { //Valor Euro
						resultado2 = fator_margem_lucro * preco_faturado_estrang * taxa_financeira_vendas * valor_moeda_euro_custo * fator_importacao
					}
//Aqui também já está somando o Custo P.A. Industrial
					elementos[x + 4].value = resultado1 + custo_pa_indust
					elementos[x + 7].value = resultado2 + custo_pa_indust
				}
				elementos[x + 4].value = arred(elementos[x + 4].value, 2, 1)
				elementos[x + 7].value = arred(elementos[x + 7].value, 2, 1)
			}
		}
	}
}

function redefinir() {
	var resposta = confirm('DESEJA RETORNAR OS VALORES ALTERADOS ?')
	if(resposta == false) {
		return false
	}else {
		document.form.reset()
	}
}

function validar() {
	var elementos = document.form.elements
//Número de elementos - 14 porque estou descontando os hiddens, e buttons, que estão abaixo do for
	for(var x = 0; x < (elementos.length) - 14; x+=9) {
		if(elementos[x].type == 'text') {
//Desabilito esse campo p/ poder gravar na Base de Dados ...
			elementos[x + 2].disabled = false
			var fator_margem_lucro_pa = eval(strtofloat(elementos[x + 2].value))
//Se o Fator Margem de Lucro do P.A. < 1.45 and Fator Margem de Lucro do P.A. > 2
			if(fator_margem_lucro_pa < 1.45 || fator_margem_lucro_pa > 2) {
				alert('FATOR MARGEM DE LUCRO INVÁLIDO !\nDIGITE UM VALOR ENTRE 1,45 E 2,00 PARA ESTE FATOR !!!')
				elementos[x + 2].focus()
				elementos[x + 2].select()
				return false
			}
		}
	}
	var resposta = confirm('DESEJA SALVAR OS VALORES ?')
	if(resposta == false) {
		return false
	}else {
		alert('AGUARDE ESTA ROTINA PODE DEMORAR ALGUNS MINUTOS ! \nSEU NAVEGADOR PODE NÃO RESPONDER DURANTE ALGUM TEMPO !');
		tratar_elementos()
		return true
	}
}

//Trata os elementos antes de submeter para o Banco de Dados
function tratar_elementos() {
	elementos = document.form.elements
//Número de elementos - 14 porque estou descontando os hiddens, e buttons, que estão abaixo do for
	for(var x = 0; x < (elementos.length) - 14; x+=9) {
		if(elementos[x].type == 'text') {
			elementos[x + 2].value = strtofloat(elementos[x + 2].value)
		}
	}
}

//Passa o índíce da coluna
function atualizar_coluna(indice) {
	var elementos = document.form.elements
	if(!confirm('DESEJA ATUALIZAR REALMENTE ?')) {
		return false
	} else {
//Número de elementos - 14 porque estou descontando os hiddens, e buttons, que estão abaixo do for
		for(var x = 0; x < (elementos.length) - 14; x+=9) {
			elementos[x + indice].value = elementos[indice].value
		}
		document.form.cmd_recalcular.onclick()
	}
}

/*Aqui eu também passo o id_pais, porque se o País for nacional "31", eu não posso ficar bloqueando 
e desbloqueando o Custo*/
function produto_acabado_industrial(id_produto_acabado, id_pais) {
//Aqui é a pergunta para a atualização
	var resposta = confirm('DESEJA SALVAR OS VALORES ?')
	if(resposta == true) {//True
		alert('AGUARDE ESTA ROTINA PODE DEMORAR ALGUNS MINUTOS ! \nSEU NAVEGADOR PODE NÃO RESPONDER DURANTE ALGUM TEMPO !');
		var elementos = document.form.elements
		tratar_elementos()
		document.form.resposta.value = resposta
		document.form.id_produto_acabado.value = id_produto_acabado
		window.document.form.submit()
	}else {//False
		nova_janela('../prod_acabado_componente/prod_acabado_componente2.php?id_produto_acabado='+id_produto_acabado+'&id_pais='+id_pais, 'DETALHES_CUSTO', '', '', '', '', '550', '950', 'c', 'c', '', '', 's', 's', '', '', '')
	}
}
</Script>
</head>
<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<table width='1000' border='0' align="left" cellspacing='1' cellpadding='1'>
<?
$sql = "Select pa.id_produto_acabado, pa.referencia, pa.discriminacao, gpa.nome, fpi.* 
        from fornecedores_x_prod_insumos fpi 
        inner join produtos_acabados pa on pa.id_produto_insumo = fpi.id_produto_insumo and pa.ativo = 1 and pa.operacao_custo = 1 
        inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
        inner join grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
        where fpi.id_fornecedor_prod_insumo in ($chkt_fornecedor_prod_insumo) order by pa.discriminacao ";
$campos_lista = bancos::sql($sql);
$linhas = count($campos_lista);

for($i = 0; $i < $linhas; $i++) {
	if(!empty($txt_preco_faturado)) {
		$preco_faturado = $txt_preco_faturado;
	}else {
		if($campos_lista[$i]['preco_faturado'] == '0.00') {
			$preco_faturado = '0,00';
		}else {
			$preco_faturado = number_format($campos_lista[$i]['preco_faturado'], 2, ',', '.');
		}
	}

	if(!empty($txt_prazo_pgto_ddl)) {
		$prazo_pgto_ddl = $txt_prazo_pgto_ddl;
	}else {
		if($campos_lista[$i]['prazo_pgto_ddl'] == '0.0') {
			$prazo_pgto_ddl = '0,0';
		}else {
			$prazo_pgto_ddl = number_format($campos_lista[$i]['prazo_pgto_ddl'], 1, ',', '.');
		}
	}
	
	if(!empty($txt_fator_margem_lucro_pa)) {
		$fator_margem_lucro_prod_acab = $txt_fator_margem_lucro_pa;
	}else {
		if($campos_lista[$i]['fator_margem_lucro_pa'] == '0.00') {
			/*Se esse Fator estiver zerado então eu já atualizo o mesmo na lista de Preço 
			com o Fator da Variável ...*/
			$sql = "UPDATE `fornecedores_x_prod_insumos` SET `fator_margem_lucro_pa` = '".genericas::variavel(22)."' WHERE `id_fornecedor_prod_insumo` = '".$campos_lista[$i]['id_fornecedor_prod_insumo']."' LIMIT 1 ";
			bancos::sql($sql);
			$fator_margem_lucro_prod_acab = number_format(genericas::variavel(22), 2, ',', '.');
		}else {
			$fator_margem_lucro_prod_acab = number_format($campos_lista[$i]['fator_margem_lucro_pa'], 2, ',', '.');
		}
	}
	
	if(!empty($txt_preco_fat_exp)) {
		$preco_fat_exp = $txt_preco_fat_exp;
	}else {
		if($campos_lista[$i]['preco_faturado_export'] == '0.00') {
			$preco_fat_exp = '0,00';
		}else {
			$preco_fat_exp = number_format($campos_lista[$i]['preco_faturado_export'], 2, ',', '.');
		}
	}
	

	if(!empty($txt_valor_moeda_compra)) {
		$valor_moeda_compra = $txt_valor_moeda_compra;
	}else {
		if($campos_lista[$i]['valor_moeda_compra'] == '0.00') {
			$valor_moeda_compra = '0,00';
		}else {
			$valor_moeda_compra = number_format($campos_lista[$i]['valor_moeda_compra'], 2, ',', '.');
		}
	}
	
	$id_tipo_moeda = $campos_lista[$i]['tp_moeda'];
?>
	<tr class="linhanormal" align="center" title="<?=$campos_lista[$i]['referencia'].' | '.$campos_lista[$i]['discriminacao'];?>">
		<td width="50">
			<a href="javascript:nova_janela('../../../vendas/estoque_acabado/detalhes.php?id_produto_acabado=<?=$campos_lista[$i]['id_produto_acabado'];?>', 'COMPRA', '', '', '', '', '600', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title="Alterar Lista de Preço" class="link">
				<font color='red' style='cursor:help' title='Compra'><b>(Compras)</b></font>
			</a>
		</td>
		<td width="282" align="left">
			<a href="javascript:nova_janela('custo_revenda.php?id_fornecedor_prod_insumo=<?=$campos_lista[$i]['id_fornecedor_prod_insumo'];?>', 'DETALHES_CUSTO', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title="Alterar Lista de Preço" class="link">
				<?=intermodular::pa_discriminacao($campos_lista[$i]['id_produto_acabado']);?>
			</a>
			&nbsp;
			<font color='black' size='-2' title='Data da Última Atualização' style='cursor:help'>
				<b>(<?=data::datetodata(substr($campos_lista[$i]['data_sys'], 0, 10), '/');?>)</b>
			</font>
		</td>
		<td width="80">
			<input type="text" name="txt_preco_faturado[]" value="<?=$preco_faturado;?>" size="7" maxlength="12" class="disabled" disabled>
		</td>
		<td width="79">
			<input type="text" name="txt_prazo_pgto_ddl[]" value="<?=$prazo_pgto_ddl;?>" size="6" maxlength="12" class="disabled" disabled>
		</td>
		<td width="90">
			<input type="text" name="txt_fator_margem_lucro_pa[]" value="<?=$fator_margem_lucro_prod_acab;?>" tabindex="<?="100".$cont;?>" size="6" maxlength="12" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>', '')" class="textdisabled" disabled>
<?
	if($i == 0) {
		echo '<img src="../../../../imagem/seta_abaixo.gif" width="12" height="12" onclick="atualizar_coluna(2)">';
	} else {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
	}
?>
		</td>
<!--Aqui chama a tela do Custo de P.A. Industrializado 
****Aqui eu também passo o id_pais, porque se o País for nacional "31", eu não posso ficar bloqueando 
e desbloqueando o Custo-->
		<td width='92' onclick="produto_acabado_industrial('<?=$campos_lista[$i]['id_produto_acabado'];?>', '<?=$id_pais;?>')">
			<?
				$todas_etapas = custos::todas_etapas($campos_lista[$i]['id_produto_acabado'], 1) * ($taxa_financeira_vendas / 100 + 1);
				$todas_etapas = number_format($todas_etapas, 2, ',', '.');
			?>
			<input type="hidden" name="txt_custo_pa_indust[]" value="<?=$todas_etapas;?>" size="10" maxlength="12" class="caixadetexto">
			<a href="#" title="Detalhes do Custo do P.A." class="link">
				<?=$todas_etapas;?>
			</a>
        </td>
		<td width="88">
			<input type="text" name="txt_valor_custo_nacional[]" size="10" maxlength="12" class="disabled" disabled>
		</td>
		<td width="89">
			<input type="text" name="txt_preco_fat_exp[]" value="<?=$preco_fat_exp;?>" size="8" maxlength="12" class="disabled" disabled>
			<?
				if($id_tipo_moeda == 1) {
					echo 'U$';
				}else if($id_tipo_moeda == 2) {
					echo '&euro;&nbsp;&nbsp;';
				}
			?>
		</td>
		<td width="80">
			<input type="text" name="txt_valor_moeda_compra[]" value="<?=$valor_moeda_compra;?>" size="8" maxlength="12" class="disabled" disabled>
		</td>
		<td width='80'>
			<input type="text" name="txt_valor_custo_internacional[]" size="10" maxlength="12" class="disabled" disabled>
			<input type="hidden" name="tipo_moeda[]" value="<?=$id_tipo_moeda;?>">
		</td>
	</tr>
<?
		$cont++;
}
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='10'>
			<?
				if($atalho == 1) {//Aqui volta somente para a tela com um único produto
					$endereco = 'pa_componente_revenda_esp2.php?id_produto_acabado='.$campos_lista[0]['id_produto_acabado'];
				}else {//Aqui volta para a lista dos vários produtos
					$endereco = 'consultar_produtos.php'.$parametro;
				}
			?>
			<input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="javascript:parent.location='<?=$endereco;?>'" class="botao">
			<input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir();calcular('', '<?=$linhas;?>')" style="color:#ff9900;" class="botao">
			<input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
			<input type='button' name="cmd_imprimir" value="Imprimir" title="Imprimir" onclick="javascript:nova_janela('imprimir.php?chkt_fornecedor_prod_insumo=<?=$chkt_fornecedor_prod_insumo;?>&razao_social=<?=$razaosocial;?>', 'IMPRIMIR', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class="botao">
			<input type='hidden' name="cmd_recalcular" value="Recalcular" onclick="calcular('', '<?=$linhas;?>')" class="botao">
			<input type='hidden' name="cmd_recarregar" value="" onclick="window.location = 'itens.php?id_fornecedor=<?=$id_fornecedor;?>'" class="botao">
		</td>
	</tr>
</table>
<input type='hidden' name='endereco' value='<?=$endereco;?>'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor?>'>
<input type='hidden' name='razaosocial' value='<?=$razaosocial?>'>
<input type='hidden' name='chkt_fornecedor_prod_insumo' value='<?=$chkt_fornecedor_prod_insumo;?>'>
<!--Variáveis para o Controle do Pop-UP-->
<input type='hidden' name='resposta' value="<?=$resposta;?>" onclick="tratar_elementos()">
<input type='hidden' name='id_produto_acabado' value="<?=$id_produto_acabado;?>">
<!--Essa parâmetro é controlado pelo Pop-Up de Lista de Preço-->
<input type='hidden' name='ignorar_update'>
<input type='hidden' name='atalho' value='<?=$atalho;?>'>
<!--***********************************-->
</form>
</body>
<Script Language = 'JavaScript'>
function imprimir(id_export) {
	var id_pais = '<?=$id_pais;?>'
	if(id_pais == 31) {
		if(id_export == 2) {
			nova_janela('pdf/relatorio.php?id_fornecedor=<?=$id_fornecedor;?>&valor=1', 'CONSULTAR', 'F')
		}else {
			nova_janela('pdf/relatorio.php?id_fornecedor=<?=$id_fornecedor;?>&valor=0','CONSULTAR', 'F')
		}
	}else {
		nova_janela('pdf/relatorio.php?id_fornecedor=<?=$id_fornecedor;?>&valor=2','CONSULTAR', 'F')
	}
}
document.form.cmd_recalcular.onclick()

/*Aqui logo na hora em q carregar a tela vai abrir um Pop-Up do P.A. referente
a pergunta feita pelo sistema caso o usuário tenha desejado anteriormente
salvar os valores da tela de itens*/
var resposta = '<?=$resposta;?>'
if(resposta == 'true') {//True
	nova_janela('../prod_acabado_componente/prod_acabado_componente2.php?id_produto_acabado=<?=$id_produto_acabado;?>&id_pais=<?=$id_pais;?>', 'CUSTO', '', '', '', '', '550', '950', 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</html>
<?}?>