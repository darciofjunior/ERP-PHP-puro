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
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>NÃO EXISTE(M) COMPRA(S) PARA ESSA FAMÍLIA.</font>";

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
	$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'relatorio_proj_vendas_pedidos_cliente_compra.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>&valor=1'
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
                        <font color='black' size='-1'>
                                 - Representante: 
                        </font>
                    <?
                                //Busca o nome do Representante que foi passado por parâmetro ...
                                $sql = "Select nome_fantasia 
                                        from `representantes` 
                                        where id_representante = '$representante' limit 1 ";
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
            $url = "javascript:window.location = 'relatorio_proj_vendas_pedidos_cliente_compra.php?passo=2&representante=".$representante."&pop_up=".$pop_up."&id_cliente=".$campos[$i]['id_cliente']."' ";
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
		<td onclick="<?=$url;?>" width='10'>
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
			$sql = "Select sigla 
                                from `ufs` 
                                where `id_uf` = '".$campos[$i]['id_uf']."' limit 1 ";
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
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'relatorio_proj_vendas_pedidos_cliente_compra.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class="botao">
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
?>
<html>
<head>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function controlar_combo(combo_multiplo) {
	var lima_selecionada = 0
	if(combo_multiplo.value == '') {
		for(i = 1; i < combo_multiplo.length; i++) combo_multiplo[i].selected = false
	}
}

function validar() {
	if(document.form.txt_perc_projetada.value == '') {
		alert('DIGITE UMA PERCENTAGEM !')
		document.form.txt_perc_projetada.focus()
		return false
	}
	return limpeza_moeda('form', 'txt_perc_projetada, ')
}
</Script>
</head>
<body onload="document.form.txt_perc_projetada.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=2';?>">
<input type='hidden' name='pop_up' value="<?=$pop_up;?>">
<input type='hidden' name='representante' value="<?=$representante;?>">
<input type='hidden' name='id_cliente' value="<?=$id_cliente;?>">
<?
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
	$qtde_meses_dinamic = 24 + $meses_ano_atual;//Esses 24, equivale aos 2 primeiros anos ...
	
	if($_POST['cmb_familia'][0] == '') {
		$familias_selecionadas = '%';
		$selected = 'selected';
	}else {
		for($i = 0; $i < count($_POST['cmb_familia']); $i++) {
			if($_POST['cmb_familia'][$i] != '') $familias_selecionadas.= $_POST['cmb_familia'][$i].', ';
		}
		$familias_selecionadas 	= substr($familias_selecionadas, 0, strlen($familias_selecionadas) - 2);
	}

	if($familias_selecionadas == '%') {
		$condicao_familia = " AND f.id_familia LIKE '$familias_selecionadas' ";
	}else {
		$condicao_familia = " AND f.id_familia IN ($familias_selecionadas) ";
	}
	$cmb_familia_filtro = (!empty($_POST['cmb_familia'])) ? $_POST['cmb_familia'] : '%';
/************************Produtos Vendidos para o Cliente nos últimos 3 anos**********************/
	$sql = "SELECT SUM(nfsi.qtde - nfsi.qtde_devolvida) AS qtde_anual, c.razaosocial cliente, ged.id_empresa_divisao, 
                ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac, pa.id_produto_acabado, pa.referencia, 
                pa.discriminacao, pa.preco_unitario, nfs.data_emissao, YEAR(nfs.data_emissao) AS ano 
                FROM `clientes` c 
                INNER JOIN `nfs` ON nfs.id_cliente = c.id_cliente 
                INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = nfsi.id_produto_acabado 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                INNER JOIN `familias` f ON f.id_familia = gpa.id_familia $condicao_familia 
                WHERE YEAR(nfs.`data_emissao`) >= '$dois_anos_atras' 
                AND nfs.`id_cliente` = '$id_cliente' 
                GROUP BY pa.id_produto_acabado, ano ORDER BY pa.discriminacao, ano ";
	$campos	= bancos::sql($sql);
	$linhas	= count($campos);
