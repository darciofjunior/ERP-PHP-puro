<?
segurancas::geral($PHP_SELF, '../../../');

if(!empty($_POST['chkt_carta_correcao'])) {
    foreach($_POST['chkt_carta_correcao'] as $id_carta_correcao) {
//Deleta os Itens da Carta de Correção ...
        $sql = "DELETE FROM `cartas_correcoes_itens` WHERE `id_carta_correcao` = '$id_carta_correcao' ";
        bancos::sql($sql);
//Deleta a Própria Carta de Correção ...
        $sql = "DELETE FROM `cartas_correcoes` WHERE `id_carta_correcao` = '$id_carta_correcao' LIMIT 1 ";
        //bancos::sql($sql);
    }
    $valor = 2;
}

$nivel_path = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Cartas de Correção que serve para o Sistema Todo ...
require('itens/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Excluir Cartas de Correção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='5'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
                <b><?=$mensagem[$valor];?></b>
            </font>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='5'>
            Excluir Carta(s) de Correção(ões)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            N.&ordm; Carta
        </td>
        <td>
            Data
        </td>
        <td>
            N.&ordm; da NF
        </td>
        <td>
            Cliente / Fornecedor
        </td>
        <td>
            <label for="todos">Todos</label>
            <input type='checkbox' name='chkt' id="todos" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox">
        </td>
    </tr>
<?
    require('../../classes/nf_carta_correcao/class_carta_correcao.php');
    for ($i = 0; $i < $linhas; $i++) {
        $dados = carta_correcao::dados_nfs($campos[$i]['id_carta_correcao']);
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['id_carta_correcao'];?>
        </td>
        <td>
            <?=$campos[$i]['data_sys'];?>
        </td>
        <td>
            <?=$dados['numero_nf'];?>
        </td>
        <td align="left">
            <?=$dados['negociador'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_carta_correcao[]' value="<?=$campos[$i]['id_carta_correcao'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
    }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='5'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
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