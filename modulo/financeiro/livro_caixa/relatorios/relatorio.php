<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = '<font class="confirmacao">CAIXA DE COMPRA EXCLUÍDO COM SUCESSO.</font>';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $cmb_empresa        = $_POST['cmb_empresa'];
    $cmd_consultar      = $_POST['cmd_consultar'];
}else {
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final     = $_GET['txt_data_final'];
    $cmb_empresa        = $_GET['cmb_empresa'];
    $cmd_consultar      = $_GET['cmd_consultar'];
}
?>
<html>
<head>
<title>.:: Livro Caixa ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final ...
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
//Empresa ...
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE A EMPRESA !')) {
        return false
    }
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value
    data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial = eval(data_inicial)
    data_final = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 365) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Livro Caixa
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Data Inicial:
            <?
//Sugestão de Período na Primeira vez em que carregar a Tela ...
                if(empty($txt_data_inicial)) {
                    $txt_data_inicial   = '01'.date('/m/Y');
                    $txt_data_final     = '30'.date('/m/Y');
                }
            ?>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;
            &nbsp;
            Empresa: 
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
            <?
                /*Não posso trazer a empresa 'Grupo' dentre essa relação de Empresas porque essa tela gerará 
                um documento que será apresentado para o Fisco ...*/
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `id_empresa` <> '4' 
                        AND `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql, $cmb_empresa);
            ?>
            </select>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
//Se foram digitadas as Datas acima, então realizo o SQL abaixo ...
    if(!empty($cmd_consultar) || !empty($cmd_atualizar)) {
        //Campos de Data ...
        $data_inicial = data::datatodate($txt_data_inicial, '-');
        $data_final = data::datatodate($txt_data_final, '-');
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            Data Pagamento
        </td>
        <td>
            Histórico
        </td>
        <td>
            Entrada(s)
        </td>
        <td>
            Saída(s)
        </td>
    </tr>
<?
        $total_entrada  = 0;
        $total_saida    = 0;
        /******************************************************************************************/
        /**************************************Contas à Pagar**************************************/
        /******************************************************************************************/
        $sql = "SELECT ca.id_conta_apagar, CONCAT(tp.`pagamento`, ' | ', b.banco, ' | ', pf.discriminacao, ' | ', ca.numero_conta, ' | ', f.razaosocial) AS historico, 
                caq.valor, DATE_FORMAT(caq.data, '%d/%m/%Y') AS data_quitacao 
                FROM `contas_apagares_quitacoes` caq 
                LEFT JOIN `bancos` b ON b.`id_banco` = caq.`id_banco` 
                INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = caq.`id_tipo_pagamento_recebimento` 
                INNER JOIN `contas_apagares` ca ON ca.`id_conta_apagar` = caq.`id_conta_apagar` AND ca.`id_empresa` = '$cmb_empresa' 
                LEFT JOIN `produtos_financeiros` pf ON pf.`id_produto_financeiro` = ca.`id_produto_financeiro` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = ca.`id_fornecedor` 
                WHERE caq.`data` BETWEEN '$data_inicial' AND '$data_final' ORDER BY caq.`data` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['data_quitacao'];?>
        </td>
        <td align='left'>
            <?=strtoupper($campos[$i]['historico']);?>
        </td>
        <td>
            <?=$campos[$i]['id_conta_apagar'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
    </tr>
<?
                $total_saida+= $campos[$i]['valor'];
            }
        }
        /******************************************************************************************/
        /*************************************Contas à Receber*************************************/
        /******************************************************************************************/
        $sql = "SELECT CONCAT(tr.recebimento, ' | ', b.banco, ' | ', cr.num_conta, ' | ', c.razaosocial) AS historico, 
                crq.valor, DATE_FORMAT(crq.data, '%d/%m/%Y') AS data_quitacao 
                FROM `contas_receberes_quitacoes` crq 
                LEFT JOIN `contas_correntes` cc ON cc.`id_contacorrente` = crq.`id_contacorrente` 
                INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = crq.`id_tipo_recebimento` 
                INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crq.`id_conta_receber` AND cr.`id_empresa` = '$cmb_empresa' 
                INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
                WHERE crq.`data` BETWEEN '$data_inicial' AND '$data_final' ORDER BY crq.`data` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['data_quitacao'];?>
        </td>
        <td align='left'>
            <?=strtoupper($campos[$i]['historico']);?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
                $total_entrada+= $campos[$i]['valor'];
            }
        }
        /******************************************************************************************/
        /********************************Transferência(s) de Caixa*********************************/
        /******************************************************************************************/
        $sql = "SELECT valor_transferencia, DATE_FORMAT(data_transferencia, '%d/%m/%Y') AS data_transferencia 
                FROM `transferencias_caixas` 
                WHERE `id_empresa` = '$cmb_empresa' 
                AND `data_transferencia` BETWEEN '$data_inicial' AND '$data_final' ORDER BY `data_transferencia` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['data_transferencia'];?>
        </td>
        <td align='left'>
            ???
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_transferencia'], 2, ',', '.');?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
                $total_entrada+= $campos[$i]['valor_transferencia'];
            }
        }
        /******************************************************************************************/
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            A TRANSPORTAR TOTAIS DO DIA R$
        </td>
        <td>
            <?=number_format($total_entrada, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_saida, 2, ',', '.');?>
        </td>
    </tr>
    <?
        //Aqui eu busco o último Saldo atual de Caixa cadastrado no sistema ...
        $sql = "SELECT saldo_atual_caixa, DATE_FORMAT(data_lancamento, '%d/%m/%Y') AS data_lancamento 
                FROM `saldos_atuais_caixas` 
                ORDER BY id_saldo_atual_caixa DESC LIMIT 1 ";
        $campos_saldo_atual_caixa = bancos::sql($sql);
    ?>
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            SALDO ANTERIOR EM 
            <font color='yellow'>
                <?=$campos_saldo_atual_caixa[0]['data_lancamento'];?>
            </font>
            R$
        </td>
        <td>
            <?=number_format($campos_saldo_atual_caixa[0]['saldo_atual_caixa'], 2, ',', '.');?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <?
        //Fórmulas ...
        $somas_conferencia_entrada  = $total_entrada + $campos_saldo_atual_caixa[0]['saldo_atual_caixa'];
        $saldo_atual                = $somas_conferencia_entrada - $total_saida;
        $somas_conferencia_saida    = $total_saida + $saldo_atual;
    ?>
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            SALDO ATUAL R$
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            <?=number_format($saldo_atual, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            (SOMAS PARA CONFERÊNCIA) R$ 
        </td>
        <td>
            <?=number_format($somas_conferencia_entrada, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($somas_conferencia_saida, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick="html5Lightbox.showLightbox(7, 'relatorio_pdf.php?txt_data_inicial=<?=$data_inicial;?>&txt_data_final=<?=$data_final;?>&cmb_empresa=<?=$cmb_empresa;?>')" class='botao'>
        </td>
    </tr>
<?
    }
?>
</table>
</form>
</body>
</html>