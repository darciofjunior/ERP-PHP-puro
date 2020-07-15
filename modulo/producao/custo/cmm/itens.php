<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/custos.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>VALOR DO PRODUTO INSUMO ALTERADO COM SUCESSO.</font>";

//Aqui são as variáveis para o cálculo do custo
$moeda_custo 	= genericas::variaveis('moeda_custo');
$dolar_custo 	= $moeda_custo['dolar_custo'];
$euro_custo 	= $moeda_custo['euro_custo'];
$fator_importacao = genericas::variaveis('fator_importacao');

if($passo == 1) {
    if($chkt_somente_atrelados_custos == 1) {//Traz somente os PI´s atrelados as Etapas do Custo ...
        $sql = "SELECT DISTINCT(id_produto_insumo) 
                FROM `pacs_vs_pis` ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produto_insumo_temp.= ($campos[$i]['id_produto_insumo'].', ');
        
        $sql = "SELECT DISTINCT(id_produto_insumo) 
                FROM `pacs_vs_pis_trat` ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produto_insumo_temp.= ($campos[$i]['id_produto_insumo'].', ');

        $sql = "SELECT DISTINCT(id_produto_insumo) 
                FROM `pacs_vs_pis_usis` ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produto_insumo_temp.= ($campos[$i]['id_produto_insumo'].', ');

        $sql = "SELECT DISTINCT(id_produto_insumo) 
                FROM `pas_vs_pis_embs` ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produto_insumo_temp.= ($campos[$i]['id_produto_insumo'].', ');

        $sql = "SELECT DISTINCT(id_produto_insumo) 
                FROM `produtos_acabados_custos` 
                WHERE id_produto_insumo IS NOT NULL ";
        $campos = bancos::sql($sql);
        $linhas	= count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produto_insumo_temp.= ($campos[$i]['id_produto_insumo'].', ');

        $id_produto_insumo_temp = substr($id_produto_insumo_temp, 0, (strlen($id_produto_insumo_temp) - 2));
        $id_produto_insumo_temp = implode(",",array_unique(explode(',', $id_produto_insumo_temp)));
        $id_produto_insumo_temp = " AND pi.`id_produto_insumo` IN (".$id_produto_insumo_temp.") ";
    }

    if(!empty($txt_data)) {
        //Transforma a Data em formato americano p/ poder fazer a Query no Banco ...
        $txt_data_usa = data::datatodate($txt_data, '-');
        $inner_join = " INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor` = pi.`id_fornecedor_default` AND fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`ativo` = '1' 
                        AND SUBSTRING(fpi.`data_sys`, 1, 10) <= '$txt_data_usa' 
                        AND pi.`id_fornecedor_default` > '0' ";
    }

    if(!empty($chkt_exibir_somente_precos_zerados)) {
        //Tenho que ter esse cuidado com esse SQL para que não dê redundância de tabelas ...
        if(!empty($inner_join)) {
            $inner_join.= " AND (fpi.preco_faturado = '0.00' AND fpi.preco_faturado_export = '0.00') ";
        }else {
            $inner_join = " INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor` = pi.`id_fornecedor_default` AND fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`ativo` = '1' 
                            AND (fpi.preco_faturado = '0.00' AND fpi.preco_faturado_export = '0.00') 
                            AND pi.`id_fornecedor_default` > '0' ";
        }
    }
    if(empty($cmb_grupo))   $cmb_grupo = '%';

    $sql = "SELECT DISTINCT(pi.id_produto_insumo), g.referencia, pi.discriminacao, DATE_FORMAT(pi.data_custo, '%d/%m/%Y') AS data_custo, u.sigla 
            FROM `produtos_insumos` pi 
            $inner_join 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.`id_grupo` LIKE '$cmb_grupo' 
            WHERE pi.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND pi.`observacao` LIKE '%$txt_observacao%' 
            AND pi.`ativo` = '1' 
            AND g.`id_grupo` <> '9' $id_produto_insumo_temp ORDER BY pi.discriminacao ";
    //Significa que o usuário não quer que apresente o relatório paginado ...
    $qtde_pagina    = ($_GET['chkt_retirar_paginacao'] == 'S') ? 3000 : 100;
    $campos         = bancos::sql($sql, $inicio, $qtde_pagina, 'sim', $pagina);
    $linhas         = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'itens.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Custo / Relatório de Estoque PI ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'controle.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['chkt_fornecedor_prod_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_fornecedor_prod_insumo[]'].length)
    }
    var checkbox_selecionados = 0
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_fornecedor_prod_insumo'+i).checked == true) {
            checkbox_selecionados++;
            break;
        }
    }
    //Se não tiver nenhuma opção selecionada, então forço o usuário a preencher alguma ...
    if(checkbox_selecionados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        document.getElementById('chkt_fornecedor_prod_insumo0').focus()
        return false
    }else {//Se existir uma selecionada pelo menos, forço o preenchimento de alguns campos da linha ...
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_fornecedor_prod_insumo'+i).checked == true) {
                if(document.getElementById('txt_preco_fat_nac_real'+i).value == '') {
                    alert('DIGITE O PREÇO FATURADO NACIONAL R$ !')
                    document.getElementById('txt_preco_fat_nac_real'+i).focus()
                    return false
                }		
                if(document.getElementById('txt_preco_fat_inter_est'+i).value == '') {
                    alert('DIGITE O PREÇO FATURADO INTERNACIONAL R$ !')
                    document.getElementById('txt_preco_fat_inter_est'+i).focus()
                    return false
                }
            }
        }
    }
    //Aqui eu preparo os valores para poder gravar no Banco de Dados ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_fornecedor_prod_insumo'+i).checked == true) {
            document.getElementById('txt_preco_fat_nac_real'+i).value	= strtofloat(document.getElementById('txt_preco_fat_nac_real'+i).value)
            document.getElementById('txt_preco_fat_inter_est'+i).value	= strtofloat(document.getElementById('txt_preco_fat_inter_est'+i).value)
            document.getElementById('txt_preco_fat_inter_real'+i).value	= strtofloat(document.getElementById('txt_preco_fat_inter_real'+i).value)
            document.getElementById('hdd_fornecedor'+i).disabled		= false
        }
    }
}

