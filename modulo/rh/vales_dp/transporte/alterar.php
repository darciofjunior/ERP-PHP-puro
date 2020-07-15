<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>VALE TRANSPORTE ALTERADO COM SUCESSO.</font>";

if(!empty($_POST['id_vale_dp'])) {
//Alterando o Vale na Tabela ...
    $sql = "UPDATE `vales_dps` SET `valor` = '$_POST[txt_vlr_vale]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_vale_dp` = '$_POST[id_vale_dp]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

$id_vale_dp = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_vale_dp'] : $_GET['id_vale_dp'];

//Busca dados de vale atrav�s do id_vale_dp passado por par�metro ...
$sql = "SELECT * 
        FROM `vales_dps` 
        WHERE `id_vale_dp` = '$id_vale_dp' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Vale Transporte ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Valor 6% VT PD
    if(!texto('form', 'txt_vlr_vale', '1', '1234567890,.', '6% VT PD', '2')) {
        return false
    }
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
    atualizar_abaixo()
//Habilito a caixa p/ poder gravar no Banco ...
    return limpeza_moeda('form', 'txt_vlr_vale, ')
}

function atualizar() {
    document.form.passo.value = 0
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) opener.location = opener.location.href
}
</Script>
</head>
<body onload='document.form.txt_vlr_vale.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Aqui eu renomeio essa vari�vel $id_funcionario para $id_funcionario_loop para n�o dar conflito com 
a vari�vel da Sess�o "$id_funcionario"-->
<input type='hidden' name='id_funcionario_loop' value='<?=$campos[0]['id_funcionario'];?>'>
<input type='hidden' name='id_vale_dp' value='<?=$id_vale_dp;?>'>
<input type='hidden' name='nao_atualizar'>
<!--Esse hidden � um controle de Tela-->
<input type='hidden' name='passo' onclick="atualizar()">
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Vale Transporte
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Funcion�rio:</b>
        </td>
        <td>
        <?
//Busca de alguns dados do cadastro de Funcion�rio ...
            $sql = "SELECT id_empresa, tipo_salario, salario_pd, salario_pf, salario_premio, nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[0]['id_funcionario']."' LIMIT 1 ";
            $campos_dados_gerais = bancos::sql($sql);
            echo $campos_dados_gerais[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <?=genericas::nome_empresa($campos_dados_gerais[0]['id_empresa']);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emiss�o:</b>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_emissao'], '/');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Holerith:</b>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_debito'], '/');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Descontar:</b>
        </td>
        <td>
            <font color="darkblue">
                <b><?=$campos[0]['descontar_pd_pf'];?><b>
            </font>
        </td>
    </tr>
<?
//Busca da Qtde de Dias p/ Pgto. de Passes, que vai estar sendo utilizado + abaixo p/ os c�lculos em PHP ...
	$sql = "SELECT id_vale_data, qtde_dias_passes 
		FROM `vales_datas` 
		WHERE `data` = '".$campos[0]['data_debito']."' LIMIT 1 ";
	$campos_data_hol    = bancos::sql($sql);
	$id_vale_data       = $campos_data_hol[0]['id_vale_data'];
	$qtde_dias_passes   = $campos_data_hol[0]['qtde_dias_passes'];
//Aqui eu busco a Comiss�o do Funcion�rio referente ao M�s Corrente da Data de Holerith ...
	$sql = "SELECT comissao_alba, comissao_tool, comissao_grupo, dsr_alba, dsr_tool, dsr_grupo 
		FROM `funcionarios_vs_holeriths` 
		WHERE `id_funcionario` = '".$campos[0]['id_funcionario']."' 
		AND `id_vale_data` = '$id_vale_data' ";
	$campos_com_dsr = bancos::sql($sql);
	if(count($campos_com_dsr) == 1) {//Se encontrar alguma ...
            if($campos_dados_gerais[0]['id_empresa'] == 1) {//Se a Empresa = Albafer ...
                $comissao_pd = $campos_com_dsr[0]['comissao_alba'];
                $comissao_pf = $campos_com_dsr[0]['comissao_tool'] + $campos_com_dsr[0]['comissao_grupo'];
                $dsr_pd = $campos_com_dsr[0]['dsr_alba'];
                $dsr_pf = $campos_com_dsr[0]['dsr_tool'] + $campos_com_dsr[0]['dsr_grupo'];
            }else if($campos_dados_gerais[0]['id_empresa'] == 2) {//Se a Empresa = Tool Master ...
                $comissao_pd = $campos_com_dsr[0]['comissao_tool'];
                $comissao_pf = $campos_com_dsr[0]['comissao_alba'] + $campos_com_dsr[0]['comissao_grupo'];
                $dsr_pd = $campos_com_dsr[0]['dsr_tool'];
                $dsr_pf = $campos_com_dsr[0]['dsr_alba'] + $campos_com_dsr[0]['dsr_grupo'];
            }else if($campos_dados_gerais[0]['id_empresa'] == 4) {//Se a Empresa = Grupo ...
                $comissao_pd = 0;//N�o � Registrado, ah !! n�o tem carteira assinada, sem direito ...
                $comissao_pf = $campos_com_dsr[0]['comissao_alba'] + $campos_com_dsr[0]['comissao_tool'] + $campos_com_dsr[0]['comissao_grupo'];
                $dsr_pd = 0;//N�o � Registrado, ah !! n�o tem carteira assinada, sem direito ...
                $dsr_pf = $campos_com_dsr[0]['dsr_alba'] + $campos_com_dsr[0]['dsr_tool'] + $campos_com_dsr[0]['dsr_grupo'];
            }
	}else {//N�o encontrou comiss�o nenhuma p/ o Funcion�rio ...
            $comissao_pd = 0;
            $comissao_pf = 0;
            $dsr_pd = 0;
            $dsr_pf = 0;
	}
