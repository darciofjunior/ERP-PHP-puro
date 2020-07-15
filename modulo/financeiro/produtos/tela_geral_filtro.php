<?
//Função que contém a Tela Principal de Filtro ...
function filtro($nivel_arquivo_principal, $valor) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
    $mensagem[2] = "<font class='confirmacao'>PRODUTO(S) FINANCEIRO(S) EXCLUÍDO COM SUCESSO.</font>";
?>
<html>
<head>
<title>.:: Filtro de Produto(s) Financeiro(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$nivel_arquivo_principal;?>css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '<?=$nivel_arquivo_principal;?>js/geral.js'></Script>
<Script Language = 'Javascript' Src = '<?=$nivel_arquivo_principal;?>js/validar.js'></Script>
</head>
<body onload='document.form.cmb_grupo.focus()'>
<form name='form' method='post' action=''>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Produto(s) Financeiro(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo
        </td>
        <td>
            <select name='cmb_grupo' title='Selecione o Grupo' class='combo'>
            <?
                $sql = "SELECT g.`id_grupo`, g.`nome` 
                        FROM `grupos` g 
                        INNER JOIN `contas_caixas_pagares` ccp ON g.`id_conta_caixa_pagar` = ccp.`id_conta_caixa_pagar` 
                        INNER JOIN `modulos` m ON ccp.`id_modulo` = m.`id_modulo` 
                        WHERE g.`ativo` = '1' ORDER BY g.`nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discrimina&ccedil;&atilde;o
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
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_forcar_preenchimento_icms' value='S' title='Forçar preenchimento de ICMS' id='forcar' class='checkbox'>
            <label for='forcar'>
                Forçar preenchimento de ICMS
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick='document.form.cmb_grupo.focus()' class='botao'>
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
        $cmb_grupo                      = $_POST['cmb_grupo'];
        $txt_discriminacao              = $_POST['txt_discriminacao'];
        $txt_observacao                 = $_POST['txt_observacao'];
        $chkt_forcar_preenchimento_icms = $_POST['chkt_forcar_preenchimento_icms'];
    }else {
        $cmb_grupo                      = $_GET['cmb_grupo'];
        $txt_discriminacao              = $_GET['txt_discriminacao'];
        $txt_observacao                 = $_GET['txt_observacao'];
        $chkt_forcar_preenchimento_icms = $_GET['chkt_forcar_preenchimento_icms'];
    }
    
    if(!empty($cmb_grupo))                      $condicao_grupo = " AND g.`id_grupo` = '$cmb_grupo' ";
    if(!empty($chkt_forcar_preenchimento_icms)) $condicao_forcar_preenchimento_icms = " AND pf.`forcar_icms` = 'S' ";
    
    $sql = "SELECT pf.`id_produto_financeiro`, pf.`discriminacao`, pf.`observacao`, g.`nome` 
            FROM `produtos_financeiros` pf 
            INNER JOIN `grupos` g ON g.`id_grupo` = pf.`id_grupo` $condicao_grupo 
            WHERE pf.`discriminacao` LIKE '%$txt_discriminacao%' 
            $condicao_forcar_preenchimento_icms 
            AND pf.`observacao` LIKE '%$txt_observacao%' 
            AND pf.`ativo` = '1' ORDER BY pf.`discriminacao` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
//Não retornou nenhum registro, então requisito a Tela de Filtro ...
    if($linhas == 0) filtro($nivel_arquivo_principal, 1);
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($nivel_arquivo_principal, $valor);
}
?>