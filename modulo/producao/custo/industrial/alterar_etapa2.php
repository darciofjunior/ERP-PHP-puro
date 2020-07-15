<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/estoque_new.php');//Essa biblioteca é requerida dentro da Produção ...
require('../../../../lib/intermodular.php');//Essa biblioteca é requerida dentro da Custos ...
require('../../../../lib/producao.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$id_produto_acabado_custo   = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado_custo'] : $_GET['id_produto_acabado_custo'];

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if($passo == 1) {
    //Busca do Produto Acabado e Produto Insumo atual antes da Alteração ...
    $sql = "SELECT `id_produto_acabado`, `id_produto_insumo` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
    $campos_custo               = bancos::sql($sql);
    $id_produto_acabado         = $campos_custo[0]['id_produto_acabado'];
    $id_produto_insumo_custo    = $campos_custo[0]['id_produto_insumo'];//Produto Insumo Atual ...
//Verifico se houve alguma alteração do Produto Insumo "Aço" ...
    if($id_produto_insumo_custo != $_POST['cmb_produto_insumo_utilizada']) {//Se houve alteração então chamo a Função ...
        producao::verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado, $id_produto_insumo_custo, $_POST['cmb_produto_insumo_utilizada'], 2);
    }
    /*******************************************************************************/
    //Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
    /*******************************************************************************/
    $id_produto_insumo_utilizada    = (!empty($_POST[cmb_produto_insumo_utilizada])) ? "'".$_POST[cmb_produto_insumo_utilizada]."'" : 'NULL';
    $id_produto_insumo_ideal        = (!empty($_POST[cmb_produto_insumo_ideal])) ? "'".$_POST[cmb_produto_insumo_ideal]."'" : 'NULL';
    
    //Etapa 2 - Atualização na tabela Produtos Acabados Custos + o Funcionário
    $sql = "UPDATE `produtos_acabados_custos` SET `id_produto_insumo` = $id_produto_insumo_utilizada, `id_produto_insumo_ideal` = $id_produto_insumo_ideal, `id_funcionario` = '$_SESSION[id_funcionario]', `qtde_lote` = '$_POST[txt_lote_custo]', `peso_kg` = '$_POST[txt_peso_aco_kg]', `peca_corte` = '$_POST[txt_pecas_corte]', `comprimento_1` = '$_POST[txt_comprimento_1]', `comprimento_2` = '$_POST[txt_comprimento_2]', `comprimento_barra` = '$_POST[txt_comprimento_barra]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
    bancos::sql($sql);

    //Atualiza o Peso Aço da Etapa 5, mais somente quando esse "NÃO estiver usando o PESO REAL" que é o digitado manualmente ...
    $peso_aco_kg = $_POST[txt_peso_aco_kg] / 1.05;//Essa variável tem q abater 5% a menos nessa etapa ...

    $sql = "UPDATE `pacs_vs_pis_trat` SET `peso_aco` = '$peso_aco_kg' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' AND `peso_aco_manual` = '0' ";
    bancos::sql($sql);
    $valor = 1;
}

//Busca de um valor para fator custo para etapa 2
$fator_custo_2 = genericas::variavel(11);
//Essa variável vai estar sendo acionada para o caso de o usuário digitar na qtde um valor maior do que 1000 ...
$fator_custo_2_new = genericas::variavel(18);

//Aqui traz todos os produtos insumos que estão relacionados ao produto acabado passado por parâmetro ...
$sql = "SELECT id_produto_insumo, id_produto_insumo_ideal, qtde_lote, peso_kg, peca_corte, 
        comprimento_1, comprimento_2, comprimento_barra 
        FROM `produtos_acabados_custos` 
        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
$campos = bancos::sql($sql);

/******************Somente na primeira vez em que carregar a tela******************/

/*Tem esse controle por que às vezes não se quer exatamente trocar a matéria prima dessa etapa, mas sim 
fazer algumas simulações com outros produtos*/

