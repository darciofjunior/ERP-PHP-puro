<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/intermodular.php');
session_start('funcionarios');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../../');

if(!empty($_POST['cmb_caixa_secundario'])) {//Se a Caixa Secund�ria j� foi alterada ent�o ...
    /*Altero somente uma Caixa Secund�ria por Outra mediante a N.� da Caixa da que foi passada por par�metro, 
    do Packing List em espec�fico ...*/
    $sql = "UPDATE `packings_lists_itens` SET `id_produto_insumo_secundario` = '$_POST[cmb_caixa_secundario]', `caixa_master_numero` = '$_POST[cmb_caixa_master_numero]' WHERE `id_packing_list` = '$_POST[hdd_packing_list]' AND `caixa_secundario_numero` = '$_POST[hdd_caixa_secundario_numero]' ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('CAIXA SECUND�RIA ALTERADA COM SUCESSO !')
        opener.window.location = opener.location.href
        window.close()
    </Script>
<?
    exit;
}

//Aqui eu busco alguns dados atrav�s do $id_packing_list_item passado por par�metro ...
$sql = "SELECT `id_packing_list`, `id_produto_insumo_secundario`, 
        `caixa_master_numero`, `caixa_secundario_numero` 
        FROM `packings_lists_itens` 
        WHERE `id_packing_list_item` = '$_GET[id_packing_list_item]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Caixa Secund�ria - Packing List ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Caixa Master N�mero ...
    if(!combo('form', 'cmb_caixa_master_numero', '', 'SELECIONE UM N�MERO PARA CAIXA MASTER !')) {
        return false
    }
//Caixa Secund�ria ...
    if(!combo('form', 'cmb_caixa_secundario', '', 'SELECIONE UMA CAIXA SECUND�RIA !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controle de Tela*****************************-->
<input type='hidden' name='hdd_packing_list' value='<?=$campos[0]['id_packing_list'];?>'>
<input type='hidden' name='hdd_caixa_secundario_numero' value='<?=$campos[0]['caixa_secundario_numero'];?>'>
<!--*************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Caixa Secund�ria - Packing List
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Caixa Master N.�:</b>
        </td>
        <td>
            <?
                //Busco o Maior N�mero de Caixa Master que est� sendo utilizada nessa Packing List ...
                $sql = "SELECT `caixa_master_numero` AS maior_caixa_master_numero 
                        FROM `packings_lists_itens` 
                        WHERE `id_packing_list` = '".$campos[0]['id_packing_list']."' ORDER BY `caixa_master_numero` DESC LIMIT 1 ";
                $campos_packing_list_item       = bancos::sql($sql);
                $maior_caixa_master_numero      = $campos_packing_list_item[0]['maior_caixa_master_numero'];
            ?>
            <select name='cmb_caixa_master_numero' title='Selecione uma Caixa Master' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
            <?
                for($i = 0; $i <= $maior_caixa_master_numero; $i++) {
                    $selected = ($i == $campos[0]['caixa_master_numero']) ? 'selected' : '';
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
            <b>Caixa(s) Secund�ria:</b>
        </td>
        <td>
            <!--Lembrando que as Caixas Secund�rias tamb�m s�o os PI�s cadastrados no Sistema -->
            <select name='cmb_caixa_secundario' title='Selecione uma Caixa Secund�ria' class='combo'>
            <?
                $sql = "SELECT id_produto_insumo, discriminacao 
                        FROM `produtos_insumos` 
                        WHERE `discriminacao` LIKE 'CAIXA%PAPEL%' 
                        AND `ativo` = '1' ORDER BY discriminacao ";
                echo combos::combo($sql, $campos[0]['id_produto_insumo_secundario']);
            ?>
            </select>
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