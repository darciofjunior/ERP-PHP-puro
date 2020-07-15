<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

/*Esse trecho de tela foi feito em um arquivo � parte, p/ evitar de recarregar toda a tela do 
Estoque Acabado que da� seria muito lento, achamos mais f�cil e mais r�pido recarregar apenas
o Iframe que � exatamente esse arquivo na hora em que o usu�rio altera o Prazo de Entrega ...*/
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

//Se n�o estiver habilitado o checkbox, s� n�o mostra os P.A. q pertecem a fam�lia de Componentes
if(empty($chkt_mostrar_componentes)) $condicao = " AND gpa.`id_familia` <> '23' ";

if(!empty($chkt_est_disp_comp_zero)) { // c tiver checked
    $condicao.= " AND (ea.qtde_disponivel - ea.qtde_pendente) < '0'  ";
    $order_by = "(-(ea.qtde_disponivel - ea.qtde_pendente) * (pa.preco_unitario * (1 - ged.desc_base_a_nac / 100) * (1-ged.desc_base_b_nac / 100) * (1 + ged.acrescimo_base_nac / 100)) * ged.desc_medio_pa) DESC "; // aqui nao posso passar o apelido por causa da sql de paginacao ela nao intende este apelido e dar erro
}else {
    $order_by = "pa.discriminacao ";
}

//Significa que o usu�rio s� quer ver os PA(s) que s�o normais de linha
if(empty($chkt_exibir_esp)) $condicao_esp = " AND pa.`referencia` <> 'ESP' ";

if($cmb_familia == '') 	$cmb_familia = '%';
if($cmb_grupo_pa == '') $cmb_grupo_pa = '%';
/*Aqui eu tive que fazer essa adapta��o, porque estava dando erro de par�metro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
if($hidden_operacao_custo == 1) {//Opera��o de Custo = Industrial
    $cmb_operacao_custo = 0;
}else if($hidden_operacao_custo == 2) {//Opera��o de Custo = Revenda
    $cmb_operacao_custo = 1;
}else {//Independente da Opera��o de Custo
    if($cmb_operacao_custo == '') { $cmb_operacao_custo = "%";}
}
//Segunda adapta��o
if($hidden_operacao_custo_sub == 1) {//Sub-Opera��o de Custo = Industrial
    $cmb_operacao_custo_sub = 0;
}else if($hidden_operacao_custo_sub == 2) {//Sub-Opera��o de Custo = Revenda
    $cmb_operacao_custo_sub = 1;
}else {//Independente da Sub-Opera��o de Custo
    if($cmb_operacao_custo_sub == '') $cmb_operacao_custo_sub = '%';
}

//Se estiver preenchido o "Fornecedor Default" ...
if(!empty($txt_fornecedor)) {
/*Aqui busco todos os PA do "Fornecedor Default", mas somente os PA que s�o do Tipo PI's, e q s�o 
normais de linha*/
    $sql = "SELECT pa.id_produto_acabado 
            FROM `fornecedores` f 
            INNER JOIN `produtos_insumos` pi ON pi.`id_fornecedor_default` = f.`id_fornecedor` AND pi.`id_fornecedor_default` > '0' AND pi.`ativo` = '1' 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`ativo` = '1' $condicao_esp 
            WHERE f.`razaosocial` LIKE '%$txt_fornecedor%' ORDER BY pa.id_produto_acabado ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {//Encontrou pelo menos 1 PA do Respectivo "Fornecedor" passado por par�metro ...
        for($i = 0; $i < $linhas; $i++) $id_produto_acabados.=$campos[$i]['id_produto_acabado'].', ';
        $id_produto_acabados    = substr($id_produto_acabados, 0, strlen($id_produto_acabados) - 2);
        $condicao_pas           = " AND pa.`id_produto_acabado` IN ($id_produto_acabados) ";
    }else {//N�o encontrou nenhum PA ...
        $condicao_pas           = " AND pa.`id_produto_acabado` = '0' ";
    }
}

//Significa que eu desejo ver todos os PA(s) em que O Prazo de Atualiza��o � > do que 7 dias ...
if(!empty($chkt_prod_prazo_atual_sete)) {
    $data_sys = date('Y-m-d');
    $sql_smart = "SELECT id_produto_acabado 
                    FROM `ops` 
                    WHERE (DATE_ADD('$data_sys', INTERVAL -7 DAY) > SUBSTRING(`data_ocorrencia`, 1, 10) || data_ocorrencia = '0000-00-00 00:00:00') AND `status_finalizar`= '0' AND `ativo` = '1' ";
    $sql_smart.= " UNION SELECT ea.id_produto_acabado 
                    FROM `estoques_acabados` ea 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ea.id_produto_acabado 
                    WHERE `operacao_custo` = '1' AND (DATE_ADD('$data_sys',INTERVAL -7 DAY) > SUBSTRING(ea.data_atualizacao_prazo_ent, 1, 10) || ea.data_atualizacao_prazo_ent = '0000-00-00 00:00:00') ";
}else {
    $sql_smart = "SELECT id_produto_acabado 
                    FROM `produtos_acabados` 
                    WHERE `ativo` = '1' ";
}
//aqui vou pegar a qtde comprometida programada do sistema, para nao produzir produtos para pedido acima de um mes ...
$data_atual = date('Y-m-d');

if(strlen($id_produto_acabados) > 0) {
    $sql = "SELECT (SUM(pvi.`qtde_pendente`)) AS est_comprometido, pvi.`id_produto_acabado` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            WHERE pvi.`id_produto_acabado` IN ($id_produto_acabados) 
            AND pv.`faturar_em` >= DATE_ADD('$data_atual', INTERVAL 1 MONTH) 
            GROUP BY pvi.`id_produto_acabado` ";
    $campos_prog = bancos::sql($sql, 0, 500);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) $est_comp_programado_array[$campos_prog[$i]['id_produto_acabado']]=$campos_prog[$i]['est_comprometido'];
}else {
    $fazer_novo_sql                 = 'SIM';
    $est_comp_programado_array[]    = 0;
}

