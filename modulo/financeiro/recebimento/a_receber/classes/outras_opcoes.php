<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
?>
<html>
<head>
<title>.:: Outras Op��es ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    if(document.form.opt_item.checked == true) {
        var id_conta_receber = ''
//Serve para verificar quantos checkbox eu tenho selecionado no frame de Baixo -> Itens 
        var checkbox  = 0
        elementos = window.opener.parent.itens.document.form.elements
        for(i = 0; i < elementos.length; i++) {
            //Aqui eu pego os valores de todos os Checkbox com exce��o do Primeiro que n�o nos interessa ...
            if(elementos[i].checked == true && elementos[i].name == 'chkt_conta_receber[]') {
                if(elementos[i].checked == true) {
                    id_conta_receber = id_conta_receber + elementos[i].value + ','
                    checkbox ++
                }
            }
        }
        id_conta_receber = id_conta_receber.substr(0, id_conta_receber.length - 1)

        if (checkbox == 0) {
            window.alert('SELECIONE PELO MENOS UM ITEM PARA A IMPRESS�O DO RELAT�RIO !')
        }else {
            nova_janela('relatorios/relatorio_a_receber.php?id_emp=<?=$id_emp;?>&id_conta_receber='+id_conta_receber, 'RELATORIO', 'F')
        }
        window.close()
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='60%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Outras Op��es 
            <font color='yellow'>
            <?
                if($id_emp != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($id_emp);
                }else {
                    echo 'TODAS EMPRESAS';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='1' title='Imprimir Relat�rio de Conta � Receber' id='opt1' checked>
            <label for='opt1'>Imprimir Relat�rio de Contas � Receber</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avan�ar &gt;&gt;' title='Avan�ar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>