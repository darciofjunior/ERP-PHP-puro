<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/categorizacoes/categorizacoes.php', '../../../../../');

if(!empty($_POST['txt_perfil'])) {
    //Verifica se este Tipo de Perfil digitado pelo usuário já está cadastrado, diferente do atual passado por parâmetro ...
    $sql = "SELECT id_cliente_perfil 
            FROM `clientes_perfils` 
            WHERE `perfil` = '$_POST[txt_perfil]' 
            AND `id_cliente_perfil` <> '$_POST[id_cliente_perfil]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Perfil não existente
        $sql = "UPDATE `clientes_perfils` SET `perfil` = '$_POST[txt_perfil]', `observacao` = '$_POST[txt_observacao]' WHERE `id_cliente_perfil` = '$_POST[id_cliente_perfil]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 3;
    }else {//Perfil já existente
        $valor = 4;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = '../categorizacoes.php?valor=<?=$valor;?>'
    </Script>
<?
}

//Busca dados do Tipo de Perfil passado por parâmetro ...
$sql = "SELECT * 
        FROM `clientes_perfils` 
        WHERE `id_cliente_perfil` = '$_GET[id_cliente_perfil]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Perfil de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Perfil
    if(!texto('form', 'txt_perfil', '3', ' abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'PERFIL', '2')) {
        return false
    }
}
</Script>
</head>
<body onLoad='document.form.txt_perfil.focus()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<input type='hidden' name='id_cliente_perfil' value="<?=$_GET['id_cliente_perfil'];?>">
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Perfil de Cliente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Perfil:</b>
        </td>
        <td>
            <input type='text' name='txt_perfil' value='<?=$campos[0]['perfil'];?>' title='Digite o Perfil de Cliente' size='22' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
      	<td>
            <textarea name='txt_observacao' cols='85' rows='3' malxength='255' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../categorizacoes.php'" class='botao'>
            <input type='reset' name='cmd_redefinir' value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_perfil.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>