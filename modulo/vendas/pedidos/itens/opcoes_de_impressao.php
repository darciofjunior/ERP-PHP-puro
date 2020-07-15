<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');

//Aki verifico qual é o País do Cliente deste Pedido p/ identificar qual documento de Impressão que deverá ser aberto ...
$sql = "SELECT c.id_pais 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
        WHERE pv.`id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
$campos     = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Opções de Impressão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    var id_pais = eval('<?=$campos[0]['id_pais']?>')
    if(document.form.opt_opcao[0].checked == true) {//Via Cliente
        //Fecha o Pop-UP atual e abre a Tela de Impressão, coloco um timer p/ que eu consiga abrir o Novo-Pop ...
        setTimeout('window.close()', '800')
        if(id_pais == 31) {//Cliente do Brasil, exibo o relatório Nacional ...
            nova_janela('relatorio/relatorio.php?id_pedido_venda=<?=$_GET['id_pedido_venda'];?>&via=0', 'RELATORIO', 'F')
        }else {//Do contrário exibo o relatório de Exportação ...
            nova_janela('relatorio/relatorio_exportacao.php?id_pedido_venda=<?=$_GET['id_pedido_venda'];?>', 'CONSULTAR', 'F')
        }
    }else if(document.form.opt_opcao[1].checked == true) {//Via Estoque
        //Fecha o Pop-UP atual e abre a Tela de Impressão, coloco um timer p/ que eu consiga abrir o Novo-Pop ...
        setTimeout('window.close()', '800')
        nova_janela('relatorio/relatorio.php?id_pedido_venda=<?=$_GET['id_pedido_venda'];?>&via=1', 'RELATORIO', 'F')
    }else {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Opções de Impressão
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Via Cliente' id='label' checked>
            <label for='label'>Via Cliente</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Via Estoque' id='label2'>
            <label for='label2'>Via Estoque</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>