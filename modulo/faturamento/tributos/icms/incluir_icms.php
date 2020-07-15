<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/cascates.php');
segurancas::geral('/erp/albafer/modulo/faturamento/tributos/icms/incluir.php', '../../../../');
$mensagem[1] = "<font class='confirmacao'>ICMS INCLUIDO COM SUCESSO.</font>";

if(!empty($_POST['id_classific_fiscal'])) {
    $data                   = date('Y-m-d H:i:s');
    
    foreach($_POST['cmb_uf'] as $id_uf) {
        $sql = "INSERT INTO `icms` (`id_icms`, `id_classific_fiscal`, `id_uf`, `icms`, `reducao`, `icms_intraestadual`, `iva`) VALUES (NULL, '$_POST[id_classific_fiscal]', '$id_uf', '$_POST[txt_aliq_icms_interestadual]', '$_POST[txt_reducao_base_calculo]', '$_POST[txt_aliq_icms_intraestadual]', '$_POST[txt_iva]') ";
        bancos::sql($sql);
    }
    $valor = 1;
}

if(cascate::incluir('classific_fiscais, ufs')) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../../html/index.php?valor=18'
    </Script>
<?
}
?>
<html>
<title>.:: Incluir ICMS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Unidade Federal
    var i, elementos = document.form.elements
    var selecionados = 0
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) {
                if(document.form.elements[i][j].selected == true) {
                    selecionados ++
                }
            }
        }
    }
    if(selecionados == 0) {
        alert('SELECIONE UMA UNIDADE FEDERAL !')
        document.form.elements[1].focus()
        return false
    }
//Alíq. Icms Interestadual
    if(!texto('form', 'txt_aliq_icms_interestadual', '1', '1234567890,.', 'ALÍQUOTA ICMS INTERESTADUAL', '1')) {
            return false
    }
//Redução de Base de Cálculo
    if(document.form.txt_reducao_base_calculo.value != '') {
        if(!texto('form', 'txt_reducao_base_calculo', '1', '1234567890,.', 'REDUÇÃO DE BASE DE CÁLCULO', '1')) {
            return false
        }
    }
//Alíq. Icms Intraestadual
    if(document.form.txt_aliq_icms_intraestadual.value != '') {
        if(!texto('form', 'txt_aliq_icms_intraestadual', '1', '1234567890,.', 'ALÍQUOTA ICMS INTRAESTADUAL', '1')) {
            return false
        }
    }
//IVA
    if(document.form.txt_iva.value != '') {
        if(!texto('form', 'txt_iva', '1', '1234567890,.', 'IVA', '2')) {
            return false
        }
    }
    return limpeza_moeda('form', 'txt_aliq_icms_interestadual, txt_reducao_base_calculo, txt_aliq_icms_intraestadual, txt_iva, ')
}

</Script>
<body onload="document.form.txt_aliquota_icms.focus()">
<form name="form" method="post" action='' onSubmit="return validar()">
<input type="hidden" name="id_classific_fiscal" value="<?=$_GET['id_classific_fiscal']?>">
<table border="0" width='70%' cellspacing ='1' cellpadding='1' align="center">
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Incluir ICMS
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Classificação Fiscal:</b>
        </td>
        <td>
        <?
            //Busco dados da Classificação Fiscal passado por parâmetro pelo Usuário ...
            $sql = "SELECT classific_fiscal 
                    FROM `classific_fiscais` 
                    WHERE `id_classific_fiscal` = '$_GET[id_classific_fiscal]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            echo $campos[0]['classific_fiscal'];
        ?>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>UF:</b>
        </td>
        <td>
            <select name="cmb_uf[]" title="Selecione a Unidade Federal" size="5" class="combo" multiple>
            <?
                $sql = "SELECT id_uf 
                        FROM `icms` 
                        WHERE `id_classific_fiscal` = '$_GET[id_classific_fiscal]' 
                        AND `ativo` = '1' ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                if($linhas > 0) {
                    for($i = 0; $i < $linhas; $i++) $ufs = $ufs.$campos[$i]['id_uf'].',';
                    $ufs = substr($ufs, 0, strlen($ufs) - 1);
                }else {
                    $ufs = 0;
                }
                //Aqui é buscada todas as Unidades Federais da Classificação que foi passada por parâmetro ...
                $sql = "SELECT id_uf, sigla 
                        FROM `ufs` 
                        WHERE ativo = '1' 
                        AND `id_uf` NOT IN ($ufs) ORDER BY sigla ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhanormal">
        <td><b>Alíq. Icms Interestadual:</b></td>
        <td>
            <input type="text" name="txt_aliq_icms_interestadual" title="Digite a Alíq. Icms Interestadual" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="8" maxlength="5" class="caixadetexto">&nbsp;%
        </td>
    </tr>
    <tr class="linhanormal">
        <td>Redução de Base de Cálculo:</td>
        <td>
            <input type="text" name="txt_reducao_base_calculo" title="Digite a Redução de Base de Cálculo" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="8" maxlength="5" class="caixadetexto">&nbsp;%
        </td>
    </tr>
    <tr class="linhanormal">
        <td>Alíq. Icms Intraestadual:</td>
        <td>
            <input type="text" name="txt_aliq_icms_intraestadual" title="Digite a Alíq. Icms Intraestadual" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="8" maxlength="5" class="caixadetexto">&nbsp;%
        </td>
    </tr>
    <tr class="linhanormal">
        <td>IVA:</td>
        <td>
            <input type="text" name="txt_iva" title="Digite o IVA" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="8" maxlength="5" class="caixadetexto">&nbsp;%
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php<?=$parametro;?>'" class="botao">
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_perc_icms.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>