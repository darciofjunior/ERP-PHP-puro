<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/comunicacao.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/variaveis/intermodular.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = "<font class='confirmacao'>RECEBIMENTO EFETUADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>NÃO FOI POSSIVEL FINALIZAR SUA QUITAÇÃO. POIS JÁ EXISTE ESTE CHEQUE.</font>";

$data_atual = date('Y-m-d');

if($passo == 1) {
/*Se tiver alguma conta que é do Tipo Cartório ou com mais de 15 dias de atraso, então está precisa ser 
exibida no E-mail com a Justificativa preenchida pelo usuário ...*/
    if(!empty($_POST['hdd_justificativa'])) {
//1)
/************************Busca de Dados************************/
        $data_atual         = date('Y-m-d');
        $txt_justificativa  = '<font color="blue">Follow-Up Registrado automaticamente (E-mail) </font>';
/*Aqui eu verifico se tem alguma conta que é do Tipo Cartório ou Protestado e com mais 
de 15 dias de atraso ...*/
        $sql = "SELECT cr.id_conta_receber, DATE_FORMAT(cr.`data_vencimento_alterada`, '%d/%m/%Y') AS data_venc_antiga, cr.id_empresa, cr.num_conta, cr.valor, tp.recebimento, tm.simbolo 
                FROM `contas_receberes` cr 
                INNER JOIN `tipos_recebimentos` tp ON tp.id_tipo_recebimento = cr.id_tipo_recebimento 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
                WHERE cr.id_conta_receber IN ($id_conta_receber) 
                AND (cr.id_tipo_recebimento IN (7, 9) 
                OR DATEDIFF('$data_atual', `data_vencimento_alterada`) > 15) ";
        $campos_irregulares = bancos::sql($sql);
        $linhas_irregulares = count($campos_irregulares);
        if($linhas_irregulares > 0) {//Se existir pelo menos 1 conta que segue nesse critério ...
            for($i = 0; $i < $linhas_irregulares; $i++) {//Listagem do N.º das contas ...
                $data_venc_antiga   = $campos_irregulares[$i]['data_venc_antiga'];
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
                $id_empresa_cr      = $campos_irregulares[$i]['id_empresa'];
                $empresa            = genericas::nome_empresa($id_empresa_cr);
                $num_conta          = $campos_irregulares[$i]['num_conta'];
                $tipo_recebimento   = $campos_irregulares[$i]['recebimento'];
                $valor_a_receber    = $campos_irregulares[$i]['simbolo'].' '.$campos_irregulares[$i]['valor'];
                $dados_cliente      = financeiros::nome_cliente_conta_receber($campos_irregulares[$i]['id_conta_receber']);
                $id_cliente_loop    = $dados_cliente['id_cliente'];
                $cliente            = $dados_cliente['cliente'];
                $id_cliente_contato = $dados_cliente['id_cliente_contato'];
//Dados p/ enviar por e-mail ...
                $complemento_justificativa.= '<br><b>Empresa: </b>'.$empresa.' <br><b>Cliente: </b>'.$cliente.' <br><b>N.º da Conta: </b>'.$num_conta.' <br><b>Data de Vencimento: </b>'.$data_venc_antiga.' <br><b>Tipo de Recebimento: </b>'.$tipo_recebimento.' <br><b>Valor à Receber: </b>'.$valor_a_receber;
                $observacao_follow_up           = $txt_justificativa.' - Contas à Receber em Cartório ou Data de Vencimento c/ mais de 15 dias '.' - <b>N.º da Conta: </b>'.$num_conta.' - <b>Justificativa: </b>'.$hdd_justificativa;
//Registrando Follow-UP(s) ...
                $id_representante               = genericas::buscar_id_representante($id_cliente_contato);
                
/*Tenho essa verificação porque nem todas as Contas à Receber terão Cliente e Representante, devido terem sido 
inclusas de forma Manual pela opção "Incluir Crédito(s) / Débito(s) Financeiro(s)" ...*/
                if(!empty($id_cliente_contato) && !empty($id_representante)) {
                    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente_loop', '$id_cliente_contato', '$id_representante', '$_SESSION[id_funcionario]', '".$campos_irregulares[$i]['id_conta_receber']."', '4', '$observacao_follow_up', '".date('Y-m-d H:i:s')."') ";
                    bancos::sql($sql);
                }
            }
        }
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver alterando a Conta à Receber do Financeiro, então o Sistema dispara um e-mail 
informando qual a Conta à Receber que está sendo alterada ...
//-Aqui eu trago alguns dados de Conta à Receber p/ passar por e-mail via parâmetro ...
//-Aqui eu busco o login de quem está alterando a Conta à Receber ...*/
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login       = bancos::sql($sql);
        $login_alterando    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $txt_justificativa.= $complemento_justificativa.'<br><b>Login: </b>'.$login_alterando.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$hdd_justificativa.'<br>'.$PHP_SELF;
//Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
        $destino    = $recebimento_contas_receber;
        $copia      = $recebimento_contas_receber_copia;
        $mensagem   = $txt_justificativa;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, $copia, 'Contas à Receber em Cartório, Protestado ou Data de Vencimento c/ mais de 15 dias', $mensagem);
    }
