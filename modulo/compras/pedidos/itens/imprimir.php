<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

//Busca do N.º de Entradas do Pedido passado por parâmetro em Notas Fiscais ...
$sql = "SELECT nfe.data_entrega 
        FROM `nfe_historicos` nfeh 
        INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
        INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` 
        WHERE p.`id_pedido` = '$_GET[id_pedido]' 
        GROUP BY nfe.data_entrega DESC ";
$campos_data_entrega    = bancos::sql($sql);
$numero_entradas        = count($campos_data_entrega);
?>
<html>
<head>
<title>.:: Opções p/ Imprimir Relatório de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../css/layout.css'>
<Script Language = 'Javascript' src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function habilitar() {
    if(document.form.opt_item[1].checked == true) {
        document.form.txt_entrada.disabled  = false
        document.form.txt_entrada.className = 'caixadetexto'
        document.form.txt_entrada.value     = '<?=$numero_entradas;?>'
        document.form.txt_entrada.focus()
    }else {
        document.form.txt_entrada.disabled  = true
        document.form.txt_entrada.className = 'textdisabled'
        document.form.txt_entrada.value     = ''
    }
}

function validar() {
    if(document.form.opt_item[1].checked == true) {//Somente na opção de Relatório de Pendências q força essa opção ...
        if(!texto('form', 'txt_entrada', '1', '1234567890', 'ENTRADA', '1')) {
            return false
        }
    }
    imprimir()
}

function imprimir() {
/*Se existir esse objeto Tipo de Cabeçalho que é para o caso de Pedidos SGD, eu forço o usuário a escolher
um Tipo de Cabeçalho p/ a Empresa*/
    if(typeof(document.form.cmb_tipo_cabecalho) == 'object') {
        if(document.form.cmb_tipo_cabecalho.value == '') {
            alert('SELECIONE UM TIPO DE CABEÇALHO PARA IMPRESSÃO !')
            document.form.cmb_tipo_cabecalho.focus()
            return false
        }
        var tipo_cabecalho = document.form.cmb_tipo_cabecalho.value
    }else {
        var tipo_cabecalho = ''
    }
    if(document.form.opt_item[0].checked == true) {//Opção de Relatório de Pedidos ...
        window.location = 'relatorio_pdf/relatorio.php?id_pedido=<?=$_GET['id_pedido'];?>&tipo_cabecalho='+tipo_cabecalho
    }else {//Opção de Relatório de Pendências ...
        var txt_entrada = (document.form.txt_entrada.value != '') ? document.form.txt_entrada.value : ''
        window.location = 'relatorio_pdf/relatorio.php?id_pedido=<?=$_GET['id_pedido'];?>&relatorio_pendencia=1&txt_entrada='+txt_entrada+'&tipo_cabecalho='+tipo_cabecalho
    }
}
</Script>
</head>
<body>
<form name="form" method="post" action="">
<table align="center" width='70%' cellpadding='1' cellspacing='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Opções p/ Imprimir Relatório do Pedido 
            <font color='yellow'>
                <?=$_GET['id_pedido'];?>
            </font>
        </td>
    </tr>
    <tr class="linhanormal">
        <td colspan="2">
            <input type="radio" name="opt_item" value="1" title="Imprimir Relatório de Pedidos" onclick="habilitar()" id="opt1" checked>
            <label for="opt1">Imprimir Relatório de Pedidos</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            <input type="radio" name="opt_item" value="2" title="Imprimir Relatório de Pendências" onclick="habilitar()" id="opt2">
            <label for="opt2">Imprimir Relatório de Pendências</label>
        </td>
        <td>
            Entrada <input type='text' name='txt_entrada' title='Digite a Entrada' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" size="5" maxlength="2" class='textdisabled' disabled>
        </td>
    </tr>
<?
    $sql = "SELECT tipo_nota 
            FROM `pedidos` 
            WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    $campos = bancos::sql($sql);
//Se o Tipo de Pedido for SGD, então mostra essa opção para escolher qual tipo de cabeçalho que se deseja imprimir ...
    if($campos[0]['tipo_nota'] == 2) {
?>
    <tr class="linhanormal">
        <td colspan='2'>
            &nbsp;&nbsp;&nbsp;<b>Tipo de Cabeçalho:</b>
            &nbsp;
            <select name='cmb_tipo_cabecalho' title="Selecione o Tipo de Cabeçalho" class='combo'>
                <option value=''>SELECIONE</option>
                <option value='1'>ALBAFER</option>
                <option value='2'>TOOL MASTER</option>
            </select>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='return validar()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_pedido' value='<?=$_GET['id_pedido'];?>'>
</form>
</body>
</html>