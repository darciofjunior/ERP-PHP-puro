<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

if(isset($_POST['txt_observacao'])) {
?>
    <Script Language = 'Javascript'>
        //Aqui leva o parâmetro da observação que foi digitada pelo usuário ...
        window.top.opener.parent.itens.location = 'itens.php?id_pedido=<?=$_POST['id_pedido'];?>&chkt_item_pedido=<?=$_POST['chkt_item_pedido'];?>&txt_qtde=<?=$_POST['txt_qtde'];?>&obs_requisicao=<?=$_POST['txt_observacao'];?>'
        window.close()
    </Script>
<?
}

//Busca a razão social do Fornecedor ...
$sql = "SELECT f.razaosocial 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor AND f.`ativo` = '1' 
        WHERE p.`id_pedido` = '$_GET[id_pedido]' ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Incluir Observação p/ Requisição ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_observacao.focus()'>
<form name='form' method='post' action=''>
<input type='hidden' name='id_pedido' value="<?=$_GET['id_pedido'];?>">
<input type='hidden' name='chkt_item_pedido' value="<?=$_GET['chkt_item_pedido'];?>">
<input type='hidden' name='txt_qtde' value="<?=$_GET['txt_qtde'];?>">
<table border="0" width="700" cellspacing ='1' cellpadding='1' align="center">
    <tr class="atencao" align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Observação p/ Requisição do Pedido Nº 
            <font color='yellow'>
                <?=$_GET['id_pedido'];?>
            </font>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Número do Pedido:
        </td>
        <td>
            <?=$_GET['id_pedido'];?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Fornecedor:
        </td>
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Observação:
        </td>
        <td>
            <textarea name="txt_observacao" rows='3' cols='85' maxlength='255' class="caixadetexto"></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class="botao">
        </td>
    </tr>
</table>
</html>