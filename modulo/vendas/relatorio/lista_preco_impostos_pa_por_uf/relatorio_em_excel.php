<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/arquivos/gerar_arquivo.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/lista_preco_impostos_pa_por_uf/relatorio.php', '../../../../');

if($_POST['id_cliente'] > 0) {//Se o usuário consultou por Cliente ...
    //Aqui eu busco alguns dados de Cliente ...
    $sql = "SELECT c.`id_uf`, IF(`nomefantasia` = '', LOWER(SUBSTRING_INDEX(`razaosocial`, ' ', 1)), LOWER(SUBSTRING_INDEX(`nomefantasia`, ' ', 1))) AS cliente 
            FROM `clientes` c 
            INNER JOIN `ufs` u ON u.id_uf = c.id_uf  
            WHERE c.`id_cliente` = '$_POST[id_cliente]' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    $id_uf          = $campos_cliente[0]['id_uf'];
    $nome_arquivo   = $campos_cliente[0]['cliente'];
}else {//Se o usuário consultou por Estado ...
    //Aqui dados da UF ...
    $sql = "SELECT `sigla` 
            FROM `ufs` 
            WHERE `id_uf` = '$_POST[cmb_uf]' LIMIT 1 ";
    $campos_uf      = bancos::sql($sql);
    $id_uf          = $_POST[cmb_uf];
    $nome_arquivo   = $campos_uf[0]['sigla'];
}
gerar_arquivo::arquivo('relatorio_em_excel_'.$nome_arquivo, 'xls');
?>
<html>
<body>
<table border='1'>
    <tr align='center'>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Código de Barra
        </td>
        <td>
            Classif Fiscal
        </td>
        <td>
            CEST
        </td>
        <td>
            CST
        </td>
        <td>
            ICMS <?=$uf;?>
        </td>
        <td>
            Dif % ICMS <?=$uf;?> p/ SP
        </td>
        <td>
            % Red BC
        </td>
        <td>
            % ICMS c/ Red BC
        </td>
        <td>
            % ICMS Intra Est
        </td>
        <td>
            IVA
        </td>
        <td>
            ST (% Aprox)
        </td>
        <td>
            % IPI
        </td>
    </tr>
<?
if(empty($_POST['cmb_gpa_vs_emp_div'])) $_POST['cmb_gpa_vs_emp_div'] = '%';

//Se essa opção estiver marcada eu apresento os dados de Impostos agrupando por ...
$group_by = ' GROUP BY pa.`id_produto_acabado` ';
$order_by = ' pa.`referencia`, pa.`discriminacao` ';
    
if($_POST['chkt_somente_com_codigo_barra'] == 'S') $condicao_codigo_barra = " AND pa.`codigo_barra` <> '' ";
        
/*********************************************************************************************************/
/************************Busca abaixo todos os PA(s) Adquiridos pelo nosso Cliente************************/
/*********************************************************************************************************/
if($_POST['chkt_somente_produtos_adquiridos'] == 'S') {
    $sql = "SELECT DISTINCT(pvi.`id_produto_acabado`) AS id_produto_acabado 
            FROM `pedidos_vendas` pv 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
            WHERE pv.`id_cliente` = '$_POST[id_cliente]' ORDER BY pvi.`id_produto_acabado` ";
    $campos_produto_acabado = bancos::sql($sql);
    $linhas_produto_acabado = count($campos_produto_acabado);
    for($i = 0; $i < $linhas_produto_acabado; $i++) $vetor_produtos_acabados[] = $campos_produto_acabado[$i]['id_produto_acabado'];
    $condicao_produtos_acabados = " AND pa.`id_produto_acabado` IN (".implode($vetor_produtos_acabados, ',').") ";
}
/*********************************************************************************************************/
        
//Fiz esse tratamento porque quando o Cliente é Estrangeiro não existe UF ...
if(!empty($id_uf)) $condicao_uf = "AND i.`id_uf` = '$id_uf' ";

