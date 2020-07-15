<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
session_start('funcionarios');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">TIPO DE PAGAMENTO INCLUIDO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">TIPO DE PAGAMENTO J¡ EXISTENTE.</font>';

if(!empty($_POST['txt_tipo_pagamento'])) {
    //Verifico se j· existe algum Tipo de Pagamento cadastrado com o nome digitado pelo Usu·rio ...
    $sql = "SELECT id_tipo_pagamento 
            FROM `tipos_pagamentos` 
            WHERE `pagamento` = '$_POST[txt_tipo_pagamento]' 
            AND `ativo` = '1' LIMIT 1";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        require('../../../lib/mda.php');
//Fazendo Upload da Imagem para o Servidor ...
        switch($_FILES['txt_imagem']['type']) {
            case 'image/gif':
                $imagem = copiar::copiar_arquivo('../../../imagem/financeiro/tipos_pag_rec/', $_FILES['txt_imagem']['tmp_name'], $_FILES['txt_imagem']['name'], $_FILES['txt_imagem']['size'], $_FILES['txt_imagem']['type'], '2');
            break;
            case 'image/pjpeg':
                $imagem = copiar::copiar_arquivo('../../../imagem/financeiro/tipos_pag_rec/', $_FILES['txt_imagem']['tmp_name'], $_FILES['txt_imagem']['name'], $_FILES['txt_imagem']['size'], $_FILES['txt_imagem']['type'], '2');
            break;
            case 'image/jpeg':
                $imagem = copiar::copiar_arquivo('../../../imagem/financeiro/tipos_pag_rec/', $_FILES['txt_imagem']['tmp_name'], $_FILES['txt_imagem']['name'], $_FILES['txt_imagem']['size'], $_FILES['txt_imagem']['type'], '2');
            break;
            case 'image/x-png':
                $imagem = copiar::copiar_arquivo('../../../imagem/financeiro/tipos_pag_rec/', $_FILES['txt_imagem']['tmp_name'], $_FILES['txt_imagem']['name'], $_FILES['txt_imagem']['size'], $_FILES['txt_imagem']['type'], '2');
            break;
            case 'image/bmp':
                $imagem = copiar::copiar_arquivo('../../../imagem/financeiro/tipos_pag_rec/', $_FILES['txt_imagem']['tmp_name'], $_FILES['txt_imagem']['name'], $_FILES['txt_imagem']['size'], $_FILES['txt_imagem']['type'], '2');
            break;
            default:
            break;
        }
        $status_db  = (empty($_POST[chkt_status_db])) ? 0 : $_POST['chkt_status_db'];
        $status_ch  = (empty($_POST[chkt_status_ch])) ? 0 : $_POST['chkt_status_ch'];
        $observacao = strtolower($_POST['txt_observacao']);
        //InserÁ„o do Tipo de Pagamento no Banco de Dados ...
        $sql = "INSERT INTO `tipos_pagamentos` (`id_tipo_pagamento`, `pagamento`, `imagem`, `observacao`, `data_sys`, `status_db`, `status_ch`) VALUES (NULL, '$_POST[txt_tipo_pagamento]', '$imagem', '$observacao', '".date('Y-m-d H:i:s')."', '$status_db', '$status_ch') ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Tipo(s) de Pagamento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tipo de Pagamento ...
    if(!texto('form', 'txt_tipo_pagamento', '2', '1234567890qwertyuiopÁlkjhgfdsazxcvbnm<>QWERTYUIOP«LKJHGFDSAZXCVBNM ·ÈÌÛ˙¡…Õ”⁄‚ÍÓÙ˚¬ Œ‘€„√ı’¸‹‡¿!@#$%®&™∫∞/*():;.π≤≥£¢¨,', 'TIPO DE PAGAMENTO', '2')) {
        return false
    }
//Imagem ...
    if(document.form.txt_imagem.value == '') {
        alert('DIGITE O ENDERE«O DA IMAGEM OU PROCURE A IMAGEM !')
        document.form.txt_imagem.focus()
        return false
    }
}

function controle(elemento) {
    if(elemento.checked == true) {
        if(elemento.value == 1) {
            document.form.chkt_status_ch[1].checked = false
        }else {
            document.form.chkt_status_ch[0].checked = false                
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_tipo_pagamento.focus()'>
<form name='form' method='post' onsubmit="return validar()" action='' enctype="multipart/form-data">
<table width='60%' cellspacing="1" cellpadding="1" align="center">
    <tr class="atencao" align="center">
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Incluir Tipo de Pagamento
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Tipo de Pagamento:</b>
        </td>
        <td>
            <input type="text" name="txt_tipo_pagamento" title='Digite o Tipo de Pagamento' size='35' maxlength='30' class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Imagem:</b>
        </td>
        <td>
            <input type="file" name="txt_imagem" title="Digite o Caminho da imagem" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan="2">
            <input type="checkbox" name="chkt_status_db" id="chkt1" value="1" class="checkbox">
            <label for="chkt1">ForÁar Dados Banc·rios</label>
            <br>
            <input type="checkbox" name="chkt_status_ch" id="chkt2" value="1" class="checkbox" onclick="controle(this)">
            <label for="chkt2">ForÁar Bancos / AgÍncia / Conta Corrente</label>
            <br>
            <input type="checkbox" name="chkt_status_ch" id="chkt3" value="2" class="checkbox" onclick="controle(this)">
            <label for="chkt3">ForÁar Bancos / AgÍncia / Conta Corrente / Cheque</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name="txt_observacao" title="Digite a ObservaÁ„o" rows='1' cols='80' maxlength='80' class="caixadetexto"></textarea>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form','LIMPAR');document.form.txt_tipo_pagamento.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>