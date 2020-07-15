<?
if($veio_do_gerenciar == 'S') {//Significa que esse arquivo foi requerido por dentro do Gerenciar, então faço esse desvio p/ não dar redeclare ...
    $title  = 'Gerenciar Estoque';
    $niveis = '../../../../../';
}else {//Significa que essa tela foi acessada de dentro do próprio Menu de Consultar Estoque ...
    require('../../../lib/segurancas.php');
    require('../../../lib/menu/menu.php');
    require('../../../lib/custos.php');
    require('../../../lib/data.php');
    require('../../../lib/estoque_acabado.php');
    require('../../../lib/intermodular.php');
    require('../../../lib/vendas.php');
    segurancas::geral($PHP_SELF, '../../../');
    $title  = 'Consultar Estoque';
    $niveis = '../../../';
}
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

/*Esse trecho de tela foi feito em um arquivo à parte, p/ evitar de recarregar toda a tela do 
Estoque Acabado que daí seria muito lento, achamos mais fácil e mais rápido recarregar apenas
o Iframe que é exatamente esse arquivo na hora em que o usuário altera o Prazo de Entrega ...*/
$data_atual_menos_sete  = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');
$data_atual             = date('Y-m-d');

if($passo == 1) {
    if($veio_do_gerenciar != 'S') {//Significa que essa tela foi acessada de dentro do próprio Menu de Consultar Estoque ...
        //Se não estiver habilitado o checkbox, só não mostra os P.A. q pertecem a família de Componentes
        if(empty($chkt_mostrar_componentes)) $condicao = ' AND gpa.`id_familia` <> 23 ';
        if(!empty($chkt_est_disp_comp_zero)) {//Estoque Disponível / Comprometido < 0 marcada ...
            $condicao.= " AND (ea.`qtde_disponivel` - ea.`qtde_pendente` ) < '0'  ";
            $order_by = " (-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) DESC "; //Aqui não posso passar o apelido por causa do sql de paginação, ela não entende este apelido e dá erro ...
        }else {
            $order_by = " pa.`discriminacao` ";
        }
        //Significa que o usuário só quer ver os PA(s) que são normais de linha
        if(empty($chkt_exibir_esp)) $condicao_esp = " AND pa.`referencia` <> 'ESP' ";
        if($cmb_familia == '')      $cmb_familia = '%';
        if($cmb_grupo_pa == '')     $cmb_grupo_pa = '%';
        /*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
        armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
        if($hidden_operacao_custo == 1) {//Operação de Custo = Industrial
            $cmb_operacao_custo = 0;
        }else if($hidden_operacao_custo == 2) {//Operação de Custo = Revenda
            $cmb_operacao_custo = 1;
        }else {//Independente da Operação de Custo
            if($cmb_operacao_custo == '') $cmb_operacao_custo = '%';
        }
        //Segunda adaptação
        if($hidden_operacao_custo_sub == 1) {//Sub-Operação de Custo = Industrial
            $cmb_operacao_custo_sub = 0;
        }else if($hidden_operacao_custo_sub == 2) {//Sub-Operação de Custo = Revenda
            $cmb_operacao_custo_sub = 1;
        }else {//Independente da Sub-Operação de Custo
            if($cmb_operacao_custo_sub == '') $cmb_operacao_custo_sub = '%';
        }
        //Se estiver preenchido o "Fornecedor Default" ...
        if(!empty($txt_fornecedor)) {
            /*Busco todos os PA do "Fornecedor Default", mas somente os PA que são do Tipo PI's e q 
            são normais de linha ...*/
            $sql = "SELECT pa.`id_produto_acabado` 
                    FROM `fornecedores` f 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_fornecedor_default` = f.`id_fornecedor` AND pi.`id_fornecedor_default` > '0' AND pi.`ativo` = '1' 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`ativo` = '1' $condicao_esp 
                    WHERE f.`razaosocial` LIKE '%$txt_fornecedor%' ORDER BY pa.`id_produto_acabado` ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            if($linhas > 0) {//Se encontrou pelo menos 1 registro do Fornecedor digitado pelo Usuário ...
                for($i = 0; $i < $linhas; $i++) $id_produto_acabados.= $campos[$i]['id_produto_acabado'].', ';
                $id_produto_acabados = substr($id_produto_acabados, 0, strlen($id_produto_acabados) - 2);
            }else {//Não encontrou nada ...
                $id_produto_acabados = 0;
            }
            $condicao_pas = " AND pa.`id_produto_acabado` IN ($id_produto_acabados) ";
        }
        //Se foi preenchido o N.º da OP, busco o PA "referência" dessa OP ...
        if(!empty($txt_numero_op)) {
            /*Busco todos os PA do "Fornecedor Default", mas somente os PA que são do Tipo PI's e q são 
            normais de linha ...*/
            $sql = "SELECT `id_produto_acabado` 
                    FROM `ops` 
                    WHERE `id_op` = '$txt_numero_op' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            if($linhas > 0) {//Se encontrou a OP digitada pelo Usuário ...
                $id_produto_acabados = $campos[0]['id_produto_acabado'];
            }else {//Não encontrou nada ...
                $id_produto_acabados = 0;
            }
            $condicao_pas = " AND pa.`id_produto_acabado` IN ($id_produto_acabados) ";
        }

        if(!empty($chkt_prod_prazo_atual_sete)) {
            //if($operacao_custo == 0) {//P.A. Industrial
            //Faço uma verificação de Toda(s) as OP(s) que estão em abertas e que possuem esse PA atrelado ...
                $sql = "SELECT `id_produto_acabado` 
                                FROM `ops` 
                                WHERE (DATE_ADD('$data_atual', INTERVAL -7 DAY) > SUBSTRING(`data_ocorrencia`, 1, 10) || `data_ocorrencia` = '0000-00-00 00:00:00') AND `status_finalizar` = '0' AND `ativo` = '1' ";
            //}else {//P.A. Revenda
                $sql.= " UNION SELECT ea.`id_produto_acabado` 
                        FROM `estoques_acabados` ea 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` 
                        WHERE pa.`operacao_custo` = '1' 
                        AND (DATE_ADD('$data_atual', INTERVAL -7 DAY) > SUBSTRING(ea.`data_atualizacao_prazo_ent`, 1, 10) || ea.`data_atualizacao_prazo_ent` = '0000-00-00 00:00:00') ";
            //}
        }else {
            $sql = "SELECT id_produto_acabado 
                            FROM `produtos_acabados` 
                            WHERE `ativo` = '1' ";
        }
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) $id_produtos_acabados_geral.= $campos[$i]['id_produto_acabado'].', ';
        $id_produtos_acabados_geral = substr($id_produtos_acabados_geral, 0, strlen($id_produtos_acabados_geral) - 2);
       
        //Se essa opção estiver marcada, então eu só mostro os P.A(s) que são Top(s) ...
        if(!empty($chkt_mostrar_top)) $condicao_top = " AND pa.`status_top` >= '1' ";
        
        //Se essa opção estiver marcada, então eu só mostro os Itens que estão com Entrada Antecipada > 0 ...
        if(!empty($chkt_entrada_antecipada_maior_zero)) $condicao_entrada_antecipada_maior_zero = " AND ea.`entrada_antecipada` > '0' ";
	
        $sql = "SELECT DISTINCT(pa.`id_produto_acabado`), gpa.`nome`, ged.`desc_medio_pa`, 
                pa.`status_top`, pa.`operacao_custo`, pa.`operacao_custo_sub`, pa.`referencia`, 
                pa.`pecas_por_jogo`, pa.`preco_promocional_b`, pa.`mmv`, pa.`observacao` AS observacao_pa, 
                ea.`id_estoque_acabado`, ea.`prazo_entrega`, 
                (ea.`qtde_disponivel` - ea.`qtde_pendente`) AS estoque_comprometido, 
                ea.`racionado`, ea.`status`, 
                (-(ea.`qtde_disponivel` - ea.`qtde_pendente`) * (pa.`preco_unitario` * (1 - ged.`desc_base_a_nac` / 100) * (1 - ged.`desc_base_b_nac` / 100) * (1 + ged.`acrescimo_base_nac` / 100)) * ged.`desc_medio_pa`) AS total_rs 
                FROM `estoques_acabados` ea 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ea.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' $condicao_esp 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_grupo_pa` LIKE '$cmb_grupo_pa' 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` LIKE '$cmb_familia' 
                WHERE pa.`id_produto_acabado` IN ($id_produtos_acabados_geral) 
                AND pa.`operacao_custo` LIKE '%$cmb_operacao_custo%' 
                AND pa.`operacao_custo_sub` LIKE '$cmb_operacao_custo_sub' 
                AND pa.`ativo` = '1' 
                $condicao_pas 
                $condicao 
                $condicao_top 
                $condicao_entrada_antecipada_maior_zero 
                GROUP BY pa.`id_produto_acabado` 
                ORDER BY $order_by ";
    }
    $campos = bancos::sql($sql, $inicio, 75, 'sim', $pagina);
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
<title>.:: <?=$title;?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$niveis;?>css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '<?=$niveis;?>lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$niveis;?>js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$niveis;?>js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$niveis;?>js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function opcoes_produto_acabado(id_produto_acabado, status_estoque) {
    if(status_estoque == 0) {//Produto Acabado Liberado ...
        nova_janela('<?=$niveis;?>modulo/classes/produtos_acabados/substituir_estoque_pa.php?id_produto_acabado='+id_produto_acabado, 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }else {//Produto Acabado Bloqueado ...
        alert('ESTE PRODUTO ACABADO ESTÁ BLOQUEADO !!!')
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='21'>
            <?=$title;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Ref
        </td>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            <font title='Operação de Custo' style='cursor:help' size='-2'>
                O.C.
            </font>
        </td>
        <td rowspan='2'>
            <font size='-2'>
                Compra<br/> Produção
            </font>
        </td>
        <td colspan='8'>
            Quantidade / Estoque
        </td>
        <td rowspan='2'>
            <font title='Média Mensal de Vendas' style='cursor:help' size='-2'>
                M.M.V.
            </font>
        </td>
        <td rowspan='2'>
            <font size='-2'>
                Prazo de Entrega
            </font>
        </td>
        <td rowspan='2'>
            <font size='-2'>
                P.Médio<br/> Venda R$
            </font>
        </td>
        <td rowspan='2'>
            <font title='Promoção B' size='-2' style='cursor:help'>
                Promo. B R$
            </font>
        </td>
        <td rowspan='2'>
            <font size='-2'>
                Total R$ EC
            </font>
        </td>
        <td rowspan='2'>
            <font size='-2'>
                Total R$<br/>s/prog
            </font>
        </td>
        <td rowspan='2'>
            <font size='-2'>
                Tot. R$ Lib. p/ Faturar
            </font>
        </td>
        <td rowspan='2'>
            <font title='Qtde Separada' size='-2' style='cursor:help'>
                Qtde Sep.
            </font>
        </td>
        <td rowspan='2'>
            <font size='-2'>
                EC p/x <br/>meses
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Estoque Real' size='-2' style='cursor:help'>
                ER
            </font>
        </td>
        <td>
            <font title='Estoque Disponivel' size='-2' style='cursor:help'>
                ED
            </font>
        </td>
        <td>
            <font title='Estoque do Fornecedor' size='-2' style='cursor:help'>
                E Forn
            </font>
        </td>
        <td>
            <font title='Estoque do Porto' size='-2' style='cursor:help'>
                E Porto
            </font>
        </td>
        <td>
            <font title='Pendência' size='-2' style='cursor:help'>
                Pend.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido' size='-2' style='cursor:help'>
                EC
            </font>
        </td>
        <td>
            <font title='Entrada Antecipada' size='-2' style='cursor:help'>
                E.Ant<br/>
            </font>
        </td>
        <td>
            <font title='Estoque Programado &gt; que 30 dias' size='-2' style='cursor:help'>
                Prog >=30
            </font>
        </td>
    </tr>
<?
	for ($i = 0; $i < $linhas; $i++) {
            $estoque_produto            = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 0);
            $quantidade_estoque         = $estoque_produto[0];
            $compra                     = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
            $producao                   = $estoque_produto[2];
            $qtde_disponivel            = $estoque_produto[3];
            $qtde_separada              = $estoque_produto[4];
            $qtde_pendente              = $estoque_produto[7];
            $est_comprometido           = $estoque_produto[8];
            $qtde_pa_possui_item_faltante   = $estoque_produto[9];
            $est_fornecedor             = $estoque_produto[12];
            $est_porto                  = $estoque_produto[13];
            $entrada_antecipada         = $estoque_produto[15];
            $qtde_programada            = estoque_acabado::qtde_programada($campos[$i]['id_produto_acabado']);
            $prazo_entrega              = strtok($campos[$i]['prazo_entrega'], '=');
            $prazo_entrega              = trim($prazo_entrega);
            $responsavel                = strtok($campos[$i]['prazo_entrega'], '|');
            $responsavel                = substr(strchr($responsavel, '> '), 1, strlen($responsavel));
            $data_hora                  = strchr($campos[$i]['prazo_entrega'], '|');
            $data_hora                  = substr($data_hora, 2, strlen($data_hora));
            $data                       = data::datetodata(substr($data_hora, 0, 10), '/');
            $hora                       = substr($data_hora, 11, 8);
//Faz esse tratamento para o caso de não encontrar o responsável ...
            $string_apresentar          = (empty($responsavel)) ? '&nbsp;' : 'Responsável: '.$responsavel.' - '.$data.' '.$hora;
//Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo a coluna na cor vermelha ...
            $font_compra                = ($compra < - ($est_comprometido)) ? "<font color='red'>" : "<font color='black'>";
            $font_producao              = ($producao < - ($est_comprometido)) ? "<font color='red'>" : "<font color='black'>";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('<?=$niveis;?>modulo/vendas/estoque_acabado/detalhes.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes' class='link'>
            <?
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
                echo '<font color="black"><b> - Grupo PA: </b>'.$campos[$i]['nome'].'</font>';
            ?>
            </a>
            <?
                if(!empty($campos[$i]['observacao_pa'])) echo "&nbsp;-&nbsp;<img width='23' height='18' title='".$campos[$i]['observacao_pa']."' src='".$niveis."imagem/olho.jpg'>";
                if($campos[$i]['racionado'] == 1) {//Verifico se o PA está racionado
            ?>
                    &nbsp;-&nbsp;
                    <font color='red' title='Racionado' style='cursor:help'>
                        <b>(R)</b>
                    </font>
            <?
                }
            ?>
            &nbsp;
            <a href="javascript:nova_janela('<?=$niveis;?>modulo/vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS_ULTIMOS_6_MESES', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <img src = '<?=$niveis;?>imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <a href = '<?=$niveis;?>modulo/vendas/relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1' class='html5lightbox'>
                <img src = '<?=$niveis;?>imagem/propriedades.png' title='Visualizar Orçamentos - Últimos 6 meses' alt='Visualizar Orçamentos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <img src = '<?=$niveis;?>imagem/carrinho_compras.png' border='0' title='Compra + Produção' alt='Compra + Produção' width='25' height='16' onclick="html5Lightbox.showLightbox(7, '<?=$niveis;?>modulo/classes/producao/visualizar_compra_producao.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>')">
            <?
                $url = $niveis.'modulo/vendas/estoque_acabado/manipular_estoque/consultar.php?passo=1';
                /*Mudança feita em 17/05/2016 - Antigamente os detalhes da consulta só eram feitos pela 
                referência independente de ser normal de Linha, eu supus que fosse assim porque temos PA(s) 
                que são similares em seu cadastro na parte de referência, por exemplo ML: 
                ML-001, ML-001A, ML-001AS, ML-001D, ML-001S, ML-001T, ML-001U, mas para ESP fica inviável 
                vindo todos os ESP´s do Sistema e trazendo informações que não tinham nada haver ...*/
                if($campos[$i]['referencia'] == 'ESP') {//Aqui quero ver detalhes do PA ESP em específico ...
                    $url.= '&id_produto_acabado='.$campos[$i]['id_produto_acabado'].'&pop_up=1';
                }else {//PA normal de Linha, quero ver detalhes de todos os PA(s) semelhantes a este da Referência ...
                    $url.= '&txt_referencia='.$campos[$i]['referencia'].'&pop_up=1';
                }
            ?>
            &nbsp;
            <img src = '<?=$niveis;?>imagem/baixas_manipulacoes.png' border='0' title='Baixas / Manipulações' alt='Baixas / Manipulações' width='22' height='20' onclick="html5Lightbox.showLightbox(7, '<?=$url;?>')">
            &nbsp;
            <img src = '<?=$niveis;?>imagem/ferramenta.png' border='0' title='Substituir Estoque' style='cursor:pointer' onclick="opcoes_produto_acabado('<?=$campos[$i]['id_produto_acabado'];?>', '<?=$campos[$i]['status'];?>')">
            &nbsp;
            <?
                /*********************Links p/ abrir o Custo*********************/
                if($campos[$i]['operacao_custo'] == 0) {//Industrial
            ?>
            <a href="javascript:nova_janela('<?=$niveis;?>modulo/producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" style='cursor:help' class='link'>
            <?
                }else {
            ?>
            <a href="javascript:nova_janela('<?=$niveis;?>modulo/producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" style='cursor:help' class='link'>
            <?
                }
            ?>
                <img src = '<?=$niveis;?>imagem/menu/alterar.png' title='Visualizar Custo' alt='Visualizar Custo' border='0'>
            </a>
                &nbsp;
                <img src = '<?=$niveis;?>imagem/estornar_entrada_antecipada.png' border='0' title='Retorno de Entrada Antecipada' alt='Retorno de Entrada Antecipada' width='22' height='20' onclick="html5Lightbox.showLightbox(7, '<?=$niveis;?>modulo/vendas/estoque_acabado/retorno_entrada_antecipada.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>')">
            <?
                if($veio_do_gerenciar == 'S') {//Significa que esse arquivo foi requerido por dentro do Gerenciar e sendo assim apresento esse ícone p/ que se possa entrar dentro dessa opção ...
            ?>
                &nbsp;
                <img src = '<?=$niveis;?>imagem/caneta.png' border='0' title='Incluir Inventário' alt='Incluir Inventário' width='22' height='20' onclick="nova_janela('<?=$niveis;?>modulo/vendas/estoque_acabado/inventario/incluir.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&pop_up=1', 'INCLUIR_INVENTARIO', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')">
                &nbsp;
                <img src = '<?=$niveis;?>imagem/desbloquear.png' border='0' title='Desbloquear PAs' alt='Desbloquear PAs' width='20' height='20' onclick="window.location = '<?=$niveis;?>modulo/producao/programacao/desbloquear_pa/consultar.php'">
                &nbsp;
                <img src = '<?=$niveis;?>imagem/seta_direita.gif' border='0' title='Gerenciar Estoque' alt='Gerenciar Estoque' width='25' height='16' onclick="window.location = 'manipular.php?tela=2&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>'">
            <?
                }
            ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos[$i]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }
            if($campos[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[$i]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos[$i]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos[$i]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
        <?
            //Aqui verifica se o PA tem relação com o PI, caso isso não acontece não apresenta o link
            $sql = "SELECT `id_produto_insumo` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
                //Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
            if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 1) {
        ?>
        <a href="javascript:nova_janela('<?=$niveis;?>modulo/classes/estoque/compra_producao.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Compra Produção" class="link">
        <?
/****************Compra****************/
            if($font_compra == "<font color='black'>") $font_compra = "<font color='#6473D4'>";//Se link, exibe em Azul ...
                echo $font_compra.number_format($compra, 2, ',', '.');
/****************Produção****************/					
                if(!empty($producao) && $producao != 0) {
                    if($font_producao == "<font color='black'>") $font_producao = "<font color='#6473D4'>";//Se link, exibe em Azul ...
                    echo ' / '.$font_producao.number_format($producao, 2, ',', '.');
                }
        ?>
        </a>
<?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
            }else if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 0) {//Não mostra o link
/****************Compra****************/					
                echo $font_compra.number_format($compra, 2, ',', '.');
/****************Produção****************/
                if(!empty($producao) && $producao != 0) echo ' / '.$font_producao.number_format($producao, 2, ',', '.');
            }else {//Aqui o PA não tem relação com o PI
/****************Produção****************/
                echo $font_producao.number_format($producao, 2, ',', '.');
            }
            $retorno_pas_atrelados          = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos[$i]['id_produto_acabado']);
            $font_compra_producao_atrelado  = (($retorno_pas_atrelados['total_compra_producao_pas_atrelados'] / $campos[$i]['pecas_por_jogo']) < (-$retorno_pas_atrelados['total_ec_pas_atrelados'] / $campos[$i]['pecas_por_jogo'])) ? 'red' : 'black';

            echo '<br/><font color="'.$font_compra_producao_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">Atrel = '.number_format($retorno_pas_atrelados['total_compra_producao_pas_atrelados'] / $campos[$i]['pecas_por_jogo'], 0, '', '.').'</font>';
            if($estoque_produto[11] > 0) echo '<br/><font color="purple"><b>(OE='.number_format($estoque_produto[11], 0, '', '.').')</b></font>';
        ?>
        </td>
        <td align='right'>
        <?
            //Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" ...
            $sql = "SELECT `qtde` 
                    FROM `estoques_excedentes` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `status` = '0' LIMIT 1 ";
            $campos_excedente = bancos::sql($sql);
            if($campos_excedente[0]['qtde'] > 0) {//Se existir Estoque Excedente, exibo um link p/ ver Detalhes
        ?>
            <a href='<?=$niveis;?>modulo/vendas/estoque_acabado/excedente/alterar.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&pop_up=1' class='html5lightbox'>
        <?
            }
            echo number_format($quantidade_estoque, 2, ',', '.');
            if($qtde_pa_possui_item_faltante > 0) echo '<br/><font color="red" title="Produto Incompleto (Faltando Item)" style="cursor:help"><b>'.$qtde_pa_possui_item_faltante.' F.I</b></font>';
        ?>
            </a>
        </td>
        <td align='right'>
        <?
            $font = ($qtde_disponivel < 0) ? "<font color='red'>" : '';
            echo $font.segurancas::number_format($qtde_disponivel, 2, '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($est_fornecedor > 0) echo segurancas::number_format($est_fornecedor, 2, '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($est_porto > 0) echo segurancas::number_format($est_porto, 2, '.');
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($qtde_pendente, 2, '.');?>
        </td>
        <td align='right'>
        <?
            if($est_comprometido < 0) {
                echo "<font color='red'>".segurancas::number_format($est_comprometido, 2, '.')."</font>";
            }else {
                echo segurancas::number_format($est_comprometido, 2, '.');
            }
            $font_ec_atrelado   = (($retorno_pas_atrelados['total_ec_pas_atrelados'] / $campos[$i]['pecas_por_jogo']) < 0) ? 'red' : 'black';
            echo '<br/><font color="'.$font_ec_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">Atrel = '.number_format($retorno_pas_atrelados['total_ec_pas_atrelados'] / $campos[$i]['pecas_por_jogo'], 0, '', '.').'</font>';
        ?>
        </td>
        <td align='right'>
            <?=number_format($entrada_antecipada, 2, ',', '.');?>
            <font color='green'>
                <b>(Disp=<?=number_format($qtde_disponivel - $entrada_antecipada, 2, ',', '.');?>)</b>
            </font>
        </td>
        <td align='right'>
        <?
            if($qtde_programada < 0) {
                echo "<font color='red'>".segurancas::number_format($qtde_programada, 2, '.')."</font>";
            }else {
                echo segurancas::number_format($qtde_programada, 2, '.');
            }
            $calculo_programado_pas_atrelados   = intermodular::calculo_programado_pas_atrelados($campos[$i]['id_produto_acabado']);
            $font_programado_atrelado           = ($calculo_programado_pas_atrelados['total_programado_pas_atrelados'] < 0) ? 'red' : 'black';
            echo '<br/><font color="'.$font_programado_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">Atrel = '.number_format($calculo_programado_pas_atrelados['total_programado_pas_atrelados'], 0, '', '.').'</font>';
        ?>
        </td>
        <td align='right'>
        <?
            echo number_format($campos[$i]['mmv'], 2, ',', '.');
            $font_mmv_atrelado   = (($retorno_pas_atrelados['total_mmv_pas_atrelados'] / $campos[$i]['pecas_por_jogo']) < 0) ? 'red' : 'black';
            echo '<br/><font color="'.$font_mmv_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">Atrel = '.number_format($retorno_pas_atrelados['total_mmv_pas_atrelados'] / $campos[$i]['pecas_por_jogo'], 0, '', '.').'</font>';
        ?>
        </td>
        <?
/*Se esse checkbox estiver habilitado, então não Mostro os Prazos de Entregas, para não atrapalhar 
a colagem lá no Excel*/
            if($chkt_nao_mostrar_prazo_entrega == 1) {
        ?>
        <td align='center'>
                &nbsp;
        </td>
        <?
//Mostra os Prazos de Entrega normalmente, toda a lógica de programação está feita dentro do Iframe ...
            }else {

        ?>
        <td align='center'>
            <iframe src = '<?=$niveis;?>modulo/classes/produtos_acabados/prazo_entrega.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&operacao_custo=<?=$campos[$i]['operacao_custo'];?>&operacao_custo_sub=<?=$campos[$i]['operacao_custo_sub'];?>' name='prazo_entrega' id='prazo_entrega' marginwidth='0' marginheight='0' frameborder='0' height='45' width='100%' scrolling='auto'></iframe>
        </td>
        <?
            }
        ?>
        <td align='right'>
        <?
            $vetor_valores = vendas::preco_venda($campos[$i]['id_produto_acabado']);
            echo number_format($vetor_valores['preco_venda_medio_rs'], 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['preco_promocional_b'], 2, '.');?>
        </td>
        <td align='right' title="Fator de Desconto=><?=$campos[$i]['desc_medio_pa'];?>" style='cursor:help'>
        <?
            if($campos[$i]['total_rs'] > 0) {
                $total_geral_com_programado+= $campos[$i]['total_rs'];
                echo segurancas::number_format($campos[$i]['total_rs'], 0, '.');
            }else {
                echo "&nbsp;";
            }
        ?>
        </td>
        <td align='right' title='Fator de Desconto => <?=$campos[$i]['desc_medio_pa'];?>' style='cursor:help'>
        <?
            $total_rs_sem_programado = -($est_comprometido + $qtde_programada) * $vetor_valores['preco_venda_medio_rs'];
            if($total_rs_sem_programado > 0) {
                echo 'Qtde='.segurancas::number_format(-($est_comprometido + $est_comp_programado), 0, '.').'<br/>';
                $total_geral_sem_programado+= $total_rs_sem_programado;
                echo segurancas::number_format($total_rs_sem_programado, 0, '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            //Aqui eu Pego tudo o que está em Pendência até a Data Atual ...
            $sql = "SELECT (SUM(pvi.`qtde_pendente`)) AS qtde_pendente_ate_hoje 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`faturar_em` <= '$data_atual' 
                    WHERE pvi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND pvi.`status` < '2' 
                    AND pvi.`qtde_pendente` > '0' ";
            $qtde_pendente_ate_hoje 	= bancos::sql($sql);
            $total_pendente_ate_hoje 	= ($qtde_pendente_ate_hoje[0]['qtde_pendente_ate_hoje'] - $qtde_disponivel) * $vetor_valores['preco_venda_medio_rs'];

            if($total_pendente_ate_hoje > 0) {
                echo 'Qtde='.segurancas::number_format($qtde_pendente_ate_hoje[0]['qtde_pendente_ate_hoje'] - $qtde_disponivel, 0, '.').'<br/>';
                echo '<b>'.segurancas::number_format($total_pendente_ate_hoje, 0, '.').'</b>';
                $total_geral_pendente_ate_hoje+= $total_pendente_ate_hoje;
            }
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($qtde_separada, 2, '.');?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['mmv'] == 0) {//Se não existir MMV, não faz pq dá derro de Divisão por Zero ...
                echo '<b>S/ MMV</b>';
            }else {
                if($est_comprometido / $campos[$i]['mmv'] < 0) {
                    echo '0';
                }else {
                    echo segurancas::number_format($est_comprometido / $campos[$i]['mmv'], 2, '.');
                }
            }
?>
        </td>
    </tr>
<?
            /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
            encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
            da Biblioteca de Custos, na função pas_atrelados ...*/
            unset($vetor_pas_atrelados);
        }
?>
    <tr class='linhanormal' align='right'>
        <td colspan='16'>
            <font color='blue'><b>Total R$ </b></font>
        </td>
        <td>
            <font color='blue'><b><?=segurancas::number_format($total_geral_com_programado, 0, '.');?></b></font>
        </td>
        <td>
            <font color='blue'><b><?=segurancas::number_format($total_geral_sem_programado, 0, '.');?></b></font>
        </td>
        <td>
            <font color='blue'><b><?=segurancas::number_format($total_geral_pendente_ate_hoje, 0, '.');?></b></font>
        </td>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='21'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Prazo de Entrega com Link em Verde -> Alterado a menos de 7 dias
* Prazo de Entrega com Link em Azul -> Alterado a mais de 7 dias
* Coluna Compra / Produção em <font color='red'>Vermelho</font> significa que a Qtde em Compra Produção é Insuficiente p/ atender o Estoque Comprometido
</pre>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function iniciar() {
    document.form.cmb_operacao_custo_sub.className  = 'textdisabled'
    document.form.cmb_operacao_custo_sub.disabled   = true
    document.form.txt_referencia.focus()
}

//Controle com a Operação de Custo
function controle_hidden_operacao_custo() {
    var operacao_custo = document.form.cmb_operacao_custo[document.form.cmb_operacao_custo.selectedIndex].text
//Se não estiver selecionada nenhuma Operação de Custo
    if(operacao_custo == 'SELECIONE') {
        document.form.hidden_operacao_custo.value = ''
    }else if(operacao_custo == 'Industrialização') {
        document.form.hidden_operacao_custo.value = 1
    }else if(operacao_custo == 'Revenda') {
        document.form.hidden_operacao_custo.value = 2
    }
}

//Controle com a Sub-Operação de Custo
function controle_hidden_operacao_custo_sub() {
    var operacao_custo_sub = document.form.cmb_operacao_custo_sub[document.form.cmb_operacao_custo_sub.selectedIndex].text
//Se não estiver selecionada nenhuma Sub-Operação de Custo
    if(operacao_custo_sub == 'SELECIONE') {
        document.form.hidden_operacao_custo_sub.value = ''
    }else if(operacao_custo_sub == 'Industrialização') {
        document.form.hidden_operacao_custo_sub.value = 1
    }else if(operacao_custo_sub == 'Revenda') {
        document.form.hidden_operacao_custo_sub.value = 2
    }
}

function controle_operacao_custo() {
    var operacao_custo = eval(document.form.cmb_operacao_custo.value)
    if(operacao_custo == 0) {//Quando a Operação de Custo = Industrial, eu habilito a Sub-Operação de Custo ...
//Layout de Habilitado
        document.form.cmb_operacao_custo_sub.className = 'combo'
//Habilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value      = ''
        document.form.cmb_operacao_custo_sub.disabled   = false
//Quando a Operação de Custo = Revenda, eu desabilito a Sub-Operação de Custo ...
    }else {
//Layout de Desabilitado
        document.form.cmb_operacao_custo_sub.className = 'textdisabled'
//Desabilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value = ''
        document.form.cmb_operacao_custo_sub.disabled = true
    }
}

function func_est_disp_comp_zero() {
/*Toda vez que essa opção estiver desmarcada, eu sempre tenho que desmarcar a opção de Produtos 
com Prazo de Atualização > 7 dias*/
    if(document.form.chkt_est_disp_comp_zero.checked == false) document.form.chkt_prod_prazo_atual_sete.checked = false
}

function func_prod_prazo_atual_sete() {
/*Toda vez que eu clicar nessa opção, eu também tenho que habilitar a opção de Estoque Disponível 
/ Comprometido < 0*/
    if(document.form.chkt_prod_prazo_atual_sete.checked == true) {//Se estiver checado
        document.form.chkt_est_disp_comp_zero.checked = true
    }else {//Se não estiver checado
        document.form.chkt_est_disp_comp_zero.checked = false
    }
}

function relatorio_qtde_pecas_embalar() {
    with(document.form) {
        method = 'post'
        action = 'relatorio_qtde_pecas_embalar.php'
        target = 'RELATORIO_QTDE_PECAS_EMBALAR'
    }
    nova_janela('relatorio_qtde_pecas_embalar.php', 'RELATORIO_QTDE_PECAS_EMBALAR', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function acertar_estoque() {
    with(document.form) {
        method = 'post'
        action = 'acertar_estoque.php'
        target = 'ACERTAR_ESTOQUE'
    }
    nova_janela('acertar_estoque.php', 'ACERTAR_ESTOQUE', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function relatorio_compra_producao() {
    with(document.form) {
        method = 'post'
        action = 'relatorio_compra_producao.php'
        target = 'RELATORIO_COMPRA_PRODUCAO'
    }
    nova_janela('relatorio_compra_producao.php', 'RELATORIO_COMPRA_PRODUCAO', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function relatorio_ed_maior_que_zero() {
    with(document.form) {
        method = 'post'
        action = 'relatorio_ed_maior_que_zero.php'
        target = 'RELATORIO_ED_MAIOR_QUE_ZERO'
    }
    nova_janela('relatorio_ed_maior_que_zero.php', 'RELATORIO_ED_MAIOR_QUE_ZERO', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function producao(valor) {
    document.form.cmd_limpar.click()//Reseta a Tela em sua posição Inicial ...
    
    document.form.txt_referencia.value      = 'ESP'//Referência ESP ...
    document.form.cmb_operacao_custo.value  = 0//Operação de Custo = Industrial ...
    
    controle_operacao_custo()
    controle_hidden_operacao_custo()
    
    document.form.chkt_exibir_esp.checked   = true
    
    if(valor == 1) {//Produção Macho ESP ...
        document.form.txt_discriminacao.value           = 'MACHO%'
        document.form.chkt_mostrar_componentes.checked  = true
    }else if(valor == 2) {//Produção ESP ...
        document.form.chkt_est_disp_comp_zero.checked   = true
    }else if(valor == 3) {//Produção Componente ESP ...
        document.form.cmb_familia.value                 = 23
        document.form.chkt_mostrar_componentes.checked  = true
    }
    
    with(document.form) {
        method = 'post'
        action = 'relatorio_compra_producao.php'
        target = 'RELATORIO_COMPRA_PRODUCAO'
    }
    nova_janela('relatorio_compra_producao.php', 'RELATORIO_COMPRA_PRODUCAO', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    /*Dou esse tempinho de 0,5 segundo p/ submeter as informações de modo a garantir que o que foi submetido chegue ao mesmo tempo com o Pop-Up 
    que será aberto ...*/
    setTimeout("document.form.submit()", 500)
}

function consultar() {
    with(document.form) {
        method = 'post'
        action = '<?=$PHP_SELF."?passo=1";?>'
        target = '_self'
    }
}
</Script>
</head>
<body onload='controle_operacao_custo();iniciar()'>
<form name='form'>
<input type='hidden' name='passo' value='1'>
<!--**********************Gambiarra**********************
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro lá no outro
passo da consulta*/-->
<input type='hidden' name="hidden_operacao_custo">
<input type='hidden' name="hidden_operacao_custo_sub">
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Estoque
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name='txt_fornecedor' title='Digite o Fornecedor' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>N.º da OP</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_numero_op' title='Digite o N.º da OP' class='caixadetexto'>
            &nbsp;
            <font color='darkblue'>
                <b>(Pesquisa Exata)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Família
        </td>
        <td>
            <select name='cmb_familia' title='Selecione a Família' class='combo'>
            <?
                $sql = "SELECT id_familia, nome 
                        FROM `familias` 
                        WHERE `ativo` = 1 ORDER BY nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo P.A.
        </td>
        <td>
            <select name='cmb_grupo_pa' title='Selecione o Grupo P.A.' class='combo'>
            <?
                $sql = "SELECT id_grupo_pa, nome 
                        FROM `grupos_pas` 
                        WHERE `ativo` = '1' ORDER BY nome";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Operação de Custo
        </td>
        <td>
            <select name='cmb_operacao_custo' title='Selecione a Operação de Custo' onchange='controle_operacao_custo();controle_hidden_operacao_custo()' class='combo'>
                <option value=''>SELECIONE</option>
                <option value='0'>Industrialização</option>
                <option value='1'>Revenda</option>
            </select>
            &nbsp;
            <select name='cmb_operacao_custo_sub' title='Selecione a Sub-Operação' onchange='controle_hidden_operacao_custo_sub()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0' selected>Industrialização</option>
                <option value='1'>Revenda</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_est_disp_comp_zero' id='chkt_est_disp_comp_zero' value='1' title='Estoque Disponível / Comprometido < 0' onclick='func_est_disp_comp_zero()' class='checkbox'>
            <label for='chkt_est_disp_comp_zero'>
                Estoque Disponível / Comprometido < 0
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_exibir_esp' id='chkt_exibir_esp' value='1' title='Exibir PA(s) do Tipo ESP' class='checkbox' checked>
            <label for='chkt_exibir_esp'>
                Exibir PA(s) do Tipo ESP
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_mostrar_componentes' id='chkt_mostrar_componentes' value='1' title='Mostrar Componentes' class='checkbox'>
            <label for='chkt_mostrar_componentes'>
                Mostrar Componentes
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_nao_mostrar_prazo_entrega' id='chkt_nao_mostrar_prazo_entrega' value='1' title='Não Mostrar Prazo de Entrega' class='checkbox'>
            <label for='chkt_nao_mostrar_prazo_entrega'>
                Não Mostrar Prazo de Entrega
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_prod_prazo_atual_sete' id='chkt_prod_prazo_atual_sete' value='1' title='Produtos com Prazo de Atualização > 7 dias' onclick='func_prod_prazo_atual_sete()' class='checkbox'>
            <label for='chkt_prod_prazo_atual_sete'>
                Produtos com Prazo de Atualização > 7 dias
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_mostrar_top' id='chkt_mostrar_top' value='1' title='Mostrar TOP' class='checkbox'>
            <label for='chkt_mostrar_top'>
                Mostrar TOP
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_entrada_antecipada_maior_zero' id='chkt_entrada_antecipada_maior_zero' value='1' title='Entrada Antecipada' class='checkbox'>
            <label for='chkt_entrada_antecipada_maior_zero'>
                Entrada Antecipada > 0
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            //Este botão só aparece para o Rivaldo 27, Roberto 62, "Dárcio 98 e Netto 147" que são programadores ...
            if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
        ?>
            <input type='button' name='cmd_acertar_estoque' value='Acertar Estoque' title='Acertar Estoque' onclick='acertar_estoque()' style='color:purple' class='botao'>
        <?
            }
        ?>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='controle_operacao_custo();iniciar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='consultar()' class='botao'>
            <input type='button' name='cmd_relatorio_qtde_pecas_embalar' value='Relatório Qtde de Peças a Embalar' title='Relatório Qtde de Peças a Embalar' onclick='relatorio_qtde_pecas_embalar()' style='color:black' class='botao'>
            <input type='button' name='cmd_relatorio_compra_producao' value='Relatório p/ Compra Produção' title='Relatório p/ Compra Produção' onclick='relatorio_compra_producao()' style='color:red' class='botao'>
            <input type='button' name='cmd_relatorio_ed_maior_que_zero' value='Relatório ED > 0' title='Relatório ED > 0' onclick='relatorio_ed_maior_que_zero()' style='color:green' class='botao'>
            <p/>
            <input type='button' name='cmd_prod_macho_esp' value='Produção Macho ESP' title='Produção Macho ESP' onclick='producao(1)' style='color:purple' class='botao'>
            <input type='button' name='cmd_prod_esp' value='Produção ESP' title='Produção ESP' onclick='producao(2)' style='color:purple' class='botao'>
            <input type='button' name='cmd_comp_esp' value='Produção Componente ESP' title='Produção Componente ESP' onclick='producao(3)' style='color:purple' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>