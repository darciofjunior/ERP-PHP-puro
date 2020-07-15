<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/variaveis/intermodular.php');

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

if(!empty($_POST['id_nf'])) {
    //Busca de alguns dados Básicos de Nota Fiscal que serão utilizados + abaixo ...
    $sql = "SELECT IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, nfs.`id_empresa` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE nfs.`id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    $campos = bancos::sql($sql);
/*********************************************************/
/***************************GNRE**************************/
/*********************************************************/
    $numero_nf              = faturamentos::buscar_numero_nf($_POST['id_nf'], 'S');
    $calculo_total_impostos = calculos::calculo_impostos(0, $_POST['id_nf'], 'NF');
    $valor_icms_st          = $calculo_total_impostos['valor_icms_st'];

    /*Verifico se existe uma Conta à Pagar lá no Financeiro do Tipo "GNRE" para esta NF 
    na situação "não paga" ... Obs: `id_produto_financeiro` = '183' "GNRE - SUBS. TRIB." */
    $sql = "SELECT id_conta_apagar, numero_conta 
            FROM `contas_apagares` 
            WHERE `id_empresa` = '".$campos[0]['id_empresa']."' 
            AND `id_produto_financeiro` = '183' 
            AND `numero_conta` = '$numero_nf' 
            AND `valor` = '$valor_icms_st' 
            AND `status` = '0' LIMIT 1 ";
    $campos_contas_apagar = bancos::sql($sql);
    if(count($campos_contas_apagar) == 1) {//Encontrou GNRE não paga ...
        $mensagem = 'A Conta à Pagar N.º "'.$campos_contas_apagar[0]['numero_conta'].'", referente pagamento de "GNRE - SUBS. TRIB." da Nota Fiscal N.º "'.$numero_nf.'", Empresa "'.genericas::nome_empresa($campos[0]['id_empresa']).'", Cliente "'.$campos[0]['cliente'].'", Valor de R$ "'.number_format($valor_icms_st, 2, ',', '.').'", <b>PRECISA SER EXCLUÍDA</b> pois essa Nota Fiscal foi cancelada.';
        comunicacao::email('ERP - GRUPO ALBAFER', $cancelar_nota_fiscal, '', 'Excluir Pagamento da "GNRE - SUBS. TRIB."', $mensagem);
    }
/*********************************************************/
/**************************ITENS**************************/
/*********************************************************/
    //Vasculho todos os Itens da Nota Fiscal que foi submetida ...
    $sql = "SELECT id_nfs_item, id_pedido_venda_item, qtde AS qtde_nfsi, vale AS vale_nfsi 
            FROM `nfs_itens` 
            WHERE `id_nf` = '$_POST[id_nf]' ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
    for($i = 0; $i < $linhas_itens; $i++) {//Disparo do Loop ...
        $id_pedido_venda_item   = $campos_itens[$i]['id_pedido_venda_item'];
        $qtde_faturar           = $campos_itens[$i]['qtde_nfsi'];
        $vale_nfsi              = $campos_itens[$i]['vale_nfsi'];
        $diferenca              = $qtde_faturar - $vale_nfsi;
        //Apaga o Item da Nota Fiscal ...
        $sql = "DELETE FROM `nfs_itens` WHERE `id_nfs_item` = '".$campos_itens[$i]['id_nfs_item']."' LIMIT 1 ";
        bancos::sql($sql);
        //Atualiza o Item do Pedido de Vendas, reabrindo-o ...
        $sql = "UPDATE `pedidos_vendas_itens` SET `vale` = `vale` + $vale_nfsi WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
        bancos::sql($sql);
        faturamentos::controle_estoque('', $id_pedido_venda_item, $diferenca, 0, 0, 0);
    }
/*********************************************************/
/**************Atualizando Status = Cancelado*************/
/*********************************************************/
/*Atualizo a NF com a justificativa que foi preenchida pelo Usuário referente ao cancelamento da Nota Fiscal 
e mudo o Status da Nota Fiscal para Cancelada ...*/
    $sql = "UPDATE `nfs` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d')."', `status` = '5' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    bancos::sql($sql);
/*****************************************E-mail*****************************************/
/*Se o Usuário estiver cancelando a Nota Fiscal, então o Sistema dispara um e-mail informando qual a 
Nota Fiscal que está sendo cancelada*/
    $justificativa  = '<font color="blue">Follow-Up Registrado automaticamente (E-mail) </font>';
//Aqui eu trago alguns dados de Nota Fiscal p/ passar por e-mail via parâmetro ...
    $sql = "SELECT nfs.`id_cliente`, nfs.`id_cliente_contato`, nfs.`id_empresa`, c.`razaosocial` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE nfs.`id_nf`  = '$_POST[id_nf]' LIMIT 1 ";
    $campos = bancos::sql($sql);

//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
    $id_cliente         = $campos[0]['id_cliente'];
    $id_cliente_contato = $campos[0]['id_cliente_contato'];
    $id_empresa_nota    = $campos[0]['id_empresa'];
    $empresa            = genericas::nome_empresa($id_empresa_nota);
    $cliente            = $campos[0]['razaosocial'];

//Dados p/ enviar por e-mail - Controle com as Mensagens de Alteração ...
    $observacao_follow_up = $justificativa.' - Cancelamento de Nota Express - <b>Justificativa: </b>'.$_POST['txt_observacao_justificativa'];

//Registrando Follow-UP(s) ...
    $id_representante   = genericas::buscar_id_representante($id_cliente_contato);

    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', '$id_cliente_contato', '$id_representante', '$_SESSION[id_funcionario]', '$_POST[id_nf]', '5', '$observacao_follow_up', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);

/*O sistema zera os vencimentos, valores do Cabeçalho, Despesas Acessórias e Valor de Frete 
devido não existir + nenhum Item na NF ...*/
    $sql = "UPDATE `nfs` SET `despesas_acessorias` = '0.00', `valor_frete` = '0.00', `vencimento1` = '0', `valor1` = '0.00', `vencimento2` = '0', `valor2` = '0.00', `vencimento3` = '0', `valor3` = '0.00', `vencimento4` = '0', `valor4` = '0.00' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    bancos::sql($sql);
/*********************************************************/
/**************************EMAIL**************************/
/*********************************************************/
//Aqui eu busco o login de quem está cancelando a Nota Fiscal ...
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos_login       = bancos::sql($sql);
    $login_cancelando   = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
    $complemento_justificativa = '<br><b>Empresa: </b>'.$empresa.' <br><b>Cliente: </b>'.$cliente.' <br><b>N.º da Conta: </b>'.$numero_nf.' <br><b>Login: </b>'.$login_cancelando;
    $justificativa.= $complemento_justificativa.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$_POST['txt_observacao_justificativa'].'<br>'.$PHP_SELF;
//Aqui eu mando um e-mail informando quem e porque que excluiu a Nota Fiscal ...
    $mensagem = $justificativa;
    comunicacao::email('ERP - GRUPO ALBAFER', $cancelar_nota_fiscal, '', 'Cancelamento de Nota Fiscal de Saída', $mensagem);
/****************************************************************************************/
?>
    <Script Language = 'JavaScript'>
        alert('NOTA FISCAL CANCELADA COM SUCESSO !')
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
        window.close()
    </Script>
<?
}

