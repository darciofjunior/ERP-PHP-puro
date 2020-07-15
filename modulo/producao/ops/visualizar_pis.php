<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
require('../../../lib/intermodular.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/estoque_new.php');
require('../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>BAIXA DE PI ESTORNADA COM SUCESSO.</font>";

//Função q verifica se os produtos insumos é de valor 0 no estoque
function estoque_insumo_zero($id_produto_insumo) {
    $sql = "SELECT qtde 
            FROM `estoques_insumos` 
            WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
    $campos = bancos::sql($sql);
    return $campos[0]['qtde'];
}
$data_sys = date('Y-m-d H:i:s');

/***********Entra só 1 vez nessa parte, pois é passado o id do produto acabado por parâmetro***********/
if(!empty($id_produto_acabado)) {
    $sql = "SELECT `operacao_custo` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $operacao_custo = $campos[0]['operacao_custo'];
//Aqui o sistema verifica se já existe Custo p/ esse PA ...
    $sql = "SELECT `id_produto_acabado_custo` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Como não existe Custo p/ o tal PA, é criado um na hora mesmo ...
        $sql = "INSER INTO `produtos_acabados_custos` (`id_produto_acabado_custo`, `id_produto_acabado`, `qtde_lote`, `comprimento_2`, `operacao_custo`, `data_sys`) VALUES (null, '$id_produto_acabado', '1', '6.0', '$operacao_custo', '$data_sys') ";
        bancos::sql($sql);
        $id_produto_acabado_custo = bancos::id_registro();
    }else {
        $id_produto_acabado_custo = $campos[0]['id_produto_acabado_custo'];
    }
}

//Busca de um valor para fator custo para etapa 2
$fator_custo_2 = genericas::variavel(11);

//Essa variável vai estar sendo acionada para o caso de o usuário digitar na qtde um valor maior do que 1000 ...
$fator_custo_2_new = genericas::variavel(18);

//Busca de um valor para fator custo para etapa 1, 3 e 7
$fator_custo_1_3_7 = genericas::variavel(12);

//Busca de um valor para fator custo para etapa 5 e 6
$fator_custo_5_6 = genericas::variavel(10);

$sql = "SELECT gpa.`id_familia`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
        pa.`operacao_custo`, pa.`desenho_para_op`, pa.`observacao` AS observacao_produto, 
        pac.`operacao_custo` AS operacao_custo_pac, pa.`status_custo`, pac.`id_funcionario` AS func, 
        CONCAT(DATE_FORMAT(SUBSTRING(pac.`data_sys`, 1, 10), '%d/%m/%Y'), SUBSTRING(pac.`data_sys`, 11, 9)) AS data_atualizacao 
        FROM `produtos_acabados_custos` pac 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        WHERE pac.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$id_familia             = $campos[0]['id_familia'];
$id_produto_acabado     = $campos[0]['id_produto_acabado'];
$referencia             = $campos[0]['referencia'];
$discriminacao          = $campos[0]['discriminacao'];
$operacao_custo_rotulo  = ($campos[0]['operacao_custo'] == 0) ? 'Industrialização' : 'Revenda';
$desenho_para_op        = $campos[0]['desenho_para_op'];
$observacao_produto     = trim($campos[0]['observacao_produto']);
$operacao_custo_pac     = $campos[0]['operacao_custo_pac'];
$status_custo           = $campos[0]['status_custo'];
$func                   = $campos[0]['func'];
$data_atualizacao       = $campos[0]['data_atualizacao'];

