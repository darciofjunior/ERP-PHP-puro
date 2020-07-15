<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_new.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CONSUMO MÉDIO MENSAL ATUALIZADO COM SUCESSO.</font>";

if($passo == 1) {
    //Não exibe PI's que são do Tipo do PRAC
    $nao_prac = "AND g.`id_grupo` <> '9' ";
    //Aqui continua com o Filtro de seleção normalmente - idéia do Luisão ...
    if(empty($chkt_visualizar_pis_mmv)) $id_produto_insumo_temp = genericas::pi_dentro_custo_pa();//se nao passar parametros trazerar de todas etapas

    switch($opt_opcao) {
        case 1:
            $sql = "SELECT g.`nome`, g.`referencia`, pi.`estocagem`, pi.`id_produto_insumo`, 
                    pi.`unidade_conversao`, pi.`discriminacao`, pi.`estoque_mensal` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.id_produto_insumo = pi.id_produto_insumo 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.referencia LIKE '%$txt_consultar%' 
                    WHERE pi.`ativo` = '1' $nao_prac $id_produto_insumo_temp ORDER BY pi.discriminacao ";
        break;
        case 2:
            $sql = "SELECT g.`nome`, g.`referencia`, pi.`estocagem`, pi.`id_produto_insumo`, 
                    pi.`unidade_conversao`, pi.`discriminacao`, pi.`estoque_mensal` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.id_produto_insumo = pi.id_produto_insumo 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE pi.`ativo` = '1' 
                    AND pi.`discriminacao` LIKE '%$txt_consultar%' $nao_prac $id_produto_insumo_temp ORDER BY pi.discriminacao ";
        break;
        case 3:
            $sql = "SELECT g.`nome`, g.`referencia`, pi.`estocagem`, pi.`id_produto_insumo`, 
                    pi.`unidade_conversao`, pi.`discriminacao`, pi.`estoque_mensal` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.id_produto_insumo = pi.id_produto_insumo 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE pi.`ativo` = '1' 
                    AND pi.`observacao` LIKE '%$txt_consultar%' $nao_prac $id_produto_insumo_temp ORDER BY pi.discriminacao ";
        break;
        default:
            $sql = "SELECT g.`nome`, g.`referencia`, pi.`estocagem`, pi.`id_produto_insumo`, 
                    pi.`unidade_conversao`, pi.`discriminacao`, pi.`estoque_mensal` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `estoques_insumos` ei ON ei.id_produto_insumo = pi.id_produto_insumo 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE pi.`ativo` = '1' $nao_prac $id_produto_insumo_temp ORDER BY pi.discriminacao ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'cmm.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consumo Mensal Médio ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'controle.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }else {
        var elementos = document.form.elements
        var objetos_linha = 2
        var objetos_fim = 2
        for(var i = 1; i < (elementos.length - objetos_fim); i+=objetos_linha) {
            if(elementos[i].type == 'checkbox' && elementos[i].checked == true) {
                elementos[i + 1].value = strtofloat(elementos[i + 1].value)
            }
        }
    }
}

function alterar_estoque_estocagem(id_produto_insumo, qtde_estoque, indice) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA MUDAR A ESTOCAGEM P/ "NÃO" E "ZERAR O ESTOQUE" DESSE PRODUTO INSUMO ?')
    if(resposta == true) ajax('estoque_estocagem.php?passo=1&id_produto_insumo='+id_produto_insumo+'&qtde_estoque='+qtde_estoque+'&indice='+indice, 'div_estoque_estocagem'+indice)
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Consultar Produto(s) Insumo(s) - Consumo Mensal Médio
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            Grupo
        </td>
        <td>
            Produto
        </td>
        <td>
            CMM do Sistema
        </td>
        <td>
            CMM Últimos <br/><?=intval(genericas::variavel(71));?> Meses
        </td>
        <td>
            CMMV
        </td>
        <td>
            Estoque / Estocagem
        </td>
    </tr>
<?		
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_insumo[]' value="<?=$campos[$i]['id_produto_insumo'];?>" onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../../estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'POP', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Consumo Mensal Médio" class="link">
            <?
                echo genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia'], 0).' * ';
                echo $campos[$i]['discriminacao'];
            ?>
            </a>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['estoque_mensal'], 2, '.');?>
        </td>
        <td>
            <input type='text' name='txt_cmm_auto[]' value="<?=compras_new::consumo_medio_mensal($campos[$i]['id_produto_insumo']);?>" maxlength="10" size="10" onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class="textdisabled" disabled>
        </td>
        <td>
        <?
            $retorno        = estoque_ic::consumo_mensal($campos[$i]['id_produto_insumo'], $campos[$i]['unidade_conversao']);//pego a qtde de cmmv do custo
            $mostrar_cmmv   = $retorno['mostrar_cmmv'];
            $cmmv           = $retorno['cmmv'];
            echo number_format($cmmv, 2, ',', '.');
        ?>
        </td>
        <td>
            <div id='div_estoque_estocagem<?=$i;?>'></div>
            <!--Aqui eu carrego a DIV "acima" com a página que precisa ser acessada via AJAX ...-->
            <Script Language = 'JavaScript'>
                ajax('estoque_estocagem.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&indice=<?=$i;?>', 'div_estoque_estocagem<?=$i;?>')
            </Script>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'cmm.php'" class='botao'>
            <input type='submit' name='cmd_atualizar' value='Atualizar' title='Atualizar' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if ($passo == 2) {
    for($i = 0; $i < count($_POST['chkt_produto_insumo']); $i++) {
        $sql = "UPDATE `produtos_insumos` SET `estoque_mensal` = '".$_POST['txt_cmm_auto'][$i]."' WHERE `id_produto_insumo` = '".$_POST['chkt_produto_insumo'][$i]."' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'cmm.php<?=$parametro?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consumo Mensal Médio ::.</title>
<meta http-equiv = 'Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content='no-store'>
<meta http-equiv = 'pragma' content='no-cache'>
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
        for(i = 0; i < 3;i ++)                 document.form.opt_opcao[i].disabled = false
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onSubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto Insumo - Consumo Mensal Médio
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Insumos por: Referência" id='label'>
            <label for='label'>Referência</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Insumos por: Referência" id='label2' checked>
            <label for='label2'>Discrimina&ccedil;&atilde;o</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="3" onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Insumos por: Observação" id='label3'>
            <label for='label3'>Observação</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' onclick='limpar()' value='4' title="Consultar todos os Produtos Insumos" class="checkbox" id='label4'>
            <label for='label4'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_visualizar_pis_mmv' value='1' title="Visualizar PI(s) com CMMV" class="checkbox" id='label5'>
            <label for='label5'>Visualizar PI(s) com CMMV</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color="blue">Lógica de CMM</font>

- Seleciono a qtde total do(s) item(ns) Baixado(s)/Manipulado(s) no último ano = $total_baixado_manipulado e $meses  = 12
- Se não tiver histórico
		retorno 0,00
_ Se não tiver Baixa/Manipulação superior a 365 dias
		$diff_dias  = diferença de dias entre a data atual e a data da primeira Baixa / Manipulação
		$meses = $diff_dias / 30
_ Retorno o $total_baixado_manipulado / $meses

<font color='red'><b>Observação:</b></font>

<b>* Só não traz P.I(s) do Tipo PRAC</b>
</pre>