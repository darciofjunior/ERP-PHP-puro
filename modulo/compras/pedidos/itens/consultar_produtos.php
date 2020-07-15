<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT fpi.`id_fornecedor_prod_insumo`, g.`nome`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao` 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`referencia` LIKE '%$txt_consultar%' 
                    WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                    AND fpi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
        case 2:
            $sql = "SELECT fpi.`id_fornecedor_prod_insumo`, g.`nome`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao` 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = fpi.`id_produto_insumo` AND pa.`referencia` LIKE '%$txt_consultar%' 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                    AND fpi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
        case 3:
            $sql = "SELECT fpi.`id_fornecedor_prod_insumo`, g.`nome`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao` 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' AND pi.`discriminacao` LIKE '%$txt_consultar%' 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                    AND fpi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
        default:
            $sql = "SELECT fpi.`id_fornecedor_prod_insumo`, g.`nome`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao` 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE fpi.`id_fornecedor` = '$id_fornecedor' 
                    AND fpi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 15, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar_produtos.php?id_pedido=<?=$id_pedido;?>&id_fornecedor=<?=$id_fornecedor;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function voltar(id_produto_insumo) {
    opener.parent.fornecedor_produto.document.location = 'superior.php?id_pedido=<?=$id_pedido;?>&cmb_produto_insumo='+id_produto_insumo
    window.close()
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Consultar Produto(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Grupo
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10' onclick="voltar('<?=$campos[$i]['id_produto_insumo'];?>')">
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="voltar('<?=$campos[$i]['id_produto_insumo'];?>')">
            <a href="javascript:voltar('<?=$campos[$i]['id_produto_insumo'];?>')" class='link'>
            <?
                if(strtoupper($campos[$i]['referencia']) == 'PRAC') {
                    $referencia = genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia'], 0);
                    echo $referencia;
                }else {
                    echo $campos[$i]['referencia'];
                }
            ?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_produtos.php?id_pedido=<?=$id_pedido;?>&id_fornecedor=<?=$id_fornecedor;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
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
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 3;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1'; ?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_fornecedor' value='<?=$_GET['id_fornecedor'];?>'>
<input type='hidden' name='id_pedido' value='<?=$_GET['id_pedido'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='label' value='1' title='Consultar Produtos Insumos por: Referência PI' onclick='document.form.txt_consultar.focus()'>
            <label for='label'>Referência PI</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='label2' value='2' title='Consultar Produtos Insumos por: Referência PA' onclick='document.form.txt_consultar.focus()'>
            <label for='label2'>Referência PA</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' id='label3' value='3' title='Consultar Produtos Insumos por: Discriminação' onclick='document.form.txt_consultar.focus()' checked>
            <label for='label3'>Discrimina&ccedil;&atilde;o</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' id='label4' title='Consultar todos os Produtos Insumos' onclick='limpar()' class='checkbox'>
            <label for='label4'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>