<?
require('../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM  RESULTADO.</FONT>";
$mensagem[2] = "<font class='confirmacao'>AG NCIA ALTERADA  COM SUCESSO.</FONT>";
$mensagem[3] = "<font class='erro'>AG NCIA J¡ EXISTE.</FONT>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT a.id_agencia, a.cod_agencia, b.banco 
                    FROM `agencias` a 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco AND b.`banco` LIKE '$txt_consultar%' 
                    WHERE a.`ativo` = '1' ORDER BY b.banco ";
        break;
        case 2:
            $sql = "SELECT a.id_agencia, a.cod_agencia, b.banco 
                    FROM `agencias` a 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE a.`cod_agencia` LIKE '$txt_consultar%' 
                    AND a.`ativo` = '1' ORDER BY b.banco ";
        break;
        default:
            $sql = "SELECT a.id_agencia, a.cod_agencia, b.banco 
                    FROM `agencias` a 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE a.`ativo` = '1' ORDER BY b.banco ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script language = 'JavaScript'>
            window.location = 'alterar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar AgÍncia(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language='JavaScript' Src='../../../js/sessao.js'></Script>
<Script Language='JavaScript' Src='../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border=0 align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Ag&ecirc;ncia(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            CÛdigo da AgÍncia
        </td>
        <td>
            Banco
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = "window.location = 'alterar.php?passo=2&id_agencia=".$campos[$i]['id_agencia']."'";
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>">
            <a href="#" class='link'>            
                <?=$campos[$i]['cod_agencia'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'alterar.php'" class="botao">
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
    //Busco dados da AgÍncia passada por par‚metro ...
    $sql = "SELECT * 
            FROM `agencias` 
            WHERE `id_agencia` = '$_GET[id_agencia]' LIMIT 1 ";
    $campos = bancos::sql($sql);

	$id_banco = $campos[0]["id_banco"];
?>
<html>
<head>
<title>.:: Alterar AgÍncia(s) ::.</title>
<meta http-equiv='content-type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../css/layout.css' type=text/css rel=stylesheet>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//CÛdigo
    if(!texto('form', 'txt_codigo', '1', '1234567890-', 'C”DIGO DA AG NCIA', '2')) {
        return false
    }
//Nome da AgÍncia
    if(!texto('form','txt_nome','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ",'NOME DA AG NCIA', '2')) {
        return false
    }
//EndereÁo
    if(!texto('form','txt_endereco','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ",'ENDERE«O DA AG NCIA','2')) {
        return false
    }
//Bairro
    if(!texto('form','txt_bairro','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'BAIRRO', '2')) {
        return false
    }
//CEP
    if(!texto('form', 'txt_cep', '8', '1234567890-', 'CEP', '2')) {
        return false
    }
//Cidade
    if(!texto('form','txt_cidade','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'CIDADE', '1')) {
        return false
    }
//Estado
    if(!combo('form', 'cmb_estado', 'SELECIONE UM ESTADO !')) {
        return false
    }
// Gerente
    if(!texto('form','txt_gerente','3',"-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'GERENTE / RESPONS¡VEL', '2')) {
        return false
    }
//Telefone
    if(!texto('form', 'txt_telefone', '8', '1234567890-', 'TELEFONE', '2')) {
        return false
    }
//EMail
    if(document.form.txt_email.value != '') {
        if(!email('form', 'txt_email', '3', 'qwertyuioplkjhgfdsazxcvbnm/-._@', 'E-MAIL', '2')) {
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_codigo.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit="return validar()">
<!--******************************Controle de Tela******************************-->
<input type='hidden' name='hdd_agencia' value="<?=$_GET[id_agencia];?>">
<input type='hidden' name='hdd_banco' value="<?=$campos[0]['id_banco'];?>">
<!--****************************************************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>  
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            Alterar AgÍncia(s)
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>C&oacute;digo da Ag&ecirc;ncia: </b>
        </td>
        <td>
            <input type="text" name="txt_codigo" value='<?=$campos[0]['cod_agencia'];?>' maxlength='20' size='25' title="Digite o CÛdigo da AgÍncia" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Nome da Ag&ecirc;ncia: </b>
        </td>
        <td>
            <input type='text' name='txt_nome' value='<?=$campos[0]['nome_agencia'];?>' maxlength='30' size='35' title='Digite o Nome da AgÍncia' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>EndereÁo: </b>
        </td>
        <td>
            <input type="text" name="txt_endereco" value='<?=$campos[0]['endereco'];?>' maxlength='75' size='50' title="Digite o EndereÁo" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Complemento:
        </td>
        <td>
            <input type='text' name='txt_complemento' value='<?=$campos[0]['complemento'];?>' maxlength='10' size='15' title='Digite o Complemento' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Bairro:</b>
        </td>
        <td>
            <input type="text" name="txt_bairro" value='<?=$campos[0]['bairro'];?>' maxlength='15' size="20" title="Digite o Bairro" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Cep:</b>
        </td>
        <td>
            <input type='text' name='txt_cep' value='<?=$campos[0]['cep'];?>' maxlength='9' size='10' title='Digite o Cep' onkeyup="verifica(this, 'cep', '', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Cidade:</b>
        </td>
        <td>
            <input type="text" name="txt_cidade" value='<?=$campos[0]['cidade'];?>' maxlength='15' size='20' title="Digite a Cidade" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Estado:</b>
        </td>
        <td>
            <select name="cmb_estado" title="Selecione o Estado" class="combo">
            <? 
                $sql = "SELECT sigla, sigla 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY sigla ";
                echo combos::combo($sql, $campos[0]['uf']);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Gerente / Respons·vel:</b>
        </td>
        <td>
            <input type="text" name="txt_gerente" value='<?=$campos[0]['gerente'];?>' maxlength='35' size='40' title="Digite o Gerente" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Telefone:</b>
        </td>
        <td>
            <input type="text" name="txt_telefone" value='<?=$campos[0]['telecom'];?>' maxlength='10' size='15' title="Digite o Telefone" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Email:
        </td>
        <td>
            <input type="text" name="txt_email" value='<?=$campos[0]['email'];?>' maxlength='50' size='55' title="Digite o E-Mail" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <?
                if(empty($_GET['pop_up'])) {
            ?>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'alterar.php<?=$parametro;?>'" class="botao">
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form','REDEFINIR');document.form.txt_codigo.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
            <?
                }
            ?>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    //Verifico se j· existe uma AgÍncia com esse cÛdigo para o mesmo Banco ...
    $sql = "SELECT id_agencia 
            FROM `agencias` 
            WHERE `cod_agencia` = '$_POST[txt_codigo]' 
            AND `ativo` = '1' 
            AND `id_banco` = '$_POST[hdd_banco]' 
            AND `id_agencia` <> '$_POST[hdd_agencia]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Atualizando dados da AgÍncia ...
        $sql = "UPDATE `agencias` SET `cod_agencia` = '$_POST[txt_codigo]', `nome_agencia` ='$_POST[txt_nome]', `endereco` = '$_POST[txt_endereco]', `complemento` = '$_POST[txt_complemento]', `bairro` = '$_POST[txt_bairro]', `cep` = '$_POST[txt_cep]', `cidade` = '$_POST[txt_cidade]', `uf` = '$_POST[cmb_estado]', `gerente` = '$_POST[txt_gerente]', `telecom` = '$_POST[txt_telefone]', `email` = '$_POST[txt_email]' WHERE `id_agencia` = '$_POST[hdd_agencia]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar AgÍncia(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
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
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar AgÍncia(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input id="opt1" type="radio" name="opt_opcao" value="1" checked onclick="document.form.txt_consultar.focus()" title="Consultar Banco por Nome do Banco">
            <label for="opt1">Nome do Banco</label>
        </td>
        <td width="20%">
            <input id="opt2" type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar AgÍncias por CÛdigo da Agencia">
            <label for="opt2">CÛdigo da AgÍncia</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input id="todos" type='checkbox' name='opcao' onclick='limpar()'  value='3' title="Consultar todos as agÍncias" class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>