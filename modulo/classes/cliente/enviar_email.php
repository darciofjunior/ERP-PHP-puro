<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/faturamentos.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
require('../array_sistema/array_sistema.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>E-MAIL ENVIADO COM SUCESSO.</font>";

//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
$vetor = array_sistema::follow_ups();

/*Aqui é aonde eu disparo o E-mail p/ o usuário debatendo ele referente ao Follow-Up que 
ele registrou ...*/
if(!empty($_POST['txt_observacao_acompanhamento'])) {
/************************************************************************************/
//Verifico se a Sessão não caiu ...
    if (!(session_is_registered('id_funcionario'))) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../html/index.php?valor=1'
    </Script>
<?
        exit;
    }
/**************Busca do E-mail da pessoa que irá enviar e receber o E-mail**************/
//Aqui eu busco o login de quem vai enviar o e-mail ...
    $sql = "SELECT email_externo 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_email   = bancos::sql($sql);
    $remetente      = $campos_email[0]['email_externo'];
	
//Aqui busco o e-mail da pessoa que irá receber o e-mail ...
    $sql = "SELECT email_externo 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_POST[cmb_funcionario]' LIMIT 1 ";
    $campos_email   = bancos::sql($sql);
    $destino        = $campos_email[0]['email_externo'];
/***************************************************************************************/
/****Busca de dados do Follow-UP selecionado p/ poder registrar e passar por e-mail*****/
//Busca dos dados do Follow-Up selecionado ...
    $sql = "SELECT fu.*, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente 
            FROM `follow_ups` fu 
            INNER JOIN `clientes` c ON c.`id_cliente` = fu.`id_cliente` 
            WHERE fu.`id_follow_up` = '$_POST[id_follow_up]' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $id_cliente                 = $campos[0]['id_cliente'];
    $id_cliente_contato         = $campos[0]['id_cliente_contato'];
    $id_funcionario_follow_up   = $campos[0]['id_funcionario'];
    $cliente                    = $campos[0]['cliente'];
//No caso o id do Cliente é a identificação a ser gravada ...
    $identificacao              = $campos[0]['id_cliente'];
    $origem                     = $vetor[$campos[0]['origem']];
    $data_ocorrencia            = data::datetodata($campos[0]['data_sys'], '/').' - '.substr($campos[0]['data_sys'], 11, 8);
	
    if($campos[0]['origem'] == 1) {//Tela de Orçamentos
        $sql = "SELECT id_orcamento_venda 
                FROM `orcamentos_vendas` 
                WHERE `id_orcamento_venda` = '".$campos[0]['identificacao']."' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $numero_conta   = $campos[0]['id_orcamento_venda'];
    }else if($campos[0]['origem'] == 2) {//Tela de Pedidos
        $sql = "SELECT id_pedido_venda 
                FROM `pedidos_vendas` 
                WHERE `id_pedido_venda` = '".$campos[0]['identificacao']."' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $numero_conta   = $campos[0]['id_pedido_venda'];
    }else if($campos[0]['origem'] == 3) {//Tela de Gerenciar Estoque
        //echo 'Cliente';
    }else if($campos[0]['origem'] == 4) {//Contas à Receber
        $sql = "SELECT num_conta 
                FROM `contas_receberes` 
                WHERE `id_conta_receber` = '".$campos[0]['identificacao']."' LIMIT 1 ";
        $campos_numero  = bancos::sql($sql);
        $numero_conta   = $campos_numero[0]['num_conta'];
    }else if($campos[0]['origem'] == 5) {//Nota Fiscal
        $sql = "SELECT id_nf_num_nota 
                FROM `nfs` 
                WHERE `id_nf` = '".$campos[0]['identificacao']."' LIMIT 1 ";
        $campos_numero  = bancos::sql($sql);
        $numero_conta   = faturamentos::buscar_numero_nf($campos[0]['identificacao'], 'S');
    }else if($campos[0]['origem'] == 6) {//APV
//Significa que um Follow-Up que está sendo registrado pela parte de Vendas (Antigo Sac)
        if($campos[0]['modo_venda'] == 1) {
            //echo 'FONE';
        }else {
            //echo 'VISITA';
        }
    }else if($campos[0]['origem'] == 7) {//Atend. Interno
        //echo 'Atend. Interno';
    }else if($campos[0]['origem'] == 8) {//Depto. Técnico
        //echo 'Depto. Técnico';
    }else if($campos[0]['origem'] == 9) {//Pendências
        //echo 'Pendências';
    }else if($campos[0]['origem'] == 10) {//TeleMarketing
        //echo 'TeleMkt';
    }else if($campos[0]['origem'] == 11) {//Acompanhamento
        //echo 'Acompanhamento';
    }else {
        $campos[0]['identificacao'];
    }
//Aqui busca o Login na Tabela Relacional
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_funcionario` = '$id_funcionario_follow_up' LIMIT 1 ";
    $campos_login   = bancos::sql($sql);
    $login          = $campos_login[0]['login'];
	
/*Se essa opção estiver marcada, então eu também Registro esse Follow-UP do e-mail que está 
sendo guardado ...*/
    if(!empty($_POST['registrar_follow_up_deste_email'])) {
        if(empty($id_cliente_contato)) {
            $id_cliente_contato = 'NULL';
            $id_representante   = 'NULL';
        }else {
            $id_representante = genericas::buscar_id_representante($id_cliente_contato);
        }
//Registrando Follow-UP(s) ...

/*Passo a origem = 11 porque estou registrando como se fosse Atendimento Interno ...
F - Follow-UP, 1 - Intermodular, 1 - Fone, C de Concluído - Status_ocorrencia*/
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_cliente_contato, $id_representante, '$_SESSION[id_funcionario]', '$identificacao', '11', '$_POST[txt_observacao_acompanhamento]', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
    
//Aqui busca o Contato na Tabela Relacional
    $sql = "SELECT nome 
            FROM `clientes_contatos` 
            WHERE `id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
    $campos_contato     = bancos::sql($sql);
    $contato 		= $campos_contato[0]['nome'];
    $observacao 	= $campos[0]['observacao'];
    
//Concateno os dados p/ enviar por e-mail junto da Observação de Acompanhamento do Usuário ...
    $corpo_email = '<b>Cliente: </b>'.$cliente.'<br><b>Origem: </b>'.$origem.'<br><b>N.º: </b>'.$numero_conta.'<br><b>Login: </b>'.$login.'<br><b>Ocorrência: </b>'.$data_ocorrencia.'<br><b>Contato: </b>'.$contato.'<br><b>Observação: </b>'.$observacao.'<br><br><font color="darkblue"><b>Observação de Acompanhamento: </b></font>'.$_POST['txt_observacao_acompanhamento'];
    $assunto = 'Acompanhamento de Cliente '.$cliente;
	
    if(!empty($_POST['cmb_funcionario_copia']) && !empty($_POST['txt_outras_copias'])) {
        $sql = "SELECT email_externo 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_POST[cmb_funcionario_copia]' LIMIT 1 ";
        $campos_email   = bancos::sql($sql);
        $destino_copia  = $campos_email[0]['email_externo'].', '.$_POST['txt_outras_copias'];
    }else if(!empty($_POST['cmb_funcionario_copia']) && empty($_POST['txt_outras_copias'])) {
        $sql = "SELECT email_externo 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_POST[cmb_funcionario_copia]' LIMIT 1 ";
        $campos_email   = bancos::sql($sql);
        $destino_copia  = $campos_email[0]['email_externo'];
    }else if(empty($_POST['cmb_funcionario_copia']) && !empty($_POST['txt_outras_copias'])) {
        $destino_copia = $_POST['txt_outras_copias'];
    }
    comunicacao::email($remetente, $destino, $destino_copia, $assunto, $corpo_email);
    $valor = 1;
}

