<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>TIPO DE VALE TRANSPORTE ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>TIPO DE VALE TRANSPORTE JÁ EXISTENTE.</font>";

if($passo == 1) {
    $sql = "SELECT * 
            FROM `vales_transportes` 
            WHERE `id_vale_transporte` = '$_GET[id_vale_transporte]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Vale(s) Transporte(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tipo de VT
    if(!texto('form', 'txt_tipo_vt', '3', '0123456789,.()[]{} +&âêîôûÂÊÎÔÛãõÃÕáéíóúÁÉÍÓÚabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZçÇ _-', 'TIPO DE VT', '2')) {
        return false
    }
//Valor Unitário
    if(!texto('form', 'txt_valor_unitario', '1', '1234567890,.', 'VALOR UNITÁRIO', '2')) {
        return false
    }
    return limpeza_moeda('form', 'txt_valor_unitario, ')
}
</Script>
</head>
<body onload='document.form.txt_tipo_vt.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<input type='hidden' name='hdd_vale_transporte' value='<?=$_GET['id_vale_transporte'];?>'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Vale(s) Transporte(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de VT:</b>
        </td>
        <td>
            <input type='text' name='txt_tipo_vt' value='<?=$campos[0]['tipo_vt'];?>' title='Digite o Tipo de VT' size='35' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Unitário:</b>
        </td>
        <td>
            <input type='text' name='txt_valor_unitario' value='<?=number_format($campos[0]['valor_unitario'], 2, ',', '.');?>' title='Digite o Valor Unitário' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='15' maxlength='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_tipo_vt.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
//Aqui eu verifico no Sistema se já existe cadastrado o Tipo de Vale Transporte digitado pelo usuário ...
    $sql = "SELECT `id_vale_transporte` 
            FROM `vales_transportes` 
            WHERE `tipo_vt` = '$_POST[txt_tipo_vt]' 
            AND `valor_unitario` = '$_POST[txt_valor_unitario]' 
            AND `ativo` = '1' 
            AND `id_vale_transporte` <> '$_POST[hdd_vale_transporte]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Não existe ...
        $sql = "UPDATE `vales_transportes` SET `tipo_vt` = '$_POST[txt_tipo_vt]', `valor_unitario` = '$_POST[txt_valor_unitario]' WHERE `id_vale_transporte` = '$_POST[hdd_vale_transporte]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Já existe ...
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
    $sql = "SELECT * 
            FROM `vales_transportes` 
            WHERE `ativo` = '1' ORDER BY `tipo_vt` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = '../../../html/index.php?valor=3'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Vale(s) Transporte(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Vale(s) Transporte(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Tipo de VT
        </td>
        <td>
            Valor Unitário
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=1&id_vale_transporte=<?=$campos[$i]['id_vale_transporte'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href = 'alterar.php?passo=1&id_vale_transporte=<?=$campos[$i]['id_vale_transporte'];?>' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href = 'alterar.php?passo=1&id_vale_transporte=<?=$campos[$i]['id_vale_transporte'];?>' class='link'>
                <?=$campos[$i]['tipo_vt'];?>
            </a>        
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor_unitario'], 2, ',', '.');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>