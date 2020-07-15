<?
require('../../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../../lib/menu/menu.php');//Se essa tela não foi aberta como sendo Pop-UP então eu exibo o menu ...
require('../../../../lib/calculos.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
require('../../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) {
    switch($opcao) {
        case 1://Significa que veio do Menu Abertas / Liberadas ...
        case 2://Significa que veio do Menu de Liberadas / Faturadas ...
        case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
        default://Significa que veio do Menu de Devolução ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
        break;
    }
}

$mensagem[1] = 'ITEM INCLUIDO COM SUCESSO !';
$mensagem[2] = 'ITEM(NS) ATUALIZADO(S) COM SUCESSO !';
$mensagem[3] = 'ITEM EXCLUIDO COM SUCESSO !';
$mensagem[4] = 'PRODUTO BLOQUEADO !!! NÃO PODE SER EXCLUIDO, PORQUE ESTÁ SENDO MANIPULADO PELO ESTOQUISTA !';
$mensagem[5] = 'NENHUM ITEM DE NOTA FISCAL PODE SER EXCLUÍDO !\nPORQUE ESTA NOTA FISCAL ESTÁ TRAVADA !';
$mensagem[6] = 'QUANTIDADE INSUFICIENTE DO ESTOQUE DISPONÍVEL PARA EXCLUSÃO DO ITEM !';
$mensagem[7] = 'NENHUM ITEM DE NOTA FISCAL PODE SER EXCLUÍDO DEVIDO TER SIDO PAGO A COMISSÃO DA MESMA !';

//Aqui é a Busca da Variável de Vendas
$fator_desc_maximo_venda = genericas::variavel(19);

//Busca o nome do Cliente, o Contato + o id_cliente_contato p/ poder buscar com + detalhes os dados do cliente
$sql = "SELECT c.`id_cliente`, c.`cod_cliente`, c.`id_pais`, c.`id_uf`, c.`tributar_ipi_rev`, 
        IF(c.`nomefantasia` = '', c.`razaosocial`, CONCAT(c.`nomefantasia`, ' (', c.`razaosocial`, ')')) AS cliente, c.`credito`, c.`cidade`, 
        c.`trading`, c.`cod_suframa`, nfs.* 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$id_cliente 		= $campos[0]['id_cliente'];
$cod_cliente		= $campos[0]['cod_cliente'];
$id_pais                = $campos[0]['id_pais'];
$id_uf_cliente		= $campos[0]['id_uf'];
$tributar_ipi_rev       = $campos[0]['tributar_ipi_rev'];
$cliente                = $campos[0]['cliente'];
$credito                = $campos[0]['credito'];
$cidade                 = $campos[0]['cidade'];
$trading                = $campos[0]['trading'];
$cod_suframa		= $campos[0]['cod_suframa'];

//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
$id_empresa_nota        = $campos[0]['id_empresa'];
$id_transportadora      = $campos[0]['id_transportadora'];
$id_nf_num_nota		= $campos[0]['id_nf_num_nota'];
$finalidade             = $campos[0]['finalidade'];
$frete_transporte       = $campos[0]['frete_transporte'];
$forma_pagamento        = $campos[0]['forma_pagamento'];
$data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');

$snf_devolvida		= $campos[0]['snf_devolvida'];
if($campos[0]['data_emissao_snf'] != '0000-00-00') $data_emissao_snf = data::datetodata($campos[0]['data_emissao_snf'], '/');

$suframa_nf             = $campos[0]['suframa'];
$suframa_ativo_nf       = $campos[0]['suframa_ativo'];

if($campos[0]['vencimento4'] > 0) $prazo_faturamento_nf = '/'.$campos[0]['vencimento4'];
if($campos[0]['vencimento3'] > 0) $prazo_faturamento_nf = '/'.$campos[0]['vencimento3'].$prazo_faturamento_nf;
if($campos[0]['vencimento2'] > 0) {
    $prazo_faturamento_nf = $campos[0]['vencimento1'].'/'.$campos[0]['vencimento2'].$prazo_faturamento_nf;
}else {
    $prazo_faturamento_nf = ($campos[0]['vencimento1'] == 0) ? 'À vista' : $campos[0]['vencimento1'];
}
$valor_dolar_nota       = $campos[0]['valor_dolar_dia'];
$prazo_medio            = $campos[0]['prazo_medio'];//Vou utilizar mais abaixo ...
$total_icms             = number_format($campos[0]['total_icms'], 2, ',', '.');
$observacao_nf          = $campos[0]['observacao'];
$status                 = $campos[0]['status'];
$livre_debito           = $campos[0]['livre_debito'];
//Significa que a Nota Fiscal é Livre de Débito, essa Mensagem será mostrada mais abaixo ...
if($livre_debito == 'S') {
    $msn_livre_debito = '<font color="darkgreen" title="Livre de Débito Propaganda / Marketing" style="cursor:help"><b> (LD)</b></font>';
}else {
    $msn_livre_debito = '';
}
//Aqui verifica o Tipo de Nota
if($id_empresa_nota == 1 || $id_empresa_nota == 2) {
    $nota_sgd = 'N';//var surti efeito lá embaixo
}else {
    $nota_sgd = 'S'; //var surti efeito lá embaixo
}
$tipo_nfe_nfs = $campos[0]['tipo_nfe_nfs'];

//Aqui é a verifica se esta Nota é de Saída ou Entrada
if($campos[0]['tipo_nfe_nfs'] == 'S') {
    $rotulo_tipo_nfe_nfs = ' (Saída) ';
}else {
    $rotulo_tipo_nfe_nfs = ' (Entrada) ';
}
//Significa que o Cliente é do Tipo Internacional
$tipo_moeda = ($id_pais != 31) ? 'U$ | R$ ' : 'R$ ';
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            <font size='3'>
                NF N.º 
                <font color='#5DECFF' size='3'>
                    <?=faturamentos::buscar_numero_nf($id_nf, 'S').$msn_livre_debito;?>
                </font>
        <?
            echo $rotulo_tipo_nfe_nfs;
        
            $sql = "SELECT `nomefantasia` 
                    FROM `empresas` 
                    WHERE `id_empresa` = '$id_empresa_nota' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            echo '<font color="#5DECFF"> - '.$campos_empresa[0]['nomefantasia'].'</font></font>';
            
            $vetor_nfs = array('EM ABERTO', 'LIBERADA P/ FATURAR', 'FATURADA', 'EMPACOTADA', 'DESPACHADA', 'CANCELADA', 'DEVOLUÇÃO');
            echo ' - Dt Emissão: <font color="#5DECFF">'.$data_emissao.' - '.$vetor_nfs[$status].' </font>';
            
//Aki busca a transportadora
            $sql = "SELECT `nome` 
                    FROM `transportadoras` 
                    WHERE `id_transportadora` = '$id_transportadora' LIMIT 1 ";
            $campos_transportadora = bancos::sql($sql);
            echo ' - Transp: <font color="#5DECFF">'.$campos_transportadora[0]['nome'].' </font>';

            if($frete_transporte == 1) {
                echo '<font color="#5DECFF">CIF (POR NOSSA CONTA - REMETENTE)</font>';
            }else if($frete_transporte == 2) {
                echo '<font color="#5DECFF">FOB (POR CONTA DO CLIENTE - DESTINATÁRIO)</font>';
            }
        ?>
            - Forma de Venda:
            <font color='#49D2FF'>
        <?
            echo $prazo_faturamento_nf.' ';

            $vetor_forma_pagamento  = array_sistema::forma_pagamento();

            foreach($vetor_forma_pagamento as $indice => $rotulo) {
                if(!empty($forma_pagamento) && $forma_pagamento == $indice) {
                    echo $rotulo;
                    break;
                }
            }
        ?>
            </font>
        <?
//Aqui eu verifico se a NF possui uma Carta de Correção ...
            $sql = "SELECT `id_carta_correcao` 
                    FROM `cartas_correcoes` 
                    WHERE `id_nf` = '$id_nf' LIMIT 1 ";
            $campos_carta_correcao = bancos::sql($sql);
            if(count($campos_carta_correcao) == 1) {
        ?>
            &nbsp;-&nbsp;
            <a href="javascript:nova_janela('../../../classes/nf_carta_correcao/itens/relatorio/imprimir.php?id_carta_correcao=<?=$campos_carta_correcao[0]['id_carta_correcao'];?>', 'ITENS', 'F')" class="link">
                <img src="../../../../imagem/carta.jpeg" title="Detalhes de Carta de Correção" alt="Detalhes de Carta de Correção" height="20" border="1">
                <font color='yellow' size='-1'>
                    Carta de Correção
                </font>
            </a>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td height='21' align='left'>
            <a href = "javascript:nova_janela('../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$id_cliente;?>&nao_exibir_menu=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <font color='#49D2FF'>
                    Cliente:
                    <font color='#FFFFFF'>
                    <?
                        echo $cod_cliente.' - '.$cliente.' / '.$cidade;
                        
                        if($id_uf_cliente > 0) {
                            $sql = "SELECT `sigla` 
                                    FROM `ufs`
                                    WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
                            $campos_uf = bancos::sql($sql);
                            echo ' / '.$campos_uf[0]['sigla'];
                        }else {
                            $sql = "SELECT `pais` 
                                    FROM `paises` 
                                    WHERE `id_pais` = '$id_pais' LIMIT 1 ";
                            $campos_pais = bancos::sql($sql);
                            echo ' / '.$campos_pais[0]['pais'];
                        }
                    ?>
                    </font>
                </font>
                <img src = '../../../../imagem/propriedades.png' title='Detalhes de Cliente' alt='Detalhes de Cliente' style='cursor:pointer' border='0'>
            </a>
            <font color='#49D2FF' size='2'>
                / Cr&eacute;dito:
            </font>
            <?
                /*Somente os Usuários Roberto "62" ou Dárcio "98 porque programa" que podem 
                estar trocando o Crédito do Cliente por aqui p/ facilitar programação ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
            ?>
                    <a href = "javascript:nova_janela('../../../financeiro/cadastro/credito_cliente/detalhes.php?id_cliente=<?=$id_cliente;?>&pop_up=1', 'POP', '', '', '', '', 450, 780, 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Cr&eacute;dito do Cliente'>
                        <font color='#FFFFFF'>
            <?
                }
                echo financeiros::controle_credito($id_cliente);
                /*Somente os Usuários Roberto "62" ou Dárcio "98 porque programa" que podem 
                estar trocando o Crédito do Cliente por aqui p/ facilitar programação ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) echo '</font></a>';
            ?>
            &nbsp;-&nbsp;
            <a href="javascript:nova_janela('../../../classes/pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$id_cliente;?>', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Relatório de Pendências' class='link'>
                <font color='#48FF73' size='-1'>
                    Pendências
                </font>
            </a>
        </td>
    </tr>
</table>
<table width='90%' border='1' align='center' cellspacing='0' cellpadding='0'>
    <tr class='linhanormal'>
        <td colspan='21' bgcolor='#CECECE'>
            <font size='2'>
                <b>Obs NF: 
                <font color='red'>
                    <?=$observacao_nf;?>
                </font>
            </b></font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='21' bgcolor='#CECECE'>
            <font size='2'>
                <b>Texto da NF: 
                    <font color='red'>
                        <?=$campos[0]['texto_nf'];?>
                    </font>
                </b>
            </font>
        </td>
    </tr>
<?
//Aqui eu verifico todos os Pedidos q estão atrelados a esse NF
    $sql = "SELECT DISTINCT(pvi.`id_pedido_venda`), pv.*, cc.`nome`, t.`nome` AS transportadora 
            FROM `nfs_itens` nfsi 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = pv.`id_cliente_contato` 
            INNER JOIN `transportadoras` t ON t.`id_transportadora` = pv.`id_transportadora` 
            WHERE nfsi.`id_nf` = '$id_nf' ";
    $campos_pedidos = bancos::sql($sql);
    $linhas = count($campos_pedidos);
    if($linhas > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Pedido(s) Atrelado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º Pedido do Cliente <br/>/ Contato
        </td>
        <td>
            Nosso <br/>N.º Pedido
        </td>
        <td>
            Forma de Venda
        </td>
        <td>
            <b>Prazo Médio</b>
        </td>
        <td>
            <b>Valor Ped / %<br/>sobre a NF</b>
        </td>
        <td>
            Transportadora
        </td>
    </tr>
<?
        $sql = "SELECT DISTINCT(pvi.`id_pedido_venda`), SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS valor_nf 
                FROM `nfs_itens` nfsi 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
                WHERE nfsi.`id_nf` = '$id_nf' 
                GROUP BY nfsi.`id_nf` ";
        $campos_valor_nf    = bancos::sql($sql);
        $valor_nf           = $campos_valor_nf[0]['valor_nf'];
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos_pedidos[$i]['num_seu_pedido'].' / '.$campos_pedidos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos_pedidos[$i]['id_pedido_venda'];?>
        </td>
        <td align='left'>
        <?
            if($campos_pedidos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos_pedidos[$i]['vencimento4'];
            if($campos_pedidos[$i]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos_pedidos[$i]['vencimento3'].$prazo_faturamento;
            if($campos_pedidos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos_pedidos[$i]['vencimento1'].'/'.$campos_pedidos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos_pedidos[$i]['vencimento1'] == 0) ? 'À vista' : $campos_pedidos[$i]['vencimento1'];
            }
//Aqui é a verificação do Tipo de Empresa
            $sql = "SELECT `nomefantasia` 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos_pedidos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $nomefantasia   = $campos_empresa[0]['nomefantasia'];
//Aqui é a verificação do Tipo de Nota
            if($nota_sgd == 'S') {
                $rotulo_sgd = ' DDL - '.$nomefantasia.' (SGD)';
            }else {
                $rotulo_sgd = ' DDL - '.$nomefantasia.' (NF)';
//Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
                if($finalidade == 'C') {
                    $rotulo_sgd.= '/CONSUMO';
                }else if($finalidade == 'I') {
                    $rotulo_sgd.= '/INDUSTRIALIZAÇÃO';
                }else {
                    $rotulo_sgd.= '/REVENDA';
                }
            }
            echo 'FAT '.$prazo_faturamento.= $rotulo_sgd;
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td>
        <?
//Aqui eu verifico se o (Prazo Médio do Pedido + 15) está irregular com o Prazo Médio da NF ...
            if(($campos_pedidos[$i]['prazo_medio'] + 15) < $prazo_medio) {
                $font = '<font color="red">';
            }else {
                $font = '<font color="green">';
            }
            echo $font.$campos_pedidos[$i]['prazo_medio'];
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT DISTINCT(pvi.id_pedido_venda), SUM(pvi.qtde * pvi.preco_liq_final) AS valor_pedido 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` AND pvi.`id_pedido_venda` = '".$campos_pedidos[$i]['id_pedido_venda']."' 
                    WHERE nfsi.`id_nf` = '$id_nf' GROUP BY pvi.id_pedido_venda ";
            $campos_pedido 	= bancos::sql($sql);
            $valor_pedido 	= $campos_pedido[0]['valor_pedido'];
            echo number_format($valor_pedido, 2, ',', '.').' / ';//Valor Pedido ...
            //Faço esse artíficio p/ q não dê erro de Divisão por Zero ...
            if($valor_nf == 0) $valor_nf = 1;
            echo number_format(($valor_pedido / $valor_nf * 100), 2, ',', '.').' %';
        ?>
        </td>
        <td>
            <?=$campos_pedidos[$i]['transportadora'];?>
        </td>
    </tr>
<?
            $sql = "SELECT `observacao` 
                    FROM `follow_ups` 
                    WHERE `identificacao` = '".$campos_pedidos[$i]['id_pedido_venda']."' 
                    AND `origem` = '2' ";
            $campos_observacoes = bancos::sql($sql);
            $linhas_observacoes = count($campos_observacoes);
            if($linhas_observacoes > 0) {
                for($j = 0; $j < $linhas_observacoes; $j++) {
?>
    <tr class='linhanormal'>
        <td colspan='21' bgcolor='#FFFFFF'>
            <img src = '../../../../imagem/exclamacao.gif' title='Detalhes da Última Venda' alt='Detalhes da Última Venda' height='30' border='0'>
            <font size='2'>
                <b>Obs. Ped. (<?=$campos_pedidos[$i]['id_pedido_venda']?>): 
                <font color='red'>
                    <?=$campos_observacoes[$j]['observacao'];?>
                </font>
            </b></font>
            <img src = '../../../../imagem/exclamacao.gif' title='Detalhes da Última Venda' alt='Detalhes da Última Venda' height='30' border='0'>
        </td>
    </tr>
<?
                }
            }
        }
    }
?>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='6'>
            <iframe name='detalhes' id='detalhes' src = '../../../classes/follow_ups/detalhes.php?identificacao=<?=$id_nf;?>&origem=5' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
<?
    /**********************************************************************/
    /*************************Observação do Cliente************************/
    /**********************************************************************/
    //Busco a observação do Cliente que foi registrada no Follow-Up ...
    $sql = "SELECT `observacao` 
            FROM `follow_ups` 
            WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
            AND `origem` = '15' LIMIT 1 ";
    $campos_follow_up = bancos::sql($sql);
    if(count($campos_follow_up) == 1) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='6' bgcolor='yellow'>
            <img src = '../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
            <font size='3'>
                <b>Obs. do Cliente: 
                <font color='blue'>
                    <?=$campos_follow_up[0]['observacao'];?>
                </font>
                </b>
            </font>
            <img src = '../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
        </td>
    </tr>
<?
    }
    /**********************************************************************/
    
    /**********************************************************************/
    /****************************Livre de Débito***************************/
    /**********************************************************************/
    if($livre_debito == 'S') {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='6'>
            <font color='red' size='4'>
                <marquee behavior='alternate' direction='right'>
                    <b>NÃO PODE GERAR BOLETO OU DUPLICATA - LIVRE DE DÉBITO !!!</b>
                </marquee>
            </font>
        </td>
    </tr>
<?
    }
    /**********************************************************************/
?>
</table>
<?
/**************************************************************************************/
/*****************Função que retorna todos os Valores referentes a NF******************/
/**************************************************************************************/
    $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf, 'NF');
