<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/index.php', '../../../');
$mensagem[1] = 'SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO !';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT id_produto_insumo, pi.discriminacao 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.`nome` LIKE '%$txt_consultar%' 
                    WHERE pi.`ativo` = '1' ORDER BY pi.discriminacao ";
        break;
        case 2:
            $sql = "SELECT id_produto_insumo, pi.discriminacao 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE pi.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' ORDER BY pi.discriminacao ";
        break;
        case 3:
            $sql = "SELECT pa.id_produto_insumo, CONCAT(pa.referencia, ' - ', pi.discriminacao) AS discriminacao 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`referencia` LIKE '%$txt_consultar%' AND pa.ativo = '1' 
                    WHERE pi.`ativo` = '1' ORDER BY discriminacao ";
        break;
        default:
            $sql = "SELECT id_produto_insumo, pi.discriminacao 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE pi.`ativo` = '1' ORDER BY pi.discriminacao ";
        break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'consultar_prod.php?valor=1'
        </Script>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Produtos Insumos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function enviar() {
    var elementos = document.form.elements
    var id_produto = ''
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1;j < document.form.elements[i].length; j++) {
                if(document.form.elements[i][j].selected == true) id_produto = id_produto + document.form.elements[i][j].value + ','
            }
        }
    }
    parent.juncao.document.form.id_produto2.value=id_produto.substr(0,id_produto.length-1);
    parent.juncao.document.form.submit();
}

function selecionar_todos() {
    var i, elementos = document.form.elements
    var selecionados = ''
    for (i = 0; i < elementos.length; i ++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
        }
    }
}
</Script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border='0' width='70%' align="center" cellspacing ='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto Insumo
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Insumos por: Grupo" id='label'>
            <label for='label'>Grupo</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" onClick="document.form.txt_consultar.focus()" title="Consultar Produtos Insumos por: Discrimina&ccedil;&atilde;o" id='label2' checked>
            <label for='label2'>Discrimina&ccedil;&atilde;o</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td width="20%">
            <input type="radio" name="opt_opcao" value="3" onClick="document.form.txt_consultar.focus()" title="Consultar Produtos Insumos por: Refer&ecirc;ncia do P.A." id='label3'>
            <label for='label3'>Refer&ecirc;ncia do P.A.</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' onClick='limpar()' value='1' title="Consultar todos os Produtos Insumos" class="checkbox" id='label4'>
            <label for='label4'>Todos os registros</label>
        </td>
    </tr>
<?
    if($passo == 1) {
?>
    <tr class="linhanormal" align="center">
        <td colspan='2'>
            <select name="cmb_produto[]" class="combo" size="5" multiple>
                <option value='' style='color:red'>
                    SELECIONE
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </option>
            <?
                for($i = 0; $i < $linhas; $i++) {
            ?>
                    <option value="<?=$campos[$i]['id_produto_insumo'];?>"><?=$campos[$i]['discriminacao'];?></option>
            <?
                }
            ?>
            </select>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
<?
	if($passo == 1) {
?>
            <input type="button" name="cmd_selecionar" value="Selecionar Todos" title="Selecionar Todos" onclick="selecionar_todos()" class="botao">
            <input type="button" name="cmd_adicionar" value="Adicionar" title="Adicionar" onclick="enviar()" class="botao">
<?
	}
?>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
<?
    if(!empty($valor)) {
?>
        <Script Language = 'JavaScript'>
            alert('<?=$mensagem[$valor];?>')
        </Script>
<?
    }
?>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</html>