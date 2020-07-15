<?
require('../../../lib/segurancas.php');
require('../../../lib/cascates.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>UNIDADE INSUMO EXCLUIDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>UNIDADE(S) NÃO PODE(M) SER APAGADA(S) POR SER USADA(S) EM OUTRO CADASTRO.</font>";

if($passo == 1) {
	switch($opt_opcao) {
		case 1:
                    $sql = "SELECT * 
                            FROM `unidades` 
                            WHERE `unidade` LIKE '$txt_consultar%' 
                            AND `ativo` = '1' ORDER BY unidade ";
		break;
		case 2:
                    $sql = "SELECT * 
                            FROM `unidades` 
                            WHERE `sigla` LIKE '$txt_consultar%' 
                            AND `ativo` = '1' ORDER BY unidade ";
		break;
		default:
                    $sql = "SELECT * 
                            FROM `unidades` 
                            WHERE `ativo` = '1' ORDER BY unidade ";
		break;
	}
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Excluir Unidade Insumo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='70%' border="0" align='center' cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)">
    <tr align="center">
        <td colspan='4'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            Excluir Unidade(s) Insumo(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Unidade Insumo
        </td>
        <td>
            Sigla
        </td>
        <td>
            Descrição
        </td>
        <td>
            <label for='todos'>Todos </label>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
    </tr>
<?
		for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align="left">
            <?=$campos[$i]['unidade'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['descricao'];?>
        </td>
        <td>
            <input type="checkbox" name="chkt_unidade[]" value="<?=$campos[$i]['id_unidade'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
		}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}elseif($passo == 2) {
    foreach($_POST['chkt_unidade'] as $id_unidade) {
//Verifico se está Unidade, não está em uso em algum outro lugar do Sistema, antes de excluir ...
        if(cascate::consultar('id_unidade', 'produtos_insumos, unidades', $id_unidade)) {
            $valor = 3;//Já está em uso por algum PI ...
        }else {
//Não está em uso, pode ser excluída ...
            $sql = "UPDATE `unidades` SET `ativo` = '0' WHERE `id_unidade` = '$id_unidade' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php<?=$parametro?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Unidade Insumo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled = true
        document.form.txt_consultar.value = ''
    }else {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = false
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
        <td colspan="2">
            Excluir Unidade Insumo
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" checked onclick="document.form.txt_consultar.focus()" title="Consultar unidade_insumo por: Unidade" id='label'>
            <label for="label">Unidade</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar unidade_insumo por: Sigla" id='label2'>
            <label for="label2">Sigla</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todas as Unidades" class="checkbox" id='label3'>
            <label for="label3">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>