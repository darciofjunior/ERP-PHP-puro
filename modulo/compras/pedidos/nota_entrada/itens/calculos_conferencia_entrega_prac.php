<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');
$mensagem[1] = "<font class='confirmacao'>PESO DO PA ALTERADO COM SUCESSO.</font>";

if(!empty($_POST['txt_peso_pa_kg'])) {
    $sql = "UPDATE `produtos_acabados` SET `peso_unitario` = '$_POST[txt_peso_pa_kg]' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

//Busca o Itens da NF que foi passado por Parâmetro
$sql = "SELECT nfeh.id_nfe_historico, nfeh.id_nfe, nfeh.id_item_pedido, nfeh.valor_entregue 
        FROM `nfe_historicos` nfeh 
        INNER JOIN `itens_pedidos` ip ON ip.id_item_pedido = nfeh.id_item_pedido 
        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pi.`ativo` = '1' 
        WHERE nfeh.`id_nfe_historico` = '$id_nfe_historico' ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Conferência de Entrega do PRAC ::.</title>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Quantidade
    if(!texto('form', 'txt_peso_pa_kg', '1', '1234567890,.', 'PESO DO PA EM KG', '2')) {
        return false
    }
    return limpeza_moeda('form', 'txt_peso_pa_kg, ')
}

function calcular() {
//Peso Aço
    if(document.form.txt_peso_pa_kg.value != '') {
        if(document.form.txt_peso_pa_kg.value == '0,0000') {
/**********************Tratamentos para não dar erro no cálculo**********************/
            var peso_aco = 1
        }else {
            var peso_aco = eval(strtofloat(document.form.txt_peso_pa_kg.value))
        }
    }else {//Se for vazio 
/**********************Tratamentos para não dar erro no cálculo**********************/
        var peso_aco = 1
    }
//Peso Total
    if(document.form.txt_peso_total.value != '') {
        var peso_total = eval(strtofloat(document.form.txt_peso_total.value))
    }else {
        var peso_total = 0
    }
//Peso da Caixa de Papelão Estimado
    if(document.form.txt_peso_cx_papelao.value != '') {
        var peso_caixa_papelao_est = eval(strtofloat(document.form.txt_peso_cx_papelao.value))
    }else {
        var peso_caixa_papelao_est = 0
    }
/************************************************************************************/
//Cálculo para encontrar a qtde_pcs_estimadas
    document.form.txt_qtde_pcs_estimadas.value = (peso_total - peso_caixa_papelao_est) / peso_aco
    document.form.txt_qtde_pcs_estimadas.value = arred(document.form.txt_qtde_pcs_estimadas.value, 3, 1)
}
</Script>
</head>
<body onload='document.form.txt_peso_total.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_nfe_historico' value='<?=$id_nfe_historico;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='0' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Conferência de Entrega do PRAC
        </td>
    </tr>
    <tr class='linhadestaque' align='left'>
        <td colspan='2'>
            <font color='yellow'>
                Produto:
            </font>
            <?
                $id_item_pedido = $campos[0]['id_item_pedido'];
                $sql = "SELECT g.referencia, ip.*, pi.id_produto_insumo, pi.discriminacao 
                        FROM `itens_pedidos` ip 
                        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
                        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                        WHERE ip.`id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
                $campos_item_pedido = bancos::sql($sql);
                $id_produto_insumo  = $campos_item_pedido[0]['id_produto_insumo'];

                echo genericas::buscar_referencia($campos_item_pedido[0]['id_produto_insumo'], $campos_item_pedido[0]['referencia']).' * ';
                echo $campos_item_pedido[0]['discriminacao'];
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço Unitário:</b>
        </td>
        <td>
            <?=$moeda.number_format($campos[0]['valor_entregue'], '2', ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso do PA Kg: </b>
        </td>
        <td>
            <?
                //Busca do Peso do PA ...
                $sql = "SELECT id_produto_acabado, peso_unitario 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                $campos_produto_acabado = bancos::sql($sql);
                $id_produto_acabado     = $campos_produto_acabado[0]['id_produto_acabado'];
            ?>
            <input type='text' name='txt_peso_pa_kg' value="<?=segurancas::number_format($campos_produto_acabado[0]['peso_unitario'], 4, ',');?>" size='10' onkeyup="verifica(this, 'moeda_especial', '4', '1', event); if(this.value == '-') { this.value = ''}; calcular()" class='caixadetexto'>&nbsp;
            <input type='submit' name='cmd_salvar_peso' value='Salvar Peso' title='Salvar Peso' style='color:green' class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Total Kg: </b>
        </td>
        <td>
            <input type='text' name='txt_peso_total' size='10' onkeyup="verifica(this, 'moeda_especial', '3', '1', event); if(this.value == '-') { this.value = ''}; calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Cx. Papelão (Estimado) Kg: </b>
        </td>
        <td>
            <input type='text' name='txt_peso_cx_papelao' size='10' onkeyup="verifica(this, 'moeda_especial', '3', '1', event); if(this.value == '-') { this.value = ''}; calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtde Pçs Estimada(s):
        </td>
        <td>
            <input type='text' name='txt_qtde_pcs_estimadas' size='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_peso_total.focus()" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
<!--Jogo essa caixa aqui em baixo porque o Sql que retorna esse valor, foi feito bem mais abaixo do head
Guardo esse valor em um hidden, para facilitar no Update-->
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
</form>
</body>
</html>