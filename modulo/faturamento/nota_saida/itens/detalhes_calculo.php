<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
session_start('funcionarios');

/*Eu tenho esse desvio aki para n�o verificar a sess�o desse arkivo, fa�o isso pq esse arquivo aki � um 
pop-up em outras partes do sistema e se eu n�o fizer esse desvio d� erro de permiss�o*/
if($nao_verificar_sessao != 1) {
    switch($opcao) {
        case 1://Significa que veio do Menu Abertas / Liberadas ...
        case 2://Significa que veio do Menu de Liberadas / Faturadas ...
        case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
        default://Significa que veio do Menu de Devolu��o ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
        break;
    }
}

//Aqui � a Busca da Vari�vel de Vendas
$fator_desc_maximo_venda    = genericas::variavel(19);
//Busca o nome do Cliente, o Contato + o id_cliente_contato p/ poder buscar com + detalhes os dados do cliente
$sql = "SELECT c.`id_pais`, c.`id_uf`, nfs.`id_empresa`, nfs.`finalidade`, nfs.`forma_pagamento`, nfs.`data_emissao`, 
        nfs.`snf_devolvida`, nfs.`data_emissao_snf`, nfs.`suframa`, nfs.`suframa_ativo`, nfs.`total_icms`, nfs.`livre_debito` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
//Coloquei esse nome na vari�vel porque na sess�o j� existe uma vari�vel com o nome de id_empresa
$id_empresa_nota 	= $campos[0]['id_empresa'];
$id_banco               = $campos[0]['id_banco'];

if($campos[0]['finalidade'] == 'C') {
    $finalidade = 'CONSUMO';
}else if($campos[0]['finalidade'] == 'I') {
    $finalidade = 'INDUSTRIALIZA��O';
}else {
    $finalidade = 'REVENDA';
}

$forma_pagamento 	= $campos[0]['forma_pagamento'];
$data_emissao_nf 	= $campos[0]['data_emissao'];
$snf_devolvida		= $campos[0]['snf_devolvida'];
if($campos[0]['data_emissao_snf'] != '0000-00-00') $data_emissao_snf = data::datetodata($campos[0]['data_emissao_snf'], '/');
$id_pais                = $campos[0]['id_pais'];
$suframa_nf             = $campos[0]['suframa'];
$suframa_ativo_nf	= $campos[0]['suframa_ativo'];
$id_uf_cliente		= $campos[0]['id_uf'];

$total_icms 		= number_format($campos[0]['total_icms'], 2, ',', '.');
$livre_debito 		= $campos[0]['livre_debito'];
	
//Significa que a Nota Fiscal � Livre de D�bito, essa Mensagem ser� mostrada mais abaixo ...
if($livre_debito == 'S') $msn_livre_debito = ' <b>(Livre de D�bito Propag / Mkt)</b>';
	
//Aqui verifica o Tipo de Nota
$nota_sgd = ($id_empresa_nota == 1 || $id_empresa_nota == 2) ? 'N' : 'S';
/**************************************************************************************/
/*****************Fun��o que retorna todos os Valores referentes a NF******************/
/**************************************************************************************/
//Essa vari�vel � utilizada l� em baixo ...
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');
?>
<Script Language = 'Javascript' src = '../../../../js/geral.js'></Script>
<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr align='center'>
        <td align='left'>
            <b>Forma de Pagamento: </b> <?=$forma_pagamento;?>
        </td>
        <td align='right'>
            <b>Valor do Frete:</b>
        </td>
        <td align='left'>
            &nbsp;<?=number_format($calculo_total_impostos['valor_frete'], 2, ',', '.');?>
        </td>
    </tr>
    <tr align='center'>
        <td align='left'>
            <b>Finalidade: </b> <?=$finalidade;?>
        </td>
        <td align='right'>
            <b>Outras Despesas Acess�rias:</b>
        </td>
        <td align='left'>
            &nbsp;<?=number_format($calculo_total_impostos['outras_despesas_acessorias'], 2, ',', '.');?>
        </td>
    </tr>
    <tr>
        <td align='left'>
            <b>UF: </b>
            <?
                $sql = "SELECT sigla 
                        FROM `ufs` 
                        WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
                $campos_uf = bancos::sql($sql);
                echo $campos_uf[0]['sigla'];
            ?>
        </td>
        <td align='right'>
        <?
