<?
//Tratamento com as variáveis que vem por parâmetro ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pop_up         = $_POST['pop_up'];
    $representante  = $_POST['representante'];
}else {
    $pop_up         = $_GET['pop_up'];
    $representante  = $_GET['representante'];
}

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/financeiros.php');
require('../../../lib/intermodular.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
	//Tratamento com as variáveis que vem por parâmetro ...
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $txt_nome_fantasia 	= $_POST['txt_nome_fantasia'];
            $txt_razao_social 	= $_POST['txt_razao_social'];
            $txt_codigo_cliente	= $_POST['txt_codigo_cliente'];
	}else {
            $txt_nome_fantasia 	= $_GET['txt_nome_fantasia'];
            $txt_razao_social 	= $_GET['txt_razao_social'];
            $txt_codigo_cliente	= $_GET['txt_codigo_cliente'];
	}
	if(empty($representante)) $representante = '%';
//Aqui eu listo todos os Clientes do Representante logado ...
	$sql = "SELECT DISTINCT(c.`id_cliente`), c.`cod_cliente`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, 
                c.`id_uf`, c.`endereco`, c.`cidade`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, c.`cnpj_cpf`, ct.`tipo` 
                FROM clientes c 
                INNER JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
                INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`id_representante` LIKE '$representante' 
                WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
                AND c.`razaosocial` LIKE '%$txt_razao_social%' 
                AND c.`cod_cliente` LIKE '%$txt_codigo_cliente%' 
                AND c.`ativo` = '1' ORDER BY c.`razaosocial` ";
	
	$sql_extra = "SELECT COUNT(DISTINCT(c.`id_cliente`)) AS total_registro 
                    FROM `clientes` c 
                    INNER JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
                    INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`id_representante` LIKE '$representante' 
                    WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
                    AND c.`razaosocial` LIKE '%$txt_razao_social%' 
                    AND c.`cod_cliente` LIKE '%$txt_codigo_cliente%' 
                    AND c.`ativo` = '1' ORDER BY c.`razaosocial` ";
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'relatorio_proj_vendas_pedidos_cliente_nao_compra.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>&valor=1'
        </Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Consultar Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
	<tr class="linhacabecalho" align="center">
		<td colspan='7'>
                    Consultar Cliente(s)
                    <?
                            /****************Se foi passado Representante por parâmetro****************/
                            if(!empty($representante)) {
                    ?>
                            <font color='yellow' size='-1'>
                                     - Representante: 
                            </font>
                    <?
                                    //Busca o nome do Representante que foi passado por parâmetro ...
                                    $sql = "SELECT nome_fantasia 
                                            FROM `representantes` 
                                            WHERE `id_representante` = '$representante' LIMIT 1 ";
                                    $campos_rep = bancos::sql($sql);
                                    echo $campos_rep[0]['nome_fantasia'];
                            }
                            /**************************************************************************/
                    ?>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan="2">
			Cliente
		</td>
		<td>
			Tipo de Cliente
		</td>
		<td>
			Tel Com
		</td>
		<td>
			Endereço
		</td>
		<td>
			Cidade / UF
		</td>
		<td>
			CNPJ / CPF
		</td>
	</tr>
