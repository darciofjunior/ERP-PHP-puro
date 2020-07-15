<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/caixa_compras/caixa_compras.php', '../../../');

if(!empty($_POST['txt_valor'])) {
    $data_emissao   = data::datatodate($_POST['txt_data_emissao'], '-');
    if($_POST['hdd_acao'] == 'E') {
        $campo_valor    = ' `valor_credito` '; 
        $alert          = 'ENTRADA';
    }else {
        $campo_valor    = ' `valor_debito` ';
        $alert          = 'SAÍDA';
    }
   
    $sql = "INSERT INTO `caixas_compras` (`id_caixa_compra`, `id_funcionario`, `data_emissao`, $campo_valor, `observacao`) VALUES (NULL, '$_SESSION[id_funcionario]', '$data_emissao', '$_POST[txt_valor]', '$_POST[txt_observacao]') ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('<?=$alert;?> INCLUÍDA COM SUCESSO !')
        parent.location = parent.location.href
    </Script>
<?
}
?>
<html>
<head>
<title>.:: Incluir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data de Emissão
    if(!data('form', 'txt_data_emissao', '4000', 'EMISSÃO')) {
        return false
    }
//Valor de Débito ou Crédito ... 
    if(!texto('form', 'txt_valor', '4', '0123456789.,-', 'VALOR', '2')) {
        return false
    }
//Observação ...
    var acao = '<?=$_GET[acao];?>'
    if(acao == 'S') {//A observação só será obrigada a ser preenchida quando o Funcionário der uma ação de Saída ...
        if(document.form.txt_observacao.value == '') {
            alert('DIGITE A OBSERVAÇÃO !')
            document.form.txt_observacao.focus()
            return false
        }
    }
    //Preparo o campo p/ gravar na Base de Dados ...
    limpeza_moeda('form', 'txt_valor, ')
}
</Script>
</head>
<body onload='document.form.txt_valor.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--********************Controle de Tela********************-->
<input type='hidden' name='hdd_acao' value='<?=$_GET[acao];?>'>
<!--********************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <?
                /*Esse parâmetro 'acao'=E', vem da tela de Baixo e foi criado p/ termos somente um único arquivo 
                de Inclusão, facilitando futuras manutenções ...*/
                if($_GET['acao'] == 'E') {
                    $acao       = 'Entrada';
                    $observacao = 'ENTRADA';
                }else {
                    $acao       = 'Saída';
                    $observacao = 'REQUISIÇÃO';
                }
            ?>
            Incluir <?=$acao;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emissão:</b>
        </td>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=date('d/m/Y');?>' title='Digite a Data de Emissão' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor:</b>
        </td>
        <td>
            <input type='text' name='txt_valor' title='Digite o Valor' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' maxlength='255' cols='64' rows='4' title='Digite a Observação' class='caixadetexto'><?=$observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_valor.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>