<?
if($nao_redeclarar != 1) {//Às vezes essa tela é requirida dentro dela mesmo, e por isso tem esse desvio ...
    require('../../../../../lib/segurancas.php');
    require('../../../../../lib/calculos.php');
    require('../../../../../lib/compras_new.php');
    require('../../../../../lib/comunicacao.php');
    require('../../../../../lib/estoque_acabado.php');
    require('../../../../../lib/estoque_new.php');
    require('../../../../../lib/genericas.php');
    require('../../../../../lib/producao.php');
    require('../../../../../lib/intermodular.php');
    require('../../../../../lib/data.php');
    require('../../../../../lib/variaveis/compras.php');
    if(empty($_GET['pop_up'])) {
        require('../../../../../lib/menu/menu.php');
        segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');
    }
    session_start('funcionarios');
}

$mensagem[1] = 'ITEM DE PEDIDO EXCLUIDO NA NOTA COM SUCESSO !';
$mensagem[2] = 'ITEM DE PEDIDO ALTERADO NA NOTA COM SUCESSO !';
$mensagem[3] = 'ITEM DE NOTA FISCAL ABERTO COM SUCESSO !';
$mensagem[4] = 'FAÇA AS CORREÇOES NECESSÁRIAS, POIS O ESTOQUE DO ITEM FICOU (-) NEGATIVO !';

/*****************Funções Particulares desta Tela******************/
if($nao_redeclarar != 1) {//Às vezes essa tela é requirida dentro dela mesmo, e por isso tem esse desvio ...
    function contador_antecipacao_aberto($id_fornecedor, $situacao_nf, $tipo_nf) {
    //Função para contar o número de antecipações que o fornecedor tem em aberto do mesmo tipo de Nota Fiscal
        if($situacao_nf == 0 || $situacao_nf == 1) { // Situação_nf -> 1 - Aberto
            $sql = "SELECT a.`id_antecipacao` 
                    FROM `pedidos` p 
                    INNER JOIN `antecipacoes` a ON a.`id_pedido` = p.`id_pedido` AND a.`status` = '1' 
                    WHERE p.`id_fornecedor` = '$id_fornecedor' 
                    AND p.`ativo` = '1' 
                    AND p.`tipo_nota` = '$tipo_nf' LIMIT 1 ";
            if(count(bancos::sql($sql)) > 0) return 1;
        }
    }
    /*Aki nessa função carrega a 'Qtde' e 'Valor Total' das Antecipações existentes nesta Nota Fiscal,
    esses valores vão servir de auxílio para a outra função de calculo_prazo*/
    function calculo_prazo($id_nfe, $prazo_a, $prazo_b, $prazo_c, $valor_total_nfe) {
        $retorno_antecipacoes = compras_new::calculo_valor_antecipacao($id_nfe);
        $valor_total_antecipacoes = $retorno_antecipacoes['valor_total_antecipacoes'];
        //Cálculo para fazer a divisão das parcelas
        if($valor_total_nfe < 0) {
            $negativo=- 1;
            $valor_total_nfe*= $negativo;
        }else {
            $negativo = 1;
        }
        if($prazo_b != 0 and $prazo_c != 0) {//3 parcelas
            if($valor_total_antecipacoes >= $valor_total_nfe) {
                $valor_aux = $valor_total_nfe - $valor_total_antecipacoes;
                $valor_a = ($valor_aux / 3);
                $valor_b = ($valor_aux / 3);
                $valor_c = $valor_aux - $valor_a - $valor_b;
                return array ('a'=>($valor_a*$negativo), 'b'=>($valor_b*$negativo), 'c'=>($valor_c*$negativo));//$prazo_a=$prazo_b=$prazo_c=0;
            }else {
                $parcela = (float)$valor_total_nfe / 3;
                $valor_a = $valor_b = round(round($parcela, 3), 2);
                $valor_c = round(round($valor_total_nfe - $valor_a - $valor_b, 3), 2);
                if($valor_a <= $valor_total_antecipacoes) {
                    $valor_total_antecipacoes = $valor_total_antecipacoes - $valor_a;
                    $valor_a = 0;
                }else {
                    $valor_a = $valor_a - $valor_total_antecipacoes;
                    $valor_total_antecipacoes = 0;
                }
                if($valor_b <= $valor_total_antecipacoes) {
                    $valor_total_antecipacoes = $valor_total_antecipacoes - $valor_b;
                    $valor_b = 0;
                    $valor_c = $valor_c - $valor_total_antecipacoes;
                }else {
                    $valor_b = $valor_b - $valor_total_antecipacoes;
                    $valor_total_antecipacoes = 0;
                }
            }
//Esta perte foi um macete para colocar valores diferentes no venc A e venc B
            $valores_novos = $valor_a + $valor_b + $valor_c;
            $valores_db = $GLOBALS['valor_a'] + $GLOBALS['valor_b'] + $GLOBALS['valor_c'];
            //if($valores_db==$valores_novos) { //nao retire o double ou o round se não da pal de PHP inclusive na nota 4175 tecnibra
            if((double)round(round($valores_db, 2), 3) == (double)round(round($valores_novos, 2), 3)) { //nao retire o double ou o round se não da pal de PHP inclusive na nota 4175 tecnibra
                $valor_a = $GLOBALS['valor_a'];
                $valor_b = $GLOBALS['valor_b'];
                $valor_c = $GLOBALS['valor_c'];
            }
/*****************************************************************************/
            return array ('a' => ($valor_a * $negativo), 'b' => ($valor_b * $negativo), 'c'=> ($valor_c * $negativo));
        }else if($prazo_b != 0 and $prazo_c == 0) {//2 parcelas
            if($valor_total_antecipacoes >= $valor_total_nfe) {
                $valor_aux = $valor_total_nfe - $valor_total_antecipacoes;
                $valor_a = ($valor_aux / 2);
                $valor_b = $valor_aux - $valor_a;
                return array ('a'=> ($valor_a * $negativo), 'b'=> ($valor_b * $negativo), 'c'=> 0);
            }else {
                $valor_a = (float)round(round($valor_total_nfe / 2, 3), 2);
                $valor_b = (float)round(round($valor_total_nfe - $valor_a, 3), 2);
                if($valor_a <= $valor_total_antecipacoes) {
                    $valor_total_antecipacoes = $valor_total_antecipacoes - $valor_a;
                    $valor_a = 0;
                    $valor_b-= $valor_total_antecipacoes;
                }else {
                    $valor_a-= $valor_total_antecipacoes;
                    $valor_total_antecipacoes = 0;
                }
            }
//Esta parte foi um macete para colocar valores diferentes no venc A e venc B
            $valores_novos = $valor_a + $valor_b;
            $valores_db = $GLOBALS['valor_a']+$GLOBALS['valor_b'];
            if(round($valores_db, 2) == round($valores_novos,2)) { //nao pode tirar este arredondamento por causa do pau q tem no PHP basiado na nota 91204 jorge ....
                $valor_a = $GLOBALS['valor_a'];
                $valor_b = $GLOBALS['valor_b'];
            }
/*****************************************************************************/
            return array ('a'=>($valor_a*$negativo), 'b'=>($valor_b*$negativo), 'c'=>0);
        }else if($prazo_b == 0 and $prazo_c == 0) {//1 parcela
                $valor_a = (float)round(round($valor_total_nfe - $valor_total_antecipacoes, 3), 2);
                return array ('a'=>($valor_a*$negativo), 'b'=>0, 'c'=>0);
        }
    }
}
/******************************************************************/

