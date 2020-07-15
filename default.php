<?
require('lib/segurancas.php');

$mensagem[1] = 'LOGIN INEXISTENTE OU FUNCIONÁRIO AFASTADO !';
$mensagem[2] = 'SENHA INVÁLIDA !';
$mensagem[3] = 'SESSÃO EXPIRADA POR FALTA DE USO !!! FAVOR LOGAR NOVAMENTE !';
$mensagem[4] = 'USUÁRIO SEM PERMISSÃO AO ENDEREÇO DA PÁGINA !!! ACESSO RESTRITO !';
$mensagem[5] = 'USUÁRIO BLOQUEADO !!! EXCEDIDO O NÚMERO DE TENTATIVAS PARA LOGAR NO SISTEMA !';

if(!empty($_POST[txt_login]) && !empty($_POST[txt_senha])) segurancas::autentica($_POST[txt_login], $_POST[txt_senha], $_POST[hdd_screen_width]);
?>
<html>
<head>
<title>.:: GRUPO ALBAFER (ERP) - Enterprise Resource Planning ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'Cache-Control' content = 'no-store'>
<meta http-equiv = 'Pragma' content = 'no-cache'>
<link rel = 'shortcut icon' href = 'imagem/albafer.ico'>
<link href = 'css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = 'js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Login ...
    if(!texto('form', 'txt_login', '3', 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ-_', 'LOGIN ', '2')) {
        return false
    }
//Senha ...
    if(!texto('form', 'txt_senha', '3', 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ-_0123456789', 'SENHA ', '1')) {
        return false
    }
/*********************Controle Scan ERP*********************/
    if(document.form.hdd_scan_erp.value == 1) {//Significa que o SCAN ERP ainda não foi executado no dia de Hoje ...
        document.getElementById('div_scan_erp').style.visibility = 'visible'//Torno a DIV visível p/ que o usuário enxergue a Mensagem de "SCAN ERP" ...
    }
/***********************************************************/
    document.form.hdd_screen_width.value = screen.width
}

function checar_caps_lock(ev) {
    var e = ev || window.event;
    codigo_tecla = e.keyCode ? e.keyCode : e.which
    tecla_shift = e.shiftKey ? e.shiftKey : ((codigo_tecla == 16) ? true : false)
    if(((codigo_tecla >= 65 && codigo_tecla <= 90) && !tecla_shift) || ((codigo_tecla >= 97 && codigo_tecla <= 122) && tecla_shift)) {
        document.getElementById('lbl_caps_lock').style.visibility = 'visible'
    }else {
        document.getElementById('lbl_caps_lock').style.visibility = 'hidden'
    }
}
</Script>
</head>
<body onload='document.form.txt_login.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<br>
<center>
    <img src='imagem/marcas/Logo Grupo Albafer.jpg' width='140'>
</center>
<br>
<table width='50%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='erro' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <font color='yellow'>
                Bem Vindo ao GRUPO ALBAFER 
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='377' align='right'>
            <font color='darkblue'>
                <b>LOGIN:</b>
            </font>
        </td>
        <td width='377'>
            <input type='text' name='txt_login' title='Digite o Login' size='25' maxlength='15' onkeypress="checar_caps_lock(event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td align='right'>
            <font color='darkblue'>
                <b>SENHA:</b>
            </font>
        </td>
        <td>
            <input type='password' name='txt_senha' title='Digite a Senha' size='25' maxlength='15' onkeypress="checar_caps_lock(event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_logar' value='Logar' title='Logar' style='color:green' class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick='document.form.txt_login.focus()' class='botao'>
        </td>
    </tr>
</table>
<br>
<center>
    <label id='lbl_caps_lock' border='1' style='visibility: hidden'>
        <img src='imagem/atencao.jpg' width='45' height='45'>&nbsp;
        <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='6' color='red'>
            <blink><b>CAPS LOCK LIGADO !!!</b></blink>
        </font>
    </label>
</center>
<center>
    <img src='imagem/marcas/Logo Cabri.jpg' width='80' height='80'>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <img src='imagem/marcas/Logo Heinz.jpg' width='200'>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <img src='imagem/marcas/Logo NVO.jpg' width='200'>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <img src='imagem/marcas/Logo Tool.jpg' width='200'>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <img src='imagem/marcas/Logo Warrior.jpg' width='200'>
    <?
        /*Verifico se existe algum "SCAN ERP" agendado p/ rodar na Data de Hoje ...
    
        Obs: Só levo em consideração as funções principais das "06:00" que são rodadas pelo horário da manhã que no caso são chamadas apenas 
        uma única vez por dia, existem outras tais como "cadastrar_contas_automaticas" mais é já é desnecessário levar em conta por rodar 
        mais de uma vez ...*/
        $sql = "SELECT `id_scan_erp` 
                FROM `scans_erps` 
                WHERE `data` = '".date('Y-m-d')."' 
                AND `hora` = '06:00:00' LIMIT 1 ";
        $campos_scan_erp = bancos::sql($sql);
        $linhas_scan_erp = count($campos_scan_erp);
        if($linhas_scan_erp == 1) {//Encontrou pelo menos 1 "SCAN ERP" p/ ser rodado no dia de Hoje ...
    ?>
        <br/><br/>
        <div id='div_scan_erp' style='visibility:hidden'>
            <img src = 'css/little_loading.gif' width='60' height='60'/>
            &nbsp;
            <font size='6' color='brown'>
                <b>RODANDO FUNÇÃO SCAN ERP
                <br/>AGUARDE 1 MINUTO ...</b>
            </font>
        </div>
    <?
        }
    ?>
</center>
<br/>
<!--Esse objeto define o tamanho do Menu na próxima tela para o usuário depois que este logou no Sistema ...-->
<input type='hidden' name='hdd_screen_width'>
<!--Variável de controle p/ mostrar a Mensagem de "SCAN ERP" p/ o usuário que está na Div Invisível, 
caso seja o 1º Acesso do dia ...-->
<input type='hidden' name='hdd_scan_erp' value='<?=$linhas_scan_erp?>'>
</form>
</body>
</html>