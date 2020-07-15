<?
if(empty($nao_redeclarar)) {//Às vezes essa tela é requirida dentro de outro arquivo ...
    require('../../../../lib/segurancas.php');
    require('../../../../lib/menu/menu.php');
    require('../../../../lib/genericas.php');
    require('../../../../lib/data.php');
    session_start('funcionarios');
}
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

function listar_linhas($data1, $data2, $valor_dolar, $valor_euro) {
    //Pego os valores semana por semana ...
    $sql = "SELECT SUM(`valor` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
            FROM `contas_apagares` 
            WHERE `data_vencimento` BETWEEN '$data1' AND '$data2' 
            AND `ativo` = '1' 
            AND `urgente` = 'S' 
            AND `status` IN (0, 1) GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
    $campos_contas_apagar = bancos::sql($sql);
    $linhas_contas_apagar = count($campos_contas_apagar);
    for($i = 0; $i < $linhas_contas_apagar; $i++) {
        switch((int)$campos_contas_apagar[$i]['id_empresa']) {
            case 1: //Empresa Albafer
                if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_apagar_semana_albafer+= $campos_contas_apagar[$i]['valores'];
                }else if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_apagar_semana_albafer+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_apagar_semana_albafer+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
            case 2: //Empresa Tool Master
                if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_apagar_semana_tool_master+= $campos_contas_apagar[$i]['valores'];
                }else if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_apagar_semana_tool_master+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_apagar_semana_tool_master+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
            case 4: //Empresa Grupo
                if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_apagar_semana_grupo+= $campos_contas_apagar[$i]['valores'];
                }else if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_apagar_semana_grupo+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_apagar_semana_grupo+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
        }
    }
    //Pego os valores semana por semana mas com o cheque pré ...
    $sql = "SELECT SUM(`valor`) AS valores, `id_empresa`, `id_tipo_moeda` 
            FROM `contas_apagares` 
            WHERE `data_vencimento` BETWEEN '$data1' AND '$data2' 
            AND `ativo` = '1' 
            AND `urgente` = 'S' 
            AND (`status` = '2' AND `predatado` = '1') GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
    $campos_semana = bancos::sql($sql);
    $linhas_semana = count($campos_semana);
    $campos_contas_apagar = bancos::sql($sql);
    $linhas_contas_apagar = count($campos_contas_apagar);
    for($i = 0; $i < $linhas_contas_apagar; $i++) {
        switch((int)$campos_contas_apagar[$i]['id_empresa']) {
            case 1: //Empresa Albafer
                if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_apagar_semana_albafer+= $campos_contas_apagar[$i]['valores'];
                }else if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_apagar_semana_albafer+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_apagar_semana_albafer+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
            case 2: //Empresa Tool Master
                if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_apagar_semana_tool_master+= $campos_contas_apagar[$i]['valores'];
                }else if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_apagar_semana_tool_master+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_apagar_semana_tool_master+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_euro']);
                }
            break;
            case 4: //Empresa Grupo
                if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 1) {//Real
                    $contas_apagar_semana_grupo+= $campos_contas_apagar[$i]['valores'];
                }else if((int)$campos_contas_apagar[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $contas_apagar_semana_grupo+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_dolar']);
                }else {//Euro
                    $contas_apagar_semana_grupo+= ($campos_contas_apagar[$i]['valores'] * $GLOBALS['valor_euro']);
                }
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
    </tr>
    <tr class='linhanormal' align='right'>
        <td align='center'>
            <?=$semana;?>
        </td>
        <td align='center'>
            <?=data::datetodata($data1, '/').' até '.data::datetodata($data2, '/');?>
        </td>
        <td>
            <?='R$ '.number_format(round(round($contas_apagar_semana_albafer, 3), 2), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format(round(round($contas_apagar_semana_tool_master, 3), 2), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format(round(round($contas_apagar_semana_grupo, 3), 2), 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $contas_apagar_total = $contas_apagar_semana_albafer + $contas_apagar_semana_tool_master + $contas_apagar_semana_grupo;
            echo 'R$ '.number_format(round(round($contas_apagar_total, 3), 2), 2, ',', '.');
            $GLOBALS['contas_apagar_alba_ac']	= $GLOBALS['contas_apagar_alba_ac'] + $contas_apagar_semana_albafer;
            $GLOBALS['contas_apagar_tool_ac']	= $GLOBALS['contas_apagar_tool_ac'] + $contas_apagar_semana_tool_master;
            $GLOBALS['contas_apagar_grupo_ac']	= $GLOBALS['contas_apagar_grupo_ac'] + $contas_apagar_semana_grupo;
        ?>
        </td>
    </tr>
<?
}
?>
<html>
<head>
<title>.:: Relatório de Conta(s) à Pagar ::.</title>
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
    if (!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
            return false
    }
    var data_inicial 	= document.form.txt_data_inicial.value
    var data_final 	= document.form.txt_data_final.value
    data_inicial 	= data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final 		= data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial 	= eval(data_inicial)
    data_final 		= eval(data_final)
	
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
            Relatório de Conta(s) à Pagar
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
    $campos                                     = bancos::sql($sql);
    $valor_dolar                                = $campos[0]['valor_dolar_dia'];
    $valor_euro                                 = $campos[0]['valor_euro_dia'];
    
    //Primeiro Intervalo de semana ...
    $intervalo_semana                           = data::intervalo_semana($txt_data_inicial);
    $intervalo_semana_data_inicial              = data::datatodate($intervalo_semana[0], '-');
    $intervalo_semana_data_final                = data::datatodate($intervalo_semana[1], '-');
    $intervalo_semana_data_inicial_anterior     = data::datatodate(data::adicionar_data_hora($intervalo_semana[0], -1), '-');
	
    //Último Intervalo de semana ...
    $intervalo_semana2                          = data::intervalo_semana($txt_data_final);
    $intervalo_semana_data_inicial2             = data::datatodate($intervalo_semana2[0], '-');
    $intervalo_semana_data_final2               = data::datatodate($intervalo_semana2[1], '-');
    $condicao                                   = " `data_vencimento` BETWEEN '$intervalo_semana_data_inicial' AND '$intervalo_semana_data_final2' ";
    $condicao_anterior                          = " `data_vencimento` < '$intervalo_semana_data_inicial_anterior' ";
        
    //Pego o valor total pela empresa e o tipo de moeda, que estejam com Marcação de Urgente ...
    $sql = "SELECT SUM(`valor` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
            FROM `contas_apagares` 
            WHERE `ativo` = '1' 
            AND `urgente` = 'S' 
            AND `status` IN (0, 1) 
            AND $condicao GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
    $campos_soma_moeda = bancos::sql($sql);
    $linhas_soma_moeda = count($campos_soma_moeda);
    for($i = 0; $i < $linhas_soma_moeda; $i++) {
        $exibir = 1;//para exibir na tela ...
        switch((int)$campos_soma_moeda[$i]['id_empresa']) {
            case 1: //Empresa Albafer
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_albafer+= $campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_albafer+= ($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_albafer+= ($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
            case 2: //Empresa Tool Master
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_tool+= $campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_tool+= ($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_tool+= ($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
            case 4: //Empresa Grupo
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
    //Pego o valor total pela empresa e o tipo de moeda mas com o calculo de cheque pré ...
    $sql = "SELECT SUM(`valor`) AS valores, `id_empresa`, `id_tipo_moeda` 
            FROM `contas_apagares` 
            WHERE `ativo` = '1' 
            AND `urgente` = 'S' 
            AND (`status` = '2' AND `predatado` = '1') 
            AND $condicao GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
    $campos_soma_moeda = bancos::sql($sql);
    $linhas_soma_moeda = count($campos_soma_moeda);
    for($i = 0; $i < $linhas_soma_moeda; $i++) {
        $exibir = 1;//para exibir na tela ...
        switch((int)$campos_soma_moeda[$i]['id_empresa']) {
            case 1: //Empresa Albafer
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_albafer+= $campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_albafer+= ($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_albafer+= ($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
            case 2: //Empresa Tool Master
                if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 1) {//Real
                    $valor_total_tool+= $campos_soma_moeda[$i]['valores'];
                }else if((int)$campos_soma_moeda[$i]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_total_tool+= ($campos_soma_moeda[$i]['valores'] * $valor_dolar);
                }else {//Euro
                    $valor_total_tool+= ($campos_soma_moeda[$i]['valores'] * $valor_euro);
                }
            break;
            case 4: //Empresa Grupo
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
    if(empty($exibir)) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='erro'>
        <td>
            NÃO EXISTE(M) CONTA(S) À PAGAR NESSE INTERVALO DE DATAS
        </td>
    </tr>
</table>
<?
    }else {
        //Pego o valor total pela empresa e o tipo de moeda da semanas anteriores ...
        $sql = "SELECT SUM(`valor` - `valor_pago`) AS valores, `id_empresa`, `id_tipo_moeda` 
                FROM `contas_apagares` 
                WHERE `ativo` = '1' 
                AND `urgente` = 'S' 
                AND (`status` IN (0,1) OR (`status` = '2' AND `predatado` = '1')) 
                AND $condicao_anterior GROUP BY id_empresa, id_tipo_moeda ORDER BY id_empresa ";
        $campos_soma_moeda_anterior = bancos::sql($sql);
        $linhas_soma_moeda_anterior = count($campos_soma_moeda_anterior);
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
                    }else {//Euro
                        $valor_total_grupo_anterior+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_euro);
                    }
                break;
            }
        }
        //Pego o valor total pela empresa e o tipo de moeda da semanas anteriores mas com o cheque pré ...
        $sql = "SELECT SUM(`valor`) AS valores, `id_empresa`, `id_tipo_moeda` 
                FROM `contas_apagares` 
                WHERE `ativo` = '1' 
                AND `urgente` = 'S' 
                AND (`status` = '2' AND `predatado` = '1') 
                AND $condicao_anterior GROUP BY `id_empresa`, `id_tipo_moeda` ORDER BY `id_empresa` ";
        $campos_soma_moeda_anterior = bancos::sql($sql);
        $linhas_soma_moeda_anterior = count($campos_soma_moeda_anterior);
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
                    }else {//Euro
                        $valor_total_grupo_anterior+= ($campos_soma_moeda_anterior[$i]['valores'] * $valor_euro);
                    }
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
        <td>
            Albafer
        </td>
        <td>
            Tool Master
        </td>
        <td>
            Grupo
        </td>
        <td>
            Total
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td align='left'>
            Total por Empresa
        </td>
        <td align='center'>
            <?='Até '.data::datetodata($intervalo_semana_data_final2,'/');?>
        </td>
        <td align='right'>
        <? 
            //Esta Parte está add o valor total de cada empresa mais o valor total anterior de cada empresa ... 
            echo 'R$ '.number_format(round(round($valor_total_albafer + $valor_total_albafer_anterior, 3), 2), 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <?='R$ '.number_format(round(round($valor_total_tool + $valor_total_tool_anterior, 3), 2), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format(round(round($valor_total_grupo + $valor_total_grupo_anterior, 3), 2), 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $valor_total_empresas = $valor_total_albafer + $valor_total_tool + $valor_total_grupo;
            $valor_total_empresas_anterior = $valor_total_albafer_anterior + $valor_total_tool_anterior + $valor_total_grupo_anterior;
            echo 'R$ '.number_format(round(round($valor_total_empresas + $valor_total_empresas_anterior, 3), 2), 2, ',', '.');
        ?>
        </td>
    </tr>
    <tr class='linhanormaldestaque'>
        <td colspan='2' align='center'>
            <b>Semanas Anteriores / Antes de <?=data::datetodata($intervalo_semana_data_inicial_anterior, '/');?></b>
        </td>
        <td align='right'>
            <b><?='R$ '.number_format(round(round($valor_total_albafer_anterior, 3), 2), 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <b><?='R$ '.number_format(round(round($valor_total_tool_anterior, 3), 2), 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <b><?='R$ '.number_format(round(round($valor_total_grupo_anterior, 3), 2), 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <b><?='R$ '.number_format(round(round($valor_total_empresas_anterior, 3), 2), 2, ',', '.');?></b>
        </td>
    </tr>
<?
        $intervalo_semana   = data::intervalo_semana($txt_data_inicial);
        $data1              = data::datatodate($intervalo_semana[0], '-');
        $data2              = data::datatodate($intervalo_semana[1], '-');

        while($data1 < $intervalo_semana_data_final2) {
            listar_linhas($data1, $data2, $valor_dolar, $valor_euro);
            $data1 = data::datetodata($data1, '-');
            $data2 = data::datetodata($data2, '-');
            $data1 = data::adicionar_data_hora($data1, 7);
            $data2 = data::adicionar_data_hora($data2, 7);
            $data1 = data::datatodate($data1, '-');
            $data2 = data::datatodate($data2, '-');
        }
?>
    </tr>
    <tr class='linhadestaque'>
        <td>
            Total por Empresa
        </td>
        <td align='center'>
            <?=' Até '.data::datetodata($intervalo_semana_data_final2, '/');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format(round(round($contas_apagar_semana_alba_ac + $valor_total_albafer_anterior, 3), 2), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format(round(round($contas_apagar_semana_tool_ac + $valor_total_tool_anterior, 3), 2), 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format(round(round($contas_apagar_semana_grupo_ac + $valor_total_grupo_anterior, 3), 2), 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $contas_apagar_total_ac = $contas_apagar_semana_alba_ac + $contas_apagar_semana_tool_ac + $contas_apagar_semana_grupo_ac;
            echo 'R$ '.number_format(round(round($contas_apagar_total_ac + $valor_total_empresas_anterior, 3), 2), 2, ',', '.');
        ?>
        </td>
    </tr>
<?
    }
}
?>