//Procedimento normal de quando se carrega a Tela ...
$id_follow_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_follow_up'] : $_GET['id_follow_up'];

//Aqui busca dados do Follow_up Corrente ...
$sql = "SELECT * 
        FROM `follow_ups` fu 
        WHERE `id_follow_up` = '$id_follow_up' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Enviar por E-mail ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Funcionário p/ quem irá enviar o e-mail ...
    if(!combo('form', 'cmb_funcionario', '', 'SELECIONE O FUNCIONÁRIO P/ QUEM IRÁ ENVIAR O E-MAIL !')) {
        return false
    }
//Com cópia ...
    if(document.form.txt_outras_copias.value != '') {
//Aqui eu verifico se já foi preenchido os Arrobas no campo com Cópia ...
        var email_com_copia = document.form.txt_outras_copias.value
        var existe_arroba = 0
        for(i = 0; i < email_com_copia.length; i++) {
            if(email_com_copia.charAt(i) == '@') {
                existe_arroba = 1
                break//P/ sair fora do Loop ...
            }
        }
//Caso o usuário não tenha clicado no Botão de Acrescentar @, então lembro o mesmo de clicar ...
        if(existe_arroba == 0) {
            alert('CLIQUE NO BOTÃO PARA ACRESCENTAR @, POIS ESTE NÃO É UM FORMATO VÁLIDO DE E-MAIL !!!')
            document.form.cmd_adicionar_arroba.focus()
            return false
        }
    }
