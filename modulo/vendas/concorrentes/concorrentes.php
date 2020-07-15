<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='confirmacao'>CONCORRENTE INCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>CONCORRENTE JÁ EXISTENTE.</font>";
$mensagem[3] = "<font class='confirmacao'>CONCORRENTE EXCLUÍDO COM SUCESSO.</font>";
$mensagem[4] = "<font class='erro'>ESSE CONCORRENTE NÃO PODE SER EXCLUÍDO ! DESATRELE O(S) PA(S) QUE ESTÁ(ÃO) LIGADO(S) A ESTE PRIMEIRO.</font>";

if(!empty($_POST['id_concorrente'])) {//Exclusão do Concorrente ...
//Verifico se existe um PA atrelado p/ o Concorrente que está sendo excluindo ...
    $sql = "SELECT id_concorrente_prod_acabado 
            FROM `concorrentes_vs_prod_acabados` 
            WHERE `id_concorrente` = '$_POST[id_concorrente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {//Não existe nenhum PA atrelado, pode excluir normalmente ...
        $sql = "DELETE FROM `concorrentes` WHERE `id_concorrente` = '$_POST[id_concorrente]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 3;
    }else {//Existe 1 PA Concorrente ...
        $valor = 4;
    }
}
?>
<html>
<head>
<title>.:: Concorrente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_concorrente) {
    var resposta = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(resposta == false) {
        return false
    }else {
        document.form.id_concorrente.value = id_concorrente
        document.form.submit()
    }
}

function relatorio_desc_clientes_rep() {
    nova_janela('rel_desc_clientes_rep.php', 'RELATORIO', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_concorrente'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='10'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Concorrente(s)
        </td>
    </tr>
<?
//Aqui vasculha todas as Faixas de Desconto do Cliente
    $sql = "SELECT * 
            FROM `concorrentes` 
            WHERE `ativo` = '1' ORDER BY nome ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='10'>
            NÃO HÁ CONCORRENTE(S) CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>Concorrente</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Lista de Preço</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <font title='Data de Início de Validade' style='cursor:help'>
                <b>Data Ini Val</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Fonte Pesquisa</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Condição</b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Obs</b>
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['lista_preco_origem'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['fonte_pesquisa'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['condicao'];?>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['observacao'])) echo "<img width='28'  height='23' title='".$campos[$i]['observacao']."' src='../../../imagem/olho.jpg'>"."<br>";
        ?>
        </td>
        <td>
            <img src = '../../../imagem/menu/incluir.png' border='0' onclick="window.location = 'concorrentes_vs_pas/concorrentes_vs_pas.php?id_concorrente=<?=$campos[$i]['id_concorrente'];?>'" title='Gerenciar PA(s)' alt='Gerenciar PA(s)'>
        </td>
        <td>
            <img src = '../../../imagem/menu/alterar.png' border='0' onclick="window.location = 'alterar.php?id_concorrente=<?=$campos[$i]['id_concorrente'];?>'" title='Alterar Concorrente' alt='Alterar Concorrente'>
        </td>
        <td>
        <?
//Visível somente para Roberto 62, Dárcio 98 e Netto 147 porque programam ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
        ?>
            <img src='../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_concorrente'];?>')" alt='Excluir Concorrente' title='Excluir Concorrente'>
        <?
            }
        ?>
        </td>
        <td>
            <img src = '../../../imagem/propriedades.png' border='0' onclick="html5Lightbox.showLightbox(7, 'follow_ups.php?id_concorrente=<?=$campos[$i]['id_concorrente'];?>')" style='cursor:pointer' title='Follow-UP(s)' alt='Follow-UP(s)'>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque' align='center'>
        <td align='left'>
            <a href='incluir.php' title='Incluir Concorrente'>
                <font color='#FFFF00'>
                    Incluir Concorrente
                </font>
            </a>
        </td>
        <td colspan='9'>
            <input type='button' name='cmd_relatorio_concorrentes' value='Relatório de Concorrentes' title='Relatório de Concorrentes' onclick="window.location = 'relatorio.php'" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>