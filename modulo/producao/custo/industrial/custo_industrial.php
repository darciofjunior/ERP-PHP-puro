<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/producao.php');

if(empty($pop_up)) {//Se esse arquivo foi acessado do Custo de Revenda, não puxa o Menu ...
    require('../../../../lib/menu/menu.php');
    segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');
}else {
    session_start('funcionarios');
}

//Função q verifica se os produtos insumos é de valor 0 no estoque
function estoque_insumo_zero($id_produto_insumo) {
    $sql = "SELECT `qtde` 
            FROM `estoques_insumos` 
            WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
    $campos = bancos::sql($sql);
    return $campos[0]['qtde'];
}

$mensagem[1] = "<font class='confirmacao'>CUSTO INDUSTRIAL ATUALIZADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CUSTO INDUSTRIAL NÃO PODE SER LIBERADO.</font>";
$mensagem[3] = "<font class='atencao'>ESTE ITEM NÃO PODE SER EXCLUÍDO ! DEVIDO EXISTIR ITEM(NS) NA 5ª ETAPA DEPENDENTE(S) DESSE ITEM.</font>";
$mensagem[4] = "<font class='confirmacao'>CUSTO INDUSTRIAL ATUALIZADO COM SUCESSO.<font class='erro'><br/>É NECESSÁRIO MARCAR NA 1ª ETAPA ALGUMA EMBALAGEM COMO SENDO PRINCIPAL.</font></font>";

/******************************************************************************/
/****************************Gerando um Custo do PA****************************/
/******************************************************************************/
//Assim que entra na tela já busco a OC do $id_produto_acabado que foi passado por parâmetro e estes servirão para todo o restante da Tela ...
if(!empty($id_produto_acabado)) {
    $sql = "SELECT `operacao_custo` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos_oc  = bancos::sql($sql);
    //Verifico se já existe um Custo para o $id_produto_acabado ...
    $sql = "SELECT `id_produto_acabado_custo` 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' 
            AND `operacao_custo` = '".$campos_oc[0]['operacao_custo']."' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        //Custo status 0 = Industrial ...
        //Custo status 1 = Industrial que está dentro de um Revenda ...
        $sql = "INSERT INTO `produtos_acabados_custos` (`id_produto_acabado`, `qtde_lote`, `comprimento_2`, `operacao_custo`, `data_sys`) values ('$id_produto_acabado', '1', '6.0', '".$campos_oc[0]['operacao_custo']."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $id_produto_acabado_custo = bancos::id_registro();
    }else {
        $id_produto_acabado_custo = $campos[0]['id_produto_acabado_custo'];
    }
/******************************************************************************/
}

