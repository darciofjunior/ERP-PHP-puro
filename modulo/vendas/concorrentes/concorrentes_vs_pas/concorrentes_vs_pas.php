<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/concorrentes/concorrentes.php', '../../../../');

$mensagem[1] = '<font class="confirmacao">PA(S) ALTERADO COM SUCESSO.</font>';
$mensagem[2] = '<font class="confirmacao">PA(S) EXCLUÍDO(S) COM SUCESSO.</font>';

//Tratamento com as variáveis que vem por parâmetro ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $passo                  = $_POST['passo'];
    $id_concorrente         = $_POST['id_concorrente'];
    $cmb_gpa_vs_emp_div     = $_POST['cmb_gpa_vs_emp_div'];
    $opt_opcao              = $_POST['opt_opcao'];
    $txt_produto_acabado    = $_POST['txt_produto_acabado'];
}else {
    $passo                  = $_GET['passo'];
    $id_concorrente         = $_GET['id_concorrente'];
    $cmb_gpa_vs_emp_div     = $_GET['cmb_gpa_vs_emp_div'];
    $opt_opcao              = $_GET['opt_opcao'];
    $txt_produto_acabado    = $_GET['txt_produto_acabado'];
}

if($passo == 1) {
    $data_sys = date('Y-m-d H:i:s');
    foreach($_POST['hdd_concorrente_prod_acabado'] as $i => $id_concorrente_prod_acabado) {
        $com_ipi    = (in_array($id_concorrente_prod_acabado, $_POST['chkt_com_ipi'])) ? 'S' : 'N';
        $com_st     = (in_array($id_concorrente_prod_acabado, $_POST['chkt_com_st'])) ? 'S' : 'N';

        //Atualizando dados do PA do Concorrente ...
        $sql = "UPDATE `concorrentes_vs_prod_acabados` SET `preco_bruto` = '".$_POST['txt_preco_bruto'][$i]."', `desc_a` = '".$_POST['txt_desc_a'][$i]."', `desc_b` = '".$_POST['txt_desc_b'][$i]."', `desc_c` = '".$_POST['txt_desc_c'][$i]."', `desc_d` = '".$_POST['txt_desc_d'][$i]."', `desc_e` = '".$_POST['txt_desc_e'][$i]."', `acrescimo` = '".$_POST['txt_acrescimo'][$i]."', `com_ipi` = '$com_ipi', `com_st` = '$com_st', `preco_liquido` = '".$_POST['txt_preco_liq'][$i]."', `data_sys_ult_alt` = '$data_sys' WHERE `id_concorrente_prod_acabado` = '$id_concorrente_prod_acabado' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'concorrentes_vs_pas.php<?=urldecode($parametro);?>&valor=1'
    </Script>
<?
}else if($passo == 2) {
    for($i = 0; $i < count($_POST['chkt_concorrente_prod_acabado']); $i++) {
        $sql = "DELETE FROM `concorrentes_vs_prod_acabados` WHERE `id_concorrente_prod_acabado` = '".$_POST['chkt_concorrente_prod_acabado'][$i]."' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'concorrentes_vs_pas.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
//Exclui o PA que está atrelado ao Concorrente ...
    if(!empty($_POST['id_concorrente_prod_acabado'])) {//Exclusão dos Concorrente ...
        $sql = "DELETE FROM `concorrentes_vs_prod_acabados` WHERE `id_concorrente_prod_acabado` = ".$_POST['id_concorrente_prod_acabado']." LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }
?>
<html>
<head>
<title>.:: Concorrente(s) vs PA(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_concorrente_prod_acabado) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_concorrente_prod_acabado.value = id_concorrente_prod_acabado
        document.form.submit()
    }
}

function calcular_preco_liquido(indice) {
    //Variáveis globais ...
    preco_bruto = 0
    desc_a      = 0
    desc_b      = 0
    desc_c      = 0
    desc_d      = 0
    desc_e      = 0
//Significa que é para fazer o Cálculo de todas as linhas ...
    if(typeof(indice) == 'undefined') {
        for(var i = 0; i < document.form.elements['txt_preco_bruto[]'].length; i++) {
//Preço Bruto
            if(document.getElementById('txt_preco_bruto'+i).value != '') {
                preco_bruto = eval(strtofloat(document.getElementById('txt_preco_bruto'+i).value))
            }
//Desconto A
            if(document.getElementById('txt_desc_a'+i).value != '') {
                desc_a = eval(strtofloat(document.getElementById('txt_desc_a'+i).value))
            }
//Desconto B
            if(document.getElementById('txt_desc_b'+i).value != '') {
                desc_b = eval(strtofloat(document.getElementById('txt_desc_b'+i).value))
            }
//Desconto C
            if(document.getElementById('txt_desc_c'+i).value != '') {
                desc_c = eval(strtofloat(document.getElementById('txt_desc_c'+i).value))
            }
//Desconto D
            if(document.getElementById('txt_desc_d'+i).value != '') {
                desc_d = eval(strtofloat(document.getElementById('txt_desc_d'+i).value))
            }
//Desconto E
            if(document.getElementById('txt_desc_d'+i).value != '') {
                desc_e = eval(strtofloat(document.getElementById('txt_desc_e'+i).value))
            }
//Cálculo do Preço Líquido
            var preco_liquido   = preco_bruto * (100 - desc_a) / 100 * (100 - desc_b) / 100 * (100 - desc_c) / 100 * (100 - desc_d) / 100 * (100 - desc_e) / 100
//Acrescer IPI ...
            var valor_ipi       = (document.getElementById('chkt_com_ipi'+i).checked) ? (preco_liquido * strtofloat(document.getElementById('txt_ipi'+i).value) / 100) : 0
//Acrescer ST ...
            var valor_icms_st   = (document.getElementById('chkt_com_st'+i).checked) ? (preco_liquido * strtofloat(document.getElementById('hdd_valor_icms_st'+i).value) / 100) : 0

            document.getElementById('txt_preco_liq'+i).value = preco_liquido + valor_ipi + valor_icms_st
            document.getElementById('txt_preco_liq'+i).value = arred(document.getElementById('txt_preco_liq'+i).value, 2, 1)
        }
    }else {//Cálculo de 1 única linha apenas ...
//Preço Bruto
        if(document.getElementById('txt_preco_bruto'+indice).value != '') {
            preco_bruto = eval(strtofloat(document.getElementById('txt_preco_bruto'+indice).value))
        }
//Desconto A
        if(document.getElementById('txt_desc_a'+indice).value != '') {
            desc_a = eval(strtofloat(document.getElementById('txt_desc_a'+indice).value))
        }
//Desconto B
        if(document.getElementById('txt_desc_b'+indice).value != '') {
            desc_b = eval(strtofloat(document.getElementById('txt_desc_b'+indice).value))
        }
//Desconto C
        if(document.getElementById('txt_desc_c'+indice).value != '') {
            desc_c = eval(strtofloat(document.getElementById('txt_desc_c'+indice).value))
        }
//Desconto D
        if(document.getElementById('txt_desc_d'+indice).value != '') {
            desc_d = eval(strtofloat(document.getElementById('txt_desc_d'+indice).value))
        }
//Desconto E
        if(document.getElementById('txt_desc_d'+indice).value != '') {
            desc_e = eval(strtofloat(document.getElementById('txt_desc_e'+indice).value))
        }
//Cálculo do Preço Líquido
        var preco_liquido   = preco_bruto * (100 - desc_a) / 100 * (100 - desc_b) / 100 * (100 - desc_c) / 100 * (100 - desc_d) / 100 * (100 - desc_e) / 100
//Acrescer IPI ...
        var valor_ipi       = (document.getElementById('chkt_com_ipi'+indice).checked) ? (preco_liquido * strtofloat(document.getElementById('txt_ipi'+indice).value) / 100) : 0
//Acrescer ST ...
        var valor_icms_st   = (document.getElementById('chkt_com_st'+indice).checked) ? (preco_liquido * strtofloat(document.getElementById('hdd_valor_icms_st'+indice).value) / 100) : 0

        document.getElementById('txt_preco_liq'+indice).value = preco_liquido + valor_ipi + valor_icms_st
        document.getElementById('txt_preco_liq'+indice).value = arred(document.getElementById('txt_preco_liq'+indice).value, 2, 1)
    }
}

function calcular_preco_bruto(indice) {
    //Variáveis globais ...
    acrescimo   = 0
//Significa que é para fazer o Cálculo de todas as linhas ...
    if(typeof(indice) == 'undefined') {
        for(var i = 0; i < document.form.elements['txt_preco_bruto[]'].length; i++) {
//Acréscimo
            if(document.getElementById('txt_acrescimo'+i).value != '') {
                acrescimo = eval(strtofloat(document.getElementById('txt_acrescimo'+i).value))
            }
            document.getElementById('txt_preco_bruto'+i).value = document.getElementById('hdd_preco_bruto'+i).value * (100 + acrescimo) / 100
            document.getElementById('txt_preco_bruto'+i).value = arred(document.getElementById('txt_preco_bruto'+i).value, 2, 1)
        }
    }else {//Cálculo de 1 única linha apenas ...
//Acréscimo
        if(document.getElementById('txt_acrescimo'+indice).value != '') {
            acrescimo = eval(strtofloat(document.getElementById('txt_acrescimo'+indice).value))
        }
        document.getElementById('txt_preco_bruto'+indice).value = document.getElementById('hdd_preco_bruto'+indice).value * (100 + acrescimo) / 100
        document.getElementById('txt_preco_bruto'+indice).value = arred(document.getElementById('txt_preco_bruto'+indice).value, 2, 1)
    }
    calcular_preco_liquido()
}

//Só passo o nome do Campo ...
function atualizar_coluna(campo) {
    for(i = 1; i < document.form.elements[campo].length; i++) document.form.elements[campo][i].value = document.form.elements[campo][0].value
}

//Função que Trata os Valores das Caixas antes de enviar p/ o Banco de Dados ...
function enviar() {
    elementos = document.form.elements
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') elementos[i].value = strtofloat(elementos[i].value)
    }
}

//Função que Exclui os PA(s) atrelado(s) ao Fornecedor ...
function excluir_pas() {
    selecionados = 0
    elementos = document.form.elements
//Verifico se há algum checkbox selecionado ...
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name == 'chkt_concorrente_prod_acabado[]' && elementos[i].checked == true) {
            selecionados++
            break
        }
    }
