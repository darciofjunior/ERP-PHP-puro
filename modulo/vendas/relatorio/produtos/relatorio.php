<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Função que serve para Retornar o Lote Mínimo de Compra ...
function retornar_lote_minimo_compra($id_produto_acabado) {
    //Através do P.A. eu busco quem é o P.I. ...
    $sql = "SELECT id_produto_insumo 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `ativo` = 1' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_produto_insumo  = $campos[0]['id_produto_insumo'];
//Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado
        $id_fornecedor_setado   = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
        if($id_fornecedor_setado == 0) {//Se não existir Fornecedor Default para esse P.I. então ñ retorno nada ...
        $retorno = '<font title="Sem Fornecedor Default" color="red">S/ Forn</font>';
    }else {//Como encontrei um Fornecedor Default p/ o P.I., então eu busco o `lote_minimo_pa_rev` deles
        $sql = "SELECT lote_minimo_pa_rev 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_fornecedor` = '$id_fornecedor_setado' 
                AND `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos     = bancos::sql($sql);
        $retorno    = '<font title="Lote Mínimo p/ Compra" color="red">L.Mín. '.$campos[0]['lote_minimo_pa_rev'].'</font>';
    }
    return $retorno;
}

if($passo == 1) {
/****************************************************/
//Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_referencia     = $_POST['txt_referencia'];
        $txt_discriminacao  = $_POST['txt_discriminacao'];
        $txt_data_inicial   = $_POST['txt_data_inicial'];
        $txt_data_final     = $_POST['txt_data_final'];
    }else {
        $txt_referencia     = $_GET['txt_referencia'];
        $txt_discriminacao  = $_GET['txt_discriminacao'];
        $txt_data_inicial   = $_GET['txt_data_inicial'];
        $txt_data_final     = $_GET['txt_data_final'];
    }
	
    if(!empty($txt_data_inicial)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_inicial, 4, 1) != '-') {
            $txt_data_inicial = data::datatodate($txt_data_inicial, '-');
            $txt_data_final = data::datatodate($txt_data_final, '-');
//Aqui é para não dar erro de SQL
        }
        $condicao_datas = " AND ov.`data_emissao` BETWEEN '$txt_data_inicial' AND '$txt_data_final' ";
    }

//Busca de Todos os Orçamentos do mesmo cliente em que constam esse Produto Acabado ...
    $sql = "SELECT c.`id_cliente`, c.`id_uf`, ged.`id_empresa_divisao`, ov.`id_cliente`, 
            ov.`finalidade`, ov.`nota_sgd`, ov.`data_emissao`, ov.`prazo_a`, ov.`prazo_b`, 
            ov.`prazo_c`, ov.`prazo_d`, ov.`data_sys`, ovi.*, pa.`id_produto_acabado`, 
            pa.`referencia`, pa.`operacao_custo`, pa.`operacao`, pa.`peso_unitario`, pa.`status_custo`, 
            pa.`observacao` AS observacao_produto 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_produto_acabado` = pa.`id_produto_acabado` 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` $condicao_datas 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            WHERE (pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%') ORDER BY ov.`data_emissao` DESC ";
    $campos_itens   = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas_itens   = count($campos_itens);
    if($linhas_itens == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'relatorio.php?valor=1'
        </Script>
<?
    }else {
//Aqui eu já deixo carregada essa variável porque vou estar utilizando essa nos cálculos em PHP 
        $ultima_venda_cliente = genericas::variavel(33);
?>
<html>
<head>
<title>.:: Última(s) Venda(s) do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body >
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            <font color='yellow'>
                Detalhes do Produto: 
            </font>
            <?=$txt_referencia;?> - <?=$txt_discriminacao;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            Qtde
        </td>
        <td rowspan='2'>
            Pre&ccedil;o L.F.<br>R$ /P&ccedil;
        </td>
        <td colspan='3'>
            Descontos % 
        </td>
        <td rowspan='2'>
            Acrésc.<br>Extra %
        </td>
        <td rowspan='2'>
            Representante
        </td>
        <td rowspan='2'>
            Preço L.<br>Final R$
        </td>
        <td rowspan='2'>
            <span title="% da Variação de Venda do Cliente: <?=number_format($ultima_venda_cliente, 4, ',', '.');?>" style="cursor:help">
                Preço Novo /<br> Sugerido R$
            </span>
        </td>
        <td rowspan='2'>
            <font title="Tipo Nota / Prazo de Pgto">
                Tipo Nota <br>/ Prazo de Pgto
            </font>
        </td>
        <td rowspan='2'>
            IPI %
        </td>
        <td rowspan='2'>
            Total Lote<br> R$ s/ IPI
        </td>
        <td rowspan='2'>
            N.º Orç.
        </td>
        <td rowspan='2'>
            Data de <br>Emissão
        </td>
        <td rowspan='2'>
            Cliente
        </td>
        <td rowspan='2'>
            N.º Ped.
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Desconto do Cliente %' style='cursor:help'>
                Cliente 
            </font>
        </td>
        <td>
            <font title='Desconto Extra' style='cursor:help'>
                Extra
            </font>
        </td>
        <td>
            <font title='Desconto SGD/ICMS' style='cursor:help'>
                <?if(strtoupper($campos[0]['nota_sgd']) == 'S') {echo 'SGD';} else {echo 'ICMS';}?>
            </font>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas_itens; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado']);?>
        </td>
        <td>
            <font color='red'>
                <font face='Verdana, Arial, Helvetica, sans-serif'>
                    <?=number_format($campos_itens[$i]['qtde'], 0, ',', '.');?>
                </font>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif'>
            <?
                if(empty($campos_itens[$i]['preco_liq_fat_disc'])) {
                    echo number_format($campos_itens[$i]['preco_liq_fat'], 2, ',', '.');
                }else {
                    if($campos_itens[$i]['preco_liq_fat_disc'] == 'Orçar') {
                        echo "<font color='red'>".$campos_itens[$i]['preco_liq_fat_disc']."</font>";
                    }else {
                        echo "<font color='blue'>".$campos_itens[$i]['preco_liq_fat_disc']."</font>";
                    }
                }
            ?>
            </font>
        </td>
        <td>
            <?=number_format($campos_itens[$i]['desc_cliente'], 2, ',', '.');?>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif'>
                <?=number_format($campos_itens[$i]['desc_extra'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif'>
                <?=number_format($campos_itens[$i]['desc_sgd_icms'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif'>
                <?=number_format($campos_itens[$i]['acrescimo_extra'], 2, ',', '.');?>
            </font>
        </td>
        <td>
        <?
            $sql = "SELECT nome_fantasia 
                    FROM `representantes` 
                    WHERE `id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
            $campos_rep = bancos::sql($sql);
            //Só exibe para esse respectivos Logins: Roberto / Dárcio ...
            if(($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) && $congelado == 'S') {
        ?>
                <a href = '../../orcamento/itens/alterar_representante.php?id_cliente=<?=$campos_itens[$i]['id_cliente'];?>&id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>' class='html5lightbox'>
        <?
                echo $campos_rep[0]['nome_fantasia']."</a>";
            }else {
                echo $campos_rep[0]['nome_fantasia'];
            }
        ?>
        </td>
        <td align='right'>
                <font face='Verdana, Arial, Helvetica, sans-serif' color="red">
                <?
//Cálculo do Preço por Kilo
                        if($campos_itens[$i]['peso_unitario'] != 0) {//P/ não dar erro de Divisão por Zero
                                $preco_por_kilo = $campos_itens[$i]['preco_liq_final'] / round($campos_itens[$i]['peso_unitario'], 4);
                        }else {
                                $preco_por_kilo = $campos_itens[$i]['preco_liq_final'];
                        }
                        $preco_por_kilo = number_format($preco_por_kilo, 2, ',', '.');
/***************************************************************************************/
                        echo "<font title = 'Preço $tipo_moeda $preco_por_kilo / Kg' style='cursor:help'>";
                        echo number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.');
//Se o P.A. = 'ESP' e a O.C. = Revenda, então eu chamo uma função p/ retornar o Lote Mínimo p/ Compra
                        if($campos_itens[$i]['referencia'] == 'ESP' && $campos_itens[$i]['operacao_custo'] == 1) echo '<br>'.retornar_lote_minimo_compra($campos_itens[$i]['id_produto_acabado']);
                ?>
                </font>
        </td>
        <td align='right'>
                <font face='Verdana, Arial, Helvetica, sans-serif' color="red"><b>
                <?
                        $preco_novo_sugerido = $campos_itens[$i]['preco_liq_final'] + (($campos_itens[$i]['preco_liq_final'] * $ultima_venda_cliente) / 100);
//Cálculo do Preço Novo Sugerido por Kilo
                        if($campos_itens[$i]['peso_unitario'] != 0) {//P/ não dar erro de Divisão por Zero
                                $preco_novo_por_kilo = $preco_novo_sugerido / round($campos_itens[$i]['peso_unitario'], 4);
                        }else {
                                $preco_novo_por_kilo = $preco_novo_sugerido;
                        }
                        $preco_novo_por_kilo = number_format($preco_novo_por_kilo, 2, ',', '.');
/***************************************************************************************/
                        echo "<font title = 'Preço $tipo_moeda $preco_novo_por_kilo / Kg' style='cursor:help'>".number_format($preco_novo_sugerido, 2, ',', '.');
                ?>
                </b></font>
        </td>
        <td>
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
        <td>
        <?
            //Isso aqui é uma adaptação, já que não existe id_empresa em Orçamento ...
            $id_empresa_nf  = ($campos_itens[$i]['nota_sgd'] == 'N') ? 1 : 4;
            $dados_produto  = intermodular::dados_impostos_pa($campos_itens[$i]['id_produto_acabado'], $campos_itens[$i]['id_uf'], $campos_itens[$i]['id_cliente'], $id_empresa_nf, $campos_itens[$i]['finalidade']);
            if($dados_produto['ipi'] > 0) echo number_format($dados_produto['ipi'], 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <b>
            <?
                $preco_total_lote = $campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'];
                if(strtoupper($campos_itens[$i]['promocao'])=='S') {
                    echo "<font color='blue' title='Preço em Promoção'>".number_format($preco_total_lote, 2, ',', '.')."</font>";
                }else {
                    echo number_format($preco_total_lote, 2, ',', '.');
                }
                //Variável que acumula o total de todos os totais
                $total_geral+= $preco_total_lote;
            ?>
            </b>
        </td>
        <td>
            <?=$campos_itens[$i]['id_orcamento_venda'];?>
        </td>
        <td>
                <?=data::datetodata($campos_itens[$i]['data_emissao'], '/');?>
        </td>
        <td align='left'>
        <?
                $sql = "SELECT IF(razaosocial = '', nomefantasia, razaosocial) AS cliente 
                        FROM `clientes` 
                        WHERE `id_cliente` = '".$campos_itens[$i]['id_cliente']."' LIMIT 1 ";
                $campos_clientes = bancos::sql($sql);
                echo $campos_clientes[0]['cliente'];
        ?>
        </td>
        <td>
        <?
            //Verifico se esse Item de Orçamento virou Pedido ...
            $sql = "SELECT `id_pedido_venda` 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_orcamento_venda_item` = ".$campos_itens[$i]['id_orcamento_venda_item'];
            $campos_pedidos = bancos::sql($sql);
            $linhas_pedidos = count($campos_pedidos);
            if($linhas_pedidos > 0) {
//Aqui eu limpo essa variável para não dar conflito com os valores armazenados nesta pelo loop anterior ...
                $id_pedidos_vendas = '';
                for($j = 0; $j < $linhas_pedidos; $j++) {
        ?>
                    <a href = '../../orcamentos/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos_pedidos[$j]['id_pedido_venda'];?>' class='html5lightbox'>
                        <?=$campos_pedidos[$j]['id_pedido_venda'];?>
                    </a>
        <?
//Enquanto não for o último pedido, eu vou exibindo essa vírgula para Separador de Pedido ...
                    if($j + 1 != $linhas_pedidos) echo ', ';
                }
            }else {
                echo '<font color="red" title="Sem Pedido" style="cursor:help"><b>S/ PED</b></font>';
            }
        ?>
        </td>
    </tr>
<?
            $qtde_geral+= $campos_itens[$i]['qtde'];
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            <font color='yellow'>
                <b>Qtde Geral =></b>
            </font>	
        </td>
        <td>
            <?=number_format($qtde_geral, 0, ',', '.');?>
        </td>
        <td colspan='13'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'relatorio.php'" class='botao'>
        </td>
        <td colspan='2' align="right">
            <font color='yellow'>
                <b>Total de Venda:</b>
            </font>
            R$ <?=number_format($total_geral, 2, ',', '.');?>
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
<?
    }
}else {
?>
<html>
<head>
<title>.:: Filtro de Produto(s) Acabado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_referencia.value == '' && document.form.txt_discriminacao.value == '') {
        alert('DIGITE UM PRODUTO !')
        document.form.txt_referencia.focus()
        return false
    }
}
</Script>
</head>
<body onload="document.form.txt_referencia.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type="text" name="txt_referencia" title="Digite a Referência" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type="text" name="txt_discriminacao" title="Digite a Discriminação" size="30" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data Inicial
        </td>
        <td>
            <input type="text" name="txt_data_inicial" title="Digite a Data Inicial" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type="text" name="txt_data_final" title="Digite a Data Final" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class="caixadetexto"> 
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhacabecalho' align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_referencia.focus()" style="color:#ff9900" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>