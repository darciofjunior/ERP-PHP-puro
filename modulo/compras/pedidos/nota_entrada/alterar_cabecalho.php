<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>NOTA FISCAL ALTERADA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>NÚMERO DE NOTA FISCAL JÁ EXISTENTE.</font>";
$mensagem[3] = "<font class='atencao'>O TIPO DE MOEDA NÃO PODE SER ALTERADO.</font>";
$mensagem[4] = "<font class='confirmacao'>VENCIMENTO INCLUÍDO COM SUCESSO.</font>";
$mensagem[5] = "<font class='confirmacao'>VENCIMENTO EXCLUÍDO COM SUCESSO.</font>";
$mensagem[6] = "<font class='erro'>PREENCHIMENTO INCORRETO P/ OS PRAZOS DO VENCIMENTO.</font>";
$mensagem[7] = "<font class='confirmacao'>VENCIMENTO ALTERADO COM SUCESSO.</font>";

if(!empty($_POST['id_nfe'])) {
    $livre_debito                       = (!empty($_POST['chkt_livre_debito'])) ? 'S' : 'N';
    $pago_pelo_caixa_compras            = (!empty($_POST['chkt_pago_pelo_caixa_compras'])) ? 'S' : 'N';
    $ignorar_impostos_financiamento     = (!empty($_POST['chkt_ignorar_impostos_financiamento'])) ? 'S' : 'N';
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_conta_corrente     = (!empty($_POST[cmb_conta_corrente])) ? "'".$_POST[cmb_conta_corrente]."'" : 'NULL';
    $hdd_importacao         = (!empty($_POST[hdd_importacao])) ? "'".$_POST[hdd_importacao]."'" : 'NULL';
    
    $txt_data_emissao       = data::datatodate($_POST['txt_data_emissao'], '-');
    $txt_data_entrega_atual = data::datatodate($txt_data_entrega_atual, '-');
    $ano                    = substr($_POST['txt_data_emissao'], 0, 4);
//Verifico se já existe alguma outra nota desse Fornecedor com o mesmo número
    $sql = "SELECT `id_nfe`, `data_emissao` 
            FROM `nfe` 
            WHERE `num_nota` = '$_POST[txt_num_nota]' 
            AND `id_fornecedor` = '$_POST[id_fornecedor]' 
            AND `id_nfe` <> '$_POST[id_nfe]' ORDER BY `id_nfe` DESC ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe então ...
        //Aqui eu verifico se a Nota Fiscal, contém 1 item, para poder trocar o Tipo de Moeda
        $sql = "SELECT `id_nfe_historico` 
                FROM `nfe_historicos` 
                WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        $campos = bancos::sql($sql);
//Como a Nota contém 1 item, eu já não posso + trocar o Tipo de Moeda
        if(count($campos) == 1) {
//Aqui compara a moeda original que está na Nota fiscal com a que o usuário selecionou na combo
            if($_POST['cmb_tipo_moeda'] != $id_tipo_moeda) {
                $valor = 3;
            }else {
                $update = 1;
            }
//A Nota Fiscal não contém Itens, então posso trocar o Tipo de Moeda normalmente
        }else {
            $update = 1;
        }
//Atualização dos Dados ...
        if($update == 1) {
/*Significa que existe algum item a Debitar que foi atrelado a essa Nota através de outra Nota, nesse caso 
existem alguns campos que eu não preciso guardar na base de dados ...*/
            if($hdd_nota_debitar == 1) {
                $sql = "UPDATE `nfe` SET `id_empresa` = '$cmb_empresa', `id_tipo_pagamento_recebimento` = '$id_tipo_pagamento', `id_tipo_moeda` = '$cmb_tipo_moeda', `id_fornecedor_propriedade` = $cmb_conta_corrente, `id_importacao` = $hdd_importacao, `num_nota` = '$_POST[txt_num_nota]', `finalidade` = '$_POST[cmb_finalidade]', `tipo` = '$cmb_tipo_nota', `pago_pelo_caixa_compras` = '$pago_pelo_caixa_compras', `ignorar_impostos_financiamento` = '$ignorar_impostos_financiamento', `data_entrega` = '$txt_data_entrega_atual', `livre_debito` = '$livre_debito'  WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
            }else {
                $sql = "UPDATE `nfe` SET `id_empresa` = '$cmb_empresa', `id_tipo_pagamento_recebimento` = '$id_tipo_pagamento', `id_tipo_moeda` = '$cmb_tipo_moeda', `id_fornecedor_propriedade` = $cmb_conta_corrente, `id_importacao` = $hdd_importacao, `num_nota` = '$_POST[txt_num_nota]', `finalidade` = '$_POST[cmb_finalidade]', `tipo` = '$cmb_tipo_nota', `pago_pelo_caixa_compras` = '$pago_pelo_caixa_compras', `ignorar_impostos_financiamento` = '$ignorar_impostos_financiamento', `data_emissao` = '$txt_data_emissao', `data_entrega` = '$txt_data_entrega_atual', `livre_debito` = '$livre_debito' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
            }
            bancos::sql($sql);
            $valor = 1;
        }
    }else {//Já existe uma Nota com essa Numeração, então ...
        $data_emissao   = substr($campos[0]['data_emissao'], 0, 10);
/*Verifico se a Diferença entre a última Data de Emissão e a Data de Emissão atual digitada pelo usuário
é superior a 1 ano*/
        $dias           = data::diferenca_data($data_emissao, $_POST['txt_data_emissao']);
/*Significa que já foi emitido a mais de 1 ano uma NF com esse número, sendo assim posso emitir uma 
outra NF com esse número ...*/
        if($dias[0] > 365) {
//Aqui eu verifico se a Nota Fiscal, contém 1 item, para poder trocar o Tipo de Moeda
            $sql = "SELECT id_nfe_historico 
                    FROM `nfe_historicos` 
                    WHERE id_nfe = '$_POST[id_nfe]' LIMIT 1 ";
            $campos = bancos::sql($sql);
//Como a Nota contém 1 item, eu já não posso + trocar o Tipo de Moeda
            if(count($campos) == 1) {
//Aqui compara a moeda original que está na Nota fiscal com a que o usuário selecionou na combo
                if($_POST['cmb_tipo_moeda'] != $id_tipo_moeda) {
                    $valor  = 3;
                }else {
                    $update = 1;
                }
//A Nota Fiscal não contém Itens, então posso trocar o Tipo de Moeda normalmente
            }else {
                $update = 1;
            }
//Atualização dos Dados ...
            if($update == 1) {
/*Significa que existe algum item a Debitar que foi atrelado a essa Nota através de outra Nota, nesse caso 
existem alguns campos que eu não preciso guardar na base de dados ...*/
                if($hdd_nota_debitar == 1) {
                    $sql = "UPDATE `nfe` SET `id_empresa` = '$cmb_empresa', `id_tipo_pagamento_recebimento` = '$id_tipo_pagamento', `id_tipo_moeda` = '$cmb_tipo_moeda', `id_fornecedor_propriedade` = $cmb_conta_corrente, `id_importacao` = $hdd_importacao, `num_nota` = '$_POST[txt_num_nota]', `finalidade` = '$_POST[cmb_finalidade]', `tipo` = '$cmb_tipo_nota', `pago_pelo_caixa_compras` = '$pago_pelo_caixa_compras', `data_entrega` = '$txt_data_entrega_atual', `livre_debito` = '$livre_debito' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
                }else {
                    $sql = "UPDATE `nfe` SET `id_empresa` = '$cmb_empresa', `id_tipo_pagamento_recebimento` = '$id_tipo_pagamento', `id_tipo_moeda` = '$cmb_tipo_moeda', `id_fornecedor_propriedade` = $cmb_conta_corrente, `id_importacao` = $hdd_importacao, `num_nota` = '$_POST[txt_num_nota]', `finalidade` = '$_POST[cmb_finalidade]', `tipo` = '$cmb_tipo_nota', `pago_pelo_caixa_compras` = '$pago_pelo_caixa_compras', `data_emissao` = '$txt_data_emissao', `data_entrega` = '$txt_data_entrega_atual', `livre_debito` = '$livre_debito' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
                }
                bancos::sql($sql);
                $valor = 1;
            }
        }else {
            $valor = 2;
        }
    }
