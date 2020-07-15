<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../');

if(!empty($_POST['id_fornecedor'])) {
//Somente o Roberto 62 "Diretor", Dárcio 98 e Netto 147 podem estar fazendo alterações porque programa ...
    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
//Retira a Condição Padrão de Todos os Produtos do Fornecedor Corrente ...
        $sql = "UPDATE `fornecedores_x_prod_insumos` SET `condicao_padrao` = '0' WHERE id_fornecedor = '$_POST[id_fornecedor]' ";
        bancos::sql($sql);
/*Aqui além de eu deixar a condição padrão somente p/ o produto selecionado do Fornecedor, eu já também atribuo 
o Fator de Margem de Lucro de Variáveis p/ este Produto ...*/
        $fator_margem_lucro_pa = genericas::variavel(22);//Busca das variáveis genéricas do Sistema ...
        $sql = "UPDATE `fornecedores_x_prod_insumos` SET `condicao_padrao` = '1', `fator_margem_lucro_pa` = '$fator_margem_lucro_pa' WHERE `id_fornecedor` = '$_POST[id_fornecedor]' AND `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
        bancos::sql($sql);
    }else {
?>
    <Script Language = 'JavaScript'>
        alert('ESSE USUÁRIO NÃO TEM PERMISSÃO PARA ALTERAR A CONDIÇÃO PADRÃO !')
    </Script>
<?
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_fornecedor      = $_POST['id_fornecedor'];
    $id_produto_insumo  = $_POST['id_produto_insumo'];
    $qtde_registro      = $_POST['qtde_registro'];
}else {
    $id_fornecedor      = $_GET['id_fornecedor'];
    $id_produto_insumo  = $_GET['id_produto_insumo'];
    $qtde_registro      = $_GET['qtde_registro'];
}

$sql = "SELECT `condicao_padrao` 
        FROM `fornecedores_x_prod_insumos` 
        WHERE `id_fornecedor` = '$id_fornecedor' 
        AND `id_produto_insumo` = '$id_produto_insumo' 
        AND `ativo` = '1' LIMIT 1 ";
$campos             = bancos::sql($sql);
$checked            = ($campos[0]['condicao_padrao'] == 1) ? 'checked' : '';
?>
<html>
<head>
<title>.:: Atualizar Lista de Preço ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' >
function tratar() {
    for(var i = 0; i < eval(document.form.qtde_registro.value); i++) {
        elemento = eval('parent.condicao_padrao'+i+'.document.form.chkt_condicao_padrao')
        elemento.checked = false
    }
    document.form.submit()
}
</Script>
</head>
<body background='#E8E8E8' bgcolor='#E8E8E8'>
<form name='form' method='post' action=''>
<table border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='chkt_condicao_padrao' value='1' title='Condição Padrão' onclick='tratar()' <?=$checked;?> class='checkbox'>
        </td>
    </tr>
</table>
<input type='hidden' name='qtde_registro' value='<?=$qtde_registro;?>'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='id_produto_insumo' value='<?=$id_produto_insumo;?>'>
</form>
</body>
</html>