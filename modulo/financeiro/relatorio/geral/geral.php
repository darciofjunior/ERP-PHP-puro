<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/financeiros.php');

function contas_pagar($id_fornecedor, $cmb_situacao) {
    if($cmb_situacao == 1) {//Pega as contas com cheque predatado e contas paga parcialmente ...
        $condicao = " AND (ca.`status` < '2' OR (ca.`status` = '2' AND ca.`predatado` = '1')) ";
    }else {//Pega todas as contas que já foram quitadas
        $condicao = " AND ca.`status` = '2' AND ca.`predatado` = '0' ";
    }
    //Tudo o que se refere a Pedidos de Compras ...
    $sql = "SELECT ca.`id_conta_apagar` 
            FROM `contas_apagares` ca 
            WHERE ca.`id_fornecedor` = '$id_fornecedor' 
            AND ca.`ativo` = '1' 
            AND ca.`id_pedido` > '0' $condicao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) $total_a_pagar_do_fornecedor+= financeiros::calculos_conta_pagar($campos[$i]['id_conta_apagar']);
    
    //Tudo o que se a Antecipação de Compras ...
    $sql = "SELECT ca.`id_conta_apagar` 
            FROM `contas_apagares` ca 
            WHERE ca.`id_fornecedor` = '$id_fornecedor' 
            AND ca.`ativo` = '1' 
            AND ca.`id_antecipacao` > '0' $condicao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) $total_a_pagar_do_fornecedor+= financeiros::calculos_conta_pagar($campos[$i]['id_conta_apagar']);
    
    //Tudo o que se a NFe de Compras ...
    $sql = "SELECT ca.`id_conta_apagar` 
            FROM `contas_apagares` ca 
            WHERE ca.`id_fornecedor` = '$id_fornecedor' 
            AND ca.`ativo` = '1' 
            AND ca.`id_nfe` > '0' $condicao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) $total_a_pagar_do_fornecedor+= financeiros::calculos_conta_pagar($campos[$i]['id_conta_apagar']);
    
    $sql = "SELECT ca.`id_conta_apagar` 
            FROM `contas_apagares` ca 
            INNER JOIN `produtos_financeiros_vs_fornecedor` pff ON pff.`id_fornecedor` = ca.`id_fornecedor` 
            INNER JOIN `contas_apagares_vs_pffs` cap ON cap.`id_conta_apagar` = ca.`id_conta_apagar` 
            WHERE ca.`id_fornecedor` = '$id_fornecedor' 
            AND ca.`ativo` = '1' 
            AND ca.`id_nfe` > '0' $condicao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) $total_a_pagar_do_fornecedor+= financeiros::calculos_conta_pagar($campos[$i]['id_conta_apagar']);

    return $total_a_pagar_do_fornecedor;
}

function contas_receber($id_cliente, $cmb_situacao) {
    //Pega as contas com cheque predatado e contas paga parcialmente ...
    $condicao_cliente = ($cmb_situacao == 1) ? " AND (`status` < '2' OR (`status` = '2' AND `predatado` = '1')) " : " AND `status` = '2' AND `predatado` = '0' ";

    $sql = "SELECT `id_conta_receber` 
            FROM `contas_receberes` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `ativo` = '1' 
            $condicao_cliente ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) $total_a_receber_do_cliente+= financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
    
    return $total_a_receber_do_cliente;
}
?>
<html>
<head>
<title>.:: Relatório Geral ::.</title>
<meta http-equiv = 'Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content='no-store'>
<meta http-equiv = 'pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type='text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function habilitar() {
    if(document.form.chkt_todos.checked == true) {
        document.form.txt_fornecedor_cliente.disabled  = true
        document.form.txt_fornecedor_cliente.className = 'textdisabled'
        document.form.txt_fornecedor_cliente.value     = ''
    }else {
        document.form.txt_fornecedor_cliente.disabled  = false
        document.form.txt_fornecedor_cliente.className = 'caixadetexto'
        document.form.txt_fornecedor_cliente.focus()
    }
}