/*****************************************************Financiamento*****************************************************/
    //Aqui eu atualizo as Datas de Financiamento de cada parcela da NFe ...
    $sql = "SELECT id_nfe_financiamento, dias 
            FROM `nfe_financiamentos` 
            WHERE `id_nfe` = '$_POST[id_nfe]' ORDER BY dias ";
    $campos_financiamento = bancos::sql($sql);
    $linhas_financiamento = count($campos_financiamento);
    if($linhas_financiamento > 0) {//Tem que ter pelo menos 1 parcela gerada ...
        for($i = 0; $i < $linhas_financiamento; $i++) {
//Aqui eu atualizo a Data de Vencimento de cada Parcela somando da Data de Entrada digitada pelo usuário ...
            $data_gravar = data::datatodate(data::adicionar_data_hora($_POST['txt_data_emissao'], $campos_financiamento[$i]['dias']), '-');
//Atualizando o Valor de cada parcela e a Data de Vencimento de cada uma dessas ...
            $sql = "UPDATE `nfe_financiamentos` SET `data` = '$data_gravar' WHERE `id_nfe_financiamento` = ".$campos_financiamento[$i]['id_nfe_financiamento']." LIMIT 1 ";
            bancos::sql($sql);
        }
    }
}

//1) Busca dos Dados de Cabeçalho desta Nota Fiscal p/ saber o Valor Total da NFE ...
$sql = "SELECT DATE_FORMAT(SUBSTRING(data_emissao, 1, 10), '%d/%m/%Y') AS data_emissao, tipo 
        FROM `nfe` 
        WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$data_emissao           = $campos[0]['data_emissao'];

