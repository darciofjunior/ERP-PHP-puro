<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
require('../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
/*Esse trecho de tela foi feito em um arquivo à parte, p/ evitar de recarregar toda a tela do 
Estoque Acabado que daí seria muito lento, achamos mais fácil e mais rápido recarregar apenas
o Iframe que é exatamente esse arquivo na hora em que o usuário altera o Prazo de Entrega ...*/
$data_atual_mais_sete = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_referencia 		= $_POST['txt_referencia'];
    $txt_discriminacao 		= $_POST['txt_discriminacao'];
    $chkt_mostrar_componentes 	= $_POST['chkt_mostrar_componentes'];
    $chkt_est_disp_comp_zero 	= $_POST['chkt_est_disp_comp_zero'];
    $chkt_exibir_esp 		= $_POST['chkt_exibir_esp'];
    $cmb_familia                = $_POST['cmb_familia'];
    $cmb_grupo_pa               = $_POST['cmb_grupo_pa'];
    $hidden_operacao_custo      = $_POST['hidden_operacao_custo'];
    $cmb_operacao_custo         = $_POST['cmb_operacao_custo'];
    $hidden_operacao_custo_sub 	= $_POST['hidden_operacao_custo_sub'];
    $cmb_operacao_custo_sub 	= $_POST['cmb_operacao_custo_sub'];
    $txt_fornecedor 		= $_POST['txt_fornecedor'];
    $txt_qtde_meses 		= $_POST['txt_qtde_meses'];
    $txt_data_embarque		= $_POST['txt_data_embarque'];
    $txt_fator_correcao_mmv 	= $_POST['txt_fator_correcao_mmv'];
    $chkt_nao_mostrar_filhos 	= $_POST['chkt_nao_mostrar_filhos'];
    $chkt_pa_componente         = $_POST['chkt_pa_componente'];
    $chkt_mostrar_top		= $_POST['chkt_mostrar_top'];
}else {
    $txt_referencia 		= $_GET['txt_referencia'];
    $txt_discriminacao 		= $_GET['txt_discriminacao'];
    $chkt_mostrar_componentes 	= $_GET['chkt_mostrar_componentes'];
    $chkt_est_disp_comp_zero 	= $_GET['chkt_est_disp_comp_zero'];
    $chkt_exibir_esp 		= $_GET['chkt_exibir_esp'];
    $cmb_familia                = $_GET['cmb_familia'];
    $cmb_grupo_pa               = $_GET['cmb_grupo_pa'];
    $hidden_operacao_custo      = $_GET['hidden_operacao_custo'];
    $cmb_operacao_custo         = $_GET['cmb_operacao_custo'];
    $hidden_operacao_custo_sub 	= $_GET['hidden_operacao_custo_sub'];
    $cmb_operacao_custo_sub 	= $_GET['cmb_operacao_custo_sub'];
    $txt_fornecedor 		= $_GET['txt_fornecedor'];
    $txt_qtde_meses 		= $_GET['txt_qtde_meses'];
    $txt_data_embarque		= $_GET['txt_data_embarque'];
    $txt_fator_correcao_mmv 	= $_GET['txt_fator_correcao_mmv'];
    $chkt_nao_mostrar_filhos 	= $_GET['chkt_nao_mostrar_filhos'];
    $chkt_pa_componente         = $_GET['chkt_pa_componente'];
    $chkt_mostrar_top		= $_GET['chkt_mostrar_top'];
}

//Na primeira vez que carregar a Tela, o Sistema sugere 1 para o período ...
if(empty($txt_qtde_meses))                                              $txt_qtde_meses = '1,0';
if(empty($txt_fator_correcao_mmv) || $txt_fator_correcao_mmv == '0,0')  $txt_fator_correcao_mmv = '1,0';

//Se não estiver habilitado o checkbox, só não mostra os P.A. q pertecem a família de Componentes
if(empty($chkt_mostrar_componentes)) $condicao = ' AND gpa.id_familia <> 23 ';

if(!empty($chkt_est_disp_comp_zero)) { // c tiver checked
    $condicao.= " AND (ea.qtde_disponivel - ea.qtde_pendente) < 0  ";
    $order_by = "(-(ea.qtde_disponivel-ea.qtde_pendente)*(pa.preco_unitario*(1-ged.desc_base_a_nac/100)*(1-ged.desc_base_b_nac/100)*(1+ged.acrescimo_base_nac/100))*ged.desc_medio_pa) desc"; // aqui nao posso passar o apelido por causa da sql de paginacao ela nao intende este apelido e dar erro
}else if(!empty($chkt_mostrar_top)) {
    $condicao_top   = " AND pa.posicao_top > '0'  ";
    $order_by       = "pa.posicao_top  ";
}else {
    $order_by       = "pa.discriminacao ";
}

//Significa que o usuário só quer ver os PA(s) que são normais de linha
if(empty($chkt_exibir_esp)) $condicao_esp = " AND pa.referencia <> 'ESP' ";

if($cmb_familia == '') $cmb_familia = '%';
if($cmb_grupo_pa == '') $cmb_grupo_pa = '%';

/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
if($hidden_operacao_custo == 1) {//Operação de Custo = Industrial
    $cmb_operacao_custo = 0;
}else if($hidden_operacao_custo == 2) {//Operação de Custo = Revenda
    $cmb_operacao_custo = 1;
}else {//Independente da Operação de Custo
    if($cmb_operacao_custo == '') $cmb_operacao_custo = '%';
}

//Segunda adaptação
if($hidden_operacao_custo_sub == 1) {//Sub-Operação de Custo = Industrial
    $cmb_operacao_custo_sub = 0;
}else if($hidden_operacao_custo_sub == 2) {//Sub-Operação de Custo = Revenda
    $cmb_operacao_custo_sub = 1;
}else {//Independente da Sub-Operação de Custo
    if($cmb_operacao_custo_sub == '') $cmb_operacao_custo_sub = '%';
}

//Se estiver preenchido o "Fornecedor Default" ...
if(!empty($txt_fornecedor)) {
    //Aqui busco todos os PA do "Fornecedor Default", mas somente os PA que são do Tipo PI's ...
    /*Código comentado no dia 05/05/2014 às 18:50 a pedido do Roberto porque hoje compramos os mesmos Machos 
    de DOIS fornecedores diferentes ...

    $sql = "SELECT pa.id_produto_acabado 
            FROM `fornecedores` f 
            INNER JOIN `produtos_insumos` pi ON pi.`id_fornecedor_default` = f.`id_fornecedor` AND pi.`id_fornecedor_default` > '0' AND pi.`ativo` = '1' 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`ativo` = '1' $condicao_esp 
            WHERE f.`razaosocial` LIKE '%$txt_fornecedor%' ORDER BY pa.id_produto_acabado ";
     */

    $sql = "SELECT pa.`id_produto_acabado` 
            FROM `fornecedores` f 
            INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor` = f.`id_fornecedor` AND fpi.`ativo` = '1' 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`id_produto_insumo` > '0' AND pa.`ativo` = '1' $condicao_esp 
            WHERE f.`razaosocial` LIKE '%$txt_fornecedor%' ORDER BY pa.`id_produto_acabado` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {//Se encontrou pelo menos 1 registro do Fornecedor digitado pelo Usuário ...
        for($i = 0; $i < $linhas; $i++) $id_produto_acabados.=$campos[$i]['id_produto_acabado'].', ';
        $id_produto_acabados = substr($id_produto_acabados, 0, strlen($id_produto_acabados) - 2);
        $condicao_pas = "AND pa.`id_produto_acabado` IN ($id_produto_acabados) ";
    }else {//Não encontrou nada ...
        $id_produto_acabados = 0;
    }
    $condicao_pas = " AND pa.`id_produto_acabado` IN ($id_produto_acabados) ";
}

$data_sys = date('Y-m-d');

if(!empty($chkt_prod_prazo_atual_sete)) {
    //Faço uma verificação de Toda(s) as OP(s) que estão em abertas e que possuem esse PA atrelado ...
    $sql_smart = "SELECT `id_produto_acabado` 
                    FROM `ops` 
                    WHERE (DATE_ADD('$data_sys', INTERVAL -7 DAY) > SUBSTRING(`data_ocorrencia`, 1, 10) || `data_ocorrencia` = '0000-00-00 00:00:00') 
                    AND `status_finalizar` = '0' 
                    AND `ativo` = '1' 
                    UNION 
                    SELECT ea.`id_produto_acabado` 
                    FROM `estoques_acabados` ea
                    INNER JOIN produtos_acabados pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` 
                    WHERE `operacao_custo` = '1' AND (DATE_ADD('$data_sys', INTERVAL -7 DAY) > SUBSTRING(ea.`data_atualizacao_prazo_ent`, 1, 10) || ea.`data_atualizacao_prazo_ent` = '0000-00-00 00:00:00') ";
}else {
    $sql_smart = "SELECT id_produto_acabado FROM `produtos_acabados` WHERE `ativo` = '1' ";
}
	
