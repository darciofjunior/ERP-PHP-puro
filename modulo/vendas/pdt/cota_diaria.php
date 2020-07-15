<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
require('class_pdt.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$valor_dolar_dia = genericas::moeda_dia('dolar');

$data_atual 	= date('Y-m-d');
$data_inicial	= $data_atual;
$data_final 	= $data_atual;

//Tratamento com as variáveis que vem por parâmetro ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_representante = $_POST['id_representante'];
}else {
    $id_representante = $_GET['id_representante'];
}

if(empty($id_representante)) $id_representante = '%';

$sql = "SELECT r.nome_fantasia, IF(c.id_pais = 31, SUM(pvi.qtde * pvi.preco_liq_final) , SUM(pvi.qtde * pvi.preco_liq_final * $valor_dolar_dia)) AS total 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
        INNER JOIN `representantes` r ON r.id_representante = pvi.id_representante 
        WHERE pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
        AND r.`id_representante` LIKE '$id_representante' 
        AND pv.liberado = '1' 
        GROUP BY r.id_representante ORDER BY total DESC ";
$campos_rep = bancos::sql($sql);
$linhas_rep = count($campos_rep);
?>
<html>
<head>
<title>.:: Relat&oacute;rio de Vendas Diária ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_representante' value="<?=$id_representante;?>">
<table width='90%' border='1' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Relat&oacute;rio de Vendas Diária<br>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Representante(s)
        </td>
        <td>
            Vendas R$
        </td>
    </tr>
<!--************************Total do Representante************************-->	
<?
    for($i = 0; $i < $linhas_rep; $i++) {
?>
    <tr class='linhanormal'>
        <td align='left' colspan='4'>
            <?=$campos_rep[$i]['nome_fantasia'];?>
        </td>
        <td align='right'>
            <?=number_format($campos_rep[$i]['total'], 2, ',', '.');?>
        </td>
    </tr>
<?
        $total_venda+= $campos_rep[$i]['total'];
    }
?>
    <tr class='linhadestaque'>
        <td colspan='5' align='right'>
            Total R$: <?=number_format($total_venda, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='5'>
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='5'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>