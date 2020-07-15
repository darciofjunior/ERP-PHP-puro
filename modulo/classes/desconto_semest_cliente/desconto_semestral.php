<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/variaveis/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = '<font class="confirmacao">FAIXA DE DESCONTO DO CLIENTE EXCLUÍDA COM SUCESSO.</font>';
$mensagem[2] = '<font class="confirmacao">DESCONTO DE CLIENTE DESFEITO A ALTERAÇÃO COM SUCESSO.</font>';

/*********************************************************************************************/
/*************************Função específica somente p/ esse arquivo*************************/
/*********************************************************************************************/
function desfazer_alterar_desconto_cliente() {
    $data_atual = date('Y-m-d');
    /*Busco somente os Clientes que compraram algo no último Ano p/ voltar o Backup de Desconto Antigo 
    somente em cima desses ...*/
    $sql = "SELECT DISTINCT(nfs.`id_cliente`) AS `id_cliente` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND c.`ativo` = '1' 
            WHERE nfs.`data_emissao` >= DATE_ADD('$data_atual', INTERVAL -365 DAY) 
            ORDER BY nfs.id_cliente ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) $vetor_cliente[] = $campos[$i]['id_cliente'];
    foreach($vetor_cliente as $id_cliente) {
        //Volto backup dos descontos Antigos p/ o Cliente independente de Empresa Divisão ...
        $sql = "UPDATE `clientes_vs_representantes` SET `desconto_cliente` = `desconto_cliente_old` WHERE `id_cliente` = '$id_cliente' ";
        bancos::sql($sql);
    }
}
/*********************************************************************************************/

if(!empty($_POST['id_descontos_clientes'])) {//Exclusão das Faixa(s) de Desconto(s) do Cliente
    $sql = "DELETE FROM `descontos_clientes` WHERE `id_descontos_clientes` = '$_POST[id_descontos_clientes]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

if($_GET[desfazer_alterar_desconto_cliente] == 1) {
    desfazer_alterar_desconto_cliente();
    $valor = 2;
}
/*********************************************************************************************/
?>
<html>
<head>
<title>.:: Faixa de Desconto do Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_descontos_clientes) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_descontos_clientes.value = id_descontos_clientes
        document.form.submit()
    }
}

function desfazer_alterar_desconto_cliente() {
    var mensagem = confirm('DESEJA REALMENTE EXECUTAR ESSA FUNÇÃO ?')
    if(mensagem == true) {
        alert('ESSA ROTINA É UM POUCO DEMORADA !!!\n\nAGUARDE DE 2 A 3 MINUTOS P/ A SUA EXECUÇÃO COMPLETA !')
        window.location = '<?=$PHP_SELF.'?desfazer_alterar_desconto_cliente=1';?>'
    }
}

function alterar_desconto_cliente() {
    var mensagem = confirm('DESEJA REALMENTE EXECUTAR ESSA FUNÇÃO ?')
    if(mensagem == true) {
        alert('ESSA ROTINA É UM POUCO DEMORADA !!!\n\nAGUARDE ALGUM(NS) MINUTOS P/ A SUA EXECUÇÃO COMPLETA !')
        html5Lightbox.showLightbox(7, 'alterar_desconto_cliente.php')
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_descontos_clientes'>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Faixa de Desconto do Cliente do Grupo (Anual)
        </td>
    </tr>
<?
//Aqui vasculha todas as Faixas de Desconto do Cliente
    $sql = "SELECT * 
            FROM `descontos_clientes` 
            WHERE `tabela_analise` = '0' ORDER BY valor_semestral ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            NÃO HÁ FAIXA(S) DE DESCONTO(S) DO CLIENTE CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Valor Faturamento Anual</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Desconto Cliente</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC' width='30'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC' width='30'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="right">
        <td>
            <?='< '.number_format($campos[$i]['valor_semestral'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['desconto_cliente'], 2, ',', '.').'%';?>
        </td>
        <td>
            <img src="../../../imagem/menu/alterar.png" border='0' onClick="window.location = 'alterar_desconto.php?id_descontos_clientes=<?=$campos[$i]['id_descontos_clientes'];?>'" alt="Alterar Faixa de Desconto do Cliente" title="Alterar Faixa de Desconto do Cliente">
        </td>
        <td>
            <img src="../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_descontos_clientes'];?>')" alt="Excluir Faixa de Desconto do Cliente" title="Excluir Faixa de Desconto do Cliente">
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <a href='incluir_desconto.php?tabela_analise=0' title='Incluir Faixa de Desconto do Cliente'>
                <font color='#FFFF00'>
                    Incluir Faixa de Desconto do Cliente
                </font>
            </a>
        </td>
    </tr>
</table>
<br>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Faixa de Desconto do Cliente por Divisão (Anual)
        </td>
    </tr>
<?
//Aqui vasculha todas as Faixas de Desconto do Cliente
    $sql = "SELECT * 
            FROM `descontos_clientes` 
            WHERE `tabela_analise` = '1' ORDER BY valor_semestral ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            NÃO HÁ FAIXA(S) DE DESCONTO(S) DO CLIENTE CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Valor Faturamento Anual</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC'>
            <font size='1'>
                <b>Desconto Cliente</b>
            </font>
        </td>
        <td bgcolor='#CCCCCC' width='30'>
            &nbsp;
        </td>
        <td bgcolor='#CCCCCC' width='30'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='right'>
        <td>
            <?='< '.number_format($campos[$i]['valor_semestral'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['desconto_cliente'], 2, ',', '.').'%';?>
        </td>
        <td>
            <img src="../../../imagem/menu/alterar.png" border='0' onClick="window.location = 'alterar_desconto.php?id_descontos_clientes=<?=$campos[$i]['id_descontos_clientes'];?>'" alt="Alterar Faixa de Desconto do Cliente" title="Alterar Faixa de Desconto do Cliente">
        </td>
        <td>
            <img src="../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_descontos_clientes'];?>')" alt="Excluir Faixa de Desconto do Cliente" title="Excluir Faixa de Desconto do Cliente">
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <a href='incluir_desconto.php?tabela_analise=1' title='Incluir Faixa de Desconto do Cliente'>
                <font color='#FFFF00'>
                    Incluir Faixa de Desconto do Cliente
                </font>
            </a>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_desconto_clientes_representantes' value='Relatório de Desconto de Clientes vs Representantes' title='Relatório de Desconto de Clientes vs Representantes' onclick="html5Lightbox.showLightbox(7, 'rel_desc_clientes_rep.php')" class='caixadetexto'>
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <?
                /*Pelo fato de as funções desses botões serem comprometedoras, só mostro esses p/ Roberto 62 Diretor, 
                Dárcio 98 porque programa e Wilson Nishimura 136 */
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
            ?>
                <input type='button' name='cmd_desfazer_alterar_desc_cliente' value='&lt;&lt; Desfazer / Alterar Desconto do Cliente' title='Desfazer / Alterar Desconto de Clientes' onclick='return desfazer_alterar_desconto_cliente()' class='botao'>
                <input type='button' name='cmd_alterar_desc_cliente' value='Alterar Desconto do Cliente &gt;&gt;' title='Alterar Desconto de Clientes' onclick='return alterar_desconto_cliente()' class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>