<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/genericas.php');

session_start('funcionarios');
if($id_emp == 1) {
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');
$mensagem[1] = "<font class='confirmacao'>CONTA À PAGAR INCLUIDA COM SUCESSO.</font>";

if($passo == 1) {
    $dia                    = substr($txt_data_vencimento, 0, 2);
    $mes                    = substr($txt_data_vencimento, 3, 2);
    $ano                    = substr($txt_data_vencimento, 6, 4);
    $semana                 = data::numero_semana($dia, $mes, $ano);
    $txt_observacao         = strtolower($txt_observacao);
    $txt_data_emissao       = data::datatodate($txt_data_emissao, '-');
    $txt_data_vencimento    = data::datatodate($txt_data_vencimento, '-');
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_importacao         = (!empty($_POST[cmb_importacao])) ? "'".$_POST[cmb_importacao]."'" : 'NULL';

    $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_funcionario`, `id_fornecedor`, `id_antecipacao`, `id_empresa`, `id_importacao`, `id_tipo_moeda`, `id_grupo`, `id_produto_financeiro`, `perc_uso_produto_financeiro`, `semana`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `id_tipo_pagamento_recebimento`, `numero_conta`, `valor`, `status`, `ativo`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_POST[id_fornecedor]', '$_POST[id_antecipacao]', '$id_emp', $cmb_importacao, '$_POST[cmb_tipo_moeda]', '$_POST[cmb_grupo]', NULL, '100', '$semana', '$txt_data_emissao', '$txt_data_vencimento', '$txt_data_vencimento', '$_POST[id_tipo_pagamento]', '".$_POST['id_pedido'].'/'.$_POST['id_antecipacao']."', '$_POST[txt_valor]', '0', '1') ";
    bancos::sql($sql);
    $id_conta_apagar = bancos::id_registro();

    //Registrando Follow-UP(s) ...
    if(!empty($_POST['txt_observacao'])) {
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$id_conta_apagar', '18', '".strtolower($_POST['txt_observacao'])."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
    financeiros::inserir_dados_bancarios($_POST['id_antecipacao'], 2, $id_conta_apagar);

    //Mudo o Status da Antecipação p/ 1, porque a mesma já foi cadastrada ...
    $sql = "UPDATE `antecipacoes` SET `status_financeiro` = '1' WHERE id_antecipacao = '$id_antecipacao' LIMIT 1 ";
    bancos::sql($sql);
    
    financeiros::atualizar_data_alterada($id_conta_apagar, 'A');
?>
    <Script Language = 'JavaScript'>
        window.location = 'consultar_antecipacao.php?valor=1'
    </Script>
<?
}else {
//Aqui verifica se já foi inserido a conta à pagar antes para poder desabilitar o botão de submit lá em baixo ...
    if($valor == 1) $disabled = 'disabled';
    //Seleciona os dados da antecipação com o id da antecipação ...
    $sql = "SELECT a.*, p.`id_tipo_moeda`, f.`razaosocial`, f.`id_fornecedor`, tp.`status_db` 
            FROM `antecipacoes` a 
            INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = a.`id_tipo_pagamento_recebimento` 
            INNER JOIN `pedidos` p ON p.`id_pedido` = a.`id_pedido` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
            WHERE a.`id_antecipacao` = '$_GET[id_antecipacao]' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $id_pedido                  = $campos[0]['id_pedido'];
    $id_grupo                   = $campos[0]['id_grupo'];
    $id_fornecedor              = $campos[0]['id_fornecedor'];
    $razaosocial                = $campos[0]['razaosocial'];
    $status_db                  = $campos[0]['status_db'];
    $id_tipo_pagamento          = $campos[0]['id_tipo_pagamento_recebimento'];
    $id_tipo_pagamento_status   = $campos[0]['id_tipo_pagamento_recebimento'].'|'.$status_db;
    $valor_conta                = $campos[0]['valor'];
    $data_emissao               = substr($campos[0]['data_sys'],0,10);
    $data_vencimento            = $campos[0]['data'];
    $observacao                 = $campos[0]['observacao'];
    $id_tipo_moeda              = $campos[0]['id_tipo_moeda'];

    //Verifico se existe Importação p/ o Pedido ...
    $sql = "SELECT `id_importacao` 
            FROM `pedidos` 
            WHERE `id_pedido` = '$id_pedido' 
            AND `id_importacao` > '0' LIMIT 1 ";
    $campos_importacao = bancos::sql($sql);
    if(count($campos_importacao) == 1) $id_importacao = $campos_importacao[0]['id_importacao'];

    //Aqui eu puxo o último valor do dólar e do euro cadastrado
    $sql = "SELECT `valor_dolar_dia`, `valor_euro_dia`, `data` 
            FROM `cambios` 
            ORDER BY `id_cambio` DESC LIMIT 1 ";
    $campos_cambios	= bancos::sql($sql);
    $valor_dolar 	= $campos_cambios[0]['valor_dolar_dia'];
    $valor_euro 	= $campos_cambios[0]['valor_euro_dia'];
    $data_cadastro 	= data::datetodata($campos_cambios[0]['data'], '/');
?>
<html>
<head>
<title>.:: Liberar Antecipação de Compras ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function separar() {
	var tipo_pagamento = document.form.cmb_tipo_pagamento.value
	var achou = 0, id_tipo_pagamento = '', status_db = ''
	for(i = 0; i < tipo_pagamento.length; i++) {
		if(tipo_pagamento.charAt(i) == '|') {
			achou = 1
		}else {
			if(achou == 0) {
				id_tipo_pagamento = id_tipo_pagamento + tipo_pagamento.charAt(i)
			}else {
				status_db = status_db + tipo_pagamento.charAt(i)
			}
		}
	}
	document.form.id_tipo_pagamento.value = id_tipo_pagamento
	document.form.status_db.value = status_db
}

function calcular() {
	var tipo_moeda = document.form.cmb_tipo_moeda.value
	document.form.valor_aux.value = document.form.txt_valor.value
	limpeza_moeda('form', 'valor_aux,')
	var valor = document.form.valor_aux.value
	if(tipo_moeda == 2) {
		document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_dolar;?>')
	}else if(tipo_moeda == 3) {
		document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_euro;?>')
	}else {
		document.form.txt_valor_reajustado.value = valor * 1
	}
	document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 2, 1)
}

function validar() {
	//Aki desabilita os campos para poder gravar no BD ...
	document.form.txt_valor.disabled 			= false
	document.form.txt_valor_reajustado.disabled = false
	document.form.txt_data_emissao.disabled 	= false
	document.form.txt_data_vencimento.disabled 	= false
	//Aqui é para não atualizar o frame de Itens abaixo desse Pop-UP ...
	document.form.nao_atualizar.value = 1
	return limpeza_moeda('form', 'txt_valor, txt_valor_reajustado, ')
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
	if(document.form.nao_atualizar.value == 0) {
		window.opener.parent.itens.document.location = '../itens.php'+window.opener.parent.itens.document.form.parametro.value
	}
}
</Script>
</head>
<body onload='calcular();separar()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onSubmit='return validar()'>
<input type='hidden' name="id_antecipacao" value="<?=$id_antecipacao;?>">
<input type='hidden' name="id_pedido" value="<?=$id_pedido;?>">
<input type="hidden" name="id_fornecedor" value="<?=$id_fornecedor;?>">
<!--Aqui precisa por causa da função do JavaScript-->
<input type='hidden' name='id_tipo_pagamento' value="<?=$id_tipo_pagamento;?>">
<input type='hidden' name='status_db' value="<?=$status_db;?>">
<input type='hidden' name="valor_aux">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<!--**********************************************-->
<table width='90%' cellspacing='1' cellpadding='1' border='0' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Liberar Antecipação de Compras
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Fornecedor:</b>
        </td>
        <td>
            <b>N.º Pedido / N.º Antecipa&ccedil;&atilde;o: </b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-2'>
                <?=$razaosocial;?>
            </font>
        </td>
        <td>
            <?=$id_pedido.' / '.$id_antecipacao;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Grupo:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <select name='cmb_grupo' title='Grupo' onfocus='document.form.cmd_salvar.focus()' class='textdisabled'>
            <?
                $sql = "SELECT `id_grupo`, `nome` 
                        FROM `grupos` 
                        WHERE `ativo` = '1' ORDER BY `nome` " ;
                echo combos::combo($sql, $id_grupo);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo Pagamento:</b>
        </td>
        <td>
            Tipo da Moeda:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_pagamento' title='Selecione o Tipo de Pagamento' onfocus='document.form.cmd_salvar.focus()' class='textdisabled'>
            <?
                $sql = "SELECT CONCAT(`id_tipo_pagamento`, '|', `status_db`) AS tipo, `pagamento` 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY `pagamento` ";
                echo combos::combo($sql, $id_tipo_pagamento_status);
            ?>
            </select>
        </td>
        <td>
            <select name='cmb_tipo_moeda' title='Tipo de Moeda' onfocus='document.form.cmd_salvar.focus()' class='textdisabled'>
            <?
                $sql = "SELECT `id_tipo_moeda`, CONCAT(`simbolo`, ' - ', `moeda`) AS moeda 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ORDER BY `moeda` ";
                echo combos::combo($sql, $id_tipo_moeda);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                Valor Dólar:
            </font>
            <?='R$ '.number_format($valor_dolar, 4, ',', '.');?>
        </td>
        <td>
            <font color='blue'>
                Valor Euro:
            </font>
            <?='R$ '.number_format($valor_euro, 4, ',', '.');?>
        </td>
    </tr>
<?
    if(isset($id_importacao)) {//Essa linha de importação só aparecerá se exister importação para essa nota ...
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Importação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <select name='cmb_importacao' title='Selecione uma Importação' onfocus='document.form.cmd_salvar.focus()' class='textdisabled'>
            <?
                $sql = "SELECT `id_importacao`, `nome` 
                        FROM `importacoes` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql, $id_importacao);
            ?>
            </select>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td>
            <b>Valor Nacional / Estrangeiro:</b>
        </td>
        <td>
            Valor Reajustado:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_valor' value='<?=number_format($valor_conta, '2', ',', '');?>' title='Valor' size='20' maxlength='15' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_valor_reajustado' title='Valor Reajustado' size='20' maxlength='15' class='textdisabled' disabled> em Reais
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data da Antecipação:</b>
        </td>
        <td>
            <b>Data de Vencimento:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=data::datetodata($data_emissao, '/');?>' title='Data de Emissão' maxlength='10' size='20' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_data_vencimento' value='<?=data::datetodata($data_vencimento, '/');?>' title='Data de Vencimento' maxlength='10' size='20' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Observação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' title='Digite a Observação' rows='4' cols='95' onfocus='document.form.cmd_salvar.focus()' class='textdisabled'><?=$observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="document.form.nao_atualizar.value = 1;window.location = 'consultar_antecipacao.php?passo=2<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');calcular();separar()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>