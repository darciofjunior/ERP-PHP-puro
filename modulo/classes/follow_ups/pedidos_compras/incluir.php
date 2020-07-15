<?
require('../../../../lib/segurancas.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/consultar.php', '../../../../');

if($passo == 1) {
    if($_POST['opt_data'] == 2) {//Resolveu alterar a Data de Entrega
        $data_entrega_para_utilizar = data::datatodate($_POST['txt_nova_data_entrega'], '-');//Esse prazo é Digitável ...
    }else {
        $data_entrega_para_utilizar = $_POST['hdd_prazo_entrega'];//Essa variável está guardada em um Hidden ...
    }
//Atualizando dados de Cabeçalho ...
    $sql = "UPDATE `pedidos` SET `prazo_entrega` = '$data_entrega_para_utilizar' WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;

    if(!empty($data_entrega_para_utilizar) && !empty($_POST['txt_observacao'])) {
        $observacao     = strtolower($_POST['txt_observacao']);
        
        //Nós só podemos ter uma Impressão de Follow-UP para cada assunto ...
        if(!empty($_POST['chkt_exibir_no_pdf'])) {
            /*Antes de qualquer coisa, desmarco todas as outras marcações de Exibir no Follow-UP, 
            afinal só posso ter uma única marcação p/ cada assunto ...*/
            $sql = "UPDATE `follow_ups` SET `exibir_no_pdf` = 'N' WHERE `identificacao` = '$_POST[id_pedido]' AND `origem` = '16' ";
            bancos::sql($sql);
            
            $exibir_no_pdf = 'S';
        }else {
            $exibir_no_pdf = 'N';
        }
        
        /*******************************************************************************/
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `data_entrega_embarque`, `observacao`, `exibir_no_pdf`, `data_sys`) VALUES (NULL, '$_POST[hdd_fornecedor]', '$_SESSION[id_funcionario]', '$_POST[id_pedido]', '16', '$data_entrega_para_utilizar', '$observacao', '$exibir_no_pdf', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
/*************************************E-mail somente para Importação*************************************/
    //Busca do Nome da Importação se Existir ...
    $sql = "SELECT i.`nome` 
            FROM `importacoes` i 
            INNER JOIN `pedidos` p ON p.`id_importacao` = i.`id_importacao` 
            WHERE p.`id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    $campos_importacao = bancos::sql($sql);
    if(count($campos_importacao) == 1) {
//Aqui eu mando um e-mail informando tudo o que foi registrado na Ocorrência do Follow-Up da Importação ...
        $sql = "SELECT `login` 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login   = bancos::sql($sql);
        $login          = $campos_login[0]['login'];
//Estrutura do E-mail ...
        $destino = $follow_up_importacao;
        $assunto = 'Follow-UP Importação - '.$campos_importacao[0]['nome'].' - '.$_POST['id_pedido'];
        $mensagem_email = '<b>Login: </b>'.$login.' - '.date('d/m/Y H:i:s').'<br>'.$_POST['txt_observacao'].'<p>'.$PHP_SELF;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $mensagem_email);
    }
/********************************************************************************************************/
?>
    <Script Language = 'JavaScript'>
        alert('FOLLOW-UP REGISTRADO COM SUCESSO !')
        opener.reler_tela_itens_pedido()
        window.close()
    </Script>
<?
}else {
//Busca de Dados para Mostrar os Follow-up(s) cadastrados no Pedido
    $sql = "SELECT f.nome AS `funcionario` 
            FROM `funcionarios` f 
            INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` AND l.`id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos_funcionario = bancos::sql($sql);
    $funcionario        = $campos_funcionario[0]['funcionario'];

    $sql = "SELECT p.*, f.`id_pais` 
            FROM `pedidos` p 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
            WHERE p.`id_pedido` = '$identificacao' LIMIT 1 ";
    $campos 		= bancos::sql($sql);
    $id_pais 		= $campos[0]['id_pais'];
    $prazo_viagem_navio = $campos[0]['prazo_navio'];
    $data_emissao 	= substr($campos[0]['data_emissao'], 0, 10);
    $data_entrega 	= $campos[0]['prazo_entrega'];
?>
<html>
<title>.:: Incluir Novo Follow-Up ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    //Essa verificação será feita quando o usuário estiver alterando os dados de Cabeçalho do Pedido e só p/ o país do Brasil ...
    if(typeof(document.form.txt_nova_data_entrega) == 'object') {
        //Nova Data de Entrega
        if(!data('form', 'txt_nova_data_entrega', '4000', 'FOLLOW-UP')) {
            return false
        }
    }
/*****************************************************************************/
//Observação
    if(document.form.txt_observacao.value == '') {
        alert('DIGITE A OBSERVAÇÃO !')
        document.form.txt_observacao.focus()
        return false
    }
    if(typeof(document.form.txt_nova_data_entrega) == 'object') document.form.txt_nova_data_entrega.disabled = false
}
</Script>
<body onload="if(typeof(document.form.txt_nova_data_entrega) == 'object') {document.form.txt_nova_data_entrega.focus()}else {document.form.txt_observacao.focus()}">
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='id_pedido' value='<?=$identificacao;?>'>
<input type='hidden' name='hdd_fornecedor' value='<?=$campos[0]['id_fornecedor'];?>'>
<input type='hidden' name='hdd_prazo_entrega' value='<?=$data_entrega;?>'>
<input type='hidden' name='data_emissao' value='<?=$data_emissao;?>'>
<!--*************************************************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Novo Follow-Up
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Funcionário:</b>
        </td>
        <td>
            <?=$funcionario;?>
        </td>
    </tr>
    <?
        /***************************Países Estrangeiros*********************/
        if($id_pais != 31) {//Só para esses países existem Importações ...
    ?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <iframe name='dados_importacao' src='dados_importacao.php?id_pedido=<?=$identificacao;?>' width='100%' height='280' scrolling='no' class='caixadetexto'></iframe>
        </td>
    </tr>
    <?
        /***************************Países Nacionais*********************/
        }else {//Para o Brasil é normal ...
    ?>
    <tr class='linhanormal'>
        <td>
            Data de Entrega Atual:
        </td>
        <td>
            <input type='text' name='txt_data_entrega_atual' value='<?=data::datetodata($data_entrega, '/');?>' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='opt_data' value='1' id='opt3'>
            <label for='opt3'>Manter Data de Entrega</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nova Data de Entrega:</b>
        </td>
        <td>
            <input type='text' name='txt_nova_data_entrega' value="<?=date('d/m/Y');?>" title='Digite a Data' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_nova_data_entrega&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')" style='cursor:hand'>
            <input type='radio' name='opt_data' value='2' id='opt4' checked>
            <label for='opt4'>Alterar Data de Entrega</label>
        </td>
    </tr>
    <?
        }
        /****************************************************************/
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Observação:</b>
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a Observação' maxlength='255' cols='80' rows='3' class='caixadetexto'></textarea>
            &nbsp;
            <input type='checkbox' name='chkt_exibir_no_pdf' id='chkt_exibir_no_pdf' value='S' title='Exibir no PDF' class='checkbox'>
            <label for='chkt_exibir_no_pdf'>
                Exibir no PDF
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_nova_data_entrega.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_incluir' value='Incluir' title='Incluir' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>