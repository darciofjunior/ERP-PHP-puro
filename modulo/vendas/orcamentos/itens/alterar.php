<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) ATUALIZADO(S) COM SUCESSO.</font>";

/**********************************************************************************************************/
/***********************************************Interpola��o***********************************************/
/**********************************************************************************************************/
/*Na data do dia 21/11/2013 trabalh�vamos com uma interpola��o +/- 5x a Qtde e mudamos para +/- 2x porque que gerava 
muito erro de Custo ...*/
$interpolacao = intval(genericas::variavel(60));
/**********************************************************************************************************/

/*********************Consulta R�pida*********************/
if(!empty($_GET['txt_produto_acabado'])) {
    //Aqui eu tento achar o id do PA ...
    $sql = "SELECT `id_produto_acabado` 
            FROM `produtos_acabados` 
            WHERE (`referencia` LIKE '$_GET[txt_produto_acabado]' OR `discriminacao` LIKE '$_GET[txt_produto_acabado]') LIMIT 1 ";
    $campos_pa 	= bancos::sql($sql);
    $id_pa      = $campos_pa[0]['id_produto_acabado'];
    //Aqui eu busco todos os Itens do Or�amento que foi passado por par�metro ...
    $sql = "SELECT `id_produto_acabado` 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' ORDER BY id_orcamento_venda_item ";
    $campos_orcamento = bancos::sql($sql);
    $linhas_orcamento = count($campos_orcamento);
    for($i = 0; $i < $linhas_orcamento; $i++) {
        //Aqui eu verifico se o PA que o usu�rio digitou est� relacionado no Or�amento que o usu�rio est� trabalhando ...
        if($id_pa == $campos_orcamento[$i]['id_produto_acabado']) {
            $posicao = ($i + 1);
            break;
        }
    }
    if($posicao == 0) echo '<Script Language = "JavaScript">alert(\'ITEM N�O ENCONTRADO !\')</Script>';
}
/*********************************************************/
if(empty($posicao)) $posicao = 1;//Macete por causa da paginacao do Pop-UP ...

