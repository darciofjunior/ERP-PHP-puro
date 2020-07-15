<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
require('../../../lib/vendas.php');
segurancas::geral($PHP_SELF, '../../../../');

if($passo == 1) {
    $valor_dolar_nota 	= genericas::moeda_dia('dolar');
    $data_sys           = date('Y-m-d H:i:s');

    if(!empty($_GET['id_pedido_venda'])) {//Significa que o usuário veio diretamente da Tela de Itens do Gerenciar Estoque ...
        //Busca dos Dados do Pedido através do $id_pedido_venda ...
        $sql = "SELECT c.`trading`, c.`tipo_faturamento`, c.`tipo_suframa`, c.`suframa_ativo`, ov.`tipo_frete`, pv.`id_cliente`, pv.`id_empresa`, 
                pv.`finalidade`, pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pv.`prazo_medio`, pv.`livre_debito` 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                WHERE pv.`id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $trading                = $campos[0]['trading'];
        $tipo_faturamento 	= $campos[0]['tipo_faturamento'];
        $tipo_suframa 		= $campos[0]['tipo_suframa'];
        $suframa_ativo 		= $campos[0]['suframa_ativo'];
        //Tenho que renomear essa variável até porque já existe uma $id_empresa na sessão do Sistema ...
        $id_empresa_pedido 	= $campos[0]['id_empresa'];
        if($id_empresa_pedido == 4) {//Só vai gerar Núm. sequencial de Nota Fiscal para a Empresa quando esta for do tipo Grupo ...
            //Busco o primeiro N.º de Nota Fiscal disponível p/ a Empresa do Pedido ...
            $id_nf_num_nota     = faturamentos::verificar_numero_disponivel($id_empresa_pedido);

            //Se a Nota for do Tipo SGD, então não existe Trading e não existe Suframa para esse Cliente ...
            $trading            = 0;
            $tipo_suframa       = 0;
            $suframa_ativo      = 'N';
        }else {
            $id_nf_num_nota     = 'NULL';
        }
        if($suframa_ativo == 'S' && $tipo_suframa == 0) exit('CHAMAR DÁRCIO - PROBLEMA DE SUFRAMA !<br>'.$PHP_SELF);
        /****************Controle de Cabeçalho de NF e Cadastro de Cliente****************/
        if($id_empresa_pedido == 1 || $id_empresa_pedido == 2) {//Se o Tipo de Pedido do Cliente = Alba ou Tool ...
            if($tipo_faturamento == 1 || $tipo_faturamento == 2) {
                $id_empresa_pedido = $tipo_faturamento;//O sys grava no Cab. o Tipo de Fat. do Cliente ...
            }else if($tipo_faturamento == 'Q') {//Quando é qualquer empresa, sugere então a Empresa definida nas variáveis ...
                $id_empresa_pedido = intval(genericas::variavel(47));
            }
        }
        
        /************************Controle com a Transportadora************************
        Aqui eu busco o "id_transportadora" que foi utilizado no último Pedido de Vendas do Cliente ...
        
        Obs: Faço isso porque pode ser que nesse momento eu esteja faturando uma Pendência de 6 meses atrás, sei lá +/-, e o Cliente sei lá 
        por quais motivos ou razões resolveu mudar e hoje está trabalhando com uma outra ...*/
        $sql = "SELECT `id_transportadora` 
                FROM `pedidos_vendas` 
                WHERE `id_cliente` = '".$campos[0]['id_cliente']."' ORDER BY `id_pedido_venda` DESC LIMIT 1 ";
        $campos_ultima_transp_pedido = bancos::sql($sql);
        
//Inserção na Tabela de Nota Fiscal normalmente
        $sql = "INSERT INTO `nfs` (`id_nf`, `id_funcionario`, `id_cliente`, `id_empresa`, `id_transportadora`, `id_nf_num_nota`, `finalidade`, `frete_transporte`, `tipo_nfe_nfs`, `data_emissao`, `vencimento1`, `vencimento2`, `vencimento3`, `vencimento4`, `valor_dolar_dia`, `prazo_medio`, `data_sys`, `trading`, `suframa`, `suframa_ativo`, `livre_debito`) VALUES (NULL, '$_SESSION[id_funcionario]', '".$campos[0]['id_cliente']."', '$id_empresa_pedido', '".$campos_ultima_transp_pedido[0]['id_transportadora']."', $id_nf_num_nota, '".$campos[0]['finalidade']."', '".$campos[0]['tipo_frete']."', 'S', '".date('Y-m-d')."', '".$campos[0]['vencimento1']."', '".$campos[0]['vencimento2']."', '".$campos[0]['vencimento3']."', '".$campos[0]['vencimento4']."', '$valor_dolar_nota', '".$campos[0]['prazo_medio']."', '$data_sys', '$trading', '$tipo_suframa', '$suframa_ativo', '".$campos[0]['livre_debito']."') ";
        bancos::sql($sql);
        $id_nf = bancos::id_registro();
        
        //Uma vez já vinculado esse N.º em Nota Fiscal, marco o mesmo como reservado ...
        faturamentos::gerar_numero_nf($id_empresa_pedido, $id_nf_num_nota);
    }else {
        //Aqui eu verifico se existe pelo menos 1 NF do Cliente que esteja em aberto sem Itens ...
        $sql = "SELECT `id_nf` 
                FROM `nfs` 
                WHERE `id_cliente` = '$_GET[id_cliente]' 
                AND `status` <= '2' 
                AND `id_nf` NOT IN 
                (SELECT DISTINCT(nfs.`id_nf`) 
                FROM `nfs` 
                INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                WHERE nfs.id_cliente = '$_GET[id_cliente]') 
                ORDER BY `id_nf` LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Significa que já existe uma NF em aberto sem Itens, sendo assim eu só reaproveita esta ...
            $id_nf = $campos[0]['id_nf'];
        }else {//Ainda não existe nenhuma NF sem Itens, sendo assim eu vou gerar uma NF ...
            vendas::transportadoras_padroes($_GET['id_cliente']);//Verifico se o cliente possui as transportadoras padrões cadastradas ...

            //Busca uma Transportadora qualquer do Cliente para gerar a NF ...
            $sql = "SELECT `id_transportadora` 
                    FROM `clientes_vs_transportadoras` 
                    WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
            $campos_transportadoras = bancos::sql($sql);
            
            //Busco o primeiro N.º de Nota Fiscal disponível p/ a Empresa SGD ...
            $id_nf_num_nota         = faturamentos::verificar_numero_disponivel(4);

            /*Além de gerar a NF na parte abaixo, já atribuo na mesma o último N.º de Talonário que 
            realmente se encontra disponível ...*/
            $sql = "INSERT INTO `nfs` (`id_nf`, `id_funcionario`, `id_cliente`, `id_empresa`, `id_transportadora`, `id_nf_num_nota`, `finalidade`, `frete_transporte`, `tipo_nfe_nfs`, `valor_dolar_dia`, `data_sys`, `livre_debito`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_GET[id_cliente]', '4', '".$campos_transportadoras[0]['id_transportadora']."', '$id_nf_num_nota', 'R', '2', 'S', '$valor_dolar_nota', '$data_sys', 'N') ";
            bancos::sql($sql);
            $id_nf = bancos::id_registro();
?>
    <Script Language = 'JavaScript'>
        alert('A EMPRESA GERADA PARA ESTA NF FOI COMO SENDO "GRUPO" - CERTIFIQUE-SE DE QUE ESTÁ ESTEJA CORRETA !')
    </Script>
<?
        }
        //Uma vez já vinculado esse N.º em Nota Fiscal, marco o mesmo como reservado ...
        faturamentos::gerar_numero_nf(4, $id_nf_num_nota);
    }
?>
    <Script Language = 'JavaScript'>
/*A NF acabou de ser confeccionada nesse exato momento. Sendo assim eu passo opcao = '1' porque a 1ª que terá que ser feita 
após a inclusão dessa NF é a Inclusão de seus itens, é o preenchimento de dados simples no cabeçalho, para daí então poder apontar 
a situação da NF como 'LIBERADA para FATURAR' que já seria aí um segundo estágio da NF*/
        window.location = 'itens/index.php?id_nf=<?=$id_nf;?>&opcao=1'
    </Script>
<?
}else {
    /*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
    requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../..';
    $qtde_por_pagina = 50;
    //Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../classes/cliente/tela_geral_filtro.php');
    if($linhas > 0) {//Se retornar pelo menos 1 registro
?>
<html>
<head>
<title>.:: Consultar Cliente(s) p/ Incluir NF de Saída ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_cliente, credito, qtde_nf_abertas) {
    if(credito == 'C' || credito == 'D') {
        alert('CLIENTE COM CRÉDITO '+credito+' !\n NÃO É PERMITIDO A EMISSÃO DE NOTA FISCAL PARA ESTE CLIENTE !')
    }else {
        if(qtde_nf_abertas > 0) {//O Cliente possui alguma NF em aberto
            alert('ESTE CLIENTE POSSUI '+qtde_nf_abertas+' NF(S) EM ABERTO(S) E QUE ESTÁ(ÃO) SEM ITEM(NS) !\nOBS: NÃO TEM COMO INFORMAR O N.º DA(S) NF(S), PORQUE NEM TODA(S) POSSUEM N.º !')
        }else {//O Cliente não possui nenhuma NF em aberto, portanto pode continuar
            window.location = 'incluir.php?passo=1&id_cliente='+id_cliente
        }
    }
}
</Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Cliente(s) p/ Incluir NF de Saída 
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Tp Cliente
        </td>
        <td>
            Duplicatas
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>', '<?=financeiros::controle_credito($campos[$i]['id_cliente']);?>')" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>', '<?=$campos[$i]['credito'];?>')" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td>
        <?
            $sql = "SELECT `id_conta_receber` 
                    FROM `contas_receberes` 
                    WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_contas_receberes = bancos::sql($sql);
            if(count($campos_contas_receberes) == 1) {
                echo 'SIM';
            }else {
                echo 'NÃO';
            }
        ?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
}
?>