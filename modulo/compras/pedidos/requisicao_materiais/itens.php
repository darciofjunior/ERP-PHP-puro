<?
require('../../../../lib/segurancas.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

//Aki Verifica se alguma antecipação do pedido, está atrelada a parte de contas bancárias
$sql = "SELECT fp.banco, fp.agencia, fp.num_cc, fp.correntista 
        FROM `antecipacoes` a 
        INNER JOIN `fornecedores_propriedades` fp ON fp.id_fornecedor_propriedade = a.id_fornecedor_propriedade 
        WHERE a.`id_pedido` = '$_GET[id_pedido]' 
        AND a.`id_fornecedor_propriedade` > '0' ORDER BY a.`id_antecipacao` DESC LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 1) {
    $bank           = (!empty($campos[0]['banco'])) ? $campos[0]['banco'] : '';
    $agencia        = (!empty($campos[0]['agencia'])) ? '*AG:'.$campos[0]['agencia'] : '';
    $conta_corrente = (!empty($campos[0]['num_cc'])) ? '*C/C:'.$campos[0]['num_cc'] : '';
    $correntista    = (!empty($campos[0]['correntista'])) ? '*'.$campos[0]['correntista'] : '';
}//Fim dos dados banc&aacute;rios

//Aqui transforma em vetor para poder realizar o loop + abaixo do código
$vetor_item_pedido  = explode(',', $_GET['chkt_item_pedido']);
$vetor_qtde         = explode(',', $_GET['txt_qtde']);

/*******************************Seleção de alguns do Pedido*******************************/
$sql = "SELECT p.*, f.* 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
        WHERE p.`id_pedido` = '$_GET[id_pedido]' 
        AND p.`ativo` = '1' LIMIT 1 ";