if($passo == 1) {
    $situacao_orcamento = vendas::situacao_orcamento($_POST['id_orcamento_venda']);
    if($situacao_orcamento == 'N') {//Or�amento Normal, pode estar sendo manipulado
        //Controle com a Parte de Promo��o ...
        if($_POST['cmb_promocao'] == 1) {//N�o ser� mais usado, aki � o modo antigo ...
            $promocao = 'S';
        }else if($_POST['cmb_promocao'] == 'A') {//Modo Novo
            $promocao = 'A';
        }else if($_POST['cmb_promocao'] == 'B') {//Modo Novo
            $promocao = 'B';
        }else if($_POST['cmb_promocao'] == 'C') {//Modo Novo
            $promocao = 'C';
        }else {
            $promocao = 'N';
        }
/*****************************************************************************************/
        //Aqui eu verifico se o Item realmente possui a Marca��o de Ignorar Lote M�nimo do Grupo Faixa Or��vel ...
        $sql = "SELECT ovi.`qtde`, ovi.`ignorar_lote_minimo_do_grupo_faixa_orcavel`, pa.`referencia` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                WHERE ovi.`id_orcamento_venda_item` = '$_POST[id_orcamento_venda_item]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        /*Enquanto estiver desmarcado o campo "ignorar_lote_minimo_do_grupo_faixa_orcavel", o usu�rio pode 
        alterar a Qtde do Or�amento ...*/
        if($campos[0]['ignorar_lote_minimo_do_grupo_faixa_orcavel'] == 'N') $atualizar_qtde = " `qtde` = '$_POST[txt_quantidade]', ";

/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n�o tiver preenchidos  ...
/*******************************************************************************/
        $cmb_pa_substitutivo = (!empty($_POST[cmb_pa_substitutivo])) ? "'".$_POST[cmb_pa_substitutivo]."'" : 'NULL';
        
        $sql = "UPDATE `orcamentos_vendas_itens` SET `id_produto_acabado_discriminacao` = $cmb_pa_substitutivo, $atualizar_qtde `promocao` = '$promocao', `queima_estoque` = '$_POST[hdd_queima_estoque]', `desc_extra` = '$_POST[txt_desconto_porc_extra]', `acrescimo_extra` = '$_POST[txt_acrescimo_extra_porc]', `preco_liq_final` = '$_POST[txt_preco_liquido_final_rs]', `prazo_entrega` = '$_POST[cmb_prazo_entrega]' WHERE `id_orcamento_venda_item` = '$_POST[id_orcamento_venda_item]' LIMIT 1 ";
        bancos::sql($sql);
        /*Aqui eu gravo todas as Pend�ncias existentes de Or�amento p/ que o Representante dono
        do Cliente, possa resolver depois as futuras ocorr�ncias ...*/
        vendas::vendedores_pendencias($_POST['id_orcamento_venda']);//coloquei aqui se n�o ele cadastra os orcs, quando algu�m mexe no pop orc. ex.: Rival consulta
        /*S� chamo essa fun��o meio "pesada" quando o PA for ESP e houve mudan�a na Qtde do Orc porque isso interfere no 
        Pre�o L�q Fat em R$ por Pe�a ...*/
        if($campos[0]['referencia'] == 'ESP' && $campos[0]['qtde'] != $_POST['txt_quantidade']) vendas::calculo_preco_liq_final_item_orc($_POST['id_orcamento_venda_item'], 'S');
//Aqui eu atualizo a ML Est do Iem do Or�amento ...
        custos::margem_lucro_estimada($_POST['id_orcamento_venda_item']);
/*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
        vendas::calculo_ml_comissao_item_orc($_POST['id_orcamento_venda'], $_POST['id_orcamento_venda_item']);
?>
    <Script Language = 'JavaScript'>
        var hdd_ir_para_incluir = '<?=$_POST['hdd_ir_para_incluir'];?>'
        //Se o usu�rio clicar no bot�o de Incluir um Novo Item, o Sys 1� salva p/ depois redirecionar de tela ...
        if(hdd_ir_para_incluir == 1) window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/incluir_lote.php?id_orcamento_venda=<?=$_POST['id_orcamento_venda'];?>'
    </Script>
<?
        $valor = 1;
    }else {//Or�amento Congelado
?>
    <Script Language = 'JavaScript'>
        alert('OR�AMENTO CONGELADO !')
        var hdd_ir_para_incluir = '<?=$_POST['hdd_ir_para_incluir'];?>'
        //Se o usu�rio clicar no bot�o de Incluir um Novo Item, o Sys 1� salva p/ depois redirecionar de tela ...
        if(hdd_ir_para_incluir == 1) window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/incluir_lote.php?id_orcamento_venda=<?=$_POST['id_orcamento_venda'];?>'
    </Script>
<?
    }
}

/*Significa que o usu�rio desejou marcar o Fornecedor corrente da Tela como Default

Observa��o:
Aqui nessa combo eu armazeno o do id_fornecedor porque segundo o Roberto toda vez em que eu trocar o Lote 
M�nimo do Fornecedor, ent�o ... eu tamb�m tenho que trocar o Fornecedor Default deste PA ...*/
if(!empty($cmb_lote_minimo)) {
//Aqui eu busco qual � o id_pa do $id_orcamento_venda_item, pq vou precisar dele no outro select abaixo ...
    $sql = "SELECT id_produto_acabado 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_produto_acabado = $campos[0]['id_produto_acabado'];
//Todo esse procedimento � para buscar o id_fornecedor do PI que � "PIPA" e ser� default ...
    $sql = "SELECT fpi.id_fornecedor, pa.id_produto_insumo 
            FROM `produtos_acabados` pa 
            INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` AND fpi.id_fornecedor = '$cmb_lote_minimo' 
            WHERE pa.`id_produto_acabado` = '$id_produto_acabado' ";
    $campos = bancos::sql($sql);
    $id_fornecedor      = $campos[0]['id_fornecedor'];
    $id_produto_insumo  = $campos[0]['id_produto_insumo'];
//Atualiza��o do Novo Fornecedor Default na tabela de PI ...
    $sql = "UPDATE `produtos_insumos` SET `id_fornecedor_default` = '$id_fornecedor' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
    bancos::sql($sql);
}
/***************************Procedimento Normal da Tela***************************/

//Seleciona o id_orcamento_venda_item p/ passar na fun��o abaixo ...
$sql = "SELECT id_orcamento_venda_item 
        FROM `orcamentos_vendas_itens` 
        WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
        ORDER BY `id_orcamento_venda_item` ";
$campos                     = bancos::sql($sql, ($posicao - 1), $posicao);
$id_orcamento_venda_item    = $campos[0]['id_orcamento_venda_item'];

//Seleciona a qtde de itens que existe no or�amento
$sql = "SELECT COUNT(`id_orcamento_venda_item`) AS qtde_itens 
        FROM `orcamentos_vendas_itens` 
        WHERE `id_orcamento_venda` = '$id_orcamento_venda' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

//Seleciona os itens do or�amento ...
$sql = "SELECT c.`id_cliente`, c.`id_pais`, c.`id_uf`, c.`id_cliente_tipo`, ged.`margem_lucro_minima`, gpa.`id_grupo_pa`, 
        gpa.`id_familia`, gpa.`lote_min_producao_reais`, gpa.`prazo_entrega` AS prazo_entrega_divisao, 
        ov.`artigo_isencao`, ov.`nota_sgd`, DATE_FORMAT(ov.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
        ov.`prazo_a`, ov.`prazo_b`, ov.`prazo_c`, ov.`prazo_d`, ovi.*, pa.*, 
        pa.`observacao` AS observacao_produto, u.`sigla` 
        FROM `orcamentos_vendas` ov 
        INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        WHERE ovi.`id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
$campos                                     = bancos::sql($sql);
$id_cliente                                 = $campos[0]['id_cliente'];
$id_pais                                    = $campos[0]['id_pais'];
$id_uf                                      = $campos[0]['id_uf'];
$id_cliente_tipo                            = $campos[0]['id_cliente_tipo'];
$id_produto_acabado_discriminacao           = $campos[0]['id_produto_acabado_discriminacao'];
$id_representante                           = $campos[0]['id_representante'];
$artigo_isencao                             = $campos[0]['artigo_isencao'];
$nota_sgd                                   = $campos[0]['nota_sgd'];
$qtde                                       = $campos[0]['qtde'];
$preco_liq_fat                              = $campos[0]['preco_liq_fat'];
$desconto_cliente                           = $campos[0]['desc_cliente'];
$promocao                                   = $campos[0]['promocao'];
$queima_estoque                             = $campos[0]['queima_estoque'];
$ignorar_lote_minimo_do_grupo_faixa_orcavel = $campos[0]['ignorar_lote_minimo_do_grupo_faixa_orcavel'];
$desconto_extra                             = $campos[0]['desc_extra'];
$acrescimo_extra                            = $campos[0]['acrescimo_extra'];
$desc_sgd_icms                              = $campos[0]['desc_sgd_icms'];
$comissao_new                               = $campos[0]['comissao_new'];
$comissao_extra                             = $campos[0]['comissao_extra'];
$peso_por_pecas_kg                          = $campos[0]['peso_unitario'];
$id_produto_acabado                         = $campos[0]['id_produto_acabado'];
$referencia                                 = $campos[0]['referencia'];
$discriminacao                              = $campos[0]['discriminacao'];
$operacao_custo                             = $campos[0]['operacao_custo'];
$qtde_promocional                           = $campos[0]['qtde_promocional'];
$preco_promocional                          = $campos[0]['preco_promocional'];
$qtde_promocional_b                         = $campos[0]['qtde_promocional_b'];
$preco_promocional_b                        = $campos[0]['preco_promocional_b'];
$status_custo                               = $campos[0]['status_custo'];
$id_familia                                 = $campos[0]['id_familia'];
$prazo_entrega_divisao                      = $campos[0]['prazo_entrega_divisao'];
$lote_min_producao_reais                    = $campos[0]['lote_min_producao_reais'];
$preco_liq_final                            = $campos[0]['preco_liq_final'];
$prazo_entrega_item                         = $campos[0]['prazo_entrega'];
$prazo_entrega_tecnico                      = $campos[0]['prazo_entrega_tecnico'];
$margem_lucro                               = $campos[0]['margem_lucro'];
$margem_lucro_estimada                      = $campos[0]['margem_lucro_estimada'];
$iva                                        = $campos[0]['iva'];
$status                                     = $campos[0]['status'];
$sigla                                      = $campos[0]['sigla'];

if($campos[0]['prazo_d'] > 0) {
    $prazo_faturamento  = $campos[0]['prazo_a'].'/'.$campos[0]['prazo_b'].'/'.$campos[0]['prazo_c'].'/'.$campos[0]['prazo_d'];
}else if($campos[0]['prazo_c'] > 0) {
    $prazo_faturamento  = $campos[0]['prazo_a'].'/'.$campos[0]['prazo_b'].'/'.$campos[0]['prazo_c'];
}else if($campos[0]['prazo_b'] > 0) {
    $prazo_faturamento  = $campos[0]['prazo_a'].'/'.$campos[0]['prazo_b'];
}else {
    $prazo_faturamento  = ($campos[0]['prazo_a'] == 0) ? '� vista' : $campos[0]['prazo_a'];
}

$prazo_medio = intermodular::prazo_medio($campos[0]['prazo_a'], $campos[0]['prazo_b'], $campos[0]['prazo_c'], $campos[0]['prazo_d']);

//Aqui � a verifica��o do Tipo de Nota
if($nota_sgd == 'S') {
    $rotulo_sgd = ' - SGD';
}else {
    $rotulo_sgd = ' - NF';
//Somente quando a nota � do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
    if($campos[0]['finalidade'] == 'C') {
        $finalidade = 'CONSUMO';
    }else if($campos[0]['finalidade'] == 'I') {
        $finalidade = 'INDUSTRIALIZA��O';
    }else {
        $finalidade = 'REVENDA';
    }
    $rotulo_sgd.= '/'.$finalidade;
}
$prazo_faturamento.= $rotulo_sgd;
/**************************Produtos Especiais***********************************/
$preco_custo_lista = custos::lista_preco_vendas($id_produto_acabado);
/***************************Produtos Normais************************************/
if($operacao_custo == 0) {//Industrializado
    $sql = "SELECT qtde_lote, lote_minimo 
            FROM `produtos_acabados_custos` 
            WHERE `operacao_custo` = '0' 
            AND `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos_custo                       = bancos::sql($sql);
    $qtde_lote                          = $campos_custo[0]['qtde_lote'];
    $lote_minimo_ignora_faixa_orcavel   = $campos_custo[0]['lote_minimo'];
}
/**********************C�lculo da Taxa Financeira*************************/
$taxa_financeira_vendas         = genericas::variaveis('taxa_financeira_vendas');
$fator_tx_financ_diaria         = pow((1 + $taxa_financeira_vendas / 100), (1 / 30));
$fator_tx_financ_prazo_medio    = pow(($fator_tx_financ_diaria), ($prazo_medio));
$tx_financeira                  = (($fator_tx_financ_prazo_medio / (1 + $taxa_financeira_vendas / 100)) - 1) * 100;
/*************************************************************************/
//J� aproveito e utilizo a mesma vari�vel declarada um pouco + acima para o C�lculo + abaixo ...
$taxa_financeira_vendas         = $taxa_financeira_vendas / 100 + 1;

$total_indust   = custos::todas_etapas($id_produto_acabado, $operacao_custo, 1, $qtde);
$etapa4         = $GLOBALS['etapa4'];
$tipo_cliente   = ($id_pais == 31) ? 'N' : 'I';

$valor_fornecedor_default_revenda = custos::procurar_fornecedor_default_revenda($id_produto_acabado, 1, '', $tipo_cliente);
/*****************************************************************************/
//O tipo_moeda p/ parte de c�lculo do Orc ...
if($id_pais != 31) {//Significa que o Cliente � do Tipo Internacional
    $tipo_moeda = 'U$ ';
//Uso essa vari�vel l� em baixo no Lote M�nimo
    $dolar_dia = genericas::moeda_dia('dolar');
}else {//Significa que o Cliente � do Tipo Nacional
    $tipo_moeda = 'R$ ';
    $dolar_dia  = 1;
}
//Essa vari�vel � utilizada em um Script no fim desse arquivo para fazer valida��o
/******************Aproveito para pegar o pe�as / corte *********************/
$sql = "SELECT id_produto_acabado_custo 
        FROM `produtos_acabados_custos` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' 
        AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
$campos_custo = bancos::sql($sql);
if(count($campos_custo) == 1) {
    $id_produto_acabado_custo 	= $campos_custo[0]['id_produto_acabado_custo'];
}else {
    $id_produto_acabado_custo 	= 0;//P/ n�o dar erro de SQL ...
}
/***************************************/
//Esses SQL(s) aki v�o auxiliar na fun��o de JavaScript + abaixo
$sql = "SELECT COUNT(id_pac_maquina) total 
        FROM `pacs_vs_maquinas` 
        WHERE id_produto_acabado_custo = '".$id_produto_acabado_custo."' ";
$campos_4_etapa = bancos::sql($sql);
$qtde_4_etapa 	= $campos_4_etapa[0]['total'];

$sql = "SELECT COUNT(id_pac_pi_trat) total 
        FROM `pacs_vs_pis_trat` 
        WHERE `lote_minimo_fornecedor` = '1' 
        AND `id_produto_acabado_custo` = '".$id_produto_acabado_custo."' ";
$campos_5_etapa = bancos::sql($sql);
$qtde_5_etapa	= $campos_5_etapa[0]['total'];

//o Padr�o das Caixas de "Desconto Extra" e "Acr�scimo Extra" � de serem Habilitadas ...
$class 		= 'caixadetexto';
$disabled 	= '';

//Aqui eu verifico a qtde dispon�vel desse item em Estoque e a qtde dele em Produ��o ...
$estoque_produto    = estoque_acabado::qtde_estoque($id_produto_acabado);
$est_real           = $estoque_produto[0];
$qtde_producao      = $estoque_produto[2];
$qtde_disponivel    = $estoque_produto[3];
$racionado          = $estoque_produto[5];
$est_comprometido   = $estoque_produto[8];
$est_fornecedor     = $estoque_produto[12];
$est_porto          = $estoque_produto[13];

if($qtde_disponivel == '')  $qtde_disponivel = 0;//Se retornar nulo do banco
if($qtde_producao == '')    $qtde_producao = 0;//Se retornar nulo do banco
if($racionado == '')        $racionado = 0;//Se retornar nulo do banco

//Dados da UF do Cliente ...
$dados_produto      = intermodular::dados_impostos_pa($id_produto_acabado, $id_uf);
$classific_fiscal   = $dados_produto['classific_fiscal'];
$ipi                = $dados_produto['ipi'];
$icms               = $dados_produto['icms'];
$icms_intraestadual = $dados_produto['icms_intraestadual'];
$reducao            = $dados_produto['reducao'];

//Se o Or�amento estiver congelado ou existir algum Item que est� em Queima de Estoque, travo o Cabe�alho ...
$vetor_dados_gerais     = vendas::dados_gerais_orcamento($id_orcamento_venda);
$data_validade_orc      = str_replace('-', '', $vetor_dados_gerais['data_validade_orc']);
$data_atual             = date('Ymd');
?>
<html>
<head>
<title>.:: Alterar Itens do Or�amento N.�&nbsp;<?=$id_orcamento_venda;?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/pecas_por_embalagem.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar_item(event) {
    var posicao     = eval('<?=$posicao;?>')
    var qtde_itens  = eval('<?=$qtde_itens;?>')
    
    if(navigator.appName == 'Microsoft Internet Explorer') {
        if(event.keyCode == 13 || event.keyCode == 35) {//Se Enter ou End faz a Consulta.
            //Representa que o Usu�rio ainda n�o est� no �ltimo Item ...
            if(posicao < qtde_itens) {
                var resposta = confirm('DESEJA AVAN�AR P/ O PR�XIMO ITEM ?')
                if(resposta == true) posicao++
            }
            return validar(posicao, 1)
        }
    }else {
        if(event.which == 13 || event.which == 35) {//Se Enter ou End faz a Consulta.
            //Representa que o Usu�rio ainda n�o est� no �ltimo Item ...
            if(posicao < qtde_itens) {
                var resposta = confirm('DESEJA AVAN�AR P/ O PR�XIMO ITEM ?')
                if(resposta == true) posicao++
            }
            return validar(posicao, 1)
        }
    }
}

function validar(posicao, verificar) {
    var ignorar_lote_minimo_do_grupo_faixa_orcavel = '<?=$ignorar_lote_minimo_do_grupo_faixa_orcavel;?>'//Essa vari�vel diz se o Or�amento est� seguindo pelo L.M.
    //Se essa op��o de Lote M�nimo estiver marcada, ent�o n�o � poss�vel salvar dados do Item ...
    if(ignorar_lote_minimo_do_grupo_faixa_orcavel == 'S') {
        var qtde_gravada = eval('<?=$qtde;?>')
        if(document.form.txt_quantidade.value != qtde_gravada) {//Verifico se o user est� mudando a Qtde Gravada ...
            alert('PROIBIDO ALTERAR QUANTIDADE, PORQUE O ITEM EST� SEGUINDO O CAMINHO DE "IGNORAR LOTE M�NIMO DO GRUPO", CONTATAR DEPTO. T�CNICO !')
            /*Significa que essa fun��o foi chamada atrav�s do bot�o Salvar, e sendo assim eu for�o o Preenchimento
            desses campos ...*/
            if(typeof(verificar) != 'undefined') return false
        }
    }
    var status = eval('<?=$status;?>')//Essa aki � a vari�vel que diz a situa��o do Or�amento
    var taxa_financeira_vendas = eval('<?=genericas::variaveis('taxa_financeira_vendas');?>')
/*Logo no in�cio dessa Tela, eu j� Zero o Hidden do id_produto_acabado_consultado p/
n�o alastrar esse valor p/ os demais Itens quando eu mudo a Pagina��o ...*/
    document.form.id_produto_acabado_consultado.value = ''
/*Significa que este Or�amento ainda est� em Fechado, sendo assim eu fa�o a valida��o dos objetos
tanto pelo bot�o salvar como pelo link tamb�m*/
    if(status < 2) {
        if(!texto('form', 'txt_quantidade', '1', '1234567890,.', 'QUANTIDADE', '1')) {
            return false
        }
//Verifica o Valor da Quantidade
        var quantidade = eval(strtofloat(document.form.txt_quantidade.value))
        if(quantidade == 0) {
            alert('QUANTIDADE INV�LIDA !!!\n\nVALOR IGUAL A ZERO !')
            document.form.txt_quantidade.focus()
            document.form.txt_quantidade.select()
            return false
        }
/*Significa que essa fun��o foi chamada atrav�s do bot�o Salvar, e sendo assim eu for�o o Preenchimento
desses campos ...*/
        if(typeof(verificar) != 'undefined') {
            var preco_liq_final_rs = document.form.txt_preco_liquido_final_rs.value
            
            if(preco_liq_final_rs == 'Adequar a qtde or��vel / chamar Depto. T�cnico !') {//Ou seja Zero ...
                alert('PRE�O L�Q FINAL INV�LIDO !!!\n\nVALOR IGUAL A ZERO !')
                document.form.txt_preco_liq_final_desejado.focus()
                document.form.txt_preco_liq_final_desejado.select()
                return false
            }else {//Diferente de Zero ...
                preco_liq_final_rs = eval(strtofloat(preco_liq_final_rs))
            }
            
            if(preco_liq_final_rs < 0) {
                alert('PRE�O L�QUIDO FINAL EM <?=$tipo_moeda;?> INV�LIDO => '+document.form.txt_preco_liquido_final_rs.value+' !!!\nPRE�O L�QUIDO FINAL EM R$ MENOR DO QUE ZERO !')
                return false
            }
//Desc % Extra
            if(document.form.txt_desconto_porc_extra.value != '') {
                //Se existir promo��o e esta foi selecionada, posso ter desconto negativo ...
                if(typeof(document.form.cmb_promocao) == 'object' && document.form.cmb_promocao.value != '') {
                    if(!texto('form', 'txt_desconto_porc_extra', '1', '1234567890,.-', 'DESC % EXTRA', '2')) {
                        return false
                    }
                }else {//N�o existe promo��o, s� posso ter desconto positivo ...
                    if(!texto('form', 'txt_desconto_porc_extra', '1', '1234567890,.', 'DESC % EXTRA', '2')) {
                        return false
                    }
                }
            }
//Acr�scimo Extra %
            if(document.form.txt_acrescimo_extra_porc.value != '') {
                if(!texto('form', 'txt_acrescimo_extra_porc', '1', '1234567890,.-', 'ACR�SCIMO EXTRA %', '2')) {
                    return false
                }
                //var acrescimo_extra_porc = strtofloat(document.form.txt_acrescimo_extra_porc.value)
/*Se o Valor do Acr�scimo for menor do que a vari�vel "taxa_financeira_vendas" que est� 
definida no Sistema, trava o Sys ...

Obs: Aqui eu fa�o a compara��o invertendo o Sinal da Taxa Financeira de Vendas p/ que n�o 
n�o seje colocado um Valor Negativo t�o abaixo do que permitido ...*/
                /*if(acrescimo_extra_porc < (-taxa_financeira_vendas)) {
                    alert('ACR�SCIMO EXTRA INV�LIDO !!!\nACR�SCIMO EXTRA MENOR DO QUE '+'<?=number_format(genericas::variaveis('taxa_financeira_vendas'), 2, ',', '.');?>'+'% !')
                    document.form.txt_acrescimo_extra_porc.focus()
                    document.form.txt_acrescimo_extra_porc.select()
                    return false
                }*/
            }
//Prazo de Entrega
            if(!combo('form', 'cmb_prazo_entrega', '', 'SELECIONE O PRAZO DE ENTREGA !')) {
                return false
            }
        }
//Compara��o entre os Prazos de Entrega do Depto. T�cnico com o Prazo de Entrega do Vendedor ...
        var prazo_entrega_tecnico = eval('<?=(int)($prazo_entrega_tecnico);?>')
        var prazo_entrega = document.form.cmb_prazo_entrega.value
        if(prazo_entrega_tecnico > prazo_entrega) {
            var resposta = confirm('O PRAZO DE ENTREGA EST� MENOR DO QUE O PRAZO DE ENTREGA DO T�CNICO !\nDESEJA CONTINUAR ?')
            if(resposta == false) return false
        }
//Aqui nessa parte do Script compara a quantidade com a de pe�as por corte
        var referencia                          = '<?=$referencia;?>'
        var operacao_custo                      = '<?=$operacao_custo;?>'
        var qtde_lote                           = eval('<?=$qtde_lote;?>')
        var lote_minimo_ignora_faixa_orcavel    = '<?=$lote_minimo_ignora_faixa_orcavel;?>'
        var interpolacao                        = eval('<?=$interpolacao;?>')
//S� pode fazer a compara��o se o Produto for do tipo Esp e a Opera��o de Custo for do Tipo Industrial
        if(referencia == 'ESP' && operacao_custo == 0) {
            /**********************L�gica para comparar Lotes Or��veis**********************/
            //Significa que esse PA, � pode ser vendido com a Qtde menor do que a Qtde do Lote, nesse caso n�o se trabalha c/ a Faixa Or��vel ...
            if(lote_minimo_ignora_faixa_orcavel == 'S') {
                var qtde_minima = qtde_lote
                var qtde_maxima = qtde_lote * interpolacao
                if((document.form.txt_quantidade.value < qtde_minima) || (document.form.txt_quantidade.value > qtde_maxima)) {
                    alert('A QUANTIDADE EST� FORA DA QTDE DE LOTE OR��VEL !\n\nEM CASO DE D�VIDAS CONSULTE O DEPTO. T�CNICO.')
                    document.form.txt_quantidade.focus()
                    document.form.txt_quantidade.select()
                    return false
                }
            }else {
                var qtde_minima = qtde_lote / interpolacao
                var qtde_maxima = qtde_lote * interpolacao
                if((document.form.txt_quantidade.value < qtde_minima) || (document.form.txt_quantidade.value > qtde_maxima)) {
                    var resposta = confirm('A QTDE EST� INCOMPAT�VEL COM A QTDE DE LOTES OR��VEIS E IMPLICAR� EM VERIFICA��O PELO DEPTO. T�CNICO !!!\nDESEJA MANTER ESSA QTDE ?')
                    if(resposta == false) {
                        document.form.txt_quantidade.focus()
                        document.form.txt_quantidade.select()
                        return false
                    }
                }
            }
            /*******************************************************************************/
        }
//S� vai existir essa combo quando o item de PA que eu estou or�ando for um ESP do Tipo Revenda ...
/*Toda vez em que for um PA ESP e que for do Tipo Revenda, eu tenho que verificar se a Qtde
do Item do Or�amento n�o est� abaixo da Qtde do Lote daquele fornecedor corrente ...*/
        if(typeof(document.form.cmb_lote_minimo) == 'object') {
            var quantidade = eval(document.form.txt_quantidade.value)
            if(document.form.cmb_lote_minimo.length == 0) {
                alert('ITEM DEPENDENDO DE LOTE M�NIMO DO CUSTO !')
            }else {
/*Comentei por enquanto a pedido do Roberto -> D�rcio ...
//Se a qtde do Item do Or�amento for < que a Qtde do Lote M�nimo n�o posso prosseguir ...
                var lote_minimo = eval(document.form.cmb_lote_minimo[document.form.cmb_lote_minimo.selectedIndex].text)
                if(quantidade < lote_minimo) {
                    alert('A QTDE DESTE ITEM N�O EST� COMPAT�VEL COM A QTDE DO LOTE DO FORNECEDOR !')
                    document.form.txt_quantidade.focus()
                    document.form.txt_quantidade.select()
                    return false
                }*/
            }
        }
        /**************************Controle com os Pre�os de Venda**************************/
        /*var id_funcionario          = '<?=$_SESSION[id_funcionario];?>'
        var preco_liquido_final_rs  = eval(strtofloat(document.form.txt_preco_liquido_final_rs.value))
        var preco_minimo_venda      = eval(strtofloat(document.form.txt_preco_minimo_venda.value))
        Somente os Funcion�rios Roberto 62, D�rcio 98 porque programa, Nishimura 136 e Netto 147 porque programa 
        que podem colocar qualquer pre�o aqui no Alterar Item de Or�amento ...
        if(id_funcionario != 62 && id_funcionario != 98 && id_funcionario != 136 && id_funcionario != 147) {
            //Nunca o Pre�o L�quido Final poder� ser menor que o Pre�o M�nimo de Venda ...
            if(preco_liquido_final_rs < preco_minimo_venda) {
                alert('PRE�O DE VENDA INV�LIDO !!!\n\nPRE�O DE VENDA ABAIXO DO PRE�O M�NIMO, FALE COM A GERENCIA !')
                return false
            }
        }*/
//Leva a posi��o da pagina��o por par�metro para n�o dar problema
        return comparar_quantidade_pecas(posicao, verificar)
    }else {//O or�amento j� est� fechado ...
//Aqui eu desabilito as caixas p/ poder gravar no BD ...
        document.form.txt_quantidade.disabled               = false
        document.form.txt_desconto_porc_extra.disabled      = false
        document.form.txt_acrescimo_extra_porc.disabled     = false
        document.form.txt_preco_liquido_final_rs.disabled   = false
//Estou submetendo atrav�s do Link
    }
/**********Fa�o essa adapta��o abaixo porque se o Usu�rio ficar clicando mais de uma vez, n�o d� merda ...**********/
    if(document.getElementById('lbl_loading').innerHTML == '') {
        limpeza_moeda('form', 'txt_quantidade, txt_desconto_porc_extra, txt_acrescimo_extra_porc, txt_preco_liquido_final_rs, ')
    }
/*******************************************************************************************************************/
    document.getElementById('lbl_loading').innerHTML = '<img src="../../../../css/little_loading.gif"> <font size="2" color="brown"><b>LOADING ...</b></font>'
//Recupera a posi��o corrente no hidden, para n�o dar erro de pagina��o
    document.form.posicao.value = posicao;
//Aqui � para n�o atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
//Submetendo o Formul�rio
    document.form.submit()
}

/*Esse par�metro "chamar_comissao" s� ser� = 'N�O' quando essa fun��o "preco_liquido_final" for chamada 
no onload do Body ...*/
function preco_liquido_final(chamar_comissao) {
    var status_custo = eval('<?=$status_custo;?>')
    var referencia   = '<?=$referencia;?>'
    var id_pais      = eval('<?=$id_pais;?>')
    var dolar_dia    = eval('<?=$dolar_dia?>')

    if(status_custo == 0 && referencia == 'ESP') {//Precisa liberar o custo do Produto
        document.form.txt_preco_liq_fat.value = 'Or�ar'
    }else {//Custo do Produto j� liberado
        var conceder_pis_cofins = '<?=$conceder_pis_cofins;?>'
        var qtde = (document.form.txt_quantidade.value != '') ? eval(document.form.txt_quantidade.value) : 0
        if(document.form.txt_preco_liq_fat.value == 'DEPTO T�CNICO') {
            var preco_liq_fat = 0
        }else {
            var preco_liq_fat = (document.form.txt_preco_liq_fat.value != 'Or�ar') ? eval(strtofloat(document.form.txt_preco_liq_fat.value)) : 0
        }
        //Se O Pa�s do Cliente � 'Estrangeiro' e o Produto Acabado � 'ESP' ...
        /*if(id_pais != 31 && referencia == 'ESP') {
            //Como o Pre�o que aqui vem do Custo est� em R$, este precisa ser transformado em Moeda Estrangeira ...
            preco_liq_fat/= dolar_dia
            preco_liq_fat = strtofloat(arred(String(preco_liq_fat), 2, 1))
        }*/
        var desconto_cliente_porc 	= (document.form.txt_desconto_cliente_porc.value != '') ? eval(strtofloat(document.form.txt_desconto_cliente_porc.value)) : 0
        var desconto_porc_extra 	= (document.form.txt_desconto_porc_extra.value != '') ? eval(strtofloat(document.form.txt_desconto_porc_extra.value)) : 0
        var acrescimo_extra_porc 	= (document.form.txt_acrescimo_extra_porc.value != '') ? eval(strtofloat(document.form.txt_acrescimo_extra_porc.value)) : 0
        var desc_sgd_icms               = (document.form.txt_desc_sgd_icms.value != '') ? eval(strtofloat(document.form.txt_desc_sgd_icms.value)) : 0
        document.form.txt_preco_liquido_final_rs.value = preco_liq_fat * (100 - desconto_cliente_porc) / 100 * (100 - desconto_porc_extra) / 100 * (100 + acrescimo_extra_porc) / 100 * (100 - desc_sgd_icms) / 100
//Aqui tem que se arredondar para 2 casas, e da� sim tem q multiplicar pela qtde ...
        var preco_liquido_final_rs      = arred(document.form.txt_preco_liquido_final_rs.value, 2, 1)
/**************************************************************************/
/*Aqui eu fiz um macete para permitir o arredondamento de forma mais exata, a fun��o arred ainda falha nessa parte, 
sendo assim eu vasculho se todas as casas decimais s�o igual a 9, para poder somar mais hum na parte inteira*/
        var vetor_preco = preco_liquido_final_rs.split(',')
        var reais       = vetor_preco[0]
        var centavos 	= vetor_preco[1]
//Verifica se a parte decimal � 99 p/ poder somar mais hum na parte inteira ...
        if(centavos == 99) {
            reais = eval(reais) + 1
            preco_liquido_final_rs = reais + ',00'
            document.form.txt_preco_liquido_final_rs.value = preco_liquido_final_rs
        }
        document.form.txt_preco_liquido_final_rs.value = arred(document.form.txt_preco_liquido_final_rs.value, 2, 1)
        if(document.form.txt_preco_liquido_final_rs.value == '0,00') {
            document.form.txt_preco_liquido_final_rs.value      = 'Adequar a qtde or��vel / chamar Depto. T�cnico !'
            document.form.txt_preco_liquido_final_rs.maxlength 	= 52
            document.form.txt_preco_liquido_final_rs.size       = 52
        }else {
            document.form.txt_preco_liquido_final_rs.maxlength 	= 12
            document.form.txt_preco_liquido_final_rs.size       = 12
        }
/**************************************************************************/
        preco_liquido_final_rs                          = eval(strtofloat(preco_liquido_final_rs))
        document.form.txt_total_rs_sem_impostos.value   = preco_liquido_final_rs * qtde
        document.form.txt_total_rs_sem_impostos.value   = arred(document.form.txt_total_rs_sem_impostos.value, 2, 1)
/**************************************************************************/
        //C�lculo dos Impostos ...
        var total_rs_lote 	= eval(strtofloat(document.form.txt_total_rs_sem_impostos.value))
        var aliquota_ipi 	= eval(strtofloat(document.form.hdd_aliquota_ipi.value))
        var aliquota_iva 	= eval(strtofloat(document.form.hdd_aliquota_iva.value))
        var total_ipi 		= eval((total_rs_lote * aliquota_ipi) / 100)
        var total_icms_st 	= eval((total_rs_lote * aliquota_iva) / 100)
        document.form.txt_total_ipi.value               = total_ipi
        document.form.txt_total_icms_st.value           = total_icms_st
        document.form.txt_total_rs_com_impostos.value 	= total_ipi + total_icms_st + total_rs_lote
        document.form.txt_total_ipi.value               = arred(document.form.txt_total_ipi.value, 2, 1)
        document.form.txt_total_icms_st.value           = arred(document.form.txt_total_icms_st.value, 2, 1)
        document.form.txt_total_rs_com_impostos.value	= arred(document.form.txt_total_rs_com_impostos.value, 2, 1)
    }
/*Eu tenho que ficar chamando essa fun��o "calcular_preco_por_kilo()" aki dentro dessa outra fun��o pq o 
Pre�o Normal deste Item est� sujeito a ter modifica��es ou atrav�s dos descontos e acr�scimos que o usu�rio 
vai digitando nessa pr�pria tela mesmo ou ent�o atrav�s do Pre�o Liq. Final que o usu�rio j� pode desejar 
diretamente pelo no Alt + C q fica em um Iframe e q da� acaba refletindo no Pre�o Normal dessa Tela 
q tem que recalcular tamb�m o "Pre�o / Kg" dessa Tela*/
    calcular_preco_por_kilo()
    if(chamar_comissao != 'NAO') calcular_comissoes()
}

function calcular_preco_por_kilo() {
    var preco_liquido_final_rs = (document.form.txt_preco_liquido_final_rs.value != 'CALCULANDO ...' && document.form.txt_preco_liquido_final_rs.value != 'Adequar a qtde or��vel / chamar Depto. T�cnico !') ? eval(strtofloat(document.form.txt_preco_liquido_final_rs.value)) : 0
    var peso_unitario = (document.form.txt_peso_por_pc_kg.value != '') ? eval(strtofloat(document.form.txt_peso_por_pc_kg.value)) : 1
    if(peso_unitario != 0) {//P/ n�o dar erro de Divis�o por Zero
            preco_por_kilo = preco_liquido_final_rs / peso_unitario
    }else {
            preco_por_kilo = preco_liquido_final_rs
    }
    document.form.txt_preco_por_kilo.value = preco_por_kilo
    document.form.txt_preco_por_kilo.value = arred(document.form.txt_preco_por_kilo.value, 2, 1)
}

//Quando essa mesma fun��o for solicitada atr�ves do evento onblur, ent�o eu n�o passo o �ndice do vetor na tela ...
function comparar_quantidade_pecas(posicao) {
    var referencia              = '<?=$referencia;?>'
    var discriminacao           = '<?=$discriminacao;?>'
    var id_familia              = eval('<?=$id_familia;?>')
    var id_pais                 = eval('<?=$id_pais;?>')
    var dolar_dia               = eval('<?=$dolar_dia;?>')
    var lote_min_producao_reais = eval('<?=$lote_min_producao_reais;?>')
//Aqui nessa parte do Script compara a quantidade de pe�as por embalagem para os produtos normais de linha
    if(referencia != 'ESP') {
        if(typeof(document.form.cmb_promocao) == 'object' && document.form.cmb_promocao.value != '') {
            if(document.form.cmb_promocao.value == 'A') {
                var qtde_promocional 	= eval('<?=$qtde_promocional;?>')
                if(qtde_promocional > document.form.txt_quantidade.value) {
                    var pergunta_a = confirm('QUANTIDADE ABAIXO DA QUANTIDADE PROMOCIONAL A !!!       SUGEST�O  =  '+qtde_promocional+'  . CONFIRMA A QUANTIDADE ? ')
                    if(pergunta_a == false) {
                        document.form.txt_quantidade.focus()
                        document.form.txt_quantidade.select()
                        return false
                    }
                }
            }else if(document.form.cmb_promocao.value == 'B') {
                var qtde_promocional_b = eval('<?=$qtde_promocional_b;?>')
                if(qtde_promocional_b > document.form.txt_quantidade.value) {
                    var pergunta_b = confirm('QUANTIDADE ABAIXO DA QUANTIDADE PROMOCIONAL B !!!       SUGEST�O  =  '+qtde_promocional_b+'  . CONFIRMA A QUANTIDADE ? ')
                    if(pergunta_b == false) {
                        document.form.txt_quantidade.focus()
                        document.form.txt_quantidade.select()
                        return false
                    }
                }
            }
        }
        /***********************************Controle de Pe�as por Embalagem***********************************/
        //Todo o controle � feito dentro da Fun��o de Pe�as por Embalagem ...
        var resultado = pecas_por_embalagem(referencia, discriminacao, id_familia, document.form.txt_quantidade.value, document.form.txt_pcs_embalagem.value, strtofloat(document.form.txt_preco_liquido_final_rs.value), lote_min_producao_reais, posicao)
        if(resultado == 1) {//Usu�rio clicou em Cancelar ...
            document.form.txt_quantidade.focus()
            document.form.txt_quantidade.select()
            return false
        }else if(resultado == 0 || resultado == 2) {//"Alert" 0, "Confirm" 2 bot�o OK ...
            /***********************************Controle por Funcion�rios***********************************/
            var id_funcionario_logado = String('<?=$_SESSION['id_funcionario'];?>')
            
            for(var j = 0; j < vetor_funcionarios_ignorar_pecas_por_embalagem.length; j++) {
                /*Verifico se o Funcion�rio que est� logado pode colocar qualquer valor no que se refere � "Pe�as por Embalagem" ...
                Essa vari�vel "vetor_funcionarios_ignorar_pecas_por_embalagem" est� dentro da biblioteca pecas_por_embalagem.js ...*/
                var indice = id_funcionario_logado.indexOf(vetor_funcionarios_ignorar_pecas_por_embalagem[j])
                if(indice == 0) {//Significa que esse Funcion�rio pode fazer o que bem entender ...
                    var pergunta = confirm('DESEJA MANTER ESTA(S) QUANTIDADE(S) ?')
                    if(pergunta == false) {//Usu�rio clicou em Cancelar ...
                        document.form.txt_quantidade.focus()
                        document.form.txt_quantidade.select()
                        return false
                    }
                    break//P/ sair do Loop ...
                }
            }
        }
        /*****************************************************************************************************/
    }
    /*Somente quando o usu�rio estiver marcando queima de Estoque que o Sistema ir� fazer 
    essa verifica��o ...*/
    if(document.form.hdd_queima_estoque.value == 'S' && typeof(document.form.hdd_quantidade_queima) == 'object') {
        //S� permite marcar queima de Estoque, se a Qtde do Item <= a Qtde de Estoque de Queima ...
        if(eval(document.form.txt_quantidade.value) > eval(document.form.hdd_quantidade_queima.value)) {
            alert('QUANTIDADE INV�LIDA !!!\nQUANTIDADE ACIMA DA QUANTIDADE DE EXCESSO DE ESTOQUE !')
            document.form.hdd_queima_estoque.value = 'N'//Retira a Marca��o de Queima de Estoque ...
            return false
        }
    }
//Aqui eu desabilito as caixas p/ poder gravar no BD ...
    document.form.txt_desconto_porc_extra.disabled      = false
    document.form.txt_acrescimo_extra_porc.disabled     = false
    document.form.txt_preco_liquido_final_rs.disabled   = false
    
/**********Fa�o essa adapta��o abaixo porque se o Usu�rio ficar clicando mais de uma vez, n�o d� merda ...**********/
    if(document.getElementById('lbl_loading').innerHTML == '') {
        limpeza_moeda('form', 'txt_quantidade, txt_desconto_porc_extra, txt_acrescimo_extra_porc, txt_preco_liquido_final_rs, ')
    }
/*******************************************************************************************************************/
    document.getElementById('lbl_loading').innerHTML = '<img src="../../../../css/little_loading.gif"> <font size="2" color="brown"><b>LOADING ...</b></font>'
//Recupera a posi��o corrente no hidden, para n�o dar erro de pagina��o
    document.form.posicao.value = posicao
//Aqui � para n�o atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
    document.form.submit()
}

function alterar_fornecedor_default(posicao) {
    var mensagem = confirm('VOC� DESEJA ALTERAR O LOTE DEFAULT DO FORNECEDOR ?')
    if(mensagem == true) {
        limpeza_moeda('form', 'txt_quantidade, txt_desconto_porc_extra, txt_acrescimo_extra_porc, ')
//Recupera a posi��o corrente no hidden, para n�o dar erro de pagina��o
        document.form.posicao.value = posicao
//Aqui � para n�o atualizar a Tela abaixo que chamou esse LightBox ...
        document.form.nao_atualizar.value = 1
//Submetendo o Formul�rio
        document.form.submit()
    }else {
        return false
    }
}

function executar_opcoes(option) {
    if(option == 1) {
        return validar('<?=$posicao;?>', 1)
    }else if(option == 2) {//Consultar Estoque ...
        ajax('../../../classes/estoque/visualizar_estoque.php?id_orcamento_venda_item<?=$id_orcamento_venda_item;?>', 'executar_opcoes')
    }else if(option == 3) {//Follow-UP
        ajax('../../../producao/cadastros/produto_acabado/follow_up.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'executar_opcoes')
    }
}

function excluir_follow_up(id_produto_acabado_follow_up) {
    var mensagem = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE FOLLOW-UP ?')
    if(mensagem == true) ajax('../../../producao/cadastros/produto_acabado/follow_up.php?id_produto_acabado_follow_up='+id_produto_acabado_follow_up, 'executar_opcoes')
}

function verificar_teclas(event) {
    if(navigator.appName == 'Microsoft Internet Explorer') {
        if(event.keyCode == 13) {//Se Enter faz a Consulta.
            ir_para_item()
            document.form.txt_produto_acabado.value = ''
        }
    }else {
        if(event.which == 13) {//Se Enter faz a Consulta.
            ir_para_item()
            document.form.txt_produto_acabado.value = ''
        }
    }
}

function calcular_desconto_extra(veio_de_descontos) {
    if(veio_de_descontos == 1) {//Representa a parte final da Tela onde tem as 4 caixas de Desconto A, B, C e D ...
        var calculo = 1
        if(document.form.txt_desc_a.value != '') var calculo = (1-eval(strtofloat(document.form.txt_desc_a.value)) / 100)
        if(document.form.txt_desc_b.value != '') var calculo = calculo * (1-eval(strtofloat(document.form.txt_desc_b.value)) / 100)
        if(document.form.txt_desc_c.value != '') var calculo = calculo * (1-eval(strtofloat(document.form.txt_desc_c.value)) / 100)
        if(document.form.txt_desc_d.value != '') var calculo = calculo * (1-eval(strtofloat(document.form.txt_desc_d.value)) / 100)
        if(calculo == 1) {
            document.form.txt_desconto_porc_extra.value = '0,00'
        }else {
            document.form.txt_desconto_porc_extra.value = (1- calculo) * 100
            document.form.txt_desconto_porc_extra.value = arred(document.form.txt_desconto_porc_extra.value, 2, 1)
        }
    }else {
        var preco_liq_fat               = (document.form.txt_preco_liq_fat.value != '' && document.form.txt_preco_liq_fat.value != 'Or�ar') ? eval(strtofloat(document.form.txt_preco_liq_fat.value)) : 0
        var desconto_cliente_porc 	= (document.form.txt_desconto_cliente_porc.value != '') ? eval(strtofloat(document.form.txt_desconto_cliente_porc.value)) : 0
        var desc_sgd_icms               = (document.form.txt_desc_sgd_icms.value != '') ? eval(strtofloat(document.form.txt_desc_sgd_icms.value)) : 0
        //Primeira F�rmula
        preco_liq_sem_extras 		= preco_liq_fat * (1 - desconto_cliente_porc / 100) * (1 - desc_sgd_icms / 100)
        
        //Defini��o de Vari�veis p/ a Segunda F�rmula
        if(document.form.txt_preco_liq_final_desejado.value != '') {
            var preco_liq_final_desejado = eval(strtofloat(document.form.txt_preco_liq_final_desejado.value))
            //Aki eu zero tanto o Desconto como o Acr�scimo ...
            document.form.txt_desconto_porc_extra.value 	= ''
            document.form.txt_acrescimo_extra_porc.value 	= ''
            //Aki � para n�o dar erro de Divis�o por 0, por isso que tem esse Tratamento
            if(preco_liq_sem_extras == 0) preco_liq_sem_extras = 1
            var	calculo_reajuste = (1 - preco_liq_final_desejado / preco_liq_sem_extras) * 100			
            if(calculo_reajuste < 0) {//Aqui eu Jogo o Valor p/ Acr�scimo ...
                document.form.txt_acrescimo_extra_porc.value = (-1)*calculo_reajuste
                document.form.txt_acrescimo_extra_porc.value = arred(document.form.txt_acrescimo_extra_porc.value, 2, 1)
            }else {//Aqui eu Jogo o Valor p/ Desconto ...
                document.form.txt_desconto_porc_extra.value = calculo_reajuste
                document.form.txt_desconto_porc_extra.value = arred(document.form.txt_desconto_porc_extra.value, 2, 1)
            }
        }else {
            var preco_liq_final_desejado = 0
            document.form.txt_desconto_porc_extra.value = ''
        }
    }
    preco_liquido_final()
}

function copiar_ultimo_preco_negociado(nota_sgd, prazo_medio, preco_liq_final) {
    var nota_sgd_orc_principal 	= '<?=$nota_sgd;?>'
    var prazo_medio_principal	= '<?=$prazo_medio;?>'
    //Tipo de Nota Venda ...
    if(nota_sgd_orc_principal != nota_sgd) {
        alert('O TIPO DE NOTA (NF/SGD) DE VENDA DO OR�AMENTO EST� INCOMPAT�VEL COM O DO �LTIMO PRE�O !')
        return false
    }
    //Prazo M�dio ...
    if((prazo_medio_principal > (eval(prazo_medio) + 15)) || (prazo_medio_principal < (eval(prazo_medio) - 15))) {
        alert('O PRAZO M�DIO DE VENDA DO OR�AMENTO EST� INCOMPAT�VEL COM O DO �LTIMO PRE�O !')
        return false
    }
    document.form.txt_preco_liq_final_desejado.value = preco_liq_final
    calcular_desconto_extra()
}

function copiar_preco_queima_estoque() {//preco_queima_estoque era par�metro que entrava aqui ...
    /*S� estamos mostrando a figura do Foguinho como referencial p/ saber que d� p/ trabalharmos com Pre�o 
    Promocional caso o item n�o tenha esse Tipo de Pre�o, esta n�o esta funcionando porque j� estamos 
    trabalhando com Pre�o Promocional em alguns itens, teoricamente n�o tem sentido mais trabalharmos com as 
    2 juntas ao mesmo tempo ... 30/06/2016 -> D�rcio - ordens do Roberto ...*/
    return false
    
    if(document.form.hdd_queima_estoque.value == 'N') {
        var data_validade_orc   = eval('<?=$data_validade_orc;?>')
        var data_atual          = eval('<?=$data_atual;?>')
        /*N�o podemos colocar em queima um Item que esteja com a Data Atual abaixo do Prazo 
        de Validade do Or�amento ...*/
        if(data_validade_orc < data_atual) {
            alert('OR�AMENTO FORA DA DATA VALIDADE !\n\n N�O � POSS�VEL INCLUIR EXCESSO NESSE ITEM !!!')
            return false
        }
        document.form.hdd_queima_estoque.value  = 'S'
    }else {
        document.form.hdd_queima_estoque.value  = 'N'
    }
    
    /*Essa aqui era a id�ia original, mas na data de 26/10/2015 Roberto achou interessante comentar 
    essa fun��o porque o Pre�o do PA � controlado pela Taxa de Estocagem e mais um outro Fator ...
    /*if(document.form.hdd_queima_estoque.value == 'N') {
        var data_validade_orc   = eval('<?=$data_validade_orc;?>')
        var data_atual          = eval('<?=$data_atual;?>')
        /*N�o podemos colocar em queima um Item que esteja com a Data Atual abaixo do Prazo 
        de Validade do Or�amento ...
        if(data_validade_orc < data_atual) {
            alert('OR�AMENTO FORA DA DATA VALIDADE !\n\n N�O � POSS�VEL INCLUIR EXCESSO NESSE ITEM !!!')
            //document.form.txt_preco_liq_final_desejado.value  = ''
            //document.form.txt_desconto_porc_extra.value       = ''
            //document.form.txt_acrescimo_extra_porc.value      = ''
            //document.form.hdd_queima_estoque.value            = 'N'
            //document.form.txt_quantidade.focus()
            //document.form.txt_quantidade.select()
            return false
        }
        /*Se o usu�rio digitar o Pre�o L�q Final Desejado e clicar em queima, significa que o usu�rio quer que a queima seja feita 
        em cima do Pre�o Digitado ...
        if(document.form.txt_preco_liq_final_desejado.value != '') {
            var preco_liq_final 		= eval(strtofloat(document.form.txt_preco_liq_final_desejado.value))
            var preco_queima_estoque 	= eval(strtofloat(preco_queima_estoque))
            /*Se o Pre�o digitado for menor do que o Pre�o de Queima de Estoque, o sistema barra, pois s� posso queimar o Item
            com o Pre�o igual ou superior ao Pre�o sugerido ...
            if(preco_liq_final < preco_queima_estoque) {
                alert('PRE�O L�QUIDO FINAL R$ DESEJADO INV�LIDO PARA QUEIMA !!!\n\nESSE PRE�O TEM QUE SER MAIOR OU IGUAL � R$ '+arred(String(preco_queima_estoque), 2, 1)+' QUE � A SUGEST�O DE EXCESSO !')
                document.form.txt_preco_liq_final_desejado.focus()
                document.form.txt_preco_liq_final_desejado.select()
                return false
            }
        }else {
            document.form.txt_preco_liq_final_desejado.value = preco_queima_estoque
        }
        document.form.hdd_queima_estoque.value          = 'S'
    }else {
        //Sempre que se tira a Queima, se tira a Promo��o ...
        if(typeof(document.form.cmb_promocao) == 'object' && document.form.cmb_promocao.value != '') {
            document.form.cmb_promocao.value = ''
        }
        document.form.txt_desconto_porc_extra.value     = ''
        document.form.txt_acrescimo_extra_porc.value    = ''
        document.form.hdd_queima_estoque.value          = 'N'
    }*/
    
    calcular_desconto_extra()
    executar_opcoes(1)
}

function ir_para_item() {
    if(document.form.txt_produto_acabado.value == '') {
        alert('DIGITE O ITEM QUE DESEJA CONSULTAR !')
        document.form.txt_produto_acabado.focus()
        return false
    }
//Aqui � para n�o atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
    window.location = 'alterar.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&txt_produto_acabado='+document.form.txt_produto_acabado.value.toUpperCase()
}

/*Fun��o que trava algumas caixas quando houver mudan�a na Qtde do Orc pq isso interfere no Pre�o L�q Fat em R$ / Pe�a ...

Somente o PHP dentro da Biblioteca Vendas na fun��o "calculo_preco_liq_final_item_orc" faz esse c�lculo, eu poderia
at� fazer isso em Ajax, mas temos tamb�m o outro problema que est� relacionado a Margem de Lucro Estimada, da� j� envolve 
Comiss�o, ent�o essa foi a melhor maneira de criarmos essa seguran�a ...*/
function alterar_quantidade() {
    var referencia  = '<?=$referencia;?>'
    var qtde        = eval('<?=$qtde;?>')
    
    if(referencia == 'ESP') {
        //Verifico se o usu�rio mudou a Quantidade do Item ...
        if(document.form.txt_quantidade.value != qtde) {//Se sim, fa�o toda a seguran�a abaixo ...
            //Desabilita as Caixas ...
            document.form.txt_desconto_porc_extra.disabled              = true
            document.form.txt_acrescimo_extra_porc.disabled             = true
            document.form.txt_preco_liq_final_desejado.disabled         = true
            document.form.txt_desc_a.disabled                           = true
            document.form.txt_desc_b.disabled                           = true
            document.form.txt_desc_c.disabled                           = true
            document.form.txt_desc_d.disabled                           = true
            //Layout de Desabilitado ...
            document.form.txt_desconto_porc_extra.className             = 'textdisabled'
            document.form.txt_acrescimo_extra_porc.className            = 'textdisabled'
            document.form.txt_preco_liq_final_desejado.className        = 'textdisabled'
            document.form.txt_desc_a.className                          = 'textdisabled'
            document.form.txt_desc_b.className                          = 'textdisabled'
            document.form.txt_desc_c.className                          = 'textdisabled'
            document.form.txt_desc_d.className                          = 'textdisabled'
            document.getElementById('lbl_mensagem').style.visibility    = 'visible'
        }else {
            //Habilita as Caixas ...
            document.form.txt_desconto_porc_extra.disabled              = false
            document.form.txt_acrescimo_extra_porc.disabled             = false
            document.form.txt_preco_liq_final_desejado.disabled         = false
            document.form.txt_desc_a.disabled                           = false
            document.form.txt_desc_b.disabled                           = false
            document.form.txt_desc_c.disabled                           = false
            document.form.txt_desc_d.disabled                           = false
            //Layout de Habilitado ...
            document.form.txt_desconto_porc_extra.className             = 'caixadetexto'
            document.form.txt_acrescimo_extra_porc.className            = 'caixadetexto'
            document.form.txt_preco_liq_final_desejado.className        = 'caixadetexto'
            document.form.txt_desc_a.className                          = 'caixadetexto'
            document.form.txt_desc_b.className                          = 'caixadetexto'
            document.form.txt_desc_c.className                          = 'caixadetexto'
            document.form.txt_desc_d.className                          = 'caixadetexto'
            document.getElementById('lbl_mensagem').style.visibility    = 'hidden'
        }
    }
    preco_liquido_final()
}

function desatrelar_pa() {
//PA Enviado ...
    if(!combo('form', 'cmb_pa_substitutivo', '', 'SELECIONE O P.A. ENVIADO !')) {
        return false
    }
    var resposta = confirm('DESEJA REALMENTE DESATRELAR ESSE P.A. DO PA PRINCIPAL ?')
    if(resposta == true) {
        var id_pa_enviado = document.form.cmb_pa_substitutivo.value
        nova_janela('../../../classes/produtos_acabados/desatrelar_pa.php?id_pa_a_ser_desatrelado='+id_pa_enviado+'&id_produto_acabado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.ativar_loading()
}
</Script>
</head>
<body topmargin='0' onload="preco_liquido_final('NAO');document.form.txt_preco_liq_final_desejado.focus()" onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>', 1)">
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<input type='hidden' name='id_orcamento_venda_item' value='<?=$id_orcamento_venda_item;?>'>
<!--Esse valor vem do Pop-UP de consultar Produto(s) Acabado(s)-->
<input type='hidden' name='id_produto_acabado_consultado' value='<?=$id_produto_acabado_consultado;?>'>
<input type='hidden' name='posicao' value='<?=$posicao;?>'>
<!--Se o usu�rio clicar no bot�o de Incluir um Novo Item, o Sys 1� salva p/ depois redirecionar de tela ...-->
<input type='hidden' name='hdd_ir_para_incluir'>
<!--Controle de Tela-->
<input type='hidden' name='hdd_queima_estoque' value='<?=$queima_estoque;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo' value='1'>
<!--****************-->
<!--Existe essa outra tabela por causa de controle do objeto de calculadora-->
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr>
        <td>
            <fieldset>
                <legend>
                    <span style='cursor: pointer'>
                        <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                            <b>CONSULTA R�PIDA</b>
                        </font>
                    </span>
                </legend>
                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                    <tr class='linhanormal'>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                                Ir para Item
                            </font>
                        </td>
                        <td>
                            <input type='text' name="txt_produto_acabado" id="txt_produto_acabado" title="Digite o P.A." size="30" onkeyup="verificar_teclas(event)" class='caixadetexto'>
                            &nbsp;
                            <img src = "../../../../imagem/menu/pesquisar.png" onclick="ir_para_item()" title='Consultar' style='cursor:pointer' border="0">
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr></tr>
    <tr>
		<td>
			<fieldset>
				<legend class="legend_contorno">
					<span style="cursor: pointer;">
						<font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'><b>
							ALTERAR ITEM DO OR�AMENTO N.� 
							<font color="darkblue">
        						<?=$id_orcamento_venda;?>
        					</font>
						</b></font>
						<font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'><b>
							 / FORMA DE VENDA: 
							<font color="darkblue">
        						<?=$prazo_faturamento;?>
        					</font>
						</b></font>
					</span>
				</legend>
				<table border="0" width="100%" cellspacing='1' cellpadding='1' align='center'>
					<tr>
						<td colspan="5">
							<fieldset>
								<legend class="legend_contorno2">
									<?=intermodular::pa_discriminacao($id_produto_acabado, 0);?>
									<font color='black'>
										<b>(Lote M�n. = </b>
									<?
//Se for Cliente do Tipo Internacional, divide pelo d�lar do dia o Lote M�nimo de Produ��o
										if($id_pais != 31) $lote_min_producao_reais/= $dolar_dia;
									?>
										<b><?=$tipo_moeda.number_format($lote_min_producao_reais, 2, ',', '.');?>)</b>
									</font>
                                                                        <?
                                                                            if($id_pais != 31) {//S� mostro o valor do D�lar em Clientes de Pa�ses Estrangeiros ...
                                                                                echo 'C�mbio - U$ = R$ '.number_format($dolar_dia, 4, ',', '.');
                                                                            }
                                                                        ?>
								</legend>
								<table width='100%' border='0' cellspacing='2' cellpadding='2' align='center'> 
									<tr class='linhanormalescura'>
                                                                            <td colspan='5'>
                                                                                <b>P.A. Substitutivo:</b>
                                                                                <font color='darkblue'>(MARCA��O NA PE�A)=></font>
                                                                                <select name='cmb_pa_substitutivo' title='Selecione o P.A. Substitutivo' class='combo'>
                                                                                <?
                                                                                    //Aqui eu listo todos os PA(s) Padr�es que j� foram substitu�dos com o PA Principal ...
                                                                                    $sql = "SELECT 
                                                                                            IF(ps.`id_produto_acabado_1` = '$id_produto_acabado', ps.`id_produto_acabado_2`, ps.`id_produto_acabado_1`) AS id_pa 
                                                                                            FROM `pas_substituires` ps 
                                                                                            WHERE 
                                                                                            (ps.`id_produto_acabado_1` = '$id_produto_acabado') 
                                                                                            OR (ps.`id_produto_acabado_2` = '$id_produto_acabado') ";
                                                                                    $campos_pas_substituicao = bancos::sql($sql);
                                                                                    $linhas_pas_substituicao = count($campos_pas_substituicao);
                                                                                    if($linhas_pas_substituicao > 0) {//Encontrou pelo menos 1 PA Substituto ...
                                                                                        for($i = 0; $i < $linhas_pas_substituicao; $i++) $id_pas_substitutos.= $campos_pas_substituicao[$i]['id_pa'].', ';
                                                                                        $id_pas_substitutos = substr($id_pas_substitutos, 0, strlen($id_pas_substitutos) - 2);
                                                                                    }
                                                                                    //Se mesmo assim n�o veio nenhum PA Substituto, trato a vari�vel abaixo p/ n�o furar o SQL abaixo ...
                                                                                    if(empty($id_pas_substitutos)) $id_pas_substitutos = 0;
//Trago todos os PA(s) que est�o atrelados na tab. relacional, + o outro selecionado pelo usu�rio no consultar P.A.
                                                                                    $sql = "SELECT `id_produto_acabado`, CONCAT(`referencia`, ' * ', `discriminacao`) AS dados 
                                                                                            FROM `produtos_acabados` 
                                                                                            WHERE `id_produto_acabado` IN ($id_pas_substitutos) ";
                                                                                    echo combos::combo($sql, $id_produto_acabado_discriminacao);
                                                                                ?>
                                                                                </select>
                                                                                &nbsp;
                                                                                <input type='button' name='cmd_atrelar_pa' value='Atrelar PA' title='Atrelar PA' onclick="nova_janela('../../../classes/produtos_acabados/atrelar_pa.php?id_pa_a_ser_atrelado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
                                                                                &nbsp;
                                                                                <input type='button' name='cmd_desatrelar_pa' value='Desatrelar PA' title='Desatrelar PA' onclick='desatrelar_pa()' class='botao'>
                                                                            </td>
									</tr>
									<?
									/*********************************Controle de Lotes Or��veis*****************************************/
									if($referencia == 'ESP') {
										//Se o PA � do Tipo Industrial, ent�o mostro p/ o Or�amentista os Lotes Or��veis ...
										if($operacao_custo == 0) {
											if($qtde_lote == '' || $qtde_lote == 0) {
												$rotulo = '';
												$qtde_lotes_orcaveis = '';
											}else {
												$qtde_minima = $qtde_lote / $interpolacao;
												$qtde_maxima = $qtde_lote * $interpolacao;
												
												if($lote_minimo_ignora_faixa_orcavel == 'S') {
													$rotulo = " / LOTES OR&Ccedil;&Aacute;VEIS ";
													$qtde_lotes_orcaveis = "<font color = 'white'>&Agrave partir de ".$qtde_lote." p&ccedil;s</font>";
												}else {
													$rotulo = " / LOTES OR&Ccedil;&Aacute;VEIS ";
													$qtde_lotes_orcaveis = "<font color = 'white'>".' / '.segurancas::number_format($qtde_minima, 2, '.').' � '.segurancas::number_format($qtde_maxima, 2, '.')."</font>";
												}
											}
											/*Se o PA � do Tipo Revenda, ent�o eu mostro todos os Lotes M�nimos dispon�veis dakele PA de acordo c/ 
											os Fornecedores atrelados em uma combo, lembrando que eu carrego primeiro o Fornecedor Default*/
										}else {
											$rotulo = "<font color = 'white'> / Lote M�nimo:</font>";
											$exibir_combo = 1;
										}
									}else {
										$rotulo = '';
										$qtde_lotes_orcaveis = '';
									}
?>
									<tr class='linhanormalescura'>
										<td>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
												<b>QTDE <?=$rotulo;?></b>
											</font>
										</td>
										<td>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
												<b>ESTOQUE</b>
											</font>
											<img src = '../../../../imagem/propriedades.png' name='img_consultar_estoque' id='img_consultar_estoque' title="Consultar Estoque" onclick='executar_opcoes(2)'>
										</td>
										<td colspan='2'>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
												<b>PRAZO DE ENTREGA</b>
											</font>
										</td>
									</tr>
									<tr class='linhanormal'>
										<td bgcolor='green'>
                                                                                    <?
                                                                                        /************************************************************/
                                                                                        /****Tratamento com as Casas Decimais do campo Quantidade****/
                                                                                        /************************************************************/
                                                                                        if($sigla == 'KG') {//Essa � a �nica sigla que permite trabalharmos com Qtde Decimais ...
                                                                                            $onkeyup            = "verifica(this, 'moeda_especial', 1, '', event) ";
                                                                                            $qtde_apresentar    = number_format($qtde, 1, ',', '.');
                                                                                        }else {
                                                                                            $onkeyup            = "verifica(this, 'aceita', 'numeros', '', event) ";
                                                                                            $qtde_apresentar    = (integer)$qtde;
                                                                                        }
                                                                                        /************************************************************/
                                                                                    ?>
											<input type='text' name='txt_quantidade' value='<?=$qtde_apresentar;?>' title='Digite a Quantidade' onkeyup="<?=$onkeyup;?>;alterar_quantidade();validar_item(event)" size='7' maxlength='6' class='caixadetexto'>
<?
											if($queima_estoque == 'S') echo '<font color="orange">&nbsp;<b>EXCESSO</b></font>';
//Significa que � um PA ESP e que � do Tipo Revenda
											if($exibir_combo == 1) {
												echo "<font color = 'white'> / </font>";
?>
<!--Observa��o:
Aqui nessa combo eu armazeno o do id_fornecedor porque segundo o Roberto toda vez em que eu trocar o Lote 
M�nimo do Fornecedor, ent�o ... eu tamb�m tenho que trocar o Fornecedor Default deste PA ...-->
											<select name='cmb_lote_minimo' title='Selecione o Lote M�nimo' onchange="alterar_fornecedor_default('<?=$posicao;?>')" class='combo'>
											<?
//O primeiro passo a fazer � verificar qual o Fornecedor Default do PA Revenda ...
												$id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1);
//Verificar todos os Fornecedores que est�o atrelados a este PA ...
												$sql = "SELECT fpi.id_fornecedor, pa.id_produto_insumo 
                                                                                                        FROM `produtos_acabados` pa 
                                                                                                        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` 
                                                                                                        WHERE pa.`id_produto_acabado` = '".$id_produto_acabado."' 
                                                                                                        AND pa.`ativo` = '1' 
                                                                                                        AND fpi.ativo = '1' ";
												$campos2 = bancos::sql($sql);
												$linhas2 = count($campos2);
												for($i = 0; $i < $linhas2; $i++) {
													$sql = "SELECT lote_minimo_pa_rev 
                                                                                                                FROM `fornecedores_x_prod_insumos` 
                                                                                                                WHERE `id_fornecedor`= ".$campos2[$i]['id_fornecedor']." 
                                                                                                                AND `id_produto_insumo`= ".$campos2[$i]['id_produto_insumo']." ORDER BY lote_minimo_pa_rev LIMIT 1 ";
													$campos_lote_min = bancos::sql($sql);
/*Se o Fornecedor Corrente do Loop do PA � o mesmo que o Fornecedor Default, ent�o este vem selecionado 
na combo ...*/
													if($id_fornecedor_setado == $campos2[$i]['id_fornecedor']) {
											?>
												<option value="<?=$campos2[$i]['id_fornecedor'];?>" selected><?=$campos_lote_min[0]['lote_minimo_pa_rev'];?></option>
											<?
													}else {
											?>
												<option value="<?=$campos2[$i]['id_fornecedor'];?>"><?=$campos_lote_min[0]['lote_minimo_pa_rev'];?></option>
											<?
													}
												}
											?>
											</select>
										<?
											}else {
                                                                                            echo $qtde_lotes_orcaveis;
											}
										?>
										</td>
										<?
                                                                                    /****Coment�rio da Queima****/
                                                                                    
                                                                                    /*A partir do dia 01/08/2014 o Roberto pediu p/ comentar a fun��o 
                                                                                    de queima, porque a ML Estimada e a Taxa de Estocagem substitui
                                                                                    essa fun��o ...*/
                                                                                    $valores        = intermodular::calculo_estoque_queima_pas_atrelados($id_produto_acabado);
                                                                                    $estoque_queima = $valores['total_eq_pas_atrelados'];

                                                                                    //Se for componente, n�o existe queima ...
                                                                                    if($id_familia == 23 || $id_familia == 24) $estoque_queima = 0;

                                                                                    $ec_tot         = $valores['total_ec_pas_atrelados'];
										?>
										<td>
                                                                                    <font title='Est. Comprometido = Est. Disp. - Pend�ncias' style='cursor:help'>
                                                                                        EC: <?=number_format($est_comprometido, 2, ',', '.');?>
                                                                                    </font>
                                                                                    &nbsp;&nbsp;&nbsp;
                                                                                    <font title='Est. Comp. Total = Est. Comp. (Todos os PAs Atrelados)' style='cursor:help'>
                                                                                        EC tot: <?=number_format($ec_tot, 2, ',', '.');?>
                                                                                    </font>
                                                                                    &nbsp;&nbsp;&nbsp;
                                                                                    <font title='Estoque Fornecedor' style='cursor:help'>
                                                                                        E Forn: <?=number_format($est_fornecedor, 2, ',', '.');?>
                                                                                    </font>
										</td>
										<td colspan='2'>
										<?
                                                                                    $vetor_prazos_entrega = vendas::prazos_entrega();
/************************Tratamento Novo com Rela��o ao Prazo de Entrega************************/
/*Se o P.A. � do Tipo = 'ESP' e a O.C. = 'Revenda', ent�o eu ignoro o prazo_entrega_tecnico da Tabela
de Or�amento e leio o "Prazo de Entrega" da tabela relacional de 'prazos_revendas_esps' ...*/
                                                                                    if($referencia == 'ESP' && $operacao_custo == 1) {
                                                                                        $sql = "SELECT `prazo` 
                                                                                                FROM `prazos_revendas_esps` 
                                                                                                WHERE `id_fornecedor` = '$id_fornecedor_setado' 
                                                                                                AND `id_orcamento_venda` = '$id_orcamento_venda' 
                                                                                                AND `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                                                                                        $campos2 = bancos::sql($sql);
//Se encontrar algum Prazo de Entrega p/ esta condi��o ...
                                                                                        if(count($campos2) == 1) {
                                                                                            $prazo_entrega_tecnico = $campos2[0]['prazo'];
                                                                                            if($prazo_entrega_tecnico == 0) {
                                                                                                $prazo_entrega_apresentar = '<font color="black"><b>IMEDIATO</b></font>';
                                                                                                $rotulo = 'Pzo Entrega sugerido pelo Depto. T�cnico =&gt;&nbsp;';
                                                                                            }else {
                                                                                                $prazo_entrega_apresentar = $prazo_entrega_tecnico;
                                                                                                $rotulo = 'Pzo Entrega sugerido pelo Depto. T�cnico =&gt;&nbsp;';
                                                                                            }
                                                                                        }else {//Se n�o encontrar ...
                                                                                            $prazo_entrega_apresentar = '<font color="red"><b>SEM PRAZO</b></font>';
                                                                                            $rotulo = 'Pzo Entrega sugerido pelo Depto. T�cnico =&gt;&nbsp;';
                                                                                        }
                                                                                    }else {
                                                                                        foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
                                                                                            if($referencia == 'ESP') {
                                                                                                if($prazo_entrega_tecnico == '0.0') {
                                                                                                    $prazo_entrega_apresentar   = '<font color="red"><b>SEM PRAZO</b></font>';
                                                                                                    $rotulo                     = 'Pzo Entrega sugerido pelo Depto. T�cnico =&gt;&nbsp;';
//Aqui � o Prazo de Ent. da Empresa Divis�o, e verifica qual � o certo para poder carregar na caixa de texto
/*Existe esse esquema de Int, porque o Campo -> 'prazo_entrega_tecnico' � do Tipo Float, foi feito
esse esquema para n�o dar problema na hora de Atualizar o Custo*/
                                                                                                }else if((int)$prazo_entrega_tecnico == $indice) {
                                                                                                    $prazo_entrega_apresentar = $prazo_entrega;
                                                                                                    $rotulo = 'Pzo Entrega sugerido pelo Depto. T�cnico =&gt;&nbsp;';
                                                                                                }
                                                                                            }else {
//Aqui � o Prazo de Ent. da Empresa Divis�o, e verifica qual � o certo para poder carregar na caixa de texto
                                                                                                if($prazo_entrega_divisao == $indice) {
                                                                                                    $prazo_entrega_apresentar = $prazo_entrega;
                                                                                                    $rotulo = 'Pzo Entrega (Grupo)=&gt;&nbsp;';
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    echo $rotulo.$prazo_entrega_apresentar;
										?>
										</td>
									</tr>
									<tr class='linhanormal'>
										<td>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
												P�s / Emb.:
											</font>
											<?
//Traz a quantidade de pe�as por embalagem da embalagem principal daquele produto
												$sql = "SELECT `pecas_por_emb` 
                                                                                                        FROM `pas_vs_pis_embs` 
                                                                                                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                                                                                                        AND `embalagem_default` = '1' LIMIT 1 ";
												$campos_pecas_embalagem     = bancos::sql($sql);
												$pecas_embalagem            = (count($campos_pecas_embalagem) == 1) ? number_format($campos_pecas_embalagem[0]['pecas_por_emb'], 0, ',', '.') : 0;
											?>
											<input type='text' name='txt_pcs_embalagem' value='<?=$pecas_embalagem;?>' title='Digite o P�s / Embalagem' onkeyup="verifica(this, 'moeda_especial', '', '', event)" size='5' maxlength='4' class='caixadetexto2' disabled>
										</td>
										<td>
                                                                                    <font title='Est. Disp. = Est. Real - Separados' style='cursor:help'>
                                                                                        ED: <?=number_format($qtde_disponivel, 2, ',', '.');?>
                                                                                    </font>
                                                                                    &nbsp;&nbsp;&nbsp;
                                                                                    <font title='Est. Real = Est. Total dentro da Empresa' style='cursor:help'>
                                                                                        ER: <?=number_format($est_real, 2, ',', '.');?>
                                                                                    </font>
                                                                                    &nbsp;&nbsp;&nbsp;
                                                                                    <font title='Est. Fornecedor Porto' style='cursor:help'>
                                                                                        E Porto: <?=number_format($est_porto, 2, ',', '.');?>
                                                                                    </font>
                                                                                    <?
                                                                                        /*Se existe queima de Estoque ou j� n�o existe mais e o usu�rio marcou queima, 
                                                                                        o sistema ainda d� uma chance p/ desmarcar Queima desse item do Or�amento */
                                                                                        if($estoque_queima > 0 || ($estoque_queima == 0 && $queima_estoque == 'S')) {
                                                                                            /*Se este item de ORC est� em Queima: ent�o al�m do Valor retornado da 
                                                                                            fun��o somo a Qtde deste item do ORC tamb�m ...*/
                                                                                            if($queima_estoque == 'S') $estoque_queima+= $qtde;

                                                                                            //$preco_excesso = vendas::calcular_preco_de_queima_pa($id_produto_acabado, $id_orcamento_venda);
                                                                                    ?>
                                                                                    <font title='Est. Disp. Excesso (Todos PAs Atrelados)' style='cursor:help'>
                                                                                        <br>EQ: <?=number_format($estoque_queima, 2, ',', '.');?>
                                                                                    </font>
                                                                                    <img src = '../../../../imagem/queima_estoque.png' id='img_queima_estoque' title='Excesso de Estoque' alt='Excesso de Estoque' onclick="copiar_preco_queima_estoque('<?=number_format($preco_excesso, 2, ',', '.');?>', '<?=$estoque_queima;?>')" border='0'>
                                                                                    <font color='darkblue'>
                                                                                        <?//=$tipo_moeda.number_format($preco_excesso, 2, ',', '.');?>
                                                                                    </font>
                                                                                    <!--Esse hidden est� sendo utilizado para valida��o de JS-->
                                                                                    <input type='hidden' name="hdd_quantidade_queima" value='<?=$estoque_queima;?>'>
                                                                                    <?
                                                                                        }
                                                                                    ?>
										</td>
										<td bgcolor='green' colspan='2'>
                                                                                    <font color='white'>
                                                                                        Pzo Entrega deste Item neste Or�amento: 
                                                                                    </font>
                                                                                    <select name='cmb_prazo_entrega' title='Selecione o Prazo de Entrega' class='combo'>
                                                                                        <option value='' style='color:red'>SELECIONE</option>
                                                                                        <?
                                                                                            foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
                                                                                                if($prazo_entrega_item == $indice) {//Se igual seleciona esse valor
                                                                                        ?>
                                                                                        <option value='<?=$indice;?>' selected><?=$prazo_entrega;?></option>
                                                                                        <?
                                                                                                }else {
                                                                                        ?>
                                                                                        <option value='<?=$indice;?>'><?=$prazo_entrega;?></option>
                                                                                        <?
                                                                                                }
                                                                                            }
                                                                                        ?>
                                                                                    </select>
                                                                                    <?
                                                                                        if($prazo_entrega_item == 'P') echo '<font color="white"><b> / '.number_format($qtde_disponivel, 2, ',', '.').' '.$sigla;
                                                                                    ?>
										</td>
									</tr>
									<tr class="linhanormalescura">
										<td>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
											<?
												if($referencia == 'ESP') {//Produto do Tipo Especial
													echo '<b>PRE&Ccedil;O FAT. IDEAL '.$tipo_moeda.' / P�:</b>';
												}else {//Produto do Tipo Normal
													echo '<b>PRE&Ccedil;O L�Q. FAT. '.$tipo_moeda.':</b>';
												}
											?>
											</font>
										</td>
										<td>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
												<b>DESCONTOS %</b>
											</font>
										</td>
										<td>
                                                                                    <font size='-5' face='Verdana, Arial, Helvetica, sans-serif'>
                                                                                        <b>Pr.L�q.Final <?=$tipo_moeda;?> | P. M�n <?=$tipo_moeda;?></b>
                                                                                    </font>
										</td>
										<td>
											<font size='-5' face='Verdana, Arial, Helvetica, sans-serif'>
												<b>TOTAL <?=$tipo_moeda;?> LOTE:</b>
											</font>
										</td>
									</tr>
									<?
										//Se existir alguma promo��o ou se foi marcada a op��o Queima de Estoque ...
										if($promocao != 'N' || $queima_estoque == 'S') {
                                                                                    //Desabilita as Caixas de Desconto Extra e Acr�scimo Extra quando tem Promo��o ...
                                                                                    $class      = 'textdisabled';
                                                                                    $disabled 	= 'disabled';
										}
									?>
									<tr class='linhanormal'>
										<td>
											<input type='text' name="txt_preco_liq_fat" value="<?=number_format($preco_liq_fat, 2, ',', '.');?>" title="Digite o Pre�o Liq. Fat. <?=$tipo_moeda;?>" size="15" maxlength="15" class='caixadetexto2' disabled>
<?
//Significa que � um PA ESP e que � do Tipo Revenda
											if($exibir_combo == 1) {
												echo "<font color = 'red'> / </font>";
?>
<!--Observa��o:
Aqui nessa combo eu armazeno o do id_fornecedor porque segundo o Roberto toda vez em que eu trocar o Lote 
M�nimo do Fornecedor, ent�o ... eu tamb�m tenho que trocar o Fornecedor Default deste PA ...-->
											<select name="cmb_lote_minimo" title="Selecione o Lote M�nimo" onchange="alterar_fornecedor_default('<?=$posicao;?>')" class='combo'>
											<?
//O primeiro passo a fazer � verificar qual o Fornecedor Default do PA Revenda ...
												$id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1);
//Verificar todos os Fornecedores que est�o atrelados a este PA ...
												$sql = "SELECT fpi.id_fornecedor, pa.id_produto_insumo 
                                                                                                        FROM `produtos_acabados` pa 
                                                                                                        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` 
                                                                                                        WHERE pa.`id_produto_acabado` = '".$id_produto_acabado."' 
                                                                                                        AND pa.`ativo` = '1' 
                                                                                                        AND fpi.`ativo` = '1' ";
												$campos2 = bancos::sql($sql);
												$linhas2 = count($campos2);
												for($i = 0; $i < $linhas2; $i++) {
													$sql = "SELECT lote_minimo_pa_rev 
                                                                                                                FROM `fornecedores_x_prod_insumos` 
                                                                                                                WHERE `id_fornecedor`= ".$campos2[$i]['id_fornecedor']." 
                                                                                                                AND `id_produto_insumo`= ".$campos2[$i]['id_produto_insumo']." ORDER BY lote_minimo_pa_rev LIMIT 1 ";
													$campos_lote_min = bancos::sql($sql);
/*Se o Fornecedor Corrente do Loop do PA � o mesmo que o Fornecedor Default, ent�o este vem selecionado 
na combo ...*/
													if($id_fornecedor_setado == $campos2[$i]['id_fornecedor']) {
											?>
												<option value="<?=$campos2[$i]['id_fornecedor'];?>" selected><?=$campos_lote_min[0]['lote_minimo_pa_rev'];?></option>
											<?
													}else {
											?>
												<option value="<?=$campos2[$i]['id_fornecedor'];?>"><?=$campos_lote_min[0]['lote_minimo_pa_rev'];?></option>
											<?
													}
												}
											?>
											</select>
										<?
											}else {
                                                                                            echo $qtde_lotes_orcaveis;
											}
										?>
										</td>
										<td>
                                                                                <?
                                                                                    if($id_cliente_tipo == 4) {//Se o Cliente � Ind�stria, nunca podemos dar Desconto Extra ...
                                                                                        /*Esses s�o os �nicos funcion�rios que podem mudar o desconto 
                                                                                        Extra: Roberto 62, Wilson 68, D�rcio 98, Nishimura 136 ...*/
                                                                                        $vetor_funcionarios_podem_mudar_desconto_extra = array(62, 68, 98, 136);
                    
                                                                                        if(in_array($_SESSION['id_funcionario'], $vetor_funcionarios_podem_mudar_desconto_extra)) {
                                                                                            $disabled_desconto_extra    = $disabled;
                                                                                            $class_desconto_extra       = $class;
                                                                                        }else {//Vai se comportar de acordo com o Tipo de Cliente ...
                                                                                            $disabled_desconto_extra    = 'disabled';
                                                                                            $class_desconto_extra       = 'textdisabled';
                                                                                        }
                                                                                    }else {//Vai se comportar de acordo com o Tipo de Cliente ...
                                                                                        $disabled_desconto_extra    = $disabled;
                                                                                        $class_desconto_extra       = $class;
                                                                                    }
                                                                                ?>
											Extra: <input type='text' name='txt_desconto_porc_extra' value='<?=number_format($desconto_extra, 2, ',', '.');?>' title="Digite o Desconto % Extra" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);preco_liquido_final()" size='6' maxlength='7' class='<?=$class_desconto_extra;?>' <?=$disabled_desconto_extra;?>>
											&nbsp;
											Cliente %: 
											<input type='text' name="txt_desconto_cliente_porc" value="<?=number_format($desconto_cliente, 2, ',', '.');?>" title="Desconto % Cliente" onKeyUp="verifica(this, 'moeda_especial', '', '', event)" size='6' maxlength='7' class='caixadetexto2' disabled>
										</td>
										<td bgcolor='green'>
                                                                                    <input type='text' name='txt_preco_liquido_final_rs' title='Pre&ccedil;o Liq. Final <?=$tipo_moeda;?>' size='8' maxlength='9' class='caixadetexto2' style='color:white' disabled>
                                                                                    |
                                                                                    <?
                                                                                        $vetor_valores      = vendas::preco_minimo_venda($id_orcamento_venda_item);
                                                                                        $preco_minimo_venda = $vetor_valores['preco_minimo_venda'];
                                                                                        //Somente para esses logins: Rivaldo, Rodrigo, Roberto, Wilson Chefe, Fabio Petroni, D�rcio, Bispo, Wilson Nishimura e Netto ...
                                                                                        if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 125 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
                                                                                            $type = 'text';
                                                                                        }else {
                                                                                            $type = 'hidden';
                                                                                        }
                                                                                    ?>
                                                                                    <input type='<?=$type;?>' name='txt_preco_minimo_venda' value='<?=number_format($preco_minimo_venda, 2, ',', '.');?>' title='Pre�o M�nimo de Venda' size='8' maxlength='9' class='caixadetexto2' style='color:yellow' disabled>
										</td>
										<td>
                                                                                    <?=$tipo_moeda;?><input type='text' name='txt_total_rs_sem_impostos' title='Total <?=$tipo_moeda;?> Lote s/ IPI' onkeyup="verifica(this, 'moeda_especial', '', '', event)" size='8' maxlength='8' class='caixadetexto2' disabled>
                                                                                    c/ IPI + ST = <?=$tipo_moeda;?><input type='text' name='txt_total_rs_com_impostos' title='Pre�o TOTAL <?=$tipo_moeda;?> LOTE + IPI + ST' size='8' maxlength='8' class='caixadetexto2' disabled>
										</td>
									</tr>
									<tr class='linhanormal'>
										<td>
                                                                                    Tx. Financeira: 
                                                                                    <input type='text' name="txt_taxa_financeira" value="<?=number_format($tx_financeira, 2, ',', '.');?>" title="Taxa Financeira" onKeyUp="verifica(this, 'moeda_especial', '', '', event)" size="10" maxlength="15" class='caixadetexto2' disabled>
										</td>
										<td>
											Acr�sc.: 
											<input type='text' name="txt_acrescimo_extra_porc" value="<?=number_format($acrescimo_extra, 2, ',', '.');?>" title="Digite o Acr�scimo Extra %" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event);preco_liquido_final()" size="6" maxlength="7" class="<?=$class;?>" <?=$disabled;?>>
											&nbsp;
											<?
                                                                                            if($nota_sgd == 'S') {//SGD - Sem Nota
                                                                                                echo 'SGD: ';
                                                                                            }else {//NF - Com Nota
                                                                                                echo 'ICMS: ';
                                                                                            }
											?>
											<input type='text' name="txt_desc_sgd_icms" value="<?=number_format($desc_sgd_icms, 1, ',', '.');?>" title="Desconto SGD/ICMS" onKeyUp="verifica(this, 'moeda_especial', '', '', event)" size="6" maxlength="7" class='caixadetexto2' disabled>
										</td>
                                                                                <td bgcolor='green'>
                                                                                    <font color='white'>
                                                                                    <?
                                                                                        $vetor_logins_com_acesso_margens_lucro = vendas::logins_com_acesso_margens_lucro();
                                                                                        
                                                                                        if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                                                                                            $valores = vendas::calcular_ml_min_pa_vs_cliente($id_produto_acabado, $id_cliente);
                                                                                            echo '<b>80%MLMin=</b>'.number_format(0.8 * $valores['margem_lucro_minima'], 1, ',', '.');
                                                                                            echo ' <b>MLG=</b>'.number_format($margem_lucro, 1, ',', '.');
                                                                                            echo ' <b>ML Est=</b>'.number_format($margem_lucro_estimada, 1, ',', '.').'%';
                                                                                        }
                                                                                    ?>
                                                                                    </font>
                                                                                </td>
										<?
