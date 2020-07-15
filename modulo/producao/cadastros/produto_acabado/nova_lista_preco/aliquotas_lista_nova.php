<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/nova_lista_preco/lista_preco.php', '../../../../../');
$mensagem[1] = "<font class='confirmacao'>AL�QUOTA(S) DE LISTA NOVA ALTERADA(S) COM SUCESSO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmb_grupo_pa_vs_empresa_divisao 	= $_POST['cmb_grupo_pa_vs_empresa_divisao'];
}else {
    $cmb_grupo_pa_vs_empresa_divisao    = $_GET['cmb_grupo_pa_vs_empresa_divisao'];
}

if(!empty($_POST['hdd_gpa_vs_emp_div'])) {
    foreach($_POST['hdd_gpa_vs_emp_div'] as $i => $id_gpa_vs_emp_div) {
        $sql = "UPDATE `gpas_vs_emps_divs` SET `desc_a_lista_nova` = '".$_POST['txt_desc_a_lista_nova'][$i]."', `desc_b_lista_nova` = '".$_POST['txt_desc_b_lista_nova'][$i]."', `acrescimo_lista_nova` = '".$_POST['txt_acrescimo_lista_nova'][$i]."', `margem_lucro_exp` = '".$_POST['txt_margem_lucro_exp_atual'][$i]."', `margem_lucro_minima` = '".$_POST['txt_margem_lucro_minima_atual'][$i]."' WHERE `id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Aqui eu busco qual � o Grupo equivalente ao Grupo do PA vs Empresa Divis�o ...
if(!empty($cmb_grupo_pa_vs_empresa_divisao)) {
    $sql = "SELECT id_grupo_pa 
            FROM `gpas_vs_emps_divs` 
            WHERE `id_gpa_vs_emp_div` = '$cmb_grupo_pa_vs_empresa_divisao' LIMIT 1 ";
    $campos_grupo_pa    = bancos::sql($sql);
    $condicao_grupo_pa  = " AND `id_grupo_pa` = '".$campos_grupo_pa[0]['id_grupo_pa']."' ";
}

$sql = "SELECT gpa.*, cf.`classific_fiscal`, f.`nome` AS familia 
        FROM `grupos_pas` gpa 
        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
        INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
        WHERE gpa.`ativo` = '1' 
        $condicao_grupo_pa ORDER BY gpa.nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Alterar Al�quota(s) de Lista Nova ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function copiar_valores(indice_coluna) {
    var elementos = document.form.elements
//Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_gpa_vs_emp_div[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['hdd_gpa_vs_emp_div[]'].length)
    }
    if(indice_coluna == 0) {//�ndice de Coluna que Equivale a Coluna Desconto A Lista Nova
        for(var i = 1; i < linhas; i++) elementos['txt_desc_a_lista_nova[]'][i].value = elementos['txt_desc_a_lista_nova[]'][0].value
    }else if(indice_coluna == 1) {//�ndice de Coluna que Equivale a Coluna Desconto B Lista Nova
        for(var i = 1; i < linhas; i++) elementos['txt_desc_b_lista_nova[]'][i].value = elementos['txt_desc_b_lista_nova[]'][0].value
    }else if(indice_coluna == 2) {//�ndice de Coluna que Equivale a Coluna Acr�scimo Lista Nova
        for(var i = 1; i < linhas; i++) elementos['txt_acrescimo_lista_nova[]'][i].value = elementos['txt_acrescimo_lista_nova[]'][0].value
    }else if(indice_coluna == 3) {//�ndice de Coluna que Equivale a Coluna Margem de Lucro Exp
        for(var i = 1; i < linhas; i++) elementos['txt_margem_lucro_exp_atual[]'][i].value = elementos['txt_margem_lucro_exp_atual[]'][0].value
    }else if(indice_coluna == 4) {//�ndice de Coluna que Equivale a Coluna Margem de Lucro M�nima
        for(var i = 1; i < linhas; i++) elementos['txt_margem_lucro_minima_atual[]'][i].value = elementos['txt_margem_lucro_minima_atual[]'][0].value
    }
}

function validar() {
    var elementos = document.form.elements
//Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_gpa_vs_emp_div[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['hdd_gpa_vs_emp_div[]'].length)
    }
//Chama a fun��o de acordo com a qtde de descontos ...
    for(var j = 0; j < linhas; j++) {
        elementos['txt_desc_a_lista_nova[]'][j].value           = strtofloat(elementos['txt_desc_a_lista_nova[]'][j].value)
        elementos['txt_desc_b_lista_nova[]'][j].value           = strtofloat(elementos['txt_desc_b_lista_nova[]'][j].value)
        elementos['txt_acrescimo_lista_nova[]'][j].value        = strtofloat(elementos['txt_acrescimo_lista_nova[]'][j].value)
        elementos['txt_margem_lucro_exp_atual[]'][j].value      = strtofloat(elementos['txt_margem_lucro_exp_atual[]'][j].value)
        elementos['txt_margem_lucro_minima_atual[]'][j].value   = strtofloat(elementos['txt_margem_lucro_minima_atual[]'][j].value)
    }
    document.form.nao_atualizar.value = 1
}

