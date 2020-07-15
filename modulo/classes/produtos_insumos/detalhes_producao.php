<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/estoque_new.php');
require('../../../lib/intermodular.php');
require('../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/inventario.php', '../../../');

$mensagem[1] = "<font class='atencao'>NÃO HÁ P.A(S) ATRELADO(S) A ESTE P.I.</font>";

$vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();
$data_atual                             = date('Y-m-d');

/* Eu busco essa Unidade de Conversão do PI, porque vou estar utilizando nos cálculos + abaixo ...
e busco a Sigla do PI também ... */
$sql = "SELECT g.`referencia`, pi.`unidade_conversao`, pi.`discriminacao`, u.`sigla` 
        FROM `produtos_insumos` pi 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        WHERE pi.`id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Detalhes de Produção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho'>
        <td>
            <font color='yellow'>Produto: </font>
                <font color='white' size='2'>
                    <?=$campos[0]['referencia'].' - '.$campos[0]['discriminacao'];?>
                </font>
            </font>
            &nbsp;
            <input type='button' name='cmd_substituir_pi_outro_pi' value='Substituir PI por outro PI' title='Substituir PI por outro PI' onclick="nova_janela('../../classes/produtos_insumos/substituir_pi_para_outro_pi.php?id_produto_insumo=<?=$_GET['id_produto_insumo'];?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Detalhes de Produção
        </td>
    </tr>
</table>
<!--1ª Etapa-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Produto(s) Acabado(s) da 1ª Etapa Atrelado(s) a este PI
        </td>
    </tr>
<?
/*Serve para listar todos os P.A.(s) que estão atrelados ao custo da Primeira Etapa através 
desse Produto Insumo*/
	$sql = "SELECT gpa.`nome`, pa.`mmv`, pa.`operacao_custo`, pa.`operacao_custo_sub`, pa.`observacao`, 
                ppe.`id_produto_acabado`, ppe.`pecas_por_emb` 
                FROM `pas_vs_pis_embs` ppe 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ppe.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE ppe.`id_produto_insumo` = '$_GET[id_produto_insumo]' ORDER BY pa.`discriminacao` ";
	$campos_dados_gerais    = bancos::sql($sql);
	$linhas                 = count($campos_dados_gerais);
//Somente Quando existir Itens ...
	if($linhas > 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            <font title='Operação de Custo' style='cursor:help'>
                O.C.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido Atrelado' style='cursor:help'>
                EC Atrel
            </font>
        </td>
        <td>
            <font title="Quantidade à Produzir" style='cursor:help'>
                Qtde Prod
            </font>
        </td>
        <td>
            <font title='Qtde Excedente s/ Embalagem' style='cursor:help'>
                Qtde Exced. s/ Emb
            </font>
        </td>
        <td>
            <font title="Estoque Baixa" style='cursor:help'>
                Est Baixa
            </font>
        </td>
        <td>
            <font title="Qtde do PI" style='cursor:help'>
                Qtde PI
            </font>
        </td>
        <td>
            <font title="Necessidade Atual" style='cursor:help'>
                Nec. Atual
            </font>
        </td>
        <td>
            MMV
        </td>
        <td>
            CMMV
        </td>
        <td>
            OP(s)
        </td>
    </tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="javascript:nova_janela('../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Estoque" class='link'>
            <?
//Verifico se este P.A., está atrelado a alguma OP que ainda não foi excluída ...
                    $sql = "SELECT `id_op` 
                            FROM `ops` 
                            WHERE `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                            AND `status_finalizar` = '0' 
                            AND `ativo` = '1' ";
                    $campos3 = bancos::sql($sql);
                    if(count($campos3) == 1) {//Se estiver, eu mudo a cor deste ...
            ?>
                        <font color='brown'>
            <?
                    }
                    echo intermodular::pa_discriminacao($campos_dados_gerais[$i]['id_produto_acabado'], 0);
                    echo '<font color="black"><b> - Grupo PA: </b>'.$campos_dados_gerais[$i]['nome'].'</font>';
            ?>
            </a>
            &nbsp;
            <?
                if(!empty($campos_dados_gerais[$i]['observacao'])) echo "<img width='22'  height='18' title='".$campos_dados_gerais[$i]['observacao']."' src = '../../../imagem/olho.jpg'>";
            ?>
            &nbsp;
<?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                if($campos_dados_gerais[$i]['operacao_custo'] == 0) {//Industrial
?>
        <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&tela=2&ignorar_sessao=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" class='link'>
<?
                }else {
?>
        <a href="javascript:nova_janela('../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" class='link'>
<?
                }
?>
            <img src = "../../../imagem/menu/alterar.png" border='0' title="Alterar Custo" alt="Alterar Custo">
        </a>
<?
            }
