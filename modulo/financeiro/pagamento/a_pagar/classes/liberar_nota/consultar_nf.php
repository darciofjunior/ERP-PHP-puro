<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');
$mensagem[1] = "<font class='confirmacao'>CONTA À PAGAR INCLUIDA COM SUCESSO.</font>";

/*Ignoro esse período que vai até o fim do ano de 2006, porque as contas que estão nesse período se encontram 
com alguns problemas devido ter sido na época de implantação do Sistema entre o módulo de Compras e do módulo 
Financeiro que ainda não estavam tão sincronizados, ao que se refere a toda essa parte de Notas Fiscais de 
Entrada - Duplicatas à Pagar ... */
$datas_ignorar = " AND nfe.`data_emissao` NOT BETWEEN '2004-01-01' AND '2006-12-31' ";

//Se a Empresa for Albafer ou Tool Master ou Grupo ...
$tipo_nf = ($id_emp == 1 || $id_emp == 2) ? " AND nfe.`tipo` = '1' " : " AND nfe.`tipo` = '2' ";

//Aqui eu listo todas as Notas Fiscais que ainda não foram importadas, da respectiva Empresa do Menu ...
$sql = "SELECT DISTINCT(nfe.`id_nfe`), nfe.*, f.`razaosocial`, e.`nomefantasia`, f.`id_pais` 
        FROM `nfe` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
        INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
        WHERE nfe.`situacao` = '2' 
        AND nfe.`importado_financeiro` = 'N' 
        AND nfe.`id_empresa` = '$id_emp' 
        $tipo_nf 
        $datas_ignorar ORDER BY f.`razaosocial` ";
$campos     = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas     = count($campos);
if($linhas  == 0) {//Não encontrou nenhum registro ...
?>
    <Script Language = 'Javascript'>
        window.location = '../opcoes.php?valor=1'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Nota de Compras ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'Javascript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP, tem um controle um pouquinho diferente
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
//Variável referente ao Frame de Baixo
    var recarregar = window.opener.parent.itens.document.form.recarregar.value
    if(recarregar == 1) {
        if(typeof(window.opener.parent.itens.document.form) == 'object') {
            window.opener.parent.itens.document.location = '../itens.php'+window.opener.parent.itens.document.form.parametro.value
        }
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr> 
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Nota(s) de Compras <?=genericas::nome_empresa($id_emp);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Nota
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Tipo
        </td>
        <td>
            Data Emissão
        </td>
        <td>
            <img src = '../../../../../../imagem/propriedades.png' width='16' height='16' border='0'>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = 'incluir.php?id_nfe='.$campos[$i]['id_nfe'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width="10">
            <a href="<?=$url;?>">
                <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
            <?
                if($campos[$i]['id_pais'] != 31) {//Aqui é somente para fornecedores que são do tipo internacional ...
                    //Busco o nome da Importação ...
                    $sql = "SELECT i.`nome` 
                            FROM `nfe` 
                            INNER JOIN `importacoes` i ON i.`id_importacao` = nfe.`id_importacao` 
                            WHERE nfe.`id_nfe` = '".$campos[$i]['id_nfe']."' ";
                    $campos_importacao = bancos::sql($sql);
                    //Se encontrar Importação, então eu concateno essa junto do N.º da Conta ...
                    if(count($campos_importacao) == 1) echo $campos_importacao[0]['nome'].' - ';
                }
                echo $campos[$i]['num_nota'];
            ?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo'] == '1') {
                echo 'NFE';
            }else {
                echo 'SGD';
            }
        ?>
        </td>
        <td>
            <?= data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../../../compras/pedidos/nota_entrada/itens/itens.php?id_nfe=<?=$campos[$i]['id_nfe'];?>&pop_up=1', 'DETALHES', 'F')">
                <img src = '../../../../../../imagem/propriedades.png' width='16' height='16' border='0'>
            </a>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = '../opcoes.php'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>