<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/banco_horas/relatorio.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>BANCO DE HORA(S) ALTERADO COM SUCESSO.</font>";

if(!empty($_POST['hdd_banco_hora'])) {
    if($_POST['txt_data_lancamento'] == '00/00/0000') {//Caso a Data de Lançamento esteja vazia não permite incluir registro ...
?>
    <Script Language = 'JavaScript'>
        alert('DATA DE LANÇAMENTO INVÁLIDA !!!')
        window.close()
    </Script>
<?
    }else {//Caso a Data esteja preenchida de forma correta, então registra a ocorrência ...
        $data_lancamento        = data::datatodate($_POST['txt_data_lancamento'], '-');
        $descontar_hora_almoco  = (!empty($_POST['chkt_descontar_hora_almoco'])) ? 'S' : 'N';
        //Atualizando a Base de Dados ...
        $sql = "UPDATE `bancos_horas` SET `data_lancamento` = '$data_lancamento', `hora_inicial` = '$_POST[txt_hora_inicial]', `hora_final` = '$_POST[txt_hora_final]', `qtde_horas` = '$_POST[txt_qtde_horas]', `descontar_hora_almoco` = '$descontar_hora_almoco', `observacao` = '$_POST[txt_observacao]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_banco_hora` = '$_POST[hdd_banco_hora]' LIMIT 1 ";
        bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('BANCO DE HORA(S) ALTERADO COM SUCESSO !!!')
        //Atualizo a tela de baixo que chamou esse Pop-UP ...
        window.opener.location = window.opener.location.href
        window.close()
    </Script>
<?
    }
    exit;
}

//Aqui eu trago dados do Banco de Horas passado por parâmetro ...
$sql = "SELECT id_funcionario, DATE_FORMAT(data_lancamento, '%d/%m/%Y') AS data_lancamento, TIME_FORMAT(hora_inicial, '%H:%i') AS hora_inicial, TIME_FORMAT(hora_final, '%H:%i') AS hora_final, TIME_FORMAT(qtde_horas, '%H:%i') AS qtde_horas, descontar_hora_almoco, observacao 
        FROM `bancos_horas` 
        WHERE `id_banco_hora` = '$_GET[id_banco_hora]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Banco de Hora(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data de Ocorrência ...
    if(!data('form', 'txt_data_lancamento', '4000', 'OCORRÊNCIA')) {
        return false
    }
//Verifico se foi selecionado o Contabilizar ...
    if(document.getElementById('lbl_credito').checked == false && document.getElementById('lbl_debito').checked == false) {
        alert('SELECIONE O CONTABILIZAR !')
        document.getElementById('lbl_credito').focus()
        return false
    }
//Hora Inicial
    if(!texto('form', 'txt_hora_inicial', '1', '1234567890:', 'HORA INICIAL', '1')) {
        return false
    }
//Hora Final
    if(!texto('form', 'txt_hora_final', '1', '1234567890:', 'HORA FINAL', '1')) {
        return false
    }
/*******Aqui eu verifico se o Usuário não digitou valores incoerentes na Hora e no Minuto da Hora Inicial ...*******/
    var vetor_qtde_horas_inicial= document.form.txt_hora_inicial.value.split(':')
    var horas_inicial           = vetor_qtde_horas_inicial[0]
    var minutos_inicial         = vetor_qtde_horas_inicial[1]
//Aqui eu verifico se as Horas digitadas pelo usuário estão Inválidas ...
    if(horas_inicial > 23) {
        alert('QTDE DE HORA(S) INICIAL(IS) INVÁLIDA !!!\n\nDIGITE HORA(S) INICIAL(IS) CORRETA ATÉ 23 !')
        document.form.txt_hora_inicial.focus()
        document.form.txt_hora_inicial.select()
        return false
    }
//Aqui eu verifico se os Minutos digitados pelo usuário estão Inválidos ...
    if(minutos_inicial > 59) {
        alert('QTDE DE MINUTO(S) INICIAL(IS) INVÁLIDO !!!\n\nDIGITE MINUTO(S) INICIAL(IS) CORRETO(S) ATÉ 59 !')
        document.form.txt_hora_inicial.focus()
        document.form.txt_hora_inicial.select()
        return false
    }
/*******Aqui eu verifico se o Usuário não digitou valores incoerentes na Hora e no Minuto da Hora Final ...*******/
    var vetor_qtde_horas_final  = document.form.txt_hora_final.value.split(':')
    var horas_final             = vetor_qtde_horas_final[0]
    var minutos_final           = vetor_qtde_horas_final[1]
//Aqui eu verifico se as Horas digitadas pelo usuário estão Inválidas ...
    if(horas_final > 23) {
        alert('QTDE DE HORA(S) FINAL(IS) INVÁLIDA !!!\n\nDIGITE HORA(S) FINAL(IS) CORRETA ATÉ 23 !')
        document.form.txt_hora_final.focus()
        document.form.txt_hora_final.select()
        return false
    }
//Aqui eu verifico se os Minutos digitados pelo usuário estão Inválidos ...
    if(minutos_final > 59) {
        alert('QTDE DE MINUTO(S) FINAL(IS) INVÁLIDO !!!\n\nDIGITE MINUTO(S) FINAL(IS) CORRETO(S) ATÉ 59 !')
        document.form.txt_hora_final.focus()
        document.form.txt_hora_final.select()
        return false
    }
