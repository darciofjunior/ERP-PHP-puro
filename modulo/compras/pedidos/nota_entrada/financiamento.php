<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>FINANCIAMENTO ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>PRAZO(S) ATUALIZADO(S) COM SUCESSO.</font>";

if($passo == 1) {
    if($_POST['atualizar_prazos'] == 'S') {
        //Aqui eu atualizo os dados de Nota Fiscal com os dados de cadastro do Fornecedor ...
        $sql = "UPDATE `nfe` SET `financiamento_taxa` = '$_POST[hdd_taxa]', `financiamento_prazo_dias` = '$_POST[hdd_prazo]' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        window.location = 'financiamento.php?id_nfe=<?=$_POST['id_nfe'];?>&valor=2'
    </Script>
<?
    }else {
/**************************Primeiro passo antes de fazer qualquer atualização*************************/
//Busca do id_fornecedor da NF ...
        $sql = "SELECT id_fornecedor 
                FROM `nfe` 
                WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $id_fornecedor  = $campos[0]['id_fornecedor'];
/*Verifica se o Fornecedor possui algum pedido em aberto, sendo assim ele busca o último pedido deste
que ainda não foi importado para NF*/
        $sql = "SELECT id_pedido 
                FROM `pedidos` 
                WHERE `id_fornecedor`  = '$id_fornecedor' 
                AND `ativo` < '2' ORDER BY id_pedido DESC LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Encontrou
            $id_pedido = $campos[0]['id_pedido'];
        }else {//Não encontrou
/*O Fornecedor não possui nenhum pedido em aberto, sendo assim ele busca o último pedido deste
que já foi importado para NF*/
            $sql = "SELECT id_pedido 
                    FROM `pedidos` 
                    WHERE `id_fornecedor` = '$id_fornecedor' 
                    AND `ativo` = '2' ORDER BY id_pedido DESC LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Se não encontrou nada, então o sistema retorna essa mensagem e sai fora da tela ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTE FORNECEDOR AINDA NÃO TEM PEDIDO !!!\n\nINCLUA UM PEDIDO !')
        window.close()
    </Script>
<?
                exit;
            }else {//Se o sistema encontrou um pedido nessa situação: beleza, continou com a atualização do Financiamento ...
                $id_pedido  = $campos[0]['id_pedido'];
            }
        }
/*********************************Atualizando a Nota Fiscal Principal*********************************/
        $data_sys = date('Y-m-d H:i:s');
/*Aqui eu atualizo os Prazos de A a N ... pelo modo Novo de Financiamento 
"Nota Fiscal Principal"*/
//Antes, eu verifico se já foi feito algum Financiamento anterior p/ esta NF ...
        $sql = "SELECT * 
                FROM `nfe_financiamentos` 
                WHERE `id_nfe` = '$_POST[id_nfe]' ORDER BY id_nfe_financiamento ";
        $campos_financiamento = bancos::sql($sql);
//Significa que ainda não tinha gerado nenhum financiamento ...
        if(count($campos_financiamento) == 0) {
//Aqui eu gero as Parcelas de Financiamento da NF mediante ao Pedido selecionado pelo usuário ...
            for($i = 0; $i < count($txt_dias_financ); $i++) {
                $txt_nova_data_financ[$i] = data::datatodate($txt_nova_data_financ[$i], '-');
                $insert_extendido.= " ('$_POST[id_nfe]', '$txt_dias_financ[$i]', '$txt_nova_data_financ[$i]', '$txt_valor_parcela_financ[$i]'), ";
            }
            $insert_extendido = substr($insert_extendido, 0, strlen($insert_extendido) - 2);
//Gravando os Vencimentos de Nota Fiscal ...
            $sql = "INSERT INTO `nfe_financiamentos` (`id_nfe`, `dias`, `data`, `valor_parcela_nf`) VALUES 
                    $insert_extendido ";
            bancos::sql($sql);
//Significa que já tinha sido gerado um Financiamento anteriormente ...
        }else {
            for($i = 0; $i < count($campos_financiamento); $i++) {
                $txt_nova_data_financ[$i] = data::datatodate($txt_nova_data_financ[$i], '-');
//Atualizando os Vencimentos de Nota Fiscal ...
                $sql = "UPDATE `nfe_financiamentos` SET `dias` = '$txt_dias_financ[$i]', `data` = '$txt_nova_data_financ[$i]', `valor_parcela_nf` = '$txt_valor_parcela_financ[$i]' WHERE `id_nfe_financiamento` = '".$campos_financiamento[$i]['id_nfe_financiamento']."' LIMIT 1 ";
                bancos::sql($sql);	
            }
        }
