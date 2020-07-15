<?
require('../../../../lib/segurancas.php');
require('../../../../lib/estoque_new.php');

if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_todos.php', '../../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo/industrial/pa_componente_esp.php', '../../../../');
}
session_start('funcionarios');

$mensagem[1] = '<font class="erro">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">CUSTO ATUALIZADO COM SUCESSO.</font>';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT ei.`qtde`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
                    INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`referencia` LIKE '%$txt_consultar%' 
                    WHERE pi.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
        case 2:
            $sql = "SELECT ei.`qtde`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
                    INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE pi.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
        default:
            $sql = "SELECT ei.`qtde`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.`id_produto_insumo` = pi.`id_produto_insumo` 
                    INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'consultar_produto_insumo.php?id_produto_acabado_custo=<?=$_POST['id_produto_acabado_custo'];?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Produto Insumo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
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
            Qtde<br/> Estoque
        </td>
        <td>
            Qtde<br/> Metros
        </td>
        <td>
            Compra<br/> Produção
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href = 'consultar_produto_insumo.php?passo=2&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center'>
            <a href = 'consultar_produto_insumo.php?passo=2&id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>' class='link'>
                <?=$campos[$i]['referencia'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
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
        <td align='right'>
            <a href = "javascript:nova_janela('../../../compras/estoque_i_c/nivel_estoque/pendencias_item.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&ignorar_seguranca_url=S', 'pop', '', '', '', '', '600', '800', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
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
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_produto_insumo.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>'" class='botao'>
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
    //Nesse primeiro momento, tanto a Matéria Prima como a Matéria Prima Ideal adquirem o mesmo PI selecionado pelo user ...
    $sql = "UPDATE `produtos_acabados_custos` SET `id_produto_insumo` = '$_GET[id_produto_insumo]', `id_produto_insumo_ideal` = '$_GET[id_produto_insumo]', `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_produto_acabado_custo` = '$_GET[id_produto_acabado_custo]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_etapa2.php?id_produto_acabado_custo=<?=$_GET['id_produto_acabado_custo'];?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produto Insumo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
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
</html>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='id_produto_acabado_custo' value='<?=$id_produto_acabado_custo;?>'>
<input type='hidden' name='passo' value='1'>
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
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Insumos por: Referência' id='label'>
            <label for='label'>Referência</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Insumos por: Referência' id='label2' checked>
            <label for='label2'>Discrimina&ccedil;&atilde;o</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' onClick='limpar()' value='1' title="Consultar todos os Produtos Insumos" id='label3' class='checkbox'>
            <label for='label3'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
<?}?>