/*******************************************************************************************/
/*Se essa variável retornar 0, então retorno os Itens que estão com o Peso Unitário zerado 
e que estão influenciando no cálculo errado da NF - geralmente acontece com as NF(s) mais 
antigas, pois a partir de agora, o Sistema cerca com essa segurança no Incluir Itens ...*/
    if($calculo_total_impostos['peso_lote_total_kg'] == 0) echo faturamentos::itens_nf_peso_unitario_zerado($id_nf);
/*******************************************************************************************/
/**************************************************************************************/
//Aqui começa a segunda parte, a parte em q calcula e exibe os itens
//A operação de Fat. do PA sempre será Industrial quando o Cliente possuir a marcação de Tributar IPI REV e for daqui do Brasil ...
    $sql = "SELECT ged.`id_empresa_divisao`, ged.`margem_lucro_minima`, nfsi.`id_nfs_item`, 
            nfsi.`id_nf_item_devolvida`, nfsi.`id_representante`, nfsi.`id_classific_fiscal`, nfsi.`peso_unitario`, 
            nfsi.`qtde`, nfsi.`qtde_devolvida`, nfsi.`qtde_nfe`, nfsi.`vale`, nfsi.`ipi`, nfsi.`valor_unitario`, 
            nfsi.`valor_unitario_exp`, nfsi.`preco_nfe`, nfsi.`comissao_new`, nfsi.`comissao_extra`, 
            nfsi.`icms`, nfsi.`icms_intraestadual`, nfsi.`reducao`, nfsi.`iva`, nfsi.`icms_creditar_rs`, 
            ov.`id_orcamento_venda`, ov.`artigo_isencao`, ovi.`id_produto_acabado_discriminacao`, 
            ovi.`desc_cliente`, ovi.`promocao`, ovi.`desc_extra`, ovi.`acrescimo_extra`, ovi.`desc_sgd_icms`, 
            pa.`referencia`, pa.`discriminacao`, pa.`operacao`, pa.`origem_mercadoria`, 
            pa.`observacao` AS observacao_produto, pa.`status_top`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
            pa.`peso_atualizado`, REPLACE(pv.`num_seu_pedido`, '-', '_') AS num_seu_pedido, 
            DATE_FORMAT(pv.`faturar_em`, '%d/%m/%Y') AS faturar_em, pvi.`id_pedido_venda_item`, 
            pvi.`id_pedido_venda`, pvi.`id_orcamento_venda_item`, pvi.`id_produto_acabado`, 
            pvi.`id_funcionario`, pvi.`qtde_pendente`, pvi.`qtde_faturada`, pvi.`prazo_entrega`, 
            pvi.`preco_liq_final`, pvi.`margem_lucro`, pvi.`margem_lucro_estimada`, pvi.`status` 
            FROM `nfs_itens` nfsi 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            WHERE nfsi.`id_nf` = '$id_nf' ORDER BY pvi.`id_pedido_venda`, pa.`discriminacao` ";
    $campos_itens   = bancos::sql($sql);
    $linhas_itens   = count($campos_itens);