/*Aqui eu atualizo os dados de Taxa, Prazo de Dias e Data de Emissão da 
"Nota Fiscal Principal"*/
        $sql = "UPDATE `nfe` SET `financiamento_taxa` = '$_POST[txt_taxa]', `financiamento_prazo_dias` = '$_POST[txt_prazo]', `data_emissao` = '$_POST[txt_data_emissao_nf_deb]' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        bancos::sql($sql);
/**********************Atualizando os Itens da Nota Fiscal Principal**********************/
//Controle com os Itens da Nota Fiscal Principal ...
//1) Controle com o Compra de Insumo p/ Terceiros ...
        $_POST['txt_compra_insumo']*= -1;//Esse valor sempre tem q ser guardado de forma positiva no BD ...
        if(!empty($_POST['hdd_item_compra_insumo'])) {//Já existia esse Item na Nota Fiscal ...
//Aqui é p/ atualizar o Item de Pedido ...
            $sql = "SELECT id_item_pedido 
                    FROM `nfe_historicos` 
                    WHERE `id_nfe_historico` = '$_POST[hdd_item_compra_insumo]' LIMIT 1 ";
            $campos_item_pedido = bancos::sql($sql);
            $id_item_pedido     = $campos_item_pedido[0]['id_item_pedido'];
//Atualizando a Tabela de Pedidos ...
            $sql = "UPDATE `itens_pedidos` SET `preco_unitario` = '$_POST[txt_compra_insumo]' WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
            bancos::sql($sql);
//Atualizando a Tabela de Nota Fiscal ...
            $sql = "UPDATE `nfe_historicos` SET `valor_entregue` = '$_POST[txt_compra_insumo]', `data_sys` = '$data_sys' WHERE `id_nfe_historico` = '$hdd_item_compra_insumo' LIMIT 1 ";
            bancos::sql($sql);
        }else {//Não existia esse Item ...
//Gravando na Tabela de Pedidos ...
            $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `preco_unitario`, `ipi`, `qtde`, `marca`, `status`) VALUES (NULL, '$id_pedido', '1340', '$_POST[txt_compra_insumo]', '', '-1', '', '2') ";
            bancos::sql($sql);
            $id_item_pedido = bancos::id_registro();
//Gravando na Tabela de Nota Fiscal ...
            $sql = "INSERT INTO `nfe_historicos` (`id_nfe_historico`, `id_item_pedido`, `id_produto_insumo`, `id_nfe`, `id_pedido`, `cod_tipo_ajuste`, `tipo`, `qtde_entregue`, `valor_entregue`, `data_sys`) VALUES (NULL, '$id_item_pedido', '1340', '$_POST[id_nfe]', '$id_pedido', '1', 'E', '-1', '$_POST[txt_compra_insumo]', '$data_sys') ";
            bancos::sql($sql);
        }

//2)Controle com o IPI ...
        if(!empty($_POST['hdd_item_ajuste_ipi'])) {//Já existia esse Item na Nota Fiscal ...
//Aqui é p/ atualizar o Item de Pedido ...
            $sql = "SELECT id_item_pedido 
                    FROM `nfe_historicos` 
                    WHERE `id_nfe_historico` = '$_POST[hdd_item_ajuste_ipi]' LIMIT 1 ";
            $campos_item_pedido = bancos::sql($sql);
            $id_item_pedido     = $campos_item_pedido[0]['id_item_pedido'];
//Atualizando a Tabela de Pedidos ...
            $sql = "UPDATE `itens_pedidos` SET `preco_unitario` = '$_POST[txt_ajuste_ipi]' WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
            bancos::sql($sql);

//Atualizando a Tabela de Nota Fiscal ...
            $sql = "UPDATE `nfe_historicos` SET `valor_entregue` = '$_POST[txt_ajuste_ipi]', `data_sys` = '$data_sys' WHERE `id_nfe_historico` = '$_POST[hdd_item_ajuste_ipi]' LIMIT 1 ";
            bancos::sql($sql);
        }else {
//Gravando na Tabela de Pedidos ...
            $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `preco_unitario`, `ipi`, `qtde`, `marca`, `status`) VALUES (NULL, '$id_pedido', '1340', '$_POST[txt_ajuste_ipi]', '', '1', '', '2') ";
            bancos::sql($sql);
            $id_item_pedido = bancos::id_registro();
//Gravando na Tabela de Nota Fiscal ...
            $sql = "INSERT INTO `nfe_historicos` (`id_nfe_historico`, `id_item_pedido`, `id_produto_insumo`, `id_nfe`, `id_pedido`, `cod_tipo_ajuste`, `tipo`, `qtde_entregue`, `valor_entregue`, `data_sys`) VALUES (NULL, '$id_item_pedido', '1340', '$_POST[id_nfe]', '$id_pedido', '2', 'E', '1', '$_POST[txt_ajuste_ipi]', '$data_sys') ";
            bancos::sql($sql);
        }

