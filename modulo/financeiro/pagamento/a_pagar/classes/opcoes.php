<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp == 1) {//Albafer
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {//Tool Master
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {//Grupo
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}else if($id_emp == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Opções de Conta à Pagar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function submeter() {
    document.form.nf.value = (document.form.chkt_nf.checked == true) ? 1 : 0
    document.form.submit()
}

function avancar() {
    if(document.form.opt_item[0].checked == true) {
        window.location = 'liberar_nota/consultar_nf.php'
    }else if(document.form.opt_item[1].checked == true) {
        window.location = 'liberar_antecipacoes/consultar_antecipacao.php'
    }else if(document.form.opt_item[2].checked == true) {
        window.location = 'liberar_pedidos/consultar_pedido.php'
    }else if(document.form.opt_item[3].checked == true) {
        window.location = 'liberar_comissoes/liberar_comissoes.php'
    }else {
        window.location = 'cadastrar_conta/consultar_fornecedor.php'
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='70%' cellpadding="1" cellspacing='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Opções de Conta à Pagar 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='1' title='Liberar Nota' id='opt1' checked>
            <label for='opt1'>Liberar Nota de Compras (Compras)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
        <?
            $condicao = " AND SUBSTRING(p.`data_emissao`, 1, 10) > '2004-12-01' ";
            
            //Verifica se tem antecipações pendentes ...
            $sql = "SELECT COUNT(DISTINCT(a.`id_antecipacao`)) AS total_registro 
                    FROM `pedidos` p 
                    INNER JOIN `antecipacoes` a ON a.`id_pedido` = p.`id_pedido` AND a.`status_financeiro` = '0' 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
                    WHERE p.ativo = '1' ";
            if($id_emp == 4) {//Significa que a Empresa do Menu escolhido no Financeiro foi Grupo ...
                $sql.= " AND p.`tipo_nota` = '2' $condicao ORDER BY p.`data_emissao` DESC ";
            }else { //caso for alba ou tool
                $sql.= " AND p.`id_empresa` = '$id_emp' AND p.`tipo_nota` = '1' $condicao ORDER BY p.`data_emissao` DESC ";
            }
            $campos         = bancos::sql($sql);
            $total_registro = $campos[0]['total_registro'];
            $disabled       = ($total_registro == 0) ? 'disabled' : '';
        ?>
            <input type='radio' name='opt_item' value='2' title='Liberar Antecipações' id='opt2' <?=$disabled;?>>
            <label for='opt2'>Liberar Antecipações de Compras (Compras)</label>
            &nbsp;-&nbsp;<b>(<?=$total_registro;?>)</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
        <?
            //Busca todos os pedidos já importados ...
            $sql = "SELECT DISTINCT(`id_pedido`) AS id_pedidos 
                    FROM `contas_apagares` 
                    WHERE `id_pedido` > '0' ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) $id_pedidos.=$campos[$i]['id_pedidos'].', ';
            $id_pedidos = substr($id_pedidos, 0, strlen($id_pedidos) - 2);
            
            //Aqui para o caso de ele não encontrar nenhum pedido relacionado
            if(empty($id_pedidos)) $id_pedidos = 0;
            $sql = "SELECT COUNT(DISTINCT(p.`id_pedido`)) AS total_registro 
                    FROM `pedidos` p 
                    INNER JOIN `importacoes` i ON i.`id_importacao` = p.`id_importacao` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` AND f.`id_pais` <> '31' 
                    INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
                    WHERE p.`id_pedido` NOT IN ($id_pedidos) 
                    AND p.`ativo` = '1' ";
            if($id_emp == 4) {//Significa que a Empresa do Menu escolhido no Financeiro foi Grupo ...
                $sql.= " AND p.`status` = '2' AND p.`tipo_nota` = '2' ORDER BY p.`data_emissao` DESC ";
            }else {
                $sql.= " AND p.`id_empresa` = '$id_emp' AND p.`status` = '2' AND p.`tipo_nota` = '1' ORDER BY p.`data_emissao` DESC ";
            }
            
            $campos         = bancos::sql($sql);
            $total_registro = $campos[0]['total_registro'];
            $disabled       = ($total_registro == 0) ? 'disabled' : '';
        ?>
            <input type='radio' name='opt_item' value='3' title='Liberar Numerário de Importação - Pedidos' <?=$disabled;?> id='opt3'>
            <label for='opt3'>Liberar Numerário de Importação - Pedidos (Compras)</label>
            &nbsp;-&nbsp;<b>(<?=$total_registro;?>)</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='4' title='Liberar Comissão(ões)' id='opt4'>
            <label for='opt4'>Liberar Comissão(ões)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                /*Quando for "Todas as Empresas", mantenho essa opção travada porque sempre tenho que 
                criar uma Conta p/ a Empresa específica do Menu, o que não é o caso dessa vez ...*/
                if($id_emp == 0) $disabled_opt_item5 = 'disabled';
            ?>
            <input type='radio' name='opt_item' value='5' title='Incluir Conta à Pagar' id='opt5' <?=$disabled_opt_item5;?>>
            <label for='opt5'>Incluir Conta à Pagar (Financeiro)</label>
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