<?
if(!class_exists('bancos'))         require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...

class producao {
/*Fun��o que verifica se tem algum Item de OS com pre�o desatualizado em compara��o com os Pre�os 
a lista de Pre�os do Fornecedor*/
    function conferir_precos_os($id_os) {
//Primeira coisa a verificar � se a OS j� foi importada p/ Pedido ...
        $sql = "SELECT `id_pedido` 
                FROM `oss` 
                WHERE `id_os` = '$id_os' LIMIT 1 ";
        $campos     = bancos::sql($sql);
        $id_pedido  = $campos[0]['id_pedido'];
        if(is_null($id_pedido)) {//Se esta ainda n�o foi importada ...
//Verifico o Fornecedor e a Data de Emiss�o da OS ...
            $sql = "SELECT `id_fornecedor`, `data_saida` 
                    FROM `oss` 
                    WHERE `id_os` = '$id_os' LIMIT 1 ";
            $campos         = bancos::sql($sql);
            $id_fornecedor  = $campos[0]['id_fornecedor'];
            $data_saida     = $campos[0]['data_saida'];
//Busco os Itens da OS
            $sql = "SELECT DISTINCT(`id_produto_insumo_ctt`) 
                    FROM `oss_itens` 
                    WHERE `id_os` = '$id_os' ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            $contador_itens = 0;//Utilizo + pra baixo
//Disparo do loop dos Itens
            for($i = 0; $i < $linhas; $i++) {
//Agora comparo a Data de Atualiza��o desse Produto com a Data de Sa�da da OS ...
                $sql = "SELECT pi.`id_produto_insumo`, pi.`discriminacao` 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = fpi.id_produto_insumo 
                        WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                        AND fpi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo_ctt']."' 
                        AND SUBSTRING(fpi.`data_sys`, 1, 10) > '$data_saida' LIMIT 1 ";
                $campos_pi = bancos::sql($sql);
//Se a Data de Atualiza��o desse PI for > do que a Data de Emiss�o da OS, ent�o eu exibo os PI(s) errados...
                if(count($campos_pi) == 1) {
                    $id_produtos_insumos.=  $campos_pi[0]['id_produto_insumo'].', ';
                    $produtos_insumos.=     $campos_pi[0]['discriminacao'].', ';
                    $contador_itens++;
                }
            }
//Se encontrou pelo menos 1 PI nessa condi��o, ent�o ...
            if($contador_itens > 0) {
                $id_produtos_insumos    = substr($id_produtos_insumos, 0, strlen($id_produtos_insumos) - 2);
                $produtos_insumos       = substr($produtos_insumos, 0, strlen($produtos_insumos) - 2);
//Eu retorno nesse os PI(s) q est�o desatualizados e as discrimina��es destes PI(s) p/ auxiliar em Alert(s)
                return array('id_produtos_insumos' => $id_produtos_insumos, 'produtos_insumos' => $produtos_insumos);
            }else {
//Como n�o encontrou nenhum PI, ent�o n�o retorno nada ...
                return 0;
            }
        }else {//Se a OS j� foi importada, eu n�o posso mais atualizar com os novos Pre�os da Lista
            return 0;
        }
    }

