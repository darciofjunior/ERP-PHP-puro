<?
require('../../lib/comunicacao.php');
//Site em que est� hospedado as NFes e Danfes do(s) Cliente(s)
$host = mysql_connect('187.45.196.216', 'grupoalbafer1', 'd4rc10');
mysql_select_db('grupoalbafer1', $host);
if(empty($indice)) $indice = 0;

function descriptografia($string) {
	for($i = 0; $i < 5; $i++) $string = base64_decode(strrev($string));
	return $string;
}

//Busca todos os Clientes Ativos cadastrados no ERP ...
$sql = "SELECT count(l.id_cliente) total_registro 
		FROM `logins` l 
		INNER JOIN `clientes` c ON c.id_cliente = l.id_cliente AND c.email <> '' 
		WHERE l.id_cliente <> '0' 
		AND l.ativo = '1' ";
$campos_total = mysql_query($sql);
$total_registro = mysql_result($campos_total, 0, 'total_registro');

//P/ n�o ficar em loop infinito ...
if($indice > $total_registro) exit;

//Lista os Clientes cadastrados no Portal que possuem e-mail ...
$sql = "SELECT c.cliente, c.email, l.login, l.senha 
		FROM `logins` l 
		INNER JOIN `clientes` c ON c.id_cliente = l.id_cliente AND c.email <> '' 
		WHERE l.id_cliente <> '0' 
		AND l.ativo = '1' LIMIT $indice, 50 ";
$campos = mysql_query($sql);
$linhas = mysql_num_rows($campos);
for($i = 0; $i < $linhas; $i++) {
    $texto= '<br><img src="http://www.grupoalbafer.com.br/portal/images/Logo Grupo Albafer 50.jpg" width="150">';
    $texto.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <img src="http://www.grupoalbafer.com.br/portal/images/Logo Cabri.jpg" width="80" height="80">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <img src="http://www.grupoalbafer.com.br/portal/images/Logo Heinz.jpg" width="200">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <img src="http://www.grupoalbafer.com.br/portal/images/Logo NVO.jpg" width="200">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <img src="http://www.grupoalbafer.com.br/portal/images/Logo Tool.jpg" width="200">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <img src="http://www.grupoalbafer.com.br/portal/images/Logo Warrior.jpg" width="200"><br><br><br>';
    $texto.= 'Ol� <b>'.strtoupper(mysql_result($campos, $i, 'cliente')).'</b> !<br><br>';
    $texto.= 'Seja bem vindo ao Portal do Grupo Albaf�r ! <a href="http://www.grupoalbafer.com.br/portal">www.grupoalbafer.com.br/portal</a><br><br>';
    $texto.= 'Aqui voc� poder� consultar e baixar seus arquivos XML <b>(NFe)</b>.<br><br>';
    $texto.= 'Voc� est� recebendo Login e Senha para seu primeiro acesso - Login: <b>'.mysql_result($campos, $i, 'login').'</b> e Senha: <b>'.descriptografia(mysql_result($campos, $i, 'senha')).'</b>.<br><br>';
    $texto.= '<b>Importante: </b>Para sua comodidade, solicitamos a altera��o de Login e Senha logo ap�s o primeiro acesso.<br><br>';
    $texto.= '<b>O login dever� ser um e-mail de sua prefer�ncia.</b><br><br>';
    $texto.= 'Em caso de d�vida entrar em contato atrav�s do e-mail: portal@grupoalbafer.com.br.<br><br>';
    $texto.= 'Atenciosamente<br>';
    $texto.= 'Grupo Albaf�r';
    $texto.= '<br><a href="http://www.grupoalbafer.com.br">www.grupoalbafer.com.br</a><br><br>';
    $headers = "MIME-Version: 1.0\r\n";
    $headers.= "Content-type: text/html; charset=iso-8859-1\r\n";
    $headers.= "From: portal@grupoalbafer.com.br\r\n";
    comunicacao::email('portal@grupoalbafer.com.br', mysql_result($campos, $i, 'email'), '', 'Nota Fiscal Eletr�nica - Grupo Albafer', $texto);
}
sleep(60);
?>
<Script Language = 'JavaScript'>
//Aqui eu j� passo o �ndice do pr�ximo ...
	window.location = '<?=$PHP_SELF;?>?indice=<?=$indice+=50;?>'
</Script>