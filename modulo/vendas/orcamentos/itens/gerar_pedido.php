<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');//Esse arquivo ñ pode ser retirado, pq a biblioteca Financeira utiliza uma função deste ...
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/genericas.php');//Esse arquivo ñ pode ser retirado, pq a biblioteca Vendas utiliza uma função deste ...
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php');

$mensagem[1] = "<font class='atencao'>CLIENTE COM CRÉDITO IRREGULAR ! NÃO É PERMITIDO A EMISSÃO DE PEDIDO PARA ESTE CLIENTE.</font>";
$mensagem[2] = "<font class='atencao'>NÃO FOI POSSÍVEL GERAR PEDIDO ! EXISTEM ITENS SEM CUSTO / BLOQUEADO / SEM PRAZO DE DEPTO. TÉCNICO.</font>";

/*******************************************************************************************************/
/*********************************************Controles*************************************************/
/*******************************************************************************************************/
/*1) Aki eu verifico quem é o Cliente deste Orçamento, p/ ver se estão preenc. corretamente os 
dados de Endereço ...*/
$sql = "SELECT `id_cliente`, `tipo_frete` 
        FROM `orcamentos_vendas` 
        WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
$campos_dados_gerais = bancos::sql($sql);
//Se o cadastro do Cliente estiver inválido, então este tem que ser corrigido, antes de qualquer outra coisa
$cadastro_cliente_incompleto = intermodular::cadastro_cliente_incompleto($campos_dados_gerais[0]['id_cliente']);
if($cadastro_cliente_incompleto == 1) {
?>
    <Script Language = 'JavaScript'>
        alert('CLIENTE COM CADASTRO IRREGULAR !!!\n\nNÃO É PERMITIDO A EMISSÃO DE PEDIDO P/ O MESMO !')
        parent.html5Lightbox.finish()
    </Script>
<?
    exit;
}

//2) Verifico se foi preenchido o Tipo de Frete ...
if($campos_dados_gerais[0]['tipo_frete'] == '') {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO FOI SELECIONADO UM TIPO DE FRETE !!!\n\nNÃO É PERMITIDO A EMISSÃO DE PEDIDO P/ O MESMO !')
        parent.html5Lightbox.finish()
    </Script>
<?
    exit;
}
/*******************************************************************************************************/

