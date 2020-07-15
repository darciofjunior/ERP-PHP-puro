<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>DEPARTAMENTO ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>DEPARTAMENTO JÁ EXISTENTE.</font>";

if($passo == 1) {
    //Aqui eu trago o Departamento do id passado por parâmetro ...
    $sql = "SELECT `departamento` 
            FROM `departamentos` 
            WHERE `id_departamento` = '$_GET[id_departamento]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Departamento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Departamento
    if(!texto('form', 'txt_departamento', '3', 'áéíóúÁÉÍÓÚçÇabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 ', 'DEPARTAMENTO', '2')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_departamento.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<input type='hidden' name='id_departamento' value='<?=$_GET['id_departamento'];?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Departamento(s)
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Departamento:</b>
        </td>
        <td>
            <input type='text' name='txt_departamento' value='<?=$campos[0]['departamento'];?>' title='Digite o Departamento' size='28' maxlength='25' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='redefinir("document.form","REDEFINIR");document.form.txt_departamento.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
<?
}else if($passo == 2) {
//Verifico se já existe esse Departamento cadastrado no BD ...
    $sql = "SELECT `id_departamento` 
            FROM `departamentos` 
            WHERE `departamento` = '$_POST[txt_departamento]' 
            AND `id_departamento` <> '$_POST[id_departamento]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe, então atualizo normalmente ...
        $sql = "UPDATE `departamentos` SET `departamento` = '$_POST[txt_departamento]' WHERE `id_departamento` = '$_POST[id_departamento]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Se já existe, então, o sistema retorna erro ...
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
    $sql = "SELECT `id_departamento`, `departamento` 
            FROM `departamentos` 
            WHERE `ativo` = '1' ORDER BY `departamento` ";
    $campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
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
<title>.:: Alterar Departamento(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Departamento(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Departamento
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=1&id_departamento=<?=$campos[$i]['id_departamento'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href = '#'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href = '#' class='link'>
                <?=$campos[$i]['departamento'];?>
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>