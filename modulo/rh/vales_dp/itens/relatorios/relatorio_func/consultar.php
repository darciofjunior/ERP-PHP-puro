<?
require('../../../../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../../../');
?>
<html>
<head>
<title>.:: Imprimir Folha de Pagamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
/*Aqui eu forço o usuário a selecionar uma Data de Holerith porque o relatório de vales dos Funcionários 
é baseado nessa Data de Holerith*/
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE UMA DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }
/*Aqui eu forço o usuário a selecionar o Descontar PD e PF porque o relatório de vales dos Funcionários 
também é baseado nesse Descontar PD e PF*/
    if(document.form.opt_descontar[0].checked == false && document.form.opt_descontar[1].checked == false) {
        alert('SELECIONE O DESCONTAR PD PF !')
        document.form.opt_descontar[0].focus()
        return false
    }
    var cmb_data_holerith   = document.form.cmb_data_holerith.value
//Aqui eu verifico qual o option da opção Descontar que foi selecionado ...
    var opt_descontar       = (document.form.opt_descontar[0].checked == true) ? 'PD' : 'PF'
    nova_janela('relatorio.php?cmb_data_holerith='+cmb_data_holerith+'&opt_descontar='+opt_descontar, 'CONSULTAR', 'F')
}

function tipos_vales() {
    var cmb_data_holerith   = document.form.cmb_data_holerith.value
//Aqui eu verifico qual o option da opção Descontar que foi selecionado ...
    if(document.form.opt_descontar[0].checked == true) {
        var opt_descontar = 'PD'
    }else if(document.form.opt_descontar[1].checked == true) {
        var opt_descontar = 'PF'
    }else {
        var opt_descontar = ''
    }
    ajax('tipos_vales.php?cmb_data_holerith='+cmb_data_holerith+'&opt_descontar='+opt_descontar, 'div_tipos_vales')
    /*Dou esse tempinho de 0,4 segundo p/ chamar essa função porque o Ajax leva um tempinho pra carregar 
    o hidden hdd_travar_botao que me servirá de controle p/ travar o Botão cmd_avancar dessa Tela ...*/
    setTimeout('avancar()', 400)
}

function avancar() {
    if(parent.document.form.hdd_travar_botao.value == 'S') {//Faltou algum registro de 1 Vale Importante, então travo o botão de Avançar ...
        document.form.cmd_avancar.disabled  = true
        document.form.cmd_avancar.className = 'textdisabled'
    }else {//Todos registros de Vales Importantes estão preenchidos, então habilito o botão p/ Avançar normalmente ...
        document.form.cmd_avancar.disabled  = false
        document.form.cmd_avancar.className = 'botao'
    }
}
</Script>
</head>
<body onload='tipos_vales()'>
<form name='form'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Imprimir Folha de Pagamento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Holerith:</b>
        </td>
        <td>
            <select name='cmb_data_holerith' title='Selecione a Data de Holerith' onchange='tipos_vales()' class='combo'>
            <?
                //Aqui eu pego a próxima Data de Holerith posterior à Data Atual ...
                $sql = "SELECT `data` 
                        FROM `vales_datas` 
                        WHERE `data` >= '".date('Y-m-d')."' LIMIT 1 ";
                $campos_vale_data = bancos::sql($sql);
                
                //Busco todos os Períodos de Data de Holerith cadastrados no Sistema ...
                $sql = "SELECT `data`, DATE_FORMAT(`data`, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        ORDER BY `data` ";
                echo combos::combo($sql, $campos_vale_data[0]['data']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Descontar:</b>
        </td>
        <td>
            <input type='radio' name='opt_descontar' id='descontar1' value='PD' title='Selecione o Descontar' onchange='tipos_vales()'>
            <label for='descontar1'>PD</label>
            &nbsp;
            <input type='radio' name='opt_descontar' id='descontar2' value='PF' title='Selecione o Descontar' onchange='tipos_vales()'>
            <label for='descontar2'>PF</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='validar()' class='botao'>
<?
//Significa que esta tela foi aberta como sendo Pop-Up ...
    if($pop_up == 1) {
?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
<?
    }
?>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <div id='div_tipos_vales'></div>
        </td>
    </tr>
</table>
</form>
</body>
</html>