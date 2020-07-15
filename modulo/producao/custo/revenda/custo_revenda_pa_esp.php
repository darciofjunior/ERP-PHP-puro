<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/data.php');
require('../../../../lib/custos.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $tres_meses_atras 	= data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -90), '-');
    $condicao           = (!empty($chkt_so_custos_nao_liberados)) ? " AND pa.status_custo = '0' " : '';
    //Traz todos os PA q forem = DEPTO TÉCNICO do Tipo Industrial
    if(!empty($chkt_depto_tecnico)) {//Habilitou a opção de trazer os PA = DEPTO TÉCNICO do tipo Industrial
        $sql = "SELECT pa.id_produto_acabado 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.operacao_custo = '1' $condicao 
                WHERE (ovi.`preco_liq_fat_disc` = 'DEPTO TÉCNICO' OR (pa.`referencia` = 'ESP' AND ovi.`prazo_entrega_tecnico` = '0.0')) ";
        $campos_depto_tecnico = bancos::sql($sql);
        $linhas_depto_tecnico = count($campos_depto_tecnico);
        for($i = 0; $i < $linhas_depto_tecnico; $i++) $id_produtos_acabados.= $campos_depto_tecnico[$i]['id_produto_acabado'].', ';
    }
    //Traz todos os PA normal segundo a claúsula
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pa.id_produto_acabado 
                    FROM `produtos_acabados` pa 
                    WHERE pa.discriminacao LIKE '%$txt_consultar%' 
                    AND pa.operacao_custo = '1' 
                    AND pa.ativo = '1' 
                    AND pa.referencia = 'ESP' 
                    $condicao ORDER BY pa.discriminacao ";
        break;
        default:
            $sql = "SELECT pa.id_produto_acabado 
                    FROM `produtos_acabados` pa 
                    WHERE pa.operacao_custo = '1' 
                    AND pa.ativo = '1' 
                    AND pa.referencia = 'ESP' 
                    $condicao ORDER BY pa.discriminacao ";
        break;
    }
    $campos_revenda = bancos::sql($sql);
    $linhas_revenda = count($campos_revenda);
