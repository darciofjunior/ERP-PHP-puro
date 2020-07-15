<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');

if(empty($_GET['pop_up'])) {
    require('../../../lib/menu/menu.php');
    segurancas::geral($PHP_SELF, '../../../');
}

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>TRANSPORTADORA ALTERADA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>TRANSPORTADORA J¡ EXISTENTE.</font>";

if($passo == 1) {
//Tratamento com os objetos apÛs ter submetido a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_transportadora = $_POST['txt_transportadora'];
    }else {
        $txt_transportadora = $_GET['txt_transportadora'];
    }
    $sql = "SELECT * 
            FROM `transportadoras` 
            WHERE (`nome` LIKE '%$txt_transportadora%' OR `nome_fantasia` LIKE '%$txt_transportadora%') 
            AND `ativo` = '1' ORDER BY `nome` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script language = 'Javascript'>
            window.location = 'alterar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Transportadoras ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Alterar Transportadora(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Transportadora
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            E-mail
        </td>
        <td>
            EndereÁo
        </td>
        <td>
            Telefone
        </td>
        <td>
            Telefone 2
        </td>
        <td>
            CNPJ
        </td>
        <td>
            Insc. Estadual
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_transportadora='.$campos[$i]['id_transportadora'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href='<?=$url;?>'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['email'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['endereco'])) echo $campos[$i]['endereco'].', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['fone'];?>
        </td>
        <td>
            <?=$campos[$i]['fone2'];?>
        </td>
        <td>
        <?
            $cnpj = ($campos[$i]['cnpj'] == 00000000000000) ? '' : $campos[$i]['cnpj'];
            echo $cnpj;
        ?>
        </td>
        <td>
            <?=$campos[$i]['ie'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}elseif($passo == 2) {
    $sql = "SELECT * 
            FROM `transportadoras` 
            WHERE `id_transportadora` = '$_GET[id_transportadora]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Transportadora(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Transportadora ...
    if(!texto('form', 'txt_transportadora', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'TRANSPORTADORA (RAZ√O SOCIAL)', '1')) {
        return false
    }
//Nome Fantasia ...
    if(document.form.txt_nome_fantasia.value != '') {
        if(!texto('form', 'txt_nome_fantasia', '3', "-1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}&*()™∫∞;\/ ", 'NOME FANTASIA', '2')) {
            return false
        }
    }
//E-mail ...
    if(document.form.txt_email.value != '') {
        if (!new_email('form', 'txt_email')) {
            return false
        }
    }
//Tipo de Transporte ...
    if(!combo('form', 'cmb_tipo_transporte', '', 'SELECIONE O TIPO DE TRANSPORTE !')) {
        return false
    }
//Cep ...
    if(!texto('form', 'txt_cep', '9', '-1234567890', 'CEP', '2')) {
        return false
    }
//N˙mero / Complemento ...
    if(!texto('form', 'txt_num_complemento', '1', "-¢{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,()™∫∞ ", 'N⁄MERO / COMPLEMENTO', '2')) {
        return false
    }
//Telefone 1 ...
    if(!texto('form', 'txt_telefone1', '3', '0123456789', 'TELEFONE 1', '2')) {
        return false
    }
//Telefone 2 ...
    if(document.form.txt_telefone2.value != '') {
        if(!texto('form', 'txt_telefone2', '3', '0123456789', 'TELEFONE 2', '2')) {
            return false
        }
    }
//CNPJ ...
    if(document.form.txt_cnpj.value == '') {
        alert('DIGITE O CNPJ !')
        document.form.txt_cnpj.focus()
        return false
    }
//Controle com o CNPJ ...
    if(document.form.txt_cnpj.value == '0') {
        alert('CNPJ INV¡LIDO !')
        document.form.txt_cnpj.focus()
        document.form.txt_cnpj.select()
        return false
    }

    nro = document.form.txt_cnpj.value
    if(nro.length > 14) {
        for(i = 0; i < nro.length; i++) {
            letra = nro.charAt(i)
            if((letra == '.') || (letra == '/') || (letra == '-')){
                nro = nro.replace(letra,'')
            }
        }
        document.form.txt_cnpj.value = nro
        if (!cnpj('form','txt_cnpj')) {
                return false
        }
    }else {
        if(!cnpj('form','txt_cnpj')) {
            return false
        }
    }
//IE ...
    if(!texto('form', 'txt_ie', '3', '0123456789', 'INSCRI«√O ESTADUAL', '1')) {
        return false
    }
//Desabilito esses campos para poder gravar no BD ...
    document.form.txt_endereco.disabled = false
    document.form.txt_bairro.disabled = false
    document.form.txt_cidade.disabled = false
    document.form.txt_estado.disabled = false
}

