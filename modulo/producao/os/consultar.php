<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $opt_opcao      = $_POST['opt_opcao'];
        $txt_consultar  = $_POST['txt_consultar'];
    }else {
        $opt_opcao      = $_GET['opt_opcao'];
        $txt_consultar  = $_GET['txt_consultar'];
    }
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT oss.*, f.`razaosocial` 
                    FROM `oss` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
                    WHERE oss.`id_os` LIKE '%$txt_consultar%' 
                    AND oss.`ativo` = '1' ORDER BY oss.`id_os` DESC ";
        break;
        case 2:
            $sql = "SELECT oss.*, f.`razaosocial` 
                    FROM `oss` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` AND f.`razaosocial` LIKE '%$txt_consultar%' 
                    WHERE oss.`ativo` = '1' ORDER BY oss.`id_os` DESC ";
        break;
        default:
            $sql = "SELECT oss.*, f.`razaosocial` 
                    FROM `oss` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
                    WHERE oss.`ativo` = '1' ORDER BY oss.`id_os` DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);

    if($linhas  == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar OS(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar OS(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º OS
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Data de Saída
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $url = 'itens/itens.php?id_os='.$campos[$i]['id_os'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['id_os'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            if($campos[$i]['data_saida'] != '0000-00-00') echo data::datetodata($campos[$i]['data_saida'], '/');
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
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
<title>.:: Consultar OS(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.txt_consultar.value = ''
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
            Consultar OS
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar OSs por: Número da OS' id='label'>
            <label for='label'>
                Número da OS
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar OSs por: Fornecedor' onclick='document.form.txt_consultar.focus()' id='label2' checked>
            <label for='label2'>
                Fornecedor
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todas as OSs' onclick='limpar()' id='label3' class='checkbox'>
            <label for='label3'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar();document.form.txt_consultar.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>