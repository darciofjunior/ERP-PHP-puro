<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/vendas/concorrentes/concorrentes.php', '../../../');

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_referencia     = $_POST['txt_referencia'];
        $txt_discriminacao  = $_POST['txt_discriminacao'];
        $cmb_gpa_vs_emp_div = $_POST['cmb_gpa_vs_emp_div'];
    }else {
        $txt_referencia     = $_GET['txt_referencia'];
        $txt_discriminacao  = $_GET['txt_discriminacao'];
        $cmb_gpa_vs_emp_div = $_GET['cmb_gpa_vs_emp_div'];
    }
/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
como caracter ...*/
    $txt_discriminacao = str_replace('|', '%', $txt_discriminacao);
    if(empty($cmb_gpa_vs_emp_div)) { $cmb_gpa_vs_emp_div = "%"; }
//Aqui eu Busco todos os PA que estão atrelados à algum Concorrente ...
    $sql = "SELECT DISTINCT(cpa.id_produto_acabado), pa.*, ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac 
            FROM `concorrentes_vs_prod_acabados` cpa 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' AND pa.`id_gpa_vs_emp_div` LIKE '$cmb_gpa_vs_emp_div' 
            INNER JOIN `gpas_vs_emps_divs` ged ON pa.id_gpa_vs_emp_div = ged.id_gpa_vs_emp_div 
            WHERE cpa.`ativo` = '1' ORDER BY pa.referencia, pa.discriminacao ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório de Concorrentes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Relatório de Concorrente(s)
        </td>
    </tr>
<?
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='6'>
            NÃO HÁ PA(S) CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <font title='Referência' size='-1'>
                <b>Ref</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
                <b>Discriminação</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Nosso Pço Líq</b>
        </td>
        <td colspan='3' bgcolor='#CCCCCC'>
            <b>Concorrentes / Preço</b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
        <?
/*O preço do PA, será atribuido de acordo com os Preços existentes, se existir Promocionais, 
a prioridade primeiro será para esses Preços ...*/
            if($campos[$i]['preco_promocional'] > 0) {
                $preco_pa   = $campos[$i]['preco_promocional'];
                $title      = 'Promoção A';
                $rotulo     = " <font color='#ff9900' title='$title' style='cursor:help'><b>(PA)</b></font>";
            }else if($campos[$i]['preco_promocional_b'] > 0) {
                $preco_pa   = $campos[$i]['preco_promocional_b'];
                $title      = 'Promoção B';
                $rotulo     = " <font color='#ff9900' title='$title' style='cursor:help'><b>(PB)</b></font>";
            }else {
                $preco_pa = $campos[$i]['preco_unitario'];
                if($campos[$i]['desc_base_a_nac'] > 0) {//Desconto A ...
                    $preco_pa*= (1 - $campos[$i]['desc_base_a_nac'] / 100);
                }
                if($campos[$i]['desc_base_b_nac'] > 0) {//Desconto B ...
                    $preco_pa*= (1 - $campos[$i]['desc_base_b_nac'] / 100);
                }
                if($campos[$i]['acrescimo_base_nac'] > 0) {//Acréscimo ...
                    $preco_pa*= (1 + $campos[$i]['acrescimo_base_nac'] / 100);
                }
            }
            echo '<br>R$ '.number_format($preco_pa, 2, ',', '.').$rotulo;
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT c.nome, cpa.preco_liquido 
                    FROM `concorrentes_vs_prod_acabados` cpa 
                    INNER JOIN `concorrentes` c ON c.id_concorrente = cpa.id_concorrente 
                    WHERE cpa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    ORDER BY c.nome, cpa.preco_liquido ";
            $campos_concorrentes = bancos::sql($sql);
            $linhas_concorrentes = count($campos_concorrentes);
            if($linhas_concorrentes > 0) {
        ?>
            <table width='90%' border='1' cellspacing='0' cellpadding='1'>
                <tr class='linhanormal'>
        <?
//Aqui eu Listo de Forma Linear todos os Concorrentes que estão atrelados ao PA da Linha ...
                        for($j = 0; $j < $linhas_concorrentes; $j++) echo '<td width="140" align="center"><font color="darkblue" size="1"><b>'.$campos_concorrentes[$j]['nome'].' - R$ '.number_format($campos_concorrentes[$j]['preco_liquido'], 2, ',', '.').'</b></font></td>';
/*Caso eu ainda não tenha preenchido com 3 Concorrentes p/ o PA, então eu completo o restante da Tabela 
com outras colunas para que ela não fique torta na Tela na Hora de Apresentação ...*/
                        for($j = $linhas_concorrentes; $j < 3; $j++) echo "<td width='140' align='center'>&nbsp;</td>";
        ?>
                </tr>
            </table>
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'relatorio.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
}else {
?>
<html>
<head>
<title>.:: Relatório de Concorrente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value="1">
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório de Concorrente(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo P.A. (Empresa Divisão)
        </td>
        <td>
            <select name='cmb_gpa_vs_emp_div' title='Selecione o Grupo P.A. (Empresa Divisão)' class='combo'>
            <?
                $sql = "SELECT ged.id_gpa_vs_emp_div, CONCAT(gpa.nome, ' (', ed.razaosocial, ') ') AS rotulo 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                        WHERE gpa.`ativo` = '1' ORDER BY rotulo ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'concorrentes.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_referencia.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>