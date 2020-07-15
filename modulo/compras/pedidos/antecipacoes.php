<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/compras_new.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../');

$mensagem[1] = "<font class='erro'>ESTE PEDIDO JÁ ESTÁ CONCLUIDO !!!<br>PORTANTO, NÃO É POSSÍVEL INCLUIR OU MANIPULAR ANTECIPAÇÃO(ÕES) NESSE PEDIDO.</font>";
$mensagem[2] = "<font class='atencao'>PEDIDO CONCLUIDO SEM ANTECIPAÇÃO(ÕES).</font>";
$mensagem[3] = "<font class='confirmacao'>ANTECIPAÇÃO INCLUÍDA COM SUCESSO.</font>";
$mensagem[4] = "<font class='erro'>ANTECIPAÇÃO JÁ EXISTENTE.</font>";
$mensagem[5] = "<font class='confirmacao'>ANTECIPAÇÃO(ÕES) EXCLUÍDA(S) COM SUCESSO.</font>";
$mensagem[6] = "<font class='erro'>ANTECIPAÇÃO(ÕES) NÃO PODEM SER EXCLUÍDAS, PORQUE FOI IMPORTADA PELO FINANCEIRO.</font>";

if($passo == 1) {
/***************************************************************************************/
/********************************Exclusão de Antecipação********************************/
/***************************************************************************************/
    $sql = "SELECT `id_conta_apagar` 
            FROM `contas_apagares` 
            WHERE `id_antecipacao` = '$_POST[opt_antecipacao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Significa que essa antecipação Ñ foi importada no contas à Pagar do Financeiro, sendo assim posso excluí-la normalmente ...
        $sql = "DELETE FROM `antecipacoes` WHERE `id_antecipacao` = '$_POST[opt_antecipacao]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 5;
    }else {//Significa que a antecipação já foi Importada e sendo assim não pode ser excluída ...
        $valor = 6;
    }
    if($requisicao == 1) {//Significa que essa tela foi solicitada de requisição
?>
	<Script Language = 'JavaScript'>
            window.location = 'antecipacoes.php?id_pedido=<?=$_POST['id_pedido'];?>&chkt_item_pedido=<?=$chkt_item_pedido;?>&txt_qtde=<?=$_POST['txt_qtde'];?>&requisicao=<?=$requisicao;?>'
//Controle do Frame de Requisição, que está abaixo do Pop-Up de Antecipação e acima do Principal
            window.top.opener.parent.itens.location = 'requisicao_materiais/itens.php?id_pedido=<?=$id_pedido;?>&chkt_item_pedido=<?=$chkt_item_pedido;?>&txt_qtde=<?=$txt_qtde;?>'
//Controle do Frame Principal
            window.top.opener.top.opener.parent.itens.document.form.submit()
	</Script>
<?
//Veio não veio de Requisição, foi soliticitada normalmente pela parte de Antecipações
    }else {
?>
	<Script Language = 'JavaScript'>
            window.location = 'antecipacoes.php?id_pedido=<?=$_POST['id_pedido'];?>&valor=<?=$valor;?>'
            //Controle do Frame Principal
            window.opener.parent.itens.document.form.submit()
	</Script>
<?
    }
}else {
/***************************************************************************************/
/********************************Inclusão de Antecipação********************************/
/***************************************************************************************/
    if(!empty($_POST['txt_valor'])) {
        $data                   = date('Y-m-d H:i:s');
        $data_atual             = date('Y-m-d');
        $txt_data_vencimento    = data::datatodate($txt_data_vencimento, '-');
        
        $sql = "SELECT `id_antecipacao` 
                FROM `antecipacoes` 
                WHERE `id_pedido` = '$id_pedido' 
                AND `id_tipo_pagamento_recebimento` = '$id_tipo_pagamento' 
                AND `valor` = '$_POST[txt_valor]' 
                AND `data` = '$txt_data_vencimento' 
                AND SUBSTRING(`data_sys`, 1, 10) = '$data_atual' 
                AND `observacao` = '$txt_observacao' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//Ainda não existe essa antecipação nesse pedido
            $sql = "INSERT INTO `antecipacoes` (`id_antecipacao`, `id_pedido`, `id_tipo_pagamento_recebimento`, `id_grupo`, `valor`, `data`, `observacao`, `data_sys`) VALUES (NULL, '$id_pedido', '$id_tipo_pagamento', '$_POST[cmb_grupo]', '$_POST[txt_valor]', '$txt_data_vencimento', '$txt_observacao', '$data') ";
            bancos::sql($sql);
            $id_antecipacao = bancos::id_registro();
//Atualiza os dados de Fornecedor na Antecipação ...
            if(!empty($_POST['cmb_conta_corrente'])) {
                $sql = "UPDATE `antecipacoes` SET `id_fornecedor_propriedade` = '$_POST[cmb_conta_corrente]' WHERE `id_antecipacao` = '$id_antecipacao' LIMIT 1 ";
                bancos::sql($sql);
            }
            if($requisicao == 1) {//Significa que essa tela foi solicitada de requisição
?>
                <Script Language = 'JavaScript'>
                    window.location = 'antecipacoes.php?id_pedido=<?=$id_pedido;?>&chkt_item_pedido=<?=$chkt_item_pedido;?>&txt_qtde=<?=$txt_qtde;?>&requisicao=<?=$requisicao;?>'
//Controle do Frame de Requisição, que está abaixo do Pop-Up de Antecipação e acima do Principal
                    window.top.opener.parent.itens.location = 'requisicao_materiais/itens.php?id_pedido=<?=$id_pedido;?>&chkt_item_pedido=<?=$chkt_item_pedido;?>&txt_qtde=<?=$txt_qtde;?>'
//Controle do Frame Principal
                    window.top.opener.top.opener.parent.itens.document.form.submit()
                </Script>
<?
//Veio não veio de Requisição, foi soliticitada normalmente pela parte de Antecipações
            }else {
                    $valor = 3;
?>
                <Script Language = 'JavaScript'>
                    window.location = 'antecipacoes.php?id_pedido=<?=$id_pedido;?>&valor=<?=$valor;?>'
//Controle do Frame Principal
                    window.opener.parent.itens.document.form.submit()
                </Script>
<?
            }
        }else {//Já existe essa antecipação nesse pedido
            $valor = 4;
        }
    }
