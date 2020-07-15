<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');//Essa biblioteca é requerida dentro do Custo ...
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/estoque_pa/estoque_pa.php', '../../../../');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_familia                     = $_POST['id_familia'];
    $id_grupo_pa                    = $_POST['id_grupo_pa'];
    $id_empresa_divisao             = $_POST['id_empresa_divisao'];
    $chkt_somente_estoque_maior_0   = $_POST['chkt_somente_estoque_maior_0'];
    $chkt_mostrar_esp               = $_POST['chkt_mostrar_esp'];
    $chkt_mostrar_custo_ml60        = $_POST['chkt_mostrar_custo_ml60'];
}else {//Somente na 1ª vez em que acabar de carregar a Tela ...
    $id_familia                     = $_GET['id_familia'];
    $id_grupo_pa                    = $_GET['id_grupo_pa'];
    $id_empresa_divisao             = $_GET['id_empresa_divisao'];
    $chkt_somente_estoque_maior_0   = $_GET['chkt_somente_estoque_maior_0'];
    $chkt_mostrar_esp               = $_GET['chkt_mostrar_esp'];
    $chkt_mostrar_custo_ml60        = $_GET['chkt_mostrar_custo_ml60'];
}

if(!empty($chkt_somente_estoque_maior_0)) {
    $condicao_somente_estoque_maior_0 	= " AND ea.`qtde` > '0' ";
    $checked1                           = 'checked';
}else {
    $condicao_somente_estoque_maior_0 	= '';
    $checked1                           = '';
}

if(!empty($chkt_mostrar_esp)) {
    $condicao_mostrar_esp   = '';
    $checked2               = 'checked';
}else {//Se essa opção estiver desmarcada, então mostro somente os PA(s) que são Normais de Linha ...
    $condicao_mostrar_esp   = " AND pa.`referencia` <> 'ESP' ";
    $checked2               = '';
}

if(!empty($chkt_mostrar_custo_ml60)) {
    $checked3               = 'checked';
}else {
    $checked3               = '';
}

if(!empty($id_familia)) {//Significa que a Consulta foi feita pelo link da Família ...
    $condicao_link = " INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` $condicao_link ";
}else {//Significa que a Consulta foi feita pelo link do Grupo ...
    $condicao_link = " AND ged.`id_grupo_pa` = '$id_grupo_pa' ";
}

$sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`mmv`, 
        (ea.`qtde_disponivel` - ea.`qtde_pendente`) AS estoque_comprometido, IF((pa.`mmv` = '0' AND (ea.`qtde_disponivel` - ea.`qtde_pendente`) = '0'), 0, IF((pa.`mmv` = '0' AND (ea.`qtde_disponivel` - ea.`qtde_pendente`) > '0'), 10000, (ea.`qtde_disponivel` - ea.`qtde_pendente`) / pa.`mmv`)) AS estoque_x_meses, 
        pa.`preco_promocional`, pa.`operacao_custo`, ged.`margem_lucro_minima` 
        FROM `estoques_acabados` ea 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` $condicao_mostrar_esp 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` = '$id_empresa_divisao' 
        $condicao_link 
        WHERE pa.`ativo` = '1' 
        $condicao_somente_estoque_maior_0 ORDER BY estoque_x_meses DESC ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relat&oacute;rio de Estoque P.A. por Grupo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' action=''>
