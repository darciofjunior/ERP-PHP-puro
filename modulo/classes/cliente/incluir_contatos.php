<?
require('../../../lib/segurancas.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>CONTATO INCLUIDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>CONTATO REATIVADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='atencao'>CONTATO CANCELADO.</font>";
$mensagem[4] = "<font class='erro'>CONTATO J¡ EXISTENTE(S) PARA ESTE CLIENTE.</font>";

//InserÁ„o do Contato para o Cliente
if(!empty($_POST['txt_nome'])) {
    if($_POST['hdd_reativar'] == 1) {//Simplesmente voltando um contato antigo ...
        $sql = "UPDATE `clientes_contatos` set `id_departamento` = '$_POST[cmb_departamento]', `opcao_phone` = '$_POST[chkt_assumir]', `ddi` = '$_POST[txt_ddi]', `ddd` = '$_POST[txt_ddd]', `telefone` = '$_POST[txt_telefone]', `ramal` = '$_POST[txt_ramal]', `email` = '$_POST[txt_email]', `observacao` = '$_POST[txt_observacao]', `ativo` = '1' WHERE `id_cliente` = '$_POST[id_cliente]' AND `nome` = '$_POST[txt_nome]' LIMIT 1 ";
        bancos::sql($sql);
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir_contatos.php?id_cliente=<?=$_POST['id_cliente'];?>&valor=2'
            window.opener.document.form.passo.onclick()
        </Script>
<?
    }else {//Incluindo um novo Contato ...
        $sql = "SELECT `id_cliente_contato`, `ativo` 
                FROM `clientes_contatos` 
                WHERE `id_cliente` = '$_POST[id_cliente]' 
                AND `nome` = '$_POST[txt_nome]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            $sql = "INSERT INTO `clientes_contatos` (`id_cliente_contato`, `id_cliente`, `id_departamento`, `nome`, `opcao_phone`, `ddi`, `ddd`, `telefone`, `ramal`, `email`, `observacao`, `ativo`) VALUES (NULL, '$_POST[id_cliente]', '$_POST[cmb_departamento]', '$_POST[txt_nome]', '$_POST[chkt_assumir]', '$_POST[txt_ddi]', '$_POST[txt_ddd]', '$_POST[txt_telefone]', '$_POST[txt_ramal]', '$_POST[txt_email]', '$_POST[txt_observacao]', '1') ";
            bancos::sql($sql);
            
            $sql = "UPDATE `clientes` SET `data_atualizacao_emails_contatos` = '".date('Y-m-d')."' WHERE `id_cliente` = '$_POST[id_cliente]' LIMIT 1 ";
            bancos::sql($sql);
?>
            <Script Language = 'JavaScript'>
                window.location = 'incluir_contatos.php?id_cliente=<?=$_POST['id_cliente'];?>&valor=1'
            </Script>
<?
        }else {
//Significa q È um contato cadastrado e q ainda est· em atividade
            if($campos[0]['ativo'] == 1) {
?>
                <Script Language = 'JavaScript'>
                    window.location = 'incluir_contatos.php?id_cliente=<?=$_POST['id_cliente'];?>&valor=4'
                </Script>
<?
//Significa q È um contato cadastrado e q j· esteve em atividade
            }else {
?>
                <Script Language = 'JavaScript'>
                    var pergunta = confirm('ESTE CONTATO J¡ FOI CADASTRO !\n\nDESEJA REATIVAR ESTE CONTATO ?')
                    if(pergunta == false) window.location = 'incluir_contatos.php?id_cliente=<?=$_POST['id_cliente'];?>&valor=3'
                </Script>
<?
            }
        }
    }
}//Fim da InserÁ„o ...

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente = $_POST['id_cliente'];
}else {
    $id_cliente = $_GET['id_cliente'];
}
?>
<html>
<head>
<title>.:: Incluir Contato(s) ::.</title>
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
        if(document.form.txt_ramal.value != '') {
            if(!texto('form', 'txt_ramal', '1', '1234567890', 'RAMAL', '2')) {
                return false
            }
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
    }else {
        document.form.txt_ddi.disabled      = false
        document.form.txt_ddd.disabled      = false
        document.form.txt_telefone.disabled = false
        document.form.txt_ramal.disabled    = false
        document.form.txt_ddi.className     = 'caixadetexto'
        document.form.txt_ddd.className     = 'caixadetexto'
        document.form.txt_telefone.className = 'caixadetexto'
        document.form.txt_ramal.className   = 'caixadetexto'
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
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
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
            Incluir Contato(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Depto.:</b>
        </td>
        <td>
            <select name="cmb_departamento" title="Selecione o Departamento" class="combo">
            <?
                $sql = "SELECT `id_departamento`, `departamento` 
                        FROM `departamentos` 
                        WHERE `ativo` = '1' ORDER BY `departamento` ";
                if(empty($cmb_departamento)) {
                    echo combos::combo($sql, 4);
                }else {
                    echo combos::combo($sql, $cmb_departamento);
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type='text' name="txt_nome" value="<?=$_POST['txt_nome'];?>" title="Digite o Nome" size="35" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <?
//Significa q j· submeteu
            if(!empty($_POST['txt_nome'])) {
                if(empty($_POST['chkt_assumir'])) {
                    $checked    = '';
                    $disabled   = '';
                }else {
                    $checked    = 'checked';
                    $disabled   = 'disabled';
                }
//Primeira vez que carrega a tela
            }else {
                $checked    = 'checked';
                $disabled   = 'disabled';
            }
        ?>
        <td colspan="2">
            <input type="checkbox" name="chkt_assumir" value="1" onclick="desabilitar_campos()" id="assumir" class="checkbox" <?=$checked;?>>
            <label for="assumir">Assumir Telefone do Cliente</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DDI:
        </td>
        <td>
            <input type='text' name="txt_ddi" value="<?=$_POST['txt_ddi'];?>" title="Digite o DDI" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="3" class='textdisabled' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DDD:
        </td>
        <td>
            <input type='text' name="txt_ddd" value="<?=$_POST['txt_ddd'];?>" title="Digite o DDD" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='textdisabled' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Telefone:</b>
        </td>
        <td>
            <input type='text' name="txt_telefone" value="<?=$_POST['txt_telefone'];?>" title="Digite o Telefone" size="15" maxlength="13" class='textdisabled' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Ramal:
        </td>
        <td>
            <input type='text' name="txt_ramal" value="<?=$_POST['txt_ramal'];?>" title="Digite o Ramal" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="2" class='textdisabled' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>E-mail:</b>
        </td>
        <td>
            <input type='text' name="txt_email" value="<?=$_POST['txt_email'];?>" title="Digite o Email" size="35" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name="txt_observacao" title="Digite a ObservaÁ„o" rows='2' cols='43' maxlength='85' class='caixadetexto'><?=$_POST['txt_observacao'];?></textarea>
        </td>
    </tr>
<?
//Controles de Tela
//Significa que est· reativando um contato antigo
	if(!empty($_POST[txt_nome])) {
            $reativar = 1;
            $rotulo = 'Reativar';
	}else {
            $reativar = 0;
            $rotulo = 'Salvar';
	}
?>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR')" class='botao'>
            <input type="submit" name="cmd_salvar" value="<?=$rotulo;?>" title="<?=$rotulo;?>" style="color:green" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='hdd_reativar' value='<?=$reativar;?>'>
</form>
</body>
</html>