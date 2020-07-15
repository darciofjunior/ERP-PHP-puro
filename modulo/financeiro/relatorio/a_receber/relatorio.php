<?
if(empty($nao_redeclarar)) {//Às vezes essa tela é requirida dentro de outro arquivo ...
    require('../../../../lib/segurancas.php');
    require('../../../../lib/menu/menu.php');
    require('../../../../lib/genericas.php');
    require('../../../../lib/data.php');
    session_start('funcionarios');
}
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Função que pega os valores só que por semana ...
function listar_linhas_receber($data1, $data2) {
    $contas_receber_total = $contas_receber_semana_albafer = $contas_receber_semana_tool_master = $contas_receber_semana_grupo = 0;
    $contas_receber_total = $contas_receber_semana_albafer_caucao = $contas_receber_semana_tool_master_caucao = $contas_receber_semana_grupo_caucao = 0;
    
    $sql = "SELECT SUM(`valor` - `valor_desconto` - `valor_abatimento` + `valor_juros` + `valor_despesas` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
            FROM `contas_receberes` 
            WHERE `data_vencimento_alterada` BETWEEN '$data1' AND '$data2' 
            AND `ativo` = '1' 
            AND `status` IN (0, 1) GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
    $campos_contas_receber      = bancos::sql($sql);
    $linhas_contas_receber      = count($campos_contas_receber);
    for($i = 0; $i < $linhas_contas_receber; $i++) {
        switch((int)$campos_contas_receber[$i]['id_empresa']) {
            case 1: //Empresa Albafer
                if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_receber_semana_albafer+= $campos_contas_receber[$i]['valores'];
                }else if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_receber_semana_albafer+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_receber_semana_albafer+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
            case 2: //Empresa Tool Master
                if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_receber_semana_tool_master+= $campos_contas_receber[$i]['valores'];
                }else if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_receber_semana_tool_master+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_receber_semana_tool_master+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
            case 4: //Empresa Grupo
                if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_receber_semana_grupo+= $campos_contas_receber[$i]['valores'];
                }else if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_receber_semana_grupo+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_receber_semana_grupo+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
        }
    }