//S� aparecer� a Parte de Promo��o para os Produtos Normais e p/ Pa�ses Nacionais
                                                                                    if($referencia != 'ESP' && $id_pais == 31) {
											if($preco_promocional != '0.00' || $preco_promocional_b != '0.00') {
											
										?>
										<td>
                                                                                    <font color='green'>
                                                                                        <b>PROMO��O</b>
                                                                                    </font>
                                                                                    <font color='blue'>
                                                                                        <b><?=date('Y')?>:</b>
                                                                                    </font>
                                                                                    <?
                                                                                        /*N�o podemos colocar Pre�o de Promo��o no Item se este j� estiver com a op��o 
                                                                                        de Excesso de Estoque marcada porque o(s) vendedor(es) estavam fazendo trambicagem p/ aumentar a 
                                                                                        Comiss�o atrav�s dessa op��o ...*/
                                                                                        if($queima_estoque == 'S') {
                                                                                            $disabled_promocao  = 'disabled';
                                                                                            $class_promocao     = 'textdisabled';
                                                                                        }else {
                                                                                            $class_promocao     = 'combo';
                                                                                        }
                                                                                    ?>
                                                                                    <!--Se o Item estiver com Promo��o, ent�o n�o posso permitir que sejam dados mais descontos ainda-->
                                                                                    <select name='cmb_promocao' title='Selecione a Promo��o' onchange='calcular_preco_promocional(this.value)' style='background:yellow; color:green' class='<?=$class_promocao;?>' <?=$disabled_promocao;?>>
                                                                                        <option value='' style="color:red" selected>SEM PROMO��O</option>
                                                                                    <?
                                                                                        if($promocao == 'A') {//Usu�rio escolheu Pre�o A ...
                                                                                            $selecteda = 'selected';
                                                                                        }else if($promocao == 'B') {//Usu�rio escolheu Pre�o B ...
                                                                                            $selectedb = 'selected';
                                                                                        }else if($promocao == 'C') {//Usu�rio escolheu Pre�o C ...
                                                                                            $selectedc = 'selected';
                                                                                        }
                                                                                        if($preco_promocional != 0 && $nota_sgd == 'N') {//Existe Pre�o A p/ o PA somente se for "NF" ...
                                                                                    ?>
                                                                                            <option value='A' <?=$selecteda;?>><?='PROMO A | '.$qtde_promocional.' '.$sigla.' | '.$tipo_moeda.number_format($preco_promocional, 2, ',', '.');?></option>
                                                                                    <?
                                                                                            /******************************Pre�o C******************************/
                                                                                            //Se existe Pre�o A, consequentemente existe Pre�o C ...
                                                                                            if($preco_promocional_b == 0) {//N�o existe Pre�o B p/ o PA ...
                                                                                                //$preco_promocional_c = ($preco_promocional * 1.1);//Adicionamos 10% em cima do Pre�o A ...
                                                                                            }else {
                                                                                                //$preco_promocional_c = ($preco_promocional + $preco_promocional_b) / 2;
                                                                                            }
                                                                                            //Sempre a Qtde Promocional C ser� baseada na Qtde Promocional A ...
                                                                                            $qtde_promocional_c = $qtde_promocional;
                                                                                            /*******************************************************************/
                                                                                        }
                                                                                        if($preco_promocional_b != 0 && $nota_sgd == 'S') {//Existe Pre�o B p/ o PA somente se for "SGD" ...
                                                                                    ?>
                                                                                            <option value='B' <?=$selectedb;?>><?='PROMO B | '.$qtde_promocional_b.' '.$sigla.' | '.$tipo_moeda.number_format($preco_promocional_b, 2, ',', '.');?></option>
                                                                                    <?
                                                                                        }
                                                                                        if($preco_promocional_c != 0) {//Existe Pre�o C p/ o PA ...
                                                                                    ?>
                                                                                            <option value='C' <?=$selectedc;?>><?='PROMO C | '.$qtde_promocional_c.' '.$sigla.' | '.$tipo_moeda.number_format($preco_promocional_c, 2, ',', '.');?></option>
                                                                                    <?
                                                                                        }
                                                                                    ?>
                                                                                    </select>
										</td>
										<?
											}
                                                                                    }
										?>
									</tr>
									<tr class='linhanormal' align='center'>
                                                                            <td colspan='4'>
                                                                                <label id='lbl_mensagem' style='visibility:hidden'>
                                                                                    <font color='red' size='5'>
                                                                                        <b>SALVE P/ RECALCULAR PRE�O(S) P/ ESTA QUANTIDADE !</b>
                                                                                    </font>
                                                                                </label>
                                                                                <br/>
                                                                            <?
                                                                                /**************************************************************************************/
                                                                                $fora_custo = vendas::verificar_orcamento_item_fora_custo($id_orcamento_venda_item);
                                                                                if($fora_custo == 'S') {
                                                                            ?>
                                                                                <font class='piscar'>
                                                                                    <font color='red' size='5'>
                                                                                        <b>
                                                                                        <?
                                                                                            /*Por conta da Crise estamos ignorando esse caminho Pre�o 
                                                                                            fora de Custo, deixando apenas a ML FORA de Custo 
                                                                                            desde 18/06/2015 ...*/
                                                                                            /*if($preco_liq_final < $preco_minimo_venda) {
                                                                                                echo 'PRE�O FORA DE CUSTO';
                                                                                            }else {
                                                                                                echo 'ML FORA DE CUSTO';
                                                                                            //}*/
                                                                                            echo 'FORA DE CUSTO';
                                                                                        ?>
                                                                                        </b>
                                                                                    </font>
                                                                                </font>
                                                                                <br/>
                                                                                <!--Tenho q colocar a fun��o depois, pq sen�o n�o � reconhecida a Tag "Font" que foi criada antes usando o atributo Piscar ...-->
                                                                                <Script Language = 'JavaScript'>
                                                                                    function blink(selector) {
                                                                                        $(selector).fadeOut('slow', function() {
                                                                                            $(this).fadeIn('slow', function() {
                                                                                                blink(this);
                                                                                            });
                                                                                        });
                                                                                    }
                                                                                    blink('.piscar');
                                                                                </Script>
                                                                            <?
                                                                                }
                                                                                /**************************************************************************************/
                                                                                //Aqui eu busco o �ltimo Pre�o de Pedido desse Item nos �ltimos 2 anos ...
                                                                                $sql = "SELECT ov.nota_sgd, DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, ov.prazo_a, 
                                                                                        ov.prazo_b, ov.prazo_c, ov.prazo_d, ov.prazo_medio, ovi.preco_liq_final 
                                                                                        FROM `orcamentos_vendas_itens` ovi 
                                                                                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.id_cliente = '$id_cliente' AND ov.id_orcamento_venda <> '$id_orcamento_venda' AND ov.`data_emissao` > DATE_ADD('".date('Y-m-d')."', INTERVAL -765 DAY) 
                                                                                        WHERE ovi.id_produto_acabado = '$id_produto_acabado' 
                                                                                        AND ovi.status > '0' ORDER BY ov.data_emissao DESC LIMIT 1 ";
                                                                                $campos_ultimo_orc = bancos::sql($sql);
                                                                                if(count($campos_ultimo_orc) == 1) {
                                                                                    $prazo_faturamento = ($campos_ultimo_orc[0]['nota_sgd'] == 'N') ? 'NF' : 'SGD';
                                                                                    $prazo_faturamento.= ' / '.$campos_ultimo_orc[0]['prazo_a'];
//Se existirem os D+ prazos da� eu vou printando ...
                                                                                    if(!empty($campos_ultimo_orc[0]['prazo_b'])) $prazo_faturamento.= '-'.$campos_ultimo_orc[0]['prazo_b'];
                                                                                    if(!empty($campos_ultimo_orc[0]['prazo_c'])) $prazo_faturamento.= '-'.$campos_ultimo_orc[0]['prazo_c'];
                                                                                    if(!empty($campos_ultimo_orc[0]['prazo_d'])) $prazo_faturamento.= '-'.$campos_ultimo_orc[0]['prazo_d'];
                                                                        ?>
                                                                                <a onclick="if(document.form.txt_preco_liq_final_desejado.disabled) {alert('DESABILITE PRE�O PROMOCIONAL P/ UTILIZAR ESTA OP��O !');}else {copiar_ultimo_preco_negociado('<?=$campos_ultimo_orc[0]['nota_sgd'];?>', '<?=$campos_ultimo_orc[0]['prazo_medio'];?>', '<?=number_format($campos_ultimo_orc[0]['preco_liq_final'], 2, ',', '.');?>')}" title="Copiar �ltimo Pre�o Negociado" style='cursor:help' class="link">
                                                                                    <font color='red'>
                                                                                        �ltimo Pre�o <?=$tipo_moeda.number_format($campos_ultimo_orc[0]['preco_liq_final'], 2, ',', '.').' em '.$campos_ultimo_orc[0]['data_emissao'].' - Forma de Venda: '.$prazo_faturamento;?>
                                                                                    </font>
                                                                                </a>
                                                                        <?
                                                                                }else {
                                                                                    echo '<font color="red"><b>S/ PRE�O NO(S) �LTIMO(S) 2 ANO(S) - </b></font>';
                                                                                }
                                                                        ?>
                                                                                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                                                                                        Pre&ccedil;o Liq. Final <?=$tipo_moeda;?> Desejado: 
                                                                                </font>
                                                                                <input type='text' name="txt_preco_liq_final_desejado" title="Pre&ccedil;o Liq. Final <?=$tipo_moeda;?>" onkeyup="verifica(this, 'moeda_especial', 2, '', event);calcular_desconto_extra();validar_item(event)" size="15" maxlength="15" class="<?=$class;?>" <?=$disabled;?>>
                                                                                &nbsp;
                                                                                <a href="#" onclick="nova_janela('ultima_venda_cliente.php?id_orcamento_venda_item=<?=$id_orcamento_venda_item;?>', 'ULTIMA_VENDA', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes da �ltima Venda" class="link">
                                                                                    <img src = '../../../../imagem/detalhes_ultima_venda.png' title='Detalhes da �ltima Venda' alt='Detalhes da �ltima Venda' width='30' height='22' border='0'>
                                                                                </a>
                                                                                <?
                                                                                        if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                                                                                            $margem         = custos::margem_lucro($id_orcamento_venda_item, $tx_financeira, $id_uf, $preco_liq_final);
                                                                                ?>
                                                                                <a href='alterar_margem_lucro.php?id_orcamento_venda_item=<?=$id_orcamento_venda_item;?>&preco_liq_final=<?=number_format($preco_liq_final, 2, ',', '.');?>&margem_lucro=<?=$margem[1];?>' class='html5lightbox'>
                                                                                    <img src = '../../../../imagem/margem_lucro.png' title='Margem de Lucro' alt='Margem de Lucro' border='0'>
                                                                                </a>
                                                                                <?
                                                                                        }
                                                                                ?>
                                                                            </td>
									</tr>
									<tr class='linhanormalescura'>
										<td>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
												<b>PESO (KG):</b>
											</font>
										</td>
										<td>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
												<b>COMISS�O:</b>
											</font>
										</td>
										<td colspan='2'>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
												<b>DADOS ADICIONAIS:</b>
											</font>
										</td>
									</tr>
									<tr class='linhanormal'>
										<td>
											<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
												Peso / P�:
											</font>
											<input type='text' name="txt_peso_por_pc_kg" value="<?=number_format($peso_por_pecas_kg, 4, ',', '.')?>" title="Digite o Peso / P� (KG)" onKeyUp="verifica(this, 'moeda_especial', '', '', event)" size="10" maxlength="15" class='caixadetexto2' disabled>
										</td>
										<td>
											<font color="darkblue">
											<?
