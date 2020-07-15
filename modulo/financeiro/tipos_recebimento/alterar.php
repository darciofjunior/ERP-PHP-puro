<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">TIPO DE RECEBIMENTO ALTERADO COM SUCESSO. </font>';
$mensagem[2] = '<font class="erro">TIPO DE RECEBIMENTO JÁ EXISTENTE. </font>';

if($passo == 1) {
    $sql = "SELECT * 
            FROM `tipos_recebimentos` 
            WHERE `id_tipo_recebimento` = '$_GET[id_tipo_recebimento]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Tipo(s) de Recebimento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Tipo de Recebimento
    if(!texto('form', 'txt_tipo_recebimento', '2', '1234567890qwertyuiopçlkjhgfdsazxcvbnm<>QWERTYUIOPÇLKJHGFDSAZXCVBNM áéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãÃõÕüÜàÀ!@#$%¨&ªº°/*():;.¹²³£¢¬,', 'TIPO DE RECEBIMENTO', '2')) {
        return false
    }
//Imagem
    var imagem_atual = '<?=$campos[0]['imagem'];?>'
    if(document.form.txt_imagem.value == '' && imagem_atual == '') {//Se não existir nenhuma Imagem então ...
        alert('DIGITE O ENDEREÇO DA IMAGEM OU PROCURE A IMAGEM !')
        document.form.txt_imagem.focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_tipo_recebimento.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()' enctype='multipart/form-data'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Tipo de Recebimento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Recebimento:</b>
        </td>
        <td>
            <input type='text' name='txt_tipo_recebimento' value='<?=$campos[0]['recebimento'];?>' title='Digite o Tipo de Recebimento' size='55' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Imagem:</b>
        </td>
        <td>
            <input type='file' name='txt_imagem' title='Digite o Caminho da imagem' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Imagem Atual
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <img src = '<?='../../../imagem/financeiro/tipos_pag_rec/'.$campos[0]['imagem'];?>' width='50' height='50'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            if($campos[0]['status'] == 1) $checked = 'checked';
        ?>
            <input type='checkbox' name='chkt_forcar' id='forcar' value='1' <?=$checked;?> class='checkbox'>
            <label for='forcar'>Forçar Banco</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a Observação' rows='1' cols='80' maxlength='80' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>	
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_tipo_recebimento.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<!--**************************Esse arquivo que está no hidden, será deletado**************************-->
<input type='hidden' name='hdd_imagem' value='<?=$campos[0]['imagem'];?>'>
<input type='hidden' name='hdd_tipo_recebimento' value='<?=$_GET['id_tipo_recebimento'];?>'>
</form>
</body>
</html>
<?
}else if($passo == 2) {
//Verifico se já existe algum Tipo de Recebimento cadastrado com o nome digitado pelo Usuário diferente do Tipo de Recebimento Atual ...
    $sql = "SELECT `id_tipo_recebimento` 
            FROM `tipos_recebimentos` 
            WHERE `recebimento` = '$_POST[txt_tipo_recebimento]' 
            AND `id_tipo_recebimento` <> '$_POST[hdd_tipo_recebimento]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {	
	$status     = (empty($_POST['chkt_forcar'])) ? 0 : 1;     
        $observacao = strtolower($_POST['txt_observacao']);

        if(!empty($_FILES['txt_imagem']['name'])) {
            if(file_exists('../../../imagem/financeiro/tipos_pag_rec/'.$_POST['hdd_imagem'])) {
                unlink('../../../imagem/financeiro/tipos_pag_rec/'.$_POST['hdd_imagem']);
            }
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
            $campo_imagem = "`imagem` = '$imagem', ";
        }
        $sql = "UPDATE `tipos_recebimentos` SET `recebimento` = '$_POST[txt_tipo_recebimento]', $campo_imagem `observacao` = '$observacao', `status` = '$status' WHERE `id_tipo_recebimento` = '$_POST[hdd_tipo_recebimento]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {
        $valor = 2;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
    //Aqui eu listo todos Recebimentos cadastrados no Sistema ...
    $sql = "SELECT * 
            FROM `tipos_recebimentos` 
            WHERE `ativo` = '1' ORDER BY `recebimento` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '../../../html/index.php?valor=3'
    </Script>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: Alterar Tipo(s) de Recebimento(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Tipo(s) de Recebimento(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Tipo de Recebimento
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=1&id_tipo_recebimento=<?=$campos[$i]['id_tipo_recebimento'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href = '#' class='link'>
                <?=$campos[$i]['recebimento'];?>
            </a>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>