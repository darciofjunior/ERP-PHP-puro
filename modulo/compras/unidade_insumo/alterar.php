<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>UNIDADE INSUMO ALTERADA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>UNIDADE INSUMO J¡ EXISTENTE.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `unidades` 
                    WHERE `unidade` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `unidade` ";
        break;
        case 2:
            $sql = "SELECT * 
                    FROM `unidades` 
                    WHERE `sigla` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `unidade` ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `unidades` 
                    WHERE `ativo` = '1' ORDER BY `unidade` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'alterar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Unidade(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar Unidade(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Unidade Insumo
        </td>
        <td>
            Sigla
        </td>
        <td>
            DescriÁ„o
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_unidade='.$campos[$i]['id_unidade'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url?>'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href='<?=$url?>'>
                <?=$campos[$i]['unidade'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['descricao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
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
            FROM `unidades` 
            WHERE `id_unidade` = '$_GET[id_unidade]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Unidade(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Unidade ...
    if(!texto('form', 'txt_unidade', '1', "'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ 1234567890", 'UNIDADE', '1')) {
        return false
    }
//Sigla ...
    if(!texto('form', 'txt_sigla', '1', "'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ 1234567890", 'SIGLA', '1')) {
        return false
    }
//DescriÁ„o ...
    if(document.form.txt_descricao.value != '') {
        if(!texto('form', 'txt_descricao', '1', "!'#$&*(Á«){[]}/|-_+abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿ 1234567890", 'DESCRICAO', '1')) {
            return false
        }
    }
}
</Script>
<body onload='document.form.txt_unidade.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='hdd_unidade' value='<?=$_GET['id_unidade'];?>'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Unidade Insumo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Unidade:</b>
        </td>
        <td>
            <input type='text' name='txt_unidade' value='<?=$campos[0]['unidade'];?>' title='Digite a Unidade' size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Sigla:</b>
        </td>
        <td>
            <input type='text' name='txt_sigla' value='<?=$campos[0]['sigla'];?>' title='Digite a Sigla' size='6' maxlength='5' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DescriÁ„o:
        </td>
        <td>
            <input type='text' name='txt_descricao' value='<?=$campos[0]['descricao'];?>' title='Digite a DescriÁ„o' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_unidade.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
//Verifico se j· existe essa Unidade alÈm da unidade Atual cadastrada na Base de Dados ...
    $sql = "SELECT `id_unidade` 
            FROM `unidades` 
            WHERE `unidade` = '$_POST[txt_unidade]' 
            AND `id_unidade` <> '$_POST[hdd_unidade]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe ...
        $sql = "UPDATE `unidades` SET `unidade` = '$_POST[txt_unidade]', `sigla` = '$_POST[txt_sigla]', `descricao` = '$_POST[txt_descricao]' WHERE `id_unidade` = '$_POST[hdd_unidade]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }else {//J· existe ...
        $valor = 3;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Unidade(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = false
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
            Alterar Unidade(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar unidade_insumo por: Unidade' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Unidade</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar unidade_insumo por: Sigla' onclick='document.form.txt_consultar.focus()' id='label2'>
            <label for='label2'>Sigla</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todas as Unidades' onclick='limpar()' id='label3' class='checkbox'>
            <label for='label3'>Todos os registros</label>
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