//Aki é a parte de deletar os Itens da Nota Fiscal ...
if($passo == 1) {
//Busco também o id_item_pedido p/ verificar se esse item é um Produto ou apenas um Ajuste ...
    $sql = "SELECT `id_nfe`, `id_item_pedido` 
            FROM `nfe_historicos` 
            WHERE `id_nfe_historico` = '$_GET[id_nfe_historico]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_nfe         = $campos[0]['id_nfe'];
    $id_item_pedido = $campos[0]['id_item_pedido'];
/*Verifico se o item com a qual está sendo excluído possui o id 1340 ou 1426,
q na realidade são os ajustes, sendo assim eu deleto estes também da tab. pedidos*/
    $sql = "SELECT `id_produto_insumo` 
            FROM `itens_pedidos` 
            WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
    $campos_item_pedido = bancos::sql($sql);
/**********************************Ajuste da Nota Fiscal**********************************/
    if($campos_item_pedido[0]['id_produto_insumo'] == 1340 || $campos_item_pedido[0]['id_produto_insumo'] == 1426) {
//Deletou o item da Nota Fiscal
        $sql = "DELETE FROM `nfe_historicos` WHERE `id_nfe_historico` = '$_GET[id_nfe_historico]' LIMIT 1 ";
        bancos::sql($sql);
//Busca do id_pedido, vou precisar dele + abaixo
        $sql = "SELECT `id_pedido` 
                FROM `itens_pedidos` 
                WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        $campos_item_pedido     = bancos::sql($sql);
        $id_pedido              = $campos_item_pedido[0]['id_pedido'];
//Deletou o item de Pedidos
        $sql = "DELETE FROM `itens_pedidos` WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        bancos::sql($sql);
//Verifico a qtde de Itens que ainda restaram no Pedido
        $sql = "SELECT COUNT(`id_item_pedido`) AS qtde_itens_pedidos 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos_item_pedido = bancos::sql($sql);
        if(count($campos_item_pedido) == 0) {//Se não existir + nenhum Item, eu volto a Sit. do Ped. p/ Aberto
            $sql = "UPDATE `pedidos` SET `status` = '0' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
            bancos::sql($sql);
        }
/*****************************************************************************************/
    }else {
//1)*******************************************Nota Fiscal********************************************/
//Deletou o item da Nota Fiscal
        $sql = "DELETE FROM `nfe_historicos` WHERE `id_nfe_historico` = '$_GET[id_nfe_historico]' LIMIT 1 ";
        bancos::sql($sql);
//Voltou o status do Item de Pedido para 0, para q este possa ser importado futur.
        compras_new::pedido_status($id_item_pedido);
//2)***********************************************OS*************************************************/
        $sql = "SELECT `id_os`, `id_os_item` 
                FROM `oss_itens` 
                WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Se achar
            $id_os_item = $campos[0]['id_os_item'];//Vou utilizar esse id na Função ...
            $id_os      = $campos[0]['id_os'];//Vou utilizar esse id na Função ...
//Essa função serve tanto para o Incluir, como Alterar e Excluir Item da Nota Fiscal ...
            producao::atualizar_status_item_os($id_os_item);
/*****************************************************************************************************/
/*****************Controle com o Status da OS*****************/
            producao::atualizar_status_os($id_os);
        }
/*************************************************************/
    }

    //Aqui eu verifico a NF possui formas de Vencimento ...
    $sql = "SELECT `id_nfe_financiamento` 
            FROM `nfe_financiamentos` 
            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos_financiamento = bancos::sql($sql);
    //Se existir então chama a função, toda vez q excluir 1 item p/ recalcular as parcelas ...
    if(count($campos_financiamento) == 1) {
    /*Toda vez que eu excluir os Itens eu garanto q o Sistema está zerando os Prazos de Vencimento do Modo 
    Antigo p/ não dar conflitos com o JavaScript no cabeçalho da NF ...*/
        $sql = "UPDATE `nfe` SET `valor_a` = '0', `valor_b` = '0', `valor_c` = '0' WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
        bancos::sql($sql);
    /*********************************************/
    /*Essa função pega o valor da Nota Fiscal, e desconta desse valor, o valor total das antecipações e 
    e divide o valor restante de acordo com a Qtde de Prazos*/
        compras_new::calculo_valor_financiamento($id_nfe);
    /*********************************************/
    }
?>
    <Script Language = 'Javascript'>
//Para não perguntar + nenhuma vez das Antecipações
        window.parent.itens.document.location = 'itens.php?id_nfe=<?=$id_nfe;?>&perguntar_uma_vez=1&valor=1'
        window.parent.rodape.document.form.submit()
    </Script>
