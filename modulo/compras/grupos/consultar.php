<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
?>
<html>
<head>
<title>.:: Consultar Grupo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/datatable.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
$(document).ready(function() {
    var selected = [];
    
    $('#example').dataTable( {
        'processing':       true,
        'serverSide':       true,
        'scrollCollapse':   true,
        'scrollY':          '65%',
        'order':            [[0, 'DESC']],
        'ajax': {
            'url':      'consultar_grupos.php',
            'type':     'post',
            'data': {
                'txt_consultar': '<?=$txt_consultar;?>'
            }
        }
    });
    
    $('#example tbody').on('click', 'tr', function () {
        var id = this.id;
        var index = $.inArray(id, selected);
 
        if(index === -1) {
            selected.push(id);
        }else {
            selected.splice(index, 1);
        }
 
        $(this).toggleClass('selected');
    });
});
</Script>
</head>
<body class='dt-example'>
<div class='container'>
<section>
<table id='example' width='100%' class='display'>
    <thead>
        <tr class='linhacabecalho' align='center'>
            <th colspan='5'>
                Consultar Grupo(s)
            </th>
        </tr>
        <tr class='linhadestaque'>
            <th>
                Referência
            </th>
            <th>
                Grupo
            </th>
            <th>
                Conta Caixa
            </th>
            <th>
                Tipo de Custo
            </th>
            <th>
                Observação
            </th>
        </tr>
    </thead>
    <tfoot>
        <tr class='linhacabecalho' align='center'>
            <td colspan='5'>
                <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
            </td>
        </tr>
    </tfoot>
</table>
</section>
</div>
</body>
</html>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Grupo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho'  align='center'>
        <td colspan='2'>
            Consultar Grupo(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Grupos por: Grupo' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Grupo</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os Grupos' onclick='limpar()' class='checkbox' id='label2'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>