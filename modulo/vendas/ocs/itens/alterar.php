<?
require('../../../../lib/segurancas.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/ocs/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>ITEM / FOLLOW-UP REGISTRADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>FOLLOW-UP COM CONTE�DO J� EXISTENTE.</font>";
$mensagem[3] = "<font class='erro'>J� EXISTE UMA MANIPULA��O P/ ESTOQUE NESTE ITEM DE OC.</font>";
$mensagem[4] = "<font class='confirmacao'>FOLLOW-UP EXCLU�DO COM SUCESSO.</font>";
$mensagem[5] = "<font class='confirmacao'>TODO(S) O(S) ITEM(NS) DESTA OC FOI(RAM) ATUALIZADO(S) COM SUCESSO.</font>";
$mensagem[6] = "<font class='confirmacao'>ITEM DE OC ALTERADO COM SUCESSO.</font>";

if($_SESSION[id_funcionario] == 10) {
    $vetor_status = array(1 => 'AVALIADO PELO CONTROLE DE QUALIDADE', 7 => 'ENVIADO P/ ESTOQUE (N�O ALTERA A QTDE ESTOQUE)', 10 => 'DESDOBRAR QUANTIDADE', 11 => 'ACOMPANHAMENTO INTERNO');
}else {
    $vetor_status = array(1 => 'AVALIADO PELO CONTROLE DE QUALIDADE', 2 => 'AVALIADO PELO SUPERVISOR', 3 => 'ENVIADO PARA PROCESSO INTERNO', 
    4 => 'ENVIADO P/ T�CNICO - PARA ESCLARECIMENTO DE PROBLEMA', 5 => 'ENVIADO P/ T�CNICO - PARA OR�AMENTO', 
    6 => 'OR�AMENTO ENVIADO P/ CLIENTE - AGUARDANDO APROVA��O', 7 => 'ENVIADO P/ ESTOQUE (N�O ALTERA A QTDE ESTOQUE)', 8 => 'MANIPULA��O P/ ESTOQUE', 
    9 => 'ENVIADO P/ CLIENTE / REPRESENTANTE', 10 => 'DESDOBRAR QUANTIDADE', 11 => 'ACOMPANHAMENTO INTERNO');
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_oc_item = $_POST['id_oc_item'];
    $posicao	= $_POST['posicao'];
}else {
    $id_oc_item = $_GET['id_oc_item'];
    $posicao	= $_GET['posicao'];
}
if(empty($posicao)) $posicao = 1;//Macete por causa da paginacao do Pop-UP ...

if(!empty($_POST['id_oc_item'])) {
    $cliente_vai_devolver_peca = (!empty($_POST['chkt_cliente_vai_devolver_peca'])) ? 'S' : 'N';
    
    $sql = "UPDATE `ocs_itens` SET `cliente_vai_devolver_peca` = '$cliente_vai_devolver_peca' WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 6;
}
/***************************************CONTROLE DE QUALIDADE****************************************/
if(!empty($_POST['cmb_sugestao_solucao_cq'])) {
    $sql = "UPDATE `ocs_itens` SET `sugestao_cq` = '$_POST[cmb_sugestao_solucao_cq]' WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
    bancos::sql($sql);
}
/*********************************************SUPERVISOR*********************************************/
if(!empty($_POST['cmb_sugestao_solucao_supervisor'])) {
    $sql = "UPDATE `ocs_itens` SET `sugestao_supervisor` = '$_POST[cmb_sugestao_solucao_supervisor]' WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
    bancos::sql($sql);
}
/*********************************************FOLLOW-UP**********************************************/
if(!empty($_POST['cmb_status'])) {
    $registrar_follow_up = 1;//Significa que eu desejo que sempre me registre o Follow-UP ...
    /*Aqui eu verifico se j� foi registrado pelo funcion�rio logado um mesmo conte�do de Follow-Up, para o mesmo Status 
    e na mesma Data e no mesmo Item, caso n�o ...*/
    $sql = "SELECT id_oc_item_follow_up 
            FROM `ocs_itens_follow_ups` 
            WHERE `id_oc_item` = '$_POST[id_oc_item]' 
            AND `id_funcionario` = '$_SESSION[id_funcionario]' 
            AND `status` = '$_POST[cmb_status]' 
            AND `observacao` = '$_POST[txt_observacao]' 
            AND SUBSTRING(`data_sys`, 1, 10) = '".date('Y-m-d')."' LIMIT 1 ";
    $campos_oci_follow_up = bancos::sql($sql);
    if(count($campos_oci_follow_up) == 0) {//Esse conte�do ainda n�o foi registrado para o Func nessa Data ... 
        //Nessas 2 op��es abaixo, � necess�rio concatenar algumas informa��es a mais ...
        if($_POST['cmb_status'] == 3) {//Enviado p/ Processo Interno ...
            $observacao = $_POST['txt_observacao'].', Enviado p/ Processo Interno: '.$_POST['hdd_enviado'];
        }else if($_POST['cmb_status'] == 7) {//Enviado p/ Estoque ...
            $observacao = $_POST['txt_observacao'].', Enviado p/ Estoque: '.$_POST['hdd_enviado'];
            if(!empty($_POST['hdd_fornecedor'])) {//Quando existir Fornecedor envia e-mail ...
                $observacao.= ', P/ o Fornecedor: '.$_POST['hdd_fornecedor'];
                /*******************************************************************************************************/
                /*************************************************EMAIL*************************************************/
                /*******************************************************************************************************/
                //Nessa inst�ncia � enviado um e-mail para o Depto. de Compras para que eles j� possam fazer Cota��o da Pe�a de OC ...
                //Busca de alguns Dados p/ enviar e-mail ...
                $sql = "SELECT oi.qtde, oi.defeito_alegado, pa.referencia, pa.discriminacao, u.sigla 
                                FROM `ocs_itens` oi 
                                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = oi.id_produto_acabado 
                                INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                                WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
                $campos_ocs = bancos::sql($sql);
                $assunto 		= 'ENVIAR PE�A DE OC PARA FORNECEDOR';
                $conteudo_email.= '<table width="880" border="0" cellspacing="1" cellpadding="0" align="center">';
                //Linha Principal ...
                $conteudo_email.= '<tr class="linhacabecalho" align="center">';
                $conteudo_email.= '<td colspan="5" bgcolor="#BEBEBE"><font color="#00008B" face="arial black">ITEM DA OC N.� '.$_POST['id_oc'].' - FORNECEDOR "'.$_POST['hdd_fornecedor'].'"</font></td>';
                $conteudo_email.= '</tr>';
                //Linha R�tulos ...
                $conteudo_email.= '<tr class="linhadestaque" align="center">';
                $conteudo_email.= '<td bgcolor="#E8E8E8"><font face="courier new">QTDE</font></td>';
                $conteudo_email.= '<td bgcolor="#E8E8E8"><font face="courier new">UNIDADE</font></td>';
                $conteudo_email.= '<td bgcolor="#E8E8E8"><font face="courier new">REFER�NCIA</font></td>';
                $conteudo_email.= '<td bgcolor="#E8E8E8"><font face="courier new">DISCRIMINA��O</font></td>';
                $conteudo_email.= '<td bgcolor="#E8E8E8"><font face="courier new">DEFEITO</font></td>';
                $conteudo_email.= '</tr>';
                //Linha de Dados ...
                $conteudo_email.= '<tr class="linhanormal" align="center">';
                $conteudo_email.= '<td bgcolor="#FFFFE0"><font color="brown" face="courier new">'.$campos_ocs[0]['qtde'].'</font></td>';
                $conteudo_email.= '<td bgcolor="#FFFFE0"><font color="brown" face="courier new">'.$campos_ocs[0]['sigla'].'</font></td>';
                $conteudo_email.= '<td bgcolor="#FFFFE0" align="left"><font color="brown" face="courier new">'.$campos_ocs[0]['referencia'].'</font></td>';
                $conteudo_email.= '<td bgcolor="#FFFFE0" align="right"><font color="brown" face="courier new">'.$campos_ocs[0]['discriminacao'].'</font></td>';
                $conteudo_email.= '<td bgcolor="#FFFFE0" align="right"><font color="brown" face="courier new">'.$campos_ocs[0]['defeito_alegado'].'</font></td>';
                $conteudo_email.= '</tr>';
                //Linha Principal ...
                $conteudo_email.= '<tr class="linhacabecalho" align="center">';
                $conteudo_email.= '<td colspan="5" bgcolor="#BEBEBE"><font color="#00008B" face="arial black">&nbsp;</font></td>';
                $conteudo_email.= '</tr>';
                $conteudo_email.= '</table><br>';
                comunicacao::email('ERP ALBAFER', 'gcompras@grupoalbafer.com.br', '', $assunto, $conteudo_email);
                /*******************************************************************************************************/
            }
        }else if($_POST['cmb_status'] == 8) {//Manipula��o p/ Estoque ...
            //Verifico se j� foi feita alguma Manipula��o para este item ...
            $sql = "SELECT id_oc_item_follow_up 
                    FROM `ocs_itens_follow_ups` 
                    WHERE `id_oc_item` = '$_POST[id_oc_item]' 
                    AND `status` = '8' LIMIT 1 ";
            $campos_manipulacao	= bancos::sql($sql);
            if(count($campos_manipulacao) == 0) {//N�o encontrou registro, posso manipular normalmente ...
                //Busca de alguns Dados p/ enviar e-mail ...
                $sql = "SELECT ocs.id_oc, ocs.id_cliente, oi.id_produto_acabado, oi.qtde 
                        FROM `ocs_itens` oi 
                        INNER JOIN `ocs` ON ocs.id_oc = oi.id_oc 
                        WHERE oi.`id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
                $campos_ocs 	= bancos::sql($sql);
                $resultado      = estoque_acabado::verificar_manipulacao_estoque($campos_ocs[0]['id_produto_acabado'], $campos_ocs[0]['qtde']);
                if($resultado['retorno'] == 'executar') {
                    /*Tenho que chamar essa fun��o para Setar o P.A., para o Pa�oquinha saber 
                    que ele poder liberar os Pedidos ...*/
                    estoque_acabado::seta_nova_entrada_pa_op_compras($campos_ocs[0]['id_produto_acabado']);
                    //Procedimento normal para registro da Entrada, sempre manipula a Qtde no Sinal inverso ...
                    $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `id_cliente`, `numero_oc`, `qtde`, `observacao`, `acao`, `data_sys`) VALUES (NULL, '".$campos_ocs[0]['id_produto_acabado']."', '$_SESSION[id_funcionario]', '$_SESSION[id_funcionario]', '".$campos_ocs[0]['id_cliente']."', '".$campos_ocs[0]['id_oc']."', '".-$campos_ocs[0]['qtde']."', '$_POST[txt_observacao]', 'O', '".date('Y-m-d H:i:s')."') ";
                    bancos::sql($sql);
                    sleep(2);
                    estoque_acabado::atualizar($campos_ocs[0]['id_produto_acabado']);//Atualiza Real ...
                    estoque_acabado::controle_estoque_pa($campos_ocs[0]['id_produto_acabado']);//Dispon�vel ...
                    estoque_acabado::atualizar_producao($campos_ocs[0]['id_produto_acabado']);//Produ��o ...
                }
            }else {//J� encontrou um Registro ...
                $registrar_follow_up = 0;
            }
        }else if($_POST['cmb_status'] == 9) {//Enviado p/ Cliente ou Representante ...	
            if($_POST['hdd_marcar_registro_todos_itens'] == 1) {//Significa que � p/ colocar o mesmo Registro p/ todos os Itens ...
                //Aqui eu busco quais s�o os Itens da OC ...
                $sql = "SELECT id_oc_item 
                        FROM `ocs_itens` 
                        WHERE `id_oc` = '$_POST[id_oc]' ";
                $campos_ocs_itens = bancos::sql($sql);
                $linhas_ocs_itens = count($campos_ocs_itens);
                for($i = 0; $i < $linhas_ocs_itens; $i++) {
                    //Registra o Follow-Up do Item da OC ...
                    $sql = "INSERT INTO `ocs_itens_follow_ups` (`id_oc_item_follow_up`, `id_oc_item`, `id_funcionario`, `status`, `observacao`, `data_sys`) VALUES (NULL, '".$campos_ocs_itens[$i]['id_oc_item']."', '$_SESSION[id_funcionario]', '9', '$_POST[txt_observacao]', '".date('Y-m-d H:i:s')."') ";
                    bancos::sql($sql);
                    //Atualizo o Status do Item da OC ...
                    $sql = "UPDATE `ocs_itens` SET `status` = '9' WHERE `id_oc_item` = '".$campos_ocs_itens[$i]['id_oc_item']."' LIMIT 1 ";
                    bancos::sql($sql);
                }
                $registrar_follow_up = 0;//Coloco isso para o Sys n�o registrar um Follow-UP que est� abaixo de todos ifs e elses de status
            }
        }else if($_POST['cmb_status'] == 10) {//Desdobrar Quantidade ...
            //Aqui ser� gerado um novo Item com a Diferen�a da Qtde que foi digitada pelo usu�rio ...
            //Busca de alguns Dados p/ criar Item ...
            $sql = "SELECT id_oc, id_produto_acabado, qtde, defeito_alegado 
                    FROM `ocs_itens` 
                    WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
            $campos_ocs 	= bancos::sql($sql);
            $qtde_item_novo = ($campos_ocs[0]['qtde'] - $_POST['hdd_qtde_para_este_item']);
            //Inserindo um Novo Item com a Diferen�a da Qtde Digitada pelo usu�rio ...
            $sql = "INSERT INTO `ocs_itens` (`id_oc_item`, `id_oc`, `id_produto_acabado`, `qtde`, `defeito_alegado`) VALUES (NULL, '".$campos_ocs[0]['id_oc']."', '".$campos_ocs[0]['id_produto_acabado']."', '$qtde_item_novo', '".$campos_ocs[0]['defeito_alegado']."') ";
            bancos::sql($sql);
            //O Item Atual passa a assumir a Qtde que o usu�rio realmente desejou manter ...
            $sql = "UPDATE `ocs_itens` SET `qtde` = '$_POST[hdd_qtde_para_este_item]' WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Qualquer outra op��o, n�o � necess�rio concatentar nenhum dado a mais na Observa��o ...
        if(!isset($observacao)) $observacao = $_POST['txt_observacao'];

        if($registrar_follow_up == 1) {
            //Registra o Follow-Up do Item da OC ...
            $sql = "INSERT INTO `ocs_itens_follow_ups` (`id_oc_item_follow_up`, `id_oc_item`, `id_funcionario`, `status`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_oc_item]', '$_SESSION[id_funcionario]', '$_POST[cmb_status]', '$observacao', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
            if($_POST['cmb_status'] != 11) {//S� n�o altera o Status do Item quando for a op��o "ACOMPANHAMENTO INTERNO"
                //Atualizo o Status do Item da OC ...
                $sql = "UPDATE `ocs_itens` SET `status` = '$_POST[cmb_status]' WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
                bancos::sql($sql);
            }
            $valor = 1;
        }else {
            //Significa que � p/ colocar o mesmo Registro p/ todos os Itens, isso j� foi feito mais acima ...
            $valor = ($_POST['hdd_marcar_registro_todos_itens'] == 1) ? 5 : 3;
        }
    }else {
        $valor = 2;
    }
}

/****************************************************************************************************/
//Excluo o Follow-Up que est� vinculado ao item da OC ...
if(!empty($_GET['id_oc_item_follow_up'])) {
    $sql = "DELETE FROM `ocs_itens_follow_ups` WHERE `id_oc_item_follow_up` = '$_GET[id_oc_item_follow_up]' LIMIT 1 ";
    bancos::sql($sql);
    /*Aqui eu busco o �ltimo Follow-UP registrado do Item para atualizar o Status do Item da OC, 
    desde que n�o seja "ACOMPANHAMENTO INTERNO" ...*/
    $sql = "SELECT `status` 
            FROM `ocs_itens_follow_ups` 
            WHERE `id_oc_item` = '$_GET[id_oc_item]' 
            AND `status` <> '11' ORDER BY `data_sys` DESC LIMIT 1 ";
    $campos_status_follow_up = bancos::sql($sql);
    if(count($campos_status_follow_up) == 1) {//Encontrou registro ...
        $sql = "UPDATE `ocs_itens` SET `status` = '".$campos_status_follow_up[0]['status']."' WHERE `id_oc_item` = '$_GET[id_oc_item]' LIMIT 1 ";
    }else {//N�o encontrou ent�o volta a situa��o deste para em Aberto ...
        $sql = "UPDATE `ocs_itens` SET `status` = '0' WHERE `id_oc_item` = '$_GET[id_oc_item]' LIMIT 1 ";
    }
    bancos::sql($sql);
    $valor = 4;
}

/***************************Procedimento Normal da Tela***************************/
//Verifica a qtde de itens que existe na OC ...
$sql = "SELECT COUNT(`id_oc_item`) AS qtde_itens 
        FROM `ocs_itens` 
        WHERE `id_oc` = '$id_oc' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

//Seleciona os itens da OC ...
$sql = "SELECT ocs.`id_cliente`, ocs.`status`, oi.`id_oc_item`, oi.`id_produto_acabado`, oi.`qtde`, 
        oi.`defeito_alegado`, oi.`cliente_vai_devolver_peca`, oi.`sugestao_cq`, oi.`sugestao_supervisor` 
        FROM `ocs` 
        INNER JOIN `ocs_itens` oi ON oi.`id_oc` = ocs.`id_oc` 
        INNER JOIN produtos_acabados pa ON pa.`id_produto_acabado` = oi.`id_produto_acabado` 
        WHERE ocs.`id_oc` = '$id_oc' ORDER BY oi.`id_oc_item` ";
$campos = bancos::sql($sql, ($posicao - 1), $posicao);

//Controle para Travar os campos da Tela ...
if($campos[0]['sugestao_cq'] > 0 && $campos[0]['sugestao_supervisor'] > 0) {//Significa que tudo j� foi preenchido ...
    if($campos[0]['status'] == 0) {//Enquanto a OC n�o estiver finalizada, o Roberto pode mudar a Sugest�o de Supervisor ...
        $class_cq               = 'textdisabled';
        $class_supervisor 	= 'caixadetexto';
        $disabled_cq		= 'disabled';
        $disabled_supervisor    = '';
    }else {//Se estiver finalizada, ningu�m mais pode editar ...
        $class_cq               = 'textdisabled';
        $class_supervisor 	= 'textdisabled';
        $disabled_cq		= 'disabled';
        $disabled_supervisor    = 'disabled';
    }
}else if($campos[0]['sugestao_cq'] > 0 && $campos[0]['sugestao_supervisor'] == 0) {//Significa que foi preenchida somente a parte do CQ ...
    /*Se j� foi preenchida a Etapa do Controle de Qualidade e o funcion�rio logado for Roberto "Diretor" 62, 
    ent�o travo os dados de CQ para que ele n�o edite esse dados ...*/
    if($_SESSION['id_funcionario'] == 62) {//S� desabilito para o Roberto, para que ele n�o possa mexer no que foi preenchido ...
        $class_cq               = 'textdisabled';
        $class_supervisor       = 'caixadetexto';
        $disabled_cq            = 'disabled';
        $disabled_supervisor    = '';
    }else {
        $class_cq               = 'caixadetexto';
        $class_supervisor 	= 'caixadetexto';
        $disabled_cq		= '';
        $disabled_supervisor    = '';
    }
}else {//Significa que nada ainda foi preenchido ...
    $class_cq 			= 'caixadetexto';
    $class_supervisor           = 'caixadetexto';
    $disabled_cq		= '';
    $disabled_supervisor        = '';
}
?>
<html>
<head>
<title>.:: Alterar Itens da OC N.�&nbsp;<?=$id_oc;?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar(posicao, verificar) {
/*Aqui significa que estou submetendo o formul�rio atrav�s do bot�o submit, sendo faz requisi��o das 
condi��es de valida��o ...*/
    if(typeof(verificar) != 'undefined') {
        //Sugest�o de Solu��o ...
        if(document.form.cmb_sugestao_solucao_cq.disabled == false) {
            if(!combo('form', 'cmb_sugestao_solucao_cq', '', 'SELECIONE A SUGEST�O DE SOLU��O !')) {
                return false
            }
        }
        //Sugest�o de Solu��o do Supervisor ...
        if(typeof(document.form.cmb_sugestao_solucao_supervisor) == 'object') {
            if(document.form.cmb_sugestao_solucao_supervisor.disabled == false) {
                if(!combo('form', 'cmb_sugestao_solucao_supervisor', '', 'SELECIONE A SUGEST�O DE SOLU��O !')) {
                    return false
                }
            }
        }
        //Status do Item ...
        if(!combo('form', 'cmb_status', '', 'SELECIONE O STATUS DO ITEM !')) {
            return false
        }
    }
//For�a o preenchimento das Sub-Op��es ...
    if(document.form.cmb_status.value == 3) {//Enviado p/ Processo Interno ...
        var lbl_enviado1 = document.getElementById('lbl_enviado1')
        var lbl_enviado2 = document.getElementById('lbl_enviado2')
        var lbl_enviado3 = document.getElementById('lbl_enviado3')
        //Se n�o tiver nenhuma Op��o selecionada, for�o o preenchimento ...
        if(!lbl_enviado1.checked && !lbl_enviado2.checked && !lbl_enviado3.checked) {
            alert('SELECIONE UMA OP��O !')
            document.getElementById('lbl_enviado1').focus()
            return false
        }
        if(lbl_enviado1.checked) {//Vari�vel que ser� utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado1.value
        }else if(lbl_enviado2.checked) {//Vari�vel que ser� utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado2.value
        }else if(lbl_enviado3.checked) {//Vari�vel que ser� utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado3.value
        }
    }else if(document.form.cmb_status.value == 7) {//Enviado p/ Estoque ...
        var lbl_enviado1 = document.getElementById('lbl_enviado1')
        var lbl_enviado2 = document.getElementById('lbl_enviado2')
        var lbl_enviado3 = document.getElementById('lbl_enviado3')
        var lbl_enviado4 = document.getElementById('lbl_enviado4')
        //Se n�o tiver nenhuma Op��o selecionada, for�o o preenchimento ...
        if(!lbl_enviado1.checked && !lbl_enviado2.checked && !lbl_enviado3.checked && !lbl_enviado4.checked) {
            alert('SELECIONE UMA OP��O !')
            document.getElementById('lbl_enviado1').focus()
            return false
        }
        //Somente nesta op��o que eu for�o o v�nculo do Fornecedor ...
        if(lbl_enviado2.checked) {
            if(document.getElementById('txt_fornecedor').value == '') {
                alert('� PRECISO VINCULAR UM FORNECEDOR !')
                document.getElementById('cmd_vincular_fornecedor').onclick()
                return false
            }
            //Vari�vel que ser� utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value         = lbl_enviado2.value
            document.form.hdd_fornecedor.value 	= document.getElementById('txt_fornecedor').value
        }else if(lbl_enviado1.checked) {//Vari�vel que ser� utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado1.value
        }else if(lbl_enviado3.checked) {//Vari�vel que ser� utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado3.value
        }else if(lbl_enviado4.checked) {//Vari�vel que ser� utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado4.value
        }
    }else if(document.form.cmb_status.value == 10) {//Desdobrar Quantidade ...
        var qtde_item_oc = '<?=$campos[0]['qtde'];?>'
        if(document.getElementById('txt_qtde_para_este_item').value == '' || document.getElementById('txt_qtde_para_este_item').value == 0) {
            alert('DIGITE A QUANTIDADE QUE DESEJA MANTER PARA ESTE ITEM !!!\n\nO RESTANTE SER� DESDOBRADO EM OUTRO ITEM !')
            document.getElementById('txt_qtde_para_este_item').focus()
            return false
        }
        /*N�o tem nexo o usu�rio digitar uma Quantidade igual ou superior ao Item da OC, afinal se � para desdobrar o item, tem que ser 
        digitado uma quantidade em um valor a menor do Item atual da OC ...*/
        if(eval(document.getElementById('txt_qtde_para_este_item').value) >= eval(qtde_item_oc)) {
            alert('DIGITE UMA QUANTIDADE MENOR DO QUE A QUANTIDADE DESTE ITEM !')
            document.getElementById('txt_qtde_para_este_item').focus()
            document.getElementById('txt_qtde_para_este_item').select()
            return false
        }
        document.form.hdd_qtde_para_este_item.value 	= document.getElementById('txt_qtde_para_este_item').value
    }
    //Manipula��o p/ Estoque, s� pode ser se a sugestao do supervisor for sem condi��es de conserto / troca em garantia...
    if(document.form.cmb_status.value == 8) {//Manipula��o para Estoque ...
        if(typeof(document.form.cmb_sugestao_solucao_supervisor) == 'object') {
            if(document.form.cmb_sugestao_solucao_supervisor.value != 3) {
                alert('O STATUS "MANIPULA��O P/ ESTOQUE" S� PODE SER UTILIZADO: \n\nQUANDO A SUGEST�O DO SUPERVISOR FOR "SEM CONDI��ES DE CONSERTO / TROCA EM GARANTIA" !')
                return false
            }
        }else {
            var sugestao_cq = eval('<?=$campos[0]['sugestao_cq'];?>')
            if(sugestao_cq != 3) {
                alert('O STATUS "MANIPULA��O P/ ESTOQUE" S� PODE SER UTILIZADO: \n\nQUANDO A SUGEST�O DO SUPERVISOR FOR "SEM CONDI��ES DE CONSERTO / TROCA EM GARANTIA" !')
                return false
            }
        }
    }
/*Aqui significa que estou submetendo o formul�rio atrav�s do bot�o submit, sendo faz requisi��o das 
condi��es de valida��o ...*/
    if(typeof(verificar) != 'undefined') {
        //Observa��o ...
        if(document.form.txt_observacao.value == '') {
            alert('DIGITE A OBSERVA��O !')
            document.form.txt_observacao.focus()
            return false
        }
        //Se a sugest�o do Supervisor for diferente do CQ int�o exibe um confirm...
        if(typeof(document.form.cmb_sugestao_solucao_supervisor) == 'object') {
            if(document.form.cmb_sugestao_solucao_supervisor.value != document.form.cmb_sugestao_solucao_cq.value) {
                var resposta = confirm('A SUGEST�O DO SUPERVISOR EST� DIFERENTE DA DO CQ ! DESEJA MANTER ESTA SUGEST�O ?')
                if(resposta == false) {
                    document.form.cmb_sugestao_solucao_supervisor.focus()
                    return false
                }
            }
            if(document.form.cmb_status.value == 9) {//Enviado p/ Cliente / Representante ...
                var resposta = confirm('DESEJA MARCAR ESSE REGISTRO P/ TODO(S) O(S) ITEM(NS) DESTA OC ?')
                if(resposta == true) document.form.hdd_marcar_registro_todos_itens.value = 1
            }
        }
    }
    //Se o usu�rio tentou salvar pela pagina��o, recupera o �ndice do item da OC ...
    if(posicao != null) document.form.posicao.value = posicao
    //Aqui � para n�o atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
    document.form.submit()//Submetendo o Formul�rio
}

function cliente_vai_devolver_peca(posicao) {
//Se o usu�rio tentou salvar pela pagina��o, recupera o �ndice do item da OC ...
    if(posicao != null) document.form.posicao.value = posicao
    //Aqui � para n�o atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
    document.form.submit()//Submetendo o Formul�rio
}

function exibir_opcoes_follow_up() {
    ajax('opcoes_follow_up.php', 'div_opcoes_follow_up')//Sempre oculta a Div ...
    //Essas op��es abrem Sub-Op��es ...
    var vetor_status = new Array(3, 7, 8, 10)
    for(i = 0; i < vetor_status.length; i++) {
        if(document.form.cmb_status.value == vetor_status[i]) {
            ajax('opcoes_follow_up.php?status='+document.form.cmb_status.value+'&id_oc_item=<?=$campos[0]['id_oc_item'];?>', 'div_opcoes_follow_up')
            break;
        }
    }
}

function controlar_options_enviar_estoque() {
    if(document.getElementById('lbl_enviado1').checked || document.getElementById('lbl_enviado3').checked || document.getElementById('lbl_enviado4').checked) {
        document.getElementById('txt_fornecedor').value 				= ''
        document.getElementById('cmd_vincular_fornecedor').disabled 	= true
        document.getElementById('cmd_vincular_fornecedor').className 	= 'textdisabled'
    }else {//Somente na op��o Troca Produto Rev. em Garantia que for�o o vinculo do Fornecedor ...
        document.getElementById('cmd_vincular_fornecedor').disabled 	= false
        document.getElementById('cmd_vincular_fornecedor').className 	= 'botao'
    }
}

//Exclus�o de Fornecedores
function excluir_fornecedor() {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.getElementById('txt_fornecedor').value = ''
    }
}

function excluir_follow_up(id_oc_item_follow_up) {
    var mensagem = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE FOLLOW-UP ?')
    if(mensagem == true) {
        //Aqui � para n�o atualizar a Tela abaixo que chamou esse LightBox ...
        document.form.nao_atualizar.value = 1
        window.location = 'alterar.php?id_oc=<?=$id_oc;?>&posicao=<?=$posicao;?>&id_oc_item=<?=$campos[0]['id_oc_item'];?>&id_oc_item_follow_up='+id_oc_item_follow_up
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.ativar_loading()
}
</Script>
</head>
<body topmargin='20%' onload='document.form.cmb_sugestao_solucao_cq.focus()' onunload='atualizar_abaixo()'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
<form name='form' method='post' action=''>
<!--*************Controle de Tela*************-->
<input type='hidden' name='id_oc' value="<?=$id_oc;?>">
<input type='hidden' name='id_oc_item' value="<?=$campos[0]['id_oc_item'];?>">
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_marcar_registro_todos_itens'>
<!--******************************************-->
<!--*************Vari�veis da Div*************-->
<input type='hidden' name='hdd_enviado'>
<input type='hidden' name='hdd_fornecedor'>
<input type='hidden' name='hdd_qtde_para_este_item'>
<!--******************************************-->
    <tr class='atencao' align='center'>
        <td>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
<?/***************************************CONTROLE DE QUALIDADE****************************************/?>
    <tr>
        <td>
            <fieldset>
                <legend class='legend_contorno'>
                    <span style='cursor: pointer'>
                        <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                            <b>CONTROLE DE QUALIDADE - OC N.� </b>
                        </font>
                        <font color='darkblue'>
                            <?=$id_oc;?>
                        </font>
                    </span>
                </legend>
                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                    <tr>
                        <td colspan='5'>
                            <fieldset>
                                <legend class='legend_contorno2'>
                                    <?=intermodular::pa_discriminacao($campos[0]['id_produto_acabado'], 0);?>
                                </legend>
                                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                                    <tr class='linhanormalescura'>
                                        <td>
                                            Qtde:
                                        </td>
                                        <td>
                                        <?
                                            echo $campos[0]['qtde'];
                                            //Aqui eu verifico se existe pelo menos 1 NF de Sa�da para esse PA e Cliente ...
                                            $sql = "SELECT DISTINCT(nfs.`id_nf`) AS id_nf 
                                                    FROM `nfs` 
                                                    INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` AND nfsi.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' 
                                                    WHERE nfs.`id_cliente` = '".$campos[0]['id_cliente']."' LIMIT 1 ";
                                            $campos_nfs = bancos::sql($sql);
                                            if(count($campos_nfs) == 1) {//Se existe pelo menos 1 NF, exibo esse Bot�o p/ q seja visualizado todas as NFs desse Cliente, desse PA ...
                                        ?>
                                            &nbsp;-&nbsp;
                                            <input type='button' name='cmd_nfs_saida' value='NFs Sa�da' title='NFs Sa�da' onclick="nova_janela('nfs_saida.php?id_cliente=<?=$campos[0]['id_cliente']?>&id_produto_acabado=<?=$campos[0]['id_produto_acabado'];?>', 'NFS_SAIDA', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:purple' class='botao'>
                                        <?
                                            }
                                        ?>
                                        </td>
                                    </tr>
                                    <tr class='linhanormalescura'>
                                        <td>
                                            Defeito Alegado:
                                        </td>
                                        <td>
                                            <?=$campos[0]['defeito_alegado'];?>
                                        </td>
                                    </tr>
                                    <tr class='linhanormalescura'>
                                        <td>
                                            Cliente vai devolver a pe�a:
                                        </td>
                                        <td>
                                            <?
                                                $checked = ($campos[0]['cliente_vai_devolver_peca'] == 'S') ? 'checked' : '';
                                            ?>
                                            <input type='checkbox' name='chkt_cliente_vai_devolver_peca' value='S' title='Cliente vai devolver a Pe�a' onclick="cliente_vai_devolver_peca('<?=$posicao;?>')" class='checkbox' <?=$checked;?>>
                                        </td>
                                    </tr>
                                    <tr class='linhanormalescura'>
                                        <td>
                                            <b>Sugest&atilde;o de Solu&ccedil;&atilde;o:</b>
                                        </td>
                                        <td>
                                            <select name='cmb_sugestao_solucao_cq' title='Selecione a Sugest�o de Solu��o do Controle de Qualidade' class='<?=$class_cq;?>' <?=$disabled_cq;?>>
                                            <?
                                                if($campos[0]['sugestao_cq'] == 1) {
                                                    $selected1 = 'selected';
                                                }else if($campos[0]['sugestao_cq'] == 2) {
                                                    $selected2 = 'selected';
                                                }else if($campos[0]['sugestao_cq'] == 3) {
                                                    $selected3 = 'selected';
                                                }else if($campos[0]['sugestao_cq'] == 4) {
                                                    $selected4 = 'selected';
                                                }else if($campos[0]['sugestao_cq'] == 5) {
                                                    $selected5 = 'selected';
                                                }
                                            ?>
                                                <option value='' style='color:red'>SELECIONE</option>
                                                <option value='1' <?=$selected1;?>>COM CONDI��O DE CONSERTO - PE�A EM GARANTIA</option>
                                                <option value='2' <?=$selected2;?>>COM CONDI��O DE CONSERTO - OR�AMENTO</option>
                                                <option value='3' <?=$selected3;?>>SEM CONDI��O DE CONSERTO - TROCA EM GARANTIA</option>
                                                <option value='4' <?=$selected4;?>>SEM CONDI��O DE CONSERTO - PE�A SEM GARANTIA</option>
                                                <option value='5' <?=$selected5;?>>SEM DEFEITO</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
<?
/****************************************************************************************************/
/*********************************************SUPERVISOR*********************************************/
//Se j� foi preenchida a Etapa do Controle de Qualidade ou os funcion�rios logado for Roberto "Diretor" 62, ent�o exibo essa parte abaixo ...
//D�rcio 98 e Netto 147 porque programa ...
	if($campos[0]['sugestao_cq'] > 0 || ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147)) {
?>
	<tr>
		<td>
			<br>
			<fieldset>
				<legend class="legend_contorno">
					<span style="cursor: pointer;">
						<font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'><b>
							SUPERVISOR
						</b></font>
					</span>
				</legend>
				<table border="0" width="100%" cellspacing='1' cellpadding='1' align='center'>
					<tr class='linhanormalescura'>
						<td>
							<b>Sugest&atilde;o de Solu&ccedil;&atilde;o:</b>
						</td>
						<td>
							<select name='cmb_sugestao_solucao_supervisor' title='Selecione a Sugest�o de Solu��o Supervisor' class="<?=$class_supervisor;?>" <?=$disabled_supervisor;?>>
							<?
								if($campos[0]['sugestao_supervisor'] == 1) {
									$selected_supervisor1 = 'selected';
								}else if($campos[0]['sugestao_supervisor'] == 2) {
									$selected_supervisor2 = 'selected';
								}else if($campos[0]['sugestao_supervisor'] == 3) {
									$selected_supervisor3 = 'selected';
								}else if($campos[0]['sugestao_supervisor'] == 4) {
									$selected_supervisor4 = 'selected';
								}else if($campos[0]['sugestao_supervisor'] == 5) {
									$selected_supervisor5 = 'selected';
								}
							?>
								<option value='' style='color:red'>SELECIONE</option>
								<option value='1' <?=$selected_supervisor1;?>>COM CONDI��O DE CONSERTO - PE�A EM GARANTIA</option>
								<option value='2' <?=$selected_supervisor2;?>>COM CONDI��O DE CONSERTO - OR�AMENTO</option>
								<option value='3' <?=$selected_supervisor3;?>>SEM CONDI��O DE CONSERTO - TROCA EM GARANTIA</option>
								<option value='4' <?=$selected_supervisor4;?>>SEM CONDI��O DE CONSERTO - PE�A SEM GARANTIA</option>
								<option value='5' <?=$selected_supervisor5;?>>SEM DEFEITO</option>
							</select>
						</td>
					</tr>
				</table>
			</td>
	</tr>
<?
	}
/****************************************************************************************************/
/****************************************REGISTRAR FOLLOW-UP*****************************************/
?>
	<tr>
		<td>
			&nbsp;
		</td>
	</tr>
	<tr class="linhadestaque">
		<td></td>
	</tr>
	<tr>
		<td>
			<br>
			<fieldset>
				<legend class="legend_contorno">
					<span style="cursor: pointer;">
						<font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'><b>
							REGISTRAR FOLLOW-UP
						</b></font>
					</span>
				</legend>
				<table border="0" width="100%" cellspacing='1' cellpadding='1' align='center'>
					<tr class='linhanormalescura'>
						<td>
							<b>Status:</b>
						</td>
						<td>
							<select name='cmb_status' title='Selecione o Status do Item' onchange="exibir_opcoes_follow_up()" class="combo">
								<option value='' style='color:red'>SELECIONE</option>
								<?
									foreach($vetor_status as $i => $status) {
								?>
								<option value='<?=$i;?>'><?=$status;?></option>
								<?
									}
								?>
							</select>
						</td>
					</tr>
					<tr class='linhanormalescura'>
						<td colspan="2">
							<div id="div_opcoes_follow_up"></div>
						</td>
					</tr>
					<tr class='linhanormalescura'>
						<td>
							<b>Observa&ccedil;&atilde;o:</b>
						</td>
						<td>
							<textarea name='txt_observacao' cols='100' rows='5' maxlength='500' title="Digite a Observa��o" class='caixadetexto'></textarea>
						</td>
					</tr>
					<tr class='linhanormalescura' align='center'>
						<td colspan='2'>
						<?
							//Enquanto a OC n�o estiver finalizada, posso registrar Follow-UP ...
							if($campos[0]['status'] == 0) {
						?>
							<img name='img_salvar' id='img_salvar' title='Salvar' src = '../../../../imagem/menu/salvar.png' width='24' height='24' onclick="return validar('<?=$posicao;?>', 1)">
						<?
							}
						?>
						</td>
					</tr>
				</table>
		</td>
	</tr>
</form>
<?
/****************************************************************************************************/
/***************************************FOLLOW-UP REGISTRADOS****************************************/
//Aqui busca os Follow_ups registrados dos Produtos ...
	$sql = "SELECT l.login, oci.* 
			FROM `ocs_itens_follow_ups` oci 
			INNER JOIN `funcionarios` f ON f.id_funcionario = oci.id_funcionario 
			INNER JOIN `logins` l ON l.id_funcionario = f.id_funcionario 
			WHERE oci.`id_oc_item` = '".$campos[0]['id_oc_item']."' ORDER BY oci.data_sys DESC ";
	$campos_oci_follow_up = bancos::sql($sql);
	$linhas_oci_follow_up = count($campos_oci_follow_up);
	if($linhas_oci_follow_up > 0) {
?>
	<tr>
		<td>
			<br>
			<fieldset>
				<legend class="legend_contorno">
					<span style="cursor: pointer;">
						<font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'><b>
							<b>FOLLOW-UP(S) REGISTRADO(S)</b>
						</b></font>
					</span>
				</legend>
				<table border="0" width="100%" cellspacing='1' cellpadding='1' align='center'>
					<tr class="linhadestaque" align='center'>
						<td>
							Itens
						</td>
						<td>
							Login
						</td>
						<td>
							Status
						</td>
						<td>
							Observa&ccedil;&atilde;o
						</td>
						<td>
							Data / Hora
						</td>
					</tr>
<?
		for($i = 0; $i < $linhas_oci_follow_up; $i++) {
?>
					<tr class="linhanormal" align='center'>
						<td>
						<?
							/*S� pode ser exclu�do o �ltimo Follow-UP registrado e somente o pr�prio autor � quem pode fazer isto, 
							a �nica op��o que n�o me permite excluir nada � a de "Manipula��o p/ Estoque" op��o 8, porque sen�o o usu�rio 
							ir� retirar pe�as do estoque v�rias vezes ...*/
							if($i == 0 && ($_SESSION['id_funcionario'] == $campos_oci_follow_up[$i]['id_funcionario']) && $campos_oci_follow_up[$i]['status'] != 8) {
						?>
							<img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_follow_up('<?=$campos_oci_follow_up[$i]['id_oc_item_follow_up'];?>')" alt="Excluir Follow-UP" title="Excluir Follow-UP">
						<?
							}else {
								echo '-';
							}
						?>
						</td>
						<td>
							<?=$campos_oci_follow_up[$i]['login'];?>
						</td>
						<td>
							<?=$vetor_status[$campos_oci_follow_up[$i]['status']];?>
						</td>
						<td align="left">
							<?=$campos_oci_follow_up[$i]['observacao'];?>
						</td>
						<td>
							<?=data::datetodata(substr($campos_oci_follow_up[$i]['data_sys'], 0, 10), '/').' '.substr($campos_oci_follow_up[$i]['data_sys'], 11, 5);?>
						</td>
					</tr>
<?
		}
?>
				</table>
		</td>
	</tr>
<?
	}
/****************************************************************************************************/
?>
    <tr align='center'>
        <td colspan='4'>
        <?
///////////////////////////////PAGINA��O CASO ESPECIFICA PARA ESTA TELA///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='return validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='#' onclick='return validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='return validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Pr�xima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
</body>
</html>