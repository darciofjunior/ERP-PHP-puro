<?
require('../../../lib/segurancas.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/estoque_new.php');
session_start('funcionarios');
$data_sys = date('Y-m-d H:i:s');

/************************************************************************************/
//Verifico se a Sessão não caiu ...
    if (!(session_is_registered('id_funcionario'))) {
?>
    <Script Language = 'JavaScript'>
            window.location = '../../../html/index.php?valor=1'
    </Script>
<?
        exit;
    }
/************************************************************************************/
    
if($vendas == 1) {//Significa que foi solicitado p/ gerar Cotação através do Módulo de Vendas ...
    $sql = "INSERT INTO `cotacoes` (`id_cotacao`, `id_funcionario`, `fator_mmv`, `qtde_mes_comprar`, `tipo_compra`, `desconto_especial_porc`, `origem`, `data_sys`) values (null, '$_SESSION[id_funcionario]', '$_POST[txt_fator_correcao_mmv]', '$_POST[txt_qtde_meses]', '$_POST[cmb_tipo_compra]', '".str_replace(',', '.', $_POST['txt_desconto'])."', 'R', '$data_sys') ";
    bancos::sql($sql);
    $id_cotacao = bancos::id_registro();
    
    //Retiro do array os elementos Nulos "Nem todo o PA que está na tela de baixo é um PI" ...
    $_POST['hdd_produto_insumo'] = array_filter($_POST['hdd_produto_insumo']);

    foreach($_POST['hdd_produto_insumo'] as $i => $id_produto_insumo) {
        //Se tiver quantidade digitada para a Cotação então ...
        $qtde_compra = str_replace('.', '', $_POST['txt_qtde'][$i]);
        $qtde_compra = str_replace(',', '.', $qtde_compra);
        if($qtde_compra > 0) {//Só gera Cotação p/ Itens maior do que Zero ...
            $sql = "INSERT INTO `cotacoes_itens` (`id_cotacao_item`, `id_cotacao`, `id_produto_insumo`, `neces_compra_prod`, `cmm_mmv_total`, `qtde_pedida`, `qtde_metros`, `qtde_producao`, `qtde_estoque`, `mlm`) VALUES (NULL, '$id_cotacao', '$id_produto_insumo', '".$_POST['hdd_neces_compra_prod'][$i]."', '".$_POST['hdd_soma_mmv_todos_niveis'][$i]."', '$qtde_compra', '0', '".$_POST['hdd_compra_producao'][$i]."', '".$_POST['hdd_estoque_comprometido'][$i]."', '".$_POST['hdd_mlm'][$i]."') ";
            bancos::sql($sql);
        }
    }
}else {//Significa que foi solicitado p/ gerar Cotação através do Módulo de Compras ...
    $sql = "INSERT INTO `cotacoes` (`id_cotacao`, `id_funcionario`, `qtde_mes_comprar`, `tipo_compra`, `data_sys`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_POST[cmb_mes]', '$_GET[cmb_tipo_compra]', '$data_sys') ";
    bancos::sql($sql);
    $id_cotacao = bancos::id_registro();

    foreach($_POST['chkt_produto_insumo'] as $i => $id_produto_insumo) {
        $qtde_compra = str_replace('.', '', $_POST['txt_qtde_compra'][$i]);
        $qtde_compra = str_replace(',', '.', $qtde_compra);
        if($qtde_compra < 0) $qtde_compra = 0;
        if($qtde_compra > 0) {//Só gera Cotação p/ Itens maior do que Zero ...
            //Busca da qtde em Produção do PI ...
            $qtde_producao = estoque_ic::compra_producao($id_produto_insumo);
            
            
            //Aqui eu verifico se o PI é um PA ...
            $sql = "SELECT id_produto_acabado 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' 
                    AND `id_produto_insumo` > '0' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            if(count($campos_pipa) == 1) {//Se for PIPA ...
                //Se for PA eu trago o Estoque do PA ...
                $retorno        = estoque_acabado::qtde_estoque($campos_pipa[0]['id_produto_acabado']);
                $qtde_estoque   = $retorno[0];
            }else {//Se for um PI Simples ...
                //Busca da qtde em Estoque do PI ...
                $sql = "SELECT `qtde` 
                        FROM `estoques_insumos` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                $campos         = bancos::sql($sql);
                $qtde_estoque   = $campos[0]['qtde'];
            }
            $sql = "INSERT INTO `cotacoes_itens` (`id_cotacao_item`, `id_cotacao`, `id_produto_insumo`, `qtde_pedida`, `qtde_metros`, `qtde_producao`, `qtde_estoque`) VALUES (NULL, '$id_cotacao', '$id_produto_insumo', '$qtde_compra', '$txt_qtde_metros[$i]', '$qtde_producao', '$qtde_estoque') ";
            bancos::sql($sql);
            $id_produtos_insumos.= $id_produto_insumo.',';
        }
    }
    echo "<Script Language = 'JavaScript'>
            window.opener.parent.rodape.document.form.id_cotacao.value = '{$id_cotacao}'
         </Script>";
}

echo "<Script Language = 'JavaScript'>
        alert('COTAÇÃO N.º {$id_cotacao} GERADA COM SUCESSO !')
        window.location = 'imprimir.php?id_cotacao={$id_cotacao}'
</Script>";
?>