<?
//Nessa parte é aonde eu abro o Item da Nota Fiscal e faço a Manipulação deste Item no Estoque no qual eu desejo estornar ...
}else if($passo == 2) {
//Gravando a Manipulação ...
    $data_sys = date('Y-m-d H:i:s');
//Aqui eu busco o responsável pela Manipulação em Estoque ...
    $sql = "SELECT `login` 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $login  = $campos[0]['login'];
//Justificativa ...
    $observacao = 'Item estornado automaticamenta pela Função existente em Nota Fiscal de Compras - '.$login;

    //Através do Id Item Nota Fiscal, eu busco qual a qtde e o id_produto_insumo ...
    $sql = "SELECT ip.`id_produto_insumo`, ip.`estocar`, nfe.`num_nota`, nfeh.`id_nfe`, nfeh.`qtde_entregue`, nfeh.`entrada_antecipada` 
            FROM `nfe_historicos` nfeh 
            INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
            INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
            WHERE nfeh.`id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_produto_insumo  = $campos[0]['id_produto_insumo'];
    $estocar            = $campos[0]['estocar'];
    $num_nota           = $campos[0]['num_nota'];
    $id_nfe             = $campos[0]['id_nfe'];
    $qtde_entregue      = $campos[0]['qtde_entregue'];
    $entrada_antecipada = $campos[0]['entrada_antecipada'];
//Agora com o id_produto_insumo eu verifico se este é um Produto Acabado ...
    $sql = "SELECT `id_produto_acabado` 
            FROM `produtos_acabados` 
            WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
    $campos_pipa = bancos::sql($sql);
    if(count($campos_pipa) == 1) {//Significa que o PI é um PA ...
        $id_produto_acabado = $campos_pipa[0]['id_produto_acabado'];
        $vetor_estoque      = estoque_acabado::qtde_estoque($id_produto_acabado);
        $qtde_real          = $vetor_estoque[0];
        $qtde_disponivel    = $vetor_estoque[3];
//Essa logística foi herdada da Biblioteca "estoque_new" ...
        if($qtde_real >= $qtde_entregue && $qtde_disponivel >= $qtde_entregue) {
            //Abrindo o Item da Nota Fiscal novamente ...
            $sql = "UPDATE `nfe_historicos` SET `entrada_antecipada` = '0', `status` = '0' WHERE `id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
            bancos::sql($sql);
            /*Retira a qtde do Item de Nota Fiscal que foi liberada p/ o Estoque Real e Disponível, 
            também atualizo o campo `data_atualizacao` da tabela "estoques_acabados" com a Data do dia 
            em que ocorreu a ação ...*/
            $sql = "UPDATE `estoques_acabados` SET `qtde` = `qtde` - $qtde_entregue, `entrada_antecipada` = `entrada_antecipada` - $entrada_antecipada, `qtde_disponivel` = `qtde_disponivel` - $qtde_entregue, `data_atualizacao` = '$data_sys' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            bancos::sql($sql);
            /*Atualiza o Prazo de Entrega ...
            
            * Esse WHERE `qtde_disponivel` >= `qtde_pendente`, significa que não precisamos 
            colocar Prazo de Entrega, porque o mesmo já é imediato ...*/
            $sql = "UPDATE `estoques_acabados` SET `prazo_entrega` = ' => ERP | $data_sys' WHERE `qtde_disponivel` >= `qtde_pendente` AND `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            bancos::sql($sql);
            /*Criando o Log de Saldo do Estoque, chamo a função de Estoque porque houve mudanças 
            na Qtde Real que foi atualizada acima ...*/
            $vetor_estoque      = estoque_acabado::qtde_estoque($id_produto_acabado);
            $qtde_real          = $vetor_estoque[0];

            $sql = "INSERT INTO `rel_saldos_estoques` (`id_rel_saldo_estoque`, `id_produto_acabado`, `id_funcionario`, `qtde_manipulada`, `saldo_est_real`, `data_acao`, `acao`, `obs_acao`) 
                    VALUES (NULL, $id_produto_acabado, $_SESSION[id_funcionario], '$qtde_entregue', '$qtde_real', '$data_sys', '2', 'Estorno Liberação de Compras. id_nfe=$id_nfe') ";
            bancos::sql($sql);
        }else {//Mensagem(ns) de Parecer p/ o usuário caso o mesmo não consiga Estornar o Item de Nota Fiscal ...
            if($qtde_real < $qtde_entregue) {//Quantidade Real do PA menor do que a Quantidade Entregue do Item ...
?>
    <Script Language = 'Javascript'>
        alert('ESTE PI QUE ESTÁ SENDO ESTORNADO É UM "PIPA" !!!\n\nNÃO É POSSÍVEL ESTAR O MESMO DEVIDO A SUA "QTDE REAL" DE ESTOQUE SER MENOR DO QUE A "QTDE ENTREGUE" EM NOTA FISCAL !')
    </Script>
<?
            }
            if($qtde_disponivel < $qtde_entregue) {//Quantidade Real do PA menor do que a Quantidade Entregue do Item ...
?>
    <Script Language = 'Javascript'>
        alert('ESTE PI QUE ESTÁ SENDO ESTORNADO É UM "PIPA" !!!\n\nNÃO É POSSÍVEL ESTAR O MESMO DEVIDO A SUA "QTDE DISPONÍVEL" DE ESTOQUE SER MENOR DO QUE A "QTDE ENTREGUE" EM NOTA FISCAL !')
    </Script>
<?
            }
        }
    }else {//Significa que é um PI mesmo ...
/**************************************Controle com PI**************************************/
/*Aqui eu verifico o quanto que eu tenho deste Produto em Estoque p/ ver ser é possível estar fazendo
a Manipulação deste Item ...*/
        $sql = "SELECT ei.`qtde` AS qtde_estoque, u.`sigla` 
                FROM `estoques_insumos` ei 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ei.`id_produto_insumo` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                WHERE ei.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos_estoque_pi = bancos::sql($sql);
        if(count($campos_estoque_pi) == 1) {//Se encontrar ...
            $qtde_estoque   = $campos_estoque_pi[0]['qtde_estoque'];
            $unidade        = $campos_estoque_pi[0]['sigla'];
        }else {//Se não encontrar ...
            $qtde_estoque   = 0;
        }
        
        if($estocar == 0) {//Não Estocável ...
            $estoque_final  = $qtde_estoque;//Nesse caso eu não preciso controlar o Estoque do PI ...
	}else {//Estocável ...
            $estoque_final  = $qtde_estoque - $qtde_entregue;//É o Estoque de PI - a Qtde do Item da NF ...

            if($estoque_final >= 0) {
                $valor = 3;//Item de Nota Fiscal aberto e Qtde de Estoque Final >= 0 ...
            }else {
                //Busco a discriminação do "id_produto_insumo" passado por parâmetro p/ enviar por e-mail ...
                $sql = "SELECT discriminacao 
                        FROM `produtos_insumos` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                $campos_pi      = bancos::sql($sql);
                
                $assunto        = 'PI '.$campos_pi[0]['discriminacao'].' com estoque Negativo';
                $mensagem_email = 'Após a última alteração de estoque, o PI '.$campos_pi[0]['discriminacao'].' ficou com estoque (-) '.number_format(abs($estoque_final), 2, ',', '.');
                comunicacao::email('ERP - GRUPO ALBAFER', 'gcompras@grupoalbafer.com.br', 'roberto@grupoalbafer.com.br; rodrigo.bispo@grupoalbafer.com.br', $assunto, $mensagem_email);

                $valor = 4;//Item de Nota Fiscal aberto e Qtde de Estoque Final < 0
            }
	}
        //Gravando a Baixa ...
        $observacao_baixa   = '<b>Estorno automático de <font color="darkblue">'.number_format($qtde_entregue*(-1), 2, ',', '.').' '.$unidade.'</font> na NF <font color="darkblue">'.$num_nota.'</font></b>';
        $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `estoque_final`, `observacao`, `acao`, `troca`, `data_sys`) VALUES (NULL, '$id_produto_insumo', '$_SESSION[id_funcionario]', '$_SESSION[id_funcionario]', '', '".$qtde_entregue*(-1)."', '$estoque_final', '$observacao_baixa', 'E', 'S', '$data_sys') ";
        bancos::sql($sql);
        estoque_ic::atualizar($id_produto_insumo, 0);

        //Abrindo o Item da Nota Fiscal novamente ...
        $sql = "UPDATE `nfe_historicos` SET `status` = '0' WHERE `id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
//Para não perguntar + nenhuma vez das Antecipações
        window.parent.itens.document.location = 'itens.php?id_nfe=<?=$id_nfe;?>&perguntar_uma_vez=1&valor=<?=$valor;?>'
        window.parent.rodape.document.form.submit()
    </Script>
<?
}else {
//Busca o nome do Fornecedor com + detalhes alguns detalhes de dados da Nota Fiscal
    $sql = "SELECT f.`id_fornecedor`, f.`id_pais`, f.`razaosocial`, 
            f.`optante_simples_nacional`, nfe.*, CONCAT(tm.`simbolo`, ' ') AS moeda 
            FROM `nfe` 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = nfe.`id_tipo_moeda` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
            WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $id_fornecedor              = $campos[0]['id_fornecedor'];
    $id_tipo_pagamento_recebimento = $campos[0]['id_tipo_pagamento_recebimento'];
    $id_fornecedor_propriedade  = $campos[0]['id_fornecedor_propriedade'];
    $num_nota                   = $campos[0]['num_nota'];
    $financiamento_taxa         = number_format($campos[0]['financiamento_taxa'], 1, ',', '.');
    $financiamento_prazo_dias   = $campos[0]['financiamento_prazo_dias'];
    $pago_pelo_caixa_compras    = $campos[0]['pago_pelo_caixa_compras'];
    $moeda                      = $campos[0]['moeda'];
    $id_pais                    = $campos[0]['id_pais'];
    $razao_social               = $campos[0]['razaosocial'];
    $optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
//Tratamento para o Tipo de Nota
    if($campos[0]['tipo'] == 1) {
        $tipo = 'NF';
        $tipo_nf = 1;
    }else {
        $tipo = 'SGD';
        $tipo_nf = 2;
    }
//Tratamento para a Empresa
    if($campos[0]['id_empresa'] == 1) {
        $empresa = 'ALBAFER';
    }else if($campos[0]['id_empresa'] == 2) {
        $empresa = 'TOOL MASTER';
    }else if($campos[0]['id_empresa'] == 4) {
        $empresa = 'GRUPO';
    }
    $data_emissao       = (!empty($campos[0]['data_emissao'])) ? data::datetodata($campos[0]['data_emissao'], '/') : '';
    $data_entrega       = (!empty($campos[0]['data_entrega'])) ? data::datetodata($campos[0]['data_entrega'], '/') : '';
//Prazos da Nota
    $prazo_a            = $campos[0]['prazo_a'];
    $data_prazo_a       = data::adicionar_data_hora($data_emissao, $prazo_a);
    $prazo_b            = $campos[0]['prazo_b'];
    $data_prazo_b       = ($prazo_b != 0) ? data::adicionar_data_hora($data_emissao, $prazo_b) : '&nbsp;';
    $prazo_c            = $campos[0]['prazo_c'];
    $data_prazo_c       = ($prazo_c != 0) ? data::adicionar_data_hora($data_emissao, $prazo_c) : '&nbsp;';
    $situacao           = $campos[0]['situacao'];//Situação da Nota Fiscal, aberto, parcial, total
    $livre_debito       = $campos[0]['livre_debito'];
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function igualar(indice) {
    var controle = 0, existe = 0, liberado = '', codigo = '', cont = 0
    var elemento = '', objeto = ''
    for(i = 0; i < parent.itens.document.form.elements.length; i++) {
        if(parent.itens.document.form.elements[i].type == 'radio') cont ++
    }
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'hidden' && document.form.elements[i].name == 'opt_item') existe ++
    }
    if(cont > 1) {
        elemento = parent.itens.document.form.opt_item[indice].value
        objeto = parent.itens.document.form.opt_item[indice]
    }else {
        if(existe == 0) {
            elemento = parent.itens.document.form.opt_item.value
            objeto = parent.itens.document.form.opt_item
        }else {
            elemento = parent.itens.document.form.opt_item[indice].value
            objeto = parent.itens.document.form.opt_item[indice]
        }
    }
    if(objeto.type == 'radio') {
        for(i = 0; i < elemento.length; i ++) {
            if(elemento.charAt(i) == '|') {
                controle ++
            }else {
                if(controle == 1) {
                    liberado = liberado + elemento.charAt(i)
                }else {
                    codigo = codigo + elemento.charAt(i)
                }
            }
        }
        parent.itens.document.form.opt_item_principal.value = codigo
    }else {
        limpar_radio()
    }
}
function limpar_radio() {
    for(i = 0; i < parent.itens.document.form.elements.length; i++) {
        if(parent.itens.document.form.elements[i].type == 'radio') parent.itens.document.form.elements[i].checked = false
    }
}
</Script>
</head>
<?
    //Se a Empresa atual da Nota Fiscal = 'ALBAFER' ou 'TOOL MASTER' então executo o alert abaixo ...
    if($campos[0]['id_empresa'] == 1 || $campos[0]['id_empresa'] == 2) $onload = "alert('VERIFICAR SE ENVIARAM O ARQUIVO XML ANTES DE LIBERAR A NOTA FISCAL !')";
