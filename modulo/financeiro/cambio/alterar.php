<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
require('../../../lib/variaveis/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CÂMBIO ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_data       = $_POST['txt_data'];
        $txt_data_ptax  = $_POST['txt_data_ptax'];
    }else {
        $txt_data       = $_GET['txt_data'];
        $txt_data_ptax  = $_GET['txt_data_ptax'];
    }
//Tratamento com as Datas p/ não furar o SQL ...
    if(!empty($txt_data))       $txt_data = data::datatodate($txt_data, '-');
    if(!empty($txt_data_ptax))  $txt_data_ptax = data::datatodate($txt_data_ptax, '-');

    $sql = "SELECT c.*, f.nome 
            FROM `cambios` c 
            INNER JOIN `logins` l ON l.id_login = c.id_funcionario 
            INNER JOIN `funcionarios` f ON f.id_funcionario = l.id_funcionario 
            WHERE c.`data` LIKE '%$txt_data%' 
            AND c.`data_ptax` LIKE '%$txt_data_ptax%' ORDER BY c.id_cambio DESC ";
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
<title>.:: Alterar Câmbio(s) ::.</title>
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
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Alterar Câmbio(s)
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td colspan='2'>
            Data
        </td>
        <td>
            Valor Dólar Dia UOL
        </td>
        <td>
            Valor Euro Dia UOL
        </td>
        <td>
            Data Ptax
        </td>
        <td>
            Valor Dólar Ptax BCB
        </td>
        <td>
            Valor Euro Ptax BCB
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=2&id_cambio=<?=$campos[$i]['id_cambio'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td width='10'>
            <a href="#">
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor_dolar_dia'], 4, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor_euro_dia'], 4, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_ptax'], '/');?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor_dolar_ptax'], 4, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor_euro_ptax'], 4, ',', '.');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align="center">
        <td colspan='7'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" onclick="window.location = 'alterar.php'" class='botao'>
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
    //Aqui faz a busca de dados do Câmbio passado por parâmetro ...
    $sql = "SELECT * 
            FROM `cambios` 
            WHERE `id_cambio` = '$_GET[id_cambio]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Câmbio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data
    if(!data('form', 'txt_data', '4000', 'CADASTRO')) {
        return false
    }
//Data Ptax
    if(!data('form', 'txt_data_ptax', '4000', 'PTAX')) {
        return false
    }
//Valor Dólar do dia
    if(!texto('form', 'txt_valor_dolar_dia', '1', '0123456789,.', 'VALOR DÓLAR DIA', '2')) {
        return false
    }
//Dólar Ptax
    if(!texto('form', 'txt_valor_dolar_ptax', '1', '0123456789,.', 'VALOR DO DÓLAR PTAX', '2')) {
        return false
    }
//Valor do Euro dia
    if(!texto('form', 'txt_valor_euro_dia', '1', '0123456789,.', 'VALOR EURO DIA!', '2')) {
        return false
    }
//Euro Ptax
    if(!texto('form', 'txt_valor_euro_ptax', '1', '0123456789,.', 'VALOR DO EURO PTAX', '2')) {
        return false
    }
    return limpeza_moeda('form', 'txt_valor_dolar_dia, txt_valor_dolar_ptax, txt_valor_euro_dia, txt_valor_euro_ptax, ')
}

