<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../../');
?>
<html>
<head>
<title>.:: Lista de Pre�o(s) Atual ::.</title>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body>
<table width='60%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Lista de Pre�o(s) Atual
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'lista_preco_nacional.php' title='Lista de Pre�o Nacional' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Lista de Pre�o Nacional
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'lista_preco_export.php' title='Lista de Pre�o Nacional Export' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Lista de Pre�o de Exporta��o
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'clonar_prom_nac_exp.php' title='Clonar Promo��o' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <font color='darkgreen'>
                    Clonar Itens da Promo��o Nacional p/ Exporta��o
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'retirar_promocao_nacional.php' title='Retirar Promo��o Nacional' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <font color='red'>
                    Retirar Promo��o Nacional
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'retirar_promocao_export.php' title='Retirar Promo��o de Exporta��o' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <font color='red'>
                    Retirar Promo��o de Exporta��o
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>