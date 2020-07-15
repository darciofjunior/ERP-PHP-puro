<?
require('../../../../lib/segurancas.php');
//Às vezes essa tela é aberta como sendo Pop-Up, então não posso mostrar o Menu ...
if(empty($_GET['pop_up'])) require('../../../../lib/menu/menu.php');
require('../../../../lib/custos.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$fator_desconto_maximo_vendas = genericas::variavel(19);//Utilizada mais abaixo ...

if($_GET['pop_up'] == 1) {//Se pop_up =1, então significa que essa Tela foi aberta como sendo Pop-UP ...
    $vetor_pa_atrelados = custos::pas_atrelados($_GET['id_produto_acabado']);

//Busca somente os dados do específico PA que foi passado por parâmetro no Pop-UP ...
    $sql = "SELECT pa.*, ged.`desc_base_a_nac`, ged.`desc_base_b_nac`, ged.`acrescimo_base_nac` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON pa.`id_gpa_vs_emp_div` = ged.`id_gpa_vs_emp_div` 
            WHERE pa.`id_produto_acabado` IN (".implode(',', $vetor_pa_atrelados).") ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../..';
    $trazer_com_precos_concorrentes = 1;
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../../classes/produtos_acabados/tela_geral_filtro.php');
}

//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Relatório de Concorrente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Relatório de Concorrente(s)
            <?
                /*Geralmente só teremos esse parâmetro -> $_GET['id_uf_cliente'], quando esse relatório for acessado 
                de dentro do Orçamento ou Pedido de Vendas ...*/
                if(!empty($_GET['id_uf_cliente'])) {
                    $sql = "SELECT sigla 
                            FROM `ufs` 
                            WHERE `id_uf` = '$_GET[id_uf_cliente]' LIMIT 1 ";
                    $campos_uf = bancos::sql($sql);
                    echo ' p/ UF = <font color="yellow">'.$campos_uf[0]['sigla'].'</font>';
                }
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Produto
        </td>
        <td>
            ICMS SP
        </td>
        <td>
            ICMS 
            <font color='yellow'>
            <?
                if(!empty($_GET['id_uf_cliente'])) echo $campos_uf[0]['sigla'];
            ?>
            </font>
        </td>
        <td>
            Preço
        </td>
        <td>
            Concorrente A
        </td>
        <td>
            % A
        </td>
        <td>
            Concorrente B
        </td>
        <td>
            % B
        </td>
        <td>
            Concorrente C
        </td>
        <td>
            % C
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td align='center'>
        <?
            $dados_produto  = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], 1);
            $icms_sp        = $dados_produto['icms'] * (1 - $dados_produto['reducao'] / 100) ;
            echo number_format($icms_sp, 2, ',', '.');
        ?>
        </td>
        <td align='center'>
        <?
            if(!empty($_GET['nota_sgd'])) {
                if($_GET['nota_sgd'] == 'N') {
                    $dados_produto  = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], $_GET['id_uf_cliente']);
                    $icms_cliente   = $dados_produto['icms'] * (1 - $dados_produto['reducao'] / 100);
                    echo number_format($icms_cliente, 2, ',', '.');
                }else {//Quando é sem NF eu tenho que Descontar o Valor de ICMS Total, pq nós "Albafer" não pagamos ...
                    $icms_cliente   = 0;
                }
            }
        ?>
        </td>
        <td align='right'>
        <?
            /*O preço do PA, será atribuido de acordo com os Preços existentes, se existir Promocionais, a prioridade 
            primeiro será para esses Preços ...*/
            if($campos[$i]['preco_promocional'] > 0) {//A prioridade é sempre para o Preço A ...
                $preco_pa = $campos[$i]['preco_promocional'];
                $title = 'Promoção A';
                $rotulo = " <font color='#ff9900' title='$title' style='cursor:help'><b>(PA)</b></font>";    
            }else if($campos[$i]['preco_promocional_b'] > 0) {
                $preco_pa = $campos[$i]['preco_promocional_b'];
                $title = 'Promoção B';
                $rotulo = " <font color='#ff9900' title='$title' style='cursor:help'><b>(PB)</b></font>";
            }else {
                $preco_pa = $campos[$i]['preco_unitario'];
                //Desconto A ...
                if($campos[$i]['desc_base_a_nac'] > 0) $preco_pa*= (1 - $campos[$i]['desc_base_a_nac'] / 100);
                //Desconto B ...
                if($campos[$i]['desc_base_b_nac'] > 0) $preco_pa*= (1 - $campos[$i]['desc_base_b_nac'] / 100);
                //Acréscimo ...
                if($campos[$i]['acrescimo_base_nac'] > 0) $preco_pa*= (1 + $campos[$i]['acrescimo_base_nac'] / 100);
                $preco_pa*= $fator_desconto_maximo_vendas;
                $title 	= 'Desconto de 20%';
                $rotulo = " <font color='#ff9900' title='$title' style='cursor:help'><b>(-20%)</b></font>";
            }
            
            if(!empty($_GET['id_uf_cliente'])) {
                $diferenca_icms = $icms_sp - $icms_cliente;
                $preco_pa*= (1 - $diferenca_icms / 100);
            }
            echo 'R$ '.number_format($preco_pa, 2, ',', '.').$rotulo;
        ?>
        </td>
        <?
