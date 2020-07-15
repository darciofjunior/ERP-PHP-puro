<?
require('email/class.phpmailer.php');

function email($nome_completo, $para_email, $cc, $assunto, $mensagem) {
	$mail = new PHPMailer();
	$mail->IsSMTP();                                	// set mailer to use SMTP
	$mail->Host 		= "smtp.grupoalbafer.com.br";  	// specify main and backup server
	$mail->SMTPAuth 	= true;   			// turn on SMTP uthentication
	$mail->Username 	= "erp@grupoalbafer.com.br";  	// SMTP username
	$mail->Password 	= "123mudar"; 			// SMTP password
	$mail->From 		= "erp@grupoalbafer.com.br";
	$mail->FromName 	= $nome_completo;
			
	$para_email		= str_replace(",",";",$para_email);
	$emailList 		= explode(";",$para_email);
	if(is_array($emailList)) {
		foreach($emailList as $email_para) {
			if(!empty($email_para)) $mail->AddAddress(trim($email_para));// envia email
		}
	} else {
		if(!empty($para_email)) $mail->AddAddress(trim($para_email));// envia email
	}
	$cc			= str_replace(",",";",$cc);
	$emailList_cc 		= explode(";",$cc);
	if(is_array($emailList_cc)) {
		foreach($emailList_cc as $email_cc) {
			if(!empty($email_cc)) $mail->AddAddress(trim($email_cc));// envia email
		}
	} else {
		if(!empty($cc)) $mail->AddAddress(trim($cc));// envia email
	}
	$mail->AddCC("erp@grupoalbafer.com.br");// envia email
	$mail->AddReplyTo("erp@grupoalbafer.com.br", "Informação");
	$mail->WordWrap = 50;                                   // set word wrap to 50 characters
	//$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
	//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
	$mail->IsHTML(true);                                 	// set email format to HTML
	$mail->Subject = $assunto;
	$mail->Body    = $mensagem;
	$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
	$mail->Send();
}
?>
<html>
<body topmargin="180">
<center>
Este site que você esta tentando visualizar não é permitido pela empresa<br> e esta sendo encaminhado para o responsável um comunicado<br> de tentativa de acesso.

<br><br><br><br>


Peço não tentar novamente.


<br><br><br><br>

<b>Não Insista !!!</b>
</center>
</body>
</html>
<?
//Autenticação no Servidor de E-mail ...
return email('erp@grupoalbafer.com.br', 'marques@grupoalbafer.com.br', '', $_SERVER['REMOTE_ADDR'].' - '.date('d/m/Y H:i:s'), 'Acesso Restrito');
?>