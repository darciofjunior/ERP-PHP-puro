<?
require('../../../../lib/segurancas.php');
require('../../../../lib/biblioteca.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/variaveis/intermodular.php');

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolução 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolução ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

if($passo == 1) {
    $data_sys               = date('Y-m-d H:i:s');
    $itens_nao_excluidos    = 0;//Variável que vai auxiliar na hora de excluir de Itens ...
    
    if($opcao == 4) {//Significa que veio do Menu de Devolução ...
        foreach($_POST['chkt_nfs_item'] as $id_nfs_item) {//Disparo do Loop
/*Aqui eu busco a qtde da Nota de Devolução, o id_pedido_venda_item, P.A. e o id_Item da NF Principal através 
do id_nfs_item_devolvida ...*/
            $sql = "SELECT nfsi.`id_nf_item_devolvida`, nfsi.`id_pedido_venda_item`, 
                    nfsi.`qtde_devolvida`, pvi.`id_produto_acabado` 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
                    WHERE nfsi.`id_nfs_item` = '$id_nfs_item' LIMIT 1 ";
            $campos                 = bancos::sql($sql);
            $id_nfs_item_principal  = $campos[0]['id_nf_item_devolvida'];
            $id_pedido_venda_item   = $campos[0]['id_pedido_venda_item'];
            $qtde_devolvida         = $campos[0]['qtde_devolvida'];
            $id_produto_acabado     = $campos[0]['id_produto_acabado'];
/*Atualizo na Tabela "pedidos_vendas_itens" o campo Quantidade "Qtde Devolvida", esse campo foi criado 
na intenção de corrigir e agilizar alguns "Relatórios de Pedidos de Vendas" ...*/
            $sql = "UPDATE `pedidos_vendas_itens` SET `qtde_devolvida` = `qtde_devolvida` - '$qtde_devolvida' WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
            bancos::sql($sql);
//Atualizo o status do Item da NF Principal p/ 0, para que este possa ser importado novamente ...
            $sql = "UPDATE `nfs_itens` SET `status` = '0' WHERE `id_nfs_item` = '$id_nfs_item_principal' LIMIT 1 ";
            bancos::sql($sql);
//Excluindo o Item da NF Secundária ...
            $sql = "DELETE FROM `nfs_itens` WHERE `id_nfs_item` = '$id_nfs_item' LIMIT 1 ";
            bancos::sql($sql);
            faturamentos::controle_estoque($id_nf, $id_pedido_venda_item, $qtde_devolvida, 0, 0, 4);
        }
//Registrando o Funcionário que fez modificações na NF de Devolução ...
        $sql = "UPDATE `nfs` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '$data_sys' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);

        if($itens_nao_excluidos == 0) {//Excluiu todos os itens da NF normalmente
            $mensagem = 'TODO(S) O(S) ITEM(NS) DA NOTA FISCAL FOI(RAM) EXCLUÍDO(S) COM SUCESSO !';
        }else {
            if($itens_nao_excluidos < count($_POST['chkt_nfs_item'])) {//Excluiu somente alguns Itens da NF ...
                $mensagem = 'ALGUM(NS) ITEM(NS) DE NOTA FISCAL NÃO PODE(M) SER EXCLUÍDO(S) !!!\nQUANTIDADE INSUFICIENTE DO ESTOQUE DISPONÍVEL PARA EXCLUSÃO DESSE(S) ITEM(NS) !';
            }else {//Não foi possível excluir nenhum Item da NF
                $mensagem = 'NENHUM ITEM DE NOTA FISCAL PODE SER EXCLUÍDO !!!\nQUANTIDADE INSUFICIENTE DO ESTOQUE DISPONÍVEL PARA EXCLUSÃO DO(S) ITEM(NS) !';
            }
        }
    }else {//Significa que veio do Menu de Nota Fiscal de Saída ...
        foreach($_POST['chkt_nfs_item'] as $id_nfs_item) {//Disparo do Loop
            $sql = "SELECT pvi.id_pedido_venda_item, nfsi.qtde AS qtde_nfsi, nfsi.vale AS vale_nfsi 
                    FROM `nfs_itens` nfsi
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
                    WHERE nfsi.`id_nfs_item` = '$id_nfs_item' LIMIT 1 ";
            $campos                     = bancos::sql($sql);
            $id_pedido_venda_item	= $campos[0]['id_pedido_venda_item'];
            $qtde_faturar		= $campos[0]['qtde_nfsi'];
            $vale_nfsi                  = $campos[0]['vale_nfsi'];
            $diferenca                  = $qtde_faturar - $vale_nfsi;
            $sql = "DELETE FROM `nfs_itens` WHERE `id_nfs_item` = '$id_nfs_item' LIMIT 1 ";
            bancos::sql($sql);
            $sql = "UPDATE `pedidos_vendas_itens` SET vale=vale+$vale_nfsi where id_pedido_venda_item = '$id_pedido_venda_item' LIMIT 1 ";
            bancos::sql($sql);
            faturamentos::controle_estoque('', $id_pedido_venda_item, $diferenca, 0, 0, 0);
        }
        /**************************************Controle com o Texto da Nota**************************************/
        /*Se houve alguma inclusão de Item de Nota Fiscal então reseto o texto da Nota Fiscal, porque tem textos que são montados 
        em cima destes itens ...*/
        $sql = "UPDATE `nfs` SET `texto_nf` = '' WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
        bancos::sql($sql);
        /********************************************************************************************************/
        $mensagem = 'TODO(S) O(S) ITEM(NS) DA NOTA FISCAL FORAM EXCLUÍDO(S) COM SUCESSO !';
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem;?>')
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
        window.close()
    </Script>
<?
}else {
    //Logo de cara já verifico se está Nota já possui comissão paga e se pode estar sendo excluída ...
    $pago_comissao_pode_excluir = faturamentos::pago_comissao_pode_excluir($_GET['id_nf']);
    if($pago_comissao_pode_excluir == 0) {//Não pode excluir a comissão
        echo '<font color="red"><div align="center"><b>NENHUM ITEM DE NOTA FISCAL PODE SER EXCLUÍDO DEVIDO TER SIDO PAGO A COMISSÃO DA MESMA !</b></div></font>';
        exit;
    }
    
    //Verifico se está Nota já foi importada p/ o Financeiro ...
    $importado_financeiro = faturamentos::importado_financeiro($_GET['id_nf']);
    if($importado_financeiro == 'S') {//Significa que a NF já está importada no Financeiro ...
        echo '<font color="red"><div align="center"><b>ESTÁ NF NÃO PODE SER + ALTERADA DEVIDO ESTAR IMPORTADA NO FINANCEIRO !</b></div></font>';
        exit;
    }

    //Significa que veio do Menu de "Em Aberto / Liberadas", "Fat. / Emp. / Despachadas", "Liberadas / Fat. / Canc." ...
    if($opcao < 4) {
        if(faturamentos::situacao_nota_fiscal($_GET['id_nf']) >= 1) {//O status da Nota Fiscal está Igual ou Superior a Faturada, então não pode ser excluído nenhum Item ...
            echo '<font color="red"><div align="center"><b>NENHUM ITEM DE NOTA FISCAL PODE SER EXCLUÍDO !\nPORQUE ESTA NOTA FISCAL ESTÁ TRAVADA !</b></div></font>';
            exit;
        }
    }

    //Aqui eu busco o id_pais através do "id_nf" p/ saber qual o Tipo de Moeda do Cliente ...
    $sql = "SELECT c.id_pais 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE nfs.`id_nf` = '$_GET[id_nf]' ";
    $campos_pais    = bancos::sql($sql);
    $id_pais        = $campos_pais[0]['id_pais'];
	
//Verifica se o Cliente é do Tipo Internacional ...
    $tipo_moeda = ($id_pais != 31) ? 'U$' : 'R$';
//Seleciona todos os itens do "id_nf" passado por parâmetro ...
    $sql = "SELECT nfsi.`id_nfs_item`, IF(nfs.`status` = '6', nfsi.`qtde_devolvida`, nfsi.`qtde`) AS qtde_utilizar, nfsi.valor_unitario, pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo 
            FROM `nfs_itens` nfsi 
            INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
            WHERE nfsi.`id_nf` = '$_GET[id_nf]' ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
<html>
<html>
<title>.:: Excluir Itens de Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
</table>
</body>
</html>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: Excluir Itens de Nota Fiscal ::.</title>
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
        if(!mensagem == true) return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Itens de Nota Fiscal - N.º&nbsp;
            <font color='yellow'>
                <?=faturamentos::buscar_numero_nf($_GET['id_nf'], 'S');?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td><font title="Quantidade" style="cursor:help">Qtde</font></td>
        <td>Produto</td>
        <td><font title="Operação de Custo" style='cursor:help'>O.C.</font></td>
        <td><font title="Pre&ccedil;o Liq. Final <?=$tipo_moeda;?> / Pç" style="cursor:help">Pre&ccedil;o Liq. <br>Final <?=$tipo_moeda;?> / Pç</font></td>
        <td><font title="Total <?=$tipo_moeda;?> Lote s/ IPI:" style="cursor:help">Total <?=$tipo_moeda;?> <br>Lote s/ IPI</font></td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_nfs_item[]' value="<?=$campos[$i]['id_nfs_item'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_utilizar'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?
            if($campos[$i]['referencia'] != 'ESP') {
                echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'],0);
            }else {
        ?>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'],0);?>
        <?
            }
        ?>
        </td>
        <td>
            <?if($campos[$i]['operacao_custo'] == 0) {echo 'I';}else {echo 'R';}?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_unitario'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_unitario'] * $campos[$i]['qtde'], 2, ',', '.');?>
        </td>
    </tr>
<?
            $total_itens+= $campos[$i]['valor_unitario'] * $campos[$i]['qtde'];
	}
?>
    <tr align='right'>
        <td class='linhadestaque' colspan='5'>
            Total do(s) Item(ns) em <?=$tipo_moeda;?>: 
        </td>
        <td class='linhadestaque'>
            <font color='yellow' size='-1'>
                <?=number_format($total_itens, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf'];?>'>
<input type='hidden' name='opcao' value='<?=$_GET['opcao'];?>'>
</form>
</html>
<?}?>