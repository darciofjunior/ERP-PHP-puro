<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_pendentes/pedidos_pendentes.php', '../../../../');

if($_GET['tipo_filtro'] == 1) {//Sem Valor de Nota ...
    $condicao 	= " AND pv.condicao_faturamento = '3' ";
    $title      = 'SEM VALOR DE NOTA';
}else if($_GET['tipo_filtro'] == 2) {//Outros ...
    $condicao   = " AND pv.condicao_faturamento = '4' ";
    $title      = 'OUTROS';
}else if($_GET['tipo_filtro'] == 3) {//Crédito C ...
    $condicao   = " AND c.credito = 'C' ";
    $title      = 'CRÉDITO C';
}else if($_GET['tipo_filtro'] == 4) {//Crédito D ...
    $condicao   = " AND c.credito = 'D' ";
    $title      = 'CRÉDITO D';
}else if($_GET['tipo_filtro'] == 5) {//Programado ...
    $datas = genericas::retornar_data_relatorio();
    $condicao   = " AND pv.`faturar_em` > '".data::datatodate($datas['data_final'], '-')."' ";
    $title      = 'PROGRAMADO';
}

$sql = "SELECT DISTINCT(pv.`id_pedido_venda`), pv.`id_cliente_contato`, pv.`id_empresa`, pv.`faturar_em`, 
        pv.`data_emissao`, pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pv.`status`, 
        SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total_pedido, 
        IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, c.`credito` 
        FROM `pedidos_vendas` pv 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` = '0' 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`id_pais` = '31' 
        WHERE pv.`liberado` = '1' 
        $condicao 
        GROUP BY pv.`id_pedido_venda` ORDER BY pv.`id_pedido_venda` DESC ";
$campos = bancos::sql($sql);
$linhas	= count($campos);
?>
<html>
<head>
<title>.:: Detalhe(s) de Pedido(s) Pendente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'> 
            Detalhe(s) de Pedido(s) Pendente(s) - 
            <font color='yellow'>
                <?=$title;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.&ordm; Ped
        </td>
        <td>
            Cliente
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Faturar Em
        </td>
        <td>
            Vale
        </td>
        <td>
            Contato
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota<br/> / Prazo Pgto
            </font>
        </td>
        <td>
            Valor Total
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <a href="javascript:nova_janela('../../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos[$i]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes de Pedido' class='link'>
            <?
                if($campos[$i]['status'] < 2) {//Pedido em Aberto
            ?>
                    <font title="Pedido em Aberto">
                            <?=$campos[$i]['id_pedido_venda'];?>
                    </font>
            <?
                }else {//Pedido Concluído
            ?>
                    <font title="Pedido Concluído" color="red">
                            <?=$campos[$i]['id_pedido_venda'];?>
                    </font>
            <?
                }
            ?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
        <?
            if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                if($campos[$i]['faturar_em'] > $data_atual_mais_um) {
                    echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }else {
                    echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
//Aqui eu verifico se existe pelo menos 1 item desse que Pedido que contém Vale ...
            $sql = "SELECT `id_pedido_venda_item` 
                    FROM `pedidos_vendas_itens`  
                    WHERE `id_pedido_venda` = '".$campos[$i]['id_pedido_venda']."' 
                    AND `vale` > 0 LIMIT 1 ";
            $campos_vale = bancos::sql($sql);
            if(count($campos_vale) == 1) {
                echo '<font color="blue"><b>SIM</b></font>';
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT SUBSTRING_INDEX(`nome`, ' ', 1) AS contato 
                    FROM `clientes_contatos` 
                    WHERE `id_cliente_contato` = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
            $campos_contato = bancos::sql($sql);
            echo $campos_contato[0]['contato'];
        ?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento= $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }

            if($campos[$i]['id_empresa']==1) {
                $nomefantasia = 'ALBA - NF';
                echo '(A - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa']==2) {
                $nomefantasia = 'TOOL - NF';
                echo '(T - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa']==4) {
                $nomefantasia = 'GRUPO - SGD';
                echo '(G - SGD) / '.$prazo_faturamento;
            }else {
                echo 'Erro';
            }
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos[$i]['total_pedido'], 2, ',', '.');?>
        </td>
    </tr>
<?
	$total_geral_pedido+= $campos[$i]['total_pedido'];
    }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='6'>
            <font color='yellow' size='-1'>
                TOTAL GERAL =>
            </font>
        </td>
        <td colspan='2'>
            <font size='-1'>
                R$ <?=number_format($total_geral_pedido, 2, ',', '.');?>
            </font>
        </td>
    </tr>
</table>
</body>
</html>