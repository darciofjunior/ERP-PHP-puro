<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/validacoes.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/utilitarios/emails/emails.php', '../../../../../');

sleep(1);
$clientes_e_contatos        = implode(',', $_POST['cmb_cliente_e_contato_selecionado']);
//Transformo em vetor, para fazer a separação do que é Cliente com o que é Contato
$vetor_clientes_e_contatos  = explode(',', $clientes_e_contatos);//Transforma em Vetor
//Disparo do Vetor
for($i = 0; $i < count($vetor_clientes_e_contatos); $i++) {
    if(substr($vetor_clientes_e_contatos[$i], 0, 1) == 'C') {//Significa que é Contato
        $contatos_selecionados.= substr($vetor_clientes_e_contatos[$i], 1, strlen($vetor_clientes_e_contatos[$i])).', ';
    }else {
        $clientes_selecionados.= $vetor_clientes_e_contatos[$i].', ';
    }
}

//Cliente(s) Selecionado(s) ...
if(strlen($clientes_selecionados) > 0) {
    $clientes_selecionados  = substr($clientes_selecionados, 0, strlen($clientes_selecionados) - 2);
    $criando_email_loop     = '';

    $sql = "SELECT razaosocial, email 
            FROM `clientes` 
            WHERE `id_cliente` IN ($clientes_selecionados) 
            AND `ativo` = '1' ORDER BY razaosocial ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        $retorno = validacoes::validar($campos[$i]['email'], 'email');
        if($retorno == 1) {
            $razoes_validas = $razoes_validas.$campos[$i]['razaosocial'].'; ';
/*Aqui eu verifico se o Campo de e-mail, possui ; ou , caso exista então eu tenho que retirar estes p/
que não dê problema na hora de enviar o e-mail no Mega-Mail*/
            for($j = 0; $j < strlen($campos[$i]['email']); $j++) {
                if(substr($campos[$i]['email'], $j, 1) == ';' || substr($campos[$i]['email'], $j, 1) == ',') {
                    $emails_validos_megamail.= $criando_email_loop." \n";
//Após adicionar o e-mail em que eu acabei de compor, eu limpo essa variável p/ poder armazenar outro e-mail
                    $criando_email_loop = '';
                }else {
//Enquanto não for ; ou , eu vou compondo o e-mail normalmente ...
                    $criando_email_loop.= substr($campos[$i]['email'], $j, 1);
                }
            }
//Aqui eu adiciono o último e-mail q não foi adicionado a lista de e-mails ...
            $emails_validos_megamail.= $criando_email_loop." \n";
//Após adicionar o e-mail em que eu acabei de compor, eu limpo essa variável p/ poder armazenar outro e-mail
            $criando_email_loop = '';
            $emails_validos_kmail.= $campos[$i]['email']."; ";
            $achou_arroba = 0;
//Aqui gera uma lista dos e-mails q estão inválidos
        }else {
            $invalidos.= $campos[$i]['razaosocial'].' ('.$campos[$i]['email'].'); ' . "\n";
        }
    }
}

//Contato(s) Selecionado(s)
if(strlen($contatos_selecionados) > 0) {
    $contatos_selecionados = substr($contatos_selecionados, 0, strlen($contatos_selecionados) - 2);

    $sql = "SELECT CONCAT(cc.nome, ' (', c.razaosocial, ') ') AS dados, cc.email 
            FROM `clientes_contatos` cc 
            INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
            WHERE cc.`id_cliente_contato` IN ($contatos_selecionados) ORDER BY nome ";
    $campos_contatos = bancos::sql($sql);
    $linhas_contatos = count($campos_contatos);
    for($j = 0; $j < $linhas_contatos; $j++) {
        $retorno = validacoes::validar($campos_contatos[$j]['email'], 'email');
        if($retorno == 1) {
            $contatos_validos = $contatos_validos.$campos_contatos[$j]['dados'].'; ';
            $emails_validos_megamail.= $campos_contatos[$j]['email']." \n";
            $emails_validos_kmail.= $campos_contatos[$j]['email'].", ";
            $achou_arroba = 0;
//Aqui gera uma lista dos e-mails q estão inválidos
        }else {
            $invalidos.= $campos_contatos[$j]['dados'].' ('.$campos_contatos[$j]['email'].'); ' . "\n";
        }
    }
}
/***************Aqui eu faço um tratamento para não exibir ; no fim da linha***************/
//Válidos
$contatos_validos           = substr($contatos_validos, 0, strlen($contatos_validos) - 3);
$razoes_validas             = substr($razoes_validas, 0, strlen($razoes_validas) - 3);
$emails_validos_megamail    = substr($emails_validos_megamail, 0, strlen($emails_validos_megamail) - 2);
$emails_validos_kmail       = substr($emails_validos_kmail, 0, strlen($emails_validos_kmail) - 2);
//Inválidos
$invalidos                  = substr($invalidos, 0, strlen($invalidos) - 2);
/******************************************************************************************/
?>
<html>
<title>.:: Lista de E-mail(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Lista de E-mail(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Lista de e-mail(s) Mega Mail:
        </b>
    </tr>
    <tr class='linhanormal'>
        <td>
            <textarea name='txt_lista_email' title='Lista de E-mail(s)' cols='80' rows='5' class='caixadetexto'><?=$emails_validos_megamail;?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Lista de e-mail(s): Outlook / kmail</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <textarea name='txt_lista_email' title='Lista de E-mail(s)' cols='80' rows='5' class='caixadetexto'><?=$emails_validos_kmail;?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Lista de e-mail(s) inválidos:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <textarea name='txt_lista_email' title='Lista de E-mail(s)' cols='80' rows='5' class='caixadetexto'><?
                if(empty($invalidos)) {
                    echo '&nbsp;';
                }else {
                    echo $invalidos;
                }
            ?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Lista de e-mail(s) Possivelmente Válidos:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href="mailto:<?=$emails_validos_kmail?>" class='link'>CLIQUE AQUI PARA TRANSPORTAR -  OUTLOOK / KMAIL !!!</a>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>