//Pego o valor semana pela empresa e o tipo de moeda do Caução ...
    $sql = "SELECT SUM(`valor` - `valor_desconto` - `valor_abatimento` + `valor_juros` + `valor_despesas` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
            FROM `contas_receberes` 
            WHERE `id_tipo_recebimento` = '11' 
            AND `data_vencimento_alterada` BETWEEN '$data1' AND '$data2' 
            AND `ativo` = '1' 
            AND `status` IN (0, 1) GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
    $campos_contas_receber      = bancos::sql($sql);
    $linhas_contas_receber      = count($campos_contas_receber);
    for($i = 0; $i < $linhas_contas_receber; $i++) {
        switch((int)$campos_contas_receber[$i]['id_empresa']) {
            case 1: //Empresa Albafer
                if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_receber_semana_albafer_caucao+= $campos_contas_receber[$i]['valores'];
                }else if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_receber_semana_albafer_caucao+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_receber_semana_albafer_caucao+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
            case 2: //Empresa Tool Master
                if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_receber_semana_tool_master_caucao+= $campos_contas_receber[$i]['valores'];
                }else if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_receber_semana_tool_master_caucao+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_receber_semana_tool_master_caucao+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
            case 4: //Empresa Grupo
                if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_receber_semana_grupo_caucao+= $campos_contas_receber[$i]['valores'];
                }else if((int)$campos_contas_receber[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_receber_semana_grupo_caucao+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_receber_semana_grupo_caucao+= ($campos_contas_receber[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
        }
    }
/******************************************************************************************************************/
//Seleciona o valor total de cheques de todo o periodo ...
/******************************************************************************************************************/
//Pego o valor total pela empresa e o tipo de moeda mas com o calculo de cheque em aberto ...
    $sql = "SELECT SUM(crq.`valor`) AS valores, cr.`id_empresa` 
            FROM `contas_receberes` cr 
            INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_conta_receber` = cr.`id_conta_receber` 
            INNER JOIN `cheques_clientes` cc ON cc.`id_cheque_cliente` = crq.`id_cheque_cliente` AND cc.`status` = '1' AND cc.`data_vencimento_alterada` BETWEEN '$data1' AND '$data2' 
            GROUP BY cr.`id_empresa` ORDER BY cr.`id_empresa` ";
//Neste select so trabalho com reais pois tenho recebimento somente em reais no financeiro ...
    $campos_contas_receber_cheque = bancos::sql($sql);
    $linhas_contas_receber_cheque = count($campos_contas_receber_cheque);
    for($i = 0; $i < $linhas_contas_receber_cheque; $i++) {
        $exibir = 1;//para exibir na tela
        switch((int)$campos_contas_receber_cheque[$i]['id_empresa']) {
            case 1://Empresa Albafer
                $valor_total_albafer_semana_cheque+= $campos_contas_receber_cheque[$i]['valores'];
            break;
            case 2://Empresa Tool Master
                $valor_total_tool_semana_cheque+= $campos_contas_receber_cheque[$i]['valores'];
            break;
            case 4://Empresa Grupo
                $valor_total_grupo_semana_cheque+= $campos_contas_receber_cheque[$i]['valores'];
            break;
        }
    }
    $dia    = substr($data1, 8, 2);
    $mes    = substr($data1, 5, 2);
    $ano    = substr($data1, 0, 4);
    $ano2   = substr($data2, 0, 4);
    if($ano != $ano2) $ano.= ' - '.$ano2;
    $semana = data::numero_semana($dia, $mes, $ano).'/'.$ano;
?>
    <tr>
    <tr class='linhanormal' align='right'>
        <td align='center'>
            <?=$semana;?>
        </td>
        <td align='center'>
            <?=data::datetodata($data1, '/').' até '.data::datetodata($data2, '/');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_albafer, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_albafer_caucao, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_albafer_semana_cheque, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_tool_master, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_tool_master_caucao, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_tool_semana_cheque, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_grupo, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_grupo_caucao, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_grupo_semana_cheque, 3), 2), 2, '.');?>
        </td>
        <td>
        <?
            $contas_receber_total = $contas_receber_semana_albafer + $contas_receber_semana_tool_master + $contas_receber_semana_grupo;
            echo segurancas::number_format(round(round($contas_receber_total, 3), 2), 2, '.');
            $GLOBALS['contas_receber_alba_ac']	= $GLOBALS['contas_receber_alba_ac'] + $contas_receber_semana_albafer;
            $GLOBALS['contas_receber_tool_ac']	= $GLOBALS['contas_receber_tool_ac'] + $contas_receber_semana_tool_master;
            $GLOBALS['contas_receber_grupo_ac']	= $GLOBALS['contas_receber_grupo_ac'] + $contas_receber_semana_grupo;
        ?>
        </td>
        <td>
        <?
            $contas_receber_total_caucao = $contas_receber_semana_albafer_caucao + $contas_receber_semana_tool_master_caucao + $contas_receber_semana_grupo_caucao;
            echo segurancas::number_format(round(round($contas_receber_total_caucao, 3), 2), 2, '.');
            $GLOBALS['contas_receber_alba_ac_caucao']+= 	$contas_receber_semana_albafer_caucao;
            $GLOBALS['contas_receber_tool_ac_caucao']+= 	$contas_receber_semana_tool_master_caucao;
            $GLOBALS['contas_receber_grupo_ac_caucao']+= 	$contas_receber_semana_grupo_caucao;
        ?>
        </td>
        <td>
        <?
            $total_semana_cheque = $valor_total_albafer_semana_cheque + $valor_total_tool_semana_cheque + $valor_total_grupo_semana_cheque;
            echo segurancas::number_format(round(round($total_semana_cheque, 3), 2), 2, '.');
            $GLOBALS['contas_receber_alba_cheque_ac']+=	$valor_total_albafer_semana_cheque;
            $GLOBALS['contas_receber_tool_cheque_ac']+=	$valor_total_tool_semana_cheque;
            $GLOBALS['contas_receber_grupo_cheque_ac']+=	$valor_total_grupo_semana_cheque;
        ?>
        </td>
    </tr>
<?
}
?>
<html>
<head>
<title>.:: Relatório de Conta(s) à Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
//Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
        return false
    }
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6, 4) + data_inicial.substr(3, 2) + data_inicial.substr(0, 2)
    data_final          = data_final.substr(6, 4) + data_final.substr(3, 2) + data_final.substr(0, 2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 365) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='erro' align='center'>
        <td colspan='2'>
            ESTE RELATÓRIO ESTÁ ERRADO !!!
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório de Conta(s) à Receber
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <?
                if(empty($_POST['txt_data_inicial'])) {
                    $data_inicial 	= date('d/m/Y');
                    $data_final 	= data::adicionar_data_hora(date('d/m/Y'), 180);
                }else {
                    $data_inicial 	= $_POST['txt_data_inicial'];
                    $data_final 	= $_POST['txt_data_final'];
                }
            ?>
            <p>Data Inicial: 
            <input type='text' name='txt_data_inicial' value='<?=$data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            Data Final:
            <input type='text' name='txt_data_final' value='<?=$data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
if(!empty($_POST['txt_data_inicial'])) {
    //Busca do último valor do dólar e do euro ...
    $sql = "SELECT `valor_dolar_dia`, `valor_euro_dia`, `data` 
            FROM `cambios` 
            ORDER BY `id_cambio` DESC LIMIT 1 ";
    $campos                         = bancos::sql($sql);
    $valor_dolar                    = $campos[0]['valor_dolar_dia'];
    $valor_euro                     = $campos[0]['valor_euro_dia'];
    $data_60_dias_atras             = data::datatodate(data::adicionar_data_hora(date('d-m-Y'), -60), '-');
    $data_180_dias_atras            = data::datatodate(data::adicionar_data_hora(date('d-m-Y'), -180), '-');
//Primeiro Intervalo da Semana ...
    $intervalo_semana               = data::intervalo_semana($txt_data_inicial);
    $intervalo_semana_data_inicial  = data::datatodate($intervalo_semana[0], '-');
    $intervalo_semana_data_final    = data::datatodate($intervalo_semana[1], '-');
    $data_60_dias_atras_inicial     = data::datatodate(data::adicionar_data_hora($intervalo_semana[0], -1), '-');
//Último Intervalo de Semana ...
    $intervalo_semana2              = data::intervalo_semana($txt_data_final);
    $intervalo_semana_data_inicial2 = data::datatodate($intervalo_semana2[0], '-');
    $intervalo_semana_data_final2   = data::datatodate($intervalo_semana2[1], '-');

/*O fato de ter condicao e condicao_cheque e por q tenho lá em baixo tabelas relaciona e n~ relaciona 
com o mesmo propósito */
    $condicao                               = " `data_vencimento_alterada` BETWEEN '$intervalo_semana_data_inicial' AND '$intervalo_semana_data_final2' ";
    $condicao_cheque                        = " cc.`data_vencimento` BETWEEN '$intervalo_semana_data_inicial' AND '$intervalo_semana_data_final2' ";
    $condicao_anterior                      = " `data_vencimento_alterada` BETWEEN '$data_60_dias_atras' AND '$data_60_dias_atras_inicial' ";
    $condicao_anterior_cheque               = " cc.`data_vencimento` BETWEEN '$data_60_dias_atras' AND '$data_60_dias_atras_inicial' ";
    $condicao_anterior_venc_180_60          = " `data_vencimento_alterada` BETWEEN '$data_180_dias_atras' AND '$data_60_dias_atras' ";
    $condicao_anterior_venc_180_60_cheque   = " cc.`data_vencimento` BETWEEN '$data_180_dias_atras' AND '$data_60_dias_atras'  ";

//Pego o valor total pela empresa e o tipo de moeda ...
    $sql = "SELECT SUM(`valor` - `valor_desconto` - `valor_abatimento` + `valor_juros` + `valor_despesas` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
            FROM `contas_receberes` 
            WHERE `ativo` = '1' 
            AND `status` IN (0, 1) 
            AND $condicao GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
    $campos_soma_moeda = bancos::sql($sql);
    $linhas_soma_moeda = count($campos_soma_moeda);

/*Tenho que zerar estas variáveis por causa que herda valores de Relatório anterior de a pagar já 
que este rel geral da require dos dois ...*/
    $valor_total_albafer                        = 0;
    $valor_total_tool                           = 0;
    $valor_total_grupo                          = 0;
    $contas_receber_semana_albafer_ac           = 0;
    $valor_total_albafer_anterior               = 0;
    $contas_receber_semana_albafer_ac_caucao    = 0;
    $valor_total_albafer_anterior_caucao        = 0;
    $contas_receber_semana_albafer_cheque_ac    = 0;
    $valor_total_albafer_anterior_cheque        = 0;
    $contas_receber_semana_tool_master_ac       = 0;
    $valor_total_tool_anterior                  = 0;
    $contas_receber_semana_tool_master_ac_caucao= 0;
    $valor_total_tool_anterior_caucao           = 0;
    $contas_receber_semana_tool_master_cheque_ac= 0;
    $valor_total_tool_anterior_cheque           = 0;
    $contas_receber_semana_grupo_ac             = 0;
    $valor_total_grupo_anterior                 = 0;
    $contas_receber_semana_grupo_ac_caucao      = 0;
    $valor_total_grupo_anterior_caucao          = 0;
    $contas_receber_semana_grupo_cheque_ac      = 0;
    $valor_total_grupo_anterior_cheque          = 0;
	
    for($i = 0; $i < $linhas_soma_moeda; $i++) {
        $exibir = 1;//P/ exibir na Tela ...
        switch((int)$campos_soma_moeda[$i]['id_empresa']) {
            case 1://Empresa Albafer
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_albafer+= $campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_albafer+= ($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_albafer+= ($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
            case 2://Empresa Tool Master
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_tool+= $campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_tool+= ($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_tool+= ($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
            case 4://Empresa Grupo
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_grupo+= $campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_grupo+= ($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_grupo+= ($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
        }
    }
//Pego o Valor Total pela empresa e o tipo de moeda mas nos pagamentos caucionado ...
    $sql = "SELECT SUM(`valor` - `valor_desconto` - `valor_abatimento` + `valor_juros` + `valor_despesas` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
            FROM `contas_receberes` 
            WHERE `id_tipo_recebimento` = '11' 
            AND `ativo` = '1' 
            AND `status` IN (0, 1) 
            AND $condicao GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
    $campos_soma_moeda = bancos::sql($sql);
    $linhas_soma_moeda = count($campos_soma_moeda);
    for($i = 0; $i < $linhas_soma_moeda; $i++) {
        $exibir = 1;//para exibir na tela
        switch((int)$campos_soma_moeda[$i]['id_empresa']) {
            case 1: //Empresa Albafer
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_albafer_caucao+= $campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_albafer_caucao+= ($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_albafer_caucao+= ($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
            case 2://Empresa Tool Master
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_tool_caucao+= $campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_tool_caucao+= ($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_tool_caucao+= ($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
            case 4://Empresa Grupo
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_grupo_caucao+=$campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_grupo_caucao+=($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_grupo_caucao+=($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
        }
    }
/******************************************************************************************************************/
//Seleciona o valor total de cheques de todo o periodo 
/******************************************************************************************************************/
//Pego o valor total pela empresa e o tipo de moeda mas com o calculo de cheque em aberto ...
    $sql = "SELECT SUM(crq.`valor`) AS valores, cr.`id_empresa` 
            FROM `contas_receberes` cr 
            INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_conta_receber` = cr.`id_conta_receber` 
            INNER JOIN `cheques_clientes` cc ON cc.`id_cheque_cliente` = crq.`id_cheque_cliente` AND cc.`status` = '1' 
            WHERE $condicao_cheque 
            GROUP BY cr.`id_empresa` ORDER BY cr.`id_empresa` ";
//Neste select só trabalho com reais pois tenho recebimento somente em reais no financeiro ...
    $campos_cheque = bancos::sql($sql);
    $linhas_cheque = count($campos_cheque);
    for($i = 0; $i < $linhas_cheque; $i++) {
        $exibir = 1;//para exibir na tela
        switch((int)$campos_cheque[$i]['id_empresa']) {
            case 1://Empresa Albafer ...
                $valor_total_albafer_cheque+= $campos_cheque[$i]['valores'];
            break;
            case 2://Empresa Tool Master ...
                $valor_total_tool_cheque+= $campos_cheque[$i]['valores'];
            break;
            case 4://Empresa Grupo ...
                $valor_total_grupo_cheque+= $campos_cheque[$i]['valores'];
            break;
        }
    }
    if(empty($exibir)) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='erro'>
        <td>
            NÃO EXISTE(M) CONTA(S) À RECEBER NESSE INTERVALO DE DATAS
        </td>
    </tr>
</table>
<?
    }else {
        //Pego o Valor Total pela empresa e o tipo de moeda da semanas anteriores ...
        $sql = "SELECT SUM(`valor` - `valor_desconto` - `valor_abatimento` + `valor_juros` + `valor_despesas` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
                FROM `contas_receberes` 
                WHERE `ativo` = '1' 
                AND `status` IN (0, 1) 
                AND $condicao_anterior GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
        $campos_soma_moeda_anterior = bancos::sql($sql);
        $linhas_soma_moeda_anterior = count($campos_soma_moeda_anterior);
        //Zero estas variáveis por causa que herda valores anteriores ...
        //Zero estas variáveis por causa que herda valores anteriores ...
        $valor_total_albafer_anterior 	= 0;
        $valor_total_tool_anterior      = 0;
        $valor_total_grupo_anterior 	= 0;
        for($i = 0; $i < $linhas_soma_moeda_anterior; $i++) {
            switch((int)$campos_soma_moeda_anterior[$i]['id_empresa']) {
                case 1: //Empresa Albafer
                    if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_albafer_anterior+= $campos_soma_moeda_anterior[$i]['valores'];
                    }else if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_albafer_anterior+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_dolar);
                    }else {//Euro
                        $valor_total_albafer_anterior+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_euro);
                    }
                break;
                case 2: //Empresa Tool Master
                    if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_tool_anterior+= $campos_soma_moeda_anterior[$i]['valores'];
                    }else if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_tool_anterior+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_dolar);
                    }else {//Euro
                        $valor_total_tool_anterior+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_euro);
                    }
                break;
                case 4: //Empresa Grupo
                    if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_grupo_anterior+= $campos_soma_moeda_anterior[$i]['valores'];
                    }else if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_grupo_anterior+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_dolar);
                    }else { //euro
                        $valor_total_grupo_anterior+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_euro);
                    }
                break;
            }
        }
        //Pego o valor total pela empresa e o tipo de moeda da semanas anteriores do Caução ...
        $sql = "SELECT SUM(`valor` - `valor_desconto` - `valor_abatimento` + `valor_juros` + `valor_despesas` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
                FROM `contas_receberes` 
                WHERE `id_tipo_recebimento` = '11' 
                AND `ativo` = '1' 
                AND `status` IN (0, 1) 
                AND $condicao_anterior GROUP BY id_empresa, id_tipo_moeda ORDER BY id_empresa ";
        $campos_soma_moeda_anterior = bancos::sql($sql);
        $linhas_soma_moeda_anterior = count($campos_soma_moeda_anterior);
        for($i = 0; $i < $linhas_soma_moeda_anterior; $i++) {
            switch((int)$campos_soma_moeda_anterior[$i]['id_empresa']) {
                case 1: //Empresa Albafer
                    if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_albafer_anterior_caucao+= $campos_soma_moeda_anterior[$i]['valores'];
                    }else if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_albafer_anterior_caucao+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_dolar);
                    }else {//Euro
                        $valor_total_albafer_anterior_caucao+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_euro);
                    }
                break;
                case 2: //Empresa Tool Master
                    if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_tool_anterior_caucao+= $campos_soma_moeda_anterior[$i]['valores'];
                    }else if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_tool_anterior_caucao+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_dolar);
                    }else { //euro
                        $valor_total_tool_anterior_caucao+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_euro);
                    }
                break;
                case 4: //Empresa Grupo
                    if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_grupo_anterior_caucao+= $campos_soma_moeda_anterior[$i]['valores'];
                    }else if((int)$campos_soma_moeda_anterior[$i]['id_tipo_moeda']==2) {//Dólar
                        $valor_total_grupo_anterior_caucao+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_dolar);
                    }else { //euro
                        $valor_total_grupo_anterior_caucao+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_euro);
                    }
                break;
            }
        }
