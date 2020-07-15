<?
//Trago todas as NFs de Saída que possuem a determinada "Referência" / "Discriminação" ...
$sql = "SELECT nfsi.`id_nfs_item` 
        FROM `nfs_itens` nfsi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {//Não encontrou nenhum Item de Nota Fiscal ...
    $vetor_nfs_item[] = 0;
}else {//Encontrou pelo menos 1 Item de Nota Fiscal ...
    for($i = 0; $i < $linhas; $i++) $vetor_nfs_item[] = $campos[$i]['id_nfs_item'];
}

//Tratamento com Sql ...
if(!empty($txt_referencia) && !empty($txt_discriminacao)) {
    $complemento_sql = " (nfsoi.`discriminacao` LIKE '%$txt_referencia%' OR nfsoi.`discriminacao` LIKE '%$txt_discriminacao%') ";
}else if(!empty($txt_referencia) && empty($txt_discriminacao)) {
    $complemento_sql = " nfsoi.`discriminacao` LIKE '%$txt_referencia%' ";
}else if(empty($txt_referencia) && !empty($txt_discriminacao)) {
    $complemento_sql = " nfsoi.`discriminacao` LIKE '%$txt_discriminacao%' ";
}

//Trago todas as NFs Outras que possuem a determinada "Referência" / "Discriminação" ...
$sql = "SELECT nfsoi.`id_nf_outra_item` 
        FROM `nfs_outras_itens` nfsoi 
        LEFT JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsoi.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
        LEFT JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = nfsoi.`id_produto_insumo` AND pi.`discriminacao` LIKE '%$txt_discriminacao%' 
        WHERE $complemento_sql ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {//Não encontrou nenhum Item de Nota Fiscal ...
    $vetor_nf_outra_item[] = 0;
}else {//Encontrou pelo menos 1 Item de Nota Fiscal ...
    for($i = 0; $i < $linhas; $i++) $vetor_nf_outra_item[] = $campos[$i]['id_nf_outra_item'];
}

/* "NFs Saída / Devolução" que é status 6 - equivale a tabela 'nfs' do sistema ...
/* NFs Outras - equivale a tabela 'nfs_outras' do sistema ...

Gambiarra: rsrs

Da tabela 'nfs' é necessário trazer o campo "tipo_despacho"; 
só que na tabela 'nfs_outras' não existe esse campo, sendo assim para não furar o Union All tive que trazer 
o campo "observacao" para substituí-lo mesmo não sendo utilizado para nada, p/ evitar o erro de SQL 
"different number of columns" ...*/

