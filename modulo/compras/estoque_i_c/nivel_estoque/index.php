<?
require('../../../../lib/segurancas.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
?>
<html>
<head>
<title>.:: Nível de Estoque PI ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
</head>
<frameset rows='*, 10%'>
    <!--O comando urlenconde, garante o envio de alguns caracteres especiais ao submeter como o % se este for digitado pelo usuário ...-->
    <frame name='itens' src='itens.php?txt_referencia=<?=urlencode($_POST['txt_referencia']);?>&txt_discriminacao=<?=urlencode($_POST['txt_discriminacao']);?>&txt_numero_cotacao=<?=$_POST['txt_numero_cotacao'];?>&chkt_todos_baixos_medios=<?=$_POST['chkt_todos_baixos_medios'];?>&chkt_somente_itens_com_estoque_maior_zero=<?=$_POST['chkt_somente_itens_com_estoque_maior_zero'];?>' frameborder='no'>
    <frame name='rodape' src='rodape.php' frameborder='no' scrolling='no' noresize='yes'>
</frameset>
</html>
<?
}else {
    //Esse arquivo que chama o menu tem que ficar aqui e não lá em cima, por causa que os frames do passo 1 acima, se perdem ...
    require('../../../../lib/menu/menu.php');
?>
<html>
<head>
<title>.:: Nível de Estoque PI ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function controle_todos_baixos_medios() {
    if(document.form.chkt_todos_baixos_medios.checked == true) {
        //Limpo os campos ...
        document.form.txt_referencia.value      = ''
        document.form.txt_discriminacao.value   = ''
        document.form.txt_numero_cotacao.value  = ''
        //Desabilito os campos ...
        document.form.txt_referencia.disabled       = true
        document.form.txt_discriminacao.disabled    = true
        document.form.txt_numero_cotacao.disabled   = true
        //Coloco o Layout de Desabilitado ...
        document.form.txt_referencia.className      = 'textdisabled'
        document.form.txt_discriminacao.className   = 'textdisabled'
        document.form.txt_numero_cotacao.className  = 'textdisabled'
    }else {
        //Habilito os campos ...
        document.form.txt_referencia.disabled       = false
        document.form.txt_discriminacao.disabled    = false
        document.form.txt_numero_cotacao.disabled   = false
        //Coloco o Layout de Habilitado ...
        document.form.txt_referencia.className      = 'caixadetexto'
        document.form.txt_discriminacao.className   = 'caixadetexto'
        document.form.txt_numero_cotacao.className  = 'caixadetexto'
        //Coloco foco no primeiro campo ...
        document.form.txt_referencia.focus()
    }
}
</Script>
</head>
<body onload='document.form.txt_discriminacao.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Nível de Estoque PI
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            Referência
        </td>
        <td width='20%'>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discrimina&ccedil;&atilde;o
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º da Cotação (Itens c/ Dados Atuais)
        </td>
        <td>
            <input type='text' name='txt_numero_cotacao' title='Digite o N.º da Cotação (Itens c/ Dados Atuais)' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_todos_baixos_medios' title='Todos os Baixos / Médios' id='label1' onclick='controle_todos_baixos_medios()' class='checkbox'>
            <label for='label1'>Todos os Baixos / Médios</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_itens_com_estoque_maior_zero' title='Somente itens com Estoque > 0' id='label2' class='checkbox'>
            <label for='label2'>Somente itens com Estoque > 0</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' onclick='document.form.txt_referencia.focus()' title='Limpar' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_consultar_cotacao' value='Consultar Cotação' title='Consultar Cotação' onclick="html5Lightbox.showLightbox(7, '../../../classes/cotacao/consultar.php')" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

<b>* Só não traz P.I(s) do Tipo PRAC</b>
</pre>
<?}?>