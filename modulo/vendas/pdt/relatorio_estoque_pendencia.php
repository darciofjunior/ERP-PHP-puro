<?
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pop_up                         = $_POST['pop_up'];
    $cmb_representante              = $_POST['cmb_representante'];
    $cmb_empresa_divisao            = $_POST['cmb_empresa_divisao'];
    $txt_referencia_discriminacao   = $_POST['txt_referencia_discriminacao'];
    $chkt_somente_itens_com_vale    = $_POST['chkt_somente_itens_com_vale'];
}else {
    $pop_up                         = $_GET['pop_up'];
    $cmb_representante              = $_GET['cmb_representante']; 
    $cmb_empresa_divisao            = $_GET['cmb_empresa_divisao'];
    $txt_referencia_discriminacao   = $_GET['txt_referencia_discriminacao'];
    $chkt_somente_itens_com_vale    = $_GET['chkt_somente_itens_com_vale'];
}

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
session_start('funcionarios');
?>
<html>
<head>
<title>.:: Relatório de Estoque vs Pendência ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    //Somente se a opção Itens Com Vale "não estiver marcada" que eu forço o preenchimento da combo Vendedor ou Empresa Divisão ...
    if(!document.form.chkt_somente_itens_com_vale.checked) {
        if(document.form.cmb_representante.value == '' && document.form.cmb_empresa_divisao.value == '') {
            alert('SELECIONE UM REPRESENTANTE OU UMA EMPRESA DIVISÃO !')
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_referencia_discriminacao.focus()'>
<form name='form' action='' method='post' onsubmit='return validar()'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='95%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='16'>
            <font color='yellow'>
                <b>Vendedor: </b>
            </font>
        <?
//Verifico se o Vendedor foi passado por Parâmetro ...
                if(!empty($representante)) {
                    $sql = "SELECT nome_fantasia 
                            FROM `representantes` 
                            WHERE `id_representante` = '$representante' LIMIT 1 ";
                    $campos_representante = bancos::sql($sql);
                    echo $campos_representante[0]['nome_fantasia'];
//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
                }else {
        ?>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
        <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, $cmb_representante);
        ?>
            </select>
            &nbsp;
            <font color='yellow'>
                <b>Empresa Divisão: </b>
            </font>
            <select name='cmb_empresa_divisao' title='Selecione a Empresa Divisão' class='combo'>
            <?
                $sql = "SELECT `id_empresa_divisao`, `razaosocial` 
                        FROM `empresas_divisoes` 
                        WHERE `ativo` = '1' ORDER BY `razaosocial` ";
                echo combos::combo($sql, $cmb_empresa_divisao);
            ?>
            </select>
            &nbsp;
            <font color='yellow'>
                <b>Referência / Discriminação: </b>
            </font>
            <input type='text' name='txt_referencia_discriminacao' value='<?=$txt_referencia_discriminacao;?>' size='20' class='caixadetexto'>
            <?
                if($chkt_somente_itens_com_vale == 'S') $checked = 'checked';
            ?>
            <input type='checkbox' name='chkt_somente_itens_com_vale' value='S' id='chkt_somente_itens_com_vale' class='checkbox' <?=$checked;?>>
            <label for='chkt_somente_itens_com_vale'>
                    Somente Itens com Vale
            </label>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
<?
    $data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
/*Se foi passado um parâmetro de Representante ou foi selecionado algum na Combo, então 
eu realizo o SQL abaixo ...*/
    if(!empty($representante) || !empty($cmb_representante) || !empty($cmb_empresa_divisao) || $chkt_somente_itens_com_vale == 'S') {
        if(empty($representante))               $representante  = '%';
        if($chkt_somente_itens_com_vale == 'S') $condicao_somente_itens_com_vale = " AND `vale` > '0' ";
        $condicao_representante                 = (!empty($cmb_representante)) ? " AND `id_representante` LIKE '$cmb_representante' ": " AND `id_representante` LIKE '$representante' ";
        if(!empty($cmb_empresa_divisao))        $inner_join_empresa_divisao  = " INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` LIKE '$cmb_empresa_divisao' ";
        
//Busco todas as Pendências de acordo com o Filtro feito pelo Usuário ...
        $sql = "SELECT id_pedido_venda_item 
                FROM `pedidos_vendas_itens` 
                WHERE `status` < '2' 
                $condicao_somente_itens_com_vale 
                $condicao_representante ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {//Não encontrou nenhuma Pendência ...
            $vetor_pedido_venda_item[] = 0;
        }else {//Encontrou pelo menos 1 Pendência ...
            for($i = 0; $i < $linhas; $i++) $vetor_pedido_venda_item[] = $campos[$i]['id_pedido_venda_item'];
        }
//Busco todos os Itens de Pedidos junto do Cliente que estão em Pendências do Representante que foi passado por parâmetro ...
        $sql = "SELECT IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, c.`credito`, 
                pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, pa.`operacao`, 
                pa.`peso_unitario`, pa.`observacao`, pv.`id_pedido_venda`, pv.`id_cliente`, pv.`id_empresa`, 
                pv.`faturar_em`, pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, 
                pvi.`id_pedido_venda_item`, pvi.`qtde`, pvi.`vale`, pvi.`qtde_pendente`, pvi.`preco_liq_final`, 
                pvi.`qtde_faturada`, pvi.`status` AS status_item, r.`nome_fantasia` 
                FROM `clientes` c 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` AND (pa.`referencia` LIKE '%$txt_referencia_discriminacao%' OR pa.`discriminacao` LIKE '%$txt_referencia_discriminacao%') 
                $inner_join_empresa_divisao 
                INNER JOIN `representantes` r ON r.id_representante = pvi.id_representante 
                WHERE pvi.`id_pedido_venda_item` IN (".implode(',', $vetor_pedido_venda_item).") ORDER BY cliente, pv.id_empresa, pv.id_pedido_venda ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
//Verifica se tem pelo menos um item no Pedido que está Pendente
?>
	<tr class='linhacabecalho' align='center'>
            <td colspan='16'>
                Relatório de Estoque vs Pendência
            </td>
	</tr>
	<tr class='linhadestaque' align='center'>
            <td rowspan='2'>
                <b title='Cliente'>Cliente</b>
            </td>
            <td rowspan='2'>
                <font title="Crédito" style="cursor:help">
                    Cr
                </font>
            </td>
            <td colspan='6'><b>Quantidade</b></td>
            <td rowspan='2'><font title="Produto">Produto</b></td>
            <td rowspan='2'>
                <font title="Preço Líquido Final <?=$tipo_moeda;?>">P. L.<br>
                Final <?=$tipo_moeda;?>
            </td>
            <td rowspan='2'>
                <font title='IPI %'>IPI<br>%</b>
            </td>
            <td rowspan='2'>
                <font title='Total <?=$tipo_moeda;?> Lote'>
                    Total<br/><?=$tipo_moeda;?> Lote
            </td>
            <td rowspan='2'>
                <font title='Empresa / Tipo de Nota / Prazo de Pagamento'>Emp / Tp Nota<br> / Prazo Pgto
            </td>
            <td rowspan='2'>
                Faturar em
            </td>
            <td rowspan='2'>
                <font title='Representante'>Rep
            </td>
            <td rowspan='2'>
                <font title='N.º do Pedido'> N.º&nbsp;Ped
            </td>
	</tr>
	<tr class='linhadestaque' align='center'>
            <td>
                Ini
            </td>
            <td>
                Fat
            </td>
            <td>
                Sep
            </td>
            <td>
                Pend
            </td>
            <td>
                Vale
            </td>
            <td>
                E.D.
            </td>
	</tr>
<?
		$id_pedido_venda_antigo = '';//Variável para controle das cores no Orçamento ...
                for ($i = 0;  $i < $linhas; $i++) {
                        $vetor = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
			$id_cliente_current = $campos[$i]['id_cliente'];
			$id_empresa_current = $campos[$i]['id_empresa'];
?>
	<tr class='linhanormal' align='center'>
		<td align='left'>
			<?=$campos[$i]['cliente'];?>
		</td>
		<td>
		<?
//Tratamento com as cores de Crédito
			if($campos[$i]['credito'] == 'C' || $campos[$i]['credito'] == 'D') {//Se for D, Cliente caloteiro, vermelho ...
				$font = "<font color='red'>";
			}else {//Se não está ok, azul ...
				$font = "<font color='blue'>";
			}
			echo $font.$campos[$i]['credito'];
		?>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=segurancas::number_format($campos[$i]['qtde'], 0, '.');?>
			</font>
		</td>
		<td>
<?
//Só aparecerá o Link do que já foi faturado, se tiver pelo menos 1 item q já está em NF
			if($campos[$i]['qtde_faturada'] > 0) {
?>
		<a href="javascript:nova_janela('../../classes/faturamento/faturado.php?id_pedido_venda_item=<?=$campos[$i]['id_pedido_venda_item'];?>', 'FATURADO', '', '', '', '', 350, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Faturamento" class='link'>
				<?=segurancas::number_format($campos[$i]['qtde_faturada'], 0, '.');?>
		</a>
<?
			}else {
				echo segurancas::number_format($campos[$i]['qtde_faturada'], 0, '.');
			}
?>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
			<?
				$separado = $campos[$i]['qtde']-$campos[$i]['qtde_pendente']-$campos[$i]['vale']-$campos[$i]['qtde_faturada'];
				echo segurancas::number_format($separado, 0, '.');
			?>
			</font>
		</td>
		<td bgcolor='#CECECE'>
		<?
			if($campos[$i]['qtde_pendente'] > $vetor[3]) {
				echo '<font color="red"><b>'.segurancas::number_format($campos[$i]['qtde_pendente'], 0, '.').'</b></font>';
			}else {
				echo segurancas::number_format($campos[$i]['qtde_pendente'], 0, '.');
			}
		?>
		</td>
		<td>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                        <?=segurancas::number_format($campos[$i]['vale'], 0, '.');?>
                    </font>
		</td>
		<td>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                        <?=segurancas::number_format($vetor[3], 0, '.');?>
                    </font>
		</td>
		<td align='left'>
                    <a href="javascript:nova_janela('../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'ESTOQUE', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
                        <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '', $campos[$i]['id_produto_acabado_discriminacao']);?>
                    </a>
		</td>
		<td align='right'>
		<?
                    $preco_liq_final = $campos[$i]['preco_liq_final'];
                    echo number_format($preco_liq_final, 2, ',', '.');
		?>
		</td>
		<td align='right'>
		<?
//Quando o país é do Tipo Internacional, ou o Pedido for do Tipo SGD ou o Cliente possuir suframa, então não existe IPI ...
			if($id_pais != 31 || $campos[$i]['id_empresa'] == 4 || !empty($suframa) || $campos[$i]['operacao'] == 1) {
                            $ipi                    = 'S/IPI';
                            $id_classific_fiscal    = '';
			}else { //Aqui tem que buscar a Classificação Fiscal para poder buscar o IPI
                            $sql = "SELECT cf.`ipi`, cf.`id_classific_fiscal` 
                                    FROM `produtos_acabados` pa 
                                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                                    INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                                    WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
                            $campos_temp = bancos::sql($sql);
                            if(count($campos_temp) > 0) {
                                $ipi                = number_format($campos_temp[0]['ipi'], 1, ',', '.');
                                $id_classific_fiscal= $campos_temp[0]['id_classific_fiscal'];
                            }else {
                                $id_classific_fiscal= '';
                                $ipi = '&nbsp;';
                            }
			}
			echo $ipi;
		?>
		</td>
		<td align='right'>
		<?
                    $preco_total_lote = $preco_liq_final * ($campos[$i]['qtde'] - $campos[$i]['qtde_faturada']);
                    $total_geral+= $preco_total_lote;
                    echo number_format($preco_total_lote, 2, ',', '.');
		?>
		</td>
		<td>
		<?
			if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
			if($campos[$i]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
			if($campos[$i]['vencimento2'] > 0) {
                            $prazo_faturamento= $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
			}else {
                            $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
			}

			if($campos[$i]['id_empresa']==1) {
                            $nomefantasia = 'ALBA - NF';
                            $total_empresa+= $preco_total_lote;
				
                            echo '(A - NF) / '.$prazo_faturamento;
			}else if($campos[$i]['id_empresa']==2) {
                            $nomefantasia = 'TOOL - NF';
                            $total_empresa+= $preco_total_lote;

                            echo '(T - NF) / '.$prazo_faturamento;
			}else if($campos[$i]['id_empresa']==4) {
                            $nomefantasia = 'GRUPO - SGD';
                            $total_empresa+= $preco_total_lote;

                            echo '(G - SGD) / '.$prazo_faturamento;
			}else {
                            echo 'Erro';
			}
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
			$prazo_faturamento = '';
		?>
		</td>
		<td>
		<?
			if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                            if($campos[$i]['faturar_em'] > $data_atual_mais_um) {
                                echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                            }else {
                                echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</b></font>';
                            }
			}else {
                            echo '&nbsp;';
			}
		?>
		</td>
		<td>
                    <?=$campos[$i]['nome_fantasia'];?>
		</td>
		<td>
		<?
			$url = "javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_pedido.php?veio_faturamento=1&id_pedido_venda=".$campos[$i]['id_pedido_venda']."', 'PED', '', '', '', '', 440, 780, 'c', 'c', '', '', 's', 's', '', '', '') ";
		?>
			<a href="<?=$url;?>" title="Visualizar Detalhes de Pedido" class='link'>
	<?
		if($id_pedido_venda_antigo != $campos[$i]['id_pedido_venda']) {
//Aki significa que mudou para outro N. de Pedido e vai exibir uma nova sequência desses mesmos
			$id_pedido_venda_antigo = $campos[$i]['id_pedido_venda'];
	?>
				<font color='red'>
					<?=$campos[$i]['id_pedido_venda'];?>
				</font>
	<?
//Ainda são os mesmos Orçamentos
		}else {
			echo $campos[$i]['id_pedido_venda'];
		}
	?>
			</a>
		</td>
	</tr>
	<?
//Se o Cliente estiver com o crédito OK, então realiza os cálculos
		if($campos[$i]['credito'] == 'A' || $campos[$i]['credito'] == 'B') {
//Se a Data de Programação for até a Data de Amanhã então é faturável
			if($campos[$i]['faturar_em'] <= $data_atual_mais_um) {
				if($campos[$i]['qtde_pendente'] >= $vetor[3]) {
					$resultado = $vetor[3];
				}else {
					$resultado = $campos[$i]['qtde_pendente'];
				}
				$valor_faturavel += ($separado + $campos[$i]['vale'] + $resultado) * ($campos[$i]['preco_liq_final']);
			}
		}
/*Verifico se o Cliente Corrente do Loop é diferente do Próxmo Cliente que será listado ou 
se a Empresa Corrente do Loop que será listada ...*/
                    if(($id_empresa_current != $campos[$i+1]['id_empresa']) || ($id_cliente_current != $campos[$i+1]['id_cliente'])) {
	?>
	<tr class='linhanormal'>
            <td colspan='10' bgcolor='#CECECE' align='left'>
                <font color='green'>
                    <b>Valor Faturável:</b>
                    <?='R$ '.number_format($valor_faturavel, 2, ',', '.');?>
                </font>
            </td>
            <td colspan='6' bgcolor='#CECECE' align='right'>
                <b>Total: <?=$nomefantasia.' => '.$tipo_moeda.number_format($total_empresa, 2, ',', '.');?></b>
            </td>
	</tr>
	<?
			$valor_faturavel = 0;
			$total_empresa = 0;
			$id_cliente_current = $campos[$i + 1]['id_cliente'];
			$id_empresa_current = $campos[$i + 1]['id_empresa'];
                    }
                }
?>
	<tr class='linhadestaque' align="right">
            <td colspan='11'>
                <font color='yellow'>Dólar Dia:</font>
                <?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?>
            </td>
            <td colspan='5' align='right'>
                <span class='style12'>
                    <font color='yellow'>TOTAL GERAL: </font>
                    <?=$tipo_moeda.number_format($total_geral, 2, ',', '.');?>
                </span>
            </td>
	</tr>
<?
        }else {
?>
    <tr class="atencao">
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="#FF0000">
                <b>NÃO HÁ PEDIDO(s) PENDENTE(s).</b>
            </font>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='atencao' align='center'>
        <td colspan='16'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' style='color:red' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>