<?
	for($i = 0; $i < $linhas; $i++) {
		$url = "window.location = 'relatorio_proj_vendas_pedidos_cliente_nao_compra.php?passo=2&representante=".$representante."&pop_up=".$pop_up."&id_cliente=".$campos[$i]['id_cliente']."' ";
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td onclick="<?=$url;?>" width="10">
                    <a href="#">
                        <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td onclick="<?=$url;?>" align="left">
			<a href="#" class="link">
				<?=$campos[$i]['cliente'];?>
			</a>
		</td>
		<td>
			<?=$campos[$i]['tipo'];?>
		</td>
		<td>
		<?
			if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
			if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
                        if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
                        if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
		?>
		</td>
		<td align="left">
		<?
			echo $campos[$i]['endereco'];
			if(!empty($campos[$i]['endereco'])) {//Daí sim printa o complemento ...
				echo ', '.$campos[$i]['num_complemento'];
			}
		?>
		</td>
		<td>
		<?
			$sql = "SELECT sigla 
                                FROM `ufs` 
                                WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
			$campos_uf 	= bancos::sql($sql);
			echo $campos[$i]['cidade'].' / '.$campos_uf[0]['sigla'];
		?>
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
	</tr>
<?
	}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'relatorio_proj_vendas_pedidos_cliente_nao_compra.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if($passo == 2) {
	//Tratamento com as variáveis que vem por parâmetro ...
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_cliente = $_POST['id_cliente'];
	}else {
            $id_cliente = $_GET['id_cliente'];
	}
	//Variáveis utilizadas mais abaixo ...
	/*
	2158 - JRC
	2209 - Imporpico
	2321 - Hierros Yacare
	2423 - Juan Bohm
	3746 - Thyssen - Esse clientes possuem uma Exceção, onde não tem compras nos anos atuais, sendo assim eu 
	retroajo com estes para o ano atual deles para 2007, p/ que daí possamos fazer as suas estastísticas ...*/
	if($id_cliente == 2158 || $id_cliente == 2209 || $id_cliente == 2321 || $id_cliente == 2423 || $id_cliente == 3746) {
		$ano_atual = 2007;
	}else {//Outros clientes, assumem o ano Atual ...
		$ano_atual = date('Y');
	}
	$dois_anos_atras 	= $ano_atual - 2;
	$meses_ano_atual 	= date('m') - 1;
	$qtde_meses = 24 + $meses_ano_atual;//Esses 24, equivale aos 2 primeiros anos ...
?>
<html>
<head>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function controlar_combo(combo_multiplo) {
	var lima_selecionada = 0
	if(combo_multiplo.value == '%') {
		for(i = 1; i < combo_multiplo.length; i++) combo_multiplo[i].selected = false
	}
	//Aqui eu verifico todas as famílias selecionadas no combo multiplo ...
	for(i = 0; i < combo_multiplo.length; i++) {
		if(combo_multiplo[i].selected == true) {
			if(combo_multiplo[i].value == '%' || combo_multiplo[i].value == 3) lima_selecionada = 1//Verifico se a opção de Lima está selecionada ...
		}
	}
	//Só irá desabilitar a combo auxiliar quando tiver apenas 1 opção habilitada e a opção de Lima marcada também ...
	if(lima_selecionada == 1) {
		document.form.cmb_divisao_lima.disabled = false
	}else {
		document.form.cmb_divisao_lima.disabled = true
	}
}

