<?
/*O Preço de Venda Fat. Inter R$ quando Fornecedor = Inter tem de usar a Moeda do Custo, quando for Hispania 
tem de usar da Moeda de Compra ...

O Preço Fat. Nac. em amarelo tem de ser o Nacional para Hispania e o Inter p/ Fornecedor Estrangeiro ...*/

require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');

if(!empty($tela)) {
    segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');
}else {
    session_start('funcionarios');//Esse procedimento é feito pq a função "setar_fornecedor_default" exige um id_funcionario ...
}

$mensagem[1] = "<font class='confirmacao'>CUSTO REVENDA ATUALIZADO COM SUCESSO.</font>";

//Aqui são as variáveis para o cálculo do custo
$taxa_financeira_vendas         = genericas::variaveis('taxa_financeira_vendas');
$fator_importacao               = genericas::variaveis('fator_importacao');
$taxa_financeiro                = genericas::variavel(4);
$desconto_snf                   = genericas::variavel(5);
$valor_moeda_dolar_custo        = genericas::variavel(7);
$valor_moeda_euro_custo         = genericas::variavel(8);
$fator_desconto_maximo_vendas   = genericas::variavel(19);
$outros_impostos_federais       = genericas::variavel(34);//Pis + cofins + csll + ir + refiz

/***************Atualização da Lista de Preço e do Custo de Revenda***************/
if($passo == 1) {
//Aqui eu busco a operação de custo do P.A.
    $sql = "SELECT `referencia`, `operacao_custo`, `status_custo` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    //Agora com o PA e a OC, busco o id_custo do PA ...
    $sql = "SELECT `id_produto_acabado_custo` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' 
            AND `operacao_custo` = '".$campos[0]['operacao_custo']."' LIMIT 1 ";
    $campos_custo = bancos::sql($sql);
//Só entra se encontrar o produto acabado na tela relacional de produtos_acabados_custos
    if(count($campos_custo) == 1) {
        $id_produto_acabado_custo = $campos_custo[0]['id_produto_acabado_custo'];
//Aqui é a Data de Atualização do Custo p/ que o sistema comece fazer a contagem a partir dos 90 dias
        $sql = "UPDATE `produtos_acabados_custos` 
                SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."' 
                WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
        bancos::sql($sql);
//Atualização do custo liberado para o produto acabado
        if(!empty($chkt_custo_liberado)) {//Selecionado
            $acao = 'SIM';
/***************************Tratamento de Margem de Lucro**************************/
//Agora se a M.L. for = 0, eu também não posso liberar o Custo do PA
            if($txt_fator_margem_lucro_pa == '0.00') {
                $acao = 'NAO';
            }else {
/**********************************************************************************/
//Antes de cair na função que já faz tudo automático, tem uma condição antes só para o caso o PA ser 'ESP'
                if($campos[0]['referencia'] == 'ESP') {
/*Listagem de Todos os Orçamento(s) que estão em aberto, que não estão congelados, em que esse PA "item de ORC" 
esteja sem "prazo de Entrega do Técnico" preenchido ...*/
                    $sql = "SELECT ovi.`id_orcamento_venda_item` 
                            FROM `orcamentos_vendas_itens` ovi 
                            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`status` < '2' AND ov.`congelar` = 'N' 
                            WHERE ovi.`id_produto_acabado` = '$_POST[id_produto_acabado]' 
                            AND ovi.`prazo_entrega_tecnico` = '' LIMIT 1 ";
                    $campos_prazo_entrega = bancos::sql($sql);
//Se encontrar algum item que tenha o prazo de entrega técnico como zerado, então não pode liberar o custo
                    if(count($campos_prazo_entrega) == 1) $acao = 'NAO';
                }
            }
        }else {//Não selecionado
            $acao = 'NAO';
        }
        custos::liberar_desliberar_custo($id_produto_acabado_custo, $acao);
//Aqui eu mudo o status desse P.A. q foi migrado, p/ 0, p/ dizer q este já foi atualizado
        $sql = "UPDATE `produtos_acabados` SET `pa_migrado` = '0' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
        bancos::sql($sql);
    }
//Aqui existe esse tratamento com as variáveis para não furar o sql abaixo
    if(empty($txt_lote_minimo_compra))  $txt_lote_minimo_compra = '0';
    if(empty($txt_preco_compra_fat_nac))$txt_preco_compra_fat_nac = '0.00';
    if(empty($txt_prazo_pgto_ddl))      $txt_prazo_pgto_ddl = '0.0';
    if(empty($txt_desc_vista))          $txt_desc_vista = '0.0';
    if(empty($txt_desc_sgd))            $txt_desc_sgd = '0.0';
    if(empty($txt_ipi))                 $txt_ipi = 0;
    if(empty($txt_icms))                $txt_icms = '0.00';
    if(empty($txt_reducao))             $txt_reducao = '0.00';
    if(empty($txt_iva))                 $txt_iva = '0.00';
    if(empty($cmb_forma_compra))        $cmb_forma_compra = 0;
    if(empty($txt_preco_compra_nac))    $txt_preco_compra_nac = '0.00';
    if(empty($txt_fator_margem_lucro_pa)) $txt_fator_margem_lucro_pa = '0.00';

//Aqui eu verifico se o usuário não alterou algum dado da lista de preço
    $sql = "SELECT `id_fornecedor_prod_insumo` 
            FROM `fornecedores_x_prod_insumos` 
            WHERE `preco_faturado` = '$txt_preco_compra_fat_nac' 
            AND `prazo_pgto_ddl` = '$txt_prazo_pgto_ddl' 
            AND `desc_vista` = '$txt_desc_vista' 
            AND `desc_sgd` = '$txt_desc_sgd' 
            AND `ipi` = '$txt_ipi' 
            AND `icms` = '$txt_icms' 
            AND `reducao` = '$txt_reducao' 
            AND `iva` = '$txt_iva' 
            AND `forma_compra` = '$cmb_forma_compra' 
            AND `preco` = '$txt_preco_compra_nac' 
            AND `lote_minimo_pa_rev` = '$txt_lote_minimo_compra' 
            AND `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
    $campos_lista = bancos::sql($sql);
//Esta parte do script serve para atualizar o custo do produto insumo
/*Não encontrou nenhum registro, ou seja significa q o usuário realizou alguma
alteração em algum campo da lista de preço, então eu mexo no custo_pi do produto*/
    if(count($campos_lista) == 0) {
//Aqui eu zero os adicionais do produto na lista de preço
        $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado_adicional` = '0', `preco_faturado_export_adicional` = '0' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
        bancos::sql($sql);
    }
//Aqui atualiza normalmente a lista de preço
    $sql = "UPDATE `fornecedores_x_prod_insumos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `preco_faturado` = '$txt_preco_compra_fat_nac', `prazo_pgto_ddl` = '$txt_prazo_pgto_ddl', `desc_vista` = '$txt_desc_vista', `desc_sgd` = '$txt_desc_sgd', `ipi` = '$txt_ipi', `icms` = '$txt_icms', `reducao` = '$txt_reducao', `iva` = '$txt_iva', `forma_compra` = '$cmb_forma_compra', `preco` = '$txt_preco_compra_nac', `fator_margem_lucro_pa` = '$txt_fator_margem_lucro_pa', `data_sys` = '".date('Y-m-d H:i:s')."', `lote_minimo_pa_rev` = '$txt_lote_minimo_compra' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
    bancos::sql($sql);

    $url_remetente = $_SERVER['REQUEST_URI'];//Equivale a URL que está na Barra de Endereços do Navegador ...
    custos::atualizar_custos_orcs_descongelados($_POST['id_produto_acabado'], $url_remetente, 1, $id_fornecedor_prod_insumo);
}