//Iguala o Produto ao retorno da consulta do Banco de Dados
if(empty($_POST['cmb_produto_insumo_utilizada'])) $_POST['cmb_produto_insumo_utilizada'] = $campos[0]['id_produto_insumo'];

$qtde_lote = $campos[0]['qtde_lote'];
/*Aqui verifica se a quantidade do lote é > 1000, porque caso isso aconteça então sofrerá alterações no 
valor do fator de custo da Etapa 2 ...*/
if($qtde_lote > 1000) $fator_custo_2 = $fator_custo_2_new;
$pecas_corte        = ($campos[0]['peca_corte'] == 0) ? 1 : $campos[0]['peca_corte'];
$comprimento_1      = (!empty($_POST['txt_comprimento_1'])) ? $_POST['txt_comprimento_1'] : $campos[0]['comprimento_1'];
$comprimento_2      = (!empty($_POST['txt_comprimento_2'])) ? $_POST['txt_comprimento_2'] : $campos[0]['comprimento_2'];
$comprimento_barra  = (!empty($_POST['txt_comprimento_barra'])) ? $_POST['txt_comprimento_barra'] : $campos[0]['comprimento_barra'];

//Aqui eu trago o produto acabado do produto acabado custo que está armazenado em um hidden ...
$sql = "SELECT `id_produto_acabado`, `operacao_custo` AS operacao_custo_prac 
        FROM `produtos_acabados_custos` 
        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
$campos_custo           = bancos::sql($sql);
$id_produto_acabado     = $campos_custo[0]['id_produto_acabado'];
$operacao_custo_prac    = $campos_custo[0]['operacao_custo_prac'];

//Busco a Família e a Referência do PA que está vinculado a este Custo ...
$sql = "SELECT gpa.`id_familia`, pa.`referencia`, pa.`status_custo`, u.`sigla` 
        FROM `produtos_acabados` pa 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos_pa      = bancos::sql($sql);
$id_familia     = $campos_pa[0]['id_familia'];
$referencia     = $campos_pa[0]['referencia'];
$status_custo   = $campos_pa[0]['status_custo'];
$unidade        = $campos_pa[0]['sigla'];

//Essa já prepara as variáveis para o cálculo das etapas do custo
$taxa_financeira_vendas = genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
//custos::custo_auto_pi_industrializado();//tem q ser antes das chamadas dos metodos todas_etapas(PA); tempo q gasta é quase zero
$total_indust = custos::todas_etapas($id_produto_acabado, $operacao_custo_prac);

$id_produto_insumo_selected = (!empty($_POST['cmb_produto_insumo_utilizada'])) ? $_POST['cmb_produto_insumo_utilizada'] : $campos[0]['id_produto_insumo'];

/*Verifico se existe pelo menos um item na 5ª Etapa do Custo desse PA, 
*** Essa variável será utilizada mais abaixo em JavaScript ...*/
$sql = "SELECT id_pac_pi_trat 
        FROM `pacs_vs_pis_trat` 
        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
$campos_etapa5 = bancos::sql($sql);
$linhas_etapa5 = count($campos_etapa5);
?>
<html>
<head>
<title>.:: Alterar Custo A&ccedil;o / Outros Metais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var referencia          = '<?=$referencia;?>'
    var operacao_custo      = '<?=$operacao_custo_prac;?>'
    var id_familia          = '<?=$id_familia;?>'
    var qtde_lote_original  = '<?=$qtde_lote;?>'
    var linhas_etapa5       = '<?=$linhas_etapa5;?>'
//Aço - PI ...	
    if(!combo('form', 'cmb_produto_insumo_utilizada', '', 'SELECIONE UM AÇO !')) {
        return false
    }
