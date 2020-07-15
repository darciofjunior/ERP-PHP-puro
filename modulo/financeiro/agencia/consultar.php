<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'> SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

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
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Agência(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)";>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Agência(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Código da Agência
        </td>
        <td>
            Banco
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href='alterar.php?passo=2&id_agencia=<?=$campos[$i]['id_agencia'];?>&pop_up=1' class='html5lightbox'>
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
        <td colspan='2'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class="botao">
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
}else {
?>
<html>
<head>
<title>.:: Consultar Agência(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value       = ''   
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.className   = 'textdisabled'
        document.form.txt_consultar.disabled    = true
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.disabled    = false
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
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Agência(s)
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
            <input id="opt2" type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar Agências por Código da Agencia">
            <label for="opt2">Código da Agência</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input id="todos" type='checkbox' name='opcao' onclick='limpar()'  value='3' title="Consultar todos as agências" class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
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