<?
require('../../../../lib/segurancas.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../lib/intermodular.php');
require('../../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

$mensagem[1] = 'ITEM INCLUIDO COM SUCESSO !';
$mensagem[2] = 'ITEM INCLUIDO NOVAMENTE COM SUCESSO !';

if($passo == 1) {
//Aqui atribui o desconto para os Itens do Pedido ...
    $desconto_especial          = ($_POST['chkt_desconto_especial'] == 1) ? 'S' : 'N';
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_fornecedor             = (!empty($_POST[cmb_fornecedor])) ? "'".$_POST[cmb_fornecedor]."'" : 'NULL';
    $cmb_fornecedor_terceiro    = (!empty($_POST[cmb_fornecedor_terceiro])) ? "'".$_POST[cmb_fornecedor_terceiro]."'" : 'NULL';
    
    $sql = "SELECT ip.`id_item_pedido`, ip.`id_produto_insumo` 
            FROM `itens_pedidos` ip 
            INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` 
            WHERE ip.`id_produto_insumo` = '$_POST[cmb_produto_insumo]' 
            AND ip.`id_pedido` = '$_POST[id_pedido]' 
            AND ip.`qtde` = '$_POST[txt_qtde]' ORDER BY ip.`id_produto_insumo` DESC LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Item de Pedido q ainda não foi cadastrado
//Este if é por causa do ajuste de preco
        if($txt_qtde == '') $txt_qtde = 1.00;
//Significa que foi escolhido a Opção de ajuste
        if($hdd_ajuste == 1) {
//Inserção dos Itens de Pedido de Compras (Ajuste)
            $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `id_fornecedor`, `id_fornecedor_terceiro`, `preco_unitario`, `desconto_especial`, `qtde`, `marca`, `estocar`) VALUES (NULL, '$id_pedido', '1340', $cmb_fornecedor, $cmb_fornecedor_terceiro, '$txt_preco_unitario', '$desconto_especial', '$txt_qtde', '$_POST[txt_marca]', '$chkt_estocar') ";
            bancos::sql($sql);
//Não foi escolhido nenhum ajuste
        }else {
//Inserção dos Itens de Pedido de Compras
            $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `id_fornecedor`, `id_fornecedor_terceiro`, `preco_unitario`, `desconto_especial`, `qtde`, `ipi`, `ipi_incluso`, `marca`, `estocar`) VALUES (NULL, '$id_pedido', '$_POST[cmb_produto_insumo]', $cmb_fornecedor, $cmb_fornecedor_terceiro, '$txt_preco_unitario', '$desconto_especial', '$txt_qtde', '$txt_ipi', '$_POST[hdd_ipi_incluso]', '$_POST[txt_marca]', '$chkt_estocar') ";
            bancos::sql($sql);
            //Se foi solicitada a troca do Fornecedor Default ...
            if($_POST['hdd_fornecedor_default'] == 1) $trocar_fornecedor_default = 1;
        }
        $valor = 1;
    }else {//Item de Pedido q está sendo cadastrado novamente
//Significa que foi escolhido a Opção de ajuste
        if($hdd_ajuste == 1) {
//Aqui tem que fazer esse cálculo, porque o usuário não é obrigado a digitar a qtde
            $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `id_fornecedor`, `id_fornecedor_terceiro`, `preco_unitario`, `desconto_especial`, `qtde`, `marca`, `estocar`) VALUES (NULL, '$id_pedido', '1340', $cmb_fornecedor, $cmb_fornecedor_terceiro, '$txt_preco_unitario', '$desconto_especial', '$txt_qtde', '$_POST[txt_marca]', '$chkt_estocar') ";
            bancos::sql($sql);
//Não foi escolhido nenhum ajuste
        }else {
//Inserção dos Itens de Pedido de Compras
            $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `id_fornecedor`, `id_fornecedor_terceiro`, `preco_unitario`, `desconto_especial`, `ipi`, `ipi_incluso`, `qtde`, `marca`, `estocar`) VALUES (NULL, '$id_pedido', '$_POST[cmb_produto_insumo]', $cmb_fornecedor, $cmb_fornecedor_terceiro, '$txt_preco_unitario', '$desconto_especial', '$txt_ipi', '$_POST[hdd_ipi_incluso]', '$txt_qtde', '$_POST[txt_marca]', '$chkt_estocar') ";
            bancos::sql($sql);
            if($_POST['hdd_fornecedor_default'] == 1) {//Se foi solicitada a troca do Fornecedor Default ...
                $trocar_fornecedor_default = 1;
            }
        }
        $valor = 2;
?>
	<Script Language = 'JavaScript'>
            alert('ESTE ITEM DE PEDIDO JÁ FOI CADASTRADO E ESTÁ SENDO INSERIDO NOVAMENTE !\n\n CERTIFIQUE-SE DE QUE A DUPLICIDADE DESSE(S) ITEM(NS) ESTEJEM CORRETAS !')
	</Script>
<?
    }
//Se foi solicitado p/ fazer a troca do Fornecedor de Default então, tem alguns procedimentos a serem feitos ...
    if($trocar_fornecedor_default == 1) {
/*****************************************E-mail*****************************************/
//Busca do Fornecedor Default até antes da troca e do Produto Insumo p/ enviar por e-mail ...
        $sql = "SELECT f.razaosocial, pi.discriminacao 
                FROM `produtos_insumos` pi 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = pi.`id_fornecedor_default` 
                WHERE pi.`id_produto_insumo` = '$_POST[cmb_produto_insumo]' 
                AND pi.`id_fornecedor_default` > '0' ";
        $campos_gerais              = bancos::sql($sql);
        $fornecedor_default_antigo  = $campos_gerais[0]['razaosocial'];
        $produto_insumo             = $campos_gerais[0]['discriminacao'];
//Busca do nome do Novo Fornecedor que será apresentado no e-mail ...
        $sql = "SELECT razaosocial 
                FROM `fornecedores` 
                WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
        $campos_fornecedor          = bancos::sql($sql);
        $fornecedor_default_novo    = $campos_fornecedor[0]['razaosocial'];
//Busca do Nome do Funcionário que está fazendo a Substituição p/ o Novo Fornecedor ...
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login = bancos::sql($sql);
        $login_alterando = $campos_login[0]['login'];
        $mensagem_email = 'O Funcionário <b>'.ucfirst($login_alterando).'</b> fez substituição de Fornecedor Default.';
        $mensagem_email.= '<br><br><b>Fornecedor Default Antigo: </b>'.$fornecedor_default_antigo.' <br><b>Novo Fornecedor Default: </b>'.$fornecedor_default_novo.' <br><b>Produto Insumo: </b>'.$produto_insumo;
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $mensagem_email.= '<br><br><b>Data e Hora: </b>'.date('d/m/Y H:i:s');
//Aqui eu mando um e-mail informando quem está alterando o Fornecedor Default ...
        $destino    = $substituicao_fornecedor_default;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Substituição de Fornecedor Default', $mensagem_email);
/********************************Função que troca o Fornecedor por outro********************************/
        custos::setar_fornecedor_default($_POST['cmb_produto_insumo'], $id_fornecedor, 'S');
    }
//Aqui verifico se o PI é um PA "PIPA" para poder executar a função abaixo ...
    $sql = "SELECT id_produto_acabado 
            FROM `produtos_acabados` 
            WHERE `id_produto_insumo` = '$_POST[cmb_produto_insumo]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_pipa = bancos::sql($sql);
    if(count($campos_pipa) == 1) intermodular::gravar_campos_para_calcular_margem_lucro_estimada($_POST[cmb_produto_insumo]);
?>
    <Script Language = 'JavaScript'>
        parent.fornecedor_produto.document.location = 'superior.php?id_pedido=<?=$_POST['id_pedido'];?>'
        parent.inferior_produto.document.location = 'inferior.php?id_pedido=<?=$_POST['id_pedido'];?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
/******************Dados de Cabeçalho do Pedido******************/
//Aqui eu busco alguns Dados de Cabeçalho do Pedido ...
    $sql = "SELECT prazo_pgto_a, prazo_pgto_b, prazo_pgto_c, desconto_especial_porc, tipo_nota, 
            material_retirado_nosso_estoque 
            FROM `pedidos` 
            WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $prazo_pgto_a                   = $campos[0]['prazo_pgto_a'];
    $prazo_pgto_b                   = $campos[0]['prazo_pgto_b'];
    $prazo_pgto_c                   = $campos[0]['prazo_pgto_c'];
    $total_prazos_cabecalho         = $prazo_pgto_a + $prazo_pgto_b + $prazo_pgto_c;//Utilizo essa variável p/ retornar Alert
    $desconto_especial_porc         = number_format($campos[0]['desconto_especial_porc'], 2, ',', '.');
    $desconto_especial_porc_aux     = $campos[0]['desconto_especial_porc'];//Usa para cálculo + abaixo
    $material_retirado_nosso_estoque = $campos[0]['material_retirado_nosso_estoque'];
    //Tratamento com o Tipo de Cabeçalho ...
    //Utilizo essa variável p/ retornar Alert
    $tipo_cabecalho                 = ($campos[0]['tipo_nota'] == 1) ? 'NF' : 'SGD';
/****************************************************************/
//Significa que já foi escolhido um Produto Insumo no Frame de Cima - arquivo superior.php
//Busca de alguns dados desse Produto, só que desse Fornecedor que está ativo na lista de Preços
    if($cmb_produto_insumo != '') {
        $sql = "SELECT pi.`estocagem`, pi.`observacao`, fpi.`preco`, fpi.`preco_exportacao`, 
                fpi.`ipi`, fpi.`ipi_incluso`, fpi.`preco_faturado_export`, fpi.`prazo_pgto_ddl`, 
                fpi.`forma_compra`, p.`tipo_export`, CONCAT(tm.`simbolo`, ' - ', tm.`moeda`) AS moeda, 
                u.`sigla` 
                FROM `pedidos` p 
                INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_fornecedor` = '$id_fornecedor' AND fpi.`id_produto_insumo` = '$cmb_produto_insumo' AND fpi.`ativo` = '1' 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = p.`id_tipo_moeda` 
                WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos                     = bancos::sql($sql);
        $estocagem                  = $campos[0]['estocagem'];
        $observacao                 = $campos[0]['observacao'];
        $prazo_pagamento            = $campos[0]['prazo_pgto_ddl'];
        $moeda                      = $campos[0]['moeda'];
        $unidade                    = $campos[0]['sigla'];
        if($campos[0]['tipo_export'] == 'N') {
            $preco = str_replace('.', ',', $campos[0]['preco']);
            $preco_aux = $campos[0]['preco'];
        }else if($campos[0]['tipo_export'] == 'E') {
            $preco = str_replace('.', ',', $campos[0]['preco_exportacao']);
            $preco_aux = $campos[0]['preco_exportacao'];
        }else if($campos[0]['tipo_export'] == 'I') {
            $preco = str_replace('.', ',', $campos[0]['preco_faturado_export']);
            $preco_aux = $campos[0]['preco_faturado_export'];
        }
//Aqui já calcula o preço abatendo o desconto que foi dado no Pedido
/*Eu tenho que fazer esse cálculo assim em PHP, sem ter q acionar a função em
JavaScript porque eu não tenho body nesse arquivo*/
        $preco_recalculado_inicio = $preco_aux * (1 - $desconto_especial_porc_aux / 100);
        $preco_recalculado_inicio = number_format($preco_recalculado_inicio, 2, ',', '.');
        $ipi            = $campos[0]['ipi'];
        $ipi_incluso    = $campos[0]['ipi_incluso'];

        $forma_compra   = $campos[0]['forma_compra'];
        $prazo_pgto_ddl = segurancas::number_format($campos[0]['prazo_pgto_ddl'], 1, '.');

        if($forma_compra == 1) {
            $compra = 'FAT/NF';
            $tipo_item = 'NF';//Utilizo essa variável p/ retornar Alert
            $compra_item = 'FAT';//Utilizo essa variável p/ retornar Alert
        }else if($forma_compra == 2) {
            $compra = 'FAT/SGD';
            $tipo_item = 'SGD';//Utilizo essa variável p/ retornar Alert
            $compra_item = 'FAT';//Utilizo essa variável p/ retornar Alert
        }else if($forma_compra == 3) {
            $compra = 'AV/NF';
            $tipo_item = 'NF';//Utilizo essa variável p/ retornar Alert
            $compra_item = 'AV';//Utilizo essa variável p/ retornar Alert
        }else if($forma_compra == 4) {
            $compra = 'AV/SGD';
            $tipo_item = 'SGD';//Utilizo essa variável p/ retornar Alert
            $compra_item = 'AV';//Utilizo essa variável p/ retornar Alert
        }
    }
    //Verifico se o PI é do Tipo PA, vai me auxiliar para a função em JavaScript desabilitar_combo()
    $sql = "SELECT id_produto_acabado 
            FROM `produtos_acabados` 
            WHERE `id_produto_insumo` = '$cmb_produto_insumo' LIMIT 1 ";
    $campos_pipa    = bancos::sql($sql);
    $is_prac        = (count($campos_pipa) == 1) ? 1 : 0;
//Verificação para saber se o Fornecedor Atual em que está se incluindo o Item é o Fornecedor Default ...
    $sql = "SELECT COUNT(id_produto_insumo) AS fornecedor_default 
            FROM `produtos_insumos` 
            WHERE `id_produto_insumo` = '$cmb_produto_insumo' 
            AND `id_fornecedor_default` = '$id_fornecedor' LIMIT 1 ";
    $campos_default         = bancos::sql($sql);
    $is_fornecedor_default  = $campos_default[0]['fornecedor_default'];
?>
<html>
<head>
<title>.:: Incluir Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var id_funcionario = eval('<?=$_SESSION['id_funcionario'];?>') //Usuário Logado ...
    if(parent.fornecedor_produto.document.form.cmb_produto_insumo.value == '') {
        alert('SELECIONE UM PRODUTO INSUMO !')
        return false
    }
//Preço Unitário
    if(!texto('form', 'txt_preco_unitario', '1', '1234567890,.', 'PREÇO UNITÁRIO', '2')) {
        return false
    }
/*Somente o Roberto e o Dárcio que podem passar qualquer preço p/ o Item de Pedido, os demais usuários
só podem estar passando até Preço que é sugerido do BD ...*/
    if(id_funcionario != 62 && id_funcionario != 98) {
//Aqui guarda o preço original do produto quando eu altero da combo, vem direto do BD
        var preco = eval(strtofloat('<?=$preco;?>'))
//Esta variável equivale a caixa de preco unitário digitada + acima pelo usuário
        var preco_unitario_digitada = eval(strtofloat(document.form.txt_preco_unitario.value))
/*Se o Preço Original for <> de 0, então eu faço a próxima comparação, faço isso p/ não dar erro caso 
o usuário desejar colocar Ajuste*/
        if(preco > 0) {
//Aqui faz comparação de Preços, do Preço original com o Preço digitado
            if(preco_unitario_digitada > preco) {
                alert('PREÇO INVÁLIDO !')
                document.form.txt_preco_unitario.focus()
                document.form.txt_preco_unitario.select()
                return false
            }
        }
    }
//Quantidade
    if(!texto('form', 'txt_qtde', '1', '1234567890,.-', 'QUANTIDADE', '1')) {
        return false
    }
//Controle com Quantidade = Zero ...
    var qtde = eval(strtofloat(document.form.txt_qtde.value))
    if(qtde == 0) {
        alert('QUANTIDADE INVÁLIDA !')
        document.form.txt_qtde.focus()
        document.form.txt_qtde.select()
        return false
    }
//Só irá existir esse campo no caso em que PI tem relação com o PA e que este PA é o do Tipo ESP ...
    if(typeof(document.form.txt_lote_minimo_pa_rev) == 'object') {
        var lote_minimo_pa_rev = eval(strtofloat(document.form.txt_lote_minimo_pa_rev.value))
        var qtde = eval(strtofloat(document.form.txt_qtde.value))
/*Se a Qtde digitada for < do que a Quantidade do Lote Mínimo do PA, então significa que está quantidade
está em inadiplência, sendo assim eu dou um alert informando o usuário ...*/
        if(qtde < lote_minimo_pa_rev) {
            alert('QUANTIDADE INVÁLIDA !!!\nA QTDE DESTE ITEM DE PEDIDO É MENOR DO QUE A QTDE DE LOTE MÍNIMO P/ COMPRA !')
            document.form.txt_qtde.focus()
            document.form.txt_qtde.select()
            return false
        }
    }
//Se essa opção estiver marcada, significa que esta mercadoria varia para um Fornecedor Terceiro
    if(document.form.chkt_estocar.checked == false && document.form.chkt_estocar.disabled == false) {
        if(document.form.cmb_fornecedor_terceiro.value == '') {
            alert('SELECIONE O FORNECEDOR TERCEIRO !')
            document.form.cmb_fornecedor_terceiro.focus()
            return false
        }
    }
    var tipo_cabecalho  = '<?=$tipo_cabecalho;?>'
    var tipo_item       = '<?=$tipo_item;?>'
    var compra_item     = '<?=$compra_item;?>'
    var total_prazos_cabecalho = eval('<?=$total_prazos_cabecalho;?>')
    var erro_prazos     = 0
//Se a Compra do Item é a Vista e Existir Prazos no Cabeçalho do Pedido de Compras ...
    //Está incoerente ...
    if(compra_item == 'AV' && total_prazos_cabecalho > 0) erro_prazos = 1
/*Se a Forma de Compra do Cabeçalho do Pedido for Diferente da Forma de Compra do Item do Pedido, 
e a Forma de Pagamento do Cabeçalho do Pedido estiver incompatível coma Forma de Pagamento
do Item do Pedido então o Sistema retorna essa mensagem apenas informando o Usuário, + prossegue 
a rotina normalmente ...*/
    if((tipo_cabecalho != tipo_item) && erro_prazos == 1) {
        alert('A CONDIÇÃO DE COMPRA DO CABEÇALHO ESTÁ INCOMPATÍVEL COM A CONDIÇÃO DE COMPRA DA LISTA DE PREÇO DESTE FORNECEDOR !!!')
    }
/************************************************************************************************/
/*Verificação p/ saber se o Fornecedor no qual estou incluindo o Item é o Fornecedor Padrão, 
mas só irá fazer essa verificação no caso em que o PI for PRAC ...*/
    var is_fornecedor_default   = eval('<?=$is_fornecedor_default;?>')
    var is_prac                 = eval('<?=$is_prac;?>')
    if(is_fornecedor_default == 0) {//Se o Fornecedor não é Default ...
        if(is_prac == 1) {//Sempre que o PI for PIPA, pergunta se deseja mudar o Fornecedor Default ...
            resposta = confirm('ESSE FORNECEDOR NÃO É O DEFAULT !!!\nDESEJA ASSUMIR ESTE COMO SENDO O DEFAULT ?')
            if(resposta == true) document.form.hdd_fornecedor_default.value = 1
        }else {//Quando não for PIPA, muda o Fornecedor Default sem perguntar ...
            document.form.hdd_fornecedor_default.value = 1
        }
    }
/************************************************************************************************/
//Aqui desabilita esses campos para poder gravar no BD
    document.form.txt_preco_unitario.disabled   = false
    document.form.txt_ipi.disabled              = false
    document.form.hdd_ipi_incluso.disabled      = false
    limpeza_moeda('form', 'txt_preco_unitario, txt_qtde, ')
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    parent.fornecedor_produto.document.form.nao_atualizar.value = 1
/*Essa função está no outro frame, que é a parte superior desse Pop-up, isso porque nesse arquivo 
não existe Body*/
    parent.fornecedor_produto.atualizar_abaixo()
}

