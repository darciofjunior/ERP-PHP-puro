<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) DE ORÇAMENTO EXCLUÍDO(S) COM SUCESSO.</font>";

if(!empty($_POST['chkt_orcamento_venda_item'])) {
//Disparo do Loop
    foreach($_POST['chkt_orcamento_venda_item'] as $id_orcamento_venda_item) {
        //Exclui direto todas mensagens ESP se o id_orcamento_venda_item estiver na Tab. Relacional mensagens_esps
        $sql = "DELETE FROM `mensagens_esps` WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        bancos::sql($sql);
        //Verifico se o Item do Orçamento está vinculado a algum Item de OPC ...
        $sql = "SELECT `id_opc_item` 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        $campos_item_opc = bancos::sql($sql);
        if($campos_item_opc[0]['id_opc_item'] > 0) {//Significa que esse Orçamento foi gerado através de um item de OPC ...
            //Então eu reabro o Item de OPC para que este possa ser importado novamente p/ algum Futuro Orçamento ...
            $sql = "UPDATE `opcs_itens` SET `status` = '0' WHERE `id_opc_item` = '".$campos_item_opc[0]['id_opc_item']."' LIMIT 1 ";
            bancos::sql($sql);
            //Aqui eu busco o id_opc do Item ...
            $sql = "SELECT id_opc 
                    FROM `opcs_itens` 
                    WHERE `id_opc_item` = '".$campos_item_opc[0]['id_opc_item']."' LIMIT 1 ";
            $campos_opc = bancos::sql($sql);
            //Aqui eu faço uma marcação nessa OPC de que esta em Aberto p/ que essa possa ser importada novamente ...
            $sql = "UPDATE `opcs` SET `importado` = 'N' WHERE `id_opc` = '".$campos_opc[0]['id_opc']."' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Excluindo o Item de Orçamento ...
        $sql = "DELETE FROM `orcamentos_vendas_itens` WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}

//Procedimento normal de quando se carrega a Tela ...
$id_orcamento_venda = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_orcamento_venda'] : $_GET['id_orcamento_venda'];

//Aqui eu busco o id_pais através do id_orcamento p/ saber qual o Tipo de Moeda do Cliente ...
$sql = "SELECT c.id_pais 
        FROM `orcamentos_vendas` ov 
        INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
        WHERE ov.`id_orcamento_venda` = '$id_orcamento_venda' ";
$campos_pais    = bancos::sql($sql);
$id_pais        = $campos_pais[0]['id_pais'];

//Verifica se o Cliente é do Tipo Internacional ...
$tipo_moeda = ($id_pais != 31) ? 'U$' : 'R$';
//Seleciona todos os itens em "Aberto" do id_orcamento passado por parâmetro ...
$sql = "SELECT ovi.`id_orcamento_venda_item`, ovi.`qtde`, ovi.`queima_estoque`, ovi.`preco_liq_final`, 
        ovi.`prazo_entrega`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo` 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
        WHERE ovi.`id_orcamento_venda` = '$id_orcamento_venda' 
        AND ovi.`status` < '1' ORDER BY ovi.`id_orcamento_venda_item` ";
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO EXISTE(M) ITEM(NS) EM ABERTO PARA EXCLUIR NESTE ORÇAMENTO !!!')
        parent.ativar_loading()
        parent.html5Lightbox.finish()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Itens de Orçamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
//Confirmando ...
        var mensagem = confirm('DESEJA EXCLUIR O(S) ITEM(NS) SELECIONADO(S) ?')
        if(mensagem == true) {
            //Aqui é para não atualizar a Tela abaixo que chamou esse LightBox ...
            document.form.nao_atualizar.value = 1
            return true
        }else {
            return false
        }
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    //Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.ativar_loading()
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Excluir Item(ns) em Aberto do Orçamento - N.º&nbsp;
            <font color='yellow'>
                <?=$id_orcamento_venda;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            <font title='Quantidade' style='cursor:help'>
                Qtde
            </font>
        </td>
        <td>
            <font title='Prazo de Entrega do Or&ccedil;amento' style='cursor:help'>
                P.Ent.Orc
            </font>
        </td>
        <td>
            Produto
        </td>
        <td>
            <font title='Operação de Custo' style='cursor:help'>
                O.C.
            </font>
        </td>
        <td>
            <font title='Pre&ccedil;o Liq. Final <?=$tipo_moeda;?> / Pç' style='cursor:help'>
                Pre&ccedil;o Liq. <br/>Final <?=$tipo_moeda;?> / Pç
            </font>
        </td>
        <td>
            <font title='Total <?=$tipo_moeda;?> Lote s/ IPI:' style='cursor:help'>
                Total <?=$tipo_moeda;?> <br/>Lote s/ IPI
            </font>
        </td>
    </tr>
<?
        $vetor_prazos_entrega   = vendas::prazos_entrega();

	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_orcamento_venda_item[]' value="<?=$campos[$i]['id_orcamento_venda_item'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 1, ',', '.');?>
        </td>
        <td>
        <?
            //Verifico qual é o Prazo do Item do Orçamento p/ Printar na Tela de Itens ...
            foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
                if($campos[$i]['prazo_entrega'] == $indice) {//Se igual
                    $color = ($campos[$i]['prazo_entrega'] == 'I') ? 'darkblue' : 'red';

                    echo '<font color="'.$color.'"><b>'.ucfirst(strtolower($prazo_entrega)).'</b></font>';
                    break;//Para sair fora do Loop ...
                }
            }
        ?>
        </td>
        <td align='left'>
            <?
            if($campos[$i]['referencia'] != 'ESP') {
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);
            }else {
        ?>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
        <?
            }
            if($campos[$i]['queima_estoque'] == 'S') echo '&nbsp;<img src="../../../../imagem/queima_estoque.png" title="Excesso de Estoque" alt="Excesso de Estoque" border="0">';
        ?>
        </td>
        <td>
            <?if($campos[$i]['operacao_custo'] == 0) {echo 'I';}else {echo 'R';}?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['preco_liq_final'] * $campos[$i]['qtde'], 2, ',', '.');?>
        </td>
    </tr>
<?
            $total_itens+= $campos[$i]['preco_liq_final'] * $campos[$i]['qtde'];
	}
?>
    <tr align='right'>
        <td class='linhadestaque' colspan='6'>
            Total do(s) Item(ns) em <?=$tipo_moeda;?>: 
        </td>
        <td class='linhadestaque'>
            <font color='yellow' size='-1'>
                <?=number_format($total_itens, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
<!--************************Controles de Tela************************-->
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<input type='hidden' name='nao_atualizar'>
<!--*****************************************************************-->
</form>
</html>
<?}?>