function validar() {
	if(document.form.elements['cmb_familia[]'].value == '') {
		alert('SELECIONE UMA FAMÍLIA !')
		document.form.elements['cmb_familia[]'].focus()
		return false
	}
	document.form.submit()
}
</Script>
</head>
<body onload="document.form.txt_perc_projetada.focus()">
<form name="form" method="post" action="#">
<input type='hidden' name='pop_up' value="<?=$pop_up;?>">
<input type='hidden' name='representante' value="<?=$representante;?>">
<input type='hidden' name='id_cliente' value="<?=$id_cliente;?>">
<table border="1" width='90%' align="center" cellspacing ='1' cellpadding='1'>
	<tr class="linhacabecalho" align="center" valign="top">
		<td colspan='10'>
			Visualizar os 
			<select name="cmb_qtde" title="Selecione a Qtde de Produtos mais vendidos" class="combo">
				<?
					if($_POST['cmb_qtde'] == 100) {
						$selected100 = 'selected';
					}else if($_POST['cmb_qtde'] == 200) {
						$selected200 = 'selected';
					}else if($_POST['cmb_qtde'] == 300) {
						$selected300 = 'selected';
					}else if($_POST['cmb_qtde'] == 400) {
						$selected400 = 'selected';
					}else if($_POST['cmb_qtde'] == 500) {
						$selected500 = 'selected';
					}else if($_POST['cmb_qtde'] == 600) {
						$selected600 = 'selected';
					}else if($_POST['cmb_qtde'] == 700) {
						$selected700 = 'selected';
					}else if($_POST['cmb_qtde'] == 800) {
						$selected800 = 'selected';
					}else if($_POST['cmb_qtde'] == 900) {
						$selected900 = 'selected';
					}else if($_POST['cmb_qtde'] == 1000) {
						$selected1000 = 'selected';
					}
				?>
				<option value="100" <?=$selected100;?>>100</option>
				<option value="200" <?=$selected200;?>>200</option>
				<option value="300" <?=$selected300;?>>300</option>
				<option value="400" <?=$selected400;?>>400</option>
				<option value="500" <?=$selected500;?>>500</option>
				<option value="600" <?=$selected600;?>>600</option>
				<option value="700" <?=$selected700;?>>700</option>
				<option value="800" <?=$selected800;?>>800</option>
				<option value="900" <?=$selected900;?>>900</option>
				<option value="1000" <?=$selected1000;?>>1000</option>
			</select>
			&nbsp;
			<select name='cmb_familia[]' title='Selecione uma Família' onchange='controlar_combo(this)' multiple class='combo'>
				<?
					for($i = 0; $i < count($_POST['cmb_familia']); $i++) {
						if($_POST['cmb_familia'][$i] == '%') {
							$selected = 'selected';
							$disabled_divisao = '';
						}else if($_POST['cmb_familia'][$i] == 3) {
							$selected3 = 'selected';
							$disabled_divisao = '';
						}else if($_POST['cmb_familia'][$i] == 9) {
							$selected9 = 'selected';
							$disabled_divisao = 'disabled';
						}else if($_POST['cmb_familia'][$i] == 2) {
							$selected2 = 'selected';
							$disabled_divisao = 'disabled';
						}else if($_POST['cmb_familia'][$i] == 10) {
							$selected10 = 'selected';
							$disabled_divisao = 'disabled';
						}else if($_POST['cmb_familia'][$i] == 'A') {
							$selecteda = 'selected';
							$disabled_divisao = 'disabled';
						}
					}
				?>
				<option value='%' <?=$selected_perc;?> style='color:red'>PRODUTOS (TODOS)</option>
				<option value='3' <?=$selected3;?>>LIMAS</option>
				<option value='9' <?=$selected9;?>>MACHOS</option>
				<option value='2' <?=$selected2;?>>PINOS</option>
				<option value='10' <?=$selected10;?>>BITS / BEDAMES</option>
				<option value='A' <?=$selecteda;?>>ACESSÓRIOS</option>
			</select>
			&nbsp;
			<select name='cmb_divisao_lima' title='Selecione uma Divisão Lima' class='combo' <?=$disabled_divisao;?>>
				<?
					if($_POST['cmb_divisao_lima'] == 1) {//Nova Lusa ...
						$selected_nova_lusa = 'selected';
					}else if($_POST['cmb_divisao_lima'] == 2) {//NVO ...
						$selected_nvo = 'selected';
					}else {//Qualquer Linha ...
						$selected_tudo = 'selected';
					}
				?>
				<option value='%' style='color:red' <?=$selected_tudo;?>>SELECIONE</option>
				<option value='1' <?=$selected_nova_lusa;?>>NOVA LUSA</option>
				<option value='2' <?=$selected_nvo;?>>NVO</option>
			</select>
			&nbsp;
			MAIS VENDIDOS NOS ÚLTIMOS 3 ANOS
			<br>CLIENTE: 
			<font color='black' size='-1'>
			<?
				$sql = "SELECT if(razaosocial = '', nomefantasia, razaosocial) as cliente 
						FROM `clientes` 
						WHERE `id_cliente` = '$id_cliente' limit 1 ";
				$campos_cliente = bancos::sql($sql);
				echo $campos_cliente[0]['cliente'];
			?>
			</font>
			&nbsp;-&nbsp;
			<font size='2'>
				<b>Percentagem Proj: </b>
			</font>
			<input type="text" name="txt_perc_projetada" value="<?=$_POST['txt_perc_projetada'];?>" title="Digite a Percentagem" onKeyUp="if(this.value == 0) {this.value = ''};verifica(this, 'aceita', 'numeros', '', event)" size="8" maxlength="6" class="caixadetexto">
			<font size='2'>
				<b>% </b>
			</font>
			<input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick="return validar()" class='botao'>
		</td>
	</tr>