$calculo_total_impostos = calculos::calculo_impostos(0, $id_nfe, 'NFC');
$valor_total_nota       = $calculo_total_impostos['valor_total_nota'];
/************************************************************************************/
/***********************///Incluindo os Vencimentos da NF .../***********************/
/************************************************************************************/
if($gerar_vencimento == 1) {
//Aqui eu verifico se já foi feito algum Financiamento anterior p/ esta NF ...
    $sql = "SELECT * 
            FROM `nfe_financiamentos` 
            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos_financiamento = bancos::sql($sql);
//Significa que ainda não gerado nenhum financiamento, sendo assim eu posso estar gerando ...
    if(count($campos_financiamento) == 0) {
//Significa que estou gerando os vencimentos mediante a seleção de um Pedido ...
        if($opcao == 1) {
//Aqui eu busco dados do Pedido selecionado pelo usuário ...
            $sql = "SELECT * 
                    FROM `pedidos_financiamentos` 
                    WHERE `id_pedido` = '$cmb_pedido_financiamento' ";
            $campos_financiamento   = bancos::sql($sql);
            $linhas_financiamento   = count($campos_financiamento);
//Gero o Valor p/ cada parcela ...
            $valor_parcela_nf       = round(round(((float)($valor_total_nota / $linhas_financiamento)), 3), 2);
            $valor_total_financiamento = 0;
//Aqui eu gero as Parcelas de Financiamento da NF mediante ao Pedido selecionado pelo usuário ...
            for($i = 0; $i < $linhas_financiamento; $i++) {
                $dias = $campos_financiamento[$i]['dias'];
                $data = data::datatodate(data::adicionar_data_hora($data_emissao, $campos_financiamento[$i]['dias']), '-');
/*Quando eu estiver na última parcela então o Sistema verifica se está coerente o somatório das Parcelas 
com o Valor total da NFE, caso isso não aconteça eu jogo nessa parcela a diferença p/ que venha
resultar no valor Total da NFE ...*/
                if(($i + 1) == $linhas_financiamento) $valor_parcela_nf = $valor_total_nota - $valor_total_financiamento;
//Se o Campo Dia estiver preenchido ...
                $insert_extendido.= " ('$id_nfe', '$dias', '$data', '$valor_parcela_nf'), ";
                $valor_total_financiamento+= $valor_parcela_nf;
            }
            $insert_extendido = substr($insert_extendido, 0, strlen($insert_extendido) - 2);
//Gravando os Pedidos de Vencimentos ...
            $sql = "INSERT INTO `nfe_financiamentos` (`id_nfe`, `dias`, `data`, `valor_parcela_nf`) VALUES 
                    $insert_extendido ";
            bancos::sql($sql);
//Chamo a função p/ fazer a divisão das parcelas pelo jeito de Vencimento ...
            compras_new::calculo_valor_financiamento($id_nfe);
//Significa que estou gerando os vencimentos pelo modo antigo sem selecionar algum Pedido de Compras ...
        }else {
            $sql = "SELECT prazo_a, prazo_b, prazo_c, valor_a, valor_b, valor_c, DATE_FORMAT(SUBSTRING(data_emissao, 1, 10), '%d/%m/%Y') AS data_emissao 
                    FROM `nfe` 
                    WHERE id_nfe = '$id_nfe' LIMIT 1 ";
            $campos         = bancos::sql($sql);
            $data_emissao   = $campos[0]['data_emissao'];
            $prazo_a        = $campos[0]['prazo_a'];
            if($prazo_a != 0) {
                $data_a = data::datatodate(data::adicionar_data_hora($data_emissao, $prazo_a), '-');
                $valor_a = $campos[0]['valor_a'];
//Gravando os novos Vencimentos p/ a NFE ...
                $sql = "INSERT INTO `nfe_financiamentos` (`id_nfe`, `dias`, `data`, `valor_parcela_nf`) VALUES ('$id_nfe', '$prazo_a', '$data_a', '$valor_a') ";
                bancos::sql($sql);
            }

            $prazo_b = $campos[0]['prazo_b'];
            if($prazo_b != 0) {
                $data_b = data::datatodate(data::adicionar_data_hora($data_emissao, $prazo_b), '-');
                $valor_b = $campos[0]['valor_b'];
//Gravando os novos Vencimentos p/ a NFE ...
                $sql = "INSERT INTO `nfe_financiamentos` (`id_nfe`, `dias`, `data`, `valor_parcela_nf`) VALUES ('$id_nfe', '$prazo_b', '$data_b', '$valor_b') ";
                bancos::sql($sql);
            }

            $prazo_c = $campos[0]['prazo_c'];
            if($prazo_c != 0) {
                $data_c = data::datatodate(data::adicionar_data_hora($data_emissao, $prazo_c), '-');
                $valor_c = $campos[0]['valor_c'];
//Gravando os novos Vencimentos p/ a NFE ...
                $sql = "INSERT INTO `nfe_financiamentos` (`id_nfe`, `dias`, `data`, `valor_parcela_nf`) VALUES ('$id_nfe', '$prazo_c', '$data_c', '$valor_c') ";
                bancos::sql($sql);
            }
        }
?>
    <Script Language = 'Javascript'>
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    </Script>
<?
        $valor = 4;
    }
}
/************************************************************************************/
/***********************///Excluindo os Vencimentos da NF .../***********************/
/************************************************************************************/
if($_GET['excluir_vencimento'] == 1) {
    //Busca dos Vencimentos ...
    $sql = "SELECT * 
            FROM `nfe_financiamentos` 
            WHERE `id_nfe` = '$_GET[id_nfe]' ";
    $campos_financiamento = bancos::sql($sql);
    $linhas_financiamento = count($campos_financiamento);
    if($linhas_financiamento > 0) {//Se foi encontrado pelo menos 1 Vencimento ...
        for($i = 0; $i < $linhas_financiamento; $i++) {
            //Exclusão dos Vencimentos ...
            $sql = "DELETE FROM `nfe_financiamentos` WHERE `id_nfe_financiamento` = '".$campos_financiamento[$i]['id_nfe_financiamento']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    /*Além de excluir os Vencimentos da Nota Fiscal, também zero o Valor de Financiamento de Taxa 
    e o Valor de Prazo em Dias p/ que possa ser Gerado um futuro Financiamento ou um Vencimento 
    nessa NF, caso assim solicitado pelo Usuário ...*/
    $sql = "UPDATE `nfe` SET `financiamento_taxa` = '0.0', `financiamento_prazo_dias` = '0' WHERE `id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
    bancos::sql($sql);
?>
<Script Language = 'Javascript'>
    window.opener.parent.itens.document.form.submit()
    window.opener.parent.rodape.document.form.submit()
</Script>
<?
    $valor = 5;
}
/******************************************/
//Seleciona a qtde de itens que existe na Nota Fiscal
$sql = "SELECT COUNT(id_nfe_historico) AS qtde_itens 
        FROM `nfe_historicos` 
        WHERE `id_nfe` = '$id_nfe' ";
$campos_itens   = bancos::sql($sql);
$qtde_itens     = $campos_itens[0]['qtde_itens'];
//Busca dos Dados de Cabeçalho desta Nota Fiscal ...
$sql = "SELECT f.razaosocial, f.id_pais, nfe.* 
        FROM `nfe` 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
        WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
$campos                     = bancos::sql($sql);
//Renomeio essa variável p/ esse nome $id_empresa_nf p/ não dar conflito com a variável $id_empresa da Sessão ...
$id_empresa_nf              = $campos[0]['id_empresa'];
$id_importacao              = $campos[0]['id_importacao'];
//Essas Taxas serão utilizadas mais abaixo para segurança dos Botões de Vencimento 
$financiamento_taxa         = $campos[0]['financiamento_taxa'];
$financiamento_prazo_dias   = $campos[0]['financiamento_prazo_dias'];
$id_pais                    = $campos[0]['id_pais'];
//Situação da Nota Fiscal p/ ver se está já foi concluída ...
$situacao                   = $campos[0]['situacao'];
$livre_debito               = $campos[0]['livre_debito'];

//Dados Referentes as Antecipações da Nota Fiscal ...
$retorno_antecipacoes       = compras_new::calculo_valor_antecipacao($id_nfe);
$qtde_antecipacao           = $retorno_antecipacoes['qtde_antecipacao'];
$valor_total_antecipacoes   = $retorno_antecipacoes['valor_total_antecipacoes'];
?>
<html>
<head>
<title>.:: Alterar Cabeçalho de Nota Fiscal de Entrada ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function separar() {
    var vetor = document.form.cmb_tipo_pagamento.value.split('|')
    var id_tipo_pagamento   = vetor[0]
    var status_db           = vetor[1]

    document.form.id_tipo_pagamento.value   = id_tipo_pagamento
    document.form.status_db.value           = status_db

    if(document.form.status_db.value == 1) {//Se a opção de Dados Bancários está marcada então habilita ...
        document.form.cmb_conta_corrente.disabled = false
        //Layout de Habilitado ...
        document.form.cmb_conta_corrente.className = 'caixadetexto'
    }else {
        document.form.cmb_conta_corrente.disabled = true
        //Layout de Desabilitado ...
        document.form.cmb_conta_corrente.className = 'textdisabled'
        document.form.cmb_conta_corrente.value = ''
    }
}

function validar() {
    var id_empresa_nf   = eval('<?=$id_empresa_nf;?>')
    var id_pais         = eval('<?=$id_pais;?>')
//Empresa ...
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE A EMPRESA !')) {
        return false
    }
//Finalidade ...
    if(document.form.cmb_finalidade.value == '') {
        alert('SELECIONE A FINALIDADE !')
        document.form.cmb_finalidade.focus()
        return false
    }
//Importação só irá forcar o preenchimento quando a Nota Fiscal for com NF "Albafer ou Tool Master" ...
    /*if(id_pais != 31 && id_empresa_nf <= 2) {
	if(!combo('form', 'cmb_importacao', '', 'SELECIONE A IMPORTAÇÃO !')) {
            return false
	}
    }*/
//Tipo de Moeda
    if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DE MOEDA !')) {
        return false
    }
//Tipo de Pagamento
    if(!combo('form', 'cmb_tipo_pagamento', '', 'SELECIONE O TIPO DE PAGAMENTO !')) {
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
//Número da Nota
    if(!texto('form', 'txt_num_nota', '1', '0123456789', 'NÚMERO DA NOTA', '2')) {
        return false
    }

    if(id_pais == 31) {//Quando o Fornecedor for do Brasil ...
//Data de Emissão
	if(!data('form', 'txt_data_emissao', '4000', 'EMISSÃO')) {
		return false
	}
    }else {//Quando o Fornecedor for Estrangeiro ...
//Data do B/L
	if(!data('form', 'txt_data_emissao', '4000', 'B/L')) {
		return false
	}
    }
//Data de Entrega
    if(!data('form', 'txt_data_entrega_atual', '4000', 'ENTREGA')) {
        return false
    }

    var data_emissao    = document.form.txt_data_emissao.value
    data_emissao        = data_emissao.substr(6,4) + data_emissao.substr(3,2) + data_emissao.substr(0, 2)
    data_emissao        = eval(data_emissao)

    var data_entrega    = document.form.txt_data_entrega_atual.value
    data_entrega        = data_entrega.substr(6,4) + data_entrega.substr(3,2) + data_entrega.substr(0, 2)
    data_entrega        = eval(data_entrega)
//Comparando as Datas
    if(data_entrega < data_emissao) {
        alert('DATA DE ENTREGA INVÁLIDA !!! \nDATA DE ENTREGA MENOR QUE A DATA DE EMISSÃO !')
        document.form.txt_data_entrega_atual.focus()
        document.form.txt_data_entrega_atual.select()
        return false
    }
/*Tratamento é o tratamento da data atual p/ 15 dias abaixo e acima
da data, para depois poder comparar com a data de emissão*/
//Data Atual - 15 dias
    nova_data('<?=date("d/m/Y")?>', 'document.form.formatar_datas', -15)
    var data_atual_anterior = document.form.formatar_datas.value
    data_atual_anterior = data_atual_anterior.substr(6, 4) + data_atual_anterior.substr(3, 2) + data_atual_anterior.substr(0, 2)
    data_atual_anterior = eval(data_atual_anterior)

//Data Atual + 15 dias
    nova_data('<?=date("d/m/Y")?>', 'document.form.formatar_datas', 15)
    var data_atual_posterior = document.form.formatar_datas.value
    data_atual_posterior = data_atual_posterior.substr(6, 4) + data_atual_posterior.substr(3, 2) + data_atual_posterior.substr(0, 2)
    data_atual_posterior = eval(data_atual_posterior)

//Data de Emissão
    var data_emissao = document.form.txt_data_emissao.value
    data_emissao = data_emissao.substr(6, 4) + data_emissao.substr(3, 2) + data_emissao.substr(0, 2)
//Aqui é a comparação das datas
    if((data_emissao < data_atual_anterior) || (data_emissao > data_atual_posterior)) {
        var resposta = confirm('A DATA DE EMISSÃO ESTÁ CORRETA ? \nCONFIRA NOVAMENTE COM A DATA DA NOTA !')
        if(resposta == false) return false
    }
/***************************************************************************************/
//Desabilito esses campos p/ poder gravar no BD ...
    document.form.nao_atualizar.value                   = 1//Para não atualizar o frames abaixo desse Pop-UP ...
    document.form.cmb_empresa.disabled                  = false
    document.form.cmb_tipo_nota.disabled                = false
    document.form.chkt_pago_pelo_caixa_compras.disabled = false
    document.form.txt_num_nota.disabled                 = false
    document.form.txt_data_emissao.disabled             = false
    atualizar_abaixo()
/***************************************************************************************/
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}

function alterar_tipo_nota_fiscal() {
    if(document.form.cmb_empresa.value == 1 || document.form.cmb_empresa.value == 2) {//Albafer ou Tool
        document.form.cmb_tipo_nota.value = 1//NF
    }else if(document.form.cmb_empresa.value == 4) {//Se for Grupo
        document.form.cmb_tipo_nota.value = 2//SGD
    }else {//Se não tiver nada selecionado, então eu zero o Tipo de Pedido
        document.form.cmb_tipo_nota.value = ''
    }
}

function gerar_vencimento(opcao) {
//Significa que estou gerando os vencimentos mediante a seleção de um Pedido ...
    if(opcao == 1) {
//Pedido de Vencimento ...
        if(!combo('form', 'cmb_pedido_financiamento', '', 'SELECIONE UM PEDIDO DE VENCIMENTO !')) {
            return false
        }
        window.location = 'alterar_cabecalho.php?id_nfe=<?=$id_nfe;?>&gerar_vencimento=1&opcao='+opcao+'&cmb_pedido_financiamento='+document.form.cmb_pedido_financiamento.value
//Significa que estou gerando os vencimentos pelo modo antigo sem selecionar algum Pedido de Compras ...
    }else {
        var mensagem = confirm('DESEJA GERAR VENCIMENTO(S) P/ ESTA NOTA FISCAL ?')
        if(mensagem == false) {
            return false
        }else {
            window.location = 'alterar_cabecalho.php?id_nfe=<?=$id_nfe;?>&gerar_vencimento=1&opcao='+opcao
        }
    }
}

//Excluir Vencimento
function excluir_vencimento(situacao) {
//Significa que essa NF já foi importada, então eu não posso mais manipular os botões de Cabeçalho 
    if(situacao == 2) {
        alert('ESSA NF NÃO PODE SER MODIFICADA !\nESTA JÁ FOI LIBERADA OU IMPORTADA P/ O FINANCEIRO !!!')
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE VENCIMENTO ?')
        if(mensagem == false) {
            return false
        }else {
            window.location = 'alterar_cabecalho.php?id_nfe=<?=$id_nfe;?>&excluir_vencimento=1'
        }
    }
}

function carregar_saida_caixa_compras() {
    if(document.getElementById('chkt_pago_pelo_caixa_compras').checked == true) {
        ajax('../../caixa_compras/saida_caixa_compras.php', 'id_saida_caixa_compras')
    }else {
        document.getElementById('id_saida_caixa_compras').innerHTML = ''
    }
}

function excluir_saida_caixa_compras(id_caixa_compra) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE REGISTRO ?')
    if(resposta == true) ajax('../../caixa_compras/saida_caixa_compras.php?id_caixa_compra='+id_caixa_compra, 'id_saida_caixa_compras')
}

