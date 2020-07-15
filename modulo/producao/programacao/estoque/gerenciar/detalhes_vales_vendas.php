<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');
?>
<html>
<head>
<title>.:: Detalhe(s) de Vale(s) de Venda(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body>
<table width='95%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Detalhe(s) de Vale(s) de Venda(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º do Vale
        </td>
        <td>
            Qtde
        </td>
        <td>
            Entregue por
        </td>
        <td>
            Retirado por
        </td>
        <td>
            Qtde de Caixas
        </td>
        <td>
            Peso Bruto
        </td>
        <td>
            Valor de Frete
        </td>
        <td>
            Transportadora
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Data e Hora
        </td>
    </tr>
<?
    //Aqui eu busco todos os Vales de Vendas do "$_GET[id_pedido_venda_item]" que foi passado por parâmetro ...
    $sql = "SELECT vv.*, vvi.`qtde`, f.`nome`, pvi.`id_pedido_venda`, t.`nome` AS transportadora 
            FROM `vales_vendas_itens` vvi 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = vvi.`id_pedido_venda_item` 
            INNER JOIN `vales_vendas` vv ON vv.`id_vale_venda` = vvi.`id_vale_venda` 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = vv.`id_funcionario` 
            INNER JOIN `transportadoras` t ON t.`id_transportadora` = vv.`id_transportadora` 
            WHERE vvi.`id_pedido_venda_item` = '$_GET[id_pedido_venda_item]' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <a href="javascript:nova_janela('comprovante_entrega.php?id_vale_venda=<?=$campos[$i]['id_vale_venda'];?>&id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>', 'MANDAR', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Imprimir Comprovante de Entrega' class='link'>
                <?=$campos[$i]['id_vale_venda'];?>
            </a>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[$i]['entregue_por'];?>
        </td>
        <td>
            <?=$campos[$i]['retirado_por'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_caixas'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['peso_bruto'], 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos[$i]['valor_frete'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[$i]['transportadora'];?>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' às '.substr($campos[$i]['data_sys'], 11, 8);?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>