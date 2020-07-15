<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../../');

if(!empty($_POST['cmb_caixa_master'])) {//Se a Caixa Master j� foi adicionada ent�o ...
    //Aqui eu busco o N.� da �ltima caixa master que foi inserida no Packing List ...
    $sql = "SELECT caixa_master_numero 
            FROM `packings_lists_itens` 
            WHERE `id_packing_list` = '$_POST[hdd_packing_list]' 
            AND `caixa_master_numero` > '0' ORDER BY caixa_master_numero DESC LIMIT 1 ";
    $campos = bancos::sql($sql);
    //Se ainda n�o foi inserido nenhuma Caixa, o Sistema j� sabe que ser� a 1� caixa, do contr�rio s� ir� continuar a contagem ...
    $caixa_master_numero = (count($campos) == 0) ? 1 : ($campos[0]['caixa_master_numero'] + 1);

    //Aqui eu incluo uma Caixa Master p/ o N.� de Caixa Secund�rio passado por par�metro ...
    foreach($_POST['chkt_caixa_secundario_numero'] as $caixa_secundario_numero) {
        $sql = "UPDATE `packings_lists_itens` SET `id_produto_insumo_master` = '$_POST[cmb_caixa_master]', `caixa_master_numero` = '$caixa_master_numero' WHERE `id_packing_list` = '$_POST[hdd_packing_list]' AND `caixa_secundario_numero` = '$caixa_secundario_numero' ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        alert('CAIXA MASTER INCLU�DA COM SUCESSO !')
        opener.window.location = opener.location.href
        window.close()
    </Script>
<?
    exit;
}
?>
<html>
<head>
<title>.:: Incluir Caixa Master - Packing List ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Caixa Master ...
    if(!combo('form', 'cmb_caixa_master', '', 'SELECIONE UMA CAIXA MASTER !')) {
        return false
    }
    var elementos = document.form.elements
    if(typeof(elementos['chkt_caixa_secundario_numero[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['chkt_caixa_secundario_numero[]'].length)
    }
    var itens_selecionados = 0
//Aqui eu verifico se foi selecionado pelo menos 1 Item ...
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_caixa_secundario_numero'+i).checked == true) {//Se estiver preenchido ...
            itens_selecionados++
            break
        }
    }
    if(itens_selecionados == 0) {
        alert('SELECIONE UMA CAIXA SECUND�RIA P/ INCLUIR UMA CAIXA MASTER !')
        document.getElementById('chkt_caixa_secundario_numero0').focus()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Caixa Master - Packing List N.� 
            <font color='yellow'>
                <?=$_GET['id_packing_list'];?>
            </font>
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td colspan='2'>
            Caixa(s) Master: 
            <!--Lembrando que as Caixas Masters tamb�m s�o os PI�s cadastrados no Sistema -->
            <select name='cmb_caixa_master' title='Selecione uma Caixa Master' class='combo'>
            <?
                $sql = "SELECT pi.`id_produto_insumo`, CONCAT(ROUND(ei.`qtde`, 0), ' * ', pi.`discriminacao`) AS dados 
                        FROM `produtos_insumos` pi 
                        LEFT JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
                        WHERE pi.`discriminacao` LIKE 'CAIXA%MADEIRA%' 
                        AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr align='center'>
        <td class='linhacabecalho'>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Tudo' class="checkbox">
        </td>
        <td class='linhacabecalho'>
            Caixa(s) Secund�ria(s)
        </td>
    </tr>
    <?
        //Aqui eu listo todas as Caixa Secund�rias que ainda n�o possuem Caixa Master atrav�s do $id_packing_list passado por par�metro ...
        $sql = "SELECT DISTINCT(pli.`caixa_secundario_numero`), pi.`discriminacao` AS discriminacao_caixa_secundario 
                FROM `packings_lists_itens` pli 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pli.`id_produto_insumo_secundario` 
                WHERE pli.`id_packing_list` = '$_GET[id_packing_list]' 
                AND pli.`id_produto_insumo_master` IS NULL ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
    ?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt_tudo', '<?=($i + 1);?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_caixa_secundario_numero[]' id='chkt_caixa_secundario_numero<?=$i;?>' value="<?=$campos[$i]['caixa_secundario_numero'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=($i + 1);?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
            Caixa N.� <?=$campos[$i]['caixa_secundario_numero'].') '.$campos[$i]['discriminacao_caixa_secundario'];?>
        </td>
    </tr>
    <?
        }
    ?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
<!--****************************Controle de Tela*****************************-->
<input type='hidden' name='hdd_packing_list' value='<?=$_GET['id_packing_list'];?>'>
<!--*************************************************************************-->
</form>
</body>
</html>