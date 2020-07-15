<?
require('../../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../../lib/menu/menu.php');//Se essa tela não foi aberta como sendo Pop-UP então eu exibo o menu ...
require('../../../../lib/calculos.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
session_start('funcionarios');
/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) {
    segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');
}
$mensagem[1] = 'ITEM EXCLUIDO COM SUCESSO !';
$mensagem[2] = 'NENHUM ITEM DE NOTA FISCAL PODE SER EXCLUÍDO !\nPORQUE ESTA NOTA FISCAL ESTÁ TRAVADA !';

//Aqui é a Busca da Variável de Vendas
$fator_desc_maximo_venda = genericas::variavel(19);

//Busca de alguns dados através do campo $id_nf_outra ...
$sql = "SELECT c.`id_cliente`, c.`cod_cliente`, c.`id_pais`, c.`razaosocial`, c.`id_uf`, c.`credito`, c.`trading`, nfso.* 
        FROM `nfs_outras` nfso 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
        WHERE nfso.`id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
$campos                 = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
$id_empresa_nota        = $campos[0]['id_empresa'];
$id_transportadora      = $campos[0]['id_transportadora'];
$id_nf_num_nota         = $campos[0]['id_nf_num_nota'];

if($campos[0]['finalidade'] == 'C') {//Consumo ...
    $finalidade = 'C';
}else if($campos[0]['finalidade'] == 'I') {//Industrialização ...
    $finalidade = 'I';
}else {//Revenda ...
    $finalidade = 'R';
}

$ajuste_total_produtos  = $campos[0]['ajuste_total_produtos'];
$ajuste_ipi             = $campos[0]['ajuste_ipi'];
$ajuste_icms            = $campos[0]['ajuste_icms'];
$data_emissao_nf        = $campos[0]['data_emissao'];
$id_pais                = $campos[0]['id_pais'];
$trading                = $campos[0]['trading'];
$id_cliente             = $campos[0]['id_cliente'];
$id_cfop                = $campos[0]['id_cfop'];
$cod_cliente            = $campos[0]['cod_cliente'];
$razao_social           = $campos[0]['razaosocial'];
$credito                = $campos[0]['credito'];
$observacao_cliente     = $campos[0]['observacao_cliente'];
$id_uf_cliente          = $campos[0]['id_uf'];

if($campos[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[0]['vencimento4'];
if($campos[0]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[0]['vencimento3'].$prazo_faturamento;
if($campos[0]['vencimento2'] > 0) {
    $prazo_faturamento = $campos[0]['vencimento1'].'/'.$campos[0]['vencimento2'].$prazo_faturamento;
}else {
    $prazo_faturamento = ($campos[0]['vencimento1'] == 0) ? 'À vista' : $campos[0]['vencimento1'];
}
$total_icms             = number_format($campos[0]['total_icms'], 2, ',', '.');
$observacao_nf          = $campos[0]['observacao'];
$status                 = $campos[0]['status'];

//Aqui verifica o Tipo de Nota
$vetor_nota_sgd         = genericas::nota_sgd($id_empresa_nota);
$tipo_nfe_nfs           = $campos[0]['tipo_nfe_nfs'];

//Aqui é a verifica se esta Nota é de Saída ou Entrada
if($campos[0]['tipo_nfe_nfs'] == 'S') {
    $rotulo_tipo_nfe_nfs = ' - Saída';
}else {
    $rotulo_tipo_nfe_nfs = ' - Entrada';
}
$prazo_faturamento.= $vetor_nota_sgd['tipo_nota'].$rotulo_tipo_nfe_nfs;
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = 'tabela_itens_radio.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho'>
        <td align='left'>
            <a href = "javascript:nova_janela('../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$id_cliente;?>&nao_exibir_menu=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <font color='yellow' size='-1'>
                    Cliente:
                    <font color='#FFFFFF'>
                        <?=$cod_cliente.' - '.$razao_social;?>
                    </font>
                </font>
                <img src = '../../../../imagem/propriedades.png' title='Detalhes de Cliente' alt='Detalhes de Cliente' style='cursor:pointer' border='0'>
            </a>
            <font color='#49D2FF' size='2'>/ Cr&eacute;dito:</font>
            <font color='#FFFFFF' size='2'>
                    <?=financeiros::controle_credito($id_cliente);?>
            </font>
            <font color='#49D2FF' size='2'>/ Forma de Venda:</font>
            <font color='#FFFFFF' size='2'>
                <?=$prazo_faturamento;?>
            </font>
        </td>
    </tr>
</table>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td>
            <!--*********Passo o parâmetro cmb_origem=15 para que no início só carregue nessa parte 
            de Follow-Ups dados que são pertinentes a parte de cadastro*********-->
            <iframe name='detalhes' id='detalhes' src = '../../../classes/follow_ups/detalhes.php?id_cliente=<?=$id_cliente;?>&origem=15&cmb_origem=15' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#CECECE'>
            <font size='2'>
                <b>Obs NF: 
                <font color='red'>
                    <?=$observacao_nf;?>
                </font>
                </b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#CECECE'>
            <font size='2'>
                <b>Texto da NF: 
                <font color='red'>
                    <?=$campos[0]['texto_nf'];?>
                </font>
                </b>
            </font>
        </td>
    </tr>
<?
    /**********************************************************************/
    /*************************Observação do Cliente************************/
    /**********************************************************************/
    //Busco a observação do Cliente que foi registrada no Follow-Up ...
    $sql = "SELECT `observacao` 
            FROM `follow_ups` 
            WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
            AND `origem` = '15' LIMIT 1 ";
    $campos_follow_up = bancos::sql($sql);
    if(count($campos_follow_up) == 1) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='6' bgcolor='yellow'>
            <img src = '../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
            <font size='3'>
                <b>Obs. do Cliente: 
                <font color='blue'>
                    <?=$campos_follow_up[0]['observacao'];?>
                </font>
                </b>
            </font>
            <img src = '../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
        </td>
    </tr>
<?
    }
    /**********************************************************************/
?>
</table>
<?
/**************************************************************************************/
/*****************Função que retorna todos os Valores referentes a NF******************/
/**************************************************************************************/
    $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf_outra, 'NFO');
/*******************************************************************************************/
/**************************************************************************************/
//Aqui começa a segunda parte, a parte em q calcula e exibe os itens
    $sql = "SELECT c.`id_cliente`, c.`id_uf`, nfsoi.`id_nf_outra_item`, nfsoi.`id_produto_acabado`, nfsoi.`id_produto_insumo`, 
            nfsoi.`referencia`, nfsoi.`discriminacao`, nfsoi.`id_classific_fiscal`, nfsoi.`origem_mercadoria`, nfsoi.`situacao_tributaria`, 
            nfsoi.`qtde`, nfsoi.`valor_unitario`, nfsoi.`peso_unitario`, nfsoi.`ipi` AS ipi_perc_item_current, nfsoi.`icms`, 
            nfsoi.`reducao`, nfsoi.`imposto_importacao`, nfsoi.`valor_cif`, nfsoi.`bc_icms_item`, nfsoi.`despesas_aduaneiras`, 
            nfsoi.`observacao`, u.`sigla` 
            FROM `nfs_outras` nfso 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
            INNER JOIN `nfs_outras_itens` nfsoi ON nfsoi.`id_nf_outra` = nfso.`id_nf_outra` 
            INNER JOIN `unidades` u ON u.`id_unidade` = nfsoi.`id_unidade` 
            WHERE nfso.`id_nf_outra` = '$id_nf_outra' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
//Verifica se tem pelo menos um item na Nota Fiscal
    if($linhas > 0) {
//Se o Status da Nota Fiscal = "Em Aberto", então eu apresento essa linha ...
        if($status == 0) {
?>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='13' bgcolor='red'>
            <font color='white' size='4'>
                <b>Nota não liberada p/ faturamento !!!</b>
            </font>
        </td>
    </tr>
</table>
<?
        }
?>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhadestaque' align='center'>
        <td colspan='13'>
            Itens da Nota Fiscal N.º&nbsp;
            <font color='#5DECFF' size='-1'>
                <?=faturamentos::buscar_numero_nf($id_nf_outra, 'O');?>
            </font>
<?
        $sql = "SELECT `nomefantasia` 
                FROM `empresas` 
                WHERE `id_empresa` = '$id_empresa_nota' LIMIT 1 ";
        $campos_empresa = bancos::sql($sql);
//Significa q estava selecionada a Albafer
        echo ' - Empresa: <font color="#5DECFF">'.$campos_empresa[0]['nomefantasia'].'</font>';
//Aki busca a transportadora
        $sql = "SELECT `nome` 
                FROM `transportadoras` 
                WHERE `id_transportadora` = '$id_transportadora' LIMIT 1 ";
        $campos_transportadora = bancos::sql($sql);
        echo ' - Transportadora: <font color="#5DECFF">'.$campos_transportadora[0]['nome'].' </font>';

//Aqui eu verifico se a NF possui uma Carta de Correção ...
        $sql = "SELECT `id_carta_correcao` 
                FROM `cartas_correcoes` 
                WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
        $campos_carta_correcao = bancos::sql($sql);
        if(count($campos_carta_correcao) == 1) {
?>
            &nbsp;-&nbsp;
            <a href="javascript:nova_janela('../../../classes/nf_carta_correcao/itens/relatorio/imprimir.php?id_carta_correcao=<?=$campos_carta_correcao[0]['id_carta_correcao'];?>', 'ITENS', 'F')" class="link">
                <img src = '../../../../imagem/carta.jpeg' title='Detalhes de Carta de Correção' alt='Detalhes de Carta de Correção' height='20' border='1'>
                <font color='yellow' size='-1'>
                    Carta de Correção
                </font>
            </a>
<?
        }
?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Item</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Quantidade <br/>Faturada</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Produto' style='cursor:help'>
                <b>Produto</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Informa&ccedil;&otilde;es' style='cursor:help'>
                <b>Info</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Peso/Peça em (Kg)' style='cursor:help'>
                <b>Peso/<br/>Pç(Kg)</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Peso do Lote em (Kg)' style='cursor:help'>
                <b>P. L.<br/>(Kg)</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Observação</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Preço L. Final <?=$rotulo_titulo;?> R$' style='cursor:help'>
                <b>Preço L.<br/> Final <?=$rotulo_titulo;?> R$</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='IPI %' style='cursor:help'>
                <b>IPI %</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='IPI R$' style='cursor:help'>
                <b>IPI R$</b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <font title='ICMS %' style='cursor:help'>
                <b>ICMS %</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Total <?=$rotulo_titulo;?> R$ Lote' style='cursor:help'>
                <b>Total Lote <br/><?=$rotulo_titulo;?> R$</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <font title='Alíquota' style='cursor:help'>
                <b>Alíq</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Redução B. C' style='cursor:help'>
                <b>Red</b>
            </font>
        </td>
    </tr>
<?
        //Esse vetor tem a idéia de armazenas todas as Classificações Fiscais existentes em todos os Itens das Notas Fiscais ...
        $vetor_classific_fiscal = array();
        for($i = 0; $i < $linhas; $i++) {
            //Essas variáveis serão utilizadas mais abaixo ...
            $dados_produto      = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], $id_uf_cliente, $id_cliente, $id_empresa_nota, $finalidade, 'S', 0, $id_nf_outra);
/*Significa q estou acessando essa tela como um Pop-Up, então preciso estar desabilitando os comandos da
linha porque da erro de Path de JS*/
            if($pop_up == 1) {
?>
    <tr class='linhanormal'>
<?
            }else {
?>
    <tr class='linhanormal' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
<?
            }
?>
        <td align='center'>
<?
/*Significa q estou acessando essa tela como um Pop-Up, então preciso estar desabilitando os comandos da
linha porque da erro de Path de JS*/
            if($pop_up == 1) {
?>
            <input type='radio' name='opt_item' value="<?=$campos[$i]['id_nf_outra_item'];?>">
<?
            }else {
?>
            <input type='radio' name='opt_item' value="<?=$campos[$i]['id_nf_outra_item'];?>" onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8')">
<?
            }
?>
        </td>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                if(strstr($campos[$i]['qtde'], '.') != '.00') {//Significa que o decimal do N.º é Dif. de Zero 
                    echo number_format($campos[$i]['qtde'], 2, ',', '.');
                }else {//Significa que o N.º é Inteiro ...
                    echo (integer)$campos[$i]['qtde'];
                }
            ?>
            </font>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['id_produto_acabado'])) {//Se foi cadastrado o PA ...
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
            }else if(!empty($campos[$i]['id_produto_insumo'])) {//Se foi cadastrado o PI ...
                $sql = "SELECT CONCAT(u.`sigla`, ' * ', g.`referencia`, ' * ', pi.`discriminacao`) AS dados_produto 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE pi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_pi = bancos::sql($sql);
                echo $campos_pi[0]['dados_produto'];
            }else if(!empty($campos[$i]['referencia']) || !empty($campos[$i]['discriminacao'])) {//Se foi cadastrado uma Referência ou Discriminação ...
                echo $campos[$i]['sigla'].' * '.$campos[$i]['referencia'].' * '.$campos[$i]['discriminacao'];
            }
        ?>
            <font title='Valor do CIF: <?=number_format($campos[$i]['valor_cif'], 2, ',', '.');?>' color='red' style='cursor:help'>
                <b>(C) </b>
            </font>
            <font title='Base de Cálculo: <?=number_format($campos[$i]['bc_icms_item'], 2, ',', '.');?>' color='darkblue' style='cursor:help'>
                <b>(BC) </b>
            </font>
            <font title='Despesas Aduaneiras: <?=number_format($campos[$i]['despesas_aduaneiras'], 2, ',', '.');?>' color='darkgreen' style='cursor:help'>
                <b>(DA) </b>
            </font>
        </td>
        <td align='center'>
        <?
            if($id_empresa_nota == 3) {//Se a Empresa = 'K2' sempre a OF do PA será Industrial ...
                echo '<font color="red" title="Industrialização (c/ IPI)" style="cursor:help"><b>I</b></font>';
            }else {
                //Só existe Operação de Faturamento p/ PA ...
                if(!empty($campos[$i]['id_produto_acabado'])) {//Se foi cadastrado um PA ...
                    $sql = "SELECT operacao 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
                    $campos_operacao = bancos::sql($sql);
                    if($campos_operacao[0]['operacao'] == 0) {//Industrialização (c/ IPI)
                        echo '<font color="red" title="Industrialização (c/ IPI)" style="cursor:help"><b>I</b></font>';
                    }else {//Revenda (s/ IPI)
                        echo '<font color="red" title="Revenda (s/ IPI)" style="cursor:help"><b>R</b></font>';
                    }
                }else if(!empty($campos[$i]['id_produto_insumo'])) {//Se foi um PI, trata tudo como se fosse Ind ...
                    echo '<font color="red" title="Industrialização (c/ IPI)" style="cursor:help"><b>I</b></font>';
                }else if(!empty($campos[$i]['discriminacao'])) {//Se for Manual, não existe OF ...
                    echo '-';
                }
            }

            if(count($vetor_classific_fiscal) == 0) {//O vetor ainda está vazio ...
//Insere no vetor o Elemento corrente ...
                array_push($vetor_classific_fiscal, $campos[$i]['id_classific_fiscal']);
            }else {//Já existe pelo menos 1 elemento no vetor ...
//Aqui eu sempre zero a variável p/ não herdar valores do loop anterior ...
                $achou = 0;
                for($j = 0; $j < count($vetor_classific_fiscal); $j++) {
//Comparo todas as Classificações Fiscais do Vetor, com a Classificação Corrente ...
                    if($vetor_classific_fiscal[$j] == $campos[$i]['id_classific_fiscal']) {//Significa que já existe ...
                        $achou = 1;
                    }
                }
/*Significa que depois de ter vasculhado todo o Vetor, não achou a Classificação corrente, 
sendo assim essa Classificação é incrementada no Vetor ...*/
                if($achou == 0) array_push($vetor_classific_fiscal, $campos[$i]['id_classific_fiscal']);
            }
            //Aqui eu apresento a Classificação Fiscal do Item da NF ...
            $sql = "SELECT `classific_fiscal` 
                    FROM `classific_fiscais` 
                    WHERE `id_classific_fiscal` = '".$campos[$i]['id_classific_fiscal']."' LIMIT 1 ";
            $campos_classif_fiscal = bancos::sql($sql);
?>
            <font title='Classifica&ccedil;&atilde;o Fiscal => <?=$campos_classif_fiscal[0]['classific_fiscal'];?>' style='cursor:help'>
                <br/><?=$campos[$i]['id_classific_fiscal'];?>
            </font>
            <font title='Origem / Situa&ccedil;&atilde;o Tribut&aacute;ria' style='cursor:help'>
                <br/>CST=<?=$campos[$i]['origem_mercadoria'].$campos[$i]['situacao_tributaria'];?>
            </font>
            <font>
                <br/>CFOP=<?=$dados_produto['cfop'];?>
            </font>
        </td>
        <td align='center'>
            <?=number_format($campos[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
        <td align='center'>
        <?
            $peso_lote_item_current_kg = $campos[$i]['peso_unitario'] * $campos[$i]['qtde'];
/*Cálculo p/ achar o "Frete + Desp. Acessórias" do Item corrente, em cima do 
"Frete Total em R$ + Despesas Acessórias em R$", achar o "Frete Individual + 
Despesas Acessórias Individual", achar a sua fatia dentro do Total ...*/
            $frete_desp_acessorias_item_current = (($calculo_total_impostos['valor_frete'] + $calculo_total_impostos['outras_despesas_acessorias']) * $peso_lote_item_current_kg);
            if($calculo_total_impostos['peso_lote_total_kg'] > 0) $frete_desp_acessorias_item_current /= $calculo_total_impostos['peso_lote_total_kg'];
            $frete_desp_acessorias_item_current = round(round($frete_desp_acessorias_item_current, 3), 2);
            echo "<font title='Frete + Desp. Acessórias Individual: ".number_format($frete_desp_acessorias_item_current, 2, ',', '.')."' style='cursor:help'>".number_format($peso_lote_item_current_kg, 3, ',', '.')."</font>";
        ?>
        </td>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                if(empty($campos[$i]['observacao'])) {
                    echo '&nbsp';
                }else {
                    echo "<img width='28' height='23' title='".$campos[$i]['observacao']."' style='cursor:help;' src='../../../../imagem/olho.jpg'>";
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_unitario'], 2, ',', '.');?>
        </td>
<?
/************************************************************************************************************/
/********************************** CFOP 3.101 e 3.102- Compra p/ Industrialização**********************************/
/************************************************************************************************************/
//Somente nessa CFOP que os cálculos são totalmente diferentes ...
            if($id_cfop == 161 || $id_cfop == 231) {
                $ipi_perc_item_current = $campos[$i]['ipi_perc_item_current'];
                $ii_item_current_rs = round($campos[$i]['valor_cif'] * $campos[$i]['imposto_importacao'] / 100);
                $ipi_item_current_rs = round((($campos[$i]['valor_cif'] + $ii_item_current_rs) * $ipi_perc_item_current / 100), 2);
                $icms_item_current_rs = round(($campos[$i]['bc_icms_item'] * $campos[$i]['icms'] / 100) * ((100 - $campos[$i]['reducao']) / 100), 2);
                $total_parcial = round($icms_item_current_rs + $ipi_item_current_rs + $ii_item_current_rs + $campos[$i]['valor_cif'], 2);
                $tot_mercadoria_item = round($total_parcial - $icms_item_current_rs - $ipi_item_current_rs, 2);
?>
        <td align='right'>
            <?=number_format($ipi_perc_item_current, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($ipi_item_current_rs, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['icms'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
                if($campos[$i]['reducao'] == '0.00') {//Se não existir Redução para o Item corrente ...
                    echo '&nbsp;';
                }else {
                    echo number_format($campos[$i]['reducao'], 2, ',', '.');
                }
        ?>
        </td>
        <td align='right'>
            <?=number_format($tot_mercadoria_item + $ajuste_total_produtos, 2, ',', '.');?>
        </td>
<?
/************************************************************************************************************/
//Nas demais CFOPs, os cálculos são normais ...
            }else {
                $total_parcial = $campos[$i]['qtde'] * $campos[$i]['valor_unitario'];
?>
        <td align='right'>
        <?
                if($campos[$i]['ipi_perc_item_current'] == '0.00') {//Se não existir IPI para o Item corrente ...
                    $ipi_item_current_rs = 0;
                    $ipi_frete_desp_aces_item_current_rs = 0;
                    echo "<font title='IPI do Frete + Desp. Acessórias Individual: R$ ".number_format($ipi_frete_desp_aces_item_current_rs, 2, ',', '.')."' style='cursor:help'>S/IPI</font>";
                }else {//Existe algum IPI ...	
                    $ipi_item_current_rs = round(($campos[$i]['ipi_perc_item_current'] / 100) * $total_parcial, 2);//Cálculo o Valor do IPI em R$ ...
                    $total_ipi_item_rs+= $ipi_item_current_rs;
                    $ipi_frete_desp_aces_item_current_rs = ($campos[$i]['ipi_perc_item_current'] / 100) * $frete_desp_acessorias_item_current;
                    echo "<font title='IPI do Frete + Desp. Acessórias Individual: R$ ".number_format($ipi_frete_desp_aces_item_current_rs, 2, ',', '.')."' style='cursor:help'>".number_format($campos[$i]['ipi_perc_item_current'], 2, ',', '.')."</font>";
                }
//Será apresentado mais abaixo ...
                $ipi_frete_desp_aces_todos_itens+= $ipi_frete_desp_aces_item_current_rs;
        ?>
        </td>
        <td align='right'>
        <?
                if($ipi_item_current_rs == 0) {
                    echo '&nbsp;';
                }else {
                    echo number_format($ipi_item_current_rs, 2, ',', '.');
                }
        ?>
        </td>
        <td align='right'>
        <?
                if($campos[$i]['icms'] == '0.00') {//Se não existir ICMS para o Item corrente ...
                    $icms_frete_desp_aces_item_current = 0;
                    echo "<font title='ICMS do Frete + Desp. Acessórias Individual: R$ ".number_format($campos[$i]['icms'], 2, ',', '.')."' style='cursor:help'>&nbsp;</font>";
                }else {
    //Quando a Finalidade da NF = Consumo, tem de somar o Valor de IPI do Frete do Item no cálculo do Icms do Frete ...
                    if($finalidade == 'C') {
                        $icms_frete_desp_aces_item_current_rs = (($frete_desp_acessorias_item_current + $ipi_frete_desp_aces_item_current_rs) * ($campos[$i]['icms'] / 100));
                    }else {//Revenda não precisa ...
                        $icms_frete_desp_aces_item_current_rs = (($frete_desp_acessorias_item_current) * ($campos[$i]['icms'] / 100));
                    }
    //Obs: Se existir redução, então eu preciso aplicar está no ICMS do Frete + DA do Item ...				
                    if($campos[$i]['reducao'] != '0.00') {
                        $icms_frete_desp_aces_item_current_rs*= (100 - $campos[$i]['reducao']) / 100;
                    }
                    echo "<font title='ICMS do Frete + Desp. Acessórias Individual: R$ ".number_format($icms_frete_desp_aces_item_current_rs, 2, ',', '.')."' style='cursor:help'>".number_format($campos[$i]['icms'], 2, ',', '.')."</font>";
                }
    //Será apresentado mais abaixo ...
                $icms_frete_desp_aces_todos_itens+= $icms_frete_desp_aces_item_current_rs;
        ?>
        </td>
        <td align='right'>
        <?
                if($campos[$i]['reducao'] == '0.00') {//Se não existir Redução para o Item corrente ...
                    echo '&nbsp;';
                }else {
                    echo number_format($campos[$i]['reducao'], 2, ',', '.');
                }
        ?>
        </td>
        <td align='right'>
            <?=number_format($total_parcial, 2, ',', '.');?>
        </td>
    </tr>
<?
            }
/************************************************************************************************************/
        }
?>
    <tr class='linhadestaque'>
        <td colspan='13' align='left'>
            <font color='#5DECFF'>
                Total Peso NF:
            </font>
            <?
                $peso_nf = faturamentos::calculo_peso_outras_nfs($id_nf_outra);
                echo number_format($peso_nf['total_peso_nf'], 4, ',', '.');
            ?>
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='0' align='center'>
<?
/*********************************************************************************************/
/**********************************************OS*********************************************/
/*********************************************************************************************/
//Verifico se existe uma OS importada nessa NF ...
    $sql = "SELECT `id_os` 
            FROM `oss` 
            WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
    $campos_os = bancos::sql($sql);
    if(count($campos_os) == 1) {
?>
    <tr>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            OS Importada(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='6'>
            <font size='-1'>
                <b>A OS N.º 
                    <font color='red'>
                        <?=$campos_os[0]['id_os'];?>
                    </font> 
                    está importada nesta NF.
                </b>
            </font>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
    </tr>
<?
    }
/*********************************************************************************************/
?>	
    <tr></tr>
    <tr></tr>
    <tr class='linhadestaque'>
        <td colspan='6'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='yellow' size='-5'>
                CÁLCULO DO IMPOSTO
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>BASE DE CÁLCULO DO ICMS: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['base_calculo_icms'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO ICMS: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_icms'], 2, ',', '.');?>
            </font>
        </td>
        <td colspan='2'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>BASE DE CÁLC. DO ICMS ST: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['base_calculo_icms_st'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO ICMS ST: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR TOTAL DOS PRODUTOS: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO FRETE: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_frete'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO SEGURO: </font>
                <br/>R$ 0,00
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>DESCONTO: </font>
                <br/>R$ <?=number_format(abs($calculo_total_impostos['desconto']), 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>OUTRAS DESPESAS ACESSÓRIAS: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['outras_despesas_acessorias'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR DO IPI: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>VALOR TOTAL DA NOTA: </font>
                <br/>R$ <?=number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class="iframe" onClick="showHide('detalhes_calculo'); return false">
        <td colspan="14" height="21" align='left'>
            <font color='yellow' size="2">&nbsp;Detalhes: </font>
        </td>
    </tr>
    <tr>
        <td colspan='14'>
            <iframe src="detalhes_calculo.php?id_nf_outra=<?=$id_nf_outra;?>&id_classific_fiscais=<?=implode(',', $vetor_classific_fiscal);?>&nao_verificar_sessao=<?=$nao_verificar_sessao;?>" name="detalhes_calculo" id="detalhes_calculo" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" width="100%" scrolling="auto"></iframe>
        </td>
    </tr>
</table>
<?
    //Se existir Frete na NF, então eu faço uma verificação de modo que garanta segurança no cálculos dos Impostos de ICMS ...
    if($calculo_total_impostos['valor_frete'] > 0) {
        //Verifico se existe pelo menos 1 Item que está sem Peso Unitário ...
        $sql = "SELECT id_nf_outra_item 
                FROM `nfs_outras_itens` 
                WHERE `id_nf_outra` = '$id_nf_outra' 
                AND `peso_unitario` = '0.0000' LIMIT 1 ";
	$campos_peso_unitario = bancos::sql($sql);
	if(count($campos_peso_unitario[0]['id_nf_outra_item']) == 1) {
?>
    <p class='piscar'>
        <font color='red'>
            &nbsp;&nbsp;&nbsp;*** EXISTE(M) ITEM(NS) QUE ESTÃO SEM PESO UNITÁRIO !!! 
            <br/>&nbsp;&nbsp;&nbsp;FIQUE ATENTO PORQUE ISTO INFLUENCIA NA <b>"BASE DE CÁLCULO DO ICMS"</b> E <b>"VALOR DO ICMS"</b> JÁ QUE ESSA NOTA FISCAL POSSUI VALOR DE FRETE !
        </font>
    </p>
    &nbsp;
    <!--Tenho q colocar a função depois, pq senão não é reconhecida a Tag "P" que foi criada antes usando o atributo Piscar ...-->
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
    }
?>    
<!--Esses 2 hiddens aki foi uma cópia de compras, por enquanto vou deixar aqui-->
<input type='hidden' name='opt_item'>
<input type='hidden' name='opt_item_principal'>
<!-- ******************************************** -->
<input type='hidden' name='id_nf_outra' value='<?=$id_nf_outra?>'>
<!--Até então serve somente para armazenar o valor das mensagens-->
<input type='hidden' name='valor'>
<!-- ******************************************** -->
</form>
</body>
</html>
<?
        if(!empty($valor)) {
?>
            <Script Language = 'JavaScript'>
                alert('<?=$mensagem[$valor];?>')
            </Script>
<?
        }
    }else {
?>
<html>
<body>
<form name='form'>
<table width='90%' border='0' align='center'>
    <tr class='atencao'>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='#FF0000'>
                <b>Nota Fiscal
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='blue'>
                    <?=faturamentos::buscar_numero_nf($id_nf_outra, 'O');?>
                </font>
                n&atilde;o cont&eacute;m itens cadastrado.</b>
            </font>
        </td>
    </tr>
</table>
<input type='hidden' name='id_nf_outra' value='<?=$id_nf_outra?>'>
</form>
</body>
</html>
<?
    }
?>