/******************************************************************************/
/**************************Verificações antes de Cancelar**********************/
/******************************************************************************/
//1) Verifico se já foi paga a Comissão dessa Nota Fiscal que estou tentando Cancelar ...
$pago_comissao_pode_excluir = faturamentos::pago_comissao_pode_excluir($_GET['id_nf']);
if($pago_comissao_pode_excluir == 0) {//Não pode Cancelar a NF, existe Comissão paga p/ a mesma ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTA NOTA FISCAL NÃO PODE SER CANCELADA !!!\nDEVIDO TER SIDO PAGO A COMISSÃO DA MESMA, SE NECESSÁRIO A OUTRA OPÇÃO É EMITIR-SE UMA NOTA FISCAL DE ENTRADA !')
        window.location = 'outras_opcoes.php?id_nf=<?=$_GET['id_nf'];?>'
    </Script>
<?
}

//2) Verifico se essa Nota Fiscal que estou tentando cancelar, possui duplicatas já importadas no Financeiros ...
$sql = "SELECT id_conta_receber 
        FROM `contas_receberes` 
        WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos_conta_receber = bancos::sql($sql);
if(count($campos_conta_receber) == 1) {//Possui duplicata(s) já Importada(s) ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTA NOTA FISCAL TEM DUPLICATA(S) IMPORTADA(S) NO FINANCEIRO !!!\nPEÇA P/ O FINANCEIRO EXCLUIR A(S) DUPLICATA(S) P/ PODER EFETURAR O CANCELAMENTO DA MESMA !')
        window.location = 'outras_opcoes.php?id_nf=<?=$_GET['id_nf'];?>'
    </Script>    
<?
}

//3) Verifico se essa Nota Fiscal que estou tentando cancelar, possui Guia(s) de ST p/ Pagar ...
$sql = "SELECT `gnre` 
        FROM `nfs` 
        WHERE `id_nf` = '$_GET[id_nf]' 
        AND `gnre` > '0' LIMIT 1 ";
$campos_gnre = bancos::sql($sql);
if(count($campos_gnre) == 1) {//Possui GNRE ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTA NOTA FISCAL TEM GNRE !!!\nCONFIRME ANTES SE ELA NÃO FOI PAGA PELO FINANCEIRO !')
    </Script>
<?
}
?>
<html>
<head>
<title>.:: Cancelar Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Observação / Justificativa ...
    if(document.form.txt_observacao_justificativa.value == '') {
        alert('DIGITE A OBSERVAÇÃO / JUSTIFICATIVA !')
        document.form.txt_observacao_justificativa.focus()
        document.form.txt_observacao_justificativa.select()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_observacao_justificativa.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_nf' value="<?=$_GET[id_nf];?>">
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Cancelar Nota Fiscal
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação / Justificativa:</b>
        </td>
        <td>
            <textarea name='txt_observacao_justificativa' cols='60' rows='2' maxlength='255' class='caixadetexto'><?=$txt_observacao_justificativa;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' class='botao' onclick="window.location = 'outras_opcoes.php?id_nf=<?=$id_nf;?>'">
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_observacao_justificativa.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>