/*********************************************************************************/
/***************************Procedimento Normal da Tela***************************/
/*********************************************************************************/
//Aqui eu verifico se o PA é um PI "PIPA" ...
$sql = "SELECT `id_produto_insumo` 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' 
        AND `id_produto_insumo` > '0' 
        AND `ativo` = '1' LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 0) {//Nunca foi importado ...
/*************************Transformando o PA em PI - PIPA*************************/
    intermodular::importar_patopi($_GET['id_produto_acabado']);//Aqui é a função que importa o PA para PI ...
    
    //Busco o id_produto_insumo que acabou de ser gerado do $id_produto_acabado ...
    $sql = "SELECT `id_produto_insumo` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos_pi = bancos::sql($sql);
?>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
    alert('ESTE "PRODUTO ACABADO" ACABOU DE VIRAR UM "PRODUTO INSUMO" !!!\n\nAGORA É PRECISO ATRELAR UM FORNECEDOR DEFAULT PARA O MESMO !')
    nova_janela('../../../classes/produtos_insumos/marcar_fornecedor_default.php?id_produto_insumo=<?=$campos_pi[0]['id_produto_insumo'];?>', 'ALTERAR_FORNECEDOR_DEFAULT', '', '', '', '', '380', '970', 'c', 'c', '', '', 's', 's', '', '', '')
</Script>
<?
}