//Busca de todos os PA(s) que são normais de Linha ...
$sql = "SELECT cf.`classific_fiscal`, cf.`cest`, cf.`ipi`, ed.`razaosocial`, f.`nome` AS familia, gpa.`nome`, 
        ged.`id_empresa_divisao`, ged.`desc_base_a_nac`, ged.`desc_base_b_nac`, ged.`acrescimo_base_nac`, 
        pa.`id_produto_acabado`, pa.`operacao`, 
        pa.`referencia`, pa.`origem_mercadoria`, pa.`discriminacao`, 
        pa.`preco_unitario`, pa.`peso_unitario`, pa.`preco_export`, pa.`status_top`, 
        pa.`codigo_barra`, u.`sigla` 
        FROM `produtos_acabados` pa 
        INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_gpa_vs_emp_div` LIKE '$_POST[cmb_gpa_vs_emp_div]' 
        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
        INNER JOIN `familias` f ON f.id_familia = gpa.id_familia AND f.`ativo` = '1' AND f.`id_familia` NOT IN (23, 24)  
        INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` AND cf.`ativo` = '1' 
        INNER JOIN `icms` i ON i.`id_classific_fiscal` = cf.`id_classific_fiscal` $condicao_uf AND i.`ativo` = '1' 
        WHERE (pa.referencia LIKE '%$_POST[txt_referencia_discriminacao]%' OR pa.discriminacao LIKE '%$_POST[txt_referencia_discriminacao]%') 
        AND pa.`referencia` <> 'ESP' 
        AND pa.`ativo` = '1' 
        $condicao_codigo_barra 
        $condicao_produtos_acabados 
        $group_by ORDER BY $order_by ";
$campos = bancos::sql($sql);
$linhas	= count($campos);

for($i = 0; $i < $linhas; $i++) {
    $dados_produto_uf_sp        = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], 1);
    $dados_produto_uf_cliente   = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], $id_uf);
?>
    <tr align='center'>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=$campos[$i]['codigo_barra'];?>
        </td>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <?=$campos[$i]['cest'];?>
        </td>
        <td>
            <?=$dados_produto_uf_cliente['cst'];?>
        </td>
        <td>
        <?
            if($dados_produto_uf_cliente['icms'] > 0) {
                echo number_format($dados_produto_uf_cliente['icms'], 2, ',', '.');
            }else {
                echo 'S/ICMS';
            }
        ?>
        </td>
        <td>
        <?
            if($dados_produto_uf_sp['icms'] - $dados_produto_uf_cliente['icms'] > 0) {
                echo number_format($dados_produto_uf_sp['icms'] - $dados_produto_uf_cliente['icms'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
            <?=number_format($dados_produto_uf_cliente['reducao'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format(($dados_produto_uf_cliente['icms'] - ($dados_produto_uf_cliente['icms'] * $dados_produto_uf_cliente['reducao']) / 100), 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($dados_produto_uf_cliente['icms_intraestadual'], 2, ',', '.');?>
        </td>
        <td>
        <?
            if($uf == 'SP') {
                //Em SP só existirá IVA quando a OF do PA for Industrial ...
                if($campos[$i]['operacao'] == 0) {
                    if($dados_produto_uf_sp['iva'] > 0) {
                        $linhas_itens.= number_format($dados_produto_uf_sp['iva'], 2, ',', '.');
                    }else {
                        $linhas_itens.= 'S/IVA';
                    }
                }else {
                    $linhas_itens.= 'S/IVA';
                }
            }else {//Em qualquer outro Estado independente da OF do PA ser Industrial ou Revenda, existirá IVA ...
                if($dados_produto_uf_cliente['iva'] > 0) {
                    echo number_format($dados_produto_uf_cliente['iva'], 2, ',', '.');
                }else {
                    echo 'S/IVA';
                }
            }
        ?>
        </td>
        <td>
        <?
            if(!empty($cmb_exibir_por)) {
                $valor_total = 10;//Pelo fato de não existir um único PA em específico, coloco como se o Preço Médio de cada um fosse R$ 10,00 ...
            }else {
                $valor_total = 1 * $campos[$i]['preco_unitario'];
            }
            $valor_icms                             = ($valor_total * $dados_produto_uf_cliente['icms'] / 100);
            $vetor_dados_substituicao_tributaria    = calculos::calculos_substituicao_tributaria($dados_produto_uf_cliente['ipi'], $dados_produto_uf_cliente['icms'], $dados_produto_uf_cliente['icms_intraestadual'], $dados_produto_uf_cliente['iva'], $valor_total, $valor_icms);

            echo number_format(($vetor_dados_substituicao_tributaria['valor_icms_st_item_current_rs'] * 100) / $valor_total, 2, ',', '.').'% ';
        ?>
        </td>
        <td>
        <?
            if($dados_produto_uf_cliente['ipi'] > 0) {
                echo number_format($dados_produto_uf_cliente['ipi'], 2, ',', '.');
            }else {
                echo 'S/IPI';
            }
        ?>
        </td>
    </tr>
<?}?>
</table>
</body>
</html>