function preco_unitario() {
    if(document.form.chkt_debito.checked == true) {
        document.form.preco_escondido.value = '<?=$preco;?>'
        document.form.txt_preco_unitario.value = '0,00'
    }else {
        document.form.txt_preco_unitario.value = '<?=$preco;?>'
        document.form.preco_escondido.value = '0,00'
    }
    calcular()
}

function calcular() {
    var qtde    = eval(strtofloat(document.form.txt_qtde.value))
    var preco   = eval(strtofloat(document.form.txt_preco_unitario.value))
//Se a Qtde estiver preenchida, ....
    if(qtde != '') {
//Se não existir Preço
        if(document.form.txt_preco_unitario.value == '') {
            document.form.txt_valor_total.value = ''
//Se existir Preço, ....
        }else {
            document.form.txt_valor_total.value = preco * qtde
            if(document.form.txt_valor_total.value == 'NaN') {
                document.form.txt_valor_total.value = ''
            }else {
                document.form.txt_valor_total.value = 'R$ '+arred(document.form.txt_valor_total.value, 2, 1)
            }
        }
//Se a Qtde não estiver digitada
    }else {
        document.form.txt_valor_total.value = ''
    }
}

function habilitar_desconto_especial() {
    var cmb_produto_insumo = '<?=$cmb_produto_insumo;?>'
    if(cmb_produto_insumo != '') {
        if(document.form.chkt_desconto_especial.checked == true) {//Checado
            document.form.txt_desc_esp_ped.value        = '<?=$desconto_especial_porc;?>'//Exibe
            document.form.txt_preco_unitario.disabled   = true
            document.form.txt_preco_unitario.className  = 'textdisabled'
            //Com o Desconto Especial, não podemos dar % de Desconto manual, pois ja é concedido um pelo Cabeçalho do Pedido ...
            document.form.txt_desconto_perc.value       = ''
            document.form.txt_desconto_perc.disabled    = true
            document.form.txt_desconto_perc.className   = 'textdisabled'
        }else {//Não Checado
            document.form.txt_desc_esp_ped.value = ''//Não Exibe
            document.form.txt_preco_unitario.disabled   = false
            document.form.txt_preco_unitario.className  = 'caixadetexto'
            //Sem o Desconto Especial, podemos dar % de Desconto manual, pois o do Cabeçalho do Pedido esta em desativado ...
            document.form.txt_desconto_perc.disabled    = false
            document.form.txt_desconto_perc.className   = 'caixadetexto'
        }
        recalcular_preco_unitario()
        calcular()
    }
}

