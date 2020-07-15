<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='erro'>TRANSPORTADORA EXCLUIDA COM SUCESSO.</font>";

if($passo == 1) {
//Tratamento com os objetos após ter submetido a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_transportadora = $_POST['txt_transportadora'];
    }else {
        $txt_transportadora = $_GET['txt_transportadora'];
    }
    $sql = "SELECT * 
            FROM `transportadoras` 
            WHERE (`nome` LIKE '%$txt_transportadora%' OR `nome_fantasia` LIKE '%$txt_transportadora%') 
            AND `ativo` = '1' ORDER BY `nome` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script language = 'Javascript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Transportadoras ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Excluir Transportadora(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Transportadora
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            E-mail
        </td>
        <td>
            Endereço
        </td>
        <td>
            Telefone 1
        </td>
        <td>
            Telefone 2
        </td>
        <td>
            CNPJ
        </td>
        <td>
            Insc. Estadual
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome_fantasia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['email'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['endereco'])) echo $campos[$i]['endereco'].', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['fone'];?>
        </td>
        <td>
            <?=$campos[$i]['fone2'];?>
        </td>
        <td>
        <?
            $cnpj = ($campos[$i]['cnpj'] == 00000000000000) ? '' : $campos[$i]['cnpj'];
            echo $cnpj;
        ?>
        </td>
        <td>
            <?=$campos[$i]['ie'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_transportadora[]' value='<?=$campos[$i]['id_transportadora'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
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
}elseif($passo == 2) {
//Atualizando a variável do cadastro de Transportadoras p/ Zero ...
    foreach($_POST['chkt_transportadora'] as $id_transportadora) {
        $sql = "UPDATE `transportadoras` SET `ativo` = '0' WHERE `id_transportadora` = '$id_transportadora' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script language = 'Javascript'>
        window.location = 'excluir.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Transportadora(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_transportadora.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Transportadora(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Transportadora
        </td>
        <td>
            <input type='text' name='txt_transportadora' title='Digite a Transportadora' size='40' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_transportadora.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>