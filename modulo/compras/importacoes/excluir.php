<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>IMPORTAÇÃO EXCLUIDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>IMPORTAÇÃO NÃO PODE SER EXCLUIDA.</font>";

if($passo == 1) {
	//Tratamento com as variáveis que vem por parâmetro ...
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$txt_importacao             = $_POST['txt_importacao'];
		$chkt_importacoes_atreladas = $_POST['chkt_importacoes_atreladas'];
	}else {
		$txt_importacao             = $_GET['txt_importacao'];
		$chkt_importacoes_atreladas = $_GET['chkt_importacoes_atreladas'];
	}
	if(!empty($chkt_importacoes_atreladas)) $condicao_atrelados = "INNER JOIN `pedidos` p ON p.id_importacao = i.id_importacao ";
	
	$sql = "SELECT i.id_importacao, i.nome, i.observacao 
                FROM `importacoes` i 
                $condicao_atrelados 
                WHERE i.nome LIKE '%$txt_importacao%' 
                AND i.ativo = '1' 
                GROUP BY i.id_importacao ORDER BY i.nome ";
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
            <Script Language = 'Javascript'>
                window.location = 'excluir.php?valor=1'
            </Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Excluir Importa&ccedil;&otilde;es ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='3'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            Excluir Importação(ões)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Nome
        </td>
        <td>
            Observa&ccedil;&atilde;o
        </td>
        <td>
            <label for='todos'>Todos </label>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td align='center'>
            <input type="checkbox" name="chkt_importacao[]" value="<?=$campos[$i]['id_importacao'];?>" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
            <input type='submit' name='cmd_excluir' value="Excluir" title='Excluir' class='botao'>
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
}else if($passo == 2) {
    foreach($_POST['chkt_importacao'] as $id_importacao) {
        $sql = "UPDATE `importacoes` SET `ativo` = '0' WHERE `id_importacao` = '$id_importacao' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Importação(ões) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body onload='document.form.txt_importacao.focus()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Excluir Importação(ões)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Importação
        </td>
        <td>
            <input type="text" name="txt_importacao" title="Digite a Importação" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_importacoes_nao_atreladas' value='1' title='Somente Importações não Atreladas' id='lbl_importacoes_nao_atreladas' onclick='document.form.txt_importacao.focus()' class='checkbox'>
            <label for='lbl_importacoes_nao_atreladas'>Somente Importações não Atreladas</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_importacao.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>