function calcular(indice) {
//Variáveis para a realização do cálculo de custo
    var dolar_custo 			= eval(document.form.hdd_dolar_custo.value)
    var euro_custo 				= eval(document.form.hdd_euro_custo.value)
    var fator_importacao 		= eval(document.form.hdd_fator_importacao.value)
    var preco_fat_nac_real 		= (document.getElementById('txt_preco_fat_nac_real'+indice).value == '') ? 0 : strtofloat(document.getElementById('txt_preco_fat_nac_real'+indice).value)
    var preco_fat_inter_est 	= (document.getElementById('txt_preco_fat_inter_est'+indice).value == '') ? 0 : strtofloat(document.getElementById('txt_preco_fat_inter_est'+indice).value)
    var preco_fat_inter_real 	= (document.getElementById('txt_preco_fat_inter_real'+indice).value == '') ? 0 : strtofloat(document.getElementById('txt_preco_fat_inter_real'+indice).value)
    var tipo_moeda 				= (document.getElementById('hdd_tipo_moeda'+indice).value == '') ? 0 : strtofloat(document.getElementById('hdd_tipo_moeda'+indice).value)
    var id_pais 				= (document.getElementById('hdd_id_pais'+indice).value == '') ? 0 : strtofloat(document.getElementById('hdd_id_pais'+indice).value)
    var valor_moeda_compra 		= (document.getElementById('hdd_valor_moeda_compra'+indice).value == '') ? 0 : strtofloat(document.getElementById('hdd_valor_moeda_compra'+indice).value)

    if(id_pais == 31) {//Brasil
            resultado1 = preco_fat_nac_real
            if(tipo_moeda == 1) {//Dólar
                    resultado2 = preco_fat_inter_est * valor_moeda_compra
            }else if(tipo_moeda == 2) {//Euro
                    resultado2 = preco_fat_inter_est * valor_moeda_compra
            }else {//Real
                    resultado2 = 0
            }
    }else {//Estrangeiro
            resultado1 = 0
            if(tipo_moeda == 1) {//Dólar
                    resultado2 = preco_fat_inter_est * dolar_custo * fator_importacao
            }else if(tipo_moeda == 2) {//Euro
                    resultado2 = preco_fat_inter_est * euro_custo * fator_importacao
            }else {
                    resultado2 = 0
            }
    }
    //document.getElementById('txt_preco_fat_nac_real'+indice).value 		= resultado1
    //document.getElementById('txt_preco_fat_nac_real'+indice).value 		= arred(document.getElementById('txt_preco_fat_nac_real'+indice).value, 2, 1)
    document.getElementById('txt_preco_fat_inter_real'+indice).value 	= resultado2
    document.getElementById('txt_preco_fat_inter_real'+indice).value 	= arred(document.getElementById('txt_preco_fat_inter_real'+indice).value, 2, 1)
}