    function atualizar_precos_os($id_os, $id_produtos_insumos) {
//Primeira coisa a verificar � se a OS j� foi importada p/ Pedido ...
        $sql = "SELECT `id_pedido` 
                FROM `oss` 
                WHERE `id_os` = '$id_os' LIMIT 1 ";
        $campos     = bancos::sql($sql);
        $id_pedido  = $campos[0]['id_pedido'];
        if(is_null($id_pedido)) {//Se esta ainda n�o foi importada ...
//Vou precisar desse Vetorzinho + pra baixo ...
            $id_produtos_insumos_array = explode(',', $id_produtos_insumos);
//Verifico o Fornecedor da OS ...
            $sql = "SELECT `id_fornecedor` 
                    FROM `oss` 
                    WHERE `id_os` = '$id_os' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $id_fornecedor = $campos[0]['id_fornecedor'];
//Disparo do Loop de Itens da OS ...
            for($i = 0; $i < count($id_produtos_insumos_array); $i++) {
//Busco os Pre�os Atualizados dos Produtos da Lista de Pre�o do Fornecedor ...
                $sql = "SELECT `preco` 
                        FROM `fornecedores_x_prod_insumos` 
                        WHERE `id_produto_insumo` = '$id_produtos_insumos_array[$i]' 
                        AND `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
                $campos = bancos::sql($sql);
/****************Atualizando os Itens da OS****************/
//Atualiza��o do Novo Pre�o da Lista p/ o Item de OS ...
                $sql = "UPDATE `oss_itens` SET `preco_pi` = '".$campos[0]['preco']."' WHERE `id_os` = '$id_os' AND `id_produto_insumo_ctt` = '$id_produtos_insumos_array[$i]' ";
                bancos::sql($sql);
            }
            return 1;
        }else {
            return 0;
        }
    }

    function atualizar_status_item_os($id_os_item) {
        //Aqui eu verifico se esse Item da OS j� foi importado em Nota Fiscal ...
        $sql = "SELECT `id_nfe_historico` 
                FROM `oss_itens` 
                WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['id_nfe_historico'] > 0) {//J� foi importado em Nota Fiscal ...
            $status_item = 2;//Item de OS conclu�do
        }else {
            $status_item = 0;//Item de OS em aberto
        }
        //Atualizando o Status do Item da OSS ...
        $sql = "UPDATE `oss_itens` SET `status` = '$status_item' WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
        bancos::sql($sql);
    }
	
    function atualizar_status_os($id_os) {
//1) Aqui eu verifico o Total de Itens que existe nessa OS
        $sql = "SELECT COUNT(id_os_item) AS qtde_itens_os 
                FROM `oss_itens` 
                WHERE `id_os` = '$id_os' ";
        $campos_qtde_itens  = bancos::sql($sql);
        $qtde_itens_os      = $campos_qtde_itens[0]['qtde_itens_os'];
//2) Aqui eu verifico se existe algum Item de Entrada da OS que ficou pendente ...
        $sql = "SELECT COUNT(qtde_entrada) AS total_itens_aberto 
                FROM `oss_itens` 
                WHERE `id_os` = '$id_os' 
                AND `status` < '2' ";
        $campos_qtde_pendente   = bancos::sql($sql);
        $total_itens_aberto     = $campos_qtde_pendente[0]['total_itens_aberto'];
//Significa que n�o existe + nenhum item pendente, sendo assim eu posso concluir essa OS
        if($total_itens_aberto == 0) {
            $status_nf = 2;
        }else {//Ainda existem itens em abertos ... ent�o continou com a OS em aberto ainda ...
            if($total_itens_aberto == $qtde_itens_os) {//OS em aberto de forma Total
                $status_nf = 0;
            }else {//OS em aberto de forma Parcial
                $status_nf = 1;
            }
        }
//Atualizo o Status da OS ...
        $sql = "UPDATE `oss` SET `status_nf` = '$status_nf' WHERE `id_os` = '$id_os' LIMIT 1 ";
        bancos::sql($sql);
    }
/*Essa fun��o s� ser� utilizada na 1�, 2� e 3� Etapas do Custo ...

 * 
 * 1� Etapa somente no Excluir Item(ns)
 * 2� Etapa alterar e Excluir Item(ns)
 * 3� Etapa somente no Excluir Item(ns)
 * 
 * Essa fun��o ser� utilizada somente nessas Etapas, devido ser somente nessas Etapas em que � dado baixa de 
 * "Mat�ria Prima" por OPs no M�dulo de Compras, Estoque -> Dar Baixa 
 */
    function verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado, $id_produto_insumo_antigo, $id_produto_insumo_novo = 0, $etapa) {
        $data_sys = date('Y-m-d H:i:s');
//Verifica se existe alguma OP em que foi dado Baixa nesse PI "A�o - Mat�ria Prima" e que n�o foi Finalizada ...
        $sql = "SELECT DISTINCT(bop.id_op) 
                FROM `baixas_ops_vs_pis` bop 
                INNER JOIN `ops` on ops.id_op = bop.id_op and ops.status_finalizar = '0' and ops.ativo = '1' and ops.id_produto_acabado = '$id_produto_acabado' 
                WHERE bop.id_produto_insumo = '$id_produto_insumo_antigo' ";
        $campos_ops = bancos::sql($sql);
        $linhas_ops = count($campos_ops);
/*Se existe alguma baixa, ent�o o Sistema manda um E-mail informando qual foi a Troca do PI 
em alguma das Etapas e qual OP que n�o foi Finalizada ainda ...*/
        if($linhas_ops > 0) {
//Busca da Discrimina��o do Produto Insumo Antigo que foi Alterado ou Exclu�do, essa op��o sempre ir� existir ...
            $sql = "SELECT discriminacao 
                    FROM `produtos_insumos` 
                    WHERE id_produto_insumo = '$id_produto_insumo_antigo' LIMIT 1 ";
            $campos_produto_antigo  = bancos::sql($sql);
            $produto_insumo         = $campos_produto_antigo[0]['discriminacao'];
/*******************************************************************************************/
//Busca do Login que est� fazendo altera��o ou exclus�o no PI da Respectiva Etapa ...
            $sql = "SELECT login 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login       = bancos::sql($sql);
            $login_responsavel  = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $mensagem_email = '<font color="darkblue"><b>Altera��o / Exclus�o de PI no Custo</b></font><br>';
            if($id_produto_insumo_novo > 0) {//Significa que houve uma Troca de um PI por outro ...
//Se existir Produto Insumo Novo ent�o fa�o a busca da Discrimina��o do Produto Novo ...
                $sql = "SELECT discriminacao 
                        FROM `produtos_insumos` 
                        WHERE `id_produto_insumo` = '$id_produto_insumo_novo' LIMIT 1 ";
                $campos_produto_novo    = bancos::sql($sql);
                $discriminacao_novo     = $campos_produto_novo[0]['discriminacao'];
                $mensagem_email.= '<br>O Produto Insumo <b>'.$produto_insumo.'</b> da Etapa <b>'.$etapa.'</b> foi alterado pelo <b>'.$discriminacao_novo.'</b>';
            }else {//Foi somente uma Exclus�o ...
                $mensagem_email.= '<br>O Produto Insumo <b>'.$produto_insumo.'</b> da Etapa <b>'.$etapa.'</b> foi exclu�do.';
            }
            //Listando as OP(s) que ainda n�o foram finalizadas ...
            for($i = 0; $i < $linhas_ops; $i++) $id_ops.= $campos_ops[$i]['id_op'].', ';
            $id_ops = substr($id_ops, 0, strlen($id_ops) - 2);
            $mensagem_email.= '<font color="red"><br>A(s) OP(s) <b>'.$id_ops.' </b>ainda n�o foi(ram) finalizada(s) e j� teve uma baixa anterior com esse PI <b>'.$produto_insumo.'</b></font>';
            $mensagem_email.= '<font color="red"><br>O sistema estar� apresentando registro de baixa zero deste novo PI para estas OPs.</b></font>';
            $mensagem_email.= '<br><b>Login Respons�vel: </b>'.$login_responsavel.' - <b>Data e Hora: </b>'.date('d/m/Y H:i:s');
//Gravando a Baixa ...
            if($id_produto_insumo_novo > 0) {//Se houve uma Troca de um PI por outro, ent�o eu registro uma Baixa
                $observacao_baixa = '<b>Baixa autom�tica por subst. PI <font color="darkblue">'.$discriminacao_novo.'</font> nas OP(s) <font color="darkblue">'.$id_ops.'</font></b>';
                for($i = 0; $i < $linhas_ops; $i++) {
                    //Inserindo os Dados no BD ...
                    $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `observacao`, `acao`, `troca`, `data_sys`) VALUES (NULL, '$id_produto_insumo_novo', '$_SESSION[id_funcionario]', '$_SESSION[id_funcionario]', '', '0', '$observacao_baixa', 'B', 'N', '$data_sys') ";
                    bancos::sql($sql);
                    $id_baixa_manipulacao = bancos::id_registro();
                    //Controle com a Parte de OP(s) ...
                    $sql = "INSERT INTO `baixas_ops_vs_pis` (`id_baixa_op_vs_pi`, `id_produto_insumo`, `id_op`, `id_baixa_manipulacao`, `qtde_baixa`, `observacao`, `data_sys`, `status`) values (NULL, '$id_produto_insumo_novo', '".$campos_ops[$i]['id_op']."', '$id_baixa_manipulacao', '0', 'Esta OP usou $produto_insumo, mas houve altera��o no custo.', '$data_sys', '2') ";
                    bancos::sql($sql);
                }
                estoque_ic::atualizar($id_produto_insumo_novo, 0);
            }
        }
//Aqui eu mando um e-mail informando os PI(s) que foram trocados em quais baixas ...
        $destino = $alterar_excluir_ops_com_baixa_nao_finalizadas;
/*Se foi poss�vel o Estorno, ent�o o Sistema dispara um e-mail informando a Dona Sandra quem foi o 
respons�vel pelo Estorno da Conta ...*/
        if(!class_exists('comunicacao')) require('comunicacao.php');
        require('variaveis/intermodular.php');
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Altera��o / Exclus�o de PI no Custo', $mensagem_email);
    }

    function gerador_codigo_barra($id_produto_acabado) {
        $ref_ean        = '789';//C�digo do Brasil
        $ref_empresa    = '90708';//C�digo de Cadastro da Albafer
//Aqui eu busco o nosso �ltimo n�mero utilizado no Sistema em cima dos PAs ...
        $sql = "SELECT SUBSTRING(`codigo_barra`, 9, 4) AS nosso_numero_codigo_barra 
                FROM `produtos_acabados` 
                ORDER BY `nosso_numero_codigo_barra` DESC LIMIT 1 ";
        $campos = bancos::sql($sql);
        $nosso_proximo_numero = $campos[0]['nosso_numero_codigo_barra'] + 1;
        $codigo_barra = $ref_ean.$ref_empresa.$nosso_proximo_numero;
/*********************************************************************************/
/**********************L�gica para gerar o D�gito Verificador*********************/
/*********************************************************************************/
        $digito1 = substr($codigo_barra, 0, 1);
        $digito2 = substr($codigo_barra, 1, 1);
        $digito3 = substr($codigo_barra, 2, 1);
        $digito4 = substr($codigo_barra, 3, 1);
        $digito5 = substr($codigo_barra, 4, 1);
        $digito6 = substr($codigo_barra, 5, 1);
        $digito7 = substr($codigo_barra, 6, 1);
        $digito8 = substr($codigo_barra, 7, 1);
        $digito9 = substr($codigo_barra, 8, 1);
        $digito10 = substr($codigo_barra, 9, 1);
        $digito11 = substr($codigo_barra, 10, 1);
        $digito12 = substr($codigo_barra, 11, 1);

        $resultado1 = ($digito1 + $digito3 + $digito5 + $digito7 + $digito9 + $digito11) + (($digito2 * 3) + ($digito4 * 3) + ($digito6 * 3) + ($digito8 * 3) + ($digito10 * 3) + ($digito12 * 3));
        $resultado2 = intval(($resultado1 / 10) + 1) * 10;
        $digito_verificador = ($resultado2 - $resultado1 == 10) ? 0 : $resultado2 - $resultado1;
/*********************************************************************************/
        $codigo_barra.= $digito_verificador;
        return $codigo_barra;
    }
}
?>