//Atrav�s do id_representante j� busca o nome fantasia do representante
												$sql = "SELECT nome_fantasia 
                                                                                                        FROM `representantes` 
                                                                                                        WHERE `id_representante` = '$id_representante' LIMIT 1 ";
												$campos_representante = bancos::sql($sql);
												echo $campos_representante[0]['nome_fantasia'];
											?>
											</font>
											-
											<font title="Levamos em conta a dif. do ICMS p/UF = SP x desc.ICMS/SGD e a dif.da tx.fin.p/30 ddl x tx.fin.pz.medio do ORC" style='cursor:help'>
												Desc. p/ Com.:
											</font>
											<input type='text' name="txt_desconto_total" title="Desconto Total" size="7" maxlength="7" class='caixadetexto2' disabled>
										</td>
										<td>
											Pis + Cofins:
											<font color='red'>(S� na NF)</font>
										<?
											if($conceder_pis_cofins == 'S') {
										?>
											<input type='text' name="txt_pis_confins" value="<?=number_format(genericas::variavel(20)+genericas::variavel(21), 2, ',', '.');?>" title="Taxa Financeira" onKeyUp="verifica(this, 'moeda_especial', '', '', event)" size="7" maxlength="7" class='caixadetexto2' disabled>
										<?
											}else {
												echo '� Concedido.';
											}
										?>
										</td>
										<td>
											ICMS = <?=number_format($icms, 2, ',', '.');?>%
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<?
											$preco_total_lote 	= $preco_liq_final * $qtde;
										?>
											IPI = <?=$ipi;?>%
											<?=$tipo_moeda;?><input type='text' name='txt_total_ipi' value="<?=number_format($preco_total_lote * ($ipi / 100), 2, ',', '.');?>" title='Total de IPI' size='7' maxlength='7' class='textdisabled' disabled>
											<input type='hidden' name='hdd_aliquota_ipi' value='<?=$ipi;?>'>
										</td>
									</tr>
									<tr class='linhanormal'>
										<td>
											Peso do Lote (KG):
											<?$peso_lote_kg = $qtde * $peso_por_pecas_kg;?>
											<input type='text' name="txt_peso_lote_kg" value="<?=number_format($peso_lote_kg, 3, ',', '.');?>" title="Digite o Peso do Lote (KG)" onKeyUp="verifica(this, 'moeda_especial', '', '', event)" size="10" maxlength="15" class='caixadetexto2' disabled>
										</td>
										<td>
                                                                                    <div id='div_comissao'>
                                                                                        <input type='text' name='txt_porc_comissao' value='<?=number_format($comissao_new + $comissao_extra, 2, ',', '.');?>' title='Digite o % Comiss�o' size='3' maxlength='4' class='caixadetexto2' disabled> % - 
                                                                                        <font title='Total p/ Comiss�o' style='cursor:help'>
                                                                                            <?=$tipo_moeda;?>
                                                                                        </font>
                                                                                        <input type='text' name='txt_porc_comissao_rs' value='<?=number_format($preco_total_lote * ($comissao_new + $comissao_extra) / 100, 2, ',', '.');?>' size='7' maxlength='7' class='caixadetexto2' disabled>
                                                                                    </div>
                                                                                    <div id='div_loading' style='visibility:hidden'>
                                                                                        <img src = '../../../../css/little_loading.gif' size='20' height='20'>
                                                                                        <font size='1.5' color='brown'>
                                                                                            <b>Calculando Comiss�o ...</b>
                                                                                        </font>
                                                                                    </div>
										</td>
										<td>
											Pre&ccedil;o / Kg:
											<?