//Caso não haja nenhum checkbox selecionado então ...
    if(selecionados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE(S) PA(S) ?')
        if(resposta == false) {
            return false
        }
    }
    document.form.passo.value = 2
    document.form.submit()
}

function selecionar_checkbox() {
    var valor = (document.form.chkt_tudo.checked == true) ? true : false
    var elementos = document.form.elements
//Aqui eu habilito ou desabilito os checkbox conforme selecionado no checkbox Principal ...
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name == 'chkt_concorrente_prod_acabado[]') elementos[i].checked = valor
    }
}

function verificar_teclas(event) {
    if(navigator.appName == 'Microsoft Internet Explorer') {
        if(event.keyCode == 13) document.form.submit()//Se Enter faz a Consulta.
    }else {
        if(event.which == 13) document.form.submit()//Se Enter faz a Consulta.
    }
}
</Script>
</head>
<body onload='calcular_preco_liquido();document.form.txt_produto_acabado.focus()'>
<form name='form' method='post' action=''>
<!--Variáveis que são Controle de Tela-->
<input type='hidden' name='id_concorrente' value='<?=$id_concorrente;?>'>
<input type='hidden' name='id_concorrente_prod_acabado'>
<input type='hidden' name='passo'>
<!--**********Esse hidden tem a função de resetar o Início e Pagina da Query quando o usuário 
trocar a combo de Grupo PA. (Empresa Divisão)**********-->
<input type='hidden' name='hdd_trocou_grupo_empresa_divisao'>
<!--**********************************-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='16'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <?
//Busca do Nome do Concorrente ...
        $sql = "SELECT `nome` 
                FROM `concorrentes` 
                WHERE `id_concorrente` = '$id_concorrente' LIMIT 1 ";
        $campos_concorrente = bancos::sql($sql);
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='16'>
        <?
            if(!empty($cmb_gpa_vs_emp_div)) $condicao_gpa_vs_emp_div = " AND pa.id_gpa_vs_emp_div = '$cmb_gpa_vs_emp_div' ";
            
            if($_POST['hdd_trocou_grupo_empresa_divisao'] == 1) {
                $inicio = 0;
                $pagina = 1;
            }
                
            if(!empty($txt_produto_acabado)) {
                $condicao_pa = ($opt_opcao == 1) ? " AND pa.`referencia` LIKE '%$txt_produto_acabado%' " : " AND pa.`discriminacao` LIKE '%$txt_produto_acabado%' ";
                /*Aqui, eu verifico se existe pelo menos 1 Produto vinculado ao Concorrente, de acordo com o que o usuário pesquisou ...
                Tenho que fazer para não dar erro nessa Tela, senão perco os objetos como a Combo das Empresas Divisões ...*/
                $sql = "SELECT cpa.*, pa.referencia, pa.discriminacao 
                        FROM `concorrentes_vs_prod_acabados` cpa 
                        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado $condicao_gpa_vs_emp_div $condicao_pa 
                        WHERE cpa.`id_concorrente` = '$id_concorrente' 
                        AND cpa.`ativo` = '1' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 0) $condicao_pa = '';//Zero essa variável para não dar erro com os objetos da Tela ...
            }
            //Aqui eu Busco todos os PA(s) do Concorrente do Grupo em Específico ...
            $sql = "SELECT cpa.*, pa.referencia, pa.discriminacao 
                    FROM `concorrentes_vs_prod_acabados` cpa 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = cpa.id_produto_acabado $condicao_gpa_vs_emp_div $condicao_pa 
                    WHERE cpa.`id_concorrente` = '$id_concorrente' 
                    AND cpa.`ativo` = '1' ORDER BY pa.referencia, pa.discriminacao ";
            $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
            $linhas = count($campos);