function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.location = 'lista_preco_nacional.php<?=$parametro;?>'
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' action='' method='post' onsubmit='return validar()'>
<!--********************************Controles de Tela********************************-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='cmb_grupo_pa_vs_empresa_divisao' value='<?=$cmb_grupo_pa_vs_empresa_divisao;?>'>
<!--*********************************************************************************-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Alterar Al�quota(s) de Lista Nova
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Grupo
        </td>
        <td colspan='3'>
            Al�quotas de Lista Nova
        </td>
        <td colspan='3'>
            Al�quotas Atuais
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Desconto A
        </td>
        <td>
            Desconto B
        </td>
        <td>
            Acr�scimo
        </td>
        <td>
            ML. Min Exp
        </td>
        <td>
            ML. Min Nac
        </td>
    </tr>
<?
    $indice = 0;//A princ�pio o Cursor vai p/ a Primeira Linha ...
    for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td bgcolor="#D8D8D8">
            <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            <?
                echo $campos[$i]['nome'];
                if($campos[$i]['desenho_para_etiqueta'] != '') {
            ?>
                    <img src="<?='../../../../../imagem/desenhos_grupos_pas/'.$campos[$i]['desenho_para_etiqueta'];?>" width="40" height="12">
            <?
                }
            ?>
        </td>
        <td bgcolor="#D8D8D8">
            <b>Fam�lia => </b> <?=$campos[$i]['familia'];?>
        </td>
        <td bgcolor="#D8D8D8">
            <b>L. M�n. Prod R$ </b> <?=segurancas::number_format($campos[$i]['lote_min_producao_reais'], 2, '.');?>
        </td>
        <td bgcolor="#D8D8D8">
            <b>Pzo Entrega - </b>
        <?
            $vetor_prazos_entrega = vendas::prazos_entrega();
            foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
                //Compara o valor do Banco com o valor do Vetor
                if($campos[$i]['prazo_entrega'] == $indice) {//Se igual seleciona esse valor
                    echo $prazo_entrega;
                }
            }
        ?>
        </td>
        <td bgcolor="#D8D8D8">
            <b>CF: </b><?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td bgcolor="#D8D8D8">
            <b>Obs: </b><?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
