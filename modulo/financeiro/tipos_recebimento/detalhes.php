<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/financeiro/tipos_recebimento/consultar.php', '../../../');

$sql = "SELECT * 
        FROM `tipos_recebimentos` 
        WHERE `id_tipo_recebimento` = '$_GET[id_tipo_recebimento]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Detalhes Tipo de Recebimento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Detalhes do Tipo de Recebimento
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Tipo de Recebimento: 
        </td>
        <td>
            <input type="text" name="txt_tipo_recebimento" value="<?=$campos[0]['recebimento'];?>" size='55' maxlength='50' class="textdisabled" disabled>
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan='2'>
        <?
            if($campos[0]['status'] == 1) $checked = 'checked';
        ?>
            <input type="checkbox" name="chkt_forcar" value="1" id="forcar" <?=$checked;?> class="checkbox" disabled>
            <label for="forcar">Forçar Banco</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan='2'>
            Imagem:
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan='2'>
            <img src='<?='../../../imagem/financeiro/tipos_pag_rec/'.$campos[0]['imagem'];?>' width="50" height="50">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Observação:
        </td>
        <td>
            <textarea name="txt_observacao" title="Digite a Observação" rows='1' cols='80' class="textdisabled" disabled><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
                &nbsp;
        </td>
    </tr>
</table>
</body>
</html>