<?
require('../../../lib/segurancas.php');
if(empty($_GET['id_orcamento_venda']) && empty($_GET['id_pedido_venda']) && empty($_GET['id_oc']) && empty($_GET['pop_up'])) {
    require('../../../lib/menu/menu.php');
}
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='confirmacao'>RELATÓRIO DE ATENDIMENTO INCLUIDO COM SUCESSO.</font>";

if(!empty($_POST['cmb_representante'])) {
    //Através do id_representante eu busco quem é o Funcionário que atende este Cliente ...
    $sql = "SELECT id_funcionario 
            FROM `representantes_vs_funcionarios` 
            WHERE `id_representante` = '$_POST[cmb_representante]' LIMIT 1 ";
    $campos_funcionario = bancos::sql($sql);
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $id_funcionario_responder   = (count($campos_funcionario) == 1) ? "'".$campos_funcionario[0]['id_funcionario']."'" : 'NULL';
    $id_cliente                 = (!empty($_POST[hdd_cliente])) ? "'".$_POST[hdd_cliente]."'" : 'NULL';

//Insere a Ocorrência no Banco de Dados ...
    $sql = "INSERT INTO `atendimentos_diarios` (`id_atendimento_diario`, `id_funcionario_registrou`, `id_funcionario_responder`, `id_cliente`, `id_representante`, `pessoa_atendida`, `contato`, `procedimento`, `numero`, `observacao`, `data_sys_registrou`) VALUES (NULL, '$_SESSION[id_funcionario]', $id_funcionario_responder, $id_cliente, '$_POST[cmb_representante]', '$_POST[txt_cliente]', '$_POST[txt_contato]', '$_POST[cmb_procedimento]', '$_POST[txt_numero]', '$_POST[txt_observacao]', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    $valor = 1;
}

