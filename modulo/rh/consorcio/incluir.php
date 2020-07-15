<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>CONS”RCIO INCLUIDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CONS”RCIO J¡ EXISTENTE.</font>";

if($_POST['passo'] == 1) {
//Aqui eu verifico no Sistema se j· existe cadastrado o ConsÛrcio digitado pelo usu·rio ...
    $sql = "SELECT id_consorcio 
            FROM `consorcios` 
            WHERE `nome_grupo` = '$_POST[txt_nome_grupo]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//N„o existe ...
        $observacao =  ucfirst(strtolower($_POST['txt_observacao']));
        $sql = "INSERT INTO `consorcios` (`id_consorcio`, `nome_grupo`, `valor`, `juros`, `data_inicial`, `meses`, `observacao`) VALUES (NULL, '$_POST[txt_nome_grupo]', '$_POST[txt_valor]', '$_POST[txt_juros]', '$_POST[cmb_data_holerith]', '$_POST[txt_meses]', '$observacao') ";
        bancos::sql($sql);
        $id_consorcio = bancos::id_registro();
        $valor = 1;
    }else {//J· existe ...
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'itens/index.php?id_consorcio=<?=$id_consorcio;?>&valor=<?=$valor;?>'
    </Script>
<?
}
?>
<html>
<head>
<title>.:: Incluir ConsÛrcio(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Nome do Grupo
    if(!texto('form', 'txt_nome_grupo', '3', '0123456789„ı√’·ÈÌÛ˙¡…Õ”⁄abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÁ« _-', 'NOME DO GRUPO', '2')) {
        return false
    }
//Valor
    if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
        return false
    }
//Juros
    if(!texto('form', 'txt_juros', '1', '1234567890,.', 'JUROS', '2')) {
        return false
    }
//Data do Holerith
    if(!combo('form', 'cmb_data_holerith', '', 'SELECIONE A DATA DE HOLERITH !')) {
        return false
    }
//Meses
    if(!texto('form', 'txt_meses', '1', '1234567890', 'QUANTIDADE DE MESES', '1')) {
        return false
    }
    document.form.passo.value = 1
    return limpeza_moeda('form', 'txt_valor, txt_juros, ')
}

function incluir_data_holerith() {
    nova_janela('../vales/class_data_holerith/incluir.php', 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        nova_janela('../vales/class_data_holerith/alterar.php?data='+document.form.cmb_data_holerith.value, 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function atualizar() {
    document.form.passo.value = 0
    document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_nome_grupo.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Esse hidden È um controle de Tela-->
<input type='hidden' name='passo' onclick='atualizar()'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir ConsÛrcio(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome do Grupo:</b>
        </td>
        <td>
            <input type='text' name='txt_nome_grupo' title='Digite o Nome do Grupo' size='26' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor:</b>
        </td>
        <td>
            <input type='text' name='txt_valor' title='Digite o Valor' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Juros:</b>
        </td>
        <td>
            <input type='text' name='txt_juros' title='Digite os Juros' size='6' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Holerith Inicial:</b>
        </td>
        <td>
            <select name="cmb_data_holerith" title="Selecione a Data de Holerith Inicial" class="combo">
            <?
                $data_atual = date('Y-m-d');
//SÛ listo nessa Combo as Datas de Holeriths que sejam > que a Data de Atual ...
                $sql = "SELECT data, DATE_FORMAT(data, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE `data` > '$data_atual' ORDER BY data ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../imagem/menu/incluir.png' border='0' title='Incluir Data de Holerith' alt='Incluir Data de Holerith' onclick='incluir_data_holerith()'>
            &nbsp;&nbsp; <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar Data de Holerith' alt='Alterar Data de Holerith' onclick='alterar_data_holerith()'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Meses:</b>
        </td>
        <td>
            <input type='text' name='txt_meses' title='Digite os Meses' size='8' maxlength='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_nome_grupo.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>