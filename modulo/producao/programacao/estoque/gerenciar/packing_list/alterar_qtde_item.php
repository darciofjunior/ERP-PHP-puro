<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../../');

if(!empty($_POST['txt_qtde_packing_list'])) {//Se a Caixa de Papelão já foi alterada então ...
    //Altero somente uma Caixa de Papelão por Outra da que foi passada por parâmetro do Packing List em específico ...
    $sql = "UPDATE `packings_lists_itens` SET `caixa_secundario_numero` = '$_POST[cmb_caixa_secundario_numero]', `qtde` = '$_POST[txt_qtde_packing_list]' WHERE `id_packing_list_item` = '$_POST[hdd_packing_list_item]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('QUANTIDADE DE ITEM ALTERADA COM SUCESSO !')
        if(typeof(top.parent) == 'object') top.parent.window.location = top.parent.location.href
        window.location = 'relatorio.php?id_packing_list=<?=$_POST['hdd_packing_list'];?>'
    </Script>
<?
    exit;
}

//Aqui eu busco mais dados do item através do $id_packing_list_item passado por parâmetro ...
$sql = "SELECT pli.`id_packing_list`, pli.`id_produto_acabado`, pli.`id_produto_insumo_secundario`, 
        pli.`caixa_secundario_numero`, pli.`qtde`, ROUND(pvi.`qtde`, 0) AS qtde_pedida 
        FROM `packings_lists_itens` pli 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = pli.`id_pedido_venda_item` 
        WHERE pli.`id_packing_list_item` = '$_GET[id_packing_list_item]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$qtde_pedida    = $campos[0]['qtde_pedida'];

//Aqui eu verifico a Qtde Total desse Item em outras caixas com exceção dessa Caixa atual nesse Packing List ...
$sql = "SELECT SUM(`qtde`) AS qtde_total_exceto_caixa_atual 
        FROM `packings_lists_itens` 
        WHERE `id_packing_list` = '".$campos[0]['id_packing_list']."' 
        AND `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' 
        AND `id_produto_insumo_secundario` <> '".$campos[0]['id_produto_insumo_secundario']."' ";
$campos_qtde_total_exceto_caixa_atual   = bancos::sql($sql);
$qtde_total_exceto_caixa_atual          = $campos_qtde_total_exceto_caixa_atual[0]['qtde_total_exceto_caixa_atual'];
?>
<html>
<head>
<title>.:: Alterar Quantidade do Item - Packing List ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Caixa Secundária Número ...
    if(!combo('form', 'cmb_caixa_secundario_numero', '', 'SELECIONE UM NÚMERO PARA CAIXA SECUNDÁRIA !')) {
        return false
    }
//Qtde Packing List ...
    if(!texto('form', 'txt_qtde_packing_list', '1', '0123456789', 'QUANTIDADE DO ITEM', '1')) {
        return false
    }
//Aqui o Sistema verifica até quanto que o usuário ainda pode acrescentar desse PA ...
    var qtde_pedida                     = eval('<?=$qtde_pedida;?>')
    var qtde_total_exceto_caixa_atual   = eval('<?=$qtde_total_exceto_caixa_atual;?>')
    var qtde_disponivel_caixa_atual     = qtde_pedida - qtde_total_exceto_caixa_atual
    var qtde_packing_list               = eval(document.form.txt_qtde_packing_list.value)
//Verifico se a Qtde Digitada pelo usuário, ultrapassou a Qtde Disponível ...
    if(qtde_packing_list > qtde_disponivel_caixa_atual) {
        alert('QUANTIDADE DE ITEM INVÁLIDA !!!\n\nA QUANTIDADE MÁXIMA PARA ESTE ITEM É DE ATÉ '+qtde_disponivel_caixa_atual+' PÇS, JÁ DESCONTANDO DE OUTRAS CAIXAS !')
        document.form.txt_qtde_packing_list.focus()
        document.form.txt_qtde_packing_list.select()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_qtde_packing_list.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--***************************Controles de Tela*****************************-->
<input type='hidden' name='hdd_packing_list_item' value='<?=$_GET[id_packing_list_item];?>'>
<input type='hidden' name='hdd_packing_list' value='<?=$campos[0]['id_packing_list'];?>'>
<!--*************************************************************************-->
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Quantidade do Item - Packing List
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <font color='yellow'>
                <b>Produto:</b>
            </font>
            <?=intermodular::pa_discriminacao($campos[0]['id_produto_acabado'], 0, 0, 0, 0, 1);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Caixa Secundária N.º:</b>
        </td>
        <td>
            <?
                //Busco o Maior Número de Caixa Secundária que está sendo utilizada nessa Packing List ...
                $sql = "SELECT `caixa_secundario_numero` AS maior_caixa_secundario_numero 
                        FROM `packings_lists_itens` 
                        WHERE `id_packing_list` = '".$campos[0]['id_packing_list']."' ORDER BY `caixa_secundario_numero` DESC LIMIT 1 ";
                $campos_packing_list_item       = bancos::sql($sql);
                $maior_caixa_secundario_numero  = $campos_packing_list_item[0]['maior_caixa_secundario_numero'];
            ?>
            <select name='cmb_caixa_secundario_numero' title='Selecione uma Caixa Secundária' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
            <?
                for($i = 1; $i <= $maior_caixa_secundario_numero; $i++) {
                    $selected = ($i == $campos[0]['caixa_secundario_numero']) ? 'selected' : '';
            ?>
                <option value='<?=$i;?>' <?=$selected;?>><?=$i;?></option>
            <?
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde Packing List:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_packing_list' value='<?=$campos[0]['qtde'];?>' title='Digite a Quantidade do Packing List' maxlength='6' size='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event); if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'relatorio.php?id_packing_list=<?=$campos[0]['id_packing_list'];?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_qtde_packing_list.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>