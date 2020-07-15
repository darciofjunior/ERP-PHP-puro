<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT f.*, cf.`classific_fiscal`, l.`login` 
                    FROM `familias` f 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    LEFT JOIN `logins` l ON l.`id_login` = f.`id_login_gerente` 
                    WHERE f.`nome` LIKE '%$txt_consultar%' 
                    AND f.`ativo` = '1' ORDER BY f.`nome` ";
        break;
        default:
            $sql = "SELECT f.*, cf.`classific_fiscal`, l.`login` 
                    FROM `familias` f 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    LEFT JOIN `logins` l ON l.`id_login` = f.`id_login_gerente` 
                    WHERE f.`ativo` = '1' ORDER BY f.`nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 30, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Fam�lia ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Fam�lia
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Fam�lia
        </td>
        <td>
            Classifica��o Fiscal
        </td>
        <td>
            Gerente da Linha
        </td>
        <td>
            Meta Mensal de Vendas
        </td>
        <td>
            Observa��o
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_familia='.$campos[$i]['id_familia'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href = '<?=$url;?>' style='cursor:pointer' class='html5lightbox'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href = '<?=$url;?>' style='cursor:pointer' class='html5lightbox'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <font color='darkblue'>
                <b><?=strtoupper($campos[$i]['login']);?></b>
            </font>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['meta_mensal_vendas'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
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
<title>.:: Consultar Fam�lia(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
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
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Fam�lia(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Fam�lia por: Fam�lia' id='label' checked>
            <label for='label'>Fam�lia</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title='Consultar todos as Fam�lias' class='checkbox' id='label2'>
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