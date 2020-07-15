<?
/*Eu tenho esse desvio aki para não redeclarar as bibliotecas novamente, isso porque tem vezes que esse 
arquivo é exibido dentro de outro arquivo como um Iframe e sendo assim no início do arquivo Principal já 
são chamadas bibliotecas ...*/
if($nao_chamar_biblioteca != 1) {
    require('../../../../lib/segurancas.php');
    require('../../../../lib/compras_new.php');
    require('../../../../lib/custos.php');
    require('../../../../lib/data.php');
    require('../../../../lib/estoque_new.php');
    require('../../../../lib/estoque_acabado.php');
    require('../../../../lib/intermodular.php');
    require('../../../../lib/vendas.php');
    //Essa segurança é p/ que ignore essa verificação devido esse arquivo ser chamado como Iframe em outros arquivos ...
    if(empty($_GET['ignorar_seguranca_url'])) segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/nivel_estoque/index.php', '../../../../');
}
session_start('funcionarios');
$mensagem[1] = "<font class='confirmacao'>NÃO HÁ PENDÊNCIA(S) DE COMPRA PARA ESTE ITEM.</font>";

/*Explicação das duas querys abaixo:

1) Aqui eu busco todos os Itens de Pedido que estejam Totalmente em Aberto ou importados Parcialmente em Nota Fiscal 
e não liberados em Estoque. Pedidos Descontabilizados aparecem nesse Relatório com a Marcação DC ...

2) Aqui eu busco todos os Itens de Pedido que estejam Totalmente importados em Nota Fiscal e ñ liberados em Estoque ...*/
$sql = "SELECT ip.id_item_pedido 
        FROM `itens_pedidos` ip 
        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`status` = '1' AND ((p.`programado_descontabilizado` = 'S' AND p.`ativo` = '0') OR (p.`programado_descontabilizado` = 'N' AND p.`ativo` = '1')) 
        WHERE ip.`id_produto_insumo` = '$_GET[id_produto_insumo]' 
        AND ip.`status` < '2' 
        UNION 
        SELECT ip.id_item_pedido 
        FROM `itens_pedidos` ip 
        INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` AND nfeh.`status` = '0' 
        WHERE ip.`id_produto_insumo` = '$_GET[id_produto_insumo]' 
        AND ip.`status` = '2' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {//Não existe nenhum item nas situações cima ...
    $id_itens_pedidos = 0;//Controle p/ não furar o SQL abaixo ...
}else {//Existe pelo menos um item na situação cima ...
    for($i = 0; $i < $linhas; $i++) $vetor_item_pedido[] = $campos[$i]['id_item_pedido'];
    $id_itens_pedidos = implode(',', $vetor_item_pedido);
}

/*Mostro todos os Pedidos de acordo com os Itens encontrados acima desde que esses "NÃO" 
sejam Programados Descontabilizados ...*/
$sql = "SELECT e.`nomefantasia`, IF(f.`nomefantasia` = '', f.`razaosocial`, f.`nomefantasia`) AS fornecedor, g.`referencia`, pi.`discriminacao`, ip.`id_item_pedido`, 
        ip.`id_produto_insumo`, ip.`preco_unitario`, ip.`qtde`, ip.`marca`, p.`id_pedido`, 
        p.`prazo_entrega`, p.`data_retirada_porto`, p.`tipo_nota_porc`, p.`tipo_export`, p.`tp_moeda`, 
        u.`sigla` 
        FROM `itens_pedidos` ip 
        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`programado_descontabilizado` = 'N' 
        INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        WHERE ip.`id_item_pedido` IN ($id_itens_pedidos) ORDER BY pi.discriminacao, g.referencia ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {//Se não encontrou nenhuma Pendência ...
?>
<html>
<head>
<title>.:: Pendências ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<form name='form' method='post'>
<table width="95%" border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td>
            <?
//Se essa variável estiver com valor Nulo, então posso exibir o Botão Voltar normalmente ...
                if(empty($nao_exibir_voltar)) {
            ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class="botao" onclick="window.location = '../detalhes.php?id_produto_insumo=<?=$_GET['id_produto_insumo'];?>'">
            <?
                }
            ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {//Existe pela menos 1 Pendência ...
?>
<html>
<head>
<title>.:: Pendências de Pedido(s) de Compra(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class="linhacabecalho" align='center'>
        <td colspan='13'>
            Pendências de Pedido(s) de Compra(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <b>N.º Ped</b>
        </td>
        <td>
            Qtde <br>Solicitado
        </td>
        <td>
            Qtde <br>Recebido
        </td>
        <td>
            Qtde <br>Restante
        </td>
        <td>
            <b>Un</b>
        </td>
        <td>
            <b>Ref</b>
        </td>
        <td>
            <b>Discriminação</b>
        </td>
        <td>
            <b>Pço Unit</b>
        </td>
        <td>
            <b>Forma de Compra</b>
        </td>
        <td>
            <b>Valor Total</b>
        </td>
        <td>
            <b>Marca / Obs</b>
        </td>
        <td>
            <b>Data de<br>Entrega</b>
        </td>
        <td>
            <b>Prev <br>Estoque</b>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $id_pedido = $campos[$i]['id_pedido'];
		if($nao_chamar_biblioteca != 1) {//Aqui é o modo normal quando chama esse arquivo, então não dá erro no link ...
                    $url = "javascript:nova_janela('../../pedidos/itens/itens.php?id_pedido=$id_pedido&pop_up=1', 'DETALHES', 'F') ";
		}else {//Tem esse desvio, pq dependendo do lugar em que eu chamo essa função, os níveis do link dá erro ...
                    $url = "javascript:nova_janela('../../compras/pedidos/itens/itens.php?id_pedido=$id_pedido&pop_up=1', 'DETALHES', 'F') ";
		}
                
                //Busca o Total entregue do Item do Pedido em diversas NF(s) ...
		$sql = "SELECT SUM(`qtde_entregue`) AS total_entregue 
                        FROM `nfe_historicos` 
                        WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' ";
		$campos_entregue    = bancos::sql($sql);
		$total_entregue     = $campos_entregue[0]['total_entregue'];

                //Busca o Total entregue do Item do Pedido em diversas NF(s) que já não foi liberado ...
		$sql = "SELECT SUM(`qtde_entregue`) AS total_entregue 
                        FROM `nfe_historicos` 
                        WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' 
                        AND `status` = '0' ";
		$campos_entregue                = bancos::sql($sql);
		$total_entregue_nao_liberado    = $campos_entregue[0]['total_entregue'];
                $total_restante                 = $campos[$i]['qtde'] - $total_entregue + $total_entregue_nao_liberado;

                //Só irá contabilcizar a Quantidade Restante quando existir Preço p/ o Item de Pedido ...
                if($campos[$i]['preco_unitario'] != '0.00') $qtde_pendencias_total+= $total_restante;//Nesse caso a Qtde Total sempre será em cima do Restante ...
                $preco_total+= $total_restante * $campos[$i]['preco_unitario'];
?>
	<tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
                <td onclick="<?=$url;?>">
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                        <a href="<?=$url;?>" class='link'>
                        <?
                            echo $campos[$i]['id_pedido'];
                            if($campos[$i]['tipo_export'] == 'E') {
                                echo '<font color="red"><b>(Exp)</b></font>';
                            }else if($campos[$i]['tipo_export'] == 'I') {
                                echo '<font color="red"><b>(Imp)</b></font>';
                            }else if($campos[$i]['tipo_export'] == 'N') {
                                echo '<font color="red"><b>(Nac)</b></font>';
                            }
//Aqui eu verifico se existe Importação atrelada com o Pedido ...
                            $sql = "SELECT i.nome 
                                    FROM `importacoes` i 
                                    INNER JOIN `pedidos` p ON p.id_importacao = i.id_importacao 
                                    WHERE p.id_pedido = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
                            $campos_importacao = bancos::sql($sql);
                            if(count($campos_importacao) == 1) {//Se existir importação, printa o nome
                                echo ' / <font color="red"><b>'.$campos_importacao[0]['nome'].'</b></font>';
                            }
                            if($campos[$i]['programado_descontabilizado'] == 'S') echo ' <font color="purple" title="Descontabilizado" style="cursor:help">(DC)</font>';
                        ?>
                        </a>
                    </font>
                    <?
                        echo '<br/><b>'.strtoupper($campos[$i]['fornecedor']).'</b>';
                    ?>
		</td>	
                <td>
                <?
                    if($campos[$i]['qtde'] < 0) {
                        echo '<font color="red"><b>'.segurancas::number_format($campos[$i]['qtde'], 2, '.').'</b></font>';
                    }else {
                        echo segurancas::number_format($campos[$i]['qtde'], 2, '.');
                    }
                ?>
		</td>
		<td>
                <?
                    if($total_entregue > 0) echo segurancas::number_format($total_entregue, 2, '.');
                    if($total_entregue_nao_liberado > 0) echo ' /<br/><font color="red"><b>'.segurancas::number_format($total_entregue_nao_liberado, 2, '.').' Ñ LIB</b></font>';
                ?>
		</td>
		<td>
                    <?=segurancas::number_format($total_restante, 2, '.');?>
		</td>
		<td align='left'>
                    <?=$campos[$i]['sigla'];?>
		</td>
		<td align='left'>
                    <?=$campos[$i]['referencia'];?>
		</td>
		<td align='left'>
                    <?=$campos[$i]['discriminacao'];?>
		</td>
		<td align='right'>
                <?
                    $tipo_moeda = $campos[$i]['tp_moeda'];
                    if($tipo_moeda == 1) {
                        $tipo_moeda = 'R$ ';
                    }else if($tipo_moeda == 2) {
                        $tipo_moeda = 'U$ ';
                    }else {
                        $tipo_moeda = '&euro; ';
                    }
                    echo $tipo_moeda.number_format($campos[$i]['preco_unitario'], 2, ',', '.');
                ?>
		</td>
                <td>
                <?
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Financiamento que foi feitas p/ o Pedido ...
                    $sql = "SELECT pf.*, tm.simbolo 
                            FROM `pedidos_financiamentos` pf 
                            INNER JOIN `pedidos` p ON p.id_pedido = pf.id_pedido 
                            INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
                            WHERE pf.`id_pedido` = '".$campos[$i]['id_pedido']."' ORDER BY pf.dias ";
                    $campos_financiamento = bancos::sql($sql);
                    $linhas_financiamento = count($campos_financiamento);
                    if($linhas_financiamento > 0) {//Se foi encontrado pelo menos 1 Financiamento ...
                        for($j = 0; $j < $linhas_financiamento; $j++) {
                            if($j == 0) {//Se eu estiver na Primeira parcela
                                $primeira_parcela = $campos_financiamento[$j]['dias'];
                            }else if($j + 1 == $linhas_financiamento) {//Última Parcela
                                $ultima_parcela = $campos_financiamento[$j]['dias'];
                            }
                        }
                        if($campos[$i]['tipo_nota'] == 1) {//NF
                            $exibir_nota = 'NF';
                        }else {//SGD
                            $exibir_nota = 'SGD';
                        }
                        if($campos[$i]['tipo_export'] == 'E') {
                            $tipo_export = 'Exp';
                        }else if($campos[$i]['tipo_export'] == 'N') {
                            $tipo_export = 'Nac';
                        }else if($campos[$i]['tipo_export'] == 'I') {
                            $tipo_export = 'Imp';
                        }
                        $condicao_ddl = $linhas_financiamento.' parc. ('.$primeira_parcela.' à '.$ultima_parcela.' DDL) '.$exibir_nota.' '.$campos[$i]['tipo_nota_porc'].' % - '.$tipo_export;
                    }
                    echo $condicao_ddl;
                ?>
		</td>
		<td align='right'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                            <?=$tipo_moeda.number_format($campos[$i]['qtde'] * $campos[$i]['preco_unitario'], 2, ',', '.');?>
			</font>
		</td>
		<td align='left'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
			<?
				if(!empty($campos[$i]['marca'])) {
					echo $campos[$i]['marca'];
				}else {
					echo '&nbsp';
				}
			?>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=data::datetodata($campos[$i]['prazo_entrega'], '/');?>
			</font>
		</td>
		<td>
		<?
			//Busca do Fornecedor Default do PI ...
			$id_fornecedor = custos::preco_custo_pi($campos[$i]['id_produto_insumo'], '', 1);

			$sql = "SELECT id_pais 
                                FROM `fornecedores` 
                                WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
			$campos_pais = bancos::sql($sql);
			
			if($campos_pais[0]['id_pais'] <> 31) {//Se Forn = 'Estrangeiro' então apresenta a Data Retirada no Porto ...
                            echo data::datetodata($campos[$i]['data_retirada_porto'], '/');
			}else {//Se for Nacional segue a regra abaixo ...
                            //Verifico se o PI é um PIPA ...
                            $sql = "SELECT ged.id_grupo_pa 
                                    FROM `produtos_acabados` pa 
                                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                                    WHERE pa.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                            $campos_gpa = bancos::sql($sql);
                            if(count($campos_gpa) == 1) {//Se encontrou algum Registro ...
                                if($campos_gpa[0]['id_grupo_pa'] == 1) {//Bedames M2
                                    echo data::adicionar_data_hora(data::datetodata($campos[$i]['prazo_entrega'], '/'), 5);
                                }else if($campos_gpa[0]['id_grupo_pa'] == 8) {//Chave de Pino
                                    echo data::adicionar_data_hora(data::datetodata($campos[$i]['prazo_entrega'], '/'), 15);
                                }else if($campos_gpa[0]['id_grupo_pa'] == 37) {//Chave de Garra
                                    echo data::adicionar_data_hora(data::datetodata($campos[$i]['prazo_entrega'], '/'), 10);
                                }else if($campos_gpa[0]['id_grupo_pa'] == 43) {//Pinos DIN 7978
                                    echo data::adicionar_data_hora(data::datetodata($campos[$i]['prazo_entrega'], '/'), 5);
                                }else if($campos_gpa[0]['id_grupo_pa'] == 44) {//Pinos DIN 7979
                                    echo data::adicionar_data_hora(data::datetodata($campos[$i]['prazo_entrega'], '/'), 10);
                                }else {
                                    echo data::datetodata($campos[$i]['prazo_entrega'], '/');
                                }
                            }
			}
		?>
		</td>
	</tr>
<?
		$compra_producao_total+= $total_restante;
	}
        //Nesse caso o Valor Corrigido já é o Próprio Preço Total ...
        $valor_total_corrigido_geral = $preco_total;
?>
	<tr class='linhadestaque'>
            <td colspan='3'>
                Necessidade: 
            <?
                $necessidade_compra = estoque_ic::necessidade_compras($_GET['id_produto_insumo']);
                if($necessidade_compra > 0) {
            ?>
                    <a href="javascript:nova_janela('../../../classes/produtos_insumos/detalhes_producao.php?id_produto_insumo=<?=$_GET['id_produto_insumo'];?>', 'NECESSIDADE_COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Necessidade de Compra" style="cursor:help" class="link">
                        <font size='2'>
                            <?=segurancas::number_format($necessidade_compra, 2, '.');?>
                        </font>
                    </a>
            <?
                }
            ?>
            </td>
            <td>
                <?=number_format($qtde_pendencias_total, 2, ',', '.');?>
            </td>
            <td colspan='4'>
                <font color='darkblue'>
                    <?$preco_pendencia_medio_corr_atual = ($valor_total_corrigido_geral / $qtde_pendencias_total);?>
                    P.Pendência Med.Corr.Atual = <?=$tipo_moeda.number_format($preco_pendencia_medio_corr_atual, 2, ',', '.');?>
                </font>
            </td>
            <td colspan='2' align='right'>
                <?=$tipo_moeda.number_format($preco_total, 2, ',', '.');?>
            </td>
            <td colspan='3'>
                
            </td>
	</tr>
        <tr class='linhadestaque'>
            <td colspan='3'>
                Compra / Produção: 
            </td>
            <td>
                <?=number_format($compra_producao_total, 2, ',', '.');?>
            </td>
            <td colspan='9'>
                &nbsp;
            </td>
	</tr>
	<tr class='linhacabecalho' align='center'>
            <td colspan='13'>
            <?
//Se essa variável estiver com valor Nulo, então posso exibir o Botão Voltar normalmente ...
                if(empty($nao_exibir_voltar)) {
            ?>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../detalhes.php?id_produto_insumo=<?=$_GET['id_produto_insumo'];?>'" class="botao">
                <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='parent.window.close()' style='color:red' class='botao'>
            <?
                }else {
                    $custo_ml_zero_preco_venda_orc                      = $_GET['txt_preco_l_fat'] / (1 + $_GET['txt_margem_lucro'] / 100);
                    $preco_pendencia_medio_corr_ml_est_mais_acessorio   = $preco_pendencia_medio_corr_atual + $_GET['acrescimo_acessorio'];
                    $custo_ml_zero_pendencias                           = $custo_ml_zero_preco_venda_orc / ($_GET['txt_preco_compra_lista_rs'] + $_GET['acrescimo_acessorio']) * $preco_pendencia_medio_corr_ml_est_mais_acessorio;
                    $ml_est_pendencias                                  = ($_GET['txt_preco_l_fat'] / $custo_ml_zero_pendencias - 1) * 100;
                    echo '<font color="yellow" size="2">ML Estimada p/ Preço Pendência Med.Corr.Atual = '.number_format($ml_est_pendencias, 1, ',', '.').' %</font>';
                    
                    //Calculamos a Margem de Lucro Estimada p/ara/ o que temos em Estoque + p/ o que temos a Receber desse PIPA ...
                    $valor_corr_atual_global = $_GET['qtde_compras_total'] * ($_GET['preco_compras_medio_corr_atual'] + $_GET['acrescimo_acessorio']) + $qtde_pendencias_total * ($preco_pendencia_medio_corr_atual + $_GET['acrescimo_acessorio']);
                    $preco_medio_corr_global = round($valor_corr_atual_global / ($_GET['qtde_compras_total'] + $qtde_pendencias_total), 2);
                }
            ?>
            </td>
	</tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr class='iframe' align='center'>
            <td colspan='13'>
               P. Med. Corr. Atual Global = <?=$tipo_moeda.number_format($preco_medio_corr_global, 2, ',', '.');?>
            </td>
	</tr>
        <tr class='iframe' align='center'>
            <td colspan='13'>
                ML Estimada p/ Med. Corr. Atual Global = 
                <?
                    $custo_ml_zero_global   = $custo_ml_zero_preco_venda_orc / ($_GET['txt_preco_compra_lista_rs'] + $_GET['acrescimo_acessorio']) * $preco_medio_corr_global;
                    $ml_est_global          = ($custo_ml_zero_global == 0) ? 0 : round(($_GET['txt_preco_l_fat'] / $custo_ml_zero_global - 1) * 100, 2);
                    echo number_format($ml_est_global, 1, ',', '.').' %';
                ?>
            </td>
	</tr>
        <tr class='iframe' align='center'>
            <td colspan='13'>
                Preço Líquido Final (Venda) p/ ML desejada (<?=number_format($_GET['txt_margem_lucro_desejada'], 1, ',', '.');?>) % = 
                <?
                    $custo_ml_zero_preco_venda_orc_ml_est               = $_GET['txt_preco_l_fat'] / (1 + $ml_est_global / 100);
                    $preco_liquido_final_venda_para_ml_desejada_ml_est  = $custo_ml_zero_preco_venda_orc_ml_est * (1 + $_GET['txt_margem_lucro_desejada'] / 100);
                    echo $tipo_moeda.number_format($preco_liquido_final_venda_para_ml_desejada_ml_est, 2, ',', '.');
                ?>
            </td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color='blue'>
* Caso exista valor de "Compra Produção" na Tela de Itens do Nível de Estoque e os detalhes 
dessa discriminação não aparece aqui nessa tela, pode ser que esse esteje importado para 
a Nota Fiscal e a mesma ainda não foi liberada.
</font>
</pre>