<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='confirmacao'>FAM�LIA INCLUIDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>FAM�LIA J� EXISTENTE.</font>";

//Significa que a familia j� est� cadastro e q o usu�rio at� ent�o est� fazendo atualiza��es (altera��es) dessa fam�lia ...
if(!empty($_POST[txt_familia])) {
    $data = date('Y-m-d H:i:s');
    $sql = "SELECT id_familia 
            FROM `familias` 
            WHERE `nome` = '$_POST[txt_familia]' 
            AND `ativo` = 1 
            LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N�o existe
        $sql = "INSERT INTO `familias` (`id_familia`, `id_classific_fiscal`, `id_login_gerente`, `meta_mensal_vendas`, `nome`, `nome_ing`, `nome_esp`, `observacao`, `ativo`) VALUES (null, '$_POST[cmb_classificacao_fiscal]', '$_POST[cmb_login_gerente]', '$_POST[txt_meta_mensal_vendas]', '$_POST[txt_familia]', '$_POST[txt_familia_ingles]', '$_POST[txt_familia_espanhol]', '$_POST[txt_observacao]', '1') ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}

//Verifica se existe Classific. Fiscal cadastrada
if(cascate::incluir('classific_fiscais') == 1) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../../../html/index.php?valor=18'
    </Script>
<?
}
?>
<html>
<title>.:: Incluir Fam�lia ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Classifica��o Fiscal
    if(!combo('form', 'cmb_classificacao_fiscal', '', 'SELECIONE A CLASSIFICA��O FISCAL !')) {
        return false
    }
//Metal Mensal de Vendas ...
    if(document.form.cmb_login_gerente.value != '') {//Se tiver um Gerente selecionado, for�a preencher a Meta ...
        if(!texto('form', 'txt_meta_mensal_vendas', '4', '0123456789,.', 'META MENSAL DE VENDAS', '1')) {
            return false
        }
    }
//Fam�lia
    if(!texto('form', 'txt_familia', '1', 'abcdefghijk��lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ �������������������������� "1234567890/', 'FAM�LIA', '1')) {
        return false
    }
//Fam�lia em Ingl�s
    if(document.form.txt_familia_ingles.value != '') {
        if(!texto('form', 'txt_familia_ingles', '1', 'abcdefghijk��lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ �������������������������� "1234567890/', 'FAM�LIA EM INGL�S', '1')) {
            return false
        }
    }
//Fam�lia em Espanhol
    if(document.form.txt_familia_espanhol.value != '') {
        if(!texto('form', 'txt_familia_espanhol', '1', 'abcdefghijk��lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ �������������������������� "1234567890/', 'FAM�LIA EM ESPANHOL', '1')) {
            return false
        }
    }
    return limpeza_moeda('form', 'txt_meta_mensal_vendas, ')
}
</Script>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Fam�lia
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Classifica��o Fiscal:</b>
        </td>
        <td>
            <select name="cmb_classificacao_fiscal" title="Selecione uma Classifica��o Fiscal" class="combo">
            <?
                $sql = "SELECT id_classific_fiscal, classific_fiscal 
                        FROM `classific_fiscais` 
                        WHERE `ativo` = '1' ORDER BY classific_fiscal ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Gerente da Linha:
        </td>
        <td>
            <select name="cmb_login_gerente" title="Selecione um Gerente da Linha" class="combo">
            <?
                $sql = "SELECT id_login, login 
                        FROM logins 
                        ORDER BY login ";
                echo combos::combo($sql, $campos[0]['id_login_gerente']);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Meta Mensal de Vendas:
        </td>
        <td>
            <input type='text' name='txt_meta_mensal_vendas' title='Digite a Meta Mensal de Vendas' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="15" maxlength="12" class='caixadetexto'>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Fam�lia:</b>
        </td>
        <td>
            <input type="text" name="txt_familia" title="Digite a Fam�lia" size="40" maxlength="50" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Fam�lia em Ingl�s:
        </td>
        <td>
            <input type="text" name="txt_familia_ingles" title="Digite a Fam�lia em Ingl�s" size="38" maxlength="50" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Fam�lia em Espanhol:
        </td>
        <td>
            <input type="text" name="txt_familia_espanhol" title="Digite a Fam�lia em Espanhol" size="38" maxlength="50" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Observa��o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='50' rows='5' title="Digite a Observa��o" onkeyup="letras()" class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR')" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>