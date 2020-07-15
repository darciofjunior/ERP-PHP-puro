<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/utilitarios/envelope/envelopes.php', '../../../../../');

//Busca de alguns dados do Representante passado por parâmetro ...
$sql = "SELECT nome_representante, endereco, cep, cidade, uf, contato 
        FROM `representantes` 
        WHERE `id_representante` = '$_GET[id_representante]' 
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
    if(document.form.cmb_departamento.value == '') {
        alert('SELECIONE O DEPARTAMENTO !')
        document.form.cmb_departamento.focus()
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
            Representante:
        </td>
        <td>
            <?=$campos[0]['nome_representante'];?>
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
                echo combos::combo($sql, 'VENDAS');
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Contato:
        </td>
        <td>
            <input type='text' name='txt_contato' value='<?=$campos[0]['contato'];?>' title='Digite o Contato' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='return perguntar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_representante' value='<?=$_GET['id_representante'];?>'>
</form>
</body>
</html>