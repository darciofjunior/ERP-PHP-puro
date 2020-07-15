<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/relatorios/atrasados_maior_60_dias/classes/consultar_contas.php', '../../../../../');
?>
<html>
<head>
<title>.:: Rodapé de Itens de Contas à Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function selecionar(valor) {
    var id_conta_receber = ''
//Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox = 0
    elemento = parent.itens.document.form.elements
    for(x = 0; x < elemento.length; x++) {
        if(elemento[x].type == 'checkbox') {
            if(elemento[x].checked == true) {
                id_conta_receber = id_conta_receber + elemento[x].value + ','
                checkbox ++
            }
        }
    }
    id_conta_receber = id_conta_receber.substr(0, id_conta_receber.length - 1)

    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        if(checkbox > 1) {
            alert('SELECIONE SOMENTE UM ITEM !')
            return false
        }else {
            nova_janela('../../../../financeiro/recebimento/detalhes.php?id_conta_receber='+id_conta_receber, 'POP', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')
        }
    }
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td align='center'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.parent.location = 'consultar_contas.php'" class='botao'>
            <input type='button' name='cmd_detalhes' value='Detalhes' title='Detalhes' onclick="return selecionar()" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>