//C�lculo do Pre�o por Kilo, para n�o dar erro de Divis�o por Zero ...
												$preco_por_kilo = ($peso_por_pecas_kg != 0) ? $preco_liq_final / round($peso_por_pecas_kg, 4) : $preco_liq_final;
											?>
											<input type='text' name="txt_preco_por_kilo" value="<?=number_format($preco_por_kilo, 2, ',', '.');?>" title="Pre�o / Kg" size="8" maxlength="12" class='textdisabled' disabled>
										</td>
										<td>
											Red. BC = <?=number_format($reducao, 2, ',', '.');?>%
											<?
                                                                                            //Aqui eu verifico se existe IVA ...
                                                                                            if($iva > 0) {
                                                                                                $calculo_impostos_item  = calculos::calculo_impostos($id_orcamento_venda_item, $id_orcamento_venda, 'OV');
                                                                                                $aliquota_iva           = ($preco_total_lote != 0) ? ($calculo_impostos_item['valor_icms_st'] * 100) / $preco_total_lote : 0;
                                                                                            }else {
                                                                                                $aliquota_iva = 0;
                                                                                            }
											?>
											&nbsp;&nbsp;
											ICMS ST = 
											<font title="IVA -> <?=number_format($iva, 2, ',', '.');?>" style='cursor:help'>
												<?=number_format($aliquota_iva, 2, ',', '.');?>%
											</font>
											<?=$tipo_moeda;?><input type='text' name="txt_total_icms_st" value="<?=number_format($calculo_impostos_item['valor_icms_st'], 2, ',', '.');?>" title="Total de ICMS ST" size="7" maxlength="7" class='textdisabled' disabled>
											<!--N�o posso arredondar o IVA p/ 2 casas pq sen�o d� erro de c�lculo do ICMS ST ...-->
											<input type="hidden" name="hdd_aliquota_iva" value="<?=str_replace('.', ',', $aliquota_iva);?>">
										</td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
					<tr>
						<td colspan='4'>
							<fieldset>
								<legend>
									<span style="cursor: pointer;">
										<font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'><b>
											DECOMPOR DESCONTO EXTRA
										</b></font>
									</span>
								</legend>
								<table border="0" width="100%" cellspacing='1' cellpadding='1' align='center'>
                                                                    <tr class='linhanormal'>
                                                                        <td>
                                                                            Desconto A: <input type='text' name="txt_desc_a" title="Digite o Desconto A" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calcular_desconto_extra(1)" size="7" maxlength="7" class="<?=$class;?>" <?=$disabled;?>>
                                                                        </td>
                                                                        <td>
                                                                            Desconto B: <input type='text' name="txt_desc_b" title="Digite o Desconto B" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calcular_desconto_extra(1)" size="7" maxlength="7" class="<?=$class;?>" <?=$disabled;?>>
                                                                        </td>
                                                                        <td>
                                                                            Desconto C: <input type='text' name="txt_desc_c" title="Digite o Desconto C" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calcular_desconto_extra(1)" size="7" maxlength="7" class="<?=$class;?>" <?=$disabled;?>>
                                                                        </td>
                                                                        <td>
                                                                            Desconto D: <input type='text' name="txt_desc_d" title="Digite o Desconto D" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calcular_desconto_extra(1)" size="7" maxlength="7" class="<?=$class;?>" <?=$disabled;?>>
                                                                        </td>
                                                                    </tr>
								</table>
							</fieldset>
						</td>
					</tr>
					<tr align='center'>
						<td colspan="5">
						<?
