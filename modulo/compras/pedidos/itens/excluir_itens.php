<?
require('../../../../lib/segurancas.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../lib/estoque_acabado.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../lib/genericas.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

if($passo == 1) {
    //Verifico se esse Pedido possui uma OS Atrelada ...
    $sql = "SELECT `id_os` 
            FROM `oss` 
            WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    $campos_os = bancos::sql($sql);
    if(count($campos_os) == 1) {//Se sim ...
        //Aki eu vasculho se tem pelo menos um Item de Pedido q já esteja importado na Nota Fiscal de Compras ...
        $sql = "SELECT `id_item_pedido` 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$_POST[id_pedido]' 
                AND `status` > '0' LIMIT 1 ";
        $campos_itens = bancos::sql($sql);
        $linhas_itens = count($campos_itens);
        if($linhas_itens == 0) {//Não existe nenhum item que esteja em NF, então posso excluir ...
            //Mudo o status da OS para em aberto p/ que esta possa ser importada futuramente ...
            //Desatrelo o id_item_pedido na tabela de 'oss_itens' da OS que está voltando a ficar com o Status em aberto
            $sql = "UPDATE `oss_itens` SET `id_item_pedido` = NULL WHERE `id_os` = '".$campos_os[0]['id_os']."' ";
            bancos::sql($sql);
            //Desatrelo o id_pedido da OS para que essa possa ser importada novamente ...
            $sql = "UPDATE `oss` SET `id_pedido` = NULL WHERE `id_os` = '".$campos_os[0]['id_os']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    
    //Se não for array, transforma em Array, isso só acontecerá nos casos em que o sistema deleta todos os Itens de OS ...
    if(!is_array($_POST['chkt_item_pedido'])) $_POST['chkt_item_pedido'] = explode(',', $_POST['chkt_item_pedido']);
    
    foreach($_POST['chkt_item_pedido'] as $id_item_pedido) {
        //Faço a busca do id_produto_insumo do $id_item_pedido p/ passar mais abaixo por parâmetro ...
        $sql = "SELECT `id_produto_insumo`, `id_cotacao_item` 
                FROM `itens_pedidos` 
                WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        $campos_item_pedido = bancos::sql($sql);
        
        //Deleta o $id_item_pedido selecionado ...
        $sql = "DELETE FROM `itens_pedidos` WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        bancos::sql($sql);
        
        /**********************************************************************/
        /***************Lógica para Concluir o Item e a Cotação****************/
        /**********************************************************************/
        if($campos_item_pedido[0]['id_cotacao_item'] > 0) {//Existia item de Cotação vinculado à item de Pedido ...
            compras_new::atualizar_status_item_cotacao($campos_item_pedido[0]['id_cotacao_item']);
            //Busco o "id_cotacao" através do "id_cotacao_item" ...
            $sql = "SELECT `id_cotacao` 
                    FROM `cotacoes_itens` 
                    WHERE `id_cotacao_item` = '".$campos_item_pedido[0]['id_cotacao_item']."' LIMIT 1 ";
            $campos_cotacao_item = bancos::sql($sql);
            compras_new::atualizar_status_cotacao($campos_cotacao_item[0]['id_cotacao']);
        }
        //Aqui verifico se o PI é um PA "PIPA" para poder executar a função abaixo ...
        $sql = "SELECT `id_produto_acabado` 
                FROM `produtos_acabados` 
                WHERE `id_produto_insumo` = '".$campos_item[0]['id_produto_insumo']."' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_pipa = bancos::sql($sql);
        if(count($campos_pipa) == 1) intermodular::gravar_campos_para_calcular_margem_lucro_estimada($campos_item[0]['id_produto_insumo']);
    }
    /***********************************************************/
    /***************Lógica para Concluir o Pedido***************/
    /***********************************************************/
    //Verifico se o Pedido possui pelo menos 1 item ...
    $sql = "SELECT `id_item_pedido` 
            FROM `itens_pedidos` 
            WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    $campos_item_pedido = bancos::sql($sql);
    if(count($campos_item_pedido) == 1) {//Significa que existe pelo menos 1 item ...
        //Verifico se existe algum item que esteja em aberto ou Parcial nesse Pedido ...
        $sql = "SELECT `id_item_pedido` 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$_POST[id_pedido]' 
                AND `status` < '2' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//Como não encontrou nenhum item nessa situação, então posso concluir o Pedido ...
            $sql = "UPDATE `pedidos` SET `status` = '2' WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    /***********************************************************/
?>
    <Script Language = 'JavaScript'>
        alert('ITEM(NS) DE PEDIDO EXCLUÍDO(S) COM SUCESSO !')
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
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
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
    /************************************************************************************************/
    /***************************************Procedimento de OS***************************************/
    /************************************************************************************************/
    //Verifico se esse Pedido possui uma OS Atrelada ...
    $sql = "SELECT id_os 
            FROM `oss` 
            WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    $campos_os = bancos::sql($sql);
    if(count($campos_os) == 1) {//Se sim ...
        //Aki eu vasculho se tem pelo menos um Item de Pedido q já esteja importado na Nota Fiscal de Compras ...
        $sql = "SELECT id_item_pedido 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$_GET[id_pedido]' 
                AND `status` > '0' LIMIT 1 ";
        $campos_itens = bancos::sql($sql);
        $linhas_itens = count($campos_itens);
        if($linhas_itens == 1) {//Se existir pelo menos 1 item que esteja em NF, então não posso excluir nada ...
?>
    <tr class='erro' align='center'>
        <td colspan='6'>
            NENHUM ITEM DE PEDIDO PODE SER EXCLUÍDO !\nALÉM DESTE PEDIDO POSSUIR UMA OS IMPORTADA, ESTE JÁ CONTÉM ALGUM(NS) ITEM(NS) IMPORTADO(S) P/ NOTA FISCAL !
        </td>
    </tr>
<?
            exit;
        }else {//Ainda não está em NF ...
            //Busco todos os itens do Pedido passado por parâmetro ...
            $sql = "SELECT id_item_pedido 
                    FROM `itens_pedidos` 
                    WHERE `id_pedido` = '$_GET[id_pedido]' ";
            $campos_itens = bancos::sql($sql);
            $linhas_itens = count($campos_itens);
            for($i = 0; $i < $linhas_itens; $i++) $id_item_pedidos.= $campos_itens[$i]['id_item_pedido'].', ';
            $id_item_pedidos = substr($id_item_pedidos, 0, strlen($id_item_pedidos) - 2);
?>
    <!--***************************Controle de Tela***************************-->
    <!--Esses hidden serão submetidos p/ a Próxima Tela ...-->
    <input type='hidden' name='chkt_item_pedido' value='<?=$id_item_pedidos;?>'>
    <input type='hidden' name='id_pedido' value='<?=$_GET[id_pedido];?>'>
    <!--**********************************************************************-->
    <Script Language = 'JavaScript'>
        var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR TODO(S) O(S) ITEM(NS) DE PEDIDO ?')
        if(resposta == true) {
            document.form.submit()
        }else {
            window.close()
        }
    </Script>
<?
            exit;
        }
    }
    /************************************************************************************************/
    /**************************************Procedimento da Tela**************************************/
    /************************************************************************************************/
    //Aqui eu busco todos os Itens em aberto do Pedido passado por parâmetro ...
    $sql = "SELECT g.referencia, ip.id_item_pedido, ip.id_produto_insumo, ip.preco_unitario, 
            ip.qtde, ip.marca, pi.discriminacao 
            FROM `itens_pedidos` ip 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
            WHERE ip.`id_pedido` = '$_GET[id_pedido]' 
            AND ip.`status` = '0' ORDER BY pi.discriminacao, ip.id_item_pedido ";
    $campos_itens = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas_itens = count($campos_itens);
    if($linhas_itens == 0) {//Não existem Itens em aberto ...
?>
    <tr class='erro' align='center'>
        <td colspan='6'>
            NÃO EXISTE(M) ITEM(NS) DE PEDIDO EM ABERTO P/ SER(EM) EXCLUÍDO(S).
        </td>
    </tr>
<?
    }else {//Existe pelo menos um item em Aberto ...
?>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Item(ns) do Pedido N.º 
            <font color='yellow'>
                <?=$_GET['id_pedido'];?>
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
            <input type='checkbox' name='chkt_item_pedido[]' id='chkt_item_pedido<?=$i;?>' value='<?=$campos_itens[$i]['id_item_pedido'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
        <?
            $referencia = genericas::buscar_referencia($campos_itens[$i]['id_produto_insumo'], $campos_itens[$i]['referencia'], 0);
            echo $referencia.' * '.$campos_itens[$i]['discriminacao'];
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos_itens[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos_itens[$i]['preco_unitario'], 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos_itens[$i]['qtde'] * $campos_itens[$i]['preco_unitario'], 2, ',', '.');?>
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
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick="fechar(window)" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<!--Controle de Tela-->
<input type='hidden' name='id_pedido' value='<?=$_GET[id_pedido];?>'>
<!--****************-->
</form>
</body>
</html>
<?
    }
}
?>