<?
/**************************Listagem de Todos os Produtos Vendidos************************/
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		for($i = 0; $i < count($_POST['cmb_familia']); $i++) {
			if($_POST['cmb_familia'][$i] == 'A') {
				$acessorios 			= array('3', '9', '2', '10');//É tudo que não seja Limas, Machos, Pinos, Bits e Bedames ...
				$familias_selecionadas 	= substr($familias_selecionadas, 0, strlen($familias_selecionadas) - 2);
				$vetor_familias			= explode(',' , $familias_selecionadas);
				for($j = 0; $j < count($vetor_familias); $j++) {
					if(in_array($vetor_familias[$j], $acessorios)) {
						$indice_array = array_search($vetor_familias[$j], $acessorios);//Localizo o Índice do Array ...
						unset($acessorios[$indice_array]);//Apago o valor / índice do Array ...
					}
				}
			}else {
				$familias_selecionadas.= $_POST['cmb_familia'][$i].', ';
			}
		}
		if(isset($acessorios)) {//Se existir acessórios, o Sistema irá cair pelo SQL de Acessórios apenas ...
			$condicao_acessorios = " AND gpa.id_familia NOT IN (".implode(',', $acessorios).") ";
		}else {//Se não, o Sistema irá cair pelo SQL de Família ...
			$familias_selecionadas 	= substr($familias_selecionadas, 0, strlen($familias_selecionadas) - 2);
			if($familias_selecionadas == '%') {
				$condicao_familia = " AND gpa.id_familia LIKE '$familias_selecionadas' ";
			}else {
				$condicao_familia = " AND gpa.id_familia IN ($familias_selecionadas) ";
			}
		}
		
		if(isset($_POST['cmb_divisao_lima'])) {
			if($_POST['cmb_divisao_lima'] == 1) {//Nova Lusa ...
				$condicao_lima = " AND pa.referencia LIKE '%NL' ";
			}else if($_POST['cmb_divisao_lima'] == 2) {//NVO ...
				$condicao_lima = " AND pa.referencia NOT LIKE '%NL' ";
			}
		}
		
		//Aqui eu trago os X melhores PAS vendidos nos últimos 3 anos - Normais de Linha ...
		$sql = "Select distinct(pa.id_produto_acabado), pa.referencia, pa.discriminacao, pa.preco_promocional_b, 
				sum(pvi.qtde) as qtde_total_item, sum(pvi.qtde * ovi.preco_liq_final * ov.valor_dolar) as volume_item_rs 
				FROM produtos_acabados pa 
				inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
				inner join grupos_pas gpa on gpa.id_grupo_pa = ged.id_grupo_pa $condicao_familia $condicao_acessorios 
				inner join pedidos_vendas_itens pvi on pvi.id_produto_acabado = pa.id_produto_acabado 
				inner join pedidos_vendas pv on pv.id_pedido_venda = pvi.id_pedido_venda AND YEAR(pv.data_emissao) >= '$dois_anos_atras' 
				inner join orcamentos_vendas_itens ovi on ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
				inner join orcamentos_vendas ov on ovi.id_orcamento_venda = ov.id_orcamento_venda 
				WHERE pa.ativo = '1' 
				AND pa.referencia <> 'ESP' 
				AND pa.referencia NOT LIKE 'HS%' 
				AND pa.referencia NOT LIKE '%WURTH%' 
				$condicao_lima 
				AND gpa.id_familia <> '23' GROUP BY pa.id_produto_acabado HAVING volume_item_rs > '0' ORDER BY volume_item_rs DESC LIMIT $_POST[cmb_qtde]  ";
		$campos_mais_vendidos 	= bancos::sql($sql);
		$linhas_mais_vendidos	= count($campos_mais_vendidos);
