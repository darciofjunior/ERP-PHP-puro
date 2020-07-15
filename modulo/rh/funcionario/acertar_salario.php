<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/variaveis/dp.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/alterar.php', '../../../');

if(!empty($_POST['id_funcionario_loop'])) {
/******************************************************************************************/
//Atualizo todos os sal�rios existentes na Tabela de "Funcion�rios" ...
    if(!empty($_POST[txt_novo_salario_pd])) {
        $sql = "UPDATE `funcionarios` SET `salario_pd` = '$_POST[txt_novo_salario_pd]', `data_registro` = '".date('Y-m-d H:i:s')."' WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' LIMIT 1 ";
        bancos::sql($sql);

        $observacao.= '<br/>Novo Sal�rio PD = R$ '.number_format($_POST['txt_novo_salario_pd'], 2, ',', '.');
        if(!empty($_POST['txt_perc_salario_pd'])) $observacao.= ', aumento = '.$_POST['txt_perc_salario_pd'].' %';
    }
    
    if(!empty($_POST[txt_novo_salario_pf])) {
        $sql = "UPDATE `funcionarios` SET `salario_pf` = '$_POST[txt_novo_salario_pf]', `data_registro` = '".date('Y-m-d H:i:s')."' WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' LIMIT 1 ";
        bancos::sql($sql);
        
        $observacao.= '<br/>Novo Sal�rio PF = R$ '.number_format($_POST['txt_novo_salario_pf'], 2, ',', '.');
        if(!empty($_POST['txt_perc_salario_pf'])) $observacao.= ', aumento = '.$_POST['txt_perc_salario_pf'].' %';
    }
    
    if(!empty($_POST[txt_novo_salario_premio])) {
        $sql = "UPDATE `funcionarios` SET `salario_premio` = '$_POST[txt_novo_salario_premio]', `data_registro` = '".date('Y-m-d H:i:s')."' WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' LIMIT 1 ";
        bancos::sql($sql);
        
        $observacao.= '<br/>Novo Pr�mio PF = R$ '.number_format($_POST['txt_novo_salario_premio'], 2, ',', '.');
        if(!empty($_POST['txt_perc_salario_pf'])) $observacao.= ', aumento = '.$_POST['txt_perc_salario_premio'].' %';
    }
    
    if(!empty($_POST[txt_novo_garantia_salarial])) {
        $sql = "UPDATE `funcionarios` SET `garantia_salarial` = '$_POST[txt_novo_garantia_salarial]', `data_registro` = '".date('Y-m-d H:i:s')."' WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' LIMIT 1 ";
        bancos::sql($sql);
        
        $observacao.= '<br/>Nova Garantia Salarial = R$ '.number_format($_POST['txt_novo_garantia_salarial'], 2, ',', '.');
        if(!empty($_POST['txt_perc_garantia_salarial'])) $observacao.= ', aumento = '.$_POST['txt_perc_garantia_salarial'].' %';
    }
        
    //Significa que houve alguma mudan�a Salarial em uma das 4 situa��es acima ...
    if(!empty($_POST[txt_novo_salario_pd]) || !empty($_POST[txt_novo_salario_pf]) || !empty($_POST[txt_novo_salario_premio]) || !empty($_POST[txt_novo_garantia_salarial])) {
        $observacao.= '<br/>Aumento Total = '.$_POST[txt_total_perc_salario].'%';
        
        //Atualizo o Acompanhamento do Funcion�rio com o Respectivo Aumento ...
        $sql = "INSERT INTO `funcionarios_acompanhamentos` (`id_funcionario_acompanhamento`, `id_funcionario_registrou`, `id_funcionario_acompanhado`, `observacao`, `data_ocorrencia`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_POST[id_funcionario_loop]', '$observacao', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
/***********************************Email***********************************/
/*Email de Confer�ncia porque estavam burlando o sistema, fazendo altera��es de sal�rio por tr�s do BD, 
essa � uma informa��o de risco.

Aqui eu trago o Funcion�rio que recebeu o aumento ou teve acerto no seu sal�rio ...*/
        $sql = "SELECT nome 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' LIMIT 1 ";
        $campos_funcionario_registrado = bancos::sql($sql);
     
//Funcion�rio que registrou a Ocorr�ncia ...
        $sql = "SELECT nome 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        $campos_funcionario_registrou = bancos::sql($sql);

        $mensagem_email = 'A partir de agora, o Funcion�rio <b>"'.$campos_funcionario_registrado[0]['nome'].'"</b> teve reajuste / aumento salarial de -> '.$observacao;
        $mensagem_email.= '<br/><br/>O Funcion�rio que registrou essa Ocorr�ncia foi: <b>"'.$campos_funcionario_registrou[0]['nome'].'"</b> no dia '.date('d/m/Y').' �s '.date('H:i:s').'.';

        comunicacao::email('ERP - GRUPO ALBAFER', 'roberto@grupoalbafer.com.br', 'sandra@grupoalbafer.com.br', 'Reajuste / Aumento Salarial', $mensagem_email, 'darcio@grupoalbafer.com.br');
/***************************************************************************/
    }
?>
    <Script Language = 'JavaScript'>
//Tela de Baixo ...
        alert('SAL�RIO ALTERADO COM SUCESSO !')
        top.opener.document.form.passo.value = 1
        top.opener.document.form.submit()
        window.close()
    </Script>
<?
}

//Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o
$sql = "SELECT f.`nome`, IF(f.`tipo_salario` = '1', 'HORISTA', 'MENSALISTA') AS tipo_salario, f.`salario_pd`, f.`salario_pf`, 
        f.`salario_premio`, f.`garantia_salarial`, e.`nomefantasia` 
        FROM `funcionarios` f 
        INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
        WHERE f.`id_funcionario` = '$_GET[id_funcionario_loop]' LIMIT 1 ";
$campos             = bancos::sql($sql);
//Aqui eu renomeio essa vari�vel p/ $id_empresa_func, porque j� uma $id_empresa dentro da sess�o do Sistema
$tipo_salario       = $campos[0]['tipo_salario'];
//Valida��o do S�lario PD
$salario_pd         = ($campos[0]['salario_pd'] == '0.00') ? '' : number_format($campos[0]['salario_pd'], 2, ',', '.');
$salario_pf         = ($campos[0]['salario_pf'] == '0.00') ? '' : number_format($campos[0]['salario_pf'], 2, ',', '.');
$salario_premio     = ($campos[0]['salario_premio'] == '0.00') ? '' : number_format($campos[0]['salario_premio'], 2, ',', '.');
$garantia_salarial  = ($campos[0]['garantia_salarial'] == '0.00') ? '' : number_format($campos[0]['garantia_salarial'], 2, ',', '.');
$salario_total      = $campos[0]['salario_pd'] + $campos[0]['salario_pf'];
/****************************************************************************************/
?>
<html>
<head>
<title>.:: Acertar Sal�rio ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Sal�rio PD ...
    if(document.form.txt_novo_salario_pd.value != '') {
        if(!texto('form', 'txt_novo_salario_pd', '1', '0123456789,.', 'NOVO SAL�RIO PD', '2')) {
            return false
        }
    }
//Sal�rio PF ...
    if(document.form.txt_novo_salario_pf.value != '') {
        if(!texto('form', 'txt_novo_salario_pf', '1', '0123456789,.', 'NOVO SAL�RIO PF', '2')) {
            return false
        }
    }
//Sal�rio Pr�mio ...
    if(document.form.txt_novo_salario_premio.value != '') {
        if(!texto('form', 'txt_novo_salario_premio', '1', '0123456789,.', 'NOVO SAL�RIO PR�MIO', '2')) {
            return false
        }
    }
//Garantia Salarial ...
    if(document.form.txt_novo_garantia_salarial.value != '') {
        if(!texto('form', 'txt_novo_garantia_salarial', '1', '0123456789,.', 'NOVO GARANTIA SALARIAL', '2')) {
            return false
        }
    }