function validar() {
    //Fornecedor / Cliente ...
    if(document.form.txt_fornecedor_cliente.disabled == false) {
        if(!texto('form', 'txt_fornecedor_cliente', '3', "qwertyuiopçlkjhgfdsazxcvbnmáéíóúâêîôûãõüà QWERTYUIOPÇLKJHGFDSAZXCVBNMÁÉÍÓÚÂÊÎÔÛÃÕÜÀ/ \ @#$%&*()'}]?/:;>.<,{[+=_-¹²³£¢¬§ªº°| 1234567890", 'FORNECEDOR / CLIENTE', '2')) {
            return false
        }
    }
    //Situação ...
    if(!combo('form', 'cmb_situacao', '', 'SELECIONE UMA SITUÇÃO !')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_fornecedor_cliente.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Relatório Geral
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Fornecedor / Cliente:</b>
            <input type='text' name='txt_fornecedor_cliente' maxlength='40' size='50' class='caixadetexto'>
            <input type='checkbox' name='chkt_todos' id='chkt_todos' value='1' onclick='habilitar()'>
            <label for='chkt_todos'>
                Todos
            </label>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
                <b>Situação</b>
                <select name='cmb_situacao' class='combo'>
                    <option value='' style='color:red' selected>SELECIONE</option>
                    <option value='1' selected>Contas Pendentes</option>
                    <option value='2'>Contas Quitadas</option>
                </select>
            </font>
            <input type='submit' name='cmd_consultar' value='Consultar' class='botao'>
        </td>
    </tr>
<?
    if(!empty($_POST['cmd_consultar'])) {
	if(!empty($_POST['chkt_tudo'])) $condicao_fornecedores = " AND f.`razaosocial` LIKE '%$txt_fornecedor_cliente%' ";
//Seleciona todos os clientes e fornecedores de mesmo "CNPJ ou CPF" ...
	$sql = "SELECT c.`id_cliente`, f.`id_fornecedor`, f.`cnpj_cpf`, c.`razaosocial` AS razao_social 
                FROM fornecedores f 
                INNER JOIN `clientes` c ON c.`cnpj_cpf` = f.`cnpj_cpf` AND c.`ativo` = '1' 
                WHERE f.`ativo` = '1' 
                AND f.`cnpj` <> '' $condicao_fornecedores ORDER BY c.`razaosocial` ";
	$campos = bancos::sql($sql);
	echo $linhas = count($campos);
        if($linhas == 0) {
?>
    <tr class='atencao' align="center">
        <td>
            <?$aviso = ($cmb_situacao == 1) ? 'PENDENTE(S)' : 'QUITADA(S)';?>
            NÃO EXISTE(M) FORNECEDOR(ES) / Cliente(S) QUE POSSUI(EM) CONTAS <?=$aviso;?>
        </td>
    </tr>
<?
	}else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Fornecedor / Cliente
        </td>
        <td>
            Contas à Pagar
        </td>
        <td>
            Contas à Receber
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
                $total_a_pagar_do_fornecedor    = contas_pagar($campos[$i]['id_fornecedor'], $_POST['cmb_situacao']);
                $total_a_receber_do_cliente     = contas_receber($campos[$i]['id_cliente'], $_POST['cmb_situacao']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['razao_social'];?>
        </td>
        <td align='right'>
            R$ <?=number_format($total_a_pagar_do_fornecedor, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($total_a_receber_do_cliente, 2, ',', '.');?>
        </td>
    </tr>
<?
                $total_conta_apagar_fornecedor+= $total_a_pagar_do_fornecedor;
                $total_conta_receber_cliente+= $total_a_receber_do_cliente;
            }
?>
    <tr class='linhacabecalho' align='right'>
        <td align='left'>
            Total(is) => 
        </td>
        <td>
            R$ <?=number_format($total_conta_apagar_fornecedor, 2, ',', '.');?>
        </td>
        <td>
            R$ <?=number_format($total_conta_receber_cliente, 2, ',', '.');?>
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