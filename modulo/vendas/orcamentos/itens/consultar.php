<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
require('../../../../lib/vendas.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$txt_numero_orcamento           = $_POST['txt_numero_orcamento'];
        $txt_cliente                    = $_POST['txt_cliente'];
        $txt_referencia                 = $_POST['txt_referencia'];
        $txt_discriminacao              = $_POST['txt_discriminacao'];
        $cmb_representante              = $_POST['cmb_representante'];
        $cmb_negociacao_finalizada      = $_POST['cmb_negociacao_finalizada'];
        $txt_observacao                 = $_POST['txt_observacao'];
        $txt_cidade                     = $_POST['txt_cidade'];
        $cmb_uf                         = $_POST['cmb_uf'];
        $chkt_orc_nao_congelados        = $_POST['chkt_orc_nao_congelados'];
        $chkt_orc_com_itens_em_aberto   = $_POST['chkt_orc_com_itens_em_aberto'];
        $chkt_data_emissao_30dias       = $_POST['chkt_data_emissao_30dias'];
        $chkt_orcamentos_exportacao     = $_POST['chkt_orcamentos_exportacao'];
    }else {
        $txt_numero_orcamento           = $_GET['txt_numero_orcamento'];
        $txt_cliente                    = $_GET['txt_cliente'];
        $txt_referencia                 = $_GET['txt_referencia'];
        $txt_discriminacao              = $_GET['txt_discriminacao'];
        $cmb_representante              = $_GET['cmb_representante'];
        $cmb_negociacao_finalizada      = $_GET['cmb_negociacao_finalizada'];
        $txt_observacao                 = $_GET['txt_observacao'];
        $txt_cidade                     = $_GET['txt_cidade'];
        $cmb_uf                         = $_GET['cmb_uf'];
        $chkt_orc_nao_congelados        = $_GET['chkt_orc_nao_congelados'];
        $chkt_orc_com_itens_em_aberto   = $_GET['chkt_orc_com_itens_em_aberto'];
        $chkt_data_emissao_30dias       = $_GET['chkt_data_emissao_30dias'];
        $chkt_orcamentos_exportacao     = $_GET['chkt_orcamentos_exportacao'];
    }
    
    /*Faço um SQL a parte para evitar um JOIN com a tabela de orcamentos_vendas_itens que não se comporta 
    muito bem com a tabela de orcamentos_vendas pesando muitíssimo ...*/
    if(!empty($txt_referencia) || !empty($txt_discriminacao) || !empty($cmb_representante)) {
        //Trago todos os Pedidos que o Representante vendeu durante toda a sua vida ...
        if(!empty($cmb_representante)) $condicao_representante = " AND ovi.`id_representante` = '$cmb_representante' ";
        
        if(!empty($txt_referencia) || !empty($txt_discriminacao)) {
            $inner_join_especial = "INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' ";
        }
        
        /*Trago todos os Orçamentos na tabela de Itens que atendem a Claúsula acima ...
        Obs: WHERE 1, macete p/ não dar erro de SQL ...*/
        $sql = "SELECT DISTINCT(ovi.`id_orcamento_venda`) 
                FROM `orcamentos_vendas_itens` ovi 
                $inner_join_especial 
                WHERE 1 
                $condicao_representante ";
        $campos_orcamento_vendas = bancos::sql($sql);
        $linhas_orcamento_vendas = count($campos_orcamento_vendas);
        if($linhas_orcamento_vendas > 0) {//Encontrou pelo menos 1 item ...
            for($i = 0; $i < $linhas_orcamento_vendas; $i++) $vetor_orcamento_vendas[] = $campos_orcamento_vendas[$i]['id_orcamento_venda'];
            $condicao_orcamentos_vendas = " AND ov.`id_orcamento_venda` IN (".implode($vetor_orcamento_vendas, ',').") ";
        }else {//Não encontrou nenhum Item ...
            $condicao_orcamentos_vendas = " AND ov.`id_orcamento_venda` = '0' ";
        }
    }
    
    if(!empty($cmb_negociacao_finalizada)) $condicao_negociacao_finalizada = " AND ov.`negociacao_finalizada` = '$cmb_negociacao_finalizada' ";
    
    //Busca Somente dos Orçamentos que não estejam Congelado(s) ...
    if($chkt_orc_nao_congelados == 1) $condicao_congelar = " AND ov.`congelar` = 'N' ";
    
    //Busca Somente dos Orçamentos em Aberto ...
    if($chkt_orc_com_itens_em_aberto == 1) $condicao_orc_em_aberto = " AND ov.`status` < '2' ";
    
    //Busca dos Orçamentos em que a Data de Emissão <= 30 dias
    if($chkt_data_emissao_30dias == 1) {
        $data_atual_menos_30    = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -30), '-');
        $condicao_data_emissao  = " AND ov.`data_emissao` >= '$data_atual_menos_30' ";
    }
    
    //Busca só os Orçamentos que são de fora do Brasil
    if($chkt_orcamentos_exportacao == 1) $condicao_cliente = " AND c.`id_pais` <> '31' ";
    
    if(!empty($txt_observacao)) {
        //Aqui eu trago o Orçamento através das Observações que foram Registradas em Follow-Ups ...
        $sql = "SELECT `identificacao` 
                FROM `follow_ups` 
                WHERE `origem` = '1' 
                AND `observacao` LIKE '%$txt_observacao%' ";
        $campos_follow_ups = bancos::sql($sql);
        $linhas_follow_ups = count($campos_follow_ups);
        if($linhas_follow_ups > 0) {
            for($i = 0; $i < $linhas_follow_ups; $i++) $vetor_orcamento_vendas[] = $campos_follow_ups[$i]['identificacao'];
            $condicao_orcamentos_vendas = " AND ov.`id_orcamento_venda` IN (".implode($vetor_orcamento_vendas, ',').") ";
        }else {//Não encontrou nenhum Item ...
            /*Se essa variável não foi abastecida mais acima, então faço esse tratamento p/ 
            não furar a Query mais abaixo ...*/
            if(empty($condicao_orcamentos_vendas)) $condicao_orcamentos_vendas = " AND ov.`id_orcamento_venda` = '0' ";
        }
    }
    
    if(!empty($cmb_uf)) $condicao_uf = " AND c.`id_uf` LIKE '$cmb_uf' ";

    $sql = "SELECT DISTINCT(ov.`id_orcamento_venda`), ov.`id_cliente_contato`, 
            DATE_FORMAT(ov.`data_emissao`, '%d/%m/%Y') AS data_emissao, ov.`valor_orc`, ov.`finalidade`, 
            ov.`nota_sgd`, ov.`prazo_a`, ov.`prazo_b`, ov.`prazo_c`, ov.`prazo_d`, ov.`congelar`, 
            c.`id_uf`, c.`nomefantasia`, c.`razaosocial`, c.`cidade`, c.`credito`, ct.`tipo` 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`cidade` LIKE '%$txt_cidade%' $condicao_uf $condicao_cliente 
            LEFT JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
            WHERE ov.`id_orcamento_venda` LIKE '$txt_numero_orcamento%' 
            $condicao_orcamentos_vendas 
            $condicao_negociacao_finalizada 
            $condicao_congelar 
            $condicao_orc_em_aberto 
            $condicao_data_emissao 
            $condicao_negociacao_finalizada 
            ORDER BY ov.`data_emissao` DESC, ov.`id_orcamento_venda` DESC ";
    $campos = bancos::sql($sql, $inicio, 30, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Se não encontrou nenhum Orçamento ...
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar.php?valor=1'
        </Script>
<?
/*Esse parâmetro veio_itens_orcamento" é um Macete p/ o Sistema não ficar entrando automáticamente na Tela de Itens 
quando eu mal sai de lá pelo botão Voltar ...*/
    }else if($linhas == 1 && empty($veio_itens_orcamento)) {//Se encontrou apenas 1 Orçamento ...
?>
	<Script Language = 'JavaScript'>
            var credito = '<?=$campos[0]['credito'];?>'
            if(credito == 'C' || credito == 'D') {
                alert('CLIENTE COM CRÉDITO '+credito+' !\n POR FAVOR CONTATAR O DEPTO. FINANCEIRO !')
            }
            window.location = 'itens.php?id_orcamento_venda=<?=$campos[0]['id_orcamento_venda'];?>'
	</Script>
<?
    }else {//Se encontrou mais do que 1 Orçamento ...
?>
<html>
<head>
<title>.:: Orçamento(s) p/ Alterar / Imprimir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_orcamento_venda, credito) {
    if(credito == 'C' || credito == 'D') alert('CLIENTE COM CRÉDITO '+credito+' !\n POR FAVOR CONTATAR O DEPTO. FINANCEIRO !')
    window.location = 'itens.php?id_orcamento_venda='+id_orcamento_venda
}
</Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Orçamento(s) p/ Alterar / Imprimir 
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Orc
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Tipo
        </td>
        <td>
            Cidade
        </td>
        <td>
            Estado
        </td>
        <td>
            Contato
        </td>
        <td>
            Data de <br/>Validade
        </td>
        <td>
            <font title="Tipo de Nota / Prazo de Pagamento / Forma de Venda" style="cursor:help">
                Tp Nota / Prazo <br>Pgto / Forma
            </font>
        </td>
        <td>
            Observação
        </td>
        <td>
            <font title="Valor do Orçamento" style="cursor:help">
                Valor R$
            </font>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_orcamento_venda'];?>', '<?=$campos[$i]['credito'];?>')" class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_orcamento_venda'];?>', '<?=$campos[$i]['credito'];?>')" class='link'>
            <?
                if($campos[$i]['congelar'] == 'N') {//Significa q o Orçamento não está Congelado
            ?>
                    <font title="Orçamento não Congelado" color="red">
                            <?=$campos[$i]['id_orcamento_venda'];?>
                    </font>
            <?
                }else {//Orçamento Congelado
            ?>
                    <font title="Orçamento Congelado">
                            <?=$campos[$i]['id_orcamento_venda'];?>
                    </font>
            <?
                }
            ?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='left'>
            <font title="Nome Fantasia: <?=$campos[$i]['nomefantasia'];?>" style="cursor:help">
                <?=$campos[$i]['razaosocial'];?>
            </font>
            <?
