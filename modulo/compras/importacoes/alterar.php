<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/compras_new.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>IMPORTA«√O ALTERADA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>IMPORTA«√O J¡ EXISTENTE.</font>";

if($passo == 1) {
    //Tratamento com as vari·veis que vem por par‚metro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_importacao             = $_POST['txt_importacao'];
        $chkt_importacoes_atreladas = $_POST['chkt_importacoes_atreladas'];
    }else {
        $txt_importacao             = $_GET['txt_importacao'];
        $chkt_importacoes_atreladas = $_GET['chkt_importacoes_atreladas'];
    }
    if(!empty($chkt_importacoes_atreladas)) $condicao_atrelados = "INNER JOIN `pedidos` p ON p.id_importacao = i.id_importacao ";

    $sql = "SELECT i.`id_importacao`, i.`nome`, i.`observacao` 
            FROM `importacoes` i 
            $condicao_atrelados 
            WHERE i.`nome` LIKE '%$txt_importacao%' 
            AND i.`ativo` = '1' 
            GROUP BY i.`id_importacao` ORDER BY i.`nome` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
	<Script Language = 'JavaScript'>
            window.location = 'alterar.php?valor=1'
	</Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar ImportaÁ„o(ıes) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar ImportaÁ„o(ıes)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Nome
        </td>
        <td>
            Observa&ccedil;&atilde;o
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_importacao='.$campos[$i]['id_importacao'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = '<?=$url;?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <a href="<?=$url?>" title="Alterar ImportaÁ„o" style='cursor:help' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="<?=$url?>" title='Alterar ImportaÁ„o' style='cursor:help' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
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
    $sql = "SELECT * 
            FROM `importacoes` 
            WHERE `id_importacao` = '$_GET[id_importacao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Importa&ccedil;&atilde;o ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Nome
    if(!texto('form', 'txt_nome', '2', "-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿'‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ", 'NOME', '2')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='hdd_importacao' value='<?=$_GET['id_importacao'];?>'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Importa&ccedil;&atilde;o
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b>
        </td>
        <td>
            <input type='text' name='txt_nome' value='<?=$campos[0]['nome'];?>' title='Digite a ImportaÁ„o' maxlength='20' size='22' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observa&ccedil;&atilde;o:
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a ObservaÁ„o' maxlength='80' cols='80' rows='1' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nome.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3){
//Aqui eu verifico se existe alguma ImportaÁ„o com o mesmo nome da Atual que o usu·rio est· tentando alterar ...
    $sql = "SELECT `id_importacao` 
            FROM `importacoes`
            WHERE `nome` = '$_POST[txt_nome]' 
            AND `id_importacao` <> '$_POST[hdd_importacao]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $txt_observacao = strtolower($txt_observacao);
    if(count($campos) == 0) {//N„o existe, sendo assim posso efetuar a alteraÁ„o ...
        $sql = "UPDATE `importacoes` SET  `nome` = '$_POST[txt_nome]', `observacao` = '$_POST[txt_observacao]' WHERE `id_importacao` = '$_POST[hdd_importacao]' LIMIT 1 ";
        bancos::sql($sql);
        //Aqui eu verifico se essa importaÁ„o j· est· vinculada a algum Pedido ...
        $sql = "SELECT `id_pedido` 
                FROM `pedidos` 
                WHERE `id_importacao` = '$_POST[hdd_importacao]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Se sim, ent„o atualizo o nome da ImportaÁ„o no N.∫ da Conta no Financeiro ...
            compras_new::atualizar_importacao($campos[0]['id_pedido']);
        }
        $valor = 2;
    }else {//J· existe, ent„o n„o posso substituir o nome ...
        $valor = 3;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar ImportaÁ„o(ıes) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_importacao.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
            <td colspan="2">
                    Alterar ImportaÁ„o(ıes)
            </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ImportaÁ„o
        </td>
        <td>
            <input type='text' name='txt_importacao' title='Digite a ImportaÁ„o' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_importacoes_atreladas' value='1' title='Somente ImportaÁıes Atreladas' id='lbl_importacoes_atreladas' onclick='document.form.txt_importacao.focus()' class='checkbox'>
            <label for='lbl_importacoes_atreladas'>Somente ImportaÁıes Atreladas</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_importacao.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>