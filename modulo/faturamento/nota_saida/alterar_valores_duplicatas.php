<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../../lib/intermodular.php');

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolução 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolução ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

if(!empty($_POST['txt_valor1'])) {
    $campos = " `valor1` = '$_POST[txt_valor1]', ";//Sempre teremos pelo menos 1 vencimento ...
    if(isset($_POST[txt_valor2])) $campos.= " `valor2` = '$_POST[txt_valor2]', ";
    if(isset($_POST[txt_valor3])) $campos.= " `valor3` = '$_POST[txt_valor3]', ";
    if(isset($_POST[txt_valor4])) $campos.= " `valor4` = '$_POST[txt_valor4]', ";
    //Retiro a última vírgula p/ não dar erro de síntaxe ...
    $campos = substr($campos, 0, strlen($campos) - 2);
/*********************************Controle com os Checkbox*********************************/
    $sql = "UPDATE `nfs` SET $campos WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('VALOR(ES) DE DUPLICATA(S) ATUALIZADO(S) COM SUCESSO !')
        opener.parent.location = opener.parent.location.href
        window.close()
    </Script>
<?
}

//Aqui eu trago dados da "id_nf" passado por parâmetro ...
$sql = "SELECT c.`id_pais`, nfs.`valor1`, nfs.`vencimento1`, nfs.`valor2`, nfs.`vencimento2`, nfs.`valor3`, nfs.`vencimento3`, 
        nfs.`valor4`, nfs.`vencimento4` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos     = bancos::sql($sql);

//Verifica qual é o País do Cliente para poder imprimir os símbolos corretos de R$ ...
$tipo_moeda = ($campos[0]['id_pais'] != 31) ? 'U$' : 'R$';

//Busco alguns dados do $id_nf passado por parâmetro para poder fazer algumas seguranças mais abaixo ...
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');
?>
<html>
<head>
<title>.:: ALTERAR VALORES DE DUPLICATAS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Vencimento 1 ...
    if(!texto('form', 'txt_valor1', '1', '0123456789,.', 'VALOR 1', '2')) {
        return false
    }
//Nunca o Valor 1 pode ser igual à Zero ...
    if(document.form.txt_valor1.value == '0,00') {
        alert('VALOR 1 INVÁLIDO !!!\n\nESTE NÃO PODE SER IGUAL À ZERO !')
        document.form.txt_valor1.focus()
        document.form.txt_valor1.select()
        return false
    }
    var campos = 'txt_valor1, '//Sempre teremos pelo menos 1 vencimento ...
//Vencimento 2 ...
    if(typeof(document.form.txt_valor2) == 'object') {
        if(!texto('form', 'txt_valor2', '1', '0123456789,.', 'VALOR 2', '2')) {
            return false
        }
        //Se o Valor 2 existe, então esse não pode ser igual à Zero ...
        if(document.form.txt_valor2.value == '0,00') {
            alert('VALOR 2 INVÁLIDO !!!\n\nESTE NÃO PODE SER IGUAL À ZERO !')
            document.form.txt_valor2.focus()
            document.form.txt_valor2.select()
            return false
        }
        campos+= 'txt_valor2, '
    }
//Vencimento 3 ...
    if(typeof(document.form.txt_valor3) == 'object') {
        if(!texto('form', 'txt_valor3', '1', '0123456789,.', 'VALOR 3', '2')) {
            return false
        }
        //Se o Valor 3 existe, então esse não pode ser igual à Zero ...
        if(document.form.txt_valor3.value == '0,00') {
            alert('VALOR 3 INVÁLIDO !!!\n\nESTE NÃO PODE SER IGUAL À ZERO !')
            document.form.txt_valor3.focus()
            document.form.txt_valor3.select()
            return false
        }
        campos+= 'txt_valor3, '
    }
//Vencimento 4 ...
    if(typeof(document.form.txt_valor4) == 'object') {
        if(!texto('form', 'txt_valor4', '1', '0123456789,.', 'VALOR 4', '2')) {
            return false
        }
        //Se o Valor 4 existe, então esse não pode ser igual à Zero ...
        if(document.form.txt_valor4.value == '0,00') {
            alert('VALOR 4 INVÁLIDO !!!\n\nESTE NÃO PODE SER IGUAL À ZERO !')
            document.form.txt_valor4.focus()
            document.form.txt_valor4.select()
            return false
        }
        campos+= 'txt_valor4, '
    }
