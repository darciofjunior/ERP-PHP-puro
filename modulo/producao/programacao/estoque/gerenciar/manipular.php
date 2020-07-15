<?
require('../../../../../lib/segurancas.php');
if(empty($pop_up))  require('../../../../../lib/menu/menu.php');//Essa tela as vezes é aberta como sendo Pop-UP ...
require('../../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../../lib/data.php');
require('../../../../../lib/estoque_acabado.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/vendas.php');//Essa biblioteca é utilizada dentro da Biblioteca de Custos ...
require('../../../../../modulo/classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) ATUALIZADO(S) COM SUCESSO.</font>";
$mensagem[2] = "<font class='atencao'>NÃO EXISTE(M) PEDIDO(S) COM ESSE ITEM EM ABERTO.</font>";

if($passo == 1 && $_SERVER['REQUEST_METHOD'] == 'POST') {
    //Depois que é feito a Manipulação do Produto Acabado no Estoque, eu tenho que atualizar esse Campo p/ 0
    $sql = "UPDATE `produtos_acabados` SET `status_material_novo` = '0' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    bancos::sql($sql);
//Aqui muda o status do estoque para 2, caso eu esteje fazendo o racionamento do Estoque
    if($chkt_racionar_estoque == 1) {//Deseja fazer racionamento
        $sql = "UPDATE `estoques_acabados` SET `racionado` = '1' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    }else {//Deseja tirar racionamento
        $sql = "UPDATE `estoques_acabados` SET `racionado` = '0' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    }
    bancos::sql($sql);
//Aqui altera as Qtde_Pendentes e Vales dos itens de Pedido
    $data_sys   = date('Y-m-d H:i:s');
    estoque_acabado::status_estoque($_POST[id_produto_acabado], 0);//Aqui eu desbloqueio o último PA utilizado pelo Estoquista ...

    foreach($_POST['chkt_pedido_venda_item'] as $i => $id_pedido_venda_item) {
        if(!empty($id_pedido_venda_item)) {//Preciso deste desvio pq tenho ckb nao liberado, ele é hidden nulo e precisa ser assim pq mostramos todos os pedidos e n~ podemos manipular o n~ liberados
            /*************************************************************************************************/
            /*Aki eu busco qual é o id_pedido_venda, porque nem sempre eu vou ter esse id na mão, eu também 
            posso ter acesso aki nessa tela pelo id_cliente, então eu já faço isso para garantir ...*/
            $sql = "SELECT `id_pedido_venda` 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
            $campos             = bancos::sql($sql);
            $id_pedido_venda    = $campos[0]['id_pedido_venda'];

            //Muda para uma situação faturável
            $sql = "UPDATE `pedidos_vendas` SET `condicao_faturamento` = '1' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
            bancos::sql($sql);
            
            $sql = "UPDATE `pedidos_vendas_itens` SET `status_estoque` = '1', `qtde_pendente` = '".$_POST['txt_qtde_pendente'][$i]."' WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
            bancos::sql($sql);
            
            estoque_acabado::atualiza_qtde_pendente($_POST['id_produto_acabado']);
            estoque_acabado::qtde_estoque($_POST['id_produto_acabado'], 1);//depois dos calculos preciso atualizar a tabela de estoque PA ñ tirar esta linha ...
            
            //Aqui guarda na tabela relacional para poder gerar um relatório de separação do Estoque ...
            if($_POST['txt_nova_separacao'][$i] != 0) {
                $sql = "INSERT INTO `pedidos_vendas_separacoes` (`id_pedido_venda_separacao`, `id_pedido_venda`, `id_produto_acabado`, `id_funcionario`, `qtde_separado`, `data_sys`) VALUES (NULL, '$id_pedido_venda', '$_POST[id_produto_acabado]', '$_SESSION[id_funcionario]', '".$_POST['txt_nova_separacao'][$i]."', '$data_sys') ";
                bancos::sql($sql);
            }
            /*************************************************************************************************/
        }
    }
    $valor = 1;
}

//Significa que veio da Parte de Pedidos
//Seleciona a qtde de itens que existe no pedido
if($tela == 1) {
    if(!empty($id_cliente)) {
//Auxiliar na Paginação ...
        $sql = "SELECT COUNT(pa.`id_produto_acabado`) AS qtde_itens 
                FROM `clientes` c 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` AND pv.`status` < '2' 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                WHERE c.`id_cliente` = '$id_cliente' 
                ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` "; //nao pode tirar o pvi.id_pedido_venda_item, pois da erro de indexação ...
        $campos     = bancos::sql($sql);
        $qtde_itens = $campos[0]['qtde_itens'];
//SQL normal da Tela ...
        $sql = "SELECT c.`id_cliente`, c.`razaosocial`, 
                (pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale`) AS separada, pvi.`qtde_faturada`, 
                pvi.`margem_lucro`, pvi.`margem_lucro_estimada`, ovi.`id_orcamento_venda`, 
                ovi.`id_produto_acabado_discriminacao`, ovi.`qtde`, ovi.`preco_liq_final`, pv.`id_pedido_venda`, 
                pv.`num_seu_pedido`, pv.`faturar_em`, pv.`condicao_faturamento`, pv.`id_empresa`, 
                pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pvi.`id_pedido_venda_item`, 
                pvi.`status_estoque`, pvi.`qtde_pendente`, pvi.`vale`, pa.`id_produto_acabado`, pa.`referencia`, 
                pa.`discriminacao`, pa.`operacao_custo`, pa.`peso_unitario`, pa.`pecas_por_jogo`, pa.`observacao` 
                FROM `clientes` c 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_cliente` = c.`id_cliente` AND pv.`status` < '2' 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                WHERE c.`id_cliente` = '$id_cliente' 
                ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` "; //nao pode tirar o pvi.id_pedido_venda_item, pois da erro de indexação
    }

    if(!empty($id_pedido_venda)) {
//Auxiliar na Paginação ...
        $sql = "SELECT COUNT(pa.`id_produto_acabado`) AS qtde_itens 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' 
                AND pvi.`status` < '2' 
                ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` ";
        $campos     = bancos::sql($sql);
        $qtde_itens = $campos[0]['qtde_itens'];
        
//SQL normal da Tela ...
        $sql = "SELECT c.`id_cliente`, c.`razaosocial`, 
                (pvi.`qtde` - pvi.`qtde_pendente` - pvi.`vale`) AS separada, pvi.`qtde_faturada`, 
                pvi.`margem_lucro`, pvi.`margem_lucro_estimada`, ovi.`id_orcamento_venda`, 
                ovi.`id_produto_acabado_discriminacao`, ovi.`qtde`, ovi.`preco_liq_final`, 
                pv.`id_pedido_venda`, pv.`num_seu_pedido`, pv.`faturar_em`, pv.`condicao_faturamento`, 
                pv.`id_empresa`, pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, 
                pvi.`id_pedido_venda_item`, pvi.`status_estoque`, pvi.`qtde_pendente`, pvi.`vale`, 
                pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, 
                pa.`peso_unitario`, pa.`pecas_por_jogo`, pa.`observacao` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' 
                AND pvi.`status` < '2' 
                ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` "; //nao pode tirar o pvi.id_pedido_venda_item, pois da erro de indexação
    }
    if(empty($posicao)) $posicao = 1;
    $campos = bancos::sql($sql, ($posicao - 1), $posicao);
}else if($tela == 2) {//Significa que veio da Parte de Produtos Acabados, seleciona dados do PA através do id_produto_acabado ...
    $sql = "SELECT `id_produto_acabado`, `operacao_custo`, `referencia`, `discriminacao`, `peso_unitario`, `pecas_por_jogo`, 
            `observacao` AS observacao_produto 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos = bancos::sql($sql);
}
$id_produto_acabado	= $campos[0]['id_produto_acabado'];
$operacao_custo		= $campos[0]['operacao_custo'];
$referencia             = $campos[0]['referencia'];
$discriminacao		= $campos[0]['discriminacao'];
$peso_unitario		= $campos[0]['peso_unitario'];
$pecas_por_jogo         = $campos[0]['pecas_por_jogo'];
$observacao_produto	= $campos[0]['observacao_produto'];
?>
<html>
<head>
<title>.:: Manipular Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<!--JS exclusivo para esta tela-->
<Script Language = 'JavaScript' Src = 'tabela_manipular_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar(posicao) {
<?
    //Aqui dispara esse loop para o vetor que será criado em JavaScript
    //Seleção de Todos os Pedidos Pendentes que contém o mesmo Item de Produto em aberto
    $sql = "SELECT c.`id_cliente`, IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, 
            pvi.`qtde` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pvi.`id_produto_acabado` = '$id_produto_acabado' 
            AND pvi.`status` < '2' 
            ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` "; //nao pode tirar o pvi.id_pedido_venda_item, pois da erro de indexação
    $campos_pedidos = bancos::sql($sql);
    $linhas_pedidos = count($campos_pedidos);
?>
    //Número de registros encontrados do loop
    var linhas_pedidos = eval('<?=$linhas_pedidos;?>')
    //Criação dos Vetores no tamanho de registros encontrados
    var vetor_clientes  = new Array(linhas_pedidos)
    var vetor_qtde      = new Array(linhas_pedidos)
<?
    for($i = 0; $i < $linhas_pedidos; $i++) {//Aqui é o disparo do loop para carregar no vetor
?>
        vetor_clientes['<?=$i;?>'] = '<?=$campos_pedidos[$i]["cliente"];?>'
        vetor_qtde['<?=$i;?>'] = '<?=number_format($campos_pedidos[$i]["qtde"], "0", ",", "");?>'
<?
    }
?>
    var elementos = document.form.elements
    
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_pedido_venda_item[]'].length)
    }

    for(i = 0; i < linhas; i++) {
        var qtde_pedida             = (document.getElementById('txt_qtde_pedida'+i).value != '') ? eval(document.getElementById('txt_qtde_pedida'+i).value) : 0
        var qtde_faturada           = (document.getElementById('txt_qtde_faturada'+i).value != '') ? eval(document.getElementById('txt_qtde_faturada'+i).value) : 0
        var qtde_separada           = (document.getElementById('txt_qtde_separada'+i).value != '') ? eval(document.getElementById('txt_qtde_separada'+i).value) : 0
        var vale                    = (document.getElementById('txt_total_vale'+i).value != '') ? eval(document.getElementById('txt_total_vale'+i).value) : 0
        var qtde_pendente           = (document.getElementById('txt_qtde_pendente'+i).value != '') ? eval(document.getElementById('txt_qtde_pendente'+i).value) : 0
        
        var comparacao              = qtde_separada + vale + qtde_pendente + qtde_faturada
        
        if(qtde_pedida != comparacao) {
            alert('MANIPULAÇÃO DE ESTOQUE INVÁLIDA PARA O CLIENTE '+vetor_clientes[i]+' ! QUANTIDADE PEDIDA DESSE CLIENTE = '+vetor_qtde[i]+' !')
            document.getElementById('txt_qtde_pendente'+i).focus()
            document.getElementById('txt_qtde_pendente'+i).select()
            return false
        }
    }
    
//Estoque Disponível não pode ser Negativo ...
    if(document.form.txt_estoque_disponivel.value < 0) {
        alert('ESTOQUE DISPONÍVEL INVÁLIDO !!!\n\nESTOQUE DISPONÍVEL ESTÁ NEGATIVO !')
        return false
    }
    
    var estoque_disponivel      = eval(strtofloat(document.form.txt_estoque_disponivel.value))
    var total_nova_separacao    = eval(strtofloat(document.form.txt_total_nova_separacao.value))
    
//Estoque Disponível nunca pode ser menor do que o "Total da Nova Separação" ...
    if(estoque_disponivel < total_nova_separacao) {
        alert('ESTOQUE DISPONÍVEL INVÁLIDO !!!\n\nESTOQUE DISPONÍVEL NÃO PODE SER MENOR DO QUE O TOTAL DA NOVA SEPARAÇÃO !')
        return false
    }
    
    for(i = 0; i < linhas; i++) {
        //Representa que este objeto é um Checkbox ...
        if(document.getElementById('chkt_pedido_venda_item'+i).type == 'checkbox') {
            //Estou desabilitando o campo Nova Separação p/ poder guardar o seu valor no banco
            if(document.getElementById('chkt_pedido_venda_item'+i).checked == true) {
                document.getElementById('txt_qtde_pendente'+i).disabled     = false
                document.getElementById('txt_nova_separacao'+i).disabled    = false
            }
        }
    }
//Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value = posicao
//Aqui é para não chamar a função de destravamento de tela
    document.form.destravar.value = 1
}

function destravar(tela) { //Só destrava a tela do estoque quando, pois já garanti a qtde do item
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.destravar.value == 0) {
        if(tela == 1) {
            //Tela = 1 -> Significa que veio de Pedidos, então precisa recarregar o frame de itens abaixo
            window.opener.parent.itens.document.form.submit()
        }
        nova_janela('destravar.php?id_produto_acabado=<?=$id_produto_acabado;?>&tela=<?=$tela;?>', 'DESTRAVAR', '', '', '', '', 1, 1, 'l', 'u')
    }
}

function recalcular(indice) {//Qtde Pendente Real já vem carregada do Banco de Dados
    var qtde_pedida             = eval(strtofloat(document.getElementById('txt_qtde_pedida'+indice).value))
    var qtde_faturada           = eval(strtofloat(document.getElementById('txt_qtde_faturada'+indice).value))
    var total_vale              = eval(strtofloat(document.getElementById('txt_total_vale'+indice).value))
    var qtde_separada           = (document.getElementById('txt_qtde_separada'+indice).value != '') ? document.getElementById('txt_qtde_separada'+indice).value : 0
    var qtde_separada_antiga    = eval(strtofloat(document.getElementById('txt_qtde_separada_antiga'+indice).value))
    
    var qtde_pendente           = qtde_pedida - qtde_faturada - total_vale - qtde_separada
    var qtde_separada_maxima    = qtde_pedida - qtde_faturada - total_vale//Esse é o Limite p/ Qtde Separada ...
    
    if(qtde_pendente < 0) {
        qtde_pendente = 0
        qtde_separada = qtde_separada_maxima
        
        //Em caso de divergência, reatribuo nessa Qtde Separada a qtde_separada_maxima ...
        document.getElementById('txt_qtde_separada'+indice).value   = qtde_separada
    }

    document.getElementById('txt_qtde_pendente'+indice).value   = qtde_pendente
    document.getElementById('txt_nova_separacao'+indice).value  = qtde_separada - qtde_separada_antiga
    
    /**************************************************************************/
    /*********************Lógica do Total da Nova Separação********************/
    /**************************************************************************/
    var elementos = document.form.elements
    
    if(typeof(elementos['chkt_pedido_venda_item[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_pedido_venda_item[]'].length)
    }
    
    var total_nova_separacao = 0
        
    for(i = 0; i < linhas; i++) {
        //Representa que este objeto é um Checkbox ...
        if(document.getElementById('chkt_pedido_venda_item'+i).type == 'checkbox') {
            //Estou desabilitando o campo Nova Separação p/ poder guardar o seu valor no banco
            if(document.getElementById('chkt_pedido_venda_item'+i).checked == true) {
                total_nova_separacao+= eval(document.getElementById('txt_nova_separacao'+i).value)
            }
        }
    }
    
    document.getElementById('txt_total_nova_separacao').value = total_nova_separacao
    /**************************************************************************/
}

function controle_racionar() {
    document.form.destravar.value = 1
/*Aki verifico se esse objeto existe, esse objeto só carrega na tela, caso o pa corrente
tenha algum pedido*/
    if(typeof(document.form.chkt) == 'object') {//Quer dizer q o objeto existe
        document.form.chkt.checked = false
        document.form.chkt.onclick()
    }
    document.form.submit()
}
</Script>
</head>
<body onunload="destravar('<?=$tela;?>')">
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar('<?=$posicao;?>')">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
/*****************************************************************************************/
/****************************************Segurança****************************************/
/*****************************************************************************************/
//Se alguém já estiver dentro do gerenciar com este PA então travo esta tela de Estoque p/ poder garantir a quantidade desse Item ...
    if(!estoque_acabado::status_estoque($id_produto_acabado, 1)) {
?>
    <tr align='center' class='erro'>
        <td colspan='4'>
            <b>EXISTE OUTRA PESSOA MANIPULANDO ESTE ITEM !</b>
        </td>
    </tr>
<?
        exit;
    }
/*****************************************************************************************/
?>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Detalhes do Item
        </td>
    </tr>
<?
    //Busca os dados do grupo_pa e da empresa divisão através do id_produto_acabado ...
    $sql = "SELECT ed.`id_empresa_divisao`, ed.`razaosocial`, gpa.`id_grupo_pa`, gpa.`nome` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos_pas = bancos::sql($sql);
    if(count($campos_pas) == 1) {
        $id_empresa_divisao = $campos_pas[0]['id_empresa_divisao'];
        $razaosocial        = $campos_pas[0]['razaosocial'];
        $id_grupo_pa        = $campos_pas[0]['id_grupo_pa'];
        $nome               = $campos_pas[0]['nome'];
    }else {
        $id_empresa_divisao = 0;
        $razaosocial        = '';
        $id_grupo_pa        = 0;
        $nome               = '';
    }
?>
    <tr class='linhanormal'>
        <td>
            <b>Grupo:</b>
        </td>
        <td>
            <?=$nome;?>
        </td>
        <td align='left'>
            <font title='Empresa Divisão' style='cursor:help'>
                <b>Divisão:</b>
            </font>
            <?=$razaosocial;?>
        </td>
        <td align='left'>
            <font title='Classificação Fiscal' style='cursor:help'>
                <b>C. F.:</b>
            </font>
            <?
                //Aqui já se aproveita o busca também o IPI da Class. Fiscal. q é utilizado + abaixo ...
                $sql = "SELECT cf.`classific_fiscal`, cf.`ipi` 
                        FROM `grupos_pas` gpa 
                        INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                        INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                        WHERE gpa.`id_grupo_pa` = '$id_grupo_pa' ";
                $campos_classific_fiscal = bancos::sql($sql);
                if(count($campos_classific_fiscal) == 1) {
                    if($operacao_custo == 1) {//Revenda
                        $ipi_classific_fiscal   = 'S / IPI'; //então é zero de IPI
                        $classific_fiscal       = $campos_classific_fiscal[0]['classific_fiscal'];
                    }else {
                        $classific_fiscal       = $campos_classific_fiscal[0]['classific_fiscal'];
                        $ipi_classific_fiscal   = number_format($campos_classific_fiscal[0]['ipi'], 1, ',', '.');
                    }
                }else {
                    $classific_fiscal       = '';
                    $ipi_classific_fiscal   = 0;
                }
                echo $classific_fiscal;
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto: </b>
        </td>
        <td colspan='3'>
            <a href="javascript:nova_janela('../../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'ESTOQUE', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
                <?=intermodular::pa_discriminacao($id_produto_acabado);?>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peças / Embalagem: </b>
            <?
    //Traz a quantidade de peças por embalagem da embalagem principal daquele produto
                $sql = "SELECT `pecas_por_emb` 
                        FROM `pas_vs_pis_embs` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `embalagem_default` = '1' LIMIT 1 ";
                $campos_pecas_por_emb = bancos::sql($sql);
                if(count($campos_pecas_por_emb) == 1) echo number_format($campos_pecas_por_emb[0]['pecas_por_emb'], 3, ',', '.');
            ?>
        </td>
        <td>
            <b>Peso / Pç(Kg): </b>
            <?=number_format($peso_unitario, 3, ',', '.');?>
        </td>
        <td colspan='2'>
            <?
                $sql = "SELECT `racionado` 
                        FROM `estoques_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                $campos_racionado   = bancos::sql($sql);
                $checked            = ($campos_racionado[0]['racionado'] == 1) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_racionar_estoque' id='id_racionar_estoque' value='1' title='Racionar Estoque' onclick='controle_racionar()' class='checkbox' <?=$checked;?>>
            <label for='id_racionar_estoque'>
                <font color='red'>
                    <b>Racionar Estoque</b>
                </font>
            </label>
            &nbsp;-&nbsp;
            <input type='button' name='cmd_baixas_manip' value='Baixas / Manipulações' title='Baixas / Manipulações' onclick="html5Lightbox.showLightbox(7, '../../../../vendas/estoque_acabado/manipular_estoque/consultar.php?passo=1&opt_opcao=1&txt_referencia=<?=$referencia;?>&pop_up=1')" style='color:brown; font-weight: bold' class='botao'>
            &nbsp;-&nbsp;
            <img src = '../../../../../imagem/certo.gif' title='Atualizar Material de Entrada' alt='Atualizar Material de Entrada' onclick="nova_janela('atualizar_material.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'ATUALIZAR_PRODUTO', '', '', '', '', 1, 1, 'l', 'u')" style='cursor:help' border='0'>
            Desmarcar PA com Nova Entrada Estoque
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação do Produto: </b>
        </td>
        <td colspan='3'>
            <font color="#0000FF">
                <?=$observacao_produto;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
        <?
            //Aqui eu gaganto a quantidade daquele item em Estoque ...
            $estoque = estoque_acabado::qtde_estoque($id_produto_acabado);
        ?>
        <b>Estoque Disponivel:</b>
        <input type='text' name='txt_estoque_disponivel' value='<?=number_format($estoque[3], 0, ',', '');?>' title='Estoque Disponível' maxlength='8' size='8' class='textdisabled' disabled>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <b>Estoque Real:</b>
        <input type='text' name='txt_estoque_real' value='<?=number_format($estoque[0], 0, ',', '');?>' title='Estoque Real' maxlength='8' size='8' class='textdisabled' disabled>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <b>Pendência Total:</b>
        <?
            $sql = "SELECT SUM(pvi.`qtde_pendente`) AS pendencia_total 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`liberado` = '1' 
                    WHERE pvi.`id_produto_acabado` = '$id_produto_acabado' 
                    AND pvi.`status` < '2' ";
            $campos_pendencia_total = bancos::sql($sql);
        ?>
        <input type='text' name='txt_pendencia_total' value='<?=number_format($campos_pendencia_total[0]['pendencia_total'], 0, ',', '');?>' title='Pendência Total' maxlength='8' size='8' class='textdisabled' disabled>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <b>Em Produção:</b>
        <?
            $producao           = $estoque[2];
            $compra             = estoque_acabado::compra_producao($id_produto_acabado);
            $compra_producao    = number_format($producao + $compra, 2, ',', '');
            ?>
            <input type='text' name='txt_em_producao' value='<?=$compra_producao;?>' title='Em Produção' maxlength='8' size='8' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <?
                $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($id_produto_acabado);
                
                $font_ed_atrelado       = ($retorno_pas_atrelados['total_ed_pas_atrelados'] < 0) ? 'red' : 'black';
                $font_er_atrelado       = ($retorno_pas_atrelados['total_er_pas_atrelados'] < 0) ? 'red' : 'black';
                $font_ec_atrelado       = ($retorno_pas_atrelados['total_ec_pas_atrelados'] < 0) ? 'red' : 'black';
            ?>
            <font title='Estoque Disponível Atrelado' style='cursor:help'>
                <b>MMV Atrel: </b>
                <?=number_format($retorno_pas_atrelados['total_mmv_pas_atrelados'], 0, '', '.');?>
            </font>
            &nbsp;-&nbsp;
            <font title='Estoque Disponível Atrelado' style='cursor:help'>
                <b>E.D. Atrel: </b>
                <font color='<?=$font_ed_atrelado;?>'>
                    <?=number_format($retorno_pas_atrelados['total_ed_pas_atrelados'] / $pecas_por_jogo, 0, '', '.');?>
                </font>
            </font>
            &nbsp;-&nbsp;
            <font title='Estoque Real Atrelado' style='cursor:help'>
                <b>E.R. Atrel: </b>
                <font color='<?=$font_er_atrelado;?>'>
                    <?=number_format($retorno_pas_atrelados['total_er_pas_atrelados'] / $pecas_por_jogo, 0, '', '.');?>
                </font>
            </font>
            &nbsp;-&nbsp;
            <font title='Estoque Comprometido Atrelado' style='cursor:help'>
                <b>E.C. Atrel: </b>
                <font color='<?=$font_ec_atrelado;?>'>
                    <?=number_format($retorno_pas_atrelados['total_ec_pas_atrelados'] / $pecas_por_jogo, 0, '', '.');?>
                </font>
            </font>
        </td>
    </tr>
</table>
<?
$data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
/***********************************************************************************************/
/*Seleção de Todos os Pedidos Pendentes que contém o mesmo Item de Produto em aberto e somente dos
itens que não estejam faturados totalmente <- 'pvi.status < 2'*/
$sql = "SELECT c.`id_cliente`, c.`id_uf`, IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, 
        c.`credito`, ovi.`id_orcamento_venda_item`, ovi.`id_orcamento_venda`, pa.`operacao_custo`, 
        pa.`id_produto_acabado`, pv.`id_empresa`, pv.`faturar_em`, pv.`condicao_faturamento`, pv.`data_emissao`, 
        pv.`liberado`, pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pv.`prazo_medio`, 
        pvi.`id_pedido_venda`, pvi.`id_oe`, pvi.`margem_lucro`, pvi.`margem_lucro_estimada`, 
        pvi.`id_pedido_venda_item`, pvi.`qtde`, pvi.`qtde_pendente`, pvi.`vale`, pvi.`qtde_faturada`, 
        pvi.`preco_liq_final` 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_orcamento_venda_item` = ovi.`id_orcamento_venda_item` AND pvi.`status` < '2' 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[2];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
        <?
            if($tela == 2) {//Significa que veio da Parte de Produtos Acabados ...
        ?>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar.php<?=$parametro;?>'" class='botao'>
        <?	
            }
            
            if($tela == 1) {//Significa que veio da Parte de Pedidos ...
        ?>
                <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
</table>
<input type='hidden' name='destravar'>
<input type='hidden' name='tela' value='<?=$tela;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<?
}else {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Pedido(s) Pendente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar_especial('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td rowspan='2'>
            N.º Ped / Emp / Pz Médio
        </td>
        <td rowspan='2'>
            Cliente
        </td>
        <td rowspan='2'>
            Faturar em
        </td>
        <td rowspan='2'>
            Crédito /<br/>Cond. Fat.
        </td>
        <td rowspan='2'>
            <font title='Preço Líquido Final R$' style='cursor:help'>
                P. L.<br>Final R$
            </font>
        </td>
        <td colspan='7'>
            Quantidade
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Quantidade Pedida' style='cursor:help'>
                Pedida
            </font>
        </td>
        <td>
            <font title='Quantidade Faturada' style='cursor:help'>
                Faturada
            </font>
        </td>
        <td>
            Separada
        </td>
        <td>
            Total de Vale
        </td>
        <td>
            <font title='Quantidade Pendente' style='cursor:help'>
                Pendente
            </font>
        </td>
        <td>
            <font title='Nova Separação' style='cursor:help'>
                Nova Sep.
            </font>
        </td>
    </tr>
<?
    $vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();

    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8')">
            <?
                //Verifico se o id_pedido_venda_item está em algum Packing List ...
                $sql = "SELECT `qtde` 
                        FROM `packings_lists_itens` 
                        WHERE `id_pedido_venda_item` = '".$campos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
                $campos_packing_list_item = bancos::sql($sql);
                if(count($campos_packing_list_item) == 0) {//Não está em Packing List, consequentemente eu mostro o Checkbox ...
            ?>
            <input type='checkbox' name='chkt_pedido_venda_item[]' id='chkt_pedido_venda_item<?=$i;?>' value='<?=$campos[$i]['id_pedido_venda_item'];?>' onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8')" class='checkbox'>
            <?
                }else {//Está em Packing List, consequentmente não mostro o Checkbox porque não posso manipular esse Item ...
            ?>
            <!--Apenas coloco esse objeto p/ não dar erro de índice e deixo esse sem value p/ que o PHP não se perca ...-->
            <input type='hidden' name='chkt_pedido_venda_item[]' id='chkt_pedido_venda_item<?=$i;?>'>
            <?
                    echo '<font color="red" title="Packing List" style="cursor:help"><b>(PL '.$campos_packing_list_item[0]['qtde'].')</b></font>';
                }
            ?>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                if($campos[$i]['liberado'] == 1) {//Se estiver liberado, mostra na cor normal
            ?>
                    <font color='blue' title='Pedido Liberado' style='cursor:help'>
                        <?=$campos[$i]['id_pedido_venda'];?>
                    </font>
            <?
                }else {//Mostra em Vermelho
            ?>
                    <font color='red' title='Pedido não Liberado' style='cursor:help'>
                        <?=$campos[$i]['id_pedido_venda'];?>
                    </font>
            <?
                }
                $dados_faturamento  = ' / <font title="'.genericas::nome_empresa($campos[$i]['id_empresa']).'" style="cursor:help">'.substr(genericas::nome_empresa($campos[$i]['id_empresa']), 0, 1).'</font> / ';
                
                if($campos[$i]['vencimento1'] == 0) {
                    $dados_vencimento = 'À vista';
                }else {
                    $dados_vencimento = $campos[$i]['vencimento1'];
                    if($campos[$i]['vencimento2'] > 0) $dados_vencimento.= ' / '.$campos[$i]['vencimento2'];
                    if($campos[$i]['vencimento3'] > 0) $dados_vencimento.= ' / '.$campos[$i]['vencimento3'];
                    if($campos[$i]['vencimento4'] > 0) $dados_vencimento.= ' / '.$campos[$i]['vencimento4'];
                }

                $dados_faturamento.= '<font title="Vencimentos: '.$dados_vencimento.'" style="cursor:help">'.$campos[$i]['prazo_medio'].'</font>';
                echo $dados_faturamento;
            ?>
            </font>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <a href="javascript:nova_janela('../../../../classes/pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$campos[$i]['id_cliente'];?>', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Relatório de Pendências' class='link'>
                    <?=$campos[$i]['cliente'];?>
                </a>
                <?
                    if($campos[$i]['liberado'] == 0) {//Se o Pedido não estiver liberado então mostra essa msn ao lado
                ?>
                <font color='red' title="Não Liberado" style='cursor:help'>
                    <b>(Ñ LIB)</b>
                </font>
                <?
                    }
                ?>
            </font>
            <?
                if($campos[$i]['id_oe']) echo '<font color="purple"><b>(OE)</b></font>';
            ?>
        </td>
        <td>
        <?
            if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                if($campos[$i]['faturar_em'] > $data_atual_mais_um) {
                    echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }else {
                    echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
            $condicao_faturamento = array_sistema::condicao_faturamento();
            echo $campos[$i]['credito'].' / '.$condicao_faturamento[$campos[$i]['condicao_faturamento']];
        ?>
        </td>
        <td align='right'>
        <?
            echo number_format($campos[$i]['preco_liq_final'], 2, ',', '.').'<br/>';

            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                $tx_financeira          = custos::calculo_taxa_financeira($campos[$i]['id_orcamento_venda']);
                $margem                 = custos::margem_lucro($campos[$i]['id_orcamento_venda_item'], $tx_financeira, $campos[$i]['id_uf'], $campos[$i]['preco_liq_final']);

                $cor_instantanea        = ($margem[0] < 0) ? 'red' : '#E8E8E8';//$margem[0] valor real da margem
                $cor_gravada            = ($campos[$i]['margem_lucro'] < 0) ? 'red': '#E8E8E8';
                $cor_estimada           = ($campos[$i]['margem_lucro_estimada'] < 0) ? 'red': '#E8E8E8';
        ?>
            <!--************************************************************************-->
            <!--A folha de estilo fica aqui dentro do Loop porque as cores de fonte 
            dos IDs se comportaram de acordo com o que foi definido acima pelo PHP ...-->
            <style type='text/css'>
                #id_ml_instantanea<?=$i;?>::-moz-selection {
                    background:#A9A9A9;
                    color:<?=$cor_instantanea;?>
                }
                #id_ml_gravada<?=$i;?>::-moz-selection {
                    background:#A9A9A9;
                    color:<?=$cor_gravada;?>
                }
                #id_ml_estimada<?=$i;?>::-moz-selection {
                    background:#A9A9A9;
                    color:<?=$cor_estimada;?>
                }
            </style>
            <!--************************************************************************-->

            <font color="<?=$cor_instantanea;?>" id='id_ml_instantanea<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'><br/>
                <?='ML='.$margem[1];//Valor Descritivo da Margem ...?>
            </font>
            <font color="<?=$cor_gravada;?>" id='id_ml_gravada<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'>
                <br/><?='MLG='.number_format($campos[$i]['margem_lucro'], 2, ',', '.');?>
            </font>
            <font color="<?=$cor_estimada;?>" id='id_ml_estimada<?=$i;?>' title='Margem de Lucro Estimada' style='cursor:help'>
                <br/><?='MLEst='.number_format($campos[$i]['margem_lucro_estimada'], 2, ',', '.');?>
            </font>
        <?
            }

            $fator_margem_lucro = genericas::variavel(22);
            $custo_bancario     = genericas::variavel(66);
            $fator_ml_min_crise = genericas::variavel(74);

            //Zero essas variáveis p/ não herdar Valores do Loop Anterior ...
            $custo_ml_zero      = 0;
            $custo_ml_zero_nac  = 0;
            $custo_ml_zero_inter= 0;

            /*Se os Pedidos tiverem sua Data de Emissão menor que esta Data 10/11/2015, sigo esse 
            caminho abaixo porque foi a partir dessa Data que aumentamos os preços dos Bits 
            TM e UL ...*/
            if($campos[$i]['data_emissao'] < '2015-11-10') {
                $valores_preco_venda_media = vendas::calculo_preco_venda_medio_nf_sp_30ddl_rs($campos[$i]['id_pedido_venda_item'], 'PVI');

                if($campos[$i]['operacao_custo'] == 0) {//Industrial, trago o Preço de Outra forma ...
                    /*Nesse caso chamei a função "todas_etapas" sem fazer a Soma da Etapa 1 porque ??? 

                    Se eu passo o parâmetro para somar a Etapa 1 que é a Embalagem nessa função, então 
                    o que acontece é que essa função não só apenas soma a Etapa1, mas além disso 
                    é adicionado o cálculo de Taxa de Estocagem que não nos interessa nesse momento 
                    porque são Produtos que a empresa não irá estocar e estamos pegando o Preço 
                    sem Custo Bancário também, utilizamos 30% porque consideramos uma Margem 
                    de Lucro Mínima mesmo nessa fase de Crise do País ...*/
                    $total_indust           = custos::todas_etapas($campos[$i]['id_produto_acabado'], $campos[$i]['operacao_custo'], 0);
                    $fator_custo_etapa_1_3  = genericas::variavel(12);
                    $etapa1                 = custos::etapa1($id_produto_acabado, $fator_custo_etapa_1_3);

                    $custo_ml_zero          = ($etapa1 + $total_indust) / $fator_margem_lucro;
                }else {//Revenda ...
                    $valores                = custos::preco_custo_pa($campos[$i]['id_produto_acabado'], '', 'S');

                    /*Nesse caso eu trago o "preco_venda_fat_nac_min_rs" do Custo que já está incluso o 
                    Custo bancário e com a fórmula abaixo, estou desembutindo esse Custo Bancário ...*/
                    if($valores['preco_venda_fat_nac_min_rs'] == 0) {
                        $custo_ml_zero = $valores['preco_venda_fat_inter_min_rs'] / (1 + $custo_bancario / 100) / $fator_margem_lucro;
                    }else {
                        if($valores['preco_venda_fat_inter_min_rs'] == 0) {
                            $custo_ml_zero = $valores['preco_venda_fat_nac_min_rs'] / (1 + $custo_bancario / 100) / $fator_margem_lucro;
                        }else {
                            /*Nesse caminho em específico o Fornecedor trabalha com 2 preços 
                            Nacional e Exportação, por isso dessa comparação com ambos (deve servir só 
                            pra Hispania ???) ...*/
                            $custo_ml_zero_nac    = $valores['preco_venda_fat_nac_min_rs'] / (1 + $custo_bancario / 100) / $fator_margem_lucro;
                            $custo_ml_zero_inter  = $valores['preco_venda_fat_inter_min_rs'] / (1 + $custo_bancario / 100) / $fator_margem_lucro;
                        }
                    }
                }

                if($custo_ml_zero_nac > 0 && $custo_ml_zero_inter > 0) {
                    $fator_ml_nac   = $valores_preco_venda_media['preco_NF_SP_30_ddl'] / $custo_ml_zero_nac;
                    $ml_nac         = ($fator_ml_nac - 1) * 100;

                    $fator_ml_inter = $valores_preco_venda_media['preco_NF_SP_30_ddl'] / $custo_ml_zero_inter;
                    $ml_inter       = ($fator_ml_inter - 1) * 100;

                    if($fator_ml_nac >= $fator_ml_min_crise) {
                        echo '<font color="darkgreen" title="ML= '.number_format($ml_nac, 1, ',', '.').' %" style="cursor:help"><b>OK NAC</b></font><br/>';
                    }else {
                        echo '<font color="darkred" title="ML= '.number_format($ml_nac, 1, ',', '.').' %" style="cursor:help"><b>Fora ML Crise NAC</b></font><br/>';
                    }
                    if($fator_ml_inter >= $fator_ml_min_crise) {
                        echo '<font color="darkgreen" title="ML= '.number_format($ml_inter, 1, ',', '.').' %" style="cursor:help"><b>OK EXP</b></font>';
                    }else {
                        echo '<font color="darkred" title="ML= '.number_format($ml_inter, 1, ',', '.').' %" style="cursor:help"><b>Fora ML Crise EXP</b></font>';
                    }
                }else {
                    $fator_ml   = $valores_preco_venda_media['preco_NF_SP_30_ddl'] / $custo_ml_zero;
                    $ml         = ($fator_ml - 1) * 100;

                    if($fator_ml >= $fator_ml_min_crise) {
                        echo '<font color="darkgreen" title="ML= '.number_format($ml, 1, ',', '.').' %" style="cursor:help"><b>OK</b></font><br/>';
                    }else {
                        echo '<font color="darkred" title="ML= '.number_format($ml, 1, ',', '.').' %" style="cursor:help"><b>Fora ML Crise</b></font>';
                    }
                }
            }else {//Maior ou igual à 10/11/2015 ...
                $ml_instantanea = str_replace(',', '.', $margem[1]);
                $ml_min_crise   = ($fator_ml_min_crise - 1) * 100;

                if($ml_instantanea >= $ml_min_crise) {
                    echo '<font color="darkgreen" title="DT.Emis= '.data::datetodata($campos[$i]['data_emissao'], '/').' - ML= '.number_format($ml_instantanea, 1, ',', '.').' %" style="cursor:help">
                            <img src = "../../../../../imagem/bloco_negro.gif" width="8" height="8" border="0">
                          <b>OK</b></font><br/>';
                }else {
                    echo '<font color="darkred" title="DT.Emis= '.data::datetodata($campos[$i]['data_emissao'], '/').' - ML= '.number_format($ml_instantanea, 1, ',', '.').' %" style="cursor:help">
                            <img src = "../../../../../imagem/bloco_negro.gif" width="8" height="8" border="0">
                          <b>Fora ML Crise</b></font><br/>';
                }
            }
        ?>
        </td>
        <td>
            <input type='text' name='txt_qtde_pedida[]' id='txt_qtde_pedida<?=$i;?>' value='<?=number_format($campos[$i]['qtde'], 0, ',', '');?>' title='Quantidade Pedida' maxlength='8' size='7' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_qtde_faturada[]' id='txt_qtde_faturada<?=$i;?>' value='<?=number_format($campos[$i]['qtde_faturada'], 0, ',', '');?>' title='Quantidade Pedida' maxlength='8' size='7' class='textdisabled' disabled>
        </td>
        <td>
        <?
            $qtde_separada = $campos[$i]['qtde'] - $campos[$i]['qtde_pendente'] - $campos[$i]['vale'] - $campos[$i]['qtde_faturada'];
        ?>
            <input type='text' name='txt_qtde_separada[]' id='txt_qtde_separada<?=$i;?>' value='<?=number_format($qtde_separada, 0, ',', '');?>' title='Quantidade Pedida' size='7' maxlength='8' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value != '') {this.value = Math.round(this.value)};recalcular('<?=$i;?>')" class='textdisabled' disabled>
            <input type='hidden' name='txt_qtde_separada_antiga[]' id='txt_qtde_separada_antiga<?=$i;?>' value='<?=number_format($qtde_separada, 0, ',', '');?>'>
        </td>
        <td>
            <input type='text' name='txt_total_vale[]' id='txt_total_vale<?=$i;?>' value='<?=number_format($campos[$i]['vale'], 0, ',', '');?>' title='Quantidade Pedida' size='7' maxlength='8' class='textdisabled' disabled>
        </td>
        <td>
        <?
            echo number_format($campos[$i]['qtde_pendente'], 0, ',', '');
        ?>
            <input type='text' name='txt_qtde_pendente[]' id='txt_qtde_pendente<?=$i;?>' value='<?=number_format($campos[$i]['qtde_pendente'], 0, ',', '');?>' title='Quantidade Pendente' maxlength='10' size='9' class='textdisabled' disabled>
            <input type='hidden' name='txt_qtde_pendente_antiga[]' id='txt_qtde_pendente_antiga<?=$i;?>' value='<?=number_format($campos[$i]['qtde_pendente'], 0, ',', '');?>'>
            <!--Aqui o Duplo clique é uma pura gambiarra (rsrsrs)-->
            <input type='hidden' name='txt_antigo_vale[]' id='txt_antigo_vale<?=$i;?>' ondblclick="recalcular('<?=$i;?>')">
        </td>
        <td>
            <input type='text' name='txt_nova_separacao[]' id='txt_nova_separacao<?=$i;?>' value='0' title='Nova Separação' size='7' maxlength='8' class='textdisabled' disabled>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
        <?
//Significa que veio da Parte de Produtos Acabados
            if($tela == 2) {
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar.php<?=$parametro;?>'" class='botao'>
        <?
            }
        ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');selecionar_especial('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
//Significa que veio da Parte de Pedidos
            if($tela == 1) {
        ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        <?
            }
        ?>
        </td>
        <td>
            <input type='text' name='txt_total_nova_separacao' id='txt_total_nova_separacao' value='0' size='7' maxlength='8' class='textdisabled' disabled>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='12'>
            &nbsp;
        </td>
    </tr>
    <tr align='center'>
        <td colspan='12'>
        <?
            ///////////////PAGINACAO CASO ESPECIFICA PARA ESTA TELA///////////////
            if($posicao > 1) echo "<b><a href='javascript:validar($posicao-1);document.form.submit()' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='javascript:validar($i);document.form.submit()' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='javascript:validar($posicao+1);document.form.submit()' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
            //////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
<input type='hidden' name='posicao' value='<?=$posicao;?>'>
<input type='hidden' name='destravar'>
<input type='hidden' name='tela' value='<?=$tela;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
</form>
</body>
</html>
<?}?>