<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/financeiros.php');
require('../../../lib/intermodular.php');
require('../array_sistema/array_sistema.php');
session_start('funcionarios');

/******************************************************************************/
/**********************Verificação Especial p/ esta Tela***********************/
/******************************************************************************/
//Usuário não estava logado, muito provável que esteja respondendo um e-mail ...
if(!isset($_SESSION['id_funcionario'])) {
    /*Crio nesse exato momento uma nova Sessão e nessa armazeno a URL pendente que o usuário ficou de acessar, 
    mas que não conseguiu devido não estar logado no Sistema ...*/
    session_start('url_pendente');
    $_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];
?>
    <Script Language = 'JavaScript'>
        alert('É NECESSÁRIO ESTAR LOGADO NO SISTEMA P/ CONCLUIR ESTE PROCEDIMENTO !!!')
        //Como o usuário não estava logado no sistema, então eu forço o mesmo a entrar na marra ...
        window.location = '../../../default.php'
    </Script>
<?
}
/******************************************************************************/

$mensagem[1] = "<font class='confirmacao'>FOLLOW-UP REGISTRADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>FOLLOW-UP JÁ REGISTRADO.</font>";
$mensagem[3] = "<font class='confirmacao'>FOLLOW-UP EXCLUÍDO COM SUCESSO.</font>";

//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
$vetor_follow_ups = array_sistema::follow_ups();