/***************************************ORÇAMENTOS*****************************************/
//Se for vazio, significa que esta tela está sendo acessado da Tela de Orçamentos ...
if(empty($id_fornecedor_prod_insumo)) {//Em primeiro lugar, verifico se o PA passado por parâmetro também é um PI ...
    $sql = "SELECT `id_produto_insumo` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_produto_insumo  = bancos::sql($sql);
    $id_produto_insumo      = $campos_produto_insumo[0]['id_produto_insumo'];
//Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado ...
    $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
//Em terceiro qual é o id_fornecedor_prod_insumo, agora já tenho $id_produto_insumo e o $id_fornecedor_setado
    $sql = "SELECT `id_fornecedor_prod_insumo` 
            FROM `fornecedores_x_prod_insumos` 
            WHERE `id_produto_insumo` = '$id_produto_insumo' 
            AND `id_fornecedor` = '$id_fornecedor_setado' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_lista_preco         = bancos::sql($sql);
    $id_fornecedor_prod_insumo  = $campos_lista_preco[0]['id_fornecedor_prod_insumo'];
/****************************************PRODUÇÃO******************************************/
}else {//Veio da Tela de Custos do PA Revenda ...
    $sql = "SELECT pa.`id_produto_acabado`, fpi.`id_produto_insumo` 
            FROM `fornecedores_x_prod_insumos` fpi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`ativo` = '1' AND pa.`operacao_custo` = '1' 
            WHERE fpi.`id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
    $campos             = bancos::sql($sql);
//Em primeiro verifico quem é o id_produto_acabado
    $id_produto_acabado = $campos[0]['id_produto_acabado'];
    $id_produto_insumo	= $campos[0]['id_produto_insumo'];
//Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado
    $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
}
/******************************************************************************************/

//Assim que entra na tela já busco alguns dados do $id_produto_acabado que foi passado por parâmetro e estes servirão para todo o restante da Tela ...
$sql = "SELECT ed.`razaosocial`, gpa.`id_familia`, gpa.`nome`, gpa.`lote_min_producao_reais`, pa.`referencia`, pa.`preco_unitario`, 
        pa.`preco_export`, pa.`operacao_custo`, pa.`status_custo`, pa.`observacao` 
        FROM `produtos_acabados` pa 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
        /*AND pa.`ativo` = '1' 
        AND pa.`operacao_custo` = '1' */ LIMIT 1 ";
$campos_pa                  = bancos::sql($sql);
$operacao_custo             = $campos_pa[0]['operacao_custo'];
$status_custo               = $campos_pa[0]['status_custo'];

//Já coloco essa variável aki, pq vou utilizar ela p/ alguns cálculos em JavaScript ...
$lote_min_producao_reais    = $campos_pa[0]['lote_min_producao_reais'];

/*Aqui eu trago dados da Lista de Preço nesse caso é quando o PA é um PI, nem sempre isso acontece principalmente quando o PA acabou de ser 
incluso na Base de Dados como OC = Industrial e virou Revenda ...*/
$sql = "SELECT fpi.*, f.`id_pais`, f.`razaosocial` 
        FROM `fornecedores_x_prod_insumos` fpi 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
        WHERE fpi.`id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' 
        AND fpi.`ativo` = '1' LIMIT 1 ";
$campos_lista           = bancos::sql($sql);
$id_fornecedor          = $campos_lista[0]['id_fornecedor'];

/********************Campos da Lista de Preços do Fornecedor - algumas das variáveis abaixo utilizo na função 
para cálculo -> valor_custo()************************/
$preco_compra_nac       = ($campos_lista[0]['preco'] == '0.00' || $campos_lista[0]['preco'] == '') ? '' : number_format($campos_lista[0]['preco'], 2, ',', '.');
$preco_exportacao       = number_format($campos_lista[0]['preco_exportacao'], 2, ',', '.');
$preco_compra_fat_nac   = ($campos_lista[0]['preco_faturado'] == '0.00') ? '' : number_format($campos_lista[0]['preco_faturado'], 2, ',', '.');
$prazo_pgto_ddl         = ($campos_lista[0]['prazo_pgto_ddl'] == '0.0') ? '' : number_format($campos_lista[0]['prazo_pgto_ddl'], 1, ',', '.');
$desc_vista             = ($campos_lista[0]['desc_vista'] == '0.0') ? '' : number_format($campos_lista[0]['desc_vista'], 1, ',', '.');
$desc_sgd               = ($campos_lista[0]['desc_sgd'] == '0.0') ? '' : number_format($campos_lista[0]['desc_sgd'], 1, ',', '.');
$ipi                    = $campos_lista[0]['ipi'];
$icms                   = ($campos_lista[0]['icms'] == '0.00') ? '' : number_format($campos_lista[0]['icms'], 2, ',', '.');
$reducao                = ($campos_lista[0]['reducao'] == '0.00') ? '' : number_format($campos_lista[0]['reducao'], 2, ',', '.');
$iva                    = ($campos_lista[0]['iva'] == '0.00') ? '' : number_format($campos_lista[0]['iva'], 2, ',', '.');
$forma_compra           = $campos_lista[0]['forma_compra'];
$tp_moeda               = $campos_lista[0]['tp_moeda'];
$preco_fat_exp          = $campos_lista[0]['preco_faturado_export'];

if($id_pais != 31) {//Se o País do Fornecedor for <> de Brasil
    //Múltiplico por 1 p/ facilitar p/ o Roberto, mas somente quando for País Estrangeiro, por causa do Dólar, Euro ...
    if($valor_moeda_compra == '' || $valor_moeda_compra == '0,0000') $preco_exportacao = $preco_fat_exp * 1;
}
$preco_fat_exp          = number_format($preco_fat_exp, 2, ',', '.');

$valor_moeda_compra     = number_format($campos_lista[0]['valor_moeda_compra'], 4, ',', '.');
$fator_margem_lucro_pa  = ($campos_lista[0]['fator_margem_lucro_pa'] == '0.00') ? 0 : $campos_lista[0]['fator_margem_lucro_pa'];
$lote_minimo_pa_rev     = $campos_lista[0]['lote_minimo_pa_rev'];
/***********************************************************************************************************/

$id_pais                = $campos_lista[0]['id_pais'];
$razaosocial            = $campos_lista[0]['razaosocial'];

/********Aqui é um select para trazer a condição padrão de preços do fornecedor********/
$sql = "SELECT * 
        FROM `fornecedores_x_prod_insumos` 
        WHERE `id_fornecedor` = '$id_fornecedor' 
        AND `condicao_padrao` = '1' LIMIT 1 ";
$campos_padrao = bancos::sql($sql);
if(count($campos_padrao) == 0) {//Não Achou
    $prazo_pgto_ddl_padrao  = '0,0';
    $desc_vista_padrao      = '0,0';
    $desc_sgd_padrao        = '0,0';
    $ipi_padrao             = 0;
    $icms_padrao            = '0,00';
    $reducao_padrao         = '0,00';
    $iva_padrao             = '0,00';
    $fator_margem_lucro_pa_padrao = '0,00';
}else {//Achou
    $prazo_pgto_ddl_padrao  = ($campos_padrao[0]['prazo_pgto_ddl'] == '0.0') ? '0,0' : number_format($campos_padrao[0]['prazo_pgto_ddl'], 1, ',', '.');
    $desc_vista_padrao      = ($campos_padrao[0]['desc_vista'] == '0.0') ? '0,0' : number_format($campos_padrao[0]['desc_vista'], 1, ',', '.');
    $desc_sgd_padrao        = ($campos_padrao[0]['desc_sgd'] == '0.0') ? '0,0' : number_format($campos_padrao[0]['desc_sgd'], 1, ',', '.');
    $ipi_padrao             = $campos_padrao[0]['ipi'];
    $icms_padrao            = ($campos_padrao[0]['icms'] == '0.00') ? '0,00' : number_format($campos_padrao[0]['icms'], 2, ',', '.');
    $reducao_padrao         = ($campos_padrao[0]['reducao'] == '0.00') ? '0,00' : number_format($campos_padrao[0]['reducao'], 2, ',', '.');
    $iva_padrao             = ($campos_padrao[0]['iva'] == '0.00') ? '0,00' : number_format($campos_padrao[0]['iva'], 2, ',', '.');
    $forma_compra_padrao    = $campos_padrao[0]['forma_compra'];
    $fator_margem_lucro_pa_padrao = ($campos_padrao[0]['fator_margem_lucro_pa'] == '0.00') ?  '0,00' : number_format($campos_padrao[0]['fator_margem_lucro_pa'], 2, ',', '.');
}
/**************************************************************************************/
$dados_produto              = intermodular::dados_impostos_pa($id_produto_acabado, 1);//Sempre trago dados como a UF = 'SP' ...
$icms_cf_uf_sp              = $dados_produto['icms'];
$reducao_uf_sp              = $dados_produto['reducao'];
$ICMS_c_red_vendas          = ($icms_cf_uf_sp) * (1 - $reducao_uf_sp / 100);
?>
<html>
<head>
<title>.:: Custo Revenda ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content= 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Lote Mínimo p/ Compra
    if(!texto('form', 'txt_lote_minimo_compra', '1', '0123456789', 'LOTE MÍNIMO P/ COMPRA', '2')) {
        return false
    }
//Se o Valor de Lote Mínimo for = Zero, então eu considero como sendo inválido ...
    if(document.form.txt_lote_minimo_compra.value == 0) {
        alert('VALOR DE LOTE MÍNIMO INVÁLIDO !')
        document.form.txt_lote_minimo_compra.focus()
        document.form.txt_lote_minimo_compra.select()
        return false
    }
/*Caso exista o Preço Fat. de Moeda Estrangeira, então não preciso forçar o preenchimento do campo 
Preço Faturado...*/
    var preco_fat_moeda_estrangeira = eval(strtofloat(document.form.txt_preco_fat_moeda_inter.value))
    if(preco_fat_moeda_estrangeira == 0) {
//Preço Faturado
        if(!texto('form', 'txt_preco_compra_fat_nac', '3', '0123456789,.', 'PREÇO FATURADO', '2')) {
            return false
        }
    }
//Prazo Pagto DLL
    if(!texto('form', 'txt_prazo_pgto_ddl', '3', '0123456789,.', 'PRAZO DE PAGTO DDL', '2')) {
        return false
    }
//Desconto à Vista
    if(!texto('form', 'txt_desc_vista', '3', '0123456789,.', 'DESCONTO À VISTA', '2')) {
        return false
    }
//Desconto SGD
    if(!texto('form', 'txt_desc_sgd', '3', '0123456789,.', 'DESCONTO SGD', '2')) {
        return false
    }
//IPI
    if(!texto('form', 'txt_ipi', '1', '0123456789', 'IPI', '2')) {
        return false
    }
//ICMS
    if(!texto('form', 'txt_icms', '3', '0123456789,.', 'ICMS', '2')) {
        return false
    }
//Reforço (rs) ...
    if(document.form.txt_icms.value == '0,00') {
        alert('DIGITE O ICMS % !')
        document.form.txt_icms.focus()
        document.form.txt_icms.select()
        return false
    }
//Redução ...
    if(document.form.txt_reducao.value != '') {
        if(!texto('form', 'txt_reducao', '3', '0123456789,.', 'REDUÇÃO', '1')) {
            return false
        }
    }
//IVA
    if(document.form.txt_iva.value != '') {
        if(!texto('form', 'txt_iva', '3', '0123456789,.', 'IVA', '2')) {
            return false
        }
    }
//Forma de Compra
    if(!combo('form', 'cmb_forma_compra', '', 'SELECIONE A FORMA DE COMPRA !')) {
        return false
    }
//Se existir Valor de Moeda Estrangeira e estiver sem Tipo de Moeda preenchido então forço o preenchimento ...
    if(eval(strtofloat(document.form.txt_preco_fat_moeda_inter.value)) > 0 && tp_moeda == '') {
        alert('ESTE CUSTO NÃO PODE SER SALVO, PORQUE ESTÁ SEM O TIPO DE MOEDA PREENCHIDO !!!\n\nCONTATE O DEPARTAMENTO DE COMPRAS PARA FAZER A CORREÇÃO !')
        return false
    }
//Verificando o Valor do Campo Fator Margem de Lucro do PA ...
    if(document.form.txt_fator_margem_lucro_pa.value != '') {
        var fator_margem_lucro_pa = eval(strtofloat(document.form.txt_fator_margem_lucro_pa.value))
//Se o Fator Margem de Lucro do P.A. < 1.45 and Fator Margem de Lucro do P.A. > 2
        if(fator_margem_lucro_pa < 1.45 || fator_margem_lucro_pa > 2) {
            alert('FATOR MARGEM DE LUCRO INVÁLIDO !\nDIGITE UM VALOR ENTRE 1,45 E 2,00 PARA ESTE FATOR !!!')
            document.form.txt_fator_margem_lucro_pa.focus()
            document.form.txt_fator_margem_lucro_pa.select()
            return false
        }
    }
    var indice_forma_compra 	= document.form.cmb_forma_compra.value
    var forma_compra            = document.form.cmb_forma_compra[indice_forma_compra].text
    var preco_compra_nacional 	= document.form.txt_preco_compra_nac.value

    var resposta = confirm('VOCÊ TEM CERTEZA DE QUE O PREÇO DE COMPRA NAC. R$ '+preco_compra_nacional+' E A FORMA DE COMPRA '+forma_compra+' ESTÃO CORRETAS ?')
    if(resposta == false) {
        return false
    }else {
//Lote Mínimo P. Venda R$
        var lote_minimo_preco_venda_rs = document.form.txt_lote_minimo_preco_venda_rs.value
//Lote Mínimo Grupo R$
        var lote_minimo_grupo_rs = eval(strtofloat(document.form.txt_lote_minimo_grupo_rs.value))
        if(typeof(lote_minimo_grupo_rs) == 'undefined') lote_minimo_grupo_rs = 0
//Essas variáveis são para exibir no Alert ...
        var lote_minimo_preco_venda_rs_apresent = arred(String(document.form.txt_lote_minimo_preco_venda_rs.value), 2, 1)
        var lote_minimo_grupo_rs_apresent = document.form.txt_lote_minimo_grupo_rs.value
//Comparação entre o Lote Mínimo P. Venda R$ com o Lote Mínimo do Grupo em R$
        if(lote_minimo_preco_venda_rs < lote_minimo_grupo_rs) {
            var resposta2 = confirm('O LOTE MÍNIMO P/ VENDA DE R$ '+lote_minimo_preco_venda_rs_apresent+' É MENOR DO QUE O LOTE MÍNIMO GRUPO R$ '+lote_minimo_grupo_rs_apresent+' ! DESEJA CONTINUAR ???')
            if(resposta2 == false) return false
        }
        document.form.passo.value = 1
        document.form.nao_atualizar.value = 1
//Desabilito esse(s) campo(s) p/ poder gravar na Base de Dados ...
        document.form.txt_preco_compra_nac.disabled = false
        document.form.txt_fator_margem_lucro_pa.disabled = false
        return limpeza_moeda('form', 'txt_preco_compra_fat_nac, txt_prazo_pgto_ddl, txt_desc_vista, txt_desc_sgd, txt_icms, txt_reducao, txt_iva, txt_fator_margem_lucro_pa, txt_preco_compra_nac, ')
    }
}

