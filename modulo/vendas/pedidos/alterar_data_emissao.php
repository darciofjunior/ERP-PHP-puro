<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../');

if(!empty($_POST['id_pedido_venda'])) {
    //Busco a Data de Emiss�o Antiga do Pedido antes de sua altera��o realizada mais abaixo ...
    $sql = "SELECT DATE_FORMAT(data_emissao, '%d/%m/%Y') AS data_emissao_anterior 
            FROM `pedidos_vendas` 
            WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $data_emissao_anterior  = $campos[0]['data_emissao_anterior'];
    //Significa que realmente houve mudan�a de Data de Emiss�o e sendo assim fa�o toda uma prepara��o p/ enviar e-mail ...
    if($data_emissao_anterior != $_POST['txt_data_emissao']) {
        //Busco o nome do Funcion�rio que fez a altera��o da Data de Emiss�o atrav�s do Link ...
        $sql = "SELECT nome 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $funcionario_alterou    = $campos[0]['nome'];
        //Aqui o sistema envia um e-mail p/ o Roberto informando quem e quando fez a Altera��o dessa Data de Emiss�o ...
        $texto = 'O funcion�rio <b>'.$funcionario_alterou.'</b>, alterou a Data de Emiss�o do Pedido de Venda N.� <b>'.$_POST[id_pedido_venda].'</b> de <b>'.$data_emissao_anterior.'</b> p/ <b>'.$_POST['txt_data_emissao'].'</b>, atrav�s do "Link" que fica no Alterar Cabe�alho �s <b>'.date('d/m/Y').'</b>.';
        comunicacao::email('ERP - GRUPO ALBAFER', 'roberto@grupoalbafer.com.br', '', 'Altera��o de Data de Emiss�o - Pedido de Venda', $texto);
    }
    //Atualizo o Pedido de Vendas com a nova Data de Emiss�o digitada pelo usu�rio ...
    $data_emissao   = data::datatodate($_POST['txt_data_emissao'], '-');
    $sql = "UPDATE `pedidos_vendas` SET `data_emissao` = '$data_emissao' WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('DATA DE EMISS�O DO PEDIDO ALTERADA COM SUCESSO !')
        window.opener.document.form.submit()
        window.close()
    </Script>
<?
}

//Aqui traz os dados do pedido
$sql = "SELECT DATE_FORMAT(data_emissao, '%d/%m/%Y') AS data_emissao 
        FROM `pedidos_vendas` 
        WHERE `id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Data de Emiss�o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Data de Emiss�o
    if(!data('form', 'txt_data_emissao', '4000', 'EMISS�O')) {
        return false
    }
}
</Script>
</head>
<body onload="document.form.txt_data_emissao.focus()">
<form name="form" method="post" action='' onSubmit="return validar()">
<input type='hidden' name="id_pedido_venda" value="<?=$_GET['id_pedido_venda'];?>">
<table width='80%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Data de Emiss�o - Pedido
            <font color='yellow'>
                <?=$_GET['id_pedido_venda'];?>
            </font>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Data de Emiss�o:</b>
        </td>
        <td>
            <input type="text" name="txt_data_emissao" value="<?=$campos[0]['data_emissao'];?>" title="Digite a Data de Emiss�o" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            &nbsp;<img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'LIMPAR');" id="cmd_redefinir" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>