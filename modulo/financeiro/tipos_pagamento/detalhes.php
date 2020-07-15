<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/financeiro/tipos_pagamento/consultar.php', '../../../');

$sql = "SELECT * 
        FROM `tipos_pagamentos` 
        WHERE `id_tipo_pagamento` = '$_GET[id_tipo_pagamento]' LIMIT 1 ";
$campos = bancos::sql($sql)
?>
<html>
<head>
<title>.:: Detalhes do Tipo de Pagamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class="linhacabecalho">
        <td colspan='2' align='center'>
            Detalhes do Tipo de Pagamento
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Tipo de Pagamento:
        </td>
        <td>
            <input type="text" name="txt_tipo_pagamento" value="<?=$campos[0]['pagamento'];?>" size='35' maxlength='30' class="textdisabled" disabled>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Imagem:
        </td>
        <td>
            <img src="<?='../../../imagem/financeiro/tipos_pag_rec/'.$campos[0]['imagem'];?>" width="50" height="50">
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan='2'>
        <?
            if($campos[0]['status_db'] == 1) $checked = 'checked';
        ?>
            <input type="checkbox" name="chkt_status_db" id="chkt1" value="1" class="checkbox" <?=$checked;?>>Forçar Dados Bancários<br>
        <?
            if($campos[0]['status_ch'] == 1) {
                $checked_status_ch1 = 'checked';
            }else if($campos[0]['status_ch'] == 2) {
                $checked_status_ch2 = 'checked';
            }
        ?>
            <input type="checkbox" name="chkt_status_ch" id="chkt2" value="1" class="checkbox" <?=$checked_status_ch1;?>>Forçar Bancos / Agência / Conta Corrente<br>
            <input type="checkbox" name="chkt_status_ch" id="chkt3" value="2" class="checkbox" <?=$checked_status_ch2;?>>Forçar Bancos / Agência / Conta Corrente / Cheque
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
</form>
</body>
</html>