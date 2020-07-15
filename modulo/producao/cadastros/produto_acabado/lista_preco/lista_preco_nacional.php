<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/custos.php');
require('../../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/lista_preco/lista_preco.php', '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>LISTA DE PREÇO NACIONAL ATUALIZADA COM SUCESSO.</font>";

$fator_margem_lucro     = genericas::variavel(22);//margem de Lucro PA
$fator_desc_max_vendas  = genericas::variavel(19);//Fator Desc Máx. de Vendas

if($passo == 1) {
    if(!empty($chkt_preco_promocional))         $condicao = " AND (pa.`preco_promocional` <> '0.00' OR pa.`preco_promocional_b` <> '0.00') ";
    if(!empty($chkt_todos_produtos_zerados))    $condicao_produtos_zerados = " AND pa.`preco_unitario` = '0.00' ";
        
    //Se estiver habilitada essa então mostra também os Produtos que são da Família de Componentes
    $condicao_componentes   = (!empty($chkt_mostrar_componentes)) ? '' : " AND gpa.`id_familia` <> '23' ";

    if(empty($cmb_empresa_divisao)) $cmb_empresa_divisao    = '%';
    if(empty($cmb_grupo_pa))        $cmb_grupo_pa           = '%';
    if(empty($cmb_familia))         $cmb_familia            = '%';
	
//Aqui traz todos os grupos com exceção dos que são pertencentes a Família de Componentes
    $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`operacao_custo_sub`, pa.`referencia`, 
            pa.`discriminacao`, pa.`preco_unitario`, pa.`qtde_promocional`, pa.`preco_promocional`, 
            pa.`qtde_promocional_b`, pa.`preco_promocional_b`, pa.`preco_unitario_simulativa`, 
            pa.`preco_promocional_simulativa`, pa.`status_top`, pa.`status_custo`, ed.`razaosocial`, gpa.`nome`, ged.* 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao AND ed.id_empresa_divisao LIKE '$cmb_empresa_divisao' 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.id_grupo_pa LIKE '$cmb_grupo_pa' AND gpa.id_familia LIKE '$cmb_familia' $condicao_componentes 
            WHERE pa.`referencia` LIKE '%$txt_referencia%' 
            AND pa.discriminacao LIKE '%$txt_discriminacao%' 
            AND pa.`referencia` <> 'ESP' 
            AND pa.`status_nao_produzir` = '0' 
            AND pa.`ativo` = '1' 
            $condicao $condicao_produtos_zerados ORDER BY $cmb_order_by ";
    $campos = bancos::sql($sql, $inicio, 500, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'lista_preco_nacional.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Lista de Preço Nacional ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calcular(indice) {
    var preco_bruto_faturado_rs = eval(strtofloat(document.getElementById('txt_preco_bruto_fat_rs'+indice).value))
    //Caso a caixa esteja vazia ...
    if(isNaN(preco_bruto_faturado_rs)) preco_bruto_faturado_rs = 0

    var desconto_a_grupo_pa = eval(strtofloat(document.getElementById('txt_desconto_a_grupoa'+indice).value))
    var desconto_b_grupo_pa = eval(strtofloat(document.getElementById('txt_desconto_b_grupoa'+indice).value))
    var acrescimo_base_nac  = eval(strtofloat(document.getElementById('txt_acrescimo_base_nac'+indice).value))

//Fórmula do Preço Líquido Fat. R$
    document.getElementById('txt_preco_liq_fat_rs'+indice).value = preco_bruto_faturado_rs * (1 - desconto_a_grupo_pa / 100) * (1 - desconto_b_grupo_pa / 100) * (1 + acrescimo_base_nac / 100)
    var preco_liq_fat_rs        = eval(document.getElementById('txt_preco_liq_fat_rs'+indice).value)
    var preco_max_custo_fat_rs  = (document.getElementById('txt_preco_max_custo_fat_rs'+indice).value == 'Orçar') ? 0 : eval(strtofloat(document.getElementById('txt_preco_max_custo_fat_rs'+indice).value))
    
//Comparação
//Preço Máx. Custo Fat. R$ maior do q P. Líq. Fat. R$
    if(preco_max_custo_fat_rs > preco_liq_fat_rs) {
        document.getElementById('txt_preco_max_custo_fat_rs'+indice).style.background   = 'red'
        document.getElementById('txt_preco_max_custo_fat_rs'+indice).style.color        = 'white'
//Preço Máx. Custo Fat. R$ menor do q P. Líq. Fat. R$
    }else {
        document.getElementById('txt_preco_max_custo_fat_rs'+indice).className          = 'textdisabled'
    }
//Arredondamentos
    document.getElementById('txt_preco_liq_fat_rs'+indice).value = arred(document.getElementById('txt_preco_liq_fat_rs'+indice).value, 2, 1)
}

function calcular_geral() {
    var elementos = document.form.elements
    if(typeof(elementos['hdd_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        var preco_bruto_faturado_rs = eval(strtofloat(document.getElementById('txt_preco_bruto_fat_rs'+i).value))
        //Caso a caixa esteja vazia ...
        if(isNaN(preco_bruto_faturado_rs)) preco_bruto_faturado_rs = 0
        
        var desconto_a_grupo_pa = eval(strtofloat(document.getElementById('txt_desconto_a_grupoa'+i).value))
        var desconto_b_grupo_pa = eval(strtofloat(document.getElementById('txt_desconto_b_grupoa'+i).value))
        var acrescimo_base_nac  = eval(strtofloat(document.getElementById('txt_acrescimo_base_nac'+i).value))
    
//Fórmula do Preço Líquido Fat. R$
        document.getElementById('txt_preco_liq_fat_rs'+i).value = preco_bruto_faturado_rs * (1 - desconto_a_grupo_pa / 100) * (1 - desconto_b_grupo_pa / 100) * (1 + acrescimo_base_nac / 100)
        document.getElementById('txt_preco_liq_fat_rs'+i).value = arred(document.getElementById('txt_preco_liq_fat_rs'+i).value, 2, 1)
        var preco_liq_fat_rs        = eval(strtofloat(document.getElementById('txt_preco_liq_fat_rs'+i).value))
        var preco_max_custo_fat_rs  = (document.getElementById('txt_preco_max_custo_fat_rs'+i).value == 'Orçar') ? 0 : eval(strtofloat(document.getElementById('txt_preco_max_custo_fat_rs'+i).value))
//Comparação
//Preço Máx. Custo Fat. R$ maior do q P. Líq. Fat. R$
        if(preco_max_custo_fat_rs > preco_liq_fat_rs) {
            document.getElementById('txt_preco_max_custo_fat_rs'+i).style.background    = 'red'
            document.getElementById('txt_preco_max_custo_fat_rs'+i).style.color         = 'white'
//Preço Máx. Custo Fat. R$ menor do q P. Líq. Fat. R$
        }else {
            document.getElementById('txt_preco_max_custo_fat_rs'+i).className           = 'textdisabled'
        }
//Arredondamentos
        document.getElementById('txt_preco_liq_fat_rs'+i).value = arred(document.getElementById('txt_preco_liq_fat_rs'+i).value, 2, 1)
    }
}

function copiar(indice) {
    document.getElementById('txt_preco_bruto_fat_rs'+indice).value = document.getElementById('txt_preco_sug_bruto_fat_rs'+indice).value
    calcular(indice)
}

function copiar_geral() {
    var elementos = document.form.elements
    if(typeof(elementos['hdd_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado[]'].length)
    }
    for(i = 0; i < linhas; i++) document.getElementById('txt_preco_bruto_fat_rs'+i).value = document.getElementById('txt_preco_sug_bruto_fat_rs'+i).value
    calcular_geral()
}

function validar() {
    var elementos = document.form.elements
    if(typeof(elementos['hdd_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado[]'].length)
    }
    for(i = 0; i < linhas; i++) {
//Preço Bruto Faturado
        if(document.getElementById('txt_preco_bruto_fat_rs'+i).value == '') {
            alert('DIGITE O PREÇO BRUTO FATURADO R$ !')
            document.getElementById('txt_preco_bruto_fat_rs'+i).focus()
            return false
        }
    }
//Trato os campos antes de guardar no Banco de Dados ...
    for(i = 0; i < linhas; i++) {
        document.getElementById('txt_preco_promocional'+i).value    = strtofloat(document.getElementById('txt_preco_promocional'+i).value)
        document.getElementById('txt_preco_promocional_b'+i).value  = strtofloat(document.getElementById('txt_preco_promocional_b'+i).value)
        document.getElementById('txt_preco_bruto_fat_rs'+i).value   = strtofloat(document.getElementById('txt_preco_bruto_fat_rs'+i).value)
    }
}

//Aqui realiza o cálculo incremento ou decremento na Tela
function calculo_incremento() {
    var fator_desc_max_vendas = eval('<?=$fator_desc_max_vendas;?>')
    var incremento  = (document.form.txt_incremento.value != '') ? (1 + (eval(strtofloat(document.form.txt_incremento.value)) / 100)) : 1
    var elementos   = document.form.elements
    if(typeof(elementos['hdd_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        var margem_lucro_minima = eval(strtofloat(document.getElementById('txt_margem_lucro_minima'+i).value))
        var desconto_a_grupo_pa = eval(strtofloat(document.getElementById('txt_desconto_a_grupoa'+i).value))
        var desconto_b_grupo_pa = eval(strtofloat(document.getElementById('txt_desconto_b_grupoa'+i).value))
        var acrescimo_base_nac  = eval(strtofloat(document.getElementById('txt_acrescimo_base_nac'+i).value))

        if(document.getElementById('txt_preco_max_custo_fat_rs'+i).value == 'Orçar') {
            var preco_max_custo_fat_rs = 0
            document.getElementById('txt_preco_sug_bruto_fat_rs'+i).value = 0
        }else {
            var preco_max_custo_fat_rs = eval(strtofloat(document.getElementById('txt_preco_max_custo_fat_rs'+i).value))
            var preco_sug_bruto_fat_rs = preco_max_custo_fat_rs / 2 * (1 + margem_lucro_minima / 100) / (1 - desconto_a_grupo_pa / 100) / (1 - desconto_b_grupo_pa / 100) * (1 + acrescimo_base_nac / 100) / fator_desc_max_vendas * incremento
            document.getElementById('txt_preco_sug_bruto_fat_rs'+i).value = preco_sug_bruto_fat_rs
        }
        document.getElementById('txt_preco_sug_bruto_fat_rs'+i).value = arred(document.getElementById('txt_preco_sug_bruto_fat_rs'+i).value, 2, 1)
    }
}

function calculo_margem_lucro(indice, preco_maximo_custo_fat_rs, fator_margem_lucro_loop, fator_desc_max_vendas) {
    var elementos = document.form.elements
    if(typeof(elementos['hdd_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado[]'].length)
    }
    if(document.getElementById('txt_preco_promocional'+indice).value != '' && document.getElementById('txt_preco_promocional'+indice).value != '0,00') {
        var preco_a = eval(strtofloat(document.getElementById('txt_preco_promocional'+indice).value))
        document.getElementById('txt_margem_lucro'+indice).value = ((preco_a / (preco_maximo_custo_fat_rs / (fator_margem_lucro_loop / fator_desc_max_vendas))) -1) * 100
        document.getElementById('txt_margem_lucro'+indice).value = arred(document.getElementById('txt_margem_lucro'+indice).value, 2, 1)
    }else {
        document.getElementById('txt_margem_lucro'+indice).value = ''
    }
}

function liberar_custo(id_produto_acabado, status_custo_desejado) {
    window.location = 'lista_preco_nacional.php?passo=2&id_produto_acabado='+id_produto_acabado+'&status_custo_desejado='+status_custo_desejado
}
</Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=3';?>' method='post' onsubmit='return validar()'>
<table width='98%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr align='center'>
        <td colspan='16'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='16'>
            Lista de Preço Nacional
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Produto</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>OC</b>
        </td>                
        <td colspan='5' bgcolor='#CECECE'>
            <b>Condições Promocionais R$</b>
        </td>
        <td colspan='4' bgcolor='#CECECE'>
            <b>Preço Bruto Fat. R$</b>
        </td>
        <td colspan='3' rowspan='2' bgcolor='#CECECE'>
            <font title='Desconto A / Desconto B / Acréscimo Grupo P.A'>
                <b>Desc. A / B / Ac. <br>Grupo P.A.</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>P. Líq. <br>Fat. R$</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Preço Máx. <br/>Custo <br/>Fat. R$</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>Qtde A</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Preço A</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Margem <br>de Lucro</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Qtde B</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Preço B</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Atual</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Sugerido</b>
            <img src = '../../../../../imagem/seta_esquerda.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_geral()'>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Margem de Lucro Min Grupo vs Divisão'>
                <b>ML Min<br/>
                Grupo vs Divisão</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Margem de Lucro já c/ 20% Desc'>
                <b>M. L.<br/>
                já c/ 20% Desc</b>
            </font>
        </td>
    </tr>
<?
//Aqui instância as sub-funções
        //custos::custo_auto_pi_industrializado();
        for ($i = 0;  $i < $linhas; $i++) {
/*********Todo esse código, vai estar me auxiliando para a Função em JavaScript*********/
//Fórmula do Preço Máximo Custo Fat. R$ - esse campo está aqui, mais ele é printado + abaixo
            $preco_maximo_custo_fat_rs = custos::preco_custo_pa($campos[$i]['id_produto_acabado']) / $fator_desc_max_vendas;
            //Forço o arred. para 2 casas para não dar erro na fórmula por causa do JavaScript -> Dárcio
            $preco_maximo_custo_fat_rs = round($preco_maximo_custo_fat_rs, 2);
            //`status_custo` = '$status_custo', 
/***************************************************************************************/
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <font title="Grupo P.A. (E. D.): <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>" size='-2'>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            </font>
            <?
                if($campos[$i]['status_custo'] == 1) {//Já está com o Custo Liberado ...
                    $texto = '<font color="red"><b>(Bloquear Custo)</b></font>';
                    $status_custo_desejado = 0;
                }else {
                    $texto = '<font color="darkblue"><b>(Liberar Custo)</b></font>';
                    $status_custo_desejado = 1;
                }
            ?>
            <a href="javascript:liberar_custo('<?=$campos[$i]['id_produto_acabado'];?>', '<?=$status_custo_desejado;?>')" class="link">
                <?=$texto;?>
            </a>
        <?
            if($campos[$i]['operacao_custo'] == 0) {//Industrial
        ?>
                <a href = '../../../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&tela=2&pop_up=1' title='Visualizar Custo Industrial' class='html5lightbox'>
        <?
            }else {
        ?>
                <a href = '../../../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>' title='Visualizar Custo Revenda' class='html5lightbox'>
        <?
            }
        ?>
                    &nbsp;
                    <img src = '../../../../../imagem/menu/alterar.png' border='0' title='Alterar Custo' alt='Alterar Custo'>
                </a>
            <font color='red' size='-2'>
            <?
                if($campos[$i]['status_top'] == 1) {
                    echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font>";
                }else if($campos[$i]['status_top'] == 2) {
                    echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font>";
                }
            ?>
            </font>
        </td>
        <td>
        <?
            if($campos[$i]['operacao_custo'] == 0) {
                echo "<font title='Opera&ccedil;&atilde;o de Custo' style='cursor:help'><font title='Industrialização' style='cursor:help'>I</font>";
                if($campos[$i]['operacao_custo_sub'] == 0) {
                    echo "-<font title='Sub-Opera&ccedil;&atilde;o Industrial' style='cursor:help'>I</font>";
                }else {
                    echo "-<font title='Sub-Opera&ccedil;&atilde;o Revenda' style='cursor:help'>R</font>";
                }
            }else {
                echo "<font title='Opera&ccedil;&atilde;o de Custo' style='cursor:help'></font><font title='Revenda' style='cursor:help'>R</font>";
            }
        ?>				
        </td>                        
        <td>
            <input type='text' name='txt_qtde_promocional[]' id='txt_qtde_promocional<?=$i;?>' value="<?=$campos[$i]['qtde_promocional'];?>" maxlength="7" size="6" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_preco_promocional[]' id='txt_preco_promocional<?=$i;?>' value="<?=number_format($campos[$i]['preco_promocional'], 2, ',', '.');?>" maxlength="7" size="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calculo_margem_lucro('<?=$i;?>', '<?=$preco_maximo_custo_fat_rs;?>', '<?=$fator_margem_lucro;?>', '<?=$fator_desc_max_vendas;?>')" class='caixadetexto'>
        </td>
        <td>
        <?
//Utilizada em alguns campos + abaixo ...
            $preco_margem_lucro_zero = $preco_maximo_custo_fat_rs / ($fator_margem_lucro / $fator_desc_max_vendas);
//Aqui eu faço o cálculo quando carregar a Tela no início ...
            if($campos[$i]['preco_promocional'] != '' && $campos[$i]['preco_promocional'] != '0.00') {
                if(empty($fator_desc_max_vendas) || $fator_desc_max_vendas=="0.00") { $fator_desc_max_vendas=1; }
                //$margem_lucro = (($campos[$i]['preco_promocional']/($preco_maximo_custo_fat_rs/($fator_margem_lucro/$fator_desc_max_vendas)))-1)*100;
                $margem_lucro = (($campos[$i]['preco_promocional']/$preco_margem_lucro_zero)-1)*100;
                $margem_lucro = number_format($margem_lucro, 2, ',', '.');
            }else {
                $margem_lucro = '';
            }
        ?>
            <input type='text' name='txt_margem_lucro[]' id='txt_margem_lucro<?=$i;?>' value="<?=$margem_lucro;?>" maxlength="7" size="7" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_qtde_promocional_b[]' id='txt_qtde_promocional_b<?=$i;?>' value="<?=$campos[$i]['qtde_promocional_b'];?>" maxlength="7" size="6" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_preco_promocional_b[]' id='txt_preco_promocional_b<?=$i;?>' value="<?=number_format($campos[$i]['preco_promocional_b'], 2, ',', '.');?>" maxlength="7" size="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
        <td bgcolor='#393939'>
           <input type='text' name='txt_preco_bruto_fat_rs[]' id='txt_preco_bruto_fat_rs<?=$i;?>' value="<?=number_format($campos[$i]['preco_unitario'], 2, ',', '.');?>" maxlength='9' size='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>')" class='caixadetexto'>
        </td>
        <td>
            <img src = '../../../../../imagem/seta_esquerda.gif' border='0' title='Copiar Valor' alt='Copiar Valor' onclick="copiar('<?=$i;?>')">
        <?
            $valores                = vendas::calcular_ml_min_pa_vs_cliente($campos[$i]['id_produto_acabado']);
            $margem_lucro_minima    = $valores['margem_lucro_minima'];
            
        
            if($campos[$i]['status_custo'] == 1) {//Custo Liberado
                $txt_preco_bruto_fat_rs = $preco_maximo_custo_fat_rs / 2 * (1 + $margem_lucro_minima / 100) / (1 - $campos[$i]['desc_base_a_nac'] / 100) / (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100) / $fator_desc_max_vendas;
            }else {//Custo não Liberado
                $txt_preco_bruto_fat_rs = 0;
            }
        ?>
            <input type='text' name='txt_preco_sug_bruto_fat_rs[]' id='txt_preco_sug_bruto_fat_rs<?=$i;?>' value="<?=number_format($txt_preco_bruto_fat_rs, 2, ',', '.');?>" maxlength='7' size='6' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_margem_lucro_minima[]' id='txt_margem_lucro_minima<?=$i;?>' value="<?=number_format($margem_lucro_minima, 2, ',', '.');?>" maxlength='7' size='6' class='textdisabled' disabled>
        </td>
        <td>
        <?
//Fórmula do Preço Líquido Fat. R$ - Impressa mais abaixa ...
            $preco_liq_fat_rs = $campos[$i]['preco_unitario'] * (1 - $campos[$i]['desc_base_a_nac'] / 100) * (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100);
//Cálculo da Margem de Lucro já com 20 Desconto, p/ não dar erro de divisão por Zero ...
            if($preco_margem_lucro_zero == 0) $preco_margem_lucro_zero = 1;
            $margem_lucro_ja_c_20_desc = (($preco_liq_fat_rs * $fator_desc_max_vendas) / $preco_margem_lucro_zero - 1) * 100;
            $margem_lucro_ja_c_20_desc = round($margem_lucro_ja_c_20_desc, 2);
            echo segurancas::number_format($margem_lucro_ja_c_20_desc, 2, '.');
        ?>
        </td>
        <td colspan='3'>
            <font color='green' size='-2'>
                <?=number_format($campos[$i]['desc_base_a_nac'], 2, ',', '.');?>
            </font>
            <input type='hidden' name='txt_desconto_a_grupoa[]' id='txt_desconto_a_grupoa<?=$i;?>' value="<?=number_format($campos[$i]['desc_base_a_nac'], 2, ',', '.');?>" maxlength="7" size="6" class='caixadetexto' disabled>
            / 
            <font color="green" size='-2'>
                <?=number_format($campos[$i]['desc_base_b_nac'], 2, ',', '.');?>
            </font>
            <input type='hidden' name='txt_desconto_b_grupoa[]' id='txt_desconto_b_grupoa<?=$i;?>' value="<?=number_format($campos[$i]['desc_base_b_nac'], 2, ',', '.');?>" maxlength="7" size="6" class='caixadetexto' disabled>
            / 
            <font color="green" size='-2'>
                <?=number_format($campos[$i]['acrescimo_base_nac'], 2, ',', '.');?>
            </font>
            <input type='hidden' name='txt_acrescimo_base_nac[]' id='txt_acrescimo_base_nac<?=$i;?>' value="<?=number_format($campos[$i]['acrescimo_base_nac'], 2, ',', '.');?>" maxlength="7" size="6" class='caixadetexto' disabled>
        </td>
        <td>
        <?
//Forço o arred. para 2 casas para não dar erro na fórmula por causa do PHP -> Dárcio
            $preco_liq_fat_rs = round($preco_liq_fat_rs, 2);
        ?>
            <input type='text' name='txt_preco_liq_fat_rs[]' id='txt_preco_liq_fat_rs<?=$i;?>' value="<?=number_format($preco_liq_fat_rs, 2, ',', '.');?>" maxlength="7" size="6" class='textdisabled' disabled>
        </td>
        <?
            if($campos[$i]['status_custo'] == 1) {//Custo Liberado
//Comparação
//Preço Máx. Custo Fat. R$ maior do q P. Líq. Fat. R$
                if($preco_maximo_custo_fat_rs > $preco_liq_fat_rs) {
//Preço Máx. Custo Fat. R$ menor do q P. Líq. Fat. R$
                    $color = 'background:red;color:white';
                }else {
                    $color = 'background:#FFFFE1;color:gray';
                }
                $printar = number_format($preco_maximo_custo_fat_rs, 2, ',', '.');
            }else {//Custo não Liberado
                $color = 'background:#FFFFE1;color:gray';
                $printar = 'Orçar';
            }
        ?>
        <td>
            <input type='text' name='txt_preco_max_custo_fat_rs[]' id='txt_preco_max_custo_fat_rs<?=$i;?>' value="<?=$printar;?>" maxlength="7" size="6" class='caixadetexto' style="<?=$color;?>" disabled>
            <input type='hidden' name='hdd_produto_acabado[]' id='hdd_produto_acabado<?=$i;?>' value="<?=$campos[$i]['id_produto_acabado'];?>">
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td align='left'>
            Incremento <input type='text' name='txt_incremento' value='0,00' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calculo_incremento()" maxlength='5' size='6' class='caixadetexto'>&nbsp;%
        </td>
        <td colspan='15'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'lista_preco_nacional.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {//Passo que serve somente p/ Liberar e Desliberar o Custo ...
    $sql = "UPDATE `produtos_acabados` SET `status_custo` = '$_GET[status_custo_desejado]' WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'lista_preco_nacional.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else if($passo == 3) {//Passo que serve p/ fazer a Atualização dos Preços ...
//Aqui é a parte de atualização dos Produtos Acabados
    foreach($_POST['hdd_produto_acabado'] as $i => $id_produto_acabado) {
        $sql = "UPDATE `produtos_acabados` SET `preco_unitario` = '".$_POST['txt_preco_bruto_fat_rs'][$i]."', `qtde_promocional` = '".$_POST['txt_qtde_promocional'][$i]."', `preco_promocional` = '".$_POST['txt_preco_promocional'][$i]."', `qtde_promocional_b` = '".$_POST['txt_qtde_promocional_b'][$i]."', `preco_promocional_b` = '".$_POST['txt_preco_promocional_b'][$i]."' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'lista_preco_nacional.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) - Lista de Preço Nacional ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function lista_preco_eletronica() {
    document.form.target = 'POP'
    document.form.action = 'lista_preco_eletronica.php'
    nova_janela('lista_preco_eletronica.php', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}
    
function submeter() {
    document.form.target = '_self'
    document.form.action = "<?=$PHP_SELF.'?passo=1';?>"
}
</Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Acabado(s) - Lista de Preço Nacional
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name="txt_referencia" title="Digite a Referência" size="40" maxlength="35" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação" size="40" maxlength="35" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa Divisão
        </td>
        <td>
            <select name="cmb_empresa_divisao" title="Consultar Produto Acabado por: Empresa Divisão" class='combo'>
            <?
                $sql = "SELECT id_empresa_divisao, razaosocial 
                        FROM `empresas_divisoes` 
                        WHERE `ativo` = '1' ORDER BY razaosocial ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
                Grupo P.A.
        </td>
        <td>
            <select name="cmb_grupo_pa" title="Consultar Produto Acabado por: Grupo P.A." class='combo'>
            <?
                //Aqui traz todos os grupos com exceção dos que são pertencentes a Família de Componentes
                $sql = "SELECT id_grupo_pa, nome 
                        FROM `grupos_pas` 
                        WHERE `ativo` = '1' 
                        AND `id_familia` <> '23' ORDER BY nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Família
        </td>
        <td>
            <select name="cmb_familia" title="Consultar Produto Acabado por: Família" class='combo'>
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
        <td>
            Ordenar por
        </td>
        <td>
            <select name='cmb_order_by' title='Ordernar' class='combo'>
                <option value="pa.referencia" selected>Referência</option>
                <option value="pa.discriminacao">Discriminação</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_preco_promocional' value='1' title='Preço Promocional <> 0' id='preco_promocional' class='checkbox'>
            <label for='preco_promocional'>Preço Promocional <> 0</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_todos_produtos_zerados' value='1' title='Todos os Produtos Zerados' id='todos' class='checkbox'>
            <label for='todos'>Todos os Produtos Zerados</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_mostrar_componentes' value='1' title="Mostrar Componentes" class="checkbox" id='label3'>
            <label for='label3'>
                Mostrar Componentes
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'lista_preco.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style="color:#ff9900;" class="botao">
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick="return submeter()" class="botao">
            <input type='button' name='cmd_lista_preco_eletronica' value='Lista de Preço Eletrônica' title='Lista de Preço Eletrônica' onclick='lista_preco_eletronica()' class='botao'>
            <input type='button' name='cmd_promocoes' value='Promoções' title='Promoções' onclick="html5Lightbox.showLightbox(7, '../../../../vendas/pdt/promocoes/promocoes.php')" class='botao'>
            <input type='button' name='cmd_relatorio' value='Relatório de Oferta Promocional vs Custo' title='Relatório de Oferta Promocional vs Custo' onclick="html5Lightbox.showLightbox(7, 'relatorio_oferta_promocional_custo.php')" style='color:darkgreen' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Não exibe os P.A(s) que estão com a marcação (ÑP) - Não Produzir.
</pre>
<?}?>