//Observação
    if(document.form.txt_observacao_acompanhamento.value == '') {
        alert('DIGITE A OBSERVAÇÃO !')
        document.form.txt_observacao_acompanhamento.focus()
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

function atualizar() {
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}

function arroba_grupo_albafer() {
    if(document.form.txt_outras_copias.value == '') {
        alert('DIGITE UM E-MAIL COM CÓPIA !')
        document.form.txt_outras_copias.focus()
        return false
    }else {
        var nova_lista      = ''//Será utilizado mais abaixo
        var existe_virgula  = 0
        
        //Verifico se o usuário está usando , ou ; como separador de e-mail entre os usuários ...
        for(i = 0; i < document.form.txt_outras_copias.value.length; i++) {
            if(document.form.txt_outras_copias.value.charAt(i) == ',') {
                existe_virgula = 1
                break//P/ sair fora do Loop ...
            }
        }

//Transforma em vetor, todos os e-mails que existem no Textarea ...
        if(existe_virgula == 1) {//Significa que o usuário utiliza "," como separador entre e-mails 
            var vetor_emails_com_virgula = document.form.txt_outras_copias.value.split(',')
            for(i = 0; i < vetor_emails_com_virgula.length; i++) {
                if(vetor_emails_com_virgula[i] != ' ') nova_lista+= vetor_emails_com_virgula[i]+ '@grupoalbafer.com.br, '
            }
            nova_lista = nova_lista.substr(0, nova_lista.length - 2)
        }else {//Significa que o usuário utiliza ";" como separador entre e-mails 
            var vetor_emails_com_ponto_virgula = document.form.txt_outras_copias.value.split(';')
            for(i = 0; i < vetor_emails_com_ponto_virgula.length; i++) {
                if(vetor_emails_com_ponto_virgula[i] != ' ') nova_lista+= vetor_emails_com_ponto_virgula[i]+ '@grupoalbafer.com.br; '
            }
            nova_lista = nova_lista.substr(0, nova_lista.length - 2)
        }
        document.form.txt_outras_copias.value = nova_lista
    }
}

function controlar_caracteres(event) {
/*Independente do Navegador, ignoro a verificação se foi digitado espaço, shift, home, end, 
vírgula e as setas de Movimentação do Teclado ...*/
    if(navigator.appName == 'Microsoft Internet Explorer') {
//Só travo o Arroba ...		
        if(event.keyCode == 50) {
            alert('NÃO DIGITE @ NESSE CAMPO, CARACTÉR INVÁLIDO !')
            document.form.txt_outras_copias.value = document.form.txt_outras_copias.value.substr(0, document.form.txt_outras_copias.value.length - 1)
            return false
        }
        if(event.keyCode == 16 || event.keyCode == 32 || event.keyCode == 35 || event.keyCode == 36 || event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40 || event.keyCode == 188) {
            return false
        }
    }else {//Se estiver acessando de outro Navegador, Firefox, Mozilla, ...
//Só travo o Arroba ...		
        if(event.which == 50) {
            alert('NÃO DIGITE @ NESSE CAMPO, CARACTÉR INVÁLIDO !')
            document.form.txt_outras_copias.value = document.form.txt_outras_copias.value.substr(0, document.form.txt_outras_copias.value.length - 1)
            return false
        }
        if(event.which == 16 || event.which == 32 || event.which == 35 || event.which == 36 || event.which == 37 || event.which == 38 || event.which == 39 || event.which == 40 || event.which == 188) {
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_outras_copias.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_follow_up' value='<?=$id_follow_up;?>'>
<!--Controle dos Pop-Ups de Contato-->
<input type='hidden' name='passo' onclick='atualizar()'>
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Enviar por E-mail
        </td>
    </tr>
    <?
        /**********************************************************************/
        if($campos[0]['id_cliente'] > 0) {
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
                    WHERE `id_cliente` = '".$campos[0]['id_cliente']."' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            echo $campos_cliente[0]['cliente'];
        ?>
        </td>
    </tr>
    <?
        }else if($campos[0]['id_fornecedor'] > 0) {
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
                    WHERE `id_fornecedor` = '".$campos[0]['id_fornecedor']."' LIMIT 1 ";
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
            <?=$vetor[$campos[0]['origem']];?>
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
            if($campos[0]['origem'] == 3) {//Tela de Gerenciar Estoque
                //echo 'Cliente';
            }else if($campos[0]['origem'] == 4) {//Contas à Receber
                $sql = "SELECT num_conta 
                        FROM `contas_receberes` 
                        WHERE `id_conta_receber` = '".$campos[0]['identificacao']."' LIMIT 1 ";
                $campos_numero = bancos::sql($sql);
                echo $campos_numero[0]['num_conta'];
            }else if($campos[0]['origem'] == 5) {//Nota Fiscal
                $sql = "SELECT id_nf_num_nota 
                        FROM `nfs` 
                        WHERE `id_nf` = '".$campos[0]['identificacao']."' LIMIT 1 ";
                $campos_numero = bancos::sql($sql);
                echo faturamentos::buscar_numero_nf($campos[0]['identificacao'], 'S');
            }else if($campos[0]['origem'] == 6) {//APV
//Significa que um Follow-Up que está sendo registrado pela parte de Vendas (Antigo Sac)
                if($campos[0]['modo_venda'] == 1) {
                    echo 'FONE';
                }else {
                    echo 'VISITA';
                }
            }else if($campos[0]['origem'] == 7) {//Atend. Interno
                //echo 'Atend. Interno';
            }else if($campos[0]['origem'] == 8) {//Depto. Técnico
                //echo 'Depto. Técnico';
            }else if($campos[0]['origem'] == 9) {//Pendências
                //echo 'Pendências';
            }else if($campos[0]['origem'] == 10) {//TeleMarketing
                //echo 'TeleMkt';
            }else if($campos[0]['origem'] == 11) {//Acompanhamento
                //echo 'Acompanhamento';
            }else {
                echo $campos[0]['identificacao'];
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
            if($campos[0]['id_funcionario'] > 0) {//Aqui se existir, eu busco o Login na Tabela Relacional ...
                $sql = "SELECT `login` 
                        FROM `logins` 
                        WHERE `id_funcionario` = ".$campos[0]['id_funcionario']." LIMIT 1 ";
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
            <?=data::datetodata($campos[0]['data_sys'], '/').' - '.substr($campos[0]['data_sys'], 11, 8);?>
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
            if(!empty($campos[0]['id_cliente_contato'])) {
                //Aqui busca o Contato na Tabela Relacional ...
                $sql = "SELECT `nome` 
                        FROM `clientes_contatos` 
                        WHERE `id_cliente_contato` = '".$campos[0]['id_cliente_contato']."' LIMIT 1 ";
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
            <?=$campos[0]['observacao'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>E-mail para: </b>
        </td>
        <?
//Aqui eu busco o id_representante do Cliente ...
            $sql = "SELECT DISTINCT(id_representante) 
                    FROM `clientes_vs_representantes` 
                    WHERE `id_cliente` = '".$campos[0]['id_cliente']."' LIMIT 1 ";
            $campos_rep = bancos::sql($sql);
            if(count($campos_rep[0]['id_representante']) == 1) {
                $id_representante = $campos_rep[0]['id_representante'];
/*Aqui eu verifico se o Representante possui um supervisor, pois caso exista, este é quem será 
sugestão p/ aparecer na combo ...*/
                $sql = "SELECT id_representante_supervisor 
                        FROM `representantes_vs_supervisores` 
                        WHERE `id_representante` = '".$campos_rep[0]['id_representante']."' LIMIT 1 ";
                $campos_rep_sup = bancos::sql($sql);
                if(count($campos_rep_sup) == 1) $id_representante_superior = $campos_rep_sup[0]['id_representante_supervisor'];
            }
//Se existe o Representante Comum e o Representante Superior, então eu apresento os 2 como sugestão na combo ...
            if(!empty($id_representante) && !empty($id_representante_superior)) {
//Representante Superior ...
                $sql = "SELECT id_funcionario 
                        FROM `representantes_vs_funcionarios` 
                        WHERE `id_representante` = '$id_representante_superior' LIMIT 1 ";
                $campos_func_sugestao       = bancos::sql($sql);
                $id_func_sugestao_superior  = $campos_func_sugestao[0]['id_funcionario'];
//Representante Comum ...
                $sql = "SELECT id_funcionario 
                        FROM `representantes_vs_funcionarios` 
                        WHERE `id_representante` = '$id_representante' LIMIT 1 ";
                $campos_func_sugestao   = bancos::sql($sql);
                $id_func_sugestao       = $campos_func_sugestao[0]['id_funcionario'];
//Só existe o Representante Comum ...
            }else {
                $sql = "SELECT id_funcionario 
                        FROM `representantes_vs_funcionarios` 
                        WHERE `id_representante` = '$id_representante' LIMIT 1 ";
                $campos_func_sugestao   = bancos::sql($sql);
                $id_func_sugestao       = $campos_func_sugestao[0]['id_funcionario'];
            }
        ?>
        <td>
            <select name='cmb_funcionario' title='Selecione o Funcionário' class='combo'>
            <?
                //Listagem de todos os Funcionários que possuem E-mail Interno e que trabalham na Empresa ...
                $sql = "SELECT id_funcionario, nome 
                        FROM `funcionarios` 
                        WHERE `email_externo` <> '' 
                        AND `status` < '3' ORDER BY nome ";
                echo combos::combo($sql, $id_func_sugestao_superior);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            E-mail com Cópia:
        </td>
        <td>
            <select name='cmb_funcionario_copia' title='Selecione o Funcionário Cópia' class='combo'>
            <?
                //Listagem de todos os Funcionários que possuem E-mail Interno e que trabalham na Empresa ...
                $sql = "SELECT id_funcionario, nome 
                        FROM `funcionarios` 
                        WHERE `email_externo` <> '' 
                        AND `status` < '3' ORDER BY nome ";
                echo combos::combo($sql, $id_func_sugestao);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Outras Cópia(s):
        </td>
        <td>
            <textarea name='txt_outras_copias' title="Digite o(s) E-mail(s) com Outra(s) Cópia(s)" cols='60' rows='3' onkeyup='controlar_caracteres(event)' class='caixadetexto'></textarea>
            &nbsp;-&nbsp;
            <input type='button' name='cmd_adicionar_arroba' value='Acrescentar @grupoalbafer.com.br' title='Acrescentar @grupoalbafer.com.br' onclick='arroba_grupo_albafer()' style='color:black' class='botao'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação de Acompanhamento:</b>
        </td>
        <td>
            <textarea name='txt_observacao_acompanhamento' title='Digite a Observação de Acompanhamento' maxlength='255' cols='85' rows='3' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='registrar_follow_up_deste_email' value='1' title='Registrar Follow-UP deste E-mail' id='registrar_follow_up_deste_email' checked>
            <label for='registrar_follow_up_deste_email'>Registrar Follow-UP deste E-mail</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_outras_copias.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_enviar' value='Enviar' title='Enviar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>