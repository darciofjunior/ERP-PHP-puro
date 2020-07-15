<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/intermodular.php');//Esse arquivo ñ pode ser retirado, pq a biblioteca Vendas utiliza uma função deste ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$mensagem[1] = '<font class="atencao">N&Atilde;O EXISTE(M) OPC(S) &Agrave; SER(EM) IMPORTADA(S).</font>';

if($passo == 1) {
    //Busco a Qtde de Itens que existe(m) no Orçamento ...
    $sql = "SELECT COUNT(`id_orcamento_venda_item`) AS qtde_itens 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' ";
    $campos_qtde_itens      = bancos::sql($sql);
    $qtde_itens_orcamento   = $campos_qtde_itens[0]['qtde_itens'];

    //Busco os itens da opc passada por parametro e busco a ref e discriminacao dos itens dela que ainda não foram importados ...
    $sql = "SELECT oi.`id_opc_item`, oi.`qtde_proposta`, oi.`preco_proposto`, pa.`referencia`, pa.`discriminacao` 
            FROM `opcs_itens` oi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = oi.`id_produto_acabado` 
            WHERE oi.`id_opc` = '$_GET[id_opc]' 
            AND oi.`status` = '0' ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Importar OPC(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos               = document.form.elements
    var qtde_itens_orcamento    = eval('<?=$qtde_itens_orcamento;?>')
    var selecionados            = 0
    for(i = 0; i < elementos.length; i++)  {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked) selecionados++
        }
    }
    //Se não tiver nenhum Item selecionado, força o Preenchimento ...
    if(selecionados == 0) {
        alert('SELECIONE PELO MENOS UM ITEM DE OPC PARA IMPORTAR !')
        return false
    }else {
        if((qtde_itens_orcamento + selecionados) > 100) {
            alert('EXCEDIDO A QUANTIDADE DE ITEM(NS) PARA ESTE ORÇAMENTO !')
            return false
        }else {
            var resposta = confirm('TEM CERTEZA DE QUE DESEJA IMPORTAR ESSE(S) ITEM(NS) DE OPC NESSE ORÇAMENTO ?')
            if(resposta == false) return false
        }
    }
}
</Script>
</head>
<body>
<form name="form" action='<?=$PHP_SELF.'?passo=2';?>' method='post' onsubmit='return validar()'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
           Importar OPC(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Qtde Proposta
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Preço Proposto R$
        </td>
        <td>
            Valor Total R$
        </td>
    </tr>
<?
    for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_opc_item[]' value="<?=$campos[$i]['id_opc_item'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=$campos[$i]['qtde_proposta'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_proposto'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['qtde_proposta'] * $campos[$i]['preco_proposto'], 2, ',', '.');?>
        </td>
    </tr>
<?  
        $valor_total_opc+= $campos[$i]['qtde_proposta'] * $campos[$i]['preco_proposto'];
    }
