<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

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
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Câmbio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar C&acirc;mbio(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
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
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
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
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class="botao">
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
<title>.:: Consultar Câmbio(s) ::.</title>
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
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Câmbio
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
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.txt_data.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>