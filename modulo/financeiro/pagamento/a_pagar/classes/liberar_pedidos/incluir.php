<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');
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

//Busca de Algumas Variáveis que serão utilizadas mais abaixo ...
$fator_custo_importacao = genericas::variavel(1);
$valor_dolar_dia        = genericas::moeda_dia('dolar');
$valor_euro_dia         = genericas::moeda_dia('euro');

if(!empty($_POST['id_pedido'])) {
    $dia 	= substr($_POST['txt_data_vencimento'], 0, 2);
    $mes        = substr($_POST['txt_data_vencimento'], 3, 2);
    $ano        = substr($_POST['txt_data_vencimento'], 6, 4);
    $semana 	= data::numero_semana($dia, $mes, $ano);

    $data_emissao       = data::datatodate($_POST['txt_data_emissao'], '-');
    $data_vencimento    = data::datatodate($_POST['txt_data_vencimento'], '-');
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_importacao     = (!empty($_POST[cmb_importacao])) ? "'".$_POST[cmb_importacao]."'" : 'NULL';
	
    $sql = "INSERT INTO `contas_apagares` (`id_conta_apagar`, `id_funcionario`, `id_fornecedor`, `id_pedido`, `id_importacao`, `id_empresa`, `id_tipo_moeda`, `id_grupo`, `id_produto_financeiro`, `perc_uso_produto_financeiro`, `numero_conta`, `semana`, `previsao`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `id_tipo_pagamento_recebimento`, `valor`, `status`, `ativo`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_POST[cmb_despachante]', '$_POST[id_pedido]', $cmb_importacao, '$id_emp', '$_POST[cmb_tipo_moeda]', '$_POST[cmb_grupo]', NULL, '100', '$_POST[txt_conta]', '$semana', '1', '$data_emissao', '$data_vencimento', '$data_vencimento', '$_POST[id_tipo_pagamento]', '$_POST[txt_valor]', '0', '1') ";
    bancos::sql($sql);
    $id_conta_apagar = bancos::id_registro();
    //Registrando Follow-UP(s) ...
    if(!empty($_POST['txt_observacao'])) {
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$id_conta_apagar', '18', '".strtolower($_POST['txt_observacao'])."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
    
    financeiros::atualizar_data_alterada($id_conta_apagar, 'A');
?>
    <Script Language = 'JavaScript'>
        window.opener.parent.itens.document.form.recarregar.value = 1
        window.location = 'consultar_pedido.php?valor=1'
    </Script>
<?
}

/*Aqui verifica se já foi inserido a conta à pagar antes para poder desabilitar
o botão de submit lá em baixo*/
if($valor == 1) $disabled = 'disabled';

//Seleciona os dados do fornecedor com o id do pedido ...
$sql = "SELECT f.razaosocial, p.* 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
        WHERE p.`id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
$campos 		= bancos::sql($sql);
$status_db 		= $campos[0]['status_db'];
$prazoa 		= $campos[0]['prazo_pgto_a'];
$prazob 		= $campos[0]['prazo_pgto_b'];
$prazoc 		= $campos[0]['prazo_pgto_c'];
$prazo_viagem_navio     = $campos[0]['prazo_navio'];
$id_grupo 		= $campos[0]['id_grupo'];
$id_tipo_moeda          = $campos[0]['id_tipo_moeda'];
$razaosocial            = $campos[0]['razaosocial'];
$num_nota 		= $campos[0]['num_nota'];

$prazo_entrega          = data::diferenca_data(substr($campos[0]['data_emissao'],0,10), $campos[0]['prazo_entrega']);
$entrega 		= $prazo_entrega[0];

$data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');
$data_entrega           = data::datetodata($campos[0]['prazo_entrega'], '/');
//Aqui adiciona os 3 dias padrão + o valor do navio + o prazo de entrega
$soma_prazo             = 3 + (integer)$prazo_viagem_navio + (integer)$entrega;
$data_vencimento        = data::adicionar_data_hora($data_emissao, $soma_prazo);
$observacao             = $campos[0]['observacao'];

//Seleção dos dados da importação com o id do pedido
$sql = "SELECT id_importacao 
        FROM `pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' 
        AND `id_importacao` > '0' LIMIT 1 ";
$campos_importacao = bancos::sql($sql);
if(count($campos_importacao) == 1) {
    $sql = "SELECT nome 
            FROM `importacoes` 
            WHERE `id_importacao` = '".$campos_importacao[0]['id_importacao']."' LIMIT 1 ";
    $campos_nome    = bancos::sql($sql);
    $nome           = $campos_nome[0]['nome'];
}
$conta = (!empty($nome)) ? $nome. ' - '.$_GET['id_pedido'] : 'Numerário - '.$_GET['id_pedido'];

//Busca do valor_total_do_pedido, esse é a somatória do valor total do item que já é a multiplicação da Qtde vs o Pço Unit. ...
$sql = "SELECT qtde, preco_unitario, ipi 
        FROM `itens_pedidos` 
        WHERE `id_pedido` = '$_GET[id_pedido]' ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
if($linhas_itens > 0) for($i = 0; $i < $linhas_itens; $i ++) $valor_total+= ($campos_itens[$i]['qtde'] * $campos_itens[$i]['preco_unitario']) + ((($campos_itens[$i]['qtde'] * $campos_itens[$i]['preco_unitario']) * $campos_itens[$i]['ipi']) / 100);
$valor_total*= ($fator_custo_importacao - 1);//Aqui é o cálculo do valor com o valor do fator
?>
<html>
<head>
<title>.:: Liberar Pedido de Compras ::.</title>
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
    var tipo_moeda  = document.form.cmb_tipo_moeda.value
    var valor       = eval(strtofloat(document.form.txt_valor.value))
    if(tipo_moeda == 2) {//Dólar ...
        document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_dolar_dia;?>')
    }else if(tipo_moeda == 3) {//Euro ...
        document.form.txt_valor_reajustado.value = valor * eval('<?=$valor_euro_dia;?>')
    }else {//Reais ...
        document.form.txt_valor_reajustado.value = valor
    }
    document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 2, 1)
}