<!--*********************************************-->
<input type='hidden' name='id_familia' value='<?=$id_familia;?>'>
<input type='hidden' name='id_grupo_pa' value='<?=$id_grupo_pa;?>'>
<input type='hidden' name='id_empresa_divisao' value='<?=$id_empresa_divisao;?>'>
<!--*********************************************-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            Relat&oacute;rio de Estoque P.A. por Grupo - 
            <font color='yellow'>
                Impressão em: 
            </font>
            <?=date('d/m/Y H:i:s');?>
            <br/>
            <input type='checkbox' name='chkt_somente_estoque_maior_0' value='S' title='Somente com Estoque > 0' id='label' class='checkbox' <?=$checked1;?>>
            <label for='label'>
                Somente com Estoque > 0
            </label>
            &nbsp;-&nbsp;
            <input type='checkbox' name='chkt_mostrar_esp' value='S' title='Mostrar ESP' id='label2' class='checkbox' <?=$checked2;?>>
            <label for='label2'>
                Mostrar ESP
            </label>
            &nbsp;
            <input type='checkbox' name='chkt_mostrar_custo_ml60' value='S' title='Mostrar Custo ML 60%' id='label3' class='checkbox' <?=$checked3;?>>
            <label for='label3'>
                Mostrar Custo ML 60%
            </label>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Ref.
        </td>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            Compra<br/> Produção
        </td>
        <td rowspan='2'>
            MMV
        </td>
        <td rowspan='2'>
            EC
        </td>
        <td colspan='3'>
            ER + OE
        </td>
        <td rowspan='2' title='Excesso de Estoque' style='cursor:help'>
            EX
        </td>
        <td colspan='2'>
            20 + 10
        </td>
        <td colspan='5'>
            Pre&ccedil;o R$
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
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
            p/ X Meses
        </td>
        <td title='Preço com 20+10 de Desconto' style='cursor:help'>
            Preço
        </td>
        <td title='Margem de Lucro' style='cursor:help'>
            M.L
        </td>
        <td title='Preço de Custo com Margem de Lucro de 60%' style='cursor:help'>
            Custo ML 60%
        </td>
        <td title='Preço Promocional' style='cursor:help'>
            Prom.B
        </td>
        <td title='Preço com Desconto (Relatório) dos ultimos 3 meses' style='cursor:help'>
            Relat&oacute;rio
        </td>
        <td title='Preço Total (Relatório)' style='cursor:help'>
            Total Relat.
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        //Fórmula do Preço Máximo Custo Fat. R$ - esse campo está aqui, mais ele é printado + abaixo ...
        if(!empty($chkt_mostrar_custo_ml60)) $preco_custo_ml_60 = custos::preco_custo_pa($campos[$i]['id_produto_acabado']);

        if($campos[$i]['referencia'] == 'ESP') {
            $preco_custo_ml_60  = 0;
            $preco_liquido      = 0;
            $preco_promocional_b= 0;
        }else {
            $preco_promocional_b= $campos[$i]['preco_promocional_b'];
            $preco_liquido      = $campos[$i]['preco_liquido'];
            
            //Em cima desse Preço Líquido, atribuo 20 + 10 de desconto ...
            $preco_liquido      = ($preco_liquido * 0.8 * 0.9);

            if($campos[$i]['operacao_custo'] == 1) {//Produto do tipo revenda então eu passo a margem de lucro dele para 60
                if(!empty($chkt_mostrar_custo_ml60)) {
                    //////////////////////////// pega o fornecedor padrao, se PA = revenda /////////////////////////////////////////////////
                    //========>>>>> Este sql abaixo é a mesma lógica da duncao procurar_fornecedor_default_revenda()
                    $id_fornecedor  = custos::procurar_fornecedor_default_revenda($campos[$i]['id_produto_acabado'], '', 1);

                    $sql = "SELECT fpi.`fator_margem_lucro_pa` 
                            FROM `fornecedores_x_prod_insumos` fpi 
                            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`ativo` = '1' AND pa.`operacao_custo` = '1' AND pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                            WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                            AND fpi.`ativo` = '1' ";
                    $campos_fator = bancos::sql($sql);
                    if(count($campos_fator) > 0) {
                        if($campos_fator[0]['fator_margem_lucro_pa'] != 0.00) {
                            $preco_custo_ml_60 = $preco_custo_ml_60 / $campos_fator[0]['fator_margem_lucro_pa'] * 1.60;
                        }else {
                            $preco_custo_ml_60 = 0;
                        }
                    }else {
                        $preco_custo_ml_60 = 0;
                    }
                }
            }
        }
        
        $estoque_produto        = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 0);
        $qtde_estoque           = $estoque_produto[0];
        $producao               = $estoque_produto[2];
        $estoque_comprometido   = $estoque_produto[8];
        $qtde_oe_em_aberto      = $estoque_produto[11];

        $compra                 = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
        <?	
            echo $campos[$i]['discriminacao'];
            if($campos[$i]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'> (TopA)</font>";
            }else if($campos[$i]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'> (TopB)</font>";
            }
        ?>
        </td>
        <td align='right'>
        <?
