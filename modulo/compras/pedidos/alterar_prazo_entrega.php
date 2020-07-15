<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/consultar.php', '../../../');
$mensagem[1] = "<font class='confirmacao'>PRAZO DE ENTREGA ALTERADO COM SUCESSO.</font>";

if(!empty($_POST['txt_prazo_entrega'])) {
//Aki registra a Data e Hora em q foi feita a alteraÁ„o
    $data_sys = date('Y-m-d H:i:s');
//Verifica quem È o respons·vel pela alteraÁ„o do prazo de entrega
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $login  = $campos[0]['login'];
//Junta o que o usu·rio digitou com o login do respons·vel que est· fazendo a manipulaÁ„o
    $prazo_entrega = $_POST['txt_prazo_entrega'].'=> '.$login.' | '.$data_sys;

    $sql = "UPDATE `estoques_acabados` SET `prazo_entrega` = '$prazo_entrega' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

//Busco o Prazo de Entrega do PI que È PA "PIPA" no Estoque ...
$sql = "SELECT prazo_entrega 
        FROM `estoques_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 0) {
    $prazo_entrega  = '';
    $responsavel    = ''; 
    $data           = '';
    $hora           = '';
}else {
    $prazo_entrega  = strtok($campos[0]['prazo_entrega'], '=');

    $responsavel    = strtok($campos[0]['prazo_entrega'], '|');
    $responsavel    = substr(strchr($responsavel, '> '), 1, strlen($responsavel));

    $data_hora      = strchr($campos[0]['prazo_entrega'], '|');
    $data_hora      = substr($data_hora, 2, strlen($data_hora));
    $data           = data::datetodata(substr($data_hora, 0, 10), '/');
    $hora           = substr($data_hora, 11, 8);
//Faz esse tratamento para o caso de n„o encontrar o respons·vel
    if(empty($responsavel)) {
        $string_apresentar = '&nbsp;';
    }else {
        $string_apresentar = $responsavel.' - '.$data.' '.$hora;
    }
}
?>
<html>
<title>.:: Alterar Prazo de Entrega ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(!texto('form', 'txt_prazo_entrega', '1', '1234567890,.=-+*<>/abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ„ı√’·ÈÌÛ˙¡…Õ”⁄Á«!_ ', 'PRAZO DE ENTREGA', '2')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_prazo_entrega.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Alterar Prazo de Entrega
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Prazo de Entrega</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_prazo_entrega' value='<?=$prazo_entrega;?>' title='Prazo de Entrega' size='82' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Respons·vel</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$string_apresentar;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>