//3)
/************************Recebimento************************/
    $data_sys                   = date('Y-m-d H:i:s');
    $txt_data                   = data::datatodate($txt_data, '-');
    $observacao                 = $_POST['txt_observacao'];
    
    $vetor_conta_receber        = explode(',', $id_conta_receber); //Transforma em vetor o id_contas_receber
    $linhas_contas_receberes    = count($vetor_conta_receber);
    
    for($i = 0; $i < $linhas_contas_receberes; $i++) {
        //Busca do Valor da Conta na sua moeda Original e o Tipo de Moeda da Conta à Receber ...
        $sql = "SELECT id_tipo_moeda, valor 
                FROM `contas_receberes` 
                WHERE `id_conta_receber` = '$vetor_conta_receber[$i]' LIMIT 1 ";
        $campos         = bancos::sql($sql);/*Aqui essas variáveis são para o cálculo da fórmula do Roberto*/
        $id_tipo_moeda  = $campos[0]['id_tipo_moeda'];
        $valor_conta    = $campos[0]['valor'];
//É para gravar no Banco o valor do Dólar ou valor do Euro diário
        if($id_tipo_moeda == 1) {//Real
            $valor_moeda_dia = '1.0000';
        }else if($id_tipo_moeda == 2) {//Dólar
            $valor_moeda_dia = $_POST['txt_valor_dolar'];
        }else if($id_tipo_moeda == 3) {//Euro
            $valor_moeda_dia = $_POST['txt_valor_euro'];
        }
        $calculos_conta_receber     = financeiros::calculos_conta_receber($vetor_conta_receber[$i]);
        $valor_reajustado           = $calculos_conta_receber['valor_reajustado'];
//Aqui eu só pego exatamente 2 casas do valor da conta ...
        $valor_reajustado           = round(round($valor_reajustado, 3), 2);
        $txt_valor_recebendo[$i]    = round(round($txt_valor_recebendo[$i], 3), 2);

        if($id_tipo_moeda == 2) {//Dólar
            $txt_valor_recebendo[$i]/= $_POST['txt_valor_dolar'];
        }else if($id_tipo_moeda == 3) {//Euro
            $txt_valor_recebendo[$i]/= $_POST['txt_valor_euro'];
        }
        //Arredondo p/ ficar com o valor mais preciso ...
        $txt_valor_recebendo[$i] = round(round($txt_valor_recebendo[$i], 3), 2);
        
        /*Se foi habilitado o checkbox de zerar juros, então atualiza a conta receber, como manual e o 
        valor de juros como 0,00*/
        if(!empty($_POST['chkt_zerar_juros'])) {
            $sql = "UPDATE `contas_receberes` SET `manual` = '1', `valor_juros` = '0.00' WHERE `id_conta_receber` = '$vetor_conta_receber[$i]' LIMIT 1 ";
            bancos::sql($sql);
            /*Como foi zerada a opção de Juros, então aqui nesse ponto eu instâncio novamente 
            a função p/ saber qual é exatamente o Valor Real da conta ...*/
            $calculos_conta_receber = financeiros::calculos_conta_receber($vetor_conta_receber[$i]);
            $valor_reajustado = $calculos_conta_receber['valor_reajustado'];
            
            $observacao.= ' <b>(Juros foram Zerados)</b>';
        }
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $cmb_conta_corrente = (!empty($_POST[cmb_conta_corrente])) ? "'".$_POST[cmb_conta_corrente]."'" : 'NULL';

        if($id_tipo_recebimento == 5) {//Significa que o recebimento das contas foi feito com cheque
/*Aqui traz todos os cheques que estão em abertos, valor do Cheque Corrente e a Sit. a respeito de 
Predatado ou não ...*/
            $sql = "SELECT id_cheque_cliente, valor_disponivel 
                    FROM `cheques_clientes` 
                    WHERE `status_disponivel` = '1' 
                    AND `ativo` = '1' 
                    AND `id_cheque_cliente` IN ($_POST[hdd_cheques_clientes]) ";
            $campos_cheques = bancos::sql($sql);
            $linhas_cheques = count($campos_cheques);
            for($j = 0; $j < $linhas_cheques; $j++) { //Aqui dispara o vetor de Cheques
                $id_cheque_cliente      = $campos_cheques[$j]['id_cheque_cliente'];
                $valor_disponivel_ch 	= $campos_cheques[$j]['valor_disponivel'];// do cheque

                if($valor_reajustado >= $valor_disponivel_ch) { //Aqui a Parcela é maior do que o valor do Cheque
                    //Aqui eu zero o valor do cheque, para saber que eu matei aquele cheque
                    $sql = "UPDATE `cheques_clientes` SET `valor_disponivel` = '0', `status_disponivel` = '2' WHERE `id_cheque_cliente` = '$id_cheque_cliente' LIMIT 1 ";
                    $valor_recebendo_cheque = $valor_disponivel_ch;
                    if($valor_reajustado == 0) $j = $linhas_cheques;//Eu paro o for para nao pegar mais cheques, pois ñ preciso ...
                }else {//Aqui a Parcela é menor do que o valor Cheque
                    $valor_recebendo_cheque = $valor_reajustado;
                    $valor_disponivel_ch-= $valor_reajustado;
                    $sql = "UPDATE `cheques_clientes` SET `valor_disponivel` = '$valor_disponivel_ch', `status_disponivel`= '1' WHERE `id_cheque_cliente` = '$id_cheque_cliente' LIMIT 1";
                    //Aqui eu forço o cheque a sair fora do loop ele
                    $j = $linhas_cheques;
                }
                bancos::sql($sql);
                //A cada recebimento vou decrementado esta variável ...
                $valor_reajustado-= $valor_recebendo_cheque;

                /**************************************************************************/
                //Aqui eu recebo a conta exatamente na moeda em que ela é R$, U$, Euro ...
                if($id_tipo_moeda==1) {//Real, não existe tratamento a ser feito ...
                    $valor_recebendo_cheque_moeda = $valor_recebendo_cheque;
                }else if($id_tipo_moeda==2) {//Dólar
                    $valor_recebendo_cheque_moeda = $valor_recebendo_cheque / $_POST['txt_valor_dolar'];
                    //Arredondo p/ ficar com o valor mais preciso ...
                    $valor_recebendo_cheque_moeda = round(round($valor_recebendo_cheque_moeda, 3), 2);
                }else if($id_tipo_moeda==3) {//Euro
                    $valor_recebendo_cheque_moeda = $valor_recebendo_cheque / $_POST['txt_valor_euro'];
                    //Arredondo p/ ficar com o valor mais preciso ...
                    $valor_recebendo_cheque_moeda = round(round($valor_recebendo_cheque_moeda, 3), 2);
                }
                /**************************************************************************/
                $sql = "INSERT INTO `contas_receberes_quitacoes` (`id_conta_receber_quitacao`, `id_conta_receber`, `id_tipo_recebimento`, `id_contacorrente`, `id_cheque_cliente`, `valor`, `valor_moeda_dia`, `data`, `data_sys`) VALUES (NULL, '$vetor_conta_receber[$i]', '$id_tipo_recebimento', $cmb_conta_corrente, $id_cheque_cliente, '$valor_recebendo_cheque_moeda', '$valor_moeda_dia', '$txt_data', '$data_sys') ";
                bancos::sql($sql);
                $id_conta_receber_quitacao = bancos::id_registro();

                $ultimo_valor_recebendo+= $valor_recebendo_cheque;//caso ter varios cheques ele soma o valor total aqui
            }
            //Significa que o recebimento das contas foi feito com dinheiro, ou qualquer outra coisa, sem ser cheque
        }else {//Adicionando as parcelas recebidas de conta à receber em outras formas de pagamento 
            $ultimo_valor_recebendo = $txt_valor_recebendo[$i];

/*Somente se tivermos mais de uma Conta à Receber e estivermos exatamente na última à 
Conta à Receber do Looping ...*/
            if($linhas_contas_receberes > 1 && (($i + 1) == $linhas_contas_receberes)) {
/*Se o Total à Receber foi Negativo, então significa que o Cliente na realidade não nos deve nada 
e muito pelo contrário tem um Crédito para Receber aqui Conosco, então ... */
                if($_POST['txt_total_recebimento'] < 0) $ultimo_valor_recebendo-= $_POST['txt_total_recebimento'];
            }

/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
            $sql = "INSERT INTO `contas_receberes_quitacoes` (`id_conta_receber_quitacao`, `id_conta_receber`, `id_tipo_recebimento`, `id_contacorrente`, `valor`, `valor_moeda_dia`, `data`, `data_sys`) VALUES (NULL, '$vetor_conta_receber[$i]', '$id_tipo_recebimento', $cmb_conta_corrente, '$ultimo_valor_recebendo', '$valor_moeda_dia', '$txt_data', '$data_sys')";
            bancos::sql($sql);
            $id_conta_receber_quitacao = bancos::id_registro();
        }
        
        if(!empty($observacao)) {
            //Busca de alguns dados p/ Registro de Follow-UP ...
            $dados_cliente      = financeiros::nome_cliente_conta_receber($vetor_conta_receber[$i]);
            $id_cliente_loop    = $dados_cliente['id_cliente'];
            $id_cliente_contato = (!empty($dados_cliente['id_cliente_contato'])) ? $dados_cliente['id_cliente_contato'] : 'NULL';

            //Registrando Follow-UP(s) ...
            $id_representante   = genericas::buscar_id_representante($id_cliente_contato);

            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente_loop', $id_cliente_contato, '$_SESSION[id_funcionario]', '$vetor_conta_receber[$i]', '4', 'Controle de Recebimento: $observacao', '$data_sys') ";
            bancos::sql($sql);
        }
/***************************************************************************************/
/***************************************Reembolso***************************************/
/***************************************************************************************/
/*Verifica se essa duplicata consta o como Atraso de Pagamento, caso sim então o Sistema sozinho 
já faz o reembolso de Comissão para o Representante através dessa função ...*/
        $sql = "SELECT * 
                FROM `comissoes_estornos` 
                WHERE `id_conta_receber` = '$vetor_conta_receber[$i]' 
                AND `tipo_lancamento` = '1' LIMIT 1 ";
        $campos_estorno_comissao = bancos::sql($sql);
        $linhas_estorno_comissao = count($campos_estorno_comissao);
        for($j = 0; $j < $linhas_estorno_comissao; $j++) {
            /*Verifico o Valor Original da Conta e o Valor Total Recebido 
            s/ "Recebimento atual que esta sendo feito agora" p/ nao dar erro 
            nas logicas abaixo de Reembolso ...*/
            $sql = "SELECT valor, valor_pago 
                    FROM `contas_receberes` 
                    WHERE `id_conta_receber` = '$vetor_conta_receber[$i]' LIMIT 1 ";
            $campos_conta_receber = bancos::sql($sql);
            //Enquanto existe pendencia na Conta a Receber, posso gerar Reembolso ...
            if($campos_conta_receber[0]['valor'] > $campos_conta_receber[0]['valor_pago']) {
                /*Se o Valor Recebendo <= (Valor da Conta - o ja o total Recebido) ...
                Ex: 400 <= (1000 - 800)*/
                if($_POST['txt_valor_recebendo'][$i] <= ($campos_conta_receber[0]['valor'] - $campos_conta_receber[0]['valor_pago'])) {
                    $valor_reembolso = $_POST['txt_valor_recebendo'][$i];
                }else {
                    $valor_reembolso = $campos_conta_receber[0]['valor'] - $campos_conta_receber[0]['valor_pago'];
                }
                //Insiro um Reembolso da duplicata que foi paga e que teve anteriormente um Atraso de Pagamento ...
                $sql = "INSERT INTO `comissoes_estornos` (`id_comissao_estorno`, `id_nf`, `id_conta_receber`, `id_conta_receber_quitacao`, `id_representante`, `num_nf_devolvida`, `data_lancamento`, `tipo_lancamento`, `porc_devolucao`, `valor_duplicata`) VALUES (NULL, '".$campos_estorno_comissao[$j]['id_nf']."', '$vetor_conta_receber[$i]', '$id_conta_receber_quitacao', '".$campos_estorno_comissao[$j]['id_representante']."', '".$campos_estorno_comissao[$j]['num_nf_devolvida']."', '".date('Y-m-d')."', '3', '".$campos_estorno_comissao[$j]['porc_devolucao']."', '$valor_reembolso') ";
                bancos::sql($sql);
            }
        }
/***************************************************************************************/
        $ultimo_valor_recebendo = round($ultimo_valor_recebendo, 2);
//Aqui eu somo o valor da última parcela recebida recente da Conta à Receber ...
        $sql = "UPDATE `contas_receberes` SET `valor_pago` = `valor_pago` + '$ultimo_valor_recebendo' WHERE id_conta_receber = '$vetor_conta_receber[$i]' LIMIT 1 ";
        bancos::sql($sql);
/*Aqui eu instâncio novamente na função p/ saber o Quanto que ainda resta a Receber da Conta depois 
do último recebimento ...*/
        $calculos_conta_receber = financeiros::calculos_conta_receber($vetor_conta_receber[$i]);
        $valor_reajustado       = $calculos_conta_receber['valor_reajustado'];
/*O restante a Receber, sempre será igual o Valor Reajustado em R$ indepedente de a Conta ser 
em Dólar ou Euro ...*/
        $restante_receber       = $valor_reajustado;
/*Verifico se ainda resta alguma coisa à receber da conta, caso não falte nada, então significa que a conta 
foi recebido de modo exato, sem um centavo a + ou a -*/
        if($restante_receber == '0.00' || $restante_receber == 0) {
            $sql = "UPDATE `contas_receberes` SET `status` = '2' WHERE `id_conta_receber` = '$vetor_conta_receber[$i]' LIMIT 1 ";
        }else {
            $sql = "UPDATE `contas_receberes` SET `status` = '1' WHERE `id_conta_receber` = '$vetor_conta_receber[$i]' LIMIT 1 ";
        }
        bancos::sql($sql);
//Aqui eu zero essa variável p/ que não continue herdando valores no Próximo Loop ...
        $ultimo_valor_recebendo = 0;
    }
    