/***************************************************************************************/
/*********************************Rotina normal da Tela*********************************/
/***************************************************************************************/
//O sistema verifica se existe algum Tipo de Pagamento cadastrado ...
    $sql = "SELECT `id_tipo_pagamento`, `pagamento`, `status_db` 
            FROM `tipos_pagamentos` 
            WHERE `ativo` = '1' ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Se não existir, não posso incluir antecipação
?>
        <Script Language = 'Javascript'>
            window.location = '../../../html/index.php?valor=18'
        </Script>
<?
        exit;
    }
//Busca de alguns dados do Pedido de Compras
    $sql = "SELECT p.`id_fornecedor`, p.`tipo_nota`, p.`status`, CONCAT(tm.`simbolo`, ' ') AS tipo_moeda 
            FROM `pedidos` p  
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = p.`id_tipo_moeda` 
            WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_fornecedor  = $campos[0]['id_fornecedor'];
    $status         = $campos[0]['status'];
    $tipo_nota      = $campos[0]['tipo_nota'];
    $tipo_moeda     = $campos[0]['tipo_moeda'];
//O Pedido não está concluído, portanto ...
//Aqui eu busco o Valor Total do Pedido, independente do Total de Itens
    $valor_total_ped_com_ipi = compras_new::valor_total_ped_com_ipi($id_pedido);

//Busca Todas as Antecipações existentes em Pedido
    $sql = "SELECT a.`id_antecipacao`, a.`valor` AS valor_antecipado, a.`data`, a.`observacao`, g.`nome`, tp.`pagamento` 
            FROM `antecipacoes` a 
            INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = a.`id_tipo_pagamento_recebimento` 
            INNER JOIN `grupos` g ON g.`id_grupo` = a.`id_grupo` 
            WHERE a.`id_pedido` = '$id_pedido' ORDER BY a.`data` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) for($i = 0; $i < $linhas; $i++) $valor_antecipado+= $campos[$i]['valor_antecipado'];
    $valor_restante_pedido = $valor_total_ped_com_ipi - $valor_antecipado;
    if($valor_restante_pedido < 0) $valor_restante_pedido = 0;