//Só pode fazer a comparação se o Produto for do tipo Esp e a Operação de Custo for do Tipo Industrial
    if(referencia == 'ESP' && operacao_custo == 0) {
        if(eval(document.form.txt_lote_custo.value) % eval(document.form.txt_pecas_corte.value) != 0) {
            alert('QUANTIDADE DE PEÇAS/CORTE INVÁLIDA ! QUANTIDADE DE PEÇAS/CORTE NÃO ESTÁ COMPATÍVEL COM A QUANTIDADE DO LOTE !')
            return false
        }
    }
//Aqui compara a quantidade do lote custo inicial 'PHP' com a quantidade do lote que foi recalculada 'JavaScript'
    if(qtde_lote_original != document.form.txt_lote_custo.value) {
        alert('A QUANTIDADE DE LOTE DO CUSTO FOI ALTERADA PARA '+document.form.txt_lote_custo.value+' PÇS !')
    }
/* Essa verificação só será feito se existir pelo menos um item na 5ª Etapa do Custo desse PA ...
*** Verifico se a família é do Tipo Bits e Bedames id => 10 ou do Tipo Pinos id => 2, caso entrar essa rotina 
então o sistema apenas da um aviso*/
    if(linhas_etapa5 == 1 && (id_familia == 10 || id_familia == 2) && document.form.txt_comprimento_1_na_unidade.value >= 150) {
        alert('CUIDADO COMPRIMENTO >= 150 mm ! MATERIAL SUJEITO A EMPENAMENTO !!!')
        window.focus()
    }
    document.form.txt_lote_custo.disabled   = false
    document.form.txt_peso_aco_kg.disabled  = false
    limpeza_moeda('form', 'txt_peso_aco_kg, ')
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
    atualizar_abaixo()
}

function calculo_etapa2() {
    var qtde_lote   = '<?=$qtde_lote;?>'
    var unidade     = '<?=$unidade;?>'
        
    if(unidade == 'MI') {//Milheiro ...
        var proporcao = 1000
    }else if(unidade == 'CT') {//Cento ...
        var proporcao = 100
    }else {//Outras Unidades
        var proporcao = 1
    }

    document.form.txt_comprimento_1_na_unidade.value                = document.form.txt_comprimento_1.value * proporcao
    document.form.txt_comprimento_2_na_unidade.value                = document.form.txt_comprimento_2.value * proporcao
    
    var comprimento_1_na_unidade        = strtofloat(document.form.txt_comprimento_1_na_unidade.value)
    if(comprimento_1_na_unidade == '')  comprimento_1_na_unidade = 0
    var comprimento_2_na_unidade        = strtofloat(document.form.txt_comprimento_2_na_unidade.value)
    if(comprimento_2_na_unidade == '')  comprimento_2_na_unidade = 0

    document.form.txt_comprimento_total.value = (eval(comprimento_1_na_unidade) + eval(comprimento_2_na_unidade)) / 1000 * 1.05//Multiplico por esse 1.05 porque representa 5% de perda ...
    document.form.txt_comprimento_total.value = (document.form.txt_comprimento_total.value == 0) ? '' : arred(document.form.txt_comprimento_total.value, 3, 1)
    
    if(document.form.txt_comprimento_total.value != '') {
        var comprimento_total                   = strtofloat(document.form.txt_comprimento_total.value)
        var comprimento_peca_usando_toda_barra  = strtofloat(document.form.txt_comprimento_barra.value) / qtde_lote
        
        if(comprimento_total < (comprimento_peca_usando_toda_barra / 1000)) {//Essa divisão por 1000 é p/ convertermos em Milimetros em Metros ...
            /*Aqui o sistema reassume o comprimento Total porque como estamos cobrando do Cliente a barra inteira 
            aqui corrigimos o comprimento da Peça ...*/
            document.form.txt_comprimento_total.value = comprimento_peca_usando_toda_barra / 1000
            document.form.txt_comprimento_total.value = arred(document.form.txt_comprimento_total.value, 3, 1)
            var comprimento_total   = strtofloat(document.form.txt_comprimento_total.value)
        }

        var pecas_corte     = document.form.txt_pecas_corte.value
        if(pecas_corte == 0 || pecas_corte == '' || pecas_corte == '0.00') pecas_corte = 1
        var densidade_kg_m  = strtofloat(document.form.txt_densidade_kg_m.value)
        
        document.form.txt_peso_aco_kg.value = eval(densidade_kg_m) * eval(comprimento_total)
        document.form.txt_peso_aco_kg.value = document.form.txt_peso_aco_kg.value / pecas_corte

        var peso_aco_kg                     = document.form.txt_peso_aco_kg.value
        document.form.txt_peso_aco_kg.value = arred(document.form.txt_peso_aco_kg.value, 3, 1)
//Cálculo das Qtde necessária para o Lote Kg
        document.form.txt_lote_custo_calculo1.value = (peso_aco_kg * qtde_lote)
//Cálculo das Qtde necessária para o Lote Metros
        document.form.txt_lote_custo_calculo2.value = document.form.txt_lote_custo_calculo1.value / densidade_kg_m
//Arredondamentos
        document.form.txt_lote_custo_calculo1.value = arred(document.form.txt_lote_custo_calculo1.value, 2, 1)
        document.form.txt_lote_custo_calculo2.value = arred(document.form.txt_lote_custo_calculo2.value, 2, 1)
    }
    var referencia      = '<?=$referencia;?>'
    var operacao_custo  = '<?=$operacao_custo_prac;?>'
//Só pode fazer a comparação se o Produto for do tipo Esp e a Operação de Custo for do Tipo Industrial
    if(referencia == 'ESP' && operacao_custo == 0) {
        var lote_custo	= '<?=$qtde_lote;?>'
        if(pecas_corte > lote_custo) {
            document.form.txt_lote_custo.value = document.form.txt_pecas_corte.value
        }else {
            for(var temp=lote_custo; temp > 1; temp--) {
                if(temp % pecas_corte == 0) {
                    document.form.txt_lote_custo.value = temp
                    temp = 0
                }
            }
        }
    }
}