?>
<body onload="<?=$onload;?>">
<form name='form'>
<!--*********************************************************************************************-->
<!--Dados de Fornecedor da Nota Fiscal-->
<table width='90%' border='0' cellspacing='0' cellpadding='0' align='center'>
<?
//Se a Empresa atual da Nota Fiscal = 'ALBAFER' ou 'TOOL MASTER' então mostro o texto abaixo ...
    if($campos[0]['id_empresa'] == 1 || $campos[0]['id_empresa'] == 2) {
?>
    <tr align='center'>
        <td>
            <font color='red' size='5'>
                <b>VERIFICAR SE ENVIARAM O ARQUIVO XML ANTES DE LIBERAR A NOTA FISCAL !!!</b>
            </font>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <font size='3'>
                NF N.º 
                <font color='#5DECFF'>
                    <?=$num_nota;?>
                </font>
                <?
                    //Se existir a marcação de Livre de Débito ...
                    if($livre_debito == 'S') echo '<font color="darkgreen" title="Livre de Débito Propaganda / Marketing" style="cursor:help"><b> (LD)</b></font>';

    //Aqui eu verifico se a NF possui uma Carta de Correção ...
                    $sql = "SELECT `id_carta_correcao` 
                            FROM `cartas_correcoes` 
                            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
                    $campos_carta_correcao = bancos::sql($sql);
                    if(count($campos_carta_correcao) == 1) {
                ?>
                    &nbsp;-&nbsp;
                    <a href="javascript:nova_janela('../../../../classes/nf_carta_correcao/itens/relatorio/imprimir.php?id_carta_correcao=<?=$campos_carta_correcao[0]['id_carta_correcao'];?>', 'ITENS', 'F')" class='link'>
                        <img src='../../../../../imagem/carta.jpeg' title='Detalhes de Carta de Correção' alt='Detalhes de Carta de Correção' border='1'>
                        <font color='yellow' size='-1'>
                            Carta de Correção
                        </font>
                    </a>
                <?
                    }
                ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' style='cursor:pointer'>
        <td colspan='2'>
            <a href="javascript:nova_janela('../../../../classes/fornecedor/alterar.php?passo=1&id_fornecedor=<?=$id_fornecedor;?>&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <font color='yellow' size='-1'>
                    Fornecedor:
                    <font color="#FFFFFF" size='-1'>
                        <?=$razao_social;?>
                    </font>
                </font>
                <img src="../../../../../imagem/propriedades.png" title="Detalhes de Cliente" alt="Detalhes de Cliente" style="cursor:pointer" border="0">
            </a>
            <?
                if($optante_simples_nacional == 'S') {
            ?>
                    <font color="darkgreen" size="2"> (Optante pelo Simples Nacional)</font>
            <?
                }
            ?>          
        </td>
    </tr>
</table>
<!--Pré-Cabeçalho de Nota Fiscal-->
<table width="90%" border='1' cellspacing='0' cellpadding='0' align='center'>
	<tr class='linhanormal'>
		<td colspan='4' bgcolor='#CECECE'>
                    <b>CONTROLE DE COMPRA: </b>
                    <?
                        echo $empresa.' ('.$tipo.')';
                        if($pago_pelo_caixa_compras == 'S') echo '&nbsp;-&nbsp;<font color="red" size="2"><b>(PAGO PELO CAIXA DE COMPRAS)</b></font>'
                    ?>
		</td>
		<td colspan='2' bgcolor='#CECECE'>
		<?
//Se for Internacional
			if($id_pais != 31) {
				$rotulo = 'DATA DO B/L: ';
//Se for Nacional
			}else {
				$rotulo = 'DATA DE EMISSÃO: ';
			}
		?>
			<b><?=$rotulo;?></b><?=$data_emissao;?>
		</td>
		<td colspan='2' bgcolor='#CECECE'>
			<b>DATA DE ENTREGA: </b><?=$data_entrega;?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan='4' bgcolor='#CECECE'>
			<b>TIPO DE PAGAMENTO: </b>
			<?
//Busca do Tipo de Pagamento da Nota Fiscal ...
                            $sql = "SELECT `pagamento` 
                                    FROM `tipos_pagamentos` 
                                    WHERE `ativo` = '1' 
                                    AND `id_tipo_pagamento` = '$id_tipo_pagamento_recebimento' LIMIT 1 ";
                            $campos_tipo_pagamento = bancos::sql($sql);
                            echo $campos_tipo_pagamento[0]['pagamento'];
			?>
		</td>
		<td colspan='4' bgcolor='#CECECE'>
			<b>CONTA CORRENTE: </b>
			<?
				if($id_fornecedor_propriedade > 0) {//Busca dos Dados de Conta Corrente do Fornecedor ...
					$sql = "SELECT CONCAT(num_cc, ' | ', agencia, ' | ', banco, ' | ', correntista, ' | ', cnpj_cpf) AS dados 
                                                FROM `fornecedores_propriedades` 
                                                WHERE `id_fornecedor_propriedade` = '$id_fornecedor_propriedade' 
                                                AND `ativo` = '1' ";
					$campos_propriedades = bancos::sql($sql);
					if(count($campos_propriedades) == 1) {
						echo $campos_propriedades[0]['dados'];
					}else {
						echo '-';
					}
				}else {
					echo '-';
				}
			?>
		</td>
	</tr>
	<?
		//Busca do Nome da Importação dessa NF ...
		$sql = "SELECT i.nome 
                        FROM `nfe` 
                        INNER JOIN `importacoes` i ON i.id_importacao = nfe.id_importacao 
                        WHERE nfe.`id_nfe` = '$id_nfe' ";
		$campos_importacao = bancos::sql($sql);
		if(count($campos_importacao) == 1) {//Caso tenha encontrado importação ...
			$colspan = '2';
		}else {//Não encontrou importação ...
			$colspan = '4';
		}
	?>
	<tr class='linhanormal'>
		<td colspan='4' bgcolor='#CECECE'>
			<b>TAXA DE FINANCIAMENTO: </b>
			<?=$financiamento_taxa;?>
		</td>
		<td colspan="<?=$colspan;?>" bgcolor='#CECECE'>
			<b>PRAZO DE FINANCIAMENTO: </b>
			<?=$financiamento_prazo_dias.' DDL';?>
		</td>
		<?
			if(count($campos_importacao) == 1) {//Caso tenha encontrado importação ...
		?>
		<td colspan='2' bgcolor='#CECECE'>
			<b>IMPORTAÇÃO: </b>
			<?
				//Busca do Nome da Importação dessa NF ...
				$sql = "SELECT i.nome 
                                        FROM `nfe` 
                                        INNER JOIN `importacoes` i ON i.id_importacao = nfe.id_importacao 
                                        WHERE nfe.`id_nfe` = '$id_nfe' ";
				$campos_importacao = bancos::sql($sql);
				echo $campos_importacao[0]['nome'];
			?>
		</td>
		<?
			}
		?>
	</tr>
<?
        $calculo_total_impostos = calculos::calculo_impostos(0, $id_nfe, 'NFC');
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Verifico se essa NF foi feita através do modo Financiamento ...
	$sql = "SELECT `id_nfe_financiamento` 
                FROM `nfe_financiamentos` 
                WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
	$campos_financiamento = bancos::sql($sql);
	$modo_financiamento = count($campos_financiamento);

	if($modo_financiamento == 1) {//Foi feito pelo modo financiamento ...
//Aqui eu busco todas as Parcelas do Financiamento da NF que foi através do Pedido ...
		$sql = "SELECT nf.*, tm.`simbolo` 
                        FROM `nfe_financiamentos` nf 
                        INNER JOIN `nfe` ON nfe.`id_nfe` = nf.`id_nfe` 
                        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = nfe.`id_tipo_moeda` 
                        WHERE nf.`id_nfe` = '$id_nfe' ORDER BY nf.`dias` ";
		$campos_financiamento = bancos::sql($sql);
		$linhas_financiamento = count($campos_financiamento);
//Disparo do Loop ...
		for($i = 0; $i < $linhas_financiamento; $i++) {
	?>
	<tr class='linhanormal'>
		<td colspan="3">
			<font color="darkblue">
				<b>Parcela N.º <?=$i + 1;?>:</b>
			</font>
		</td>
		<td>
			<font color="darkblue">Dias: </font><?=$campos_financiamento[$i]['dias'];?>
		</td>
		<td>
			<font color="darkblue">Data: </font><?=data::datetodata($campos_financiamento[$i]['data'], '/');?>
		</td>
		<td colspan="3">
			<font color="darkblue">Valor <?=$campos_financiamento[$i]['simbolo'];?>: </font><?=number_format($campos_financiamento[$i]['valor_parcela_nf'], 2, ',', '.');?>
		</td>
	</tr>
<?
		}
//Modo Normal, modo antigo ...
	}else {
//Eu preciso desses valores aki, pq eles são valores utilizados como GLOBAL para a function calculo_prazo()
		$valor_a = $campos[0]['valor_a'];
		$valor_b = $campos[0]['valor_b'];
		$valor_c = $campos[0]['valor_c'];
/*Essa função pega o valor da Nota Fiscal, desconta desse valor, o valor total das antecipações e 
e divide o valor restante de acordo com a Qtde de Prazos*/
		$valores = calculo_prazo($id_nfe, $prazo_a, $prazo_b, $prazo_c, round(round($calculo_total_impostos['valor_total_nota'], 3), 2));
//Aqui já grava na Nota Fiscal os valores retornados através da função de cada Prazo de Faturamento
		$sql = "UPDATE `nfe` SET `valor_a` = ".$valores['a'].", `valor_b` = ".$valores['b'].", `valor_c` = ".$valores['c']." WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
		bancos::sql($sql);
/*********************************************/
//Dados Referente ao A
?>
	<tr class='linhanormal'>
		<td colspan="3">
			<b>PRAZO (A): </b>
			<?
				if($prazo_a == 0) {
					echo 'À VISTA';
				}else {
					echo $prazo_a.' DDL';
				}
			?>
		</td>
		<td colspan="3">
			<b>VENC: </b><?=$data_prazo_a?>
		</td>
		<td colspan='2'>
			<b>VALOR <?=$moeda;?>: </b><?=number_format($valores['a'], 2, ',', '.');?>
		</td>
	</tr>
<?
/*********************************************/
//Dados Referente ao B
?>
	<tr class='linhanormal'>
		<td colspan="3">
			<b>PRAZO (B): </b>
			<?
				if($prazo_b != 0) {
					echo $prazo_b.' DDL';
				}else {
					echo '&nbsp;';
				}
			?>
		</td>
		<td colspan="3">
			<b>VENC: </b><?=$data_prazo_b;?>
		</td>
		<td colspan='2'>
			<b>VALOR <?=$moeda;?>: </b><?=number_format($valores['b'], 2, ',', '.');?>
		</td>
	</tr>
<?
/*********************************************/
//Dados Referente ao C
?>
	<tr class='linhanormal'>
		<td colspan="3">
			<b>PRAZO (C): </b>
			<?
				if($prazo_c != 0) {
					echo $prazo_c.' DDL';
				}else {
					echo '&nbsp;';
				}
			?>
		</td>
		<td colspan="3">
			<b>VENC: </b><?=$data_prazo_c;?>
			</font>
		</td>
		<td colspan='2'>
			<b>VALOR <?=$moeda;?>: </b><?=number_format($valores['c'], 2, ',', '.');?>
		</td>
	</tr>
<?
	}
//Busca de Todas as Antecipações que estão atreladas nessa Nota Fiscal corrente
	$sql = "SELECT a.* 
                FROM `nfe_antecipacoes` nfea 
                INNER JOIN `antecipacoes` a ON a.`id_antecipacao` = nfea.`id_antecipacao` 
                WHERE nfea.`id_nfe` = '$id_nfe' ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	if($linhas > 0) {//Se existir antecipações, ele começa a printar ...
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan="9">
			<font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
				ANTECIPAÇÃO(ÕES)
			</font>
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class='linhanormal' align='center'>
		<td colspan="3">
			<font face='Verdana, Arial, Helvetica, sans-serif' color='#002E84' size='2'>
				<?='<b>ANTECIPAÇÃO N.º '.($i + 1).'</b>';?>
			</font>
		</td>
		<td colspan="3">
			<font face='Verdana, Arial, Helvetica, sans-serif' size='2'>
				<b><?=data::datetodata($campos[$i]['data'], '/');?></b>
			</font>
		</td>
		<td colspan="3" align="right">
			<font face='Verdana, Arial, Helvetica, sans-serif' size='2'>
				<b><?=$moeda.number_format($campos[$i]['valor'], 2, ',', '.');?></b>
			</font>
		</td>
	</tr>
<?		
		}
	}
        
    /**********************************************************************/
    /****************************Livre de Débito***************************/
    /**********************************************************************/
    if($livre_debito == 'S') {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='7'>
            <font color='red' size='4'>
                <marquee behavior='alternate' direction='right'>
                    <b>NÃO PODE GERAR BOLETO OU DUPLICATA - LIVRE DE DÉBITO !!!</b>
                </marquee>
            </font>
        </td>
    </tr>
<?
    }
    /**********************************************************************/
