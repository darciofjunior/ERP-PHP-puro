<?
require('../../../lib/segurancas.php');
?>
<html>
<head>
<title>.:: Visualizar Compra Produ��o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<form name='form' method='post'>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
//Aqui eu busco o PI do PA do Loop ...
$sql = "SELECT `id_produto_insumo` 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
$campos_pi = bancos::sql($sql);
if(count($campos_pi) == 1) {//Este PA � um PIPA, ent�o exibo o Link, tem que ser o Roberto 62 ou D�rcio 98 porque programa ...
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            Pend�ncia(s)
        </td>
    </tr>
    <tr>
        <td>
            <iframe name = 'pendencias_item' src = '../../compras/estoque_i_c/nivel_estoque/pendencias_item.php?id_produto_insumo=<?=$campos_pi[0]['id_produto_insumo'];?>&ignorar_seguranca_url=1' width='100%' height='250'></iframe>
        </td>
    </tr>
<?
}
?>
    <tr>
        <td>
            <hr/>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            OP(s)
        </td>
    </tr>
<!--Esse par�metro cmd_consultar=Consultar � um macete significando que o usu�rio j� clicou no Bot�o 
cmd_consultar da Tela de Filtro e ir diretamente p/ a Tela P�s-Filtro, o par�metro pop_up=1 j� � outro macete 
porque apesar dessa tela n�o ter sido aberta como Pop-UP eu n�o quero que a mesma me apresente o Menu ...-->
    <tr>
        <td>
            <iframe name = 'iframe_ops' src = '../../producao/ops/alterar.php?id_produto_acabado=<?=$_GET['id_produto_acabado'];?>&cmd_consultar=Consultar&pop_up=1' width='100%' height='250'></iframe>
        </td>
    </tr>
    <tr>
        <td>
            <hr/>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            OE(s)
        </td>
    </tr>
    <tr>
        <td>
            <!--Esse par�metro cmd_consultar=Consultar � um macete significando que o usu�rio j� clicou no Bot�o 
            cmd_consultar da Tela de Filtro e ir diretamente p/ a Tela P�s-Filtro, o par�metro iframe=1 j� � outro macete 
            porque apesar dessa tela n�o ter sido aberta como Pop-UP eu n�o quero que a mesma me apresente o Menu ...-->
            <iframe name = 'iframe_oes' src = '../../producao/oes/alterar.php?id_produto_acabado=<?=$_GET['id_produto_acabado'];?>&cmd_consultar=Consultar&iframe=1' width='100%' height='250'></iframe>
        </td>
    </tr>
</table>
</form>
</body>
</html>