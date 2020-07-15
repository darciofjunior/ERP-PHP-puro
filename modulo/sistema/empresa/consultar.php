<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$sql = "SELECT * 
        FROM `empresas` 
        WHERE `ativo` = '1' ORDER BY nomefantasia ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../html/index.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Empresa(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Empresa(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
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
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        //Tenho que mudar o nome da variável, para não dar problema com a variável id_empresa da Sessão ...
        $url = 'alterar.php?passo=1&id_empresa_loop='.$campos[$i]['id_empresa'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['nomefantasia'];?>
            </a>
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
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>