?>
	<tr class="linhadestaque" align="center">
		<td>
			Referência
		</td>
		<td>
			Discriminação
		</td>
		<td>
			Qtde
		</td>
		<td>
			Volume R$
		</td>
		<td>
			Qtde Média <?=$qtde_meses;?> meses
		</td>
		<td>
			Volume Médio <?=$qtde_meses;?> meses
		</td>
		<td>
			<font title='Estoque Disponível' style='cursor:help'>
				E.D.
			</font>
		</td>
		<td>
			Qtde Proj.
		</td>
		<td>
			Pço Unit.
		</td>
		<td>
			Total
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas_mais_vendidos; $i++) {
			//Aqui eu verifico se o Cliente, tem pelo menos 1 compra desse Item mais vendido ...
			$sql = "SELECT pvi.id_pedido_venda_item 
					FROM `pedidos_vendas_itens` pvi 
					INNER JOIN `pedidos_vendas` pv on pv.id_pedido_venda = pvi.id_pedido_venda and pv.id_cliente = '$id_cliente' 
					WHERE `id_produto_acabado` = '".$campos_mais_vendidos[$i]['id_produto_acabado']."' limit 1 ";
			$campos_pedido = bancos::sql($sql);
			if(count($campos_pedido) == 0) {//Se o Cliente nunca comprou esse PA, então eu faço a oferta pra ele ...
?>
	<tr class="linhanormal" align="center">
		<td>
			<?=$campos_mais_vendidos[$i]['referencia'];?>
		</td>
		<td align="left">
			<?=$campos_mais_vendidos[$i]['discriminacao'];?>
		</td>
		<td>
			<?=intval($campos_mais_vendidos[$i]['qtde_total_item']);?>
		</td>
		<td align="right">
			<?=number_format($campos_mais_vendidos[$i]['volume_item_rs'], 2, ',', '.');?>
		</td>
		<td>
		<?
			$qtde_mensal = ceil(intval($campos_mais_vendidos[$i]['qtde_total_item']) / $qtde_meses);
			echo $qtde_mensal;
		?>
		</td>
		<td>
			<?=number_format($campos_mais_vendidos[$i]['volume_item_rs'] / $qtde_meses, 2, ',', '.');?>
		</td>
		<td>
		<?
			//Busca a Quantidade de Estoque Disponível do PA ...
			$sql = "SELECT qtde_disponivel 
					FROM `estoques_acabados` 
					WHERE `id_produto_acabado` = '".$campos_mais_vendidos[$i]['id_produto_acabado']."' LIMIT 1 ";
			$campos_estoque = bancos::sql($sql);
			$font = ($campos_estoque[0]['qtde_disponivel'] > 0) ? '<font color="darkblue"><b>' : '<font color="red"><b>';
			echo $font.number_format($campos_estoque[0]['qtde_disponivel'], 0, ',', '.');
		?>		
		</td>
		<td bgcolor='#CECECE'>
			<?
				$qtde_projetada = ceil($qtde_mensal * $_POST['txt_perc_projetada'] / 100);
				echo $qtde_projetada;
			?>
		</td>
		<td bgcolor='#CECECE' align="right">
		<?
			//Se não tem Preço ...
			if($campos_mais_vendidos[$i]['preco_promocional_b'] == 0) {
				//Puxo o Preço A do SU - Supercut para o HS - HardSteel ...
				if(substr_count(strtoupper($campos_mais_vendidos[$i]['referencia']), 'HS')) {
					$sql = "SELECT preco_promocional 
							FROM `produtos_acabados` 
							WHERE referencia = '".str_replace('HS', 'SU', $campos_mais_vendidos[$i]['referencia'])."' LIMIT 1 ";
					$campos_preco_a = bancos::sql($sql);
					$preco_unitario = round($campos_preco_a[0]['preco_promocional'] + ($campos_preco_a[0]['preco_promocional'] * 0.1), 2);//Preço c/ + 10 %
				//Puxo o Preço B da NVO - Nova Lusa para o NVO ...
				}else if(substr_count(strtoupper($campos_mais_vendidos[$i]['referencia']), 'NL')) {
					$sql = "SELECT preco_promocional_b 
							FROM `produtos_acabados` 
							WHERE referencia = 'L".strtok($campos_mais_vendidos[$i]['referencia'], 'NL')."' LIMIT 1 ";
					$campos_preco_b = bancos::sql($sql);
					$preco_unitario = round($campos_preco_b[0]['preco_promocional_b'] - ($campos_preco_b[0]['preco_promocional_b'] * 0.1), 2);//Preço c/ + 10 %
				}
				echo '*';
			}else {
				$preco_unitario = $campos_mais_vendidos[$i]['preco_promocional_b'];
			}
			echo number_format($preco_unitario, 2, ',', '.');
		?>
		</td>
		<td bgcolor='#CECECE' align="right">
		<?
			$projetado_item = $qtde_projetada * $preco_unitario;
			echo number_format($projetado_item, 2, ',', '.');
		?>
		</td>
	</tr>
<?
				$qtde_total_geral+= $campos_mais_vendidos[$i]['qtde_total_item'];
				$volume_total_geral+= $campos_mais_vendidos[$i]['volume_item_rs'];
				$qtde_media_total_geral+= ceil(intval($campos_mais_vendidos[$i]['qtde_total_item']) / $qtde_meses);
				$volume_medio_total_geral+= $campos_mais_vendidos[$i]['volume_item_rs'] / $qtde_meses;
				$qtde_projetada_total_geral+= $qtde_projetada;
				$projetado_item_total_geral+= $projetado_item;
				$contador_pas++;//Aqui eu vou contando todos os PAs que foram exibidos para o usuário na Tela ...
			}
		}
