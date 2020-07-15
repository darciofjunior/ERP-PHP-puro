<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
session_start('funcionarios');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) {
    require('../../../../lib/menu/menu.php');
    segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');
}
$mensagem[1] = 'ITEM INCLUIDO COM SUCESSO !';
$mensagem[2] = 'ITEM(NS) ATUALIZADO(S) COM SUCESSO !';
$mensagem[3] = 'ITEM EXCLUIDO COM SUCESSO !';
$mensagem[4] = 'PRODUTO BLOQUEADO !!! NÃO PODE SER EXCLUIDO, PORQUE ESTÁ SENDO MANIPULADO PELO ESTOQUISTA !';
$mensagem[5] = 'ESSE ITEM NÃO PODE SER EXCLUÍDO !\n\nPODE CONTER ENTREGA(S) EM VALE OU SE FOR "ESP" É NECESSÁRIO DESLIBERAR O CABEÇALHO DESTE PEDIDO P/ PODER EXCLUÍ-LO !';
$mensagem[6] = 'NENHUM ITEM DE PEDIDO PODE SER EXCLUÍDO !\nPORQUE ESTE PEDIDO JÁ FOI LIBERADO !';

/*********************************************************************************/
/***************** Lógica de Pendência de Acompanhamento *************************/
/*********************************************************************************/
//Aqui eu verifico se existe alguma pendência de Acompanhamento p/ este Pedido ...
$sql = "SELECT id_vendedor_pendencia 
        FROM `vendedores_pendencias` 
        WHERE `id_pedido_venda` = '$id_pedido_venda' limit 1 ";
$campos_pendencia = bancos::sql($sql);
if(count($campos_pendencia) == 1) {
/*Deleto a Pendência de Pedido daquele Vendedor, pois o Vendedor já tomou conhecimento, se ele quiser registrar 
algum Follow-UP daí fica a critério dele ...*/
    $sql = "DELETE FROM `vendedores_pendencias` WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
    bancos::sql($sql);
}
/*********************************************************************************/

//Aqui é a Busca da Variável de Vendas
$fator_desc_maximo_venda = genericas::variavel(19);
//Busca o nome do Cliente, o Contato + o id_cliente_contato p/ poder buscar com + detalhes os dados do cliente
$sql = "SELECT c.`cod_cliente`, c.`id_pais`, 
        IF(c.`nomefantasia` = '', c.`razaosocial`, CONCAT(c.`nomefantasia`, ' (', c.`razaosocial`, ')')) AS cliente, c.`id_uf`, 
        c.`id_cliente_tipo`, c.`credito`, c.`trading`, c.`tributar_ipi_rev`, c.`isento_st`, 
        c.`tipo_suframa`, c.`cod_suframa`, c.`suframa_ativo`, cc.`nome`, pv.* 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = pv.`id_cliente_contato` 
        INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
        WHERE pv.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
$campos                 = bancos::sql($sql);
//Aqui eu renomeio a variável p/ não dar conflito com o id_funcionario da Sessão ...
$id_funcionario_pedido  = $campos[0]['id_funcionario'];
$id_login_pedido        = $campos[0]['id_login'];
$id_cliente             = $campos[0]['id_cliente'];
$id_pais                = $campos[0]['id_pais'];
$cod_suframa            = $campos[0]['cod_suframa'];
$tipo_suframa           = $campos[0]['tipo_suframa'];
$suframa_ativo          = $campos[0]['suframa_ativo'];
$cod_cliente            = $campos[0]['cod_cliente'];
$cliente                = $campos[0]['cliente'];
$contato                = $campos[0]['nome'];
$trading                = $campos[0]['trading'];
$tributar_ipi_rev       = $campos[0]['tributar_ipi_rev'];
$isento_st              = $campos[0]['isento_st'];
$id_uf_cliente          = $campos[0]['id_uf'];
$faturar_em             = ($campos[0]['faturar_em'] != '0000-00-00') ? data::datetodata($campos[0]['faturar_em'], '/') : '';
$seu_pedido_numero      = $campos[0]['num_seu_pedido'];
$data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');
$prazo_medio            = $campos[0]['prazo_medio'];

//Aqui é a verificação do Tipo de Nota
if($campos[0]['id_empresa'] == 4) {//Se a Empresa for Grupo ...
    $rotulo_sgd = ' - SGD';
    $nota_sgd   = 'S';
}else {
    $rotulo_sgd = ' - NF';
    $nota_sgd   = 'N';
//Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
    if($campos[0]['finalidade'] == 'C') {
        $finalidade = 'CONSUMO';
    }else if($campos[0]['finalidade'] == 'I') {
        $finalidade = 'INDUSTRIALIZAÇÃO';
    }else {
        $finalidade = 'REVENDA';
    }
    $rotulo_sgd.= '/'.$finalidade;
}
$prazo_faturamento      = $rotulo_sgd;
//Significa que o Cliente é do Tipo Internacional
$tipo_moeda             = ($id_pais != 31) ? 'U$ ' : 'R$ ';
$mg_l_m_g               = $campos[0]['mg_l_m_g'];
$liberado               = $campos[0]['liberado'];
$modo_venda             = $campos[0]['modo_venda'];
$data_hora_alteracao    = '<center><font color="darkblue"><b>Data: </b></font>'.data::datetodata(substr($campos[0]['data_sys'], 0, 10), '/').' - <font color="darkblue"><b>Hora: </b></font>'.substr($campos[0]['data_sys'], 11, 8).'</center>';

$vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = 'tabela_itens_radio.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function igualar(indice) {
    var controle = 0, existe = 0, liberado = '', codigo = '', cont = 0
    var elemento = '', objeto = ''
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'radio') cont ++
    }
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'hidden' && document.form.elements[i].name == 'opt_item') existe ++
    }
    if(cont > 1) {
        elemento = document.form.opt_item[indice].value
        objeto = document.form.opt_item[indice]
    }else {
        if(existe == 0) {
            elemento = document.form.opt_item.value
            objeto = document.form.opt_item
        }else {
            elemento = document.form.opt_item[indice].value
            objeto = document.form.opt_item[indice]
        }
    }
    if(objeto.type == 'radio') {
        for(i = 0; i < elemento.length; i ++) {
            if(elemento.charAt(i) == '|') {
                controle ++
            }else {
                if(controle == 1) {
                    liberado = liberado + elemento.charAt(i)
                }else {
                    codigo = codigo + elemento.charAt(i)
                }
            }
        }
        document.form.opt_item_principal.value = codigo
    }else {
        limpar_radio()
    }
}
</Script>
</head>
<body bottommargin='1' marginheight='1' topmargin='5'>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='26'>
            <font size='3'>
                Pedido N.º 
                <font color='yellow'>
                <?
                    echo $id_pedido_venda;
                    //Se existir a marcação de Livre de Débito ...
                    if($campos[0]['livre_debito'] == 'S') echo '<font color="darkgreen" title="Livre de Débito Propaganda / Marketing" style="cursor:help"><b> (LD)</b></font>';
                    
                    echo $prazo_faturamento;
                ?>
                    -
                    <a href="javascript:nova_janela('../../../classes/pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$id_cliente;?>', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Relatório de Pendências' class='link'>
                        <font color='#48FF73' size='-1'>
                            Pendências
                        </font>
                    </a>
                </font>
            </font>
            -
