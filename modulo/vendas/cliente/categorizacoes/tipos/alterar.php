<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/categorizacoes/categorizacoes.php', '../../../../../');

if(!empty($_POST['txt_tipo'])) {
    //Verifica se este Tipo de Cliente digitado pelo usuário já está cadastrado, diferente do atual passado por parâmetro ...
    $sql = "SELECT id_cliente_tipo 
            FROM `clientes_tipos` 
            WHERE `tipo` = '$_POST[txt_tipo]' 
            AND `id_cliente_tipo` <> '$_POST[id_cliente_tipo]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Tipo não existente
        $sql = "UPDATE `clientes_tipos` SET `tipo` = '$_POST[txt_tipo]' WHERE `id_cliente_tipo` = '$_POST[id_cliente_tipo]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Tipo já existente
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = '../categorizacoes.php?valor=<?=$valor;?>'
    </Script>
<?
}

//Busca dados do Tipo de Cliente passado por parâmetro ...
$sql = "SELECT * 
        FROM `clientes_tipos` 
        WHERE `id_cliente_tipo` = '$_GET[id_cliente_tipo]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Tipo de Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Perfil
    if(!texto('form', 'txt_tipo', '3', ' abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'TIPO', '2')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_tipo.focus()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<input type='hidden' name='id_cliente_tipo' value="<?=$_GET['id_cliente_tipo'];?>">
<table width='50%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Tipo de Cliente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo:</b>
        </td>
        <td>
            <input type='text' name='txt_tipo' value='<?=$campos[0]['tipo'];?>' title='Digite o Tipo de Cliente' size='22' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../categorizacoes.php'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_tipo.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>