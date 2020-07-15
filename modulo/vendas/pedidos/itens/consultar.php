<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/financeiros.php');
require('../../../../modulo/classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    /*Faço um SQL a parte para evitar um JOIN com a tabela de pedidos_vendas_itens que não se comporta 
    muito bem com a tabela de pedidos_vendas pesando muitíssimo ...*/
    if(!empty($txt_referencia) || !empty($txt_discriminacao) || !empty($cmb_representante) 
    || !empty($cmb_empresa_divisao) || $chkt_ped_em_aberto == 1 || $chkt_ped_nao_liberado == 1 
    || $chkt_pedidos_com_vales == 1 || $chkt_ped_em_aberto_superior_6_meses == 1) {
        //Trago todos os Pedidos que o Representante vendeu durante toda a sua vida ...
        if(!empty($cmb_representante)) $condicao_representante = " AND pvi.`id_representante` = '$cmb_representante' ";
        
        //Busca Somente dos Pedidos em aberto ...
        if($chkt_ped_em_aberto == 1) {
            $condicao                   = " AND pv.`status` < '2' ";
            $condicao_itens_em_aberto   = " AND pvi.`status` < '2' ";//Itens que estejam em Aberto ou Parcial ...
        }

        //Que contêm pelo menos 1 item ...
        if($chkt_ped_nao_liberado == 1) $condicao_liberado = " AND pv.`liberado` = '0' ";
        
        //Item(ns) que foi(ram) enviado(s) em Vale ...
	if($chkt_pedidos_com_vales == 1) $condicao_itens_com_vale = " AND pvi.`vale` > '0' ";
        
        if($chkt_ped_em_aberto_superior_6_meses == 1) {
            $condicao_6_meses           = " AND (pv.`data_emissao` < DATE_ADD('".date('Y-m-d')."', INTERVAL -180 DAY)) AND pv.`status` < '2' ";
            $order_by                   = " pv.`data_emissao` DESC, pv.`id_pedido_venda` DESC ";
        }
        
        if(!empty($cmb_empresa_divisao)) {
            $inner_join_especial = "
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` = '$cmb_empresa_divisao' ";
        }else {
            if(!empty($txt_referencia) || !empty($txt_discriminacao)) {
                $inner_join_especial = " INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' ";
            }
        }

        /*Trago todos os Pedidos na tabela de Itens que atendem a Claúsula acima ...
        Obs: WHERE 1, macete p/ não dar erro de SQL ...*/
        $sql = "SELECT DISTINCT(pvi.`id_pedido_venda`) 
                FROM `pedidos_vendas_itens` pvi 
                $inner_join_especial 
                WHERE 1 
                $condicao_representante 
                $condicao_itens_em_aberto 
                $condicao_itens_com_vale ";
        $campos_pedido_vendas = bancos::sql($sql);
        $linhas_pedido_vendas = count($campos_pedido_vendas);
        if($linhas_pedido_vendas == 0) {//Não encontrou nenhum Item na condição acima ...
            $condicao_pedidos_vendas = " AND pv.`id_pedido_venda` = '0' ";//Macete p/ não furar o SQL abaixo ...
        }else {//Encontrou pelo menos 1 item ...
            for($i = 0; $i < $linhas_pedido_vendas; $i++) $vetor_pedido_vendas[] = $campos_pedido_vendas[$i]['id_pedido_venda'];
            $condicao_pedidos_vendas = " AND pv.`id_pedido_venda` IN (".implode($vetor_pedido_vendas, ',').") ";
        }
    }
    
//Busca Todos os Pedidos que são de fora do Brasil (Estrangeiros) ...
    if($chkt_pedidos_exportacao == 1) $condicao_estrangeiros = " AND c.`id_pais` <> '31' ";

//Busca Somente dos Pedido(s) Programado(s) ...
    if($chkt_pedidos_programados == 1) {
        $data_atual_mais_dois = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 2), '-');
        $condicao_programado = " AND pv.`faturar_em` >= '$data_atual_mais_dois' "; 
    }
//Busca Somente dos Pedido(s) Livre de Débito ...
    if($chkt_pedidos_livre_debito == 'S') $condicao_livre_debito = " AND pv.`livre_debito` = 'S' ";
//Busca Somente Expresso ...
    if($chkt_somente_expresso == 'S') $condicao_somente_expresso = " AND pv.`expresso` = 'S' ";
    
    //Significa que se deseja trazer todos os Clientes (Pendentes) e não Faturáveis
    $data_atual_mais_um     = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');
    $data_atual_menos_sete  = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -7), '-');
    
    if($chkt_clientes_nao_faturaveis == 1) {//nesse caso o Order By Tem q ser pelo cliente ...
        $condicao_super_extra = " AND (pv.`faturar_em` > '$data_atual_mais_um' OR pv.`condicao_faturamento` > 2 OR c.`credito` IN ('C', 'D')) ";
        /*Eu tenho q colocar 2 campos nesse order by porque, nem sempre eu vou ter os 2 campos juntos na hora
        de apresentação da tela, uma hora vou ter só o nomefantasia, outra hora só a razãosocial*/
        $order_by = 'c.nomefantasia, c.razaosocial ';
    }
    
    if(!empty($txt_observacao)) {
        //Aqui eu trago o Orçamento através das Observações que foram Registradas em Follow-Ups ...
        $sql = "SELECT `identificacao` 
                FROM `follow_ups` 
                WHERE `origem` = '2' 
                AND `observacao` LIKE '%$txt_observacao%' ";
        $campos_follow_ups = bancos::sql($sql);
        $linhas_follow_ups = count($campos_follow_ups);
        if($linhas_follow_ups > 0) {
            for($i = 0; $i < $linhas_follow_ups; $i++) $vetor_pedido_vendas[] = $campos_follow_ups[$i]['identificacao'];
            $condicao_pedidos_vendas = " AND pv.`id_pedido_venda` IN (".implode($vetor_pedido_vendas, ',').") ";
        }else {//Não encontrou nenhum Item ...
            /*Se essa variável não foi abastecida mais acima, então faço esse tratamento p/ 
            não furar a Query mais abaixo ...*/
            if(empty($condicao_pedidos_vendas)) $condicao_pedidos_vendas = " AND pv.`id_pedido_venda` = '0' ";
        }
    }
    
    if(!empty($cmb_uf)) $condicao_uf = " AND c.`id_uf` LIKE '$cmb_uf' ";
    if(empty($order_by)) $order_by = 'pv.data_emissao DESC, pv.id_pedido_venda DESC ';

    $sql = "SELECT DISTINCT(pv.`id_pedido_venda`), pv.`id_cliente_contato`, pv.`id_empresa`, 
            pv.`finalidade`, pv.`faturar_em`, pv.`data_emissao`, pv.`valor_ped`, pv.`condicao_faturamento`, 
            pv.`vencimento1`, pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pv.`status`, pv.`liberado`, 
            pv.`valor_pendencia`, c.`id_cliente`, 
            IF(c.`razaosocial` = '', c.`nomefantasia`, CONCAT(c.`razaosocial`, ' - ', c.`nomefantasia`)) AS cliente, 
            c.`credito` 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`cidade` LIKE '%$txt_cidade%' $condicao_uf 
            WHERE pv.id_pedido_venda LIKE '$txt_numero_pedido%' 
            AND pv.num_seu_pedido LIKE '%$txt_seu_pedido_numero%' 
            $condicao 
            $condicao_liberado 
            $condicao_estrangeiros 
            $condicao_6_meses 
            $condicao_programado 
            $condicao_livre_debito 
            $condicao_somente_expresso 
            $condicao_super_extra 
            $condicao_pedidos_vendas ORDER BY $order_by ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
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
<title>.:: Consultar Pedidos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_pedido_venda, credito) {
    if(credito == 'C' || credito == 'D') alert('CLIENTE COM CRÉDITO '+credito+' !\n POR FAVOR CONTATAR O DEPTO. FINANCEIRO !')
    window.location = 'index.php?id_pedido_venda='+id_pedido_venda
}
</Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Consultar Pedido(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Ped
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Faturar Em
        </td>
        <td>
            Condição de<br>Faturamento
        </td>
        <td>
            Vale
        </td>
        <td>
            Cliente / Pendência
        </td>
        <td>
            Contato
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento / Forma de Venda' style='cursor:help'>
                Emp / Tp Nota / <br>Prazo Pgto / Forma
            </font>
        </td>
        <td>
            Valor R$
        </td>   
        <td>
            Valor Pend. R$
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width="10">
            <a href="javascript:prosseguir('<?=$campos[$i]['id_pedido_venda'];?>', '<?=financeiros::controle_credito($id_cliente);?>')" class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_pedido_venda'];?>', '<?=financeiros::controle_credito($id_cliente);?>')" class='link'>
            <?
                if($campos[$i]['status'] == 1) {//Pedido em Aberto
            ?>
                    <font title='Pedido em Aberto' color='red'>
                    <?
                        echo $campos[$i]['id_pedido_venda'];
                        if($campos[$i]['liberado'] == 0) "<br><font title='Não Liberado' style='cursor:help'><b>Ñ LIB</b></font>";
                    ?>
                    </font>
            <?
                }else {//Pedido Concluído
            ?>
                    <font title='Pedido Concluído'>
                        <?=$campos[$i]['id_pedido_venda'];?>
                    </font>
            <?
                }
            ?>
            </a>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
        <?
            if($campos[$i]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
                if($campos[$i]['faturar_em'] > $data_atual_mais_um) {
                    echo '<font color="red">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }else {
                    echo '<font color="green">'.data::datetodata($campos[$i]['faturar_em'], '/').'</font>';
                }
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['credito'] == 'C' || $campos[$i]['credito'] == 'D') {
                echo '<font color="red">CRÉDITO '.$campos[$i]['credito'].'</font>';
            }else {
                $condicao_faturamento = array_sistema::condicao_faturamento();
                echo $condicao_faturamento[$campos[$i]['condicao_faturamento']];
            }
        ?>
        </td>
        <td>
        <?
//Aqui eu verifico se existe pelo menos 1 item desse que Pedido que contém Vale ...
            $sql = "SELECT `id_pedido_venda_item` 
                    FROM `pedidos_vendas_itens` 
                    WHERE `id_pedido_venda` = '".$campos[$i]['id_pedido_venda']."' 
                    AND `vale` > '0' LIMIT 1 ";
            $campos_vale = bancos::sql($sql);
            if(count($campos_vale) == 1) echo '<font color="blue"><b>SIM</b></font>';
        ?>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../../../classes/pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$campos[$i]['id_cliente'];?>', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Relatório de Pendências' class='link'>
                <?=$campos[$i]['cliente'];?>
            </a>
            <?
/**********************************************************************************************************/
/*Essa verificação é para facilitar p/ saber se o Representante já visitou ou não o Cliente*/
/**********************************************************************************************************/
/*1) Verifico se foi registrado algum Follow de Pendência nos últimos 7 dias e se o funcionário 
com a qual emitiu essa ocorrência é um Supervisor de Vendas '25', um vendedor Externo '27' ou 
um vendedor Interno '47'*/
                $sql = "SELECT f.`nome` 
                        FROM `follow_ups` fu 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = fu.`id_funcionario` 
                        WHERE fu.`origem` = '9' 
                        AND SUBSTRING(fu.`data_sys`, 1, 10) >= '$data_atual_menos_sete' 
                        AND fu.`id_cliente_contato` = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
                $campos_funcionario = bancos::sql($sql);
                if(count($campos_funcionario) == 1) {//Existe nos últimos 7 dias ...
            ?>
                    &nbsp;
                    <a href="javascript:nova_janela('../../../classes/cliente/follow_up.php?identificacao=<?=$campos[$i]['id_pedido_venda'];?>&origem=2', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='<?=$campos_funcionario[0]['nome'];?> registrou Follow-UP de Pendência nos últimos 7 dias' style='cursor:help'>
                        <img width='23' height='18' title='<?=$campos_funcionario[0]['nome'];?> registrou Follow-UP de Pendência nos últimos 7 dias' border="0" src = '../../../../imagem/olho_red.jpg'>
                    </a>
            <?
                }
                /*Verifico se o Pedido contém pelo menos 1 item, desde que essa opção "Só Pedido(s) em Aberto 
                e c/ Itens" não esteja marcada ...*/
                if($chkt_ped_em_aberto != 1) {
                    $sql = "SELECT `id_pedido_venda_item` 
                            FROM `pedidos_vendas_itens` 
                            WHERE `id_pedido_venda` = '".$campos[$i]['id_pedido_venda']."' LIMIT 1 ";
                    $campos_itens_pedido = bancos::sql($sql);
                    $qtde_itens_pedido = count($campos_itens_pedido);
                    if($qtde_itens_pedido == 0) echo ' <font color="red">(S/ ITENS)</font>';
                }
            ?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT `nome` 
                    FROM `clientes_contatos` 
                    WHERE `id_cliente_contato` = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
            $campos_contato = bancos::sql($sql);
            echo $campos_contato[0]['nome'];
        ?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }

            if($campos[$i]['id_empresa'] == 1) {
                echo '(A - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa'] ==2 ) {
                echo '(T - NF) / '.$prazo_faturamento;
            }else if($campos[$i]['id_empresa'] == 4) {
                echo '(G - SGD) / '.$prazo_faturamento;
            }else {
                echo 'Erro';
            }
            if($campos[$i]['finalidade'] == 'C') {
                echo ' / CONSUMO';
            }else if($campos[$i]['finalidade'] == 'I') {
                echo ' / INDUSTRIALIZAÇÃO';
            }else {
                echo ' / REVENDA';
            }
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_ped'], 2, ',', '.');?>
        </td>  
        <td align='right'>
            <?
                if($campos[$i]['valor_pendencia'] != 0) echo number_format($campos[$i]['valor_pendencia'], 2, ',', '.');
            ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
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
<title>.:: Consultar Pedidos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function controlar_objetos() {
	if(document.form.chkt_ped_em_aberto_superior_6_meses.checked == true) {
//Desabilita e desmarca a opção de Pedidos em Aberto ...		
		document.form.chkt_ped_em_aberto.disabled = true
		document.form.chkt_ped_em_aberto.checked = false
	}else {
//Volta a habilitar e checar a opção de Pedidos em Aberto ...
		document.form.chkt_ped_em_aberto.disabled = false
		document.form.chkt_ped_em_aberto.checked = true
	}
} 
</Script>
</head>
<body onload="document.form.txt_cliente.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Pedido(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número do Pedido
        </td>
        <td>
            <input type='text' name='txt_numero_pedido' title='Digite o Número do Pedido' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Seu Pedido N.º
        </td>
        <td>
            <input type='text' name="txt_seu_pedido_numero" title="Digite o Seu Pedido N.º" class='caixadetexto'>
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
            <input type='text' name="txt_discriminacao" title="Digite a Discriminação" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Representante
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa Divisão
        </td>
        <td>
            <select name='cmb_empresa_divisao' title='Selecione a Empresa Divisão' class='combo'>
            <?
                $sql = "SELECT id_empresa_divisao, razaosocial 
                        FROM `empresas_divisoes` 
                        ORDER BY razaosocial ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name="txt_observacao" title="Digite a Observação" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade
        </td>
        <td>
            <input type='text' name='txt_cidade' title='Digite a Cidade' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estado
        </td>
        <td>
            <select name='cmb_uf' title='Selecione o Estado' class='combo'>
            <?
                $sql = "SELECT id_uf, sigla 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY sigla ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ped_nao_liberado' value='1' title="Somente Pedido(s) não liberado(s) e c/ Item(ns)" class='checkbox' id='label1'>
            <label for="label1">Somente Pedido(s) não liberado(s) e c/ Item(ns)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ped_em_aberto' value='1' title="Só Pedido(s) em Aberto" class='checkbox' id='label2' checked>
            <label for="label2">Só Pedido(s) em Aberto e c/ Itens</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_pedidos_exportacao' value='1' title="Somente Pedido(s) de Exportação" class='checkbox' id='label3'>
            <label for="label3">Somente Pedido(s) de Exportação</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_pedidos_com_vales' value='1' title="Somente Pedido(s) que foram em Vale(s)" class='checkbox' id='label4'>
            <label for="label4">Somente Pedido(s) que foram em Vale(s)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ped_em_aberto_superior_6_meses' value='1' title='Somente Pedido(s) em Aberto, c/ itens e com Data de Emissão superior há 6 meses' id='label5' onclick='controlar_objetos()' class='checkbox'>
            <label for='label5'>
                <font color='red'>
                    <b>Somente Pedido(s) em Aberto, c/ itens e com Data de Emissão superior há 6 meses</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_pedidos_programados' value='1' title="Somente Pedido(s) Programado(s)" id='label6' onclick="controlar_objetos()" class='checkbox'>
            <label for="label6">
                <font color='darkblue'>
                    <b>Somente Pedido(s) Programado(s)</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_pedidos_livre_debito' value='S' title="Somente Pedido(s) Livre de Débito" id='label7' onclick="controlar_objetos()" class='checkbox'>
            <label for="label7">
                <font color='darkgreen'>
                    <b>Somente Pedido(s) Livre de Débito</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_somente_expresso' value='S' title='Somente Expresso' id='label8' onclick='controlar_objetos()' class='checkbox'>
            <label for='label8'>
                Somente Expresso
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_clientes_nao_faturaveis' value='1' title="Consultar todos os Clientes (Pendentes) e Não Faturáveis" class='checkbox' id='clientes_nao_faturaveis'>
            <label for="clientes_nao_faturaveis">Todos os Clientes (Pendentes) e Não Faturáveis</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_cliente.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>