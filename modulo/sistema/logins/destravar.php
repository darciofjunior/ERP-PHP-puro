<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>USUÁRIO DESTRAVADO COM SUCESSO.</font>";

if(!empty($_GET['id_login'])) {
    $sql = "UPDATE `logins` SET `tentativa_errada` = 0
            WHERE id_login = '$_GET[id_login]' ";
    bancos::sql($sql);
    $valor = 1;
}

$sql = "SELECT l.id_login, l.login, l.tentativa_errada, f.nome, e.nomefantasia
        FROM logins l
        INNER JOIN funcionarios f ON f.id_funcionario = l.id_funcionario
        INNER JOIN empresas e ON e.id_empresa = f.id_empresa
        WHERE l.id_funcionario = f.id_funcionario AND f.id_empresa = e.id_empresa AND l.tentativa_errada = 3
        ORDER BY e.nomefantasia, f.nome";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Destravar Usuário(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<?
    if($linhas == 0) {//Não existem Usuários Travados ...
        echo '<font class="atencao"><center>NÃO EXISTE(M) USUÁRIO TRAVADO(S).</center></font>';
        exit;
    }else {
?>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
   <tr align='center'>
        <td colspan='4'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            Destravar Usuário(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Empresa
        </td>
        <td> 
            Nome
        </td>
        <td>
            Login
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['login'];?>
        </td>
        <td>
            <img src="../../../imagem/propriedades.png" title="Destravar" alt="Destravar" style="cursor:pointer" border="0" onclick="window.location = 'destravar.php?id_login=<?=$campos[$i]['id_login'];?>'">
        </td>
    </tr>
<?
	}
?>
    <tr class="linhacabecalho">
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</body>
</html>