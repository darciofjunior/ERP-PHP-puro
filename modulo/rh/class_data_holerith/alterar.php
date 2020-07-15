<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../');

$mensagem[1] = 'DATA DE HOLERITH INVÁLIDA !\nDATA DE HOLERITH MENOR DO QUE A DATA ATUAL !!!';
$mensagem[2] = 'DATA DE HOLERITH ALTERADA COM SUCESSO !';
$mensagem[3] = 'DATA DE HOLERITH JÁ EXISTENTE !';

if(!empty($_POST['txt_data_holerith'])) {
//Tratamento com os campos p/ poder gravar no BD ...
    $txt_data_holerith  = data::datatodate($_POST['txt_data_holerith'], '-');
/*Aqui eu verifico se a Data de Holerith que está sendo cadastrada, não é uma Data de Holerith com um 
Valor muito ultrapassado*/
    $data_atual         = date('Ymd');
    $data_cadastrada    = str_replace('-', '', $txt_data_holerith);
//Data de Holerith com uma Da
    /*if($data_cadastrada < $data_atual) {
        $valor = 1;
    }else {*/
        //Verifico se essa Data de Holerith já foi existe ...
        $sql = "SELECT `id_vale_data` 
                FROM `vales_datas` 
                WHERE `data` = '$txt_data_holerith' 
                AND `id_vale_data` <> '$_POST[id_vale_data]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {//Data de Holerith ainda não foi cadastrada ...
//1) Se houve alguma alteração no campo 'Qtde de Horas Trabalhadas' ...
            if($_POST['hdd_alterar_qtde_hrs_trabalhadas'] == 1) {
//ALTERA A QTDE DE HORA(S) TRABALHADA(S) NA FOLHA P/ TODO(S) O(S) HORISTA(S) ...
                $sql = "UPDATE `funcionarios_vs_holeriths` fh 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = fh.`id_funcionario` AND f.tipo_salario = '1' 
                        SET fh.dias_horas_trabalhadas = '$_POST[txt_qtde_horas_trabalhadas]' WHERE `id_vale_data` = '$_POST[id_vale_data]' ";
                bancos::sql($sql);
            }
//2) Se houve alguma alteração no campo 'Qtde H. Extras Feriado ao Sábado' ...
            if($_POST['hdd_alterar_qtde_h_ext_fer_sab'] == 1) {
                $sql = "UPDATE `funcionarios_vs_holeriths` SET `hora_extra` = '$_POST[txt_qtde_h_ext_fer_sab]' WHERE id_vale_data = '$_POST[id_vale_data]' ";
                bancos::sql($sql);
            }
/******Tratamento c/ todos os vales q utilizam a Data de Holerith antes da alteração******/
//1) Aqui eu busco a Data de Holerith atual antes da alteração ...
            $sql = "SELECT `data` 
                    FROM `vales_datas` 
                    WHERE `id_vale_data` = '$_POST[id_vale_data]' LIMIT 1 ";
            $campos             = bancos::sql($sql);
            $data_debito_atual  = $campos[0]['data'];
//2) Atualizando Todos os Vales q utilizam essa Data de Holerith atual p/ a Nova Data de Holerith ...
            $sql = "UPDATE `vales_dps` SET `data_debito` = '$txt_data_holerith' WHERE `data_debito` = '$data_debito_atual' ";
            bancos::sql($sql);
//3) Gravando a Nova Data de Holerith na Tabela de Datas de Holerith ...
            $txt_data_emissao_nf_convenio = (!empty($_POST['txt_data_emissao_nf_convenio'])) ? data::datatodate($_POST['txt_data_emissao_nf_convenio'], '-') : '';
            
            $sql = "UPDATE `vales_datas` SET `data` = '$txt_data_holerith', `qtde_dias_passes` = '$_POST[txt_qtde_dias_pgto_passes]', `qtde_hrs_trabalhadas` = '$_POST[txt_qtde_horas_trabalhadas]', `qtde_dias_trabalhados` = '$_POST[txt_qtde_dias_trabalhados]', `qtde_dias_uteis_mes` = '$_POST[txt_qtde_dias_uteis_mes]', `qtde_dias_inuteis_mes` = '$_POST[txt_domingos_feriados_mes]', `hora_extra` = '$_POST[txt_qtde_h_ext_fer_sab]', `total_faturamento` = '$_POST[txt_total_faturamento]', `data_emissao_nf_convenio` = '$txt_data_emissao_nf_convenio' WHERE `id_vale_data` = '$_POST[id_vale_data]' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }else {//Data de Holerith já cadastrada ...
            $valor = 3;
        }
    //}
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[$valor];?>')
        parent.document.form.passo.onclick()
        parent.fechar_pop_up_div()
    </Script>
