<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/apv/apv.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

$sql = "SELECT id_pedido_venda, data_emissao 
        FROM `pedidos_vendas` 
        WHERE `id_cliente` = '$_GET[id_cliente]' 
        AND `livre_debito` = 'S' ORDER BY data_emissao DESC ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Pedido(s) Livre(s) de Débito(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<body>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class="linhacabecalho" align="center">
        <td colspan='5'>
            <font color='#FFFFFF'>
                Pedido(s) Livre(s) de Débito(s) do Cliente<br>
                <font color='yellow'>
                <?
                    $sql = "SELECT CONCAT(razaosocial, '(', nomefantasia, ')') AS cliente 
                            FROM `clientes` 
                            WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
                    $campos_cliente = bancos::sql($sql);
                    echo $campos_cliente[0]['cliente'];
                ?>
                </font>
            </font>
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            &nbsp;
        </td>
        <td>
            <font title="N.&ordm; Pedido">
                N.&ordm; Ped
            </font>
        </td>
        <td>
            <font title="Data de Emissão">
                Data Em.
            </font>
        </td>
        <td>
            Follow UP
        </td>  
        <td>
            Análise
        </td>              
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td width='10'>
            <?=($linhas - $i);?>) 
        </td>
        <td>
            <a href="javascript:nova_janela('../../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>', 'CONSULTAR', '', '', '', '', '450', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" class="link">
                <font title="Pedido em Aberto">
                    <?=$campos[$i]['id_pedido_venda'];?>
                </font>
            </a>
        </td>  
        <td>
            <font title="Pedido em Aberto">
                <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
            </font>
        </td>   
        <td align='left'>
        <?
            //Busco todos os Follow-UP(s) registrado(s) do Pedido de Venda ...
            $sql = "SELECT observacao 
                    FROM `follow_ups` 
                    WHERE identificacao = '".$campos[$i]['id_pedido_venda']."'
                    AND `origem` = 2";
            $campos_follow_up = bancos::sql($sql);
            $linhas_follow_up = count($campos_follow_up);
            for($j = 0; $j < $linhas_follow_up; $j++) echo '* '.$campos_follow_up[$j]['observacao'].'<br>';
        ?>
        </td>
        <td align='left'>
        <?
            //Aqui eu busco todos os itens do Pedido do atual do Loop ...
            $sql = "SELECT id_produto_acabado 
                    FROM `pedidos_vendas_itens` 
                    WHERE id_pedido_venda = '".$campos[$i]['id_pedido_venda']."' ";
            $campos_pedidos = bancos::sql($sql);
            $linhas_pedidos = count($campos_follow_up);
            for($j = 0; $j < $linhas_pedidos; $j++) {
                //Verifico se existe o mesmo PA em um outro Pedido do mesmo Cliente e que seja Livre de Débito ...
                $sql = "SELECT id_pedido_venda_item 
                        FROM pedidos_vendas_itens pvi 
                        INNER JOIN pedidos_vendas pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pvi.id_produto_acabado = '".$campos_pedidos[$j]['id_produto_acabado']."' 
                        WHERE pv.livre_debito = 'S' 
                        AND pv.id_cliente = '$_GET[id_cliente]' 
                        AND pv.id_pedido_venda <> '".$campos[$i]['id_pedido_venda']."' LIMIT 1 ";
                $campos_outro_livre_debito = bancos::sql($sql);
                if(count($campos_outro_livre_debito) == 1) {//Significa que existe outro Livre de Débito ...
                    //Verifico se esse Cliente já fez alguma compra desse PA que foi para Amostra pelo menos pela 2ª vez ...
                    $sql = "SELECT pvi.id_pedido_venda, pv.data_emissao 
                            FROM pedidos_vendas_itens pvi 
                            INNER JOIN pedidos_vendas pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pvi.id_produto_acabado = '".$campos_pedidos[$j]['id_produto_acabado']."' 
                            AND pv.id_cliente = '".$_GET['id_cliente']."' LIMIT 1 ";
                    $campos_venda = bancos::sql($sql);
                    $linhas_venda = count($campos_venda);
                    if($linhas_venda == 1) {
        ?>
                N.º Pedido de Venda -> 
            <a href="javascript:nova_janela('../../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos_venda[0]['id_pedido_venda'];?>', 'CONSULTAR', '', '', '', '', '450', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" class="link">
                <?=$campos_venda[0]['id_pedido_venda'];?>
            </a>
        <?
                    }else {
                        echo '<font color="red"><b>Livre de Débito >= 2. <br>Nunca houve venda deste PA -> '.intermodular::pa_discriminacao($campos_pedidos[$j]['id_produto_acabado'], 0, 0, 0, 0, 1).'.</b></font>';
                    }
                }
            }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>