<!--O nome desse parâmetro tem que ser id_clientes, porque existe uma outra tela no Sistema 
que leva como parâmetro vários clientes, daí por isso que eu acabei mantendo esse nome ...-->
            <a href="javascript:nova_janela('/erp/albafer/modulo/vendas/apv/informacoes_apv.php?id_clientes=<?=$id_cliente;?>&pop_up=1', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='APV do Cliente' class='link'>
                <font color='#49D2FF' size='-1'>
                    APV do Cliente
                </font>
            </a>
            -
            <a href="javascript:parent.location = '../../pdt/pdt.php'" title='PDT' class='link'>
                <font color='#ff9900' size='-1'>PDT</font>
            </a>
        </td>
    </tr>
    <tr class='linhadestaque' style='cursor:pointer'>
        <td height='21' align='left' colspan='2'>
            <a href="javascript:nova_janela('../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$id_cliente;?>&nao_exibir_menu=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <font color='yellow'>
                    Cliente:
                    <font color='#FFFFFF'>
                        <?=$cod_cliente.' - '.$cliente;?>
                    </font>
                </font>
                <img src = '../../../../imagem/propriedades.png' title='Detalhes de Cliente' alt='Detalhes de Cliente' style='cursor:pointer' border='0'>
            </a>
            <font color='yellow'>
                / Crédito:
            </font>
            <?
                /*Somente os Usuários Roberto "62" ou Dárcio "98 porque programa" que podem 
                estar trocando o Crédito do Cliente por aqui p/ facilitar programação ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
            ?>
                    <a href = "javascript:nova_janela('../../../financeiro/cadastro/credito_cliente/detalhes.php?id_cliente=<?=$id_cliente;?>&pop_up=1', 'POP', '', '', '', '', 450, 780, 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Cr&eacute;dito do Cliente'>
            <?
                }
                echo financeiros::controle_credito($id_cliente);
                /*Somente os Usuários Roberto "62" ou Dárcio "98 porque programa" que podem 
                estar trocando o Crédito do Cliente por aqui p/ facilitar programação ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) echo '</a>';
            ?>
            <font color='yellow'>
                / Contato:
            </font>
            <?=$contato;?>
        </td>
    </tr>
</table>
<?
//Aqui eu verifico todos os Orçamentos q estão atrelados a esse pedido
    $sql = "SELECT DISTINCT(ovi.`id_orcamento_venda`), ov.`prazo_a`, ov.`prazo_b`, ov.`prazo_c`, ov.`prazo_d`, 
            ov.`finalidade`, ov.`nota_sgd`, ov.`prazo_medio`, ov.`valor_dolar` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
            WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' ";
    $campos_orcamento = bancos::sql($sql);
    $linhas_orcamento = count($campos_orcamento);
    if($linhas_orcamento > 0) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Orçamento(s) Atrelado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <b>N.º Orçamento</b>
        </td>
        <td>
            <b>Forma de Venda</b>
        </td>
        <td>
            <b>Prazo Médio</b>
        </td>
        <td>
            <b>D&oacute;lar do Or&ccedil;amento R$ </b></td>
        <td>
            <b>Observação</b>
        </td>
    </tr>
<?
//Aki eu limpo essa variável para não dar conflito com a mesma variável q está na linha 74
        $prazo_faturamento = '';
        for($i = 0; $i < $linhas_orcamento; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos_orcamento[$i]['id_orcamento_venda'];?>
        </td>
        <td align="left">
        <?
            if($campos_orcamento[$i]['prazo_d'] > 0) $prazo_faturamento = '/'.$campos_orcamento[$i]['prazo_d'];
            if($campos_orcamento[$i]['prazo_c'] > 0) $prazo_faturamento= '/'.$campos_orcamento[$i]['prazo_c'].$prazo_faturamento;
            if($campos_orcamento[$i]['prazo_b'] > 0) {
                $prazo_faturamento= $campos_orcamento[$i]['prazo_a'].'/'.$campos_orcamento[$i]['prazo_b'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos_orcamento[$i]['prazo_a'] == 0) ? 'À vista' : $campos_orcamento[$i]['prazo_a'];
            }
//Aqui é a verificação do Tipo de Nota
            $nota_sgd = $campos_orcamento[$i]['nota_sgd'];
            if($nota_sgd == 'S') {
                $rotulo_sgd = ' - SGD';
            }else {
                $rotulo_sgd = ' - NF';
//Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
                if($campos_orcamento[$i]['finalidade'] == 'C') {
                    $finalidade = 'CONSUMO';
                }else if($campos_orcamento[$i]['finalidade'] == 'I') {
                    $finalidade = 'INDUSTRIALIZAÇÃO';
                }else {
                    $finalidade = 'REVENDA';
                }
                $rotulo_sgd.= '/'.$finalidade;
            }
            echo $prazo_faturamento.=$rotulo_sgd;
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td>
            <?=$campos_orcamento[$i]['prazo_medio'];?>
        </td>
        <td align='right'>
            <?=number_format($campos_orcamento[$i]['valor_dolar'], 4, ',', '.');?>
        </td>
        <td align='left'>
            <font color='red'>
                EM DESENVOLVIMENTO
            </font>
        </td>
    </tr>
<?
        }
?>
</table>
<?
    }
?>
<!--Pré-Cabeçalho de Pedido de Venda-->
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhanormal'>
        <td bgcolor='#CECECE'>
            <b>FRETE / TRANSPORTADORA: </b>
            <?
                $sql = "SELECT ov.`tipo_frete` 
                        FROM `pedidos_vendas_itens` pvi 
                        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                        WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
                $campos_orcamento = bancos::sql($sql);

                if($campos_orcamento[0]['tipo_frete'] == 'F') {
                    echo 'FOB / ';
                }else if($campos_orcamento[0]['tipo_frete'] == 'C') {
                    echo 'CIF / ';
                }
            
                //Busca o Tipo da Transportadora ...
                $sql = "SELECT `nome` 
                        FROM `transportadoras` 
                        WHERE `id_transportadora` = '".$campos[0]['id_transportadora']."' LIMIT 1 ";
                $campos_transportadora = bancos::sql($sql);
                echo $campos_transportadora[0]['nome'];
            ?>
        </td>
        <td bgcolor='#CECECE'>
            <b>FINALIDADE: </b>
            <?
                echo $finalidade;
                if($isento_st == 'S') echo '<font color="red" size="2"><b> (ISENTO DE SUBSTITUIÇÃO TRIBUTÁRIA)</b></font>';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#CECECE'>
            <b>TIPO / CÓDIGO DO SUFRAMA: </b>
<?
                $tipo_suframa_vetor[1] = 'Área de Livre Comércio (ICMS/IPI) / ';
                $tipo_suframa_vetor[2] = 'Zona Franca de Manaus (ICMS/PIS/COFINS/IPI) / ';
                $tipo_suframa_vetor[3] = 'Amazônia Ocidental (IPI) / ';

                echo '<br><font color="blue">'.$tipo_suframa_vetor[$tipo_suframa].$cod_suframa.'</font>';
//Se o Suframa for Ativo, então exibo essa Mensagem de Ativo ao lado ...
                if($suframa_ativo == 'S') echo ' <font color="red"><b>(ATIVO)</b></font>';

                if($tipo_suframa == 1 && $suframa_ativo == 'S') {//Área de Livre e o Cliente possui o Suframa Ativo ...
?>
                    <br>Desconto de ICMS = <?=number_format(genericas::variavel(40), 2, ',', '.');?> % <font color='red'>(A ser concedido na Emissão da NF)</font>
<?
                }else if($tipo_suframa == 2 && $suframa_ativo == 'S') {//Zona Franca de Man...
?>
                    <br>Desconto de PIS + Cofins = <?=number_format((genericas::variavel(20)+genericas::variavel(21)), 2, ',', '.');?> % e ICMS = <?=number_format(genericas::variavel(40), 2, ',', '.');?> % <font color='red'>(A ser concedido na Emissão da NF)</font>
<?
                }
?>
        </td>
        <td bgcolor='#CECECE'>
            <b>MODO DE VENDA: </b>
            <?
                if($modo_venda == 1) {
                    echo 'FONE';
                }else {
                    echo 'VENDEDOR';
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#CECECE'>
            <b>SITUAÇÃO DO PEDIDO: </b>
            <?
                if($liberado == 1) {
                    echo "<font color='darkblue'><b>LIBERADO</b></font>";
                }else {
                    echo "<font color='red'><b>NÃO LIBERADO</b></font>";
                }
                if($id_funcionario_pedido > 0) {//99% dos casos, serão os funcionários da Albafer que irão acessar nosso sistema ...
                    $sql = "SELECT f.`nome` AS login 
                            FROM `funcionarios` f 
                            INNER JOIN `logins` l ON l.id_funcionario = f.`id_funcionario` 
                            WHERE f.`id_funcionario` = '$id_funcionario_pedido' LIMIT 1 ";
                }else {//No demais representantes ...
                    $sql = "SELECT `login` 
                            FROM `logins` 
                            WHERE `id_login` = '$id_login_pedido' LIMIT 1 ";
                }
                $campos_login = bancos::sql($sql);
                echo " - <b>Última alteração feita por ".$campos_login[0]['login'].'<br>'.$data_hora_alteracao."</b>";
            ?>
        </td>
        <td bgcolor='#CECECE'>
            <b>SEU PEDIDO N.º: </b><?=$seu_pedido_numero;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#CECECE'>
            <b>FATURAR EM: </b><?=$faturar_em;?>
        </td>
        <td bgcolor='#CECECE'>
            <b>DATA DE EMISSÃO: </b><?=$data_emissao;?>
        </td>
    </tr>
<?
/*********************************************/
//Dados Referente ao A
?>
    <tr class='linhanormal'>
        <td>
            <b>PRAZO (A): </b>
            <?
                if($campos[0]['vencimento1'] == 0) {
                    echo 'À vista';
                }else {
                    echo $campos[0]['vencimento1'].' DIAS';
                }
            ?>
        </td>
        <td>
            <b>VENC: </b><?=data::adicionar_data_hora($faturar_em, $campos[0]['vencimento1']);?>
        </td>
    </tr>