?>
</table>
<?
//Aqui começa a segunda parte em q exibe os itens da Nota Fiscal de Entrada
    $sql = "SELECT * 
            FROM `nfe_historicos` 
            WHERE `id_nfe` = '$id_nfe' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table width='90%' border='1' cellspacing='0' cellpadding='0' onmouseover="total_linhas(this)" align='center'>
    <tr></tr>
    <tr></tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>Itens</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Qtde</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Un</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Produto</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Pre&ccedil;o Unit.</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Valor Total</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>IPI %</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>ICMS %</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Red %</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>IVA %</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Marca / Obs</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Peso Total do Item</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>N.º Ped / OS</b>
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
//Verifica se o PI é um PRAC ...
            $sql = "SELECT `id_produto_acabado`, `peso_unitario` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `id_produto_insumo` > '0' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            if(count($campos_pipa) == 1) $pipa = 1;//Variável utilizada mais abaixo ...
//Verifico se esta OS está atrelada em algum Pedido ...
            $sql = "SELECT `id_os` 
                    FROM `oss` 
                    WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' ";
            $campos_os = bancos::sql($sql);
            if(count($campos_os) == 1) {//Está importado p/ OS
                $id_os              = $campos_os[0]['id_os'];
                $tem_os_importada   = 1;
            }else {//Ainda não está importado p/ OS
                $tem_os_importada   = 0;
            }