function recalcular_preco_unitario() {
    if(document.form.chkt_desconto_especial.checked == true) {//Hab. a Promoção
/*Aqui eu igualo o Preço ao seu valor original, que é o valor de Preço de Lista, pois o Desconto
só pode ser dado em cima do Preço de Lista do Produto, e não do valor digitado na hora pelo usuário*/
        document.form.txt_preco_unitario.value = '<?=$preco;?>'
        var preco_unitario = eval(strtofloat(document.form.txt_preco_unitario.value))
        var desconto_especial_porc = eval(strtofloat('<?=$desconto_especial_porc;?>'))
        novo_preco_unitario = preco_unitario * (1 - desconto_especial_porc / 100)
        document.form.txt_preco_unitario.value = novo_preco_unitario
        document.form.txt_preco_unitario.value = arred(document.form.txt_preco_unitario.value, 2, 1)
    }else {//Retirou a Promoção
        document.form.txt_preco_unitario.value = '<?=$preco;?>'
    }
}

/********************Controle de Ajuste********************/
//Significa que o usuário quer trabalhar somente com ajuste, 
function controlar_ajuste() {
    var hdd_ajuste = eval('<?=$hdd_ajuste;?>')
//Significa que já foi acetada a opção de Inclusão do Ajuste
    if(hdd_ajuste == 1) {
        if(!texto('form', 'txt_preco_unitario', '1', '1234567890,.-', 'PREÇO', '2')) {
                return false
        }
//Tratamento antes de gravar no BD
        limpeza_moeda('form', 'txt_preco_unitario, txt_qtde, ')
        document.form.submit()
//Ainda não foi acetada a opção do Ajuste
    }else {
        var mensagem = confirm('DESEJA ADICIONAR AJUSTE ?')
        if(mensagem == true) document.location = 'inferior.php?id_pedido=<?=$id_pedido;?>&hdd_ajuste=1'
    }
}
/**********************************************************/