//Verifica se tem pelo menos um item na Nota Fiscal
    if($linhas_itens > 0) {
        if($status == 0) {//Se o Status da Nota Fiscal = "Em Aberto", então eu apresento essa linha ...
?>
<table width='90%' border=1 align='center' cellspacing='0' cellpadding='0'>
    <tr class='atencao' align='center'>
        <td colspan='21' bgcolor='red'>
            <font color='white' size='4'>
                <b>Nota não liberada p/ faturamento !!!</b>
            </font>
        </td>
    </tr>
</table>
<?
        }
?>
<table width='90%' border=1 align='center' cellspacing='0' cellpadding='0' onmouseover='total_linhas(this)'>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='N.º do Pedido' style='cursor:help'>
                <b>
                    N.º&nbsp;Ped<br/>
                    Faturar em<br/>
                <?
//Nessa coluna, o rótulo será dinâmico de acordo com o Cliente ...
                    if($campos[0]['id_pais'] == 31) {//Cliente Nacional ...
                        $title = 'Observação do Produto';
                        $descricao = 'Obs. Prod.';
                    }else {//Cliente Internacional ...
                        $title = 'Desconto s/ Lista';
                        $descricao = 'Desc. s/ Lista';
                    }
                ?>
            <font title="<?=$title;?>" style='cursor:help'>
                <b><?=$descricao;?></b>
            </font>
        </td>
        <td bgcolor='#CECECE' colspan='4'><b>Quantidade</b></td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title="Estoque Dispon&iacute;vel" style='cursor:help'><b>E.D.</b></font>
            <font title="Pe&ccedil;as por Embalagem" style='cursor:help'><br/><b>Pc/Emb</b></font>
            <font color="darkblue" title="Prazo de Entrega do Or&ccedil;amento" style='cursor:help'><br/><b>P.Ent.Orc</b></font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'><b>Produto</b></td>
        <td rowspan='2' bgcolor='#CECECE'><font title="Informa&ccedil;&otilde;es" style='cursor:help'><b>Info</b></font></td>
        <td rowspan='2' bgcolor='#CECECE' width="8%"><font title="Desconto SGD/ICMS" style='cursor:help'><b>Descontos % </b></td>
        <td rowspan='2' bgcolor='#CECECE'><font title="Acr&eacute;scimo Extra" style='cursor:help'><b>Acr. <br/>Ext. %</b></td>
        <td rowspan='2' bgcolor='#CECECE'><font title="Comiss&atilde;o <?=$tipo_moeda;?>" style='cursor:help'><b>Comiss&atilde;o <br/><?=$tipo_moeda;?></b></td>
        <td rowspan='2' bgcolor='#CECECE' width="2%"><font title="Pre&ccedil;o Liquido Final em <?=$tipo_moeda;?>" style='cursor:help'><b>Pre&ccedil;o L.<br/>Final <?=$tipo_moeda;?></b></font></td>
        <td rowspan='2' bgcolor='#CECECE' width="2%"><b>Total Lote <?=$tipo_moeda;?> <br/>s/ IPI</b></td>
        <td rowspan='2' bgcolor='#CECECE'><b>TOTAL <br/>IPI</b></td>
        <td rowspan='2' bgcolor='#CECECE'><b>TOTAL <br/>ICMS <br/>ST</b></td>
        <td rowspan='2' bgcolor='#CECECE'><b>ICMS</b></td>
        <td rowspan='2' bgcolor='#CECECE'><b>Dados Adicionais</b></td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <font title="Quantidade Faturada" style='cursor:help'><b>Fat.</b></font>
        </td>
        <td bgcolor='#CECECE'>
            <font title="Quantidade Devolvida" style='cursor:help'><font color="dark"><b>Dev.</b></font>
        </td>
        <td bgcolor='#CECECE'>
            <font title="Quantidade de Vale" style='cursor:help'><b>Vale</b></font>
        </td>
        <td bgcolor='#CECECE'>
            <font title="Quantidade do Pacote" style='cursor:help'><b>Pac.</b></font>
        </td>
    </tr>
<?
        $tx_financeira = custos::calculo_taxa_financeira($campos_itens[0]['id_orcamento_venda']);
        $id_pedido_venda_antigo = '';//Variável para controle das cores no Pedido

        $vetor_prazos_entrega                   = vendas::prazos_entrega();
        $vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();
        //Esse vetor tem a idéia de armazenas todas as Classificações Fiscais existentes em todos os Itens das Notas Fiscais ...
        $vetor_classific_fiscal = array();

        /*Se a Nota Fiscal for uma Devolução coloco essa Letra E que equivale a Entrada, senão 
        S que equivale a Saída ...*/
        $tipo_negociacao        = ($status == 6) ? 'E' : 'S';

        for($i = 0;  $i < $linhas_itens; $i++) {
            /*Esse controle é de extrema importância porque em casos de "Gato por Lebre", preciso pegar 
            os impostos do Gato ...

            Ex: o cliente comprou MRH-042 "Gato", mas estamos enviando o MRT-042 "Lebre ou substituto" ...*/
            $id_produto_acabado_utilizar = (!empty($campos_itens[$i]['id_produto_acabado_discriminacao'])) ? $campos_itens[$i]['id_produto_acabado_discriminacao'] : $campos_itens[$i]['id_produto_acabado'];

            //Essas variáveis serão utilizadas mais abaixo ...
            $dados_produto      = intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf_cliente, $id_cliente, $id_empresa_nota, $finalidade, $tipo_negociacao, $id_nf);
            
            //Se for NF de Devolução, usará o campo qtde_devolvida p/ calculo ...                       
            $qtde_nota          = ($status == 6) ? $campos_itens[$i]['qtde_devolvida'] : $campos_itens[$i]['qtde'];
?>
    <tr class='linhanormal'>
        <td align='center'>
            <a href="javascript:nova_janela('detalhes_pedido.php?id_pedido_venda=<?=$campos_itens[$i]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes de Pedido' class='link'>
            <?
                if($id_pedido_venda_antigo != $campos_itens[$i]['id_pedido_venda']) {
//Aki significa que mudou para outro N. de Pedido e vai exibir uma nova sequência desses mesmos
                    $id_pedido_venda_antigo = $campos_itens[$i]['id_pedido_venda'];
            ?>
                    <font color='red'>
                        <?=$campos_itens[$i]['id_pedido_venda'];?>
                    </font>
            <?
//Ainda são os mesmos Pedidos
                }else {
                    echo $campos_itens[$i]['id_pedido_venda'];
                }
            ?>
            </a>
            <br/>
            <?=$campos_itens[$i]['faturar_em'];?>
            <br/>
            <?
//Nessa coluna, o rótulo será dinâmico de acordo com o Cliente ...
                if($campos[0]['id_pais'] == 31) {//Cliente Nacional ...
                    if(!empty($campos_itens[$i]['observacao_produto'])) echo "<img width='22' height='18' title='".$campos_itens[$i]['observacao_produto']."' src='../../../../imagem/olho.jpg'>";
//Aqui verifica se tem pelo menos 1 Follow Up Registrado
                    $sql = "SELECT l.login, pafu.* 
                            FROM `produtos_acabados_follow_ups` pafu 
                            INNER JOIN `funcionarios` f ON f.id_funcionario = pafu.id_funcionario 
                            INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
                            WHERE pafu.`id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' ORDER BY pafu.`data_sys` DESC ";
                    $campos2 = bancos::sql($sql);
                    if(count($campos2) > 0) {
                        $title = 'Existem '.count($campos2).' Follow-Up(s) Registrado(s) p/ este Produto Acabado';
                        $color = 'red';
                    }else {
                        $title = 'Não há nenhum Follow-Up Registrado p/ este Produto Acabado';
                        $color = '#6473D4';
                    }
            ?>
                <a href = "javascript:nova_janela('../../../producao/cadastros/produto_acabado/follow_up.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' color='<?=$color;?>'>
                        OBS
                    </font>
                </a>
            <?
                }else {//Cliente Internacional ...
                        $desconto_sem_lista = (1 - $campos_itens[$i]['valor_unitario']) * 100;
                        echo number_format($desconto_sem_lista, 2, ',', '.').' %';
                }
            ?>
        </td>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                //Significa que o número é inteiro ...
                $casas_decimais = (strstr($qtde_nota, '.00') == '.00') ? 1 : 2;
                echo number_format($qtde_nota, $casas_decimais, ',', '.');
                if($campos_itens[$i]['qtde_nfe'] > 0) echo ' / <font color="brown"><b>NFe='.(int)$campos_itens[$i]['qtde_nfe'].'</b></font>';

                $peso_total_geral+= $campos_itens[$i]['peso_unitario'] * $qtde_nota;

//Significa q estou acessando essa tela como um Pop-Up, então preciso estar desabilitando os comandos da linha porque da erro de Path de JS
                if($pop_up == 1) {
                    echo '<br/><font title="Peso Total sobre o Peso" style="cursor:help">'.number_format($campos_itens[$i]['peso_unitario'] * $qtde_nota, 2, ',', '.').' kgs';
                }else {
                    //Significa que já foi atualizado o Peso Atualizado do PA para 4 casas decimais
                    $color = ($campos_itens[$i]['peso_atualizado'] == 'S') ? '#6473D4' : 'red';
                    echo '<br/><font title="Peso Total sobre o Peso" style="cursor:help">';
                    
                    if($status == 0) {//Somente no Status de "Em Aberto" que é permitido alterarmos o Peso Unitário ...
            ?>
<!--Esses parâmetros tela1, tela2 servem para o pop-up fazer a atualização na tela de baixo, caso exista frames-->
                <a href = "javascript:nova_janela('../../../classes/produtos_acabados/alterar_peso_unitario.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>&id_nfs_item=<?=$campos_itens[$i]['id_nfs_item'];?>&tela1=window.opener.parent.itens&tela2=window.opener.parent.rodape', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
            <?
                    }
            ?>
                    <font color='<?=$color;?>'>
                        <?=number_format($campos_itens[$i]['peso_unitario'] * $qtde_nota, 4, ',', '.');?>
                    </font>
            <?
                    if($status == 0) {//Somente no Status de "Em Aberto" que é permitido alterarmos o Peso Unitário ...
            ?>
                </a>
            <?
                    }
                    echo ' kgs';
                }
            ?>
            </font>
        </td>
		<td align='center'>
		<?
                    if($status == 6) {//Significa que eu acessei uma NF de Devolução ...
                        //Aqui eu busco o N.º da NF de Saída através do Item que foi devolvido ...
                        $sql = "SELECT `id_nf` 
                                FROM `nfs_itens` 
                                WHERE `id_nfs_item` = '".$campos_itens[$i]['id_nf_item_devolvida']."' LIMIT 1 ";
                        $campos_nfs             = bancos::sql($sql);
                        $nf_saida               = '<br/> NF '.faturamentos::buscar_numero_nf($campos_nfs[0]['id_nf'], 'S');
                        $qtde_devolvida         = $campos_itens[$i]['qtde_devolvida'];
                        $title                  = 'Detalhes da NF de Saída';
                    }else {//Significa que eu acessei uma NF normal ...
                        //Busca da Qtde Devolvida do Item, p/ apresentar na NF Principal ...
                        $sql = "SELECT SUM(qtde_devolvida) AS qtde_devolvida 
                                FROM `nfs_itens` 
                                WHERE `id_nf_item_devolvida` = '".$campos_itens[$i]['id_nfs_item']."' ";
                        $campos_item_devolvido  = bancos::sql($sql);
                        $qtde_devolvida         = $campos_item_devolvido[0]['qtde_devolvida'];
                        $title                  = "Detalhes da NF de Devolução";
                    }
		?>
                    <font color='red'>
                        <b>
                    <?
                        if(strstr($qtde_devolvida, '.') != '.00') {//Significa que o decimal do N.º é Dif. de Zero 
                            echo number_format($qtde_devolvida, 2, ',', '.');
                        }else {
                            echo number_format($qtde_devolvida, 1, ',', '.');
                        }
                        echo $nf_saida;
                    ?>
                        </b>
                    </font>
		</td>
		<td align='center'>
                <?
                    //Aqui eu verifico se tem existe algum histórico do "Vale de Venda" que foi enviado ...
                    $sql = "SELECT `id_vale_venda_item` 
                            FROM `vales_vendas_itens` 
                            WHERE `id_pedido_venda_item` = '".$campos_itens[$i]['id_pedido_venda_item']."' LIMIT 1 ";
                    $campos_vale_venda_item = bancos::sql($sql);
                    if(count($campos_vale_venda_item) == 1) {//Existe Histórico ...
                ?>
                    <a href="javascript:nova_janela('../../../producao/programacao/estoque/gerenciar/detalhes_vales_vendas.php?id_pedido_venda_item=<?=$campos_itens[$i]['id_pedido_venda_item'];?>', 'DETALHES_VALES_VENDAS', '', '', '', '', 380, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Vales de Vendas' class='link'>
                        <?=number_format($campos_itens[$i]['vale'], 0, ',', '.');?>
                    </a>
                <?
                    }else {//Não existe histórico ...
                        echo segurancas::number_format($campos_itens[$i]['vale'], 0, '.');
                    }
                ?>
		</td>
		<td>
			<?=number_format($qtde_nota - $campos_itens[$i]['vale'], 1, ',', '.');?>
		</td>
		<td>
			<a href="javascript:nova_janela('../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Estoque" class="link">
			<?
                            $vetor = estoque_acabado::qtde_estoque($campos_itens[$i]['id_produto_acabado']);
                            echo number_format($vetor[3], 1, ',', '.').'<br/>';
			?>
			</a>
			<?
				//Traz a quantidade de peças por embalagem da embalagem principal daquele produto ...
				$sql = "SELECT `pecas_por_emb` 
                                        FROM `pas_vs_pis_embs` 
                                        WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' 
                                        AND `embalagem_default` = '1' LIMIT 1 ";
				$campos_pecas_emb = bancos::sql($sql);
				if(count($campos_pecas_emb) == 1) {
					echo number_format($campos_pecas_emb[0]['pecas_por_emb'], 3, ',', '.');
				}else {
					echo 0;
				}
				//Verifico qual é o Prazo do Item do Orçamento p/ Printar na Tela de Itens ...
				foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
                                    if($campos_itens[$i]['prazo_entrega'] == $indice) {//Se igual
                                        echo '<br/><font color="darkblue"><b>'.ucfirst(strtolower($prazo_entrega)).'</b></font>';
                                        break;
                                    }
				}
			?>
		</td>
		<td align='left'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <?
                        //Aqui eu busco o código do PA do Cliente na Base ...
                        $sql = "SELECT `cod_cliente` 
                                FROM `pas_cod_clientes` 
                                WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' 
                                AND `id_cliente` = '$id_cliente' LIMIT 1 ";
                        $campos_cod_cliente = bancos::sql($sql);
                        $cProdCliente       = (count($campos_cod_cliente) == 1) ? '-cProdCliente:"'.$campos_cod_cliente[0]['cod_cliente'].'"' : '';

                        echo intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, 1, 1, $campos_itens[$i]['id_produto_acabado_discriminacao']).'-N.PED:"'.$campos_itens[$i]['num_seu_pedido'].'"'.$cProdCliente;

                        if($campos_itens[$i]['status_top'] == 1) {
                            echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'> (TopA)</font>";
                        }else if($campos_itens[$i]['status_top'] == 2) {
                            echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'> (TopB)</font>";
                        }
                        //Se existir tem q fazer uma marcação nesse item com [*]
                        if($campos_itens[$i]['artigo_isencao'] == 1) echo '<font color="black"> [*] </font>';
                        echo '&nbsp;';
                        //Aqui eu verifico se existe alguma promoção ...
                        if($campos_itens[$i]['promocao'] == 'A') {
                            $title = 'Promoção A';
                            echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(PA)</b></font>";
                        }else if($campos_itens[$i]['promocao'] == 'B') {
                            $title = 'Promoção B';
                            echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(PB)</b></font>";
                        }else if($campos_itens[$i]['promocao'] == 'C') {
                            $title = 'Promoção C';
                            echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(PC)</b></font>";
                        }else if($campos_itens[$i]['promocao'] == 1) {
                            $title = 'Promoção Antiga';
                            echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(P)</b></font>";
                        }
                    ?>
                    </font>
		</td>
		<td align='center'>
		<?
                    if($campos_itens[$i]['operacao_custo'] == 0) {
                        echo "<font title='Opera&ccedil;&atilde;o de Custo' style='cursor:help'>OC</font>=<font title='Industrialização' style='cursor:help'>I</font>";
                        if($campos_itens[$i]['operacao_custo_sub'] == 0) {
                            echo "<font title='Sub-Opera&ccedil;&atilde;o Industrial' style='cursor:help'>I</font>";
                        }else {
                            echo "<font title='Sub-Opera&ccedil;&atilde;o Revenda' style='cursor:help'>R</font>";
                        }
                    }else {
                        echo "<font title='Opera&ccedil;&atilde;o de Custo' style='cursor:help'>OC</font>=<font title='Revenda' style='cursor:help'>R</font>";
                    }
                    
                    if($tributar_ipi_rev == 'S') {//Marcação Realizada no Cadastro do Cliente "TRIBUTAR o PA OC=Revenda COMO INDUSTRIAL" ...
                        //A cor azul representa que é uma Operação Falsa ...
                        $color          = 'darkblue';
                        $operacao       = ($dados_produto['operacao'] == 0) ? 'I' : 'R';
                        $title_operacao = ($dados_produto['operacao'] == 0) ? 'Revenda' : 'Industrial';//A apresentação que eu faço aqui, realmente é a Oposta ...
                    }else {
                        //A cor vermelha representa que não é trambicagem ...
                        $color          = 'red';
                        $operacao       = ($dados_produto['operacao'] == 0) ? 'I' : 'R';
                        $title_operacao = ($dados_produto['operacao'] == 0) ? 'Industrial' : 'Revenda';
                    }
                    echo '<br/>OF=<font color="'.$color.'" title="Operação Real de Faturamento => '.$title_operacao.'" style="cursor:help"><b>'.$operacao.'</b></font>';
		?>
                    <font title="Classifica&ccedil;&atilde;o Fiscal => <?=$dados_produto['classific_fiscal'];?>" style='cursor:help'>
                        <br/>CF=<?=$dados_produto['id_classific_fiscal'];?>
                    </font>
                    <font title='Origem / Situa&ccedil;&atilde;o Tribut&aacute;ria' style='cursor:help'>
                        <br/>CST=<?=$dados_produto['cst'];?>
                    </font>
                    <font>
                        <br/>CFOP=<?=$dados_produto['cfop'];?>
                    </font>
		</td>
		<td>
			Cliente=
		<?
			echo number_format($campos_itens[$i]['desc_cliente'], 2, ',', '.');
			if($campos_itens[$i]['desc_cliente'] < 0) echo '<font color="red"><b> (Acr)</b></font>';
			echo '<br/>';
		?>
			Extra=
		<?	
			$coeficiente = (1 - $campos_itens[$i]['desc_cliente'] / 100) * (1 - $campos_itens[$i]['desc_extra'] / 100) * (1 + $campos_itens[$i]['acrescimo_extra'] / 100) * (1 - $tx_financeira / 100);
			$desconto_total 	= (1 - $coeficiente) * 100;	
		?>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5' title="Desc.Total p/comissao = <?=number_format($desconto_total, 2, ',', '.');?>% - Levamos em conta a dif. do ICMS p/UF = SP x desc.ICMS/SGD e a dif.da tx.fin.p/30 ddl x tx.fin.pz.medio do ORC" style='cursor:help'>
                            <?=number_format($campos_itens[$i]['desc_extra'], 2, ',', '.').'<br/>';?>
			</font>
			<?if($nota_sgd == 'S') {echo 'SGD=';}else {echo 'ICMS=';}?>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                            <?=number_format($campos_itens[$i]['desc_sgd_icms'], 2, ',', '.');?>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                            <?=number_format($campos_itens[$i]['acrescimo_extra'], 2, ',', '.');?>
			</font>
		</td>
		<td>
		<?
                    //O id_pais será muito útil mais abaixo ...
                    $sql = "SELECT `id_pais`, `nome_fantasia` 
                            FROM `representantes` 
                            WHERE `id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
                    $campos_representante = bancos::sql($sql);
                    if(count($campos_representante) > 0) {
                ?>
                    <a href="javascript:nova_janela('../../../vendas/representante/alterar2.php?passo=1&id_representante=<?=$campos_itens[$i]['id_representante'];?>&pop_up=1', 'POP', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?
                        /*Se existir país no cadastro desse Representante, então representa que o mesmo é externo, 
                        consequentemente apresento o seu Supervisor ...*/
                        if(!is_null($campos_representante[0]['id_pais'])) {
                            //Busca do Supervisor ...
                            $sql = "SELECT r.`nome_fantasia` AS supervisor 
                                    FROM `representantes_vs_supervisores` rs 
                                    INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                                    WHERE rs.`id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
                            $campos_supervisor  = bancos::sql($sql);
                            $title              = 'Supervisor => '.$campos_supervisor[0]['supervisor'];
                        }else {
                            $title              = '';
                        }
                ?>
                        <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' title='<?=$title;?>' style='cursor:help' <?=$color;?>>
                            <?=$campos_representante[0]['nome_fantasia'];?>
                        </font>
                    </a>
                <?
                    }

                    if($campos_itens[$i]['id_representante'] == 71 && $campos_itens[$i]['id_funcionario'] > 0) {//Se o Grupo for PME, listo qual o funcionário do Grupo que fez a Venda ...
                        $sql = "SELECT SUBSTRING_INDEX(UPPER(nome), ' ', 1) AS nome 
                                FROM `funcionarios` 
                                WHERE `id_funcionario` = '".$campos_itens[$i]['id_funcionario']."' LIMIT 1 ";
                        $campos_func = bancos::sql($sql);
                        echo '<br/><font color="darkblue"><b>'.$campos_func[0]['nome'];
                    }
                    $preco_total_lote = $campos_itens[$i]['valor_unitario'] * $campos_itens[$i]['qtde'];
                    $valor_produto_todos_itens+= round($preco_total_lote, 2);
                    /************************************************************************************************/
                    /*********************************Logística Nova Margem de Lucro*********************************/
                    /************************************************************************************************/		
                    //Aqui eu entro com a Margem de Lucro dentro da Tabela da Nova Margem de Lucro ...
                    echo '<br/><font color="brown" title="Comiss&atilde;o = '.number_format($campos_itens[$i]['comissao_new'], 2, ',', '.').'% + Extra = '.number_format($campos_itens[$i]['comissao_extra'], 2, ',', '.').'%" style="cursor:help">'.number_format($campos_itens[$i]['comissao_new'] + $campos_itens[$i]['comissao_extra'], 2, ',', '.').'% ';
                    if($id_pais != 31) {//Quando o País for Estrangeiro, calcula tanto em Dólar como R$ ...
                        echo '<br/>'.number_format(($campos_itens[$i]['qtde'] * $campos_itens[$i]['valor_unitario_exp']) * ($campos_itens[$i]['comissao_new'] / 100), 2, ',', '.').' | ';
                    }
                    echo number_format(vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new'] + $campos_itens[$i]['comissao_extra']), 2, ',', '.');
                    /************************************************************************************************/
		?>
		</td>
		<td align='right'>
		<?
                    if($id_pais != 31) echo number_format($campos_itens[$i]['valor_unitario_exp'], 2, ',', '.').' | ';
                    echo number_format($campos_itens[$i]['valor_unitario'], 2, ',', '.');
                    if($campos_itens[$i]['preco_nfe'] > 0) echo ' / <font color="brown"><b>NFe='.number_format($campos_itens[$i]['preco_nfe'], 2, ',', '.').'</b></font>';
			
                    if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                        $margem         = custos::margem_lucro($campos_itens[$i]['id_orcamento_venda_item'], $tx_financeira, $id_uf_cliente, $campos_itens[$i]['preco_liq_final']);
                        
                        $custo_margem_lucro_zero = $margem[2];//preco_custo_zero
                        $soma_margem+= $custo_margem_lucro_zero * $qtde_nota;
                        
                        $cor_instantanea        = ($margem[0] < 0) ? 'red' : '#E8E8E8';//$margem[0] valor real da margem
                        $cor_gravada            = ($campos_itens[$i]['margem_lucro'] < 0) ? 'red': '#E8E8E8';
                        $cor_estimada           = ($campos_itens[$i]['margem_lucro_estimada'] < 0) ? 'red': '#E8E8E8';
				
                        $valores                = vendas::calcular_ml_min_pa_vs_cliente($campos_itens[$i]['id_produto_acabado'], $id_cliente);
                        $rotulo_ml_min          = $valores['rotulo_ml_min'];
                        $rotulo_preco           = $valores['rotulo_preco'];
                        $margem_lucro_minima    = $valores['margem_lucro_minima'];
		?>
                        <a href = "javascript:nova_janela('../../../vendas/orcamentos/itens/alterar_margem_lucro.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>&preco_liq_final=<?=number_format($campos_itens[$i]['valor_unitario'], 2, ',', '.');?>&margem_lucro=<?=$margem[1];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                            <font color="<?=$cor_gravada;?>" title='Margem de Lucro M&iacute;nima' style='cursor:help'><br/>
                                <?=$rotulo_ml_min.number_format($margem_lucro_minima, 1, ',', '');?>
                            </font>
		<?	
                            if((double)strtr($margem[1], ',%', '. ') < $margem_lucro_minima) {//Se a ML do ORC < ML do Grupo ...
		?>
                            <font color="<?=$cor_gravada;?>" title='Pre&ccedil;o Ideal' style='cursor:help'><br/>
                            <?
                                $pco_ideal = round($campos_itens[$i]['valor_unitario'] / ((double)strtr($margem[1], ',%', '. ') / 100 + 1) * ($margem_lucro_minima / 100 + 1), 2);
                                echo $rotulo_preco.number_format($pco_ideal, 2, ',', '');
                            ?>
                            </font>
                            <img src = '../../../../imagem/bloco_negro.gif' width='5' height='5' border='0'>
		<?
                            }
		?>
                        </a>
		<?
                    }
		?>
		</td>
		<td align='right'>
		<?
                    if($id_pais != 31) echo number_format($campos_itens[$i]['valor_unitario_exp'] * $qtde_nota, 2, ',', '.').' | ';
                    echo number_format($campos_itens[$i]['valor_unitario'] * $qtde_nota, 2, ',', '.');//Aqui eu Sempre trago em R$ ...
                    
                    if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
		?>
                        <!--************************************************************************-->
                        <!--A folha de estilo fica aqui dentro do Loop porque as cores de fonte 
                        dos IDs se comportaram de acordo com o que foi definido acima pelo PHP ...-->
                        <style type='text/css'>
                            #id_ml_instantanea<?=$i;?>::-moz-selection {
                                background:#A9A9A9;
                                color:<?=$cor_instantanea;?>
                            }
                            #id_ml_gravada<?=$i;?>::-moz-selection {
                                background:#A9A9A9;
                                color:<?=$cor_gravada;?>
                            }
                            #id_ml_estimada<?=$i;?>::-moz-selection {
                                background:#A9A9A9;
                                color:<?=$cor_estimada;?>
                            }
                        </style>
                        <!--************************************************************************-->
                    
                        <a href = "javascript:nova_janela('/erp/albafer/modulo/vendas/orcamentos/itens/alterar_margem_lucro.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>&preco_liq_final=<?=number_format($campos_itens[$i]['valor_unitario'], 2, ',', '.');?>&margem_lucro=<?=$margem[1];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                            <font color="<?=$cor_instantanea;?>" id='id_ml_instantanea<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'><br/>
                                <?='ML='.$margem[1];//Valor Descritivo da Margem ...?>
                            </font>
                            <font color="<?=$cor_gravada;?>" id='id_ml_gravada<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'>
                                <br/><?='MLG='.number_format($campos_itens[$i]['margem_lucro'], 2, ',', '.');?>
                            </font>
                            <font color="<?=$cor_estimada;?>" id='id_ml_estimada<?=$i;?>' title='Margem de Lucro Estimada' style='cursor:help'>
                                <br/><?='MLEst='.number_format($campos_itens[$i]['margem_lucro_estimada'], 2, ',', '.');?>
                            </font>
                        </a>
		<?
                    }
		?>
		</td>
		<td>
		<?
                    //Esse controle é somente para printar um texto + abaixo, se encontrar 1 item apenas nessa condição já está bom ...
                    if(!isset($existe_artigo_isencao)) {//Verifica se o item corrente tem a lei de Artigo de Isenção
                        if($campos_itens[$i]['artigo_isencao'] == 1) $existe_artigo_isencao = 1;
                    }
                    if($dados_produto['ipi'] > 0) {
                        echo number_format($dados_produto['ipi'], 2, ',', '.').' %';
                        echo '<br><b>'.$tipo_moeda.number_format($preco_total_lote * ($dados_produto['ipi'] / 100), 2, ',', '.').'</b>';
                        $valor_ipi_todos_itens+= round($preco_total_lote * ($dados_produto['ipi'] / 100), 2);
                    }else {
                        echo 'S/IPI';
                    }
		?>
		</td>
		<td>
		<?
                    $calculo_impostos_item = calculos::calculo_impostos($campos_itens[$i]['id_nfs_item'], $id_nf, 'NF');
                    echo '<font title="ICMS INTRAESTADUAL '.number_format($campos_itens[$i]['icms_intraestadual'], 2, ',', '.').' - IVA AJUSTADO '.number_format($calculo_impostos_item['iva_ajustado'] * 100, 2, ',', '.').'%" style="cursor:help">IVA='.number_format($campos_itens[$i]['iva'], 2, ',', '.').' %</font>';

                    echo '<br/>ST=<font color="blue"><b>'.number_format(($calculo_impostos_item['valor_icms_st'] * 100) / $preco_total_lote, 2, ',', '.').'%</b></font>';
                    echo '<br/><b>'.$tipo_moeda.number_format($calculo_impostos_item['valor_icms_st'], 2, ',', '.').'</b></font>';
		?>
		</td>
		<td>
		<?
                    if($dados_produto['icms'] > 0) {
                        echo number_format($campos_itens[$i]['icms'], 2, ',', '.').' %';
                        echo '<br/><font color="blue"><b>RED '.number_format($dados_produto['reducao'], 2, ',', '.').'%</b></font>';
                        echo '<br/><b>'.$tipo_moeda.number_format($calculo_impostos_item['valor_icms'], 2, ',', '.').'</b></font>';
                    }else {
                        echo 'S/ICMS';
                    }
                    if($campos_itens[$i]['icms_creditar_rs'] > 0) {//Se existir ICMS à creditar em R$ então ...
                ?>
                    <img src='../../../../imagem/certo.gif' title='ICMS à Creditar em R$ => <?=number_format($campos[$i]['icms_creditar_rs'], 2, ',', '.');?>' style='cursor:help'>
                <?
                    }
		?>
		</td>
                <td>
                <?
                    echo 'BC ICMS = <b>'.$tipo_moeda.number_format($calculo_impostos_item['base_calculo_icms'], 2, ',', '.').'</b>';
                    echo '<br/>ISENTO = <b>'.$tipo_moeda.number_format($calculo_impostos_item['isento'], 2, ',', '.').'</b>';
                    echo '<br/>BC IPI = <b>'.$tipo_moeda.number_format($calculo_impostos_item['base_calculo_ipi'], 2, ',', '.').'</b>';
                    echo '<br/>BC ST = <b>'.$tipo_moeda.number_format($calculo_impostos_item['base_calculo_icms_st'], 2, ',', '.').'</b>';
                ?>
                </td>
	</tr>
<?
                    $comissao_por_item_rs = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new']);
                    $total_comissoes_dos_itens_rs+= $comissao_por_item_rs;

                    $comissao_extra_por_item_rs = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_extra']);
                    $total_comissoes_extras_dos_itens_rs+= $comissao_extra_por_item_rs;
		}//Fim do for
?>
    <tr align='center'>
        <td colspan='5' class='linhadestaque'>
            <font color='yellow'>
                Taxa Financeira:
            </font>
            <?=number_format($tx_financeira, 2, ',', '.');?>
        </td>
        <td class='linhadestaque'>
            <font color='yellow'>
                &nbsp;<?if($id_pais != 31) echo 'Valor Dólar da Nota: '.number_format($valor_dolar_nota, 4, ',', '.');?>
            </font>
        </td>
        <td colspan='3' class='linhadestaque' align='left'>
            <font color='yellow'>
                Comissão Média %:
            </font>
            <?
//Aqui nessa parte do cálculo eu pego a comissão média e divido pela qtde de Itens da Nota Fiscal
//Esse desvio para não dar erro de divisão por Zero, Dárcio
                if($calculo_total_impostos['valor_total_produtos'] > 0) {
                    $comissao_media         = round(($total_comissoes_dos_itens_rs / $calculo_total_impostos['valor_total_produtos']) * 100, 2);
                    $comissao_media_extra   = round(($total_comissoes_extras_dos_itens_rs / $calculo_total_impostos['valor_total_produtos']) * 100, 2);
                }else {
                    $comissao_media         = round($total_comissoes_dos_itens_rs * 100, 2);
                    $comissao_media_extra   = round($total_comissoes_extras_dos_itens_rs * 100, 2);
                }
                echo '<font title="Comiss&atilde;o = '.number_format($comissao_media, 2, ',', '.').'% + Extra = '.number_format($comissao_media_extra, 2, ',', '.').'%" style="cursor:help">'.number_format($comissao_media + $comissao_media_extra, 2, ',', '.');
                /*****************************************************************************************/
                //Enquanto a NF estiver como em Aberto ou se for Devolucao, então eu guardo a Comissão Média do Pedido de Venda ...
                if($status == 0 || $status == 6) {
                    $sql = "UPDATE `nfs` SET `comissao_media` = '$comissao_media', `comissao_media_extra` = '$comissao_media_extra' WHERE `id_nf` = '$id_nf' LIMIT 1 ";
                    bancos::sql($sql);
                }
                /*****************************************************************************************/
            ?>
        </td>
        <td colspan='8' class='linhadestaque' align="right">
            <span class='style12'>
                &nbsp;
                <?
                    if(!empty($existe_artigo_isencao)) {//Aqui printa se existir artigo de isenção ...
                        echo '<br/><font color="black"> [*] -> </font> SUSPENSO IPI, CONF.ART.29, PARÁGRAFO 1, ALÍNEA A E B, LEI 10637/02.';
                    }
                ?>
            </span>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='6' align='left'>
        <?
            $peso_nf = faturamentos::calculo_peso_nf($id_nf);
            
            if(!empty($campos[0]['id_nf_vide_nota'])) {
                if($opcao == 1 || $opcao == 3) {
        ?>
                    <a href="javascript:nova_janela('../atrelar_quantidade_volume.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>', 'PESO_UNITARIO', '', '', '', '', 650, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Atrelar Caixas Coletivas">
        <?
                }
        ?>
                        <font color='#5DECFF'>Vide Nota N.º:</font>
                    </a>
        <?
                echo faturamentos::buscar_numero_nf($campos[0]['id_nf_vide_nota'], 'S');
            }else {
                /*Só pode exibir o Link para alterar qtde de Volume, quando o usuário entrar 
                pelas opções de Em Aberto / Liberadas ou Fat. / Emp. / Despachadas ...*/
                if($opcao == 1 || $opcao == 3) {
        ?>
                    <a href="javascript:nova_janela('../atrelar_quantidade_volume.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>', 'PESO_UNITARIO', '', '', '', '', 650, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Atrelar Caixas Coletivas">
        <?
                }
        ?>
                        <font color='#5DECFF'>Qtde de Caixas: </font>
                    </a>
        <?
                echo number_format($peso_nf['qtde_caixas'], 0, ',', '.');
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
//Só pode exibir o Link para alterar qtde de Volume, quando o usuário entrar pela opção de romaneio
                if($opcao == 1 || $opcao == 3) {
        ?>
                    <a href="javascript:nova_janela('../atrelar_quantidade_volume.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>', 'PESO_UNITARIO', '', '', '', '', 650, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Atrelar Caixas Coletivas">
        <?
                }
        ?>
                        <font color='#5DECFF'>Peso Tot. Embal:</font>
                    </a>
        <?

                echo number_format($peso_nf['peso_total_emb_nf_current'], 4, ',', '.');
            }
        ?>
        </td>
        <td colspan='3' align='left' style='cursor:help' title='Peso Liquido da Nota=<?=number_format($peso_nf['peso_total_nf_current'], 4, ',', '.');?>'>
            <font color='#5DECFF'>Peso Líq. Total:</font>
            <?
                echo number_format($peso_nf['peso_liq_total_nf'], 4, ',', '.');

                if($calculo_total_impostos['difal'] > 0) {
            ?>
                <font color='#000000' size='4'>
                    <p/><?='DIFAL R$ '.number_format($calculo_total_impostos['difal'], 2, ',', '.');?>
                    <br/><?='VALOR ICMS DE DESTINO R$ '.number_format($calculo_total_impostos['valor_icms_destino'], 2, ',', '.');?>
                    <br/><?='VALOR ICMS DE REMETENTE R$ '.number_format($calculo_total_impostos['valor_icms_remetente'], 2, ',', '.');?>
                </font>
                    
            <?
                }
            ?>
        </td>
        <td colspan='8' align='left'>
            <font color='#5DECFF'>Peso Bruto Total:</font>
            <?=number_format($peso_nf['peso_bruto_total'], 4, ',', '.');?>
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='0' align='center'>
    <tr></tr>
    <tr></tr>
    <tr class='linhadestaque'>
        <td colspan='6'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='yellow' size='-5'>
                CÁLCULO DO IMPOSTO
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>BASE DE CÁLCULO DO ICMS: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['base_calculo_icms'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO ICMS: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_icms'], 2, ',', '.');?>
            </font>
        </td>
        <td colspan='2'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>BASE DE CÁLC. DO ICMS ST: </font>
                <br/>R$ 
                <?
                    echo number_format($calculo_total_impostos['base_calculo_icms_st'], 2, ',', '.');
                ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO ICMS ST: </font>
                <br/>R$ 
                <?
                    echo number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.');
                    
                    if($calculo_total_impostos['fecp'] > 0) {
                        $base_calculo_fecp  = $calculo_total_impostos['base_calculo_icms_st'];
                        $valor_fecp         = number_format(round($base_calculo_fecp * ($calculo_total_impostos['fecp'] / 100), 2), 2, '.', '');
                        $total_icms_st      = round($calculo_total_impostos['valor_icms_st'] + $valor_fecp, 2);
                        
                        echo '<br/><font color="#49D2FF">FECP = R$ '.number_format($valor_fecp, 2, ',', '.').'&nbsp;&nbsp;-&nbsp;&nbsp;TOTAL ICMS ST = R$ '.number_format($total_icms_st, 2, ',', '.').'</font>';
                    }
                ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR TOTAL DOS PRODUTOS: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO FRETE: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_frete'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO SEGURO: </font>
                <br/>R$ 0,00
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>DESCONTO: </font>
                <br/>R$ <?=number_format(abs($calculo_total_impostos['desconto']), 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>OUTRAS DESPESAS ACESSÓRIAS: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['outras_despesas_acessorias'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO IPI: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR TOTAL DA NOTA: </font>
                <br/>
                <?
                    //Se o País for Estrangeiro então mostro o Valor da Nota Fiscal em U$ também ...
                    if($id_pais != 31) echo 'U$ '.number_format($calculo_total_impostos['valor_total_nota_us'], 2, ',', '.').' | ';
                ?>
                R$ <?=number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes_calculo'); return false">
        <td colspan='14' height='21' align='left'>
            <font color='yellow' size="2">&nbsp;Detalhes: </font>
        </td>
    </tr>
    <tr align='center'>
            <td colspan='20'>
                <font face='verdana, arial, helvetica, sans-serif' size='4' color='green'>
                    <br/><b>Total de Registro(s): <?=$linhas_itens;?></b>
                </font>
            </td>
    </tr>
    <tr>
        <td colspan='14'>
            <iframe src="detalhes_calculo.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>&nao_verificar_sessao=<?=$nao_verificar_sessao;?>" name="detalhes_calculo" id="detalhes_calculo" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" width="100%" scrolling="auto"></iframe>
        </td>
    </tr>
</table>
<input type='hidden' name='opt_item_principal'>
<!-- ******************************************** -->
<input type='hidden' name='id_nf' value='<?=$id_nf?>'>
<!--Até então serve somente para armazenar o valor das mensagens-->
<input type='hidden' name='valor'>
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
<!-- ******************************************** -->
</form>
</body>
</html>
<?
        if(!empty($valor)) {
?>
        <Script Language = 'JavaScript'>
            alert('<?=$mensagem[$valor];?>')
        </Script>
<?
        }
    }else {
?>
<html>
<body>
<form name='form'>
<table width='90%' border='0' align='center'>
    <tr class='atencao' align='center'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='#FF0000'>
                <b>Nota Fiscal
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='blue'>
                    <?=faturamentos::buscar_numero_nf($id_nf, 'S');?>
                </font>
                n&atilde;o cont&eacute;m itens cadastrado.</b>
            </font>
        </td>
    </tr>
</table>
<input type='hidden' name='id_nf' value='<?=$id_nf?>'>
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
</form>
</body>
</html>
<?}?>