function alterar_fornecedor_default(id_produto_insumo) {
    nova_janela('../../../classes/produtos_insumos/marcar_fornecedor_default.php?id_produto_insumo='+id_produto_insumo, 'ALTERAR_FORNECEDOR_DEFAULT', '', '', '', '', '380', '970', 'c', 'c', '', '', 's', 's', '', '', '')
}
  
function alterar_produto_acabado(id_produto_acabado) {
    nova_janela('../../cadastros/produto_acabado/alterar.php?passo=1&id_produto_acabado='+id_produto_acabado+'&pop_up=1', 'CONSULTAR', '', '', '', '', '450', '780', 'c', 'c', '', '', 's', 's', '', '', '')
}

function copiar_condicao_padrao() {
    document.form.txt_prazo_pgto_ddl.value  = document.form.txt_prazo_pgto_ddl2.value
    document.form.txt_desc_vista.value      = document.form.txt_desc_vista2.value
    document.form.txt_desc_sgd.value        = document.form.txt_desc_sgd2.value
    document.form.txt_ipi.value             = document.form.txt_ipi2.value
    document.form.txt_icms.value            = document.form.txt_icms2.value
    document.form.txt_reducao.value         = document.form.txt_reducao2.value
    document.form.txt_iva.value             = document.form.txt_iva2.value
    document.form.cmb_forma_compra.value    = document.form.cmb_forma_compra2.value
    document.form.txt_fator_margem_lucro_pa.value = document.form.txt_fator_margem_lucro_pa2.value
    //Aqui calcula novamente
    calculos_gerais_custo_revenda()
}

function calculos_gerais_custo_revenda() {
    var financeiro                  = '<?=$taxa_financeiro;?>'
    var desconto_snf                = '<?=$desconto_snf;?>'
    var taxa_financeira_vendas      = '<?=$taxa_financeira_vendas;?>'
    var taxa_financeira_vendas      = ((taxa_financeira_vendas / 100) + 1)
    var fator_importacao            = '<?=$fator_importacao;?>'
    var valor_moeda_dolar_custo     = '<?=$valor_moeda_dolar_custo;?>'
    var valor_moeda_euro_custo      = '<?=$valor_moeda_euro_custo;?>'
    var id_pais                     = '<?=$id_pais;?>'
    var outros_impostos_federais    = '<?=$outros_impostos_federais;?>'
    var fator_desconto_maximo_vendas = '<?=$fator_desconto_maximo_vendas;?>'
    var tp_moeda                    = '<?=$tp_moeda;?>'
    var ICMS_c_red_vendas           = '<?=$ICMS_c_red_vendas;?>'

    var lote_minimo_compra = eval(strtofloat(document.form.txt_lote_minimo_compra.value))
    if(typeof(lote_minimo_compra) == 'undefined') lote_minimo_compra = 0
    
    var preco_compra_fat_nac = eval(strtofloat(document.form.txt_preco_compra_fat_nac.value))
    if(typeof(preco_compra_fat_nac) == 'undefined') preco_compra_fat_nac = 0
    
    var prazo_pgto_dias = eval(strtofloat(document.form.txt_prazo_pgto_ddl.value))
    if(typeof(prazo_pgto_dias) == 'undefined') prazo_pgto_dias = 0
    
    var desconto_vista  = eval(strtofloat(document.form.txt_desc_vista.value))
    if(typeof(desconto_vista) == 'undefined') desconto_vista = 0
    
    var desconto_sgd    = eval(strtofloat(document.form.txt_desc_sgd.value))
    if(typeof(desconto_sgd) == 'undefined') desconto_sgd = 0
    
    var icms            = eval(strtofloat(document.form.txt_icms.value))
    if(typeof(icms) == 'undefined') icms = 0
    
    var reducao         = eval(strtofloat(document.form.txt_reducao.value))
    if(typeof(reducao) == 'undefined') reducao = 0
    
    var ICMS_c_red_compras   =  icms * (1 - reducao / 100)
    
    var fator_margem_lucro_pa = eval(strtofloat(document.form.txt_fator_margem_lucro_pa.value))
    if(typeof(fator_margem_lucro_pa) == 'undefined') fator_margem_lucro_pa = 0
    
    var custo_pa_indust = eval(strtofloat(document.form.txt_custo_pa_indust.value))
    if(typeof(custo_pa_indust) == 'undefined') custo_pa_indust = 0
    
    var preco_faturado_inter = eval(strtofloat(document.form.txt_preco_fat_moeda_inter.value))
    if(typeof(preco_faturado_inter) == 'undefined') preco_faturado_inter = 0
    
    /******************************************************************************************************************/
    /********************Calcula o Preço de Compra de acordo com a Mudança na Combo Forma de Compra********************/
    /******************************************************************************************************************/
    //Verifica se a combo forma de compra está selecionada
    if(document.form.cmb_forma_compra.value == '') {
        document.form.txt_preco_compra_nac.value = ''
    }else {
        /*Parte onde começa a calcular: 
         
        Esses preços mínimos deveriam ser usados quando Salvar comparando se a forma de Compra escolhida foi a ideal,
        mas não estão sendo utilizados no momento ... - 05/06/2014 ...
        
        precominimo     = (preco_compra_fat_nac * (100 - (prazo_pgto_dias / 30) * financeiro) / 100)
        precominimo2    = (preco_compra_fat_nac * (100 - (ICMS_c_red_compras - desconto_snf)) / 100)
        precominimo3    = (precominimo * (100 - (icms_com_reducao - desconto_snf)) / 100)
        precominimo     = (Math.round((precominimo * 100))) / 100
        precominimo2    = (Math.round((precominimo2 * 100))) / 100
        precominimo3    = (Math.round((precominimo3 * 100))) / 100 ...*/
         
        preco_av_nf     = (preco_compra_fat_nac * (100 - desconto_vista) / 100)
        preco_fat_sgd   = (preco_compra_fat_nac * (100 - desconto_sgd) / 100)
        preco_av_sgd    = (preco_fat_sgd * (100 - desconto_vista) / 100)

        resposta1       = (Math.round((preco_av_nf * 100))) / 100
        resposta2       = (Math.round((preco_fat_sgd * 100))) / 100
        resposta3       = (Math.round((preco_av_sgd * 100))) / 100
//A caixa de resultado Preço de Compra Nacional receberá o Preço FAT/SGD ...
        if(document.form.cmb_forma_compra.value == 1) {
            document.form.txt_preco_compra_nac.value = document.form.txt_preco_compra_fat_nac.value
            for(var i = 0; i < document.form.txt_preco_compra_nac.value.length; i++) {
                if(document.form.txt_preco_compra_nac.value.charAt(i) == '.') {
                    document.form.txt_preco_compra_nac.value = document.form.txt_preco_compra_nac.value.replace(document.form.txt_preco_compra_nac.value.charAt(i), '')
                }
            }
            document.form.txt_preco_compra_nac.value = document.form.txt_preco_compra_nac.value.replace(',', '.')
            document.form.txt_preco_compra_nac.value = arred(document.form.txt_preco_compra_nac.value, 2, 1)
        }
//A caixa de resultado Preço de Compra Nacional receberá o Preço FAT/SGD ...
        if(document.form.cmb_forma_compra.value == 2) {
            document.form.txt_preco_compra_nac.value = resposta2
            document.form.txt_preco_compra_nac.value = arred(document.form.txt_preco_compra_nac.value, 2, 1)
        }
//A caixa de resultado Preço de Compra Nacional receberá o Preço AV/NF ...
        if(document.form.cmb_forma_compra.value == 3) {
            document.form.txt_preco_compra_nac.value = resposta1
            document.form.txt_preco_compra_nac.value = arred(document.form.txt_preco_compra_nac.value, 2, 1)
        }
//A caixa de resultado Preço de Compra Nacional receberá o Preço AV/SGD ...
        if(document.form.cmb_forma_compra.value == 4) {
            document.form.txt_preco_compra_nac.value = resposta3
            document.form.txt_preco_compra_nac.value = arred(document.form.txt_preco_compra_nac.value, 2, 1)
        }
    }
    /******************************************************************************************************************/
    /*********************Calcula o Preço Fat. Nac. Min R$  ou seja o Custo Revenda desse Produto**********************/
    /******************************************************************************************************************/
//Valor Moeda p/ Compra
    if(id_pais != 31) {//Se o País do Fornecedor for <> de Brasil
        if(document.form.txt_valor_moeda_compra.value == '' || document.form.txt_valor_moeda_compra.value == '0,0000') {
//Igualo a 1 p/ facilitar para o Roberto, mas somente quando for País Estrangeiro, por causa do Dólar, Euro
            var valor_moeda_compra = 1
        }else {
            var valor_moeda_compra = eval(strtofloat(document.form.txt_valor_moeda_compra.value))
        }
    }else {//Se o País for nacional, segue o procedimento normal ...
        var valor_moeda_compra = (document.form.txt_valor_moeda_compra.value != '') ? eval(strtofloat(document.form.txt_valor_moeda_compra.value)) : 0
    }

    if(id_pais == 31) {//Nacional, se estrangeiro multiplica pela Moeda Compra ...
        var valor_custo_rev_nacional    = (preco_compra_fat_nac == 0) ?  0 : fator_margem_lucro_pa * preco_compra_fat_nac * taxa_financeira_vendas
        var valor_custo_rev_inter       = (preco_faturado_inter == 0) ? 0 : fator_margem_lucro_pa * preco_faturado_inter * taxa_financeira_vendas * valor_moeda_compra
    }else {//Se for Estrangeiro multiplica pela Moeda do Custo ...
        var valor_custo_rev_nacional    = 0
        if(preco_faturado_inter == 0) {
            valor_custo_rev_inter       = 0
        }else {
            //Valor Dólar ...
            var valor_moeda_custo       = (tp_moeda == 1) ? valor_moeda_dolar_custo : valor_moeda_euro_custo
            var valor_custo_rev_inter   = fator_margem_lucro_pa * preco_faturado_inter * taxa_financeira_vendas * valor_moeda_custo * fator_importacao
        }
    }
    
    var diferenca_icms  = (ICMS_c_red_vendas - ICMS_c_red_compras)

    if(valor_custo_rev_nacional > 0) {
        //Aqui eu acrescento os Impostos Federais em cima dessa parte de Revenda que foi calculada ...
        document.form.txt_preco_venda_fat_nac_min_rs.value      = valor_custo_rev_nacional / (1 - outros_impostos_federais / 100) / (1 - diferenca_icms / 100)
        //Somo o Valor de Custo Industrial em cima do Custo Revenda calculado acima ...
        document.form.txt_preco_venda_fat_nac_min_rs.value      = eval(document.form.txt_preco_venda_fat_nac_min_rs.value) + custo_pa_indust
        //Arredondo o valor calculado ...
        document.form.txt_preco_venda_fat_nac_min_rs.value      = arred(document.form.txt_preco_venda_fat_nac_min_rs.value, 2, 1)
    }
    
    if(valor_custo_rev_inter > 0) {
        //Aqui eu acrescento os Impostos Federais em cima dessa parte de Revenda que foi calculada ...
        document.form.txt_preco_venda_fat_inter_min_rs.value    = valor_custo_rev_inter / (1 - outros_impostos_federais / 100) / (1 - diferenca_icms / 100)
        //Somo o Valor de Custo Industrial em cima do Custo Revenda calculado acima ...
        document.form.txt_preco_venda_fat_inter_min_rs.value    = eval(document.form.txt_preco_venda_fat_inter_min_rs.value) + custo_pa_indust
        //Arredondo o valor calculado ...
        document.form.txt_preco_venda_fat_inter_min_rs.value    = arred(document.form.txt_preco_venda_fat_inter_min_rs.value, 2, 1)
    }
    
    //Aqui eu calculo o Preço de Venda Faturado Nacional e Internacional em R$ ...
    var preco_venda_fat_nac_min_rs  = eval(strtofloat(document.form.txt_preco_venda_fat_nac_min_rs.value))
    var preco_venda_fat_nac_max_rs  = preco_venda_fat_nac_min_rs / fator_desconto_maximo_vendas
    
    //Arredondo o valor calculado ...
    document.form.txt_preco_venda_fat_nac_max_rs.value  = arred(String(preco_venda_fat_nac_max_rs), 2, 1)
    
    if(document.form.txt_preco_venda_fat_inter_min_rs.value != '') {
        var preco_venda_fat_inter_min_rs  = eval(strtofloat(document.form.txt_preco_venda_fat_inter_min_rs.value))
        var preco_venda_fat_inter_max_rs  = preco_venda_fat_inter_min_rs / fator_desconto_maximo_vendas
        //Arredondo o valor calculado ...
        document.form.txt_preco_venda_fat_inter_max_rs.value    = arred(String(preco_venda_fat_inter_max_rs), 2, 1)
    }

    var lote_minimo_preco_venda_rs = lote_minimo_compra * preco_venda_fat_nac_max_rs
    document.form.txt_lote_minimo_preco_venda_rs.value = lote_minimo_preco_venda_rs
    document.form.txt_lote_minimo_preco_venda_rs.value = arred(document.form.txt_lote_minimo_preco_venda_rs.value, 2, 1)
    
    //Lote Mínimo P. Venda R$
    var lote_minimo_preco_venda_rs = eval(strtofloat(document.form.txt_lote_minimo_preco_venda_rs.value))

    //Lote Mínimo Grupo R$
    var lote_minimo_grupo_rs = eval(strtofloat(document.form.txt_lote_minimo_grupo_rs.value))
    if(typeof(lote_minimo_grupo_rs) == 'undefined') lote_minimo_grupo_rs = 0
//Comparação entre o Lote Mínimo P. Venda R$ com o Lote Mínimo do Grupo em R$
/*Se o Lote Mínimo P. Venda R$ for < Lote Mínimo Grupo R$, então printo a caixa em Vermelho, dizendo 
que a caixa de Lote Mínimo de Compra está em Irregular*/
    if(lote_minimo_preco_venda_rs < lote_minimo_grupo_rs) {
        document.form.txt_lote_minimo_compra.style.background   = 'red'
        document.form.txt_lote_minimo_compra.style.color        = 'white'
/*Se o Lote Mínimo P. Venda R$ for < Lote Mínimo Grupo R$, então printo a caixa em Branca, dizendo 
que a caixa de Lote Mínimo de Compra está Normal*/
    }else {
        document.form.txt_lote_minimo_compra.style.background   = 'white'
        document.form.txt_lote_minimo_compra.style.color        = 'brown'
    }
}

