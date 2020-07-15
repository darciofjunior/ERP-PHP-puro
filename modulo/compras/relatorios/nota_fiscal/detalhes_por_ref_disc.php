<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
session_start('funcionarios');

//Se o usuário já passou a Data então ...
if(!empty($txt_data_inicial)) {
    $campo_data = ($opt_data == 1) ? 'nfe.data_emissao' : 'nfe.data_entrega';
    $condicao_nf = " AND SUBSTRING($campo_data, 1, 10) BETWEEN '$txt_data_inicial' AND '$txt_data_final' ";
}
if($cmb_empresa == '') $cmb_empresa = '%';

/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
como caracter ...*/
$txt_consultar = str_replace('|', '%', $txt_consultar);

if($opt_opcao == 2) {//Filtro feito somente por Referência ...
    $buscar_referencia = "g.`referencia`, ";
    $inner_join_grupos = "INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`referencia` LIKE '%$txt_consultar%' ";
}else {//Filtro feito somente por Discriminação ...
    $where = " WHERE pi.`discriminacao` LIKE  '%$txt_consultar%' ";
}

/*Busca de todas as Compras na Tabela de NFE(s) de acordo com o PI digitado pelo usuário e Empresa Selecionada 
no período passado por parâmetro ...*/
$sql = "SELECT DISTINCT(nfeh.`id_produto_insumo`), $buscar_referencia pi.`discriminacao` AS discriminacao, nfe.`id_tipo_moeda` 
        FROM `produtos_insumos` pi 
        $inner_join_grupos 
        INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_produto_insumo` = pi.`id_produto_insumo` 
        INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` AND nfe.`id_empresa` LIKE '$cmb_empresa' $condicao_nf 
        $where ";
