<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Se não estiver habilitado o checkbox, só não mostra os P.A. q pertecem a família de Componentes
    if(empty($chkt_mostrar_componentes)) $condicao = " AND gpa.`id_familia` <> '23' ";

    if(!empty($chkt_est_disp_comp_zero)) {//Se tiver checked
        $condicao.= " AND (ea.qtde_disponivel - ea.qtde_pendente) < '0' ";
        $order_by = "(ea.qtde_disponivel - ea.qtde_pendente)";//aqui nao posso passar o apelido por causa da sql de paginacao ela nao intende este apelido e dar erro
    }else {
        $order_by = "pa.discriminacao ";
    }

    switch($opt_opcao) {
        case 1:
            $sql = "SELECT ged.desc_medio_pa, pa.id_produto_acabado, pa.operacao_custo, pa.referencia, pa.observacao observacao_pa, ea.id_estoque_acabado, ea.prazo_entrega, (ea.qtde_disponivel-ea.qtde_pendente) as estoque_comprometido, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao 
                    WHERE pa.`ativo` = '1' 
                    AND pa.`referencia` LIKE '%$txt_consultar%' 
                    ORDER BY $order_by ";
        break;
        case 2:           
            $sql = "SELECT ged.desc_medio_pa, pa.id_produto_acabado, pa.operacao_custo, pa.referencia, pa.observacao observacao_pa, ea.id_estoque_acabado, ea.prazo_entrega, (ea.qtde_disponivel-ea.qtde_pendente) as estoque_comprometido, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao 
                    WHERE pa.`ativo` = '1' 
                    AND pa.`discriminacao` LIKE '%$txt_consultar%' 
                    ORDER BY $order_by ";
        break;
        case 3:
            //Aqui busco todos os PA do Fornecedor, mas somente os PA que são do Tipo PI's, e q são normais de linha
            $sql = "SELECT pa.id_produto_acabado 
                    FROM `fornecedores` f
                    INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.id_fornecedor = f.id_fornecedor AND fpi.`ativo` = '1' 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`referencia` <> 'ESP' AND pa.`ativo` = '1' 
                    WHERE f.`razaosocial` LIKE '%$txt_consultar%' 
                    ORDER BY pa.id_produto_acabado ";
            //Comentei por enquanto, por causa das fórmulas q o Roberto está fazendo no Excel
            //$sql.= "and pipa.ativo = 1 order by pa.id_produto_acabado asc ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) $id_produto_acabados.=$campos[$i]['id_produto_acabado'].', ';
            $id_produto_acabados = substr($id_produto_acabados, 0, strlen($id_produto_acabados) - 2);
            //Aki é o SQL Principal, agora o Sistema só traz os PA(s) do Fornecedor digitado via consulta ...
            $sql = "SELECT ged.desc_medio_pa, pa.id_produto_acabado, pa.operacao_custo, pa.referencia, pa.observacao observacao_pa, ea.id_estoque_acabado, ea.prazo_entrega, (ea.qtde_disponivel-ea.qtde_pendente) as estoque_comprometido, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao 
                    WHERE pa.`ativo` = '1' 
                    AND pa.id_produto_acabado IN ($id_produto_acabados) 
                    ORDER BY $order_by ";
        break;
        case 4://Somente os PA que são normais de linha
            $sql = "SELECT ged.desc_medio_pa, pa.id_produto_acabado, pa.operacao_custo, pa.referencia, pa.observacao observacao_pa, ea.id_estoque_acabado, ea.prazo_entrega, (ea.qtde_disponivel-ea.qtde_pendente) as estoque_comprometido, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` = '$cmb_familia' $condicao 
                    WHERE pa.`ativo` = '1' 
                    AND pa.`referencia` <> 'ESP' 
                    ORDER BY $order_by ";
        break;
        case 5://Somente os PA que são normais de linha           
            $sql = "SELECT ged.desc_medio_pa, pa.id_produto_acabado, pa.operacao_custo, pa.referencia, pa.observacao observacao_pa, ea.id_estoque_acabado, ea.prazo_entrega, (ea.qtde_disponivel-ea.qtde_pendente) as estoque_comprometido, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div AND ged.`id_grupo_pa` = '$cmb_grupo_pa' 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao 
                    WHERE pa.`ativo` = '1' 
                    AND pa.`referencia` <> 'ESP' 
                    ORDER BY $order_by ";
        break;
        default:
            $sql = "SELECT ged.desc_medio_pa, pa.id_produto_acabado, pa.operacao_custo, pa.referencia, pa.observacao observacao_pa, ea.id_estoque_acabado, ea.prazo_entrega, (ea.qtde_disponivel-ea.qtde_pendente) as estoque_comprometido, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `estoques_acabados` ea ON ea.id_produto_acabado = pa.id_produto_acabado 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao 
                    WHERE pa.`ativo` = '1' 
                    ORDER BY $order_by ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 150, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'listagem_estoque.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Listagem de Estoque ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='1' cellspacing='0' cellpadding='0' onmouseover="total_linhas(this)" align='center'>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            Listagem de Estoque
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            <font title='Compra Produção'>
                Compra Prod
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido'>
                Est Comp
            </font>
        </td>
        <td>
            Produto
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $id_estoque_acabado     = $campos[$i]['id_estoque_acabado'];
            $operacao_custo         = $campos[$i]['operacao_custo'];
            $retorno                = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
            $compra                 = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
            $producao               = $retorno[2];
            $est_comprometido       = $retorno[8];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='right'>
        <?
            echo '&nbsp;';
            //Aqui verifica se o PA tem relação com o PI ...
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
//Aqui o PA tem relação com o PI
            if(count($campos_pipa) == 1 && $operacao_custo == 1) {
                if($compra > 0 && $producao > 0) {
                    echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                }else if($compra > 0 && $producao == 0) {
                    echo segurancas::number_format($compra, 2, '.');
                }else if($compra == 0 && $producao > 0) {
                    echo segurancas::number_format($producao, 2, '.');
                }
//O PA não tem relação com o PI
            }else {
                if($producao > 0) echo segurancas::number_format($producao, 2, '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            echo '&nbsp;';
            if($est_comprometido < 0) {
                echo "<font color='red'>".segurancas::number_format($est_comprometido, 2, '.')."</font>";
            }else {
                echo segurancas::number_format($est_comprometido, 2, '.');
            }
        ?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, 0);?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'listagem_estoque.php'" class="botao">
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Listagem de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 5; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 5;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
    document.form.cmb_familia.value     = ''
    document.form.cmb_familia.disabled  = true
    document.form.cmb_grupo_pa.value    = ''
    document.form.cmb_grupo_pa.disabled = true
}

function iniciar() {
    if(document.form.opt_opcao[3].checked == true) {//Selecionada a Opção Família
        document.form.cmb_grupo_pa.value    = ''
        document.form.cmb_familia.disabled  = false
        document.form.cmb_grupo_pa.disabled = true
        document.form.cmb_familia.focus()
        document.form.txt_consultar.disabled = true
    }else if(document.form.opt_opcao[4].checked == true) {//Selecionada a Opção Grupo
        document.form.cmb_familia.value     = ''
        document.form.cmb_familia.disabled  = true
        document.form.cmb_grupo_pa.disabled = false
        document.form.cmb_grupo_pa.focus()
        document.form.txt_consultar.disabled = true
    }else {//Selecionada uma outra Opção qualquer
        document.form.cmb_familia.value     = ''
        document.form.cmb_familia.disabled  = true
        document.form.cmb_grupo_pa.value    = ''
        document.form.cmb_grupo_pa.disabled = true
        document.form.txt_consultar.disabled = false
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
//Foi escolhida a Opção de Família ou de Grupo
    }else {
        if(document.form.opt_opcao[3].checked == true) {//Selecionada a Opção Família
            if(document.form.cmb_familia.value == '') {
                alert('SELECIONE UMA FAMÍLIA !')
                document.form.cmb_familia.focus()
                return false
            }
        }else if(document.form.opt_opcao[4].checked == true) {//Selecionada a Opção Grupo
            if(document.form.cmb_grupo_pa.value == '') {
                alert('SELECIONE UM GRUPO PA !')
                document.form.cmb_grupo_pa.focus()
                return false
            }
        }
    }
}
</script>
</head>
<body onLoad="iniciar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Listagem de Estoque
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <label for='consultar'>Consultar</label>
            <input type="text" name="txt_consultar" size="45" id="consultar" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Produtos Acabados por: Referência" id="opt1" onclick="iniciar()">
            <label for="opt1">Referência</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar Produtos Acabados por: Discriminação" id="opt2" onclick="iniciar()" checked>
            <label for="opt2">Discrimina&ccedil;&atilde;o</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type="radio" name="opt_opcao" value="3" title="Consultar Produtos Acabados por: Fornecedor" id="opt3" onclick="iniciar()">
            <label for="opt3">Fornecedor (S/ ESP)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type="radio" name="opt_opcao" value="4" title="Consultar Produtos Acabados por: Família" id="opt4" onclick="iniciar()">
            <label for="opt4">Família (S/ ESP)</label>&nbsp;
            <select name="cmb_familia" title="Selecione a Família" class="combo" disabled>
            <?
                $sql = "SELECT id_familia, nome 
                        FROM `familias` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type="radio" name="opt_opcao" value="5" title="Consultar Produtos Acabados por: Grupo P.A." id="opt5" onclick="return iniciar()">
            <label for="opt5">Grupo P.A. (S/ ESP)</label>&nbsp;
            <select name="cmb_grupo_pa" title="Selecione o Grupo P.A." class="combo" disabled>
            <?
                $sql = "SELECT id_grupo_pa, nome 
                        FROM `grupos_pas` 
                        WHERE ativo = 1 order by nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_est_disp_comp_zero' value='1' title="Estoque Disponível / Comprometido < 0 (MENOS ESP)" id="est_disp_comp_zero" class="checkbox">
            <label for="est_disp_comp_zero">Estoque Disponível / Comprometido < 0 (MENOS ESP)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type='checkbox' name='chkt_mostrar_componentes' value='1' title="Mostrar Componentes" id="mostrar_componentes" class="checkbox">
            <label for="mostrar_componentes">Mostrar Componentes</label>
        </td>
        <td width="20%">
            <input type='checkbox' id="todos" name='opcao' onClick='limpar()' value='1' title="Consultar todos os Produtos Acabados" class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>