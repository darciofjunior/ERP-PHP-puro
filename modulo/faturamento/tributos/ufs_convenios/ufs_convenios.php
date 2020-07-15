<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = '<font class="confirmacao">UF(S) ALTERADA COM SUCESSO.</font>';
$mensagem[2] = '<font class="confirmacao">UF(S) EXCLUÍDA(S) COM SUCESSO.</font>';

//Exclui o PA que está atrelado ao Concorrente ...
if(!empty($_POST['id_uf'])) {//Exclusão dos Concorrente ...
    $sql = "UPDATE `ufs` SET `ativo` = '0' WHERE `id_uf` = '$_POST[id_uf]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 2;
}

//Aqui eu Busco todas as UF(s) Cadastrada(s) ...
$sql = "SELECT * 
        FROM `ufs` 
        WHERE `ativo` = '1' ORDER BY sigla ";
$campos = bancos::sql($sql, $inicio, 30, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: UF(s) vs Convênio(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_uf) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_uf.value = id_uf
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name="form" method="post" action='' onSubmit="return enviar()">
<!--Variáveis que são Controle de Tela-->
<input type="hidden" name="id_uf">
<!--**********************************-->
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class="atencao" align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='8'>
            UF(s) vs Convênio(s)
        </td>
    </tr>
<?
	if($linhas == 0) {
?>
    <tr class="atencao" align="center">
        <td colspan='8'>
            <font size='-1'>
                NÃO HÁ UF(S) CADASTRADA(S).
            </font>
        </td>
    </tr>
<?
    }else {
?>
    <tr class="linhanormal" align="center">
        <td bgcolor="#CCCCCC">
            <b>Sigla</b>
        </td>
        <td bgcolor="#CCCCCC">
            <b>Estado</b>
        </td>
        <td bgcolor="#CCCCCC">
            <b>Capital</b>
        </td>
        <td bgcolor="#CCCCCC">
            <b>Região</b>
        </td>
        <td bgcolor="#CCCCCC">
            <b>Código</b>
        </td>
        <td bgcolor="#CCCCCC">
            <b>Convênio</b>
        </td>
        <td width="30" bgcolor="#CCCCCC">
            &nbsp;
        </td>
        <td width="30" bgcolor="#CCCCCC">
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['estado'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['capital'];?>
        </td>
        <td>
            <?=$campos[$i]['regiao'];?>
        </td>
        <td>
            <?=$campos[$i]['codigo'];?>
        </td>
        <td>
            <?=$campos[$i]['convenio'];?>
        </td>
        <td>
            <img src="../../../../imagem/menu/alterar.png" border='0' onClick="window.location = 'alterar.php?id_uf=<?=$campos[$i]['id_uf'];?>'" alt="Alterar UF" title="Alterar UF" style="cursor:help">
        </td>
        <td>
            <img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_uf'];?>')" alt="Excluir UF" title="Excluir UF" style="cursor:help">
        </td>
    </tr>
<?
        }
    }
?>
    <tr class="linhadestaque" align="left">
        <td colspan='8'>
            <a href="incluir.php" title="Incluir UF">
                <font color="#FFFF00">
                    Incluir UF
                </font>
            </a>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>