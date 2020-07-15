<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../../');
?>
<html>
<head>
<title>.:: Lista de Preço(s) Atual ::.</title>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body>
<table width='60%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Lista de Preço(s) Atual
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'lista_preco_nacional.php' title='Lista de Preço Nacional' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Lista de Preço Nacional
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'lista_preco_export.php' title='Lista de Preço Nacional Export' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Lista de Preço de Exportação
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'clonar_prom_nac_exp.php' title='Clonar Promoção' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <font color='darkgreen'>
                    Clonar Itens da Promoção Nacional p/ Exportação
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'retirar_promocao_nacional.php' title='Retirar Promoção Nacional' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <font color='red'>
                    Retirar Promoção Nacional
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'retirar_promocao_export.php' title='Retirar Promoção de Exportação' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <font color='red'>
                    Retirar Promoção de Exportação
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