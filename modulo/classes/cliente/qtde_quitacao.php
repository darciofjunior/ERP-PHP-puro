<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) QUITAÇÕES(S) PARA ESSE CLIENTE.</font>";
$data_atual_americano = date('Y-m-d');

///////////////////////////////////Estrutura dos SQL(s) do Union///////////////////////////////////
//1) Aqui seleciono todas as contas à receber do cliente - Modo Antigo ...
//2) Aqui busca as contas que são importadas diretamente do faturamento ...

/*Busca todas as Contas à Receber em que o Status de Recebimento está totalmente Recebido '2' ou que o Cliente tenha 
algum crédito, mesmo que ele tenha pago a Dívida conosco de forma total ...*/

if($_GET['cmb_periodo'] == 6) {//Últimos 6 meses
    $dias = 180;
}else if($_GET['cmb_periodo'] == 12) {//Últimos 1 ano ...
    $dias = 365;
}else if($_GET['cmb_periodo'] == 24) {//Últimos 2 anos ...
    $dias = 365 * 2;
}else if($_GET['cmb_periodo'] == 36) {//Últimos 3 anos ...
    $dias = 365 * 3;
}else if($_GET['cmb_periodo'] == 48) {//Últimos 4 anos ...
    $dias = 365 * 4;
}else if($_GET['cmb_periodo'] == 60) {//Últimos 5 anos ...
    $dias = 365 * 5;
}else {//Senão existir a combo de Período então, sugere os últimos 6 meses como default ...
    $dias = 180;
}
$sql = "SELECT DISTINCT(cr.id_conta_receber) 
        FROM `contas_receberes` cr 
        INNER JOIN `contas_receberes_quitacoes` crq ON crq.id_conta_receber = cr.id_conta_receber AND crq.data > DATE_ADD('$data_atual_americano', "."INTERVAL -$dias DAY".") 
        WHERE cr.`id_cliente` = '$_GET[id_cliente]' 
        AND cr.ativo = '1' 
        AND cr.status >= '1' ";
$campos = bancos::sql($sql);
$linhas = count($campos);	
for($i = 0; $i < $linhas; $i++) $id_contas[] = $campos[$i]['id_conta_receber'];
//Arranjo Ténico
if(count($id_contas) == 0) $id_contas[] = 0;
$vetor_contas = implode(',', $id_contas);

$sql = "SELECT cr.*, tm.simbolo 
        FROM `contas_receberes` cr 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
        WHERE cr.id_conta_receber in ($vetor_contas) order by cr.data_vencimento ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Qtde de Quitação(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='100%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
if($linhas == 0) {
?>
    <tr align='center'>
        <td colspan="6">
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
}else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#DEDEDE'>
            <b>N.° Conta</b>
        </td>
        <td bgcolor='#DEDEDE' width="20%">
            <b>Empresa</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>Data Venc</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>Última Data Recebida</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>Dias de Atraso</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>Valor Recebido</b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
//Eu antecipo esse SQL aqui, p/ controlar as Cores das Linhas ...
	$sql = "SELECT data AS data_recebida 
                FROM `contas_receberes_quitacoes` 
                WHERE `id_conta_receber` = ".$campos[$i]['id_conta_receber']." 
                ORDER BY `data_recebida` DESC LIMIT 1 ";
	$campos_recebida    = bancos::sql($sql);
	$data_recebida      = $campos_recebida[0]['data_recebida'];
//Se a Conta foi recebida depois da data de Recebimento, então mostro a linha em Vermelho ...
	$color = ($campos[$i]['data_vencimento'] < $data_recebida) ? 'red' : '';
?>
    <tr class='linhanormal' align='center'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
                <a href="javascript:nova_janela('../../financeiro/recebimento/alterar.php?id_conta_receber=<?=$campos[$i]['id_conta_receber'];?>&pop_up=1', 'POP', '', '', '', '', '550', '950', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes' class='link'>
                    <?=$campos[$i]['num_conta'];?>
                </a>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
                <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
                <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
                <?=data::datetodata($data_recebida, '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
            <?
                $diferenca = data::diferenca_data($campos[$i]['data_vencimento'], $data_recebida);
//Só mostra o Dia das Contas que foram pagas atrasadas ...
                if($diferenca[0] > 0) {
                    echo $diferenca[0];
                }else {
                    echo '&nbsp;';
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
            <?
            //Verifica o Total que foi recebido da Conta Corrente ...
                $sql = "SELECT SUM(valor) AS valor_recebido 
                        FROM `contas_receberes_quitacoes` 
                        WHERE `id_conta_receber` = ".$campos[$i]['id_conta_receber']." GROUP BY id_conta_receber ";
                $campos_total_recebido = bancos::sql($sql);
                echo 'R$ '.number_format($campos_total_recebido[0]['valor_recebido'], 2, ',', '.');
                $valor_total+= $campos_total_recebido[0]['valor_recebido'];
            ?>
            </font>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal'>
        <td colspan='6' bgcolor='#DEDEDE' align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1' color='#000000'>
                <b>Valor Total.:</b>&nbsp;
            </font>
            <font color='darkblue'>
                <b><?='R$ '.number_format($valor_total, 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
<?}?>
</table>
</body>
</html>