$sql = "SELECT pa.id_produto_acabado 
        FROM `estoques_acabados` ea 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ea.id_produto_acabado AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' $condicao_esp 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.`id_grupo_pa` LIKE '$cmb_grupo_pa' 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` LIKE '$cmb_familia' 
        WHERE pa.`operacao_custo` LIKE '%$cmb_operacao_custo%' 
        AND pa.`operacao_custo_sub` LIKE '$cmb_operacao_custo_sub' 
        AND pa.`ativo` = '1' $condicao_pas $condicao 
        AND pa.`id_produto_acabado` IN ($sql_smart) 
        GROUP BY pa.id_produto_acabado 
        ORDER BY $order_by ";

$sql_extra = "SELECT COUNT(DISTINCT(pa.id_produto_acabado)) AS total_registro 
                FROM `estoques_acabados` ea 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ea.id_produto_acabado AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' $condicao_esp 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.`id_grupo_pa` LIKE '$cmb_grupo_pa' 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` LIKE '$cmb_familia' 
                WHERE pa.`operacao_custo` LIKE '%$cmb_operacao_custo%' 
                AND pa.`operacao_custo_sub` LIKE '$cmb_operacao_custo_sub' 
                AND pa.`ativo` = '1' $condicao_pas $condicao 
                AND pa.`id_produto_acabado` IN ($sql_smart) 
                ORDER BY $order_by ";
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('SUA CONSULTA N�O RETORNOU NENHUM RESULTADO !')
        window.close()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Acertar Estoque Acabado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Acertar Estoque Acabado
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            Estoque<br> Excedente
        </td>
        <td colspan='5'>
            Quantidade / Estoque
        </td>
        <td rowspan='2'>
            <font title='M�dia Mensal de Vendas' style='cursor:help'>
                M.M.V.
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Real
        </td>
        <td>
            Disp.
        </td>
        <td>
            Pend.
        </td>
        <td>
            <font title='Estoque Comprometido' style='cursor:help'>
                Comp.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido Programado &gt; que 30 dias' style='cursor:help'>
                Prog.
            </font>
        </td>
    </tr>
<?
//Essas Datas eu vou estar passando por par�metro no relat�rio de Venda de Produto + abaixo ...
    $data_inicial = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
    $data_final = date('Y-m-d');

    $data_atual_menos_sete = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');
    for ($i = 0; $i < $linhas; $i++) {
        $retorno                = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 0);
        $quantidade_estoque     = $retorno[0];

        $compra                 = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
        $producao               = $retorno[2];
        $quantidade_disponivel	= $retorno[3];
        $qtde_pendente          = $retorno[7];
        $est_comprometido       = $retorno[8];

        //Aqui eu acerto todos os Estoques Poss�veis ...
        estoque_acabado::seta_nova_entrada_pa_op_compras($campos[$i]['id_produto_acabado']);
        estoque_acabado::atualizar($campos[$i]['id_produto_acabado']);
        estoque_acabado::controle_estoque_pa($campos[$i]['id_produto_acabado']);
        estoque_acabado::atualizar_producao($campos[$i]['id_produto_acabado']);
        estoque_acabado::atualiza_qtde_pendente($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td align='right'>
        <?
            //Verifico se o Item possui Estoque Excedente, mas somente do que est� "Em aberto" ...
            $sql = "SELECT SUM(`qtde`) AS total_excedente 
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `status` = '0' ";
            $campos_excedente = bancos::sql($sql);
            echo $campos_excedente[0]['total_excedente'];
        ?>
        </td>
        <td align='right'>
            <?=intval($quantidade_estoque);?>
        </td>
        <td align='right'>
            <?=intval($quantidade_disponivel);?>
        </td>
        <td align='right'>
            <?=intval($qtde_pendente);?>
        </td>
        <td align='right'>
        <?
            if($est_comprometido < 0) echo '<font color="red">';
            echo intval($est_comprometido);
        ?>
        </td>
        <td align='right'>
        <?
            if($fazer_novo_sql == 'SIM') {
                //aqui vou pegar a qtde comprometida programada do sistema, para nao produzir produtos para pedido acima de um mes ...
                $data_atual = date('Y-m-d');
                $sql = "SELECT (SUM(pvi.`qtde_pendente`)) AS est_comprometido 
                        FROM `pedidos_vendas_itens` pvi 
                        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                        WHERE pvi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        AND pv.`faturar_em` >= DATE_ADD('$data_atual', INTERVAL 1 MONTH) ";
                $campos_prog            = bancos::sql($sql);
                $est_comp_programado    = $campos_prog[0]['est_comprometido'];
                if($est_comp_programado < 0) echo '<font color="red">';
                echo intval($est_comp_programado);
            }else {
                $est_comp_programado = $est_comp_programado_array[$campos[$i]['id_produto_acabado']];
                if($est_comp_programado < 0) echo '<font color="red">';
                echo intval($est_comp_programado);
            }
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['mmv'], 2, ',', '.');?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
    <font color='red'>
        <b>Observa��o:</b>
    </font>
<pre>
* O(s) item(ns) acima est�(�o) com todos os seus dados de Estoque Acertado(s) Atualizado(s).

<b>** Estoque Real
** Estoque Faturado
** Estoque Dispon�vel
** Estoque Separado
** Quantidade Pendente
** Quantidade em Produ��o</b>
</pre>
<?}?>