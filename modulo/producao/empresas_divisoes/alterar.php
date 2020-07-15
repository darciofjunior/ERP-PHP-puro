<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="atencao">SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">EMPRESA DIVIS√O ALTERADA COM SUCESSO.</font>';
$mensagem[3] = '<font class="erro">EMPRESA DIVIS√O J¡ EXISTENTE.</font>';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT e.`nomefantasia`, ed.`id_empresa_divisao`, ed.`razaosocial` 
                    FROM `empresas_divisoes` ed 
                    INNER JOIN `empresas` e ON e.`id_empresa` = ed.`id_empresa` 
                    WHERE ed.`razaosocial` LIKE '%$txt_consultar%' 
                    AND ed.`ativo` = '1' 
                    ORDER BY e.`nomefantasia`, ed.`razaosocial` ";
        break;
        default:
            $sql = "SELECT e.`nomefantasia`, ed.`id_empresa_divisao`, ed.`razaosocial` 
                    FROM `empresas_divisoes` ed 
                    INNER JOIN `empresas` e ON e.`id_empresa` = ed.`id_empresa` 
                    WHERE ed.`ativo` = '1' 
                    ORDER BY e.`nomefantasia`, ed.`razaosocial` ";
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
<title>.:: Alterar Empresa Divis„o ::.</title>
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
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Empresa(s) Divis„o(ıes)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Empresa
        </td>
        <td>
            Divis„o
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=2&id_empresa_divisao=<?=$campos[$i]['id_empresa_divisao'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align="left">
            <a href='alterar.php?passo=2&id_empresa_divisao=<?=$campos[$i]['id_empresa_divisao'];?>' class='link'>
                <?=$campos[$i]['nomefantasia'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
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
}else if($passo == 2) {
    //Aqui eu trago dados da Empresa Divis„o passada por par‚metro ...
    $sql = "SELECT * 
            FROM `empresas_divisoes` 
            WHERE `id_empresa_divisao` = '$_GET[id_empresa_divisao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Empresa Divis„o ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
// Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
        return false
    }
//Divis„o
    if(!texto('form', 'txt_divisao', '3', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,'.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'DIVIS√O', '1')) {
        return false
    }
}
</Script>
<body onload='document.form.cmb_empresa.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='hdd_empresa_divisao' value="<?=$_GET[id_empresa_divisao];?>">
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Empresa Divis„o
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa:
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
            <?
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY `nomefantasia` ";
                echo combos::combo($sql, $campos[0]['id_empresa']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Divis„o:</b>
        </td>
        <td>
            <input type='text' name='txt_divisao' value='<?=$campos[0]['razaosocial'];?>' title='Digite a Divis„o' size='30' maxlength='80' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_empresa.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
<?
}else if($passo == 3) {
    /*Verifico se j· existe alguma Empresa Divis„o cadastrada de acordo com o que foi digitado pelo Usu·rio e diferente da 
    atual que est· sendo alterada ...*/
    $sql = "SELECT `id_empresa_divisao` 
            FROM `empresas_divisoes` 
            WHERE `razaosocial` = '$_POST[txt_divisao]' 
            AND `id_empresa_divisao` <> '$_POST[hdd_empresa_divisao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "UPDATE `empresas_divisoes` SET `id_empresa` ='$_POST[cmb_empresa]', `razaosocial` = '$_POST[txt_divisao]' WHERE `id_empresa_divisao` = '$_POST[hdd_empresa_divisao]' LIMIT 1";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Empresa(s) Divis„o(ıes) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value       = ''
    
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
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Empresa(s) Divis„o(ıes)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Empresa Divis„o por: Divis„o' onclick='document.form.txt_consultar.focus()' id='opt1' checked>
            <label for='opt1'>Divis„o</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todas as Empresas Divisıes' onclick='limpar()' id='todos' class='checkbox'>
            <label for='todos'>Todos os registros</label>
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