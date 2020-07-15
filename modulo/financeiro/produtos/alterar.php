<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PRODUTO FINANCEIRO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>PRODUTO FINANCEIRO JÁ EXISTENTE.</font>";

if($passo == 1) {
    //Aqui eu busco dados do Produto Financeira passado por parâmetro ...
    $sql = "SELECT * 
            FROM `produtos_financeiros` 
            WHERE `ativo` = '1' 
            AND `id_produto_financeiro` = '$_GET[id_produto_financeiro]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Produto(s) Financeiro(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Grupo
    if(!combo('form', 'cmb_grupo', '', 'SELECIONE UM GRUPO !')) {
        return false
    }
//Discriminação
    if(!texto('form', 'txt_discriminacao', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀ'àºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'DISCRIMINAÇÃO', '1')) {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit='return validar()'>
<!--************************Controle de Tela************************-->
<input type='hidden' name='hdd_produto_financeiro' value='<?=$_GET['id_produto_financeiro'];?>'>
<input type='hidden' name='hdd_grupo_atual' value='<?=$campos[0]['id_grupo'];?>'>
<!--****************************************************************-->
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Produto Financeiro
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo:</b>
        </td>
        <td>
            <b>Discriminação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_grupo' title='Selecione o Grupo' class='combo'>
            <?
                $sql = "SELECT g.`id_grupo`, g.`nome` 
                        FROM `grupos` g 
                        INNER JOIN `contas_caixas_pagares` ccp ON g.`id_conta_caixa_pagar` = ccp.`id_conta_caixa_pagar` 
                        INNER JOIN `modulos` m ON ccp.`id_modulo` = m.`id_modulo` 
                        WHERE g.`ativo` = '1' ORDER BY g.`nome` ";
                echo combos::combo($sql, $campos[0]['id_grupo']);
            ?>
            </select>
        </td>
        <td>
            <input type='text' name='txt_discriminacao' value='<?=$campos[0]['discriminacao'];?>' title='Digite a Discriminação' size='50' maxlength='55' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <?
                if($campos[0]['forcar_icms'] == 'S') $checked = 'checked';
            ?>
            <input type='checkbox' name='chkt_forcar_preenchimento_icms' value='S' title='Forçar preenchimento de ICMS' id='forcar' class='checkbox' <?=$checked;?>>
            <label for='forcar'>
                Forçar preenchimento de ICMS
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Observação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' rows='1' cols='80' maxlength='80' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
    //Verifico se esse Produto Financeiro já está cadastrado na Base de Dados ...
    $sql = "SELECT `id_produto_financeiro` 
            FROM `produtos_financeiros` 
            WHERE `discriminacao` = '$_POST[txt_discriminacao]' 
            AND `ativo` = '1' 
            AND `id_produto_financeiro` <> '$_POST[hdd_produto_financeiro]' ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe ...
        if(empty($chkt_forcar_preenchimento_icms)) $chkt_forcar_preenchimento_icms = 'N';//Controle com o Checkbox ...
        $sql = "UPDATE `produtos_financeiros` SET `id_grupo` = '$_POST[cmb_grupo]', `discriminacao` = '$_POST[txt_discriminacao]', `forcar_icms` = '$chkt_forcar_preenchimento_icms', `observacao` = '".ucfirst(strtolower($_POST['txt_observacao']))."' WHERE `id_produto_financeiro` = '$_POST[hdd_produto_financeiro]' LIMIT 1 ";
        bancos::sql($sql);
        /****************Controle referente a mudança do Grupo****************/
        /*Sempre que mudar o Grupo de um Produto Financeiro, então todas Contas à Pagar devem ter seus Grupos alterados também 
        independente de estarem aberta(s) ou ter(em) sido quitada(s) ...*/
        if($_POST['cmb_grupo'] != $_POST['hdd_grupo_atual']) {//Houve mudança de Grupo ...
            $sql = "UPDATE `contas_apagares` SET `id_grupo` = '$_POST[cmb_grupo]' WHERE `id_produto_financeiro` = '$_POST[hdd_produto_financeiro]' ";
            bancos::sql($sql);
        }
        /*****************************************************************************************/
        $valor = 2;
    }else {//Já existe ...
        $valor = 3;
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../';
    //Aqui eu vou puxar a Tela única de Filtro de Notas Fiscais que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
    //Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Alterar Produto(s) Financeiro(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Alterar Produto(s) Financeiro(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Grupo
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Forçar ICMS
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=2&id_produto_financeiro=<?=$campos[$i]['id_produto_financeiro'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href = 'alterar.php?passo=1&id_produto_financeiro=<?=$campos[$i]['id_produto_financeiro'];?>'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href = 'alterar.php?passo=1&id_produto_financeiro=<?=$campos[$i]['id_produto_financeiro'];?>' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
        <?
            if($campos[$i]['forcar_icms'] == 'S') {
                echo 'Sim';
            }else {
                echo 'Não';
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
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
}
?>