//Aqui verifica se o Orçamento contém pelo menos 1 item
                $sql = "SELECT id_orcamento_venda_item 
                        FROM `orcamentos_vendas_itens` 
                        WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' LIMIT 1 ";
                $campos_itens_orcamento = bancos::sql($sql);
                $qtde_itens_orcamento = count($campos_itens_orcamento);
                if($qtde_itens_orcamento == 0) echo ' <font color="red">(S/ ITENS)</font>';
            ?>
        </td>
        <td>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
        <?
            $sql = "SELECT sigla 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_uf = bancos::sql($sql);
            echo $campos_uf[0]['sigla'];
        ?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT nome 
                    FROM `clientes_contatos` 
                    WHERE `id_cliente_contato` = '".$campos[$i]['id_cliente_contato']."' LIMIT 1 ";
            $campos_contato = bancos::sql($sql);
            echo $campos_contato[0]['nome'];
        ?>
        </td>
        <td>
        <?
            //Se o Orçamento estiver congelado ou existir algum Item que está em Queima de Estoque, travo o Cabeçalho ...
            $vetor_dados_gerais     = vendas::dados_gerais_orcamento($campos[$i]['id_orcamento_venda']);
            echo data::datetodata($vetor_dados_gerais['data_validade_orc'], '/');
        ?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['prazo_d'] > 0) $prazo_faturamento = '/'.$campos[$i]['prazo_d'];
            if($campos[$i]['prazo_c'] > 0) $prazo_faturamento= '/'.$campos[$i]['prazo_c'].$prazo_faturamento;
            if($campos[$i]['prazo_b'] > 0) {
                $prazo_faturamento = $campos[$i]['prazo_a'].'/'.$campos[$i]['prazo_b'].$prazo_faturamento;
            }else {
                if($campos[$i]['prazo_a'] == 0) {
                    $prazo_faturamento = 'À vista';
                }else {
                    $prazo_faturamento = $campos[$i]['prazo_a'];
                }
            }
            if($campos[$i]['nota_sgd'] == 'N') {
                    echo 'NF / '.$prazo_faturamento;
            }else if($campos[$i]['nota_sgd'] == 'S') {
                    echo 'SGD / '.$prazo_faturamento;
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
        <td align='left'>
        <?
            //Busco o último Follow-UP que foi registrado p/ este Orçamento de Venda do Loop ...
            $sql = "SELECT `observacao` 
                    FROM `follow_ups` 
                    WHERE `identificacao` = '".$campos[$i]['id_orcamento_venda']."' 
                    AND `origem` = '1' 
                    ORDER BY `id_follow_up` DESC LIMIT 1 ";
            $campos_follow_up = bancos::sql($sql);
            echo $campos_follow_up[0]['observacao'];
        ?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_orc'], 2, ',', '.');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
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
<title>.:: Consultar Orçamento(s) p/ Alterar / Imprimir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function controlar_checkboxs() {
    if(document.form.cmb_negociacao_finalizada.value != '') {//Combo "Negociação Finalizada" selecionada ...
        document.form.chkt_orc_nao_congelados.disabled      = true//Desabilita esse Checkbox ...
        document.form.chkt_orc_com_itens_em_aberto.checked  = true//Marca esse Checkbox ...
        document.form.chkt_data_emissao_30dias.checked      = true//Marca esse Checkbox ...
    }else {
        document.form.chkt_orc_nao_congelados.disabled = false
    }
}

function nao_desmarcar_checkbox(objeto) {
    if(document.form.cmb_negociacao_finalizada.value != '') {//Combo "Negociação Finalizada" selecionada ...
        if(objeto.checked == false) objeto.checked = true//Nunca esse Checkbox poderá ser desmarcado se tivermos alguma opção na Combo selecionada ...
    }
}
</Script>
</head>
<body onload='document.form.txt_cliente.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Orçamento(s) p/ Alterar / Imprimir
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número do Orçamento
        </td>
        <td>
            <input type='text' name='txt_numero_orcamento' title='Digite o Número do Orçamento' class='caixadetexto'>
            &nbsp;
            <font color='darkblue'>
                <b>*** (Esse é o único campo que quando preenchido, ignora o que foi preenchido nos Outros campos)</b>
            </font>
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
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' class='caixadetexto'>
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
                        WHERE `ativo` = '1' ORDER BY `nome_fantasia` ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;-
            <font color='red'>
                <b>Negociação(ões) Finalizada(s): </b>
            </font>
            &nbsp;
            <select name='cmb_negociacao_finalizada' title='Selecione um Tipo de Negociação finalizada' onclick='controlar_checkboxs()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='S'>SIM</option>
                <option value='N'>NÃO</option>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name='txt_observacao' title='Digite a Observação' class='caixadetexto'>
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
            <input type='checkbox' name='chkt_orc_nao_congelados' value='1' title='Só Orçamento(s) não Congelado(s)' id='label1' class='checkbox'>
            <label for='label1'>Só Orçamento(s) não Congelado(s)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_orc_com_itens_em_aberto' value='1' title='Só Orçamento(s) c/ Item(ns) em Aberto' onclick='nao_desmarcar_checkbox(this)' id='label2' class='checkbox'>
            <label for='label2'>Só Orçamento(s) c/ Item(ns) em Aberto</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_data_emissao_30dias' value='1' title='Data de Emissão <= 30 dias' onclick='nao_desmarcar_checkbox(this)' id='label3' class='checkbox' checked>
            <label for='label3'>Data de Emissão <= 30 dias</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_orcamentos_exportacao' value='1' title='Somente Orçamento(s) de Exportação' id='label4' class='checkbox'>
            <label for='label4'>Somente Orçamento(s) de Exportação</label>
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
<pre>
<b><font color='red'>Observação:</font></b>
    <pre>
    * Só exibe para exclusão os P.A(s) que estejam com os dados cadastrados corretamente.
    </pre>
</pre>
<?}?>