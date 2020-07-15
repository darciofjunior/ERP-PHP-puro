<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca 'compras_new' ...
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');
require('../../../../../lib/variaveis/compras.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

if($passo == 1) {
    $data_sys       = date('Y-m-d H:i:s');
    $ipi_incluso    = (!empty($_POST['chkt_ipi_incluso'])) ? 'S' : 'N';
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_fornecedor = (!empty($_POST[cmb_fornecedor])) ? "'".$_POST[cmb_fornecedor]."'" : 'NULL';

//Inserindo o Ajuste escolhido pelo usuário na Parte de Pedidos ...
    $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `id_fornecedor`, `preco_unitario`, `ipi`, `ipi_incluso`, `qtde`, `marca`) VALUES (NULL, '$_POST[hdd_pedido]', '1340', $cmb_fornecedor, '$_POST[txt_preco_unitario]', '$_POST[txt_ipi]', '$ipi_incluso', '$_POST[txt_quantidade]', '$_POST[txt_marca]') ";
    bancos::sql($sql);
    $id_item_pedido = bancos::id_registro();

    //Verifico se existe pelo menos um Prazo de Financiamento para este Pedido ...
    $sql = "SELECT `id_pedido_financiamento` 
            FROM `pedidos_financiamentos` 
            WHERE `id_pedido` = '$_POST[hdd_pedido]' LIMIT 1 ";
    $campos_pedidos_financiamento = bancos::sql($sql);
    if(count($campos_pedidos_financiamento) == 0) {//Não existe então vou criar pelo menos 1 como à Vista ...
        //Busco a Data de Emissão do Pedido para jogar no Prazo de Financiamento do Pedido ...
        $sql = "SELECT `prazo_entrega` 
                FROM `pedidos` 
                WHERE `id_pedido` = '$_POST[hdd_pedido]' LIMIT 1 ";
        $campos_pedido 	= bancos::sql($sql);
        //Gero um Prazo de Financiamento para o Pedido, mas gero este como sendo à vista, p/ facilitar aqui na NF ...
        $sql = "INSERT INTO `pedidos_financiamentos` (`id_pedido_financiamento`, `id_pedido`, `dias`, `data`) VALUES (NULL, '$_POST[hdd_pedido]', '0', '".$campos_pedido[0]['prazo_entrega']."') ";
        bancos::sql($sql);
    }
    ////////// AQUI ENTRA A PARTE DA INSERÇÃO DO AJUSTE NA NOTA FISCAL //////////
    //                                                                         //
    /////////////////////////////////////////////////////////////////////////////

//Inserindo o Ajuste escolhido pelo usuário na Parte de Nota Fiscal ...
    $sql = "INSERT INTO `nfe_historicos` (`id_nfe_historico`, `id_item_pedido`, `id_produto_insumo`, `id_nfe`, `id_pedido`, `cod_tipo_ajuste`, `tipo`, `qtde_entregue`, `valor_entregue`, `ipi_entregue`, `ipi_incluso`, `icms_entregue`, `reducao`, `iva`, `marca`, `data_prox_entrega`, `nf_obs_abatimento`, `data_sys`) VALUES (NULL, '$id_item_pedido', '1340', '$_POST[id_nfe]', '$_POST[hdd_pedido]', '$_POST[cmb_tipo_ajuste]', 'E', '$_POST[txt_quantidade]', '$_POST[txt_preco_unitario]', '$_POST[txt_ipi]', '$ipi_incluso', '$_POST[txt_icms]', '$_POST[txt_reducao]', '$_POST[txt_iva]', '$_POST[txt_marca]', '', '$_POST[txt_num_nf]', '$data_sys') ";
    bancos::sql($sql);
    $id_nfe_historico = bancos::id_registro();
    
//Controle com os Status de Item do Pedido e com o próprio Pedido ...
    compras_new::pedido_status($id_item_pedido);
//Aqui eu verifico se a NF possui formas de Vencimento ...
    $sql = "SELECT `id_nfe_financiamento` 
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
    
    /*Significa que o Usuário simplesmente desejou reaproveitar uma Nota Fiscal de Entrada que existe 
    e o Sistema sugeriu como opção ao $id_nfe_historico de "Ajuste" que acabou de ser Incluso mais acima ...*/
    if($_POST['hdd_acao_nfe_debitar'] == 'REAPROVEITAR') {
        $vetor_possui_nfe_debitar   = explode('|', $_POST['hdd_possui_nfe_debitar']);
            
        /*Atrelo a Nota Fiscal de Entrada que o Usuário simplesmente desejou reaproveitar ao 
        $id_nfe_historico de "Ajuste" que acabou de ser Incluso mais acima ...*/
        $sql = "UPDATE `nfe_historicos` SET `id_nfe_debitar` = '$vetor_possui_nfe_debitar[0]' WHERE `id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        var hdd_acao_nfe_debitar = '<?=$_POST['hdd_acao_nfe_debitar'];?>'
        
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
        window.close()
        
        if(hdd_acao_nfe_debitar == 'INCLUIR') {//Significa que o Usuário deseja Incluir uma Nova Nota Fiscal de Entrada ...
            nova_janela('../incluir.php?passo=2&id_nfe_historico=<?=$id_nfe_historico;?>&id_fornecedor=<?=$_POST['cmb_fornecedor'];?>&num_nota=<?=$_POST['hdd_num_nota'];?>&data_emissao=<?=$_POST['hdd_data_emissao'];?>', 'CABECALHO', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')
        }else if(hdd_acao_nfe_debitar == 'REAPROVEITAR') {//Significa que o Usuário simplesmente desejou reaproveitar uma Nota Fiscal de Entrada que existe e o Sistema sugeriu como opção ...
            var hdd_possui_nfe_debitar   = '<?=$_POST['hdd_possui_nfe_debitar'];?>'
            var vetor_possui_nfe_debitar = hdd_possui_nfe_debitar.split('|')
            var id_nfe                   = vetor_possui_nfe_debitar[0]
            
            nova_janela('../alterar_cabecalho.php?id_nfe='+id_nfe, 'CABECALHO', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')
        }
    </Script>
<?
}

//Busca do id_fornecedor, N.º de NF e Data de Emissão através do $id_nfe passado por parâmetro ...
$sql = "SELECT f.`razaosocial`, nfe.`id_fornecedor`, nfe.`num_nota`, 
        SUBSTRING(nfe.`data_emissao`, 1, 10) AS data_emissao 
        FROM `nfe` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
        WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
$campos         = bancos::sql($sql);
$fornecedor     = $campos[0]['razaosocial'];
$id_fornecedor  = $campos[0]['id_fornecedor'];
$num_nota       = $campos[0]['num_nota'];
$data_emissao   = $campos[0]['data_emissao'];

//Aqui eu busco o id_pedido, referente ao último item que foi importado na NF
$sql = "SELECT `id_pedido` 
        FROM `nfe_historicos` 
        WHERE `id_nfe` = '$id_nfe' ORDER BY `id_nfe_historico` DESC LIMIT 1 ";
$campos = bancos::sql($sql);
if(count($campos) == 1) {//Achou 1 pedido
    $id_pedido = $campos[0]['id_pedido'];
}else {//Essa NF não possui nenhum item de Pedido atrelado
/*Verifica se o Fornecedor possui algum pedido em aberto que foi feito pelo modo de Financimento, sendo assim 
ele busca o último pedido deste que ainda não foi importado para NF*/
    $sql = "SELECT p.`id_pedido` 
            FROM `pedidos` p 
            INNER JOIN `pedidos_financiamentos` pf ON pf.`id_pedido` = p.`id_pedido` 
            WHERE p.`id_fornecedor` = '$id_fornecedor' 
            AND p.`ativo` < '2' ORDER BY p.`id_pedido` DESC LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Encontrou
        $id_pedido = $campos[0]['id_pedido'];
    }else {//Não encontrou
/*O Fornecedor não possui nenhum pedido em aberto, sendo assim ele busca o último pedido 
que foi feito pelo modo de Financimento deste que já foi importado para NF*/
        $sql = "SELECT p.`id_pedido` 
                FROM `pedidos` p 
                INNER JOIN `pedidos_financiamentos` pf ON pf.`id_pedido` = p.`id_pedido` 
                WHERE p.`id_fornecedor` = '$id_fornecedor' 
                AND p.`ativo` = '2' ORDER BY p.`id_pedido` DESC LIMIT 1 ";    
        $campos     = bancos::sql($sql);
        $id_pedido  = $campos[0]['id_pedido'];
    }
}

/*Se retornou Zero para pedido, que é este fornecedor nunca teve nenhum pedido anteriormente,
sendo assim o sistema força o usuário a Cadastrar um Pedido deste pelo menos*/
if($id_pedido == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO PODE SER INCLUSO NENHUM AJUSTE PARA ESTA NOTA !\nNÃO EXISTE PEDIDO PARA ESTE FORNECEDOR OU ATÉ EXISTE PEDIDO MAIS QUE ESTÁ SEM FINANCIAMENTO !')
        window.close()
    </Script>
<?
    exit;
}
?>
<html>
<head>
<title>.:: Incluir Ajuste ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function calcular_dif_preco(txt_valor_total_tela_itens) {
    if(document.form.txt_ipi.value == '') {
        alert('DIGITE O IPI!')
        document.form.txt_ipi.focus()
        return false
    }
    if(document.form.txt_valor_total_nf.value == '' || document.form.txt_valor_total_nf.value == '0,00') {
        alert('DIGITE O VALOR TOTAL CONTIDA NA NF!')
        document.form.txt_valor_total_nf.focus()
        return false
    }

    var valor_total_nf  = eval(strtofloat(document.form.txt_valor_total_nf.value))
    var ipi             = eval(strtofloat(document.form.txt_ipi.value))
    var resultado       = (valor_total_nf - txt_valor_total_tela_itens) / (1 + ipi / 100)
    if(resultado < 0) {//caso o result for negativo transformar em posittivo e a qtde em negativo, pois o valor não pode ser negativo
        document.form.txt_quantidade.value = '-1,00'
        resultado*= (-1)
    }else {
        document.form.txt_quantidade.value = '1,00'
    }
    document.form.txt_preco_unitario.value = resultado
    document.form.txt_preco_unitario.value = arred(document.form.txt_preco_unitario.value, 2, 1)
    calcular()
}

function calcular() {
    var quantidade      = eval(strtofloat(document.form.txt_quantidade.value))
    var preco_unitario  = eval(strtofloat(document.form.txt_preco_unitario.value))

    if(quantidade != '') {
        if(document.form.txt_preco_unitario.value == '') {
            document.form.txt_valor_total.value = ''
        }else {
            document.form.txt_valor_total.value = preco_unitario * quantidade
            if(document.form.txt_valor_total.value == 'NaN') {
                document.form.txt_valor_total.value = ''
            }else {
                document.form.txt_valor_total.value = arred(document.form.txt_valor_total.value, 2)
            }
        }
    }else {
        document.form.txt_valor_total.value = ''
    }
}

/*Esse parâmetro "debitar_fornecedor" que está no Escopo dessa função só será requirido quando o usuário 
chamar essa função através do botão "Incluir Nota p/ Débito" ...*/
function validar(debitar_fornecedor) {
//Tipo de Ajuste ...
    if(!combo('form', 'cmb_tipo_ajuste', '', 'SELECIONE O TIPO DE AJUSTE !')) {
        return false
    }
//Somente nessa opção "Abatimento de NF" que eu forço o preenchimento desse campo 'N.º NF'
    if(document.form.cmb_tipo_ajuste.value == 4) {//Habilitado ...
//N.º da NF
        if(!texto('form', 'txt_num_nf', '1', '1234567890', 'N.º DA NF', '2')) {
            return false
        }
    }
//Preço Unitário ...
    if(!texto('form', 'txt_preco_unitario', '1', '1234567890,.', 'PREÇO UNITÁRIO', '2')) {
        return false
    }
//Quantidade ...
    if(!texto('form', 'txt_quantidade', '1', '1234567890,.-', 'QUANTIDADE', '1')) {
        return false
    }
//IPI ...
    if(document.form.txt_ipi.value != '') {
        if(!texto('form', 'txt_ipi', '1', '1234567890,.', 'IPI', '2')) {
            return false
        }
    }
//IPI Incluso ...
    if(document.form.chkt_ipi_incluso.checked == true) {
        var ipi = (document.form.txt_ipi.value == '') ? 0 : eval(strtofloat(document.form.txt_ipi.value))
        //IPI ...
        if(ipi == 0) {
            alert('IPI INVÁLIDO !!!\n\nIPI IGUAL A ZERO !')
            document.form.txt_ipi.focus()
            document.form.txt_ipi.select()
            return false
        }
    }
//ICMS ...
    if(document.form.txt_icms.value != '') {
        if(!texto('form', 'txt_icms', '1', '1234567890,.', 'ICMS', '2')) {
            return false
        }
    }
//Redução ...
    if(document.form.txt_reducao.value != '') {
        if(!texto('form', 'txt_reducao', '1', '1234567890,.', 'REDUÇÃO', '1')) {
            return false
        }
    }
//IVA ...
    if(document.form.txt_iva.value != '') {
        if(!texto('form', 'txt_iva', '1', '1234567890,.', 'IVA', '2')) {
            return false
        }
    }
/*Significa que essa função foi chamada através do botão "Incluir Nota p/ Débito" e nesse caso o Usuário 
é totalmente forçado a selecionar um fornecedor ...*/
    if(debitar_fornecedor == 'S') {
        //Fornecedor ...
        if(!combo('form', 'cmb_fornecedor', '', 'SELECIONE O FORNECEDOR !')) {
            return false
        }
    }
/*************************************************************************************************/
//Esse acerto é p/ a Gladys não dar + Desconto Positivo ...
//Sempre que o Tipo de Ajuste = à Abatimento de NF, então eu verifico se o valor do Ajuste é Negativo ...
/*************************************************************************************************/
    if(document.form.cmb_tipo_ajuste.value == 4) {//Abatimento de NF ...
        var valor_total = eval(strtofloat(document.form.txt_valor_total.value))

        if(valor_total > 0) {
//Se o Valor Total for Menor do que Zero, então o Sistema tem que barrar ...
            alert('ABATIMENTO DE NF INVÁLIDO !!!\nESTE TIPO DE AJUSTE TEM QUE SER DE VALOR NEGATIVO !')
            document.form.txt_quantidade.focus()
            document.form.txt_quantidade.select()
            return false
        }
    }
/*************************************************************************************************/
/**************************************Debitar do Fornecedor**************************************/
/*************************************************************************************************/
    /*Se o Usuário selecionou um Fornecedor de modo Obrigado ou Espontâneo, então o Sistema faz essas 
    verificações abaixo ...*/
    if(document.form.cmb_fornecedor.value != '') {
        var fornecedor = '<?=$fornecedor;?>'
    //Aqui eu verifico se o Fornecedor que foi escolhido possui uma Nota Fiscal p/ Debitar ...
        if(document.form.hdd_possui_nfe_debitar.value != '') {//Fornecedor possui alguma Nota Fiscal de Entrada p/ Debitar em aberto ...
            var vetor_possui_nfe_debitar = document.form.hdd_possui_nfe_debitar.value.split('|')
            var num_nota                 = vetor_possui_nfe_debitar[1]

            var resposta = confirm('A NOTA FISCAL DE ENTRADA N.º '+num_nota+' ENCONTRA-SE EM ABERTO P/ O FORNECEDOR "'+fornecedor+'" !!!\n\nDESEJA UTILIZAR ESSA NOTA FISCAL ?')
            if(resposta == true) {//Significa que o usuário simplesmente quis reaproveitar a Nota Fiscal ja existente e sugerida pelo Sistema ...
                document.form.hdd_acao_nfe_debitar.value = 'REAPROVEITAR'
            }else {//O usuário não quis reaproveitar nada, então pergunto se o mesmo deseja Incluir uma Nova Nota Fiscal de Entrada ...
                var resposta = confirm('DESEJA INCLUIR UMA NOTA FISCAL DE ENTRADA P/ DEBITAR DO FORNECEDOR "'+fornecedor+'" ?')
                if(resposta == true) {
                    document.form.hdd_acao_nfe_debitar.value = 'INCLUIR'
                }
            }
        }else {//Fornecedor não possui nenhuma Nota Fiscal de Entrada p/ Debitar em aberto ...
            var resposta = confirm('DESEJA INCLUIR UMA NOTA FISCAL DE ENTRADA P/ DEBITAR DO FORNECEDOR "'+fornecedor+'" ?')
            if(resposta == true) {
                document.form.hdd_acao_nfe_debitar.value = 'INCLUIR'
            }
        }
    }
/*************************************************************************************************/
//Preparando p/ gravar no BD ...
    document.form.txt_valor_total.disabled = false
    return limpeza_moeda('form', 'txt_preco_unitario, txt_quantidade, txt_valor_total, txt_ipi, txt_icms, txt_reducao, txt_iva, ')
}

function controlar_numero_nf() {
//Somente nessa opção "Abatimento de NF" que eu habilito esse campo 'N.º NF'
    if(document.form.cmb_tipo_ajuste.value == 4) {//Habilitado ...
        document.form.txt_num_nf.className  = 'caixadetexto'
        document.form.txt_num_nf.disabled   = false
        document.form.txt_num_nf.focus()
    }else {//Desabilitado ...
        document.form.txt_num_nf.className  = 'textdisabled'
        document.form.txt_num_nf.disabled   = true
        document.form.txt_num_nf.value      = ''
    }
}
</Script>
</head>
<body onload='document.form.cmb_tipo_ajuste.focus()'>
<form name='form' action='<?=$PHP_SELF.'?passo=1';?>' method='post'>
<!--*****************************************-->
<!--Aqui é para quando for submeter-->
<input type='hidden' name='id_nfe' value='<?=$id_nfe;?>'>
<!--Guardo esses id(s) porque daí fica muito + fácil lá no outro passo-->
<input type='hidden' name='hdd_pedido' value='<?=$id_pedido;?>'>
<input type='hidden' name='hdd_num_nota' value='<?=$num_nota;?>'>
<input type='hidden' name='hdd_data_emissao' value='<?=$data_emissao;?>'>
<!--******Controles de Tela******-->
<input type='hidden' name='hdd_acao_nfe_debitar'>
<!--Nesse hidden eu guardo o "id_nfe" em primeiro plano e o "N.º da Nota Fiscal" em segundo plano ...-->
<input type='hidden' name='hdd_possui_nfe_debitar'>
<!--*****************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Ajuste
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Ajuste:</b>
        </td>
        <td>
            <select name='cmb_tipo_ajuste' title='Selecione o Tipo de Ajuste' onchange='controlar_numero_nf()' class='combo'>
                <?=combos::combo_array($tipos_ajustes);?>
            </select>
            &nbsp;&nbsp;<b>N.º da NF:</b>&nbsp;
            <input type='text' name='txt_num_nf' title='Digite o N.º da NF' size='12' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço:</b>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario' title='Digite o Preço Unitário' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" onblur='calcular()' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade:</b>
        </td>
        <td>
            <input type='text' name='txt_quantidade' value='1,00' title='Digite a Quantidade' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '1', event);calcular()" onblur='calcular()' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Total R$: 
        </td>
        <td>
            <input type='text' name='txt_valor_total' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            IPI %:
        </td>
        <td>
            <input type='text' name='txt_ipi' title='Digite o IPI %' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
            <input type='checkbox' name='chkt_ipi_incluso' id='chkt_ipi_incluso' value='S' class='checkbox'>
            <label for='chkt_ipi_incluso'>
                <font color='red' title='IPI Incluso' style='cursor:help'>
                    <b>(Incl)</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ICMS %:
        </td>
        <td>
            <input type='text' name='txt_icms' title='Digite o ICMS %' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Redução %:
        </td>
        <td>
            <input type='text' name='txt_reducao' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            IVA:
        </td>
        <td>
            <input type='text' name='txt_iva' title='Digite o IVA' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Marca / Obs:
        </td>
        <td>
            <input type='text' name='txt_marca' size='52' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Debitar do Fornecedor:
        </td>
        <td>
            <select name='cmb_fornecedor' title='Selecione o Fornecedor' onchange="ajax('verificar_nfe_debitar.php?id_fornecedor='+this.value+'&num_nota=<?=$num_nota;?>&data_emissao=<?=$data_emissao;?>', 'hdd_possui_nfe_debitar')" class='combo'>
            <?
                $sql = "SELECT `id_fornecedor`, `razaosocial` 
                        FROM `fornecedores` 
                        WHERE `material_ha_debitar` = '1' 
                        AND `ativo` = '1' ORDER BY `razaosocial` ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <input type='submit' name='cmd_incluir_nota_debito' value='Incluir Nota p/ Débito' title='Incluir Nota p/ Débito' onclick="return validar('S')" class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <hr width='100%'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <table width='90%' border='1' cellspacing='1' cellpadding='1' align='center'>
                <tr class='linhanormal'>
                    <td>
                        Valor Total da NF: <input type='text' name='txt_valor_total_nf' title='Digite o Valor Total da NF' size='12' maxlength="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
                    </td>
                    <td>
                        Valor Total da Tela de Itens:
                        <?
                            $calculo_total_impostos = calculos::calculo_impostos(0, $id_nfe, 'NFC');
                        ?>
                        <input type='text' name="txt_valor_total_tela_itens" size="12" maxlength="10" value="<?=number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');?>" class='textdisabled' disabled>
                    </td>
                    <td align='center'>
                        <input type='button' name="cmd_calcular" value="Calcular Diferença de Preço da NF" title="Calcular Diferença de Preço da NF" onclick="calcular_dif_preco('<?=$calculo_total_impostos['valor_total_nota'];?>')" style='color:blue' class='botao'>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <hr width='100%'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_ajuste' value='Adicionar Ajuste' title='Adicionar Ajuste' style='color:green' onclick='return validar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
<b>Somente no caso de ajuste do Tipo Produto Acabado c/ IVA > 0, o sistema calcula ICMS ST. 
Para qualquer outro Tipo de Ajuste, o ICMS será calculado como oculto.</b>
</pre>