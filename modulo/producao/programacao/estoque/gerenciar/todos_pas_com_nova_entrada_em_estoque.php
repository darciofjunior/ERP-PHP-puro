<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');

if($passo == 1) {
    //Atualizo a marcação de "Todos os PA(s)" p/ uma situação faturável de modo que os mesmos não apareçam mais no Filtro da próxima vez ...
    foreach($_POST['hdd_produto_acabado'] as $i => $id_produto_acabado) {
        $sql = "UPDATE `produtos_acabados` SET `status_material_novo` = '0' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        bancos::sql($sql);
    }

    /*Atualizo todos os Pedidos dessas PA(s) p/ uma situação faturável de modo que o Estoquista possa trabalhar com estes 
    e encaminhá-los p/ serem faturados ...*/
    foreach($_POST['hdd_pedido_venda'] as $i => $id_pedido_venda) {
        //Muda para uma situação faturável
        $sql = "UPDATE `pedidos_vendas` SET `condicao_faturamento` = '1' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        alert('TODOS PA(S) COM NOVA ENTRADA EM ESTOQUE FOI(RAM) ATUALIZADO(S) COM SUCESSO !')
        parent.html5Lightbox.finish()
    </Script>
<?
}else {
    $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`discriminacao`, 
            pa.`pecas_por_jogo`, pa.`mmv`, ea.`prazo_entrega`, ea.`racionado`, ed.`razaosocial`, 
            ged.`desc_medio_pa`, gpa.`nome`, (pa.`preco_unitario` *(1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) AS preco_list_desc, 
            (-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) AS total_rs 
            FROM `produtos_acabados` pa 
            INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` <> '23' 
            WHERE pa.`status_material_novo` = '1' 
            AND pa.`ativo` = '1' 
            ORDER BY pa.`discriminacao` ";
    $campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO EXISTE(M) PA(S) COM NOVA ENTRADA EM ESTOQUE !')
        parent.html5Lightbox.finish()
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Todos PA(s) com Nova Entrada em Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ATUALIZAR TODOS PA(S) COM NOVA ENTRADA EM ESTOQUE ?')
    if(resposta == true) document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Todos PA(s) com Nova Entrada em Estoque
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Ref
        </td>
        <td>
            Produto
        </td>
        <td>
            N.º Pedido - Clientes Pendentes e Faturáveis
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../../vendas/estoque_acabado/detalhes.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes' class='link'>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            </a>
        </td>
        <td>
        <?
            //Listo todos os Itens Pedidos que estão em Pendência Total ou Parcial e que possuem esse PA do Loop ...
            $sql = "SELECT DISTINCT(pvi.`id_pedido_venda`), IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                    WHERE pvi.`status` < '2' 
                    AND pvi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ORDER BY pvi.`id_pedido_venda` ";
            $campos_pedidos_vendas = bancos::sql($sql);
            $linhas_pedidos_vendas = count($campos_pedidos_vendas);
            for($j = 0; $j < $linhas_pedidos_vendas; $j++) {
        ?>
            <a href="javascript:nova_janela('../../../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos_pedidos_vendas[$j]['id_pedido_venda'];?>', 'PED', '', '', '', '', 450, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Detalhes de Pedido' class='link'>
                <?=$campos_pedidos_vendas[$j]['id_pedido_venda'];?>
            </a>
            <?=' - '.$campos_pedidos_vendas[$j]['cliente'];?>
            <br/>
            <!--Essa variável será utilizada na próxima tela, assim que submeter ...-->
            <input type='hidden' name='hdd_pedido_venda[]' value='<?=$campos_pedidos_vendas[$j]['id_pedido_venda'];?>'>
        <?
            }
        ?>
            <input type='hidden' name='hdd_produto_acabado[]' value='<?=$campos[$i]['id_produto_acabado'];?>'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick='validar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='parent.html5Lightbox.finish()' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
    }
}
?>