<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>COMPOSIÇÃO(ÕES) ALTERADA(S) COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT qa.`id_qualidade_aco`, qa.`nome` 
                    FROM `qualidades_acos` qa 
                    INNER JOIN `qualidades_acos_vs_composicoes` qac ON qac.`id_qualidade_aco` = qa.`id_qualidade_aco` 
                    WHERE qa.`nome` LIKE '%$txt_consultar%' 
                    AND qa.`ativo` = '1' ORDER BY qa.`nome` ";
        break;
        default:
            $sql = "SELECT qa.`id_qualidade_aco`, qa.`nome` 
                    FROM `qualidades_acos` qa 
                    INNER JOIN `qualidades_acos_vs_composicoes` qac ON qac.`id_qualidade_aco` = qa.`id_qualidade_aco` 
                    WHERE qa.`ativo` = '1' ORDER BY qa.`nome` ";
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
<title>.:: Alterar Composição(ões) p/ Qualidade de Aço ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Composição(ões) p/ Qualidade de Aço
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Nome
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = "alterar.php?passo=2&id_qualidade_aco=".$campos[$i]['id_qualidade_aco'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
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
    $sql = "SELECT * 
            FROM `qualidades_acos_vs_composicoes` 
            WHERE `id_qualidade_aco` = '$_GET[id_qualidade_aco]' ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Composição(ões) p/ Qualidade de Aço ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos 	= document.form.elements
    var preenchido	= 0
    //Verifico se existe pelo menos 1 item que foi preenchido ...
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') {
            if(elementos[i].value != '') {//Existe preenchido ...
                preenchido = 1
                break
            }
        }
    }
    //Forço o preenchimento de alguma composição química ...
    if(preenchido == 0) {
        alert('DIGITE ALGUMA COMPOSIÇÃO QUÍMICA !')
        elementos[1].focus()
        return false
    }
    //Tratamento com as caixas de texto para poder gravar no BD ...
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') elementos[i].value = elementos[i].value.replace(',', '.')
    }
}
</Script>
</head>
<body onload='document.form.elements[1].focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<!--Controles de Tela-->
<input type='hidden' name='id_qualidade_aco' value='<?=$_GET['id_qualidade_aco'];?>'>
<!--*****************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Composição(ões) p/ Qualidade de Aço 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>QUALIDADE DO AÇO:</b>
            </font>
        </td>
        <td>
            <font color='darkblue' size='2'>
            <?
                //Sql para pegar o nome do aço ...
                $sql = "SELECT `nome`, `valor_perc` 
                        FROM `qualidades_acos` 
                        WHERE `id_qualidade_aco` = '$_GET[id_qualidade_aco]' 
                        AND `ativo` = '1' ORDER BY `nome` LIMIT 1 ";
                $campos_qualidade = bancos::sql($sql);
                echo '<b>'.$campos_qualidade[0]['nome'].' - '.number_format($campos_qualidade[0]['valor_perc'], 2, ',', '.').' %</b>';
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Carbono:
        </td>
        <td>
            De: 
            <input type='text' name='txt_carbono1' value='<?if($campos[0]['carbono1'] != '0.000') echo number_format($campos[0]['carbono1'], 3, ',', '.');?>' title='Carbono 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_carbono2' value='<?if($campos[0]['carbono2'] != '0.000') echo number_format($campos[0]['carbono2'], 3, ',', '.');?>'title='Carbono 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Silício:
        </td>
        <td>
            De: 
            <input type='text' name='txt_silicio1' value='<?if($campos[0]['silicio1'] != '0.000') echo number_format($campos[0]['silicio1'], 3, ',', '.');?>' title='Silício 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_silicio2' value='<?if($campos[0]['silicio2'] != '0.000') echo number_format($campos[0]['silicio2'], 3, ',', '.');?>'title='Silício 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>	
    <tr class='linhanormal'>
        <td>
            Manganês:
        </td>
        <td>
            De: 
            <input type='text' name='txt_manganes1' value='<?if($campos[0]['manganes1'] != '0.000') echo number_format($campos[0]['manganes1'], 3, ',', '.');?>' title='Manganês 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_manganes2' value='<?if($campos[0]['manganes2'] != '0.000') echo number_format($campos[0]['manganes2'], 3, ',', '.');?>' title='Manganês 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fósforo:
        </td>
        <td>
            De: 
            <input type='text' name='txt_fosforo1' value='<?if($campos[0]['fosforo1'] != '0.000') echo number_format($campos[0]['fosforo1'], 3, ',', '.');?>' title='Fósforo 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_fosforo2' value='<?if($campos[0]['fosforo2'] != '0.000') echo number_format($campos[0]['fosforo2'], 3, ',', '.');?>' title='Fósforo 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Enxofre:
        </td>
        <td>
            De: 
            <input type='text' name='txt_enxofre1' value='<?if($campos[0]['enxofre1'] != '0.000') echo number_format($campos[0]['enxofre1'], 3, ',', '.');?>' title='Enxofre 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_enxofre2' value='<?if($campos[0]['enxofre2'] != '0.000') echo number_format($campos[0]['enxofre2'], 3, ',', '.');?>' title='Enxofre 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cromo:
        </td>
        <td>
            De: 
            <input type='text' name='txt_cromo1' value='<?if($campos[0]['cromo1'] != '0.000') echo number_format($campos[0]['cromo1'], 3, ',', '.');?>' title='Cromo 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_cromo2' value='<?if($campos[0]['cromo2'] != '0.000') echo number_format($campos[0]['cromo2'], 3, ',', '.');?>' title='Cromo 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>		
    <tr class='linhanormal'>
        <td>
            Níquel:
        </td>
        <td>
            De: 
            <input type='text' name='txt_niquel1' value='<?if($campos[0]['niquel1'] != '0.000') echo number_format($campos[0]['niquel1'], 3, ',', '.');?>' title='Níquel 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_niquel2' value='<?if($campos[0]['niquel2'] != '0.000') echo number_format($campos[0]['niquel2'], 3, ',', '.');?>' title='Níquel 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>				
    <tr class='linhanormal'>
        <td>
            Molibdênio:
        </td>
        <td>
            De: 
            <input type='text' name='txt_molibdenio1' value='<?if($campos[0]['molibdenio1'] != '0.000') echo number_format($campos[0]['molibdenio1'], 3, ',', '.');?>' title='Molibdênio 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_molibdenio2' value='<?if($campos[0]['molibdenio2'] != '0.000') echo number_format($campos[0]['molibdenio2'], 3, ',', '.');?>' title='Molibdênio 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tungstênio:
        </td>
        <td>
            De: 
            <input type='text' name='txt_tungstenio1' value='<?if($campos[0]['tungstenio1'] != '0.000') echo number_format($campos[0]['tungstenio1'], 3, ',', '.');?>' title='Tungstênio 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_tungstenio2' value='<?if($campos[0]['tungstenio2'] != '0.000') echo number_format($campos[0]['tungstenio2'], 3, ',', '.');?>' title='Tungstênio 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Titânio:
        </td>
        <td>
            De: 
            <input type='text' name='txt_titanio1' value='<?if($campos[0]['titanio1'] != '0.000') echo number_format($campos[0]['titanio1'], 3, ',', '.');?>' title='Titânio 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_titanio2' value='<?if($campos[0]['titanio2'] != '0.000') echo number_format($campos[0]['titanio2'], 3, ',', '.');?>' title='Titânio 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vanádio:
        </td>
        <td>
            De: 
            <input type='text' name='txt_vanadio1' value='<?if($campos[0]['vanadio1'] != '0.000') echo number_format($campos[0]['vanadio1'], 3, ',', '.');?>' title='Vanádio 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_vanadio2' value='<?if($campos[0]['vanadio2'] != '0.000') echo number_format($campos[0]['vanadio2'], 3, ',', '.');?>' title='Vanádio 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cobre:
        </td>
        <td>
            De: 
            <input type='text' name='txt_cobre1' value='<?if($campos[0]['cobre1'] != '0.000') echo number_format($campos[0]['cobre1'], 3, ',', '.');?>' title='Cobre 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_cobre2' value='<?if($campos[0]['cobre2'] != '0.000') echo number_format($campos[0]['cobre2'], 3, ',', '.');?>' title='Cobre 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Alumínio:
        </td>
        <td>
            De: 
            <input type='text' name='txt_aluminio1' value='<?if($campos[0]['aluminio1'] != '0.000') echo number_format($campos[0]['aluminio1'], 3, ',', '.');?>' title='Alumínio 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_aluminio2' value='<?if($campos[0]['aluminio2'] != '0.000') echo number_format($campos[0]['aluminio2'], 3, ',', '.');?>' title='Alumínio 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cobalto:
        </td>
        <td>
            De: 
            <input type='text' name='txt_cobalto1' value='<?if($campos[0]['cobalto1'] != '0.000') echo number_format($campos[0]['cobalto1'], 3, ',', '.');?>' title='Cobalto 1' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
            à 
            <input type='text' name='txt_cobalto2' value='<?if($campos[0]['cobalto2'] != '0.000') echo number_format($campos[0]['cobalto2'], 3, ',', '.');?>' title='Cobalto 2' size='9' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'> %
        </td>
    </tr>																																			
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.elements[1].focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $sql = "UPDATE `qualidades_acos_vs_composicoes` SET `carbono1` = '$_POST[txt_carbono1]', `carbono2` = '$_POST[txt_carbono2]', `silicio1` = '$_POST[txt_silicio1]', `silicio2` = '$_POST[txt_silicio2]', `manganes1` = '$_POST[txt_manganes1]', `manganes2` = '$_POST[txt_manganes2]',
            `fosforo1` = '$_POST[txt_fosforo1]', `fosforo2` = '$_POST[txt_fosforo2]', `enxofre1` = '$_POST[txt_enxofre1]', `enxofre2` = '$_POST[txt_enxofre2]', `cromo1` = '$_POST[txt_cromo1]', `cromo2` = '$_POST[txt_cromo2]', `niquel1` = '$_POST[txt_niquel1]', `niquel2` = '$_POST[txt_niquel2]',
            `molibdenio1` = '$_POST[txt_molibdenio1]', `molibdenio2` = '$_POST[txt_molibdenio2]', `tungstenio1` = '$_POST[txt_tungstenio1]', `tungstenio2` = '$_POST[txt_tungstenio2]', `titanio1` = '$_POST[txt_titanio1]', `titanio2` = '$_POST[txt_titanio2]', `vanadio1` = '$_POST[txt_vanadio1]', `vanadio2` = '$_POST[txt_vanadio2]',
            `cobre1` = '$_POST[txt_cobre1]', `cobre2` = '$_POST[txt_cobre2]', `aluminio1` = '$_POST[txt_aluminio1]', `aluminio2` = '$_POST[txt_aluminio2]', `cobalto1` = '$_POST[txt_cobalto1]', `cobalto2` = '$_POST[txt_cobalto2]' WHERE `id_qualidade_aco` = '$_POST[id_qualidade_aco]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Composição(ões) p/ Qualidade de Aço ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
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
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Composição(ões) p/ Qualidade de Aço
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Qualidade Aço por: Nome' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Nome</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title='Consultar Todas Composição(ões) p/ Qualidade de Aço' onclick='limpar()' id='label2' class='checkbox'>
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