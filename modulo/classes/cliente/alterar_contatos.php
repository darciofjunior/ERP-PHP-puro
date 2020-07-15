<?
require('../../../lib/segurancas.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>CONTATO ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CONTATO J¡ EXISTENTE PARA ESTE CLIENTE.</font>";

//InserÁ„o do Contato para o Cliente
if(!empty($_POST['txt_nome'])) {
    //Aqui eu verifico se existe algum outro contato com o mesmo nome p/ o mesmo Cliente ...
    $sql = "SELECT `id_cliente_contato` 
            FROM `clientes_contatos` 
            WHERE `id_cliente` = '$_POST[hdd_cliente]' 
            AND `nome` = '$_POST[txt_nome]' 
            AND `ativo` = '1' 
            AND `id_cliente_contato` <> '$_POST[hdd_cliente_contato]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "UPDATE `clientes_contatos` SET `id_departamento` = '$_POST[cmb_departamento]', `nome` = '$_POST[txt_nome]', `opcao_phone` = '$_POST[chkt_assumir]', `ddi` = '$_POST[txt_ddi]', `ddd` = '$_POST[txt_ddd]', `telefone` = '$_POST[txt_telefone]', `ramal` = '$_POST[txt_ramal]', `email` = '$_POST[txt_email]', `observacao` = '$_POST[txt_observacao]' WHERE `id_cliente_contato` = '$_POST[hdd_cliente_contato]' LIMIT 1 ";
        bancos::sql($sql);
        
        $sql = "UPDATE `clientes` SET `data_atualizacao_emails_contatos` = '".date('Y-m-d')."' WHERE `id_cliente` = '$_POST[hdd_cliente]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}//Fim da AtualizaÁ„o

$sql = "SELECT * 
        FROM `clientes_contatos` 
        WHERE `id_cliente_contato` = '$_GET[id_cliente_contato]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Contato(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Departamento
    if(!combo('form', 'cmb_departamento', '', 'SELECIONE UM DEPARTAMENTO !')) {
        return false
    }
//Nome
    if(!texto('form', 'txt_nome', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'NOME', '2')) {
        return false
    }
/*******************************************************************************/
    if(document.form.txt_ddi.disabled == false) {
//DDI Comercial
        if(document.form.txt_ddi.value != '') {
            if(!texto('form', 'txt_ddi', '1', '1234567890', 'DDI', '2')) {
                return false
            }
        }
//DDD Comercial
        if(document.form.txt_ddd.value != '') {
            if(!texto('form', 'txt_ddd', '1', '1234567890', 'DDD', '2')) {
                return false
            }
        }
//Telefone Comercial
        if(!texto('form', 'txt_telefone', '7', '()1234567890-/ ', 'TELEFONE', '2')) {
            return false
        }
//Ramal
        if(!texto('form', 'txt_ramal', '1', '1234567890', 'RAMAL', '2')) {
            return false
        }
    }
/*******************************************************************************/
//E-mail
    if(!new_email('form', 'txt_email')) {
        return false
    }
//SeguranÁa para o vendedor n„o fazer trambicagem, porque nunca podemos ter albafer no endereÁo de e-mail, afinal albafer È e-mail daqui da empresa ...
    if(document.form.txt_email.value.indexOf('albafer') != -1) {
        alert('ENDERE«O DE E-MAIL INV¡LIDO !!!\n\nE-MAIL COM DADOS DAQUI DA EMPRESA !')
        document.form.txt_email.focus()
        document.form.txt_email.select()
        return false
    }
//Aqui È para n„o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

function desabilitar_campos() {
    if(document.form.chkt_assumir.checked == true) {
        document.form.txt_ddi.disabled      = true
        document.form.txt_ddd.disabled      = true
        document.form.txt_telefone.disabled = true
        document.form.txt_ramal.disabled    = true
        document.form.txt_ddi.className     = 'textdisabled'
        document.form.txt_ddd.className     = 'textdisabled'
        document.form.txt_telefone.className = 'textdisabled'
        document.form.txt_ramal.className   = 'textdisabled'
        document.form.txt_ramal.value = ''
    }else {
        document.form.txt_ddi.disabled      = false
        document.form.txt_ddd.disabled      = false
        document.form.txt_telefone.disabled = false
        document.form.txt_ramal.disabled    = false
        document.form.txt_ddi.className     = 'caixadetexto'
        document.form.txt_ddd.className     = 'caixadetexto'
        document.form.txt_telefone.className = 'caixadetexto'
        document.form.txt_ramal.className   = 'caixadetexto'
        document.form.txt_ddi.value         = "<?=$campos[0]['ddi'];?>"
        document.form.txt_ddd.value         = "<?=$campos[0]['ddd'];?>"
        document.form.txt_telefone.value    = "<?=$campos[0]['telefone'];?>"
        document.form.txt_ramal.value       = "<?=$campos[0]['ramal'];?>"
        document.form.txt_ddi.focus()
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP ...
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.location = window.opener.document.location.href
}
</Script>
</head>
<body onload='document.form.txt_nome.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='hdd_cliente_contato' value='<?=$_GET['id_cliente_contato'];?>'>
<input type='hidden' name='hdd_cliente' value='<?=$campos[0]['id_cliente'];?>'>
<input type='hidden' name='nao_atualizar'>
<!--*************************************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Contato(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Depto.:</b>
        </td>
        <td>
            <select name='cmb_departamento' title='Selecione o Departamento' class='combo'>
            <?
                $sql = "SELECT `id_departamento`, `departamento` 
                        FROM `departamentos` 
                        WHERE `ativo` = '1' ORDER BY `departamento` ";
                echo combos::combo($sql, $campos[0]['id_departamento']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type='text' name="txt_nome" value="<?=$campos[0]['nome'];?>" title="Digite o Nome" size="35" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <?
            if(empty($campos[0]['opcao_phone'])) {
                $checked    = '';
                $disabled   = '';
                $class      = 'caixadetexto';
            }else {
                $checked    = 'checked';
                $disabled   = 'disabled';
                $class      = 'textdisabled';
            }
        ?>
        <td colspan='2'>
            <input type="checkbox" name="chkt_assumir" value="1" onclick="desabilitar_campos()" class="checkbox" id="assumir" <?=$checked;?>>
            <label for="assumir">Assumir Telefone do Cliente</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DDI:
        </td>
        <td>
            <input type='text' name="txt_ddi" value="<?=$campos[0]['ddi'];?>" title="Digite o DDI" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size='3' maxlength='3' class="<?=$class;?>" <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DDD:
        </td>
        <td>
            <input type='text' name="txt_ddd" value="<?=$campos[0]['ddd'];?>" title="Digite o DDD" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class="<?=$class;?>" <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Telefone:</b>
        </td>
        <td>
            <input type='text' name="txt_telefone" value="<?=$campos[0]['telefone'];?>" title="Digite o telefone" size="15" maxlength="13" class="<?=$class;?>" <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Ramal:
        </td>
        <td>
            <input type='text' name="txt_ramal" value="<?=$campos[0]['ramal'];?>" title="Digite o Ramal" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class="<?=$class;?>" <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>E-mail:</b>
        </td>
        <td>
            <input type='text' name="txt_email" value="<?=$campos[0]['email'];?>" title="Digite o Email" size="35" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name="txt_observacao" title="Digite a ObservaÁ„o" rows='2' cols='43' maxlength='85' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>