function adicionar_dia() {
    if(document.form.chkt_feriado.checked == true) {
        nova_data('document.form.txt_data_ptax', 'document.form.txt_data_ptax', 1)
    }else {
        document.form.txt_data_ptax.value = document.form.txt_data_antiga.value
    }
}
</Script>
<body onload="document.form.txt_data.focus()" >
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<input type='hidden' name="id_cambio" value="<?=$_GET['id_cambio'];?>">
<table width='60%' align="center" cellspacing ='1' cellpadding='1' border='0'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Câmbio
        </td>
    </tr>
    <tr class="linhanormal">
        <td>Data:</td>
        <td>Data Ptax:
            &nbsp;<input type="checkbox" name="chkt_feriado" class="checkbox" onclick="adicionar_dia()" id="feriado">
            <label for="feriado">Feriado</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type="text" name="txt_data" value="<?=data::datetodata($campos[0]['data'],'/');?>" title="Digite a Data" maxlength="10" size="12" onkeyup="verifica(this, 'data', '', '', event)" class="caixadetexto">
        </td>
        <td>
            <input type="text" name="txt_data_ptax" value="<?=data::datetodata($campos[0]['data_ptax'], '/');?>" title="Digite a Data Ptax" maxlength="10" size="12" onkeyup="verifica(this, 'data', '', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>Valor Dólar dia R$: (UOL Câmbio)</td>
        <td>Valor Dólar Ptax R$: (B. Central)</td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type="text" name="txt_valor_dolar_dia" maxlength="15" size="10" value="<?=number_format($campos[0]['valor_dolar_dia'], 4, ',', '.');?>" title="Digite o Valor Dólar do Dia" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class="caixadetexto">
        </td>
        <td>
            <input type="text" name="txt_valor_dolar_ptax" value="<?=number_format($campos[0]['valor_dolar_ptax'], 4, ',', '.');?>" title="Digite o Valor Dólar Pitax" maxlength="15" size="10" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>Valor Euro Dia R$: (UOL Câmbio)</td>
        <td>Valor Euro Ptax R$: (B. Central)</td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type="text" name="txt_valor_euro_dia" value="<?=number_format($campos[0]['valor_euro_dia'], 4, ',', '.');?>" maxlength="15" size="10" title="Digite o Valor Euro Dia" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class="caixadetexto">
        </td>
        <td>
            <input type="text" name="txt_valor_euro_ptax" value="<?=number_format($campos[0]['valor_euro_ptax'], 4, ',', '.');?>" maxlength="15" size="10" title="Digite o Valor Euro Pitax" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhacabecalho' align="center">
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_data.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
<input type="hidden" name="txt_data_antiga" value="<?=data::datetodata($campos[0]['data_ptax'],'/');?>">
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $data       = data::datatodate($_POST['txt_data'], '-');
    $data_ptax  = data::datatodate($_POST['txt_data_ptax'], '-');

    $dolar_do_custo     = genericas::variavel(7);//Busco o Valor de Dólar do Custo ...
    $euro_do_custo      = genericas::variavel(8);//Busco o Valor de Euro do Custo ...
    $diff_prec_dolar    = genericas::diff_porcentagem($txt_valor_dolar_dia, $dolar_do_custo, 3);
    $diff_prec_euro     = genericas::diff_porcentagem($txt_valor_euro_dia, $euro_do_custo, 3);
    if(!$diff_prec_dolar || !$diff_prec_euro) {// c for true significa que a difereca é maior do que o passada na função diff_porcetagem
        if(!class_exists('comunicacao')) require('../../../lib/comunicacao.php');
        // Email Roberto=> Ao incluir o cambio e c tiverem diferença superior a 2%=>3%(para + ou para -) dos valores respectivos do dólar/euro do custo, mandar email.
        $mensagem_email = 'Os valores cambiais estão com diferenças de mais de 3% das variáveis de “moeda custo”.';
        $destino        = $alterar_cambio;
        $assunto        = 'SCAN - Cambio Desatualizado '.date('d-m-Y H:i:s');
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $mensagem_email);
    }
    $sql = "UPDATE `cambios` SET `valor_dolar_dia` = '$_POST[txt_valor_dolar_dia]', `valor_euro_dia` = '$_POST[txt_valor_euro_dia]', `valor_dolar_ptax` = '$_POST[txt_valor_dolar_ptax]', `valor_euro_ptax` = '$_POST[txt_valor_euro_ptax]', `data` = '$data', `data_ptax` = '$data_ptax' WHERE `id_cambio` = '$_POST[id_cambio]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 2;
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Câmbio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onLoad="document.form.txt_data.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Câmbio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data
        </td>
        <td>
            <input type="text" name="txt_data" title="Digite a Data" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class="caixadetexto">&nbsp;
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="nova_janela('../../../calendario/calendario.php?campo=txt_data&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> Calendário
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data Ptax
        </td>
        <td>
            <input type="text" name="txt_data_ptax" title="Digite a Data Ptax" size="12" maxlength="10" onKeyUp="verifica(this, 'data', '', '', event)" class="caixadetexto">&nbsp;
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="nova_janela('../../../calendario/calendario.php?campo=txt_data_ptax&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> Calendário
        </td>
    </tr>
    <tr class='linhacabecalho' align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.txt_data.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>