<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../../lib/intermodular.php');
require('../../../lib/vendas.php');
require('../../../lib/variaveis/intermodular.php');
require('../../classes/array_sistema/array_sistema.php');
require('../pdt/class_pdt.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>PEDIDO ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>PEDIDO DESLIBERADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='atencao'>NÃO É POSSÍVEL ALTERAR O CABEÇALHO ! EXISTE(M) ORÇAMENTO(S) EM QUE O PRAZO MÉDIO ESTÁ IRREGULAR EM COMPARAÇÃO AO DO PEDIDO.</font>";

$prazo_validade_pedido  = genericas::variavel(68);
$dif_max_dias_prog_esp  = 92;//Colocamos essa variável como sendo 92 pq temos meses em que temos 31 dias ao invés de 30 dentro do Período de 3 meses ...

/**********************************************************************/
/************Quinhentos Quilos de Funções só para essa Tela************/
/**********************************************************************/

/*Essa função permite que o usuário mude uma transportadora mesmo com o Pedido já Liberado, mas se todos os itens de Pedido 
estiver totalmente Importado p/ Nota Fiscal então o usuário não pode alterar para nenhuma Transportadora ...*/
function seguranca_com_transportadora($id_pedido_venda, $id_transportadora_new) {
    //Aqui eu busco o Cliente do Pedido ...
    $sql = "SELECT pv.`id_cliente`, pv.`status`, CONCAT(c.`razaosocial`, ' (', c.`nomefantasia`, ')') AS cliente, 
            IF(t.`nome_fantasia` = '', t.`nome`, t.`nome_fantasia`) AS transportadora 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            INNER JOIN `transportadoras` t ON t.`id_transportadora` = pv.`id_transportadora` 
            WHERE pv.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
    $campos = bancos::sql($sql);
    switch($campos[0]['status']) {
        case 0:
        case 1:
            //Aqui eu atualizo o Pedido com a Nova Transportadora ...
            $sql = "UPDATE `pedidos_vendas` SET `id_transportadora` = '$id_transportadora_new' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
            bancos::sql($sql);
            //Busco todas as NF´s que estão com o status até "Empacotada" do Cliente do Pedido de Vendas ...
            $sql = "SELECT id_nf 
                    FROM `nfs` 
                    WHERE `id_cliente` = '".$campos[0]['id_cliente']."' 
                    AND `status` <= '3' ";
            $campos_nf = bancos::sql($sql);
            $linhas_nf = count($campos_nf);
            if($linhas_nf > 0) {//Só será disparado e-mail quando o Pedido de Venda estiver em uma NF pelo menos ...
                for($i = 0; $i < $linhas_nf; $i++) $notas_existentes.= faturamentos::buscar_numero_nf($campos_nf[$i]['id_nf'], 'S').', ';
                $notas_existentes = substr($notas_existentes, 0, strlen($notas_existentes) - 2);

                //Aqui eu busco o Nome da Nova Transportadora que foi alterada ...
                $sql = "SELECT IF(nome_fantasia = '', nome, nome_fantasia) AS transportadora_new 
                        FROM `transportadoras` 
                        WHERE id_transportadora = '$id_transportadora_new' LIMIT 1 ";
                $campos_transp = bancos::sql($sql);
                $destino    = 'agueda@grupoalbafer.com.br; rivaldo@grupoalbafer.com.br; wilson.nishimura@grupoalbafer.com.br';
                $mensagem   = "A transportadora do pedido <b>".$id_pedido_venda."</b> do cliente <b>'".$campos[0]['cliente']."'</b> foi alterada de <b>'".$campos[0]['transportadora']."'</b> para <b>'".$campos_transp[0]['transportadora_new']."'</b>.<br><br>Existem Notas Fiscais não despachadas para este cliente => <font color='darkblue'><b>".$notas_existentes."</b></font>.";
                comunicacao::email('erp@grupoalbafer.com.br', $destino, '', 'Mudança de Transportadora no Pedido', $mensagem);
                return 1;
            }
        break;
    }
}

//Aqui nessa função eu faço a busca de todos os Orçamentos que estão com o Prazo Médio irregular em comparação ao do Pedido ...
function orcs_prazo_medio_irregular($id_pedido_venda, $prazo_medio) {
    $diferenca_prazo_medio_maximo_entre_pedido_nf = genericas::variavel(78);
    
    $sql = "SELECT DISTINCT(ov.`id_orcamento_venda`) 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
            WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' 
            AND (ov.`prazo_medio` + $diferenca_prazo_medio_maximo_entre_pedido_nf) < '$prazo_medio' LIMIT 1 ";
    $campos = bancos::sql($sql);
    return count($campos);
}

function modificar_representantes_livre_debito($id_pedido_venda, $chkt_livre_debito) {
    //Verifico se esse Pedido possui pelo menos um Item ...
    $sql = "SELECT id_pedido_venda_item 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda` = '$id_pedido_venda' ";
    $campos_itens = bancos::sql($sql);
    if(count($campos_itens) > 0) {
        //Primeira coisa que eu faço, é verificar se o Pedido é realmente "Livre de Débito" ...
	$sql = "SELECT ged.id_empresa_divisao, pvi.id_orcamento_venda_item, pvi.id_produto_acabado, pv.id_cliente, pv.livre_debito, pvi.id_pedido_venda_item 
                FROM `pedidos_vendas` pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                WHERE pv.`id_pedido_venda` = '$id_pedido_venda' ";
	$campos_pedidos = bancos::sql($sql);
        $linhas_pedidos = count($campos_pedidos);
	/*Se o Pedido foi marcado como sendo Livre de Débito, então é preciso modificar todos os 
        representantes do Orçamento e do Pedido como sendo "Direto" para que não seja 
	paga comissão alguma para eles ...*/
	if($chkt_livre_debito == 'S') {
            for($i = 0; $i < $linhas_pedidos; $i++) {
                //Modifico os Representantes do Orçamento para "Direto" ....
                $sql = "UPDATE `orcamentos_vendas_itens` SET `id_representante` = '1' WHERE `id_orcamento_venda_item` = '".$campos_pedidos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
                bancos::sql($sql);
            }
            //Modifico os Representantes do Pedido para "Direto" ....
            $sql = "UPDATE `pedidos_vendas_itens` SET `id_representante` = '1' WHERE `id_pedido_venda` = '$id_pedido_venda' ";
            bancos::sql($sql);
            //Faço a Marcação no Pedido como sendo Livre de Débito ...
            $sql = "UPDATE `pedidos_vendas` SET `livre_debito` = '$chkt_livre_debito' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
            bancos::sql($sql);
	/*Se no Pedido foi desmarcada a opção Livre de Débito, então é preciso modificar todos os 
        representantes do Orçamento e do Pedido como sendo o Representante "Original" para que 
	esses recebam comissão normalmente ...*/
	}else if($chkt_livre_debito == 'N') {
            for($i = 0; $i < $linhas_pedidos; $i++) {
                //Aqui eu busco o Representante do determinado Cliente e Divisão do PA ...
                $sql = "SELECT id_representante 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_cliente` = '".$campos_pedidos[$i]['id_cliente']."' 
                        AND `id_empresa_divisao` = '".$campos_pedidos[$i]['id_empresa_divisao']."' LIMIT 1 ";
                $campos_representantes = bancos::sql($sql);
                //Modifico os Representantes do Orçamento para o "Original" ...
                $sql = "UPDATE `orcamentos_vendas_itens` SET `id_representante` = '".$campos_representantes[0]['id_representante']."' WHERE `id_orcamento_venda_item` = '".$campos_pedidos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
                bancos::sql($sql);
                //Modifico os Representantes do Pedido para o "Original" ...
                $sql = "UPDATE `pedidos_vendas_itens` SET `id_representante` = '".$campos_representantes[0]['id_representante']."' WHERE `id_pedido_venda_item` = '".$campos_pedidos[$i]['id_pedido_venda_item']."' LIMIT 1 ";
                bancos::sql($sql);
            }
            //Faço a Desmarcação no Pedido como sendo Livre de Débito ...
            $sql = "UPDATE `pedidos_vendas` SET `livre_debito` = '$chkt_livre_debito' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
            bancos::sql($sql);
	}
    }else {//Se não existir nenhum Item simplesmente ...
        //Marco o Pedido como sendo ou não sendo Livre de Débito ...
        $sql = "UPDATE `pedidos_vendas` SET `livre_debito` = '$chkt_livre_debito' WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        bancos::sql($sql);
    }
}
/**********************************************************************/

if($_POST['passo'] == 1) {
/*********************************Controle com os Checkbox*********************************/
    $chkt_livre_debito 	= (!empty($chkt_livre_debito)) 			? 'S' : 'N';
    $expresso 		= (!empty($_POST['chkt_expresso'])) 		? 'S' : 'N';
    $projecao_vendas 	= (!empty($_POST['chkt_projecao_vendas'])) 	? 'S' : 'N';
    $projecao_apv       = (!empty($_POST['chkt_projecao_apv'])) 	? 'S' : 'N';
    $liberado           = (!empty($_POST['chkt_liberar_pedido'])) 	? 1 : 0;
/********************************************************************************************/
/*Se o usuário que estiver liberando o Pedido, for diferente do Roberto, Wilson, Dárcio, Wilson Japonês e Netto ... 
então não é permitido a liberação do Pedido ... - foi feita essa segurança pois houve uma situação muito 
estranho de existir um Pedido que foi liberado por um Vendedor ...*/
    if($_SESSION['id_funcionario'] != 62 && $_SESSION['id_funcionario'] != 68 && $_SESSION['id_funcionario'] != 98 && $_SESSION['id_funcionario'] != 136 && $_SESSION['id_funcionario'] != 147) $liberado = 0;
/***************************************************************************************************/
/**************************************CNPJ ou CPF do Cliente***************************************/
/***************************************************************************************************/
//Aqui eu verifico se o Cliente possui algum CNPJ quando for do Brasil ...
    if($liberado == 1) {
        $sql = "SELECT c.`id_cliente` 
                FROM `pedidos_vendas` pv 
                INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND c.`cnpj_cpf` = '' AND c.id_pais = '31' 
                WHERE pv.`id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
        $campos_cnpj = bancos::sql($sql);
        if(count($campos_cnpj) == 1) {//Se o "CNPJ ou CPF" = 0, então não posso estar liberando o Pedido ...
            $liberado = 0;
            $alert_registrar_follow_up = 3;//Utilizada mais abaixo
        }
/***************************************************************************************************/
/*****************************************Itens de Pedido*******************************************/
/***************************************************************************************************/
//Aqui eu verifico se o Pedido possui pelo menos 1 item ...
        $sql = "SELECT COUNT(`id_pedido_venda_item`) 
                FROM `pedidos_vendas_itens` 
                WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
        $campos_itens_pedidos = bancos::sql($sql);
        if(count($campos_itens_pedidos) == 0) {//Se o Pedido não possuir nenhum Item, então não posso estar liberando ...
            $liberado = 0;
            $alert_registrar_follow_up = 4;//Utilizada mais abaixo
        }
    }
    $data_sys       = date('Y-m-d H:i:s');
    
    //Com essa função eu atualizo o Prazo Médio do Pedido de Venda ...
    $prazo_medio    = intermodular::prazo_medio($_POST['txt_vencimento1'], $_POST['txt_vencimento2'], $_POST['txt_vencimento3'], $_POST['txt_vencimento4']);
    
    /*Se o funcionário logado for diferente do Roberto Chefe 62 e Dárcio 98 porque programa, 
    então o sistema faz Comparação do Prazo Médio do ORC ...*/
    if($_SESSION['id_funcionario'] != 62 && $_SESSION['id_funcionario'] != 98) {
        $prazo_medio_irregular 	= orcs_prazo_medio_irregular($_POST['id_pedido_venda'], $prazo_medio);
    }

    /*Se existir pelo menos 1 orçamento que está com o Prazo Médio Irregular em Comparação ao do Pedido, o sistema não permite 
    alterar os dados de Cabeçalho do Pedido e retorna uma mensagem informando o usuário ...*/
    if($prazo_medio_irregular > 0) {
        $valor = 3;
    }else {//Não existe nenhum 1 Orçamento com o Prazo Médio Irregular, sendo assim eu posso alterar normal os dados de cabeçalho ...
        $faturar_em     = data::datatodate($_POST['txt_faturar_em'], '-');
//Verificação para ver se o Pedido está liberado
        if(vendas::situacao_pedido($_POST['id_pedido_venda']) == 1) {//Está liberado, então ñ posso alterar o cabeçote
            /*Nesse caso eu não posso alterar dados do cabeçalho, pois o pedido está liberado, e sendo assim
            eu só posso mudar o modo de venda do pedido e desliberar o pedido*/
            $sql = "UPDATE `pedidos_vendas` SET `id_funcionario` = '$_SESSION[id_funcionario]', `faturar_em` = '$faturar_em', `vencimento1` = '$_POST[txt_vencimento1]', `vencimento2` = '$_POST[txt_vencimento2]', `vencimento3` = '$_POST[txt_vencimento3]', `vencimento4` = '$_POST[txt_vencimento4]', `prazo_medio` = '$prazo_medio', `liberado` = '$liberado', `projecao_vendas` = '$projecao_vendas', `projecao_apv` = '$projecao_apv', `expresso` = '$expresso', `modo_venda` = '$_POST[opt_modo_venda]', `data_sys` = '$data_sys' WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
            bancos::sql($sql);
            
            $valor = ($liberado == 1) ? 1 : 2;
        }else {//Pode realizar o processo normalmente ...
            $data_emissao = data::datatodate($_POST['txt_data_emissao'], '-');//A princípio esse é o próprio campo "txt_data_emissao" digitado pelo usuário ...
/********************************************************************************************/
//Aki verifica se o Pedido já foi liberado pelo menos 1 vez
            $sql = "SELECT `ja_liberado` 
                    FROM `pedidos_vendas` 
                    WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $ja_liberado = $campos[0]['ja_liberado'];
//A data de Emissão para a assumir a Data Atual do Pedido
            if($liberado == 1) {
                if($ja_liberado == 0) {//Como não tinha sido, este agora assume a posição de liberado
                    $data_emissao = date('Y-m-d');
                    /*Se a Data do Faturar em, passar a ser menor do que a Data de Emissão devido as regras, esse também 
                    passa a assumir a Data de Emissão, porque este nunca pode ser menor do que a Data de Emissão ...*/
                    if($faturar_em < $data_emissao) $faturar_em = $data_emissao;
                    $ja_liberado = 1;
                }
            }
            //Significa que não tem itens de pedidos, sendo assim ainda é possível alterar a Empresa do Pedido ...
            if($_POST['qtde_itens_pedidos'] == 0) $condicao_empresa = " `id_empresa` = '$_POST[cmb_empresa]', ";
            //Esse objeto $opt_modo_venda, só aparece p/ alguns usuários, por isso que faço o Tratamento abaixo ...
            if(!empty($_POST['opt_modo_venda'])) $condicao_modo_venda = " `modo_venda` = '$_POST[opt_modo_venda]', ";

            $sql = "UPDATE `pedidos_vendas` SET `id_funcionario` = '$_SESSION[id_funcionario]', `id_transportadora` = '$_POST[cmb_cliente_transportadora]', `id_cliente_contato` = '$_POST[cmb_cliente_contato]', $condicao_empresa `finalidade` = '$_POST[cmb_finalidade]', `num_seu_pedido` = '$_POST[txt_seu_pedido_numero]', `faturar_em` = '$faturar_em', `data_emissao` = '$data_emissao', `vencimento1` = '$_POST[txt_vencimento1]', `vencimento2` = '$_POST[txt_vencimento2]', `vencimento3` = '$_POST[txt_vencimento3]', `vencimento4` = '$_POST[txt_vencimento4]', `prazo_medio` = '$prazo_medio', `liberado` = '$liberado', `ja_liberado` = '$ja_liberado', $condicao_modo_venda `projecao_vendas` = '$projecao_vendas', `projecao_apv` = '$projecao_apv', `expresso` = '$expresso', `data_sys` = '$data_sys' WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 1;
        }
    }
    
    if($liberado == 1) {//Se o Pedido foi liberado ...
        //Verifico se o Pedido que foi liberado é >= à R$ 5.000,00 independente de sua moeda ...
        $sql = "SELECT SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS valor_total_produtos_moeda_pedido, /*99% dos casos são em R$ */
                IF(c.`id_pais` <> '31', SUM(pvi.`qtde` * pvi.`preco_liq_final` * ov.`valor_dolar`), 
                SUM(pvi.`qtde` * pvi.`preco_liq_final`)) AS valor_total_produtos_moeda_reais 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
                WHERE pvi.`id_pedido_venda` = '$_POST[id_pedido_venda]' 
                GROUP BY pvi.`id_pedido_venda` ";
        $campos_valor_total_produtos = bancos::sql($sql);
        
        if($chkt_livre_debito == 'S' || $campos_valor_total_produtos[0]['valor_total_produtos_moeda_pedido'] >= 5000) {//Livre de Débito ou >= à 5000 ...
            //Indepente de uma das 2 situações desse IF, eu preciso fazer a busca de alguns dados do Pedido p/ passar por e-mail via parâmetro ...
            $sql = "SELECT IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, pv.* 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
                    WHERE pv.`id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
            $campos_pedido  = bancos::sql($sql);
            $cliente        = $campos_pedido[0]['cliente'];
            //Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
            $id_empresa_pedido  = $campos_pedido[0]['id_empresa'];
            $empresa            = genericas::nome_empresa($id_empresa_pedido);

            if($campos_pedido[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos_pedido[0]['vencimento4'];
            if($campos_pedido[0]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos_pedido[0]['vencimento3'].$prazo_faturamento;
            if($campos_pedido[0]['vencimento2'] > 0) {
                $prazo_faturamento = $campos_pedido[0]['vencimento1'].'/'.$campos_pedido[0]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos_pedido[0]['vencimento1'] == 0) ? 'À vista' : $campos_pedido[0]['vencimento1'];
            }
            
            if($_POST['cmb_empresa'] == 4) {//Grupo ...
                $rotulo_sgd = ' - SGD';
            }else {
                $rotulo_sgd = ' - NF';
                //Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
                if($campos[0]['finalidade'] == 'C') {
                    $finalidade = 'CONSUMO';
                }else if($campos[0]['finalidade'] == 'I') {
                    $finalidade = 'INDUSTRIALIZAÇÃO';
                }else {
                    $finalidade = 'REVENDA';
                }
                $rotulo_sgd.= '/'.$finalidade;
            }
            $prazo_faturamento.= $rotulo_sgd;
            
            /**Busca do IP Externo que está cadastrado em alguma Empresa aqui do Sistema ...**/
            $sql = "SELECT `ip_externo` 
                    FROM `empresas` 
                    WHERE `ip_externo` <> '' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            /*Se encontrar um IP Externo cadastrado, o conteúdo do e-mail apontará p/ esse IP "que é a preferência", 
            do contrário o IP será da onde o usuário está acessando o ERP $_SERVER['HTTP_HOST'] ...*/
            $ip_externo     = (count($campos_empresa) == 1) ? $campos_empresa[0]['ip_externo'] : $_SERVER['HTTP_HOST'];
            
            /************************E-mail************************/
            $conteudo_email = "O Pedido N.º <a href='http://192.168.1.253/erp/albafer/modulo/vendas/pedidos/itens/itens.php?id_pedido_venda=$_POST[id_pedido_venda]'>".$_POST['id_pedido_venda']."</a> / 
                        <a href='http://".$ip_externo."/erp/albafer/modulo/vendas/pedidos/itens/itens.php?id_pedido_venda=$_POST[id_pedido_venda]'>".$_POST['id_pedido_venda']." Ext</a>
                        <br/><b>Empresa: </b>".$empresa." (".$prazo_faturamento.") <br/><b>Cliente: </b>".$cliente;
        
            if($chkt_livre_debito == 'S') {//Foi marcada a opção Livre de Débito Propag / Mkt então eu faço alguns controles ...
                //1) Aqui eu faço a troca do representante dos Itens do Orçamento - Pedido p/ o Representante Direto ...
                $sql = "UPDATE `orcamentos_vendas_itens` ovi 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_orcamento_venda_item` = ovi.`id_orcamento_venda_item` 
                        SET ovi.`id_representante` = '1' 
                        WHERE pvi.`id_pedido_venda` = '$_POST[id_pedido_venda]' ";
                bancos::sql($sql);

                //Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
                $destino = $cabec_pedido_livre_debito;
                $assunto = 'Pedido de Venda com marcação Livre de Débito Propag / Mkt';

                $conteudo_email.= '<br/><br/><font color="red" size="5">Marcação Livre de Débito Propag / Mkt</font>';
                $conteudo_email.= '<br/><br/><b>Justificativa: </b>'.$justificativa.'<br/>'.date('d/m/Y H:i:s').' - '.$PHP_SELF;
            }else if($campos_valor_total_produtos[0]['valor_total_produtos_moeda_pedido'] >= 5000) {//>= à 5000 ...
                //Mando e-mail só p/ o Roberto analisar a Produção ...
                $destino = 'roberto@grupoalbafer.com.br';
                $assunto = 'Pedido de Venda com Valor Total dos Produtos >= 5.000,00';
                
                if($campos_valor_total_produtos[0]['valor_total_produtos_moeda_pedido'] != $campos_valor_total_produtos[0]['valor_total_produtos_moeda_reais']) {
                    $conteudo_email.= '<br/><br/><font color="red" size="5">Pedido de Venda com o Valor Total dos Produtos em U$ '.number_format($campos_valor_total_produtos[0]['valor_total_produtos_moeda_pedido'], 2, ',', '.').'</font>';
                }
                $conteudo_email.= '<br/><br/><font color="red" size="5">Pedido de Venda com o Valor Total dos Produtos em R$ '.number_format($campos_valor_total_produtos[0]['valor_total_produtos_moeda_reais'], 2, ',', '.').'</font>';
                $conteudo_email.= '<br/><br/>'.date('d/m/Y H:i:s').' - '.$PHP_SELF;
            }
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $conteudo_email);
        }
    }
    
    //Sempre faço essa verificação no Pedido independente de ser "Livre de Débito" ...
    modificar_representantes_livre_debito($_POST['id_pedido_venda'], $chkt_livre_debito);
//Executo esta função somente no final para ter certeza que foi liberado ou não o pedido para refazer o calculo de comissão extra ...
//Roberto, Wilson, Dárcio, Wilson Japonês e Netto ...
    /*if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
        vendas::comissao_extra_meta_atingida($id_pedido_venda);
    }*/
    if($liberado == 1) vendas::verificar_ml_baixa($_POST['id_pedido_venda']);//Quando é liberado verifica se a margem está baixa ...

    if($_SESSION['id_funcionario'] != 136) {
        /*Aqui eu gravo todas as Pendências existentes de Orçamento p/ que o Representante dono
        do Cliente, possa resolver depois as futuras ocorrências ...*/
        vendas::vendedores_pendencias('', $_POST['id_pedido_venda']);
    }
?>
    <Script Language='JavaScript'>
        var alert_registrar_follow_up = '<?=$alert_registrar_follow_up;?>'
        if(alert_registrar_follow_up == 3) {
            alert('ESTE PEDIDO NÃO PODE SER LIBERADO !\nO CLIENTE DESTE PEDIDO NÃO POSSUI CNPJ E CPF !!!')
        }else if(alert_registrar_follow_up == 4) {
            alert('ESTE PEDIDO NÃO PODE SER LIBERADO !\nESTE PEDIDO NÃO POSSUI NENHUM ITEM !!!')
        }
        window.location = 'alterar_cabecalho.php?id_pedido_venda=<?=$_POST['id_pedido_venda'];?>&valor=<?=$valor;?>'
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    </Script>
<?
}else {
    $id_pedido_venda = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_pedido_venda'] : $_GET['id_pedido_venda'];
/*****************************************************************************************/
/***********************************Pequenos Controles************************************/
/*****************************************************************************************/
    if(!empty($id_cliente_contato)) {//Exclusão de Contatos ...
        $sql = "UPDATE `clientes_contatos` SET `ativo` = '0' WHERE `id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
        bancos::sql($sql);
    }
    if(!empty($id_transportadora_excluir)) {//Exclusão de Transportadoras ...
        //Se a Transportadora for N/Carro ou Retira, então não pode ser excluido do Cliente
        if($id_transportadora_excluir != 795 && $id_transportadora_excluir != 796) {
            $sql = "DELETE FROM `clientes_vs_transportadoras` WHERE `id_cliente` = '$id_cliente' AND `id_transportadora` = '$id_transportadora_excluir' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    if(!empty($_POST['cmb_cliente_transportadora'])) {//Significa que houve mudança de Transportadora na Combo ...
        $valor = seguranca_com_transportadora($id_pedido_venda, $_POST['cmb_cliente_transportadora']);
    }
/*****************************************************************************************/
//Aqui Trago dados do Pedido de Venda passado por parâmetro ...
    $sql = "SELECT pv.*, c.`id_pais`, c.`forma_pagamento`, c.`tipo_suframa`, c.`cod_suframa`, c.`suframa_ativo`, c.`razaosocial` 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pv.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_transportadora 	= $campos[0]['id_transportadora'];
    //Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
    $id_empresa_pedido 	= $campos[0]['id_empresa'];
    $id_contacorrente 	= $campos[0]['id_contacorrente'];
    $forma_pagamento    = $campos[0]['forma_pagamento'];
    $tipo_suframa       = $campos[0]['tipo_suframa'];
    $cod_suframa        = $campos[0]['cod_suframa'];
    $suframa_ativo      = $campos[0]['suframa_ativo'];
    $id_pais            = $campos[0]['id_pais'];
    $id_cliente         = $campos[0]['id_cliente'];
    $id_cliente_contato = $campos[0]['id_cliente_contato'];
    $finalidade 	= $campos[0]['finalidade'];
    if($campos[0]['faturar_em'] != '0000-00-00') {//Coloca no formato de Data
        $faturar_em     = data::datetodata($campos[0]['faturar_em'], '/');
    }else {
        $faturar_em     = '';
    }
    $seu_pedido_numero 	= $campos[0]['num_seu_pedido'];
    $data_emissao       = data::datetodata($campos[0]['data_emissao'], '/');
    //Prazos
    $vencimento1 = $campos[0]['vencimento1'];
    $data_vencimento1   = data::adicionar_data_hora($faturar_em, $vencimento1);

    if($campos[0]['vencimento2'] == 0) {
        $vencimento2    = '';
        $data_vencimento2 = '';
    }else {
        $vencimento2    = $campos[0]['vencimento2'];
        $data_vencimento2 = data::adicionar_data_hora($faturar_em, $vencimento2);
    }
    if($campos[0]['vencimento3'] == 0) {
        $vencimento3    = '';
        $data_vencimento3 = '';
    }else {
        $vencimento3    = $campos[0]['vencimento3'];
        $data_vencimento3 = data::adicionar_data_hora($faturar_em, $vencimento3);
    }
    if($campos[0]['vencimento4'] == 0) {
        $vencimento4    = '';
        $data_vencimento4 = '';
    }else {
        $vencimento4    = $campos[0]['vencimento4'];
        $data_vencimento4 = data::adicionar_data_hora($faturar_em, $vencimento4);
    }
    $prazo_medio            = $campos[0]['prazo_medio'];
    $status                 = $campos[0]['status'];

//Significa que o Pedido é Livre de Débito Propag / Mkt
    $checked_livre_debito       = ($campos[0]['livre_debito'] == 'S') ? 'checked' : '';
    //Significa que o Pedido foi liberado ...
    $checked_liberar 		= ($campos[0]['liberado'] == 1) ? 'checked' : '';
    $checked_expresso 		= ($campos[0]['expresso'] == 'S') ? 'checked' : '';
    $checked_projecao_vendas 	= ($campos[0]['projecao_vendas'] == 'S') ? 'checked' : '';
    $checked_projecao_apv       = ($campos[0]['projecao_apv'] == 'S') ? 'checked' : '';

    //Verifico a Qtde de Itens existentes no Pedido, se tiver pelo menos 1 não pode alterar a Empresa e o Tipo de Nota ...
    $sql = "SELECT pvi.`id_pedido_venda_item`, ovi.`id_orcamento_venda` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' ";
    $campos_itens       = bancos::sql($sql);
    $qtde_itens_pedidos = count($campos_itens);
    //Se encontrou pelo menos 1 item, trago o id_orcamento_venda pq este será utilizando no Caso de Pedidos de Exportação ...
    if($qtde_itens_pedidos > 0) $id_orcamento_venda = $campos_itens[0]['id_orcamento_venda'];

    //Aqui eu verifico se existe algum Item que está em Queima de Estoque ...
    $sql = "SELECT COUNT(`id_pedido_venda_item`) AS total_registro 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
            AND ovi.`queima_estoque` = 'S' 
            WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' ";
    $campos_queima_estoque 	= bancos::sql($sql);
    $qtde_itens_queima_estoque  = $campos_queima_estoque[0]['total_registro'];

    //Aqui eu verifico se existe algum Item que não está c/ Margem de Lucro Estimada ...
    $sql = "SELECT id_pedido_venda_item 
            FROM `pedidos_vendas_itens` 
            WHERE `id_pedido_venda` = '$id_pedido_venda' 
            AND `margem_lucro_estimada` = '0' LIMIT 1 ";
    $campos_ml_estimada     = bancos::sql($sql);
    //$itens_ml_estimada      = count($campos_ml_estimada);
    $itens_ml_estimada      = 0;

    /*******************Controle com a Liberação de Pedido - Itens ESP*******************/
    $esp_nao_importados_totalmente = 0;//Valor Default, p/ não dar erro de JavaScript ...
    //Aqui eu verifico se existem PA´s ESP nesse Pedido atual ...
    $sql = "SELECT pvi.`id_orcamento_venda_item` 
            FROM `pedidos_vendas_itens` pvi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` AND pa.`referencia` = 'ESP' 
            WHERE pvi.id_pedido_venda = '$id_pedido_venda' ";
    $campos_pas_esp = bancos::sql($sql);
    $linhas_pas_esp = count($campos_pas_esp);
    for($i = 0; $i < $linhas_pas_esp; $i++) {
        $id_orcamento_venda_item_atual = $campos_pas_esp[$i]['id_orcamento_venda_item'];
        //Verifico se esse item de Orc já foi importado de forma 100% nos vários Pedidos ...
        $sql = "SELECT `id_orcamento_venda_item` 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item_atual' 
                AND `status` = '2' LIMIT 1 ";
        $campos_item_orc_importado_total = bancos::sql($sql);
        /*Se já foi importado de forma 100%, então eu verifico se a Diferença de Prazo 
        de todos os Pedidos ultrapassam a $dif_max_dias_prog_esp dias ...*/
        if(count($campos_item_orc_importado_total) == 1) {
            //Se sim, verifico em quais Pedidos que o item atual está importado ...
            $sql = "SELECT pv.`faturar_em`, pvi.`id_orcamento_venda_item` 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                    WHERE pvi.`id_orcamento_venda_item` = '".$campos_pas_esp[$i]['id_orcamento_venda_item']."' ";
            $campos_pedidos_importados = bancos::sql($sql);
            $linhas_pedidos_importados = count($campos_pedidos_importados);
            for($j = 0; $j < $linhas_pedidos_importados; $j++) {
                //Verifico se já cheguei no último item do mesmo Orçamento do Loop ...
                if($id_orcamento_venda_item_atual != $campos_pedidos_importados[$j + 1]['id_orcamento_venda_item']) {
                    $vetor_faturar_em[] = $campos_pedidos_importados[$j]['faturar_em'];
                    sort($vetor_faturar_em);//Ordeno por ordem de Data do Faturar em ...
                    for($k = 0; $k < count($vetor_faturar_em); $k++) {
                        if($k == 0) {//Aqui eu guardo a Data Inicial ...
                            $data_inicial_faturar_em    = $vetor_faturar_em[$k];
                        }else if($k == (count($vetor_faturar_em) - 1)) {//Aqui eu guardo a Data Final ...
                            $data_final_faturar_em      = $vetor_faturar_em[count($vetor_faturar_em) - 1];
                        }
                    }
                    $diferenca_data             = data::diferenca_data($data_inicial_faturar_em, $data_final_faturar_em);
                    $diferenca_dias_faturar_em  = $diferenca_data[0];
                    if($diferenca_dias_faturar_em > $dif_max_dias_prog_esp) {
                        $esp_nao_importados_totalmente  = 2;//Ñ posso liberar este Pedido ...
                        break;//P/ sair do Loop
                    }
                    //O item atual passa a assumir o item do próximo Loop ...
                    $id_orcamento_venda_item_atual = $campos_pedidos_importados[$j + 1]['id_orcamento_venda_item'];
                    //Deleto desse vetor valores desse Pedido atual p/ ñ acumular c/ os valores do Próximo Pedido ...
                    unset($vetor_faturar_em);
                }else {//Ainda estou no mesmo Item do Orçamento ...
                    $vetor_faturar_em[] = $campos_pedidos_importados[$j]['faturar_em'];
                }
            }
        }else {//Este item ainda não foi importado de forma 100%, então ñ posso liberar este Pedido ...
            $esp_nao_importados_totalmente  = 1;
            break;//P/ sair do Loop
        }
    }
/************************************************************************************/
    //Aqui eu busco o "Valor Total dos Produtos" p/ verificar qual o Prazo Médio ideal p/ se dar na negociação ...
    $calculo_total_impostos = calculos::calculo_impostos(0, $id_pedido_venda, 'PV');
?>
<html>
<head>
<title>.:: Alterar Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/prazo_medio.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
<?
    if($qtde_itens_pedidos == 0) {//Não tem itens cadastrados
?>
//Empresa
        if(!combo('form', 'cmb_empresa', '', 'SELECIONE A EMPRESA !')) {
            return false
        }
<?
    }
?>
//Transportadora
    if(document.form.cmb_cliente_transportadora.value == '') {
        alert('SELECIONE A TRANSPORTADORA DO CLIENTE !')
        document.form.cmb_cliente_transportadora.focus()
        return false
    }
//Finalidade ...
    if(document.form.cmb_finalidade.value == '') {
        alert('SELECIONE A FINALIDADE !')
        document.form.cmb_finalidade.focus()
        return false
    }
//Contato do Cliente
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }
//Faturar em
    if(!data('form', 'txt_faturar_em', '4000', 'FATURAR')) {
        return false
    }
//Data de Emissão
    if(!data('form', 'txt_data_emissao', '4000', 'EMISSÃO')) {
        return false
    }
//Modo de Venda
//Aqui tem essa verificação, porque tem telas em que esse objeto não aparece
    if(typeof(document.form.opt_modo_venda) == 'object') {
        if(document.form.opt_modo_venda[0].checked == false && document.form.opt_modo_venda[1].checked == false) {
            alert('SELECIONE UM MODO DE VENDA !')
            return false
        }
    }
//Seu Pedido N.º
    if(!texto('form', 'txt_seu_pedido_numero', '1', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_/ ', 'SEU PEDIDO N.º', '2')) {
        return false
    }
//Vencimento 1
    if(document.form.txt_vencimento1.value != '') {
        if(!texto('form', 'txt_vencimento1', '1', '0123456789', 'VENCIMENTO 1', '2')) {
            return false
        }
    }
//Vencimento 2
    if(document.form.txt_vencimento2.value != '') {
        if(!texto('form', 'txt_vencimento2', '1', '0123456789', 'VENCIMENTO 2', '2')) {
            return false
        }
    }
//Vencimento 3
    if(document.form.txt_vencimento3.value != '') {
        if(!texto('form', 'txt_vencimento3', '1', '0123456789', 'VENCIMENTO 3', '2')) {
            return false
        }
    }
//Vencimento 4
    if(document.form.txt_vencimento4.value != '') {
        if(!texto('form', 'txt_vencimento4', '1', '0123456789', 'VENCIMENTO 4', '2')) {
            return false
        }
    }
/****************Comparação dos Vencimentos**********************/
    var vencimento1 = eval(document.form.txt_vencimento1.value)
    var vencimento2 = eval(document.form.txt_vencimento2.value)
    var vencimento3 = eval(document.form.txt_vencimento3.value)
    var vencimento4 = eval(document.form.txt_vencimento4.value)
//Comparando o Vencimento 2
    if(vencimento2 <= vencimento1) {
        alert('VENCIMENTO 2 INVÁLIDO !!! \n VALOR DO VENCIMENTO 2 MENOR OU IGUAL AO VALOR DO VENCIMENTO 1 !')
        document.form.txt_vencimento2.focus()
        document.form.txt_vencimento2.select()
        return false
    }
//Comparando o Vencimento 3
    if(vencimento3 <= vencimento2 || vencimento3 <= vencimento1) {
        alert('VENCIMENTO 3 INVÁLIDO !!! \n VALOR DO VENCIMENTO 3 MENOR OU IGUAL AO VALOR DO VENCIMENTO 2 OU \n VALOR DO VENCIMENTO 3 MENOR OU IGUAL AO VALOR DO VENCIMENTO 1 !')
        document.form.txt_vencimento3.focus()
        document.form.txt_vencimento3.select()
        return false
    }
//Comparando o Vencimento 4
    if(vencimento4 <= vencimento3 || vencimento4 <= vencimento2 || vencimento4 <= vencimento1) {
        alert('VENCIMENTO 4 INVÁLIDO !!! \n VALOR DO VENCIMENTO 4 MENOR OU IGUAL AO VALOR DO VENCIMENTO 3 OU \n VALOR DO VENCIMENTO 4 MENOR OU IGUAL AO VALOR DO VENCIMENTO 2 OU \n VALOR DO VENCIMENTO 4 MENOR OU IGUAL AO VALOR DO VENCIMENTO 1 !')
        document.form.txt_vencimento4.focus()
        document.form.txt_vencimento4.select()
        return false
    }
    //Aqui o sistema faz Comparação do Prazo Médio do Pedido ...
    var array_prazo_medio = prazo_medio('<?=$calculo_total_impostos['valor_total_produtos'];?>', '<?=$_SESSION['id_funcionario'];?>')
    if(array_prazo_medio['situacao_prazo'] != 1) return false//Significa que existe prazo Irregular nessa Negociação ...
/**************************************************************************************/	
    var data_emissao        = document.form.txt_data_emissao.value
    var faturar_em          = document.form.txt_faturar_em.value
    data_emissao            = data_emissao.substr(6, 4) + data_emissao.substr(3, 2) + data_emissao.substr(0, 2)
    faturar_em              = faturar_em.substr(6, 4) + faturar_em.substr(3, 2) + faturar_em.substr(0, 2)
    data_emissao            = eval(data_emissao)
    faturar_em              = eval(faturar_em)

    if(faturar_em < data_emissao) {
        alert('DATA PARA FATURAR INVÁLIDA !!!\n DATA PARA FATURAR MENOR DO QUE A DATA DE EMISSÃO !')
        document.form.txt_faturar_em.focus()
        document.form.txt_faturar_em.select()
        return false
    }

    /************Controle com a Parte de Queima de Estoque************/
    var qtde_itens_queima_estoque       = '<?=$qtde_itens_queima_estoque;?>'
    var maximo_dias_programar_queima    = 7

    /*Se existir pelo menos 1 item no Pedido que está em Queima de Estoque então a Data Programada máxima a ser 
    faturada é de até no máximo 7 dias ...*/
    if(qtde_itens_queima_estoque > 0) {
        dias_para_faturar = eval(diferenca_datas(document.form.txt_data_emissao.value, document.form.txt_faturar_em.value))
        if(dias_para_faturar > maximo_dias_programar_queima) {
            alert('DATA PARA FATURAR INVÁLIDA !!!\n\n ITEM(NS) EM EXCESSO DE ESTOQUE SÃO PROGRAMÁVEIS POR NO MÁX. '+maximo_dias_programar_queima+' DIA(S) !')
            document.form.txt_faturar_em.focus()
            document.form.txt_faturar_em.select()
            return false
        }
    }
    /*****************************************************************/
    if(typeof(document.form.chkt_liberar_pedido) == 'object') {
        if(document.form.chkt_liberar_pedido.checked == true) {
            if(typeof(document.form.opt_data_emissao) == 'object') {
                if(document.form.opt_data_emissao[0].checked == false && document.form.opt_data_emissao[1].checked == false) {
                    alert('SELECIONE UMA OPÇÃO PARA DATA DE EMISSÃO !')
                    document.form.opt_data_emissao[0].focus()
                    return false
                }
            }
        }
    }
    //Desabilito esses objetos p/ poder gravar no Banco de Dados ...
    document.form.txt_data_emissao.disabled = false
    document.form.passo.value = 1
    return true
}

function alterar_contato() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }else {
        nova_janela('../../classes/cliente/alterar_contatos.php?id_cliente_contato='+document.form.cmb_cliente_contato.value, 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

//Exclusão de Contatos
function excluir_contato() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            document.form.passo.value = 0
            document.form.txt_data_emissao.disabled = false
            document.form.id_cliente_contato.value = document.form.cmb_cliente_contato.value
            document.form.submit()
        }
    }
}

//Exclusão das Transportadoras
function excluir_transportadora() {
    if(document.form.cmb_cliente_transportadora.value == '') {
        alert('SELECIONE A TRANSPORTADORA DO CLIENTE !')
        document.form.cmb_cliente_transportadora.focus()
        return false
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            document.form.passo.value                       = 0
            document.form.txt_data_emissao.disabled         = false
            document.form.id_transportadora_excluir.value   = document.form.cmb_cliente_transportadora.value
            document.form.submit()
        }
    }
}

function atualizar() {
    document.form.passo.value = 0
    document.form.txt_data_emissao.disabled = false
    document.form.id_cliente_contato.value = ''
    document.form.submit()
}

function verificar(valor) {
    if(valor == 1) {//Vencimento 1
        if(document.form.txt_vencimento1.value == '') {
            document.form.txt_data_vencimento1.value = ''
        }else {
            nova_data('document.form.txt_faturar_em', 'document.form.txt_data_vencimento1', 'document.form.txt_vencimento1')
        }
    }else if(valor == 2) {//Vencimento 2
        if(document.form.txt_vencimento2.value == '') {
            document.form.txt_data_vencimento2.value = ''
        }else {
            nova_data('document.form.txt_faturar_em', 'document.form.txt_data_vencimento2', 'document.form.txt_vencimento2')
        }
    }else if(valor == 3) {//Vencimento 3
        if(document.form.txt_vencimento3.value == '') {
            document.form.txt_data_vencimento3.value = ''
        }else {
            nova_data('document.form.txt_faturar_em', 'document.form.txt_data_vencimento3', 'document.form.txt_vencimento3')
        }
    }else if(valor == 4) {//Vencimento 4
        if(document.form.txt_vencimento4.value == '') {
            document.form.txt_data_vencimento4.value = ''
        }else {
            nova_data('document.form.txt_faturar_em', 'document.form.txt_data_vencimento4', 'document.form.txt_vencimento4')
        }
    }
}

function enviar() {
    if(document.form.chkt_liberar_pedido.checked == true) {//O pedido está sendo liberado ...
        var esp_nao_importados_totalmente   = eval('<?=$esp_nao_importados_totalmente;?>')
        var itens_ml_estimada               = eval('<?=$itens_ml_estimada;?>')
        /*Se a validação desse cabeçalho não estiver de acordo, ou existirem Itens ESP(s) que não foram 
        importados com suas qtdes em 100% do orçamento p/ Pedidos então ñ é possível liberar esse Pedido ...*/
        if(esp_nao_importados_totalmente == 1) {
            alert('PEDIDO NÃO PODE SER LIBERADO !!!\n\nEXISTE(M) ITEM(NS) ESP COM QTDE DE PEDIDO MENOR QUE A QTDE DO ORÇAMENTO !')
            controlar_checkbox()
            return false
        }else if(esp_nao_importados_totalmente == 2) {
            alert('PEDIDO NÃO PODE SER LIBERADO !!!\n\nEXISTE(M) ITEM(NS) ESP COM PRAZO DE PROGRAMAÇÃO MAIOR QUE <?=$dif_max_dias_prog_esp;?> DIAS ENTRE A PRIMEIRA E A ÚLTIMA ENTREGA !')
            controlar_checkbox()
            return false
        }
        //Se existir algum Item que está sem Margem de Lucro Estimada, então não é possível Liberar o Pedido ...
        if(itens_ml_estimada == 1) {
            alert('EXISTE(M) ITEM(NS) QUE ESTÃO SEM MARGEM DE LUCRO ESTIMADA !')
            controlar_checkbox()
        }
        /***********************Novo Controle desenvolvido a partir do dia 10/04/2015***********************/
        var data_emissao    = document.form.txt_data_emissao.value
        var ultimos_x_dias  = '<?=data::adicionar_data_hora(date('d/m/Y'), -$prazo_validade_pedido);?>'
        
        data_emissao        = data_emissao.substr(6, 4) + data_emissao.substr(3, 2) + data_emissao.substr(0, 2)
        ultimos_x_dias      = ultimos_x_dias.substr(6, 4) + ultimos_x_dias.substr(3, 2) + ultimos_x_dias.substr(0, 2)
        
        if(data_emissao <= ultimos_x_dias) {
            var resposta = confirm('ESTE PEDIDO ESTÁ FORA DO PRAZO DE VALIDADE DE '+<?=$prazo_validade_pedido;?>+' DIAS !!!\n\nOS CUSTOS ESTÃO DENTRO DO PADRÃO ACEITÁVEL ?')
            if(resposta == false) {//O usuário cancelou a Liberação do Pedido, porque tem algo errado ...
                controlar_checkbox()
                return false
            }
        }
        /***************************************************************************************************/
        if(!validar()) {
            controlar_checkbox()
        }else {
            document.form.passo.value = 1
            document.form.txt_data_emissao.disabled = false
            document.form.submit()
        }
    }else {//O pedido está sendo desliberado ...
        var status = eval('<?=$status;?>')
        if(status == 2) {
            alert('PEDIDO NÃO PODE SER DESLIBERADO !!!\n\nESTE PEDIDO JÁ FOI TOTALMENTE FATURADO !')
            controlar_checkbox()
            return false
        }
        document.form.passo.value = 1
        document.form.txt_data_emissao.disabled = false
        document.form.submit()
    }
}

function controlar_checkbox() {
    if(document.form.chkt_liberar_pedido.checked == true) {//Fica deschecado
        document.form.chkt_liberar_pedido.checked = false
    }else {//Fica checado
        document.form.chkt_liberar_pedido.checked = true
    }
}

function controle_data_emissao() {
    if(document.form.opt_data_emissao[0].checked == true) {
        document.form.txt_data_emissao.value = '<?=$data_emissao;?>'
    }else {
        document.form.txt_data_emissao.value = '<?=date('d/m/Y');?>'
    }
}
</Script>
<body onload='if(document.form.txt_data_emissao.disabled == false) {document.form.txt_data_emissao.focus()}'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo' onclick='atualizar()'>
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='id_cliente_contato'>
<input type='hidden' name='id_transportadora_atrelar'>
<input type='hidden' name='id_transportadora_excluir'>
<input type='hidden' name='qtde_itens_pedidos' value='<?=$qtde_itens_pedidos;?>'>
<!--Caixa que faz o controle de contatos inclusos deste Cliente nesse Pedido-->
<input type='hidden' name='controle' onclick='verificar(1);verificar(2);verificar(3);verificar(4)'>
<!--Caixa para controle dos Combos-->
<input type='hidden' name='ja_submeteu' value='1'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cabeçalho do Pedido N.° 
            <font color='yellow'>
                <?=$id_pedido_venda;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente:
        </td>
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
        <?
            if($qtde_itens_pedidos == 0) {//Não tem itens cadastrados
//Aqui busca as empresas
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ";
                $campos_empresas = bancos::sql($sql);
                $linhas = count($campos_empresas);
        ?>
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
        <?
                    for($i = 0; $i < $linhas; $i++) {
                        $tipo_nota = ($campos_empresas[$i]['id_empresa'] == 1 || $campos_empresas[$i]['id_empresa'] == 2) ? ' (NF)' : ' (SGD)';
//Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                        if(!empty($_POST['cmb_empresa'])) {
                            if($_POST['cmb_empresa'] == $campos_empresas[$i]['id_empresa']) {
        ?>
                <option value="<?=$campos_empresas[$i]['id_empresa'];?>" selected><?=$campos_empresas[$i]['nomefantasia'].$tipo_nota;?></option>
        <?
                            }else {
        ?>
                <option value="<?=$campos_empresas[$i]['id_empresa'];?>"><?=$campos_empresas[$i]['nomefantasia'].$tipo_nota;?></option>
        <?
                            }
						
//Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                        }else {//Só lista
						
                            if($id_empresa_pedido == $campos_empresas[$i]['id_empresa']) {
        ?>
                <option value="<?=$campos_empresas[$i]['id_empresa'];?>" selected><?=$campos_empresas[$i]['nomefantasia'].$tipo_nota;?></option>
        <?
                            }else {
        ?>
                <option value="<?=$campos_empresas[$i]['id_empresa'];?>"><?=$campos_empresas[$i]['nomefantasia'].$tipo_nota;?></option>
        <?
                            }
                        }
                    }
        ?>
            </select>
        <?
                }else {//Tem 1 item cadastrado
                    $tipo_nota = ($id_empresa_pedido == 1 || $id_empresa_pedido == 2) ? ' (NF)' : ' (SGD)';

                    $sql = "SELECT nomefantasia 
                            FROM `empresas` 
                            WHERE `id_empresa` = '$id_empresa_pedido' LIMIT 1 ";
                    $campos_empresa = bancos::sql($sql);
                    echo $campos_empresa[0]['nomefantasia'].$tipo_nota;
        ?>
            <!--Esse hidden é imprescendível porque senão acaba atrapalhando no campo nota_sgd do Cabeçalho ...-->
            <input type='hidden' name='cmb_empresa' value='<?=$id_empresa_pedido;?>'>
        <?
                }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Transportadora:</b>
        </td>
        <td>
            <select name="cmb_cliente_transportadora" title="Selecione a Transportadora do Cliente" onChange="document.form.submit()" class='combo'>
            <?
                $sql = "SELECT t.id_transportadora, IF(t.nome_fantasia = '', t.nome, t.nome_fantasia) AS transportadora 
                        FROM `clientes_vs_transportadoras` ct 
                        INNER JOIN `transportadoras` t ON t.id_transportadora = ct.id_transportadora AND t.ativo = '1' 
                        WHERE ct.`id_cliente` = '$id_cliente' ORDER BY t.nome ";
//Significa que o usuário atrelou uma transportadora no Pop-UP de Transportadoras
                if(!empty($id_transportadora_atrelar)) {
                    echo combos::combo($sql, $id_transportadora_atrelar);
                }else {//Aqui carrega a transportadora já escolhida em Pedido
//Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                    if(!empty($cmb_cliente_transportadora)) {
                        echo combos::combo($sql, $cmb_cliente_transportadora);
//Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                    }else {//Aqui carrega a transportadora já escolhida em Pedido
                        echo combos::combo($sql, $id_transportadora);
                    }
                }
            ?>
            </select>
            &nbsp;&nbsp;
            <img src = "../../../imagem/menu/incluir.png" border='0' title="Atrelar Transportadora" alt="Atrelar Transportadora" onClick="nova_janela('../../classes/cliente/atrelar_transportadoras.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;&nbsp;
            <img src = "../../../imagem/menu/excluir.png" border='0' title="Excluir Transportadora" alt="Excluir Transportadora" onClick="excluir_transportadora()">
        </td>
    </tr>
<?
        /******************************************************************************/
        //Busco Dados de Frete que estão no Orçamento que gerou este Pedido de Venda ...
        $sql = "SELECT ov.`tipo_frete`, ov.`valor_frete_estimado` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
        $campos_orcamento = bancos::sql($sql);
?>
    <tr class='linhanormal'>
        <td>
            Tipo de Frete:
        </td>
        <td>
        <?
            if($campos_orcamento[0]['tipo_frete'] == 'F') {
                echo 'FOB (POR CONTA DO CLIENTE)';
            }else if($campos_orcamento[0]['tipo_frete'] == 'C') {
                echo 'CIF (POR NOSSA CONTA)';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor de Frete Estimado:
        </td>
        <td>
            <?=number_format($campos_orcamento[0]['valor_frete_estimado'], 2, ',', '.');?>
        </td>
    </tr>
<?
        /******************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            <b>Finalidade:</b>
        </td>
        <td>
            <select name='cmb_finalidade' title='Selecione a Finalidade' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
//Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                    if(!empty($cmb_finalidade)) {
                        if($cmb_finalidade == 'C') {
                            $selected_consumo           = 'selected';
                        }else if($cmb_finalidade == 'I') {
                            $selected_industrializacao  = 'selected';
                        }else {
                            $selected_revenda = 'selected';
                        }
                    }else {
                        if($finalidade == 'C') {
                            $selected_consumo           = 'selected';
                        }else if($finalidade == 'I') {
                            $selected_industrializacao  = 'selected';
                        }else {
                            $selected_revenda           = 'selected';
                        }
                    }
                ?>
                <option value='C' <?=$selected_consumo;?>>CONSUMO</option>
                <option value='I' <?=$selected_industrializacao;?>>INDUSTRIALIZAÇÃO</option>
                <option value='R' <?=$selected_revenda;?>>REVENDA</option>
            </select>
            <?
                if($id_pais != 31) {//Se o Cliente for do Tipo Internacional então ...
                    echo 'Exportação';
                    $tipo_moeda = 'U$ ';//Será utilizado mais abaixo ...
                }else {
                    $tipo_moeda = 'R$ ';//Será utilizado mais abaixo ...
                }
//Esse checkbox só irá ter a função de submeter nos usuários do Roberto, Wilson, Dárcio, Wilson Japonês e Netto ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
//Enquanto o Pedido não estiver liberado, então esse checkbox irá executar essa função ...
                        if(strtoupper($campos[0]['liberado'] != 1)) $onclick = "onclick = 'enviar()'";
                }
            ?>
            <input type="checkbox" name="chkt_livre_debito" value="S" id="livre_debito" <?=$onclick;?>  <?=$checked_livre_debito;?>>
            <label for="livre_debito">
                <font color='darkblue'>
                        <b>Livre de Débito Propag / Mkt</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Contato(s) do Cliente:</b>
        </td>
        <td>
            <select name='cmb_cliente_contato' title='Selecione o Contato do Cliente' class='combo'>
            <?
/*Significa que foi incluido algum contato no Pop-Up de contatos, sendo assim, o sistema sugere esse contato na combo
assim que acaba de ser incluso*/
                if($controle == 1) {
//Aqui eu pego o ultimo contato que acabou de ser incluido ou alterado
                    $sql = "SELECT `id_cliente_contato`, `nome` 
                            FROM `clientes_contatos` 
                            WHERE `id_cliente` = '$id_cliente' 
                            AND `ativo` = '1' ORDER BY `id_cliente_contato` DESC LIMIT 1 ";
                    $campos_contato 	= bancos::sql($sql);
                    $id_cliente_contato = $campos_contato[0]['id_cliente_contato'];

                    $sql = "SELECT `id_cliente_contato`, `nome` 
                            FROM `clientes_contatos` 
                            WHERE `id_cliente` = '$id_cliente' 
                            AND `ativo` = '1' ORDER BY `nome` ";
                    echo combos::combo($sql, $id_cliente_contato);
                }else {//Aqui e quando carrega a tela de primeira
                    $sql = "SELECT `id_cliente_contato`, `nome` 
                            FROM `clientes_contatos` 
                            WHERE `id_cliente`  = '$id_cliente' 
                            AND `ativo` = '1' ORDER BY `nome` ";
//Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                    if(!empty($cmb_cliente_contato)) {
                        echo combos::combo($sql, $cmb_cliente_contato);
//Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                    }else {//Aqui carrega o contato já escolhida em Pedido
                        echo combos::combo($sql, $id_cliente_contato);
                    }
                }
            ?>
            </select>
            &nbsp;&nbsp;
            <img src = "../../../imagem/menu/incluir.png" border='0' title="Incluir Contato" alt="Incluir Contato" onClick="nova_janela('../../classes/cliente/incluir_contatos.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;&nbsp;
            <img src = "../../../imagem/menu/alterar.png" border='0' title="Alterar Contato" alt="Alterar Contato" onClick="alterar_contato()">
            &nbsp;&nbsp;
            <img src = "../../../imagem/menu/excluir.png" border='0' title="Excluir Contato" alt="Excluir Contato" onClick="excluir_contato()">
        </td>
    </tr>
<?
/*************************************************************************************/
/***************************************Suframa***************************************/
/*************************************************************************************/
	if($tipo_suframa > 0) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                <b>TIPO / CÓDIGO SUFRAMA: </b>
            </font>
        </td>
        <td>
<?
            $tipo_suframa_vetor[1] = 'Área de Livre Comércio (ICMS/IPI) / ';
            $tipo_suframa_vetor[2] = 'Zona Franca de Manaus (ICMS/PIS/COFINS/IPI) / ';
            $tipo_suframa_vetor[3] = 'Amazônia Ocidental (IPI) / ';

            echo '<font color="blue">'.$tipo_suframa_vetor[$tipo_suframa].$cod_suframa.'</font>';
//Se o Suframa for Ativo, então exibo essa Mensagem de Ativo ao lado ...
            if($suframa_ativo == 'S') echo ' <font color="red"><b>(ATIVO)</b></font>';

            if($tipo_suframa == 1 && $suframa_ativo == 'S') {//Área de Livre e o Cliente possui o Suframa Ativo ...
?>
                <br/>Desconto de ICMS = <?=number_format(genericas::variavel(40), 2, ',', '.');?> % <font color='red'>(A ser concedido na Emissão da NF)</font>
<?
            }else if($tipo_suframa == 2 && $suframa_ativo == 'S') {//Zona Franca de Man...
?>
                <br/>Desconto de PIS + Cofins = <?=number_format((genericas::variavel(20)+genericas::variavel(21)), 2, ',', '.');?> % e ICMS = <?=number_format(genericas::variavel(40), 2, ',', '.');?> % <font color='red'>(A ser concedido na Emissão da NF)</font>
<?
            }
?>
        </td>
    </tr>
<?
	}
/*************************************************************************************/
//Esse checkbox só irá ter a função de submeter nos usuários do Roberto, Wilson, Dárcio, Wilson Japonês e Netto ...
        if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
?>
    <tr class='linhanormal'>
        <td>
            <b>Modo de Venda:</b>
        </td>
        <td>
<?
        if($campos[0]['modo_venda'] == 1) { //Antigo fone => Fone ( Atendimento Interno )
            $checked = 'checked';
        }else if($campos[0]['modo_venda'] == 2) {//Vendedor
            $checked2 = 'checked';
        }
?>
            <input type='radio' name='opt_modo_venda' value='1' title='Selecione o Modo de Venda' id='fone' <?=$checked;?>>
            <label for='fone'>Atendimento Interno</label>
            &nbsp;
            <input type='radio' name='opt_modo_venda' value='2' title='Selecione o Modo de Venda' id='vendedor' <?=$checked2;?>>
            <label for='vendedor'>Vendedor</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Liberar Pedido:</b>
        </td>
        <td>
            <input type='checkbox' name='chkt_liberar_pedido' value='1' id='liberar_pedido' onclick='enviar()' class='checkbox' <?=$checked_liberar;?>>
            <label for='liberar_pedido'>Liberar Pedido</label>
            <?
                //Essa frase só aparecerá quando o Pedido for Livre de Débito ...
                if($campos[0]['livre_debito'] == 'S') {
            ?>
                <font color='red'><b> (Não precisa mais de autorização do Nishi / Roberto via Follow-UP)</b></font>
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_projecao_vendas' value='S' id='chkt_projecao_vendas' class='checkbox' <?=$checked_projecao_vendas;?>>
            <label for='chkt_projecao_vendas'>
                <font color='darkgreen'>
                    <b>PROJEÇÃO DE VENDAS</b>
                </font>
            </label>
            <?
                //Aqui eu busco todas as Projeções do Cliente realizada nos últimos 3 meses - Trimestre ...	
                $sql = "SELECT SUM(valor_projecao) AS total_projecao 
                        FROM `projecoes_trimestrais` 
                        WHERE `id_cliente` = '$id_cliente' 
                        AND SUBSTRING(`data_sys`, 1, 10) >= DATE_ADD('".date('Y-m-d')."', INTERVAL -$dif_max_dias_prog_esp DAY) ";
                $campos_projecao = bancos::sql($sql);
                if($campos_projecao[0]['total_projecao'] > 0) echo ' - <font size="2"><b>EXISTE PROJEÇÃO NO VALOR DE R$ '.number_format($campos_projecao[0]['total_projecao'], 2, ',', '.').'</b></font>';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_projecao_apv' value='S' id='chkt_projecao_apv' class='checkbox' <?=$checked_projecao_apv;?>>
            <label for='chkt_projecao_apv'>
                <font color="brown">
                    <b>PROJEÇÃO DE APV</b>
                </font>
            </label>
            <?
                //Aqui eu busco todas as OPC(s) do Cliente no ano atual ...
                $sql = "SELECT SUM(qtde_proposta * preco_proposto) AS total_opc 
                        FROM `opcs` 
                        INNER JOIN `opcs_itens` oi ON oi.id_opc = opcs.id_opc 
                        WHERE opcs.`id_cliente` = '$id_cliente' 
                        AND YEAR(`data_sys`) = '".date('Y-m-d')."' ";
                $campos_projecao = bancos::sql($sql);
                if($campos_projecao[0]['total_opc'] > 0) echo ' - <font size="2"><b>EXISTE OPC NO VALOR DE R$ '.number_format($campos_projecao[0]['total_opc'], 2, ',', '.').'</b></font>';
            ?>
        </td>
    </tr>
<?
        //Essa opção de Expresso, só aparece p/ os Funcionários Roberto 62, Dárcio 98 porque programa e Nishimura 136 ...
        if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
?>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_expresso' value='S' id='chkt_expresso' class='checkbox' <?=$checked_expresso;?>>
            <label for='chkt_expresso'>
                <font color='red'>
                    <b>EXPRESSO</b>
                </font>
            </label>
        </td>
    </tr>
<?
        }
    }
/*************************************************************************************/
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
	if(empty($txt_seu_pedido_numero)) $txt_seu_pedido_numero = $seu_pedido_numero;
?>
    <tr class='linhanormal'>
        <td>
            <b>Seu Pedido N.º:</b>
        </td>
        <td>
            <input type='text' name='txt_seu_pedido_numero' value='<?=$seu_pedido_numero;?>' title='Digite o Seu Pedido N.º' size='17' maxlength='40' class='caixadetexto'>
            &nbsp;&nbsp;
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($txt_faturar_em)) $txt_faturar_em = $faturar_em;
?>
    <tr class='linhanormal'>
        <td>
            <b>Faturar em:</b>
        </td>
        <td>
            <input type='text' name='txt_faturar_em' value='<?=$txt_faturar_em;?>' title='Digite o Faturar em' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_faturar_em&tipo_retorno=1&caixa_auxiliar=controle', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
            <?
//Se o Faturar Em for maior do que a Data de Emissão então apresenta a palavra programado para facilitar ...
                if(data::datatodate($faturar_em, '-') > data::datatodate($data_emissao, '-')) {
                    echo ' <font color="darkblue"><b>(PROGRAMADO)</b></font>';
                }
            ?>
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($txt_data_emissao)) $txt_data_emissao = $data_emissao;
?>
    <tr class='linhanormal'>
        <td>
        <?
//Esse checkbox só irá ter a função de submeter nos usuários do Roberto, Wilson, Dárcio, Wilson Japonês e Netto ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {

        ?>
                <a href="javascript:nova_janela('alterar_data_emissao.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Data de Emissão' class='link'>
                    <b>Data de Emissão:</b>
                </a>
        <?
            }else {
        ?>
                <b>Data de Emissão:</b>
        <?
            }
        ?>
        </td>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=$txt_data_emissao;?>' title='Data de Emissão' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;&nbsp;
<?
//Esse checkbox só irá ter a função de submeter nos usuários do Roberto, Wilson, Dárcio, Wilson Japonês e Netto ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
                if($campos[0]['ja_liberado'] == 1 && $campos[0]['liberado'] == 0) {
?>
            <input type='radio' name='opt_data_emissao' id='opt_data_emissao1' value='1' title='Manter Data Anterior' onclick='controle_data_emissao()'>
            <label for='opt_data_emissao1'>Manter Data Anterior</label>
            &nbsp;&nbsp;
            <input type='radio' name='opt_data_emissao' id='opt_data_emissao2' value='2' title='Assumir Data Atual' onclick='controle_data_emissao()'>
            <label for='opt_data_emissao2'>Assumir Data Atual</label>
<?
                }
            }
?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Forma de Pagamento:</b>
        </td>
        <td>
            <?
                $vetor_forma_pagamento  = array_sistema::forma_pagamento();

                foreach($vetor_forma_pagamento as $indice => $rotulo) {
                    if(!empty($forma_pagamento) && $forma_pagamento == $indice) {
                        echo $rotulo;
                        break;
                    }
                }
            ?>
            </select>
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
        if(empty($ja_submeteu)) {//Na primeira vez
            $txt_vencimento1        = $vencimento1;
            $txt_data_vencimento1   = $data_vencimento1;
/*Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP, então uso essa caixa
de empresa para fazer esse macete*/
        }else {
            if(!empty($txt_vencimento1)) $txt_data_vencimento1 = data::adicionar_data_hora($faturar_em, $txt_vencimento1);
        }
?>
    <tr class='linhanormal'>
        <td>
            <b>Vencimento 1:</b>
        </td>
        <td>
            <input type='text' name="txt_vencimento1" value="<?=$txt_vencimento1;?>" title="Digite o Vencimento 1" size="5" maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(1)" class='caixadetexto'>
            DIAS &nbsp;&nbsp;
            <input type='text' name="txt_data_vencimento1" value="<?=$txt_data_vencimento1;?>" title="Data do Vencimento 1" size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
        if(empty($ja_submeteu)) {//Na primeira vez
            $txt_vencimento2        = $vencimento2;
            $txt_data_vencimento2   = $data_vencimento2;
/*Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP, então uso essa caixa
de empresa para fazer esse macete*/
        }else {
            if(!empty($txt_vencimento2)) $txt_data_vencimento2 = data::adicionar_data_hora($faturar_em, $txt_vencimento2);
        }
?>
    <tr class='linhanormal'>
        <td>
            Vencimento 2:</td>
        <td>
            <input type='text' name="txt_vencimento2" value="<?=$txt_vencimento2;?>" title="Digite o Vencimento 2" size="5" maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(2)" class='caixadetexto'>
            DIAS &nbsp;&nbsp;
            <input type='text' name="txt_data_vencimento2" value="<?=$txt_data_vencimento2;?>" title="Data do Vencimento 2" size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
        if(empty($ja_submeteu)) {//Na primeira vez
            $txt_vencimento3        = $vencimento3;
            $txt_data_vencimento3   = $data_vencimento3;
/*Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP, então uso essa caixa
de empresa para fazer esse macete*/
        }else {
            if(!empty($txt_vencimento3)) $txt_data_vencimento3 = data::adicionar_data_hora($faturar_em, $txt_vencimento3);
        }
?>
    <tr class='linhanormal'>
        <td>
            Vencimento 3:
        </td>
        <td>
            <input type='text' name="txt_vencimento3" value="<?=$txt_vencimento3;?>" title="Digite o Vencimento 3" size="5" maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(3)" class='caixadetexto'>
            DIAS &nbsp;&nbsp;
            <input type='text' name="txt_data_vencimento3" value="<?=$txt_data_vencimento3;?>" title="Data do Vencimento 3" size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
        if(empty($ja_submeteu)) {//Na primeira vez
            $txt_vencimento4        = $vencimento4;
            $txt_data_vencimento4   = $data_vencimento4;
/*Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP, então uso essa caixa
de empresa para fazer esse macete*/
        }else {
            if(!empty($txt_vencimento4)) $txt_data_vencimento4 = data::adicionar_data_hora($faturar_em, $txt_vencimento4);
        }
?>
    <tr class='linhanormal'>
        <td>
            Vencimento 4:
        </td>
        <td>
            <input type='text' name="txt_vencimento4" value="<?=$txt_vencimento4;?>" title="Digite o Vencimento 4" size="5" maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(4)" class='caixadetexto'>
            DIAS &nbsp;&nbsp;
            <input type='text' name="txt_data_vencimento4" value="<?=$txt_data_vencimento4;?>" title="Data do Vencimento 4" size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Prazo Médio:
        </td>
        <td>
            <?=$prazo_medio;?>
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormaldestaque' align='center'>
        <td width='20%'>
            &nbsp;
        </td>
        <td width='20%'>
            ALBAFER
        </td>
        <td width='20%'>
            TOOL MASTER
        </td>
        <td width='20%'>
            GRUPO
        </td>
        <td width='20%'>
            TOTAL
        </td>
    </tr>
    <tr class='linhanormal'>
<?
//Esses vetores vão me auxiliar mais abaixo ...
        $vetor_emp_valor_venda  = array();
        $vetor_emp_valor_vale   = array();
        $id_empresa_atual = 0;
//Aqui eu busco todos os Itens de Pedidos que estão Pendentes Total ou Parcial p/ este Cliente
        $sql = "SELECT pvi.preco_liq_final, pv.id_pedido_venda, pv.id_empresa, pvi.id_pedido_venda_item, pvi.id_produto_acabado, pvi.qtde, pvi.vale, pvi.qtde_faturada 
                FROM pedidos_vendas pv 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.status < '2' 
                WHERE pv.`id_cliente` = '$id_cliente' ORDER BY pv.id_empresa ";
        $campos_pedido = bancos::sql($sql);
        $linhas_pedido = count($campos_pedido);
        for($i = 0;  $i < $linhas_pedido; $i++) {
//Verifico se a Empresa Corrente que está sendo listada, é diferente da Atual do Loop ...
            if($id_empresa_atual != $campos_pedido[$i]['id_empresa']) {
                if($i != 0) {//Irá incrementar essa variável a partir da 2ª Empresa ...
                    $vetor_emp_valor_venda[$id_empresa_atual] = $total_emp_valor_venda;
                    $vetor_emp_valor_vale[$id_empresa_atual] = $total_emp_valor_vale;
                }
//Zera os valores p/ não misturar com o Valor da outra Empresa ...
                $id_empresa_atual = $campos_pedido[$i]['id_empresa'];//Recebe a Empresa Atual ...
                $total_emp_valor_venda = 0;
                $total_emp_valor_vale = 0;
            }
            $total_emp_valor_venda+= $campos_pedido[$i]['preco_liq_final'] * ($campos_pedido[$i]['qtde'] - $campos_pedido[$i]['qtde_faturada']);
//Só irá entrar nesse cálculo, quando existir vale ...
            if($campos_pedido[$i]['vale'] > 0) $total_emp_valor_vale+= $campos_pedido[$i]['preco_liq_final'] * ($campos_pedido[$i]['vale'] - $campos_pedido[$i]['qtde_faturada']);
        }
//Aqui eu guardo na variável o valor Total da última Empresa ...
        $vetor_emp_valor_venda[$id_empresa_atual] = $total_emp_valor_venda;
        $vetor_emp_valor_vale[$id_empresa_atual] = $total_emp_valor_vale;
?>
        <td>
            <font color='darkgreen'>
                <b>PENDÊNCIA</b>
            </font>
        </td>
        <td align='right'>
            <b><?=$tipo_moeda.number_format($vetor_emp_valor_venda[1], 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <b><?=$tipo_moeda.number_format($vetor_emp_valor_venda[2], 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <b><?=$tipo_moeda.number_format($vetor_emp_valor_venda[4], 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <font color='darkgreen'>
                <b><?=$tipo_moeda.number_format($vetor_emp_valor_venda[1] + $vetor_emp_valor_venda[2] + $vetor_emp_valor_venda[4], 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkgreen'>
                <b>VALE</b>
            </font>
        </td>
        <td align='right'>
            <b><?=$tipo_moeda.number_format($vetor_emp_valor_vale[1], 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <b><?=$tipo_moeda.number_format($vetor_emp_valor_vale[2], 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <b><?=$tipo_moeda.number_format($vetor_emp_valor_vale[4], 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <font color='darkgreen'>
                <b><?=$tipo_moeda.number_format($vetor_emp_valor_vale[1] + $vetor_emp_valor_vale[2] + $vetor_emp_valor_vale[4], 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
<?
//Esses vetor vai me auxiliar mais abaixo ...
        $vetor_emp_faturamento = array();
//Zero a variável p/ não dar conflito com a variável de Cima ...
        $id_empresa_atual = 0;
//Busca tudo o que foi vendido p/ o Cliente no decorrer do ano Corrente por empresa ...
        $sql = "SELECT nfs.id_empresa, nfsi.qtde, nfsi.valor_unitario 
                FROM `nfs` 
                INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                WHERE nfs.`id_cliente` = '$id_cliente' 
                AND SUBSTRING(nfs.`data_emissao`, 1, 4) = '".date('Y')."' ORDER BY nfs.id_empresa ";
        $campos_faturamento = bancos::sql($sql);
        $linhas_faturamento = count($campos_faturamento);
        for ($i = 0;  $i < $linhas_faturamento; $i++) {
//Verifico se a Empresa Corrente que está sendo listada, é diferente da Atual do Loop ...
            if($id_empresa_atual != $campos_faturamento[$i]['id_empresa']) {
                if($i != 0) {//Irá incrementar essa variável a partir da 2ª Empresa ...
                    $vetor_emp_faturamento[$id_empresa_atual] = $total_valor_faturamento;
                }
//Zera os valores p/ não misturar com o Valor da outra Empresa ...
                $id_empresa_atual = $campos_faturamento[$i]['id_empresa'];//Recebe a Empresa Atual ...
                $total_valor_faturamento = 0;
            }
            $total_valor_faturamento+= $campos_faturamento[$i]['qtde'] * $campos_faturamento[$i]['valor_unitario'];
        }
//Aqui eu guardo na variável o valor Total da última Empresa ...
        $vetor_emp_faturamento[$id_empresa_atual] = $total_valor_faturamento;
?>
        <td>
            <font color='darkgreen'>
                <b>VENDA <?=date('Y');?></b>
            </font>
        </td>
        <td align='right'>
            <b><?=$tipo_moeda.number_format($vetor_emp_faturamento[1], 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <b><?=$tipo_moeda.number_format($vetor_emp_faturamento[2], 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <b><?=$tipo_moeda.number_format($vetor_emp_faturamento[4], 2, ',', '.');?></b>
        </td>
        <td align='right'>
            <font color='darkgreen'>
                <b><?=$tipo_moeda.number_format($vetor_emp_faturamento[1] + $vetor_emp_faturamento[2] + $vetor_emp_faturamento[4], 2, ',', '.');?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='5' bgcolor='#CECECE'>
            &nbsp;
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormal'>
        <td>
            Situação do Pedido:
        </td>
        <td>
        <?
//Controle de Retorno de Mensagens referentes ao Pedido
            if(vendas::situacao_pedido($id_pedido_venda) == 1) {
                echo "<font color='blue'><b>PEDIDO LIBERADO PARA FATURAMENTO !</b></font>";
            }else {
                echo "<font color='red'><b>PEDIDO NÃO LIBERADO PARA FATURAMENTO !</b></font>";
            }
        ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');if(document.form.txt_data_emissao.disabled==false) {document.form.txt_data_emissao.focus()}" style='color:#ff9900' class='botao'>
        <?
            /*O Roberto 62 "Diretor" e Dárcio "98" porque programa são os únicos que podem salvar o Cabeçalho de Pedido 
            independente de o mesmo estar liberado ou não ...*/
            if($_SESSION['id_funcionario'] != 62 && $_SESSION['id_funcionario'] != 98) {
                //Se o pedido estiver liberado, não pode alterar o cabeçote ...
                if(vendas::situacao_pedido($id_pedido_venda) == 1) $disabled = 'disabled';
            }
        ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        <?
            //Esse botão só aparecerá p/ Pedidos que são de Exportação "Clientes Estrangeiros" ...
            if($id_pais != 31) {
                //E para os usuários Roberto 62, Wilson 68, Mercedes 81, Dárcio 98 "porque programa" e Nishimura 136 ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 81 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
        ?>
            <input type='button' name='cmd_dados_exportacao' value='Dados de Exportação' title='Dados de Exportação' style='color:darkblue' onclick="nova_janela('dados_exportacao.php?id_pedido_venda=<?=$id_pedido_venda;?>', 'DADOS_EXPORTACAO', '', '', '', '', '450', '750', 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        <?
                }
            }
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>