function fechar_pop_up() {
    var resposta = confirm('DESEJA REALMENTE FECHAR ESTA JANELA ?')
    if(resposta == true) {
        window.parent.close()
/*Essa função está no outro frame, que é a parte superior desse Pop-up, isso porque nesse arquivo 
não existe Body*/
        parent.fornecedor_produto.atualizar_abaixo()
    }else {
        return false
    }
}

function desabilitar_combo(is_prac) {
    if(is_prac == 1) {
        alert('ESSE PRODUTO É DO TIPO PRAC !\nDEVIDO A ISSO, ESTE É DO TIPO ESTOCÁVEL !!!')
        document.form.chkt_estocar.checked = true
        return false
    }else {
//Quando o Produto for estocável, significa que este vai ser p/ a Própria Empresa
        if(document.form.chkt_estocar.checked == true && document.form.chkt_estocar.disabled == false) {
            document.form.cmb_fornecedor_terceiro.disabled  = true
            document.form.cmb_fornecedor_terceiro.value     = ''
//Aki nesse caso, significa que estamos comprando o Produto para outro Fornecedor
        }else {
            document.form.cmb_fornecedor_terceiro.disabled  = false
            document.form.cmb_fornecedor_terceiro.value     = ''
        }
    }
}

function lote_minimo_compra() {
//Só irá existir esse campo no caso em que PI tem relação com o PA e que este PA é o do Tipo ESP ...
    if(typeof(document.form.txt_lote_minimo_pa_rev) == 'object') {
        var lote_minimo_pa_rev = eval(strtofloat(document.form.txt_lote_minimo_pa_rev.value))
        var qtde = eval(strtofloat(document.form.txt_qtde.value))
/*Se a Qtde digitada for < do que a Quantidade do Lote Mínimo do PA, então significa que está quantidade
está em inadiplência, sendo assim eu marco a caixa na cor vermelha ...*/
        if(qtde < lote_minimo_pa_rev) {
            document.form.txt_qtde.style.background = 'red'
            document.form.txt_qtde.style.color = 'white'
/*Se a Qtde digitada for > do que a Quantidade do Lote Mínimo do PA, então significa que está quantidade
está em satisfatório, a caixa fica na cor branca normalmente ...*/
        }else {
            document.form.txt_qtde.style.background = 'white'
            document.form.txt_qtde.style.color = 'brown'
        }
    }
}

