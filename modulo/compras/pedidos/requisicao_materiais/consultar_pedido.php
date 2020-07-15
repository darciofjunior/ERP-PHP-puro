<?
require('../../../../lib/segurancas.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

if(!empty($_POST['chkt_item_pedido'])) {
//Armazeno aqui todos os Itens e Qtdes que foram selecionadas
    foreach($_POST['chkt_item_pedido'] as $i => $id_item_pedido) {
        $vetor_chkt_item_pedido.= $id_item_pedido.',';
        $vetor_qtde.= $_POST['txt_qtde'][$i].',';
    }
/*Aqui não tem jeito além do id_pedido que eu já levava, eu tenho que levar esses 2 parâmetros que são 
daqui de requisição: os Itens de Pedido que foram escolhidos e as Qtdes Digitadas, 
+ o parâmetro de criar 1 antecipação*/
    $vetor_chkt_item_pedido = substr($vetor_chkt_item_pedido, 0, strlen($vetor_chkt_item_pedido) - 1);
    $vetor_qtde             = substr($vetor_qtde, 0, strlen($vetor_qtde) - 1);
?>
    <Script Language = 'JavaScript'>
        window.location = 'index.php?id_pedido=<?=$_POST['id_pedido'];?>&chkt_item_pedido=<?=$vetor_chkt_item_pedido;?>&txt_qtde=<?=$vetor_qtde;?>&criar_antecipacao=<?=$criar_antecipacao;?>'
    </Script>
<?
}else {
//Utilizo este Tipo de Nota do Pedido, mais abaixo para cálculo
    $sql = "SELECT tipo_nota 
            FROM `pedidos` 
            WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $tipo_nota  = $campos[0]['tipo_nota'];
//Busca os Item(ns) do Pedido de Compra(s)
    $sql = "SELECT ip.*, g.referencia, pi.discriminacao 
            FROM `itens_pedidos` ip 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
            WHERE ip.`id_pedido` = '$_GET[id_pedido]' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Não existe 1 item sequer no Pedido, sendo assim não tem pelo que fazermos uma Requisição ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTE PEDIDO NÃO CONTÉM ITEM(NS) !')
        window.close()
    </Script>
<?
    }else {//Pedido tem pelo menos 1 item ...
?>
<head>
<title>.:: Requisição de Materiais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'Javascript'>
function calcular(indice) {
    var tipo_nota           = eval('<?=$tipo_nota;?>')
//Se a qtde estiver preenchida, então recálcula os preços
    if(document.getElementById('txt_qtde'+indice).value != '') {
//Declaração das demais variáveis para cálculo
        var quantidade      = eval(strtofloat(document.getElementById('txt_qtde'+indice).value))
        var preco_unitario  = eval(strtofloat(document.getElementById('txt_preco_unitario'+indice).value))
        var valor_total     = quantidade * preco_unitario
//Printagem do Valor Total
        document.getElementById('txt_valor_total'+indice).value = valor_total
        document.getElementById('txt_valor_total'+indice).value = arred(document.getElementById('txt_valor_total'+indice).value, 2, 1)
//Cálculo do IPI
        if(tipo_nota == 2) {//Se o Tipo de Pedido for SGD, não existe IPI
            var ipi = 0
        }else {//Se for NF, então existe IPI
            var ipi = eval(strtofloat(document.getElementById('txt_ipi'+indice).value))
        }
        var valor_com_ipi = (valor_total * ipi) / 100
//Printagem do IPI
        document.getElementById('txt_valor_com_ipi'+indice).value = valor_com_ipi
        document.getElementById('txt_valor_com_ipi'+indice).value = arred(document.getElementById('txt_valor_com_ipi'+indice).value, 2, 1)
//Qtde está vazia, então limpa os preços
    }else {
        document.getElementById('txt_valor_total'+indice).value     = ''
        document.getElementById('txt_valor_com_ipi'+indice).value   = ''
    }
}

function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
    var elementos = document.form.elements
    if(typeof(elementos['chkt_item_pedido[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_item_pedido[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        var valor           = eval(strtofloat(document.getElementById('txt_qtde'+i).value))
        var valor_original  = eval(strtofloat(document.getElementById('txt_qtde_real'+i).value))
        if(valor > valor_original) {
            alert('QUANTIDADE INVÁLIDA !')
            document.getElementById('txt_qtde'+i).value        = document.getElementById('txt_qtde_real'+i).value
            document.getElementById('txt_valor_total'+i).value = document.getElementById('txt_valor_total_real'+i).value
            document.getElementById('txt_qtde'+i).select()
            return false
        }
    }
    var pergunta = confirm('DESEJA CRIAR A VIA FINANCEIRO (ANTECIPAÇÃO) ?')
    if(pergunta == true) document.form.criar_antecipacao.value = 1

    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_item_pedido'+i).checked == true) {
            document.getElementById('txt_valor_total'+i).disabled   = false
            document.getElementById('txt_qtde'+i).value             = eval(strtofloat(document.getElementById('txt_qtde'+i).value))
            document.getElementById('txt_valor_total'+i).value      = eval(strtofloat(document.getElementById('txt_valor_total'+i).value))
        }
    }
    return true
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Requisição de Materiais - Pedido N.º 
            <font color='yellow'>
                <?=$_GET['id_pedido'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar todos' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <font title='Quantidade Restante' style='cursor:help'>
                Qtde Rest
            </font>
        </td>
        <td>
            Discrimina&ccedil;&atilde;o
        </td>
        <td>
            <font title='Preço Unitário' style='cursor:help'>
                Pre&ccedil;o Unit
            </font>
        </td>
        <td>
            Valor Total
        </td>
        <td>
            IPI
        </td>
        <td>
            Valor c/ IPI
        </td>
        <td>
            Marca / Obs
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_item_pedido[]' id='chkt_item_pedido<?=$i;?>' value='<?=$campos[$i]['id_item_pedido'];?>' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$i;?>' value='<?=str_replace('.', ',', $campos[$i]['qtde']);?>' size='10' onkeyup="verifica(this,'moeda_especial', '2', '', event);calcular('<?echo $i;?>')" onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled>
            <input type='hidden' name='txt_qtde_real[]' id='txt_qtde_real<?=$i;?>' value="<?=str_replace('.', ',', $campos[$i]['qtde']);?>">
        </td>
        <td align='left'>
        <?
            $referencia = genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia'], 0);
            echo $referencia.' * '.$campos[$i]['discriminacao'];
        ?>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario[]' id='txt_preco_unitario<?=$i;?>' value='<?=number_format($campos[$i]['preco_unitario'], 2, ',', '.');?>' size='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_valor_total[]' id='txt_valor_total<?=$i;?>' value='<?=number_format($campos[$i]['valor_total'], 2, ',', '.');?>' size='10' class='textdisabled' disabled>
            <input type='hidden' name='txt_valor_total_real[]' id='txt_valor_total_real<?=$i;?>' value='<?=number_format($campos[$i]['valor_total'], 2, ',', '.');?>' disabled>
        </td>
        <td>
            <input type='text' name='txt_ipi[]' id='txt_ipi<?=$i;?>' value='<?=number_format($campos[$i]['ipi'], 2, ',', '.');?>' size='7' class='textdisabled' disabled>
        </td>
        <td>
            <?
                if($tipo_nota == 2) {//Se o Tipo de Pedido for SGD, não existe IPI
                    $ipi = 0;
                }else {//Se for NF, então existe IPI
                    $ipi = $campos[$i]['ipi'];
                }
                $valor_com_ipi = ($campos[$i]['valor_total'] * $ipi) / 100;
            ?>
            <input type='text' name='txt_valor_com_ipi[]' id='txt_valor_com_ipi<?=$i;?>' value='<?=number_format($valor_com_ipi, 2, ',', '.');?>' size='7' class='textdisabled' disabled>
        </td>
        <td>
        <?
            if($campos[$i]['marca'] == '') {
                echo '&nbsp;';
            }else {
                echo $campos[$i]['marca'];
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../itens/outras_opcoes.php?id_pedido=<?=$_GET['id_pedido'];?>'" class='botao'>
            <input type='submit' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_pedido' value="<?=$_GET['id_pedido'];?>">
<!--Esse parâmetro significa que daqui da requisição eu desejo criar uma antecipação-->
<input type='hidden' name='criar_antecipacao'>
</form>
</body>
</html>
<?
    }
}
?>