//3) Controle com o ICMS ...
        if(!empty($_POST['hdd_item_ajuste_icms'])) {//Já existia esse Item na Nota Fiscal ...
//Aqui é p/ atualizar o Item de Pedido ...
            $sql = "SELECT id_item_pedido 
                    FROM `nfe_historicos` 
                    WHERE `id_nfe_historico` = '$_POST[hdd_item_ajuste_icms]' LIMIT 1 ";
            $campos_item_pedido = bancos::sql($sql);
            $id_item_pedido     = $campos_item_pedido[0]['id_item_pedido'];
//Atualizando a Tabela de Pedidos ...
            $sql = "UPDATE `itens_pedidos` SET `preco_unitario` = '$_POST[txt_ajuste_icms]' WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
            bancos::sql($sql);

//Atualizando a Tabela de Nota Fiscal ...
            $sql = "UPDATE `nfe_historicos` SET `valor_entregue` = '$_POST[txt_ajuste_icms]', `data_sys` = '$data_sys' WHERE `id_nfe_historico` = '$_POST[hdd_item_ajuste_icms]' LIMIT 1 ";
            bancos::sql($sql);
        }else {//Não existia esse Item ...
//Gravando na Tabela de Pedidos ...
            $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `preco_unitario`, `ipi`, `qtde`, `marca`, `status`) VALUES (NULL, '$id_pedido', '1340', '$_POST[txt_ajuste_icms]', '', '1', '', '2') ";
            bancos::sql($sql);
            $id_item_pedido = bancos::id_registro();
//Gravando na Tabela de Nota Fiscal ...
            $sql = "INSERT INTO `nfe_historicos` (`id_nfe_historico`, `id_item_pedido`, `id_produto_insumo`, `id_nfe`, `id_pedido`, `cod_tipo_ajuste`, `tipo`, `qtde_entregue`, `valor_entregue`, `data_sys`) VALUES (NULL, '$id_item_pedido', '1340', '$_POST[id_nfe]', '$id_pedido', '3', 'E', '1', '$_POST[txt_ajuste_icms]', '$data_sys') ";
            bancos::sql($sql);
        }

//4) Controle com o Ajuste de Financiamento ...
        $ajuste_financiamento = $_POST[txt_valor_com_taxa] - $_POST['txt_valor_ser_financiado'];//Esse valor sempre tem q ser guardado de forma positiva no BD
        $ajuste_financiamento=-$ajuste_financiamento;//Esse valor sempre tem q ser guardado de forma positiva no BD
//Só irá gerar essa diferença quando o Valor for diferente de Zero ...
        if($ajuste_financiamento != 0) {
            if(!empty($_POST['hdd_item_ajuste_financiamento'])) {//Já existia esse Item na Nota Fiscal ...
//Aqui é p/ atualizar o Item de Pedido ...
                $sql = "SELECT id_item_pedido 
                        FROM `nfe_historicos` 
                        WHERE `id_nfe_historico` = '$_POST[hdd_item_ajuste_financiamento]' LIMIT 1 ";
                $campos_item_pedido = bancos::sql($sql);
                $id_item_pedido     = $campos_item_pedido[0]['id_item_pedido'];
//Atualizando a Tabela de Pedidos ...
                $sql = "UPDATE `itens_pedidos` SET `preco_unitario` = '$ajuste_financiamento' WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
                bancos::sql($sql);
//Atualizando a Tabela de Nota Fiscal ...
                $sql = "UPDATE `nfe_historicos` SET `valor_entregue` = '$ajuste_financiamento', `data_sys` = '$data_sys' where `id_nfe_historico` = '$_POST[hdd_item_ajuste_financiamento]' LIMIT 1 ";
                bancos::sql($sql);
            }else {//Não existia esse Item ...
//Gravando na Tabela de Pedidos ...
                $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `preco_unitario`, `ipi`, `qtde`, `marca`, `status`) VALUES (NULL, '$id_pedido', '1340', '$ajuste_financiamento', '', '-1', '', '2') ";
                bancos::sql($sql);
                $id_item_pedido = bancos::id_registro();
//Gravando na Tabela de Nota Fiscal ...
                $sql = "INSERT INTO `nfe_historicos` (`id_nfe_historico`, `id_item_pedido`, `id_produto_insumo`, `id_nfe`, `id_pedido`, `cod_tipo_ajuste`, `tipo`, `qtde_entregue`, `valor_entregue`, `data_sys`) VALUES (NULL, '$id_item_pedido', '1340', '$_POST[id_nfe]', '$id_pedido', '6', 'E', '-1', '$ajuste_financiamento', '$data_sys') ";
                bancos::sql($sql);
            }
        }
