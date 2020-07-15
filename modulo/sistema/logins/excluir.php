<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">USUÁRIO EXCLUÍDO COM SUCESSO.</font>';

if(!empty($_POST['chkt_login'])) {
    //Aqui eu renomeio essa varíavel porque já existe uma na sessão do Sistema chamada $id_login ...
    foreach($_POST['chkt_login'] as $id_login_loop) {
        //Exclui todas as Permissões do Login Corrente ...
        $sql = "DELETE FROM `tipos_acessos` WHERE `id_login` = '$id_login_loop' ";
        bancos::sql($sql);
        //Excluindo o Login "na realidade Oculta o Login" ...
        $sql = "UPDATE `logins` SET `ativo` = '0' WHERE `id_login` = '$id_login_loop' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Listagem somente dos Logins ativos no ERP P/ poder excluir ...
$sql = "SELECT `id_login`, `id_funcionario`, `login`, `tipo_login`, `ativo` 
        FROM `logins` 
        WHERE `ativo` >= '1' ORDER BY `login` ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../html/index.php?valor=4'
    </Script>
<?
    exit;
}
?>
<html>
<head>
<title>.:: Excluir Login(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Login(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            Login
        </td>
        <td>
            Tipo de Login
        </td>
        <td>
            Tipo de Acesso
        </td>
        <td>
            Nome
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_login[]' value="<?=$campos[$i]['id_login'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=$campos[$i]['login'];?>
        </td>
        <td>
            <?=$campos[$i]['tipo_login'];?>
        </td>
        <td>
        <?
            if($campos[$i]['ativo'] == 0) {
                echo 'SEM ACESSO';
            }else if($campos[$i]['ativo'] == 1) {
                echo 'ACESSO INTERNO';
            }else if($campos[$i]['ativo'] == 2) {
                echo 'ACESSO INTERNO E EXTERNO';
            }
        ?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['id_funcionario'] > 0) {
                //Busca o nome do Funcionário ...
                $sql = "SELECT `id_empresa`, `nome` 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
                $campos_funcionario = bancos::sql($sql);
                echo $campos_funcionario[0]['nome'];
            }
        ?>
        </td>
        <td>
        <?
            if($campos_funcionario[0]['id_empresa'] > 0) {
                //Busca o nome do Funcionário ...
                $sql = "SELECT `nomefantasia` 
                        FROM `empresas` 
                        WHERE `id_empresa` = '".$campos_funcionario[0]['id_empresa']."' LIMIT 1 ";
                $campos_empresa = bancos::sql($sql);
                echo $campos_empresa[0]['nomefantasia'];
            }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
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
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Ao excluir o Login, será apagado automaticamente todas as permissões deste.
</pre>