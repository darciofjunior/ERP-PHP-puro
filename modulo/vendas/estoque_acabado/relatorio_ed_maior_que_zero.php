<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

/*Esse trecho de tela foi feito em um arquivo à parte, p/ evitar de recarregar toda a tela do 
Estoque Acabado que daí seria muito lento, achamos mais fácil e mais rápido recarregar apenas
o Iframe que é exatamente esse arquivo na hora em que o usuário altera o Prazo de Entrega ...*/
$data_atual_mais_sete = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chkt_mostrar_componentes 	= $_POST['chkt_mostrar_componentes'];
    $chkt_est_disp_comp_zero 	= $_POST['chkt_est_disp_comp_zero'];
    $chkt_exibir_esp            = $_POST['chkt_exibir_esp'];
    $cmb_familia                = $_POST['cmb_familia'];
    $cmb_grupo_pa               = $_POST['cmb_grupo_pa'];
    $hidden_operacao_custo      = $_POST['hidden_operacao_custo'];
    $cmb_operacao_custo         = $_POST['cmb_operacao_custo'];
    $hidden_operacao_custo_sub 	= $_POST['hidden_operacao_custo_sub'];
    $cmb_operacao_custo_sub 	= $_POST['cmb_operacao_custo_sub'];
    $txt_fornecedor             = $_POST['txt_fornecedor'];
}else {
    $chkt_mostrar_componentes 	= $_GET['chkt_mostrar_componentes'];
    $chkt_est_disp_comp_zero 	= $_GET['chkt_est_disp_comp_zero'];
    $chkt_exibir_esp            = $_GET['chkt_exibir_esp'];
    $cmb_familia                = $_GET['cmb_familia'];
    $cmb_grupo_pa               = $_GET['cmb_grupo_pa'];
    $hidden_operacao_custo      = $_GET['hidden_operacao_custo'];
    $cmb_operacao_custo         = $_GET['cmb_operacao_custo'];
    $hidden_operacao_custo_sub 	= $_GET['hidden_operacao_custo_sub'];
    $cmb_operacao_custo_sub 	= $_GET['cmb_operacao_custo_sub'];
    $txt_fornecedor             = $_GET['txt_fornecedor'];
}

//Se não estiver habilitado o checkbox, só não mostra os P.A. q pertecem a família de Componentes
if(empty($chkt_mostrar_componentes)) $condicao = " AND gpa.`id_familia` <> '23' ";
if(!empty($chkt_est_disp_comp_zero)) { // c tiver checked
    $condicao.= " AND (ea.`qtde_disponivel` - ea.`qtde_pendente`) < '0'  ";
    $order_by = "(-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * ( 1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) DESC "; // aqui nao posso passar o apelido por causa da sql de paginacao ela nao intende este apelido e dar erro
}else {
    $order_by = " pa.`discriminacao` ";
}
//Significa que o usuário só quer ver os PA(s) que são normais de linha
if(empty($chkt_exibir_esp)) $condicao_esp = " AND pa.`referencia` <> 'ESP' ";
if($cmb_familia == '')      $cmb_familia = '%';
if($cmb_grupo_pa == '')     $cmb_grupo_pa = '%';
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
if($hidden_operacao_custo == 1) {//Operação de Custo = Industrial
    $cmb_operacao_custo = 0;
}else if($hidden_operacao_custo == 2) {//Operação de Custo = Revenda
    $cmb_operacao_custo = 1;
}else {//Independente da Operação de Custo
    if($cmb_operacao_custo == '') { $cmb_operacao_custo = "%";}
}
//Segunda adaptação
if($hidden_operacao_custo_sub == 1) {//Sub-Operação de Custo = Industrial
    $cmb_operacao_custo_sub = 0;
}else if($hidden_operacao_custo_sub == 2) {//Sub-Operação de Custo = Revenda
    $cmb_operacao_custo_sub = 1;
}else {//Independente da Sub-Operação de Custo
    if($cmb_operacao_custo_sub == '') { $cmb_operacao_custo_sub = "%";}
}
//Se estiver preenchido o "Fornecedor Default" ...
if(!empty($txt_fornecedor)) {
/*Aqui busco todos os PA do "Fornecedor Default", mas somente os PA que são do Tipo PI's, e q são 
normais de linha*/
    $sql = "SELECT pa.`id_produto_acabado` 
            FROM `fornecedores` f 
            INNER JOIN `produtos_insumos` pi ON pi.`id_fornecedor_default` = f.`id_fornecedor` AND pi.`id_fornecedor_default` > '0' AND pi.`ativo` = '1' 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`ativo` = '1' $condicao_esp 
            WHERE f.`razaosocial` LIKE '%$txt_fornecedor%' ORDER BY pa.`id_produto_acabado` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) $id_produto_acabados.= $campos[$i]['id_produto_acabado'].', ';
    $id_produto_acabados = substr($id_produto_acabados, 0, strlen($id_produto_acabados) - 2);
    $condicao_pas = "and pa.id_produto_acabado in ($id_produto_acabados) ";
}

