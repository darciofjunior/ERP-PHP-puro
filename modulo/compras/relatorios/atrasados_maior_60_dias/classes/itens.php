<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/compras/relatorios/atrasados_maior_60_dias/classes/consultar_contas.php', '../../../../../');

//////////////////////// Tratamentos para não furar o SQL ///////////////////////////
if(empty($cmb_representante))       $cmb_representante = '%';
if(empty($cmb_tipo_recebimento))    $cmb_tipo_recebimento = '%';
if(empty($cmb_uf))                  $cmb_uf = '%';
if(empty($cmb_ano))                 $cmb_ano = '%';
if(empty($cmb_banco))               $cmb_banco = '%';

if(!empty($chkt_somente_exportacao)) $condicao_exportacao = ' AND c.id_pais <> 31';

//Busca do último valor do dólar e do euro
$sql = "SELECT valor_dolar_dia, valor_euro_dia 
        FROM `cambios` 
        ORDER BY id_cambio DESC LIMIT 1 ";
$campos         = bancos::sql($sql);
$valor_dolar 	= $campos[0]['valor_dolar_dia'];
$valor_euro 	= $campos[0]['valor_euro_dia'];

$data_retirada_60   = data::adicionar_data_hora(date('d/m/Y'), -60);
$data_retirada_60   = data::datatodate($data_retirada_60, '-');

$condicao           = " AND cr.`data_vencimento` < '$data_retirada_60' ";

if(!empty($txt_data_emissao_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_emissao_final, 4, 1) != '-') {
            $txt_data_emissao_inicial 	= data::datatodate($txt_data_emissao_inicial, '-');
            $txt_data_emissao_final 	= data::datatodate($txt_data_emissao_final, '-');
    }
    //Aqui é para não dar erro de SQL
    $condicao1 = " AND cr.`data_emissao` BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
}

if(!empty($txt_data_vencimento_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_vencimento_final, 4, 1) != '-') {
            $txt_data_vencimento_inicial = data::datatodate($txt_data_vencimento_inicial, '-');
            $txt_data_vencimento_final = data::datatodate($txt_data_vencimento_final, '-');
    }
    //Aqui é para não dar erro de SQL
    $condicao2 = " AND cr.`data_vencimento` BETWEEN '$txt_data_vencimento_inicial' AND '$txt_data_vencimento_final' ";
}

if(!empty($txt_data_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_final, 4, 1) != '-') {
        $txt_data_inicial = data::datatodate($txt_data_inicial, '-');
        $txt_data_final = data::datatodate($txt_data_final, '-');
    }
}

if(!empty($txt_data_cadastro)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_cadastro, 4, 1) != '-') {
        $txt_data_cadastro = data::datatodate($txt_data_cadastro, '-');
    }
}

$sql = "SELECT cr.*, c.razaosocial, c.credito, t.recebimento, t.imagem, concat(tm.simbolo, '&nbsp;') AS simbolo 
        FROM `contas_receberes` cr 
        INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente AND (c.nomefantasia LIKE '%$txt_cliente%' OR c.razaosocial LIKE '%$txt_cliente%') AND c.`ativo` = '1' AND c.bairro like '%$txt_bairro%' and c.cidade like '%$txt_cidade%' and c.id_uf like '$cmb_uf' $condicao_exportacao 
        INNER JOIN `tipos_recebimentos` t ON t.id_tipo_recebimento = cr.id_tipo_recebimento 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
        WHERE cr.ativo = '1' 
        AND cr.status < '2' 
        AND cr.`id_banco` LIKE '$cmb_banco' 
        AND cr.descricao_conta LIKE '%$txt_descricao_conta_sql1%' 
        AND cr.num_conta LIKE '%$txt_numero_conta%' 
        AND SUBSTRING(cr.data_vencimento, 1, 4) LIKE '$cmb_ano' 
        AND cr.semana LIKE '%$txt_semana%' 
        AND SUBSTRING(cr.data_sys, 1, 10) LIKE '%$txt_data_cadastro%' 
        AND cr.id_tipo_recebimento LIKE '$cmb_tipo_recebimento' 
        $condicao_emp $condicao $condicao1 $condicao2 ORDER BY cr.data_vencimento ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
/*******************************************************************************************/
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.parent.location = 'consultar_contas.php?valor=1'
    </Script>
<?
	exit;
}

