<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/lista_preco/lista_preco.php', '../../../../../');

//Filtro normal ...
if(!empty($chkt_novo_preco_promocional)) 	$condicao_novo_preco_promocional    = " AND (pa.preco_promocional_simulativa <> '0' OR pa.preco_promocional_simulativa_b <> '0') ";
if(!empty($chkt_preco_promocional_atual))	$condicao_preco_promocional_atual   = " AND (pa.preco_promocional <> '0' OR pa.preco_promocional_b <> '0') ";
if(!empty($chkt_todos_produtos_zerados)) 	$condicao_produtos_zerados          = " AND pa.preco_unitario = '0.00' ";
if(empty($cmb_empresa_divisao)) 		$cmb_empresa_divisao                = '%';
if(empty($cmb_grupo_pa)) 			$cmb_grupo_pa                       = '%';
if(empty($cmb_familia)) 			$cmb_familia                        = '%';
if(empty($cmb_order_by)) 			$cmb_order_by                       = '1';
?>
<html>
<head>
<title>.:: Lista de Pre�o Nacional ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='1' cellspacing='1' cellpadding='0' align='center'>
    <tr class="linhanormal" align='center'>
        <td colspan='7' bgcolor='#FFFFFF'>
            <b>LISTA DE PRE�O ELETR�NICA</b>
        </td>
    </tr>
<?
/******************************Empresas******************************/
//Busca das Empresas Albafer e Tool Master ...
$sql = "SELECT id_empresa, nomefantasia 
        FROM `empresas` 
        WHERE `id_empresa` IN (1, 2) 
        AND `ativo` = '1' ORDER BY nomefantasia ";
