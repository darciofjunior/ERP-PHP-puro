<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/hora_extra/opcoes_gerenciar_hora_extra.php', '../../../');

$mensagem[1] = "<font class='atencao'>ESSE PERÍODO NÃO PODE SER ALTERADO, DEVIDO NÃO SER + O ÚLTIMO.</font>";
$mensagem[2] = "<font class='confirmacao'>HORA(S) EXTRA(S) ALTERADA(S) COM SUCESSO.</font>";

if($passo == 1) {
//Muda o Formato das variáveis p/ poder gravar no BD ...
    $data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-');
    $data_final     = data::datatodate($_POST['txt_data_final'], '-');
    $data_pagamento = data::datatodate($_POST['txt_data_pagamento'], '-');
/*Na tabela de Relatórios atualizo a Data Final e Data de Pagamento de todos os funcionários que estão na 
Data Inicial do Pagamento especificado ...*/
    $sql = "UPDATE `funcionarios_hes_rel` SET `data_final` = '$data_final', `data_pagamento` = '$data_pagamento' WHERE `data_inicial` = '$data_inicial' ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_periodo.php?txt_data_inicial=<?=$_POST['txt_data_inicial'];?>&txt_data_final=<?=$_POST['txt_data_final'];?>&txt_data_pagamento=<?=$_POST['txt_data_pagamento'];?>&valor=2'
    </Script>
<?
}else {
//Muda o Formato das variável p/ poder fazer a consultas via SQL abaixo ...
    $txt_data_inicial = data::datatodate($txt_data_inicial, '-');
/*Eu só posso estar editando a Data Final e a Data de Pagamento do último Período, sendo assim 
eu faço essa verificação, verifico se existe algum período posterior ao do que foi selecionado ...*/
    $sql = "SELECT id_funcionario_he_rel 
            FROM `funcionarios_hes_rel` 
            WHERE `data_inicial` > '$txt_data_inicial' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
        $onload = 'document.form.txt_data_final.focus()';
        $onunload = 'atualizar_abaixo()';
    }
?>
<html>
<head>
<title>.:: Alterar Período ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
//Data de Pagamento
    if(!data('form', 'txt_data_pagamento', '4000', 'PAGAMENTO')) {
        return false
    }
/*****************************Seguranças com as Datas*****************************/
//Comparações entre as Datas de Prazo ...
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value

    data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)

    data_inicial = eval(data_inicial)
    data_final = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 1 mês. Faço essa verificação porque se o usuário colocar 
um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 30) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A 1 MÊS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
//Comparações com a Data de Pagamento ...
    var data_pagamento = document.form.txt_data_pagamento.value
    data_pagamento = data_pagamento.substr(6,4)+data_pagamento.substr(3,2)+data_pagamento.substr(0,2)
    data_pagamento = eval(data_pagamento)
//1) Comparação da Data de Pagamento com a Data Final ...
    if(data_pagamento < data_final) {
        alert('DATA DE PAGAMENTO INVÁLIDA !!!\n DATA DE PAGAMENTO MENOR DO QUE A DATA FINAL !')
        document.form.txt_data_pagamento.focus()
        document.form.txt_data_pagamento.select()
        return false
    }
//2) Comparação da Data de Pagamento com a Data de Emissão ...
    var data_atual = eval('<?=date("Ymd");?>')
    if(data_pagamento < data_atual) {
        alert('DATA DE PAGAMENTO INVÁLIDA !!!\n DATA DE PAGAMENTO MENOR DO QUE A DATA ATUAL !')
        document.form.txt_data_pagamento.focus()
        document.form.txt_data_pagamento.select()
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}
</Script>
</head>
<body onload='<?=$onload;?>' onunload='<?=$onunload;?>'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar()">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    if($linhas == 1) {//Significa que esse não é o último período e sendo assim não posso alterar
?>
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
<?
        exit;
    }
?>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Período
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Inicial: </b>
        </td>
        <td>
            <?=data::datetodata($txt_data_inicial, '/');?>
            <input type='hidden' name='txt_data_inicial' value="<?=data::datetodata($txt_data_inicial, '/');?>">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Final: </b>
        </td>
        <td>
            <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" size="11" maxlength="10" onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src="../../../imagem/calendario.gif" width="12" height="12" alt="" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Pagamento: </b>
        </td>
        <td>
            <input type="text" name="txt_data_pagamento" value="<?=$txt_data_pagamento;?>" size="11" maxlength="10" onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src="../../../imagem/calendario.gif" width="12" height="12" alt="" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_pagamento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_data_final.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title="Salvar" style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>