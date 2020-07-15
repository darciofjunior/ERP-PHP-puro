<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/comissoes/margem_lucro_nova/comissao.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>NOVA COMISS�O MARGEM DE LUCRO ALTERADA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>NOVA COMISS�O MARGEM DE LUCRO J� EXISTENTE.</font>";

if(!empty($_POST['txt_margem_lucro'])) {
    $vetor_calcular_comissao = vendas::calcular_comissoes($_POST['txt_base_comis_dentro_sp']);

    $sql = "SELECT `id_nova_comissao_margem_lucro` 
            FROM `novas_comissoes_margens_lucros` 
            WHERE `margem_lucro` = '$_POST[txt_margem_lucro]' 
            AND  `base_comis_dentro_sp` = '$_POST[txt_base_comis_dentro_sp]' 
            AND `comis_vend_fora_sp` = '$vetor_calcular_comissao[comis_vend_fora_sp]' 
            AND `comis_vend_interior_sp` = '$vetor_calcular_comissao[comis_vend_interior_sp]' 
            AND `comis_autonomo` = '$vetor_calcular_comissao[comis_autonomo]' 
            AND  `comis_vend_sup_interno` = '$vetor_calcular_comissao[comis_vend_sup_interno]' 
            AND  `comis_export` = '$vetor_calcular_comissao[comis_export]' 
            AND `comis_sup_outras_ufs` = '$vetor_calcular_comissao[comis_sup_outras_ufs]' 
            AND `comis_sup_autonomo` = '$vetor_calcular_comissao[comis_sup_autonomo]' 
            AND `id_nova_comissao_margem_lucro` <> '$_POST[id_nova_comissao_margem_lucro]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Nova Comiss�o Margem de Lucro n�o existe
        $sql = "UPDATE `novas_comissoes_margens_lucros` SET `margem_lucro` = '$_POST[txt_margem_lucro]', `base_comis_dentro_sp` = '$_POST[txt_base_comis_dentro_sp]', `comis_vend_fora_sp` = '$vetor_calcular_comissao[comis_vend_fora_sp]', `comis_vend_interior_sp` = '$vetor_calcular_comissao[comis_vend_interior_sp]', `comis_autonomo` = '$vetor_calcular_comissao[comis_autonomo]', `comis_vend_sup_interno` = '$vetor_calcular_comissao[comis_vend_sup_interno]', `comis_export` = '$vetor_calcular_comissao[comis_export]', `comis_sup_outras_ufs` = '$vetor_calcular_comissao[comis_sup_outras_ufs]', `comis_sup_autonomo` = '$vetor_calcular_comissao[comis_sup_autonomo]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_nova_comissao_margem_lucro` = '$_POST[id_nova_comissao_margem_lucro]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Nova Comiss�o Margem de Lucro existente
        $valor = 2;
    }
}

$id_nova_comissao_margem_lucro = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nova_comissao_margem_lucro'] : $_GET['id_nova_comissao_margem_lucro'];

//Busca dados da Nova Comiss�o Margem de Lucro passada por par�metro ...
$sql = "SELECT * 
        FROM `novas_comissoes_margens_lucros` 
        WHERE `id_nova_comissao_margem_lucro` = '$id_nova_comissao_margem_lucro' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Nova Comiss�o Margem de Lucro ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../js/ajax.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Margem de Lucro ...
    if(!texto('form', 'txt_margem_lucro', '3', '1234567890,', 'MARGEM DE LUCRO', '1')) {
        return false
    }
//Base Comiss�o Vendedor dentro de SP ...
    if(!texto('form', 'txt_base_comis_dentro_sp', '3', '1234567890,', 'BASE COMISS�O VENDEDOR DENTRO DE SP', '1')) {
        return false
    }
//Esta Base Comiss�o Vendedor dentro de SP nunca pode ser igual a Zero ...
    var base_comis_dentro_sp = strtofloat(document.form.txt_base_comis_dentro_sp.value)
    if(base_comis_dentro_sp == 0) {
        alert('BASE COMISS�O VENDEDOR DENTRO DE SP INV�LIDA !\n\nESTA N�O PODE SER IGUAL A ZERO !!!')
        document.form.txt_base_comis_dentro_sp.focus()
        document.form.txt_base_comis_dentro_sp.select()
        return false
    }
    return limpeza_moeda('form', 'txt_margem_lucro, txt_base_comis_dentro_sp, ')
}

function calcular() {
    ajax('calcular_comissoes.php?base_comissao_vendedor_sp='+strtofloat(document.form.txt_base_comis_dentro_sp.value), 'div_calcular_comissoes')
}
</Script>
</head>
<body onload='calcular();document.form.txt_margem_lucro.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_nova_comissao_margem_lucro' value='<?=$id_nova_comissao_margem_lucro;?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Nova Comiss�o Margem de Lucro
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='40%'>
            <b>Margem de Lucro ></b>
        </td>
        <td width='60%'>
            <input type='text' name='txt_margem_lucro' value='<?=number_format($campos[0]['margem_lucro'], 2, ',', '.');?>' title='Digite a Margem de Lucro' size='8' maxlength='9' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Base Comiss&atilde;o dentro de SP:
        </td>
        <td>
            <input type='text' name='txt_base_comis_dentro_sp' value='<?=number_format($campos[0]['base_comis_dentro_sp'], 2, ',', '.');?>' title='Digite a Base Comiss&atilde;o dentro de SP' size='8' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'comissao.php'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_margem_lucro.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
    <tr>
        <td>
            <div name='div_calcular_comissoes' id='div_calcular_comissoes' style='height:25px; width:500px; font: 16px verdana'></div>
        </td>
    </tr>
</table>
</form>
</body>
</html>