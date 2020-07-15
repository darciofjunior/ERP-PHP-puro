<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/comissoes/comissoes_cidades/comissoes_cidades.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>COMISSÃO POR CIDADE INCLUÍDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>COMISSÃO POR CIDADE JÁ EXISTENTE.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT DISTINCT(cidade) 
                    FROM `ceps` 
                    WHERE `cidade` LIKE '%$txt_consultar%' ORDER BY cidade ";
        break;
        default:
            $sql = "SELECT DISTINCT(cidade) 
                    FROM `ceps` 
                    ORDER BY cidade ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir_comissao_cidade.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir Comissão por Cidade ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit='return validar()'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Comissão por Cidade(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cidade
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Todos' class='checkbox'>
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_cidade[]' value='<?=$campos[$i]['cidade'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir_comissao_cidade.php'" class='botao'>
            <input type='submit' name='cmd_incluir' value='Incluir' title='Incluir' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    foreach($_POST['chkt_cidade'] as $cidade) {
        //Verifico se já existe Comissão cadastrada p/ a cidade selecionada pelo Usuário ...
        $sql = "SELECT id_comissao_cidade 
                FROM `comissoes_cidades` 
                WHERE `comissao_cidade` = '$cidade' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            $sql = "INSERT INTO `comissoes_cidades` (`id_comissao_cidade`, `comissao_cidade`) VALUES (NULL, '$cidade') ";
            bancos::sql($sql);
            $valor = 1;
        }else {
            $valor = 2;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_comissao_cidade.php?valor=<?=$valor?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cidade(s) p/ Incluir Comissão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
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
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1'; ?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cidade(s) p/ Incluir Comissão
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='label' value='1' title='Consultar cidades por: Cidade' onclick='document.form.txt_consultar.focus()' checked>
            <label for='label'>
                Cidade
            </label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' id='label2' value='1' title='Consultar todas as Cidades' onclick='limpar()' class='checkbox'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'comissoes_cidades.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>