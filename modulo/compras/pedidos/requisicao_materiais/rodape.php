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
<title>.:: Requisição de Materiais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function antecipacoes() {
/*Aqui não tem jeito além do id_pedido que eu já levava, eu tenho que levar esses 2 parâmetros que são 
daqui de requisição: os Itens de Pedido que foram escolhidos e as Qtdes Digitadas

Também solicito a tela de Antecipações, só que está agora vem como Pop-UP e por isso eu levo
a mais um parâmetro de requisicao=1, para controle de alguns botões dentro dessa tela*/
    nova_janela('../antecipacoes.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_item_pedido=<?=$_GET['chkt_item_pedido'];?>&txt_qtde=<?=$_GET['txt_qtde'];?>&requisicao=1', 'CONSULTAR', '', '', '', '', 500, 950, 'c', 'c', '', '', 's', 's', '', '', '')
}

function imprimir() {
/*Se o Tipo de Pedido for SGD, então terá que redirecionar para uma tela em que o usuário possa escolher 
o cabeçalho que deseja imprimir*/
    var tipo_nota = eval('<?= $campos[0]['tipo_nota'];?>')
/*A observação eu pego do Frame de cima, eu preferi fazer assim, para não ter que ficar levando
mais um parâmetro por causa do Rodapé, e não perder o Botão de Antecipações Via Financeiro*/
    var obs_requisicao = parent.itens.document.form.obs_requisicao.value
/*Se o Tipo de Pedido for NF, então eu já posso imprimir a Requisição diretamente*/
    if(tipo_nota == 1) {
        return nova_janela('relatorio_pdf/requisicao.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_item_pedido=<?= $_GET['chkt_item_pedido'];?>&txt_qtde=<?= $_GET['txt_qtde'];?>&obs_requisicao='+obs_requisicao, 'CONSULTAR', 'F')
/*Se o Tipo de Pedido for SGD, então mostra essa opção para escolher qual tipo de cabeçalho que se deseja 
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
//Significa que o usuário anteriormente pediu para criar uma antecipação na tela de Requisição de Pedidos
            if($_GET['criar_antecipacao'] == 1) {
        ?>
        <input type="button" name="cmd_antecipacoes" value="Via Financeiro (Antecipação)" title="Via Financeiro (Antecipação)" onclick="antecipacoes()" class="botao">
        <?
            }
        ?>
        <input type="button" name="cmd_observacao" value="Observação" title="Observação" onclick="observacao()" class="botao">
        <input type='button' name="cmd_imprimir" value="Imprimir" title="Imprimir" onclick='return imprimir()' class='botao'>
        <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick='window.parent.close()' style="color:red" class='botao'>
    </td>
</table>
</body>
</html>