if(!empty($_GET['id_orcamento_venda'])) {
    $sql = "SELECT DISTINCT(ov.id_orcamento_venda) AS numero, CONCAT(c.razaosocial, ' (', c.nomefantasia, ') | ', ov.id_cliente) AS cliente, cc.nome 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.id_orcamento_venda = ov.id_orcamento_venda 
            INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = ov.`id_cliente_contato` 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            WHERE ov.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
    $campos = bancos::sql($sql);
}else if(!empty($_GET['id_pedido_venda'])) { 
    $sql = "SELECT DISTINCT(pv.id_pedido_venda) AS numero, CONCAT(c.razaosocial, ' (', c.nomefantasia, ') | ', pv.id_cliente) AS cliente, cc.nome 
            FROM `pedidos_vendas` pv 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
            INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = pv.`id_cliente_contato` 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pv.`id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
    $campos = bancos::sql($sql);
}else if(!empty($_GET['id_oc'])) { 
    $sql = "SELECT DISTINCT(ocs.id_oc) AS numero, CONCAT(c.razaosocial, ' (', c.nomefantasia, ') | ', ocs.id_cliente) AS cliente, cc.nome 
            FROM `ocs` 
            INNER JOIN `ocs_itens` oi ON oi.id_oc = ocs.id_oc 
            INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = ocs.`id_cliente_contato` 
            INNER JOIN `clientes` c ON c.`id_cliente` = ocs.`id_cliente` 
            WHERE ocs.`id_oc` = '$_GET[id_oc]' LIMIT 1 ";
    $campos = bancos::sql($sql);
}
?>
<html>
<head>
<title>.:: Incluir Relatório Diário de Atendimento ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Cliente ...
    if(!texto('form', 'txt_cliente', '3', '0123456789&ãõÃÕáéíóúÁÉÍÓÚabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZçÇ()/, ._-', 'CLIENTE', '2')) {
        return false
    }
//Representante ...
    if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
        return false
    }
//Contato ...
    if(!texto('form', 'txt_contato', '3', 'ãõÃÕáéíóúÁÉÍÓÚabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZçÇ _-/ ', 'CONTATO', '2')) {
        return false
    }
//Procedimento ...
    if(!combo('form', 'cmb_procedimento', '', 'SELECIONE O PROCEDIMENTO !')) {
        return false
    }
//O campo número será obrigado a ser preenchido quando estiver habilitado ...
    if(document.form.txt_numero.disabled == false) {
        if(!texto('form', 'txt_numero', '3', '0123456789', 'NÚMERO', '2')) {
            return false
        }
    }
//Observação
    if(document.form.txt_observacao.value == '') {
        alert('DIGITE A OBSERVAÇÃO !')
        document.form.txt_observacao.focus()
        return false
    }
//Aqui eu anulo o id do cliente caso exista, por que o usuário preferiu digitar manualmente ...
    if(document.form.chkt_ignorar_clientes_erp.checked) {
        document.form.hdd_cliente.value = 0
    }
}

function controlar_numero(combo) {
    //Só força o preenchimento do Número quando for Orçamento, Pedido ou OC ...
    if(combo.value == 'O' || combo.value == 'P' || combo.value == 'OC') {
        document.form.txt_numero.disabled 	= false
        document.form.txt_numero.className 	= 'caixadetexto'
        document.form.txt_numero.focus()
    }else {//Em outro caso sempre deixa desabilitado ...
        document.form.txt_numero.disabled 	= true
        document.form.txt_numero.className 	= 'textdisabled'
        document.form.txt_numero.value 		= ''
    }
}

function separar_string_cliente() {
    //Aqui eu verifico se existe o caractér Pipe "|" dentro da String ...
    if(document.form.txt_cliente.value.indexOf('|') != -1) {
        vetor_cliente = document.form.txt_cliente.value.split('|')
        document.form.txt_cliente.value = vetor_cliente[0]
        document.form.hdd_cliente.value = vetor_cliente[1]
        document.form.hdd_cliente.value = document.form.hdd_cliente.value.replace(' ', '')
        //Se for cliente, então listo todos os representantes do cliente passado por parâmetro ...
        ajax('consultar_representantes.php?id_cliente='+document.form.hdd_cliente.value, 'cmb_representante')
    }else {
        if(document.form.chkt_ignorar_clientes_erp.checked == true) {//Se não é cliente, listo todos os Representantes ...
            ajax('consultar_representantes.php', 'cmb_representante')
        }
    }
}
</Script>
</head>
<?
    //Tenho que chamar a função em Ajax para Carregar o Representante ...
    if(!empty($_GET['id_orcamento_venda']) || !empty($_GET['id_pedido_venda'])) {
        $onload = 'separar_string_cliente();controlar_numero(document.form.cmb_procedimento);document.form.cmb_representante.focus()';
    }else {
        $onload = 'document.form.txt_cliente.focus()';
    }
?>
<body onload='<?=$onload;?>'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='hdd_cliente'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Relatório Diário de Atendimento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cliente:</b>
        </td>
        <td>
            <input type='text' name='txt_cliente' id='txt_cliente' title='Digite o Cliente' size="110" value="<?=$campos[0]['cliente'];?>" maxlength="100" onkeyup="if(!document.form.chkt_ignorar_clientes_erp.checked) {auto_complete('consultar_clientes.php', 'txt_cliente', -158, 29.2, event)}" autocomplete="off" class='caixadetexto'>
            <input type='checkbox' name='chkt_ignorar_clientes_erp' id='chkt_ignorar_clientes_erp' value='S' onclick='document.form.txt_cliente.value="";document.form.txt_cliente.focus()' class='checkbox'>
            <label for='chkt_ignorar_clientes_erp'>
                <font color='red'>
                    <b>Ignorar Clientes do ERP</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante:</b>
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' onfocus="separar_string_cliente()" class='combo'>
                <option value=''>--------<option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>	
        <td>
            <b>Contato:</b>
        </td>
        <td>
            <input type='text' name='txt_contato' value='<?=$campos[0]['nome'];?>' title='Digite o Contato' onfocus="if(!document.form.chkt_ignorar_clientes_erp.checked) {separar_string_cliente()}" size="30" maxlength="25" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Procedimento:</b>
        </td>
        <td>
        <?
            if(!empty($_GET['id_orcamento_venda'])) {
                    $selectedo 	= 'selected';
            }else if(!empty($_GET['id_pedido_venda'])) {
                    $selectedp 	= 'selected';
            }else if(!empty($_GET['id_oc'])) {
                    $selectedoc 	= 'selected';
            }
        ?>
            <select name='cmb_procedimento' title='Selecione o Procedimento' onchange="controlar_numero(this)" class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='C'>Ocorrência</option>
                <option value='O' <?=$selectedo;?>>Orçamento</option>
                <option value='P' <?=$selectedp;?>>Pedido</option>
                <option value='OC' <?=$selectedoc;?>>OC</option>
            </select>
            &nbsp;-&nbsp;
            Número: 
            <input type='text' name='txt_numero' value='<?=$campos[0]['numero'];?>' title='Digite o Número' size='12' maxlength='11' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) this.value = ''" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observação:</b>
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a Observação' rows='3' cols='85' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_cliente.focus()" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>