function atualizar_abaixo() {
    if(typeof(window.opener.document.form.resposta) == 'object') {
//Aqui é para não abrir o Pop-up do Custo
        window.opener.document.form.resposta.value = false
//Aqui é para não dar Update na tela de baixo e gravar o valor das caixas abaixo
        window.opener.document.form.ignorar_update.value = 1
        window.opener.document.form.submit()
    }
    window.close()
}
</Script>
</head>
<body onload='calculos_gerais_custo_revenda();document.form.txt_lote_minimo_compra.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Aqui eu já guardo essas variável pq utilizo elas no Update-->
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<!--Controle de Tela-->
<input type='hidden' name='id_fornecedor_prod_insumo' value='<?=$id_fornecedor_prod_insumo;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo'>
<!--********************************************************-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <font color='#00FF00' size='2'>
                <b>CUSTO REVENDA</b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font color='yellow'>
                Fornecedor:
            </font>
            <?=$razaosocial;?>
            &nbsp;-&nbsp;
            <?
                /***********************************************************************************************************/
                if($status_custo == 0) {//Só se o Custo estiver bloqueado q exibe o link p/ modificar o Fornecedor Default ..
            ?>
            <a href = "javascript:alterar_fornecedor_default('<?=$id_produto_insumo;?>')" alt='Alterar Fornecedor Default' title='Alterar Fornecedor Default' class='link'>
            <?
                }
            ?>
                <font color='yellow' size='2'>
                <?
                    if($id_fornecedor == $id_fornecedor_setado) {
                        echo '(DEFAULT)';
                    }else {
                        echo '(NÃO É DEFAULT)';
                    }
                ?>
                </font>
            <?
                if($status_custo == 0) {//Só se o Custo estiver bloqueado q exibe o link p/ modificar o Fornecedor Default ..
            ?>
            </a>
            <?
                }
                /***********************************************************************************************************/
            ?>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font color='yellow'>Produto: </font>
            <?=intermodular::pa_discriminacao($id_produto_acabado);?>
            &nbsp;
            <img src = '../../../../imagem/menu/alterar.png' border='0' title='Alterar Produto Acabado' alt='Alterar Produto Acabado' onclick="alterar_produto_acabado('<?=$id_produto_acabado;?>')">
            &nbsp;
            <a href="javascript:nova_janela('../../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class='link'>
                <img src="../../../../imagem/visualizar_detalhes.png" title="Visualizar Pedidos - Últimos 6 meses" alt="Visualizar Pedidos - Últimos 6 meses" border="0">
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../../../vendas/relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Orçamentos - Últimos 6 meses" class='link'>
                <img src="../../../../imagem/propriedades.png" title="Visualizar Orçamentos - Últimos 6 meses" alt="Visualizar Orçamentos - Últimos 6 meses" border="0">
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'VISUALIZAR_ESTOQUE', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
                <font color='yellow'>
                    Visualizar Estoque
                </font>
            </a>
            &nbsp;
            <img src = '../../../../imagem/carrinho_compras.png' border='0' title='Compra + Produção' alt='Compra + Produção' width='25' height='16' onclick="html5Lightbox.showLightbox(7, '../../../classes/producao/visualizar_compra_producao.php?id_produto_acabado=<?=$id_produto_acabado;?>')">
            &nbsp;
            <a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$id_produto_insumo;?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Compras' class='link'>
                <font color='red' title='Compra' style='cursor:help'>
                    <b>(Compras)</b>
                </font>
            </a>
            <?
                $url = '../../../vendas/estoque_acabado/manipular_estoque/consultar.php?passo=1';
                /*Mudança feita em 17/05/2016 - Antigamente os detalhes da consulta só eram feitos pela 
                referência independente de ser normal de Linha, eu supus que fosse assim porque temos PA(s) 
                que são similares em seu cadastro na parte de referência, por exemplo ML: 
                ML-001, ML-001A, ML-001AS, ML-001D, ML-001S, ML-001T, ML-001U, mas para ESP fica inviável 
                vindo todos os ESP´s do Sistema e trazendo informações que não tinham nada haver ...*/
                if($campos_pa[0]['referencia'] == 'ESP') {//Aqui quero ver detalhes do PA ESP em específico ...
                    $url.= '&id_produto_acabado='.$id_produto_acabado.'&pop_up=1';
                }else {//PA normal de Linha, quero ver detalhes de todos os PA(s) semelhantes a este da Referência ...
                    $url.= '&txt_referencia='.$campos_pa[0]['referencia'].'&pop_up=1';
                }
            ?>
            &nbsp;
            <img src = '../../../../imagem/baixas_manipulacoes.png' border='0' title='Baixas / Manipulações' alt='Baixas / Manipulações' width='22' height='20' onclick="html5Lightbox.showLightbox(7, '<?=$url;?>')">
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font color='yellow'>
                Grupo PA:
            </font>
            <?=$campos_pa[0]['nome'];?>
            &nbsp;-&nbsp;
            <font color='yellow'>
                Empresa Divisão:
            </font>
            <?=$campos_pa[0]['razaosocial'];?>
        </td>
    </tr>
