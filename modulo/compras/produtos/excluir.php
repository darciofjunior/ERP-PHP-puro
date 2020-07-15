<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    foreach($_POST['chkt_produto_insumo'] as $id_produto_insumo) {
//Aki estou desatrelando o PI do PA ...
        $sql = "UPDATE `produtos_acabados` SET `id_produto_insumo` = NULL WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        bancos::sql($sql);
//Excluindo - "Ocultando" o PI do Sistema ...
        $sql = "UPDATE `produtos_insumos` SET `ativo` = '0' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        bancos::sql($sql);
//Desativa os PIs que foram excluídos dos Fornecedores na Lista de Preço ...
        $sql = "SELECT id_fornecedor_prod_insumo 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' ";
        $campos_lista = bancos::sql($sql);
        $linhas_lista = count($campos_lista);
        for($i = 0; $i < $linhas_lista; $i ++) {
            $sql = "UPDATE `fornecedores_x_prod_insumos` SET `ativo` = '0' WHERE `id_fornecedor_prod_insumo` = '".$campos_lista[$i]['id_fornecedor_prod_insumo']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php?valor=2'
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../';
//Aqui eu vou puxar a Tela única de Filtro de Notas Fiscais que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Excluir Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar_checkbox('form','SELECIONE UMA OPÇÃO !')">
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="4">
            Excluir Produto(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Grupo
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Itens <input type='hidden' name='chkt_tudo'>
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
        <?
//Aki verifica se o PI está sendo utilizado em lugares comprometedores
            if(cascate::consultar('id_produto_insumo', 'itens_pedidos', $campos[$i]['id_produto_insumo']) == 1) {
//Aki eu verifico a situação do PI em relação ao Estoque
        ?>
                <a href="javascript:nova_janela('../../classes/produtos_insumos/detalhes_producao.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title="Locais Atrelados" class="link">?</a>
                <!--Esse objeto é para não dar erro de JS-->
                <input type='hidden'>
        <?
            }else {
//Aqui verifica se o PI está atrelado em alguma etapa do custo
//Significa que esse PI não está atrelado a nenhuma etapa do Custo
                if(cascate::consultar('id_produto_insumo', 'pas_vs_pis_embs, produtos_acabados_custos, pacs_vs_pis, pacs_vs_pis_trat, pacs_vs_pis_usis', $campos[$i]['id_produto_insumo']) == 0) {
//Aki eu verifico a situação do PI em relação ao Estoque
                    $sql = "SELECT qtde 
                            FROM `estoques_insumos`
                            WHERE `id_produto_insumo` = ".$campos[$i]['id_produto_insumo']." LIMIT 1 ";
                    $campos_estoque = bancos::sql($sql);
                    if($campos_estoque[0]['qtde'] == 0) {//Se a qtde == 0, então posso excluir esse PI
        ?>
                        <input type='checkbox' name='chkt_produto_insumo[]' value="<?=$campos[$i]['id_produto_insumo'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        <?
                    }else {//Não posso excluir ainda, pois não está zerada a qtde em Est.
        ?>
                        <a href="javascript:nova_janela('../../classes/produtos_insumos/detalhes_producao.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title="Locais Atrelados" class="link">?</a>
                        <!--Esse objeto é para não dar erro de JS-->
                        <input type='hidden'>
        <?
                    }
//Não posso excluir ainda, pois o PI está atrelado à alguma etapa do Custo
                }else {
        ?>
                    <a href="javascript:nova_janela('../../classes/produtos_insumos/detalhes_producao.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title="Locais Atrelados" class="link">?</a>
                    <!--Esse objeto é para não dar erro de JS-->
                    <input type='hidden'>
        <?
                }
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>