/*******************************************************************************************************************/
/*Aqui eu verifico se todo o Horário Final Digitado é menor do que todo o Horário Inicial Digitado, 
o que não podemos hoje ...*/
    var horario_inicial = eval(horas_inicial + minutos_inicial)
    var horario_final   = eval(horas_final   + minutos_final)
    if(horario_final < horario_inicial) {
        alert('HORA(S) FINAL(IS) INVÁLIDA !!!\n\nHORA(S) FINAL(IS) MENOR DO QUE A HORA INICIAL(IS) !')
        document.form.txt_hora_final.focus()
        document.form.txt_hora_final.select()
        return false
    }
    //Aqui preparo p/ gravar no Banco de Dados ...
    document.form.txt_qtde_horas.disabled   = false
}

function calcular_qtde_horas() {
    //Quando o campo Hora Inicial e a Hora Final estiverem preenchidas, faço o Cálculo de Qtde Horas ...
    if(document.form.txt_hora_inicial.value != '' && document.form.txt_hora_final.value != '') {
        var vetor_qtde_horas_inicial= document.form.txt_hora_inicial.value.split(':')
        var horas_inicial           = eval(vetor_qtde_horas_inicial[0])
        var minutos_inicial         = eval(vetor_qtde_horas_inicial[1])

        var vetor_qtde_horas_final  = document.form.txt_hora_final.value.split(':')
        var horas_final             = eval(vetor_qtde_horas_final[0])
        var minutos_final           = eval(vetor_qtde_horas_final[1])

        var qtde_horas              = horas_final - horas_inicial
        if(minutos_final < minutos_inicial) {
            var qtde_minutos = (minutos_final - minutos_inicial) + 60
            qtde_horas-= 1//Aqui eu subtraio uma hora ...
        }else {
            var qtde_minutos = (minutos_final - minutos_inicial)
        }
        //Se foi selecionada a opção de Descontar Hora de Almoço ...
        if(document.getElementById('id_descontar_hora_almoco').checked == true) qtde_horas-= 1//Aqui eu subtraio uma hora ...
        
        //Tratamento p/ exibir os minutos com 2 dígitos ...
        if(qtde_minutos < 10) qtde_minutos = '0' + qtde_minutos
        if(qtde_horas >= 0 && qtde_horas < 10) qtde_horas = '0' + qtde_horas
        //Se foi selecionada a opção de Debitar então acrescento o Sinal Negativo na frente da hora se é que esse ainda não existe ...
        if(document.getElementById('lbl_debito').checked == true) {
            //Transformo a Hora em String de Propósito, senão eu não consigo usar as funções de String ...
            qtde_horas = String(qtde_horas)
            if(qtde_horas.substr(0, 1) != '-') qtde_horas = '-' + qtde_horas
        }
        document.form.txt_qtde_horas.value = qtde_horas + ':' + qtde_minutos
    }else {//Se não limpo a variável ...
        document.form.txt_qtde_horas.value = ''
    }
}
</Script>
</head>
<body onload='document.form.txt_hora_inicial.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='hdd_banco_hora' value="<?=$_GET['id_banco_hora'];?>">
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Banco de Hora(s)
        </td>
    </tr>
    <tr class="linhanormal">
        <td width='15%'>
            Funcionário:
        </td>
        <td width='45%'>
        <?
            $sql = "SELECT nome, id_funcionario_superior 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[0]['id_funcionario']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo $campos_funcionario[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Lançamento:</b>
        </td>
        <td>
            <input type="text" name="txt_data_lancamento" value='<?=$campos[0]['data_lancamento'];?>' title="Digite a Data de Lançamento" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            &nbsp;<img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_lancamento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Contabilizar:</b>
        </td>
        <td>
            <?
                //Se o 1º caractér for negativo, então isso significa que foi lançado um Débito ...
                if(substr($campos[0]['qtde_horas'], 0, 1) == '-') {//Débito ...
                    $debito_checked = 'checked';
                }else {//Crédito ...
                    $credito_checked = 'checked';
                }
            ?>
            <input type='radio' name='opt_opcao' id='lbl_credito' value='Crédito' onclick='calcular_qtde_horas()' <?=$credito_checked;?>>
            <label for='lbl_credito'>
                Crédito
            </label>
            &nbsp;
            <input type='radio' name='opt_opcao' id='lbl_debito' value='Débito' onclick='calcular_qtde_horas()' <?=$debito_checked;?>>
            <label for='lbl_debito'>
                Débito
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Hora Inicial:</b>
        </td>
        <td>
            <input type='text' name='txt_hora_inicial' value='<?=$campos[0]['hora_inicial'];?>' title='Digite a Hora Inicial' onkeyup="verifica(this, 'hora', '', '', event);calcular_qtde_horas()" onblur='calcular_qtde_horas()' size='8' maxlength='5' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Hora Final:</b>
        </td>
        <td>
            <input type='text' name='txt_hora_final' value='<?=$campos[0]['hora_final'];?>' title='Digite a Hora Final' onkeyup="verifica(this, 'hora', '', '', event);calcular_qtde_horas()" onblur='calcular_qtde_horas()' size='8' maxlength='5' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtde de Horas:
        </td>
        <td>
            <input type='text' name='txt_qtde_horas' value='<?=$campos[0]['qtde_horas'];?>' title='Qtde de Horas' size='8' maxlength='5' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <?
                $checked = ($campos[0]['descontar_hora_almoco'] == 'S') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_descontar_hora_almoco' id='id_descontar_hora_almoco' title='Descontar Hora de Almoço' onclick='calcular_qtde_horas()' class='checkbox' <?=$checked;?>>
            <label for='id_descontar_hora_almoco'>
                Descontar Hora de Almoço
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' title="Digite a Observação" cols='51' rows='5' maxlength='255' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_hora_inicial.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>