function excluir_importacao() {
    document.form.txt_nome_importacao.value = ''
    document.form.hdd_importacao.value      = ''
    document.form.submit()
}
</Script>
</head>
<body onload='carregar_saida_caixa_compras();alterar_tipo_nota_fiscal();separar();document.form.txt_num_nota.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_nfe' value='<?=$id_nfe;?>'>
<?
//Aqui eu verifico se existe algum item a Debitar que foi atrelado a essa Nota, através de outra Nota ...
	$sql = "SELECT nfe.id_nfe 
                FROM `nfe_historicos` nh 
                INNER JOIN `nfe` ON nfe.id_nfe = nh.id_nfe 
                WHERE nh.`id_nfe_debitar` = '$id_nfe' LIMIT 1 ";
	$campos_deb = bancos::sql($sql);
/*Se existir, então irá travar algumas caixas de texto dessa tela e exibirá o botão de Vencimento da 
Nota Fiscal à Debitar ...*/
	if(count($campos_deb) == 1) $hdd_nota_debitar = 1;
//Guardo essa variável em um hidden p/ ficar + fácil o controle depois que eu submeter ...
?>
<input type='hidden' name='hdd_nota_debitar' value="<?=$hdd_nota_debitar;?>">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cabeçalho de Nota Fiscal de Entrada
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>Fornecedor:
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <?
/*Se existir pelo menos 1 item de Pedido ou a Nota Fiscal tiver com pelo menos 1 antecipação, 
então eu já não posso + alterar a Empresa deste Pedido ...*/
        if($qtde_itens > 0 || $qtde_antecipacao > 0) {
            $disabled   = 'disabled';
            $class      = 'textdisabled';
        }else {
            $disabled   = '';
            $class      = 'caixadetexto';
        }
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange='alterar_tipo_nota_fiscal()' class="<?=$class;?>" <?=$disabled;?>>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, $campos[0]['id_empresa']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Finalidade:</b>
        </td>
        <td>
            <select name='cmb_finalidade' title='Selecione a Finalidade' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos[0]['finalidade'] == 'C') {
                        $selected_consumo           = 'selected';
                    }else if($campos[0]['finalidade'] == 'I') {
                        $selected_industrializacao  = 'selected';
                    }else {
                        $selected_revenda           = 'selected';
                    }
                ?>
                <option value='C' <?=$selected_consumo;?>>CONSUMO</option>
                <option value='I' <?=$selected_industrializacao;?>>INDUSTRIALIZAÇÃO</option>
                <option value='R' <?=$selected_revenda;?>>REVENDA</option>
            </select>
            <?
                //Significa que a Nota Fiscal é Livre de Débito ...
                $checked_livre_debito = ($livre_debito == 'S') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_livre_debito' value='S' id='livre_debito' onclick='controlar_checkbox()' class='checkbox' <?=$checked_livre_debito;?>>
            <label for='livre_debito'>
                <font color='darkblue'>
                    <b>Livre de Débito Propag / Mkt</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tipo da Nota:
        </td>
        <td>
            <select name='cmb_tipo_nota' title='Selecione o Tipo da Nota' class='textdisabled' disabled>
                <option value='' style="color:red">SELECIONE</option>
                <?
                    if($campos[0]['tipo'] == 1) {
                ?>
                <option value='1' selected>NF</option>
                <option value='2'>SGD</option>
                <?
                    }else {
                ?>
                <option value='1'>NF</option>
                <option value='2' selected>SGD</option>
                <?
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Importação:</b>
        </td>
        <td>
        <?
            //Não é permitido mudar a Importação no Cabeçalho de NF, se a mesma já conter Itens ...
            if($qtde_itens > 0) {
                $class_botao    = 'textdisabled';
                $disabled_botao = 'disabled';
            }else {
                $class_botao    = 'botao';
                $disabled_botao = '';
            }
            //Se existir Importação, busco o nome dessa p/ exibir p/ o Usuário ...
            if($id_importacao > 0) {
                $sql = "SELECT nome 
                        FROM `importacoes` 
                        WHERE `id_importacao`  = '$id_importacao' LIMIT 1 ";
                $campos_importacao = bancos::sql($sql);
            }
        ?>
            <input type='text' name='txt_nome_importacao' value='<?=$campos_importacao[0]['nome'];?>' class='textdisabled' disabled>           
        <?
            /*Se existir Importação e não existir nenhum Item na NF, então apresento esse X p/ que o Usuário 
            possa Excluí-la, caso desejar ...*/
            if($id_importacao > 0 && $qtde_itens == 0) {
        ?>
            <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Vencimento' alt='Excluir Vencimento' onclick="excluir_importacao()">
        <?
            }
        ?>
            &nbsp;-&nbsp;
            <input type='button' name='consultar_importacao' value='Consultar Importação' title='Consultar Importação' onclick="html5Lightbox.showLightbox(7, 'consultar_importacao.php?id_nfe=<?=$id_nfe;?>&id_pais=<?=$id_pais;?>')" class='<?=$class_botao;?>' <?=$disabled_botao;?>>
            <input type='hidden' name='hdd_importacao' value='<?=$id_importacao;?>'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <?
                $checked        = ($campos[0]['pago_pelo_caixa_compras'] == 'S') ? 'checked' : '';
                //Esse checkbox "Pago pelo Caixa de Compras" só é habilitado p/ a Gladys 14, Roberto 62, Fábio Petroni 64 e Dárcio 98 porque programa ...
                $disabled_caixa = ($_SESSION['id_funcionario'] == 14 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98) ? '' : 'disabled';
            ?>
            <input type='checkbox' name='chkt_pago_pelo_caixa_compras' id='chkt_pago_pelo_caixa_compras' value='S' onclick='carregar_saida_caixa_compras()' class='checkbox' <?=$checked;?> <?=$disabled_caixa;?>>
            <label for='chkt_pago_pelo_caixa_compras'>
                Pago pelo Caixa de Compras
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <div id='id_saida_caixa_compras'></div>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo Moeda:</b>
        </td>
        <td>
            <select name='cmb_tipo_moeda' title='Selecione o Tipo Moeda' class='combo'>
            <?
                $condicao_moeda = ($id_pais == 31) ? " AND `id_tipo_moeda` = '1' " : " AND `id_tipo_moeda` <> '1' ";

                $sql = "SELECT id_tipo_moeda, CONCAT(simbolo, ' - ', moeda) 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' 
                        $condicao_moeda ";
                echo combos::combo($sql, $campos[0]['id_tipo_moeda']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Pagamento:</b>
        </td>
        <td>
            <select name='cmb_tipo_pagamento' title='Selecione o Tipo de Pagamento' onchange='separar()' class='combo'>
                <option value='' style="color:red">SELECIONE</option>
<?
                    $sql = "SELECT id_tipo_pagamento, pagamento, status_db 
                            FROM `tipos_pagamentos` 
                            WHERE `ativo` = '1' ";
                    $campos_pagamento = bancos::sql($sql);
                    $linhas_pagamento = count($campos_pagamento);
                    for($i = 0; $i < $linhas_pagamento; $i++) {
                        $id_tipo_pagamento  = $campos_pagamento[$i]['id_tipo_pagamento'];
                        $status_db          = $campos_pagamento[$i]['status_db'];
                        if($campos[0]['id_tipo_pagamento_recebimento'] == $id_tipo_pagamento) {
?>
                <option value='<?=$id_tipo_pagamento.'|'.$status_db;?>' selected><?=$campos_pagamento[$i]['pagamento'];?></option>
<?
                        }else {
?>
                <option value='<?=$id_tipo_pagamento.'|'.$status_db;?>'><?=$campos_pagamento[$i]['pagamento'];?></option>
<?
                        }
                    }
?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Conta Corrente:</b>
        </td>
        <td>
            <select name='cmb_conta_corrente' title='Selecione a Conta Corrente' class='combo'>
            <?
//Aqui seleciona a conta corrente da tabela fornecedores_propriedades
                $sql = "SELECT id_fornecedor_propriedade, CONCAT(num_cc, ' | ', agencia, ' | ', banco, ' | ', correntista, ' | ', cnpj_cpf) 
                        FROM `fornecedores_propriedades` 
                        WHERE `id_fornecedor` = '".$campos[0]['id_fornecedor']."' 
                        AND `ativo` = '1' ";
                echo combos::combo($sql, $campos[0]['id_fornecedor_propriedade']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Número da Nota:</b>
        </td>
        <td>
        <?
            //Aqui eu verifico se na NF existe algum item de Pedido que tem algum vínculo com OS ...
            $sql = "SELECT oi.`id_os_item` 
                    FROM `oss_itens` oi 
                    INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = oi.`id_item_pedido` 
                    INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` 
                    WHERE oi.`id_nfe` = '$id_nfe' LIMIT 1 ";
            $campos_itens_os = bancos::sql($sql);
            if(count($campos_itens_os) == 1) {//Se existe 1 item, não posso mudar + o N.º da NF ...
                $class_num_nota     = 'textdisabled';
                $disabled_num_nota  = 'disabled';
            }else {
                $class_num_nota     = 'caixadetexto';
                $disabled_num_nota  = '';
            }
        ?>
            <input type='text' name='txt_num_nota' value="<?=$campos[0]['num_nota'];?>" title='Digite o Número da Nota Fiscal' size='22' maxlength='20' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='<?=$class_num_nota;?>' <?=$disabled_num_nota;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <?
            $data_emissao = (!is_null($campos[0]['data_emissao'])) ? data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/') : '';
        ?>
        <td>
        <?
            if($id_pais == 31) {
                echo '<b>Data Emissão:</b>';
            }else {
                echo '<b>Data do B/L:</b>';
            }
/***********************Controle que irá servir p/ algumas das caixas aqui na Tela***********************/
//Se existir algum item a Debitar que foi atrelado a essa Nota, então eu travo essa caixa ...
            if($hdd_nota_debitar == 1) {//Existe 1 Item, sendo assim travo essa caixa ...
                $class      = 'textdisabled';
                $disabled   = 'disabled';
            }else {//Não existe nenhum item, sendo a caixa está liberada normalmente ...
                $class      = 'caixadetexto';
                $disabled   = '';
            }
/********************************************************************************************************/
        ?>
        <td>
            <input type='text' name="txt_data_emissao" value="<?=$data_emissao;?>" title="Digite a Data de Emissão" maxlength="10" size="12" onkeyup="verifica(this, 'data', '', '', event)" class="<?=$class;?>" <?=$disabled;?>>
            &nbsp;<img src="../../../../imagem/calendario.gif" width="12" height="12" alt="" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="if(document.form.txt_data_emissao.disabled == false) {javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <?
            $data_entrega = (!is_null($campos[0]['data_entrega'])) ? data::datetodata(substr($campos[0]['data_entrega'], 0, 10), '/') : '';
        ?>
        <td>
            <b>Data Entrega:</b>
        </td>
        <td>
            <input type='text' name="txt_data_entrega_atual" value="<?=$data_entrega;?>" maxlength="10" size="12" onkeyup="verifica(this, 'data', '', '', event)" class="caixadetexto">
            &nbsp;<img src="../../../../imagem/calendario.gif" width="12" height="12" alt="" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_entrega_atual&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
<?
/****************************************************************************************************/
/***************************************** Vencimento ***********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Vencimento da NF que foi através do Pedido ...
        $sql = "SELECT nfef.*, tm.`simbolo` 
                FROM `nfe_financiamentos` nfef 
                INNER JOIN `nfe` ON nfe.`id_nfe` = nfef.`id_nfe` 
                INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = nfe.`id_tipo_moeda` 
                WHERE nfef.`id_nfe` = '$id_nfe' ORDER BY nfef.`dias` ";
        $campos_financiamento = bancos::sql($sql);
        $linhas_financiamento = count($campos_financiamento);
        if($linhas_financiamento == 0) {//Não existem Vencimentos ...
//Se esse NF estiver fechada, então eu não posso mais manipular os botões de Cabeçalho dessa NF
            if($situacao == 2) {
                $disabled_financiamento = 'disabled';
                $class_financiamento    = 'textdisabled';
            }else {
                $disabled_financiamento = '';
                $class_financiamento    = 'botao';
            }
            $tab_prazos = 'block';
//Aqui eu busco todos os Pedidos que estão atrelados a NF mas que possuem Vencimento ...
            $sql = "SELECT DISTINCT(nfeh.`id_pedido`), nfeh.`id_pedido` 
                    FROM `nfe_historicos` nfeh 
                    INNER JOIN `pedidos_financiamentos` pf ON pf.`id_pedido` = nfeh.`id_pedido` 
                    WHERE nfeh.`id_nfe` = '$id_nfe' ";
            $campos_importar = bancos::sql($sql);
            if(count($campos_importar) > 0) {
?>
<table width='90%' align='center' cellspacing='0' cellpadding='1' border='0'>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Pedidos com Vencimento: </b>
            </font>
        </td>
        <td>
            <select name="cmb_pedido_financiamento" title="Selecione um Pedido de Financiamento" class='combo'>
                    <?=combos::combo($sql);?>
            </select>
            &nbsp;
            <input type='button' name="cmd_gerar_vencimento" value="Gerar Vencimento" title="Gerar Vencimento" onclick="gerar_vencimento(1)" class="<?=$class_financiamento;?>" <?=$disabled_financiamento;?>>
        </td>
    </tr>
<?
//Não encontrou nenhum Item de Nota Fiscal ainda ...
            }else {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <font color='red'>
                <b>INCLUA PELO MENOS UM ITEM NA NOTA FISCAL EM QUE O PEDIDO TENHA SIDO <font color='darkblue'>FEITO PELO MODO DE FINANCIAMENTO</font> P/ QUE SE POSSA GERAR O(S) VENCIMENTO(S).</b>
            </font>
        </td>
    </tr>
<?
            }
?>
</table>
<?
        }else {//Se foi encontrado pelo menos 1 Vencimento ...
            $tab_prazos = 'none';
?>
<table width='90%' align='center' cellspacing='0' cellpadding='1' border='1'>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            <font color='#FFFDCA'>
                <b>VENCIMENTO E PRAZO(S)</b>
                &nbsp; <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Vencimento' alt='Excluir Vencimento' onclick="excluir_vencimento('<?=$situacao;?>')">
            </font>
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas_financiamento; $i++) {
?>
    <tr class='linhanormal'>
        <td width='15%'>
            <font color='darkblue'>
                <b>Parcela N.º <?=$i + 1;?>:</b>
            </font>
        </td>
        <td width='25%'>
            <font color='darkblue'>Dias: </font><?=$campos_financiamento[$i]['dias'];?>
        </td>
        <td width='25%'>
            <font color='darkblue'>Data: </font><?=data::datetodata($campos_financiamento[$i]['data'], '/');?>
        </td>
        <td width='25%'>
            <font color='darkblue'>Valor <?=$campos_financiamento[$i]['simbolo'];?>: </font><?=number_format($campos_financiamento[$i]['valor_parcela_nf'], 2, ',', '.');?>
        </td>
    </tr>
    <?
            }
    ?>
</table>
<?
        }
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
//Se existe pelo menos uma antecipação exibe essa linha a +
	if($qtde_antecipacao > 0) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='red' size='2'>
                <b>NOTA FISCAL COM <?=$qtde_antecipacao;?> ANTECIPAÇÃO(ÕES) - VALOR TOTAL: <?='R$ '.number_format($valor_total_antecipacoes, 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
//Se esse NF estiver fechada, então eu não posso mais manipular os botões de Cabeçalho dessa NF
            if($situacao == 2) {
                $disabled_botao = 'disabled';
                $class          = 'textdisabled';
                /*O Roberto é o único que pode Salvar dados de Cabeçalho da NF mesmo que essa já 
                esteja liberada ...*/
                $disabled_salvar = ($_SESSION['id_funcionario'] == 62) ? '' : 'disabled';
                $class_salvar 	 = ($_SESSION['id_funcionario'] == 62) ? 'botao' : 'textdisabled';
            }else {
/*Aqui eu verifico se essa Nota Fiscal corrente está com Itens à serem debitados 
em outra NF ...*/
                $sql = "SELECT id_nfe_debitar 
                        FROM `nfe_historicos` 
                        WHERE `id_nfe` = '$id_nfe' 
                        AND `id_nfe_debitar` > '0' LIMIT 1 ";
                $campos_debitar = bancos::sql($sql);
                if(count($campos_debitar) == 1) {//Significa que está NF, está em outra NF ...
//Verifico se a NF encontrada que contém essa NF corrente, possuis Itens ...
                    $sql = "SELECT COUNT(id_nfe_historico) as total_itens 
                            FROM `nfe_historicos` 
                            WHERE `id_nfe` = ".$campos_debitar[0]['id_nfe_debitar'];
                    $campos_itens 	= bancos::sql($sql);
                    $total_itens 	= $campos_itens[0]['total_itens'];
//Se existir pelo menos 1 item, não pode mexer no Cabeçalho ...
                    if($total_itens > 0) {
                        $disabled_botao     = 'disabled';
                        $class              = 'textdisabled';
                        $disabled_salvar    = 'disabled';
                        $class_salvar       = 'textdisabled';
                    }else {//Não existem Itens, então posso mexer normalmente ...
                        $disabled_botao     = '';
                        $class              = 'botao';
                        $disabled_salvar    = '';
                        $class_salvar       = 'botao';
                    }
                }else {//Não está em Outra NF ...
                    $disabled_botao     = '';
                    $class              = 'botao';
                    $disabled_salvar    = '';
                    $class_salvar       = 'botao';
                }
            }
        ?>
            <input type='reset' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');separar()" style="color:#ff9900;" class="<?=$class;?>" <?=$disabled_botao;?>>
            <input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="<?=$class_salvar;?>" <?=$disabled_salvar;?>>
        <?
            if($linhas_financiamento > 0) {
                /*Se a NF estiver Liberada, não posso alterar os Vencimentos de modo nenhum ...

                Se existe 1 Vencimento e algum Valor de Financiamento de Taxa ou algum Valor de Prazo 
                em Dias então posso Gerar ou Alterar Financiamento ...*/
                if($situacao == 2 || $linhas_financiamento > 0 && ($financiamento_taxa > 0 || $financiamento_prazo_dias > 0)) {
                    //Não posso mexer no Vencimento ...
                    $class_vencimento       = 'textdisabled';
                    $disabled_vencimento    = 'disabled';
                    //Mas podia mexer no Financiamento até dia 03/06/2014 ...
                    //$class_financiamento    = 'botao';
                    //$disabled_financiamento = '';
                }else {
                    //Posso mexer no Vencimento ...
                    $class_vencimento       = 'botao';
                    $disabled_vencimento    = '';
                    /*Nesse caso eu não posso Gerar ou Alterar nenhum Financiamento 
                    porque o cálculo de hum é incompatível com o cálculo de outro ...*/
                    //$class_financiamento    = 'textdisabled';
                    //$disabled_financiamento = 'disabled';
                }
        ?>
            <input type='button' name='cmd_alterar_vencimento' value='Alterar Vencimento' title='Alterar Vencimento' onclick="window.location = 'alterar_finame.php?id_nfe=<?=$id_nfe;?>&txt_data_emissao='+document.form.txt_data_emissao.value" style='color:darkblue' class="<?=$class_vencimento;?>" <?=$disabled_vencimento;?>>
        <?
            }else {//Se existe financiamento, sempre deixo esse CSS abaixo preparado p/ evitar Bugs de Sistema ...
                $class_vencimento       = 'botao';
                $disabled_vencimento    = '';
            }
        ?>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick='fechar(window)' class="botao">
        <?
/*Se existe algum item a Debitar que foi atrelado a essa Nota, através de outra Nota, então eu exibo 
o botão de Vencimento da Nota Fiscal à Debitar ...*/
            if($hdd_nota_debitar == 1) {
        ?>
            &nbsp;|&nbsp;<input type='button' name='cmd_financiamento' value='&gt;&gt; Financiamento &gt;&gt;' title='Financiamento' style='color:black' onclick="document.form.nao_atualizar.value = 1;window.location = 'financiamento.php?id_nfe=<?=$id_nfe;?>'" class="<?=$class_vencimento;?>" <?=$disabled_vencimento;?>>
            <?
                $checked = ($campos[0]['ignorar_impostos_financiamento'] == 'S') ? 'checked' : '';
            ?>  
            <input type='checkbox' name='chkt_ignorar_impostos_financiamento' id='chkt_ignorar_impostos_financiamento' value='S' class='checkbox' <?=$checked;?>>
            <label for='chkt_ignorar_impostos_financiamento'>
                Ignorar Impostos no Financiamento
            </label>
        <?
            }
        ?>
        </td>
    </tr>
</table>
<input type='hidden' name='status_db'>
<input type='hidden' name='id_tipo_pagamento'>
<!--Aqui eu guardo esses valores só para facilitar umas lá comparações de dentro do PHP-->
<input type='hidden' name='id_fornecedor' value='<?=$campos[0]['id_fornecedor'];?>'>
<input type='hidden' name='id_tipo_moeda' value='<?=$campos[0]['id_tipo_moeda'];?>'>
<!--Aqui eu utilizo para tratar a Datas-->
<input type='hidden' name='formatar_datas'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
</form>
</body>
</html>