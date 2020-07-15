<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_embarque		= $_POST['txt_data_embarque'];
    $txt_fator_correcao_mmv	= $_POST['txt_fator_correcao_mmv'];
    $txt_qtde_meses 		= $_POST['txt_qtde_meses'];
    $cmb_tipo_compra 		= $_POST['cmb_tipo_compra'];
}else {
    $txt_data_embarque		= $_GET['txt_data_embarque'];
    $txt_fator_correcao_mmv	= $_GET['txt_fator_correcao_mmv'];
    $txt_qtde_meses 		= $_GET['txt_qtde_meses'];
    $cmb_tipo_compra 		= $_GET['cmb_tipo_compra'];
}

//Na primeira vez que carregar a Tela, o Sistema sugere Normal para o Tipo de Compra ...
if(empty($cmb_tipo_compra)) $cmb_tipo_compra = 'N';
?>
<html>
<head>
<title>.:: Pendências ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calcular(indice) {
    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde[]'].length)
    }
    var desconto = strtofloat(document.form.txt_desconto.value)
    var total_geral = 0
    if(typeof(indice) != 'undefined') {//Significa que o usuário está digitando em uma única linha específica ...
        var qtde                = strtofloat(document.getElementById('txt_qtde'+indice).value)
        var preco_unitario      = strtofloat(document.getElementById('hdd_preco_unitario'+indice).value)
        
        document.getElementById('txt_preco_unitario'+indice).value = (preco_unitario * (100 - desconto) / 100)
        document.getElementById('txt_preco_unitario'+indice).value = arred(document.getElementById('txt_preco_unitario'+indice).value, 2, 1)
        
        var preco_unitario_com_desconto = strtofloat(document.getElementById('txt_preco_unitario'+indice).value)
        document.getElementById('txt_preco_total'+indice).value = qtde * preco_unitario_com_desconto
        document.getElementById('txt_preco_total'+indice).value = arred(document.getElementById('txt_preco_total'+indice).value, 2, 1)
    }else {//Significa que o usuário NÃO está digitando em uma única linha ...
        for(var i = 0; i < linhas; i++) {
            var qtde            = strtofloat(document.getElementById('txt_qtde'+i).value)
            var preco_unitario  = strtofloat(document.getElementById('hdd_preco_unitario'+i).value)
            
            document.getElementById('txt_preco_unitario'+i).value = (preco_unitario * (100 - desconto) / 100)
            document.getElementById('txt_preco_unitario'+i).value = arred(document.getElementById('txt_preco_unitario'+i).value, 2, 1)
            
            var preco_unitario_com_desconto = strtofloat(document.getElementById('txt_preco_unitario'+i).value)
            document.getElementById('txt_preco_total'+i).value = qtde * preco_unitario_com_desconto
            document.getElementById('txt_preco_total'+i).value = arred(document.getElementById('txt_preco_total'+i).value, 2, 1)
        }
    }
//Aqui eu faço o somatório de todos os Preços Totais
    for(var i = 0; i < linhas; i++) {
        total_geral+= eval(strtofloat(document.getElementById('txt_preco_total'+i).value))
    }
    document.form.txt_total_geral.value = total_geral
    document.form.txt_total_geral.value = arred(document.form.txt_total_geral.value, 2, 1)
}