$campos_nfe = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas_nfe = count($campos_nfe);
if($linhas_nfe == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'relatorio_nota_fiscal.php?valor=1'
    </Script>
<?
}else {//Se encontrou alguma Compra ...
/************************************************************/
//Esse fator será utilizado mais abaixo ...
$fator_custo_importacao = genericas::variavel(1);
/************************************************************/
//Como achou Itens, dispara o Loop
    for($i = 0; $i < $linhas_nfe; $i++) {
        $produto = '';//Limpo a variável para não herdar valores do Loop anterior ...
        if(!empty($campos_nfe[$i]['referencia'])) $produto = '<font color="yellow">Referência: </font>'.$campos_nfe[$i]['referencia'].' - ';
        $produto.= '<font color="yellow">Discriminação: </font>'.$campos_nfe[$i]['discriminacao'];
//Retorna todas as NFE(s) de acordo com o id_produto_insumo do Loop ...
        $sql = "SELECT e.nomefantasia, f.razaosocial, nfe.*, nfeh.qtde_entregue, nfeh.valor_entregue, tm.simbolo 
                FROM `nfe` 
                INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = nfe.id_tipo_moeda 
                INNER JOIN `empresas` e ON e.id_empresa = nfe.id_empresa 
                INNER JOIN `nfe_historicos` nfeh ON nfeh.id_nfe = nfe.id_nfe AND nfeh.id_produto_insumo = ".$campos_nfe[$i]['id_produto_insumo']." 
                WHERE nfe.`id_empresa` LIKE '$cmb_empresa' 
                $condicao_nf ORDER BY nfe.data_emissao DESC ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
?>
<html>
<head>
<title>.:: Compras no Período ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
        <?
            echo $produto;
            if(!empty($txt_data_inicial)) {
        ?>
                - Período de <?=data::datetodata($txt_data_inicial, '/');?> até <?=data::datetodata($txt_data_final, '/');?>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Nº da Nota
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Data de Entrega
        </td>
        <td>
            Tipo da Nota
        </td>
        <td>
        <?
            if($campos_nfe[$i]['id_tipo_moeda'] == 2) {//Dólar
                echo 'Valor em Dólar';
            }else if($campos_nfe[$i]['id_tipo_moeda'] == 3) {//Euro
                echo 'Valor em Euro';
            }else {
                echo 'Moeda Nacional';
            }
        ?>
        </td>
        <td>
            Valor em Real
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
//Sempre zero esse valor antes de verificar os Itens de NF ...
            $valor_todas_nfes_itens = 0;
            for($j = 0; $j < $linhas; $j++) {
                $id_nfe = $campos[$j]['id_nfe'];
                $data_emissao = $campos[$j]['data_emissao'];
                $id_tipo_moeda = $campos[$j]['id_tipo_moeda'];
                $valor_total_item = $campos[$j]['qtde_entregue'] * $campos[$j]['valor_entregue'];
                $moeda = $campos[$j]['simbolo'].' ';
//Se o Tipo da Moeda da NF for diferente de Real então ...
                if($id_tipo_moeda > 1) {
//Busca o Dólar e Euro baseados na Data de Emissão da NF ...
                    $sql = "SELECT valor_dolar_dia, valor_euro_dia 
                            FROM `cambios` 
                            WHERE `data` = '$data_emissao' LIMIT 1 ";
                    $campos_moeda = bancos::sql($sql);
                    if(count($campos_moeda) > 0) {
                        $valor_dolar_dia    = $campos_moeda[0]['valor_dolar_dia'];
                        $valor_euro_dia     = $campos_moeda[0]['valor_euro_dia'];
                    }else {
                        $sql = "SELECT valor_dolar_dia, valor_euro_dia 
                                FROM `cambios` 
                                WHERE `data` < '$data_emissao' ORDER BY data DESC LIMIT 1 ";
                        $campos_moeda = bancos::sql($sql);
                        $valor_dolar_dia = $campos_moeda[0]['valor_dolar_dia'];
                        $valor_euro_dia = $campos_moeda[0]['valor_euro_dia'];
                    }
                }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href = '../../pedidos/nota_entrada/itens/itens.php?id_nfe=<?=$campos[$j]['id_nfe'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos[$j]['num_nota']?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$j]['razaosocial'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$j]['data_emissao'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$j]['data_entrega'], '/');?>
        </td>
        <td>
            <?=($campos[$j]['tipo'] == 1) ? 'NF' : 'SGD';?>
        </td>
        <td align='right'>
            <?=$moeda.number_format($valor_total_item, 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            if($id_tipo_moeda == 2) {//Se a NF foi negociado em Dólar, então multiplica pelo Dólar ...
                $valor_total_item*= $valor_dolar_dia * $fator_custo_importacao;
            }else if($id_tipo_moeda == 3) {//Se a NF foi negociado em Euro, então multiplica pelo Euro ...
                $valor_total_item*= $valor_euro_dia * $fator_custo_importacao;
            }
            echo 'R$ '.number_format($valor_total_item, 2, ',', '.');
        ?>
        </td>
        <td>
            <?=$campos[$j]['nomefantasia'];?>
        </td>
    </tr>
<?
                    $valor_todas_nfes_itens+= $valor_total_item;
                }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='8'>
            <font color="yellow">
                Valor Total: 
            </font>
            <?='R$ '.number_format($valor_todas_nfes_itens, 2, ',', '.');?>
        </td>
    </tr>
</table>
<br>
<?
//Aqui eu acumulo o Total de Todas NFE(s) de Todos os Fornecedores do Loop ...
            $valor_geral_todas_nfes+= $valor_todas_nfes_itens;
        }
    }
?>
</table>
<table width='80%' cellpadding='1' cellspacing='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Valor Total Geral de Toda(s) NF(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font color='yellow'>
                <?=$moeda.number_format($valor_geral_todas_nfes, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <br/>
            <font size='-2' color='#0066ff' face='verdana, arial, helvetica, sans-serif'>
                <?=paginacao::print_paginacao('sim');?>
            </font>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'relatorio_nota_fiscal.php'" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?}?>