<?
        //Se a família desse PA, for pertencente a família de Componentes, então não mostra link para esse PA
	if($campos_pa[0]['id_familia'] == 13) {
            $url = '../../../classes/producao/alterar_prazo_entrega_normal.php?';
	}else {
            if($campos_pa[0]['referencia'] == 'ESP') {//Se for Especial
                $url = '../../../classes/producao/alterar_prazo_entrega_esp.php?';
            }else {
                $url = '../../../classes/producao/alterar_prazo_entrega_normal.php?';
            }
	}

	if($campos_pa[0]['referencia'] == 'ESP') {//Se for Especial
?>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('alterar_prazo_entrega_tecnico'); return false" style='cursor:pointer'>
        <td colspan='4'>
            Alterar Prazo de Entrega Sugerido pelo Depto. Técnico
            <span id='statusalterar_prazo_entrega_tecnico'>&nbsp;</span>
            <span id='statusalterar_prazo_entrega_tecnico'>&nbsp;</span>
        </td>
    </tr>
    <tr>
        <td colspan='4'>
<!--Eu passo a origem por parâmetro também para não dar erro de URL na parte de detalhes da conta e de cheque-->
            <iframe src="<?=$url.'id_produto_acabado='.$id_produto_acabado;?>" name='alterar_prazo_entrega_tecnico' id='alterar_prazo_entrega_tecnico' marginwidth='0' marginheight="0" style='display: none' frameborder='0' height='185' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
<?
	}
