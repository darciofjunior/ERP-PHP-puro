<?
require('../../../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) {//Significa que essa Tela abriu de forma normal, não como sendo Pop-UP ...
    require '../../../../../lib/menu/menu.php';
    segurancas::geral($PHP_SELF, '../../../../../');
}
require('../../../../../lib/genericas.php');

$mensagem[1] = '<font class="confirmacao">NOVA COMISSÃO MARGEM DE LUCRO EXCLUÍDA COM SUCESSO.</font>';

if(!empty($id_nova_comissao_margem_lucro)) {//Exclusão das Comissões Margens de Lucro
    $sql = "DELETE FROM `novas_comissoes_margens_lucros` WHERE `id_nova_comissao_margem_lucro` = '$id_nova_comissao_margem_lucro' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Comissão vs Margem de Lucro ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_nova_comissao_margem_lucro) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == true) {
        document.form.id_nova_comissao_margem_lucro.value = id_nova_comissao_margem_lucro
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_nova_comissao_margem_lucro'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Comissão vs Margem de Lucro
        </td>
    </tr>
<?
//Aqui vasculha todas as Novas Comissões Margens de Lucro ...
    $sql = "SELECT * 
            FROM `novas_comissoes_margens_lucros` 
            ORDER BY margem_lucro ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            <font size='-1'>
                NÃO HÁ NOVA(S) COMISSÃO(ÕES) MARGEM(NS) DE LUCRO(S) CADASTRADAS.
            </font>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal' align='center'>
        <td rowspan='2' bgcolor='#CCCCCC'>
            <b>Nova Margem de Lucro</b>
        </td>
        <td colspan='4' bgcolor='#CCCCCC'>
            <font size='-1'>
                <b>Comiss&atilde;o Vendedor  Externo ou Representante</b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CCCCCC'>
            <font size='-1'>
                <b>Comissão Vendedor</b>
            </font>
        </td>
        <td colspan='2' bgcolor='#CCCCCC'>
            <font size='-1'>
                <b>Supervis&atilde;o</b>
            </font>
        </td>
        <td rowspan='2' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
        <td rowspan='2' bgcolor='#CCCCCC'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b>Dentro de SP </b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Fora de SP
                <br/><?=(genericas::variavel(54) * 100);?> %
                Base dentro de SP
            </b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Interior de SP
                <br/><?=(genericas::variavel(55) * 100);?> %
                Base dentro de SP
            </b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Autônomo
                <br/><?=(genericas::variavel(56) * 100);?> %
                Base dentro de SP
            </b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Sup. Interno
                <br/><?=(genericas::variavel(57) * 100);?> %
                Base dentro de SP
            </b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Exportação
                <br/><?=(genericas::variavel(58) * 100);?> %
                Base dentro de SP
            </b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Outras UF
                <br/><?=(genericas::variavel(88) * 100);?> %
                Base dentro de SP
            </b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>Autônomo
                <br/><?=(genericas::variavel(89) * 100);?> %
                Base dentro de SP
            </b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas ; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?='> '.number_format($campos[$i]['margem_lucro'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['base_comis_dentro_sp'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['comis_vend_fora_sp'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['comis_vend_interior_sp'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['comis_autonomo'], 2, ',', '.').'%';?>
        </td>
        <td>
           <?=number_format($campos[$i]['comis_vend_sup_interno'], 2, ',', '.').'%';?>
        </td>
        <td>
           <?=number_format($campos[$i]['comis_export'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['comis_sup_outras_ufs'], 2, ',', '.').'%';?>
        </td>
        <td>
            <?=number_format($campos[$i]['comis_sup_autonomo'], 2, ',', '.').'%';?>
        </td>
        <td>
        <?
            //Só exibo esse link, quando essa Tela não foi aberta como sendo Pop-UP ...
            if(empty($_GET['pop_up'])) {//Não é Pop-UP ...
        ?>
            <img src="../../../../../imagem/menu/alterar.png" border='0' onClick="window.location = 'alterar_comissao.php?id_nova_comissao_margem_lucro=<?=$campos[$i]['id_nova_comissao_margem_lucro'];?>'" alt="Alterar Comissão" title="Alterar Comissão">
        <?
            }
        ?>
        </td>
        <td>
        <?
            //Só exibo esse link, quando essa Tela não foi aberta como sendo Pop-UP ...
            if(empty($_GET['pop_up'])) {//Não é Pop-UP ...
        ?>
            <img src="../../../../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_nova_comissao_margem_lucro'];?>')" alt="Excluir Comissão" title="Excluir Comissão">
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
        <td colspan='11'>
        <?
            //Só exibo esse link, quando essa Tela não foi aberta como sendo Pop-UP ...
            if(empty($_GET['pop_up'])) {//Não é Pop-UP ...
        ?>
            <a href='incluir_comissao.php' title='Incluir Nova Comissão Margem de Lucro'>
                <font color='#FFFF00'>
                    Incluir Nova Comissão Margem de Lucro
                </font>
            </a>
        <?
            }else {//É Pop-Up ...
                echo '&nbsp;';
            }
        ?>
        </td>
    </tr>
</table>
</form>
<pre>
- Comissão de Supervisão é 1% da venda de seus representados (Exportação 1,5%).
- Comissão de Excesso de Estoque <img src='/erp/albafer/imagem/queima_estoque.png' title='Excesso de Estoque' alt='Excesso de Estoque' border='0'> = <?=number_format(genericas::variavel(46), 1, ',', '.');?> %
</pre>
</body>
</html>
<!--Aqui chamamos esse arquivo p/ que seja visualizada a Data Limite e Comissão Extra 
dos Grupos PA(s) vs Empresa Divisão que estão com essa Promoção ...-->
<center>
    <iframe src='/erp/albafer/modulo/producao/cadastros/produto_acabado/grupo_pa/alterar.php?passo=1&opcao_itens_comissao=1&pop_up=1' width='95%' height='500'></iframe>
</center>
<br/>
<pre>
<?
    $fator_reducao_comissao_extra_x_ml  = genericas::variavel(84);
?>
<b>Cálculo da Comissão Extra:</b>

_ Se ML >= <?=number_format($fator_reducao_comissao_extra_x_ml, 1, ',', '.');?> * ML min do grupo x divisão , pagamos 100% da Comissão Extra;

_ Se ML < <?=number_format($fator_reducao_comissao_extra_x_ml, 1, ',', '.');?> * ML min do grupo x divisão, pagamos 50% da Comissão Extra (até 01/08/13 não pagávamos comissão extra nesta condição);

_ A % da Comissão Extra é corrigida proporcionalmente pela % Base dentro de SP da tabela Comissão x  Margem de Lucro, com exceção na exportação, onde pagamos 100% da Comissão Extra.
<font color='red'>
<b>Exemplo:</b> Autônomo recebe 140% da Comissão Extra do Grupo x Divisão.
</font>
</pre>
<!--Aqui chamamos esse arquivo p/ que sejam visualizadas as Cidades próximas da Grande 
São Paulo - essas cidades por serem cidades muito próximas a Capital, tratamos como se
fossem aqui de dentro do Estado mesmo ...-->
<center>
    <iframe src='/erp/albafer/modulo/vendas/representante/comissoes/comissoes_cidades/comissoes_cidades.php?pop_up=1' width='95%' height='500'></iframe>
</center>