//Limpo as variáveis p/ não herdar valores do Loop Anterior ...
            $preco_1        = ''; $preco_2          = ''; $preco_3          = '';
            $concorrente_1  = ''; $concorrente_2    = ''; $concorrente_3    = '';
            $valor_1        = ''; $valor_2          = ''; $valor_3          = '';
//Busca dos Concorrentes atrelados ao Produto Acabado ...
            $sql = "SELECT cpa.com_ipi, cpa.com_st, cpa.preco_liquido, cc.nome 
                    FROM `concorrentes_vs_prod_acabados` cpa 
                    INNER JOIN `concorrentes` cc ON cc.id_concorrente = cpa.id_concorrente 
                    WHERE cpa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ";
            $campos_concorrentes = bancos::sql($sql);
            $linhas_concorrentes = count($campos_concorrentes);
            if($linhas_concorrentes == 3) {
                /*************Concorrente 1*************/
                if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'N') {
                    $rotulo_impostos1 = '<font color="red"><b> (c/IPI) </b></font>';
                }else if($campos_concorrentes[0]['com_ipi'] == 'N' && $campos_concorrentes[0]['com_st'] == 'S') {
                    $rotulo_impostos1 = '<font color="red"><b> (c/ST) </b></font>';
                }else if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'S') {
                    $rotulo_impostos1 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                }else {
                    $rotulo_impostos1 = '';
                }
                $preco_1 = $campos_concorrentes[0]['preco_liquido'];
                if(!empty($_GET['id_uf_cliente'])) $preco_1*= (1 - $diferenca_icms / 100);
                $valor_1 = 'R$ '.($preco_1 > 0) ? intval((1 - $preco_1 / $preco_pa) * 100) : '';
                $concorrente_1 = $campos_concorrentes[0]['nome'];
                /*************Concorrente 2*************/
                if($campos_concorrentes[1]['com_ipi'] == 'S' && $campos_concorrentes[1]['com_st'] == 'N') {
                    $rotulo_impostos2 = '<font color="red"><b> (c/IPI) </b></font>';
                }else if($campos_concorrentes[1]['com_ipi'] == 'N' && $campos_concorrentes[1]['com_st'] == 'S') {
                    $rotulo_impostos2 = '<font color="red"><b> (c/ST) </b></font>';
                }else if($campos_concorrentes[1]['com_ipi'] == 'S' && $campos_concorrentes[1]['com_st'] == 'S') {
                    $rotulo_impostos2 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                }else {
                    $rotulo_impostos2 = '';
                }
                $preco_2 = $campos_concorrentes[1]['preco_liquido'];
                if(!empty($_GET['id_uf_cliente'])) $preco_2*= (1 - $diferenca_icms / 100);
                $valor_2 = 'R$ '.($preco_2 > 0) ? intval((1 - $preco_2 / $preco_pa) * 100) : '';
                $concorrente_2 = $campos_concorrentes[1]['nome'];
                /*************Concorrente 3*************/
                if($campos_concorrentes[2]['com_ipi'] == 'S' && $campos_concorrentes[2]['com_st'] == 'N') {
                    $rotulo_impostos3 = '<font color="red"><b> (c/IPI) </b></font>';
                }else if($campos_concorrentes[2]['com_ipi'] == 'N' && $campos_concorrentes[2]['com_st'] == 'S') {
                    $rotulo_impostos3 = '<font color="red"><b> (c/ST) </b></font>';
                }else if($campos_concorrentes[2]['com_ipi'] == 'S' && $campos_concorrentes[2]['com_st'] == 'S') {
                    $rotulo_impostos3 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                }else {
                    $rotulo_impostos3 = '';
                }
                $preco_3 = $campos_concorrentes[2]['preco_liquido'];
                if(!empty($_GET['id_uf_cliente'])) $preco_3*= (1 - $diferenca_icms / 100);
                $valor_3 = 'R$ '.($preco_3 > 0) ? intval((1 - $preco_3 / $preco_pa) * 100) : '';
                $concorrente_3 = $campos_concorrentes[2]['nome'];
            }else if($linhas_concorrentes == 2) {
                /*************Concorrente 1*************/
                if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'N') {
                    $rotulo_impostos1 = '<font color="red"><b> (c/IPI) </b></font>';
                }else if($campos_concorrentes[0]['com_ipi'] == 'N' && $campos_concorrentes[0]['com_st'] == 'S') {
                    $rotulo_impostos1 = '<font color="red"><b> (c/ST) </b></font>';
                }else if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'S') {
                    $rotulo_impostos1 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                }else {
                    $rotulo_impostos1 = '';
                }
                $preco_1 = $campos_concorrentes[0]['preco_liquido'];
                if(!empty($_GET['id_uf_cliente'])) $preco_1*= (1 - $diferenca_icms / 100);
                $valor_1 = 'R$ '.($preco_1 > 0) ? intval((1 - $preco_1 / $preco_pa) * 100) : '';
                $concorrente_1 = $campos_concorrentes[0]['nome'];
                /*************Concorrente 2*************/
                if($campos_concorrentes[1]['com_ipi'] == 'S' && $campos_concorrentes[1]['com_st'] == 'N') {
                    $rotulo_impostos2 = '<font color="red"><b> (c/IPI) </b></font>';
                }else if($campos_concorrentes[1]['com_ipi'] == 'N' && $campos_concorrentes[1]['com_st'] == 'S') {
                    $rotulo_impostos2 = '<font color="red"><b> (c/ST) </b></font>';
                }else if($campos_concorrentes[1]['com_ipi'] == 'S' && $campos_concorrentes[1]['com_st'] == 'S') {
                    $rotulo_impostos2 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                }else {
                    $rotulo_impostos2 = '';
                }
                $preco_2 = $campos_concorrentes[1]['preco_liquido'];
                if(!empty($_GET['id_uf_cliente'])) $preco_2*= (1 - $diferenca_icms / 100);
                $valor_2 = 'R$ '.($preco_2 > 0) ? intval((1 - $preco_2 / $preco_pa) * 100) : '';
                $concorrente_2 = $campos_concorrentes[1]['nome'];
            }else if($linhas_concorrentes == 1) {
                /*************Concorrente 1*************/
                if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'N') {
                    $rotulo_impostos1 = '<font color="red"><b> (c/IPI) </b></font>';
                }else if($campos_concorrentes[0]['com_ipi'] == 'N' && $campos_concorrentes[0]['com_st'] == 'S') {
                    $rotulo_impostos1 = '<font color="red"><b> (c/ST) </b></font>';
                }else if($campos_concorrentes[0]['com_ipi'] == 'S' && $campos_concorrentes[0]['com_st'] == 'S') {
                    $rotulo_impostos1 = '<font color="red"><b> (c/IPI + ST) </b></font>';
                }else {
                    $rotulo_impostos1 = '';
                }
                $preco_1 = $campos_concorrentes[0]['preco_liquido'];
                if(!empty($_GET['id_uf_cliente'])) $preco_1*= (1 - $diferenca_icms / 100);
                $valor_1 = 'R$ '.($preco_1 > 0) ? intval((1 - $preco_1 / $preco_pa) * 100) : '';
                $concorrente_1 = $campos_concorrentes[0]['nome'];
            }
        ?>
        <td>
        <?
            if(!empty($preco_1)) echo 'R$ '.number_format($preco_1, 2, ',', '.').' - '.$concorrente_1.$rotulo_impostos1;
        ?>
        </td>
        <td align="center">
            <?=$valor_1;?>
        </td>
        <td>
        <?
            if(!empty($preco_2)) echo 'R$ '.number_format($preco_2, 2, ',', '.').' - '.$concorrente_2.$rotulo_impostos2;
        ?>
        </td>
        <td align='center'>
            <?=$valor_2;?>
        </td>
        <td>
        <?
            if(!empty($preco_3)) echo 'R$ '.number_format($preco_3, 2, ',', '.').' - '.$concorrente_3.$rotulo_impostos3;
        ?>
        </td>
        <td align='center'>
            <?=$valor_3;?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
        <?
            if($_GET['pop_up'] != 1) {//Só irá mostrar o Botão de Consultar quando essa Tela for aberta como sendo normal ...
        ?>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'relatorio.php'" class='botao'>
        <?
            }
        ?>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* O preço do PA, será atribuido de acordo com os Preços existentes, se existir Promocionais, a prioridade 
  primeiro será para esses Preços: Preço A, Preço B e Preço c/ 20% de Desconto Extra.

* Não levamos em conta o prazo médio da venda, mas os preços são baseados em 30 DDL.

* Os preços acima, já levam em conta a diferença de ICMS tanto  para a UF do cliente como para venda SGD.
</pre>
<?}?>