<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

$sql = "SELECT tipo_nota 
	FROM `pedidos` 
	WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Requisi��o de Materiais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function antecipacoes() {
/*Aqui n�o tem jeito al�m do id_pedido que eu j� levava, eu tenho que levar esses 2 par�metros que s�o 
daqui de requisi��o: os Itens de Pedido que foram escolhidos e as Qtdes Digitadas

Tamb�m solicito a tela de Antecipa��es, s� que est� agora vem como Pop-UP e por isso eu levo
a mais um par�metro de requisicao=1, para controle de alguns bot�es dentro dessa tela*/
    nova_janela('../antecipacoes.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_item_pedido=<?=$_GET['chkt_item_pedido'];?>&txt_qtde=<?=$_GET['txt_qtde'];?>&requisicao=1', 'CONSULTAR', '', '', '', '', 500, 950, 'c', 'c', '', '', 's', 's', '', '', '')
}

function imprimir() {
/*Se o Tipo de Pedido for SGD, ent�o ter� que redirecionar para uma tela em que o usu�rio possa escolher 
o cabe�alho que deseja imprimir*/
    var tipo_nota = eval('<?= $campos[0]['tipo_nota'];?>')
/*A observa��o eu pego do Frame de cima, eu preferi fazer assim, para n�o ter que ficar levando
mais um par�metro por causa do Rodap�, e n�o perder o Bot�o de Antecipa��es Via Financeiro*/
    var obs_requisicao = parent.itens.document.form.obs_requisicao.value
/*Se o Tipo de Pedido for NF, ent�o eu j� posso imprimir a Requisi��o diretamente*/
    if(tipo_nota == 1) {
        return nova_janela('relatorio_pdf/requisicao.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_item_pedido=<?= $_GET['chkt_item_pedido'];?>&txt_qtde=<?= $_GET['txt_qtde'];?>&obs_requisicao='+obs_requisicao, 'CONSULTAR', 'F')
/*Se o Tipo de Pedido for SGD, ent�o mostra essa op��o para escolher qual tipo de cabe�alho que se deseja 
imprimir*/
    }else {
        return nova_janela('cabecalho_requisicao.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_item_pedido=<?=$_GET['chkt_item_pedido'];?>&txt_qtde=<?=$_GET['txt_qtde'];?>&obs_requisicao='+obs_requisicao, 'CONSULTAR', '', '', '', '', 150, 600, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function observacao() {
    nova_janela('observacao.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_item_pedido=<?=$_GET['chkt_item_pedido'];?>&txt_qtde=<?=$_GET['txt_qtde'];?>', 'CONSULTAR', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <td align='center'>
        <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.parent.location = '../itens/outras_opcoes.php?id_pedido=<?=$_GET['id_pedido'];?>'" class="botao">
        <?
//Significa que o usu�rio anteriormente pediu para criar uma antecipa��o na tela de Requisi��o de Pedidos
            if($_GET['criar_antecipacao'] == 1) {
        ?>
        <input type="button" name="cmd_antecipacoes" value="Via Financeiro (Antecipa��o)" title="Via Financeiro (Antecipa��o)" onclick="antecipacoes()" class="botao">
        <?
            }
        ?>
        <input type="button" name="cmd_observacao" value="Observa��o" title="Observa��o" onclick="observacao()" class="botao">
        <input type='button' name="cmd_imprimir" value="Imprimir" title="Imprimir" onclick='return imprimir()' class='botao'>
        <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick='window.parent.close()' style="color:red" class='botao'>
    </td>
</table>
</body>
</html>