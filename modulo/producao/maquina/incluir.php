<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = 'M¡QUINA INCLUIDA COM SUCESSO !';
$mensagem[2] = 'M¡QUINA J¡ EXISTENTE !';

if(!empty($_POST['txt_maquina'])) {
    //Verifico se essa M·quina que o usu·rio est· tentando cadastrar, j· existe no Sistema ...
    $sql = "SELECT id_maquina 
            FROM `maquinas` 
            WHERE `nome` = '$_POST[txt_maquina]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe, posso cadastrar a Nova m·quina ...
        $sql = "INSERT INTO `maquinas` (`id_maquina`, `nome`, `valor`, `qtde_maq_vs_func`, `duracao`, `caracteristica`, `observacao`, `porc_ferramental`, `setup`, `data_sys`) VALUES (NULL, '$_POST[txt_maquina]', '$_POST[txt_valor]', '$_POST[txt_qtde_maq_func]', '$_POST[txt_duracao]', '$_POST[txt_caracteristica]', '$_POST[txt_observacao]', '$_POST[txt_porc_ferramental]', '$_POST[txt_setup]', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $id_maquina = bancos::id_registro();
        $valor = 1;
    }else {//M·quina j· existente ...
        $id_maquina = $campos[0]['id_maquina'];
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[$valor];?>')
        var resposta = confirm('DESEJA IR PARA O ATRELAMENTO DE FUNCION¡RIO VS M¡QUINA ?')
        if(resposta == true) window.location = 'alterar.php?passo=2&id_maquina=<?=$id_maquina;?>'
    </Script>
<?
}
?>
<html>
<title>.:: Incluir M·quina ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//M·quina ...
    if(!texto('form', 'txt_maquina', '1', "abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ '1234567890", 'M¡QUINA', '1')) {
        return false
    }
//Valor R$ ...
    if(!texto('form', 'txt_valor', '1', '1234567890.,', 'VALOR R$', '2')) {
        return false
    }
//Qtde de M·q. por Func ...
    if(!texto('form', 'txt_qtde_maq_func', '1', '1234567890.,', 'QUANTIDADE DE M¡QUINA POR FUNCION¡RIO', '1')) {
        return false
    }
//Anos p/ AmortizaÁ„o ...
    if(!texto('form', 'txt_duracao', '1', '1234567890.,', 'ANO P/ AMORTIZA«√O', '2')) {
        return false
    }
//Porc Ferramental ...
    if(!texto('form', 'txt_porc_ferramental', '1', '1234567890.,', 'PORCENTAGEM FERRAMENTAL', '1')) {
        return false
    }
//Setup ...
    if(!texto('form', 'txt_setup', '1', '1234567890.,', 'SETUP', '2')) {
        return false
    }
    limpeza_moeda('form', 'txt_valor, txt_qtde_maq_func, txt_duracao, txt_porc_ferramental, txt_setup, ')
}
</Script>
<body onload='document.form.txt_maquina.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir M·quina
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            <b>M·quina:</b>
        </td>
        <td>
            <input type='text' name='txt_maquina' title='Digite a M·quina' size='50' maxlength='80' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor R$: </b>
        </td>
        <td>
            <input type='text' name='txt_valor' title='Digite o Valor R$' size='15' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>M·quina por Funcion·rio:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_maq_func' title='Digite a Quantidade de M·quina por Funcion·rio' size='7' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>&nbsp;&nbsp;Quantidade
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Anos p/ AmortizaÁ„o:</b>
        </td>
        <td>
            <input type='text' name='txt_duracao' title='Digite os Anos p/ AmortizaÁ„o' size='7' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Porcentagem Ferramental:</b>
        </td>
        <td>
            <input type='text' name='txt_porc_ferramental' title='Digite a Porcentagem Ferramental' size='10' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Setup:</b>
        </td>
        <td>
            <input type='text' name='txt_setup' title='Digite o Setup' size='10' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '1', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CaracterÌstica:
        </td>
        <td>
            <textarea name='txt_caracteristica' title='Digite a CaracterÌstica' cols='50' rows='2' maxlength='100' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_observacao' title="Digite a ObservaÁ„o" cols='40' rows='2' maxlength='80' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form','LIMPAR');document.form.txt_maquina.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>