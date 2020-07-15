<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
require('../../../lib/intermodular.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) COMPRA(S) PARA ESSA FAMÍLIA.</font>";

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente     = $_POST['id_cliente'];
    $cmb_familia    = $_POST['cmb_familia'];
}else {
    $id_cliente     = $_GET['id_cliente'];
    $cmb_familia    = $_GET['cmb_familia'];
}

$colspan_principal = 2;//Isso porque estou contando a primeira e última coluna que são fixas ...
for($ano = 2006; $ano <= date('Y'); $ano++) $colspan_principal++;//Vai somando cada ano ...
?>
<html>
<head>
<title>.:: Relatório de Vendas por Referência vs Ano ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
</Script>
</head>
<body>
<form name='form' action='' method='post'>
<input type='hidden' name='id_cliente' value="<?=$id_cliente;?>">
<?
    if($cmb_familia[0] == '') {
        $familias_selecionadas = '%';
        $selected = 'selected';
    }else {
        if(is_array($cmb_familia)) {
            for($i = 0; $i < count($cmb_familia); $i++) {
                if($cmb_familia[$i] != '') $familias_selecionadas.= $cmb_familia[$i].', ';
            }
        }else {
            $familias_selecionadas.= $cmb_familia.', ';
        }
        $familias_selecionadas 	= substr($familias_selecionadas, 0, strlen($familias_selecionadas) - 2);
    }
    if($familias_selecionadas == '%') {
        $condicao_familia = " AND gpa.`id_familia` LIKE '$familias_selecionadas' ";
    }else {
        $condicao_familia = " AND gpa.`id_familia` IN ($familias_selecionadas) ";
    }
    $cmb_familia_filtro = (!empty($cmb_familia)) ? $cmb_familia : '%';
/************************Produtos Vendidos para o Cliente nos últimos 5 anos**********************/
    $sql = "SELECT SUM(nfsi.`qtde` - nfsi.`qtde_devolvida`) AS qtde_anual, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
            YEAR(nfs.`data_emissao`) AS ano, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente 
            FROM `clientes` c 
            INNER JOIN `nfs` ON nfs.`id_cliente` = c.`id_cliente` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` AND pa.`ativo` = '1' 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` $condicao_familia 
            WHERE nfs.`id_cliente` = '$id_cliente' 
            GROUP BY pa.`id_produto_acabado`, ano ORDER BY pa.`discriminacao`, ano ";
    $campos	= bancos::sql($sql);
    $linhas	= count($campos);
?>
<table width='90%' border='1' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <!--******Essas 3 linhas é p/ não dar erro na cor do JavaScript do CSS quando clicar no Checkbox******-->
    <!--**************************************************************************************************-->
    <tr class='linhacabecalho' align='center'>
        <td colspan='<?=$colspan_principal;?>'>
            Relatório de Vendas por Referência vs Ano<br>
            PRODUTOS COMPRADOS PELO CLIENTE: 
            <font color='black' size='-1'>
                    <?=$campos[0]['cliente'];?>
            </font>
            <br>
            <font size='2'>
                <b>Família: </b>
            </font>
            &nbsp;
            <select name='cmb_familia[]' title='Selecione uma Família' onchange='controlar_combo(this)' multiple size='5' class='combo'>
                <option value='' style='color:red' <?=$selected;?>>SELECIONE</option>
                <?
                        $sql = "SELECT `id_familia`, UPPER(`nome`) AS nome 
                                FROM `familias` 
                                WHERE ativo = '1' ORDER BY `nome` ";
                        $campos_familia = bancos::sql($sql);
                        $linhas_familia = count($campos_familia);
                        for($i = 0; $i < $linhas_familia; $i++) {
                            $selected = '';//Limpo a variável para não herdar valor do Loop Anterior ...
                            if(is_array($cmb_familia)) {
                                if(in_array($campos_familia[$i]['id_familia'], $cmb_familia)) $selected = 'selected';
                            }else {
                                if($campos_familia[$i]['id_familia'] == $cmb_familia) $selected = 'selected';
                            }
                ?>
                <option value='<?=$campos_familia[$i]['id_familia'];?>' <?=$selected;?>><?=$campos_familia[$i]['nome'];?></option>
                <?
                        }
                ?>
            </select>
            &nbsp;
            <input type='submit' name='cmd_atualizar' value='Atualizar' title='Atualizar' class='botao'>
        </td>
    </tr>
<?
    if($linhas > 0) {//Existe pelo menos uma Compra ...
?>
    <tr align='center'>
        <td rowspan='2' class='linhadestaque'>
            Produto Acabado
        </td>
        <?
            for($ano = 2006; $ano <= date('Y'); $ano++) $colspan++;
        ?>
        <td colspan="<?=$colspan;?>" class='linhadestaque'>
            Quantidade Comprada
        </td>
        <td rowspan='2' class='linhadestaque'>
            Média dos Ultimos 5 Anos
        </td>
    </tr>
    <tr align='center'>
    <?
        for($ano = 2006; $ano <= date('Y'); $ano++) echo '<td class="linhadestaque">'.$ano.'</td>';
    ?>
    </tr>
    <?
        $vetor_pa = array();
        for($i = 0; $i < $linhas; $i++) {
            //Verifico se existe o PA no Array criado acima ...
            if(!in_array($campos[$i]['id_produto_acabado'], $vetor_pa)) $vetor_pa[] = $campos[$i]['id_produto_acabado'];
            $vetor_valores[$campos[$i]['id_produto_acabado']][$campos[$i]['ano']] = $campos[$i]['qtde_anual'];
        }
        $linhas_pa = count($vetor_pa);

        for($i = 0; $i < $linhas_pa; $i++) {
            //Zera para não mudar valor do looping anterior...
            $soma_ultimos_5anos_cheios = 0;
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=intermodular::pa_discriminacao($vetor_pa[$i], 0);?>
        </td>
        <?
            for($ano = 2006; $ano <= date('Y'); $ano++) {
                if($vetor_valores[$vetor_pa[$i]][$ano] > 0) {
        ?>
        <td align='right'>
                <?=number_format($vetor_valores[$vetor_pa[$i]][$ano], 0, '', '.');?>
        </td>	
        <?			
                }else {
                    echo '<td>&nbsp;</td>';
                }
                if(($ano == date('Y') - 1) || ($ano == date('Y') - 2) || ($ano == date('Y') - 3) || ($ano == date('Y') - 4) || ($ano == date('Y') - 5)) {
                    $soma_ultimos_5anos_cheios+= $vetor_valores[$vetor_pa[$i]][$ano];
                }
            }
        ?>
        <td align='right'>
            <?=number_format($soma_ultimos_5anos_cheios / 3, 0, '', '.');?>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='<?=$colspan_principal;?>'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window:print()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
<?
    }else {//Significa que não existe nenhuma Compra do Cliente ...
?>
<table width='90%' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
</table>
<?
    }
?>
</form>
</body>
</html>