<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');
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

$mensagem[1] = '<font class="atencao">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="erro">NÃO EXISTE(M) CONTA(S) PAGA(S) COM ESSE CHEQUE.</font>';
$mensagem[3] = '<font class="confirmacao">CHEQUE DESVINCULADO COM SUCESSO.</font>';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente AS id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.id_talao = c.id_talao AND t.ativo = '1' 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE c.`num_cheque` LIKE '$txt_consultar%' 
                    AND c.`status` = '2' 
                    AND c.`ativo` = '1' ORDER BY c.num_cheque DESC ";
        break;
        default:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente AS id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.id_talao = c.id_talao AND t.ativo = '1' 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE c.`status` = '2' 
                    AND c.`ativo` = '1' ORDER BY c.num_cheque DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'desvincular.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Cheque(s) p/ Desvincular ::.</title>
<meta http-equiv = 'Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content='no-store'>
<meta http-equiv = 'pragma' content='no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Cheque(s) p/ Desvincular
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Cheque
        </td>
        <td>
            Número Inicial
        </td>
        <td>
            Conta Corrente
        </td>
        <td>
            Agência
        </td>
        <td>
            Banco
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = 'desvincular.php?passo=2&id_cheque_antigo='.$campos[$i]['id_cheque'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url;?>" title='Cheques do tal&atilde;o <?=$campos[$i]['num_inicial'];?>/<?=$campos[$i]['num_final'];?>' class='link'>
                <?=$campos[$i]['num_cheque'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['num_inicial'];?>
        </td>
        <td>
            <?=$campos[$i]['conta_corrente'];?>
        </td>
        <td>
            <?=$campos[$i]['cod_agencia'];?>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' onclick="window.location = 'desvincular.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    $id_cheque_antigo = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cheque_antigo'] : $_GET['id_cheque_antigo'];
//Aqui o sistema traz o N.º do Cheque "Antigo" passado por parâmetro ...
    $sql = "SELECT num_cheque, valor 
            FROM `cheques` 
            WHERE `id_cheque` = '$id_cheque_antigo' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $num_cheque_antigo  = $campos[0]['num_cheque'];
    $valor_cheque_atual = number_format($campos[0]['valor'], 2, ',', '.');

//Aqui eu seleciono todas as contas que foram pagas com o cheque antigo
    $sql = "SELECT ca.*, f.razaosocial, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
            FROM `contas_apagares_quitacoes` caq 
            INNER JOIN `contas_apagares` ca ON ca.`id_conta_apagar` = caq.`id_conta_apagar` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = ca.`id_fornecedor` 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
            WHERE caq.`id_cheque` = '$id_cheque_antigo' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'desvincular.php<?=$parametro;?>&passo=1&valor=2'
        </Script>
<?
    }else {
        //Aqui atualiza o status do cheque p/ 0 "Aberto", pq este apenas estava travada e nunca tinha sido usado antes ...
        $sql = "UPDATE `cheques` SET `status` = '0' WHERE `status` = '1' AND `id_funcionario` = '$_SESSION[id_funcionario]' AND `id_cheque` <> '$id_cheque_antigo' AND `valor` = '0.00' ";
        bancos::sql($sql);
        //Aqui atualiza o status do cheque p/ 2 "Emitido", pq ele esta travado e já foi usado ...
        $sql = "UPDATE `cheques` SET `status` = '2' WHERE `status` = '1' AND `id_funcionario` = '$_SESSION[id_funcionario]' AND `id_cheque` <> '$id_cheque_antigo' AND `valor` <> '0.00' ";
        bancos::sql($sql);
        //Aqui volta o status do cheque p/ 0 "Aberto", caso o usuário tenha desistido daquele cheque ...
        if(!empty($_POST['cmb_cheque'])) {
            //Verifico se o cheque selecionado pelo Usuário está travado ...
            $sql = "SELECT status 
                    FROM `cheques` 
                    WHERE `id_cheque` = '$_POST[cmb_cheque]' LIMIT 1 ";
            $campos_status = bancos::sql($sql);
            //Se sim, eu igualo o Cheque da Combo com o id_cheque que está no Hidden de Controle ...
            if($campos_status[0]['status'] == 1) $_POST['cmb_cheque'] = $_POST['hdd_cheque'];
            //Travo o cheque da Combo "Reservando" p/ que não chegue outro usuário e não utilize o mesmo número ...
            $sql = "UPDATE `cheques` SET `status` = '1', id_funcionario = '$_SESSION[id_funcionario]' WHERE `id_cheque` = '$_POST[cmb_cheque]' LIMIT 1 ";
            bancos::sql($sql);
        }
?>
<html>
<head>
<title>.:: Cheque(s) p/ Desvincular ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.cmb_tipo_pagamento.value == '') {
        alert('SELECIONE O TIPO DE PAGAMENTO !')
        document.form.cmb_tipo_pagamento.focus()
        return false
    }
    if(typeof(document.form.cmb_banco) == 'object') {
        if(document.form.cmb_banco.value == '') {
            alert('SELECIONE O BANCO !')
            document.form.cmb_banco.focus()
            return false
        }
    }
    if(typeof(document.form.cmb_cheque) == 'object') {
        if(document.form.cmb_cheque.value == '') {
            alert('SELECIONE O CHEQUE !')
            document.form.cmb_cheque.focus()
            return false
        }
        if(document.form.opt_cheque_antigo.value == document.form.cmb_cheque.value) {
            alert('SELECIONE UM CHEQUE DIFERENTE DO ANTIGO !')
            document.form.cmb_cheque.focus()
            return false
        }
    }
    if(typeof(document.form.cmb_conta_corrente) == 'object') {
        if(document.form.cmb_conta_corrente.value == '') {
            alert('SELECIONE A CONTA CORRENTE !')
            document.form.cmb_conta_corrente.focus()
            return false
        }
    }
    if(typeof(document.form.chkt_predatado) == 'object') {
        if(document.form.chkt_predatado.checked == true) {
            document.form.predatado.value = 1
        }else {
            document.form.predatado.value = 0
        }
    }
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if (elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        document.form.passo.value = 3
        return true
    }
}