?>
    <Script Language = 'Javascript'>
        window.location = 'financiamento.php?id_nfe=<?=$_POST['id_nfe'];?>&valor=1'
    </Script>
<?
    }
}else {
/***************************************************************************************************/
//Busca dos Dados de Cabeçalho da Nota Fiscal Principal ...
    $sql = "SELECT f.razaosocial, nfe.id_fornecedor, nfe.num_nota, nfe.financiamento_prazo_dias, 
            nfe.pago_pelo_caixa_compras, nfe.ignorar_impostos_financiamento 
            FROM `nfe` 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
            WHERE nfe.`id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
    $campos                             = bancos::sql($sql);
    $id_fornecedor                      = $campos[0]['id_fornecedor'];
    $fornecedor_principal               = $campos[0]['razaosocial'];
    $num_nota_principal                 = $campos[0]['num_nota'];
    $ignorar_impostos_financiamento     = $campos[0]['ignorar_impostos_financiamento'];
    $mensagem_impostos_financiamento    = ($ignorar_impostos_financiamento == 'S') ? '&nbsp;<font color="darkred"><b>(IGNORADO)</b></font>' : '';
    
//Por enquanto os valores a serem utilizados são os que estão gravados na Base de Dados de NF ...
    $financiamento_taxa                 = number_format($campos[0]['financiamento_taxa'], 1, ',', '.');
    $financiamento_prazo_dias           = $campos[0]['financiamento_prazo_dias'];
//Verifico se a Nota Fiscal Principal possui Itens ...
    $sql = "SELECT id_nfe_historico 
            FROM `nfe_historicos` 
            WHERE `id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
    $campos = bancos::sql($sql);
//Aqui eu busco dados do Cadastro do Fornecedor da Nota Fiscal Principal ...
    $sql = "SELECT financiamento_taxa, financiamento_prazo_dias 
            FROM `fornecedores` 
            WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
    $campos_fornecedor                      = bancos::sql($sql);
    $financiamento_taxa_fornecedor          = number_format($campos_fornecedor[0]['financiamento_taxa'], 1, ',', '.');
    $financiamento_prazo_dias_fornecedor    = $campos_fornecedor[0]['financiamento_prazo_dias'];
/***************************************************************************************************/
/*Aqui eu busco o id_nfe que foi atrelado a essa Nota, através de outra Nota como valores que 
tem que ser debitados do fornecedor - "Nota Fiscal Debitar"*/
    $sql = "SELECT nfe.id_nfe 
            FROM `nfe_historicos` nh 
            INNER JOIN `nfe` ON nfe.id_nfe = nh.id_nfe 
            WHERE nh.`id_nfe_debitar` = '$_GET[id_nfe]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_nfe_outro_fornec = $campos[0]['id_nfe'];

//Busca dos Dados de Cabeçalho da Nota Fiscal Debitar ...
    $sql = "SELECT f.razaosocial, nfe.* 
            FROM `nfe` 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
            WHERE `id_nfe` = '$id_nfe_outro_fornec' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $fornecedor_debitar     = $campos[0]['razaosocial'];
    $num_nota_debitar       = $campos[0]['num_nota'];
    
    $calculo_total_impostos = calculos::calculo_impostos(0, $id_nfe_outro_fornec, 'NFC', $_GET['id_nfe']);
    $valor_icms             = $calculo_total_impostos['valor_icms'];
    $valor_ipi              = $calculo_total_impostos['valor_ipi'];
    $valor_total_nota       = $calculo_total_impostos['valor_total_nota'];

    $data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');
        
//Aqui eu busco todos os Vencimentos da Nota Fiscal Debitar ...
    $sql = "SELECT nf.*, tm.simbolo 
            FROM `nfe_financiamentos` nf 
            INNER JOIN `nfe` ON nfe.id_nfe = nf.id_nfe 
            INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = nfe.id_tipo_moeda 
            WHERE nf.`id_nfe` = '$id_nfe_outro_fornec' ORDER BY nf.dias ";
    $campos_financiamento = bancos::sql($sql);
    $linhas_financiamento = count($campos_financiamento);
?>
<html>
<head>
<title>.:: Alterar Financiamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	var elementos = document.form.elements
//Taxa
	if(!texto('form', 'txt_taxa', '1', '0123456789,.', 'TAXA', '1')) {
		return false
	}
//Prazo
	if(!texto('form', 'txt_prazo', '1', '0123456789', 'PRAZO', '2')) {
		return false
	}
//Desabilito p/ poder gravar no BD ...
	document.form.txt_taxa.disabled                 = false
	document.form.txt_prazo.disabled                = false
	document.form.txt_compra_insumo.disabled        = false
	document.form.txt_ajuste_ipi.disabled           = false
	document.form.txt_ajuste_icms.disabled          = false
	document.form.txt_valor_ser_financiado.disabled = false
	document.form.txt_valor_com_taxa.disabled       = false
//Aqui eu verifico a qtde de Prazos da Nota principal ...
	if(typeof(elementos['txt_dias[]'][0]) == 'undefined') {
		total_prazos = 1//Existe apenas 1 único elemento ...
	}else {
		total_prazos = (elementos['txt_dias[]'].length)
	}
	
	for(i = 1; i <= total_prazos; i++) {
//Deixa no formato Moeda p/ poder gravar no Banco de Dados ...
		document.getElementById('txt_valor_parcela_financ'+i).value = strtofloat(document.getElementById('txt_valor_parcela_financ'+i).value)
//Desabilita as caixinhas p/ poder gravar no BD ...		
		document.getElementById('txt_dias_financ'+i).disabled = false
		document.getElementById('txt_nova_data_financ'+i).disabled = false
		document.getElementById('txt_valor_parcela_financ'+i).disabled = false
	}
	return limpeza_moeda('form', 'txt_taxa, txt_compra_insumo, txt_ajuste_ipi, txt_ajuste_icms, txt_valor_ser_financiado, txt_valor_com_taxa, ')
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
	if(document.form.nao_atualizar.value == 0) {
		window.opener.parent.itens.document.form.submit()
		window.opener.parent.rodape.document.form.submit()
	}
}