//Aqui eu verifico se a Situação Atual do Orçamento para saber se este está congelado ou não
if($passo == 1) {
    $sql = "SELECT ov.`id_transportadora`, ov.`id_cliente_contato`, ov.`id_cliente`, ov.`finalidade`, 
            ov.`nota_sgd`, ov.`data_emissao`, ov.`prazo_a`, ov.`prazo_b`, ov.`prazo_c`, ov.`prazo_d`, 
            ov.`prazo_medio`, ov.`valor_dolar`, ov.`congelar`, c.`id_cliente_tipo` 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
            WHERE ov.`id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
    $campos 		= bancos::sql($sql);
    $id_transportadora  = $campos[0]['id_transportadora'];
    $id_cliente_contato = $campos[0]['id_cliente_contato'];
    $id_cliente         = $campos[0]['id_cliente'];
    $finalidade         = $campos[0]['finalidade'];
    $nota_sgd 		= $campos[0]['nota_sgd'];
    $data_emissao       = $campos[0]['data_emissao'];
    $prazo_a 		= $campos[0]['prazo_a'];
    $prazo_b 		= $campos[0]['prazo_b'];
    $prazo_c 		= $campos[0]['prazo_c'];
    $prazo_d 		= $campos[0]['prazo_d'];
    $prazo_medio        = $campos[0]['prazo_medio'];
    $congelar 		= $campos[0]['congelar'];
    $valor_dolar        = $campos[0]['valor_dolar'];
    $id_cliente_tipo	= $campos[0]['id_cliente_tipo'];
    
    //Tem esse controle agora por causa dos vendedores que estavam com falcatrua no sistema
    /*Verificação se a Data de Emissão do Orc, é maior do que a Data Atual, caso sim
    então a Data de Emissão passa a assumir a Data Atual*/
    if($data_emissao > date('Y-m-d')) {
        $data_emissao = date('Y-m-d');
        $sql = "UPDATE `orcamentos_vendas` SET `data_emissao` = '$data_emissao' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
        bancos::sql($sql);
    }
    $credito = financeiros::controle_credito($id_cliente);
    
    //Verifico se o cliente está com crédito Irregular ... se estiver não podemos gerar Pedido p/ o mesmo ...
    if($credito == 'C' || $credito == 'D') {
        echo "
            <Script Language = 'JavaScript'>
                    alert('CLIENTE COM CRÉDITO {$credito} ! NÃO É PERMITIDO A EMISSÃO DE PEDIDO PARA ESTE CLIENTE !')
                    window.location = 'gerar_pedido.php?id_orcamento_venda={$_POST[id_orcamento_venda]}&valor=1'
            </Script>";
    }else {//Cliente está com crédito Ok
        if(!empty($_SESSION[id_funcionario])) {//99% dos casos, serão os funcionários da Albafer que irão acessar nosso sistema ...
            //Mudo os nomes das variáveis aqui p/ não conflitar com as que existem na Sessão e são esses mesmos nomes ...
            $id_funcionario_gravar  = $_SESSION[id_funcionario];
            $id_login_gravar        = 'NULL';
        }else {//No demais representantes ...
            $id_funcionario_gravar  = 'NULL';
            $id_login_gravar        = $_SESSION[id_login];
        }
        /*Significa que o Orçamento ainda não está congelado, então se faz uma verificação pra ver se é possível
        congelar este orçamento*/
        if($congelar == 'N') {
            $congelar_orcamento = 'S';//Sugestão Inicial do Sistema
            /*
            1) Verifico se existe o ORC possui algum Item ...
            2) Se o Orc Possui algum Item na Situação de ORÇAR ou DEP. TÉCNICO ...
            3) Com Custo Bloqueado ...
            4) Com algum Item 'ESP' e que esteje sem Pzo. Técnico ...*/
            $sql = "SELECT ovi.id_orcamento_venda_item 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
                    WHERE ovi.id_orcamento_venda = '$_POST[id_orcamento_venda]' 
                    AND (ovi.`preco_liq_fat_disc` <> '' OR pa.status_custo = '0' OR (pa.referencia = 'ESP' AND ovi.prazo_entrega_tecnico = '0.0')) LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) $congelar_orcamento = 'N';//Significa que não posso congelar ...
            if($congelar_orcamento == 'N') {//Se Não foi possível congelar o orçamento ...
                echo "
                    <Script Language = 'JavaScript'>
                        window.location = 'gerar_pedido.php?id_orcamento_venda={$id_orcamento_venda}&valor=2'
                    </Script>";
                exit;

            }else {//Aqui simplesmente congela o Orçamento caso foi possível o seu Congelamento
                //Aqui eu mudo a Situação do Cliente p/ Revenda Ativa, afinal de Contas ele está comprando ...
                if($id_cliente_tipo == 2 || $id_cliente_tipo == 10) {//Somente se Tipo de Cliente Rev. Inativa ou Não Compra ...
                    $sql = "UPDATE `clientes` SET `id_cliente_tipo` = '1' WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                    bancos::sql($sql);
                }
                $sql = "UPDATE `orcamentos_vendas` SET `congelar` = '$congelar_orcamento' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1";
                bancos::sql($sql);
                //Exclui direto todas mensagens ESP se o id_orcamento_venda_item estiver na Tab. Relacional mensagens_esps
                $sql = "DELETE me 
                        FROM `mensagens_esps` me 
                        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = me.`id_orcamento_venda_item` AND ovi.`id_orcamento_venda` = '$_POST[id_orcamento_venda]' ";
                bancos::sql($sql);
            }
        }
/*******************************************************/ 
        //Significa que o usuário optou em Reaproveitar um Pedido q estava aberto, isso só acontecerá p/ os casos de Exportação ...
        if($_POST['hdd_pedido_venda'] > 0) {
            $id_pedido_venda = $_POST['hdd_pedido_venda'];
        }else {
            //Verifico se existe pelo menos 1 Pedido Antigo em aberto, sem Itens e q não esteja liberado ...
            $sql = "SELECT pv.`id_pedido_venda` 
                    FROM `pedidos_vendas` pv 
                    WHERE pv.`liberado` = '0' 
                    AND pv.id_empresa = '$cmb_empresa' 
                    AND pv.`status` < '2' 
                    AND pv.`id_pedido_venda` NOT IN 
                    (SELECT DISTINCT(pv.`id_pedido_venda`) 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`liberado` = '0' 
                    AND pv.`id_empresa` = '$cmb_empresa' 
                    AND pv.`status` < '2' 
                    AND pv.`id_cliente` = '$id_cliente') 
                    AND pv.`id_cliente` = '$id_cliente' ORDER BY pv.`id_pedido_venda` LIMIT 1 ";
            $campos_pedidos  = bancos::sql($sql);
            $id_pedido_venda = $campos_pedidos[0]['id_pedido_venda'];
        }
/******************Variáveis Genéricas******************/
        $data_sys       = date('Y-m-d H:i:s');
        //A data de Faturar em do Pedido, também passa a assumir a Data Atual do Sistema
        $faturar_em     = date('Y-m-d');
        //A data de Emissão passa a assumir a Data Atual do Sistema e não a data de Emissão do Orçamento
        $data_emissao   = date('Y-m-d');
        
        //Aqui chama a função para verificar se o cliente já possui as transportadoras padrões cadastradas
        vendas::transportadoras_padroes($id_cliente);
        
        /*Se o usuário ainda não selecionou nenhuma Transportadora aqui no Orçamento, então o sistema sugere 
        a inserção dessa como sendo N/Carro (Baldez) -> id_transportadora = 795*/
        if(empty($id_transportadora)) $id_transportadora = 795;
        
        //Significa que existem pedidos em brancos, sendo assim eu vou reaproveitar esse pedido
        if(!empty($id_pedido_venda)) {//Atualiza o Cabeçalho para com os dados atuais do Orçamento ...
            //Coloquei esse nome na variável, mais isso é entre 'aspas', porque só estou reaproveitando o pedido
            $id_pedido_venda_novo = $id_pedido_venda;
            //Aqui sugere a inserção da Transportadora como sendo N/Carro (Baldez) -> id_transportadora = 795
            $sql = "UPDATE `pedidos_vendas` SET `id_funcionario` = $id_funcionario_gravar, `id_login` = $id_login_gravar, `id_transportadora` = '$id_transportadora', `id_cliente` = '$id_cliente', `id_cliente_contato` = '$id_cliente_contato', `id_empresa` = '$cmb_empresa', `finalidade` = '$finalidade', `faturar_em` = '$faturar_em', `data_emissao` = '$data_emissao', `fecha` = '$data_emissao', `vencimento1` = '$prazo_a', `vencimento2` = '$prazo_b', `vencimento3` = '$prazo_c', `vencimento4` = '$prazo_d', `prazo_medio` = '$prazo_medio', `valor_dolar` = '$valor_dolar', `data_sys` = '$data_sys' WHERE `id_pedido_venda` =  '$id_pedido_venda_novo' LIMIT 1 ";
            bancos::sql($sql);
        }else {//Aqui é a inserção dos dados de cabeçalho no novo pedido ...
            $sql = "INSERT INTO `pedidos_vendas` (`id_pedido_venda`, `id_funcionario`, `id_login`, `id_transportadora`, `id_cliente`, `id_cliente_contato`, `id_empresa`, `finalidade`, `faturar_em`, `data_emissao`, `fecha`, `vencimento1`, `vencimento2`, `vencimento3`, `vencimento4`, `prazo_medio`, `valor_dolar`, `data_sys`) VALUES (NULL, $id_funcionario_gravar, $id_login_gravar, '$id_transportadora', '$id_cliente', '$id_cliente_contato', '$cmb_empresa', '$finalidade', '$faturar_em', '$data_emissao', '$data_emissao', '$prazo_a', '$prazo_b', '$prazo_c', '$prazo_d', '$prazo_medio', '$valor_dolar', '$data_sys') ";
            bancos::sql($sql);
            $id_pedido_venda_novo = bancos::id_registro();
        }

        //Aqui é a inserção dos itens do Orçamento antigo para o Pedido que acabou de ser gerado
        for($i = 0; $i < count($_POST['chkt_orcamento_venda_item']); $i++) {
            /*Busco o id_produto_acabado para fazer controles de estoque, e o status do item para saber a situação
            atual deste item com relação a sua pendência em pedido: total, parcial, nenhuma*/
            $sql = "SELECT `id_produto_acabado`, `id_representante`, `comissao_new`, `comissao_extra`, 
                    `preco_liq_final`, `prazo_entrega`, `margem_lucro`, `margem_lucro_estimada`, `status` 
                    FROM `orcamentos_vendas_itens` 
                    WHERE `id_orcamento_venda_item` = '".$_POST['chkt_orcamento_venda_item'][$i]."' LIMIT 1 ";
            $campos_pa              = bancos::sql($sql);
            $id_produto_acabado     = $campos_pa[0]['id_produto_acabado'];
            $id_representante       = $campos_pa[0]['id_representante'];
            $comissao_new           = $campos_pa[0]['comissao_new'];
            $comissao_extra         = $campos_pa[0]['comissao_extra'];
            $preco_liq_final        = $campos_pa[0]['preco_liq_final'];
            $prazo_entrega          = $campos_pa[0]['prazo_entrega'];
            $margem_lucro           = $campos_pa[0]['margem_lucro'];
            $margem_lucro_estimada  = $campos_pa[0]['margem_lucro_estimada'];
            $status                 = $campos_pa[0]['status'];
            $retorno                = estoque_acabado::qtde_estoque($campos_pa[0]['id_produto_acabado']);//busco a qtde do estoque do PA corrente
            $status_estoque         = $retorno[1]; //status do estoque para saber se ele está bloqueado
            $qtde_estoque           = $retorno[3];//quantidade disponível do estoque
            $racionado              = $retorno[5]; //status do estoque para saber se ele está racionado
            if($status_estoque == 1 || $racionado == 1) { //então tá bloqueado ou racioado
                $qtde_pendente = $_POST['txt_quantidade'][$i];
            }else {
                /*Mudamos isso no dia 14/11/2016 porque não queremos mais separação automática na hora de 
                gerar Pedido, devido muitos erros de Estoque com a Entrada dos Machos ...

                $qtde_pendente = $_POST['txt_quantidade'][$i] - $qtde_estoque;*/
                
                $qtde_pendente = $_POST['txt_quantidade'][$i];
                
                //Preciso deste macete para quando eu incluir uma qtde de item menos q a est. disponivel p/ não dar erro
                if($qtde_pendente < 0) $qtde_pendente = 0;
            }
            //Caso 0 - Significa que este item ainda não foi importado para pedido, sendo assim posso mandar bala
            /*Caso 1 - Significa que este item já tem uma parte importada em pedido, então tenho q verificar o
            que eu ainda tenho disponível para poder importar o resto*/
            if($status == 0 || $status == 1) {
                $sql = "INSERT INTO `pedidos_vendas_itens` (`id_pedido_venda_item`, `id_pedido_venda`, `id_orcamento_venda_item`, `id_produto_acabado`, `id_representante`, `id_funcionario`, `qtde`, `qtde_pendente`, `comissao_new`, `comissao_extra`, `preco_liq_final`, `prazo_entrega`, `margem_lucro`, `margem_lucro_estimada`, `data_sys`) VALUES (NULL, '$id_pedido_venda_novo', '".$_POST['chkt_orcamento_venda_item'][$i]."', '$id_produto_acabado', '$id_representante', $id_funcionario_gravar, '".$_POST['txt_quantidade'][$i]."', '$qtde_pendente', '$comissao_new', '$comissao_extra', '$preco_liq_final', '$prazo_entrega', '$margem_lucro', '$margem_lucro_estimada', '$data_sys') ";
                bancos::sql($sql);
                $id_pedido_venda_item = bancos::id_registro();
                estoque_acabado::controle_pedidos_vendas_itens($id_pedido_venda_item, 1);// é só para controle de importacao dos itens do orçamentos e tb chama a função que atualiza o Estoque PA
            }
        }

        //Aqui eu busco todos os Follow-Ups registrados do Orçamento passado por parâmetro ...
        $sql = "SELECT * 
                FROM `follow_ups` 
                WHERE `identificacao` = '$_POST[id_orcamento_venda]' 
                AND `origem` = '1' ";
        $campos_follow_ups = bancos::sql($sql);
        $linhas_follow_ups = count($campos_follow_ups);
        for($i = 0; $i < $linhas_follow_ups; $i++) {
            /*Antes de registrar esse Follow-UP do Orçamento no Pedido, verifico se o mesmo já não foi 
            registrado anteriormente, afim de evitar um Registro em Duplicidade ...*/
            $sql = "SELECT `id_follow_up` 
                    FROM `follow_ups` 
                    WHERE `identificacao` = '$id_pedido_venda_novo' 
                    AND `origem` = '2' 
                    AND `observacao` = '".$campos_follow_ups[$i]['observacao']."' LIMIT 1 ";
            $campos_follow_up = bancos::sql($sql);
            /*Esse Follow-UP de Orçamento ainda não foi registrado nesse Pedido, sendo assim posso estar 
            fazendo um INSERT normalmente ...*/
            if(count($campos_follow_up) == 0) {
                $id_cliente_contato = (!empty($campos_follow_ups[$i]['id_cliente_contato'])) ?  $campos_follow_ups[$i]['id_cliente_contato'] : 'NULL';
                $id_representante   = (!empty($campos_follow_ups[$i]['id_representante'])) ?  $campos_follow_ups[$i]['id_representante'] : 'NULL';

                /*Registro no Pedido que acabou de ser gerado ou reaproveitado todos os Follow-Ups do Orçamento 
                que foram encontrados acima ...*/
                $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `exibir_no_pdf`, `data_sys`) VALUES (NULL, '".$campos_follow_ups[$i]['id_cliente']."', $id_cliente_contato, $id_representante, '$_SESSION[id_funcionario]', '$id_pedido_venda_novo', '2', '".$campos_follow_ups[$i]['observacao']."', '".$campos_follow_ups[$i]['exibir_no_pdf']."', '".date('Y-m-d H:i:s')."') ";
                bancos::sql($sql);
            }
        }
?>
        <Script Language = 'JavaScript'>
<?
//Significa que o pedido foi reaproveitado, então exibe outra mensagem
            if(!empty($id_pedido_venda)) {
?>
                alert('O PEDIDO N.º '+<?=$id_pedido_venda_novo;?>+' FOI ATUALIZADO COM SUCESSO !\nOBSERVAÇÃO: ESTE PEDIDO ESTAVA EM BRANCO OU FOI REAPROVEITADO !')
<?
//Aki exibe a mensagem normal, pois foi criado um novo pedido mesmo
            }else {
?>
                alert('NOVO PEDIDO N.º '+<?=$id_pedido_venda_novo;?>+' GERADO COM SUCESSO !')
<?
            }
?>
            parent.location = '../../pedidos/itens/index.php?id_pedido_venda=<?=$id_pedido_venda_novo;?>&clique_automatico_cabecalho=1'
        </Script>
<?
    }//IF credito
}else {
//Verifico qual é o Tipo de Orçamento, se é com Nota ou S/ Nota
    $sql = "SELECT ov.`nota_sgd`, c.`id_cliente`, c.`id_pais`, c.`id_cliente_tipo`, c.`tipo_faturamento` 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c on c.`id_cliente` = ov.`id_cliente` 
            WHERE ov.`id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $nota_sgd           = $campos[0]['nota_sgd'];
    $id_cliente         = $campos[0]['id_cliente'];
//Verifica se o Cliente é do Tipo Internacional ou Nacional ...
    $tipo_moeda         = ($campos[0]['id_pais'] != 31) ? 'U$' : 'R$';
    $id_cliente_tipo    = $campos[0]['id_cliente_tipo'];
	
    if($nota_sgd == 'S') {//Se for SGD - Grupo, traz Orçamentos do Tipo SGD ...
        $cmb_empresa    = 4;
        $condicao       = 'ed.id_empresa IN (1, 2) ';
        $class 		= 'combo';
        $disabled 	= '';
    }else {//Se for NF - Alba e Tool, traz Orçamentos do Tipo NF ...
        if($campos[0]['tipo_faturamento'] == 1 || $campos[0]['tipo_faturamento'] == 2) {//Significa que o Cliente fatura tudo pela Albafér ou Tool Master ...
            $cmb_empresa    = ($campos[0]['tipo_faturamento'] == 1) ? 1 : 2;//A combo sugere como Default a Empresa Albafer que vem Travada ...
            $texto          = ($campos[0]['tipo_faturamento'] == 1) ? 'TUDO PELA ALBAFER' : 'TUDO PELA TOOL MASTER';//A combo sugere como Default a Empresa que vem Travada ...
            $condicao       = 'ed.id_empresa in (1, 2) ';
            $class          = 'textdisabled';
            $disabled       = 'disabled';
        }else if($campos[0]['tipo_faturamento'] == 'Q') {//Significa que o Cliente fatura por Ambas Empresas - Indiferente ...
            $condicao       = 'ed.id_empresa in (1, 2) ';
            $texto          = 'QUALQUER EMPRESA';
            $class          = 'combo';
            $disabled       = '';
        }else if($campos[0]['tipo_faturamento'] == 'S') {//Significa que o Cliente fatura por Ambas Empresas - apenas itens da empresa escolhida ...
            $condicao       = ($cmb_empresa == 1) ? "ed.id_empresa = '1' " : "ed.id_empresa = '2' ";
            $texto          = 'SEPARADAMENTE';
            $class          = 'combo';
            $disabled       = '';
        }
    }
    /***************************Esse Controle será utilizado + abaixo em JavaScript***************************/
    if($campos[0]['id_pais'] != 31 && $cmb_empresa <= 2) {//Somente p/ Clientes Estrangeiros e Pedidos q sejam feitos c/ NF ...
        //Verifico se existe pelo menos 1 pedido que ainda esteja em aberto p/ o Cliente Estrangeiro ...
        $sql = "SELECT `id_pedido_venda` 
                FROM `pedidos_vendas` 
                WHERE `id_cliente` = '$id_cliente' 
                AND `id_empresa` = '$cmb_empresa' 
                AND `status` < '2' ORDER BY id_pedido_venda LIMIT 1 ";
        $campos_pedidos_estrangeiros = bancos::sql($sql);
        $linhas_pedidos_estrangeiros = count($campos_pedidos_estrangeiros);
        //Encontrou-se pelo menos 1 Pedido, então o sistema  p/ o usuário se ele deseja utilizar o mesmo ...
    }else {
        $linhas_pedidos_estrangeiros = 0;//P/ Clientes Nacionais daqui do Brasil, não é necessário fazermos esse Controle ...
    }
    /*********************************************************************************************************/
?>
<html>
<head>
<title>.:: Gerar Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/pecas_por_embalagem.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = 'incluir_itens_orcamento.js'></Script>
<Script Language = 'JavaScript'>
//Esse parâmetro é para controlar quantos itens que eu já tenho nesse Pedido
function validar(total_itens_pedidos) {
    var elementos                           = document.form.elements
    var chamar_funcao_pecas_por_embalagem   = 'S'//Foi criada uma variável desse Tipo nessa rotina, pelo fato de estar em Loop ...
    var cont_checkbox_selecionados          = 0, total_linhas = 0
    var id_funcionario                      = eval('<?=$_SESSION['id_funcionario'];?>')
    var id_cliente_tipo                     = eval('<?=$id_cliente_tipo;?>')
    
    //Esses são os únicos funcionários que podem mudar o Desconto acumulado do Item: Roberto 62, Wilson 68, Dárcio 98, Nishimura 136 ...
    var vetor_funcionarios_podem_mudar_desconto_extra = [62, 68, 98, 136]
    
//Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE A EMPRESA !')) {
        return false
    }
//Verificação de Itens Selecionados
    for(var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if(elementos[i].name == 'chkt_orcamento_venda_item[]') {//Só vasculho os checkbox de Orcs ...
                if(elementos[i].checked) cont_checkbox_selecionados++
                total_linhas++
            }
        }
    }
    if(cont_checkbox_selecionados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        total_itens_pedidos = eval(total_itens_pedidos) + total_linhas
    }
