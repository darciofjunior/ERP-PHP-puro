<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/data.php');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}else if($id_emp == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = "<font class='confirmacao'>CONTA(S) ATUALIZADA(S) COM SUCESSO !</font>";

if(!empty($_POST['hdd_conta_apagar'])) {
    foreach($_POST['hdd_conta_apagar'] as $i=>$id_conta_apagar) {
        $sql = "UPDATE `contas_apagares` SET `id_grupo` = '".$_POST['cmb_grupo'][$i]."' WHERE `id_conta_apagar` = '$id_conta_apagar' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Trago apenas dos últimos 3 ano(s) ...
$sql = "SELECT * 
        FROM `contas_apagares` 
        WHERE `id_grupo` = '' 
        AND `data_emissao` >= DATE_ADD(NOW(), INTERVAL -1100 DAY) 
        ORDER BY id_conta_apagar DESC ";
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Conta(s) sem Grupo(s) dos último(s) 3 Ano(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post' action=''>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Conta(s) sem Grupo(s) dos último(s) 3 ano(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º Conta
        </td>
        <td>
            Data Venc.
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Valor
        </td>
        <td>
            Grupo
        </td>
    </tr>
<?
    for($i = 0;  $i < $linhas; $i++) {
        $url = "javascript:nova_janela('../../../../compras/pedidos/itens/itens.php?id_pedido=".$campos[$i]['id_pedido']."&pop_up=1', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')";
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <a href="<?=$url;?>" title="Registrar Follow-Up do Cliente" class='link'>
                <?=$campos[$i]['numero_conta'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
            <?=number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
            <select name='cmb_grupo[]' title='Grupo' class='combo'>
            <?
                $sql = "SELECT id_grupo, nome 
                        FROM `grupos` 
                        WHERE ativo = '1' ORDER BY nome ";
                echo combos::combo($sql, $campos[$i]['id_grupo']);
            ?>
            </select>
            <input type='hidden' name='hdd_conta_apagar[]' value="<?=$campos[$i]['id_conta_apagar'];?>">
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>