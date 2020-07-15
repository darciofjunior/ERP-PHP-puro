<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = '<font class="confirmacao">FAIXA DE DESCONTO DO CLIENTE EXCLUÍDA COM SUCESSO.</font>';
?>
<html>
<head>
<title>.:: Atualizar Cota(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
</head>
<body>
<table width='60%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td>
            Atualizar Cota(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href='paises_vs_cotas.php' title='País(es) vs Cota(s)' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'> País(es) vs Cota(s)
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href='ufs_vs_cotas.php' title='UF(s) vs Cota(s)' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'> UF(s) vs Cota(s)
            </a>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>