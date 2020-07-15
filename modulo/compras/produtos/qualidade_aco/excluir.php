<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>QUALIDADE DE AÇO EXCLUÍDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ALGUNS REGISTROS NÃO PODEM SER EXCLUÍDO(S).</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `qualidades_acos` 
                    WHERE `nome` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY nome ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `qualidades_acos` 
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
<title>.:: Excluir Qualidade de Aço(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name="form" method="POST" action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar()">
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Excluir Qualidade de Aço(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qualidado do Aço
        </td>
        <td>
            Densidade do Material (g/cm³ ou ton/m³)
        </td>
        <td>
            Valor Percentual
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['densidade_material'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor_perc'], 2, ',', '.').' %';?>
        </td>
        <td>
            <input type='checkbox' name='chkt_qualidade_aco[]' value='<?=$campos[$i]['id_qualidade_aco'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
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
    foreach($_POST['chkt_qualidade_aco'] as $id_qualidade_aco) {
//Verifico se está Qualidade do Aço, não está em uso em algum outro lugar do Sistema, antes de excluir ...
        if(cascate::consultar('id_qualidade_aco', 'produtos_insumos_vs_acos', $id_qualidade_aco)) {
            $valor = 3;//Já está em uso por algum PI ...
        }else {//Não está em uso, pode ser excluída ...
            $sql = "UPDATE `qualidades_acos` SET `ativo` = '0' WHERE `id_qualidade_aco` = '$id_qualidade_aco' LIMIT 1 ";
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
<title>.:: Excluir Qualidade de Aço(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
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
<body onLoad='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Qualidade de Aço(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Qualidade Aço por: Qualidade do Aço' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Qualidade do Aço</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todas Qualidades de Aço' onclick='limpar()' id='label2' class='checkbox'>
            <label for='label2'>Todos os registros</label>
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