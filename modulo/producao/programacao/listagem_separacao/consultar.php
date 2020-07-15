<?
require('../../../../lib/data.php');
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM(NS) MARCADO(S) COMO LIDO(S).</font>";

if($passo == 1) {
    if(!empty($chkt_listas_nao_impressas)) $condicao = "WHERE pvs.`status` = '0' ";//Não Impresso
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT DISTINCT(pvs.id_pedido_venda_separacao), pvs.id_pedido_venda, pvs.qtde_vale, pvs.data_sys, pvs.qtde_separado, pvs.data_sys, pa.id_produto_acabado, pa.referencia, pa.discriminacao 
                    FROM `pedidos_vendas_separacoes` pvs 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvs.id_produto_acabado AND pa.`ativo` = '1' AND pa.`referencia` LIKE '%$txt_consultar%' 
                    $condicao ORDER BY pa.referencia, pa.discriminacao, pvs.data_sys ";
        break;
        case 2:
            $sql = "SELECT DISTINCT(pvs.id_pedido_venda_separacao), pvs.id_pedido_venda, pvs.qtde_vale, pvs.data_sys, pvs.qtde_separado, pvs.data_sys, pa.id_produto_acabado, pa.referencia, pa.discriminacao 
                    FROM `pedidos_vendas_separacoes` pvs 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvs.id_produto_acabado AND pa.`ativo` = '1' AND pa.`discriminacao` LIKE '%$txt_consultar%' 
                    $condicao ORDER BY pa.referencia, pa.discriminacao, pvs.data_sys ";
        break;
        default:
            $sql = "SELECT DISTINCT(pvs.id_pedido_venda_separacao), pvs.id_pedido_venda, pvs.qtde_vale, pvs.data_sys, pvs.qtde_separado, pvs.data_sys, pa.id_produto_acabado, pa.referencia, pa.discriminacao 
                    FROM `pedidos_vendas_separacoes` pvs 
                    INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvs.id_produto_acabado AND pa.`ativo` = '1' 
                    $condicao ORDER BY pa.referencia, pa.discriminacao, pvs.data_sys ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Listagem de Separação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var x, mensagem = '', valor = false, elementos = document.form.elements
    for (x = 0; x < elementos.length; x ++)   {
        if (elementos[x].type == 'checkbox')  {
            if (elementos[x].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        document.form.action = 'listagem_separacao.php'
        document.form.target = 'POPUP'
        nova_janela('listagem_separacao.php', 'POPUP', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

//Essa função serve para quando fecharmos o POPUP da Listagem de Separação
function submeter() {
    document.form.passo.value = 2
    document.form.action = 'consultar.php'
    document.form.method = 'POST'
    document.form.target = '_self'
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' onsubmit="return validar()">
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='7'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
                <b><?=$mensagem[$valor];?></b>
            </font>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            Listagem de Separação
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            Qtde Sep
        </td>
        <td>
            Qtde Vale
        </td>
        <td>
            Produto
        </td>
        <td>
            Data
        </td>
        <td>
            N.&ordm; Ped
        </td>
        <td>
            Cliente
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_pedido_venda_separacao[]' value="<?=$campos[$i]['id_pedido_venda_separacao'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['qtde_separado'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['qtde_vale'], 2, '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'].' * '.$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=substr(data::datetodata($campos[$i]['data_sys'], '/'), 0, 10).' '.substr($campos[$i]['data_sys'], 11, 8);?>
        </td>
        <td>
            <?=$campos[$i]['id_pedido_venda'];?>
        </td>
        <td align='left'>
        <?
//Busca da razão social
            $sql = "SELECT nomefantasia, razaosocial 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `clientes` c ON c.id_cliente = pv.id_cliente 
                    WHERE pv.`id_pedido_venda` = '".$campos[$i]['id_pedido_venda']."' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            if(!empty($campos_cliente[0]['nomefantasia'])) {
                echo $campos_cliente[0]['nomefantasia'];
            }else {
                echo $campos_cliente[0]['razaosocial'];
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class="botao">
            <input type="submit" name="cmd_imprimir" value="Imprimir" title="Imprimir" class="botao">
        </td>
    </tr>
</table>
<input type='hidden' name='id_pedido_venda_separacao'>
<input type='hidden' name='passo' onclick="submeter()">
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    $id_pedido_venda_separacao = explode(',', $_POST['id_pedido_venda_separacao']);
//Disparo do Vetor
    foreach($id_pedido_venda_separacao as $id_pedido_venda_separacao_loop) {
//Aki ele marca o item como lido
        $sql = "UPDATE `pedidos_vendas_separacoes` SET `status` = '1' WHERE `id_pedido_venda_separacao` = '$id_pedido_venda_separacao_loop' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'consultar.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Listagem de Separação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function iniciar() {
    if(document.form.txt_consultar.disabled == false) document.form.txt_consultar.focus()
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
<body onLoad="iniciar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Listagem de Separação
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" title="Consultar Pedido" size='45' maxlength='45' class="caixadetexto" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Pedido por: Referência" onclick="iniciar()" id='label' checked disabled>
            <label for="label">Referência</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar Pedido por: Discriminação" onclick="iniciar()" id='label2' disabled>
            <label for="label2">Discriminação</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type='checkbox' name='chkt_listas_nao_impressas' value='1' title="Consultar Lista(s) não Impressa(s)" class="checkbox" id='label3' checked>
            <label for="label3">Lista(s) não Impressa(s)</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='1' title="Consultar Todas as Listagem(ns)" onclick='limpar()' id='label4' class="checkbox" checked>
            <label for="label4">Todas as Listagem(ns)</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = true;limpar()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>