//Aki verifica se passou da qtde limite de itens para o Pedido
    if(total_itens_pedidos > 200) {
        alert('EXCEDIDO A QUANTIDADE DE ITEM(NS) PARA ESSE PEDIDO N.º <?=$id_pedido_venda;?> !\nDESMARQUE ALGUM(NS) ITEM(NS) SELECIONADO(S) !\n\nOBS: A QTDE MÁXIMA PERMITIDA POR PEDIDO É DE NO MÁXIMO 200 ITEM(NS) !')
        return false
    //Ainda não ultrapassou a margem de itens permitidos, então pode continuar incluindo itens
    }else {
        var itens_com_nadiplencia = 0
        
        for(var i = 0; i < total_linhas; i++) {
//Força o Preenchimento do Campo Quantidade ...
            if(document.getElementById('chkt_orcamento_venda_item'+i).checked == true) {
                if(document.getElementById('txt_quantidade'+i).value == '') {
                    alert('DIGITE A QUANTIDADE !')
                    document.getElementById('txt_quantidade'+i).focus()
                    return false
                }
                if(document.getElementById('txt_quantidade'+i).value == 0) {
                    alert('QUANTIDADE INVÁLIDA !')
                    document.getElementById('txt_quantidade'+i).focus()
                    document.getElementById('txt_quantidade'+i).select()
                    return false
                }
            }
            //Verifica se o valor digitado no Pedido é maior do que o valor do Orçamento
            if(eval(strtofloat(document.getElementById('txt_quantidade'+i).value)) > eval(strtofloat(document.getElementById('txt_qtde_real'+i).value))) {
                alert('QUANTIDADE PEDIDA INVÁLIDA !\nQUANTIDADE PEDIDA MAIOR DO QUE A QUANTIDADE SOLICITADA EM ORÇAMENTO !')
                document.getElementById('txt_quantidade'+i).focus()
                document.getElementById('txt_quantidade'+i).select()
                return false
            }
            //Aqui nessa parte do Script compara a Qtde Pçs / Emb para os produtos normais de linha e selecionados ...
            if(document.getElementById('chkt_orcamento_venda_item'+i).checked == true && document.getElementById('hdd_referencia'+i).value != 'ESP') {
                if(chamar_funcao_pecas_por_embalagem == 'S') {
                    /***********************************Controle de Peças por Embalagem***********************************/
                    //Todo o controle é feito dentro da Função de Peças por Embalagem ...
                    var resultado = pecas_por_embalagem(document.getElementById('hdd_referencia'+i).value, document.getElementById('hdd_discriminacao'+i).value, document.getElementById('hdd_familia'+i).value, document.getElementById('txt_quantidade'+i).value, document.getElementById('hdd_pecas_emb'+i).value)
                    if(resultado == 1) {//Usuário clicou em Cancelar ...
                        document.getElementById('txt_quantidade'+i).focus()
                        document.getElementById('txt_quantidade'+i).select()
                        return false
                    }else if(resultado == 0 || resultado == 2) {//"Alert" 0, "Confirm" 2 botão OK ...
                        /***********************************Controle por Funcionários***********************************/
                        var id_funcionario_logado = String('<?=$_SESSION['id_funcionario'];?>')
                        
                        for(var j = 0; j < vetor_funcionarios_ignorar_pecas_por_embalagem.length; j++) {
                            /*Verifico se o Funcionário que está logado pode colocar qualquer valor no que se refere à "Peças por Embalagem" ...
                            Essa variável "vetor_funcionarios_ignorar_pecas_por_embalagem" está dentro da biblioteca pecas_por_embalagem.js ...*/
                            var indice = id_funcionario_logado.indexOf(vetor_funcionarios_ignorar_pecas_por_embalagem[j])
                            if(indice == 0) {//Significa que esse Funcionário pode fazer o que bem entender ...
                                var pergunta = confirm('PODE SER QUE EXISTA(M) MAIS ITEM(NS) COM QTDE(S) NÃO COMPATÍVEL(IS) COM A(S) QTDE DE PÇS / EMBALAGEM !!!\n\nDESEJA MANTER ESTA(S) QUANTIDADE(S) P/ TODO(S) O(S) ITEM(NS) ?')
                                if(pergunta == true) {//Usuário clicou em OK, sai Loop Principal ...
                                    //P/ não chamar mais está função porque o próprio funcionário ignorou e não perder a validação no restante do Script ...
                                    chamar_funcao_pecas_por_embalagem = 'N'
                                }else {//Usuário clicou em Cancelar, sistema barra ...
                                    document.getElementById('txt_quantidade'+i).focus()
                                    document.getElementById('txt_quantidade'+i).select()
                                    return false
                                }
                                break//P/ sair do Loop ...
                            }
                        }
                    }
                }
                /*****************************************************************************************************/
            }
            /**************************Controle p/ PA(s) ESP**************************/
            if(document.getElementById('chkt_orcamento_venda_item'+i).checked == true && document.getElementById('hdd_referencia'+i).value == 'ESP') {
                /*Se a Qtde digitada pelo usuário p/ geração de Pedido for menor do que 
                a Qtde Real orçada, o Sistema dá uma mensagem de nadiplência ...*/
                if(eval(document.getElementById('txt_quantidade'+i).value) < eval(document.getElementById('txt_qtde_real'+i).value)) itens_com_nadiplencia = 1
            }
            /**************************Controle com os Preços*************************/
            if(document.getElementById('chkt_orcamento_venda_item'+i).checked == true && document.getElementById('hdd_preco_liq_final'+i).value == '0,00') {
                alert('PREÇO LÍQ FINAL INVÁLIDO !!!\n\nVALOR IGUAL A ZERO !')
                document.getElementById('txt_quantidade'+i).focus()
                document.getElementById('txt_quantidade'+i).select()
                return false
            }
            /**************************Controle com os Preços*************************/
            var desc_cliente    = eval(document.getElementById('hdd_desc_cliente'+i).value)
            var desc_extra      = eval(document.getElementById('hdd_desc_extra'+i).value)
            var acrescimo_extra = eval(document.getElementById('hdd_acrescimo_extra'+i).value)
            
            /*Se o Cliente é Indústria existe uma restrição ao qual o Desconto acumulado do item não pode exceder X%, 
            existe uma exceção somente p/ o grupo de funcionários discriminados no vetor abaixo ...*/
            if(id_cliente_tipo == 4) {
                if(((1 - desc_cliente / 100) * (1 - desc_extra / 100) * (1 + acrescimo_extra / 100)) < (1 - desc_cliente / 100)) {
                    if(vetor_funcionarios_podem_mudar_desconto_extra.indexOf(id_funcionario) > -1) {
                        var resposta = confirm('DESCONTO ACUMULADO EXCEDEU A % LIMITE DE DESCONTO PARA INDÚSTRIA '+desc_cliente+'% !!!\n\nDESEJA CONTINUAR ?')
                        if(resposta == false) {
                            document.getElementById('txt_quantidade'+i).focus()
                            document.getElementById('txt_quantidade'+i).select()
                            return false
                        }
                    }else {
                        alert('DESCONTO ACUMULADO EXCEDEU A % LIMITE DE DESCONTO PARA INDÚSTRIA '+desc_cliente+'% !!!')
                        document.getElementById('txt_quantidade'+i).focus()
                        document.getElementById('txt_quantidade'+i).select()
                        return false
                    }
                }
            }
            /*************************************************************************/
        }
        //Significa que existem Itens ESP(s) em que a Qtde que foi digitada é menor do que a Qtde Orçada ...
        if(itens_com_nadiplencia == 1) {
            var resposta = confirm('A QTDE PARA GERAR PEDIDO ESTÁ DIFERENTE DA QTDE ORÇADA !\n\nO PEDIDO SÓ SERÁ LIBERADO SE TODA A QTDE DO ORC FOR TRANSFORMADA EM PEDIDO !!!\n\nDESEJA CONTINUAR ?')
            if(resposta == false) return false
        }
    }
    
