<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>TIPO DE CLIENTE ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>TIPO DE CLIENTE JÁ EXISTENTE.</font>";
$mensagem[3] = "<font class='confirmacao'>PERFIL DE CLIENTE ALTERADO COM SUCESSO.</font>";
$mensagem[4] = "<font class='erro'>PERFIL DE CLIENTE JÁ EXISTENTE.</font>";
$mensagem[5] = "<font class='confirmacao'>TIPO DE CLIENTE EXCLUÍDO COM SUCESSO.</font>";
$mensagem[6] = "<font class='confirmacao'>PERFIL DE CLIENTE EXCLUÍDO COM SUCESSO.</font>";

if(!empty($id)) {
    if($opcao == 1) {//Significa que estou excluindo um Tipo de Cliente  
        $sql = "DELETE FROM `clientes_tipos` WHERE `id_cliente_tipo` = '$id' LIMIT 1 ";
        $valor = 5;	
    }else {//Significa que estou excluindo um Perfil de Cliente
        $sql = "DELETE FROM `clientes_perfils` WHERE `id_cliente_perfil` = '$id' LIMIT 1 ";
        $valor = 6;
    }
    bancos::sql($sql);
}
?>
<html>
<head>
<title>.:: Categorizações ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id, opcao) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id.value = id
        document.form.opcao.value = opcao
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id'>
<input type='hidden' name='opcao'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Tipo(s) de Cliente(s)
        </td>
    </tr>
<?
//Aqui vasculha todos os Tipos de Cliente
    $sql = "SELECT * 
            FROM `clientes_tipos` 
            ORDER BY tipo ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            NÃO HÁ TIPO(S) DE CLIENTE(S) CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>Tipo</b>
        </td>
        <td width='30' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td width='30' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td>
            <img src="../../../../imagem/menu/alterar.png" border='0' onClick="window.location = 'tipos/alterar.php?id_cliente_tipo=<?=$campos[$i]['id_cliente_tipo'];?>'" alt="Alterar Tipo(s) de Cliente" title="Alterar Tipo(s) de Cliente">
        </td>
        <td>
        <?
//Aqui eu verifico se este Tipo de Cliente está sendo utilizado no cadastro de Clientes ...
            $sql = "SELECT id_cliente 
                    FROM `clientes` 
                    WHERE `id_cliente_tipo` = ".$campos[$i]['id_cliente_tipo']." LIMIT 1 ";
            $campos_verificar = bancos::sql($sql);
            if(count($campos_verificar) == 1) {//Já está em uso, não pode excluir ...
        ?>
                <a href = 'detalhes.php?id=<?=$campos[$i]['id_cliente_tipo'];?>&opcao=1' style='cursor:pointer' class='html5lightbox'>?</a>
                <!--Esse objeto é para não dar erro de JS-->
                <input type='hidden'>
        <?					
            }else {//Ainda não está em uso, sendo assim posso excluir normalmente ...
        ?>
                <img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_cliente_tipo'];?>', 1)" style="cursor:help" title="Não pode ser excluído - Detalhes">
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <a href='tipos/incluir.php' title='Incluir Tipo(s) de Cliente(s)'>
                <font color='#FFFF00'>
                    Incluir Tipo(s) de Cliente(s)
                </font>
            </a>
        </td>
    </tr>
</table>
<br>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Perfil(is) de Cliente(s)
        </td>
    </tr>
<?
//Aqui vasculha todos os Perfis de Cliente
    $sql = "SELECT * 
            FROM `clientes_perfils` 
            ORDER BY perfil ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            NÃO HÁ PERFIL(IS) DE CLIENTE(S) CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>Perfil</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Observação</b>
        </td>
        <td width='30' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td width='30' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['perfil'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
            <img src="../../../../imagem/menu/alterar.png" border='0' onClick="window.location = 'perfis/alterar.php?id_cliente_perfil=<?=$campos[$i]['id_cliente_perfil'];?>'" alt="Alterar Perfil(is) de Cliente" title="Alterar Perfil(is) de Cliente">
        </td>
        <td>
        <?
//Aqui eu verifico se este Perfil de Cliente está sendo utilizado no cadastro de Clientes ...
            $sql = "SELECT id_cliente 
                    FROM `clientes` 
                    WHERE `id_cliente_perfil` = ".$campos[$i]['id_cliente_perfil']." LIMIT 1 ";
            $campos_verificar = bancos::sql($sql);
            if(count($campos_verificar) == 1) {//Já está em uso, não pode excluir ...
        ?>
            <a href = 'detalhes.php?id=<?=$campos[$i]['id_cliente_perfil'];?>&opcao=2' style='cursor:help' class='html5lightbox'>?</a>
                <!--Esse objeto é para não dar erro de JS-->
                <input type='hidden'>			
        <?					
            }else {//Ainda não está em uso, sendo assim posso excluir normalmente ...
        ?>
                <img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_cliente_perfil'];?>', 2)" alt="Excluir Perfil(is) de Cliente" title="Excluir Perfil(is) de Cliente">
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <a href='perfis/incluir.php' title='Incluir Perfil(is) de Cliente(s)'>
                <font color='#FFFF00'>
                    Incluir Perfil(is) de Cliente(s)
                </font>
            </a>
        </td>
    </tr>
</table>
</form>
</body>
</html>