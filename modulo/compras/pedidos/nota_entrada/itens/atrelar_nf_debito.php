<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>NOTA FISCAL DE DÉBITO ATRELADA COM SUCESSO.</font>";

if(!empty($_POST['id_nfe_historico'])) {
    if($_POST['hdd_atrelar_varios_itens'] == 1) {//Deseja atrelar a NF de Débito p/ todos os Itens da NF Corrente ...
//Aqui eu verifico quem é o Fornecedor do N.º da NF de Débito que foi escolhido pelo usuário na combo ...
        $sql = "SELECT `id_fornecedor` 
                FROM `nfe` 
                WHERE `id_nfe` = '$_POST[cmb_nota_fiscal]' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $id_fornecedor_debito   = $campos[0]['id_fornecedor'];
//Busca do id_nfe da Nota Fiscal através do id_nfe_historico corrente ...
        $sql = "SELECT id_nfe 
                FROM `nfe_historicos` 
                WHERE `id_nfe_historico` = '$_POST[id_nfe_historico]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $id_nfe = $campos[0]['id_nfe'];
/*Através do $id_nfe, eu busco todos os Itens de Nota Fiscal que vão p/ o outro Fornecedor que seje 
diferente do Fornecedor atual desta Nota Fiscal ...*/
        $sql = "SELECT ip.id_item_pedido, nfeh.id_nfe_historico 
                FROM `nfe_historicos` nfeh 
                INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
                WHERE nfeh.`id_nfe` = '$id_nfe' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
//Atrelando a NF de Débito p/ todos os Itens 
        for($i = 0; $i < $linhas; $i++) {
            $sql = "UPDATE `nfe_historicos` SET `id_nfe_debitar` = '$_POST[cmb_nota_fiscal]' WHERE `id_nfe_historico` = '".$campos[$i]['id_nfe_historico']."' LIMIT 1 ";
            bancos::sql($sql);
//Atrelo p/ todos os Itens de Pedido o mesmo Fornec do N.º da NF de Débito escolhido pelo usuário na combo 
            $sql = "UPDATE `itens_pedidos` SET `id_fornecedor` = '$id_fornecedor_debito' WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }else {//Deseja atrelar NF de Débito apenas p/ o Item Corrente ...
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $cmb_nota_fiscal = (!empty($_POST[cmb_nota_fiscal])) ? "'".$_POST[cmb_nota_fiscal]."'" : 'NULL';
        
        if(!empty($_POST['hdd_desatrelar_fornecedor_terceiro'])) {
            $sql = "UPDATE `nfe_historicos` nfeh 
                    INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
                    SET ip.`id_fornecedor` = NULL, nfeh.`id_nfe_debitar` = $cmb_nota_fiscal 
                    WHERE nfeh.`id_nfe_historico` = '$_POST[id_nfe_historico]' ";
        }else {
            $sql = "UPDATE `nfe_historicos` SET `id_nfe_debitar` = $cmb_nota_fiscal WHERE `id_nfe_historico` = '$_POST[id_nfe_historico]' LIMIT 1 ";
        }
        bancos::sql($sql);
    }
    $valor = 1;
}

//Procedimento quando carrega a Tela ...
$id_nfe_historico = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nfe_historico'] : $_GET['id_nfe_historico'];

//Busca o Fornecedor de quem eu devo debitar o produto ...
$sql = "SELECT f.id_fornecedor, f.razaosocial, nh.id_nfe_debitar 
        FROM `nfe_historicos` nh 
        INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nh.`id_item_pedido` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = ip.`id_fornecedor` 
        WHERE nh.`id_nfe_historico` = '$id_nfe_historico' ";
$campos         = bancos::sql($sql);
$id_fornecedor  = $campos[0]['id_fornecedor'];
$razaosocial    = $campos[0]['razaosocial'];
$id_nfe_debitar = $campos[0]['id_nfe_debitar'];
?>
<html>
<head>
<title>.:: Atrelar Nota Fiscal de Débito ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}

function validar() {
    if(document.form.cmb_nota_fiscal.value > 0) {//Se estiver preenchido o N.º de Nota Fiscal ...
        var resposta = confirm('DESEJA ATRELAR ESSE MESMO N.º DE NF DE DÉBITO P/ OS DEMAIS ITENS DESSA NF ?')
        if(resposta == true) document.form.hdd_atrelar_varios_itens.value = 1
    }else {//Não está preenchido o N.º de Nota Fiscal - SELECIONE ...
        var resposta = confirm('DESEJA DESATRELAR O FORNECEDOR DESTE ITEM TAMBÉM ?')
        if(resposta == true) document.form.hdd_desatrelar_fornecedor_terceiro.value = 1
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}
</Script>
</head>
<body onload='document.form.cmb_nota_fiscal.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_nfe_historico' value='<?=$id_nfe_historico;?>'>
<!--****************Controles de Tela****************-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_atrelar_varios_itens'>
<input type='hidden' name='hdd_desatrelar_fornecedor_terceiro'>
<!--*************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Atrelar Nota Fiscal de Débito
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Fornecedor:
            </font>
            <?=$razaosocial;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nota Fiscal:
        </td>
        <td>
            <select name='cmb_nota_fiscal' title='Selecione a Nota Fiscal' class='combo'>
            <?
                //Seleciono Todas as Notas Fiscais que estão em Aberto desse Fornecedor
                $sql = "SELECT id_nfe, num_nota 
                        FROM `nfe` 
                        WHERE `situacao` < '2' 
                        AND `id_fornecedor` = '$id_fornecedor' ORDER BY num_nota ";
                echo combos::combo($sql, $id_nfe_debitar);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.cmb_nota_fiscal.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>