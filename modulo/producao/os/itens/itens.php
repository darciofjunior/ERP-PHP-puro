<?
require('../../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require '../../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/producao.php');
segurancas::geral('/erp/albafer/modulo/producao/os/incluir.php', '../../../../');

$mensagem[1] = 'NENHUM ITEM DE O.S. PODE SER EXCLUÍDO !\nESTÁ OS JÁ FOI IMPORTADA P/ PEDIDO !';
$mensagem[2] = 'ESTE ITEM DE O.S. PODE SER EXCLUÍDO PORQUE ESTÁ IMPORTADO EM NOTA FISCAL !';
$mensagem[3] = 'ITEM (SAÍDA DE O.S) EXCLUÍDO COM SUCESSO !';
$mensagem[4] = 'ITEM (ENTRADA DE O.S) EXCLUÍDO COM SUCESSO !';

function mudar_status_import($id_op) {
    $sql = "SELECT `id_op` 
            FROM `oss_itens` 
            WHERE `id_op` = '$id_op' LIMIT 1 ";
    $campos = bancos::sql($sql);
//Significa que essa OP já não está em nenhuma outra OS, então eu posso mudar o status_import da OP p/ 0 de novo
    if(count($campos) == 0) {
        $sql = "UPDATE `ops` SET `status_import` = '0' WHERE `id_op` = '$id_op' LIMIT 1 ";
        bancos::sql($sql);
    }
}

if($passo == 1) {//Exclusão dos Itens da OS
    $sql = "SELECT `id_os`, `id_op` 
            FROM `oss_itens` 
            WHERE `id_os_item` = '$_GET[id_os_item]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_os = $campos[0]['id_os']; //pego para dá reload na pagina
    $id_op = $campos[0]['id_op']; //pego para fazer controle de status_import da OP na function + abaixo
    
    if(empty($_GET['excluir_entrada'])) {//Caminho de Pedido "Saída" ...
        /****************************************Controle**********************************************/
        //Se a OS já estiver Importada p/ Pedido, de jeito maneira que eu posso manipular os Itens da OS
        $sql = "SELECT `id_pedido` 
                FROM `oss` 
                WHERE `id_os` = '$id_os' LIMIT 1 ";
        $campos_pedido = bancos::sql($sql);
        $id_pedido      = $campos_pedido[0]['id_pedido'];
        if($id_pedido != 0) {//Essa O.S. já foi importada para Pedido, sendo assim não posso excluir nenhum Item ...
            $valor = 1;
        }else {//Essa O.S. ainda está em aberto, então posso excluir os Itens dela normalmente ...
        /**********************************************************************************************/
            $sql = "DELETE FROM `oss_itens` WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
            bancos::sql($sql);
            mudar_status_import($id_op);
            $valor = 3;
        }
    }else {//Caminho de Nota Fiscal "Entrada" ... 
        /****************************************Controle**********************************************/
        //Verifico se o "id_os_item" passado por parâmetro está importado na NF de Entrada ...
        $sql = "SELECT `id_os_item` 
                FROM `oss_itens` 
                WHERE `id_os_item` = '$_GET[id_os_item]' 
                AND `id_nfe_historico` IS NOT NULL LIMIT 1 ";
        $campos_nfe = bancos::sql($sql);
        if(count($campos_nfe) == 1) {//Esse item de O.S. esta importado em NFe, sendo assim não posso excluí-lo ...
            $valor = 2;
        }else {//Essa O.S. ainda está em aberto, então posso excluir os Itens dela normalmente ...
        /**********************************************************************************************/
            $sql = "DELETE FROM `oss_itens` WHERE `id_os_item` = '$_GET[id_os_item]' LIMIT 1 ";
            bancos::sql($sql);
            mudar_status_import($id_op);
            $valor = 4;
        }
    }
?>
    <Script Language = 'JavaScript'>
        //Esse controle abaixo é porque nem sempre esse arquivo é aberto como sendo um Frame ...
        if(typeof(parent.itens) == 'object') {//Aberto como Frame ...
            parent.itens.document.location = 'itens.php?id_os=<?=$id_os;?>&perguntar_uma_vez=1&valor=<?=$valor;?>'
            parent.rodape.document.form.submit()
        }else {//Aberto de modo Normal ...
            document.location = 'itens.php?id_os=<?=$id_os;?>&id_nfe=<?=$_GET['id_nfe'];?>&acesso_sem_link=S&perguntar_uma_vez=1&valor=<?=$valor;?>'
        }
    </Script>
<?
}else {
    /*****************************************************************************************************************/
    /************************Controle com o Filtro quando a consulta for por N.º NF de Entrada ************************/
    /*****************************************************************************************************************/
    if(!empty($_GET['id_nfe'])) {
        /*Aqui eu verifico se existe pelo menos um 1 item de OSS que está vinculada a NFe passada por 
        parâmetro ...*/
        $sql = "SELECT DISTINCT(oi.`id_nfe`), e.`nomefantasia`, f.`id_fornecedor`, f.`id_pais`, 
                f.`razaosocial`, f.`nf_minimo_tt` AS nf_minimo_tt_cad, nfe.`id_empresa`, nfe.`num_nota`, 
                DATE_FORMAT(nfe.`data_emissao`, '%d/%m/%Y') AS data_emissao 
                FROM `oss_itens` oi 
                INNER JOIN `nfe` ON nfe.`id_nfe` = oi.`id_nfe` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
                WHERE oi.`id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            echo "<Script type='text/javascript'>window.location = 'consultar.php?valor=1'</Script>";
            exit;
        }
    }else {
    /*****************************************************************************************************************/
    //Busca o nome do Fornecedor com + detalhes alguns detalhes de dados do fornecedor
        $sql = "SELECT e.`nomefantasia`, f.`id_fornecedor`, f.`id_pais`, f.`razaosocial`, 
                f.`nf_minimo_tt` AS nf_minimo_tt_cad, oss.* 
                FROM `oss` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
                LEFT JOIN `empresas` e ON e.`id_empresa` = oss.`id_empresa` 
                WHERE oss.`id_os` = '$id_os' LIMIT 1 ";
        $campos = bancos::sql($sql);
    }
    $id_fornecedor  = $campos[0]['id_fornecedor'];
    $id_pedido      = $campos[0]['id_pedido'];
    $nome_empresa   = $campos[0]['nomefantasia'];
    $id_pais        = $campos[0]['id_pais'];
    $razao_social   = $campos[0]['razaosocial'];
/********************************Controle para apresentação dos Dados********************************/
/*Verifico se a OS já foi importada para Pedido p/ saber de quais locais que eu vou buscar 
o lote mínimo e a nf mínima*/
    if($id_pedido == 0) {//Essa OS ainda não foi importada, então busco do Cadastro do Fornecedor
        $nf_minimo_tt           = $campos[0]['nf_minimo_tt_cad'];//Uso para comparar no JavaScript ...
    }else {//Essa OS já foi importada sendo assim, eu busco os valores da própria OS
        $nf_minimo_tt           = $campos[0]['nf_minimo_tt'];//Uso para comparar no JavaScript ...
    }
    
/****************************************************************************************************/
    /*Essa variável $parametro_filtro guardará o parâmetro de filtragem que foi feito pelo usuário, eu crio essa variável exatamente nesse 
    ponto porque mais pra baixo faço uma Query dos itens e por está trazer com paginação, a variável parâmetro que fica na sessão passa a 
    ser substituída perdendo assim o filtro que foi feito pelo usuário ...*/
    $parametro_filtro = $parametro;
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = 'tabela_itens_radio.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></script>
<Script Language = 'JavaScript'>
function excluir_entrada(id_os_item) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE ITEM (ENTRADA DE OS) ?')
    if(resposta == true) {
        //Esse controle abaixo é porque nem sempre esse arquivo é aberto como sendo um Frame ...
        if(typeof(parent.itens) == 'object') {//Aberto como Frame ...
            parent.itens.document.location = 'itens.php?passo=1&excluir_entrada=S&id_os_item='+id_os_item
        }else {//Aberto de modo Normal ...
            document.location = 'itens.php?passo=1&excluir_entrada=S&id_os_item='+id_os_item+'&id_nfe=<?=$_GET['id_nfe'];?>&acesso_sem_link=S'
        }
    }
}

function igualar(indice) {
    var controle = 0, existe = 0, liberado = '', codigo = '', cont = 0
    var elemento = '', objeto = ''
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'radio') cont ++
    }
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'hidden' && document.form.elements[i].name == 'opt_item_os') existe ++
    }
    if(cont > 1) {
        elemento = document.form.opt_item_os[indice].value
        objeto = document.form.opt_item_os[indice]
    }else {
        if(existe == 0) {
            elemento = document.form.opt_item_os.value
            objeto = document.form.opt_item_os
        }else {
            elemento = document.form.opt_item_os[indice].value
            objeto = document.form.opt_item_os[indice]
        }
    }
    if(objeto.type == 'radio') {
        for(i = 0; i < elemento.length; i++) {
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

function limpar_radio() {
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'radio') {
            document.form.elements[i].checked = false
        }
    }
}

function detalhes_pedidos(id_pedido) {
    nova_janela('../../../compras/pedidos/itens/itens.php?id_pedido='+id_pedido+'&pop_up=1', 'DETALHES_PEDIDO', 'F')
}

function detalhes_nota_fiscal(id_nfe) {
    nova_janela('../../../compras/pedidos/nota_entrada/itens/itens.php?id_nfe='+id_nfe+'&pop_up=1', 'DETALHES_NF', 'F')
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' align='center' cellspacing='0' cellpadding='0'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            <font size='3'>
<?
    /*****************************************************************************************************************/
    /************************Controle com o Filtro quando a consulta for por N. NF de Entrada ************************/
    /*****************************************************************************************************************/
    if(!empty($_GET['id_nfe'])) {
?>
        NF de Entrada N.º <?=$campos[0]['num_nota'];?> - <?=$nome_empresa;?> - <font size='2'>Dt Emissão: <?=$campos[0]['data_emissao'];?></font>
<?
    /*****************************************************************************************************************/
    }else {
?>
        O.S. N.º <?=$id_os;?> - <?=$nome_empresa;?> - <font size='2'>Dt Saída: <?=data::datetodata($campos[0]['data_saida'], '/');?></font>
<?
    }
?>
            </font>
            <?
                //Verifico se essa OS está importada em NF ...
                $sql = "SELECT `id_nf_outra` 
                        FROM `oss` 
                        WHERE `id_os` = '$id_os' LIMIT 1 ";
                $campos_nf_outra = bancos::sql($sql);
                if(count($campos_nf_outra[0]['id_nf_outra']) == 1) {
                    echo ' - NF DE SAÍDA ';
            ?>
            <a href="javascript:nova_janela('../../../faturamento/outras_nfs/itens/detalhes_nota_fiscal.php?id_nf_outra=<?=$campos_nf_outra[0]['id_nf_outra'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes' class='link'>
                <font color='#CCCCCC'>
                    <?=faturamentos::buscar_numero_nf($campos_nf_outra[0]['id_nf_outra'], 'O');?>
                </font>
            </a>
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' style='cursor:pointer'>
        <td colspan='15'>
            <a href = "javascript:nova_janela('../../../classes/fornecedor/alterar.php?passo=1&id_fornecedor=<?=$id_fornecedor;?>&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <font color='#CCCCCC'>
                    Fornecedor: 
                </font>
                <font color='#FFFFFF'>
                    <?=$razao_social;?>
                </font>
                <img src="../../../../imagem/propriedades.png" title="Detalhes de Cliente" alt="Detalhes de Cliente" style="cursor:pointer" border="0">
            </a>
        </td>
    </tr>
</table>
<?
//Aqui começa a segunda parte em q exibe os itens da OS
/*****************************************************************************************************************/
/************************Controle com o Filtro quando a consulta for por N. NF de Entrada ************************/
/*****************************************************************************************************************/
    if(!empty($_GET['id_nfe'])) {
        /*Aqui eu busco todos os itens da OS que são saída, isso só é possível através dos itens de Entrada, afinal eu só posso ter uma Entrada 
        se anteriormente existiu uma Saída e somente esses de Entrada que possuem vínculo ao campo $id_nfe que é a própria NF de Entrada 
        que foi encontrada através do Número de NF digitada pelo usuário ...*/
        $sql = "SELECT `id_os_item_saida` 
                FROM `oss_itens` 
                WHERE `id_nfe` = '".$campos[0]['id_nfe']."' ";
        $campos_os_item_saida   = bancos::sql($sql);
        $linhas_os_item_saida   = count($campos_os_item_saida);
        $id_os_item_saida       = '';//Declarando a variável ...

        for($i = 0; $i < $linhas_os_item_saida; $i++) $id_os_item_saida.= $campos_os_item_saida[$i]['id_os_item_saida'].', ';
        $id_os_item_saida = substr($id_os_item_saida, 0, strlen($id_os_item_saida) - 2);

        //Aqui traz os itens da OS(s) encontradas acima que são de Saída "não possuem Entrada" ...
        $sql = "SELECT * 
                FROM `oss_itens` 
                WHERE `id_os_item` IN ($id_os_item_saida) ORDER BY `id_os_item` ";
        /*****************************************************************************************************************/
    }else {
        //Aqui traz os itens da OS(s) específica passada por parâmetro que são de Saída "não possuem Entrada" ...
        $sql = "SELECT * 
                FROM `oss_itens` 
                WHERE `id_os` = '$id_os' 
                AND `qtde_saida` > '0' ORDER BY `id_os_item` ";
    }
    $campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas > 0) {//Verifica se tem pelo menos um item na OS ...
?>
<table width='90%' border='1' align='center' cellspacing='0' cellpadding='0' onmouseover='total_linhas(this)'>
    <tr>
        <td colspan='18'></td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Item</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>N.º OS</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>N.º OP / Qtde Entrada</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Saída Qtde</b>
        </td>
        <td colspan='3' bgcolor='#CECECE'>
            <b>Entrada</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Dif.<br/>Qtde</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>EC</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Produto</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Matéria Prima</b>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <b>Total de</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>CTT / USI</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Preço<br/>Unit. R$</b>
        </td>
        <td rowspan='2' bgcolor='#CECECE'>
            <b>Total R$</b>
        </td>
        <td colspan='2' bgcolor='#CECECE'>
            <b>Dureza</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>Qtde</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>NF</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Saída</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Ent.</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Fornecedor</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Interno</b>
        </td>
    </tr>
<?
        //Nesse vetor eu vou armazenar todos os CTT(s) que estão atrelados a esses PI(s) da OS ...
        $vetor_ctts = array();
        $valor_total_os = 0;
        /*****************************Controle p/ Alert*****************************/
        $id_ctt_antigo = '';//Variável para controle dos CTT(s) ...
        $id_preco_ctt_antigo = '';//Variável para controle dos CTT(s) ...
        /***************************************************************************/
        for($i = 0; $i < $linhas; $i++) {
            $url = "javascript:nova_janela('../../ops/alterar.php?passo=1&id_op=".$campos[$i]['id_op']."&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')";
?>
    <tr class='linhanormal' onclick="options('form', 'opt_item_os', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
        <?
                //Se o Item estiver "Em Aberto" ou Parcialmente Importado em Nota Fiscal, ainda posso alterar dados ...
                if($campos[$i]['status'] == 0 || $campos[$i]['status'] == 1) {
        ?>
            <input type='radio' name='opt_item_os' value="<?=$campos[$i]['id_os_item'];?>" onclick="options('form', 'opt_item_os', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')">
        <?
                }else {
        ?>
                <font color='red'>
                    TOTAL
                </font>
                <input type='hidden' name='opt_item_os'>
        <?
                }
                
                if(!empty($campos[$i]['id_item_pedido'])) echo '<br/><b>EM PEDIDO</b>';
        ?>
        </td>
        <td>
            <?=$campos[$i]['id_os'];?>
        </td>                
        <td>
            <a href="<?=$url;?>" title='Detalhes de OP' style='cursor:help' class='link'>
            <?
                $vetor_dados_op = intermodular::dados_op($campos[$i]['id_op']);
                
                echo $campos[$i]['id_op'].$vetor_dados_op['posicao_op'];
            ?>
            </a>
            /
            <br/>
            <?
                $sql = "SELECT SUM(`qtde_baixa`) AS total_entradas_registradas 
                        FROM `baixas_manipulacoes_pas` bmp 
                        INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_baixa_manipulacao_pa` = bmp.`id_baixa_manipulacao_pa` AND bop.`id_op` = '".$campos[$i]['id_op']."' 
                        WHERE bmp.`acao` = 'E' ";
                $campos_total_entradas = bancos::sql($sql);
                echo number_format($campos_total_entradas[0]['total_entradas_registradas'], 2, ',', '.');
            ?>
            &nbsp;
            <?
                //Já busco de antecipado o PA da OP agora através do id_op que está na OS p/ ver o seu EC Atrelado ...
                $sql = "SELECT `id_produto_acabado` 
                        FROM `ops` 
                        WHERE `id_op` = '".$campos[$i]['id_op']."' LIMIT 1 ";
                $campos_pa = bancos::sql($sql);
            ?>
            <a href="javascript:nova_janela('../../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos_pa[0]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS_ULTIMOS_6_MESES', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <img src = '../../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
        </td>
        <td>
            <?=$campos[$i]['qtde_saida'];?>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
        <td>
        <?
            //Aqui eu busco o total de Entradas do id_os_item que saiu ...
            $sql = "SELECT SUM(`qtde_entrada`) AS total_entradas 
                    FROM `oss_itens` 
                    WHERE `id_os_item_saida` = '".$campos[$i]['id_os_item']."' ";
            $total_entradas = bancos::sql($sql);
            
//Comparação entre as 2 Quantidades - Faço controle de Cores ...
            if((($total_entradas[0]['total_entradas'] / $campos[$i]['qtde_saida']) > 1.01) || (($total_entradas[0]['total_entradas'] / $campos[$i]['qtde_saida']) < 0.99)) {
                $color = 'red';
            }else {
                $color = 'blue';
            }
            $resultado = $total_entradas[0]['total_entradas'] - $campos[$i]['qtde_saida'];
            echo "<font color=$color>".$resultado."</font>";
        ?>
        </td>
        <td>
        <?
            $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_pa[0]['id_produto_acabado']);
            $font_ec_atrelado       = ($retorno_pas_atrelados['total_ec_pas_atrelados'] < 0) ? 'red' : 'black';
            echo '<br/><font color="'.$font_ec_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help"> '.number_format($retorno_pas_atrelados['total_ec_pas_atrelados'], 0, '', '.').'</font>';
        ?>
        </td>
        <td align='left'>
        <?
            echo intermodular::pa_discriminacao($campos_pa[0]['id_produto_acabado']);
//Aki eu printo se é Retrabalho na Frente da Discriminação ...
            if($campos[$i]['retrabalho'] == 1) echo ' <font color="red"><b>RETRABALHO</b></font>';
        ?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT `discriminacao` 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo_mat_prima']."' LIMIT 1 ";
            $campos_discriminacao = bancos::sql($sql);
            if(!empty($campos_discriminacao[0]['discriminacao'])) {
                echo $campos_discriminacao[0]['discriminacao'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['peso_total_saida'], 3, ',', '.');?>
        </td>
        <td>
            &nbsp;
        </td>
        <td align='left'>
        <?
//Aqui eu verifico se este Item de Pedido, já teve andamento em Nota Fiscal ...
            $sql = "SELECT `id_nfe_historico` 
                    FROM `nfe_historicos` 
                    WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' LIMIT 1 ";
            $campos_item_nfe = bancos::sql($sql);
//Enquanto este Item não estiver em NF e o item não tiver com a Marcação de Lote Mínimo, eu apresento link para o usuário poder trocar o CTT do Item da OS ...
            if(count($campos_item_nfe) == 0 && $campos[$i]['cobrar_lote_minimo'] == 'N') {//Fizemos ignorar o link no Lote Mínimo, pq seria muito complexo controlarmos isso ...
//Eu só mostro esse link p/ poder alterar o PI nos usuários do Rivaldo 27, Roberto 62, Dárcio 98 e Netto 147 pq programam ...
                if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
        ?>
                <a href="javascript:nova_janela('alterar_produto_insumo.php?id_os_item=<?=$campos[$i]['id_os_item'];?>', 'POP', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Produto Insumo' class='link'>
        <?
                }
            }
            
            $sql = "SELECT pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo_ctt']."' LIMIT 1 ";
            $campos_dados = bancos::sql($sql);
            if(!empty($campos_dados[0]['discriminacao'])) {
                echo $campos_dados[0]['sigla'].' - '.$campos_dados[0]['discriminacao'];
            }else {
                echo '&nbsp;';
            }

//Verifico se esse PI tem algum CTT, atrelado ...
            $sql = "SELECT ctts.`id_ctt`, ctts.`codigo` AS dados_ctt 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `ctts` on ctts.`id_ctt` = pi.`id_ctt` 
                    WHERE pi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo_ctt']."' LIMIT 1 ";
            $campos2 = bancos::sql($sql);
            if(count($campos2) == 1) {//Se encontrar CTT atrelado ao PI, então eu printo este ...
                echo ' / <font color="darkblue">'.$campos2[0]['dados_ctt'].'</font>';
//Insiro nesse $vetor_ctts o id_ctt corrente ...
                array_push($vetor_ctts, $campos2[0]['id_ctt']);
/********************************Controle exibição do Alert********************************/
//Aki significa que mudou para outro CTT ...
                if($id_ctt_antigo != $campos2[0]['id_ctt']) {
/*Significa que é a Primeira vez que eu estou nesse CTT, sendo assim eu só vou armazenar 
os valores nesta variável ...*/
                    $id_ctt_antigo          = $campos2[0]['id_ctt'];
                    $id_preco_ctt_antigo    = $campos[$i]['preco_pi'];
//Aki significa que eu estou no mesmo CTT ainda
                }else {
//Aki eu verifico se o mesmo CTT possui preços diferentes ...
                    if($id_preco_ctt_antigo != $campos[$i]['preco_pi']) {
        ?>
                <Script Language = 'JavaScript'>
                        alert('O '+'<?=$campos2[0]["dados_ctt"]?>'+' POSSUI PREÇO(S) DIFERENTE(S) !')
                </Script>
        <?
                    }
                }
/******************************************************************************************/
            }
        ?>
            </a>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_pi'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            if($campos_dados[0]['sigla'] == 'UN') {//Se a unidade do CTT = "Unidade", então utilizo o campo Qtde ... 
                $peso_qtde_total_utilizar = $campos[$i]['qtde_saida'];
            }else {//Se a unidade do CTT <> "Unidade", então utilizo o campo Peso Total  ... 
                $peso_qtde_total_utilizar = $campos[$i]['peso_total_saida'];
            }
//Aki eu verifico se existe a marcação de Lote Mínimo p/ o Item ...
            if($campos[$i]['cobrar_lote_minimo'] == 'S') {
//Aqui eu verifico se o Cálculo do Peso pelo Preço é menor do que o Lote Mínimo do Custo ...
                if($peso_qtde_total_utilizar * $campos[$i]['preco_pi'] < $campos[$i]['lote_minimo_custo_tt']) {
                    $valor_total_saida_os+= $campos[$i]['lote_minimo_custo_tt'];
                    echo number_format($campos[$i]['lote_minimo_custo_tt'], 2, ',', '.');
                }else {
                    $valor_item_saida_os = round($peso_qtde_total_utilizar * $campos[$i]['preco_pi'], 2);
                    $valor_total_saida_os+= $valor_item_saida_os;
                    echo number_format($valor_item_saida_os, 2, ',', '.');
                }
                echo '<font color="brown" title="Lote Mínimo" style="cursor:help"><b> (L. Mín)</b></font>';
            }else {//Não existe marcação, sendo assim faço o cálculo normal ...
                $valor_item_saida_os = round($peso_qtde_total_utilizar * $campos[$i]['preco_pi'], 2);
                $valor_total_saida_os+= $valor_item_saida_os;
                echo number_format($valor_item_saida_os, 2, ',', '.');
            }
        ?>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
        <?
            if(empty($campos[$i]['dureza_interna'])) {
                echo '&nbsp;';
            }else {
                echo $campos[$i]['dureza_interna'];
            }
        ?>
        </td>
    </tr>
<?
            //Aqui eu busco todas as Entradas do determinado id_os_item do Loop ...
            $sql = "SELECT * 
                    FROM `oss_itens` 
                    WHERE `id_os_item_saida` = '".$campos[$i]['id_os_item']."' ";
            $campos_itens_entradas = bancos::sql($sql);
            $linhas_itens_entradas = count($campos_itens_entradas);
            for($j = 0; $j < $linhas_itens_entradas; $j++) {

?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC' colspan='4' align='right'>
            <font color='red'>
                <b>ENTRADA </b>
            </font>
            <?
                //Se o Item estiver "Em Aberto" ou Parcialmente Importado em Nota Fiscal, ainda posso alterar dados ...
                if($campos_itens_entradas[$j]['status'] == 0 || $campos_itens_entradas[$j]['status'] == 1) {
            ?>
                    <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Entrada' alt='Excluir Entrada' onclick="excluir_entrada('<?=$campos_itens_entradas[$j]['id_os_item'];?>')">
            <?
                }else {
                    echo '<b>EM NF</b>';
                }
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td>
                <?=$campos_itens_entradas[$j]['qtde_entrada'];?>
        </td>
        <td>
        <?
                if(!empty($campos_itens_entradas[$j]['id_nfe'])) {
                    //Aqui eu busco o N.º da NF de Entrada ...
                    $sql = "SELECT `num_nota` 
                            FROM `nfe` 
                            WHERE `id_nfe` = '".$campos_itens_entradas[$j]['id_nfe']."' LIMIT 1 ";
                    $campos_nfe = bancos::sql($sql);
        ?>
            <a href="javascript:nova_janela('../../../compras/pedidos/nota_entrada/itens/itens.php?id_nfe=<?=$campos_itens_entradas[$j]['id_nfe'];?>&pop_up=1', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Nota Fiscal' class='link'>
                <?=$campos_nfe[0]['num_nota'];?>
            </a>
        <?
                }
        ?>
        </td>
        <td>
        <?
                if(!empty($campos_itens_entradas[$j]['id_nfe'])) echo data::datetodata($campos_itens_entradas[$j]['data_entrada'], '/');
        ?>
        </td>
        <td bgcolor='#CCCCCC' colspan='5'>
            &nbsp;
        </td>
        <td>
                <?=number_format($campos_itens_entradas[$j]['peso_total_entrada'], 2, ',', '.');?>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC' align='right'>
            <?=number_format($campos[$i]['preco_pi'], 2, ',', '.');?>
        </td>
        <td bgcolor='#CCCCCC' align='right'>
        <?
            $sql = "SELECT pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`id_produto_insumo` = '".$campos_itens_entradas[$j]['id_produto_insumo_ctt']."' LIMIT 1 ";
            $campos_dados = bancos::sql($sql);
            
            if($campos_dados[0]['sigla'] == 'UN') {//Se a unidade do CTT = "Unidade", então utilizo o campo Qtde ... 
                $peso_qtde_total_utilizar = $campos_itens_entradas[$j]['qtde_entrada'];
            }else {//Se a unidade do CTT <> "Unidade", então utilizo o campo Peso Total  ... 
                $peso_qtde_total_utilizar = $campos_itens_entradas[$j]['peso_total_entrada'];
            }
//Aki eu verifico se existe a marcação de Lote Mínimo p/ o Item ...
            if($campos_itens_entradas[$j]['cobrar_lote_minimo'] == 'S') {
//Aqui eu verifico se o Cálculo do Peso pelo Preço é menor do que o Lote Mínimo do Custo ...
                if($peso_qtde_total_utilizar * $campos[$i]['preco_pi'] < $campos[$i]['lote_minimo_custo_tt']) {
                    $valor_total_saida_os+= $campos[$i]['lote_minimo_custo_tt'];
                    echo number_format($campos[$i]['lote_minimo_custo_tt'], 2, ',', '.');
                }else {
                    $valor_item_entrada_os = round($peso_qtde_total_utilizar * $campos[$i]['preco_pi'], 2);
                    $valor_total_entrada_os+= $valor_item_entrada_os;
                    echo number_format($valor_item_entrada_os, 2, ',', '.');
                }
                echo '<font color="brown" title="Lote Mínimo" style="cursor:help"><b> (L. Mín)</b></font>';
            }else {//Não existe marcação, sendo assim faço o cálculo normal ...
                $valor_item_entrada_os = round($peso_qtde_total_utilizar * $campos[$i]['preco_pi'], 2);
                $valor_total_entrada_os+= $valor_item_entrada_os;
                echo number_format($valor_item_entrada_os, 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
                if(empty($campos_itens_entradas[$j]['dureza_fornecedor'])) {
                    echo '&nbsp;';
                }else {
                    echo $campos_itens_entradas[$j]['dureza_fornecedor'];
                }
        ?>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
            }
            /*Essa variável "$vetor_pas_atrelados" é processada dentro da função -> 
            intermodular::calculo_producao_mmv_estoque_pas_atrelados(), e esse unset serve para eliminar 
            o acúmulo de ID_PAs que fica de um loop para o outro

            Exemplo: Primeiro Loop 9 Registros, Segundo Loop 8 Registros, mas me retorna 17 acumulando com os
            9 do Primeiro Loop, o mais ideal seria de jogar esse unset dentro da função, mais não funciona 
            agora já não sei se é por causa do Global, vai entender, tive que fazer esse Macete ...

            02/06/2016 ...*/
            unset($vetor_pas_atrelados);
        }
?>
    <tr align='center'>
        <td class='linhadestaque' colspan='7'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='red' size='-5'>
            <?
                //Se o campo "$nf_minimo_tt" do cadastro do Fornecedor for > que o Valor Total da OS printa a msn ...
                if($nf_minimo_tt > $valor_total_saida_os) {
                    echo '<b>ESTA O.S. NÃO ATINGIU O VALOR DE NF MÍNIMO !</b>';
                }else {
                    echo '&nbsp;';
                }
            ?>
            </font>
        </td>
        <td class='linhadestaque' colspan='6' align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='black' size='-5'>
                TOTAL DE SAÍDA DA OS R$: <?=number_format($valor_total_saida_os, 2, ',', '.');?>
            </font>
        </td>
        <td class='linhadestaque' colspan='5' align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='black' size='-5'>
                TOTAL DE ENTRADA DA OS R$: <?=number_format($valor_total_entrada_os, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
//Aqui eu faço Tratamento com a Parte de CTT(s) ...
        if(count($vetor_ctts) > 0) {//Se existir pelo menos 1 CTT atrelado a esses Itens, então ...
            //Novo vetor sem os valores duplicados
            $vetor_ctts = array_unique($vetor_ctts);//Removo do Vetor todos os elementos que estão duplicados
            sort($vetor_ctts);//Ordeno o vetor em ordem Crescente
            $vetor_ctts = implode(',', $vetor_ctts);
?>
    <tr class='iframe' onClick="showHide('lote_minimo'); return false">
        <td colspan='18'>
            <font color='black' size='2'>&nbsp;Resumo: </font>
        </td>
    </tr>
    <tr>
        <td colspan='18'>
            <iframe src = 'relatorio_lote_minimo.php?vetor_ctts=<?=$vetor_ctts;?>&id_os=<?=$id_os;?>' name='lote_minimo' id='lote_minimo' marginwidth='0' marginheight='0' style='display: none' frameborder='0' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
<?
        }
?>
</table>
<!--*********************************************************************************************-->
<?
/******************************************************************************************/
/*************************************Dados de Pedido**************************************/
/******************************************************************************************/
//Caso essa OS esteja atrelada a algum pedido, então eu apresento alguns dados de Pedido ...
        if(!empty($id_pedido)) {//Busca de Alguns dados do Pedido ...
            $sql = "SELECT f.`razaosocial`, p.`data_emissao` 
                    FROM `pedidos` p 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
                    WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
            $campos_pedido = bancos::sql($sql);
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td colspan='6'></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Pedido(s) atrelado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Pedido
        </td>
        <td>
            Nossa NF N.º de Saída
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Observação
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td width='10'>
            <a href="javascript:detalhes_pedidos('<?=$id_pedido;?>')" title='Detalhes de Pedido' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="javascript:detalhes_pedidos('<?=$id_pedido;?>')" title='Detalhes de Pedido' class='link'>
                <?=$id_pedido;?>
            </a>
        </td>
        <td>
        <?
//Significa que por enquanto 
            if(empty($nnf)) {
                echo 'S/ NÚMERO';
            }else {
                echo $nnf;
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos_pedido[0]['razaosocial'];?>
        </td>
        <td>
            <?=data::datetodata($campos_pedido[0]['data_emissao'], '/');?>
        </td>
        <td>
        <?
            $sql = "SELECT `observacao` 
                    FROM `follow_ups` 
                    WHERE `identificacao` = '".$campos_pedido[0]['id_pedido']."' 
                    AND `origem` = '16' ";
            $campos_follow_ups = bancos::sql($sql);
            $linhas_follow_ups = count($campos_follow_ups);
            for($i = 0; $i < $linhas_follow_ups; $i++) {
                if(!empty($campos_follow_ups[$i]['observacao'])) echo "<img width='28'  height='23' title='".$campos_follow_ups[$i]['observacao']."' src = '../../../../imagem/olho.jpg'><br/>";
            }
        ?>
        </td>
    </tr>
</table>
<?
        }
/***************************************************************************************** */
/**********************************Dados de Nota Fiscal*********************************** */
/***************************************************************************************** */
//Uma OS pode ter tido entrada em várias Notas Fiscais "Compras" ...
        $sql = "SELECT DISTINCT(oi.`id_nfe`), f.`razaosocial`, nfe.`num_nota`, nfe.`data_emissao` 
                FROM `oss_itens` oi 
                INNER JOIN `nfe` ON nfe.`id_nfe` = oi.`id_nfe` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                WHERE oi.`id_os` = '$id_os' GROUP BY oi.`id_nfe` ";
        $campos_nfe = bancos::sql($sql);
        $linhas_nfe = count($campos_nfe);
        for($i = 0; $i < $linhas_nfe; $i++) {//Busca de alguns dados de Nota Fiscal ...
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Nota(s) Fiscal(is) atrelada(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º NF de Entrada
        </td>
        <td>
            Nossa NF N.º de Saída
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas_nfe; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td width='10'>
            <a href="javascript:detalhes_nota_fiscal('<?=$campos_nfe[$i]['id_nfe'];?>')" title='Detalhes de Nota Fiscal' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="javascript:detalhes_nota_fiscal('<?=$campos_nfe[$i]['id_nfe'];?>')" title='Detalhes de Nota Fiscal' class='link'>
                <?=$campos_nfe[$i]['num_nota'];?>
            </a>
        </td>
        <td>
        <?
                if(empty($nnf)) {
                    echo 'S/ NÚMERO';
                }else {
                    echo $nnf;
                }
        ?>
        </td>
        <td align='left'>
            <?=$campos_nfe[$i]['razaosocial'];?>
        </td>
        <td>
            <?=data::datetodata($campos_nfe[$i]['data_emissao'], '/');?>
        </td>
        <td>
        <?
                $sql = "SELECT `observacao` 
                        FROM `follow_ups` 
                        WHERE `identificacao` = '".$campos_nfe[$i]['id_nfe']."' 
                        AND `origem` = '17' ";
                $campos_follow_ups = bancos::sql($sql);
                $linhas_follow_ups = count($campos_follow_ups);
                for($j = 0; $j < $linhas_follow_ups; $j++) {
                    if(!empty($campos_follow_ups[$j]['observacao'])) echo "<img width='28'  height='23' title='".$campos_follow_ups[$j]['observacao']."' src = '../../../../imagem/olho.jpg'><br/>";
                }
        ?>
        </td>
    </tr>
<?
            }
?>
</table>
<?
        }
/***************************************************************************************** */
?>
<!--Para perguntar se deseja inserir as Antecipações, mas só na primeira vez em que cair nessa tela-->
<?
        if(!empty($valor)) {
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[$valor];?>')
    </Script>
<?
        }
    }else {//Não existe item para essa OS(s) ...
?>
<table width='90%' border='0' align='center'>
    <tr class='atencao'>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='#FF0000'>
                <b>OS
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='blue'><?=$id_os;?></font>
                n&atilde;o cont&eacute;m itens cadastrado.</b>
            </font>
        </td>
    </tr>
</table>
<?
        if(!empty($valor)) {
?>
            <Script Language = 'JavaScript'>
                alert('<?=$mensagem[$valor];?>')
            </Script>
<?
        }
    }
}
//Macete (rsrs)
if(empty($perguntar_uma_vez)) $perguntar_uma_vez = 0;
?>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='perguntar_uma_vez' value='<?=$perguntar_uma_vez;?>'>
<input type='hidden' name='parametro_filtro' value='<?=$parametro_filtro;?>'>
<!--Esses 2 hiddens aki foi uma cópia de compras, por enquanto vou deixar aqui-->
<input type='hidden' name='opt_item_os'>
<input type='hidden' name='opt_item_principal'>
<!-- ******************************************** -->
<input type='hidden' name='id_os' value='<?=$id_os?>'>
<!--Até então serve somente para armazenar o valor das mensagens-->
<input type='hidden' name='valor'>
<!--*************************************************************************-->
</form>
<center>
    <?
        echo paginacao::print_paginacao('sim');
        //Significa que essa Tela foi aberta como sendo Pop-UP ...
        if(empty($_GET['pop_up'])) {
            $voltar = ($_GET['acesso_sem_link'] == 'S') ? 'consultar.php' : 'consultar.php'.$parametro_filtro;
    ?>
    <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="parent.location = '<?=$voltar;?>'" class='botao'>
    <?
        }
    ?>
    <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' style='color:black' class='botao'>
</center>
</body>
</html>
<?
//Somente na Primeira vez em que carregar essa Tela
/****************Aqui dispara a função automaticamente****************/
/*Se a OS estiver não estiver importada ainda, e está possuir algum Item que esteje com o Preço incoerente
em comparação ao da lista de Preço, então dispara essa função automaticamente perguntando se o usuário
realmente deseja atualizar o Item da OS com o Item da Lista de Preço*/
$retorno = producao::conferir_precos_os($id_os);

$id_produtos_insumos    = count($retorno['id_produtos_insumos']);
$produtos_insumos       = $retorno['produtos_insumos'];

if($id_produtos_insumos > 0) {
?>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
//Só vai fazer essa pergunta, na primeira em que carregar essa tela
if(parent.itens.document.form.perguntar_uma_vez.value == 0) {
    valor = confirm('A LISTA DE PREÇO DESTE FORNECEDOR FOI ALTERADA !!!\nDESEJAR ATUALIZAR O(S) PREÇOS PARA ESSES PI(S): \n\n'+'<?=$produtos_insumos;?>'+'\n ?')
    if(valor == true) nova_janela('outras_opcoes.php?id_os=<?=$id_os;?>', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
//Para não perguntar + nenhuma vez
    parent.itens.document.form.perguntar_uma_vez.value = 1
    parent.itens.document.form.submit()
}
</Script>
<?}?>
