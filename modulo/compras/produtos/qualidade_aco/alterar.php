<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>QUALIDADE DE A«O INCLUÕDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>QUALIDADE DE A«O J¡ EXISTENTE.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `qualidades_acos` 
                    WHERE `nome` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `nome` ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `qualidades_acos` 
                    WHERE `ativo` = '1' ORDER BY `nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Qualidade de AÁo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar Qualidade de AÁo(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Qualidade do AÁo
        </td>
        <td>
            Densidade do Material (g/cm≥ ou ton/m≥)
        </td>
        <td>
            Valor Percentual
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_qualidade_aco='.$campos[$i]['id_qualidade_aco'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = ';?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10' onclick="window.location = '<?=$url;?>'">
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href='<?=$url;?>' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=number_format($campos[$i]['densidade_material'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor_perc'], 2, ',', '.').' %';?>
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
}else if($passo == 2) {
    //Trago dados da Qualidade do AÁo passado por par‚metro ...
    $sql = "SELECT `nome`, `densidade_material` 
            FROM `qualidades_acos` 
            WHERE `id_qualidade_aco` = '$_GET[id_qualidade_aco]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Qualidade do AÁo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Qualidade do AÁo ...
    if(!texto('form', 'txt_nome', '1', 'qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOP«LKJHGFDSAZXCVBNM<>.,;:/?]}()!@#$%®&*() _-+=ß™∫∞¡…Õ”⁄·ÈÌÛ˙‚ÍÓÙ˚¬ Œ‘€¿‡:;π≤≥£¢¨?/¸‹1234567890,.', 'QUALIDADE DO A«O', '1')) {
        return false
    }
//Densidade do Material ...
    if(!texto('form', 'txt_densidade_material', '1', '1234567890,.', 'DENSIDADE DO MATERIAL (G/CM≥ OU TON/M≥)', '2')) {
        return false
    }
//SeguranÁa se a Densidade do Material estiver preenchida ...
    if(document.form.txt_densidade_material.value != '') {
        var densidade_material = eval(strtofloat(document.form.txt_densidade_material.value))
        if(densidade_material < 7) {
            var resposta = confirm('A DENSIDADE DO MATERIAL EST¡ MENOR DO QUE 7 !!!\n\nDESEJA CONTINUAR ?')
            if(resposta == false) return false
        }
    }
    return limpeza_moeda('form', 'txt_densidade_material, txt_valor_perc, ')
}

function calcular_valor_perc() {
    if(document.form.txt_densidade_material.value != '') {
        var densidade_material              = eval(strtofloat(document.form.txt_densidade_material.value))
        document.form.txt_valor_perc.value  = (densidade_material / 7.86 - 1) * 100
        document.form.txt_valor_perc.value  = arred(document.form.txt_valor_perc.value, 2, 1)
    }else {
        document.form.txt_valor_perc.value  = ''
    }
}
</Script>
<body onload='calcular_valor_perc();document.form.txt_nome.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='id_qualidade_aco' value='<?=$_GET['id_qualidade_aco'];?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Qualidade do AÁo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qualidade do AÁo:</b>
        </td>
        <td>
            <input type='text' name='txt_nome' value='<?=$campos[0]['nome'];?>' title='Digite a Qualidade do AÁo' size='35' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Densidade do Material (g/cm≥ ou ton/m≥):</b>
        </td>
        <td>
            <input type='text' name='txt_densidade_material' value='<?=number_format($campos[0]['densidade_material'], 2, ',', '.');?>' title='Densidade do Material (g/cm≥ ou ton/m≥)' onkeyup="verifica(this, 'moeda', '2', '', event);calcular_valor_perc()" size='20' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Percentual:</b>
        </td>
        <td>
            <input type='text' name='txt_valor_perc' value='<?=number_format($campos[0]['valor_perc'], 2, ',', '.');?>' title='Digite o Valor Percentual' onfocus='document.form.txt_densidade_material.focus()' size='20' maxlength='15' class='textdisabled'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nome.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
    <tr class='atencao'>
        <td colspan='2'>
            <marquee loop='100' scrollamount='5'>
                <font size='2' color='blue'>
                    <b>TOMAR CUIDADO AO CRIAR UM TIPO DE A«O, PORQUE … PRECISO VERIFICAR SE ESTE ESTAR¡ COMPATÕVEL COM A DISCRIMINA«√O, POR FAVOR CONSULTAR EM M”DULO PRODU«√O, RELAT”RIO A«O VS DISCRIMINA«√O !</b>
                </font>
            </marquee>
        </td>
    </tr>
</tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>ObservaÁ„o:</font></b>
<pre>
* Densidade do AÁo serve como base e È 7,85 (g/cm≥ ou ton/m≥)
</pre>
<?
}else if($passo == 3) {
//Verifico se j· existe essa Qualidade de AÁo alÈm da qualidade Atual cadastrada na Base de Dados ...
    $sql = "SELECT `id_qualidade_aco` 
            FROM `qualidades_acos` 
            WHERE `nome` = '$_POST[txt_nome]' 
            AND `id_qualidade_aco` <> '$_POST[id_qualidade_aco]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//N„o existe ...
        $sql = "UPDATE `qualidades_acos` SET `nome` = '$_POST[txt_nome]', `densidade_material` = '$_POST[txt_densidade_material]', `valor_perc` = '$_POST[txt_valor_perc]' WHERE `id_qualidade_aco` = '$_POST[id_qualidade_aco]' LIMIT 1 ";
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
<title>.:: Alterar Qualidade de AÁo(s) ::.</title>
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
<body onLoad='document.form.txt_consultar.focus()'>
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
            Alterar Qualidade de AÁo(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Qualidade AÁo por: Qualidade do AÁo' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Qualidade do AÁo</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todas Qualidades de AÁo' onclick='limpar()' id='label2' class='checkbox'>
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