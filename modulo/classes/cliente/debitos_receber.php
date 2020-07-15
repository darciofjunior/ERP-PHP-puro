<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');

//Quando essa variável for igual a 1, o sistema ignora esta Sessão p/ não dar erro de permissão na Tela
if($_GET['ignorar_sessao'] != 1) {
    if($id_emp == 1) {
        $endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/albafer/index.php';
    }else if($id_emp == 2) {
        $endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/tool_master/index.php';
    }else if($id_emp == 4) {
        $endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/grupo/index.php';
    }else if($id_emp == 0) {//Todos
        $endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/todas_empresas/index.php';
    }
    segurancas::geral($endereco, '../../../../../');
}
$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) DÉBITO(S) PARA ESSE CLIENTE.</font>";

//Busca do último valor do Dólar e do Euro ...
$valor_dolar = genericas::moeda_dia('dolar');
$valor_euro = genericas::moeda_dia('euro');

//Visualizando as Contas à Receber
$retorno    = financeiros::contas_em_aberto($_GET['id_cliente'], 1, '', 2);
$linhas     = count($retorno['id_contas']);
?>
<html>
<head>
<title>.:: Débito(s) à Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='100%' cellspacing ='1' cellpadding='1' border='0' align='center'>
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
    <tr class='linhanormal' align='center'>
        <td bgcolor='#DEDEDE'>
            <b>N.° CONTA</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>EMPRESA</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>DATA VENC</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>DATA VENC ALT</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>DIA(S) VENCIDO(S)</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>VALOR</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>VALORES EXTRAS</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>VALORES DESPESAS</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>VALOR À RECEBER</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>VALOR RECEBIDO</b>
        </td>
        <td bgcolor='#DEDEDE'>
            <b>COMISSÃO ESTORNADA</b>
            &nbsp;
            <img src = '../../../imagem/bloco_negro.gif' title='A comissão será Estornada apenas após 60 Dia(s) Vencido(s)' style='cursor:help' width='5' height='5' border='0'>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
//Busca de Alguns Dados da Conta à Receber ...
        $sql = "SELECT cr.*, CONCAT(tm.`simbolo`, '&nbsp;') AS simbolo 
                FROM `contas_receberes` cr 
                INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
                WHERE cr.`id_conta_receber` = '".$retorno['id_contas'][$i]."' LIMIT 1 ";
        $campos = bancos::sql($sql);
//Essa variável iguala o tipo de moeda da conta à receber
        $moeda = $campos[0]['simbolo'];

        $calculos_conta_receber = financeiros::calculos_conta_receber($retorno['id_contas'][$i]);
        $valor_reajustado       = $calculos_conta_receber['valor_reajustado'];
        $valores_extra          = $calculos_conta_receber['valores_extra'];
        
        if($calculos_conta_receber['valor_reajustado'] < 0) {
            $color  = '#ff33ff';
        }else {
            if($campos[0]['data_vencimento_alterada'] < date('Y-m-d')) {//Contas Vencidas ...
                if($calculos_conta_receber['valor_reajustado'] > 0) {
                    $total_vencidas+= $calculos_conta_receber['valor_reajustado'];
                    $vetor_data = data::diferenca_data($campos[0]['data_vencimento_alterada'], date('Y-m-d'));
                    $dias       = $vetor_data[0];
                    $color      = 'red';
                }else {
                    $color      = '';
                    $dias       = '';
                }
            }else {//Contas à vencer
                $total_vencer+= $calculos_conta_receber['valor_reajustado'];
                $color          = '';
                $dias           = '';
            }
        }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
                <a href="javascript:nova_janela('../../financeiro/recebimento/alterar.php?id_conta_receber=<?=$retorno['id_contas'][$i];?>&pop_up=1', 'POP', '', '', '', '', '550', '950', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class="link">
                    <?=$campos[0]['num_conta'];?>
                </a>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
                <?=genericas::nome_empresa($campos[0]['id_empresa']);?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
                <?=data::datetodata($campos[0]['data_vencimento'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
                <?=data::datetodata($campos[0]['data_vencimento_alterada'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
                <?=$dias;?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1.5' color='<?=$color;?>'>
            <?
                if($campos[0]['valor'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $campos[0]['simbolo'].number_format($campos[0]['valor'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                /*Propositalmente eu estou descontando o Valor Despesas de dentro da variável $valores_extras, que 
                já leva esse valor em conta, porque criei uma coluna específica abaixo só para "valores Despesas" ...*/
                echo 'R$ '.number_format($calculos_conta_receber['valores_extra'] - $campos[0]['valor_despesas'], 2, ',', '.');
            ?>
            </font>
        </td>		
        <td align='right'>
            <?=number_format($campos[0]['valor_despesas'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?='R$ '.number_format($calculos_conta_receber['valor_reajustado'], 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[0]['valor_pago'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $campos[0]['simbolo'].number_format($campos[0]['valor_pago'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                /*Esses "60" é porque no SCAN ERP é feita uma verificação se tem alguma Duplicata que 
                está com atraso maior do que 60 dias e que já foi importada, indiferente da Empresa 
                e que não tenham algum Lançamento como sendo "Atraso de Pagamento"*/
                if($dias > 60) {
                    $sql = "SELECT nfs.`comissao_media` 
                            FROM `contas_receberes` cr 
                            INNER JOIN `nfs` ON nfs.`id_nf` = cr.`id_nf` 
                            WHERE cr.`id_conta_receber` = '".$campos[0]['id_conta_receber']."' LIMIT 1 ";
                    $campos_comissao = bancos::sql($sql);
                    echo 'R$ '.number_format(($campos_comissao[0]['comissao_media'] / 100) * $calculos_conta_receber['valor_reajustado'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
    </tr>
<?
        $valor_total+= $calculos_conta_receber['valor_reajustado'];
    }
?>
    <tr class='linhanormal'>
        <td colspan='3' bgcolor='#DEDEDE'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1' color='red'>
                <b>Total Vencido.:</b>&nbsp;
            </font>
            <?
                //Se for Negativa então ...
                $escrever = ($total_vencidas < 0) ? ' (CRÉDITO A FAVOR)' : '';
                echo 'R$ '.number_format($total_vencidas, 2, ',', '.').$escrever;
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1' color='blue'>
                <b>Total à Vencer.:</b>&nbsp;
            </font>
            <?='R$ '.number_format($total_vencer, 2, ',', '.');?>
        </td>
        <td colspan='6' bgcolor='#DEDEDE' align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1' color='#000000'>
                <b>Valor Total.:</b>&nbsp;
            </font>
            <font color="darkblue">
                <b><?='R$ '.number_format($valor_total, 2, ',', '.');?></b>
            </font>
        </td>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>