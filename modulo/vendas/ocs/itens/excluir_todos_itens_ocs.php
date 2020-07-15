<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) ITEM(NS) EM ABERTO PARA EXCLUIR NESTE ORÇAMENTO.</font>";

if($passo == 1) {
//Disparo do Loop
    foreach ($_POST['chkt_oc_item'] as $id_oc_item) {
        $sql = "DELETE FROM `ocs_itens` WHERE `id_oc_item` = '$id_oc_item' LIMIT 1 ";//Excluindo o Item da OC ...
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        alert('TODO(S) O(S) ITEM(NS) SELECIONADO(S) FORAM EXCLUÍDO(S) COM SUCESSO !')
        parent.location = 'itens.php?id_oc=<?=$_POST['id_oc'];?>'
    </Script>
<?
}else {
//Seleciona todos os itens em "Aberto" do id_oc passado por parâmetro ...
    $sql = "SELECT oi.`id_oc_item`, oi.`qtde`, oi.`status`, pa.`id_produto_acabado`, pa.`referencia`, 
            pa.`discriminacao`, pa.`operacao_custo` 
            FROM `ocs_itens` oi 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = oi.id_produto_acabado 
            WHERE oi.`id_oc` = '$_GET[id_oc]' ORDER BY oi.id_oc_item ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
<html>
<html>
<title>.:: Excluir Itens de OC ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<body>
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_oc=<?=$_GET['id_oc'];?>'" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: Excluir Itens de OC ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_excluir_itens_oc.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox')  {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
//Confirmando ...
        var mensagem = confirm('DESEJA EXCLUIR O(S) ITEM(NS) SELECIONADO(S) ?')
        if(!mensagem == true) return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar('<?=$total_itens_pedidos;?>')">
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Item(ns) em Aberto da OC - N.º&nbsp;
            <font color='yellow'>
                <?=$_GET['id_oc'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            <font title='Quantidade' style='cursor:help'>
                Qtde
            </font>
        </td>
        <td>
            Produto
        </td>
        <td>
            <font title='Operação de Custo' style='cursor:help'>
                O.C.
            </font>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $disabled = ($campos[$i]['status'] > 0) ? 'disabled' : '';
?>
    <tr class='linhanormal' onclick="checkbox('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_oc_item[]' id='chkt_oc_item<?=$i;?>' value="<?=$campos[$i]['id_oc_item'];?>" onclick="checkbox('<?=$i;?>', '#E8E8E8')" class='checkbox' <?=$disabled;?>>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'].' * '.intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
        </td>
        <td>
            <?if($campos[$i]['operacao_custo'] == 0) {echo 'I';}else {echo 'R';}?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_oc=<?=$_GET['id_oc'];?>'" class='botao'>
            <input type='submit' name='cmd_excluir_item' value='Excluir Item(ns)' title='Excluir Item(ns)' style="color:green" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
<input type='hidden' name='id_oc' value='<?=$_GET['id_oc'];?>'>
</form>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* O(s) item(ns) que está(ão) com o(s) checkbox(s) travado(s) se refere(m) à item(ns) que estão com algum status de Follow-UP registrado.
</pre>
</pre>
<?}?>