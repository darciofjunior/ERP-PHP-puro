<?
//Função que contém a Tela Principal de Filtro ...
function filtro($nivel_arquivo_principal, $valor) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
    $mensagem[2] = "<font class='confirmacao'>PRODUTO INSUMO EXCLUIDO COM SUCESSO.</font>";
?>
<html>
<head>
<title>.:: Filtro de Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$nivel_arquivo_principal;?>css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '<?=$nivel_arquivo_principal;?>js/geral.js'></Script>
<Script Language = 'Javascript' Src = '<?=$nivel_arquivo_principal;?>js/validar.js'></Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post' action=''>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Produto(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name='txt_observacao' title='Digite a Observação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick='document.form.txt_referencia.focus()' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}

//Significa que o usuário já fez pelo menos uma consulta ...
if(!empty($cmd_consultar)) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_referencia     = $_POST['txt_referencia'];
        $txt_discriminacao  = $_POST['txt_discriminacao'];
        $txt_observacao     = $_POST['txt_observacao'];
    }else {
        $txt_referencia     = $_GET['txt_referencia'];
        $txt_discriminacao  = $_GET['txt_discriminacao'];
        $txt_observacao     = $_GET['txt_observacao'];
    }
	
    $sql = "SELECT pi.*, g.nome, g.referencia, u.sigla 
            FROM `produtos_insumos` pi 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.referencia LIKE '%$txt_referencia%' 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            WHERE pi.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND pi.`observacao` LIKE '%$txt_observacao%' 
            AND pi.`ativo` = '1' ORDER BY pi.discriminacao ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
//Não retornou nenhum registro, então requisito a Tela de Filtro ...
    if($linhas == 0) {
        filtro($nivel_arquivo_principal, 1);
    }
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($nivel_arquivo_principal, $valor);
}
?>