if($passo == 1) {//Exclusões de Etapas e por aí vai ...
    $valor = 1;//A princípio o sistema foi preparado para que retornar essa Frase ...

//Exclui os itens da etapa conforme o valor passado por parâmetro
    if(!empty($_POST['hdd_numero_etapa'])) {
        if($_POST['hdd_numero_etapa'] == 1) {//Etapa 1
//Busca do Produto Insumo atual antes da Exclusão ...
            $sql = "SELECT `id_produto_insumo` 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_pa_pi_emb` = '$_POST[id_pa_pi_emb_item]' LIMIT 1 ";
            $campos_pi                  = bancos::sql($sql);
            $id_produto_insumo_etapa1   = $campos_pi[0]['id_produto_insumo'];//Produto Insumo Atual ...
//Se houve exclusão, então eu chamo a Função ...
            producao::verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado, $id_produto_insumo_etapa1, 0, 1);
            
            $sql = "DELETE FROM `pas_vs_pis_embs` WHERE `id_pa_pi_emb` = '$_POST[id_pa_pi_emb_item]' LIMIT 1 ";
            bancos::sql($sql);
            
//Verifico se ainda restou(ram) Embalagem(ns) atrelada(s) nessa 1ª Etapa do Custo p/ esse PA ...
            $sql = "SELECT `id_pa_pi_emb`, `embalagem_default` 
                    FROM `pas_vs_pis_embs` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' ";
            $campos_embalagens = bancos::sql($sql);
            $linhas_embalagens = count($campos_embalagens);
            if($linhas_embalagens > 0) {//Restou pelo menos 1 Embalagem atrelada p/ esse PA do Custo ...
                $embalagem_default = 0;//Valor Inicial ...
                //Verifico se das Embalagens restantes, alguma ainda é a Principal ...
                for($i = 0; $i < $linhas_embalagens; $i++) {
                    if($campos_embalagens[$i]['embalagem_default'] == 1) {
                        $embalagem_default = 1;
                        break;//P/ sair fora do Loop, afinal apenas uma Embalagem Default é o que me interessa ...
                    }
                }
                //Se das Embalagem que restaram nenhuma for Default, então dou um Alert informando o Usuário ...
                if($embalagem_default == 0) $valor = 4;//Significa que é necessário marcar alguma Embalagem como Principal ...
            }
        }else if($_POST['hdd_numero_etapa'] == 2) {
/*Antes de excluir o Custo na Etapa 2, eu verifico se existe algum Peso de Aço da Etapa 5, que está sem a marcação no 
checkbox, caso exista, não posso estar excluindo devido esses Itens da Etapa 5 depender dos que estão na Etapa 2 ...*/
            $sql = "SELECT `id_produto_acabado_custo` 
                    FROM `pacs_vs_pis_trat` 
                    WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' 
                    AND `peso_aco_manual` = '0' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Não existem itens ...
                //Busca do Produto Insumo atual antes da Exclusão ...
                $sql = "SELECT `id_produto_insumo` 
                        FROM `produtos_acabados_custos` 
                        WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
                $campos_pi                  = bancos::sql($sql);
                $id_produto_insumo_etapa2   = $campos_pi[0]['id_produto_insumo'];//Produto Insumo Atual ...
//Se houve exclusão, então eu chamo a Função ...
                producao::verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado, $id_produto_insumo_etapa2, 0, 2);
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
                $hdd_produto_insumo_etapa2 = (!empty($_POST[hdd_produto_insumo])) ? "'".$_POST[hdd_produto_insumo]."'" : 'NULL';

//Etapa 2 - Exclui o Produto dessa Etapa ...
                $sql = "UPDATE `produtos_acabados_custos` SET `id_produto_insumo` = $hdd_produto_insumo_etapa2, `qtde_lote` = '0', `peso_kg` = '0.0', `peca_corte`= '0', `comprimento_1`= '0', `comprimento_2` = '0', `observacao` = '' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
                bancos::sql($sql);
//Exclui o Peso Aço da Etapa 5, mas somente quando o peso estiver no modo manual = 0
                $sql = "UPDATE `pacs_vs_pis_trat` SET `peso_aco` = '0.0000' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' AND `peso_aco_manual` = '0' ";
                bancos::sql($sql);
            }else {//Ainda existe pelo menos 1 item da 5ª Etapa que depende de algum da 2ª ...
                $valor = 3;
            }
        }else if($_POST['hdd_numero_etapa'] == 3) {//Etapa 3
//Busca do Produto Insumo atual antes da Exclusão ...
            $sql = "SELECT `id_produto_insumo` 
                    FROM `pacs_vs_pis` 
                    WHERE `id_pac_pi` = '$_POST[id_pac_pi_item]' LIMIT 1 ";
            $campos_pi                  = bancos::sql($sql);
            $id_produto_insumo_etapa3   = $campos_pi[0]['id_produto_insumo'];//Produto Insumo Atual ...
//Se houve exclusão, então eu chamo a Função ...
            producao::verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado, $id_produto_insumo_etapa3, 0, 3);
            $sql = "DELETE FROM `pacs_vs_pis` WHERE `id_pac_pi` = '$_POST[id_pac_pi_item]' LIMIT 1 ";
            bancos::sql($sql);
        }else if($_POST['hdd_numero_etapa'] == 4) {//Etapa 4
            $sql = "DELETE FROM `pacs_vs_maquinas` WHERE `id_pac_maquina` = '$_POST[id_pac_maquina_item]' LIMIT 1 ";
            bancos::sql($sql);
        }else if($_POST['hdd_numero_etapa'] == 5) {//Etapa 5
            $sql = "DELETE FROM `pacs_vs_pis_trat` WHERE `id_pac_pi_trat` = '$_POST[id_pac_pi_trat_item]' LIMIT 1 ";
            bancos::sql($sql);
        }else if($_POST['hdd_numero_etapa'] == 6) {//Etapa 6
            $sql = "DELETE FROM `pacs_vs_pis_usis` WHERE `id_pac_pi_usi` = '$_POST[id_pac_pi_usi_item]' LIMIT 1 ";
            bancos::sql($sql);
        }else if($_POST['hdd_numero_etapa'] == 7) {//Etapa 7
            $sql = "DELETE FROM `pacs_vs_pas` WHERE `id_pac_pa` = '$_POST[id_pac_pa_item]' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    /*******************************************************************************************/
    //Busca de alguns dados de Custo através do "$id_produto_acabado_custo" p/ fazer alguns controles mais abaixo ...
    $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`operacao_custo`, pa.`status_custo`, 
            pac.`lote_minimo`, pac.`operacao_custo` 
            FROM `produtos_acabados_custos` pac 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
            WHERE pac.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
    $campos = bancos::sql($sql);
    /*******************************************************************************************/
    /****************************Controle com o Lote Mínimo do Custo****************************/
    /*******************************************************************************************/
    $lote_minimo_ignora_faixa_orcavel   = (!empty($_POST['chkt_lote_minimo'])) ? 'S' : 'N';
    if($campos[0]['lote_minimo'] != $lote_minimo_ignora_faixa_orcavel) {
        //Se houve mudança nesse Lote então registro a Data e Hora do Funcionário que fez essa alteração no Custo ...
        $sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."', `lote_minimo` = '$lote_minimo_ignora_faixa_orcavel' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
        bancos::sql($sql);
    }
    /*******************************************************************************************/
    //Atualização do custo liberado para o produto acabado
    if(!empty($chkt_custo_liberado)) {//Deseja liberar o Custo ...
        $acao = 'SIM';
        //Antes de cair na função que já faz tudo automático, tem uma condição antes só para o caso o PA ser 'ESP'
        if($campos[0]['referencia'] == 'ESP') {
            /*Listagem de Todos os Orçamento(s) que estão em Aberto, q não estão congelados, que contém esse Item 
            em que o prazo de Entrega seja igual a Imediato*/
            $sql = "SELECT ovi.`id_orcamento_venda_item` 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                    WHERE ovi.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' 
                    AND ovi.`prazo_entrega_tecnico` = '0.0' 
                    AND ov.`congelar` = 'N' LIMIT 1 ";
            $campos_orcamento = bancos::sql($sql);
            //Se encontrar algum item que tenha o prazo de entrega técnico como zerado, então não pode liberar o custo
            if(count($campos_orcamento) == 1) $acao = 'NAO';
        }
    }else {//Não selecionado - desejar bloquear o Custo ...
        $acao = 'NAO';
    }
    custos::liberar_desliberar_custo($id_produto_acabado_custo, $acao);

    /*A função "custos::liberar_desliberar_custo" pode bloquear o Custo desse PA principal se a mesma tiver algum item 
    na 7ª Etapa que esteja Bloqueado, por isso que faço um Novo SQL, p/ verificar se esse Custo realmente foi Liberado 
    caso o usuário teve a Intenção de Liberá-lo ...*/
    if($acao == 'SIM') {
        $sql = "SELECT `status_custo` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
        $campos_status = bancos::sql($sql);
        if($campos_status[0]['status_custo'] == 0) $acao = 'NAO';//Significa que o Custo desse PA Principal permaneceu bloqueado ...
    }
    //Aqui eu mudo o status desse P.A. q foi migrado, p/ 0, p/ dizer q este já foi atualizado
    $sql = "UPDATE `produtos_acabados` SET `pa_migrado` = '0' WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
    bancos::sql($sql);
    //Aqui é só para retornar as mensagens
    if($valor < 3) {
        if(!empty($chkt_custo_liberado)) {//Selecionado p/ liberar o Custo
            if($campos[0]['status_custo'] == 1) {//Significa está liberado
                $valor = 1;//Retorno da Frase
            }else {//Não está liberado
                $valor = ($acao == 'NAO') ? 2 : 1;
            }
        }
    }
    $url_remetente = $_SERVER['REQUEST_URI'];//Equivale a URL que está na Barra de Endereços do Navegador ...
    custos::atualizar_custos_orcs_descongelados($campos[0]['id_produto_acabado'], $url_remetente, $valor);
}else {
    $fator_custo_2                  = genericas::variavel(11);//Busca de um valor para fator custo para etapa 2
    //Essa variável vai estar sendo acionada para o caso de o usuário digitar na qtde um valor maior do que 1000 ...
    $fator_custo_2_new              = genericas::variavel(18);
    $fator_custo_1_3_7              = genericas::variavel(12);//Busca de um valor para fator custo para etapa 1, 3 e 7
    $fator_custo_4                  = genericas::variavel(9);//Busca de um valor para fator custo para etapa 4
    $fator_custo_5_6                = genericas::variavel(10);//Busca de um valor para fator custo para etapa 5 e 6
    $fator_desconto_maximo_vendas   = genericas::variavel(19);

    $sql = "SELECT gpa.`id_familia`, pa.`id_produto_acabado`, pa.`id_produto_insumo`, pa.`id_gpa_vs_emp_div`, pa.`referencia`, 
            pa.`discriminacao`, pa.`preco_unitario`, pa.`preco_export`, pa.`operacao_custo`, 
            pa.`operacao_custo_sub`, pa.`desenho_para_op`, pa.`observacao` AS observacao_produto, 
            pac.`operacao_custo`, pa.`status_custo`, pac.`id_funcionario` AS id_funcionario_alterou_custo, 
            pac.`lote_minimo`, CONCAT(DATE_FORMAT(SUBSTRING(pac.`data_sys`, 1, 10), '%d/%m/%Y'), SUBSTRING(pac.`data_sys`, 11, 9)) AS data_atualizacao 
            FROM `produtos_acabados_custos` pac 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pac.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $id_familia             = $campos[0]['id_familia'];
    $id_produto_acabado     = $campos[0]['id_produto_acabado'];
    $id_produto_insumo      = $campos[0]['id_produto_insumo'];//Pode ser que o PA seja um PIPA ...
    $id_gpa_vs_emp_div      = $campos[0]['id_gpa_vs_emp_div'];
    $referencia             = $campos[0]['referencia'];
    $desenho_para_op        = $campos[0]['desenho_para_op'];
    $observacao_produto     = trim($campos[0]['observacao_produto']);
    $operacao_custo         = $campos[0]['operacao_custo'];
    
    if($operacao_custo == 0) {//Industrialização
        $operacao_custo_rotulo = 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
        if($campos[0]['operacao_custo_sub'] == 0) {
            $operacao_custo_rotulo.= '-I';
        }else if($campos[0]['operacao_custo_sub'] == 1) {
            $operacao_custo_rotulo.= '-R';
        }
    }else {//Revenda
        $operacao_custo_rotulo = 'R';
    }
    $id_funcionario_alterou_custo       = $campos[0]['id_funcionario_alterou_custo'];
    $lote_minimo_ignora_faixa_orcavel 	= $campos[0]['lote_minimo'];
    $data_atualizacao                   = $campos[0]['data_atualizacao'];
    $status_custo                       = $campos[0]['status_custo'];
    //Essa variável estará sendo utilizada no meio das etapas 2 e 3
    $preco_custo_zero                   = 0;
?>
<html>
<head>
<title>.:: Custo Industrial ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function bloquear_custo() {
    var mensagem = confirm('DESEJA BLOQUEAR O CUSTO ?')
    if(mensagem == false) {
        document.form.chkt_custo_liberado.checked = true
    }else {
        document.form.chkt_custo_liberado.checked = false
        document.form.passo.value = 1
        document.form.submit()
    }
}
    
function incluir_item_da_etapa(numero_etapa) {
    var status_custo = '<?=$status_custo;?>'
    if(status_custo == 1) {//Se o Custo está liberado, não é possível ser incluído mais nada em nenhuma Etapa ...
        return bloquear_custo()
    }else {//Só é possível estar incluindo algum Item em alguma Etapa quando o Custo estiver Bloqueado ...
        if(numero_etapa == 1) {
            html5Lightbox.showLightbox(7, 'incluir_embalagem.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')
        }else if(numero_etapa == 2) {
            html5Lightbox.showLightbox(7, 'consultar_produto_insumo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')
        }else if(numero_etapa == 3) {
            html5Lightbox.showLightbox(7, 'incluir_produto_insumo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')
        }else if(numero_etapa == 4) {
            html5Lightbox.showLightbox(7, 'incluir_maquina.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')
        }else if(numero_etapa == 5) {
            html5Lightbox.showLightbox(7, 'incluir_tratamento_termico.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')
        }else if(numero_etapa == 6) {
            html5Lightbox.showLightbox(7, 'incluir_usinagem.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')
        }else if(numero_etapa == 7) {
            html5Lightbox.showLightbox(7, 'incluir_produto_acabado.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')
        }
    }
}
    
function alterar_item_da_etapa(posicao, numero_etapa) {
    /*A etapa 2 é a única na qual posso mexer mesmo com o Custo já Liberado ...

    Obs: Mas se o Custo estiver liberado, só é possível trocar o aço para fazer Simulações porque 
    o botão Salvar dessa Etapa que guarda as alterações sempre estará desabilitado nessa situação ...*/
    if(numero_etapa == 2) {
        html5Lightbox.showLightbox(7, 'alterar_etapa2.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')
    }else {//Outras Etapas ...
        var status_custo = '<?=$status_custo;?>'
        if(status_custo == 1) {//Se o Custo está liberado, não é possível ser alterado mais nada em nenhuma Etapa ...
            return bloquear_custo()
        }else {//Só é possível estar alterando algum Item em alguma Etapa quando o Custo estiver Bloqueado ...
            if(numero_etapa == 1) {
                html5Lightbox.showLightbox(7, 'alterar_etapa1.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao='+posicao)
            }else if(numero_etapa == 3) {
                html5Lightbox.showLightbox(7, 'alterar_etapa3.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao='+posicao)
            }else if(numero_etapa == 4) {
                html5Lightbox.showLightbox(7, 'alterar_etapa4.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao='+posicao)
            }else if(numero_etapa == 5) {
                html5Lightbox.showLightbox(7, 'alterar_etapa5.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao='+posicao)
            }else if(numero_etapa == 6) {
                html5Lightbox.showLightbox(7, 'alterar_etapa6.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao='+posicao)
            }else if(numero_etapa == 7) {
                html5Lightbox.showLightbox(7, 'alterar_etapa7.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&posicao='+posicao)
            }
        }
    }
}

function excluir_item_da_etapa(id, numero_etapa) {
    var status_custo = '<?=$status_custo;?>'

    if(status_custo == 1) {//Se o Custo está liberado, não é possível ser excluído mais nada em nenhuma Etapa ...
        return bloquear_custo()
    }else {//Só é possível estar excluindo algum Item em alguma Etapa quando o Custo estiver Bloqueado ...
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            if(numero_etapa == 1) {
                document.form.id_pa_pi_emb_item.value = id
                document.form.hdd_numero_etapa.value = 1
            }else if(numero_etapa == 2) {
                document.form.hdd_produto_insumo.value = ''
                document.form.hdd_numero_etapa.value = 2
            }else if(numero_etapa == 3) {
                document.form.id_pac_pi_item.value = id
                document.form.hdd_numero_etapa.value = 3
            }else if(numero_etapa == 4) {
                document.form.id_pac_maquina_item.value = id
                document.form.hdd_numero_etapa.value = 4
            }else if(numero_etapa == 5) {
                document.form.id_pac_pi_trat_item.value = id
                document.form.hdd_numero_etapa.value = 5
            }else if(numero_etapa == 6) {
                document.form.id_pac_pi_usi_item.value = id
                document.form.hdd_numero_etapa.value = 6
            }else if(numero_etapa == 7) {
                document.form.id_pac_pa_item.value = id
                document.form.hdd_numero_etapa.value = 7
            }
            document.form.passo.value = 1
            document.form.submit()
        }
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    if(typeof(window.opener.document.form.resposta) == 'object') {
        window.opener.document.form.resposta.value = false
//Aqui é para não dar Update na tela de baixo e gravar o valor das caixas abaixo
        window.opener.document.form.ignorar_update.value = 1
        window.opener.document.form.submit()
    }
    window.close()
}

function alterar_produto_acabado(id_produto_acabado) {
//Pop-UP 1 - significa que esta tela está sendo aberta como Pop-UP ...
    nova_janela('../../cadastros/produto_acabado/alterar.php?passo=1&id_produto_acabado='+id_produto_acabado+'&pop_up=1', 'CONSULTAR', '', '', '', '', '450', '780', 'c', 'c', '', '', 's', 's', '', '', '')
}

function confirmar_lote_minimo() {
    var status_custo = '<?=$status_custo;?>'
    if(status_custo == 1) {//Se o Custo está liberado, não é possível ser alterado mais nada em nenhuma Etapa ...
        bloquear_custo()
        if(document.form.chkt_custo_liberado.checked == true) {//Significa que o usuário ainda desejou manter o Custo Liberado ...
            //Aqui o sistema anula o click do Usuário nesse Checkbox, voltando p/ a situação Inicial ...
            document.form.chkt_lote_minimo.checked = (document.form.chkt_lote_minimo.checked) ? false : true
        }
    }else {//Só é possível estar alterando algum Item em alguma Etapa quando o Custo estiver Bloqueado ...
        if(document.form.chkt_lote_minimo.checked) {//Se checou ...
            var resposta = confirm('TEM CERTEZA DE QUE DESEJA MARCAR LOTE MÍNIMO PARA ESSE CUSTO ?')
            if(resposta == true) {
                document.form.passo.value = 1
                document.form.submit()
            }else {
                document.form.chkt_lote_minimo.checked = false
            }
        }else {//Se deschecou ...
            document.form.passo.value = 1
            document.form.submit()
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<!--*************Hiddens que servem p/ controle das Etapas do Custo*************-->
<input type='hidden' name='id_pa_pi_emb_item'>
<input type='hidden' name='id_pac_pi_item'>
<input type='hidden' name='id_pac_maquina_item'>
<input type='hidden' name='id_pac_pi_trat_item'>
<input type='hidden' name='id_pac_pi_usi_item'>
<input type='hidden' name='id_pac_pa_item'>
<input type='hidden' name='hdd_numero_etapa'>
<input type='hidden' name='status_custo' value='<?=$status_custo;?>'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<!--****************************************************************************-->
<?
//Vai entrar aqui somente na primeira em que carregar a tela ...
if(empty($parametro_velho)) {
    $parametro_velho = $parametro;
}else {//Demais vezes
    $parametro_velho = $parametro_velho;
}
?>
<input type='hidden' name='parametro_velho' value='<?=$parametro_velho;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='8'> 
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <font color='#00FF00' size='2'>
                <b>CUSTO INDUSTRIAL</b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='left'>
        <td colspan='8'>
            <font color='yellow'>Produto: </font>
            <?
                //Aqui verifico a qual família que pertence esse PA ...
                $sql = "SELECT ed.`razaosocial`, gpa.`id_familia`, gpa.`nome` 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                        WHERE ged.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' LIMIT 1 ";
                $campos_dados_gerais = bancos::sql($sql);
//Se a família desse PA, for pertencente a família de Componentes, então mostra outro caminho ...
                if($campos_dados_gerais[0]['id_familia'] == 23) {
                    $url_prazo_entrega_do_pa = '../../../classes/producao/alterar_prazo_entrega_normal.php?';
                }else {
                    if($referencia == 'ESP') {//Se for Especial
                        $url_prazo_entrega_do_pa = '../../../classes/producao/alterar_prazo_entrega_esp.php?';
                    }else {
                        $url_prazo_entrega_do_pa = '../../../classes/producao/alterar_prazo_entrega_normal.php?';
                    }
                }
                echo ' * '.intermodular::pa_discriminacao($id_produto_acabado);
            ?>
            &nbsp;
            <img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Produto Acabado" alt="Alterar Produto Acabado" onClick="alterar_produto_acabado('<?=$id_produto_acabado;?>')">
            &nbsp;
            <a href="javascript:nova_janela('../../../vendas/relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Pedidos - Últimos 6 meses' class='link'>
                <img src='../../../../imagem/visualizar_detalhes.png' title='Visualizar Pedidos - Últimos 6 meses' alt='Visualizar Pedidos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../../../vendas/relatorio/orcamentos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&sumir_botao=1', 'VISUALIZAR_ORCAMENTOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Orçamentos - Últimos 6 meses' class='link'>
                <img src='../../../../imagem/propriedades.png' title='Visualizar Orçamentos - Últimos 6 meses' alt='Visualizar Orçamentos - Últimos 6 meses' border='0'>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'VISUALIZAR_ESTOQUE', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
                <font color='yellow'>
                    Visualizar Estoque
                </font>
            </a>
            &nbsp;-
            <font color='yellow' title='Operação de Custo' style='cursor:help'>
                O.C.:
            </font>
            <?=$operacao_custo_rotulo;?>
            &nbsp;
            <img src = '../../../../imagem/carrinho_compras.png' border='0' title='Compra + Produção' alt='Compra + Produção' width='25' height='16' onclick="html5Lightbox.showLightbox(7, '../../../classes/producao/visualizar_compra_producao.php?id_produto_acabado=<?=$id_produto_acabado;?>')">
            <?
                if(!empty($id_produto_insumo)) {
            ?>
            &nbsp;
            <a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$id_produto_insumo;?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Compras' class='link'>
                <font color='red' title='Compra' style='cursor:help'>
                    <b>(Compras)</b>
                </font>
            </a>
            <?
                }
                $url = '../../../vendas/estoque_acabado/manipular_estoque/consultar.php?passo=1';
                /*Mudança feita em 17/05/2016 - Antigamente os detalhes da consulta só eram feitos pela 
                referência independente de ser normal de Linha, eu supus que fosse assim porque temos PA(s) 
                que são similares em seu cadastro na parte de referência, por exemplo ML: 
                ML-001, ML-001A, ML-001AS, ML-001D, ML-001S, ML-001T, ML-001U, mas para ESP fica inviável 
                vindo todos os ESP´s do Sistema e trazendo informações que não tinham nada haver ...*/
                if($referencia == 'ESP') {//Aqui quero ver detalhes do PA ESP em específico ...
                    $url.= '&id_produto_acabado='.$id_produto_acabado.'&pop_up=1';
                }else {//PA normal de Linha, quero ver detalhes de todos os PA(s) semelhantes a este da Referência ...
                    $url.= '&txt_referencia='.$referencia.'&pop_up=1';
                }
            ?>
            &nbsp;
            <img src = '../../../../imagem/baixas_manipulacoes.png' border='0' title='Baixas / Manipulações' alt='Baixas / Manipulações' width='22' height='20' onclick="html5Lightbox.showLightbox(7, '<?=$url;?>')">
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='8'>
            <font color='yellow'>
                Grupo PA:
            </font>
            <?=$campos_dados_gerais[0]['nome'];?>
            &nbsp;-&nbsp;
            <font color='yellow'>
                Empresa Divisão:
            </font>
            <?=$campos_dados_gerais[0]['razaosocial'];?>
        </td>
    </tr>
<?
//Se for Especial exibe para Alterar o Prazo Técnico
	if($referencia == 'ESP') {
?>
	<tr><td></td></tr>
	<tr class='iframe' onClick="showHide('alterar_prazo_entrega_tecnico'); return false" style='cursor:pointer' height='22'>
            <td colspan='8'>
                Alterar Prazo de Entrega Sugerido pelo Depto. Técnico
                <span id='statusalterar_prazo_entrega_tecnico'>&nbsp;</span>
                <span id='statusalterar_prazo_entrega_tecnico'>&nbsp;</span>
            </td>
	</tr>
	<tr>
            <td colspan='8'>
<!--Eu passo a origem por parâmetro também para não dar erro de URL na parte de detalhes da conta e de cheque-->
                <iframe src="<?=$url_prazo_entrega_do_pa.'id_produto_acabado='.$id_produto_acabado;?>" name='alterar_prazo_entrega_tecnico' id='alterar_prazo_entrega_tecnico' marginwidth='0' marginheight='0' style='display: none' frameborder='0' height='185' width='100%' scrolling='auto'></iframe>
            </td>
	</tr>
<?
	}
//Significa que já teve alguma alteração do usuário em relação a esse custo
	if($id_funcionario_alterou_custo != 0) {
            $sql = "SELECT `nome` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '$id_funcionario_alterou_custo' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='8'>
            <font color='#FFFF00'>
                Última alteração realizada por:
            </font>
            <?=$campos_funcionario[0]['nome'];?>
            &nbsp;-&nbsp; 
            <font color='#FFFF00'>
                Data e Hora de Atualização:
            </font>
            <?=$data_atualizacao;?>
        </td>
    </tr>
<?
	}
?>
    <tr>
        <td colspan='8' bgcolor='#CCCCCC'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b>Lista de Preço de Venda: </b>
                &nbsp;&nbsp;&nbsp;
                <font color='darkblue'>
                    <b>Nacional:</b>
                </font>
                R$ <?=number_format($campos[0]['preco_unitario'], 2, ',', '.')?>
                &nbsp;&nbsp;-&nbsp;&nbsp;
                <font color='darkblue'>
                    <b>Export:</b>
                </font>
                U$ <?=number_format($campos[0]['preco_export'], 2, ',', '.')?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='8' bgcolor='#CCCCCC'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b>Observação do Produto: </b>
                <a href="javascript:nova_janela('../observacao_produto.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'CONSULTAR', '', '', '', '', '250', '780', 'c', 'c', '', '', 's', 's', '', '', '')" alt='Alterar Observação do Produto' title='Alterar Observação do Produto' class='link'>
                <?
                    if(empty($observacao_produto)) {
                        echo 'SEM OBSERVAÇÃO';
                    }else {
                        echo $observacao_produto;
                    }
                ?>
                </a>
            </font>
            <?
/*Se existir algum desenho anexado p/ essa P.A., então eu exibo essa palavra de desenho 
junto desse ícone de Impressora ...*/
                if(!empty($desenho_para_op)) {
                    $sql = "SELECT `desenho_para_op` 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                    $campos_desenho = bancos::sql($sql);
            ?>
            <a href="javascript:nova_janela('../../../../imagem/fotos_produtos_acabados/<?=$campos_desenho[0]['desenho_para_op'];?>', 'EXIBIR_DESENHO', '', '', '', '', '700', '980', 'c', 'c', '', '')" class='link'>
                <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' title='Existe desenho anexado p/ este P.A' style='cursor:help' color='darkgreen' size='1'>
                    - <b>DESENHO</b>
                </font>
                <img src = '../../../../imagem/impressora.gif' border='0' title='Existe desenho anexado p/ este P.A' alt='Existe desenho anexado p/ este P.A' style='cursor:pointer'>
            </a>
            <?
                }
            ?>
        </td>
    </tr>
    <tr>
        <td colspan='4' bgcolor='#CCCCCC'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b>Follow-Up do Produto Acabado (Vendedores e Depto. Técnico): </b>
            </font>
        </td>
        <td colspan='4' bgcolor='#CCCCCC'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <a href='../../cadastros/produto_acabado/follow_up.php?id_produto_acabado=<?=$id_produto_acabado;?>' class='html5lightbox'>
                <?
                    $sql = "SELECT COUNT(`id_produto_acabado_follow_up`) AS total_follow_ups 
                            FROM `produtos_acabados_follow_ups` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado' ";
                    $campos_follow_ups  = bancos::sql($sql);
                    $total_follow_ups   = $campos_follow_ups[0]['total_follow_ups'];
                    if($total_follow_ups == 0) {
                        echo 'NÃO HÁ FOLLOW-UP(S) REGISTRADO(S)';
                    }else {
                        echo '<font color="red"><marquee width="280">'.$total_follow_ups.' FOLLOW-UP(S) REGISTRADO(S)</marquee></font>';
                    }
                ?>
                </a>
            </font>
        </td>
    </tr>
 <?
//Aqui eu fiz uma antecipação de sql da etapa 2, antes mesmo da etapa 1 porque o campo quantidade de lote se encontra aki antes do loop da etapa 1 ...
	$sql = "SELECT `id_produto_insumo`, `id_produto_insumo_ideal`, `qtde_lote`, `peso_kg`, `peca_corte`, 
                `comprimento_1`, `comprimento_2`, `comprimento_barra` 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
	$campos_etapa2              = bancos::sql($sql);
        $id_produto_insumo_etapa2   = $campos_etapa2[0]['id_produto_insumo'];
        $id_produto_insumo_ideal    = $campos_etapa2[0]['id_produto_insumo_ideal'];
//Qtde Lote
	$qtde_lote                  = $campos_etapa2[0]['qtde_lote'];
	$qtde_lote_alert            = $campos_etapa2[0]['qtde_lote'];//Usado para segurança em JavaScript + abaixo ...
/*Aqui verifica se a quantidade do lote é > 1000, porque caso isso aconteça então
sofrerá alterações no valor do fator de custo da Etapa 2*/
	if($qtde_lote > 1000) $fator_custo_2 = $fator_custo_2_new;
//Peso Kg ...
	$peso_aco_kg        = $campos_etapa2[0]['peso_kg'];// *1.05 esta parte e no JS
//Peça Corte ...
	$pecas_corte        = ($campos_etapa2[0]['peca_corte'] == 0) ? 1 : $campos_etapa2[0]['peca_corte'];
//Comprimento A ...
	$comprimento_1      = $campos_etapa2[0]['comprimento_1'];
//Comprimento B ...
	$comprimento_2      = $campos_etapa2[0]['comprimento_2'];
//Comprimento Barra ...        
        $comprimento_barra  = $campos_etapa2[0]['comprimento_barra'];

//Essa já prepara as variáveis para o cálculo das etapas do custo
	$taxa_financeira_vendas = genericas::variaveis('taxa_financeira_vendas');
	//custos::custo_auto_pi_industrializado();//tem q ser antes das chamadas dos metodos todas_etapas(PA); tempo q gasta é quase zero
	$total_indust           = custos::todas_etapas($id_produto_acabado, $operacao_custo);
	$total_com_impostos     = $GLOBALS['total_com_impostos'];
?>
    <tr class='linhacabecalho'>
        <td colspan='3'>
            Quantidade do Lote:
            <?
                if($status_custo == 1) {//Se o Custo está liberado, não é possível alterar a Qtde do Lote ...
                    $url_qtde_lote = "javascript:bloquear_custo()";
                }else {//Só é possível estar alterando a Qtde do Lote quando o Custo estiver Bloqueado ...
                    $url_qtde_lote = "javascript:nova_janela('alterar_quantidade_lote.php?id_produto_acabado_custo=$id_produto_acabado_custo', 'QUANTIDADE_LOTE', '', '', '', '', '160', '800', 'c', 'c', '', '', 's', 's', '', '', '') ";
                }
            ?>
            <a href="<?=$url_qtde_lote;?>" alt='Alterar Quantidade do Lote' title='Alterar Quantidade do Lote' class='link'>
                <font size='2' color='yellow'>    
                    <?=$qtde_lote;?>
                </font>
            </a>
        </td>
        <td align='center'>
        <?
            $checked = ($status_custo == 1) ? 'checked' : '';
        ?>
            <input type='checkbox' name='chkt_custo_liberado' id='checar' value='1' title='Custo Liberado' onclick='confirmar_liberacao()' <?=$checked;?> class='checkbox'>
            <label for='checar'>
                Custo Liberado
            </label>
        </td>
        <td colspan='4'>
            Pre&ccedil;o Fat. Nac. Min. 
            <a href="javascript:nova_janela('valor_custo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>', 'POP', '', '', '', '', 450, 700, 'c', 'c')" title='Valor Real do Custo' class='link'>
                <font size='2' color='yellow'>
                <?
                    echo 'R$ '.number_format($total_indust, 2, ',', '.');
                    //Ñ estamos mais apresentando o Custo c/ Impostos porque por enquanto o mesmo ñ stá sendo utilizado - 18/09/2013 ...
                    //number_format(($total_com_impostos * $taxa_financeira_vendas), 2, ',', '.')
                    $valor_custo_sem_taxa_financeira = round($total_indust, 2);
                ?>
                </font>
            </a>
            <br/>
            Pre&ccedil;o Fat. Nac. Máx. 
            <font size='2' color='yellow'>
                R$ <?=number_format($total_indust / $fator_desconto_maximo_vendas, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <!--*******************************Etapa 1*******************************-->
    <tr class='linhadestaque'>
        <td colspan='3'>
            <a href="javascript:incluir_item_da_etapa(1)" title='Atrelar Embalagem'>
                <font color="#FFFF00">
                    <i>1&ordf; Etapa: Atrelar Embalagem(ns)</i>
                </font>
            </a>
        </td>
        <td colspan='5' align='right'>
            <font color='#FFFF00'>
                <em> F.C.:
                    <font color='#FFFFFF'>
                        <?=number_format($fator_custo_1_3_7, 2, ',', '.');?>
                    </font>
                </em>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <font color='#0000FF'>
                    Total R$:
                </font>
                <font color='#FFFFFF'>
                    <?=number_format($GLOBALS['etapa1'], 2, ',', '.');?>
                </font>
                <font color='black'>
                    <?=' | '.number_format($GLOBALS['etapa1'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                </font>
            </font>
        </td>
    </tr>
 <?
//Aqui traz todas as embalagens que estão relacionadas ao $id_produto_acabado passado por parâmetro ...
    $sql = "SELECT ppe.`id_pa_pi_emb`, ppe.`pecas_por_emb`, ppe.`embalagem_default`, pi.`id_produto_insumo`, pi.`discriminacao`, 
            pi.`unidade_conversao`, u.`sigla` 
            FROM `pas_vs_pis_embs` ppe 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            WHERE ppe.`id_produto_acabado` = '$id_produto_acabado' ORDER BY ppe.`id_pa_pi_emb` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2' bgcolor='#CCCCCC'>
            <b><i>Ref. Emb - Discriminação</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <font title='Embalagem Principal' style='cursor:help'>
                <b><i> E.P.</i></b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Pçs / Emb</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Preço <br/> Unitário sem ICMS R$ </i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Total R$ </i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td colspan='2' align='left'>
            <?=$campos[$i]['sigla'].' - '.$campos[$i]['discriminacao'];?>
            <a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Compras" class='link'>
                <font color='red' style='cursor:help' title='Compra'><b>(Compras)</b></font>
            </a>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['embalagem_default'] == 1) {//Principal
                echo '<img src="../../../../imagem/certo.gif">';
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['unidade_conversao'] > 0) {
                echo number_format($campos[$i]['pecas_por_emb'], 3, ',', '.').' / '.number_format($campos[$i]['unidade_conversao'], 2, ',', '.').' ('.number_format(1 / ($campos[$i]['pecas_por_emb'] * $campos[$i]['unidade_conversao']), 2, ',', '.').') ';
                $unidade_conversao = $campos[$i]['unidade_conversao'];
            }else {
                echo number_format($campos[$i]['pecas_por_emb'], 3, ',', '.').' / <font color="red" title="Sem Conversão">S. C.</font>';
                $unidade_conversao = 1;
            }
            
            //Aqui eu já busco a Unidade do PA que será utilizada mais abaixo ...
            $sql = "SELECT u.`sigla` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                    WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_unidade_pa = bancos::sql($sql);
            echo ' ('.number_format((1 / $unidade_conversao) / $campos[$i]['pecas_por_emb'], 2, ',', '.').' / '.$campos_unidade_pa[0]['sigla'].')';
        ?>
        </td>
        <?
            $preco_unitario = custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
        ?>
        <td align='center'>
            <?=number_format($preco_unitario, 2, ',', '.');?>
        </td>
        <td align='center'>
        <?
            $unidade_conversao = $campos[$i]['unidade_conversao'];
//Para não dar erro de divisão no cálculo abaixo
            if($unidade_conversao == 0) $unidade_conversao = 1;
            $total = ((1 / $unidade_conversao) / $campos[$i]['pecas_por_emb']) * $preco_unitario * $fator_custo_1_3_7;
            echo number_format($total, 2, ',', '.');
        ?>
        </td>
        <td align='center'>
            <img src="../../../../imagem/menu/alterar.png" border='0' onclick="alterar_item_da_etapa('<?=($i + 1);?>', '1')" alt='Alterar Embalagem(ns)' title='Alterar Embalagem(ns)'>
        </td>
        <td align='center'>
            <img src="../../../../imagem/menu/excluir.png" border='0' onclick="excluir_item_da_etapa('<?=$campos[$i]['id_pa_pi_emb'];?>', '1')">
        </td>
        <input type='hidden' name="id_pa_pi_emb[]" value="<?=$campos[$i]['id_pa_pi_emb'];?>">
    </tr>
<?
        }
    }
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <!--*******************************Etapa 2*******************************-->
    <tr class='linhadestaque'>
        <td colspan='2' align='left'>
            <a href="javascript:incluir_item_da_etapa(2)" title="Custo A&ccedil;o / Outros Metais">
                <font color='#FFFF00'>
                    <i>2&ordf; Etapa: Custo A&ccedil;o / Outros Metais </i>
                </font>
            </a>
        </td>
        <td colspan='4' align='right'>
            <font color='#FFFF00'>
                <i>F.C.:
                    <font color='#FFFFFF'>
                        <?=number_format($fator_custo_2, 2, ',', '.');?>
                    </font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>&nbsp;Total R$:</font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa2'], 2, ',', '.');?>
            </font>
            <font color='black'>
                <?=' | '.number_format($GLOBALS['etapa2'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
            </font>
        </td>
    </tr>
<?
	if($id_produto_insumo_etapa2 != 0) {
            if(estoque_insumo_zero($id_produto_insumo_etapa2) < 2) $total_estoque_insumo_zero_2++;
	}

	$sql = "SELECT pi.`discriminacao`, u.`sigla` 
                FROM `produtos_insumos` pi 
                INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                WHERE pi.`ativo` = '1' 
                AND pi.`id_produto_insumo` = '$id_produto_insumo_etapa2' ORDER BY pi.`discriminacao` ";
	$campos = bancos::sql($sql);
	if(!empty($campos[0]['discriminacao'])) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2' align='left'>
            <b><i>Ref. Aço - Discriminação Utilizada:</i></b>
<?/*Coloquei esse nome para o hidden porque aki era um antigo combo, e também
por causa de outras variáveis q possuem esse nome e estão espalhadas nesse arquivo*/
?>
            <input type='hidden' name='hdd_produto_insumo' value='<?=$id_produto_insumo_etapa2;?>'>
            <?=$campos[0]['sigla'].' - '.$campos[0]['discriminacao'];?>
            <a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$id_produto_insumo_etapa2;?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Compras' class='link'>
                <font color='red' style='cursor:help' title='Compra'><b>(Compras)</b></font>
            </a>
        </td>
        <td colspan='2' align='left'>
            <b><i>Ref. Aço - Discriminação Ideal:</i></b>
            <?
                //P/ que se perceba que o PI Utilizado é diferente do PI Ideal, "nós mudamos" a cor ...
                if($id_produto_insumo_etapa2 != $id_produto_insumo_ideal) echo '<font color="red"><b>';
            
                $sql = "SELECT pi.`discriminacao`, u.`sigla` 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE pi.`ativo` = '1' 
                        AND pi.`id_produto_insumo` = '$id_produto_insumo_ideal' ORDER BY pi.`discriminacao` ";
                $campos_pi_ideal = bancos::sql($sql);
                echo $campos_pi_ideal[0]['sigla'].' - '.$campos_pi_ideal[0]['discriminacao'];
                
                if($id_produto_insumo_etapa2 != $id_produto_insumo_ideal) echo '</b></font>';//P/ dar um Destaque melhor ...
            ?>
        </td>
        <td>
            <img src = '../../../../imagem/menu/alterar.png' border='0' onclick="alterar_item_da_etapa(0, '2')" alt='Alterar Custo A&ccedil;o / Outros Metais' title='Alterar Custo A&ccedil;o / Outros Metais'>
        </td>
        <td>
            <img src = '../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item_da_etapa(0, '2')" alt='Excluir Custo A&ccedil;o / Outros Metais' title='Excluir Custo A&ccedil;o / Outros Metais'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='green'>
                <b><i>Comprimento Barra:</i></b>
            </font>
            <?
                echo $comprimento_barra;
                if(!empty($comprimento_barra) && $comprimento_barra > 0) {//Se o Comprimento da Barra estiver preenchido, o sistema calcula o Comprimento da Peça ...
                    $quantidade_barras                      = intval($qtde_lote * (($comprimento_1 + $comprimento_2) / $pecas_corte) / $comprimento_barra) + 1;//Somo + 1 porque se der 1,3 por exemplo teremos que usar 2 barras ...
                    $comprimento_peca_usando_todas_barras   = $comprimento_barra * $quantidade_barras / $qtde_lote;
                    $lote_ideal_para_uso_todas_barras       = intval($comprimento_barra * $quantidade_barras / (($comprimento_1 + $comprimento_2) * 1.05));
                }
            ?>
            &nbsp;MM&nbsp;
            &nbsp;-&nbsp;
            <?=$quantidade_barras;?>
            barra(s) p/ Lote
        </td>
        <td colspan='4'>    
            <font color='green'>
                <b><i>Comprimento da Peça usando toda(s) Barra(s):</i></b>
            </font>
            <?=$comprimento_peca_usando_todas_barras;?>&nbsp;MM&nbsp;
            &nbsp;-&nbsp;
            <font color='green'>
                <b><i>Lote Ideal para uso de toda(s) Barra(s):</i></b>
            </font>
            <?=$lote_ideal_para_uso_todas_barras;?>&nbsp;Pçs&nbsp;
        </td>
    </tr>
<?
            //Traz o preço custo e a densidade do produto insumo que está selecionado na combo ...
            $sql = "SELECT pia.`densidade_aco` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                    WHERE pi.`id_produto_insumo` = '$id_produto_insumo_etapa2' LIMIT 1 ";
            $campos_densidade = bancos::sql($sql);
            if(count($campos_densidade) == 1) {
                $preco_custo    = custos::preco_custo_pi($id_produto_insumo_etapa2);
                //Significa que encontrou um produto com o Preço 0, sendo assim acrescenta na variável ...
                if($preco_custo == 0) $preco_custo_zero++;
                $preco_custo    = number_format($preco_custo, 2, ',', '.');
                $densidade      = $campos_densidade[0]['densidade_aco'];
            }else {
                $preco_custo = '';
                $densidade = '';
            }
?>
    <tr class='linhanormal'>
        <td>
            <b><i>Preço R$ / Kg Sem ICMS: </i></b>
        </td>
        <td>
            <b><i>Comprimento da Peça: </i></b>
        </td>
        <td>
            <b><i>Corte: </i></b>
        </td>
        <td colspan='3'>
            <b><i>Comprimento Total + 5%: </i></b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$preco_custo;?>
        </td>
        <td>
            <?=$comprimento_1;?>&nbsp;MM&nbsp;&nbsp;
        </td>
        <td>
            <?=$comprimento_2;?>&nbsp;MM&nbsp;&nbsp;
        </td>
        <td colspan='3'>
        <?
            $comprimento_total = ($comprimento_1 + $comprimento_2) / 1000 * 1.05;//Multiplico por esse 1.05 porque representa 5% de perda ...
            echo number_format($comprimento_total, 3, ',', '.');
        ?>
            &nbsp;M
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b><i>Peças / Corte:</i></b>
        </td>
        <td>
            <b><i>Densidade Kg / M :</i></b>
        </td>
        <td>
            <?
                $sql = "SELECT u.`sigla` 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                $campos_unidade = bancos::sql($sql);
            ?>
            <b><i>Peso por <?=$campos_unidade[0]['sigla'];?>: </i></b>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$pecas_corte;?>
        </td>
        <td>
            <?=number_format($densidade, 3, ',', '.');?>
        </td>
        <td>
        <?
            $peso_aco_kg = $densidade * $comprimento_total / $pecas_corte;
            echo number_format($peso_aco_kg, 3, ',', '.');
        ?>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
<?
//Aqui são os cálculos para q Qtde do Lote do Custo
    $lote_custo_calculo1 = $peso_aco_kg * $qtde_lote;
    $lote_custo_calculo2 = $lote_custo_calculo1 / $densidade;
?>
    <tr class='linhanormal'>
        <td>
            <font color='green'>
                <b><i>Qtde necessária p/ o Lote:</i></b>
            </font>
        </td>
        <td>
            <i><?=number_format($lote_custo_calculo1, 3, ',', '.');?></i> Kg
        </td>
        <td>
            <font color='green'>
                <b>Qtde necessária p/ o Lote:</i></b>
            </font>
        </td>
        <td colspan='3'>
            <i><?=number_format($lote_custo_calculo2, 3, ',', '.');?>&nbsp;Metros&nbsp;</i>
        </td>
    </tr>
<?
            //Traz a quantidade em estoque do $id_produto_insumo_etapa2 ...
            $sql = "SELECT `qtde` 
                    FROM `estoques_insumos` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo_etapa2' LIMIT 1 ";
            $campos_estoque_pi = bancos::sql($sql);
            if(count($campos_estoque_pi) == 1) {
                $qtde_estoque   = number_format($campos_estoque_pi[0]['qtde'], 2, ',', '.');
                $qtde_estoque2  = number_format(($campos_estoque_pi[0]['qtde'] / $densidade), 2, ',', '.');
            }else {
                $qtde_estoque   = '0,00';
                $qtde_estoque2  = '0,00';
            }
?>
    <tr class='linhanormal'>
        <td>
            <font color='green'>
                <b><i>Estoque do Produto Insumo:</i></b>
            </font>
        </td>
        <td>
            <i><?=$qtde_estoque;?></i> Kg
        </td>
        <td>
            <font color='green'>
                <b><i>Estoque do Produto Insumo:</i></b>
            </font>
        </td>
        <td colspan='3'>
            <i><?=$qtde_estoque2;?>&nbsp;Metros&nbsp;</i>
        </td>
    </tr>
</table>
<?
	}
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <!--*******************************Etapa 3*******************************-->
    <tr class='linhadestaque' align='center'>
        <td colspan='2' align='left'>
            <a href='javascript:incluir_item_da_etapa(3)' title='Atrelar Produto Insumo'>
                <font color='#FFFF00'>
                    <i>3&ordf; Etapa: Atrelar Produto Insumo</i>
                </font>
            </a>
        </td>
        <td colspan='5' align='right'>
            <font color='#FFFF00'>
                <i> F.C.:
                    <font color='#FFFFFF'>
                        <?=number_format($fator_custo_1_3_7, 2, ',', '.');?>
                    </font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa3'], 2, ',', '.');?>
                <font color='black'>
                    <?=' | '.number_format($GLOBALS['etapa3'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                </font>
            </font>
        </td>
    </tr>
 <?
//Aqui traz todos os PIs que estão relacionado ao $id_produto_acabado passado por parâmetro ...
    $sql = "SELECT pp.`id_pac_pi`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, pp.`qtde`, u.`sigla` 
            FROM `pacs_vs_pis` pp 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = pp.`id_produto_insumo` 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.`id_pac_pi` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' bgcolor='#CCCCCC' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Ref - Discriminação</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Estoque</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Quantidade</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Pre&ccedil;o Unitário  sem ICMS R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Total R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>&nbsp;</td>
        <td bgcolor='#CCCCCC'>&nbsp;</td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            if(estoque_insumo_zero($campos[$i]['id_produto_insumo']) < 2) $total_estoque_insumo_zero_3 ++;
?>
    <tr class='linhanormal'>
        <td align="left">
            <?=$campos[$i]['sigla'].' - '.$campos[$i]['referencia'];?>
            -
            <?=$campos[$i]['discriminacao'];?>
            <a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Compras" class='link'>
                <font color='red' style='cursor:help' title='Compra'><b>(Compras)</b></font>
            </a>
        </td>
        <td align='center'>
        <?
            //Traz a quantidade em estoque do PI que está selecionado na combo ...
            $sql = "SELECT `qtde` AS qtde_estoque 
                    FROM `estoques_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
            $campos_estoque_pi  = bancos::sql($sql);
            $qtde_estoque       = (count($campos_estoque_pi) == 1) ? number_format($campos_estoque_pi[0]['qtde_estoque'], 2, ',', '.') : '0,00';
            echo $qtde_estoque;
        ?>
        </td>
        <td align='center'>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td align='center'>
        <?
            $preco_custo = custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
            if($preco_custo == 0) $preco_custo_zero++;
            echo number_format($preco_custo, 2, ',', '.');
        ?>
        </td>
        <td align='center'>
        <?
            $total = $campos[$i]['qtde'] * $preco_custo * $fator_custo_1_3_7;
            echo number_format($total, 2, ',', '.');
        ?>
        </td>
        <td align='center'>
            <img src='../../../../imagem/menu/alterar.png' border='0' onclick="alterar_item_da_etapa('<?=($i + 1);?>', '3')" alt='Alterar Produto Insumo' title='Alterar Produto Insumo'>
        </td>
        <td align='center'>
            <img src='../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item_da_etapa('<?=$campos[$i]['id_pac_pi'];?>', '3')">
        </td>
        <input type='hidden' name='id_pac_pi[]' value='<?=$campos[$i]['id_pac_pi'];?>'>
    </tr>
<?
        }
    }
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <!--*******************************Etapa 4*******************************-->
    <tr class='linhadestaque' align='center'>
        <td colspan='2' align='left'>
            <a href="javascript:incluir_item_da_etapa(4)" title='Atrelar Produto Insumo'>
                <font color='#FFFF00'>
                    <i>4&ordf; Etapa: Atrelar Custo M&aacute;quina</i>
                </font>
            </a>
            &nbsp;
            <?
                //Se o Custo está liberado, não é possível ser alterado mais nada ...
                if($status_custo == 1) {
                    $disabled   = 'disabled';
                    $class      = 'textdisabled';
                }else {//Só é possível estar alterando algum campo quando o Custo estiver Bloqueado ...
                    $disabled   = '';
                    $class      = 'botao';
                }
            ?>
            <input type='button' name='cmd_custo_padrao' value='Custo Padrão' title='Custo Padrão' onclick="html5Lightbox.showLightbox(7, 'custo_padrao.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')" class='<?=$class;?>' <?=$$disabled;?>>
        </td>
        <td colspan='5' align='right'>
            <font color='#FFFF00'>
                <i> F.C.:
                    <font color='#FFFFFF'>
                        <?=number_format($fator_custo_4, 2, ',', '.');?>
                    </font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa4'], 2, ',', '.');?>
                <font color='black'>
                    <?=' | '.number_format($GLOBALS['etapa4'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.');?>
                </font>
            </font>
        </td>
    </tr>
<?
//Aqui traz todas as máquinas que estão relacionado ao $id_produto_acabado passado por parâmetro ...
    $sql = "SELECT pm.`id_pac_maquina`, m.`id_maquina`, m.`nome`, m.`custo_h_maquina`, pm.`tempo_hs` 
            FROM `pacs_vs_maquinas` pm 
            INNER JOIN `maquinas` m ON m.`id_maquina` = pm.`id_maquina` 
            WHERE pm.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pm.`id_pac_maquina` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr align='center' class='linhanormal'>
        <td bgcolor='#CCCCCC'>
            <b><i>Nome da Máquina</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Tempo (Hs)</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>R$ / h</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Total R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
        <?
            echo $campos[$i]['nome'];
            //Aqui tem esse tratamento, para não dar erro de divisão por zero nas etapas 4 e 5
            if($qtde_lote == 0) $qtde_lote = 1;
            $total_rs   = ($campos[$i]['tempo_hs'] * $campos[$i]['custo_h_maquina'] * $fator_custo_4) / $qtde_lote;

            //Só irá mostrar essa Conta quando a Máquina for "Tx Financ Estocagem" ...
            if($campos[$i]['id_maquina'] == 40) {
                $preco_maximo_custo_fat_rs = custos::preco_custo_pa($id_produto_acabado);
                echo '&nbsp;&nbsp;<font color="red"><b>('.number_format($total_rs / ($preco_maximo_custo_fat_rs - $total_rs) * 100, 1, ',', '.').'%)</b></font>';
            }
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['tempo_hs'], 1, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['custo_h_maquina'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_rs, 2, ',', '.');?>
        </td>
        <td>
            <img src='../../../../imagem/menu/alterar.png' border='0' onclick="alterar_item_da_etapa('<?=($i + 1);?>', '4')" alt='Alterar Custo M&aacute;quina' title='Alterar Custo M&aacute;quina'>
        </td>
        <td>
            <img src='../../../../imagem/menu/excluir.png' border='0' onClick="excluir_item_da_etapa('<?=$campos[$i]['id_pac_maquina'];?>', '4')">
        </td>
        <input type='hidden' name="id_pac_maquina[]" value="<?=$campos[$i]['id_pac_maquina'];?>">
    </tr>
<?
        }
    }
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <!--*******************************Etapa 5*******************************-->
    <tr class='linhadestaque' align='center'>
        <td colspan='2' align='left'>
            <a href='javascript:incluir_item_da_etapa(5)' title='Atrelar Usinagem'>
                <font color='#FFFF00'>
                    <i>5&ordf; Etapa: Atrelar Custo de Trat. T&eacute;rmico / Galvanoplastia</i>
                </font>
            </a>
        </td>
        <td colspan='5' align='right'>
            <font color='#FFFF00'>
                <i> F.C.:
                    <font color='#FFFFFF'>
                        <?=number_format($fator_custo_5_6, 2, ',', '.');?>
                    </font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa5'], 2, ',', '.');?>
                <font color='black'>
                    <?=' | '.number_format($GLOBALS['etapa5'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                </font>
            </font>
        </td>
    </tr>
<?
//Aqui traz todos os PIs que estão relacionado ao $id_produto_acabado passado por parâmetro ...
    $sql = "SELECT ppt.`id_pac_pi_trat`, u.`sigla`, pi.`id_produto_insumo`, pi.`discriminacao`, ppt.`fator`, ppt.`peso_aco`, ppt.`peso_aco_manual`, 
            ppt.`lote_minimo_fornecedor` 
            FROM `pacs_vs_pis_trat` ppt 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY ppt.`id_pac_pi_trat` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Ref. Trat - Discriminação </i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Fator T.T.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>P. Unit&aacute;rio sem ICMS R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Peso p/ T.T.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Total R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos[$i]['sigla'];?>
            -
            <?
                echo $campos[$i]['discriminacao'];
                //Verifico se este Item está com Indução, porque se sim é necessário fazermos os furos de Centro ...
                if(strpos(strtr(strtolower($campos[$i]['discriminacao']), 'çã', 'ca'), 'inducao') > 0) echo ' <font color="red"><b>(Fazer furo de centro dos 2 lados da peça)</b></font>';
            ?>
        </td>
        <td>
            <?=number_format($campos[$i]['fator'], 2, ',', '.');?>
        </td>
        <td>
        <?
            $preco_custo = custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
            echo number_format($preco_custo, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
//Peso Aço Manual está checado
            if($campos[$i]['peso_aco_manual'] == 1) {
                echo number_format($campos[$i]['peso_aco'], 3, ',', '.');
            }else {
                echo number_format($campos[$i]['peso_aco'] * $campos[$i]['fator'], 3, ',', '.');
            }
//Peso Aço Manual está checado
            if($campos[$i]['peso_aco_manual'] == 1) echo ' <font color="green"><b>REAL</b></font>';
        ?>
        </td>
        <td>
        <?
            //Ignora a multiplicação pelo fator_tt
            if($campos[$i]['peso_aco_manual'] == 1) {
                $total = $preco_custo * $campos[$i]['peso_aco'] * $fator_custo_5_6;
            }else {
                $total = $campos[$i]['fator'] * $preco_custo * $campos[$i]['peso_aco'] * $fator_custo_5_6;
            }
            //////////////////////////////////////////////////////////
            if($campos[$i]['lote_minimo_fornecedor'] == 1) {// Se estiver setado ou 1 acionar o calculo abaixo de lote minimo por fornecedor default por pedido
                $id_fornecedor_default 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo'], 0, 1);
                //Busco na Lista de Preços o Lote Mínimo em R$ do Fornecedor e do PI na Lista de Preços ...
                $sql = "SELECT `lote_minimo_reais` 
                        FROM `fornecedores_x_prod_insumos` 
                        WHERE `id_fornecedor` = '$id_fornecedor_default' 
                        AND `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_lista       = bancos::sql($sql);
                $lote_minimo_reais  = $campos_lista[0]['lote_minimo_reais'];//lote minimo do fornecedor default
                $preco_peca_corte   = $lote_minimo_reais / $qtde_lote;
                $total_pecas_s_fator= $total/$fator_custo_5_6;
                if($total_pecas_s_fator < $preco_peca_corte) $total = $preco_peca_corte * $fator_custo_5_6;
            }
            //////////////////////////////////////////////////////////
            echo number_format($total, 2, ',', '.');
            //Também se tiver marcada a opção de lote mínimo, eu mostro essa Mensagem ...
            if($campos[$i]['lote_minimo_fornecedor'] == 1) echo ' <font color="red" title="Cálculo por Lote Mínimo" style="cursor:help"><b>LTM</b></font>';
        ?>
        </td>
        <td>
            <img src='../../../../imagem/menu/alterar.png' border='0' onclick="alterar_item_da_etapa('<?=($i + 1);?>', '5')" alt='Alterar Custo de Trat. T&eacute;rmico / Galvanoplastia' title='Alterar Custo de Trat. T&eacute;rmico / Galvanoplastia'>
        </td>
        <td>
            <img src='../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item_da_etapa('<?=$campos[$i]['id_pac_pi_trat'];?>', '5')">
        </td>
        <input type='hidden' name='id_pac_pi_trat[]' value="<?=$campos[$i]['id_pac_pi_trat'];?>">
    </tr>
<?
        }
        //Verifico se existe algum Item nesta 5ª Etapa que está com Indução ...
        $sql = "SELECT ppt.`id_pac_pi_trat` 
                FROM `pacs_vs_pis_trat` ppt 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` AND pi.`discriminacao` LIKE '%INDUÇÃO%' 
                WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
        $campos_inducao = bancos::sql($sql);
        //Se existe, então o Sistema dá um alert de modo a avisar o Depto. Técnico que precisa fazer furo de Centro ...
        if(count($campos_inducao) == 1) {
?>
    <Script Language = 'JavaScript'>
        alert('INDUÇÃO - FAZER FURO DE CENTRO DO(S) 2 LADO(S) DA PEÇA !')
    </Script>
<?
        }
    }
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <!--*******************************Etapa 6*******************************-->
    <tr class='linhadestaque' align='center'>
        <td colspan='2' align='left'>
            <a href="javascript:incluir_item_da_etapa(6)" title='Atrelar Usinagem'>
                <font color='#FFFF00'>
                    <i>6&ordf; Etapa: Atrelar Custo de Usinagem</i>
                </font>
            </a>
        </td>
        <td colspan='5' align='right'>
            <font color='#FFFF00'>
                <i> F.C.:
                    <font color='#FFFFFF'>
                        <?=number_format($fator_custo_5_6, 2, ',', '.');?>
                    </font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa6'], 2, ',', '.');?>
                <font color='black'>
                    <?=' | '.number_format($GLOBALS['etapa6'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                </font>
            </font>
        </td>
    </tr>
<?
//Aqui traz todos os PIs que estão relacionado ao $id_produto_acabado passado por parâmetro ...
    $sql = "SELECT ppu.`id_pac_pi_usi`, ppu.`qtde`, u.`sigla`, pi.`id_produto_insumo`, pi.`discriminacao` 
            FROM `pacs_vs_pis_usis` ppu 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppu.`id_produto_insumo` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            WHERE ppu.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY ppu.`id_pac_pi_usi` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Ref. Usi - Discriminação </i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Quantidade</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>P. Bruto sem ICMS R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Total R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos[$i]['sigla'];?>
            -
            <?=$campos[$i]['discriminacao'];?>  
            <a href="javascript:nova_janela('../../../compras/estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&nao_exibir_voltar=1', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Compras' class='link'>
                <font color='red' style='cursor:help' title='Compra'><b>(Compras)</b></font>
            </a>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
        <?
            $preco_custo = custos::preco_custo_pi($campos[$i]['id_produto_insumo']);
            echo number_format($preco_custo, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            $total = $campos[$i]['qtde'] * $preco_custo * $fator_custo_5_6;
            echo number_format($total, 2, ',', '.');
            ?>
        </td>
        <td>
            <img src='../../../../imagem/menu/alterar.png' border='0' onclick="alterar_item_da_etapa('<?=($i + 1);?>', '6')" alt='Alterar Custo de Usinagem' title='Alterar Custo de Usinagem'>
        </td>
        <td>
            <img src='../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item_da_etapa('<?=$campos[$i]['id_pac_pi_usi'];?>', '6')">
        </td>
        <input type='hidden' name='id_pac_pi_usi[]' value="<?=$campos[$i]['id_pac_pi_usi'];?>">
    </tr>
<?
        }
    }
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <!--*******************************Etapa 7*******************************-->
    <tr class='linhadestaque' align='center'>
        <td align='left'>
            <a href="javascript:incluir_item_da_etapa(7)" title='Atrelar Usinagem'>
                <font color='#FFFF00'>
                    <i>7&ordf; Etapa: Atrelar Produto Acabado / Componente</i>
                </font>
            </a>
        </td>
        <td colspan='8' align='right'>
            <font color='#FFFF00'>
                <i> F.C.:
                    <font color='#FFFFFF'>
                        <?=number_format(1, 2, ',', '.');?>
                    </font>
                </i>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='#0000FF'>
                Total R$:
            </font>
            <font color='#FFFFFF'>
                <?=number_format($GLOBALS['etapa7'], 2, ',', '.');?>
                <font color='black'>
                    <?=' | '.number_format($GLOBALS['etapa7'] / $valor_custo_sem_taxa_financeira * 100, 2, ',', '.').'%';?>
                </font>
            </font>
        </td>
    </tr>
<?
//Aqui traz todos produtos acabados componentes que estão relacionadas ao $id_produto_acabado passado por parâmetro ...
    $sql = "SELECT pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, 
            pa.`operacao_custo_sub`, pa.`preco_unitario`, pa.`status_custo`, pp.`id_pac_pa`, 
            pp.`id_produto_acabado`, 
            pp.`qtde`, pp.`usar_este_lote_para_orc`, u.`sigla` 
            FROM `pacs_vs_pas` pp 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pp.`id_produto_acabado` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
            WHERE pp.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY pp.`id_pac_pa` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Ref. PA - Discriminação</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde Lote</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>O.C.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Estoque Real</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Qtde</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>P. Unit. (s/ Tx + s/ Emb R$)</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Total R$</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            //Só entra aqui caso seja o produto do tipo Especial e  do Tipo Industrializado ...
            if($campos[$i]['referencia'] == 'ESP' && $campos[$i]['operacao_custo'] == 0) {
                //Busca a matéria Prima da 2ª Etapa ...
                $sql = "SELECT `id_produto_insumo` 
                        FROM `produtos_acabados_custos` 
                        WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                        AND `operacao_custo` = '0' LIMIT 1 ";
                $campos_materia_prima = bancos::sql($sql);
                if(estoque_insumo_zero($campos_materia_prima[0]['id_produto_insumo']) < 2) $total_estoque_insumo_zero_7 ++;
            }
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
        <?
/********************************************************************/
//Verificação p/ ver qual caminho caminho que o link deverá seguir ...
            if($campos[$i]['operacao_custo'] == 0) {//Industrial
                $url_custo = "../../../producao/custo/industrial/custo_industrial.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&pop_up=1";
                $url_prazo_entrega = "../../../classes/estoque/alterar_prazo_entrega_industrial.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&operacao_custo=".$campos[$i]['operacao_custo']."&atualizar_iframe=1";
            }else {//Revenda
                $url_custo = "../../../producao/custo/revenda/custo_revenda.php?id_produto_acabado=".$campos[$i]['id_produto_acabado'];
                $url_prazo_entrega = "../../../classes/estoque/alterar_prazo_entrega_revenda.php?id_produto_acabado=".$campos[$i]['id_produto_acabado']."&operacao_custo=".$campos[$i]['operacao_custo']."&atualizar_iframe=1";
            }
        ?>
            <a href="javascript:nova_janela('<?=$url_custo;?>', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Custo' class='link'>
        <?
/********************************************************************/
            $title = ($campos[$i]['status_custo'] == 1) ? 'Custo Liberado' : 'Custo não Liberado';
            $color = ($campos[$i]['status_custo'] == 1) ? '' : 'red';
        ?>
                <font title="<?=$title;?>" color="<?=$color;?>">
                    <?=$campos[$i]['sigla'].' - '.$campos[$i]['referencia'].' - '.$campos[$i]['discriminacao'];?>
                </font>
            </a>
            &nbsp;
            <a href="javascript:nova_janela('<?=$url_prazo_entrega;?>', 'PRAZO_ENTREGA', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Prazo de Entrega' class='link'>
                <font color='red' style='cursor:help' title='Alterar Prazo Entrega'><b>(Prazo de Entrega)</b></font>
            </a>
        </td>
        <td>
        <?
            //Busca o Lote de Custo do atual PA do Loop ...
            $sql = "SELECT `qtde_lote` 
                    FROM `produtos_acabados_custos` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
            $campos_custo = bancos::sql($sql);
            echo $campos_custo[0]['qtde_lote'];
            if($campos[$i]['usar_este_lote_para_orc'] == 'S') echo '&nbsp;<img src="../../../../imagem/certo.gif" title="Usa este Lote de Custo p/ Orc" style="cursor:help">';
        ?>
        </td>
        <td align='center'>
        <?
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
        <?
            //Traz a quantidade em estoque do produto acabado ...
            $estoque_produto    = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], '1');
            $estoque_real       = number_format($estoque_produto[0], 2, ',', '.');
        ?>
        <td>
            <?=$estoque_real;?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
        <?
            /*Aqui eu trago o valor $preco_custo já multiplicado pela Qtde de Peças da 7ª Etapa, consequentemente divido o mesmo 
            p/ apresentarmos p/ o Usuário o Valor deste de modo Unitário ...*/
            $preco_custo = custos::etapa7($campos[$i]['id_produto_acabado'], $id_produto_acabado_custo);
            echo number_format($preco_custo / $campos[$i]['qtde'], 2, ',', '.');
        ?>
        </td>
        <td>
            <?=number_format($preco_custo, 2, ',', '.');?>
        </td>
        <td>
            <img src='../../../../imagem/menu/alterar.png' border='0' onclick="alterar_item_da_etapa('<?=($i + 1);?>', '7')" alt='Alterar Produto Acabado / Componente' title='Alterar Produto Acabado / Componente'>
        </td>
        <td>
            <img src='../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item_da_etapa('<?=$campos[$i]['id_pac_pa'];?>', '7')">
        </td>
        <input type='hidden' name='id_pac_pa[]' value='<?=$campos[$i]['id_pac_pa'];?>'>
    </tr>
<?
        }
    }
?>
    <tr class='linhanormal' align='center'>
        <td colspan='9'>
            <?$checked_lote_minimo = ($lote_minimo_ignora_faixa_orcavel == 'S') ? 'checked' : '';?>
            <input type='checkbox' name='chkt_lote_minimo' value='S' id='lote_minimo' title='Lote Mínimo' onclick='confirmar_lote_minimo()' <?=$checked_lote_minimo;?> class='checkbox'>
            <label for='lote_minimo'>
                <b>LOTE M&Iacute;NIMO (DEFINIR ESTE LOTE COMO O M&Iacute;NIMO OR&Ccedil;&Aacute;VEL)</b>
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
        <?
//Esse parâmetro é porque essa tela também é puxada de lá da tela de Orçamentos, e daí tem conflito de sessão
            if(empty($ignorar_sessao)) {
        ?>
                <input type='button' name='cmd_clonar' value='Clonar Custo' title='Clonar Custo' style='color:black' onclick="html5Lightbox.showLightbox(7, 'clonagem_custo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>')" class='<?=$class;?>' <?=$disabled;?>>
                <input type='button' name='cmd_incluir_pa_do_custo_no_orc' value='Incluir PA do Custo no Orçamento' title='Incluir PA do Custo no Orçamento' style='color:black' onclick="html5Lightbox.showLightbox(7, 'incluir_pa_do_custo_no_orc.php?id_produto_acabado=<?=$id_produto_acabado;?>')" class='botao'>
                <input type='button' name='cmd_visualizar_pis' value="Visualizar PI's" title="Visualizar PI's" onclick="alert('ALGUMA(S) VEZ(ES) ESSE PROCESSO É UM POUCO DEMORADO, AGUARDE ...');nova_janela('../../ops/visualizar_pis.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'POP', '', '', '', '', 600, 900, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        <?
            }else {
        ?>
                <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='10'>
            <iframe name='detalhes' id='detalhes' src = '../../../classes/follow_ups/detalhes.php?identificacao=<?=$id_produto_acabado;?>&origem=19' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
</table>
<input type='hidden' name='id_produto_acabado_custo' value='<?=$id_produto_acabado_custo;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='passo'>
</form>
<?
    $valores                            = intermodular::calculo_producao_mmv_estoque_pas_atrelados($id_produto_acabado);
    $total_mmv_pas_atrelados            = $valores['total_mmv_pas_atrelados'];
    //Estamos estimando que o Maior Lote de Produção será consumido em no máximo X meses ...
    $qtde_meses_mmv_maximo_lote_custo   = genericas::variavel(82);
    
    //Se a Operação de Custo do PA = 'Revenda', então tem que fazer esse Tratamento p/ Margem de Lucro
    if($operacao_custo_rotulo == 'R') {
    /***************************Tratamento de Margem de Lucro**************************/
        //Em segundo verifico qual é o id_fornecedor_setado, já tenho mesmo o $id_produto_acabado
        $id_fornecedor_setado   = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1); //busco somente o id_forncedor default para saber de qual forncedor q estou pegando para calcular o custo do PA revenda
        //Busco o Fator Margem de Lucro do PA da Lista através do '$id_produto_insumo' e do '$id_fornecedor_setado' ...
        $sql = "SELECT `id_fornecedor_prod_insumo`, `fator_margem_lucro_pa` 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                AND `id_fornecedor` = '$id_fornecedor_setado' LIMIT 1 ";
        $campos_lista               = bancos::sql($sql);
        /***************************Tratamento de Margem de Lucro**************************/
        //Agora se a M.L. for = 0, eu também não posso liberar o Custo do PA
        $fator_margem_lucro_pa      = ($campos_lista[0]['fator_margem_lucro_pa'] == '0.00') ? 0 : 1;
        //Se a Operação de Custo do PA = 'Industrial', então não preciso fazer esse Tratamento p/ Margem de Lucro
    }else {
        $fator_margem_lucro_pa = 1;
    }
/******************************************************************************************/

    //Controle que serve para essa função de JavaScript -> confirmar_liberacao(), mais abaixo
    if($referencia == 'ESP') {
    /*Listagem de Todos os Orçamento(s) que estão em Aberto, q não estão congelados, que contém esse Item
    em que o prazo de Entrega seja igual a Imediato*/
        $sql = "SELECT ovi.`id_orcamento_venda_item` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`congelar` = 'N' 
                WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' 
                AND ovi.`prazo_entrega_tecnico` = 'I' LIMIT 1 ";
        $campos_orcamento = bancos::sql($sql);
        //Se encontrar algum item que tenha o prazo de entrega técnico como zerado, então não pode liberar o custo ...
        $custo_nao_pode_liberar = (count($campos_orcamento) == 1) ?  1 : 0;
    }else {//Se for Industrial ...
        $custo_nao_pode_liberar = 0;
    }
?>
<!--**********************************************************************************************-->
<!--**********************************Funções com idéia de Onload*********************************-->
<!--**********************************************************************************************-->
<!--
Joguei essa function aki em baixo, devido a variável de preço $preco_custo_zero em PHP, que
foi sendo tratada no meio das etapas 2 e 3 e da variável $custo_nao_pode_liberar-->
<Script Language = 'JavaScript'>
function confirmar_liberacao() {
    var id_familia          = '<?=$id_familia;?>'
/*Essa variável é iniciada com o valor 0 no início do código, caso esta seje > do q 0, então
significa que foi encontrado algum preço de pi_insumo como sendo 0.00, sendo assim não pode
ser liberado o custo*/
    var preco_custo_zero    = '<?=$preco_custo_zero;?>'
    var referencia          = '<?=$referencia;?>'
    var status_custo        = '<?=$status_custo;?>'
    
    //Não encontrou nenhum PI como sendo 0 ...
    if(preco_custo_zero == 0) {
        if(document.form.chkt_custo_liberado.checked == true) {//Vai liberar o custo
/***************************Tratamento de Margem de Lucro**************************/
//Agora se a M.L. for = 0, eu também não posso liberar o Custo do PA
            var fator_margem_lucro_pa = eval('<?=$fator_margem_lucro_pa;?>')
            if(fator_margem_lucro_pa == 0) {
                alert('ESSE CUSTO NÃO PODE SER LIBERADO !\nO FATOR MARGEM DE LUCRO DESSA P.A. É = 0,00 !')
                document.form.chkt_custo_liberado.checked = false
                return false
            }
/**********************************************************************************/
/*Na hora de liberar o custo, o Sistema verifica se o Depto. Técnico já deu o prazo para este Item
do Orçamento do PA atrelado, se isso ainda não aconteceu, não posso liberar o custo*/
            var custo_nao_pode_liberar = eval('<?=$custo_nao_pode_liberar;?>')
            if(custo_nao_pode_liberar == 1) {
                alert('ESSE CUSTO NÃO PODE SER LIBERADO, PREENCHA O PRAZO DE ENTREGA DO P.A. !')
                document.form.chkt_custo_liberado.checked = false
                showHide('alterar_prazo_entrega_tecnico')
                return false
            }
/**********************************************************************************/
//Se a Qtde do Lote for = 0, eu também não posso liberar o Custo do PA
            var qtde_lote   = eval('<?=$qtde_lote_alert;?>')
            if(qtde_lote == 0) {
                alert('ESSE CUSTO NÃO PODE SER LIBERADO !\nA QUANTIDADE DO LOTE DESSE CUSTO É = 0,00 !')
                document.form.chkt_custo_liberado.checked = false
                return false
            }
            
            var total_mmv_pas_atrelados             = eval('<?=$total_mmv_pas_atrelados;?>')
            var qtde_meses_mmv_maximo_lote_custo    = eval('<?=$qtde_meses_mmv_maximo_lote_custo;?>')
            var qtde_meses_lote_custo               = (qtde_lote / total_mmv_pas_atrelados)
            var lote_maximo_custo_aceitavel         = parseInt(qtde_meses_mmv_maximo_lote_custo * total_mmv_pas_atrelados)
            
            //Somente p/ os PA(s) normais de Linha que farei o procedimento abaixo porque só estes possuem MMV ...
            if(referencia != 'ESP') {
                if(qtde_meses_lote_custo > qtde_meses_mmv_maximo_lote_custo && referencia) {//Alguma divergência ...
                    var resposta = confirm('O LOTE DO CUSTO ESTÁ MAIOR QUE A QTDE DE MESES DE ESTOCAGEM MÁXIMA, QUE É DE '+qtde_meses_mmv_maximo_lote_custo+' MESES !!! O LOTE MÁXIMO DE CUSTO ACEITAVEL É '+lote_maximo_custo_aceitavel+' !!!\n\nDESEJA LIBERAR O CUSTO MESMO ASSIM ?')
                    if(resposta == false) {
                        document.form.chkt_custo_liberado.checked = false
                        return false
                    }
                }
            }else {//Em caso de ESP, normal - nunca terei que fazer a verificação ...
                var resposta = confirm('DESEJA LIBERAR O CUSTO ?')
                if(resposta == true) {//Liberando o Custo ...
                    //Somente no caminho de ESP, Família Macho e a opção de Lote Miníma estiver desmarcada ...
                    if(id_familia == 9 && !document.form.chkt_lote_minimo.checked) {
                        var resposta2 = confirm('MACHOS ESP, TEM DE MARCAR "LOTE MINIMO ORÇÁVEL" NO FINAL DO CUSTO !!! DESEJA MARCAR ESTA OPÇÃO ?')
                        if(resposta2 == true) document.form.chkt_lote_minimo.checked = true
                    }
                }else {//Bloqueando o Custo ...
                    document.form.chkt_custo_liberado.checked = false
                    return false
                }
            }
            document.form.passo.value = 1
            document.form.submit()
        }else {//Vai bloquear o custo
            return bloquear_custo()
        }
    }else {
/*Aqui verifica se já trouxe do BD o custo como liberado ou bloqueado, então se eu quiser bloquear 
o custo, posso fazer normalmente, o que eu não posso fazer é o inverso*/
        if(status_custo == 1) {//Custo já estava liberado
            document.form.passo.value = 1
            document.form.submit()
        }else {//Custo ainda está para ser liberado
            alert('ESSE CUSTO NÃO PODE SER LIBERADO !\n EXISTE(M) ITEM(NS) CUJO O PREÇO DE CUSTO NAS ETAPAS 2 OU 3 É DE VALOR R$ 0,00 !')
            document.form.chkt_custo_liberado.checked = false
            return false
        }
    }
}

/*Joguei essa function aki em baixo, devido a variável de preço $estoque_insumo_zero_2, 3 e 7
 em PHP, que foi sendo tratada no meio das etapas 2,3 e 7*/
function verificar_estoque_insumo_zero() {
    //Essa function só serve para produtos do Tipo Especial
    var referencia = '<?=$referencia;?>'
    if(referencia == 'ESP') {
        var total_estoque_insumo_zero_2 = '<?=$total_estoque_insumo_zero_2;?>'
        //var total_estoque_insumo_zero_3 = eval('<?=$total_estoque_insumo_zero_3;?>')
        var total_estoque_insumo_zero_7 = '<?=$total_estoque_insumo_zero_7;?>'
//Não achou
        if(total_estoque_insumo_zero_2 == '') total_estoque_insumo_zero_2 = 0
        if(total_estoque_insumo_zero_7 == '') total_estoque_insumo_zero_7 = 0
//Comparação - Aqui significa que foi encontrado um produto pelo menos q tem o estoque menor q 2
        if(total_estoque_insumo_zero_2 > 0)     alert('ATENÇÃO !!!\n SEU PRODUTO INSUMO NÃO POSSUE ESTOQUE SUFICIENTE NA 2º ETAPA !')
        if(total_estoque_insumo_zero_7 > 0)     alert('ATENÇÃO !!!\n SEU PRODUTO INSUMO NÃO POSSUE ESTOQUE SUFICIENTE NA 7º ETAPA !')
    }
}
verificar_estoque_insumo_zero()
</Script>
<!--**********************************************************************************************-->
</body>
</html>
<?
}
?>
<pre>
<b><font color='blue'>Variáveis:</font></b>
<pre>
<b><font color='green'>* Taxa Financeira de Vendas no Custo: 0,70 * <?=number_format($taxa_financeira_vendas, 2, ',', '.');?>% = </font><?=number_format(0.70 * $taxa_financeira_vendas, 2, ',', '.');?> %</b><br>
</pre>