var contador = 0

function carregar_acos() {
    var status_custo    = eval('<?=$status_custo;?>')
    
    if(contador == 0) {
        var id_produto_insumo_selected = eval('<?=$id_produto_insumo_selected;?>')
        ajax('carregar_acos.php', 'cmb_produto_insumo_utilizada', id_produto_insumo_selected)
    }else {
        ajax('carregar_acos.php', 'cmb_produto_insumo_utilizada')
    }
    //Custo Bloqueado "posso fazer qualquer alteração" então executo o comando abaixo ...
    if(status_custo == 0) {
        //Só irá desabilitar o Botão Salvar, quando carregar pelo menos um Aço dentro da Combo ...
        if(document.form.cmb_produto_insumo_utilizada.length > 0) {
            document.form.cmd_salvar.disabled   = false
            document.form.cmd_salvar.className  = 'botao'
        }
    }
    contador++
}

function alterar_produto_insumo() {
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.submit()
}

function compras(id_produto_insumo) {
    nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo='+id_produto_insumo+'&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')
}

function comprimento_peca() {
    if(document.form.txt_comprimento_barra.value != '' && document.form.txt_comprimento_barra.value > 0) {//Se o Comprimento da Barra estiver preenchido, o sistema calcula o Comprimento da Peça ...
        var qtde_lote   = eval('<?=$qtde_lote;?>')

        document.form.txt_quantidade_barras.value                       = parseInt(qtde_lote * ((eval(document.form.txt_comprimento_1_na_unidade.value) + eval(document.form.txt_comprimento_2_na_unidade.value)) / document.form.txt_pecas_corte.value) / document.form.txt_comprimento_barra.value) + 1//Somo + 1 porque se der 1,3 por exemplo teremos que usar 2 barras ...
        document.form.txt_comprimento_peca_usando_todas_barras.value    = document.form.txt_comprimento_barra.value * document.form.txt_quantidade_barras.value / qtde_lote
        document.form.txt_comprimento_peca_usando_todas_barras.value    = arred(document.form.txt_comprimento_peca_usando_todas_barras.value, 2, 1)
    }else {//Se o Comprimento da Barra estiver Vázio, o sistema zera o Valor dessa Caixinha ...
        document.form.txt_quantidade_barras.value                       = ''    
        document.form.txt_comprimento_peca_usando_todas_barras.value    = ''
    }
}