/*Quando for Exportação, a idéia desse controle é p/ que o usuário venha gerar todos os seus Orçamentos apenas 
num Único Pedido, pois só temos uma única Factura Proforma por Pedido ...*/
    var linhas_pedidos_estrangeiros = eval('<?=$linhas_pedidos_estrangeiros;?>')
    if(linhas_pedidos_estrangeiros == 1) {
        var resposta = confirm('DESEJA UTILIZAR ESSE PEDIDO N.º <?=$campos_pedidos_estrangeiros[0]['id_pedido_venda'];?> QUE ESTÁ EM ABERTO ?')
        if(resposta == true) document.form.hdd_pedido_venda.value = '<?=$campos_pedidos_estrangeiros[0]['id_pedido_venda'];?>'
    }
/*Desabilita as caixas de qtde para poder gravar no BD porque não é desabilitado a caixa de qtde quando 
o produto é do tipo ESP*/
    for(var i = 0; i < total_linhas; i++) {
        if(document.getElementById('chkt_orcamento_venda_item'+i).checked == true) {
            document.getElementById('txt_quantidade'+i).disabled    = false
//Tratamento com as Caixa de Qtde para gravar no BD ...
            document.getElementById('txt_quantidade'+i).value       = strtofloat(document.getElementById('txt_quantidade'+i).value)
        }
    }
    document.form.cmb_empresa.disabled = false
