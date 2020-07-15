<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');//Essa biblioteca È utilizada dentro da Biblioteca de Faturamentos ...
require('../../../../../lib/data.php');
require('../../../../../lib/estoque_acabado.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');

if($_SESSION['id_modulo'] == 18) {//MÛdulo de Vendas ...
    segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/mandar_vale/consultar_cliente.php', '../../../../../');
}else {//MÛdulo de Vendas ...
    segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');
}

if($passo == 1) {
    /********************************************************************************************/
    /*******************************************CrÈdito******************************************/
    /********************************************************************************************/
    //Verifico se o Cliente est· OK no que se refere ao seu CrÈdito ...
    $retorno_analise_credito = faturamentos::analise_credito_cliente($id_cliente);//Analisa p/ V c ele tem dÈbito e pode comprar devido seu limite ou crÈdito

    /*Significa que o Cliente n„o est· OK no que se refere a sua situaÁ„o Financeira e sendo 
    assim eu n„o posso mandar no Vale os PA(s) ...*/
    if($retorno_analise_credito['historico_cliente'] != 'OK') {
/*Busco o Limite de CrÈdito do Cliente p/ poder comparar:
1) Com o Valor Total de sua dÌvida com a Empresa retornardo atravÈs da funÁ„o analise_credito
2) somando do Vale que est· sendo separado naquele exato momento para o mesmo ...*/
        $sql = "SELECT `limite_credito` 
                FROM `clientes` 
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos_cliente = bancos::sql($sql);
        $limite_credito = $campos_cliente[0]['limite_credito'];
/*Se o Valor de Limite de CrÈdito do Cliente for menor do que a sua dÌvida junto do vale 
que est· sendo separado, ent„o eu n„o posso separar esse Vale ...*/
        if(($valor_analise_credito + $_POST['txt_valor_total_produtos']) > $limite_credito) {
?>
            <Script Language = 'JavaScript'>
                alert('N√O … POSSÕVEL MANDAR NO VALE !!!')
                window.close()
            </Script>
<?
            exit;
        }
    }
/**********************************************************************************/
/***************Novo Controle de Rastreamento de Vales - 01/03/2016****************/
/**********************************************************************************/
    $sql = "INSERT INTO `vales_vendas` (`id_vale_venda`, `id_funcionario`, `id_transportadora`, `entregue_por`, `retirado_por`, `qtde_caixas`, `peso_bruto`, `valor_frete`, `data_sys`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_POST[cmb_transportadora]', '$_POST[txt_entregue_por]', '$_POST[txt_retirado_por]', '$_POST[txt_qtde_caixas]', '$_POST[txt_peso_bruto]', '$_POST[txt_valor_frete]', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    $id_vale_venda = bancos::id_registro();
/**********************************************************************************/

    //Aqui transforma em vetor a String de Itens de Pedido -> $id_pedido_venda_item
    $vetor_pedido_venda_item = explode(',', $_POST['id_pedido_venda_item']);

    //VerificaÁ„o de Empresas antes de disparo do loop de Itens + abaixo
    //Existe essa verificaÁ„o, porque n„o se pode mandar vale para pedidos de Empresas diferentes
    foreach($vetor_pedido_venda_item as $i => $id_pedido_venda_item) {
/*SeleÁ„o somente de Itens de Pedidos que foram selecionados pelo usu·rio na tela Principal de Itens 
<- 'pvi.status < 2' e pedidos j· liberados*/
        $sql = "SELECT pvi.`id_pedido_venda`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                WHERE pvi.`id_pedido_venda_item` = '$id_pedido_venda_item' 
                AND pvi.`status` < '2' LIMIT 1 ";
        $campos = bancos::sql($sql);
        
        //FunÁ„o que manda no vale a quantidade solicitada pelo usu·rio ...
        estoque_acabado::mandar_vale($id_pedido_venda_item, $_POST['txt_qtde'][$i]);
/*********************Roteiro para Gerar Listagem de SeparaÁ„o*********************/
//Aqui guarda na tabela relacional para poder gerar um relatÛrio de separaÁ„o do Estoque
        $sql = "INSERT INTO `pedidos_vendas_separacoes` (`id_pedido_venda_separacao`, `id_pedido_venda`, `id_produto_acabado`, `id_funcionario`, `qtde_separado`, `qtde_vale`, `data_sys`) VALUES (NULL, '".$campos[0]['id_pedido_venda']."', '".$campos[0]['id_produto_acabado']."', '$_SESSION[id_funcionario]', '0', '".$_POST['txt_qtde'][$i]."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
/**********************************************************************************/
/***************Novo Controle de Rastreamento de Vales - 01/03/2016****************/
/**********************************************************************************/
        $sql = "INSERT INTO `vales_vendas_itens` (`id_vale_venda_item`, `id_vale_venda`, `id_pedido_venda_item`, `qtde`) VALUES (NULL, '$id_vale_venda', '$id_pedido_venda_item', '".$_POST['txt_qtde'][$i]."') ";
        bancos::sql($sql);
/**********************************************************************************/
    }
?>
    <Script Language = 'JavaScript'>
        alert('ITEM(S) MANDADO(S) COM SUCESSO PARA VALE !')
        window.location = 'comprovante_entrega.php?id_vale_venda=<?=$id_vale_venda;?>&id_pedido_venda=<?=$campos[0]['id_pedido_venda'];?>'
    </Script>
<?
}else {
    /********************************************************************************************/
    /*******************************************CrÈdito******************************************/
    /********************************************************************************************/
    if(empty($id_cliente)) {
    //Busco o Cliente para testar na funÁ„o
        $sql = "SELECT `id_cliente` 
                FROM `pedidos_vendas` 
                WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        $campos     = bancos::sql($sql);
        $id_cliente = $campos[0]['id_cliente'];
    }
    //Verifico se o Cliente est· OK no que se refere ao seu CrÈdito ...
    $retorno_analise_credito = faturamentos::analise_credito_cliente($id_cliente);//Analisa p/ V c ele tem dÈbito e pode comprar devido seu limite ou crÈdito

    /*Significa que o Cliente for crÈdito C, D ou n„o est· OK no que se refere a sua situaÁ„o Financeira e sendo 
    assim eu n„o posso mandar no Vale os PA(s) ...*/
    //Significa que o est· em inegligÍncia no que se refere ao seu CrÈdito ...
    if($retorno_analise_credito['credito'] == 'C' || $retorno_analise_credito['credito'] == 'D' || ($retorno_analise_credito['credito_comprometido'] > $retorno_analise_credito['tolerancia_cliente'])) {
        if($retorno_analise_credito['credito'] == 'C' || $retorno_analise_credito['credito'] == 'D') {
            $problema_cliente = '\n\nCLIENTE COM CR…DITO => '.$retorno_analise_credito['credito'];
        }else if($retorno_analise_credito['credito_comprometido'] > $retorno_analise_credito['tolerancia_cliente']) {
            $problema_cliente = '\n\nCR…DITO COMPROMETIDO DO CLIENTE R$ '.number_format($retorno_analise_credito['credito_comprometido'], 2, ',', '.').' … MAIOR QUE A SUA TOLER¬NCIA R$ '.number_format($retorno_analise_credito['tolerancia_cliente'], 2, ',', '.').' !';
        }
?>
        <Script Language = 'JavaScript'>
            alert('N√O … POSSÕVEL MANDAR NO VALE !!!<?=$problema_cliente;?>')
            window.close()
        </Script>
<?
        exit;
    }
    /********************************************************************************************/
    
    //Busca o id_pais atr·ves do Cliente ...
    $sql = "SELECT `id_pais` 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    //Significa que o Cliente È do Tipo Internacional
    $tipo_moeda     = ($campos_cliente[0]['id_pais'] != 31) ? 'U$ ' : 'R$ ';
/****************************************************************************************/

    //Aqui transforma em vetor a String de Itens de Pedido -> $id_pedido_venda_item
    $vetor_pedido_venda_item = explode(',', $id_pedido_venda_item);

    //VerificaÁ„o de Empresas antes de disparo do loop de Itens + abaixo
    //Existe essa verificaÁ„o, porque n„o se pode mandar vale para pedidos de Empresas diferentes
    for($i = 0; $i < count($vetor_pedido_venda_item); $i++) {
        $sql = "SELECT pv.`id_empresa` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                WHERE pvi.`id_pedido_venda_item` = $vetor_pedido_venda_item[$i] LIMIT 1 ";
        $campos = bancos::sql($sql);
        //SÛ vai existir isso na primeira vez do loop, faÁo isso p/ poder comparar com as outras demais empresas
        if(empty($id_empresa_inicio)) {
            $id_empresa_inicio = $campos[0]['id_empresa'];
            //Busca de Dados da Empresa
            $empresa = genericas::nome_empresa($id_empresa_inicio);
            if($id_empresa_inicio == 1 || $id_empresa_inicio == 2) {//Se Albafer ou Tool Master
                $tipo_nota = ' (NF)';
            }else {//Se for Grupo
                $tipo_nota = ' (SGD)';
            }
            $empresas_diferentes = 0;//Essa vari·vel vai me servir de auxÌlio no JavaScript na hora de submeter
        }else {
            //Compara a primeira empresa do loop com as demais
            if($id_empresa_inicio != $campos[0]['id_empresa']) {
                $empresas_diferentes = 1;
                $i = count($vetor_pedido_venda_item);//FaÁo isso para sair do loop
            }
        }
    }

    if($empresas_diferentes == 1) {
    //Significa que contÈm Itens que s„o de Empresas Diferentes
?>
    <Script Language = 'JavaScript'>
        alert('N√O … POSSÕVEL MANDAR NO VALE !\nEXISTEM ITEM(NS) DE EMPRESA(S) DIFERENTE(S) !')
        window.close()
    </Script>
<?
	exit;
    }

    //Exclus„o de Transportadoras
    if(!empty($_POST['id_transportadora_excluir'])) {
        //Se a Transportadora for N/Carro ou Retira, ent„o n„o pode ser excluido do Cliente
        if($_POST['id_transportadora_excluir'] != 795 && $_POST['id_transportadora_excluir'] != 796) {
            $sql = "DELETE FROM `clientes_vs_transportadoras` WHERE `id_cliente` = '$id_cliente' AND `id_transportadora` = '$_POST[id_transportadora_excluir]' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
?>
<html>
<head>
<title>.:: Mandar no Vale ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos 	= document.form.elements
    var linhas 		= (typeof(elementos['txt_qtde[]'][0]) == 'undefined') ? 1 : (elementos['txt_qtde[]'].length)
//Verifica se a qtde digitada È > do que a qtde original
    for(var i = 0; i < linhas; i++) {
        var qtde_digitada   = eval(strtofloat(document.getElementById('txt_qtde'+i).value))
        var qtde_separada   = eval(strtofloat(document.getElementById('hdd_qtde_separada'+i).value))
        
        //Se o campo "qtde_digitada" estiver preenchido, mais o campo qtde qtde_separada = Zero, n„o tem sentido mandar no Vale 0 peÁas ??? ...
        if(document.getElementById('txt_qtde'+i).value != '' && qtde_digitada == 0) {
            alert('N√O … POSSÕVEL MANDAR NO VALE UM ITEM COM "QUANTIDADE SEPARADA" IGUAL A ZERO !')
            document.getElementById('txt_qtde'+i).focus()
            document.getElementById('txt_qtde'+i).select()
            return false
        }
        
        //A Qtde Digitada nunca pode ser maior do que a Qtde Separada ...
        if(qtde_digitada > qtde_separada) {
            alert('QTDE INV¡LIDA !!!\n\nQTDE DIGITADA MAIOR QUE A QTDE SEPARADA !')
            document.getElementById('txt_qtde'+i).focus()
            document.getElementById('txt_qtde'+i).select()
            return false
        }
    }
//Entregue por
    if(!texto('form', 'txt_entregue_por', '1', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ·ÈÌÛ˙¡…Õ”⁄Á« ', 'ENTREGUE POR', '2')) {
        return false
    }
//Retirado por
    if(!texto('form', 'txt_retirado_por', '1', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ·ÈÌÛ˙¡…Õ”⁄Á« ', 'RETIRADO POR', '2')) {
        return false
    }
//Qtde de Caixas
    if(!texto('form', 'txt_qtde_caixas', '1', '0123456789', 'QUANTIDADE DE CAIXAS', '1')) {
        return false
    }
//Peso Bruto
    if(!texto('form', 'txt_peso_bruto', '1', '0123456789,.', 'PESO BRUTO', '2')) {
        return false
    }
    var peso_liquido 	= eval(strtofloat(document.form.txt_peso_liquido.value))
    var peso_bruto      = eval(strtofloat(document.form.txt_peso_bruto.value))
//Aqui eu verifico se o Peso Bruto È menor do que o Peso LÌquido ...
    if(peso_bruto < peso_liquido) {
        alert('PESO BRUTO INV¡LIDO !\nPESO BRUTO MENOR DO QUE O PESO LÕQUIDO !!!')
        document.form.txt_peso_bruto.focus()
        document.form.txt_peso_bruto.select()
        return false
    }
//Transportadora
    if(!combo('form', 'cmb_transportadora', '', 'SELECIONE A TRANSPORTADORA !')) {
        return false
    }
    var id_transportadora = eval(document.form.cmb_transportadora.value)
/*Frete - Se a Transportadora for 797 - Sedex, 1050 - Correio Encomenda P.A.C., 
1092 - Sedex 10, 1093 - Motoboy, e Valor do Frete = 0, ent„o forÁo a calcular ...*/
    if(document.form.txt_valor_frete.value == '0,00' && (id_transportadora == 797 || id_transportadora == 1050 || id_transportadora == 1092 || id_transportadora == 1093)) {
        alert('VALOR DO FRETE INV¡LIDO !!!\nCALCULE O VALOR DO FRETE PARA ESSA TRANSPORTADORA ! ')
        document.form.cmd_calcular_frete.focus()
        document.form.cmd_calcular_frete.select()
        return false
    }
//Prepara a Tela p/ poder gravar no BD ...
//Aki faz tratamento nas caixinhas de qtde no vale para poder gravar no Banco
    for(i = 0; i < linhas; i++) document.getElementById('txt_qtde'+i).value         = strtofloat(document.getElementById('txt_qtde'+i).value)

/*Desabilito essa caixa aqui, porque ela ser· usada por par‚metro na outra tela p/ fazer 
a comparaÁ„o com a An·lise do CrÈdito ...*/
    document.form.txt_valor_total_produtos.disabled = false
    document.form.txt_peso_bruto.disabled           = false
    document.form.txt_valor_frete.disabled          = false
    document.form.txt_valor_total_produtos.value    = eval(strtofloat(document.form.txt_valor_total_produtos.value))
    document.form.txt_peso_bruto.value              = eval(strtofloat(document.form.txt_peso_bruto.value))
    document.form.txt_valor_frete.value             = eval(strtofloat(document.form.txt_valor_frete.value))
    
    /*Desabilito esse bot„o p/ o nome querido e felizardo usu·rio n„o ficar clicando trilhıes de vezes 
    e mandando v·rios vales e e-mails ...*/
    document.form.cmd_salvar.disabled               = false
    document.form.cmd_salvar.className              = 'textdisabled'
}

function calcular_peso_total(indice, peso_unitario) {
    var elementos   = document.form.elements
    var linhas      = (typeof(elementos['txt_qtde[]'][0]) == 'undefined') ? 1 : (elementos['txt_qtde[]'].length)
//Qtde de PÁs para o Vale
    var qtde        = (document.getElementById('txt_qtde'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_qtde'+indice).value)) : 0
//Aqui È o Resultado do Peso Total por Linha
    document.getElementById('txt_peso_total'+indice).value = qtde * peso_unitario
    document.getElementById('txt_peso_total'+indice).value = arred(document.getElementById('txt_peso_total'+indice).value, 4, 1)
    var peso_liquido_total = 0
//Aqui È o C·lculo referente ao Peso LÌquido -> que È o somatÛrio de todos os Pesos Totais
    for(i = 0; i < linhas; i++) peso_liquido_total+= eval(strtofloat(document.getElementById('txt_peso_total'+i).value))
    document.form.txt_peso_liquido.value = peso_liquido_total
//Tratamento da Caixa para exibiÁ„o na Tela
    document.form.txt_peso_liquido.value = arred(document.form.txt_peso_liquido.value, 4, 1)
}

function calcular_valor_item(indice, preco_unitario) {
    var elementos   = document.form.elements
    var linhas      = (typeof(elementos['txt_qtde[]'][0]) == 'undefined') ? 1 : (elementos['txt_qtde[]'].length)
//Qtde de PÁs para o Vale
    var qtde        = (document.getElementById('txt_qtde'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_qtde'+indice).value)) : 0
//Aqui È o Resultado do PreÁo Total por Linha
    document.getElementById('txt_valor_total_item'+indice).value = qtde * preco_unitario
    document.getElementById('txt_valor_total_item'+indice).value = arred(document.getElementById('txt_valor_total_item'+indice).value, 2, 1)
//Aqui È o C·lculo referente ao Total do Pedido -> que È o somatÛrio de todos os Itens
    var vlr_total_pedido = 0
    for(i = 0; i < linhas; i++) vlr_total_pedido+= eval(strtofloat(document.getElementById('txt_valor_total_item'+i).value))
    document.form.txt_valor_total_produtos.value = vlr_total_pedido
//Tratamento da Caixa para exibiÁ„o na Tela
    document.form.txt_valor_total_produtos.value = arred(document.form.txt_valor_total_produtos.value, 2, 1)
}

//Exclus„o das Transportadoras
function excluir_transportadora() {
    if(document.form.cmb_transportadora.value == '') {
        alert('SELECIONE A TRANSPORTADORA DO CLIENTE !')
        document.form.cmb_transportadora.focus()
        return false
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            document.form.id_transportadora_excluir.value = document.form.cmb_transportadora.value
            document.form.submit()
        }
    }
}

function calcular_frete() {
    if(document.form.cmb_modo_envio.value == 'CORREIO') {
        nova_janela('../../../../classes/cliente/calcular_frete_correio.php', 'CALCULAR_FRETE', '', '', '', '', '150', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {
        nova_janela('../../../../classes/cliente/calcular_frete_tam.php?valor_total_produtos='+document.form.txt_valor_total_produtos.value, 'CALCULAR_FRETE', '', '', '', '', '250', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<!--******************Controles de Tela*******************-->
<input type='hidden' name='controle'>
<input type='hidden' name='id_transportadora_atrelar'>
<input type='hidden' name='id_transportadora_excluir'>
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='id_pedido_venda_item' value='<?=$id_pedido_venda_item;?>'>
<!--******************************************************-->
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Mandar no Vale - <?=$empresa.$tipo_nota;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            <font title='Quantidade' style='cursor:help'>
                Qtde
            </font>
        </td>
        <td rowspan='2'>
            <font title='ReferÍncia * DiscriminaÁ„o' style='cursor:help'>
                Ref. * DiscriminaÁ„o
            </font>
        </td>
        <td rowspan='2'>
            <font title='Peso Unit·rio' style='cursor:help'>
                P. Unit
            </font>
        </td>
        <td rowspan='2'>
            <font title='Peso Total' style='cursor:help'>
                P. Total
            </font>
        </td>
        <td rowspan='2'>
            <font title='PreÁo Unit·rio <?=$tipo_moeda;?>' style='cursor:help'>
                PÁ Unit <?=$tipo_moeda;?>
            </font>
        </td>
        <td rowspan='2'>
            <font title='Valor Total <?=$tipo_moeda;?>' style='cursor:help'>
                Vlr Tot <?=$tipo_moeda;?>
            </font>
        </td>
        <td colspan='5'>
            <font title='Dados do Pedido' style='cursor:help'>
                Dados do Pedido
            </font>
        </td>
        <td rowspan='2'>
            <font title='N.∫ do Pedido' style='cursor:help'>
                N.∫&nbsp;Ped
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Quantidade Pedida' style='cursor:help'>
                Ped.
            </font>
        </td>
        <td>
            <font title='Quantidade Faturada' style='cursor:help'>
                Fat.
            </font>
        </td>
        <td>
            <font title='Quantidade Separada' style='cursor:help'>
                Sep.
            </font>
        </td>
        <td>
            <font title='Quantidade Pendente' style='cursor:help'>
                Pend.
            </font>
        </td>
        <td>
            <font title='Vale' style='cursor:help'>
                Vale
            </font>
        </td>
    </tr>
<?
        for($i = 0; $i < count($vetor_pedido_venda_item); $i++) {
/*SeleÁ„o somente de Itens de Pedidos que foram selecionados pelo usu·rio na tela Principal de Itens 
<- 'pvi.status < 2' e pedidos j· liberados*/
            $sql = "SELECT (pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale`) AS separada, pvi.`qtde_faturada`, 
                    ovi.`qtde`, ovi.`preco_liq_final`, pv.`id_empresa`, pv.`id_pedido_venda`, pv.`faturar_em`, 
                    pv.`condicao_faturamento`, pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, 
                    pv.`vencimento4`, pv.`liberado`, pvi.`id_pedido_venda_item`, pvi.`status_estoque`, 
                    pvi.`qtde_pendente`, pvi.`vale`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
                    pa.`operacao_custo`, pa.`peso_unitario`, pa.`observacao` 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                    WHERE pvi.`id_pedido_venda_item` = '$vetor_pedido_venda_item[$i]' 
                    AND pvi.`status` < 2 
                    ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` LIMIT 1 ";//N„o pode tirar o pvi.id_pedido_venda_item pois da erro de indexaÁ„o ...
            $campos             = bancos::sql($sql);
            $retorno            = estoque_acabado::qtde_estoque($campos[0]['id_produto_acabado']);
            $compra             = estoque_acabado::compra_producao($campos[0]['id_produto_acabado']);
            $producao           = $retorno[2];
            $compra_producao    = number_format($producao + $compra, 2, ',', '.');
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?
                $qtde = $campos[0]['separada'] - $campos[0]['qtde_faturada'];
            ?>
            <!--Essa quantidade È digit·vel-->
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$i;?>' value='<?=number_format($qtde, 1, ',', '');?>' size='8' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '1', '1', event);if(this.value == '0,0') {this.value = ''};calcular_peso_total('<?=$i;?>', '<?=$campos[0]['peso_unitario'];?>');calcular_valor_item('<?=$i;?>', '<?=$campos[0]['preco_liq_final'];?>')" class='caixadetexto'>
        </td>
        <td align='left'>
            <?=$campos[0]['referencia'].' * '.intermodular::pa_discriminacao($campos[0]['id_produto_acabado']);?>
        </td>
        <td>
<!--Esses par‚metro tela1 servem para o pop-up fazer a atualizaÁ„o na tela de baixo-->
            <a href="javascript:nova_janela('../../../../classes/produtos_acabados/alterar_peso_unitario.php?id_produto_acabado=<?=$campos[0]['id_produto_acabado'];?>&tela1=window.opener', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Atualizar Peso do Produto" class='link'>
            <?
                if($campos[0]['peso_unitario'] != '0.0000') {
                    echo number_format($campos[0]['peso_unitario'], 4, ',', '.');
                }else {
                    echo '-';
                }
            ?>
            </a>
        </td>
        <td>
            <?$peso_total = $qtde * $campos[0]['peso_unitario'];?>
            <input type='text' name='txt_peso_total[]' id='txt_peso_total<?=$i;?>' value='<?=number_format($peso_total, 4, ',', '.');?>' size='7' maxlength='6' class='textdisabled' disabled>
        </td>
        <td>
            <?=number_format($campos[0]['preco_liq_final'], 2, ',', '.');?>
        </td>
        <td>
            <?           
                $valor_total_item = ($qtde * $campos[0]['preco_liq_final']);
            ?>
            <input type='text' name='txt_valor_total_item[]' id='txt_valor_total_item<?=$i;?>' value='<?=number_format($valor_total_item, 2, ',', '.');?>' size='10' maxlength='9' class='textdisabled' disabled>
        </td>
        <td>
            <?=segurancas::number_format($campos[0]['qtde']);?>
        </td>
        <td>
            <?=segurancas::number_format($campos[0]['qtde_faturada']);?>
        </td>
        <td>
            <?=segurancas::number_format($campos[0]['separada'] - $campos[0]['qtde_faturada']);?>
            <!--Essa qtde È a original que eu utilizo para fazer comparaÁ„o com a digit·vel-->
            <input type='hidden' name='hdd_qtde_separada[]' id='hdd_qtde_separada<?=$i;?>' value='<?=number_format($campos[0]['separada'] - $campos[0]['qtde_faturada'], 1, ',', '.');?>'>
        </td>
        <td>
        <?
            if($campos[0]['qtde_pendente'] > $retorno[3]) {
                echo '<font color="red"><b>'.segurancas::number_format($campos[0]['qtde_pendente']).'</b></font>';
            }else {
                echo segurancas::number_format($campos[0]['qtde_pendente']);
            }
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos[0]['vale']);?>
        </td>
        <td>
            <?=$campos[0]['id_pedido_venda'];?>
        </td>
    </tr>
<?
            $peso_liquido_total+= $peso_total;
            $valor_total_dos_produtos+= $valor_total_item;
        }
?>
</table>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Dados para Comprovante de Entrega de Material p/ Cliente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Entregue por:</b>
        </td>
        <td colspan='3'>
            <input type='text' name='txt_entregue_por' title='Entregue por' size='25' class='caixadetexto'>
        </td>
        <td>
            <b>Retirado por:</b>
        </td>
        <td>
            <input type='text' name='txt_retirado_por' title='Retirado por' size='25' class='caixadetexto'>
        </td>
        <td>
            <b>Valor Total dos Produtos <?=$tipo_moeda;?></b>
        </td>
        <td>
            <input type='text' name='txt_valor_total_produtos' value='<?=number_format($valor_total_dos_produtos, 2, ',', '.');?>' title='Valor Total dos Produtos' size='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Qtde de Caixas:</b>
        </td>
        <td colspan='3'>
            <input type='text' name='txt_qtde_caixas' title='Qtde de Caixas' size='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
        <td>
            <b>Peso LÌquido:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_liquido' value='<?=number_format($peso_liquido_total, 4, ',', '.')?>' title='Peso LÌquido' size='10' class='textdisabled' disabled> Kgs
        </td>
        <td>
            <b>Peso Bruto:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_bruto' title='Peso Bruto' size='10' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> Kgs
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Transportadora:</b>
        </td>
        <td colspan='3'>
            <select name='cmb_transportadora' title='Selecione a Transportadora' class='combo'>
            <?
                $sql = "SELECT t.`id_transportadora`, t.`nome` 
                        FROM `clientes_vs_transportadoras` ct 
                        INNER JOIN `transportadoras` t ON t.`id_transportadora` = ct.`id_transportadora` AND t.`ativo` = '1' 
                        WHERE ct.`id_cliente` = '$id_cliente' ORDER BY t.`nome` ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;&nbsp;
            <img src = '../../../../../imagem/menu/incluir.png' border='0' title='Atrelar Transportadora' alt='Atrelar Transportadora' onclick="nova_janela('../../../../classes/cliente/atrelar_transportadoras.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;&nbsp;
            <img src = '../../../../../imagem/menu/excluir.png' border='0' title='Excluir Transportadora' alt='Excluir Transportadora' onclick='excluir_transportadora()'>
        </td>
        <td>
            <b>Valor do Frete:</b>
        </td>
        <td>
            <input type='text' name='txt_valor_frete' value='0,00' title='Valor do Frete' size='11' maxlength='9' class='textdisabled' disabled>
            -
            &nbsp;
            <select name='cmb_modo_envio' title='Modo de Envio' class='combo'>
                <option value='CORREIO'>CORREIO</option>
                <option value='TAM'>TAM</option>
            </select>
            &nbsp;
            <input type='button' name='cmd_calcular_frete' value='Calcular Frete' title='Calcular Frete' onclick='calcular_frete()' class='botao'>
        </td>
        <td colspan='2'>
            <a href="javascript:nova_janela('http://www2.correios.com.br/sistemas/precosPrazos/', 'CORREIOS', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Consultar Sedex (Correios)' class='link'>
                Consultar Sedex (Correios)
            </a>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>