<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/intermodular.php');
require('../../../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');

if(!empty($_POST['cmb_condicao_faturamento'])) {
    //Aki eu busco o id_pedido_venda através do id_pedido_venda_item ...
    $sql = "SELECT id_pedido_venda 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda_item` = '$_POST[id_pedido_venda_item]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    //Atualizo a Condição de Faturamento do Pedido de Venda ...
    $sql = "UPDATE `pedidos_vendas` SET `condicao_faturamento` = '$_POST[cmb_condicao_faturamento]' where `id_pedido_venda` = '".$campos[0]['id_pedido_venda']."' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('DATA DE MATERIAL PRONTO ALTERADA COM SUCESSO !')
        opener.parent.itens.document.form.submit()
        window.close()
    </Script>
<?
}

//Busca a Condição de Faturamento do Pedido, através do $id_pedido_venda_item passado por parâmetro ...
$sql = "SELECT pv.condicao_faturamento, pvi.id_produto_acabado 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
        WHERE pvi.`id_pedido_venda_item` = '$_GET[id_pedido_venda_item]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Condição de Faturamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Condição de Faturamento
    if(!combo('form', 'cmb_condicao_faturamento', '', 'SELECIONE A CONDIÇÃO DE FATURAMENTO !')) {
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
<?
    if(!empty($cmb_condicao_faturamento)) {
?>
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
        if(document.form.nao_atualizar.value == 0) {
            parent.parent.itens.document.form.submit()
            parent.parent.rodape.document.form.submit()
        }
<?
    }
?>
}
</Script>
</head>
<body onload='document.form.cmb_condicao_faturamento.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_pedido_venda_item' value='<?=$_GET[id_pedido_venda_item];?>'>
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Condição de Faturamento
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font color='yellow'>
                <b>Produto: </b>
            </font>
            <?=intermodular::pa_discriminacao($campos[0]['id_produto_acabado']);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Condição de Faturamento:</b>
        </td>
        <td>
            <select name='cmb_condicao_faturamento' title='Selecione a Condição de Faturamento' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
            <?
                $condicao_faturamento = array_sistema::condicao_faturamento();
                for($i = 1; $i <= count($condicao_faturamento); $i++) {
                    $selected = ($campos[0]['condicao_faturamento'] == $i) ? 'selected' : '';
            ?>
                <option value='<?=$i;?>' <?=$selected;?>><?=strtoupper($condicao_faturamento[$i]);?></option>
            <? 
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_condicao_faturamento.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>