<?
}

//Trago dados da Data do Holerith passada por parâmetro ...
$sql = "SELECT * 
        FROM `vales_datas` 
        WHERE `data` = '$_GET[data]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Data de Holerith ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data do Holerith
    if(!data('form', 'txt_data_holerith', "4000", 'HOLERITH')) {
        return false
    }
//Qtde de Dias p/ Pgto. de Passes
    if(!texto('form', 'txt_qtde_dias_pgto_passes', '1', '0123456789', 'QTDE DE DIA(S) P/ PAGAMENTO DE PASSES(S)', '1')) {
        return false
    }
//Qtde de Horas Trabalhadas
    if(!texto('form', 'txt_qtde_horas_trabalhadas', '1', '0123456789:', 'QTDE DE HORA(S) TRABALHADA(S)', '1')) {
        return false
    }
//Qtde de Dias Trabalhados
    if(!texto('form', 'txt_qtde_dias_trabalhados', '1', '0123456789', 'QTDE DE DIA(S) TRABALHADO(S)', '1')) {
        return false
    }
//Qtde de Dias Úteis por Mês
    if(!texto('form', 'txt_qtde_dias_uteis_mes', '1', '0123456789', 'QTDE DE DIA(S) ÚTEL(IS) POR MÊS', '1')) {
        return false
    }
//Domingos e Feriados por Mês
    if(!texto('form', 'txt_domingos_feriados_mes', '1', '0123456789', 'DOMINGO(S) E FERIADO(S) POR MÊS', '2')) {
        return false
    }
//Qtde H. Extras Feriado ao Sábado
    if(document.form.txt_qtde_h_ext_fer_sab.value != '') {
        if(!texto('form', 'txt_qtde_h_ext_fer_sab', '1', '0123456789:', 'QTDE DE H. EXTRA(S) FERIADO AO SÁBADO', '1')) {
            return false
        }
    }
//Total de Faturamento
    if(!texto('form', 'txt_total_faturamento', '4', '0123456789,.', 'TOTAL DE FATURAMENTO', '2')) {
        return false
    }
//Data de Emissão da NF do Convênio
    if(document.form.txt_data_emissao_nf_convenio.value != '') {
        if(!data('form', 'txt_data_emissao_nf_convenio', "4000", 'EMISSÃO DA NF DO CONVÊNIO')) {
            return false
        }
    }
/***************************************Verificação de Alteração***************************************/
//1) Verificação com o campo Qtde de Horas Trabalhadas ...
    qtde_hrs_trabalhadas_bd = '<?=number_format($campos[0]['qtde_hrs_trabalhadas'], 2, ':', '');?>'
    qtde_horas_trabalhadas = document.form.txt_qtde_horas_trabalhadas.value
//Verifico se houve alteração do campo "qtde_hrs_trabalhadas_bd" com o campo "qtde_hrs_trabalhadas" ...
    if(qtde_hrs_trabalhadas_bd != qtde_horas_trabalhadas) {
        resposta1 = confirm('DESEJA ALTERAR A QTDE DE HORA(S) TRABALHADA(S) NA FOLHA P/ TODO(S) O(S) HORISTA(S) ?')
        if(resposta1 == true) document.form.hdd_alterar_qtde_hrs_trabalhadas.value = 1
    }
//2) Verificação com o campo Qtde H. Extras Feriado ao Sábado ...
    qtde_h_ext_fer_sab_bd = '<?=number_format($campos[0]['hora_extra'], 2, ':', '');?>'
    qtde_h_ext_fer_sab = document.form.txt_qtde_h_ext_fer_sab.value
//Verifico se houve alteração do campo "qtde_h_ext_fer_sab_bd" com o campo "qtde_h_ext_fer_sab" ...
    if(qtde_h_ext_fer_sab_bd != qtde_h_ext_fer_sab) {
        resposta2 = confirm('DESEJA ALTERAR A QTDE DE HORA(S) EXTRA(S) FERIADO AO SÁBADO P/ TODA A FOLHA ?')
        if(resposta2 == true) document.form.hdd_alterar_qtde_h_ext_fer_sab.value = 1
    }
/******************************************************************************************************/
//Tratamento com o campo na hora de Gravar no BD ...
    document.form.txt_qtde_horas_trabalhadas.value = document.form.txt_qtde_horas_trabalhadas.value.replace(':', '.')
    document.form.txt_qtde_h_ext_fer_sab.value = document.form.txt_qtde_h_ext_fer_sab.value.replace(':', '.')

    limpeza_moeda('form', 'txt_total_faturamento, ')
//Desabilito p/ poder gravar no BD ...
    document.form.txt_qtde_dias_trabalhados.disabled = false
}

