<?
require('../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../lib/menu/menu.php');//Significa que foi acessado de fora do PDT ...
//segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');
?>
<html>
<head>
<title>.:: Relat�rios Estrat�gicos de Vendas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relat�rios Estrat�gicos de Vendas
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <a href = 'relatorio_desconto_volume.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Relat�rio de Desconto vs Volume' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Relat�rio de Desconto vs Volume
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'relatorio_estoque_pendencia.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Relat�rio de Estoque vs Pend�ncia' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Relat�rio de Estoque vs Pend�ncia
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'relatorio_estoque_pendencia_empresa.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Relat�rio de Estoque vs Pend�ncia (Total por Empresa)' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Relat�rio de Estoque vs Pend�ncia (Total por Empresa)
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'relatorio_analise_clientes.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Relat�rio de An�lise de Cliente(s)' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Relat�rio de An�lise de Cliente(s) <font color='red'>(TAMB�M GERENCIA TODA PARTE DE CR�DITOS DO CLIENTE)</font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'relatorio_cliente_com_movimento.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Relat�rio de Clientes com Movimento' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Relat�rio de Clientes com Movimento <font color='red'>(FATURAMENTO)</font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'relatorio_estoque_compra_cliente.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Relat�rio de Estoque vs Compra Cliente' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Relat�rio de Estoque vs Compra Cliente
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'relatorio_faturamento_por_linha.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Relat�rio de Faturamento por Linha' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Relat�rio de Faturamento por Linha
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'relatorio_rep_cliente.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Relat�rio de Pedidos Emitidos Vs Clientes' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Relat�rio de Pedidos Emitidos vs Clientes por ANO <font color='red'>(PEDIDO)</font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'relatorio_proj_vendas_pedidos_cliente_compra.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Avalia��o de Resultados - Proje��o de Vendas p/ Pedidos que o Cliente Compra' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Avalia��o de Resultados - Proje��o de Vendas p/ Pedidos que o Cliente Compra
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'relatorio_proj_vendas_pedidos_cliente_nao_compra.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Avalia��o de Resultados - Proje��o de Vendas p/ Pedidos que o Cliente N�o Compra' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Avalia��o de Resultados - Proje��o de Vendas p/ Pedidos que o Cliente n�o Compra
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = '../relatorio/projeto_trimestral/projeto_trimestral.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Relat�rio de Projeto Trimestral' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Relat�rio de Projeto Trimestral
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'maiores_compradores_por_familia.php?representante=<?=$_GET['representante'];?>&pop_up=<?=$_GET['pop_up'];?>' title='Maiores Compradores por Fam�lia' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>&nbsp;
                Maiores Compradores por Fam�lia
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = 'crm_clientes.php?pop_up=<?=$_GET['pop_up'];?>' title='CRM de Clientes - D�rcio' class='link'>
                <font color='black'>
                    * CRM de Clientes (D�RCIO)
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>