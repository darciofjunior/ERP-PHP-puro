<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');//Essa biblioteca é requerida dentro da Financeiros ...
require('../../../lib/financeiros.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

if(!empty($_GET['cmb_credito'])) $condicao_credito = " AND c.`credito` LIKE '$_GET[cmb_credito]' ";

/*****************************************************************************************/
//Aqui eu busco o Valor Total Devido Geral de todos os Clientes ...
$sql = "SELECT cr.`id_conta_receber`, cr.`id_cliente` 
        FROM `contas_receberes` cr 
        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` $condicao_credito 
        WHERE cr.`status` < '2' 
        AND cr.`data_vencimento` < '".date('Y-m-d')."' ";
$campos_total_duplicatas = bancos::sql($sql);
$linhas_total_duplicatas = count($campos_total_duplicatas);
for($i = 0; $i < $linhas_total_duplicatas; $i++) {
    /*Aqui eu verifico se está Duplicata do Loop realmente possui Pendência "Dívida Conosco" ...

    Obs: Existem Duplicatas que estão no Valor Negativo o que representa que o Cliente possui Crédito 
    conosco e não pendência e essas não podem ser Contabilizadas p/ mudar o Crédito do Cliente p/ C ...*/
    $calculos_conta_receber = financeiros::calculos_conta_receber($campos_total_duplicatas[$i]['id_conta_receber']);

    /*Se esse "Valor Reajustado" for maior do que Zero, então isso representa "Dívida" e sendo assim tenho 
    que contabilizar o "id_cliente" dessa Duplicata p/ poder mudar o Crédito do Cliente p/ "C" ...*/
    if($calculos_conta_receber['valor_reajustado'] > 0) {
        $vetor_clientes[] = $campos_total_duplicatas[$i]['id_cliente'];
        $valor_total_devido_por_cliente[$campos_total_duplicatas[$i]['id_cliente']]+= $calculos_conta_receber['valor_reajustado'];
    }
}
$vetor_clientes = array_unique($vetor_clientes);
/*****************************************************************************************/

//Aqui só apresento os dados de Clients nos seus respectivos Valores ...
$sql = "SELECT c.id_cliente, IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente, c.cidade, c.credito, u.sigla
        FROM `clientes` c
        LEFT JOIN `ufs` u ON u.id_uf = c.id_uf
        WHERE c.`id_cliente` IN (".implode($vetor_clientes, ',').") 
        ORDER BY cliente";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Detalhes de Valor Total Devido por Cliente ::.</title>
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
        <td colspan='7'>
            Detalhes de Valor Total Devido por Cliente
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
        <td>
            Crédito
        </td>
        <td>
            Valor Reajustado<br/>Devido R$
        </td>
        <td>
            Representante
        </td>
        <td>
            1º Vencimento <br/>em Atraso
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
            <?=$campos[$i]['credito'];?>
        </td>
        <td align='right'>
            <?=number_format($valor_total_devido_por_cliente[$campos[$i]['id_cliente']], 2 ,',', '.');?>
        </td>
        <td>
        <?
        //Aqui eu busco o Representante daquele Cliente ...
            $sql = "SELECT DISTINCT(r.nome_fantasia) AS representante 
                    FROM `representantes` r 
                    INNER JOIN `clientes_vs_representantes` cr ON cr.id_representante = r.id_representante AND cr.id_cliente = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['representante'];
        ?>
        </td>
        <td>
        <?
            //Busco o 1º Vencimento em Atraso desse Cliente do Loop ...
            $sql = "SELECT id_conta_receber, DATE_FORMAT(`data_vencimento`, '%d/%m/%Y') AS data_vencimento 
                    FROM `contas_receberes` 
                    WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' 
                    AND `data_vencimento` < '".date('Y-m-d')."' 
                    AND `status` < '2' ";
            $campos_primeiro_vencimento = bancos::sql($sql);
            $linhas_primeiro_vencimento = count($campos_primeiro_vencimento);
            for($j = 0; $j < $linhas_primeiro_vencimento; $j++) {
                /*Aqui eu verifico se está Duplicata do Loop realmente possui Pendência "Dívida Conosco" ...

                Obs: Existem Duplicatas que estão no Valor Negativo o que representa que o Cliente possui Crédito 
                conosco e não pendência e essas não podem ser Contabilizadas p/ mudar o Crédito do Cliente p/ C ...*/
                $calculos_conta_receber = financeiros::calculos_conta_receber($campos_primeiro_vencimento[$j]['id_conta_receber']);

                /*Se esse "Valor Reajustado" for maior do que Zero, então isso representa "Dívida" e sendo assim tenho 
                que contabilizar o "id_cliente" dessa Duplicata p/ poder mudar o Crédito do Cliente p/ "C" ...*/
                if($calculos_conta_receber['valor_reajustado'] > 0) {
                    echo $campos_primeiro_vencimento[$j]['data_vencimento'];
                    break;//Para sair fora do Loop ...
                }
            }
        ?>
        </td>
    </tr>
<?
        $valor_total_geral+= $valor_total_devido_por_cliente[$campos[$i]['id_cliente']];
    }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='7'>
            <font color='yellow'>
                Valor Total Geral =>
            </font>
            <?=number_format($valor_total_geral, 2, ',', '.');?>
        </td>
    </tr>
</table>
</body>
</html>