<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="atencao">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">EMPRESA DIVISÃO EXCLUÍDA COM SUCESSO.</font>';
$mensagem[3] = '<font class="erro">EMPRESA DIVISÃO NÃO PODE SER EXCLUÍDA, POIS CONSTA EM USO.</font>';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT e.nomefantasia, ed.id_empresa_divisao, ed.razaosocial 
                    FROM `empresas_divisoes` ed 
                    INNER JOIN `empresas` e ON e.id_empresa = ed.id_empresa 
                    WHERE ed.`razaosocial` LIKE '%$txt_consultar%' 
                    AND ed.`ativo` = '1' 
                    ORDER BY e.nomefantasia, ed.razaosocial ";
        break;
        default:
            $sql = "SELECT e.nomefantasia, ed.id_empresa_divisao, ed.razaosocial 
                    FROM `empresas_divisoes` ed 
                    INNER JOIN `empresas` e ON e.id_empresa = ed.id_empresa 
                    WHERE ed.`ativo` = '1' 
                    ORDER BY e.nomefantasia, ed.razaosocial ";
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
<title>.:: Excluir Empresa Divisão ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='70%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr></tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            Excluir Empresa(s) Divisão(ões)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Empresa
        </td>
        <td>
            Divisão
        </td>
        <td>
            <input type="checkbox" name="chkt_tudo" onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox">
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td align="left">
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <input type="checkbox" name="chkt_empresa_divisao[]" value="<?=$campos[$i]['id_empresa_divisao'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
            <input type="submit" name="cmd_excluir" value="Excluir" title="Excluir" class="botao">
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
}else if($passo == 2) {
    foreach ($_POST['chkt_empresa_divisao'] as $id_empresa_divisao) {
//Verifica se a Empresa Divisão esta em uso
        if(cascate::consultar('id_empresa_divisao', 'gpas_vs_emps_divs', $id_empresa_divisao)) {//Em uso
            $valor = 3;
        }else {//Não está em uso, pode apagar a Empresa Divisão
            $sql = "UPDATE `empresas_divisoes` SET `ativo` = '0' WHERE `id_empresa_divisao` = '$id_empresa_divisao' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Empresa(s) Divisão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
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
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1'; ?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Empresa(s) Divisão(ões)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Empresa Divisão por: Divisão" onclick="document.form.txt_consultar.focus()" id="opt1" checked>
            <label for="opt1">Divisão</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='1' title="Consultar todas as Empresas Divisões" onclick='limpar()' id="todos" class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>