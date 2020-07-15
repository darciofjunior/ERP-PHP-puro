<?
require('../../../lib/segurancas.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

//Atrav�s do id_produto_acabado, eu busco qual � o id_produto_insumo
//Aqui eu verifico se o PA � do Tipo PRAC, se este for, ent�o essa op��o Consultar Compras, estar� habilitada
$sql = "SELECT id_produto_insumo 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' 
        AND `id_produto_insumo` > '0' 
        AND `ativo` = '1' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 0) {//Se o PI n�o for PRAC, ent�o travo alguns Options ...
    $disabled = 'disabled';
}else {//Pode ver os detalhes normalmente
    $disabled = '';
}
?>
<html>
<head>
<title>.:: Detalhes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    window.close()
    if(document.form.opt_opcao[0].checked == true) {        
        nova_janela('manipular_estoque/consultar.php?passo=1&opt_opcao=2&id_produto_acabado=<?=$id_produto_acabado;?>&chkt_mostrar_componentes=1&pop_up=1&cmb_opcao_entrada='+document.form.cmb_opcao_entrada.value, 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    }else if(document.form.opt_opcao[1].checked == true) {
//Esse par�metro veio_vendas = 1, significa que essa Tela foi acessada do M�dulo de Vendas do Menu de Estoque, 
//Se esse par�metro "ver_todas_compras" = 1, significa que o Sistema tem que mostrar todas as Compras que j� foram realizadas do determinado PA ...
        nova_janela('../../compras/estoque_i_c/detalhes_compras.php?id_produto_acabado=<?=$id_produto_acabado;?>&veio_vendas=1&ver_todas_compras=1', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    }else if(document.form.opt_opcao[2].checked == true) {
        nova_janela('../../classes/produtos_acabados/rel_saldo_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'OUTRAS', '', '', '', '', '500', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    }else if(document.form.opt_opcao[3].checked == true) {
        nova_janela('../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'OUTRAS', '', '', '', '', '500', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    }else if(document.form.opt_opcao[4].checked == true) {
        nova_janela('../../compras/estoque_i_c/nivel_estoque/pendencias_item.php?id_produto_insumo=<?=$campos[0]['id_produto_insumo'];?>', 'OUTRAS', '', '', '', '', '500', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    }else if(document.form.opt_opcao[5].checked == true) {
        //Passo par�metros p/ a Tela P�s-Filtro como se o usu�rio tivesse feito um filtro de consulta por refer�ncia ...
        nova_janela('../../producao/ops/alterar.php?passo=1&id_produto_acabado=<?=$_GET['id_produto_acabado'];?>&pop_up=1', 'OUTRAS', '', '', '', '', '500', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {
        alert('SELECIONE UMA OP��O !')
        return false
    }
}

function controle_objetos() {
    if(document.form.opt_opcao[0].checked == true) {
        document.form.cmb_opcao_entrada.disabled = false
    }else if(document.form.opt_opcao[1].checked == true) {
        document.form.cmb_opcao_entrada.value = ''
        document.form.cmb_opcao_entrada.disabled = true
    }else if(document.form.opt_opcao[2].checked == true) {
        document.form.cmb_opcao_entrada.value = ''
        document.form.cmb_opcao_entrada.disabled = true
    }else if(document.form.opt_opcao[3].checked == true) {
        document.form.cmb_opcao_entrada.value = ''
        document.form.cmb_opcao_entrada.disabled = true
    }else if(document.form.opt_opcao[4].checked == true) {
        document.form.cmb_opcao_entrada.value = ''
        document.form.cmb_opcao_entrada.disabled = true
    }
}
</Script>
</head>
<body>
<form name="form" method="post">
<table border="0" width="90%" align="center" cellspacing ='1' cellpadding='1'>
    <tr class='linhacabecalho'>
        <td colspan="2" align='center'>
            Detalhes
        </td>
    </tr>
    <tr class="linhadestaque">
        <td>
            <font size="-1">
                <font color='yellow'>Produto: </font><?=intermodular::pa_discriminacao($id_produto_acabado);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Relat�rio de Movimenta��o do Estoque" onclick="controle_objetos()" id='label'>
            <label for='label'>Relat�rio de Movimenta��o do Estoque / </label>
            &nbsp;
            <select name="cmb_opcao_entrada" title="Op��o de Entrada" class="combo" disabled>
                <option value="" style="color:red">SELECIONE</option>
                <option value="B">BAIXA DO ESTOQUE</option>
                <option value="E">ENTRADA DE PRODU��O</option>
                <option value="S">ESTORNO DE BAIXA</option>
                <option value="I">INVENT�RIO</option>
                <option value="M">MANIPULA��O DO ESTOQUE</option>
                <option value="O">OC</option>
                <option value="P">OP NOVA</option>
                <option value="R">REFUGO</option>
                <option value="U">USO P/ F�BRICA</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar NF de Compras" onclick="controle_objetos()" id='label2' <?=$disabled;?>>
            <label for='label2'>Consultar NF de Compras</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="3" title="Rel�torio de Movimenta��o de Estoque (Completo)" onclick="controle_objetos()" id='label3'>
            <label for='label3'>Rel�torio de Movimenta��o de Estoque (Completo)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="4" title="Consultar Estoque" onclick="controle_objetos()" id='label4'>
            <label for='label4'>Consultar Estoque</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="5" title="Pend�ncia de Pedidos (Compra Produ��o)" onclick="controle_objetos()" id='label5' <?=$disabled;?>>
            <label for='label5'>Pend�ncia de Pedidos (Compra Produ��o)</label>
        </td>
    </tr>
    <?
        //Essa op��o de OP s� aparece para os usu�rios abaixo ...
        //Rivaldo 27, Rodrigo T�cnico 54, Roberto 62, D�rcio 98, Bispo 125, Netto 147 ...
        if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 125 || $_SESSION['id_funcionario'] == 147) {
    ?>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="6" title="OP(s) Emitidas" onclick="controle_objetos()" id='label6'>
            <label for='label6'>OP(s) Emitidas</label>
        </td>
    </tr>
    <?
        }
    ?>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type='button' name='cmd_avan�ar' value='&gt;&gt; Avan�ar &gt;&gt;' title='Avan�ar' onclick="avancar()" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class="botao">
        </td>
    </tr>
</table>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
</form>
</body>
</html>