/************************************************************************************************************/
/***********************************************Lógica da Combo**********************************************/
/************************************************************************************************************/
            if($linhas > 0) {
?>
                Grupo P.A. (Empresa Divisão) 
                <select name='cmb_gpa_vs_emp_div' title='Selecione o Grupo P.A. (Empresa Divisão)' onchange='document.form.hdd_trocou_grupo_empresa_divisao.value = 1;document.form.submit()' class='combo'>
                <?
                    $sql = "SELECT ged.id_gpa_vs_emp_div, CONCAT(gpa.nome, ' (', ed.razaosocial, ') ') AS rotulo 
                            FROM `gpas_vs_emps_divs` ged 
                            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                            WHERE ged.`id_gpa_vs_emp_div` IN (
/*Se existir pelo menos 1 PA, então busco apenas os 'Grupos vs Empresas Divisões' dos PA(s) do Concorrente 
para listar nessa combo abaixo, cujo o intuito principal é facilitar no filtro p/ abaixo ...*/
                                SELECT DISTINCT(pa.id_gpa_vs_emp_div) 
                                FROM `concorrentes_vs_prod_acabados` cpa 
                                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = cpa.`id_produto_acabado` 
                                WHERE cpa.`id_concorrente` = '$id_concorrente' 
                                AND cpa.`ativo` = '1') 
/*******************************************************************************************/
                            AND gpa.`ativo` = '1' ORDER BY rotulo ";
                    echo combos::combo($sql, $cmb_gpa_vs_emp_div);
                ?>
                </select>
<?
            }
