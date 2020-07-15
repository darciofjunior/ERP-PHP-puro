<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/conversoes/consultar.php', '../../../../../');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM DE CONVERSÃO INCLUIDO COM SUCESSO.</font>";

if($passo == 1) {
//Verifico todos os produtos insumos que são do tipo aço
    $sql = "SELECT `id_produto_insumo` 
            FROM `produtos_insumos_vs_acos` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) $id_produtos_insumos = $id_produtos_insumos.$campos[$i]['id_produto_insumo'].',';
    $id_produtos_insumos = substr($id_produtos_insumos, 0, strlen($id_produtos_insumos) - 1);
        
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pi.`id_produto_insumo`, pi.`discriminacao`, g.`referencia` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE pi.`id_produto_insumo` IN ($id_produtos_insumos) 
                    AND g.`referencia` LIKE '$txt_consultar%' 
                    AND pi.`ativo` = '1' ORDER BY g.`referencia` ";
        break;
        case 2:
            $sql = "SELECT pi.`id_produto_insumo`, pi.`discriminacao`, g.`referencia` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE pi.`id_produto_insumo` IN ($id_produtos_insumos) 
                    AND pi.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
        default:
            $sql = "SELECT pi.`id_produto_insumo`, pi.`discriminacao`, g.`referencia` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE pi.`id_produto_insumo` IN ($id_produtos_insumos) 
                    AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas  == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir.php?id_conversoes_temps=<?=$id_conversoes_temps;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir Itens - Consultar Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox')  {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
    //Verifico a Qtde de Linhas da Tela ...
    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
            //Quantidade ...
            if(document.getElementById('txt_qtde_metros'+i).value == '') {
                alert('DIGITE A QUANTIDADE EM METROS !')
                document.getElementById('txt_qtde_metros'+i).focus()
                return  false
            }
            if(document.getElementById('txt_qtde_metros'+i).value == 0) {
                alert('QUANTIDADE EM METROS INVÁLIDA !')
                document.getElementById('txt_qtde_metros'+i).focus()
                document.getElementById('txt_qtde_metros'+i).select()
                return false
            }
        }
    }
    //Prepara a Tela p/ poder gravar no BD ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
            document.getElementById('txt_qtde_metros'+i).value = strtofloat(document.getElementById('txt_qtde_metros'+i).value)
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Produto(s) Insumo(s) p/ Incluir Item(ns)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde. Mts
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_insumo[]' id='chkt_produto_insumo<?=$i;?>' value='<?=$campos[$i]['id_produto_insumo'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <input type='text' name='txt_qtde_metros[]' id='txt_qtde_metros<?=$i;?>' title='Quantidade em Mts' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '3', '', event)" maxlength='8' size='8' class='textdisabled' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php?id_conversoes_temps=<?=$id_conversoes_temps;?>'" class='botao'>
            <input type='submit' name='cmd_incluir' value='Incluir' title='Incluir' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_conversoes_temps' value='<?=$id_conversoes_temps;?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    for($i = 0; $i < count($_POST['chkt_produto_insumo']); $i++) {
//Busca de alguns dados do PI para poder gravar no Banco
        $sql = "SELECT `id_geometria_aco`, `bitola1_aco`, `bitola2_aco` 
                FROM `produtos_insumos_vs_acos` 
                WHERE `id_produto_insumo` = '".$_POST['chkt_produto_insumo'][$i]."' LIMIT 1 ";
        $campos = bancos::sql($sql);
        
        $sql = "INSERT INTO `itens_conversoes_temps` (`id_item_conversoes_temps`, `id_conversoes_temps`, `id_produto_insumo`, `id_geometria_aco`, `medida1`, `medida2`, `qtde`, `data_sys`) VALUES (null, '$id_conversoes_temps', '".$_POST['chkt_produto_insumo'][$i]."', '".$campos[0]['id_geometria_aco']."', '".$campos[0]['bitola1_aco']."', '".$campos[0]['bitola2_aco']."', '".$_POST['txt_qtde_metros'][$i]."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?id_conversoes_temps=<?=$id_conversoes_temps;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Itens - Consultar Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.className   = 'textdisabled'
        document.form.txt_consultar.disabled    = true
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.disabled    = false
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
//Aqui é para não atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_conversoes_temps' value='<?=$id_conversoes_temps;?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Consultar Produto(s) Insumo(s) p/ Incluir Item(ns)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Produtos Insumos por: Referência' onclick='document.form.txt_consultar.focus()' id='label'>
            <label for='label'>Referência</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar Produtos Insumos por: Discriminação' onclick='document.form.txt_consultar.focus()' id='label2' checked>
            <label for='label2'>Discriminação</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os Produtos Insumos' onclick='limpar()' id='label3' class='checkbox'>
            <label for='label3'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>