//C�lculo do Sal�rio PD ...
	if($campos_dados_gerais[0]['tipo_salario'] == 1) {//Horista
            $vlr_salario_pd = 220 * $campos_dados_gerais[0]['salario_pd'];
	}else {//Mensalista
            $vlr_salario_pd = $campos_dados_gerais[0]['salario_pd'];
	}
//C�lculo do Sal�rio PF ...
	if($campos_dados_gerais[0]['tipo_salario'] == 1) {//Horista
            $vlr_salario_pf = 220 * ($campos_dados_gerais[0]['salario_pf'] + $campos_dados_gerais[0]['salario_premio']);
	}else {//Mensalista
            $vlr_salario_pf = ($campos_dados_gerais[0]['salario_pf'] + $campos_dados_gerais[0]['salario_premio']);
	}
//Ir� mostrar em titles, quando o funcion�rio tiver comiss�o ...
	$vlr_salario_pd_title = $vlr_salario_pd;
	$vlr_salario_pf_title = $vlr_salario_pf;
/*Junto do sal�rio, eu somo o valor das comiss�es do Vendedor Tamb�m referente ao M�s Corrente 
da Data de Holerith ...*/
	$vlr_salario_pd+= $comissao_pd;
	$vlr_salario_pf+= $comissao_pf;
//Se a Empresa = Grupo, s� se recebe no PF ...
	if($campos_dados_gerais[0]['id_empresa'] == 4) {
//6 % do Sal�rio PD + 6 % do Sal�rio PF ...
            $seis_perc_salario_pd = 0;
            $seis_perc_salario_pf = (0.06 * $vlr_salario_pd) + (0.06 * $vlr_salario_pf);
	}else {
//6 % do Sal�rio PD e 6 % do Sal�rio PF ...
            $seis_perc_salario_pd = 0.06 * $vlr_salario_pd;
            $seis_perc_salario_pf = 0.06 * $vlr_salario_pf;
	}
?>
    <tr class='linhanormal'>
        <td>
            <b>Sal PD:</b>
        </td>
        <td>
            <input type='text' name='txt_salario_pd' value="<?=number_format($vlr_salario_pd, 2, ',', '.');?>" size='12' maxlength='10' class='textdisabled' disabled>
            <?
//Se existir comiss�o p/ o Funcion�rio "Vendedor", ent�o eu apresento est� na Tela ...
                if($comissao_pd != 0) echo '<font title="Sal�rio PD => R$ '.number_format($vlr_salario_pd_title, 2, ',', '.').' - Comiss�o PD => R$ '.number_format($comissao_pd, 2, ',', '.').'" style="cursor:help"><b>* Obs</b>';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Sal PF + Pr�mio:</b>
        </td>
        <td>
            <input type='text' name='txt_salario_pf' value="<?=number_format($vlr_salario_pf, 2, ',', '.');?>" size="12" maxlength="10" class='textdisabled' disabled>
            <?
//Se existir comiss�o p/ o Funcion�rio "Vendedor", ent�o eu apresento est� na Tela ...
                if($comissao_pf != 0) echo '<font title="Sal�rio PF => R$ '.number_format($vlr_salario_pf_title, 2, ',', '.').' - Comiss�o PF => R$ '.number_format($comissao_pf, 2, ',', '.').'" style="cursor:help"><b>* Obs</b>';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>6% VT PD:</b>
        </td>
        <td>
            <input type='text' name='txt_6_vlr_salario_pd' value="<?=number_format($seis_perc_salario_pd, 2, ',', '.');?>" title='6% VT PD' size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>6% VT PF:</b>
        </td>
        <td>
            <input type='text' name='txt_6_vlr_salario_pf' value="<?=number_format($seis_perc_salario_pf, 2, ',', '.');?>" title='6% VT PF' size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor do Vale:</b>
        </td>
        <td>
            <input type='text' name='txt_vlr_vale' value="<?=number_format($campos[0]['valor'], 2, ',', '.');?>" title='6% VT <?=$rotulo;?> no Vale' size="12" maxlength="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_vlr_vale.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>