//Aqui retorna na tela para o usuário somente contas que não foram recebidas totalmente
    $sql = "SELECT id_conta_receber 
            FROM `contas_receberes` 
            WHERE `id_conta_receber` IN ($id_conta_receber) 
            AND `status` < '2' ";
    $campos = bancos::sql($sql);
    $linhas_status = count($campos);
    if($linhas_status > 0) {
        $id_conta_receber = '';
        for($i = 0; $i < $linhas_status; $i++) $id_conta_receber.= $campos[$i]['id_conta_receber'].', ';
        $id_conta_receber = substr($id_conta_receber, 0, strlen($id_conta_receber) - 2);
//Aqui nem todas as contas foram recebidas de forma total, então ainda volta para a tela de recebimento
?>
    <Script Language = 'JavaScript'>
        window.location = 'controle_recebimento.php?id_conta_receber=<?=$id_conta_receber;?>&valor=1'
    </Script>
<?
    }else {//Aqui todas as contas foram recebidas de forma total, daí já fecha a tela de Pop-Up automaticamente
?>
    <Script Language = 'JavaScript'>
        opener.parent.itens.recarregar_tela()
        alert('RECEBIMENTO EFETUADO COM SUCESSO !')
        window.close()
    </Script>
<?
    }
}else {
    //Busca do último valor do dólar e do euro
    $valor_dolar            = genericas::moeda_dia('dolar');
    $valor_euro             = genericas::moeda_dia('euro');
    $vetor_conta_receber    = explode(',', $id_conta_receber);
//Significa que a(s) conta(s) são toda(s) do mesmo Cliente
    $clientes_diferentes    = 0;
//Aqui eu verifico se as conta(s) selecionada(s) são do mesmo cliente
    for($i = 0; $i < count($vetor_conta_receber); $i++) {
        $dados_cliente 		= financeiros::nome_cliente_conta_receber($vetor_conta_receber[$i]);
        $id_cliente_loop 	= $dados_cliente['id_cliente'];
//Na primeira vez que carrega a Tela o Cliente é vazio, sendo assim só iguala o cliente com o do loop
        if(empty($id_cliente_antigo)) {
            $id_cliente_antigo = $id_cliente_loop;
        }else {//Já existe cliente
//Aqui eu verifico se o id_cliente_corrente é igual ao id_cliente_anterior
            if($id_cliente_loop != $id_cliente_antigo) {
/*Significa que a(s) conta(s) são de Cliente(s) diferente(s) e se isso acontece o Sistema não sabe 
para quem jogar valores Negativos caso aconteça ...*/
                $clientes_diferentes = 1;
            }
        }
    }
/*****************************Controle p/ envio de e-mail*****************************/
    $data_atual = date('Y-m-d');
/*Aqui eu verifico se tem alguma conta que é do Tipo Cartório ou Protestado e com mais 
de 15 dias de atraso ...*/
    $sql = "SELECT num_conta 
            FROM `contas_receberes` 
            WHERE `id_conta_receber` IN ($id_conta_receber) 
            AND (`id_tipo_recebimento` IN (7, 9) 
            OR DATEDIFF('$data_atual', `data_vencimento_alterada`) > 15) ";
    $campos_irregulares = bancos::sql($sql);
    $linhas_irregulares = count($campos_irregulares);
    if($linhas_irregulares > 0) {//Se existir pelo menos 1 conta que segue nesse critério ...
        //Listagem do N.º das contas ...
        for($i = 0; $i < $linhas_irregulares; $i++) $numeros.= $campos_irregulares[$i]['num_conta'].', ';
        $numeros = substr($numeros, 0, strlen($numeros) - 2);
    }
/*************************************************************************************/
    //Aqui eu verifico se existe alguma conta à Receber em que na Duplicata possui a marcação de Livre de Déb ...
    $sql = "SELECT nfs.id_nf, IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente 
            FROM `contas_receberes` cr 
            INNER JOIN `nfs` ON nfs.id_nf = cr.id_nf AND nfs.livre_debito = 'S' 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            WHERE cr.`id_conta_receber` IN ($id_conta_receber) LIMIT 1 ";
    $campos_livre_debito = bancos::sql($sql);
/*Se existir 1 conta que esteje com essa Marcação Livre de Débito, então o Sistema retorna um aviso p/ o
usuário informando este que está conta X não pode ser quitada por aqui, e somente pela opção de 
Contas à Pagar*/
    if(count($campos_livre_debito) == 1) {
?>
    <Script Language = 'JavaScript'>
        alert('O CLIENTE "'+'<?=$campos_livre_debito[0]["cliente"]?>'+'" POSSUE CONTA(S) COM A MARCAÇÃO LIVRE DE DÉBITO !\nO RECEBIMENTO DESSA SÓ PODE SER EFETUADO MEDIANTE A SEU PAGAMENTO EM "CONTAS À PAGAR" PELA FERRAMENTA DE ACERTO DE CONTAS !!!')
        window.close()
    </Script>
<?
    }
/*************************************************************************************/
?>
<html>
<head>
<title>.:: Quitar Conta à Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script language = 'JavaScript'>
function separar() {
    var tipo_recebimento    = document.form.cmb_tipo_recebimento.value
    var achou               = 0
    var id_tipo_recebimento = ''
    var status              = ''
    var elementos           = document.form.elements
    if(typeof(elementos['txt_tipo_moeda[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_tipo_moeda[]'].length)
    }
	
    for(i = 0; i < tipo_recebimento.length; i++) {
        if(tipo_recebimento.charAt(i) == '|') {
            achou = 1
        }else {
            if(achou == 0) {
                id_tipo_recebimento+= tipo_recebimento.charAt(i)
            }else {
                status+= tipo_recebimento.charAt(i)
            }
        }
    }
    document.form.id_tipo_recebimento.value = id_tipo_recebimento
    document.form.status.value = status
	
/*Se for escolhido uma opção que é diferente de Cheque, então ele limpa a caixa de valor total em cheques
e limpa a caixa com os ids_cheques*/
    if(document.form.id_tipo_recebimento.value != 5) {
        document.form.txt_total_em_cheques.value = ''
        document.form.hdd_cheques_clientes.value = ''
        for(var i = 0; i < linhas; i++) {
            document.getElementById('txt_valor_recebendo'+i).disabled   = false
            document.getElementById('txt_valor_recebendo'+i).className  = 'caixadetexto'
        }
//Significa que foi escolhido a Opção de Cheque como sendo Tipo de Recebimento
    }else {
        for(var i = 0; i < linhas; i++) {
            document.getElementById('txt_valor_recebendo'+i).disabled   = true
            document.getElementById('txt_valor_recebendo'+i).className  = 'textdisabled'
        }
    }
    if(document.form.status.value == 0) {//Desabilita a Conta Corrente ...
        document.form.cmb_conta_corrente.value      = ''
        document.form.cmb_conta_corrente.disabled   = true
        document.form.cmb_conta_corrente.className  = 'textdisabled'
    }else {//Habilita o Banco
        document.form.cmb_conta_corrente.disabled   = false
        document.form.cmb_conta_corrente.className  = 'caixadetexto'
    }
}

function validar() {
//Tipo de Recebimento
    if(document.form.cmb_tipo_recebimento.value == '') {
        alert('SELECIONE O TIPO DE RECEBIMENTO !')
        document.form.cmb_tipo_recebimento.focus()
        return false
    }
/*Se o Tipo de Recebimento foi selecionado como cheque, então tenho que verificar se já existe algum cheque
selecionado*/
    if(document.form.id_tipo_recebimento.value == 5) {
        if(document.form.hdd_cheques_clientes.value == '') {
            alert('SELECIONE UM CHEQUE !')
            return false
        }
    }
//Conta Corrente ...
    if(document.form.cmb_conta_corrente.disabled == false) {
        if(document.form.cmb_conta_corrente.value == '') {
            alert('SELECIONE A CONTA CORRENTE !')
            document.form.cmb_conta_corrente.focus()
            return false
        }
    }
//Data de Emissão
    if(!data('form','txt_data','4000','EMISSÃO')) {
        return false
    }
//Objetos
    var elementos = document.form.elements
    if(typeof(elementos['txt_tipo_moeda[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_tipo_moeda[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_valor_recebendo'+i).value == '') {
            alert('DIGITE O VALOR RECEBENDO !')
            document.getElementById('txt_valor_recebendo'+i).select()
            return false
        }
    }
/***************************************************************************************/
    var total_recebimento   = eval(strtofloat(document.form.txt_total_recebimento.value))
    if(total_recebimento < 0) {
        var clientes_diferentes = '<?=$clientes_diferentes;?>'
        //Significa que a(s) conta(s) são de Cliente(s) diferente(s) ...
        if(clientes_diferentes > 0) {
            alert('O TOTAL DE RECEBIMENTO NÃO PODE SER NEGATIVO PARA CLIENTE(S) DIFERENTE(S) !')
            return false
        }
    }
/******************Controle com algum Dado q foi alterado pelo usuário******************/
//Aqui eu verifico se tem alguma conta que é do Tipo Cartório, Protestado ou com mais de 15 dias de atraso ...
    var linhas_irregulares = eval('<?=$linhas_irregulares;?>')
    if(linhas_irregulares > 0) {
//Verifico se a Data de Vencimento foi alterada pelo usuário ...
        var justificativa = prompt('DIGITE UMA JUSTIFICATIVA COM BASE NO SERASA !!!\n\nPARA A(S) CONTA(S): <?=$numeros;?>.\nESTAS CONTAS SÃO DO TIPO CARTÓRIO, PROTESTADO OU ESTÃO COM MAIS DE 15 DIAS DE ATRASO NA DATA DE VENCIMENTO: ')
        document.form.hdd_justificativa.value = justificativa
//Controle com a Justificativa ...
        if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
            alert('JUSTIFICATIVA INVÁLIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ ALTERAÇÃO DE DADO(S) !')
            return false
        }
    }
/***************************************************************************************/
    //Prepara as Caixas p/ gravar no Banco de Dados ...
    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_valor_receber'+i).disabled     = false
        document.getElementById('txt_valor_recebendo'+i).disabled   = false
        document.getElementById('txt_valor_receber'+i).value        = strtofloat(document.getElementById('txt_valor_receber'+i).value)
        document.getElementById('txt_valor_recebendo'+i).value      = strtofloat(document.getElementById('txt_valor_recebendo'+i).value)
    }
    document.form.txt_total_em_cheques.disabled     = false
    document.form.txt_total_recebimento.disabled    = false
	
    //Aqui eu travo o botão de salvar para o usuário não submeter a Informação mais de uma vez ...
    document.form.cmd_salvar.className  = 'textdisabled'
    document.form.cmd_salvar.disabled   = true
