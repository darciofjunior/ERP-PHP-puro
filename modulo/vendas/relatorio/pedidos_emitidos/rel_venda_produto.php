<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');//Essa Biblioteca é requirida de dentro da Biblioteca de Custos ...


/*require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');*/
/*Se foi passado esse parâmetro "$_GET['sumir_botao']" significa que essa Tela foi aberta como sendo Pop-UP 
e sendo não preciso fazer essa Segurança de Url ...*/
if(empty($_GET['sumir_botao'])) {
    segurancas::geral('/erp/albafer/modulo/vendas/relatorio/pedidos_emitidos/pedidos_emitidos.php', '../../../../');
}else {
    /*Preciso do id_funcionario da Sessão nesse caso p/ aparecer o Botão Alterar MMV do PA que 
    fica no fim desta tela ...*/
    session_start('funcionarios');
}

if($passo == 1) {
//Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_gpa_vs_emp_div  = $_POST['id_gpa_vs_emp_div'];
        $data_inicial       = $_POST['data_inicial'];
        $data_final         = $_POST['data_final'];
        $sumir_botao        = $_POST['sumir_botao'];
        $id_produto_acabado = $_POST['id_produto_acabado'];
        $passo              = $_POST['passo'];
    }else {
        $id_gpa_vs_emp_div  = $_GET['id_gpa_vs_emp_div'];
        $data_inicial       = $_GET['data_inicial'];
        $data_final         = $_GET['data_final'];
        $sumir_botao        = $_GET['sumir_botao'];
        $id_produto_acabado = $_GET['id_produto_acabado'];
        $passo              = $_GET['passo'];
    }
//Verifico se o PA corrente é um ESP ...
    /*$sql = "SELECT `referencia` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos_pa  = bancos::sql($sql);
    $referencia = $campos_pa[0]['referencia'];
//Se a Combo de Período foi alterada então ...
    if(!empty($cmb_periodo)) {
        if($cmb_periodo == 3) {//Busca no Período de 3 Meses ...
            $data_inicial = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -90), '-');
            $data_final = date('Y-m-d');//Sempre será a Data Atual ...
        }else if($cmb_periodo == 6) {//Busca no Período de 6 Meses ...
            $data_inicial = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
            $data_final = date('Y-m-d');//Sempre será a Data Atual ...
        }else if($cmb_periodo == 12) {//Busca no Período de 12 Meses (1 Ano) ...
            $data_inicial = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-');
            $data_final = date('Y-m-d');//Sempre será a Data Atual ...
        }else {//Exibe a opção Todos - no caso de ESP e não existe período ... 
            $data_inicial = '';
            $data_final = '';//Sempre será a Data Atual ...
        }
    }else {//Quando a carrega a Tela, o Default é de 6 Meses, quando ñ é passado algum parâm ...
        if(empty($data_inicial)) {
//O Período sugerido pra consulta é de 6 Meses ...
            $data_inicial = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
            $data_final = date('Y-m-d');//Sempre será a Data Atual ...
            $cmb_periodo = 6;
        }
    }
    //Apenas Itens com Pendência ...
    if($cmb_forma_listagem == 'A') $condicao_forma_listagem = " AND pvi.qtde_pendente > 0 ";
//Se existir Datas ...
    //if(!empty($data_inicial)) $condicao_datas = " AND pv.data_emissao BETWEEN '$data_inicial' AND '$data_final' ";
//Utilizo essa variável mais abaixo para auxiliar nos cálculos ...
    //$data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
    /*Aqui eu busco todos os Itens de Pedidos que estão atrelados a este Produto ...
    $sql = "SELECT IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, c.`credito`, 
            ov.`id_cliente`, ov.`artigo_isencao`, ovi.`id_produto_acabado`, ovi.`preco_liq_final`, 
            pv.`id_empresa`, pv.`id_pedido_venda`, pv.`faturar_em`, pv.`vencimento1`, pv.`vencimento2`, 
            pv.`vencimento3`, pv.`vencimento4`, pv.`liberado`, pvi.`id_pedido_venda_item`, pvi.`qtde`, 
            pvi.`vale`, pvi.`qtde_pendente`, pvi.`status` AS status_item 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ovi.`status` > '0' 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_orcamento_venda_item` = ovi.`id_orcamento_venda_item` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            WHERE pvi.`id_produto_acabado` = '$id_produto_acabado' $condicao_datas $condicao_forma_listagem 
            ORDER BY pv.`faturar_em` DESC, pvi.qtde ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);*/
