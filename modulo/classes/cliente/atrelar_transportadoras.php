<?
require('../../../lib/segurancas.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>TRANSPORTADORA ATRELADA COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `transportadoras` 
                    WHERE (`nome` LIKE '%$txt_consultar%' OR `nome_fantasia` LIKE '%$txt_consultar%') 
                    AND `ativo` = '1' ORDER BY `nome` ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `transportadoras` 
                    WHERE `ativo` = '1' ORDER BY `nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script language = 'Javascript'>
            window.location = 'atrelar_transportadoras.php?id_cliente=<?=$id_cliente;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Transportadora(s) p/ Atrelar ao Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Transportadora(s) p/ Atrelar ao Cliente
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Transportadora
        </td>
        <td>
            Endereço
        </td>
        <td>
            Telefone
        </td>
        <td>
            Telefone 2
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'atrelar_transportadoras.php?passo=2&id_cliente=<?=$id_cliente;?>&id_transportadora=<?=$campos[$i]['id_transportadora'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href='atrelar_transportadoras.php?passo=2&id_cliente=<?=$id_cliente;?>&id_transportadora=<?=$campos[$i]['id_transportadora'];?>' class='link'>
            <?
                if(!empty($campos[$i]['nome_fantasia'])) {
                    echo $campos[$i]['nome_fantasia'];
                }else {
                    echo $campos[$i]['nome'];
                }
            ?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['endereco'];?>
        </td>
        <td>
            <?=$campos[$i]['fone'];?>
        </td>
        <td>
            <?=$campos[$i]['fone2'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'atrelar_transportadoras.php?id_cliente=<?=$id_cliente;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}elseif($passo == 2) {
    $sql = "SELECT `id_cliente_transportadora` 
            FROM `clientes_vs_transportadoras` 
            WHERE `id_cliente` = '$_GET[id_cliente]' 
            AND `id_transportadora` = '$_GET[id_transportadora]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Transportadora já existente para esse Cliente
?>
    <Script language = 'Javascript'>
        alert('TRANSPORTADORA JÁ EXISTENTE PARA ESTE CLIENTE !')
    </Script>
<?
    }else {
        $sql = "INSERT INTO `clientes_vs_transportadoras` (`id_cliente_transportadora`, `id_cliente`, `id_transportadora`) VALUES (NULL, '$_GET[id_cliente]', '$_GET[id_transportadora]') ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        opener.document.form.id_transportadora_atrelar.value = '<?=$_GET['id_transportadora'];?>'
        opener.document.form.submit()
        window.close()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Transportadora(s) p/ Atrelar ao Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value       = ''

    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
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
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_cliente' value='<?=$_GET['id_cliente'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Transportadora(s) p/ Atrelar ao Cliente
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' id='opt1' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Transportadoras por: Transportadora' checked>
            <label for='opt1'>Transportadora</label>
        </td>
        <td width='20%'>
            <input type='checkbox' id='todos' name='opcao' onclick='limpar()' value='1' title='Consultar todas as Transportadoras' class='checkbox'>
            <label for='todos'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>