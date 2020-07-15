<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/vendas/atendimento_diario/responder_feedback.php', '../../../');

if(!empty($_POST['txt_feedback'])) {
    $sql = "UPDATE `atendimentos_diarios` SET `feedback` = '$_POST[txt_feedback]', `data_sys_resposta` = '".date('Y-m-d H:i:s')."' WHERE `id_atendimento_diario` = '$_POST[hdd_atendimento_diario]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>		
        alert('FEEDBACK DE RELÁTORIO DE ATENDIMENTO DIÁRIO RESPONDIDO COM SUCESSO !')
        window.location = 'responder_feedback.php'
    </Script>
<?
}

//Busco dados do id_atendiamento_diario passado por parâmetro pelo Usuário ...
$sql = "SELECT ad.`id_atendimento_diario`, IF(ad.id_cliente = 0, ad.pessoa_atendida, c.`razaosocial`) AS cliente, r.`nome_fantasia`, ad.`contato` , ad.`procedimento`, ad.`observacao`, ad.`feedback`, f.`nome`, DATE_FORMAT(ad.`data_sys_registrou`, '%d/%m/%Y') AS data, TIME_FORMAT(ad.`data_sys_registrou`, '%H:%i:%s') AS hora, ad.`numero` 
        FROM `atendimentos_diarios` ad 
        LEFT JOIN `clientes` c ON c.`id_cliente` = ad.`id_cliente` 
        INNER JOIN `representantes` r ON r.`id_representante` = ad.`id_representante` 
        INNER JOIN `funcionarios` f ON f.`id_funcionario` = ad.`id_funcionario_registrou` 
        WHERE ad.`id_atendimento_diario` = '$_GET[id_atendimento_diario]' LIMIT 1 ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Responder Relatório de Atendimento Diário (FeedBack) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    //FeedBack
    if(document.form.txt_feedback.value == '') {
        alert('DIGITE O FEEDBACK !')
        document.form.txt_feedback.focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_feedback.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='hdd_atendimento_diario' value='<?=$_GET['id_atendimento_diario'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Responder Relatório de Atendimento Diário (FeedBack)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Funcionário que Registrou:</b>
        </td>
        <td>
            <?=$campos[0]['nome'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data e Hora de Registro:</b>
        </td>
        <td>
            <?=$campos[0]['data'].' '.$campos[0]['hora'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cliente:</b>
        </td>
        <td>
            <?=$campos[0]['cliente'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante:</b>
        </td>
        <td>
            <?=$campos[0]['nome_fantasia'];?>
        </td>
    </tr>
    <tr class='linhanormal'>	
        <td>
            <b>Contato:</b>
        </td>
        <td>
            <?=$campos[0]['contato'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Procedimento:</b>
        </td>
        <td>
            <?
                $vetor_procedimentos = array('C' => 'Ocorrência', 'O' => 'Orçamento', 'P' => 'Pedido', 'OC' => 'OC');
                echo $vetor_procedimentos[$campos[0]['procedimento']];

                if($campos[0]['procedimento'] == 'O') {
                        $url = "../../vendas/pedidos/itens/detalhes_orcamento.php?veio_faturamento=1&id_orcamento_venda=".$campos[0]['numero'].'&pop_up=1';
                }else if($campos[0]['procedimento'] == 'P') {
                        $url = "../../faturamento/nota_saida/itens/detalhes_pedido.php?veio_faturamento=1&id_pedido_venda=".$campos[0]['numero'].'&pop_up=1';
                }else if($campos[0]['procedimento'] == 'OC') {
                        $url = "../../vendas/ocs/itens/itens.php?id_oc=".$campos[0]['numero'];
                }
            ?>
            &nbsp;-&nbsp;
            Número: 
            <a href="javascript:nova_janela('<?=$url;?>', 'ORC', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class="link">	
                <?=$campos[0]['numero'];?>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação:</b>
        </td>
        <td>
            <?=$campos[0]['observacao'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>FeedBack:</b>
        </td>
        <td>
            <textarea name="txt_feedback" title="Digite a Observação" rows="3" cols="85" maxlength="255" class="caixadetexto"></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'responder_feedback.php<?=$parametro;?>'" class='botao'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_feedback.focus()" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>