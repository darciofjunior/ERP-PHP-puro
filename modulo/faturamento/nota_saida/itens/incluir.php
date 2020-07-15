<?
require('../../../../lib/segurancas.php');
require('../../../../lib/biblioteca.php');
require('../../../../lib/calculos.php');//Essa biblioteca � utilizada dentro da Biblioteca de Faturamentos ...
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');

$diferenca_prazo_medio_maximo_entre_pedido_nf = genericas::variavel(78);

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolu��o 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolu��o ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

$mensagem[1] = "<font class='atencao'>N�O EXISTE(M) PEDIDO(S) PENDENTE(S) PARA ESTE CLIENTE.</font>";
$mensagem[2] = "<font class='atencao'>EXCEDIDO A QUANTIDADE DE ITEM(NS) PARA ESSA NOTA FISCAL.</font>";
$mensagem[3] = "<font class='confirmacao'>ITEM(NS) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[4] = "<font class='atencao'>N�O EXISTE(M) ITEM(NS) PENDENTE(S) PARA ESSE PEDIDO !!! VERIFIQUE SE ESTE � DO TIPO L.D. OU SE ESTE N�O EST� SEPARADO PELO ESTOQUISTA.</font>";
$mensagem[5] = "<font class='atencao'>N�O � POSS�VEL INCLUIR ITEM(NS) ! ESTA NOTA FISCAL EST� TRAVADA.</font>";
$mensagem[6] = "<font class='erro'>VOC� N�O PODE INCLUIR ITEM(NS) COM IVA E SEM IVA NA MESMA NF QUANDO FOR FORA DO ESTADO DE S�O PAULO.</font>";

/********************************************************************************************************/
//Aki eu verifico quem � o Cliente deste Pedido, p/ ver se est�o preenc. corretamente os dados de Endere�o
$sql = "SELECT `id_cliente` 
        FROM `nfs` 
        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
$campos     = bancos::sql($sql);
$id_cliente = $campos[0]['id_cliente'];

//Fun��o q verifica se a Nota est� faturada, empacotada, despachada, cancelada
//caso sim, ent�o o usu�rio n�o pode + incluir, alterar ou excluir nenhum item
if(faturamentos::situacao_nota_fiscal($id_nf) >= 2) {//Est� liberado, ent�o � posso excluir nada
?>
<html>
<head>
<title>.:: Incluir Itens de Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[5];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    exit;
}

