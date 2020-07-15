<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/custos.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../../');

if(!empty($_POST['chkt_tudo_produto_acabado'])) {
//Vari�veis para controle do retorno de mensagem
    $pa_excluidos       = 0;
    $pa_nao_excluidos   = 0;
    foreach ($_POST['chkt_tudo_produto_acabado'] as $id_produto_acabado) {
        //Verifico quais PA�s que possuem esse PA que estou tentando excluir atrelado em sua 7� Etapa ...
        $sql = "SELECT pa.`discriminacao` AS produtos_q_utilizam 
                FROM `pacs_vs_pas` pp 
                INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = pp.id_produto_acabado_custo 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`ativo` = '1' 
                WHERE pp.`id_produto_acabado` = '$id_produto_acabado' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        /*Encontrou algum PA q est� utilizando o PA corrente em q o usu�rio est� tentando excluir, sendo assim n�o 
        posso excluir esse PA*/
        if($linhas > 0) {
            //Busca a discrimina��o do PA q est� sendo exclu�do pelo usu�rio ...
            $sql = "SELECT discriminacao AS produto_excluir 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_excluir         = bancos::sql($sql);
            $produto_excluir        = $campos_excluir[0]['produto_excluir'];
            $produtos_q_utilizam    = '';
            for($i = 0; $i < $linhas; $i++) {
                //Aqui lista todos todos os PA(s) q utilizam o PA q o usu�rio est� tentando excluir
                $produtos_q_utilizam.= '=> '.$campos[$i]['produtos_q_utilizam'].';\n';
            }
            $produtos_q_utilizam = substr($produtos_q_utilizam, 0, strlen($produtos_q_utilizam) - 2);
            $alert.= '* O PA "'.$produto_excluir.'" N�O PODE SER EXCLU�DO ! \n\nPOIS ELE EST� SENDO UTILIZADO NA 7� ETAPA PELO(S) PRODUTO(S): \n'.$produtos_q_utilizam.'\n\n\n\n\n';
            $pa_nao_excluidos++;
/*Esse PA n�o est� sendo utilizado por nenhum produto em 7� etapa, sendo assim eu posso excluir*/
        }else {
            //Em primeiro lugar eu busco quem � o PI desse PA
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            if(count($campos_pipa) == 1) {
                $id_produto_insumo = $campos_pipa[0]['id_produto_insumo'];
                /*Parte do PI: 
                O sistema ter� que executar essa parte para nao ficar mostrando na lista de pre�o. Mas esta parte � s� para PA que � PIPA*/
                $sql = "UPDATE `produtos_insumos` SET `ativo` = 0 WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                $campos =  bancos::sql($sql);
                //Aqui eu desativo o PI da Lista de Pre�o, independente do Fornecedor ...
                $sql = "SELECT id_fornecedor_prod_insumo 
                        FROM `fornecedores_x_prod_insumos` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo' ";
                $campos_lista = bancos::sql($sql);
                if(count($campos_lista) > 0) {
                    for($i = 0; $i < count($campos_lista); $i++) {
                        $sql = "UPDATE `fornecedores_x_prod_insumos` SET `ativo` = '0' WHERE `id_fornecedor_prod_insumo` = '".$campos_lista[$i]['id_fornecedor_prod_insumo']."' LIMIT 1 ";
                        bancos::sql($sql);
                    }
                }
//Aki estou desatrelando o PA do PI
                $sql = "UPDATE `produtos_acabados` SET `id_produto_insumo` = NULL WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                bancos::sql($sql);
            }
//Deletando o PA ...
            $sql = "UPDATE `produtos_acabados` SET `ativo`='0' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            bancos::sql($sql);
            genericas::atualizar_pas_no_site_portal($id_produto_acabado);
            $pa_excluidos++;
        }
    }
    if($pa_excluidos != 0 && $pa_nao_excluidos == 0) $valor = 2;//Significa que todos os PA(s) foram exclu�dos com sucesso
    if($pa_excluidos != 0 && $pa_nao_excluidos != 0) $valor = 3;//Significa que alguns PA foram exclu�dos e outros n�o
    if($pa_excluidos == 0 && $pa_nao_excluidos != 0) $valor = 4;//Significa que nenhum PA pode ser exclu�do
?>
    <Script Language = 'JavaScript'>
<?
//Significa q alguns PA(s) n�o podem ser exclu�do, ent�o d� uma alerta
    if(!empty($alert)) {
?>
        alert('<?=$alert;?>')
<?
    }
?>
        window.location = 'excluir.php?valor=<?=$valor;?>'
    </Script>
<?
}

