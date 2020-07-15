<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
session_start('funcionarios');
if($permissao == 'inc') {//Significa q vem de incluir grupo P.A.
    segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/grupo_pa/incluir.php', '../../../../../');
}else {//Significa q vem de alterar grupo P.A.
    segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/grupo_pa/alterar.php', '../../../../../');
}
$mensagem[1] = "<font class='confirmacao'>EMPRESA DIVISÃO ALTERADA COM SUCESSO PARA GRUPO P.A.</font>";

if($passo == 1) {
    $realcar        = (!empty($_POST['chkt_realcar'])) ? 'S' : 'N';
    $data_limite    = data::datatodate($_POST['txt_data_limite'], '-');
    //Alteração dos dados na Empresa Divisão vs Grupo P.A. ...
    $sql = "UPDATE `gpas_vs_emps_divs` SET `desc_base_a_nac` = '$_POST[txt_desc_base_nac_a]', `desc_base_b_nac` = '$_POST[txt_desc_base_nac_b]', `acrescimo_base_nac` = '$_POST[txt_acrescimo_base_nac]', `margem_lucro_exp` = '$_POST[txt_ml_min_exp]', `margem_lucro_minima` = '$_POST[txt_ml_min_nac]', `comissao_extra` = '$_POST[txt_comissao_extra]', `data_limite` = '$data_limite', `realcar` = '$realcar', `path_pdf` = '$_POST[txt_caminho_pdf_site]' WHERE `id_gpa_vs_emp_div` = '$_POST[id_gpa_vs_emp_div]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_empresa_divisao.php?permissao=<?=$permissao;?>&id_gpa_vs_emp_div=<?=$_POST['id_gpa_vs_emp_div'];?>&valor=1'
    </Script>
<?
}else {
    $sql = "SELECT ged.*, ed.razaosocial 
            FROM `gpas_vs_emps_divs` ged 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao AND ed.`ativo` = '1' 
            WHERE ged.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if($campos[0]['data_limite'] != '0000-00-00') $data_limite = data::datetodata($campos[0]['data_limite'], '/');
?>
<html>
<head>
<title>.:: Alterar Empresa(s) Divisão(ões) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Desconto Base Nacional A
    if(!texto('form', 'txt_desc_base_nac_a', '1', '0123456789,.', 'DESCONTO BASE NACIONAL A', '2')) {
        return false
    }
//Desconto Base Nacional B
    if(!texto('form', 'txt_desc_base_nac_b', '1', '0123456789,.', 'DESCONTO BASE NACIONAL B', '2')) {
        return false
    }
//Acréscimo Base Nacional
    if(!texto('form', 'txt_acrescimo_base_nac', '1', '0123456789,.', 'ACRÉSCIMO BASE NACIONAL', '2')) {
        return false
    }
//Margem de Lucro Min. Exportação ...
    if(!texto('form', 'txt_ml_min_exp', '1', '0123456789,.', 'MARGEM DE LUCRO MÍNIMA DE EXPORTAÇÃO', '1')) {
        return false
    }
//Margem de Lucro Min. Nacional ...
    if(!texto('form', 'txt_ml_min_nac', '1', '0123456789,.', 'MARGEM DE LUCRO MÍNIMA NACIONAL', '1')) {
        return false
    }
//Comissão Extra
    if(!texto('form', 'txt_comissao_extra', '1', '0123456789,.', 'COMISSÃO EXTRA', '1')) {
        return false
    }
//Data Limite
    if(document.form.txt_data_limite.value != '') {
        if(!data('form', 'txt_data_limite', '4000', 'LIMITE')) {
            return false
        }
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
    limpeza_moeda('form', 'txt_desc_base_nac_a, txt_desc_base_nac_b, txt_acrescimo_base_nac, txt_margem_lucro_exp, txt_comissao_extra, ')
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.location = parent.location.href//Atualiza a Tela de Baixo ...
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar()">
<!--Controle de Tela-->
<input type='hidden' name='id_gpa_vs_emp_div' value="<?=$id_gpa_vs_emp_div;?>">
<input type='hidden' name='nao_atualizar'>
<!--****************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Empresa(s) Divisão(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Razão Social:</b>
        </td>
        <td>
            <font color='#0041A2'>
                <b><?=$campos[0]['razaosocial'];?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Desc. Base A Nac.:</b>
        </td>
        <td>
            <input type='text' name="txt_desc_base_nac_a" value="<?=number_format($campos[0]['desc_base_a_nac'], 2, ',', '.');?>" title="Digite o Desconto Base Nacional A" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='caixadetexto'>&nbsp;%
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Desc. Base B Nac.:</b>
        </td>
        <td>
            <input type='text' name="txt_desc_base_nac_b" value="<?=number_format($campos[0]['desc_base_b_nac'], 2, ',', '.');?>" title="Digite o Desconto Base Nacional B" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='caixadetexto'>&nbsp;%
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Acrésc. Base Nac.:</b>
        </td>
        <td>
            <input type='text' name="txt_acrescimo_base_nac" value="<?=number_format($campos[0]['acrescimo_base_nac'], 2, ',', '.');?>" title="Digite o Acréscimo Base Nacional" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='caixadetexto'>&nbsp;%
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>ML Min Exp.:</b>
        </td>
        <td>
            <input type='text' name='txt_ml_min_exp' value="<?=number_format($campos[0]['margem_lucro_exp'], 2, ',', '.');?>" title="Digite a Margem de Lucro Exp" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='caixadetexto'>&nbsp;%
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>ML Min Nac.:</b>
        </td>
        <td>
            <input type='text' name='txt_ml_min_nac' value='<?=number_format($campos[0]['margem_lucro_minima'], 2, ',', '.');?>' title="Digite a Margem de Lucro Exp" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='8' class='caixadetexto'>&nbsp;%
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Comissão Extra:</b>
        </td>
        <td>
            <input type='text' name="txt_comissao_extra" value="<?=number_format($campos[0]['comissao_extra'], 1, ',', '.');?>" title="Digite a Comissão Extra" onkeyup="verifica(this, 'moeda_especial', '1', '', event)" maxlength='6' size='8' class='caixadetexto'>&nbsp;%
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Limite:</b>
        </td>
        <td>
            <input type='text' name='txt_data_limite' value="<?=$data_limite;?>" title="Digite a Data Limite" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
            &nbsp;<img src = '../../../../../imagem/calendario.gif' width='12' height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../../calendario/calendario.php?campo=txt_data_limite&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <label for='chkt_realcar'>
                <b>Realçar p/ Vender:</b>
            </label>
        </td>
        <td>
            <?$checked = ($campos[0]['realcar'] == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_realcar' id='chkt_realcar' value='S' title='Realçar' class='checkbox' <?=$checked;?>>
            <font color='red'>
                <b>(Em algumas telas os PA(s) pertencentes este Grupo vs Empresa Divisão serão destacados.)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Caminho do PDF do Site:
        </td>
        <td>
            <input type='text' name="txt_caminho_pdf_site" value="<?=$campos[0]['path_pdf'];?>" title="Caminho do PDF do Site" size="60" maxlength="85" class='caixadetexto'>
            <br><b>Exemplo:</b> http://www.grupoalbafer.com.br/arquivo.pdf
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class="botao">
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='permissao' value='<?=$permissao;?>'>
</form>
</body>
</html>
<?}?>