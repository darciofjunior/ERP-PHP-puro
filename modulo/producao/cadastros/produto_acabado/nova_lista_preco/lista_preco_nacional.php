<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/custos.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/nova_lista_preco/lista_preco.php', '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>LISTA DE PREÇO NACIONAL ATUALIZADA COM SUCESSO.</font>";

$fator_desc_max_vendas 	= genericas::variavel(19);//Fator Desc Máx. de Vendas
$fator_margem_lucro 	= genericas::variavel(22);//margem de Lucro PA

if($passo == 1) {
    /*Como esse processamento pode ser muito pesado, deixo o servidor operar excepcionalmente em até 
    3 minutos para essa tela ...*/
    set_time_limit(180);
    
    //Essa combo tem de funcionar sozinha apenas, sem concatenar com nenhuma outra opção ...
    if(!empty($cmb_grupo_pa_vs_empresa_divisao) && $cmb_grupo_pa_vs_empresa_divisao != '%') {
        $txt_referencia                     = '%';
        $txt_discriminacao                  = '%';
        $cmb_empresa_divisao                = '%';
        $cmb_grupo_pa                       = '%';
        $cmb_familia                        = '%';
        $cmb_order_by                       = '1';
    }else {//Filtro normal ...
        if(!empty($chkt_novo_preco_promocional)) 	$condicao_novo_preco_promocional    = " AND (pa.preco_promocional_simulativa <> '0' OR pa.preco_promocional_simulativa_b <> '0') ";
        if(!empty($chkt_preco_promocional_atual))	$condicao_preco_promocional_atual   = " AND (pa.preco_promocional <> '0' OR pa.preco_promocional_b <> '0') ";
        if(!empty($chkt_todos_produtos_zerados)) 	$condicao_produtos_zerados          = " AND pa.preco_unitario_simulativa = '0.00' ";
        if(empty($cmb_grupo_pa_vs_empresa_divisao))     $cmb_grupo_pa_vs_empresa_divisao    = '%';
        if(empty($cmb_empresa_divisao)) 		$cmb_empresa_divisao                = '%';
        if(empty($cmb_grupo_pa)) 			$cmb_grupo_pa                       = '%';
        if(empty($cmb_familia)) 			$cmb_familia                        = '%';
        if(empty($cmb_order_by)) 			$cmb_order_by                       = '1';
    }
	
//Aqui traz todos os grupos com exceção dos que são pertencentes a Família de Componentes
    $sql = "SELECT pa.id_produto_acabado, pa.operacao_custo, pa.operacao_custo_sub, pa.referencia, pa.discriminacao, pa.preco_unitario, pa.qtde_promocional, pa.preco_promocional, pa.qtde_promocional_b, pa.preco_promocional_b, pa.preco_unitario_simulativa, 
            pa.perc_estimativa_custo, pa.qtde_promocional_simulativa, pa.preco_promocional_simulativa, pa.qtde_promocional_simulativa_b, pa.preco_promocional_simulativa_b, pa.mmv, 
            pa.status_top, pa.status_custo, ed.razaosocial, gpa.nome, ged.* 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.`id_gpa_vs_emp_div` LIKE '$cmb_grupo_pa_vs_empresa_divisao' 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_grupo_pa LIKE '$cmb_grupo_pa' AND gpa.id_familia LIKE '$cmb_familia' AND gpa.id_familia <> '23' 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao AND ed.id_empresa_divisao LIKE '$cmb_empresa_divisao' 
            WHERE pa.referencia LIKE '%$txt_referencia%' 
            AND pa.discriminacao LIKE '%$txt_discriminacao%' 
            AND pa.referencia <> 'ESP' 
            AND pa.status_nao_produzir = '0' 
            AND pa.ativo = '1' 
            $condicao_novo_preco_promocional $condicao_preco_promocional_atual $condicao_produtos_zerados ORDER BY $cmb_order_by ";
    $campos = bancos::sql($sql, $inicio, 200, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'lista_preco_nacional.php?valor=1'
        </Script>
<?
    }else {
//Variáveis utilizadas nos cálculos abaixo ...
        $taxa_financeira_vendas         = genericas::variaveis('taxa_financeira_vendas');
        //Esse fator servirá p/ corrigir os preços de promoção de 30 p/ 60 ...
        $fator_taxa_financeira_vendas   = (1 + $taxa_financeira_vendas / 100);
?>
<html>
<head>
<title>.:: Nova Lista de Preço Nacional ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['id_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['id_produto_acabado[]'].length)
    }
//Verifico se existe Hum item que esteja com esse campo "perc_estimativa_custo" preenchido ...
    for(i = 0; i < linhas; i++) {
        if(strtofloat(document.getElementById('txt_aumento_est_custo_lista_nova'+i).value) > 0) {
            var resposta = confirm('EXISTE(M) ITEM(NS) COM % AUMENTO EST. CUSTO L. NOVA. TEM CERTEZA DE QUE QUER MANTÊ-LO(S) ?')
            if(resposta == true) {//Se o usuário aceitou a Pergunta ...
                break;//Avança e sai fora do Loop ...
            }else {//Se rejeitou para o código por aqui não submetendo a Tela ...
                return false
            }
        }
    }
//Prepara no formato moeda antes de submeter para o BD ...
    for(i = 0; i < linhas; i++) {
        document.getElementById('txt_qtde_promocional_novo'+i).value 		= strtofloat(document.getElementById('txt_qtde_promocional_novo'+i).value)
        document.getElementById('txt_preco_promocional_novo'+i).value 		= strtofloat(document.getElementById('txt_preco_promocional_novo'+i).value)
        document.getElementById('txt_qtde_promocional_novo_b'+i).value 		= strtofloat(document.getElementById('txt_qtde_promocional_novo_b'+i).value)
        document.getElementById('txt_preco_promocional_novo_b'+i).value 	= strtofloat(document.getElementById('txt_preco_promocional_novo_b'+i).value)
        document.getElementById('txt_preco_bruto_fat_novo_rs'+i).value 		= strtofloat(document.getElementById('txt_preco_bruto_fat_novo_rs'+i).value)
        document.getElementById('txt_aumento_est_custo_lista_nova'+i).value     = strtofloat(document.getElementById('txt_aumento_est_custo_lista_nova'+i).value)
        document.getElementById('txt_aumento_est_custo_lista_nova'+i).disabled 	= false
        document.getElementById('txt_preco_bruto_fat_novo_rs'+i).disabled 	= false
    }
}

function calcular_preco_promocional_a_dif(indice) {
    var preco_promocional_novo = eval(strtofloat(document.getElementById('txt_preco_promocional_novo'+indice).value))
    if(isNaN(preco_promocional_novo)) 	preco_promocional_novo = 0
    var preco_promocional_atual = eval(strtofloat(document.getElementById('txt_preco_promocional_atual'+indice).value))
    if(isNaN(preco_promocional_atual)) 	preco_promocional_atual = 0

    document.getElementById('txt_preco_promocional_dif_perc'+indice).value 	= (preco_promocional_novo / preco_promocional_atual - 1) * 100
    document.getElementById('txt_preco_promocional_dif_perc'+indice).value 	= arred(document.getElementById('txt_preco_promocional_dif_perc'+indice).value, 1, 1)
    document.getElementById('txt_margem_lucro_novo_a'+indice).value 		= (preco_promocional_novo / document.getElementById('hdd_preco_margem_lucro_zero'+indice).value - 1) * 100
    document.getElementById('txt_margem_lucro_novo_a'+indice).value             = arred(document.getElementById('txt_margem_lucro_novo_a'+indice).value, 1, 1)
}

function calcular_preco_promocional_b_dif(indice) {
    var preco_promocional_novo_b = eval(strtofloat(document.getElementById('txt_preco_promocional_novo_b'+indice).value))
    if(isNaN(preco_promocional_novo_b)) 	preco_promocional_novo_b = 0
    var preco_promocional_atual_b = eval(strtofloat(document.getElementById('txt_preco_promocional_atual_b'+indice).value))
    if(isNaN(preco_promocional_atual_b)) 	preco_promocional_atual_b = 0
    
    if(preco_promocional_atual_b != 0) {//P/ não dar erro de Divisão por Zero ...
        document.getElementById('txt_preco_promocional_b_dif_perc'+indice).value 	= (preco_promocional_novo_b / preco_promocional_atual_b - 1) * 100
    }else {
        document.getElementById('txt_preco_promocional_b_dif_perc'+indice).value 	= 0
    }

    document.getElementById('txt_preco_promocional_b_dif_perc'+indice).value 	= arred(document.getElementById('txt_preco_promocional_b_dif_perc'+indice).value, 1, 1)
    document.getElementById('txt_margem_lucro_novo_b'+indice).value 		= ((preco_promocional_novo_b / '<?=$fator_taxa_financeira_vendas;?>') / document.getElementById('hdd_preco_margem_lucro_zero'+indice).value - 1) * 100
    document.getElementById('txt_margem_lucro_novo_b'+indice).value 		= arred(document.getElementById('txt_margem_lucro_novo_b'+indice).value, 1, 1)
    /******************Lógica de Cálculo para Preço Promocional B Ideal******************/
    //Padrões Iniciais ao chamar essa função quando a mesma é invocada ...
    document.getElementById('txt_preco_liq_fat_atual_rs'+indice).className      = 'textdisabled'
    
    //Essa linha foi comentada no dia 14/05/2013, devido algumas mudanças solicitas pelo Roberto ...
    //document.getElementById('txt_preco_promocional_ideal_b'+indice).value               = document.getElementById('hdd_preco_promocional_novo_b_inicial'+indice).value
    var preco_liq_fat_atual_rs 	= eval(strtofloat(document.getElementById('txt_preco_liq_fat_atual_rs'+indice).value))
    var preco_custo_ml_min_rs 	= eval(strtofloat(document.getElementById('txt_preco_custo_ml_min_rs'+indice).value))

    //Se o Novo Preço B for Maior do que o Preço Atual que está com 20% de Desconto em R$ ...
    if(preco_promocional_novo_b >= preco_liq_fat_atual_rs) document.getElementById('txt_preco_promocional_ideal_b'+indice).value 			= document.getElementById('txt_preco_liq_fat_atual_rs'+indice).value
    if(preco_promocional_novo_b > preco_liq_fat_atual_rs) {
        document.getElementById('txt_preco_liq_fat_atual_rs'+indice).style.color        = 'white'
        document.getElementById('txt_preco_liq_fat_atual_rs'+indice).style.background   = 'red'
    }
    //Se a Diferença do Preço Novo com o Preço Atual da Coluna Preço Líquido + 20% Fat, for negativa, deixa a coluna em vermelho ...
    var preco_liq_dif_perc 	= eval(strtofloat(document.getElementById('txt_preco_liq_dif_perc'+indice).value))   
    if(preco_liq_dif_perc < 0) {
        document.getElementById('txt_preco_liq_dif_perc'+indice).style.color 		= 'white'
        document.getElementById('txt_preco_liq_dif_perc'+indice).style.background       = 'red'
    }else if(preco_liq_dif_perc >= 7) {
        document.getElementById('txt_preco_liq_dif_perc'+indice).style.color 		= 'white'
        document.getElementById('txt_preco_liq_dif_perc'+indice).style.background       = 'darkblue'
    }else {
        document.getElementById('txt_preco_liq_dif_perc'+indice).className              = 'textdisabled'
    }
    if(preco_promocional_novo_b <= preco_custo_ml_min_rs) document.getElementById('txt_preco_promocional_ideal_b'+indice).value 			= document.getElementById('txt_preco_custo_ml_min_rs'+indice).value
    /************************************************************************************/
}

function calcular_preco_liq_fat_20_desc_dif(indice) {
    var fator_desc_max_vendas 	= eval('<?=$fator_desc_max_vendas;?>')
/**********************************************************************************************/
//Tratamento para não dar erros nos cálculos
    var desc_a_lista_nova       = eval(strtofloat(document.getElementById('hdd_desc_a_lista_nova'+indice).value))
    var desc_b_lista_nova       = eval(strtofloat(document.getElementById('hdd_desc_b_lista_nova'+indice).value))
    var acrescimo_lista_nova    = eval(strtofloat(document.getElementById('hdd_acrescimo_lista_nova'+indice).value))
/**********************************************************************************************/
//Fórmula do Novo Preço Bruto Fat. R$
    var preco_liq_fat_novo_rs           = eval(strtofloat(document.getElementById('txt_preco_liq_fat_novo_rs'+indice).value))
    if(isNaN(preco_liq_fat_novo_rs))    preco_liq_fat_novo_rs = 0
    var preco_liq_fat_atual_rs          = eval(strtofloat(document.getElementById('txt_preco_liq_fat_atual_rs'+indice).value))
    if(isNaN(preco_liq_fat_atual_rs))   preco_liq_fat_atual_rs = 0
    
    if(preco_liq_fat_atual_rs == 0) {
        document.getElementById('txt_preco_liq_dif_perc'+indice).value  = 0
    }else {
        document.getElementById('txt_preco_liq_dif_perc'+indice).value  = (preco_liq_fat_novo_rs / preco_liq_fat_atual_rs - 1) * 100
    }
    
    document.getElementById('txt_preco_bruto_fat_novo_rs'+indice).value         = preco_liq_fat_novo_rs / ((1 - desc_a_lista_nova / 100) * (1 - desc_b_lista_nova / 100) * (1 + acrescimo_lista_nova / 100)) / fator_desc_max_vendas
    document.getElementById('txt_preco_liq_dif_perc'+indice).value              = arred(document.getElementById('txt_preco_liq_dif_perc'+indice).value, 1, 1)
    document.getElementById('txt_preco_bruto_fat_novo_rs'+indice).value         = arred(document.getElementById('txt_preco_bruto_fat_novo_rs'+indice).value, 2, 1)  
    
    //Se a Diferença do Preço Novo com o Preço Atual da Coluna Preço Líquido + 20% Fat, for negativa, deixa a coluna em vermelho ...
    var preco_liq_dif_perc 	= eval(strtofloat(document.getElementById('txt_preco_liq_dif_perc'+indice).value))   
    if(preco_liq_dif_perc < 0) {
        document.getElementById('txt_preco_liq_dif_perc'+indice).style.color        = 'white'
        document.getElementById('txt_preco_liq_dif_perc'+indice).style.background   = 'red'
    }else if(preco_liq_dif_perc >= 7) {
        document.getElementById('txt_preco_liq_dif_perc'+indice).style.color        = 'white'
        document.getElementById('txt_preco_liq_dif_perc'+indice).style.background   = 'darkblue'
    }else {
        document.getElementById('txt_preco_liq_dif_perc'+indice).className          = 'textdisabled'
    }
    //Cálculo por Item das Vendas Novas e Atuais ...
    var mmv                     = eval(strtofloat(document.getElementById('hdd_mmv'+indice).value))
    var preco_liq_fat_novo_rs   = eval(strtofloat(document.getElementById('txt_preco_liq_fat_novo_rs'+indice).value))
    var preco_liq_fat_atual_rs  = eval(strtofloat(document.getElementById('txt_preco_liq_fat_atual_rs'+indice).value))
    
    document.getElementById('txt_total_venda_novo_rs'+indice).value    = preco_liq_fat_novo_rs * mmv
    document.getElementById('txt_total_venda_novo_rs'+indice).value    = arred(document.getElementById('txt_total_venda_novo_rs'+indice).value, 2, 1)
    document.getElementById('txt_total_venda_atual_rs'+indice).value   = preco_liq_fat_atual_rs * mmv
    document.getElementById('txt_total_venda_atual_rs'+indice).value   = arred(document.getElementById('txt_total_venda_atual_rs'+indice).value, 2, 1)
}

function copiar(indice) {
    var fator_desc_max_vendas 	= eval('<?=$fator_desc_max_vendas;?>')
    var preco_custo_ml_min_rs = eval(strtofloat(document.getElementById('txt_preco_custo_ml_min_rs'+indice).value))
    document.getElementById('txt_preco_liq_fat_novo_rs'+indice).value = preco_custo_ml_min_rs
    document.getElementById('txt_preco_liq_fat_novo_rs'+indice).value = arred(document.getElementById('txt_preco_liq_fat_novo_rs'+indice).value, 2, 1)
    calcular_preco_liq_fat_20_desc_dif(indice)
}

function copiar_preco_ideal_para_preco_b(indice) {
    document.getElementById('txt_preco_promocional_novo_b'+indice).value = document.getElementById('txt_preco_promocional_ideal_b'+indice).value
    calcular_preco_promocional_b_dif(indice)
}

function copiar_qtde_promocional_a(indice) {
    document.getElementById('txt_qtde_promocional_novo'+indice).value = document.getElementById('txt_qtde_promocional_atual'+indice).value
}

function copiar_qtde_promocional_b(indice) {
    //document.getElementById('txt_qtde_promocional_novo_b'+indice).value = document.getElementById('txt_qtde_promocional_atual_b'+indice).value
    document.getElementById('txt_qtde_promocional_novo_b'+indice).value = document.getElementById('txt_qtde_promocional_novo'+indice).value
}

function calcular_geral() {
    var elementos                   = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['id_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['id_produto_acabado[]'].length)
    }
    for(var i = 0; i < linhas; i++) calcular_preco_liq_fat_20_desc_dif(i)
}

function copiar_preco_ideal_para_preco_b_geral() {
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['id_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['id_produto_acabado[]'].length)
    }
    for(var i = 0; i < linhas; i++) copiar_preco_ideal_para_preco_b(i)
}

function copiar_geral() {
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['id_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['id_produto_acabado[]'].length)
    }
    for(var i = 0; i < linhas; i++) copiar(i)
    calcular_geral()
}

function calcular_novo_desconto_a() {
    var elementos                       = document.form.elements
    var total_geral_venda_novo_rs       = 0
    var total_geral_venda_atual_rs      = 0
    var cmb_grupo_pa_vs_empresa_divisao = '<?=$cmb_grupo_pa_vs_empresa_divisao;?>'
    
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['id_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['id_produto_acabado[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        total_venda_novo_rs  = eval(strtofloat(document.getElementById('txt_total_venda_novo_rs'+i).value))
        total_venda_atual_rs = eval(strtofloat(document.getElementById('txt_total_venda_atual_rs'+i).value))

        total_geral_venda_novo_rs+= total_venda_novo_rs
        total_geral_venda_atual_rs+= total_venda_atual_rs
    }
    if(cmb_grupo_pa_vs_empresa_divisao != '' && cmb_grupo_pa_vs_empresa_divisao != '%') {//Essa cálculo só irá existir se essa combo tiver alguma opção preenchida ...
        total_geral_venda_novo_rs   = eval(strtofloat(arred(String(total_geral_venda_novo_rs), 2, 1)))
        total_geral_venda_atual_rs  = eval(strtofloat(arred(String(total_geral_venda_atual_rs), 2, 1)))

        document.getElementById('txt_aumento_ideal').value  = ((total_geral_venda_novo_rs / total_geral_venda_atual_rs - 1) * 100)
        document.getElementById('txt_aumento_ideal').value  = arred(document.getElementById('txt_aumento_ideal').value, 1, 1)

        var aumento_ideal                                           = eval(strtofloat(document.getElementById('txt_aumento_ideal').value))
        document.getElementById('txt_novo_desconto_a_ideal').value  = (1 - (100 - eval('<?=intval($campos[0]['desc_base_a_nac']);?>')) / 100 * (1 + aumento_ideal / 100)) * 100
        document.getElementById('txt_novo_desconto_a_ideal').value  = arred(document.getElementById('txt_novo_desconto_a_ideal').value, 1, 1)
    }else {
        document.getElementById('txt_aumento_ideal').value          = ''
        document.getElementById('txt_novo_desconto_a_ideal').value  = ''
    }
}

function copiar_qtde_promocional_a_geral() {
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['id_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['id_produto_acabado[]'].length)
    }
    for(var i = 0; i < linhas; i++) copiar_qtde_promocional_a(i)
}

function copiar_qtde_promocional_b_geral() {
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['id_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['id_produto_acabado[]'].length)
    }
    for(var i = 0; i < linhas; i++) copiar_qtde_promocional_b(i)
}

function copiar_perc_estimativa_custo_geral(indice) {      
    if(indice >= 0) {//Aqui significa que eu estou digitando na linha corrente ...
        if(document.getElementById('txt_aumento_est_custo_lista_nova'+indice).value == '') {
            alert('DIGITE UMA % DE ESTIMATIVA DE CUSTO PARA NOVA LISTA !')
            document.getElementById('txt_aumento_est_custo_lista_nova'+indice).focus()
            return false
        }
        var perc_estimativa_custo = eval(strtofloat(document.getElementById('txt_aumento_est_custo_lista_nova'+indice).value))
        var preco_max_custo_fat_rs = eval(strtofloat(document.getElementById('hdd_preco_max_custo_fat_rs'+indice).value))
        document.getElementById('txt_preco_max_custo_fat_rs'+indice).value = (perc_estimativa_custo / 100 + 1) * preco_max_custo_fat_rs
        document.getElementById('txt_preco_max_custo_fat_rs'+indice).value = arred(document.getElementById('txt_preco_max_custo_fat_rs'+indice).value, 2, 1)

        var margem_lucro_minima = eval(strtofloat(document.getElementById('txt_margem_lucro_minima'+indice).value))
        var preco_max_custo_fat_rs = eval(strtofloat(document.getElementById('txt_preco_max_custo_fat_rs'+indice).value))
        document.getElementById('txt_preco_custo_ml_min_rs'+indice).value = preco_max_custo_fat_rs / 2 * (margem_lucro_minima / 100 + 1)
        document.getElementById('txt_preco_custo_ml_min_rs'+indice).value = arred(document.getElementById('txt_preco_custo_ml_min_rs'+indice).value, 2, 1)
        /*********************************************************************************************/
        //Nessa parte eu verifico qual o preço utilizar p/ o Preço Ideal ...
        var preco_custo_ml_min_rs       = eval(strtofloat(document.getElementById('txt_preco_custo_ml_min_rs'+indice).value))
        var preco_promocional_novo_b 	= eval(strtofloat(document.getElementById('txt_preco_promocional_novo_b'+indice).value))
        var preco_liq_fat_atual_rs      = eval(strtofloat(document.getElementById('txt_preco_liq_fat_atual_rs'+indice).value))

        //if(preco_promocional_novo_b >= preco_liq_fat_atual_rs) 	document.getElementById('txt_preco_promocional_ideal_b'+indice).value = document.getElementById('txt_preco_promocional_novo_b'+indice).value
        //if(preco_promocional_novo_b <= preco_custo_ml_min_rs) 	document.getElementById('txt_preco_promocional_ideal_b'+indice).value = document.getElementById('txt_preco_custo_ml_min_rs'+indice).value
        /*********************************************************************************************/
    }else {
        if(document.form.txt_aumento_est_custo_lista_nova_geral.value == '') {
            alert('DIGITE UMA % DE ESTIMATIVA DE CUSTO PARA NOVA LISTA !')
            document.form.txt_aumento_est_custo_lista_nova_geral.focus()
            return false
        }
        var elementos = document.form.elements
        //Prepara a Tela p/ poder gravar no BD ...
        if(typeof(elementos['id_produto_acabado[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
        }else {
            var linhas = (elementos['id_produto_acabado[]'].length)
        }
        var perc_estimativa_custo_geral = eval(strtofloat(document.form.txt_aumento_est_custo_lista_nova_geral.value))
        for(var i = 0; i < linhas; i++) {
            document.getElementById('txt_aumento_est_custo_lista_nova'+i).value = document.form.txt_aumento_est_custo_lista_nova_geral.value
            var preco_max_custo_fat_rs = eval(strtofloat(document.getElementById('hdd_preco_max_custo_fat_rs'+i).value))
            document.getElementById('txt_preco_max_custo_fat_rs'+i).value = (perc_estimativa_custo_geral / 100 + 1) * preco_max_custo_fat_rs
            document.getElementById('txt_preco_max_custo_fat_rs'+i).value = arred(document.getElementById('txt_preco_max_custo_fat_rs'+i).value, 2, 1)

            var margem_lucro_minima = eval(strtofloat(document.getElementById('txt_margem_lucro_minima'+i).value))
            var preco_max_custo_fat_rs = eval(strtofloat(document.getElementById('txt_preco_max_custo_fat_rs'+i).value))
            document.getElementById('txt_preco_custo_ml_min_rs'+i).value = preco_max_custo_fat_rs / 2 * (margem_lucro_minima / 100 + 1)
            document.getElementById('txt_preco_custo_ml_min_rs'+i).value = arred(document.getElementById('txt_preco_custo_ml_min_rs'+i).value, 2, 1)

            /*********************************************************************************************/
            //Nessa parte eu verifico qual o preço utilizar p/ o Preço Ideal ...
            var preco_custo_ml_min_rs 		= eval(strtofloat(document.getElementById('txt_preco_custo_ml_min_rs'+i).value))
            var preco_promocional_novo_b 	= eval(strtofloat(document.getElementById('txt_preco_promocional_novo_b'+i).value))
            var preco_liq_fat_atual_rs 		= eval(strtofloat(document.getElementById('txt_preco_liq_fat_atual_rs'+i).value))
            
            //if(preco_promocional_novo_b >= preco_liq_fat_atual_rs) 	document.getElementById('txt_preco_promocional_ideal_b'+i).value = document.getElementById('txt_preco_promocional_novo_b'+i).value
            //if(preco_promocional_novo_b <= preco_custo_ml_min_rs) 	document.getElementById('txt_preco_promocional_ideal_b'+i).value = document.getElementById('txt_preco_custo_ml_min_rs'+i).value
           
            /*****************************************************************************************************/
            /***********************************Nova Logística Preço Promocional**********************************/
            /*****************************************************************************************************/
            preco_custo_ml_min_promocional  = eval(preco_custo_ml_min_rs * '<?=$fator_taxa_financeira_vendas;?>')
            estoque_queima                  = document.getElementById('hdd_estoque_queima'+i).value
            preco_excesso_estoque           = (estoque_queima > 0) ? eval(document.getElementById('hdd_preco_excesso_estoque'+i).value) : 0
            
            if(estoque_queima > 0) {
                preco_medio_promocional     = (preco_custo_ml_min_promocional + preco_excesso_estoque) / 2
                preco_minimo_promocional    = Math.min(preco_medio_promocional, preco_custo_ml_min_promocional)
            }else {
                preco_minimo_promocional    = preco_custo_ml_min_promocional
            }
            if(preco_minimo_promocional > preco_liq_fat_atual_rs) {
                document.getElementById('txt_preco_promocional_ideal_b'+i).value = 0
            }else {
                document.getElementById('txt_preco_promocional_ideal_b'+i).value = preco_minimo_promocional;
            }
            document.getElementById('txt_preco_promocional_ideal_b'+i).value = arred(document.getElementById('txt_preco_promocional_ideal_b'+i).value, 2, 1)
            /*****************************************************************************************************/
        }
    }
}
</Script>
</head>
<body>
<form name='form' action="<?=$PHP_SELF.'?passo=2';?>" method='post' onsubmit="return validar()">
<table width="98%" border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='32'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='32'>
            Nova Lista de Preço Nacional
            &nbsp;
            <?
                //Se essa combo tiver alguma opção preenchida, levo o parâmetro desta p/ o Pop-Up de Alterar Alíquotas da Lista Nova ...
                if(!empty($cmb_grupo_pa_vs_empresa_divisao) && $cmb_grupo_pa_vs_empresa_divisao != '%') $parametro_aliquota = '?cmb_grupo_pa_vs_empresa_divisao='.$cmb_grupo_pa_vs_empresa_divisao;
            ?>
            <img src = "../../../../../imagem/menu/alterar.png" border='0' title="Alterar Alíquotas da Lista Nova" alt="Alterar Alíquotas da Lista Nova" onClick="nova_janela('aliquotas_lista_nova.php<?=$parametro_aliquota;?>', 'LISTA_NOVA', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')">
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Produto</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>TOP/ O.C./<br/> MMV / MMV Atrel</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Dados Custo</b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <font title="Qtde Promocional A" size='-2'>
                <b>Qtde <br>Promo A</b>
            </font>
        </td>
        <td colspan='3' bgcolor='#CECECE'>
            <font title="Preço Promocional A R$" size='-2'>
                <b>Preço <br>Promo A R$</b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>M.L. Promo A</b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <font title="Qtde Promocional B" size='-2'>
                <b>Qtde <br>Promo B</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title="Diferença %A / B Novo" size='-2'>
                <b>Dif.%<br>A/B<br>Novo</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title="Promoção B Ideal R$" size='-2'>
                <b>Promo B<br>Ideal 60 ddl R$</b>
            </font>
            <img src = '../../../../../imagem/seta_direita.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_preco_ideal_para_preco_b_geral()'>
        </td>
        <td colspan='3' bgcolor='#CECECE'>
            <font title="Preço Promocional B R$" size='-2'>
                <b>Preço <br>Promo B 60 ddl R$</b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>M.L. Promo B</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Preço R$<br>Concor.A</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Preço R$<br>Concor.B</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Preço R$<br>Concor.C</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>ML.Min.%
                <br>Gr.x Div. Nova</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='% de Aumento Estimativo de Custo p/ Cálculo da Lista Nova' size='-2' style='cursor:help'>
                <b>%Aum 
                <br>Est.Custo
                <br>L.Nova</b>
            </font>
            <input type='text' name="txt_aumento_est_custo_lista_nova_geral" maxlength="6" size="6" onkeyup="verifica(this, 'moeda_especial', '1', '1', event)" class='caixadetexto'>
            <img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_perc_estimativa_custo_geral()">
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Pço.Custo R$<br>ML. 100%</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Pço.Custo <br>ML. Mín <br>Nova R$</b>
                <img src = '../../../../../imagem/seta_direita.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_geral();calcular_novo_desconto_a()'>
            </font>
        </td>
        <td colspan='3' bgcolor='#CECECE'>
            <font size='-2'>
                <b>Preço Líquido + 20% Fat</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title="Novo Preço Bruto Faturado R$" size='-2'>
                <b>Novo Pr.R$ <br>Bruto Fat.</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title="Total de Venda Novo" size='-2'>
                <b>Tot Venda <br>Novo </b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title="Total de Venda Atual" size='-2'>
                <b>Tot Venda <br>Atual </b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Novo</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <img src="../../../../../imagem/seta_esquerda.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_qtde_promocional_a_geral()">
            <font size='-2'>
                <b>Atual</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Novo</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Atual</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Dif %</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Novo</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Atual</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Novo</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <img src="../../../../../imagem/seta_esquerda.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_qtde_promocional_b_geral()">
            <font size='-2'>
                <b>Atual</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Novo</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Atual</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Dif. %</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Novo</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Atual</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Novo</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <?
                $title = number_format($campos[0]['desc_base_a_nac'], 2, ',', '.');
                if($campos[0]['desc_base_b_nac'] > 0) 		$title.= '+'.number_format($campos[0]['desc_base_b_nac'], 2, ',', '.');
                if($campos[0]['acrescimo_base_nac'] > 0) 	$title.= 'Ac.'.number_format($campos[0]['acrescimo_base_nac'], 2, ',', '.');
            ?>
            <font title='<?=$title;?>' style='cursor:help' size='-2'>
                <b>Atual</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font size='-2'>
                <b>Dif %</b>
            </font>
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
//Fórmula do Preço Máximo Custo Fat. R$ - esse campo está aqui, mais ele é printado + abaixo
/*A percentagem extra, eu já aplico desde o início no "Preço Máximo Custo Fat. R$", pq essa variável é
utilizada praticamente em todos o cálculo dessa Lista ...*/
            $preco_custo_pa                     = custos::preco_custo_pa($campos[$i]['id_produto_acabado']);
            $preco_maximo_custo_fat_rs          = $preco_custo_pa / $fator_desc_max_vendas * ($campos[$i]['perc_estimativa_custo'] / 100 + 1);
            //Forço o arred. para 2 casas para não dar erro na fórmula por causa do JavaScript -> Dárcio ...
            $preco_maximo_custo_fat_rs          = round($preco_maximo_custo_fat_rs, 2);

            $preco_maximo_custo_fat_rs_sem_perc = $preco_custo_pa / $fator_desc_max_vendas;
            //Forço o arred. para 2 casas para não dar erro na fórmula por causa do JavaScript -> Dárcio ...
            $preco_maximo_custo_fat_rs_sem_perc = round($preco_maximo_custo_fat_rs_sem_perc, 2);

            if($campos[$i]['operacao_custo'] == 0) {//Operação de Custo = Industrial
//Atribui de acordo com o Fator Margem de Lucro declarado no início do código
                $fator_margem_lucro_loop = $fator_margem_lucro;
                $printar = segurancas::number_format(($fator_margem_lucro_loop - 1) * 100, 2, '.');
            }else {//Operação de Custo = Revenda
//Busco somente o id_fornecedor default para saber qual fornecedor q estou pegando para calcular o custo PA
                $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($campos[$i]['id_produto_acabado'], '', 1);
//Busco desse Fornecedor e PA corrente o Fator Margem de Lucro na Lista de Preços
                $sql = "SELECT fpi.fator_margem_lucro_pa 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' AND pa.`ativo` = '1' 
                        WHERE fpi.`id_fornecedor` = '$id_fornecedor_setado' LIMIT 1 ";
                $campos_fator               = bancos::sql($sql);
                $fator_margem_lucro_loop    = $campos_fator[0]['fator_margem_lucro_pa'];
                if(empty($fator_margem_lucro_loop)) $fator_margem_lucro_loop = 1;
                $printar = segurancas::number_format(($fator_margem_lucro_loop - 1) * 100, 2, '.');
            }
            //Fórmula do Preço Líquido Fat. Atual R$
            $preco_liq_fat_atual_rs = round($campos[$i]['preco_unitario'] * (1 - $campos[$i]['desc_base_a_nac'] / 100) * (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100) * $fator_desc_max_vendas, 2);
            //Utilizada em alguns campos + abaixo ...
            $preco_margem_lucro_zero = $preco_maximo_custo_fat_rs / ($fator_margem_lucro_loop / $fator_desc_max_vendas);
            //Aqui eu faço o cálculo quando carregar a Tela no início ...
            if($campos[$i]['preco_promocional_b'] != '' && $campos[$i]['preco_promocional_b'] != '0.00') {
                $margem_lucro_novo_b 	= number_format((($campos[$i]['preco_promocional_simulativa_b'] / $preco_margem_lucro_zero) - 1) * 100, 2, ',', '.');
                $margem_lucro_atual_b 	= number_format((($campos[$i]['preco_promocional_b'] / $preco_margem_lucro_zero) - 1) * 100, 2, ',', '.');
            }else {
                $margem_lucro_novo_b 	= '';
                $margem_lucro_atual_b 	= '';
            }

            //Se a Margem de Lucro < 70%, não aplicamos a redução de 5% pois a ML ficaria abaixo de 45% para Atacadista.
            if($campos[$i]['status_top'] == 1) {//Quando é TOP A 95% ...
                $margem_lucro_minima_corr = ($campos[$i]['margem_lucro_minima'] <= 70) ? $campos[$i]['margem_lucro_minima'] : $campos[$i]['margem_lucro_minima'] * 0.95;
            }else if($campos[$i]['status_top'] == 2) {//TOP é 100% ...
                $margem_lucro_minima_corr = $campos[$i]['margem_lucro_minima'];
            }else if($campos[$i]['status_top'] == 0) {//Sem ser TOP 110% ...
                $margem_lucro_minima_corr = $campos[$i]['margem_lucro_minima'] * 1.1;
            }

            $preco_custo_ml_min_rs = round(($preco_maximo_custo_fat_rs / 2) * (1 + $margem_lucro_minima_corr / 100), 2);
            //Busca dos Preços dos Concorrentes atrelados ao Produto Acabado ...
            $sql = "SELECT cpa.com_ipi, cpa.com_st, cpa.preco_liquido, cc.nome 
                    FROM `concorrentes_vs_prod_acabados` cpa 
                    INNER JOIN `concorrentes` cc ON cc.id_concorrente = cpa.id_concorrente 
                    WHERE cpa.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' ";
            $campos_concorrentes = bancos::sql($sql);
            $url = ($campos[$i]['operacao_custo'] == 0) ? '../../../custo/industrial/custo_industrial.php?id_produto_acabado='.$campos[$i]['id_produto_acabado'].'&tela=2&pop_up=1' : '../../../custo/revenda/custo_revenda.php?id_produto_acabado='.$campos[$i]['id_produto_acabado'];

            //Essa variável "$retorno_pas_atrelados" será utilizada + abaixo, em alguns pontos ...
            $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align="left">
            <a href="javascript:nova_janela('<?=$url;?>', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" style="cursor:help" class="link">
                <font title="Grupo P.A. (E. D.): <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>" size='-2'>
                    <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
                </font>
            </a>
        </td>
        <td>
            <font color='red' size='-2'>
            <?
                if($campos[$i]['status_top'] == 1) {
                    echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font>";
                }else if($campos[$i]['status_top'] == 2) {
                    echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font>";
                }
                echo '<b>';
                if($campos[$i]['operacao_custo'] == 0) {
                    echo '<br>I';
                    //Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                    if($campos[$i]['operacao_custo_sub'] == 0) {
                        echo '-I';
                    }else if($campos[$i]['operacao_custo_sub'] == 1) {
                        echo '-R';
                    }else {
                        echo '-';
                    }
                }else if($campos[$i]['operacao_custo'] == 1) {
                    echo '<br>R';
                }else {
                    echo ' -';
                }
                echo '<br/>'.number_format($campos[$i]['mmv'], 1, ',', '.');
                echo '<br/>'.number_format($retorno_pas_atrelados['total_mmv_pas_atrelados'], 2, ',', '.');
            ?>
            </font>
        </td>
        <td>
        <?
            //2) Busco o Custo desse PA na mesma Operação de Custo do PA ...
            $sql = "SELECT f.nome, pac.id_produto_acabado_custo, pac.qtde_lote, pac.peca_corte, SUBSTRING(pac.data_sys, 1, 10) AS data_sys, DATE_FORMAT(SUBSTRING(pac.data_sys, 1, 10), '%d/%m/%Y') AS data_atualizacao 
                    FROM `produtos_acabados_custos` pac 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = pac.`id_funcionario` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `operacao_custo` = '".$campos[$i]['operacao_custo']."' LIMIT 1 ";
            $campos_pa_custo = bancos::sql($sql);
            echo 'Lote = '.$campos_pa_custo[0]['qtde_lote'].' p/ '.number_format($campos_pa_custo[0]['qtde_lote'] / $retorno_pas_atrelados['total_mmv_pas_atrelados'], 1, ',', '.').' MMV Tot';
            /*A partir dessa Data 01/01/2014 nós começamos a corrigir os Lotes dos Custos levando em Conta a 
            Taxa Financeira de Estocagem ...*/
            $color = ($campos_pa_custo[0]['data_sys'] < '2014-01-01') ? 'red' : '';
            //Aqui eu apresento dados de Data e de quem foi o último que fez a Alteração no Custo desse PA ...
            echo '<br/><font color="'.$color.'"><b>'.$campos_pa_custo[0]['nome'].' - '.$campos_pa_custo[0]['data_atualizacao'].'</b></font>';
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['qtde_promocional_simulativa'] == 0) {//Se a Qtde for Zerada, então sugere a ...
                //Qtde de Peças por Embalagem do Produto Acabado ...
                $sql = "SELECT ppe.pecas_por_emb 
                        FROM `pas_vs_pis_embs` ppe 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ppe.id_produto_acabado AND pa.operacao_custo = '".$campos[$i]['operacao_custo']."' 
                        WHERE ppe.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' 
                        AND ppe.embalagem_default = '1' LIMIT 1 ";
                $campos_pcs_embalagem           = bancos::sql($sql);
                $qtde_promocional_simulativa    = (count($campos_pcs_embalagem) == 1) ? intval($campos_pcs_embalagem[0]['pecas_por_emb']) : 1;
            }else {
                $qtde_promocional_simulativa    = $campos[$i]['qtde_promocional_simulativa'];
            }
        ?>
                <input type='text' name="txt_qtde_promocional_novo[]" id="txt_qtde_promocional_novo<?=$i;?>" value="<?=$qtde_promocional_simulativa;?>" maxlength="4" size="4" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) this.value = ''" class='caixadetexto'>
        </td>
        <td>
                <img src="../../../../../imagem/seta_esquerda.gif" title="Copiar Valor" alt="Copiar Valor" border="0" onClick="copiar_qtde_promocional_a('<?=$i;?>')">
                <input type='text' name="txt_qtde_promocional_atual[]" id="txt_qtde_promocional_atual<?=$i;?>" value="<?=$campos[$i]['qtde_promocional'];?>" maxlength="4" size="4" class='textdisabled' disabled>
        </td>
        <td>
                <input type='text' name="txt_preco_promocional_novo[]" id="txt_preco_promocional_novo<?=$i;?>" value="<?=number_format($campos[$i]['preco_promocional_simulativa'], 2, ',', '.');?>" maxlength="7" size="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_promocional_a_dif('<?=$i;?>')" class='caixadetexto'>
        </td>
        <td>
                <input type='text' name="txt_preco_promocional_atual[]" id="txt_preco_promocional_atual<?=$i;?>" value="<?=number_format($campos[$i]['preco_promocional'], 2, ',', '.');?>" maxlength="7" size="6" class='textdisabled' disabled>
        </td>
        <td>
                <?
                        //Fórmula da Diferença em Percentagem, tem essa verificação para não dar erro de divisão por Zero
                        if($campos[$i]['preco_promocional'] != 0) $preco_promocional_dif_perc = ($campos[$i]['preco_promocional_simulativa'] / $campos[$i]['preco_promocional'] - 1) * 100;
                ?>
                <input type='text' name="txt_preco_promocional_dif_perc[]" id="txt_preco_promocional_dif_perc<?=$i;?>" value="<?=number_format($preco_promocional_dif_perc, 1, ',', '.');?>" maxlength="5" size="5" class='textdisabled' disabled> 
        </td>
        <td>
        <?
//Utilizada em alguns campos + abaixo ...
                $preco_margem_lucro_zero = $preco_maximo_custo_fat_rs / ($fator_margem_lucro_loop / $fator_desc_max_vendas);
//Aqui eu faço o cálculo quando carregar a Tela no início ...
                if($campos[$i]['preco_promocional'] != '' && $campos[$i]['preco_promocional'] != '0.00') {
                    $margem_lucro_novo_a 	= number_format((($campos[$i]['preco_promocional_simulativa'] / $preco_margem_lucro_zero) - 1) * 100, 2, ',', '.');
                    $margem_lucro_atual_a 	= number_format((($campos[$i]['preco_promocional'] / $preco_margem_lucro_zero) - 1) * 100, 2, ',', '.');
                }else {
                    $margem_lucro_novo_a 	= '';
                    $margem_lucro_atual_a 	= '';
                }
        ?>
                <input type='text' name="txt_margem_lucro_novo_a[]" id="txt_margem_lucro_novo_a<?=$i;?>" value="<?=$margem_lucro_novo_a;?>" maxlength="7" size="7" class='textdisabled' disabled>
        </td>
        <td>
                <input type='text' name="txt_margem_lucro_atual_a[]" id="txt_margem_lucro_atual_a<?=$i;?>" value="<?=$margem_lucro_atual_a;?>" maxlength="7" size="7" class='textdisabled' disabled>
        </td>
        <td>
        <?
                if($campos[$i]['qtde_promocional_simulativa'] == 0) {//Se a Qtde for Zerada, então sugere a ...
                    //Qtde de Peças por Embalagem do Produto Acabado ...
                    $sql = "SELECT ppe.pecas_por_emb 
                            FROM `pas_vs_pis_embs` ppe 
                            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ppe.id_produto_acabado AND pa.operacao_custo = '".$campos[$i]['operacao_custo']."' 
                            WHERE ppe.id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' 
                            AND ppe.embalagem_default = '1' LIMIT 1 ";
                    $campos_pcs_embalagem 		= bancos::sql($sql);
                    $qtde_promocional_simulativa_b 	= (count($campos_pcs_embalagem) == 1) ? intval($campos_pcs_embalagem[0]['pecas_por_emb']) : 1;
                }else {
                    $qtde_promocional_simulativa_b 	= $campos[$i]['qtde_promocional_simulativa_b'];
                }
        ?>
            <input type='text' name="txt_qtde_promocional_novo_b[]" id="txt_qtde_promocional_novo_b<?=$i;?>" value="<?=$qtde_promocional_simulativa_b;?>" maxlength="4" size="4" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) this.value = ''" class='caixadetexto'>
        </td>
        <td>
                <img src="../../../../../imagem/seta_esquerda.gif" title="Copiar Valor" alt="Copiar Valor" border="0" onClick="copiar_qtde_promocional_b('<?=$i;?>')">
                <input type='text' name="txt_qtde_promocional_atual_b[]" id="txt_qtde_promocional_atual_b<?=$i;?>" value="<?=$campos[$i]['qtde_promocional_b'];?>" maxlength="4" size="4" class='textdisabled' disabled>
        </td>
        <td>
        <?
                if($campos[$i]['preco_promocional_simulativa'] == 0 || $campos[$i]['preco_promocional_simulativa_b'] == 0) {
                        echo '-';
                }else {
                        echo number_format(($campos[$i]['preco_promocional_simulativa'] / $campos[$i]['preco_promocional_simulativa_b'] - 1) * 100, 1, ',', '.');
                }
        ?>
        </td>
        <td bgcolor='gray'>
        <?
            //$preco_promocional_ideal_b = $campos[$i]['preco_promocional_simulativa_b'];//De início essa var é o Pço B ...
            //Se o Novo Preço B for Maior do que o Preço Atual que está com 20% de Desconto em R$ ...
            if($campos[$i]['preco_promocional_simulativa_b'] >= $preco_liq_fat_atual_rs)              $preco_promocional_ideal_b = $campos[$i]['preco_promocional_simulativa_b'];
            if($campos[$i]['preco_promocional_simulativa_b'] > $preco_liq_fat_atual_rs)                 $style_preco_liq_fat_atual_rs = 'background:red; color:white';
            if($campos[$i]['preco_promocional_simulativa_b'] <= $preco_custo_ml_min_promocional)        $preco_promocional_ideal_b = ($preco_custo_ml_min_promocional);
        
            /*****************************************************************************************************/
            /***********************************Nova Logística Preço Promocional**********************************/
            /*****************************************************************************************************/
            $preco_custo_ml_min_promocional = ($preco_custo_ml_min_rs * $fator_taxa_financeira_vendas);
            
            /*$valores                        = intermodular::calculo_estoque_queima_pas_atrelados($campos[$i]['id_produto_acabado']);
            $estoque_queima                 = $valores['total_eq_pas_atrelados'];*/
            
            $preco_excesso_estoque          = ($estoque_queima > 0) ? vendas::calcular_preco_de_queima_pa($campos[$i]['id_produto_acabado']) : 0;
            $style_preco_liq_fat_atual_rs   = '';//O padrão do Layout é Vazio ...

            if($estoque_queima > 0) {
                $preco_medio_promocional    = ($preco_custo_ml_min_promocional + $preco_excesso_estoque) / 2;
                $preco_minimo_promocional   = min($preco_medio_promocional, $preco_custo_ml_min_promocional);
            }else {
                $preco_minimo_promocional   = $preco_custo_ml_min_promocional;
            }
            if($preco_minimo_promocional > $preco_liq_fat_atual_rs) {
                $preco_promocional_ideal_b = 0;
                echo '<font color="orange"><b>S/ PROMO</b></font>';
            }else {
                $preco_promocional_ideal_b = $preco_minimo_promocional;
            }
            /*****************************************************************************************************/
        ?>
            <input type='text' name="txt_preco_promocional_ideal_b[]" id="txt_preco_promocional_ideal_b<?=$i;?>" value="<?=number_format($preco_promocional_ideal_b, 2, ',', '.');?>" maxlength="7" size="6" class='textdisabled' disabled>
            <img src = '../../../../../imagem/seta_direita.gif' title='Copiar Valor' alt='Copiar Valor' border='0' onclick="copiar_preco_ideal_para_preco_b('<?=$i;?>')">
        </td>
        <td>
            <input type='text' name="txt_preco_promocional_novo_b[]" id="txt_preco_promocional_novo_b<?=$i;?>" value="<?=number_format($campos[$i]['preco_promocional_simulativa_b'], 2, ',', '.');?>" maxlength="7" size="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_promocional_b_dif('<?=$i;?>')" class='caixadetexto'>
        </td>
        <td bgcolor='gray'>
            <input type='text' name="txt_preco_promocional_atual_b[]" id="txt_preco_promocional_atual_b<?=$i;?>" value="<?=number_format($campos[$i]['preco_promocional_b'], 2, ',', '.');?>" maxlength="7" size="6" class='textdisabled' disabled>
        </td>
        <td bgcolor='gray'>
        <?
            //Fórmula da Diferença em Percentagem, tem essa verificação para não dar erro de divisão por Zero
            if($campos[$i]['preco_promocional_b'] != 0) $preco_promocional_b_dif_perc = ($campos[$i]['preco_promocional_simulativa_b'] / $campos[$i]['preco_promocional_b'] - 1) * 100;
        ?>
            <input type='text' name="txt_preco_promocional_b_dif_perc[]" id="txt_preco_promocional_b_dif_perc<?=$i;?>" value="<?=number_format($preco_promocional_b_dif_perc, 1, ',', '.');?>" maxlength="5" size="5" class='textdisabled' disabled>
        </td>
        <td bgcolor='gray'>
            <input type='text' name="txt_margem_lucro_novo_b[]" id="txt_margem_lucro_novo_b<?=$i;?>" value="<?=$margem_lucro_novo_b;?>" maxlength="7" size="7" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_margem_lucro_atual_b[]" id="txt_margem_lucro_atual_b<?=$i;?>" value="<?=$margem_lucro_atual_b;?>" maxlength="7" size="7" class='textdisabled' disabled>
        </td>
        <td>
            <font title='<?=$campos_concorrentes[0]['nome'];?>' style='cursor:help'>
            <?
                if($campos_concorrentes[0]['preco_liquido'] != 0) {
                    echo number_format($campos_concorrentes[0]['preco_liquido'], 2, ',', '.').' <b>'.substr(strtoupper($campos_concorrentes[0]['nome']), 0, 4).'</b>';
                    if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'N') {
                        $rotulo_impostos1 = '<font color="red"><b> (c/IPI) </b></font>';
                    }else if($campos_concorrentes[0]['com_ipi'] == 'N' && $campos_concorrentes[0]['com_st'] == 'S') {
                        $rotulo_impostos1 = '<font color="red"><b> (c/ST) </b></font>';
                    }else if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'S') {
                        $rotulo_impostos1 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                    }else {
                        $rotulo_impostos1 = '';
                    }
                    echo $rotulo_impostos1;
                }
            ?>
            </font>
        </td>
        <td>
            <font title='<?=$campos_concorrentes[1]['nome'];?>' style='cursor:help'>
            <?
                if($campos_concorrentes[1]['preco_liquido'] != 0) {
                    echo number_format($campos_concorrentes[1]['preco_liquido'], 2, ',', '.').' <b>'.substr(strtoupper($campos_concorrentes[1]['nome']), 0, 4).'</b>';
                    if($campos_concorrentes[1]['com_ipi'] == 'S' && $campos_concorrentes[1]['com_st'] == 'N') {
                        $rotulo_impostos2 = '<font color="red"><b> (c/IPI) </b></font>';
                    }else if($campos_concorrentes[1]['com_ipi'] == 'N' && $campos_concorrentes[1]['com_st'] == 'S') {
                        $rotulo_impostos2 = '<font color="red"><b> (c/ST) </b></font>';
                    }else if($campos_concorrentes[1]['com_ipi'] == 'S' && $campos_concorrentes[1]['com_st'] == 'S') {
                        $rotulo_impostos2 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                    }else {
                        $rotulo_impostos2 = '';
                    }
                    echo $rotulo_impostos2;
                }
            ?>
            </font>
        </td>
        <td>
            <font title='<?=$campos_concorrentes[2]['nome'];?>' style='cursor:help'>
            <?
                if($campos_concorrentes[2]['preco_liquido'] != 0) {
                    echo number_format($campos_concorrentes[2]['preco_liquido'], 2, ',', '.').' <b>'.substr(strtoupper($campos_concorrentes[2]['nome']), 0, 4).'</b>';
                    if($campos_concorrentes[2]['com_ipi'] == 'S' && $campos_concorrentes[2]['com_st'] == 'N') {
                        $rotulo_impostos3 = '<font color="red"><b> (c/IPI) </b></font>';
                    }else if($campos_concorrentes[2]['com_ipi'] == 'N' && $campos_concorrentes[2]['com_st'] == 'S') {
                        $rotulo_impostos3 = '<font color="red"><b> (c/ST) </b></font>';
                    }else if($campos_concorrentes[2]['com_ipi'] == 'S' && $campos_concorrentes[2]['com_st'] == 'S') {
                        $rotulo_impostos3 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                    }else {
                        $rotulo_impostos3 = '';
                    }
                    echo $rotulo_impostos3;
                }
            ?>
            </font>
        </td>
        <td>
            <font title='Gerado dentro do relatório de Estoque ou Produção' style='cursor:help'>
                MLMG=<?=number_format($campos[$i]['mlmg'], 1, ',', '.');?>
            </font>
            <input type='text' name="txt_margem_lucro_minima[]" id="txt_margem_lucro_minima<?=$i;?>" value="<?=number_format($margem_lucro_minima_corr, 2, ',', '.');?>" maxlength="7" size="6" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_aumento_est_custo_lista_nova[]" id="txt_aumento_est_custo_lista_nova<?=$i;?>" value="<?=number_format($campos[$i]['perc_estimativa_custo'], 1, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '1', '1', event);copiar_perc_estimativa_custo_geral('<?=$i;?>')" maxlength="7" size="6" class='caixadetexto'>
        </td>
        <td>
        <?
            $printar    = ($campos[$i]['status_custo'] == 1) ? number_format($preco_maximo_custo_fat_rs, 2, ',', '.') : 'Orçar';
        ?>
            <input type='text' name="txt_preco_max_custo_fat_rs[]" id="txt_preco_max_custo_fat_rs<?=$i;?>" value="<?=$printar;?>" maxlength="7" size="6" class='textdisabled' disabled>
        </td>
        <td bgcolor='gray'>
            <?
                if($estoque_queima > 0) {//Se existir Excesso de Estoque, mostro a figura abaixo ...
            ?>
            <img src = '../../../../../imagem/queima_estoque.png' title='Excesso de Estoque' alt='Excesso de Estoque' border='0'>
            EE=<?=number_format($estoque_queima, 0, ',', '.');?>
            <?
                    echo '<font title="Preço Excedente p/ 60 ddl" style="cursor:help">R$ '.number_format($preco_excesso_estoque, 2, ',', '.');
                }
            ?>
            <input type='text' name="txt_preco_custo_ml_min_rs[]" id="txt_preco_custo_ml_min_rs<?=$i;?>" value="<?=number_format($preco_custo_ml_min_rs, 2, ',', '.');?>" maxlength="7" size="6" class='textdisabled' disabled>
            <img src = '../../../../../imagem/seta_direita.gif' title='Copiar Valor' alt='Copiar Valor' border='0' onclick="copiar('<?=$i;?>')">
        </td>
        <td>
        <?
            $preco_liq_fat_novo_rs = round($campos[$i]['preco_unitario_simulativa'] * (100 - $campos[$i]['desc_a_lista_nova']) / 100 * (100 - $campos[$i]['desc_b_lista_nova']) / 100 * (100 + $campos[$i]['acrescimo_lista_nova']) / 100 * $fator_desc_max_vendas, 2);
            echo '<font color="darkblue"><b>Aum.'.round(($preco_custo_ml_min_rs / $preco_liq_fat_novo_rs - 1) * 100, 1).' %</b></font><br>';
            echo '<font color="darkgreen"><b>Desc.A Ideal '.round((1 - ($preco_custo_ml_min_rs / $preco_liq_fat_novo_rs * (1 - $campos[$i]['desc_a_lista_nova'] / 100))) * 100, 1).' %</b></font>';
        ?>
            <input type='text' name="txt_preco_liq_fat_novo_rs[]" id="txt_preco_liq_fat_novo_rs<?=$i;?>" value="<?=number_format($preco_liq_fat_novo_rs, 2, ',', '.');?>" maxlength="7" size="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_liq_fat_20_desc_dif('<?=$i;?>');calcular_novo_desconto_a()" class='caixadetexto'>
            <?='<br>Desc.Novo-'.intval($campos[$i]['desc_a_lista_nova']).'+'.intval($campos[$i]['desc_b_lista_nova']).'+Ac.'.intval($campos[$i]['acrescimo_lista_nova']).'%';?>
        </td>
        <td bgcolor='gray'>
            <input type='text' name="txt_preco_liq_fat_atual_rs[]" id="txt_preco_liq_fat_atual_rs<?=$i;?>" value="<?=number_format($preco_liq_fat_atual_rs, 2, ',', '.');?>" maxlength="7" size="6" class='textdisabled' style="<?=$style_preco_liq_fat_atual_rs;?>" disabled>
            <font color='#FFFFFF'>
                <?='<br>Desc.Atual-'.intval($campos[$i]['desc_base_a_nac']).'+'.intval($campos[$i]['desc_base_b_nac']).'+Ac.'.intval($campos[$i]['acrescimo_base_nac']).'%';?>
            </font>
        </td>
        <td>
        <?
            //Fórmula da Diferença em Percentagem, tem essa verificação para não dar erro de divisão por Zero ...
            if($preco_liq_fat_atual_rs != 0) $preco_liq_dif_perc = ($preco_liq_fat_novo_rs / $preco_liq_fat_atual_rs - 1) * 100;
        ?>
            <input type='text' name="txt_preco_liq_dif_perc[]" id="txt_preco_liq_dif_perc<?=$i;?>" value="<?=number_format($preco_liq_dif_perc, 1, ',', '.');?>" maxlength="7" size="6" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_preco_bruto_fat_novo_rs[]" id="txt_preco_bruto_fat_novo_rs<?=$i;?>" value="<?=number_format($campos[$i]['preco_unitario_simulativa'], 2, ',', '.');?>" maxlength="7" size="6" class='textdisabled' disabled>
        </td>
        <td>
            <?$total_venda_novo_rs = round($preco_liq_fat_novo_rs * round($campos[$i]['mmv'], 1), 2);?>
            <input type='text' name="txt_total_venda_novo_rs[]" id="txt_total_venda_novo_rs<?=$i;?>" value="<?=number_format($total_venda_novo_rs, 2, ',', '.');?>" maxlength="15" size="13" class='textdisabled' disabled>
        </td>
        <td>
            <?$total_venda_atual_rs = round($preco_liq_fat_atual_rs * round($campos[$i]['mmv'], 1), 2);?>
            <input type='text' name="txt_total_venda_atual_rs[]" id="txt_total_venda_atual_rs<?=$i;?>" value="<?=number_format($total_venda_atual_rs, 2, ',', '.');?>" maxlength="15" size="13" class='textdisabled' disabled>
            <!--***********************************Controles de Tela***********************************-->
            <input type='hidden' name="hdd_preco_max_custo_fat_rs[]" id="hdd_preco_max_custo_fat_rs<?=$i;?>" value="<?=number_format($preco_maximo_custo_fat_rs_sem_perc, 2, ',', '.');?>" maxlength="7" size="6" class='textdisabled' disabled>
            <input type='hidden' name="txt_fator_margem_lucro[]" value="<?=$fator_margem_lucro_loop;?>">
            <input type='hidden' name="operacao_custo[]" value="<?=$campos[$i]['operacao_custo'];?>">
            <input type='hidden' name="id_produto_acabado[]" value="<?=$campos[$i]['id_produto_acabado'];?>">
            <input type='hidden' name="hdd_preco_margem_lucro_zero[]" id="hdd_preco_margem_lucro_zero<?=$i;?>" value="<?=$preco_margem_lucro_zero;?>">
            <input type='hidden' name="hdd_mmv[]" id="hdd_mmv<?=$i;?>" value="<?=number_format($campos[$i]['mmv'], 1, ',', '.');?>">
            <input type='hidden' name="hdd_preco_promocional_novo_b_inicial[]" id="hdd_preco_promocional_novo_b_inicial<?=$i;?>" value="<?=number_format($campos[$i]['preco_promocional_simulativa_b'], 2, ',', '.');?>">
            <input type='hidden' name="hdd_estoque_queima[]" id="hdd_estoque_queima<?=$i;?>" value="<?=$estoque_queima;?>" maxlength='10' size='11' class='textdisabled' disabled>
            <input type='hidden' name="hdd_preco_excesso_estoque[]" id="hdd_preco_excesso_estoque<?=$i;?>" value="<?=$preco_excesso_estoque;?>" maxlength='10' size='11' class='textdisabled' disabled>
            <!--***********************************Campos Lista Nova***********************************-->
            <input type='hidden' name="hdd_desc_a_lista_nova[]" id="hdd_desc_a_lista_nova<?=$i;?>" value="<?=number_format($campos[$i]['desc_a_lista_nova'], 2, ',', '.');?>">
            <input type='hidden' name="hdd_desc_b_lista_nova[]" id="hdd_desc_b_lista_nova<?=$i;?>" value="<?=number_format($campos[$i]['desc_b_lista_nova'], 2, ',', '.');?>">
            <input type='hidden' name="hdd_acrescimo_lista_nova[]" id="hdd_acrescimo_lista_nova<?=$i;?>" value="<?=number_format($campos[$i]['acrescimo_lista_nova'], 2, ',', '.');?>">
        </td>
    </tr>
