<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca 'compras_new' ...
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../../lib/producao.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

if($passo == 1) {
//Disparo do Loop
    foreach($_POST['chkt_nfe_historico'] as $id_nfe_historico) {
//Eu busco o id_item_pedido para verificar se esse é um Produto ou apenas um Ajuste ...
        $sql = "SELECT `id_item_pedido` 
                FROM `nfe_historicos` 
                WHERE `id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $id_item_pedido = $campos[0]['id_item_pedido'];
/*Verifico se o item com a qual está sendo excluído possui o id 1340 ou 1426, q na realidade são os ajustes, 
sendo assim eu deleto estes também da tab. pedidos*/
        $sql = "SELECT `id_produto_insumo` 
                FROM `itens_pedidos` 
                WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        $campos_produto_insumo = bancos::sql($sql);
/**********************************Ajuste da Nota Fiscal**********************************/
        if($campos_produto_insumo[0]['id_produto_insumo'] == 1340 || $campos_produto_insumo[0]['id_produto_insumo'] == 1426) {
//Deletou o item da Nota Fiscal
            $sql = "DELETE FROM `nfe_historicos` WHERE `id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
            bancos::sql($sql);
//Busca do id_pedido, vou precisar dele + abaixo ...
            $sql = "SELECT `id_pedido` 
                    FROM `itens_pedidos` 
                    WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
            $campos_pedido  = bancos::sql($sql);
            $id_pedido      = $campos_pedido[0]['id_pedido'];
//Deletou o item de Pedido ...
            $sql = "DELETE FROM `itens_pedidos` WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
            bancos::sql($sql);
//Verifico a qtde de Itens que ainda restaram no Pedido
            $sql = "SELECT COUNT(`id_item_pedido`) AS qtde_itens_pedidos 
                    FROM `itens_pedidos` 
                    WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
            $campos_qtde_itens = bancos::sql($sql);
            if(count($campos_qtde_itens) == 0) {//Se não existir + nenhum Item, eu volto a Sit. do Ped. p/ Aberto
                $sql = "UPDATE `pedidos` SET `status` = '0' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
                bancos::sql($sql);
            }
/*****************************************************************************************/
        }else {
//1)***********************************************OS*************************************************/
            $sql = "SELECT `id_os`, `id_os_item` 
                    FROM `oss_itens` 
                    WHERE `id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) {//Se achar
                $id_os_item = $campos[0]['id_os_item'];//Vou utilizar esse id na Função ...
                $id_os      = $campos[0]['id_os'];//Vou utilizar esse id na Função ...
                
                //Desatrelo do item de Entrada da OS o id_nfe_historico que foi excluído acima ...
                $sql = "UPDATE `oss_itens` SET `id_nfe_historico` = NULL WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
                bancos::sql($sql);
                
                //Essa função serve tanto para o Incluir, como Alterar e Excluir Item da Nota Fiscal ...
                producao::atualizar_status_item_os($id_os_item);//Aqui atualiza o status do Item de Entrada ...
/*****************************************************************************************************/
/*****************Controle com o Status da OS*****************/
                producao::atualizar_status_os($id_os);
/*************************************************************/
            }
//2)*******************************************Nota Fiscal********************************************/
//Deleto o item de Entrada da OS na Nota Fiscal ...
            $sql = "DELETE FROM `nfe_historicos` WHERE `id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
            bancos::sql($sql);
//Voltou o status do Item de Pedido para 0, para q este possa ser importado futur.
            compras_new::pedido_status($id_item_pedido);
        }
    }
//Aqui eu verifico a NF possui formas de Vencimento ...
    $sql = "SELECT `id_nfe_financiamento` 
            FROM `nfe_financiamentos` 
            WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
    $campos_financiamento = bancos::sql($sql);
