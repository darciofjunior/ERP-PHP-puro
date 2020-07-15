<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');

if($id_empresa_menu == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/albafer/index.php';
}else if($id_empresa_menu == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/tool_master/index.php';
}else if($id_empresa_menu == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/cadastro/contas_automaticas/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../');

//Seleção de dados da Conta à Pagar automática passada por parâmetro ...
$sql = "SELECT caa.*, f.id_fornecedor, f.razaosocial, func.`nome`, tp.`status_db` 
        FROM `contas_apagares_automaticas` caa 
        INNER JOIN `funcionarios` func ON func.`id_funcionario` = caa.`id_funcionario` 
        INNER JOIN `produtos_financeiros_vs_fornecedor` pff ON pff.`id_produto_financeiro_vs_fornecedor` = caa.`id_produto_financeiro_vs_fornecedor` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = pff.`id_fornecedor` 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = caa.`id_tipo_pagamento_recebimento` 
        WHERE caa.`id_conta_apagar_automatica` = '$_GET[id_conta_apagar_automatica]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$id_tipo_moeda          = $campos[0]['id_tipo_moeda'];
$id_tipo_pagamento      = $campos[0]['id_tipo_pagamento_recebimento'];
$intervalo              = ($campos[0]['intervalo'] == 0) ? '' : $campos[0]['intervalo'];
$data_vencimento        = data::datetodata($campos[0]['data_vencimento'], '/');
$data_sys_funcionario   = data::datetodata(substr($campos[0]['data_sys'], 0, 10), '/').' '.substr($campos[0]['data_sys'], 11, 8).' ('.$campos[0]['nome'].')';
$id_fornecedor          = $campos[0]['id_fornecedor'];
/*********************************************************************************/
?>
<html>
<head>
<title>.:: Detalhes de Contas à Pagar Automática ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Detalhes de Conta à Pagar Automática
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_menu);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Detalhes
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font size='2' color='#6473D4'>
                <b>Fornecedor:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font size='2'>
                <?=$campos[0]['razaosocial'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2' color='#6473D4'>
                <b>N.º / Conta:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Produto(s) Financeiro(s):</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2'>
                <?=$campos[0]['numero_conta'];?>
            </font>
        </td>
        <td>
            <font size='2'>
            <?
                $sql = "SELECT CONCAT(g.`referencia`, ' - ', pf.discriminacao) AS produto 
                        FROM `produtos_financeiros_vs_fornecedor` pfv 
                        INNER JOIN `produtos_financeiros` pf ON pf.`id_produto_financeiro` = pfv.`id_produto_financeiro` 
                        INNER JOIN `grupos` g ON g.`id_grupo` = g.`id_grupo` 
                        WHERE pfv.`id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
                $campos_produto = bancos::sql($sql);
                echo $campos_produto[0]['produto'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2' color='#6473D4'>
                <b>Tipo de Pagamento:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Tipo da Moeda:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2'>
            <?
                $sql = "SELECT pagamento, status_db 
                        FROM `tipos_pagamentos` 
                        WHERE `id_tipo_pagamento` = '$id_tipo_pagamento' LIMIT 1 ";
                $campos_tipo_pagamento = bancos::sql($sql);
                echo $campos_tipo_pagamento[0]['pagamento'];
            ?>
            </font>
        </td>
        <td>
            <font size='2'>
            <?
                $sql = "SELECT CONCAT(simbolo, ' - ', moeda) AS moeda 
                        FROM `tipos_moedas` 
                        WHERE `id_tipo_moeda` = '$id_tipo_moeda' LIMIT 1 ";
                $campos_moeda = bancos::sql($sql);
                echo $campos_moeda[0]['moeda'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
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
        <td colspan='3'>
            <font size='2'>
                <?=number_format($campos[0]['valor'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=number_format($campos[0]['valor_reajustado'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2' color='#6473D4'>
                <b>Tipo de Data:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Intervalo:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2'>
            <?
                if($campos[0]['tipo_data'] == 0) {
                    echo 'Fixa';
                }else {
                    echo 'Intervalo';
                }
            ?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$intervalo;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2' color='#6473D4'>
                <b>Tipo de Automação:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Dia de Exibição:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2'>
            <?
                if($campos[0]['status'] == 0) {
                    echo 'POR DATA';
                }else if($campos[0]['status'] == 1) {
                    echo 'PAGO A CONTA ANTERIOR';
                }else if($campos[0]['status'] == 2) {
                    echo 'AMBAS ACIMA';
                }
            ?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$campos[0]['dia_exibicao'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2' color='#6473D4'>
                <b>Data de Vencimento:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Data Sys / Funcionário:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2'>
                <?=$data_vencimento;?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$data_sys_funcionario;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font size='2' color='#6473D4'><b>Observação:</b></font><font size='2'>
                <?=$campos[0]['observacao'];?>
            </font>
        </td>
    </tr>
<?
/*Tive que voltar a habilitar isso aki, porque o Pessoal registrava os dados em Compras e daí 
não aparecia para o financeiro, mas não me lembro o do porque que tinha erro ???
Consta um erro, e por isso que eu desabilitei, por causa da Dona Sandra ...*/
    if(!empty($campos[0]['banco'])) {
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Detalhes Bancários
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='2' color='#6473D4'>
                <b>Agência - Banco:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>N.º C. C.:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Correntista:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>CNPJ / CPF:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='2'>
                <?=$campos[0]['agencia'];?> - <?=$campos[0]['banco'];?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$campos[0]['num_cc'];?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$campos[0]['correntista'];?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$campos[0]['cnpj_cpf'];?>
            </font>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?
/************************Visualização das Contas à Pagar************************/
//Aqui eu zero a variável para não dar conflito com a variável lá de cima
    $valor_pagar = 0;
//Visualizando as Contas à Pagar
    $retorno = financeiros::contas_em_aberto('', '', '', '', $_GET[id_conta_apagar_automatica]);
    $linhas = count($retorno['id_contas']);
//Se encontrou uma Conta à Pagar pelo menos
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes1'); return false">
        <td height="22" align="left" colspan="2">
            <font color="yellow" size="2">(<?=$linhas;?>) </font>
            Contas à Pagar do Fornecedor:
            <font color="#FFFFFF" size="2"><?=$fornecedor;?></font>
            <font color="yellow" size="2"> - Valor Total:</font>
            <?
                for($i = 0; $i < $linhas; $i++) {
                    $sql = "SELECT ca.*, concat(tm.simbolo, '&nbsp;') as simbolo 
                            FROM `contas_apagares` ca 
                            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
                            WHERE ca.`id_conta_apagar` = ".$retorno['id_contas'][$i]." LIMIT 1 ";
                    $campos         = bancos::sql($sql);
//Essa variável iguala o tipo de moeda da conta à pagar
                    $moeda          = $campos[0]['simbolo'];
                    $valor_pagar    = $campos[0]['valor'] - $campos[0]['valor_pago'];
                    if($campos[0]['predatado'] == 1) {
//Está parte é o script q exibirá o valor da conta quando o cheque for pré-datado ...
                        $sql = "SELECT SUM(caq.valor) valor 
                                FROM `contas_apagares_quitacoes` caq 
                                INNER JOIN `cheques` c ON c.`id_cheque` = caq.`id_cheque` AND c.`status` IN (1, 2) AND c.`predatado` = '1' 
                                WHERE caq.`id_conta_apagar` = '".$retorno['id_contas'][$i]."' ";
                        $campos_pagamento   = bancos::sql($sql);
                        $valor_conta        = $campos_pagamento[0]['valor'];
                        $valor_pagar+= $valor_conta;
                    }
                    if($campos[0]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_pagar*= $valor_dolar;
                    }else if($campos[0]['id_tipo_moeda'] == 3) {//Euro
                        $valor_pagar*= $valor_euro;
                    }
                    $valor_pagar_total+= $valor_pagar;
                }
            ?>
            <font color="#FFFFFF" size="2"><?=number_format($valor_pagar_total, 2, ',', '.');?></font>
            &nbsp;
            <span id='statusdados_fornecedor'>&nbsp;</span>
            <span id='statusdados_fornecedor'>&nbsp;</span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_fornecedor por parâmetro porque utilizo dentro da Função de Apagar-->
            <iframe src = '../../../../classes/cliente/debitos_pagar.php?id_conta_apagar_automatica=<?=$id_conta_apagar_automatica;?>' name='detalhes1' id='detalhes1' marginwidth='0' marginheight='0' style='display:none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
    }
?>
</body>
</html>