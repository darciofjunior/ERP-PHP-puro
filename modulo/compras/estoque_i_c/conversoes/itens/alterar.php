<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/compras_new.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/conversoes/consultar.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>ITEM DE CONVERSÃO ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
    $sql = "UPDATE `itens_conversoes_temps` SET `medida1` = '$_POST[txt_medida1]', `medida2` = '$_POST[txt_medida2]', `qtde` = '$_POST[txt_qtde_metros]', `preco_kg` = '$_POST[txt_preco_kg]', `preco_m` = '$_POST[txt_preco_m]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_item_conversoes_temps` = '$id_item_conversoes_temps' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

if(empty($posicao)) $posicao = 1;

//Seleção da qtde de Item(ns) existente(s) na Conversão
$sql = "SELECT COUNT(`id_conversoes_temps`) AS qtde_itens 
        FROM `itens_conversoes_temps` 
        WHERE `id_conversoes_temps` = '$id_conversoes_temps' ";
$campos                     = bancos::sql($sql);
$qtde_itens                 = $campos[0]['qtde_itens'];

//Seleção de Dados do Item de Conversão Corrente
$sql = "SELECT * 
        FROM `itens_conversoes_temps` 
        WHERE `id_conversoes_temps` = '$id_conversoes_temps' ORDER BY `id_item_conversoes_temps` ";
$campos                     = bancos::sql($sql, ($posicao - 1), $posicao);
$id_item_conversoes_temps   = $campos[0]['id_item_conversoes_temps'];
$id_produto_insumo          = $campos[0]['id_produto_insumo'];

$densidade_kg_por_m         = compras_new::calcular_densidade('', $id_item_conversoes_temps);

$sql = "SELECT ga.`nome` AS geometria_aco, pi.`id_produto_insumo`, pi.`discriminacao`, qa.`nome` 
        FROM `produtos_insumos` pi 
        INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
        INNER JOIN `geometrias_acos` ga ON ga.`id_geometria_aco` = pia.`id_geometria_aco` 
        INNER JOIN `qualidades_acos` qa ON qa.`id_qualidade_aco` = pia.`id_qualidade_aco` 
        WHERE pi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
$campos_conversao   = bancos::sql($sql);
$discriminacao      = $campos_conversao[0]['discriminacao'];
$geometria_aco      = $campos_conversao[0]['geometria_aco'];
$tipo_aco           = $campos_conversao[0]['nome'];
?>
<html>
<title>.:: Alterar Itens de Conversão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar(posicao, verificar) {
/*Aqui significa que estou submetendo o formulário através do botão submit, sendo
faz requisição das condições de validação*/
    if(typeof(verificar) != 'undefined') {
//Medida 1
        if(!texto('form', 'txt_medida1', '1', '1234567890,', 'MEDIDA 1', '1')) {
            return false
        }
        var geometria_aco = '<?=$geometria_aco;?>'
//Medida 2
        if(geometria_aco == 'X' || geometria_aco == 'TB') {
            if(!texto('form', 'txt_medida2', '1', '1234567890,', 'MEDIDA 2', '1')) {
                return false
            }
        }
//Quantidade
        if(!texto('form', 'txt_qtde_metros', '1', '1234567890,.', 'QUANTIDADE EM METROS', '1')) {
            return false
        }
    }
    limpeza_moeda('form', 'txt_medida1, txt_medida2, txt_qtde_metros, txt_preco_kg, txt_preco_m, ')
//Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value = posicao
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
//Submetendo o Formulário
    document.form.submit()
}

function habilitar_medida2() {
    var geometria_aco = '<?=$geometria_aco;?>'
    if(geometria_aco == 'X' || geometria_aco == 'TB') {//Habilita p/ digitar 2ª Bitola ...
        document.form.txt_medida2.disabled  = false
//Layout de Habilitado ...
        document.form.txt_medida2.className = 'caixadetexto'
    }else {//Outras Geometrias desabilita a 2ª Bitola ...
        document.form.txt_medida2.disabled  = true
//Layout de Desabilitado ...
        document.form.txt_medida2.className = 'textdisabled'
        document.form.txt_medida2.value = ''
    }
}

function calcular_preco_m() {
    var preco_kg                    = eval(strtofloat(document.form.txt_preco_kg.value))
    var densidade_kg_por_m          = '<?=$densidade_kg_por_m;?>'
    
    document.form.txt_preco_m.value = preco_kg * densidade_kg_por_m
    document.form.txt_preco_m.value = arred(document.form.txt_preco_m.value, 2, 1)
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
<body onload='document.form.txt_qtde_metros.focus();habilitar_medida2()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar('<?=$posicao;?>', 1)">
<!--Aqui é para quando for submeter-->
<input type='hidden' name='id_conversoes_temps' value='<?=$id_conversoes_temps;?>'>
<input type='hidden' name='id_item_conversoes_temps' value='<?=$id_item_conversoes_temps;?>'>
<!--Controle de Tela-->
<input type='hidden' name='posicao' value='<?=$posicao;?>'>
<input type='hidden' name='nao_atualizar'>
<table width='80%' border='0' cellpadding='1' cellspacing ='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Itens da Conversão N.º 
            <font color='yellow'>
                <?=$id_conversoes_temps;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto:</b>
        </td>
        <td>
            <?=$discriminacao;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo do Aço:</b>
        </td>
        <td>
            <?=$tipo_aco;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Geometria do Aço:</b>
        </td>
        <td>
            <?=$geometria_aco;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Medida 1:</b>
        </td>
        <td>
            <input type='text' name='txt_medida1' value='<?=number_format($campos[0]['medida1'], 2, ',', '.');?>' title='Digite a Medida 1' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='15' size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Medida 2:
        </td>
        <td>
            <input type='text' name='txt_medida2' value='<?=number_format($campos[0]['medida2'], 2, ',', '.');?>' title='Digite a Medida 2' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='15' size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade em Mts:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_metros' value='<?=number_format($campos[0]['qtde'], 3, ',', '.');?>' title='Digite a Quantidade em Mts' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" maxlength='15' size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Dens Kg/M:
        </td>
        <td>
            <?=number_format($densidade_kg_por_m, 3, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço/Kg:</b>
        </td>
        <td>
            <input type='text' name='txt_preco_kg' value='<?=number_format($campos[0]['preco_kg'], 2, ',', '.');?>' title='Digite o Preço/Kg' maxlength='12' size='12' onkeyup="verifica(this, 'moeda_especial', '3', '', event);calcular_preco_m()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço/M:</b>
        </td>
        <td>
            <input type='text' name='txt_preco_m' value='<?=number_format($campos[0]['preco_m'], 2, ',', '.');?>' title='Preço/M' size='12' onfocus='document.form.txt_preco_kg.focus()' class='textdisabled'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick='document.form.txt_medida1.focus();habilitar_medida2()' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='return fechar(window)' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'>
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i % 40 == 0) echo '<br>';//Quebro a linha porque não estoura o limite da Tela ...

                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>