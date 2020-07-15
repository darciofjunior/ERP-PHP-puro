<?
require('../../../../lib/segurancas.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/excedente/relatorio.php', '../../../../');

if(!empty($_GET['cmb_embalado']))                       $condicao_embalado = " AND ee.`embalado` = '$_GET[cmb_embalado]' ";
if(!empty($_GET['chkt_somente_em_aberto']))             $condicao_somente_em_aberto = " AND ee.`status` = '0' ";
if(!empty($_GET['chkt_tem_item_faltante_atrelado']))    $condicao_tem_item_faltante_atrelado = " AND ee.`id_produto_acabado_faltante` <> '0' ";
if(empty($_GET['txt_prateleira']))                      $_GET['txt_prateleira'] = '%';

//Aqui eu busco todos os Registros de Estoque Excedentes registrados do PA passado por parâmetro ...
$sql = "SELECT ee.*, pa.`referencia`, pa.`discriminacao`, pa.`peso_unitario` 
        FROM `estoques_excedentes` ee 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ee.`id_produto_acabado` AND pa.`referencia` LIKE '%$_GET[txt_referencia]%' AND pa.`discriminacao` LIKE '%$_GET[txt_discriminacao]%' 
        WHERE ee.`prateleira` LIKE '$_GET[txt_prateleira]' 
        AND ee.`bandeja` LIKE '%$_GET[txt_bandeja]%' 
        AND ee.`observacao` LIKE '%$_GET[txt_observacao]%' 
        $condicao_embalado 
        $condicao_somente_em_aberto 
        $condicao_tem_item_faltante_atrelado 
        ORDER BY ee.`id_estoque_excedente` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Relatório de Estoque Excedente do PA por Data ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Relatório de Estoque Excedente do PA por Data
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Prateleira
        </td>
        <td>
            Bandeja
        </td>
        <td>
            Qtde
        </td>
        <td>
            Embalado
        </td>
        <td>
            Ref
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Peso<br>Unitário
        </td>
        <td>
            Peso<br>Total
        </td>
        <td>
            Item<br>Faltante
        </td>
        <td>
            Observação
        </td>
        <td>
            Status
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {//For ...
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['prateleira'];?>
        </td>
        <td>
            <?=$campos[$i]['bandeja'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde'];?>
        </td>
        <td>
        <?
            if($campos[$i]['embalado'] == 'S') {
                echo 'SIM';
            }else if($campos[$i]['embalado'] == 'N') {
                echo 'NÃO';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'] * $campos[$i]['peso_unitario'], 1, ',', '.');?>
        </td>
        <td>
        <?
            $sql = "SELECT referencia, CONCAT(referencia, ' * ', discriminacao) AS dados 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado_faltante']."' LIMIT 1 ";
            $campos_pas = bancos::sql($sql);
        ?>
            <font title="<?=$campos_pas[0]['dados'];?>" style="cursor:help" class="link">
                <a href='../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado_faltante'];?>' class='html5lightbox'>
                    <?=$campos_pas[0]['referencia'];?>
                </a>
            </font>
        </td>		
        <td align='left'>
        <?
            $observacao     = strstr($campos[$i]['observacao'], 'Observação:');
            echo $parte_inicial  = str_replace(strstr($campos[$i]['observacao'], 'Observação:'), '', $campos[$i]['observacao']).'<br><MARQUEE behavior="alternate" width="55%">'.$observacao.'</MARQUEE>';                                                    
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['status'] == 0) {
                echo '<font color="red"><b>(Em Aberto)</b></font>';
            }else {
                echo '<font color="darkblue"><b>(Concluído)</b></font>';
            }
        ?>
        </td>
    </tr>
<?
    }//Fim do For ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='parent.html5Lightbox.finish()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>