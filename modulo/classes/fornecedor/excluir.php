<?
require('../../../lib/genericas.php');
require '../../../lib/menu/menu.php';//CASO EXISTA EU DESVIO A CLASSE ...
require('../../../lib/financeiros.php');
require('../../../lib/data.php');
session_start('funcionarios');

if(!empty($_POST['chkt_tudo_fornecedor'])) {
    foreach($_POST['chkt_tudo_fornecedor'] as $id_fornecedor) {
        if(cascate::consultar('id_fornecedor', 'nfe, pedidos', $id_fornecedor)) {
            $valor = 3;
        }else {
            $sql = "UPDATE `fornecedores` SET `ativo` = '0' WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }
    }
}
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Excluir Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Fornecedor
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Razão Social
        </td>
        <td>
            Fone 1
        </td>
        <td>
            Fone 2
        </td>
        <td>
            Fax
        </td>
        <td>
            Produtos
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=$campos[$i]['ddd_fone1'].' '.$campos[$i]['fone1'];?>
        </td>
        <td>
            <?=$campos[$i]['ddd_fone2'].' '.$campos[$i]['fone2'];?>
        </td>
        <td>
            <?=$campos[$i]['ddd_fax'].' '.$campos[$i]['fax'];?>
        </td>
        <td>
            <?=$campos[$i]['produto'];?>
        </td>
        <td align='center'>
            <input type='checkbox' name='chkt_tudo_fornecedor[]' value='<?=$campos[$i]['id_fornecedor'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
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
<?}?>