<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produto_acabado = $_POST['id_produto_acabado'];
    $tela               = $_POST['tela'];
}else {
    $id_produto_acabado = $_GET['id_produto_acabado'];
    $tela               = $_GET['tela'];
}

segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>FOLLOW-UP REGISTRADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>FOLLOW-UP EXCLUÍDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ESTE FOLLOW-UP NÃO PODE SER EXCLUÍDO !<br>USUÁRIO SEM PERMISSÃO PARA APAGAR !</font>";
$mensagem[4] = "<font class='atencao'>NÃO HÁ FOLLOW-UP(S) REGISTRADO(S) !</font>";

/* Exclusão do Follow-up do Produto, caso este foi registrado errado, 
só q só podera apagar apenas quem for o autor do follow_up*/
if(!empty($_POST['id_produto_acabado_follow_up'])) {
    //Aqui eu verifico se quem está apagando o Follow_up é realmente o autor do Follow_up selecionado
    $sql = "SELECT `id_funcionario` AS id_funcionario_follow_up 
            FROM `produtos_acabados_follow_ups` 
            WHERE `id_produto_acabado_follow_up` = '$_POST[id_produto_acabado_follow_up]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_funcionario_follow_up = $campos[0]['id_funcionario_follow_up'];
    //Aqui compara se o autor do Follow-up é o mesmo do usuário que está logado e que está tentando apagar o registro
    if($id_funcionario_follow_up == $_SESSION['id_funcionario']) {//Tem permissão para apagar o Follow_up ...
        $sql = "DELETE FROM `produtos_acabados_follow_ups` WHERE `id_produto_acabado_follow_up` = '$_POST[id_produto_acabado_follow_up]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
}
?>
<html>
<head>
<title>.:: Registrar Follow-up do Produto Acabado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function excluir_follow_up(id_produto_acabado_follow_up) {
    var mensagem = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE FOLLOW-UP ?')
    if(mensagem == true) {
        document.form.id_produto_acabado_follow_up.value = id_produto_acabado_follow_up
        document.form.submit()
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
/*Aqui tem esse macete, porque essa tela também é chamada em Vendas no Orçamento, só que ela é chamada em um iframe
e não em um Pop-Up diretamente, então se essa tela for um iframe ele tem q ignorar essa rotina de atualizar abaixo

//Se for diferente de object, significa que essa tela é um Pop-up normal e daí pode executar a rotina normalmente*/
    if(typeof(parent.document.getElementById('listar_itens')) == 'object') {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
        if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action=''>
<!--*************************Controles de Tela*************************-->
<input type='hidden' name='id_produto_acabado' value="<?=$id_produto_acabado;?>">
<input type='hidden' name='id_produto_acabado_follow_up'>
<input type='hidden' name='tela' value="<?=$tela;?>">
<input type='hidden' name='nao_atualizar'>
<!--*******************************************************************-->
<table width='100%' border='0' cellspacing ='0' cellpadding='0'>
    <tr align='center'>
        <td colspan='2'>
            <?=utf8_encode($mensagem[$valor]);?>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <iframe src='/erp/albafer/modulo/producao/cadastros/produto_acabado/registrar_follow_up.php?id_produto_acabado=<?=$id_produto_acabado;?>&tela=<?=$tela;?>' name='follow_up_produtos_acabados' id='follow_up_produtos_acabados' marginwidth='0' marginheight='0' frameborder='0' height='175' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
//Aqui busca os Follow_ups registrados dos Produtos ...
    $sql = "SELECT l.`login`, pafu.* 
            FROM `produtos_acabados_follow_ups` pafu 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = pafu.`id_funcionario` 
            INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
            WHERE pafu.`id_produto_acabado` = '$id_produto_acabado' ORDER BY pafu.`data_sys` DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='4'>
            <?=utf8_encode($mensagem[4]);?>
        </td>
    </tr>
</table>
<?
    }else {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='4'>
            <fieldset>
                <legend>
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='2' color='#000000'>
                        <b>FOLLOW-UP(S) REGISTRADO(S) DO PRODUTO ACABADO</b>
                    </font>
                </legend>
                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                    <tr class="linhadestaque" align='center'>
                        <td>
                            Itens
                        </td>
                        <td>
                            Login
                        </td>
                        <td>
                            Observa&ccedil;&atilde;o
                        </td>
                        <td>
                            Data / Hora
                        </td>
                    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
                    <tr class='linhanormal' align='center'>
                        <td>
                            <img src="../../../../imagem/menu/excluir.png" border='0' onClick="excluir_follow_up('<?=$campos[$i]['id_produto_acabado_follow_up'];?>')" alt="Excluir Item" title="Excluir Item">
                        </td>
                        <td>
                            <?=$campos[$i]['login'];?>
                        </td>
                        <td align="left">
                            <?=utf8_encode($campos[$i]['observacao']);?>
                        </td>
                        <td>
                            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' '.substr($campos[$i]['data_sys'], 11, 5);?>
                        </td>
                    </tr>
<?
        }
?>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<?
    }
?>
</form>
</body>
</html>