<?
require('../../../lib/segurancas.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

/*Esse trecho de tela foi feito em um arquivo à parte, p/ evitar de recarregar toda a tela do 
Estoque Acabado que daí seria muito lento, achamos mais fácil e mais rápido recarregar apenas
o Iframe que é exatamente esse arquivo na hora em que o usuário altera o Prazo de Entrega ...*/
$data_atual_mais_sete = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chkt_mostrar_componentes   = $_POST['chkt_mostrar_componentes'];
    $chkt_est_disp_comp_zero    = $_POST['chkt_est_disp_comp_zero'];
    $chkt_exibir_esp            = $_POST['chkt_exibir_esp'];
    $cmb_familia                = $_POST['cmb_familia'];
    $cmb_grupo_pa               = $_POST['cmb_grupo_pa'];
    $hidden_operacao_custo      = $_POST['hidden_operacao_custo'];
    $cmb_operacao_custo         = $_POST['cmb_operacao_custo'];
    $hidden_operacao_custo_sub  = $_POST['hidden_operacao_custo_sub'];
    $cmb_operacao_custo_sub     = $_POST['cmb_operacao_custo_sub'];
    $txt_fornecedor             = $_POST['txt_fornecedor'];
}else {
    $chkt_mostrar_componentes   = $_GET['chkt_mostrar_componentes'];
    $chkt_est_disp_comp_zero    = $_GET['chkt_est_disp_comp_zero'];
    $chkt_exibir_esp            = $_GET['chkt_exibir_esp'];
    $cmb_familia                = $_GET['cmb_familia'];
    $cmb_grupo_pa               = $_GET['cmb_grupo_pa'];
    $hidden_operacao_custo      = $_GET['hidden_operacao_custo'];
    $cmb_operacao_custo         = $_GET['cmb_operacao_custo'];
    $hidden_operacao_custo_sub  = $_GET['hidden_operacao_custo_sub'];
    $cmb_operacao_custo_sub     = $_GET['cmb_operacao_custo_sub'];
    $txt_fornecedor             = $_GET['txt_fornecedor'];
}

//Se não estiver habilitado o checkbox, só não mostra os P.A. q pertecem a família de Componentes
if(empty($chkt_mostrar_componentes)) $condicao = " AND gpa.`id_familia` <> '23' ";

if(!empty($chkt_est_disp_comp_zero)) { // c tiver checked
    $condicao.= " AND (ea.`qtde_disponivel` - ea.`qtde_pendente`) < '0'  ";
    $order_by = "(-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1-ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) DESC "; // aqui nao posso passar o apelido por causa da sql de paginacao ela nao intende este apelido e dar erro
}else {
    $order_by = "pa.discriminacao ";
}

//Significa que o usuário só quer ver os PA(s) que são normais de linha
if(empty($chkt_exibir_esp)) $condicao_esp = " AND pa.`referencia` <> 'ESP' ";

if($cmb_familia == '')  $cmb_familia = '%';
if($cmb_grupo_pa == '') $cmb_grupo_pa = '%';
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
if($hidden_operacao_custo == 1) {//Operação de Custo = Industrial
    $cmb_operacao_custo = 0;
}else if($hidden_operacao_custo == 2) {//Operação de Custo = Revenda
    $cmb_operacao_custo = 1;
}else {//Independente da Operação de Custo
    if($cmb_operacao_custo == '') $cmb_operacao_custo = '%';
}
//Segunda adaptação
if($hidden_operacao_custo_sub == 1) {//Sub-Operação de Custo = Industrial
    $cmb_operacao_custo_sub = 0;
}else if($hidden_operacao_custo_sub == 2) {//Sub-Operação de Custo = Revenda
    $cmb_operacao_custo_sub = 1;
}else {//Independente da Sub-Operação de Custo
    if($cmb_operacao_custo_sub == '') $cmb_operacao_custo_sub = '%';
}
//Se estiver preenchido o "Fornecedor Default" ...
if(!empty($txt_fornecedor)) {
/*Aqui busco todos os PA do "Fornecedor Default", mas somente os PA que são do Tipo PI's, e q são 
normais de linha*/
    $sql = "SELECT pa.id_produto_acabado 
            FROM `fornecedores` f 
            INNER JOIN `produtos_insumos` pi ON pi.`id_fornecedor_default` = f.`id_fornecedor` AND pi.`id_fornecedor_default` > '0' AND pi.`ativo` = '1' 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`ativo` = '1' $condicao_esp 
            WHERE f.`razaosocial` LIKE '%$txt_fornecedor%' ORDER BY pa.id_produto_acabado ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {//Encontrou pelo menos 1 PA do Respectivo "Fornecedor" passado por parâmetro ...
        for($i = 0; $i < $linhas; $i++) $id_produto_acabados.=$campos[$i]['id_produto_acabado'].', ';
        $id_produto_acabados    = substr($id_produto_acabados, 0, strlen($id_produto_acabados) - 2);
        $condicao_pas           = " AND pa.`id_produto_acabado` IN ($id_produto_acabados) ";
    }else {//Não encontrou nenhum PA ...
        $condicao_pas           = " AND pa.`id_produto_acabado` = '0' ";
    }
}