if(!empty($chkt_prod_prazo_atual_sete)) {
//if($operacao_custo == 0) {//P.A. Industrial
    //Faço uma verificação de Toda(s) as OP(s) que estão em abertas e que possuem esse PA atrelado ...
    $sql_smart = "SELECT `id_produto_acabado` 
                    FROM `ops` 
                    WHERE (DATE_ADD('$data_sys', INTERVAL -7 DAY) > SUBSTRING(`data_ocorrencia`, 1, 10) || `data_ocorrencia` = '0000-00-00 00:00:00') 
                    AND `status_finalizar` = '0' 
                    AND `ativo` = '1' 
                    UNION 
                    SELECT ea.`id_produto_acabado` 
                    FROM `estoques_acabados` ea 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` 
                    WHERE `operacao_custo` = '1' AND (DATE_ADD('$data_sys', INTERVAL -7 DAY) > SUBSTRING(ea.`data_atualizacao_prazo_ent`, 1, 10) || ea.`data_atualizacao_prazo_ent` = '0000-00-00 00:00:00') ";
//}
}else {
    $sql_smart = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `ativo` = '1' ";
}
	
/*Aqui eu só trago PA(s) que estejam na 1ª cadeia do Filtro, exemplo:

TM-020 está na primeira cadeia, que por sua vez tem o filho TM-020s, 
Sendo assim eu não trago o TM-020s no 1º nível*/
    if($chkt_nao_mostrar_filhos == 'S') {
        $condicao_nao_mostrar_filhos = " AND pa.`id_produto_acabado` NOT IN 
                                        (SELECT DISTINCT(pa.`id_produto_acabado`) 
                                        FROM `pacs_vs_pas` pp 
                                        INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`referencia` <> 'ESP') ";
    }else {
        $condicao_nao_mostrar_filhos = '';
    }

    $sql = "SELECT DISTINCT(pa.`id_produto_acabado`), pa.`status_top`, pa.`operacao_custo`, pa.`operacao_custo_sub`, pa.`referencia`, 
            pa.`discriminacao`, pa.`pecas_por_jogo`, pa.`observacao` AS observacao_pa, gpa.`id_familia` 
            FROM `produtos_acabados` pa 
            INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_grupo_pa` LIKE '$cmb_grupo_pa' 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` LIKE '$cmb_familia' 
            WHERE pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' $condicao_esp 
            AND pa.`operacao_custo` LIKE '$cmb_operacao_custo' 
            AND pa.`operacao_custo_sub` LIKE '$cmb_operacao_custo_sub' 
            AND pa.`ativo` = '1' $condicao_pas $condicao $condicao_top 
            AND pa.`id_produto_acabado` IN ($sql_smart) 
            $condicao_nao_mostrar_filhos 
            GROUP BY pa.`id_produto_acabado` 
            ORDER BY $order_by ";
    $campos = bancos::sql($sql, $inicio, 150, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            alert('SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO !')
            window.close()
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Relatório de Compra Produção ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function enviar_dados(e) {
    var executar = 0
    if(e == null) {
        executar = 1
    }else {
        if(navigator.appName == 'Microsoft Internet Explorer') {
            if(e.keyCode == 13) executar = 1
        }else {
            if(e.which == 13) executar = 1
        }
    }
    var periodo = document.form.txt_qtde_meses.value
    //Aqui eu forço o usuário a preencher a Data de Embarque de forma correta com os 10 dígitos ...
    if(document.form.txt_data_embarque.value.length > 0 && document.form.txt_data_embarque.value.length < 10) {
        alert('DATA DE EMBARQUE INVÁLIDA !')
        document.form.txt_data_embarque.focus()
        document.form.txt_data_embarque.select()
        return false
    }else {
        var data_embarque   = document.form.txt_data_embarque.value
    }
    var fator_correcao_mmv  = document.form.txt_fator_correcao_mmv.value

    if(executar == 1) {
        window.location = 'relatorio_compra_producao.php<?=$parametro;?>&txt_qtde_meses='+periodo+'&txt_data_embarque='+data_embarque+'&txt_fator_correcao_mmv='+fator_correcao_mmv+'&ancora=1'
    }
}

function calcular_qtde_meses() {
    var data_embarque = document.form.txt_data_embarque.value
    if(data_embarque.length == 10) {//Se a Data de Embarque já estiver digitada de forma completa ...
        var data_atual = '<?=date('d/m/Y');?>'
        var dias_viagem_navio = 32//Para Great China ...
        var dias_retirada_porto = 15
        var dias_estocagem = 30
        var diferenca_qtde_dias = eval(diferenca_datas(data_atual, data_embarque))
        //Verifico se a Data de Embarque é menor ou igual a Data Atual ...
        if(diferenca_qtde_dias == 0) {
            alert('DATA DE EMBARQUE INVÁLIDA !!!\nDATA DE EMBARQUE MENOR OU IGUAL A DATA ATUAL !')
            document.form.txt_data_embarque.focus()
            document.form.txt_data_embarque.select()
            return false
        }
        var qtde_dias = diferenca_qtde_dias + dias_viagem_navio + dias_retirada_porto + dias_estocagem
        var qtde_meses = qtde_dias / 30//Aqui eu transformo em Meses
        document.form.txt_qtde_meses.value = qtde_meses
        document.form.txt_qtde_meses.value = arred(document.form.txt_qtde_meses.value, 1, 1)
    }else {
        document.form.txt_qtde_meses.value = ''
    }
}

function compra_producao() {
    var qtde_meses_submetido    = eval(strtofloat('<?=$txt_qtde_meses;?>'))
    var qtde_meses_digitado     = eval(strtofloat(document.form.txt_qtde_meses.value))
    var fornecedor              = '<?=strtoupper($txt_fornecedor);?>'
    
    /*
    Comentado no dia 05/10/2017 a Pedido do Roberto porque nesse momento estamos trabalhando com Estoques menores ...
     
     if(qtde_meses_digitado < 1) {
        alert('QTDE DE MESES INVÁLIDA !!!\nQTDE DE MESES NÃO PODE SER MENOR QUE HUM !')
        document.form.txt_qtde_meses.focus()
        document.form.txt_qtde_meses.select()
        return false
    }*/
    
    if(qtde_meses_submetido != qtde_meses_digitado) {
        alert('CLIQUE NO BOTÃO ATUALIZAR !')
        document.form.cmd_atualizar.focus()
        return false
    }
    
    /*Aqui o sistema verifica se o usuário digitou "Winner" ou "Intertaps" no campo Fornecedor ...
    var achou_winner    = fornecedor.indexOf('WIN')
    var achou_intertaps = fornecedor.indexOf('INTER')

    if(achou_winner >= 0 || achou_intertaps > 0) alert('PARA PEDIDO DE MACHOS NORMAIS DE LINHA PARA WINNER OU INTERTAPS, POR 10% DE DESCONTO !')*/
    
    document.form.action = 'imprimir_relatorio_compra_producao.php'
    document.form.target = 'IMPRIMIR_RELATORIO'
    nova_janela('imprimir_relatorio_compra_producao.php', 'IMPRIMIR_RELATORIO', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function compra_producao_pendencias(acao) {
    var qtde_meses_submetido    = eval(strtofloat('<?=$txt_qtde_meses;?>'))
    var qtde_meses_digitado     = eval(strtofloat(document.form.txt_qtde_meses.value))
    var fornecedor              = '<?=strtoupper($txt_fornecedor);?>'
    
    /*
    Comentado no dia 05/10/2017 a Pedido do Roberto porque nesse momento estamos trabalhando com Estoques menores ...
     
     if(qtde_meses_digitado < 1) {
        alert('QTDE DE MESES INVÁLIDA !!!\nQTDE DE MESES NÃO PODE SER MENOR QUE HUM !')
        document.form.txt_qtde_meses.focus()
        document.form.txt_qtde_meses.select()
        return false
    }*/
    
    if(qtde_meses_submetido != qtde_meses_digitado) {
        alert('CLIQUE NO BOTÃO ATUALIZAR !')
        document.form.cmd_atualizar.focus()
        return false
    }
    
    //Aqui o sistema verifica se o usuário digitou "Winner" ou "Intertaps" no campo Fornecedor ...
    var achou_winner    = fornecedor.indexOf('WIN')
    var achou_intertaps = fornecedor.indexOf('INTER')

    if(achou_winner >= 0 || achou_intertaps > 0) alert('PARA PEDIDO DE MACHOS NORMAIS DE LINHA PARA WINNER OU INTERTAPS, POR 10% DE DESCONTO !')
    
    if(acao == 'COMPRA_PRODUCAO') {
        document.form.hdd_acao.value = 'COMPRA_PRODUCAO'
    }else {
        document.form.hdd_acao.value = 'PENDENCIAS'
    }
    
    document.form.action = 'compra_producao_pendencias.php'
    document.form.target = 'COMPRA_PRODUCAO_PENDENCIAS'
    nova_janela('compra_producao_pendencias.php', 'COMPRA_PRODUCAO_PENDENCIAS', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function pendencias() {
    var qtde_meses_submetido    = eval(strtofloat('<?=$txt_qtde_meses;?>'))
    var qtde_meses_digitado     = eval(strtofloat(document.form.txt_qtde_meses.value))
    
    /*if(qtde_meses_digitado < 1) {
        alert('QTDE DE MESES INVÁLIDA !!!\nQTDE DE MESES NÃO PODE SER MENOR QUE HUM !')
        document.form.txt_qtde_meses.focus()
        document.form.txt_qtde_meses.select()
        return false
    }*/
    
    if(qtde_meses_submetido != qtde_meses_digitado) {
        alert('CLIQUE NO BOTÃO ATUALIZAR !')
        document.form.cmd_atualizar.focus()
        return false
    }
    
    document.form.action = 'pendencias.php'
    document.form.target = 'PENDENCIAS'
    nova_janela('pendencias.php', 'PENDENCIAS', '', '', '', '', 520, 920, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function cotacao_importacao() {
    var qtde_meses_submetido = eval(strtofloat('<?=$txt_qtde_meses;?>'))
    var qtde_meses_digitado	 = eval(strtofloat(document.form.txt_qtde_meses.value))
    
    /*
    Comentado no dia 05/10/2017 a Pedido do Roberto porque nesse momento estamos trabalhando com Estoques menores ...
     
     if(qtde_meses_digitado < 1) {
        alert('QTDE DE MESES INVÁLIDA !!!\nQTDE DE MESES NÃO PODE SER MENOR QUE HUM !')
        document.form.txt_qtde_meses.focus()
        document.form.txt_qtde_meses.select()
        return false
    }*/
    
    if(qtde_meses_submetido != qtde_meses_digitado) {
        alert('CLIQUE NO BOTÃO ATUALIZAR !')
        document.form.cmd_atualizar.focus()
        return false
    }
    
    document.form.action = 'cotacao_importacao.php'
    document.form.target = 'COTACAO_IMPORTACAO'
    nova_janela('cotacao_importacao.php', 'COTACAO_IMPORTACAO', '', '', '', '', 520, 920, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function recarregar_tela() {
    var nao_mostrar_filhos  = (document.form.chkt_nao_mostrar_filhos.checked) ? 'S' : 'N'
    var pa_componente       = (document.form.chkt_pa_componente.checked) ? 'S' : 'N'
    window.location = 'relatorio_compra_producao.php<?=$parametro;?>&txt_qtde_meses='+document.form.txt_qtde_meses.value+'&chkt_nao_mostrar_filhos='+nao_mostrar_filhos+'&chkt_pa_componente='+pa_componente+'&ancora=1'
}

function iniciar() {
    document.form.txt_qtde_meses.focus()
    var ancora = '<?=$_GET['ancora'];?>'
    //Se essa tela já foi submetida pelo menos 1 vez, aí sim vai para o fim quando atualizar a tela ...
    if(ancora == 1) location.href = '#fim'
}
</Script>
</head>
<body onload='iniciar()'>
<form name='form' method='post'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            <font color='yellow' size='-1'>
                <b>Relatório de Compra Produção</b></br>
            </font>
            Data de Embarque Great: 
            <input type='text' name="txt_data_embarque" value="<?=$txt_data_embarque;?>" title="Digite a Data de Embarque" onkeyup="verifica(this, 'data', '', '', event);calcular_qtde_meses()" size="12" maxlength="10" class='caixadetexto'>
            - Fator de Correção do MMV: 
            <input type='text' name="txt_fator_correcao_mmv" value="<?=$txt_fator_correcao_mmv;?>" title="Digite o Fator de Correção do MMV" onkeyup="verifica(this, 'moeda_especial', '1', '', event);enviar_dados(event)" size="5" maxlength="7" class='caixadetexto'>
            - Qtde de Mês:
            <input type='text' name="txt_qtde_meses" value="<?=$txt_qtde_meses;?>" title="Digite o Período" maxlength="5" size="7" onkeyup="verifica(this, 'moeda_especial', '1', '', event);enviar_dados(event)" class="caixadetexto">
            <input type='button' name="cmd_atualizar" value="Atualizar" title="Atualizar" onclick='enviar_dados()' class='botao'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Ref
        </td>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            Qtde
            7ª Etapa <br/>Item / Pai
        </td>
        <td rowspan='2'>
            <font style='cursor:help' title='Operação de Custo'>
                O.C.
            </font>
        </td>
        <td rowspan='2'>
            Compra<br/> Produção
        </td>
        <td colspan='8'>
            Quantidade / Estoque
        </td>
        <td rowspan='2'>
            <font title='Média Mensal de Vendas 6 meses' style='cursor:help'>
                MMV <br/>6 meses
            </font>
        </td>
        <td rowspan='2'>
            <font title='MMV Item / Acumulado' style='cursor:help'>
                MMV <br/>Item / Acum
            </font>
        </td>
        <td rowspan='2'>
            <font title='Estoque Comprometido p/ x meses' style='cursor:help'>
                EC p/x <br/>meses
            </font>
        </td>
        <td rowspan='2'>
            <font title='Margem de Lucro Média' style='cursor:help'>
                MLM <br/>6 meses
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font style='cursor:help' title='Estoque Real'>
                Real
            </font>
        </td>
        <td>
            <font style='cursor:help' title='Estoque Disponivel'>
                Disp.
            </font>
        </td>
        <td>
            <font title='Estoque do Fornecedor' size='-2' style='cursor:help'>
                E Forn
            </font>
        </td>
        <td>
            <font title='Estoque do Porto' size='-2' style='cursor:help'>
                E Porto
            </font>
        </td>
        <td>
            <font title='Pendência' style='cursor:help'>
                Pend.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido Item / Acumulado' style='cursor:help'>
                EC <br/>Item / Acum
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido Programado &gt; que 30 dias' style='cursor:help' size='-2'>
                Prog.
            </font>
        </td>
        <td>
            Item <br/>Faltante
        </td>
    </tr>
<?
        //Variável utilizada em algumas partes p/ corrigir a variável -> $fator_corr_top_qtde_meses + abaixo ...
        $qtde_meses_p_corrigir_mmv_dos_top  = 2;
	$data_atual                         = date('Y-m-d');
	$data_atual_menos_sete              = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');
        $data_inicial_6_meses               = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
        
        //Aqui eu faço esse tratamento porque esse campo é decimal e faço isso para não dar erro nos cálculos mais abaixo ...
        $txt_qtde_meses         = str_replace(',', '.', $txt_qtde_meses);
        $txt_fator_correcao_mmv = str_replace(',', '.', $txt_fator_correcao_mmv);
	
	for($i = 0; $i < $linhas; $i++) {
/*Sempre que entrar no Loop do PA pai, eu zero essas variáveis p/ não continuar mantendo 
valores do Loop anterior ...*/
/**********************************************************************************/
/*******************************Variáveis de Cálculo*******************************/
/**********************************************************************************/
            $mmv_corrigido_pa_princ             = 0;
            $mmv_corrigido_pa_nivel1            = 0;
            $mmv_corrigido_pa_nivel2            = 0;
            $total_est_comp_nivel1              = 0;
            $total_est_comp_nivel2              = 0;
            $total_qtde_oes_pa_nivel1           = 0;
            $total_qtde_oes_pa_nivel2           = 0;
            $total_compra_producao_pa_princ     = 0;
            $total_compra_producao_pa_nivel1    = 0;
            $total_compra_producao_pa_nivel2    = 0;
            $compra_producao_pa_nivel1          = 0;
            $compra_producao_pa_nivel2          = 0;
/**********************************************************************************/
/***********************Variáveis passadas por parâmetro ...***********************/
/**********************************************************************************/
            //$total_hierarquia_urgentes        = 0;//Soma todos os Valores de todas as Etapas Pai, Filho e Neto ...
            $total_compra_prod_todos_niveis     = 0;
            $total_est_prog_todos_niveis        = 0;
            $total_baixas_todos_niveis          = 0;
            $total_mmv_todos_niveis             = 0;
            $total_mmv_6_meses_todos_niveis     = 0;
            $total_mmv_corrigido_todos_niveis   = 0;
            $total_vendas_6_meses_pa_princ      = 0;
            $total_vendas_6_meses_pa_nivel1     = 0;
            $total_vendas_6_meses_pa_nivel2     = 0;
            $total_mlm_zero_6_meses_pa_princ    = 0;
            $total_mlm_zero_6_meses_pa_nivel1   = 0;
            $total_mlm_zero_6_meses_pa_nivel2   = 0;
/**********************************************************************************/
            $id_estoque_acabado                 = $campos[$i]['id_estoque_acabado'];
            $id_produto_acabado_princ           = $campos[$i]['id_produto_acabado'];
            $referencia                         = $campos[$i]['referencia'];
            $estoque_produto                    = estoque_acabado::qtde_estoque($id_produto_acabado_princ, 0);
            $qtde_estoque                       = $estoque_produto[0];
            $compra_princ                       = estoque_acabado::compra_producao($id_produto_acabado_princ);
            $producao_princ                     = $estoque_produto[2];
            $compra_producao_pa_princ 		= $compra_princ + $producao_princ;
            $qtde_disponivel			= $estoque_produto[3];
            $qtde_separada                      = $estoque_produto[4];
            $qtde_faturada                      = $estoque_produto[6];
            $qtde_pendente                      = $estoque_produto[7];
            $est_comp_pa_princ			= $estoque_produto[8];
            $qtde_programada_princ              = estoque_acabado::qtde_programada($id_produto_acabado_princ);
            if($producao_princ > 0) {
                if($estoque_produto[11] > 0) $qtde_oe_em_aberto = '<br/><font color="purple"><b>(OE='.number_format($estoque_produto[11], 0, '', '.').')</b></font>';
            }else {
                $qtde_oe_em_aberto              = '';
            }
            $qtde_pa_possui_item_faltante       = $estoque_produto[9];
            $qtde_pa_e_item_faltante		= $estoque_produto[10];
            $qtde_oe_pa_princ                   = $estoque_produto[11];
            $est_fornecedor_pa_princ            = $estoque_produto[12];
            $est_porto_pa_princ                 = $estoque_produto[13];
?>
    <tr class='linhanormal'>
        <td align="left">
            <?=$campos[$i]['referencia'];?>
        </td>
        <td colspan='2'>
            <a href="javascript:nova_janela('detalhes.php?id_produto_acabado=<?=$id_produto_acabado_princ;?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class='link'>
                <?=intermodular::pa_discriminacao($id_produto_acabado_princ);?>
            </a>
            <?
                if(!empty($campos[$i]['observacao_pa'])) echo "&nbsp;-&nbsp;<img width='23' height='18' title='".$campos[$i]['observacao_pa']."' src='../../../imagem/olho.jpg'>";
            ?>
            &nbsp;
            <a href="#" onclick="nova_janela('../relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado_princ;?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class='link'>
                <img src="../../../imagem/visualizar_detalhes.png" title="Visualizar Pedidos - Últimos 6 meses" alt="Visualizar Pedidos - Últimos 6 meses" border="0">
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class='link'>
                <img src="../../../imagem/propriedades.png" title="Visualizar Orçamentos - Últimos 6 meses" alt="Visualizar Orçamentos - Últimos 6 meses" border="0">
            </a>
            &nbsp;
            <?
                /*********************Links p/ abrir o Custo*********************/
                if($campos[$i]['operacao_custo'] == 0) {//Industrial
            ?>
            <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" style='cursor:help' class='link'>
            <?
                }else {
            ?>
            <a href="javascript:nova_janela('../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" style='cursor:help' class='link'>
            <?
                }
            ?>
                <img src = '../../../imagem/menu/alterar.png' title='Visualizar Custo' alt='Visualizar Custo' border='0'>
            </a>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['status_top'] == 1) {
                $fator_correcao_top = 1.5;
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos[$i]['status_top'] == 2) {
                $fator_correcao_top = 1.25;
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }else {
                $fator_correcao_top = 1;
            }
            $multiplicar_top_pa_princ   = $fator_correcao_top;
            $fator_corr_top_qtde_meses  = ($txt_qtde_meses >= $qtde_meses_p_corrigir_mmv_dos_top) ? $txt_qtde_meses - $qtde_meses_p_corrigir_mmv_dos_top + $qtde_meses_p_corrigir_mmv_dos_top * $fator_correcao_top : $txt_qtde_meses * $fator_correcao_top;
            
            if($campos[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[$i]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos[$i]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos[$i]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
        <?
/*Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo essa coluna com a cor 
em Vermelho a Pedido do Betão ...*/
            if(!empty($compra_princ) && $compra_princ != 0) {//Se existir Compra ...
                $font = ($compra_princ < - ($est_comp_pa_princ)) ? "<font color='red'>" : "<font color='black'>";
            }
//Aqui verifica se o PA tem relação com o PI, caso isso não acontece não apresenta o link
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado_princ' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);

//Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
            if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 1) {
        ?>
        <a href="javascript:nova_janela('../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$id_produto_acabado_princ;?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Compra Produção" class='link'>
        <?
/****************Compra****************/
                $font = ($compra_princ < - ($est_comp_pa_princ)) ? "<font color='red'>" : "<font color='#6473D4'>";
                echo $font.number_format($compra_princ, 2, ',', '.');
/****************Produção****************/
                if(!empty($producao_princ) && $producao_princ != 0) {
                    $font = ($producao_princ < - ($est_comp_pa_princ)) ? "<font color='red'>" : "<font color='#6473D4'>";
                    echo ' / '.$font.number_format($producao_princ, 2, ',', '.');
                }
        ?>
        </a>
<?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
            }else if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 0) {//Não mostra o link
/****************Compra****************/
                $font = ($compra_princ < - ($est_comp_pa_princ)) ? "<font color='red'>" : "<font color='#6473D4'>";
                echo $font.number_format($compra_princ, 2, ',', '.');
/****************Produção****************/
                if(!empty($producao_princ) && $producao_princ != 0) {
                    $font = ($producao_princ < - ($est_comp_pa_princ)) ? "<font color='red'>" : "<font color='black'>";
                    echo ' / '.$font.number_format($producao_princ, 2, ',', '.');
                }
//Aqui o PA não tem relação com o PI
            }else {
/****************Produção****************/
                $font = ($producao_princ < - ($est_comp_pa_princ)) ? "<font color='red'>" : "<font color='black'>";
                echo $font.number_format($producao_princ, 2, ',', '.');
            }
            $total_compra_prod_todos_niveis+= $compra_producao_pa_princ;
            echo $qtde_oe_em_aberto;
        ?>
        </td>
        <td align='right'>
        <?
            //Verifico se o Item possui Qtde Excedente ...
            $sql = "SELECT `qtde` 
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado_princ' 
                    AND `status` = '0' LIMIT 1 ";
            $campos_excedente = bancos::sql($sql);
            if($campos_excedente[0]['qtde'] > 0) {//Se existir Estoque Excedente, exibo um link p/ ver Detalhes
        ?>
            <a href = 'excedente/alterar.php?passo=1&id_produto_acabado=<?=$id_produto_acabado_princ;?>&pop_up=1' style='cursor:help' class='html5lightbox'>
        <?
            }
            echo segurancas::number_format($qtde_estoque, 2, '.');
            if($qtde_pa_possui_item_faltante > 0) echo '<br/><font color="red"><b>'.$qtde_pa_possui_item_faltante.' F.I</b></font>';
        ?>
            </a>
        </td>
        <td align='right'>
        <?
            if($qtde_disponivel < 0) echo "<font color='red'>";
            echo segurancas::number_format($qtde_disponivel, 2, '.');
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($est_fornecedor_pa_princ, 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($est_porto_pa_princ, 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($qtde_pendente, 2, '.');?>
        </td>
        <td align='right'>
        <?
            if($est_comp_pa_princ < 0) echo "<font color='red'>";
            echo segurancas::number_format($est_comp_pa_princ, 2, '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($qtde_programada_princ < 0) echo "<font color='red'>";
            echo segurancas::number_format($qtde_programada_princ, 2, '.');
            
            $total_est_prog_todos_niveis+= $qtde_programada_princ;
        ?>
        </td>
        <td align='center'>
        <?
            if($qtde_pa_e_item_faltante > 0) echo '<br/><font color="red"><b>'.$qtde_pa_e_item_faltante.' F.I</b></font>';
        ?>
        </td>
        <td align='right'>
        <?
            //Aqui eu pego o Total vendido do PA nos últimos 6 meses em Qtde, Valor e Total Margem de Lucro Zero ...
            $sql = "SELECT SUM(pvi.`qtde`) AS total_qtde_6_meses, 
                    SUM(IF(c.`id_pais` = '31', (pvi.`qtde` * pvi.`preco_liq_final`), (pvi.`qtde` * pvi.`preco_liq_final` * ov.`valor_dolar`))) AS total_todas_empresas, 
                    SUM(IF(c.`id_pais` = '31', (pvi.`qtde` * pvi.`preco_liq_final` / (1 + pvi.`margem_lucro` / 100)), (pvi.`qtde` * pvi.`preco_liq_final` * ov.`valor_dolar` / (1 + pvi.`margem_lucro` / 100)))) AS total_ml_zero 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `pedidos_vendas_itens` pvi on pvi.id_produto_acabado = pa.id_produto_acabado 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
                    INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                    INNER JOIN `pedidos_vendas` pv on pv.id_pedido_venda = pvi.id_pedido_venda AND pv.data_emissao >= '$data_inicial_6_meses' 
                    INNER JOIN `clientes` c on c.id_cliente = pv.id_cliente 
                    WHERE pvi.id_produto_acabado = '$id_produto_acabado_princ' ";
            $campos_pedidos = bancos::sql($sql);
            if($campos_pedidos[0]['total_todas_empresas'] > 0) {
                $total_vendas_6_meses_pa_princ+=      $campos_pedidos[0]['total_todas_empresas'];
                $total_mlm_zero_6_meses_pa_princ+=    $campos_pedidos[0]['total_ml_zero'];
                $mlm_zero_6_meses_pa_princ =          ($campos_pedidos[0]['total_todas_empresas'] / $campos_pedidos[0]['total_ml_zero'] - 1) * 100;
                echo number_format($campos_pedidos[0]['total_qtde_6_meses'] / 6, 1, ',', '.');
                $total_mmv_6_meses_todos_niveis+= ($campos_pedidos[0]['total_qtde_6_meses'] / 6);
            }else {
                $mlm_zero_6_meses_pa_princ = '';
            }
        ?>
        </td>
        <td align='right'>
        <?
//Aki eu busco a média mensal de vendas do PA ...
            $sql = "SELECT IF(`referencia` = 'ESP', 0, `mmv`) AS mmv 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado_princ' LIMIT 1 ";
            $campos_pa_princ = bancos::sql($sql);
            if($campos_pa_princ[0]['mmv'] > 0) {
                $mmv_corrigido_pa_princ = $campos_pa_princ[0]['mmv'] * $txt_fator_correcao_mmv * $fator_corr_top_qtde_meses;
                echo number_format($campos_pa_princ[0]['mmv'], 1, ',', '.');
                $total_mmv_todos_niveis+= $campos_pa_princ[0]['mmv'];
                $total_mmv_corrigido_todos_niveis+= $campos_pa_princ[0]['mmv'] * $txt_fator_correcao_mmv * $txt_qtde_meses * $multiplicar_top_pa_princ;
            }
        ?>
        </td>
        <?
            if($campos_pa_princ[0]['mmv'] > 0 && $est_comp_pa_princ >= 0) {
                $ec_p_x_meses_princ = $est_comp_pa_princ / $campos_pa_princ[0]['mmv'];
                /*Até o dia 02/02/12 nós utilizávamos essa condição abaixo como sendo 
                ec_p_x_meses > 0 e a partir dessa data ficou ec_p_x_meses >= 0, porque 
                vimos mais coerência ...*/
                if($ec_p_x_meses_princ >= 0 && $ec_p_x_meses_princ < 1) {
                    $cor_fundo = '#FFFF99';
                    //Comentado no dia 02/02/12 porque passamos basear essa lógica na variável "Urgentes"
                    //$vetor_pas_urgentes.= $id_produto_acabado_princ.'|'.$ec_p_x_meses_princ;
                    //$somar_hierarquia_urgentes = 1;
                }else {
                    $cor_fundo = '#E8E8E8';
                }
                $resultado = number_format($ec_p_x_meses_princ, 1, ',', '.');
            }else {
                $cor_fundo = '';
                $resultado = '-';
            }
        ?>
        <td bgcolor='<?=$cor_fundo;?>' align='right'>
            <?=$resultado;?>
        </td>
        <td align='right'>
            <?=number_format($mlm_zero_6_meses_pa_princ, 1, ',', '.');?>
        </td>
    </tr>
<?
/*A partir daqui, eu vejo por quais PAs da 7ª Etapa que este PA Corrente é utilizado, e a OC do custo 
do PA (pa.operacao_custo) tem de ser igual a OC do custo da 7a etapa (pac.`operacao_custo`), ou seja, 
se o PA tem OC = 'REV', ele só pode ver se tem PA na 7a etapa do Custo Industrial atrelado ao custo REV.
        
Nesse caso o "Primário é que está na 7ª Etapa" do Secundário, exemplo LE-301 está na 7ª Etapa do LE-301S,
mas o LE-301S só será filho do LE-301 se a OC do LE-301S for igual a OC de Custo da 7ª Etapa onde o LE-301 
está atrelado, esta OC do custo da 7a etapa é o campo operacao_custo da tabela pacs_vs_pas ...*/
            $sql = "SELECT DISTINCT(pa.`id_produto_acabado`), pa.`status_top`, pa.`operacao_custo`, 
                    pa.`operacao_custo_sub`, pa.`referencia`, pa.`observacao` AS observacao_pa, pp.`qtde` 
                    FROM `pacs_vs_pas` pp 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`operacao_custo` = pac.`operacao_custo` 
                    WHERE pp.`id_produto_acabado` = '$id_produto_acabado_princ' ORDER BY pp.`id_pac_pa` ";
            $campos_pas7_nivel1 = bancos::sql($sql);
            $linhas_pas7_nivel1 = count($campos_pas7_nivel1);
            for($j = 0; $j < $linhas_pas7_nivel1; $j++) {
                $id_estoque_acabado    					= $campos_pas7_nivel1[$j]['id_estoque_acabado'];
                $id_produto_acabado_nivel1                              = $campos_pas7_nivel1[$j]['id_produto_acabado'];
                $referencia 	       					= $campos_pas7_nivel1[$j]['referencia'];
                $estoque_produto                                        = estoque_acabado::qtde_estoque($id_produto_acabado_nivel1, 0);
                $qtde_estoque 						= $estoque_produto[0];
                $compra_nivel1						= estoque_acabado::compra_producao($id_produto_acabado_nivel1);
                $producao_nivel1					= $estoque_produto[2];
                $compra_producao_pa_nivel1				= $compra_nivel1 + $producao_nivel1;
                $qtde_disponivel					= $estoque_produto[3];
                $qtde_separada						= $estoque_produto[4];
                $qtde_faturada						= $estoque_produto[6];
                $qtde_pendente						= $estoque_produto[7];
                $est_comp_pa_nivel1					= $estoque_produto[8];
                $qtde_programada_nivel1                                 = estoque_acabado::qtde_programada($id_produto_acabado_nivel1);
                if($producao_nivel1 > 0) {
                    if($estoque_produto[11] > 0) $qtde_oe_em_aberto = '<br/><font color="purple"><b>(OE='.number_format($estoque_produto[11], 0, '', '.').')</b></font>';
                }else {
                    $qtde_oe_em_aberto                                  = '';
                }
                $qtde_pa_possui_item_faltante_nivel1                    = $estoque_produto[9];
                $qtde_pa_e_item_faltante_nivel1                         = $estoque_produto[10];
                $qtde_oe_pa_nivel1                                      = $estoque_produto[11];
                $est_fornecedor_pa_nivel1                               = $estoque_produto[12];
                $est_porto_pa_nivel1                                    = $estoque_produto[13];
                $status_top_pa_nivel1					= $campos_pas7_nivel1[$j]['status_top'];
                
                $qtde_7_etapa_corrigida_nivel1                          = $campos_pas7_nivel1[$j]['qtde'];
?>
    <tr class='linhanormal'>
        <td bgcolor='#C0C0C0'>
                <?=$campos_pas7_nivel1[$j]['referencia'];?>
        </td>
        <td bgcolor='#C0C0C0'>
            <a href="javascript:nova_janela('detalhes.php?id_produto_acabado=<?=$campos_pas7_nivel1[$j]['id_produto_acabado'];?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class='link'>
                <?=intermodular::pa_discriminacao($campos_pas7_nivel1[$j]['id_produto_acabado']);?>
            </a>
            <?
                if(!empty($campos_pas7_nivel1[$j]['observacao_pa'])) echo "&nbsp;-&nbsp;<img width='23' height='18' title='".$campos_pas7_nivel1[$j]['observacao_pa']."' src='../../../imagem/olho.jpg'>";
            ?>
            &nbsp;
            <a href="#" onclick="nova_janela('../relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos_pas7_nivel1[$j]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class='link'>
                <img src="../../../imagem/visualizar_detalhes.png" title="Visualizar Pedidos - Últimos 6 meses" alt="Visualizar Pedidos - Últimos 6 meses" border="0">
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos_pas7_nivel1[$j]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class='link'>
                <img src="../../../imagem/propriedades.png" title="Visualizar Orçamentos - Últimos 6 meses" alt="Visualizar Orçamentos - Últimos 6 meses" border="0">
            </a>
            &nbsp;
            <?
                /*********************Links p/ abrir o Custo*********************/
                if($campos_pas7_nivel1[$j]['operacao_custo'] == 0) {//Industrial
            ?>
            <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos_pas7_nivel1[$j]['id_produto_acabado'];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" style='cursor:help' class='link'>
            <?
                }else {
            ?>
            <a href="javascript:nova_janela('../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos_pas7_nivel1[$j]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" style='cursor:help' class='link'>
            <?
                }
            ?>
                <img src = '../../../imagem/menu/alterar.png' title='Visualizar Custo' alt='Visualizar Custo' border='0'>
            </a>
        </td>
        <td bgcolor='#C0C0C0' align='center'>
                <?=number_format($campos_pas7_nivel1[$j]['qtde'], 2, ',', '.');?>
        </td>
        <td bgcolor='#C0C0C0' align='center'>
        <?
                if($campos_pas7_nivel1[$j]['status_top'] == 1) {
                    $fator_correcao_top = 1.5;
                    echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
                }else if($campos_pas7_nivel1[$j]['status_top'] == 2) {
                    $fator_correcao_top = 1.25;
                    echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
                }else {
                    $fator_correcao_top = 1;
                }
                $multiplicar_top_pa_nivel1  = $fator_correcao_top;
                $fator_corr_top_qtde_meses  = ($txt_qtde_meses >= $qtde_meses_p_corrigir_mmv_dos_top) ? $txt_qtde_meses - $qtde_meses_p_corrigir_mmv_dos_top + $qtde_meses_p_corrigir_mmv_dos_top * $fator_correcao_top : $txt_qtde_meses * $fator_correcao_top;

                if($campos_pas7_nivel1[$j]['operacao_custo'] == 0) {
                    echo 'I';
    //Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                    if($campos_pas7_nivel1[$j]['operacao_custo_sub'] == 0) {
                        echo '-I';
                    }else if($campos_pas7_nivel1[$j]['operacao_custo_sub'] == 1) {
                        echo '-R';
                    }else {
                        echo '-';
                    }
                }else if($campos_pas7_nivel1[$j]['operacao_custo'] == 1) {
                    echo 'R';
                }else {
                    echo '-';
                }
        ?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
        <?
/*Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo essa coluna com a cor 
em Vermelho a Pedido do Betão ...*/
                if(!empty($compra_nivel1) && $compra_nivel1 != 0) {//Se existir Compra ...
                    $font = ($compra_nivel1 < - ($est_comp_pa_nivel1)) ? "<font color='red'>" : "<font color='black'>";
                }
//Aqui verifica se o PA tem relação com o PI, caso isso não acontece não apresenta o link
                $sql = "SELECT `id_produto_insumo` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado_nivel1' 
                        AND `id_produto_insumo` > '0' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos_pipa = bancos::sql($sql);
    //Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
                if(count($campos_pipa) == 1 && $campos_pas7_nivel1[$j]['operacao_custo'] == 1) {
        ?>
        <a href="javascript:nova_janela('../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$id_produto_acabado_nivel1;?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Compra Produção" class='link'>
        <?
/****************Compra****************/
                    $font = ($compra_nivel1 < - ($est_comp_pa_nivel1)) ? "<font color='red'>" : "<font color='#6473D4'>";
                    echo $font.number_format($compra_nivel1, 2, ',', '.');
/****************Produção****************/
                    if(!empty($producao_nivel1) && $producao_nivel1 != 0) {
                        $font = ($producao_nivel1 < - ($est_comp_pa_nivel1)) ? "<font color='red'>" : "<font color='#6473D4'>";
                        echo ' / '.$font.number_format($producao_nivel1, 2, ',', '.');
                    }
        ?>
        </a>
<?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
                }else if(count($campos_pipa) == 1 && $campos_pas7_nivel1[$j]['operacao_custo'] == 0) {//Não mostra o link
/****************Compra****************/
                    $font = ($compra_nivel1 < - ($est_comp_pa_nivel1)) ? "<font color='red'>" : "<font color='#6473D4'>";
                    echo $font.number_format($compra_nivel1, 2, ',', '.');
/****************Produção****************/
                    if(!empty($producao_nivel1) && $producao_nivel1!=0) {
                        $font = ($producao_nivel1 < - ($est_comp_pa_nivel1)) ? "<font color='red'>" : "<font color='black'>";
                        echo ' / '.$font.number_format($producao_nivel1, 2, ',', '.');
                    }
//Aqui o PA não tem relação com o PI
                }else {
/****************Produção****************/
                    $font = ($producao_nivel1 < - ($est_comp_pa_nivel1)) ? "<font color='red'>" : "<font color='black'>";
                    echo $font.number_format($producao_nivel1, 2, ',', '.');
                }
//Se o Filhos do PA for Diferente de Componente e Diferente de FIX, pode ser o Compra Produção ...
                if($campos[$i]['id_familia'] <> 23 && !is_numeric(strpos(strtoupper($txt_referencia), 'FIX'))) {
                    $total_compra_prod_todos_niveis+= $compra_producao_pa_nivel1 * $qtde_7_etapa_corrigida_nivel1;
                }
                echo $qtde_oe_em_aberto;
//Aqui é vantajoso criar uma Variável de Baixa p/ não chamar 2 vezes a mesma função ...
                $baixa = estoque_acabado::baixas_pas_para_ops($id_produto_acabado_princ, $id_produto_acabado_nivel1);
                echo '<br/><font color="red"><b>Baixa(s) = '.$baixa.'</b></font>';
                //Aqui eu multiplico pela Qtde da 7ª Etapa para saber a Qtde de Componentes q preciso p/ produzir essas OPs ...
                $total_baixas_todos_niveis+= $baixa * $campos_pas7_nivel1[$j]['qtde'];
        ?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
        <?
                //Verifico se o Item possui Qtde Excedente ...
                $sql = "SELECT `qtde` 
                        FROM `estoques_excedentes` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado_nivel1' 
                        AND `status` = '0' LIMIT 1 ";
                $campos_excedente = bancos::sql($sql);
                if($campos_excedente[0]['qtde'] > 0) {//Se existir Estoque Excedente, exibo um link p/ ver Detalhes
        ?>
                <a href = 'excedente/alterar.php?passo=1&id_produto_acabado=<?=$id_produto_acabado_nivel1;?>&pop_up=1' title="Detalhes de Estoque Excedente" style='cursor:help' class='html5lightbox'>
        <?
                }
                echo segurancas::number_format($qtde_estoque, 2, '.');
                if($qtde_pa_possui_item_faltante_nivel1 > 0) echo '<br/><font color="red"><b>'.$qtde_pa_possui_item_faltante_nivel1.' F.I</b></font>';
        ?>
                </a>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
        <?
                if($qtde_disponivel < 0) echo "<font color='red'>";
                echo segurancas::number_format($qtde_disponivel, 2, '.');
        ?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
                <?=segurancas::number_format($est_fornecedor_pa_nivel1, 2, '.');?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
                <?=segurancas::number_format($est_porto_pa_nivel1, 2, '.');?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
                <?=segurancas::number_format($qtde_pendente, 2, '.');?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
        <?
                if($est_comp_pa_nivel1 != 0) {
                    if($est_comp_pa_nivel1 < 0) echo "<font color='red'>";
                    echo segurancas::number_format($est_comp_pa_nivel1, 1, '.').' / ';
                    
                    $est_comp_pa_corrigida_nivel1 = $est_comp_pa_nivel1 * $qtde_7_etapa_corrigida_nivel1;
                    echo number_format($est_comp_pa_corrigida_nivel1, 1, ',', '.');

                    $total_est_comp_nivel1+= $est_comp_pa_corrigida_nivel1;
                }
        ?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
        <?
                if($qtde_programada_nivel1 < 0) echo "<font color='red'>";
                echo segurancas::number_format($qtde_programada_nivel1, 2, '.').' / ';
                
                $qtde_programada_corrigida_nivel1 = $qtde_programada_nivel1 * $qtde_7_etapa_corrigida_nivel1;
                echo number_format($qtde_programada_corrigida_nivel1, 1, ',', '.');
                
                $total_est_prog_todos_niveis+= $qtde_programada_corrigida_nivel1;
        ?>
        </td>
        <td bgcolor='#C0C0C0' align='center'>
        <?
                if($qtde_pa_e_item_faltante_nivel1 > 0) echo '<br/><font color="red"><b>'.$qtde_pa_e_item_faltante_nivel1.' F.I</b></font>';
        ?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
        <?
                //Aqui eu pego o Total vendido do PA nos últimos 6 meses em Qtde, Valor e Total Margem de Lucro Zero ...
                $sql = "SELECT SUM(pvi.qtde) AS total_qtde_6_meses, SUM(IF(c.id_pais = 31, (pvi.qtde * pvi.preco_liq_final), (pvi.qtde * pvi.preco_liq_final * ov.valor_dolar))) AS total_todas_empresas, SUM(IF(c.id_pais = 31, (pvi.qtde * pvi.preco_liq_final / (1 + pvi.margem_lucro / 100)), (pvi.qtde * pvi.preco_liq_final * ov.valor_dolar / (1 + pvi.margem_lucro / 100)))) AS total_ml_zero 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `pedidos_vendas_itens` pvi on pvi.id_produto_acabado = pa.id_produto_acabado 
                        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                        INNER JOIN `pedidos_vendas` pv on pv.id_pedido_venda = pvi.id_pedido_venda AND pv.data_emissao >= '$data_inicial_6_meses' 
                        INNER JOIN `clientes` c on c.id_cliente = pv.id_cliente 
                        WHERE pvi.id_produto_acabado = '$id_produto_acabado_nivel1' ";
                $campos_pedidos = bancos::sql($sql);
                if($campos_pedidos[0]['total_todas_empresas'] > 0) {
                    $total_vendas_6_meses_pa_nivel1+=     $campos_pedidos[0]['total_todas_empresas'];
                    $total_mlm_zero_6_meses_pa_nivel1+=   $campos_pedidos[0]['total_ml_zero'];
                    $mlm_zero_6_meses_pa_nivel1 =         ($campos_pedidos[0]['total_todas_empresas'] / $campos_pedidos[0]['total_ml_zero'] - 1) * 100;
                    echo number_format($campos_pedidos[0]['total_qtde_6_meses'] / 6, 1, ',', '.');
                    //Se o Filhos do PA for Diferente de Componente e Diferente de FIX, pode ser o Compra Produção ...
                    if($campos[$i]['id_familia'] <> 23 && !is_numeric(strpos(strtoupper($txt_referencia), 'FIX'))) {
                        $total_mmv_6_meses_todos_niveis+= ($campos_pedidos[0]['total_qtde_6_meses'] / 6);
                    }
                }else {
                    $mlm_zero_6_meses_pa_nivel1 = '';
                }
        ?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
        <?
//Aki eu busco a média mensal de vendas do PA
                $sql = "SELECT IF(`referencia` = 'ESP', 0, `mmv`) AS mmv 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado_nivel1' LIMIT 1 ";
                $campos_pa_nivel1 = bancos::sql($sql);
                if($campos_pa_nivel1[0]['mmv'] > 0) {
                    echo number_format($campos_pa_nivel1[0]['mmv'], 1, ',', '.').' / ';
                    $mmv_corrigido_linha_pa_nivel1 = $campos_pa_nivel1[0]['mmv'] * $txt_fator_correcao_mmv * $fator_corr_top_qtde_meses * $qtde_7_etapa_corrigida_nivel1;
                    echo number_format($mmv_corrigido_linha_pa_nivel1, 1, ',', '.');
                    
                    $mmv_corrigido_pa_nivel1+= $mmv_corrigido_linha_pa_nivel1;

                    //Se o Filhos do PA for Diferente de Componente e Diferente de FIX, pode ser o Compra Produção ...
                    if($campos[$i]['id_familia'] <> 23 && !is_numeric(strpos(strtoupper($txt_referencia), 'FIX'))) {
                        $total_mmv_todos_niveis+= $campos_pa_nivel1[0]['mmv'];
                        $total_mmv_corrigido_todos_niveis+= $campos_pa_nivel1[0]['mmv'] * $txt_fator_correcao_mmv * $txt_qtde_meses * $multiplicar_top_pa_nivel1;
                    }
                }
        ?>
        </td>
        <?
                if($campos_pa_nivel1[0]['mmv'] > 0 && $est_comp_pa_nivel1 >= 0) {
                    $ec_p_x_meses = $est_comp_pa_nivel1 / $campos_pa_nivel1[0]['mmv'];
                    $cor_fundo = ($ec_p_x_meses > 0 && $ec_p_x_meses < 1) ? '#FFFF99' : '#C0C0C0';
                    $resultado = number_format($ec_p_x_meses, 1, ',', '.');
                }else {
                    $cor_fundo = '#C0C0C0';
                    $resultado = '-';
                }
        ?>
        <td bgcolor='<?=$cor_fundo;?>' align='right'>
                <?=$resultado;?>
        </td>
        <td bgcolor='#C0C0C0' align='right'>
                <?=number_format($mlm_zero_6_meses_pa_nivel1, 1, ',', '.');?>
        </td>
    </tr>
<?
/*A partir daqui, eu vejo por quais PAs da 7ª Etapa que este PA Corrente é utilizado, e a OC do custo 
do PA (pa.operacao_custo) tem de ser igual a OC do custo da 7a etapa (pac.`operacao_custo`), ou seja, 
se o PA tem OC = 'REV', ele só pode ver se tem PA na 7a etapa do Custo Industrial atrelado ao custo REV.
        
Nesse caso o "Primário é que está na 7ª Etapa" do Secundário, exemplo LE-301 está na 7ª Etapa do LE-301S,
mas o LE-301S só será filho do LE-301 se a OC do LE-301S for igual a OC de Custo da 7ª Etapa onde o LE-301 
está atrelado, esta OC do custo da 7a etapa é o campo operacao_custo da tabela pacs_vs_pas ...*/

//Vejo se existe um sub-nível do 1º nível ...
                $sql = "SELECT DISTINCT(pa.`id_produto_acabado`), pa.`status_top`, pa.`operacao_custo`, 
                        pa.`operacao_custo_sub`, pa.`referencia`, pa.`observacao` AS observacao_pa, pp.`qtde` 
                        FROM `pacs_vs_pas` pp 
                        INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`operacao_custo` = pac.`operacao_custo` 
                        WHERE pp.`id_produto_acabado` = '$id_produto_acabado_nivel1' ORDER BY pp.`id_pac_pa` ";
                $campos_pas7_nivel2 = bancos::sql($sql);
                $linhas_pas7_nivel2 = count($campos_pas7_nivel2);
                for($k = 0; $k < $linhas_pas7_nivel2; $k++) {
                    $id_estoque_acabado         = $campos_pas7_nivel2[$k]['id_estoque_acabado'];
                    $id_produto_acabado_nivel2  = $campos_pas7_nivel2[$k]['id_produto_acabado'];
                    $referencia                 = $campos_pas7_nivel2[$k]['referencia'];
                    $estoque_produto            = estoque_acabado::qtde_estoque($id_produto_acabado_nivel2, 0);
                    $qtde_estoque               = $estoque_produto[0];
                    $compra_nivel2              = estoque_acabado::compra_producao($id_produto_acabado_nivel2);
                    $producao_nivel2            = $estoque_produto[2];
                    $compra_producao_pa_nivel2  = $compra_nivel2 + $producao_nivel2;
                    $qtde_disponivel            = $estoque_produto[3];
                    $qtde_separada              = $estoque_produto[4];
                    $qtde_faturada              = $estoque_produto[6];
                    $qtde_pendente              = $estoque_produto[7];
                    $est_comp_pa_nivel2         = $estoque_produto[8];
                    $qtde_programada_nivel2     = estoque_acabado::qtde_programada($id_produto_acabado_nivel2);
                    if($producao_nivel2 > 0) {
                        if($estoque_produto[11] > 0) $qtde_oe_em_aberto = '<br/><font color="purple"><b>(OE='.number_format($estoque_produto[11], 0, '', '.').')</b></font>';
                    }else {
                        $qtde_oe_em_aberto      = '';
                    }
                    $qtde_pa_possui_item_faltante_nivel2    = $estoque_produto[9];
                    $qtde_pa_e_item_faltante_nivel2         = $estoque_produto[10];
                    $qtde_oe_pa_nivel2                      = $estoque_produto[11];
                    $est_fornecedor_pa_nivel2               = $estoque_produto[12];
                    $est_porto_pa_nivel2                    = $estoque_produto[13];
                    $status_top_pa_nivel2                   = $campos_pas7_nivel2[$k]['status_top'];
                    
                    $qtde_7_etapa_corrigida_nivel2          = $campos_pas7_nivel2[$k]['qtde'] * $campos_pas7_nivel1[$j]['qtde'];
?>
    <tr class='linhanormal'>
        <td bgcolor='#A0A0A0'>
                    <?=$campos_pas7_nivel2[$k]['referencia'];?>
        </td>
        <td bgcolor='#A0A0A0'>
            <a href="javascript:nova_janela('detalhes.php?id_produto_acabado=<?=$campos_pas7_nivel2[$k]['id_produto_acabado'];?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class='link'>
                    <?=intermodular::pa_discriminacao($campos_pas7_nivel2[$k]['id_produto_acabado']);?>
            </a>
            <?
                    if(!empty($campos_pas7_nivel2[$k]['observacao_pa'])) echo "&nbsp;-&nbsp;<img width='23' height='18' title='".$campos_pas7_nivel2[$k]['observacao_pa']."' src='../../../imagem/olho.jpg'>";
            ?>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos_pas7_nivel2[$k]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class='link'>
                <img src="../../../imagem/visualizar_detalhes.png" title="Visualizar Pedidos - Últimos 6 meses" alt="Visualizar Pedidos - Últimos 6 meses" border="0">
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos_pas7_nivel2[$k]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class='link'>
                <img src="../../../imagem/propriedades.png" title="Visualizar Orçamentos - Últimos 6 meses" alt="Visualizar Orçamentos - Últimos 6 meses" border="0">
            </a>
            &nbsp;
            <?
                    /*********************Links p/ abrir o Custo*********************/
                    if($campos_pas7_nivel2[$k]['operacao_custo'] == 0) {//Industrial
            ?>
            <a href="javascript:nova_janela('../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos_pas7_nivel2[$k]['id_produto_acabado'];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" style='cursor:help' class='link'>
            <?
                    }else {
            ?>
            <a href="javascript:nova_janela('../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos_pas7_nivel2[$k]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" style='cursor:help' class='link'>
            <?
                    }
            ?>
                <img src = '../../../imagem/menu/alterar.png' title='Visualizar Custo' alt='Visualizar Custo' border='0'>
            </a>
        </td>
        <td bgcolor='#A0A0A0' align='center'>
                    <?=number_format($campos_pas7_nivel2[$k]['qtde'], 2, ',', '.');?>
        </td>
        <td bgcolor='#A0A0A0' align='center'>
        <?			
                    if($campos_pas7_nivel2[$k]['status_top'] == 1) {
                        $fator_correcao_top = 1.5;
                        echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
                    }else if($campos_pas7_nivel2[$k]['status_top'] == 2) {
                        $fator_correcao_top = 1.25;
                        echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
                    }else {
                        $fator_correcao_top = 1;
                    }
                    $multiplicar_top_pa_nivel2  = $fator_correcao_top;
                    $fator_corr_top_qtde_meses  = ($txt_qtde_meses >= $qtde_meses_p_corrigir_mmv_dos_top) ? $txt_qtde_meses - $qtde_meses_p_corrigir_mmv_dos_top + $qtde_meses_p_corrigir_mmv_dos_top * $fator_correcao_top : $txt_qtde_meses * $fator_correcao_top;

                    if($campos_pas7_nivel2[$k]['operacao_custo'] == 0) {
                        echo 'I';
    //Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                        if($campos_pas7_nivel2[$k]['operacao_custo_sub'] == 0) {
                            echo '-I';
                        }else if($campos_pas7_nivel2[$k]['operacao_custo_sub'] == 1) {
                            echo '-R';
                        }else {
                            echo '-';
                        }
                    }else if($campos_pas7_nivel2[$k]['operacao_custo'] == 1) {
                        echo 'R';
                    }else {
                        echo '-';
                    }
        ?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
        <?
/*Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo essa coluna com a cor 
em Vermelho a Pedido do Betão ...*/
                    if(!empty($compra_nivel2) && $compra_nivel2 != 0) {//Se existir Compra ...
                        $font = ($compra_nivel2 < - ($est_comp_pa_nivel2)) ? "<font color='red'>" : "<font color='black'>";
                    }
//Aqui verifica se o PA tem relação com o PI, caso isso não acontece não apresenta o link
                    $sql = "SELECT `id_produto_insumo` 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado_nivel2' 
                            AND `id_produto_insumo` > '0' 
                            AND `ativo` = '1' LIMIT 1 ";
                    $campos_pipa = bancos::sql($sql);
    //Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
                    if(count($campos_pipa) == 1 && $campos_pas7_nivel2[$k]['operacao_custo'] == 1) {
        ?>
                <a href="javascript:nova_janela('../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$id_produto_acabado_nivel2;?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Compra Produção" class='link'>
                <?
/****************Compra****************/
                        $font = ($compra_nivel2 < - ($est_comp_pa_nivel2)) ? "<font color='red'>" : "<font color='#6473D4'>"; 
                        echo $font.number_format($compra_nivel2, 2, ',', '.');
        /****************Produção****************/
                        if(!empty($producao_nivel2) && $producao_nivel2 != 0) {
                            $font = ($producao_nivel2 < - ($est_comp_pa_nivel2)) ? "<font color='red'>" : "<font color='#6473D4'>";
                            echo ' / '.$font.number_format($producao_nivel2, 2, ',', '.');
                        }
                ?>
            </a>
<?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
                    }else if(count($campos_pipa) == 1 && $campos_pas7_nivel2[$k]['operacao_custo'] == 0) {//Não mostra o link
/****************Compra****************/
                        $font = ($compra_nivel2 < - ($est_comp_pa_nivel2)) ? "<font color='red'>" : "<font color='#6473D4'>";
                        echo $font.number_format($compra_nivel2, 2, ',', '.');
    /****************Produção****************/
                        if(!empty($producao_nivel2) && $producao_nivel2 != 0) {
                            $font = ($producao_nivel2 < - ($est_comp_pa_nivel2)) ? "<font color='red'>" : "<font color='black'>";
                            echo ' / '.$font.number_format($producao_nivel2, 2, ',', '.');
                        }
//Aqui o PA não tem relação com o PI
                    }else {
/****************Produção****************/
                        $font = ($producao_nivel2 < - ($est_comp_pa_nivel2)) ? "<font color='red'>" : "<font color='black'>";
                        echo $font.number_format($producao_nivel2, 2, ',', '.');
                    }
                    //Se o Filhos do PA for Diferente de Componente e Diferente de FIX, pode ser o Compra Produção ...
                    if($campos[$i]['id_familia'] <> 23 && !is_numeric(strpos(strtoupper($txt_referencia), 'FIX'))) {
                        $total_compra_prod_todos_niveis+= $compra_producao_pa_nivel2 * $qtde_7_etapa_corrigida_nivel2;
                    }
                    echo $qtde_oe_em_aberto;
                    //Aqui é vantajoso criar uma Variável de Baixa p/ não chamar 2 vezes a mesma função ...
                    $baixa = estoque_acabado::baixas_pas_para_ops($id_produto_acabado_princ, $id_produto_acabado_nivel2);
                    echo '<br/><font color="red"><b>Baixa(s) = '.$baixa.'</b></font>';
                    //Aqui eu multiplico pela Qtde da 7ª Etapa para saber a Qtde de Componentes q preciso p/ produzir essas OPs ...
                    $total_baixas_todos_niveis+= $baixa * $campos_pas7_nivel2[$k]['qtde'];
        ?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
        <?
                    //Verifico se o Item possui Qtde Excedente ...
                    $sql = "SELECT `qtde` 
                            FROM `estoques_excedentes` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado_nivel2' 
                            AND `status` = '0' LIMIT 1 ";
                    $campos_excedente = bancos::sql($sql);
                    if($campos_excedente[0]['qtde'] > 0) {//Se existir Estoque Excedente, exibo um link p/ ver Detalhes
        ?>
            <a href = 'excedente/alterar.php?passo=1&id_produto_acabado=<?=$id_produto_acabado_nivel2;?>&pop_up=1' style='cursor:help' class='html5lightbox'>
        <?
                    }
                    echo segurancas::number_format($qtde_estoque, 2, '.');
                    if($qtde_pa_possui_item_faltante_nivel2 > 0) echo '<br/><font color="red"><b>'.$qtde_pa_possui_item_faltante_nivel2.' F.I</b></font>';
        ?>
            </a>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
        <?
                    if($qtde_disponivel < 0) echo "<font color='red'>";
                    echo segurancas::number_format($qtde_disponivel, 2, '.');
        ?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
                    <?=segurancas::number_format($est_fornecedor_pa_nivel2, 2, '.');?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
                    <?=segurancas::number_format($est_porto_pa_nivel2, 2, '.');?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
                    <?=segurancas::number_format($qtde_pendente, 2, '.');?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
        <?
                    $total_est_comp_linha_pa_nivel2 = 0;
                    if($est_comp_pa_nivel2 != 0) {
                        if($est_comp_pa_nivel2 < 0) echo "<font color='red'>";
                        echo segurancas::number_format($est_comp_pa_nivel2, 1, '.').' / ';
                        $total_est_comp_linha_pa_nivel2 = $est_comp_pa_nivel2 * $qtde_7_etapa_corrigida_nivel2;
                        echo number_format($total_est_comp_linha_pa_nivel2, 1, ',', '.');

                        $total_est_comp_nivel2+= $total_est_comp_linha_pa_nivel2;
                    }
        ?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
        <?
                    if($qtde_programada_nivel2 < 0) echo "<font color='red'>";
                    echo segurancas::number_format($qtde_programada_nivel2, 2, '.').' / ';

                    $qtde_programada_corrigida_nivel2 = $qtde_programada_nivel2 * $qtde_7_etapa_corrigida_nivel2;
                    echo number_format($qtde_programada_corrigida_nivel2, 1, ',', '.');
                    
                    $total_est_prog_todos_niveis+= $qtde_programada_corrigida_nivel2;
        ?>
        </td>
        <td bgcolor='#A0A0A0' align='center'>
        <?
                    if($qtde_pa_e_item_faltante_nivel2 > 0) echo '<br/><font color="red"><b>'.$qtde_pa_e_item_faltante_nivel2.' F.I</b></font>';
        ?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
        <?
                    //Aqui eu pego o Total vendido do PA nos últimos 6 meses em Qtde, Valor e Total Margem de Lucro Zero ...
                    $sql = "SELECT SUM(pvi.`qtde`) AS total_qtde_6_meses, SUM(IF(c.`id_pais` = '31', (pvi.`qtde` * pvi.`preco_liq_final`), (pvi.`qtde` * pvi.`preco_liq_final` * ov.`valor_dolar`))) AS total_todas_empresas, SUM(IF(c.`id_pais` = '31', (pvi.`qtde` * pvi.`preco_liq_final` / (1 + pvi.`margem_lucro` / 100)), (pvi.`qtde` * pvi.`preco_liq_final` * ov.`valor_dolar` / (1 + pvi.`margem_lucro` / 100)))) AS total_ml_zero 
                            FROM `produtos_acabados` pa 
                            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_produto_acabado` = pa.`id_produto_acabado` 
                            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`data_emissao` >= '$data_inicial_6_meses' 
                            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                            WHERE pvi.`id_produto_acabado` = '$id_produto_acabado_nivel2' ";
                    $campos_pedidos = bancos::sql($sql);
                    if($campos_pedidos[0]['total_todas_empresas'] > 0) {
                        $total_vendas_6_meses_pa_nivel2+=     $campos_pedidos[0]['total_todas_empresas'];
                        $total_mlm_zero_6_meses_pa_nivel2+=   $campos_pedidos[0]['total_ml_zero'];
                        $mlm_zero_6_meses_pa_nivel2 =         ($campos_pedidos[0]['total_todas_empresas'] / $campos_pedidos[0]['total_ml_zero'] - 1) * 100;
                        echo number_format($campos_pedidos[0]['total_qtde_6_meses'] / 6, 1, ',', '.');
                        //Se o Filhos do PA for Diferente de Componente e Diferente de FIX, pode ser o Compra Produção ...
                        if($campos[$i]['id_familia'] <> 23 && !is_numeric(strpos(strtoupper($txt_referencia), 'FIX'))) {
                            $total_mmv_6_meses_todos_niveis+= ($campos_pedidos[0]['total_qtde_6_meses'] / 6);
                        }
                    }else {
                        $mlm_zero_6_meses_pa_nivel2 = '';
                    }
        ?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
        <?
//Aki eu busco a média mensal de vendas do PA
                    $sql = "SELECT IF(`referencia` = 'ESP', 0, `mmv`) AS mmv 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado_nivel2' LIMIT 1 ";
                    $campos_pa_nivel2 = bancos::sql($sql);
                    if($campos_pa_nivel2[0]['mmv'] > 0) {
                        echo number_format($campos_pa_nivel2[0]['mmv'], 1, ',', '.').' / ';
                        $mmv_corrigido_linha_pa_nivel2 = $campos_pa_nivel2[0]['mmv'] * $txt_fator_correcao_mmv * $fator_corr_top_qtde_meses * $qtde_7_etapa_corrigida_nivel2;
                        echo number_format($mmv_corrigido_linha_pa_nivel2, 1, ',', '.');
                        
                        $mmv_corrigido_pa_nivel2+= $mmv_corrigido_linha_pa_nivel2;
                        
                        //Se o Filhos do PA for Diferente de Componente e Diferente de FIX, pode ser o Compra Produção ...
                        if($campos[$i]['id_familia'] <> 23 && !is_numeric(strpos(strtoupper($txt_referencia), 'FIX'))) {
                            $total_mmv_todos_niveis+= $campos_pa_nivel2[0]['mmv'];
                            $total_mmv_corrigido_todos_niveis+= $campos_pa_nivel2[0]['mmv'] * $txt_fator_correcao_mmv * $txt_qtde_meses * $multiplicar_top_pa_nivel2;
                        }
                    }
        ?>
        </td>
        <?
                    if($campos_pa_nivel2[0]['mmv'] > 0 && $est_comp_pa_nivel2 >= 0) {
                        $ec_p_x_meses   = $est_comp_pa_nivel2 / $campos_pa_nivel2[0]['mmv'];
                        $cor_fundo      = ($ec_p_x_meses > 0 && $ec_p_x_meses < 1) ? '#FFFF99' : '#A0A0A0';
                        $resultado      = number_format($ec_p_x_meses, 1, ',', '.');
                    }else {
                        $cor_fundo      = '#A0A0A0';
                        $resultado      = '-';
                    }
        ?>
        <td bgcolor='<?=$cor_fundo;?>' align='right'>
                    <?=$resultado;?>
        </td>
        <td bgcolor='#A0A0A0' align='right'>
                    <?=number_format($mlm_zero_6_meses_pa_nivel2, 1, ',', '.');?>
        </td>
    </tr>
<?
                    $total_compra_producao_pa_nivel2+= $compra_producao_pa_nivel2 * $qtde_7_etapa_corrigida_nivel2;
                    $total_qtde_oes_pa_nivel2+= $qtde_oe_pa_nivel2 * $qtde_7_etapa_corrigida_nivel2;
                }//Fim do Loop dos Netos ...
                $total_compra_producao_pa_nivel1+= $compra_producao_pa_nivel1 * $qtde_7_etapa_corrigida_nivel1;
                $total_qtde_oes_pa_nivel1+= $qtde_oe_pa_nivel1 * $qtde_7_etapa_corrigida_nivel1;
            }//Fim do Loop dos Filhos ...
            $total_compra_producao_pa_princ+= $compra_producao_pa_princ;

            //Tratamento para não dar erro de Divisão por Zero ...
            if($total_mlm_zero_6_meses_pa_princ + $total_mlm_zero_6_meses_pa_nivel1 + $total_mlm_zero_6_meses_pa_nivel2 == 0) {
                $total_mlm_todos_niveis = 0;
            }else {
                $total_mlm_todos_niveis = (($total_vendas_6_meses_pa_princ + $total_vendas_6_meses_pa_nivel1 + $total_vendas_6_meses_pa_nivel2) / ($total_mlm_zero_6_meses_pa_princ + $total_mlm_zero_6_meses_pa_nivel1 + $total_mlm_zero_6_meses_pa_nivel2) - 1) * 100;
            }
            $total_mlm_todos_niveis = round($total_mlm_todos_niveis, 1);
