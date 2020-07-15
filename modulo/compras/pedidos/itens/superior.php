<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

$sql = "SELECT f.id_fornecedor, f.razaosocial 
	FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` AND f.`ativo` = '1' 
	WHERE p.id_pedido = '$_GET[id_pedido]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_fornecedor  = $campos[0]['id_fornecedor'];
$razaosocial    = $campos[0]['razaosocial'];
?>
<html>
<head>
<title>.:: Incluir Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        top.opener.parent.itens.document.form.submit()
        top.opener.parent.rodape.document.form.submit()
    }
}

function valor(resultado) {
    if(parent.fornecedor_produto.document.form.cmb_produto_insumo.value == '') {
        if(resultado == 1) {//Redefine a Tela
            parent.inferior_produto.document.location = 'inferior.php?id_pedido=<?=$_GET['id_pedido'];?>'
        }
    }else {
        parent.inferior_produto.document.location = 'inferior.php?id_pedido=<?=$_GET['id_pedido'];?>&id_fornecedor=<?=$id_fornecedor;?>&cmb_produto_insumo='+document.form.cmb_produto_insumo.value
    }
}
</Script>
</head>
<body topmargin='20' onload='return valor(0)' onunload="atualizar_abaixo()">
<form name='form'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Itens para o Pedido N.º 
            <font color='yellow'>
                <?=$_GET[id_pedido];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor:
        </td>
        <td>
            <?=$razaosocial;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto Insumo:</b>
        </td>
        <td>
            <select name='cmb_produto_insumo' title='Selecione o Produto Insumo' onchange='return valor(1)' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
            <?
                    //Busco dados de um PI que está ativo na Lista de Preços do Fornecedor do Pedido ...
                    $sql = "SELECT g.referencia, pi.id_produto_insumo, pi.discriminacao 
                            FROM `produtos_insumos` pi 
                            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                            INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.id_produto_insumo = pi.id_produto_insumo AND fpi.id_fornecedor = '$id_fornecedor' AND fpi.ativo = '1' 
                            ORDER BY pi.discriminacao ";
                    $campos_produtos    = bancos::sql($sql);
                    $linhas             = count($campos_produtos);
                    for($i = 0; $i < $linhas; $i++) {
                        $id_produto_insumo_loop = $campos_produtos[$i]['id_produto_insumo'];
                        $referencia             = genericas::buscar_referencia($campos_produtos[$i]['id_produto_insumo'], $campos_produtos[$i]['referencia'], 0);
                        $discriminacao          = $campos_produtos[$i]['discriminacao'];
                        //Aqui já são as demais vezes, ...
                        if(!empty($cmb_produto_insumo)) {
                            if($cmb_produto_insumo == $id_produto_insumo_loop) {
?>
                    <option value="<?=$id_produto_insumo_loop;?>" selected><?=$referencia.' * '.$discriminacao;?></option>
<?
                            }else {
?>
                    <option value="<?=$id_produto_insumo_loop;?>"><?=$referencia.' * '.$discriminacao;?></option>
<?
                            }
//Quando carrega a Tela na primeira vez
                        }else {
?>
                    <option value="<?=$id_produto_insumo_loop;?>"><?=$referencia.' * '.$discriminacao;?></option>
<?
                        }
                    }
            ?>
            </select>
            &nbsp;&nbsp;&nbsp;<input type='button' name='consultar' value='Consultar' title="Consultar Produto" onclick="nova_janela('consultar_produtos.php?id_pedido=<?=$_GET['id_pedido'];?>&id_fornecedor=<?=$id_fornecedor;?>', 'CONSULTAR', '', '', '', '', '400', '750', 'c', 'c')" class="botao">
        </td>
    </tr>
</table>
<input type='hidden' name='nao_atualizar'>
</form>
</body>
</html>