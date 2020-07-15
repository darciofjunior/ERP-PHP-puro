<?
require('../../../../lib/segurancas.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');
?>
<html>
<head>
<title>.:: Outras Opções ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post'>
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Outras Opções
        </td>
    </tr>
<?
//Aqui eu busco qualé o id_cliente do Pedido ...
    $sql = "SELECT id_cliente 
            FROM `pedidos_vendas` 
            WHERE id_pedido_venda = '$_GET[id_pedido_venda]' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    $id_cliente     = $campos_cliente[0]['id_cliente'];
?>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Certificado de Qualidade' id='label1' checked>
            <label for='label1'>Certificado de Qualidade</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Incluir Atendimento Diário' id='label2'>
            <label for='label2'>Incluir Atendimento Diário</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
<?
    /******************************************************************************************/
    //O botão abaixo só aparecerá se existir pelo menos 1 item de Pedido em Aberto, status = '0' ...
    $sql = "SELECT `id_orcamento_venda_item` 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda` = '$_GET[id_pedido_venda]' 
            AND `status` = '0' LIMIT 1 ";
    $campos_pedido_venda_item = bancos::sql($sql);
    if(count($campos_pedido_venda_item) == 1) {//Encontrou pelo menos 1 item ...
?>
    <tr align='center'>
        <td colspan='2'>
            <br/>
            <input type='button' name='cmd_atualizar_itens_pedido_data_fora_validade' value='Atualizar Itens de Pedido c/ Data Fora de Validade' title='Atualizar Itens de Pedido c/ Data Fora de Validade' onclick="window.location = 'atualizar_itens_pedido_data_fora_validade.php?id_pedido_venda=<?=$id_pedido_venda;?>'" style='color:black' class='caixadetexto'>
        </td>
    </tr>
<?
    }
    /******************************************************************************************/
?>
</table>
</form>
</body>
<Script Language = 'JavaScript'>
function avancar() {
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    if(document.form.opt_opcao[0].checked == true) {//Certificado de Qualidade
        window.location = 'certificado_qualidade.php?id_pedido_venda=<?=$id_pedido_venda;?>'
    }else if(document.form.opt_opcao[1].checked == true) {//Incluir Atendimento Diario
        window.location = '../../atendimento_diario/incluir.php?id_pedido_venda=<?=$id_pedido_venda;?>'
    }else {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}
</Script>
</html>