<?
            /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
            encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
            da Biblioteca de Custos, na função pas_atrelados ...*/
            unset($vetor_pas_atrelados);

            $total_geral_venda_novo_rs+= $total_venda_novo_rs;
            $total_geral_venda_atual_rs+= $total_venda_atual_rs;
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='23'>
            <input type='button' name='cmd_incrementar_percentagem' value='Incrementar %' title='Incrementar %' onclick="html5Lightbox.showLightbox(7, 'incrementar_percentagem.php')" style='color:red' class='botao'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'lista_preco_nacional.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');calcular_geral()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class='botao'>
        </td>
        <td colspan='3' align='right'>
            Aumento Ideal ->
        </td>
        <td colspan='2' align='left'>
            <?
                if(!empty($cmb_grupo_pa_vs_empresa_divisao) && $cmb_grupo_pa_vs_empresa_divisao != '%') {//Essa cálculo só irá existir se essa combo tiver alguma opção preenchida ...
                    $aumento_ideal = round((($total_geral_venda_novo_rs / $total_geral_venda_atual_rs - 1) * 100), 2);
                }else {
                    $aumento_ideal = 'INDEFINIDO';
                }
            ?>
            <input type='text' name="txt_aumento_ideal" id="txt_aumento_ideal" value="<?=number_format($aumento_ideal, 1, ',', '.');?>" maxlength="15" size="13" class='textdisabled' disabled>
        </td>
        <td colspan='3' align='right'>
            Novo Desc. A Ideal ->
        </td>
        <td align='left'>
            <?
                if(!empty($cmb_grupo_pa_vs_empresa_divisao) && $cmb_grupo_pa_vs_empresa_divisao != '%') {//Essa cálculo só irá existir se essa combo tiver alguma opção preenchida ...
                    $novo_desconto_a_ideal = round((1 - (100 - intval($campos[0]['desc_base_a_nac'])) / 100 * (1 + $aumento_ideal / 100)) * 100, 2);
                }else {
                    $novo_desconto_a_ideal = 'INDEFINIDO';
                }
            ?>
            <input type='text' name='txt_novo_desconto_a_ideal' id='txt_novo_desconto_a_ideal' value="<?=number_format($novo_desconto_a_ideal, 1, ',', '.');?>" maxlength='15' size='13' class='textdisabled' disabled>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    //Aqui é a parte de atualização dos Produtos Acabados
    for($i = 0; $i < count($_POST['id_produto_acabado']); $i++) {
        $sql = "UPDATE `produtos_acabados` SET `perc_estimativa_custo` = '".$_POST['txt_aumento_est_custo_lista_nova'][$i]."', `preco_unitario_simulativa` = '".$_POST['txt_preco_bruto_fat_novo_rs'][$i]."', `qtde_promocional_simulativa` = '".$_POST['txt_qtde_promocional_novo'][$i]."', `preco_promocional_simulativa` = '".$_POST['txt_preco_promocional_novo'][$i]."', `qtde_promocional_simulativa_b` = '".$_POST['txt_qtde_promocional_novo_b'][$i]."', `preco_promocional_simulativa_b` = '".$_POST['txt_preco_promocional_novo_b'][$i]."' WHERE `id_produto_acabado` = '".$_POST['id_produto_acabado'][$i]."' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'lista_preco_nacional.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Nova Lista de Preço Nacional ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function gerar_relatorio() {
    document.form.target = 'POP'
    document.form.action = 'rel_lista_preco.php'
    nova_janela('rel_lista_preco.php', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function lista_preco_eletronica() {
    document.form.target = 'POP'
    document.form.action = 'lista_preco_eletronica.php'
    nova_janela('lista_preco_eletronica.php', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function submeter() {
    document.form.target = '_self'
    document.form.action = '<?=$PHP_SELF.'?passo=1';?>'
}
</Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Nova Lista de Preço Nacional
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name="txt_referencia" title="Digite a Referência" size="40" maxlength="35" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação" size="40" maxlength="35" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>Grupo P.A vs Empresa Divisão</b>
            </font>
        </td>
        <td>
            <select name="cmb_grupo_pa_vs_empresa_divisao" title="Consultar Produto Acabado por: Grupo P.A vs Empresa Divisão" class="combo">
            <?
                //Aqui traz todos os grupos vs Empresa Divisão com exceção dos que são pertencentes a Família de Componentes
                $sql = "SELECT ged.id_gpa_vs_emp_div, CONCAT(gpa.nome, ' (', ed.razaosocial, ')') AS grupo_vs_empresa_divisao 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`ativo` = '1' AND gpa.`id_familia` <> '23' 
                        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao AND ed.`ativo` = '1' 
                        ORDER BY gpa.nome, ed.razaosocial ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <font color='red'>
                <b>(ESTE COMBO FUNCIONA INDEPENDENTE DAS OUTRAS OPÇÕES DE FILTRO)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa Divisão
        </td>
        <td>
            <select name="cmb_empresa_divisao" title="Consultar Produto Acabado por: Empresa Divisão" class="combo">
            <?
                $sql = "Select id_empresa_divisao, razaosocial 
                        from empresas_divisoes 
                        where ativo = 1 order by razaosocial ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo P.A.
        </td>
        <td>
            <select name="cmb_grupo_pa" title="Consultar Produto Acabado por: Grupo P.A." class="combo">
            <?
//Aqui traz todos os grupos com exceção dos que são pertencentes a Família de Componentes
                $sql = "Select id_grupo_pa, nome 
                        from grupos_pas 
                        where ativo = 1 
                        and id_familia <> 23 order by nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Família
        </td>
        <td>
            <select name="cmb_familia" title="Consultar Produto Acabado por: Família" class="combo">
            <?
                $sql = "Select id_familia, nome 
                        from familias 
                        where ativo = 1 order by nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Ordenar por
        </td>
        <td colspan='2'>
            <select name="cmb_order_by" title="Ordernar" class="combo">
                <option value="pa.referencia" selected>Referência</option>
                <option value="pa.discriminacao">Discriminação</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_novo_preco_promocional' value='1' title='Novo Preço Promocional A ou B <> 0' id='novo_preco_promocional' class='checkbox'>
            <label for='novo_preco_promocional'>Novo Preço Promocional A ou B <> 0</label>
        </td>
    </tr>
    <tr class='linhanormal'>
            <td colspan='2'>
                    <input type='checkbox' name='chkt_preco_promocional_atual' value='1' title='Preço Promocional Atual A ou B <> 0' id='preco_promocional_atual' class='checkbox'>
                    <label for='preco_promocional_atual'>Preço Promocional Atual A ou B <> 0</label>
            </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_todos_produtos_zerados' value='1' title='Todos os Produtos Zerados' id='todos' class='checkbox'>
            <label for='todos'>Todos os Produtos Zerados</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'lista_preco.php'" class='botao'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' onclick="submeter()" class="botao">
            <input type='button' name='cmd_lista_preco_eletronica' value='Lista de Preço Eletrônica' title='Lista de Preço Eletrônica' onclick='lista_preco_eletronica()' class='botao'>
            <input type='button' name='cmd_relatorio' value='Relatório' title='Relatório' style="color:black" onclick="gerar_relatorio()" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* Não exibe P.A(s) que são ESP, que são pertencentes a Família de Componentes e 
que estão com a marcação (ÑP) - Não Produzir.

Como fazer uma Nova Lista de Preço: 

1) Uma nova lista Impressa (Os preços brutos serão alterados e os descontos serão unificados)

.............................................................................................

2) Mudança dos Descontos de Lista (Os preços brutos serão mantidos e os descontos de Grupo vs Divisão serão alterados)

A) Verificar as Margens de Lucro Mínimas e MLMG que equivale a ML Mínima da Venda dos últimos meses usando o link Lápis (Alterar Alíquotas da Lista Nova)

B) Clicar na Seta do Campo Pço.Custo ML.Min Atual R$ para verificar o aumento médio e o <b>"Novo Desconto A"</b> ideal que ficam no rodapé da página.


Como fazer uma Nova Promoção: 

* O promo B ideal 60 ddl R$ proposto é o Pço.Custo ML.Min nova R$ acrescido da taxa financeira de 
vendas (pois a promoção é p/ 60 ddl).

* Caso o produto tenha "Estoque Excedente", calculamos uma média entre o Pço de Excesso e o 
Pço.Custo ML.Min nova R$, acrescidos da taxa financeira de vendas.

* Verificamos o valor mínimo dos 2 preços acima e comparamos com o P. Liq + 20% Fat atual.
Caso esse preço "P. Liq + 20% Fat atual" seja maior, o sistema irá sugerir s/ promoção.
</pre>