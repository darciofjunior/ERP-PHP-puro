<?
//Função que contém a Tela Principal de Filtro ...
function filtro($nivel_arquivo_principal, $valor) {
	$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Consultar Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$nivel_arquivo_principal;?>/css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/nova_janela.js'></Script>
</head>
<body onload='document.form.txt_fornecedor.focus()'>
<form name='form' method='post' action=''>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Pedido(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name="txt_fornecedor" title="Digite o Fornecedor" size="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número do Pedido
        </td>
        <td>
            <input type='text' name="txt_numero_pedido" title="Digite o Número do Pedido" size="12" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência do P.A.
        </td>
        <td>
            <input type='text' name="txt_referencia_pa" title="Digite a Referência do P.A." size="15" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação" size="45" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Importação
        </td>
        <td>
            <input type='text' name="txt_importacao" title="Digite a Importação" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>Obs / Marca / Follow-UP</b>
            </font>
        </td>
        <td>
            <input type='text' name="txt_observacao" title="Digite a Observação" size="35" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_pedidos_estrangeiros' value='1' title='Selecionar Somente Pedidos Estrangeiros / Todos Estrangeiros' id='label1' class='checkbox'>
            <label for='label1'>Somente Pedidos Estrangeiros / Todos Estrangeiros</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_pedidos_com_itens_em_aberto' value='1' title='Selecionar Pedidos c/ Itens em Aberto' id='label2' class='checkbox' checked>
            <label for='label2'>Pedidos c/ Itens em Aberto</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_programados_descontabilizados' value='1' title='Selecionar Programado Descontabilizado' id='label3' class='checkbox'>
            <label for='label3'>Programado Descontabilizado</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Quantidade de Dias para Follow-up
        </td>
        <td>
            <select name='cmb_qtde_dias' title='Selecione a Qtde de Dias' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    for($i = 1; $i <= 50; $i++) {
                ?>
                    <option value='<?=$i;?>'><?=$i;?></option>
                <?
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_fornecedor.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='nivel_arquivo_principal' value='<?=$nivel_arquivo_principal;?>'>
</form>
</body>
</html>
<?}

//Significa que o usuário já fez pelo menos uma consulta ...
if(!empty($cmd_consultar)) {
    $ativo = " AND p.`ativo` = '1' ";
    //Controle com os Checkbox ...

    //1) Somente Pedidos Estrangeiros
    if(!empty($chkt_pedidos_estrangeiros)) $condicao_estrangeiros = " AND f.`id_pais` <> '31' ";

    //2) Pedidos c/ Itens em Aberto ...
    if(!empty($chkt_pedidos_com_itens_em_aberto)) $condicao_pedidos_com_itens_em_aberto = " INNER JOIN `itens_pedidos` ip ON ip.`id_pedido` = p.`id_pedido` AND ip.`status` < '2' ";

    //3) Programados Descontabilizados ...
    if(!empty($chkt_programados_descontabilizados)) {
        $condicao_programados_descontabilizados = " AND (p.`programado_descontabilizado` = 'S' AND p.`ativo` = '0') "; 
        $ativo = '';
    }
//Se o usuário fez consulta por Referência do P.A. ...
    if(!empty($txt_referencia_pa)) {
        $sql = "SELECT DISTINCT(`id_produto_insumo`) 
                FROM `produtos_acabados` 
                WHERE `referencia` LIKE '%$txt_referencia_pa%' 
                AND `id_produto_insumo` IS NOT NULL ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {//Se não encontrar nenhum registro então retorno Zero, nessa variável p/ não furar o SQL
            $id_produto_insumos = 0;
        }else {//Se encontrar pelo menos 1 ...
            for($i = 0; $i < $linhas; $i++) {$id_produto_insumos.= $campos[$i]['id_produto_insumo'].', ';}
            $id_produto_insumos = substr($id_produto_insumos, 0, strlen($id_produto_insumos) - 2);
        }
    }
//Se o usuário fez consulta por Referência do P.A. e por Discriminacao ...
    if(!empty($txt_referencia_pa) && !empty($txt_discriminacao)) {
        if(!empty($condicao_pedidos_com_itens_em_aberto)) {
            $condicao_pedidos_com_itens_em_aberto.= "
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                    AND pi.`id_produto_insumo` IN ($id_produto_insumos) 
                    AND pi.`discriminacao` LIKE '%$txt_discriminacao%' ";
        }else {
            $condicao_pedidos_com_itens_em_aberto = "
                    INNER JOIN `itens_pedidos` ip ON ip.`id_pedido` = p.`id_pedido` 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                    AND pi.`id_produto_insumo` IN ($id_produto_insumos) 
                    AND pi.`discriminacao` LIKE '%$txt_discriminacao%' ";
        }
//Se o usuário fez consulta por Referência do P.A. ...
    }else if(!empty($txt_referencia_pa) && empty($txt_discriminacao)) {
        if(!empty($condicao_pedidos_com_itens_em_aberto)) {
            $condicao_pedidos_com_itens_em_aberto.= "
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                    AND pi.`id_produto_insumo` IN ($id_produto_insumos) ";
        }else {
            $condicao_pedidos_com_itens_em_aberto = "
                    INNER JOIN `itens_pedidos` ip ON ip.`id_pedido` = p.`id_pedido` 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                    AND pi.`id_produto_insumo` IN ($id_produto_insumos) ";
        }
//Se o usuário fez consulta por Discriminação ...
    }else if(empty($txt_referencia_pa) && !empty($txt_discriminacao)) {
        if(!empty($condicao_pedidos_com_itens_em_aberto)) {
            $condicao_pedidos_com_itens_em_aberto.= "
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                    AND pi.`discriminacao` LIKE '%$txt_discriminacao%' ";
        }else {
            $condicao_pedidos_com_itens_em_aberto = "
                    INNER JOIN `itens_pedidos` ip ON ip.`id_pedido` = p.`id_pedido` 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                    AND pi.`discriminacao` LIKE '%$txt_discriminacao%' ";
        }
    }
    //Se o usuário fez consulta por nome de Importação ...
    if(!empty($txt_importacao)) $condicao_importacao = "INNER JOIN `importacoes` i ON i.`id_importacao` = p.`id_importacao` AND i.`nome` LIKE '%$txt_importacao%' ";
    
    if(!empty($cmb_qtde_dias)) {
        $data_dias              = data::datatodate(data::adicionar_data_hora(data::datetodata(date('Y-m-d H:m:s'), '-'), $cmb_qtde_dias), '-');
        $condicao_prazo_entrega = " AND p.`prazo_entrega` <= '$data_dias' AND f.`aparecer_follow_up` = 'S' ";
        $order_by               = " ORDER BY p.`prazo_entrega` DESC ";
    }else {
        $order_by               = " ORDER BY p.`data_emissao` DESC, p.`id_pedido` DESC ";
    }

//Filtro de Pedidos normal ...
    $sql = "SELECT DISTINCT(p.`id_pedido`) 
            FROM `pedidos` p 
            INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` $condicao_estrangeiros 
            $condicao_importacao 
            $condicao_pedidos_com_itens_em_aberto 
            AND f.`razaosocial` LIKE '%$txt_fornecedor%' 
            WHERE p.`id_pedido` LIKE '%$txt_numero_pedido%' 
            $condicao_prazo_entrega 
            $ativo 
            $condicao_programados_descontabilizados ";
    $campos_pedidos = bancos::sql($sql);
    $linhas_pedidos = count($campos_pedidos);
    for($l = 0; $l < $linhas_pedidos; $l++) $id_pedidos[] = $campos_pedidos[$l]['id_pedido'];

    if(!empty($txt_observacao)) {
        if(!empty($condicao_pedidos_com_itens_em_aberto)) {
            $condicao_pedidos_com_itens_em_aberto.= " AND ip.`marca` LIKE '%$txt_observacao%' ";
        }else {
            $condicao_pedidos_com_itens_em_aberto = " INNER JOIN `itens_pedidos` ip ON ip.`id_pedido` = p.`id_pedido` AND ip.`status` < '2' ";
            $condicao_pedidos_com_itens_em_aberto.= " AND ip.`marca` LIKE '%$txt_observacao%' ";
        }
//Verifico se tem algum Item de Pedido com a Marca digitada no campo observação do Pedido ...
//Filtro somente para Observação de Follow-UP de Pedido ...
        $sql = "SELECT DISTINCT(p.`id_pedido`) AS id_pedido 
                FROM `pedidos` p 
                INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` $condicao_estrangeiros 
                $condicao_importacao 
                $condicao_pedidos_com_itens_em_aberto 
                AND f.`razaosocial` LIKE '%$txt_fornecedor%' 
                WHERE p.`id_pedido` LIKE '%$txt_numero_pedido%' 
                $condicao_prazo_entrega 
                $ativo 
                $condicao_programados_descontabilizados 
                $order_by ";
        $campos_pedidos = bancos::sql($sql);
        $linhas_pedidos = count($campos_pedidos);
        for($l = 0; $l < $linhas_pedidos; $l++) $id_pedidos[] = $campos_pedidos[$l]['id_pedido'];
        
        //Essa parte de Follow-Ups, substitui o antigo campo Observação que existia na tab. Pedidos ...
        $sql = "SELECT DISTINCT(`identificacao`) AS id_pedido 
                FROM `follow_ups` 
                WHERE `observacao` LIKE '%$txt_observacao%' 
                AND `origem` = '16' ";
        $campos_follow_up = bancos::sql($sql);
        $linhas_follow_up = count($campos_follow_up);
        if($linhas_follow_up > 0) {
            for($i = 0; $i < $linhas_follow_up; $i++) $vetor_follow_ups[] = $campos_follow_up[$i]['id_pedido'];
            $condicao_follow_ups = " AND p.`id_pedido` IN (".implode(',', $vetor_follow_ups).") ";
        }
    }
//Arranjo Ténico
    if(count($id_pedidos) == 0) {$id_pedidos[] = '0';}
    $vetor_pedidos = implode(',', $id_pedidos);
    $condicao_pedidos = " p.`id_pedido` IN ($vetor_pedidos) ";

    $sql = "SELECT DISTINCT(p.id_pedido), e.nomefantasia, f.razaosocial, f.id_pais, p.prazo_entrega, p.prazo_navio, p.tipo_nota, p.tipo_export, p.programado_descontabilizado, p.data_emissao, p.status, p.valor_ped, p.valor_pendencia, CONCAT(tm.simbolo, ' ') AS simbolo 
            FROM `pedidos` p 
            INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = p.`id_tipo_moeda` 
            WHERE $condicao_pedidos 
            $condicao_follow_ups 
            $order_by ";
//Não entendi o do porque faz isso, não me lembro disso aqui ????? - Dárcio
//Aki é um caso especial para os Pedidos Estrangeiros
    if(!empty($chkt_pedidos_estrangeiros)) {
        $campos = bancos::sql($sql, $inicio, 500, 'sim', $pagina);
        $linhas = count($campos);
        for($j = 0; $j < $linhas; $j++) {
//Data Corrente
            $data_entrega = $campos[$j]['prazo_entrega'];
            if($campos[$j]['id_pais'] == 31) {
                $data_entrega = str_replace('-', '', $data_entrega);
//Armazena as datas nesse vetor
                $vetor_data_chegada[$j][0] = $data_entrega;
//Armazena os índices nesse vetor
                $vetor_data_chegada[$j][1] = $j;
            }else {
                $prazo_viagem_navio = $campos[$j]['prazo_navio'];
                $data_entrega = data::adicionar_data_hora(data::datetodata($data_entrega, '-'), $prazo_viagem_navio);
                $data_entrega = data::datatodate($data_entrega, '');
//Armazena as datas nesse vetor
                $vetor_data_chegada[$j][0] = $data_entrega;
//Armazena os índices nesse vetor
                $vetor_data_chegada[$j][1] = $j;
            }
        }
        //Arranjo Técnico (rsrs) ...
        if($linhas > 0) sort($vetor_data_chegada);//Ordena o vetor na ordem certa crescente
//Aqui é para os Pedidos Normais
    }else {
        $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
        $linhas = count($campos);
        for($j = 0; $j < $linhas; $j++) $vetor_data_chegada[$j][1] = $j;
    }
//Não retornou nenhum registro, então requisito a Tela de Filtro ...
    if($linhas == 0) filtro($nivel_arquivo_principal, 1);//Aqui eu chamo a Tela Principal de Filtro ...
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($nivel_arquivo_principal, '');
}
?>