/****************************************************************************/
//Eu já antecipo esse código aqui porque vou precisar dessa variável p/ passar por parâmetro ...
            //$retorno = estoque_acabado::qtde_estoque($campos_dados_gerais[$i]['id_produto_acabado']);
            $compra                 = estoque_acabado::compra_producao($campos_dados_gerais[$i]['id_produto_acabado']);
            $baixas_pis_para_ops    = estoque_ic::baixas_pis_para_ops($campos_dados_gerais[$i]['id_produto_acabado'], $_GET['id_produto_insumo']);
            
            $sql = "SELECT SUM(`qtde_produzir`) AS qtde_produzindo 
                    FROM `ops` 
                    WHERE `status_finalizar` = '0' 
                    AND `ativo` = '1' 
                    AND `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' ";
            $campos_produzir = bancos::sql($sql);
            $qtde_produzindo = $campos_produzir[0]['qtde_produzindo']; // esse calculo é sem a entrada de PA parcial da OP mesmo
            $producao = $qtde_produzindo - $baixas_pis_para_ops;
            //$producao = $retorno[2] - $baixas_pis_para_ops;
            $compra_producao = $producao + $compra;
/****************************************************************************/
?>
            &nbsp;
            <a href="nova_janela('../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Pedidos - Últimos 6 meses' class='link'>
                <img src= '../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
<?
            //Somente para esses funcionários -> Roberto 62, Dárcio 98 porque programa ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
?>
                &nbsp;
                <a href="javascript:nova_janela('../../producao/ops/visualizar_pis.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&nova_qtde_produzir=<?=$compra_producao;?>', 'POP', '', '', '', '', 600, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar PI's" class='link'>
                    <img src = '../../../imagem/propriedades.png' title="Visualizar PI's" alt="Visualizar PI's" border='0'>
                </a>
<?
            }
?>
        </td>
        <td>
        <?
            if($campos_dados_gerais[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos_dados_gerais[$i]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos_dados_gerais[$i]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos_dados_gerais[$i]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
        <?
            $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_dados_gerais[$i]['id_produto_acabado']);
            $color_ec_pas_atrelados = ($retorno_pas_atrelados['total_ec_pas_atrelados'] < 0) ? 'red' : 'black';
        ?>
            <font color='<?=$color_ec_pas_atrelados;?>'>
                <?=number_format($retorno_pas_atrelados['total_ec_pas_atrelados'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <?=segurancas::number_format($producao, 2, '.');?>
        </td>
        <td align='right'>
        <?
            //Verifico se o Item possui Estoque Excedente "NÃO Embalado", mas somente do que está "Em aberto" ...
            $sql = "SELECT SUM(`qtde`) AS quantidade 
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                    AND `embalado` = 'N' 
                    AND `status` = '0' ";
            $campos_excedente    = bancos::sql($sql);
            echo $campos_excedente[0]['quantidade'];
        ?>
        </td>
        <td align='right'>
            <a href="javascript:nova_janela('../../compras/estoque_i_c/ops_baixadas.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&id_produto_insumo=<?=$_GET['id_produto_insumo'];?>', 'OPS_BAIXADAS', '', '', '', '', 350, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='OP(s) Baixada(s)' style='cursor:help' class='link'>
            <?
                $baixa_pi = estoque_ic::baixa_pi($_GET['id_produto_insumo'], $campos_dados_gerais[$i]['id_produto_acabado']);
                echo segurancas::number_format($baixa_pi, 2, '.');
            ?>
            </a>
        </td>
        <td align='right'>
        <?
//Se existir a Unidade de Conversão, então eu também faço a divisão com esta na fórmula também ...
            if($campos[0]['unidade_conversao'] != 0) {
                $qtde = (1 / $campos_dados_gerais[$i]['pecas_por_emb']) * (1 / $campos[0]['unidade_conversao']);
                echo segurancas::number_format($qtde, 8, '.');
/*Caso não exista a Unidade de Conversão, então eu não aplico esta na Fórmula p/ que não de erro
de Divisão por Zero ...*/
            }else {
                $qtde = (1 / $campos_dados_gerais[$i]['pecas_por_emb']);
                echo segurancas::number_format($qtde, 2, '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            //Somente se o PI for do Grupo 'EMB' então somo também na Necessidade a Qtde Excedente ...
            if($campos[0]['referencia'] == 'EMB') {
                $nec_atual1 = ($compra_producao + $campos_excedente[0]['quantidade']) * $qtde;
            }else {
                $nec_atual1 = $qtde * $compra_producao;
            }
            
            if($nec_atual1 != 0) echo segurancas::number_format($nec_atual1, 2, '.').' '.$campos[0]['sigla'];
            $total_nec_atual1+= $nec_atual1;
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos_dados_gerais[$i]['mmv'], 2, '.');?>
        </td>
        <td align='right'>
        <?
            $consumo_mensal1 = $qtde * $campos_dados_gerais[$i]['mmv'];
            if($consumo_mensal1 != 0) echo segurancas::number_format($consumo_mensal1, 2, '.').' '.$campos[0]['sigla'];
            $total_consumo_mensal1+= $consumo_mensal1;
        ?>
        </td>
        <td>
        <?
/****************************************************************************************************/
//Busca das OP(s) nos últimos 6 meses ...
            $sql = "SELECT ops.`id_op`, bop.`status` 
                    FROM `ops` 
                    INNER JOIN `baixas_ops_vs_pis` bop ON bop.`id_op` = ops.`id_op` 
                    WHERE bop.`id_produto_insumo` = '$_GET[id_produto_insumo]' 
                    AND ops.`id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                    AND (ops.`data_emissao` >= DATE_ADD('$data_atual', INTERVAL -180 DAY)) 
                    AND ops.`ativo` = '1' ";
            $campos_ops = bancos::sql($sql);
            $linhas_ops = count($campos_ops);
            for($j = 0; $j < $linhas_ops; $j++) {
                if($campos_ops[$j]['status'] == 2) {//Se já foi dado Baixa Total na OP, mostra em Verde ...
                    $color = 'green';
                    $title = 'OP Baixada';
                }else {//Mostra em Vermelho, caso esteja com Baixa Parcial ou Estorno ...
                    $color = 'red';
                    $title = 'OP em Aberto';
                }
        ?>
            <!--******Esse parâmetro é para que essa tela seja aberta como Pop-UP e não mostre os botões 
            do fim da Tela******-->
            <a href="javascript:nova_janela('../../producao/ops/alterar.php?passo=2&id_op=<?=$campos_ops[$j]['id_op'];?>&pop_up=1', 'DETALHES', '', '', '', '', '480', '880', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de OP' class='link'>
                <font color="<?=$color;?>" title="<?=$title;?>">
                    <?=$campos_ops[$j]['id_op'];?><br>
                </font>
            </a>
        <?
                }
/****************************************************************************************************/
        ?>
        </td>
    </tr>
<?
            /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
            encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
            da Biblioteca de Custos, na função pas_atrelados ...*/
            unset($vetor_pas_atrelados);
        }
