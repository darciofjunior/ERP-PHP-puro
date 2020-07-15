<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
session_start('funcionarios');
$valor_dolar_dia = genericas::moeda_dia('dolar');

if($passo == 1) {
//Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_gpa_vs_emp_div  = $_POST['id_gpa_vs_emp_div'];
        $data_inicial       = $_POST['data_inicial'];
        $data_final         = $_POST['data_final'];
        $sumir_botao        = $_POST['sumir_botao'];
        $id_produto_acabado = $_POST['id_produto_acabado'];
        $passo              = $_POST['passo'];
    }else {
        $id_gpa_vs_emp_div  = $_GET['id_gpa_vs_emp_div'];
        $data_inicial       = $_GET['data_inicial'];
        $data_final         = $_GET['data_final'];
        $sumir_botao        = $_GET['sumir_botao'];
        $id_produto_acabado = $_GET['id_produto_acabado'];
        $passo              = $_GET['passo'];
    }
//Se a Combo de Período foi alterada então ...
    if(!empty($cmb_periodo)) {
        if($cmb_periodo == 6) {//Busca no Período de 6 Meses ...
            $data_inicial   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
            $data_final     = date('Y-m-d');//Sempre será a Data Atual ...
        }else if($cmb_periodo == 12) {//Busca no Período de 12 Meses (1 Ano) ...
            $data_inicial   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-');
            $data_final     = date('Y-m-d');//Sempre será a Data Atual ...
        }else {//Exibe a opção Todos - no caso de ESP e não existe período ... 
            $data_inicial   = '';
            $data_final     = '';//Sempre será a Data Atual ...
        }
    }else {//Quando a carrega a Tela, o Default é de 6 Meses, quando ñ é passado algum parâm ...
        if(empty($data_inicial)) {
//O Período sugerido pra consulta é de 6 Meses ...
            $data_inicial   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-');
            $data_final     = date('Y-m-d');//Sempre será a Data Atual ...
            $cmb_periodo    = 6;
        }
    }
//Se existir Datas ...
    if(!empty($data_inicial)) $condicao_datas = " AND ov.data_emissao BETWEEN '$data_inicial' AND '$data_final' ";
//Aqui eu busco todos os Itens de Pedidos que estão atrelados a este Produto ...
    $sql = "SELECT IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente, c.credito, ov.id_orcamento_venda, ov.id_cliente, ov.nota_sgd, date_format(ov.data_emissao, '%d/%m/%Y') as data_emissao, ov.prazo_a, ov.prazo_b, ov.prazo_c, ov.prazo_d, ovi.*, pa.id_produto_acabado, pa.operacao_custo, pa.operacao, pa.peso_unitario, pa.observacao 
            FROM `produtos_acabados` pa 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_produto_acabado = pa.id_produto_acabado 
            INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
            INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
            WHERE pa.`id_produto_acabado` = '$id_produto_acabado' $condicao_datas 
            ORDER BY ov.data_emissao DESC, ovi.qtde ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Orçamento(s) que contém esse Produto atrelado ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function voltar() {
    document.form.submit()
}

function alterar_periodo() {
    document.form.passo.value = 1
    document.form.submit()
}
</Script>
</head>
<body onload="ajax('listar_orcamentos.php', 'div_listar_orcamentos')">
<form name='form' method='post' action=''>
<!--****************Controles da Tela do JavaScript*****************-->
<input type='hidden' name='id_gpa_vs_emp_div' value='<?=$id_gpa_vs_emp_div;?>'>
<input type='hidden' name='data_inicial' value='<?=$data_inicial;?>'>
<input type='hidden' name='data_final' value='<?=$data_final;?>'>
<input type='hidden' name='sumir_botao' value='<?=$sumir_botao;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='passo'>
<!--****************************************************************-->
<table width='90%' border='1' align='center' cellspacing='0' cellpadding='0'>
    <tr class="linhacabecalho" align='center'>
        <td colspan="8">
            Orçamento(s) que contém esse Produto atrelado - 
            <font color='yellow'>
                Período de 
