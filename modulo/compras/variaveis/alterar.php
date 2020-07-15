<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/custos.php');
require('../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/compras/variaveis/variaveis.php', '../../../');

if(!empty($_POST['txt_valor'])) {
//Atualizando as vari·veis ...
    $sql = "UPDATE `variaveis` SET `valor` = '$_POST[txt_valor]', `opcao` = '$_POST[txt_opcao]', `modulo_obs` = '$_POST[txt_modulo_obs]' WHERE `id_variavel` = '$_POST[id_variavel]' LIMIT 1 ";
    bancos::sql($sql);
/************************************PreferÍncia de Compra***********************************/
//Se a vari·vel 6 for um valor, a 47 tem de ser outro e vice-versa ...
    if($_POST['id_variavel'] == 6) {
        $valor = ($_POST['txt_valor'] == 1) ? 2 : 1;
        $sql = "UPDATE `variaveis` SET `valor` = '$valor' WHERE `id_variavel` = '47' LIMIT 1 ";
        bancos::sql($sql);
    }
//Se a vari·vel 47 for um valor, a 6 tem de ser outro e vice-versa ...
    if($_POST['id_variavel'] == 47) {
        $valor = ($_POST['txt_valor'] == 1) ? 2 : 1;
        $sql = "UPDATE `variaveis` SET `valor` = '$valor' WHERE `id_variavel` = '6' LIMIT 1 ";
        bancos::sql($sql);
    }
/********************************************************************************************/
//Se for alguma das vari·veis abaixo ...
    if($_POST['id_variavel'] == 13 || $_POST['id_variavel'] == 14 || $_POST['id_variavel'] == 15) {
        custos::localizar_maquina(); //FunÁ„o que recalcula a hora custo do funcion·rio ...
    }
/******************************Novas Comissıes Margens de Lucro******************************/
    $vetor_variaveis_novas_comissoes_margens_lucros = array(54, 55, 56, 57, 58, 88, 89);
    
    if(in_array($_POST['id_variavel'], $vetor_variaveis_novas_comissoes_margens_lucros)) vendas::calcular_comissoes();
/***********************************Fator Encargos Sociais***********************************/
/*Se a vari·vel que estiver sendo alterada for a de "Fator Encargos Sociais", ent„o preciso rodar a funÁ„o abaixo p/ 
todas as vari·veis do Sistema ...*/
    if($_POST['id_variavel'] == 63) {
        //Aqui o sistema busca todas as M·quinas "ativas" que est„o habilitadas no Sistema ...
        $sql = "SELECT `id_maquina` 
                FROM `maquinas` 
                WHERE `ativo` = '1' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) custos::custos_hora_maquina($campos[$i]['id_maquina']);//FunÁ„o que recalcula a hora custo do funcion·rio ...
    }
/********************************************************************************************/
/*Significa que essa Tela foi acessada atravÈs do menu de RelatÛrio, sendo assim o usu·rio n„o pode 
voltar da tela principal de vari·veis, redireciono o usu·rio novamente p/ a Tela de RelatÛrios*/
    if($_POST['tela_aces_por_rel'] == 1) {
?>
	<Script Language = 'Javascript'>
            alert('VARI¡VEL ALTERADA COM SUCESSO !')
            window.location = '../../vendas/relatorio/estoque_pa/rel_atualiza_grupos.php'
	</Script>
<?
//Tela acessada normalmente pela parte de vari·veis ...
    }else {
?>
	<Script Language = 'Javascript'>
            window.location = 'variaveis.php'
	</Script>
<?
    }
}else {
/************************************PreferÍncia de Compra***********************************/	
//Se a vari·vel 6 for um valor, a 47 tem de ser outro e vice-versa ...
    if($_GET['id_variavel'] == 6) {
        $sql            = "SELECT `opcao` FROM `variaveis` WHERE `id_variavel` = '47' LIMIT 1 ";
        $campos_opcao 	= bancos::sql($sql);
        $opcao_alert	= $campos_opcao[0]['opcao'];
    }
//Se a vari·vel 47 for um valor, a 6 tem de ser outro e vice-versa ...
    if($_GET['id_variavel'] == 47) {
        $sql            = "SELECT `opcao` FROM `variaveis` WHERE `id_variavel` = '6' LIMIT 1 ";
        $campos_opcao 	= bancos::sql($sql);
        $opcao_alert	= $campos_opcao[0]['opcao'];
    }
/********************************************************************************************/
    //Busca dados da Vari·vel que foi passada por par‚metro ...
    $sql = "SELECT * 
            FROM `variaveis`  
            WHERE `id_variavel` = '$_GET[id_variavel]' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $valor      = number_format($campos[0]['valor'], 4, ',', '.');
    $opcao      = $campos[0]['opcao'];
    $modulo_obs = $campos[0]['modulo_obs'];
?>
<html>
<head>
<title>.:: Alterar Vari·vel(is) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//OpÁ„o ...
    if(!texto('form', 'txt_opcao', '1', '1234567890QWERTYUIOP«LKJHGFDSAZXCVBNM zaqwsxcderfvbgtyhnmjuik,.lopÁ;·ÈÌÛ˙¡…Õ”⁄¬ Œ‘€‚ÍÓÙ˚„ı√’‹¸¿‡!@#$%®&*()(_-+π≤≥££¢¨ß™∫∞|\,.<>;:{[}]/ÿ= "', 'OP«√O', '1')) {
        return false
    }
//Valor ...
    if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
        return false
    }