function submeter(valor) {
    document.form.valor_combo.value = valor
    document.form.passo.value       = 2
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
    document.form.status_ch.value = status_ch
    document.form.id_tipo_pagamento.value = id_tipo_pagamento
}

function calcular(indice, valor) {
    if(document.getElementById('chkt_conta_apagar'+indice).checked == true) {
        document.getElementById('chkt_conta_apagar'+indice).checked = false
    }else {
        document.getElementById('chkt_conta_apagar'+indice).checked = true
    }
//Aqui é a parte aonde eu abato do valor atual e acrescento no valor posterior ...
    var valor_atual     = eval(strtofloat(document.form.txt_cheque_pagto_atual.value))
    var valor_posterior = eval(strtofloat(document.form.txt_valor_posterior.value))

    document.form.txt_cheque_pagto_atual.value  = valor_atual
    document.form.txt_valor_posterior.value     = valor_posterior
//Aqui eu jogo o valor atual e posterior nas caixas de texto
    if(document.getElementById('chkt_conta_apagar'+indice).checked == true) {
        document.form.txt_cheque_pagto_atual.value  = eval(strtofloat(document.form.txt_cheque_pagto_atual.value)) - valor
        document.form.txt_valor_posterior.value     = eval(strtofloat(document.form.txt_valor_posterior.value)) + valor
    }else {
        document.form.txt_cheque_pagto_atual.value  = eval(strtofloat(document.form.txt_cheque_pagto_atual.value)) + valor
        document.form.txt_valor_posterior.value     = eval(strtofloat(document.form.txt_valor_posterior.value)) - valor
    }
    document.form.txt_cheque_pagto_atual.value      = arred(document.form.txt_cheque_pagto_atual.value, 2, 1)
    document.form.txt_valor_posterior.value         = arred(document.form.txt_valor_posterior.value, 2, 1)
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Cheque(s) p/ Desvincular
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='7'>
            Forma(s) de Pagamento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Pagto:</b>
            <?
//Verifica aqui o tipo de status_ch para poder saber se tem banco ou não
                if($_POST['valor_combo'] >= 1 && !empty($_POST['cmb_tipo_pagamento']) && $_POST['status_ch'] > 0) {
            ?>
        </td>
        <td>
            <b>Banco:</b>
        <?
                }
                if($_POST['valor_combo'] >= 2 && !empty($_POST['cmb_banco']) && $_POST['status_ch'] >= 1) {
        ?>
        </td>
        <td>
            <b>Agência: </b>
        <?
                }
                if($_POST['valor_combo'] >= 3 && !empty($_POST['cmb_agencia']) && $_POST['status_ch'] == 1) {
        ?>
        </td>
        <td>
            <b>Conta Corrente:</b>
        <?
                }else if($_POST['valor_combo'] >= 3 && !empty($_POST['cmb_agencia']) && $_POST['status_ch'] == 2) {
        ?>
        </td>
        <td>
            <b>Cheque:</b>
        <?
            $checked = ($_POST['chkt_predatado'] == 1) ? 'checked' : '';
        ?>
            <input type='checkbox' name='chkt_predatado' id='chkt_predatado' value='1' <?=$checked;?> class='checkbox'>
            <label for='chkt_predatado'>Pré-Datado</label>
        <?
                }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_pagamento' title='Selecione o Tipo de Pagamento' onchange='separar();submeter(1)' class='combo'>
            <?
                $sql = "SELECT CONCAT(id_tipo_pagamento, '|', status_ch) AS tipo_pagamento_status, pagamento 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY pagamento ";
                echo combos::combo($sql, $_POST['cmb_tipo_pagamento']);
            ?>
            </select>
        <?
            //Verifica aqui o tipo de status_ch para poder saber se tem banco ou não ...
            if($_POST['valor_combo'] >= 1 && !empty($_POST['cmb_tipo_pagamento']) && $_POST['status_ch'] > 0) {
        ?>
        </td>
        <td>
            <select name='cmb_banco' title='Selecione o Banco' onchange='submeter(2)' class='combo'>
          <?
                $sql = "SELECT DISTINCT(b.id_banco) AS id_banco, b.banco 
                        FROM `bancos` b 
                        INNER JOIN `agencias` a ON a.`id_banco` = b.`id_banco` 
                        INNER JOIN `contas_correntes` cc ON cc.`id_agencia` = a.`id_agencia` AND cc.`id_empresa` = '$id_emp2' 
                        WHERE b.`ativo` = '1' ORDER BY b.banco ";
                echo combos::combo($sql, $_POST['cmb_banco']);
        ?>
            </select>
        <?
            }
            if($_POST['valor_combo'] >= 2 && !empty($_POST['cmb_banco']) && $_POST['status_ch'] >= 1) {
        ?>
        </td>
        <td>
            <select name='cmb_agencia' title='Selecione a Agência' onchange='submeter(3)' class='combo'>
            <?
                $sql = "SELECT DISTINCT(id_agencia) AS id_agencia, CONCAT(cod_agencia, ' | ' ,nome_agencia) AS agencia 
                        FROM `agencias` 
                        WHERE `id_banco` = '$_POST[cmb_banco]' 
                        and ativo = 1 ";
                echo combos::combo($sql, $_POST['cmb_agencia']);
            ?>
            </select>
        <?
            }
            if($_POST['valor_combo'] >= 3 && !empty($_POST['cmb_agencia']) && $_POST['status_ch'] == 1) {
        ?>
        </td>
        <td>
            <select name='cmb_conta_corrente' title='Selecione a Conta Corrente' onchange='submeter(4)' class='combo'>
            <?
                $sql = "SELECT DISTINCT(id_contacorrente) AS id_conta_corrente, conta_corrente 
                        FROM `contas_correntes` 
                        WHERE `id_agencia` = '$_POST[cmb_agencia]' 
                        AND `id_empresa` = '$id_emp2' ";
                echo combos::combo($sql, $_POST['cmb_conta_corrente']);
            ?>
            </select>
        <?
            }else if($_POST['valor_combo'] >= 3 && !empty($_POST['cmb_agencia']) && $_POST['status_ch'] == 2) {
        ?>
        </td>
        <td>
            <select name='cmb_cheque' title='Selecione o Cheque' onchange='submeter(4)' class='combo'>
        <?
                if(!empty($_POST['cmb_cheque'])) $condicao = " OR (c.`status` = '1' AND c.`id_cheque` = '$_POST[cmb_cheque]') ";
        
                $sql = "SELECT DISTINCT(c.id_cheque) AS id_cheque, CONCAT(cc.`conta_corrente`, ' | ', c.num_cheque) AS cheque 
                        FROM `cheques` c 
                        INNER JOIN `taloes` t ON t.`id_talao` = c.`id_talao` 
                        INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = t.`id_contacorrente` AND cc.`ativo` = '1' AND cc.`id_agencia` = '$_POST[cmb_agencia]' AND cc.`id_empresa` = '$id_emp2' 
                        WHERE `id_cheque` <> '$id_cheque_antigo' 
                        AND (c.`status` = '0')
                        OR (c.status = '2') $condicao ";
                echo combos::combo($sql, $_POST['cmb_cheque']);
        ?>
            </select>
        <?
            }
        ?>
        </td>
    </tr>
</table>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Contas Paga(s) com o Cheque N.º 
            <font color='yellow'>
                <?=$num_cheque_antigo;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º / Conta
        </td>
        <td>
            Fornecedor / Descrição da Conta
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Valor
        </td>
        <td>
            Valor Pago
        </td>
        <td>
            Valor Reajustado
        </td>
        <td>
            Itens
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $moeda = $campos[$i]['simbolo'];//Essa variável iguala o tipo de moeda da conta à pagar ...
?>
    <tr class='linhanormal' onclick="calcular('<?=$i;?>', '<?=$campos[$i]['valor_reajustado'];?>')" align='center'>
        <td>
            <?=$campos[$i]['numero_conta'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo $moeda.number_format($campos[$i]['valor'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor_pago'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo $moeda.number_format($campos[$i]['valor_pago'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor_reajustado'], 2, ',', '.');?>
        </td>
        <td>
            <input type='checkbox' name='chkt_conta_apagar[]' id='chkt_conta_apagar<?=$i;?>' value='<?=$campos[$i]['id_conta_apagar'];?>' onclick="calcular('<?=$i;?>', '<?=$campos[$i]['valor_reajustado'];?>')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhadestaque'>
        <td colspan='2'>
            Valor Atual do Cheque N.º 
            <font color='yellow'>
                <?=$num_cheque_antigo;?>
            </font>
        </td>
        <td align='center'>
            <input type='text' name='txt_cheque_pagto_atual' value='<?=$valor_cheque_atual;?>' class='textdisabled' disabled>
        </td>
        <td colspan='3'>
            <font color='yellow'>
                Valor Descontado do Cheque N.º <?=$num_cheque_antigo;?>
            </font>
        </td>
        <td align='center'>
            <input type='text' name='txt_valor_posterior' value='0,00' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'desvincular.php<?=$parametro;?>&passo=1'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_cheque_antigo' value='<?=$id_cheque_antigo;?>'>
<input type='hidden' name='valor_combo'>
<!--Essa caixa é só para poder voltar o status do cheque 0, caso o usuário desista daquele cheque ...-->
<input type='hidden' name='hdd_cheque' value='<?=$_POST['cmb_cheque'];?>'>
<!--****************************************************-->
<input type='hidden' name='status_ch' value='<?=$_POST['status_ch'];?>'>
<input type='hidden' name='predatado'>
<input type='hidden' name='id_tipo_pagamento' value='<?=$_POST[id_tipo_pagamento];?>'>
<input type='hidden' name='passo'>
</form>
</body>
</html>
<?
    }
}else if($passo == 3) {
/*Aqui eu faço um somatório de todas as contas que utilizaram o cheque
antigo para assim poder saber qual o valor que eu vou ter que abater do cheque
antigo e somar no cheque novo e também atualizo todas as contas que utilizaram
o cheque antigo para o cheque novo*/
    $sql = "SELECT SUM(ca.valor_reajustado) AS valor_total_cheque_antigo 
            FROM `contas_apagares_quitacoes` caq 
            INNER JOIN `contas_apagares` ca ON ca.`id_conta_apagar` = caq.`id_conta_apagar` 
            WHERE caq.`id_cheque` = '$_POST[opt_cheque_antigo]' ";
    $campos                     = bancos::sql($sql);
    $valor_total_cheque_antigo  = $campos[0]['valor_total_cheque_antigo'];
    
    foreach ($_POST['chkt_conta_apagar'] as $id_conta_apagar) {
//Aqui eu busco somente o id_conta_apagar, valor da conta que foi selecionada
        $sql = "SELECT valor_reajustado 
                FROM `contas_apagares` 
                WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $valor_reajustado   = $campos[0]['valor_reajustado'];

//Aqui eu verifico se aquela conta foi paga com cheque anteriormente ...
        $sql = "SELECT id_conta_apagar_quitacao 
                FROM `contas_apagares_quitacoes` 
                WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Sim - Sempre vai ser
            $id_conta_apagar_quitacao = $campos[0]['id_conta_apagar_quitacao'];
            if(!empty($_POST['cmb_cheque'])) {//Pagamento Efetuado com Cheque ...
//Aqui é a troca do cheque antigo pelo cheque novo
                $sql = "UPDATE `contas_apagares_quitacoes` SET `id_cheque` = '$_POST[cmb_cheque]' WHERE `id_conta_apagar_quitacao` = '$id_conta_apagar_quitacao' LIMIT 1 ";
                bancos::sql($sql);
/*Só entra aqui se o cheque for pré-datado, muda o campo predatado da conta à pagar para 1, significando que aquela conta 
foi paga com cheque mas predatado ...*/
                if($_POST['predatado'] == 1) {
                    $sql = "UPDATE `contas_apagares` SET `predatado` = '1' WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
                    bancos::sql($sql);
                }
            }else {//Foi pago de alguma outra maneira, menos com Cheque ...
                /*Nessa parte mudo o Tipo de Pagamento, Banco, CC da Conta à Pagar ... e zero todas as 
                contas relacionadas com cheque, porque o usuário selecionou outra forma de pagar a conta ??? */
                $sql = "UPDATE `contas_apagares_quitacoes` SET `id_tipo_pagamento_recebimento` = '$_POST[id_tipo_pagamento]', `id_banco` = '$_POST[cmb_banco]', `id_contacorrente` = '$_POST[cmb_conta_corrente]', `id_cheque` = '0' WHERE `id_conta_apagar_quitacao` = '$id_conta_apagar_quitacao' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
    }
/*Aqui eu abato o valor das contas no cheque antigo ...*/
//Se o valor for = 0 significa que já não tem + nenhuma conta atrelada aquele cheque
    if($valor_total_cheque_antigo == $valor_reajustado) {
        //O cheque passa a ficar como sendo cancelado ...
        $sql = "UPDATE `cheques` SET `valor` = `valor` - '$valor_reajustado', `status` = '4' WHERE `id_cheque` = '$_POST[id_cheque_antigo]' LIMIT 1 ";
    }else {
        $sql = "UPDATE `cheques` SET `valor` = `valor` - '$valor_reajustado' WHERE `id_cheque` = '$_POST[id_cheque_antigo]' LIMIT 1 ";
    }
    bancos::sql($sql);
/*Aqui eu somo o valor das contas no cheque cheque, atualizo o status desse cheque para 2 para constar que esse já foi emitido 
e mudo o predatado para 1 caso esse foi passado como predatado ...*/
    if(!empty($_POST['cmb_cheque'])) {
        $sql = "UPDATE `cheques` SET `valor` = `valor` + '$valor_reajustado', `status` = '2', `predatado` = '$_POST[predatado]' WHERE `id_cheque` = '$_POST[cmb_cheque]' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'desvincular.php?valor=3'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cheque(s) p/ Desvincular ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cheque(s) p/ Desvincular
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name="opt_opcao" id='opt1' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Cheque por Número do Cheque' checked>
            <label for='opt1'>Número do Cheque</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' id='opcao' value='1' title="Consultar todos os Cheques" onclick='limpar()' class='checkbox'>
            <label for='opcao'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../../<?=$endereco_volta;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>