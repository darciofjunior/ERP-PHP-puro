<?
require('../lib/segurancas.php');
//segurancas::geral($PHP_SELF, '../');

session_start('funcionarios');
$mensagem[1] = '<font class="confirmacao">MENSAGEM ALTERADA COM SUCESSO.</font>';

//Busca a ajuda do id passado por parâmetro
$sql = "SELECT * 
        FROM `helps` 
        WHERE `id_help` = '$_GET[id_help]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Visualizar Ajuda ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../js/sessao.js'></Script>
</head>
<body>
<table width='80%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Visualizar Ajuda
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Título:</b>
        </td>
        <td>
            <?=$campos[0]['titulo'];?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Mensagem:</b>
        </td>
        <td>
            <?=$campos[0]['mensagem'];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
<?
        //Os únicos usuários que podem alterar o conteúdo da Ajuda são Roberto 66, Dárcio 98 e Arnaldo Netto 147 porque programam ...
	if($_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
?>
        <input type="button" name="cmd_alterar_ajuda" value="Alterar Ajuda" title="Alterar Ajuda" onclick="window.location = 'alterar.php?id_help=<?=$_GET['id_help'];?>'" class="botao">
<?
	}
?>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>