$situacao1              = ' <b> - Necessita Compra / Produção</b>';
$situacao2              = ' <b> - Verificar Pzo de Entrega Pedidos / OP(s) Emitidos</b>';
?>
<html>
<head>
<title>.:: Visualizar PI(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function compra_de_pis(existem_qtdes_igual_zero, compra_de_pis, id_produto_acabado, qtde_lote) {
    if(existem_qtdes_igual_zero == 'S') {
        alert("EXISTE(M) QTDE(S) PARA LOTE(S) DE OP(S) IGUAL(IS) À ZERO !!!\n\nSENDO ASSIM NÃO É POSSÍVEL SOLICITAR COMPRA DE PI'S, FAÇA UMA REAVALIAÇÃO !")
    }else {
        var observacao_depto_compras = prompt('DIGITE UMA OBSERVAÇÃO P/ DEPTO DE COMPRAS: ')
        //Representa que o Usuário clicou no Botão OK e sendo assim posso enviar e-mail ao Depto. de Compras ...
        if(observacao_depto_compras != null) {
            nova_janela('compra_de_pis.php?compra_de_pis='+compra_de_pis+'&id_produto_acabado='+id_produto_acabado+'&qtde_lote='+qtde_lote+'&observacao_depto_compras='+observacao_depto_compras, 'COMPRA_DE_PIS', '', '', '', '', 450, 700, 'c', 'c', '', '', 's', 's', '', '', '')
        }
    }
}

function cotar_pis_para_orc(id_produto_acabado, qtde_lote, existe_etapa2) {
    var elementos   = document.form.elements
    var valores     = ''
    for(var i = 0; i < elementos.length; i++) {
        //Verifico se o Usuário selecionou pelo menos um checkbox "chkt_cotar[]" ...
        if(elementos[i].name == 'chkt_cotar[]') {
            if(elementos[i].checked) valores+= elementos[i].value+', '
        }
    }
    if(valores == '') {
        alert('SELECIONE UMA OPÇÃO P/ COTAR !')
    }else {
        //Significa que existe Matéria Prima e sendo assim o Sistema já sugere um Ø mínimo ...
        var observacao_depto_compras = (existe_etapa2 == 1) ? prompt('DIGITE UMA OBSERVAÇÃO P/ DEPTO DE COMPRAS: ', 'Ømín.aço: ') : prompt('DIGITE UMA OBSERVAÇÃO P/ DEPTO DE COMPRAS: ')
        valores = valores.substr(0, (valores.length) - 2)
        nova_janela('cotar_pis_para_orc.php?valores='+valores+'&id_produto_acabado='+id_produto_acabado+'&qtde_lote='+qtde_lote+'&observacao_depto_compras='+observacao_depto_compras, 'COTAR_PIS_PARA_ORC', '', '', '', '', 450, 700, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Visualizar PI(s)
        </td>
    </tr>
 <?
//Significa que já teve alguma alteração do usuário em relação a esse custo
    if($func != 0) {
        $sql = "SELECT `nome` 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$func' LIMIT 1 ";
        $campos_funcionario = bancos::sql($sql);
        $nome               = $campos_funcionario[0]['nome'];
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='9'>
            <b><font color='#FFFF00'>Última alteração realizada por:</font></b>
            <?=$nome;?>
            &nbsp;-&nbsp; <b><font color='#FFFF00'>Data e Hora de Atualização:</font></b>
            <?=$data_atualizacao;?>
        </td>
    </tr>
<?
    }
?>
    <tr align='center'>
        <td colspan='5' bgcolor='#CCCCCC' align='left'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' title='Produto Acabado / Componente' size='2'>
                <b>PA/C: </b>
                <font size='1'>
                    <?=' * '.intermodular::pa_discriminacao($id_produto_acabado);?>
                </font>
            </font>
        </td>
        <td colspan='4' bgcolor='#CCCCCC' align='left'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b>O.C.: </b>
                <font size='1'>
                    <?=$operacao_custo_rotulo;?>
                </font>
            </font>
        </td>
    </tr>
    <tr class='linhanormaldestaque'>
        <td colspan='9'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b>OBSERVAÇÃO DO PRODUTO: </b>
                <font size='1'>
                <?
                    if(empty($observacao_produto)) {
                        echo 'SEM OBSERVAÇÃO';
                    }else {
                        echo $observacao_produto;
                    }
                ?>
                </font>
            </font>
            <?
/*Se existir algum desenho anexado p/ essa P.A., então eu exibo essa palavra de desenho 
junto desse ícone de Impressora ...*/
                if(!empty($desenho_para_op)) {
                    $sql = "SELECT `desenho_para_op` 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                    $campos_desenho = bancos::sql($sql);
            ?>
            <a href="javascript:nova_janela('../../../imagem/fotos_produtos_acabados/<?=$campos_desenho[0]['desenho_para_op'];?>', 'EXIBIR_DESENHO', '', '', '', '', '700', '980', 'c', 'c', '', '')" class='link'>
                <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' title='Existe desenho anexado p/ este P.A' style='cursor:help' color='darkgreen' size='1'>
                    - <b>DESENHO</b>
                </font>
                <img src = '../../../imagem/impressora.gif' border='0' title='Existe desenho anexado p/ este P.A' alt='Existe desenho anexado p/ este P.A' style='cursor:pointer'>
            </a>
            <?
                }
            ?>
        </td>
    </tr>
    <tr align='left'>
        <td colspan='7' bgcolor='#CCCCCC'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b>Follow-Up do Produto Acabado (Vendedores e Depto. Técnico): </b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CCCCCC'>
            <font face="Verdana, Geneva, Arial, Helvetica, sans-serif;" size='1'>
                <a href="javascript:nova_janela('../cadastros/produto_acabado/follow_up.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', '500', '780', 'c', 'c', '', '', 's', 's', '', '', '')" alt="Registrar Follow_up(s) do Produto" title="Registrar Follow_up(s) do Produto" class="link">
                <?
                    $sql = "SELECT COUNT(`id_produto_acabado_follow_up`) AS total_follow_ups 
                            FROM `produtos_acabados_follow_ups` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado' ";
                    $campos = bancos::sql($sql);
                    $total_follow_ups = $campos[0]['total_follow_ups'];
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
 <?
/*Aqui eu fiz uma antecipação de sql da etapa 2, antes mesmo da etapa 1 porque
o campo quantidade de lote se encontra aki antes do loop da etapa 1*/
    $sql = "SELECT `id_produto_insumo`, `id_produto_insumo_ideal`, `qtde_lote`, `peso_kg`, `peca_corte`, `comprimento_1`, `comprimento_2` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
    $campos_etapa2              = bancos::sql($sql);
    $id_produto_insumo          = $campos_etapa2[0]['id_produto_insumo'];
    $id_produto_insumo_ideal    = $campos_etapa2[0]['id_produto_insumo_ideal'];
/******************************Esse Controle vem de outra Tela******************************/
    //Não posso levar em conta a Qtde da própria OP, pq esta já está inclusa dentro da necessidade ...
    if($_GET['id_op'] > 0) {//Significa que o usuário acessou essa Tela dentro de uma OP ...
        $qtde_lote = 0;
    }else {
        //Aqui serve para atualizar a qtde do lote na Etapa 2, que já reflete nas outras etapas
        $qtde_lote = ($_GET['nova_qtde_produzir'] > 0) ? $_GET['nova_qtde_produzir'] : $campos_etapa2[0]['qtde_lote'];
    }
/*******************************************************************************************/
/*Aqui verifica se a quantidade do lote é > 1000, porque caso isso aconteça então
sofrerá alterações no valor do fator de custo da Etapa 2*/
    if($qtde_lote > 1000) $fator_custo_2 = $fator_custo_2_new;
//Peça Corte
    if($campos_etapa2[0]['peca_corte'] == 0) {
        $pecas_corte = 1;
    }else {
        $pecas_corte = $campos_etapa2[0]['peca_corte'];
    }
//Comprimento A
    $comprimento_a = $campos_etapa2[0]['comprimento_1'];
//Comprimento B
    $comprimento_b = $campos_etapa2[0]['comprimento_2'];
/*Aqui eu trago o produto acabado do produto acabado custo que está
armazenado em um hidden*/
    $sql = "SELECT pa.id_produto_acabado, pa.operacao_custo 
            FROM `produtos_acabados_custos` pac 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pac.id_produto_acabado 
            WHERE pac.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_produto_acabado = $campos[0]['id_produto_acabado'];
//Essa já prepara as variáveis para o cálculo das etapas do custo
    $taxa_financeira_vendas = genericas::variaveis('taxa_financeira_vendas') / 100 + 1;
    //custos::custo_auto_pi_industrializado();//tem q ser antes das chamadas dos metodos todas_etapas(PA); tempo q gasta é quase zero
    $total_indust = custos::todas_etapas($id_produto_acabado, $operacao_custo_pac);
?>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b>Quantidade do Lote: </b>
            &nbsp;&nbsp;
            <?
//Verifica se esse id_produto_acabado está em algum Orçamento e se ele tem o seu valor igual a DEPTO TÉCNICO
                $sql = "SELECT ov.id_orcamento_venda, ovi.id_orcamento_venda_item 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.congelar = 'N' 
                        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' 
                        AND ovi.`preco_liq_fat_disc` = 'DEPTO TÉCNICO' LIMIT 1 ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                echo $qtde_lote;
                if($status_custo == 1) {
                    echo ' <font color="blue"><b> (CUSTO LIBERADO)</b></font>';
                }else {
                    echo ' <font color="red"><b> (CUSTO NÃO LIBERADO)</b></font>';
                }
        ?>
        </td>
        <td colspan='5'>
            <font color='#FF0000'>
                <b>Pre&ccedil;o Fat. Nac. Min. R$:</b>
            </font>
            <font color='#000000'>
                <?=number_format(($total_indust * $taxa_financeira_vendas), 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='6' align='left'>
            <font color='#FFFF00'>
                <i>1&ordf; Etapa: Embalagem(ns)</i>
            </font>
        </td>
        <td colspan='5' align='left'>
            <font color='#FFFF00'>
                <i>F.C.:
                    <font color='#FFFFFF'><?=number_format($fator_custo_1_3_7, 2, ',', '.');?></font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa1'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
/*Essa variável será responsável pelo abastecimento dos PI(s) ou PA(s) que estão com 
Estoque Final negativo na parte s/ Compra ...*/
    $compra_de_pis              = '';
    $existem_qtdes_igual_zero   = 'N';
//Aqui traz todas as embalagens que estão relacionadas ao produto acabado passado por parâmetro ...
    $sql = "SELECT ppe.id_pa_pi_emb, ppe.pecas_por_emb, ppe.embalagem_default, pi.id_produto_insumo, pi.discriminacao, pi.unidade_conversao, u.sigla 
            FROM `pas_vs_pis_embs` ppe 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ppe.id_produto_insumo 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            WHERE ppe.`id_produto_acabado` = '$id_produto_acabado' ORDER BY ppe.id_pa_pi_emb ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2' bgcolor='#CCCCCC'>
            <b><i>Ref. Emb - Discriminação</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <font title='Embalagem Principal' style='cursor:help'>
                <b><i>E.P.</i></b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Pçs / Emb </i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde p/ Lote OP</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Estoque</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde Compra</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='incluso Pedido(s) não Contabilizado(s)' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Necessidade</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='p/ todas OP(s) emitidas' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Estoque Final</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='(Est + Qtde Compra) - (Qtde Lote + Neces)' style='cursor:help' width='5' height='5' border='0'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2' align='left'>
            <?=$campos[$i]['sigla'].' - '.$campos[$i]['discriminacao'];?>
        </td>
        <td>
        <?
            if($campos[$i]['embalagem_default'] == 1) {//Principal
                echo '<img src="../../../imagem/certo.gif">';
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['unidade_conversao'] > 0.00) {
                echo $campos[$i]['pecas_por_emb'].' / '.number_format($campos[$i]['unidade_conversao'], 2, ',', '.').' ('.number_format(1 / ($campos[$i]['pecas_por_emb'] * $campos[$i]['unidade_conversao']), 2, ',', '.').') ';
            }else {
                echo $campos[$i]['pecas_por_emb'].' / <font color="red" title="Sem Conversão">S. C.</font>';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['unidade_conversao'] > 0.00) {
                $qtde_para_lote_op = $qtde_lote / $campos[$i]['pecas_por_emb'] / $campos[$i]['unidade_conversao'];
            }else {
                $qtde_para_lote_op = $qtde_lote / $campos[$i]['pecas_por_emb'];
            }
            echo number_format($qtde_para_lote_op, 2, ',', '.').' '.$campos[$i]['sigla'];
        ?>
        </td>
        <td>
        <?
//Traz a quantidade em estoque do produto insumo que está selecionado na combo
            $sql = "SELECT qtde as qtde_estoque 
                    FROM `estoques_insumos` 
                    WHERE `id_produto_insumo` = ".$campos[$i]['id_produto_insumo']." LIMIT 1 ";
            $campos_estoque_pi  = bancos::sql($sql);
            $qtde_estoque       = (count($campos_estoque_pi) == 1) ? $campos_estoque_pi[0]['qtde_estoque'] : 0;
            echo number_format($qtde_estoque, 2, ',', '.').' '.$campos[$i]['sigla'];
        ?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'pop', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
            <?  
                $qtde_compra = estoque_ic::compra_producao($campos[$i]['id_produto_insumo']);
                echo number_format($qtde_compra, 2, ',', '.');
            ?>
            </a>
        </td>
        <td>
        <?
            $necessidade = estoque_ic::necessidade_compras($campos[$i]['id_produto_insumo']);
            echo number_format($necessidade, 2, ',', '.');
        ?>
        </td>
        <td colspan='2'>
        <?
            echo 'c/ Compra=';
            $estoque_final_com_compra = ($qtde_estoque + $qtde_compra) - ($qtde_para_lote_op + $necessidade);
            if($estoque_final_com_compra < 0) {
                $font = '<font color="red"><b>';
                $situacao_compra = $situacao1;
            }else {
                $font = '<font color="blue"><b>';
                $situacao_compra = $situacao2;
            }
            echo $font.number_format($estoque_final_com_compra, 2, ',', '.').'</b></font>';
            echo ' | s/ Compra=';
            $estoque_final_sem_compra = $qtde_estoque - ($qtde_para_lote_op + $necessidade);
            if($estoque_final_sem_compra < 0) {
                $font = '<font color="red"><b>';
                if($qtde_para_lote_op == 0) {//Não tem sentido gerar essa Linha $compra_de_pis se essa Qtde for igual a Zero ...
                    $existem_qtdes_igual_zero = 'S';
                }else {
                    /************************************************************************************/
                    /*Por esse "Estoque Final sem Compra" estar negativo, então representa que eu não tenho 
                    componente suficiente em Estoque p/ essa Produção ...*/
                    $compra_de_pis.= '<br/>'.number_format($qtde_para_lote_op, 1, ',', '.').' '.$campos[$i]['sigla'].' - '.$campos[$i]['discriminacao'].$situacao_compra;
                    /************************************************************************************/
                }
            }else {
                $font = '<font color="blue"><b>';
            }
            echo $font.number_format($estoque_final_sem_compra, 2, ',', '.').'</b></font>';
        ?>
        </td>
    </tr>
<?
        }
    }
?>
</table>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class="linhadestaque" align='center'>
        <td colspan='2' align='left'>
            <font color='#FFFF00'>
                <i>2&ordf; Etapa: Custo A&ccedil;o / Outros Metais</i>
            </font>
        </td>
        <td colspan='4' align='left'>
            <font color='#FFFF00'>
                <i>F.C.:
                    <font color='#FFFFFF'><?=number_format($fator_custo_2, 2, ',', '.');?></font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa2'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
    //A cada checkbox "Cotar" somo 1 no índice, e isto me serve de controle p/ os Clicks em JavaScript ...
    $indice_cotar   = 0;

    //Essa variável será passada por parâmetro quando o usuário clicar no botão Cotar Pis p/ Orçamento ...
    $existe_etapa2  = 0;//Valor Default ...

    $sql = "SELECT pi.id_produto_insumo, pi.discriminacao, u.sigla 
            FROM `produtos_insumos` pi 
            INNER JOIN `produtos_insumos_vs_acos` pia ON pia.id_produto_insumo = pi.id_produto_insumo 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            WHERE pi.`id_produto_insumo` = '$id_produto_insumo' 
            AND pi.`ativo` = '1' ORDER BY pi.discriminacao ";
    $campos = bancos::sql($sql);
    if(!empty($campos[0]['discriminacao'])) {
        /********************************************************************************************************************/
        /*****Essas buscas de variáveis é uma preparação p/ passar por parâmetro a Qtde necessária p/ o Lote no Checkbox*****/
        /********************************************************************************************************************/
        //Traz o preço custo e a densidade do produto insumo ...
        $sql = "SELECT pia.densidade_aco 
                FROM `produtos_insumos` pi 
                INNER JOIN `produtos_insumos_vs_acos` pia ON pia.id_produto_insumo = pi.id_produto_insumo 
                WHERE pi.id_produto_insumo = ".$campos[0]['id_produto_insumo']." LIMIT 1 ";
        $campos_produto_insumo = bancos::sql($sql);
        if(count($campos_produto_insumo) == 1) {
            $dados_pi       = custos::preco_custo_pi($campos[0]['id_produto_insumo']);
            $preco_custo    = number_format($dados_pi['preco_comum'], 2, ',', '.');
            $densidade      = $campos_produto_insumo[0]['densidade_aco'];
        }else {
            $preco_custo    = '';
            $densidade      = '';
        }
//Traz a quantidade em estoque do produto insumo
        $sql = "SELECT qtde AS qtde_estoque 
                FROM `estoques_insumos` 
                WHERE `id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' LIMIT 1 ";
        $campos_estoque_pi = bancos::sql($sql);
        if(count($campos_estoque_pi) == 1) {
            $qtde_estoque   = $campos_estoque_pi[0]['qtde_estoque'];
            $qtde_estoque2  = ($campos_estoque_pi[0]['qtde_estoque'] / $densidade);
        }else {
            $qtde_estoque   = 0;
            $qtde_estoque2  = 0;
        }
        $comprimento_total = ($comprimento_a + $comprimento_b) / 1000;
        //O cálculo p/ Pinos com PA(s) = 'ESP' é o mesmo com 5% a mais da Quantidade ...
        $fator_perda    = ($id_familia == 2 && $referencia == 'ESP') ? 1.10 : 1.05;
        $peso_aco_kg    = $densidade * $comprimento_total * $fator_perda;
//Aqui são os cálculos para q Qtde do Lote do Custo
        $lote_custo_calculo1 = $peso_aco_kg * $qtde_lote;
        if($pecas_corte != 0) $lote_custo_calculo1/= $pecas_corte;
        $lote_custo_calculo2 = $lote_custo_calculo1 / $densidade;
        /********************************************************************************************************************/
?>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b><i>Ref. Aço - Discriminação Utilizada:</i></b>
            <?=$campos[0]['sigla'].' - '.$campos[0]['discriminacao'];?>
            &nbsp;-&nbsp;
            <input type='checkbox' name='chkt_cotar[]' id='chkt_cotar<?=$indice_cotar;?>' value='<?='2|'.round($lote_custo_calculo1, 1).'|'.$id_produto_insumo;?>' class='checkbox'>
            <label for='chkt_cotar<?=$indice_cotar;?>'>
                <font color='red'>
                    <b>Cotar</b>
                </font>
            </label>
            <?$indice_cotar++;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b><i>Ref. Aço - Discriminação Ideal:</i></b>
            <?
                //P/ que se perceba que o PI Utilizado é diferente do PI Ideal, "nós mudamos" a cor ...
                if($campos[0]['id_produto_insumo'] != $id_produto_insumo_ideal) echo '<font color="red"><b>';
            
                $sql = "SELECT pi.discriminacao, u.sigla 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE pi.`ativo` = '1' 
                        AND pi.`id_produto_insumo` = '$id_produto_insumo_ideal' ORDER BY pi.discriminacao ";
                $campos_pi_ideal = bancos::sql($sql);
                echo $campos_pi_ideal[0]['sigla'].' - '.$campos_pi_ideal[0]['discriminacao'];
                
                if($campos[0]['id_produto_insumo'] != $id_produto_insumo_ideal) echo '</b></font>';//P/ dar um Destaque melhor ...
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b><i>Preço R$ / Kg:</i></b>
        </td>
        <td>
            <b><i>Comprimento:</i></b>
        </td>
        <td>
            <b><i>Corte:</i></b>
        </td>
        <td colspan='3'>
            <b><i>Comprimento Total:</i></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$preco_custo;?>
        </td>
        <td>
            <?=$comprimento_a;?>&nbsp;MM&nbsp;&nbsp;
        </td>
        <td>
            <?=$comprimento_b;?>&nbsp;MM&nbsp;&nbsp;
        </td>
        <td colspan='3'>
            <?=number_format($comprimento_total, 3, ',', '.');?>
            &nbsp;M
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b><i>Peças / Corte:</i></b>
        </td>
        <td>
            <b><i>Densidade Kg / M:</i></b>
        </td>
        <td>
            <b><i>Peso / KG + 5%:</i></b>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$pecas_corte;?>
        </td>
        <td>
            <?=number_format($densidade, 3, ',', '.');?>
        </td>
        <td>
            <?=number_format($peso_aco_kg, 3, ',', '.');?>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>    
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='green'>
                <b><i>Qtde necessária p/ o Lote:</i></b>
            </font>
        </td>
        <td>
            <i><?=number_format($lote_custo_calculo1, 3, ',', '.');?></i> Kg
            <?
                if($id_familia == 2 && $referencia == 'ESP' && $lote_custo_calculo1 != 0) {
            ?>
                &nbsp;<img src='../../../imagem/bloco_negro.jpg' width='6' height='6' title="Família = 'PINO' e PA = 'ESP', acréscimo de 5% na Quantidade" style='cursor:help'>
            <?
                }
            ?>
        </td>
        <td>
            <font color='green'>
                <b><i>Qtde necessária p/ o Lote:</i></b>
            </font>
        </td>
        <td>
            <i><?=number_format($lote_custo_calculo2, 3, ',', '.');?></i> Metros
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='green'>
                <b><i>Estoque do Produto Insumo:</i></b>
            </font>
        </td>
        <td>
            <i><?=number_format($qtde_estoque, 3, ',', '.');?></i> Kg
        </td>
        <td>
            <font color='green'>
                <b><i>Estoque do Produto Insumo:</i></b>
            </font>
        </td>
        <td>
            <i><?=number_format($qtde_estoque2, 3, ',', '.');?></i> Metros
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b><i>Qtde Compra</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='Incluso Pedido(s) não Contabilizado(s)' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td>
            <a href="javascript:nova_janela('../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[0]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'pop', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
            <?
                $qtde_compra1 = estoque_ic::compra_producao($campos[0]['id_produto_insumo']);
                echo number_format($qtde_compra1, 2, ',', '.');
            ?>
            </a>
        </td>
        <td>
            <b><i>Qtde Compra</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='incluso Pedido(s) não Contabilizado(s)' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td>
        <?
            $qtde_compra2 = $qtde_compra1 / $densidade;
            echo number_format($qtde_compra2, 2, ',', '.');
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b><i>Necessidade</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='p/ todas OP(s) emitidas' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td>
        <?
            $necessidade1 = estoque_ic::necessidade_compras($campos[0]['id_produto_insumo']);
            echo number_format($necessidade1, 2, ',', '.');
        ?>
        </td>
        <td>
            <b><i>Necessidade</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='p/ todas OP(s) emitidas' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td>
        <?
            $necessidade2 = $necessidade1 / $densidade;
            echo number_format($necessidade2, 2, ',', '.');
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b><i>Estoque Final</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='(Est + Qtde Compra) - (Qtde Lote + Neces)' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td>
            <i>
            <?
                echo 'c/ Compra=';
                $estoque_final_com_compra1 = ($qtde_estoque + $qtde_compra1) - ($lote_custo_calculo1 + $necessidade1);
                if($estoque_final_com_compra1 < 0) {
                    $font = '<font color="red"><b>';
                    $situacao_compra = $situacao1;
                }else {
                    $font = '<font color="blue"><b>';
                    $situacao_compra = $situacao2;
                }
                echo $font.number_format($estoque_final_com_compra1, 2, ',', '.').'</b></font>';

                echo ' | s/ Compra=';
                $estoque_final_sem_compra1 = $qtde_estoque - ($lote_custo_calculo1 + $necessidade1);
                
                if($estoque_final_sem_compra1 < 0) {
                    $font = '<font color="red"><b>';
                    if($lote_custo_calculo1 == 0) {//Não tem sentido gerar essa Linha $compra_de_pis se essa Qtde for igual a Zero ...
                        $existem_qtdes_igual_zero = 'S';
                    }else {
                        /************************************************************************************/
                        /*Por esse "Estoque Final sem Compra" estar negativo, então representa que eu não tenho 
                        componente suficiente em Estoque p/ essa Produção ...*/
                        $compra_de_pis.= '<br/>'.number_format($lote_custo_calculo1, 1, ',', '.').' '.$campos[0]['sigla'].' - '.$campos[0]['discriminacao'].' ('.$qtde_lote.' pçs c/ '.($comprimento_a + 1).'  mm, se pçs cortadas) '.$situacao_compra;
                        /************************************************************************************/
                    }
                }else {
                    $font = '<font color="blue"><b>';
                }
                echo $font.number_format($estoque_final_sem_compra1, 2, ',', '.').'</b></font>';
            ?>
            </i> Kg
        </td>
        <td>
            <b><i>Estoque Final</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='(Est + Qtde Compra) - (Qtde Lote + Neces)' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td>
            <i>
            <?
                echo 'c/ Compra=';
                $estoque_final_com_compra2 = $estoque_final_com_compra1 / $densidade;
                if($estoque_final_com_compra2 < 0) {
                    $font = '<font color="red"><b>';
                }else {
                    $font = '<font color="blue"><b>';
                }
                echo $font.number_format($estoque_final_com_compra2, 2, ',', '.').'</b></font>';

                echo ' | s/ Compra=';
                $estoque_final_sem_compra2 = $estoque_final_sem_compra1 / $densidade;
                if($estoque_final_sem_compra2 < 0) {
                    $font = '<font color="red"><b>';
                }else {
                    $font = '<font color="blue"><b>';
                }
                echo $font.number_format($estoque_final_sem_compra2, 2, ',', '.').'</b></font>';
            ?>
            </i> Metros
        </td>
    </tr>
</table>
<?
        //Significa que existe Matéria Prima e será passada por parâmetro quando o usuário clicar no botão Cotar Pis p/ Orc ...
        $existe_etapa2 = 1;
    }
?>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='2' align='left'>
            <font color='#FFFF00'>
                <i>3&ordf; Etapa: Produto Insumo</i>
            </font>
        </td>
        <td colspan='5' align='left'>
            <font color='#FFFF00'>
                <i>F.C.:
                    <font color='#FFFFFF'><?=number_format($fator_custo_1_3_7, 2, ',', '.');?></font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa3'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
 <?
//Aqui traz todos os produtos insumos que estão relacionado ao produto acabado passado por parâmetro ...
    $sql = "SELECT pp.id_pac_pi, g.referencia, pi.id_produto_insumo, pi.discriminacao, pp.qtde, u.sigla 
            FROM `pacs_vs_pis` pp 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = pp.id_produto_insumo 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.id_pac_pi ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Ref - Discriminação</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Quantidade</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde p/ Lote OP</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Estoque</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde Compra</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='incluso Pedido(s) não Contabilizado(s)' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Necessidade</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='p/ todas OP(s) emitidas' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Estoque Final</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='(Est + Qtde Compra) - (Qtde Lote + Neces)' style='cursor:help' width='5' height='5' border='0'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos[$i]['sigla'].' - '.$campos[$i]['referencia'];?>
            -
            <?=$campos[$i]['discriminacao'];?>
            &nbsp;-&nbsp;
            <input type='checkbox' name='chkt_cotar[]' id='chkt_cotar<?=$indice_cotar;?>' value='<?='3|'.round($campos[$i]['qtde'] * $qtde_lote, 1).'|'.$campos[$i]['id_produto_insumo'];?>' class='checkbox'>
            <label for='chkt_cotar<?=$indice_cotar;?>'>
                <font color='red'>
                    <b>Cotar</b>
                </font>
            </label>
            <?$indice_cotar++;?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 1, ',', '.');?>
        </td>
        <td>
        <?
            $qtde_para_lote_op = $qtde_lote * $campos[$i]['qtde'];
            echo number_format($qtde_para_lote_op, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            //Traz a quantidade em estoque do produto insumo que está selecionado na combo
            $sql = "SELECT qtde AS qtde_estoque 
                    FROM `estoques_insumos` 
                    WHERE `id_produto_insumo` = ".$campos[$i]['id_produto_insumo']." LIMIT 1 ";
            $campos_estoque_pi  = bancos::sql($sql);
            $qtde_estoque       = (count($campos_estoque_pi) == 1) ? $campos_estoque_pi[0]['qtde_estoque'] : 0;
            echo number_format($qtde_estoque, 2, ',', '.');
        ?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'pop', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
            <?
                $qtde_compra = estoque_ic::compra_producao($campos[$i]['id_produto_insumo']);
                echo number_format($qtde_compra, 2, ',', '.');
            ?>
            </a>
        </td>
        <td>
        <?
            $necessidade = estoque_ic::necessidade_compras($campos[$i]['id_produto_insumo']);
            echo number_format($necessidade, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            echo 'c/ Compra=';
            $estoque_final_com_compra = ($qtde_estoque + $qtde_compra) - ($qtde_para_lote_op + $necessidade);
            if($estoque_final_com_compra < 0) {
                $font = '<font color="red"><b>';
                $situacao_compra = $situacao1;
            }else {
                $font = '<font color="blue"><b>';
                $situacao_compra = $situacao2;
            }
            echo $font.number_format($estoque_final_com_compra, 2, ',', '.').'</b></font>';
            
            echo ' | s/ Compra=';
            $estoque_final_sem_compra = $qtde_estoque - ($qtde_para_lote_op + $necessidade);
            if($estoque_final_sem_compra < 0) {
                $font = '<font color="red"><b>';
                if($qtde_para_lote_op == 0) {//Não tem sentido gerar essa Linha $compra_de_pis se essa Qtde for igual a Zero ...
                    $existem_qtdes_igual_zero = 'S';
                }else {
                    /************************************************************************************/
                    /*Por esse "Estoque Final sem Compra" estar negativo, então representa que eu não tenho 
                    componente suficiente em Estoque p/ essa Produção ...*/
                    $compra_de_pis.= '<br/>'.number_format($qtde_para_lote_op, 1, ',', '.').' '.$campos[$i]['sigla'].' - '.$campos[$i]['discriminacao'].$situacao_compra;
                    /************************************************************************************/
                }
            }else {
                $font = '<font color="blue"><b>';
            }
            echo $font.number_format($estoque_final_sem_compra, 2, ',', '.').'</b></font>';
        ?>
        </td>
    </tr>
<?
        }
    }
?>
</table>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='3' align='left'>
            <font color='#FFFF00'>
                <i>5&ordf; Etapa: Atrelar Custo de Trat. T&eacute;rmico / Galvanoplastia</i>
            </font>
        </td>
        <td colspan='5' align='left'>
            <font color='#FFFF00'>
                <i>F.C.:
                    <font color='#FFFFFF'><?=number_format($fator_custo_5_6, 2, ',', '.');?></font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa5'], 2, ',', '.');?>
            </font>
        </td>
    </tr>	
<?
/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
    $sql = "SELECT ppt.id_pac_pi_trat, u.sigla, pi.id_produto_insumo, pi.discriminacao, ppt.fator, ppt.peso_aco, ppt.peso_aco_manual, ppt.lote_minimo_fornecedor 
            FROM `pacs_vs_pis_trat` ppt 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ppt.id_produto_insumo 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY ppt.id_pac_pi_trat ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' align='center'>
    <td bgcolor='#CCCCCC'>
            <b><i>Ref. Trat - Discriminação</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Fator T.T.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>P. Unit&aacute;rio R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Peso p/ T.T.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Total R$</i></b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            //Peso Aço Manual está checado
            $peso_aco = ($campos[$i]['peso_aco_manual'] == 1) ? $campos[$i]['peso_aco'] : $campos[$i]['peso_aco'] * $campos[$i]['fator'];
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos[$i]['sigla'];?>
            -
            <?=$campos[$i]['discriminacao'];?>
            &nbsp;-&nbsp;
            <input type='checkbox' name='chkt_cotar[]' id='chkt_cotar<?=$indice_cotar;?>' value='<?='5|'.round($peso_aco * $qtde_lote, 1).'|'.$campos[$i]['id_produto_insumo'];?>' class='checkbox'>
            <label for='chkt_cotar<?=$indice_cotar;?>'>
                <font color='red'>
                    <b>Cotar</b>
                </font>
            </label>
            <?$indice_cotar++;?>
        </td>
        <td>
            <?=number_format($campos[$i]['fator'], 2, ',', '.');?>
        </td>
        <td>
        <?
            $dados_pi 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
            $preco_pi 	= $dados_pi['preco_comum'];
            $icms       = $dados_pi['icms'];
            echo number_format($preco_pi, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            echo number_format($peso_aco, 3, ',', '.');
            //Peso Aço Manual está checado
            if($campos[$i]['peso_aco_manual'] == 1) echo '<font color="green"><b>REAL</b></font>';
        ?>
        </td>
        <td>
        <?
            //Ignora a multiplicação pelo fator_tt ...
            if($campos[$i]['peso_aco_manual'] == 1) {
                $total = $preco_pi * $campos[$i]['peso_aco'] * $fator_custo_5_6;
            }else {
                $total = $campos[$i]['fator'] * $preco_pi * $campos[$i]['peso_aco'] * $fator_custo_5_6;
            }
            //////////////////////////////////////////////////////////
            if($campos[$i]['lote_minimo_fornecedor'] == 1) {//Se estiver setado ou 1 acionar o calculo abaixo de lote minimo por fornecedor default por pedido ...
                $id_fornecedor_default 	= custos::preco_custo_pi($campos[0]['id_produto_insumo'], 0, 1);
                //Busco na Lista de Preços o Lote Mínimo em R$ do Fornecedor e do PI na Lista de Preços ...
                $sql = "SELECT lote_minimo_reais 
                        FROM `fornecedores_x_prod_insumos` 
                        WHERE `id_fornecedor` = '$id_fornecedor_default' 
                        AND `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_lista       = bancos::sql($sql);
                $lote_minimo_reais  = $campos_lista[0]['lote_minimo_reais'];//lote minimo do fornecedor default
                $preco_peca_corte   = $lote_minimo_reais / $qtde_lote;
                $total_pecas_s_fator= $total / $fator_custo_5_6;
                if($total_pecas_s_fator < $preco_peca_corte) $total = $preco_peca_corte * $fator_custo_5_6;
            }
            //////////////////////////////////////////////////////////
            echo number_format($total, 2, ',', '.');
            //Também se tiver marcada a opção de lote mínimo, eu mostro essa Mensagem ...
            if($campos[$i]['lote_minimo_fornecedor'] == 1) echo ' <font color="red" title="Cálculo por Lote Mínimo" style="cursor:help"><b>LTM</b></font>';
        ?>
        </td>
    </tr>
<?
        }
    }
?>
</table>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='2' align='left'>
            <font color='#FFFF00'>
                <i>6&ordf; Etapa: Atrelar Custo de Usinagem</i>
            </font>
        </td>
        <td colspan='5' align='right'>
            <font color='#FFFF00'>
                <i> F.C.:
                    <font color='#FFFFFF'>
                        <?=number_format($fator_custo_5_6, 2, ',', '.');?>
                    </font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa6'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
    $sql = "SELECT ppu.id_pac_pi_usi, ppu.qtde, u.sigla, pi.id_produto_insumo, pi.discriminacao 
            FROM `pacs_vs_pis_usis` ppu 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ppu.id_produto_insumo 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            WHERE ppu.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY ppu.id_pac_pi_usi ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Ref. Usi - Discriminação </i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Quantidade</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>P. Bruto sem ICMS R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Total R$</i></b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos[$i]['sigla'];?>
            -
            <?=$campos[$i]['discriminacao'];?>
            &nbsp;-&nbsp;
            <input type='checkbox' name='chkt_cotar[]' id='chkt_cotar<?=$indice_cotar;?>' value='<?='6|'.round($campos[$i]['qtde'] * $qtde_lote, 1).'|'.$campos[$i]['id_produto_insumo'];?>' class='checkbox'>
            <label for='chkt_cotar<?=$indice_cotar;?>'>
                <font color='red'>
                    <b>Cotar</b>
                </font>
            </label>
            <?$indice_cotar++;?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
        <?
            $preco_custo = custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
            echo number_format($preco_custo, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $total = $campos[$i]['qtde'] * $preco_custo * $fator_custo_5_6;
            echo number_format($total, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
        }
    }
?>
</table>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='3' align='left'>
            <font color='#FFFF00'>
                <i>7&ordf; Etapa: Produto Acabado / Componente</i>
            </font>
        </td>
        <td colspan='5' align='left'>
            <font color='#FFFF00'>
                <i>F.C.:
                    <font color='#FFFFFF'><?=number_format($fator_custo_1_3_7, 2, ',', '.');?></font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa7'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
/*Aqui traz todos produtos acabados componentes que estão relacionadas ao produto acabado
passado por parâmetro*/
    $sql = "SELECT pa.referencia, pa.id_produto_acabado, pa.discriminacao, pa.operacao_custo, pa.preco_unitario, pa.status_custo, pp.id_pac_pa, pp.qtde, u.sigla 
            FROM `pacs_vs_pas` pp 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pp.id_produto_acabado 
            INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
            WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.id_pac_pa ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Ref. PA - Discriminação</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <font title='Operação de Custo' style='cursor:help'>
                <b><i>O.C.</i></b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde p/ Lote OP</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Est Real</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde Compra / Prod</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='incluso Pedido(s) não Contabilizado(s)' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Necessidade</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='p/ todas OP(s) emitidas' style='cursor:help' width='5' height='5' border='0'>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Estoque Final</i></b>
            <img src='../../../imagem/bloco_negro.gif' title='(Est + Qtde Compra) - (Qtde Lote + Neces)' style='cursor:help' width='5' height='5' border='0'>
        </td>
    </tr>
<?
        $exibir_titulo_do_setor = 'SIM';
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
        <?
            if($campos[$i]['status_custo'] == 1) {//Já está liberado
        ?>
            <font title='Custo Liberado'>
                <?=$campos[$i]['sigla'].' - '.$campos[$i]['referencia'].' - '.$campos[$i]['discriminacao'];?>
            </font>
        <?
            }else {//Não está liberado
        ?>
            <font title='Custo não Liberado' color='red'>
                <?=$campos[$i]['sigla'].' - '.$campos[$i]['referencia'].' - '.$campos[$i]['discriminacao'];?>
            </font>
        <?
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['operacao_custo'] == 0) {//Industrialização
        ?>
                <p title='Industrialização'>I</p>
        <?
            }else {//Revenda
        ?>
                <p title='Revenda'>R</p>
        <?
            }
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
        <?
            $qtde_para_lote_op = $qtde_lote * $campos[$i]['qtde'];
            echo number_format($qtde_para_lote_op, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $estoque_produto    = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], '1');
            $qtde_estoque       = $estoque_produto[0];
            $producao           = $estoque_produto[2];
            echo number_format($qtde_estoque, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['operacao_custo'] == 0) {//Industrialização ...
                $url                = "../../classes/estoque/alterar_prazo_entrega_industrial.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&operacao_custo=".$operacao_custo."&atualizar_iframe=1";
                $string_apresentar  = 'Alterar Prazo de Entrega Industrial';
            }else {//Revenda ...
                $url                = "../../classes/estoque/alterar_prazo_entrega_revenda.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&operacao_custo=".$operacao_custo."&atualizar_iframe=1";
                $string_apresentar  = 'Alterar Prazo de Entrega Revenda';
            }
        ?>
            <a href="javascript:nova_janela('<?=$url;?>', 'PRAZO_ENTREGA', '', '', '', '', 500, 960, 'c', 'c', '', '', 's', 's', '', '', '')" title="<?=$string_apresentar;?>" alt="<?=$string_apresentar;?>" class='link'>
            <?
                $qtde_compra = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);

                echo number_format($qtde_compra, 2, ',', '.');
                echo ' / '.number_format($producao, 2, ',', '.');
            ?>
            </a>
        </td>
        <td>
        <?
            /*$necessidade = estoque_acabado::necessidade_pa_componentes($campos[$i]['id_produto_acabado']);
            echo number_format($necessidade, 2, ',', '.');*/
        ?>
            ??????
        </td>
        <td>
        <?
            echo 'c/ Compra=';
            $estoque_final_com_compra = ($qtde_estoque + $qtde_compra) - ($qtde_para_lote_op + $necessidade);
            if($estoque_final_com_compra < 0) {
                $font = '<font color="red"><b>';
                $situacao_compra = $situacao1;
            }else {
                $font = '<font color="blue"><b>';
                $situacao_compra = $situacao2;
            }
            echo $font.number_format($estoque_final_com_compra, 2, ',', '.').'</b></font>';
            
            echo ' | s/ Compra=';
            $estoque_final_sem_compra = $qtde_estoque - ($qtde_para_lote_op + $necessidade);
            if($estoque_final_sem_compra < 0) {
                if($qtde_para_lote_op == 0) {//Não tem sentido gerar essa Linha $compra_de_pis se essa Qtde for igual a Zero ...
                    $existem_qtdes_igual_zero = 'S';
                }else {
                    //Esse título abaixo só poderá ser exibido apenas uma única vez ...
                    if($exibir_titulo_do_setor == 'SIM') {
                        $compra_de_pis.= '<br><br><b>A/C DEPTO. TÉCNICO</b><br>';
                        $exibir_titulo_do_setor = 'NAO';
                    }
                    $font = '<font color="red"><b>';
                    /************************************************************************************/
                    /*Por esse "Estoque Final sem Compra" estar negativo, então representa que eu não tenho 
                    componente suficiente em Estoque p/ essa Produção ...*/
                    $compra_de_pis.= '<br/>'.number_format($qtde_para_lote_op, 1, ',', '.').' '.$campos[$i]['sigla'].' - '.$campos[$i]['discriminacao'].$situacao_compra;
                    /************************************************************************************/
                }
            }else {
                $font = '<font color="blue"><b>';
            }
            echo $font.number_format($estoque_final_sem_compra, 2, ',', '.').'</b></font>';
        ?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_cotar_pis_para_orc' value='Cotar Pis p/ Orçamento' title='Cotar Pis p/ Orçamento' onclick="cotar_pis_para_orc('<?=$id_produto_acabado;?>', '<?=$qtde_lote;?>', '<?=$existe_etapa2;?>')" style='color:red' class='botao'>
        </td>    
        <td colspan='2'>
            <?
                if(!empty($compra_de_pis)) {
            ?>
            <input type='button' name='cmd_compra_de_pis' value='Compra de PI´s' title='Compra de PI´s' onclick="compra_de_pis('<?=$existem_qtdes_igual_zero;?>', '<?=$compra_de_pis;?>', '<?=$id_produto_acabado;?>', '<?=$qtde_lote;?>')" class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='blue'>Variáveis:</font></b>
<pre>
<b><font color='green'>* Taxa Financeira de Vendas: </font><?=number_format((($taxa_financeira_vendas-1)*100), 2, ',', '.');?> %</b><br>
</pre>