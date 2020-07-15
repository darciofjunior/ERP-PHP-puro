<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>DEPARTAMENTO EXCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>ALGUN(S) REGISTRO(S) NÃO PODEM SER APAGADO(S) POIS CONSTA EM USO POR OUTRO CADASTRO.</font>";

if($passo == 1) {
    foreach($_POST['chkt_departamento'] as $id_departamento) {
//Verifico se o Departamento está em uso p/ poder estar excluindo o mesmo ...
        if(cascate::consultar('id_departamento', 'funcionarios', $id_departamento)) {//Em uso
            $valor = 2;
        }else {//Não está em uso, sendo assim posso excluir este normalmente
            $sql =  "UPDATE `departamentos` SET `ativo` = '0' WHERE `id_departamento` = '$id_departamento' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 1;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
    $sql = "SELECT id_departamento, departamento 
            FROM `departamentos` 
            WHERE `ativo` = '1' ORDER BY departamento ";
    $campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '../../../html/index.php?valor=4'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Departamento(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
            <td colspan='2'>
                Excluir Departamento(s)
            </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Departamento
        </td>
        <td>
            <input type='checkbox' name='chkt' title='Selecionar Todos' onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" id='todos' class='checkbox'>
        </td>
    </tr>
<?

        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td align="left">
            <?=$campos[$i]['departamento'];?>
        </td>
        <td> 
            <input type='checkbox' name='chkt_departamento[]' value='<?=$campos[$i]['id_departamento'];?>' onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'> 
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' style ='cursor:hand' class="botao">
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