<?
require('../../lib/segurancas.php');
require('../../modulo/classes/array_sistema/array_sistema.php');

$mensagem[1] = "<font class='confirmacao'>GRUPO DE E-MAIL ATRELADO PARA TODO(S) FUNCIONÁRIO(S) SELECIONADO(S).</font>";
$mensagem[2] = "<font class='confirmacao'>GRUPO(S) DE E-MAIL ATRELADO(S) PARA ALGUM(NS) FUNCIONÁRIO(S) SELECIONADO(S).</font>";
$mensagem[3] = "<font class='erro'>GRUPO DE E-MAIL JÁ EXISTENTE PARA FUNCIONÁRIO(S).</font>";

if(!empty($_POST['cmb_funcionario'])) {
//Variáveis p/ controle de retorno das Mensagens ...
    $atrelados = 0;
    $nao_atrelados = 0;
    foreach($_POST['cmb_funcionario'] as $id_funcionario_loop) {
        $sql = "INSERT INTO `grupos_emails_vs_funcionarios` (`id_grupo_email_funcionario`, `id_grupo_email`, `id_funcionario`) VALUES (NULL, '$_POST[hdd_grupo_email]', '$id_funcionario_loop') ";
        bancos::sql($sql);
        $atrelados++;
    }
//Situação 1 ...
    if($atrelados > 0 && $nao_atrelados == 0) {
        $valor = 1;	
    }else if($atrelados > 0 && $nao_atrelados > 0) {//Situação 2 ...
        $valor = 2;
    }else if($atrelados == 0 && $nao_atrelados > 0) {//Situação 3 ...
        $valor = 3;
    }
}

$id_grupo_email = ($_SERVER['REQUEST_METHOD'] == 'POST') ?  $_POST['id_grupo_email'] : $_GET['id_grupo_email'];

//Vetor que armazena todos os grupos de e-mails criados, que está em outro arquivo ...
$grupos_emails = array_sistema::grupos_emails();
/*Só exibe aqui os funcionários que possuem pelo menos um Tipo de Conta de e-mail criada 
p/ poder atrelar esse à algum Grupo ...*/
$sql = "SELECT DISTINCT(f.id_funcionario), f.nome 
        FROM `funcionarios` f 
        INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo AND c.`id_cargo` <> '82' 
        WHERE f.`id_funcionario` NOT IN (1, 2, 91, 114) 
        AND (f.`status` < '3' AND f.`email_externo` <> '') OR (f.`status` = '3' AND (f.`email_externo` <> '')) 
        ORDER BY f.nome ";
$campos_funcionarios = bancos::sql($sql);
$linhas_funcionarios = count($campos_funcionarios);
?>
<html>
<head>
<title>.:: Atrelar Funcionário(s) nesse Grupo de E-mail ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var selecionados = 0
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 0; j < document.form.elements[i].length; j++) {
                if(document.form.elements[i][j].selected == true) selecionados ++
            }
            if(selecionados == 0) {
                alert('SELECIONE PELO MENOS UM FUNCIONÁRIO !')
                document.form.elements[i].focus()
                return false
            }
        }
    }
//Aqui é para não atualizar os frames abaixo desse Pop-UP
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
<body onload='document.form.elements[0].focus()' onunload="atualizar_abaixo()">
<form name='form' action='' method='post' onsubmit="return validar()">
<input type='hidden' name='hdd_grupo_email' value='<?=$id_grupo_email;?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='90%' border=0 align='center' cellspacing=1 cellpadding=1>
    <tr class="atencao" align="center">
        <td>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td>
            Atrelar Funcionário(s) nesse Grupo de E-mail
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            <font color='yellow'>
                Grupo de E-mail - 
            </font>
            <?=$grupos_emails[$id_grupo_email];?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Funcionário(s): </b>
        </td>
    </tr>
    <tr class="linhanormal" align="center">
        <td>
            <select name="cmb_funcionario[]" title="Selecione o Funcionário" size="20" multiple class="combo">
                    <option value='' style='color:red'>
                    SELECIONE
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </option>
        <?
                    for($i = 0; $i < $linhas_funcionarios; $i++) {
        ?>
                    <option value='<?=$campos_funcionarios[$i]['id_funcionario']?>'><?=$campos_funcionarios[$i]['nome']?></option>
        <?
                    }
        ?>
            </select>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.elements[0].focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_atrelar" value="Atrelar" title="Atrelar" style="color:green" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>