//Se existir então chama a função, toda vez q excluir 1 item p/ recalcular as parcelas ...
    if(count($campos_financiamento) == 1) {
/*Toda vez que eu excluir os Itens eu garanto q o Sistema está zerando os Prazos de Vencimento do Modo 
Antigo p/ não dar conflitos com o JavaScript no cabeçalho da NF ...*/
        $sql = "UPDATE `nfe` SET `valor_a` = '0', `valor_b` = '0', `valor_c` = '0' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        bancos::sql($sql);
/*********************************************/
/*Essa função pega o valor da Nota Fiscal, e desconta desse valor, o valor total das antecipações e 
e divide o valor restante de acordo com a Qtde de Prazos ...*/
        compras_new::calculo_valor_financiamento($_POST['id_nfe']);
/*********************************************/
    }
    //Após a exclusão dos Itens, aqui eu verifico se ainda existe algum item incluso nessa NFe ...
    $sql = "SELECT `id_nfe_historico` 
            FROM `nfe_historicos` 
            WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
    $campos_nfe = bancos::sql($sql);
    if(count($campos_nfe) == 0) {//Como não existe nenhum Item então mudo a Situação da NFe p/ em Aberto ...
        $sql = "UPDATE `nfe` SET `situacao` = '0' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
<Script Language = 'JavaScript'>
    alert('ITEM(NS) DE NOTA FISCAL EXCLUÍDO(S) COM SUCESSO !')
    window.opener.parent.itens.document.form.submit()
    window.opener.parent.rodape.document.form.submit()
    window.close()
</Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Item(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        return true
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    //Verifica a situação da Nota Fiscal ...
    $sql = "SELECT num_nota, situacao 
            FROM `nfe` 
            WHERE `id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if($campos[0]['situacao'] == 2) {//Nota Fiscal liberada em Estoque ...
?>
    <tr class='erro' align='center'>
        <td colspan='6'>
            NENHUM ITEM DE NOTA FISCAL PODE SER EXCLUÍDO ! PORQUE ESTA NOTA FISCAL JÁ ESTÁ LIBERADA EM ESTOQUE !
        </td>
    </tr>
<?
        exit;
    }else {//Nota Fiscal em Aberto ...
        /************************************************************************************************/
        /**************************************Procedimento da Tela**************************************/
        /************************************************************************************************/
        //Aqui eu busco todos os Itens em aberto do Pedido passado por parâmetro ...
        $sql = "SELECT g.referencia, nfeh.id_nfe_historico, nfeh.id_produto_insumo, 
                nfeh.qtde_entregue, nfeh.valor_entregue, nfeh.marca, pi.discriminacao 
                FROM `nfe_historicos` nfeh 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = nfeh.`id_produto_insumo` 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                WHERE nfeh.`id_nfe` = '$_GET[id_nfe]' 
                AND nfeh.`status` = '0' ";
        $campos_itens = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
        $linhas_itens = count($campos_itens);
        if($linhas_itens == 0) {//Não existem Itens em aberto ...
?>
    <tr class='erro' align='center'>
        <td colspan='6'>
            NÃO EXISTE(M) ITEM(NS) DE NOTA FISCAL EM ABERTO P/ SER(EM) EXCLUÍDO(S).
        </td>
    </tr>
<?
        }else {//Existe pelo menos um item em Aberto ...
?>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Item(ns) da Nota Fiscal N.º 
            <font color='yellow'>
                <?=$campos[0]['num_nota'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Produto
        </td>
        <td>
            Qtde Solicitada
        </td>
        <td>
            Pre&ccedil;o Unit&aacute;rio
        </td>
        <td>
            Valor Total
        </td>
        <td>
            Marca / Obs
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas_itens; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_nfe_historico[]' id='chkt_nfe_historico<?=$i;?>' value='<?=$campos_itens[$i]['id_nfe_historico'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
        <?
            $referencia = genericas::buscar_referencia($campos_itens[$i]['id_produto_insumo'], $campos_itens[$i]['referencia'], 0);
            echo $referencia.' * '.$campos_itens[$i]['discriminacao'];
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos_itens[$i]['qtde_entregue'], 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos_itens[$i]['valor_entregue'], 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos_itens[$i]['qtde_entregue'] * $campos_itens[$i]['valor_entregue'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos_itens[$i]['marca'];?>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<!--Controle de Tela-->
<input type='hidden' name='id_nfe' value='<?=$_GET[id_nfe];?>'>
<!--****************-->
</form>
</body>
</html>
<?
        }
    }
}
?>