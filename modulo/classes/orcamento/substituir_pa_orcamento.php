<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');//Essa biblioteca é usada dentro da Vendas por isso não posso arrancar ...
require('../../../lib/intermodular.php');//Essa biblioteca é usada dentro da Vendas por isso não posso arrancar ...
require('../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/alterar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Aqui traz todos os PA com exceção dos que são pertencentes a Família de Componentes
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `referencia` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `discriminacao` ";
        break;
        case 2:
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `discriminacao` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `discriminacao` ";
        break;
        default:
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `ativo` = '1' ORDER BY `discriminacao` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'substituir_pa_orcamento.php?id_orcamento_venda_item=<?=$_POST['id_orcamento_venda_item'];?>&id_produto_acabado=<?=$_POST['id_produto_acabado'];?>&valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Substituir Produto(s) Acabado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function substituir_pa_orcamento(id_produto_acabado_substituir) {
    var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA SUBSTITUIR ESTE ITEM DE ORÇAMENTO POR ESSE NOVO P.A. ?')
    if(resposta == true) {
        window.location = 'substituir_pa_orcamento.php?passo=2&id_orcamento_venda_item=<?=$id_orcamento_venda_item;?>&id_produto_acabado=<?=$id_produto_acabado;?>&id_produto_acabado_substituir='+id_produto_acabado_substituir
    }
}
</Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Substituir Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Produto
        </td>
    </tr>
<?
/************************************************************/
        for($i = 0; $i < $linhas; $i++) {
/****************************************************************************************************/
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href="javascript:substituir_pa_orcamento('<?=$campos[$i]['id_produto_acabado'];?>')" title='Substituir P.A. do Orçamento' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href="javascript:substituir_pa_orcamento('<?=$campos[$i]['id_produto_acabado'];?>')" title='Substituir P.A. do Orçamento' class='link'>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'substituir_pa_orcamento.php?id_orcamento_venda_item=<?=$id_orcamento_venda_item;?>&id_produto_acabado=<?=$id_produto_acabado;?>'" class='botao'>
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
}else if($passo == 2) {
//substitui o P.A. antigo do Item do Orçamento pelo P.A. novo ...
    $sql = "UPDATE `orcamentos_vendas_itens` SET `id_produto_acabado` = '$_GET[id_produto_acabado_substituir]' WHERE `id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
    bancos::sql($sql);
//Busco qual é o $id_orcamento_venda porque vou precisar dessa variável p/ passar por parâmetro + abaixo ...
    $sql = "SELECT id_orcamento_venda 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
    $campos = bancos::sql($sql);
/*******************************************************************************************************/
    vendas::calculo_preco_liq_final_item_orc($_GET[id_orcamento_venda_item]);
//Aqui eu atualizo a ML Est do Iem do Orçamento ...
    custos::margem_lucro_estimada($_GET[id_orcamento_venda_item]);
/*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
    vendas::calculo_ml_comissao_item_orc($campos[0]['id_orcamento_venda'], $_GET[id_orcamento_venda_item]);
?>
    <Script Language = 'JavaScript'>
        alert('PRODUTO ACABADO SUBSTITUÍDO COM SUCESSO !')
        window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$campos[0]['id_orcamento_venda'];?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Substituir Produto(s) Acabado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i++)  document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='id_orcamento_venda_item' value='<?=$_GET['id_orcamento_venda_item'];?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$_GET['id_produto_acabado'];?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Substituir Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='label1' value='1' title='Consultar Produtos Acabados por: Referência' onclick='document.form.txt_consultar.focus()' checked>
            <label for='label1'>
                Referência
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='label2' value='2' title='Consultar Produtos Acabados por: Discriminação' onclick='document.form.txt_consultar.focus()'>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' id='label3' value='1' title='Consultar todos os Produtos Acabados' onclick='limpar()' class='checkbox'>
            <label for='label3'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'orcamentos.php?id_produto_acabado=<?=$_GET['id_produto_acabado'];?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>