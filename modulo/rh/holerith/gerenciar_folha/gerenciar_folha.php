<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Gerenciar Folha ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data de Holerith
    if(!combo('form', 'cmb_data_holerith', '', 'SELECIONE UMA DATA DE HOLERITH !')) {
        return false
    }
//Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
        return false
    }
}

/*Nessa função eu verifico se estão preenchidos os campos de Qtde de Horas Trabalhadas e 
Qtde de Dias Trabalhados*/
function controlar_data_holerith() {
    var id_vale_data = document.form.cmb_data_holerith.value
//Verifico dentro desse Iframe se a Data de Holerith é válida ...
    //top.iframe_verificar_data_holerith.document.location = '../../class_data_holerith/verificar_data_holerith.php?id_vale_data='+id_vale_data
	
    top.verificar_data_holerith.document.location = '../../class_data_holerith/verificar_data_holerith.php?id_vale_data='+id_vale_data
}

function incluir_data_holerith() {
    nova_janela('../../class_data_holerith/incluir.php', 'CONSULTAR', '', '', '', '', '280', '800', 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        var data_holerith   = document.form.cmb_data_holerith
        var data            = data_holerith[data_holerith.selectedIndex].text
        data                = data.substr(6, 4) + '-' + data.substr(3, 2) + '-' + data.substr(0, 2)
        nova_janela('../../class_data_holerith/alterar.php?data='+data, 'CONSULTAR', '', '', '', '', '280', '800', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body onload='document.form.cmb_data_holerith.focus()'>
<form name='form' method='post' action='itens/itens.php' onsubmit='return validar()'>
<input type='hidden' name='passo' onclick='atualizar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerenciar Folha
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Holerith:</b>
        </td>
        <td>
            <select name='cmb_data_holerith' title='Selecione a Data de Holerith' class='combo'>
            <?
                //Aqui eu pego a próxima Data de Holerith posterior à Data Atual ...
                $sql = "SELECT id_vale_data 
                        FROM `vales_datas` 
                        WHERE `data` >= '".date('Y-m-d')."' LIMIT 1 ";
                $campos_vale_data = bancos::sql($sql);
                
                //Busco todos os Períodos de Data de Holerith cadastrados no Sistema ...
                $sql = "SELECT `id_vale_data`, DATE_FORMAT(`data`, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        ORDER BY `data` ";
                echo combos::combo($sql, $campos_vale_data[0]['id_vale_data']);
            ?>
            </select>
            &nbsp;&nbsp; <img src = "../../../../imagem/menu/incluir.png" border='0' title="Incluir Data de Holerith" alt="Incluir Data de Holerith" onClick="incluir_data_holerith()">
            &nbsp;&nbsp; <img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Data de Holerith" alt="Alterar Data de Holerith" onClick="alterar_data_holerith()">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.cmb_data_holerith.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>