<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/comunicacao.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/variaveis/intermodular.php');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/estornos/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/estornos/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/estornos/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>SUA(S) CONTA(S) / QUITA��O(�ES) � PAGAR FORAM ESTORNADA(S) COM SUCESSO.</font>";

//Busca do �ltimo valor do d�lar e do euro ...
$valor_dolar        = genericas::moeda_dia('dolar');
$valor_euro         = genericas::moeda_dia('euro');

if($passo == 1) {
    /************************************Tratamentos para n�o furar o SQL************************************/
    if(!empty($chkt_somente_importacao)) {
        $condicao_importacao 	= ' AND f.id_pais <> 31 ';
        $condicao_representante = ' AND r.id_pais NOT IN (0, 31) ';
    }
    /********************************************************************************************************/

    if(empty($cmb_importacao)) 	$cmb_importacao = '%';

    $condicao_emp = " AND ca.`id_empresa` = '$id_emp2' ";
    if(!empty($txt_data_emissao_inicial)) {
        //Aqui verifica se a Data est� no formato Americano p/ n�o ter que fazer o Tratamento Novamente
        if(substr($txt_data_emissao_final, 4, 1) != '-') {
            $txt_data_emissao_inicial 		= data::datatodate($txt_data_emissao_inicial, '-');
            $txt_data_emissao_final 		= data::datatodate($txt_data_emissao_final, '-');
        }
        $condicao_emissao = " AND ca.data_emissao BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
        //Aqui eu vou utilizar nos SQls abaixo ...
    }

    if(!empty($txt_data_vencimento_inicial)) {
        //Aqui verifica se a Data est� no formato Americano p/ n�o ter que fazer o Tratamento Novamente
        if(substr($txt_data_vencimento_final, 4, 1) != '-') {
            $txt_data_vencimento_inicial 	= data::datatodate($txt_data_vencimento_inicial, '-');
            $txt_data_vencimento_final 		= data::datatodate($txt_data_vencimento_final, '-');
        }
        $condicao_vencimento = " AND ca.data_vencimento BETWEEN '$txt_data_vencimento_inicial' AND '$txt_data_vencimento_final' ";
    }

    if(!empty($txt_data_pagamento_inicial)) {
        //Aqui verifica se a Data est� no formato Americano p/ n�o ter que fazer o Tratamento Novamente ...
        if(substr($txt_data_pagamento_inicial, 4, 1) != '-') {
            $txt_data_pagamento_inicial = data::datatodate($txt_data_pagamento_inicial, '-');
            $txt_data_pagamento_final   = data::datatodate($txt_data_pagamento_final, '-');
        }
        $condicao_pagamento = " AND caq.data BETWEEN '$txt_data_pagamento_inicial' AND '$txt_data_pagamento_final' ";
    }

    $sql = "(SELECT ca.*, caq.`data`, f.razaosocial AS fornecedor, tp.`pagamento`, tp.`imagem`, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
            FROM `contas_apagares` ca 
            INNER JOIN `contas_apagares_quitacoes` caq ON caq.id_conta_apagar = ca.id_conta_apagar $condicao_pagamento 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = ca.id_fornecedor AND f.razaosocial LIKE '%$txt_fornecedor%' AND f.bairro LIKE '%$txt_bairro%' AND f.cidade LIKE '%$txt_cidade%' $condicao_importacao 
            INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
            INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
            $inner_join 
            WHERE ca.`numero_conta` LIKE '$txt_numero_conta%' 
            AND ca.`ativo` = '1' 
            AND (ca.`status` IN (1, 2) OR (ca.`status` = '2' AND ca.`predatado` = '1')) 
            $condicao_emp $condicao_emissao $condicao_vencimento 
            GROUP BY caq.`id_conta_apagar`) 
            UNION ALL 
            (SELECT ca.*, caq.data, r.nome_fantasia AS fornecedor, tp.`pagamento`, tp.`imagem`, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
            FROM `contas_apagares` ca 
            INNER JOIN `contas_apagares_quitacoes` caq ON caq.id_conta_apagar = ca.id_conta_apagar $condicao_pagamento 
            INNER JOIN `representantes` r ON r.id_representante = ca.id_representante AND r.nome_fantasia LIKE '%$txt_fornecedor%' AND r.bairro LIKE '%$txt_bairro%' AND r.cidade LIKE '%$txt_cidade%' $condicao_representante 
            INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
            INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
            $inner_join 
            WHERE ca.`numero_conta` LIKE '$txt_numero_conta%' 
            AND ca.`ativo` = '1' 
            AND (ca.`status` IN (1, 2) OR (ca.`status` = '2' AND ca.`predatado` = '1')) 
            $condicao_emp $condicao_emissao $condicao_vencimento 
            GROUP BY caq.`id_conta_apagar`) ";

    $sql_extra = "SELECT COUNT(DISTINCT(ca.id_conta_apagar)) AS total_registro 
                    FROM `contas_apagares` ca 
                    INNER JOIN `contas_apagares_quitacoes` caq ON caq.id_conta_apagar = ca.id_conta_apagar $condicao_pagamento 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = ca.id_fornecedor AND f.razaosocial LIKE '%$txt_fornecedor%' AND f.bairro LIKE '%$txt_bairro%' AND f.cidade LIKE '%$txt_cidade%' $condicao_importacao 
                    INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
                    INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
                    $inner_join 
                    WHERE ca.`numero_conta` LIKE '$txt_numero_conta%' 
                    AND ca.`ativo` = '1' 
                    AND (ca.`status` IN (1, 2) OR (ca.`status` = '2' AND ca.`predatado` = '1')) 
                    $condicao_emp $condicao_emissao $condicao_vencimento 
                    GROUP BY caq.`id_conta_apagar` 
                    UNION ALL 
                    SELECT COUNT(DISTINCT(ca.id_conta_apagar)) AS total_registro 
                    FROM `contas_apagares` ca 
                    INNER JOIN `contas_apagares_quitacoes` caq ON caq.id_conta_apagar = ca.id_conta_apagar $condicao_pagamento 
                    INNER JOIN `representantes` r ON r.id_representante = ca.id_representante AND r.nome_fantasia LIKE '%$txt_fornecedor%' AND r.bairro LIKE '%$txt_bairro%' AND r.cidade LIKE '%$txt_cidade%' $condicao_representante 
                    INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
                    INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
                    $inner_join 
                    WHERE ca.`numero_conta` LIKE '$txt_numero_conta%' 
                    AND ca.`ativo` = '1' 
                    AND (ca.`status` IN (1, 2) OR (ca.`status` = '2' AND ca.`predatado` = '1')) 
                    $condicao_emp $condicao_emissao $condicao_vencimento 
                    GROUP BY caq.`id_conta_apagar` ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
