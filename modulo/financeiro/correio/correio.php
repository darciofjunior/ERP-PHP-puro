<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../classes/array_sistema/array_sistema.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    //Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $cmb_tipo_documento         = $_POST['cmb_tipo_documento'];
        $txt_numero_documento       = $_POST['txt_numero_documento'];
        $txt_cliente                = $_POST['txt_cliente'];
        $txt_transportadora         = $_POST['txt_transportadora'];
        $cmb_empresa                = $_POST['cmb_empresa'];
        $cmb_uf                     = $_POST['cmb_uf'];
        $txt_data_emissao_inicial   = $_POST['txt_data_emissao_inicial'];
        $txt_data_emissao_final     = $_POST['txt_data_emissao_final'];
        $chkt_somente_nao_liberadas = $_POST['chkt_somente_nao_liberadas'];
    }else {
        $cmb_tipo_documento         = $_GET['cmb_tipo_documento'];
        $txt_numero_documento       = $_GET['txt_numero_documento'];
        $txt_cliente                = $_GET['txt_cliente'];
        $txt_transportadora         = $_GET['txt_transportadora'];
        $cmb_empresa                = $_GET['cmb_empresa'];
        $cmb_uf                     = $_GET['cmb_uf'];
        $txt_data_emissao_inicial   = $_GET['txt_data_emissao_inicial'];
        $txt_data_emissao_final     = $_GET['txt_data_emissao_final'];
        $chkt_somente_nao_liberadas = $_GET['chkt_somente_nao_liberadas'];
    }
    if(empty($cmb_empresa)) $cmb_empresa = '%';
    if(empty($cmb_uf))      $cmb_uf = '%';
    
    if($cmb_tipo_documento == 'NF') {//NF ...
        //Se o usuário consultar as NFs por número, então eu acrescento essa cláusula a mais no SQL ...
        if(!empty($txt_numero_documento)) {//Lembrando que QQ tipo de NF usa o mesmo Talonário nnn_num_notas ...
/**************************************NFs Saída*****************************************/
/*Essa Tabela de NFs está totalmente relacionada com as NFs de Saída que vem de Pedidos, Orçamentos ...

*** Só listo as Notas Fiscais em que a Transportadora seja do Tipo "Correio" ou "TAM = 1265" ...*/
            $sql = "SELECT DISTINCT(nfs.`id_nf`) 
                    FROM `nfs` 
                    INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` AND nnn.`numero_nf` LIKE '$txt_numero_documento%' 
                    INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` AND (t.`nome` LIKE 'CORREIO%' OR t.`id_transportadora` = '1265') 
                    UNION 
                    SELECT DISTINCT(nfs.`id_nf`) 
                    FROM `nfs` 
                    INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` AND (t.`nome` LIKE 'CORREIO%' OR t.`id_transportadora` = '1265') 
                    WHERE nfs.`snf_devolvida` LIKE '$txt_numero_documento%' 
                    AND nfs.`id_empresa` LIKE '$cmb_empresa' 
                    ORDER BY `id_nf` DESC ";
            $campos_nfs = bancos::sql($sql);
            $linhas_nfs = count($campos_nfs);
            for($l = 0; $l < $linhas_nfs; $l++) $id_nfs[] = $campos_nfs[$l]['id_nf'];
            //Arranjo Ténico
            if(count($id_nfs) == 0) $id_nfs[] = '0';
            $vetor_nfs      = implode(',', $id_nfs);
            $condicao_nfs   = " AND nfs.`id_nf` IN ($vetor_nfs) ";
        }
/**************************************NFs Saída*************************************/
//NFs Saída / Devolução que equivale ao Status 6 - nfs ...
//NFs Outras - nfs_outras ...
        if(!empty($txt_data_emissao_inicial)) {
            //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente ...
            if(substr($txt_data_emissao_final, 4, 1) != '-') {
                $txt_data_emissao_inicial = data::datatodate($txt_data_emissao_inicial, '-');
                $txt_data_emissao_final = data::datatodate($txt_data_emissao_final, '-');
            }
            //Aqui é para não dar erro de SQL
            $condicao_datas = " AND nfs.`data_emissao` BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
        }
        //Se estiver marcada a opção de Nfs não Conferidas ...
        if(!empty($chkt_somente_nao_liberadas)) $condicao_somente_nao_liberadas = " AND nfs.`observacao_conferencia_correio` = '' ";
/**************************************NFs Saída*************************************/
/*Exibo somente as NFs Saída a partir do dia 1 de dezembro de 2009, que foi quando iniciamos a Nova Lógica ...

*** Só listo as Notas Fiscais em que a Transportadora seja do Tipo "Correio" ou "TAM = 1265" ...*/
        $sql = "(SELECT nfs.`id_nf`, nfs.`id_empresa`, nfs.`id_nf_num_nota`, nfs.`valor_frete`, 
                nfs.`valor_frete_pago`, nfs.`data_emissao`, nfs.`data_saida_entrada`, 
                nfs.`peso_bruto_balanca`, c.`id_pais`, c.`id_uf`, c.`nomefantasia`, c.`razaosocial`, 
                c.`cidade`, t.`nome` AS transportadora 
                FROM `nfs` 
                INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` AND t.`nome` LIKE '%$txt_transportadora%' AND (t.`nome` LIKE 'CORREIO%' OR t.`id_transportadora` = '1265') 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`ativo` = '1' AND c.`id_uf` LIKE '$cmb_uf' 
                WHERE nfs.`ativo` = '1' 
                AND nfs.`id_empresa` LIKE '$cmb_empresa' 
                AND SUBSTRING(nfs.`data_emissao`, 1, 10) >= '2009-12-01' 
                $condicao_nfs 
                $condicao_datas 
                $condicao_somente_nao_liberadas GROUP BY nfs.`id_nf`) ORDER BY `data_emissao` DESC ";

        $sql_extra = "SELECT COUNT(DISTINCT(nfs.`id_nf`)) AS total_registro 
                    FROM `nfs` 
                    INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` AND t.`nome` LIKE '%$txt_transportadora%' AND (t.`nome` LIKE 'CORREIO%' OR t.`id_transportadora` = '1265') 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`ativo` = '1' AND c.`id_uf` LIKE '$cmb_uf' 
                    WHERE nfs.`ativo` = '1' 
                    AND nfs.`id_empresa` LIKE '$cmb_empresa' 
                    AND SUBSTRING(nfs.`data_emissao`, 1, 10) >= '2009-12-01' 
                    $condicao_nfs 
                    $condicao_datas 
                    $condicao_somente_nao_liberadas GROUP BY nfs.`id_nf` ";
    }else {//Vale ...
        if(!empty($txt_data_emissao_inicial)) {
            //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente ...
            if(substr($txt_data_emissao_final, 4, 1) != '-') {
                $txt_data_emissao_inicial = data::datatodate($txt_data_emissao_inicial, '-');
                $txt_data_emissao_final = data::datatodate($txt_data_emissao_final, '-');
            }
            //Aqui é para não dar erro de SQL
            $condicao_datas = " AND SUBSTRING(vv.`data_sys`, 1, 10) BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
        }
        //Se estiver marcada a opção de Nfs não Conferidas ...
        if(!empty($chkt_somente_nao_liberadas)) $condicao_somente_nao_liberadas = " AND vv.`observacao_conferencia_correio` = '' ";
        
//*** Só listo os Vales em que a Transportadora seja do Tipo "Correio" ou "TAM = 1265" ...
        $sql = "SELECT SUM(vvi.`qtde` * pvi.`preco_liq_final`) AS valor_total_vale, vv.`id_vale_venda`, 
                vv.`peso_bruto`, vv.`valor_frete`, vv.`valor_frete_pago`, 
                SUBSTRING(vv.`data_sys`, 1, 10) AS data_emissao, 
                vvi.`id_pedido_venda_item`, c.`id_pais`, c.`id_uf`, c.`nomefantasia`, c.`razaosocial`, 
                c.`cidade`, pv.`id_empresa`, t.`nome` AS transportadora 
                FROM `vales_vendas` vv 
                INNER JOIN `vales_vendas_itens` vvi ON vvi.`id_vale_venda` = vv.`id_vale_venda` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = vvi.`id_pedido_venda_item` 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`id_empresa` LIKE '$cmb_empresa' 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                INNER JOIN `transportadoras` t ON t.`id_transportadora` = vv.`id_transportadora` AND t.`nome` LIKE '%$txt_transportadora%' AND (t.`nome` LIKE 'CORREIO%' OR t.`id_transportadora` = '1265') 
                WHERE vv.`id_vale_venda` LIKE '$txt_numero_documento%' 
                $condicao_datas 
                $condicao_somente_nao_liberadas 
                GROUP BY vv.`id_vale_venda` ";
        
        $sql_extra = "SELECT COUNT(DISTINCT(vv.`id_vale_venda`)) AS total_registro 
                FROM `vales_vendas` vv 
                INNER JOIN `vales_vendas_itens` vvi ON vvi.`id_vale_venda` = vv.`id_vale_venda` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = vvi.`id_pedido_venda_item` 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`id_empresa` LIKE '$cmb_empresa' 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                INNER JOIN `transportadoras` t ON t.`id_transportadora` = vv.`id_transportadora` AND t.`nome` LIKE '%$txt_transportadora%' AND (t.`nome` LIKE 'CORREIO%' OR t.`id_transportadora` = '1265') 
                WHERE vv.`id_vale_venda` LIKE '$txt_numero_documento%' 
                $condicao_datas 
                $condicao_somente_nao_liberadas 
                GROUP BY vv.`id_vale_venda` ";
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
//Não retornou nenhum registro, então requisito a Tela de Filtro ...
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = '<?=$PHP_SELF;?>?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Conferir / Liberar Correio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='14'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            Conferir / Liberar Correio - 
            <font color='yellow'>
            <?
                if($cmb_tipo_documento == 'NF') {//NF ...
                    echo 'NF(S)';
                }else {//Vale ...
                    echo 'VALE DE VENDA';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
                echo 'N.º NF(s)';
            }else {//Vale ...
                echo 'N.º do Vale de Venda';
            }
        ?>
        </td>
        <td>
            Data Em.
        </td>
        <td>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
                echo 'Data Saída';
            }else {//Vale ...
                echo '-';
            }
        ?>
        </td>
        <td>
            Cliente
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
        <td>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
        ?>
            <font title='Valor do Frete na NF c/ Impostos' style='cursor:help'>
                V.F. na NF c/ I
            </font>
        <?
            }else {//Vale ...
        ?>
            <font title='Valor do Frete no Vale' style='cursor:help'>
                V.F. no Vale
            </font>
        <?
            }
        ?>
        </td>
        <td>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
        ?>
            <font title='Valor do Frete na NF s/ Impostos' style='cursor:help'>
                V.F. na NF s/ I
            </font>
        <?
            }else {//Vale ...
                echo '-';
            }
        ?>
        </td>
        <td>
            <font title='Valor do Frete Pago' style='cursor:help'>
                V.F. Pg
            </font>
        </td>
        <td>
            Transportadora
        </td>
        <td>
            Empresa
        </td>
        <td>
            Peso Bruto
        </td>
        <td>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
                echo 'Valor Total da Nota';
            }else {//Vale ...
                echo 'Valor Total do Vale';
            }
        ?>
        </td>
    </tr>
<?
//Utilizado mais abaixo nos cálculos ...
    $outros_impostos_federais   = genericas::variavel(34);
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
    $vetor                      = array_sistema::nota_fiscal();
    for($i = 0;  $i < $linhas; $i++) {
        if($cmb_tipo_documento == 'NF') {//NF ...
            $parametro_liberacao = '?id_nf='.$campos[$i]['id_nf'].'&cmb_tipo_documento='.$cmb_tipo_documento;
        }else {//Vale ...
            $parametro_liberacao = '?id_vale_venda='.$campos[$i]['id_vale_venda'].'&cmb_tipo_documento='.$cmb_tipo_documento;
        }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='liberacao.php<?=$parametro_liberacao;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='liberacao.php<?=$parametro_liberacao;?>' class='html5lightbox'>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
                echo faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');
            }else {//Vale ...
                echo $campos[$i]['id_vale_venda'];
            }
        ?>
            </a>
        </td>
        <td>
        <?
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
                if($campos[$i]['data_saida_entrada'] != '0000-00-00') echo data::datetodata($campos[$i]['data_saida_entrada'], '/');
            }else {//Vale ...
                echo '-';
            }
        ?>
        </td>
        <td align='left'>
            
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
        ?>
            <a href = '../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>&pop_up=1' class='html5lightbox'>
        <?
            }else {//Vale ...
        ?>
            <a href = '../../producao/programacao/estoque/gerenciar/detalhes_vales_vendas.php?id_pedido_venda_item=<?=$campos[$i]['id_pedido_venda_item'];?>' title='Detalhes de Vales de Vendas' class='html5lightbox'>
        <?
            }
        ?>
                <font title='Nome Fantasia: <?=$campos[$i]['nomefantasia'];?>' style='cursor:help'>
                    <?=$campos[$i]['razaosocial'];?>
                </font>
            </a>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
        <?
            if($campos[$i]['id_uf'] > 0) {//Se existir UF para o Cliente ...
                $sql = "SELECT `sigla` 
                        FROM `ufs` 
                        WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
                $campos_ufs = bancos::sql($sql);
                echo $campos_ufs[0]['sigla'];
            }
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor_frete'], 2, ',', '.');?>
        </td>
        <td>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
                //Busca o Valor do Maior ICMS da NF ...
                $sql = "SELECT `icms` AS maior_icms 
                        FROM `nfs_itens` 
                        WHERE `id_nf` = '".$campos[$i]['id_nf']."' ORDER BY icms DESC LIMIT 1 ";
                $campos_maior = bancos::sql($sql);
                $valor_frete_sem_impostos = $campos[$i]['valor_frete'] * (100 - $campos_maior[0]['maior_icms'] - $outros_impostos_federais) / 100;
                echo number_format($valor_frete_sem_impostos, 2, ',', '.');
            }else {//Vale ...
                echo '-';
            }
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor_frete_pago'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[$i]['transportadora'];?>
        </td>
        <td>
        <?
            //Busca da Empresa da NF ...
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            echo $campos_empresa[0]['nomefantasia'];
        ?>
        </td>
        <td>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
                echo number_format($campos[$i]['peso_bruto_balanca'], 2, ',', '.');
//Se o Valor do Frete = 0 e o Peso Bruto = 0, então significa que a Mercadoria foi no Vale ...
                if($campos[$i]['valor_frete'] == 0 && $campos[$i]['peso_bruto_balanca'] == 0) echo '<font color="red"><b> (VALE)</b></font>';
            }else {//Vale ...
                echo number_format($campos[$i]['peso_bruto'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if($cmb_tipo_documento == 'NF') {//NF ...
                //Função p/ o cálculo do Valor Total da NF - tem q ter todos os calculos da NF, pois o valor contém frete + impostos e etc ...
                $calculo_total_impostos = calculos::calculo_impostos(0, $campos[$i]['id_nf'], 'NF');
                echo number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');
            }else {//Vale ...
                echo number_format($campos[$i]['valor_total_vale'], 2, ',', '.');
            }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'correio.php'" class='botao'>
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
}else {
?>
<html>
<head>
<title>.:: Conferir / Liberar Correio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Se a Data de Emissão estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
    if(document.form.txt_data_emissao_inicial.value != '') {
//Data de Emissão Inicial
        if(!data('form', 'txt_data_emissao_inicial', '4000', 'EMISSÃO INICIAL')) {
            return false
        }
//Data de Emissão Final
        if(!data('form', 'txt_data_emissao_final', '4000', 'EMISSÃO FINAL')) {
            return false
        }
//Comparação com as Datas ...
        var data_emissao_inicial = document.form.txt_data_emissao_inicial.value
        var data_emissao_final = document.form.txt_data_emissao_final.value
        data_emissao_inicial = data_emissao_inicial.substr(6,4) + data_emissao_inicial.substr(3,2) + data_emissao_inicial.substr(0,2)
        data_emissao_final = data_emissao_final.substr(6,4) + data_emissao_final.substr(3,2) + data_emissao_final.substr(0,2)
        data_emissao_inicial = eval(data_emissao_inicial)
        data_emissao_final = eval(data_emissao_final)

        if(data_emissao_final < data_emissao_inicial) {
            alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
            document.form.txt_data_emissao_final.focus()
            document.form.txt_data_emissao_final.select()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_numero_documento.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<!--***************Controle de Tela***************-->
<input type='hidden' name='passo' value='1'>
<!--**********************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Conferir / Liberar Correio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tipo do Documento
        </td>
        <td>
            <select name='cmb_tipo_documento' title='Selecione um Tipo do Documento' class='combo'>
                <option value='NF' selected>NF</option>
                <option value='VALE'>VALE</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número do Documento
        </td>
        <td>
            <input type='text' name='txt_numero_documento' title='Digite o Número do Documento' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type='text' name='txt_cliente' title='Digite o Cliente' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Transportadora
        </td>
        <td>
            <input type='text' name='txt_transportadora' title='Digite a Transportadora' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa
        </b>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
            <?
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY `nomefantasia` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            UF
        </b>
        <td>
            <select name='cmb_uf' title='Selecione a UF' class='combo'>
            <?
                $sql = "SELECT `id_uf`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emissão
        </td>
        <td>
            <input type='text' name='txt_data_emissao_inicial' title='Digite a Data de Emissão Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name='txt_data_emissao_final' title='Digite a Data de Emissão Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'> 
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_nao_liberadas' value='1' title='Somente não Liberadas' id='somente_nao_liberadas' class='checkbox' checked>
            <label for='somente_nao_liberadas'>Somente não Liberadas</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_numero_documento.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>