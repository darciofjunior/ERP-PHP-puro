<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PEDIDO EXCLUIDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ESTE PEDIDO NÃO PODE SER EXCLUIDO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pv.id_pedido_venda, pv.id_empresa, pv.data_emissao, c.nomefantasia, c.razaosocial, cc.nome, t.nome AS transportadora 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `transportadoras` t ON t.id_transportadora = pv.id_transportadora 
                    INNER JOIN `clientes_contatos` cc ON cc.id_cliente_contato = pv.id_cliente_contato 
                    INNER JOIN `clientes` c ON c.id_cliente = cc.id_cliente 
                    WHERE pv.`id_pedido_venda` LIKE '$txt_consultar%' 
                    ORDER BY pv.id_pedido_venda DESC ";
            
        break;
        case 2:
            $sql = "SELECT pv.id_pedido_venda, pv.id_empresa, pv.data_emissao, c.nomefantasia, c.razaosocial, cc.nome, t.nome AS transportadora 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `transportadoras` t ON t.id_transportadora = pv.id_transportadora 
                    INNER JOIN `clientes_contatos` cc ON cc.id_cliente_contato = pv.id_cliente_contato 
                    INNER JOIN `clientes` c ON c.id_cliente = cc.id_cliente AND c.`razaosocial` LIKE '%$txt_consultar%' 
                    ORDER BY pv.id_pedido_venda DESC ";
        break;
        case 3:          
            
            
            $sql = "SELECT pv.id_pedido_venda, pv.id_empresa, pv.data_emissao, c.nomefantasia, c.razaosocial, cc.nome, t.nome AS transportadora 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `transportadoras` t ON t.id_transportadora = pv.id_transportadora 
                    INNER JOIN `clientes_contatos` cc ON cc.id_cliente_contato = pv.id_cliente_contato 
                    INNER JOIN `clientes` c ON c.id_cliente = cc.id_cliente 
                    WHERE pv.`observacao` LIKE '%$txt_consultar%' 
                    ORDER BY pv.id_pedido_venda DESC ";
        break;
        default:
            $sql = "SELECT pv.id_pedido_venda, pv.id_empresa, pv.data_emissao, c.nomefantasia, c.razaosocial, cc.nome, t.nome AS transportadora 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `transportadoras` t ON t.id_transportadora = pv.id_transportadora 
                    INNER JOIN `clientes_contatos` cc ON cc.id_cliente_contato = pv.id_cliente_contato 
                    INNER JOIN `clientes` c ON c.id_cliente = cc.id_cliente 
                    ORDER BY pv.id_pedido_venda DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Pedido(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
</head>
<body>
<form name='form' method='post' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')" action='<?=$PHP_SELF.'?passo=2';?>'>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='7'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            Excluir Pedido(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            N.&ordm; Pedido
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Contato
        </td>
        <td>
            Transportadora
        </td>
        <td>
            Empresa
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox">
        </td>
    </tr>
<?
        for ($i=0;  $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['id_pedido_venda'];?>
        </td>
        <td>
                <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td align="left">
        <?
            if(!empty($campos[$i]['nomefantasia'])) {
                echo $campos[$i]['nomefantasia'];
            }else {
                echo $campos[$i]['razaosocial'];
            }
        ?>
        </td>
        <td align="left">
            <?=$campos[$i]['nome'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['transportadora'];?>
        </td>
        <td>
        <?
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
                echo $campos_empresa[0]['nomefantasia'].' (NF)';
            }else {
                echo $campos_empresa[0]['nomefantasia'].' (SGD)';
            }
        ?>
        </td>
        <td>
            <input type='checkbox' name='chkt_pedido_venda[]' value="<?=$campos[$i]['id_pedido_venda'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
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
}elseif($passo == 2) {
    foreach ($_POST['chkt_pedido_venda'] as $id_pedido_venda) {
        //Verifico se esse Pedido selecionado pelo usuário já consta em NF ...
        $sql = "SELECT pvi.id_pedido_venda_item 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda AND pv.`status` = '1' 
                WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' 
                AND pvi.`status` > '0' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {//Significa que esse Pedido pode ser excluído ...
            $sql = "DELETE FROM `pedidos_vendas_itens` WHERE `id_pedido_venda` = '$id_pedido_venda' ";
            bancos::sql($sql);
            $sql = "DELETE FROM `pedidos_vendas` WHERE `id_pedido_venda` = '$id_pedido_venda' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }else {//Não Pode ser excluído, porque já consta em Nota Fiscal ...
            $valor= 3;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Pedido(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
	if(document.form.opcao.checked == true) {
		for(i = 0; i < 3; i++) {
			document.form.opt_opcao[i].disabled = true
		}
		document.form.txt_consultar.disabled = true
		document.form.txt_consultar.value = ''
	}else {
		for(i = 0; i < 3; i++) {
			document.form.opt_opcao[i].disabled = false
		}
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
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Pedido(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" title="Consultar Pedido" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Pedido por: Número do Pedido" onclick="document.form.txt_consultar.focus()" id='label'>
            <label for="label">Número do Pedido</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar Pedido por: Cliente" onclick="document.form.txt_consultar.focus()" id='label2' checked>
            <label for="label2">Cliente</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="3" title="Consultar Pedido por: Observação" onclick="document.form.txt_consultar.focus()" id='label3'>
            <label for="label3">Observação</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os pedidos" onclick='limpar()' title='Selecionar Todos os Registros' class="checkbox" id='label4'>
            <label for="label4">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
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