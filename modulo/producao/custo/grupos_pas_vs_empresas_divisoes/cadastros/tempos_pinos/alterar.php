<?
require('../../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo/grupos_pas_vs_empresas_divisoes/cadastros/cadastros.php', '../../../../../../');

$mensagem[1] = "<font class='confirmacao'>TEMPO(S) PINO(S) ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>TEMPO(S) PINO(S) JÁ EXISTENTE.</font>";

//Procedimento normal de quando se carrega a Tela ...
$id_custo_tempo_pino_conico = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_custo_tempo_pino'] : $_GET['id_custo_tempo_pino'];

if(!empty($_POST['id_custo_tempo_pino'])) {
    /*Verifico se já foi cadastrado esse Grupo vs Empresa Divisão e Qualidade do Aço diferente do Registro 
    que está sendo alterado ...*/
    $sql = "SELECT id_custo_tempo_pino 
            FROM `custos_tempos_pinos` 
            WHERE `id_maquina` = '$_POST[cmb_maquina]' 
            AND `variacao_diametro_pino_conico` = '$_POST[txt_variacao_diametro_pino_conico]' 
            AND `id_custo_tempo_pino` <> '$_POST[id_custo_tempo_pino]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não encontrou nada nessa situação acima ...
        $sql = "UPDATE `custos_tempos_pinos` SET `id_maquina` = '$_POST[cmb_maquina]', `variacao_diametro_pino_conico` = '$_POST[txt_variacao_diametro_pino_conico]', `diametro_menor_din7977` = '$_POST[txt_diametro_menor_din7977]', `comprimento_pino_paralelo` = '$_POST[txt_comprimento_pino_paralelo]', `perc_tempo_a_mais` = '$_POST[txt_perc_tempo_a_mais]' WHERE `id_custo_tempo_pino` = '$_POST[id_custo_tempo_pino]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}

//Trago dados do "$id_custo_tempo_pino" passado por parâmetro ...
$sql = "SELECT * 
        FROM `custos_tempos_pinos` 
        WHERE `id_custo_tempo_pino` = '$id_custo_tempo_pino' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Tempo(s) Pino(s) - Usinagem Conicidade CNC ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Máquina ...
    if(!combo('form', 'cmb_maquina', '', 'SELECIONE A MÁQUINA !')) {
        return false
    }
//Variação do Diâmetro Pino Cônico ...
    if(document.form.txt_variacao_diametro_pino_conico.value != '') {
        if(!texto('form', 'txt_variacao_diametro_pino_conico', '3', '0123456789,.', 'VARIAÇÃO DO DIÂMETRO PINO CÔNICO', '1')) {
            return false
        }
    }
//Diâmetro Menor Din 7977 ...
    if(document.form.txt_diametro_menor_din7977.value != '') {
        if(!texto('form', 'txt_diametro_menor_din7977', '3', '0123456789,.', 'DIÂMETRO MENOR DIN 7977', '2')) {
            return false
        }
    }
//Comprimento Pino Paralelo ...
    if(document.form.txt_comprimento_pino_paralelo.value != '') {
        if(!texto('form', 'txt_comprimento_pino_paralelo', '3', '0123456789,.', 'COMPRIMENTO PINO PARALELO', '2')) {
            return false
        }
    }
//% de Tempo a mais ...
    if(!texto('form', 'txt_perc_tempo_a_mais', '3', '0123456789,.', '% DE TEMPO A MAIS', '1')) {
        return false
    }
//Controle para não recarregar o Iframe abaixo que chamou esse Pop-UP ...
    document.form.nao_atualizar.value = 1
    limpeza_moeda('form', 'txt_variacao_diametro_pino_conico, txt_diametro_menor_din7977, txt_comprimento_pino_paralelo, txt_perc_tempo_a_mais, ')
}

function atualizar_abaixo() {
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up ...
    if(document.form.nao_atualizar.value == 0) opener.location = opener.location.href
}
</Script>
</head>
<body onload='document.form.cmb_maquina.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--********************Controle de Tela********************-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='id_custo_tempo_pino' value='<?=$id_custo_tempo_pino;?>'>
<!--********************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Tempo(s) Pino(s) - Usinagem Conicidade CNC
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Máquina:</b>
        </td>
        <td>
            <select name='cmb_maquina' title='Selecione a Máquina' class='combo'>
            <?
                $sql = "SELECT id_maquina, nome 
                        FROM `maquinas` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql, $campos[0]['id_maquina']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Variação do Diâmetro Pino Cônico:
        </td>
        <td>
            <input type='text' name='txt_variacao_diametro_pino_conico' value='<?=number_format($campos[0]['variacao_diametro_pino_conico'], 2, ',', '.');?>' title='Digite a Variação do Diâmetro Pino Cônico' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='5' maxlength='6' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Diâmetro Menor Din 7977:
        </td>
        <td>
            <input type='text' name='txt_diametro_menor_din7977' value='<?=number_format($campos[0]['diametro_menor_din7977'], 2, ',', '.');?>' title='Digite o Diâmetro Menor Din 7977' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='5' maxlength='6' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Comprimento Pino Paralelo:
        </td>
        <td>
            <input type='text' name='txt_comprimento_pino_paralelo' value='<?=number_format($campos[0]['comprimento_pino_paralelo'], 2, ',', '.');?>' title='Digite o Comprimento Pino Paralelo' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='5' maxlength='6' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>% de Tempo a mais:</b>
        </td>
        <td>
            <input type='text' name='txt_perc_tempo_a_mais' value='<?=number_format($campos[0]['perc_tempo_a_mais'], 2, ',', '.');?>' title='Digite a % de Tempo a mais' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='5' maxlength='6' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_maquina.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>