//Desabilito os campos p/ poder gravar no BD ...
    document.form.txt_perc_salario_pd.disabled          = false
    document.form.txt_perc_salario_pf.disabled          = false
    document.form.txt_perc_salario_premio.disabled      = false
    document.form.txt_perc_garantia_salarial.disabled   = false
    document.form.txt_total_perc_salario.disabled       = false
    return limpeza_moeda('form', 'txt_novo_salario_pd, txt_novo_salario_pf, txt_novo_salario_premio, txt_novo_garantia_salarial, ')
}

function calcular_novo_salario(indice) {
    if(indice == 1) {//Sal�rio PD ...
        if(document.form.txt_novo_salario_pd.value != '' && document.form.txt_salario_pd.value != '') {//Porque sen�o d� erro de Divis�o por Zero ...
            var salario_pd      = eval(strtofloat(document.form.txt_salario_pd.value))
            var novo_salario_pd = eval(strtofloat(document.form.txt_novo_salario_pd.value))
            document.form.txt_perc_salario_pd.value = (novo_salario_pd / salario_pd - 1) * 100
            document.form.txt_perc_salario_pd.value = arred(document.form.txt_perc_salario_pd.value, 2, 1)
        }
    }else if(indice == 2) {//Sal�rio PF ...
        if(document.form.txt_novo_salario_pf.value != '' && document.form.txt_salario_pf.value != '') {//Porque sen�o d� erro de Divis�o por Zero ...
            var salario_pf      = eval(strtofloat(document.form.txt_salario_pf.value))
            var novo_salario_pf = eval(strtofloat(document.form.txt_novo_salario_pf.value))
            document.form.txt_perc_salario_pf.value = (novo_salario_pf / salario_pf - 1) * 100
            document.form.txt_perc_salario_pf.value = arred(document.form.txt_perc_salario_pf.value, 2, 1)
        }
    }else if(indice == 3) {//Sal�rio Pr�mio ...
        if(document.form.txt_novo_salario_premio.value != '' && document.form.txt_salario_premio.value != '') {//Porque sen�o d� erro de Divis�o por Zero ...
            var salario_premio      = eval(strtofloat(document.form.txt_salario_premio.value))
            var novo_salario_premio = eval(strtofloat(document.form.txt_novo_salario_premio.value))
            document.form.txt_perc_salario_premio.value = (novo_salario_premio / salario_premio - 1) * 100
            document.form.txt_perc_salario_premio.value = arred(document.form.txt_perc_salario_premio.value, 2, 1)
        }
    }else if(indice == 4) {//Garantia Salarial ...
        if(document.form.txt_novo_garantia_salarial.value != '' && document.form.txt_garantia_salarial.value != '') {//Porque sen�o d� erro de Divis�o por Zero ...
            var garantia_salarial       = eval(strtofloat(document.form.txt_garantia_salarial.value))
            var novo_garantia_salarial  = eval(strtofloat(document.form.txt_novo_garantia_salarial.value))
            document.form.txt_perc_garantia_salarial.value = (novo_garantia_salarial / garantia_salarial - 1) * 100
            document.form.txt_perc_garantia_salarial.value = arred(document.form.txt_perc_garantia_salarial.value, 2, 1)
        }
    }
    /**********************************C�lculo do Novo Sal�rio Total**********************************/
    //Se o usu�rio preencheu a Caixa de Novo Sal�rio PD ...
    if(document.form.txt_novo_salario_pd.value != '') {
        var salario_pd = eval(strtofloat(document.form.txt_novo_salario_pd.value))
    }else {
        var salario_pd = (document.form.txt_salario_pd.value != '') ? eval(strtofloat(document.form.txt_salario_pd.value)) : 0
    }
    //Se o usu�rio preencheu a Caixa de Novo Sal�rio PF ...
    if(document.form.txt_novo_salario_pf.value != '') {
        var salario_pf = eval(strtofloat(document.form.txt_novo_salario_pf.value))
    }else {
        var salario_pf = (document.form.txt_salario_pf.value != '') ? eval(strtofloat(document.form.txt_salario_pf.value)) : 0
    }
    //Se o usu�rio preencheu a Caixa de Novo Sal�rio Pr�mio ...
    if(document.form.txt_novo_salario_premio.value != '') {
        var salario_premio = eval(strtofloat(document.form.txt_novo_salario_premio.value))
    }else {
        var salario_premio = (document.form.txt_salario_premio.value != '') ? eval(strtofloat(document.form.txt_salario_premio.value)) : 0
    }
    /*************************************************************************************************/
    var salario_total       = eval(strtofloat(document.form.txt_salario_total.value))
    var novo_salario_total  = (salario_pd + salario_pf)
    var perc_salario_total  = (novo_salario_total / salario_total - 1) * 100
    
    document.form.txt_novo_salario_total.value = novo_salario_total
    document.form.txt_novo_salario_total.value = arred(document.form.txt_novo_salario_total.value, 2, 1)
    
    document.form.txt_total_perc_salario.value = perc_salario_total
    document.form.txt_total_perc_salario.value = arred(document.form.txt_total_perc_salario.value, 2, 1)
}
</Script>
</head>
<body onload='calcular_novo_salario();document.form.txt_novo_salario_pd.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o-->
<input type='hidden' name='id_funcionario_loop' value='<?=$_GET['id_funcionario_loop'];?>'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Acertar Sal�rio
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font color='yellow'>
                Nome: 
            </font>
            <?=$campos[0]['nome'];?>
            <font color='yellow'>
                - Empresa: 
            </font>
            <?=$campos[0]['nomefantasia'];?>
            <font color='yellow'>
                - Tipo de Sal�rio: 
            </font>
            <?=$tipo_salario;?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            &nbsp;
        </td>
        <td bgcolor='#CECECE'>
            <b>ATUAL</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>NOVO</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>% AUMENTO</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            Sal�rio PD: 
        </td>
        <td>
            <input type='text' name='txt_salario_pd' value='<?=$salario_pd;?>' maxlength='12' size='15' title='Sal�rio PD' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_novo_salario_pd' maxlength='12' size='15' title='Digite o Novo Sal�rio PD' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_novo_salario(1)" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_perc_salario_pd' maxlength='12' size='15' title='% de Aumento Sal�rio PD' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            Sal�rio PF: 
        </td>
        <td>
            <input type='text' name='txt_salario_pf' value='<?=$salario_pf;?>' maxlength='12' size='15' title='Sal�rio PF' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_novo_salario_pf' maxlength='12' size='15' title='Digite o Novo Sal�rio PF' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_novo_salario(2)" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_perc_salario_pf' maxlength='12' size='15' title='% de Aumento Sal�rio PF' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            Pr�mio PF (N�o Incide 13� + F�rias): 
        </td>
        <td>
            <input type='text' name='txt_salario_premio' value='<?=$salario_premio;?>' maxlength='12' size='15' title='Sal�rio Pr�mio' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_novo_salario_premio' maxlength='12' size='15' title='Digite o Novo Sal�rio Pr�mio' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_novo_salario(3)" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_perc_salario_premio' maxlength='12' size='15' title='% de Aumento Sal�rio Pr�mio' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            Garantia Salarial: 
        </td>
        <td>
            <input type='text' name='txt_garantia_salarial' value='<?=$garantia_salarial;?>' maxlength='12' size='15' title='Garantia Salarial' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_novo_garantia_salarial' maxlength='12' size='15' title='Digite o Novo Garantia Salarial' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_novo_salario(4)" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_perc_garantia_salarial' maxlength='12' size='15' title='% de Garantia Salarial' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE' align='left'>
            <b>Sal�rio Total (PD + PF):</b>
        </td>
        <td bgcolor='#CECECE'>
            <input type='text' name='txt_salario_total' value='<?=number_format($salario_total, 2, ',', '.');?>' maxlength='12' size='15' title='Sal�rio Total' class='textdisabled' disabled>
        </td>
        <td bgcolor='#CECECE'>
            <input type='text' name='txt_novo_salario_total' maxlength='12' size='15' title='Novo Sal�rio Total' class='textdisabled' disabled>
        </td>
        <td bgcolor='#CECECE'>
            <input type='text' name='txt_total_perc_salario' maxlength='12' size='15' title='% Total de Aumento de Sal�rio' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_novo_salario_pd.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>