?>
    <tr class='linhadestaque'>
        <td colspan='8'>
            <font color='yellow'>
                Total Geral: 
            </font>
        </td>
        <td align='right'>
            <?=segurancas::number_format($total_nec_atual1, 2, '.').' '.$campos[0]['sigla'];?>
        </td>
        <td>
            &nbsp;
        </td>
        <td align='right'>
            <?=segurancas::number_format($total_consumo_mensal1, 2, '.').' '.$campos[0]['sigla'];?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
	}
?>
</table>
<!--2ª Etapa-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Produto(s) Acabado(s) da 2ª Etapa Atrelado(s) a este PI
        </td>
    </tr>
<?
/*Serve para listar o P.A. que está atrelado ao custo da Segunda Etapa através desse Produto Insumo*/
	$sql = "SELECT gpa.`id_familia`, gpa.`nome`, pa.`referencia`, pa.`mmv`, pa.`operacao_custo`, pa.`operacao_custo_sub`, pa.`observacao`, 
                pac.`id_produto_acabado_custo`, pac.`id_produto_acabado`, pac.`qtde_lote`, pac.`peca_corte`, 
                pac.`comprimento_1`, pac.`comprimento_2` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE pac.`id_produto_insumo` = '$_GET[id_produto_insumo]' 
                AND pac.`operacao_custo` = '0' ORDER BY pa.`discriminacao` ";
	$campos_dados_gerais = bancos::sql($sql);
	$linhas = count($campos_dados_gerais);