//Aqui trava o botão para evitar de gerar + de uma vez o mesmo pedido
    document.form.cmd_avancar.disabled = true
    document.form.passo.value = 1
    return true
}

//Aqui recebe o índice da linha e o Valor Original do Estoque
function calcular_estoque_real(indice, estoque_original) {
    var qtde                = (document.getElementById('txt_quantidade'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_quantidade'+indice).value)) : 0
    var estoque_original    = eval(estoque_original)
    //Somente para ESP ...
    if(document.getElementById('hdd_referencia'+indice).value == 'ESP') {
        /*Se a Qtde digitada for menor do que a Qtde Real, o Sistema deixa a caixa em Vermelho 
        p/ representar uma infração ...*/
        if(qtde < document.getElementById('txt_qtde_real'+indice).value) {
            document.getElementById('txt_quantidade'+indice).style.background   = 'red'
            document.getElementById('txt_quantidade'+indice).style.color        = 'white'
        }else {
            document.getElementById('txt_quantidade'+indice).style.background   = 'white'
            document.getElementById('txt_quantidade'+indice).style.color        = 'brown'
        }
    }
//Novo valor do Estoque Real
    if(estoque_original > qtde) {
        document.getElementById('txt_novo_estoque'+indice).value = estoque_original - qtde
    }else {
        document.getElementById('txt_novo_estoque'+indice).value = 0
    }
    document.getElementById('txt_novo_estoque'+indice).value = arred(document.getElementById('txt_novo_estoque'+indice).value, 2, 1)
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar('<?=$total_itens_pedidos;?>')">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Gerar Pedido p/ Empresa ->
            <select name="cmb_empresa" title="Selecione a Empresa" onchange="document.form.submit()" class="<?=$class;?>" <?=$disabled;?>>
            <?
                //Exibe as Empresas Albafer / Tool ou Grupo ...
                $simbolo = ($nota_sgd == 'N') ? ' <> ' : ' = ';
                
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' 
                        AND `id_empresa` $simbolo '4' ";
                echo combos::combo($sql, $cmb_empresa);
            ?>
            </select>
            &nbsp;-&nbsp;Faturar 
            <!--Esse parâmetro pop_up=1 é para que o Sistema não exiba o botão Voltar ...-->
            <a href="javascript:nova_janela('../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$campos[0]['id_cliente'];?>&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title="Alterar Cliente" class="link">
                <font color='yellow' size='-2'>
                    <?=$texto;?>
                </font>
            </a>
            para este Cliente.
        </td>
    </tr>
<?
//Aqui já submeteu
    if(!empty($cmb_empresa)) {
//Somente todos os itens em aberto com o id_orcamento q foi passado
        $sql = "SELECT ged.`id_empresa_divisao`, gpa.`id_familia`, ovi.`id_orcamento_venda_item`, 
                ovi.`id_produto_acabado`, ovi.`qtde`, ovi.`queima_estoque`, ovi.`desc_cliente`, ovi.`desc_extra`, ovi.`acrescimo_extra`, 
                ovi.`preco_liq_final`, ovi.`margem_lucro`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, u.`sigla` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`nota_sgd` = '$nota_sgd' AND ov.`status` < '2' 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND $condicao 
                WHERE ovi.`id_orcamento_venda` = '$id_orcamento_venda' 
                AND ovi.`status` < '2' ORDER BY ovi.`id_orcamento_venda_item` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {//Não encontrou nenhum Item
?>
    <Script Language = 'JavaScript'>
            alert('NÃO EXISTE(M) ITEM(NS) DE ORÇAMENTO(S) DO MESMO TIPO DE EMPRESA (DIVISÃO) DA EMPRESA QUE VOCÊ SELECIONOU PARA GERAR PEDIDO !')
    </Script>
<?
        }else {//Encontrou pelo menos 1 item
?>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onclick="selecionar_tudo_gerar_pedido(totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            <b>Qtde</b>
        </td>
        <td>
            <b>P&ccedil;s /<br>Emb.</b>
        </td>
        <td>
            <font title='Estoque Disponível Novo'>
                <b>E.D.N.</b>
            </font>
        </td>
        <td>
            <font title='Estoque Disponível'>
                <b>E.D.</b>
            </font>
        </td>
        <td>
            <b>Produto</b>
        </td>
        <td>
            <font title='Preço L. Final <?=$tipo_moeda;?>'>
                <b>Preço <br/>L. Final <?=$tipo_moeda;?></b>
            </font>
        </td>
    </tr>
<?
            /***************************************Duplicidade***************************************/
            //Aqui eu verifico se existe algum Item que está em Duplicidade, p/ informar o usuário ...
            $sql = "SELECT COUNT(ovi.`id_produto_acabado`) AS total_por_produto_acabado 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`nota_sgd` = '$nota_sgd' AND ov.`status` < '2' 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` AND $condicao 
                    WHERE ovi.`id_orcamento_venda` = '$id_orcamento_venda' 
                    AND ovi.`status` < '2' GROUP BY ovi.`id_produto_acabado` HAVING COUNT(total_por_produto_acabado) > '1' ";
            $campos_duplicidade = bancos::sql($sql);
            if(count($campos_duplicidade) >= 1) {
?>
                <Script Language = 'JavaScript'>
                    alert('EXISTE(M) ITEM(NS) EM DUPLICIDADE !')
                </Script>
<?
            }
            /*****************************************************************************************/
            for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox_gerar_pedido('<?=$i;?>', '#E8E8E8', '<?=$campos[$i]['referencia'];?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='center'>
            <?
                /**************************************************************************************/
                //Se a ML Gravada do Item do ORC < ML do Grupo, travo o Checkbox p/ que o Vendedor não consiga Gerar pedido p/ esse Item ...
                $fora_custo = vendas::verificar_orcamento_item_fora_custo($campos[$i]['id_orcamento_venda_item']);
                if($fora_custo == 'S') {
                    /*À partir do dia 12/04/2016, os vendedores poderão gerar Pedido p/ itens de Orçamento 
                    mesmo que esse item esteja na situação (Fora de Custo), decisão da Diretoria ...*/
                    
                    /*Se o(s) funcionário(s) logado(s) for(em) Roberto 62, Wilson 68, Dárcio 98 "porque programa" ou Nishimura 136 
                    pode-se gerar Pedido com Preço fora de Custo caso os mesmos desejarem ...
                    if($_SESSION['id_funcionario'] != 62 && $_SESSION['id_funcionario'] != 68 && $_SESSION['id_funcionario'] != 98 && $_SESSION['id_funcionario'] != 136) {
                        //$disabled_checkbox  = 'disabled';
                    }else {
                        $disabled_checkbox  = '';
                    }*/
                    //$disabled_checkbox  = '';
                    $fora_custo         = "<font color='red'><b> (Fora de custo)</b></font>";
                }else {
                    //$disabled_checkbox  = '';
                    $fora_custo         = '';
                }
            ?>
            <input type='checkbox' name='chkt_orcamento_venda_item[]' id='chkt_orcamento_venda_item<?=$i;?>' value="<?=$campos[$i]['id_orcamento_venda_item'];?>" onclick="checkbox_gerar_pedido('<?=$i;?>', '#E8E8E8', '<?=$campos[$i]['referencia'];?>')" class='checkbox' <?=$disabled_checkbox;?>>
        </td>
        <td align='center'>
        <?
            $qtde_orcamento = $campos[$i]['qtde'];
/*Aqui eu verifico o quanto que eu tenho já importado desse item de orçamento em todos os pedidos 
com exceção do pedido corrente ...*/
            $sql = "SELECT SUM(`qtde`) AS qtde_total_em_pedido 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' ";
            $campos_qtde_pedido 	= bancos::sql($sql);
            $qtde_total_em_pedido 	= $campos_qtde_pedido[0]['qtde_total_em_pedido'];
            $restante                   = $qtde_orcamento - $qtde_total_em_pedido;
//Aqui eu verifico a qtde disponível desse item em Estoque e a qtde dele em Produção ...
            $estoque_produto            = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
            $racionado                  = $estoque_produto[5];
            $qtde_estoque               = $estoque_produto[3];
            //Se retornar nulo do banco
            if($qtde_estoque == '') $qtde_estoque = 0;
            
            //Aqui tem esse cálculo por causa do início quando carregar a tela ???
            if($racionado == 1) {
                $type                   = 'hidden';
                $qtde_estoque           = 0;
                $qtde_estoque_calculo   = 0;
                $msg_racionado          = '<font color="red"><b>Racionado</b></font>';
            }else {
                $type                   = 'text';
                $qtde_estoque_calculo   = ($qtde_estoque > $restante) ? $qtde_estoque - $restante : 0;
            }
            
            /************************************************************/
            /****Tratamento com as Casas Decimais do campo Quantidade****/
            /************************************************************/
            if($campos[$i]['sigla'] == 'KG') {//Essa é a única sigla que permite trabalharmos com Qtde Decimais ...
                $onkeyup            = "verifica(this, 'moeda_especial', 1, '', event) ";
                $qtde_apresentar    = number_format($restante, 1, ',', '.');
            }else {
                $onkeyup            = "verifica(this, 'aceita', 'numeros', '', event) ";
                $qtde_apresentar    = (integer)$restante;
            }
            /************************************************************/
        ?>
            <input type='text' name='txt_quantidade[]' id='txt_quantidade<?=$i;?>' value='<?=$qtde_apresentar;?>' title="Digite a Quantidade" maxlength="8" size="8" onclick="checkbox_gerar_pedido('<?=$i;?>', '#E8E8E8', '<?=$campos[$i]['referencia'];?>');return focos(this)" onkeyup="<?=$onkeyup;?>;calcular_estoque_real('<?=$i;?>', '<?=$qtde_estoque;?>')" class='textdisabled' disabled>
            <input type='hidden' name='txt_qtde_real[]' id='txt_qtde_real<?=$i;?>' value='<?=$qtde_apresentar;?>'>
        </td>
        <td>
        <?
//Traz a quantidade de peças por embalagem da embalagem principal daquele produto
            $sql = "SELECT `pecas_por_emb` 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `embalagem_default` = '1' LIMIT 1 ";
            $campos_pecas_emb = bancos::sql($sql);
            $pecas_embalagem = (count($campos_pecas_emb) == 1) ? $campos_pecas_emb[0]['pecas_por_emb'] : 0;
            echo number_format($pecas_embalagem, 0, ',', '.');
        ?>
            <input type='hidden' id='hdd_pecas_emb<?=$i;?>' value='<?=$pecas_embalagem;?>'>
        </td>
        <td align='center'>
            <?=$msg_racionado;?>
            <input type='<?=$type;?>' name='txt_novo_estoque[]' id='txt_novo_estoque<?=$i;?>' value='<?=number_format($qtde_estoque_calculo, 2, ',', '.');?>' title="Estoque Disponível Novo" maxlength="8" size="8" class='textdisabled' disabled>
            <input type='hidden' id='hdd_referencia<?=$i;?>' value='<?=$campos[$i]['referencia'];?>'>
            <input type='hidden' id='hdd_discriminacao<?=$i;?>' value='<?=$campos[$i]['discriminacao'];?>'>
            <input type='hidden' id='hdd_familia<?=$i;?>' value='<?=$campos[$i]['id_familia'];?>'>
        </td>
        <td align='center'>
        <?
            if($racionado == 1) {
                echo '&nbsp;';
            }else {
                echo number_format($qtde_estoque, 2, ',', '.');
            }
        ?>
        </td>
        <td align='left'>
        <?
//Produto Normal
            if($campos[$i]['referencia'] != 'ESP') {
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '', $campos[$i]['id_produto_acabado_discriminacao']);
            }else {
//Quando for ESP printa de Verde ...
    ?>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '', $campos[$i]['id_produto_acabado_discriminacao']);?>
    <?
            }
            if($campos[$i]['queima_estoque'] == 'S') echo '&nbsp;<img src="../../../../imagem/queima_estoque.png" title="Queima de Estoque" alt="Queima de Estoque" border="0">';
            echo $fora_custo;
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>
            <input type='hidden' id='hdd_preco_liq_final<?=$i;?>' value='<?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>'>
            <input type='hidden' id='hdd_desc_cliente<?=$i;?>' value='<?=$campos[$i]['desc_cliente'];?>'>
            <input type='hidden' id='hdd_desc_extra<?=$i;?>' value='<?=$campos[$i]['desc_extra'];?>'>
            <input type='hidden' id='hdd_acrescimo_extra<?=$i;?>' value='<?=$campos[$i]['acrescimo_extra'];?>'>
        </td>
    </tr>
<?
            }
        }
        if($linhas == 0) {//Não encontrou nenhum Item
?>
    <tr class='atencao' align='center'>
        <td></td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
        </td>
    </tr>
<?
        }else {//Encontrou pelo menos 1 item
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class='botao'>
            <input type='submit' name="cmd_avancar" value="&gt;&gt; Avançar &gt;&gt;" title="Avançar" class='botao'>
        </td>
    </tr>
</table>
<pre>
<font color='red'><b>Observação:</b></font>

Só exibe empresas do mesmo Tipo de Nota que foi selecionado na Cabeçalho do Orçamento

Orc - NF  => Empresa = Albafer e Tool Master
Orc - SGD => Empresa = Grupo

Aqui só exibe itens de Orçamento cujo os produtos são referentes ao Tipo de Empresa (Divisão)
da empresa que você selecionar para gerar pedido

Pedido - Albafer     => Produtos da Divisão Albafer
Pedido - Tool Master => Produtos da Divisão Tool Master
Pedido - Grupo       => Produtos da Albafer e Tool Master
</pre>
<?
        }
//Ainda não submeteu
    }else {
?>
    <tr><td></td></tr>
    <tr class='atencao' align='center'>
        <td colspan='7'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'" class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
<input type='hidden' name='hdd_pedido_venda'><!--Controle feito somente p/ Exportação ...-->
<input type='hidden' name='id_orcamento_venda' value="<?=$_GET['id_orcamento_venda'];?>">
<input type='hidden' name='passo'>
</form>
</body>
</html>
<?}?>