/**********************************************************************************/
/*Aqui significa que essa tela foi acessada pela parte de Requisição, e sendo assim
executará esse trecho de código*/
    if($requisicao == 1) $txt_observacao = 'Antecipação - Requisição';
/**********************************************************************************/
    //Se o Pedido já estiver concluído, então eu já não posso mais incluir Antecipação(ões)
    if($status == 2) {
        if($linhas > 0) {//Se existir antecipações ao Pedido, e este já estiver concluído ...
            $valor = 1;
        }else {//Se não existir nenhuma antecipação atrelada ao Pedido, e este já estiver concluído ...
            $valor = 2;
        }
    }
?>
<html>
<title>.:: Antecipação(ões) de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(document.form.passo.value == '') {
//Tipo de Pagamento
        if(!combo('form', 'cmb_tipo_pagamento', '', 'SELECIONE O TIPO DE PAGAMENTO !')) {
            return false
        }
//Grupo
        if(!combo('form', 'cmb_grupo', '', 'SELECIONE O GRUPO !')) {
            return false
        }
//Conta Corrente
        if(document.form.cmb_conta_corrente.disabled == false) {
            if(document.form.cmb_conta_corrente.value == '') {
                alert('SELECIONE A CONTA CORRENTE !')
                document.form.cmb_conta_corrente.focus()
                return false
            }
        }
//Data de Vencimento
        if(!data('form', 'txt_data_vencimento', '4000', 'VENCIMENTO DA ANTECIPAÇÃO ')) {
            return false
        }
//Valor
        if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
            return false
        }
        var moeda = eval(strtofloat(document.form.txt_valor.value))
        var restante = eval('<?=number_format($valor_restante_pedido, 2, ".", "");?>')
        if(moeda > restante) {
            alert('VALOR DE ANTECIPAÇÃO MAIOR DO QUE O VALOR RESTANTE DE PEDIDO !')
            document.form.txt_valor.focus()
            document.form.txt_valor.select()
            return false
        }
        return limpeza_moeda('form', 'txt_valor, ')
    }else {
        return option('form', 'opt_antecipacao', 'SELECIONE UMA OPÇÃO !')
    }
}

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
    if(document.form.status_db.value == 1) {//Habilita a Conta Corrente
        document.form.cmb_conta_corrente.disabled   = false
        document.form.cmb_conta_corrente.className  = 'caixadetexto'
    }else {//Desabilita a Conta Corrente
        document.form.cmb_conta_corrente.disabled   = true
        document.form.cmb_conta_corrente.className  = 'textdisabled'
    }
}

