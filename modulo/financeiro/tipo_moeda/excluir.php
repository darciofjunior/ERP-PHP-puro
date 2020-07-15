<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">TIPO DE MOEDA EXCLUIDA COM SUCESSO.</font>';

if(!empty($_POST['chkt_tipo_moeda'])) {
//Atualizando o Status do Tipo de Moeda p/ Zero ...
    foreach($_POST['chkt_tipo_moeda'] as $id_tipo_moeda) {
        $sql = "UPDATE `tipos_moedas` SET `ativo` = '0' WHERE `id_tipo_moeda` = '$id_tipo_moeda' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Busca todos os Tipos de Moedas que estão cadastradas no Sistema ...
$sql = "SELECT * 
        FROM `tipos_moedas` 
        WHERE `ativo` = '1' ORDER BY moeda ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '../../../html/index.php?valor=4'
    </Script>
<?
    exit;
}
?>
<html>
<head>
<head>
<title>.:: Excluir Tipo de Moeda ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
}
</script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='4'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            Excluir Tipo de Moeda
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Moeda
        </td>
        <td>
            Símbolo
        </td>
        <td>
            Origem
        </td>
        <td>
            <label for='todos'>Todos </label>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['moeda'];?>
        </td>
        <td>
            <?=$campos[$i]['simbolo'];?>
        </td>
        <td>
            <?=$campos[$i]['origem'];?>
        </td>
        <td>
            <input type="checkbox" name="chkt_tipo_moeda[]" value="<?=$campos[$i]['id_tipo_moeda'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
     </tr>
<?
    }
?>
    <tr class="linhacabecalho" align='center'>
        <td colspan='4'>
            <input type="submit" name="cmd_excluir" value="Excluir" title="Excluir" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>