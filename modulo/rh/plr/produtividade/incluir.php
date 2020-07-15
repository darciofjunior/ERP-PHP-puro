<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/plr/produtividade/opcoes.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>PRODU��O MENSAL INCLU�DA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>PRODU��O MENSAL J� EXISTENTE.</font>";

if(!empty($_POST['txt_albafer_tool'])) {
//Muda o Formato das vari�veis p/ poder gravar no BD ...
    $data_inicial_sub_per   = data::datatodate($_POST['txt_data_inicial_sub_per'], '-');
    $data_final_sub_per     = data::datatodate($_POST['txt_data_final_sub_per'], '-');
//Verifico se j� existe esse Valor Alba + Tool no per�odo selecionado pelo usu�rio ...
    $sql = "SELECT id_plr_produtividade 
            FROM `plr_produtividades` 
            WHERE `id_plr_periodo` = '$_POST[cmb_periodo]' 
            AND `data_inicial_sub_per` = '$data_inicial_sub_per' 
            AND `data_final_sub_per` = '$data_final_sub_per' 
            AND `albafer_tool` = '$_POST[txt_albafer_tool]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Produ��o Mensal n�o existente
        $sql = "INSERT INTO `plr_produtividades` (`id_plr_produtividade`, `id_plr_periodo`, `data_inicial_sub_per`, `data_final_sub_per`, `albafer_tool`) values (NULL, '$_POST[cmb_periodo]', '$data_inicial_sub_per', '$data_final_sub_per', '$_POST[txt_albafer_tool]') ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Produ��o Mensal j� existente no m�s e ano especificados ...
        $valor = 2;
    }
}

/*Aqui eu busco o �ltimo Per�odo de PLR utilizado e a �ltima Data Final de Sub-Per�odo 
cadastrada pela Tela de Produ��o Mensal ...*/
$sql = "SELECT id_plr_periodo, data_final_sub_per 
        FROM `plr_produtividades` 
        ORDER BY data_final_sub_per DESC LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 0) {//N�o existe nenhum registro na Base de Dados ainda ...
/*Aqui eu sugiro as datas de sub-per�odo de PLR cadastrado na Base de Dados, p/ que este venha 
como sugestivo na hora de se incluir a Produ��o Mensal ...*/
    $sql = "SELECT id_plr_periodo, data_inicial 
            FROM plr_periodos 
            ORDER BY id_plr_periodo LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $id_plr_periodo             = $campos[0]['id_plr_periodo'];
    $prox_data_inicial_sub_per  = data::datetodata($campos[0]['data_inicial'], '/');
}else {
//Deixa como sugestivo a �ltima data de sub-per�odo cadastrada na Base de Dados ...
    $id_plr_periodo             = $campos[0]['id_plr_periodo'];
    $ult_data_final_sub_per     = data::datetodata($campos[0]['data_final_sub_per'], '/');
    $prox_data_inicial_sub_per  = data::adicionar_data_hora($ult_data_final_sub_per, 1);
}

$mes = substr($prox_data_inicial_sub_per, 3, 2);
$ano = substr($prox_data_inicial_sub_per, 6, 4);
//Montagem da Data Final ...
$mes++;
if($mes == 13) {
    $mes = 1;
    $ano++;
}
if($mes < 10) $mes = '0'.$mes;
$prox_data_final_sub_per = '25/'.$mes.'/'.$ano;
?>
<html>
<title>.:: Incluir Produ��o Mensal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Per�odo ...
    if(!combo('form', 'cmb_periodo', '', 'SELECIONE O PER�ODO !')) {
        return false
    }
//Data Inicial do Sub-Per�odo
    if(!data('form', 'txt_data_inicial_sub_per', '4000', 'IN�CIO DO SUB-PER�ODO')) {
        return false
    }
//Data Final do Sub-Per�odo
    if(!data('form', 'txt_data_final_sub_per', '4000', 'FIM DO SUB-PER�ODO')) {
        return false
    }
//Albaf�r + Tool
    if(!texto('form', 'txt_albafer_tool', '3', '1234567890,.', 'VALOR ALBAF�R + TOOL', '2')) {
        return false
    }
