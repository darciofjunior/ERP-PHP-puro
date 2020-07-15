<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/validacoes.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/utilitarios/emails/emails.php', '../../../../../');

$representantes = implode(',', $_POST['cmb_representante_selecionado']);

$sql = "SELECT nome_fantasia, email 
        FROM `representantes` 
        WHERE `id_representante` IN ($representantes) 
        AND `ativo` = '1' ORDER BY nome_fantasia ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $retorno = validacoes::validar($campos[$i]['email'], 'email');
    if($retorno == 1) {
        $razoes_validas = $razoes_validas.$campos[$i]['nome_fantasia'].';';
        $emails_validos.= str_replace(';', chr(13), $campos[$i]['email'])." \n";
        $emails_validos_kmail.= $campos[$i]['email']."; ";
//Aqui gera uma lista dos e-mails q estão inválidos
    }else {
        $invalidos = $invalidos.$campos[$i]['nome_fantasia'].' ('.$campos[$i]['email'].');' . "\n";
    }
}
/***************Aqui eu faço um tratamento para não exibir ; no fim da linha***************/
//Válidos
$razoes_validas         = substr($razoes_validas, 0, strlen($razoes_validas) - 3);
$emails_validos         = substr($emails_validos, 0, strlen($emails_validos) - 2);
$emails_validos_kmail   = substr($emails_validos_kmail, 0, strlen($emails_validos_kmail) - 2);
//Inválidos
$invalidos              = substr($invalidos, 0, strlen($invalidos) - 2);
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
            <textarea name='txt_lista_email' title='Lista de E-mail(s)' cols='80' rows='5' class='caixadetexto'><?=$emails_validos;?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Lista de e-mail(s) Outlook / kmail:</b>
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
            <a href="mailto:<?=$emails_validos_kmail;?>" class='link'>CLIQUE AQUI PARA TRANSPORTAR -  OUTLOOK / KMAIL !!!</a>
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