<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');
require('../../../../lib/data.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='erro'>PEDIDO LIBERADO !!! NÃO É POSSÍVEL EXCLUIR O(S) ITEM(NS) !</font>";
$mensagem[2] = "<font class='atencao'>NÃO EXISTE(M) ITEM(NS) EM ABERTO PARA EXCLUIR NESTE PEDIDO.</font>";

if($passo == 1) {
    //Variáveis para controle de retorno de Mensagens
    $cont_excluidos     = 0;
    $cont_nao_excluidos = 0;
    $cont_bloqueados    = 0;

    //Disparo do Loop
    foreach ($_POST['chkt_pedido_venda_item'] as $id_pedido_venda_item) {
        //Aqui eu busco o id_produto_acabado do "$id_pedido_venda_item" do Loop selecionado pelo usuário ...
        $sql = "SELECT `id_produto_acabado` 
                FROM `pedidos_vendas_itens` 
                WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $vetor          = estoque_acabado::qtde_estoque($campos[0]['id_produto_acabado']);
        $status_estoque = $vetor[1];

        //$status_estoque => para saber se o estoquista esta manpulando o  produto 0-free  1-locked 2-racionado
        //$status_estoque_item => é para saber se o item poder ser manipulado ou liberado para manipular 0-free 1-lock
        if($status_estoque == 0) {
            //Aqui eu recoloco a Qtde do Produto no Estoque e a própria função deleta o item
            if(!estoque_acabado::controle_pedidos_vendas_itens($id_pedido_venda_item, 0)) {
                $cont_nao_excluidos++;
            }else {
                $cont_excluidos++;
            }
        }else if($status_estoque == 1) {
            $cont_bloqueados++;
        }else {
//Aqui eu recoloco a Qtde do Produto no Estoque e a propria funcao deleta o item
            estoque_acabado::controle_pedidos_vendas_itens($id_pedido_venda_item, 0);
            $cont_excluidos++;
        }
    }
//Verificação para os Retornos de Mensagem:
//1) Todos os Itens foram excluídos do Pedido com sucesso
    if($cont_excluidos != 0 && ($cont_nao_excluidos == 0 && $cont_bloqueados == 0)) {
        //Verifico se ainda existe algum item no Pedido de Vedida atual ...
        $sql = "SELECT `id_pedido_venda_item` 
                FROM `pedidos_vendas_itens` 
                WHERE `id_pedido_venda` = '$_POST[id_pedido_venda]' LIMIT 1 ";
        $campos_pedido_venda_item = bancos::sql($sql);
        if(count($campos_pedido_venda_item) == 0) {
            //Verifico se existe pelo menos 1 Follow-Up para o Pedido de Vedida da qual estou tentando excluir os Itens ...
            $sql = "SELECT `id_follow_up` 
                    FROM `follow_ups` 
                    WHERE `identificacao` = '$_POST[id_pedido_venda]' 
                    AND `origem` = '2' LIMIT 1 ";
            $campos_follow_up = bancos::sql($sql);
            if(count($campos_follow_up) == 1) {//Sim existe ...
                //Conseqüentemente apago todos os Follow-UP(s) do $id_pedido_venda atual ...
                $sql = "DELETE FROM `follow_ups` WHERE `identificacao` = '$_POST[id_pedido_venda]' AND `origem` = '2' ";
                bancos::sql($sql);
                $mensagem = 'TODO(S) O(S) ITEM(NS) DE PEDIDO FORAM EXCLUÍDO(S) COM SUCESSO !!!\n\nO(S) FOLLOW-UP(S) EXISTENTE(S) TAMBÉM FOI(RAM) EXCLUÍDO(S) !';
            }else {//Não existe nenhum Follow-UP ...
                $mensagem = 'TODO(S) O(S) ITEM(NS) DE PEDIDO FORAM EXCLUÍDO(S) COM SUCESSO !';
            }
        }else {
            $mensagem = 'TODO(S) O(S) ITEM(NS) DE PEDIDO FORAM EXCLUÍDO(S) COM SUCESSO !';
        }
    }
//2) Nenhum Item pode ser Excluído, tem vale, ou item(ns) pode estar na NF, ou tem Produtos q estão bloqueados pelo Estoquista ..., sei lá 
    if($cont_excluidos == 0 && ($cont_nao_excluidos != 0 || $cont_bloqueados != 0)) {
        $mensagem = 'NENHUM ITEM DE PEDIDO PODE SER EXCLUÍDO !\n\nOBS: ALGUM(NS) ITEM(NS) PODEM TER VALE, OUTROS PODEM TER SIDO IMPORTADO(S) PARA NOTA FISCAL, OUTROS PODEM ESTAR BLOQUEADO(S) PELO ESTOQUISTA !';
    }
//3) Alguns Itens foram excluídos com sucesso e outros não podem ser excluídos
    if($cont_excluidos != 0 && ($cont_nao_excluidos != 0 || $cont_bloqueados != 0)) {
        $mensagem = 'ALGUM(NS) ITEM(NS) DE PEDIDO FORAM EXCLUÍDO(S) COM SUCESSO !\n\nOUTROS NÃO PODEM SER EXCLUÍDO(S), PORQUE ALGUM(NS) ITEM(NS) PODEM TER VALE, OUTROS PODEM TER SIDO IMPORTADO(S) PARA NOTA FISCAL, OUTROS PODEM ESTAR BLOQUEADO(S) PELO ESTOQUISTA !';
    }
//4) Aki é para o caso de não acontecer nada, nem excluir, nem barrar na hora de excluir,
//caso isso venha acontecer, é porque certamente este(s) item(ns) já foram importado(s) para NF
    if($cont_excluidos == 0 && $cont_nao_excluidos == 0 && $cont_bloqueados == 0) {
        $mensagem = 'ITEM(NS) DE PEDIDO NÃO PODE(M) SER EXCLUÍDO(S), DEVIDO TER SIDO IMPORTADO(S) PARA A NOTA FISCAL !';
    }
//5) P/ Itens ESP, caso não foi possível a exclusão ...
    if($cont_nao_excluidos > 0) {
        $mensagem = '\n\nEXISTE(M) ITEM(NS) ESP(S) DESSE PEDIDO QUE FOI(RAM) IMPORTADO(S) EM OUTRO(S) PEDIDO(S) E EXISTE ALGUM PEDIDO QUE FOI LIBERADO, POR ISSO QUE NÃO PODE SER EXCLUÍDO !\n\n';
    }
?>
<Script Language = 'JavaScript'>
    alert('<?=$mensagem;?>')
    //Significa que essa Tela foi aberta do próprio local de origem - menu "Pedido de Vendas" ...
    if(typeof(opener.document.form) == 'object') {
        opener.parent.itens.document.form.submit()
        opener.parent.rodape.document.form.submit()
    }else {//Significa que essa Tela foi acessada de algum local ...
        opener.document.location = opener.document.location.href
    }
    window.close()
</Script>
<?
}else {
    $vetor_logins_com_acesso_margens_lucro = vendas::logins_com_acesso_margens_lucro();
?>
<html>
<html>
<title>.:: Excluir Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
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
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    //Aqui eu busco alguns campos do "id_pedido_venda" passado por parâmetro ...
    $sql = "SELECT c.`id_pais`, pv.`liberado` 
            FROM `pedidos_vendas` pv 
            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
            WHERE pv.`id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
    $campos_pedido_venda = bancos::sql($sql);
    if($campos_pedido_venda[0]['liberado'] == 1 && !in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {//Se o Pedido está Liberado e o Usuário logado não é um dos 3 acima que tem permissão geral, travo a tela ...
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
    }else {
        //Verifica se o Cliente é do Tipo Internacional ...
        $tipo_moeda = ($campos_pedido_venda[0]['id_pais'] != 31) ? 'U$' : 'R$';
        //Seleciona todos os itens em "Aberto" do id_pedido_venda passado por parâmetro ...
        $sql = "SELECT c.`id_uf`, ovi.`id_orcamento_venda`, ovi.`id_orcamento_venda_item`, ovi.`queima_estoque`, 
                pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`operacao_custo`, 
                pvi.`id_pedido_venda_item`, pvi.`qtde`, pvi.`qtde_pendente`, pvi.`vale`, pvi.`qtde_faturada`, 
                pvi.`preco_liq_final`, pvi.`margem_lucro`, 
                pvi.`margem_lucro_estimada` 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                WHERE pvi.`id_pedido_venda` = '$_GET[id_pedido_venda]' 
                AND pvi.`status` = '0' ORDER BY pvi.id_pedido_venda_item ";
        $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
        $linhas = count($campos);
        if($linhas == 0) {//Não existem itens no Pedido à serem Excluídos ...
?>
    <tr align='center'>
        <td>
            <?=$mensagem[2];?>
        </td>
    </tr>
<?
        }else {//Existe pelo menos 1 item ...
            //Variáveis que serão utilizadas mais abaixo, no decorrer do Script ...
            $tx_financeira = custos::calculo_taxa_financeira($campos[0]['id_orcamento_venda']);
?>
    <tr align='center'>
        <td colspan='6'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Item(ns) em Aberto do Pedido - N.º&nbsp;
            <font color='yellow'>
                <?=$_GET['id_pedido_venda'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar_excluir('form', '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            <font title='Quantidade' style='cursor:help'>
                Qtde
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
                Total <?=$tipo_moeda;?>
                <br/>Lote s/ IPI
            </font>
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
                $qtde_separada      = $campos[$i]['qtde'] - $campos[$i]['qtde_pendente'] - $campos[$i]['vale'] - $campos[$i]['qtde_faturada'];
                $disabled_checked   = ($qtde_separada > 0) ? 'disabled' : '';
?>
    <tr class='linhanormal' onclick="checkbox_excluir('form', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_pedido_venda_item[]' id='chkt_pedido_venda_item<?=$i;?>' value='<?=$campos[$i]['id_pedido_venda_item'];?>' onclick="checkbox_excluir('form', '<?=$i;?>', '#E8E8E8')" class='checkbox' <?=$disabled_checked;?>>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 1, ',', '.');?>
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
        <?
                echo number_format($campos[$i]['preco_liq_final'] * $campos[$i]['qtde'], 2, ',', '.');
            
                if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                    $margem             = custos::margem_lucro($campos[$i]['id_orcamento_venda_item'], $tx_financeira, $campos[0]['id_uf'], $campos[$i]['preco_liq_final']);

                    $cor_instantanea    = ($margem[0] < 0) ? 'red' : '#E8E8E8';//$margem[0] valor real da margem
                    $cor_gravada        = ($campos[$i]['margem_lucro'] < 0) ? 'red': '#E8E8E8';
                    $cor_estimada       = ($campos[$i]['margem_lucro_estimada'] < 0) ? 'red': '#E8E8E8';
        ?>
                <!--************************************************************************-->
                <!--A folha de estilo fica aqui dentro do Loop porque as cores de fonte 
                dos IDs se comportaram de acordo com o que foi definido acima pelo PHP ...-->
                <style type='text/css'>
                    #id_ml_instantanea<?=$i;?>::-moz-selection {
                        background:#A9A9A9;
                        color:<?=$cor_instantanea;?>
                    }
                    #id_ml_gravada<?=$i;?>::-moz-selection {
                        background:#A9A9A9;
                        color:<?=$cor_gravada;?>
                    }
                    #id_ml_estimada<?=$i;?>::-moz-selection {
                        background:#A9A9A9;
                        color:<?=$cor_estimada;?>
                    }
                </style>
                <!--************************************************************************-->
            
                <a href="javascript:nova_janela('/erp/albafer/modulo/vendas/orcamentos/itens/alterar_margem_lucro.php?id_orcamento_venda_item=<?=$campos_itens[$i]['id_orcamento_venda_item'];?>&preco_liq_final=<?=number_format($campos_itens[$i]['preco_liq_final'], 2, ',', '.');?>&margem_lucro=<?=$margem[1];?>', 'COMPRAS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Compras' class='link'>
                    <font color="<?=$cor_instantanea;?>" id='id_ml_instantanea<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'><br/>
                        <?='ML='.$margem[1];//Valor Descritivo da Margem ...?>
                    </font>
                    <font color="<?=$cor_gravada;?>" id='id_ml_gravada<?=$i;?>' title='Margem de Lucro Instant&acirc;nea' style='cursor:help'>
                        <br/><?='MLG='.number_format($campos[$i]['margem_lucro'], 2, ',', '.');?>
                    </font>
                    <font color="<?=$cor_estimada;?>" id='id_ml_estimada<?=$i;?>' title='Margem de Lucro Estimada' style='cursor:help'>
                        <br/><?='MLEst='.number_format($campos[$i]['margem_lucro_estimada'], 2, ',', '.');?>
                    </font>
                </a>
        <?
                }
        ?>
        </td>
    </tr>
<?
                $total_itens+= $campos[$i]['preco_liq_final'] * $campos[$i]['qtde'];
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
<?
        }
    }
?>
</body>
<input type='hidden' name='id_pedido_venda' value='<?=$_GET['id_pedido_venda'];?>'>
</form>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
    <pre>
    * Só é possível excluir o(s) item(ns) que não possui(em) Qtde Separada.
    </pre>
</pre>
<?}?>