//Aqui traz todos as empresas divis�es e descontos que est�o relacionados a este grupo aqui do loop
        $sql = "SELECT ged.`id_gpa_vs_emp_div`, ged.`desc_base_a_nac`, ged.`desc_base_b_nac`, ged.`acrescimo_base_nac`, 
                ged.`desc_a_lista_nova`, ged.`desc_b_lista_nova`, ged.`acrescimo_lista_nova`, ged.`margem_lucro_exp`, 
                ged.`margem_lucro_minima`, ed.`id_empresa_divisao`, ed.`razaosocial` 
                FROM `gpas_vs_emps_divs` ged 
                INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                WHERE ged.`id_grupo_pa` = ".$campos[$i]['id_grupo_pa']." ORDER BY ed.razaosocial ";
        $campos_empresas_divisoes = bancos::sql($sql);
        $linhas_empresas_divisoes = count($campos_empresas_divisoes);
        if($linhas_empresas_divisoes > 0) {
            for($j = 0; $j < $linhas_empresas_divisoes; $j++) {
?>
    <tr class='linhanormal'>
        <td>
            <b>Divis�o:</b>
            <?=$campos_empresas_divisoes[$j]['razaosocial'];?>
        </td>
        <td>
            <input type='text' name='txt_desc_a_lista_nova[]' id='txt_desc_a_lista_nova<?=$indice;?>' value='<?=number_format($campos_empresas_divisoes[$j]['desc_a_lista_nova'], 2, ',', '.');?>' title='Digite o Desconto A' size='8' maxlenght='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//S� ir� mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_valores(0)">
            <?
                }
                echo '<b>Atual='.number_format($campos_empresas_divisoes[$j]['desc_base_a_nac'], 2, ',', '.').'</b>';
            ?>
        </td>
        <td>
            <input type='text' name='txt_desc_b_lista_nova[]' id='txt_desc_b_lista_nova<?=$indice;?>' value="<?=number_format($campos_empresas_divisoes[$j]['desc_b_lista_nova'], 2, ',', '.');?>" title="Digite o Desconto B" size="8" maxlenght="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//S� ir� mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_valores(1)">
            <?
                }
                echo '<b>Atual='.number_format($campos_empresas_divisoes[$j]['desc_base_b_nac'], 2, ',', '.').'</b>';
            ?>
        </td>
        <td>
            <input type='text' name='txt_acrescimo_lista_nova[]' id='txt_acrescimo_lista_nova<?=$indice;?>' value="<?=number_format($campos_empresas_divisoes[$j]['acrescimo_lista_nova'], 2, ',', '.');?>" title="Digite o Acr�scimo" size="8" maxlenght="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//S� ir� mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_valores(2)">
            <?
                }
                echo '<b>Atual='.number_format($campos_empresas_divisoes[$j]['acrescimo_lista_nova'], 2, ',', '.').'</b>';
            ?>
        </td>
        <td>
            <input type='text' name='txt_margem_lucro_exp_atual[]' id='txt_margem_lucro_exp_atual<?=$indice;?>' value="<?=number_format($campos_empresas_divisoes[$j]['margem_lucro_exp'], 2, ',', '.');?>" title="Digite a Margem de Lucro de Exporta��o" size="8" maxlenght="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//S� ir� mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_valores(3)">
            <?
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_margem_lucro_minima_atual[]' id='txt_margem_lucro_minima_atual<?=$indice;?>' value="<?=number_format($campos_empresas_divisoes[$j]['margem_lucro_minima'], 2, ',', '.');?>" title="Digite a Margem de Lucro M�nima" size="8" maxlenght="6" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
            <?
                if($i == 0 && $linhas > 1 && $j == 0) {//S� ir� mostrar na Primeira Linha se tiver pelo menos 2 registros ...
            ?>
                    <img src="../../../../../imagem/seta_abaixo.gif" border="0" title="Copiar Geral" alt="Copiar Geral" onClick="copiar_valores(4)">
            <?
                }
            ?>
            <input type='hidden' name='hdd_gpa_vs_emp_div[]' id='hdd_gpa_vs_emp_div<?=$indice;?>' value="<?=$campos_empresas_divisoes[$j]['id_gpa_vs_emp_div'];?>">
        </td>
    </tr>
<?
                if(!empty($cmb_grupo_pa_vs_empresa_divisao)) {//Essa vari�vel me serve p/ fazer a fun��o de Onload ...
                    if($cmb_grupo_pa_vs_empresa_divisao == $campos_empresas_divisoes[$j]['id_gpa_vs_emp_div']) $indice_cursor = $indice;
                }
                $indice++;
            }
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<!--***********Coloquei essa fun��o aqui embaixo, porque dependia dos valores do PHP que foram carregados no for acima***********-->
<!--Essa fun��o faz um Papel de Onload-->
<Script Language = 'JavaScript'>
    document.getElementById('txt_desc_a_lista_nova<?=$indice_cursor;?>').focus()
    document.getElementById('txt_desc_a_lista_nova<?=$indice_cursor;?>').select()
</Script>