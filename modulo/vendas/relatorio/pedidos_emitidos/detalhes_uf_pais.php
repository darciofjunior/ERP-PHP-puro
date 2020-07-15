<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');

if($_GET['id_uf'] > 0) {
    $sql = "SELECT estado 
            FROM `ufs` 
            WHERE `id_uf` = '$_GET[id_uf]' LIMIT 1 ";
    $campos_uf          = bancos::sql($sql);
    $rotulo_detalhes    = 'UF: <font color="yellow">'.$campos_uf[0]['estado'].'</font>';
    $tipo_moeda         = 'R$';
}else {
    $sql = "SELECT pais 
            FROM `paises` 
            WHERE `id_pais` = '$_GET[id_pais]' LIMIT 1 ";
    $campos_pais        = bancos::sql($sql);
    $rotulo_detalhes    = 'País: <font color="yellow">'.$campos_pais[0]['pais'].'</font>';
    $tipo_moeda         = 'U$';
}

/*Verifico se foi passado o "id_uf" por parâmetro, e se sim então significado que este é nacional, do contrário 
é internacional ...*/
$condicao_cliente = ($_GET['id_uf'] > 0) ? " and c.id_pais = 31 and c.id_uf = '$_GET[id_uf]' " : " and c.id_pais <> 31 and c.id_pais = '$_GET[id_pais]' ";

//Busca dos Representantes que venderam na determinada UF e período passados por parâmetro ...
$sql = "SELECT SUM(pvi.preco_liq_final * pvi.qtde) as valor_venda, pvi.id_representante, r.nome_fantasia AS representante 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente $condicao_cliente 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
        INNER JOIN `representantes` r ON r.id_representante = pvi.id_representante 
        WHERE pv.`data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' 
        AND pv.`liberado` = '1' GROUP BY r.id_representante ORDER BY representante ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Detalhes por UF / País ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Detalhes por <?=$rotulo_detalhes;?> no Período de 
            <font color="yellow">
                    <?=data::datetodata($_GET['data_inicial'], '/').' à '.data::datetodata($_GET['data_final'], '/');?>
            </font>
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Vendedor
        </td>
        <td>
            Valor em <?=$tipo_moeda;?>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td align='left'>
            <?=$campos[$i]['representante'];?>
        </td>
        <td align='right'>
            <a href="javascript:nova_janela('detalhes_clientes.php?id_representante=<?=$campos[$i]['id_representante'];?>&id_uf=<?=$_GET['id_uf'];?>&data_inicial=<?=$_GET['data_inicial'];?>&data_final=<?=$_GET['data_final'];?>', 'DETALHES_CLIENTES', '', '', '', '', 300, 780, 'c', 'c')" class="link">
            <?
                echo number_format($campos[$i]['valor_venda'], 2, ',', '.');
                $valor_total_venda+= $campos[$i]['valor_venda'];
            ?>
            </a>
        </td>
    </tr>
<?
	}
?>
    <tr class="linhadestaque" align="right">
        <td>
            <font color='yellow'>
                Valor Total em <?=$tipo_moeda;?>
            </font>
        </td>
        <td>
            <?=number_format($valor_total_venda, 2, ',', '.');?>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            &nbsp;
        </td>
    </tr>
</body>
</html>