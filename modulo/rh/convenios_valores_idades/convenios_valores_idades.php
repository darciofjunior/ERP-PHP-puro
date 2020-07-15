<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>CONVÊNIO VALOR VS IDADE ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>CONVÊNIO VALOR VS IDADE EXCLUÍDO COM SUCESSO.</font>";

if(!empty($id_convenio_valor_idade)) {
    $sql = "DELETE FROM `convenios_valores_vs_idades` WHERE `id_convenio_valor_idade` = '$id_convenio_valor_idade' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 2;
}
?>
<html>
<head>
<title>.:: Convênio(s) Valor(es) vs Idade(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_convenio_valor_idade) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_convenio_valor_idade.value = id_convenio_valor_idade
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_convenio_valor_idade'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Convênio(s) Valor(es) vs Idade(s)
        </td>
    </tr>
<?
//Aqui vasculha todos os Tipos de Cliente
    $sql = "SELECT * 
            FROM `convenios_valores_vs_idades` 
            ORDER BY `convenio_valor` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            NÃO HÁ CONVÊNIO(S) VALOR(ES) VS IDADE(S) CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            <b>Convênio Valor</b>
        </td>
        <td>
            <b>Idade <</b>
        </td>
        <td width='30'>
            &nbsp;
        </td>
        <td width='30'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=number_format($campos[$i]['convenio_valor'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[$i]['idade'];?>
        </td>
        <td>
            <img src = '../../../imagem/menu/alterar.png' border='0' onclick="window.location = 'alterar.php?id_convenio_valor_idade=<?=$campos[$i]['id_convenio_valor_idade'];?>'" alt='Alterar Convênio Valor vs Idade' title='Alterar Convênio Valor vs Idade'>
        </td>
        <td>
            <img src = '../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_convenio_valor_idade'];?>')" title='Excluir Convênio Valor vs Idade' style='cursor:help'>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            <a href='incluir.php' title='Incluir Convênio(s) Valor(es) vs Idade(s)'>
                <font color='#FFFF00'>
                    Incluir Convênio(s) Valor(es) vs Idade(s)
                </font>
            </a>
        </td>
    </tr>
</table>
</form>
</body>
</html>