/*********************************Controle com os Textos de Suframa*********************************/
            if($suframa_nf > 0 && $suframa_ativo_nf == 'S') {//Cliente possui o Suframa Ativo ...
                if($suframa_nf == 1 ) {//�rea de Livre  
                    echo '<b>Desconto de ICMS = '.number_format(genericas::variavel(40), 2, ',', '.').' % </b>';
                }else if($suframa_nf == 2) {//Zona Franca de Manaus ...
                    echo '<b>Desconto de PIS + Cofins = </b>'.number_format((genericas::variavel(20)+genericas::variavel(21)), 2, ',', '.').' % e ICMS = '.number_format(genericas::variavel(40), 2, ',', '.').' % ';
                }
            }
        ?>
        </td>
        <td align='left'>
        <?
            if($suframa_nf > 0 && $suframa_ativo_nf == 'S') {//Cliente possui o Suframa Ativo ...
                echo ':&nbsp;'.number_format(abs($calculo_total_impostos['desconto']), 2, ',', '.');
            }
        ?>
        </td>
    </tr>
    <tr>
        <td align='left'>
        <?
//Se existir dados referentes a NF de Devolu��o do Cliente, ent�o apresento estes aqui na Tela de Itens da NF ...
            if(!empty($snf_devolvida) && !empty($data_emissao_snf)) {
        ?>
            <b>NF de Remessa do Cliente </b>
            <font color='darkblue'>
                <b>NF <?=$snf_devolvida;?> de <?=$data_emissao_snf;?></b>
            </font>
        <?
            }
        ?>
        </td>
        <td align='right'>
            <b>Valor Total dos Produtos:</b>
        </td>
        <td align='left'>
            &nbsp;<?=number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.');?>
        </td>
    </tr>
    <tr>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <?
/****************************************************************************/
/******************Nova Implementa��o 12 de Setembro de 2008******************/
/****************************************************************************/
//Controle p/ atualiza��o autom�tica do cabe�alho ...
/*Aqui eu guardo no Cabe�alho da Nota Fiscal o Total do IPI em Reais, acredito que o Total 
do IPI esteje sendo utilizado em algum tipo de Relat�rio ...*/
        if($data_emissao_nf >= '2008-09-12') {//A partir dessa data come�ou a gravar o ICMS de forma autom�tica ...
            $sql = "UPDATE `nfs` SET `total_icms` = '$calculo_total_impostos[valor_icms]', `total_ipi` = '".$calculo_total_impostos['valor_ipi']."' WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
        }else {
            $sql = "UPDATE `nfs` SET `total_ipi` = '".$calculo_total_impostos['valor_ipi']."' WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
        }
        bancos::sql($sql);
