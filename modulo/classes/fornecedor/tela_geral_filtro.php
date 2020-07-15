<?
//Função que contém a Tela Principal de Filtro ...
function filtro($nivel_arquivo_principal, $valor) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
    $mensagem[2] = "<font class='confirmacao'>FORNECEDOR EXCLUÍDO COM SUCESSO.</font>";
    $mensagem[3] = "<font class='erro'>ALGUM(NS) REGISTRO(S) NÃO PODEM SER APAGADOS POIS CONSTA EM USO POR OUTRO CADASTRO.</font>";
?>
<html>
<head>
<title>.:: Filtro de Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$nivel_arquivo_principal;?>/css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/validar.js'></Script>
</head>
<body onload='document.form.txt_razao_social.focus()'>
<form name='form' method='post' action="<?=$GLOBALS['PHP_SELF'];?>">
<input type='hidden' name='nivel_arquivo_principal' value='<?=$nivel_arquivo_principal;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Filtro de Fornecedor(es)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Razão Social
        </td>
        <td>
            <input type='text' name="txt_razao_social" title="Digite a Razão Social" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CNPJ
        </td>
        <td>
            <input type='text' name="txt_cnpj" title="Digite o CNPJ" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Telefone
        </td>
        <td>
            <input type='text' name='txt_telefone' title='Digite o Telefone' size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Produtos
        </td>
        <td>
            <input type='text' name="txt_produtos" title="Digite os Produtos" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name="txt_observacao" title="Digite a Observação" maxlength="50" size="35" class='textdisabled' disabled> (Desabilitado)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='opt_apenas_internacionais' value='1' title='Apenas Internacionais' id='label3' class='checkbox'>
            <label for='label3'>Apenas Internacionais</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_nome_fantasia.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}

//Significa que o usuário já fez pelo menos uma consulta ...
if(!empty($cmd_consultar)) {
//CNPJ
    if(!empty($txt_cnpj)) {
        $txt_cnpj = str_replace('.', '', $txt_cnpj);
        $txt_cnpj = str_replace('.', '', $txt_cnpj);
        $txt_cnpj = str_replace('/', '', $txt_cnpj);
        $txt_cnpj = str_replace('-', '', $txt_cnpj);
        $condicao_cnpj = " AND `cnpj` LIKE '%$txt_cnpj%' ";
    }
//Tratamento com o Checkbox agora p/ não dar erro ...
    if($opt_apenas_internacionais == 1) $condicao = " AND `id_pais` <> '31' ";

    $sql = "SELECT DISTINCT(`id_fornecedor`), `razaosocial`, `ddd_fone1`, `fone1`, 
            `ddd_fone2`, `fone2`, `ddd_fax`, `fax`, `produto` 
            FROM `fornecedores` 
            WHERE `razaosocial` LIKE '%$txt_razao_social%' 
            $condicao_cnpj 
            AND (`fone1` LIKE '$txt_telefone%' OR `fone2` LIKE '$txt_telefone%' OR `fax` LIKE '$txt_telefone%') 
            AND `produto` LIKE '%$txt_produtos%' 
            AND `ativo` = '1' 
            AND `razaosocial` <> '' 
            $condicao 
            ORDER BY razaosocial ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não retornou nenhum registro, então requisito a Tela de Filtro ...
//Aqui eu chamo a Tela Principal de Filtro ...
        filtro($nivel_arquivo_principal, 1);
    }
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($nivel_arquivo_principal, $valor);
}
?>