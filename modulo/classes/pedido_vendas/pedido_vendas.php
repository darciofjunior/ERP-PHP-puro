<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
session_start('funcionarios');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) {
    //segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../');
}

//Busca o nome do Cliente, o Contato + o id_cliente_contato p/ poder buscar com + detalhes os dados do cliente
$sql = "SELECT c.id_pais, c.razaosocial, c.id_uf, c.id_cliente, c.credito, cc.nome, pv.* 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
        INNER JOIN `clientes_contatos` cc ON cc.id_cliente_contato = pv.id_cliente_contato 
        INNER JOIN `clientes` c ON c.id_cliente = cc.id_cliente 
        WHERE pvi.`id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
$campos             = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
$id_empresa_nota    = $campos[0]['id_empresa'];
$id_pais            = $campos[0]['id_pais'];
$id_cliente         = $campos[0]['id_cliente'];
$razao_social       = $campos[0]['razaosocial'];
$contato            = $campos[0]['nome'];
$credito            = $campos[0]['credito'];

if($campos[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento4'];
if($campos[0]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento3'].$prazo_faturamento;
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
$tipo_nfe_nfs   = ($campos[0]['tipo_nfe_nfs'] == 'S') ? ' - Saída' : ' - Entrada';

$prazo_faturamento.= $tipo_nota.$tipo_nfe_nfs;
$tipo_moeda     = ($id_pais != 31) ? 'U$' : 'R$';
?>
<html>
<head>
<title>.:: Visualizar Pedido(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho'>
        <td height='17' align='left'>
            <font color='#49D2FF' size='2'>
                Cliente:
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$id_cliente.' - '.$razao_social;?>
            </font>
            <font color='#49D2FF' size='2'>
                / Cr&eacute;dito:
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$credito;?>
            </font>
            <font color='#49D2FF' size='2'>
                / Contato:
            </font>
            <font color='#FFFFFF' size="2">
                <?=$contato;?>
            </font>
            <font color='#49D2FF' size='2'>
                &nbsp;/ Forma de Venda:
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$prazo_faturamento;?>
            </font>
        </td>
    </tr>
</table>
<?
//Aqui começa a segunda parte, a parte em q calcula e exibe os itens
    $sql = "SELECT cc.nome, t.nome AS transportadora, pv.id_pedido_venda, pv.id_funcionario, pv.id_empresa, DATE_FORMAT(pv.data_emissao, '%d/%m/%Y') AS data_emissao, pv.data_sys, pvi.qtde 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
            INNER JOIN `transportadoras` t ON t.id_transportadora = pv.id_transportadora 
            INNER JOIN `clientes_contatos` cc ON cc.id_cliente_contato = pv.id_cliente_contato 
            WHERE pvi.`id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' ORDER BY pv.id_pedido_venda ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='7'>
            Visualizar Pedido(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>N.º Ped</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data de Emissão</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Transportadora</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Empresa</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Login</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data e Hora</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Qtde</b>
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes de Pedido' class='link'>
                <?=$campos[$i]['id_pedido_venda'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
            <?=$campos[$i]['transportadora'];?>
        </td>
        <td>
        <?
            //Busco a Empresa do Pedido ...
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    where id_empresa = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $nomefantasia   = $campos_empresa[0]['nomefantasia'];

            if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
                $apresentar = $nomefantasia.' (NF)';
            }else {
                $apresentar = $nomefantasia.' (SGD)';
            }
//Significa q é da Albafer
            if($campos[$i]['id_empresa'] == 1) {
                if($campos[$i]['id_empresa_divisao'] == 1) {
                    $apresentar.= ' - CABRI - HEINZ';
                }else {
                    $apresentar.= ' - WARRIOR';
                }
//Significa q é da Tool Master
            }else if($campos[$i]['id_empresa'] == 2) {
                if($campos[$i]['id_empresa_divisao'] == 5) {
                    $apresentar.= ' - NVO';
                }else {
                    $apresentar.= ' - TOOL';
                }
//Significa q estava selecionada o Grupo
            }else if($campos[$i]['id_empresa'] == 4) {
                $apresentar.= ' - TODAS DIVISÕES';
            }
            echo $apresentar;
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT l.login 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
                    WHERE f.`id_funcionario` = ".$campos[$i]['id_funcionario']." LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
        ?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' - '.substr($campos[$i]['data_sys'], 11, 8);?>
        </td>
        <td>
            <font color='darkblue'>
                <b><?=segurancas::number_format($campos[$i]['qtde'], 0, '.');?></b>
            </font>
        </td>
    </tr>
<?
            $qtde_total+= $campos[$i]['qtde'];
        }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan="5">
            &nbsp;
        </td>
        <td align='right'>
            <font color='yellow'>
                Qtde Total =>
            </font>
            &nbsp;
        </td>
        <td>
            <?=$qtde_total;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>