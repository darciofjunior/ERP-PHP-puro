<?
require('../segurancas.php');
session_start('funcionarios');
/********************************Significa que o Sistema expirou o Tempo********************************/
if(!empty($_GET['destruir_sessao'])) {
    /*******************************************Sess�o*******************************************/
    /*Aqui eu guardo a �ltima URL que o usu�rio estava acessando dentro do ERP at� cair a Sess�o, para que est� possa 
    ser restaurada num pr�ximo Login ...*/
    if(strpos($_GET[url], 'mural') === false && strpos($_GET[url], 'relogio_sessao') === false) {
        $sql = "UPDATE `logins` SET `ultima_url_acessada` = '".strchr(str_replace('|', '&', $_GET[url]), '/erp/')."' WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        var endereco = '<?=$_SERVER['HTTP_HOST'];?>'
        window.top.parent.location = 'http://'+endereco+'/erp/albafer/default.php?deslogar=s&valor=3&largura='+screen.width
    </Script>
<?
    exit;
}
/*******************************************************************************************************/
if(!empty($_GET['renovar_sessao'])) {//Aqui � o Procedimento Normal ...
    $_SESSION['ultimo_acesso']      = date('Y-m-d H:i:s');//O usu�rio optou em fazer com que a Sess�o reassumisse o tempo atual ...
    $_GET['ultima_hora_acessada']   = substr($_SESSION['ultimo_acesso'], 11, 8);
}
$hora_logada    = strtotime($_GET['ultima_hora_acessada']);
$hora_atual     = strtotime(date('H:i:s'));
$tempo_logado   = mktime(date('H', $hora_atual) - date('H', $hora_logada), date('i', $hora_atual) - date('i', $hora_logada), date('s', $hora_atual) - date('s', $hora_logada));
?>
<head>
<meta http-equiv='Refresh' content="20;URL=<?=$PHP_SELF.'?ultima_hora_acessada='.$_GET['ultima_hora_acessada'];?>">
</head>
<body topmargin='7' background='#004000'>
<img src='/erp/albafer/imagem/relogio.png' id='img_relogio' onclick="window.location = '<?=$PHP_SELF.'?renovar_sessao=S';?>'" width="18" height="18">
<!--****************Aqui eu pego a URL da P�gina Principal - que est� na Barra de Ferramentas****************-->
<input type='hidden' id='url'>
<?
    $color  = (date('H:i:s', $tempo_logado) > '00:09:00') ? 'red' : '#cccccc';
?>
<font color='<?=$color;?>' face='arial, verdana, sans-serif' size='2px'><b>
    <?=date('H:i:s', $tempo_logado).' s';?>
</b></font>
</body>
<Script Language = 'JavaScript'>
    document.getElementById('url').value = parent.location
    /*Se n�o substituir os & por |, o sistema perde a maioria dos par�metros a partir do caracter & na hora em que eu passo esse 
    objeto por par�metro mais abaixo ...*/
    document.getElementById('url').value = document.getElementById('url').value.replace('&', '|')
    var tempo_logado = '<?=date('H:i:s', $tempo_logado);?>'
    //Se o usu�rio ficou + de 10 minutos sem mexer, destr�i a Sess�o do Sistema ...

    //Comentei essa parte da Sess�o na M�quina de Teste porque atrapalha muito, quando estamos simulando as log�sticas ...
    //if(tempo_logado > '00:10:00') window.location = '<?=$PHP_SELF;?>'+'?destruir_sessao=S&url='+document.getElementById('url').value
</Script>