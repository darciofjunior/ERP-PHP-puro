<?
require('../../lib/segurancas.php');
require('../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../lib/data.php');
require('../../lib/intermodular.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../');

if(!empty($_GET['id_orcamento_venda'])) {//Por dentro do Orçamento ...
    $sql = "SELECT ed.`razaosocial`, ovi.`id_produto_acabado`, ovi.`qtde`, ovi.`comissao_new`, ovi.`comissao_extra`, 
            ovi.`preco_liq_final`, (ovi.`qtde` * ovi.`preco_liq_final`) AS total_produto_rs, ovi.`margem_lucro`, 
            ovi.`margem_lucro_estimada`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, 
            pa.`operacao_custo_sub` 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' ORDER BY ed.razaosocial";
}else if(!empty($_GET['id_pedido_venda'])) {//Por dentro do Pedido de Venda ...
    $sql = "SELECT ed.`razaosocial`, pvi.`id_produto_acabado`, pvi.`qtde`, pvi.`comissao_new`, pvi.`comissao_extra`, 
            pvi.`preco_liq_final`, (pvi.`qtde` * pvi.`preco_liq_final`) AS total_produto_rs, pvi.`margem_lucro`, 
            pvi.`margem_lucro_estimada`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, 
            pa.`operacao_custo_sub` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            WHERE pvi.`id_pedido_venda` = '$_GET[id_pedido_venda]' ORDER BY ed.razaosocial";
}
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<title>.:: Total Produto(s) MLG por Divisão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
<body>
<table width='95%' cellspacing='1' cellpadding='1' border='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Total Produto(s) MLG por Divisão
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde
        </td>
        <td>
            Referencia
        </td>
        <td>
            Produto
        </td>
        <td>
            OC + Sub
        </td>
        <td>
            MLG %
        </td>
        <td>
            MLG Est.
        </td>
        <td>
            Comissão %
        </td>
        <td>
            Preço Unitario R$
        </td>
        <td>
            Total Lote R$
        </td>
    </tr>
<?
    $vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();
    $total_produto_por_divisao_rs           = 0;

    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0)?>
        </td>
        <td>
            <?if($campos[$i]['operacao_custo'] == 0) {
                echo 'I-';
                if($campos[$i]['operacao_custo_sub'] == 0) {echo 'I';}else {echo 'R';}
            }else {
                echo 'R';                
            }
                
            ?>
        </td>
        <td align='right'>
        <?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                echo number_format($campos[$i]['margem_lucro'], 2, ',', '.');
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
        <?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                echo number_format($campos[$i]['margem_lucro_estimada'], 2, ',', '.');
            }else {
                echo '-';
            }
        ?>
        </td>
        <td>
            <font color='brown' title='Comiss&atilde;o Extra => <?=number_format($campos[$i]['comissao_extra'], '2', ',', '.');?>' style='cursor:help'>
                <?=number_format($campos[$i]['comissao_new'] + $campos[$i]['comissao_extra'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['total_produto_rs'], 2, ',', '.');?>
        </td>
    </tr>
<?
        $total_produto_por_divisao_rs+=     $campos[$i]['total_produto_rs'];
        $total_custo_ml_zero_por_div+=      $campos[$i]['total_produto_rs'] / (1 + $campos[$i]['margem_lucro'] / 100);
        $total_custo_ml_est_zero_por_div+=  $campos[$i]['total_produto_rs'] / (1 + $campos[$i]['margem_lucro_estimada'] / 100);
        
        //A próxima Empresa Divisão for diferente da Atual do Loop, printo essa Linha com um Resumo da Mesma ...
        if($campos[$i]['razaosocial'] != $campos[$i + 1]['razaosocial']) {
?>
        <tr class='linhadestaque' align='right'>
            <td colspan='4'>
                <font color='yellow'>
                    TOTAL <?=$campos[$i]['razaosocial'];?> => 
                </font>
            </td>
            <td>
            <?
                //Somente para esses logins: Rivaldo, Rodrigo, Roberto, Wilson Chefe, Fabio Petroni, Dárcio, Bispo, Wilson Nishimura e Netto ...
                if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 125 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
                    $mlmg_geral_por_div = ($total_produto_por_divisao_rs / $total_custo_ml_zero_por_div - 1) * 100;
                    echo number_format($mlmg_geral_por_div, 1, ',', '.').' %';
                }
            ?>
            </td>
            <td>
            <?
                //Somente para esses logins: Rivaldo, Rodrigo, Roberto, Wilson Chefe, Fabio Petroni, Dárcio, Bispo, Wilson Nishimura e Netto ...
                if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 125 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
                    $mlm_est_geral_por_div = ($total_produto_por_divisao_rs / $total_custo_ml_est_zero_por_div - 1) * 100;
                    echo number_format($mlm_est_geral_por_div, 1, ',', '.').' %';
                }
            ?>
            </td>
            <td colspan='3'>
                <?=number_format($total_produto_por_divisao_rs, 2, ',', '.');?>
            </td>
        </tr>
        <?
            //Zero essas variáveis p/ não acumular valores das outras Divisões ...
            $total_produto_por_divisao_rs       = 0;
            $total_custo_ml_zero_por_div        = 0;
            $total_custo_ml_est_zero_por_div    = 0;
        }
        $total_produtos_geral+=             $campos[$i]['total_produto_rs'];
        $total_custo_ml_zero_geral+=        $campos[$i]['total_produto_rs'] / (1 + $campos[$i]['margem_lucro'] / 100);
        $total_custo_ml_est_zero_geral+=    $campos[$i]['total_produto_rs'] / (1 + $campos[$i]['margem_lucro_estimada'] / 100);
    }
?>
    <tr class='linhacabecalho' align='right'>        
        <td colspan='4'>
            &nbsp;
        </td>
        <td>
            <font color='yellow'>
            <?
                //Somente para esses logins: Rivaldo, Rodrigo, Roberto, Wilson Chefe, Fabio Petroni, Dárcio, Bispo, Wilson Nishimura e Netto ...
                if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 125 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
                    $mlmg_geral = ($total_produtos_geral / $total_custo_ml_zero_geral - 1) * 100;
                    echo number_format($mlmg_geral, 1, ',', '.').' %';
                }
            ?>
            </font>
        </td>
        <td>
            <font color='yellow'>
            <?
                //Somente para esses logins: Rivaldo, Rodrigo, Roberto, Wilson Chefe, Fabio Petroni, Dárcio, Bispo, Wilson Nishimura e Netto ...
                if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 125 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
                    $mlm_est_geral = ($total_produtos_geral / $total_custo_ml_est_zero_geral - 1) * 100;
                    echo number_format($mlm_est_geral, 1, ',', '.').' %';
                }
            ?>
            </font>
        </td>
        <td colspan='3'>
            <font color='yellow'>
                R$ <?=number_format($total_produtos_geral, 2, ',', '.');?>
            </font>
        </td>
    </tr>
</table>
</body>
</html>