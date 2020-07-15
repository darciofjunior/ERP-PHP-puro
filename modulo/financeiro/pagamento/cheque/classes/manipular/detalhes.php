<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/cheque/albafer/index.php';
    $endereco_volta = 'albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/cheque/tool_master/index.php';
    $endereco_volta = 'tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/cheque/grupo/index.php';
    $endereco_volta = 'grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$sql = "SELECT * 
        FROM `cheques` 
        WHERE `id_cheque` = '$_GET[id_cheque]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Consultar Cheque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
</head>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar Cheque N.º 
            <font color='yellow'>
                <?=$campos[0]['num_cheque'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Histórico:</b>
        </td>
        <td colspan='4'>
            <?=$campos[0]['historico'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor:</b>
        </td>
        <td colspan='4'>
            <?='R$ '.number_format($campos[0]['valor'],2,',','.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Compensação:</b>
        </td>
        <td colspan='4'>
        <?
            if(substr($campos[0]['data_compensacao'],0,10) == '0000-00-00') {
                echo '&nbsp;';
            }else {
                echo data::datetodata($campos[0]['data_compensacao'],'/');
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Condição:</b>
        </td>
        <td colspan='4'>
        <?
            if($campos[0]['status'] == 0) {
                echo 'Aberto';
            }else if($campos[0]['status'] == 1) {
                echo 'Travado';
            }else if($campos[0]['status'] == 2) {
                echo 'Emitido';
            }else if($campos[0]['status'] == 3) {
                echo 'Compensado';
            }else if($campos[0]['status'] == 4) {
                echo 'Cancelado';
            }
//Aqui é para o caso de ser predatado
            if($campos[0]['predatado'] == 1) echo ' / Pré-datado';
        ?>
        </td>
    </tr>
<?
$sql = "SELECT ca.`id_conta_apagar`, ca.`numero_conta`, ca.`semana`, caq.`valor`, 
        DATE_FORMAT(caq.`data`, '%d/%m/%Y') AS bom_para, f.`razaosocial` 
        FROM `cheques` c 
        INNER JOIN `contas_apagares_quitacoes` caq ON caq.`id_cheque` = c.`id_cheque` 
        INNER JOIN `contas_apagares` ca ON ca.`id_conta_apagar` = caq.`id_conta_apagar` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = ca.`id_fornecedor` 
        WHERE c.id_cheque = '$_GET[id_cheque]' 
        AND (c.`status` = '2' OR c.`status` = '3') ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Conta(s) Paga(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Semana
        </td>
        <td>
            Bom para
        </td>
        <td>
            N.º / Conta
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Valor
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['semana'];?>
        </td>
        <td>
            <?=$campos[$i]['bom_para'];?>
        </td>
        <td>
            <?=$campos[$i]['numero_conta'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
    </tr>
<?
    }
}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>