<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = '<font class="atencao">SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">CTT ALTERADA COM SUCESSO.</font>';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql= "SELECT * 
                    FROM `ctts` 
                    WHERE `codigo` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `codigo` ";
        break;
        case 2:
            $sql= "SELECT * 
                    FROM `ctts` 
                    WHERE `aplicacao_usual` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `codigo` ";
        break;
        case 3:
            $sql= "SELECT * 
                    FROM `ctts` 
                    WHERE `descricao` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `codigo` ";
        break;
        default:
            $sql= "SELECT * 
                    FROM `ctts` 
                    WHERE `ativo` = '1' ORDER BY `codigo` ";
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
<title>.:: Alterar CTT(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Alterar CTT(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            CÛdigo
        </td>
        <td>
            AplicaÁ„o Usual
        </td>
        <td>
            Dureza Interna
        </td>
        <td>
            DescriÁ„o
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=2&id_ctt=<?=$campos[$i]['id_ctt'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='center'>
            <a href='#' class='link'>
                <?=$campos[$i]['codigo'];?>
            </a>
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
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
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
    $sql = "SELECT * 
            FROM `ctts` 
            WHERE `id_ctt` = '$_GET[id_ctt]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar CTT(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//AplicaÁ„o Usual
    if(!texto('form', 'txt_aplicacao_usual', '3', "-=!@π≤≥£¢¨{} 1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM,.'‹¸·ÈÌÛ˙¡…Õ”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.,%&*$()@#<>™∫∞:;\/", 'APLICA«√O USUAL', '1')) {
        return false
    }
//Dureza Interna
    if(!texto('form', 'txt_dureza_interna', '3', "-=!@π≤≥£¢¨{} 1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«JHGFDSAZXCVBNM,.'‹¸·ÈÌÛ˙¡…Õ”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’.,%&*$()@#<>™∫∞:;\/", 'DUREZA INTERNA', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_aplicacao_usual.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<input type='hidden' name='id_ctt' value='<?=$_GET['id_ctt'];?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar CTT
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CÛdigo:
        </td>
        <td>
            <input type='text' name='txt_codigo' value='<?=$campos[0]['codigo'];?>' title='CÛdigo' maxlength='5' size='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>AplicaÁ„o Usual:</b>
        </td>
        <td>
            <input type='text' name='txt_aplicacao_usual' value='<?=$campos[0]['aplicacao_usual'];?>' title='Digite a AplicaÁ„o Usual' maxlength='50' size='60' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Dureza Interna:</b>
        </td>
        <td>
            <input type='text' name='txt_dureza_interna' value='<?=$campos[0]['dureza_interna'];?>' title='Digite a Dureza Interna' maxlength='30' size='35' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            DescriÁ„o:
        </td>
        <td>
            <textarea name='txt_descricao' title='Digite a DescriÁ„o' cols='85' rows='3' maxlength='255' class='caixadetexto'><?=$campos[0]['descricao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_aplicacao_usual.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
<?
/***************************************************************************************************/
//FaÁo a busca de Todos os PI(s) que utilizam esse CTT ...
$sql = "SELECT g.referencia, pi.id_produto_insumo, pi.discriminacao 
        FROM `grupos` g 
        INNER JOIN `produtos_insumos` pi ON pi.id_grupo = g.id_grupo 
        WHERE pi.`id_ctt` = '$_GET[id_ctt]' ";
$campos_pis = bancos::sql($sql);
$linhas_pis = count($campos_pis);
if($linhas_pis == 0) {//N„o encontrou nenhum PI que est· atrelado a este CTT ...
?>
    <tr align='center'>
        <td>
            <font class='atencao'>N√O H¡ PRODUTO(S) INSUMO(S) ATRELADO(S) A ESTE CTT.</font>
        </td>
    </tr>
<?
}else {//Encontrou mais de Hum PI que est· atrelado a este CTT ...
?>
<table width='70%' border="0" cellspacing="1" cellpadding="1" align='center'>
    <tr>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            RelatÛrio de PI(s) atrelado(s) a este CTT
            <font color='yellow'>
                <?=$campos[0]['codigo'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            ReferÍncia
        </td>
        <td>
            DiscriminaÁ„o
        </td>
    </tr>
<?
    for ($i = 0;  $i < $linhas_pis; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=genericas::buscar_referencia($campos_pis[$i]['id_produto_insumo'], $campos_pis[$i]['referencia']);?>
        </td>
        <td align='left'>
            <?=$campos_pis[$i]['discriminacao'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
<?
/***************************************************************************************************/
}
?>    
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $sql = "UPDATE `ctts` SET `aplicacao_usual` = '$_POST[txt_aplicacao_usual]', `dureza_interna` = '$_POST[txt_dureza_interna]', `descricao` = '$_POST[txt_descricao]' WHERE `id_ctt` = '$_POST[id_ctt]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar CTT(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 3 ;i++) document.form.opt_opcao[i].disabled = false
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
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onSubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar CTT
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar CTTs por: CÛdigo' id='label' checked>
            <label for='label'>
                CÛdigo
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' onclick='document.form.txt_consultar.focus()' title='Consultar CTTs por: AplicaÁ„o Usual' id='label2'>
            <label for='label2'>
                AplicaÁ„o Usual
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='3' onclick='document.form.txt_consultar.focus()' title='Consultar CTTs por: DescriÁ„o' id='label3'>
            <label for='label3'>
                DescriÁ„o
            </label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title='Consultar todas as CTTs' id='label4' class='checkbox'>
            <label for='label4'>
                Todos os registros
            </label>
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