if(!empty($chkt_prod_prazo_atual_sete)) {
    //if($operacao_custo == 0) {//P.A. Industrial
    //Faço uma verificação de Toda(s) as OP(s) que estão em abertas e que possuem esse PA atrelado ...
    $sql_smart = "SELECT `id_produto_acabado` 
                    FROM `ops` 
                    WHERE (DATE_ADD('$data_atual', INTERVAL -7 DAY) > SUBSTRING(`data_ocorrencia`, 1, 10) || `data_ocorrencia` = '0000-00-00 00:00:00') AND `status_finalizar` = '0' AND `ativo` = '1' ";
    //}else {//P.A. Revenda
    $sql_smart.= " UNION 
                    SELECT ea.`id_produto_acabado` 
                    FROM `estoques_acabados` ea 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` 
                    WHERE `operacao_custo` = '1' 
                    AND (DATE_ADD('$data_atual', INTERVAL -7 DAY) > SUBSTRING(ea.`data_atualizacao_prazo_ent`, 1, 10) || ea.`data_atualizacao_prazo_ent` = '0000-00-00 00:00:00') ";
    $campos_sql = bancos::sql($sql_smart);
    //}
}else {
    $sql_smart = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `ativo` = '1' ";
    $campos_sql = bancos::sql($sql_smart);
}
$linhas = count($campos_sql);
for($i = 0; $i < $linhas; $i++) {
    $ids_produtos.= $campos_sql[$i]['id_produto_acabado'].', ';
}
$ids_produtos = substr($ids_produtos, 0, strlen($ids_produtos) - 2);
        
//Se essa opção estiver marcada, então eu só mostro os P.A(s) que são Top(s) ...
if(!empty($chkt_mostrar_top)) $condicao_top = " AND pa.status_top >= '1' ";
	
$sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`mmv` 
        FROM `estoques_acabados` ea 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' $condicao_esp 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_grupo_pa` LIKE '$cmb_grupo_pa' 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` LIKE '$cmb_familia' 
        WHERE ea.`qtde_disponivel` > '0' 
        AND pa.`operacao_custo` LIKE '%$cmb_operacao_custo%' 
        AND pa.`operacao_custo_sub` LIKE '$cmb_operacao_custo_sub' 
        AND pa.`ativo` = '1' $condicao_pas $condicao $condicao_top 
        AND pa.`id_produto_acabado` IN ($ids_produtos) 
        GROUP BY pa.`id_produto_acabado` 
        ORDER BY $order_by ";
$campos = bancos::sql($sql, $inicio, 500, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório ED > 0 ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
}else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Relatório ED > 0
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Ref
        </td>
        <td>
            Produto
        </td>
        <td>
            Compra
        </td>
        <td>
            Produção
        </td>
        <td>
            OE
        </td>
        <td>
            <font title='Estoque Real' style='cursor:help'>
                ER
            </font>
        </td>
        <td>
            <font title='Estoque Disponível' style='cursor:help'>
                ED
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido' style='cursor:help'>
                EC
            </font>
        </td>
        <td>
            EC p/x <br>meses
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $compra             = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
        
        $estoque_produto        = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 0);
        $quantidade_estoque     = $estoque_produto[0];
        $producao               = $estoque_produto[2];
        $qtde_disponivel        = $estoque_produto[3];
        $estoque_comprometido   = $estoque_produto[8];
        $qtde_oe_em_aberto      = $estoque_produto[11];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='left'>
            <a href = '../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>' class='html5lightbox'>
                <?=$campos[$i]['referencia'];?>
            </a>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td align='right'>
            <?=number_format($compra, 0, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($producao, 0, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($qtde_oe_em_aberto, 0, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $font = ($quantidade_estoque < 0) ? "<font color='red'>" : '';
            echo $font.number_format($quantidade_estoque, 0, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $font = ($qtde_disponivel < 0) ? "<font color='red'>" : '';
            echo $font.number_format($qtde_disponivel, 0, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $font = ($estoque_comprometido < 0) ? "<font color='red'>" : '';
            echo $font.number_format($estoque_comprometido, 0, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['mmv'] == 0) {//Se não existir MMV, não faz pq dá derro de Divisão por Zero ...
                echo '<b>S/ MMV</b>';
            }else {
                if($estoque_comprometido / $campos[$i]['mmv'] < 0) {
                    echo '0';
                }else {
                    echo segurancas::number_format($estoque_comprometido / $campos[$i]['mmv'], 1, '.');
                }
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>