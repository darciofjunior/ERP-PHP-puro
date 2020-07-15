<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../../');
$mensagem[1] = '<font class="confirmacao">COMISSÃO DESCONTO EXCLUÍDA COM SUCESSO.</font>';

if(!empty($_POST['id_comissao'])) {//Exclusão das Comissões Desconto(s)
    $sql = "DELETE FROM `comissoes` WHERE `id_comissao` = '$_POST[id_comissao]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Comissões Descontos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_comissao) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_comissao.value = id_comissao
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_comissao'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Comissão(ões) Desconto(s)
        </td>
    </tr>
<?
//Aqui vasculha todas as Comissões
    $sql = "SELECT * 
            FROM `comissoes` 
            ORDER BY percentual ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='8'>
            NÃO HÁ COMISSÃO(ÕES) DESCONTO(S) CADASTRADAS.
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CCCCCC'>
            <b>Desconto Total</b>
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
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>Interno / Supervisor Interno</b>
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
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <?='< '.number_format($campos[$i]['percentual'], 2, ',', '.').'%';?>
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
            <img src='../../../../../imagem/menu/alterar.png' border='0' onclick="window.location = 'alterar_comissao.php?id_comissao=<?=$campos[$i]['id_comissao'];?>'" alt='Alterar Comissão Desconto' title='Alterar Comissão Desconto'>
        </td>
        <td>
            <img src='../../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_comissao'];?>')" alt='Excluir Comissão Desconto' title='Excluir Comissão Desconto'>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='8'>
            <a href='incluir_comissao.php' title='Incluir Comissão Desconto'>
                <font color='#FFFF00'>
                    Incluir Comissão Desconto
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
</pre>
</body>
</html>