function buscar_cep(cep_digitado) {
    iframe_buscar_cep.location = '../../classes/cep/buscar_cep.php?txt_cep='+cep_digitado
}
</Script>
<body onload='document.form.txt_transportadora.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='id_transportadora' value='<?=$_GET['id_transportadora'];?>'>
<table width='80%' cellspacing ='1' cellpadding='1' border='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Transportadora(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='40%'>
            <b>Transportadora (Raz„o Social):</b>
        </td>
        <td width='40%'>
            Nome Fantasia:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_transportadora' value='<?=$campos[0]['nome'];?>' title='Digite a Transportadora' size='40' maxlength='50' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_nome_fantasia' value='<?=$campos[0]['nome_fantasia'];?>' title='Digite o Nome Fantasia' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Transporte:</b>
        </td>
        <td>
            Email:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_transporte' title='Selecione o Tipo de Transporte' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos[0]['tipo_transporte'] == 'A') {
                        $selecteda = 'selected';
                    }else if($campos[0]['tipo_transporte'] == 'R') {
                        $selectedr = 'selected';
                    }else if($campos[0]['tipo_transporte'] == 'RA') {
                        $selectedra = 'selected';
                    }
                ?>
                <option value='A' <?=$selecteda;?>>A…REO</option>
                <option value='R' <?=$selectedr;?>>RODOVI¡RIO</option>
                <option value='RA' <?=$selectedra;?>>RODOVI¡RIO / A…REO</option>
            </select>
        </td>
        <td>
            <input type='text' name='txt_email' value='<?=$campos[0]['email'];?>' title='Digite o E-mail' size='50' maxlength='80' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <font color='#FFFFFF' size='-1'>
                Dados de EndereÁo
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Cep:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_cep' value='<?=$campos[0]['cep'];?>' title='Digite o CEP' size='11' maxlength='9' onkeyup="verifica(this, 'cep', '', '', event)" onblur='buscar_cep(this.value)' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            EndereÁo:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>N.∫ / Complemento:</b>
        </td>
        <td>
            Bairro:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_endereco' value='<?=$campos[0]['endereco'];?>' title='EndereÁo' size='55' maxlength='50' class='textdisabled' disabled>
            &nbsp;
            <input type='text' name='txt_num_complemento' value='<?=$campos[0]['num_complemento'];?>' title='N˙mero, Complemento, ...' size='10' maxlength='50' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_bairro' value='<?=$campos[0]['bairro'];?>' title='Bairro' size='25' maxlength='20' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade:
        </td>
        <td>
            Estado:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_cidade' value='<?=$campos[0]['cidade'];?>' title='Cidade' maxlength='30' size='25' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_estado' value='<?=$campos[0]['uf'];?>' title='Estado' maxlength='2' size='3' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Telefone 1:</b>
        </td>
        <td>
            Telefone 2:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_telefone1' value='<?=$campos[0]['fone'];?>' title='Digite o Telefone 1' size='17' maxlength='15' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_telefone2' value='<?=$campos[0]['fone2'];?>' title='Digite o Telefone 2' size='17' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CNPJ:</b>
        </td>
        <td>
            <b>InscriÁ„o Estadual:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text'  name='txt_cnpj' value='<?=$campos[0]['cnpj'];?>' title='Digite o CNPJ' size='20' maxlength='18' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_ie' value='<?=$campos[0]['ie'];?>' title='Digite a InscriÁ„o Estadual' size='20' maxlength='20' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            if(empty($_GET['pop_up'])) {
        ?>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
                <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_transportadora.focus()" style='color:#ff9900' class='botao'>
                <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }
        ?>   
            &nbsp;
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <iframe name='iframe_buscar_cep' width='0' height='0' style='border-width:0px'></iframe>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
//Verifica no Banco de Dados se existe alguma transportadora com esse CNPJ, as ˙nicas que podem manter os mesmos CNPJS s„o as de Correio ...
    $sql = "SELECT `id_transportadora` 
            FROM `transportadoras` 
            WHERE `cnpj` = '$_POST[txt_cnpj]' 
            AND (`nome` NOT LIKE '%CORREIO%' AND `nome_fantasia` NOT LIKE '%CORREIO%') 
            AND `id_transportadora` <> '$_POST[id_transportadora]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//J· existe outra com o mesmo CNPJ ...
        $valor = 3;
    }else {//N„o existe ...
        $sql = "UPDATE `transportadoras` SET `nome` = '$_POST[txt_transportadora]', `nome_fantasia` = '$_POST[txt_nome_fantasia]', `email` = '$_POST[txt_email]', `tipo_transporte` = '$_POST[cmb_tipo_transporte]', `endereco` = '$_POST[txt_endereco]', `num_complemento` = '$_POST[txt_num_complemento]', `bairro` = '$_POST[txt_bairro]', `cidade` = '$_POST[txt_cidade]', `uf` = '$_POST[txt_estado]', `cep` = '$_POST[txt_cep]', `fone` = '$_POST[txt_telefone1]', `fone2` = '$_POST[txt_telefone2]', `cnpj` = '$_POST[txt_cnpj]', `ie` = '$_POST[txt_ie]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_transportadora` = '$_POST[id_transportadora]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }
?>
    <Script language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Transportadora(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_transportadora.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Transportadora(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Transportadora
        </td>
        <td>
            <input type='text' name='txt_transportadora' title='Digite a Transportadora' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_transportadora.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>