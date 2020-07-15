<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../../');

//Aqui é quando o usuário deseja excluir algum Item "PA" da Caixa Secundária ...
if(!empty($_GET['id_packing_list_item'])) {
    $sql = "DELETE FROM `packings_lists_itens` WHERE `id_packing_list_item` = '$_GET[id_packing_list_item]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('PRODUTO ACABADO EXCLUÍDO DA CAIXA SECUNDÁRIA COM SUCESSO !')
        if(typeof(top.parent) == 'object') top.parent.window.location = top.parent.location.href
    </Script>
<?
}
?>
<html>
<head>
<title>.:: Relatório de Packing List ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_packing_list_item) {
    var resposta = confirm('DESEJA EXCLUIR ESSE PRODUTO ACABADO DESSA CAIXA SECUNDÁRIA ?')
    if(resposta == true) window.location = 'relatorio.php?id_packing_list=<?=$_GET['id_packing_list'];?>&id_packing_list_item='+id_packing_list_item
}
</Script>
</head>
<body>
<table width='98%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='6'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Relatório de Packing List N.º 
            <font color='yellow'>
                <?=$_GET['id_packing_list'];?>
            </font>
        </td>
    </tr>
<?
//Essas variáveis serão utilizadas mais abaixo ...
    $peso_liquido_caixa_secundaria = 0;
//Aqui eu busco todos os Itens que compõem o Packing List ...
    $sql = "SELECT pa.peso_unitario, pli.id_packing_list_item, pli.id_produto_acabado, pli.id_produto_insumo_master, 
            pli.id_produto_insumo_secundario, pli.caixa_master_numero, pli.caixa_secundario_numero, pli.qtde 
            FROM `packings_lists_itens` pli 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pli.id_produto_acabado 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = pli.id_produto_insumo_secundario 
            WHERE pli.`id_packing_list` = '$_GET[id_packing_list]' ORDER BY pli.caixa_master_numero, pli.caixa_secundario_numero, pa.referencia, pa.discriminacao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        /*****************************Organização por Caixa Master*****************************/
        if($caixa_master_numero != $campos[$i]['caixa_master_numero']) {//Organização por N.º de Caixa Master ...
            $caixa_master_numero = $campos[$i]['caixa_master_numero'];
?>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font color='yellow'>
                <b>Caixa Master N.º <?=$campos[$i]['caixa_master_numero'];?>) </b>
            </font>
            <?
                if($campos[$i]['id_produto_insumo_master'] > 0) {
                    //Busco as medidas externas, porque é o espaço real que será utilizado no Container "Navio" ...
                    $sql = "SELECT discriminacao, peso, altura_externo, largura_externo, comprimento_externo 
                            FROM `produtos_insumos` 
                            WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo_master']."' LIMIT 1 ";
                    $campos_caixa_master = bancos::sql($sql);
                    $volume_caixa_master = ($campos_caixa_master[0]['altura_externo'] * $campos_caixa_master[0]['largura_externo'] * $campos_caixa_master[0]['comprimento_externo'] / pow(10, 9));
                    echo $campos_caixa_master[0]['discriminacao'];
                }
            ?>
        </td>
        <td>
            <font color='yellow'>
                <b>(Peso <?=number_format($campos_caixa_master[0]['peso'], 4, ',', '.');?> Kgs) </b>
            </font>
        </td>
        <td>
            <font color='yellow'>
                <b>(Vol. <?=number_format($volume_caixa_master, 4, ',', '.');?> m³) </b>
            </font>
        </td>
        <td>
            <img src="../../../../../../imagem/menu/alterar.png" border='0' onClick="nova_janela('alterar_caixa_master.php?id_packing_list_item=<?=$campos[$i]['id_packing_list_item'];?>', 'POP', '', '', '', '', 150, 780, 'c', 'c', '', '', 's', 's', '', '', '')" alt="Alterar Caixa Master" title="Alterar Caixa Master">
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
        }
        /**************************************************************************************/
        /***************************Organização por Caixa Secundária***************************/
        if($caixa_secundario_numero != $campos[$i]['caixa_secundario_numero']) {//Organização por N.º de Caixa Secundária ...
            $caixa_secundario_numero = $campos[$i]['caixa_secundario_numero'];
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            <font color='yellow'>
                <b>Caixa Secundária N.º <?=$campos[$i]['caixa_secundario_numero'];?>) </b>
            </font>
            <?
                $sql = "SELECT `discriminacao`, `peso` 
                        FROM `produtos_insumos` 
                        WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo_secundario']."' LIMIT 1 ";
                $campos_caixa_secundario = bancos::sql($sql);
                echo $campos_caixa_secundario[0]['discriminacao'];
            ?>
            &nbsp;
            <font color='yellow'>
                <b>(Peso <?=number_format($campos_caixa_secundario[0]['peso'], 4, ',', '.');?> Kgs) </b>
            </font>
        </td>
        <td>
            <img src = '../../../../../../imagem/menu/alterar.png' border='0' onclick="nova_janela('alterar_caixa_secundaria.php?id_packing_list_item=<?=$campos[$i]['id_packing_list_item'];?>', 'POP', '', '', '', '', 150, 780, 'c', 'c', '', '', 's', 's', '', '', '')" alt='Alterar Caixa Secundária' title='Alterar Caixa Secundária'>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='iframe' align='center'>
        <td>
            Qtde Packing List
        </td>
        <td>
            Produto
        </td>
        <td>
            Peso Unitário
        </td>
        <td>
            Peso Total
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
        }
        /**************************************************************************************/
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['qtde'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, 0, 0, 0, 1);?>
        </td>
        <td align='right'>
            <!--Esses parâmetros tela1 serve para o pop-up fazer a atualização na tela de baixo-->
            <a href="javascript:nova_janela('../../../../../classes/produtos_acabados/alterar_peso_unitario.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&tela1=window.opener', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Atualizar Peso do Produto' class='link'>
                <?=number_format($campos[$i]['peso_unitario'], 4, ',', '.');?>
            </a>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['qtde'] * $campos[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
        <td>
            <img src = '../../../../../../imagem/menu/alterar.png' border='0' onclick="window.location = 'alterar_qtde_item.php?id_packing_list_item=<?=$campos[$i]['id_packing_list_item'];?>'" alt='Alterar Quantidade do Produto Acabado da Caixa Secundária' title='Alterar Quantidade do Produto Acabado da Caixa Secundária'>
        </td>
        <td>
            <img src = '../../../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_packing_list_item'];?>')" alt='Excluir Produto Acabado da Caixa Secundária' title='Excluir Produto Acabado da Caixa Secundária'>
        </td>
    </tr>
