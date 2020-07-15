<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/ocs/itens/consultar.php', '../../../../');

$sql = "SELECT DISTINCT(nfs.`id_nf`), DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS data_emissao, nfsi.`qtde`, nnn.`numero_nf`, e.`nomefantasia` 
        FROM `nfs_num_notas` nnn 
        INNER JOIN `nfs` ON nfs.`id_nf_num_nota` = nnn.`id_nf_num_nota` AND nfs.`id_cliente` = '$_GET[id_cliente]' 
        INNER JOIN `empresas` e ON e.`id_empresa` = nfs.`id_empresa` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.`id_produto_acabado` = '$_GET[id_produto_acabado]' 
        ORDER BY nfs.`data_emissao` DESC ";
$campos = bancos::sql($sql, $inicio, 5, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: NF(s) Saída ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            NF(s) Saída
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º NF
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Qtde
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = "javascript:nova_janela('../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=".$campos[$i]['id_nf']."&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <a href="<?=$url;?>" title='Detalhes de NF' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>">
            <a href="<?=$url;?>" title='Detalhes de NF' class='link'>
                <?=$campos[$i]['numero_nf'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
            <?=intval($campos[$i]['qtde']);?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>  
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>