/**************************************************************************************/
/***********************Controle p/ exibi��o de Dados**********************************/
/**************************************************************************************/
        if($data_emissao_nf == '0000-00-00') {
            $logica_erp_lotus = 'erp';
    /*Se a Data de Emiss�o da NF for menor que 12 de Setembro de 2008, ent�o 
    s� exibe dados de Lotus*/
        }else if($data_emissao_nf < '2008-09-12') {
    //L�gica para exibi��o dos Dados do Lotus ou do ERP quando necess�rio ...
            $logica_erp_lotus = 'lotus';			
        }else {//Nova L�gica ...
            $logica_erp_lotus = 'erp';
        }

        if($logica_erp_lotus == 'lotus') {
?>
    <tr>
        <td>
            <b><font color='darkblue'>Lotus </font> - Valor do IPI:</b>
            <?=number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <b><font color='darkblue'>Lotus </font> - (N�o incluso no Total da NF R$) Valor do ICMS:</b>
        </td>
        <td>
            &nbsp;<?=$total_icms;?>
        </td>
    </tr>
    <tr>
        <td colspan='3'>
            <b><font color='darkblue'>Lotus </font> - Valor da Nota Fiscal <?=$msn_livre_debito;?>:</b> <?=number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');?>
        </td>
    </tr>
    <tr>
        <td colspan='3'>
            <b><font color='darkblue'>Lotus </font></b> - P/ c�lculo da % IPI Frete e Despesas Acess�rias, o sistema busca o > valor de IPI dos Itens de NF
        </td>
    </tr>
<?
//Nova L�gica, s� mostra dados do ERP ...
        }else {
            $exibir = 1;//Por padr�o, o correto seria mostrar todos os textos, mas agora ...
            //Existir� uma nova l�gica p/ controlar a apresenta��o dos Textos de acordo com a CFOP ...
            if($campos_cfop[0]['cfop_industrial'] == '7.101') $exibir = 0;//Nessa CFOP acima 
?>
    <tr>
        <td>
        <?
            if($exibir == 1) {
        ?>
            <b>Base de C�lculo ICMS Bits Bedames Riscador: </b> <?=number_format($calculo_total_impostos['base_calculo_icms_bits_bedames_riscador'], 2, ',', '.');?>
        <?
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($exibir == 1) {
        ?>
            <b>Base de C�lculo do ICMS: </b>
        </td>
        <td>
        &nbsp;<?=number_format($calculo_total_impostos['base_calculo_icms'], 2, ',', '.')?>
        <?
            }
        ?>
        </td>
    </tr>
    <tr>
        <td>
        <?
            if($exibir == 1) {
        ?>
            <b>Base de C�lculo ICMS C/ Red: </b> <?=number_format($calculo_total_impostos['base_calculo_icms_c_red'], 2, ',', '.')?>
        <?
            }
        ?>
        </td>
        <td align='right'>
            <b>Valor do ICMS: </b>
        </td>
        <td>
            &nbsp;
        <?
//Valor do ICMS ...
            if(strpos($calculo_total_impostos['valor_icms'], '.') > 0 || is_numeric($calculo_total_impostos['valor_icms'])) {//Nem sempre esse valor � num�rico ...
                echo number_format($calculo_total_impostos['valor_icms'], 2, ',', '.');
            }else {//�s vezes ele pode ser Isento tamb�m ...
                echo $calculo_total_impostos['valor_icms'];
            }
        ?>
        </td>
    </tr>
    <tr>
        <td>
        <?
            if($exibir == 1) {
        ?>
            <b>Base de C�lculo ICMS S/ Red: </b> <?=number_format($calculo_total_impostos['base_calculo_icms_s_red'], 2, ',', '.')?>
        <?
            }
        ?>
        </td>
        <td align='right'>
            <b>Valor do IPI: </b>
        </td>
        <td>
            &nbsp;<?=number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.');?>
        </td>
    </tr>
<?
            if($exibir == 1) {
?>
    <tr>
        <td>
            <b>Base de C�lculo do ICMS ST: </b> <?=number_format($calculo_total_impostos['base_calculo_icms_st'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <b>Valor do ICMS ST: </b>
        </td>
        <td>
            &nbsp;<?=number_format($calculo_total_impostos['valor_icms'], 2, ',', '.');?>
        </td>
    </tr>
<?
            }
?>
    <tr>
        <td>
        <?
            if($exibir == 1) {
        ?>
                <b>Isento: </b> <?=number_format($calculo_total_impostos['isento'], 2, ',', '.')?>
        <?
            }
        ?>
        </td>
        <td align='right'>
            <b>Valor Total da Nota <?=$msn_livre_debito;?>:</b>
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
            <b>P/ c�lculo do IPI Frete e Despesas Acess�rias, o sistema calcula proporcional a % do IPI de cada item. </b>
        </td>
    </tr>
    <tr>
        <td>
            <b>Isento ST:</b> <?=number_format($calculo_total_impostos['isento_st'], 2, ',', '.');?>
        </td>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
<?
        }
?>
</table>