/************************************************************************************************************/
?>
            &nbsp;-
            <input type="radio" name="opt_opcao" value="1" title="Consultar Produtos Insumos por: Referência" id='label' checked>
            <label for='label'>
                Referência
            </label>
            <input type="radio" name="opt_opcao" value="2" title="Consultar Produtos Insumos por: Referência" id='label2'>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
            <input type='text' name="txt_produto_acabado" title="Digite o Produto Acabado" size="40" onkeyup="verificar_teclas(event)" class='caixadetexto'>
            &nbsp;
            <img src = "../../../../imagem/menu/pesquisar.png" id="img_pesquisar" onclick="document.form.submit()" title='Pesquisar' style='cursor:pointer' border="0">
            <br>
            Listagem de PA(s) do Concorrente(s) 
            <font color='yellow'>
                <?=$campos_concorrente[0]['nome'];?>
            </font>
            &nbsp;-&nbsp;
            <font color="#FFFF00">
                <img src = "../../../../imagem/menu/incluir.png" border='0' title="Atrelar PA(s)" alt="Atrelar PA(s)" onclick="nova_janela('incluir.php?id_concorrente=<?=$id_concorrente;?>', 'CALENDÁRIO', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')">
            </font>
        </td>
    </tr>
<?
	if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='14'>
            NÃO HÁ PA(S) CADASTRADO(S).
        </td>
    </tr>