?>
	<tr class="linhadestaque" align="right">
		<td colspan="2">
			<font size='2' color='black'>
				<b>TOTAL(IS) GERAL(IS) => 
			</font>
		</td>
		<td align="center">
			<?=number_format($qtde_total_geral, 0, ',', '.');?>
		</td>
		<td>
			R$ <?=number_format($volume_total_geral, 2, ',', '.');?>
		</td>
		<td align="center">
			<?=number_format($qtde_media_total_geral, 0, ',', '.');?>
		</td>
		<td>
			R$ <?=number_format($volume_medio_total_geral, 2, ',', '.');?>
		</td>
		<td>
			&nbsp;
		</td>
		<td align="center">
			<?=number_format($qtde_projetada_total_geral, 0, ',', '.');?>
		</td>
		<td>
			&nbsp;
		</td>
		<td>
			R$ <?=number_format($projetado_item_total_geral, 2, ',', '.');?>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='10'>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'relatorio_proj_vendas_pedidos_cliente_nao_compra.php<?=$parametro;?>&passo=1'" class='botao'>
			<?
				if(!empty($_POST['txt_perc_projetada'])) {
			?>
				<input type='button' name='cmd_imprimir_estudo' value='Imprimir Estudo' title='Imprimir Estudo' onclick="window.print()" style="color:blue" class='botao'>
				<input type='button' name='cmd_imprimir_carta' value='Imprimir Carta de Projeção' title='Imprimir Carta de Projeção' onclick="nova_janela('imprimir_carta_pedidos_cliente_nao_compra.php?representante=<?=$representante;?>&id_cliente=<?=$id_cliente;?>&txt_perc_projetada=<?=$_POST['txt_perc_projetada'];?>&cmb_qtde=<?=$_POST['cmb_qtde'];?>&cmb_familia=<?=implode(',', $_POST['cmb_familia']);?>&cmb_divisao_lima=<?=$_POST['cmb_divisao_lima'];?>&contador_pas=<?=$contador_pas;?>', 'CONSULTAR', 'F')" style="color:red" class='botao'>
			<?
				}
			?>
		</td>
	</tr>
</table>
<font size='-2' face='verdana, arial, helvetica, sans-serif' class='confirmacao'>
    <center>
        <br/>Total de Registro(s): <?=$contador_pas;?>
    </center>
</font>
<?
	}
?>
</form>
</body>
</html>
<pre>
<font color='red' size='4'>
<b>Aparece somente o(s) PA(s) que nunca foram Comprado(s) pelo Cliente.</b>
</font>
</pre>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_razao_social.focus()'>
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='representante' value="<?=$representante;?>">
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
            <td colspan='2'>
                    <b><?=$mensagem[$valor];?></b>
            </td>
    </tr>
    <tr class='linhacabecalho'>
            <td colspan="2" align='center'>
                Consultar Cliente(s) 
                    <?
                            /****************Se foi passado Representante por parâmetro****************/
                            if(!empty($representante)) {
                    ?>
                            <font color='yellow' size='-1'>
                                     - Representante: 
                            </font>
                    <?
                                    //Busca o nome do Representante que foi passado por parâmetro ...
                                    $sql = "SELECT nome_fantasia 
                                            FROM `representantes` 
                                            WHERE `id_representante` = '$representante' LIMIT 1 ";
                                    $campos_rep = bancos::sql($sql);
                                    echo $campos_rep[0]['nome_fantasia'];
                            }
                            /**************************************************************************/
                    ?>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Razão Social
            </td>
            <td>
                    <input type="text" name="txt_razao_social" title="Digite a Razão Social" class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Nome Fantasia
            </td>
            <td>
                    <input type="text" name="txt_nome_fantasia" title="Digite a Nome Fantasia" class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Código do Cliente
            </td>
            <td>
                    <input type="text" name="txt_codigo_cliente" title="Digite o Código do Cliente" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class="caixadetexto">
            </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class="botao">
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.reset()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>