function calcular_desconto() {
    var preco   = eval('<?=$preco_aux;?>')
    if(document.form.txt_desconto_perc.value != 0) {//Existe uma % de Desconto digitada ...
        var desconto_perc                           = eval(strtofloat(document.form.txt_desconto_perc.value))
        document.form.txt_preco_unitario.value      = preco * (1 - desconto_perc / 100)
        //Travo a caixa de Preço Unitário se existir alguma % de Desconto digitada ...
        document.form.txt_preco_unitario.className  = 'textdisabled'
        document.form.txt_preco_unitario.disabled   = true
    }else {//Não existe % de Desconto digitada ...
        document.form.txt_preco_unitario.value  = preco
        //Destravo a caixa de Preço Unitário se não existir % de Desconto digitada ...
        document.form.txt_preco_unitario.className  = 'caixadetexto'
        document.form.txt_preco_unitario.disabled   = false
    }
    document.form.txt_preco_unitario.value  = arred(document.form.txt_preco_unitario.value, 2, 1)
}
</Script>
</head>
<form name='form' action='<?=$PHP_SELF.'?passo=1';?>' method='post'>
<!--Controles de Tela-->
<input type='hidden' name='hdd_ajuste' value='<?=$hdd_ajuste;?>'>
<input type='hidden' name='hdd_fornecedor_default'>
<input type='hidden' name='preco_escondido'>
<input type='hidden' name='cmb_produto_insumo' value='<?=$cmb_produto_insumo;?>'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
<!--*****************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormal'>
        <td colspan='2'>
            Desconto %:
        </td>
        <td>
            <input type='text' name='txt_desconto_perc' size='5' maxlength='4' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calcular_desconto();calcular()"  class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <?
            if(!empty($moeda)) {//Já foi escolhido um produto na combo do frame de cima
                $texto = '<div align="right">'.$moeda.'</div>';
            }else {//Ainda não foi escolhido nenhum produto
                $texto = '&nbsp;';
            }
        ?>
        <td>
            <b>Preço: </b>
        </td>
        <td align='right'>
            <?=$texto;?>
        </td>
        <td>
        <?
            //Somente na Primeira vez em q carregar a Tela ...
            if(empty($preco_recalculado_inicio)) {
                $disabled = 'disabled';
                $class = 'textdisabled';
            }else {//Demais vezes
                //Se for diferente de 0, sugere a opção selecionada
                if($desconto_especial_porc_aux != 0) {
                    $disabled = 'disabled';
                    $class = 'textdisabled';
                }else {
                    $disabled = '';
                    $class = 'caixadetexto';
                }
            }
        ?>
            <input type='text' name='txt_preco_unitario' value="<?=$preco_recalculado_inicio;?>" size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class="<?=$class;?>" <?=$disabled;?>>
        <?
            if(!empty($unidade)) echo '&nbsp;/&nbsp;'.$unidade;
        ?>
        &nbsp;&nbsp;&nbsp;
        <input type='checkbox' name='chkt_debito' value='1' title='Livre de Débito' id='livre_debito' onclick='preco_unitario()' class='checkbox'>
        <label for='livre_debito'>LD</label>
        &nbsp;&nbsp;
        <?
        //Se for diferente de 0, sugere a opção selecionada
            if($desconto_especial_porc_aux != 0) $checked = 'checked';
        ?>
            <input type='checkbox' name='chkt_desconto_especial' id='desconto_especial' value='1' title='Desconto Especial' onclick='habilitar_desconto_especial()' class='checkbox' <?=$checked;?>>
            <label for='desconto_especial'>Desconto Especial</label>
            <input type='text' name='txt_desc_esp_ped' value='<?=$desconto_especial_porc;?>' title='Desconto Especial p/ Pedido' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='5' maxlength='5' class='textdisabled' disabled>&nbsp;%&nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Prazo de Pagto:
        </td>
        <td>
            <input type='text' name='txt_prazo_entrega' value='<?=$prazo_pgto_ddl;?>' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Forma de Compra:
        </td>
        <td>
            <input type='text' name='txt_forma_compra' value='<?=$compra;?>' size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            IPI:
        </td>
        <td>
            <input type='text' name='txt_ipi' value='<?=$ipi;?>' size='12' maxlength='10' class='textdisabled' disabled>
            <?
                if($ipi_incluso == 'S') echo '<font color="red" title="IPI Incluso" style="cursor:help"><b>(Incl)</b></font>';
            ?>
            <input type='hidden' name='hdd_ipi_incluso' value='<?=$ipi_incluso;?>' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Quantidade:</b>
        </td>
        <td>
            <?
                //Quando o Material for retirado do nosso Estoque, esse campo só aceitará números negativos ...
                $operador = ($material_retirado_nosso_estoque == 'S') ? 2 : 1;
            ?>
            <input type='text' name='txt_qtde' size="12" maxlength="10" onkeyup="verifica(this, 'moeda_especial', '2', '<?=$operador;?>', event);lote_minimo_compra();calcular()" onblur='document.form.txt_marca.focus()' class='caixadetexto'>
        </td>
    </tr>