function validar() {
//Despachante ...
    if(!combo('form', 'cmb_despachante', '', 'SELECIONE UM DESPACHANTE !')) {
        return false
    }
//Tipo de Pagamento ...
    if(!combo('form', 'cmb_tipo_pagamento', '', 'SELECIONE UM TIPO DE PAGAMENTO !')) {
        return false
    }
//Grupo ...
    if(!combo('form', 'cmb_grupo', '', 'SELECIONE O GRUPO !')) {
        return false
    }
//Tipo de Moeda ...
    if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DA MOEDA !')) {
        return false
    }
//Valor ...
    if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
        return false
    }
//Data de Emissão ...
    if(!data('form', 'txt_data_emissao', '4000', 'EMISSÃO')) {
        return false
    }
//Data de Vencimento ...
    if(!data('form', 'txt_data_vencimento', '4000', 'VENCIMENTO')) {
        return false
    }
//Aki desabilita os campos para poder gravar no BD
    document.form.txt_valor_reajustado.disabled = false
    document.form.cmb_tipo_pagamento.disabled   = false
    if(typeof(document.form.cmb_importacao) == 'object') document.form.cmb_importacao.disabled = false
//Aqui é para não atualizar o frame de Itens abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    return limpeza_moeda('form', 'txt_valor, txt_valor_reajustado, ')
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.parent.itens.document.location = '../itens.php'+window.opener.parent.itens.document.form.parametro.value
}
</Script>
</head>
<body onload='calcular()' onunload="atualizar_abaixo()">
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()";>
<input type='hidden' name='id_pedido' value="<?=$_GET['id_pedido'];?>">
<!--Aqui precisa por causa da função do JavaScript-->
<input type='hidden' name='id_tipo_pagamento'>
<input type='hidden' name='status_db'>
<input type='hidden' name='txt_conta' value="<?=$conta;?>">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<!--**********************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Liberar Pedido de Compras 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Despachante:</b>
        </td>
        <td>
            <b>N.º da Conta / Pedido:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-2'>
                <select name="cmb_despachante" title="Selecione o Despachante" class='combo'>
                <?
                    $sql = "SELECT id_fornecedor, razaosocial 
                            FROM `fornecedores` 
                            WHERE `despachante` = 'S' ORDER BY razaosocial ";
                    echo combos::combo($sql);
                ?>
                </select>
                <br>
                <?=$razaosocial;?>
            </font>
        </td>
        <td>
            <?=$conta;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Pagamento:</b>
            &nbsp;<input type="checkbox" name="chkt_previsao" value="1" onclick="document.form.chkt_previsao.checked = true" id="label" class="checkbox" checked>
            <label for="label">Previsão</label>
        </td>
        <td>
            <b>Grupo:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name="cmb_tipo_pagamento" title="Selecione o Tipo de Pagamento" onchange="separar()" class='combo'>
            <?
                $sql = "SELECT CONCAT(id_tipo_pagamento, '|', status_db) AS tipo, pagamento 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY pagamento ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
        <td>
            <select name="cmb_grupo" title='Grupo' class='combo'>
            <?
                $sql = "SELECT id_grupo, nome 
                        FROM `grupos` 
                        WHERE ativo = '1' ORDER BY nome " ;
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
<?
//Essa coluna de importação só aparecerá se exister importação para esse pedido
	
