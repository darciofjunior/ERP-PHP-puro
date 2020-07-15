<? 
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
session_start('funcionarios');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Tratamento com as Datas ...
    if(!empty($txt_data_inicial) && ($_SERVER['REQUEST_METHOD'] == 'POST')) {
        $txt_data_inicial = data::datatodate($txt_data_inicial, '-');
        $txt_data_final = data::datatodate($txt_data_final, '-');
    }
//Redireciona os parâmetros de acordo com o Option selecionado ...
    switch($_POST['opt_opcao']) {
        case 1://Fornecedor ...
            header("Location: detalhes_por_fornecedor.php?passo=1&txt_consultar=".$txt_consultar."&cmb_empresa=".$_POST['cmb_empresa']."&txt_data_inicial=".$txt_data_inicial."&txt_data_final=".$txt_data_final."&opt_data=".$opt_data);
        break;
        case 2://Referência ...
        case 3://Discriminação ...
/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
como caracter ...*/
            $txt_consultar = str_replace('%', '|', $txt_consultar);
            header("Location: detalhes_por_ref_disc.php?txt_consultar=".$txt_consultar."&cmb_empresa=".$_POST['cmb_empresa']."&txt_data_inicial=".$txt_data_inicial."&txt_data_final=".$txt_data_final."&opt_data=".$opt_data."&opt_opcao=".$opt_opcao);
        break;
    }
}else {
    require('../../../../lib/menu/menu.php');
?>
<html>
<head>
<title>.:: Consultar Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial ...
	if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
		return false
	}
//Data Final ...
	if(!data('form', 'txt_data_final', '4000', 'FIM')) {
		return false
	}
//Comparação com as Datas ...
	var data_inicial 	= document.form.txt_data_inicial.value
	var data_final 		= document.form.txt_data_final.value
	data_inicial 		= data_inicial.substr(6,4) + data_inicial.substr(3,2) + data_inicial.substr(0,2)
	data_final 			= data_final.substr(6,4) + data_final.substr(3,2) + data_final.substr(0,2)
	data_inicial 		= eval(data_inicial)
	data_final 			= eval(data_final)
	if(data_final < data_inicial) {
		alert('DATA DE FIM INVÁLIDA !!!\nDATA DE FIM MENOR DO QUE A DATA DE INÍCIO !')
		document.form.txt_data_final.focus()
		document.form.txt_data_final.select()
		return false
	}
}
</Script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório Nota Fiscal de Entrada
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" title="Relatório de Nota Fiscal de Entrada" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Notas Fiscais de Entrada por: Fornecedor" onclick="document.form.txt_consultar.focus()" id='label' checked>
            <label for="label">Fornecedor</label>
        </td>
        <td width="20%">
                Empresa
            <select name="cmb_empresa" title="Selecione a Empresa" class="combo">
            <?
                    $sql = "SELECT id_empresa, nomefantasia 
                            FROM `empresas` 
                            WHERE `ativo` = '1' ";
                    echo combos::combo($sql, $campos[0]['id_empresa']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar Notas Fiscais de Entrada por: Referência" onclick="document.form.txt_consultar.focus()" id='label2'>
            <label for="label2">Referência</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="3" title="Consultar Notas Fiscais de Entrada por: Discriminação" onclick="document.form.txt_consultar.focus()" id='label3'>
            <label for="label3">Discriminação</label>
        </td>
    </tr>
    <tr class='linhadestaque' align="center">
        <td colspan="2">
            <b>Consulta por Datas</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_data" value="1" title="Consultar Notas Fiscais de Entrada por: Data de Emissão" onclick="document.form.txt_consultar.focus()" id='label4' checked>
            <label for="label4">
                Data de Emissão
            </label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_data" value="2" title="Consultar Notas Fiscais de Entrada por: Data de Entrega" onclick="document.form.txt_consultar.focus()" id='label5'>
            <label for="label5">Data de Entrega</label>
        </td>
    </tr>
    <tr class="linhanormal" align="center">
        <td width="20%" colspan="2">
        De <input type="text" name="txt_data_inicial" title="Digite a Data Inicial" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" alt="" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c') "> até 
            <input type="text" name="txt_data_final" title="Digite a Data Final" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" alt="" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c') ">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_consultar.focus()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>