<?
        $peso_liquido_caixa_secundaria+=    $campos[$i]['qtde'] * $campos[$i]['peso_unitario'];
        /**************************************************************************************/
        /*******************************Pesos da Caixa Secundária******************************/
        //Se a caixa Secundária atual for diferente da próxima Caixa Secundária, então já apresento o Peso Total da Caixa ...
        if($caixa_secundario_numero != $campos[$i + 1]['caixa_secundario_numero']) {
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            <font color='yellow'>
                <b>Peso Líquido da Caixa Secundária => </b>
            </font>
        </td>
        <td>
            <!--Só apresento o Total de Peso da Mercadoria que está dentro da caixa, 
            nesse caso não levo em conta o peso do própria Caixa ...-->
            <?
                echo number_format($peso_liquido_caixa_secundaria, 4, ',', '.');
                $peso_liquido_todas_caixa_master+=  $peso_liquido_caixa_secundaria;//Que na realidade é o peso do próprio PA sem as caixas de Papelão ...
            ?>
        </td>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='right'>
        <td colspan='3'>
            <font color='yellow'>
                <!--Além do Total de Peso da Mercadoria que está dentro da caixa, 
                levo em conta também o peso do própria Caixa ...-->
                <b>Peso Bruto da Caixa Secundária => </b>
            </font>
        </td>
        <td>
            <!--Sempre somo ao Total da Caixa "Todos os PAs" o Valor da Caixa Secundária ...-->
            <?=number_format($peso_liquido_caixa_secundaria + $campos_caixa_secundario[0]['peso'], 4, ',', '.');?>
        </td>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
<?
            //Essas variáveis serão utilizadas mais abaixo ...

            //1) Tratamento com as Caixas Secundárias do Lote ...
            $vetor_peso_bruto_caixa_secundaria[$caixa_secundario_numero]        = $peso_liquido_caixa_secundaria + $campos_caixa_secundario[0]['peso'];
            
            //2) Tratamento com as Caixas Master do Lote ...
            $vetor_peso_liquido_caixa_master[$caixa_master_numero]+=            $vetor_peso_bruto_caixa_secundaria[$caixa_secundario_numero];
            $vetor_peso_bruto_caixa_master[$caixa_master_numero]                = $vetor_peso_liquido_caixa_master[$caixa_master_numero] + $campos_caixa_master[0]['peso'];
            
            $peso_liquido_caixa_secundaria      = 0;//Aqui eu zero o Total da caixa Secundária para não acumular o valor dessa nas próximas Caixas ...
        }
        /**************************************************************************************/
        /*******************************Pesos da Caixa Master******************************/
        //Se a caixa Master atual for diferente da próxima Caixa Master, então já apresento o Peso Total da Caixa ...
        if($caixa_master_numero != $campos[$i + 1]['caixa_master_numero']) {
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='3'>
            <font color='yellow'>
                <b>Peso Líquido da Caixa Master => </b>
            </font>
        </td>
        <td>
            <?=number_format($vetor_peso_liquido_caixa_master[$caixa_master_numero], 4, ',', '.');?>
        </td>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhadestaque' align='right'>
        <td colspan='3'>
            <font color='yellow'>
                <b>Peso Bruto da Caixa Master => </b>
            </font>
        </td>
        <td>
            <?=number_format($vetor_peso_bruto_caixa_master[$caixa_master_numero], 4, ',', '.');?>
        </td>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
<?

//3) Tratamento com todas as Caixas Master do Packing List Inteiro ...
            $peso_bruto_todas_caixa_master+=    $vetor_peso_bruto_caixa_master[$caixa_master_numero];
            $volume_todas_caixa_master+=        $volume_caixa_master;
        }
        /**************************************************************************************/
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <?
                //Aqui eu verifico se ainda existe alguma Caixa Secundária, sem uma Caixa Master ...
                $sql = "SELECT `id_packing_list_item` 
                        FROM `packings_lists_itens` 
                        WHERE `id_packing_list` = '$_GET[id_packing_list]' 
                        AND `id_produto_insumo_master` IS NULL LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 1) {//Se ainda existe, então mostro esse Botão p/ incluir a tal Caixa Master ...
            ?>
            <input type='button' name='cmd_incluir_caixa_master' value='Incluir Caixa Master' title='Incluir Caixa Master' onclick="nova_janela('incluir_caixa_master.php?id_packing_list=<?=$_GET['id_packing_list'];?>', 'POP', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:purple' class='botao'>
            <?
                }
            ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick="fechar(parent)" class='botao'>
        </td>
    </tr>