//Passa o índíce da coluna
function atualizar_coluna(opcao, indice) {
    if(opcao == 1) {//Verifica se está preenchido o preco_fat_nac_real ...
            if(document.form.txt_preco_fat_nac_real_geral.value == '') {
                    alert('PREENCHA O PREÇO FATURADO NACIONAL REAL !')
                    document.form.txt_preco_fat_nac_real_geral.focus()
                    return false
            }
    }else {//Verifica se está preenchido o preco_fat_inter_est_geral
            if(document.form.txt_preco_fat_inter_est_geral.value == '') {
                    alert('PREENCHA O PREÇO FATURADO INTERNACIONAL ESTRANGEIRO !')
                    document.form.txt_preco_fat_inter_est_geral.focus()
                    return false
            }
    }
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['chkt_fornecedor_prod_insumo[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
    }else {
            var linhas = (elementos['chkt_fornecedor_prod_insumo[]'].length)
    }
    //Aqui eu preparo os valores para poder gravar no Banco de Dados ...
    for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_fornecedor_prod_insumo'+i).checked == true) {
                    if(opcao == 1) {//preco_fat_nac_real
                            document.getElementById('txt_preco_fat_nac_real'+i).value = document.form.txt_preco_fat_nac_real_geral.value
                    }else {//preco_fat_inter_est_geral
                            document.getElementById('txt_preco_fat_inter_est'+i).value= document.form.txt_preco_fat_inter_est_geral.value
                    }
            }
    }
}

