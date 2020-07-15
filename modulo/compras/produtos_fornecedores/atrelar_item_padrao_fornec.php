<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/consultar.php', '../../../');

if(!empty($_POST['cmb_produto_insumo'])) {
    $sql = "UPDATE `fornecedores_x_prod_insumos` SET `condicao_padrao` = '1' WHERE `id_fornecedor` = '$_POST[id_fornecedor]' AND `id_produto_insumo` ='$_POST[cmb_produto_insumo]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        alert('ITEM PADRÃO ATRELADO COM SUCESSO P/ O FORNECEDOR !')
        window.opener.document.form.submit()
        window.close()
    </Script>
<?	
}
?>
<html>
<title>.:: Alterar Item Padrão p/ Fornecedor ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Produto Insumo
    if(!combo('form', 'cmb_produto_insumo', '', 'SELECIONE O PRODUTO INSUMO !')) {
        return false
    }
//Confirmação
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ATRELAR ESSE ITEM COMO SENDO PADRÃO ?')
    if(resposta == true) {
        return true
    }else {
        return false
    }
}
</Script>
<body onload='document.form.cmb_produto_insumo.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_fornecedor' value='<?=$_GET['id_fornecedor'];?>'>
<table width='98%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Item Padrão p/ Fornecedor
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Fornecedor:</b>
        </td>
        <td>
        <?
            $sql = "SELECT razaosocial 
                    FROM `fornecedores` 
                    WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
            $campos_fornecedor = bancos::sql($sql);
            echo $campos_fornecedor[0]['razaosocial'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto Insumo:</b>
        </td>
        <td>
            <select name='cmb_produto_insumo' title='Selecione um Produto Insumo' class='combo'>
            <?
//Aqui eu busco todos os PI(s) atrelados ao Fornecedor ...
                $sql = "SELECT pi.id_produto_insumo, pi.discriminacao 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` 
                        WHERE `id_fornecedor` = '$_GET[id_fornecedor]' ORDER BY pi.discriminacao ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_produto_insumo.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>