<!--******************************************Total do Packing List******************************************-->
<?
    //Aqui eu verifico se existe pelo menos uma Caixa Master p/ Fazer o relatório de demonstração ...
    $sql = "SELECT COUNT(DISTINCT(`caixa_master_numero`)) AS qtde_caixa_master 
            FROM `packings_lists_itens` 
            WHERE `id_packing_list` = '$_GET[id_packing_list]' 
            AND `id_produto_insumo_master` > '0' ";
    $campos = bancos::sql($sql);
    if($campos[0]['qtde_caixa_master'] > 0) {
?>
</table>
<br>
<table width='98%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <b>Total do Packing List</b>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde Caixas Master
        </td>
        <td>
            Peso Bruto (Kg)
        </td>
        <td>
            Peso Líquido (Kg)
        </td>
        <td>
            Volume (m³)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[0]['qtde_caixa_master'];?>
        </td>
        <td>
            <?=number_format($peso_bruto_todas_caixa_master, 4, ',', '.');?>
        </td>
        <td>
            <?=number_format($peso_liquido_todas_caixa_master, 4, ',', '.');?>
        </td>
        <td>
            <?=number_format($volume_todas_caixa_master, 4, ',', '.');?> (m³)
        </td>
    </tr>
    <?
        //Gravo na Tabela de Packing List esse "Resumo" de Qtde de Caixas, Peso Bruto, Peso Líquido e Volume ...
        $sql = "UPDATE `packings_lists` SET `qtde_caixas` = '".$campos[0]['qtde_caixa_master']."', `peso_bruto` = '$peso_bruto_todas_caixa_master', `peso_liquido` = '$peso_liquido_todas_caixa_master', `volume` = '$volume_todas_caixa_master' WHERE `id_packing_list` = '$_GET[id_packing_list]' LIMIT 1 ";
        bancos::sql($sql);
    ?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick="nova_janela('relatorio/relatorio.php?id_packing_list=<?=$_GET['id_packing_list'];?>', 'CONSULTAR', 'F')" class='botao'>
        </td>
    </tr>
<?
    }
?>
</table>
</form>
</body>
</html>