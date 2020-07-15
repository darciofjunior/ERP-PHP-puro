<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Aqui eu listo todos Recebimentos cadastrados no Sistema ...
$sql = "SELECT * 
	FROM `tipos_recebimentos` 
	WHERE `ativo` = '1' ORDER BY `recebimento` ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '../../../html/index.php?valor=2'
    </Script>
<?
    exit;
}
?>
<html>
<head>
<title>.:: Consultar Tipo(s) de Recebimento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Tipo(s) de Recebimento(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Tipo de Recebimento
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'detalhes.php?id_tipo_recebimento='.$campos[$i]['id_tipo_recebimento'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['recebimento'];?>
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