function controlar_cor_pi_ideal() {
    var id_produto_insumo_selected  = eval('<?=$id_produto_insumo_selected;?>')
    var id_produto_insumo_ideal     = document.form.cmb_produto_insumo_ideal.value

    //P/ que se perceba que o PI Utilizado é diferente do PI Ideal, "nós mudamos" a cor de Fundo e da letra da Combo ...
    if(id_produto_insumo_selected != id_produto_insumo_ideal) {
        document.form.cmb_produto_insumo_ideal.style.background = 'red'
        document.form.cmb_produto_insumo_ideal.style.color      = 'white'
    }else {
        document.form.cmb_produto_insumo_ideal.style.background = 'white'
        document.form.cmb_produto_insumo_ideal.style.color      = 'brown'
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}
</Script>
</head>
<body onload='carregar_acos();controlar_cor_pi_ideal();comprimento_peca();calculo_etapa2();document.form.txt_comprimento_1.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<!--***************Controle de Tela***************-->
<input type='hidden' name='passo'>
<input type='hidden' name='nao_atualizar'>
<!--**********************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            2&ordf; Etapa: Alterar Custo A&ccedil;o / Outros Metais
            &nbsp;&nbsp;&nbsp;&nbsp;
            <font color='yellow'>
                &nbsp;Total R$ 
            </font>
            <?=number_format($GLOBALS['etapa2'], 2, ',', '.');?>
        </td>
    </tr>
    <?
        //Busca da Unidade da Matéria Prima e mais alguns alguns campos na parte de Aço ...
        $sql = "SELECT pia.`id_geometria_aco`, pia.`id_qualidade_aco`, u.`sigla` 
                FROM `produtos_insumos` pi 
                INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade`  
                WHERE pi.`id_produto_insumo` = '$_POST[cmb_produto_insumo_utilizada]' 
                AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        $campos_produto_insumo  = bancos::sql($sql);
        $sigla                  = $campos_produto_insumo[0]['sigla'];
        /*Esse controle é para que as Combos de Geometria do Aço e Qualidade do Aço, sejam carregadas com menos itens, 
        fazendo com que a Tela fique mais leve ...*/
        $id_geometria_aco       = (!empty($_POST['cmb_geometria_aco'])) ? $_POST['cmb_geometria_aco'] : $campos_produto_insumo[0]['id_geometria_aco'];
        $id_qualidade_aco       = (!empty($_POST['cmb_qualidade_aco'])) ? $_POST['cmb_qualidade_aco'] : $campos_produto_insumo[0]['id_qualidade_aco'];
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Filtro para AÇO
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b><i>Geometria: </i></b>
            </font>
            <select name='cmb_geometria_aco' title='Selecione a Geometria do Aço' onchange='carregar_acos()' class='combo'>
            <?
                $sql = "SELECT id_geometria_aco, nome 
                        FROM `geometrias_acos` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql, $id_geometria_aco);
            ?>
            </select>
        </td>
        <td>
            <font color='darkblue'>
                <b><i>Qualidade: </i></b>
            </font>
            <select name='cmb_qualidade_aco' title='Selecione a Qualidade do Aço' onchange='carregar_acos()' class='combo'>
            <?
                $sql = "SELECT id_qualidade_aco, nome 
                        FROM `qualidades_acos` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql, $id_qualidade_aco);
            ?>
            </select>
        </td>
        <td>
            <font color='darkblue'>
                <b><i>Bitola 1: </i></b>
            </font>
            <input type='text' name='txt_bitola1_aco' value="<?if(!empty($_POST['txt_bitola1_aco'])) {echo number_format($_POST['txt_bitola1_aco'], 2, ',', '.');}?>" title="Digite a Bitola 1 Aço" onkeyup="verifica(this, 'moeda_especial', '2', '', event);carregar_acos()" size='10' maxlength='20' class='caixadetexto'> mm
        </td>
        <td>
            <font color='darkblue'>
                <b><i>Bitola 2: </i></b>
            </font>
            <input type='text' name='txt_bitola2_aco' value="<?if(!empty($_POST['txt_bitola2_aco'])) {echo number_format($_POST['txt_bitola2_aco'], 2, ',', '.');}?>" title="Digite a Bitola 2 Aço" onkeyup="verifica(this, 'moeda_especial', '2', '', event);carregar_acos()" size='10' maxlength='20' class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b><i>Discriminação Utilizada:</i></b>
            </font>
        </td>
        <td colspan='3'>
            <select name='cmb_produto_insumo_utilizada' id='cmb_produto_insumo_utilizada' onchange='alterar_produto_insumo()' class='combo'>
                <option value=''>LOADING ...</option>
            </select>
            <?=$sigla;?>
            &nbsp;
            <a href="javascript:compras(document.form.cmb_produto_insumo_utilizada.value)" title='Compras' class='link'>
                <font color='red' title='Compra' style='cursor:help'>
                    <b>(Compras)</b>
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='#FFFF00'>
                <font color='green'>
                    <b><i>Comprimento da Barra:</i></b>
                </font>
            </font>
        </td>
        <td>
            <input type='text' name='txt_comprimento_barra' value='<?=$comprimento_barra;?>' title='Digite o Comprimento da Barra' onkeyup="verifica(this, 'aceita', 'numeros_inteiros', '', event);comprimento_peca();calculo_etapa2()" maxlength='5' size='4' class='caixadetexto'> MM&nbsp;
            &nbsp;-&nbsp;
            <input type='text' name='txt_quantidade_barras' maxlenght='3' size='3' class='textdisabled' disabled> barra(s) p/ Lote
        </td>
        <td>
            <font color='green'>
                <b><i>Comprimento da Peça usando toda(s) Barra(s):</i></b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_comprimento_peca_usando_todas_barras' size='8' class='textdisabled' disabled> MM&nbsp;&nbsp;
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
    <?
//Traz o preço custo e a densidade do produto insumo que está selecionado na combo
        $sql = "SELECT pia.`densidade_aco` 
                FROM `produtos_insumos` pi 
                INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                WHERE pi.`id_produto_insumo` = '$_POST[cmb_produto_insumo_utilizada]' LIMIT 1 ";
        $campos_aco = bancos::sql($sql);
        if(count($campos_aco) == 1) {
            $preco_custo    = custos::preco_custo_pi($_POST['cmb_produto_insumo_utilizada']);
            $preco_custo    = number_format($preco_custo, 2, ',', '.');
            $densidade      = $campos_aco[0]['densidade_aco'];
        }else {
            $preco_custo    = '';
            $densidade      = '';
        }
    ?>
    <tr class='linhanormal'>
        <td>
            Preço R$ / Kg sem ICMS:
        </td>
        <td>
            <i>Comprimento da Peça:</i>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='red'>
                <b>Comprimento do(a) <?=$unidade;?>:</b>
            </font>
        </td>
        <td>
            <i>Corte:</i>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='red'>
                <b>Corte do(a) <?=$unidade;?>:</b>
            </font>
        </td>
        <td>
            <i>Comprimento Total + 5%:</i>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_preco_rs_kg' value="<?=$preco_custo;?>" title='Preço R$ / Kg' size='15' class='textdisabled' disabled>&nbsp;<?=$sigla;?>
        </td>
        <td>
            <input type='text' name='txt_comprimento_1' value="<?=$comprimento_1;?>" title='Digite o Comprimento da Peça' onkeyup="verifica(this, 'aceita', 'numeros_inteiros', '', event);comprimento_peca();calculo_etapa2()" size='8' class='caixadetexto'> MM&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='text' name='txt_comprimento_1_na_unidade' size='8' class='textdisabled' disabled> MM&nbsp;&nbsp;
        </td>
        <td>
            <input type='text' name='txt_comprimento_2' value="<?=$comprimento_2;?>" title='Digite o Corte' onkeyup="verifica(this, 'aceita', 'numeros', '', event);comprimento_peca();calculo_etapa2()" size='5' class='caixadetexto'> MM&nbsp;
            &nbsp;&nbsp;&nbsp;
            <input type='text' name='txt_comprimento_2_na_unidade' size='5' class='textdisabled' disabled> MM&nbsp;
        </td>
        <td>
            <input type='text' name='txt_comprimento_total' title='Comprimento Total' size='12' class='textdisabled' disabled>&nbsp;M
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peças / Corte:
        </td>
        <td>
            Densidade Kg / M :
        </td>
        <td>
            <?
                $sql = "SELECT u.`sigla` 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                $campos_unidade = bancos::sql($sql);
            ?>
            Peso por <?=$campos_unidade[0]['sigla'];?>:
        </td>
        <td>
            Qtde de lote do Custo:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_pecas_corte' value='<?=$pecas_corte;?>' title='Digite as Peças / Corte' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value != '') {this.value = Math.round(this.value)};calculo_etapa2()" size='15' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_densidade_kg_m' value='<?=number_format($densidade, 3, ',', '.');?>' title='Densidade de Kg / M' size='15' class='textdisabled' disabled>
        </td>
        <td>
        <?
            $peso_aco_kg = $densidade * $comprimento_total;
            $peso_aco_kg/= $pecas_corte;
        ?>
            <input type='text' name='txt_peso_aco_kg' value='<?=number_format($peso_aco_kg, 3, ',', '.');?>' title='Peso KG' size='15' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_lote_custo' value='<?=$qtde_lote;?>' title='Peso KG' size='15' class='textdisabled' disabled>
        </td>
    </tr>
<?
//Aqui são os cálculos para q Qtde do Lote do Custo
    $lote_custo_calculo1 = $peso_aco_kg * $qtde_lote;
    $lote_custo_calculo2 = $lote_custo_calculo1 / $densidade;
?>
    <tr class='linhanormal'>
        <td>
            <font color='#FFFF00'>
                <font color='green'>
                    <b><i>Qtde necessária p/ o Lote:</i></b>
                </font>
            </font>
        </td>
        <td>
            <input type='text' name="txt_lote_custo_calculo1" value="<?=number_format($lote_custo_calculo1, 3, ',', '.');?>" title='Qtde necessária p/ o Lote' size='15' class='textdisabled' disabled>
            <font color='#000000'>Kg</font>
        </td>
        <td>
            <font color='#FFFF00'>
                <font color='green'>
                    <b><i>Qtde necessária p/ o Lote:</i></b>
                </font>
            </font>
        </td>
        <td>
            <input type='text' name='txt_lote_custo_calculo2' value="<?=number_format($lote_custo_calculo2, 3, ',', '.');?>" title='Qtde necessária p/ o Lote' size='15' class='textdisabled' disabled>
            <font color='#000000'>Metros</font>
        </td>
    </tr>
<?
        //Traz a Qtde em estoque da Matéria Prima "PI" ...
        $sql = "SELECT `qtde` AS qtde_estoque 
                FROM `estoques_insumos` 
                WHERE `id_produto_insumo` = '$_POST[cmb_produto_insumo_utilizada]' LIMIT 1 ";
        $campos_estoque_pi = bancos::sql($sql);
        if(count($campos_estoque_pi) == 1) {
            $qtde_estoque   = number_format($campos_estoque_pi[0]['qtde_estoque'], 2, ',', '.');
            $qtde_estoque2  = number_format(($campos_estoque_pi[0]['qtde_estoque'] / $densidade), 2, ',', '.');
        }else {
            $qtde_estoque   = '0,00';
            $qtde_estoque2  = '0,00';
        }
?>
    <tr class='linhanormal'>
        <td>
            <font color='#FFFF00'>
                <font color='green'>
                    <b><i>Estoque do Produto Insumo:</i></b>
                </font>
            </font>
        </td>
        <td>
            <input type='text' name='txt_estoque' value='<?=$qtde_estoque;?>' title='Digite o Custor Fator' size='15' class='textdisabled' disabled>
            <font color='#000000'>Kg</font>
        </td>
        <td>
            <font color='#FFFF00'>
                <font color='green'>
                    <b><i>Estoque do Produto Insumo:</i></b>
                </font>
            </font>
        </td>
        <td>
            <input type='text' name='txt_estoque2' value='<?=$qtde_estoque2;?>' title='Digite o Custor Fator' size='15' class='textdisabled' disabled>
            <font color='#000000'>Metros</font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b><i>Discriminação Ideal:</i></b>
            </font>
        </td>
        <td colspan='3'>
            <select name='cmb_produto_insumo_ideal' title='Selecione o Produto Insumo Ideal' onchange='controlar_cor_pi_ideal()' class='combo'>
            <?
                //Listo todos os aços de acordo com a Geometria, Qualidade e Bitolas ...
                $sql = "SELECT pi.`id_produto_insumo`, CONCAT(pi.`discriminacao`, ' | ', REPLACE(ROUND(ei.`qtde` / pia.`densidade_aco`, 2), '.', ','), ' m | ', REPLACE(ei.`qtde`, '.', ','), ' kg') AS rotulo 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
                        INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                        WHERE pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
                echo combos::combo($sql, $campos[0]['id_produto_insumo_ideal']);
            ?>
            </select>
            <?=$sigla;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');carregar_acos();calculo_etapa2();document.form.txt_comprimento_1.focus()" style='color:#ff9900' class='botao'>
            <?
                if($status_custo == 1) {//Custo Bloqueado, não é possível "Salvar" nada nessa Etapa ...
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                }else {//Custo Liberado, pode "Salvar" qualquer alteração nessa Etapa ...
                    $class      = 'botao';
                    $disabled   = '';
                }
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='<?=$class;?>' <?=$disabled;?>>
            <input type='button' name='cmd_alterar_fornecedores' value='Alterar Fornecedores' title='Alterar Fornecedores' onClick="showHide('alterar_fornecedores'); return false" style='color:black' class='botao'>
        </td>
    </tr>
</table>
<!--Agora sempre irá mostrar esse Iframe-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td align='center'>
            &nbsp;
        </td>
        <td align='right'>
            &nbsp;
            <span id='statusalterar_fornecedores'></span>
            <span id='statusalterar_fornecedores'></span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <iframe src='../../../classes/produtos_insumos/marcar_fornecedor_default.php?id_produto_insumo=<?=$_POST['cmb_produto_insumo_utilizada'];?>' name="alterar_fornecedores" id='alterar_fornecedores' marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="260" width="100%" scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<!--Controle para saber se vai estar mostrando este Iframe para o Usuário-->
<?
//Verifico se esse PI corrente está em algum Pedido de Compras ...
    $sql = "SELECT id_item_pedido 
            FROM `itens_pedidos` 
            WHERE `id_produto_insumo` = '$_POST[cmb_produto_insumo_utilizada]' LIMIT 1 ";
    $campos_pedido = bancos::sql($sql);
    if(count($campos_pedido) == 0) {//Como não está, exibo essa Tela com Todos os Fornecedores desse PI ...
?>
<Script Language = 'JavaScript'>
/*Idéia de Onload

Na primeira vez em que carregar essa Tela, caso venha existir algum Pedido de Compras para esse PI, então 
eu disparo por meio do JavaScript essa função para que já venha mostrar esse iframe ...*/
	showHide('alterar_fornecedores')
</Script>
<?
    }
?>
</form>
</body>
</html>