//Nessa parte eu verifico se o Total que foi digitado nas Duplicatas bate com o valor Total da Nota ...
    var valor_total_nota        = eval('<?=$calculo_total_impostos['valor_total_nota'];?>')
    var valor_total_duplicatas  = eval(strtofloat(document.form.txt_valor_total_duplicatas.value))
    
    if(valor_total_nota != valor_total_duplicatas) {
        alert('O VALOR TOTAL DA(S) DUPLICATA(S) É DIFERENTE DO VALOR TOTAL DA NOTA !')
        document.form.txt_valor1.focus()
        document.form.txt_valor1.select()
        return false
    }
//Tratamento com os campos p/ poder gravar no Banco de Dados ...
    limpeza_moeda('form', campos)
}

function calcular_valor_total_duplicatas() {
//Vencimento 1 ...
    var valor1 = (document.form.txt_valor1.value != '') ? eval(strtofloat(document.form.txt_valor1.value)) : 0
//Vencimento 2 ...
    if(typeof(document.form.txt_valor2) == 'object') {
        var valor2 = (document.form.txt_valor2.value != '') ? eval(strtofloat(document.form.txt_valor2.value)) : 0
    }else {
        var valor2 = 0
    }
//Vencimento 3 ...
    if(typeof(document.form.txt_valor3) == 'object') {
        var valor3 = (document.form.txt_valor3.value != '') ? eval(strtofloat(document.form.txt_valor3.value)) : 0
    }else {
        var valor3 = 0
    }
//Vencimento 4 ...
    if(typeof(document.form.txt_valor4) == 'object') {
        var valor4 = (document.form.txt_valor3.value != '') ? eval(strtofloat(document.form.txt_valor3.value)) : 0
    }else {
        var valor4 = 0
    }
    document.form.txt_valor_total_duplicatas.value = valor1 + valor2 + valor3 + valor4
    document.form.txt_valor_total_duplicatas.value = arred(document.form.txt_valor_total_duplicatas.value, 2, 1)
}
</Script>
</head>
<body onload='calcular_valor_total_duplicatas();document.form.txt_valor1.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********Controles de Tela**********-->
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf'];?>'>
<!--*************************************-->
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            ALTERAR VALORES DE DUPLICATAS
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor 1:</b>
        </td>
        <td>
            <?=$tipo_moeda;?>
            <input type='text' name='txt_valor1' value='<?=number_format($campos[0]['valor1'], 2, ',', '.');?>' title='Digite o Valor 1' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_total_duplicatas()" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <?
        if($campos[0]['valor2'] > 0) {//Existe Vencimento 2 ...
    ?>
    <tr class='linhanormal'>
        <td>
            Valor 2:
        </td>
        <td>
            <?=$tipo_moeda;?>
            <input type='text' name='txt_valor2' value='<?=number_format($campos[0]['valor2'], 2, ',', '.');?>' title='Digite o Valor 2' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_total_duplicatas()" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <?
        }
        
        if($campos[0]['valor3'] > 0) {//Existe Vencimento 3 ...
    ?>
    <tr class='linhanormal'>
        <td>
            Valor 3:
        </td>
        <td>
            <?=$tipo_moeda;?>
            <input type='text' name='txt_valor3' value='<?=number_format($campos[0]['valor3'], 2, ',', '.');?>' title='Digite o Valor 3' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_total_duplicatas()" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <?
        }
        
        if($campos[0]['valor4'] > 0) {//Existe Vencimento 4 ...
    ?>
    <tr class='linhanormal'>
        <td>
            Valor 4:
        </td>
        <td>
            <?=$tipo_moeda;?>
            <input type='text' name='txt_valor4' value='<?=number_format($campos[0]['valor4'], 2, ',', '.');?>' title='Digite o Valor 4' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_total_duplicatas()" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <?
        }
/************************************************************************************************/
    ?>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>VALOR TOTAL DA(S) DUPLICATA(S) => </b>
            </font>
        </td>
        <td>
            <?=$tipo_moeda;?>
            <input type='text' name='txt_valor_total_duplicatas' title='Valor Total das Duplicatas' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow'>
                Valor Total da Nota:
            </font>
        </td>
        <td>
            <?=$tipo_moeda;?> <?=number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');calcular_valor_total_duplicatas();document.form.txt_valor1.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>