<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM  RESULTADO.</FONT>";
$mensagem[2] = "<font class='confirmacao'>AG NCIA INCLUÕDA COM SUCESSO.</FONT>";
$mensagem[3] = "<font class='erro'>AG NCIA J¡ EXISTE.</FONT>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `bancos` 
                    WHERE `banco` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `banco` ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `bancos` 
                    WHERE `ativo` = '1' ORDER BY `banco` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir AgÍncia(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Incluir AgÍncia(s) - Banco(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Banco
        </td>
        <td>
            P&aacute;gina Web
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'incluir.php?passo=2&id_banco='.$campos[$i]['id_banco'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10' onclick="window.location = '<?=$url;?>'">
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'" align='left'>
            <a href='#' class='link'>
                <?=$campos[$i]['banco'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['pagweb'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
}else if($passo == 2) {
    //Busco o nome do Banco onde o usu·rio o usu·rio passou por par‚metro e deseja incluir a AgÍncia ...
    $sql = "SELECT `banco` 
            FROM `bancos` 
            WHERE `id_banco` = '$_GET[id_banco]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Incluir AgÍncia ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//CÛdigo ...
    if(!texto('form', 'txt_codigo', '1', '1234567890-', 'C”DIGO DA AG NCIA', '2')) {
        return false
    }
//Nome da AgÍncia ...
    if(!texto('form','txt_nome','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ",'NOME DA AG NCIA', '2')) {
        return false
    }
//EndereÁo ...
    if(!texto('form','txt_endereco','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ",'ENDERE«O DA AG NCIA','2')) {
        return false
    }
//Bairro ...
    if(!texto('form','txt_bairro','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'BAIRRO', '2')) {
        return false
    }
//CEP ...
    if(!texto('form', 'txt_cep', '8', '1234567890-', 'CEP', '2')) {
        return false
    }
//Cidade ...
    if(!texto('form','txt_cidade','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'CIDADE', '1')) {
        return false
    }
//Estado ...
    if(!combo('form', 'cmb_estado', 'SELECIONE UM ESTADO !')) {
        return false
    }
//Gerente ...
    if(!texto('form','txt_gerente','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'GERENTE / RESPONS¡VEL', '2')) {
        return false
    }
//Telefone ...
    if(!texto('form', 'txt_telefone', '8', '1234567890-', 'TELEFONE', '2')) {
        return false
    }
//Email ...
    if(document.form.txt_email.value != '') {
        if(!email('form', 'txt_email', '3', 'qwertyuioplkjhgfdsazxcvbnm/-._@', 'E-MAIL', '2')) {
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_codigo.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<!--******************************Controle de Tela******************************-->
<input type='hidden' name='hdd_banco' value='<?=$_GET['id_banco'];?>'>
<!--****************************************************************************-->
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Ag&ecirc;ncia para o Banco => 
            <font color='yellow'>
                <?=$campos[0]['banco'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>C&oacute;digo da Ag&ecirc;ncia: </b>
        </td>
        <td>
            <input type='text' name='txt_codigo' maxlength='20' size='25' title="Digite o CÛdigo da AgÍncia" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome da Ag&ecirc;ncia: </b>
        </td>
        <td>
            <input type='text' name='txt_nome' maxlength='30' size='35' title='Digite o Nome da AgÍncia' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>EndereÁo: </b>
        </td>
        <td>
            <input type='text' name='txt_endereco' maxlength='75' size='50' title="Digite o EndereÁo" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Complemento:
        </td>
        <td>
            <input type='text' name='txt_complemento' maxlength='10' size='15' title='Digite o Complemento' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Bairro:</b>
        </td>
        <td>
            <input type='text' name='txt_bairro' maxlength='15' size='20' title='Digite o Bairro' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cep:</b>
        </td>
        <td>
            <input type='text' name='txt_cep' maxlength='9' size='10' title='Digite o Cep' onkeyup="verifica(this, 'cep', '', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cidade:</b>
        </td>
        <td>
            <input type='text' name='txt_cidade' maxlength='15' size='20' title='Digite a Cidade' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Estado:</b>
        </td>
        <td>
            <select name='cmb_estado' title='Selecione o Estado' class='combo'>
            <? 
                $sql = "SELECT `sigla`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql, 'SP');
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Gerente / Respons·vel:</b>
        </td>
        <td>
            <input type='text' name='txt_gerente' maxlength='35' size='40' title='Digite o Gerente' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Telefone:</b>
        </td>
        <td>
            <input type='text' name='txt_telefone' maxlength='10' size='15' title='Digite o Telefone' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Email:
        </td>
        <td>
            <input type='text' name='txt_email' maxlength='50' size='55' title='Digite o E-Mail' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_codigo.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    //Aqui eu verifico se essa agÍncia j· existe p/ esse Banco ...
    $sql = "SELECT `id_agencia` 
            FROM `agencias` 
            WHERE `cod_agencia` = '$_POST[txt_codigo]' 
            AND `id_banco` = '$_POST[hdd_banco]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "INSERT INTO `agencias` (`id_agencia` , `id_banco` , `cod_agencia` , `nome_agencia` , `endereco` , `complemento` , `bairro` , `cep` , `cidade` , `uf` , `gerente` , `telecom` , `email` , `ativo` ) VALUES (NULL, '$_POST[hdd_banco]', '$_POST[txt_codigo]', '$_POST[txt_nome]', '$_POST[txt_endereco]', '$_POST[txt_complemento]', '$_POST[txt_bairro]', '$_POST[txt_cep]', '$_POST[txt_cidade]', '$_POST[cmb_estado]', '$_POST[txt_gerente]', '$_POST[txt_telefone]', '$_POST[txt_email]', '1')";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir AgÍncia(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir AgÍncia(s) - Consultar Banco(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt_opcao' value='1' title='Consultar Banco' onclick='document.form.txt_consultar.focus()' checked>
            <label for='opt_opcao'>
                Nome do Banco
            </label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' id='todos' value='1' title='Consultar todos os fornecedores' onclick='limpar()' class='checkbox'>
            <label for='todos'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>