function selecionar(opcao) {
    var valor           = false
    var id_antecipacao  = 0
    var elementos       = document.form.elements
    
    for(var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'radio') {
            if (elementos[i].checked == true) {
                id_antecipacao = elementos[i].value
                valor = true
            }
        }
    }

    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        if(opcao == 1) {//Deseja excluir um Item ...
            document.form.passo.value = 1
        }else {//Deseja imprimir um Item ...
            nova_janela('itens/relatorio_pdf/dados_bancarios.php?id_antecipacao='+id_antecipacao,'RELATORIO','F')
        }
    }
}
</Script>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Controles Normais dessa tela de Antecipação-->
<input type='hidden' name='status_db'>
<input type='hidden' name='id_tipo_pagamento'>
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
<!--**************************************************************************-->
<!--Variáveis que controla se a antecipação não foi requisitada de outro lugar-->
<input type='hidden' name='chkt_item_pedido' value='<?=$chkt_item_pedido;?>'>
<input type='hidden' name='txt_qtde' value='<?=$txt_qtde;?>'>
<input type='hidden' name='requisicao' value='<?=$requisicao;?>'>
<!--**************************************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Registar Nova Antecipação
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Valor Total c/ IPI:</td>
        <td>
            <input type='text' name="txt_valor_total_ped_com_ipi" value="<?=segurancas::number_format($valor_total_ped_com_ipi, 2, '.');?>" size="20" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Restante:</td>
        <td>
            <input type='text' name="txt_valor_restante_pedido" value="<?=number_format($valor_restante_pedido, '2', ',', '.');?>" size="20" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Pagamento:</b>
        </td>
        <td>
            <select name='cmb_tipo_pagamento' title='Selecione o Tipo de Pagamento' onchange='separar()' class='combo'>
            <?
                $sql = "SELECT CONCAT(`id_tipo_pagamento`, '|', `status_db`) AS id_tipo_pagamento, `pagamento` 
                        FROM `tipos_pagamentos` 
                        WHERE `ativo` = '1' ORDER BY `pagamento` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo:</b>
        </td>
        <td>
            <select name='cmb_grupo' title='Selecione o Grupo' class='combo'>
            <?
                $sql = "SELECT `id_grupo`, `nome` 
                        FROM `grupos` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Conta Corrente:</td>
        <td>
            <select name='cmb_conta_corrente' title='Selecione a Conta Corrente' class='textdisabled' disabled>
            <?
                $sql = "SELECT `id_fornecedor_propriedade`, CONCAT(`num_cc`, ' | ', `agencia`, ' | ', `banco`, ' | ', `correntista`) AS conta_corrente 
                        FROM `fornecedores_propriedades` 
                        WHERE `id_fornecedor` = '$id_fornecedor' ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Vencimento:</b>
        </td>
        <td>
            <input type='text' name="txt_data_vencimento" value="<?=date('d/m/Y');?>" title="Digite a Data" size="20" maxlength="10" onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor:</b>
        </td>
        <td>
            <input type='text' name="txt_valor" value="<?=number_format($valor_restante_pedido, '2', ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="20" maxlength="15" title="Digite o Valor" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a Observação' cols='85' rows='3' class='caixadetexto'><?=$txt_observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
<?
/*Se essa tela for um Pop-Up, então não mostra esse botão de voltar <- Acontece isso quando essa é 
soliticada de Requisição de Materiais*/
    if($requisicao != 1) {
?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'itens/outras_opcoes.php?id_pedido=<?=$id_pedido;?>'" class='botao'>
<?
    }
//Enquanto o Pedido não estiver totalmente fechado, então é possível gerar uma Antecipação ...
    if($status < 2) {
?>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_data_vencimento.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_antecipar' value='Antecipar' title='Antecipar' onclick="document.form.passo.value = ''" style='color:green' class='botao'>
<?
    }
?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?
//Aqui eu trago as antecipações atrelada(s) ao Pedido
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='6'>
            <?=$aviso;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Antecipação(ões) de Pedido(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Item(ns)
        </td>
        <td>
            Pagamento
        </td>
        <td>
            Grupo
        </td>
        <td>
            Data Venc
        </td>
        <td>
            Valor
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <input type='radio' name='opt_antecipacao' value="<?=$campos[$i]['id_antecipacao'];?>" id="<?=$i+1;?>">
        </td>
        <td>
            <label for="<?=$i+1;?>">
                <?=$campos[$i]['pagamento'];?>
            </label>
        </td>
        <td>
            <label for="<?=$i+1;?>">
                <?=$campos[$i]['nome'];?>
            </label>
        </td>
        <td>
            <label for="<?=$i+1;?>">
                <?=data::datetodata($campos[$i]['data'], '/');?>
            </label>
        </td>
        <td>
            <label for="<?=$i+1;?>">
                <?=$tipo_moeda.number_format($campos[$i]['valor_antecipado'], 2, ',', '.');?>
            </label>
        </td>
        <td align='left'>
            <label for="<?=$i+1;?>">
                <?=$campos[$i]['observacao'];?>
            </label>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' onclick='return selecionar(1)' style='color:green' class='botao' <?=$disabled;?>>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='return selecionar(2)' style='color:black' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='return fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
<input type="hidden" name="passo">
</form>
</body>
</html>
<?}?>