///////////////////////////////PAGINA��O CASO ESPECIFICA PARA ESTA TELA///////////////////////////////////////
							if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
							for($i = 1; $i <= $qtde_itens; $i++) {
                                                            if($i % 40 == 0) echo '<br>';//Quebro a linha porque n�o estoura o limite da Tela ...
                                                            
                                                            if($i == $posicao) {
                                                                echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                                                            }else {
                                                                echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                                                            }
							}
							if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Pr�xima &gt;&gt; </font></a>&nbsp;</b>";
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						?>
							<br/>
                                                        &nbsp;
                                                        <label id='lbl_loading'></label>
							<img name="img_salvar" id="img_salvar" src="../../../../imagem/menu/salvar.png" width="24" height="24" onclick="executar_opcoes(1)" title="Salvar">
							<img name="img_follow_up" id="img_follow_up" src="../../../../imagem/menu/alterar.png" width="24" height="24" onclick="executar_opcoes(3)" title="Follow-UP">
							&nbsp;
							<img src = "../../../../imagem/menu/adicao.jpeg" width="24" height="24" title="Incluir Item(ns)" alt="Incluir Item(ns)" onclick="document.form.hdd_ir_para_incluir.value=1;executar_opcoes(1)" border='0'>
							<div id="executar_opcoes" align='center'></div>
						</td>
					</tr>
				</table>
			</form>
