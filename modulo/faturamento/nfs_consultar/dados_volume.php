<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../');

//Assim que submeter, o sistema automaticamente chama a função de fazer Download ...
if(!empty($_POST['txt_quantidade'])) {
?>
<Script Language = 'Javascript' src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>	
    document.write('ASSIM QUE FIZER O DOWNLOAD, FECHAR ESTA TELA ...')
    nova_janela('../nfs_consultar/gerar_txt_nfe.php?id_nf=<?=$_POST['id_nf'];?>&id_nf_outra=<?=$_POST['id_nf_outra'];?>&txt_quantidade=<?=$_POST['txt_quantidade'];?>&txt_especie=<?=$_POST['txt_especie'];?>&txt_peso_bruto=<?=$_POST['txt_peso_bruto'];?>&txt_peso_liquido=<?=$_POST['txt_peso_liquido'];?>', 'POP', '', '', '', '', 180, 700, 'c', 'c', '', '', 's', 's', '', '', '')
</Script>
<?
    exit;
}
?>
<html>
<head>
<title>.:: Dados de Volume ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'Javascript' src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Quantidade ...
    if(!texto('form', 'txt_quantidade', '1', '0123456789', 'QUANTIDADE', '1')) {
        return false
    }
//Espécie ...
    if(!texto('form', 'txt_especie', '1', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZãõÃÕáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛçÇ-()_.,; ', 'ESPÉCIE', '1')) {
        return false
    }
//Peso Bruto ...
    if(!texto('form', 'txt_peso_bruto', '1', '0123456789,.', 'PESO BRUTO', '2')) {
        return false
    }
//Peso Líquido ...
    if(!texto('form', 'txt_peso_liquido', '1', '0123456789,.', 'PESO LÍQUIDO', '2')) {
        return false
    }
//Controle para não haver erro de Digitação com relação aos Pesos ...
    var peso_bruto = eval(strtofloat(document.form.txt_peso_bruto.value))
    var peso_liquido = eval(strtofloat(document.form.txt_peso_liquido.value))

    if(peso_bruto <= peso_liquido) {
        alert('PESO BRUTO INVÁLIDO !!! PESO BRUTO MENOR OU IGUAL AO PESO LÍQUIDO !')
        document.form.txt_peso_liquido.focus()
        document.form.txt_peso_liquido.select()
        return false
    }
    limpeza_moeda('form', 'txt_peso_bruto, txt_peso_liquido, ')
}
</Script>
</head>
<body onload='document.form.txt_quantidade.focus()' topmargin='20'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Controle de Tela-->
<input type='hidden' name='seguranca' value='<?=$_GET['seguranca'];?>'>
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf'];?>'>
<input type='hidden' name='id_nf_outra' value='<?=$_GET['id_nf_outra'];?>'>
<!--******************************************************-->
<table width='90%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Preencha os Dados de Volume antes de Gerar a NFe
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade:</b>
        </td>
        <td>
            <input type='text' name='txt_quantidade' title='Digite a Quantidade' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="12" maxlength="10" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Espécie:</b>
        </td>
        <td>
            <input type='text' name='txt_especie' title='Digite a Espécie' size="22" maxlength="20" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Bruto:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_bruto' title='Digite o Peso Bruto' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" size="18" maxlength="16" class='caixadetexto'> Kgs
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Líquido:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_liquido' title='Digite o Peso Líquido' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" size="18" maxlength="16" class='caixadetexto'> Kgs
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_quantidade.focus()" class='botao'>
            <input type='submit' name='cmd_gerar_nfe' value='Gerar NFe' title='Gerar NFe' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>