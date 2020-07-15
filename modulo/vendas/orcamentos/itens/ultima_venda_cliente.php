<?
require('../../../../lib/segurancas.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO HÁ VENDA(S) A SER(EM) CONSULTADA(S).</font>";

//Função que serve para Retornar o Lote Mínimo de Compra ...
function retornar_lote_minimo_compra($id_produto_acabado) {
//Através do P.A. eu busco quem é o P.I. ...
    $sql = "SELECT id_produto_insumo 
            FROM `produtos_insumos_vs_produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_produto_insumo = $campos[0]['id_produto_insumo'];
//Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado
        $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, "", "", 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
        if($id_fornecedor_setado == 0) {//Se não existir Fornecedor Default para esse P.I. então ñ retorno nada ...
        $retorno = '<font title="Sem Fornecedor Default" color="red">S/ Forn</font>';
    }else {//Como encontrei um Fornecedor Default p/ o P.I., então eu busco o `lote_minimo_pa_rev` deles
        $sql = "SELECT lote_minimo_pa_rev 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_fornecedor` = '$id_fornecedor_setado' 
                AND `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $retorno = '<font title="Lote Mínimo p/ Compra" color="red">L.Mín. '.$campos[0]['lote_minimo_pa_rev'].'</font>';
    }
    return $retorno;
}

//Aqui eu já deixo carregada essa variável porque vou estar utilizando essa nos cálculos em PHP 
$ultima_venda_cliente = genericas::variavel(33);

//Aqui eu busco o id_produto_acabado e o Cliente através do $id_orcamento_venda_item
$sql = "SELECT ov.id_cliente, ovi.id_orcamento_venda, ovi.id_produto_acabado 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
        WHERE ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
$campos = bancos::sql($sql);
$id_cliente = $campos[0]['id_cliente'];
$id_orcamento_venda = $campos[0]['id_orcamento_venda'];
$id_produto_acabado = $campos[0]['id_produto_acabado'];

//Busca de Alguns dados do Cliente ...
$sql = "SELECT id_pais, razaosocial, id_uf, id_cliente, credito, trading, cod_suframa, tipo_suframa 
        FROM `clientes` 
        WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_pais        = $campos[0]['id_pais'];
$razaosocial    = $campos[0]['razaosocial'];
$suframa        = $campos[0]['cod_suframa'];
$trading        = $campos[0]['trading'];
$artigo_isencao = $campos[0]['artigo_isencao'];
$id_uf_cliente  = $campos[0]['id_uf'];
//Significa que o Cliente é do Tipo Internacional
$tipo_moeda = ($id_pais != 31) ? 'U$' : 'R$';

//Aqui eu verifico a Referência do PA ...
$sql = "SELECT referencia 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos_referencia = bancos::sql($sql);
//Se a referência começar por ML- ou MR- na referência do Filtro, ofereço também p/ ele a opção dos Machos Heinz ...
if(strpos(strtoupper($campos_referencia[0]['referencia']), 'ML-') !== false || strpos(strtoupper($campos_referencia[0]['referencia']), 'MR-') !== false) {
    $referencia_heinz = substr($campos_referencia[0]['referencia'], 0, 2).'H'.substr($campos_referencia[0]['referencia'], 2, strlen($campos_referencia[0]['referencia']));
    //Busco o id_produto_acabado do mesmo PA, mas como sendo referência Heinz ...
    $sql = "SELECT id_produto_acabado 
            FROM `produtos_acabados` 
            WHERE `referencia` = '$referencia_heinz' LIMIT 1 ";
    $campos_heinz           = bancos::sql($sql);
    if(count($campos_heinz) == 1) {
        $id_produtos_acabados   = $id_produto_acabado.', '.$campos_heinz[0]['id_produto_acabado'];
    }else {
        $id_produtos_acabados   = $id_produto_acabado;
    }
}else {
    $id_produtos_acabados   = $id_produto_acabado;
}

//Busca de Todos os Orçamentos do mesmo cliente em que constam esse Produto Acabado ...
$sql = "SELECT ov.id_cliente, ov.nota_sgd, ov.data_emissao, ov.prazo_a, ov.prazo_b, ov.prazo_c, ov.prazo_d, 
        ov.data_sys, ovi.*, pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, 
        pa.operacao, pa.peso_unitario, pa.status_custo, pa.observacao AS observacao_produto, ged.id_empresa_divisao 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
        AND ov.`id_orcamento_venda` <> '$id_orcamento_venda' 
        AND ov.`id_cliente` = '$id_cliente' 
        WHERE ovi.`id_produto_acabado` IN ($id_produtos_acabados) 
        ORDER BY ov.data_emissao DESC ";
$campos_itens = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas_itens = count($campos_itens);
?>
<html>
<head>
<title>.:: Última(s) Venda(s) do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<form name="form">
<table width='98%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
<?
    if($linhas_itens == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick='window.close()' style="color:red" class="botao">
        </td>
    </tr>
<?
        exit;
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='18'>
            Última(s) Venda(s) do Cliente: 
            <font color='yellow'>
                <?=$razaosocial;?>
            </font>
        </td>
    </tr>
<?
	//$peso_total_geral = 0;//Essa variável é printada lá em baixo depois do for na linha 633 ...
        $id_produto_acabado_guardado = '';
	for($i = 0; $i < $linhas_itens; $i++) {
            /**************************************************************************************/
            /****************** Verifico se o PA guardado é diferente do PA atual *****************/
            /**************************************************************************************/
            if($id_produto_acabado_guardado != $campos_itens[$i]['id_produto_acabado']) {
?>
    <tr class='linhadestaque'>
        <td colspan='18'>
            <font size='-1'>
                <font color='yellow'>Produto: </font><?=intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0);?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td rowspan='2'>
            Qtde
        </td>
        <td rowspan='2'>
            Pre&ccedil;o L.F.<br><?=$tipo_moeda;?>/P&ccedil;
        </td>
        <td colspan='3'>
            Descontos %
        </td>
        <td rowspan='2'>
            Acrésc.<br>Extra %
        </td>
        <td colspan='3'>
            Comiss&atilde;o
        </td>
        <td rowspan='2'>
            Preço L.<br>Final <?=$tipo_moeda;?>
        </td>
        <td rowspan='2'>
            <font title='% da Variação de Venda do Cliente: <?=number_format($ultima_venda_cliente, 4, ',', '.');?>' style='cursor:help'>
                Preço Novo /<br> Sugerido R$
            </font>
        </td>
        <td rowspan='2'>
            Tipo Nota <br>/ Prazo de Pgto
        </td>
        <td rowspan='2'>
            IPI %
        </td>
        <td rowspan='2'>
            Total Lote<br> <?=$tipo_moeda;?> s/ IPI
        </td>
        <td rowspan='2'>
            N.º Orç.
            <br/>Data Emissão
        </td>
        <td rowspan='2'>
            Qtde | N.º Ped.
            <br/>Data Emissão
        </td>
        <td rowspan='2'>
            Qtde | N.º NF
            <br/>Data Emissão
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Extra
        </td>
        <td>
            <font title='Desconto SGD/ICMS' style='cursor:help'>
                <?if(strtoupper($campos[0]['nota_sgd']) == 'S') {echo 'SGD';} else {echo 'ICMS';}?>
            </font>
        </td>
        <td colspan='2'>
            Representante
        </td>
        <td>
            <?=$tipo_moeda;?>
        </td>
    </tr>
<?
                $id_produto_acabado_guardado = $campos_itens[$i]['id_produto_acabado'];
            }
            /**************************************************************************************/
?>
    <tr class='linhanormal'>
        <td align='center'>
            <font color='red'>
                <b><?=number_format($campos_itens[$i]['qtde'], 0, ',', '.');?></b>
            </font>
        </td>
        <td align='right'>
        <?
            if(empty($campos_itens[$i]['preco_liq_fat_disc'])) {
                echo number_format($campos_itens[$i]['preco_liq_fat'], 2, ',', '.');
            }else {
                if($campos_itens[$i]['preco_liq_fat_disc']=='Orçar') {
                    echo "<font color='red'>".$campos_itens[$i]['preco_liq_fat_disc']."</font>";
                }else {
                    echo "<font color='blue'>".$campos_itens[$i]['preco_liq_fat_disc']."</font>";
                }
            }
        ?>
        </td>
        <td align='center'>
            <?=number_format($campos_itens[$i]['desc_cliente'], 2, ',', '.');?>
        </td>
        <td align='center'>
            <?=number_format($campos_itens[$i]['desc_extra'], 2, ',', '.');?>
        </td>
        <td align='center'>
            <?=number_format($campos_itens[$i]['desc_sgd_icms'], 2, ',', '.');?>
        </td>
        <td align='center'>
            <?=number_format($campos_itens[$i]['acrescimo_extra'], 2, ',', '.');?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT `nome_fantasia` 
                    FROM `representantes` 
                    WHERE `id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
            $campos_rep = bancos::sql($sql);
            //Só exibe para esse respectivos Funcionários: Roberto, Dárcio e Netto ...
            if(($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) && $congelado == 'S') {
        ?>
            <a href="javascript:nova_janela('../../orcamento/itens/alterar_representante.php?id_cliente=<?=$id_cliente;?>&id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Alterar Representante" class="link">
        <?
                echo $campos_rep[0]['nome_fantasia']."</a>";
            }else {
                echo $campos_rep[0]['nome_fantasia'];
            }
        ?>
        </td>
        <td align='center'>
            <font color='brown' title='Comiss&atilde;o Extra => <?=number_format($campos_itens[$i]['comissao_extra'], '2', ',', '.');?>' style='cursor:help'>
                <?=number_format($campos_itens[$i]['comissao_new'] + $campos_itens[$i]['comissao_extra'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
        <?
            $preco_liq_final    = $campos_itens[$i]['preco_liq_final'];
            $preco_total_lote   = $preco_liq_final * $campos_itens[$i]['qtde'];
            echo number_format(vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new']), 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='red'>
            <?
                //Cálculo do Preço por Kilo - Para não dar erro de Divisão por Zero ...
                $preco_por_kilo = ($campos_itens[$i]['peso_unitario'] != 0) ? $preco_liq_final / round($campos_itens[$i]['peso_unitario'], 4) : $preco_liq_final;
                $preco_por_kilo = number_format($preco_por_kilo, 2, ',', '.');
/***************************************************************************************/
                echo "<font title = 'Preço $tipo_moeda $preco_por_kilo / Kg' style='cursor:help'>";
                echo number_format($preco_liq_final, 2, ',', '.');
//Se o P.A. = 'ESP' e a O.C. = Revenda, então eu chamo uma função p/ retornar o Lote Mínimo p/ Compra
                if($campos_itens[$i]['referencia'] == 'ESP' && $campos_itens[$i]['operacao_custo'] == 1) {
                    echo '<br/>'.retornar_lote_minimo_compra($campos_itens[$i]['id_produto_acabado']);
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='red'><b>
            <?
                $preco_novo_sugerido = $preco_liq_final + (($preco_liq_final * $ultima_venda_cliente) / 100);
                //Cálculo do Preço Novo Sugerido por Kilo - P/ não dar erro de Divisão por Zero ...
                $preco_novo_por_kilo = ($campos_itens[$i]['peso_unitario'] != 0) ? $preco_novo_sugerido / round($campos_itens[$i]['peso_unitario'], 4) : $preco_novo_sugerido;
                $preco_novo_por_kilo = number_format($preco_novo_por_kilo, 2, ',', '.');
/***************************************************************************************/
                echo "<font title = 'Preço $tipo_moeda $preco_novo_por_kilo / Kg' style='cursor:help'>";
                echo number_format($preco_novo_sugerido, 2, ',', '.');
            ?>
            </b></font>
        </td>
        <td align='center'>
            <font color='red'><b>
            <?
                if($campos_itens[$i]['nota_sgd'] == 'N') {
                    echo 'NF';
                }else {
                    echo 'SGD';
                }
                echo ' / '.$campos_itens[$i]['prazo_a'];
//Se existirem os D+ prazos daí eu vou printando ...
                if(!empty($campos_itens[$i]['prazo_b'])) echo '-'.$campos_itens[$i]['prazo_b'];
                if(!empty($campos_itens[$i]['prazo_c'])) echo '-'.$campos_itens[$i]['prazo_c'];
                if(!empty($campos_itens[$i]['prazo_d'])) echo '-'.$campos_itens[$i]['prazo_d'];
            ?>
            </b></font>
        </td>
        <td align='center'>
        <?
            if($campos_itens[$i]['nota_sgd'] == 'S') {
                $ipi = 'S/IPI';
            }else {
//Quando o Cliente possui Trading, então ele é isento de IPI
                if($trading == 1) {
                    $ipi = 'S/IPI'; //ent&atilde;o &eacute; zero de IPI
                }else {
/*Quando OFat do PA=rev, Quando o país é do Tipo Internacional, ou o Orçamento for do Tipo SGD ou o Cliente possuir 
suframa ou se no cabeçalho do orçamento estiver checado "Tributar IPI", que vem sugerido do cadastro do cliente, mas pode ser alterado no cabeçalho do ORC", então não existe IPI*/
//Aqui tem que buscar a Classificação Fiscal para poder buscar o IPI
                    $sql = "SELECT cf.`ipi`, cf.`id_classific_fiscal` 
                            FROM `produtos_acabados` pa 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                            INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                            INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                            WHERE pa.`id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' LIMIT 1 ";
                    $campos_temp = bancos::sql($sql);
                    if(count($campos_temp) > 0) {
                        if($campos_itens[$i]['operacao'] ==1 || $id_pais != 31 || $nota_sgd == 'S' || !empty($suframa) || $artigo_isencao == 1) {//op de fat = revenda
                            $ipi = 'S/IPI'; //ent&atilde;o &eacute; zero de IPI
                        }else {
//Nesses estados Acre, Amazonas, Roraima, Rondônia -> zero o IPI
                            if($id_uf_cliente == 25 || $id_uf_cliente == 26 || $id_uf_cliente == 28 || $id_uf_cliente == 29) {
                                $ipi = 'S/IPI'; //ent&atilde;o &eacute; zero de IPI
                            }else {
                                $ipi = number_format($campos_temp[0]['ipi'], 1, ',', '.');
                            }
                        }
                        $id_classific_fiscal = $campos_temp[0]['id_classific_fiscal'];
                    }else {
                        echo "&nbsp;";
                    }
                }
            }
            echo $ipi;
        ?>
        </td>
        <td align='right'>
            <b>
            <?
                if(strtoupper($campos_itens[$i]['promocao']) == 'S') {
                    echo "<font color='blue' title='Preço em Promoção'>".number_format($preco_total_lote, 2, ',', '.')."</font>";
                }else {
                    echo number_format($preco_total_lote, 2, ',', '.');
                }
                //Variável que acumula o total de todos os totais
                $total_geral+= $preco_total_lote;
            ?>
            </b>
        </td>
        <td align='center'>
            <a href = 'itens.php?id_orcamento_venda=<?=$campos_itens[$i]['id_orcamento_venda'];?>&pop_up=1' title='Visualizar Detalhes do Orçamento' class='html5lightbox'>
                <?=$campos_itens[$i]['id_orcamento_venda'];?>
            </a>
            <br/>(<?=data::datetodata($campos_itens[$i]['data_emissao'], '/');?>)
        </td>
        <td align='center'>
        <?
            //Aqui eu verifico se esse Item de Orçamento virou Pedido ...
            $sql = "SELECT DATE_FORMAT(pv.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
                    pvi.`id_pedido_venda`, pvi.`qtde` 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                    WHERE pvi.`id_orcamento_venda_item` = '".$campos_itens[$i]['id_orcamento_venda_item']."' ";
            $campos_pedido = bancos::sql($sql);
            $linhas_pedido = count($campos_pedido);
            if($linhas_pedido > 0) {
//Aqui eu limpo essa variável para não dar conflito com os valores armazenados nesta pelo loop anterior ...
                $id_pedidos_vendas = '';
                for($j = 0; $j < $linhas_pedido; $j++) {
                    echo number_format($campos_pedido[$j]['qtde'], 0, ',', '.').'|';
        ?>
        <a href = 'detalhes_pedido.php?id_pedido_venda=<?=$campos_pedido[$j]['id_pedido_venda'];?>' title='Visualizar Detalhes de Pedido' class='html5lightbox'>
            <?=$campos_pedido[$j]['id_pedido_venda'];?>
        </a>
        <?
                    echo '<br/>('.$campos_pedido[$j]['data_emissao'].')';
                    //Enquanto não for o último pedido, vou gerando Linhas ...
                    if($j + 1 != $linhas_pedido) echo '<br/>';
                }
            }else {
                echo '<font color="red" title="Sem Pedido" style="cursor:help"><b>S/ PED</b></font>';
            }
        ?>
        </td>
        <td align='center'>
        <?
            //Aqui eu verifico se esse Item de Orçamento virou Nota Fiscal ...
            $sql = "SELECT nfs.`id_nf`, DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
                    nfsi.`qtde` 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `nfs_itens` nfsi ON nfsi.`id_pedido_venda_item` = pvi.`id_pedido_venda_item` 
                    INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                    WHERE pvi.`id_orcamento_venda_item` = '".$campos_itens[$i]['id_orcamento_venda_item']."' ";
            $campos_nfs = bancos::sql($sql);
            $linhas_nfs = count($campos_nfs);
            if($linhas_nfs > 0) {
//Aqui eu limpo essa variável para não dar conflito com os valores armazenados nesta pelo loop anterior ...
                $id_nfs = '';
                for($j = 0; $j < $linhas_nfs; $j++) {
                    echo number_format($campos_nfs[$j]['qtde'], 0, ',', '.').'|';
        ?>
        <a href = '../../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos_nfs[$j]['id_nf'];?>&pop_up=1' title='Visualizar Detalhes de Nota Fiscal' class='html5lightbox'>
            <?=faturamentos::buscar_numero_nf($campos_nfs[$j]['id_nf'], 'S');?>
        </a>
        <?
                    echo '<br/>('.$campos_nfs[$j]['data_emissao'].')';
                    //Enquanto não for o último pedido, vou gerando Linhas ...
                    if($j + 1 != $linhas_nfs) echo '<br/>';
                }
            }else {
                echo '<font color="red" title="Sem Nota Fiscal de Saída" style="cursor:help"><b>S/ NF</b></font>';
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='18'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<br>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>