<iframe name='iframe_calcular_comissoes' id='iframe_calcular_comissoes' frameborder='0' vspace='0' hspace='0' marginheight='0' marginwidth='0' scrolling='yes' title='Calcular Comiss�es' width='0' height='0'></iframe>
</body>
</html>
<?
/*Aqui eu j� pego a Taxa Financeira atrav�s do or�amento, aonde eu utilizo para
calcular na fun��o de calcular_comissoes() em JavaScript abaixo*/
$tx_financeira = custos::calculo_taxa_financeira($id_orcamento_venda);
//////////////////////////////////////////////////////////////////////////////////
/*Joguei essas fun��es na parte de baixo, para aproveitar algumas vari�veis carregadas
acima pelo PHP no decorrer desse arquivo ...*/?>
<Script Language = 'JavaScript'>
function calcular_comissoes() {
    document.getElementById('div_comissao').style.visibility    = 'hidden'
    document.getElementById('div_loading').style.visibility     = 'visible'

    //Aqui nessa primeira parte da fun��o eu c�lculo o Desconto Total via JavaScript mesmo ...
    var desconto_porc_extra 	= (document.form.txt_desconto_porc_extra.value != '') ? eval(strtofloat(document.form.txt_desconto_porc_extra.value)) : 0
    var acrescimo_extra_porc 	= (document.form.txt_acrescimo_extra_porc.value != '') ? eval(strtofloat(document.form.txt_acrescimo_extra_porc.value)) : 0
    var desconto_cliente_porc 	= (document.form.txt_desconto_cliente_porc.value != '') ? eval(strtofloat(document.form.txt_desconto_cliente_porc.value)) : 0
    var tx_financeira           = eval('<?=$tx_financeira;?>')

    var coeficiente             = (1 - desconto_cliente_porc / 100) * (1 - desconto_porc_extra / 100) * (1 + acrescimo_extra_porc / 100) * (1 - tx_financeira / 100)
    var desconto_total          = (1 - coeficiente) * 100

    document.form.txt_desconto_total.value = desconto_total
    document.form.txt_desconto_total.value = arred(document.form.txt_desconto_total.value, 2, 1)
/******************************************************************************************************/
//Aqui eu c�lculo as Comiss�es em outro arquivo via PHP pela fun��o do Gomes ...
    var preco_liquido_final	= (document.form.txt_preco_liquido_final_rs.value != 'CALCULANDO ...' && document.form.txt_preco_liquido_final_rs.value != 'Adequar a qtde or��vel / chamar Depto. T�cnico !') ? eval(strtofloat(document.form.txt_preco_liquido_final_rs.value)) : 0
    var preco_total_lote 	= eval(strtofloat(document.form.txt_total_rs_sem_impostos.value))
    iframe_calcular_comissoes.location = 'calcular_comissoes.php?id_orcamento_venda_item=<?=$id_orcamento_venda_item;?>&qtde='+document.form.txt_quantidade.value+'&preco_liquido_final='+preco_liquido_final+'&preco_total_lote='+preco_total_lote
}