//Aqui é para não atualizar o frame de Itens abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    return limpeza_moeda('form', 'txt_valor_dolar, txt_valor_euro, txt_total_recebimento, ')
}

//Essa função calcula o valor da conta apenas na linha corrente
function calcular_conta(indice, tipo_moeda) {
/*Essa variável valor à receber serve para me retornar o quanta que falta da
conta para receber na moeda R$, U$ ou Euro*/
    var valor_receber_real_vetor    = new Array('<?=count($vetor_conta_receber);?>')
    //O sistema verifica se o valor com o qual qual irá trabalhar será com Juros ou não ...
<?
    for($i = 0; $i < count($vetor_conta_receber); $i++) {
        //Busca de alguns dados da Conta à Receber "Duplicata" ...
        $sql = "SELECT valor, valor_pago 
                FROM `contas_receberes` 
                WHERE `id_conta_receber` = '".$vetor_conta_receber[$i]."' LIMIT 1 ";
        $campos_conta_receber   = bancos::sql($sql);
        $calculos_conta_receber = financeiros::calculos_conta_receber($vetor_conta_receber[$i]);
        
        //$valor_reajustado => sempre retorna o valor da Duplicata em R$ por causa da função em PHP ...
        $valor_reajustado   = $calculos_conta_receber['valor_reajustado'];
        $valor_juros        = $calculos_conta_receber['valor_juros'];
?>
        if(document.form.chkt_zerar_juros.checked == true) {//Significa que o usuário resolveu tirar os Juros ...
            valor_receber_real_vetor['<?=$i?>'] = '<?=number_format($valor_reajustado - $valor_juros, 2, ',', '.');?>'
        }else {//Significa que o usuário decidiu colocar os Juros ...
            valor_receber_real_vetor['<?=$i?>'] = '<?=number_format($valor_reajustado, 2, ',', '.');?>'
        }
<?
    }
?>
    valor_a_receber_corrente = (document.getElementById('txt_valor_recebendo'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_valor_recebendo'+indice).value)) : 0
//Verifica o tipo da moeda da Conta Dólar ou Euro
    if(tipo_moeda == 1) {
        valor_moeda = 1
    }else if(tipo_moeda == 2) {//Dólar
        valor_moeda = eval(strtofloat(document.form.txt_valor_dolar.value))
    }else if(tipo_moeda == 3) {//Euro
        valor_moeda = eval(strtofloat(document.form.txt_valor_euro.value))
    }
    //Essas variáveis eu já deixo preparadas p/ as Contas à Receber que são Internacionais ...
    var valor_dolar = '<?=$valor_dolar;?>'
    var valor_euro  = '<?=$valor_euro;?>'
    /***********************Controle com Moeda Estrangeira***********************/
    /*Esse Macete é p/ que o Cálculo em JS fique dinâmico, pois o que acontece: o valor_receber_real_vetor[indice]
    que foi retornado vem diretamente da função em PHP e não leva em Conta o Valor de Dólar ou Euro que é 
    digitada na Caixa dessa Tela pelo usuário ...
        
    Sendo assim o que eu faço é o seguinte: tiro o Dólar ou Euro que vem como Padrão da Função e acrescento o 
    que foi digitado pelo Usuário na Caixinha ...*/
    if(document.getElementById('txt_tipo_moeda'+indice).value == 2) {//Conta em Dólar ...
        /*Como sempre vem em R$ por causa da função em PHP, é necessário transformar 
        em Dólar no seu valor original ...*/
        valor_receber_real_vetor[indice] = strtofloat(valor_receber_real_vetor[indice])
        valor_receber_real_vetor[indice]/= valor_dolar
        //Transformo a variável "valor_receber_real_vetor[indice]" em String p/ arredondar p/ 2 casas ...
        valor_receber_real_vetor[indice] = arred(String(valor_receber_real_vetor[indice]), 2, 1)
    }else if(document.getElementById('txt_tipo_moeda'+indice).value == 3) {//Conta em Euro ...
        /*Como sempre vem em R$ por causa da função em PHP, é necessário transformar 
        em Euro no seu valor original ...*/
        valor_receber_real_vetor[indice] = strtofloat(valor_receber_real_vetor[indice])
        valor_receber_real_vetor[indice]/= valor_euro
        //Transformo a variável "valor_receber_real_vetor[indice]" em String p/ arredondar p/ 2 casas ...
        valor_receber_real_vetor[indice] = arred(String(valor_receber_real_vetor[indice]), 2, 1)
    }
    /****************************************************************************/
/*Aqui é o quanto falta para receber da conta na moeda da conta R$, U$, ? ...
//Esse variável valor_recebendo_aux[indice] é o valor devido da conta na moeda correnta daquela conta 
e nela também já está embutido todas as taxas de juros, acréscimos, etc ...
Na outra parte -> (valor_a_receber_corrente / valor_moeda), eu transformo o valor em R$ para moeda da conta U$, ? */   
    var valor_reajustado    = strtofloat(valor_receber_real_vetor[indice])
    var valor_recebendo     = strtofloat(document.getElementById('txt_valor_recebendo'+indice).value)
    //Calculando ...
    document.getElementById('txt_valor_receber'+indice).value = valor_reajustado - valor_recebendo / valor_moeda
    document.getElementById('txt_valor_receber'+indice).value = arred(document.getElementById('txt_valor_receber'+indice).value, 2, 1)
}

function zerar_juros() {
    var elementos                   = document.form.elements
    var valor_receber_real_vetor    = new Array('<?=count($vetor_conta_receber);?>')
    //O sistema verifica se o valor com o qual qual irá trabalhar será com Juros ou não ...
<?
    for($i = 0; $i < count($vetor_conta_receber); $i++) {
        //Busca de alguns dados da Conta à Receber "Duplicata" ...
        $sql = "SELECT valor, valor_pago 
                FROM `contas_receberes` 
                WHERE `id_conta_receber` = '".$vetor_conta_receber[$i]."' LIMIT 1 ";
        $campos_conta_receber   = bancos::sql($sql);
        $calculos_conta_receber = financeiros::calculos_conta_receber($vetor_conta_receber[$i]);
        
        //$valor_reajustado => sempre retorna o valor da Duplicata em R$ por causa da função em PHP ...
        $valor_reajustado   = $calculos_conta_receber['valor_reajustado'];
        $valor_juros        = $calculos_conta_receber['valor_juros'];
?>
        if(document.form.chkt_zerar_juros.checked == true) {//Significa que o usuário resolveu tirar os Juros ...
            valor_receber_real_vetor['<?=$i?>'] = '<?=number_format($valor_reajustado - $valor_juros, 2, ',', '.');?>'
        }else {//Significa que o usuário decidiu colocar os Juros ...
            valor_receber_real_vetor['<?=$i?>'] = '<?=number_format($valor_reajustado, 2, ',', '.');?>'
        }
<?
    }
?>
    //Essas variáveis eu já deixo preparadas p/ as Contas à Receber que são Internacionais ...
    var valor_dolar = '<?=$valor_dolar;?>'
    var valor_euro  = '<?=$valor_euro;?>'
   
    if(typeof(elementos['txt_tipo_moeda[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_tipo_moeda[]'].length)
    }
    
    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_valor_receber'+i).value = '0,00'
        /***********************Controle com Moeda Estrangeira***********************/
        /*Esse Macete é p/ que o Cálculo em JS fique dinâmico, pois o que acontece: o valor_receber_real_vetor['$i']
        que foi retornado vem diretamente da função em PHP e não leva em Conta o Valor de Dólar ou Euro que é 
        digitada na Caixa dessa Tela pelo usuário ...
        
        Sendo assim o que eu faço é o seguinte: tiro o Dólar ou Euro que vem como Padrão da Função e acrescento o 
        que foi digitado pelo Usuário na Caixinha ...*/
        if(document.getElementById('txt_tipo_moeda'+i).value == 2) {//Conta em Dólar ...
            /*Como sempre vem em R$ por causa da função em PHP, é necessário transformar 
            em Dólar no seu valor original ...*/
            valor_receber_real_vetor[i] = strtofloat(valor_receber_real_vetor[i])
            valor_receber_real_vetor[i]/= valor_dolar
            //Transformo a variável "valor_receber_real_vetor[i]" em String p/ arredondar p/ 2 casas ...
            valor_receber_real_vetor[i] = arred(String(valor_receber_real_vetor[i]), 2, 1)
            //Cálculo baseado no Dólar digitado pelo Usuário na Caixinha ...
            var calculo_em_reais  = strtofloat(valor_receber_real_vetor[i]) * strtofloat(document.form.txt_valor_dolar.value)
            document.getElementById('txt_valor_recebendo'+i).value = arred(String(calculo_em_reais), 2, 1)
        }else if(document.getElementById('txt_tipo_moeda'+i).value == 3) {//Conta em Euro ...
            /*Como sempre vem em R$ por causa da função em PHP, é necessário transformar 
            em Euro no seu valor original ...*/
            valor_receber_real_vetor[i] = strtofloat(valor_receber_real_vetor[i])
            valor_receber_real_vetor[i]/= valor_euro
            //Transformo a variável "valor_receber_real_vetor[i]" em String p/ arredondar p/ 2 casas ...
            valor_receber_real_vetor[i] = arred(String(valor_receber_real_vetor[i]), 2, 1)
            //Cálculo baseado no Euro digitado pelo Usuário na Caixinha ...
            var calculo_em_reais  = strtofloat(valor_receber_real_vetor[i]) * strtofloat(document.form.txt_valor_euro.value)
            document.getElementById('txt_valor_recebendo'+i).value = arred(String(calculo_em_reais), 2, 1)
        }else {//Conta em Real ... Não precisa fazer nada ...
            document.getElementById('txt_valor_recebendo'+i).value = valor_receber_real_vetor[i]
        }
        /****************************************************************************/
    }
    valor_total_contas()
    verificar_contas_negativas()
}

//Desconta em cima de cada Conta os valores de Cheques atrelados
function debater_valor_cheque() {
    var elementos           = document.form.elements
    var total_em_cheques    = eval(strtofloat(document.form.txt_total_em_cheques.value))
    if(typeof(elementos['txt_tipo_moeda[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_tipo_moeda[]'].length)
    }
//Aqui é quando a caixa de total de cheques está vázia
    if(typeof(total_em_cheques) != 'undefined') {
        for(var i = 0; i < linhas; i++) {
            tipo_moeda                  = document.getElementById('txt_tipo_moeda'+i).value
            valor_a_receber_corrente    = eval(strtofloat(document.getElementById('txt_valor_recebendo'+i).value)) + eval(strtofloat(document.getElementById('txt_valor_receber'+i).value))
//Aqui é quando a caixa valor recebendo está vázia
            if(typeof(valor_a_receber_corrente) == 'undefined' || valor_a_receber_corrente == '') valor_a_receber_corrente = 0
//Aqui eu verifico se o valor do Cheque é maior que o valor da Conta
            if(total_em_cheques > valor_a_receber_corrente) {//Valor do Cheque > do que o Valor da Conta
//Desconto no Cheque o valor daquela Conta
                total_em_cheques-= valor_a_receber_corrente
                document.getElementById('txt_valor_receber'+i).value      = '0,00'
                document.getElementById('txt_valor_recebendo'+i).value    = valor_a_receber_corrente
            }else {//Valor do Cheque < do que o Valor da Conta
                document.getElementById('txt_valor_recebendo'+i).value    = total_em_cheques
                document.getElementById('txt_valor_receber'+i).value      = valor_a_receber_corrente - total_em_cheques
//Zera a variável de cheques para não dar problema nas outras caixas de valor à receber
                total_em_cheques = 0
                if(tipo_moeda == 2) {//Dólar
                    valor_dolar = eval(strtofloat(document.form.txt_valor_dolar.value))
                    document.getElementById('txt_valor_receber'+i).value /= eval(valor_dolar)
                }else if(tipo_moeda == 3) {
                    valor_euro = eval(strtofloat(document.form.txt_valor_euro.value))
                    document.getElementById('txt_valor_receber'+i).value /= eval(valor_euro)
                }
                document.getElementById('txt_valor_receber'+i).value = arred(document.getElementById('txt_valor_receber'+i).value, 2, 1)
            }
            document.getElementById('txt_valor_recebendo'+i).value = arred(document.getElementById('txt_valor_recebendo'+i).value, 2, 1)
        }
    }
    valor_total_contas()
}

//Faz um somatório do Valor de todas as contas, bem simplesinho
function valor_total_contas() {
    var elementos = document.form.elements
    var valor_total_a_receber = 0

    if(typeof(elementos['txt_tipo_moeda[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_tipo_moeda[]'].length)
    }

    for(i = 0; i < linhas; i++) {
        valor_a_receber_corrente = (document.getElementById('txt_valor_recebendo'+i).value != '') ? eval(strtofloat(document.getElementById('txt_valor_recebendo'+i).value)) : 0
        valor_total_a_receber+= eval(valor_a_receber_corrente)
    }
    document.form.txt_total_recebimento.value = valor_total_a_receber
    document.form.txt_total_recebimento.value = arred(document.form.txt_total_recebimento.value, 2, 1)
}

//Serve para abrir o Pop-Up para cadastramento dos Cheques, bem simplesinho
function controle_cheques(id_cliente) {      
//Significa que não existe Cliente, então não abre a Tela para Inclusão de Cheques
    if(id_cliente != '') {
//Aqui faz o apontamento do Tipo de Recebimento para Cheque Automaticamente, assim que clica no link
        document.form.cmb_tipo_recebimento.value = '5|0'
        separar()
        var clientes_diferentes = '<?=$clientes_diferentes;?>'
//Significa que a(s) conta(s) são toda(s) do mesmo Cliente
        if(clientes_diferentes == 0) {
            nova_janela('controle_cheques.php?id_cliente='+id_cliente+'&id_cheques_clientes='+document.form.hdd_cheques_clientes.value, 'CONSULTAR', '', '', '', '', '500', '920', 'c', 'c', '', '', 's', 's', '', '', '')
//Significa que a(s) conta(s) são de Cliente(s) diferente(s)
        }else {
            alert('NÃO É POSSÍVEL FAZER ATRELAMENTO DE CHEQUE(S) PARA ESTA(S) CONTA(S) !\nDEVIDO A EXISTÊNCIA DE CLIENTE(S) DIVERSIFICADO(S) !!!')
        }
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    var valor = '<?=$valor?>'
//Significa que já foi submetido pelo menos uma vez
    if(valor > 0) {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
        if(document.form.nao_atualizar.value == 0) opener.parent.itens.recarregar_tela()
    }
}

/*Tenho que ter pelo menos 2 contas selecionadas p/ poder chamar essa função, de modo 
a deixar a última Conta à Receber do Cliente Credito caso isso exista ...
 
Essa função foi comentada no dia 09/09/2013 devido acharmos que essa seria um acerto 
de Contas que deveria ser feita apenas p/ Contas do mesmo Cliente ...*/
function verificar_contas_negativas() {
    /*var numero_contas_selecionadas  = '<?=count($vetor_conta_receber);?>'
    var total_recebimento           = eval(strtofloat(document.form.txt_total_recebimento.value))
    var elementos                   = document.form.elements
//Tenho que ter pelo menos 2 contas selecionadas ...
    if(total_recebimento < 0 && numero_contas_selecionadas > 1) {
        for(i = 0; i < elementos.length; i++) {
            if(elementos[i].name == 'txt_valor_recebendo[]') {
                
                ind_ult_box = i
                val_ult_box = eval(strtofloat(elementos[i].value))
            }
        }
//Valor à Receber - Objeto Anterior ...
        valor_a_receber                                 = eval(strtofloat(document.form.elements[ind_ult_box - 1].value))
        document.form.elements[ind_ult_box - 1].value   = valor_a_receber + total_recebimento
        document.form.elements[ind_ult_box - 1].value   = arred(document.form.elements[ind_ult_box - 1].value, 2, 1)
//Valor Recebendo ...
        document.form.elements[ind_ult_box].value       = val_ult_box - total_recebimento
        document.form.elements[ind_ult_box].value       = arred(document.form.elements[ind_ult_box].value, 2, 1)
    }*/
    valor_total_contas()
}
</Script>
</head>
<body onunload='atualizar_abaixo()' onload='verificar_contas_negativas()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<!--Aqui esses hiddens estão relacionados a uma função do JavaScript-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_justificativa'>
<input type='hidden' name='id_tipo_recebimento'>
<input type='hidden' name='status'>
<!--*************** Outros hiddens *********************************-->
<input type='hidden' name='id_conta_receber' value='<?=$id_conta_receber;?>'>
<!--****************************************************************-->
<table width='95%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='6'>
            Quitar Conta à Receber
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Tipo de Recebimento:</b>
        </td>
        <td colspan='4'>
            <b>Conta Corrente:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <select name='cmb_tipo_recebimento' title='Selecione o Tipo de Recebimento' onchange='separar()' class='combo'>
            <?
                $sql = "SELECT CONCAT(id_tipo_recebimento, '|', status) AS tipo_recebimento_status, recebimento 
                        FROM `tipos_recebimentos` 
                        WHERE `ativo` = '1' ORDER BY recebimento ";
                echo combos::combo($sql, $cmb_tipo_recebimento);
            ?>
            </select>
        </td>
        <td colspan='4'>
            <select name='cmb_conta_corrente' title='Selecione a Conta Corrente' class='textdisabled' disabled>
            <?
                /*Se o Menu acessado pelo Usuário em Contas à Receber for "Todas Empresas" $id_emp = '0' 
                sempre trago todas as Contas Correntes cadastradas no sistema, do contrário só trago 
                as Contas Correntes do Menu acessado pelo Usuário "Só Alba ou Só Tool" ...*/
                if($id_emp > 0) $condicao_conta_corrente = " AND cc.`id_empresa` = '$id_emp' ";
            
                //Aqui é para a empresa do tipo Albafer, Tool Master ...
                $sql = "SELECT DISTINCT(cc.`id_contacorrente`) AS id_id_contacorrente, CONCAT(b.`banco`, ' | ',cc. `conta_corrente`) AS banco 
                        FROM `bancos` b 
                        INNER JOIN `agencias` a ON a.id_banco = b.id_banco AND a.`ativo` = '1' 
                        INNER JOIN `contas_correntes` cc ON cc.`id_agencia` = a.`id_agencia` $condicao_conta_corrente 
                        WHERE b.`ativo` = '1' ORDER BY banco ";
                echo combos::combo($sql, $cmb_conta_corrente);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Data de Recebimento / Vencimento do Cheque:
        </td>
        <td colspan='2'>
            <font color='blue'>
                <b>Valor Dólar:</b>
            </font>
        </td>
        <td colspan='2'>
            <font color='blue'>
                <b>Valor Euro:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_data' value='<?=date('d/m/Y');?>' title='Digite a Data de Recebimento' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src = '../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../calendario/calendario.php?campo=txt_data&tipo_retorno=1&chamar_funcao=2', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
        <td colspan='2'>
            <input type='text' name='txt_valor_dolar' value='<?=number_format($valor_dolar, 4, ',', '.');?>' title='Valor Dólar' size='12' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '4', '', event);zerar_juros();debater_valor_cheque()" class='caixadetexto'>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_valor_euro' value='<?=number_format($valor_euro, 4, ',', '.');?>' title='Valor Euro' size='12' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '4', '', event);zerar_juros();debater_valor_cheque()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Cheques</b>
        </td>
        <td colspan='4'>
            <b>Total em Cheques</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <a href="javascript:controle_cheques('<?=$id_cliente_antigo;?>')" title='Controle de Cheques' class='link'>
                <img src = '../../../../../imagem/propriedades.png' border='0' title='Controle de Cheques' alt='Controle de Cheques' onclick="controle_cheques('<?=$id_cliente_antigo;?>')"> Controle de Cheques
            </a>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_total_em_cheques' class='textdisabled' disabled>
            <input type='hidden' name='hdd_cheques_clientes'>
        </td>
        <td colspan='2'>
            <input type='checkbox' name='chkt_zerar_juros' value='1' onclick='zerar_juros();debater_valor_cheque()' title="Zerar Juros" id='zerar' class='checkbox'>
            <label for='zerar'>Zerar Juros</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N.º / Conta:</b>
        </td>
        <td>
            <b>Cliente / Descrição da Conta:</b>
        </td>
        <td>
            <b>Valor Total:</b>
        </td>
        <td>
            <b>Valor Recebido:</b>
        </td>
        <td>
            <b>Valor à Receber:</b>
        </td>
        <td>
            <b>Valor Recebendo:</b>
        </td>
    </tr>
<?
        for($i = 0; $i < count($vetor_conta_receber); $i++) {
            $dados_cliente      = financeiros::nome_cliente_conta_receber($vetor_conta_receber[$i]);
            $id_cliente_loop 	= $dados_cliente['id_cliente'];
            $cliente            = $dados_cliente['cliente'];
/***************************************************************************/
//Busca de Alguns Dados da Conta à Receber para verificar se está foi recebida parcialmente ...
            $sql = "SELECT cr.id_tipo_moeda, cr.num_conta, cr.valor, cr.valor_pago, cr.status AS status_conta, tm.simbolo 
                    FROM `contas_receberes` cr 
                    INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
                    WHERE cr.`id_conta_receber` = '$vetor_conta_receber[$i]' LIMIT 1 ";
            $campos_contas_receber 		= bancos::sql($sql);
            $id_tipo_moeda 	= $campos_contas_receber[0]['id_tipo_moeda'];
            $num_conta 		= $campos_contas_receber[0]['num_conta'];
            $valor_conta 	= $campos_contas_receber[0]['valor'];
            $valor_recebido     = $campos_contas_receber[0]['valor_pago'];
            $moeda              = $campos_contas_receber[0]['simbolo'];
            if(strlen($moeda) == 1) $moeda.= '&nbsp;&nbsp;';

            $calculos_conta_receber = financeiros::calculos_conta_receber($vetor_conta_receber[$i]);
            
            //O Valor Reajustado, já está descontando o que foi recebido daquela Duplicata ...
            $valor_reajustado       = $calculos_conta_receber['valor_reajustado'];
            if($valor_reajustado == '-0.00') $valor_reajustado = 0;
//Aqui eu tenho o total da soma de várias parcelas
            $valor_total_receber_real+= $valor_reajustado;
?>
    <tr class='linhanormal'>
        <td>
            <!--Para que a tela seja aberta como Pop-UP ...-->
            <a href="javascript:nova_janela('../../alterar.php?id_conta_receber=<?=$vetor_conta_receber[$i];?>&pop_up=1', 'DETALHES', '', '', '', '', 520, 950, 'c', 'c', '', '', 's', 's', '', '', '', '')" title="Parcelas Recebidas" class='link'>
                <?=$campos_contas_receber[0]['num_conta'];?>
            </a>
        </td>
        <td>
            <!--Para que a tela seja aberta como Pop-UP ...-->
            <a href="javascript:nova_janela('../../alterar.php?id_conta_receber=<?=$vetor_conta_receber[$i];?>&pop_up=1', 'DETALHES', '', '', '', '', 520, 950, 'c', 'c', '', '', 's', 's', '', '', '', '')" title="Parcelas Recebidas" class="link">
            <?
                if(!empty($cliente) && $cliente != '&nbsp;') echo $cliente.' / ';

                if($campos_contas_receber[0]['descricao_conta'] == '') {
                    echo '&nbsp;';
                }else {
                    echo $campos_contas_receber[0]['descricao_conta'];
                }
//Visualizando as Contas à Pagar
                $retorno        = financeiros::contas_em_aberto($id_cliente_loop, 1, $id_emp, 1);
                $qtde_contas 	= count($retorno['id_contas']);
//Significa que existem Contas à Pagar desse Cliente
                if($qtde_contas > 0) {
            ?>
                    &nbsp;<img src = '../../../../../imagem/icones/outros.gif' width='33' height='20' border='0' title='Exite(m) <?=$qtde_contas;?> Conta(s) à Pagar desse Cliente'>
            <?
                }
            ?>
            </a>
        </td>
        <td align='right'>
            <input type='hidden' name='txt_tipo_moeda[]' id='txt_tipo_moeda<?=$i;?>' value='<?=$id_tipo_moeda;?>'>
            <?=$moeda;?><input type='text' name='txt_valor_total[]' id='txt_valor_total<?=$i;?>' value="<?=str_replace('.', ',', $valor_conta);?>" title='Valor Total' size='12' maxlength='15' class='textdisabled' disabled>
        </td>
        <td align='right'>
            <?=$moeda;?><input type='text' name='txt_valor_recebido[]' id='txt_valor_recebido<?=$i;?>' value="<?=number_format($valor_recebido, 2, ',', '.');?>" title="Valor Recebido" size="12" maxlength="15" class='textdisabled' disabled>
        </td>
        <td align='right'>
        <?
//Verifica o tipo da moeda da Conta Dólar ou Euro
            if($id_tipo_moeda == 1) {
                $valor_moeda = 1;
            }else if($id_tipo_moeda == 2) {//Dólar
                $valor_moeda = $valor_dolar;
            }else if($id_tipo_moeda == 3) {//Euro
                $valor_moeda = $valor_euro;
            }
        ?>
            <?=$moeda;?><input type='text' name='txt_valor_receber[]' id='txt_valor_receber<?=$i;?>' value='0,00' title='Valor à Receber' size='12' maxlength='15' class='textdisabled' disabled>
        </td>
        <td align='right'>
            R$ <input type='text' name='txt_valor_recebendo[]' id='txt_valor_recebendo<?=$i;?>' value="<?=number_format($valor_reajustado, 2, ',', '.');?>" title="Digite o Valor" size="12" maxlength="15" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_conta('<?=$i;?>', '<?=$id_tipo_moeda;?>');valor_total_contas()" class='caixadetexto'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b>Total de Contas:</b> <?=$i;?>
        </td>
        <td colspan='2' align='right'>
            <font color='red'>
                <b>Valor Total R$ : </b>
            </font>
            <input type='text' name='txt_total_recebimento' value='<?=number_format($valor_total_receber_real, 2, ',', '.');?>' size='16' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='6'>
            Observação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='6'>
            <textarea name='txt_observacao' title='Digite a Observação' rows='5' cols='120' maxlength='600' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');separar();verificar_contas_negativas()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<Script Language = 'JavaScript'>
//Acabando de carregar o formulário ele tem que redefinir os campos para não dar pau
    document.form.reset()
</Script>
<?}?>