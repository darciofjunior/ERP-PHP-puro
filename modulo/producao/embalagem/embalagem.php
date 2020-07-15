<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/estoque_new.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
require('../../classes/produtos_acabados/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Relatório de Embalagem(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function recarregar_tela() {
    window.document.location = '<?=$PHP_SELF.$parametro;?>'
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Relatório de Embalagem(ns)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            <font title='Operação de Custo' style='cursor:help'>
                O. C.
            </font>
        </td>
        <td>
            <font title='Peso Unitário do PA' style='cursor:help'>
                Peso Unit PA
            </font>
        </td>
        <td>
            <font title='Quantidade de Pçs. / Embalagem' style='cursor:help'>
                Qtde Pçs. / Emb
            </font>
        </td>
        <td>
            Cons.<br/>
            <font color='blue'>
                CMM
            </font>
            /<font color='green'>
                CMMV
            </font>
        </td>
        <td>
            Neces.<br>Compra
        </td>
        <td>
            Compra<br>Prod.
        </td>
        <td>
            Qtde<br>Est.
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        //Limpa o valor das variáveis, p/ não ficar acumulando com o do Loop Anterior ...
        $embalagens = '';
        $consumos_cmm_cmmv      = '';
        $necessidades_compras   = '';
        $compras_producoes      = '';
        $estoques_insumos       = '';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
        <?
            echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
//Aki é a Marcação de PA Migrado
            if($campos[$i]['pa_migrado'] == 1) echo '<font color="red" title="PA MIGRADO" style="cursor:help"><b>MIG</b></font>';
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['operacao_custo'] == 0) {
                echo '<font title="Industrial" style="cursor:help">I</font>';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[$i]['operacao_custo_sub'] == 0) {
                    echo '-<font title="Industrial" style="cursor:help">I</font>';
                }else if($campos[$i]['operacao_custo_sub'] == 1) {
                    echo '-<font title="Revenda" style="cursor:help">R</font>';
                }else {
                    echo '-';
                }
            }else if($campos[$i]['operacao_custo'] == 1) {
                echo '<font title="Revenda" style="cursor:help">R</font>';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['peso_unitario'], 3, ',', '.');?>
        </td>
        <?
/*********************************Etapa 1***********************************/
//Equivale a 1ª Etapa do Custo ...
            $sql = "SELECT ei.qtde, pi.id_produto_insumo, pi.unidade_conversao, pi.discriminacao, pi.estoque_mensal, ppe.pecas_por_emb, ppe.embalagem_default 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.id_produto_insumo = pi.id_produto_insumo 
                    INNER JOIN `pas_vs_pis_embs` ppe ON ppe.id_produto_insumo = pi.id_produto_insumo 
                    WHERE ppe.`id_produto_acabado` = ".$campos[$i]['id_produto_acabado']." ORDER BY pi.discriminacao ";
            $campos_etapa1 = bancos::sql($sql);
            $linhas_etapa1 = count($campos_etapa1);
            if($linhas_etapa1 == 0) {//Se não existe nenhuma Embalagem, apresento essa opção p/ Atrelar ...
                $embalagens.= "&nbsp;<img src = '../../../imagem/menu/incluir.png' border='0' title='Atrelar Embalagem' alt='Atrelar Embalagem' onclick=\"html5Lightbox.showLightbox(7, 'atrelar_embalagem.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."')\" style='cursor:help'>&nbsp;<b>Atrelar Embalagem</b>";
            }else {//Se existir pelo menos 1, então eu apresento as Existentes ...
                for($j = 0; $j < $linhas_etapa1; $j++) {
                    //1) Embalagens ...
                    if($campos_etapa1[$j]['embalagem_default'] == 1) {//Principal
                        $embalagens.= '<img src="../../../imagem/certo.gif" title="Embalagem Principal" style="cursor:help"><b>* <font color="darkblue">'.$campos_etapa1[$j]['pecas_por_emb'].'</font></b> / '.$campos_etapa1[$j]['discriminacao'].'<br>';
                    }else {
                        $embalagens.= '<b>* <font color="darkblue">'.$campos_etapa1[$j]['pecas_por_emb'].'</font></b> / '.$campos_etapa1[$j]['discriminacao'].'<br>';
                    }
/****************Esses dados são equivalentes a uma parte da Tela do Nível de Estoque de Compras**************/
                    //2) CMM e CMMV ...
                    $cmm = number_format($campos_etapa1[$j]['estoque_mensal'], 2, ',', '.');
                    //Busca do CMMV ...
                    $retorno = estoque_ic::consumo_mensal($campos_etapa1[$j]['id_produto_insumo'], $campos_etapa1[$j]['unidade_conversao']);//pego a qtde de cmmv do custo
                    $cmmv = '<font color="green"> / '.number_format($retorno['cmmv'], 2, ',', '.').'</font>';
/*Eu também levo o parâmetro de pop_up igual a 1, p/ q o Sistema não abra esse arquivo como sendo uma 
Tela Normal, evitando erro de redirecionamento da Tela, após a atualização dos dados do Produto Insumo*/
                    $consumos_cmm_cmmv.= "<a href=\"javascript:nova_janela('../../compras/produtos/alterar.php?passo=1&id_produto_insumo=".$campos_etapa1[$j]['id_produto_insumo']."&pop_up=1', 'pop', '', '', '', '', '620', '850', 'c', 'c', '', '', 's', 's', '', '', '')\" class='link'>".$cmm.$cmmv."</a><br>";
                    //3) Necessidade de Compra ...
                    $necessidades_compras.= "<a href=\"javascript:nova_janela('../../classes/produtos_insumos/detalhes_producao.php?id_produto_insumo=".$campos_etapa1[$j]['id_produto_insumo']."', 'pop', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')\" title='Necessidade de Compra' class='link'>".segurancas::number_format(estoque_ic::necessidade_compras($campos_etapa1[$j]['id_produto_insumo']), 2, '.')."</a><br>";
                    //4) Compra Produção ...
                    $compras_producoes.= segurancas::number_format(estoque_ic::compra_producao($campos_etapa1[$j]['id_produto_insumo']), 2, '.').'<br>';
                    //5) Estoque Insumo
                    $estoques_insumos.= segurancas::number_format($campos_etapa1[$j]['qtde'], 2, '.').'<br>';
                }
            }
        ?>
        <td>
            <?=$embalagens;?>
        </td>
        <td align='right'>
            <?=$consumos_cmm_cmmv;?>
        </td>
        <td align='right'>
            <?=$necessidades_compras;?>
        </td>
        <td align='right'>
            <?=$compras_producoes;?>
        </td>
        <td align='right'>
            <?=$estoques_insumos;?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = '<?=$PHP_SELF;?>'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

<font><b>Discriminação </b></font>-> Custo(s) Liberado(s)
<font color='red'><b>Discriminação </b></font>-> Custo(s) não Liberado(s)
</pre>
<?}?>