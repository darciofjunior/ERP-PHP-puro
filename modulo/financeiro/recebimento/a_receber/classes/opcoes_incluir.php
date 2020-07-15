<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
?>
<html>
<head>
<title>.:: Opções de Conta à Receber ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    if(document.form.opt_item[0].checked == true) {//Liberar NF de Saída ...
        window.location = 'liberar_nota/incluir_nfs_saida.php'
    }else if(document.form.opt_item[1].checked == true) {//Liberar Outras NF de Saída ...
        window.location = 'liberar_nota/incluir_nfs_outras.php'
    }else if(document.form.opt_item[2].checked == true) {//Liberar Nota de Devolução ...
        window.location = 'liberar_nota_devolucao/incluir_nota.php'
    }else {//Incluir Crédito(s) Finaceiro(s) ...
        window.location = 'credito_debito_financeiro/incluir.php'
    }
}
</Script>
</head>
<body>
<form name='form' method='post'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align="center">
        <td>
            Opções de Conta à Receber 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
<?
/*Aqui todas as Notas Fiscais que já podem ser liberadas da mesma empresa do Menu,
nas condições de Liberada / Empacotada ou Despachada, só exibe Notas a partir do Mês de Maio
e que tenham valor de Faturamento > R$ 0,00*/
        /*Aqui exibo todas as NFs que já podem ser liberadas da mesma empresa do Menu, nas condições de "Liberada / Empacotada ou Despachada", 
à partir de 01/05/2006, que tenham valor de Faturamento > R$ 0,00 e que estejam com a Marcação de Gerar Duplicatas ...*/
        $sql = "SELECT COUNT(nfs.`id_nf`) AS qtde_nfs 
                FROM `nfs` 
                INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` 
                INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                WHERE nfs.`id_empresa` = '$id_emp' 
                AND nfs.`data_emissao` >= '2006-05-01' 
                AND nfs.`valor1` <> '0' 
                AND nfs.`status` IN (2, 3, 4) 
                AND nfs.`importado_financeiro` = 'N' 
                AND nfs.`gerar_duplicatas` = 'S' 
                ORDER BY nnn.`numero_nf` ";
        $campos     = bancos::sql($sql);
        $qtde_nfs   = $campos[0]['qtde_nfs'];
?>
    <tr class='linhanormal'>
        <td>
            <input type='radio' id='opt1' name='opt_item' value='1' title='Liberar NF de Saída' id='opt1' checked>
            <label for='opt1'>Liberar NF de Saída - <b>(<?=$qtde_nfs;?>)</b></label>
        </td>
    </tr>
<?
/*Aqui todas as Notas Fiscais que já podem ser liberadas da mesma empresa do Menu,
nas condições de Liberada / Empacotada ou Despachada, só exibe Notas a partir do Mês de Maio
e que tenham valor de Faturamento > R$ 0,00*/
        $sql = "SELECT id_nf_outra 
                FROM `nfs_outras` 
                WHERE gerar_duplicatas = 'S' 
                AND `id_empresa` = '$id_emp' 
                AND `status` IN (2, 3, 4) 
                AND `importado_financeiro` = 'N' 
                AND `valor1` <> '0' ";
        $campos     = bancos::sql($sql);
        $qtde_nfs   = count($campos);
?>
    <tr class='linhanormal'>
        <td>
            <input type='radio' id='opt2' name='opt_item' value='2' title='Liberar Outra(s) NF de Saída' id='opt2'>
            <label for='opt2'>Liberar Outras NF de Saída - <b>(<?=$qtde_nfs;?>)</b></label>
        </td>
    </tr>
<?
/*Aqui todas as Notas Fiscais que são do Tipo Devolução, só exibe Notas a partir do Mês de 
Maio, que tenham valor de Faturamento > R$ 0,00 e que possuem algum 
N.º de NF de Devolução - independente de ser Nosso N.º ou N.º do Cliente*/
        $sql = "SELECT COUNT(id_nf) AS qtde_nfs 
                FROM `nfs` 
                WHERE `id_empresa` = '$id_emp' 
                AND nfs.`valor1` <> '0' 
                AND nfs.`status` = '6' 
                AND nfs.`importado_financeiro` = 'N' 
                AND nfs.`devolucao_faturada` = 'S' 
                AND (nfs.`id_nf_num_nota` <> '0' OR nfs.`snf_devolvida` <> '') ";
        $campos = bancos::sql($sql);
        $qtde_nfs = $campos[0]['qtde_nfs'];
?>
    <tr class='linhanormal'>
        <td>
            <input type='radio' id='opt3' name='opt_item' value='3' title='Liberar NF de Devolução' id='opt3'>
            <label for='opt3'>Liberar NF de Devolução - <b>(<?=$qtde_nfs;?>)</b></label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='1' title='Incluir Crédito(s) / Débito(s) Financeiro(s)' id='opt4'>
            <label for='opt4'>Incluir Crédito(s) / Débito(s) Financeiro(s)</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>