$campos             = bancos::sql($sql);
$tipo_nota          = $campos[0]['tipo_nota'];
$tipo_nota_porc     = $campos[0]['tipo_nota_porc'];
$data               = $campos[0]['data_emissao'];
$data               = substr($data, 8, 2).'/'.substr($data, 5, 2).'/'.substr($data, 0, 4);
$fornecedor         = $campos[0]['razaosocial'];
$endereco           = $campos[0]['endereco'];
$num_complemento    = $campos[0]['num_complemento'];
$bairro             = $campos[0]['bairro'];
$vendedor           = $campos[0]['vendedor'];
$ddd_fone1          = $campos[0]['ddd_fone1'];
$fone1              = $campos[0]['fone1'];
if(empty($fone1))   $fone1 = '';
$ddd_fone2          = $campos[0]['ddd_fone2'];
$fone2              = $campos[0]['fone2'];
if(empty($fone2))   $fone2 = '';
$ddd_fax            = $campos[0]['ddd_fax'];
$fax                = $campos[0]['fax'];
if(empty($fax))     $fax = '';
$condicao_ddl       = $campos[0]['desc_ddl'];
$qtde_caracter      = strlen($condicao_ddl);
for($i = 0; $i < $qtde_caracter; $i++) {
    if(substr($condicao_ddl,$i,1) != '-') {
        $condicao_ddl.= substr($condicao_ddl, $i, 1);
    }else {
        $i = $qtde_caracter;
    }
}
$condicao_ddl = trim($condicao_ddl);
/*****************************************************************************************/
?>
<html>
<head>
<title>.:: Requisição de Materiais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<form name='form'>
<?
    $vias = array('1&ordm; VIA - DEPTO. DE COMPRAS', '2&ordm; VIA - TRANSPORTE');
    for($i = 0; $i < count($vias); $i++) {
?>
<table width='90%' border='1' cellspacing ='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho'>
        <td colspan='8'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>PEDIDO -</b> <?=$_GET['id_pedido'];?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                REQUISIÇÃO DE MATERIAIS
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?=$data;?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?=$vias[$i];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='8' bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>Fornecedor - </b>&nbsp; <?=$fornecedor;?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <b>Fones - </b>&nbsp;
            <?
                if($fone2 == '' && $fax == '') {
                    echo '('.$ddd_fone1.') '.$fone1;
                }else if($fone2 != '' && $fax == '') {
                    echo '('.$ddd_fone1.') '.$fone1.' / ('.$ddd_fone2.') '.$fone2;
                }else {
                    echo '('.$ddd_fone1.') '.$fone1.' / ('.$ddd_fone2.') '.$fone2.' / FAX ('.$ddd_fax.') '.$fax;
                }
            ?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='8' bgcolor='#CECECE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>End.: </b>&nbsp; <?=$endereco.', '.$num_complemento.' - '.$bairro;?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <b>Vend.: </b>&nbsp;<?=$vendedor;?>
                &nbsp;&nbsp;&nbsp;
                <b>CONDIÇÃO: </b>
                <?
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Financiamento que foi feitas p/ o Pedido ...
                $sql = "SELECT pf.*, tm.simbolo 
                        FROM `pedidos_financiamentos` pf 
                        INNER JOIN `pedidos` p ON p.`id_pedido` = pf.`id_pedido` 
                        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = p.`id_tipo_moeda` 
                        WHERE pf.`id_pedido` = '$_GET[id_pedido]' ORDER BY pf.dias ";
                $campos_financiamento = bancos::sql($sql);
                $linhas_financiamento = count($campos_financiamento);
                if($linhas_financiamento > 0) {//Se foi encontrado pelo menos 1 Financiamento ...
                    for($j = 0; $j < $linhas_financiamento; $j++) {
                        if($i == 0) {//Se eu estiver na Primeira parcela
                            $primeira_parcela = $campos_financiamento[$j]['dias'];
                        }else if($i + 1 == $linhas_financiamento) {//Última Parcela
                            $ultima_parcela = $campos_financiamento[$j]['dias'];
                        }
                    }
                    $exibir_nota = ($tipo_nota == 1) ? 'NF' : 'SGD';
                    $condicao_ddl = $linhas_financiamento.' parc. ('.$primeira_parcela.' à '.$ultima_parcela.' DDL) '.$exibir_nota.' '.$tipo_nota_porc.' %';
                }
                echo $condicao_ddl;
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <b>Qtde</b>
        </td>
        <td>
            <b>Unidade</b>
        </td>
        <td>
            <b>Discriminação</b>
        </td>
        <td>
            <b>Preço Unit. R$</b>
        </td>
        <td>
            <b>Valor Total</b>
        </td>
        <td>
            <b>Ipi %</b>
        </td>
        <td>
            <b>VLR. IPI</b>
        </td>
        <td>
            <b>Marca / Obs</b>
        </td>
    </tr>
<?
//Seleção somente dos Itens em que o usuário escolheu para a Requisição de Material
        $valor_total_ipi    = 0;
        $valor_total        = 0;
        for($j = 0; $j < count($vetor_item_pedido); $j++) {
            $sql = "SELECT ip.*, g.referencia, pi.discriminacao, u.sigla 
                    FROM `itens_pedidos` ip 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE ip.`id_item_pedido` = '$vetor_item_pedido[$j]' LIMIT 1 ";
            $campos = bancos::sql($sql);
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=number_format($vetor_qtde[$j], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[0]['sigla'];?>
        </td>
        <td align='left'>
        <?
            $referencia = genericas::buscar_referencia($campos[0]['id_produto_insumo'], $campos[0]['referencia'], 0);
            echo $referencia.' * '.$campos[0]['discriminacao'];
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[0]['preco_unitario'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
//Aqui eu não puxo o valor total direto do Banco de Dados, pq pode a qtde nesse caso é a requirida
//Qtde Dig. lá na Requisição de Material
            $valor_item = ($campos[0]['preco_unitario'] * $vetor_qtde[$j]);
            echo $moeda.number_format($valor_item, 2, ',', '.');
            $valor_total+= $valor_item;
        ?>
        </td>
        <td align='right'>
        <?
            if(($campos[0]['ipi'] == 0) or ($tipo_nota == 2)) {
                echo '&nbsp;';
            }else {
                echo $campos[0]['ipi'];
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($tipo_nota == 2) {//Se o Tipo de Pedido for SGD, não existe IPI
                $ipi = 0;
            }else {//Se for NF, então existe IPI
                $ipi = $campos[0]['ipi'];
            }
            $valor_com_ipi = ($valor_item * $ipi) / 100;
            echo $moeda.number_format($valor_com_ipi, 2, ',', '.');
            $valor_total_ipi+= $valor_com_ipi;
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[0]['marca'] == '') {
                echo '&nbsp;';
            }else {
                echo $campos[0]['marca'];
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>DADOS BANCÁRIOS: <?=$bank;?><?=$agencia;?><?=$conta_corrente;?><?=$correntista;?></b>
            </font>
        </td>
        <td colspan='4' align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'><b>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                TOTAL C/ IPI: R$&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?=$moeda.number_format($valor_total_ipi + $valor_total, 2, ',', '.');?>
            </b></font>
        </td>
    </tr>
<?
	if(!empty($_GET['obs_requisicao'])) {
?>
    <tr class="linhadestaque">
        <td colspan="8">
        <?
            $obs_requisicao = ucfirst(strtolower($_GET['obs_requisicao']));
        ?>
            Observação: <?=$obs_requisicao;?>
        </td>
    </tr>
<?
	}
?>
</table>
<?
    }
?>
<!--
Eu prefiro ter que criar apenas um hidden aqui, do que ter que ficar
levando mais parâmetros. Esse hidden guarda a Observação da Requisição-->
<input type='hidden' name='obs_requisicao' value="<?=$obs_requisicao;?>">
</form>
</body>
</html>