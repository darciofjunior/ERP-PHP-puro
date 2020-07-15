<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>CLASSIFICAÇÃO FISCAL INCLUIDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CLASSIFICAÇÃO FISCAL JÁ EXISTENTE.</font>";

if(!empty($_POST['txt_class_fiscal'])) {
    $id_unidade                     = (!empty($_POST['cmb_unidade'])) ? $_POST['cmb_unidade'] : 'NULL';
    $pa_comercializado_pelo_grupo   = (!empty($_POST['chkt_pa_comercializado_pelo_grupo'])) ? 'S' : 'N';
//Verifica se já existe no cadastro a classificação Fiscal digitada pelo Usuário ...
    $sql = "SELECT `id_classific_fiscal` 
            FROM `classific_fiscais` 
            WHERE `classific_fiscal` = '$_POST[txt_class_fiscal]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe ...
        $sql = "INSERT INTO `classific_fiscais` (`id_classific_fiscal`, `id_unidade`, `classific_fiscal`, `cest`, `ipi`, `imposto_importacao`, `reducao_governo`, `pa_comercializado_pelo_grupo`, `ativo`) VALUES (NULL, $id_unidade, '$_POST[txt_class_fiscal]', '$_POST[txt_cest]', '$_POST[txt_ipi]', '$_POST[txt_imposto_importacao]', '$_POST[txt_texto_nota]', '$pa_comercializado_pelo_grupo', '1') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Já existente
        $valor = 2;
    }
}
?>
<html>
<title>.:: Incluir Classificação Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Classificação Fiscal ...
    if(!texto('form', 'txt_class_fiscal', '11', '1234567890.', 'CLASSIFICAÇÃO FISCAL', '1')) {
        return false
    }
//CEST ...
    if(document.form.txt_cest.value != '') {
        if(!texto('form', 'txt_cest', '9', '1234567890.', 'CEST', '1')) {
            return false
        }
    }
//IPI ...
    if(!texto('form', 'txt_ipi', '1', '1234567890,.', 'IPI', '2')) {
        return false
    }
//Imposto de Importação ...
    if(document.form.txt_imposto_importacao.value != '') {
        if(!texto('form', 'txt_imposto_importacao', '1', '1234567890,.', 'IMPOSTO DE IMPORTAÇÃO', '2')) {
            return false
        }
    }
    return limpeza_moeda('form', 'txt_ipi, ')
}
</Script>
<body onload='document.form.txt_class_fiscal.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Classificação Fiscal
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Classificação Fiscal:</b>
        </td>
        <td>
            <input type='text' name='txt_class_fiscal' title='Digite a Classificação Fiscal' size='13' maxlength='11' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Unidade:
        </td>
        <td>
            <select name='cmb_unidade' title='Selecione a Unidade' class='combo'>
            <?
                $sql = "SELECT `id_unidade`, `unidade` 
                        FROM `unidades` 
                        WHERE `ativo` = '1' ORDER BY `unidade` ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <font color='red'>
                <b>(Hoje este campo só é utilizado como "Unidade Tributável" nas Emissões de Nota Fiscal de Saída p/ Exportação)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CEST:
        </td>
        <td>
            <input type='text' name='txt_cest' title='Digite a CEST' size='11' maxlength='9' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>IPI:</b>
        </td>
        <td>
            <input type='text' name='txt_ipi' size='15' maxlength='10' title='Digite o IPI' onkeyup="verifica(this, 'moeda_especial', 2, '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Imposto de Importação:
        </td>
        <td>
            <input type='text' name='txt_imposto_importacao' size='15' maxlength='10' title='Digite o Imposto de Importação' onkeyup="verifica(this, 'moeda_especial', 2, '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Texto da Nota:
        </td>
        <td>
            <textarea name='txt_texto_nota' title='Digite o Texto da Nota' maxlength='355' cols='89' rows='4' class='caixadetexto'></textarea>
        </td>
    </tr>       
    <tr class='linhanormal'>
        <td>&nbsp;</td>
        <td>
            <input type='checkbox' name='chkt_pa_comercializado_pelo_grupo' value='S' id='pa_comercializado_pelo_grupo' class='checkbox'>
            <label for='pa_comercializado_pelo_grupo'>PA comercializado pelo Grupo</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_class_fiscal.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>