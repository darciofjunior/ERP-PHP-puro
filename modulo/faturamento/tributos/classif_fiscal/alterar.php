<?
require('../../../../lib/segurancas.php');

//Procedimento normal de quando se carrega a Tela ...
$pop_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];
if(empty($pop_up)) require('../../../../lib/menu/menu.php');//Significa que essa Tela "NÃO" foi aberta como sendo Pop-UP ...

segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CLASSIFICAÇÃO FISCAL ALTERADA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>CLASSIFICAÇÃO FISCAL JÁ EXISTENTE.</font>";

if($passo == 1) {
    $chkt_pa_comercializado_pelo_grupo = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['chkt_pa_comercializado_pelo_grupo'] : $_GET['chkt_pa_comercializado_pelo_grupo'];
    if(!empty($chkt_pa_comercializado_pelo_grupo)) $condicao = " AND pa_comercializado_pelo_grupo = 'S' ";

    switch($opt_opcao) {
        case 1:
            $sql = "SELECT cf.*, u.`sigla` 
                    FROM `classific_fiscais` cf 
                    LEFT JOIN `unidades` u ON u.`id_unidade` = cf.`id_unidade` 
                    WHERE cf.`classific_fiscal` LIKE '$txt_consultar%' 
                    AND cf.`ativo` = '1' 
                    $condicao ORDER BY cf.`classific_fiscal` ";
        break;
        default:
            $sql = "SELECT cf.*, u.`sigla` 
                    FROM `classific_fiscais` cf 
                    LEFT JOIN `unidades` u ON u.`id_unidade` = cf.`id_unidade` 
                    WHERE cf.`ativo` = '1' 
                    $condicao ORDER BY cf.`classific_fiscal` ";
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
<title>.:: Alterar Classificação Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Alterar Classificação(ões) Fiscal(is)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Id
        </td>
        <td>
            Classificação <br/>Fiscal
        </td>
        <td>
            Unidade
        </td>
        <td>
            CEST
        </td>
        <td>
            IPI
        </td>
        <td>
            <font title='Imposto de Importação' style='cursor:help'>
                II
            </font>
        </td>
        <td>
            Texto da Nota
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
            $url = "window.location = 'alterar.php?passo=2&id_classific_fiscal=".$campos[$i]['id_classific_fiscal']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <a href="alterar.php?passo=2&id_classific_fiscal=<?=$campos[$i]['id_classific_fiscal'];?>">
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>" align='center'>
            <a href="alterar.php?passo=2&id_classific_fiscal=<?=$campos[$i]['id_classific_fiscal'];?>" class='link'>
                <?=$campos[$i]['id_classific_fiscal'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
            <?=$campos[$i]['cest'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['ipi'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['imposto_importacao'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['reducao_governo'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Os textos da Classificação Fiscal: <b>84.66.93.30 (1)</b> e <b>84.66.93.40 (2)</b> são os únicos utilizados atualmente em Dados Adicionais da Nota Fiscal Eletrônica.
</pre>
</pre>
<?
    }
}elseif($passo == 2) {
//Busco todos os Dados da Classificação Fiscal passada por parâmetro ...	
    $sql = "SELECT * 
            FROM `classific_fiscais` 
            WHERE `id_classific_fiscal` = '$_GET[id_classific_fiscal]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Classificação Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Classificação Fiscal ...
    if(!texto('form', 'txt_class_fiscal', '11', '1234567890.', 'CLASSIFICAÇÃO FISCAL', '1')) {
        return false
    }
//CEST ...
    if(document.form.txt_cest.value != '') {
        if(!texto('form', 'txt_cest', '9', '1234567890.', 'CEST', '1')) {
            return false
        }
    }
//IPI ...
    if(!texto('form', 'txt_ipi', '1', '1234567890,.', 'IPI', '2')) {
        return false
    }
//Imposto de Importação
    if(document.form.txt_imposto_importacao.value != '') {
        if(!texto('form', 'txt_imposto_importacao', '1', '1234567890,.', 'IMPOSTO DE IMPORTAÇÃO', '2')) {
            return false
        }
    }
    return limpeza_moeda('form', 'txt_ipi, txt_imposto_importacao, ')
}
</Script>
<body onload='document.form.txt_class_fiscal.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<!--************Controles de Tela************-->
<input type='hidden' name='id_classific_fiscal' value='<?=$_GET[id_classific_fiscal];?>'>
<input type='hidden' name='pop_up' value='<?=$_GET[pop_up];?>'>
<!--*****************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Classificação Fiscal
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Classificação Fiscal:</b>
        </td>
        <td>
            <input type='text' name='txt_class_fiscal' value='<?=$campos[0]['classific_fiscal']?>' title='Digite a Classificação Fiscal' size='13' maxlength='11' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Unidade:
        </td>
        <td>
            <select name='cmb_unidade' title='Selecione a Unidade' class='combo'>
            <?
                $sql = "SELECT `id_unidade`, `unidade` 
                        FROM `unidades` 
                        WHERE `ativo` = '1' ORDER BY `unidade` ";
                echo combos::combo($sql, $campos[0]['id_unidade']);
            ?>
            </select>
            &nbsp;
            <font color='red'>
                <b>(Hoje este campo só é utilizado como "Unidade Tributável" nas Emissões de Nota Fiscal de Saída p/ Exportação)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CEST:
        </td>
        <td>
            <input type='text' name='txt_cest' value='<?=$campos[0]['cest'];?>' title='Digite a CEST' size='11' maxlength='9' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>IPI:</b>
        </td>
        <td>
            <input type='text' name='txt_ipi' value='<?=number_format($campos[0]['ipi'], 2, ',', '.');?>' title='Digite o IPI' size='15' maxlength='10' onkeyup="verifica(this, 'moeda_especial', 2, '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Imposto de Importação:</td>
        <td>
            <input type='text' name='txt_imposto_importacao' value="<?=number_format($campos[0]['imposto_importacao'], 2, ',', '.');?>" title='Digite o Imposto de Importação' size='15' maxlength='10' onkeyup="verifica(this, 'moeda_especial', 2, '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Texto da Nota:
        </td>
        <td>
            <textarea name='txt_texto_nota' title='Digite o Texto da Nota' maxlength='355' cols='89' rows='4' class='caixadetexto'><?=$campos[0]['reducao_governo'];?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>&nbsp;</td>
        <td>
            <?
                if($campos[0]['pa_comercializado_pelo_grupo'] == 'S') $checked = 'checked';
            ?>
            <input type='checkbox' name='chkt_pa_comercializado_pelo_grupo' value='S' id='pa_comercializado_pelo_grupo' class='checkbox' <?=$checked;?>>
            <label for='pa_comercializado_pelo_grupo'>PA comercializado pelo Grupo</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            if(empty($_GET['pop_up'])) {//Significa que essa Tela foi aberta do Modo Normal ...
        ?>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
        <?
            }
        ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_class_fiscal.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $id_unidade                     = (!empty($_POST['cmb_unidade'])) ? $_POST['cmb_unidade'] : 'NULL';
    $pa_comercializado_pelo_grupo   = (!empty($_POST['chkt_pa_comercializado_pelo_grupo'])) ? 'S' : 'N';
//Verifica se já existe no cadastro a classificação Fiscal digitada pelo Usuário ...
    $sql = "SELECT `id_classific_fiscal` 
            FROM `classific_fiscais` 
            WHERE `classific_fiscal` = '$_POST[txt_class_fiscal]' 
            AND `id_classific_fiscal` <> '$_POST[id_classific_fiscal]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "UPDATE `classific_fiscais` SET `id_unidade` = $id_unidade, `classific_fiscal` = '$_POST[txt_class_fiscal]', `cest` = '$_POST[txt_cest]', `ipi` = '$_POST[txt_ipi]', `imposto_importacao` = '$_POST[txt_imposto_importacao]', `reducao_governo` = '$_POST[txt_texto_nota]', `pa_comercializado_pelo_grupo` = '$pa_comercializado_pelo_grupo' WHERE `id_classific_fiscal` = '$_POST[id_classific_fiscal]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'Javascript'>
        var pop_up = '<?=$_POST['pop_up'];?>'
        if(pop_up == 1) {//Significa que essa Tela foi aberta como sendo Pop-UP ...
            window.location = 'alterar.php?passo=2&id_classific_fiscal=<?=$_POST['id_classific_fiscal'];?>&pop_up=1&valor=<?=$valor;?>'
        }else {//Foi aberta do Modo Normal ...
            window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
        }
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Classificação Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value   = ''
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Classificação Fiscal
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar classificações fiscais por: Classificação Fiscal' id='label1' onclick='iniciar()' checked>
            <label for='label1'>Classificação Fiscal</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='chkt_pa_comercializado_pelo_grupo' value='S' id='pa_comercializado_pelo_grupo' class='checkbox' checked>
            <label for='pa_comercializado_pelo_grupo'>
                <font color='red'>
                    <b>PA(s) comercializado(s) pelo Grupo</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title="Consultar todas as classificações fiscais" onclick='limpar()' id="todos" class="checkbox">
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