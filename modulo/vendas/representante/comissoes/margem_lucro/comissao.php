<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../../');
$mensagem[1] = '<font class="confirmacao">COMISSÃO MARGEM DE LUCRO EXCLUÍDA COM SUCESSO.</font>';

if(!empty($_POST[id_comissao_margem_lucro])) {//Exclusão das Comissões Margens de Lucro
    $sql = "DELETE FROM `comissoes_margens_lucros` WHERE `id_comissao_margem_lucro` = '$id_comissao_margem_lucro' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Comissões Margens de Lucro ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_comissao_margem_lucro) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_comissao_margem_lucro.value = id_comissao_margem_lucro
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_comissao_margem_lucro'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Comissão(ões) Margem(ns) de Lucro(s)
        </td>
    </tr>
<?
//Aqui vasculha todas as Comissões Margens de Lucro ...
    $sql = "SELECT * 
            FROM `comissoes_margens_lucros` 
            ORDER BY percentual ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='8'>
            NÃO HÁ COMISSÃO(ÕES) MARGEM(NS) DE LUCRO(S) CADASTRADAS.
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CCCCCC'>
            <b>Margem de Lucro</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Comissão Vendedor</b>
        </td>
        <td colspan='4' bgcolor='#CCCCCC'>
            <b>Comiss&atilde;o Vendedor  Externo ou Representante </b>
        </td>
        <td width='30' rowspan='2' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td width='30' rowspan='2' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal' align="center">
        <td bgcolor='#CCCCCC'>
            <b>Interno / Sup. Interno</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Dentro de SP </b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Fora do Estado</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>No Interior</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Autônomo</b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?='> '.number_format($campos[$i]['percentual'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['com_perc_interno'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['com_perc_externo'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['com_perc_externo'] - 1.0, 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['com_perc_externo_esp'] - 0.5, 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['com_perc_externo_esp'], 2, ',', '.').'%';?>
        </td>
        <td>
            <img src='../../../../../imagem/menu/alterar.png' border='0' onclick="window.location = 'alterar_comissao.php?id_comissao_margem_lucro=<?=$campos[$i]['id_comissao_margem_lucro'];?>'" alt='Alterar Comissão' title='Alterar Comissão'>
        </td>
        <td>
            <img src='../../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_comissao_margem_lucro'];?>')" alt='Excluir Comissão' title='Excluir Comissão'>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='8'>
            <a href='incluir_comissao.php' title='Incluir Comissão Margem de Lucro'>
                <font color='#FFFF00'>
                    Incluir Comissão Margem de Lucro
                </font>
            </a>
        </td>
    </tr>
</table>
</form>
<pre>
<font color='=blue'>Tabelas Virtuais:</font>
	- Comiss&atilde;o Vendedor Externo Fora do Estado = (Comiss&atilde;o Vendedor Externo - SP) - 1,00% 
	- Comiss&atilde;o Vendedor Externo no Interior = (Comiss&atilde;o Representantes Aut&ocirc;nomo:) - 0,50%
	- Mercedes recebe comissão interna + 1,00%
	- Vendedor externo e supervisor seguem pelo mesmo caminho.
    - A diferença é que o supervisor o sistema da 1% da venda de seus subordinados no relatório.
</pre>
</body>
</html>