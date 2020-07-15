<?
require('../../../lib/segurancas.php');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) {
    switch($opcao) {
        case 1://Significa que veio do Menu Abertas / Liberadas ...
        case 2://Significa que veio do Menu de Liberadas / Faturadas ...
        case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
        case 4://Significa que veio do Menu de Devolução 
            segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
        break;
        default://Significa que veio do Menu de Devolução ...
            segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
        break;
    }
}

//Aqui eu busco o campo `importado_financeiro` p/ exibir uma mensagem mais abaixo ...
$sql = "SELECT `importado_financeiro` 
        FROM `nfs` 
        WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Cabeçalho ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function atualizar_frames_abaixo() {
    if(typeof(opener.parent.itens) == 'object') {
        opener.parent.itens.document.form.submit()
        opener.parent.rodape.document.form.submit()
    }else {
        opener.location.href = 'itens/alterar_imprimir.php<?=$parametro;?>'
    }
}
</Script>
<body onunload='atualizar_frames_abaixo()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
/**********************************************************************************/
    //Se a NF já foi importada pelo Financeiro, mostro essa mensagem abaixo ...
    if($campos[0]['importado_financeiro'] == 'S') {
?>
    <tr class='erro' align='center'>
        <td colspan='2'>
            (NOTA FISCAL JÁ IMPORTADA PELO DEPTO. FINANCEIRO)
        </td>
    </tr>
<?
    }
/**********************************************************************************/
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <iframe name='dados_iniciais' src='dados_iniciais.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>&acao=L' frameborder='0' scrolling='no' width='100%' height='180'></iframe>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <iframe name='destinatario_remetente_fatura' src='destinatario_remetente_fatura.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>&acao=L' frameborder='0' scrolling='no' width='100%' height='290'></iframe>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <iframe name='frete' src='frete.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>&acao=L' frameborder='0' scrolling='no' width='100%' height='270'></iframe>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <iframe name='dados_gerais' src='dados_gerais.php?id_nf=<?=$_GET['id_nf'];?>&opcao=<?=$_GET['opcao'];?>&acao=L' frameborder='0' scrolling='no' width='100%' height='150'></iframe>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>