function calcular_geral() {
	calcular_taxa_final()
	calcular_valor_com_taxa()
	calcular_parcelas()
}

function calcular_taxa_final() {
//Só irá realizar o cálculo da Taxa Final, quando os 2 campos taxa e prazo estiverem preenchidos
	if(document.form.txt_taxa.value != '' && document.form.txt_prazo.value != '') {
		var taxa = eval(strtofloat(document.form.txt_taxa.value))
		var prazo = document.form.txt_prazo.value
		document.form.txt_taxa_final.value = (prazo / 30) * taxa
		document.form.txt_taxa_final.value = arred(document.form.txt_taxa_final.value, 2, 1)
	}else {//Do contrário o campo ficará limpo ...
		document.form.txt_taxa_final.value = ''
	}
}

function calcular_valor_com_taxa() {
	var ajuste_compra_insumo = eval(strtofloat(document.form.txt_compra_insumo.value))
//Controle com o IPI ...
	if(document.form.txt_ajuste_ipi.value != '') {
		ajuste_ipi = eval(strtofloat(document.form.txt_ajuste_ipi.value))
	}else {
		ajuste_ipi = 0
	}
//Controle com o ICMS ...
	if(document.form.txt_ajuste_icms.value != '') {
		ajuste_icms = eval(strtofloat(document.form.txt_ajuste_icms.value))
	}else {
		ajuste_icms = 0
	}
//Cálculo do Valor à ser Financiado
	document.form.txt_valor_ser_financiado.value = ajuste_compra_insumo + ajuste_ipi + ajuste_icms
	document.form.txt_valor_ser_financiado.value = arred(document.form.txt_valor_ser_financiado.value, 2, 1)
/*Só irá realizar o cálculo do Valor Com Taxa, quando os 2 campos Taxa Final e Valor à ser Financiado 
estiverem preenchidos*/
	if(document.form.txt_taxa_final.value != '' && document.form.txt_valor_ser_financiado.value != '') {
		var valor_ser_financiado = eval(strtofloat(document.form.txt_valor_ser_financiado.value))
		var taxa_final = eval(strtofloat(document.form.txt_taxa_final.value))
		document.form.txt_valor_com_taxa.value = valor_ser_financiado * (taxa_final / 100 + 1)
		document.form.txt_valor_com_taxa.value = arred(document.form.txt_valor_com_taxa.value, 2, 1)
	}else {//Do contrário o campo ficará limpo ...
		document.form.txt_valor_com_taxa.value = ''
	}
}