/******************************************************************************************************************/
//Seleciona o valor total de cheques de todo o periodo da semanas anteriores mas com o cheque em aberto e nao compensados
/******************************************************************************************************************/
        $sql = "SELECT SUM(crq.`valor`) AS valores, cr.`id_empresa` 
                FROM `contas_receberes` cr 
                INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_conta_receber` = cr.`id_conta_receber` 
                INNER JOIN `cheques_clientes` cc ON cc.`id_cheque_cliente` = crq.`id_cheque_cliente` AND cc.`status` = '1' AND $condicao_anterior_cheque 
                GROUP BY cr.`id_empresa` ORDER BY cr.`id_empresa` ";
        //Neste select so trabalho com reais pois tenho recebimento somente em reais no financeiro ...
        $campos_anterior_cheque = bancos::sql($sql);
        $linhas_anterior_cheque = count($campos_anterior_cheque);
        for($i = 0; $i < $linhas_anterior_cheque; $i++) {
            $exibir = 1;//Para exibir na Tela ...
            switch((int)$campos_anterior_cheque[$i]['id_empresa']) {
                case 1://Empresa Albafer
                    $valor_total_albafer_anterior_cheque+= $campos_anterior_cheque[$i]['valores'];
                break;
                case 2://Empresa Tool Master
                    $valor_total_tool_anterior_cheque+= $campos_anterior_cheque[$i]['valores'];
                break;
                case 4://Empresa Grupo
                    $valor_total_grupo_anterior_cheque+= $campos_anterior_cheque[$i]['valores'];
                break;
            }
        }
        //Pego o Valor Total pela empresa e o tipo de moeda das contas vencidas entre 180 e 60 dias ...
        $sql = "SELECT SUM(`valor` - `valor_desconto` - `valor_abatimento` + `valor_juros` + `valor_despesas` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
                FROM `contas_receberes` 
                WHERE `ativo` = '1' 
                AND `status` IN (0, 1) 
                AND $condicao_anterior_venc_180_60 GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
        $campos_soma_moeda_venc_60 = bancos::sql($sql);
        $linhas_soma_moeda_venc_60 = count($campos_soma_moeda_venc_60);
        for($i = 0; $i < $linhas_soma_moeda_venc_60; $i++) {
            switch((int)$campos_soma_moeda_venc_60[$i]['id_empresa']) {
                case 1://Empresa Albafer
                    if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_albafer_venc_60+= $campos_soma_moeda_venc_60[$i]['valores'];
                    }else if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_albafer_venc_60+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_dolar);
                    }else {//Euro
                        $valor_total_albafer_venc_60+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_euro);
                    }
                break;
                case 2://Empresa Tool Master
                    if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_tool_venc_60+= $campos_soma_moeda_venc_60[$i]['valores'];
                    }else if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_tool_venc_60+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_dolar);
                    }else {//Euro
                        $valor_total_tool_venc_60+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_euro);
                    }
                break;
                case 4://Empresa Grupo
                    if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_grupo_venc_60+= $campos_soma_moeda_venc_60[$i]['valores'];
                    }else if((int)$campos_soma_venc_60[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_grupo_venc_60+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_dolar);
                    }else {//euro
                        $valor_total_grupo_venc_60+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_euro);
                    }
                break;
            }
        }
        //Pego o Valor Total pela empresa e o tipo de moeda das contas vencidas entre 180 e 60 dias - Caução ...
        $sql = "SELECT SUM(`valor` - `valor_desconto` - `valor_abatimento` + `valor_juros` + `valor_despesas` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
                FROM `contas_receberes` 
                WHERE `id_tipo_recebimento` = '11' 
                AND `ativo` = '1' 
                AND `status` IN (0, 1) 
                AND $condicao_anterior_venc_180_60 GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
        $campos_soma_moeda_venc_60 = bancos::sql($sql);
        $linhas_soma_moeda_venc_60 = count($campos_soma_moeda_venc_60);
        for($i = 0; $i < $linhas_soma_moeda_venc_60; $i++) {
            switch((int)$campos_soma_moeda_venc_60[$i]['id_empresa']) {
                case 1: //Empresa Albafer
                    if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_albafer_venc_60_caucao+= $campos_soma_moeda_venc_60[$i]['valores'];
                    }else if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_albafer_venc_60_caucao+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_dolar);
                    }else {//Euro
                        $valor_total_albafer_venc_60_caucao+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_euro);
                    }
                break;
                case 2: //Empresa Tool Master
                    if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_tool_venc_60_caucao+= $campos_soma_moeda_venc_60[$i]['valores'];
                    }else if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_tool_venc_60_caucao+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_dolar);
                    }else {//Euro
                        $valor_total_tool_venc_60_caucao+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_euro);
                    }
                break;
                case 4: //Empresa Grupo
                    if((int)$campos_soma_moeda_venc_60[$i]['id_tipo_moeda'] == 1) {//Real
                        $valor_total_grupo_venc_60_caucao+= $campos_soma_moeda_venc_60[$i]['valores'];
                    }else if((int)$campos_soma_venc_60[$i]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_total_grupo_venc_60_caucao+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_dolar);
                    }else {//Euro
                        $valor_total_grupo_venc_60_caucao+= ($campos_soma_moeda_venc_60[$i]['valores'] * $valor_euro);
                    }
                break;
            }
        }
/******************************************************************************************************************/
//Seleciona o valor total de cheques de todo o periodo da semanas anteriores mas com o cheque em aberto e nao compensados
/******************************************************************************************************************/
        $sql = "SELECT SUM(crq.`valor`) AS valores, cr.`id_empresa` 
                FROM `contas_receberes` cr 
                INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_conta_receber` = cr.`id_conta_receber` 
                INNER JOIN `cheques_clientes` cc ON cc.`id_cheque_cliente` = crq.`id_cheque_cliente` AND cc.`status` = '1' AND $condicao_anterior_venc_180_60_cheque 
                GROUP BY cr.`id_empresa` ORDER BY cr.`id_empresa` ";
        //Neste select só trabalho com reais pois tenho recebimento somente em reais no financeiro ...
        $campos_venc_60_cheque = bancos::sql($sql);
        $linhas_venc_60_cheque = count($campos_venc_60_cheque);
        for($i = 0; $i < $linhas_venc_60_cheque; $i++) {
            $exibir = 1;//para exibir na tela
            switch((int)$campos_venc_60_cheque[$i]['id_empresa']) {
                case 1: //Empresa Albafer
                    $valor_total_albafer_venc_60_cheque+= $campos_venc_60_cheque[$i]['valores'];
                break;
                case 2: //Empresa Tool Master
                    $valor_total_tool_venc_60_cheque+= $campos_venc_60_cheque[$i]['valores'];
                break;
                case 4: //Empresa Grupo
                    $valor_total_grupo_venc_60_cheque+= $campos_venc_60_cheque[$i]['valores'];
                break;
            }
        }
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Semana / Ano
        </td>
        <td>
            Intervalo de datas
        </td>
        <td colspan='3'>
            Albafer
        </td>
        <td colspan='3'>
            Tool Master
        </td>
        <td colspan='3'>
            Grupo
        </td>
        <td colspan='3'>
            Total
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
        <td>
            Contas
        </td>
        <td>
            Caução
        </td>
        <td>
            Cheques
        </td>
        <td>
            Contas
        </td>
        <td>
            Caução
        </td>
        <td>
            Cheques
        </td>
        <td>
            Contas
        </td>
        <td>
            Caução
        </td>
        <td>
            Cheques
        </td>
        <td>
            Contas
        </td>
        <td>
            Caução
        </td>
        <td>
            Cheques
        </td>
    </tr>
    <tr class='linhacabecalho' align='right'>
        <td align='left'>
            Total por Empresa
        </td>
        <td align='center'>
            Até <?=data::datetodata($intervalo_semana_data_final2, '/');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_albafer + $valor_total_albafer_anterior, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_albafer_caucao + $valor_total_albafer_anterior_caucao, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_albafer_cheque + $valor_total_albafer_anterior_cheque, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_tool + $valor_total_tool_anterior, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_tool_caucao + $valor_total_tool_anterior_caucao, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_tool_cheque + $valor_total_tool_anterior_cheque,3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_grupo + $valor_total_grupo_anterior, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_grupo_caucao + $valor_total_grupo_anterior_caucao, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($valor_total_grupo_cheque + $valor_total_grupo_anterior_cheque, 3), 2), 2, '.');?>
        </td>
        <td>
        <?
            $valor_total_empresas           = $valor_total_albafer + $valor_total_tool + $valor_total_grupo;
            $valor_total_empresas_anterior  = $valor_total_albafer_anterior + $valor_total_tool_anterior + $valor_total_grupo_anterior;
            echo segurancas::number_format(round(round($valor_total_empresas + $valor_total_empresas_anterior, 3), 2), 2, '.');
        ?>
        </td>
        <td>
        <?
            $valor_total_empresas_caucao            = $valor_total_albafer_caucao + $valor_total_tool_caucao + $valor_total_grupo_caucao;
            $valor_total_empresas_anterior_caucao   = $valor_total_albafer_anterior_caucao + $valor_total_tool_anterior_caucao + $valor_total_grupo_anterior_caucao;
            echo segurancas::number_format(round(round($valor_total_empresas_caucao + $valor_total_empresas_anterior_caucao, 3), 2), 2, '.');
        ?>
        </td>
        <td align='right'>
        <?
            $val_tot_cheque                         = $valor_total_albafer_cheque + $valor_total_tool_cheque + $valor_total_grupo_cheque;
            $val_tot_ant_cheque                     = $valor_total_albafer_anterior_cheque + $valor_total_tool_anterior_cheque + $valor_total_grupo_anterior_cheque;
            echo segurancas::number_format(round(round($val_tot_cheque + $val_tot_ant_cheque, 3), 2), 2, '.');
        ?>
        </td>
    </tr>
    <tr class='linhanormaldestaque' align='right'>
        <td colspan='2' align='center'>
            <b>Vencidas a mais de 60 até 180 dias / antes de <?=data::datetodata($data_60_dias_atras, '/');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_albafer_venc_60, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_albafer_venc_60_caucao, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_albafer_venc_60_cheque, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_tool_venc_60, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_tool_venc_60_caucao, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_tool_venc_60_cheque, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_grupo_venc_60, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_grupo_venc_60_caucao, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_grupo_venc_60_cheque, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b>
            <?
                $valor_total_empresas_venc_60 = $valor_total_albafer_venc_60 + $valor_total_tool_venc_60 + $valor_total_grupo_venc_60;
                echo segurancas::number_format(round(round($valor_total_empresas_venc_60, 3), 2), 2, '.');
            ?>
            </b>
        </td>
        <td>
            <b>
            <?
                $valor_total_empresas_venc_60_caucao = $valor_total_albafer_venc_60_caucao + $valor_total_tool_venc_60_caucao + $valor_total_grupo_venc_60_caucao;
                echo segurancas::number_format(round(round($valor_total_empresas_venc_60_caucao, 3), 2), 2, '.');
            ?>
            </b>
        </td>
        <td>
            <b>
            <?
                $valor_total_empresas_venc_60_cheque = $valor_total_albafer_venc_60_cheque + $valor_total_tool_venc_60_cheque + $valor_total_grupo_venc_60_cheque;
                echo segurancas::number_format(round(round($valor_total_empresas_venc_60_cheque, 3), 2), 2, '.');
            ?>
            </b>
        </td>
    </tr>
    <tr class='linhanormaldestaque' align='right'>
        <td colspan='2' align='center'>
            <b>Semanas Anteriores de <?=data::datetodata($data_60_dias_atras, '/');?> até <?=data::datetodata($data_60_dias_atras_inicial, '/');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_albafer_anterior, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_albafer_anterior_caucao, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_albafer_anterior_cheque, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_tool_anterior, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_tool_anterior_caucao, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_tool_anterior_cheque, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_grupo_anterior, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_grupo_anterior_caucao, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_grupo_anterior_cheque, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_empresas_anterior, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($valor_total_empresas_anterior_caucao, 3), 2), 2, '.');?></b>
        </td>
        <td>
            <b><?=segurancas::number_format(round(round($val_tot_ant_cheque, 3), 2), 2, '.');?></b>
        </td>
    </tr>