/*Esse par�metro de n�vel vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisi��o desse arquivo Filtro*/
$nivel_arquivo_principal = '../../../..';
//Aqui eu vou puxar a Tela �nica de Filtro de Produtos Acabados que serve para o Sistema Todo ...
require('../../../classes/produtos_acabados/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
if($linhas > 0) {
/******************************************************************************/
/****************Procedimento para Desatrelar Fornecedor Padr�o****************/
/******************************************************************************/
    if(!empty($_GET['id_produto_insumo'])) {
        $sql = "UPDATE `produtos_insumos` SET `id_fornecedor_default` = NULL WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
        bancos::sql($sql);
?>
        <Script Language = 'JavaScript'>
            alert('FORNECEDOR PADR�O DESATRELADO COM SUCESSO !')
        </Script>
<?
    }
/******************************************************************************/
?>
<html>
<head>
<title>.:: Excluir Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function desatrelar_fornecedor_padrao(id_produto_insumo) {
    var resposta = confirm('DESEJA DESATRELAR ESSE FORNECEDOR PADR�O DESSE PRODUTO ?')
    if(resposta == true) window.location = '<?=$PHP_SELF.$parametro;?>&id_produto_insumo='+id_produto_insumo
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OP��O !')">
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Excluir Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <?=genericas::order_by('pa.discriminacao', 'Produto', '', $order_by, '../../../../');?>
        </td>
        <td>
            <font title='Opera��o de Custo' style='cursor:help'>
                O.C.
            </font>
        </td>
        <td>
            Impedimentos
        </td>
        <td>
            <input type='hidden' name='chkt_tudo'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']).'&nbsp;';
                /********************Links p/ abrir o Custo********************/
                if($campos[$i]['operacao_custo'] == 0) {//Industrial
            ?>
            <a href="javascript:nova_janela('../../custo/industrial/custo_industrial.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Custo Industrial' style='cursor:help' class='link'>
            <?
                }else {
            ?>
            <a href="javascript:nova_janela('../../custo/revenda/custo_revenda.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Custo Revenda' style='cursor:help' class='link'>
            <?
                }
                /**************************************************************/
            ?>
                <img src = '../../../../imagem/menu/alterar.png' title="Visualizar Custo" alt='Visualizar Custo' border='0'>
            </a>
            &nbsp;
        </td>
        <td align='center'>
        <?
            if($campos[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Opera��o de Custo for Industrial, ent�o eu apresento a Sub-Opera��o de Custo do PA ...
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
        <td>
        <?
            //Verifico se o PA est� vinculado a algum Or�amento ...
            $sql = "SELECT `id_orcamento_venda` 
                    FROM `orcamentos_vendas_itens` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ";
            $campos_orcamento = bancos::sql($sql);
            $linhas_orcamento = count($campos_orcamento);
            if($linhas_orcamento > 0) {
                for($j = 0; $j < $linhas_orcamento; $j++) $id_orcamentos_vendas.= $campos_orcamento[$j]['id_orcamento_venda'].', ';
                $id_orcamentos_vendas = substr($id_orcamentos_vendas, 0, strlen($id_orcamentos_vendas) - 2);
        ?>
                <p/><b>* Esse produto est� atrelado ao(s) Or�amento(s) N.� -> <?=$id_orcamentos_vendas;?></b>
                <font color='red'>
                    <b>(ESTA CLA�SULA N�O IMPEDE MAIS A EXCLUS�O DO PA)</b>
                </font>
        <?
            }
            
            $estoque_produto    = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 1);
            $qtde_estoque_real  = $estoque_produto[0];
            $qtde_producao      = $estoque_produto[2];

            if($qtde_estoque_real > 0) {//Possui Estoque Real ...
        ?>
            <font color='red'>
                <p/><b>* Esse produto tem ER: <?=number_format($qtde_estoque_real, 2, ',', '.');?></b>
            </font>
        <?
            }

            //Na realidade essa fun��o s� me traz a Compra, n�o tem nada de Produ��o apesar do nome ... rsrs
            $qtde_compra = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
            
            //Observa��o: Se Produ��o > 0, representa que temos OPs ...
            if(($qtde_compra + $qtde_producao) > 0) {
?>
            <font color='red'>
                <p/>
                <b>
                    * Esse produto tem Compra = <?=number_format($qtde_compra, 2, ',', '.');?>
                    e Produ��o = <?=number_format($qtde_producao, 2, ',', '.');?>
                
                </b>
            </font>
<?
            }
            
            //Verifico quais PA�s que possuem esse PA que estou tentando excluir atrelado em sua 7� Etapa ...
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pac.`operacao_custo` 
                    FROM `pacs_vs_pas` pp 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.id_produto_acabado_custo = pp.id_produto_acabado_custo 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`ativo` = '1' 
                    WHERE pp.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ";
            $campos_etapa7_q_possuem_este_pa = bancos::sql($sql);
            $linhas_etapa7_q_possuem_este_pa = count($campos_etapa7_q_possuem_este_pa);
            if($linhas_etapa7_q_possuem_este_pa > 0) {
                for($j = 0; $j < $linhas_etapa7_q_possuem_este_pa; $j++) {
                    /********************Links p/ abrir o Custo********************/
                    if($campos_etapa7_q_possuem_este_pa[$j]['operacao_custo'] == 0) {//Nesse caso a OC do Custo que tem Prioridade sobre a OC do PA ...
                        $caminho = "<a href=\"javascript:nova_janela('../../custo/industrial/custo_industrial.php?id_produto_acabado=".$campos_etapa7_q_possuem_este_pa[$j]['id_produto_acabado']."&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')\" title='Visualizar Custo Industrial' style='cursor:help' class='link'>";
                    }else {
                        $caminho = "<a href=\"javascript:nova_janela('../../custo/revenda/custo_revenda.php?id_produto_acabado=".$campos_etapa7_q_possuem_este_pa[$j]['id_produto_acabado']."&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')\" title='Visualizar Custo Revenda' style='cursor:help' class='link'>";
                    }
                    /**************************************************************/
                    if(($j + 1) == $linhas_etapa7_q_possuem_este_pa) {//Significa que estou no �ltimo Registro ...
                        $produtos_q_possuem_este_pa.= $caminho.$campos_etapa7_q_possuem_este_pa[$j]['referencia'].'</a>';
                    }else {
                        $produtos_q_possuem_este_pa.= $caminho.$campos_etapa7_q_possuem_este_pa[$j]['referencia'].', </a>';
                    }
                }
                $produtos_q_possuem_este_pa = substr($produtos_q_possuem_este_pa, 0, strlen($produtos_q_possuem_este_pa) - 2);
        ?>
                <p/><b>* Esse produto est� na 7� Etapa do(s) seguinte(s) PA(s)</b>
                <font color='red'>
                    <b><?=$produtos_q_possuem_este_pa;?></b>
                </font>
        <?
                
            }
            
            //Verifico qual � o Custo desse PA que estou tentando excluir ...
            $sql = "SELECT `id_produto_acabado_custo` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `operacao_custo` = '".$campos[$i]['operacao_custo']."' LIMIT 1 ";
            $campos_custo               = bancos::sql($sql);
            $id_produto_acabado_custo   = $campos_custo[0]['id_produto_acabado_custo'];
            
            //Verifico quais PA�s est�o na 7� Etapa desse PA que estou tentando excluir ...
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pac.`operacao_custo` 
                    FROM `pacs_vs_pas` pp 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado` = pa.`id_produto_acabado` 
                    WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
            $campos_etapa7_q_este_pa_possui = bancos::sql($sql);
            $linhas_etapa7_q_este_pa_possui = count($campos_etapa7_q_este_pa_possui);
            if($linhas_etapa7_q_este_pa_possui > 0) {
                for($j = 0; $j < $linhas_etapa7_q_este_pa_possui; $j++) {
                    /********************Links p/ abrir o Custo********************/
                    if($campos_etapa7_q_este_pa_possui[$j]['operacao_custo'] == 0) {//Nesse caso a OC do Custo que tem Prioridade sobre a OC do PA ...
                        $caminho = "<a href=\"javascript:nova_janela('../../custo/industrial/custo_industrial.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')\" title='Visualizar Custo Industrial' style='cursor:help' class='link'>";
                    }else {
                        $caminho = "<a href=\"javascript:nova_janela('../../custo/revenda/custo_revenda.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')\" title='Visualizar Custo Revenda' style='cursor:help' class='link'>";
                    }
                    /**************************************************************/
                    if(($j + 1) == $linhas_etapa7_q_este_pa_possui) {//Significa que estou no �ltimo Registro ...
                        $produtos_q_este_pa_possui.= $caminho.$campos_etapa7_q_este_pa_possui[$j]['referencia'].'</a>';
                    }else {
                        $produtos_q_este_pa_possui.= $caminho.$campos_etapa7_q_este_pa_possui[$j]['referencia'].', </a>';
                    }
                }
                $produtos_q_este_pa_possui = substr($produtos_q_este_pa_possui, 0, strlen($produtos_q_este_pa_possui) - 2);
        ?>
            <p/><b>* Esse(s) produto(s) est�(�o) na 7� Etapa deste PA(s)</b>
                <font color='red'>
                    <b><?=$produtos_q_este_pa_possui;?></b>
                </font>
        <?
            }
            
            //Quando for revenda, tem q utilizar a produ��o inicial q deve ser de compra produ��o do estoque
            if($campos[$i]['operacao_custo'] == 1) {//Revenda
//Agora j� tem mais outra verifica��o (rsrs), vejo se o PA � PI
                $sql = "SELECT `id_produto_insumo` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        AND `id_produto_insumo` > '0' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos_pipa = bancos::sql($sql);
                if(count($campos_pipa) == 1) {
//Verifico se o PI tem algum Fornecedor Padr�o atrelado
                    $sql = "SELECT id_fornecedor_default 
                            FROM `produtos_insumos` 
                            WHERE `id_produto_insumo` = '".$campos_pipa[0]['id_produto_insumo']."' 
                            AND `id_fornecedor_default` > '0' 
                            AND `ativo` = '1' LIMIT 1 ";
                    $campos_default = bancos::sql($sql);
                    //Se tiver Fornecedor Padr�o, ent�o n�o posso excluir esse PA
                    if(count($campos_default) == 1) {//Existe ...
                        /*Tenho essa seguran�a aqui para desatrelarmos o Fornecedor Padr�o, porque em primeiro 
                        lugar o que deve e � necess�rio ser feito � resolvermos as outras quest�es ...*/
                        if(($qtde_estoque_real + $qtde_compra + $qtde_producao) == 0) {
        ?>
            <a href="javascript:desatrelar_fornecedor_padrao('<?=$campos_pipa[0]['id_produto_insumo'];?>')" title='Desatrelar Fornecedor Padr�o' class='link'>
                <font color='red'>
                    <p/><b>* Esse produto tem um Fornecedor Padr�o atrelado</b>
                </font>
            </a>
        <?
                        }
                        $existe_fornecedor_padrao = 'S';
                    }else {//N�o existe ...
                        $existe_fornecedor_padrao = 'N';
                    }
                }
            }else {//Quando Industrial, ent�o nunca existir� Fornec. Padr�o ...
                $existe_fornecedor_padrao = 'N';
            }
        ?>
        </td>
        <td align='center'>
        <?
            if(($qtde_estoque_real + $qtde_compra + $qtde_producao) == 0 && $existe_fornecedor_padrao == 'N' && $linhas_etapa7_q_possuem_este_pa == 0 && $linhas_etapa7_q_este_pa_possui == 0) {//Posso excluir esse PA
        ?>
                <input type='checkbox' name='chkt_tudo_produto_acabado[]' value="<?=$campos[$i]['id_produto_acabado'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        <?
            }else {
        ?>
                <input type='hidden' name='chkt_tudo_produto_acabado[]'>
        <?
            }
        ?>
        </td>
    </tr>
<?
            //Deleto essas vari�veis p/ n�o herdar(em) Valor(es) do Loop(s) Anterior(es) ...
            unset($id_orcamentos_vendas);
            unset($produtos_q_possuem_este_pa);
            unset($produtos_q_este_pa_possui);
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
?>