//Somente Quando existir Itens ...
	if($linhas > 0) {
//Trago a densidade do produto insumo, para auxiliar no cálculo de $peso_aco_kg ...
            $sql = "SELECT pia.`densidade_aco` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                    WHERE pi.`id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
            $campos3 	= bancos::sql($sql);
            $densidade 	= (count($campos3) == 1) ? $campos3[0]['densidade_aco'] : '';
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            <font title="Operação de Custo" style='cursor:help'>
                O.C.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido Atrelado' style='cursor:help'>
                EC Atrel
            </font>
        </td>
        <td>
            <font title="Quantidade à Produzir" style='cursor:help'>
                Qtde Prod
            </font>
        </td>
        <td>
            <font title='Qtde Excedente s/ Embalagem' style='cursor:help'>
                Qtde Exced. s/ Emb
            </font>
        </td>
        <td>
            <font title="Comprimento da 2ª Etapa + 1 milímetro" style='cursor:help'>
                Comp + 1 mm
            </font>
        </td>
        <td>
            <font title="Estoque Baixa" style='cursor:help'>
                Est Baixa
            </font>
        </td>
        <td>
            <font title="Qtde do PI" style='cursor:help'>
                Qtde PI
            </font>
        </td>
        <td>
            <font title="Necessidade Atual" style='cursor:help'>
                Nec. Atual
            </font>
        </td>
        <td>
            MMV
        </td>
        <td>
            CMMV
        </td>
        <td>
            OP(s)
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="javascript:nova_janela('../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
            <?
                //Verifico se este P.A. tem pelo menos 1 OP atrelada que ainda não foi excluída ...
                $sql = "SELECT `id_op` 
                        FROM `ops` 
                        WHERE `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                        AND `status_finalizar` = '0' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos3 = bancos::sql($sql);
                if(count($campos3) == 1) {//Se estiver, eu mudo a cor deste ...
            ?>
                <font color="brown">
            <?
                }
            ?>
            <?
                echo intermodular::pa_discriminacao($campos_dados_gerais[$i]['id_produto_acabado'], 0);
                echo '<font color="black"><b> - Grupo PA: </b>'.$campos_dados_gerais[$i]['nome'].'</font>';
            ?>
            </a>
            &nbsp;
            <?
                if(!empty($campos_dados_gerais[$i]['observacao'])) echo "<img width='22'  height='18' title='".$campos_dados_gerais[$i]['observacao']."' src = '../../../imagem/olho.jpg'>";
            ?>
            &nbsp;
<?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                if($campos_dados_gerais[$i]['operacao_custo'] == 0) {//Industrial
?>
            <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&tela=2&ignorar_sessao=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" class='link'>
<?
                }else {
?>
            <a href="javascript:nova_janela('../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" class='link'>
<?
                }
?>
                <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar Custo' alt='Alterar Custo'>
            </a>
<?
            }
/****************************************************************************/
//Eu já antecipo esse código aqui porque vou precisar dessa variável p/ passar por parâmetro ...
            //$retorno = estoque_acabado::qtde_estoque($campos_dados_gerais[$i]['id_produto_acabado']);
            $compra                 = estoque_acabado::compra_producao($campos_dados_gerais[$i]['id_produto_acabado']);
            $baixas_pis_para_ops    = estoque_ic::baixas_pis_para_ops($campos_dados_gerais[$i]['id_produto_acabado'], $_GET['id_produto_insumo']);
            
            $sql = "SELECT SUM(`qtde_produzir`) AS qtde_produzindo 
                    FROM `ops` 
                    WHERE `status_finalizar` = '0' 
                    AND `ativo` = '1' 
                    AND `id_produto_acabado` = ".$campos_dados_gerais[$i]['id_produto_acabado'];
            $campos_produzir 	= bancos::sql($sql);
            $qtde_produzindo	= $campos_produzir[0]['qtde_produzindo'];// esse calculo é sem a entrada de PA parcial da OP mesmo
            $producao           = $qtde_produzindo - $baixas_pis_para_ops;
            //$producao = $retorno[2] - $baixas_pis_para_ops;
            $compra_producao 	= $producao + $compra;
/****************************************************************************/
?>
            &nbsp;
            <a href="javascript:nova_janela('../../producao/ops/visualizar_pis.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&nova_qtde_produzir=<?=$compra_producao;?>', 'POP', '', '', '', '', 600, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar PI's" class='link'>
                <img src = '../../../imagem/propriedades.png' title="Visualizar PI's" alt="Visualizar PI's" border='0'>
            </a>
<?
            //Somente para esses funcionários -> Roberto 62, Dárcio 98 porque programa ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
?>
            &nbsp;
            <a href="javascript:nova_janela('../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Pedidos - Últimos 6 meses' class='link'>
                <img src= '../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
<?
            }
?>
        </td>
        <td>
        <?
            if($campos_dados_gerais[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos_dados_gerais[$i]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos_dados_gerais[$i]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos_dados_gerais[$i]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
        <?
            $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_dados_gerais[$i]['id_produto_acabado']);
            $color_ec_pas_atrelados = ($retorno_pas_atrelados['total_ec_pas_atrelados'] < 0) ? 'red' : 'black';
        ?>
            <font color='<?=$color_ec_pas_atrelados;?>'>
                <?=number_format($retorno_pas_atrelados['total_ec_pas_atrelados'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <?=segurancas::number_format($producao, 2, '.');?>
        </td>
        <td align='right'>
        <?
            //Aqui eu só trago a Qtde Excedente do PA do Loop que NÃO está Embalada ...
            $sql = "SELECT SUM(`qtde`) AS quantidade
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                    AND `embalado` = 'N' ";
            $campos_excedente    = bancos::sql($sql);
            echo $campos_excedente[0]['quantidade'];
        ?>
        </td>
        <td>
        <?
            //Busca o Comprimento do Aço da 2ª Etapa do Custo + 1 mm quando é comprado cortado ...
            $sql = "SELECT `comprimento_1` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                    AND `operacao_custo` = '".$campos_dados_gerais[$i]['operacao_custo']."' limit 1 ";
            $campos_comprimento = bancos::sql($sql);
            echo $campos_comprimento[0]['comprimento_1'] + 1;
        ?>
        </td>
        <td align='right'>
            <a href="javascript:nova_janela('../../compras/estoque_i_c/ops_baixadas.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&id_produto_insumo=<?=$_GET['id_produto_insumo'];?>', 'OPS_BAIXADAS', '', '', '', '', 350, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='OP(s) Baixada(s)' style='cursor:help' class='link'>
            <?
                $baixa_pi = estoque_ic::baixa_pi($_GET['id_produto_insumo'], $campos_dados_gerais[$i]['id_produto_acabado']);
//Verifico se já foi dado a Baixa de PI para o PA, mas a OP ainda permaneceu em aberto, q não foram excluídas ...
                $sql = "SELECT bop.`status` 
                        FROM `ops` 
                        INNER JOIN `baixas_ops_vs_pis` bop ON bop.`id_op` = ops.`id_op` 
                        WHERE bop.`id_produto_insumo` = '$_GET[id_produto_insumo]' 
                        AND ops.`id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                        AND ops.`status_finalizar` = '0' 
                        AND ops.`ativo` = '1' ORDER BY bop.`id_baixa_op_vs_pi` DESC LIMIT 1 ";
                $campos_status_baixa = bancos::sql($sql);
                if(count($campos_status_baixa) == 1 && $campos_status_baixa[0]['status'] == 2) {
                    echo number_format($baixa_pi, 2, ',', '.');
                }else {
                    echo segurancas::number_format($baixa_pi, 2, '.');
                }
            ?>
            </a>
        </td>
        <td align='right'>
        <?
//Tenho que fazer esse Tratamento para que não de erro de Divisão por Zero ...
            $pecas_corte        = ($campos_dados_gerais[$i]['peca_corte'] == 0) ? 1 : $campos_dados_gerais[$i]['peca_corte'];
            $comprimento_total  = ($campos_dados_gerais[$i]['comprimento_1'] + $campos_dados_gerais[$i]['comprimento_2']) / 1000;
            //O cálculo p/ Pinos com PA(s) = 'ESP' é o mesmo com 5% a mais da Quantidade ...
            $fator_perda        = ($campos_dados_gerais[$i]['id_familia'] == 2 && $campos_dados_gerais[$i]['referencia'] == 'ESP') ? 1.10 : 1.05;
            $peso_aco_kg        = $densidade * $comprimento_total * $fator_perda;
            $peso_aco_kg/=      $pecas_corte;
            $peso_aco_kg        = round($peso_aco_kg, 4);
            echo number_format($peso_aco_kg, 4, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            //Somente se o PI for do Grupo 'EMB' então somo também na Necessidade a Qtde Excedente ...
            if($campos[0]['referencia'] == 'EMB') {
                $nec_atual2 = ($compra_producao + $campos_excedente[0]['quantidade']) * $peso_aco_kg;
            }else {
                $nec_atual2 = $peso_aco_kg * $compra_producao;
            }
            
            if($nec_atual2 != 0) echo segurancas::number_format($nec_atual2, 2, '.');
            $total_nec_atual2+= $nec_atual2;
            if($campos_dados_gerais[$i]['id_familia'] == 2 && $campos_dados_gerais[$i]['referencia'] == 'ESP' && $nec_atual2 != 0) {
        ?>
            &nbsp;<img src='../../../imagem/bloco_negro.jpg' width='6' height='6' title="Família = 'PINO' e PA = 'ESP', acréscimo de 5% na Quantidade" style='cursor:help'>
        <?
            }
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos_dados_gerais[$i]['mmv'], 2, '.');?>
        </td>
        <td>
        <?
            $consumo_mensal2 = $peso_aco_kg * $campos_dados_gerais[$i]['mmv'];
            if($consumo_mensal2 != 0) echo segurancas::number_format($consumo_mensal2, 2, '.');
            $total_consumo_mensal2+= $consumo_mensal2;
        ?>
        </td>
        <td>
        <?
/****************************************************************************************************/
//Busca das OP(s) nos últimos 6 meses ...
            $sql = "SELECT ops.`id_op`, bop.`status` 
                    FROM `ops` 
                    INNER JOIN `baixas_ops_vs_pis` bop ON bop.`id_op` = ops.`id_op` 
                    WHERE bop.`id_produto_insumo` = '$_GET[id_produto_insumo]' 
                    AND ops.`id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                    AND (ops.`data_emissao` >= DATE_ADD('$data_atual', INTERVAL -180 DAY)) 
                    AND ops.`ativo` = '1' ";
            $campos_ops = bancos::sql($sql);
            $linhas_ops = count($campos_ops);
            for($j = 0; $j < $linhas_ops; $j++) {
                if($campos_ops[$j]['status'] == 2) {//Se já foi dado Baixa Total na OP, mostra em Verde ...
                    $color = 'green';
                    $title = 'OP Baixada';
                }else {//Mostra em Vermelho, caso esteja com Baixa Parcial ou Estorno ...
                    $color = 'red';
                    $title = 'OP em Aberto';
                }
        ?>
            <!--******Esse parâmetro é para que essa tela seja aberta como Pop-UP e não mostre os botões 
            do fim da Tela******-->
            <a href="javascript:nova_janela('../../producao/ops/alterar.php?passo=2&id_op=<?=$campos_ops[$j]['id_op'];?>&pop_up=1', 'DETALHES', '', '', '', '', '480', '880', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de OP' class='link'>
                <font color="<?=$color;?>" title="<?=$title;?>">
                    <?=$campos_ops[$j]['id_op'];?><br>
                </font>
            </a>
        <?
            }
/****************************************************************************************************/
        ?>
        </td>
    </tr>
<?
            /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
            encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
            da Biblioteca de Custos, na função pas_atrelados ...*/
            unset($vetor_pas_atrelados);
        }
?>
    <tr class='linhadestaque'>
        <td colspan='8'>
            <font color='yellow'>
                Total Geral: 
            </font>
        </td>
        <td align='right'>
            <?=segurancas::number_format($total_nec_atual2, 2, '.');?>
        </td>
        <td>
            &nbsp;
        </td>
        <td align='right'>
            <?=segurancas::number_format($total_consumo_mensal2, 2, '.');?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
	}
?>
</table>
<!--3ª Etapa-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Produto(s) Acabado(s) da 3ª Etapa Atrelado(s) a este PI
        </td>
    </tr>
<?
/*Serve para listar todos os P.A.(s) que estão atrelados ao custo da Terceira Etapa através 
desse Produto Insumo*/
	$sql = "SELECT gpa.`nome`, pa.`mmv`, pa.`operacao_custo`, pa.`operacao_custo_sub`, pa.`observacao`, pac.`id_produto_acabado`, pp.`qtde` 
                FROM `pacs_vs_pis` pp 
                INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` AND pac.`operacao_custo` = '0' 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE pp.`id_produto_insumo` = '$_GET[id_produto_insumo]' ORDER BY pa.`discriminacao` ";
	$campos_dados_gerais = bancos::sql($sql);
	$linhas = count($campos_dados_gerais);
//Somente Quando existir Itens ...
	if($linhas > 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            <font title="Operação de Custo" style='cursor:help'>
                O.C.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido Atrelado' style='cursor:help'>
                EC Atrel
            </font>
        </td>
        <td>
            <font title="Quantidade à Produzir" style='cursor:help'>
                Qtde Prod
            </font>
        </td>
        <td>
            <font title='Qtde Excedente s/ Embalagem' style='cursor:help'>
                Qtde Exced. s/ Emb
            </font>
        </td>
        <td>
            <font title="Estoque Baixa" style='cursor:help'>
                Est Baixa
            </font>
        </td>
        <td>
            <font title="Qtde do PI" style='cursor:help'>
                Qtde PI
            </font>
        </td>
        <td>
            <font title="Necessidade Atual" style='cursor:help'>
                Nec. Atual
            </font>
        </td>
        <td>
            MMV
        </td>
        <td>
            CMMV
        </td>
        <td>
            OP(s)
        </td>
    </tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="javascript:nova_janela('../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
            <?
//Verifico se este P.A., está atrelado a alguma OP que ainda não foi excluída ...
                $sql = "SELECT id_op 
                        FROM `ops` 
                        WHERE `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                        AND `status_finalizar` = '0' 
                        AND `ativo` = '1' ";
                $campos3 = bancos::sql($sql);
                if(count($campos3) == 1) {//Se estiver, eu mudo a cor deste ...
            ?>
                    <font color="brown">
            <?
                }
                echo intermodular::pa_discriminacao($campos_dados_gerais[$i]['id_produto_acabado'], 0);
                echo '<font color="black"><b> - Grupo PA: </b>'.$campos_dados_gerais[$i]['nome'].'</font>';
            ?>
            </a>
            &nbsp;
            <?
                if(!empty($campos_dados_gerais[$i]['observacao'])) echo "<img width='22'  height='18' title='".$campos_dados_gerais[$i]['observacao']."' src = '../../../imagem/olho.jpg'>";
            ?>
            &nbsp;
<?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {

                if($campos_dados_gerais[$i]['operacao_custo'] == 0) {//Industrial
?>
            <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&tela=2&ignorar_sessao=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" class='link'>
<?
                }else {
?>
            <a href="javascript:nova_janela('../../producao/revenda/custo_revenda.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" class='link'>
<?
                }
?>
                <img src = "../../../imagem/menu/alterar.png" border='0' title="Alterar Custo" alt="Alterar Custo">
            </a>
<?
            }
/****************************************************************************/
//Eu já antecipo esse código aqui porque vou precisar dessa variável p/ passar por parâmetro ...
            //$retorno = estoque_acabado::qtde_estoque($campos_dados_gerais[$i]['id_produto_acabado']);
            $compra                 = estoque_acabado::compra_producao($campos_dados_gerais[$i]['id_produto_acabado']);
            $baixas_pis_para_ops    = estoque_ic::baixas_pis_para_ops($campos_dados_gerais[$i]['id_produto_acabado'], $_GET['id_produto_insumo']);
            
            $sql = "SELECT SUM(`qtde_produzir`) AS qtde_produzindo 
                    FROM `ops` 
                    WHERE `status_finalizar` = '0' 
                    AND `ativo` = '1' 
                    AND `id_produto_acabado` = ".$campos_dados_gerais[$i]['id_produto_acabado'];
            $campos_produzir 	= bancos::sql($sql);
            $qtde_produzindo 	= $campos_produzir[0]['qtde_produzindo']; // esse calculo é sem a entrada de PA parcial da OP mesmo
            $producao 		= $qtde_produzindo - $baixas_pis_para_ops;
            //$producao = $retorno[2] - $baixas_pis_para_ops;			
            $compra_producao 	= $producao + $compra;
/****************************************************************************/
?>
            &nbsp;
            <a href="javascript:nova_janela('../../producao/ops/visualizar_pis.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&nova_qtde_produzir=<?=$compra_producao;?>', 'POP', '', '', '', '', 600, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar PI's" class='link'>
                <img src = '../../../imagem/propriedades.png' title="Visualizar PI's" alt="Visualizar PI's" border='0'>
            </a>
<?
//Somente para esses funcionários -> Roberto 62, Dárcio 98 porque programa ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
?>
            &nbsp;
            <a href="javascript:nova_janela('../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Pedidos - Últimos 6 meses' class='link'>
                <img src= '../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
<?
            }
?>
        </td>
        <td>
        <?
            if($campos_dados_gerais[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos_dados_gerais[$i]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos_dados_gerais[$i]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos_dados_gerais[$i]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
        <?
            $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_dados_gerais[$i]['id_produto_acabado']);
            $color_ec_pas_atrelados = ($retorno_pas_atrelados['total_ec_pas_atrelados'] < 0) ? 'red' : 'black';
        ?>
            <font color='<?=$color_ec_pas_atrelados;?>'>
                <?=number_format($retorno_pas_atrelados['total_ec_pas_atrelados'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <?=segurancas::number_format($producao, 2, '.');?>
        </td>
        <td align='right'>
        <?
            //Aqui eu só trago a Qtde Excedente do PA do Loop que NÃO está Embalada ...
            $sql = "SELECT SUM(`qtde`) AS quantidade
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                    AND `embalado` = 'N' ";
            $campos_excedente    = bancos::sql($sql);
            echo $campos_excedente[0]['quantidade'];
        ?>
        </td>
        <td align='right'>
            <a href="javascript:nova_janela('../../compras/estoque_i_c/ops_baixadas.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&id_produto_insumo=<?=$_GET['id_produto_insumo'];?>', 'OPS_BAIXADAS', '', '', '', '', 350, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='OP(s) Baixada(s)' style='cursor:help' class='link'>
            <?
                $baixa_pi = estoque_ic::baixa_pi($_GET['id_produto_insumo'], $campos_dados_gerais[$i]['id_produto_acabado']);
                echo segurancas::number_format($baixa_pi, 2, '.');
            ?>
            </a>
        </td>
        <td>
            <?=segurancas::number_format($campos_dados_gerais[$i]['qtde'], 1, '.');?>
        </td>
        <td>
        <?
            //Somente se o PI for do Grupo 'EMB' então somo também na Necessidade a Qtde Excedente ...
            if($campos[0]['referencia'] == 'EMB') {
                $nec_atual3 = ($campos_dados_gerais[$i]['qtde'] + $campos_excedente[0]['quantidade']) * $compra_producao;
            }else {
                $nec_atual3 = $campos_dados_gerais[$i]['qtde'] * $compra_producao;
            }

            if($nec_atual3 != 0) echo segurancas::number_format($nec_atual3, 2, '.');
            $total_nec_atual3+= $nec_atual3;
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos_dados_gerais[$i]['mmv'], 2, '.');?>
        </td>
        <td>
        <?
            $consumo_mensal3 = $campos_dados_gerais[$i]['qtde'] * $campos_dados_gerais[$i]['mmv'];
            if($consumo_mensal3 != 0) echo segurancas::number_format($consumo_mensal3, 2, '.');
            $total_consumo_mensal3+= $consumo_mensal3;
        ?>
        </td>
        <td>
        <?
/****************************************************************************************************/
//Busca das OP(s) nos últimos 6 meses ...
            $sql = "SELECT ops.id_op, bop.status 
                    FROM `ops` 
                    INNER JOIN `baixas_ops_vs_pis` bop ON bop.id_op = ops.id_op 
                    WHERE bop.id_produto_insumo = '$_GET[id_produto_insumo]' 
                    AND ops.id_produto_acabado = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                    AND (ops.data_emissao >= DATE_ADD('$data_atual', INTERVAL -180 DAY)) 
                    AND ops.ativo = '1' ";
            $campos_ops = bancos::sql($sql);
            $linhas_ops = count($campos_ops);
            for($j = 0; $j < $linhas_ops; $j++) {
                if($campos_ops[$j]['status'] == 2) {//Se já foi dado Baixa Total na OP, mostra em Verde ...
                    $color = 'green';
                    $title = 'OP Baixada';
                }else {//Mostra em Vermelho, caso esteja com Baixa Parcial ou Estorno ...
                    $color = 'red';
                    $title = 'OP em Aberto';
                }
        ?>
            <!--******Esse parâmetro é para que essa tela seja aberta como Pop-UP e não mostre os botões 
            do fim da Tela******-->
            <a href="javascript:nova_janela('../../producao/ops/alterar.php?passo=2&id_op=<?=$campos_ops[$j]['id_op'];?>&pop_up=1', 'DETALHES', '', '', '', '', '480', '880', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de OP' class='link'>
                <font color="<?=$color;?>" title="<?=$title;?>">
                    <?=$campos_ops[$j]['id_op'];?><br>
                </font>
            </a>
        <?
            }
/****************************************************************************************************/
        ?>
        </td>
    </tr>
<?
                    /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
                    encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
                    da Biblioteca de Custos, na função pas_atrelados ...*/
                    unset($vetor_pas_atrelados);
		}
?>
    <tr class='linhadestaque'>
        <td colspan='8'>
            <font color='yellow'>
                Total Geral: 
            </font>
        </td>
        <td align='right'>
            <?=segurancas::number_format($total_nec_atual3, 2, '.');?>
        </td>
        <td>
            &nbsp;
        </td>
        <td align='right'>
            <?=segurancas::number_format($total_consumo_mensal3, 2, '.');?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
	}
?>
</table>
<!--6ª Etapa-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Produto(s) Acabado(s) da 6ª Etapa Atrelado(s) a este PI
        </td>
    </tr>
<?
//Serve para listar todos os P.A.(s) que estão atrelados ao custo da Sexta Etapa através desse Produto Insumo ...
	$sql = "SELECT gpa.`nome`, pa.`mmv`, pa.`operacao_custo`, pa.`operacao_custo_sub`, pa.`observacao`, pac.`id_produto_acabado`, ppu.`qtde` 
                FROM `pacs_vs_pis_usis` ppu 
                INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = ppu.`id_produto_acabado_custo` AND pac.`operacao_custo` = '0' 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE ppu.`id_produto_insumo` = '$_GET[id_produto_insumo]' ORDER BY pa.`discriminacao` ";
	$campos_dados_gerais = bancos::sql($sql);
	$linhas = count($campos_dados_gerais);
//Somente Quando existir Itens ...
	if($linhas > 0) {
?>
    <tr>
        <td></td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            <font title="Operação de Custo" style='cursor:help'>
                O.C.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido Atrelado' style='cursor:help'>
                EC Atrel
            </font>
        </td>
        <td>
            <font title="Quantidade à Produzir" style='cursor:help'>
                Qtde Prod
            </font>
        </td>
        <td>
            <font title='Qtde Excedente s/ Embalagem' style='cursor:help'>
                Qtde Exced. s/ Emb
            </font>
        </td>
        <td>
            <font title="Estoque Baixa" style='cursor:help'>
                Est Baixa
            </font>
        </td>
        <td>
            <font title="Qtde do PI" style='cursor:help'>
                Qtde PI
            </font>
        </td>
        <td>
            <font title="Necessidade Atual" style='cursor:help'>
                Nec. Atual
            </font>
        </td>
        <td>
            MMV
        </td>
        <td>
            CMMV
        </td>
        <td>
            OP(s)
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="javascript:nova_janela('../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Estoque" class='link'>
            <?
//Verifico se este P.A., está atrelado a alguma OP que ainda não foi excluída ...
                $sql = "SELECT `id_op` 
                        FROM `ops` 
                        WHERE `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                        AND `ativo` = '1' 
                        AND `status_finalizar` = '0' ";
                $campos3 = bancos::sql($sql);
                if(count($campos3) == 1) {//Se estiver, eu mudo a cor deste ...
            ?>
                    <font color='brown'>
            <?
                }
                echo intermodular::pa_discriminacao($campos_dados_gerais[$i]['id_produto_acabado'], 0);
                echo '<font color="black"><b> - Grupo PA: </b>'.$campos_dados_gerais[$i]['nome'].'</font>';
            ?>
            </a>
            &nbsp;
            <?
                if(!empty($campos_dados_gerais[$i]['observacao'])) echo "<img width='22'  height='18' title='".$campos_dados_gerais[$i]['observacao']."' src = '../../../imagem/olho.jpg'>";
            ?>
            &nbsp;
<?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                if($campos_dados_gerais[$i]['operacao_custo'] == 0) {//Industrial
?>
            <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&tela=2&ignorar_sessao=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" class='link'>
<?
                }else {
?>
            <a href="javascript:nova_janela('../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" class='link'>
<?
                }
?>
                <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar Custo' alt='Alterar Custo'>
            </a>
<?
            }
/****************************************************************************/
//Eu já antecipo esse código aqui porque vou precisar dessa variável p/ passar por parâmetro ...
            //$retorno = estoque_acabado::qtde_estoque($campos_dados_gerais[$i]['id_produto_acabado']);
            $compra                 = estoque_acabado::compra_producao($campos_dados_gerais[$i]['id_produto_acabado']);
            $baixas_pis_para_ops    = estoque_ic::baixas_pis_para_ops($campos_dados_gerais[$i]['id_produto_acabado'], $_GET['id_produto_insumo']);
            
            $sql = "SELECT SUM(`qtde_produzir`) qtde_produzindo 
                    FROM `ops` 
                    WHERE `status_finalizar` = '0' 
                    AND `ativo` = '1' 
                    AND `id_produto_acabado` = ".$campos_dados_gerais[$i]['id_produto_acabado'];
            $campos_produzir 	= bancos::sql($sql);
            $qtde_produzindo	= $campos_produzir[0]['qtde_produzindo']; // esse calculo é sem a entrada de PA parcial da OP mesmo
            $producao 		= $qtde_produzindo - $baixas_pis_para_ops;
            //$producao = $retorno[2] - $baixas_pis_para_ops;
            $compra_producao 	= $producao + $compra;
/****************************************************************************/
?>
            &nbsp;
            <a href="javascript:nova_janela('../../producao/ops/visualizar_pis.php?id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&nova_qtde_produzir=<?=$compra_producao;?>', 'POP', '', '', '', '', 600, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar PI's" class='link'>
                <img src = '../../../imagem/propriedades.png' title="Visualizar PI's" alt="Visualizar PI's" border='0'>
            </a>
<?
//Somente para esses funcionários -> Roberto 62, Dárcio 98 porque programa ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
?>
            &nbsp;
            <a href="javascript:nova_janela('../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos_dados_gerais[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Pedidos - Últimos 6 meses' class='link'>
                <img src= '../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
<?
            }
