<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>NÃO HÁ ICMS CADASTRADO PARA ESSA CLASSIFICAÇÃO FISCAL.</font>";
$mensagem[3] = "<font class='confirmacao'>ICMS EXCLUIDO COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `classific_fiscais` 
                    WHERE `classific_fiscal` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `classific_fiscal` ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `classific_fiscais` 
                    WHERE `ativo` = '1' ORDER BY `classific_fiscal` ";
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
<title>.:: Excluir ICMS ::.</title>
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
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Excluir ICMS
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Id
        </td>
        <td>
            Classificação Fiscal
        </td>
        <td>
            IPI
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url = "excluir.php?passo=2&id_classific_fiscal=".$campos[$i]['id_classific_fiscal'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <a href="<?=$url;?>" title='Excluir ICMS' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" title='Excluir ICMS' class='link'>
                <?=$campos[$i]['id_classific_fiscal'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['ipi'], 2, ',', '.').' %';?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
            <input type='submit' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
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
//Busca dados da Classificação Fiscal Corrente ...
    $sql = "SELECT `classific_fiscal`, `reducao_governo` 
            FROM `classific_fiscais` 
            WHERE `id_classific_fiscal` = '$_GET[id_classific_fiscal]' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $classific_fiscal       = $campos[0]['classific_fiscal'];
    $texto_reducao_governo  = $campos[0]['reducao_governo'];

    //Busca todos os Estados que estão atreladas p/ está classificação corrente ...
    $sql = "SELECT i.*, u.`sigla` 
            FROM `icms` i 
            INNER JOIN `ufs` u ON u.`id_uf` = i.`id_uf` 
            WHERE i.`id_classific_fiscal` = '$_GET[id_classific_fiscal]' 
            AND i.`ativo` = '1' ORDER BY u.`sigla` ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'excluir.php<?=$parametro;?>&passo=1&valor=2'
        </Script>
<?
        exit;
    }
?>
<html>
<title>.:: Excluir ICMS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Excluir ICMS - 
            <font color='yellow'>
                ID: 
            </font>
            <?=$_GET['id_classific_fiscal'];?>
            - 
            <font color='yellow'>
                Classificação Fiscal:
            </font>
            <?=$classific_fiscal;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            UF
        </td>
        <td>
            <font style='cursor:help' title='Alíq. Icms Interestadual'>
                Alíq. Icms Inter
            </font>
        </td>
        <td>
            <font style='cursor:help' title='Redução de Base de Cálculo'>
                Red. B. C.
            </font>
        </td>
        <td>
            <font style='cursor:help' title='Alíq. Icms Intraestadual'>
                Alíq. Icms Intra
            </font>
        </td>
        <td>
            IVA
        </td>
        <td>
            <font style='cursor:help' title='Texto de Redução do Governo'>
                Texto Red. Gov.
            </font>
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['icms'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['reducao'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['icms_intraestadual'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['iva'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=str_replace('?', number_format($campos[$i]['reducao'], 2, ',', '.'), $texto_reducao_governo);?>
        </td>
        <td>
            <input type='checkbox' name='chkt_icms[]' value='<?=$campos[$i]['id_icms'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'excluir.php<?=$parametro;?>&passo=1'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
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
}else if($passo == 3) {
    foreach($_POST['chkt_icms'] as $id_icms) {
        $sql = "UPDATE `icms` SET  ativo = '0' WHERE `id_icms` = '$id_icms' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php?valor=3'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir ICMS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
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
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir ICMS
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt1' value='1' title='Consultar Classificações Fiscais por: Classificação Fiscal' onclick='document.form.txt_consultar.focus()' checked>
            <label for='opt1'>Classificação Fiscal</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' id='todos' value='1' title='Consultar todas as Classificações Fiscais' onclick='limpar()' class='checkbox'>
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