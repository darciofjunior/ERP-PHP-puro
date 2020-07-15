<?
require('../../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo/grupos_pas_vs_empresas_divisoes/cadastros/cadastros.php', '../../../../../../');

$mensagem[1] = "<font class='confirmacao'>QUALIDADE(S) AÇO(S) vs TEMPO(S) USINAGEM(NS) ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>QUALIDADE(S) AÇO(S) vs TEMPO(S) JÁ EXISTENTE.</font>";

//Procedimento normal de quando se carrega a Tela ...
$id_custo_qualidade_aco_vs_tempo_usinagem = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_custo_qualidade_aco_vs_tempo_usinagem'] : $_GET['id_custo_qualidade_aco_vs_tempo_usinagem'];

if(!empty($_POST['id_custo_qualidade_aco_vs_tempo_usinagem'])) {
    /*Verifico se já foi cadastrado esse Grupo vs Empresa Divisão e Qualidade do Aço diferente do Registro 
    que está sendo alterado ...*/
    $sql = "SELECT id_custo_qualidade_aco_vs_tempo_usinagem 
            FROM `custos_qualidades_acos_vs_tempos_usinagens` 
            WHERE `id_gpa_vs_emp_div` = '$_POST[cmb_gpa_vs_emp_div]' 
            AND `id_qualidade_aco` = '$_POST[cmb_qualidade_aco]' 
            AND `id_custo_qualidade_aco_vs_tempo_usinagem` <> '$_POST[id_custo_qualidade_aco_vs_tempo_usinagem]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não encontrou nada nessa situação acima ...
        $sql = "UPDATE `custos_qualidades_acos_vs_tempos_usinagens` SET `id_gpa_vs_emp_div` = '$_POST[cmb_gpa_vs_emp_div]', `id_qualidade_aco` = '$_POST[cmb_qualidade_aco]', `perc_tempo_a_mais` = '$_POST[txt_perc_tempo_a_mais]' WHERE `id_custo_qualidade_aco_vs_tempo_usinagem` = '$_POST[id_custo_qualidade_aco_vs_tempo_usinagem]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}

//Trago dados do "$id_custo_qualidade_aco_vs_tempo_usinagem" passado por parâmetro ...
$sql = "SELECT * 
        FROM `custos_qualidades_acos_vs_tempos_usinagens` 
        WHERE `id_custo_qualidade_aco_vs_tempo_usinagem` = '$id_custo_qualidade_aco_vs_tempo_usinagem' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Qualidade(s) Aço(s) vs Tempo(s) Usinagem(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Grupo PA vs Empresa Divisão ...
    if(!combo('form', 'cmb_gpa_vs_emp_div', '', 'SELECIONE O GRUPO PA vs EMPRESA DIVISÃO !')) {
        return false
    }
//Qualidade do Aço ...
    if(!combo('form', 'cmb_qualidade_aco', '', 'SELECIONE A QUALIDADE DO AÇO !')) {
        return false
    }
//% de Tempo a mais ...
    if(!texto('form', 'txt_perc_tempo_a_mais', '3', '0123456789,.', '% DE TEMPO A MAIS', '1')) {
        return false
    }
//Controle para não recarregar o Iframe abaixo que chamou esse Pop-UP ...
    document.form.nao_atualizar.value = 1
    limpeza_moeda('form', 'txt_perc_tempo_a_mais, ')
}

function atualizar_abaixo() {
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up ...
    if(document.form.nao_atualizar.value == 0) opener.location = opener.location.href
}
</Script>
</head>
<body onload='document.form.cmb_gpa_vs_emp_div.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--********************Controle de Tela********************-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='id_custo_qualidade_aco_vs_tempo_usinagem' value='<?=$id_custo_qualidade_aco_vs_tempo_usinagem;?>'>
<!--********************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Qualidade(s) Aço(s) vs Tempo(s) Usinagem(ns)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo PA vs Empresa Divisão:</b>
        </td>
        <td>
            <select name='cmb_gpa_vs_emp_div' title='Selecione o Grupo PA vs Empresa Divisão' class='combo'>
            <?
                $sql = "SELECT ged.id_gpa_vs_emp_div, CONCAT(gpa.nome, ' (', ed.razaosocial, ')') AS grupo_vs_empresa_divisao 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.`id_empresa_divisao` 
                        ORDER BY gpa.nome, ed.razaosocial ";
                echo combos::combo($sql, $campos[0]['id_gpa_vs_emp_div']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qualidade do Aço:</b>
        </td>
        <td>
            <select name='cmb_qualidade_aco' title='Selecione a Qualidade do Aço' class='combo'>
            <?
                $sql = "SELECT id_qualidade_aco, nome 
                        FROM `qualidades_acos` 
                        WHERE ativo = '1' ORDER BY nome ";
                echo combos::combo($sql, $campos[0]['id_qualidade_aco']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>% de Tempo a mais:</b>
        </td>
        <td>
            <input type='text' name='txt_perc_tempo_a_mais' value='<?=number_format($campos[0]['perc_tempo_a_mais'], 2, ',', '.')?>' title='Digite a % de Tempo a mais' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='5' maxlength='6' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_gpa_vs_emp_div.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>