<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca 'compras_new' ...
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) ANTECIPAÇÃO(ÕES) PARA SER(EM) EXCLUÍDA(S).</font>";
$mensagem[2] = "<font class='confirmacao'>ANTECIPAÇÃO EXCLUÍDA COM SUCESSO.</font>";

if(!empty($_POST['chkt_antecipacao'])) {
    foreach($_POST['chkt_antecipacao'] as $id_antecipacao) {
//Aki desatrelo a antecipação da tabela relacional de Nota Fiscal vs Antecipações
        $sql = "DELETE FROM `nfe_antecipacoes` WHERE `id_antecipacao` = '$id_antecipacao' LIMIT 1 ";
        bancos::sql($sql);
//Atualiza o Status da Antecipação p/ Liberada p/ que está possa ser importada futuramente p/ outra NF de Ent.
        $sql = "UPDATE `antecipacoes` SET `status` = '1' WHERE `id_antecipacao` = '$id_antecipacao' LIMIT 1 ";
        bancos::sql($sql);
/*Volta o Status do Pedido p/ "Em Aberto" p/ que se possa excluir a Antecipação do Pedido caso essa não venha 
ter mais utilidade na negociação de Compra*/
        $sql = "SELECT id_pedido 
                FROM `antecipacoes` 
                WHERE `id_antecipacao` = '$id_antecipacao' LIMIT 1 ";
        $campos_pedido = bancos::sql($sql);
//Abrindo o Pedido ...
        $sql = "UPDATE `pedidos` SET `status` = '1' WHERE `id_pedido` = '".$campos_pedido[0]['id_pedido']."' LIMIT 1 ";
        bancos::sql($sql);
    }
//Aqui eu verifico a NF possui formas de Vencimento ...
/*********************************************/
/*Essa função pega o valor da Nota Fiscal, e desconta desse valor, o valor total das antecipações e 
e divide o valor restante de acordo com a Qtde de Prazos*/
    $sql = "SELECT id_nfe_financiamento 
            FROM `nfe_financiamentos` 
            WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
    $campos_financiamento = bancos::sql($sql);
//Se existir então chama a função, toda vez q excluir 1 item p/ recalcular as parcelas ...
    if(count($campos_financiamento) == 1) {
/*********************************************/
/*Essa função pega o valor da Nota Fiscal, e desconta desse valor, o valor total das antecipações e 
e divide o valor restante de acordo com a Qtde de Prazos*/
        compras_new::calculo_valor_financiamento($_POST['id_nfe']);
/*********************************************/
    }
/*********************************************/
    $valor = 2;
}
$id_nfe = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nfe'] : $_GET['id_nfe'];

//Busca do número da Nota Fiscal através do id_nfe da NF de Entrada corrente
$sql = "SELECT num_nota 
        FROM `nfe` 
        WHERE `id_nfe` = '$id_nfe' ";
$campos     = bancos::sql($sql);
$num_nota   = $campos[0]['num_nota'];
//Busca de Todas as Antecipações que estão atreladas a Nota Fiscal corrente
$sql = "SELECT a.*, p.id_pedido, tp.pagamento 
        FROM `nfe_antecipacoes` nfea 
        INNER JOIN `antecipacoes` a ON a.`id_antecipacao` = nfea.`id_antecipacao` 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = a.`id_tipo_pagamento_recebimento` 
        INNER JOIN `pedidos` p ON p.id_pedido = a.id_pedido 
        WHERE nfea.`id_nfe` = '$id_nfe' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Excluir Antecipação(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if (elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        document.form.nao_atualizar.value = 1
        return true
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        top.opener.parent.itens.document.form.submit()
        top.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?	
    if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <b><?=$mensagem[1];?></b>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_nfe=<?=$id_nfe;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }else {
?>    
    
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Excluir Antecipação(ões) da Nota Fiscal de Entrada N.º 
            <font color='yellow'>
                <?=$num_nota;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td style='cursor:help'>
            <font title='N.º do Pedido / N.º da Antecipação' style='cursor:help'>
                N.º Ped / Ant
            </font>
        </td>
        <td>
            Pagamento
        </td>
        <td style='cursor:help'>
            <font title='Data de Vencimento'>
                Data Venc
            </font>
        </td>
        <td>
            Valor
        </td>
        <td>
            Observação
        </td>
        <td>
            Status
        </td>
    </tr>
<?
//Vetor de Situações da Antecipação ...
        $vetor_status[0] = '<font color="red"><b>À LIBERAR</b></font>';
        $vetor_status[1] = '<font color="darblue"><b>LIBERADA</b></font>';
        $vetor_status[2] = '<font color="darkgreen"><b>CONCLUÍDA</b></font>';

        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_antecipacao[]' value="<?=$campos[$i]['id_antecipacao'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=$campos[$i]['id_pedido'].' / '.$campos[$i]['id_antecipacao']?>
        </td>
        <td>
            <?=$campos[$i]['pagamento'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
            <?=$vetor_status[$campos[$i]['status']];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan = '7'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_nfe=<?=$id_nfe;?>'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_nfe' value='<?=$id_nfe;?>'>
<input type='hidden' name='nao_atualizar'>
</form>
</body>
</html>
<?}?>