//Exclusão do Follow-up do Cliente, caso este foi registrado errado
if($passo == 2) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $sql = "DELETE FROM `follow_ups` WHERE `id_follow_up` = '$id_follow_up' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 3;
    }else {
        $valor = '';
    }
}else {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        if(!empty($_POST['txt_observacao'])) {
            $data_hoje          = date('Y-m-d');
            $id_cliente_contato = (!empty($_POST['cmb_cliente_contato'])) ? $_POST['cmb_cliente_contato'] : 'NULL';
        
            if($id_cliente_contato > 0) {
                $sql = "SELECT `id_cliente` 
                        FROM `clientes_contatos` 
                        WHERE `id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
                $campos_cliente = bancos::sql($sql);
                $id_cliente     = $campos_cliente[0]['id_cliente'];
            }else {
                if(empty($id_cliente)) $id_cliente = 'NULL';
            }
            
            $sql = "SELECT `id_follow_up` 
                    FROM `follow_ups` 
                    WHERE `id_cliente_contato` = $id_cliente_contato 
                    AND `identificacao` = '$identificacao' 
                    AND `observacao` = '$txt_observacao' 
                    AND SUBSTRING(`data_sys`, 1, 10) = '$data_hoje' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Ainda não foi registrado esse Follow-UP ...
                if(empty($id_fornecedor)) $id_fornecedor = 'NULL';
                if(!empty($cmb_cliente_contato)) {
                    //Aqui eu guardo o id_representante no Registro de Follow-UP p/ agilizar o processamento da tela de PDT ...
                    $sql = "SELECT cr.`id_representante` 
                            FROM `clientes_contatos` cc 
                            INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                            INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` 
                            WHERE cc.`id_cliente_contato` = '$cmb_cliente_contato' LIMIT 1 ";
                    $campos_representante   = bancos::sql($sql);
                    $id_representante       = (count($campos_representante) == 1) ? $campos_representante[0]['id_representante'] : 'NULL';
                }else {
                    $id_representante   = 'NULL';
                }
                
                $observacao = strtolower($_POST['txt_observacao']);

                //Nós só podemos ter uma Impressão de Follow-UP para cada assunto ...
                if(!empty($_POST['chkt_exibir_no_pdf'])) {
                    /*Antes de qualquer coisa, desmarco todas as outras marcações de Exibir no Follow-UP, 
                    afinal só posso ter uma única marcação p/ cada assunto ...*/
                    $sql = "UPDATE `follow_ups` SET `exibir_no_pdf` = 'N' WHERE `identificacao` = '$identificacao' AND `origem` = '$origem' ";
                    bancos::sql($sql);

                    $exibir_no_pdf = 'S';
                }else {
                    $exibir_no_pdf = 'N';
                }
                
                /*******************************************************************************/
                $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_fornecedor`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `exibir_no_pdf`, `data_sys`) 
                        VALUES (NULL, $id_cliente, $id_fornecedor, $id_cliente_contato, $id_representante, '$_SESSION[id_funcionario]', '$identificacao', '$origem', '$observacao', '$exibir_no_pdf', '".date('Y-m-d H:i:s')."') ";
                bancos::sql($sql);
                $id_follow_up = bancos::id_registro();
                
                if($origem == 6) {//APV (Atendimento Planejado de Vendas)
/******************************************************************************/
//Atualizando Clientes ...
/*Atualizo o Campo de data da Última Visita no Cadastro de Clientes com a Data Atual, porque se o Usuário
respondeu um APV que estava em aberto, então significa que esteje visitou o Cliente q estava com pendência.*/
                    $sql = "UPDATE `clientes` SET `data_ultima_visita` = '$data_hoje' WHERE `id_cliente` = '$identificacao' LIMIT 1 ";
                    bancos::sql($sql);
                }
                $valor = 1;
                
                /**************************************************************/
                /*****************Controle p/ envio de E-mail******************/
                /**************************************************************/
                if(!empty($_POST['cmb_email_para'])) {
                    /**Busca do IP Externo que está cadastrado em alguma Empresa aqui do Sistema, esse número
                    será utilizado mais abaixo ...**/
                    $sql = "SELECT `ip_externo` 
                            FROM `empresas` 
                            WHERE `ip_externo` <> '' LIMIT 1 ";
                    $campos_empresa = bancos::sql($sql);
                    /*Se encontrar um IP Externo cadastrado, o conteúdo do e-mail apontará p/ esse IP "que é a preferência", 
                    do contrário o IP será da onde o usuário está acessando o ERP $_SERVER['HTTP_HOST'] ...*/
                    $ip_externo     = (count($campos_empresa) == 1) ? $campos_empresa[0]['ip_externo'] : $_SERVER['HTTP_HOST'];
                    
                    //Aqui eu busco o email do Funcionário logado que está enviando o e-mail "Remetente" ...
                    $sql = "SELECT `email_externo` 
                            FROM `funcionarios` 
                            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
                    $campos_email   = bancos::sql($sql);
                    $remetente      = $campos_email[0]['email_externo'];
                    
                    //"Destinatários" ...
                    $destinos = $_POST['cmb_email_para'].', ';
                    
                    if(!empty($_POST['cmb_com_copia_para'])) {
                        foreach($_POST['cmb_com_copia_para'] as $cmb_com_copia_para) {
                            //Combo "Com Cópia para" preenchido, diferente de SELECIONE ...
                            if($cmb_com_copia_para != '') $destinos.= $cmb_com_copia_para.', ';
                        }
                    }
                    $destinos = substr($destinos, 0, strlen($destinos) - 2);
                    
                    /*Faço a busca do E-mail do(s) Funcionário(s) que foi selecionado(s) nas combos 
                    "cmb_email_para" e "cmb_com_copia_para" ...
                    $sql = "SELECT `email_externo` 
                            FROM `funcionarios` 
                            WHERE `id_funcionario` IN ($id_funcionarios_enviar_email) ";
                    $campos_email   = bancos::sql($sql);
                    $linhas_email   = count($campos_email);
                    
                    for($i = 0; $i < $linhas_email; $i++) $destinos_copia.= $campos_email[$i]['email_externo'].', ';
                    
                    $destinos_copia = substr($destinos_copia, 0, strlen($destinos_copia) - 2);*/
                    
                    /****************Montando o Corpo do E-mail****************/
                    if(!empty($_POST['id_follow_up'])) {//Resposta à um Follow-UP visualizado e registrado anteriormente ...
                        //Aqui eu busco dados de um Follow-UP que foi visualizado e registrado anteriormente ...
                        $sql = "SELECT * 
                                FROM `follow_ups` 
                                WHERE `id_follow_up` = '$_POST[id_follow_up]' LIMIT 1 ";
                        $campos_follow_up           = bancos::sql($sql);
                        $id_cliente                 = $campos_follow_up[0]['id_cliente'];
                        $id_cliente_contato         = $campos_follow_up[0]['id_cliente_contato'];
                        $id_funcionario_follow_up   = $campos_follow_up[0]['id_funcionario'];
                        
                        /*Aqui eu busco o contato na Tabela Relacional se é que 
                        esse foi selecionado ...*/
                        if(!empty($id_cliente)) {
                            $sql = "SELECT IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS cliente 
                                    FROM `clientes` 
                                    WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                            $campos_cliente = bancos::sql($sql);
                            $cliente        = $campos_cliente[0]['cliente'];
                        }
                        
                        $rotulo_origem              = $vetor_follow_ups[$campos_follow_up[0]['origem']];
                        
                        if($_POST['origem'] == 4) {//Contas à Receber
                            $sql = "SELECT `num_conta` 
                                    FROM `contas_receberes` 
                                    WHERE `id_conta_receber` = '".$campos_follow_up[0]['identificacao']."' LIMIT 1 ";
                            $campos_numero  = bancos::sql($sql);
                            $numero_conta   = $campos_numero[0]['num_conta'];
                        }else if($_POST['origem'] == 5) {//Nota Fiscal
                            $numero_conta   = faturamentos::buscar_numero_nf($campos_follow_up[0]['identificacao'], 'S');
                        }else {
                            $numero_conta               = $campos_follow_up[0]['identificacao'];
                        }
                        
                        /*Aqui eu busco o login do Funcionário que registrou esse Follow-UP e o mesmo 
                        será apresentado no corpo do "E-mail" ...*/
                        $sql = "SELECT `login` 
                                FROM `logins` 
                                WHERE `id_funcionario` = '$id_funcionario_follow_up' LIMIT 1 ";
                        $campos_login       = bancos::sql($sql);
                        $login              = $campos_login[0]['login'];
                        
                        $data_ocorrencia    = data::datetodata($campos_follow_up[0]['data_sys'], '/').' - '.substr($campos_follow_up[0]['data_sys'], 11, 8);
                        
                        /*Aqui eu busco o contato na Tabela Relacional se é que 
                        esse foi selecionado ...*/
                        if(!empty($id_cliente_contato)) {
                            $sql = "SELECT `nome` 
                                    FROM `clientes_contatos` 
                                    WHERE `id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
                            $campos_contato = bancos::sql($sql);
                            $contato        = $campos_contato[0]['nome'];
                        }
                        $observacao         = $campos_follow_up[0]['observacao'];
                    }else {//Resposta à uma Ocorrência que está sendo inclusa na hora ...
                        /*Aqui eu busco o contato na Tabela Relacional se é que 
                        esse foi selecionado ...*/
                        if(!empty($id_cliente)) {
                            $sql = "SELECT IF(`razaosocial` = '', `nomefantasia`, `razaosocial`) AS cliente 
                                    FROM `clientes` 
                                    WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                            $campos_cliente = bancos::sql($sql);
                            $cliente        = $campos_cliente[0]['cliente'];
                        }
                        
                        $rotulo_origem  = $vetor_follow_ups[$_POST['origem']];
                    
                        if($_POST['origem'] == 1) {//Tela de Orçamentos
                            $sql = "SELECT e.`nomefantasia`, ov.`id_orcamento_venda` 
                                    FROM `orcamentos_vendas` ov 
                                    INNER JOIN `empresas` e ON e.`id_empresa` = ov.`id_empresa` 
                                    WHERE ov.`id_orcamento_venda` = '$_POST[identificacao]' LIMIT 1 ";
                            $campos_dados_gerais    = bancos::sql($sql);
                            $empresa                = $campos_dados_gerais[0]['nomefantasia'];

                            $numero_conta   = "<a href='http://192.168.1.253/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=".$campos_dados_gerais[0]['id_orcamento_venda']."'>".$campos_dados_gerais[0]['id_orcamento_venda']."</a> <- Interno / ";
                            $numero_conta.= "<a href='http://".$ip_externo."/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=".$campos_dados_gerais[0]['id_orcamento_venda']."'>".$campos_dados_gerais[0]['id_orcamento_venda']."</a> <- Externo";
                        }else if($_POST['origem'] == 2) {//Tela de Pedidos
                            $sql = "SELECT e.`nomefantasia`, pv.`id_pedido_venda` 
                                    FROM `pedidos_vendas` pv 
                                    INNER JOIN `empresas` e ON e.`id_empresa` = pv.`id_empresa` 
                                    WHERE pv.`id_pedido_venda` = '$_POST[identificacao]' LIMIT 1 ";
                            $campos_dados_gerais    = bancos::sql($sql);
                            $empresa                = $campos_dados_gerais[0]['nomefantasia'];
                            
                            $numero_conta   = "<a href='http://192.168.1.253/erp/albafer/modulo/vendas/pedidos/itens/itens.php?id_pedido_venda=".$campos_dados_gerais[0]['id_pedido_venda']."'>".$campos_dados_gerais[0]['id_pedido_venda']."</a> <- Interno / ";
                            $numero_conta.= "<a href='http://".$ip_externo."/erp/albafer/modulo/vendas/pedidos/itens/itens.php?id_pedido_venda=".$campos_dados_gerais[0]['id_pedido_venda']."'>".$campos_dados_gerais[0]['id_pedido_venda']."</a> <- Externo";
                        }else if($_POST['origem'] == 3) {//Tela de Gerenciar Estoque
                            //echo 'Cliente';
                        }else if($_POST['origem'] == 4) {//Contas à Receber
                            $sql = "SELECT e.`nomefantasia`, cr.`num_conta` 
                                    FROM `contas_receberes` cr 
                                    INNER JOIN `empresas` e ON e.`id_empresa` = cr.`id_empresa` 
                                    WHERE `id_conta_receber` = '$_POST[identificacao]' LIMIT 1 ";
                            $campos_dados_gerais    = bancos::sql($sql);
                            $empresa                = $campos_dados_gerais[0]['nomefantasia'];
                            $numero_conta           = $campos_dados_gerais[0]['num_conta'];
                        }else if($_POST['origem'] == 5) {//Nota Fiscal
                            $numero_conta   = "<a href='http://192.168.1.253/erp/albafer/modulo/faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=".$_POST['identificacao']."&pop_up=1'>".faturamentos::buscar_numero_nf($_POST['identificacao'], 'S')."</a> <- Interno / ";
                            $numero_conta.= "<a href='http://".$ip_externo."/erp/albafer/modulo/faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=".$_POST['identificacao']."&pop_up=1'>".faturamentos::buscar_numero_nf($_POST['identificacao'], 'S')."</a> <- Externo";
                        }else if($_POST['origem'] == 6) {//APV
                            //Significa que um Follow-Up que está sendo registrado pela parte de Vendas (Antigo Sac)
                            if($campos[0]['modo_venda'] == 1) {
                                //echo 'FONE';
                            }else {
                                //echo 'VISITA';
                            }
                        }else if($_POST['origem'] == 7) {//Atend. Interno
                            //echo 'Atend. Interno';
                        }else if($_POST['origem'] == 8) {//Depto. Técnico
                            //echo 'Depto. Técnico';
                        }else if($_POST['origem'] == 9) {//Pendências
                            //echo 'Pendências';
                        }else if($_POST['origem'] == 10) {//TeleMarketing
                            //echo 'TeleMkt';
                        }else if($_POST['origem'] == 11) {//Acompanhamento
                            //echo 'Acompanhamento';
                        }else if($_POST['origem'] == 17) {//Acompanhamento
                            $sql = "SELECT e.`nomefantasia`, nfe.`num_nota` 
                                    FROM `nfe` 
                                    INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
                                    WHERE nfe.`id_nfe` = '$_POST[identificacao]' LIMIT 1 ";
                            $campos_dados_gerais    = bancos::sql($sql);
                            $empresa                = $campos_dados_gerais[0]['nomefantasia'];
                            $numero_conta           = $campos_dados_gerais[0]['num_conta'];
                        }else {
                            $numero_conta           = $_POST['identificacao'];
                        }
                        
                        //Aqui eu busco o login do Funcionário logado que será apresentado no corpo do "E-mail" ...
                        $sql = "SELECT `login` 
                                FROM `logins` 
                                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
                        $campos_login       = bancos::sql($sql);
                        $login              = $campos_login[0]['login'];
                        
                        $data_ocorrencia    = date('d/m/Y').' - '.date('H:i:s');
                        
                        /*Aqui eu busco o contato na Tabela Relacional se é que 
                        esse foi selecionado ...*/
                        if(!empty($id_cliente_contato)) {
                            $sql = "SELECT `nome` 
                                    FROM `clientes_contatos` 
                                    WHERE `id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
                            $campos_contato = bancos::sql($sql);
                            $contato        = $campos_contato[0]['nome'];
                        }
                        
                        $observacao         = '';
                    }
                    /**********************************************************/
                    //Concateno os dados p/ enviar por e-mail junto da Observação de Acompanhamento do Usuário ...
                    $corpo_email = '<b>Cliente: </b>'.$cliente.'<br/><b>Origem: </b>'.$rotulo_origem.'<br/><b>N.º: </b>'.$numero_conta.'<br/><b>Empresa: </b>'.$empresa.'<br/><b>Login: </b>'.$login.'<br/><b>Ocorrência: </b>'.$data_ocorrencia.'<br/><b>Contato: </b>'.$contato.'<br/><b>Observação: </b>'.$observacao.'<br/><br/><font color="darkblue"><b>Observação de Acompanhamento: </b></font>'.$_POST['txt_observacao'];
                    $corpo_email.= "
                                <br/><br/>
                                <center>
                                    <a href='http://192.168.1.253/erp/albafer/modulo/classes/follow_ups/incluir.php?identificacao=$_POST[identificacao]&origem=$_POST[origem]&id_follow_up=$id_follow_up' title='Acesso Interno'>
                                        Acesso Interno
                                    </a>
                                    &nbsp;
                                    <a href='http://".$ip_externo."/erp/albafer/modulo/classes/follow_ups/incluir.php?identificacao=$_POST[identificacao]&origem=$_POST[origem]&id_follow_up=$id_follow_up' title='Acesso Externo'>
                                        Acesso Externo
                                    </a>
                                </center>";
                    $assunto = 'Acompanhamento de Cliente '.$cliente;
                    comunicacao::email($remetente, $destinos, '', $assunto, $corpo_email);
                }
                /**************************************************************/
            }else {//Já foi registrado esse Follow-UP
                $valor = 2;
            }
            
            /*Verifico se existe alguma Sessão com o nome "url_pendente", se sim, já não faz mais sentido eu 
            ainda manter a mesma, afinal o usuário a esta altura do campeonato já acessou essa que estava 
            pendente desenrolando o procedimento que estava em pendência ...*/
            if(session_is_registered('request_uri')) {
                //Removo a sessão URL Pendente ...
                session_start('url_pendente');
                unset($_SESSION['request_uri']);//Exclui todas as variáveis armazenadas da Sessão ...
                session_destroy('url_pendente');//Destrói a Sessão já vazia ...
            }
?>
    <Script Language = 'JavaScript'>
            alert('FOLLOW-UP REGISTRADO COM SUCESSO !')

            if(opener != null) {//Significa que essa tela foi aberta como sendo Pop-UP ...
                opener.location = opener.location.href
                window.close()
            }else {//Significa que essa tela foi aberta de modo normal, provavelmente por uma reposta de e-mail ...
                //Como o usuário não estava logado no sistema, então eu forço o mesmo a entrar na marra ...
                window.location = '../../../mural/mural.php'
            }
    </Script>
<?
        }
    }
}

if($controle == 2) {//Exclusão de contatos ...
    $sql = "UPDATE `clientes_contatos` SET `ativo` = '0' WHERE `id_cliente_contato` = '$_POST[cmb_cliente_contato]' LIMIT 1 ";
    bancos::sql($sql);
}

/**************************Buscas Genéricas de Dados Independente da Situação*************************/
//Com o id_identificação eu busco qual é a conta, e o id_cliente
if($origem == 1) {//Tela de Orçamentos
    $sql = "SELECT `id_orcamento_venda`, `id_cliente` 
            FROM `orcamentos_vendas` 
            WHERE `id_orcamento_venda` = '$identificacao' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_cliente     = $campos[0]['id_cliente'];
    $numero_conta   = $campos[0]['id_orcamento_venda'];
    $rotulo         = 'N.º do Orçamento:';
}else if($origem == 2) {//Tela de Pedidos
    $sql = "SELECT `id_pedido_venda`, `id_cliente` 
            FROM `pedidos_vendas` 
            WHERE `id_pedido_venda` = '$identificacao' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_cliente     = $campos[0]['id_cliente'];
    $numero_conta   = $campos[0]['id_pedido_venda'];
    $rotulo         = 'N.º do Pedido:';
}else if($origem == 3) {//Tela de Gerenciar Estoque
//Busca a razão social do Cliente
    $sql = "SELECT c.`id_cliente`, c.`razaosocial` 
            FROM `follow_ups` fu 
            LEFT JOIN `clientes` c ON c.`id_cliente` = fu.`id_cliente` 
            WHERE fu.`id_follow_up` = '$id_follow_up' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $razao_social   = $campos[0]['razaosocial'];
    $numero_conta   = $razao_social;
    $rotulo         = 'Cliente:';
}else if($origem == 4) {//Contas à Receber
    $sql = "SELECT `id_cliente`, `num_conta` 
            FROM `contas_receberes` 
            WHERE `id_conta_receber` = '$identificacao' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_cliente     = $campos[0]['id_cliente'];
    $numero_conta   = $campos[0]['num_conta'];
    $rotulo         = 'N.º da Conta à Receber:';
}else if($origem == 5) {//Nota Fiscal
    $sql = "SELECT `id_cliente` 
            FROM `nfs` 
            WHERE `id_nf` = '$identificacao' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_cliente     = $campos[0]['id_cliente'];
    $numero_conta   = faturamentos::buscar_numero_nf($identificacao, 'S');
    $rotulo         = 'N.º da Nota Fiscal:';
}else if($origem == 6) {//APV (Atendimento Planejado de Vendas)
//Busca a razão social do Cliente
    /*$sql = "SELECT `razaosocial` 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $razao_social   = $campos[0]['razaosocial'];
    $numero_conta   = $razao_social;*/
    $rotulo         = 'APV:';
}else if($origem == 7) {//Atendimento Interno - (Vendas)
//Nesse caso eu já estou trabalhando com o Cliente diretamente, não existe conta
    //$id_cliente     = $identificacao;
//Busca a razão social do Cliente
    /*$sql = "SELECT `razaosocial` 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $razao_social   = $campos[0]['razaosocial'];
    $numero_conta   = $razao_social;*/
    $rotulo         = 'Atend. Interno:';
}else if($origem == 8) {//Depto. Técnico - (Técnico)
//Nesse caso eu já estou trabalhando com o Cliente diretamente, não existe conta
    $id_cliente = $identificacao;
//Busca a razão social do Cliente
    $sql = "SELECT `razaosocial` 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $razao_social   = $campos[0]['razaosocial'];
    $numero_conta   = $razao_social;
    $rotulo         = 'Depto. Técnico:';
}else if($origem == 9) {//Pendências
//Busca a razão social do Cliente
    /*$sql = "SELECT `razaosocial` 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $razao_social   = $campos[0]['razaosocial'];
    $numero_conta   = $razao_social;*/
    $rotulo         = 'Pendências:';
}else if($origem == 10) {//TeleMarketing
//Nesse caso eu já estou trabalhando com o Cliente diretamente, não existe conta
    /*$sql = "SELECT `razaosocial` 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $razao_social   = $campos[0]['razaosocial'];
    $numero_conta   = $razao_social;*/
    $rotulo         = 'TeleMkt:';
}else if($origem == 11) {//Acompanhamento
    $sql = "SELECT `razaosocial` 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $razao_social   = $campos[0]['razaosocial'];
    $numero_conta   = $razao_social;
    $rotulo         = 'Acompanhamento:';
}else if($origem == 12) {//Simples Relato
//Nesse caso eu já estou trabalhando com o Cliente diretamente, não existe conta
    $id_cliente = $identificacao;
    $sql = "SELECT `razaosocial` 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $razao_social   = $campos[0]['razaosocial'];
    $numero_conta   = $razao_social;
    $rotulo         = 'Simples Relato:';
}else if($origem == 13) {//Projeção Trimestral
//Nesse caso eu já estou trabalhando com o Cliente diretamente, não existe conta
    $id_cliente = $identificacao;
    $sql = "SELECT `razaosocial` 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $razao_social   = $campos[0]['razaosocial'];
    $numero_conta   = $razao_social;
    $rotulo         = 'Projeção Trimestral:';
}else if($origem == 14) {//OPC ...
//Nesse caso eu já estou trabalhando com o Cliente diretamente, não existe conta
    $sql = "SELECT `id_opc`, `id_cliente` 
            FROM `opcs` 
            WHERE `id_opc` = '$identificacao' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_cliente     = $campos[0]['id_cliente'];
    $numero_conta   = $campos[0]['id_projecao_apv'];
    $rotulo         = 'OPC:';
}else if($origem == 18) {//Contas à Pagar ...
    $sql = "SELECT `id_fornecedor`, `numero_conta` 
            FROM `contas_apagares` 
            WHERE `id_conta_apagar` = '$identificacao' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_fornecedor  = $campos[0]['id_fornecedor'];
    $numero_conta   = $campos[0]['numero_conta'];
    $rotulo         = 'N.º da Conta à Pagar:';
}else if($origem == 19) {//Produto Acabado ...
    $sql = "SELECT `id_produto_acabado` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$identificacao' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_produto_acabado = $campos[0]['id_produto_acabado'];
    $rotulo             = 'Produto Acabado:';
}else if($origem == 20) {//Produto Insumo ...
    $sql = "SELECT `id_produto_insumo`, `discriminacao` 
            FROM `produtos_insumos` 
            WHERE `id_produto_insumo` = '$identificacao' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_produto_insumo  = $campos[0]['id_produto_insumo'];
    $rotulo             = 'Produto Insumo:';
}
/*****************************************************************************************************/
?>
<html>
<head>
<title>.:: Incluir Novo Follow-up ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
//Variável Global ...
qtde_com_copia = 1

function validar() {
//Contato do Cliente
    if(typeof(document.form.cmb_cliente_contato == 'object')) {
        if(document.form.cmb_cliente_contato.value == '') {
            var resposta = confirm('DESEJA SELECIONAR UM CONTATO ?')
            if(resposta == true) {
                document.form.cmb_cliente_contato.focus()
                return false
            }
        }
    }
//Observação ...
    if(document.form.txt_observacao.value == '') {
        alert('DIGITE A OBSERVAÇÃO !')
        document.form.txt_observacao.focus()
        return false
    }
//Forço o usuário a digitar no mínimo 8 caracteres nesta observação de Follow-UP ...
    if(document.form.txt_observacao.value.length < 8) {
        alert('OBSERVAÇÃO INCOMPLETA !')
        document.form.txt_observacao.focus()
        return false
    }
}

function alterar_contato() {
    if(document.form.cmb_cliente_contato.value == '') {
        alert('SELECIONE O CONTATO DO CLIENTE !')
        document.form.cmb_cliente_contato.focus()
        return false
    }else {
        nova_janela('../cliente/alterar_contatos.php?id_cliente_contato='+document.form.cmb_cliente_contato.value, 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')
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
            document.form.controle.value = 2
            document.form.submit()
        }
    }
}

function incluir_com_copia() {
//"E-mail para" ...
    if(!combo('form', 'cmb_email_para', '', 'SELECIONE O E-MAIL PARA !')) {
        return false
    }
//Ainda não existe o Primeiro "Com cópia para" ...
    if(parent.document.getElementById('cmb_com_copia_para0') == null) {
        qtde_com_copia++
        ajax('com_copia.php?qtde_com_copia='+qtde_com_copia, 'div_com_copia')
    }else {//Já existe pelo menos um "Com cópia para" ...
        var elementos   = parent.document.form.elements
        var linhas      = 0
        for(var i = 0; i < elementos.length; i++) {
            if(elementos[i].name == 'cmb_com_copia_para[]') linhas++
        }
        //Significa que já existe pelo menos um "Com cópia para" 
        for(var i = 0; i < linhas; i++) {
            if(parent.document.getElementById('cmb_com_copia_para'+i).value == '') {
                //"Com cópia para" ...
                alert('SELECIONE O COM CÓPIA PARA !')
                parent.document.getElementById('cmb_com_copia_para'+i).focus()
                return false
            }
        }
        qtde_com_copia++
        ajax('com_copia.php?qtde_com_copia='+qtde_com_copia, 'div_com_copia')
    }
}

function excluir_com_copia() {
    qtde_com_copia--
    ajax('com_copia.php?qtde_com_copia='+qtde_com_copia, 'div_com_copia')
}
</Script>
</head>
<body onload="ajax('com_copia.php?qtde_com_copia=1', 'div_com_copia');document.form.txt_observacao.focus()">
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--*************************************************************************-->
<input type='hidden' name='id_follow_up' value='<?=$id_follow_up;?>'>
<!--Aki é o id_orcamento, id_pedido, id_conta_apagar, id_conta_receber, sei lá ... qualquer id-->
<input type='hidden' name='identificacao' value='<?=$identificacao;?>'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<!--Controle dos Pop-Ups de Contato-->
<input type='hidden' name='controle'>
<!--Tipo de Tela-->
<input type='hidden' name='origem' value='<?=$origem;?>'>
<!--*************************************************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Novo Follow-Up
        </td>
    </tr>
<?
    /******************************************************************************/
    /**********************************Follow-Ups**********************************/
    /******************************************************************************/
    if(!empty($id_follow_up)) {//Significa que está sendo visualizado um Follow-UP já registrado ...
        /*Aqui eu busco dados do $id_follow_up passado por parâmetro, todos os dados serão utilizados em algumas 
        linhas mais abaixo, mas o principal para este momento é o id_cliente para apresentar a sua situção 
        aqui na empresa e seus dados de Quitações nos iframes mais abaixo ...*/
        $sql = "SELECT * 
                FROM `follow_ups` 
                WHERE `id_follow_up` = '$id_follow_up' LIMIT 1 ";
        $campos_follow_up   = bancos::sql($sql);
        $id_cliente         = $campos_follow_up[0]['id_cliente'];
    }
/******************************************************************************/
/***********************************Clientes***********************************/
/******************************************************************************/
    if(!empty($id_cliente)) {
        //Busca o Cliente e o Crédito do Cliente com o id_cliente
        $sql = "SELECT `razaosocial`, `credito` 
                FROM `clientes` 
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $credito        = $campos[0]['credito'];
        $razao_social   = $campos[0]['razaosocial'];

        if(empty($rotulo) && empty($numero_conta)) {
            $numero_conta   = $razao_social;
            $rotulo         = 'Cliente:';
        }else if(empty($rotulo) && !empty($numero_conta)) {
            $rotulo         = 'Cliente:';
        }else if(!empty($rotulo) && empty($numero_conta)) {
            $numero_conta   = $razao_social;
        }
?>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow' size='2'>&nbsp;Cliente: </font>
            <font color='#FFFFFF' size='2'><?=$razao_social;?></font>
            <font color='yellow' size='2'>/ Crédito:</font>
            <font color='#FFFFFF' size='2'><?=$credito;?></font>
            &nbsp;
            <a href="javascript:nova_janela('../cliente/alterar.php?passo=1&id_cliente=<?=$id_cliente;?>&pop_up=1', 'POP', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')">
                <img src = '../../../imagem/propriedades.png' title='Detalhes de Cliente' alt='Detalhes de Cliente' style='cursor:pointer' border='0'>
            </a>
            -
            <a href="javascript:nova_janela('../pedido_vendas/relatorio_pendencias.php?id_cliente=<?=$id_cliente;?>', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Relatório de Pendências' class='link'>
                <font color='#48FF73' size='-1'>
                    Pendências
                </font>
            </a>
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='iframe' onclick="showHide('qtde_quitacao'); return false" style='cursor:pointer'>
        <td height='22' align='left'>
            <font color='yellow' size='2'>
                &nbsp;Quitação(ões) nos últimos 6 meses
            </font>
            <span id='statusqtde_quitacao'>
                &nbsp;
            </span>
            <span id='statusqtde_quitacao'>
                &nbsp;
            </span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Eu passo a origem por parâmetro também para não dar erro de URL na parte de detalhes da conta e de cheque-->
            <iframe src = '../cliente/qtde_quitacao.php?id_cliente=<?=$id_cliente;?>&origem=<?=$origem;?>' name='qtde_quitacao' id='qtde_quitacao' marginwidth='0' marginheight='0' style='display: none' frameborder='0' height='200' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
/************************Visualização das Contas à Receber************************/
    //Visualizando as Contas à Receber
    $retorno    = financeiros::contas_em_aberto($id_cliente, 1, '', 2);
    $linhas     = count($retorno['id_contas']);
    if($linhas > 0) {
?>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes2'); return false">
        <td colspan='2'>
            <font color='yellow' size='2'>
                &nbsp;Débito(s) à Receber: 
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$linhas;?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_cliente por parâmetro porque utilizo dentro da Função de Receber-->
            <iframe src = '../cliente/debitos_receber.php?id_cliente=<?=$id_cliente;?>&ignorar_sessao=1' name='detalhes2' id='detalhes2' marginwidth='0' marginheight='0' style='display: none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
<?
        }
/*********************************************************************************/
    /******************************************************************************/
    /*********************************Fornecedores*********************************/
    /******************************************************************************/
    }else if($id_fornecedor > 0) {
        //Busca a Razão Social do id_fornecedor passado por parâmetro ...
        $sql = "SELECT `razaosocial` 
                FROM `fornecedores` 
                WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
        $campos = bancos::sql($sql);
?>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow' size='2'>&nbsp;Fornecedor: </font>
            <font color='#FFFFFF' size='2'><?=$campos[0]['razaosocial'];?></font>
        </td>
    </tr>
<?
    /******************************************************************************/
    /******************************Produtos Acabados*******************************/
    /******************************************************************************/
    }else if($id_produto_acabado > 0) {
?>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow' size='2'>&nbsp;Produto Acabado: </font>
            <font color='#FFFFFF' size='2'><?=intermodular::pa_discriminacao($id_produto_acabado);?></font>
        </td>
    </tr>
<?
    /******************************************************************************/
    /******************************Produtos Insumos********************************/
    /******************************************************************************/
    }else if($id_produto_insumo > 0) {
        //Busca a Referência e Discriminação do id_produto_insumo passado por parâmetro ...
        $sql = "SELECT pi.`id_produto_insumo`, CONCAT(g.`referencia`, ' - ', pi.`discriminacao`) AS discriminacao 
                FROM `produtos_insumos` pi 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                WHERE pi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos = bancos::sql($sql);
?>
    <tr class='linhadestaque'>
        <td>
            <font color='yellow' size='2'>&nbsp;Produto Acabado: </font>
            <font color='#FFFFFF' size='2'><?=$campos[0]['discriminacao'];?></font>
        </td>
    </tr>
<?
    }
    /******************************************************************************/
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    /******************************************************************************/
    /**********************************Follow-Ups**********************************/
    /******************************************************************************/
    if(!empty($id_follow_up)) {//Significa que está sendo visualizado um Follow-UP já registrado ...
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Dados do Follow-UP Registrado
        </td>
    </tr>
    <?
        /**********************************************************************/
        if($id_cliente > 0) {
    ?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Cliente:</b>
            </font>
        </td>
        <td>
        <?
            $sql = "SELECT CONCAT(`razaosocial`, ' - ', `nomefantasia`) AS cliente 
                    FROM `clientes` 
                    WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            echo $campos_cliente[0]['cliente'];
        ?>
        </td>
    </tr>
    <?
        }else if($campos_follow_up[0]['id_fornecedor'] > 0) {
    ?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Fornecedor:</b>
            </font>
        </td>
        <td>
        <?
            $sql = "SELECT CONCAT(`razaosocial`, ' - ', `nomefantasia`) AS fornecedor 
                    FROM `fornecedores` 
                    WHERE `id_fornecedor` = '".$campos_follow_up[0]['id_fornecedor']."' LIMIT 1 ";
            $campos_fornecedor = bancos::sql($sql);
            echo $campos_fornecedor[0]['fornecedor'];
        ?>
        </td>
    </tr>
    <?
        }
        /**********************************************************************/
    ?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Origem: </b>
            </font>
        </td>
        <td>
            <?=$vetor_follow_ups[$campos_follow_up[0]['origem']];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>N.º: </b>
            </font>
        </td>
        <td>
        <?
            if($campos_follow_up[0]['origem'] == 3) {//Tela de Gerenciar Estoque
                //echo 'Cliente';
            }else if($campos_follow_up[0]['origem'] == 4) {//Contas à Receber
                $sql = "SELECT `num_conta` 
                        FROM `contas_receberes` 
                        WHERE `id_conta_receber` = '".$campos_follow_up[0]['identificacao']."' LIMIT 1 ";
                $campos_numero = bancos::sql($sql);
                echo $campos_numero[0]['num_conta'];
            }else if($campos_follow_up[0]['origem'] == 5) {//Nota Fiscal
                echo faturamentos::buscar_numero_nf($campos_follow_up[0]['identificacao'], 'S');
            }else if($campos_follow_up[0]['origem'] == 6) {//APV
//Significa que um Follow-Up que está sendo registrado pela parte de Vendas (Antigo Sac)
                if($campos_follow_up[0]['modo_venda'] == 1) {
                    echo 'FONE';
                }else {
                    echo 'VISITA';
                }
            }else if($campos_follow_up[0]['origem'] == 7) {//Atend. Interno
                //echo 'Atend. Interno';
            }else if($campos_follow_up[0]['origem'] == 8) {//Depto. Técnico
                //echo 'Depto. Técnico';
            }else if($campos_follow_up[0]['origem'] == 9) {//Pendências
                //echo 'Pendências';
            }else if($campos_follow_up[0]['origem'] == 10) {//TeleMarketing
                //echo 'TeleMkt';
            }else if($campos_follow_up[0]['origem'] == 11) {//Acompanhamento
                //echo 'Acompanhamento';
            }else {
                echo $campos_follow_up[0]['identificacao'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Login:</b>
            </font>
        </td>
        <td>
        <?
            if($campos_follow_up[0]['id_funcionario'] > 0) {//Aqui se existir, eu busco o Login na Tabela Relacional ...
                $sql = "SELECT `login` 
                        FROM `logins` 
                        WHERE `id_funcionario` = ".$campos_follow_up[0]['id_funcionario']." LIMIT 1 ";
                $campos_login = bancos::sql($sql);
                echo $campos_login[0]['login'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Ocorrência:</b>
            </font>
        </td>
        <td>
            <?=data::datetodata($campos_follow_up[0]['data_sys'], '/').' - '.substr($campos_follow_up[0]['data_sys'], 11, 8);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Contato:</b>
            </font>
        </td>
        <td>
        <?
            if(!empty($campos_follow_up[0]['id_cliente_contato'])) {
                //Aqui busca o Contato na Tabela Relacional ...
                $sql = "SELECT `nome` 
                        FROM `clientes_contatos` 
                        WHERE `id_cliente_contato` = '".$campos_follow_up[0]['id_cliente_contato']."' LIMIT 1 ";
                $campos_contato = bancos::sql($sql);
                echo $campos_contato[0]['nome'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Observação:</b>
            </font>
        </td>
        <td>
            <?=$campos_follow_up[0]['observacao'];?>
        </td>
    </tr>
<?
    }
    /**************************************************************************/
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Campos p/ Registrar Novo Follow-UP
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Contato(s) do Cliente:
        </td>
        <td>
            <select name='cmb_cliente_contato' title='Selecione os Contatos do Cliente' class='combo'>
            <?
/*Significa que foi incluido algum contato no Pop-Up de contatos, sendo assim, o sistema sugere esse contato na combo
assim que acaba de ser incluso*/
                if($controle == 1) {
//Aqui eu pego o ultimo contato que acabou de ser incluido ou alterado
                    $sql = "SELECT `id_cliente_contato` 
                            FROM `clientes_contatos` 
                            WHERE `id_cliente` = '$id_cliente' 
                            AND `ativo` = '1' ORDER BY `id_cliente_contato` DESC LIMIT 1 ";
                    $campos_contato     = bancos::sql($sql);
                    $id_cliente_contato = $campos_contato[0]['id_cliente_contato'];
                }
                $sql = "SELECT `id_cliente_contato`, CONCAT(`nome`, ' (', `departamento`, ')') AS dados_contato 
                        FROM `clientes_contatos` cc 
                        INNER JOIN `departamentos` d ON d.`id_departamento` = cc.`id_departamento` 
                        WHERE cc.`id_cliente` = '$id_cliente' 
                        AND cc.`ativo` = '1' ORDER BY dados_contato ";
/*Significa que foi incluido algum contato no Pop-Up de contatos, sendo assim, o sistema sugere esse contato na combo
assim que acaba de ser incluso*/
                if($controle == 1) {
                    echo combos::combo($sql, $id_cliente_contato);
                }else {
                    echo combos::combo($sql);
                }
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../imagem/menu/incluir.png' border='0' title='Incluir Contato' alt='Incluir Contato' onclick="nova_janela('../cliente/incluir_contatos.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;&nbsp; <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar Contato' alt='Alterar Contato' onclick='alterar_contato()'>
            &nbsp;&nbsp; <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Contato' alt='Excluir Contato' onclick='excluir_contato()'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Origem: 
        </td>
        <td>
            <?=$vetor_follow_ups[$origem];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b><?=$rotulo;?></b>
        </td>
        <td>
            <?=$numero_conta;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            E-mail para:
        </td>
        <td>
            <select name='cmb_email_para' title='Selecione o E-mail para' class='combo'>
            <?
                if(empty($campos_follow_up[0]['id_funcionario'])) {//Usuário está cadastrando um Novo Follow-UP do Zero ...
                    if(!empty($id_cliente)) {//Fará essa consulta abaixo se existir um "Cliente" é claro ...
                        //O id_pais será muito útil mais abaixo ...
                        $sql = "SELECT r.`id_representante`, r.`id_pais` 
                                FROM `clientes_vs_representantes` cr 
                                INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                                WHERE cr.`id_cliente` = '$id_cliente' LIMIT 1 ";
                        $campos_representante = bancos::sql($sql);
                        
                        /*Se existir país no cadastro desse Representante, então representa que o mesmo é externo, 
                        consequentemente busco o seu Supervisor ...*/
                        if(!is_null($campos_representante[0]['id_pais'])) {
                            /*Hoje 14/12/2016, essa logística abaixo já não precisaria mais estar sendo feita uma vez que já apresentamos até os 
                            Representantes Externos na combo p/ envio de e-mail, mais mesmo assim o Roberto "Diretor" quer que esse controle 
                            seja confiscado aqui internamente ...*/
                            
                            if($campos_representante[0]['id_representante'] == 120) {//Representante = Direto 2 ...
                                $id_representante   = 112;//Coloco automaticamente o "Nishimura" como sendo o Supervisor de Vendas ...
                            }else {
                                //Busca do Supervisor ...
                                $sql = "SELECT `id_representante_supervisor` AS id_representante 
                                        FROM `representantes_vs_supervisores` 
                                        WHERE `id_representante` = '".$campos_representante[0]['id_representante']."' LIMIT 1 ";
                                $campos_supervisor  = bancos::sql($sql);
                                $id_representante   = $campos_supervisor[0]['id_representante'];
                            }
                        }else {
                            $id_representante   = $campos_representante[0]['id_representante'];
                        }
                        /*Através desse $id_representante encontrado acima, eu busco o id_funcionario p/ ver se não é o mesmo que está logado 
                        no Sistema e o campo email para que esse apareça como sugestão no campo "email_para" na hora de se enviar o e-mail 
                        de Follow-UP ...*/
                        $sql = "SELECT rf.`id_funcionario`, IF(r.`email` = '', f.`email_externo`, r.`email`) AS email 
                                FROM `representantes_vs_funcionarios` rf 
                                INNER JOIN `representantes` r ON r.`id_representante` = rf.`id_representante` 
                                INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` 
                                WHERE rf.`id_representante` = '$id_representante' ";
                        $campos_funcionario = bancos::sql($sql);
                        /*Se o funcionário que foi encontrado acima for diferente do que funcionário que 
                        está logado no sistema, então esse me aparece como sugestão no campo "email_para", 
                        senão fica sem sentido o usuário logado mandar e-mail para ele mesmo ... ???*/
                        if($_SESSION['id_funcionario'] != $campos_funcionario[0]['id_funcionario']) {
                            $email_para = $campos_funcionario[0]['email'];
                        }
                    }
                }else {//Usuário está respondendo um Follow-UP ja éxistente ...
                    /*Se o funcionário que foi encontrado acima for diferente do que funcionário que 
                    está logado no sistema, então esse me aparece como sugestão no campo "email_para", 
                    senão fica sem sentido o usuário logado mandar e-mail para ele mesmo ... ???*/
                    if($_SESSION['id_funcionario'] != $campos_follow_up[0]['id_funcionario']) {
                        /*Essa combo "E-mail para" já virá com um Funcionário sugerido caso seja uma visualização de 
                        Follow-UP já registrado pois significa que estarei respondendo p/ o "autor" dessa ocorrência que 
                        foi registrada anteriormente ...*/
                        $sql = "SELECT `email_externo` 
                                FROM `funcionarios` 
                                WHERE `id_funcionario` = '".$campos_follow_up[0]['id_funcionario']."' LIMIT 1 ";
                        $campos_funcionario = bancos::sql($sql);
                        $email_para         = $campos_funcionario[0]['email_externo'];
                    }
                }
            
                //SQL 1) Listagem de todos os Funcionários que possuem E-mail Interno e que trabalham na Empresa
                //SQL 2) Listagem de todos os Representantes ...
                $sql = "(SELECT `email_externo`, `nome` 
                        FROM `funcionarios` 
                        WHERE `email_externo` <> '' 
                        AND `status` < '3') 
                        UNION 
                        (SELECT `email`, CONCAT(UPPER(SUBSTR(`nome_fantasia`, 1, 1)), LOWER(SUBSTR(`nome_fantasia`, 2, LENGTH(`nome_fantasia`)))) AS nome 
                        FROM `representantes` 
                        WHERE `email` <> '' 
                        AND `ativo` = '1') 
                        ORDER BY `nome` ";
                echo combos::combo($sql, $email_para, 'S');
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <div name='div_com_copia' id='div_com_copia'></div>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação:</b>
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a Observação' maxlength='500' cols='84' rows='6' class='caixadetexto'><?=$_GET['txt_observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_exibir_no_pdf' id='chkt_exibir_no_pdf' value='S' title='Exibir no PDF' class='checkbox'>
            <label for='chkt_exibir_no_pdf'>
                Exibir no PDF
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_observacao.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_incluir' value='Incluir' title='Incluir' onclick="document.form.passo.value=''" style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>