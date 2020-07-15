<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Custos ...
require('../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) ITEM(NS) EM ABERTO PARA SER(EM) IGNORADO(S) NESTE ORÇAMENTO.</font>";

if($passo == 1) {
    //Garanto todos os Itens do Orçamento como sendo Ignorar Lote Mínimo do Grupo Faixa Orçável = 'N' ...
    $sql = "UPDATE `orcamentos_vendas_itens` SET `ignorar_lote_minimo_do_grupo_faixa_orcavel` = 'N' WHERE `id_orcamento_venda` = '$id_orcamento_venda' ";
    bancos::sql($sql);
    
    if(count($_POST['chkt_orcamento_venda_item']) > 0) {//Se existir pelo menos 1 Item marcado então faz o Processo ...
        foreach ($_POST['chkt_orcamento_venda_item'] as $i => $id_orcamento_venda_item) {//Faz a Marcação de Ignorar somente nos Itens marcados e zero a ML Estimada porque mexe com a ML ...
            $sql = "UPDATE `orcamentos_vendas_itens` SET `ignorar_lote_minimo_do_grupo_faixa_orcavel` = 'S' WHERE id_orcamento_venda_item = '$id_orcamento_venda_item' LIMIT 1 ";
            bancos::sql($sql);
/*******************************************************************************************************/
            vendas::calculo_preco_liq_final_item_orc($id_orcamento_venda_item, 'S');
/*******************************************************************************************************/
            //Aqui eu atualizo a ML Est do Iem do Orçamento ...
            custos::margem_lucro_estimada($id_orcamento_venda_item);
/*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($id_orcamento_venda, $id_orcamento_venda_item);
        }
    }
?>
    <Script Language = 'JavaScript'>
        alert('TODO(S) O(S) ITEM(NS) SELECIONADO(S) FORAM IGNORADO(S) COM SUCESSO !')
        parent.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'
    </Script>
<?
}else {
//Aqui eu busco o id_pais através do id_orcamento p/ saber qual o Tipo de Moeda do Cliente ...
    $sql = "SELECT c.`id_pais` 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
            WHERE ov.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' ";
    $campos_pais    = bancos::sql($sql);
    $id_pais        = $campos_pais[0]['id_pais'];
    
//Verifica se o Cliente é do Tipo Internacional ...
    $tipo_moeda = ($id_pais != 31) ? 'U$' : 'R$';
    
    
    
/*Exibe somente os itens que estão com Custos Liberados, diferentes de "Orçar" e "DEPTO TÉCNICO"
e que estejam em "Aberto" dos PA(s) do id_orcamento_venda passado por parâmetro ...*/
    $sql = "SELECT ovi.`id_orcamento_venda_item`, ovi.`qtde`, ovi.`ignorar_lote_minimo_do_grupo_faixa_orcavel`, 
            ovi.`preco_liq_final`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
            pa.`operacao_custo` 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` AND pa.`status_custo` = '1' 
            WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' 
            AND (`preco_liq_fat_disc` <> 'Orçar') 
            AND ovi.`status` < '1' ORDER BY ovi.`id_orcamento_venda_item` ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
<html>
<html>
<title>.:: Ignorar Lote Mínimo do Grupo Faixa Orçável ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
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
<title>.:: Ignorar Lote Mínimo do Grupo Faixa Orçável ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) {//Confirmando ...
                var mensagem = confirm('DESEJA IGNORAR O LOTE MÍNIMO DO(S) ITEM(NS) SELECIONADO(S) ?')
                if(mensagem == false) return false
            }
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar('<?=$total_itens_pedidos;?>')">
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Ignorar Lote Mínimo do Grupo Faixa Orçável do(s) Item(ns) em Aberto do Orçamento - N.º&nbsp;
            <font color='yellow'>
                <?=$_GET['id_orcamento_venda'];?>
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
        <td>Produto</td>
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
                Total <?=$tipo_moeda;?> <br>Lote s/ IPI
            </font>
        </td>
    </tr>
<?
    /*PA(s) que são normal de Linha, somente o Roberto 62 que é diretor e Dárcio 98 porque programa que 
    podem visualizar mais abaixo ...*/
    $vetor_funcionarios_com_acesso  = array(62, 98);
    $indice                         = 0;

    for($i = 0; $i < $linhas; $i++) {
        if($campos[$i]['referencia'] == 'ESP' || ($campos[$i]['referencia'] <> 'ESP' && in_array($_SESSION['id_funcionario'], $vetor_funcionarios_com_acesso))) {
            $checked = ($campos[$i]['ignorar_lote_minimo_do_grupo_faixa_orcavel'] == 'S') ? 'checked' : '';
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$indice;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_orcamento_venda_item[]' value="<?=$campos[$i]['id_orcamento_venda_item'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$indice;?>', '#E8E8E8')" class='checkbox' <?=$checked;?>>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif'>
<?
            if($campos[$i]['referencia'] != 'ESP') {
                echo $campos[$i]['referencia'].' * '.intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'],0);
            }else {
?>
                <?=$campos[$i]['referencia'].' * '.intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'],0);?>
<?
            }
?>
            </font>
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
            $indice++;
        }
    }
?>
    <tr align='right'>
        <td class='linhadestaque' colspan='5'>
            Total do(s) Item(ns) em <?=$tipo_moeda;?>: 
        </td>
        <td class='linhadestaque'>
            <font color='yellow'>
                <?=number_format($total_itens, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
<input type='hidden' name='id_orcamento_venda' value='<?=$_GET['id_orcamento_venda'];?>'>
</form>
</html>
<?}?>