?>
    <tr>
        <td colspan='4' bgcolor='#CCCCCC'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b>Lista de Preço de Venda: </b>
                &nbsp;&nbsp;&nbsp;
                <font color='darkblue'>
                    <b>Nacional:</b>
                </font>
                R$ <?=number_format($campos_pa[0]['preco_unitario'], 2, ',', '.')?>
                &nbsp;&nbsp;-&nbsp;&nbsp;
                <font color='darkblue'>
                    <b>Export:</b>
                </font>
                U$ <?=number_format($campos_pa[0]['preco_export'], 2, ',', '.')?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='4' bgcolor='#CCCCCC'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b>Observação do Produto: </b>
                <a href="javascript:nova_janela('../observacao_produto.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', '250', '780', 'c', 'c', '', '', 's', 's', '', '', '')" alt='Alterar Observação do Produto' title='Alterar Observação do Produto' class='link'>
                <?
                    if(empty($campos_pa[0]['observacao'])) {
                        echo 'SEM OBSERVAÇÃO';
                    }else {
                        echo $campos_pa[0]['observacao'];
                    }
                ?>
                </a>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='2' bgcolor='#CCCCCC'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b>Follow-Up do Produto Acabado (Vendedores e Depto. Técnico): </b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CCCCCC'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <a href = '../../cadastros/produto_acabado/follow_up.php?id_produto_acabado=<?=$id_produto_acabado;?>&tela=<?=$tela;?>' class='html5lightbox'>
                <?
                    $sql = "SELECT COUNT(id_produto_acabado_follow_up) AS total_follow_ups 
                            FROM `produtos_acabados_follow_ups` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado'";
                    $campos             = bancos::sql($sql);
                    $total_follow_ups   = $campos[0]['total_follow_ups'];
                    if($total_follow_ups == 0) {
                        echo 'NÃO HÁ FOLLOW-UP(S) REGISTRADO(S)';
                    }else {
                        echo '<font color="red"><marquee width="280">'.$total_follow_ups.' FOLLOW-UP(S) REGISTRADO(S)</marquee></font>';
                    }
                ?>
                </a>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            Lote Mínimo p/ Compra:
        </td>
        <td>
            <input type='text' name='txt_lote_minimo_compra' value='<?=$lote_minimo_pa_rev;?>' title='Digite o Lote Mínimo p/ Compra' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calculos_gerais_custo_revenda()" size='7' class='caixadetexto'>
        </td>
        <td align='center'>
            <?
                $checked = ($status_custo == 1) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_custo_liberado' id='checar' value='1' title='Custo Liberado' onclick='confirmar_liberacao()' <?=$checked;?> class='checkbox'>
            <label for='checar'>
                Custo Liberado
            </label>
        </td>
        <td>
            Pre&ccedil;o Fat. Nac. Min. 
            <font size='2' color='yellow'>
            <?
                $valores = custos::preco_custo_pa($id_produto_acabado, '', 'S');
                echo 'R$ '.number_format($valores['preco_venda_fat_nac_min_rs'], 2, ',', '.');
            ?>
            </font>
            <br/>
            Pre&ccedil;o Fat. Inter. Min. 
            <font size='2' color='yellow'>
                R$ <?=number_format($valores['preco_venda_fat_inter_min_rs'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='4'>
            <font size='2' color='red'>
                <b>O PREÇO COMPRA FAT. NAC EM R$ E O PREÇO COMPRA FAT. MOEDA INTER. TEM DE SER FAT E NF.</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço Compra Fat. Nac. R$:</b>
        </td>
        <td>
            <input type='text' name='txt_preco_compra_fat_nac' value='<?=$preco_compra_fat_nac;?>' title='Digite o Preço Faturado Nac. R$' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calculos_gerais_custo_revenda()" size='7' class='caixadetexto'>
        </td>
        <td>
            Preço Compra Fat. Moeda Inter.:
        </td>
        <td>
        <?
            if($tp_moeda == 1) {//Dólar ...
                echo 'U$';
            }else if($tp_moeda == 2) {//Euro ...
                echo '&euro;';
            }
        ?>
            <input type='text' name='txt_preco_fat_moeda_inter' value='<?=$preco_fat_exp;?>' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Prazo Pgto Dias:</b>
        </td>
        <td>
            <input type='text' name='txt_prazo_pgto_ddl' value='<?=$prazo_pgto_ddl;?>' title='Digite o Prazo Pgto Dias' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calculos_gerais_custo_revenda()" size='7' class='caixadetexto'>
        </td>
        <td colspan='2'>
            Padr&atilde;o =&gt; <input type='text' name='txt_prazo_pgto_ddl2' value='<?=$prazo_pgto_ddl_padrao;?>' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Desc. A/V %:</b>
        </td>
        <td>
            <input type='text' name='txt_desc_vista' value='<?=$desc_vista;?>' title='Digite o Desc. A/V %' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calculos_gerais_custo_revenda()" size='7' class='caixadetexto'>
        </td>
        <td colspan='2'>
            Padr&atilde;o =&gt; <input type='text' name='txt_desc_vista2' value="<?=$desc_vista_padrao;?>" size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Desc. SGD %:</b>
        </td>
        <td>
            <input type='text' name='txt_desc_sgd' value='<?=$desc_sgd;?>' title='Digite o Desc. SGD %' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calculos_gerais_custo_revenda()" size='7' class='caixadetexto'>
        </td>
        <td colspan='2'>
            Padr&atilde;o =&gt; <input type='text' name='txt_desc_sgd2' value='<?=$desc_sgd_padrao;?>' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>IPI %:</b>
        </td>
        <td>
            <input type='text' name='txt_ipi' value='<?=$ipi;?>' title='Digite o IPI %' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calculos_gerais_custo_revenda()" size='7' class='caixadetexto'>
        </td>
        <td>
            Padr&atilde;o =&gt; <input type='text' name='txt_ipi2' value='<?=$ipi_padrao;?>' size='7' class='textdisabled' disabled>
        </td>
        <td>
            <input type='button' name='cmd_usar_condicao_padrao' value='Usar Condi&ccedil;&otilde;es Padr&atilde;o' title='Usar Condi&ccedil;&otilde;es Padr&atilde;o' onclick='copiar_condicao_padrao()' style='color:green' class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>ICMS %:</b>
        </td>
        <td>
            <input type='text' name='txt_icms' value='<?=$icms;?>' title='Digite o ICMS %' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calculos_gerais_custo_revenda()" size='7' class='caixadetexto'>
        </td>
        <td colspan='2'>
            Padr&atilde;o =&gt; <input type='text' name='txt_icms2' value='<?=$icms_padrao;?>' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Redução %:</b>
        </td>
        <td>
            <input type='text' name='txt_reducao' value='<?=$reducao;?>' title='Digite a Redução %' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calculos_gerais_custo_revenda()" size='7' class='caixadetexto'>
        </td>
        <td colspan='2'>
            Padr&atilde;o =&gt; <input type='text' name='txt_reducao2' value='<?=$txt_reducao_padrao;?>' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            IVA:
        </td>
        <td>
            <input type='text' name='txt_iva' value='<?=$iva;?>' title='Digite o IVA' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calculos_gerais_custo_revenda()" size='7' class='caixadetexto'>
        </td>
        <td colspan='2'>
            Padr&atilde;o =&gt; <input type='text' name='txt_iva2' value='<?=$iva_padrao;?>' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Forma de Compra:</b>
        </td>
        <td>
            <select name='cmb_forma_compra' title='Selecione a Forma de Compra' onchange='calculos_gerais_custo_revenda()' class='combo'>
            <?
                if($forma_compra == 1) {
                    $selected1 = 'selected';
                }else if($forma_compra == 2) {
                    $selected2 = 'selected';
                }else if($forma_compra == 3) {
                    $selected3 = 'selected';
                }else if($forma_compra == 4) {
                    $selected4 = 'selected';
                }
            ?>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1' <?=$selected1;?>>FAT/NF</option>
                <option value='2' <?=$selected2;?>>FAT/SGD</option>
                <option value='3' <?=$selected3;?>>AV/NF</option>
                <option value='4' <?=$selected4;?>>AV/SGD</option>
            </select>
        </td>
        <td colspan='2'>
            Padr&atilde;o =&gt;
            <?
                if($forma_compra_padrao == 1) {
                    $texto = 'FAT/NF';
                }else if($forma_compra_padrao == 2) {
                    $texto = 'FAT/SGD';
                }else if($forma_compra_padrao == 3) {
                    $texto = 'AV/NF';
                }else if($forma_compra_padrao == 4) {
                    $texto = 'AV/SGD';
                }
            ?>
            <input type='text' name='txt_forma_compra' value='<?=$texto;?>' size='7' class='textdisabled' disabled>
            <input type='hidden' name='cmb_forma_compra2' value='<?=$forma_compra_padrao;?>'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fator Margem de Lucro:
        </td>
        <td>
            <?
                /*Somente para essa caixa apenas que eu coloco a formatação dentro do próprio Value, porque como eu chamo 
                a função que calcula o Preço Fat. Nac. Min. R$, o sistema afetava essa caixa trocando a vírgula por ponto - 
                de modo que atrapalhava todos os meus cálculos em JavaScript ... agora porque ??? - Acho que dentro da 
                Biblioteca de Custo deve ter alguma variável com esse nome de $fator_margem_lucro_pa que está interferindo 
                aqui dentro dessa Tela - preciso analisar com mais tempo e calma ...*/
            ?>
            <input type='text' name='txt_fator_margem_lucro_pa' value='<?=number_format($fator_margem_lucro_pa, 2, ',', '.');?>' title='Digite o Fator de Margem de Lucro' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calculos_gerais_custo_revenda()" size='7' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
            Padr&atilde;o =&gt; <input type='text' name='txt_fator_margem_lucro_pa2' value="<?=$fator_margem_lucro_pa_padrao;?>" size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Preço de Compra Nac. R$ na forma de Compra:
        </td>
        <td>
            <input type='text' name='txt_preco_compra_nac' value='<?=$preco_compra_nac;?>' title='Preço de Compra Nacional' size='7' class='textdisabled' disabled>
            &nbsp;
            <input type='button' name='cmd_calcular_preco_compra_fat_nac_rs' value='Calcular Pço Compra Fat Nac R$' title='Calcular Preço Compra Fat Nac R$' onclick="nova_janela('calculo_preco_compra_fat_nac_rs.php?id_fornecedor_prod_insumo=<?=$id_fornecedor_prod_insumo;?>', 'CALCULAR_PRECO_COMPRA_FAT_NAC_RS', '', '', '', '', '220', '680', 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        </td>
        <td>
            Preço de Compra Inter. na forma de Compra:
        </td>
        <td>
        <?
            if($tp_moeda == 1) {//Dólar ...
                echo 'U$';
            }else if($tp_moeda == 2) {//Euro ...
                echo '&euro;';
            }
        ?>
            <input type='text' name='txt_preco_compra_inter' value='<?=$preco_exportacao;?>' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Custo P.A. Indust. R$:
        </td>
        <td>
            <?$todas_etapas = custos::todas_etapas($id_produto_acabado, 1);?>
            <input type='text' name='txt_custo_pa_indust' value='<?=number_format($todas_etapas, 2, ',', '.');?>' title='Custo Industrial' size='7' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Preço de Venda Fat. Nac. Min R$:
        </td>
        <td>
            <input type='text' name='txt_preco_venda_fat_nac_min_rs' title='Preço de Venda Fat. Nac. Min R$' size='7' class='textdisabled' disabled>
        </td>
        <td>
            Preço de Venda Fat. Inter. Min R$:
        </td>
        <td>
            <input type='text' name='txt_preco_venda_fat_inter_min_rs' title='Preço de Venda Fat. Inter. Min R$' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Lote Mínimo do Grupo R$:
        </td>
        <td>
            <input type='text' name='txt_lote_minimo_grupo_rs' value='<?=number_format($lote_min_producao_reais, 2, ',', '.');?>' title='Lote Mínimo do Grupo R$' size='7' class='textdisabled' disabled>
        </td>
        <td>
            Vlr Moeda p/ Compra R$:
        </td>
        <td>
            <input type='text' name='txt_valor_moeda_compra' value='<?=$valor_moeda_compra;?>' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Preço de Venda Fat. Nac. Máx R$:
        </td>
        <td>
            <input type='text' name='txt_preco_venda_fat_nac_max_rs' title='Preço de Venda Fat. Nac. Máx R$' size='7' class='textdisabled' disabled>
        </td>
        <td>
            Preço de Venda Fat. Inter. Máx R$:
        </td>
        <td>
            <input type='text' name='txt_preco_venda_fat_inter_max_rs' title='Preço de Venda Fat. Inter. Máx R$' size='7' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Lote Mínimo p/ Venda R$:
        </td>
        <td>
            <input type='text' name='txt_lote_minimo_preco_venda_rs' title='Lote Mínimo p/ Venda R$' size='7' class='textdisabled' disabled>
        </td>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Taxa Financeira de Vendas:</b> <?=number_format($taxa_financeira_vendas, 2, ',', '.');?> %
        </td>
        <td>
            <b>Valor do U$ p/ Custo:</b> <?=number_format($valor_moeda_dolar_custo, 4, ',', '.');?>  -  
        </td>
        <td colspan='2'>
            <b>Valor do &euro; p/ Custo:</b> <?=number_format($valor_moeda_euro_custo, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_lote_minimo_compra.focus();calculos_gerais_custo_revenda()" class='botao'>
            <?
                //Se o Custo está liberado, não é possível ser alterado mais nada ...
                if($status_custo == 1) {
                    $disabled   = 'disabled';
                    $class      = 'textdisabled';
                }else {//Só é possível estar alterando algum campo quando o Custo estiver Bloqueado ...
                    $disabled   = '';
                    $class      = 'botao';
                }
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='<?=$class;?>' <?=$disabled;?>>
            <input type='button' name='cmd_fechar_atualizar' value='Fechar e Atualizar' title='Fechar e Atualizar' onclick='atualizar_abaixo()' style='color:red' class='botao'>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='10'>
            <iframe name='detalhes' id='detalhes' src = '../../../classes/follow_ups/detalhes.php?identificacao=<?=$id_produto_acabado;?>&origem=19' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
</table>
</form>
</body>
</html>
<?
//Controle que serve para essa função de JavaScript -> confirmar_liberacao(), mais abaixo
if($campos_pa[0]['referencia'] == 'ESP') {
/*Listagem de Todos os Orçamento(s) que estão em aberto, que não estão congelados, em que esse PA 
"item de ORC" esteja sem "prazo de Entrega do Técnico" preenchido ...*/
    $sql = "SELECT ovi.`id_orcamento_venda_item` 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`status` < 2 AND ov.`congelar` = 'N' 
            WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' 
            AND ovi.`prazo_entrega_tecnico` = '' LIMIT 1 ";
    $campos_orcamento = bancos::sql($sql);
//Se encontrar algum item que tenha o prazo de entrega técnico como zerado, então não pode liberar o custo
    if(count($campos_orcamento) == 1) {
        $custo_nao_pode_liberar = 1;
    }else {
        $custo_nao_pode_liberar = 0;
    }
}else {//Se for Industrial
    $custo_nao_pode_liberar = 0;
}
?>
<!--Joguei essa function aki em baixo, devido a variável $custo_nao_pode_liberar-->
<Script Language = 'JavaScript'>
function confirmar_liberacao() {
    if(document.form.chkt_custo_liberado.checked == true) {//Vai liberar o custo
        var preco_compra_nacional   = eval(strtofloat(document.form.txt_preco_compra_nac.value))
        var preco_compra_inter      = eval(strtofloat(document.form.txt_preco_compra_inter.value))
    
        if(preco_compra_nacional == 0 && preco_compra_inter == 0) {
            alert('ESSE CUSTO NÃO PODE SER LIBERADO !!!\n\nO "PREÇO COMPRA FAT. NAC. R$" E "PREÇO DE COMPRA INTER. NA FORMA DE COMPRA" ESTÃO ZERADOS !')
            document.form.chkt_custo_liberado.checked = false
            document.form.txt_preco_compra_nac.focus()
            document.form.txt_preco_compra_nac.select()
            return false
        }
/***************************Tratamento de Margem de Lucro**************************/
//Agora se a M.L. for = 0, eu também não posso liberar o Custo do PA
        if(document.form.txt_fator_margem_lucro_pa.value == '' || document.form.txt_fator_margem_lucro_pa.value == '0,00') {
            alert('ESSE CUSTO NÃO PODE SER LIBERADO !\nO FATOR MARGEM DE LUCRO DESSE P.A. É = 0,00 !')
            document.form.chkt_custo_liberado.checked = false
            document.form.txt_fator_margem_lucro_pa.focus()
            document.form.txt_fator_margem_lucro_pa.select()
            return false
        }
/**********************************************************************************/
/*Na hora de liberar o custo, o Sistema verifica se o Depto. Técnico já deu o prazo para este Item
do Orçamento do PA atrelado, se isso ainda não aconteceu, não posso liberar o custo*/
        var custo_nao_pode_liberar = eval('<?=$custo_nao_pode_liberar;?>')
        if(custo_nao_pode_liberar == 1) {
            alert('ESSE CUSTO NÃO PODE SER LIBERADO, PREENCHA O PRAZO DE ENTREGA DO P.A. !')
            document.form.chkt_custo_liberado.checked = false
            showHide('alterar_prazo_entrega_tecnico')
            return false
        }
        var mensagem = confirm('DESEJA LIBERAR O CUSTO ?')
        if(mensagem == false) {
                document.form.chkt_custo_liberado.checked       = false
                return false
        }else {
            document.form.passo.value                           = 1
            document.form.nao_atualizar.value                   = 1
//Desabilito esse(s) campo(s) p/ poder gravar na Base de Dados ...
            document.form.txt_preco_compra_nac.disabled         = false
            document.form.txt_fator_margem_lucro_pa.disabled    = false
            limpeza_moeda('form', 'txt_preco_compra_fat_nac, txt_prazo_pgto_ddl, txt_desc_vista, txt_desc_sgd, txt_icms, txt_reducao, txt_fator_margem_lucro_pa, txt_preco_compra_nac, ')
            document.form.submit()
        }
    }else {//Vai bloquear o custo
        var mensagem = confirm('DESEJA BLOQUEAR O CUSTO ?')
        if(mensagem == false) {
            document.form.chkt_custo_liberado.checked = true
            return false
        }else {
            document.form.passo.value                           = 1
            document.form.nao_atualizar.value                   = 1
//Desabilito esse(s) campo(s) p/ poder gravar na Base de Dados ...
            document.form.txt_preco_compra_nac.disabled         = false
            document.form.txt_fator_margem_lucro_pa.disabled    = false
            limpeza_moeda('form', 'txt_preco_compra_fat_nac, txt_prazo_pgto_ddl, txt_desc_vista, txt_desc_sgd, txt_icms, txt_reducao, txt_fator_margem_lucro_pa, txt_preco_compra_nac, ')
            document.form.submit()
        }
    }
}
</Script>
<!--Aqui nós enxergamos o Custo Industrial desse Produto Acabado ...-->
<center>
    <iframe src='../industrial/custo_industrial.php?id_produto_acabado=<?=$id_produto_acabado;?>&pop_up=1' width='100%' height='280' frameborder='1'></iframe>
</center>