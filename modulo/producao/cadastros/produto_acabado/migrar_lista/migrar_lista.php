<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../../');
?>
<html>
<head>
<title>.:: Migrar Nova Lista de Pre�o(s) ::.</title>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function migrar_atual_lista_preco() {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA MIGRAR A LISTA ATUAL P/ A NOVA LISTA ?')
    if(resposta == true) window.location = 'migrar_atual_lista_preco.php'
}
</Script>
</head>
<body>
<form name='form'>
<table width='80%' cellpadding='1' cellspacing='1' align='center'>
    <tr>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Migrar Lista(s) de Pre�o(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font color='yellow'>
                Op��es Referentes a Nova Lista de Pre�o
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href='migrar_nova_lista_preco.php' title='Migrar Nova Lista de Pre�o p/ a Lista de Pre�o' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Migrar Nova Lista de Pre�o p/ a Lista de Pre�o
                &nbsp;
                <font color='red'>
                    <b>(Faz backup da Lista e Migra Pre�os, Descontos e Acr�scimos)</b>
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'migrar_promocao.php' title='Migrar Promo��o da Nova Lista de Pre�o p/ a Lista de Pre�o' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Migrar Promo��o da Nova Lista de Pre�o p/ a Lista de Pre�o
                &nbsp;
                <font color='red'>
                    <b>(Apaga P�os e Qtds Promocionais, depois Migra P�os e Qtds Promocionais de acordo c/ o Grupo Empresa Divis�o selecionado)</b>
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'desmigrar_nova_lista_preco.php' title='Desmigrar Nova Lista de Pre�o (Retorna os Valores de backup da Lista)' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <font color='red'>
                    Desmigrar Nova Lista de Pre�o (Retorna os Valores de backup da Lista)
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font color='yellow'>
                Op��es Referentes a Atual Lista de Pre�o
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href="javascript:migrar_atual_lista_preco()" title='Migrar Atual Lista de Pre�o p/ Nova Lista de Pre�o' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Migrar Atual Lista de Pre�o p/ Nova Lista de Pre�o
            </a>
            &nbsp;
            <font color='red'>
                <b>(Migra os Pre�os da Lista Atual e o(s) Desc. A, Desc. B e Acr�scimo)</b>
            </font>
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