//Aqui verifica se o PA tem relação com o PI, caso isso não acontece não apresenta o link
            $sql = "SELECT `id_produto_insumo` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' ";
            $campos_pipa = bancos::sql($sql);
//Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
            if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 1) {
                if($font_compra == "<font color='black'>") {
    ?>
            <a href="javascript:nova_janela('../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'POP', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Compra Produção" class="link">
    <?
                }
/****************Compra****************/
                if($font_compra == "<font color='black'>") $font_compra = "<font color='#6473D4'>";//Se link, exibe em Azul ...
                echo $font_compra.number_format($compra, 2, ',', '.');
/****************Produção****************/
                if(!empty($producao) && $producao != 0) {
                    if($font_producao == "<font color='black'>") $font_producao = "<font color='#6473D4'>";//Se link, exibe em Azul ...
                    echo ' / '.$font_producao.number_format($producao, 2, ',', '.');
                }
    ?>
            </a>
    <?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
            }else if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 0) {//Não mostra o link
/****************Compra****************/
                echo $font_compra.number_format($compra, 2, ',', '.');
/****************Produção****************/
                if(!empty($producao) && $producao != 0) echo ' / '.$font_producao.number_format($producao, 2, ',', '.');
            }else {//Aqui o PA não tem relação com o PI
/****************Produção****************/
                echo $font_producao.number_format($producao, 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['mmv'], 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($estoque_comprometido, 2, '.');?>
        </td>
        <td align='right'>
        <?
            echo segurancas::number_format($qtde_estoque, 2, '.');
            $total_qtde_estoque+= $qtde_estoque;
        ?>
        </td>
        <td align='right'>
        <?
            echo segurancas::number_format($qtde_oe_em_aberto, 2, '.');
            $total_qtde_oe_em_aberto+= $qtde_oe_em_aberto;
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['mmv'] != 0) echo intval(segurancas::number_format($campos[$i]['estoque_x_meses'], 2, '.'));
        ?>
        </td>
        <td>
            <?=intval(number_format($campos[$i]['qtde_queima_estoque'], 2, ',', '.'));?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($preco_liquido, 2, '.');?>
        </td>
        <td align='right'>
        <?
            $preco_ml_zero = $preco_custo_ml_60 / 1.60;
            $margem_lucro = ($preco_liquido / $preco_ml_zero - 1) * 100;
            echo number_format($margem_lucro, 1, ',', '.');
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($preco_custo_ml_60, 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($preco_promocional_b, 2, '.');?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['preco_c_desconto_rs'], 2, '.');?>
        </td>
        <td align='right'>
        <?
            $total_parcial = ($qtde_estoque + $qtde_oe_em_aberto) * $campos[$i]['preco_c_desconto_rs'];
            $total_geral+= $total_parcial;
            if($total_parcial != 0) echo segurancas::number_format($total_parcial, 2, '.');
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td colspan='5' align='left'>
            <font color='red' size='2'>
                <b>Total:</b>
            </font>
        </td>
        <td>
            <b><?=segurancas::number_format($total_qtde_estoque, 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format($total_qtde_oe_em_aberto, 2, '.');?></b>
        </td>
        <td colspan='8' align='right'>
            <b><?='R$ '.segurancas::number_format($total_geral, 2, '.');?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' style='color:black' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>