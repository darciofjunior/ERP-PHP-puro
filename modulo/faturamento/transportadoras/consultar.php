<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

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
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Transportadora(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Consultar Transportadora(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
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
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_transportadora='.$campos[$i]['id_transportadora'].'&pop_up=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['nome'];?>
            </a>
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
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
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
}else {
?>
<html>
<head>
<title>.:: Consultar Transportadora(s) ::.</title>
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
            Consultar Transportadora(s)
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