function gerar_impressao_pendencias() {
    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        var qtde            = eval(strtofloat(document.getElementById('txt_qtde'+i).value))
        var compra_producao = eval(strtofloat(document.getElementById('txt_compra_producao'+i).value))

        //Nunca a Qtde Digitada pelo Usuário pode ser maior do que a Compra / Produção ...
        if(qtde > compra_producao) {
            var resposta = confirm('QUANTIDADE INVÁLIDA !!! QUANTIDADE MAIOR DO QUE A COMPRA / PRODUÇÃO !\n\nDESEJA CONTINUAR COM AS QUANTIDADES INVÁLIDAS ?')
            if(resposta == true) {
                break;//Esse break é para sair fora do Loop ...
            }else {
                document.getElementById('txt_qtde'+i).focus()
                document.getElementById('txt_qtde'+i).select()
                return false
            }
        }
    }
    
    for(var i = 0; i < linhas; i++) {
        //Deixo no Formato em que o Banco de Dados vai reconhecer ...
        //document.getElementById('txt_preco_total'+i).value      = strtofloat(document.getElementById('txt_preco_total'+i).value)
        //Desabilito esse campo p/ que os valores desse subam como parâmetro p/ a próxima Tela de Impressão ...
        document.getElementById('txt_preco_total'+i).disabled   = false
    }

    document.form.action = 'impressao_pendencias.php'//Significa q está sendo gerado de Vendas ...
    document.form.target = 'IMPRESSAO_PENDENCIAS'
    nova_janela('impressao_pendencias.php', 'IMPRESSAO_PENDENCIAS', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', '', '')
    limpeza_moeda('form', 'txt_fator_correcao_mmv, txt_qtde_meses, txt_desconto, ')
    document.form.submit()
}
</Script>
</head>
<body onload='calcular();document.form.txt_desconto.focus()'>
<form name='form' action='' method='post'>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Pendências - 
            <font color='yellow'>
                Data: 
            </font>
            <?=date('d/m/Y');?>
            <br/>
            <?
                if(!empty($txt_data_embarque)) {
            ?>
            <font color='yellow'>
                Data de Embarque Great: 
            </font>
            <?
                    echo $txt_data_embarque.'&nbsp;-&nbsp;';
                }
            ?>
            <font color='yellow'>
                Fat. MMV = 
            </font>
            <?=$txt_fator_correcao_mmv;?>&nbsp;e
            <?=str_replace('.', ',', $txt_qtde_meses);?> 
            <font color='yellow'>
                Mês(es)
            </font>
            &nbsp;-&nbsp;
            <select name='cmb_tipo_compra' title='Selecione o Tipo de Compra' onchange='document.form.submit()' class='combo'>
            <?
                if($cmb_tipo_compra == 'N') {
                    $selectedn = 'selected';
                }else {
                    $selectede = 'selected';
                }
            ?>
                <option value='N' <?=$selectedn;?>>Normal</option>
                <option value='E' <?=$selectede;?>>Export</option>
            </select>
            &nbsp;-&nbsp;
            Desconto: <input type='text' name='txt_desconto' value='<?=$txt_desconto;?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='9' maxlength='8' class='caixadetexto'>
        </td>
    </tr>
<?
    //Essas variáveis serão utilizadas mais abaixo ...
    $indice 		= 0;
    $urgentissimos 	= 0;
    $urgentes 		= 0;
