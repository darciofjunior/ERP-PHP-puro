<?
//Função que contém a Tela Principal de Filtro ...
function filtro($nivel_arquivo_principal, $valor) {
	$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Consultar Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$nivel_arquivo_principal;?>/css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/nova_janela.js'></Script>
</head>
<body onLoad="document.form.txt_fornecedor.focus()">
<form name='form' method='post' action=''>
<input type='hidden' name='nivel_arquivo_principal' value='<?=$nivel_arquivo_principal;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Nota(s) Fiscal(is) de Entrada
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name="txt_fornecedor" title="Digite o Fornecedor" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da Nota
        </td>
        <td>
            <input type='text' name="txt_numero_nota" title="Digite o Número da Nota" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emissão
        </td>
        <td>
            <input type='text' name='txt_data_emissao' title='Digite a Data de Emissão' size='12' maxlength='10' onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src="<?=$nivel_arquivo_principal;?>/imagem/calendario.gif" width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('<?=$nivel_arquivo_principal;?>/calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> Calendário
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Entrega
        </td>
        <td>
            <input type='text' name='txt_data_entrega' title='Digite a Data de Entrega' size='12' maxlength='10' onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src="<?=$nivel_arquivo_principal;?>/imagem/calendario.gif" width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('<?=$nivel_arquivo_principal;?>/calendario/calendario.php?campo=txt_data_entrega&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> Calendário
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
            <font color='red'>
                <b>Observação / Marca</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_observacao' title='Digite a Observação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_aberto' value='1' title='Somente em aberto' id='label1' class='checkbox' checked>
            <label for='label1'>Somente em aberto</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_pago_pelo_caixa_compras' value='1' title='Pago pelo Caixa de Compras' id='label2' class='checkbox'>
            <label for='label2'>Pago pelo Caixa de Compras</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_fornecedor.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}

//Significa que o usuário já fez pelo menos uma consulta ...
if(!empty($cmd_consultar)) {
//Controle com os Checkbox ...
    if(!empty($chkt_aberto)) {//Somente Notas em Aberto ...
        $situacao = 2;
    }else {//Todos os Tipos de Notas, abertas ou fechadas, independente ...
        $situacao = 3;
    }
    if(!empty($chkt_pago_pelo_caixa_compras)) $condicao_pago_pelo_caixa_compras = " AND `pago_pelo_caixa_compras` = 'S' ";
    
//Tratamento com as Caixas de Data de Emissão e de Data de Entrega ...
    if(!empty($txt_data_emissao)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_emissao, 4, 1) != '-') $txt_data_emissao = data::datatodate($txt_data_emissao, '-');
//Aqui é para não dar erro de SQL
        $condicao_data_emissao = " AND SUBSTRING(nfe.`data_emissao`, 1, 10) = '$txt_data_emissao' ";
    }

    if(!empty($txt_data_entrega)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_entrega, 4, 1) != '-') $txt_data_entrega = data::datatodate($txt_data_entrega, '-');
//Aqui é para não dar erro de SQL
        $condicao_data_entrega = " AND SUBSTRING(nfe.`data_entrega`, 1, 10) = '$txt_data_entrega' ";
    }

//Aqui eu verifico se a Referência digitada do Produto, é uma referência de PA ...
    if(!empty($txt_referencia) || !empty($txt_discriminacao)) {
        if(!empty($txt_referencia)) {
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `referencia` = '$txt_referencia' 
                    AND `id_produto_insumo` > '0' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
//Significa que é uma referência de PI ...
            if($linhas == 0) {
                $condicao_referencia = "INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`referencia` LIKE '%$txt_referencia%' ";
//Significa que é uma referência de PA ...
            }else {
                $where_referencia = " AND pi.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' ";
            }
        }
/*Tenho que fazer esse controle, porque se a Nota Fiscal não tiver nenhum Item, então está não aparece
para consulta no Sistema ...*/
        $condicao_itens = "
                INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` 
                INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` AND pi.`discriminacao` LIKE '%$txt_discriminacao%' $where_referencia 
        ";
    }

    if(!empty($txt_observacao)) {
//Verifico se tem algum Item de Pedido com a Marca digitada no campo observação do Pedido ...
//Verifico se tem algum Item de NF com a Marca digitada no campo observação do Pedido ...
        $sql = "(SELECT DISTINCT(id_nfe) 
                FROM `nfe_historicos` 
                WHERE `marca` LIKE '%$txt_observacao%' GROUP BY id_nfe) 
                UNION 
                (SELECT DISTINCT(nfeh.id_nfe) 
                FROM `nfe_historicos` nfeh 
                INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
                WHERE ip.`marca` LIKE '%$txt_observacao%' GROUP BY id_nfe) ";
        $campos_nfe = bancos::sql($sql);
        $linhas_nfe = count($campos_nfe);
        for($l = 0; $l < $linhas_nfe; $l++) $id_nfes[] = $campos_nfe[$l]['id_nfe'];
        
        //Essa parte de Follow-Ups, substitui o antigo campo Observação que existia na tab. Pedidos ...
        $sql = "SELECT DISTINCT(`identificacao`) AS id_nfe 
                FROM `follow_ups` 
                WHERE `observacao` LIKE '%$txt_observacao%' 
                AND `origem` = '17' ";
        $campos_follow_up = bancos::sql($sql);
        $linhas_follow_up = count($campos_follow_up);
        for($i = 0; $i < $linhas_follow_up; $i++) $id_nfes[] = $campos_follow_up[$i]['id_nfe'];
        
        //Se encontrou pelo menos 1 Nota Fiscal ...
        $condicao_nfes = (count($id_nfes) > 0) ? " AND nfe.`id_nfe` IN (".implode(',', $id_nfes).") " : " AND nfe.`id_nfe` = '0' ";
    }

//Busca todas as NF(s) de acordo com o Filtro feito pelo usuário ...
    $sql = "SELECT nfe.*, f.razaosocial, e.nomefantasia 
            FROM `nfe` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` AND f.`razaosocial` LIKE '%$txt_fornecedor%' 
            INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
            $condicao_itens 
            $condicao_referencia 
            WHERE nfe.`num_nota` LIKE '%$txt_numero_nota%' 
            $condicao_nfes 
            $condicao_pago_pelo_caixa_compras 
            $condicao_notas_fiscais_vista_sem_cc 
            $condicao_data_emissao 
            $condicao_data_entrega 
            AND nfe.situacao < '$situacao' GROUP BY nfe.`id_nfe` 
            ORDER BY `data_entrega` DESC ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
//Não retornou nenhum registro, então requisito a Tela de Filtro ...
    if($linhas == 0) {
//Aqui eu chamo a Tela Principal de Filtro ...
        filtro($nivel_arquivo_principal, 1);
    }
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($nivel_arquivo_principal, '');
}
?>