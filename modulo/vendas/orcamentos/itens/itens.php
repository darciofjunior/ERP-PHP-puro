<?
require('../../../../lib/segurancas.php');
if(empty($_GET['pop_up']))  require '../../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
require('../../../../lib/calculos.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro do Custos ...
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$id_orcamento_venda = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_orcamento_venda'] : $_GET['id_orcamento_venda'];

//Essa j� prepara as vari�veis para o c�lculo das etapas do custo ...
$taxa_financeira_vendas     = genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
//Vari�veis utilizadas mais abaixo ...
$fator_desc_maximo_venda    = genericas::variavel(19);

/*********************************************************************************/
/************************* Negocia��o Finalizada *********************************/
/*********************************************************************************/
if(!empty($_GET['negociacao_finalizada'])) {
    $sql = "UPDATE `orcamentos_vendas` SET `negociacao_finalizada` = '$_GET[negociacao_finalizada]' WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
    bancos::sql($sql);
}
/*********************************************************************************/
/***************** L�gica de Pend�ncia de Acompanhamento *************************/
/*********************************************************************************/
//Aqui eu verifico se existe alguma pend�ncia de Acompanhamento p/ este Or�amento ...
$sql = "SELECT `id_vendedor_pendencia` 
        FROM `vendedores_pendencias` 
        WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
$campos_pendencia = bancos::sql($sql);
if(count($campos_pendencia) == 1) {
/*Deleto a Pend�ncia de Or�amento daquele Vendedor, pois o Vendedor j� tomou conhecimento, se ele quiser registrar 
algum Follow-UP da� fica a crit�rio dele ...*/
    $sql = "DELETE FROM `vendedores_pendencias` WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
    bancos::sql($sql);
}
/**********************************************************************************/
/************************Melhoria de Desempenho no Or�amento***********************/
/**********************************************************************************/
/*� partir de 25/10/13, para deixar o ORC + r�pido, quando o ORC estiver dentro do prazo de validade n�o 
ser�o mais atualizados os campos: 

1) Pre�o L.F.R$/p� p/ itens ESP, a n�o ser que algu�m altere o Custo do PA (altera��es de insumos em compras, 
aumento salarial, mudan�a de lista de pre�os, etc ... n�o ser�o levadas em conta)   
2) Representante
3) Desconto do cliente.

Estes dados ser�o alterados apenas quando o ORC estiver fora do prazo de validade ou alterando-se algo no item do ORC ... */
/**********************************************************************************/
if($_POST['hdd_calcular_orcamentos_itens'] == 'S') {
    /*Se existirem muitos itens no Or�amento, isso faz com que o mesmo fique muito pesado, ent�o sendo assim 
    aumentei o timer em espec�fico p/ essa Rotina = 300 segundos ...*/
    set_time_limit(300);
    
    /*Tiro a Marca��o de Queima de todos os Itens do Or�amento que est�o em Queima de Estoque - Zero o Desc Extra e Acr�scimo 
    porque esses 2 campos interferem no P�o L�quido do Item ...*/
    $sql = "UPDATE `orcamentos_vendas_itens` SET `queima_estoque` = 'N', `desc_extra` = '0', `acrescimo_extra` = '0' WHERE `id_orcamento_venda` = '$id_orcamento_venda' AND `queima_estoque` = 'S' ";
    bancos::sql($sql);
    //Busco o id_item do Or�amento p/ poder rodar a fun��o abaixo em cima de cada item ...
    $sql = "SELECT id_orcamento_venda_item 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$id_orcamento_venda' ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
    if($linhas_itens == 0) {//Se o ORC n�o possui nenhum Item, simpleste chamo a fun��o abaixo ...
        //Fun��o q atualiza a 'Data e Hora' e 'Data de Emiss�o' que o usu�rio realizou a altera��o nesse or�amento ...
        vendas::atualizar_orcamento_vendas($id_orcamento_venda);
    }else {//ORC possui menos 1 item ...
        for($i = 0; $i < $linhas_itens; $i++) {
            vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item'], 'S', $_POST['hdd_atualizar_tudo']);
//Aqui eu atualizo a ML Est do Iem do Or�amento ...
            custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
/*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($id_orcamento_venda, $campos_itens[$i]['id_orcamento_venda_item']);
        }
    }
?>
    <Script Language = 'JavaScript'>
        alert('TODOS OS PRE�OS E CUSTOS DO OR�AMENTO FORAM ATUALIZADOS COM SUCESSO !')
        /*Fa�o esse trambique para o caso de o usu�rio ter escolhido atualizar os pre�os e custos do or�amento 
        clicando no bot�o atualizar_tudo() o que gera a vari�vel $_POST['hdd_calcular_orcamentos_itens'] = 'S' 
        ao dar recarregar na tela de itens, ficava sempre retirando a promo��o porque na mem�ria do servidor 
        esse POST -> $_POST['hdd_calcular_orcamentos_itens'], uma vez submetido ficava armazenado, agora no caso 
        de um location como o que fiz abaixo esse $_POST � eliminado e n�o tenho mais nenhum problema com a 
        queima ...*/
        window.location = 'itens.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'
    </Script>
<?
}
/*********************************************************************************/
//Busca de alguns dados do Or�amento ...
$sql = "SELECT c.`id_pais`, IF(c.`nomefantasia` = '', c.`razaosocial`, CONCAT(c.`nomefantasia`, ' (', c.`razaosocial`, ')')) AS cliente, 
        c.`cod_cliente`, c.`tipo_suframa`, c.`cod_suframa`, c.`isento_st`, c.`tipo_faturamento`, 
        ov.`id_cliente`, ov.`id_cliente_contato`, ov.`finalidade`, ov.`tipo_frete`, ov.`nota_sgd`, 
        ov.`desc_icms_sqd_auto`, DATE_FORMAT(ov.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
        ov.`prazo_a`, ov.`prazo_b`, ov.`prazo_c`, ov.`prazo_d`, ov.`prazo_medio`, ov.`congelar`, 
        ov.`comprar_como_export`, ov.`negociacao_finalizada` 
        FROM `orcamentos_vendas` ov 
        INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
        WHERE ov.id_orcamento_venda = '$id_orcamento_venda' ";
$campos = bancos::sql($sql);
if($campos[0]['prazo_d'] > 0) $prazo_faturamento = '/'.$campos[0]['prazo_d'];
if($campos[0]['prazo_c'] > 0) $prazo_faturamento = '/'.$campos[0]['prazo_c'].$prazo_faturamento;

if($campos[0]['prazo_b'] > 0) {
    $prazo_faturamento = $campos[0]['prazo_a'].'/'.$campos[0]['prazo_b'].$prazo_faturamento;
}else {
    $prazo_faturamento = ($campos[0]['prazo_a'] == 0) ? '� vista' : $campos[0]['prazo_a'];
}

//Aqui � a verifica��o do Tipo de Nota
if($campos[0]['nota_sgd'] == 'S') {
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
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet' media = 'screen'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Vari�vel Global
var exibir_dados = 0

function negociacao_finalizada() {
    var negociacao_finalizada = (document.form.chkt_negociacao_finalizada.checked) ? 'S' : 'N'
    window.location = '<?=$PHP_SELF.'?id_orcamento_venda='.$id_orcamento_venda;?>&negociacao_finalizada='+negociacao_finalizada
}

function ativar_loading() {
    document.getElementById('listar_itens').innerHTML = "<img src='/erp/albafer/css/new_loading.gif'>"
    listar_itens()
}

function carregar_tela_itens() {
    window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'
}

function listar_itens() {
    ajax('/erp/albafer/modulo/vendas/orcamentos/itens/listar_itens.php?id_orcamento_venda=<?=$id_orcamento_venda;?>', 'listar_itens')
}

function alterar_item(posicao) {
    html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/orcamentos/itens/alterar.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&posicao='+posicao)
}

function imprimir() {
    var id_pais = eval('<?=$campos[0]['id_pais'];?>')
    if(id_pais == 31) {//Se o pa�s do Cliente for do Brasil, exibo o relat�rio Nacional  ...
        html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/orcamentos/itens/relatorio/relatorio.php?id_orcamento_venda=<?=$id_orcamento_venda;?>')
    }else {//Do contr�rio exibo o relat�rio de Exporta��o ...
        html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/orcamentos/itens/relatorio/relatorio_exportacao.php?id_orcamento_venda=<?=$id_orcamento_venda;?>')
    }
}

function atualizar_tudo() {
    var resposta    = confirm('DESEJA ATUALIZAR TODOS OS PRE�OS E CUSTOS DO OR�AMENTO ?')
    if(resposta == true) {//Significa que o Usu�rio deseja atualizar os Itens "PAs" com os Custos Atuais ...
        document.form.hdd_calcular_orcamentos_itens.value   = 'S'
        document.form.hdd_atualizar_tudo.value              = 'S'
        document.form.submit()
    }
}
</Script>
</head>
<body onload='listar_itens()'>
<form name='form' method='post' action=''>
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<input type='hidden' name='hdd_calcular_orcamentos_itens'>
<input type='hidden' name='hdd_atualizar_tudo' value='N'>
<?
/****************************Controle for�ado para o Or�amento n�o perder o par�metro do Filtro****************************/
//Isso s� acontecer� a partir da 2� vez em que essa Tela foi submetida ...
if(!empty($_POST['parametro_filtro'])) $parametro = $_POST['parametro_filtro'];
/**************************************************************************************************************************/
?>
<input type='hidden' name='hdd_parametro_filtro' value='<?=$parametro;?>'>
<table width='90%' border='0' cellpadding='0' cellspacing='0' align='center'>
    <tr>
        <td>
            <fieldset>
                <legend class='legend_contorno'>
                    <a href='/erp/albafer/modulo/vendas/pdt/pdt.php' title='PDT' class='link'>
                        <font color='#ff9900' size='-1'>PDT</font>
                    </a>
                    &nbsp;&nbsp;-&nbsp;&nbsp;
                    Or�amento N�: 
                    <font color='darkblue'>
                        <?=$id_orcamento_venda;?>
                    </font>
                    &nbsp;&nbsp;-&nbsp;&nbsp;
                    <a href='/erp/albafer/modulo/classes/pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$campos[0]['id_cliente'];?>' class='html5lightbox'>
                        <font color='darkblue' size='-1'>Pend�ncias</font>
                    </a>
                    &nbsp;&nbsp;-&nbsp;&nbsp;
                    <!--O nome desse par�metro tem que ser id_clientes, porque existe uma outra tela no Sistema 
                    que leva como par�metro v�rios clientes, da� por isso que eu acabei mantendo esse nome ...-->
                    <a href='/erp/albafer/modulo/vendas/apv/informacoes_apv.php?id_clientes=<?=$campos[0]['id_cliente'];?>&pop_up=1' class='html5lightbox'>
                        <font color='darkblue' size='-1'>APV do Cliente</font>
                    </a>
                    <?
                        $checked = ($campos[0]['negociacao_finalizada'] == 'S') ? 'checked' : '';
                    ?>
                    &nbsp;&nbsp;-
                    <input type='checkbox' name='chkt_negociacao_finalizada' value='S' title='Negocia��o Finalizada (N�o enviar e-mail de Mala Direta)' onclick='negociacao_finalizada()' id='label' class='checkbox' <?=$checked;?>>
                    <label for='label'>
                        Negocia��o Finalizada
                        <font color='red'>
                            (N�o enviar e-mail de Mala Direta)
                        </font>
                    </label>
                </legend>
                <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center'>
                    <tr align='left'>
                        <td colspan='2'>
                            <fieldset>
                                <legend>
                                    <span style="cursor: pointer">
                                        <b>DADOS DO CLIENTE</b>
                                        <a href='../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$campos[0]['id_cliente'];?>&nao_exibir_menu=1' class='html5lightbox'>
                                            <img src = '../../../../imagem/propriedades.png' title='Detalhes de Cliente' alt='Detalhes de Cliente' style='cursor:pointer' border='0'>
                                        </a>
                                    </span>
                                </legend>
                                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                                    <tr class='linhanormal'>
                                        <td bgcolor='#CECECE' colspan="2">
                                            <font color="#000000">
                                                <?=$campos[0]['cod_cliente'].' - '.$campos[0]['cliente'];?>
                                            </font>
                                            / <b>CONTATO:</b>
                                            <?
                                                    $sql = "SELECT nome 
                                                            FROM `clientes_contatos` 
                                                            WHERE `id_cliente_contato` = '".$campos[0]['id_cliente_contato']."' LIMIT 1 ";
                                                    $campos_contato = bancos::sql($sql);
                                                    echo $campos_contato[0]['nome'];
                                            ?>
                                            / <font color='darkred'><b>TIPO DE FATURAMENTO:</b></font>
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
                                            / <b>FORMA DE VENDA:</b>
                                            <?
                                                echo $prazo_faturamento;
                                                if($campos[0]['isento_st'] == 'S') echo '<font color="red" size="2"><b> (ISENTO DE SUBSTITUI��O TRIBUT�RIA)</b></font>';
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    <tr align='left' height='5'>
                        <td colspan='2'>
                            <fieldset>
                                <legend><b>DADOS DE OR�AMENTO</b>
                                    <img style='cursor:pointer' title='Alterar Dados de Or�amento' src="/erp/albafer/imagem/menu/alterar.png" width='17' height='15' border="0" onclick="html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/orcamentos/alterar_cabecalho.php?id_orcamento_venda=<?=$id_orcamento_venda;?>')"/>
                                </legend>
                                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                                    <tr class='linhanormal'>
                                        <td bgcolor='#CECECE' width='500'>
                                            <b>TIPO DE NOTA: </b>
                                            <?
                                                //Busca do Tipo de Pagamento da Nota Fiscal ...
                                                if($campos[0]['nota_sgd'] == 'S') {
                                                    echo 'SGD';
                                                }else {
                                                    echo 'NF';
                                                    echo '&nbsp;-&nbsp;';
                                                    echo $finalidade;
                                                }
                                            ?>
                                        </td>
                                        <td bgcolor='#CECECE' width='500'>
                                            <b>DESCONTO ICMS/SGD: </b>
                                            <?if(!empty($campos[0]['desc_icms_sqd_auto'])) echo 'SIM';?>
                                        </td>
                                    </tr>
                                    <tr class='linhanormal'>
                                        <td bgcolor='#CECECE'>
                                            <b>TIPO / C�DIGO DO SUFRAMA: </b>
                                            <?
                                                $tipo_suframa_vetor[1] = '�rea de Livre Com�rcio (ICMS/IPI)';
                                                $tipo_suframa_vetor[2] = 'Zona Franca de Manaus (ICMS/PIS/COFINS/IPI)';
                                                $tipo_suframa_vetor[3] = 'Amaz�nia Ocidental (IPI)';

                                                if(!empty($campos[0]['cod_suframa'])) {
                                                    echo '<font color="blue">'.$tipo_suframa_vetor[$campos[0]['tipo_suframa']].' / '.$campos[0]['cod_suframa'].'</font>';
                                                }else {
                                                    echo 'N�O EXISTE SUFRAMA PARA ESTE CLIENTE';
                                                }
                                            ?>
                                        </td>
                                        <td bgcolor='#CECECE'>
                                            <b>DATA DE EMISS�O: </b><?=$campos[0]['data_emissao'];?>
                                        </td>
                                    </tr>
                                    <tr class='linhanormal'>
                                        <td>
                                            <b>PRAZO (A): </b>
                                            <?
                                                if($campos[0]['prazo_a'] == 0) {
                                                    echo '� vista';
                                                }else {
                                                    echo $campos[0]['prazo_a'].' DIAS';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <b>VENC: </b><?=data::adicionar_data_hora($campos[0]['data_emissao'], $campos[0]['prazo_a']);?>
                                        </td>
                                    </tr>
                                    <tr class='linhanormal'>
                                        <td>
                                            <b>PRAZO (B): </b>
                                            <?
                                                if($campos[0]['prazo_b'] != 0) {
                                                    echo $campos[0]['prazo_b'].' DDL';
                                                }else {
                                                    echo '&nbsp;';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <b>VENC: </b>
                                            <?
                                                if($campos[0]['prazo_b'] != 0) {
                                                    echo data::adicionar_data_hora($campos[0]['data_emissao'], $campos[0]['prazo_b']);
                                                }else {
                                                    echo '&nbsp;';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr class='linhanormal'>
                                        <td>
                                            <b>PRAZO (C): </b>
                                            <?
                                                if($campos[0]['prazo_c'] != 0) {
                                                    echo $campos[0]['prazo_c'].' DDL';
                                                }else {
                                                    echo '&nbsp;';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <b>VENC: </b>
                                            <?
                                                if($campos[0]['prazo_c'] != 0) {
                                                    echo data::adicionar_data_hora($campos[0]['data_emissao'], $campos[0]['prazo_c']);
                                                }else {
                                                    echo '&nbsp;';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr class='linhanormal'>
                                        <td>
                                            <b>PRAZO (D): </b>
                                            <?
                                                if($campos[0]['prazo_d'] != 0) {
                                                    echo $campos[0]['prazo_d'].' DDL';
                                                }else {
                                                    echo '&nbsp;';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <b>VENC: </b>
                                            <?
                                                if($campos[0]['prazo_d'] != 0) {
                                                    echo data::adicionar_data_hora($campos[0]['data_emissao'], $campos[0]['prazo_d']);
                                                }else {
                                                    echo '&nbsp;';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr class='linhanormal'>
                                        <td bgcolor='#CECECE'>
                                            <b>PRAZO M�DIO: </b><?=$campos[0]['prazo_medio'];?>
                                        </td>
                                        <td bgcolor='#CECECE'>
                                        <?
                                            $vetor_dados_gerais     = vendas::dados_gerais_orcamento($id_orcamento_venda);
                                            $data_validade_orc      = $vetor_dados_gerais['data_validade_orc'];
                                            $dias_validade          = $vetor_dados_gerais['dias_validade'];
                                        ?>
                                            <font color='darkblue'>
                                                <b>DATA DE VALIDADE: </b><?=data::datetodata($data_validade_orc, '/');?> - <font color='darkgreen'><b><?=$dias_validade;?> DIAS</b></font>
                                                <?
                                                    if($data_validade_orc < date('Y-m-d')) echo '<font color="red" size="2"><b> (OR�AMENTO FORA DA DATA DE VALIDADE).</b></font>';
                                                ?>
                                            </font>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    <!--****************************Follow-UPs***************************-->
                    <tr align='center'>
                        <td colspan='2'>
                            <iframe name='detalhes' id='detalhes' src = '../../../classes/follow_ups/detalhes.php?identificacao=<?=$id_orcamento_venda;?>&origem=1' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
                        </td>
                    </tr>
                    <!--*****************************************************************-->
<?
                    /**********************************************************************/
                    /*************************Observa��o do Cliente************************/
                    /**********************************************************************/
                    //Busco a observa��o do Cliente que foi registrada no Follow-Up ...
                    $sql = "SELECT `observacao` 
                            FROM `follow_ups` 
                            WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
                            AND `origem` = '15' LIMIT 1 ";
                    $campos_follow_up = bancos::sql($sql);
                    if(count($campos_follow_up) == 1) {
?>
                    <tr class='linhanormal' align='center'>
                        <td colspan='6' bgcolor='yellow'>
                            <img src = '../../../../imagem/exclamacao.gif' title='Observa��o do Pedido' alt='Observa��o do Pedido' height='30' border='0'>
                            <font size='3'>
                                <b>Obs. do Cliente: 
                                <font color='blue'>
                                    <?=$campos_follow_up[0]['observacao'];?>
                                </font>
                                </b>
                            </font>
                            <img src = '../../../../imagem/exclamacao.gif' title='Observa��o do Pedido' alt='Observa��o do Pedido' height='30' border='0'>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='6'>
                            <br/>
                        </td>
                    </tr>
<?
                    }
                    /**********************************************************************/
?>
                    <tr class='linha_e' valign='top'>
                        <td colspan='6'>
                        <?
                            if($campos[0]['tipo_frete'] == '') {//Enquanto n�o preencher o Tipo de Frete, n�o ser� poss�vel utilizar nenhuma op��o abaixo ...
                        ?>
                            <center>
                                <!--<marquee behavior='alternate' scrolldelay='200'>
                                    <font class='erro'>
                                        SELECIONE UM TIPO DE FRETE P/ CORRIGIR A(S) MARGEM(NS) DE LUCRO / COMISS�O(�ES)
                                    </font>
                                </marquee>-->
                                <font class='erro'>
                                    SELECIONE UM TIPO DE FRETE P/ CORRIGIR A(S) MARGEM(NS) DE LUCRO / COMISS�O(�ES)
                                </font>
                            </center>
                            <br/>
                        <?
                            }
                        ?>
                            <fieldset>
                                <legend>
                                    <b>ITENS OR&Ccedil;ADOS</b>
                                    &nbsp;-&nbsp;
                                    <!--Esse par�metro -> "veio_itens_orcamento" � um Macete p/ o Sistema n�o ficar nessa Tela de Itens 
                                    quando for encontrado apenas 1 �nico registro no Filtro ...-->
                                    <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class='botao' onclick="window.location = 'consultar.php<?=$parametro;?>&veio_itens_orcamento=1'">
                                    <?
                                        if(strtoupper($campos[0]['congelar']) == 'S') {//Se o Or�amento estiver congelado ...
//Aqui se a Data de Validade do Or�amento for maior do que a Data Atual, eu ainda posso Imprimir o Or�amento ...
                                            if($data_validade_orc >= date('Y-m-d')) {
                                                $botao_imprimir = "class='botao' onclick='imprimir()' ";
                                            }else {
                                                $botao_imprimir = "class='disabled' onclick='alert(".'"OR�AMENTO FORA DA DATA DE VALIDADE !"'.")' ";
                                            }
                                            $botoes_acao    = "class='disabled' onclick='alert(".'"OR�AMENTO CONGELADO !"'.")' disabled ";
                                            $disabled       = 'disabled';//N�o pode marcar / desmarcar o Checkbox ...
                                        }else {//Caso n�o esteja congelado, ent�o posso incluir + Itens ...
                                            $botoes_acao    = "class='botao' ";
                                            $botao_imprimir = "class='disabled' disabled ";
                                            $disabled       = '';//Pode marcar / desmarcar o Checkbox ...
                                        }
                                    ?>
                                    <input type='button' name='cmd_incluir_orcar' value='Incluir / Or&ccedil;ar' title='Incluir / Or&ccedil;ar' onclick="html5Lightbox.showLightbox(7, 'incluir_lote.php?id_orcamento_venda=<?=$id_orcamento_venda;?>')" <?=$botoes_acao;?>>
                                    <input type='button' name='cmd_excluir_item' value='Excluir Item(ns)' title='Excluir Item(ns)' onclick="html5Lightbox.showLightbox(7, 'excluir_itens.php?id_orcamento_venda=<?=$id_orcamento_venda;?>')" <?=$botoes_acao;?>>
                                    <input type='button' name='cmd_outras' value='Outras Op&ccedil;&otilde;es' title='Outras Op&ccedil;&otilde;es' onclick="html5Lightbox.showLightbox(7, 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>')" class='botao'>
                                    <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' <?=$botao_imprimir;?>>
                                    <input type='button' name='cmd_dados_comissao' value='Dados de Comiss�o' title='Dados de Comiss�o' onclick="html5Lightbox.showLightbox(7, '../../representante/comissoes/margem_lucro_nova/comissao.php?pop_up=1')" style='color:red' class='botao'>
                                    <?
                                        //S� mostra esse bot�o e checkbox p/ os usu�rios do Rivaldo 27, Roberto 62, Fabio 64, Wilson Diretor 68, D�rcio 98 'pq programa' e Nishimura 136 ...
                                        if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
                                    ?>
                                    <input type='button' name='cmd_imprimir_margem_lucro' value='Imprimir Margem Lucro' title='Imprimir Margem Lucro' onclick="html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/orcamentos/itens/relatorio/relatorio.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&exibir_margem_lucro=1')" class='botao'>
                                    <?
                                            if($campos[0]['comprar_como_export'] == 'S') echo " <font color='darkblue'>(COMPRAR COMO EXPORT)</font>";
                                        }
                                    ?>
                                    <input type='button' name='cmd_total_produtos_mlg_por_divisao' value='Total Produtos MLG por Divis&atilde;o' title='Total Produtos MLG por Divis&atilde;o' onclick="html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/total_produtos_mlg_por_divisao.php?id_orcamento_venda=<?=$id_orcamento_venda;?>')" style='color:black' class='botao'>
                                    <input type='button' name='cmd_atualizar_tudo' value='Atualizar Tudo' title='Atualizar Tudo' onclick='atualizar_tudo()' style='color:red' <?=$botoes_acao;?>>
                                </legend>
                                <div id='listar_itens' align='center'>
                                    <img src='/erp/albafer/css/new_loading.gif'>
                                </div>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
    /*Se o Or�amento est� com sua Data fora do Prazo de Validade e est� descongelado pergunto p/ o usu�rio se ele deseja 
    atualizar os Custos dos Itens "PAs" desse Or�amento ...*/
    if($data_validade_orc < date('Y-m-d') && strtoupper($campos[0]['congelar']) == 'N') {
?>
        <Script Language = 'JavaScript'>
            var resposta    = confirm('ESTE OR�AMENTO ESTA FORA DA DATA DE VALIDADE ! DESEJA ATUALIZAR OS CUSTOS ?')
            if(resposta == true) {//Significa que o Usu�rio deseja atualizar os Itens "PAs" com os Custos Atuais ...
                document.form.hdd_calcular_orcamentos_itens.value = 'S'
                document.form.submit()
            }
        </Script>
<?
    }
?>