<?
require('../../../../lib/segurancas.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/estoque_pa/estoque_pa.php', '../../../../');

$valor_dolar_dia = genericas::moeda_dia('dolar');
?>
<html>
<head>
<title>.:: Relatório de Estoque P.A. ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='1' cellspacing='0' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Relat&oacute;rio de Estoque P.A. - 
            <font color='black'>
                Impressão em: 
            </font>
            <?=date('d/m/Y H:i:s');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa Divis&atilde;o / Família / Grupo P.A.
        </td>
        <td>
            <font title='Estoque Real' style='cursor:help'>
                ER
            </font>
        </td>
        <td>
            <font title='Ordem de Embalagem' style='cursor:help'>
                OE
            </font>
        </td>
        <td>
            Total R$
        </td>
    </tr>
    <!-----------------------------------------ESTA PARTE É DA EMPRESA DIVISÃO---------------------------------------------------->
<?
    $sql = "SELECT ed.`id_empresa_divisao`, ed.`razaosocial`, SUM(ea.`qtde`) AS total_qtde_estoque_empresa_divisao, 
            SUM(ea.`qtde_oe_em_aberto`) AS total_qtde_oe_em_aberto_empresa_divisao 
            FROM `empresas_divisoes` ed 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_empresa_divisao` = ed.`id_empresa_divisao` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_gpa_vs_emp_div` = ged.`id_gpa_vs_emp_div` 
            INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
            GROUP BY ed.`id_empresa_divisao` ORDER BY ed.`razaosocial` ";
    $campos_empresa_divisao = bancos::sql($sql);
    $linhas_empresa_divisao = count($campos_empresa_divisao);

    for($i = 0; $i < $linhas_empresa_divisao; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                <b><?=$campos_empresa_divisao[$i]['razaosocial'];?></b>
            </font>
        </td>
        <td align='right'>
            <font color='blue'>
                <b><?=segurancas::number_format($campos_empresa_divisao[$i]['total_qtde_estoque_empresa_divisao'], 2, '.');?></b>
            </font>
        </td>
        <td align='right'>
            <font color='blue'>
                <b><?=segurancas::number_format($campos_empresa_divisao[$i]['total_qtde_oe_em_aberto_empresa_divisao'], 2, '.');?></b>
            </font>
        </td>
        <td align='right'>
            &nbsp;
        </td>
    </tr>
    <!-----------------------------------------ESTA PARTE É DA FAMÍLIA---------------------------------------------------->
<?
	$sql = "SELECT f.`id_familia`, f.`nome`, SUM(ea.`qtde`) AS total_qtde_estoque_familia, 
                SUM(ea.`qtde_oe_em_aberto`) AS total_qtde_oe_em_aberto_familia 
                FROM `gpas_vs_emps_divs` ged 
                INNER JOIN `produtos_acabados` pa ON pa.`id_gpa_vs_emp_div` = ged.`id_gpa_vs_emp_div` 
                INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`ativo` = '1' 
                INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` AND f.`ativo` = '1' 
                WHERE ged.`id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' 
                GROUP BY f.`id_familia` ORDER BY f.`nome` ";
	$campos_familia = bancos::sql($sql);
	$linhas_familia = count($campos_familia);
	for($j = 0; $j < $linhas_familia; $j++) {
?>
    <tr class='linhanormal'>
        <td>
            <a href='detalhes_estoque_pa.php?id_familia=<?=$campos_familia[$j]['id_familia'];?>&id_empresa_divisao=<?=$campos_empresa_divisao[$i]['id_empresa_divisao'];?>' style='color:#ff9900' class='html5lightbox'>
                <font color='#990099'>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;<?=$campos_familia[$j]['nome'];?></b>
                </font>
            </a>
        </td>
        <td align='right'>
            <font color='#990099'>
                <b><?=segurancas::number_format($campos_familia[$j]['total_qtde_estoque_familia'], 2, '.');?></b>
            </font>
        </td>
        <td align='right'>
            <font color='#990099'>
                <b><?=segurancas::number_format($campos_familia[$j]['total_qtde_oe_em_aberto_familia'], 2, '.');?></b>
            </font>
        </td>
        <td align='right'>
            &nbsp;
        </td>
    </tr>
    <!-----------------------------------------ESTA PARTE É DO GRUPO---------------------------------------------------->
<?
            $sql = "SELECT ged.`id_empresa_divisao`, gpa.`id_grupo_pa`, gpa.`nome`, 
                    SUM(ea.`qtde`) AS total_qtde_estoque_grupo, 
                    SUM(ea.`qtde_oe_em_aberto`) AS total_qtde_oe_em_aberto_grupo, 
                    SUM(((ea.`qtde` + ea.`qtde_oe_em_aberto`) * pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) AS total_grupo_pa_rs 
                    FROM `gpas_vs_emps_divs` ged 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_gpa_vs_emp_div` = ged.`id_gpa_vs_emp_div` 
                    INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` AND f.`id_familia` = '".$campos_familia[$j]['id_familia']."' 
                    WHERE ged.`id_empresa_divisao` = '".$campos_empresa_divisao[$i]['id_empresa_divisao']."' 
                    GROUP BY gpa.`id_grupo_pa` ORDER BY gpa.`nome` ";
            $campos_grupo = bancos::sql($sql);
            $linhas_grupo = count($campos_grupo);
            for($k = 0; $k < $linhas_grupo; $k++) {
?>
    <tr class='linhanormal'>
        <td>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a href='detalhes_estoque_pa.php?id_grupo_pa=<?=$campos_grupo[$k]['id_grupo_pa'];?>&id_empresa_divisao=<?=$campos_grupo[$k]['id_empresa_divisao'];?>' class='html5lightbox'>
                <font color='#ff9900'>
                    <?=$campos_grupo[$k]['nome'];?>
                </font>
            </a>
        </td>
        <td align='right'>
            <font color='#ff9900'>
                <b>
                <?
                    echo segurancas::number_format($campos_grupo[$k]['total_qtde_estoque_grupo'], 2, '.');
                    
                    //Essas variáveis serão apresentadas mais abaixo no "Resumo" do fim desse Relatório ...
                    $total_qtde_estoque_grupo+= $campos_grupo[$k]['total_qtde_estoque_grupo'];
                    $vetor_qtde_estoque_empresa_divisao[$campos_empresa_divisao[$i]['id_empresa_divisao']]+= $campos_grupo[$k]['total_qtde_estoque_grupo'];
                    
                    $total_qtde_oe_em_aberto_grupo+= $campos_grupo[$k]['total_qtde_oe_em_aberto_grupo'];
                    $vetor_qtde_oe_em_aberto_empresa_divisao[$campos_empresa_divisao[$i]['id_empresa_divisao']]+= $campos_grupo[$k]['total_qtde_oe_em_aberto_grupo'];
                ?>
                </b>
            </font>
        </td>
        <td align='right'>
            <font color='#ff9900'>
                <b><?=segurancas::number_format($campos_grupo[$k]['total_qtde_oe_em_aberto_grupo'], 2, '.');?></b>
            </font>
        </td>
        <td align='right'>
            <font color='#ff9900'>
                <b>
                <?
                    echo segurancas::number_format($campos_grupo[$k]['total_grupo_pa_rs'], 2, '.');
                
                    $total_estoque_grupo_rs+= $campos_grupo[$k]['total_grupo_pa_rs'];
                    $vetor_estoque_divisao_rs[$campos_empresa_divisao[$i]['id_empresa_divisao']]+= $campos_grupo[$k]['total_grupo_pa_rs'];
		?>
                </b>
            </font>
        </td>
    </tr>
<?
            }//Fim do For da Grupo ...
        }//Fim do For da Família ...
    }//Fim do For da Divisão ...
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Empresa Divis&atilde;o
        </td>
        <td>
            Total ER
        </td>
        <td>
            Total OE
        </td>
        <td>
            Total R$
        </td>
    </tr>
<?
    //Deleto todos os registro "temp" q possui status igual a Zero, por mim já teria deletado essa Tabela "Dárcio" ...
    $sql = "DELETE 
            FROM `rel_estoques` 
            WHERE `status` = '0' ";
    bancos::sql($sql);
    
    //Não me lembro o porque desse Abastecimento por Divisão nessa Tabela - Dárcio 13/02/2015 ??? ...
    for($i = 0; $i < $linhas_empresa_divisao; $i++) {
        $empresa_divisao        = $campos_empresa_divisao[$i]['razaosocial'];
        $estoque_divisao_rs     = $vetor_estoque_divisao_rs[$campos_empresa_divisao[$i]['id_empresa_divisao']];
        
        $sql = "INSERT INTO `rel_estoques` (`id_rel_estoque`, `divisao`, `qtde_est_real`, `total_reais`, `data_atualizacao`, `status`) 
                VALUES (NULL, '$empresa_divisao', '".$vetor_qtde_estoque_empresa_divisao[$campos_empresa_divisao[$i]['id_empresa_divisao']]."', '$estoque_divisao_rs', '".date('Y-m-d H:i:s')."', '0') ";
        bancos::sql($sql);
?>
    <tr class='linhanormal' align='left'>
        <td>
            <font color='blue'>
                <b><?=$empresa_divisao;?></b>
            </font>
        </td>
        <td align='right'>
            <font color='blue'>
                <b><?=segurancas::number_format($vetor_qtde_estoque_empresa_divisao[$campos_empresa_divisao[$i]['id_empresa_divisao']], 2, '.');?></b>
            </font>
        </td>
        <td align='right'>
            <font color='blue'>
                <b><?=segurancas::number_format($vetor_qtde_oe_em_aberto_empresa_divisao[$campos_empresa_divisao[$i]['id_empresa_divisao']], 2, '.');?></b>
            </font>
        </td>
        <td align='right'>
            <font color='#ff9900'>
                <b><?=segurancas::number_format($estoque_divisao_rs, 2, '.');?></b>
            </font>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal' align='left'>
        <td>
            <font color='red' size='2'>
                <b>Total:</b>
            </font>
        </td>
        <td align='right'>
            <span class='style3'>
                <font color='red' size='2'>
                    <?=segurancas::number_format($total_qtde_estoque_grupo, 2, '.');?>
                </font>
            </span>
        </td>
        <td align='right'>
            <span class='style3'>
                <font color='red' size='2'>
                    <?=segurancas::number_format($total_qtde_oe_em_aberto_grupo, 2, '.');?>
                </font>
            </span>
        </td>
        <td align='right'>
            <span class='style3'>
                <font color='red' size='2'>
                    <?=segurancas::number_format($total_estoque_grupo_rs, 2, '.');?>
                </font>
            </span>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font color='yellow'>
                Valor Dólar do dia => 
            </font>
            R$ <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='submit' name='cmd_atualizar' title='Atualizar Relatório' value='Atualizar Relatório' class='botao'>
            <input type='button' name='cmd_atualizar_grupos' title='Atualizar Grupo(s)' value='Atualizar Grupo(s)' onclick="nova_janela('atualizar_grupos.php', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            <?
                //Essa query é feita aqui p/ que o Pop-UP acima não faça essa Query toda vez em que a tela for recarregada ...
                $sql = "SELECT COUNT(DISTINCT(pa.`id_produto_acabado`)) AS total_registro 
                        FROM `produtos_acabados` pa 
                        WHERE pa.`ativo` = '1' 
                        AND pa.`referencia` = 'ESP' 
                        AND pa.`status_custo` = '1' 
                        UNION ALL 
                        SELECT COUNT( DISTINCT(pa.`id_produto_acabado`)) AS total_registro 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` IN (23, 24) 
                        WHERE pa.`ativo` = '1' 
                        AND pa.`status_custo` = '1' ";
                $campos_total_registro  = bancos::sql($sql);
                $total_registro         = $campos_total_registro[0]['total_registro'] + $campos_total_registro[1]['total_registro'];
            ?>
            <input type='button' name='cmd_atualizar_lista_PA_ESP' value='Atualizar à Lista dos P.A.s ESPs' title='Atualizar à Lista dos P.A.s ESPs' onclick="html5Lightbox.showLightbox(7, '../estoque_pa/atualizar_lista_pas_esp.php?total_registro=<?=$total_registro;?>')" class='botao'>
            <input type='button' name='cmd_PA_nao_liberados' value='P.A.s não liberado(s) e ER > 0' title='P.A.s não liberado(s) e Estoque Real > 0' onclick="html5Lightbox.showLightbox(7, 'pas_nao_liberados.php')" class='botao'>
            <input type='button' name='cmd_salvar' value='Salvar Resumo' title='Salvar resumo de Relatório para o Faturamento' onclick="nova_janela('resumo_relatorio.php', 'CONSULTAR', '', '', '', '', '10', '10', 'c', 'c', '', '', 's', 's', '', '', '')" style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<!--Apresento o Botão de Imprimir p/ que o Usuário Imprima a Listagem caso desejar ...-->
<Script Language = 'JavaScript'>
    parent.document.getElementById('linha_imprimir').style.visibility = 'visible'
</Script>