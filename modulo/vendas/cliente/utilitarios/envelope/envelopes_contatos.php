<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/utilitarios/envelope/envelopes.php', '../../../../../');

//Busca de alguns dados do Cliente passado por par�metro ...
$sql = "SELECT `nomefantasia`, `razaosocial` 
	FROM `clientes` 
	WHERE `id_cliente` = '$_GET[id_cliente]' 
	AND `ativo` = '1' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Imprimir Envelope(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../../css/layout.css'>
<Script Language = 'Javascript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Departamento
    if(document.form.cmb_departamento.value == '') {
        alert('SELECIONE O DEPARTAMENTO !')
        document.form.cmb_departamento.focus()
        return false
    }
//Contato
    if(document.form.txt_contato.value == '') {
        alert('DIGITE O CONTATO !')
        document.form.txt_contato.focus()
        return false
    }
}

function perguntar() {
    if(!redefinir('document.form', 'REDEFINIR OS CAMPOS ')) {
        return false
    }
    document.form.cmb_departamento.focus()
}
</script>
</head>
<body onload='document.form.cmb_departamento.focus()'>
<form name='form' method='post' action='relatorio.php' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Imprimir Envelope(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente:
        </td>
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Depto.:
        </td>
        <td>
            <select name='cmb_departamento' title='Selecione o Departamento' class='combo'>
            <?
                $sql = "SELECT departamento, departamento 
                        FROM `departamentos` 
                        WHERE `ativo` = '1' ORDER BY departamento ";
                echo combos::combo($sql, 'COMPRAS');
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Contato:</b>
        </td>
        <td>
            <input type='text' name='txt_contato' title='Digite o Contato' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='return perguntar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_avancar' value='&gt;&gt; Avan�ar &gt;&gt;' title='Avan�ar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_cliente' value='<?=$_GET['id_cliente'];?>'>
</form>
</body>
</html>