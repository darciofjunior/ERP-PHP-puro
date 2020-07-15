<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../../');
?>
<html>
<head>
<title>.:: Migrar Nova Lista de Preço(s) ::.</title>
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
            Migrar Lista(s) de Preço(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font color='yellow'>
                Opções Referentes a Nova Lista de Preço
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href='migrar_nova_lista_preco.php' title='Migrar Nova Lista de Preço p/ a Lista de Preço' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Migrar Nova Lista de Preço p/ a Lista de Preço
                &nbsp;
                <font color='red'>
                    <b>(Faz backup da Lista e Migra Preços, Descontos e Acréscimos)</b>
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'migrar_promocao.php' title='Migrar Promoção da Nova Lista de Preço p/ a Lista de Preço' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Migrar Promoção da Nova Lista de Preço p/ a Lista de Preço
                &nbsp;
                <font color='red'>
                    <b>(Apaga Pços e Qtds Promocionais, depois Migra Pços e Qtds Promocionais de acordo c/ o Grupo Empresa Divisão selecionado)</b>
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'desmigrar_nova_lista_preco.php' title='Desmigrar Nova Lista de Preço (Retorna os Valores de backup da Lista)' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <font color='red'>
                    Desmigrar Nova Lista de Preço (Retorna os Valores de backup da Lista)
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font color='yellow'>
                Opções Referentes a Atual Lista de Preço
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href="javascript:migrar_atual_lista_preco()" title='Migrar Atual Lista de Preço p/ Nova Lista de Preço' class='link'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                Migrar Atual Lista de Preço p/ Nova Lista de Preço
            </a>
            &nbsp;
            <font color='red'>
                <b>(Migra os Preços da Lista Atual e o(s) Desc. A, Desc. B e Acréscimo)</b>
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