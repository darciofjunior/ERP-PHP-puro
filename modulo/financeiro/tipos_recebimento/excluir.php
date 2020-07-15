<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>TIPO DE RECEBIMENTO EXCLUIDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>TIPO DE RECEBIMENTO NÃO PODE SER EXCLUÍDO, POIS CONSTA EM USO.</font>";

if(!empty($_POST['chkt_tipo_recebimento'])) {
    foreach($_POST['chkt_tipo_recebimento'] as $id_tipo_recebimento) {
        if(cascate::consultar('id_tipo_pagamento_recebimento', 'nfe, antecipacoes', $id_tipo_recebimento)) {
            $valor = 2;
        }else {
            $sql = "SELECT imagem 
                    FROM `tipos_recebimentos` 
                    WHERE `id_tipo_recebimento` = '$id_tipo_recebimento' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(!empty($campos[0]['imagem'])) unlink("../../../imagem/financeiro/tipos_pag_rec/".$imagem);
            $sql = "UPDATE `tipos_recebimentos` SET `ativo` = '0' WHERE `id_tipo_recebimento` = ' $id_tipo_recebimento ' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    $valor = 1;
}

//Aqui eu listo todos Recebimentos cadastrados no Sistema ...
$sql = "SELECT * 
        FROM `tipos_recebimentos` 
        WHERE `ativo` = '1' ORDER BY recebimento ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
<Script Language = 'Javascript'>
    window.location = '../../../html/index.php?valor=4'
</Script>
<?
    exit;
}
?>
<html>
<head>
<title>.:: Excluir Tipo(s) de Recebimento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
}
</script>
</head>
<body>
<form name="form" method="POST" action='' onsubmit="return validar()">
<table width='60%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class="atencao" align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Excluir Tipo(s) de Recebimento(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            Tipo de Recebimento
        </td>
        <td>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" class="linhanormal">
        <td>
            <?=$campos[$i]['recebimento'];?>
        </td>
        <td align='center'>
            <input type="checkbox" name="chkt_tipo_recebimento[]" value="<?=$campos[$i]['id_tipo_recebimento'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
	}
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="submit" name="cmd_excluir" value="Excluir" title="Excluir" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>