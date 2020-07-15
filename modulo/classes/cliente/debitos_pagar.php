<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');

//Quando essa variável for igual a 1, o sistema ignora esta Sessão p/ não dar erro de permissão na Tela
if($_GET['ignorar_sessao'] != 1) {
    if($id_emp == 1) {
        $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/albafer/index.php';
    }else if($id_emp == 2) {
        $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/tool_master/index.php';
    }else if($id_emp == 4) {
        $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/grupo/index.php';
    }else if($id_emp == 0) {//Todos
        $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/todas_empresas/index.php';
    }
    segurancas::geral($endereco, '../../../../../');
}
$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) DÉBITO(S) PARA ESSE CLIENTE.</font>";

//Busca do último valor do Dólar e do Euro ...
$valor_dolar    = genericas::moeda_dia('dolar');
$valor_euro     = genericas::moeda_dia('euro');

//Visualizando as Contas à Pagar
$retorno        = financeiros::contas_em_aberto($_GET['id_fornecedor'], 2, '', 1, $id_conta_apagar_automatica);
$linhas         = count($retorno['id_contas']);
?>
<html>
<head>
<title>.:: Débito(s) à Pagar ::.</title>
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
    <tr>
        <td></td>
    </tr>
    <tr class='atencao'>
        <td align='center'>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
        exit;
    }
?>
    <tr class='linhanormal' align="center">
        <td bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2'>
                <b>N.° Conta</b>
            </font>
        </td>
        <td bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2'>
                <b>Empresa</b>
            </font>
        </td>
        <td bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2'>
                <b>Data Venc</b>
            </font>
        </td>
        <td bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2'>
                <b>Valor Pago</b>
            </font>
        </td>
        <td bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2'>
                <b>Valor à Pagar</b>
            </font>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $sql = "SELECT ca.*, concat(tm.simbolo, '&nbsp;') as simbolo 
                FROM `contas_apagares` ca 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
                WHERE ca.`id_conta_apagar` = '".$retorno['id_contas'][$i]."' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $moeda 	= $campos[0]['simbolo'];//Essa variável iguala o tipo de moeda da conta à pagar
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5'>
<!--Essa variável $nao_exibir iframe é uma jogadinha para não mostrar o iframe abaixo, faço isso porque
quando eu clico nesse link ele abre um Pop-UP com esse mesmo arquivo que eu estou corrente, mas na
intenção de simplesmente exibir os detalhes da conta-->
                <a href="javascript:nova_janela('../../financeiro/pagamento/detalhes.php?id_conta_apagar=<?=$campos[0]['id_conta_apagar'];?>&nao_exibir_iframe=1', 'DETALHES2', '', '', '', '', '500', '920', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes' class='link'>
                    <?=$campos[0]['numero_conta'];?>
                </a>
            </font>
        </td>
        <td>
            <?=genericas::nome_empresa($campos[0]['id_empresa']);?>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_vencimento'], '/');?>
        </td>
        <td align='right'>
        <?
            if($campos[0]['valor_pago'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo $moeda.number_format($campos[0]['valor_pago'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            $valor_pagar = $campos[0]['valor'] - $campos[0]['valor_pago'];
            
            /* A princípio não está sendo utilizado, Dárcio dia - 16/10/2014 ...

            if($campos[0]['predatado'] == 1) {
                //Está parte é o script q exibirá o valor da conta quando o cheque for pré-datado ...
                $sql = "SELECT SUM(caq.valor) valor 
                        FROM `contas_apagares` ca 
                        INNER JOIN `contas_apagares_quitacoes` caq ON caq.`id_conta_apagar` = ca.`id_conta_apagar` 
                        INNER JOIN `cheques` c ON c.`id_cheque` = caq.`id_cheque` AND c.`status` IN (1, 2) AND c.`predatado` = '1' 
                        WHERE ca.`id_conta_apagar` = '".$retorno['id_contas'][$i]."' ";
                $campos_pagamento   = bancos::sql($sql);
                $valor_conta        = $campos_pagamento[0]['valor'];
                $valor_pagar+= $valor_conta;
            }*/
            
            if($campos[0]['id_tipo_moeda'] == 2) {//Dólar
                $valor_pagar*= $valor_dolar;
            }else if($campos[0]['id_tipo_moeda'] == 3) {//Euro
                $valor_pagar*= $valor_euro;
            }

            if($valor_pagar == '0.00') {
                echo '&nbsp;';
            }else {
                echo 'R$ '.number_format($valor_pagar, 2, ',', '.');
                $valor_pagar_total+= $valor_pagar;
            }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td colspan='4' bgcolor='#DEDEDE' align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='red'>
                <b>Valor Total.:</b>&nbsp;
            </font>
        </td>
        <td bgcolor='#DEDEDE' align='right'>
            <b><?='R$ '.number_format($valor_pagar_total, 2, ',', '.');?></b>
        </td>
    </tr>
</table>
</body>
</html>