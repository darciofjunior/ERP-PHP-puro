<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/depto_pessoal.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');

/*Eu tenho esse desvio aki para n�o verificar a sess�o desse arkivo, fa�o isso pq esse arquivo aki � um 
pop-up em outras partes do sistema e se eu n�o fizer esse desvio d� erro de permiss�o*/
if($nao_verificar_sessao != 1) {
    switch($opcao) {
        case 1://Significa que veio do Menu Abertas / Liberadas ...
        case 2://Significa que veio do Menu de Liberadas / Faturadas ...
        case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
        case 4://Significa que veio do Menu de Devolu��o 
            segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
        break;
        default://Significa que veio do Menu de Devolu��o ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
    }
}

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_nf      = $_POST['id_nf'];
    $opcao      = $_POST['opcao'];
    $acao       = $_POST['acao'];
}else {
    $id_nf      = $_GET['id_nf'];
    $opcao      = $_GET['opcao'];
    $acao       = $_GET['acao'];
}

//Essa fun��o atualiza todas as Notas Fiscais que s�o Vide-Notas da Nota Fiscal Principal ...
function atualizar_vide_notas($id_nf, $id_cliente, $id_empresa_nf, $id_funcionario, $data_saida_entrada, $hora_saida_entrada, $data_envio, $hora_envio, $status, $tipo_despacho, $numero_remessa) {
    //Data Atual ...
    $data_sys = date('Y-m-d H:i:s');
//Busco as Notas Fiscais atreladas a est� Nota Fiscal ...
    $sql = "SELECT `id_nf` 
            FROM `nfs` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `id_nf_vide_nota` = '$id_nf' ORDER BY `id_nf` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($j = 0; $j < $linhas; $j++) {
//Atualiza a Nota Fiscal com os novos dados de Cabe�alho da Nota Principal ...
        $sql = "UPDATE `nfs` SET `id_funcionario` = '$id_funcionario', `data_saida_entrada` = '$data_saida_entrada', `hora_saida_entrada` = '$hora_saida_entrada', `data_envio` = '$data_envio', `hora_envio` = '$hora_envio', `data_sys` = '$data_sys', `status` = '$status', `tipo_despacho` = '$tipo_despacho', `numero_remessa` = '$numero_remessa' WHERE `id_nf` = '".$campos[$j]['id_nf']."' LIMIT 1 ";
        bancos::sql($sql);
        atualizar_vide_notas($campos[$j]['id_nf'], $id_cliente, $id_empresa_nf, $id_funcionario, $data_saida_entrada, $hora_saida_entrada, $data_envio, $hora_envio, $status, $tipo_despacho, $numero_remessa);
    }
}

function verificar_vide_notas($id_nf, $id_cliente, $id_empresa_nf, $numero_nf_ac = '') {
//Aqui vai acumulando todos os N�ms. de Nota
    $numero_nf_ac.= faturamentos::buscar_numero_nf($id_nf, 'S').' <- ';

    $sql = "SELECT `id_nf` 
            FROM `nfs` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `id_nf_vide_nota` = '$id_nf' ORDER BY numero_nf ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($j = 0; $j < $linhas; $j++) $numero_nf_ac = verificar_vide_notas($campos[$j]['id_nf'], $id_cliente, $id_empresa_nf, $numero_nf_ac);
    return $numero_nf_ac;
}

