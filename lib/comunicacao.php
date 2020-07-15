<?
require('email/class.phpmailer.php');

class comunicacao {
    function email($nome_completo, $para_email, $cc, $assunto, $mensagem, $cco, $caminho_arquivo, $arquivo) {
        return false;//Comenta aqui para não ficar mandando e-mail na máquina de Teste ...
        //Inicia a classe PHPMailer ...
        $mail = new PHPMailer();
 
        //Define os dados do servidor e tipo de conexão
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        $mail->IsSMTP(); // Define que a mensagem será SMTP
        $mail->Host     = "smtp.grupoalbafer.com.br";//Endereço do servidor SMTP (caso queira utilizar a autenticação, utilize o host smtp.seudomínio.com.br) ...
        $mail->SMTPAuth = true;//Usar autenticação SMTP (obrigatório para smtp.seudomínio.com.br) ...
        $mail->Username = 'erp@grupoalbafer.com.br';//Usuário do servidor SMTP (endereço de email) ...
        $mail->Password = '123mudar';//Senha do servidor SMTP (senha do email usado) ...
 
        //Define o remetente
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        if(strpos($nome_completo, '@') === false) {//Nesse caso não existe o @ no e-mail ...
            //$para_email.=     ', darcio@grupoalbafer.com.br';
            $mail->From         = 'erp@grupoalbafer.com.br';
            $mail->Sender       = 'erp@grupoalbafer.com.br';
            $mail->FromName 	= 'GRUPO ALBAFER';
        }else {//Significa que existe ...
            $mail->From         = $nome_completo;
            $mail->Sender       = 'erp@grupoalbafer.com.br';
            $mail->FromName 	= strtok($nome_completo, '@');
        }
        
        //Define os destinatário(s)
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        $para_email		= str_replace(',', ';', $para_email);
        $emailList 		= explode(';', $para_email);
        if(is_array($emailList)) {
            foreach($emailList as $email_para) {
                if(!empty($email_para)) {
                    $mail->AddAddress(trim($email_para));//envia email
                }
            }
        }else {
            if(!empty($para_email)) {
                $mail->AddAddress(trim($para_email));//envia email
            }
        }

        //Define as cópia(s)
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        $cc             = str_replace(',', ';', $cc);
        $emailList_cc 	= explode(';', $cc);
        if(is_array($emailList_cc)) {
            foreach($emailList_cc as $email_cc) {
                if(!empty($email_cc)) $mail->AddAddress(trim($email_cc));//envia email
            }
        }else {
            if(!empty($cc)) $mail->AddAddress(trim($cc));//envia email
        }

        //Define as cópia(s) oculta(s)
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        $cco            = str_replace(',', ';', $cco);
        $emailList_cco 	= explode(';', $cco);
        if(is_array($emailList_cco)) {
            foreach($emailList_cco as $email_cco) {
                if(!empty($email_cco)) $mail->AddBCC(trim($email_cco));//envia email
            }
        }else {
            if(!empty($cco)) $email_cco->AddBCC(trim($cco));//envia email
        }
 
        //Define os dados técnicos da Mensagem
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        $mail->IsHTML(true); // Define que o e-mail será enviado como HTML
        //$mail->CharSet = 'iso-8859-1'; // Charset da mensagem (opcional)
 
        //Define a mensagem (Texto e Assunto)
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        $mail->Subject  = $assunto;//Assunto da mensagem ...
        $mail->Body     = $mensagem;//Este é o corpo da mensagem em HTML ...
 
        //Define os anexos (opcional)
        //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
        if(!empty($arquivo)) $mail->AddAttachment($caminho_arquivo, $arquivo);//Insere um anexo ...

        //Envia o e-mail ...
        $enviado = $mail->Send();

        //Limpa os destinatários e os anexos ...
        $mail->ClearAllRecipients();
        $mail->ClearAttachments();
 
        //Exibe uma mensagem de resultado ...
        /*if($enviado) {
            echo "E-mail enviado com sucesso!";
        }else {
            echo "Não foi possível enviar o e-mail.";
            echo "Informações do erro: " . $mail->ErrorInfo;
        }*/
    }
}
?>