<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../lib/custos.php');
require('../../../lib/data.php');
require('../../../lib/intermodular.php');//Esse arquivo ñ pode ser retirado, pq a biblioteca Vendas utiliza uma função deste ...
require('../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../');

$mensagem[1] = 'ORÇAMENTO ALTERADO COM SUCESSO !';
$mensagem[2] = 'NÃO FOI POSSÍVEL CONGELAR SEU ORÇAMENTO !\n\nEXISTEM ITENS SEM CUSTO / BLOQUEADO / SEM PRAZO DE DEPTO. TÉCNICO / ITENS EM QUE A QTDE NÃO ESTÁ COMPATÍVEL COM A QTDE DE PÇS/CORTE DO CUSTO.';
$mensagem[3] = 'NÃO FOI POSSÍVEL DESCONGELAR SEU ORÇAMENTO !\n\n EXISTEM ITEM(NS) IMPORTADO(S) PARA PEDIDO.';
$mensagem[4] = 'NÃO FOI POSSÍVEL CONGELAR SEU ORÇAMENTO !\n\n NÃO EXISTE ITEM.';

//Aki eu verifico se tem algum item do Orçamento q já está em Pedido, caso sim, eu não posso mais descongelar
function verificar_pedido($id_orcamento_venda) {
    $sql = "SELECT pvi.id_pedido_venda_item 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_orcamento_venda_item` = ovi.`id_orcamento_venda_item` 
            WHERE ovi.`id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
    $campos = bancos::sql($sql);//Se encontrar pelo menos 1 item, já ta bom pra mim saber
    $linhas = count($campos);
    return $linhas;
}

$valor              = 1;//Variável de Retorno de Mensagem
$desconto_icms_sgd  = (!empty($_POST['chkt_desconto_icms_sgd'])) ? 1 : 0;

/***Aki eu verifico a situação atual do Orçamento, se este está congelado ou não e se esta com Desconto de "ICMS/SGD" ***/
$sql = "SELECT `congelar`, `nota_sgd`, `desc_icms_sqd_auto`, `prazo_medio` 
        FROM `orcamentos_vendas` 
        WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
$campos                     = bancos::sql($sql);
$nota_sgd                   = $campos[0]['nota_sgd'];
$desconto_icms_sgd_gravado  = $campos[0]['desc_icms_sqd_auto'];
$prazo_medio_gravado        = $campos[0]['prazo_medio'];

//Variável q retorna se tem algum item do Orçamento em Pedido
$verificar_pedido   = verificar_pedido($_POST['id_orcamento_venda']);

if(empty($_POST['chkt_congelar'])) {//Significa que o Orçamento está sendo descongelado - Checkbox
//Como não existe nenhum item importado em Pedido, então eu posso descongelar o mesmo ...
    if($verificar_pedido == 0) {
        $congelar = 'N';
    }else {//Significa que existe pelo menos importado em Pedido, então já não posso + descongelar ...
        $congelar = 'S';
        $valor = 3;//Variável de Retorno de Mensagem
    }
}else {//Significa que o Orçamento está sendo congelado - Checkbox
    $discriminacao 	= '';//Variável de Controle p/ não dar erro ...
    $congelar 		= 'S';
/*Verifico a situação dos itens de orçamento, caso existe algum produto em ORÇAR ou DEP. TÉCNICO no 
orçamento corrente não posso congelar*/
    $sql = "SELECT preco_liq_fat_disc 
            FROM `orcamentos_vendas_itens` 
            WHERE`id_orcamento_venda` = '$_POST[id_orcamento_venda]' 
            AND `preco_liq_fat_disc` <> '' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) > 0) {//Não posso congelar ...               
        $congelar   = 'N';
        $valor      = 2;//Variável de Retorno de Mensagem
    }else {//Caso eu possa congelar, então eu verifico se existe custo bloqueado
        $sql = "SELECT ovi.id_produto_acabado 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
                WHERE ovi.`id_orcamento_venda` = '$_POST[id_orcamento_venda]' 
                AND pa.`status_custo` = '0' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) > 0) {//Também não posso congelar o orçamento ...
            $congelar   = 'N';
            $valor      = 2;//Variável de Retorno de Mensagem
        }else {//Aqui eu verifico se existe algum Item no Orc = 'ESP' e que esteje sem Pzo. Técnico
            $sql = "SELECT ovi.id_produto_acabado 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado AND pa.referencia = 'ESP' 
                    WHERE ovi.`id_orcamento_venda` = '$_POST[id_orcamento_venda]' 
                    AND ovi.`prazo_entrega_tecnico` = '0.0' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) > 0) {//Também não posso congelar o orçamento ...
                $congelar = 'N';
                $valor = 2;//Variável de Retorno de Mensagem
            }else {//Aqui eu verifico se a Qtde do Orçamento está compatível com a qtde de Pças/Corte do Custo ...
                $sql = "SELECT pa.discriminacao 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `produtos_acabados` pa on pa.id_produto_acabado = ovi.id_produto_acabado and pa.operacao_custo = '0' and pa.referencia = 'ESP' 
                        INNER JOIN `produtos_acabados_custos` pac on pac.id_produto_acabado = pa.id_produto_acabado 
                        WHERE ovi.`id_orcamento_venda` = '$_POST[id_orcamento_venda]' 
                        AND (MOD(ovi.qtde, pac.peca_corte) <> '0') LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) > 0) {//Também não posso congelar o orçamento ...
                    $discriminacao  = '\n\n'.$campos[0]['discriminacao'];
                    $congelar       = 'N';
                    $valor          = 2;//Variável de Retorno de Mensagem
                }else {//Caso eu possa congelar, então eu verifico se existe pelo menos 1 item no Orc.
                    $sql = "SELECT preco_liq_fat_disc 
                            FROM `orcamentos_vendas_itens` 
                            WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
                    $campos = bancos::sql($sql);
                    if(count($campos) == 0) {//Significa q não possui item nenhum item no Orc.
                        $congelar   = 'N';
                        $valor      = 4;//Variável de Retorno de Mensagem
                    }
                }
            }
        }
    }
}

//Quando eu descongelo o Orçamento, então ... 
if(empty($_POST['chkt_congelar'])) {//Significa que o Orçamento está sendo descongelado - Checkbox
    if($verificar_pedido == 0) {//Não existe nenhum item importado, sendo assim eu posso descongelar ...
        $sql = "UPDATE `orcamentos_vendas` SET `congelar` = '$congelar', `artigo_isencao` = '$_POST[chkt_artigo_isencao]' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Existem itens importados p/ pedido ...
        $valor = 3;
    }
}else {//Significa que o Orçamento está sendo congelado - Checkbox
    $prazo_medio = intermodular::prazo_medio($_POST['txt_prazo_a'], $_POST['txt_prazo_b'], $_POST['txt_prazo_c'], $_POST['txt_prazo_d']);
    /*******************************************************/
    if($verificar_pedido == 0) {//Não existe nenhum item importado, estou congelando o Orc ...
        if($congelar == 'S') {//Significa que foi possível congelar o Orc ...
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
            $id_transportadora = (!empty($_POST[cmb_cliente_transportadora])) ? $_POST[cmb_cliente_transportadora] : 'NULL';
            
            /*Existem itens que estão em Queima de Estoque ou que possuem Promoção então 
            só atualizo alguns campos ...*/
            if($_POST['hdd_possui_queima_estoque'] == 'S' || $_POST['hdd_possui_promocao'] == 'S') {
                $sql = "UPDATE `orcamentos_vendas` SET congelar = '$congelar' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
            }else {//Não existem Itens em Estoque, então eu atualizo todos os campos ...
                $sql = "UPDATE `orcamentos_vendas` SET congelar = '$congelar', desc_icms_sqd_auto = '$desconto_icms_sgd', `artigo_isencao` = '$_POST[chkt_artigo_isencao]', `id_transportadora` = $id_transportadora, `id_cliente_contato` = '$_POST[cmb_cliente_contato]', `finalidade` = '$_POST[cmb_finalidade]', `tipo_frete` = '$_POST[cmb_tipo_frete]', `valor_frete_estimado` = '$_POST[txt_valor_frete_estimado]', `nota_sgd` = '$_POST[cmb_tipo_nota]', `prazo_a` = '$_POST[txt_prazo_a]', `prazo_b` = '$_POST[txt_prazo_b]', `prazo_c` = '$_POST[txt_prazo_c]', `prazo_d` = '$_POST[txt_prazo_d]', `prazo_medio` = '$prazo_medio' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
            }
            bancos::sql($sql);
            //Exclui direto todas mensagens ESP se o id_orcamento_venda_item estiver na Tab. Relacional mensagens_esps
            $sql = "DELETE me.* 
                    FROM `mensagens_esps` me 
                    INNER JOIN `orcamentos_vendas_itens` ovi on ovi.id_orcamento_venda_item = me.id_orcamento_venda_item 
                    INNER JOIN `orcamentos_vendas` ov on ov.id_orcamento_venda = ovi.id_orcamento_venda 
                    WHERE ov.`id_orcamento_venda` = '$_POST[id_orcamento_venda]' ";
            bancos::sql($sql);
        }else {//Não foi possível congelar o Orc, por alguns dos critérios acima ...
            $sql = "UPDATE `orcamentos_vendas` SET congelar = '$congelar', `artigo_isencao` = '$_POST[chkt_artigo_isencao]' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
            bancos::sql($sql);
        }
    }else {//Tem pelo menos um item que foi que já foi importado para pedido
        $valor = 3;
    }
}

/*******************************************************************/
if($congelar == 'S') {//Significa que foi possível congelar o Orc ...
/********************************************************************************************************/
    //Busco todos os itens do $id_orcamento_venda passado por parâmetro p/ poder rodar algumas funções abaixo ...
    $sql = "SELECT id_orcamento_venda_item 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
    for($i = 0; $i < $linhas_itens; $i++) {
        //Verifico se o usuário fez mudanças no Tipo de Nota ou no Desconto ICMS/SGD Automático do Cabeçalho ...
        if(($nota_sgd != $_POST[cmb_tipo_nota]) || ($desconto_icms_sgd_gravado != $desconto_icms_sgd)) {
            vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item']);
        }
        //Se houve mudança no Tipo de Nota ou no Desconto ICMS/SGD Automático do Cabeçalho ou nos Prazos do Orçamento tenho que rodar essa função abaixo também ...
        if(($nota_sgd != $_POST[cmb_tipo_nota]) || ($desconto_icms_sgd_gravado != $desconto_icms_sgd) || ($prazo_medio_gravado != $prazo_medio)) {
            //Aqui eu atualizo a ML Est do Iem do Orçamento ...
            custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
/*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($_POST['id_orcamento_venda'], $campos_itens[$i]['id_orcamento_venda_item']);
        }
    }
}else {//Significa que não foi possível congelar o Orc por algum dos Motivos acima ...
?>
<Script Language = 'JavaScript'>
    alert('<?=$mensagem[$valor].$discriminacao;?>')
</Script>
<?
}
/*******************************************************************/
?>
<Script Language = 'JavaScript'>
    window.parent.location = 'itens/itens.php?id_orcamento_venda=<?=$_POST[id_orcamento_venda];?>'//Relê a Tela de Baixo ...
</Script>