//Substitui��o do Pre�o L�quido, caso o usu�rio selecione o checkbox Pre�o Promocional ...
function calcular_preco_promocional(indice_combo) {
    var preco_excesso   = eval('<?=$preco_excesso;?>')
    var id_pais   	= eval('<?=$id_pais;?>')
    var dolar_dia 	= eval('<?=$dolar_dia?>')
    var referencia 	= '<?=$referencia;?>'
    var queima_estoque  = '<?=$queima_estoque;?>'
    if(document.form.cmb_promocao.value != '') {//Selecionou Promo��o
//Igualo tanto o Pre�o da Promo��o como a Qtde da Promo��o ...
        if(indice_combo == 'A') {
            var preco_promocional   = '<?=$preco_promocional;?>'
            var qtde_promocional    = eval('<?=$qtde_promocional;?>')
//Aqui eu verifico se a Qtde da Promo��o � maior do que o Digitado pelo usu�rio na caixa Qtde ...
            if(qtde_promocional > document.form.txt_quantidade.value) {//Se Maior assume a Qtde da Promo��o ...
                document.form.txt_quantidade.value = qtde_promocional
            }
        }else if(indice_combo == 'B') {
            var preco_promocional   = '<?=$preco_promocional_b;?>'
            var qtde_promocional    = eval('<?=$qtde_promocional_b;?>')
//Aqui eu verifico se a Qtde da Promo��o � maior do que o Digitado pelo usu�rio na caixa Qtde ...
            if(qtde_promocional > document.form.txt_quantidade.value) {//Se Maior assume a Qtde da Promo��o ...
                document.form.txt_quantidade.value = qtde_promocional
            }
        }else if(indice_combo == 'C') {
            var preco_promocional   = '<?=$preco_promocional_c;?>'
            var qtde_promocional    = eval('<?=$qtde_promocional_c;?>')
//Aqui eu verifico se a Qtde da Promo��o � maior do que o Digitado pelo usu�rio na caixa Qtde ...
            if(qtde_promocional > document.form.txt_quantidade.value) {//Se Maior assume a Qtde da Promo��o ...
                document.form.txt_quantidade.value = qtde_promocional
            }
        }else {
            var preco_promocional = 0
            document.form.txt_quantidade.value = eval('<?=$qtde;?>')
        }
        if(document.form.txt_preco_liq_fat.value != 'DEPTO T�CNICO' && document.form.txt_preco_liq_fat.value != 'Or�ar') {
            var preco_liq_fat = eval(strtofloat(document.form.txt_preco_liq_fat.value))
            if(preco_liq_fat == 0) {//Se n�o existir pre�o L�quido Faturado do PA, ent�o retorna um dos alerts abaixo ...
                if(referencia == 'ESP') {//Especial - Depto. T�cnico ...
                    alert('O PRE�O L�QUIDO FATURADO = R$ 0,00 ! PRECISA SER DEFINIDO O CUSTO DESSE PRODUTO, AVISAR DEPTO. T�CNICO !!!')
                }else {//Normal de Linha - Roberto ...
                    alert('O PRE�O L�QUIDO FATURADO = R$ 0,00 ! PRECISA SER DEFINIDO O PRE�O DE LISTA DESSE PRODUTO, AVISAR ROBERTO !!!')
                }
                return false
            }
        }
        document.form.txt_preco_liq_final_desejado.value    = preco_promocional
        document.form.txt_preco_liq_final_desejado.value    = arred(document.form.txt_preco_liq_final_desejado.value, 2, 1)
//Desabilita as Caixas ...
        document.form.txt_desconto_porc_extra.disabled      = true
        document.form.txt_acrescimo_extra_porc.disabled     = true
        document.form.txt_preco_liq_final_desejado.disabled = true
        document.form.txt_desc_a.disabled                   = true
        document.form.txt_desc_b.disabled                   = true
        document.form.txt_desc_c.disabled                   = true
        document.form.txt_desc_d.disabled                   = true
//Layout de Desabilitado ...
        document.form.txt_desconto_porc_extra.className     = 'textdisabled'
        document.form.txt_acrescimo_extra_porc.className    = 'textdisabled'
        document.form.txt_preco_liq_final_desejado.className = 'textdisabled'
        document.form.txt_desc_a.className                  = 'textdisabled'
        document.form.txt_desc_b.className                  = 'textdisabled'
        document.form.txt_desc_c.className                  = 'textdisabled'
        document.form.txt_desc_d.className                  = 'textdisabled'
/******************************************************************************/
/**************Controle do Excesso de Estoque com a Promo��o A e B*************/
/******************************************************************************/
        if(indice_combo == 'A' || indice_combo == 'B') {
            if(preco_promocional >= preco_excesso) {
                //Se o item n�o estava em Queima, a partir desse instante passa a ficar ...
                if(queima_estoque == 'N') document.getElementById('img_queima_estoque').onclick()
            }
        }
/******************************************************************************/
    }else {//Tirou a Promo��o
        document.form.txt_quantidade.value                  = eval('<?=$qtde;?>')
//Limpa as caixas ...
        document.form.txt_desconto_porc_extra.value         = ''
        document.form.txt_acrescimo_extra_porc.value        = ''
        document.form.txt_preco_liq_final_desejado.value    = ''
        document.form.txt_desc_a.value                      = ''
        document.form.txt_desc_b.value                      = ''
        document.form.txt_desc_c.value                      = ''
        document.form.txt_desc_d.value                      = ''
//Habilita as caixas ...
        document.form.txt_desconto_porc_extra.disabled      = false
        document.form.txt_acrescimo_extra_porc.disabled     = false
        document.form.txt_preco_liq_final_desejado.disabled = false
        document.form.txt_desc_a.disabled                   = false
        document.form.txt_desc_b.disabled                   = false
        document.form.txt_desc_c.disabled                   = false
        document.form.txt_desc_d.disabled                   = false
//Layout de Habilitado ...
        document.form.txt_desconto_porc_extra.className     = 'caixadetexto'
        document.form.txt_acrescimo_extra_porc.className    = 'caixadetexto'
        document.form.txt_preco_liq_final_desejado.className= 'caixadetexto'
        document.form.txt_desc_a.className                  = 'caixadetexto'
        document.form.txt_desc_b.className                  = 'caixadetexto'
        document.form.txt_desc_c.className                  = 'caixadetexto'
        document.form.txt_desc_d.className                  = 'caixadetexto'
        //Se o item estiver em Queima, a id�ia � tir�-lo da Queima ...
        var queima_estoque                                  = '<?=$queima_estoque;?>'
        if(queima_estoque == 'S') document.getElementById('img_queima_estoque').onclick()
    }
    calcular_desconto_extra()
}
</Script>