?>
        </td>
        <td>
        <?
            if($campos_dados_gerais[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos_dados_gerais[$i]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos_dados_gerais[$i]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos_dados_gerais[$i]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
        <?
            $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_dados_gerais[$i]['id_produto_acabado']);
            $color_ec_pas_atrelados = ($retorno_pas_atrelados['total_ec_pas_atrelados'] < 0) ? 'red' : 'black';
        ?>
            <font color='<?=$color_ec_pas_atrelados;?>'>
                <?=number_format($retorno_pas_atrelados['total_ec_pas_atrelados'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <?=segurancas::number_format($producao, 2, '.');?>
        </td>
        <td align='right'>
        <?
            //Aqui eu só trago a Qtde Excedente do PA do Loop que NÃO está Embalada ...
            $sql = "SELECT SUM(`qtde`) AS quantidade 
                    FROM estoques_excedentes 
                    WHERE `id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                    AND `embalado` = 'N' ";
            $campos_excedente    = bancos::sql($sql);
            echo $campos_excedente[0]['quantidade'];
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos_dados_gerais[$i]['qtde'], 2, '.');?>
        </td>
        <td>
        <?
            //Somente se o PI for do Grupo 'EMB' então somo também na Necessidade a Qtde Excedente ...
            if($campos[0]['referencia'] == 'EMB') {
                $nec_atual6 = ($campos_dados_gerais[$i]['qtde'] + $campos_excedente[0]['quantidade']) * $compra_producao;
            }else {
                $nec_atual6 = $campos_dados_gerais[$i]['qtde'] * $compra_producao;
            }

            if($nec_atual6 != 0) echo segurancas::number_format($nec_atual6, 2, '.');
            $total_nec_atual6+= $nec_atual6;
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos_dados_gerais[$i]['mmv'], 2, '.');?>
        </td>
        <td>
        <?
            $consumo_mensal6 = $campos_dados_gerais[$i]['qtde'] * $mmv;
            if($consumo_mensal6 != 0) echo segurancas::number_format($consumo_mensal6, 2, '.');
            $total_consumo_mensal6+= $consumo_mensal6;
        ?>
        </td>
        <td>
        <?
/****************************************************************************************************/
//Busca das OP(s) nos últimos 6 meses ...
            $sql = "SELECT ops.`id_op`, bop.`status` 
                    FROM `ops` 
                    INNER JOIN `baixas_ops_vs_pis` bop ON bop.`id_op` = ops.`id_op` 
                    WHERE bop.`id_produto_insumo` = '$_GET[id_produto_insumo]' 
                    AND ops.`id_produto_acabado` = '".$campos_dados_gerais[$i]['id_produto_acabado']."' 
                    AND (ops.`data_emissao` >= DATE_ADD('$data_atual', INTERVAL -180 DAY)) 
                    AND ops.`ativo` = '1' ";
            $campos_ops = bancos::sql($sql);
            $linhas_ops = count($campos_ops);
            for($j = 0; $j < $linhas_ops; $j++) {
                if($campos_ops[$j]['status'] == 2) {//Se já foi dado Baixa Total na OP, mostra em Verde ...
                    $color = 'green';
                    $title = 'OP Baixada';
                }else {//Mostra em Vermelho, caso esteja com Baixa Parcial ou Estorno ...
                    $color = 'red';
                    $title = 'OP em Aberto';
                }
        ?>
            <!--******Esse parâmetro é para que essa tela seja aberta como Pop-UP e não mostre os botões 
            do fim da Tela******-->
            <a href="javascript:nova_janela('../../producao/ops/alterar.php?passo=2&id_op=<?=$campos_ops[$j]['id_op'];?>&pop_up=1', 'DETALHES', '', '', '', '', '480', '880', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de OP' class='link'>
                <font color="<?=$color;?>" title="<?=$title;?>">
                    <?=$campos_ops[$j]['id_op'];?><br>
                </font>
            </a>
        <?
                }
/****************************************************************************************************/
        ?>
        </td>
    </tr>
<?
                /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
                encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
                da Biblioteca de Custos, na função pas_atrelados ...*/
                unset($vetor_pas_atrelados);
            }
?>
    <tr class='linhadestaque'>
        <td colspan='8'>
            Total Geral: 
        </td>
        <td align='right'>
            <?=segurancas::number_format($total_nec_atual6, 2, '.');?>
        </td>
        <td>
            &nbsp;
        </td>
        <td align='right'>
            <?=segurancas::number_format($total_consumo_mensal6, 2, '.');?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
	}
?>
</table>
<!--Continuação da Tela-->
<?
/******************************************************************************/
    //Verifico se existe pelo menos um Pedido com este Item ...
    $sql = "SELECT `id_pedido` 
            FROM `itens_pedidos` 
            WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
    $campos_pedidos = bancos::sql($sql);
    $linhas_pedidos = count($campos_pedidos);
    //Verifico se esse PI está atrelado a Estoque ...
    $sql = "SELECT `qtde` 
            FROM `estoques_insumos` 
            WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
    $campos_estoque_pi = bancos::sql($sql);
    $linhas_estoque_pi = count($campos_estoque_pi);
    if($linhas_pedidos > 0 || $linhas_estoque_pi > 0) {
?>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Locais Atrelados
        </td>
    </tr>
<?
        if($linhas_pedidos > 0) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>* Esse produto está atrelado a(s) Pedido(s) de Compra(s)</b>
        </td>
    </tr>
<?
        }
        if($linhas_estoque_pi > 0) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>* Esse produto está atrelado ao Estoque com Quantidade: <?=number_format($campos_estoque_pi[0]['qtde'], 2, ',', '.');?></b>
        </td>
    </tr>
<?
        }
    }
/******************************************************************************/
?>
</table>
<!--Continuação da Tela-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
<?
//Aqui eu visualizo todas as pendências do respectivo PI ...
    $nao_chamar_biblioteca = 1;
    echo '<br>';
    require('../../compras/estoque_i_c/nivel_estoque/pendencias_item.php');
?>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Links em <font color='green'><b>Verde</b></font> significam que o P.A. está atrelado a alguma <font color='green'><b>OP Baixada</b></font> ou <font color='green'><b>OP s/ baixa do Almoxarifado</b></font>.
* <font color='darkblue'><b>Coluna de OP(s)</b></font> - Lista toda(s) as OP(s) dos últimos 6 meses que contém o PI (Matéria Prima) atrelado.
</pre>