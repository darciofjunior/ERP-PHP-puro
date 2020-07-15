<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

//Significa que veio do Menu Abertas / Liberadas
if($seguranca == 1) {
    $endereco = '/erp/albafer/modulo/faturamento/nota_saida/itens/abertas_liberadas.php';
//Significa que veio do Menu de Liberadas / Faturadas
}else if($seguranca == 2) {
    $endereco = '/erp/albafer/modulo/faturamento/nota_saida/itens/liberadas_faturadas.php';
}else if($seguranca == 3) {
    $endereco = '/erp/albafer/modulo/faturamento/nota_saida/itens/fat_empac_despachada.php';
}
segurancas::geral($endereco, '../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) CAIXA(S) COLETIVA(S) P/ NF.</font>";

/*Aqui traz todos os PIs Caixa(s) Coletiva(s) que podem estar sendo utilizados com 
a Caixa Coletiva para NFS - trago as medidas externas porque é exatamente o espaço 
 a ocupar no Container "Navio" ...*/
$sql = "SELECT id_produto_insumo, discriminacao, peso, altura_externo, largura_externo, comprimento_externo 
        FROM `produtos_insumos` 
        WHERE `ativo` = '1' 
        AND `caixa_coletiva_nfs` = '1' ORDER BY discriminacao ";
$campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Caixa(s) Coletiva(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<!--JS específico para esse arquivo-->
<Script Language = 'JavaScript' Src = 'dados_de_caixas.js'></Script>
<Script Language = 'JavaScript'>
function transportar() {
    var elementos           = document.form.elements
    var caixas_selecionadas = 0
    
    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }

    //Aqui eu verifico se tem alguma Caixa "PI" selecionada ...
    for(var i = 0; i < linhas; i++) {
        //Se tiver pelo menos 1 caixa selecionada, saio fora do Loop, 1 já satisfaz ...
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
            caixas_selecionadas++
            break
        }
    }
    
    if(caixas_selecionadas == 0) {
        alert('SELECIONE UMA CAIXA !')
        return false
    }

    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
            if(document.getElementById('txt_quantidade'+i).value == '') {
                alert('DIGITE A QUANTIDADE !')
                document.getElementById('txt_quantidade'+i).focus()
                return  false
            }

            if(document.getElementById('txt_quantidade'+i).value == 0) {
                alert('QUANTIDADE INVÁLIDA !')
                document.getElementById('txt_quantidade'+i).focus()
                document.getElementById('txt_quantidade'+i).select()
                return  false
            }
        }
    }
    
    var valor = false
    //Verifica se tem hum checkbox selecionado pelo menos ...
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo')  {
            if (elementos[i].checked == true) valor = true
        }
    }
    
    //Transportando dados p/ a Tela de Baixo que chamou esse Pop-Up ...
    if(typeof(opener.document.form.txt_qtde_caixas) == 'object') {
        opener.document.form.txt_qtde_caixas.value = document.form.txt_quantidade_total.value
    }

    if(typeof(opener.document.form.txt_peso_caixas) == 'object') {
        opener.document.form.txt_peso_caixas.value = document.form.txt_peso_total.value
    }
    
    if(typeof(opener.document.form.txt_volume_externo) == 'object') {
        opener.document.form.txt_volume_externo.value = document.form.txt_volume_total.value
    }
    
    var url = opener.document.location.href
    //Se o sistema chamou essa Tela do Cabeçalho do Pedido, executo esse cálculo abaixo ...
    if(url.indexOf('pedidos') != -1) {
        opener.document.form.txt_peso_bruto.value = eval(strtofloat(opener.document.form.txt_peso_neto.value)) + eval(strtofloat(document.form.txt_peso_total.value))
        opener.document.form.txt_peso_bruto.value = arred(opener.document.form.txt_peso_bruto.value, 4, 1)
    }
    window.close()//Fecha a Tela ...
}

function redefinir() {
    var resposta = confirm('DESEJA REDEFINIR ?')
    if(resposta == true) {
        window.location = 'atrelar_quantidade_volume.php?id_nf=<?=$id_nf;?>&seguranca=<?=$seguranca;?>'
    }else {
        return false
    }
}

