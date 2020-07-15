<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/producao/os/incluir.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

$id_os_item = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_os_item'] : $_GET['id_os_item'];

if(!empty($_POST['id_os_item'])) {
/**************Procedimento para alterar a Quinta Etapa do Custo**************/
/*1) Aqui eu busco alguns dados da OS e Item, mais o PA através do $id_os_item e também 
quem é o Fornecedor dessa OS ...*/
    $sql = "SELECT oi.id_produto_insumo_ctt, oi.id_item_pedido, oss.id_fornecedor, 
            oss.id_pedido, pa.id_produto_acabado, pa.operacao_custo 
            FROM `oss_itens` oi 
            INNER JOIN `oss` ON oss.id_os = oi.id_os 
            INNER JOIN `ops` ON ops.id_op = oi.id_op 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ops.id_produto_acabado 
            WHERE oi.id_os_item = '$_POST[id_os_item]' ";
    $campos                 = bancos::sql($sql);
    $id_produto_insumo_ctt  = $campos[0]['id_produto_insumo_ctt'];//Utilizo p/ alterar o Prod e Preço do Item de Ped
    $id_item_pedido         = $campos[0]['id_item_pedido'];//Utilizo p/ alterar o Prod e Preço do Item de Ped
    $id_fornecedor          = $campos[0]['id_fornecedor'];
    $id_pedido              = $campos[0]['id_pedido'];//Utilizo p/ alterar o Prod e Preço do Item de Pedido
    $id_produto_acabado     = $campos[0]['id_produto_acabado'];
    $operacao_custo = $campos[0]['operacao_custo'];
//2) Verifico quem é o id_produto_acabado_custo através do id_produto_acabado e operacao_custo
    $sql = "SELECT id_produto_acabado_custo 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $id_produto_acabado_custo   = $campos[0]['id_produto_acabado_custo'];
//3) Aqui eu atribuo o novo PI na Quinta Etapa do Custo do Produto Acabado Custo
    $sql = "UPDATE `pacs_vs_pis_trat` SET `id_produto_insumo` = '$_POST[cmb_produto_insumo]' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' and `id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
    bancos::sql($sql);
/*4) Busco qual é o Preço de Custo desse novo PI na lista de Preços desse Fornecedor da OS em questão
que vai me servir p/ atualizar no Item da OS e também no Item do Pedido de Compras ...*/
    $sql = "SELECT preco_faturado, lote_minimo_reais 
            FROM fornecedores_x_prod_insumos 
            WHERE `id_fornecedor` = '$id_fornecedor' 
            AND `id_produto_insumo` = '$cmb_produto_insumo' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $preco_lista    = $campos[0]['preco_faturado'];
    if($campos[0]['lote_minimo_reais'] > 0) {
//Não permitimos essa alteração por falta de segurança, já que é necessário modificações / alterações em vários locais ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTE ITEM TEM LOTE MÍNIMO !!! NÃO É POSSÍVEL ALTERAR POR ESTA FUNÇÃO, FALAR COM ROBERTO !')
        window.close()
    </Script>
<?
        exit;
    }
/*****************************************************************************/
//Aqui eu estou substituindo o Item da OS pelo novo PI com seu respectivo Preço ...
    $sql = "UPDATE `oss_itens` SET `id_produto_insumo_ctt` = '$_POST[cmb_produto_insumo]', `preco_pi` = '$preco_lista' WHERE `id_os_item` = '$_POST[id_os_item]' LIMIT 1 ";
    bancos::sql($sql);
//6) Verifico se essa OSS já está importada em Pedido anteriormente ...
    if(!empty($id_pedido)) {
//Se está já estiver em Pedido, eu busco essa qtde do Item para poder refazer o cálculo ...
        $sql = "SELECT qtde 
                FROM `itens_pedidos` 
                WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $valor_total    = $preco_lista * $campos[0]['qtde'];
//7) Atualizando os Dados de Item de Pedido, com o Novo Produto e Preço ...
        $sql = "UPDATE `itens_pedidos` SET `id_produto_insumo` = '$cmb_produto_insumo', `preco_unitario` = '$preco_lista', `valor_total` = '$valor_total' WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Busco do Produto Insumo do Item da OSS
$sql = "SELECT id_produto_insumo_ctt 
        FROM `oss_itens` 
        WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$id_produto_insumo_ctt  = $campos[0]['id_produto_insumo_ctt'];
?>
<html>
<head>
<title>.:: Alterar Produto Insumo ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Produto Insumo
    if(!combo('form', 'cmb_produto_insumo', '', 'SELECIONE UM PRODUTO INSUMO !')) {
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.document.form.submit()
    }
}
</Script>
</head>
<body onload="document.form.cmb_produto_insumo.focus()" onunload="atualizar_abaixo()">
<form name="form" method="post" action='' onsubmit="return validar()">
<input type='hidden' name='id_os_item' value="<?=$id_os_item;?>">
<input type='hidden' name='id_produto_insumo_ctt' value="<?=$id_produto_insumo_ctt;?>">
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Produto Insumo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto Insumo:</b>
        </td>
        <td>
            <select name="cmb_produto_insumo" title="Selecione o Produto Insumo" class="combo">
            <?
            //Só listo os Produtos que são do Grupo TRATAMENTO TERMICO - GALVANOPLASTIA
                $sql = "SELECT pi.id_produto_insumo, pi.discriminacao 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                        WHERE pi.`ativo` = '1' 
                        AND g.`id_grupo` = '11' ORDER BY pi.discriminacao ";
                echo combos::combo($sql, $id_produto_insumo_ctt);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_produto_insumo.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'> 
        </td>
    </tr>
</table>
</form>
</body>
</html>