//Unifica os PA -> DEPTO TÉCNICO do Tipo Revenda com os da cláusula normal
    for($i = 0; $i < $linhas_revenda; $i++) $id_produtos_acabados.= $campos_revenda[$i]['id_produto_acabado'].', ';
    $id_produtos_acabados = (!empty($id_produtos_acabados)) ? substr($id_produtos_acabados, 0, strlen($id_produtos_acabados) - 2) : 0;

    if($chkt_pas_com_orcamento == 1) {
        /*Aqui eu verifico se dos PA´s que foram retornardos dos SQL´s acima, existem aqueles q estão 
        vinculados a algum orçamento Não Congelado e dos últimos 90 dias, pois só estes que me interessam ...*/
        $sql = "SELECT pa.id_produto_acabado 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ovi.id_orcamento_venda = ov.id_orcamento_venda AND ov.congelar = 'N' AND ov.data_emissao > '$tres_meses_atras' 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.operacao_custo = '1' $condicao 
                        WHERE ovi.id_produto_acabado IN ($id_produtos_acabados) ";
        $campos_pas_com_orcs = bancos::sql($sql);
        $linhas_pas_com_orcs = count($campos_pas_com_orcs);
        for($i = 0; $i < $linhas_pas_com_orcs; $i++) $id_pas_com_orcs.= $campos_pas_com_orcs[$i]['id_produto_acabado'].', ';
        $id_pas_com_orcs = (!empty($id_pas_com_orcs)) ? substr($id_pas_com_orcs, 0, strlen($id_pas_com_orcs) - 2) : 0;
        $condicao_pas = " pa.id_produto_acabado IN ($id_pas_com_orcs) ";
    }else {
        $condicao_pas = " pa.id_produto_acabado IN ($id_produtos_acabados) ";
    }
    //Select Principal ...
    $sql = "SELECT pa.id_produto_acabado, pa.id_gpa_vs_emp_div, pa.id_funcionario, pa.operacao, pa.operacao_custo, pa.operacao_custo_sub, pa.peso_unitario, 
            pa.pa_migrado, pa.observacao, DATE_FORMAT(SUBSTRING(pa.data_sys, 1, 10), '%d/%m/%Y') AS data_inclusao, ed.razaosocial, gpa.nome 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
            WHERE $condicao_pas ORDER BY pa.discriminacao ";
    $campos_principal = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas_principal = count($campos_principal);
    if($linhas_principal == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'custo_revenda_pa_esp.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Custo Revenda - (PA Especial) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <font color='#00FF00' size='2'>
                <b>CUSTO REVENDA - (PA Especial)</b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Grupo P.A.
        </td>
        <td>
            <font title='Qtde do Último Orçamento' style='cursor:help'>
                Qtde
            </font>
        </td>
        <td>
            Produto
        </td>
        <td>
            N.º Orc(s) <br/>em Aberto
        </td>
        <td>
            Data Inclusão
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            <font title='Fator Margem de Lucro' style='cursor:help'>
                F. M. L
            </font>
        </td>
    </tr>
<?
/*Listagem de Todos os Itens que são 'ESP', que o Prazo do P.A. = 'Depart Técnico', O.C. = 'Revenda' e que o Custo esteja liberado 
- O Sistema só lista orçamentos que não estejam congelados ...*/
        $sql = "SELECT pa.id_produto_acabado 
                FROM `produtos_acabados` pa 
                WHERE pa.referencia = 'ESP' 
                AND pa.ativo = '1' 
                AND pa.status_custo = '1' 
                AND pa.operacao_custo = '1' ORDER BY pa.id_produto_acabado ";
        $campos_pas = bancos::sql($sql);
        $linhas_pas = count($campos_pas);
        for($i = 0; $i < $linhas_pas; $i++) $id_pas_custo_liberado.= $campos_pas[$i]['id_produto_acabado'].', ';
        $id_pas_custo_liberado = (!empty($id_pas_custo_liberado)) ? substr($id_pas_custo_liberado, 0, strlen($id_pas_custo_liberado) - 2) : 0;
        $condicao_pas_custo_liberado.= " AND ovi.id_produto_acabado IN ($id_pas_custo_liberado) ";

        $sql = "SELECT ovi.id_produto_acabado 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ovi.id_orcamento_venda = ov.id_orcamento_venda AND ov.congelar = 'N' AND ov.data_emissao > '$tres_meses_atras' 
                WHERE ovi.prazo_entrega_tecnico = '0.0' 
                $condicao_pas_custo_liberado ORDER BY ovi.id_orcamento_venda LIMIT 20 ";
        $campos_esp_custo_liberado = bancos::sql($sql);
        $linhas_esp_custo_liberado = count($campos_esp_custo_liberado);
        if($linhas_esp_custo_liberado > 0) {
            for($i = 0;  $i < $linhas_esp_custo_liberado; $i++) {
?>
    <tr onclick="window.location = 'custo_revenda_pa_esp2.php?id_produto_acabado=<?=$campos_esp_custo_liberado[$i]['id_produto_acabado'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" class='linhanormal' align='center'>
        <td>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT ed.razaosocial, gpa.nome, pa.id_funcionario, pa.operacao, pa.operacao_custo, pa.operacao_custo_sub, pa.peso_unitario, 
                    pa.pa_migrado, pa.observacao, DATE_FORMAT(SUBSTRING(pa.data_sys, 1, 10), '%d/%m/%Y') AS data_inclusao  
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE pa.id_produto_acabado = '".$campos_esp_custo_liberado[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_dados_pa = bancos::sql($sql);
            echo $campos_dados_pa[0]['nome'].' / '.$campos_dados_pa[0]['razaosocial'];
        ?>
        </td>
        <td>
            <?=number_format($campos_esp_custo_liberado[$i]['qtde'], 0, ',', '.');?>
        </td>
        <td align='left'>
                    <?=intermodular::pa_discriminacao($campos_esp_custo_liberado[$i]['id_produto_acabado']);?>
        </td>
        <td align='center'>
        <?
            //Aqui eu verifico os Orc(s) dos Últimos 3 meses em aberto que contém esse PA ...
            $sql = "SELECT DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, ovi.id_orcamento_venda, l.login 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.status < '2' AND ov.data_emissao >= '$tres_meses_atras' 
                    INNER JOIN `funcionarios` f ON f.id_funcionario = ov.id_funcionario 
                    INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
                    WHERE ovi.id_produto_acabado = ".$campos_esp_custo_liberado[$i]['id_produto_acabado']." 
                    AND ovi.prazo_entrega_tecnico = '0.0' ORDER BY ov.id_orcamento_venda DESC LIMIT 1 ";
            $campos_orcs = bancos::sql($sql);
            $linhas_orcs = count($campos_orcs);
            if($linhas_orcs == 0) {
                echo '<center> - </center>';
            }else {
                echo $campos_orcs[0]['data_emissao'].' | '.$campos_orcs[0]['id_orcamento_venda'].'<font color="blue"> ('.$campos_orcs[0]['login'].')</font><br> ';
            }
        ?>
        </td>
        <td align='center'>
        <?
        //Se for Diferente de 00/00/0000, então a Data Normal
            if($campos_esp_custo_liberado[$i]['data_inclusao'] != '00/00/0000') {
                if($campos_esp_custo_liberado[$i]['id_funcionario'] != 0) {
//Aqui eu busco qual foi o login responsável pela Inclusão ou Alteração do Prod
                $sql = "SELECT l.login 
                        FROM `funcionarios` f 
                        INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                        WHERE f.`id_funcionario` = ".$campos_esp_custo_liberado[$i]['id_funcionario']." LIMIT 1 ";
                $campos_login = bancos::sql($sql);
?>
                <font title="Responsável pela alteração: <?=$campos_login[0]['login'];?>"><?=$campos_esp_custo_liberado[$i]['data_inclusao']?></font>
<?
                }else {
                    echo $campos_esp_custo_liberado[$i]['data_inclusao'];
                }
            }
        ?>
        </td>
        <td align='left'>
        <?
            //Verifico se existem Fornecedores atrelados p/ o PA do Loop "PIPA" ...
            $sql = "SELECT f.id_fornecedor, f.razaosocial, fpi.fator_margem_lucro_pa 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` AND pa.`id_produto_insumo` > '0' AND fpi.`ativo` = '1' 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor 
                    WHERE pa.`id_produto_acabado` = '".$campos_esp_custo_liberado[$i]['id_produto_acabado']."' 
                    AND pa.`ativo` = '1' ";
            $campos_lista_preco = bancos::sql($sql);
            $linhas_lista_preco = count($campos_lista_preco);
            $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($campos_esp_custo_liberado[$i]['id_produto_acabado'], '', 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
            if($linhas_lista_preco > 0) {//Encontrou
                for($j = 0; $j < $linhas_lista_preco; $j++) {
                    echo "<font color='red'>-> </font>";
                    if($campos_lista_preco[$j]['id_fornecedor'] == $id_fornecedor_setado) {
                        echo "<b title='Fornecedor default'>".$campos_lista_preco[$j]['razaosocial']."</b>";
                    }else {
                        echo $campos_lista_preco[$j]['razaosocial'];
                    }
                    echo '<br>';
                }
            }else {//Não encontrou
                echo "<font color='red'>SEM FORNECEDOR</font>";
            }
        ?>
        </td>
        <td align='center'>
        <?
                if($linhas_lista_preco > 0) {//Encontrou
                    for($j = 0; $j < $linhas_lista_preco; $j++) {
        ?>
                    <?=number_format($campos_lista_preco[$j]['fator_margem_lucro_pa'], 2, ',', '.').'<br>';?>
        <?
                    }
                }else {//Não encontrou
        ?>
                    <font color="red">-</font>
        <?
                }
        ?>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal'>
        <td colspan='8' bgcolor='#CECECE'>
            <font color='white' size='1'>
                <b>Total de Item(ns): 
                <font color='darkblue'>
                    <b><?=$linhas_esp_custo_liberado;?></b>
                </font> 
                <font color='black'><b>sem Prazo de Entrega</b></font> nos Últimos 3 meses
            </font>
        </td>
    </tr>
<?
        }
//Listagem de Itens do SQL Principal ...
        for ($i = 0;  $i < $linhas_principal; $i++) {
?>
    <tr onclick="window.location = 'custo_revenda_pa_esp2.php?id_produto_acabado=<?=$campos_principal[$i]['id_produto_acabado'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" class='linhanormal' align='center'>
        <td>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <?=$campos_principal[$i]['nome'];?>
        </td>
        <td>
        <?
            //Busca o último Orçamento com a sua qtde Orçada desse PA do Loop ...
            $sql = "SELECT qtde, id_orcamento_venda 
                    FROM `orcamentos_vendas_itens` 
                    WHERE `id_produto_acabado` = '".$campos_principal[$i]['id_produto_acabado']."' ORDER BY id_orcamento_venda DESC LIMIT 1 ";
            $campos_orcamento = bancos::sql($sql);
            if(count($campos_orcamento) == 1) {//Achou esse item em algum orçamento
        ?>
            <font title='Orçamento N.º <?=$campos_orcamento[0]['id_orcamento_venda'];?>'>
                <?=number_format($campos_orcamento[0]['qtde'], 0, ',', '.');?>
            </font>
        <?
            }else {//Não achou esse item
        ?>
            <font title='Item sem Orçamento'>
                    0
            </font>
        <?
            }
?>
        </td>
        <td align='left'>
        <?
            if($campos_principal[$i]['status_custo'] == 1) {//Já está liberado
        ?>
            <font title="Custo Liberado">
        <?
                    echo intermodular::pa_discriminacao($campos_principal[$i]['id_produto_acabado']);
            }else {//Não está liberado
        ?>
            <font title="Custo não Liberado" color="red">
        <?
                echo intermodular::pa_discriminacao($campos_principal[$i]['id_produto_acabado']);
        ?>
            </font>
        <?
            }
        ?>
        </td>
        <td align='center'>
        <?
            //Aqui eu verifico os Orc(s) dos Últimos 3 meses em aberto que contém esse PA ...
            $sql = "SELECT DATE_FORMAT(ov.data_emissao, '%d/%m/%Y') AS data_emissao, ovi.id_orcamento_venda, l.login 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.status < '2' AND ov.data_emissao >= '$tres_meses_atras' 
                    INNER JOIN `funcionarios` f ON f.id_funcionario = ov.id_funcionario 
                    INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
                    WHERE ovi.id_produto_acabado = ".$campos_principal[$i]['id_produto_acabado']." ORDER BY ov.id_orcamento_venda DESC LIMIT 1 ";
            $campos_orcs = bancos::sql($sql);
            $linhas_orcs = count($campos_orcs);
            if($linhas_orcs == 0) {
                echo '<center> - </center>';
            }else {
                echo $campos_orcs[0]['data_emissao'].' | '.$campos_orcs[0]['id_orcamento_venda'].'<font color="blue"> ('.$campos_orcs[0]['login'].')</font><br> ';
            }
        ?>
        </td>
        <td align='center'>
        <?
            //Se for Diferente de 00/00/0000, então a Data Normal
            if($campos_principal[$i]['data_inclusao'] != '00/00/0000') {
                if($campos_principal[$i]['id_funcionario'] != 0) {
                //Aqui eu busco qual foi o login responsável pela Inclusão ou Alteração do Prod ...
                $sql = "SELECT l.login 
                        FROM `funcionarios` f 
                        INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
                        WHERE f.id_funcionario = ".$campos_principal[$i]['id_funcionario']." LIMIT 1 ";
                $campos_login = bancos::sql($sql);
        ?>
                <font title="Responsável pela alteração: <?=$campos_login[0]['login'];?>"><?=$campos_principal[$i]['data_inclusao']?></font>
        <?
                }else {
                        echo $campos_principal[$i]['data_inclusao'];
                }
            }
        ?>
        </td>
        <td align='left'>
        <?
            //Verifico se existem Fornecedores atrelados p/ o PA do Loop "PIPA" ...
            $sql = "SELECT f.id_fornecedor, f.razaosocial, fpi.fator_margem_lucro_pa 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` AND fpi.`ativo` = '1' 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor 
                    WHERE pa.`id_produto_acabado` = '".$campos_principal[$i]['id_produto_acabado']."' 
                    AND pa.`id_produto_insumo` > '0' 
                    AND pa.`ativo` = '1' ";
            $campos_lista_preco = bancos::sql($sql);
            $linhas_lista_preco = count($campos_lista_preco);
            $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($campos_principal[$i]['id_produto_acabado'], '', 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
            if($linhas_lista_preco > 0) {//Encontrou
                for($j = 0; $j < $linhas_lista_preco; $j++) {
                    echo "<font color='red'>-> </font>";
                    if($campos_lista_preco[$j]['id_fornecedor'] == $id_fornecedor_setado) {
                        echo "<b title='Fornecedor default'>".$campos_lista_preco[$j]['razaosocial']."</b>";
                    }else {
                        echo $campos_lista_preco[$j]['razaosocial'];
                    }
                    echo '<br>';
                }
            }else {//Não encontrou
                echo "<font color='red'>SEM FORNECEDOR</font>";
            }
        ?>
        </td>
        <td align='center'>
        <?
            if($linhas_lista_preco > 0) {//Encontrou
                for($j = 0; $j < $linhas_lista_preco; $j++) {
        ?>
                    <?=number_format($campos_lista_preco[$j]['fator_margem_lucro_pa'], 2, ',', '.').'<br>';?>
        <?
                }
            }else {//Não encontrou
        ?>
            <font color='red'>-</font>
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'custo_revenda_pa_esp.php'" class='botao'>
            <input type='submit' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='razaosocial' value="<?=$razaosocial;?>">
</form>
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
<title>.:: Custo Revenda - (PA Especial) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value       = ''
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function iniciar() {
    if(document.form.txt_consultar.disabled == false) document.form.txt_consultar.focus()
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
<body onload='iniciar()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='razaosocial' value='<?=$razaosocial;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <font color='#00FF00' size='2'>
                <b>CUSTO REVENDA - (PA Especial)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Produtos Insumos por: Discrimina&ccedil;&atilde;o' onclick='iniciar()' id='label1' checked disabled>
            <label for='label1'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' title='Só Custos não Liberados' id='label2' class='checkbox' checked>
            <label for='label2'>
                Só Custos não Liberados
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='chkt_depto_tecnico' value='1' title='Consultar todos os DEPTO. TÉCNICO' onclick='limpar()' id='label3' class='checkbox' checked>
            <label for='label3'>
                Todos os DEPTO. TÉCNICO
            </label>
        </td>
        <td>
            <input type='checkbox' name='chkt_pas_com_orcamento' value='1' title='Consultar Somente PA(s) com Orçamento' id='label4' class='checkbox' checked>
            <label for='label4'>
                Somente PA(s) com Orçamento
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os registros' onclick='limpar()' class='checkbox' checked id='label5'>
            <label for='label5'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick='document.form.opcao.checked = true;limpar();' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>