<?
	}else {
?>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Ref
        </td>
        <td rowspan='2'>
            Discriminação
        </td>
        <td rowspan='2'>
            Acrésc. %
        </td>
        <td rowspan='2'>
            Pço Bruto R$
        </td>
        <td rowspan='2'>
            Desc A %
        </td>
        <td rowspan='2'>
            Desc B %
        </td>
        <td rowspan='2'>
            Desc C %
        </td>
        <td rowspan='2'>
            Desc D %
        </td>
        <td rowspan='2'>
            Desc E %
        </td>
        <td rowspan='2'>
            ICMS %
        </td>
        <td rowspan='2'>
            IPI %
        </td>
        <td rowspan='2'>
            ICMS ST %
        </td>
        <td colspan='2'>
            Acrescer
        </td>
        <td rowspan='2'>
            Pço Líq R$
        </td>
        <td rowspan='2'>
            <input type='checkbox' name='chkt_tudo' onclick='selecionar_checkbox()' class='checkbox'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            IPI
        </td>
        <td>
            ST
        </td>
    </tr>
<?
            $indice = 0;//Variável de Controle ...
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <input type='text' name='txt_acrescimo[]' id='txt_acrescimo<?=$i;?>' value="<?=number_format($campos[$i]['acrescimo'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '1', event);calcular_preco_bruto('<?=$i;?>', '<?=$campos[$i]['preco_bruto'];?>')" maxlength='6' size='5' tabIndex='2<?=$indice;?>' class='caixadetexto'>
            <?
/*A idéia da Seta é de exibir somente quando eu tiver + do que 1 registro e somente na Primeira Linha 
p/ copiar o valor p/ as demais caixinhas ...*/
                if($linhas > 1 && $i == 0) {
            ?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_acrescimo[]');calcular_preco_bruto()">
            <?
                }else {
                    if($linhas > 1) echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_preco_bruto[]' id='txt_preco_bruto<?=$i;?>' value="<?=number_format($campos[$i]['preco_bruto'], 3, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '3', '', event);calcular_preco_liquido('<?=$i;?>')" maxlength='10' size='8' tabIndex='1<?=$indice;?>' class='caixadetexto'>
            <!--******Esse hidden é utilizado dentro da função "calcular_preco_bruto" pq é o único 
            local que guarda o Preço Bruto de origem após várias alterações feitas pelo Usuario******-->
            <input type='hidden' name='hdd_preco_bruto[]' id='hdd_preco_bruto<?=$i;?>' value="<?=$campos[$i]['preco_bruto'];?>">
            <?
/*A idéia da Seta é de exibir somente quando eu tiver + do que 1 registro e somente na Primeira Linha 
p/ copiar o valor p/ as demais caixinhas ...*/
                if($linhas > 1 && $i == 0) {
            ?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_preco_bruto[]');calcular_preco_liquido()">
            <?
                }else {
                    if($linhas > 1) echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_desc_a[]' id='txt_desc_a<?=$i;?>' value="<?=number_format($campos[$i]['desc_a'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '1', event);calcular_preco_liquido('<?=$i;?>')" maxlength='6' size='5' tabIndex='3<?=$indice;?>' class='caixadetexto'>
            <?
/*A idéia da Seta é de exibir somente quando eu tiver + do que 1 registro e somente na Primeira Linha 
p/ copiar o valor p/ as demais caixinhas ...*/
                if($linhas > 1 && $i == 0) {
            ?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_desc_a[]');calcular_preco_liquido()">
            <?
                }else {
                    if($linhas > 1) echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_desc_b[]' id='txt_desc_b<?=$i;?>' value="<?=number_format($campos[$i]['desc_b'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '1', event);calcular_preco_liquido('<?=$i;?>')" maxlength='6' size='5' tabIndex='4<?=$indice;?>' class='caixadetexto'>
            <?
/*A idéia da Seta é de exibir somente quando eu tiver + do que 1 registro e somente na Primeira Linha 
p/ copiar o valor p/ as demais caixinhas ...*/
                if($linhas > 1 && $i == 0) {
            ?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_desc_b[]');calcular_preco_liquido()">
            <?
                }else {
                    if($linhas > 1) echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_desc_c[]' id='txt_desc_c<?=$i;?>' value="<?=number_format($campos[$i]['desc_c'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_liquido('<?=$i;?>')" maxlength='6' size='5' tabIndex='5<?=$indice;?>' class='caixadetexto'>
            <?
/*A idéia da Seta é de exibir somente quando eu tiver + do que 1 registro e somente na Primeira Linha 
p/ copiar o valor p/ as demais caixinhas ...*/
                if($linhas > 1 && $i == 0) {
            ?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_desc_c[]');calcular_preco_liquido()">
            <?
                }else {
                    if($linhas > 1) echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_desc_d[]' id='txt_desc_d<?=$i;?>' value="<?=number_format($campos[$i]['desc_d'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_liquido('<?=$i;?>')" maxlength='6' size='5' tabIndex='6<?=$indice;?>' class='caixadetexto'>
            <?
/*A idéia da Seta é de exibir somente quando eu tiver + do que 1 registro e somente na Primeira Linha 
p/ copiar o valor p/ as demais caixinhas ...*/
                if($linhas > 1 && $i == 0) {
            ?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_desc_d[]');calcular_preco_liquido()">
            <?
                }else {
                    if($linhas > 1) echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_desc_e[]' id='txt_desc_e<?=$i;?>' value="<?=number_format($campos[$i]['desc_e'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_preco_liquido('<?=$i;?>')" maxlength='6' size='5' tabIndex='7<?=$indice;?>' class='caixadetexto'>
            <?
/*A idéia da Seta é de exibir somente quando eu tiver + do que 1 registro e somente na Primeira Linha 
p/ copiar o valor p/ as demais caixinhas ...*/
                if($i == 0) {
            ?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_desc_e[]');calcular_preco_liquido()">
            <?
                }else {
                    if($linhas > 1) echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            ?>
        </td>
        <td>
            <?
                //Nesse caso em específico, eu tenho que trazer os valores cadastrados da própria Base de Dados ...
                $sql = "SELECT cf.`ipi`, icms.`icms`, icms.`reducao`, icms.`iva` 
                        FROM `produtos_acabados` pa  
                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                        INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                        INNER JOIN `icms` ON icms.`id_classific_fiscal` = cf.`id_classific_fiscal` AND icms.`id_uf` = '1' 
                        WHERE pa.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
                $campos_pa_na_uf_sp = bancos::sql($sql);
                
                $icms_c_red                 = (100 * $campos_pa_na_uf_sp[0]['icms'] / 100 * (100 - $campos_pa_na_uf_sp[0]['reducao']) / 100);
                $base_calculo_icms_st_rs    = (100 + $campos_pa_na_uf_sp[0]['ipi']) * (1 + $campos_pa_na_uf_sp[0]['iva'] / 100);
                $valor_icms_st_rs           = ($base_calculo_icms_st_rs * $campos_pa_na_uf_sp[0]['icms'] / 100) - $icms_c_red;
                $icms_st_perc               = round($valor_icms_st_rs, 2);
            ?>
            <input type='text' name='txt_icms[]' id='txt_icms<?=$i;?>' value="<?=number_format($icms_c_red, 2, ',', '.');?>" size='5' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_ipi[]' id='txt_ipi<?=$i;?>' value="<?=number_format($campos_pa_na_uf_sp[0]['ipi'], 2, ',', '.');?>" size='5' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='hdd_valor_icms_st[]' id='hdd_valor_icms_st<?=$i?>' value="<?=number_format($icms_st_perc, 2, ',', '.');?>" size='5' class='textdisabled' disabled>
        </td>
        <td>
            <?
                $checked_com_ipi = ($campos[$i]['com_ipi'] == 'S') ? 'checked' : '';
                /*É guardado o id_concorrente_prod_acabado dentro desse checkbox porque somente dessa forma que 
                esse objeto não perde o seu índice ...*/
            ?>
            <input type='checkbox' name='chkt_com_ipi[]' id='chkt_com_ipi<?=$i;?>' value='<?=$campos[$i]['id_concorrente_prod_acabado'];?>' onclick="calcular_preco_liquido('<?=$i;?>')" class='checkbox' <?=$checked_com_ipi;?>>
        </td>
        <td>
            <?
                $checked_com_st = ($campos[$i]['com_st'] == 'S') ? 'checked' : '';
                /*É guardado o id_concorrente_prod_acabado dentro desse checkbox porque somente dessa forma que 
                esse objeto não perde o seu índice ...*/
            ?>
            <input type='checkbox' name='chkt_com_st[]' id='chkt_com_st<?=$i;?>' value='<?=$campos[$i]['id_concorrente_prod_acabado'];?>' onclick="calcular_preco_liquido('<?=$i;?>')" class='checkbox' <?=$checked_com_st;?>>
        </td>
        <td>
            <input type='text' name='txt_preco_liq[]' id='txt_preco_liq<?=$i;?>' value="<?=number_format($campos[$i]['preco_liquido'], 2, ',', '.');?>" onfocus="document.getElementById('txt_preco_bruto<?=$i?>').focus()" size='9' class='textdisabled'>
        </td>
        <td>
            <!--
                Apesar de termos 2 caixas que armazenam o mesmo id, a diferença entra elas é a seguinte: 
                
                1) O Checkbox serve somente p/ excluir(mos) o(s) Item(ns) que no caso estejam selecionados ...
                2) o Hidden serve p/ alterar(mos) o(s) Item(ns) independente de ter(em) sido selecionados ...
            -->
            <input type='checkbox' name='chkt_concorrente_prod_acabado[]' value='<?=$campos[$i]['id_concorrente_prod_acabado'];?>' class='checkbox'>
            <input type='hidden' name='hdd_concorrente_prod_acabado[]' value='<?=$campos[$i]['id_concorrente_prod_acabado'];?>'>
        </td>
    </tr>
<?
                $indice*= 2;
            }
	}
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='16'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../concorrentes.php'" class='botao'>
            <?
//Só irá mostrar esse botão quando existir pelo Menos 1 Registro ...
                if($linhas > 0) {
            ?>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form','REDEFINIR');document.getElementById('txt_preco_bruto0').focus()" style="color:#ff9900;" class='botao'>
            <input type='button' name="cmd_salvar" value="Salvar" title="Salvar" onclick="enviar();document.form.passo.value = 1;document.form.submit()" style="color:green" class='botao'>
            <input type='button' name="cmd_excluir_pas" value="Excluir PA(s)" title="Excluir PA(s)" onclick="excluir_pas()" style="color:black" class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?}?>