function calcular_parcelas() {
    var elementos = document.form.elements
//Se o Valor com Taxa estiver calculado ...
    if(document.form.txt_valor_com_taxa.value != '') {
        var valor_com_taxa = eval(strtofloat(document.form.txt_valor_com_taxa.value))
    }else {
        var valor_com_taxa = 0
    }
//Aqui eu verifico a qtde de Prazos da Nota principal ...
    if(typeof(elementos['txt_dias[]'][0]) == 'undefined') {
        total_prazos = 1//Existe apenas 1 único elemento ...
    }else {
        total_prazos = (elementos['txt_dias[]'].length)
    }
//Tratamento com o Prazo Principal da Nota à Debitar ...
    if(document.form.txt_prazo.value != '') {
        prazo = eval(document.form.txt_prazo.value)
    }else {
        prazo = 0
    }
    var valor_parcelas = arred(String(valor_com_taxa / total_prazos), 2, 1)

    for(i = 1; i <= total_prazos; i++) {
        if(valor_com_taxa != 0) {
//Aqui é o Novo Prazo p/ a Nota à Debitar ...
            document.getElementById('txt_dias_financ'+i).value = eval(document.getElementById('txt_dias'+i).value) + prazo 
//Aqui é a Nova Data ...
            nova_data('<?=$data_emissao;?>', document.getElementById('txt_nova_data_financ'+i), document.getElementById('txt_dias_financ'+i))
//Aqui é o Novo Valor
            document.getElementById('txt_valor_parcela_financ'+i).value = valor_parcelas
        }else {
//Limpa as caixinhas ...
            document.getElementById('txt_dias_financ'+i).value          = ''
            document.getElementById('txt_nova_data_financ'+i).value     = ''
            document.getElementById('txt_valor_parcela_financ'+i).value = ''
        }
    }
}
</Script>
</head>
<body onload='calcular_geral()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onSubmit='return validar()'>
<!--*****************************Controles de Tela*****************************-->
<input type='hidden' name='id_nfe_outro_fornec' value="<?=$id_nfe_outro_fornec;?>">
<input type='hidden' name='id_nfe' value="<?=$_GET['id_nfe'];?>">
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='atualizar_prazos' value='N'>
<!--***************************************************************************-->
<table border='0' width='90%' align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
            <td colspan='2'>
                <b><?=$mensagem[$valor];?></b>
            </td>
	</tr>
	<tr class='linhacabecalho' align='center'>
            <td colspan='2'>
                Alterar Financiamento
            </td>
	</tr>
	<tr class='linhanormal'>
            <td width='30%'><b>Fornecedor:</b></td>
            <td width='70%'>
                <?=$fornecedor_debitar;?>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td><b>Número da Nota:</b></td>
            <td>
                <?=$num_nota_debitar;?>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td><b>Data de Emissão:</b></td>
            <td>
                <?=$data_emissao;?>
<!--Esse campo da Nota Fiscal Debitar vai ser gravado na NF Principal depois que eu submeter ...-->
                <input type='hidden' name="txt_data_emissao_nf_deb" value="<?=data::datatodate($data_emissao, '-');?>">
            </td>
	</tr>
        
        <?
            /*****Segurança para Comparar Prazos*****/
            //Se esse prazos da NF estiverem vazios, então p/ a 1ª vez q entrar NF eu sugiro os prazos do Fornecedor ...
            if($financiamento_taxa == 0 || $financiamento_prazo_dias == '0.0') {
                $financiamento_taxa         = $financiamento_taxa_fornecedor;
                $financiamento_prazo_dias   = $financiamento_prazo_dias_fornecedor;
            }
            /****************************************/
        ?>
	<tr class='linhanormal'>
            <td><b>Taxa Financeira deste Fornecedor:</b></td>
            <td>
                <input type='text' name="txt_taxa" value="<?=$financiamento_taxa;?>" title="Digite a Taxa" size="7" maxlength="6" onkeyup="verifica(this, 'moeda_especial', '1', '', event);calcular_geral()" class='textdisabled' disabled> %
                <font color="red">
                    <b>(Taxa Atual do Cadastro = <?=$financiamento_taxa_fornecedor;?>)</b>
                    <input type='hidden' name='hdd_taxa' value="<?=$financiamento_taxa_fornecedor;?>">
                </font>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td><b>Prazo de Financeira deste Fornecedor:</b></td>
            <td>
                <input type='text' name="txt_prazo" value="<?=$financiamento_prazo_dias;?>" title="Digite o Prazo" size="7" maxlength="6" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == '00' || this.value == '000' || this.value == '0000' || this.value == '00000' || this.value == '000000') {this.value = ''};calcular_geral()" class='textdisabled' disabled> Dias
                <font color="red">
                    <b>(Prazo Atual do Cadastro = <?=$financiamento_prazo_dias_fornecedor;?>)</b>
                    <input type='hidden' name="hdd_prazo" value="<?=$financiamento_prazo_dias_fornecedor;?>">
                </font>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td><b>Taxa Final:</b></td>
            <td>
                <input type='text' name="txt_taxa_final" title="Taxa Final" size="7" maxlength="6" class='textdisabled' disabled> %
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <b>Compra de Insumo p/ Terceiros:</b>
            </td>
            <td>
            <?
