<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

/*************Atualização dos Cortes das Matérias Primas da Segunda Etapa do Custo*************/
if(!empty($_POST['hdd_produto_acabado'])) {
    foreach($_POST['hdd_produto_acabado'] as $i => $id_produto_acabado) {
        //Se esse Checkbox estiver marcado, então Libero o Custo do Produto Acabado ...
        if(in_array($id_produto_acabado, $_POST['chkt_custo_liberado'])) {
            $sql = "UPDATE `produtos_acabados` SET `status_custo` = '1' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        }else {
            $sql = "UPDATE `produtos_acabados` SET `status_custo` = '0' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        }
        bancos::sql($sql);
    }
/*************Atualização dos Cortes das Matérias Primas da Segunda Etapa do Custo*************/
    foreach($_POST['hdd_produto_acabado_custo'] as $i => $id_produto_acabado_custo) {
        //Só irá atualizar se tiver Valor de Corte preenchido ...
        if($_POST['txt_corte'][$i] > 0) {
            $sql = "UPDATE `produtos_acabados_custos` SET `comprimento_2` = '".$_POST['txt_corte'][$i]."' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
/**********************************************************************************************/
?>
    <Script Language = 'JavaScript'>
        window.location = 'custo_unificado.php<?=$parametro;?>&valor=1'
    </Script>
<?
}else {
    /*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
    requisição desse arquivo Filtro*/
    $nivel_arquivo_principal    = '../../..';
    $url_remetente              = 'CUSTO';

    //Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../classes/produtos_acabados/tela_geral_filtro.php');
    //Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Custo Unificado ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function copiar_corte_geral() {
    if(document.form.txt_corte_geral.value == '') {
        alert('DIGITE O CORTE PARA COPIAR PARA O(S) OUTRO(S) PRODUTO(S) INSUMO(S) !')
        document.form.txt_corte_geral.focus()
        return false
    }
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_produto_acabado_custo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado_custo[]'].length)
    }
    for(var i = 0; i < linhas; i++) document.getElementById('txt_corte'+i).value = document.form.txt_corte_geral.value
}

function validar() {
    var preenchido  = 0
    var elementos   = document.form.elements
    if(typeof(elementos['hdd_produto_acabado_custo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_produto_acabado_custo[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        //Aqui eu verifico se foi preenchido algum "Custo p/ ser Liberado" ou "Corte" de algum Item ...
        if(document.getElementById('chkt_custo_liberado'+i).value != '' || document.getElementById('txt_corte'+i).value != '') {
            preenchido++
            break;//Para sair fora do Loop ...
        }
    }
    if(preenchido == 0) {
        alert('PREENCHA O(S) CAMPO(S) COM ALGUM CORTE PARA SALVAR !')
        document.getElementById('txt_corte0').focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_corte_geral.focus()'>
<form name='form' action='' method='post' onsubmit='return validar()'>
<table width='150%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='28'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='28'>
            Custo Unificado - Consultar Produtos Acabados
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Grupo P.A. (Empresa Divisão)'>
                Grupo P.A. (E.D.)
            </font>
        </td>
        <td>
            Produto
        </td>
        <td>
            Custo Liberado
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' value='S' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <font title='Operação de Custo' style='cursor:help'>
                O. C.
            </font>
        </td>
        <td>
            Últ. Orc Irregular p/ DT
        </td>
        <td>
            Fornecedor Default
        </td>
        <td>
            Preço Fat. <br/>Nac. Min. R$
        </td>
        <td>
            Preço Fat. <br/>Inter. Min. R$
        </td>
        <td>
            <font title='Data de Inclusão'>
                Data Inc
            </font>
        </td>
        <td>
            Última Alteração
        </td>
        <td>
            <font title='Quantidade em Estoque' style='cursor:help'>
                Qtde Est
            </font>
        </td>
        <td>
            <font title='Quantidade em Produção' style='cursor:help'>
                Qtde Prod
            </font>
        </td>
        <td>
            Origem - ST
        </td>
        <td>
            <font title='Operação (Fat)'>
                O. F.
            </font>
        </td>
        <td>
            <font title='Peso Unitário' style='cursor:help'>
                P. U.
            </font>
        </td>
        <td>
            <font title='Média Mensal de Vendas' style='cursor:help'>
                M.M.V.
            </font>
        </td>
        <td>
            <font title='Quantidade do Lote' style='cursor:help'>
                Qtde Lote
            </font>
        </td>
        <td>
            1&ordf; Etapa
        </td>
        <td colspan='4'>
            2&ordf; Etapa
        </td>
        <td>
            3&ordf; Etapa
        </td>
        <td>
            4&ordf; Etapa
        </td>
        <td>
            5&ordf; Etapa
        </td>
        <td>
            6&ordf; Etapa
        </td>
        <td>
            7&ordf; Etapa
        </td>
        <td>
            Observação
        </td>
    </tr>
    <tr align='center'>
        <td colspan='17' class='linhacabecalho'>
            &nbsp;
        </td>
        <td class='linhacabecalho'>
            Qtde - Embalagem
        </td>
        <td class='linhacabecalho'>
            <label title='Peças Cortes'>
                P.C.
            </label>
        </td>
        <td class='linhacabecalho'>
            <label title='Comprimento'>
                Comp.
            </label>
        </td>
        <td class='linhacabecalho'>
            Corte
            &nbsp;
            <input type='text' name='txt_corte_geral' maxlength='6' size='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
            <img src = '../../../imagem/seta_abaixo.gif' border='0' title='Copiar Geral' alt='Copiar Geral' onclick='copiar_corte_geral()'>
        </td>
        <td class='linhacabecalho'>
            Discriminação
        </td>
        <td class='linhacabecalho'>
            Qtde - Discriminação
        </td>
        <td class='linhacabecalho'>
            Tempo - Máquina
        </td>
        <td class='linhacabecalho'>
            Fator T.T. - Peso - Discriminação
        </td>
        <td class='linhacabecalho'>
            Qtde - Discriminação
        </td>
        <td class='linhacabecalho'>
            Qtde - Discriminação
        </td>
        <td class='linhacabecalho'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $dados_produto = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado']);
            
            $url = "javascript:nova_janela('abrir_custo.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&pop_up=1', 'CUSTO', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="<?=$url;?>">
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
        <td onclick="<?=$url;?>">
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td align='center'>
            <?
                //Se o Custo desse PA estiver Liberado, então eu já apresento marcado este checkbox do Loop ...
                $checked = ($campos[$i]['status_custo'] == 1) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_custo_liberado[]' id='chkt_custo_liberado<?=$i;?>' value='<?=$campos[$i]['id_produto_acabado'];?>' title='Custo Liberado' class='checkbox' <?=$checked;?>>
            
            <!--******************************************************************-->
            
            
        </td>
        <td onclick="<?=$url;?>" align='center'>
        <?
            if($campos[$i]['operacao_custo'] == 0) {
                echo '<font title="Industrial" style="cursor:help">I</font>';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[$i]['operacao_custo_sub'] == 0) {
                    echo '-<font title="Industrial" style="cursor:help">I</font>';
                }else if($campos[$i]['operacao_custo_sub'] == 1) {
                    echo '-<font title="Revenda" style="cursor:help">R</font>';
                }else {
                    echo '-';
                }
            }else if($campos[$i]['operacao_custo'] == 1) {
                echo '<font title="Revenda" style="cursor:help">R</font>';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td class='linhanormalescura' align='center'>
        <?
            if($campos[$i]['referencia'] == 'ESP') {
                $tres_meses_atras = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -90), '-');

                //Aqui eu verifico os Orc(s) dos Últimos 3 meses em aberto que contém esse PA ...
                $sql = "SELECT DATE_FORMAT(ov.`data_emissao`, '%d/%m/%Y') AS data_emissao, ovi.`id_orcamento_venda`, l.`login` 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`status` < '2' AND ov.`data_emissao` >= '$tres_meses_atras' 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = ov.`id_funcionario` 
                        INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                        WHERE ovi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        AND (ovi.`prazo_entrega_tecnico` = '0.0' OR ovi.`preco_liq_fat_disc` = 'DEPTO TÉCNICO') ORDER BY ov.`id_orcamento_venda` DESC LIMIT 1 ";
                $campos_orcs = bancos::sql($sql);
                $linhas_orcs = count($campos_orcs);
                if($linhas_orcs == 0) {
                    echo '-';
                }else {
                    echo $campos_orcs[0]['data_emissao'].' | '.$campos_orcs[0]['id_orcamento_venda'].'<font color="blue"> ('.$campos_orcs[0]['login'].')</font><br> ';
                }
            }else {
                echo '-';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['operacao_custo'] == 1) {//Somente quando for Revenda ...
                //Busco o Fornecedor Default do PA do Loop ...
                $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($campos[$i]['id_produto_acabado'], '', 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda

                $sql = "SELECT `razaosocial` 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$id_fornecedor_setado' LIMIT 1 ";
                $campos_fornecedor  = bancos::sql($sql);
                echo $campos_fornecedor[0]['razaosocial'];
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['operacao_custo'] == 0) {//Industrial, trago o Preço de Outra forma ...
                //Essa já prepara as variáveis para o cálculo das etapas do custo
                $total_indust = custos::todas_etapas($campos[$i]['id_produto_acabado'], 0);
                echo number_format($total_indust, 2, ',', '.');
            }else {//Revenda ...
                $valores = custos::preco_custo_pa($campos[$i]['id_produto_acabado'], '', 'S');
                echo segurancas::number_format($valores['preco_venda_fat_nac_min_rs'], 2, '.');
            }
        ?>    
        </td>
        <td align='right'>
        <?
            /*if($campos[$i]['operacao_custo'] == 0) {//Industrial, trago o Preço de Outra forma ...
                $fator_desconto_maximo_vendas   = genericas::variavel(19);
                echo number_format($total_indust / $fator_desconto_maximo_vendas, 2, ',', '.');
            }else {*/

            //Se a OC = 'Revenda' e existe Preço de Venda Fat Inter Min R$ ...
            if($campos[$i]['operacao_custo'] == 1 && $valores['preco_venda_fat_inter_min_rs'] > 0) {
                echo segurancas::number_format($valores['preco_venda_fat_inter_min_rs'], 2, '.');
            }
        ?>
        </td>
        <td onclick="<?=$url;?>" align='center'>
        <?
            if(substr($campos[$i]['data_sys'], 0, 10) != '0000-00-00') echo data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/');
        ?>
        </td>
        <td onclick="<?=$url;?>" align='center'>
        <?
            //Busca do Login e da data de última alteração que foi realizada no Custo ...
            $sql = "SELECT l.`login`, 
                    CONCAT(DATE_FORMAT(SUBSTRING(pac.`data_sys`, 1, 10), '%d/%m/%Y'), SUBSTRING(pac.`data_sys`, 11, 9)) AS data_atualizacao 
                    FROM `produtos_acabados_custos` pac 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = pac.`id_funcionario` 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    WHERE pac.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND pac.`operacao_custo` = '".$campos[$i]['operacao_custo']."' LIMIT 1 ";
            $campos_custo = bancos::sql($sql);
            if(count($campos_custo) == 1) {
                echo $campos_custo[0]['login'].' às '.$campos_custo[0]['data_atualizacao'];
            }
        ?>
        </td>
        <?
//Aqui eu trago a qtde em Estoque e a qtde em Produção
            $sql = "SELECT `qtde`, `qtde_producao` 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_estoque_pa = bancos::sql($sql);
            if(count($campos_estoque_pa) == 1) {
                $estoque    = $campos_estoque_pa[0]['qtde'];
                $producao   = $campos_estoque_pa[0]['qtde_producao'];
            }else {
                $estoque    = 0;
                $producao   = 0;
            }
        ?>
        <td onclick="<?=$url;?>" align='center'>
            <?=number_format($estoque, 2, ',', '.');?>
        </td>
        <td onclick="<?=$url;?>" align='center'>
            <?=number_format($producao, 2, ',', '.');?>
        </td>
        <td onclick="<?=$url;?>" align='center'>
            <?=$campos[$i]['origem_mercadoria'].$dados_produto['situacao_tributaria'];?>
        </td>
        <td onclick="<?=$url;?>" align='center'>
        <?
            if($campos[$i]['operacao'] == 0) {
        ?>
                <p title="Industrialização (c/ IPI)">I - C</p>
        <?
            }else {
        ?>
                <p title="Revenda (s/ IPI)">R - S</p>
        <?
            }
        ?>
        </td>
        <td onclick="<?=$url;?>" align='right'>
            <?=number_format($campos[$i]['peso_unitario'], 3, ',', '.');?>
        </td>
        <td align='right'>
        <?
            echo number_format($campos[$i]['mmv'], 2, ',', '.');
            
            $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos[$i]['id_produto_acabado']);
            $font_mmv_atrelado      = (($retorno_pas_atrelados['total_mmv_pas_atrelados'] / $campos[$i]['pecas_por_jogo']) < 0) ? 'red' : 'black';
            echo '<br/><font color="'.$font_mmv_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">Atrel = '.number_format($retorno_pas_atrelados['total_mmv_pas_atrelados'] / $campos[$i]['pecas_por_jogo'], 0, '', '.').'</font>';
        ?>
        </td>
        <td onclick="<?=$url;?>" align='center'>
        <?
            //Busco a Qtde de Lote do Custo do respectivo PA do Loop, mas isso só existe p/ Custo Industrial ...
            $sql = "SELECT `qtde_lote` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `operacao_custo` = '0' LIMIT 1 ";
            $campos_custo = bancos::sql($sql);
            echo $campos_custo[0]['qtde_lote'];
        ?>
        </td>
<?/*********************************Etapa 1***********************************/?>
        <td onclick="<?=$url;?>">
        <?
            $sql = "SELECT ppe.`id_pa_pi_emb`, ppe.`pecas_por_emb`, ppe.`embalagem_default`, 
                    pi.`id_produto_insumo`, pi.`discriminacao`, pi.`unidade_conversao`, u.`sigla` 
                    FROM `pas_vs_pis_embs` ppe 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE ppe.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ORDER BY ppe.`id_pa_pi_emb` ";
            $campos_etapa1 = bancos::sql($sql);
            $linhas_etapa1 = count($campos_etapa1);
            if($linhas_etapa1 > 0) {//Encontrou Embalagens Atrelada(s)
                if($linhas_etapa1 > 0) {//Encontrou Embalagens Atrelada(s)
                    for($j = 0; $j < $linhas_etapa1; $j++) {
                        $embalagem_default  = $campos_etapa1[$j]['embalagem_default'];
                        $pecas_por_emb      = $campos_etapa1[$j]['pecas_por_emb'];
                        $discriminacao_loop = $campos_etapa1[$j]['discriminacao'];
                        $unidade_conversao  = $campos_etapa1[$j]['unidade_conversao'];

                        if($embalagem_default == 1) {//Principal
        ?>
                            <img src = '../../../imagem/certo.gif'>
                            <font title='Embalagem Principal'>
        <?
                            if($unidade_conversao > 0.00) {
                                echo number_format($pecas_por_emb, 3, ',', '.').' / '.number_format($unidade_conversao, 2, ',', '.');
                            }else {
                                echo number_format($pecas_por_emb, 3, ',', '.').' / <font color="red" title="Sem Conversão">S. C.</font>';
                            }
                            echo ' - '.$discriminacao_loop;
                            if(($j + 1) < $linhas_etapa1) echo '<br/><br/>';
        ?>
                            </font>
        <?
                        }else {
                            echo number_format($pecas_por_emb, 3, ',', '.').' - '.$discriminacao_loop;
                            if(($j + 1) < $linhas_etapa1) echo '<br/><br/>';
                        }
                    }
                }
            }else {//Não encontrou Embalagens Atrelada(s)
                echo '&nbsp;';
            }
        ?>
        </td>
<?/*********************************Etapa 2***********************************/
        /*Nessa parte eu busco o $id_produto_acabado_custo do $campos[$i]['id_produto_acabado'] e $campos[$i]['operacao_custo'] do looping para 
        ver o que temos nas demais Etapas desse Produto Acabado ...*/
        $sql = "SELECT `id_produto_acabado_custo` 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                AND `operacao_custo` = '".$campos[$i]['operacao_custo']."' LIMIT 1 ";
        $campos_custo               = bancos::sql($sql);
        $id_produto_acabado_custo   = $campos_custo[0]['id_produto_acabado_custo'];

        $sql = "SELECT `id_produto_insumo`, `peca_corte`, `comprimento_1`, `comprimento_2` 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
        $campos_etapa2 = bancos::sql($sql);
        if(count($campos_etapa2) == 1) {
            $id_produto_insumo  = $campos_etapa2[0]['id_produto_insumo'];
//Peça Corte
            $pecas_corte        = $campos_etapa2[0]['peca_corte'];
//Comprimento A
            $comprimento_a      = $campos_etapa2[0]['comprimento_1'];
//Comprimento B
            $comprimento_b      = $campos_etapa2[0]['comprimento_2'];
//Discriminação
            $sql = "SELECT discriminacao 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            $campos_pi = bancos::sql($sql);
            $discriminacao = $campos_pi[0]['discriminacao'];
        }else {
            $pecas_corte    = '';
            $comprimento_a  = '';
            $comprimento_b  = '';
            $discriminacao  = '';
        }
?>
        <td onclick="<?=$url;?>" align='center'>
            <?=$pecas_corte;?>
        </td>
        <td onclick="<?=$url;?>" align='center'>
            <?=$comprimento_a;?>
        </td>
        <td align='center'>
            <?=$comprimento_b;?>
            <!--*************************Controles de Tela*************************-->
            <!--Esse primeira hidden "hdd_produto_acabado" faz controle com checkbox Custo Liberado ...-->
            <input type='hidden' name='hdd_produto_acabado[]' value='<?=$campos[$i]['id_produto_acabado'];?>'>
            <!--Esse segundo hidden "hdd_produto_acabado_custo" faz controle com o text Corte ...-->
            <input type='hidden' name='hdd_produto_acabado_custo[]' value='<?=$id_produto_acabado_custo;?>'>
            <!--*******************************************************************-->
            &nbsp;<input type='text' name='txt_corte[]' id='txt_corte<?=$i;?>' maxlength='6' size='6' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
        <td onclick="<?=$url;?>">
            <?=$discriminacao;?>
        </td>
<?/*********************************Etapa 3***********************************/
	$sql = "SELECT pp.`id_pac_pi`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, 
                pp.`qtde`, u.`sigla` 
                FROM `pacs_vs_pis` pp 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pp.`id_produto_insumo` 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.`id_pac_pi` ";
	$campos_etapa3 = bancos::sql($sql);
	$linhas_etapa3 = count($campos_etapa3);
?>
        <td onclick="<?=$url;?>">
        <?
            if($linhas_etapa3 > 0) {
                for($j = 0; $j < $linhas_etapa3; $j++) {
                    echo number_format($campos_etapa3[$j]['qtde'], 1, ',', '.').' - '.$campos_etapa3[$j]['discriminacao'];
                    if(($j + 1) < $linhas_etapa3) echo '<br/><br/>';
                }
            }else {
                echo '';
            }
        ?>
        </td>
<?/*********************************Etapa 4***********************************/
	$sql = "SELECT pm.`id_pac_maquina`, m.`id_maquina`, m.`nome`, m.`custo_h_maquina`, pm.`tempo_hs` 
                FROM `pacs_vs_maquinas` pm 
                INNER JOIN `maquinas` m ON m.`id_maquina` = pm.`id_maquina` 
                WHERE pm.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pm.`id_pac_maquina` ";
	$campos_etapa4 = bancos::sql($sql);
	$linhas_etapa4 = count($campos_etapa4);
?>
        <td onclick="<?=$url;?>">
        <?
            if($linhas_etapa4 > 0) {
                for($j = 0; $j < $linhas_etapa4; $j++) {
                    echo number_format($campos_etapa4[$j]['tempo_hs'], 1, ',', '.').' - '.$campos_etapa4[$j]['nome'];
                    if(($j + 1) < $linhas_etapa4) echo '<br/><br/>';
                }
            }else {
                echo '';
            }
        ?>
        </td>
<?/*********************************Etapa 5***********************************/
	$sql = "SELECT ppt.`id_pac_pi_trat`, ppt.`fator`, ppt.`peso_aco`, ppt.`peso_aco_manual`, 
                pi.`id_produto_insumo`, pi.`discriminacao`, u.`sigla` 
                FROM `pacs_vs_pis_trat` ppt 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY ppt.`id_pac_pi_trat` ";
	$campos_etapa5 = bancos::sql($sql);
	$linhas_etapa5 = count($campos_etapa5);
?>
        <td onclick="<?=$url;?>">
        <?
            if($linhas_etapa5 > 0) {
                for($j = 0; $j < $linhas_etapa5; $j++) {
                    echo number_format($campos_etapa5[$j]['fator'], 2, ',', '.');
                    if($campos_etapa5[$j]['peso_aco_manual'] == 1) {
                        echo number_format($campos_etapa5[$j]['peso_aco'], 3, ',', '.');
                    }else {
                        echo number_format($campos_etapa5[$j]['peso_aco'] * $campos_etapa5[$j]['fator'], 3, ',', '.');
                    }
//Peso Aço Manual está checado
                    if($campos_etapa5[$j]['peso_aco_manual'] == 1) echo ' <font color="green"><b>REAL</b></font>';
                    echo $campos_etapa5[$j]['discriminacao'];
                    if(($j + 1) < $linhas_etapa5) echo '<br/><br/>';
                }
            }else {
                echo '';
            }
        ?>
        </td>
<?/*********************************Etapa 6***********************************/
/*Aqui traz todos os produtos insumos que estão relacionados ao produto acabado
passado por parâmetro*/
	$sql = "SELECT ppu.`id_pac_pi_usi`, ppu.`qtde`, pi.`id_produto_insumo`, pi.`discriminacao`, u.`sigla` 
                FROM `pacs_vs_pis_usis` ppu 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppu.`id_produto_insumo` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                WHERE ppu.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY ppu.`id_pac_pi_usi` ";
	$campos_etapa6 = bancos::sql($sql);
	$linhas_etapa6 = count($campos_etapa6);
?>
        <td onclick="<?=$url;?>">
        <?
            if($linhas_etapa6 > 0) {
                for($j = 0; $j < $linhas_etapa6; $j++) {
                    echo number_format($campos_etapa6[$j]['qtde'], 2, ',', '.').' - '.$campos_etapa6[$j]['discriminacao'];
                    if(($j + 1) < $linhas_etapa6) echo '<br/><br/>';
                }
            }else {
                echo '';
            }
        ?>
        </td>
<?/*********************************Etapa 7***********************************/
	$sql = "SELECT pa.`referencia`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, 
                pa.`preco_unitario`, pa.`status_custo`, pp.`id_pac_pa`, pp.`qtde`, pp.`usar_este_lote_para_orc`, 
                u.`sigla` 
                FROM `pacs_vs_pas` pp 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.`id_pac_pa` ";
	$campos_etapa7 = bancos::sql($sql);
	$linhas_etapa7 = count($campos_etapa7);
?>
        <td onclick="<?=$url;?>" align='left'>
        <?
            if($linhas_etapa7 > 0) {
                for($j = 0; $j < $linhas_etapa7; $j++) {
                    echo number_format($campos_etapa7[$j]['qtde'], 2, ',', '.').' - '.$campos_etapa7[$j]['referencia'].' - '.$campos_etapa7[$j]['discriminacao'];
                    if($campos_etapa7[$j]['usar_este_lote_para_orc'] == 'S') echo '<img src="../../../imagem/certo.gif" title="Usa este Lote de Custo p/ Orc" style="cursor:help">';
                    if(($j + 1) < $linhas_etapa7) echo '<br/><br/>';
                }
            }
        ?>
        </td>
<?/***************************************************************************/?>
        <td onclick="<?=$url;?>" align='left'>
        <?
            echo $campos[$i]['referencia'];
            if(!empty($campos[$i]['observacao'])) echo ' - '.$campos[$i]['observacao'];
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr align='center'>
        <td class='linhacabecalho' colspan='20'>
            &nbsp;
        </td>
        <td class='linhacabecalho'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
        <td class='linhacabecalho' colspan='7' align='left'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'custo_unificado.php'" class='botao'>
            <!--<input type="button" name="cmd_modo_normal" value="Modo Normal" title="Modo Normal" onclick="modo_normal()" class='botao'>-->
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='28'>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<font color='red'><b>Observação:</b></font>

<font><b>Discriminação </b></font>-> Custo(s) Liberado(s)
<font color='red'><b>Discriminação </b></font>-> Custo(s) não Liberado(s)
</pre>
<?
    }
}
?>