?>
<html>
<head>
<title>.:: Relat&oacute;rio de Pedidos Emitidos por Família - Produtos Acabados ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function voltar() {
    document.form.submit()
}

function submeter() {
    document.form.passo.value = 1
    document.form.submit()
}
</Script>
</head>
<body onload="ajax('listar_pedidos.php', 'div_listar_pedidos')">
<form name='form' method='post' action=''>
<!--****************Controles da Tela do JavaScript*****************-->
<input type='hidden' name='id_gpa_vs_emp_div' value='<?=$id_gpa_vs_emp_div;?>'>
<input type='hidden' name='data_inicial' value='<?=$data_inicial;?>'>
<input type='hidden' name='data_final' value='<?=$data_final;?>'>
<input type='hidden' name='sumir_botao' value='<?=$sumir_botao;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='passo'>
<!--****************************************************************-->
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Relat&oacute;rio de Pedidos Emitidos por Família - Produtos Acabados
            <font color='yellow'>
                Período de 
<?
/*Esse parâmetro, vem de outros arquivos que solicitam essa tela, sendo assim eu controlo esse botão p/
aparecer ou desaparecer quando necessário ...*/
            if($sumir_botao == 1) {
                if(!isset($cmb_periodo)) {//Sugestão Inicial de quando carregarmos a Tela, usuário ainda não escolheu o Período ...
                    $selectedt = 'selected';
                }else {//Significa que já houve uma escolhe anterior ...
                    if($cmb_periodo == 3) {//3 Meses ...
                        $selected3 = 'selected';
                    }else if($cmb_periodo == 6) {//6 Meses ...
                        $selected6 = 'selected';
                    }else if($cmb_periodo == 12) {//12 Meses ...
                        $selected12 = 'selected';
                    }else if($cmb_periodo == 'T') {//Exibe a opção Todos - no caso de ESP ...
                        $selectedt = 'selected';
                    }
                }
?>
                <select name='cmb_periodo' title='Selecione o Período' onchange='submeter()' class='combo'>
                    <option value='3' <?=$selected3;?>>3 Meses</option>
                    <option value='6' <?=$selected6;?>>6 Meses</option>
                    <option value='12' <?=$selected12;?>>12 Meses</option>
                    <option value='T' <?=$selectedt;?>>Todos</option>
                </select>
<?
            }
            
            if(!isset($cmb_forma_listagem)) {//Sugestão Inicial de quando carregarmos a Tela, usuário ainda não escolheu a Forma de Listagem ...
                $selecteda = 'selected';
            }else {//Significa que já houve uma escolhe anterior ...
                if($cmb_forma_listagem == 'T') {//Todos os Itens ...
                    $selectedt = 'selected';
                    /*Apenas Apenas Itens Pendentes ou Separados que equivale aos campos -> 
                    "pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale` - pvi.`qtde_faturada`" ...*/
                }else if($cmb_forma_listagem == 'A') {
                    $selecteda = 'selected';
                }
            }
?>
            </font>
            &nbsp;-&nbsp;
            <select name='cmb_forma_listagem' title='Selecione a Forma de Listagem' onchange="if(this.value == 'A') document.form.cmb_periodo.value = 'T';submeter()" class='combo'>
                <option value='T' <?=$selectedt;?>>Todos os Itens</option>
                <option value='A' <?=$selecteda;?>>Apenas Itens Pendentes / Separados</option>
            </select>
        </td>
    </tr>
<?
    $vetor_pa_atrelados = custos::pas_atrelados($id_produto_acabado);
    $id_pas_atrelados   = implode(',', $vetor_pa_atrelados);
    $linhas_pas         = count($vetor_pa_atrelados);
    for($i = 0; $i < $linhas_pas; $i++) {
?>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow' size='2'>&nbsp;Produto: </font>
            <font size='2'>
                <?=intermodular::pa_discriminacao($vetor_pa_atrelados[$i]);?>
            </font>
        </td>
    </tr>
<?
    }
?>
</table>
<!--Aqui dentro dessa DIV eu listo todos os Pedidos desse Produto - via Ajax-->
<div id='div_listar_pedidos'>
    <center>
        <img src = '../../../../css/little_loading.gif'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='brown'><b>LOADING ...</b>
        </font>
    </center>
