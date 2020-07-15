<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) CHEQUE(S) NESSA CONDIÇÃO.</font>";
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
    if(document.form.txt_data_vencimento_inicial.value != '') {
        if(!data('form', 'txt_data_vencimento_inicial', '4000', 'VENCIMENTO INICIAL')) {
            return false
        }
        if(!data('form', 'txt_data_vencimento_final', '4000', 'VENCIMENTO FINAL')) {
            return false
        }
    }

    if(document.form.txt_data_vencimento_final.value != '') {
        if(!data('form', 'txt_data_vencimento_inicial', '4000', 'VENCIMENTO INICIAL')) {
            return false
        }
        if(!data('form', 'txt_data_vencimento_final', '4000', 'VENCIMENTO FINAL')) {
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_data_vencimento_inicial.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Relatório de Cheque(s)
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='8'>
            Data Venc Inicial:
            <input type='text' name='txt_data_vencimento_inicial' value='<?=$data_inicial;?>' title='Data Inicial' size='11' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_vencimento_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;
            Data Venc Final:
            <input type='text' name='txt_data_vencimento_final' value='<?=$data_final;?>' title='Data Final' size='11' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_vencimento_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;
            Condição:
            <select name="cmb_status" title="Selecione a Condição" class="combo">
            <?
                if($cmb_status == 1) {
                    $selected1 = 'selected';
                }else if($cmb_status == 2) {
                    $selected2 = 'selected';
                }else if($cmb_status == 3) {
                    $selected3 = 'selected';
                }
            ?>
                <option value='1' <?=$selected1;?>>ABERTO (VALOR DISPONÍVEL)</option>
                <option value='2' <?=$selected2;?>>TERCEIROS</option>
                <option value='3' <?=$selected3;?>>CONCLUÍDO / COMPENSADO</option>
            </select>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
//Aki todos os Cheques das Datas, Cheque e Banco Selecionados
if(!empty($cmb_status)) {
    if($cmb_status == 1) {
        $condicao = " AND cc.`status_disponivel` = '1' ";//Significa que esse cheque ainda tem valor em aberto ...
    }else if($cmb_status == 2) {
        $condicao = " AND cc.`status_disponivel` = '2' AND cc.`status` = '1' ";//Significa que esse cheque não tem + valor em aberto
    }else if($cmb_status == 3) {
        $condicao = " AND cc.`status` = '2' ";//Significa que esse cheque já foi compensando ...
    }
//Foi preenchido as Datas de Vencimento
    if(!empty($txt_data_vencimento_inicial)) {
        $txt_data_vencimento_inicial    = data::datatodate($txt_data_vencimento_inicial, '-');
        $txt_data_vencimento_final      = data::datatodate($txt_data_vencimento_final, '-');
        $condicao.= " WHERE cc.`data_vencimento` BETWEEN '$txt_data_vencimento_inicial' AND '$txt_data_vencimento_final' ";
    }
//Seleção dos dados do cheque - aqui é genérico para os 3 tipos de casos
//Aqui traz todos os cheques que estão em abertos, específicos daquele cliente e daquela empresa
    $sql = "SELECT DISTINCT(cc.num_cheque), cc.*, c.razaosocial, e.nomefantasia 
            FROM `cheques_clientes` cc 
            INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
            INNER JOIN `empresas` e ON e.`id_empresa` = cc.`id_empresa` 
            $condicao ORDER BY cc.num_cheque ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <td>
        <td></td>
    </tr>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
        exit;
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            N.º Cheque
        </td>
        <td>
            Banco
        </td>
        <td>
            Cliente
        </td>
        <td>
            Correntista
        </td>
        <td>
            Valor Cheque
        </td>
        <td>
            Valor Disp
        </td>
        <td>
            Data de Venc
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href = '../../recebimento/cheque_cliente/classes/manipular/detalhes.php?id_cheque_cliente=<?=$campos[$i]['id_cheque_cliente'];?>&ignorar_sessao=1' class='html5lightbox'>
                <?=$campos[$i]['num_cheque'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['correntista'];?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor_disponivel'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho'>
        <td colspan='8'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?}?>
</body>
</html>