<?
/*Esse parâmetro, vem de outros arquivos que solicitam essa tela, sendo assim eu controle esse botão p/
aparecer ou desaparecer quando necessário ...*/
    if($sumir_botao == 1) {
        if($cmb_periodo == 6) {//6 Meses ...
            $selected6 = 'selected';
        }else if($cmb_periodo == 12) {//12 Meses ...
            $selected12 = 'selected';
        }else if($cmb_periodo == 'T') {//Exibe a opção Todos - no caso de ESP ...
            $selectedt = 'selected';
        }
?>
                <select name="cmb_periodo" title="Selecione o Período" onchange="alterar_periodo()" class="combo">
                    <option value="6" <?=$selected6;?>>6 Meses</option>
                    <option value="12" <?=$selected12;?>>12 Meses</option>
                    <option value="T" <?=$selectedt;?>>Todos</option>
                </select>
<?
    }
?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow' size='2'>&nbsp;Produto: </font>
            <font color='#FFFFFF' size='2'>
                <?=intermodular::pa_discriminacao($id_produto_acabado);?>
            </font>
        </td>
    </tr>
</table>

<!--Aqui dentro dessa DIV eu listo todos os Orçamentos desse Produto - via Ajax-->
<div id='div_listar_orcamentos'>
    <center>
        <img src='../../../../css/little_loading.gif'>
        <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='brown'>
            <b>LOADING ...</b>
        </font>
    </center>
</div>
<!--************************************************************************-->

