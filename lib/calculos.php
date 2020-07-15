<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class calculos {
    /*Observa��o muito importante: quando o "$id_negociacao_item" � diferente de Zero ent�o o sistema faz o c�lculo 
    de todos os Impostos p/ o Item espec�fico da Negocia��o, do contr�rio se for passado igual a Zero o sistema 
    faz o c�lculo de todos os Impostos de todos os Itens da Negocia��o ...

    ** Na maioria dos casos o $id_produto � um $id_produto_acabado, exceto p/ NFC que � um $id_produto_insumo ...
    ** Esse 4� par�metro $id_nfe_debitar s� � utilizado p/ NFC ...*/

    //Essa fun��o retorna o Valor de Imposto por Item ou de todos os Itens ...
    function calculo_impostos($id_negociacao_item, $id_negociacao, $tipo_negociacao = '', $id_nfe_debitar = 0) {
        if(!class_exists('genericas'))      require 'genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
        if(!class_exists('intermodular'))   require 'intermodular.php';//CASO EXISTA EU DESVIO A CLASSE ...

        $valor_total_nota_us    = 0;//Essa vari�vel s� ser� abastecida quando for pelo caminho NF de Sa�da e Estrangeira ...
        $ipi_incluso            = 'N';//Essa vari�vel s� ser� abastecida quando for pelo caminho NF de Entrada "Compras" ...
        /**********************************************Or�amento**********************************************/
        if($tipo_negociacao == 'OV') {//Or�amento de Vendas ...
            //Aqui eu busco dados do Tipo de Negocia��o ...
            $sql = "SELECT c.`id_pais`, c.`id_uf`, c.`insc_estadual`, c.`tipo_suframa`, c.`suframa_ativo`, c.`tributar_ipi_rev`, 
                    c.`optante_simples_nacional`, c.`isento_st`, ov.`id_cliente`, ov.`finalidade`, 
                    ov.`valor_frete_estimado`, ov.`artigo_isencao`, ov.`nota_sgd`, ov.`data_emissao` 
                    FROM `orcamentos_vendas` ov 
                    INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
                    WHERE ov.`id_orcamento_venda` = '$id_negociacao' LIMIT 1 ";
            $campos                     = bancos::sql($sql);
            $id_pais                    = $campos[0]['id_pais'];
            $id_uf_cliente              = $campos[0]['id_uf'];
            $insc_estadual              = $campos[0]['insc_estadual'];
            $suframa                    = $campos[0]['tipo_suframa'];
            $suframa_ativo              = $campos[0]['suframa_ativo'];
            $tributar_ipi_rev		= $campos[0]['tributar_ipi_rev'];
            $optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
            $isento_st                  = $campos[0]['isento_st'];
            $id_cliente                 = $campos[0]['id_cliente'];
            //N�o existe o campo id_empresa na parte de Or�amento, sendo assim fa�o essa adapta��o ...
            $id_empresa_negociacao      = ($campos[0]['nota_sgd'] == 'S') ? 4 : 1;
            $finalidade                 = $campos[0]['finalidade'];
            $artigo_isencao 		= $campos[0]['artigo_isencao'];
            $nota_sgd                   = $campos[0]['nota_sgd'];
            $valor_frete                = $campos[0]['valor_frete_estimado'];
            $outras_despesas_acessorias = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_cfop                    = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_nf_comp                 = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_nf_outra_comp           = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $data_emissao               = $campos[0]['data_emissao'];
            $texto_nf                   = '';//Nessa Situa��o n�o existe essa vari�vel ...
            
            if(!empty($id_negociacao_item)) $condicao = " AND ovi.`id_orcamento_venda_item` = '$id_negociacao_item' LIMIT 1 ";

            //Busca o Peso do Lote de Todos os Itens em Kg p/ utilizar mais abaixo para fazer os c�lculos ...
            $sql = "SELECT SUM(ovi.`qtde` * pa.`peso_unitario`) AS peso_lote_total_kg 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                    WHERE ovi.`id_orcamento_venda` = '$id_negociacao' ";
            $campos_lote                = bancos::sql($sql);
            $peso_lote_total_kg 	= round($campos_lote[0]['peso_lote_total_kg'], 4);
            
            //A opera��o de Fat. do PA sempre ser� Industrial quando o Cliente possuir a marca��o de Tributar IPI REV e for daqui do Brasil ...
            //Aqui eu busco detalhes espec�fico do Item ...
            $sql = "SELECT ovi.`id_produto_acabado`, ovi.`id_produto_acabado_discriminacao`, ovi.`qtde`, ovi.`preco_liq_final` AS preco_unitario, 
                    ovi.`iva`, pa.`origem_mercadoria`, pa.`peso_unitario` 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                    WHERE ovi.`id_orcamento_venda` = '$id_negociacao' $condicao ";
            $campos_itens   = bancos::sql($sql);
            $linhas_itens   = count($campos_itens);
        /**********************************************Pedido**********************************************/
        }else if($tipo_negociacao == 'PV') {//Pedido de Vendas ...
            //Aqui eu busco dados do Tipo de Negocia��o ...
            $sql = "SELECT c.`id_pais`, c.`id_uf`, c.`insc_estadual`, c.`tipo_suframa`, c.`suframa_ativo`, c.`tributar_ipi_rev`, 
                    c.`optante_simples_nacional`, c.`isento_st`, pv.`id_cliente`, pv.`id_empresa`, 
                    pv.`finalidade`, pv.`data_emissao` 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                    WHERE pv.`id_pedido_venda` = '$id_negociacao' LIMIT 1 ";
            $campos                     = bancos::sql($sql);
            $id_pais                    = $campos[0]['id_pais'];
            $id_uf_cliente              = $campos[0]['id_uf'];
            $insc_estadual              = $campos[0]['insc_estadual'];
            $suframa                    = $campos[0]['tipo_suframa'];
            $suframa_ativo              = $campos[0]['suframa_ativo'];
            $tributar_ipi_rev           = $campos[0]['tributar_ipi_rev'];
            $optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
            $isento_st                  = $campos[0]['isento_st'];
            $id_cliente                 = $campos[0]['id_cliente'];
            $id_empresa_negociacao      = $campos[0]['id_empresa'];
            $finalidade                 = $campos[0]['finalidade'];
            $nota_sgd                   = ($campos[0]['id_empresa'] == 4) ? 'S' : 'N';
            $valor_frete                = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $outras_despesas_acessorias = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_cfop                    = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_nf_comp                 = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_nf_outra_comp           = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $data_emissao               = $campos[0]['data_emissao'];
            $texto_nf                   = '';//Nessa Situa��o n�o existe essa vari�vel ...
            
            //Busca do Artigo Isen��o do Or�amento ...
            $sql = "SELECT ov.`artigo_isencao` 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                    INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                    WHERE pvi.`id_pedido_venda` = '$id_negociacao' LIMIT 1 ";
            $campos_artigo_isencao	= bancos::sql($sql);
            $artigo_isencao 		= $campos_artigo_isencao[0]['artigo_isencao'];
            
            if(!empty($id_negociacao_item)) $condicao = " AND pvi.`id_pedido_venda_item` = '$id_negociacao_item' LIMIT 1 ";

            //Busca o Peso do Lote de Todos os Itens em Kg p/ utilizar mais abaixo para fazer os c�lculos ...
            $sql = "SELECT SUM(pvi.`qtde` * pa.`peso_unitario`) AS peso_lote_total_kg 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                    WHERE pvi.`id_pedido_venda` = '$id_negociacao' ";
            $campos_lote                = bancos::sql($sql);
            $peso_lote_total_kg 	= round($campos_lote[0]['peso_lote_total_kg'], 4);

            //A opera��o de Fat. do PA sempre ser� Industrial quando o Cliente possuir a marca��o de Tributar IPI REV e for daqui do Brasil ...
            //Aqui eu busco detalhes espec�fico do Item ...
            $sql = "SELECT pvi.`id_produto_acabado`, pvi.`qtde`, pvi.`preco_liq_final` AS preco_unitario, 
                    ovi.`id_produto_acabado_discriminacao`, ovi.`iva`, pa.`origem_mercadoria`, pa.`peso_unitario` 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                    WHERE pvi.`id_pedido_venda` = '$id_negociacao' $condicao ";
            $campos_itens   = bancos::sql($sql);
            $linhas_itens   = count($campos_itens);
        /**********************************************Nota Fiscal**********************************************/	
        }else if($tipo_negociacao == 'NF') {//NF de Sa�da ou NF de Devolu��o - Setor de Faturamento ...
            //Aqui eu busco dados do Tipo de Negocia��o ...
            //Aqui eu busco dados do Tipo de Negocia��o ...
            $sql = "SELECT c.`id_pais`, c.`id_uf`, c.`insc_estadual`, c.`tipo_suframa`, c.`suframa_ativo`, c.`tributar_ipi_rev`, 
                    c.`optante_simples_nacional`, c.`isento_st`, nfs.`id_cliente`, nfs.`id_empresa`, nfs.`finalidade`, nfs.`despesas_acessorias`, 
                    nfs.`valor_frete`, nfs.`data_emissao`, nfs.`ajuste_valor_icms`, nfs.`ajuste_base_calc_icms_st`, nfs.`ajuste_valor_icms_st`, 
                    nfs.`texto_nf` 
                    FROM `nfs` 
                    INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                    WHERE nfs.`id_nf` = '$id_negociacao' LIMIT 1 ";
            $campos                     = bancos::sql($sql);
            $id_pais                    = $campos[0]['id_pais'];
            $id_uf_cliente              = $campos[0]['id_uf'];
            $insc_estadual              = $campos[0]['insc_estadual'];
            $suframa                    = $campos[0]['tipo_suframa'];
            $suframa_ativo              = $campos[0]['suframa_ativo'];
            $tributar_ipi_rev           = $campos[0]['tributar_ipi_rev'];
            $optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
            $isento_st                  = $campos[0]['isento_st'];
            $id_cliente                 = $campos[0]['id_cliente'];
            $id_empresa_negociacao      = $campos[0]['id_empresa'];
            $id_cfop                    = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_nf_comp                 = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_nf_outra_comp           = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $finalidade                 = $campos[0]['finalidade'];
            
            $vetor_nota_sgd             = genericas::nota_sgd($id_empresa_negociacao);
            $nota_sgd                   = $vetor_nota_sgd['nota_sgd'];

            $outras_despesas_acessorias = $campos[0]['despesas_acessorias'];
            $valor_frete                = $campos[0]['valor_frete'];
            $data_emissao               = $campos[0]['data_emissao'];
            $ajuste_valor_icms          = $campos[0]['ajuste_valor_icms'];
            $ajuste_base_calc_icms_st   = $campos[0]['ajuste_base_calc_icms_st'];
            $ajuste_valor_icms_st       = $campos[0]['ajuste_valor_icms_st'];
            $texto_nf                   = $campos[0]['texto_nf'];
            
            //Busca do Artigo Isen��o do Or�amento ...
            $sql = "SELECT ov.`artigo_isencao` 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                    INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                    WHERE nfsi.`id_nf` = '$id_negociacao' LIMIT 1 ";
            $campos_artigo_isencao	= bancos::sql($sql);
            $artigo_isencao 		= $campos_artigo_isencao[0]['artigo_isencao'];
            
            if(!empty($id_negociacao_item)) $condicao = " AND nfsi.`id_nfs_item` = '$id_negociacao_item' LIMIT 1 ";

            //Busca o Peso do Lote de Todos os Itens em Kg p/ utilizar mais abaixo para fazer os c�lculos ...
            $sql = "SELECT IF(nfs.`status` = '6', SUM(nfsi.`qtde_devolvida` * pa.`peso_unitario`), SUM(nfsi.`qtde` * pa.`peso_unitario`)) AS peso_lote_total_kg 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
                    WHERE nfsi.`id_nf` = '$id_negociacao' ";
            $campos_lote                = bancos::sql($sql);
            $peso_lote_total_kg 	= round($campos_lote[0]['peso_lote_total_kg'], 4);
            
            /******************************************************************/
            /*************************Pa�s Estrangeiro*************************/
            /******************************************************************/
            /*Se o pa�s for Estrangeiro, ent�o retorno esse Valor Total da Nota Fiscal em U$ porque o mesmo � 
            utilizado na tela de Itens da Nota Fiscal e nas Duplicatas do Financeiro ...*/
            if($id_pais != 31) {
                //Busca o Peso do Lote de Todos os Itens em Kg p/ utilizar mais abaixo para fazer os c�lculos ...
                $sql = "SELECT SUM(`qtde` * `valor_unitario_exp`) AS valor_total_nota_us 
                        FROM `nfs_itens` 
                        WHERE `id_nf` = '$id_negociacao' ";
                $campos_total_nota_us   = bancos::sql($sql);
                $valor_total_nota_us    = $campos_total_nota_us[0]['valor_total_nota_us'];
            }
            /******************************************************************/

            //A opera��o de Fat. do PA sempre ser� Industrial quando o Cliente possuir a marca��o de Tributar IPI REV e for daqui do Brasil ...
            //Aqui eu busco detalhes espec�fico do Item ...
            $sql = "SELECT nfsi.`id_produto_acabado`, IF(nfs.`status` = 6, nfsi.`qtde_devolvida`, nfsi.`qtde`) AS qtde, 
                    nfsi.`valor_unitario` AS preco_unitario, nfsi.`icms`, nfsi.`ipi`, nfsi.`reducao`, 
                    nfsi.`icms_intraestadual`, nfsi.`iva`, ovi.`id_produto_acabado_discriminacao`, pa.`origem_mercadoria`, pa.`peso_unitario` 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                    INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
                    WHERE nfsi.`id_nf` = '$id_negociacao' $condicao ";
            $campos_itens   = bancos::sql($sql);
            $linhas_itens   = count($campos_itens);
        /**********************************************Nota Fiscal Outra**********************************************/
        }else if($tipo_negociacao == 'NFO') {//NF Outras - Setor de Faturamento ...
            //Aqui eu busco dados do Tipo de Negocia��o ...
            $sql = "SELECT c.`id_pais`, c.`id_uf`, c.`insc_estadual`, c.`tipo_suframa`, c.`suframa_ativo`, 
                    c.`tributar_ipi_rev`, c.`optante_simples_nacional`, c.`isento_st`, 
                    nfso.`id_cliente`, nfso.`id_empresa`, nfso.`id_cfop`, 
                    nfso.`id_nf_comp`, nfso.`id_nf_outra_comp`, nfso.`finalidade`, 
                    IF(nfso.`id_empresa` = 4, 'S', 'N') AS nota_sgd, 
                    nfso.`valor_frete`, nfso.`ajuste_total_produtos`, nfso.`ajuste_total_nf`, 
                    nfso.`ajuste_ipi`, nfso.`ajuste_icms`, nfso.`data_emissao`, nfso.`texto_nf`, 
                    nfso.`base_calculo_icms_comp`, nfso.`valor_icms_comp`, 
                    nfso.`base_calculo_icms_st_comp`, nfso.`valor_icms_st_comp`, 
                    nfso.`valor_total_produtos_comp`, nfso.`valor_frete_comp`, 
                    nfso.`valor_seguro_comp`, nfso.`outras_despesas_acessorias_comp`, 
                    nfso.`valor_ipi_comp`, nfso.`valor_total_nota_comp` 
                    FROM `nfs_outras` nfso 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
                    WHERE nfso.`id_nf_outra` = '$id_negociacao' LIMIT 1 ";
            $campos                     = bancos::sql($sql);
            $id_pais                    = $campos[0]['id_pais'];
            $id_uf_cliente              = $campos[0]['id_uf'];
            $insc_estadual              = $campos[0]['insc_estadual'];
            $suframa                    = $campos[0]['tipo_suframa'];
            $suframa_ativo              = $campos[0]['suframa_ativo'];
            $optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
            $isento_st                  = $campos[0]['isento_st'];
            $id_cliente                 = $campos[0]['id_cliente'];
            $id_empresa_negociacao      = $campos[0]['id_empresa'];

            /*Se a Empresa da Nota Fiscal for 'K2', ent�o eu sempre assumo que esse campo 
            "$tributar_ipi_rev" do Cliente est� marcado, p/ que as OF(s) do PA sempre saiam 
            como Industrial ...*/
            $tributar_ipi_rev           = ($id_empresa_negociacao == 3) ? 'S' : $campos[0]['tributar_ipi_rev'];

            $id_cfop                    = $campos[0]['id_cfop'];
            $id_nf_comp                 = $campos[0]['id_nf_comp'];
            $id_nf_outra_comp           = $campos[0]['id_nf_outra_comp'];
            $finalidade                 = $campos[0]['finalidade'];
            $nota_sgd                   = $campos[0]['nota_sgd'];
            $outras_despesas_acessorias = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $valor_frete                = $campos[0]['valor_frete'];
            $ajuste_total_produtos      = $campos[0]['ajuste_total_produtos'];
            $ajuste_total_nf            = $campos[0]['ajuste_total_nf'];
            $ajuste_ipi                 = $campos[0]['ajuste_ipi'];
            $ajuste_icms                = $campos[0]['ajuste_icms'];
            $data_emissao               = $campos[0]['data_emissao'];
            $texto_nf                   = $campos[0]['texto_nf'];
            
            if(!empty($id_negociacao_item)) $condicao = " AND nfsoi.`id_nf_outra_item` = '$id_negociacao_item' LIMIT 1 ";

            //Busca o Peso do Lote de Todos os Itens em Kg p/ utilizar mais abaixo para fazer os c�lculos ...
            $sql = "SELECT SUM(qtde * peso_unitario) AS peso_lote_total_kg 
                    FROM `nfs_outras_itens` 
                    WHERE `id_nf_outra` = '$id_negociacao' ";
            $campos_lote                = bancos::sql($sql);
            $peso_lote_total_kg 	= round($campos_lote[0]['peso_lote_total_kg'], 4);

            //A opera��o de Fat. do PA sempre ser� Industrial quando o Cliente possuir a marca��o de Tributar IPI REV e for daqui do Brasil ...
            //Aqui eu busco detalhes espec�fico do Item ...
            $sql = "SELECT nfsoi.`id_produto_acabado`, nfsoi.`qtde`, nfsoi.`valor_unitario` AS preco_unitario, 
                    nfsoi.`ipi`, nfsoi.`icms`, nfsoi.`reducao`, nfsoi.`icms_intraestadual`, nfsoi.`iva`, 
                    nfsoi.`peso_unitario`, nfsoi.`imposto_importacao`, nfsoi.`valor_cif`, 
                    nfsoi.`bc_icms_item`, nfsoi.`pis`, nfsoi.`cofins`, nfsoi.`despesas_aduaneiras`, 
                    nfsoi.`despesas_acessorias`, IF('$tributar_ipi_rev' = 'S', 0, pa.`operacao`) AS operacao, 
                    pa.`origem_mercadoria`, pa.`peso_unitario` 
                    FROM `nfs_outras_itens` nfsoi 
                    LEFT JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsoi.`id_produto_acabado` 
                    WHERE nfsoi.`id_nf_outra` = '$id_negociacao' $condicao ";
            $campos_itens   = bancos::sql($sql);
            $linhas_itens   = count($campos_itens);
        /**********************************************Nota Fiscal Compras**********************************************/
        }else if($tipo_negociacao == 'NFC') {//NF de Compras "Entrada" - Setor de Compras ...
            //Aqui eu busco dados do Tipo de Negocia��o ...
            $sql = "SELECT f.`id_uf`, f.`id_pais`, f.`optante_simples_nacional`, nfe.`id_fornecedor`, 
                    nfe.`finalidade`, IF(nfe.`tipo` = '1', 'N', 'S') AS nota_sgd, 
                    SUBSTRING(nfe.`data_emissao`, 1, 10) AS data_emissao 
                    FROM `nfe` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                    WHERE nfe.`id_nfe` = '$id_negociacao' LIMIT 1 ";
            $campos                     = bancos::sql($sql);
            $id_uf_fornecedor           = $campos[0]['id_uf'];
            $id_pais                    = $campos[0]['id_pais'];
            $optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
            $isento_st                  = 'N';//Nessa Situa��o n�o existe essa vari�vel ...
            $finalidade                 = $campos[0]['finalidade'];
            $nota_sgd                   = $campos[0]['nota_sgd'];
            $data_emissao               = $campos[0]['data_emissao'];
            
            //Busca o Peso do Lote de Todos os Itens em Kg p/ utilizar mais abaixo para fazer os c�lculos ...
            $sql = "SELECT SUM(nfeh.`qtde_entregue` * pi.`peso`) AS peso_lote_total_kg 
                    FROM `nfe_historicos` nfeh 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = nfeh.`id_produto_insumo` 
                    WHERE nfeh.`id_nfe` = '$id_negociacao' ";
            $campos_lote                = bancos::sql($sql);
            $peso_lote_total_kg 	= round($campos_lote[0]['peso_lote_total_kg'], 4);
            
            if(!empty($id_negociacao_item)) $condicao = " AND `id_nfe_historico` = '$id_negociacao_item' LIMIT 1 ";
/*Esse par�metro "$id_nfe_debitar" traz da Nota Fiscal passada por par�metro, itens que foram atrelados a esta 
e esses itens est�o vinculados � algum N.� de Nota Fiscal ...
 

Exemplo Real: 

Temos uma Nota Fiscal Principal do Fornecedor "ESPACIAL" 273133 que tem 11 itens:
 
    *** 2 desses itens ser�o debitados para K2 onde cada um desses tem um N.� de Nota Fiscal diferente ...
    *** O primeiro � da Nota Fiscal Fiscal "273133" da K2 ...
    *** O segundo � da Nota Fiscal Fiscal "4217" da K2 ...

Se dentro do sistema estamos acessando a Nota Fiscal "273133" da K2, por mais que o sistema perceba que a Nota Fiscal 
do Fornecedor "ESPACIAL" � correlata e exiba a mesma na Tela de itens, o Financiamento que fica dentro do Cabe�alho 
s� ser� calculado p/ "K2" na Nota Fiscal "273133" em cima da Nota Fiscal Principal "ESPACIAL" em que os itens 
dessa Nota Fiscal tenham a marca��o de debitados com o mesmo N.� "273133" da K2 ...*/
            if($id_nfe_debitar > 0) $condicao_itens = " AND `id_nfe_debitar` = '$id_nfe_debitar' ";

            //Aqui eu busco detalhes espec�fico do Item, obs: n�o existe IPI p/ id_empresa = '4' que � "Grupo" ...
            $sql = "SELECT nfeh.`id_produto_insumo`, nfeh.`cod_tipo_ajuste`, nfeh.`qtde_entregue` AS qtde, 
                    nfeh.`valor_entregue` AS preco_unitario, 
                    IF(nfe.`id_empresa` = 4, '0', nfeh.`ipi_entregue`) AS ipi, nfeh.`ipi_incluso`, 
                    nfeh.`icms_entregue` AS icms, nfeh.`reducao`, nfeh.`iva` 
                    FROM `nfe_historicos` nfeh 
                    INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
                    WHERE nfeh.`id_nfe` = '$id_negociacao' $condicao $condicao_itens ";
            $campos_itens   = bancos::sql($sql);
            $linhas_itens   = count($campos_itens);
        }else if($tipo_negociacao == 'SN') {//Sem Negocia��o, nesse caso n�o existe e sendo assim fa�o uma atribui��o manual p/ as vari�veis ...
            $id_pais                    = 31;//Brasil ...
            $id_uf_cliente              = 1;//Estado de S�o Paulo
            $tributar_ipi_rev		= 'N';//Nessa Situa��o n�o existe essa vari�vel ...
            $optante_simples_nacional   = 'N';//Nessa Situa��o n�o existe essa vari�vel ...
            $isento_st                  = 'N';//Nessa Situa��o n�o existe essa vari�vel ...
            $suframa                    = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $suframa_ativo              = 'N';//Nessa Situa��o n�o existe essa vari�vel ...
            $finalidade                 = 'R';
            $artigo_isencao 		= 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $nota_sgd                   = 'N';
            $valor_frete                = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $outras_despesas_acessorias = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_cfop                    = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_nf_comp                 = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $id_nf_outra_comp           = 0;//Nessa Situa��o n�o existe essa vari�vel ...
            $data_emissao               = date('Y-m-d');
            $texto_nf                   = '';//Nessa Situa��o n�o existe essa vari�vel ...
            
            //Busca o Peso do Lote de Todos os Itens em Kg p/ utilizar mais abaixo para fazer os c�lculos ...
            $sql = "SELECT `peso_unitario` AS peso_lote_total_kg 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_negociacao_item' ";
            $campos_lote        = bancos::sql($sql);
            $peso_lote_total_kg = round($campos_lote[0]['peso_lote_total_kg'], 4);
            
            //A opera��o de Fat. do PA sempre ser� Industrial independente de possuir a marca��o de Tributar IPI REV ...
            //Aqui eu busco detalhes espec�fico do Item ...
            $sql = "SELECT `id_produto_acabado`, `origem_mercadoria`, `peso_unitario` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_negociacao_item' ";
            $campos_itens   = bancos::sql($sql);
            $linhas_itens   = count($campos_itens);
        }
/***********************************************************************************************************/
/**********************************************NF Complementar**********************************************/
/***********************************************************************************************************/
/*Significa que foi feito uma NF Complementar e sendo assim � s� eu retornar os campos complementares que j� 
foram gravados na NF anteriormente ...*/ 
        if($id_nf_comp > 0 || $id_nf_outra_comp > 0) {
            $base_calculo_icms                  = $campos[0]['base_calculo_icms_comp'];
            $valor_icms                         = $campos[0]['valor_icms_comp'];
            $base_calculo_icms_st               = $campos[0]['base_calculo_icms_st_comp'];
            $valor_icms_st                      = $campos[0]['valor_icms_st_comp'];
            $valor_total_produtos               = $campos[0]['valor_total_produtos_comp'];
            $valor_frete                        = $campos[0]['valor_frete_comp'];
            $outras_despesas_acessorias         = $campos[0]['outras_despesas_acessorias_comp'];
            $valor_ipi                          = $campos[0]['valor_ipi_comp'];
            $valor_total_nota                   = $campos[0]['valor_total_nota_comp'];
        }else {//Foi feito qualquer outro Tipo de NF ...
/****************************************************************************************************/
/***********************Procedimento gen�rico independente das situa��es acima***********************/
/****************************************************************************************************/
            if($nota_sgd == 'N' && $id_cfop != 0) {//Se a Empresa for Alba ou Tool e existir CFOP ...
                $sql = "SELECT CONCAT(cfop, '.', num_cfop) AS cfop, natureza_operacao_resumida 
                        FROM `cfops` 
                        WHERE `id_cfop` = '$id_cfop' LIMIT 1 ";
                $campos_cfop        = bancos::sql($sql);
                $cfop               = $campos_cfop[0]['cfop'];
                $natureza_operacao  = $campos_cfop[0]['natureza_operacao_resumida'];
            }
/************************************************************************************************************/
/********************************** CFOP 3.101 - Compra p/ Industrializa��o *********************************/
/************************************************************************************************************/
            //Somente nessas CFOP que os c�lculos s�o totalmente diferenciados ...
            if($cfop == '3.101' || $cfop == '3.102') {
                for($i = 0; $i < $linhas_itens; $i++) {
                    $base_calculo_icms+=    $campos_itens[$i]['bc_icms_item'];
                    $pis+=                  $campos_itens[$i]['pis'];
                    $cofins+=               $campos_itens[$i]['cofins'];
                    $despesas_aduaneiras+=  $campos_itens[$i]['despesas_aduaneiras'];
                    $outras_despesas_acessorias+= $campos_itens[$i]['despesas_acessorias'];

                    $icms_item_current_rs   = round(($campos_itens[$i]['bc_icms_item'] * $campos_itens[$i]['icms'] / 100) * ((100 - $campos_itens[$i]['reducao']) / 100), 2);
                    $valor_icms+=           $icms_item_current_rs;

                    $ii_item_current_rs     = round($campos_itens[$i]['valor_cif'] * $campos_itens[$i]['imposto_importacao'] / 100, 2);
                    
                    $ipi_item_current_rs    = round((($campos_itens[$i]['valor_cif'] + $ii_item_current_rs) * $campos_itens[$i]['ipi'] / 100), 2);
                    $total_ipi_itens_rs+=   $ipi_item_current_rs;

                    $valor_total            = round($ii_item_current_rs + $campos_itens[$i]['valor_cif'], 2);
                    $valor_total_produtos+= $valor_total;
                    
                    $valor_total_mais_impostos  = round($valor_total + $icms_item_current_rs + $ipi_item_current_rs, 2);
                    $valor_total_nota+= $valor_total_mais_impostos;
                }
                $valor_total_produtos+= $ajuste_total_produtos;
                $valor_ipi              = $total_ipi_itens_rs + $ajuste_ipi;
                $valor_icms+=           $ajuste_icms;
                
                $valor_total_nota+=     $valor_frete + $outras_despesas_acessorias + $ajuste_total_nf + $pis + $cofins + $despesas_aduaneiras;
/************************************************************************************************************/
/******************************************** Qualquer outra CFOP *******************************************/
/************************************************************************************************************/
            }else {
                for($i = 0; $i < $linhas_itens; $i++) {
                    /*Esse controle � de extrema import�ncia porque em casos de "Gato por Lebre", preciso pegar os impostos do Gato ...

                    Ex: o cliente comprou MRH-042 "Gato", mas estamos enviando o MRT-042 "Lebre ou substituto" ...*/
                    $id_produto_acabado_utilizar = (!empty($campos_itens[$i]['id_produto_acabado_discriminacao'])) ? $campos_itens[$i]['id_produto_acabado_discriminacao'] : $campos_itens[$i]['id_produto_acabado'];
                    
                    if($tipo_negociacao == 'OV' || $tipo_negociacao == 'PV' || $tipo_negociacao == 'SN') {//Essas s�o as 3 �nicas situa��es que n�o gravamos os impostos na tabela de Itens ...
                        $dados_produto 		= intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf_cliente, $id_cliente, $id_empresa_negociacao, $finalidade);
                        $id_classific_fiscal    = $dados_produto['id_classific_fiscal'];
                        $ipi                    = $dados_produto['ipi'];
                        $icms                   = $dados_produto['icms'];
                        $reducao                = $dados_produto['reducao'];
                        $icms_intraestadual     = $dados_produto['icms_intraestadual'];
                        $fecp                   = $dados_produto['fecp'];
                        
                        //No or�amento e no Pedido, n�s gravamos essa vari�vel ...
                        if($tipo_negociacao == 'OV' || $tipo_negociacao == 'PV') {
                            $iva                = $campos_itens[$i]['iva'];
                        }else {
                            $iva                = $dados_produto['iva'];
                        }
                    }else {
                        $dados_produto 		= intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf_cliente, $id_cliente, $id_empresa_negociacao, $finalidade);
                        $id_classific_fiscal    = $dados_produto['id_classific_fiscal'];
                        $ipi                    = $campos_itens[$i]['ipi'];
                        $ipi_incluso            = $campos_itens[$i]['ipi_incluso'];//Esse campo s� existe p/ NF de Entrada "Compras" ...
                        $icms                   = $campos_itens[$i]['icms'];
                        $reducao                = $campos_itens[$i]['reducao'];
                        $icms_intraestadual     = $campos_itens[$i]['icms_intraestadual'];
                        $fecp                   = $dados_produto['fecp'];
                        $iva                    = $campos_itens[$i]['iva'];
                    }
                    /**************************************************************************************************/
                    /*Esse tratamento est� sendo feito por causa do Tipo de Negocia��o = 'SN' que � Sem Negocia��o, 
                    somente nesse caso que essas vari�veis ser�o nulas e fa�o isso p/ n�o dar erro mais abaixo 
                    c/ o desenrolar dos c�lculos ...*/
                    $qtde           = (!empty($campos_itens[$i]['qtde'])) ? $campos_itens[$i]['qtde'] : 1;
                    $preco_unitario = (!empty($campos_itens[$i]['preco_unitario'])) ? $campos_itens[$i]['preco_unitario'] : 100;
                    /**************************************************************************************************/
                    
                    //C�lculos gen�ricos independente das situa��es acima ...
                    $valor_total                = round($qtde * $preco_unitario, 2);
                    //Aqui eu tenho o Peso do Lote de Todos em Kg do �nico item em espec�fico ...
                    $peso_lote_item_current_kg  = $qtde * $campos_itens[$i]['peso_unitario'];

                    /*C�lculo p/ achar o "Frete + Desp. Acess�rias" do Item corrente, em cima do "Frete Total em R$ + 
                    Despesas Acess�rias em R$", achar o "Frete Individual + Despesas Acess�rias Individual", 
                    achar a sua fatia dentro do Total ...*/
                    $frete_despesas_acessorias_item_current_rs = (($valor_frete + $outras_despesas_acessorias) * $peso_lote_item_current_kg);

                    //P/ evitar erro de Divis�o por zero, s� existe a Divis�o se > 0
                    if($peso_lote_total_kg > 0) $frete_despesas_acessorias_item_current_rs/= $peso_lote_total_kg;
                    
                    /***********************************************************************************************/
                    /*****************************************C�lculo de IPI****************************************/
                    /***********************************************************************************************/
                    if($ipi_incluso == 'S') {//Somente na NF de Compras "Entrada" que existir� esse campo ...
                        $ipi_incluso_item_current_rs = round(($ipi / 100) * $valor_total, 2);//C�lculo o Valor do IPI em R$ ...
                        $total_ipi_incluso_itens_rs+= $ipi_incluso_item_current_rs;
                        $ipi_frete_despesas_acessorias_item_current_rs  = 0;
                    }else {
                        if($ipi == '0.00') {//Se n�o existir IPI para o Item corrente ...
                            $ipi_item_current_rs                            = 0;
                            $ipi_frete_despesas_acessorias_item_current_rs  = 0;
                        }else {//Existe algum IPI ...
                            $ipi_item_current_rs = round(($ipi / 100) * $valor_total, 2);//C�lculo o Valor do IPI em R$ ...
                            $total_ipi_itens_rs+= $ipi_item_current_rs;
                            $ipi_frete_despesas_acessorias_item_current_rs  = ($ipi / 100) * $frete_despesas_acessorias_item_current_rs;
                        }
                    }
                    
                    //Acumula o Total de todos os IPI(s) Frete Desp Acess�rias em R$ ...
                    $ipi_frete_desp_aces_todos_itens+= $ipi_frete_despesas_acessorias_item_current_rs;

                    if($icms == '0.00') {//Se n�o existir ICMS para o Item corrente ...
                        $icms_frete_despesas_acessorias_item_current_rs = 0;
                    }else {
                        //Quando for CONSUMO, tem de somar o Valor de IPI do Frete do Item no c�lculo do Icms do Frete ...
                        if($finalidade == 'C') {
                            $icms_frete_despesas_acessorias_item_current_rs = (($frete_despesas_acessorias_item_current_rs + $ipi_frete_despesas_acessorias_item_current_rs) * ($icms / 100));
                        }else {//Do contr�rio n�o precisa ...
                            $icms_frete_despesas_acessorias_item_current_rs = (($frete_despesas_acessorias_item_current_rs) * ($icms / 100));
                        }
                        //Obs: Se existir redu��o, ent�o eu preciso aplicar est� no ICMS do Frete + DA do Item ...
                        if($reducao != '0.00') $icms_frete_despesas_acessorias_item_current_rs*= (100 - $reducao) / 100;
                    }

                    //Acumula o Total de todos os ICMS(s) Frete Desp Acess�rias em R$ ...
                    $icms_frete_desp_aces_todos_itens+= $icms_frete_despesas_acessorias_item_current_rs;

                    /*Criei essa vari�vel de 'ipi_ST' pois preciso desse valor para os c�lculos de ST + abaixo. Lembrando que as vari�veis de IPI s�o zeradas 
                    por causa das Bases de C�lculo ...*/
                    $ipi_item_current_para_st_rs = $ipi_item_current_rs + $ipi_frete_despesas_acessorias_item_current_rs + $frete_despesas_acessorias_item_current_rs;
                    
                    //Essa vari�vel ser� utilizada somente no "TXT" de nossas notas fiscais Eletr�nicas ...
                    $ipi_frete_despesas_acessorias_txt = $ipi_frete_despesas_acessorias_item_current_rs;

                    /*Verifico a Finalidade da Nota Fiscal - sempre que a NF for "REVENDA" ou "INDUSTRIALIZA��O" 
                    eu zero o valor dessas vari�veis que foi calculado anteriormente, porque ir� influenciar 
                    nos resultados de bases de c�lculo ...

                    Revenda ou Industrializa��o n�o caracteriza fato gerador de IPI ...*/
                    if($finalidade == 'R' || $finalidade == 'I') {
                        $ipi_item_current_rs                            = 0;
                        $ipi_frete_despesas_acessorias_item_current_rs  = 0;
                    }
                    $valor_total_produtos+= $valor_total;

                    $dados_produto              = intermodular::dados_impostos_pa($id_produto_acabado_utilizar, $id_uf_cliente, $id_cliente, $id_empresa_negociacao, $finalidade);
                    $operacao                   = $dados_produto['operacao'];
                    $situacao_tributaria        = $dados_produto['situacao_tributaria'];
                    $desconto_pis_cofins_icms   = $dados_produto['desconto_pis_cofins_icms'];

                    if($reducao != '0.00') {//Quando o item tiver Redu��o B. C.
                        /**********C�digo Adaptado no dia 11/02/2014 - D�rcio
                        Sempre que a Situa��o do PA vs UF for = 60 'ICMS cobrado anteriormente por substitui��o 
                        tribut�ria' o Cliente n�o tem direito de se Creditar de ICMS ...

                        * Quando a Tipo de Negocia��o = "Sem Negocia��o", nunca podemos zerar essa vari�vel 
                        $icms_item_current_rs porque sen�o teremos problemas para calcular o valor_icms_st ...*/
                        /*if($situacao_tributaria == '60' && $tipo_negociacao != 'SN') {
                            $icms_item_current_rs = 0;
                        }else {*/
                            if($finalidade == 'C') {
                                $icms_item_current_rs = (($valor_total + $ipi_item_current_rs) * $icms / 100 * (100 - $reducao) / 100) + $icms_frete_despesas_acessorias_item_current_rs;
                            }else {
                                $icms_item_current_rs = ($valor_total * $icms / 100 * (100 - $reducao) / 100) + $icms_frete_despesas_acessorias_item_current_rs;
                            }
                        //}
                        /*Devido as novas leis de ST, ent�o eu s� terei as Bases de C�lculo  
                        1) Quando possuir iva em S�o Paulo + somente com o PA de Op. Fat = 'Ind'
                        2) Quando possuir iva em qualquer outro Estado n�o importa a OF do PA ...*/
                        if($iva == 0 || ($iva > 0 && $operacao == 0 && $id_uf_cliente == 1) || ($iva > 0 && $id_uf_cliente > 1)) {
                            //C�lculo com Redu��o � em cima do Total dos Itens e do IPI dos Item ...
                            $base_calculo_icms_c_red = (($frete_despesas_acessorias_item_current_rs + $valor_total) * (100 - $reducao) / 100);
                            //Somente quando a Finalidade da NF for Igual � CONSUMO, que acrescenta o IPI do Frete R$ + IPI do Item em R$ ...
                            if($finalidade == 'C') $base_calculo_icms_c_red+= (($ipi_frete_despesas_acessorias_item_current_rs + $ipi_item_current_rs) * (100 - $reducao) / 100);
                        }
                        $base_calculo_icms_item_rs = $base_calculo_icms_c_red;
                   }else {
                        /**********C�digo Adaptado no dia 11/02/2014 - D�rcio
                        Sempre que a Situa��o do PA vs UF for = 60 'ICMS cobrado anteriormente por substitui��o 
                        tribut�ria' o Cliente n�o tem direito de se Creditar de ICMS ...

                        * Quando a Tipo de Negocia��o = "Sem Negocia��o", nunca podemos zerar essa vari�vel 
                        $icms_item_current_rs porque sen�o teremos problemas para calcular o valor_icms_st ...*/
                        /*if($situacao_tributaria == '60' && $tipo_negociacao != 'SN') {
                            $icms_item_current_rs = 0;
                        }else {*/
                            if($finalidade == 'C') {
                                $icms_item_current_rs = (($valor_total + $ipi_item_current_rs) * $icms / 100 + $icms_frete_despesas_acessorias_item_current_rs);
                            }else {
                                $icms_item_current_rs = ($valor_total * $icms / 100 + $icms_frete_despesas_acessorias_item_current_rs);
                            }
                        //}

                        /*Devido as novas leis de ST, ent�o eu s� terei as Bases de C�lculo 
                        1) Quando n�o tiver o IVA 
                        2) Quando possuir iva em S�o Paulo + somente com o PA de Op. Fat = 'Ind'
                        3) Quando possuir iva em qualquer outro Estado n�o importa a OF do PA ...*/
                        if($iva == 0 || ($iva > 0 && $operacao == 0 && $id_uf_cliente == 1) || ($iva > 0 && $id_uf_cliente > 1)) {
                            /*Aqui eu verifico a Classifica��o Fiscal do Produto Corrente, se no caso for id_4 ou id_6 
                            que equivale a Classifa��o Fiscal 82.07.90.00 ou 90.17.20.00, ir� abastecer a vari�vel 
                            $base_calculo_icms_bits_bedames_riscador ...*/
                            if($id_classific_fiscal == 4 || $id_classific_fiscal == 6) {//82.07.90.00 ou 90.17.20.00 ...
                                $base_calculo_icms_bits_bedames_riscador    = $valor_total + $frete_despesas_acessorias_item_current_rs + $ipi_frete_despesas_acessorias_item_current_rs + $ipi_item_current_rs;
                                $base_calculo_icms_item_rs                  = $base_calculo_icms_bits_bedames_riscador;
                                /*Esse desvio ser� muito raro de acontecer, pois n�o ser� mais feito uma Nota de Conserto 
                                junto com uma Nota Fiscal de Venda, s� aconteceu no in�cio p/ a Nota 4333 da Albaf�r ...*/
                            }else if($id_classific_fiscal == 14) {//00.00.00.00 - Isento de M�o de Obra ...
                                $isento+= $valor_total + $frete_despesas_acessorias_item_current_rs + $ipi_frete_despesas_acessorias_item_current_rs + $ipi_item_current_rs;
                            }else {//Outra Classifica��o fiscal ...
                                $base_calculo_icms_s_red                    = $valor_total + $frete_despesas_acessorias_item_current_rs + $ipi_frete_despesas_acessorias_item_current_rs + $ipi_item_current_rs;
                                $base_calculo_icms_item_rs                  = $base_calculo_icms_s_red;
                            }
                        }
                    }
                    /***********************************************************************************************/
                    /***********************Atualiza��o do ICMS � Creditar nas NF(s) de Sa�da***********************/
                    /***********************************************************************************************/
                    /*Regras 

                    * 1) Se existir Valor de IVA em R$ ...
                    * 2) Somente p/ Empresa Alba ou Tool ...
                    * 3) NF(s) com Data de Emiss�o >= 01/04/2009 que foi quando come�ou a vigorar a Nova Lei de ST ...
                    * 4) Opera��o de Faturamento do PA = 'Revenda' 
                    * 5) Notas Fiscais = 'CONSUMO' / 'INDUSTRIALIZA��O' / 'REVENDA' mais que sejam diferentes de S�o Paulo ...

                    Muito prov�vel que este campo `icms_creditar_rs` s� seja utilizado no Relat�rio de Balan�o 
                    de Total de Impostos NFs vs NFe ...*/
                    if($tipo_negociacao == 'NF') {//Somente quando for Nota Fiscal que far� essa Rotina ...
                        if($iva > 0 && $nota_sgd == 'N' && $data_emissao >= '2009-04-01' && $operacao == 1 && ($finalidade == 'C' || $finalidade == 'I' || ($finalidade == 'R' && $id_uf_cliente > 1))) {
                            $sql = "UPDATE `nfs_itens` SET `icms_creditar_rs` = '$icms_item_current_rs' WHERE `id_nf` = '$id_negociacao' AND `id_produto_acabado` = '".$campos_itens[$i]['id_produto_acabado']."' LIMIT 1 ";
                            bancos::sql($sql);
                        }
                    }
                    /***********************************************************************************************/
                    /*************S� existir� o c�lculo de Substitui��o Tribut�ria (ST) se existir o IVA************/
                    /***********************************************************************************************/
                    if($tipo_negociacao == 'NFC') {//NF de Compras "Entrada" aqui o c�lculo � um pouco diferente ...
                        /*Porque quem emite a Nota Fiscal � o Fornecedor que pode ser Optante pelo Simples Nacional 
                        de outra UF e que exigem c�lculos particularizados incluindo at� uma Maracutaia que temos 
                        com a Contabilidade p/ se Creditar de Imposto ...*/
                        
                        /*Na NF de Compra a Base de C�lculo de ICMS � calculada de outro modo, por isso que eu 
                        zero as vari�veis $base_calculo_icms_c_red, $base_calculo_icms_s_red, 
                        $base_calculo_icms_bits_bedames_riscador ...*/
                        $base_calculo_icms_c_red                    = 0;
                        $base_calculo_icms_s_red                    = 0;
                        $base_calculo_icms_bits_bedames_riscador    = 0;
                        
                        if($optante_simples_nacional == 'S') {//Se for optante Simples Nacional ...
                            /*Verifica se o PI possui IVA, mas na minha vis�o n�o precisar�amos fazer esse SQL devido 
                            termos o IVA j� gravado na Tabela de Itens de Nota Fiscal, talvez poderia dar erro exemplo: 
                            Nota Fiscal 288 "Intertaps" tem 52,19 de IVA e quando puxa do SQL n�o vem nada - 12/03/14 ...*/
                            $sql = "SELECT icms.iva 
                                    FROM `produtos_insumos` pi 
                                    INNER JOIN `icms` ON icms.`id_classific_fiscal` = pi.`id_classific_fiscal` AND icms.`id_uf` = '$id_uf_fornecedor' 
                                    WHERE pi.`id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo']."' LIMIT 1 ";
                            $campos_pi = bancos::sql($sql);
                            /*Se for Optante pelo Simples e existir IVA n�o temos valor de ICMS, importante lembrarmos 
                            isso porque essa vari�vel est� sendo utilizada pelo $icms_item_current_oculto_creditar_rs ...*/
                            if($campos_pi[0]['iva'] > 0) $icms_item_current_rs = 0;
                        }
                        $icms_item_current_oculto_creditar_rs = $icms_item_current_rs;
                        
                        //C�lculo do IVA s� existir� p/ "NFs" que s�o do Tipo NF mesmo e que possuem Valor de Iva ...
                        if($iva > 0 && $nota_sgd == 'N') {
                            /*Pode parecer uma redund�ncia essa $icms_item_current_rs = 0, mas a situa��o aqui � 
                            diferente porque agora essa vari�vel � utilizada nos c�lculos de ST ... */
                            $icms_item_current_rs = 0;
                            //Verifica se o PA � um PI "PRAC" ...
                            $sql = "SELECT `id_produto_acabado` 
                                    FROM `produtos_acabados` 
                                    WHERE `id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo']."' 
                                    AND `id_produto_insumo` > '0' LIMIT 1 ";
                            $campos_pipa = bancos::sql($sql);
/*Se o PI for PRAC que � um PI de Revenda ou o PI for um Ajuste do Tipo Produto Acabado, 
ent�o existe c�lculo p/ os campos de ST - Base ST e ICMS ST ...*/
                            if(count($campos_pipa) == 1 || ($campos_itens[$i]['id_produto_insumo'] == 1340 && $campos_itens[$i]['cod_tipo_ajuste'] == 5)) {//1340 � o id do PI que � Ajuste ...
                                //N�o estamos levando em conta o Frete no c�lculo abaixo, porque atualmente o Frete � um PI ...
                                $base_calculo_icms_st_item_current_rs = ($valor_total + $ipi_item_current_rs) * (1 + $iva / 100);
                                if($optante_simples_nacional == 'S') {
                                    if($data_emissao <= '2009-07-31') {
                                        $abatimento_icms_st_para_simples = 7;//S� existe p/ Simples Nacional ...
                                    }else {//A partir do m�s de agosto mudou a Regra com Rela��o a esse Valor ...
                                        $abatimento_icms_st_para_simples = $icms;//S� existe p/ Simples Nacional ...
                                    }
                                    $valor_icms_st_item_current_rs = $base_calculo_icms_st_item_current_rs * ($icms / 100) - ($abatimento_icms_st_para_simples / 100) * $valor_total;
                                }else {
                                    /*Estamos mantendo o icms_item_rs na f�rmula abaixo, pois caso o fornecedor seja 
                                    uma ind�stria existir� esse ICMS, mesmo que o item possua IVA, 
                                    nos casos de Revenda o ICMS � Zerado ...*/
                                    $valor_icms_st_item_current_rs = $base_calculo_icms_st_item_current_rs * ($icms / 100) - $icms_item_current_rs;
                                }
                            }else {//N�o � PRAC e nem Ajuste de "Produto Acabado", apenas PI ...
                                $valor_icms_oculto_creditar+= $icms_item_current_oculto_creditar_rs;//Esse ICMS oculto s� existir� quando o PI for PI mesmo ...
                                
                                $base_calculo_icms_st_item_current_rs   = 0;
                                $valor_icms_st_item_current_rs          = 0;
                            }
                            //Acumula o Total de Todas as vari�veis referentes ao ST ...
                            $base_calculo_icms_st+= $base_calculo_icms_st_item_current_rs;
                            $valor_icms_st+= $valor_icms_st_item_current_rs;
                        }else {//Somente quando n�o existir o IVA ...
                            /*Obs: Tive que Criar essa vari�vel $base_calculo_icms_em_compras espec�fica s� p/ Compras, 
                            devido os c�lculos p/ essa Transa��o serem diferentes ... 
                            Por enquanto � o Valor Total do Item, caso seja CONSUMO � necess�rio somar o IPI do Item 
                            em R$, s� existir� no caso de a NF for do Tipo NF mesmo ...*/
                            if($nota_sgd == 'N' && $icms > 0) {
                                if($finalidade == 'C') {//CONSUMO ...
                                    $base_calculo_icms_em_compras = $valor_total + $ipi_item_current_rs;
                                }else {
                                    $base_calculo_icms_em_compras = $valor_total;
                                }
                            }
                            /*****************C�lculo para Redu��o de Base de C�lculo*****************/
                            $reducao_compras                = ($campos_itens[$i]['reducao'] > 0) ? $valor_total * ($campos_itens[$i]['reducao'] / 100) : 0;
                            $base_calculo_icms_em_compras-= $reducao_compras;
                            /*************************************************************************/
                            $base_calculo_icms_item_rs      = $base_calculo_icms_em_compras;
                        }
                    }else {//Esse calculo � o Padr�o independente do Tipo de Nota ...
                        /******************************************************************************/
                        /*Devido as novas leis de ST, ent�o eu s� terei as Bases de C�lculo 

                        1) Se existir Al�quota de IVA p/ o Item Corrente ...
                        2) Se o Cliente realmente quiser que seja tributado o IVA, pq hoje em dia muitos possuem 
                        uma "credencial" que isentam o Cliente de pagar esse Imposto ...
                        3) Quando a comercializa��o s� for do Tipo NF mesmo "Alba ou Tool" ...
                        4) Quando for negociado REVENDA, p/ CONSUMO ou INDUSTRIALIZA��O n�o existe ...
                        5) Quando possuir iva em S�o Paulo + somente com o PA de Op. Fat = 'Ind' ou Op. 'Rev' desde que sejam nas Origens de Mercadoria 1, 3, 5, 6 e 8 "em que somos Substitutos por industrializarmos ou n�o termos adquirido o Produto com Recolhimento de ST" ...
                        6) Quando possuir iva em qualquer outro Estado n�o importa a OF do PA ...*/
                        if($iva > 0 && $isento_st == 'N' && $nota_sgd == 'N' && $finalidade == 'R' && ($id_uf_cliente == 1 && ($operacao == 0 || $operacao == 1 && $campos_itens[$i]['origem_mercadoria'] == 1 || $campos_itens[$i]['origem_mercadoria'] == 3 || $campos_itens[$i]['origem_mercadoria'] == 5 || $campos_itens[$i]['origem_mercadoria'] == 6 || $campos_itens[$i]['origem_mercadoria'] == 8) || $id_uf_cliente > 1)) {
                            //C�lculo do IVA - Base de C�lculo ICMS ST por Item de NF ...
                            $vetor_dados_substituicao_tributaria = self::calculos_substituicao_tributaria($ipi_item_current_para_st_rs, $icms, $icms_intraestadual, $fecp, $iva, $valor_total, $icms_item_current_rs);
                            
                            //Acumula o Total de Todas as vari�veis referentes ao ST ...
                            $base_calculo_icms_st+= $vetor_dados_substituicao_tributaria['base_calculo_icms_st_item_current_rs'];
                            $valor_icms_st+=        $vetor_dados_substituicao_tributaria['valor_icms_st_item_current_rs'];
                            
                            if($fecp > 0) {
                                $fecp_item              = $fecp;//� a pr�pria Al�quota FECP do Estado ...
                                $base_calculo_fecp_item = $vetor_dados_substituicao_tributaria['base_calculo_icms_st_item_current_rs'];
                                $valor_fecp_item        = number_format(round($base_calculo_fecp_item * ($fecp_item / 100), 2), 2, '.', '');

                                $valor_fecp+=           $valor_fecp_item;
                            }else {
                                $valor_fecp             = 0;
                            }
                        }
                    }
                    /***********************************************************************************************/
                    $base_calculo_icms+=    $base_calculo_icms_item_rs;
                    $valor_icms+=           $icms_item_current_rs;
                    /***********************************************************************************************/
                    /********************************************SUFRAMA********************************************/
                    /***********************************************************************************************/
                    if($desconto_pis_cofins_icms > 0) {
                        //O c�lculo de Desc. do Suframa c/ o Pr�-Rata do Item sai + exato ...
                        $desconto+= (-1) * round(($qtde * $preco_unitario) * (($desconto_pis_cofins_icms) / 100), 2);
                    }
                    /***********************************************************************************************/
                    /*********************************************DIFAL*********************************************/
                    /***********************************************************************************************/
                    //Quando o Cliente n�o tiver Inscri��o Estadual e este sempre fora de SP ...
                    if((empty($insc_estadual) || $insc_estadual == 0) && $id_uf_cliente > 1) {
                        $base_calculo_icms_uf_destino   = ($base_calculo_icms_item_rs * $campos_itens[$i]['icms_intraestadual'] / 100);
                        $base_calculo_icms_uf_remetente = ($base_calculo_icms_item_rs * $campos_itens[$i]['icms'] / 100);

                        $difal_item_current_rs          = $base_calculo_icms_uf_destino - $base_calculo_icms_uf_remetente;
                        $difal+= $difal_item_current_rs;

                        //At� o ano passado 2017, a divis�o era de 60% e 40% ...
                        $valor_icms_destino_item_rs     = $difal_item_current_rs * 0.80;//O Cliente ir� pagar 80% do ICMS ...
                        $valor_icms_remetente_item_rs   = $difal_item_current_rs * 0.20;//O Cliente ir� pagar 20% do ICMS ...
                        
                        $valor_icms_destino+=           $valor_icms_destino_item_rs;
                        $valor_icms_remetente+=         $valor_icms_remetente_item_rs;
                    }
                }
                //Aqui eu arredondo os valores ...
                $base_calculo_icms                          = round($base_calculo_icms, 2);
                $valor_icms                                 = round($valor_icms, 2);
                $base_calculo_icms_st                       = round($base_calculo_icms_st, 2);
                $valor_icms_st                              = round($valor_icms_st, 2);
                $base_calculo_ipi                           = round($valor_total + $frete_despesas_acessorias_item_current_rs, 2);
                
                if($suframa > 0 || ($nota_sgd == 'S') || $id_pais != 31) {
                    $frete_ipi                          = 0;
                    $despesas_acessorias_ipi            = 0;
                }else {
                    $frete_ipi                          = ($valor_frete * ($ipi / 100));
                    $despesas_acessorias_ipi            = ($outras_despesas_acessorias * ($ipi / 100));
                }
                $valor_ipi          = $total_ipi_itens_rs + $ipi_frete_desp_aces_todos_itens;
                $valor_ipi_incluso  = $total_ipi_incluso_itens_rs / 2;//Essa divis�o por 2 � porque s� temos direito de creditar 50% quando o IPI � Incluso ...
                
                /*O C�lculo de Isento deixei para fazer aqui embaixo porque precisava de algumas vari�veis 
                que s� foram sendo abastecidas anteriormente no c�digo ...*/
                if($finalidade == 'C') {//Nota Fiscal do Tipo CONSUMO ...
                    $isento = $valor_total_produtos + $valor_frete + $outras_despesas_acessorias + $valor_ipi - $base_calculo_icms;
                }else {//Revenda, n�o se acrescenta o Valor de IPI em R$ do Item no c�lculo do ICMS Corrente ...
                    //Lembrando que esse Isento � somente p/ os Itens que n�o s�o ST ...
                    $isento = $valor_total_produtos + $valor_frete + $outras_despesas_acessorias - $base_calculo_icms;
                }
                if((integer)$isento == '-0') $isento = 0;//Macete (rs) ...
                /********************************************************************************************/
                /********************Influenciar� na parte de Impress�o dos Textos da NF*********************/
                /********************************************************************************************/
                /*Se a NF for das Seguintes CFOP(s): 
                - 5.552 -> Transfer�ncia de Bens do Ativo Imobilizado
                - 5.901 -> Remessa p/ Industrializa��o
                - 5.916 / 6.916 -> Retorno de Conserto
                - 5.913 / 6.913 -> Retorno de Demonstra��o
                - 5.551 -> Venda de bem do Ativo Imobilizado
                - 5.908 -> Remessa em Comodato
                - 5.915 / 6.915 - Remessa p/ Conserto
                - 5.401 / 5.405 / 6.401 / 6.404 / 1.410 / 1.411 / 2.410 / 2.411 - NFs de Substitui��o Tribut�ria ...
                - 5.914 -> Remessa p/ Exposi��o
                - 1.914 -> Retorno de Exposi��o
                ent�o: */
                
                //Se a Empresa da Nota Fiscal for 'K2', n�o preciso fazer essa verifica��o de CFOPS ...
                if($id_empresa_negociacao == 3) {//K2 ...
                    $vetor_cfops = array();
                }else {//Outras Empresas ...
                    //$vetor_cfops = array('5.552', '5.901', '5.916', '6.916', '5.913', '6.913', '5.551', '5.908', '5.915', '6.915', '5.914', '1.914');
                    $vetor_cfops = array('5.552', '5.901', '5.913', '6.913', '5.551', '5.908', '5.915', '6.915', '5.914', '1.914');
                }
                
                if(in_array($cfop, $vetor_cfops)) {
                    $isento     = $valor_total_produtos;
                    $valor_icms = 0;//ISENTO ...
                    //Casos Especiais p/ IPI ...
                    //1)//Quando a CFOP for "Venda de bem do Ativo Imobilizado", n�o existe IPI = ISENTO ...
                    //if($cfop != '5.551' && $cfop != '5.908') $valor_ipi = 'ISENTO';
                    //2)//Quando a CFOP for "Exposi��o", IPI sempre ser� = SUSPENSO ...
                    if($cfop == '1.914' || $cfop == '5.914') $valor_ipi = 0;//SUSPENSO ...
                    $base_calculo_icms = 0;
                    //5.949 -> Existem Trocentas ...
                }else if($cfop == '5.949') {
                    $inicio_natureza = strtoupper(strtok($natureza_operacao, ' '));
                    //Em outros tipos de NF em que a CFOP = "Remessa" ou = "Retorno" n�o existe Tributa��o ...
                    if($inicio_natureza == 'REMESSA' || $inicio_natureza == 'RETORNO') {
                        $isento             = 0;
                        $valor_icms         = 0;//ISENTO ...
                        $valor_ipi          = 0;//ISENTO ...
                        $base_calculo_icms  = 0;
                    }
                }else if($cfop == '1.604') {//Cr�dito Ativo Imobilizado ...
                    /*Busca o Valor do ICMS em cima do Texto que foi preenchido pelo usu�rio na parte de Texto de NF 
                    e este, ser� apresentado no campo Valor do ICMS da NF ...*/
                    if(!empty($texto_nf)) {
                        $valor_icms = strstr($texto_nf, 'R$ ');
                        $valor_ipi  = strtok($valor_icms, 'ref');
                    }
                }
                /*Se a NF possui Suframa e este est� "Ativo" ent�o: 
                1) �rea de Livre Com�rcio IPI e ICMS de 7% (Se Suframa Ativo) ...
                2) Zona Franca de Manaus IPI, ICMS 7 % (Se Suframa Ativo) e (PIS+COFINS de 3,65% ...*/
                if(($suframa == 1 || $suframa == 2) && $suframa_ativo == 'S') {//�rea de Livre Com�rcio ou Zona Franca de Manaus
                    //O valor de Isento fica igual ao Total da Mercadoria devido ter sido concedido o ICMS de 7% ...
                    $isento     = $valor_total_produtos;
                    $valor_icms = 0;//ISENTO ...
                    //Somente na Zona Franca "Suframa = 2" ... que eu zero as Bases de C�lculo ...
                    if($suframa == 2) $base_calculo_icms = 0;
                }
                $valor_total_nota   = $valor_total_produtos + $valor_frete + $outras_despesas_acessorias + $valor_ipi + $valor_icms_st + $valor_fecp + $desconto;
            }
        }
        /*Sempre fa�o esse arredondamento p/ n�o dar erro em outros locais do Sistema que fazem compara��o 
        com esse Valor retornado ...*/
        $valor_total_nota = round($valor_total_nota, 2);
        
        /*********************************************************************************************/
        /*******************************************Ajustes*******************************************/
        /*********************************************************************************************/
        /*S� ir� acrescentar ajuste nesses impostos abaixo quando a vari�vel "$id_negociacao_item" = 0, porque isso 
        representa que o c�lculo foi feito em cima de todos os Itens da negocia��o passada por par�metro ...*/
        if($id_negociacao_item == 0) {
            $valor_icms+= $ajuste_valor_icms;
            $base_calculo_icms_st+= $ajuste_base_calc_icms_st;
            $valor_icms_st+= $ajuste_valor_icms_st;
        }
        /*********************************************************************************************/
        //Os 10 primeiros par�metros que s�o retornados, s�o os campos que aparecem no C�lculo do Impostos da Nota Fiscal ...
        return array('base_calculo_icms' => $base_calculo_icms, 'valor_icms' => $valor_icms, 'base_calculo_icms_st' => $base_calculo_icms_st, 'valor_icms_st' => $valor_icms_st, 'valor_total_produtos' => $valor_total_produtos, 'valor_frete' => $valor_frete, 'outras_despesas_acessorias' => $outras_despesas_acessorias, 'valor_ipi' => $valor_ipi, 'valor_total_nota' => $valor_total_nota, 'valor_total_nota_us' => $valor_total_nota_us, 'base_calculo_icms_c_red' => $base_calculo_icms_c_red, 'base_calculo_icms_s_red' => $base_calculo_icms_s_red, 'base_calculo_icms_bits_bedames_riscador' => $base_calculo_icms_bits_bedames_riscador, 'base_calculo_ipi' => $base_calculo_ipi, 'frete_despesas_acessorias' => $frete_despesas_acessorias_item_current_rs, 'ipi_frete_despesas_acessorias' => $ipi_frete_despesas_acessorias_txt, 'icms_frete_despesas_acessorias' => $icms_frete_despesas_acessorias_item_current_rs, 'imposto_importacao' => $ii_item_current_rs, 'iva_ajustado' => $vetor_dados_substituicao_tributaria['iva_ajustado'], 'peso_lote_total_kg' => $peso_lote_total_kg, 'desconto' => $desconto, 'valor_icms_oculto_creditar' => $valor_icms_oculto_creditar, 'valor_ipi_incluso' => $valor_ipi_incluso, 'difal' => $difal, 'valor_icms_destino' => $valor_icms_destino, 'valor_icms_remetente' => $valor_icms_remetente, 'fecp' => $fecp, 'valor_fecp' => $valor_fecp);
    }
    
    function calculos_substituicao_tributaria($ipi, $icms, $icms_intraestadual, $fecp, $iva, $valor_total, $valor_icms) {
        //C�lculo do IVA - Base de C�lculo ICMS ST Retido na Compra por Item de NF ...
        $iva_ajustado                           = ((1 + $iva / 100) * (1 - $icms / 100) / (1 - ($icms_intraestadual + $fecp) / 100)) - 1;
        $iva_ajustado                           = round($iva_ajustado, 4);
        $base_calculo_icms_st_item_current_rs 	= ($valor_total + $ipi) * (1 + $iva_ajustado);
        $valor_icms_st_item_current_rs 		= round(($base_calculo_icms_st_item_current_rs * $icms_intraestadual / 100) - $valor_icms, 2);
        
        return array('iva_ajustado' => $iva_ajustado, 'base_calculo_icms_st_item_current_rs' => $base_calculo_icms_st_item_current_rs, 'valor_icms_st_item_current_rs' => $valor_icms_st_item_current_rs);
    }

    function calculos_genericos() {
        /*Linha �Valor fatur�vel� n�o existe .
        Fator Taxa financeira di�ria  = ((1+tx.financ./100)^(1/30)
        Podemos calcular este valor como uma vari�vel , atualizada toda vez que mudarmos a vari�vel tx.financeira 30 dias .
        Fator Taxa financeira do pedido = fator tx.fin.diaria ^pz medio do pedido
        Fator SGD_UF=SP = (1-(%icms SP *(1-red SP /100)/100))*(1-imp.fed/100)
        Fator NF UF pedido = 1-(%icms UF pedido *(1-red UF pedido /100)/100)
        IF SGD 
        P.Fat.30/NF/SP = P.L.Final / Fator Taxa financeira do pedido * (1+tx.financ./100) / Fator SGD_UF=SP
        IF NF e UF = SP
        P.Fat.30/NF/SP = P.L.Final / Fator Taxa financeira do pedido * (1+tx.financ./100)
        IF NF e UF <> SP
        P.Fat.30/NF/SP = P.L.Final / Fator Taxa financeira do pedido * (1+tx.financ./100) * Fator NF UF pedido / Fator SGD_UF=SP*/
    }
}
?>