?>
<table border="1" width='90%' align="center" cellspacing ='1' cellpadding='1'>
	<tr class="linhacabecalho" align="center">
		<td colspan='14'>
			PRODUTOS COMPRADOS PELO CLIENTE: 
			<font color='black' size='-1'>
				<?=$campos[0]['cliente'];?>
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td colspan='14'>
			<font size='2' color='black'>
				<b>Família: </b>
			</font>
			<select name='cmb_familia[]' title='Selecione uma Família' onchange='controlar_combo(this)' multiple size='5' class='combo'>
				<option value="" style="color:red" <?=$selected;?>>SELECIONE</option>
			<?
				$sql = "SELECT id_familia, UPPER(nome) as nome 
						FROM `familias` 
						WHERE ativo = '1' ORDER BY nome ";
				$campos_familia = bancos::sql($sql);
				$linhas_familia = count($campos_familia);
				for($i = 0; $i < $linhas_familia; $i++) {
					$selected = '';//Limpo a variável para não herdar valor do Loop Anterior ...
					if(in_array($campos_familia[$i]['id_familia'], $_POST['cmb_familia'])) $selected = 'selected';
			
			?>
				<option value="<?=$campos_familia[$i]['id_familia'];?>" <?=$selected;?>><?=$campos_familia[$i]['nome'];?></option>
			<?
				}
			?>
			</select>
			&nbsp;
			<font size='2' color='black'>
				<b>Meses: </b>
			</font>
			<select name="cmb_qtde_meses" title="Selecione a Qtde de Meses" onchange="limpeza_moeda('form', 'txt_perc_projetada, ');document.form.submit()" class="combo">
				<?
					if(empty($_POST['cmb_qtde_meses'])) {//Para não dar erro de 1ª quando carregar a Tela ...
						$selectedvariavel = 'selected';
						$cmb_qtde_meses = 34;
					}else {
						if($_POST['cmb_qtde_meses'] == 12) {
							$selected12 = 'selected';
						}else if($_POST['cmb_qtde_meses'] == 24) {
							$selected24 = 'selected';
						}else if($_POST['cmb_qtde_meses'] == 36) {
							$selected36 = 'selected';
						}else {
							$selectedvariavel = 'selected';
						}
					}
				?>
				<option value='12' <?=$selected12;?>>12 (1 ANO)</option>
				<option value='24' <?=$selected24;?>>24 (2 ANOS)</option>
				<option value='36' <?=$selected36;?>>36 (3 ANOS)</option>
				<option value='<?=$qtde_meses_dinamic;?>' <?=$selectedvariavel;?>><?=$qtde_meses_dinamic;?> (MESES)</option>
			</select>
			&nbsp;
			<font size='2' color='black'>
				<b>Perc. Projetada: </b>
			</font>
			<input type="text" name="txt_perc_projetada" value="<?if(!empty($_POST['txt_perc_projetada'])) echo number_format($_POST['txt_perc_projetada'], 0, ',', '.');?>" title="Digite o Desconto % Extra" onKeyUp="if(this.value == 0) {this.value = ''};verifica(this, 'aceita', 'numeros', '', event)" size="8" maxlength="6" class="caixadetexto">
			<font size='2' color='black'>
				<b>% </b>
			</font>
			<input type='submit' name='cmd_calcular' value='Calcular' title='Calcular' onclick='return validar()' class='botao'>
		</td>
	</tr>