function calcular() {
    var elementos           = document.form.elements
    var quantidade_total    = 0
    var peso_total          = 0
    var volume_total        = 0
    
    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
            quantidade_corrente = eval(document.getElementById('txt_quantidade'+i).value)
            //Tratamento p/ não dar erro de Cálculo ...
            if(isNaN(quantidade_corrente)) quantidade_corrente = 0
            //Cálculo de 
            document.getElementById('txt_peso_total'+i).value   = quantidade_corrente * document.getElementById('hdd_peso'+i).value
            document.getElementById('txt_peso_total'+i).value   = arred(document.getElementById('txt_peso_total'+i).value, 4, 1)
            //Cálculo de Volumes ...
            document.getElementById('txt_volume_total'+i).value = quantidade_corrente * document.getElementById('hdd_volume_externo'+i).value
            document.getElementById('txt_volume_total'+i).value = arred(document.getElementById('txt_volume_total'+i).value, 4, 1)
            //Totais ...
            quantidade_total+=      quantidade_corrente//Total da Coluna Qtde
            peso_total+=            eval(strtofloat(document.getElementById('txt_peso_total'+i).value))//Total da Coluna Qtde Total
            volume_total+=          eval(strtofloat(document.getElementById('txt_volume_total'+i).value))//Total da Coluna Qtde Total
        }
    }
    //Totais ...
    document.form.txt_quantidade_total.value    = quantidade_total
    document.form.txt_peso_total.value          = peso_total
    document.form.txt_peso_total.value          = arred(document.form.txt_peso_total.value, 4, 1)
    document.form.txt_volume_total.value        = volume_total
    document.form.txt_volume_total.value        = arred(document.form.txt_volume_total.value, 4, 1)
}
</Script>
</head>
<body onload='calcular()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
<?
    if($linhas == 0) {//Não existem PIs Caixa(s) Coletiva(s) ...
?>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
<?
        exit;
    }
?>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Consultar Caixa(s) Coletiva(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar_especial('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox">
        </td>
        <td>
            Qtde
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Peso
        </td>
        <td>
            Peso Total
        </td>
        <td>
            Volume
        </td>
        <td>
            Volume Total
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
/****************************************************************************************************/
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');calcular()" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_insumo[]' id='chkt_produto_insumo<?=$i;?>' value='<?=$campos[$i]['id_produto_insumo'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox' <?=$checked;?>>
        </td>
        <td>
            <input type='text' name='txt_quantidade[]' id='txt_quantidade<?=$i;?>' title='Digite a Quantidade' maxlength="8" size="8" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyUp="verifica(this, 'aceita', 'numeros', '', event);if(this.value == '0') {this.value = ''};calcular()" class='textdisabled' disabled>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['peso'], 4, ',', '.');?>
            <input type='hidden' name='hdd_peso[]' id='hdd_peso<?=$i;?>' value='<?=$campos[$i]['peso'];?>'>
        </td>
        <td>
            <input type='text' name='txt_peso_total[]' id='txt_peso_total<?=$i;?>' title='Peso Total' maxlength='10' size='11' class='textdisabled' disabled>
        </td>
        <td>
            <?
                $volume_externo = ($campos[$i]['altura_externo'] * $campos[$i]['largura_externo'] * $campos[$i]['comprimento_externo']) * pow(10, -9);
                echo number_format($volume_externo, 4, ',', '.');
            ?>
            <input type='hidden' name='hdd_volume_externo[]' id='hdd_volume_externo<?=$i;?>' value='<?=$volume_externo;?>'>
        </td>
        <td>
            <input type='text' name='txt_volume_total[]' id='txt_volume_total<?=$i;?>' title='Volume Total' maxlength='10' size='11' class='textdisabled' disabled>
        </td>
    </tr>
<?
    }
?>
    <tr align='center'>
        <td class='linhadestaque'>
            Totai(s) => 
        </td>
        <td class='linhadestaque'>
            <input type='text' name='txt_quantidade_total' maxlength='8' size='8' class='textdisabled' disabled>
        </td>
        <td class='linhadestaque' colspan='2'>
            &nbsp;
        </td>
        <td class='linhadestaque'>
            <input type='text' name='txt_peso_total' maxlength='10' size='11' class='textdisabled' disabled>
        </td>
        <td class='linhadestaque'>
            &nbsp;
        </td>
        <td class='linhadestaque'>
            <input type='text' name='txt_volume_total' maxlength='10' size='11' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick='redefinir()' class='botao'>
            <input type='button' name='cmd_transportar' value='Transportar' title='Transportar' onclick='transportar()' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>