<?
/*********************************************/
//Dados Referente ao B
?>
    <tr class='linhanormal'>
        <td>
            <b>PRAZO (B): </b>
            <?
                if($campos[0]['vencimento2'] != 0) {
                    echo $campos[0]['vencimento2'].' DDL';
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
        <td>
            <b>VENC: </b>
            <?
                if($campos[0]['vencimento2'] != 0) {
                    echo data::adicionar_data_hora($faturar_em, $campos[0]['vencimento2']);
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
    </tr>
<?
/*********************************************/
//Dados Referente ao C
?>
    <tr class='linhanormal'>
        <td>
            <b>PRAZO (C): </b>
            <?
                if($campos[0]['vencimento3'] != 0) {
                    echo $campos[0]['vencimento3'].' DDL';
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
        <td>
            <b>VENC: </b>
            <?
                if($campos[0]['vencimento3'] != 0) {
                    echo data::adicionar_data_hora($faturar_em, $campos[0]['vencimento3']);
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
    </tr>
<?
/*********************************************/
//Dados Referente ao D
?>
    <tr class='linhanormal'>
        <td>
            <b>PRAZO (D): </b>
            <?
                if($campos[0]['vencimento4'] != 0) {
                    echo $campos[0]['vencimento4'].' DDL';
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
        <td>
            <b>VENC: </b>
            <?
                if($campos[0]['vencimento4'] != 0) {
                    echo data::adicionar_data_hora($faturar_em, $campos[0]['vencimento4']);
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#CECECE' colspan='2'>
            <b>PRAZO MÉDIO: </b><?=$prazo_medio;?>
            <?
                if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                    //Verifico se esse Pedido veio de um Orçamento com Marcação de "Comprar como Export" e se sim apresento na Tela de Itens ...
                    $sql = "SELECT ov.comprar_como_export 
                            FROM `pedidos_vendas_itens` pvi 
                            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda_item = pvi.id_orcamento_venda_item 
                            INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
                            WHERE pvi.id_pedido_venda = '$id_pedido_venda' LIMIT 1 ";
                    $campos_orcamento = bancos::sql($sql);
                    if($campos_orcamento[0]['comprar_como_export'] == 'S') {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <font color='darkblue'><b>(COMPRAR COMO EXPORT)</b></font>";
                    }
                }
            ?>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='2'>
            <iframe name='detalhes' id='detalhes' src = '../../../classes/follow_ups/detalhes.php?identificacao=<?=$id_pedido_venda;?>&origem=2' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
<?
    /**********************************************************************/
    /*************************Observação do Cliente************************/
    /**********************************************************************/
    //Busco a observação do Cliente que foi registrada no Follow-Up ...
    $sql = "SELECT `observacao` 
            FROM `follow_ups` 
            WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
            AND `origem` = '15' LIMIT 1 ";
    $campos_follow_up = bancos::sql($sql);
    if(count($campos_follow_up) == 1) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2' bgcolor='yellow'>
            <img src = '../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
            <font size='3'>
                <b>Obs. do Cliente: 
                <font color='blue'>
                    <?=$campos_follow_up[0]['observacao'];?>
                </font>
                </b>
            </font>
            <img src = '../../../../imagem/exclamacao.gif' title='Observação do Pedido' alt='Observação do Pedido' height='30' border='0'>
        </td>
    </tr>
<?
    }
    /**********************************************************************/
?>
</table>
<?
//Aqui começa a segunda parte, a parte em q calcula e exibe os itens
    $sql = "SELECT ged.`id_empresa_divisao`, ged.`margem_lucro_minima`, ov.`id_cliente`, ov.`artigo_isencao`, 
            ovi.`id_orcamento_venda`, ovi.`id_produto_acabado_discriminacao`, ovi.`preco_liq_fat`, 
            ovi.`desc_cliente`, ovi.`promocao`, ovi.`queima_estoque`, ovi.`desc_extra`, ovi.`acrescimo_extra`, 
            ovi.`desc_sgd_icms`, ovi.`comissao_perc`, ovi.`preco_liq_fat_disc`, ovi.`iva`, pa.`referencia`, 
            pa.`discriminacao`, pa.`operacao`, pa.`peso_unitario`, pa.`observacao` AS observacao_produto, 
            pa.`status_top`, pa.`operacao_custo`, pa.`operacao_custo_sub`, pvi.`id_pedido_venda_item`, 
            pvi.`id_orcamento_venda_item`, pvi.`id_produto_acabado`, pvi.`id_representante`, 
            pvi.`id_funcionario`, pvi.`qtde`, pvi.`vale`, pvi.`qtde_pendente`, pvi.`qtde_faturada`, 
            pvi.`comissao_new`, pvi.`comissao_extra`, pvi.`preco_liq_final`, pvi.`prazo_entrega`, 
            pvi.`margem_lucro`, pvi.`margem_lucro_estimada`, pvi.`status` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' ORDER BY ovi.`id_orcamento_venda_item`, pa.`discriminacao` ";
    $campos_itens = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas_itens = count($campos_itens);
    //Verifica se tem pelo menos um item no pedido
    if($linhas_itens > 0) {
?>
  
<table width='90%' border='1' align='center' cellspacing='0' cellpadding='0' onmouseover='total_linhas(this)'>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='N.º do Orçamento' style='cursor:help'>
                <b>N.º&nbsp;Orc</b>
            </font>
            <?
//Nessa coluna, o rótulo será dinâmico de acordo com o Cliente ...
            if($campos[0]['id_pais'] == 31) {//Cliente Nacional ...
                $title = 'Observação do Produto';
                $descricao = 'Obs. Prod.';
            }else {//Cliente Internacional ...
                $title = 'Desconto s/ Lista';
                $descricao = 'Desc. s/ Lista';
            }
    ?>
            <font title="<?=$title;?>" style='cursor:help'>
                <br/><b><?=$descricao;?></b>
            </font>
        </td>
        <td bgcolor='#CECECE' colspan='5'>
            <b>Quantidade</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Estoque Dispon&iacute;vel' style='cursor:help'>
                <b>E.D.</b>
            </font>
            <font title='Estoque do Fornecedor / Estoque do Porto' style='cursor:help'>
                <br/><b>E Forn/E Porto</b>
            </font>
            <font title='Pe&ccedil;as por Embalagem' style='cursor:help'>
                <br/><b>Pc/Emb</b>
            </font>
            <font color='darkblue' title='Prazo de Entrega do Or&ccedil;amento' style='cursor:help'>
                <br/><b>P.Ent.Orc</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Produto</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Informa&ccedil;&otilde;es' style='cursor:help'>
                <b>Info</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Pre&ccedil;o L&iacute;quido Faturado' style='cursor:help'>
                <b>Pre&ccedil;o L.F.<br><?=$tipo_moeda;?>/P&ccedil;</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE' width='8%'>
            <font title='Desconto SGD/ICMS' style='cursor:help'>
                <b>Descontos % </b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <font title='Acr&eacute;scimo Extra' style='cursor:help'>
                <b>Acr. <br>Ext. %</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <?
                /*Este link p/ alterar representante, só é mostrado p/ os funcionários: Roberto 62, Dárcio 98 
                porque programa e Nishimura 136 ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
                    $title = 'Alterar Representante do Pedido';
            ?>
            <a href="javascript:nova_janela('alterar_representante.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'ALTERAR_REPRESENTANTE', '', '', '', '', 280, 780, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
            <?
                }else {
                    $title = 'Comiss&atilde;o '.$tipo_moeda;
                }
            ?>
                <font title='<?=$title;?>' style='cursor:help'>
                    <b>Comiss&atilde;o</b>
                </font>
            <?
                /*Este link p/ alterar representante, só é mostrado p/ os funcionários: Roberto 62, Dárcio 98 
                porque programa e Nishimura 136 ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
            ?>
            </a>
            <?
                }
            ?>
        </td>
        <td rowspan='2' bgcolor='#CECECE' width='2%'>
            <font title='Pre&ccedil;o Liquido Final em <?=$tipo_moeda;?>' style='cursor:help'>
                <b>Pre&ccedil;o L.<br>Final <?=$tipo_moeda;?></b>
                <?
                    if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                ?>
                <img src = '../../../../imagem/margem_lucro.png' title='Margem de Lucro Mínima em Lote' onclick="nova_janela('/erp/albafer/modulo/vendas/orcamentos/itens/alterar_margem_lucro.php?id_orcamento_venda=<?=$campos_itens[0]['id_orcamento_venda'];?>', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" style='cursor:help'>
                <?
                    }
                ?>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CECECE' width='2%'>
            <b>Total Lote <?=$tipo_moeda;?> <br>s/ IPI</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>TOTAL <br>IPI</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>TOTAL <br>ICMS <br>ST</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>ICMS</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>P. Min. Crise</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <font title='Quantidade Inicial' style='cursor:help'>
                <b>Ini</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Quantidade Faturada' style='cursor:help'>
                <b>Fat</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Quantidade Separada' style='cursor:help'>
                <b>Sep</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Quantidade Pendente' style='cursor:help'>
                <b>Pend</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <font title='Quantidade de Vale' style='cursor:help'>
                <b>Vale</b>
            </font>
        </td>
    </tr>
<?
        //Variáveis que serão utilizadas mais abaixo, no decorrer do Script ...
        $tx_financeira              = custos::calculo_taxa_financeira($campos_itens[0]['id_orcamento_venda']);
        $id_orcamento_venda_antigo  = '';//Variável para controle das cores no Orçamento
        $existe_artigo_isencao      = 0;
        $peso_total_geral           = 0;
        $vetor_prazos_entrega       = vendas::prazos_entrega();

        for($i = 0;  $i < $linhas_itens; $i++) {
            /*Esse controle é de extrema importância porque em casos de "Gato por Lebre", preciso pegar 
            os impostos do Gato ...

            Ex: o cliente comprou MRH-042 "Gato", mas estamos enviando o MRT-042 "Lebre ou substituto" ...*/
            $id_produto_acabado_utilizar = (!empty($campos_itens[$i]['id_produto_acabado_discriminacao'])) ? $campos_itens[$i]['id_produto_acabado_discriminacao'] : $campos_itens[$i]['id_produto_acabado'];

            //Essas variáveis serão utilizadas mais abaixo ...
            $dados_produto      = intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf_cliente, $id_cliente, $campos[0]['id_empresa'], $campos[0]['finalidade']);

/*Significa q estou vindo lá do Módulo de faturamento, então preciso estar desabilitando os comandos da
linha porque da erro de Path de JS*/
            if($veio_faturamento == 1) {
?>
    <tr class='linhanormal'>
<?
            }else {
?>
    <tr class='linhanormal' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
<?
            }
?>
        <td>
        <?
            /*Significa q estou vindo lá do Módulo de faturamento, então o q estou tentando fazer é 
            enxergar os Orçamentos dentro do Pedido, lembrando que os Pedidos agora não é só uma simples tela e
            sim é um pop-up, e lembrando que estes Pedidos já estão sendo enxergados de dentro
            da Nota Fiscal, sendo assim preciso estar mudando a URL para enxergar os Orçamentos em um outro Pop-Up*/
            if($veio_faturamento == 1) {
                $url = "javascript:nova_janela('../../../vendas/pedidos/itens/detalhes_orcamento.php?veio_faturamento=1&id_orcamento_venda=".$campos_itens[$i]['id_orcamento_venda']."', 'ORC', '', '', '', '', 440, 780, 'c', 'c', '', '', 's', 's', '', '', '')";
//Aqui significa que estou dentro do Módulo de Vendas mesmo, na tela de Pedidos
            }else {
                $url = "javascript:nova_janela('detalhes_orcamento.php?id_orcamento_venda=".$campos_itens[$i]['id_orcamento_venda']."&pop_up=1', 'ORC', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')";
            }
        ?>
            <a href="<?=$url;?>" title="Visualizar Detalhes de Orçamento" class='link'>
        <?
                if($id_orcamento_venda_antigo != $campos_itens[$i]['id_orcamento_venda']) {
                    //Aki significa que mudou para outro N. de Orçamento e vai exibir uma nova sequência desses mesmos
                    $id_orcamento_venda_antigo = $campos_itens[$i]['id_orcamento_venda'];
        ?>
                    <font color='red'>
                        <?=$campos_itens[$i]['id_orcamento_venda'];?>
                    </font>
        <?
                }else {//Ainda são os mesmos Orçamentos
                    echo $campos_itens[$i]['id_orcamento_venda'];
                }
        ?>
                </a>
                <br/>
        <?
//Nessa coluna, o rótulo será dinâmico de acordo com o Cliente ...
                if($campos[0]['id_pais'] == 31) {//Cliente Nacional ...
                    if(!empty($campos_itens[$i]['observacao_produto'])) echo "<img width='22' height='18' title='".$campos_itens[$i]['observacao_produto']."' src='../../../../imagem/olho.jpg'>";
//Aqui verifica se tem pelo menos 1 Follow Up Registrado
                    $sql = "SELECT l.login, pafu.* 
                            FROM `produtos_acabados_follow_ups` pafu 
                            INNER JOIN `funcionarios` f ON f.id_funcionario = pafu.id_funcionario 
                            INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
                            WHERE pafu.`id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' ORDER BY pafu.`data_sys` DESC ";
                    $campos2 = bancos::sql($sql);
                    if(count($campos2) > 0) {
                        $title = 'Existem '.count($campos2).' Follow-Up(s) Registrado(s) p/ este Produto Acabado';
                        $color = 'red';
                    }else {
                        $title = 'Não há nenhum Follow-Up Registrado p/ este Produto Acabado';
                        $color = '#6473D4';
                    }
                ?>
                <a href = "javascript:nova_janela('../../../producao/cadastros/produto_acabado/follow_up.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='<?=$title;?>' class='link'>
                    <font face='Verdana, Arial, Helvetica, sans-serif' color='<?=$color;?>'>
                        OBS
                    </font>
                </a>
            <?
                }else {//Cliente Internacional ...
                    $desconto_sem_lista = (1 - $campos_itens[$i]['preco_liq_final']) * 100;
                    echo number_format($desconto_sem_lista, 2, ',', '.').' %';
                }
            ?>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
<?
                if($campos_itens[$i]['status'] == 0) {//Pendência Total
/*Significa q estou vindo lá do Módulo de faturamento, então preciso estar desabilitando os comandos da
linha porque da erro de Path de JS*/
                    if($veio_faturamento == 1) {
?>
                    <br/><input type='radio' name='opt_item' value="<?=$campos_itens[$i]['id_pedido_venda_item'];?>">
<?
                    }else {
?>
                    <br/><input type='radio' name='opt_item' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')" value="<?=$campos_itens[$i]['id_pedido_venda_item'];?>" ondblclick="parent.rodape.document.form.cmd_alterar.onclick()">
<?
                    }
                }else {
?>
                    <br/><input type='hidden' name='opt_item'>
<?
                    if($campos_itens[$i]['status'] == 1) {//Pendência Parcial
                        echo '<font title="PARCIAL" color="blue">P</font>';
                    }else {//Item Concluído
                        echo '<font title="TOTAL" color="red">T</font>';
                    }
                }
?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                /*Somente os funcionários: Rivaldo '27', Agueda '32', Roberto '62', Wilson Diretor '68', Tampelini '72', 
                Dárcio '98' pq programa, Bispo '125' e Nishimura '136' que podem alterar Qtde ...*/
                $vetor_funcionarios_alterar_qtde = array(27, 32, 62, 68, 72, 98, 125, 136);
            
                if(in_array($_SESSION['id_funcionario'], $vetor_funcionarios_alterar_qtde)) {
                    if($campos_itens[$i]['status'] < 2) {
            ?>
                <a href="javascript:nova_janela('../../../../modulo/vendas/pedidos/itens/alterar_qtdes.php?id_pedido_venda=<?=$id_pedido_venda;?>&posicao=<?=($i + 1);?>&nao_verificar_sessao=1', 'FATURADO', '', '', '', '', 350, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Qtde do Item do Pedido' class='link'>
            <?
                    }
                }
                echo number_format($campos_itens[$i]['qtde'], 1, ',', '.');
            ?>
        </a>
        <?
            $peso_total_geral+= $campos_itens[$i]['peso_unitario'] * $campos_itens[$i]['qtde'];
            echo '<br><font title="Peso Total sobre o Peso" style="cursor:help">'.number_format($campos_itens[$i]['peso_unitario'] * $campos_itens[$i]['qtde'], 2, ',', '.').' kgs';
        ?>
            </font>
        </td>
        <td>
        <?
//Só aparecerá o Link do que já foi faturado, se tiver pelo menos 1 item q já está em NF
            if($campos_itens[$i]['qtde_faturada'] > 0) {
?>
            <a href="javascript:nova_janela('../../../classes/faturamento/faturado.php?id_pedido_venda_item=<?=$campos_itens[$i]['id_pedido_venda_item'];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Faturamento" class='link'>
                <?=number_format($campos_itens[$i]['qtde_faturada'], 0, '.' , '');?>
            </a>
<?
            }else {
                echo number_format($campos_itens[$i]['qtde_faturada'], 0, '.', '');
            }
//Aqui eu busco a Qtde_Devolvida do Item no que se refere à todas as NF(s) de Devolução ...
            $sql = "SELECT SUM(qtde_devolvida) qtde_devolvida 
                    FROM `nfs_itens` 
                    WHERE `id_pedido_venda_item` = '".$campos_itens[$i]['id_pedido_venda_item']."' 
                    AND `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' 
                    AND `id_nf_item_devolvida` <> '0' ";
            $campos_devolvida = bancos::sql($sql);
            if($campos_devolvida[0]['qtde_devolvida'] > 0) echo '<br><font color="red" title="Qtde Devolvida" style="cursor:help"><b>('.number_format($campos_devolvida[0]['qtde_devolvida'], 0, '.', '').' D)</b></font>';
?>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=number_format($campos_itens[$i]['qtde'] - $campos_itens[$i]['qtde_pendente'] - $campos_itens[$i]['vale'] - $campos_itens[$i]['qtde_faturada'], 0, '.', '');?>
            </font>
        </td>
        <td>
            <?=number_format($campos_itens[$i]['qtde_pendente'], 0, ',', '.');?>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=number_format($campos_itens[$i]['vale'], 0, ',', '.');?>
            </font>
        </td>
        <td>
        <?
            /*Aqui eu verifico a qtde disponível desse item em Estoque e a qtde dele em Produção*/
            $estoque_produto                = estoque_acabado::qtde_estoque($campos_itens[$i]['id_produto_acabado']);
            $qtde_disponivel                = $estoque_produto[3];
            $est_fornecedor                 = $estoque_produto[12];
            $est_porto                      = $estoque_produto[13];
        ?>
            <a href="javascript:nova_janela('../../../classes/estoque/visualizar_estoque.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
                <?=number_format($qtde_disponivel, 0, ',', '.');?>
            </a>
        <?
            echo '<br/>'.number_format($est_fornecedor, 0, ',', '.').'/'.number_format($est_porto, 0, ',', '.');

            //Traz a quantidade de peças por embalagem da embalagem principal daquele produto ...
            $sql = "SELECT pecas_por_emb 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' 
                    AND `embalagem_default` = '1' LIMIT 1 ";
            $campos_pecas_emb = bancos::sql($sql);
            if(count($campos_pecas_emb) == 1) {
                echo '<br/>'.number_format($campos_pecas_emb[0]['pecas_por_emb'], 3, ',', '.');
            }else {
                echo '<br/>0';
            }
            //Verifico qual é o Prazo do Item do Orçamento p/ Printar na Tela de Itens ...
            foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
                if($campos_itens[$i]['prazo_entrega'] == $indice) {//Se igual
                    echo '<br><font color="darkblue"><b>'.ucfirst(strtolower($prazo_entrega)).'</b></font>';
                    break;
                }
            }
        ?>
        </td>
        <td align='left'>
        <?
            //Aqui eu busco o código do PA do Cliente na Base ...
            $sql = "SELECT `cod_cliente` 
                    FROM `pas_cod_clientes` 
                    WHERE `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' 
                    AND `id_cliente` = '$id_cliente' LIMIT 1 ";
            $campos_cod_cliente = bancos::sql($sql);
            $cProdCliente       = (count($campos_cod_cliente) == 1) ? '-cProdCliente:"'.$campos_cod_cliente[0]['cod_cliente'].'"' : '';

            echo intermodular::pa_discriminacao($campos_itens[$i]['id_produto_acabado'], 0, 1, 1, $campos_itens[$i]['id_produto_acabado_discriminacao']).'"'.$cProdCliente;

            if($campos_itens[$i]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'> (TopA)</font>";
            }else if($campos_itens[$i]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'> (TopB)</font>";
            }
            echo '&nbsp;';
            //Aqui eu verifico se existe alguma promoção ...
            if($campos_itens[$i]['promocao'] == 'A') {
                $title = 'Promoção A';
                echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(PA)</b></font>";
            }else if($campos_itens[$i]['promocao'] == 'B') {
                $title = 'Promoção B';
                echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(PB)</b></font>";
            }else if($campos_itens[$i]['promocao'] == 'C') {
                $title = 'Promoção C';
                echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(PC)</b></font>";
            }else if($campos_itens[$i]['promocao'] == 1) {
                $title = 'Promoção Antiga';
                echo "<font color='#ff9900' title='$title' style='cursor:help'><b>(P)</b></font>";
            }
/*Significa q estou vindo lá do Módulo de faturamento, então preciso mudar o caminho para o acesso ao 
arquivo de Visualização de Detalhes das Vendas*/
            if($veio_faturamento == 1) {
                $caminho = '../../../vendas/orcamentos/itens/';
            }else {
                $caminho = '../../orcamentos/itens/';
            }
        ?>
        &nbsp;
        <a href="javascript:nova_janela('<?=$caminho;?>ultima_venda_cliente.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>', 'ULTIMA_VENDA', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes da Última Venda' class='link'>
            <img src = '../../../../imagem/detalhes_ultima_venda.png' title='Detalhes da Última Venda' alt='Detalhes da Última Venda' width='30' height='22' border='0'>
        </a>
        &nbsp;
        <!--Esse parâmetro de pop_up = 1, significa que essa Tela estará sendo aberta como Pop-UP-->
        <a href="javascript:nova_janela('/erp/albafer/modulo/vendas/relatorio/concorrentes/relatorio.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>&id_uf_cliente=<?=$id_uf_cliente?>&nota_sgd=<?=$nota_sgd;?>&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Relatório de Concorrentes' class='link'>
            <img src='../../../../imagem/concorrencia.jpeg' title='Relatório de Concorrentes' alt='Relatório de Concorrentes' width='16' height='16' border='0'>
        </a>
        <?
            if($campos_itens[$i]['queima_estoque'] == 'S') echo '&nbsp;<img src="../../../../imagem/queima_estoque.png" title="Excesso de Estoque" alt="Excesso de Estoque" border="0">';
        ?>
        </td>
        <td align='center'>
        <?
            if($campos_itens[$i]['operacao_custo'] == 0) {
                echo "<font title='Opera&ccedil;&atilde;o de Custo' style='cursor:help'>OC</font>=<font title='Industrialização' style='cursor:help'>I</font>";
                if($campos_itens[$i]['operacao_custo_sub'] == 0) {
                    echo "<font title='Sub-Opera&ccedil;&atilde;o Industrial' style='cursor:help'>I</font>";
                }else {
                    echo "<font title='Sub-Opera&ccedil;&atilde;o Revenda' style='cursor:help'>R</font>";
                }
            }else {
                echo "<font title='Opera&ccedil;&atilde;o de Custo' style='cursor:help'>OC</font>=<font title='Revenda' style='cursor:help'>R</font>";
            }

            if($tributar_ipi_rev == 'S') {//Marcação Realizada no Cadastro do Cliente "TRIBUTAR o PA OC=Revenda COMO INDUSTRIAL" ...
                //A cor azul representa que é uma Operação Falsa ...
                $color          = 'darkblue';
                $operacao       = ($dados_produto['operacao'] == 0) ? 'I' : 'R';
                $title_operacao = ($dados_produto['operacao'] == 0) ? 'Revenda' : 'Industrial';//A apresentação que eu faço aqui, realmente é a Oposta ...
            }else {
                //A cor vermelha representa que não é trambicagem ...
                $color          = 'red';
                $operacao       = ($dados_produto['operacao'] == 0) ? 'I' : 'R';
                $title_operacao = ($dados_produto['operacao'] == 0) ? 'Industrial' : 'Revenda';
            }
            echo '<br/>OF=<font color="'.$color.'" title="Operação Real de Faturamento => '.$title_operacao.'" style="cursor:help"><b>'.$operacao.'</b></font>';
        ?>
            <font title="Classifica&ccedil;&atilde;o Fiscal => <?=$dados_produto['classific_fiscal'];?>" style='cursor:help'>
                <br>CF=<?=$dados_produto['id_classific_fiscal'];?>
            </font>
            <font title='Origem / Situa&ccedil;&atilde;o Tribut&aacute;ria' style='cursor:help'>
                <br/>CST=<?=$dados_produto['cst'];?>
            </font>
            <font>
                <br/>CFOP=<?=$dados_produto['cfop'];?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif'>
            <?
                if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                    if($campos_itens[$i]['operacao_custo'] == 0) {//Industrial
            ?>
                        <a href="javascript:nova_janela('../../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>&tela=2&pop_up=1', 'CUSTO', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" class='link'>
            <?
                    }else {
            ?>
                        <a href="javascript:nova_janela('../../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos_itens[$i]['id_produto_acabado'];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" class='link'>
            <?
                    }
                }
                if(empty($campos_itens[$i]['preco_liq_fat_disc'])) {
                    echo number_format($campos_itens[$i]['preco_liq_fat'], 2, ',', '.');
                }else {
                    if($campos_itens[$i]['preco_liq_fat_disc']=='Orçar') {
                        echo "<font color='red'>".$campos_itens[$i]['preco_liq_fat_disc']."</font>";
                    }else {
                        echo "<font color='blue'>".$campos_itens[$i]['preco_liq_fat_disc']."</font>";
                    }
                }
            ?>
            </font>
        </td>
        <td>
            Cliente=
        <?
            echo number_format($campos_itens[$i]['desc_cliente'], 2, ',', '.');
            if($campos_itens[$i]['desc_cliente'] < 0) echo '<font color="red"><b> (Acr)</b></font>';
            echo '<br>';
        ?>
            Extra=
        <?	
            $coeficiente = (1 - $campos_itens[$i]['desc_cliente'] / 100) * (1 - $campos_itens[$i]['desc_extra'] / 100) * (1 + $campos_itens[$i]['acrescimo_extra'] / 100) * (1 - $tx_financeira / 100);
            $desconto_total 	= (1 - $coeficiente) * 100;
        ?>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' title="Desc.Total p/comissao = <?=number_format($desconto_total, 2, ',', '.');?>% - Levamos em conta a dif. do ICMS p/UF = SP x desc.ICMS/SGD e a dif.da tx.fin.p/30 ddl x tx.fin.pz.medio do ORC" style='cursor:help'>
                <?=number_format($campos_itens[$i]['desc_extra'], 2, ',', '.').'<br>';?>
            </font>
            <?if($nota_sgd == 'S') {echo 'SGD=';}else {echo 'ICMS=';}?>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=number_format($campos_itens[$i]['desc_sgd_icms'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=number_format($campos_itens[$i]['acrescimo_extra'], 2, ',', '.');?>
            </font>
        </td>
        <td>
        <?
            //O id_pais será muito útil mais abaixo ...
            $sql = "SELECT `id_pais`, `nome_fantasia` 
                    FROM `representantes` 
                    WHERE `id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            if(count($campos_representante) > 0) {
        ?>
            <a href="javascript:nova_janela('../../representante/alterar2.php?passo=1&id_representante=<?=$campos_itens[$i]['id_representante'];?>&pop_up=1', 'POP', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
        <?
                /*Se existir país no cadastro desse Representante, então representa que o mesmo é externo, 
                consequentemente apresento o seu Supervisor ...*/
                if(!is_null($campos_representante[0]['id_pais'])) {
                    //Busca do Supervisor ...
                    $sql = "SELECT r.`nome_fantasia` AS supervisor 
                            FROM `representantes_vs_supervisores` rs 
                            INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                            WHERE rs.`id_representante` = '".$campos_itens[$i]['id_representante']."' LIMIT 1 ";
                    $campos_supervisor  = bancos::sql($sql);
                    $title              = 'Supervisor => '.$campos_supervisor[0]['supervisor'];
                }else {
                    $title              = '';
                }
            ?>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' title='<?=$title;?>' style='cursor:help' <?=$color;?>>
                    <?=$campos_representante[0]['nome_fantasia'];?>
                </font>
            </a>
        <?
            }

            if($campos_itens[$i]['id_representante'] == 71 && $campos_itens[$i]['id_funcionario'] > 0) {//Se o Grupo for PME, listo qual o funcionário do Grupo que fez a Venda ...
                $sql = "SELECT SUBSTRING_INDEX(UPPER(`nome`), ' ', 1) AS nome 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '".$campos_itens[$i]['id_funcionario']."' LIMIT 1 ";
                $campos_func = bancos::sql($sql);
                echo '<br><font color="darkblue"><b>'.$campos_func[0]['nome'];
            }
            $preco_total_lote = $campos_itens[$i]['preco_liq_final'] * $campos_itens[$i]['qtde'];
            /************************************************************************************************/
            /*********************************Logística Nova Margem de Lucro*********************************/
            /************************************************************************************************/		
            //Aqui eu entro com a Margem de Lucro dentro da Tabela da Nova Margem de Lucro ...
            echo '<br><font color="brown" title="Comiss&atilde;o Extra => '.number_format($campos_itens[$i]['comissao_extra'], '2', ',', '.').'" style="cursor:help">'.number_format($campos_itens[$i]['comissao_new'] + $campos_itens[$i]['comissao_extra'], 2, ',', '.').'% ';
            echo '<br>'.$tipo_moeda.number_format(vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new'] + $campos_itens[$i]['comissao_extra']), 2, ',', '.');
            /************************************************************************************************/
        ?>
        </td>
        <td align='right'>
        <?
            echo number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.');

            /*Somente p/ estas referências TM e UL que são itens que vendemos sem Margem de Lucro
            cientes que a partir de 10/11/2015 teríamos que negociar os preços por um Margem Melhor ...

            Observação: Só enquanto o "item de Pedido de Venda" estiver com Pendência ...*/
            if((strpos($campos_itens[$i]['referencia'], 'TM') !== false || strpos($campos_itens[$i]['referencia'], 'UL') !== false) && $campos_itens[$i]['status'] < 2) {
                $calcular_preco_minimo = 'S';
            }else {
                $calcular_preco_minimo = 'N';
            }

            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro) || $calcular_preco_minimo == 'S') {
                $margem         = custos::margem_lucro($campos_itens[$i]['id_orcamento_venda_item'], $tx_financeira, $id_uf_cliente, $campos_itens[$i]['preco_liq_final']);

                if($calcular_preco_minimo == 'S') {
                    /*Aqui eu multiplico por 1.085 porque este valor equivale a uma Margem de Lucro 
                    Instantânea de 8,5% que se for zerado o Custo Bancário, teríamos uma Margem de Lucro 
                    de 30% que é o Mínimo que podemos ter mesmo sendo em época de Crise ...*/
                    $preco_minimo   = $campos_itens[$i]['preco_liq_final'] / (1 + $margem[0] / 100) * 1.085;

                    /*Se o Preço Líquido Final for menor que o P.Min.Crise, então apresento este 
                    porque realmente esta muito abaixo do que o necessário ...*/
                    if($campos_itens[$i]['preco_liq_final'] < $preco_minimo) echo '<br/><font color="red"><b>P.Min.Crise '.number_format($preco_minimo, 2, ',', '.').'</b></font>';
                }

                //Só esses usuários podem ver a Impressão das Margens de Lucro ...
                if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                    $custo_margem_lucro_zero        = $margem[2];//preco_custo_zero
                    $soma_margem_lucro_zero+=       $custo_margem_lucro_zero * $campos_itens[$i]['qtde'];
                    $soma_margem_lucro_est_zero+=   ($campos_itens[$i]['qtde'] * $campos_itens[$i]['preco_liq_final']) / (1 + $campos_itens[$i]['margem_lucro_estimada'] / 100);

                    $cor_instantanea    = ($margem[0] < 0) ? 'red' : '#E8E8E8';//$margem[0] valor real da margem
                    $cor_gravada        = ($campos_itens[$i]['margem_lucro'] < 0) ? 'red': '#E8E8E8';
                    $cor_estimada       = ($campos_itens[$i]['margem_lucro_estimada'] < 0) ? 'red': '#E8E8E8';

                    $valores                = vendas::calcular_ml_min_pa_vs_cliente($campos_itens[$i]['id_produto_acabado'], $id_cliente);
                    $rotulo_ml_min          = $valores['rotulo_ml_min'];
                    $rotulo_preco           = $valores['rotulo_preco'];
                    $margem_lucro_minima    = $valores['margem_lucro_minima'];
        ?>
                <a href="javascript:nova_janela('/erp/albafer/modulo/vendas/orcamentos/itens/alterar_margem_lucro.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Compras" class='link'>
                    <font color="<?=$cor_gravada;?>" title='Margem de Lucro M&iacute;nima' style='cursor:help'><br/>
                        <?=$rotulo_ml_min.number_format($margem_lucro_minima, 1, ',', '');?>
                    </font>
        <?	
                    if((double)strtr($margem[1], ',%', '. ') < $margem_lucro_minima) {//Se a ML do ORC < ML do Grupo ...
        ?>
                        <font color="<?=$cor_gravada;?>" title='Pre&ccedil;o Ideal' style='cursor:help'><br/>
                        <?
                            $pco_ideal = round($campos_itens[$i]['preco_liq_final'] / ((double)strtr($margem[1], ',%', '. ') / 100 + 1) * ($margem_lucro_minima / 100 + 1), 2);
                            echo $rotulo_preco.number_format($pco_ideal, 2, ',', '');
                        ?>
                        </font>
                        <img src = '../../../../imagem/bloco_negro.gif' width='5' height='5' border='0'>
        <?
                    }
        ?>
                </a>
        <?
                }
            }
        ?>
        </td>
        <td align='right'>
        <?
            echo number_format($preco_total_lote, 2, ',', '.');

            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
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
                <a href="javascript:nova_janela('/erp/albafer/modulo/vendas/orcamentos/itens/alterar_margem_lucro.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>&preco_liq_final=<?=number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.');?>&margem_lucro=<?=$margem[1];?>', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Compras' class='link'>
                    <font color="<?=$cor_instantanea;?>" id='id_ml_instantanea<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'><br/>
                        <?='ML='.$margem[1];//Valor Descritivo da Margem ...?>
                    </font>
                    <font color="<?=$cor_gravada;?>" id='id_ml_gravada<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'>
                        <?='MLG='.number_format($campos_itens[$i]['margem_lucro'], 2, ',', '.');?>
                    </font>
                    <font color="<?=$cor_estimada;?>" id='id_ml_estimada<?=$i;?>' title='Margem de Lucro Estimada' style='cursor:help'>
                        <?='MLEst='.number_format($campos_itens[$i]['margem_lucro_estimada'], 2, ',', '.');?>
                    </font>
                </a>
        <?
            }
        ?>
        </td>
        <td>
        <?
            //Se existir tem q fazer uma marcação nesse item com [*]
            if($campos_itens[$i]['artigo_isencao'] == 1) {//Verifica se o item corrente tem a lei de Artigo de Isenção
                echo '<font color="black" title="SUSPENSO IPI, CONF.ART.29, PARÁGRAFO 1, ALÍNEA A E B, LEI 10637/02" style="cursor:help"> [*] </font>';
                $existe_artigo_isencao++;
            }else {
                if($dados_produto['ipi'] > 0) {
                    echo number_format($dados_produto['ipi'], 2, ',', '.').' %';
                    echo '<br><b>'.$tipo_moeda.number_format($preco_total_lote * ($dados_produto['ipi'] / 100), 2, ',', '.').'</b>';
                }else {
                    echo 'S/IPI';
                }
            }
        ?>
        </td>
        <td>
        <?
            $calculo_impostos_item = calculos::calculo_impostos($campos_itens[$i]['id_pedido_venda_item'], $id_pedido_venda, 'PV');
            //Aqui eu verifico se existe IVA ...
            if($campos_itens[$i]['iva'] > 0) {
                echo '<font title="ICMS INTRAESTADUAL '.number_format($dados_produto['icms_intraestadual'], 2, ',', '.').' - IVA AJUSTADO '.number_format($calculo_impostos_item['iva_ajustado'] * 100, 2, ',', '.').'%" style="cursor:help">'.number_format($campos_itens[$i]['iva'], 2, ',', '.').' %</font>';

                echo '<br>ST=<font color="blue"><b>'.number_format(($calculo_impostos_item['valor_icms_st'] * 100) / $preco_total_lote, 2, ',', '.').'%</b></font>';
                echo '<br><b>'.$tipo_moeda.number_format($calculo_impostos_item['valor_icms_st'], 2, ',', '.').'</b></font>';
            }else {
                echo 'S/IVA';
            }
        ?>
        </td>
        <td>
        <?
            if($dados_produto['icms'] > 0) {
                echo number_format($dados_produto['icms'], 2, ',', '.').' %';
                if($dados_produto['reducao'] > 0) echo '<br><font color="blue"><b>RED '.number_format($dados_produto['reducao'], 2, ',', '.').'%</b></font>';
            }else {
                echo 'S/ICMS';
            }
        ?>
        </td>
        <td>
        <?
            $preco_min_crise = $campos_itens[$i]['preco_liq_final'];
            ;
        ?>
        </td>
    </tr>
<?
            $comissao_por_item_rs = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_new']);
            $total_comissoes_dos_itens_rs+= $comissao_por_item_rs;

            $comissao_extra_por_item_rs = vendas::comissao_representante_reais($preco_total_lote, $campos_itens[$i]['comissao_extra']);
            $total_comissoes_extras_dos_itens_rs+= $comissao_extra_por_item_rs;
        }//Fim do for
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='0' align='center'>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>C&Aacute;LCULO DO IMPOSTO</font>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <font color='yellow'>VALOR DO ICMS ST: </font>
            <br>R$ 
            <?
                $calculo_total_impostos = calculos::calculo_impostos(0, $id_pedido_venda, 'PV');
                
                echo number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.');
                            
                if($calculo_total_impostos['fecp'] > 0) {
                    $base_calculo_fecp  = $calculo_total_impostos['base_calculo_icms_st'];
                    $valor_fecp         = number_format(round($base_calculo_fecp * ($calculo_total_impostos['fecp'] / 100), 2), 2, '.', '');
                    $total_icms_st      = round($calculo_total_impostos['valor_icms_st'] + $valor_fecp, 2);

                    echo '<br/><font color="#49D2FF">FECP = R$ '.number_format($valor_fecp, 2, ',', '.').'&nbsp;&nbsp;-&nbsp;&nbsp;TOTAL ICMS ST = R$ '.number_format($total_icms_st, 2, ',', '.').'</font>';
                }
            ?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR DO IPI: </font>
                <br><?=$tipo_moeda.number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR TOTAL DOS PRODUTOS: </font>
                <br><?=$tipo_moeda.number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.');?></td>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR TOTAL DO PEDIDO: </font>
                <br>
                <?
                    vendas::valor_pendencia($id_pedido_venda);//Aproveito p/ atualizar a Pendência do Pedido ...
                    
                    echo $tipo_moeda.number_format($calculo_total_impostos['valor_icms_st'] + $calculo_total_impostos['valor_ipi'] + $calculo_total_impostos['valor_total_produtos'], 2, ',', '.');
                    //Sempre que carregar essa tela guarda esse valor Total do Pedido ...
                    $sql = "UPDATE `pedidos_vendas` SET `valor_ped` = '".($calculo_total_impostos['valor_icms_st'] + $calculo_total_impostos['valor_ipi'] + $calculo_total_impostos['valor_total_produtos'])."' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
                    bancos::sql($sql);
                ?>
            </font>
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <font color='yellow'>
                Taxa Financeira:
            </font>
            <?=number_format($tx_financeira, 2, ',', '.');?>
            <font color='yellow'>
                <br/>Dólar do Dia:
            </font>
            <?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?>
        </td>
        <td colspan='9'>
            <font color='yellow'>
                Comissão Média %:
            </font>
            <?
