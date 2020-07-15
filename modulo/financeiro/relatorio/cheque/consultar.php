<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial       = $_POST['txt_data_inicial'];
    $txt_data_final         = $_POST['txt_data_final'];
    $cmb_status_cheque      = $_POST['cmb_status_cheque'];
    $cmb_banco              = $_POST['cmb_banco'];
    $cmb_agencia            = $_POST['cmb_agencia'];
    $cmb_conta_corrente     = $_POST['cmb_conta_corrente'];
    $hdd_exibir_relatorio   = $_POST['hdd_exibir_relatorio'];
}else {
    $txt_data_inicial       = $_GET['txt_data_inicial'];
    $txt_data_final         = $_GET['txt_data_final'];
    $cmb_status_cheque      = $_GET['cmb_status_cheque'];
    $cmb_banco              = $_GET['cmb_banco'];
    $cmb_agencia            = $_GET['cmb_agencia'];
    $cmb_conta_corrente     = $_GET['cmb_conta_corrente'];
    $hdd_exibir_relatorio   = $_GET['hdd_exibir_relatorio'];
}
?>
<html>
<head>
<title>.:: Relatório de Cheque(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial, Data Final ...
    if(document.form.txt_data_inicial.value != '' || document.form.txt_data_final.value != '') {
        if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
            return false
        }
        if(!data('form', 'txt_data_final', '4000', 'FIM')) {
            return false
        }
    }
//Banco ...
    if(!combo('form', 'cmb_banco', '', 'SELECIONE UM BANCO !')) {
        return false
    }else {
        if(!combo('form', 'cmb_agencia', '', 'SELECIONE UMA AGÊNCIA !')) {
            return false
        }else {
            if(!combo('form', 'cmb_conta_corrente', '', 'SELECIONE UMA CONTA CORRENTE !')) {
                return false
            }
        }
    }
    document.form.hdd_exibir_relatorio.value = 1
}
</Script>
</head>
<body onload='document.form.txt_data_inicial.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********************Controle de Tela**********************-->
<input type='hidden' name='hdd_exibir_relatorio'>
<!--************************************************************-->
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Relatório de Cheque(s)
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            <b>Data de Compensação Inicial:</b>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' title='Digite a Data Inicial' maxlength='10' size='12' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;
            <b>Data de Compensação Final:</b>
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' title='Digite a Data Final' maxlength='10' size='12' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;&nbsp;
            <b>Status de Cheque:</b>
            <select name='cmb_status_cheque' title='Selecione um Status de Cheque' class='combo'>
            <?
                if($cmb_status_cheque == 1) {
                    $selected1 = 'selected';
                }else if($cmb_status_cheque == 2) {
                    $selected2 = 'selected';
                }else if($cmb_status_cheque == 3) {
                    $selected3 = 'selected';
                }
            ?>    
                <option value='1' <?=$selected1;?>>ABERTO</option>
                <option value='2' <?=$selected2;?>>EMITIDO</option>
                <option value='3' <?=$selected3;?>>COMPENSADO</option>
            </select>
<?
/*******************************************************************************************************/
//Dados Dinâmicos ...
?>
            <br/>
            <hr>
            Banco:
            <select name='cmb_banco' title='Selecione o Banco' onchange='document.form.submit()' class='combo'>
            <?
                $sql = "SELECT id_banco, banco 
                        FROM `bancos` 
                        WHERE `ativo` = '1' ORDER BY banco ";
                echo combos::combo($sql, $cmb_banco);
            ?>
            </select>
<?
/*******************************************************************************************************/
        if($cmb_banco > 0) {
?>
            Agência:
            <select name='cmb_agencia' title='Selecione uma Agência' onchange='document.form.submit()' class='combo'>
            <?
                //Trago todas as Agências do Banco selecionado na Combo ...
                $sql = "SELECT id_agencia, nome_agencia 
                        FROM `agencias` 
                        WHERE `ativo` = '1' 
                        AND `id_banco` = '$cmb_banco' ORDER BY nome_agencia ";
                echo combos::combo($sql, $cmb_agencia);
            ?>
            </select>
<?
        }
/*******************************************************************************************************/
        if($cmb_agencia > 0) {
?>
            Conta Corrente:
            <select name='cmb_conta_corrente' title='Selecione uma Conta Corrente' class='combo'>
            <?
                //Trago todas as Contas Correntes da Agência selecionada na Combo ...
                $sql = "SELECT id_contacorrente, conta_corrente 
                        FROM `contas_correntes` 
                        WHERE `ativo` = '1' 
                        AND `id_agencia` = '$cmb_agencia' ORDER BY conta_corrente ";
                echo combos::combo($sql, $cmb_conta_corrente);
            ?>
            </select>
<?
        }
/*******************************************************************************************************/
?>
            <input type='submit' name='Pesquisar' value='Pesquisar' title='Pesquisar' class='botao'>
        </td>
    </tr>
<?
    if($hdd_exibir_relatorio == 1) {
        if(!empty($txt_data_inicial)) {
            $data_inicial   = data::datatodate($txt_data_inicial, '-');
            $data_final     = data::datatodate($txt_data_final, '-');
            $condicao       = " AND SUBSTRING(c.`data_compensacao`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' ";
        }
        //Trago todos os Cheques dentro da Situação que foi selecionada pelo Usuário ...
        $sql = "SELECT c.num_cheque, c.id_cheque, c.historico, c.valor, c.data_compensacao, f.nome 
                FROM `cheques` c 
                INNER JOIN `taloes` t ON t.`id_talao` = c.`id_talao` 
                INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` AND cc.`id_contacorrente` = '$cmb_conta_corrente' 
                INNER JOIN `funcionarios` f ON f.`id_funcionario` = c.`id_funcionario` 
                WHERE c.`status` = '$cmb_status_cheque' $condicao ";
        $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
	$linhas = count($campos);
	if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='5'>
            SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.
        </td>
    </tr>
<?
        }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            N.º Cheque
        </td>
        <td>
            Valor
        </td>
        <td>
            Data de Comp.
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Histórico
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
            if($cmb_status_cheque == 2 || $cmb_status_cheque == 3) {
        ?>
                <a href = '../../pagamento/cheque/classes/manipular/detalhes.php?id_cheque=<?=$campos[$i]['id_cheque'];?>' class='html5lightbox'>
        <?
            }
            echo $campos[$i]['num_cheque'];
        ?>
        </td>
        <td>
            <?='R$ '.number_format($campos[$i]['valor'],2,',','.');?>
        </td>
        <td>
        <?
            if(substr($campos[$i]['data_compensacao'], 0, 10) != '0000-00-00') {
                echo data::datetodata(substr($campos[$i]['data_compensacao'], 0, 10), '/');
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['historico'];?>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
    <tr align='center'>
        <td colspan='5'>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
<?
        }
    }
?>  
</table>
</form>
</body>
</html>