<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/custos.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
require('../../../classes/produtos_acabados/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Consultar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='120%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class='atencao' align='center'>
        <td colspan='25'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='25'>
            Consultar Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <?=genericas::order_by('gpa.nome', 'Grupo P.A.', 'Grupo P.A.', $order_by, '../../../../');?>
            <?=genericas::order_by('ed.razaosocial', ' / (E.D.)', 'Empresa Divisão', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.referencia', 'Ref.', 'Referência', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.discriminacao', 'Discriminação', '', $order_by, '../../../../');?>
        </td>
        <td>
            Top
        </td>
        <td>
            <?=genericas::order_by('pa.operacao_custo', 'O. C.', 'Operação de Custo', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.operacao', 'O. F.', 'Operação (Fat)', $order_by, '../../../../');?>
        </td>
        <td>
            Origem - ST
        </td>
        <td>
            <p title="Classificação Fiscal">C. F.</p>
        </td>
        <td>
            Código<br>Barra
        </td>
        <td>
            Des.<br/>OP
        </td>
        <td>
            Des.<br/>Etiq.
        </td>
        <td>
            Des.<br/>Conf.
        </td>
        <td>
            <font title='Peças por Embalagem' style='cursor:help'>
                P.E.
            </font>
        </td>
        <td>
            M.M.V
        </td>
        <td>
            <?=genericas::order_by('pa.peso_unitario', 'P. U.', 'Peso Unitário', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.altura', 'Alt', 'Altura', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.largura', 'Larg', 'Largura', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.comprimento', 'Comp', 'Comprimento', $order_by, '../../../../');?>
        </td>
        <td>
            <p title='Dimensões da Embalagem'>Dim. Embals.</p>
        </td>
        <td>
            P.U.
        </td>
        <td>
            <p title='Altura'>Alt</p>
        </td>
        <td>
            <p title='Largura'>Larg</p>
        </td>
        <td>
            <p title='Comprimento'>Comp</p>
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
//recontar_estoque
//Carrega na Tela os registros ...
    for($i = 0; $i < $linhas; $i++) {
        $dados_produto = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado']);

        $url = 'alterar.php?passo=1&id_produto_acabado='.$campos[$i]['id_produto_acabado'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href = '<?=$url;?>' class='html5lightbox'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href = '<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
        <?
            if($campos[$i]['status_custo'] == 1) {//Já está liberado
        ?>
            <font title='Custo Liberado'>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            </font>
        <?
            }else {//Não está liberado
        ?>
            <font title='Custo não Liberado' color='red'>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            </font>
        <?
            }
//Aki é a Marcação de PA Migrado
            if($campos[$i]['pa_migrado'] == 1) echo '<font color="red" title="PA MIGRADO" style="cursor:help"><b>MIG</b></font>';
        ?>
        <td align='right'>
        <? 
            if($campos[$i]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos[$i]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[$i]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos[$i]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos[$i]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['operacao'] == 0) {
        ?>
                <p title="Industrialização (c/ IPI)">I - C</p>
        <?
            }else if($campos[$i]['operacao'] == 1) {
        ?>
                <p title="Revenda (s/ IPI)">R - S</p>
        <?
            }else {
                echo '-';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['origem_mercadoria'].$dados_produto['situacao_tributaria'];?>
        </td>
        <td>
        <?
//Busca a Classificação do P.A.
            $sql = "SELECT cf.`classific_fiscal` 
                    FROM `gpas_vs_emps_divs` ged 
                    INNER JOIN `grupos_pas` gp ON gp.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `familias` f ON f.`id_familia` = gp.`id_familia` 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    WHERE ged.`id_gpa_vs_emp_div` = '".$campos[$i]['id_gpa_vs_emp_div']."' LIMIT 1 ";
            $campos_classif_fiscal = bancos::sql($sql);
            echo $campos_classif_fiscal[0]['classific_fiscal'];
        ?>
        <td>
        <?
            if($campos[$i]['codigo_barra'] != '') echo $campos[$i]['codigo_barra'];
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['desenho_para_op'] != '') echo 'Sim';
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['desenho_para_etiqueta'] != '') {
        ?>
                <img src="<?='../../../../imagem/desenhos_grupos_pas/'.$campos[$i]['desenho_para_etiqueta'];?>" width='40' height='12'>
        <?
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['desenho_para_conferencia'] != '') {
        ?>
                <img src="<?='../../../../imagem/fotos_produtos_acabados/'.$campos[$i]['desenho_para_conferencia'];?>" width='40' height='12'>
        <?
            }
        ?>
        </td>
        <td>
        <?
            //Faz a Verificação no Custo do PA <- Etapa 1 - busco qual é a embalagem principal do PA ...
            $sql = "SELECT pi.`id_produto_insumo`, pi.`discriminacao`, pi.`peso`, pi.`altura`, pi.`largura`, 
                    pi.`comprimento`, ppe.`pecas_por_emb` 
                    FROM `pas_vs_pis_embs` ppe 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
                    WHERE ppe.`id_produto_acabado` = ".$campos[$i]['id_produto_acabado']." 
                    AND ppe.`embalagem_default` = '1' ORDER BY ppe.id_pa_pi_emb ";
            $campos_pecas_emb = bancos::sql($sql);
            if(count($campos_pecas_emb) == 1) echo intval($campos_pecas_emb[0]['pecas_por_emb']);
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['mmv'], 1, '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['altura'], 0);?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['largura'], 0);?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['comprimento'], 0);?>
        </td>
        <td align='left'>
            <?=$campos_pecas_emb[0]['discriminacao'];?>
        </td>
        <td>
            <?=segurancas::number_format($campos_pecas_emb[0]['peso'],4, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos_pecas_emb[0]['altura'], 0);?>
        </td>
        <td>
            <?=segurancas::number_format($campos_pecas_emb[0]['largura'], 0);?>
        </td>
        <td>
            <?=segurancas::number_format($campos_pecas_emb[0]['comprimento'], 0);?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='25'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
        </td>
    </tr>
</table>
<table width='120%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr align='center'>
        <td>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
</table>
</body>
</html>
<?}?>