$sql = "(SELECT nfs.`id_nf`, nfs.`id_empresa`, nfs.`id_nf_num_nota`, nfs.`data_emissao`, nfs.`vencimento1`, 
        nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, nfs.`status`, nfs.`tipo_despacho`, 
        nfsi.`id_nfs_item`, nfsi.`id_produto_acabado`, nfsi.`qtde`, nfsi.`valor_unitario`, 
        c.`razaosocial`, c.`credito`, t.`nome` AS transportadora 
        FROM `nfs` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
        INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` AND t.`nome` LIKE '%$txt_transportadora%' 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`ativo` = '1' $condicao_uf 
        WHERE nfsi.`id_nfs_item` IN (".implode(',', $vetor_nfs_item).") 
        AND nfs.`ativo` = '1' 
        AND nfs.`id_empresa` LIKE '$cmb_empresa' 
        AND nfs.`status` $status_nf 
        AND nfs.`finalidade` LIKE '$cmb_finalidade' 
        $condicao_ultimos_30_dias_nfs 
        $condicao_datas_nfs 
        $condicao_documental GROUP BY nfsi.`id_nfs_item`) 
        UNION ALL 
        (SELECT /*Esse Pipe é um Macete ...*/ CONCAT('|', nfso.`id_nf_outra`), nfso.`id_empresa`, 
        nfso.`id_nf_num_nota`, nfso.`data_emissao`, nfso.`vencimento1`, nfso.`vencimento2`, nfso.`vencimento3`, 
        nfso.`vencimento4`, nfso.`status`, nfso.`observacao`, nfsoi.`id_nf_outra_item`, nfsoi.`id_produto_acabado`, 
        nfsoi.`qtde`, nfsoi.`valor_unitario`, c.`razaosocial`, c.`credito`, t.nome AS transportadora 
        FROM `nfs_outras` nfso 
        INNER JOIN `nfs_outras_itens` nfsoi ON nfsoi.id_nf_outra = nfso.id_nf_outra 
        INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfso.`id_transportadora` AND t.`nome` LIKE '%$txt_transportadora%' 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`ativo` = '1' $condicao_uf 
        WHERE nfsoi.`id_nf_outra_item` IN (".implode(',', $vetor_nf_outra_item).") 
        AND nfso.`ativo` = '1' 
        AND nfso.`id_empresa` LIKE '$cmb_empresa' 
        AND nfso.`status` $status_nf_outras 
        AND nfso.`finalidade` LIKE '$cmb_finalidade' 
        $condicao_ultimos_30_dias_nfs_outras 
        $condicao_datas_nfs_outras GROUP BY nfsoi.`id_nf_outra_item`) ORDER BY `data_emissao` DESC ";
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'consultar.php?valor=1'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Nota(s) Fiscal(is) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Consultar Nota(s) Fiscal(is)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; NF
        </td>
        <td>
            Cliente
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Qtde
        </td>
        <td>
            Produto
        </td>
        <td>
            Preço L. <br>Final R$ 
        </td>
        <td>
            Total R$ 
        </td>
        <td>
            Transportadora
        </td>
        <td>
            Status da NF
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota <br>/ Prazo Pgto
            </font>
        </td>
    </tr>
<?
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
    $vetor = array_sistema::nota_fiscal();
    $tipo_despacho = array('', 'PORTARIA', 'TRANSPORTADORA', 'NOSSO CARRO', 'RETIRA', 'CORREIO/SEDEX', 'FINANCEIRO');
    
    for($i = 0;  $i < $linhas; $i++) {
        //Zero as variáveis p/ não dar problema no próximo loop ...
        $id_nf_outra = 0; $id_nf = 0;
/*Obs: O Union retorna o "id_nf_outra" e o "id_nf" como sendo um único campo, que no caso está sendo "id_nf", 
daí para distinguir de uma tabela com outra, eu joguei um "|" na Frente do campo id_nf_outra ...*/
//Verifico o Tipo de Nota Fiscal que está sendo listada dentro do Loop ...
        if(substr($campos[$i]['id_nf'], 0, 1) == '|') {//Significa que está sendo listada uma NF Outra(s) no Loop ...
            $id_nf_outra    = substr($campos[$i]['id_nf'], 1, strlen($campos[$i]['id_nf']));
            $caminho        = '../outras_nfs/itens/detalhes_nota_fiscal.php?id_nf_outra='.$id_nf_outra;
        }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
            $id_nf          = $campos[$i]['id_nf'];
            $caminho        = '../nota_saida/itens/detalhes_nota_fiscal.php?id_nf='.$id_nf;
        }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$caminho;?>'" width='10'>
            <a href="<?=$caminho;?>" class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="<?=$caminho;?>" class='link'>
            <?
/**************************************NF Outras*****************************************/
                if($id_nf_outra > 0) {//Significa que está sendo listada uma NF Outra(s) no Loop ...
                    echo '<font title="NF Outras" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($id_nf_outra, 'O').'</b></font>';
                }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
/**************************************Devolução*****************************************/
                    if($campos[$i]['status'] == 6) {//Está sendo acessada uma NF de Devolução ...
                        echo '<font color="red" title="NF de Devolução" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($id_nf, 'D').'</b></font>';
                    }else {//Está sendo acessada uma NF normal ...
/**************************************NF Saída*****************************************/
                        echo '<font title="NF de Saída" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($id_nf, 'S').'</b></font>';
                    }
                }
/****************************************************************************************/
            ?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td onclick="window.location = '<?=$caminho;?>'">
        <?
            if($campos[$i]['status'] == 6) {//Se for NF de Devolução Printo em Vermelho e transformo em negativo p/ acertar a Qtde Total
                echo '<font color="red">';
                echo number_format($campos[$i]['qtde'] * -1, 2, ',', '.');
                $qtde_total+= ($campos[$i]['qtde'] * -1);
            }else {
                echo number_format($campos[$i]['qtde'], 2, ',', '.');
                $qtde_total+= $campos[$i]['qtde'];
            }
        ?>
        </td>
        <td align='left'>
        <?
/******************************************NF Outras**********************************************/
            if($id_nf_outra > 0) {//Significa que está sendo listada uma NF Outra(s) no Loop ...
/*Busco o id_produto_acabado, id_produto_insumo e discriminação que está na Tabela de NFs Outras, porque 
através desse, eu tenho como saber o Tipo de Produto que foi inserido na minha NF ...*/
                $sql = "SELECT `id_produto_acabado`, `id_produto_insumo`, `discriminacao` 
                        FROM `nfs_outras_itens` 
                        WHERE `id_nf_outra_item` = ".$campos[$i]['id_nfs_item']." LIMIT 1 ";
                $campos_produto = bancos::sql($sql);
                if(!empty($campos_produto[0]['id_produto_acabado'])) {//Se foi cadastrado o PA ...
                    echo intermodular::pa_discriminacao($campos_produto[0]['id_produto_acabado']);
                }else if(!empty($campos_produto[0]['id_produto_insumo'])) {//Se foi cadastrado o PI ...
                    $sql = "SELECT CONCAT(u.`sigla`, ' * ', pi.`discriminacao`) AS discriminacao 
                            FROM `produtos_insumos` pi 
                            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                            WHERE pi.`id_produto_insumo` = '".$campos_produto[0]['id_produto_insumo']."' LIMIT 1 ";
                    $campos_pi = bancos::sql($sql);
                    echo $campos_pi[0]['discriminacao'];
                }else if(!empty($campos_produto[0]['discriminacao'])) {//Se foi cadastrado uma Discriminação ...
                    echo $campos_produto[0]['discriminacao'];
                }
            }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
/**************************************NF Devolução/Saída*****************************************/
//Busco o id_pedido_venda_item que está na Tabela de NFs, porque através desse, eu tenho como buscar o PA ...
                $sql = "SELECT `id_pedido_venda_item` 
                        FROM `nfs_itens` 
                        WHERE `id_nfs_item` = ".$campos[$i]['id_nfs_item']." LIMIT 1 ";
                $campos_pedido_venda_item = bancos::sql($sql);
//Aqui eu verifico se o P.A. Principal tem um P.A. Discriminação ...
                $sql = "SELECT `id_produto_acabado_discriminacao` 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = ".$campos_pedido_venda_item[0]['id_pedido_venda_item']." 
                        AND pvi.`id_orcamento_venda_item` = ovi.`id_orcamento_venda_item` LIMIT 1 ";
                $campos_discriminacao = bancos::sql($sql);
                $id_produto_acabado_discriminacao = $campos_discriminacao[0]['id_produto_acabado_discriminacao'];
//Se o PA for ESP, então eu printo o mesmo de Verde ...
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, 0, 0, $id_produto_acabado_discriminacao);
            }
/****************************************************************************************/
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_unitario'], 2, ',', '.')?>
        </td>
        <td align='right'>
        <?  
            echo number_format($campos[$i]['qtde'] * $campos[$i]['valor_unitario'], 2, ',', '.');
            $valor_total+= $campos[$i]['qtde'] * $campos[$i]['valor_unitario'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['transportadora'];?>
        </td>
        <td align='left'>
        <?
            echo $vetor[$campos[$i]['status']];
            if($campos[$i]['status'] == 4) echo ' ('.$tipo_despacho[$campos[$i]['tipo_despacho']].')';
        ?>
        </td>
        <td align='left'>
        <?
//Busca da Empresa da NF ...
            $sql = "SELECT `nomefantasia` 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $apresentar = $campos_empresa[0]['nomefantasia'];
            $apresentar.= ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) ? ' (NF)' : ' (SGD)';
//Vencimentos da NF ...
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
        <td>
            <font color='yellow'>
                <?=number_format($qtde_total, 2, ',', '.');?>
            </font>
        </td>
        <td colspan='2'>
        <?
            if($_GET['pop_up'] != 1) {
        ?>
        <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php?pop_up=<?=$pop_up;?>'" class='botao'>
        <?
            }
        ?>
        </td>
        <td>
            <font color='yellow'>
                R$ <?=number_format($valor_total, 2, ',', '.');?>    
            </font>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>