<?
    $intervalo_semana 	= data::intervalo_semana($txt_data_inicial);
    $data1              = data::datatodate($intervalo_semana[0], '-');
    $data2              = data::datatodate($intervalo_semana[1], '-');

    while($data1 < $intervalo_semana_data_final2) {
        listar_linhas_receber($data1, $data2);
        $data1 = data::datetodata($data1, '-');
        $data2 = data::datetodata($data2, '-');
        $data1 = data::adicionar_data_hora($data1, 7);
        $data2 = data::adicionar_data_hora($data2, 7);
        $data1 = data::datatodate($data1, '-');
        $data2 = data::datatodate($data2, '-');
    }
?>
    </tr>
    <tr class='linhadestaque' align='right'>
        <td>
            Total por Empresa
        </td>
        <td align='center'>
            Até <?=data::datetodata($intervalo_semana_data_final2,'/');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_albafer_ac + $valor_total_albafer_anterior, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_albafer_ac_caucao + $valor_total_albafer_anterior_caucao, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_albafer_cheque_ac + $valor_total_albafer_anterior_cheque, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_tool_master_ac + $valor_total_tool_anterior, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_tool_master_ac_caucao + $valor_total_tool_anterior_caucao, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_tool_master_cheque_ac + $valor_total_tool_anterior_cheque, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_grupo_ac + $valor_total_grupo_anterior, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_grupo_ac_caucao + $valor_total_grupo_anterior_caucao, 3), 2), 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format(round(round($contas_receber_semana_grupo_cheque_ac + $valor_total_grupo_anterior_cheque, 3), 2), 2, '.');?>
        </td>
        <td>
        <?
            $contas_receber_total_ac = $contas_receber_semana_albafer_ac + $contas_receber_semana_tool_master_ac + $contas_receber_semana_grupo_ac;
            echo segurancas::number_format(round(round($contas_receber_total_ac + $valor_total_empresas_anterior, 3), 2), 2, '.');
        ?>
        </td>
        <td>
        <?
            $contas_receber_total_ac_caucao = $contas_receber_semana_albafer_ac_caucao + $contas_receber_semana_tool_master_ac_caucao + $contas_receber_semana_grupo_ac_caucao;
            echo segurancas::number_format(round(round($contas_receber_total_ac_caucao + $valor_total_empresas_anterior_caucao, 3), 2), 2, '.');
        ?>
        </td>
        <td>
        <?
            $contas_receber_total_cheque_ac = $contas_receber_semana_albafer_cheque_ac + $contas_receber_semana_tool_master_cheque_ac + $contas_receber_semana_grupo_cheque_ac;
            echo segurancas::number_format(round(round($contas_receber_total_cheque_ac + $val_tot_ant_cheque, 3), 2), 2, '.');
        ?>
        </td>
    </tr>
</table>
</body>
</html>
<pre>
<b><font color='red'>Atenção:</font></b>
<pre>
* Os vencimentos a mais de 60 até 180 dias não estão somados no total por empresa.
* Os valores das colunas caução não estão abatidos nas outras colunas.
</pre>
<?
    }
}
?>