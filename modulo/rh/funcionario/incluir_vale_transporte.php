<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/alterar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>VALE TRANSPORTE INCLUIDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>VALE TRANSPORTE JÁ EXISTENTE(S) PARA ESTE FUNCIONÁRIO.</font>";

//Inserção do Contato para o Cliente
if(!empty($_POST['chkt_vale_transporte'])) {
//Aqui é a parte da inserção dos Vales Transportes p/ o Funcionário ...
    foreach($_POST['chkt_vale_transporte'] as $i => $id_vale_transporte) {//Verifico se esse VT já foi incluido ant p/ o Func ...
        $sql = "SELECT id_funcionario_vale_transporte 
                FROM `funcionarios_vs_vales_transportes` 
                WHERE `id_funcionario` = '$_POST[id_funcionario_loop]' 
                AND `id_vale_transporte` = '$id_vale_transporte' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//Ainda não foi 
            $sql = "INSERT INTO `funcionarios_vs_vales_transportes` (`id_funcionario_vale_transporte`, `id_funcionario`, `id_vale_transporte`, `qtde_vale`) VALUES (NULL, '$_POST[id_funcionario_loop]', '$id_vale_transporte', '".$_POST['txt_qtde_vale'][$i]."') ";
            bancos::sql($sql);
            $valor = 1;
        }else {//Já foi incluido ...
            $valor = 2;
        }
    }
}

$id_funcionario_loop = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_funcionario_loop'] : $_GET['id_funcionario_loop'];

//Busca de Todos os Tipo(s) de Vale Transporte ...
$sql = "SELECT * 
        FROM `vales_transportes` 
        WHERE `ativo` = '1' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO EXISTE(M) VALE(S) TRANSPORTE(S) CADASTRADO(S) !')
        window.close()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Vale(s) Transporte(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/arred.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements

    if(typeof(elementos['chkt_vale_transporte[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_vale_transporte[]'].length)
    }

    for (var i = 0; i < linhas; i++) {
//Valor Unitário
        if(document.getElementById('chkt_vale_transporte'+i).checked) {
            if(document.getElementById('txt_qtde_vale'+i).value == '') {
                alert('DIGITE A QTDE DE VALE !')
                document.getElementById('txt_qtde_vale'+i).focus()
                return false
            }
        }
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
    return true
}

function calcular_item_vt(indice) {
    var elementos = document.form.elements
/*Significa que o usuário está clicando da segunda linha em diante, aqui se realiza esse macete porque 
se tem 4 objetos por linha contando o checkbox também*/
    if(document.getElementById('chkt_vale_transporte'+indice).checked) {//Se o checkbox da linha estiver checado ...
        var valor_unitario = strtofloat(document.getElementById('txt_valor_unitario'+indice).value)
        if(document.getElementById('txt_qtde_vale'+indice).value != '') {//Se a Qtde estiver preenchida ...
            document.getElementById('txt_valor_total'+indice).value = valor_unitario * document.getElementById('txt_qtde_vale'+indice).value
            document.getElementById('txt_valor_total'+indice).value = arred(document.getElementById('txt_valor_total'+indice).value, 2, 1)
        }else {
            document.getElementById('txt_valor_total'+indice).value = ''
        }
    }else {//Se não estiver marcado ...
        document.getElementById('txt_valor_total'+indice).value = ''
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.document.form.passo.value = 0
        window.opener.document.form.submit()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controles de Tela****************************-->
<input type='hidden' name='id_funcionario_loop' value='<?=$id_funcionario_loop;?>'>
<input type='hidden' name='nao_atualizar'>
<!--*************************************************************************-->
<table width='95%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Incluir Vale(s) Transporte(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar todos' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            Tipo VT
        </td>
        <td>
            Valor Unitário
        </td>
        <td>
            Qtde de Vale
        </td>
        <td>
            Valor Total
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_vale_transporte[]' id='chkt_vale_transporte<?=$i;?>' value='<?=$campos[$i]['id_vale_transporte'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
            <?=$campos[$i]['tipo_vt'];?>
        </td>
        <td>
            <input type='text' name='txt_valor_unitario[]' id='txt_valor_unitario<?=$i;?>' value='<?=number_format($campos[$i]['valor_unitario'], 2, ',', '.');?>' title='Valor Unitário' maxlength='11' size='12' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_qtde_vale[]' id='txt_qtde_vale<?=$i;?>' title='Digite a Qtde de Vale' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'verifica', 'moeda_especial', '', event);calcular_item_vt('<?=$i;?>');if(this.value == 0) {this.value = ''}" maxlength='11' size='12' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_valor_total[]' id='txt_valor_total<?=$i;?>' maxlength='11' size='12' class='textdisabled' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>