$campos_empresas = bancos::sql($sql, $inicio, 1, 'sim', $pagina);//Aqui � paginado por Empresa ...
$linhas_empresas = count($campos_empresas);
for($i = 0; $i < $linhas_empresas; $i++) {
?>
    <tr class="linhanormal">
        <td bgcolor='#FFFFFF' colspan='7'>
            <font size='-2'>
                <b>* EMPRESA: </b><?=strtoupper($campos_empresas[$i]['nomefantasia']);?>
            </font>
        </td>
    </tr>
<?
/******************************Empresas Divis�es******************************/
//Busca das Empresas Divis�es ...
    $sql = "SELECT DISTINCT(id_empresa_divisao), razaosocial 
            FROM `empresas_divisoes` 
            WHERE `id_empresa` = ".$campos_empresas[$i]['id_empresa']." 
            AND `ativo` = '1' 
            AND `id_empresa_divisao` LIKE '$cmb_empresa_divisao' ORDER BY razaosocial ";
    $campos_emp_div = bancos::sql($sql);
    $linhas_emp_div = count($campos_emp_div);
    for($j = 0; $j < $linhas_emp_div; $j++) {
?>
    <tr class='linhanormal'>
        <td bgcolor='#FFFFFF' colspan='7'>
            <font size='-2'>
                <b>** EMPRESA DIVIS�O: </b><?=strtoupper($campos_emp_div[$j]['razaosocial']);?>
            </font>
        </td>
    </tr>
<?
/******************************Fam�lias******************************/
//Busca das Fam�lias com exce��o das Fam�lias de Componentes de 'Produ��o Interna ou M�quina' e M�o de Obra ...
        $sql = "SELECT DISTINCT(f.id_familia), f.nome 
                FROM `gpas_vs_emps_divs` ged 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.ativo = '1' AND gpa.id_grupo_pa LIKE '$cmb_grupo_pa' AND gpa.id_familia LIKE '$cmb_familia' 
                INNER JOIN `familias` f ON f.id_familia = gpa.id_familia AND f.ativo = '1' AND f.id_familia NOT IN (23, 24, 25) 
                WHERE ged.`id_empresa_divisao` = ".$campos_emp_div[$j]['id_empresa_divisao']." ORDER BY f.nome ";
        $campos_familia = bancos::sql($sql);
        $linhas_familia = count($campos_familia);
        for($k = 0; $k < $linhas_familia; $k++) {
?>
    <tr class="linhanormal">
        <td bgcolor='#FFFFFF' colspan='7'>
            <font size='-2'>
                <b>*** FAM�LIA: </b><?=strtoupper($campos_familia[$k]['nome']);?>
            </font>
        </td>
    </tr>
<?
/******************************Grupos******************************/
            //Busca dos Grupos com exce��o dos que s�o Nova Lusa e Hardsteel ...
            $sql = "SELECT DISTINCT(gpa.id_grupo_pa), gpa.nome, ged.desc_base_a_nac, ged.desc_base_b_nac 
                    FROM `gpas_vs_emps_divs` ged 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`ativo` = '1' AND gpa.`id_grupo_pa` NOT IN (61, 66, 74, 75, 76, 77, 79, 80, 82, 89) 
                    INNER JOIN `familias` f ON f.id_familia = gpa.id_familia AND f.id_familia = ".$campos_familia[$k]['id_familia']." AND f.ativo = '1' 
                    WHERE ged.`id_empresa_divisao` = ".$campos_emp_div[$j]['id_empresa_divisao']." ORDER BY gpa.nome ";
            $campos_grupos_pas = bancos::sql($sql);
            $linhas_grupos_pas = count($campos_grupos_pas);
            for($l = 0; $l < $linhas_grupos_pas; $l++) {
?>
    <tr class="linhanormal">
        <td bgcolor='#FFFFFF' colspan='7'>
            <font size='-2'>
                <b>**** GRUPO: </b><?=strtoupper($campos_grupos_pas[$l]['nome']);?>
            </font>
            <font size='-2'>
                <b> - DESCONTOS: </b><?=number_format($campos_grupos_pas[$l]['desc_base_a_nac'], 0, ',', '.').'% + '.number_format($campos_grupos_pas[$l]['desc_base_b_nac'], 0, ',', '.').'% ';?>
            </font>
        </td>
    </tr>
<?			
/******************************Produtos Acabados******************************/
//Busca todos os Produtos Acabados com exce��o dos 'ESP' e dos que 'N�o Produzem Temporariamente' ...
                $sql = "SELECT DISTINCT(pa.id_produto_acabado), pa.referencia, pa.discriminacao, pa.preco_unitario, u.sigla, ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac
                        FROM `produtos_acabados` pa 
                        INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.id_empresa_divisao = '".$campos_emp_div[$j]['id_empresa_divisao']."' 
                        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_grupo_pa = '".$campos_grupos_pas[$l]['id_grupo_pa']."' AND gpa.ativo = '1' 
                        INNER JOIN `familias` f ON f.id_familia = gpa.id_familia AND f.id_familia = ".$campos_familia[$k]['id_familia']." AND f.ativo = '1' 
                        WHERE pa.referencia LIKE '%$txt_referencia%' 
                        AND pa.discriminacao LIKE '%$txt_discriminacao%' 
                        AND pa.referencia <> 'ESP' 
                        AND pa.`status_nao_produzir` = '0' 
                        AND pa.`ativo` = '1' ORDER BY pa.referencia, pa.discriminacao ";
                $campos_pas = bancos::sql($sql);
                $linhas_pas = count($campos_pas);
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#FFFFFF'>
            <font size='-2'>
                <b>REFER�NCIA</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font size='-2'>
                <b>DISCRIMINA��O</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font size='-2'>
                <b>P�S / EMB</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font size='-2'>
                <b>UNIDADE</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font size='-2'>
                <b>P�O BRUTO</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font title='Desconto A / Desconto B / Acr�scimo Grupo P.A'>
                <b>Desc. A / B / Ac. <br>Grupo P.A.</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <b>P. L�q. <br>Fat. R$</b>
        </td>        
    </tr>
<?
                for($m = 0; $m < $linhas_pas; $m++) {
                    $total_produtos+= 1;
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#FFFFFF'>
            <?=$campos_pas[$m]['referencia'];?>
        </td>
        <td align="left" bgcolor='#FFFFFF'>
            <?=$campos_pas[$m]['discriminacao'];?>
        </td>
        <td bgcolor='#FFFFFF'>
        <?
            $sql = "SELECT pecas_por_emb 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = ".$campos_pas[$m]['id_produto_acabado']." 
                    AND `embalagem_default` = '1' LIMIT 1 ";
            $campos_pecas_por_emb = bancos::sql($sql);
            echo intval($campos_pecas_por_emb[0]['pecas_por_emb']);
        ?>
        </td>
        <td bgcolor='#FFFFFF'>
            <?=$campos_pas[$m]['sigla'];?>
        </td>	
        <td align="right" bgcolor='#FFFFFF'>
            R$ <?=number_format($campos_pas[$m]['preco_unitario'], 2, ',', '.');?>
        </td>
        <td align="right" bgcolor='#FFFFFF'>
            <font color='black' size='-2'>
                <?=number_format($campos_pas[$m]['desc_base_a_nac'], 2, ',', '.');?>
            </font>
            <input type='hidden' name='txt_desconto_a_grupoa[]' id='txt_desconto_a_grupoa<?=$m;?>' value="<?=number_format($campos_pas[$m]['desc_base_a_nac'], 2, ',', '.');?>" maxlength="7" size="6" class='caixadetexto' disabled>
            / 
            <font color="black" size='-2'>
                <?=number_format($campos_pas[$m]['desc_base_b_nac'], 2, ',', '.');?>
            </font>
            <input type='hidden' name='txt_desconto_b_grupoa[]' id='txt_desconto_b_grupoa<?=$m;?>' value="<?=number_format($campos_pas[$m]['desc_base_b_nac'], 2, ',', '.');?>" maxlength="7" size="6" class='caixadetexto' disabled>
            / 
            <font color="black" size='-2'>
                <?=number_format($campos_pas[$m]['acrescimo_base_nac'], 2, ',', '.');?>
            </font>
            <input type='hidden' name='txt_acrescimo_base_nac[]' id='txt_acrescimo_base_nac<?=$m;?>' value="<?=number_format($campos_pas[$m]['acrescimo_base_nac'], 2, ',', '.');?>" maxlength="7" size="6" class='caixadetexto' disabled>
        </td>
        <td align="right" bgcolor='#FFFFFF'>
             <?
             //F�rmula do Pre�o L�quido Fat. R$ - Impressa mais abaixa ...
            $preco_liq_fat_rs = $campos_pas[$m]['preco_unitario'] * (1 - $campos_pas[$m]['desc_base_a_nac'] / 100) * (1 - $campos_pas[$m]['desc_base_b_nac'] / 100) * (1 + $campos_pas[$m]['acrescimo_base_nac'] / 100);
            //For�o o arred. para 2 casas para n�o dar erro na f�rmula por causa do PHP -> D�rcio
            $preco_liq_fat_rs = round($preco_liq_fat_rs, 2);        
            echo number_format($preco_liq_fat_rs, 2, ',', '.');
            ?>
        </td>        
    </tr>
<?
                }
            }
        }
    }
}
?>
    <tr class='linhanormal' align='center'>
        <td colspan='7' bgcolor='#FFFFFF'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'lista_preco_nacional.php'" class='botao'>
        </td>
    </tr>
</table>
<table align='center'>
    <tr class="linhanormal" align="center">
        <td bgcolor='#FFFFFF'>
            <font size='3' color='darkblue'>
                <b>Total de Produto(s): <?=$total_produtos;?></b>
            </font>
        </td>
    </tr>
</table>
<br>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>