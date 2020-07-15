<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">EMPRESA EXCLUÍDA COM SUCESSO.</font>';

if(!empty($_POST['chkt_empresa'])) {
//Coloquei esse nome de id_empresa_loop, por causa que já existe id_empresa na sessão, daí iria dar conflito
    foreach ($_POST['chkt_empresa'] as $id_empresa_loop) {
        $sql =  "UPDATE `empresas` SET `ativo` = '0' WHERE `id_empresa` = '$id_empresa_loop' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

$sql = "SELECT * 
        FROM `empresas` 
        WHERE `ativo` = '1' ORDER BY nomefantasia ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../html/index.php?valor=4'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Empresa(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Empresa(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Nome Fantasia
        </td>
        <td>
            Razão Social
        </td>
        <td>
            CNPJ
        </td>
        <td>
            IE
        </td>
        <td>
            IP Externo
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar todos' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
                <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            if(empty($campos[$i]['cnpj'])) {
                echo '&nbsp;';
            }else {
                echo substr($campos[$i]['cnpj'], 0, 2).'.'.substr($campos[$i]['cnpj'], 2, 3).'.'.substr($campos[$i]['cnpj'], 5, 3).'/'.substr($campos[$i]['cnpj'], 8, 4).'-'.substr($campos[$i]['cnpj'], 12, 2);
            }
        ?>
        </td>
        <td>
        <?
            if(empty($campos[$i]['ie'])) {
                echo '&nbsp;';
            }elseif(strlen($campos[$i]['ie'] == 12)) {
                echo substr($campos[$i]['ie'], 0, 3).'.'.substr($campos[$i]['ie'], 3, 3).'.'.substr($campos[$i]['ie'], 6, 3).'.'.substr($campos[$i]['ie'], 9, 3);
            }else {
                echo $campos[$i]['ie'];
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['ip_externo'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_empresa[]' value='<?=$campos[$i]['id_empresa'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' style='cursor:hand' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>