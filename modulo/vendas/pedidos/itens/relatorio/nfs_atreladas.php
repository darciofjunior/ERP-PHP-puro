<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../../');
?>
<html>
<head>
<title>.:: Relatório de NFs Atreladas ::.</title>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body topmargin='20'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    $id_nf_atual = 0;
    //Busca Itens do Pedido passado por parâmetro ...
    $sql = "SELECT id_pedido_venda_item 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda` = '$_GET[id_pedido_venda]' 
            AND `status` > '0' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Relatório de NFs Atreladas do Pedido de Venda N.º 
            <font color='yellow'>
                <?=$_GET['id_pedido_venda'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N° NF
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Valor Mercadoria
        </td>
        <td>
            Valor IPI
        </td>
        <td>
            Valor ICMS ST
        </td>
        <td>
            Valor Total
        </td>
    </tr>
    <?
        for($i = 0; $i < $linhas; $i++) {
            //Verifica em quais NF(s) que esses Itens de Pedido estão vinculados ...
            $sql = "SELECT id_nfs_item, id_produto_acabado, (qtde * valor_unitario) AS valor_mercadoria, 
                    (qtde * valor_unitario * ipi / 100) AS valor_ipi, id_nf 
                    FROM `nfs_itens` 
                    WHERE `id_pedido_venda_item` = ".$campos[$i]['id_pedido_venda_item']." ";
            $campos_nf = bancos::sql($sql);
            $linhas_nf = count($campos_nf);
            for($j = 0; $j < $linhas_nf; $j++) {
                if($id_nf_atual != $campos_nf[$j]['id_nf']) {
                    $vetor_nfs[] = $campos_nf[$j]['id_nf'];
                    //Igualo para não entrar mais de uma vez nesse IF quando for a mesma NF ...
                    $id_nf_atual = $campos_nf[$j]['id_nf'];
                }
                $calculo_impostos_item = calculos::calculo_impostos($campos_nf[$j]['id_nfs_item'], $campos_nf[$j]['id_nf'], 'NF');
                $vetor_valor_icms_st[$campos_nf[$j]['id_nf']]+=     $calculo_impostos_item['valor_icms_st'];
                $vetor_valor_mercadoria[$campos_nf[$j]['id_nf']]+=  $campos_nf[$j]['valor_mercadoria'];
                $vetor_valor_ipi[$campos_nf[$j]['id_nf']]+=         $campos_nf[$j]['valor_ipi'];
            }
        }
        $id_nfs = implode(',', $vetor_nfs);
        
        //Exibe p/ o usuário quais são as NF(s) em que os Itens do Pedido passado por parâmetro estão vinculados ...
        $sql = "SELECT id_nf, DATE_FORMAT(data_emissao, '%d/%m/%Y') AS data_emissao 
                FROM `nfs` 
                WHERE `id_nf` IN ($id_nfs) ";
        $campos_nfs = bancos::sql($sql);
        $linhas_nfs = count($campos_nfs);
        for($i = 0; $i < $linhas_nfs; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href="javascript:nova_janela('../../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos_nfs[$i]['id_nf'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes' class='link'>
                <?=faturamentos::buscar_numero_nf($campos_nfs[$i]['id_nf'], 'S');?>
            </a>
        </td>
        <td>
            <?=$campos_nfs[$i]['data_emissao'];?>
        </td>
        <td>
            R$ <?=number_format($vetor_valor_mercadoria[$campos_nfs[$i]['id_nf']], 2, ',', '.');?>
        </td>
        <td>
            R$ <?=number_format($vetor_valor_ipi[$campos_nfs[$i]['id_nf']], 2, ',', '.');?>
        </td>
        <td>
            R$ <?=number_format($vetor_valor_icms_st[$campos_nfs[$i]['id_nf']], 2, ',', '.');?>
        </td>
        <td>
            R$ <?=number_format(($vetor_valor_mercadoria[$campos_nfs[$i]['id_nf']] + $vetor_valor_ipi[$campos_nfs[$i]['id_nf']] + $vetor_valor_icms_st[$campos_nfs[$i]['id_nf']]), 2, ',', '.');?>
        </td>
    </tr>
<?	
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='atencao' align='center'>
        <td colspan='7'>
            NÃO EXISTE(M) ITEM(NS) NESSE PEDIDO QUE ESTEJA(M) ATRELADO(S) EM NF !
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='7'></td>
    </tr>
    <tr class="atencao" align="center">
        <td colspan='7'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style="color:red" class='botao'>
        </td>
    </tr>
<?
    }
?>
</table>
</body>
</html>