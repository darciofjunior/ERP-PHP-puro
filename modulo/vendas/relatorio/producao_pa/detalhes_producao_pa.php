<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/estoque_pa/estoque_pa.php', '../../../../');

/*Como esse processamento pode ser muito pesado, deixo o servidor operar excepcionalmente em até 
5 minutos para essa tela ...*/
set_time_limit(300);

$sql = "SELECT ged.`desc_medio_pa`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, 
        ROUND(pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100), 2) AS preco_list_desc 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_grupo_pa` = '$_GET[id_grupo_pa]' 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND ed.`id_empresa_divisao` = '$_GET[id_empresa_divisao]' 
        WHERE pa.`ativo` = '1' 
        ORDER BY pa.`referencia` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório de Produção P.A. ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Relat&oacute;rio de Produção P.A. no Período de <?=$_GET['txt_data_inicial'];?> à <?=$_GET['txt_data_final'];?><br/>
            <font color='yellow'>
                Impressão em: 
            </font>
            <?=date('d/m/Y H:i:s');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Ref.
        </td>
        <td rowspan='2'>
            Produto
        </td>
        <td colspan='3'>
            OE
        </td>
        <td colspan='3'>
            OP
        </td>
        <td colspan='3'>
            NF de Entrada
        </td>	
        <td rowspan='2'>
            Pço. Unit.
        </td>
        <td rowspan='2'>
            Total
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º
        </td>
        <td>
            Data <br/>Entrada
        </td>
        <td>
            Qtde <br/>Entrada
        </td>
        <td>
            N.º
        </td>
        <td>
            Data <br/>Entrada
        </td>
        <td>
            Qtde <br/>Entrada
        </td>
        <td>
            N.º
        </td>
        <td>
            Data <br/>Entrada
        </td>
        <td>
            Qtde <br/>Entrada
        </td>
    </tr>
<?
    $data_inicial   = data::datatodate($_GET['txt_data_inicial'], '-');
    $data_final     = data::datatodate($_GET['txt_data_final'], '-');

    for($i = 0; $i < $linhas; $i++) {
        //Limpa as variáveis p/ não herdar valores do Loop Anterior ...
        $qtde_entrada	= '';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <!--******************************OE(s)******************************-->
        <td>
        <?
            /*Verifico o Total de Entrada(s) Registrada(s) p/ esse PA no caminho de OE cuja ação seja:

            M -> "Manipulação" e Tipo seja: 

            1) -> Manipulação p/ Substituição;
            2) -> Manipulação p/ Substituição com Ordem de Embalagem;
            3) -> Manipulação p/ Montagem de Jogos;

            /* Aqui controlamos tanto as Entradas como PA(s) enviados da OE ... Sinal Positivo representa entrada, 
            negativo PA(s) enviados ou correção de Entrada ...*/
            $sql = "SELECT bmp.`id_oe`, bmp.`qtde` AS qtde_entrada_saida, DATE_FORMAT(SUBSTRING(bmp.`data_sys`, 1, 10), '%d/%m/%Y') AS data_entrada_saida 
                    FROM `baixas_manipulacoes_pas` bmp 
                    WHERE bmp.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND SUBSTRING(bmp.`data_sys`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
                    AND bmp.`acao` = 'M' 
                    AND bmp.`tipo_manipulacao` IN (1, 2, 3) ";
            $campos_entrada_por_oe = bancos::sql($sql);
            $linhas_entrada_por_oe = count($campos_entrada_por_oe);
            for($j = 0; $j < $linhas_entrada_por_oe; $j++) {
        ?>
            <a href = '../../../producao/oes/alterar.php?passo=1&id_oe=<?=$campos_entrada_por_oe[$j]['id_oe'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos_entrada_por_oe[$j]['id_oe'];?>
            </a>
            <br/>
        <?
            }
        ?>
        </td>
        <td>
        <?
            for($j = 0; $j < $linhas_entrada_por_oe; $j++) echo $campos_entrada_por_oe[$j]['data_entrada_saida'].'<br/>';
        ?>
        </td>
        <td>
        <?
            for($j = 0; $j < $linhas_entrada_por_oe; $j++) {
                echo number_format($campos_entrada_por_oe[$j]['qtde_entrada_saida'], 2, ',', '.').'<br/>';
                $qtde_entrada+= $campos_entrada_por_oe[$j]['qtde_entrada_saida'];
            }
        ?>
        </td>
        <!--******************************OP(s)******************************-->
        <td>
        <?
            /*Aqui eu localizo todas as Baixas Manipulações de PA dentro do Período digitado pelo Usuário 

            Obs: Ação E -> Entrada de OP ...*/
            $sql = "SELECT bmp.`id_baixa_manipulacao_pa`, bmp.`qtde` AS qtde_entrada, DATE_FORMAT(SUBSTRING(bmp.`data_sys`, 1, 10), '%d/%m/%Y') AS data_entrada 
                    FROM `baixas_manipulacoes_pas` bmp 
                    WHERE bmp.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND SUBSTRING(bmp.`data_sys`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' AND bmp.`acao` = 'E' ";
            $campos_entrada_por_op = bancos::sql($sql);
            $linhas_entrada_por_op = count($campos_entrada_por_op);
            for($j = 0; $j < $linhas_entrada_por_op; $j++) {
                //Busco o N.º da OP através do id_baixa_manipulação do PA ...
                $sql = "SELECT `id_op` 
                        FROM `baixas_ops_vs_pas` 
                        WHERE `id_baixa_manipulacao_pa` = '".$campos_entrada_por_op[$j]['id_baixa_manipulacao_pa']."' ";
                $campos_ops = bancos::sql($sql);
                $linhas_ops = count($campos_ops);
                for($k = 0; $k < $linhas_ops; $k++) {
            ?>
                    <a href = '../../../producao/ops/alterar.php?passo=2&id_op=<?=$campos_ops[$k]['id_op'];?>&pop_up=1' class='html5lightbox'>
                        <?=$campos_ops[$k]['id_op'];?>
                    </a>
                    <br/>
            <?
                }
            }
        ?>
        </td>
        <td>
        <?
            for($j = 0; $j < $linhas_entrada_por_op; $j++) echo $campos_entrada_por_op[$j]['data_entrada'].'<br/>';
        ?>
        </td>
        <td>
        <?
            for($j = 0; $j < $linhas_entrada_por_op; $j++) {
                echo number_format($campos_entrada_por_op[$j]['qtde_entrada'], 2, ',', '.').'<br/>';
                $qtde_entrada+= $campos_entrada_por_op[$j]['qtde_entrada'];
            }
        ?>
        </td>
        <!--******************************NF(e)******************************-->
        <td>
        <?
            //Aqui eu localizo todas as NF de Entrada do PA dentro do Período digitado pelo Usuário ...
            $sql = "SELECT nfe.`id_nfe`, nfe.`num_nota`,  SUM(nfeh.`qtde_entregue`) AS qtde_entrada, 
                    DATE_FORMAT(SUBSTRING(nfe.`data_entrega`, 1, 10), '%d/%m/%Y') AS data_entrada 
                    FROM `nfe_historicos` nfeh 
                    INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = nfeh.`id_produto_insumo` AND pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' AND pa.`ativo` = '1' 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    WHERE SUBSTRING(nfeh.`data_sys`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
                    GROUP BY nfe.`id_nfe` ";
            $campos_entrada_por_nfe = bancos::sql($sql);
            $linhas_entrada_por_nfe = count($campos_entrada_por_nfe);
            for($j = 0; $j < $linhas_entrada_por_nfe; $j++) {
        ?>
                <a href = '../../../compras/pedidos/nota_entrada/itens/index.php?id_nfe=<?=$campos_entrada_por_nfe[$j]['id_nfe'];?>&pop_up=1' class='html5lightbox'>
                    <?=$campos_entrada_por_nfe[$j]['num_nota'];?>
                </a>
                <br/>
        <?
            }
        ?>
        </td>
        <td>
        <?
            for($j = 0; $j < $linhas_entrada_por_nfe; $j++) echo $campos_entrada_por_nfe[$j]['data_entrada'].'<br/>';
        ?>
        </td>
        <td>
        <?
            for($j = 0; $j < $linhas_entrada_por_nfe; $j++) {
                echo number_format($campos_entrada_por_nfe[$j]['qtde_entrada'], 2, ',', '.').'<br/>';
                $qtde_entrada+= $campos_entrada_por_nfe[$j]['qtde_entrada'];
            }
        ?>
        </td>
        <td align='right'>
        <?
            $preco_lista = ($campos[$i]['desc_medio_pa'] > 0) ? $campos[$i]['preco_list_desc'] * $campos[$i]['desc_medio_pa'] : $campos[$i]['preco_list_desc'];
            //echo 'R$ '.number_format($preco_lista, 2, ',', '.');
            echo $preco_lista;
        ?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($qtde_entrada * $preco_lista, 2, ',', '.');?>
        </td>
    </tr>
<?
        $total_geral_entrada+= $qtde_entrada * $preco_lista;
    }
?>
    <tr class='linhanormal' align='right'>
        <td colspan='12'>
            <font color='red' size='2'>
                <b>Total => </b>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                R$ <?=number_format($total_geral_entrada, 2, ',', '.');?>
            </font>
        </td>
    </tr>
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