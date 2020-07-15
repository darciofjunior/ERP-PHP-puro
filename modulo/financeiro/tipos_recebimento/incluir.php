<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">TIPO DE RECEBIMENTO INCLUIDO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">TIPO DE RECEBIMENTO J¡ EXISTENTE.</font>';

if(!empty($_POST['txt_tipo_recebimento'])) {
    //Verifico se j· existe algum Tipo de Recebimento cadastrado com o nome digitado pelo Usu·rio ...
    $sql = "SELECT id_tipo_recebimento 
            FROM `tipos_recebimentos` 
            WHERE `recebimento` = '$_POST[txt_tipo_recebimento]' 
            AND `ativo` = '1' LIMIT 1 ";
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
        $status     = (empty($_POST['chkt_forcar'])) ? 0 : 1;
        $observacao = strtolower($_POST['txt_observacao']);
        //InserÁ„o do Tipo de Recebimento no Banco de Dados ...
        $sql = "INSERT INTO `tipos_recebimentos` (`id_tipo_recebimento`, `recebimento`, `imagem`, `observacao`, `data_sys`, `status`) VALUES (NULL, '$_POST[txt_tipo_recebimento]', '$imagem', '$observacao', '".date('Y-m-d H:i:s')."', '$status') ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
}
?>
<html>
<head>
<title>.:: Incluir Tipo(s) de Recebimento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Tipo de Recebimento
    if(!texto('form', 'txt_tipo_recebimento', '2', '1234567890qwertyuiopÁlkjhgfdsazxcvbnm<>QWERTYUIOP«LKJHGFDSAZXCVBNM ·ÈÌÛ˙¡…Õ”⁄‚ÍÓÙ˚¬ Œ‘€„√ı’¸‹‡¿!@#$%®&™∫∞/*():;.π≤≥£¢¨,', 'TIPO DE RECEBIMENTO', '2')) {
        return false
    }
//Imagem
    if(document.form.txt_imagem.value == '') {
        alert('DIGITE O ENDERE«O DA IMAGEM OU PROCURE A IMAGEM !')
        document.form.txt_imagem.focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_tipo_recebimento.focus()'>
<form name='form' method='post' action='' onsubmit="return validar()" enctype="multipart/form-data">
<table width='60%' cellspacing="1" cellpadding="1" align="center">
    <tr class="atencao" align="center">
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            Incluir Tipo de Recebimento
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <b>Tipo de Recebimento:</b>
        </td>
        <td>
            <input type="text" name="txt_tipo_recebimento" title="Digite o Tipo de Recebimento" size='55' maxlength='50' class="caixadetexto">
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
        <td colspan='2'>
            <input type="checkbox" name="chkt_forcar" value="1" class="checkbox" id="forcar">
            <label for="forcar">ForÁar Banco</label>
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
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_tipo_recebimento.focus()" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>