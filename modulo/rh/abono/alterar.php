<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/abono/consultar.php', '../../../');

$mensagem[1]    = "<font class='confirmacao'>ABONO ALTERADO COM SUCESSO.</font>";
$id_abono       = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_abono'] : $_GET['id_abono'];

$data_emissao   = date('Y-m-d');
$data_sys       = date('Y-m-d H:i:s');

if(!empty($_POST['txt_valor'])) {
    $sql = "UPDATE `abonos` SET `valor` = '$_POST[txt_valor]' WHERE `id_abono` = '$_POST[id_abono]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

$sql = "SELECT a.*, e.razaosocial, f.nome, vd.data 
        FROM `abonos` a 
        INNER JOIN funcionarios f ON f.id_funcionario = a.id_funcionario 
        INNER JOIN empresas e ON e.id_empresa = f.id_empresa 
        INNER JOIN vales_datas vd ON vd.id_vale_data = a.id_vale_data 
        WHERE a.id_abono = '$id_abono' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Abono ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Valor ...
    if(!texto('form', 'txt_valor', '1', '0123456789,.', 'VALOR', '2')) {
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
    return limpeza_moeda('form', 'txt_valor, ')
}


//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_valor.focus()' onunload="atualizar_abaixo()">
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_abono' value='<?=$id_abono;?>'>
<!--Esse hidden é um controle de Tela-->
<input type="hidden" name='passo' onclick="atualizar()">
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Abono
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Funcionário:</b>
            </font>
        </td>
        <td>
            <?=$campos[0]['nome'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Empresa:</b>
            </font>
        </td>
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor:</b>
        </td>
        <td>
            <input type="text" name="txt_valor" value="<?=number_format($campos[0]['valor'], 2, ',', '.');?>" title="Digite o Valor" onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" size="7" maxlength="6" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Data de Holerith:</b>
            </font>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data'], '/');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Data de Emissão:</b>
            </font>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_emissao'], '/');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Abono:</b>
            </font>
        </td>
        <td>
            <?=$campos[0]['descontar_pd_pf'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Descontado:</b>
            </font>
        </td>
        <td>
        <?
            if($campos[0]['descontado'] == 'N') {
                echo 'Não';
            }else {
                echo 'Sim';
            }
        ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_valor.focus()" class="botao">
            <input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>