</div>
<!--************************************************************************-->
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <?
        $valores = vendas::calculo_preco_venda_medio_nf_sp_30ddl_rs($id_produto_acabado, 'PA', $cmb_forma_listagem);
    ?>
    <tr class='linhacabecalho' align='center'>
        <td rowspan='2'>
            Qtde Meses
        </td>
        <td colspan='4'>
            Total de Venda
        </td>
        <td colspan='2'>
            Total Pendência
        </td>
        <td rowspan='2'>
            MMV
        </td>
        <td rowspan='2'>
            Pçs / Pedido
        </td>
        <td rowspan='2'>
            MLM %
        </td>
        <td rowspan='2'>
            P.Med.NF SP 30d R$
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Pçs
        </td>
        <td>
            NF R$
        </td>
        <td>
            SGD R$
        </td>
        <td>
            Total R$
        </td>
        <td>
            Pçs
        </td>
        <td>
            R$
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            3
        </td>
        <td>
            <?=$valores['total_qtde_inicial_3meses'];?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_nf_rs_3meses'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_sgd_rs_3meses'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_nf_rs_3meses'] + $valores['total_venda_sgd_rs_3meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=$valores['total_qtde_pendencia_3meses'];?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_pendencia_rs_3meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['total_mmv_3meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['pecas_por_pedido_3meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['total_mlm_3meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['preco_medio_NF_SP_30_ddl_3meses'], 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            6
        </td>
        <td>
            <?=$valores['total_qtde_inicial_6meses'];?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_nf_rs_6meses'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_sgd_rs_6meses'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_nf_rs_6meses'] + $valores['total_venda_sgd_rs_6meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=$valores['total_qtde_pendencia_6meses'];?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_pendencia_rs_6meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['total_mmv_6meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['pecas_por_pedido_6meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['total_mlm_6meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['preco_medio_NF_SP_30_ddl_6meses'], 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            12
        </td>
        <td>
            <?=$valores['total_qtde_inicial_12meses'];?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_nf_rs_12meses'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_sgd_rs_12meses'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_nf_rs_12meses'] + $valores['total_venda_sgd_rs_12meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=$valores['total_qtde_pendencia_12meses'];?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_pendencia_rs_12meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['total_mmv_12meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['pecas_por_pedido_12meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['total_mlm_12meses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['preco_medio_NF_SP_30_ddl_12meses'], 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Todos
        </td>
        <td>
            <?=$valores['total_qtde_inicial_Xmeses'];?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_nf_rs_Xmeses'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_sgd_rs_Xmeses'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_venda_nf_rs_Xmeses'] + $valores['total_venda_sgd_rs_Xmeses'], 2, ',', '.');?>
        </td>
        <td>
            <?=$valores['total_qtde_pendencia_Xmeses'];?>
        </td>
        <td align='right'>
            <?=number_format($valores['total_pendencia_rs_Xmeses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['total_mmv_Xmeses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['pecas_por_pedido_Xmeses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['total_mlm_Xmeses'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($valores['preco_medio_NF_SP_30_ddl_Xmeses'], 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            <font color='yellow'>
                Dólar Dia:
            </font>
            <?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?>
<?
/*Esse parâmetro, vem de outros arquivos que solicitam essa tela, sendo assim eu controle esse botão p/
aparecer ou desaparecer quando necessário ...*/
    if($sumir_botao != 1) {
?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick='voltar()' class='botao'>
<?
    }
    
    //Esse botão só aparecerá p/ Rivaldo 27, Rodrigo 54, Roberto 62, Dárcio 98 e Bispo 125 porque programa ...
    if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 125) {
?>
            <input type='button' name='cmd_alterar_mmv_pa' value='Alterar MMV do PA' title='Alterar MMV do PA' onclick="nova_janela('../../../producao/cadastros/produto_acabado/mmv/mmv.php?passo=1&pop_up=1&id_pas_atrelados=<?=$id_pas_atrelados;?>', 'CONSULTAR', '', '', '', '', '480', '880', 'c', 'c', '', '', 's', 's', '', '', '')" style='color:darkblue' class='botao'>
<?
    }
?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {
//Busca a Venda Total de todos os PAs da determinada Empresa por Divisão no Período passado por parâmetro ...
    $sql = "SELECT c.`id_cliente`, c.`id_pais`, pa.`id_produto_acabado`, pa.`discriminacao`, 
            IF(c.`id_pais` = '31', (pvi.`qtde` * pvi.`preco_liq_final`), (pvi.`qtde` * pvi.`preco_liq_final` * ov.`valor_dolar`)) AS total, 
            pvi.`margem_lucro` 
            FROM `produtos_acabados` pa 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_produto_acabado` = pa.`id_produto_acabado` 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' AND pv.`liberado` = '1' 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pa.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' ";
    $campos	= bancos::sql($sql);
    $linhas	= count($campos);
	
    $vetor_produto_acabado  = array();
    $indice                 = 0;

    for($i = 0; $i < $linhas; $i++) {
        if (!in_array($campos[$i]['id_produto_acabado'], $vetor_produto_acabado)) {//NAO CONSTA NO ARRAY
            $vetor_produto_acabado[]    = $campos[$i]['id_produto_acabado'];
            $indice++;
        }
        $vetor_total[$campos[$i]['id_produto_acabado']]+=   $campos[$i]['total'];
        $vetor_mlmg[$campos[$i]['id_produto_acabado']]+=    $campos[$i]['margem_lucro'];

        if($campos[$i]['margem_lucro'] != '-100.00') {
            $vetor_custo_ml_zero[$campos[$i]['id_produto_acabado']]+= $campos[$i]['total'] / (1 + $campos[$i]['margem_lucro'] / 100);
            $total_custo_ml_zero+=  $campos[$i]['total'] / (1 + $campos[$i]['margem_lucro'] / 100);
        }
        $total_pedidos_emitidos+= $campos[$i]['total'];
    }
?>
<html>
<head>
<title>.:: Relat&oacute;rio de Pedidos Emitidos por Família - Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function pedidos_atrelados(id_produto_acabado) {
    document.form.id_produto_acabado.value = id_produto_acabado
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='id_gpa_vs_emp_div' value='<?=$id_gpa_vs_emp_div;?>'>
<input type='hidden' name='data_inicial' value='<?=$data_inicial;?>'>
<input type='hidden' name='data_final' value='<?=$data_final;?>'>
<!--Essa variável é controlada pela função em JavaScript-->
<input type='hidden' name='id_produto_acabado'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Relat&oacute;rio de Pedidos Emitidos por Família - Produtos Acabados
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            Total em R$
        </td>
        <td>
            Porcentagem(ns)
        </td>
        <td title='Margem de Lucro Média Gravada'>
            <a style='cursor:help'>
                M.L.M.G.
            </a>
        </td>
        <td>
            Lucro em <br/> Reais
        </td>
    </tr>
<?
    $total_pas = count($vetor_produto_acabado);
    for($i = 0; $i < $total_pas; $i++) {
        $total_parcial = $vetor_total[$vetor_produto_acabado[$i]];
?>
    <tr class='linhanormal'>
        <td>
            <a href="javascript:pedidos_atrelados('<?=$vetor_produto_acabado[$i];?>')" title='Pedidos Atrelados' class='link'>
                <?=intermodular::pa_discriminacao($vetor_produto_acabado[$i]);?>
            </a>
        </td>
        <td align='right'>
        <?	
            $total_geral+= $total_parcial;//O Total parcial é o total de todos os pedidos NAC ...
            echo number_format($total_parcial, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $porc_parcial = ($vetor_total[$vetor_produto_acabado[$i]] / $total_pedidos_emitidos) * 100;
            $porc_total+= $porc_parcial;
            echo number_format($porc_parcial, 2, ',', '.');
        ?>
        %
        </td>
        <td align='right'>
        <?
            $mlmg_pa = ($vetor_custo_ml_zero[$vetor_produto_acabado[$i]] == 0) ? 0 : ($vetor_total[$vetor_produto_acabado[$i]] / $vetor_custo_ml_zero[$vetor_produto_acabado[$i]] - 1) * 100;
            echo number_format($mlmg_pa, 2, ',', '.');
        ?> %
        </td>
        <td align='right'>
        <?
            $custo_ml_zero = round($vetor_total[$vetor_produto_acabado[$i]], 2) / (1 + round($mlmg_pa, 2) / 100);
            $lucro_rs      = round($vetor_total[$vetor_produto_acabado[$i]], 2) - $custo_ml_zero;
            echo number_format($lucro_rs, 2, ',', '.');
            $lucro_total_reais+= $lucro_rs;
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal' align='right'>
        <td colspan='2'>
            <font color='red' size='2'>
                <b>Total Geral: </b><?=number_format($total_pedidos_emitidos, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($porc_total, 2, ',', '.');?>%
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format(($total_geral / $total_custo_ml_zero - 1) * 100, 2, ',', '.');?>%
            </font>
        </td>
        <td>
            <font color='red' size='2'>
                <?=number_format($lucro_total_reais, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='5'>
            <font size='2' color='red'>
                <b>PEDIDOS EXPORT USAM O U$ DO ORÇAMENTO.</b>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relatório' title='Atualizar Relatório' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>