<?
	if($linhas > 0) {//Existe pelo menos uma Compra da Família ou Famílias ...
?>
	<tr class="linhadestaque" align="center">
		<td>Ref</td>
		<td>Discriminação</td>
		<td><?=$ano_atual - 2;?></td>
		<td><?=$ano_atual - 1;?></td>
		<td><?=$ano_atual;?></td>
		<td>Média <?=$cmb_qtde_meses;?> meses</td>
		<td>Proj. 3 mês</td>
		<td>Valor Unit R$</td>
		<td>Total Trimestre</td>
		<td align="right">
			c/+ <?=number_format($_POST['txt_perc_projetada'], 0, ',', '.');?> % Acrésc.
		</td>
		<td>1º Mês</td>
		<td>2º Mês</td>
		<td>3º Mês</td>
		<td>Total Trim. Proj.</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
			if($id_pa_antigo != $campos[$i]['id_produto_acabado']) {
				if(($campos[$i]['id_produto_acabado'] == $campos[$i + 1]['id_produto_acabado']) && ($campos[$i]['id_produto_acabado'] == $campos[$i + 2]['id_produto_acabado'])) {
					$qtde_anos_exibir = 3; 
				}else if(($campos[$i]['id_produto_acabado'] == $campos[$i + 1]['id_produto_acabado']) && ($campos[$i]['id_produto_acabado'] != $campos[$i + 2]['id_produto_acabado'])) {
					$qtde_anos_exibir = 2;
				}else {
					$qtde_anos_exibir = 1;
				}
				if($qtde_anos_exibir == 3) {
					if($campos[$i]['ano'] == $ano_atual - 2) {
						$qtde_2anos_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
						$qtde_1ano_atras = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
						$qtde_ano_atual = number_format($campos[$i + 2]['qtde_anual'], 0, '', '');
					}else if($campos[$i]['ano'] == $ano_atual - 1) {
						$qtde_2anos_atras = number_format(0, 0, '', '');
						$qtde_1ano_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
						$qtde_ano_atual = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
					}else if($campos[$i]['ano'] == $ano_atual) {
						$qtde_2anos_atras = number_format(0, 0, '', '');
						$qtde_1ano_atras = number_format(0, 0, '', '');
						$qtde_ano_atual = number_format($campos[$i]['qtde_anual'], 0, '', '');
					}
				}else if($qtde_anos_exibir == 2) {
					if($campos[$i]['ano'] == $ano_atual - 2 && $campos[$i + 1]['ano'] == $ano_atual - 1) {
						$qtde_2anos_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
						$qtde_1ano_atras = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
						$qtde_ano_atual = number_format(0, 0, '', '');
					}else if($campos[$i]['ano'] == $ano_atual - 2 && $campos[$i + 1]['ano'] == $ano_atual) {
						$qtde_2anos_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
						$qtde_1ano_atras = number_format(0, 0, '', '');
						$qtde_ano_atual = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
					}else if($campos[$i]['ano'] == $ano_atual - 1 && $campos[$i + 1]['ano'] == $ano_atual) {
						$qtde_2anos_atras = number_format(0, 0, '', '');
						$qtde_1ano_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
						$qtde_ano_atual = number_format($campos[$i + 1]['qtde_anual'], 0, '', '');
					}
				}else {
					if($campos[$i]['ano'] == $ano_atual - 2) {
						$qtde_2anos_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
						$qtde_1ano_atras = number_format(0, 0, '', '');
						$qtde_ano_atual = number_format(0, 0, '', '');
					}else if($campos[$i]['ano'] == $ano_atual - 1) {
						$qtde_2anos_atras = number_format(0, 0, '', '');
						$qtde_1ano_atras = number_format($campos[$i]['qtde_anual'], 0, '', '');
						$qtde_ano_atual = number_format(0, 0, '', '');
					}else if($campos[$i]['ano'] == $ano_atual) {
						$qtde_2anos_atras = number_format(0, 0, '', '');
						$qtde_1ano_atras = number_format(0, 0, '', '');
						$qtde_ano_atual = number_format($campos[$i]['qtde_anual'], 0, '', '');
					}
				}
				$qtde_projetada_por_mes = round(($qtde_2anos_atras + $qtde_1ano_atras + $qtde_ano_atual) / $cmb_qtde_meses, 2);
				$qtde_projetada_trimestre = ceil($qtde_projetada_por_mes * 3);
				
				//A nova proposta passa a ser o quero o Cliente comprava antes + a Nova % ...
				$nova_proposta = ceil($qtde_projetada_trimestre * $_POST['txt_perc_projetada'] / 100);
				$nova_proposta_por_3meses = ($nova_proposta / 3);
				
				$mes1 = ceil($nova_proposta_por_3meses);
				$mes2 = ceil($nova_proposta_por_3meses);
				$mes3 = ceil($nova_proposta_por_3meses);
				
				if($mes1 + $mes2 + $mes3 != $nova_proposta) $mes2-= 1;
				if($mes1 + $mes2 + $mes3 != $nova_proposta) $mes3-= 1;
?>
	<tr class="linhanormal" align="center">
		<td>
			<?=$campos[$i]['referencia'];?>
		</td>
		<td align="left">
			<?=$campos[$i]['discriminacao'];?>
		</td>
		<td>
			<?=$qtde_2anos_atras;?>
		</td>
		<td>
			<?=$qtde_1ano_atras;?>
		</td>
		<td>
			<?=$qtde_ano_atual;?>
		</td>
		<td>
			<?=number_format($qtde_projetada_por_mes, 2, ',', '.');?>
		</td>
		<td bgcolor='#CECECE' align="right">
			<?=intval($qtde_projetada_trimestre);?>
		</td>
		<td bgcolor='#CECECE' align="right">
		<?
			//Busca o Último Preço negociado em Pedido no ano Anterior ...
			$sql = "SELECT pvi.preco_liq_final 
                                FROM `pedidos_vendas_itens` pvi 
                                INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND YEAR(pv.data_emissao) = '".(date('Y') - 1)."' AND pv.id_cliente = '$id_cliente' 
                                WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ORDER BY id_pedido_venda_item DESC LIMIT 1 ";
			$campos_preco_unitario = bancos::sql($sql);
			if(count($campos_preco_unitario) == 1) {//Se existir uma Compra no ano Anterior ...
				$preco_unitario = $campos_preco_unitario[0]['preco_liq_final'];
				$marcacao		= '*';
			}else {//Se não existir eu pego o Preço de Lista cheio, dando a este todos os descontos necessários 
				//Aqui pego o representante e o desconto do cliente para o calculo ...
				$sql = "SELECT desconto_cliente 
                                        FROM `clientes_vs_representantes` 
                                        WHERE `id_cliente` = '$id_cliente' 
                                        AND `id_empresa_divisao` = '".$campos[$i]['id_empresa_divisao']."' LIMIT 1 ";
				$campos_desconto = bancos::sql($sql);
				if(count($campos_desconto) > 0) $desconto_cliente = (strtoupper($campos[$i]['referencia']) == 'ESP') ? 0 : $campos_desconto[0]['desconto_cliente'];
				$preco_unitario = $campos[$i]['preco_unitario'] * (1 - $campos[$i]['desc_base_a_nac'] / 100) * (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 - $desconto_cliente / 100);
				$marcacao	= '';
			}
			echo $marcacao.number_format($preco_unitario, 2, ',', '.');
		?>
		</td>
		<td bgcolor='#CECECE' align="right">
			<?=number_format($qtde_projetada_trimestre * $preco_unitario, 2, ',', '.');?>
		</td>
		<td align="right">
			<?=intval($nova_proposta);?>
		</td>
		<td>
			<input type="text" name="txt_mes1" value="<?=$mes1;?>" title="1° Mês" size="5" maxlength="3" class="caixadetexto" disabled>
		</td>
		<td>
			<input type="text" name="txt_mes2" value="<?=$mes2;?>" title="2° Mês" size="5" maxlength="3" class="caixadetexto" disabled>
		</td>
		<td>
			<input type="text" name="txt_mes3" value="<?=$mes3;?>" title="3° Mês" size="5" maxlength="3" class="caixadetexto" disabled>
		</td>
		<td align="right">
			<?=number_format(intval($nova_proposta) * $preco_unitario, 2, ',', '.');?>
		</td>
	</tr>
<?
				$sub_total_atual+= $qtde_projetada_trimestre * $preco_unitario;
				$sub_total_projetado+= intval($nova_proposta) * $preco_unitario;
			}
			$id_pa_antigo = $campos[$i]['id_produto_acabado'];
		}