//Aqui nessa parte do cálculo eu pego a comissão média e divido pela qtde de Itens do Pedido de Venda 
//Esse desvio para não dar erro de divisão por Zero, Dárcio
            if($calculo_total_impostos['valor_total_produtos'] > 0) {
                $comissao_media         = round(($total_comissoes_dos_itens_rs / $calculo_total_impostos['valor_total_produtos']) * 100, 2);
                $comissao_media_extra   = round(($total_comissoes_extras_dos_itens_rs / $calculo_total_impostos['valor_total_produtos']) * 100, 2);
            }else {
                $comissao_media         = round($total_comissoes_dos_itens_rs * 100, 2);
                $comissao_media_extra   = round($total_comissoes_extras_dos_itens_rs * 100, 2);
            }
            echo '<font title="Comiss&atilde;o = '.number_format($comissao_media, 2, ',', '.').'% + Extra = '.number_format($comissao_media_extra, 2, ',', '.').'%" style="cursor:help">'.number_format($comissao_media + $comissao_media_extra, 2, ',', '.');
            /*****************************************************************************************/
            //Enquanto o Pedido não estiver Liberado, então eu guardo a Comissão Média do Pedido de Venda ...
            if($liberado == 0) {
                $sql = "UPDATE `pedidos_vendas` SET `comissao_media` = '$comissao_media', `comissao_media_extra` = '$comissao_media_extra'  WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
                bancos::sql($sql);
            }
            /*****************************************************************************************/

            if(!empty($cod_suframa)) { //aqui pego se tem suframa
                if($tipo_suframa == 1) {//Área de Livre ...
                    echo "Desconto de ICMS = 7 % <font color='yellow'>(A ser concedido na Emissão da Nota Fiscal)<br></font></font></font>";
                }else if($tipo_suframa == 2) {//Zona Franca de Man...
                    echo "Desconto de PIS + Cofins = ".number_format((genericas::variavel(20)+genericas::variavel(21)), 2, ',', '.')." %  e ICMS = 7 % <font color='yellow'>(A ser concedido na Emissão da Nota Fiscal)<br></font></font></font>";
                }
            }
            if($existe_artigo_isencao > 0) {//Só printo se existir Artigo de Isenção ...
                echo '<font color="black"> [*] -> </font> SUSPENSO IPI, CONF.ART.29, PARÁGRAFO 1, ALÍNEA A E B, LEI 10637/02.<br>';
            }
            if(!empty($trading)) { //aqui printa se existir trading para este cliente
                echo 'COMERCIAL EXPORTADOR (TRADING).';
//Nova Regra implantada no dia 18/02/2008 de acordo com o Roberto q disse q não está muito bem (rs) ...
                $data_atual = date('Y-m-d');
                if($data_atual >= '2008-02-18') echo '<br>M.Lucro descontado ICMS de SP + PIS + Cofins';
            }
        ?>
        </td>
        <td colspan='3' align="right">
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                Peso Total:
            </font>
        </td>
        <td colspan='2' align='center'>
            <a href="javascript:nova_janela('http://www2.correios.com.br/sistemas/precosPrazos/', 'CORREIOS', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Consultar Sedex (Correios)" class='link'>
                <font color='#000000'>
                    <?=number_format($peso_total_geral, 3, ',', '.').' (Kg)';?>
                </font>
            </a>
        </td>
        <td colspan='3' align="right">
        <?
            if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                if($soma_margem_lucro_zero == 0 || empty($soma_margem_lucro_zero) || (integer)($soma_margem_lucro_zero) == 0) $soma_margem_lucro_zero = 1;
                $margem_lucro_media = ((($calculo_total_impostos['valor_total_produtos'] / round($soma_margem_lucro_zero, 1)) - 1) * 100);
                echo "<font color='#83B2E3'>M.L.M=".number_format($margem_lucro_media, 1, ',', '.')." %</font>";
                /*****************************************************************************************/
                /*Enquanto o Pedido de Vendas não estiver liberado, então o Sistema irá ficar atualizando o 
                Margem de Lucro Média.
                    * 
                Existe uma inconsistência entre a MLMG e a MLG de cada item do Pedido.
                Esta MLMG é calculada quando damos inserção / alteração nos itens 
                do Pedido. A MLG de cada Item do Pedido, é herdada do Orçamento, pois 
                a Comissão é baseada nela ...*/
                if($liberado == 0) {
                    $sql = "UPDATE `pedidos_vendas` SET `mg_l_m_g` = '$margem_lucro_media' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
                    bancos::sql($sql);
                    echo "<br><font color='#83B2E3'>M.L.M.G=".number_format($margem_lucro_media, 1, ',', '.')." %</font>";
                }else {//Já está liberado, apenas lê do Banco de Dados ...
                    echo "<br><font color='#83B2E3'>M.L.M.G=".number_format($mg_l_m_g, 1, ',', '.')." %</font>";
                }
                //Aqui guarda a ML Estimada no Pedido ...
                $sql = "UPDATE `pedidos_vendas` SET `ml_est_m` = '".round(($calculo_total_impostos['valor_total_produtos'] / $soma_margem_lucro_est_zero - 1) * 100, 1)."' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
                bancos::sql($sql);
                echo "<br><font color='#83B2E3'>M.L.Est.M=".number_format(($calculo_total_impostos['valor_total_produtos'] / $soma_margem_lucro_est_zero - 1) * 100, 1, ',', '.')." %</font>";
                /*****************************************************************************************/
            }
        ?>
        </td>
    </tr>
    <tr>
        <td colspan='20'>
            <br/>
            <font face='verdana, arial, helvetica, sans-serif' size='-1'>Em pedidos especiais observar: Quantidade de 10% para mais/menos; Pedido e desenho assinado pelo cliente, pois não aceitamos o cancelamento.</font>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='20'>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