<?
//Aqui eu verifico se esse PI tem relação com o PA ...
	$sql = "SELECT id_produto_acabado, referencia 
                FROM `produtos_acabados` 
                WHERE `id_produto_insumo` = '$cmb_produto_insumo' LIMIT 1 ";
	$campos_pipa = bancos::sql($sql);
	if(count($campos_pipa) == 1) {
//Só irá exibir esse prazo somente p/ P.A(s) que são ESP ...
            if($campos_pipa[0]['referencia'] == 'ESP') {
                $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($campos_pipa[0]['id_produto_acabado'], '', 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
                $sql = "SELECT lote_minimo_pa_rev 
                        FROM `fornecedores_x_prod_insumos` 
                        WHERE `id_fornecedor` = '$id_fornecedor_setado' 
                        AND `id_produto_insumo` = '$cmb_produto_insumo' LIMIT 1 ";
                $campos_lote_minimo = bancos::sql($sql);
                $lote_minimo_pa_rev = $campos_lote_minimo[0]['lote_minimo_pa_rev'];
?>
	<tr class='linhanormal'>
		<td colspan="3">
			<font color="red">
				<b>Lote Mínimo p/ Compra: </b>
			</font>
			<input type='text' name="txt_lote_minimo_pa_rev" value="<?=$lote_minimo_pa_rev?>" class="caixadetexto2" disabled>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="3">
			<font color="red">
				<b>Último Prazo de Entrega sugerido pelo Depto. Técnico foi: </b>
			</font>
			<?
				$sql = "SELECT prazo_entrega_tecnico 
                                        FROM `orcamentos_vendas_itens` 
                                        WHERE `id_produto_acabado` = '".$campos_pipa[0]['id_produto_acabado']."' LIMIT 1 ";
				$campos_prazo_entrega   = bancos::sql($sql);
				$prazo_entrega_tecnico  = $campos_prazo_entrega[0]['prazo_entrega_tecnico'];
				if($prazo_entrega_tecnico == '0.0') {
                                    $prazo_entrega_apresentar = '<font color="red"><b>SEM PRAZO</b></font>';
//Aqui é o Prazo de Ent. da Empresa Divisão, e verifica qual é o certo para poder carregar na caixa de texto
/*Existe esse esquema de Int, porque o Campo -> 'prazo_entrega_tecnico' é do Tipo Float, foi feito
esse esquema para não dar problema na hora de Atualizar o Custo*/
				}else {
                                    $prazo_entrega_apresentar = (int)$prazo_entrega_tecnico.' dias';
				}
				echo $prazo_entrega_apresentar;
			?>
		</td>
	</tr>
<?
            }
	}
?>
	<tr class='linhanormal'>
            <td colspan='2'>
                Valor Total:
            </td>
            <td>
                <input type='text' name='txt_valor_total' size='20' maxlength='15' class='textdisabled' disabled>
                &nbsp;&nbsp;<?=$moeda;?>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td colspan='2'>
                Marca / Obs:
            </td>
            <td>
                <input type='text' name='txt_marca' size='52' maxlength='50' class='caixadetexto'>
            </td>
	</tr>
	<?
            if($estocagem == 'N') {//PI do Tipo "NÃO Estocável" ...
                $checked    = '';
                $disabled   = 'disabled';
            }else {//Se for Estocável
                $checked    = 'checked';
                $disabled   = '';
            }
	?>
    <tr class='linhanormal'>
        <td colspan='2'>
            Estocagem:
        </td>
        <td>
            <input type='checkbox' name='chkt_estocar' value='1' title='Estocar' id='label' onclick="desabilitar_combo('<?=$is_prac;?>')" class="checkbox" <?=$checked;?> <?=$disabled;?>>
            <label for='label'>
            <?
                if($estocagem == 'N' && !empty($cmb_produto_insumo)) {//PI do Tipo "NÃO Estocável" ...
                    echo 'ESSE PI É DO TIPO NÃO ESTOCÁVEL';
                }else {//Se for Estocável
                    echo 'ESTOCAR ESSE PRODUTO';
                }
            ?>
            </label>
            -&nbsp;<b>Enviar para:</b>
            <select name='cmb_fornecedor_terceiro' title='Selecione o Fornecedor Terceiro' class='combo' disabled>
            <?
                $sql = "SELECT id_fornecedor, razaosocial 
                        FROM `fornecedores` 
                        WHERE `material_ha_debitar` = '1' 
                        AND `ativo` = '1' ORDER BY razaosocial ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
<?
//Se foi escolhido um Produto pela combo ou então foi escolhida a opção de ajuste pelo botão
//... então lista os fornecedores que tem como marcação
	if(!empty($cmb_produto_insumo) || !empty($hdd_ajuste)) {
?>
	<tr class='linhanormal'>
            <td colspan='2'>
                Debitar do Fornecedor:
            </td>
            <td>
                <select name="cmb_fornecedor" title="Selecione o Fornecedor" class='combo'>
                <?
                    $sql = "SELECT id_fornecedor, razaosocial 
                            FROM `fornecedores` 
                            WHERE `material_ha_debitar` = 1 
                            AND `ativo` = '1' ORDER BY razaosocial ";
                    echo combos::combo($sql);
                ?>
                </select>
            </td>
	</tr>
<?
	}
?>
	<tr class='linhanormal'>
            <td colspan='2'>
                <b>Obs do Produto:</b>
            </td>
            <td>
                <textarea name='txt_observacao' title='Observação' cols='50' rows='3' class='textdisabled' disabled><?=$observacao;?></textarea>
            </td>
	</tr>
	<tr class='linhacabecalho' align='center'>
            <td colspan='3'>
<?
//Enquanto não for escolhida a opção de Ajuste, então ele exibe o botão normalmente
	if($hdd_ajuste != 1) {
?>
                <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick='return validar()' class='botao'>
<?
	}
?>
                <input type='button' name='cmd_ajuste' value='Adicionar Ajuste' title='Adicionar Ajuste' onclick='controlar_ajuste()' class='botao'>
                <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar_pop_up()' class='botao'>
            </td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>

<!--*************Equivalente ao onload do Body*************-->
<Script Language = 'Javascript'>
//Quando carregar a Tela
/*Tive que colocar aqui embaixo, pq não existe Body nesse arquivo e também porque quando esse
arquivo ler esse trecho de código, daí já vai ter carregado os objetos + acima*/
//Significa que o usuário quis trabalhar com ajuste, então desabilita a Caixa p/ passar o Preço
    var hdd_ajuste = eval('<?=$hdd_ajuste;?>')
    if(hdd_ajuste == 1) {
        document.form.txt_preco_unitario.disabled   = false
        document.form.txt_preco_unitario.className  = 'caixadetexto'
        document.form.txt_preco_unitario.focus()
    }
    habilitar_desconto_especial()
</Script>
<!--*******************************************************-->
<?
//Aqui é para mostrar a mensagem de Inclusão do PI
if(!empty($valor)) {
?>
    <Script Language = 'Javascript'>
        alert('<?=$mensagem[$valor];?>')
    </Script>
<?}?>