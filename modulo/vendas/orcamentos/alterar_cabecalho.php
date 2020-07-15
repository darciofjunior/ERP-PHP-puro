<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../lib/data.php');
require('../../../lib/intermodular.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../lib/vendas.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>ORÇAMENTO ALTERADO COM SUCESSO.</font>";

//Tratamento com as variáveis que vem por parâmetro ...
$id_orcamento_venda = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_orcamento_venda'] : $_GET['id_orcamento_venda'];

//Aki eu verifico se tem algum item do Orçamento q já está em Pedido, caso sim, eu não posso mais descongelar
function verificar_pedido($id_orcamento_venda) {
    $sql = "SELECT `id_orcamento_venda_item` 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
            AND `status` > '0' LIMIT 1 ";
    $campos = bancos::sql($sql);//Se encontrar pelo menos 1 item, já ta bom pra mim saber
    $linhas = count($campos);
    return $linhas;
}

if($passo == 1) {
    /*Se existirem muitos itens de Orçamento, isso faz com que o sistema fique muito pesado e trave a tela não concluindo toda a Rotina, 
    então sendo assim aumentei o timer em específico p/ essa Rotina = 600 segundos = 10 minutos ...*/
    set_time_limit(600);
    
    $desconto_icms_sgd  = (!empty($_POST['chkt_desconto_icms_sgd'])) ? 1 : 0;
    $cartao_bndes       = (!empty($_POST['chkt_cartao_bndes'])) ? 'S' : 'N';
/***Aki eu verifico a situação atual do Orçamento, se este está congelado ou não e se esta com Desconto de "ICMS/SGD" ***/
    $sql = "SELECT `finalidade`, `tipo_frete`, `nota_sgd`, `desc_icms_sqd_auto`, `prazo_medio`, `congelar` 
            FROM `orcamentos_vendas` 
            WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $finalidade                 = $campos[0]['finalidade'];
    $tipo_frete                 = $campos[0]['tipo_frete'];
    $nota_sgd                   = $campos[0]['nota_sgd'];
    $desconto_icms_sgd_gravado  = $campos[0]['desc_icms_sqd_auto'];
    $prazo_medio_gravado        = $campos[0]['prazo_medio'];
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $id_transportadora          = (!empty($_POST[cmb_cliente_transportadora])) ? $_POST[cmb_cliente_transportadora] : 'NULL';
/**********************Controle da Tabela de Orçamentos - Cabeçalho**********************/
    /*Orçamento Descongelado e não existem Itens em Queima de Estoque, posso salvar 
    todos os campos se ...*/
    if($campos[0]['congelar'] == 'N' && $_POST['hdd_possui_queima_estoque'] == 'N') {
        $prazo_medio = intermodular::prazo_medio($_POST['txt_prazo_a'], $_POST['txt_prazo_b'], $_POST['txt_prazo_c'], $_POST['txt_prazo_d']);
/*******************************************************/
        if($_POST['hdd_possui_promocao'] == 'N') {/*Nenhum item está em Promoção, então posso salvar 
        todos os campos normalmente ...*/
            $sql = "UPDATE `orcamentos_vendas` SET `id_transportadora` = $id_transportadora, `id_cliente_contato` = '$_POST[cmb_cliente_contato]', `finalidade` = '$_POST[cmb_finalidade]', `tipo_frete` = '$_POST[cmb_tipo_frete]', `valor_frete_estimado` = '$_POST[txt_valor_frete_estimado]', `artigo_isencao` = '$_POST[chkt_artigo_isencao]', `nota_sgd` = '$_POST[cmb_tipo_nota]', `desc_icms_sqd_auto` = '$desconto_icms_sgd', `cartao_bndes` = '$cartao_bndes', `prazo_a` = '$_POST[txt_prazo_a]', `prazo_b` = '$_POST[txt_prazo_b]', `prazo_c` = '$_POST[txt_prazo_c]', `prazo_d` = '$_POST[txt_prazo_d]', `prazo_medio` = '$prazo_medio' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
        }else {//Quando está em Promoção não posso Salvar o "Tipo de Nota", nem o campo "Finalidade" ...
            $sql = "UPDATE `orcamentos_vendas` SET `id_transportadora` = $id_transportadora, `id_cliente_contato` = '$_POST[cmb_cliente_contato]', `tipo_frete` = '$_POST[cmb_tipo_frete]', `valor_frete_estimado` = '$_POST[txt_valor_frete_estimado]', `artigo_isencao` = '$_POST[chkt_artigo_isencao]', `desc_icms_sqd_auto` = '$desconto_icms_sgd', `cartao_bndes` = '$cartao_bndes', `prazo_a` = '$_POST[txt_prazo_a]', `prazo_b` = '$_POST[txt_prazo_b]', `prazo_c` = '$_POST[txt_prazo_c]', `prazo_d` = '$_POST[txt_prazo_d]', `prazo_medio` = '$prazo_medio' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
        }
    }else {//Não atende nenhum dos 3 critérios acima, então está totalmente manipulável ...
        //Orçamento Descongelado e possui Itens em Queima de Estoque, posso salvar a Transp. normalmente ...
        if($campos[0]['congelar'] == 'N' && $_POST['hdd_possui_queima_estoque'] == 'S') {
            $sql = "UPDATE `orcamentos_vendas` SET `id_transportadora` = $id_transportadora WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Verifico tem algum item do Orçamento em NF ...
        if(verificar_pedido($id_orcamento_venda) == 0) {//Não tem itens na NF ainda
            $sql = "UPDATE `orcamentos_vendas` SET `artigo_isencao` = '$_POST[chkt_artigo_isencao]' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
        }
    }
    bancos::sql($sql);
/********************************************************************************************************/
    //Busco todos os itens do $id_orcamento_venda passado por parâmetro p/ poder rodar algumas funções abaixo ...
    $sql = "SELECT id_orcamento_venda_item 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
    for($i = 0; $i < $linhas_itens; $i++) {
        //Verifico se o usuário fez mudanças no Tipo de Nota ou no Desconto ICMS/SGD Automático do Cabeçalho ...
        if(($finalidade != $_POST[cmb_finalidade]) || ($tipo_frete != $_POST[cmb_tipo_frete]) || ($nota_sgd != $_POST[cmb_tipo_nota]) || ($desconto_icms_sgd_gravado != $desconto_icms_sgd)) {
            vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item']);
        }
        //Se houve mudança no Tipo de Nota ou no Desconto ICMS/SGD Automático do Cabeçalho ou nos Prazos do Orçamento tenho que rodar essa função abaixo também ...
        if(($finalidade != $_POST[cmb_finalidade]) || ($tipo_frete != $_POST[cmb_tipo_frete]) || ($nota_sgd != $_POST[cmb_tipo_nota]) || ($desconto_icms_sgd_gravado != $desconto_icms_sgd) || ($prazo_medio_gravado != $prazo_medio)) {
            //Aqui eu atualizo a ML Est do Iem do Orçamento ...
            custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
/*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($_POST['id_orcamento_venda'], $campos_itens[$i]['id_orcamento_venda_item']);
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = '/erp/albafer/modulo/vendas/orcamentos/alterar_cabecalho.php?id_orcamento_venda=<?=$_POST['id_orcamento_venda'];?>&valor=1'
    </Script>
<?
}else {
/*****************************************************************************************/
/***********************************Pequenos Controles************************************/
/*****************************************************************************************/
    if(!empty($id_cliente_contato)) {//Exclusão de Contatos ...
        $sql = "UPDATE `clientes_contatos` SET `ativo` = '0' WHERE `id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
        bancos::sql($sql);
    }
    if(!empty($id_transportadora_excluir)) {//Exclusão de Transportadoras ...
        //Se a Transportadora for N/Carro ou Retira, então não pode ser excluido do Cliente
        if($id_transportadora_excluir != 795 && $id_transportadora_excluir != 796) {
            $sql = "DELETE FROM `clientes_vs_transportadoras` WHERE `id_cliente` = '$id_cliente' AND `id_transportadora` = '$id_transportadora_excluir' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
/*****************************************************************************************/
//Aqui traz os dados do orçamento
    $sql = "SELECT ov.*, c.`id_pais`, c.`id_cliente_tipo`, c.`forma_pagamento`, c.`tipo_faturamento`, c.`tipo_suframa`, c.`cod_suframa`, 
            c.`suframa_ativo`, c.`razaosocial`, c.`artigo_isencao`, c.`email_financeiro`, c.`data_atualizacao_emails`, c.`data_atualizacao_emails_contatos` 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            WHERE ov.`id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_cliente		= $campos[0]['id_cliente'];
    $id_pais            = $campos[0]['id_pais'];
    $id_cliente_contato	= $campos[0]['id_cliente_contato'];
    $id_transportadora  = $campos[0]['id_transportadora'];
    $nota_sgd           = $campos[0]['nota_sgd'];
    $data_emissao       = data::datetodata($campos[0]['data_emissao'], '/');
//Prazos
    $prazo_a = $campos[0]['prazo_a'];
    if($prazo_a == 0) {
        $prazo_a = '';
//Se vazio sugere a Data de Emissão
        $data_prazo_a   = $data_emissao;
    }else {
        $data_prazo_a   = data::adicionar_data_hora($data_emissao, $prazo_a);
    }
    $prazo_b = $campos[0]['prazo_b'];
    if($prazo_b == 0) {
        $prazo_b        = '';
        $data_prazo_b   = '';
    }else {
        $data_prazo_b   = data::adicionar_data_hora($data_emissao, $prazo_b);
    }
    $prazo_c = $campos[0]['prazo_c'];
    if($prazo_c == 0) {
        $prazo_c        = '';
        $data_prazo_c   = '';
    }else {
        $data_prazo_c = data::adicionar_data_hora($data_emissao, $prazo_c);
    }
    $prazo_d = $campos[0]['prazo_d'];
    if($prazo_d == 0) {
        $prazo_d        = '';
        $data_prazo_d   = '';
    }else {
        $data_prazo_d   = data::adicionar_data_hora($data_emissao, $prazo_d);
    }
/*Verifico se o Orçamento possui algum item que esteja em Pedido, se sim não posso mais alterar 
o Tipo de Nota do Cabeçalho ...*/
    $sql = "SELECT `id_orcamento_venda_item` 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$id_orcamento_venda' 
            AND `status` > '0' LIMIT 1 ";
    $campos_itens_importados    = bancos::sql($sql);
    $qtde_itens_importados      = count($campos_itens_importados);

    //Variável q retorna se tem algum item do Orçamento em Pedido
    $verificar_pedido = verificar_pedido($id_orcamento_venda);

    //Se o Orçamento estiver congelado ou existir algum Item que está em Queima de Estoque, travo o Cabeçalho ...
    $vetor_dados_gerais     = vendas::dados_gerais_orcamento($id_orcamento_venda);
    $data_validade_orc      = $vetor_dados_gerais['data_validade_orc'];
    $dias_validade          = $vetor_dados_gerais['dias_validade'];
    $possui_queima_estoque  = $vetor_dados_gerais['possui_queima_estoque'];
    $possui_promocao        = $vetor_dados_gerais['possui_promocao'];
    
    if(strtoupper($campos[0]['congelar'] == 'S') || $possui_queima_estoque == 'S') {
        $class                  = 'caixadetexto2';
        $disabled               = 'disabled';
        $disabled_desc_icms_sgd = 'disabled';
    }else {
        $class                  = 'caixadetexto';
        $disabled               = '';
        $disabled_desc_icms_sgd = '';
    }

    if($campos[0]['congelar'] == 'S') {//Se o Orçamento estiver congelado ...
        $onload                 = 'verificar(5)';
        $checked_congelar 	= 'checked';
    }else {
        $onload = 'habilitar_tipo_venda();verificar(5);verificar_tipo_cliente()';
        $checked_congelar 	= '';
    }
    
//Aqui eu busco o "Valor Total dos Produtos" p/ verificar qual o Prazo Médio ideal p/ se dar na negociação ...
    $calculo_total_impostos = calculos::calculo_impostos(0, $id_orcamento_venda, 'OV');
?>
<html>
<head>
<title>.:: Alterar Orçamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/prazo_medio.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    return validando_campos('SALVAR')
}

function validando_campos(caminho) {
/******************************E-mail Financeiro******************************/
//Controle com o E-mail Financeiro ...
    var email_financeiro = '<?=$campos[0]['email_financeiro']?>'
    if(email_financeiro == '') {
        alert('DIGITE O E-MAIL FINANCEIRO !!!\n\nSEM ESSE CAMPO PREENCHIDO NAO E POSSIVEL GERAR A(S) DUPLICATA(S) !')
        return false
    }
/*****************************************************************************/
//Validações referentes a Tipo de Frete ...
    if(typeof(document.form.cmb_tipo_frete) == 'object') {
        //Tipo de Frete ...
        if(!combo('form', 'cmb_tipo_frete', '', 'SELECIONE UM TIPO DE FRETE !')) {
            return false
        }
//Se o Frete for CIF = (por nossa Conta), forço o preenchimento de alguns campos abaixo ...
        if(document.form.cmb_tipo_frete.value == 'C') {
//Transportadora do Cliente ...
            if(!combo('form', 'cmb_cliente_transportadora', '', 'SELECIONE A TRANSPORTADORA DO CLIENTE !')) {
                return false
            }
/*Se a Transportadora escolhida pelo Vendedor for Diferente de "N/CARRO" 795 e Diferente de "N/CARRO (VENDEDOR)" 
1098, aí sim é obrigado a ser preenchido um Valor de Frete Estimado ...*/
            if(document.form.cmb_cliente_transportadora.value != 795 && document.form.cmb_cliente_transportadora.value != 1098) {
//Valor de Frete Estimado ...
                if(!texto('form', 'txt_valor_frete_estimado', '1', '0123456789,.', 'VALOR DE FRETE ESTIMADO', '2')) {
                    return false
                }
                var valor_frete_estimado = (document.form.txt_valor_frete_estimado.value != '') ? eval(strtofloat(document.form.txt_valor_frete_estimado.value)) : 0
                if(valor_frete_estimado == 0) {
                    alert('VALOR DE FRETE ESTIMADO INVÁLIDO !!! \n VALOR DE FRETE ESTIMADO = ZERO !')
                    document.form.txt_valor_frete_estimado.focus()
                    document.form.txt_valor_frete_estimado.select()
                    return false
                }
            }
        }
    }
//Contato do Cliente
    if(typeof(document.form.cmb_cliente_contato) == 'object') {
        if(document.form.cmb_cliente_contato.value == '') {
            alert('SELECIONE O CONTATO DO CLIENTE !')
            document.form.cmb_cliente_contato.focus()
            return false
        }
    }
<?
    if($qtde_itens_importados == 0) {//Não existem itens Importados em Pedido ...
?>
//Forca o preenchimento caso o Tipo de Nota for NF
        if(typeof(document.form.cmb_tipo_nota) == 'object') {
            if(document.form.cmb_tipo_nota.value == 'N') {
                if(document.form.cmb_finalidade.value == '') {
                    alert('SELECIONE O TIPO DE VENDA !')
                    document.form.cmb_finalidade.focus()
                    return false
                }
            }
        }
<?
    }
?>
//Prazo A
    if(typeof(document.form.txt_prazo_a) == 'object' && document.form.txt_prazo_a.disabled == false) {
        if(document.form.txt_prazo_a.value != '') {
            if(!texto('form', 'txt_prazo_a', '1', '0123456789', 'PRAZO A', '2')) {
                return false
            }
        }
    }
//Prazo B
    if(typeof(document.form.txt_prazo_b) == 'object' && document.form.txt_prazo_b.disabled == false) {
        if(document.form.txt_prazo_b.value != '') {
            if(!texto('form', 'txt_prazo_b', '1', '0123456789', 'PRAZO B', '2')) {
                return false
            }
        }
    }
//Prazo C
    if(typeof(document.form.txt_prazo_c) == 'object' && document.form.txt_prazo_c.disabled == false) {
        if(document.form.txt_prazo_c.value != '') {
            if(!texto('form', 'txt_prazo_c', '1', '0123456789', 'PRAZO C', '2')) {
                return false
            }
        }
    }
//Prazo D
    if(typeof(document.form.txt_prazo_d) == 'object' && document.form.txt_prazo_d.disabled == false) {
        if(document.form.txt_prazo_d.value != '') {
            if(!texto('form', 'txt_prazo_d', '1', '0123456789', 'PRAZO D', '2')) {
                return false
            }
        }
    }
/****************Comparação dos Prazos**********************/
    var prazo_a = (document.form.txt_prazo_a.value != '' && document.form.txt_prazo_a.value != 'À vista') ? eval(document.form.txt_prazo_a.value) : 0
    var prazo_b = (document.form.txt_prazo_b.value != '') ? eval(document.form.txt_prazo_b.value) : 0
    var prazo_c = (document.form.txt_prazo_c.value != '') ? eval(document.form.txt_prazo_c.value) : 0
    var prazo_d = (document.form.txt_prazo_d.value != '') ? eval(document.form.txt_prazo_d.value) : 0

//Comparando o Prazo D
    if(document.form.txt_prazo_d.value != '') {
        if(prazo_d <= prazo_c || prazo_c == 0) {
            alert('PRAZO D INVÁLIDO !!! \n PRAZO D MENOR OU IGUAL AO PRAZO C !')
            document.form.txt_prazo_d.focus()
            document.form.txt_prazo_d.select()
            return false
        }
    }
//Comparando o Prazo C
    if(document.form.txt_prazo_c.value != '') {
        if(prazo_c <= prazo_b || prazo_b == 0) {
            alert('PRAZO C INVÁLIDO !!! \n PRAZO C MENOR OU IGUAL AO PRAZO B !')
            document.form.txt_prazo_c.focus()
            document.form.txt_prazo_c.select()
            return false
        }
    }
//Comparando o Prazo B
    if(document.form.txt_prazo_b.value != '') {
        if(prazo_b <= prazo_a) {
            alert('PRAZO B INVÁLIDO !!! \n PRAZO B MENOR OU IGUAL AO PRAZO A !')
            document.form.txt_prazo_b.focus()
            document.form.txt_prazo_b.select()
            return false
        }
    }   
/***********************************************************/
/******************Particularizações************************/
/***********************************************************/
    if(caminho == 'SALVAR') {
        var array_prazo_medio = prazo_medio('<?=$calculo_total_impostos['valor_total_produtos'];?>', '<?=$_SESSION['id_funcionario'];?>')
        if(array_prazo_medio['situacao_prazo'] != 1) return false//Significa que existe prazo Irregular nessa Negociação ...

        //Controle com Cartão BNDES ...
        if(document.form.chkt_cartao_bndes.checked == true) {
            //Tipo de Nota ...
            if(typeof(document.form.cmb_tipo_nota) == 'object' && document.form.cmb_tipo_nota.value == 'S') {
                alert('CARTÃO BNDES SOMENTE COM NOTA FISCAL !')
                document.form.chkt_cartao_bndes.checked = false
                return false
            }
            //Prazo Médio ...
            if(array_prazo_medio['prazo_medio'] != 30) {
                alert('PRAZO INVÁLIDO P/ CARTÃO BNDES !!!\n\nO PRAZO CORRETO P/ CARTÃO BNDES = "30" !')
                document.form.txt_prazo_a.focus()
                document.form.txt_prazo_a.select()
                document.form.chkt_cartao_bndes.checked = false
                return false
            }
        }
        document.form.passo.value                       = 1
        //Preparo este campo p/ poder gravar no Banco de Dados ...
        if(typeof(document.form.txt_valor_frete_estimado) == 'object') limpeza_moeda('form', 'txt_valor_frete_estimado, ')

        /*Habilito esse checkbox p/ não dar erro quando o usuário inventar de Salvar os dados de 
        Cabeçalho do Orçamento ...*/
        document.form.chkt_desconto_icms_sgd.disabled   = false
        //Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
        document.form.nao_atualizar.value               = 1
        /*Desabilito esse botão para que o usuário não fique submetendo as informações de Cabeçalho 
        milhares de vezes ...*/
        document.form.cmd_salvar.disabled               = true
        document.form.cmd_salvar.className              = 'textdisabled'
    }else if(caminho == 'CONGELAR') {
        /*Nesse momento está descongelado esse ORC, mas significa que o Usuário está tentando 
        congelar ...*/
        if(document.form.chkt_congelar.checked == false) {
            if(typeof(document.form.chkt_artigo_isencao) == 'object') {//Quer dizer existe objeto ...
                if(document.form.chkt_artigo_isencao.checked == false) {
                    alert('VOCÊ NÃO MARCOU A SUSPENSÃO DE IPI !')
                }
            }
            //Aqui sim ..., na hora de Congelar o ORC verifico se os Prazos estão dentro do Padrão da Cartilha ...
            var array_prazo_medio = prazo_medio('<?=$calculo_total_impostos['valor_total_produtos'];?>', '<?=$_SESSION['id_funcionario'];?>')
            if(array_prazo_medio['situacao_prazo'] != 1) return false//Significa que existe prazo Irregular nessa Negociação ...
            
            var pergunta = confirm('DESEJA REALMENTE CONGELAR ESTE ORÇAMENTO ?')
            if(pergunta == false) return false
        }else {
            var pergunta = confirm('DESEJA REALMENTE DESCONGELAR ESTE ORÇAMENTO ?\nSEU ORÇAMENTO ESTÁ SUJEITO A TER ALTERAÇÕES NOS VALORES !')
            if(pergunta == false) return false
        }
        /*Como foram satisfeitas todas as regras de validação, então agora o checkbox passa a assumir 
        o valor de princípio selecionado pelo usuário ...*/
        if(document.form.chkt_congelar.checked == true) {//Fica deschecado
            document.form.chkt_congelar.checked = false
        }else {//Fica checado
            document.form.chkt_congelar.checked = true
        }
        document.getElementById('chkt_congelar').style.display = 'none'//Desaparece o Checkbox, p/ o user não clicar 1000 vezes ...
        document.getElementById('lbl_mensagem').innerHTML = '<img src="../../../css/little_loading.gif"> <font size="2" color="brown"><b>LOADING ...</b></font>'
        /************************************************************************/
        //Habilito esse checkbox p/ não dar erro quando o usuário inventar de Congelar / Descongelar o Orçamento ...
        document.form.chkt_desconto_icms_sgd.disabled   = false
        //Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
        document.form.nao_atualizar.value               = 1
        document.form.action                            = 'congelar_descongelar.php'
        document.form.submit()
    }
}

function calcular_frete() {
    if(document.form.cmb_modo_envio.value == 'CORREIO') {
        nova_janela('../../classes/cliente/calcular_frete_correio.php?id_orcamento_venda=<?=$id_orcamento_venda;?>', 'CALCULAR_FRETE', '', '', '', '', '150', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {
        nova_janela('../../classes/cliente/calcular_frete_tam.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&valor_total_produtos=<?=$calculo_total_impostos['valor_total_produtos'];?>', 'CALCULAR_FRETE', '', '', '', '', '250', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function alterar_contato() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }else {
        nova_janela('../../classes/cliente/alterar_contatos.php?id_cliente_contato='+document.form.cmb_cliente_contato.value, 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function excluir_contato() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            document.form.passo.value = 0
            document.form.id_cliente_contato.value = document.form.cmb_cliente_contato.value
//Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
            document.form.nao_atualizar.value = 1
            document.form.submit()
        }
    }
}

function excluir_transportadora() {
    if(document.form.cmb_cliente_transportadora.value == '') {
        alert('SELECIONE A TRANSPORTADORA DO CLIENTE !')
        document.form.cmb_cliente_transportadora.focus()
        return false
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            document.form.passo.value = 0
            document.form.txt_data_emissao.disabled = false
            document.form.id_transportadora_excluir.value = document.form.cmb_cliente_transportadora.value
//Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
            document.form.nao_atualizar.value = 1
            document.form.submit()
        }
    }
}

function atualizar() {
    document.form.passo.value               = 0
    document.form.id_cliente_contato.value  = ''
//Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value       = 1
    document.form.submit()
}

function verificar(valor) {
    if(valor == 1) {//Prazo A
        if(document.form.txt_prazo_a.value == '') {
            document.form.txt_data_prazo_a.value = '<?=$data_emissao;?>'
        }else {
            nova_data('document.form.txt_data_emissao', 'document.form.txt_data_prazo_a', 'document.form.txt_prazo_a')
        }
    }else if(valor == 2) {//Prazo B
        if(document.form.txt_prazo_b.value == '') {
            document.form.txt_data_prazo_b.value = ''
        }else {
            nova_data('document.form.txt_data_emissao', 'document.form.txt_data_prazo_b', 'document.form.txt_prazo_b')
        }
    }else if(valor == 3) {//Prazo C
        if(document.form.txt_prazo_c.value == '') {
            document.form.txt_data_prazo_c.value = ''
        }else {
            nova_data('document.form.txt_data_emissao', 'document.form.txt_data_prazo_c', 'document.form.txt_prazo_c')
        }
    }else if(valor == 4) {//Prazo D
        if(document.form.txt_prazo_d.value == '') {
            document.form.txt_data_prazo_d.value = ''
        }else {
            nova_data('document.form.txt_data_emissao', 'document.form.txt_data_prazo_d', 'document.form.txt_prazo_d')
        }
    }else {//Se estiver preenchida de forma correta a Data de Emissão, então ...
        if(document.form.txt_data_emissao.value.length == 10) {
            nova_data('document.form.txt_data_emissao', 'document.form.txt_data_validade_orcamento', <?=(int)$dias_validade;?>)
        }else {
            document.form.txt_data_validade_orcamento.value = ''
        }
    }
}

function controlar_checkbox() {
    if(document.getElementById('chkt_congelar').style.display == '') {//Se o Checkbox do Congelar estiver visível ...
        if(document.form.chkt_congelar.checked == true) {//Fica deschecado
            document.form.chkt_congelar.checked = false
        }else {//Fica checado
            document.form.chkt_congelar.checked = true
        }
        return true
    }else {//Caso esteja Oculto, nem faz nada ...
        return false
    }
}

function controlar_checkbox2() {
    if(document.form.chkt_artigo_isencao.checked == true) {//Fica deschecado
        document.form.chkt_artigo_isencao.checked = false
    }else {//Fica checado
        document.form.chkt_artigo_isencao.checked = true
    }
}

function artigo_isencao_verificar() {
//Verifica se está importado em Nota Fiscal
    var verificar_pedido = eval('<?=$verificar_pedido;?>')
    if(verificar_pedido == 1) {//Já está importado em NF, não consigo manipular o Congelar Orçamento
/*Função q verifica a situação atual do checkbox e não permite trocar o valor do checkbox até q
todas as regras de validação estejam satisfatórias*/
        controlar_checkbox2()
        alert('NÃO É POSSÍVEL ALTERAR ESSA OPÇÃO !\nEXISTEM ITEM(NS) IMPORTADO(S) EM NOTA FISCAL !')
        return false
    }
}

function enviar() {
/*Função q verifica a situação atual do checkbox e não permite trocar o valor do checkbox até q
todas as regras de validação estejam satisfatórias*/
    if(controlar_checkbox() == false) return false//Nem avança p/ o resto do código abaixo ...
//Verifica se está importado em Pedido ...
    var verificar_pedido = eval('<?=$verificar_pedido;?>')
    if(verificar_pedido == 1) {//Já está importado em Pedido, não consigo manipular o Congelar Orçamento
        alert('NÃO FOI POSSÍVEL DESCONGELAR SEU ORÇAMENTO !\nEXISTEM ITEM(NS) IMPORTADO(S) EM PEDIDO !')
        return false
    }else {//Não itens em Pedido ainda
        return validando_campos('CONGELAR')
    }
}

function desabilitar() {
    if(document.form.opt_opcao[4].checked == true) {
        document.form.cmb_tipo_cliente.disabled = false
        document.form.cmb_tipo_cliente.value    = ''
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.cmb_tipo_cliente.disabled = true
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function habilitar_tipo_venda() {
    var finalidade = '<?=$campos[0]['finalidade'];?>'
    if(typeof(document.form.cmb_tipo_nota) == 'object') {
        if(document.form.cmb_tipo_nota.value == 'N') {
            document.form.cmb_finalidade.disabled   = false
            document.form.cmb_finalidade.value      = finalidade
            document.form.cmb_finalidade.focus()
        }else {
            document.form.cmb_finalidade.value = ''
            document.form.cmb_finalidade.disabled = true
        }
    }
}

function verificar_tipo_cliente() {
    var finalidade      = '<?=$campos[0]['finalidade'];?>'
    var id_cliente_tipo = '<?=$campos[0]['id_cliente_tipo'];?>'
    /*Se o Cliente for "Indústria" e o Orçamento negociado como Revenda, então o Sistema dá um aviso p/ lembrar o
    vendedor de que esse Orc é "CONSUMO"*/
    if(finalidade == 'R' && id_cliente_tipo == 4) {//Negociação está Irregular ...
        alert('VERIFIQUE COM O CLIENTE SE É CONSUMO OU REVENDA, E ACERTO O TIPO DE NOTA NO CABEÇALHO !')
        document.form.cmb_finalidade.focus()
    }else {//Negociação está Correta ...
        document.form.txt_prazo_a.focus()
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.carregar_tela_itens()
}
</Script>
</head>
<body onload='<?=$onload;?>' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--************************Controles de Tela************************-->
<input type='hidden' name='passo' onclick='atualizar()'>
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<input type='hidden' name='id_cliente_contato'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='id_transportadora_atrelar'>
<input type='hidden' name='id_transportadora_excluir'>
<input type='hidden' name='hdd_possui_queima_estoque' value='<?=$possui_queima_estoque;?>'>
<input type='hidden' name='hdd_possui_promocao' value='<?=$possui_promocao;?>'>
<!--Caixa que faz o controle de contatos inclusos deste Cliente nesse Orcamento-->
<input type='hidden' name='controle' onclick="verificar(1);verificar(2);verificar(3);verificar(4);verificar(5)">
<input type='hidden' name='nao_atualizar'>
<!--*****************************************************************-->
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Orçamento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente:
        </td>
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkred'>
                <b>Tipo de Faturamento:</b>
            </font>
        </td>
        <td>
            <font color='red'><b>
            <?
                if($campos[0]['tipo_faturamento'] == 1) {
                    echo 'TUDO PELA ALBAFER';
                }else if($campos[0]['tipo_faturamento'] == 2) {
                    echo 'TUDO PELA TOOL MASTER';
                }else if($campos[0]['tipo_faturamento'] == 'Q') {
                    echo 'QUALQUER EMPRESA';
                }else if($campos[0]['tipo_faturamento'] == 'S') {
                    echo 'SEPARADAMENTE';
                }
            ?>
            </b></font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Transportadora:
        </td>
        <td>
        <?
            //Se o Orçamento estiver congelado, travo o Cabeçalho ...
            if(strtoupper($campos[0]['congelar'] == 'S')) {
                $sql = "SELECT `nome` 
                        FROM `transportadoras` 
                        WHERE `id_transportadora` = '$id_transportadora' LIMIT 1 ";
                $campos_transportadora = bancos::sql($sql);
                echo $campos_transportadora[0]['nome'];
            }else {
        ?>
                <select name='cmb_cliente_transportadora' title='Selecione a Transportadora do Cliente' class='combo'>
                <?
                    $sql = "SELECT t.`id_transportadora`, IF(t.`nome_fantasia` = '', t.`nome`, t.`nome_fantasia`) AS transportadora 
                            FROM `clientes_vs_transportadoras` ct 
                            INNER JOIN `transportadoras` t ON t.`id_transportadora` = ct.`id_transportadora` AND t.`ativo` = '1' 
                            WHERE ct.`id_cliente` = '$id_cliente' ORDER BY t.`nome` ";
    //Significa que o usuário atrelou uma transportadora no Pop-UP de Transportadoras
                    if(!empty($id_transportadora_atrelar)) {
                        echo combos::combo($sql, $id_transportadora_atrelar);
                    }else {//Aqui carrega a transportadora já escolhida em Pedido
    //Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                        if(!empty($cmb_cliente_transportadora)) {
                            echo combos::combo($sql, $cmb_cliente_transportadora);
    //Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                        }else {//Aqui carrega a transportadora já escolhida em Pedido
                            echo combos::combo($sql, $id_transportadora);
                        }
                    }
                ?>
                </select>
                &nbsp;&nbsp;
                <img src = '../../../imagem/menu/incluir.png' border='0' title='Atrelar Transportadora' alt='Atrelar Transportadora' onclick="nova_janela('../../classes/cliente/atrelar_transportadoras.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')">
                &nbsp;&nbsp;
                <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Transportadora' alt='Excluir Transportadora' onclick='excluir_transportadora()'>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Frete:</b>
        </td>
        <td>
        <?
            //Se o Orçamento estiver congelado ou existir algum Item que está em Queima de Estoque, travo o Cabeçalho ...
            if(strtoupper($campos[0]['congelar'] == 'S') || $possui_queima_estoque == 'S') {
                if($campos[0]['tipo_frete'] == 'F') {
                    echo 'FOB (POR CONTA DO CLIENTE)';
                }else if($campos[0]['tipo_frete'] == 'C') {
                    echo 'CIF (POR NOSSA CONTA)';
                }
            }else {//Não está Congelado
        ?>
                <select name='cmb_tipo_frete' title='Selecione o Tipo de Frete' class='<?=$class;?>'>
        <?
                    if($campos[0]['tipo_frete'] == 'F') {//FOB ...
                        $selectedfob = 'selected';
                    }else if($campos[0]['tipo_frete'] == 'C') {//CIF ...
                        $selectedcif = 'selected';
                    }
        ?>
                    <option value='' style='color:red'>SELECIONE</option>
                    <option value='F' <?=$selectedfob;?>>FOB (POR CONTA DO COMPRADOR)</option>
                    <option value='C' <?=$selectedcif;?>>CIF (POR NOSSA CONTA)</option>
                </select>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor de Frete Estimado:
        </td>
        <td>
            <input type='text' name='txt_valor_frete_estimado' value='<?=number_format($campos[0]['valor_frete_estimado'], 2, ',', '.');?>' title='Digite o Valor Frete Estimado' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='11' maxlength='9' class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;
            -
            &nbsp;
            <select name='cmb_modo_envio' title='Modo de Envio' class='combo'>
                <option value='CORREIO'>CORREIO</option>
                <option value='TAM'>TAM</option>
            </select>
            &nbsp;
            <input type='button' name='cmd_calcular_frete' value='Calcular Frete' title='Calcular Frete' onclick='calcular_frete()' class='botao'>
            &nbsp;
            <a href="javascript:nova_janela('http://www2.correios.com.br/sistemas/precosPrazos/', 'CORREIOS', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Consultar Sedex (Correios)' class='link'>
                Consultar Sedex (Correios)
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Contato(s) do Cliente:</b>
        <td>
        <?
            //Se o Orçamento estiver congelado ou existir algum Item que está em Queima de Estoque, travo o Cabeçalho ...
            if(strtoupper($campos[0]['congelar'] == 'S') || $possui_queima_estoque == 'S') {
                $sql = "SELECT nome 
                        FROM `clientes_contatos` 
                        WHERE `id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
                $campos_contato = bancos::sql($sql);
                echo $campos_contato[0]['nome'];
            }else {
        ?>
                <select name='cmb_cliente_contato' title='Selecione os Contatos do Cliente' class='combo'>
                <?
/*Significa que foi incluido algum contato no Pop-Up de contatos, sendo assim, o sistema sugere esse contato na combo
assim que acaba de ser incluso*/
                    if($controle == 1) {
                        //Aqui eu pego o último contato que acabou de ser Incluso ou Alterado ...
                        $sql = "SELECT `id_cliente_contato` 
                                FROM `clientes_contatos` 
                                WHERE `id_cliente` = '$id_cliente' 
                                AND `ativo` = '1' ORDER BY `id_cliente_contato` DESC LIMIT 1 ";
                        $campos_contato = bancos::sql($sql);
                        $id_cliente_contato = $campos_contato[0]['id_cliente_contato'];//Será listado na combo ...
                    }
                    //Select normal da Combo ...
                    $sql = "SELECT `id_cliente_contato`, `nome` 
                            FROM `clientes_contatos` 
                            WHERE `id_cliente` = '$id_cliente' 
                            AND `ativo` = '1' ORDER BY `nome` ";
                    echo combos::combo($sql, $id_cliente_contato);
                ?>
                </select>
                &nbsp;&nbsp; <img src = '../../../imagem/menu/incluir.png' border='0' title='Incluir Contato' alt='Incluir Contato' onclick="nova_janela('../../classes/cliente/incluir_contatos.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')">
                &nbsp;&nbsp; <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar Contato' alt='Alterar Contato' onclick='alterar_contato()'>
                &nbsp;&nbsp; <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Contato' alt='Excluir Contato' onclick='excluir_contato()'>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>E-mail Financeiro:</b>
        </td>
        <td>
        <?
            if(empty($campos[0]['email_financeiro'])) {
                echo '<font color="darkblue"><b>S/ EMAIL FINANCEIRO CADASTRADO</b></font>';
            }else {
                echo $campos[0]['email_financeiro'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Nota:</b>
        </td>
        <td>
<?
            if($qtde_itens_importados == 0) {//Não existem itens Importados em Pedido ...
                /*Se o Orçamento estiver congelado, existir algum Item que está em Queima de Estoque 
                ou tivermos algum item que estiver em Promoção então travo o Cabeçalho ...*/
                if(strtoupper($campos[0]['congelar'] == 'S') || $possui_queima_estoque == 'S' || $possui_promocao == 'S') {
                    if($nota_sgd == 'S') {
                        echo 'SGD';
                    }else {
                        echo 'NF&nbsp;-&nbsp;';
                        if($campos[0]['finalidade'] == 'C') {
                            echo 'Consumo';
                        }else if($campos[0]['finalidade'] == 'I') {
                            echo 'Industrialização';
                        }else {
                            echo 'Revenda';
                        }
                    }
                }else {//Não atende nenhum dos 3 critérios acima, então está totalmente manipulável ...
?>
                <select name='cmb_tipo_nota' title='Selecione o Tipo de Nota' onchange='habilitar_tipo_venda()' class='<?=$class;?>'>
<?
                    if($nota_sgd == 'S') {$selectedsgd = 'selected';}else if($nota_sgd == 'N') {$selectednf = 'selected';}
?>
                    <option value='S' <?=$selectedsgd;?>>SGD</option>
                    <option value='N' <?=$selectednf;?>>NF</option>
                </select>
                &nbsp;
                <select name='cmb_finalidade' title='Selecione a Finalidade' class='combo' <?=$disabled;?>>
                    <option value='' style='color:red'>SELECIONE</option>
<?
                        if($campos[0]['finalidade'] == 'C') {
                            $selected_consumo           = 'selected';
                        }else if($campos[0]['finalidade'] == 'I') {
                            $selected_industrializacao  = 'selected';
                        }else if($campos[0]['finalidade'] == 'R') {
                            $selected_revenda           = 'selected';
                        }
?>
                    <option value='C' <?=$selected_consumo;?>>CONSUMO</option>
                    <option value='I' <?=$selected_industrializacao;?>>INDUSTRIALIZAÇÃO</option>
                    <option value='R' <?=$selected_revenda;?>>REVENDA</option>
                </select>
<?
                }
            }else {//Tem pelo menos 1 item já Importado em Pedido ...
                if($nota_sgd == 'S') {echo 'SGD';}else {echo 'NF';} echo '&nbsp;-&nbsp;';
                if($campos[0]['finalidade'] == 'C') {
                    echo 'Consumo';
                }else if($campos[0]['finalidade'] == 'I') {
                    echo 'Industrialização';
                }else {
                    echo 'Revenda';
                }
            }
//Significa que o Cliente é do Tipo Internacional
            if($id_pais != 31) echo 'Exportação';
?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_congelar' id='chkt_congelar' onclick='enviar()' class='checkbox' <?=$checked_congelar;?>>
            <label for='chkt_congelar' id='lbl_mensagem'>Congelar Orçamento</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
        <?
//Significa que foi incluido algum contato no Pop-Up de contatos
            if($controle == 1) {
                if(!empty($_POST['chkt_desconto_icms_sgd'])) $checked = 'checked';
//Aqui é quando carrega a Tela de Primeira
            }else {
                if(!empty($campos[0]['desc_icms_sqd_auto'])) $checked = 'checked';
            }
            /*Sempre que o país for Estrangeiro, deixamos desabilitado esse campo p/ que o usuário não desmarque o mesmo, é
            interessante darmos esse Desconto porque nós não pagamos esse Impostos de ICMS na Exportação ...*/
            if($id_pais != 31) $disabled_desc_icms_sgd = 'disabled';
        ?>
            <input type='checkbox' name='chkt_desconto_icms_sgd' value='1' id='desconto_icms_sgd' class='checkbox' <?=$checked;?> <?=$disabled_desc_icms_sgd;?>>
            <label for='desconto_icms_sgd'>Desconto ICMS/SGD Automático</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
        <?
//Significa que foi incluido algum contato no Pop-Up de contatos
            if($controle == 1) {
                if(!empty($_POST['chkt_cartao_bndes'])) $checked_cartao_bndes = 'checked';
//Aqui é quando carrega a Tela de Primeira
            }else {
                if($campos[0]['cartao_bndes'] == 'S') $checked_cartao_bndes = 'checked';
            }
        ?>
            <input type='checkbox' name='chkt_cartao_bndes' value='S' id='cartao_bndes' class='checkbox' <?=$checked_cartao_bndes;?>>
            <label for='cartao_bndes'>Cartão BNDES</label>
        </td>
    </tr>
<?	
//Se o no cadastro de cliente a Opção de Artigo Isenção estiver marcada, então aki no Orc ele exibe essa opção também
	if($campos[0]['artigo_isencao'] == 1) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            if($controle == 1) {//Significa que foi incluido algum contato no Pop-Up de contatos ...
                if(!empty($chkt_artigo_isencao)) $checked = 'checked';
            }else {//Aqui é quando carrega a Tela de Primeira ...
                $checked = (!empty($campos[0]['artigo_isencao'])) ? 'checked' : '';
            }
        ?>
            <input type='checkbox' name='chkt_artigo_isencao' value='1' onclick="artigo_isencao_verificar()" id='artigo_isencao' class='checkbox' <?=$checked;?>>
            <label for='artigo_isencao'><b>SUSPENSO IPI, CONF.ART.29, PARÁGRAFO 1, ALÍNEA A E B, LEI 10637/02.</b></label>
        </td>
    </tr>
<?
        }
/*************************************************************************************/
/***************************************Suframa***************************************/
/*************************************************************************************/
/*Esse campo $conceder_pis_cofins só serve simplesmente como histórico, pra saber se o Orçamento foi negociado 
com base de Suframa caso o Cliente passe a ter o Suframa como sendo Inativo ...*/
	if($campos[0]['conceder_pis_cofins'] == 'S') {
?>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                <b>TIPO / CÓDIGO SUFRAMA: </b>
            </font>
        </td>
        <td>
<?
            $tipo_suframa_vetor[1] = 'Área de Livre Comércio (ICMS/IPI) / ';
            $tipo_suframa_vetor[2] = 'Zona Franca de Manaus (ICMS/PIS/COFINS/IPI) / ';
            $tipo_suframa_vetor[3] = 'Amazônia Ocidental (IPI) / ';

            echo '<font color="blue">'.$tipo_suframa_vetor[$campos[0]['tipo_suframa']].$campos[0]['cod_suframa'].'</font>';
            
//Se o Suframa for Ativo, então exibo essa Mensagem de Ativo ao lado ...
            if($campos[0]['suframa_ativo'] == 'S') echo ' <font color="red"><b>(ATIVO)</b></font>';

            if($campos[0]['tipo_suframa'] == 1 && $campos[0]['suframa_ativo'] == 'S') {//Área de Livre e o Cliente possui o Suframa Ativo ...
?>
                <br>Desconto de ICMS = <?=number_format(genericas::variavel(40), 2, ',', '.');?> % <font color='red'>(A ser concedido na Emissão da NF)</font>
<?
            }else if($campos[0]['tipo_suframa'] == 2 && $campos[0]['suframa_ativo'] == 'S') {//Zona Franca de Man...
?>
                <br>Desconto de PIS + Cofins = <?=number_format((genericas::variavel(20)+genericas::variavel(21)), 2, ',', '.');?> % e ICMS = <?=number_format(genericas::variavel(40), 2, ',', '.');?> % <font color='red'>(A ser concedido na Emissão da NF)</font>
<?
            }
?>
        </td>
    </tr>
<?
	}
/*************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emissão:</b>
        </td>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=$data_emissao;?>' title='Data de Emissão' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto2' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Forma de Pagamento:</b>
        </td>
        <td>
            <?
                $vetor_forma_pagamento  = array_sistema::forma_pagamento();

                foreach($vetor_forma_pagamento as $indice => $rotulo) {
                    if(!empty($campos[0]['forma_pagamento']) && $campos[0]['forma_pagamento'] == $indice) {
                        echo $rotulo;
                        break;
                    }
                }
            ?>
            </select>
        </td>
    </tr>
<?
	//Se o Orçamento estiver congelado ou existir algum Item que está em Queima de Estoque, travo o Cabeçalho ...
	if(strtoupper($campos[0]['congelar'] == 'S') || $possui_queima_estoque == 'S') {
            if($prazo_a == '' && $prazo_b == '' && $prazo_c == '' && $prazo_d == '') $prazo_a = 'À vista';
	}
?>
    <tr class='linhanormal'>
        <td>
            Prazo A:
        </td>
        <td>
            <input type='text' name='txt_prazo_a' value='<?=$prazo_a;?>' title='Digite o Prazo A' size='7' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(1)" class='<?=$class;?>' <?=$disabled;?>> DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_prazo_a' value='<?=$data_prazo_a;?>' title='Data do Prazo A' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Prazo B:
        </td>
        <td>
            <input type='text' name='txt_prazo_b' value='<?=$prazo_b;?>' title='Digite o Prazo B' size='7' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(2)" class='<?=$class;?>' <?=$disabled;?>> DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_prazo_b' value='<?=$data_prazo_b;?>' title='Data do Prazo B' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Prazo C:
        </td>
        <td>
            <input type='text' name='txt_prazo_c' value='<?=$prazo_c;?>' title='Digite o Prazo C' size='7' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(3)" class='<?=$class;?>' <?=$disabled;?>> DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_prazo_c' value='<?=$data_prazo_c;?>' title='Data do Prazo C' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Prazo D:
        </td>
        <td>
            <input type='text' name='txt_prazo_d' value='<?=$prazo_d;?>' title='Digite o Prazo D' size='7' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(4)" class='<?=$class;?>' <?=$disabled;?>> DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_prazo_d' value='<?=$data_prazo_d;?>' title='Data do Prazo D' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Prazo Médio:
        </td>
        <td>
            <?=$campos[0]['prazo_medio'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Validade:</b>
        </td>
        <td>
            <font color='red' size='1'>
                <?='<b>'.(int)$dias_validade.' DIAS</b>';?>
            </font>
            - &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='text' name="txt_data_validade_orcamento" title="Data de Validade do Orçamento" size='12' maxlength='10' class='textdisabled' disabled>
            <?
                //Se a Data de Validade do Orc for maior ou igual a Data Atual ainda é possível gerarmos Pedido ...
                if($data_validade_orc < date('Y-m-d')) echo '<font color="red"><b> (ORÇAMENTO FORA DA DATA DE VALIDADE).</b></font>';
            ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');verificar(5)" class='botao'>
            <?
                //Verifico quantos dias passaram da data de atualização do E-mail até a data atual ...
                $vetor_valores                              = data::diferenca_data($campos[0]['data_atualizacao_emails'], date('Y-m-d'));
                $qtde_dias_sem_atualizar_emails             = $vetor_valores[0];

                $vetor_valores                              = data::diferenca_data($campos[0]['data_atualizacao_emails_contatos'], date('Y-m-d'));
                $qtde_dias_sem_atualizar_emails_contatos    = $vetor_valores[0];
                
                if($qtde_dias_sem_atualizar_emails > 90 || $qtde_dias_sem_atualizar_emails_contatos > 90) {
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                }else {
                    $class      = 'botao';
                    $disabled   = '';
                }
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <?
        if($possui_queima_estoque == 'S') {
    ?>
    <tr class='erro' align='center'>
        <td colspan='2'>
            <?='<br><b>EXISTE(M) ITEM(NS) EM EXCESSO DE ESTOQUE !!!<br>SÓ É POSSÍVEL ALTERAR O CABEÇALHO SE DESMARCAR ESSE ITENS !</b>';?>
        </td>
    </tr>
    <?
        }
        
        if($qtde_dias_sem_atualizar_emails > 90 || $qtde_dias_sem_atualizar_emails_contatos > 90) {
    ?>
    <Script Language = 'JavaScript'>
        alert('EXISTE(M) E-MAIL(S) DE CLIENTE / CONTATO(S) DE CLIENTE(S) COM MAIS DE 90 DIAS SEM ATUALIZAÇÃO !!!\n\nFAÇA A ATUALIZAÇÃO DESTE(S) !')
        nova_janela('atualizar_emails.php?id_cliente=<?=$id_cliente;?>', 'ATUALIZAR_EMAILS', 'F')
    </Script>
    <?
        }
    ?>
</table>
</form>
</body>
</html>
<?}?>