</table>
<!--Esses 2 hiddens aki foi uma cópia de compras, por enquanto vou deixar aqui-->
<input type='hidden' name='opt_item'>
<input type='hidden' name='opt_item_principal'>
<!-- ******************************************** -->
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda?>'>
<!--Até então serve somente para armazenar o valor das mensagens-->
<input type='hidden' name='valor'>
<!-- ******************************************** -->
</form>
</body>
</html>
<?
        if(!empty($valor)) {
?>
            <Script Language = 'JavaScript'>
                alert('<?=$mensagem[$valor];?>')
            </Script>
<?
        }
    }else {
?>
<html>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="#FF0000">
                <b>Pedido
                <font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="blue"><?=$id_pedido_venda;?></font>
                n&atilde;o cont&eacute;m itens cadastrado.</b>
            </font>
        </td>
    </tr>
</table>
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda?>'>
</form>
</body>
</html>
<?
        if(!empty($valor)) {
?>
            <Script Language = 'JavaScript'>
                alert('<?=$mensagem[$valor];?>')
            </Script>
<?
        }
        //Aqui eu zero o valor de pedido devido já não conter mais nenhum Item ...
        $sql = "UPDATE `pedidos_vendas` SET `valor_ped` = '0.00' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        bancos::sql($sql);
    }
?>