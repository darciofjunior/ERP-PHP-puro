<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
session_start('funcionarios');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

//Aqui é a Busca da Variável de Vendas
$fator_desc_maximo_venda = genericas::variavel(19);
//Busca o nome do Cliente, o Contato + o id_cliente_contato p/ poder buscar com + detalhes os dados do cliente
$sql = "SELECT c.`id_cliente`, c.`cod_cliente`, c.`id_pais`, c.`razaosocial`, c.`id_uf`, c.`credito`, c.`trading`, cc.`nome`, nfso.* 
        FROM `nfs_outras` nfso 
        INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = nfso.`id_cliente_contato` 
        INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
        WHERE nfso.`id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
$id_empresa_nota 	= $campos[0]['id_empresa'];

if($campos[0]['finalidade'] == 'C') {
    $finalidade = 'CONSUMO';
}else if($campos[0]['finalidade'] == 'I') {
    $finalidade = 'INDUSTRIALIZAÇÃO';
}else {
    $finalidade = 'REVENDA';
}

$ajuste_total_produtos  = $campos[0]['ajuste_total_produtos'];
$ajuste_total_nf 	= $campos[0]['ajuste_total_nf'];
$ajuste_ipi             = $campos[0]['ajuste_ipi'];
$ajuste_icms		= $campos[0]['ajuste_icms'];
$id_pais                = $campos[0]['id_pais'];
$id_uf_cliente		= $campos[0]['id_uf'];

if($campos[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento4'];
if($campos[0]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[0]['vencimento3'].$prazo_faturamento;
if($campos[0]['vencimento2'] > 0) {
    $prazo_faturamento = $campos[0]['vencimento1'].'/'.$campos[0]['vencimento2'].$prazo_faturamento;
}else {
    $prazo_faturamento = ($campos[0]['vencimento1'] == 0) ? 'À vista' : $campos[0]['vencimento1'];
}

//Aqui verifica o Tipo de Nota
if($id_empresa_nota == 1 || $id_empresa_nota == 2) {
    $nota_sgd   = 'N';//var surti efeito lá embaixo
    $tipo_nota  = ' (NF)';
}else {
    $nota_sgd   = 'S'; //var surti efeito lá embaixo
    $tipo_nota  = ' (SGD)';
}

//Aqui é a verifica se esta Nota é de Saída ou Entrada
if($campos[0]['tipo_nfe_nfs'] == 'S') {
    $rotulo_tipo_nfe_nfs = ' - Saída';
} else {
    $rotulo_tipo_nfe_nfs = ' - Entrada';
}
$prazo_faturamento.= $tipo_nota.$rotulo_tipo_nfe_nfs;
/**************************************************************************************/
/*****************Função que retorna todos os Valores referentes a NF******************/
/**************************************************************************************/
//Essa variável é utilizada lá em baixo ...
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf_outra'], 'NFO');
?>
<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr>
        <td>
        <?
            $sql = "SELECT login 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login = bancos::sql($sql);
        ?>
            <b>Emitida Por: </b><?=ucfirst($campos_login[0]['login']);?> <b>Em</b> <?=date('d/m/Y H:i:s');?>
        </td>
        <td align='right'>
            <b>Valor do Frete:</b>
        </td>
        <td>
            &nbsp;<?=number_format($calculo_total_impostos['valor_frete'], 2, ',', '.');?>
        </td>
    </tr>
    <tr>
        <td>
            <b>Finalidade: </b> <?=$finalidade;?>
        </td>
        <td align='right'>
            <b>Outras Despesas Acessórias:</b>
        </td>
        <td>
            &nbsp;<?=number_format($calculo_total_impostos['outras_despesas_acessorias'], 2, ',', '.');?>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <b>UF: </b>
            <?
                $sql = "SELECT sigla 
                        FROM `ufs` 
                        WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
                $campos_uf = bancos::sql($sql);
                echo $campos_uf[0]['sigla'];
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='blue'>
                <b>Ajuste Tot. Prod.: </b><?=number_format($ajuste_total_produtos, 2, ',', '.');?> -
                <b>Ajuste Tot. NF: </b><?=number_format($ajuste_total_nf, 2, ',', '.');?> -  
                <b>Ajuste IPI: </b><?=number_format($ajuste_ipi, 2, ',', '.');?> - 
                <b>Ajuste ICMS:
            </font>
        </td>
        <td align='left'>
            <font color='blue'>
                &nbsp;<?=number_format($ajuste_icms, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr>
        <td>
            &nbsp;
        </td>
        <td align='right'>
            <b>Valor Total dos Produtos:</b>
        </td>
        <td>
            &nbsp;<?=number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.');?>
        </td>
    </tr>
    <tr>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
<?
        $sql = "UPDATE `nfs_outras` SET `total_icms` = '$calculo_total_impostos[valor_icms]', `total_ipi` = '".$calculo_total_impostos['valor_ipi']."' WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
        bancos::sql($sql);
?>
    <tr>
        <td>
            <b>Base de Cálculo ICMS Bits Bedames Riscador: </b> <?=number_format($calculo_total_impostos['base_calculo_icms_bits_bedames_riscador'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <b>Base de Cálculo ICMS: </b>
        </td>
        <td>
            &nbsp;<?=number_format($calculo_total_impostos['base_calculo_icms'], 2, ',', '.')?>
        </td>
    </tr>
    <tr>
        <td>
            <b>Base de Cálculo ICMS C/ Red: </b> <?=number_format($calculo_total_impostos['base_calculo_icms_c_red'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <b>Valor do ICMS: </b>
        </td>
        <td>
        &nbsp;
        <?
//Valor do ICMS ...
            if(strpos($calculo_total_impostos['valor_icms'], '.') > 0 || is_numeric($calculo_total_impostos['valor_icms'])) {//Nem sempre esse valor é numérico ...
                echo number_format($calculo_total_impostos['valor_icms'], 2, ',', '.');
            }else {//Às vezes ele pode ser Isento também ...
                echo $calculo_total_impostos['valor_icms'];
            }
        ?>
        </td>
    </tr>
    <tr>
        <td>
            <b>Base de Cálculo ICMS S/ Red: </b> <?=number_format($calculo_total_impostos['base_calculo_icms_s_red'], 2, ',', '.')?>
        </td>
        <td align='right'>
            <b>Valor do IPI: </b>
        </td>
        <td>
        &nbsp;
        <?
//Valor do IPI ...
            if(strpos($calculo_total_impostos['valor_ipi'], '.') > 0 || is_numeric($calculo_total_impostos['valor_ipi'])) {//Nem sempre esse valor é numérico ...
                echo number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.');
            }else {//Às vezes ele pode ser Isento também ...
                echo $calculo_total_impostos['valor_ipi'];
            }
        ?>	
        </td>
    </tr>
    <tr>
        <td>
            <b>Isento: </b> <?=number_format($calculo_total_impostos['isento'], 2, ',', '.')?>
        </td>
        <td align='right'>
                <b>Valor total da Nota: </b>
        </td>
        <td>
            &nbsp;<?=number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');?>
        </td>
    </tr>
    <tr>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr>
        <td colspan='3'>
            <b>P/ cálculo do IPI Frete e Despesas Acessórias, o sistema calcula proporcional a % do IPI de cada item. </b>
        </td>
    </tr>
    <tr>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr>
        <td>
            <b>Classificação Fiscal</b>
        </td>
    <tr>
<?
/*********************************Classificação Fiscal*********************************/
//Aqui eu listo todas as Situações Tributárias que ficaram armazenadas no Vetor ...
	$sql = "SELECT id_classific_fiscal, classific_fiscal, iva 
                FROM `classific_fiscais` 
                WHERE `id_classific_fiscal` IN ($id_classific_fiscais) ORDER BY id_classific_fiscal ";
	$campos_classific_fiscal = bancos::sql($sql);
	$linhas_classific_fiscal = count($campos_classific_fiscal);
//Disparo do Loop ...
	for($i = 0; $i < $linhas_classific_fiscal; $i++) {
            $valor_iva_classific_fiscal = '';
//Se existir valor de IVA (ST), então apresento o valor ao lado das classificações Fiscais na NF ...
            if($campos_classific_fiscal[$i]['iva'] > 0) $valor_iva_classific_fiscal = ' (IVA = '.number_format($campos_classific_fiscal[$i]['iva'], 2, ',', '.').')';
?>
    <tr>
        <td colspan='3'>
            <?=$campos_classific_fiscal[$i]['id_classific_fiscal'].' - '.$campos_classific_fiscal[$i]['classific_fiscal'].$valor_iva_classific_fiscal;?>
        </td>
    <tr>
<?
	}
/*************************************************************************************/
?>
</table>