//Significa que eu desejo ver todos os PA(s) em que O Prazo de Atualização é > do que 7 dias ...
if(!empty($chkt_prod_prazo_atual_sete)) {
    $data_sys = date('Y-m-d');
    $sql_smart = "SELECT id_produto_acabado 
                    FROM `ops` 
                    WHERE (DATE_ADD('$data_sys', INTERVAL -7 DAY) > SUBSTRING(`data_ocorrencia`, 1, 10) || `data_ocorrencia` = '0000-00-00 00:00:00') 
                    AND `status_finalizar` = '0' 
                    AND `ativo` = '1' ";
    
    $sql_smart.= " UNION SELECT ea.id_produto_acabado 
                    FROM `estoques_acabados` ea 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` AND pa.`operacao_custo` = '1' 
                    WHERE (DATE_ADD('$data_sys',INTERVAL -7 DAY) > SUBSTRING(ea.data_atualizacao_prazo_ent, 1, 10) || ea.data_atualizacao_prazo_ent = '0000-00-00 00:00:00') ";
}else {
    $sql_smart = "SELECT id_produto_acabado 
                    FROM `produtos_acabados` 
                    WHERE `ativo` = '1' ";
}

//Pega a Qtde Comprometida programada do sistema, p/ não produzir PA(s) p/ pedido(s) acima de um mês ...
$data_atual = date('Y-m-d');

if(strlen($id_produto_acabados) > 0) {
    $sql = "SELECT (SUM(qtde_pendente)) AS est_comprometido, pvi.id_produto_acabado 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`faturar_em` >= DATE_ADD('$data_atual', INTERVAL 1 MONTH) 
            WHERE pvi.`id_produto_acabado` IN ($id_produto_acabados) 
            GROUP BY pvi.id_produto_acabado ";
    $campos_prog = bancos::sql($sql, 0, 500);
    $linhas_prog = count($campos_prog);
    for($i = 0; $i < $linhas_prog; $i++) $est_comp_programado_array[$campos_prog[$i]['id_produto_acabado']] = $campos_prog[$i]['est_comprometido'];
}else {
    $fazer_novo_sql = 'SIM';
    $est_comp_programado_array[] = 0;
}

$sql = "SELECT distinct(pa.id_produto_acabado), ged.desc_medio_pa, pa.status_top, pa.mmv, pa.operacao_custo, pa.operacao_custo_sub, pa.referencia, pa.observacao observacao_pa, ea.id_estoque_acabado, ea.prazo_entrega, 
        (ea.qtde_disponivel-ea.qtde_pendente) as estoque_comprometido, u.sigla, 
        (-(ea.qtde_disponivel-ea.qtde_pendente)*(pa.preco_unitario*(1-ged.desc_base_a_nac/100)*(1-ged.desc_base_b_nac/100)*(1+ged.acrescimo_base_nac/100))*ged.desc_medio_pa) AS total_rs, 
        (pa.preco_unitario*(1-ged.desc_base_a_nac/100)*(1-ged.desc_base_b_nac/100)*(1+ged.acrescimo_base_nac/100)) AS preco_list_desc 
        FROM `estoques_acabados` ea 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' $condicao_esp 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_grupo_pa` LIKE '$cmb_grupo_pa' 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` LIKE '$cmb_familia' 
        WHERE pa.`operacao_custo` LIKE '%$cmb_operacao_custo%' 
        AND pa.`operacao_custo_sub` LIKE '$cmb_operacao_custo_sub' 
        AND pa.`ativo` = '1' $condicao_pas $condicao 
        AND pa.`id_produto_acabado` IN ($sql_smart) 
        GROUP BY pa.`id_produto_acabado` ORDER BY $order_by ";

$sql_extra = "Select count(distinct(pa.id_produto_acabado)) total_registro 
				from estoques_acabados ea 
				inner join produtos_acabados pa on pa.id_produto_acabado=ea.id_produto_acabado and pa.referencia like '%$txt_referencia%' and pa.discriminacao like '%$txt_discriminacao%' $condicao_esp 
				inner join unidades u on u.id_unidade=pa.id_unidade 
				inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div=pa.id_gpa_vs_emp_div and ged.id_grupo_pa like '$cmb_grupo_pa' 
				inner join grupos_pas gpa on gpa.id_grupo_pa=ged.id_grupo_pa and gpa.id_familia like '$cmb_familia' 
				where pa.operacao_custo like '%$cmb_operacao_custo%' 
				and pa.operacao_custo_sub like '$cmb_operacao_custo_sub' 
				and pa.ativo=1 $condicao_pas $condicao 
				and pa.id_produto_acabado in ($sql_smart) 
				order by $order_by ";
$campos = bancos::sql($sql, $inicio, 500, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO !')
        window.close()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Relatório Qtde de Peças a Embalar ::.</title>
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
        <td colspan='13'>
            Relatório Qtde de Peças a Embalar
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Ref
        </td>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            <font title='Peças por Embalagem' style='cursor:help'>
                Peças por <br/>Embalagem
            </font>
        </td>
        <td rowspan='2'>
            Estoque<br/> Excedente
        </td>
        <td colspan='5'>
            Quantidade / Estoque
        </td>
        <td rowspan='2'>
            <font title='Média Mensal de Vendas' style='cursor:help'>
                M.M.V.
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Estoque Real' style='cursor:help'>
                Real
            </font>
        </td>
        <td>
            <font title='Estoque Disponivel' style='cursor:help'>
                Disp.
            </font>
        </td>
        <td>
            <font title='Pendência' style='cursor:help'>
                Pend.
            </font>
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
//Essas Datas eu vou estar passando por parâmetro no relatório de Venda de Produto + abaixo ...
    $data_inicial   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
    $data_final     = date('Y-m-d');

    $data_atual_menos_sete = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');
    for ($i = 0; $i < $linhas; $i++) {
            $id_estoque_acabado     = $campos[$i]['id_estoque_acabado'];
            $referencia             = $campos[$i]['referencia'];
            $unidade                = $campos[$i]['sigla'];
            $preco_list_desc        = $campos[$i]['preco_list_desc'];
            $total_rs               = $campos[$i]['total_rs'];
            $desc_medio_pa          = $campos[$i]['desc_medio_pa'];
            $retorno                = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 0);
            $quantidade_estoque     = $retorno[0];
            $compra                 = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
            $producao               = $retorno[2];
            $quantidade_disponivel  = $retorno[3];
            $qtde_pendente          = $retorno[7];
            $est_comprometido       = $retorno[8];
            $prazo_entrega          = strtok($campos[$i]['prazo_entrega'], '=');
            $responsavel            = strtok($campos[$i]['prazo_entrega'], '|');
            $responsavel            = substr(strchr($responsavel, '> '), 1, strlen($responsavel));
            $data_hora              = strchr($campos[$i]['prazo_entrega'], '|');
            $data_hora              = substr($data_hora, 2, strlen($data_hora));
            $data                   = data::datetodata(substr($data_hora, 0, 10), '/');
            $hora                   = substr($data_hora, 11, 8);
//Faz esse tratamento para o caso de não encontrar o responsável
            $string_apresentar      = (empty($responsavel)) ? '&nbsp;' : 'Responsável: '.$responsavel.' - '.$data.' '.$hora;
//Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo a coluna na cor vermelha ...
            $font_compra            = ($compra < - ($est_comprometido)) ? "<font color='red'>" : "<font color='black'>";
            $font_producao          = ($producao < - ($est_comprometido)) ? "<font color='red'>" : "<font color='black'>";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT pecas_por_emb 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `embalagem_default` = '1' LIMIT 1 ";
            $campos_embalagem = bancos::sql($sql);
            if(count($campos_embalagem) == 1) echo intval($campos_embalagem[0]['pecas_por_emb']);
        ?>
        </td>
        <td align='right'>
        <?
            //Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" ...
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
                $sql = "SELECT (SUM(`qtde_pendente`)) AS est_comprometido 
                        FROM `pedidos_vendas_itens` pvi 
                        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                        WHERE pvi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        AND pv.`faturar_em` >= DATE_ADD('$data_atual',INTERVAL 1 MONTH) ";
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
        <td colspan='13'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>