if($passo == 1) {
//Se o cadastro do Cliente estiver inv�lido, ent�o este tem que ser corrigido, antes de qualquer outra coisa
    $cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($id_cliente);
    if($cadastro_cliente_incompleto == 1) {
?>
        <Script Language = 'JavaScript'>
            alert('O CADASTRO DESTE CLIENTE EST� INCOMPLETO !\nCORRIJA O MESMO PARA CONTINUAR COM ESTE PROCEDIMENTO NORMALMENTE !')
            window.close()
        </Script>
<?
        exit;
    }
/********************************************************************************************************/
//Aki eu busco a quantidade de Itens que j� foram inclusos para esta Nota Fiscal
    $sql = "SELECT COUNT(`id_nf`) AS total_itens_nota 
            FROM `nfs_itens` 
            WHERE `id_nf` = '$_POST[id_nf]' ";
    $campos = bancos::sql($sql);
    $total_itens_nota = $campos[0]['total_itens_nota'];

//Aki verifico qual a foi a qtde de itens selecionados pelo usu�rio para Inclus�o em Nota Fiscal
    $checkbox_selecionados = count($_POST['chkt_pedido_venda_item']);

//Verifica se o Cliente tem Suframa
    $sql = "SELECT c.`id_pais`, nfs.`id_empresa`, nfs.`despesas_acessorias`, nfs.`valor_frete` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE nfs.`id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_pais            = $campos[0]['id_pais'];
    $id_empresa_nf      = $campos[0]['id_empresa'];//Empresa da NF que ser� utilizada mais abaixo ...
    $despesas_acessorias = $campos[0]['despesas_acessorias'];
    $valor_frete        = $campos[0]['valor_frete'];
    $total_maximo_permitida = 1000;//Na realidade, hoje em dia, j� � ilimitado ...
//Aki ultrapassou a qtde de itens permitidos por Nota Fiscal
    if(($checkbox_selecionados + $total_itens_nota) > $total_maximo_permitida) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>&valor=2'
        </Script>
<?
    }else {
        $retorno_analise_credito = faturamentos::analise_credito_cliente($id_cliente, $id_nf);
        $credito = $retorno_analise_credito['credito'];
        if($credito == 'C' || $credito == 'D') {//O Cliente jamais pode faturar uma NF caso possua o seu cr�dito como sendo C ou D ...
?>
            <Script Language = 'JavaScript'>
                alert('CLIENTE COM CR�DITO <?=$credito;?> !!!\n<?=$retorno_analise_credito['historico_cliente_em_js'];?>')
                window.close()
            </Script>
<?
            exit;
        }else if($credito == 'B') {
            $dolar_dia              = genericas::moeda_dia('dolar');//Usado mais abaixo ...
            $credito_comprometido 	= $retorno_analise_credito['credito_comprometido'];
            $tolerancia_cliente     = $retorno_analise_credito['tolerancia_cliente'];
//N�o posso incluir mais Itens nessa NF p/ o Cliente, pois o mesmo est� com o Saldo devedor ...
            if($credito_comprometido > $tolerancia_cliente) {
?>
            <Script Language = 'JavaScript'>
                alert('<?=$retorno_analise_credito['historico_cliente_em_js'];?>')
                window.close()
            </Script>
<?
            }
        }
//Vari�vel para fazer o controle da Mensagem
        $cont_itens_ignorados           = 0;
        $GLOBALS['cont_itens_aceitos']  = 0;

        if($credito == 'B') {
//Aqui eu busco o Peso de todos os Itens de PA que j� foram faturados ...
            $sql = "SELECT SUM(pa.`peso_unitario`) AS peso_total_faturado 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
                    WHERE nfsi.`id_nf` = '$id_nf' ";
            $campos_peso_total_faturado = bancos::sql($sql);
            $peso_total_faturado        = $campos_peso_total_faturado[0]['peso_total_faturado'];

            foreach($_POST['chkt_pedido_venda_item'] as $i => $id_pedido_venda_item) {
//Busca o Valor do Item Corrente que est� sendo faturado e que n�o foi enviado em vale ...
                $sql = "SELECT IF($id_pais = 31, SUM(pvi.`preco_liq_final` * $txt_qtde[$i]), SUM(pvi.`preco_liq_final` * $txt_qtde[$i]) * $dolar_dia) AS valor_item_faturando, SUM(pa.`peso_unitario`) AS peso_total_faturando 
                        FROM `pedidos_vendas_itens` pvi 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                        WHERE pvi.`id_pedido_venda_item` = '$id_pedido_venda_item' 
                        AND pvi.`vale` = '0' LIMIT 1 ";
                $campos_valores = bancos::sql($sql);
//Fa�o a busca desses dados p/ poder buscar o IPI na fun��o abaixo ...
                $sql = "SELECT `id_produto_acabado` 
                        FROM `pedidos_vendas_itens` 
                        WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
                $campos_consulta = bancos::sql($sql);
                $valor_total_itens_faturar+= round($campos_valores[0]['valor_item_faturando'], 2);
                $peso_total_faturando+=      $campos_valores[0]['peso_total_faturando'];
                /*Verifico se o Cr�dito Comprometido do Cliente somado ao Valor do Item q est� sendo faturado, 
                ultrapassa a Toler�ncia de Cr�dito do Cliente, caso isso aconte�a, ent�o eu n�o posso faturar 
                esse Item ...*/
            }
            $peso_total_faturar = $peso_total_faturado + $peso_total_faturando;
            $icms_st_ipi_perc = 20;//Aqui estamos estimando que esses impostos em uma NF dariam a� no m�ximo 20% ...
            $valor_total_itens_faturar*= (1 + $icms_st_ipi_perc / 100);

            if(($credito_comprometido + $valor_total_itens_faturar) > $tolerancia_cliente) {
?>
                <Script Language = 'JavaScript'>
                    window.location = '../../../financeiro/cadastro/credito_cliente/enviar_email_solic_credito.php?id_cliente=<?=$id_cliente;?>&valor_total_itens_faturar=<?=$valor_total_itens_faturar;?>&peso_total_faturar=<?=$peso_total_faturar;?>'
                </Script>
<?
                exit;
            }
        }
        //Aqui � a parte da inser��o dos itens do Pedido
        foreach($_POST['chkt_pedido_venda_item'] as $i => $id_pedido_venda_item) {
            //� partir de 09/05/2018 sempre � permitido incluir mais de uma vez o mesmo item em Nota Fiscal ...
            $retorno_funcao = faturamentos::controle_estoque($id_nf, $id_pedido_venda_item, $_POST['txt_qtde'][$i], $_POST['txt_qtde_nfe'][$i], $_POST['txt_preco_nfe'][$i], 1);//insere itens da nfs
                
        }

        if($retorno_funcao == 1) {//Significa que a Fun��o, foi executada normalmente ...
            //� partir de 09/05/2018 sempre � permitido incluir mais de uma vez o mesmo item em Nota Fiscal ...
            $valor = 3;
        }else if($retorno_funcao == 2) {//Significa que houve algum erro de Inclus�o de Item, devido existir alguns Itens com IVA e outros sem IVA ...
            $valor = 6;
        }
        /**************************************Controle com o Texto da Nota**************************************/
        /*Se houve alguma inclus�o de Item de Nota Fiscal ent�o reseto o texto da Nota Fiscal, porque tem textos que s�o montados 
        em cima destes itens ...*/
        if($valor == 3) {
            $sql = "UPDATE `nfs` SET `texto_nf` = '' WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
            bancos::sql($sql);
        }
        /********************************************************************************************************/
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir.php?id_nf=<?=$_POST['id_nf'];?>&opcao=<?=$_POST['opcao'];?>&gerenciar=<?=$_POST['gerenciar'];?>&valor=<?=$valor;?>'
        </Script>
<?
    }
}else {
//Aki eu busco a quantidade de Itens que j� foram inclus�o para esta Nota Fiscal
    $sql = "SELECT COUNT(`id_nf`) AS total_itens_nota 
            FROM `nfs_itens` 
            WHERE `id_nf` = '$id_nf' ";
    $campos = bancos::sql($sql);
    $total_itens_nota = $campos[0]['total_itens_nota'];//Qtde de Itens que j� foi importada para NF
/*Aqui eu verifico quem � o cliente da Nota Fiscal, e mais alguns campos que v�o estar me auxiliando 
na hora de fazer a importa��o do Pedido de Venda do cliente como Empresa, Empresa Divis�o, Prazo M�dio NF, 
marca��o Livre de D�bito, ...*/
    $sql = "SELECT c.`id_cliente`, c.`razaosocial`, c.`id_pais`, c.`id_uf`, c.`forma_pagamento` AS forma_pagamento_cliente, c.`tipo_faturamento`, 
            c.`tipo_suframa`, c.`tributar_ipi_rev`, c.`credito`, nfs.`id_empresa`, nfs.`id_nf_num_nota`, nfs.`finalidade`, nfs.`frete_transporte`, 
            nfs.`natureza_operacao`, nfs.`forma_pagamento`, nfs.`prazo_medio`, nfs.`suframa`, nfs.`id_funcionario_suframa`, nfs.`livre_debito` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $id_cliente             = $campos[0]['id_cliente'];
    $razao_social           = $campos[0]['razaosocial'];
    $id_pais                = $campos[0]['id_pais'];
    $id_uf_cliente          = $campos[0]['id_uf'];
    $forma_pagamento_cliente= $campos[0]['forma_pagamento_cliente'];
    $tipo_faturamento       = $campos[0]['tipo_faturamento'];
    $tipo_suframa           = $campos[0]['tipo_suframa'];
    $tributar_ipi_rev       = $campos[0]['tributar_ipi_rev'];
    $credito                = $campos[0]['credito'];
    $id_empresa_nf          = $campos[0]['id_empresa'];
    $id_nf_num_nota         = $campos[0]['id_nf_num_nota'];
    $finalidade             = $campos[0]['finalidade'];
    $frete_transporte       = $campos[0]['frete_transporte'];
    $natureza_operacao      = $campos[0]['natureza_operacao'];
    $forma_pagamento        = $campos[0]['forma_pagamento'];
    $prazo_medio_nf         = $campos[0]['prazo_medio'];
    $suframa_nf             = $campos[0]['suframa'];
    $id_funcionario_suframa = $campos[0]['id_funcionario_suframa'];
    $livre_debito           = $campos[0]['livre_debito'];//Vou utilizar + abaixo na hora de importar os Pedidos ...
    
    //Significa que o Cliente � do Tipo Internacional
    $tipo_moeda             = ($id_pais != 31) ? 'U$' : 'R$';
   
    $sql = "SELECT ov.`tipo_frete`, ovi.`id_produto_acabado_discriminacao`, pa.`referencia`, pa.`discriminacao`, 
            pa.`operacao`, pa.`operacao_custo`, pa.`peso_unitario`, pa.`peso_unitario`, 
            pa.`observacao`, pv.`id_empresa`, pv.`finalidade`, pv.`faturar_em`, pv.`livre_debito`, 
            pv.`prazo_medio`, pvi.*, r.`nome_fantasia` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
            INNER JOIN `representantes` r ON r.`id_representante` = ovi.`id_representante` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`liberado` = '1' AND pv.`livre_debito` = '$livre_debito' AND ((pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale` - pvi.`qtde_faturada`) + (pvi.`vale`) > 0) AND pv.`id_cliente` = '$id_cliente' 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
            WHERE pvi.`status` < '2' ";
/*N�o tem como paginar esse SQL, porque dentro do loop, tem uns desvios conforme o solicitado pelo Roberto
q acaba suprimindo as linhas, e no fim das contas o total de linhas n�o confere com o n�mero de registros
exibidos*/
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
/**************************Novo Controle de N.� **************************/
/*Aqui eu verifico se foi preenchido um N.� de Nota Fiscal no Cabe�alho, mas far� esse controle 
somente se a NF for da Albafer ou da Tool Master ...*/
    if($id_nf_num_nota == 0 && $id_empresa_nf != 4) {
?>
	<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
	<Script Language = 'JavaScript'>
            alert('SELECIONE UM N.� PARA ESTA NF DE SA�DA !')
            window.close()
/*Aqui eu passo a opcao como sendo 1, porque somente no primeiro Menu que eu posso 
incluir Itens de Nota Fiscal ...*/
            nova_janela('../dados_iniciais.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>&acao=G', 'DADOS_INICIAIS', '', '', '', '', '290', '750', 'c', 'c', '', '', 's', 's', '', '', '')
	</Script>
<?
        exit;
    }
/**************************Novo Controle de Suframa**************************/
/*Se o Cliente possui Suframa, ent�o verifico se j� foi dado algum parecer para este Suframa em NF, de habilitado ou desabilitado 
somente se a NF for da Albafer ou da Tool Master ...*/
    if($suframa_nf > 0 && $id_funcionario_suframa == 0 && $id_empresa_nf != 4) {
?>
	<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
	<Script Language = 'JavaScript'>
            alert('N�O FOI DEFINIDA A SITUA��O DO SUFRAMA NESTA NF !\n� NECESS�RIO DEFINIR A SITUA��O ANTES DE INCLUIR O(S) ITEM(NS) !')
/*Aqui eu passo a opcao como sendo 1, porque somente no primeiro Menu que eu posso 
incluir Itens de Nota Fiscal ...*/
            nova_janela('../alterar_cabecalho.php?id_nf=<?=$id_nf;?>&opcao=1', 'POP', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')
            nova_janela('https://servicos.suframa.gov.br/servicos/', 'CONSULTAR_SUFRAMA', 'F')
            window.close()
	</Script>
<?
        exit;
    }
        
/*************************************************************************/
//Se o cadastro do Cliente estiver inv�lido, ent�o este tem que ser corrigido, antes de qualquer outra coisa
    $cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($id_cliente);
    if($cadastro_cliente_incompleto == 1) {
?>
	<Script Language = 'JavaScript'>
            alert('O CADASTRO DESTE CLIENTE EST� INCOMPLETO !\nCORRIJA O MESMO PARA CONTINUAR COM ESTE PROCEDIMENTO NORMALMENTE !')
            window.close()
	</Script>
<?
        exit;
    }
/********************************************************************************************************/

    if(empty($forma_pagamento_cliente)) {//Se ainda n�o foi preenchida a Forma de Pagamento no cadastro do cliente pelo pessoal do Financeiro ...
        if($forma_pagamento == '') {//N�o est� preenchida a Forma de Pagamento do Cabe�alho da Nota Fiscal ...
            //Disparo um e-mail ao pessoal do Financeiro para que providenciem esse acerto o mais r�pido que poss�vel ...
            $assunto    = 'Urgente - Forma de Pagamento do Cliente '.$razao_social;
            $mensagem   = 'Estamos emitindo uma Nota Fiscal para o Cliente acima e precisa-se acertar a forma de pagamento.';

            comunicacao::email('ERP - GRUPO ALBAFER', 'analise.vendas@grupoalbafer.com.br; gfinanceiro@grupoalbafer.com.br; patricia.sueko@grupoalbafer.com.br', '', $assunto, $mensagem);
        
            //Modifico na Nota Fiscal esse campo para "Em An�lise" de modo que o sistema n�o fique disparando e-mail sempre que passar por aqui ...
            $sql = "UPDATE `nfs` SET `forma_pagamento` = '0' WHERE `id_nf` = '$id_nf' LIMIT 1 ";
            bancos::sql($sql);
        }
?>
        <Script Language = 'JavaScript'>
            alert('ENQUANTO O CAMPO "FORMA DE PAGAMENTO" N�O ESTIVER PREENCHIDO NO CADASTRO DO CLIENTE, N�O SER� POSS�VEL FATURAR ESSA NOTA FISCAL !!!\n\nJ� FOI ENVIADA UMA SOLICITA��O PARA O FINANCEIRO FAZER ESSE PROCEDIMENTO !')
            window.close()
	</Script>
<?
        exit;
    }else {//J� foi preenchida a Forma de Pagamento no cadastro do cliente pelo pessoal do Financeiro ...
        if(empty($forma_pagamento)) {//N�o est� preenchida a Forma de Pagamento do Cabe�alho da Nota Fiscal ...
?>
        <Script Language = 'JavaScript'>
            alert('"FORMA DE PAGAMENTO" J� EST� PREENCHIDA NO CADASTRO DO CLIENTE !!!\n\nPREENCHER COM ESTA NO CABE�ALHO DA NOTA FISCAL, SEN�O N�O SER� POSS�VEL FATURAR ESSA NOTA FISCAL !!!')
            window.close()
	</Script>
<?
        exit;
        }
    }
	
//M�ximo Permitido para Incluis�o de Itens em NF
    $total_maximo_permitida = 1000;//Na realidade hoje em dia, j� � ilimitado ...
//Aki � a Qtde de Itens que eu ainda posso estar Importando na NF
    $restante_itens_importar = $total_maximo_permitida - $total_itens_nota;
?>
<html>
<head>
<title>.:: Incluir Itens de Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var total_maximo_permitida  = 1000//Na realidade hoje em dia, j� � ilimitado ...
//Total de Itens que j� importados em NF
    var total_itens_nota        = eval('<?=$total_itens_nota;?>')
    var checkbox_selecionados   = 0
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if (elementos[i].checked == true) {
                valor = true
                checkbox_selecionados++
            }
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OP��O !')
        return false
    }
//Aki ultrapassou a qtde de itens permitidos por Nota Fiscal
    if((checkbox_selecionados + total_itens_nota) > total_maximo_permitida) {
        alert('EXCEDIDO A QUANTIDADE DE ITEM(NS) PARA ESSA NOTA FISCAL !\n\nOBS: DESMARQUE ALGUM(NS) ITEM(NS), POIS A QTDE M�XIMA PERMITIDA NESSA NOTA � DE NO M�XIMO '+total_maximo_permitida+' ITEM(NS) !')
        return false
//Ainda n�o ultrapassou a margem de itens permitidos, ent�o pode continuar incluindo itens
    }else {
        var linhas  = (typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') ? 1 : (elementos['chkt_pedido_venda_item[]'].length)
        for(i = 0; i < linhas; i++) {
            /**************************Controle p/ PA(s) ESP**************************/
            if(document.getElementById('chkt_pedido_venda_item'+i).checked) {
                if(document.getElementById('txt_qtde'+i).value == '') {
                    alert('DIGITE A QUANTIDADE !')
                    document.getElementById('txt_qtde'+i).focus()
                    return  false
                }

                if(document.getElementById('txt_qtde'+i).value == '0,00') {
                    alert('QUANTIDADE INV�LIDA !')
                    document.getElementById('txt_qtde'+i).focus()
                    document.getElementById('txt_qtde'+i).select()
                    return  false
                }
//Verifica se o valor digitado na Nota Fiscal � > do que o valor q est� em Pedido e q j� foi faturado
                var qtde            = eval(strtofloat(document.getElementById('txt_qtde'+i).value))
                var qtde_original   = eval(strtofloat(document.getElementById('txt_qtde_original'+i).value))
                var vale            = eval(strtofloat(document.getElementById('hdd_vale'+i).value))
//Nunca a Qtde da Nota Fiscal, pode ser menor do que a Qtde do Vale
                if(qtde < vale) {
                    alert('QUANTIDADE A FATURAR INV�LIDA !\nQUANTIDADE A FATURAR MENOR DO QUE A QUANTIDADE DO VALE !')
                    document.getElementById('txt_qtde'+i).focus()
                    document.getElementById('txt_qtde'+i).select()
                    return false
                }
                if(qtde > qtde_original) {
                    alert('QUANTIDADE A FATURAR INV�LIDA !\nQUANTIDADE A FATURAR MAIOR DO QUE A QUANTIDADE PEDIDA !')
                    document.getElementById('txt_qtde'+i).focus()
                    document.getElementById('txt_qtde'+i).select()
                    return false
                }
                /*Se o usu�rio preencher o campo de Qtde da coluna NFe e o "Pre�o de NFe" que foi calculado retornar 
                inv�lido, n�o podemos deixar salvar a Tela, at� que o usu�rio coloque uma Qtde compat�vel ...*/
                if(document.getElementById('txt_preco_nfe'+i).value == 'QTDE INVAL.') {
                    alert('QUANTIDADE NFe INV�LIDA !!!\nDIGITE UMA QUANTIDADE NFe V�LIDA !')
                    document.getElementById('txt_qtde_nfe'+i).focus()
                    document.getElementById('txt_qtde_nfe'+i).select()
                    return false
                }
                /**************************Controle com os Pre�os*************************/
                if(document.getElementById('hdd_valor_unitario'+i).value == '0,00') {
                    alert('PRE�O L�Q FINAL INV�LIDO !!!\n\nVALOR IGUAL A ZERO !')
                    document.getElementById('txt_qtde'+i).focus()
                    document.getElementById('txt_qtde'+i).select()
                    return false
                }
                /*************************************************************************/
            }
        }

//Aqui verifica se existem itens programados que est�o foram do prazo de faturamento
        for(i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_pedido_venda_item'+i).checked) {
                if(document.getElementById('hdd_item_programado'+i).value == 1) {//Significa que este item � programado
                    var resposta = confirm('EXISTEM ITEM(NS) PROGRAMADO(S) !\nPODE INCLUIR ???')
                    if(resposta == false) {//Aki o usu�rio n�o quis continuar o proc.
                        return false
//O usu�rio quis continuar o proced., independente da qtde de itens selec. e q est�o foram da programa��o
                    }else {//Foi Ok
                        break;
                    }
                }
            }
        }
/*Desabilita as caixas de qtde para poder gravar no BD, fa�o isso porque Tamb�m Prepara no formato moeda antes de submeter para o BD 
n�o � desabilitado a caixa de qtde quando o produto � do tipo ESP*/
        for(i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_pedido_venda_item'+i).checked) {
                //Desabilito p/ poder gravar no BD pq alguns casos, esses campos � s�o habilitados ...
                document.getElementById('txt_qtde'+i).disabled      = false
                document.getElementById('txt_qtde_nfe'+i).disabled  = false
                document.getElementById('txt_preco_nfe'+i).disabled = false
                //Trato esses campos em Formato de BD ...
                document.getElementById('txt_qtde'+i).value         = strtofloat(document.getElementById('txt_qtde'+i).value)
                document.getElementById('txt_preco_nfe'+i).value    = strtofloat(document.getElementById('txt_preco_nfe'+i).value)
            }
        }
        //Aqui � para n�o atualizar o frames abaixo desse Pop-UP ...
        document.form.nao_atualizar.value = 1
        document.form.action = '<?=$PHP_SELF.'?passo=1';?>'
    }
}

function calcular_preco_nfe(indice, valor_total_item) {
    if(document.getElementById('txt_qtde_nfe'+indice).value == '') {
        document.getElementById('txt_preco_nfe'+indice).value = ''
    }else {
        valor_total_item        = eval(strtofloat(valor_total_item))
        var preco_nfe           = valor_total_item / document.getElementById('txt_qtde_nfe'+indice).value
        //Sempre for�o o sistema a arredondar p/ 10 casas porque o JavaScript tem um erro Matem�tico de arredondamento - s� Deus sabe, testar 139.70 / 5 ????
        preco_nfe               = strtofloat(arred(String(preco_nfe), 10, 1))
        var preco_nfe_2casas    = strtofloat(arred(String(valor_total_item / document.getElementById('txt_qtde_nfe'+indice).value), 2, 1))

        if(preco_nfe - preco_nfe_2casas != 0) {
            document.getElementById('txt_preco_nfe'+indice).value = 'QTDE INVAL.'
        }else {
            document.getElementById('txt_preco_nfe'+indice).value = preco_nfe_2casas
            document.getElementById('txt_preco_nfe'+indice).value = arred(document.getElementById('txt_preco_nfe'+indice).value, 2, 1)
        }
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        var gerenciar   = eval('<?=$gerenciar;?>')
/*Significa que essa Tela de Incluir Itens de Nota Fiscal, foi acessada de dentro do Menu 
de Gerenciar que fica em Nota Fiscal ...*/  
        if(gerenciar == 1) {
            window.opener.parent.itens.document.location = '../../../faturamento/nota_saida/itens/itens.php?id_nf=<?=$_POST['id_nf'];?>&opcao=<?=$_POST['opcao'];?>'
            window.opener.parent.rodape.document.location = '../../../faturamento/nota_saida/itens/rodape.php?id_nf=<?=$_POST['id_nf'];?>&opcao=<?=$_POST['opcao'];?>'
/*Significa que essa Tela de Incluir Itens de Nota Fiscal, foi acessada de forma normal 
que � pela Nota Fiscal mesmo*/
        }else {
            window.opener.parent.itens.document.form.submit()
            window.opener.parent.rodape.document.form.submit()
        }
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' onsubmit='return validar()'>
<table width='95%' border='0' name='tabela1' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='13'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Incluir Item(ns) de Pedido(s) p/ Cliente:&nbsp;
            <font color='yellow'>
                <?=$razao_social;?>
            </font>
            <p/>
            Empresa: 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_nf);?>
            </font>
            &nbsp;-&nbsp;
            Tipo de Faturamento: 
            <font color='yellow'>
            <?
                if($tipo_faturamento == 1) {//Significa que o Cliente fatura tudo pela Albaf�r ...
                    echo 'TUDO PELA ALBAFER';
                }else if($tipo_faturamento == 2) {//Significa que o Cliente fatura tudo pela Tool Master ...
                    echo 'TUDO PELA TOOL MASTER';
                }else if($tipo_faturamento == 'Q') {//Significa que o Cliente fatura por Ambas Empresas - Indiferente ...
                    echo 'QUALQUER EMPRESA';
                }else if($tipo_faturamento == 'S') {//Significa que o Cliente fatura por Ambas Empresas - apenas itens da empresa escolhida ...
                    echo 'SEPARADAMENTE';
                }
            ?>
            </font>
            &nbsp;-&nbsp;
            Finalidade: 
            <font color='yellow'>
            <?
                if($finalidade == 'C') {
                    echo 'CONSUMO';
                }else if($finalidade == 'I') {
                    echo 'INDUSTRIALIZA��O';
                }else {
                    echo 'REVENDA';
                }
            ?>
            </font>
            &nbsp;-&nbsp;
            Frete Transporte: 
            <font color='yellow'>
            <?
                if($frete_transporte == 'C') {
                    echo 'CIF';
                }else {
                    echo 'FOB';
                }
            ?>
            </font>
            &nbsp;-&nbsp;
            Prazo M�dio: 
            <font color='yellow'>
            <?
                echo $prazo_medio_nf;
                if($livre_debito == 'S') echo '<font title="Livre de D�bito" style="cursor:help">LD</font>';
            ?>
        </td>
    </tr>
</table>
<?
    if($linhas_itens > 0) {//Se encontrou pelo menos 1 Item ...
?>
<table width='95%' border='0' name='tabela1' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onclick="selecionar_especial('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td rowspan='2'>
            Emp / Cons-Rev <br/>Frete / Pz M�dio / LD
            <br/>
            <?
                $checked = (!empty($_POST['chkt_ignorar_seguranca'])) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_ignorar_seguranca' id='chkt_ignorar_seguranca' value='S' title='Ignorar Seguran�a' onclick="document.form.vetor_esp.value = '';document.form.submit()" class='checkbox' <?=$checked;?>>
            <label for='chkt_ignorar_seguranca' title='Ignorar Seguran�a' style='cursor:help'>
                Ig Seg
            </label>
        </td>
        <td colspan='4'>
            <b>Quantidade(s)</b>
        </td>
        <td rowspan='2'>
            <b>Produto</b>
        </td>
        <td rowspan='2'>
            <b title='Pre�o L. Final <?=$tipo_moeda;?>' style='cursor:help'>
                P�o L. <br>Final <?=$tipo_moeda;?>
            </b>
        </td>
        <td rowspan='2'>
            <b title='N.� do Pedido' style='cursor:help'>
                N.� Ped
            </b>
        </td>
        <td rowspan='2'>
            <b>IPI %</b>
        </td>
        <td rowspan='2'>
            <b title='Peso/Pe�a em (Kg)' style='cursor:help'>
                Peso/<br>P�(Kg)
            </b>
        </td>
        <td rowspan='2'>
            <b>Faturar em</b>
        </td>
        <td colspan='2'>
            <b>Valor(es) NFE</b>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Fat
        </td>
        <td>
            Sep.
        </td>
        <td>
            Vale
        </td>
        <td>
            Rep
        </td>
        <td>
            Qtde
        </td>
        <td>
            Pre�o
        </td>
    </tr>
<?
        /*Data de Programa��o seguindo o Padr�o que � a Data de Hoje + "1 dia", para que o faturista 
        n�o esque�a de dar dias a mais no Faturamento ...*/
        $data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
        
        $indice = 0;//Essa vari�vel vai auxiliar na pagina��o
        
        if($id_empresa_nf == 1 || $id_empresa_nf == 2) {//Alba ou Tool ...
            if($id_pais == 31) {//Nacional ...
                if($tipo_faturamento == 1) {//Tudo pela Alba ...
                    $vetor_empresas_tipo_faturamento = array(1);
                }else if($tipo_faturamento == 2) {//Tudo pela Tool ...
                    $vetor_empresas_tipo_faturamento = array(2);
                }else if($tipo_faturamento == 'Q') {//Qualquer Empresa ...
                    $vetor_empresas_tipo_faturamento = array(1, 2);
                }else {//Separadamente ...
                    //Se o Cliente tem o Faturamento do Tipo Separadamente, traz s� da Empresa do Pedido ...
                    $vetor_empresas_tipo_faturamento = array($id_empresa_nf);
                }
            }else {//Internacional ...
                /*O sistema s� ira cair nesse if abaixo quando o Pais for Brasil 

                Obs: Se o Pais for Estrangeiro: Trato como se fosse Separadamento, pois ja tivemos
                problemas de o Pedido sair como um Empresa e a NF como outra, isso atrapalha toda
                documentacao de Venda levantada "Proforma" ...*/
                $vetor_empresas_tipo_faturamento = array($id_empresa_nf);
            }
        }else {//Grupo
            $vetor_empresas_tipo_faturamento = array(4);
        }
        
        for($i = 0; $i < $linhas_itens; $i++) {
            /****************Regras p/ habilitar ou desabilitar Checkbox****************/

            /*1) Empresa do Pedido difere do Tipo de Faturamento do cadastro do Cliente ...
              2) Finalidade da Nota Fiscal difere da Finalidade do Pedido, mas s� quando esta for com Nota Fiscal ...
              3) Livre de D�bito da Nota Fiscal difere do Livre de D�bito do Pedido ...
              4) Frete Transporte da Nota Fiscal difere do Frete Transporte do Or�amento ...
              5) O prazo m�dio da NF n�o pode ser maior que o Prazo M�dio do pedido + $diferenca_prazo_medio_maximo_entre_pedido_nf 

                Exemplo: Pz na Nota Fiscal = 11 - Pz M�dio no Pedido = 30 ...
                $prazo_medio_nf > ($campos_itens[$i]['prazo_medio'] + $diferenca_prazo_medio_maximo_entre_pedido_nf) 
             
             11 > (30 + 10) = 11 > 40 Se fosse n�o passaria porque significa que esses Prazos est�o 
            com muito diverg�ncia e n�o podemos faturar ...*/
            
            if(!in_array($campos_itens[$i]['id_empresa'], $vetor_empresas_tipo_faturamento) || 
                ($finalidade != $campos_itens[$i]['finalidade'] && $campos_itens[$i]['id_empresa'] != 4) || 
                ($livre_debito != $campos_itens[$i]['livre_debito']) || 
                ($frete_transporte != $campos_itens[$i]['tipo_frete'] && empty($_POST['chkt_ignorar_seguranca'])) || 
                ($prazo_medio_nf > ($campos_itens[$i]['prazo_medio'] + $diferenca_prazo_medio_maximo_entre_pedido_nf) && empty($_POST['chkt_ignorar_seguranca'])) 
                /*(abs($campos_itens[$i]['prazo_medio'] - $prazo_medio_nf) > $diferenca_prazo_medio_maximo_entre_pedido_nf)*/) {
                    $disabled_checkbox  = 'disabled';
                    $onclick_checkbox   = '';
            }else {
                //$prazo_medio        = 'OK';
                $disabled_checkbox  = '';
                $onclick_checkbox   = "checkbox('form', '".$indice."', '#E8E8E8', '".$campos_itens[$i]['referencia']."', '".$campos_itens[$i]['prazo_medio']."') ";
            }
            /***************************************************************************/

            $qtde_separada  = $campos_itens[$i]['qtde'] - $campos_itens[$i]['qtde_pendente'] - $campos_itens[$i]['vale'] - $campos_itens[$i]['qtde_faturada'];

            if($indice == $restante_itens_importar) {
?>
    <tr class='linhanormal'>
        <td bgcolor='red' colspan='11'>
            <font color='#FFFFFF' size='2'>
                <b>* Limite M�ximo Permitido para Inclus�o de Item(ns) nesta Nota Fiscal</b>
            </font>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal' onclick="checkbox('form', '<?=$indice;?>', '#E8E8E8', '<?=$campos_itens[$i]['referencia'];?>', '<?=$campos_itens[$i]['prazo_medio'];?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_pedido_venda_item[]' id='chkt_pedido_venda_item<?=$indice;?>' value="<?=$campos_itens[$i]['id_pedido_venda_item'];?>" onclick="<?=$onclick_checkbox;?>" class='checkbox' <?=$disabled_checkbox;?>>
        </td>
        <td>
            <?
                echo '<font title="'.genericas::nome_empresa($campos_itens[$i]['id_empresa']).'" style="cursor:help">'.substr(genericas::nome_empresa($campos_itens[$i]['id_empresa']), 0, 1).'</font> / ';
            
                if($campos_itens[$i]['finalidade'] == 'C') {
                    echo '<font title="Consumo" style="cursor:help">C</font> / ';
                }else if($campos_itens[$i]['finalidade'] == 'I') {
                    echo '<font title="Industrializa��o" style="cursor:help">I</font> / ';
                }else {
                    echo '<font title="Revenda" style="cursor:help">R</font> / ';
                }
                
                if($campos_itens[$i]['tipo_frete'] == 'C') {
                    echo 'CIF / ';
                }else {
                    echo 'FOB / ';
                }
                
                if($campos_itens[$i]['vencimento1'] == 0) {
                    $dados_vencimento = '� vista';
                }else {
                    $dados_vencimento = $campos_itens[$i]['vencimento1'];
                    if($campos_itens[$i]['vencimento2'] > 0) $dados_vencimento.= ' / '.$campos_itens[$i]['vencimento2'];
                    if($campos_itens[$i]['vencimento3'] > 0) $dados_vencimento.= ' / '.$campos_itens[$i]['vencimento3'];
                    if($campos_itens[$i]['vencimento4'] > 0) $dados_vencimento.= ' / '.$campos_itens[$i]['vencimento4'];
                }
                
                $dados_faturamento.= '<font title="Vencimentos: '.$dados_vencimento.'" style="cursor:help">'.$campos_itens[$i]['prazo_medio'].'</font>';
                echo $dados_faturamento;
                
                if($campos_itens[$i]['livre_debito'] == 'S') echo '<font style="Livre de D�bito" cursor="help"> / LD</font>';
            ?>
        </td>
        <td>
            <?
                if($campos_itens[$i]['referencia'] != 'ESP') {//Normais
                    $class = 'textdisabled';
                }else {
                    $class = 'textdisabled';
                    //Esse aqui � um vetor de controle para o JavaScript ...
                    $vetor_esp = $vetor_esp.$indice.',';
                }
                $quantidade     = $qtde_separada + $campos_itens[$i]['vale'];
                $casas_decimais = (is_integer($quantidade)) ? 0 : 2;
            ?>
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$indice;?>' value='<?=number_format($quantidade, $casas_decimais, ',', '.');?>' title='Digite a Quantidade' maxlength='8' size='7' onclick="checkbox('form', '<?=$indice;?>', '#E8E8E8', '<?=$campos_itens[$i]['referencia'];?>', '<?=$campos_itens[$i]['prazo_medio'];?>');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '<?=$casas_decimais;?>', '', event)" class='<?=$class;?>' disabled>
            <input type='hidden' name='txt_qtde_original[]' id='txt_qtde_original<?=$indice;?>' value='<?=number_format($quantidade, $casas_decimais, ',', '.');?>'>
        </td>
        <td>
            <?=number_format($qtde_separada, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos_itens[$i]['vale'], 2, ',', '.');?>
            <input type='hidden' name='hdd_vale[]' id='hdd_vale<?=$indice;?>' value='<?=number_format($campos_itens[$i]['vale'], 2, ',', '.');?>'>
        </td>
        <td>
            <?=$campos_itens[$i]['nome_fantasia'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, 1, 1, $campos_itens[$i]['id_produto_acabado_discriminacao']);?>
        </td>
        <td align='right'>
        <?
            /**********************************************************************/
            /***Nota Fiscal de Venda Originada de Encomenda para Entrega Futura****/
            /**********************************************************************/
            if($natureza_operacao == 'VOF') {
                //Mas estes incidem sobre o valor Total do Produto ...
                $calculo_impostos_item  = calculos::calculo_impostos($campos_itens[$i]['id_pedido_venda_item'], $campos_itens[$i]['id_pedido_venda'], 'PV');
                $valor_ipi              = round($calculo_impostos_item['valor_ipi'] / $quantidade, 2);
                $valor_icms_st          = round($calculo_impostos_item['valor_icms_st'] / $quantidade, 2);
                $valor_unitario         = $campos_itens[$i]['preco_liq_final'] + ($valor_ipi + $valor_icms_st);
                
                if($valor_ipi > 0) {
                    $font = '<font title="Pre�o L. Final '.$tipo_moeda.' c/ IPI => '.number_format($campos_itens[$i]['preco_liq_final'] + $valor_ipi, 2, ',', '.').'" style="cursor:help">';
                }
                echo $font.number_format($valor_unitario, 2, ',', '.');
            }else {
                $valor_unitario         = $campos_itens[$i]['preco_liq_final'];
                
                /*Esse controle � de extrema import�ncia porque em casos de "Gato por Lebre", preciso pegar 
                os impostos do Gato ...

                Ex: o cliente comprou MRH-042 "Gato", mas estamos enviando o MRT-042 "Lebre ou substituto" ...*/
                $id_produto_acabado_utilizar = (!empty($campos_itens[$i]['id_produto_acabado_discriminacao'])) ? $campos_itens[$i]['id_produto_acabado_discriminacao'] : $campos_itens[$i]['id_produto_acabado'];

                //Essas vari�veis ser�o utilizadas mais abaixo ...
                $dados_produto  = intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf_cliente, $id_cliente, $id_empresa_nf, $finalidade);
                
                if($dados_produto['ipi'] > 0) {
                    $valor_ipi = $valor_unitario * $dados_produto['ipi'] / 100;
                    $font = '<font title="Pre�o L. Final '.$tipo_moeda.' c/ IPI => '.number_format($valor_unitario + $dados_produto['ipi'], 2, ',', '.').'" style="cursor:help">';
                }
                echo $font.number_format($valor_unitario, 2, ',', '.');
            }
        ?>
            <input type='hidden' name='hdd_valor_unitario[]' id='hdd_valor_unitario<?=$indice;?>' value='<?=number_format($valor_unitario, 2, ',', '.');?>'>
        </td>
        <td>
            <a href='detalhes_pedido.php?id_pedido_venda=<?=$campos_itens[$i]['id_pedido_venda'];?>' title='Visualizar Detalhes de Pedido' class='html5lightbox'>
                <?=$campos_itens[$i]['id_pedido_venda'];?>
            </a>
        </td>
        <td>
        <?
            if($dados_produto['ipi'] == 0) {
                echo 'S/IPI';
            }else {
                echo number_format($dados_produto['ipi'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
            <?=number_format($campos_itens[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
        <td>
        <?
            if($campos_itens[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                if($campos_itens[$i]['faturar_em'] > $data_atual_mais_um) {
                    echo '<font color="red">'.data::datetodata($campos_itens[$i]['faturar_em'], '/').'</font>';
                    //Item est� fora do Prazo de Faturamento
                    $item_programado = 1;
                }else {
                    echo data::datetodata($campos_itens[$i]['faturar_em'], '/');
                    $item_programado = 0;
                }
            }else {
                echo '&nbsp;';
                $item_programado = 0;
            }
        ?>
            <input type='hidden' name='hdd_item_programado[]' id='hdd_item_programado<?=$indice;?>' value='<?=$item_programado;?>'>
        </td>
        <td>
            <input type='text' name='txt_qtde_nfe[]' id='txt_qtde_nfe<?=$indice;?>' title='Digite a Quantidade da NFe' maxlength='6' size='8' onclick="checkbox('form', '<?=$indice;?>', '#E8E8E8', '<?=$campos_itens[$i]['referencia'];?>', '<?=$campos_itens[$i]['prazo_medio'];?>');return focos(this)" onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular_preco_nfe('<?=$indice;?>', '<?=number_format($quantidade * $valor_unitario, 2, ',', '.');?>')" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_preco_nfe[]' id='txt_preco_nfe<?=$indice;?>' title='Digite o Pre�o da NFe' maxlength='10' size='12' onclick="checkbox('form', '<?=$indice;?>', '#E8E8E8', '<?=$campos_itens[$i]['referencia'];?>', '<?=$campos_itens[$i]['prazo_medio'];?>');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>
        </td>
    </tr>
<?
            $indice++;
            
            //Limpo essas vari�veis p/ n�o herdar valores do Loop anterior ...
            unset($font);
            unset($dados_faturamento);
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <input type='button' name='cmd_cabecalho' value='Cabe&ccedil;alho' title='Cabe&ccedil;alho' onclick="nova_janela('../alterar_cabecalho?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>', 'CABECALHO', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');selecionar_especial('form', 'chkt_tudo', totallinhas, '#E8E8E8')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
<?
//Aki � simplesmente um contador, n�o tem pagina��o por causa que o Roberto pediu uns desvio no meio do loop
?>
    <tr>
        <td colspan='14'>
            &nbsp;
        </td>
    </tr>
    <tr class='confirmacao' align='center'>
        <td colspan='14'>
            <font face='verdana, arial, helvetica, sans-serif'><b>
                Total de Registro(s): <?=$indice;?>
            </font>
        </td>
    </tr>
<?/****************************************************************************************************/?>
</table>
<!--****************Controles de Tela***************-->
<?
    $vetor_esp = substr($vetor_esp, 0, strlen($vetor_esp) - 1);
?>
<input type='hidden' name='vetor_esp' value='<?=$vetor_esp;?>'>
<input type='hidden' name='id_nf' value='<?=$id_nf;?>'>
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
<!--Significa que esse arquivo foi chamado pela Tela do Gerenciar-->
<input type='hidden' name='gerenciar' value='<?=$gerenciar;?>'>
<input type='hidden' name='nao_atualizar'>
<!--************************************************-->
</form>
</body>
</html>
<pre>
<font color='red'><b>Observa��o:</b></font>

S� exibe Pedido(s) do mesmo Tipo de Nota que foi selecionado em NF

NF - Consumo            => Ped - Consumo
NF - Industrializa��o   => Ped - Industrializa��o
NF - Revenda            => Ped - Revenda
NF - SGD                => Ped - SGD
NF - NF                 => Ped - NF


<font color='blue'><b>Total de Item(ns) Permitido(s) em Nota Fiscal</b></font>

<b>Nota Fiscal do Tipo NF / SGD</b>  -> 1000 Itens no m�ximo

<font color='blue'>
Os campos gravados nessa tabela s�o:

id_produto_acabado
id_representante
id_classific_fiscal
peso_unitario
qtde
qtde_nfe
vale
valor_unitario ou valor_unitario_exp
preco_nfe
comissao_new
comissao_extra
ipi
icms
reducao
icms_intraestadual
iva
icms_creditar_rs
data_sys
</font>
</pre>
<?
    }else {//N�o existe nenhuma linha exibida ...
?>
<table width='95%' border='0' name="tabela1" align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class='botao'>
        </td>
    </tr>
</table>
<?
    }
}
?>