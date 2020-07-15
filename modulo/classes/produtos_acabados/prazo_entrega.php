<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

//Procedimento quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produto_acabado = $_POST['id_produto_acabado'];
    $operacao_custo     = $_POST['operacao_custo'];
}else {
    $id_produto_acabado = $_GET['id_produto_acabado'];
    $operacao_custo     = $_GET['operacao_custo'];
}

/*Esse trecho de tela foi feito em um arquivo à parte, p/ evitar de recarregar toda a tela do 
Estoque Acabado que daí seria muito lento, achamos mais fácil e mais rápido recarregar apenas
o Iframe que é exatamente esse arquivo na hora em que o usuário altera o Prazo de Entrega ...*/
$data_atual_menos_sete = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');

/***********************************************************************************************************/
/********************Verificação Principal antes de entrar em qq uma das situações abaixo*******************/
/***********************************************************************************************************/
if($operacao_custo == 0) {//Somente quando a Operação de Custo passa por parâmetro for Industrial ...
//Verifica se existe pelo menos uma OP que está em aberta e que possuem esse PA atrelado ...
    $sql = "SELECT `prazo_entrega`, `situacao`, `data_ocorrencia` 
            FROM `ops` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `status_finalizar` = '0' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_prazo = bancos::sql($sql);
    $linhas_prazo = count($campos_prazo);
    if($linhas_prazo == 0) {//Se não existir nenhuma OP atrelada a essa PA então ...
//Preciso buscar o Custo Industrial desse PA que foi passado por parâmetro ...
        $sql = "SELECT id_produto_acabado_custo 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                AND `operacao_custo` = '0' LIMIT 1 ";
        $campos_pa_custo = bancos::sql($sql);
//Se não existe Pzo Entrega, então verifico qual é o PA pai do PA filho q foi passado por parâmetro na 7ª Etapa ...
        $sql = "SELECT pa.`id_produto_acabado`, pa.`operacao_custo` 
                FROM `pacs_vs_pas` pp 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                WHERE pp.`id_produto_acabado_custo` = '".$campos_pa_custo[0]['id_produto_acabado_custo']."' ORDER BY pp.`id_pac_pa` LIMIT 1 ";
        $campos_pa_pai = bancos::sql($sql);
        if(count($campos_pa_pai) == 1) {//Se encontrar algum PA ...
//Nesse instante o "id_produto_acabado" e a "OC" passam a assumir do Pai, e não mais do PA passado por parâmetro ...
            $id_produto_acabado = $campos_pa_pai[0]['id_produto_acabado'];
            $operacao_custo 	= $campos_pa_pai[0]['operacao_custo'];
        }
    }
}
/***********************************************************************************************************/
?>
<html>
<head>
<title>.:: Prazo de Entrega ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>
<table border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
<?
//Quando a O.C. for Industrial, vai estar printando toda a parte de Prazos de Entrega das OP(s) ...
if($operacao_custo == 0) {
//Faço uma verificação de Toda(s) as OP(s) que estão em abertas e que possuem esse PA atrelado ...
    $sql = "SELECT `id_op`, `prazo_entrega`, `situacao`, `data_ocorrencia` 
            FROM `ops` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `status_finalizar` = '0' 
            AND `ativo` = '1' ";
    $campos_prazo = bancos::sql($sql);
    $linhas_prazo = count($campos_prazo);
    if($linhas_prazo == 0) {//Se não existir nenhuma OP atrelada a essa PA então ...
?>
        <td title='Visualizar Estoque' alt='Visualizar Estoque' style='cursor:help' align='center'>
            <a href="javascript:nova_janela('../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>&nivel_reduzido=1', 'POP', '', '', '', '', 500, 960, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
                <font color='red' size='-2'>
                    <b>S/ OP(s)</b>
                </font>
            </a>
        </td>
		
<?	
    }else {
?>
        <td style='cursor:help' align='center'>
<?
        //Variáveis p/ fazer controle com os Prazos das OPs ...
        $itens_atualizados      = 0;
        $itens_nao_atualizados  = 0;

        //Por esse trecho de código ser carregado em um Iframe dentro de um Loop da Tela principal, por isso que eu trabalho com o índice sendo $j ...
        for($j = 0; $j < $linhas_prazo; $j++) {
            /*Se esse Prazo de Entrega da OP foi atualizado recentemente, quer dizer em até 7 dias abaixo da data atual "HOJE", 
            ele printa a cor do link em verde*/
            if(substr($campos_prazo[$j]['data_ocorrencia'], 0, 10) >= $data_atual_menos_sete) {
                $itens_atualizados++;
            }else {
                $itens_nao_atualizados++;
            }
        }
        
        $compra             = estoque_acabado::compra_producao($id_produto_acabado);
        $estoque_produto    = estoque_acabado::qtde_estoque($id_produto_acabado, 0);
        $producao           = $estoque_produto[2];
        
        if(($compra == 0 && $producao == 0) || ($itens_atualizados == 0 && $itens_nao_atualizados > 0)) {
            //Não tem Compra nem Produção ...
            if($compra == 0 && $producao == 0) $mensagem = 'S/ Prazo';
            
            //Só existem itens em azul ...
            if($itens_atualizados == 0 && $itens_nao_atualizados > 0) $mensagem = 'Atualizar Prazo';
?>
            <a href="javascript:nova_janela('../../classes/estoque/alterar_prazo_entrega_industrial.php?id_produto_acabado=<?=$id_produto_acabado;?>&operacao_custo=<?=$operacao_custo;?>&atualizar_iframe=1', 'FATURADO', '', '', '', '', 500, 960, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <font color='red' size='-2'>
                    <?=$mensagem;?>
                </font>
            </a>
<?
        }else {//Pode ter verdes e azuis, mas os azuis estou ignorando ...
            for($j = 0; $j < $linhas_prazo; $j++) {
                $vetor_dados_op = intermodular::dados_op($campos_prazo[$j]['id_op']);
                
                /*Se esse Prazo de Entrega da OP foi atualizado recentemente, quer dizer em até 7 dias abaixo da data atual "HOJE", 
                ele printa a cor do link em verde*/
                if(substr($campos_prazo[$j]['data_ocorrencia'], 0, 10) >= $data_atual_menos_sete) {
                    $cor_link       = 'green';

                    $prazo_entrega      = strtok($campos_prazo[$j]['prazo_entrega'], '=');
                    $prazo_entrega      = trim($prazo_entrega);
                    $responsavel        = strtok($campos_prazo[$j]['prazo_entrega'], '|');
                    $responsavel        = substr(strchr($responsavel, '> '), 1, strlen($responsavel));

                    if(strlen($campos_prazo[$j]['prazo_entrega']) > 10) {
                        $data_hora  = strchr($campos_prazo[$j]['prazo_entrega'], '|');
                        $data_hora  = substr($data_hora, 2, strlen($data_hora));
                        $data       = data::datetodata(substr($data_hora, 0, 10), '/');
                        $hora       = substr($data_hora, 11, 8);
                        /*Se estiver gravado apenas a Data no Prazo de Entrega do P.A., então só trago ele do jeito que é trato 
                        ele normal como data para que não venha ter nenhum erro e aconteça nenhum problema ...*/
                    }else {
                        $data       = $prazo_entrega;
                    }
                
/**********************************************/
                    $hora = substr($data_hora, 11, 8);
//Faz esse tratamento para o caso de não encontrar o responsável ...
                    if(empty($responsavel)) {
                        $string_apresentar = 'Alterar Prazo de Entrega';
                    }else {
                        $string_apresentar = 'Responsável: '.$responsavel.' - '.$data.' '.$hora;
                    }
?>
<!--Disponibilizo um link p/ o usuário, caso este queira mudar os prazos-->
            <a href="javascript:nova_janela('../../classes/estoque/alterar_prazo_entrega_industrial.php?id_produto_acabado=<?=$id_produto_acabado;?>&operacao_custo=<?=$operacao_custo;?>&atualizar_iframe=1', 'FATURADO', '', '', '', '', 500, 960, 'c', 'c', '', '', 's', 's', '', '', '')" title='<?=$string_apresentar;?>' alt='<?=$string_apresentar;?>' class='link'>
                <font color='<?=$cor_link;?>' size='-2'>
<!--//Listagem de Todos os Prazos de Entrega ...-->
                <?
                    echo number_format($vetor_dados_op['qtde_saldo'], 0, ',', '.').'-';
                    echo data::datetodata($campos_prazo[$j]['prazo_entrega'], '/');
                    echo '(OP '.$campos_prazo[$j]['id_op'];
                    if(!empty($campos_prazo[$j]['situacao'])) echo '-'.$campos_prazo[$j]['situacao'];
                    echo ')<br/>';
                ?>
                </font>
            </a>
<?
                }
            }
        }
?>
        </td>
<?
    }
/*****************************************Revenda*************************************************/
//Quando a O.C. for Revenda, vai estar seguindo toda a Rotina normalmente, Prazo de Entrega manual ...
}else {
    $sql = "SELECT `prazo_entrega` 
            FROM `estoques_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos_prazo = bancos::sql($sql);
    if(count($campos_prazo) == 0) {
        $prazo_entrega  = 'S/ Inexistente';
        $responsavel    = '';
        $data           = '';
        $hora           = '';
    }else {
        $prazo_entrega  = strtok($campos_prazo[0]['prazo_entrega'], '=');
        $prazo_entrega  = trim($prazo_entrega);
        $responsavel    = strtok($campos_prazo[0]['prazo_entrega'], '|');
        $responsavel    = substr(strchr($responsavel, '> '), 1, strlen($responsavel));

        if(strlen($campos_prazo[0]['prazo_entrega']) > 10) {
            $data_hora  = strchr($campos_prazo[0]['prazo_entrega'], '|');
            $data_hora  = substr($data_hora, 2, strlen($data_hora));
            $data       = data::datetodata(substr($data_hora, 0, 10), '/');
            $hora       = substr($data_hora, 11, 8);
/*Se estiver gravado apenas a Data no Prazo de Entrega do P.A., então só trago ele do jeito que é trato 
ele normal como data para que não venha ter nenhum erro e aconteça nenhum problema ...*/
        }else {
            $data       = $prazo_entrega;
        }
    }
/**********Controles para cor do link**********/
    if(empty($prazo_entrega)) {
        $prazo_entrega  = 'S/ Prazo';
        $cor_link       = 'red';
    }else {
/*Se esse prazo de entrega foi atualizado recentemente, quer dizer em até 7 dias abaixo da data atual 
de hoje, ele printa a cor do link em verde*/
        $cor_link       = (data::datatodate($data, '-') >= $data_atual_menos_sete) ? 'green' : '';
    }
/**********************************************/
    $hora = substr($data_hora, 11, 8);
?>
<!--Disponibilizo um link p/ o usuário, caso este queira mudar os prazos-->
        <td style='cursor:help' align='center'>
            <a href="javascript:nova_janela('../../classes/estoque/alterar_prazo_entrega_revenda.php?id_produto_acabado=<?=$id_produto_acabado;?>&operacao_custo=<?=$operacao_custo;?>&atualizar_iframe=1', 'FATURADO', '', '', '', '', 500, 960, 'c', 'c', '', '', 's', 's', '', '', '')" title='<?=$string_apresentar;?>' alt='<?=$string_apresentar;?>' class='link'>
                <font color='<?=$cor_link;?>' size='-2'>
                    <?=$prazo_entrega;?>
                </font>
            </a>
        </td>
<?
}
?>
    </tr>
</table>
</form>
</body>
</html>