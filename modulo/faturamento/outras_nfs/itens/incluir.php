<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca � requerida dentro do Faturamento ...
require('../../../../lib/data.php');//Essa biblioteca � requerida dentro do Financeiro ...
require('../../../../lib/faturamentos.php');
require('../../../../lib/financeiros.php');//Essa biblioteca � requerida dentro do Faturamento ...
require('../../../../lib/genericas.php');//Essa biblioteca � requerida dentro do Faturamento ...
require('../../../../lib/intermodular.php');//Essa biblioteca � requerida dentro do C�lculos ...
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

//Logo de cara j� verifico se est� Nota j� foi importada p/ o Financeiro ...
$importado_financeiro = faturamentos::importado_financeiro_outras_nfs($_GET['id_nf_outra']);
if($importado_financeiro == 'S') {//Significa que a NF j� est� importada no Financeiro ...
    echo '<font color="red"><div align="center"><b>EST� NF N�O PODE SER + ALTERADA DEVIDO ESTAR IMPORTADA NO FINANCEIRO !</b></div></font>';
    exit;
}

/*****************************************************************************/
/*********************************Controles***********************************/
/*****************************************************************************/
//Aqui eu verifico quem � o Cliente da Nota Fiscal Outra e se na mesma est� marcada a op��o de "Gerar Duplicatas" ...
$sql = "SELECT `id_cliente`, `id_empresa`, `id_cfop`, `id_nf_comp`, `id_nf_outra_comp`, `gerar_duplicatas` 
        FROM `nfs_outras` 
        WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
$campos = bancos::sql($sql);

/*****************************************************************************/
/******************************Controle de CFOP*******************************/
/*****************************************************************************/
/*Aqui eu verifico se existe uma CFOP preenchida p/ o $id_nf_outra passado por par�metro, mas s� farei esse 
controle somente se a NF for da Albafer ou da Tool Master e que n�o seja Complementar ...*/
if($campos[0]['id_empresa'] != 4 && $campos[0]['id_cfop'] == 0 && $campos[0]['id_nf_comp'] == 0 && $campos[0]['id_nf_outra_comp'] == 0) {
?>
    <Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        alert('N�O FOI DEFINIDA NENHUMA CFOP P/ ESTA NF !\n� NECESS�RIO COLOCAR UMA CFOP ANTES DE INCLUIR O(S) ITEM(NS) !')
/*Aqui eu passo a seguranca como sendo 1, porque somente no primeiro Menu que eu posso 
incluir Itens de Nota Fiscal*/
        nova_janela('../alterar_cabecalho.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>', 'POP', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')
        window.close()
    </Script>
<?
    exit;
}

/*****************************************************************************/
/*****************************Controle de Cr�dito*****************************/
/*****************************************************************************/
//Se a op��o de "Gerar Duplicatas" estiver marcada, ent�o fa�o controle de Cr�dito antes de Incluir os Itens ...
if($campos[0]['gerar_duplicatas'] == 'S') {
    $retorno_analise_credito    = faturamentos::analise_credito_cliente($campos[0]['id_cliente']);
    $credito                    = $retorno_analise_credito['credito'];
    if($credito == 'C' || $credito == 'D') {//O Cliente jamais pode faturar uma NF caso possua o seu cr�dito como sendo C ou D ...
?>
        <Script Language = 'JavaScript'>
            alert('CLIENTE COM CR�DITO <?=$credito;?> !!!\n<?=$retorno_analise_credito['historico_cliente_em_js'];?>')
            window.close()
        </Script>
<?
        exit;
    }else if($credito == 'B') {
        $credito_comprometido   = $retorno_analise_credito['credito_comprometido'];
        $tolerancia_cliente     = $retorno_analise_credito['tolerancia_cliente'];
        //N�o posso incluir mais Itens nessa NF p/ o Cliente, pois o mesmo est� com o Saldo devedor ...
        if($credito_comprometido > $tolerancia_cliente) {
?>
        <Script Language = 'JavaScript'>
            alert('<?=$retorno_analise_credito['historico_cliente_em_js'];?>')
            window.close()
        </Script>
<?
        }
    }
}

$status = faturamentos::situacao_outras_nfs($_GET['id_nf_outra']);
//Fun��o q verifica se a Nota est� liberada_para_faturar, faturada, empacotada, despachada, cancelada
//caso sim, ent�o o usu�rio n�o pode + incluir, alterar ou excluir nenhum item
if($status >= 1) {//Est� liberado, ent�o � posso Incluir + nada ...
    $disabled = 'disabled';
    $checked = '';
}else {
    $checked = 'checked';
}
?>
<html>
<head>
<title>.:: Incluir Item(ns) de NF Outra(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    if(document.form.opt_opcao[0].checked == true) {//Incluir PA ...
        window.location = 'incluir_pa.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'
    }else if(document.form.opt_opcao[1].checked == true) {//Incluir PI ...
        window.location = 'incluir_pi.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'
    }else if(document.form.opt_opcao[2].checked == true) {//Incluir Manual ...
        window.location = 'incluir_manual.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'
    }else if(document.form.opt_opcao[3].checked == true) {//Importar OS ...
        window.location = 'importar_os.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'
    }else {//Se n�o estiver nenhum op��o selecionada, ent�o ...
        alert('SELECIONE UMA OP��O !')
        return false
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Item(ns) de NF Outra(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="1" title="Incluir PA" id='label1' <?=$disabled;?> <?=$checked;?>>
            <label for='label1'>Incluir PA</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="2" title="Incluir PI" id='label2' <?=$disabled;?> <?=$checked;?>>
            <label for='label2'>Incluir PI</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="3" title="Incluir Manual" id='label3' <?=$disabled;?> <?=$checked;?>>
            <label for='label3'>Incluir Manual</label>
        </td>
    </tr>
<?
//Se essa NF possuir pelo menos 1 item, ent�o eu j� n�o posso importar mais nenhuma OS ...
    $sql = "SELECT id_nf_outra_item 
            FROM `nfs_outras_itens` 
            WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos_itens = bancos::sql($sql);
    //Se existir 1 item, trava o Option de Importar OS ...
    if(count($campos_itens) == 1) $disabled = 'disabled';
?>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="4" title="Importar OS" id='label4' <?=$disabled;?>>
            <label for='label4'>Importar OS</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avan�ar &gt;&gt;' title='Avan�ar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style="color:red" onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>