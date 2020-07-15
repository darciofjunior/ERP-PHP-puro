<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">TIPO DE PAGAMENTO EXCLUIDO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">TIPO DE PAGAMENTO NÃO PODE SER EXCLUÍDO, POIS CONSTA EM USO.</font>';

if(!empty($_POST['chkt_tipo_pagamento'])) {
    foreach($_POST['chkt_tipo_pagamento'] as $id_tipo_pagamento) {
        if(cascate::consultar('id_tipo_pagamento', 'nfe, antecipacoes', $id_tipo_pagamento)) {
            $valor = 2;
        }else {
            //Se tiver excluíndo o Tipo de Pagamento, então eu também deleto a Imagem correspondente a este ...
            $sql = "SELECT imagem 
                    FROM `tipos_pagamentos` 
                    WHERE `id_tipo_pagamento` = '$id_tipo_pagamento' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(!empty($campos[0]['imagem'])) unlink('../../../imagem/financeiro/tipos_pag_rec/'.$campos[0]['imagem']);
            $sql = "UPDATE `tipos_pagamentos` SET `ativo` = '0' WHERE `id_tipo_pagamento` = '$id_tipo_pagamento' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 1;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php?valor=<?=$valor;?>'
    </Script>
<?
}

//Aqui eu listo todos Pagamentos cadastrados no Sistema ...
$sql = "SELECT * 
        FROM `tipos_pagamentos` 
        WHERE `ativo` = '1' ORDER BY pagamento ";
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
<title>.:: Excluir Tipo(s) de Pagamento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name="form" method="POST" action='' onsubmit="return validar()">
<table width='60%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align="center">
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Excluir Tipo(s) de Pagamento(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>Tipo de Pagamento</td>
        <td>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" class="linhanormal">
        <td>
            <?=$campos[$i]['pagamento'];?>
        </td>
        <td align='center'>
            <input type="checkbox" name="chkt_tipo_pagamento[]" value="<?=$campos[$i]['id_tipo_pagamento'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
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
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>