if(!empty($_POST['hdd_atualizar_dados_gerais'])) {
/*N�o existe nenhum 1 Or�amento com o Prazo M�dio Irregular, sendo assim eu posso
alterar normal os dados de cabe�alho*/
/********************************************************************************************/
/*********************************Cliente com Cr�dito C ou D*********************************/
/********************************************************************************************/
    $retorno_analise_credito = faturamentos::analise_credito_cliente($_POST['id_cliente'], $_POST['id_nf'], $_POST['cmb_status']);//Analisa p/ V c ele tem d�bito e pode comprar devido seu limite ou cr�dito
//Se o Cliente estiver com Cr�dito = C ou D, ent�o n�o posso Faturar a NF ...
    if($_POST['cmb_status'] == 1 && ($retorno_analise_credito['credito'] == 'C' || $retorno_analise_credito['credito'] == 'D')) {
?>
    <Script Language = 'JavaScript'>
        alert('ESTA NF N�O PODE SER FATURADA !!!\nESTE CLIENTE EST� COM CR�DITO <?=$retorno_analise_credito['credito'];?> ! ')
        window.close()
    </Script>
<?
        exit;
    }
/********************************************************************************************/
//Significa que o est� em ineglig�ncia no que se refere ao seu Cr�dito ...
    if($retorno_analise_credito['credito_comprometido'] > $retorno_analise_credito['tolerancia_cliente']) {
        if($retorno_analise_credito['credito'] == 'B') {
//Aqui eu busco o Peso de todos os Itens de PA que j� foram faturados ...
            $sql = "SELECT SUM(nfsi.`qtde` * pa.`peso_unitario`) AS peso_total_faturado 
                    FROM `nfs` 
                    INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
                    WHERE nfs.`id_nf` = '$_POST[id_nf]' ";
            $campos_peso_total_faturado = bancos::sql($sql);
            $peso_total_faturado 		= $campos_peso_total_faturado[0]['peso_total_faturado'];
//Busca de alguns dados p/ adequar na Fun��o ...
            $sql = "SELECT c.`id_pais`, nfs.`id_empresa`, nfs.`data_emissao`, nfs.`valor_dolar_dia` 
                    FROM `nfs` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                    WHERE nfs.`id_nf` = '$_POST[id_nf]' LIMIT 1 ";
            $campos_nf              = bancos::sql($sql);
            $id_pais                = $campos_nf[0]['id_pais'];
//Aqui verifica o Tipo de Nota, var surti efeito l� embaixo ...
            $nota_sgd               = ($campos_nf[0]['id_empresa'] == 1 || $campos_nf[0]['id_empresa'] == 2) ? 'N' : 'S';
            $data_emissao           = $campos_nf[0]['data_emissao'];
            $valor_dolar_nota       = $campos_nf[0]['valor_dolar_dia'];
//Fun��o para o c�lculo do Valor Total da NF - tem q ter todos os calculos da NF, pois o valor cont�m frete+impostos e etc.
            $calculo_total_impostos = calculos::calculo_impostos(0, $_POST[id_nf], 'NF');
            $valor_total_nota       = round($calculo_total_impostos['valor_total_nota'], 2);//Usada em JavaScript ...
/****************************************************************************/
?>
            <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
            <Script Language = 'JavaScript'>
                window.close()
                nova_janela('../../financeiro/cadastro/credito_cliente/enviar_email_solic_credito.php?id_cliente=<?=$id_cliente;?>&valor_total_itens_faturar=<?=$valor_total_nota;?>&peso_total_faturar=<?=$peso_total_faturado;?>', 'ENVIAR_EMAIL_SOLIC_CRED', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')
            </Script>
<?
        }
    }
/*********************************Controle com os Checkbox*********************************/
    $incompatibilidade_empresas = 0;
    
    //Fa�o a busco de v�rios campos e estes ser�o utilizados de acordo com algumas regras no decorrer deste Script ...
    $sql = "SELECT IF(c.`nomefantasia` = '', c.`razaosocial`, CONCAT(c.`nomefantasia`, ' (', c.`razaosocial`, ')')) AS cliente, c.`email`, c.`email_nfe`, c.`id_pais`, 
            c.`tipo_faturamento`, IF(e.`id_empresa` = '4', 'GRUPO ALBAFER', e.`nomefantasia`) AS empresa, nnn.`id_empresa`, nnn.`numero_nf`, 
            DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS data_emissao, nfs.`suframa`, nfs.`status`, nfs.`numero_remessa`, nfs.`devolucao_faturada` 
            FROM `nfs` 
            INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            INNER JOIN `empresas` e ON e.`id_empresa` = nfs.`id_empresa` 
            WHERE nfs.`id_nf` = '$id_nf' ";
    $campos = bancos::sql($sql);
    
    //Se o Tipo de Faturamento do Cliente for Albafer ou Tool Master ...
    if($campos[0]['tipo_faturamento'] == 1 || $campos[0]['tipo_faturamento'] == 2) {
        //Se o cadastro do Cliente = 'Albafer' e a Empresa da NF = 'Tool Master' ...
        if($campos[0]['tipo_faturamento'] == 1 && $campos[0]['id_empresa'] == 2) $incompatibilidade_empresas = 1;
        //Se o cadastro do Cliente = 'Tool Master' e a Empresa da NF = 'Albafer' ...
        if($campos[0]['tipo_faturamento'] == 2 && $campos[0]['id_empresa'] == 1) $incompatibilidade_empresas = 1;
    }

    if($incompatibilidade_empresas == 0) {//Est� ok o Tipo de Fat. do Cliente com a Empresa NF
        /*************************************************************************************************************/
        /******************Enviando Email p/ o Cliente referente ao C�digo de Rastreamento do Correio*****************/
        /*************************************************************************************************************/
        if($_POST['cmb_tipo_despacho'] == 5 && !empty($_POST['txt_numero_remessa'])) {//Correio e Preenchido o N.� de Remessa ...
            if($campos[0]['numero_remessa'] != $_POST['txt_numero_remessa']) {//Toda vez que for feita alguma altera��o nesse campo, enviamos ai um e-mail p/ o Cliente ...
                $mensagem = '<br/>Ol� <b>'.$campos[0]['cliente'].'</b> !';
                $mensagem.= '<p/>Segue abaixo seu c�digo de Rastreamento referente Nota Fiscal N.� <b>'.faturamentos::buscar_numero_nf($_POST['id_nf'], 'S').'</b>, ';
                $mensagem.= 'data de Emiss�o: <b>'.$campos[0]['data_emissao'].'</b>, ';
                $mensagem.= 'da empresa <b>'.$campos[0]['empresa'].'</b>;';
                $mensagem.= '<p/>C�digo de Rastreamento N.� <b>'.$_POST['txt_numero_remessa'].'</b>;';
                $mensagem.= '<br/><br/><br/>Atenciosamente.';
                
                $assunto = 'C�digo de Rastreamento Nota Fiscal '.faturamentos::buscar_numero_nf($_POST['id_nf'], 'S').' '.$campos[0]['empresa'];
                
                comunicacao::email('ERP - GRUPO ALBAFER', $campos[0]['email_nfe'], $campos[0]['email'], $assunto, $mensagem);
            }
        }
        /*************************************************************************************************************/
        
        //Atualizo as Vide-Notas dessa nota Principal ...
	atualizar_vide_notas($_POST[id_nf], $_POST[id_cliente], $campos_nf[0]['id_empresa'], $_SESSION['id_funcionario'], $_POST['txt_data_saida_entrada'], $_POST['txt_hora_saida_entrada'], $_POST['txt_data_envio'], $_POST['txt_hora_envio'], $_POST['cmb_status'], $_POST['cmb_tipo_despacho'], $_POST['txt_numero_remessa']);
        
        /*********************************Controle com os Checkbox********************************/
        $devolucao_faturada = ($_POST['chkt_devolucao_faturada'] == 'S') ?  'S' : 'N';
        $data_saida_entrada = data::datatodate($_POST['txt_data_saida_entrada'], '-');
        $data_envio         = data::datatodate($_POST['txt_data_envio'], '-');
        
        $sql = "UPDATE `nfs` SET `id_funcionario` = '$_SESSION[id_funcionario]', `gnre` = '$_POST[txt_gnre]', `chave_acesso` = '$_POST[txt_chave_acesso]', `data_saida_entrada` = '$data_saida_entrada', `hora_saida_entrada` = '$_POST[txt_hora_saida_entrada]', `data_envio` = '$data_envio', `hora_envio` = '$_POST[txt_hora_envio]', `data_sys` = '".date('Y-m-d H:i:s')."', `status` = '$_POST[cmb_status]', `tipo_despacho` = '$_POST[cmb_tipo_despacho]', `numero_remessa` = '$_POST[txt_numero_remessa]', `devolucao_faturada` = '$devolucao_faturada' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);
        
        //Antes a "NF de Devolu��o" n�o estava faturada, mas o usu�rio resolveu marcar que esta agora foi ...
        if($campos[0]['devolucao_faturada'] == 'N' && $devolucao_faturada == 'S') {
?>
        <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
        <Script Language = 'JavaScript'>
            var txt_observacao = 'Esta NF de Devolucao / Entrada acabou de ser liberada no sistema. Como foi emitida uma NF de Entrada, caso os itens desta NF necessitem ser refaturados, precisa-se reemitir Pedido de Venda destes itens.'
            nova_janela('../../classes/follow_ups/incluir.php?identificacao=<?=$_POST[id_nf];?>&txt_observacao='+txt_observacao+'&origem=5', 'INCLUIR_SALVAR', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
        </Script>
<?
        }
        //O status da NF estava como "Em Aberto" e o usu�rio modificou para "Liberada p/ Faturar" ...
        if($campos[0]['status'] == 0 && $_POST['cmb_status'] == 1) {
            //Aqui eu busco o representante da Nota Fiscal ...
            $sql = "SELECT DISTINCT(`id_representante`) AS id_representante 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = '$_POST[id_nf]' ";
            $campos_representantes = bancos::sql($sql);
            $linhas_representantes = count($campos_representantes);
            for($i = 0; $i < $linhas_representantes; $i++) {
                //Se o Representante for Direto n�o precisa pq o e-mail j� vai para a Dona Sandra e para o Wilson ...
                if($campos_representantes[$i]['id_representante'] != 1) {
                    //Aqui eu verifico se o Representante � Funcion�rio ...
                    $sql = "SELECT f.`email_externo` 
                            FROM `representantes_vs_funcionarios` rf 
                            INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
                            WHERE rf.`id_representante` = '".$campos_representantes[$i]['id_representante']."' LIMIT 1 ";
                    $campos_funcionario = bancos::sql($sql);
                    if(count($campos_funcionario) == 1) {//Se for funcion�rio ...
                        $vendedores.= $campos_funcionario[0]['email_externo'].', ';
                    }else {//Significa que � aut�nomo, sendo assim eu busco o Supervisor do Representante p/ passar e-mail ...
                        $sql = "SELECT r.`id_representante`, r.`nome_fantasia` 
                                FROM `representantes_vs_supervisores` rs 
                                INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                                WHERE rs.`id_representante` = '".$campos_representantes[$i]['id_representante']."' LIMIT 1 ";
                        $campos_supervisores = bancos::sql($sql);
                        //Tratamento com alguns e-mails ...
                        if($campos_supervisores[0]['id_representante'] == 42) {//Arnaldo Nogueira ...
                            $vendedores.= 'nogueira@grupoalbafer.com.br'.', ';
                        }else if($campos_supervisores[0]['id_representante'] == 69) {//Carlos Junior ...
                            $vendedores.= 'carlos.junior@grupoalbafer.com.br'.', ';
                        }else if($campos_supervisores[0]['id_representante'] == 6) {//Edson Gon�alves ...
                            $vendedores.= 'edson.goncalves@grupoalbafer.com.br'.', ';
                        }else if($campos_supervisores[0]['id_representante'] == 93) {//Izael Pedreira ...
                            $vendedores.= 'noronha@grupoalbafer.com.br'.', ';
                        }else if($campos_supervisores[0]['id_representante'] == 137) {//Wilson Roberto "Diretor" ...
                            $vendedores.= 'wilson@grupoalbafer.com.br'.', ';
                        }else {
                            $vendedores.= strtolower($campos_supervisores[0]['nome_fantasia']).'@grupoalbafer.com.br, ';
                        }
                    }
                }
            }
            $vendedores = substr($vendedores, 0, strlen($vendedores) - 2);
            
            $assunto    = 'A NF N� '.$campos[0]['numero_nf'].', do cliente '.$campos[0]['cliente'].', da empresa '.$campos[0]['empresa'].' est� pronta para emiss�o.';
            $texto      = 'Favor verificar URGENTE se existe alguma diverg�ncia, antes de pagarmos a Guia de ST (principalmente), transportadora, etc ...<p/>';
            
            /**Busca do IP Externo que est� cadastrado em alguma Empresa aqui do Sistema ...**/
            $sql = "SELECT `ip_externo` 
                    FROM `empresas` 
                    WHERE `ip_externo` <> '' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            /*Se encontrar um IP Externo cadastrado, o conte�do do e-mail apontar� p/ esse IP "que � a prefer�ncia", 
            do contr�rio o IP ser� da onde o usu�rio est� acessando o ERP $_SERVER['HTTP_HOST'] ...*/
            $ip_externo     = (count($campos_empresa) == 1) ? $campos_empresa[0]['ip_externo'] : $_SERVER['HTTP_HOST'];
            
            $emails         = 'rivaldo@grupoalbafer.com.br; roberto@grupoalbafer.com.br; wilson@grupoalbafer.com.br; wilson.nishimura@grupoalbafer.com.br; ';
            
            //E-mail p/ a Dona Sandra ficar � par do que est� acontecendo no Faturamento, se a NF realmente for uma NF = Albafer ou Tool Master ...
            if($campos[0]['id_empresa'] == 1 || $campos[0]['id_empresa'] == 2) $emails.= 'sandra@grupoalbafer.com.br';

            /************Compondo a Mensagem para Enviar por e-mail************/
            $texto.=    "Acesse a NF pelo link: Interno <a href='http://192.168.1.253/erp/albafer/modulo/faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=$_POST[id_nf]&pop_up=1'>".$campos[0]['numero_nf']."</a> / 
                        Externo <a href='http://".$ip_externo."/erp/albafer/modulo/faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=$_POST[id_nf]&pop_up=1'>".$campos[0]['numero_nf']." Ext</a> ";
            comunicacao::email('ERP - GRUPO ALBAFER', $vendedores, $emails, $assunto, $texto);
        }
        
        /*************************************************************************************************************/
        /****************Enviando Email p/ informar aos Diretores p/ terem conhecimento desta Devolu��o***************/
        /*************************************************************************************************************/
        if($devolucao_faturada == 'S') {//S� ir� enviar esse e-mail quando o usu�rio Faturar essa NF pelo Checkbox do Cabe�alho ...
            $texto  = 'Foi inclusa a NF de Devolu��o N.� <b>'.$campos[0]['numero_nf'].'</b> c/ Data de Emiss�o <b>'.$campos[0]['data_emissao'].'</b> p/ o Cliente <b>'.$campos[0]['razaosocial'].'</b> na Empresa <b>'.$campos[0]['nomefantasia'].'</b>.';
            comunicacao::email('ERP - GRUPO ALBAFER', 'diretoria@grupoalbafer.com.br', '', 'Inclus�o de NF de Devolu��o', $texto);
        }
        /*************************************************************************************************************/
        
        //Se foi preenchido um N.� de GNRE aqui no Cabe�alho de NF, ent�o ...
        if(!empty($_POST['txt_gnre'])) {
            //Verifico se a guia � ser Paga j� foi gerada pelo Financeiro ...
            $sql = "SELECT `id_conta_apagar` 
                    FROM `contas_apagares` 
                    WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
            $campos_contas_apagar = bancos::sql($sql);
            if(count($campos_contas_apagar) == 0) {//Ainda n�o ...
?>
    <Script Language = 'JavaScript'>
                var resposta = confirm('DESEJA GERAR UMA "CONTA � PAGAR" P/ ESTA NOTA FISCAL ?')
                if(resposta == true) {//O usu�rio deseja ...
                    opener.parent.location = opener.parent.location.href
                    window.location = 'gerar_conta_a_pagar.php?id_nf=<?=$_POST[id_nf];?>'
                }else {//N�o deseja ...
                    alert('DADO(S) GERAL(IS) ATUALIZADO(S) COM SUCESSO !')
                    opener.parent.location = opener.parent.location.href
                    window.close()
                }
    </Script>
<?
            }else {//Sim ...
?>
    <Script Language = 'JavaScript'>
        alert('DADO(S) GERAL(IS) ATUALIZADO(S) COM SUCESSO !')
        opener.parent.location = opener.parent.location.href
        window.close()
    </Script>
<?
            }
        }else {
?>
    <Script Language = 'JavaScript'>
        alert('DADO(S) GERAL(IS) ATUALIZADO(S) COM SUCESSO !')
        opener.parent.location = opener.parent.location.href
        window.close()
    </Script>
<?
        }
    }else {//Est� incompat�vel o Tipo de Fat. do Cliente com a Empresa NF
?>
    <Script Language = 'JavaScript'>
        alert('INCOMPAT�VEL EMPRESA DO CABE�ALHO x EMPRESA DO CADASTRO DO CLIENTE ! EXCLUA OS ITENS DA NF OU ALTERE O CADASTRO DO CLIENTE !!!')
        window.close()
    </Script>
<?
    }
}

//Aqui eu trago dados da "id_nf" passado por par�metro ...
$sql = "SELECT c.`id_uf`, nfs.`id_cliente`, nfs.`id_empresa`, nfs.`id_transportadora`, nfs.`id_nf_num_nota`, nfs.`id_funcionario_confirm_doc`, 
        nfs.`frete_transporte`, nfs.`valor_frete`, nfs.`data_emissao`, nfs.`gnre`, nfs.`chave_acesso`, nfs.`data_saida_entrada`, 
        TIME_FORMAT(nfs.`hora_saida_entrada`, '%H:%i') AS hora_saida_entrada, nfs.`data_envio`, 
        TIME_FORMAT(nfs.`hora_envio`, '%H:%i') AS hora_envio, nfs.`trading`, nfs.`trading_confirmacao`, nfs.`suframa`, 
        nfs.`status`, nfs.`status_comissao_pg`, nfs.`tipo_despacho`, nfs.`numero_remessa`, nfs.`devolucao_faturada`, t.`nome` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` 
        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_empresa_nf  = $campos[0]['id_empresa'];

if($campos[0]['data_saida_entrada'] != '0000-00-00') $data_saida_entrada = data::datetodata($campos[0]['data_saida_entrada'], '/');
$hora_saida_entrada = ($campos[0]['hora_saida_entrada'] != '00:00') ? $campos[0]['hora_saida_entrada'] : '';
if($campos[0]['data_envio'] != '0000-00-00') $data_envio = data::datetodata($campos[0]['data_envio'], '/');
$hora_envio = ($campos[0]['hora_envio'] != '00:00') ? $campos[0]['hora_envio'] : '';

/*Aqui verifica se a Nota Fiscal tem pelo menos 1 item cadastrado, se tiver n�o pode alterar 
a Empresa e o Tipo de Nota*/
$sql = "SELECT `id_nfs_item` 
        FROM `nfs_itens` 
        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
$campos_qtde_itens  = bancos::sql($sql);
$qtde_itens_nf      = count($campos_qtde_itens);

if($acao == 'L') {//Significa que essa Tela foi aberta somente p/ Modo Leitura ...
    $disabled       = 'disabled';
    $class          = 'textdisabled';
    $class_combo    = 'textdisabled';
    $class_botao    = 'textdisabled';
    $width          = '100%';
}else {//Significa que essa Tela foi aberta como Modo Grava��o ...
    $disabled       = '';
    $class          = 'caixadetexto';
    $class_combo    = 'combo';
    $class_botao    = 'botao';
    $width          = '95%';
}

/*Nessa �ltima Inst�ncia -> Menu Fat. / Emp. / Despachadas, fa�o uma seguran�a especial p/ os campos 
"GNRE" e "Chave de Acesso" p/ que os usu�rios n�o fiquem digitando ...*/
if($opcao == 3) {
    $class_opcao        = 'textdisabled';
    $disabled_opcao     = 'disabled';
}else {//Outros Menus ...
    $class_opcao        = $class;
    $disabled_opcao     = $disabled;
}

//Observa��o: No ERP as Notas Fiscais come�aram a funcionar a partir do dia 12 de Setembro de 2008 ...
$calculo_total_impostos = calculos::calculo_impostos(0, $id_nf, 'NF');

//Regra p/ obrigar o "usu�rio" a Preencher a Guia de Recolhimento "GNRE", somente se existir conv�nio entre UFs ...
$preencher_guia_recolhimento = 'N';//Valor Inicial ...

if($calculo_total_impostos['valor_icms_st'] > 0) {
    //Verifico se no Estado do Cliente existe algum Conv�nio ...
    $sql = "SELECT convenio 
            FROM `ufs` 
            WHERE `id_uf` = '".$campos[0]['id_uf']."' LIMIT 1 ";
    $campos_convenio = bancos::sql($sql);
    if($campos_convenio[0]['convenio'] != '') {//Existe Conv�nio ...
        /*//Verifico se existe uma Conta � Pagar dessa NF l� no Financeiro ...
        $sql = "SELECT id_conta_apagar 
                FROM `contas_apagares` 
                WHERE `id_nf` = '$id_nf' LIMIT 1 ";
        $campos_contas_apagar = bancos::sql($sql);
        if(count($campos_contas_apagar) == 1) {*/
            $preencher_guia_recolhimento = 'S';//� obrigat�rio preenchermos a Guia de Recolhimento de GNRE ...
        /*}else {
            $preencher_guia_recolhimento = 'N';//N�o � obrigado preenchermos a Guia de Recolhimento de GNRE ...
        }*/
    }
}

/************************************************************************************/
/************************Controle p/ ajudar o Faturista******************************/
/************************************************************************************/
/*Data de Programa��o seguindo o Padr�o que � a Data de Hoje + "1 dia", para que o faturista 
n�o esque�a de dar dias a mais no Faturamento ...*/
$data_atual_mais_um = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), 1), '-');

/*Verifico se foi faturado pelo menos 1 item nessa Nota Fiscal de maneira antecipada p/ dar um aviso 
como lembrete na inten��o de ajudar o faturista a dar dias a mais ...*/
$sql = "SELECT pv.`id_pedido_venda` 
        FROM `nfs_itens` nfsi 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`faturar_em` > '$data_atual_mais_um' 
        WHERE nfsi.`id_nf` = '$id_nf' LIMIT 1 ";
$campos_nfs_item                    = bancos::sql($sql);
$exibir_mensagem_datas_vencimento   = (count($campos_nfs_item) == 1) ? 'S' : 'N';
/************************************************************************************/

/***************************Controles com a Data de Emiss�o**************************/
//Essa fun��o de Talon�rio que controla tudo referente � parte de NF(s) ...
$talonario                  = faturamentos::buscar_numero_ant_post_talonario($campos[0]['id_nf_num_nota']);
$data_emissao_anterior      = $talonario['data_emissao_anterior'];
$data_emissao_posterior     = $talonario['data_emissao_posterior'];

//Busco o per�odo mais recente de Comiss�o ...
$sql = "SELECT DATE_FORMAT(`data`, '%d/%m/%Y') AS data_formatada 
        FROM `vales_datas` 
        WHERE `qtde_dias_uteis_mes` > '0' ORDER BY `data` DESC LIMIT 1 ";
$campos_vale_data       = bancos::sql($sql);
$vetor_depto_pessoal    = depto_pessoal::periodo_folha($campos_vale_data[0]['data_formatada']);

/************************************************************************************/
?>
<html>
<head>
<title>.:: DADOS GERAIS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var opcao                       = eval('<?=$opcao;?>')
    var preencher_guia_recolhimento = '<?=$preencher_guia_recolhimento;?>'
    var id_empresa_nota             = eval('<?=$id_empresa_nf;?>')
    var id_transportadora           = '<?=$campos[0]['id_transportadora'];?>'
    var id_nf_num_nota              = '<?=$campos[0]['id_nf_num_nota'];?>'
    var frete_transporte            = '<?=$campos[0]['frete_transporte'];?>'
    var valor_frete                 = eval(strtofloat('<?=$campos[0]['valor_frete'];?>'))
    var numero_nf                   = '<?=faturamentos::buscar_numero_nf($id_nf, 'S');?>'
    var status_comissao_pg          = '<?=$campos[0]['status_comissao_pg'];?>'
    
/*Infelizmente esse c�digo "Valor do Frete" est� dobrado no outro arquivo "frete.php" outro Frame porque l� 
� feito todo um Controle de forma din�mica de acordo com o que o usu�rio vai preenchendo, j� aqui eu s� leio 
os campos que est�o guardados na Tabela de NF mesmo ...*/
/****************************************Valor do Frete****************************************/
//1) Se o Frete = 'REMETENTE' e o campo Valor do Frete > 0 ...
    if(frete_transporte == 'C' && valor_frete > 0) {
        alert('QUANDO O FRETE � POR CONTA DO REMETENTE, O VALOR DO FRETE TEM DE SER ZERADO !!!')
        return false
    }
//2) ...
    var transportadora_para_confiscar = 0
/*Se a Transportadora = 797 - Sedex, 1050 - Correio Encomenda P.A.C., 1092 - Sedex 10, 1093 - Motoboy ou 1265 - Tam Linhas A�reas
e Valor do Frete = 0, ent�o for�o a calcular ...*/
    var vetor_transportadoras       = ['797', '1050', '1092', '1093', '1265']
    if(vetor_transportadoras.indexOf(id_transportadora) != -1) transportadora_para_confiscar = 1

/*Se o Valor de Frete = Zero, for uma das 4 Transportadoras acima e o Frete Transporte = 'DESTINAT�RIO', 
for�o esse campo p/ preenchimento de Valor do Frete ...*/
    if(valor_frete == 0 && transportadora_para_confiscar == 1 && frete_transporte == 'F') {
        alert('VALOR DO FRETE INV�LIDO !!! CALCULE O VALOR DO FRETE PARA A TRANSPORTADORA "<?=$campos[0]['nome'];?>" !')
        return false
    }
/**********************************************************************************************/
//Status da Nota Fiscal ...
    if(document.form.cmb_status.value == '') {
        alert('SELECIONE O STATUS DA NOTA FISCAL !')
        document.form.cmb_status.focus()
        return false
    }
    
    /***************************Controles com a Data de Emiss�o**************************/
    /*Somente at� a op��o de Faturada que devo fazer essa verifica��o, mas somente se: 
    
    1) A NF que esta sendo emitida for pelas empresas "Albafer 1" ou "Tool Master 2" porque nesse caso a NF tem v�nculo c/ o Sefaz ...
    2) Independente da Empresa, se a Comiss�o j� foi paga ...*/
    if((document.form.cmb_status.value <= 2 && id_empresa_nota != 4) || status_comissao_pg == 'S') {
        var data_emissao                    = opener.parent.destinatario_remetente_fatura.document.form.txt_data_emissao.value
        data_emissao                        = data_emissao.substr(6, 4) + data_emissao.substr(3, 2) + data_emissao.substr(0, 2)
        var dia_da_data_emissao             = data_emissao.substr(6, 2)

        var data_emissao_anterior           = '<?=str_replace('-', '', $talonario['data_emissao_anterior']);?>'
        var data_emissao_posterior          = '<?=str_replace('-', '', $talonario['data_emissao_posterior']);?>'

        if(data_emissao_anterior != '' && data_emissao_posterior != '') {//Existe uma Data de Emiss�o Anterior e Posterior ...
            if(data_emissao < data_emissao_anterior || data_emissao > data_emissao_posterior) {
                alert('DATA DE EMISS�O INV�LIDA !\n\nDATA DE EMISS�O N�O PODE SER MENOR DO QUE A DATA DE EMISS�O ANTERIOR E N�O PODE SER MAIOR DO QUE A DATA DE EMISS�O POSTERIOR !!!')
                return false
            }
        }else if(data_emissao_anterior != '' && data_emissao_posterior == '') {//S� existe uma Data de Emiss�o Anterior ...
            if(numero_nf != '') {//Significa que j� foi escolhido algum N.� de NF para esta NF que est� sendo emitida ...
                if(data_emissao < data_emissao_anterior) {
                    alert('DATA DE EMISS�O INV�LIDA !\n\nDATA DE EMISS�O N�O PODE SER MENOR DO QUE A DATA DE EMISS�O ANTERIOR !!!')
                    return false
                }
            }
        }

        var data_atual              = '<?=date('Ymd')?>'
        var data_final_folha        = '<?=$vetor_depto_pessoal['data_final_folha'];?>'
        data_final_folha            = data_final_folha.substr(6, 4) + data_final_folha.substr(3, 2) + data_final_folha.substr(0, 2)
        /*Controle pela Comiss�o ...

        A data de Emiss�o n�o pode ser menor do que Data Final da Folha, mas desde que a Data Atual n�o 
        seja superior a Data Final da Folha ...

        Posso emitir Notas Fiscais at� o dia 25 de cada m�s que � o Per�odo de fechamento do Faturamento e 
        acontece muito de faturarem com Data Retr�grada ...*/
        if(data_emissao <= data_final_folha && data_atual > data_final_folha && dia_da_data_emissao > 25) {
            alert('DATA DE EMISS�O INV�LIDA !\n\nDATA DE EMISS�O TEM QUE SER MAIOR QUE O �LTIMO PER�ODO DE COMISS�O J� PAGA !!!')
            return false
        }
        
        if(document.form.cmb_status.value == 1) {//Usu�rio selecionou a op��o Liberada p/ Faturar, ent�o fa�o a verifica��o abaixo ...
            if(numero_nf == '') {//Nesse est�gio � mais do que necess�rio se escolher um N.� de NF ...
                alert('SELECIONE O N.� DA NOTA FISCAL DE SA�DA !')
                return false
            }
        }
    }
    /************************************************************************************/
    
    if(opcao == 1) {//Significa que veio do Menu Abertas / Liberadas ...
        if(document.form.cmb_status.value == 1) {//Usu�rio selecionou a op��o Liberada p/ Faturar, ent�o fa�o a verifica��o abaixo ...
            var exibir_mensagem_datas_vencimento    = '<?=$exibir_mensagem_datas_vencimento;?>'
            var data_atual_mais_um                  = '<?=data::datetodata($data_atual_mais_um, '/');?>'

            if(exibir_mensagem_datas_vencimento == 'S') {
                var resposta = confirm('EXISTE(M) ITEM(NS) COM DATA PROGRAMADA MAIOR QUE '+data_atual_mais_um+' !!!\n\nDESEJA ALTERAR AS DATAS DE VENCIMENTO ?')
                if(resposta == true) {
                    nova_janela('destinatario_remetente_fatura.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>&acao=G', 'DESTINATARIO_REMETENTE_FATURA', '', '', '', '', '320', '750', 'c', 'c', '', '', 's', 's', '', '', '')
                }
            }
        }
    }
    
    if(document.form.cmb_status.value >= 2) {//Se a Nota Fiscal for para uma situa��o "Faturada", ent�o ...
//Se essa vari�vel "preencher_guia_recolhimento" = 'S', sou obrigado a preencher ...
        if(preencher_guia_recolhimento == 'S') {
            if(document.form.txt_gnre.value == '') {
                alert('DIGITE O GNRE !')
                document.form.txt_gnre.focus()
                return false
            }
//GNRE INCOMPLETO ...
            if(!texto('form', 'txt_gnre', '6', '0123456789.-', 'GNRE', '2')) {
                return false
            }
            gnre = eval(document.form.txt_gnre.value)
            if(gnre == 0) {
                alert('GNRE INV�LIDO !')
                document.form.txt_gnre.focus()
                return false
            }
        }
    }
//Se a Nota Fiscal for para uma situa��o de Faturada, ent�o ...
    if(document.form.cmb_status.value >= 2) {//Se a NF estiver Faturada ...
        if(id_empresa_nota != 4) {//S� existir� Chave de Acesso p/ Alba ou Tool ...
            /*Quando existir esse objeto "Chave de Acesso" e quando for utilizado um n�mero 
            de nosso Talon�rio p/ Nota Fiscal, ent�o somos obrigados a preenchermos esse campo ...*/
            if(typeof(document.form.txt_chave_acesso) == 'object' && id_nf_num_nota > 0) {
                if(!texto('form', 'txt_chave_acesso', '44', '0123456789 ', 'CHAVE DE ACESSO', '1')) {
                    return false
                }
            }
        }
    }
/*Esse campo "Data de Sa�da / Entrada" s� pode ser preenchido quando a NF estiver com status 
� partir de Empacotada ...*/
    if(document.form.cmb_status.value >= 3) {
        //Data de Sa�da / Entrada ...
        if(document.form.txt_data_saida_entrada.value != '') {//N�o � obrigat�rio, mais � aceit�vel ...
            if(!data('form', 'txt_data_saida_entrada', '4000', 'SA�DA / ENTRADA')) {
                return false
            }
            //Nunca a "Data de Sa�da / Entrada" pode ser menor que a Data de Emiss�o ...
            var data_emissao        = eval('<?=str_replace('-', '', $campos[0]['data_emissao']);?>')
            var data_saida_entrada  = document.form.txt_data_saida_entrada.value
            data_saida_entrada      = data_saida_entrada.substr(6, 4) + data_saida_entrada.substr(3, 2) + data_saida_entrada.substr(0, 2)
            data_saida_entrada      = eval(data_saida_entrada)
    
            if(data_saida_entrada < data_emissao) {
                alert('DATA DE SA�DA / ENTRADA INV�LIDA !!!\n\nDATA DE "SA�DA / ENTRADA" MENOR DO QUE A DATA DE EMISS�O !')
                document.form.txt_data_saida_entrada.focus()
                document.form.txt_data_saida_entrada.select()
                return false
            }
        }
        //Hora de Sa�da / Entrada ...
        if(document.form.txt_hora_saida_entrada.value != '') {
            if(!texto('form', 'txt_hora_saida_entrada', '1', '1234567890:', 'HORA DE SA�DA / ENTRADA', '1')) {
                return false
            }
            /****Aqui eu verifico se o Usu�rio n�o digitou valores incoerentes na Hora e no Minuto da Sa�da / Entrada ...****/
            var vetor_qtde_horas_saida_entrada  = document.form.txt_hora_saida_entrada.value.split(':')
            var horas_saida_entrada             = vetor_qtde_horas_saida_entrada[0]
            var minutos_saida_entrada           = vetor_qtde_horas_saida_entrada[1]
            if(horas_saida_entrada > 23) {
                alert('QTDE DE HORA(S) DE SA�DA / ENTRADA INV�LIDA !!!\n\nDIGITE HORA(S) DE SA�DA / ENTRADA CORRETA AT� 23 !')
                document.form.txt_hora_saida_entrada.focus()
                document.form.txt_hora_saida_entrada.select()
                return false
            }
            //Aqui eu verifico se os Minutos digitados pelo usu�rio est�o Inv�lidos ...
            if(minutos_saida_entrada > 59) {
                alert('QTDE DE MINUTO(S) DE SA�DA / ENTRADA INV�LIDO !!!\n\nDIGITE MINUTO(S) DE SA�DA / ENTRADA CORRETO(S) AT� 59 !')
                document.form.txt_hora_saida_entrada.focus()
                document.form.txt_hora_saida_entrada.select()
                return false
            }
        }
        //Data de Envio ...
        if(document.form.txt_data_envio.value != '') {//N�o � obrigat�rio, mais � aceit�vel ...
            if(!data('form', 'txt_data_envio', '4000', 'ENVIO')) {
                return false
            }
            //Nunca a "Data de Envio" pode ser menor que a Data de Sa�da / Entrada ...
            var data_saida_entrada  = document.form.txt_data_saida_entrada.value
            var data_envio          = document.form.txt_data_envio.value
            data_saida_entrada      = data_saida_entrada.substr(6, 4) + data_saida_entrada.substr(3, 2) + data_saida_entrada.substr(0, 2)
            data_envio              = data_envio.substr(6, 4) + data_envio.substr(3, 2) + data_envio.substr(0, 2)
            data_saida_entrada      = eval(data_saida_entrada)
            data_envio              = eval(data_envio)
    
            if(data_envio < data_saida_entrada) {
                alert('DATA DE ENVIO INV�LIDA !!!\n\nDATA DE "ENVIO" MENOR DO QUE A DATA DE SA�DA / ENTRADA !')
                document.form.txt_data_envio.focus()
                document.form.txt_data_envio.select()
                return false
            }
        }
        //Hora de Envio ...
        if(document.form.txt_hora_envio.value != '') {
            if(!texto('form', 'txt_hora_envio', '1', '1234567890:', 'HORA DE ENVIO', '1')) {
                return false
            }
            /****Aqui eu verifico se o Usu�rio n�o digitou valores incoerentes na Hora e no Minuto da Sa�da / Entrada ...****/
            var vetor_qtde_horas_envio  = document.form.txt_hora_envio.value.split(':')
            var horas_envio             = vetor_qtde_horas_envio[0]
            var minutos_envio           = vetor_qtde_horas_envio[1]
            if(horas_envio > 23) {
                alert('QTDE DE HORA(S) DE ENVIO INV�LIDA !!!\n\nDIGITE HORA(S) DE ENVIO CORRETA AT� 23 !')
                document.form.txt_hora_envio.focus()
                document.form.txt_hora_envio.select()
                return false
            }
            //Aqui eu verifico se os Minutos digitados pelo usu�rio est�o Inv�lidos ...
            if(minutos_envio > 59) {
                alert('QTDE DE MINUTO(S) DE ENVIO INV�LIDO !!!\n\nDIGITE MINUTO(S) DE ENVIO CORRETO(S) AT� 59 !')
                document.form.txt_hora_envio.focus()
                document.form.txt_hora_envio.select()
                return false
            }
        }
    }else {//Outros status como "Faturada, Liberada p/ Faturar", ...
        //Data de Sa�da / Entrada ...
        if(document.form.txt_data_saida_entrada.value != '') {
            alert('DATA DE SA�DA / ENTRADA INV�LIDA !!!\n\nESSA N�O PODE SER PREENCHIDA NESSE STATUS !')
            document.form.txt_data_saida_entrada.focus()
            document.form.txt_data_saida_entrada.select()
            return false
        }
        //Hora de Sa�da / Entrada ...
        if(document.form.txt_hora_saida_entrada.value != '') {
            alert('HORA DE SA�DA / ENTRADA INV�LIDA !!!\n\nESSA N�O PODE SER PREENCHIDA NESSE STATUS !')
            document.form.txt_hora_saida_entrada.focus()
            document.form.txt_hora_saida_entrada.select()
            return false
        }
        //Data de Envio ...
        if(document.form.txt_data_envio.value != '') {
            alert('DATA DE ENVIO INV�LIDA !!!\n\nESSA N�O PODE SER PREENCHIDA NESSE STATUS !')
            document.form.txt_data_envio.focus()
            document.form.txt_data_envio.select()
            return false
        }
        //Hora de Envio ...
        if(document.form.txt_hora_envio.value != '') {
            alert('HORA DE ENVIO INV�LIDA !!!\n\nESSA N�O PODE SER PREENCHIDA NESSE STATUS !')
            document.form.txt_hora_envio.focus()
            document.form.txt_hora_envio.select()
            return false
        }
    }
//A Op��o DESPACHADA tem que fazer essa verifica��o
    if(document.form.cmb_status.value == 4) {
//Tipo de Despacho
        if(!combo('form', 'cmb_tipo_despacho', '', 'SELECIONE O TIPO DE DESPACHO !')) {
            return false
        }
//N.� de Remessa ...
        if(document.form.txt_numero_remessa.value != '') {
            if(!texto('form', 'txt_numero_remessa', 5, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'N.� DE REMESSA', '2')) {
                return false
            }
        }
    }
/*Habilito essas caixas p/ poder gravar no BD e n�o perder os valores que foram digitados anteriormente 
nas outras inst�ncias ...*/
    if(typeof(document.form.txt_gnre) == 'object')          document.form.txt_gnre.disabled = false
    if(typeof(document.form.txt_chave_acesso) == 'object')  document.form.txt_chave_acesso.disabled = false
    
    document.form.hdd_atualizar_dados_gerais.value = 'S'
}

function gerar_conta_a_pagar() {
    nova_janela('gerar_conta_a_pagar.php?id_nf=<?=$id_nf;?>', 'GERAR', '', '', '', '', '500', '780', 'c', 'c', '', '', 's', 's', '', '', '');
}

function desabilitar_tipo_despacho() {
/*Quando a Op��o do Status da NF, estiver apontando para Despachada, ent�o tem q 
habilitar a outro combo que � justamente a Op��o do Tipo de Despacho e a Caixa de Texto N�mero de Remessa*/
    if(document.form.cmb_status.value == 4) {
//Muda o Campo para Habilitado ...
        document.form.cmb_tipo_despacho.className   = 'combo'
        document.form.cmb_tipo_despacho.disabled    = false
        document.form.cmb_tipo_despacho.focus()
//Aki tem q desabilitar a outro combo Op��o Tipo de Despacho e a Caixa de Text 
    }else {
        document.form.cmb_tipo_despacho.value       = ''
        document.form.cmb_tipo_despacho.disabled    = true
        document.form.txt_numero_remessa.disabled   = true
//Muda o Campo para Desabilitado ...
        document.form.cmb_tipo_despacho.className   = 'textdisabled'
        document.form.txt_numero_remessa.className  = 'textdisabled'
        document.form.txt_numero_remessa.value      = ''
    }
}

function desabilitar_numero_remessa() {
//S� habilita esse campo de N.� de Remessa quando o despacho for 'COLETADO / ENTREGUE' ...
    if(document.form.cmb_tipo_despacho.value == 3) {
        document.form.txt_numero_remessa.disabled   = false
        //Muda a Cor de Fundo para Habilitado ...
        document.form.txt_numero_remessa.className  = 'caixadetexto'
        document.form.txt_numero_remessa.value      = '<?=$campos[0]['numero_remessa'];?>'
        document.form.txt_numero_remessa.focus()
    }else {//Escolheu outro Tipo de Despacho, ent�o desabilita o campo de N.� de Remessa ...
        document.form.txt_numero_remessa.disabled   = true
        //Muda a Cor de Fundo para Desabilitado ...
        document.form.txt_numero_remessa.className  = 'textdisabled'
        document.form.txt_numero_remessa.value      = ''
    }
}

function controle_devolucao_faturada() {
    var data_emissao = eval('<?=str_replace('-', '', $campos[0]['data_emissao']);?>')
//Data de Emiss�o ...
    if(data_emissao == 0) {
        alert('N�O � POSS�VEL MARCAR ESTA NF COMO "DEVOLU��O FATURADA" !!!\n\nDIGITE A DATA DE EMISS�O !')
        document.form.chkt_devolucao_faturada.checked = false//Desmarco o checkbox por haver Nadipl�ncia com rela��o a Data de Emiss�o ...
    }
}
</Script>
<?
//Se essa Tela foi aberta como Modo Grava��o ...
if($acao == 'G') $onload = 'desabilitar_tipo_despacho();desabilitar_numero_remessa()';
?>
<body onload='<?=$onload;?>'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********Controles de Tela**********-->
<input type='hidden' name='id_nf' value='<?=$id_nf;?>'>
<input type='hidden' name='acao' value='<?=$acao;?>'>
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
<input type='hidden' name='id_cliente' value='<?=$campos[0]['id_cliente'];?>'>
<input type='hidden' name='hdd_atualizar_dados_gerais'>
<!--*************************************-->
<table width='<?=$width;?>' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            DADOS GERAIS
            <?
                /*Nos menus: 

                "Em aberto / Liberadas 1" e ...;
                "Liberadas / Fat. / Canc. 2" sempre mostrarei o l�pis p/ edi��o de dados;

                "Fat. / Emp. / Despachadas 3", s� mostro esse l�pis p/ edi��o quando o status da 
                Nota Fiscal for >= Faturada;

                "Devolu��o 4" tamb�m sempre mostrarei o l�pis p/ edi��o de dados ...*/
                if($acao == 'L' && ($opcao <= 2 || $opcao == 3 && $campos[0]['status'] >= 2 || $opcao == 4)) {//Significa que essa Tela foi aberta somente p/ Modo Leitura ...
            ?>
            <img src = '../../../imagem/menu/alterar.png' border='0' onclick="nova_janela('dados_gerais.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>&acao=G', 'DADOS_GERAIS', '', '', '', '', '220', '750', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Dados Gerais' alt='Alterar Dados Gerais'>
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Status da NF:</b>
        </td>
        <td>
            <select name='cmb_status' title='Selecione o Status Nota Fiscal' onchange='desabilitar_tipo_despacho()' class='<?=$class_combo;?>' <?=$disabled;?>>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos[0]['status'] == 0) {
                        $selected0 = 'selected';
                    }else if($campos[0]['status'] == 1) {
                        $selected1 = 'selected';
                    }else if($campos[0]['status'] == 2) {
                        $selected2 = 'selected';
                    }else if($campos[0]['status'] == 3) {
                        $selected3 = 'selected';
                    }else if($campos[0]['status'] == 4) {
                        $selected4 = 'selected';
                    }else if($campos[0]['status'] == 5) {
                        $selected5 = 'selected';
                    }else if($campos[0]['status'] == 6) {
                        $selected6 = 'selected';
                    }
                    
                    if($opcao == 1) {//Menu Em Aberto / Liberadas ...
                ?>
                <option value='0' <?=$selected0;?>>EM ABERTO</option>
                <option value='1' <?=$selected1;?>>LIBERADA P/ FATURAR</option>
                <?
                    }else if($opcao == 2) {//Menu Liberadas / Fat. / Canc.
                ?>
                <option value='1' <?=$selected1;?>>LIBERADA P/ FATURAR</option>
                <option value='2' <?=$selected2;?>>FATURADA</option>
                <option value='5' <?=$selected5;?>>CANCELADA</option>
                <?
                    }else if($opcao == 3) {//Menu Fat. / Emp. / Despachadas ...
                ?>
                <option value='2' <?=$selected2;?>>FATURADA</option>
                <option value='3' <?=$selected3;?>>EMPACOTADA</option>
                <option value='4' <?=$selected4;?>>DESPACHADA</option>
                <?
                    }else if($opcao == 4) {//Menu de Devolu��o ...
                ?>
                <option value='6' <?=$selected6;?>>DEVOLU��O</option>
                <?
                    }else {//Acessado do Menu "Consultar" ...
                ?>    
                <option value='0' <?=$selected0;?>>EM ABERTO</option>
                <option value='1' <?=$selected1;?>>LIBERADA P/ FATURAR</option>
                <option value='2' <?=$selected2;?>>FATURADA</option>
                <option value='3' <?=$selected3;?>>EMPACOTADA</option>
                <option value='4' <?=$selected4;?>>DESPACHADA</option>
                <option value='5' <?=$selected5;?>>CANCELADA</option>
                <option value='6' <?=$selected6;?>>DEVOLU��O</option>
                <?        
                    }
                ?>
            </select>
            &nbsp;
            <select name='cmb_tipo_despacho' title='Tipo de Despacho' onchange='desabilitar_numero_remessa()' class='<?=$class_combo;?>' <?=$disabled;?>>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos[0]['tipo_despacho'] == 1) {
                        $selectedI = 'selected';
                    }else if($campos[0]['tipo_despacho'] == 2) {
                        $selectedII = 'selected';
                    }else if($campos[0]['tipo_despacho'] == 3) {
                        $selectedIII = 'selected';
                    }
                ?>
                <option value='1' <?=$selectedI;?>>PORTARIA</option>
                <option value='2' <?=$selectedII;?>>SAIU P/ ENTREGA</option>
                <option value='3' <?=$selectedIII;?>>COLETADO / ENTREGUE</option>
            </select>
            &nbsp;
            N.� de Remessa: <input type='text' name='txt_numero_remessa' value='<?=$campos[0]['numero_remessa'];?>' title='Digite o N.� de Remessa' maxlength='13' size='15' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
<?
/**********************************************************************************/
        //Se o Estado for MS, MT ou GS Ferramentas do Rio de Janeiro ...
        $vetor_ufs = array('9', '34');
        //... OU possuir Iva, ent�o eu for�o o usu�rio a preencher a GNRE ...
        if(in_array($campos[0]['id_uf'], $vetor_ufs) || ($calculo_total_impostos['valor_icms_st'] > 0 && $campos[0]['id_uf'] != 1) || $calculo_total_impostos['difal'] > 0) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>GNRE: </b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_gnre' value='<?=$campos[0]['gnre'];?>' title='Digite o GNRE' size='25' maxlength='18' class='<?=$class_opcao;?>' <?=$disabled_opcao;?>>
            &nbsp;
            <?
                //S� exibo este bot�o para os Estados que pagam ST devido terem conv�nio com SP ou DIFAL que � um imposto de ST tamb�m ...
                if(($calculo_total_impostos['valor_icms_st'] > 0 && $campos[0]['id_uf'] != 1) || $calculo_total_impostos['difal'] > 0) {
            ?>                      
                <input type='button' name='cmd_conta_a_pagar' value='Gerar Conta � Pagar' title='Gerar Conta � Pagar' onclick='gerar_conta_a_pagar()' class='<?=$class_botao;?>' <?=$disabled;?>>
            <?
                }
                if(in_array($campos[0]['id_uf'], $vetor_ufs)) echo "<br><font color='#ff9900'><b>N�o gerar GNRE, s� passar a c�pia da NF para o Cliente e aguardar o Fax com o N.� da via que ele Pagou.</b></font>";
            ?>
        </td>
    </tr>
<?
        }
        
        //S� para Empresas como Albafer ou Tool Master que aparecer� essa op��o de Chave de Acesso ...
        if($id_empresa_nf != 4) {
?>
    <tr class='linhanormal'>
        <td>
            Chave de Acesso:
        </td>
        <td>
            <input type='text' name='txt_chave_acesso' value='<?=$campos[0]['chave_acesso'];?>' title='Digite a Chave de Acesso' maxlength='54' size='70' class='<?=$class_opcao;?>' <?=$disabled_opcao;?>>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal'>
        <td>
            Data e Hora de Sa�da / Entrada:
        </td>
        <td>
            <input type='text' name='txt_data_saida_entrada' value='<?=$data_saida_entrada;?>' title='Digite a Data de Sa�da / Entrada' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_saida_entrada.disabled == false) {nova_janela('../../../calendario/calendario.php?campo=txt_data_saida_entrada&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
            &nbsp;<input type='text' name='txt_hora_saida_entrada' value='<?=$hora_saida_entrada;?>' title='Digite a Hora de Sa�da / Entrada' onkeyup="verifica(this, 'hora', '', '', event)" size='8' maxlength='5' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data e Hora de Envio:
        </td>
        <td>
            <input type='text' name='txt_data_envio' value='<?=$data_envio;?>' title='Digite a Data de Envio' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_saida_entrada.disabled == false) {nova_janela('../../../calendario/calendario.php?campo=txt_data_envio&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
            &nbsp;<input type='text' name='txt_hora_envio' value='<?=$hora_envio;?>' title='Digite a Hora de Envio' onkeyup="verifica(this, 'hora', '', '', event)" size='8' maxlength='5' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
<?
    //S� teremos como fazer uma Confirma��o Documental caso exista algum Trading ou Suframa na NF
    if($campos[0]['trading'] == 1 || $campos[0]['suframa'] == 1) {
?>
    <tr class='linhanormal'>
        <td>
            Confirma��o Documental <br>(Trading / Suframa):
        </td>
        <td>
            <?
                if($acao == 'G') {//Se essa Tela foi aberta como Modo Grava��o, ent�o exibo o Link ...
            ?>
            <a href="javascript:if(document.form.cmb_status.disabled == false) {nova_janela('../nota_saida/confirmacao_documental.php?id_nf=<?=$id_nf;?>', 'TRADING', '', '', '', '', 180, 700, 'c', 'c', '', '', 's', 's', '', '', '')}" title='Alterar Confirma��o Documental' class='link'>
            <?
                }
            
                if(empty($campos[0]['trading_confirmacao'])) {
                    echo '<font color="red"><b>SEM TRADING (DOCUMENTA��O)</b></font>';
                }else {
                    echo $campos[0]['trading_confirmacao'];
                }

                if($acao == 'G') {//Se essa Tela foi aberta como Modo Grava��o, ent�o exibo o Link ...
            ?>
            </a>
            <?
                }
//Busco do Funcion�rio que foi respons�vel pela Digita��o do documento
                if($campos[0]['id_funcionario_confirm_doc'] > 0) {
                    $sql = "SELECT nome 
                            FROM `funcionarios` 
                            WHERE `id_funcionario` = '".$campos[0]['id_funcionario_confirm_doc']."' LIMIT 1 ";
                    $campos_funcionario = bancos::sql($sql);
                    echo ' - <b>Respons�vel: </b>'.$campos_funcionario[0]['nome'];
                }
            ?>
        </td>
    </tr>
<?
    }
    /******************************************************************************/
    if($campos[0]['status'] == 6) {//S� para NF�s de Devolu��o que aparecer� essa op��o ...
        $checked = ($campos[0]['devolucao_faturada'] == 'S') ? 'checked' : '';
?>
    <tr class='linhanormal'>
        <td>
            <label for='chkt_devolucao_faturada'>
                <b>Devolu��o Faturada:</b>
            </label>
        </td>
        <td>
            <input type='checkbox' name='chkt_devolucao_faturada' value='S' id='chkt_devolucao_faturada' title='Devolu��o Faturada' onclick='controle_devolucao_faturada()' style='cursor:help' class='checkbox' <?=$checked;?> <?=$disabled;?>>
        </td>
    </tr>
<?
    }
    /******************************************************************************/
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <?
                if($acao == 'G') {//Significa que essa Tela foi aberta como Modo Grava��o ...
            ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_cliente_transportadora.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
            <?
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>