if($linhas > 0) {
    $dia        = date('d');
    $mes        = date('m');
    $ano        = date('Y');
    $data_hoje  = $ano.$mes.$dia;
?>
<html>
<head>
<title>.:: Itens de Contas à Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
/*Função que serve para verificar quantas contas que estão em previsão ou que já
foram pagas, se nesta retornar zero, então eu posso estar pagando as contas
apagares, do contrário ele não permite que eu venha pagar nenhuma conta*/
function bloquear(posicao) {
	var elements = document.form.elements
	posicao = eval(posicao) + 1
	if(elements[posicao].checked == true) {
		document.form.bloquear_pagamento.value = eval(document.form.bloquear_pagamento.value) + 1
	}else {
		document.form.bloquear_pagamento.value = eval(document.form.bloquear_pagamento.value) - 1
	}
}

//Função que Desmarca todos os checkboxs ...
function desmarcar_checkbox() {
	var elements = document.form.elements
	for(i = 0; i < elements.length; i++) {
		if(elements[i].type == 'checkbox') {
			elements[i].checked = false
		}
	}
}
</Script>
</head>
<body>
<form name='form'>
<table align='center' width='90%' cellspacing='0' cellpadding='0' border='1' onmouseover="total_linhas(this)">
	<tr></tr>
	<tr class="linhacabecalho" align="center">
            <td colspan='13'>
                Contas à Receber
            </td>
	</tr>
	<tr class='linhanormal' align='center'>
            <td bgcolor='#CECECE'>
                <label for="desmarcar_tudo">
                    <b>Desmarcar</b>
                </label>
                &nbsp;
                <input type="checkbox" name="chkt" title="Desmarcar Tudo" onclick="desmarcar_checkbox()" id="desmarcar_tudo" class="checkbox">
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' title="Empresa" style="cursor:help">
                    <b>E</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>N.º da<br>Conta</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>Cliente / Descrição da Conta</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>Cr</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <!--<b>Data de<br>Emissão</b>-->
                    <b>Representante</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>Data de<br>Vencimento</b>
                </font>
            </td>
            <td bgcolor='#CECECE' align="center">
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>Tipo<br>Rec.</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>Praça de<br>Recebimento</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>Valor</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>Valor Recebido</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>Valores Extras</b>
                </font>
            </td>
            <td bgcolor='#CECECE'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <b>Valor Reajustado</b>
                </font>
            </td>
	</tr>
<?
	$cont_vencer = 0;
	$cont_vencidas = 0;
	for ($i = 0; $i < $linhas; $i++) {
		//Essa variável iguala o tipo de moeda da conta à receber
		$moeda = $campos[$i]['simbolo'];
//Aqui eu limpo as variáveis para no caso de não encontrar o cliente no if abaixo e não manter os valores antigos
		$virar_link = 1;//Valor Default - Significa que é p/ virar link ...
		
		$id_cliente = $campos[$i]['id_cliente'];
		$cliente 	= $campos[$i]['razaosocial'];
		$credito 	= $campos[$i]['credito'];
		if(empty($credito)) $credito = ' ';
/***************************************************************************/
		$data_vencimento = substr($campos[$i]['data_vencimento'],0,4).substr($campos[$i]['data_vencimento'],5,2).substr($campos[$i]['data_vencimento'],8,2);
//Aqui verifica se é previsão também para poder chamar a função que bloqueia o pagamento

		if($campos[$i]['id_tipo_recebimento'] == 7) {// o 7 É O id DO PROTESTADO
			$color = "color='blue'";
			$protestado="PROTESTADO";
			$onclick = "checkbox('form', 'chkt','".$i."', '#E8E8E8');document.form.chkt.checked = false";
		}else if($campos[$i]['id_tipo_recebimento']==9) {// o 9 É O id DO CARTORIO
			$color = "color='blue'";
			$onclick = "checkbox('form', 'chkt','".$i."', '#E8E8E8');document.form.chkt.checked = false";
		}else if($campos[$i]['predatado']==1) {// este predatado serve para cheque devolvido
			$color = "color='green'";
			$onclick = "checkbox('form', 'chkt','".$i."', '#E8E8E8');document.form.chkt.checked = false";
		}else {
			if($data_vencimento < $data_hoje) {
				$color = "color='#FF0000'";
				$onclick = "checkbox('form', 'chkt','".$i."', '#E8E8E8');document.form.chkt.checked = false";
				$cont_vencidas ++;
			}else {
				$color = '';
				$onclick = "checkbox('form', 'chkt','".$i."', '#E8E8E8');document.form.chkt.checked = false";
				$cont_vencer ++;
			}

//Aqui faz esse cálculo só para verificar se é negativo e mudar a cor da linha
			$valor_recebido_conta = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
			if($campos[$i]['id_tipo_moeda'] == 2) {//Dólar
				$valor_recebido_conta = $valor_recebido_conta * $valor_dolar;
			}else if($campos[$i]['id_tipo_moeda'] == 3) {//Euro
				$valor_recebido_conta = $valor_recebido_conta * $valor_euro;
			}
//Aqui é para contas negativas
			if($valor_recebido_conta < 0) {
				$color = "color='#ff33ff'";
			}
		}
?>
	<tr class='linhanormal' onclick="<?=$onclick;?>" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align='center'>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
				<input type='checkbox' name='chkt_conta_receber[]' value="<?=$campos[$i]['id_conta_receber'];?>" onclick="<?=$onclick;?>" class="checkbox">
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
			<?
				if(genericas::nome_empresa($campos[$i]['id_empresa']) == 'ALBAFER') {
					echo '<font title="ALBAFER" style="cursor:help">A</font>';
				}else if(genericas::nome_empresa($campos[$i]['id_empresa']) == 'TOOL MASTER') {
					echo '<font title="TOOL MASTER" style="cursor:help">T</font>';
				}else if(genericas::nome_empresa($campos[$i]['id_empresa']) == 'GRUPO') {
					echo '<font title="GRUPO" style="cursor:help">G</font>';
				}else {
					echo '&nbsp';
				}
			?>
			</font>
		</td>
		<td align='left'>
		<?
			if($virar_link == 1) {
				if($campos[$i]['id_nf'] > 0) {//Exibir Dados de NFs de Saída ...
					//Aki eu busco o id_pedido_venda_item p/ visualizar os Detalhes da NF de Saída ...
					$sql = "SELECT nfsi.id_pedido_venda_item, nfs.livre_debito 
                                                FROM `nfs` 
                                                INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                                                WHERE nfs.id_nf = '".$campos[$i]['id_nf']."' LIMIT 1 ";
					$campos_pedido_venda 	= bancos::sql($sql);
					$livre_debito           = $campos_pedido_venda[0]['livre_debito'];
					//Exibo aqui detalhes da NF de Vendas de Saída ...
					$a_href = "javascript:nova_janela('../../../../classes/faturamento/faturado.php?id_pedido_venda_item=".$campos_pedido_venda[0]['id_pedido_venda_item']."', 'FATURADO', '', '', '', '', 350, 1000, 'c', 'c', '', '', 's', 's', '', '', '') ";
				}else if($campos[$i]['id_nf_outra'] > 0) {//Exibir Dados de NFs de Saída Outras ...
					$a_href = "javascript:nova_janela('../../../../faturamento/nfs_consultar/cabecalho_nfs_outras.php?id_nf_outra=".$campos[$i]['id_nf_outra']."', 'DETALHES', '', '', '', '', 700, 850, 'c', 'c', '', '', 's', 's', '', '', '')";
				}
		?>
			<a href="<?=$a_href;?>" title="Visualizar Faturamento" class="link">
		<?
			}
		?>
				<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
				<?
					if($campos[$i]['num_conta'] == '') {
						echo '&nbsp;';
					} else {
						echo $campos[$i]['num_conta'];
					}
				?>
				</font>
			<?
				if($virar_link == 1) {
			?>
				</a>
			<?
				}
			?>
		</td>
		<td align="left">
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
				<a href="javascript:nova_janela('../../../../classes/follow_ups/detalhes.php?identificacao=<?=$campos[$i]['id_conta_receber'];?>&origem=4', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Registrar Follow-Up do Cliente" class="link">
				<?
					if(!empty($cliente) && $cliente != '&nbsp;') {
						echo $cliente.' / ';
					}
					if($campos[$i]['descricao_conta'] == '') {
						echo '&nbsp;';
					}else {
						echo $campos[$i]['descricao_conta'];
					}
				?>
				</a>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
				<?=$credito;?>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
			<?
				//Verifica se tem Representante na tabela relacional de conta à receber ...
				$sql = "SELECT r.nome_fantasia 
                                        FROM `contas_receberes` cr 
                                        INNER JOIN `representantes` r ON r.id_representante = cr.id_representante 
                                        WHERE cr.`id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
				$campos_representante = bancos::sql($sql);
				if(count($campos_representante) > 0) {
                                    echo $campos_representante[0]['nome_fantasia'];
				}else {
                                    echo '&nbsp;';
				}
			?>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
				<?= data::datetodata($campos[$i]['data_vencimento'], '/');?>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
				<?$url = '../../../../../imagem/financeiro/tipos_pag_rec/'.$campos[$i]['imagem'];?>
				<img src='<?=$url;?>' width="33" height="20" border="0" title="<?=$campos[$i]['recebimento'];?>">
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
			<?
				$sql = "SELECT b.banco 
                                        FROM `contas_receberes` cr 
                                        INNER JOIN `bancos` b ON b.id_banco = cr.id_banco 
                                        WHERE cr.`id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
				$campos_bancos = bancos::sql($sql);
				if(count($campos_bancos) > 0) {
                                    echo $campos_bancos[0]['banco'];
				}else {
                                    if($campos[$i]['id_tipo_recebimento'] == 7) {
                                        echo $protestado;
                                    }else {
                                        echo "&nbsp";
                                    }
				}
			?>
			</font>
		</td>
		<td align='right'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
			<?
				if($campos[$i]['valor'] == '0.00') {
					echo '&nbsp;';
				}else {
					echo $moeda.number_format($campos[$i]['valor'], 2, ',', '.');
				}
			?>
			</font>
		</td>
		<td align='right'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
			<?
				if($campos[$i]['valor_pago']=='0.00') {
					echo '&nbsp;';
				}else {
					echo $moeda.number_format($campos[$i]['valor_pago'], 2, ',', '.');
				}
			?>
			</font>
		</td>
		<td align='right'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
			<?
				$calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
				echo number_format($calculos_conta_receber['valores_extra'], 2, ',', '.');
			?>
        		</font>
		</td>
		<td align='right'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
			<?
				$calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
				echo number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.');
			?>
			</font>
		</td>
	</tr>
<?
		$valor_receber_total+= $calculos_conta_receber['valor_reajustado'];
	}
?>
	<tr class='linhanormal' >
		<td colspan="5">
			<font size='0'>
				<b>Contas Vencidas: </b><?=$cont_vencidas;?>
				&nbsp;&nbsp;
				<b>Contas à Vencer: </b><?=$cont_vencer;?>
				&nbsp;&nbsp;
				<b>Total: </b><?=$cont_vencer + $cont_vencidas;?>
			</font>
		</td>
		<td colspan="4">
			<font size='0'>
<?
	$semana = data::numero_semana(date('d'),date('m'),date('Y'));
	$ano = date('Y');
	$sql = "SELECT dia_inicio, dia_fim 
		FROM `semanas` 
		WHERE `semana` = '$semana' 
		AND SUBSTRING(`dia_inicio`, 1, 4) = '$ano' LIMIT 1 ";
	$campos = bancos::sql($sql);
	if(count($campos) != 0) {
            $dia_inicio = data::datetodata($campos[0]['dia_inicio'], '/');
            $dia_inicio = substr($dia_inicio, 0, 6).substr($dia_inicio, 8, 2);
            $dia_fim = data::datetodata($campos[0]['dia_fim'], '/');
            $dia_fim = substr($dia_fim, 0, 6).substr($dia_fim, 8, 2);
?>
                <b>Semana: </b><?=$semana;?>
                <b> - Período: </b><?echo $dia_inicio.' a '.$dia_fim;?>
<?
	}else {
?>
				<b>Semana: </b><?=$semana;?>
<?
	}
?>
			</font>
		</td>
		<td colspan="4">
			<font size='0' color="#FF0000">
				<b>Total: </b><?='R$ '.number_format($valor_receber_total, 2, ',', '.');?>
			</font>
		</td>
	</tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<input type="hidden" name="bloquear_pagamento" value="0">
<!--Esse Hidden é controlado pelo Pop-UP de Incluir Itens da Nota Automático-->
<input type="hidden" name="recarregar">
</form>
</body>
</html>
<?
}

if(!empty($valor)) {
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[$valor];?>')
    </Script>
<?
}
?>