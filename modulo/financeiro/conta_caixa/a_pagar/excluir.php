<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');
session_start('funcionarios');
$mensagem[1] = "<font class='confirmacao'>CONTA CAIXA EXCLUIDA COM SUCESSO. </font>";

/***********************Aqui é o Delete da Conta Caixa***********************/
if(!empty($_POST['chkt_conta_caixa_pagar'])) {
    foreach($_POST['chkt_conta_caixa_pagar'] as $id_conta_caixa_pagar) {
        $sql = "UPDATE `contas_caixas_pagares` SET `ativo` = '0' WHERE `id_conta_caixa_pagar` = '$id_conta_caixa_pagar' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}
/****************************************************************************/
$sql = "SELECT ccp.id_conta_caixa_pagar, ccp.conta_caixa, ccp.descricao, m.modulo 
        FROM `contas_caixas_pagares` ccp 
        INNER JOIN `modulos` m ON m.id_modulo = ccp.id_modulo 
        WHERE ccp.`ativo` = '1' ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../../html/index.php?valor=4'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Conta(s) Caixa(s) à Pagar(es) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name="form" method='post' action='' onsubmit="return validar()">
<table width='70%' border="0" cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)" align="center">
	<tr align='center'>
		<td colspan='4'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='4'>
                    Excluir Conta(s) Caixa(s) à Pagar(es)
		</td>
	</tr>
	<tr class="linhadestaque" align='center'>
            <td>
                Conta Caixa
            </td>
            <td>
                Módulo
            </td>
            <td>
                Descrição
            </td>
            <td>
                <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
                <label for='todos'>Todos </label>
            </td>
	</tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
            <td align="left">
                <?=$campos[$i]['conta_caixa'];?></div>
            </td>
            <td>
                <?=$campos[$i]['modulo'];?>
            </td>
            <td align="left">
                <?=$campos[$i]['descricao'];?>
            </td>
            <td>
                <input type='checkbox' name='chkt_conta_caixa_pagar[]' value="<?=$campos[$i]['id_conta_caixa_pagar'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
            </td>
	</tr>
<?
        }
?>
	<tr class="linhacabecalho" align="center">
            <td colspan="4">
                <input type="submit" name="cmd_excluir" value="Excluir" title="Excluir" class="botao">
            </td>
	</tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?}?>