/*****************************Seguran�as com as Datas*****************************/
//Aqui eu separo as datas de Per�odo que foram selecionadas na combo de Per�odo ...
    periodo = (document.form.cmb_periodo[document.form.cmb_periodo.selectedIndex].text)
    tamanho_periodo = periodo.length

    primeira_data_periodo = periodo.substr(0, 10)//Pega a 1� data do Per�odo
    ultima_data_periodo = periodo.substr((tamanho_periodo - 10), 10)//Pega a 2� data do Per�odo

    primeira_data_periodo = primeira_data_periodo.substr(6,4)+primeira_data_periodo.substr(3,2)+primeira_data_periodo.substr(0,2)
    ultima_data_periodo = ultima_data_periodo.substr(6,4)+ultima_data_periodo.substr(3,2)+ultima_data_periodo.substr(0,2)

    primeira_data_periodo = eval(primeira_data_periodo)
    ultima_data_periodo = eval(ultima_data_periodo)

    var data_inicial_sub_per = document.form.txt_data_inicial_sub_per.value
    var data_final_sub_per = document.form.txt_data_final_sub_per.value

    data_inicial_sub_per = data_inicial_sub_per.substr(6,4)+data_inicial_sub_per.substr(3,2)+data_inicial_sub_per.substr(0,2)
    data_final_sub_per = data_final_sub_per.substr(6,4)+data_final_sub_per.substr(3,2)+data_final_sub_per.substr(0,2)

    data_inicial_sub_per = eval(data_inicial_sub_per)
    data_final_sub_per = eval(data_final_sub_per)
//Compara��es da Data Inicial do Sub-Per�odo com as Datas do Per�odo ...
    if((data_inicial_sub_per < primeira_data_periodo) || (data_inicial_sub_per > ultima_data_periodo)) {
        alert('DATA INICIAL DE SUB-PER�ODO INV�LIDA !!!\n DATA INICIAL DE SUB-PER�ODO EST� FORA DO PER�ODO SELECIONADO !')
        document.form.txt_data_inicial_sub_per.focus()
        document.form.txt_data_inicial_sub_per.select()
        return false
    }
//Compara��es da Data Final do Sub-Per�odo com as Datas do Per�odo ...
    if((data_final_sub_per < primeira_data_periodo) || (data_final_sub_per > ultima_data_periodo)) {
        alert('DATA FINAL DE SUB-PER�ODO INV�LIDA !!!\n DATA FINAL DE SUB-PER�ODO EST� FORA DO PER�ODO SELECIONADO !')
        document.form.txt_data_final_sub_per.focus()
        document.form.txt_data_final_sub_per.select()
        return false
    }
//Compara��es entre as Datas de Sub-Per�odo ...
    if(data_final_sub_per < data_inicial_sub_per) {
        alert('DATA FINAL DE SUB-PER�ODO INV�LIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL DO SUB-PER�ODO !')
        document.form.txt_data_final_sub_per.focus()
        document.form.txt_data_final_sub_per.select()
        return false
    }
    return limpeza_moeda('form', 'txt_albafer_tool, ')
}
</Script>
</head>
<body onload="document.form.txt_albafer_tool.focus()">
<form name="form" method="post" action='' onSubmit="return validar()">
<table border="0" width='60%' align="center" cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Produ��o Mensal
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Per�odo: </b>
        </td>
        <td>
            <select name="cmb_periodo" title="Selecione o Per�odo" class="combo">
            <?
                $sql = "SELECT id_plr_periodo, CONCAT(DATE_FORMAT(data_inicial, '%d/%m/%Y'), ' � ', DATE_FORMAT(data_final, '%d/%m/%Y')) AS periodo 
                        FROM `plr_periodos` 
                        ORDER BY id_plr_periodo DESC ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Data Inicial do Sub-Per�odo: </b>
        </td>
        <td>
            <input type="text" name="txt_data_inicial_sub_per" value="<?=$prox_data_inicial_sub_per;?>" title="Digite a Data Inicial do Sub-Per�odo" size="13" maxlength="10" onkeyup="verifica(this, 'data', '', '', event)" class="caixadetexto">
            &nbsp;<img src="../../../../imagem/calendario.gif" width="12" height="12" alt="" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial_sub_per&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Data Final do Sub-Per�odo: </b>
        </td>
        <td>
            <input type="text" name="txt_data_final_sub_per" value="<?=$prox_data_final_sub_per;?>" title="Digite a Data Final do Sub-Per�odo" size="13" maxlength="10" onkeyup="verifica(this, 'data', '', '', event)" class="caixadetexto">
            &nbsp;<img src="../../../../imagem/calendario.gif" width="12" height="12" alt="" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final_sub_per&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Albaf�r + Tool: </b>
        </td>
        <td>
            <input type="text" name="txt_albafer_tool" title="Digite o Valor Albaf�r + Tool" size="15" maxlength="13" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'opcoes.php'" class="botao">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_albafer_tool.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>