/************************************PreferÍncia de Compra***********************************/
//Verifico se a vari·vel 6 ou 47 esta no mesmo valor antes de dar Update ...
    var id_variavel = eval('<?=$_GET['id_variavel'];?>')
    var opcao_alert = '<?=$opcao_alert;?>'
    if(id_variavel == 6) {
        if(eval(strtofloat(document.form.txt_valor.value)) != 1 && eval(strtofloat(document.form.txt_valor.value)) != 2) {
            alert('VALOR INV¡LIDO !!! O VALOR DIGITADO TEM DE SER 1 OU 2 !')
            document.form.txt_valor.focus()
            document.form.txt_valor.select()
            return false
        }
        if(eval(strtofloat(document.form.txt_valor.value)) == 1) {//Significa que a pref. È Albafer ...
            alert('A '+opcao_alert+' A PARTIR DE AGORA O FATURAMENTO SER¡ PELA "TOOL MASTER" !')
        }
        if(eval(strtofloat(document.form.txt_valor.value)) == 2) {//Significa que a pref. È Tool Master ...
            alert('A '+opcao_alert+' A PARTIR DE AGORA O FATURAMENTO SER¡ PELA "ALBAFER" !')
        }
    }else if(id_variavel == 47) {
        if(eval(strtofloat(document.form.txt_valor.value)) != 1 && eval(strtofloat(document.form.txt_valor.value)) != 2) {
            alert('VALOR INV¡LIDO !!! O VALOR DIGITADO TEM DE SER 1 OU 2 !')
            document.form.txt_valor.focus()
            document.form.txt_valor.select()
            return false
        }
        if(eval(strtofloat(document.form.txt_valor.value)) == 1) {//Significa que a pref. È Albafer ...
            alert('A '+opcao_alert+' A PARTIR DE AGORA SER¡ PELA "TOOL MASTER" !')
        }
        if(eval(strtofloat(document.form.txt_valor.value)) == 2) {//Significa que a pref. È Tool Master ...
            alert('A '+opcao_alert+' A PARTIR DE AGORA SER¡ PELA "ALBAFER" !')
        }
    }
/********************************************************************************************/
    limpeza_moeda('form', 'txt_valor, ')
}
</Script>
</head>
<body onload="document.form.txt_valor.focus()">
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_variavel' value='<?=$_GET['id_variavel'];?>'>
<!--Significa que essa Tela foi acessada atravÈs do menu de RelatÛrio, sendo assim o usu·rio n„o pode 
ter acesso ao bot„o voltar da tela principal de vari·veis-->
<input type='hidden' name='tela_aces_por_rel' value='<?=$_GET['tela_aces_por_rel'];?>'>
<table width='60%' cellpadding="1" cellspacing="1" align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Vari·vel(is)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>ID:</b>
        </td>
        <td>
            <font color='darkblue'>
                <b><?=$_GET['id_variavel'];?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>OpÁ„o:</b>
        </td>
        <td>
            <input type='text' name='txt_opcao' value='<?=$opcao;?>' title='Digite a OpÁ„o' maxlength='75' size='70' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor:</b>
        </td>
        <td>
            <input type='text' name='txt_valor' value='<?=$valor;?>' title='Digite o Valor' onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            MÛdulo ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_modulo_obs' cols='43' rows='2' maxlength='85' class='caixadetexto'><?=$modulo_obs;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <?
//SÛ ir· mostrar esse bot„o se essa tela for acessada da tela principal de vari·veis
                if($tela_aces_por_rel != 1) {
            ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'variaveis.php'" class='botao'>
            <?
                }
            ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_valor.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>