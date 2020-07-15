<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/grupo/index.php';
}else if($id_emp == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/pago/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../');

if($_POST['passo'] == 1) {
    $sql = "UPDATE `contas_apagares_quitacoes` SET `id_tipo_pagamento_recebimento` = '$_POST[id_tipo_pagamento]', `id_banco` = '$_POST[cmb_banco]', `id_contacorrente` = '$_POST[cmb_conta_corrente]', `id_cheque` = '$_POST[cmb_cheque]' WHERE `id_conta_apagar_quitacao` = '$_POST[id_conta_apagar_quitacao]' LIMIT 1 ";
    bancos::sql($sql);
    
    //Aqui adiciona dados na tabela relacional de contas_apagares com cheques
    if(!empty($_POST['cmb_cheque'])) {
/*Só entra aqui se o cheque for pré-datado, muda o campo predatado da conta à pagar
para 1, significando que aquela conta foi paga com cheque mas predatado*/
        if($_POST[chkt_predatado] == 1) {
            $sql = "SELECT id_conta_apagar 
                    FROM `contas_apagares_quitacoes` 
                    WHERE `id_conta_apagar_quitacao` = '$_POST[id_conta_apagar_quitacao]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $sql = "UPDATE `contas_apagares` SET `predatado` = '1' WHERE `id_conta_apagar` = '".$campos[0]['id_conta_apagar']."' LIMIT 1 ";
            bancos::sql($sql);
            
            $sql = "UPDATE `cheques` SET `predatado` = '1' WHERE `id_cheque` = '$_POST[cmb_cheque]' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
?>
<Script Language = 'JavaScript'>
    opener.location = opener.location.href
    alert('PAGAMENTO ATUALIZADO COM SUCESSO !')
    window.close()
</Script>
<?
}
?>
<html>
<head>
<title>.:: Atualizar Pagamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function submeter(valor) {
    document.form.valor_combo.value = valor
    document.form.submit()
}

function separar() {
    var tipo_pagamento = document.form.cmb_tipo_pagamento.value
    var achou = 0, id_tipo_pagamento = '', status_ch = ''
    for(i = 0; i < tipo_pagamento.length; i++) {
        if(tipo_pagamento.charAt(i) == '|') {
            achou = 1
        }else {
            if(achou == 0) {
                id_tipo_pagamento = id_tipo_pagamento + tipo_pagamento.charAt(i)
            }else {
                status_ch = status_ch + tipo_pagamento.charAt(i)
            }
        }
    }
    document.form.id_tipo_pagamento.value   = id_tipo_pagamento
    document.form.status_ch.value           = status_ch
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<table border='0' width='98%' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Atualizar Pagamento
            <font color='yellow'>
            <?
                if($id_emp != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($id_emp);
                }else {
                    echo 'TODAS EMPRESAS';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Pagto:</b>
        <?
//Verifica aqui o tipo de status_ch para poder saber se tem banco ou não
            if($valor_combo >= 1 && !empty($cmb_tipo_pagamento) && $status_ch > 0) {
        ?>
        </td>
        <td>
            <b>Banco / Agência: </b>
        <?
            }
            if($valor_combo>=2 && !empty($cmb_banco) && $status_ch == 1) {
        ?>
        </td>
        <td>
            <b>Conta Corrente:</b>
        <?
            }else if($valor_combo>=2 && !empty($cmb_banco) && $status_ch == 2) {
        ?>
        </td>
        <td>
            <b>Cheque:</b>
            <?
                $checked = ($chkt_predatado == 1) ? 'checked': '';
            ?>
            <input type="checkbox" name="chkt_predatado" value="1" <?=$checked;?> class="checkbox" id="cheque">
            <label for="cheque">Pré-Datado</label>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
                <select name='cmb_tipo_pagamento' title='Selecione o Tipo de Pagamento' onchange="separar();submeter(1)" class='combo'>
                <?
                    $sql = "SELECT CONCAT(id_tipo_pagamento, '|', status_ch) AS tipo_pagamento_status, pagamento 
                            FROM `tipos_pagamentos` 
                            WHERE `ativo` = '1' ORDER BY pagamento ";
                    echo combos::combo($sql, $cmb_tipo_pagamento);
                ?>
                </select>
        <?
//Verifica aqui o tipo de status_ch para poder saber se tem banco ou não
                if($valor_combo >= 1 && !empty($cmb_tipo_pagamento) && $status_ch > 0) {
        ?>
        </td>
        <td>
                <select name="cmb_banco" class="combo" title="Selecione o Banco" onChange="submeter(2)">
                <?
                    $sql = "SELECT DISTINCT(b.id_banco) AS id_banco, CONCAT(b.banco, ' / ', cod_agencia, ' | ', nome_agencia) AS agencia 
                            FROM `bancos` b 
                            INNER JOIN `agencias` a ON a.id_banco = b.id_banco AND a.`ativo` = '1' 
                            INNER JOIN `contas_correntes` cc ON cc.id_agencia = a.id_agencia AND cc.`id_empresa` = '$id_emp' 
                            WHERE b.`ativo` = '1' ORDER BY agencia ";
                    echo combos::combo($sql, $_POST['cmb_banco']);
                ?>
                </select>
        <?
                }
                if($valor_combo>=2 && !empty($cmb_banco) && $status_ch == 1) {
        ?>
        </td>
        <td>
                <select name="cmb_conta_corrente" title="Selecione a Conta Corrente" onChange="submeter(3)" class="combo">
        <?
                $sql = "SELECT DISTINCT(cc.id_contacorrente) AS id_conta_corrente, cc.conta_corrente 
                        FROM `bancos` b 
                        INNER JOIN `agencias` a ON a.id_banco = b.id_banco 
                        INNER JOIN `contas_correntes` cc ON cc.id_agencia = a.id_agencia AND cc.ativo = '1' AND cc.id_empresa = '$id_emp' 
                        WHERE b.id_banco = '$cmb_banco' ORDER BY cc.conta_corrente ";
                echo combos::combo($sql, $_POST['cmb_conta_corrente']);
        ?>
                </select>
        <?
                }else if($valor_combo>=2 && !empty($cmb_banco) && $status_ch == 2) {
        ?>
        </td>
        <td>
            <select name='cmb_cheque' title='Selecione o Cheque' onchange='submeter(3)' class='combo'>
            <?
                if($cmb_banco == 2) {//Se Bradesco ...
                    $numero_cheque = '6300';
                }else if($cmb_banco == 4) {//Se Itaú ...
                    $numero_cheque = '506000';
                }
                //Aqui listo os cheques a partir do Status de Emitidos, das contas correntes ativas, talões ativos, do Banco que foi selecionado ...
                $sql = "SELECT DISTINCT(id_cheque) AS id_cheque, CONCAT(conta_corrente, ' | ', num_cheque, ' | R$ ', REPLACE(valor, '.', ',')) AS cheque 
                        FROM cheques c 
                        INNER JOIN `taloes` t ON t.id_talao = c.id_talao AND t.ativo = '1' 
                        INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente AND cc.ativo = '1' AND cc.id_empresa = '$id_emp' 
                        INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                        INNER JOIN `bancos` b ON b.id_banco = a.id_banco AND b.id_banco = '$cmb_banco' 
                        WHERE (c.`status` >= '2' 
                        AND c.num_cheque >= '$numero_cheque' 
                        AND c.`ativo` = '1'
                        AND c.`valor` > '0') ";
                echo combos::combo($sql, $cmb_cheque);
            ?>
            </select>
        <?
                }
        ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick='document.form.passo.value = 1' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<!--Essa caixa é só para poder voltar o status do cheque 0, caso o usuário desista daquele cheque-->
<input type='hidden' name='id_cheque' value='<?=$cmb_cheque;?>'>
<!--****************************************************-->
<input type='hidden' name='status_ch' value='<?=$status_ch;?>'>
<input type='hidden' name='id_tipo_pagamento' value='<?=$id_tipo_pagamento;?>'>
<input type='hidden' name='id_conta_apagar_quitacao' value='<?=$_GET['id_conta_apagar_quitacao']?>'>
<input type='hidden' name='valor_combo'>
<input type='hidden' name='passo'>
</form>
</body>
</html>