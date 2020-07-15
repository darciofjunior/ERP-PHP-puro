<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../../');
?>
<html>
<head>
<title>.:: Nova Lista de Preço(s) ::.</title>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body>
<form name='form'>
<table width='70%' cellpadding='1' cellspacing='1' align='center'>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Nova Lista de Preço(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'lista_preco_nacional.php' title='Nova Lista de Preço Nacional' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Nova Lista de Preço Promocional e Nacional
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'lista_preco_promocional.php' title='Nova Lista de Preço Promocional' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Nova Lista de Preço Promocional 
                <font color='red'>
                    (De preferência usar a Nova Lista de Preço Promocional e Nacional acima)
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'retirar_promocao.php' title='Retirar Promoção' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Retirar Promoção
            </a>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>