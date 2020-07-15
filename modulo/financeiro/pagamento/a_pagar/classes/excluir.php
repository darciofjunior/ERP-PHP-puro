<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/variaveis/intermodular.php');
session_start('funcionarios');

if($id_emp == 1) {
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}else if($id_emp == 0) {//Todos
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = 'CONTA À PAGAR EXCLUIDA COM SUCESSO !';
$mensagem[2] = 'ESSA(S) CONTA(S) NÃO PODE(M) SER EXCLUÍDA(S) ! \nELA(S) JÁ POSSUE(M) PAGAMENTO(S) !';

//Busca do último valor do dólar e do euro
$sql = "SELECT `valor_dolar_dia`, `valor_euro_dia` 
        FROM `cambios` 
        ORDER BY `id_cambio` DESC LIMIT 1 ";
$campos         = bancos::sql($sql);
$valor_dolar 	= $campos[0]['valor_dolar_dia'];
$valor_euro 	= $campos[0]['valor_euro_dia'];

if(!empty($_POST['hdd_contas_apagares'])) {
    $vetor_contas_apagares  = explode(',', $_POST['hdd_contas_apagares']);
    $vetor_antecipacoes     = explode(',', $_POST['hdd_antecipacoes']);
    $vetor_nfes             = explode(',', $_POST['hdd_nfes']);
    $vetor_fornecedores     = explode(',', $_POST['hdd_fornecedores']);
    $vetor_numero_contas    = explode(',', $_POST['hdd_numero_contas']);

    foreach($vetor_contas_apagares as $i => $id_conta_apagar) {
        if(!empty($vetor_nfes[$i])) {//Marcação que significa que essa conta foi importada de Compras ...
            //Verifico todas as Contas à Pagares vinculadas a essa NFE de Compras passada por parâmetro ...
            $sql = "SELECT id_conta_apagar 
                    FROM `contas_apagares` 
                    WHERE `id_nfe` = '".$vetor_nfes[$i]."' ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            if($linhas > 0) {//Encontrou pelo menos 1 Conta à Pagar vinculada a NF passada por parâmetro ...
                for($l = 0; $l < $linhas; $l++) $id_contas_apagar[] = $campos[$l]['id_conta_apagar'];
            }
        }else {//Não encontrou nenhuma Conta à Pagar vinculada a NF ...
            $id_contas_apagar[] = $id_conta_apagar;
        }
    
        //Arranjo Ténico
        if(count($id_contas_apagar) > 0) {
            $vetor_contas = implode(',', $id_contas_apagar);
            $condicao_contas = (count($id_contas_apagar) == 1) ? " AND ca.id_conta_apagar = '$vetor_contas' " : " AND ca.id_conta_apagar in ($vetor_contas) ";
        }
        //Aqui eu verifico se alguma dessas contas não tem pelo menos uma parcela que foi paga antes ...
        $sql = "SELECT id_conta_apagar_quitacao 
                FROM `contas_apagares_quitacoes` 
                WHERE `id_conta_apagar` IN ($vetor_contas) LIMIT 1 ";
        $campos = bancos::sql($sql);
        //Nenhuma parcela foi paga anteriormente e sendo assim eu posso estar excluindo essas contas normalmente ...
        if(count($campos) == 0) {
            foreach($id_contas_apagar as $id_conta_apagar_loop) {
                //Apago as tabelas relacionais desse id_conta_apagar ...
                $sql = "DELETE FROM `contas_apagares_vs_pffs` WHERE `id_conta_apagar` = '$id_conta_apagar_loop' LIMIT 1 ";
                bancos::sql($sql);
                //Apago a Conta à Pagar ...
                $sql = "DELETE FROM `contas_apagares` WHERE `id_conta_apagar` = '$id_conta_apagar_loop' LIMIT 1 ";
                bancos::sql($sql);
                //Controle das Notas Fiscais - Volta o status da nota p/ 'N' para que está possa ser importada novamente ...
                if(!empty($vetor_nfes[$i])) {
                    $sql = "UPDATE `nfe` SET `importado_financeiro` = 'N' WHERE `id_nfe` = '".$vetor_nfes[$i]."' LIMIT 1 ";
                    bancos::sql($sql);
                }
                //Controle das antecipações - Volta o status_financeiro da antecipação p/ 0 para que está possa ser importada novamente ...
                if(!empty($vetor_antecipacoes[$i])) {
                    $sql = "UPDATE `antecipacoes` 
                            SET `status_financeiro` = '0' 
                            WHERE `id_antecipacao` = '".$vetor_antecipacoes[$i]."' LIMIT 1 ";
                    bancos::sql($sql);
                }
            }
            $valor = 1;
        }else {//Já foi paga uma parcela, sendo assim eu não posso excluir nenhuma Conta ...
            $valor = 2;
        }
    }
?>
    <Script Language = 'Javascript'>
        alert('<?=$mensagem[$valor];?>')
        window.opener.parent.itens.document.location = 'itens.php<?=$parametro;?>'
        window.close()
    </Script>
<?
}

$vetor_contas_apagares = explode(',', $_GET['id_conta_apagar']);
?>
<html>
<head>
<title>.:: Excluir Conta à Pagar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
for($i = 0; $i < count($vetor_contas_apagares); $i++) {
    $calculos_conta_pagar = financeiros::calculos_conta_pagar($vetor_contas_apagares[$i]);
    
    //Seleção dos dados de contas à pagar ...
    $sql = "SELECT ca.*, tp.`status_db` 
            FROM `contas_apagares` ca 
            INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
            WHERE ca.`id_conta_apagar` = '".$vetor_contas_apagares[$i]."' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $id_fornecedor          = $campos[0]['id_fornecedor'];
    $id_pedido              = $campos[0]['id_pedido'];
    $id_antecipacao         = $campos[0]['id_antecipacao'];
    $id_nfe                 = $campos[0]['id_nfe'];
    $id_representante       = $campos[0]['id_representante'];
    $id_importacao          = $campos[0]['id_importacao'];
    $id_tipo_moeda          = $campos[0]['id_tipo_moeda'];
    $id_tipo_pagamento      = $campos[0]['id_tipo_pagamento_recebimento'];
    $id_produto_financeiro  = $campos[0]['id_produto_financeiro'];
    $numero_conta           = $campos[0]['numero_conta'];
    $semana                 = $campos[0]['semana'];
    $status_db              = $campos[0]['status_db'];
    $id_tipo_pagamento_status = $campos[0]['id_tipo_pagamento_recebimento'].'|'.$status_db;
    $valor                  = $campos[0]['valor'];
    $valor_reajustado       = $campos[0]['valor_reajustado'];
    $data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');
    $data_vencimento        = data::datetodata($campos[0]['data_vencimento'], '/');
    $status_conta           = $campos[0]['status'];
    
    if($id_fornecedor > 0) {//Se existe Fornecedor para a Conta então ...
        $sql = "SELECT `razaosocial` AS fornecedor 
                FROM `fornecedores` 
                WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
        $campos_fornecedor  = bancos::sql($sql);
        $fornecedor         = $campos_fornecedor[0]['fornecedor'];
    }else {//Se não existe Fornecedor, então existe Representante ...
        $sql = "SELECT `nome_fantasia` AS fornecedor 
                FROM `representantes` 
                WHERE `id_representante` = '$id_representante' LIMIT 1 ";
        $campos_representante   = bancos::sql($sql);
        $fornecedor             = $campos_representante[0]['fornecedor'];
    }
/*********************************************************************************/
//Se o Primeiro dígito da Conta for numérico então fará o procedimento + abaixo do if ...
    $primeiro_digito = substr($numero_conta, 0, 1);
    if(is_numeric($primeiro_digito)) {
        $ultimo_digito = substr($numero_conta, strlen($numero_conta) - 1, 1);
    //Aqui faz um tratamento do Número da Nota para depois poder puxar as demais vias de duplicatas
        if($ultimo_digito == 'A' || $ultimo_digito == 'B' || $ultimo_digito == 'C') {
            $numero_conta = substr($numero_conta, 0, strlen($numero_conta) - 1);
        }else {
            $numero_conta = strtok($numero_conta, ' -');
        }
    }

    if($i > 0) {
?>
    <tr>
        <td colspan='4'>
            <hr/>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Excluir Conta à Pagar
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Detalhes
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2' color='#6473D4'>
                <b>Fornecedor:</b>
            </font>
        </td>
        <td width='217'>
            <font size='2' color='#6473D4'>
                <b>N.º / Conta:</b>
            </font>
            <font size='2'>&nbsp;</font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2'>
                <?=$fornecedor;?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$numero_conta;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2' color='#6473D4'>
                <b>Produto(s) Financeiro(s):</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Importação:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2'>
            <?
                $sql = "SELECT CONCAT(g.`referencia`, ' - ', pf.`discriminacao`) AS produto 
                        FROM `produtos_financeiros` pf 
                        INNER JOIN `grupos` g ON g.id_grupo = pf.id_grupo 
                        WHERE pf.`id_produto_financeiro` = '$id_produto_financeiro' LIMIT 1 ";
                $campos_produto = bancos::sql($sql);
                echo $campos_produto[0]['produto'];
            ?>
            </font>
            <font size='2'>&nbsp;</font>
        </td>
        <td>
            <font size='2'>
            <?
                $sql = "SELECT `nome` 
                        FROM `importacoes` 
                        WHERE `id_importacao` = '$id_importacao' LIMIT 1 ";
                $campos_importacao = bancos::sql($sql);
                echo $campos_importacao[0]['nome'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' >
        <td>
            <font size='2' color='#6473D4'>
                <b>Semana:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Data da Conta:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Data de Vencimento:</b>
            </font>
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr class='linhanormal' >
        <td>
            <font size='2'>
                <?=$semana;?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$data_emissao;?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$data_vencimento;?>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?

    if(!empty($id_pedido)) {//Seleção de alguns dados do pedido, para o caso de ser Exportação ...
        $sql = "SELECT SUBSTRING(`data_emissao`, 1, 10) AS data_emissao, `prazo_entrega`, 
                `prazo_navio`, `prazo_pgto_a`, `prazo_pgto_b`, `prazo_pgto_c` 
                FROM `pedidos` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos_pedido 	= bancos::sql($sql);
        $data_emissao 	= $campos_pedido[0]['data_emissao'];
        $data_embarque 	= $campos_pedido[0]['prazo_entrega'];
        $prazo_entrega 	= data::diferenca_data($data_emissao, $data_embarque);
        $entrega        = $prazo_entrega[0];
        $prazo_navio 	= $campos_pedido[0]['prazo_navio'];
        $prazo_pgto_a 	= $campos_pedido[0]['prazo_pgto_a'];
        $prazo_pgto_b 	= $campos_pedido[0]['prazo_pgto_b'];
        $prazo_pgto_c 	= $campos_pedido[0]['prazo_pgto_c'];
?>
    <tr class='linhanormal' >
        <td>
            <font size='2' color='#6473D4'>
                <b>Data de Embarque:</b>
            </font>
        </td>
        <td colspan='2'>
            <font size='2' color='#6473D4'>
                <b>Data de Chegada ao Porto:</b>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal' >
        <td>
            <font size='2'>
                <?=data::datetodata(substr($data_embarque, 0, 10), '/');?>
            </font>
        </td>
        <td colspan='2'>
            <font size='2'>
            <?
                $dias = $prazo_navio + $entrega;
                echo data::adicionar_data_hora(data::datetodata($data_emissao, '/'), $dias);
            ?>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='2' color='#6473D4'><b>Prazo de Venc. A:</b></font>
        </td>
        <td>
            <font size='2' color='#6473D4'><b>Prazo de Venc. B:</b></font>
        </td>
        <td>
            <font size='2' color='#6473D4'><b>Prazo de Venc. C:</b></font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='2'>
                <?=$prazo_pgto_a.' DDL - '.data::adicionar_data_hora(data::datetodata($data_embarque, '/'), $prazo_pgto_a);?>
            </font>
        </td>
        <td>
            <font size='2'>
            <?
                if(!empty($prazo_pgto_b)) {
                    echo $prazo_pgto_b.' DDL - '.data::adicionar_data_hora(data::datetodata($data_embarque, '/'), $prazo_pgto_b);
                }else {
                    echo '&nbsp;';
                }
            ?>
            </font>
        </td>
        <td>
            <font size='2'>
            <?
                if(!empty($prazo_pgto_c)) {
                    echo $prazo_pgto_c.' DDL - '.data::adicionar_data_hora(data::datetodata($data_embarque, '/'), $prazo_pgto_c);
                }else {
                    echo '&nbsp;';
                }
            ?>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td height='19'>
            <font size='2' color='#6473D4'>
                <b>Tipo de Pagamento:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Tipo da Moeda:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Valor Nacional / Estrangeiro:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Valor Reajustado:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='2'>
            <?
                $sql = "SELECT `pagamento`, `status_db` 
                        FROM `tipos_pagamentos` 
                        WHERE `id_tipo_pagamento` = '$id_tipo_pagamento' LIMIT 1 ";
                $campos_tipo_pagamento = bancos::sql($sql);
                echo $campos_tipo_pagamento[0]['pagamento'];
            ?>
            </font>
        </td>
        <td>
            <font size='2' color='red'>
            <?
                $sql = "SELECT `simbolo`, CONCAT(`simbolo`, ' - ', moeda) AS moeda 
                        FROM `tipos_moedas` 
                        WHERE `id_tipo_moeda` = '$id_tipo_moeda' LIMIT 1 ";
                $campos_moeda   = bancos::sql($sql);
                $simbolo_moeda  = $campos_moeda[0]['simbolo'];
                echo $campos_moeda[0]['moeda'];
            ?>
            </font>
        </td>
        <td>
            <font size='2' color='red'>
                <?=number_format($valor, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font size='2' color='red'>
                <?=number_format($calculos_conta_pagar['valor_reajustado'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font size='2' color='#6473D4'>
                <b>Observação:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font size='2'>
            <?
                $sql = "SELECT `observacao` 
                        FROM `follow_ups` 
                        WHERE `origem` = '18' 
                        AND `identificacao` = '".$vetor_contas_apagares[$i]."' ";
                $campos_follow_ups = bancos::sql($sql);
                $linhas_follow_ups = count($campos_follow_ups);
                for($f = 0; $f < $linhas_follow_ups; $f++) {
                    echo $campos_follow_ups[$f]['observacao'];
                    if($f + 1 != $linhas_follow_ups) echo '<br/>';//Enquanto não chegar no último registro ...
                }
            ?>
            </font>
        </td>
    </tr>
<?
//Seleção dos dados bancários e do produto financeiro - produtos financeiros fornecedor ...
    $sql = "SELECT `banco`, `agencia`, `num_cc`, `correntista`, `cnpj_cpf` 
            FROM `fornecedores_propriedades` 
            WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
    $campos_fp = bancos::sql($sql);
    if(count($campos_fp) == 1) {
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Detalhes Bancários
        </td>
    </tr>
	<tr class='linhanormal'>
		<td><font size='2' color='#6473D4'><b>Agência - Banco:</b></font></td>
		<td><font size='2' color='#6473D4'><b>N.º C. C.:</b></font> </td>
		<td><font size='2' color='#6473D4'><b>Correntista:</b></font></td>
		<td><font size='2' color='#6473D4'><b>CNPJ / CPF:</b></font> </td>
	</tr>
    <tr class='linhanormal'>
        <td>
            <font size='2'>
                <?=$campos_fp[0]['agencia'];?> - <?=$campos_fp[0]['banco'];?>
                </font>
        </td>
        <td>
            <font size='2'>
                <?=$campos_fp[0]['num_cc'];?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$campos_fp[0]['correntista'];?>
            </font>
        </td>
        <td>
            <font size='2'>
            <?
                if(!empty($campos_fp[0]['cnpj_cpf'])) {//Campo está preenchido ...
                    if(strlen($campos_fp[0]['cnpj_cpf']) == 11) {//CPF ...
                        echo substr($campos_fp[0]['cnpj_cpf'], 0, 3).'.'.substr($campos_fp[0]['cnpj_cpf'], 3, 3).'.'.substr($campos_fp[0]['cnpj_cpf'], 6, 3).'-'.substr($campos_fp[0]['cnpj_cpf'], 9, 2);
                    }else {//CNPJ ...
                        echo substr($campos_fp[0]['cnpj_cpf'], 0, 2).'.'.substr($campos_fp[0]['cnpj_cpf'], 2, 3).'.'.substr($campos_fp[0]['cnpj_cpf'], 5, 3).'/'.substr($campos_fp[0]['cnpj_cpf'], 8, 4).'-'.substr($campos_fp[0]['cnpj_cpf'], 12, 2);
                    }
                }
            ?>
            </font>
        </td>
    </tr>
<?
    }
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
    //Aqui eu verifico se essa Conta à Pagar foi gerada através de Parcelas de Financiamento de NF ...
    $sql = "SELECT DISTINCT(ca.numero_conta), ca.* 
            FROM `contas_apagares` ca 
            INNER JOIN `nfe` ON nfe.id_nfe = ca.id_nfe AND nfe.id_fornecedor = '$id_fornecedor' 
            INNER JOIN `nfe_financiamentos` nf on nf.id_nfe = nfe.id_nfe 
            WHERE ca.numero_conta LIKE '$numero_conta -%' 
            AND ca.`ativo` = '1' 
            AND ca.`id_empresa` = '$id_emp' ORDER BY ca.data_vencimento ";
    $campos_financiamento = bancos::sql($sql);
    $linhas_financiamento = count($campos_financiamento);
    if($linhas_financiamento == 0) {//Significa que não foi feito nenhum Financiamento p/ esta NF ...
        if($id_nfe > 0) {//Essa marcação significa que essa que foi importada, é pertencente a NFE de Compras ...
            //Tratamento apenas para garantir as Contas que eu estou tentando excluir realmente ...
            $clausula = "'".$numero_conta."', '".$numero_conta."A', '".$numero_conta."B', '".$numero_conta."C'";
            //Aki lista todas as contas que possui o mesmo número, da mesma Empresa ...
            $sql = "SELECT * 
                    FROM `contas_apagares` 
                    WHERE (numero_conta IN ($clausula) OR `numero_conta` LIKE '$numero_conta -%') 
                    AND `ativo` = '1' 
                    AND `id_empresa` = '$id_emp' ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
        }
    }
//Se existir Mais de 1 via Financiamento ou mais de 1 via do Modo Antigo - A, B, C ...
    if($linhas_financiamento > 0 || $linhas > 0) {
/**********************/
//Variável utilizada mais abaixo, para controle dos Avisos ...
//Vetor de Status
        $status = array('');
/**********************/
?>
</table>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td>
            N.&ordm; da Duplicata
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Data de Vencimento
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
    </tr>
<?
        if($linhas_financiamento > 0) {//Encontrou pelo menos 1 Financiamento ...
            for($j = 0; $j < $linhas_financiamento; $j++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
<?
                if($campos_financiamento[$j]['status'] > 0) {//Quer dizer que já foi recebido algo dessa conta
                    $status[$j] = 1;
?>
            <a href="javascript:nova_janela('../../detalhes.php?id_conta_apagar=<?=$campos_financiamento[$j]['id_conta_apagar'];?>', 'DETALHES', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Conta à Pagar' class='link'>
                <?=$campos_financiamento[$j]['numero_conta'];?>
            </a>
<?
                }else {
                    echo $campos_financiamento[$j]['numero_conta'];
                }
?>
        </td>
        <td>
            <?=data::datetodata($campos_financiamento[$j]['data_emissao'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos_financiamento[$j]['data_vencimento'], '/');?>
        </td>
        <td align='right'>
            <?=$simbolo_moeda.' '.number_format($campos_financiamento[$j]['valor'], 2, ',', '.');?>
        </td>
        <td>
        <?
            if($campos_financiamento[$j]['valor_pago'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo $simbolo_moeda.number_format($campos_financiamento[$j]['valor_pago'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
                $valor_pagar_conta = $campos_financiamento[$j]['valor'] - $campos_financiamento[$j]['valor_pago'];
                if($campos_financiamento[$j]['id_tipo_moeda'] == 2) {//Dólar
                    $valor_pagar_conta*= $valor_dolar;
                }else if($campos_financiamento[$j]['id_tipo_moeda'] == 3) {//Euro
                    $valor_pagar_conta*= $valor_euro;
                }
                echo 'R$ '.number_format($valor_pagar_conta, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
            }
        }else {//Significa que não foi feito nenhum Financiamento p/ esta NF ...
            for($j = 0; $j < $linhas; $j++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
<?
            if($campos[$j]['status'] > 0) {//Quer dizer que já foi recebido algo dessa conta
                $status[$j] = 1;
?>
            <a href="javascript:nova_janela('../../detalhes.php?id_conta_apagar=<?=$campos[$j]['id_conta_apagar'];?>', 'DETALHES', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Conta à Receber' class='link'>
                <?=$campos[$j]['numero_conta'];?>
            </a>
<?
            }else {
                echo $campos[$j]['numero_conta'];
            }
?>
        </td>
        <td>
            <?=data::datetodata($campos[$j]['data_emissao'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$j]['data_vencimento'], '/');?>
        </td>
        <td align='right'>
            <?=$simbolo_moeda.' '.number_format($campos[$j]['valor'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            if($campos[$j]['valor_pago'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo $simbolo_moeda.number_format($campos[$j]['valor_pago'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            $valor_pagar_conta = $campos[$j]['valor'] - $campos[$j]['valor_pago'];
            if($campos[$j]['id_tipo_moeda'] == 2) {//Dólar
                $valor_pagar_conta*= $valor_dolar;
            }else if($campos[$j]['id_tipo_moeda'] == 3) {//Euro
                $valor_pagar_conta*= $valor_euro;
            }
            echo 'R$ '.number_format($valor_pagar_conta, 2, ',', '.');
        ?>
        </td>
    </tr>
<?
            }
        }
?>
</table>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
    }
//Aqui eu verifico se existe algum motivo o do porque não posso excluir a Conta à Pagar ...
    if($linhas_financiamento > 0) {//Encontrou pelo menos 1 Financiamento ...
        for($j = 0; $j < $linhas_financiamento; $j++) {
            if($status[$j] == 1) $atencao = 1;
        }
    }else {//Significa que não foi feito nenhum Financiamento p/ esta NF ...
        for($j = 0; $j < $linhas; $j++) {
            if($status[$j] == 1) $atencao = 1;
        }
    }
//Significa que existem motivos para não estar excluindo a Conta à Receber
    if($atencao == 1) {
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            Duplicata desta Nota Fiscal não pode ser excluída. Motivos:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font color='red'><b>
<?
//Aqui eu verifico se existe algum motivo o do porque não posso excluir a Conta à Pagar ...
        if($linhas_financiamento > 0) {//Encontrou pelo menos 1 Financiamento ...
            for($j = 0; $j < $linhas_financiamento; $j++) if($status[$j] == 1) echo '-> '.$campos_financiamento[$j]['numero_conta'].' possui parcela(s) paga(s).';
        }else {//Significa que não foi feito nenhum Financiamento p/ esta NF ...
            for($j = 0; $j < $linhas; $j++) if($status[$j] == 1) echo '-> '.$campos[$j]['numero_conta'].' possui parcela(s) paga(s).';
        }
?>
            </b></font>
        </td>
    </tr>
<?
    }
//Significa que existem motivos o do porque não posso excluir a Conta à Pagar ...
    if($atencao == 1) {
        $disabled   = 'disabled';
        $class      = 'disabled';
    }else {
        $class      = 'botao';
    }
    /**************************************************************************/
    //Aqui eu preparo as variáveis p/ abastecer os hiddens mais abaixo ...
    if(!empty($vetor_contas_apagares[$i])) $hdd_contas_apagares.= $vetor_contas_apagares[$i].', ';
    if(!empty($id_antecipacao)) $hdd_antecipacoes.= $id_antecipacao.', ';
    if(!empty($id_nfe)) $hdd_nfes.= $id_nfe.', ';
    if(!empty($id_fornecedor)) $hdd_fornecedores.= $id_fornecedor.', ';
    if(!empty($numero_conta)) $hdd_numero_contas.= $numero_conta.', ';
    /**************************************************************************/
}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='<?=$class;?>' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
<!--***************Tive que colocar esses hiddens aqui por causa do Loop***************-->
<input type='hidden' name='hdd_contas_apagares' value='<?=substr($hdd_contas_apagares, 0, strlen($hdd_contas_apagares) - 2);?>'>
<input type='hidden' name='hdd_antecipacoes' value='<?=substr($hdd_antecipacoes, 0, strlen($hdd_antecipacoes) - 2);?>'>
<input type='hidden' name='hdd_nfes' value='<?=substr($hdd_nfes, 0, strlen($hdd_nfes) - 2);?>'>
<input type='hidden' name='hdd_fornecedores' value='<?=substr($hdd_fornecedores, 0, strlen($hdd_fornecedores) - 2);?>'>
<input type='hidden' name='hdd_numero_contas' value='<?=substr($hdd_numero_contas, 0, strlen($hdd_numero_contas) - 2);?>'>
<!--***********************************************************************************-->
</form>
    <center>
        <p/>
        <font color='darkgreen' face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='4'>
            <b>Total de Registro(s): <?=count($vetor_contas_apagares);?></b>
        </font>
    </center>
</body>
</html>
<?$qtde_vias = ($linhas_financiamento > 0) ? $linhas_financiamento : $linhas;//Vou utilizar essa variável em JavaScript ...?>
<!--Joguei essa function aqui em baixo, por causa da variável em PHP $linhas que só carreguei mais
abaixo do <head>-->
<Script Language = 'JavaScript'>
function validar() {
    var linhas = eval('<?=$qtde_vias;?>')
    if(linhas == 1) {//Nota Fiscal que possui apenas 1 via ...
        var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA EXCLUIR ESSA DUPLICATA ?')
    }else {//Nota Fiscal que possui mais de 1 via ...
        var resposta = confirm('TODAS AS DUPLICATAS DESSA NOTA FISCAL SERÃO EXCLUÍDAS.\nVOCÊ TEM CERTEZA DE QUE DESEJA EXCLUIR ?')
    }
//Se o usuário desejar excluir realmente a Conta então ...
    if(resposta == true) {
        return true
    }else {
        return false
    }
}
</Script>