?>
	<tr class="linhadestaque" align="right">
		<td colspan='9' align='right'>
			<font size='2' color='black'>
				<b><?=$campos_fat[$i]['razaosocial'];?> => </b>
				<b>TOTAL PROJ R$ <?=number_format($sub_total_atual, 2, ',', '.');?></b>
			</font>
		</td>
		<td colspan='5' align='right'>
			<font size='2' color='black'>
				<b><?=$campos_fat[$i]['razaosocial'];?> => </b>
				<b>TOTAL PROJ C/ ACRÉS R$ <?=number_format($sub_total_projetado, 2, ',', '.');?></b>
			</font>
		</td>
	</tr>
<?
		$total_atual+= $sub_total_atual;
		$total_projetado+= $sub_total_projetado;
?>
	<tr class="linhacabecalho" align="center">
		<td colspan='4'>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'relatorio_proj_vendas_pedidos_cliente_compra.php<?=$parametro;?>'" class='botao'>
		<?
			if(!empty($_POST['txt_perc_projetada'])) {
		?>
			<input type='button' name='cmd_imprimir_estudo' value='Imprimir Estudo' title='Imprimir Estudo' onclick="window.print()" style="color:blue" class='botao'>
			<input type='button' name='cmd_imprimir_carta' value='Imprimir Carta de Projeção Trimestral' title='Imprimir Carta de Projeção Trimestral' onclick="nova_janela('imprimir_carta_pedidos_cliente_compra.php?representante=<?=$representante;?>&id_cliente=<?=$id_cliente;?>&cmb_qtde_meses=<?=$_POST['cmb_qtde_meses'];?>&cmb_familia=<?=implode(',' ,$_POST['cmb_familia']);?>&txt_perc_projetada=<?=$_POST['txt_perc_projetada'];?>', 'CONSULTAR', 'F')" style="color:red" class='botao'>
		<?
			}
		?>
		</td>
		<td colspan='5' align='right'>
			<font size='2' color='black'>
				<b>TOTAL GERAL PROJ R$ <?=number_format($total_atual, 2, ',', '.');?></b>
			</font>
		</td>
		<td colspan='5' align='right'>
			<font size='2' color='black'>
				<b>TOTAL GERAL PROJ C/ ACRÉS R$ <?=number_format($total_projetado, 2, ',', '.');?></b>
			</font>
		</td>
	</tr>
	<?
		/**********************Programado dentro do Trimestre**********************/
		//Busca do Total de Pedidos Programados do Cliente dentro do Trimestre do Ano Atual ...
		if(date('m') <= 3) {//Significa que o Mês é pertinente ao 1º Trimestre ...
			$periodo_programado_trimestre = " AND pv.`faturar_em` BETWEEN '".$ano_atual."-01-01' AND '".$ano_atual."-03-31' ";
		}else if(date('m') <= 6) {//Significa que o Mês é pertinente ao 2º Trimestre ...
			$periodo_programado_trimestre = " AND pv.`faturar_em` BETWEEN '".$ano_atual."-04-01' AND '".$ano_atual."-06-30' ";
		}else if(date('m') <= 9) {//Significa que o Mês é pertinente ao 3º Trimestre ...
			$periodo_programado_trimestre = " AND pv.`faturar_em` BETWEEN '".$ano_atual."-07-01' AND '".$ano_atual."-09-30' ";
		}else {//Significa que o Mês é pertinente ao 4º Trimestre ...
			$periodo_programado_trimestre = " AND pv.`faturar_em` BETWEEN '".$ano_atual."-10-01' AND '".$ano_atual."-12-31' ";
		}
		$sql = "SELECT SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total_programado_trimestre 
                        FROM `pedidos_vendas` pv 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                        WHERE pv.`id_cliente` = '$id_cliente' 
                        $periodo_programado_trimestre ";
		$campos = bancos::sql($sql);
		if($campos[0]['total_programado_trimestre'] > 0) {
?>
	<tr class="linhadestaque">
		<td colspan='14'>
			<font size='2' color='black'>
				<blink>
					<b>PEDIDO(S) PROGRAMADO(S) DENTRO DESSE TRIMESTRE => R$ <?=number_format($campos[0]['total_programado_trimestre'], 2, ',', '.');?></b>
				</blink>
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho">
		<td colspan='14'>
			<font size='2' color='black'>
			<?
				$nova_projecao_c_acrescimo = $total_projetado - $campos[0]['total_programado_trimestre'];
				if($nova_projecao_c_acrescimo < 0) $nova_projecao_c_acrescimo = 0;
			?>
				<b>NOVA PROJ C/ ACRÉS => <?=number_format($nova_projecao_c_acrescimo, 2, ',', '.');?></b>
			</font>
		</td>
	</tr>
<?	
		}
		/**************************************************************************/
?>
</table>
<?
	}else {//Significa que não existe nenhuma Compra do Cliente p/ a Família selecionada ...
?>
<table width='90%' align="center">
    <tr align='center'>
        <td>
            <b><?=$mensagem[2];?></b>
        </td>
    </tr>
</table>
<?
	}
?>
</form>
</body>
</html>
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