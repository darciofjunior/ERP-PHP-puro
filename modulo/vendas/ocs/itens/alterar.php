<?
require('../../../../lib/segurancas.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/ocs/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>ITEM / FOLLOW-UP REGISTRADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>FOLLOW-UP COM CONTEÚDO JÁ EXISTENTE.</font>";
$mensagem[3] = "<font class='erro'>JÁ EXISTE UMA MANIPULAÇÃO P/ ESTOQUE NESTE ITEM DE OC.</font>";
$mensagem[4] = "<font class='confirmacao'>FOLLOW-UP EXCLUÍDO COM SUCESSO.</font>";
$mensagem[5] = "<font class='confirmacao'>TODO(S) O(S) ITEM(NS) DESTA OC FOI(RAM) ATUALIZADO(S) COM SUCESSO.</font>";
$mensagem[6] = "<font class='confirmacao'>ITEM DE OC ALTERADO COM SUCESSO.</font>";

if($_SESSION[id_funcionario] == 10) {
    $vetor_status = array(1 => 'AVALIADO PELO CONTROLE DE QUALIDADE', 7 => 'ENVIADO P/ ESTOQUE (NÃO ALTERA A QTDE ESTOQUE)', 10 => 'DESDOBRAR QUANTIDADE', 11 => 'ACOMPANHAMENTO INTERNO');
}else {
    $vetor_status = array(1 => 'AVALIADO PELO CONTROLE DE QUALIDADE', 2 => 'AVALIADO PELO SUPERVISOR', 3 => 'ENVIADO PARA PROCESSO INTERNO', 
    4 => 'ENVIADO P/ TÉCNICO - PARA ESCLARECIMENTO DE PROBLEMA', 5 => 'ENVIADO P/ TÉCNICO - PARA ORÇAMENTO', 
    6 => 'ORÇAMENTO ENVIADO P/ CLIENTE - AGUARDANDO APROVAÇÃO', 7 => 'ENVIADO P/ ESTOQUE (NÃO ALTERA A QTDE ESTOQUE)', 8 => 'MANIPULAÇÃO P/ ESTOQUE', 
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
    /*Aqui eu verifico se já foi registrado pelo funcionário logado um mesmo conteúdo de Follow-Up, para o mesmo Status 
    e na mesma Data e no mesmo Item, caso não ...*/
    $sql = "SELECT id_oc_item_follow_up 
            FROM `ocs_itens_follow_ups` 
            WHERE `id_oc_item` = '$_POST[id_oc_item]' 
            AND `id_funcionario` = '$_SESSION[id_funcionario]' 
            AND `status` = '$_POST[cmb_status]' 
            AND `observacao` = '$_POST[txt_observacao]' 
            AND SUBSTRING(`data_sys`, 1, 10) = '".date('Y-m-d')."' LIMIT 1 ";
    $campos_oci_follow_up = bancos::sql($sql);
    if(count($campos_oci_follow_up) == 0) {//Esse conteúdo ainda não foi registrado para o Func nessa Data ... 
        //Nessas 2 opções abaixo, é necessário concatenar algumas informações a mais ...
        if($_POST['cmb_status'] == 3) {//Enviado p/ Processo Interno ...
            $observacao = $_POST['txt_observacao'].', Enviado p/ Processo Interno: '.$_POST['hdd_enviado'];
        }else if($_POST['cmb_status'] == 7) {//Enviado p/ Estoque ...
            $observacao = $_POST['txt_observacao'].', Enviado p/ Estoque: '.$_POST['hdd_enviado'];
            if(!empty($_POST['hdd_fornecedor'])) {//Quando existir Fornecedor envia e-mail ...
                $observacao.= ', P/ o Fornecedor: '.$_POST['hdd_fornecedor'];
                /*******************************************************************************************************/
                /*************************************************EMAIL*************************************************/
                /*******************************************************************************************************/
                //Nessa instância é enviado um e-mail para o Depto. de Compras para que eles já possam fazer Cotação da Peça de OC ...
                //Busca de alguns Dados p/ enviar e-mail ...
                $sql = "SELECT oi.qtde, oi.defeito_alegado, pa.referencia, pa.discriminacao, u.sigla 
                                FROM `ocs_itens` oi 
                                INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = oi.id_produto_acabado 
                                INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                                WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
                $campos_ocs = bancos::sql($sql);
                $assunto 		= 'ENVIAR PEÇA DE OC PARA FORNECEDOR';
                $conteudo_email.= '<table width="880" border="0" cellspacing="1" cellpadding="0" align="center">';
                //Linha Principal ...
                $conteudo_email.= '<tr class="linhacabecalho" align="center">';
                $conteudo_email.= '<td colspan="5" bgcolor="#BEBEBE"><font color="#00008B" face="arial black">ITEM DA OC N.º '.$_POST['id_oc'].' - FORNECEDOR "'.$_POST['hdd_fornecedor'].'"</font></td>';
                $conteudo_email.= '</tr>';
                //Linha Rótulos ...
                $conteudo_email.= '<tr class="linhadestaque" align="center">';
                $conteudo_email.= '<td bgcolor="#E8E8E8"><font face="courier new">QTDE</font></td>';
                $conteudo_email.= '<td bgcolor="#E8E8E8"><font face="courier new">UNIDADE</font></td>';
                $conteudo_email.= '<td bgcolor="#E8E8E8"><font face="courier new">REFERÊNCIA</font></td>';
                $conteudo_email.= '<td bgcolor="#E8E8E8"><font face="courier new">DISCRIMINAÇÃO</font></td>';
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
        }else if($_POST['cmb_status'] == 8) {//Manipulação p/ Estoque ...
            //Verifico se já foi feita alguma Manipulação para este item ...
            $sql = "SELECT id_oc_item_follow_up 
                    FROM `ocs_itens_follow_ups` 
                    WHERE `id_oc_item` = '$_POST[id_oc_item]' 
                    AND `status` = '8' LIMIT 1 ";
            $campos_manipulacao	= bancos::sql($sql);
            if(count($campos_manipulacao) == 0) {//Não encontrou registro, posso manipular normalmente ...
                //Busca de alguns Dados p/ enviar e-mail ...
                $sql = "SELECT ocs.id_oc, ocs.id_cliente, oi.id_produto_acabado, oi.qtde 
                        FROM `ocs_itens` oi 
                        INNER JOIN `ocs` ON ocs.id_oc = oi.id_oc 
                        WHERE oi.`id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
                $campos_ocs 	= bancos::sql($sql);
                $resultado      = estoque_acabado::verificar_manipulacao_estoque($campos_ocs[0]['id_produto_acabado'], $campos_ocs[0]['qtde']);
                if($resultado['retorno'] == 'executar') {
                    /*Tenho que chamar essa função para Setar o P.A., para o Paçoquinha saber 
                    que ele poder liberar os Pedidos ...*/
                    estoque_acabado::seta_nova_entrada_pa_op_compras($campos_ocs[0]['id_produto_acabado']);
                    //Procedimento normal para registro da Entrada, sempre manipula a Qtde no Sinal inverso ...
                    $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `id_cliente`, `numero_oc`, `qtde`, `observacao`, `acao`, `data_sys`) VALUES (NULL, '".$campos_ocs[0]['id_produto_acabado']."', '$_SESSION[id_funcionario]', '$_SESSION[id_funcionario]', '".$campos_ocs[0]['id_cliente']."', '".$campos_ocs[0]['id_oc']."', '".-$campos_ocs[0]['qtde']."', '$_POST[txt_observacao]', 'O', '".date('Y-m-d H:i:s')."') ";
                    bancos::sql($sql);
                    sleep(2);
                    estoque_acabado::atualizar($campos_ocs[0]['id_produto_acabado']);//Atualiza Real ...
                    estoque_acabado::controle_estoque_pa($campos_ocs[0]['id_produto_acabado']);//Disponível ...
                    estoque_acabado::atualizar_producao($campos_ocs[0]['id_produto_acabado']);//Produção ...
                }
            }else {//Já encontrou um Registro ...
                $registrar_follow_up = 0;
            }
        }else if($_POST['cmb_status'] == 9) {//Enviado p/ Cliente ou Representante ...	
            if($_POST['hdd_marcar_registro_todos_itens'] == 1) {//Significa que é p/ colocar o mesmo Registro p/ todos os Itens ...
                //Aqui eu busco quais são os Itens da OC ...
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
                $registrar_follow_up = 0;//Coloco isso para o Sys não registrar um Follow-UP que está abaixo de todos ifs e elses de status
            }
        }else if($_POST['cmb_status'] == 10) {//Desdobrar Quantidade ...
            //Aqui será gerado um novo Item com a Diferença da Qtde que foi digitada pelo usuário ...
            //Busca de alguns Dados p/ criar Item ...
            $sql = "SELECT id_oc, id_produto_acabado, qtde, defeito_alegado 
                    FROM `ocs_itens` 
                    WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
            $campos_ocs 	= bancos::sql($sql);
            $qtde_item_novo = ($campos_ocs[0]['qtde'] - $_POST['hdd_qtde_para_este_item']);
            //Inserindo um Novo Item com a Diferença da Qtde Digitada pelo usuário ...
            $sql = "INSERT INTO `ocs_itens` (`id_oc_item`, `id_oc`, `id_produto_acabado`, `qtde`, `defeito_alegado`) VALUES (NULL, '".$campos_ocs[0]['id_oc']."', '".$campos_ocs[0]['id_produto_acabado']."', '$qtde_item_novo', '".$campos_ocs[0]['defeito_alegado']."') ";
            bancos::sql($sql);
            //O Item Atual passa a assumir a Qtde que o usuário realmente desejou manter ...
            $sql = "UPDATE `ocs_itens` SET `qtde` = '$_POST[hdd_qtde_para_este_item]' WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Qualquer outra opção, não é necessário concatentar nenhum dado a mais na Observação ...
        if(!isset($observacao)) $observacao = $_POST['txt_observacao'];

        if($registrar_follow_up == 1) {
            //Registra o Follow-Up do Item da OC ...
            $sql = "INSERT INTO `ocs_itens_follow_ups` (`id_oc_item_follow_up`, `id_oc_item`, `id_funcionario`, `status`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_oc_item]', '$_SESSION[id_funcionario]', '$_POST[cmb_status]', '$observacao', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
            if($_POST['cmb_status'] != 11) {//Só não altera o Status do Item quando for a opção "ACOMPANHAMENTO INTERNO"
                //Atualizo o Status do Item da OC ...
                $sql = "UPDATE `ocs_itens` SET `status` = '$_POST[cmb_status]' WHERE `id_oc_item` = '$_POST[id_oc_item]' LIMIT 1 ";
                bancos::sql($sql);
            }
            $valor = 1;
        }else {
            //Significa que é p/ colocar o mesmo Registro p/ todos os Itens, isso já foi feito mais acima ...
            $valor = ($_POST['hdd_marcar_registro_todos_itens'] == 1) ? 5 : 3;
        }
    }else {
        $valor = 2;
    }
}

/****************************************************************************************************/
//Excluo o Follow-Up que está vinculado ao item da OC ...
if(!empty($_GET['id_oc_item_follow_up'])) {
    $sql = "DELETE FROM `ocs_itens_follow_ups` WHERE `id_oc_item_follow_up` = '$_GET[id_oc_item_follow_up]' LIMIT 1 ";
    bancos::sql($sql);
    /*Aqui eu busco o último Follow-UP registrado do Item para atualizar o Status do Item da OC, 
    desde que não seja "ACOMPANHAMENTO INTERNO" ...*/
    $sql = "SELECT `status` 
            FROM `ocs_itens_follow_ups` 
            WHERE `id_oc_item` = '$_GET[id_oc_item]' 
            AND `status` <> '11' ORDER BY `data_sys` DESC LIMIT 1 ";
    $campos_status_follow_up = bancos::sql($sql);
    if(count($campos_status_follow_up) == 1) {//Encontrou registro ...
        $sql = "UPDATE `ocs_itens` SET `status` = '".$campos_status_follow_up[0]['status']."' WHERE `id_oc_item` = '$_GET[id_oc_item]' LIMIT 1 ";
    }else {//Não encontrou então volta a situação deste para em Aberto ...
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
if($campos[0]['sugestao_cq'] > 0 && $campos[0]['sugestao_supervisor'] > 0) {//Significa que tudo já foi preenchido ...
    if($campos[0]['status'] == 0) {//Enquanto a OC não estiver finalizada, o Roberto pode mudar a Sugestão de Supervisor ...
        $class_cq               = 'textdisabled';
        $class_supervisor 	= 'caixadetexto';
        $disabled_cq		= 'disabled';
        $disabled_supervisor    = '';
    }else {//Se estiver finalizada, ninguém mais pode editar ...
        $class_cq               = 'textdisabled';
        $class_supervisor 	= 'textdisabled';
        $disabled_cq		= 'disabled';
        $disabled_supervisor    = 'disabled';
    }
}else if($campos[0]['sugestao_cq'] > 0 && $campos[0]['sugestao_supervisor'] == 0) {//Significa que foi preenchida somente a parte do CQ ...
    /*Se já foi preenchida a Etapa do Controle de Qualidade e o funcionário logado for Roberto "Diretor" 62, 
    então travo os dados de CQ para que ele não edite esse dados ...*/
    if($_SESSION['id_funcionario'] == 62) {//Só desabilito para o Roberto, para que ele não possa mexer no que foi preenchido ...
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
<title>.:: Alterar Itens da OC N.º&nbsp;<?=$id_oc;?> ::.</title>
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
/*Aqui significa que estou submetendo o formulário através do botão submit, sendo faz requisição das 
condições de validação ...*/
    if(typeof(verificar) != 'undefined') {
        //Sugestão de Solução ...
        if(document.form.cmb_sugestao_solucao_cq.disabled == false) {
            if(!combo('form', 'cmb_sugestao_solucao_cq', '', 'SELECIONE A SUGESTÃO DE SOLUÇÃO !')) {
                return false
            }
        }
        //Sugestão de Solução do Supervisor ...
        if(typeof(document.form.cmb_sugestao_solucao_supervisor) == 'object') {
            if(document.form.cmb_sugestao_solucao_supervisor.disabled == false) {
                if(!combo('form', 'cmb_sugestao_solucao_supervisor', '', 'SELECIONE A SUGESTÃO DE SOLUÇÃO !')) {
                    return false
                }
            }
        }
        //Status do Item ...
        if(!combo('form', 'cmb_status', '', 'SELECIONE O STATUS DO ITEM !')) {
            return false
        }
    }
//Força o preenchimento das Sub-Opções ...
    if(document.form.cmb_status.value == 3) {//Enviado p/ Processo Interno ...
        var lbl_enviado1 = document.getElementById('lbl_enviado1')
        var lbl_enviado2 = document.getElementById('lbl_enviado2')
        var lbl_enviado3 = document.getElementById('lbl_enviado3')
        //Se não tiver nenhuma Opção selecionada, forço o preenchimento ...
        if(!lbl_enviado1.checked && !lbl_enviado2.checked && !lbl_enviado3.checked) {
            alert('SELECIONE UMA OPÇÃO !')
            document.getElementById('lbl_enviado1').focus()
            return false
        }
        if(lbl_enviado1.checked) {//Variável que será utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado1.value
        }else if(lbl_enviado2.checked) {//Variável que será utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado2.value
        }else if(lbl_enviado3.checked) {//Variável que será utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado3.value
        }
    }else if(document.form.cmb_status.value == 7) {//Enviado p/ Estoque ...
        var lbl_enviado1 = document.getElementById('lbl_enviado1')
        var lbl_enviado2 = document.getElementById('lbl_enviado2')
        var lbl_enviado3 = document.getElementById('lbl_enviado3')
        var lbl_enviado4 = document.getElementById('lbl_enviado4')
        //Se não tiver nenhuma Opção selecionada, forço o preenchimento ...
        if(!lbl_enviado1.checked && !lbl_enviado2.checked && !lbl_enviado3.checked && !lbl_enviado4.checked) {
            alert('SELECIONE UMA OPÇÃO !')
            document.getElementById('lbl_enviado1').focus()
            return false
        }
        //Somente nesta opção que eu forço o vínculo do Fornecedor ...
        if(lbl_enviado2.checked) {
            if(document.getElementById('txt_fornecedor').value == '') {
                alert('É PRECISO VINCULAR UM FORNECEDOR !')
                document.getElementById('cmd_vincular_fornecedor').onclick()
                return false
            }
            //Variável que será utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value         = lbl_enviado2.value
            document.form.hdd_fornecedor.value 	= document.getElementById('txt_fornecedor').value
        }else if(lbl_enviado1.checked) {//Variável que será utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado1.value
        }else if(lbl_enviado3.checked) {//Variável que será utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado3.value
        }else if(lbl_enviado4.checked) {//Variável que será utilizada na hora de registrar o Follow-UP ...
            document.form.hdd_enviado.value 	= lbl_enviado4.value
        }
    }else if(document.form.cmb_status.value == 10) {//Desdobrar Quantidade ...
        var qtde_item_oc = '<?=$campos[0]['qtde'];?>'
        if(document.getElementById('txt_qtde_para_este_item').value == '' || document.getElementById('txt_qtde_para_este_item').value == 0) {
            alert('DIGITE A QUANTIDADE QUE DESEJA MANTER PARA ESTE ITEM !!!\n\nO RESTANTE SERÁ DESDOBRADO EM OUTRO ITEM !')
            document.getElementById('txt_qtde_para_este_item').focus()
            return false
        }
        /*Não tem nexo o usuário digitar uma Quantidade igual ou superior ao Item da OC, afinal se é para desdobrar o item, tem que ser 
        digitado uma quantidade em um valor a menor do Item atual da OC ...*/
        if(eval(document.getElementById('txt_qtde_para_este_item').value) >= eval(qtde_item_oc)) {
            alert('DIGITE UMA QUANTIDADE MENOR DO QUE A QUANTIDADE DESTE ITEM !')
            document.getElementById('txt_qtde_para_este_item').focus()
            document.getElementById('txt_qtde_para_este_item').select()
            return false
        }
        document.form.hdd_qtde_para_este_item.value 	= document.getElementById('txt_qtde_para_este_item').value
    }
    //Manipulação p/ Estoque, só pode ser se a sugestao do supervisor for sem condições de conserto / troca em garantia...
    if(document.form.cmb_status.value == 8) {//Manipulação para Estoque ...
        if(typeof(document.form.cmb_sugestao_solucao_supervisor) == 'object') {
            if(document.form.cmb_sugestao_solucao_supervisor.value != 3) {
                alert('O STATUS "MANIPULAÇÃO P/ ESTOQUE" SÓ PODE SER UTILIZADO: \n\nQUANDO A SUGESTÃO DO SUPERVISOR FOR "SEM CONDIÇÕES DE CONSERTO / TROCA EM GARANTIA" !')
                return false
            }
        }else {
            var sugestao_cq = eval('<?=$campos[0]['sugestao_cq'];?>')
            if(sugestao_cq != 3) {
                alert('O STATUS "MANIPULAÇÃO P/ ESTOQUE" SÓ PODE SER UTILIZADO: \n\nQUANDO A SUGESTÃO DO SUPERVISOR FOR "SEM CONDIÇÕES DE CONSERTO / TROCA EM GARANTIA" !')
                return false
            }
        }
    }
/*Aqui significa que estou submetendo o formulário através do botão submit, sendo faz requisição das 
condições de validação ...*/
    if(typeof(verificar) != 'undefined') {
        //Observação ...
        if(document.form.txt_observacao.value == '') {
            alert('DIGITE A OBSERVAÇÃO !')
            document.form.txt_observacao.focus()
            return false
        }
        //Se a sugestão do Supervisor for diferente do CQ intão exibe um confirm...
        if(typeof(document.form.cmb_sugestao_solucao_supervisor) == 'object') {
            if(document.form.cmb_sugestao_solucao_supervisor.value != document.form.cmb_sugestao_solucao_cq.value) {
                var resposta = confirm('A SUGESTÃO DO SUPERVISOR ESTÁ DIFERENTE DA DO CQ ! DESEJA MANTER ESTA SUGESTÃO ?')
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
    //Se o usuário tentou salvar pela paginação, recupera o índice do item da OC ...
    if(posicao != null) document.form.posicao.value = posicao
    //Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
    document.form.submit()//Submetendo o Formulário
}

function cliente_vai_devolver_peca(posicao) {
//Se o usuário tentou salvar pela paginação, recupera o índice do item da OC ...
    if(posicao != null) document.form.posicao.value = posicao
    //Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
    document.form.nao_atualizar.value = 1
    document.form.submit()//Submetendo o Formulário
}

function exibir_opcoes_follow_up() {
    ajax('opcoes_follow_up.php', 'div_opcoes_follow_up')//Sempre oculta a Div ...
    //Essas opções abrem Sub-Opções ...
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
    }else {//Somente na opção Troca Produto Rev. em Garantia que forço o vinculo do Fornecedor ...
        document.getElementById('cmd_vincular_fornecedor').disabled 	= false
        document.getElementById('cmd_vincular_fornecedor').className 	= 'botao'
    }
}

//Exclusão de Fornecedores
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
        //Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
        document.form.nao_atualizar.value = 1
        window.location = 'alterar.php?id_oc=<?=$id_oc;?>&posicao=<?=$posicao;?>&id_oc_item=<?=$campos[0]['id_oc_item'];?>&id_oc_item_follow_up='+id_oc_item_follow_up
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
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
<!--*************Variáveis da Div*************-->
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
                            <b>CONTROLE DE QUALIDADE - OC N.º </b>
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
                                            //Aqui eu verifico se existe pelo menos 1 NF de Saída para esse PA e Cliente ...
                                            $sql = "SELECT DISTINCT(nfs.`id_nf`) AS id_nf 
                                                    FROM `nfs` 
                                                    INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` AND nfsi.`id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' 
                                                    WHERE nfs.`id_cliente` = '".$campos[0]['id_cliente']."' LIMIT 1 ";
                                            $campos_nfs = bancos::sql($sql);
                                            if(count($campos_nfs) == 1) {//Se existe pelo menos 1 NF, exibo esse Botão p/ q seja visualizado todas as NFs desse Cliente, desse PA ...
                                        ?>
                                            &nbsp;-&nbsp;
                                            <input type='button' name='cmd_nfs_saida' value='NFs Saída' title='NFs Saída' onclick="nova_janela('nfs_saida.php?id_cliente=<?=$campos[0]['id_cliente']?>&id_produto_acabado=<?=$campos[0]['id_produto_acabado'];?>', 'NFS_SAIDA', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:purple' class='botao'>
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
                                            Cliente vai devolver a peça:
                                        </td>
                                        <td>
                                            <?
                                                $checked = ($campos[0]['cliente_vai_devolver_peca'] == 'S') ? 'checked' : '';
                                            ?>
                                            <input type='checkbox' name='chkt_cliente_vai_devolver_peca' value='S' title='Cliente vai devolver a Peça' onclick="cliente_vai_devolver_peca('<?=$posicao;?>')" class='checkbox' <?=$checked;?>>
                                        </td>
                                    </tr>
                                    <tr class='linhanormalescura'>
                                        <td>
                                            <b>Sugest&atilde;o de Solu&ccedil;&atilde;o:</b>
                                        </td>
                                        <td>
                                            <select name='cmb_sugestao_solucao_cq' title='Selecione a Sugestão de Solução do Controle de Qualidade' class='<?=$class_cq;?>' <?=$disabled_cq;?>>
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
                                                <option value='1' <?=$selected1;?>>COM CONDIÇÃO DE CONSERTO - PEÇA EM GARANTIA</option>
                                                <option value='2' <?=$selected2;?>>COM CONDIÇÃO DE CONSERTO - ORÇAMENTO</option>
                                                <option value='3' <?=$selected3;?>>SEM CONDIÇÃO DE CONSERTO - TROCA EM GARANTIA</option>
                                                <option value='4' <?=$selected4;?>>SEM CONDIÇÃO DE CONSERTO - PEÇA SEM GARANTIA</option>
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
//Se já foi preenchida a Etapa do Controle de Qualidade ou os funcionários logado for Roberto "Diretor" 62, então exibo essa parte abaixo ...
//Dárcio 98 e Netto 147 porque programa ...
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
							<select name='cmb_sugestao_solucao_supervisor' title='Selecione a Sugestão de Solução Supervisor' class="<?=$class_supervisor;?>" <?=$disabled_supervisor;?>>
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
								<option value='1' <?=$selected_supervisor1;?>>COM CONDIÇÃO DE CONSERTO - PEÇA EM GARANTIA</option>
								<option value='2' <?=$selected_supervisor2;?>>COM CONDIÇÃO DE CONSERTO - ORÇAMENTO</option>
								<option value='3' <?=$selected_supervisor3;?>>SEM CONDIÇÃO DE CONSERTO - TROCA EM GARANTIA</option>
								<option value='4' <?=$selected_supervisor4;?>>SEM CONDIÇÃO DE CONSERTO - PEÇA SEM GARANTIA</option>
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
							<textarea name='txt_observacao' cols='100' rows='5' maxlength='500' title="Digite a Observação" class='caixadetexto'></textarea>
						</td>
					</tr>
					<tr class='linhanormalescura' align='center'>
						<td colspan='2'>
						<?
							//Enquanto a OC não estiver finalizada, posso registrar Follow-UP ...
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
							/*Só pode ser excluído o último Follow-UP registrado e somente o próprio autor é quem pode fazer isto, 
							a única opção que não me permite excluir nada é a de "Manipulação p/ Estoque" opção 8, porque senão o usuário 
							irá retirar peças do estoque várias vezes ...*/
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
///////////////////////////////PAGINAÇÃO CASO ESPECIFICA PARA ESTA TELA///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='return validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='#' onclick='return validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='return validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
</body>
</html>