function calcular_meses() {
//Criei esse vetor p/ facilitar na hora de apresentação p/ o usuário ...
    var vetor_meses = new Array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro')

    if(document.form.txt_data_holerith.value.length == 10) {
        var mes_digitado = document.form.txt_data_holerith.value.substr(3, 2)
        var hum_mes_anterior = mes_digitado - 1
        var dois_meses_anterior = mes_digitado - 2
//Tratamento p/ não dar erro na hora de apresentar p/ o usuário ...
        if(hum_mes_anterior == 0) {//Não existe esse mês (rs), então ...
            hum_mes_anterior = 12//Vira dezembro ...
        }else if(hum_mes_anterior == -1) {
            hum_mes_anterior = 11//Vira novembro ...
        }
        if(dois_meses_anterior == 0) {//Não existe esse mês (rs), então ...
            dois_meses_anterior = 12//Vira dezembro ...
        }else if(dois_meses_anterior == -1) {
            dois_meses_anterior = 11//Vira novembro ...
        }
//Montagem do Texto do Período
        var periodo = 'Os campos abaixo serão contados do dia 26 de '+vetor_meses[dois_meses_anterior]+' ao dia 25 de '+vetor_meses[hum_mes_anterior]+', igual ao Faturamento.'
    }else {
        var periodo = 'Digite uma Data de Holerith para exibir o período de Faturamento.'
    }
    document.form.txt_periodo.value = periodo
}
</Script>
</head>
<body onload='calcular_meses();document.form.txt_data_holerith.focus()'>
<form name="form" method="post" action='' onsubmit='return validar()'>
<!--*****************Controles de Tela*****************-->
<input type='hidden' name='id_vale_data' value="<?=$campos[0]['id_vale_data'];?>">
<input type='hidden' name='hdd_alterar_qtde_hrs_trabalhadas'>
<input type='hidden' name='hdd_alterar_qtde_h_ext_fer_sab'>
<input type='hidden' name='calcular_automatico' onclick='calcular_meses()'>
<!--***************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Data de Holerith
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            <b>Data de Holerith:</b>
        </td>
        <td>
            <input type='text' name='txt_data_holerith' value='<?=data::datetodata($campos[0]['data'], '/');?>' title='Digite a Data de Holerith' onkeyup="verifica(this, 'data', '', '', event)" onblur="calcular_meses()" size='12' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_holerith&tipo_retorno=1&caixa_auxiliar=calcular_automatico', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_periodo' size='120' style='color:green' class='caixadetexto2' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Dias p/ Pgto. de Passes:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_dias_pgto_passes' value='<?=$campos[0]['qtde_dias_passes'];?>' title='Digite a Qtde de Dias p/ Pgto. de Passes' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Hs / Min Trabalhados:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_horas_trabalhadas' value='<?=number_format($campos[0]['qtde_hrs_trabalhadas'], 2, ':', '');?>' title='Digite a Qtde de Horas Trabalhadas' onkeyup="verifica(this, 'hora', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Dias Trabalhados:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_dias_trabalhados' value='<?=$campos[0]['qtde_dias_trabalhados'];?>' title='Qtde de Dias Trabalhados' size='6' maxlength='5' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Dias Úteis por Mês:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_dias_uteis_mes' value='<?=$campos[0]['qtde_dias_uteis_mes'];?>' title='Digite a Qtde de Dias Úteis por Mês' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Domingos e Feriados por Mês:</b>
        </td>
        <td>
            <input type='text' name='txt_domingos_feriados_mes' value='<?=$campos[0]['qtde_dias_inuteis_mes'];?>' title='Digite os Domingos e Feriados por Mês' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtde H. Extras Feriado ao Sábado:
        </td>
        <td>
            <input type='text' name='txt_qtde_h_ext_fer_sab' value='<?=number_format($campos[0]['hora_extra'], 2, ':', '');?>' title='Digite a Qtde H. Extras (Feriado ao Sábado)' size='7' maxlength='6' onkeyup="verifica(this, 'hora', '', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Total de Faturamento:</b>
        </td>
        <td>
            <input type='text' name='txt_total_faturamento' value="<?=number_format($campos[0]['total_faturamento'], 2, ',', '.');?>" title='Digite o Total de Faturamento' size='15' maxlength='13' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emissão da NF do Convênio:
        </td>
        <td>
            <?
                $data_emissao_nf_convenio = ($campos[0]['data_emissao_nf_convenio'] != '0000-00-00') ? data::datetodata($campos[0]['data_emissao_nf_convenio'], '/') : '';
            ?>
            <input type='text' name='txt_data_emissao_nf_convenio' value='<?=$data_emissao_nf_convenio;?>' title='Digite a Data de Emissão da NF do Convênio' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao_nf_convenio&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_data_holerith.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>