?>
    <tr align='right'>
        <td class='linhadestaque' colspan='5'>
            Valor Total da OPC => 
        </td>
        <td class='linhadestaque'>
            R$ <?=number_format($valor_total_opc, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'importar_opc.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'" class='botao'>
            <input type='submit' name='cmd_importar_opc' value='Importar OPC' title='Importar OPC' class='botao'>
        </td>
    </tr>
</table>
<!--***************Controle de Tela***************-->
<input type='hidden' name='id_orcamento_venda' value='<?=$_GET['id_orcamento_venda'];?>'>
<input type='hidden' name='id_opc' value='<?=$_GET['id_opc'];?>'>
<!--**********************************************-->
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
<?
}else if($passo == 2) {
    //Como esse processamento pode ser muito pesado, deixo o servidor operar excepcionalmente em até 10 minutos para essa tela ...
    set_time_limit(600);
    
    //Aqui eu busco o id_cliente da OPC que foi submetida ...
    $sql = "SELECT `id_cliente` 
            FROM `opcs` 
            WHERE `id_opc` = '$_POST[id_opc]' ";
    $campos_cliente = bancos::sql($sql);
    $id_cliente     = $campos_cliente[0]['id_cliente'];
    //Aqui eu listo todos os itens da OPC que foram selecionados ...
    foreach($_POST['chkt_opc_item'] as $id_opc_item) {
        $sql = "SELECT `id_produto_acabado`, `qtde_proposta`, 
                `desconto_extra` AS desconto_item_opc, `preco_proposto` 
                FROM `opcs_itens` 
                WHERE `id_opc_item` = '$id_opc_item' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $estoque_produto    = estoque_acabado::qtde_estoque($campos[0]['id_produto_acabado']);
        
        //Aqui eu busco o Prazo de Entrega do Grupo desse PA ...
        $sql = "SELECT ged.`id_empresa_divisao`, gpa.`prazo_entrega` 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE pa.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
        $campos_pas = bancos::sql($sql);
        
        //Verifico se esse PA dessa OPC é ESP ...
        $sql = "SELECT `referencia` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
        $campos_referencia = bancos::sql($sql);
        if($campos_referencia[0]['referencia'] == 'ESP') {//Se for ESP ...
            $prazo_entrega_grupo = $campos_pas[0]['prazo_entrega'];
        }else {//Quando o PA é normal de linha, o prazo fica como sendo Imediato ...
            $qtde_estoque = $estoque_produto[3];//Me baseio sempre na qtde de Estoque Disponível ...
            if($qtde_estoque == 0) {//Se não tenho Qtde em Estoque p/ Entregar ...
                $prazo_entrega_grupo = $campos_pas[0]['prazo_entrega'];
            }else if($qtde_estoque < $campos[0]['qtde_proposta']) {//Prazo vira Parcial ...
                $prazo_entrega_grupo = 'P';
            }else {//Se tiver pelo menos 1 pç em Estoque, o Prazo de Entrega do Grupo será imediato ...
                $prazo_entrega_grupo = '0';
            }
        }
        
        //Busco o representante da determinada Divisão do PA ...
        $sql = "SELECT `id_representante` 
                FROM `clientes_vs_representantes` 
                WHERE `id_cliente` = '$id_cliente' 
                AND `id_empresa_divisao` = '".$campos_pas[0]['id_empresa_divisao']."' LIMIT 1 ";
        $campos_representante = bancos::sql($sql);

        //1) Nesse primeiro momento, simplesmente eu importo o Item da OPC para o Orçamento ...
        $sql = "INSERT INTO `orcamentos_vendas_itens` (`id_orcamento_venda_item`, `id_orcamento_venda`, `id_produto_acabado`, `id_representante`, `id_opc_item`, `qtde`, `preco_liq_final`, `prazo_entrega`, `data_sys`) VALUES (NULL, '$_POST[id_orcamento_venda]', '".$campos[0]['id_produto_acabado']."', '".$campos_representante[0]['id_representante']."', '$id_opc_item', '".$campos[0]['qtde_proposta']."', '".$campos[0]['preco_proposto']."', '$prazo_entrega_grupo', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $id_orcamento_venda_item = bancos::id_registro();
        
        /*2) Quando chamo essa função, o sistema calcula o "Preço Líq. Fat" que até então ainda não existia 
        no sistema p/ esse item, consequentemente recalcula o "Preço Líq Final", fazendo com que este preço 
        fique diferente do que o Vendedor havia projetado no OPC  ...*/
        vendas::calculo_preco_liq_final_item_orc($id_orcamento_venda_item);
        
        /**************************************************************************************************/
        /*******************Variáveis p/ descobrir o Desconto Extra do Item de Orçamento*******************/
        /**************************************************************************************************/
        /*Busco desse $id_orcamento_venda_item que acabou de ser gerado o "Preço Líq. Fat" que agora eu já 
        tenho gravado ...*/
        $sql = "SELECT `preco_liq_fat`, `desc_cliente` 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos_orcamento_venda_item = bancos::sql($sql);
        
        /*Com esse "Preço Líq. Fat", "Desconto do Cliente" e "Preço Proposto", tenho o real desconto Extra 
        desse item de Orçamento ...*/
        $vetor_valores = vendas::alt_c($campos_orcamento_venda_item[0]['preco_liq_fat'], $campos_orcamento_venda_item[0]['desc_cliente'], $campos[0]['preco_proposto']);
        
        //Gravo esse $desconto_extra que foi calculado acima no $id_orcamento_venda_item ...
        $sql = "UPDATE `orcamentos_vendas_itens` SET `desc_extra` = '$vetor_valores[desconto_extra]' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        bancos::sql($sql);
        /**************************************************************************************************/
        
        /*3) Dessa vez quando chamo essa mesma função que já foi chamada mais acima, o sistema a chamará de modo 
        mais rápido e só irá recalcular o "Preço Líq Final" fazendo com que este preço fique exatamente do modo 
        que o Vendedor havia projetado no OPC, porque agora já temos o Preço Líquido Faturado e Desconto Extra 
        corretos deste $id_orcamento_venda_item ...*/
        vendas::calculo_preco_liq_final_item_orc($id_orcamento_venda_item);
        
        //4) Aqui eu atualizo a ML Est do Iem do Orçamento ...
        custos::margem_lucro_estimada($id_orcamento_venda_item);

        //5) Rodo a função de Comissão depois de ter gravado a ML Estimada ...
        vendas::calculo_ml_comissao_item_orc($_POST[id_orcamento_venda], $id_orcamento_venda_item);
        
        //6) Aqui eu atualizo o Status do Item da OPC como sendo Importado ...
        $sql = "UPDATE `opcs_itens` SET `status` = '1' WHERE `id_opc_item` = '$id_opc_item' LIMIT 1 ";
        bancos::sql($sql);
    }
    //Aqui eu verifico se existe algum Item de OPC em aberto que ainda não foi importado ...
    $sql = "SELECT `id_opc_item` 
            FROM `opcs_itens` 
            WHERE `id_opc` = '$_POST[id_opc]' 
            AND `status` = '0' LIMIT 1 ";
    $campos_itens_opcs = bancos::sql($sql);
    if(count($campos_itens_opcs) == 0) {//Se não existir nenhum Item de OPC em Aberto, então posso finalizar a OPC ...
        //Aqui eu faço uma marcação nessa OPC de que esta foi Importada ...
        $sql = "UPDATE `opcs` SET `importado` = 'S' WHERE `id_opc` = '$_POST[id_opc]' LIMIT 1 ";
        bancos::sql($sql);
    }
    //Gero um Follow-UP p/ o Orçamento c/ os dados da OPC importada ...
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', '$_SESSION[id_funcionario]', '$id_pedido_venda_novo', '1', 'OPC N.º $_POST[id_opc]', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    
    //Aqui eu atualizo alguns campos do ORC ...
    vendas::atualizar_orcamento_vendas($_POST['id_orcamento_venda']);
?>
    <Script Language = 'JavaScript'>
        alert('OPC IMPORTADA COM SUCESSO !')
        parent.window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$_POST[id_orcamento_venda];?>'
    </Script>
<?
}else {
    /*********************************************************************************/
    //Busco o Cliente do Orçamento ...
    $sql = "SELECT c.id_cliente, CONCAT(c.razaosocial, ' (', c.nomefantasia, ')') AS cliente, IF(ov.nota_sgd = 'N', 'NF', 'SGD') AS tipo_nota 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
            WHERE ov.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);

    /*Aqui eu verifico se esse Cliente possui OPC´s não Importadas e do mesmo Tipo de Negociação 
    do Cabeçalho do Orçamento NF - SGD ...*/
    $sql = "SELECT opcs.id_opc, f.nome, opcs.tipo_nota, opcs.qtde_anos, opcs.data_sys, SUM(oi.qtde_proposta * oi.preco_proposto) AS valor_total 
            FROM `opcs` 
            INNER JOIN `opcs_itens` oi ON oi.id_opc = opcs.id_opc 
            INNER JOIN `funcionarios` f ON f.id_funcionario = opcs.id_funcionario 
            WHERE opcs.`id_cliente` = '".$campos_cliente[0]['id_cliente']."' 
            AND opcs.tipo_nota = '".$campos_cliente[0]['tipo_nota']."' 
            AND opcs.`importado` = 'N' GROUP BY opcs.`id_opc` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Importar OPC ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<!--Controle de Tela-->
<input type='hidden' name='id_orcamento_venda' value='<?=$_GET['id_orcamento_venda'];?>'>
<!--****************-->
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
<?
    if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <b><?=$mensagem[1];?></b>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'" class="botao">
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Importar OPC(s) do Cliente: <font color='yellow'><?=$campos_cliente[0]['cliente'];?></font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.&ordm; OPC
        </td>
        <td>
            Funcionário Que Projetou
        </td>
        <td>
            Tipo de Nota
        </td>
        <td>
            Qtde de Anos
        </td>
        <td>
            Valor Total
        </td>
        <td>
            Data Criação
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <a href="importar_opc.php?passo=1&id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>&id_opc=<?=$campos[$i]['id_opc'];?>" class="link">
                <?=$campos[$i]['id_opc'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>  
        <td>
            <?=$campos[$i]['tipo_nota'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_anos'];?>
        </td>                     
        <td>
            <?=number_format($campos[$i]['valor_total'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_sys'], '/');?>
        </td>
    </tr>     
<?  
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    }
}
?>