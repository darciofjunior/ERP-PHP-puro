<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
session_start('funcionarios');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial 	= $_POST['txt_data_inicial'];
    $txt_data_final 	= $_POST['txt_data_final'];
    $cmb_departamento 	= $_POST['cmb_departamento'];
    $hdd_ja_submeteu	= $_POST['hdd_ja_submeteu'];
}else {
    $txt_data_inicial 	= $_GET['txt_data_inicial'];
    $txt_data_final 	= $_GET['txt_data_final'];
    $cmb_departamento 	= $_GET['cmb_departamento'];
    $hdd_ja_submeteu	= $_GET['hdd_ja_submeteu'];
}
?>
<html>
<head>
<title>.:: Ouvidoria(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_data_inicial.value != '' && document.form.txt_data_final.value != '') {
//Data de Emissão Inicial
        if(!data('form', 'txt_data_inicial', '4000', 'DE INÍCIO')) {
            return false
        }
//Data de Emissão Final
        if(!data('form', 'txt_data_final', '4000', 'FIM')) {
            return false
        }
        data_inicial = document.form.txt_data_inicial.value
        data_final = document.form.txt_data_final.value

        data_inicial = data_inicial.replace('/', '')
        data_inicial_sem_formatacao = data_inicial.replace('/', '')

        data_final = data_final.replace('/', '')
        data_final_sem_formatacao = data_final.replace('/', '')

        data_inicial_invertida = data_inicial_sem_formatacao.substr(4, 4)+data_inicial_sem_formatacao.substr(2, 2)+data_inicial_sem_formatacao.substr(0, 2)
        data_final_invertida = data_final_sem_formatacao.substr(4, 4)+data_final_sem_formatacao.substr(2, 2)+data_final_sem_formatacao.substr(0, 2)

        data_inicial_invertida = eval(data_inicial_invertida)
        data_final_invertida = eval(data_final_invertida)

        if(data_inicial_invertida > data_final_invertida) {
            alert('DATAS INVÁLIDAS !')
            document.form.txt_data_inicial.focus()
            return false
        }
        document.form.hdd_ja_submeteu.value = 1
    }
}
</Script>
</head>
<body onLoad="document.form.cmb_departamento.focus()">
<form name="form" method="post" action='' onsubmit="return validar()">
<!--Controle de Tela - Significa que já submeteu pelo menos 1 vez-->
<input type='hidden' name='hdd_ja_submeteu' value='<?=$hdd_ja_submeteu;?>'>
<!--*************************************************************-->
<table border="0" width='80%' align="center" cellspacing ='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Ouvidoria(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Departamento:
            <select name='cmb_departamento' title="Selecione o Departamento" class="combo">
            <?
                //Aqui eu só listo os Depto. que estão ativos  ...
                $sql = "SELECT `id_departamento`, `departamento` 
                        FROM `departamentos` 
                        WHERE `ativo` = '1' ORDER BY `departamento` ";
                echo combos::combo($sql, $cmb_departamento);
            ?>
            </select> 
            <?
//Ainda não fez o Filtro ...
                if(empty($hdd_ja_submeteu)) {//Sugestão na hora em que acaba de carregar a Tela ...
                    $txt_data_inicial = date('d/m/Y');
                    $txt_data_final = date('d/m/Y');
                }else {
//Busca das Pendências no Período especificado ...
                    if(!empty($txt_data_inicial)) {
                        $txt_data_inicial_usa 	= data::datatodate($txt_data_inicial, '-');
                        $txt_data_final_usa 	= data::datatodate($txt_data_final, '-');
                        $condicao_periodo = " AND SUBSTRING(o.data_sys, 1, 10) BETWEEN '$txt_data_inicial_usa' AND '$txt_data_final_usa' ";
                    }
                }
            ?>
            &nbsp;-&nbsp;Data Inicial: 
            <input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final: 
            <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<? 
//Ainda não fez o Filtro ...
	if(empty($hdd_ja_submeteu)) {
?>
    <tr>
        <td></td>
    </tr>
<? 
	}else {
		if(empty($cmb_departamento)) $cmb_departamento = '%';

		$sql = "SELECT d.departamento, o.assunto, o.ocorrencia, CONCAT(DATE_FORMAT(SUBSTRING(o.data_sys, 1, 10), '%d/%m/%Y'), ' às ',SUBSTRING(o.data_sys, 12, 8)) as data_hora 
                        FROM `ouvidorias` o 
                        INNER JOIN `departamentos` d ON d.id_departamento = o.id_departamento 
                        WHERE o.id_departamento LIKE '$cmb_departamento' 
                        $condicao_periodo ORDER BY o.data_sys DESC, o.assunto ";
		$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
		$linhas = count($campos);
		if($linhas == 0) {//Caso não encontre algum Cliente nesse Tipo de Filtro ...
?>
    <tr></tr>
    <tr align="center">
        <td colspan='4'>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
			exit;
		}
?>
    <tr class="linhacabecalho" align="center">
        <td>Assunto</td>
        <td>Departamento</td>
        <td>Ocorrência</td>
        <td>Data e Hora</td>
    </tr>
<?
//Listagem de Follow-Up(s)
		for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal">
        <td>
            <?=$campos[$i]['assunto'];?>
        </td>
        <td align="center">
            <?=$campos[$i]['departamento'];?>
        </td>
        <td>
            <?=$campos[$i]['ocorrencia'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['data_hora'];?>
        </td>
    </tr>
<?
		}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>