?>
    <tr class='linhanormal'>
        <td>
            <b>Tipo da Moeda:</b>
        </td>
        <td>
            <b>Importação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_moeda' title='Selecione o Tipo de Moeda' onchange='calcular()' class='combo'>
            <?
                $sql = "SELECT id_tipo_moeda, CONCAT(simbolo, ' - ', moeda) AS moeda 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, $id_tipo_moeda);
            ?>
            </select>
        </td>
        <td>
        <?
            //Se foi importada alguma importação no Pedido, mostro o nome da mesma ...
            if($campos_importacao[0]['id_importacao'] > 0) {
        ?>
            <select name='cmb_importacao' title='Selecione uma Importação' class='textdisabled' disabled>
            <?
                $sql = "SELECT id_importacao, nome 
                        FROM `importacoes` 
                        WHERE `id_importacao` = '".$campos_importacao[0]['id_importacao']."' LIMIT 1 ";
                echo combos::combo($sql, $campos_importacao[0]['id_importacao']);
            ?>
            </select>
        <?
            }else {
                echo '<font color="red">S/ IMPORTAÇÃO NO PEDIDO !!!</font>';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                Valor Dólar:
            </font>
            <?='R$ '.number_format($valor_dolar_dia, 4, ',', '.');?>
        </td>
        <td>
            <font color='blue'>
                Valor Euro:
            </font>
            <?='R$ '.number_format($valor_euro_dia, 4, ',', '.');?>
        </td>
    </tr>
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
            <input type='text' name='txt_valor' value="<?=number_format($valor_total, '2', ',', '');?>" title='Digite o Valor' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
            <marquee width='150'>
                <font color='darkgreen'>
                    <b>VALOR CORRIGIDO PELO FATOR DE IMPORTAÇÃO.</b>
                </font>
            </marquee>
        </td>
        <td>
            <input type='text' name='txt_valor_reajustado' title='Digite o Valor Reajustado' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>em Reais
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emissão:</b>
        </td>
        <td>
            <b>Data de Vencimento:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=$data_emissao;?>' title='Digite a Data de Emissão' onkeyup="verifica(this, 'data', '', '', event)" maxlength='10' size='20' class='caixadetexto'>
            &nbsp;<img src="../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
        <td>
            <input type='text' name='txt_data_vencimento' value='<?=$data_vencimento;?>' title='Digite a Data de Vencimento' onkeyup="verifica(this, 'data', '', '', event)" maxlength='10' size='20' class='caixadetexto'>
            &nbsp;<img src="../../../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_vencimento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Observação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' title='Digite a Observação' maxlength='255' rows='3' cols='85' class='caixadetexto'><?=$observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar_pedido.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');calcular()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>