/*******************************************Urgentíssimo(s)*******************************************/
    if(!empty($_POST['hdd_vetor_pas_urgentissimos'])) {
        $lista_pas_urgentissimos = substr($_POST['hdd_vetor_pas_urgentissimos'], 0, strlen($_POST['hdd_vetor_pas_urgentissimos']) - 1);
        $lista_pas_urgentissimos = explode(';', $lista_pas_urgentissimos);
    }
	
    for($i = 0; $i < count($lista_pas_urgentissimos); $i++) {
        $contador = 0;
        $id_produto_acabado = ''; $ec_negativo = ''; $compra_producao = '';
        $estoque_comprometido = ''; $estoque_programado = ''; $mmv = ''; $urgencia = '';
//Aqui eu vasculho cada caractér do Item da Lista ...
        for($j = 0; $j < strlen($lista_pas_urgentissimos[$i]); $j++) {
            if(substr($lista_pas_urgentissimos[$i], $j, 1) == '|') {
                $contador++;
            }else {
                if($contador == 0) {
                    $id_produto_acabado.= substr($lista_pas_urgentissimos[$i], $j, 1);
                }else if($contador == 1) {
                    $ec_negativo.= substr($lista_pas_urgentissimos[$i], $j, 1);
                }else if($contador == 2) {
                    $compra_producao.= substr($lista_pas_urgentissimos[$i], $j, 1);
                }else if($contador == 3) {
                    $estoque_comprometido.= substr($lista_pas_urgentissimos[$i], $j, 1);
                }else if($contador == 4) {
                    $estoque_programado.= substr($lista_pas_urgentissimos[$i], $j, 1);
                }else if($contador == 5) {
                    $mmv.= substr($lista_pas_urgentissimos[$i], $j, 1);
                }else if($contador == 6) {
                    $urgencia.= substr($lista_pas_urgentissimos[$i], $j, 1);
                }
            }
        }
        $vetor_produto_acabado[]        = $id_produto_acabado;
        $vetor_ec_negativo[]            = $ec_negativo;
        $vetor_compra_producao[]        = $compra_producao;
        $vetor_estoque_comprometido[] 	= $estoque_comprometido;
        $vetor_estoque_programado[] 	= $estoque_programado;
        $vetor_mmv[]                    = $mmv;
        $vetor_urgencia[]               = $urgencia;
    }
	
    if(count($vetor_produto_acabado) > 0) {
        $qtde_pipas = 0;//Aqui é para eu saber Qtdes PIs de PAs que existem nessa listagem ...
        for($i = 0; $i < count($vetor_produto_acabado); $i++) {	
            //Limpa essas variáveis para não herdar valores do Loop anterior ...	
            $mostrar_msn_blank = 0;
            $blanks_em_estoque = 0;
            //Busca da Operação do Produto Acabado ...
            $sql = "SELECT `operacao_custo` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$vetor_produto_acabado[$i]' LIMIT 1 ";
            $campos_oc_pa = bancos::sql($sql);
            //Busco o Custo Industrial desse PA na mesma Operação de Custo do PA ...
            $sql = "SELECT `id_produto_acabado_custo` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '$vetor_produto_acabado[$i]' 
                    AND `operacao_custo` = '".$campos_oc_pa[0]['operacao_custo']."' LIMIT 1 ";
            $campos_pa_custo = bancos::sql($sql);
            //Verifico se o PA tem algum PI que é do Grupo Blank na 3ª Etapa do Custo p/ buscar o seu preço ...
            $sql = "SELECT pp.`id_produto_insumo`, pa.`status_top`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
                    pa.`referencia`, pa.`discriminacao` 
                    FROM `pacs_vs_pis` pp 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pp.`id_produto_insumo` AND pi.`id_grupo` = '22' 
                    WHERE pp.`id_produto_acabado_custo` = '".$campos_pa_custo[0]['id_produto_acabado_custo']."' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos[0]['id_produto_insumo']) == 0) {//Se não achou PI na 3ª do Custo ...
                //Verifico se o PA é um PI que foi importado e está atrelado p/ buscar o seu preço ...
                $sql = "SELECT `id_produto_insumo`, `status_top`, `operacao_custo`, `operacao_custo_sub`, 
                        `referencia`, `discriminacao` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$vetor_produto_acabado[$i]' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 1 && $campos[0]['id_produto_insumo'] > 0) $qtde_pipas++;
            }else {
                $mostrar_msn_blank = 1;
                $qtde_pipas++;
            }

            //Aqui eu busco o Fornecedor Default do PI que é PA ...
            $sql = "SELECT `id_fornecedor_default` 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' 
                    AND `id_fornecedor_default` > '0' 
                    AND `ativo` = '1' ";
            $campos_fornecedor_default  = bancos::sql($sql);
            $id_fornecedor_default      = $campos_fornecedor_default[0]['id_fornecedor_default'];
            //Aqui eu busco o Preço de Lista do PI e do Fornecedor default ...
            $sql = "SELECT `preco`, `preco_exportacao` 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_fornecedor` = '$id_fornecedor_default' 
                    AND `id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' LIMIT 1 ";
            $campos_lista   = bancos::sql($sql);
            
            if($campos_oc_pa[0]['operacao_custo'] == 0 && $mostrar_msn_blank == 0) {//Se a OC do PA = Industrial e não tem Blank ...
                $vetor_valores = vendas::preco_venda($vetor_produto_acabado[$i]);
                $preco_produto = $vetor_valores['preco_venda_medio_rs'];//Trago o Preço de Venda do PA ...
            }else {//Outras Situações ...
                $preco_produto = ($cmb_tipo_compra == 'N') ? $campos_lista[0]['preco'] : $campos_lista[0]['preco_exportacao'];
            }

            $qtde           = (empty($txt_qtde[$i])) ? trim($vetor_urgencia[$i]) : $txt_qtde[$i];
            //Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo a coluna na cor vermelha ...
            $font_compra    = ($vetor_compra_producao[$i] < - ($vetor_estoque_comprometido[$i])) ? "<font color='red'><b>" : "<font color='black'>";
            
            //Quando esse campo "Great" for Preenchido - nunca iremos descontar o programado ...
            if(!empty($txt_data_embarque)) {
                /*Aqui eu busco os Pedidos da Great em Aberto que estejam com Importação atrelada 
                e esta importação comece com as iniciais GH ...*/
                $sql = "SELECT SUM(ip.qtde) AS qtde_compras_embarcadas 
                        FROM `itens_pedidos` ip 
                        INNER JOIN `pedidos` p ON p.id_pedido = ip.id_pedido AND p.status < '2' 
                        INNER JOIN `importacoes` i ON i.id_importacao = p.id_importacao AND SUBSTRING(i.nome, 1, 2) = 'GH' 
                        WHERE ip.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' ";
                $campos_qtde_compras_embarcadas = bancos::sql($sql);
                if($mostrar_msn_blank == 1) {
                    /**************************Cálculo de Blanks em nosso Estoque**************************/
                    $sql = "SELECT SUM(ip.qtde) AS qtde_pedidos_em_aberto_great 
                            FROM `itens_pedidos` ip 
                            INNER JOIN `pedidos` p ON p.id_pedido = ip.id_pedido AND p.status < '2' 
                            WHERE ip.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' ";
                    $campos_pedidos_em_aberto_great = bancos::sql($sql);
                    $compras_nao_embarcadas         = $campos_pedidos_em_aberto_great[0]['qtde_pedidos_em_aberto_great'] - $campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'];
                    $blanks_em_estoque 		= $vetor_compra_producao[$i] - $campos_pedidos_em_aberto_great[0]['qtde_pedidos_em_aberto_great'];
                    //As qtdes adquiridas dos Pedidos da Great, eu desconto da Qtde Urgente e a Qtde em Estoque ...
                    $qtde_show = $qtde - $campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'] - $blanks_em_estoque;
                }else {
                    //As qtdes adquiridas dos Pedidos da Great, eu desconto da Qtde Urgente ...
                    $qtde_show = $qtde - $campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'];
                }
            }else {
                $qtde_show = ($qtde - $vetor_estoque_programado[$i]);
            }
            if($qtde_show > 0) {//Só mostra a linha quando realmente existir necessidade ...
/****************Rótulos****************/
                if($urgentissimos == 0) {//Só exibo esses rótulos na 1ª Linha ...
?>
    <tr class='iframe' align='center'>
        <td colspan='12'>
            Urgentíssimo(s) - (Em Falta)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde<br/>Urgente
        </td>
        <td>
            Qtde<br/>Urg. Sug.
        </td>
        <td>
            <font style='cursor:help' title='Operação de Custo'>
                O.C.
            </font>
        </td>
        <td>
            Comp/Prod<br/>Total
        </td>
        <td>
            <font style='cursor:help' title='Estoque Comprometido Total'>
                E.C.<br/>Total
            </font>
        </td>
        <td>
            Prog.<br/>Total
        </td>
        <td>
            MMV<br/>Total
        </td>
        <td>
            Ref
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Pço. Unit.
        </td>
        <td>
            Pço. Total
        </td>
        <td>
            EC<br/>Pai
        </td>
    </tr>
<?
                }
/***************************************/
?>
    <tr class='linhanormal' align='center'>
        <td>
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$indice;?>' value='<?=$qtde_show;?>' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular('<?=$indice;?>')" size='9' maxlength='8' class='caixadetexto'>
        </td>
        <td>
            <?=number_format($vetor_urgencia[$i], 2, ',', '.');?>
        </td>
        <td>
        <?
            if($campos[0]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos[0]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }
            if($campos[0]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[0]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos[0]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos[0]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td>
        <?
            echo $font_compra.$vetor_compra_producao[$i];
            if($campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'] > 0) {
                echo ' / GH='.intval($campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas']);
                if($blanks_em_estoque > 0) echo ' + Est='.$blanks_em_estoque;
            }
            $estoque_produto = estoque_acabado::qtde_estoque($vetor_produto_acabado[$i], 0);
            if($estoque_produto[11] > 0) echo '<br/><font color="purple"><b>(OE='.number_format($estoque_produto[11], 0, '', '.').')</b></font>';
        ?>
            <input type='hidden' name='txt_compra_producao[]' id='txt_compra_producao<?=$indice;?>' value='<?=$vetor_compra_producao[$i];?>'>
        </td>
        <td>
        <?
            if($vetor_estoque_comprometido[$i] < 0) {
                echo "<font color='red'>".number_format($vetor_estoque_comprometido[$i], 0, ',', '.')."</font>";
            }else {
                echo number_format($vetor_estoque_comprometido[$i], 0, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if($vetor_estoque_programado[$i] < 0) {
                echo "<font color='red'>".segurancas::number_format($vetor_estoque_programado[$i], 2, '.')."</font>";
            }else {
                echo segurancas::number_format($vetor_estoque_programado[$i], 2, '.');
            }
        ?>
        </td>
        <td>
            <?=number_format($vetor_mmv[$i], 1, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[0]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[0]['discriminacao'];?>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class='link'>
                <img src = '../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class='link'>
                <img src = '../../../imagem/propriedades.png' title='Visualizar Orçamentos - Últimos 6 meses' alt='Visualizar Orçamentos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <?
                /*********************Links p/ abrir o Custo*********************/
                if($campos_oc_pa[0]['operacao_custo'] == 0) {//Industrial
            ?>
            <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" style='cursor:help' class='link'>
            <?
                }else {
            ?>
            <a href="javascript:nova_janela('../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$vetor_produto_acabado[$i];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" style='cursor:help' class='link'>
            <?
                }
            ?>
                <img src = '../../../imagem/menu/alterar.png' title='Visualizar Custo' alt='Visualizar Custo' border='0'>
            </a>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario[]' id='txt_preco_unitario<?=$indice;?>' value='<?=number_format($preco_produto, 2, ',', '.');?>' size='9' maxlength='8' class='textdisabled' disabled>
            <!--Esse preço em oculto, eu utilizo para os cálculos em JavaScript quando acrescento desconto ...-->
            <input type='hidden' name='hdd_preco_unitario[]' id='hdd_preco_unitario<?=$indice;?>' value="<?=number_format($preco_produto, 2, ',', '.');?>">
            <?if($mostrar_msn_blank == 1) echo '<font color="red"><b>BLANK</b></font>';?>
        </td>
        <td>
        <?
            $preco_total = $qtde * $preco_produto;
            $total_geral+= $preco_total;
        ?>
            <input type='text' name='txt_preco_total[]' id='txt_preco_total<?=$indice;?>' value="<?=number_format($preco_total, 2, ',', '.');?>" size='9' maxlength='8' class='textdisabled' disabled>
            <?if($id_fornecedor_default == 0) echo '<font color="red" title="Sem Fornecedor" style="cursor:help"><b>S/ FORN</b></font>';?>
            <!--Aqui eu guardo o ID PA caso eu deseje gerar uma Impressão ...-->
            <input type='hidden' name='hdd_produto_acabado[]' value="<?=$vetor_produto_acabado[$i].'|'.$vetor_urgencia[$i].'|'.$vetor_compra_producao[$i].'|'.$vetor_estoque_comprometido[$i].'|'.$vetor_estoque_programado[$i].'|'.$vetor_mmv[$i].'|Urgentíssimo';?>">
        </td>
        <td>
            <?=number_format($vetor_ec_negativo[$i], 1, ',', '.');?>
        </td>
    </tr>
<?
                $indice++;
                $urgentissimos++;
            }
        }
        unset($vetor_produto_acabado);
        unset($vetor_ec_negativo);
        unset($vetor_compra_producao);
        unset($vetor_estoque_comprometido);
        unset($vetor_estoque_programado);
        unset($vetor_mmv);
        unset($vetor_urgencia);
    }
/**********************************************Urgente(s)*********************************************/
    if(!empty($_POST['hdd_vetor_pas_urgentes'])) {
        $lista_pas_urgentes = substr($_POST['hdd_vetor_pas_urgentes'], 0, strlen($_POST['hdd_vetor_pas_urgentes']) - 1);
        $lista_pas_urgentes = explode(';', $lista_pas_urgentes);
    }
	
    for($i = 0; $i < count($lista_pas_urgentes); $i++) {
        $contador = 0;
        $id_produto_acabado = ''; $ec_p_x_meses = ''; $compra_producao = '';
        $estoque_comprometido = ''; $estoque_programado = ''; $mmv = ''; $urgencia = '';
//Aqui eu vasculho cada caractér do Item da Lista ...
        for($j = 0; $j < strlen($lista_pas_urgentes[$i]); $j++) {
            if(substr($lista_pas_urgentes[$i], $j, 1) == '|') {
                $contador++;
            }else {
                if($contador == 0) {
                    $id_produto_acabado.= substr($lista_pas_urgentes[$i], $j, 1);
                }else if($contador == 1) {
                    $ec_p_x_meses.= substr($lista_pas_urgentes[$i], $j, 1);
                }else if($contador == 2) {
                    $compra_producao.= substr($lista_pas_urgentes[$i], $j, 1);
                }else if($contador == 3) {
                    $estoque_comprometido.= substr($lista_pas_urgentes[$i], $j, 1);
                }else if($contador == 4) {
                    $estoque_programado.= substr($lista_pas_urgentes[$i], $j, 1);
                }else if($contador == 5) {
                    $mmv.= substr($lista_pas_urgentes[$i], $j, 1);
                }else if($contador == 6) {
                    $urgencia.= substr($lista_pas_urgentes[$i], $j, 1);
                }
            }
        }
        $vetor_produto_acabado[]        = $id_produto_acabado;
        $vetor_ec_p_x_meses[]           = $ec_p_x_meses;
        $vetor_compra_producao[]        = $compra_producao;
        $vetor_estoque_comprometido[] 	= $estoque_comprometido;
        $vetor_estoque_programado[] 	= $estoque_programado;
        $vetor_mmv[]                    = $mmv;
        $vetor_urgencia[]               = $urgencia;
    }
	
    if(count($vetor_produto_acabado) > 0) {
        $qtde_pipas = 0;//Aqui é para eu saber Qtdes PIs de PAs que existem nessa listagem ...
        for($i = 0; $i < count($vetor_produto_acabado); $i++) {
            $mostrar_msn_blank = 0;
            $blanks_em_estoque = 0;
            //Busca da Operação do Produto Acabado ...
            $sql = "SELECT `operacao_custo` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$vetor_produto_acabado[$i]' LIMIT 1 ";
            $campos_oc_pa = bancos::sql($sql);
            //Busco o Custo Industrial desse PA na mesma Operação de Custo do PA ...
            $sql = "SELECT `id_produto_acabado_custo` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '$vetor_produto_acabado[$i]' 
                    AND `operacao_custo` = '".$campos_oc_pa[0]['operacao_custo']."' LIMIT 1 ";
            $campos_pa_custo = bancos::sql($sql);
            //Verifico se o PA tem algum PI que é do Grupo Blank na 3ª Etapa do Custo p/ buscar o seu preço ...
            $sql = "SELECT pp.`id_produto_insumo`, pa.`status_top`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
                    pa.`referencia`, pa.`discriminacao` 
                    FROM `pacs_vs_pis` pp 
                    INNER JOIN produtos_acabados_custos pac on pac.id_produto_acabado_custo = pp.id_produto_acabado_custo 
                    INNER JOIN produtos_acabados pa on pa.id_produto_acabado = pac.id_produto_acabado 
                    INNER JOIN produtos_insumos pi on pi.id_produto_insumo = pp.id_produto_insumo and pi.id_grupo = '22' 
                    WHERE pp.`id_produto_acabado_custo` = '".$campos_pa_custo[0]['id_produto_acabado_custo']."' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if($campos[0]['id_produto_insumo'] == 0) {//Se não achou PI na 3ª do Custo ...
                //Verifico se o PA é um PI que foi importado e está atrelado p/ buscar o seu preço ...
                $sql = "SELECT `id_produto_insumo`, `status_top`, `operacao_custo`, `operacao_custo_sub`, 
                        `referencia`, `discriminacao` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$vetor_produto_acabado[$i]' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 1 && $campos[0]['id_produto_insumo'] > 0) $qtde_pipas++;
            }else {
                $mostrar_msn_blank = 1;
                $qtde_pipas++;
            }
			
            //Aqui eu busco o Fornecedor Default do PI que é PA ...
            $sql = "SELECT `id_fornecedor_default` 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' 
                    AND `id_fornecedor_default` > '0' 
                    AND `ativo` = '1' ";
            $campos_fornecedor_default  = bancos::sql($sql);
            $id_fornecedor_default      = $campos_fornecedor_default[0]['id_fornecedor_default'];
            //Aqui eu busco o Preço de Lista do PI e do Fornecedor default ...
            $sql = "SELECT `preco`, `preco_exportacao` 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_fornecedor` = '$id_fornecedor_default' 
                    AND `id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' LIMIT 1 ";
            $campos_lista   = bancos::sql($sql);
            
            if($campos_oc_pa[0]['operacao_custo'] == 0 && $mostrar_msn_blank == 0) {//Se a OC do PA = Industrial e não tem Blank ...
                $vetor_valores = vendas::preco_venda($vetor_produto_acabado[$i]);
                $preco_produto = $vetor_valores['preco_venda_medio_rs'];//Trago o Preço de Venda do PA ...
            }else {//Outras Situações ...
                $preco_produto = ($cmb_tipo_compra == 'N') ? $campos_lista[0]['preco'] : $campos_lista[0]['preco_exportacao'];
            }

            $qtde           = (empty($txt_qtde[$i])) ? trim($vetor_urgencia[$i]) : $txt_qtde[$i];
            //Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo a coluna na cor vermelha ...
            $font_compra    = ($vetor_compra_producao[$i] < - ($vetor_estoque_comprometido[$i])) ? "<font color='red'><b>" : "<font color='black'>";
            
            //Quando esse campo "Great" for Preenchido - nunca iremos descontar o programado ...
            if(!empty($txt_data_embarque)) {
                /*Aqui eu busco os Pedidos da Great em Aberto que estejam com Importação atrelada 
                e esta importação comece com as iniciais GH ...*/
                $sql = "SELECT SUM(ip.`qtde`) AS qtde_compras_embarcadas 
                        FROM `itens_pedidos` ip 
                        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`status` < '2' 
                        INNER JOIN `importacoes` i ON i.id_importacao = p.id_importacao AND SUBSTRING(i.nome, 1, 2) = 'GH' 
                        WHERE ip.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' ";
                $campos_qtde_compras_embarcadas = bancos::sql($sql);
                //Se for Blank, busco todos os Pedidos da Great em Aberto, independente de ter Importação 
                if($mostrar_msn_blank == 1) {
                    /**************************Cálculo de Blanks em nosso Estoque**************************/
                    $sql = "SELECT SUM(ip.qtde) AS qtde_pedidos_em_aberto_great 
                            FROM `itens_pedidos` ip 
                            INNER JOIN `pedidos` p ON p.id_pedido = ip.id_pedido AND p.status < '2' 
                            WHERE ip.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' ";
                    $campos_pedidos_em_aberto_great = bancos::sql($sql);
                    $compras_nao_embarcadas = $campos_pedidos_em_aberto_great[0]['qtde_pedidos_em_aberto_great'] - $campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'];
                    $blanks_em_estoque      = $vetor_compra_producao[$i] - $campos_pedidos_em_aberto_great[0]['qtde_pedidos_em_aberto_great'];
                    //As qtdes adquiridas dos Pedidos da Great, eu desconto da Qtde Urgente e a Qtde em Estoque ...
                    $qtde_show              = $qtde - $campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'] - $blanks_em_estoque;
                    
                    echo $qtde_show.'<br/>';
                    
                }else {
                    //As qtdes adquiridas dos Pedidos da Great, eu desconto da Qtde Urgente ...
                    $qtde_show              = $qtde - $campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'];
                }
            }else {
                $qtde_show                  = ($qtde - $vetor_estoque_programado[$i]);
            }
            
            if($qtde_show > 0) {//Só mostra a linha quando realmente existir necessidade ...
/****************Rótulos****************/
                if($urgentes == 0) {//Só exibo esses rótulos na 1ª Linha ...
?>
    <tr class='iframe' align='center'>
        <td colspan='12'>
            Urgente(s) - (Estoque Baixo)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde<br/>Urgente
        </td>
        <td>
            Qtde<br/>Urg. Sug.
        </td>
        <td>
            <font title='Operação de Custo' style='cursor:help'>
                O.C.
            </font>
        </td>
        <td>
            Comp/Prod<br/>Total
        </td>
        <td>
            <font title='Estoque Comprometido Total' style='cursor:help'>
                E.C.<br/>Total
            </font>
        </td>
        <td>
            Prog.<br/>Total
        </td>
        <td>
            MMV<br/>Total
        </td>
        <td>
            Ref
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Pço. Unit.
        </td>
        <td>
            Pço. Total
        </td>
        <td>
            EC p/x <br/>meses
        </td>
    </tr>
<?
                }
/***************************************/
?>
    <tr class='linhanormal' align='center'>
        <td>
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$indice;?>' value='<?=$qtde_show;?>' onkeyup="calcular('<?=$indice;?>')" size='9' maxlength='8' class='caixadetexto'>
        </td>
        <td>
            <?=number_format($vetor_urgencia[$i], 2, ',', '.');?>
        </td>
        <td>
        <?
            if($campos[0]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos[0]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }
            if($campos[0]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[0]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos[0]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos[0]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td>
        <?
            echo $font_compra.$vetor_compra_producao[$i];
            if($campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas'] > 0) {
                echo ' / GH='.intval($campos_qtde_compras_embarcadas[0]['qtde_compras_embarcadas']);
                if($blanks_em_estoque > 0) echo ' + Est='.$blanks_em_estoque;
            }
            $estoque_produto = estoque_acabado::qtde_estoque($vetor_produto_acabado[$i], 0);
            if($estoque_produto[11] > 0) echo '<br/><font color="purple"><b>(OE='.number_format($estoque_produto[11], 0, '', '.').')</b></font>';
        ?>
            <input type='hidden' name='txt_compra_producao[]' id='txt_compra_producao<?=$indice;?>' value='<?=$vetor_compra_producao[$i];?>'>
        </td>
        <td>
        <?
            if($vetor_estoque_comprometido[$i] < 0) {
                echo "<font color='red'>".number_format($vetor_estoque_comprometido[$i], 0, ',', '.')."</font>";
            }else {
                echo number_format($vetor_estoque_comprometido[$i], 0, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if($vetor_estoque_programado[$i] < 0) {
                echo "<font color='red'>".segurancas::number_format($vetor_estoque_programado[$i], 2, '.')."</font>";
            }else {
                echo segurancas::number_format($vetor_estoque_programado[$i], 2, '.');
            }
        ?>
        </td>
        <td>
            <?=number_format($vetor_mmv[$i], 1, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[0]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[0]['discriminacao'];?>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class='link'>
                <img src = '../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class='link'>
                <img src = '../../../imagem/propriedades.png' title='Visualizar Orçamentos - Últimos 6 meses' alt='Visualizar Orçamentos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <?
                /*********************Links p/ abrir o Custo*********************/
                if($campos_oc_pa[0]['operacao_custo'] == 0) {//Industrial
            ?>
            <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$vetor_produto_acabado[$i];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" style='cursor:help' class='link'>
            <?
                }else {
            ?>
            <a href="javascript:nova_janela('../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$vetor_produto_acabado[$i];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" style='cursor:help' class='link'>
            <?
                }
            ?>
                <img src = '../../../imagem/menu/alterar.png' title='Visualizar Custo' alt='Visualizar Custo' border='0'>
            </a>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario[]' id='txt_preco_unitario<?=$indice;?>' value='<?=number_format($preco_produto, 2, ',', '.');?>' size='9' maxlength='8' class='textdisabled' disabled>
            <!--Esse preço em oculto, eu utilizo para os cálculos em JavaScript quando acrescento desconto ...-->
            <input type='hidden' name='hdd_preco_unitario[]' id='hdd_preco_unitario<?=$indice;?>' value="<?=number_format($preco_produto, 2, ',', '.');?>">
            <?if($mostrar_msn_blank == 1) echo '<font color="red"><b>BLANK</b></font>';?>
        </td>
        <td>
            <?
                $preco_total = $qtde * $preco_produto;
                $total_geral+= $preco_total;
            ?>
            <input type='text' name='txt_preco_total[]' id='txt_preco_total<?=$indice;?>' value='<?=number_format($preco_total, 2, ',', '.');?>' size='9' maxlength='8' class='textdisabled' disabled>
            <?if($id_fornecedor_default == 0) echo '<font color="red" title="Sem Fornecedor" style="cursor:help"><b>S/ FORN</b></font>';?>
            <!--Aqui eu guardo o ID PA caso eu deseje gerar uma Impressão ...-->
            <input type='hidden' name='hdd_produto_acabado[]' value="<?=$vetor_produto_acabado[$i].'|'.$vetor_urgencia[$i].'|'.$vetor_compra_producao[$i].'|'.$vetor_estoque_comprometido[$i].'|'.$vetor_estoque_programado[$i].'|'.$vetor_mmv[$i].'|Urgente';?>">
        </td>
        <td>
            <?=number_format($vetor_ec_p_x_meses[$i], 1, ',', '.');?>
        </td>
    </tr>
<?
                $indice++;
                $urgentes++;
            }
        }
    }
	
    if($urgentissimos > 0 || $urgentes > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class='botao'>
            <input type='button' name="cmd_gerar_impressao_pendencias" value="Gerar Impressão" title="Gerar Impressão de Pendências" onclick="gerar_impressao_pendencias()" style="color:darkgreen" class='botao'>
        </td>
        <td>
            Total =>
        </td>
        <td>
            <input type='text' name='txt_total_geral' value='<?=number_format($total_geral, 2, ',', '.');?>' size='9' maxlength='8' class='textdisabled' disabled>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    }else {
?>	
    <tr align='center'>
        <td colspan='12'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?	
    }
?>
</table>
<input type='hidden' name='txt_fator_correcao_mmv' value='<?=$txt_fator_correcao_mmv;?>'>
<input type='hidden' name='txt_qtde_meses' value='<?=$txt_qtde_meses;?>'>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>

* Quando usamos Data de Embarque Great, ignoramos o Prog. Total e descontamos da Qtde Urgente 
a Compra / Prod. de Pedidos que possuem importações com inicias GH.

<pre>
<b><font color='blue'>Cálculos:</font></b>

* Qtde Urgente = Qtde Urg.Sug. - Prog. Total

* Se GREAT: Qtde Urgente = Qtde Urg.Sug. - Importações Embarcadas (GH)

* Se GREAT / BLANK: Qtde Urgente = Qtde Urg.Sug. - Importações Embarcadas (GH) - Blanks em Estoque (Est)
</pre>