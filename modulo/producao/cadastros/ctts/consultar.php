<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/data.php');
session_start('funcionarios');
segurancas::geral($PHP_SELF, '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
	switch($opt_opcao) {
		case 1:
			$sql = "SELECT * 
                                FROM `ctts` 
                                WHERE `codigo` LIKE '%$txt_consultar%' 
                                AND `ativo` = '1' ORDER BY codigo ";
		break;
		case 2:
			$sql = "SELECT * 
                                FROM `ctts` 
                                WHERE `aplicacao_usual` LIKE '%$txt_consultar%' 
                                AND `ativo` = '1' ORDER BY codigo ";
		break;
		case 3:
			$sql = "SELECT * 
                                FROM `ctts` 
                                WHERE `descricao` LIKE '%$txt_consultar%' 
                                AND `ativo` = '1' ORDER BY codigo ";
		break;
		default:
			$sql = "SELECT * 
                                FROM `ctts` 
                                WHERE `ativo` = '1' ORDER BY codigo ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar.php?valor=1'
        </script>
<?
	}else {
?>
<html>
<head>
<title>.:: Consultar CTT(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border="0" cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)" align='center'>
	<tr></tr>
	<tr class="linhacabecalho" align="center">
            <td colspan='4'>
                Consultar CTT(s)
            </td>
	</tr>
	<tr class="linhadestaque" align="center">
            <td>
                C�digo
            </td>
            <td>
                Aplica��o Usual
            </td>
            <td>
                Dureza Interna
            </td>
            <td>
                Descri��o
            </td>
	</tr>
<?
		for ($i = 0;  $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
            <td align='center'>
                <?=$campos[$i]['codigo'];?>
            </td>
            <td>
                <?=$campos[$i]['aplicacao_usual'];?>
            </td>
            <td>
                <?=$campos[$i]['dureza_interna'];?>
            </td>
            <td>
                <?=$campos[$i]['descricao'];?>
            </td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
            <td colspan='4'>
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
<title>.:: Consultar CTT(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i++) {
            document.form.opt_opcao[i].disabled = true
        }
        document.form.txt_consultar.disabled = true
        document.form.txt_consultar.value = ''
    }else {
        for(i = 0; i < 3 ;i++) {
            document.form.opt_opcao[i].disabled = false
        }
        document.form.txt_consultar.disabled = false
        document.form.txt_consultar.value = ''
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
                Consultar CTT
            </td>
	</tr>
	<tr class='linhanormal' align='center'>
		<td colspan='2'>
                    Consultar <input type="text" name="txt_consultar" size="45" maxlength='45' class="caixadetexto">
		</td>
	</tr>
	<tr class='linhanormal'>
            <td width="20%">
                <input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar CTTs por: C�digo" id='label' checked>
                <label for='label'>
                    C�digo
                </label>
            </td>
            <td width="20%">
                <input type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar CTTs por: C�digo" id='label2'>
                <label for='label2'>
                    Aplica��o Usual
                </label>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td width="20%">
                <input type="radio" name="opt_opcao" value="3" onclick="document.form.txt_consultar.focus()" title="Consultar CTTs por: Descri��o" id='label3'>
                <label for='label3'>
                    Descri��o
                </label>
            </td>
            <td width="20%">
                <input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todas as CTTs" id='label4' class="checkbox">
                <label for='label4'>
                    Todos os registros
                </label>
            </td>
	</tr>
	<tr class="linhacabecalho" align="center">
            <td colspan="2">
                <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
                <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
            </td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>