<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>GEOMETRIA DE AÇO EXCLUÍDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ALGUNS REGISTROS NÃO PODEM SER EXCLUÍDO(S).</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `geometrias_acos` 
                    WHERE `nome` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY nome ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `geometrias_acos` 
                    WHERE `ativo` = '1' ORDER BY nome ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Geometria do Aço ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
}
</script>
</head>
<body>
<form name="form" method="POST" action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar()">
<table width='60%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Excluir Geometria do Aço(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Nome
        </td>
        <td>
            <label for='todos'>Todos </label>
            <input type="checkbox" name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <input type="checkbox" name="chkt_geometria_aco[]" value="<?=$campos[$i]['id_geometria_aco'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
            <input type="submit" name="cmd_excluir" value="Excluir" title="Excluir" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
	}
}else if($passo == 2) {
    foreach($_POST['chkt_geometria_aco'] as $id_geometria_aco) {
//Verifico se está Geometria do Aço, não está em uso em algum outro lugar do Sistema, antes de excluir ...
        if(cascate::consultar('id_geometria_aco', 'produtos_insumos_vs_acos', $id_geometria_aco)) {
            $valor = 3;//Já está em uso por algum PI ...
        }else {
//Não está em uso, pode ser excluída ...
            $sql = "UPDATE `geometrias_acos` SET `ativo` = '0' WHERE `id_geometria_aco` = '$id_geometria_aco' LIMIT 1 ";
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
<title>.:: Excluir Geometria do Aço ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
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
    <tr class='linhacabecalho'>
        <td colspan="2" align='center'>
            Excluir Geometria do Aço
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Geometria do Aço" onclick="document.form.txt_consultar.focus()" id='label' checked>
            <label for="label">Nome</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='1' title="Consultar todas Geometrias do Aço" onclick='limpar()' id='label2' class="checkbox">
            <label for="label2">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>