<table width='90%' border='1' align='center' cellspacing='0' cellpadding='0'>
    <tr class='linhadestaque' align='right'>
        <td colspan="6" align="left">
            <font color="yellow">
            <?
                //Aqui eu busco a Qtde de Pedidos que estão atrelados a este Produto dos últimos 6 meses ...
                $sql = "SELECT COUNT(ovi.qtde) AS total_ini_6_meses 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.`data_emissao` BETWEEN '".data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-')."' AND '".date('Y-m-d')."' 
                        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ";
                $campos_qtde_6_meses = bancos::sql($sql);
                //Aqui eu busco a Qtde de Pedidos que estão atrelados a este Produto dos últimos 12 meses ...
                $sql = "SELECT COUNT(ovi.qtde) AS total_ini_12_meses 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.`data_emissao` BETWEEN '".data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-')."' AND '".date('Y-m-d')."' 
                        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ";
                $campos_qtde_12_meses = bancos::sql($sql);
                //Aqui eu busco a Qtde de Orçamentos que estão atrelados a este Produto de forma total ...
                $sql = "SELECT COUNT(ov.id_orcamento_venda) AS total_orcamento_venda 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                        WHERE ovi.id_produto_acabado = '$id_produto_acabado' ";
                $campos_qtde_todos_meses = bancos::sql($sql);
                /***************************MMV 6 Meses***************************/ 				   
                $sql = "SELECT SUM(ovi.qtde) AS total_ini_6_meses, SUM(ovi.qtde * ovi.preco_liq_final) AS total_todas_empresas, SUM(ovi.qtde * ovi.preco_liq_final / (1 + ovi.margem_lucro / 100)) AS total_ml_zero 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.`data_emissao` BETWEEN '".data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -180), '-')."' AND '".date('Y-m-d')."' 
                        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ";
                $campos_qtde_total = bancos::sql($sql);
                $total_ini_6_meses = $campos_qtde_total[0]['total_ini_6_meses'];
                $total_mlm_6_meses = ($campos_qtde_total[0]['total_todas_empresas'] / $campos_qtde_total[0]['total_ml_zero'] - 1) * 100;
                echo '6 meses -> Tot INI = '.number_format($total_ini_6_meses, 0, '', '.').'   MMV = '.number_format($total_ini_6_meses / 6, 2, ',', '.').'   ou   '.number_format($total_ini_6_meses / $campos_qtde_6_meses[0]['total_orcamento_venda'], 1, ',', '.').' / Pedido - MLM => '.number_format($total_mlm_6_meses, 1, ',', '.').' ';
                /***************************MMV 12 Meses**************************/
                $sql = "SELECT SUM(ovi.qtde) AS total_ini_12_meses, SUM(ovi.qtde * ovi.preco_liq_final) AS total_todas_empresas, SUM(ovi.qtde * ovi.preco_liq_final / (1 + ovi.margem_lucro / 100)) AS total_ml_zero 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda AND ov.`data_emissao` BETWEEN '".data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-')."' AND '".date('Y-m-d')."' 
                        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ";
                $campos_qtde_total = bancos::sql($sql);
                $total_ini_12_meses = $campos_qtde_total[0]['total_ini_12_meses'];
                $total_mlm_12_meses = ($campos_qtde_total[0]['total_todas_empresas'] / $campos_qtde_total[0]['total_ml_zero'] - 1) * 100;
                echo '<br>12 meses -> Tot INI = '.number_format($total_ini_12_meses, 0, '', '.').'   MMV = '.number_format($total_ini_12_meses / 12, 2, ',', '.').'   ou   '.number_format($total_ini_12_meses / $campos_qtde_12_meses[0]['total_orcamento_venda'], 2, ',', '.').' / Pedido - MLM => '.number_format($total_mlm_12_meses, 2, ',', '.').' ';
                /****************************MMV Geral****************************/
                $sql = "SELECT SUM(qtde) AS total_ini_geral 
                        FROM `orcamentos_vendas_itens` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' ";
                $campos_qtde_total  = bancos::sql($sql);
                $total_ini_geral    = $campos_qtde_total[0]['total_ini_geral'];
                //Busca a primeira Data programada desse PA ...
                $sql = "SELECT ov.data_emissao AS data_inicial 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ORDER BY data_emissao LIMIT 1 ";
                $campos_data_inicial    = bancos::sql($sql);
                $data_inicial           = $campos_data_inicial[0]['data_inicial'];
                //Busca a última Data programada desse PA ...
                $sql = "SELECT ov.data_emissao AS data_final 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ORDER BY data_emissao DESC LIMIT 1 ";
                $campos_data_final = bancos::sql($sql);
                $data_final = $campos_data_final[0]['data_final'];

                //Aqui eu verifico a Diferença de Meses no decorrer de Todo o período p/ aplicar nas fórmulas ...
                $dias = data::diferenca_data($data_inicial, $data_final);
                $qtde_meses = $dias[0] / 30;
                if($qtde_meses == 0) $qtde_meses = 1;//Para evitar erro de Divisão por Zero ...
                echo '<br>'.number_format($qtde_meses, 1, ',', '.').' meses -> Tot INI = '.number_format($total_ini_geral, 0, '', '.').'   MMV = '.number_format($total_ini_geral / $qtde_meses, 2, ',', '.').'   ou   '.number_format($total_ini_geral / $campos_qtde_todos_meses[0]['total_orcamento_venda'], 2, ',', '.').' / Pedido';
                /*****************************************************************/
            ?>
            </font>
        </td>
        <td colspan='2' align='left'>
            &nbsp;
        </td>
        <td colspan='2'>
            <font color="yellow">Dólar Dia:</font>
            <?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?>
        </td>
        <td colspan='3' align='left'>
            <font color="yellow">
            <?
                $sql = "SELECT SUM(ovi.qtde * ovi.preco_liq_final) AS total_venda_emp, if(ov.nota_sgd = 'S', 'SGD', 'NF') AS nota_sgd 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                        WHERE ovi.id_produto_acabado = '$id_produto_acabado' 
                        GROUP BY ov.nota_sgd ORDER BY ov.nota_sgd ";
                $campos_total_venda_emp = bancos::sql($sql);
                $linhas_total_venda_emp = count($campos_total_venda_emp);
                for($i = 0; $i < $linhas_total_venda_emp; $i++) {
                    if($i > 0) $quebra_linha = '<br>';
                    echo $quebra_linha.$campos_total_venda_emp[$i]['nota_sgd'].': '.number_format($campos_total_venda_emp[$i]['total_venda_emp'], 2, ',', '.');
                    $todas_empresas+= $campos_total_venda_emp[$i]['total_venda_emp'];
                }
            ?>
            <br/>Total Geral: <?=number_format($todas_empresas, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
<?
/*Esse parâmetro, vem de outros arquivos que solicitam essa tela, sendo assim eu controle esse botão p/
aparecer ou desaparecer quando necessário ...*/
    if($sumir_botao != 1) {
?>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="voltar()" class="botao">
<?
    }
?>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {
    $sql = "SELECT pa.id_produto_acabado, pa.discriminacao nome, 
            IF(c.id_pais = '31', SUM(ovi.qtde * ovi.preco_liq_final), SUM(ovi.qtde * ovi.preco_liq_final * $valor_dolar_dia)) AS total, 
            IF(c.id_pais = '31', SUM((ovi.qtde * ovi.preco_liq_final) / (1 + ovi.margem_lucro / 100)), SUM((ovi.qtde * ovi.preco_liq_final * $valor_dolar_dia) / (1+ovi.margem_lucro/100))) AS mlmg 
            FROM `produtos_acabados` pa 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_produto_acabado = pa.id_produto_acabado 
            INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
            INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
            WHERE ov.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' AND pa.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' 
            GROUP BY pa.referencia, pa.id_produto_acabado ORDER BY total DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);

    $pa = array();
    for($i = 0; $i < $linhas; $i++) {
            //echo $campos[$i]['nome']."=>".$campos[$i]['mlmg']."<br>";//"O elemento 'tal' está no array!";
            if(array_key_exists($campos[$i]['nome'], $pa)) {//sim o elemento consta no array
               $pa[$campos[$i]['nome']]+= $campos[$i]['total'];//"O elemento 'tal' está no array!";
               $pa_mlmg[$campos[$i]['nome']]+= $campos[$i]['mlmg'];//"O elemento 'tal' está no array!";
            }else {// NAO CONSTA NO ARRAY
               $pa[$campos[$i]['nome']] = $campos[$i]['total']; //$pa = array("primeiro" => 1, "segundo" => 4);
               $pa_mlmg[$campos[$i]['nome']] = $campos[$i]['mlmg'];//"O elemento 'tal' está no array!";
            }
    }

    $sql = "SELECT c.id_pais, SUM(ovi.qtde * ovi.preco_liq_final) AS total 
            FROM `produtos_acabados` pa 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_produto_acabado = pa.id_produto_acabado 
            INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
            INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
            WHERE ov.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' AND pa.id_gpa_vs_emp_div = '$id_gpa_vs_emp_div' 
            GROUP BY c.id_pais ORDER BY total DESC ";
    $campos_tot	= bancos::sql($sql);
    $linhas     = count($campos_tot);
    for($i = 0; $i < $linhas; $i++) $total_orcamentos_emitidos+= ($campos_tot[$i]['id_pais'] == 31) ? $campos_tot[$i]['total'] : ($campos_tot[$i]['total'] * $valor_dolar_dia);
?>
<html>
<head>
<title>.:: Orçamento(s) que contém esse Produto atrelado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function orcamentos_atrelados(id_produto_acabado) {
    document.form.id_produto_acabado.value = id_produto_acabado
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='id_gpa_vs_emp_div' value='<?=$id_gpa_vs_emp_div;?>'>
<input type='hidden' name='data_inicial' value='<?=$data_inicial;?>'>
<input type='hidden' name='data_final' value='<?=$data_final;?>'>
<!--Essa variável é controlada pela função em JavaScript-->
<input type='hidden' name='id_produto_acabado'>
<table width='90%' border="0" align='center' cellspacing ='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Relat&oacute;rio de Produtos
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            Total em R$
        </td>
        <td>
            Porcentagem(ns)
        </td>
        <td title='Margem de Lucro Média Gravada'>
            M.L.M.G.
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td onclick="orcamentos_atrelados('<?=$campos[$i]['id_produto_acabado'];?>')">
            <a href="#" title="Pedidos Atrelados" class='link'>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            </a>
        </td>
        <td align='right'>
        <?
            $total_parcial = $pa[$campos[$i]['nome']];
            //Somo os valores de custo do produto nacional com o export para descobrir o valor real da ML da pa ...
            $parcial_mlmg_pa = $pa_mlmg[$campos[$i]['nome']];
            if($parcial_mlmg_pa != 0) {
                $total_mlmg_pa = ($total_parcial / $parcial_mlmg_pa - 1) * 100;
                $total_mlmg_pa_tot+= $parcial_mlmg_pa;
            }else {
                $total_mlmg_pa = 0;
            }
            $total_geral+= $total_parcial;//O Total parcial é o total de todos os pedidos NAC
            echo number_format($total_parcial, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            $porc_parcial = ($campos[$i]['total'] / $total_orcamentos_emitidos) * 100;
            $porc_total+= $porc_parcial;
            echo number_format($porc_parcial, 2, ',', '.');
        ?>
        %
        </td>
        <td align='right'>
            <?=number_format($total_mlmg_pa, 2, ',', '.');?>%
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td colspan="2" align='right'>
            <font color='red' size='2'>
                <b>Total Geral:</b><?=number_format($total_orcamentos_emitidos, 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font color='red' size='2'>
                <?=number_format($porc_total, 2, ',', '.');?>%
            </font>
        </td>
        <td align='right'>
            <font color='red' size='2'>
                <?=number_format(($total_geral / $total_mlmg_pa_tot - 1) * 100, 2, ',', '.');?>%
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan="4">
            Valor Dolar dia R$: <?=number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan="4">
            <input type="submit" name="cmd_atualizar" value="Atualizar Relatório" title='Atualizar Relatório' id="cmd_atualizar" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title='Fechar' onclick="window.close()" style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>