<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = '<font class="erro">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';

 if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `representantes` 
                    WHERE `id_representante` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `id_representante` ";
        break;
        case 2:
            $sql = "SELECT * 
                    FROM `representantes` 
                    WHERE `nome_representante` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `id_representante` ";
        break;
        case 3:
            $sql = "SELECT * 
                    FROM `representantes` 
                    WHERE `nome_fantasia` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `id_representante` ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `representantes` 
                    WHERE `ativo` = '1' ORDER BY `id_representante` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'envelopes.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Representante(s) p/ Imprimir Envelope(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Representante(s) p/ Imprimir Envelope(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cód. Rep.
        </td>
        <td>
            Nome do Representante
        </td>
        <td>
            Nome Fantasia
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'envelopes_contatos.php?id_representante='.$campos[$i]['id_representante'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href = '<?=$url;?>' class='html5lightbox'>
                <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center'>
            <a href = '<?=$url;?>' class='html5lightbox'>
                 <?=$campos[$i]['id_representante'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nome_representante'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'envelopes.php'" class='botao'>
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
<title>.:: Consultar Representante(s) p/ Imprimir Envelope(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''

    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = false
        document.form.opt_opcao[1].checked      = true
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Representante(s) p/ Imprimir Envelope(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Representante por: Código do Representante' onclick='document.form.txt_consultar.focus()' id='label1'>
            <label for='label1'>Código do Representante</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar representante por: Nome do Representante' onclick='document.form.txt_consultar.focus()' id='label2' checked>
            <label for='label2'>Nome do Representante</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Consultar Representante por: Nome Fantasia' onclick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>Nome Fantasia</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os representantes' onclick='limpar()' class='checkbox' id='label4'>
            <label for='label4'>Todos os registros</label>
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