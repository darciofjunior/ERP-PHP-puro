<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/ops/controle_processo/controle_processo.php', '../../../../');

if(!empty($_POST['txt_hora_final'])) {
    $data_sys   = date('Y-m-d H:i:s');
    $data_final = data::datatodate($_POST['txt_data_final'], '-');
	
    $sql = "UPDATE `ops_vs_processos` SET `data_final` = '$data_final', `hora_final` = '$_POST[txt_hora_final]', `qtde_produzida` = '$_POST[txt_qtde_produzida]', `data_sys` = '$data_sys' WHERE id_op_processo = '$_POST[hdd_op_processo]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('PROCESSO ALTERADO COM SUCESSO !')
        parent.location = 'controle_processo.php?passo=2&id_op=<?=$_POST['hdd_op'];?>'
    </Script>
<?
}

//Busco dados da Operação da OP passado por parâmetro ...
$sql = "SELECT op.*, f.`nome` AS funcionario, m.`nome`, CONCAT(mcm.`codigo_maquina`, ' - ', m.`caracteristica`) AS dados, mo.`operacao` 
        FROM `ops_vs_processos` op 
        INNER JOIN `maquinas` m ON m.`id_maquina` = op.`id_maquina` 
        INNER JOIN `maquinas_vs_codigos_maquinas` mcm ON mcm.`id_maquina_codigo_maquina` = op.`id_maquina_codigo_maquina` 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = op.`id_funcionario` 
        INNER JOIN `maquinas_vs_operacoes` mo ON mo.`id_maquina_operacao` = op.`id_maquina_operacao` 
        WHERE op.`id_op_processo` = '$_GET[id_op_processo]' LIMIT 1 ";
$campos	= bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Processo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Final ...
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
//Hora Final ...
    if(!texto('form', 'txt_hora_final', '3', '0123456789:', 'HORA FINAL', '1')) {
        return false
    }
//Quantidade Produzida ...
    if(document.form.txt_qtde_produzida.value != '') {
        if(!texto('form', 'txt_qtde_produzida', '1', '0123456789', 'QUANTIDADE PRODUZIDA', '1')) {
            return false
        }
    }
}
</Script>
<body onload="document.form.txt_data_processo.focus()">
<form name="form" method="post" action="" onsubmit="return validar()">
<!--**********************Controle de Tela**********************-->
<input type='hidden' name="hdd_op" value="<?=$campos[0]['id_op'];?>">
<input type='hidden' name="hdd_op_processo" value="<?=$_GET['id_op_processo'];?>">
<!--************************************************************-->
<table width='90%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            Alterar Processo da OP N.º
            <font color="yellow">
                <?=$campos[0]['id_op'];?>
            </font> 
        </td>
    </tr>
    <tr class='linhanormal' align="left">
            <td>
                    Máquina:
            </td>
            <td>
                    <input type='text' value="<?=$campos[0]['nome'];?>" class='textdisabled' size="50" disabled>
            </td>
    </tr>
    <tr class='linhanormal' align="left">	
            <td>
                    Código(s) da Máquina:
            </td>
            <td>	
                    <input type='text' value="<?=$campos[0]['dados'];?>" class='textdisabled' size="50" disabled>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Funcionário(s) da Máquina:
            </td>
            <td>
                    <input type='text' value="<?=$campos[0]['funcionario'];?>" class='textdisabled' size="50" disabled>
            </td>
    </tr>
    <tr class='linhanormal' align="left">	
            <td>
                    Operação(ões) da Máquina
            </td>
            <td>	
                    <input type='text' value="<?=$campos[0]['operacao'];?>" class='textdisabled' size="50" disabled>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Data Inicial:
            </td>
            <td>
                    <input type='text' name="txt_data_inicial" value="<?=data::datetodata($campos[0]['data_inicial'], '/');?>" title="Data Inicial" size="12" maxlength="10" class='textdisabled' disabled>
            </td>
    </tr>
    <tr class='linhanormal' align="left">
            <td>
                    Hora Inicial:
            </td>
            <td>	
                    <input type='text' value="<?=$campos[0]['hora_inicial'];?>" class='textdisabled' disabled>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Data Final:</b>
            </td>
            <td>
                    <?$data_final = ($campos[0]['data_final'] != '0000-00-00') ? data::datetodata($campos[0]['data_final'], '/') : date('d/m/Y');?>
                    <input type='text' name="txt_data_final" value="<?=$data_final;?>" title="Digite a Data Final" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class='caixadetexto'>
                    <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_processo&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            </td>
    </tr>
    <tr class='linhanormal' align="left">
        <td>
            <b>Hora Final: </b>
        </td>
        <td>
            <?$hora_final = ($campos[0]['hora_final'] != '00:00:00') ? substr($campos[0]['hora_final'], 0, 5) : date('H:i');?>
            <input type='text' name="txt_hora_final" value="<?=$hora_final;?>" size="7" maxlength="5" title='Digite a Hora Final' onkeyup="verifica(this, 'hora', '', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal' align="left">	
        <td>
            Qtde Produzida: 
        </td>
        <td>
            <?$qtde_produzida = ($campos[0]['qtde_produzida'] > 0) ? $campos[0]['qtde_produzida'] : '';?>
            <input type='text' name="txt_qtde_produzida" value="<?=$qtde_produzida;?>" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" maxlength="10" size="12" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" style="color:#ff9900;" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_data_processo.focus()" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>