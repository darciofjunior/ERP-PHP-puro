<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../../');

if(!empty($_POST['cmb_caixa_master'])) {//Se a Caixa Master já foi alterada então ...
    /*Altero somente uma Caixa Secundária por Outra mediante a N.º da Caixa da que foi passada por parâmetro, 
    do Packing List em específico ...*/
    $sql = "UPDATE `packings_lists_itens` SET `id_produto_insumo_master` = '$_POST[cmb_caixa_master]' WHERE `id_packing_list` = '$_POST[hdd_packing_list]' AND `caixa_master_numero` = '$_POST[hdd_caixa_master_numero]' ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('CAIXA MASTER ALTERADA COM SUCESSO !')
        opener.window.location = opener.location.href
        window.close()
    </Script>
<?
    exit;
}

//Aqui eu busco a Caixa Master e o Número da Caixa Master através do $id_packing_list_item passado por parâmetro ...
$sql = "SELECT `id_packing_list`, `id_produto_insumo_master`, `caixa_master_numero` 
        FROM `packings_lists_itens` 
        WHERE `id_packing_list_item` = '$_GET[id_packing_list_item]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Caixa Master - Packing List ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Caixa Secundária ...
    if(!combo('form', 'cmb_caixa_master', '', 'SELECIONE UMA CAIXA MASTER !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controle de Tela*****************************-->
<input type='hidden' name='hdd_packing_list' value='<?=$campos[0]['id_packing_list'];?>'>
<input type='hidden' name='hdd_caixa_master_numero' value='<?=$campos[0]['caixa_master_numero'];?>'>
<!--*************************************************************************-->
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Caixa Master - Packing List
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Caixa(s) Master:</b>
        </td>
        <td>
            <!--Lembrando que as Caixas Masters também são os PI´s cadastrados no Sistema -->
            <select name='cmb_caixa_master' title='Selecione uma Caixa Master' class='combo'>
            <?
                $sql = "SELECT pi.`id_produto_insumo`, CONCAT(ROUND(ei.`qtde`, 0), ' * ', pi.`discriminacao`) AS dados 
                        FROM `produtos_insumos` pi 
                        LEFT JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
                        WHERE pi.`discriminacao` LIKE 'CAIXA%MADEIRA%' 
                        AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
                echo combos::combo($sql, $campos[0]['id_produto_insumo_master']);
            ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>