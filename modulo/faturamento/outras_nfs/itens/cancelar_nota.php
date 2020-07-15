<?
require('../../../../lib/segurancas.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

if(!empty($_POST['txt_observacao_justificativa'])) {
//1) Deleta os Itens da Nota Fiscal ...
    $sql = "DELETE FROM `nfs_outras_itens` WHERE `id_nf_outra` = '$_POST[id_nf_outra]' ";
    bancos::sql($sql);
//Aki atualiza a Tabela de Nota Fiscal
    $data_sys = date('Y-m-d H:i:s');
    $sql = "UPDATE `nfs_outras` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '$data_sys' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    bancos::sql($sql);
//3) E-mail ...
/*****************************************E-mail*****************************************/
/*Se o Usuário estiver cancelando a Nota Fiscal, então o Sistema dispara um e-mail informando qual a 
Nota que está sendo cancelada*/
    $data_ocorrencia = date('Y-m-d H:i:s');
    $txt_justificativa = '<font color="blue">Follow-Up Registrado automaticamente (E-mail) </font>';
//Aqui eu trago alguns dados de Nota Fiscal p/ passar por e-mail via parâmetro ...
    $sql = "SELECT nfso.id_cliente_contato, nfso.id_empresa, c.razaosocial 
            FROM `nfs_outras` nfso 
            INNER JOIN `clientes` c ON c.id_cliente = nfso.id_cliente 
            WHERE nfso.`id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
    $id_empresa_nota    = $campos[0]['id_empresa'];
    $empresa            = genericas::nome_empresa($id_empresa_nota);
    $cliente            = $campos[0]['razaosocial'];
    $id_cliente_contato = $campos[0]['id_cliente_contato'];
    $numero_nf          = faturamentos::buscar_numero_nf($_POST['id_nf_outra'], 'O');
//Dados p/ enviar por e-mail - Controle com as Mensagens de Alteração ...
    $observacao_follow_up = $txt_justificativa.' - Cancelamento de Nota Express - <b>Justificativa: </b>'.$_POST['txt_observacao_justificativa'];
/***********************************E-mail***********************************/
//Aqui eu busco o login de quem está excluindo a Conta ...
    $sql = "SELECT `login` 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos_login       = bancos::sql($sql);
    $login_cancelando   = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
    $complemento_justificativa = '<br><b>Empresa: </b>'.$empresa.' <br><b>Cliente: </b>'.$cliente.' <br><b>N.º da Conta: </b>'.$numero_nf.' <br><b>Login: </b>'.$login_cancelando;
    $txt_justificativa.= $complemento_justificativa.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$_POST['txt_observacao_justificativa'].'<br>'.$PHP_SELF;
//Aqui eu mando um e-mail informando quem e porque que exclui a Conta à Receber ...
    $destino    = $cancelar_nota_fiscal;
    $mensagem   = $txt_justificativa;
    comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Cancelamento de Nota Express', $mensagem);
?>
    <Script Language = 'JavaScript'>
        alert('NOTA FISCAL CANCELADA COM SUCESSO !')
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
        window.close()
    </Script>
<?
}
?>
<html>
<head>
<title>.:: Cancelar Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Observação / Justificativa ...
    if(document.form.txt_observacao_justificativa.value == '') {
        alert('DIGITE A OBSERVAÇÃO / JUSTIFICATIVA !')
        document.form.txt_observacao_justificativa.focus()
        document.form.txt_observacao_justificativa.select()
        return false
    }
}
</Script>
</head>
<body onload="document.form.txt_observacao_justificativa.focus()">
<form name='form' method="post" action='' onSubmit="return validar()">
<input type='hidden' name='id_nf_outra' value="<?=$_GET['id_nf_outra'];?>">
<table width='90%' border="0" cellspacing ='1' cellpadding='1' align="center">
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Cancelar Nota Fiscal
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Observação / Justificativa:</b>
        </td>
        <td>
            <textarea name='txt_observacao_justificativa' cols='85' rows='3' maxlength='255' class='caixadetexto'><?=$txt_observacao_justificativa;?></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'outras_opcoes.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'" class="botao">
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_observacao_justificativa.focus()" style="color:#ff9900" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>