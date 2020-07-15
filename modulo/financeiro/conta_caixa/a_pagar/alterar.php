<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');
$mensagem[1] = "<font class='confirmacao'> CONTA CAIXA ALTERADA COM SUCESSO.  </font>";
$mensagem[2] = "<font class='erro'> CONTA CAIXA JÁ EXISTENTE. </font>";

if($passo == 1) {
//Busca de Dados da Conta Caixa à Pagar com o $id_conta_caixa_apagar passado por parâmetro ...
    $sql = "SELECT id_modulo,conta_caixa, descricao 
            FROM `contas_caixas_pagares` 
            WHERE `id_conta_caixa_pagar` = '$_GET[id_conta_caixa_pagar]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Família - Conta(s) Caixa(s) à Pagar(es) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Módulo
    if(!combo('form', 'cmb_modulo', '', 'SELECIONE UM MÓDULO !')) {
        return false
    }
//Conta Caixa à Pagar
    if(!texto('form', 'txt_conta_caixa', '2', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀ'àºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'CONTA CAIXA', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_conta_caixa.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar()">
<input type='hidden' name='hdd_conta_caixa_pagar' value="<?=$_GET['id_conta_caixa_pagar'];?>">
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Alterar Conta Caixa &agrave; Pagar
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Módulo:</b>
        </td>
        <td>
            <select name="cmb_modulo" title="Selecione o Módulo" class="combo">
            <?
                $sql = "SELECT id_modulo, modulo 
                        FROM `modulos` 
                        ORDER BY modulo ";
                echo combos::combo($sql, $campos[0]['id_modulo']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Conta Caixa:</b>
        </td>
        <td>
            <input type="text" name="txt_conta_caixa" value="<?=$campos[0]['conta_caixa'];?>" title="Digite a Conta Caixa" size='35' maxlength='30' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Descrição:</td>
        <td>
            <textarea name="txt_descricao" title="Digite a Descrição" cols='80' rows='1' class="caixadetexto"><?=$campos[0]['descricao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'alterar.php<?=$parametro;?>'" class="botao">
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_conta_caixa.focus()" style="color:#ff9900;" class="botao">
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
//Verifico se já existe essa Conta Caixa em Cadastro ...
    $sql = "SELECT id_conta_caixa_pagar 
            FROM `contas_caixas_pagares` 
            WHERE `conta_caixa` = '$_POST[txt_conta_caixa]' 
            AND `id_conta_caixa_pagar` <> '$_POST[hdd_conta_caixa_pagar]' ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe ...
        $sql = "UPDATE `contas_caixas_pagares` SET `conta_caixa` = '$_POST[txt_conta_caixa]', `id_modulo` = '$_POST[cmb_modulo]', `descricao` = '$_POST[txt_descricao]' WHERE `id_conta_caixa_pagar` = '$_POST[hdd_conta_caixa_pagar]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//Já existe ...
        $valor= 2;
    }
?>
    <Script language = 'JavaScript'>
        window.location = 'alterar.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
    $sql = "SELECT ccp.id_conta_caixa_pagar, ccp.conta_caixa, ccp.descricao, m.modulo 
            FROM `contas_caixas_pagares` ccp 
            INNER JOIN `modulos` m ON m.id_modulo = ccp.id_modulo 
            WHERE ccp.`ativo` = '1' ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../../html/index.php?valor=3'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Conta(s) Caixa(s) à Pagar(es) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar Conta(s) Caixa(s) à Pagar(es)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Conta Caixa
        </td>
        <td>
            Módulo
        </td>
        <td>
            Descrição
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = 'alterar.php?passo=1&id_conta_caixa_pagar='.$campos[$i]['id_conta_caixa_pagar'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = '<?=$url;?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="window.location = '<?=$url;?>'" width="10">
            <a href="<?=$url?>">
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href="<?=$url?>" class='link'>
                <?=$campos[$i]['conta_caixa'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['modulo'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['descricao'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            &nbsp;
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
}
?>