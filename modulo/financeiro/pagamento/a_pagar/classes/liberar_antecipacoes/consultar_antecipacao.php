<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');
if($id_emp == 1) {
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
	$endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>ANTECIPAÇÕES LIBERADAS COM SUCESSO.</font>";
$mensagem[3] = "<font class='atencao'>NÃO HÁ ANTECIPAÇÃO(ÕES) PARA ESSE PEDIDO.</font>";

if($passo == 1) {
    $sql = "SELECT CONCAT(tm.`simbolo`, ' ') AS moeda 
            FROM `pedidos` p 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = p.`id_tipo_moeda` 
            WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
    $campos_moeda   = bancos::sql($sql);
    $moeda          = $campos_moeda[0]['moeda'];

    $sql = "SELECT a.`id_antecipacao`, a.`id_pedido`, a.`valor`, DATE_FORMAT(SUBSTRING(a.`data_sys`, 1, 10), '%d/%m/%Y') AS data_emissao, 
            DATE_FORMAT(SUBSTRING(a.`data`, 1, 10), '%d/%m/%Y') AS data_vencimento, a.`observacao`, tp.`pagamento` 
            FROM `antecipacoes` a 
            INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = a.`id_tipo_pagamento_recebimento` 
            WHERE a.`id_pedido` = '$id_pedido' 
            AND a.`status_financeiro` = '0' ORDER BY a.`data` ";
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'consultar_antecipacao.php?nf=<?=$nf;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Antecipação de Compras ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Antecipação de Compras do Pedido N.º <?=$id_pedido;?>
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Ant
        </td>
        <td>
            Pagamento
        </td>
        <td>
            Data Emissão
        </td>
        <td>
            Data Venc.
        </td>
        <td>
            Valor
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'incluir.php?id_antecipacao='.$campos[$i]['id_antecipacao'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href="<?=$url;?>" class='link'>
                <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center'>
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['id_antecipacao'];?>
            </a>
        </td>
        </td>
        <td>
            <?=$campos[$i]['pagamento'];?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
            <?=$campos[$i]['data_vencimento'];?>
        </td>
        <td>
            <?=$moeda.str_replace('.', ',', $campos[$i]['valor']);?>
        </td>
        <td>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar_antecipacao.php?nf=<?=$nf;?>'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else {
    $condicao = " AND SUBSTRING(p.`data_emissao`, 1, 10) > '2004-12-01' ";
//Verifica se tem antecipações pendentes
    $sql = "SELECT DISTINCT(a.`id_pedido`), p.`tipo_nota`, p.`data_emissao`, f.`id_fornecedor`, f.`razaosocial`, e.`nomefantasia` 
            FROM `pedidos` p 
            INNER JOIN `antecipacoes` a ON a.`id_pedido` = p.`id_pedido` AND a.`status_financeiro` = '0' 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
            INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
            WHERE p.`ativo` = '1' ";
    if($id_emp == 4) {//Significa que a Empresa do Menu escolhido no Financeiro foi Grupo ...
        if($nf == 1) { //Significa que traz os dois tipos
            $sql.= " AND p.`tipo_nota` = '1' $condicao ORDER BY p.`data_emissao` DESC ";
        }else {
            $sql.= " AND p.`tipo_nota` = '2' $condicao ORDER BY p.`data_emissao` DESC ";
        }
    }else { //caso for alba ou tool
        $sql.= " AND p.`id_empresa` = '$id_emp' AND p.`tipo_nota` = '1' $condicao ORDER BY p.`data_emissao` DESC ";
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('NO MOMENTO, NÃO EXISTE(M) ANTECIPAÇÃO(ÕES) PARA SER(EM) INCLUÍDA(S) !')
        window.opener.parent.itens.document.location = '../itens.php?parametro='+window.opener.parent.itens.document.form.parametro.value
        window.close()
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Antecipação de Compras ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr> 
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Consultar Antecipação(ões) de Compras
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Pedido
        </td>
        <td>
            Data / Hora <br>da Emissão
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Qtde. de <br>Antecip.
        </td>
        <td>
            Qtde. <br>Pendente
        </td>
        <td>
            Tipo
        </td>
        <td>
            Empresa
        </td>
        <td>
            <img src = '../../../../../../imagem/propriedades.png' width='16' height='16' border='0'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'consultar_antecipacao.php?passo=1&nf='.$nf.'&id_pedido='.$campos[$i]['id_pedido'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href="<?=$url?>">
                <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='<?=$url?>' class='link'>
                <?=$campos[$i]['id_pedido'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/').' - '.substr($campos[$i]['data_emissao'], 11, 8);?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            //Busco a Quantidade de Antecipações existentes no Pedido do Loop ...
            $sql = "SELECT COUNT(`id_antecipacao`) AS qtde_antecipacoes 
                    FROM `antecipacoes` 
                    WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' ";
            $campos_antecipacao = bancos::sql($sql);
            echo $campos_antecipacao[0]['qtde_antecipacoes'];
        ?>
        </td>
        <td>
        <?
            //Busco a Quantidade de Antecipações pendentes no Pedido do Loop ...
            $sql = "SELECT COUNT(`id_antecipacao`) AS qtde_antecipacoes_pendentes 
                    FROM `antecipacoes` 
                    WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' 
                    AND `status_financeiro` = '0' ";
            $campos_antecipacao = bancos::sql($sql);
            echo $campos_antecipacao[0]['qtde_antecipacoes_pendentes'];
        ?>
        </td>
        <td align='left'>
        <?
            $vetor_tipo_nota = array('', 'NF', 'SGD');
            echo $vetor_tipo_nota[$campos[$i]['tipo_nota']];
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../../../classes/fornecedor/alterar.php?passo=1&id_fornecedor=<?=$campos[$i]['id_fornecedor'];?>&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')">
                <img src="../../../../../../imagem/propriedades.png" width='16' height='16' border='0'>
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = '../opcoes.php?nf=<?=$nf;?>'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>