?>
    <tr class='iframe'>
        <td colspan='4'>
            <font size='2'><b>
            <?
                $vetor_produtos_diferenciados = array(176, 194, 368, 369, 17571);
            
                /*Se o PA for um Componente, for um FIX, 176 (RR-922), 194 (RR-925), 368 (H-406) 
                369 (H-407), 17571 (H-408) ou o usuário marcou o checkbox de Seguir o caminho como se fosse PA Componente ...*/
                if($campos[$i]['id_familia'] == 23 || is_numeric(strpos(strtoupper($txt_referencia), 'FIX')) || in_array($campos[$i]['id_produto_acabado'], $vetor_produtos_diferenciados) || $chkt_pa_componente == 'S') {
                    $total_mmv_corrigido            = $mmv_corrigido_pa_princ;
                    $total_qtde_oes_todos_niveis    = $qtde_oe_pa_princ;
                    $total_est_comp                 = $est_comp_pa_princ;
                    $a_produzir                     = $total_compra_producao_pa_nivel1 - $est_comp_pa_princ - $compra_producao_pa_princ + $mmv_corrigido_pa_princ - $total_baixas_todos_niveis;
                }else {//Se não for, então ...
                    //Essa fórmula foi mudada no dia 05/09/2016 às 16:45 ...
                    //$a_produzir             = $mmv_corrigido_pa_princ - $compra_producao_pa_princ - $est_comp_pa_princ - $qtde_oe_pa_princ + $qtde_pa_e_item_faltante + ($mmv_corrigido_pa_nivel1 + $mmv_corrigido_pa_nivel2) - ($total_compra_producao_pa_nivel1 + $total_compra_producao_pa_nivel2) - ($total_est_comp_nivel1 + $total_est_comp_nivel2) - ($total_qtde_oes_pa_nivel1 + $total_qtde_oes_pa_nivel2);
                    
                    $total_mmv_corrigido            = $mmv_corrigido_pa_princ + $mmv_corrigido_pa_nivel1 + $mmv_corrigido_pa_nivel2;
                    $total_qtde_oes_todos_niveis    = $qtde_oe_pa_princ + $total_qtde_oes_pa_nivel1 + $total_qtde_oes_pa_nivel2;
                    $total_est_comp                 = $est_comp_pa_princ + $total_est_comp_nivel1 + $total_est_comp_nivel2;
                    
                    //Quando o Macho tem mais de uma peça por Jogo, a Produção é feita pelos Blanks ...
                    if($campos[$i]['id_familia'] == 9 && $campos[$i]['pecas_por_jogo'] > 1) {
                        $a_produzir = 0;
                    }else {
                        /*Roberto tirou essa a variável $total_qtde_oes_todos_niveis dessa fórmula no dia 27/10/2016 porque 
                        essa já entra dentro do Compra Produção ...
                        $a_produzir             = $total_mmv_corrigido - ($total_compra_producao_pa_princ + $total_compra_producao_pa_nivel1 + $total_compra_producao_pa_nivel2) - $total_est_comp - $total_qtde_oes_todos_niveis + $qtde_pa_e_item_faltante;*/
                        $a_produzir             = $total_mmv_corrigido - ($total_compra_producao_pa_princ + $total_compra_producao_pa_nivel1 + $total_compra_producao_pa_nivel2) - $total_est_comp + $qtde_pa_e_item_faltante;
                    }
                }
                $urgencia = $total_mmv_corrigido - $total_est_comp - $total_qtde_oes_todos_niveis;
                if($txt_qtde_meses <= 1) $urgencia-= $total_est_prog_todos_niveis;

                $font = ($a_produzir >= 0) ? "<font color='red'>" : "<font color='darkblue'>";
                echo $font.'À Produzir => '.number_format($a_produzir, 0, ',', '.').'</font>';
                echo '&nbsp;&nbsp;&nbsp;<font color="black"><b>*</b></font>&nbsp;&nbsp;&nbsp;';

                $font = ($urgencia > 0) ? "<font color='red'>" : "<font color='darkblue'>";
                echo $font.'Urgência => '.number_format($urgencia, 0, ',', '.').'</font>';

                echo '&nbsp;&nbsp;&nbsp;<font color="black"><b>*</b></font>&nbsp;&nbsp;&nbsp;';
                echo $font.'Baixa(s) <img src="../../../imagem/bloco_negro.gif" title="Total de Baixa(s) = Qtde da 7ª Etapa * Baixas" style="cursor:help" width="5" height="5" border="0"> => '.number_format($total_baixas_todos_niveis, 0, ',', '.').'</font>';
            ?>
            </b></font>
        </td>
        <td align='right'>
            <font size='2'><b>
            <?
                echo ($total_compra_producao_pa_princ + $total_compra_producao_pa_nivel1 + $total_compra_producao_pa_nivel2).' / ';
                echo '<font color="purple"><b>(OE='.$total_qtde_oes_todos_niveis.')</b></font>';
            ?>
            </b></font>
        </td>
        <td colspan='5'>
            &nbsp;
        </td>
        <td align='right'>
            <font size='2'><b>
                <?=$total_est_comp;?>
            </b></font>
        </td>
        <td align='right'>
            <font size='2'><b>
                <?=$total_est_prog_todos_niveis;?>
            </b></font>
        </td>
        <td>
            &nbsp;
        </td>
        <td align='right'>
            <font size='2'><b>
                <?=number_format($total_mmv_6_meses_todos_niveis, 1, ',', '.');?>
            </b></font>
        </td>
        <td align='right'>
            <font size='2'><b>
                <?=number_format($total_mmv_todos_niveis, 1, ',', '.').' / ';?>
                <?=number_format($mmv_corrigido_pa_princ + $mmv_corrigido_pa_nivel1 + $mmv_corrigido_pa_nivel2, 1, ',', '.');?>
            </b></font>
        </td>
        <td align='right'>
            <font size='2'>
            <?
                if($total_mmv_todos_niveis == 0) $total_mmv_todos_niveis = 0.01;//Para não dar erro de Divisão por Zero ...

                $ec_p_meses_todos_niveis    = $total_est_comp / $total_mmv_todos_niveis;

                if($ec_p_meses_todos_niveis <= 0) {
                    echo ' - ';
                }else {
                    $font = ($ec_p_meses_todos_niveis >= 4) ? '<font color="red"><b>': '<font color="black"><b>';
                    echo $font.number_format($ec_p_meses_todos_niveis, 1, ',', '.');
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font size='2'><b>
            <?
                $font = ($total_mlm_todos_niveis < 0) ? "<font color='red'>" : "<font color='black'>";
                echo $font.number_format($total_mlm_todos_niveis, 1, ',', '.').'</font>';
            ?>
            </b></font>
        </td>
    </tr>
<?
/*Se a Qtde a Produzir for maior do que Zero e os PA(s) não possuirem skin na Discriminação, 
então eu acrescento no vetorzinho abaixo os PAs com suas respectivas quantidades a Produzir p/ ser 
apresentado no botão de Impressão do Relatório ...*/
            if(round($a_produzir) > 0 && strpos($campos[$i]['discriminacao'], 'SKIN') == '') {
                $vetor_pas_a_produzir.= $campos[$i]['id_produto_acabado'].'|'.number_format($a_produzir, 0, ',', '').'|'.$total_compra_prod_todos_niveis.'|'.$total_qtde_oes_todos_niveis.'|'.$total_est_comp.'|'.$total_est_prog_todos_niveis.'|'.$total_mmv_todos_niveis.'|'.$total_mmv_corrigido.'|'.$total_compra_producao_pa_nivel1.'|'.$total_mlm_todos_niveis.';';
                $vetor_compra_producao.= $campos[$i]['id_produto_acabado'].'|'.number_format($a_produzir, 0, ',', '').'|'.$total_compra_prod_todos_niveis.'|'.$total_qtde_oes_todos_niveis.'|'.$total_est_comp.'|'.$total_est_prog_todos_niveis.'|'.$total_mmv_todos_niveis.'|'.$total_mmv_corrigido.'|'.$total_compra_producao_pa_nivel1.'|'.$total_mlm_todos_niveis.'|'.$est_comp_pa_princ.'|'.$ec_p_meses_todos_niveis.'|'.number_format($urgencia, 0, ',', '').'|';

                if($total_est_comp < 0) {
                    $vetor_compra_producao.= 'Urgentíssimo;';
                }else if($urgencia > 0) {
                    $vetor_compra_producao.= 'Urgente;';
                }else {
                    $vetor_compra_producao.= 'Tranquilo;';
                }
            }
//A verificação de Urgentíssimo é baseada quando o EC de todos Pais / Filhos e Netos for Menor do que Zero ...
            if($total_est_comp < 0) {
                $vetor_pas_urgentissimos.= $id_produto_acabado_princ.'|'.$est_comp_pa_princ.'|'.$total_compra_prod_todos_niveis.'|'.$total_est_comp.'|'.$total_est_prog_todos_niveis.'|'.$total_mmv_corrigido.'|'.number_format($urgencia, 0, ',', '').';';
                if(round($urgencia, 0) >= 0) $vetor_pendencia.= $id_produto_acabado_princ.'|'.number_format($a_produzir, 0, ',', '').'|'.$total_compra_prod_todos_niveis.'|'.$total_qtde_oes_todos_niveis.'|'.$total_est_comp.'|'.$total_est_prog_todos_niveis.'|'.$total_mmv_todos_niveis.'|'.$total_mmv_corrigido.'|'.$total_compra_producao_pa_nivel1.'|'.$total_mlm_todos_niveis.'|'.$est_comp_pa_princ.'|'.$ec_p_meses_todos_niveis.'|'.number_format($urgencia, 0, ',', '').'|Urgentíssimo;';
            }else if($urgencia > 0) {
                $vetor_pas_urgentes.= $id_produto_acabado_princ.'|'.$ec_p_x_meses_princ.'|'.$total_compra_prod_todos_niveis.'|'.$total_est_comp.'|'.$total_est_prog_todos_niveis.'|'.$total_mmv_corrigido.'|'.number_format($urgencia, 0, ',', '').';';
                //if($somar_hierarquia_urgentes == 1) $vetor_pas_urgentes.= '|'.$total_compra_prod_todos_niveis.'|'.$total_est_comp.'|'.$total_est_prog_todos_niveis.'|'.$total_mmv_corrigido.'|'.number_format($urgencia, 0, ',', '').';';
                if(round($urgencia, 0) >= 0) $vetor_pendencia.= $id_produto_acabado_princ.'|'.number_format($a_produzir, 0, ',', '').'|'.$total_compra_prod_todos_niveis.'|'.$total_qtde_oes_todos_niveis.'|'.$total_est_comp.'|'.$total_est_prog_todos_niveis.'|'.$total_mmv_todos_niveis.'|'.$total_mmv_corrigido.'|'.$total_compra_producao_pa_nivel1.'|'.$total_mlm_todos_niveis.'|'.$est_comp_pa_princ.'|'.$ec_p_meses_todos_niveis.'|'.number_format($urgencia, 0, ',', '').'|Urgente;';
            }
            $vetor_pas_cotacao_importacao.= $campos[$i]['id_produto_acabado'].'|'.$total_mmv_corrigido.';';
        }//Fim do Loop dos Pais ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <?
                $checked_pa_componente = ($chkt_pa_componente == 'S') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_pa_componente' id='id_pa_componente' value='S' onclick='recarregar_tela()' class='checkbox' <?=$checked_pa_componente;?>>
            <label for='id_pa_componente'>
                Seguir Caminho de PA Componente
            </label>
            &nbsp;-&nbsp;
            <?$checked_nao_mostrar_filhos = ($chkt_nao_mostrar_filhos == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_nao_mostrar_filhos' value='S' id='id_nao_mostrar_filhos' onclick='recarregar_tela()' class='checkbox' <?=$checked_nao_mostrar_filhos;?>>
            <label for='id_nao_mostrar_filhos'>
                Não Mostrar Filhos
            </label>
        </td>
        <td colspan='12'>
            <input type='button' name='cmd_cotacao_para_importacao' value='Cotação p/ Importação' title='Cotação p/ Importação' onclick='return cotacao_importacao()' style='color:darkblue' class='botao'>
<?
        if(strlen($vetor_pas_a_produzir) > 0) {//Se existir pelo menos 1 PA a Produzir apresento o botão abaixo ...
?>
            <input type='submit' name='cmd_nova_compra_producao' value='Nova Compra / Produção' title='Nova Compra / Produção' onclick="return compra_producao_pendencias('COMPRA_PRODUCAO')" style='color:black' class='botao'>
<?
        }

        //Se existir pelo menos 1 PA "Urgentissimo" ou "Urgente" apresento o botão abaixo ...
        if(strlen($vetor_pas_urgentes) > 0 || strlen($vetor_pas_urgentissimos) > 0) {
?>
            <input type='submit' name='cmd_nova_pendencias' value='Nova Pendências' title='Nova Pendências' onclick="return compra_producao_pendencias('PENDENCIAS')" style='color:darkgreen' class='botao'>
<?
        }
        /*
        if(strlen($vetor_pas_a_produzir) > 0) {//Se existir pelo menos 1 PA a Produzir apresento o botão abaixo ...
            //Comentado na data do dia 05/08/2016 ...
?>
            <!--<input type='submit' name='cmd_compra_producao' value='Compra / Produção' title='Compra / Produção' onclick='return compra_producao()' style='color:black' class='botao'>-->
<?
        }
        //Se existir pelo menos 1 PA "Urgentissimo" ou "Urgente" apresento o botão abaixo ...
        if(strlen($vetor_pas_urgentes) > 0 || strlen($vetor_pas_urgentissimos) > 0) {
            //Comentado na data do dia 05/08/2016 ...
?>
            <!--<input type='button' name='cmd_pendencias' value='Pendências' title='Pendências' onclick='return pendencias()' style='color:darkgreen' class='botao'>-->
<?
        }*/
?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
            <a name='fim'></a><!--Essa âncora é para cair diretamente aqui quando essa página acabar de ser carregada-->
        </td>
    </tr>
</table>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr align='center'>
        <td>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
</table>
<input type='hidden' name='txt_fornecedor' value='<?=$txt_fornecedor;?>'>
<!--Aqui é um Macete em que eu guardo todos os PA(s) e o MMV de Geral de toda hierarquia, 
entre pais, filhos e netos ...-->
<input type='hidden' name='hdd_vetor_pas_cotacao_importacao' value='<?=$vetor_pas_cotacao_importacao;?>'>
<!--Aqui é um Macete em que eu guardo todos os PA(s) com saldo de a_produzir > 0 "listados acima" para Imprimir no 
no relatório de Componentes-->
<input type='hidden' name='hdd_vetor_pas_a_produzir' value='<?=$vetor_pas_a_produzir;?>'>
<!--Aqui é um Macete em que eu guardo todos os PA(s) em que o EC p/ x Meses é menor do que 1 - "Urgentíssimos" ...-->
<input type='hidden' name='hdd_vetor_pas_urgentissimos' value='<?=$vetor_pas_urgentissimos;?>'>
<!--Aqui é um Macete em que eu guardo todos os PA(s) em que o EC p/ x Meses é menor do que 1 - "Urgentes" ...-->
<input type='hidden' name='hdd_vetor_pas_urgentes' value='<?=$vetor_pas_urgentes;?>'>

<!--******Parâmetros que serão levados p/ a próxima tela, depois que submeter essa tela******-->
<input type='hidden' name='hdd_vetor_compra_producao' value='<?=$vetor_compra_producao;?>'>
<input type='hidden' name='hdd_vetor_pendencia' value='<?=$vetor_pendencia;?>'>
<input type='hidden' name='hdd_acao'>
<!--*****************************************************************************************-->
</form>
</body>
</html>
<b><font color='red'>Observação:</font></b>

* Quando o EC p/x meses < 1, aparece em amarelo.
<pre>
<b><font color='darkblue'>
Fórmulas:
</font>
PA Componente ou PA Não Componente que é usado como Componente também: Ref.= <b>FIX</b>, Brocas SSC, REC VND 1.2 RETA:
* À produzir = Somatório (Compra/Produção_filhos * Qtde da Sétima Etapa) - Compra/Produção_pai - Estoque Comprometido_pai + MMV_corrigido_pai - (Baixas_pai_filhos * Qtde da Sétima Etapa) - (OEs_pai_filhos * Qtde da Sétima Etapa)
<font color='red'>* Cabos de Lima e Limas Agulha estamos tratando como PA não Componente.</font>
* Urgência = MMV_corrigido_pai - Ec_pai

PA Não Componente: 

* À produzir = 0, quando o Macho tem mais de uma peça por Jogo, a Produção é feita pelos Blanks ou ...
* À produzir = total_mmv_corrigido - total_compra_producao - total_ec_corrigido + I.F.pai

<font color='red'>(Não descontamos as Baixas p/ PA(s) Não Componente(s)) (Roberto vai analisar)</font>

* Urgência = total_mmv_corrigido - total_ec_corrigido - total_oe_corrigido - <font color='red'>(Se qtde_meses <= 1, então Desconto o Total Programado)</font>

Fator TOP A - 1,50
Fator TOP B - 1,25

Fator.Corr.TOP vs Qtde_meses = Se (Qtde_meses >= <?=$qtde_meses_p_corrigir_mmv_dos_top;?>): Qtde_meses - <?=$qtde_meses_p_corrigir_mmv_dos_top;?> + <?=$qtde_meses_p_corrigir_mmv_dos_top;?> * Fator_Correção_Top senão, Qtde_meses * Fator_Correção_Top

MMV_corrigido (Necessidade p/ Qtde Meses) = MMV * Fat.Corr.MMV  * Fat.Corr.TOP vs Qtde_meses

</b>
* Como usamos o MMV na Produção do(s) PA(s), não precisamos usar ele na produção dos Componentes.
</pre>
<?
    }
?>