//Aqui eu verifico se já existe esse campo de Compra de Insumo p/ Terceiros na Tabela da NF Principal ...
                $sql = "SELECT id_nfe_historico, valor_entregue 
                        FROM `nfe_historicos` 
                        WHERE `id_nfe` = '$id_nfe' 
                        AND `cod_tipo_ajuste` = 1 ";
                $campos_item_nf = bancos::sql($sql);
                if(count($campos_item_nf) == 1) {
                    //Por ser uma NF que será descontada do Fornecedor, então jogo o sinal Negativo ...
                    $compra_insumo = number_format(-$campos_item_nf[0]['valor_entregue'], 2, ',', '.');
                }else {//Se não estiver com esse valor ainda gravado na Base de dados ...
                    //Busca o Valor Total da Nota pela função ...
                    $compra_insumo = number_format(-$valor_total_nota, 2, ',', '.');
                }
            ?>
                R$ <input type='text' name='txt_compra_insumo' value="<?=$compra_insumo;?>" title="Ajuste (Compra de Insumo p/ Terceiros)" size='15' maxlength='14' class='textdisabled' disabled>
                <input type='hidden' name="hdd_item_compra_insumo" value="<?=$campos_item_nf[0]['id_nfe_historico'];?>">
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <b>Ajuste de IPI:</b>
            </td>
            <td>
                <?
                    if($ignorar_impostos_financiamento == 'S') {//Se a NF possui essa marcação, então o IPI não é Contabilizado ...
                        $ajuste_ipi = '0,00';
                    }else {
                        //Aqui eu verifico se já existe esse campo de Ajuste de IPI p/ Terceiros na Tabela da NF Principal ...
                        $sql = "SELECT id_nfe_historico, valor_entregue 
                                FROM `nfe_historicos` 
                                WHERE `id_nfe` = '$id_nfe' 
                                AND `cod_tipo_ajuste` = '2' ";
                        $campos_nfe_item = bancos::sql($sql);
                        if(count($campos_nfe_item) == 1) {
                            $ajuste_ipi = number_format($campos_nfe_item[0]['valor_entregue'], 2, ',', '.');
                        }else {//Sugere o Valor de Total do IPI da Nota Fiscal à Debitar
                            $ajuste_ipi = number_format($valor_ipi, 2, ',', '.');
                        }
                    }
                ?>
                R$ <input type='text' name="txt_ajuste_ipi" value="<?=$ajuste_ipi;?>" title="Digite o Ajuste de IPI" size="15" maxlength="14" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_geral()" class='textdisabled' disabled>
                <input type='hidden' name="hdd_item_ajuste_ipi" value="<?=$campos_nfe_item[0]['id_nfe_historico'];?>">
                <?=$mensagem_impostos_financiamento;?>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <b>Ajuste de ICMS:</b>
            </td>
            <td>
                <?
                    if($ignorar_impostos_financiamento == 'S') {//Se a NF possui essa marcação, então o ICMS não é Contabilizado ...
                        $ajuste_icms = '0,00';
                    }else {
                        //Aqui eu verifico se já existe esse campo de Ajuste de ICMS p/ Terceiros na Tabela da NF Principal ...
                        $sql = "SELECT id_nfe_historico, valor_entregue 
                                FROM `nfe_historicos` 
                                WHERE `id_nfe` = '$id_nfe' 
                                AND `cod_tipo_ajuste` = '3' ";
                        $campos_nfe_item = bancos::sql($sql);
                        if(count($campos_nfe_item) == 1) {
                            $ajuste_icms = number_format($campos_nfe_item[0]['valor_entregue'], 2, ',', '.');
                        }else {//Sugere o Valor de Total do ICMS da Nota Fiscal à Debitar
                            $ajuste_icms = number_format($valor_icms, 2, ',', '.');
                        }
                    }
                ?>
                R$ <input type='text' name="txt_ajuste_icms" value="<?=$ajuste_icms;?>" title="Digite o Ajuste de ICMS" size="15" maxlength="14" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_geral()" class='textdisabled' disabled>
                <input type='hidden' name="hdd_item_ajuste_icms" value="<?=$campos_nfe_item[0]['id_nfe_historico'];?>">
                <?=$mensagem_impostos_financiamento;?>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <b>Valor à ser Financiado:</b>
            </td>
            <td>
                R$ <input type='text' name="txt_valor_ser_financiado" title="Digite o Valor à ser Financiado" size="15" maxlength="14" class='textdisabled' disabled>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <b>Valor c/ Taxa:</b>
            </td>
            <td>
                R$ <input type='text' name="txt_valor_com_taxa" title="Valor c/ Taxa" size="15" maxlength="14" class='textdisabled' disabled>
                <?