?>
    <tr class='linhanormal' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
        <?
                $id_item_pedido = $campos[$i]['id_item_pedido'];
                if($campos[$i]['status'] == 0) {//Se o Item estiver em Aberto, exibe o Option ...
        ?>
                <input type='radio' name='opt_item' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')" value="<?=$campos[$i]['id_nfe_historico'];?>">
        <?
                }else {//Se o Item estiver Fechado
        ?>
                <input type='hidden' name='opt_item'>
        <?
//Só posso exibir esse X p/ os Gladys, Roberto, Fabio e Dárcio ...
                    if($_SESSION['id_funcionario'] == 14 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98) {
/*Se o Item da NF estiver fechado e a Nota Fiscal estiver em Aberto, então eu exibo esse X, 
p/ poder abrir o item novamente ...*/
                        if($situacao < 2 && $campos[$i]['status'] == 1) {
        ?>
                <img src = '../../../../../imagem/letra_x.jpeg' alt='Abrir Item de Nota Fiscal' title='Abrir Item de Nota Fiscal' style='cursor:help' border='0' onclick="window.location = 'itens.php?passo=2&id_nfe_historico=<?=$campos[$i]['id_nfe_historico'];?>'">
        <?
                        }else {
                            echo '&nbsp;';
                        }
                    }
                }
        ?>
        </td>
        <td align='right'>
        <?
            echo number_format($campos[$i]['qtde_entregue'], 2, ',', '.');
            $total_qtde+= $campos[$i]['qtde_entregue'];
        ?>
        </td>
        <td align='left'>
        <?
            //Busca dos Dados do PI ...
            $sql = "SELECT g.referencia, ip.*, pi.id_produto_insumo, pi.discriminacao, u.sigla  
                    FROM `itens_pedidos` ip 
                    INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
                    INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE ip.`id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
            $campos_pi = bancos::sql($sql);
            echo $campos_pi[0]['sigla'];
        ?>
        </td>
        <td align='left'>
        <?
//Se o Produto tiver débito com Fornecedor, então precisa aparecer link
            if($campos_pi[0]['id_fornecedor'] != 0) {
        ?>
                <a href="javascript:nova_janela('atrelar_nf_debito.php?id_nfe_historico=<?=$campos[$i]['id_nfe_historico'];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Atrelar Nota Fiscal' class='link'>
        <?
            }
/***********************************************************************/
            echo genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos_pi[0]['referencia']).' * ';
            echo $campos_pi[0]['discriminacao'];
//Impressão do Tipo de Ajuste na Tela ...
            if(!empty($campos[$i]['cod_tipo_ajuste'])) {//Certifico que ele não foi deletado, Luis ..
                echo ' - <b>'.$tipos_ajustes[$campos[$i]['cod_tipo_ajuste']][1];
//Se o Tipo de Ajuste = 'Abatimento de NF' então eu exibo o N.º da NF ...
                if($campos[$i]['cod_tipo_ajuste'] == 4) echo ' => '.$campos[$i]['nf_obs_abatimento'];
            }
//Aqui eu verifico qual que é o PA referente a esse PI devido, esse Pedido ser atrelado a uma OS
            if($tem_os_importada == 1) {
                $sql = "SELECT pa.id_produto_acabado, pa.referencia 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `oss_itens` oi ON oi.id_item_pedido = '$id_item_pedido' 
                        INNER JOIN `ops` ON ops.id_op = oi.id_op 
                        WHERE pa.`id_produto_acabado` = ops.id_produto_acabado ";
                $campos_os = bancos::sql($sql);	
//Produto Normal
                if($campos_os[0]['referencia'] != 'ESP') {
?>
                <font color="darkblue">
                        <?=' / '.intermodular::pa_discriminacao($campos_os[0]['id_produto_acabado']);?>
                </font>
<?
                }else {
//Quando o Produto Acabado for ESP printa em verde
?>
                    <?=' / '.intermodular::pa_discriminacao($campos_os[0]['id_produto_acabado']);?>
<?
                }
            }
?>            
            &nbsp;
            <a href="javascript:nova_janela('../../../estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'POP', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes da Última Compra' class='link'>
                <img src = '../../../../../imagem/visualizar_detalhes.png' title='Detalhes da Última Compra' alt='Detalhes da Última Compra' border='0'>
            </a>
<?
/**********************OP**********************/
//Verifico se esse Item está atrelado a alguma OP ...
            $sql = "SELECT id_op 
                    FROM `oss_itens` 
                    WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
            $campos_op = bancos::sql($sql);
            if(count($campos_op) == 1) {
?>
                / <b>OP N.º</b>
                <a href="javascript:nova_janela('../../../../producao/ops/alterar.php?passo=2&id_op=<?=$campos_op[0]['id_op'];?>&pop_up=1', 'DETALHES_OP', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de OP' class='link'>
                    <?=$campos_op[0]['id_op'];?>
                </a>
<?
            }
/**********************************************/
//Significa que é um Produto do Tipo não Estocável
            if($campos_pi[0]['estocar'] == 0) {
//Se eu não vou estocar, esse Produto, então significa que este vai para alguém, então busco p/ qual fornec
                if($campos_pi[0]['id_fornecedor_terceiro'] != 0) {
//Busca o nome do Fornecedor que deve ser cobrado
                    $sql = "SELECT razaosocial 
                            FROM `fornecedores` 
                            WHERE `id_fornecedor` = ".$campos_pi[0]['id_fornecedor_terceiro']." LIMIT 1 ";
                    $campos_fornecedor = bancos::sql($sql);
                }
                echo "<font color='red' title='Não Estocar - Enviar p/: ".$campos_fornecedor[0]['razaosocial']."' style='cursor:help'><b> (N.E) </b></font>";
            }
//Significa que esse Produto tem débito com Fornecedor
            if($campos_pi[0]['id_fornecedor'] != 0) {
//Busca o nome do Fornecedor que deve ser cobrado
                $sql = "SELECT razaosocial 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '".$campos_pi[0]['id_fornecedor']."' LIMIT 1 ";
                $campos_fornecedor  = bancos::sql($sql);
                $fornecedor         = $campos_fornecedor[0]['razaosocial'];
//Também busco o N.º da Nota Fiscal deste Fornecedor de qual vai ser cobrado
                $sql = "SELECT DISTINCT(nfe.num_nota) 
                        FROM `nfe_historicos` nh 
                        INNER JOIN `nfe` ON nfe.`id_nfe` = nh.`id_nfe_debitar` 
                        WHERE nh.`id_nfe_debitar` = '".$campos[$i]['id_nfe_debitar']."' ";
                $campos_nfe_debitar = bancos::sql($sql);
                if(count($campos_nfe_debitar) == 1) {//Se já estiver atrelado a outra Nota Fiscal ...
                    $num_nota_fiscal = $campos_nfe_debitar[0]['num_nota'];
                    echo "<font color='red' title='Debitar do(a): $fornecedor - Nota Fiscal N.º $num_nota_fiscal' style='cursor:help'><b> (DEB) </b></font>";
                }else {//Se ainda não estiver atrelada a nenhuma outra Nota, então ...
                    echo "<font color='red' title='Debitar do(a): $fornecedor - S/ N.º de Nota Fiscal atrelada' style='cursor:help'><b> (DEB) </b></font>";
                }
            }
        ?>
                </a>
        <?
                if($pipa == 1) {//Se o PI tem relação com o PA ...
        ?>
                &nbsp;
                <a href="javascript:nova_janela('../../../../vendas/estoque_acabado/detalhes.php?id_produto_acabado=<?=$campos_pipa[0]['id_produto_acabado'];?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class='link'>
                    <img src="../../../../../imagem/detalhes.png" title="Detalhes" alt="Detalhes" width='20' height='20' border='0'>
                </a>
        <?
                }
        ?>
        </td>
        <td align='right'>
            <?=$moeda.number_format($campos[$i]['valor_entregue'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $valor_item_rs = round(round($campos[$i]['qtde_entregue'] * $campos[$i]['valor_entregue'], 3), 2);
            echo $moeda.number_format($valor_item_rs, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['ipi_incluso'] == 'S') {
                echo '<font color="red" title="IPI Incluso de '.number_format($campos[$i]['ipi_entregue'], 2, ',', '.').' %" style="cursor:help"><b>(Incl)</b></font>';
            }else {
                if(($campos[$i]['ipi_entregue'] == '0.00') or ($tipo == 'SGD')) {//SGD
                    echo '&nbsp;';
                }else {//NF
                    //Cálculo do Valor do IPI ...
                    $ipi_item_rs = round(($valor_item_rs * $campos[$i]['ipi_entregue']) / 100, 2);
                    echo '<font title="Valor IPI Item = R$ '.number_format($ipi_item_rs, 2, ',', '.').'" style="cursor:help">'.number_format($campos[$i]['ipi_entregue'], 2, ',', '.').'</font>';
                }
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['icms_entregue'] == '0.00') {//Não existe ICMS ...
                echo '&nbsp;';
            }else {//Se existe então ...
                //Cálculo do Valor do ICMS, mas somente quando não existir IVA ...
                $icms_item_rs = ($campos[$i]['iva'] == 0) ?	round(($valor_item_rs * $campos[$i]['icms_entregue']) / 100, 2) : 0;
                echo '<font title="Valor ICMS Item = R$ '.number_format($icms_item_rs, 2, ',', '.').'" style="cursor:help">'.number_format($campos[$i]['icms_entregue'], 2, ',', '.').'</font>';
//Aqui eu verifico o Crédito de ICMS diretamente do PI ...
                $sql = "SELECT pi.credito_icms 
                        FROM `itens_pedidos` ip 
                        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                        WHERE ip.`id_item_pedido` = '$id_item_pedido' ";
                $campos_icms = bancos::sql($sql);
                if($campos_icms[0]['credito_icms'] == 0) echo '<font color="red" title="Sem Crédito ICMS" style="cursor:help"><b> (S.C)</b></font>';
            }
        ?>
        </td>
        <td>
        <?
            if(($campos[$i]['reducao'] == '0.00') or ($tipo == 'SGD')) {//SGD
                echo '&nbsp;';
            }else {//NF
                echo number_format($campos[$i]['reducao'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if(($campos[$i]['iva'] == '0.00') or ($tipo == 'SGD')) {//SGD
                echo '&nbsp;';
            }else {
                //Cálculo do Valor do ICMS ...
                if($campos[$i]['iva'] > 0 && $tipo == 'NF') {
                    $icms_item_rs = 0;//Tem que zerar essa variável quando existir IVA ...
//Se o PI for PRAC que é um PI de Revenda ou o PI for um Ajuste então existe cálculo p/ os campos de ST - Base ST e ICMS ST
                    if($pipa == 1 || $campos[$i]['id_produto_insumo'] == 1340) {//1340 é o id do PI que é Ajuste ...
                        //Não estamos levando em conta o Frete no cálculo abaixo, porque atualmente o Frete é um PI ...
                        $bc_icms_st = ($valor_item_rs + $ipi_item_rs) * (1 + $campos[$i]['iva'] / 100);
                        if($optante_simples_nacional == 'S') {
                            if(data::datatodate($data_emissao, '-') <= '2009-07-31') {
                                $abatimento_icms_st_para_simples = 7;//Só existe p/ Simples Nacional ...
                            }else {//A partir do mês de agosto mudou a Regra com Relação a esse Valor ...
                                $abatimento_icms_st_para_simples = $icms;//Só existe p/ Simples Nacional ...
                            }
                            $icms_st = $bc_icms_st * ($icms / 100) - ($abatimento_icms_st_para_simples / 100) * $valor_item_rs;	
                        }else {
                            /*Estamos mantendo o icms_item_rs na fórmula abaixo, pois caso o fornecedor seja uma indústria existirá esse ICMS, mesmo
                            que o item possua IVA, nos casos de Revenda o ICMS é Zerado ...*/
                            $icms_st = $bc_icms_st * ($icms / 100) - $icms_item_rs;
                        }
                    }else {//Não é PRAC e nem Ajuste, apenas PI ...
                        $icms_st = 0;
                    }
                    $icms_st = round($icms_st, 2);
                }
                echo '<font title="Valor ICMS ST Item = R$ '.number_format($icms_st, 2, ',', '.').'" style="cursor:help">'.number_format($campos[$i]['iva'], 2, ',', '.').'</font>';
            }
        ?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['marca'])) {
                echo $campos[$i]['marca'];
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($pipa == 1) {//Se o PI tem relação com o PA ...
                if($campos_pipa[0]['peso_unitario'] == 0) {//Peso igual Zero mostro em Vermelho ...
                    echo '<font color="red" title="Peso Unitário '.number_format($campos_pipa[0]['peso_unitario'], 4, ',', '.').'" style="cursor:help">0,0000</font>';
                }else {//Peso maior do que Zero mostro na cor normal ...
                    $peso_unitario_item = abs($campos_pipa[0]['peso_unitario'] * $campos[$i]['qtde_entregue']);//Sempre valor positivo porque temos casos em que a Nota Fiscal tem a sua Quantidade Negativa ...
                    echo '<font title="Peso Unitário '.number_format($campos_pipa[0]['peso_unitario'], 4, ',', '.').'" style="cursor:help">'.number_format($peso_unitario_item, 4, ',', '.').'</font>';

                    $peso_total_todos_itens+= $peso_unitario_item;
                }
            }
        ?>
        </td>
        <td>
<!--Aqui eu exibo os Detalhes de Pedido-->
                <a href="javascript:nova_janela('../../itens/itens.php?id_pedido=<?=$campos[$i]['id_pedido'];?>&pop_up=1', 'DETALHES', 'F')" alt='Detalhes do Pedido' title='Detalhes do Pedido' class='link'>
                <?
                    echo $campos[$i]['id_pedido'];
                    //Encontrou a OS em um Pedido, então eu printo o N. da OS e Ped
                    if($tem_os_importada == 1) {
                ?>
                    <a href="javascript:nova_janela('../../../../producao/os/itens/itens.php?id_os=<?=$id_os;?>&pop_up=1', 'DETALHES_OS', '', '', '', '', 560, 960, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de OS' class='link'>
                        <font color='red'>
                            <?=$id_os;?>
                        </font>
                    </a>
                <?
                    }
                ?>
                </a>
        </td>
    </tr>
<?
            $pipa = 0;//Zero essa variável p/ não herdar o valor do Loop anterior ...
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                FRETE QTD KGS P/ CÁLCULO TOTAL/FRETE ->
            </font>
        </td>
        <td colspan='5'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=number_format($total_qtde, 2, ',', '.');?> / KG-TOT
            </font>
        </td>
        <td align='right'>
        <?
            if($peso_total_todos_itens > 0) echo number_format($peso_total_todos_itens, 4, ',', '.');
        ?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
<?
//Aqui eu atualizo na tabela de NF os campos de IPI e ICMS ...
    $sql = "UPDATE `nfe` SET `total_ipi` = '".$calculo_total_impostos['valor_ipi']."', total_icms = '".$calculo_total_impostos['valor_icms']."', `valor_icms_oculto_creditar` = '".$calculo_total_impostos['valor_icms_oculto_creditar']."' WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
    bancos::sql($sql);
?>
<table width='90%' border="0" cellspacing='1' cellpadding='0' align='center'>
    <tr></tr>
    <tr></tr>
    <tr class='linhadestaque'>
        <td colspan='6'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>CÁLCULO DO IMPOSTO</font>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>BASE DE CÁLCULO DO ICMS: </font>
                <br/><?=$moeda.number_format(round(round($calculo_total_impostos['base_calculo_icms'], 3), 2), 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR DO ICMS: </font>
                <br/><?=$moeda.number_format(round(round($calculo_total_impostos['valor_icms'], 3), 2), 2, ',', '.');?>
            </font>
        </td>
        <td colspan='2'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>BASE DE CÁLC. DO ICMS SUBST.: </font>
                <br/><?=$moeda.number_format(round(round($calculo_total_impostos['base_calculo_icms_st'], 3), 2), 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR DO ICMS SUBSTITUIÇÃO: </font>
                <br/><?=$moeda.number_format(round(round($calculo_total_impostos['valor_icms_st'], 3), 2), 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR TOTAL DOS PRODUTOS: </font>
                <br/><?=$moeda.number_format(round(round($calculo_total_impostos['valor_total_produtos'], 3), 2), 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR DO FRETE: </font>
                <br/>R$ 0,00
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR DO SEGURO: </font>
                <br/>R$ 0,00
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>DESCONTO: </font>
                <br/>R$ <?=number_format(abs($calculo_total_impostos['desconto']), 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>OUTRAS DESPESAS ACESSÓRIAS: </font>
                <br/>R$ 0,00
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR DO IPI: </font>
                <br/><?=$moeda.number_format(round(round($calculo_total_impostos['valor_ipi'], 3), 2), 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><font color='yellow'>VALOR TOTAL DA NOTA: </font>
                <br/><?=$moeda.number_format(round(round($calculo_total_impostos['valor_total_nota'], 3), 2), 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
    if($calculo_total_impostos['valor_icms_oculto_creditar'] > '0' || $calculo_total_impostos['valor_ipi_incluso'] > '0') {
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            <?
                if($calculo_total_impostos['valor_icms_oculto_creditar'] > '0') {
            ?>
            <b><font color='red' size='4'>TOTAL ICMS OCULTO À CREDITAR: <?=$moeda.number_format(round(round($calculo_total_impostos['valor_icms_oculto_creditar'], 3), 2), 2, ',', '.');?></font></b>
            <br/>
            <?
                }
            ?>
        </td>
        <td colspan='2'>
            <?
                if($calculo_total_impostos['valor_ipi_incluso'] > '0') {
            ?>
            <b><font color='red' size='4'>TOTAL IPI INCLUSO À CREDITAR: <?=$moeda.number_format(round(round($calculo_total_impostos['valor_ipi_incluso'], 3), 2), 2, ',', '.');?></font></b>
            <br/>
            <?
                }
            ?>
        </td>
    </tr>
<?
    }
?>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='6'>
            <iframe name='detalhes' id='detalhes' src = '/erp/albafer/modulo/classes/follow_ups/detalhes.php?identificacao=<?=$id_nfe;?>&origem=17' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
</table>
<?
        if(!empty($valor)) {
?>
            <Script Language = 'Javascript'>
                alert('<?=$mensagem[$valor];?>')
            </Script>
<?
        }
    }else {
?>
<table width='90%' border='0' align='center'>
    <tr class='atencao' align='center'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='#FF0000'>
                Nota
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='blue'>
                    <b><?=$num_nota;?></b>
                </font>
                n&atilde;o cont&eacute;m itens cadastrados.
            </font>
        </td>
    </tr>
</table>
<?
    }

    if($nao_redeclarar != 1) {//Às vezes essa tela é requirida dentro dela mesmo, e por isso tem esse desvio ...
?>
<!--Não me lembro desses hidden aki (rsrs)-->
<input type='hidden' name='opt_item_principal'>
<!-- ******************************************** -->
<!--Aqui no hidden eu sempre guardo o id_nfe da Primeira NF de Entrada que é a Principal p/ não sobrepor 
com o id_nfe logo abaixo casos esse exista que aí dá caca se eu guardar aqui ...-->
<input type='hidden' name='id_nfe' value='<?=$id_nfe;?>'>
<?
    }
/*****************************************************************************************/
/*Aqui eu faço a listagem dos Itens que foram atrelados a essa Nota, através de outra Nota 
como valores que tem que ser debitados do fornecedor - "Nota Fiscal Debitar"*/
    $sql = "SELECT `id_nfe` 
            FROM `nfe_historicos` 
            WHERE `id_nfe_debitar` = '$id_nfe' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {
?>
<pre>

* Listagem de Item(ns) que foram atrelados a essa Nota, mas por meio de outra(s) Nota(s)
* Para excluir um item atrelado errado, vá a NF de origem e exclua e inclua o item novamente

<font face='arial' color='red' size='3'><b><center>ITENS P/ DEBITAR DESTA NOTA FISCAL</center></b></font>
</pre>
<iframe src='itens.php?id_nfe=<?=$campos[0]['id_nfe'];?>&pop_up=1' marginwidth='0' marginheight='0' frameborder='0' height='580' width='100%' scrolling='auto'></iframe>
<?
    }
?>
<!--*********************************************************************************************-->
<!--Para perguntar se deseja inserir as Antecipações, mas só na primeira vez em que cair nessa tela-->
<?
    if(empty($perguntar_uma_vez)) $perguntar_uma_vez = 0;//Macete (rsrs)
?>
<input type='hidden' name='perguntar_uma_vez' value="<?=$perguntar_uma_vez;?>">
</form>
</body>
</html>
<?
}

//Somente na Primeira vez em que carregar essa Tela
/****************Aqui dispara a função automaticamente****************/
/*Se a Nota Fiscal ainda estiver com a Situação de em aberto, então ele faz uma verificação de todas 
as antecipações que já estão liberadas do Fornecedor dessa Nota e que ainda não foram importadas para esta, 
caso exista 1 pelo menos, então terá essa pergunta + abaixo para o usuário perguntando se ele 
deseja visualizar as antecipações da Nota Fiscal, se sim abrirá o Pop-UP p/ o usuário 
importar a Antecipação*/
if(contador_antecipacao_aberto($id_fornecedor, $situacao, $tipo_nf) > 0) {
?>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
//Só vai fazer essa pergunta, na primeira em que carregar essa tela
    if(parent.itens.document.form.perguntar_uma_vez.value == 0) {
        valor = confirm('EXISTEM ANTECIPAÇÕES PENDENTES PARA ESSE FORNECEDOR ! DESEJA VISUALIZAR ?')
        if(valor == true) {
            nova_janela('incluir_antecipacao.php?id_nfe=<?=$id_nfe;?>', 'POP_UP', '', '', '', '', 600, 1000, 'c', 'c')
        }
//Para não perguntar + nenhuma vez
        parent.itens.document.form.perguntar_uma_vez.value = 1
        parent.itens.document.form.submit()
    }
</Script>
<?}?>