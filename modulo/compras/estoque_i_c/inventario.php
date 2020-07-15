<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/estoque_new.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Significa que o usuário deseja ver somente os PI(s) em que o Estoque seje menor do que Zero 0 ...
    if(!empty($_POST['chkt_todos_estoque_0'])) $condicao = ' AND ei.qtde < 0 ';
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT ei.`qtde`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, pi.`estoque_mensal`, pi.`prazo_entrega`, 
                    pi.`observacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` $condicao 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE g.`referencia` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' 
                    ORDER BY pi.`discriminacao` ";
        break;
        case 2:
            $sql = "SELECT ei.`qtde`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, pi.`estoque_mensal`, pi.`prazo_entrega`, 
                    pi.`observacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` $condicao 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' 
                    ORDER BY pi.`discriminacao` ";
        break;
        default:
            $sql = "SELECT ei.`qtde`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, pi.`estoque_mensal`, pi.`prazo_entrega`, 
                    pi.`observacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` $condicao 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`ativo` = '1' 
                    ORDER BY pi.`discriminacao` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'inventario.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Consultar Estoque
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde<br>Estoque
        </td>
        <td>
            Qtde<br>Metros
        </td>
        <td>
            Un.
        </td>
        <td>
            Produto
        </td>
        <td>
            <font title='Consumo Mensal Médio' style='cursor:help'>
                CMM
            </font>
        </td>
        <td>
            Compra<br> Produção
        </td>
        <td>
            <font title='Prazo de Entrega em Dias' style='cursor:help'>
                Pz. Ent.<br>(Dias)
            </font>
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='right'>
        <?
//Se a qtde em Estoque é menor do que Zero, então exibo essa coluna em Vermelho ...
            $color = ($campos[$i]['qtde'] < 0) ? 'red' : 'black';
//Se for Zero, não mostra nada ... 
            if($campos[$i]['qtde'] == 0) {
                echo '';//Faço assim p/ não dar erro de Relatório ...
            }else {
                echo "<font color=$color>".number_format($campos[$i]['qtde'], 2, ',', '.')."</font>";				
            }
        ?>
        </td>
        <td align='right'>
        <?
            $sql = "SELECT `densidade_aco` 
                    FROM `produtos_insumos_vs_acos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            if(count($campos_pipa) > 0) {
                if($campos_pipa[0]['densidade_aco'] != '0.000') {
                    echo '<font title="Densidade = '.number_format($campos_pipa[0]['densidade_aco'], 3, ',', '.').' KG/m" style="cursor:help">'.segurancas::number_format($campos[$i]['qtde'] / $campos_pipa[0]['densidade_aco'], 2, '.').'</font>';
                }else {
                    echo 'Erro de Cálc. /Divisão por 0';
                }
            }else {
                echo '--------';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='left'>
            <a href="#" onclick="nova_janela('detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'pop', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes' class='link'>
                <?=$campos[$i]['referencia'].' / '.$campos[$i]['discriminacao'];?>
            </a>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['estoque_mensal'], 2, '.');?>
        </td>
        <td align='right'>
            <a href = "javascript:nova_janela('nivel_estoque/pendencias_item.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&ignorar_seguranca_url=S', 'pop', '', '', '', '', '600', '800', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
            <?
                $restante = estoque_ic::compra_producao($campos[$i]['id_produto_insumo']);
                if($restante > 0) {
                    echo str_replace('&nbsp;', '', segurancas::number_format($restante, 2, '.'));
                }else {
                    echo "<font color='red'>".str_replace('&nbsp;', '', segurancas::number_format($restante, 2, '.'))."</font>";
                }
            ?>
            </a>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['prazo_entrega'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'inventario.php'" class='botao'>
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
<title>.:: Consultar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled = true
        document.form.txt_consultar.value = ''
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled = false
        document.form.txt_consultar.value = ''
        document.form.txt_consultar.focus()
    }
}

function todos_estoque_menor_zero() {
    if(document.form.chkt_todos_estoque_0.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.opcao.checked = false
        document.form.opcao.disabled = true
        document.form.txt_consultar.disabled = true
        document.form.txt_consultar.value = ''
    }else {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = false
        document.form.opcao.checked = false
        document.form.opcao.disabled = false
        document.form.txt_consultar.disabled = false
        document.form.txt_consultar.value = ''
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
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Estoque
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <label for="consultar">Consultar</label>
            <input type="text" name="txt_consultar" size="45" maxlength="45" id="consultar" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td width='20%'>
            <input type="radio" name="opt_opcao" value="1" title="Consultar Produtos Insumos por: Referência" id="opt1" onclick="document.form.txt_consultar.focus()">
            <label for="opt1">Referência</label>
        </td>
        <td width='20%'>
            <input type="radio" name="opt_opcao" value="2" title="Consultar Produtos Insumos por: Discriminação" id="opt2" onclick="document.form.txt_consultar.focus()" checked>
            <label for="opt2">Discrimina&ccedil;&atilde;o</label>
        </td>
    </tr>
    <tr class="linhanormal">
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os Produtos Insumos" id="todos" onclick='limpar()' class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='chkt_todos_estoque_0' value='1' title="Consultar Todos com estoque < 0" id="todos_estoque_0" onclick='todos_estoque_menor_zero()' class="checkbox">
            <label for="todos_estoque_0">Todos com estoque < 0</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color='red'><b>Observação:</b></font>

<b>* Só não traz P.I(s) do Tipo PRAC (Reabilitado pelo Roberto no dia 18/07/2006)</b>
</pre>