/*******************************************************************************************/
    if($linhas == 0) {
?>
    <Script Language= 'Javascript'>
        window.location = '../classes/consultar.php?id_emp2=<?=$id_emp2;?>&valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Estornar Conta(s) Paga(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Estornar Conta(s) Paga(s) 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Semana
        </td>
        <td>
            N.� / Conta
        </td>
        <td>
            Fornecedor / <br>Descri��o da Conta
        </td>
        <td>
            Data de <br/>Emiss�o
        </td>
        <td>
            Data de <br/>Vencimento
        </td>
        <td>
            Data do <br/>�ltimo Pagamento
        </td>
        <td>
            Tipo de <br/>Pagamento
        </td>
        <td>
            Valor
        </td>
        <td>
            Valor <br/>Pago
        </td>
        <td>
            <b>Valor <br/>Reajustado
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url                = "../classes/consultar.php?passo=2&id_emp2=$id_emp2&id_conta_apagar=".$campos[$i]['id_conta_apagar'];
            $moeda              = $campos[$i]['simbolo'];//Essa vari�vel iguala o tipo de moeda da conta � pagar ...
            $data_vencimento    = substr($campos[$i]['data_vencimento'], 0, 4).substr($campos[$i]['data_vencimento'], 5, 2).substr($campos[$i]['data_vencimento'], 8, 2);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location='<?=$url;?>'" width="10">
            <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location='<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                    <?=$campos[$i]['semana'];?>
                </font>
            </a>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                    <?=$campos[$i]['numero_conta'];?>
            </font>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                <?=$campos[$i]['fornecedor'];?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                <?=data::datetodata($campos[$i]['data'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
                <img src="<?='../../../../../imagem/financeiro/tipos_pag_rec/'.$campos[$i]['imagem'];?>" title="<?=$campos[$i]['pagamento'];?>" width="33" height="20" border="0">
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                if($campos[$i]['valor'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $moeda.number_format($campos[$i]['valor'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                if($campos[$i]['valor_pago'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $moeda.number_format($campos[$i]['valor_pago'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' color='#FF0000'>
            <?
                $valor_pagar = $campos[$i]['valor'] - $campos[$i]['valor_pago'];
                if($campos[$i]['predatado'] == 1) {//Est� parte � o script q exibir� o valor da conta quando o cheque for pr�-datado ...
                    $sql = "SELECT SUM(caq.valor) AS valor 
                            FROM `contas_apagares_quitacoes` caq 
                            INNER JOIN `cheques` c ON c.`id_cheque` = caq.`id_cheque` AND c.status IN (1, 2) AND c.predatado = '1' 
                            WHERE caq.`id_conta_apagar` = ".$campos[$i]['id_conta_apagar'];
                    $campos_pagamento   = bancos::sql($sql);
                    $valor              = $campos_pagamento[0]['valor'];
                    $valor_pagar+= $valor;
                }
                if($campos[$i]['id_tipo_moeda'] == 2) {//D�lar
                    $valor_pagar*= $valor_dolar;
                }else if($campos[$i]['id_tipo_moeda'] == 3) {//Euro
                    $valor_pagar*= $valor_euro;
                }
                $valor_pagar_total+= $valor_pagar;
                echo 'R$ '.number_format($valor_pagar, 2, ',', '.');
            ?>
            </font>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = '../classes/consultar.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
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
    //Aqui eu trago todos os pagamentos da conta � pagar passada por par�metro ...
    $sql = "SELECT * 
            FROM `contas_apagares_quitacoes` 
            WHERE `id_conta_apagar` = '$_GET[id_conta_apagar]' ORDER BY id_conta_apagar_quitacao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Estornar Conta(s) Paga(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor       = false
    var elementos   = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if (elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OP��O !')
        return false
    }else {
        var justificativa = prompt('DIGITE UMA JUSTIFICATIVA P/ ESTORNAR ESSE(S) PAGAMENTO(S): ')
        document.form.hdd_justificativa.value = justificativa
//Controle com a Observa��o ...
        if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
            alert('JUSTIFICATIVA INV�LIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ ESTORNAR ESSE PAGAMENTO !')
            return false
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3'?>' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Parcela(s) Quitada(s) 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <?
        $sql = "SELECT ca.`numero_conta`, ca.`id_tipo_moeda`, f.`razaosocial` AS fornecedor, CONCAT(tm.`simbolo`, '&nbsp;') AS simbolo 
                FROM `contas_apagares` ca 
                INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = ca.`id_fornecedor` 
                WHERE ca.`id_conta_apagar` = '$_GET[id_conta_apagar]' 
                UNION ALL 
                SELECT ca.`numero_conta`, ca.`id_tipo_moeda`, r.`nome_fantasia` AS fornecedor, CONCAT(tm.`simbolo`, '&nbsp;') AS simbolo 
                FROM `contas_apagares` ca 
                INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
                INNER JOIN `representantes` r ON r.`id_representante` = ca.`id_representante` 
                WHERE ca.`id_conta_apagar` = '$_GET[id_conta_apagar]' LIMIT 1 ";
        $campos_contas_apagar = bancos::sql($sql);
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='8'>
            <font color='yellow' size='2'>
                Fornecedor:
            </font>
            <?=$campos_contas_apagar[0]['fornecedor'];?>
            &nbsp;-&nbsp;
            <font color='yellow' size='2'>
            N.� da Conta:
            </font>
                <?=$campos_contas_apagar[0]['numero_conta'];?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            Tipo <br/>de Pag.
        </td>
        <td>
            Banco / <br>Conta Corrente
        </td>
        <td>
            Valor <br>do Pag.
        </td>
        <td>
            Valor <br/>Total Pago
        </td>
        <td>
            N.� Cheque
        </td>
        <td>
            Data de Pagamento
        </td>
        <td>
            Observa��o
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $moeda  = $campos_contas_apagar[0]['simbolo'];//Essa vari�vel iguala o tipo de moeda da conta � pagar ...
            
/***********************************************************************/
            $valor_pago+= $campos[$i]['valor'];
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_conta_apagar_quitacao[]' value='<?=$campos[$i]['id_conta_apagar_quitacao']?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
        <?
            $sql = "SELECT pagamento 
                    FROM `tipos_pagamentos` 
                    WHERE `id_tipo_pagamento` = '".$campos[$i]['id_tipo_pagamento_recebimento']."' LIMIT 1 ";
            $campos_pagamento = bancos::sql($sql);
            echo $campos_pagamento[0]['pagamento'];
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT CONCAT(b.`banco`, ' / ', cc.`conta_corrente`) AS dados_bancarios 
                    FROM `contas_correntes` cc 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE `id_contacorrente` = '".$campos[$i]['id_contacorrente']."' LIMIT 1 ";
            $campos_dados_bancarios = bancos::sql($sql);
            if(count($campos_dados_bancarios) == 1) echo $campos_dados_bancarios[0]['dados_bancarios'];
        ?>
        </td>
        <td>
        <?
            echo $moeda.number_format($campos[$i]['valor'], '2', ',', '.');
            //Se a Moeda da Conta for D�lar, Euro ent�o apresento o quanto que foi pago desta em R$ ...
            if($campos_contas_apagar[0]['id_tipo_moeda'] > 1) echo '<br/><font color="brown"> / <b>R$ '.number_format($campos[$i]['valor'] * $campos[$i]['valor_moeda_dia'], '2', ',', '.').'</b></font>';
        ?>
        </td>
        <td>
            <?=$moeda.number_format($valor_pago, '2', ',', '.');?>
        </td>
        <td>
        <?
            //Aqui eu verifico se tenho cheque nas tabelas relacionais de quita��es � pagares ...
            $sql = "SELECT num_cheque, status 
                    FROM `cheques` 
                    WHERE `id_cheque` = '".$campos[$i]['id_cheque']."' LIMIT 1 ";
            $campos_cheque = bancos::sql($sql);
            if(count($campos_cheque) == 1) {
                if($campos_cheque[0]['status'] == 0) {
                    $situacao = 'Aberto';
                }else if($campos_cheque[0]['status'] == 1) {
                    $situacao = 'Travado';
                }else if($campos_cheque[0]['status'] == 2) {
                    $situacao = 'Emitido';
                }else if($campos_cheque[0]['status'] == 3) {
                    $situacao = 'Compensado';
                }else if($campos_cheque[0]['status'] == 4) {
                    $situacao = 'Cancelado';
                }
                echo $campos_cheque[0]['num_cheque'].' <b>('.$situacao.')</b>';
            }
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT `identificacao`, `observacao` 
                    FROM `follow_ups` 
                    WHERE `origem` = '18' 
                    AND `identificacao` = '$_GET[id_conta_apagar]' LIMIT 1 ";
            $campos_follow_ups = bancos::sql($sql);
            echo $campos_follow_ups[0]['observacao'];
        ?>
        </td>
    </tr>
<?
	}
?>

    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location='../classes/consultar.php?<?=$parametro;?>'" class='botao'>
            <input type='submit' name='cmd_estornar' value='Estornar' title='Estornar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_conta_apagar' value='<?=$_GET[id_conta_apagar];?>'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<input type='hidden' name='hdd_justificativa'>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    foreach($_POST['chkt_conta_apagar_quitacao'] as $id_conta_apagar_quitacao) {
//Talvez eu passe essas informa��es por e-mail ...
/*************************************Dados do Pagto Estornado*************************************/
        $sql = "SELECT * 
                FROM `contas_apagares_quitacoes` 
                WHERE `id_conta_apagar_quitacao` = '$id_conta_apagar_quitacao' ORDER BY id_conta_apagar_quitacao ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) {
//Zero essas vari�veis p/ n�o dar problema na hora em que voltar do Loop ...
            $num_cheque = '';
            $dados = '';
//Aqui eu busco o n�mero de cheque na tabela relacional de contas � pagar ...
            $sql = "SELECT num_cheque 
                    FROM `cheques` 
                    WHERE `id_cheque` = '".$campos[$i]['id_cheque']."' LIMIT 1 ";
            $campos_cheques = bancos::sql($sql);
            if(count($campos_cheques) == 1) $num_cheque = $campos_cheques[0]['num_cheque'];
//Aqui verifico se tenho banco na Tabela de Quita��es � Pagar ...
            $sql = "SELECT banco 
                    FROM `bancos` 
                    WHERE `id_banco` = '".$campos[$i]['id_banco']."' LIMIT 1 ";
            $campos_bancos = bancos::sql($sql);
            if(count($campos_bancos) == 1) $dados = $campos_bancos[0]['banco'];
//Aqui verifico se tenho Conta Corrente na Tabela de Quita��es � Pagar ...
            $sql = "SELECT conta_corrente 
                    FROM `contas_correntes` 
                    WHERE `id_contacorrente` = '".$campos[$i]['id_contacorrente']."' LIMIT 1 ";
            $campos_cc = bancos::sql($sql);
            if(count($campos_cc) == 1) $dados.= $campos_cc[0]['conta_corrente'];
/***********************************************************************/
//Tipo de Pagamento da Parcela ...
            $sql = "SELECT pagamento AS tipo_pagamento_parcela 
                    FROM `tipos_pagamentos` 
                    WHERE `id_tipo_pagamento` = '".$campos[$i]['id_tipo_pagamento_recebimento']."' LIMIT 1 ";
            $campos_tipo_pagamento  = bancos::sql($sql);
            $tipo_pagamento_parcela = $campos_tipo_pagamento[0]['tipo_pagamento_parcela'];
            $valor_pagamento        = 'R$ '.number_format($campos[$i]['valor'], '2', ',', '.');
            $data_pagamento         = data::datetodata($campos[$i]['data'], '/');
            $observacao             = $campos[$i]['observacao'];
            $pagamento_estornado    = '<br><b>Tipo de Pagamento: </b>'.$tipo_pagamento_parcela.' <br><b>Banco / Conta Corrente: </b>'.$dados.' <br><b>Valor do Pag.: </b>'.$valor_pagamento.' <br><b>N.� Cheque: </b>'.$num_cheque.' <br><b>Data do Pagamento: </b>'.$data_pagamento.' <br><b>Observa��o: </b>'.$observacao.'<br>';
        }
        $retorno    = financeiros::estorno_conta_paga($id_conta_apagar_quitacao);//Fun��o que estorna ...
        $status     = $retorno['status'];
        if($status == 0) $dados_pagamentos_estornados.= $pagamento_estornado;
    }
//Se foi poss�vel o Estorno da Conta, ent�o busco alguns dados da Conta � Pagar que foi estornada ...
    if($status == 0) {
        $sql = "(SELECT ca.id_empresa, DATE_FORMAT(ca.data_vencimento, '%d/%m/%Y') AS data_vencimento, ca.valor, ca.valor_pago, ca.predatado, f.razaosocial AS fornecedor, tp.`pagamento`, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
                FROM `contas_apagares` ca 
                INNER JOIN `fornecedores` f ON f.id_fornecedor = ca.id_fornecedor 
                INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
                WHERE ca.id_conta_apagar = '$_POST[id_conta_apagar]') 
                UNION ALL 
                (SELECT ca.id_empresa, DATE_FORMAT(ca.data_vencimento, '%d/%m/%Y') AS data_vencimento, ca.valor, ca.valor_pago, ca.predatado, r.nome_fantasia AS fornecedor, tp.`pagamento`, CONCAT(tm.simbolo, '&nbsp;') AS simbolo 
                FROM `contas_apagares` ca 
                INNER JOIN `representantes` r ON r.id_representante = ca.id_representante 
                INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = ca.`id_tipo_pagamento_recebimento` 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = ca.id_tipo_moeda 
                WHERE ca.id_conta_apagar = '$_POST[id_conta_apagar]') LIMIT 1 ";
        $campos_contas_apagar   = bancos::sql($sql);
        $fornecedor             = $campos_contas_apagar[0]['fornecedor'];
        $empresa                = genericas::nome_empresa($campos_contas_apagar[0]['id_empresa']);
        $data_vencimento        = $campos_contas_apagar[0]['data_vencimento'];
        $moeda                  = $campos_contas_apagar[0]['simbolo'];
        $valor                  = $moeda.number_format($campos_contas_apagar[0]['valor'], 2, ',', '.');
        $valor_pago             = $moeda.number_format($campos_contas_apagar[0]['valor_pago'], 2, ',', '.');
        $valor_pagar            = $campos_contas_apagar[0]['valor'] - $campos_contas_apagar[0]['valor_pago'];
        $pagamento              = $campos_contas_apagar[0]['pagamento'];
//Est� parte � o script q exibir� o valor da conta quando o cheque for pr�-datado ...
        if($campos_contas_apagar[0]['predatado'] == 1) {
            $sql = "SELECT SUM(caq.valor) valor 
                    FROM `contas_apagares` ca 
                    INNER JOIN `contas_apagares_quitacoes` caq ON caq.id_conta_apagar = ca.id_conta_apagar 
                    INNER JOIN `cheques` c ON c.`id_cheque` = caq.`id_cheque` AND c.status IN (1, 2) and c.predatado = '1' 
                    WHERE ca.id_conta_apagar = '$_POST[id_conta_apagar]' ";
            $campos_pagamento = bancos::sql($sql);
            $valor_pagar+= $campos_pagamento[0]['valor'];
        }
        if($campos_pagamento[0]['id_tipo_moeda'] == 2) {//D�lar
            $valor_pagar*= $valor_dolar;
        }else if($campos_pagamento[0]['id_tipo_moeda'] == 3) {//Euro
            $valor_pagar*= $valor_euro;
        }
        $valor_pagar        = $moeda.number_format($valor_pagar, 2, ',', '.');
        $conta_estornada    = '<br><b>Empresa: </b>'.$empresa.' <br><b>Fornecedor: </b>'.$fornecedor.' <br><b>N.� da Conta: </b>'.$num_nota.' <br><b>Data de Vencimento: </b>'.$data_vencimento.' <br><b>Tipo de Pagamento: </b>'.$pagamento.' <br><b>Valor da Conta: </b>'.$valor.' <br><b>Valor Pago: </b>'.$valor_pago.' <br><b>Valor Reajustado: </b>'.$valor_pagar;
/*******************************************************************************************/
//Busca do Login que est� estornando a Conta ...
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login       = bancos::sql($sql);
        $login_estornando   = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $mensagem_email = '<font color="darkblue"><b>Conta Estornada</b></font>';
        $mensagem_email.= $conta_estornada;
        $mensagem_email.= '<br><br><font color="darkblue"><b>Pagamentos Estornados</b></font>';
        $mensagem_email.= $dados_pagamentos_estornados;
        $mensagem_email.= '<br><b>Login: </b>'.$login_estornando.' - <b>Data e Hora: </b>'.date('d/m/Y H:i:s');
        $mensagem_email.= '<br><b>Justificativa: </b>'.$_POST['hdd_justificativa'];
//Aqui eu mando um e-mail informando quem e porque est� estornando as Contas � Pagar ...
        $destino = $estornar_contas_apagar;
/*Se foi poss�vel o Estorno, ent�o o Sistema dispara um e-mail informando a Dona Sandra quem foi o 
respons�vel pelo Estorno da Conta ...*/
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Estorno de Contas � Pagar', $mensagem_email);
    }
?>
    <Script Language = 'Javascript'>
        window.location= 'consultar.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Estornar Conta(s) Paga(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Se a Data de Emiss�o estiver preenchida, ent�o eu for�o o usu�rio a preencher as 2 Datas ...
	if(document.form.txt_data_emissao_inicial.value != '' || document.form.txt_data_emissao_final.value != '') {
//Data de Emiss�o Inicial
		if(!data('form', 'txt_data_emissao_inicial', '4000', 'EMISS�O INICIAL')) {
			return false
		}
//Data de Emiss�o Final
		if(!data('form', 'txt_data_emissao_final', '4000', 'EMISS�O FINAL')) {
			return false
		}
//Compara��o com as Datas ...
		var data_emissao_inicial = document.form.txt_data_emissao_inicial.value
		var data_emissao_final = document.form.txt_data_emissao_final.value
		data_emissao_inicial = data_emissao_inicial.substr(6,4) + data_emissao_inicial.substr(3,2) + data_emissao_inicial.substr(0,2)
		data_emissao_final = data_emissao_final.substr(6,4) + data_emissao_final.substr(3,2) + data_emissao_final.substr(0,2)
		data_emissao_inicial = eval(data_emissao_inicial)
		data_emissao_final = eval(data_emissao_final)
	
		if(data_emissao_final < data_emissao_inicial) {
			alert('DATA DE EMISS�O FINAL INV�LIDA !!!\n DATA DE EMISS�O FINAL MENOR DO QUE A DATA DE EMISS�O INICIAL !')
			document.form.txt_data_emissao_final.focus()
			document.form.txt_data_emissao_final.select()
			return false
		}
/**Verifico se o intervalo entre Datas � > do que 2 anos. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
		var dias = diferenca_datas(document.form.txt_data_emissao_inicial, document.form.txt_data_emissao_final)
		if(dias > 730) {
			alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A DOIS ANOS !')
			document.form.txt_data_emissao_final.focus()
			document.form.txt_data_emissao_final.select()
			return false
		}
	}
//Se a Data de Vencimento estiver preenchida, ent�o eu for�o o usu�rio a preencher as 2 Datas ...
	if(document.form.txt_data_vencimento_inicial.value != '' || document.form.txt_data_vencimento_final.value != '') {
//Data de Vencimento Inicial
		if(!data('form', 'txt_data_vencimento_inicial', '4000', 'VENCIMENTO INICIAL')) {
			return false
		}
//Data de Vencimento Final
		if(!data('form', 'txt_data_vencimento_final', '4000', 'VENCIMENTO FINAL')) {
			return false
		}
//Compara��o com as Datas ...
		var data_vencimento_inicial = document.form.txt_data_vencimento_inicial.value
		var data_vencimento_final = document.form.txt_data_vencimento_final.value
		data_vencimento_inicial = data_vencimento_inicial.substr(6,4) + data_vencimento_inicial.substr(3,2) + data_vencimento_inicial.substr(0,2)
		data_vencimento_final = data_vencimento_final.substr(6,4) + data_vencimento_final.substr(3,2) + data_vencimento_final.substr(0,2)
		data_vencimento_inicial = eval(data_vencimento_inicial)
		data_vencimento_final = eval(data_vencimento_final)
	
		if(data_vencimento_final < data_vencimento_inicial) {
			alert('DATA DE VENCIMENTO FINAL INV�LIDA !!!\n DATA DE VENCIMENTO FINAL MENOR DO QUE A DATA DE VENCIMENTO INICIAL !')
			document.form.txt_data_vencimento_final.focus()
			document.form.txt_data_vencimento_final.select()
			return false
		}
/**Verifico se o intervalo entre Datas � > do que 2 anos. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
		var dias = diferenca_datas(document.form.txt_data_vencimento_inicial, document.form.txt_data_vencimento_final)
		if(dias > 730) {
			alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A DOIS ANOS !')
			document.form.txt_data_vencimento_final.focus()
			document.form.txt_data_vencimento_final.select()
			return false
		}
	}
//Se a Data de Pagamento estiver preenchida, ent�o eu for�o o usu�rio a preencher as 2 Datas ...
	if(document.form.txt_data_pagamento_inicial.value != '' || document.form.txt_data_pagamento_final.value != '') {
//Data de Vencimento Inicial
		if(!data('form', 'txt_data_pagamento_inicial', '4000', 'PAGAMENTO INICIAL')) {
			return false
		}
//Data de Vencimento Final
		if(!data('form', 'txt_data_pagamento_final', '4000', 'PAGAMENTO FINAL')) {
			return false
		}
//Compara��o com as Datas ...
		var data_inicial = document.form.txt_data_pagamento_inicial.value
		var data_final = document.form.txt_data_pagamento_final.value
		data_inicial = data_inicial.substr(6,4) + data_inicial.substr(3,2) + data_inicial.substr(0,2)
		data_final = data_final.substr(6,4) + data_final.substr(3,2) + data_final.substr(0,2)
		data_inicial = eval(data_inicial)
		data_final = eval(data_final)

		if(data_final < data_inicial) {
			alert('DATA DE PAGAMENTO FINAL INV�LIDA !!!\n DATA DE PAGAMENTO FINAL MENOR DO QUE A DATA DE PAGAMENTO INICIAL !')
			document.form.txt_data_pagamento_final.focus()
			document.form.txt_data_pagamento_final.select()
			return false
		}
/**Verifico se o intervalo entre Datas � > do que 2 anos. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
		var dias = diferenca_datas(document.form.txt_data_pagamento_inicial, document.form.txt_data_pagamento_final)
		if(dias > 730) {
			alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A DOIS ANOS !')
			document.form.txt_data_pagamento_final.focus()
			document.form.txt_data_pagamento_final.select()
			return false
		}
	}
}
</Script>
</head>
<body onload='document.form.txt_fornecedor.focus()'>
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Estornar Conta(s) Paga(s) 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name='txt_fornecedor' title='Digite o Fornecedor' size='40' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N�mero da Conta
        </td>
        <td>
            <input type='text' name='txt_numero_conta' title='Digite o N�mero da Conta' size='20' maxlength='18' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emiss�o
        </td>
        <td>
            <input type='text' name='txt_data_emissao_inicial' value='<?=data::adicionar_data_hora(date('d/m/Y'), -365);?>' title='Digite a Data de Emiss�o Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')"> at�&nbsp;
            <input type='text' name='txt_data_emissao_final' value='<?=date('d/m/Y');?>' title='Digite a Data de Emiss�o Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src="../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onClick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_emissao_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Vencimento
        </td>
        <td>
            <input type='text' name='txt_data_vencimento_inicial' title='Digite a Data de Vencimento Inicial' size='12' maxlength='10' onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')"> at�&nbsp;
            <input type='text' name='txt_data_vencimento_final' title='Digite a Data de Vencimento Final' size='12' maxlength='10' onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_vencimento_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data Inicial do Pagamento
        </td>
        <td>
            <input type='text' name='txt_data_pagamento_inicial' title='Digite a Data de Pagamento Inicial' size='12' maxlength='10' onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_pagamento_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">  at�&nbsp; 
            <input type='text' name='txt_data_pagamento_final' title='Digite a Data de Pagamento Final' size='12' maxlength='10' onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src='../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data_pagamento_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Semana
        </td>
        <td>
            <input type='text' name='txt_semana' title='Digite a Semana' size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Bairro
        </td>
        <td>
            <input type='text' name='txt_bairro' title='Digite o Bairro' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade
        </td>
        <td>
            <input type='text' name='txt_cidade' title='Digite a Cidade' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Conta Caixa
        </td>
        <td>
            <select name='cmb_conta_caixa' title='Selecione a Conta Caixa' class='combo'>
            <?
                //Traz somente as contas caixas do M�dulo Financeiro ...
                $sql = "SELECT id_conta_caixa_pagar, conta_caixa 
                        FROM `contas_caixas_pagares` 
                        WHERE `ativo` = '1' ORDER BY conta_caixa ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Importa��o
        </td>
        <td>
            <select name='cmb_importacao' title='Selecione a Importa��o' class='combo'>
            <?
                $sql = "SELECT id_importacao, nome 
                        FROM `importacoes` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_somente_importacao' value='1' title='Somente Importa��o' id='label1' class='checkbox'>
            <label for='label1'>Somente Importa��o</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_fornecedor.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>