function retirar_paginacao(chkt_retirar_paginacao) {
    var retirar_paginacao = (chkt_retirar_paginacao.checked) ? 'S' : 'N';
    window.location = 'itens.php<?=$parametro;?>&chkt_retirar_paginacao='+retirar_paginacao
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit="return validar()">
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Custo / Relatório de Estoque PI -
            <?$checked = ($_GET['chkt_retirar_paginacao'] == 'S') ? 'checked' : '';?>
            <input type="checkbox" value="S" id="chkt_retirar_paginacao" onclick="retirar_paginacao(this)" class="checkbox" <?=$checked;?>>
            <label for="chkt_retirar_paginacao">
                Retirar Paginação
            </label>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar_todos('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
        <td>
            Ref.
        </td>
        <td>
            <font title='Unidade' style='cursor:help'>
                Un
            </font>
        </td>
        <td>
            Estoque PI
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Data Últ. Atual.
        </td>
        <td>
            Fornec / Func
        </td>
        <td>
            Preço Fat. Nac. + <br>Adic. R$
        </td>
        <td>
            Preço Fat. Inter. + <br>Adic. Estr.
        </td>
        <td>
            Preço Fat. Inter. R$
        </td>
        <td>
            Total Estoque R$
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $dados_pi   = custos::preco_custo_pi($campos[$i]['id_produto_insumo'], 1);
            $preco_pi   = $dados_pi;
            
            //Aqui eu verifico se o produto insumo tem pelo menos um pedido ...
            $sql = "SELECT `id_fornecedor_default` 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `id_fornecedor_default` > '0' LIMIT 1 ";
            $campos_fornecedor_default = bancos::sql($sql);
            if(count($campos_fornecedor_default) > 0) {//Existe um Pedido ...
                $cor = 'preto';
                //Aqui eu busco qual é o Fornecedor desse PI na Lista de Preços ...
                $sql = "SELECT f.id_pais, f.razaosocial, fpi.* 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor 
                        WHERE fpi.`id_fornecedor` = '".$campos_fornecedor_default[0]['id_fornecedor_default']."' 
                        AND fpi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                        AND fpi.ativo = '1' LIMIT 1 ";
                $campos_lista = bancos::sql($sql);
            }else {//Não existe nenhum Pedido ...
                $cor = 'azul';
                $id_fornecedor = custos::preco_produto_sem_pedido($campos[$i]['id_produto_insumo'], 1);
                //Arranjo Técnico
                if(empty($id_fornecedor)) $id_fornecedor = 0;
                //Busca os dados da lista de preço + o fornecedor
                $sql = "SELECT f.`id_pais`, f.`razaosocial`, fpi.* 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
                        WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                        AND fpi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                        AND fpi.`ativo` = '1' LIMIT 1 ";
                $campos_lista = bancos::sql($sql);
            }
            if(count($campos_lista) == 1) {//Encontrou dados da Lista de Preço do PI ...
                if(!empty($campos_lista[0]['id_fornecedor_prod_insumo'])) {
                    $id_fornecedor                  = $campos_lista[0]['id_fornecedor'];
                    $tp_moeda                       = $campos_lista[0]['tp_moeda'];
                    $preco_faturado                 = number_format($campos_lista[0]['preco_faturado'], 2, ',', '.');
                    $preco_faturado_export          = $campos_lista[0]['preco_faturado_export'];
                    $preco_faturado_export_adicional= $campos_lista[0]['preco_faturado_export_adicional'];
                    $valor_moeda_compra             = number_format($campos_lista[0]['valor_moeda_compra'], 2, ',', '.');
                    $data_sys                       = data::datetodata(substr($campos_lista[0]['data_sys'], 0, 10), '/');
                    if($data_sys == '00/00/0000')   $data_sys = '';
                    $fornecedor                     = "<a href='#' title='Listagem de Fornecedor(es)' class='link'>".$campos_lista[0]['razaosocial']."</a>";
                    $id_pais                        = $campos_lista[0]['id_pais'];
                    $funcao                         = "checkbox_habilita('form', 'chkt_tudo', $i, '#E8E8E8') ";
                    
                    //Se teve algum funcionário que fez mudança na Lista de Preço, então trago o nome deste ...
                    if(!empty($campos_lista[0]['id_funcionario'])) {
                        $sql = "SELECT `nome` 
                                FROM `funcionarios` 
                                WHERE `id_funcionario` = '".$campos_lista[0]['id_funcionario']."' LIMIT 1 ";
                        $campos_funcionario = bancos::sql($sql);
                        if(count($campos_funcionario) == 1) $fornecedor.=' / <b>'.$campos_funcionario[0]['nome'].'</b>';
                    }
                    //Aqui verifica as cores ...
                    if($cor == 'azul') $fornecedor = "<font color = 'blue'>".$fornecedor.'</font>';
                }
            }else {
                $fornecedor             = "<a href='#' title='Listagem de Fornecedor(es)' class='link'><font color='red'>PRODUTO SEM FORNECEDOR</font></a>";
                $cor                    = 'vermelho';
                $id_pais                = 31;
                $id_fornecedor          = '';
                $tp_moeda               = 0;
                $preco_faturado         = number_format(0, 2, ',', '.');
                $preco_faturado_export  = 0;
                $preco_faturado_export_adicional = 0;
                $valor_moeda_compra     = number_format(0, 2, ',', '.');
                $data_sys               = '&nbsp;';
                $funcao                 = '';
            }
?>
    <tr class='linhanormal' onclick="<?=$funcao;?>" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_fornecedor_prod_insumo[]' id='chkt_fornecedor_prod_insumo<?=$i;?>' value="<?=$campos_lista[0]['id_fornecedor_prod_insumo'];?>" onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
        <?
            $sql = "SELECT `qtde` 
                    FROM `estoques_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
            $campos_estoque = bancos::sql($sql);
            echo number_format($campos_estoque[0]['qtde'], 2, ',', '.');
        ?>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../../../classes/produtos_insumos/detalhes_producao.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'CONSULTAR', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Locais Atrelados" class='link'>
                <?=$campos[$i]['discriminacao'];?>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'POP', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes da Última Compra" class='link'>
                <img src="../../../../imagem/visualizar_detalhes.png" title="Detalhes da Última Compra" alt="Detalhes da Última Compra" border="0">
            </a>
        </td>
        <td>
            <?=$data_sys;?>
        </td>
        <td onclick="nova_janela('../../../classes/produtos_insumos/marcar_fornecedor_default.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'CONSULTAR', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" align='left'>
            <?=$fornecedor;?>
        </td>
        <td>
            R$ <?=$preco_faturado;?><br>
            <input type='text' name="txt_preco_fat_nac_real[]" id="txt_preco_fat_nac_real<?=$i;?>" value="<?=number_format($preco_pi[0], 2, ',', '.');?>" size="10" onClick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>
        </td>
        <td>
            <?=number_format($preco_faturado_export, 2, ',', '.');?><br>
            <?
                    if($tp_moeda == 0) {
                            echo 'S/ ';
                    }else if($tp_moeda == 1) {
                            echo 'U$ ';
                    }else if($tp_moeda == 2) {
                            echo '&euro;&nbsp;&nbsp;';
                    }
                    $preco_com_adicional = $preco_faturado_export + $preco_faturado_export_adicional;
//Somente para os nacionais
                    if($id_pais == 31) {
                            $title = 'Valor Moeda p/ Compra.: '.$valor_moeda_compra;
                            $alt = 'Valor Moeda p/ Compra.: '.$valor_moeda_compra;
                    }else {
                            $title = '';
                            $alt = '';
                    }
            ?>
            <input type='text' name="txt_preco_fat_inter_est[]" id="txt_preco_fat_inter_est<?=$i;?>" value="<?=number_format($preco_com_adicional, 2, ',', '.');?>" title="<?=$title;?>" alt="<?=$alt;?>" size="8" onClick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>')" class='textdisabled' disabled>
            <input type="hidden" name="hdd_tipo_moeda[]" id="hdd_tipo_moeda<?=$i;?>" value="<?=$tp_moeda;?>" disabled>
            <input type="hidden" name="hdd_id_pais[]" id="hdd_id_pais<?=$i;?>" value="<?=$id_pais;?>" disabled>
            <input type="hidden" name="hdd_valor_moeda_compra[]" id="hdd_valor_moeda_compra<?=$i;?>" value="<?=$valor_moeda_compra;?>" disabled>
        </td>
        <td>
            <input type='text' name="txt_preco_fat_inter_real[]" id="txt_preco_fat_inter_real<?=$i;?>" value="<?=number_format($preco_pi[1], 2, ',', '.');?>" size="8" onClick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>
            <input type="hidden" name="hdd_fornecedor[]" id="hdd_fornecedor<?=$i;?>" value="<?=$id_fornecedor;?>" disabled>
        </td>
        <td align="right">
        <?
            if($preco_pi[0] > 0 && $preco_pi[1] > 0) {
                echo '<font color="red" title="Tem Preço Nacional e Estrangeiro cadastrados na Lista de Preço deste Fornecedor." style="cursor:help"><b>ERRO NA LISTA DE PREÇO</b></font>';
            }else {
                if(($preco_pi[0] + $preco_pi[1]) * $campos_estoque[0]['qtde'] > 0) {
                    echo number_format(($preco_pi[0] + $preco_pi[1]) * $campos_estoque[0]['qtde'], 2, ',', '.');
                    $total_estoque_todos_itens+= ($preco_pi[0] + $preco_pi[1]) * $campos_estoque[0]['qtde'];
                }
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='left'>
        <td colspan="5">
            <font color="#0000FF">
                D&oacute;lar Custo:
            </font> U$
            <?=number_format($dolar_custo, 2, ',', '.');?>
        </td>
        <td colspan="2">
            <font color="#0000FF">
                Euro Custo: 
            </font>
            &euro; <?=number_format($euro_custo, 2, ',', '.');?>
        </td>
        <td colspan='4'>
            <font color="#0000FF">
                Fator Importação: 
            </font>
            R$ <?=number_format($fator_importacao, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form','REDEFINIR')" style="color:#ff9900;" class='botao'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title="Consultar Novamente" onclick="window.location = 'itens.php'" class='botao'>
            <input type='submit' name='cmd_atualizar' value='Atualizar' title='Atualizar' class='botao'>
        </td>
        <td>
            <img src="../../../../imagem/seta_acima.gif" width="12" height="12" title="Atualizar Preço Fat. Nac. + Adic. R$" alt="Atualizar Preço Fat. Nac. + Adic. R$" onclick="atualizar_coluna(1, '<?=$i;?>')">
            &nbsp;<input type='text' name="txt_preco_fat_nac_real_geral" size="8" onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" class="caixadetexto">
        </td>
        <td>
            <img src="../../../../imagem/seta_acima.gif" width="12" height="12" title="Preço Fat. Inter. + Adic. Estr." alt="Preço Fat. Inter.+ Adic. Estr." onclick="atualizar_coluna(2, '<?=$i;?>')">
            &nbsp;<input type='text' name="txt_preco_fat_inter_est_geral" size="8" onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" class="caixadetexto">
        </td>
        <td>
            Total R$ 
        </td>
        <td align='right'>
            <?=number_format($total_estoque_todos_itens, 2, ',', '.');?>
        </td>
    </tr>
</table>
<input type='hidden' name='hdd_dolar_custo' value='<?=$dolar_custo;?>'>
<input type='hidden' name='hdd_euro_custo' value='<?=$euro_custo;?>'>
<input type='hidden' name='hdd_fator_importacao' value='<?=$fator_importacao;?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color="red"><b>Legenda:</b></font>

<font color="red"><b>* Cor Vermelha: </b></font>Produtos que não possuem nenhum pedido e nenhum fornecedor atrelado(s).

<font color="blue"><b>* Cor Azul: </b></font>Produtos que não possuem nenhum pedido, mas possuem fornecedor(es)
atrelado(s), no qual o sistema retorna o fornecedor que possui este produto com o seu maior preço.

<b>* Cor Preta: </b>Produtos que estão atrelado(s) a algum pedido ou alterado(s) por algum funcionário, e neste
o sistema retorna o último fornecedor através do último pedido de compra deste produto.

</pre>
<?
	}
}elseif ($passo == 2) {
    foreach($_POST['chkt_fornecedor_prod_insumo'] as $i => $id_fornecedor_prod_insumo) {
/*Busca o id e o preco_faturado, preco_faturado_estrangeiro da lista de preço na tabela "fornecedores_x_prod_insumos" 
"Lista de Preços" p/ poder achar a diferença do preço faturado adicional e atualizar na lista do produto e 
fornecedor específico passado por parâmetro ...*/
        $sql = "SELECT `id_produto_insumo`, `preco_faturado`, `preco_faturado_export` 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Encontrou ...
            $id_produto_insumo      = $campos[0]['id_produto_insumo'];
            $preco_faturado         = $campos[0]['preco_faturado'];
            $preco_faturado_export  = $campos[0]['preco_faturado_export'];
/*Fórmula: Preço Faturado Atual - Valor Digitado pelo usuário de preço faturado,
e daí joga no adicional*/
            $diferenca_adicional = $txt_preco_fat_nac_real[$i] - $preco_faturado;
/*Fórmula: Preço Faturado Estrangeiro - Valor Digitado pelo usuário de preço
faturado, e daí joga no adicional*/
            $diferenca_adicional_2 = $txt_preco_fat_inter_est[$i] - $preco_faturado_export;
/*Atualiza a Lista de Preços e Libero este PI "Matéria Prima" porque esse item de Lista está tendo atualização 
de Preço nesse exato momento, e esse novo Controle pelo campo `custo_pi_bloqueado` -> permitirá de o Custo ser 
Liberado caso ocorra isso nas "Etapas 1, 2, 3, 5 e 6" ...*/
            $sql = "UPDATE `fornecedores_x_prod_insumos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `preco_faturado_adicional` = '$diferenca_adicional', `preco_faturado_export_adicional` = '$diferenca_adicional_2', `data_sys` = '".date('Y-m-d H:i:s')."', `custo_pi_bloqueado` = 'N' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
            bancos::sql($sql);
//Atualizo a Tabela de "Produtos Insumos" com o id_funcionario que alterou o Fornecedor Default ...
            $sql = "UPDATE `produtos_insumos` SET `id_funcionario_fornecedor_default` = '$_SESSION[id_funcionario]' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'itens.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
//Aqui já deixa a data carregada, para o controle do JavaScript
    $data_atual 				= date('d/m/Y');
    $prazo_dias_validade_custo	= genericas::variavel(43);
    $data_retrocedente 			= data::adicionar_data_hora($data_atual, -$prazo_dias_validade_custo);
?>
<html>
<head>
<title>.:: Custo / Relatório de Estoque PI ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function atribuir_ultimos_x_dias() {
	if(document.form.chkt_atribuir_ultimos_x_dias.checked == true) {//Se estiver checado ...
            document.form.txt_data.value = '<?=$data_retrocedente;?>'
	}else {//Se não estiver checado ...
            document.form.txt_data.value = ''
	}
	document.form.txt_data.focus()
}
</Script>
</head>
<body onload='document.form.cmb_grupo.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' cellspacing='1' cellpadding='1' border='0' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Custo / Relatório de Estoque PI
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência / Grupo
        </td>
        <td>
            <select name="cmb_grupo" title="Selecione a Referência / Grupo" class='combo'>
            <?
                    $sql = "SELECT `id_grupo`, `nome` 
                            FROM `grupos` 
                            WHERE `ativo` = '1' ORDER BY nome ";
                    echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação" size="30" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data da Última Atualização
        </td>
        <td>
            <input type='text' name="txt_data" title="Digite a Data" size="11" maxlength="10" onkeyup="" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
            &nbsp;-&nbsp;
            <input type='checkbox' name='chkt_atribuir_ultimos_x_dias' value='1' title="Atribuir últimos <?=intval($prazo_dias_validade_custo);?> dias" onclick="atribuir_ultimos_x_dias()" id='label1' class="checkbox">
            <label for='label1'>Atribuir últimos <?=intval($prazo_dias_validade_custo);?> dias</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name="txt_observacao" title="Digite a Observação" size="40" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_atrelados_custos' value='1' title="Mostrar Somente os atrelados ao Custo" class="checkbox" id='label2' checked>
            <label for='label2'>Mostrar Somente os atrelados ao Custo</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_exibir_somente_precos_zerados' value='S' title="Exibir Somente Preços Zerados" class="checkbox" id='label3'>
            <label for='label3'>Exibir Somente Preços Zerados</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.cmb_grupo.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color='red'><b>Observação:</b></font>

Preços Faturados baseados na lista de Preço de Compras, referentes a última compra realizada do produto ao
seu último respectivo fornecedor.

<b>* Não traz PI do Tipo (PRAC).</b>
</pre>