//Aqui eu verifico se já existe esse campo de Ajuste de Financiamento na Tabela da NF Principal ...
                    $sql = "SELECT id_nfe_historico, valor_entregue 
                            FROM `nfe_historicos` 
                            WHERE `id_nfe` = '$id_nfe' 
                            AND `cod_tipo_ajuste` = '6' ";
                    $campos_nfe_item = bancos::sql($sql);
                    if(count($campos_nfe_item) == 1) {//Ajuste de Financiamento
                        $ajuste_financiamento = number_format($campos_nfe_item[0]['valor_entregue'], 2, ',', '.');
                    }
                ?>
                <input type='hidden' name="hdd_item_ajuste_financiamento" value="<?=$campos_nfe_item[0]['id_nfe_historico'];?>">
            </td>
	</tr>
	<tr class='linhanormal'>
            <td colspan='2'>
                <font color='darkblue'>
                    <b>PRAZO(S) DA NOTA: </b><?=$num_nota_debitar;?> - <?=$fornecedor_debitar;?>
                </font>
            </td>
	</tr>
	<?
		if($linhas_financiamento == 0) {
	?>
	<tr class='linhanormal'>
		<td colspan='2'>
			<font color="red">
				<b>NÃO EXISTE(M) VENCIMENTO(S) GERADO(S) P/ ESTA NOTA FISCAL.</b>
			</font>
		</td>
	</tr>
	<?
		}else {
			for($i = 0; $i < $linhas_financiamento; $i++) {
	?>
	<tr class='linhanormal'>
		<td width="209">
			<font color='darkblue'>
				<b>Parcela N.º <?=$i + 1;?>:</b>
			</font>
		</td>
		<td>
			<font color='darkblue'>Dias: </font>
			<input type='text' name="txt_dias[]" id="txt_dias<?=$i + 1;?>" value="<?=$campos_financiamento[$i]['dias'];?>" size="8" maxlength="7" class='textdisabled' disabled>
			| 
			<font color='darkblue'>Data: </font>
			<input type='text' name="txt_data[]" id="txt_data<?=$i + 1;?>" value="<?=data::datetodata($campos_financiamento[$i]['data'], '/');?>" size="12" maxlength="10" class='textdisabled' disabled>
			| 
			<font color='darkblue'>Valor <?=$campos_financiamento[$i]['simbolo'];?>: </font>
			<input type='text' name="txt_valor_parcela[]" id="txt_valor_parcela<?=$i + 1;?>" value="<?=number_format($campos_financiamento[$i]['valor_parcela_nf'], 2, ',', '.');?>" size="15" maxlength="14" class='textdisabled' disabled>
			<input type='hidden' name="hdd_nfe_financiamento[]" value="<?=$campos_financiamento[$i]['id_nfe_financiamento'];?>">
		</td>
	</tr>
	<?
			}
		}
	?>
	<tr class='linhanormal'>
		<td colspan='2'>
			<font color="darkgreen">
				<b>NOVOS PRAZO(S) DA NOTA: </b><?=$num_nota_principal;?> - <?=$fornecedor_principal;?>
			</font>
		</td>
	</tr>
	<?
		if($linhas_financiamento == 0) {
	?>
	<tr class='linhanormal'>
		<td colspan='2'>
			<font color="red">
				<b>NÃO EXISTE(M) VENCIMENTO(S) GERADO(S) P/ ESTA NOTA FISCAL.</b>
			</font>
		</td>
	</tr>
	<?
		}else {
			for($i = 0; $i < $linhas_financiamento; $i++) {
	?>
	<tr class='linhanormal'>
		<td width="209">
			<font color="darkgreen">
				<b>Parcela N.º <?=$i + 1;?>:</b>
			</font>
		</td>
		<td>
			<font color="darkgreen">Dias: </font>
			<input type='text' name="txt_dias_financ[]" id="txt_dias_financ<?=$i + 1;?>" size="8" maxlength="7" class='textdisabled' disabled>
			| 
			<font color="darkgreen">Data: </font>
			<input type='text' name="txt_nova_data_financ[]" id="txt_nova_data_financ<?=$i + 1;?>" size="12" maxlength="10" class='textdisabled' disabled>
			| 
			<font color="darkgreen">Valor <?=$campos_financiamento[$i]['simbolo'];?>: </font>
			<input type='text' name="txt_valor_parcela_financ[]" id="txt_valor_parcela_financ<?=$i + 1;?>" size="15" maxlength="14" class='textdisabled' disabled>
		</td>
	</tr>
	<?
			}
		}
	?>
	<tr class="linhacabecalho" align="center">
            <td colspan='2'>
                <input type='button' name='cmd_voltar_cabecalho' value='&lt;&lt; Voltar p/ Cabeçalho &lt;&lt;' title='Voltar p/ Cabeçalho' onclick="document.form.nao_atualizar.value = 1;window.location = 'alterar_cabecalho.php?id_nfe=<?=$id_nfe;?>'" class='botao'>
                <input type="reset" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="document.form.reset();calcular_geral()" style="color:#ff9900;" class="botao">
                <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
                <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick='fechar(window)' class="botao">
            </td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>