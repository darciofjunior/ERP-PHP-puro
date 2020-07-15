<?
require('../../../../lib/segurancas.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produto_acabado = $_POST['id_produto_acabado'];
    $tela               = $_POST['tela'];
}else {
    $id_produto_acabado = $_GET['id_produto_acabado'];
    $tela               = $_GET['tela'];
}

if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_esp.php', '../../../../');
}else {
    session_start('funcionarios');
}

if(!empty($_POST['txt_observacao'])) {
    $data_sys   = date('Y-m-d H:i:s');
    $observacao = strtolower($_POST['txt_observacao']);
    
    $sql = "INSERT INTO `produtos_acabados_follow_ups` (`id_produto_acabado_follow_up`, `id_produto_acabado`, `id_funcionario`, `observacao`, `data_sys`) VALUES (null, '$_POST[id_produto_acabado]', '$_SESSION[id_funcionario]', '$observacao', '$data_sys') ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('FOLLOW-UP REGISTRADO COM SUCESSO !')
        parent.document.location = "follow_up.php?id_produto_acabado=<?=$_POST['id_produto_acabado'];?>&tela=<?=$_POST['tela'];?>"
    </Script>
<?
}

//Procedimento quando carrega a Tela ...
$id_produto_acabado = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado'] : $_GET['id_produto_acabado'];
?>
<html>
<head>
<title>.:: Registrar Follow-up do Produto Acabado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Observação
    if(document.form.txt_observacao.value == '') {
        alert('DIGITE A OBSERVAÇÃO !')
        document.form.txt_observacao.focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_observacao.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--*************************Controles de Tela*************************-->
<input type='hidden' name='id_produto_acabado' value="<?=$id_produto_acabado;?>">
<input type='hidden' name='tela' value="<?=$tela;?>">
<!--*******************************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td>
            <fieldset>
                <legend>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                        <b>REGISTRAR FOLLOW-UP DO PRODUTO ACABADO</b>
                    </font>
                </legend>
                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                    <tr class='linhanormal'>
                        <td colspan='2'>
                            <b>Observação:</b>
                        </td>
                    </tr>
                    <tr class='linhanormal'>
                        <td colspan='2'>
                            <textarea name='txt_observacao' cols='110' rows='3' title="Digite a Observação" class='caixadetexto'></textarea>
                        </td>
                    </tr>
                    <tr class='linhacabecalho' align='center'>
                        <td colspan='2'>
                            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_observacao.focus()" style='color:#ff9900' class='botao'>
                            <input type='submit' name='cmd_registrar' value='Registrar' title='Registrar' onclick="if(typeof(window.top.opener) == 'object') {window.top.document.form.nao_atualizar.value = 1}" style='color:green' class='botao'>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
</form>
</body>
</html>