<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');
session_start('funcionarios');

/*Significa que essa tela está sendo aberto de outra tela como um Pop-Up e por um redirecionamento, 
mas não está sendo chamada diretamente de um Menu normal ...*/
if(empty($ignorar_sessao)) {
    if($id_emp2 == 1) {
        $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=1';
        $endereco_volta = 'opcoes.php?id_emp2=1';
    }else if($id_emp2 == 2) {
        $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=2';
        $endereco_volta = 'opcoes.php?id_emp2=2';
    }else if($id_emp2 == 4) {
        $endereco = '/erp/albafer/modulo/financeiro/recebimento/cheque_cliente/classes/manipular/opcoes.php?id_emp2=4';
        $endereco_volta = 'opcoes.php?id_emp2=4';
    }
    segurancas::geral($endereco, '../../../../../../');
}

if(!empty($_POST['txt_observacao'])) {
    $sql = "UPDATE `cheques_clientes` SET `historico` = CONCAT(`historico`, '  => ', '$_POST[txt_observacao]') WHERE `id_cheque_cliente` = '$_POST[id_cheque_cliente]' LIMIT 1 ";
    bancos::sql($sql);
}

//Procedimento quando carrega a Tela ...
$id_cheque_cliente = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cheque_cliente'] : $_GET['id_cheque_cliente'];

//Trago dados do Cheque do Cliente passado por parâmetro ...
$sql = "SELECT * 
        FROM `cheques_clientes` 
        WHERE `id_cheque_cliente` = '$id_cheque_cliente' LIMIT 1 ";
$campos             = bancos::sql($sql);
$id_empresa_cheque  = $campos[0]['id_empresa'];
$num_cheque         = $campos[0]['num_cheque'];
$banco              = $campos[0]['banco'];
$correntista        = $campos[0]['correntista'];
$valor              = number_format($campos[0]['valor'], 2, ',', '.');
$valor_disponivel   = number_format($campos[0]['valor_disponivel'], 2, ',', '.');
$data_emissao       = data::datetodata($campos[0]['data_sys'], '/');
$data_vencimento    = data::datetodata($campos[0]['data_vencimento'], '/');
$status             = $campos[0]['status'];
$predatado          = $campos[0]['predatado'];
$historico          = $campos[0]['historico'];
?>
<html>
<head>
<title>.:: Consultar Cheque ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_observacao.value == '') {
        alert('DIGITE A OBSERVAÇÃO !')
        document.form.txt_observacao.focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_observacao.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Detalhes de Cheques Recebidos
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Detalhes 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_cheque);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='#6473D4'>
                <b>N.º / Cheque:</b>
            </font>
        </td>
        <td>
            <font color='#6473D4'>
                <b>Banco:</b>
            </font>
        </td>
        <td>
            <font color='#6473D4'>
                <b>Correntista:</b>
            </font>
        </td>
        <td>
            <font color='#6473D4'>
                <b>Condição:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$num_cheque;?>
        </td>
        <td>
            <?=$banco;?>
        </td>
        <td>
            <?=$correntista;?>
        </td>
        <td>
        <?
            //Vetor de Status de Cheque ...
            $vetor_status = array('Cancelado', 'A compensar', 'Concluído / Compensado', 'Devolvido');
            echo $texto[$status];
            if($predatado == 1) echo ' <b>(Pré-Datado)</b>';
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='#6473D4'>
                <b>Data de Emissão:</b>
            </font>
        </td>
        <td>
            <font color='#6473D4'>
                <b>Data de Vencimento:</b>
            </font>
        </td>
        <td>
            <font color='#6473D4'>
                <b>Valor do Cheque:</b>
            </font>
        </td>
        <td>
            <font color='#6473D4'>
                <b>Valor Disponível:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' >
        <td>
            <?=$data_emissao;?>
        </td>
        <td>
            <?=$data_vencimento;?>
        </td>
        <td>
            <?=$valor;?>
        </td>
        <td>
            <?=$valor_disponivel;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='#6473D4'>
                <b>Histórico:</b>
            </font>
        </td>
        <td colspan='3'>
            <?=$historico;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='#6473D4'>
                <b>Observação:</b>
            </font>
        </td>
        <td colspan='3'>
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
</table>
<?
//Aqui retorna todas as contas em que esse cheque foi atrelado
$sql = "SELECT DISTINCT(cr.id_conta_receber), cr.num_conta, cr.descricao_conta, cr.data_vencimento, cr.valor, c.razaosocial, crq.valor AS valor_recebido 
        FROM `contas_receberes_quitacoes` crq 
        INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crq.`id_conta_receber` 
        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
        WHERE crq.`id_cheque_cliente` = '$id_cheque_cliente' ORDER BY crq.id_conta_receber_quitacao ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Conta(s) Atrelada(s) a esse Cheque
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.° Conta
        </td>
        <td>
            Cliente / Descrição
        </td>
        <td>
            Data Venc
        </td>
        <td>
            Valor Recebido
        </td>
        <td>
            Valor Total
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td align='center'>
            <?=$campos[$i]['num_conta'];?>
        </td>
        <td>
        <?
            echo $campos[$i]['razaosocial'].' / ';
            if($campos[$i]['descricao_conta'] == '') {
                echo '&nbsp;';
            }else {
                echo $campos[$i]['descricao_conta'];
            }
        ?>
        </td>
        <td align='center'>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor_recebido'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
    </tr>
<?
    }
?>
</table>
<?}?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_cheque_cliente' value="<?=$id_cheque_cliente;?>">
</form>
</body>
</html>