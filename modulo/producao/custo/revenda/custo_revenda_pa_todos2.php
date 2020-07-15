<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/producao/custo/revenda/custo_revenda_pa_todos.php', '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $id_fornecedor          = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_fornecedor'] : $_GET['id_fornecedor'];
    $condicao_status_custo  = (!empty($chkt_so_custos_nao_liberados)) ? " AND pa.`status_custo` = '0' " : '';
    
    switch($opt_opcao) {
        case 1://Referência
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.status_custo, pa.id_funcionario, DATE_FORMAT(SUBSTRING(pa.`data_sys`, 1, 10), '%d/%m/%Y') AS data_inclusao, ed.razaosocial, gpa.nome, fpi.id_fornecedor_prod_insumo, fpi.fator_margem_lucro_pa 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    INNER JOIN `grupos_pas` gpa ON ged.`id_grupo_pa` = gpa.`id_grupo_pa` 
                    WHERE pa.`referencia` LIKE '%$txt_consultar%' 
                    AND fpi.`id_fornecedor` = '$id_fornecedor' 
                    AND pa.`id_produto_insumo` > '0' 
                    AND pa.`ativo` = '1' 
                    AND pa.`operacao_custo` = '1' 
                    AND fpi.`ativo` = '1' $condicao_status_custo ORDER BY pa.discriminacao ";
        break;
        case 2://Discriminação
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.status_custo, pa.id_funcionario, DATE_FORMAT(SUBSTRING(pa.`data_sys`, 1, 10), '%d/%m/%Y') AS data_inclusao, ed.razaosocial, gpa.nome, fpi.id_fornecedor_prod_insumo, fpi.fator_margem_lucro_pa 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    INNER JOIN `grupos_pas` gpa ON ged.`id_grupo_pa` = gpa.`id_grupo_pa` 
                    WHERE pa.`discriminacao` LIKE '%$txt_consultar%' 
                    AND fpi.`id_fornecedor` = '$id_fornecedor' 
                    AND pa.`id_produto_insumo` > '0' 
                    AND pa.`ativo` = '1' 
                    AND pa.`operacao_custo` = '1' 
                    AND fpi.`ativo` = '1' $condicao_status_custo ORDER BY pa.discriminacao ";
        break;
        default://Todos
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.status_custo, pa.id_funcionario, DATE_FORMAT(SUBSTRING(pa.`data_sys`, 1, 10), '%d/%m/%Y') AS data_inclusao, ed.razaosocial, gpa.nome, fpi.id_fornecedor_prod_insumo, fpi.fator_margem_lucro_pa 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    INNER JOIN `grupos_pas` gpa ON ged.`id_grupo_pa` = gpa.`id_grupo_pa` 
                    WHERE fpi.id_fornecedor = '$id_fornecedor' 
                    AND pa.`id_produto_insumo` > '0' 
                    AND pa.`ativo` = '1' 
                    AND pa.`operacao_custo` = '1' 
                    AND fpi.`ativo` = '1' $condicao_status_custo ORDER BY pa.discriminacao ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'custo_revenda_pa_todos2.php?id_fornecedor=<?=$id_fornecedor;?>&valor=1'
    </Script>
<?
    }else {
        $sql = "SELECT id_pais 
                FROM `fornecedores` 
                WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
        $campos_fornecedor  = bancos::sql($sql);
        $id_pais            = $campos_fornecedor[0]['id_pais'];
?>
<html>
<head>
<title>.:: Custo Revenda - (Todos PAs) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <font color='#00FF00' size='2'>
                <b>CUSTO REVENDA - (Todos PAs)</b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='8'>
            Produto(s) Atrelado(s) p/ o Fornecedor => 
            <font color='yellow'>
            <?
                $sql = "SELECT razaosocial 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
                $campos_fornecedor = bancos::sql($sql);
                echo $campos_fornecedor[0]['razaosocial'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Discriminação
        </td>
        <td>
            <font title='Grupo do PA / Empresa Divisão' style='cursor:help'>
                Grupo /<br/>Divisão
            </font>
        </td>
        <td>
            <font title='Data de Inclusão' style='cursor:help'>
                Data Inc
            </font>
        </td>
        <td>
            <font title='Fator Margem de Lucro' style='cursor:help'>
                F. M. L
            </font>
        </td>
        <td>
            <font title='Quantidade de Pçs. / Embalagem' style='cursor:help'>
                Qtde Pçs. / Emb
            </font>
        </td>
        <td>
            Preço Fat. <br/>Nac. Min. R$
        </td>
        <td>
            Preço Fat. <br/>Inter. Min. R$
        </td>
        <td>
            Custo PA <br/>Indust. R$
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($campos[$i]['id_produto_acabado'], '', 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <a href="javascript:nova_janela('custo_revenda.php?id_fornecedor_prod_insumo=<?=$campos[$i]['id_fornecedor_prod_insumo'];?>', 'CUSTO_REVENDA', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Custo Revenda' class='link'>
            <?
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);
                if($id_fornecedor == $id_fornecedor_setado) echo ' <font color="red">(DEFAULT)</font>';
            ?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
        <td align='center'>
        <?
            //Se for Diferente de 00/00/0000, então a Data Normal
            if($campos[$i]['data_inclusao'] != '00/00/0000') echo $campos[$i]['data_inclusao'];
        ?>
        </td>
        <td align='center'>
            <?=number_format($campos[$i]['fator_margem_lucro_pa'], 2, ',', '.');?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT pi.discriminacao, ppe.pecas_por_emb, ppe.embalagem_default 
                    FROM `pas_vs_pis_embs` ppe 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
                    WHERE ppe.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ORDER BY pi.discriminacao ";
            $campos_produto_insumo = bancos::sql($sql);
            $linhas_produto_insumo = count($campos_produto_insumo);
            if($linhas_produto_insumo > 0) {
                for($j = 0; $j < $linhas_produto_insumo; $j++) {
                    if($campos_produto_insumo[$j]['embalagem_default'] == 1) {//Principal
        ?>
                    <img src="../../../../imagem/certo.gif">
                    <font title="Embalagem Principal">
        <?
                        echo '<b>* </b>'.$campos_produto_insumo[$j]['pecas_por_emb'].' - '.$campos_produto_insumo[$j]['discriminacao'].'<br>';
        ?>
                    </font>
        <?
                    }else {
                        echo '<b>* </b>'.$campos_produto_insumo[$j]['pecas_por_emb'].' - '.$campos_produto_insumo[$j]['discriminacao'].'<br>';
        ?>
                    <!--<font color="red">
                        <b>* </b><?=$campos_produto_insumo[$j]['pecas_por_emb'].' - '.$campos_produto_insumo[$j]['discriminacao'];?><br>
                    </font>-->
        <?
                    }
                }
            }else {
                echo '<p align="center">&nbsp;-&nbsp;</p>';
            }
        ?>
        </td>
        <td>
        <?
            $valores = custos::preco_custo_pa($campos[$i]['id_produto_acabado'], '', 'S');
            echo number_format($valores['preco_venda_fat_nac_min_rs'], 2, ',', '.');
        ?>
        </td>
        <td>
            <?=number_format($valores['preco_venda_fat_inter_min_rs'], 2, ',', '.');?>
        </td>
        <td>
            <?
                $custo_industrial = custos::todas_etapas($campos[$i]['id_produto_acabado'], 1);
            ?>
            <!--Aqui eu também passo o id_pais, porque se o País for nacional "31", eu não posso ficar bloqueando 
            e desbloqueando o Custo-->
            <a href="javascript:nova_janela('../industrial/custo_industrial.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&id_pais=<?=$id_pais;?>&pop_up=1', 'CUSTO_INDUSTRIAL', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes do Custo do P.A.' class='link'>
                <?=number_format($custo_industrial, 2, ',', '.');?>
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'custo_revenda_pa_todos2.php?id_fornecedor=<?=$id_fornecedor;?>'" class='botao'>
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
<title>.:: Custo Revenda - (Todos PAs) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
    document.form.txt_consultar.value   = ''
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<table width='70%' border='0' align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <font color='#00FF00' size='2'>
                <b>CUSTO REVENDA - (Todos PAs)</b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Consultar Produto(s) Atrelado(s) p/ o Fornecedor => 
            <font color='yellow'>
            <?
                $sql = "SELECT razaosocial 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
                $campos_fornecedor = bancos::sql($sql);
                echo $campos_fornecedor[0]['razaosocial'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Produtos Insumos por: Referência' onclick='document.form.txt_consultar.focus()' id='label'>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar Produtos Insumos por: Discrimina&ccedil;&atilde;o' onclick='document.form.txt_consultar.focus()' id='label2' checked>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' title='Só Custos não Liberados' id='label3' class='checkbox'>
            <label for='label3'>
                Só Custos não Liberados
            </label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os Produtos Insumos' onclick='limpar()' id='label4' class='checkbox'>
            <label for='label4'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'custo_revenda_pa_todos.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color='red'><b>Observação:</b></font>

* Traz somente P.A(s) do:

<b>* Tipo PI (PRAC) que estejam relacionados com o PIPA;</b>
<b>* Tipo de O.C. = Revenda;</b>
<b>* Fornecedor selecionado que estejam ativos.</b>
</pre>