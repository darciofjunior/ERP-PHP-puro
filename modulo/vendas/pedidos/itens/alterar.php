<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pedidos/itens/consultar.php', '../../../../');
$mensagem[1] = "<font class='confirmacao'>ITEM(NS) ATUALIZADO(S) COM SUCESSO.</font>";

//Verificação para ver se o Pedido está liberado ...
if(vendas::situacao_pedido($id_pedido_venda) == 1) {
?>
<html>
<head>
<title>.:: Alterar Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            NÃO É POSSÍVEL ALTERAR ITEM(NS) ! ESTE PEDIDO ESTÁ LIBERADO.
        </td>
    </tr>
    <tr>
        <td align='center'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red'class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    exit;
}

if(!empty($_POST['id_pedido_venda_item'])) {
    estoque_acabado::controle_pedidos_vendas_itens($_POST['id_pedido_venda_item'], 2, $_POST['txt_quantidade']);
    $valor = 1;
}

//Seleciona os itens do Pedido ...
$sql = "SELECT ed.`id_empresa_divisao`, ed.`razaosocial`, gpa.`id_grupo_pa`, gpa.`id_familia`, gpa.`nome`, 
        gpa.`lote_min_producao_reais`, ovi.`id_orcamento_venda_item`, ovi.`id_orcamento_venda`, ovi.`qtde`, 
        ovi.`preco_liq_final`, ovi.`prazo_entrega`, ovi.`promocao`, pa.`id_produto_acabado`, pa.`operacao_custo`, 
        pa.`referencia`, pa.`preco_promocional`, pa.`observacao` AS observacao_produto, u.`sigla` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
        WHERE pvi.`id_pedido_venda` = '$id_pedido_venda' ORDER BY ovi.`id_orcamento_venda_item`, pa.`discriminacao` ";
if(empty($posicao)) $posicao = 1;
$campos                     = bancos::sql($sql, ($posicao - 1), $posicao);
$id_empresa_divisao         = $campos[0]['id_empresa_divisao'];
$razaosocial                = $campos[0]['razaosocial'];
$id_grupo_pa                = $campos[0]['id_grupo_pa'];
$id_familia                 = $campos[0]['id_familia'];
$nome                       = $campos[0]['nome'];
$lote_min_producao_reais    = $campos[0]['lote_min_producao_reais'];
$id_orcamento_venda_item    = $campos[0]['id_orcamento_venda_item'];
$id_orcamento_venda         = $campos[0]['id_orcamento_venda'];
$qtde_orcamento             = $campos[0]['qtde'];
$preco_liq_final            = $campos[0]['preco_liq_final'];
$prazo_entrega_item         = $campos[0]['prazo_entrega'];
$promocao                   = $campos[0]['promocao'];
$id_produto_acabado         = $campos[0]['id_produto_acabado'];
$operacao_custo             = $campos[0]['operacao_custo'];
$referencia                 = $campos[0]['referencia'];
$preco_promocional          = $campos[0]['preco_promocional'];
$observacao_produto         = $campos[0]['observacao_produto'];
$sigla                      = $campos[0]['sigla'];

/*Aqui eu verifico a quantidade desse item em Estoque e já trago o status do Estoque para saber se este
pode ser manipulado pelo Estoquista*/
$vetor              = estoque_acabado::qtde_estoque($id_produto_acabado);
$status_estoque     = $vetor[1];
$qtde_estoque_disp  = $vetor[3];
$racionado          = $vetor[5];
?>
<html>
<head>
<title>.:: Alterar Itens do Pedido N.º&nbsp;<?=$id_pedido_venda;?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar(posicao, verificar) {
/*Aqui significa que estou submetendo o formulário através do botão submit, sendo
faz requisição das condições de validação*/
    if(typeof(verificar) != 'undefined') {
//Só faz essa consistência quando o PA for do tipo normal de linha
//Quantidade
        if(document.form.txt_quantidade.disabled == false) {
            if(!texto('form', 'txt_quantidade', '1', '1234567890,.', 'QUANTIDADE', '1')) {
                return false
            }
//Verifica a Quantidade
            var quantidade = eval(strtofloat(document.form.txt_quantidade.value))
            if(quantidade == 0) {
                alert('QUANTIDADE INVÁLIDA ! \nVALOR IGUAL A ZERO !')
                document.form.txt_quantidade.focus()
                document.form.txt_quantidade.select()
                return false
            }
        }
    }
/*Aqui chama uma função mais abaixo, porque tem uma variável em PHP em que o
select só foi feito mais abaixo*/
    return comparar_quantidade(posicao)
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        opener.parent.itens.document.form.submit()
        opener.parent.rodape.document.form.submit()
    }
}

function calculo_total_lote() {
    if(document.form.txt_quantidade.value != '') {//Preenchido
        var preco_liq_final = eval(strtofloat(document.form.txt_preco_liquido_final_rs.value))
        var quantidade      = eval(document.form.txt_quantidade.value)
        document.form.txt_total_rs_lote.value = preco_liq_final * quantidade
        document.form.txt_total_rs_lote.value = arred(document.form.txt_total_rs_lote.value, 2, 1)
    }else {//Vazio
        document.form.txt_total_rs_lote.value = ''
    }
}
</Script>
</head>
<body onload='calculo_total_lote()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>', 1)">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Detalhes do Item do Orçamento N.º&nbsp;
            <font color='yellow'>
                <?=$id_orcamento_venda;?>
            </font>
        </td>
    </tr>
<?
	/*Aqui eu busco o id_cliente e o id_pais através do id_orcamento, utilizo esse id praticamente
	no fim do arquivo e o tipo_moeda p/ parte de cálculo do Orc*/
	$sql = "SELECT c.`id_pais` 
                FROM `orcamentos_vendas` ov 
                INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
                WHERE ov.`id_orcamento_venda` = '$id_orcamento_venda' ";
	$campos_cliente = bancos::sql($sql);
	$id_pais        = $campos_cliente[0]['id_pais'];
	//Significa que o Cliente é do Tipo Internacional
	if($id_pais != 31) {
            $tipo_moeda = 'U$';
            //Uso essa variável lá em baixo no Lote Mínimo
            $dolar_dia = genericas::moeda_dia('dolar');
            //Significa que o Cliente é do Tipo Nacional
	}else {
            $tipo_moeda = 'R$';
            $dolar_dia  = 1;
	}
?>
    <tr class='linhanormal'>
        <td>
            <b>Grupo:</b>
        </td>
        <td colspan='2'>
            <font color="#0000FF">
                <?=$nome;?>
            </font>
        </td>
        <td>
            <b title='Empresa Divisão'>Divisão: </b>
            <?=$razaosocial;?>
        </td>
        <td colspan='2'>
        <b title='Classificação Fiscal'>C. F.:</b>
        <?
            //Aqui já se aproveita o busca também o IPI da Class. Fiscal. q é utilizado + abaixo
            $sql = "SELECT cf.classific_fiscal, cf.ipi 
                    FROM `grupos_pas` gpa 
                    INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    WHERE gpa.`id_grupo_pa` = '$id_grupo_pa' ";
            $campos_classif_fiscal = bancos::sql($sql);
            if(count($campos_classif_fiscal) == 1) {
                if($operacao_custo == 1) {//Revenda
                    $ipi_classific_fiscal   = 'S/IPI'; //então é zero de IPI
                    $classific_fiscal       = $campos_classif_fiscal[0]['classific_fiscal'];
                }else {
                    $classific_fiscal       = $campos_classif_fiscal[0]['classific_fiscal'];
                    $ipi_classific_fiscal   = number_format($campos_classif_fiscal[0]['ipi'], 1, ',', '.');
                }
            }else {
                $classific_fiscal       = '';
                $ipi_classific_fiscal   = 0;
            }
            //Quando o país é do Tipo Internacional não existe IPI
            if($id_pais != 31) $ipi_classific_fiscal = 'S/IPI';
            echo $classific_fiscal;
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Ref. * Discriminação:
        </td>
        <td colspan='5'>
            <?=$referencia.' * '.intermodular::pa_discriminacao($id_produto_acabado);?>
        </td>
    </tr>
<?
//Só aparecerá a Parte de Promoção para os Produtos Normais
    if($referencia != 'ESP') {
        if($preco_promocional != '0.00') {//Só exibe o preço quando tem preço cadastrado ...
?>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td colspan='5'>
            <?$rotulo = ($promocao == 'S') ? 'Com ' : 'Sem ';?>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=$rotulo;?> Promo&ccedil;&atilde;o
            </font>
            &nbsp;&nbsp;<b>-></b>&nbsp;&nbsp;<?=$tipo_moeda;?>
            <input type='text' name='txt_preco_promocional' value='<?=number_format($preco_promocional, 2, ',', '.');?>' title='Preço Promocional' size='10' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade:</b>
        </td>
        <td colspan='2'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=number_format($qtde_orcamento, 1, ',', '.');?>
            </font>
        </td>
        <td>
            <b>Prazo de Entrega:</b>
        </td>
        <td colspan='2'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                $vetor_prazos_entrega = vendas::prazos_entrega();

                foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
                    if($prazo_entrega_item == $indice) echo $prazo_entrega;
                }
            ?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='6'>
            &nbsp;
        </td>
    </tr>
<?
//Parte de Pedidos
//Seleciona a qtde de itens que existe no pedido
$sql = "SELECT COUNT(`id_pedido_venda_item`) AS qtde_itens 
        FROM `pedidos_vendas_itens` 
        WHERE `id_pedido_venda` = '$id_pedido_venda' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

//Seleciona os itens do pedido
$sql = "SELECT * 
        FROM `pedidos_vendas_itens` 
        WHERE `id_pedido_venda` = '$id_pedido_venda' 
        ORDER BY `id_orcamento_venda_item` ";
if(empty($posicao)) $posicao = 1;
$campos                 = bancos::sql($sql, ($posicao - 1), $posicao);
$id_pedido_venda_item	= $campos[0]['id_pedido_venda_item'];
$qtde                   = $campos[0]['qtde'];
$qtde_pendente          = $campos[0]['qtde_pendente'];
$total_vale             = $campos[0]['vale'];
$status_estoque_item	= $campos[0]['status_estoque'];
$qtde_total_pedido      = (int)estoque_acabado::qtde_total_pedido($id_orcamento_venda_item, $id_pedido_venda_item);
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            Alterar Itens do Pedido N.º&nbsp;
            <font color='yellow'>
                <?=$id_pedido_venda;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde Pedida:</b>
        </td>
        <td>
            <b>Qtde Dispon. do Orc.:</b>
        </td>
        <td>
            <b>Qtde Separada:</b>
        </td>
        <td>
            <b>Qtde Pendente:</b>
        </td>
        <td>
            <b>Total de Vale:</b>
        </td>
        <td>
            <b>Estoque Disponível:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
<?
//Controle para poder alterar a quantidade, mas isso só quando for PA normal de linha
	if($referencia == 'ESP' || $status_estoque_item == '1') {//Normais
            $disabled_botao = 'disabled';
            $disabled       = 'disabled';
            $class          = 'textdisabled';
	}else {
//Se a qtde que foi faturada for > 0, então tem que desabilitar o alterar qtde do Item e o botão Salvar
            if($campos[0]['qtde_faturada'] > 0) {
                $disabled_botao = 'disabled';
                $disabled       = 'disabled';
                $class          = 'textdisabled';
            }else {
                $disabled_botao = '';
                $disabled       = '';
                $class          = 'caixadetexto';
            }
	}
        
        /************************************************************/
        /****Tratamento com as Casas Decimais do campo Quantidade****/
        /************************************************************/
        if($sigla == 'KG') {//Essa é a única sigla que permite trabalharmos com Qtde Decimais ...
            $onkeyup            = "verifica(this, 'moeda_especial', 1, '', event) ";
            $qtde_apresentar    = number_format($qtde, 1, ',', '.');
        }else {
            $onkeyup            = "verifica(this, 'aceita', 'numeros', '', event) ";
            $qtde_apresentar    = (integer)$qtde;
        }
        /************************************************************/
?>
            <input type='text' name='txt_quantidade' value='<?=$qtde_apresentar;?>' title='Digite a Quantidade' onkeyup="<?=$onkeyup;?>;calcular_estoque()" size='10' maxlength='10' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name='txt_quantidade_disponivel' title='Quantidade Disponível' size='10' maxlength='10' class='textdisabled' disabled>
        </td>
        <td>
        <?
            $qtde_separada_item = $qtde - $qtde_pendente - $total_vale;
        ?>
            <input type='text' name='txt_quantidade_separada' value='<?=number_format($qtde_separada_item, 1, ',', '.');?>' title='Quantidade Separada' size='10' maxlength='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_quantidade_pendente' value='<?=number_format($qtde_pendente, 2, ',', '.');?>' title='Quantidade Pendente' size='10' maxlength='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_total_vale' value='<?=number_format($total_vale, 2, ',', '.');?>' title='Total de Vale' size='10' maxlength='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_estoque_disponivel' value='<?=number_format($qtde_estoque_disp, 2, ',', '.');?>' title='Quantidade Pendente' size='10' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Pçs / Emb:</b>
            <?
//Traz a quantidade de peças por embalagem da embalagem principal daquele produto
                $sql = "SELECT `pecas_por_emb` 
                        FROM `pas_vs_pis_embs` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `embalagem_default` = '1' LIMIT 1 ";
                $campos_pecas_embalagem = bancos::sql($sql);
                $pecas_embalagem        = (count($campos_pecas_embalagem) == 1) ? number_format($campos_pecas_embalagem[0]['pecas_por_emb'], 0, ',', '.') : 0;
            ?>
            <input type='text' name='txt_pcs_embalagem' value='<?=$pecas_embalagem;?>' title='Digite o Pçs / Embalagem' onkeyup="verifica(this, 'moeda_especial', '', '', event)" size='10' maxlength='15' class='textdisabled' disabled>
        </td>
        <td>
            <font color='#FF0000'>
                <b>Pre&ccedil;o Liq. Final <?=$tipo_moeda;?>:</b>
            </font>
        <td colspan='2'>
            <input type='text' name='txt_preco_liquido_final_rs' value='<?=number_format($preco_liq_final, 2, ',', '.');?>' title='Pre&ccedil;o Liq. Final <?=$tipo_moeda;?>' onkeyup="verifica(this, 'moeda_especial', '', '', event)" size='15' maxlength='15' class='textdisabled' disabled>
            <b>&nbsp;&nbsp;+&nbsp;&nbsp;IPI %:</b>
            <input type='text' name='txt_ipi_porc' value='<?=$ipi_classific_fiscal;?>' title='Digite o IPI %' onkeyup="verifica(this, 'moeda_especial', '', '', event)" size='10' maxlength='15' class='textdisabled' disabled>
        </td>
        <td align='right'>
            <font color='#FF0000'>
                <b>Total <?=$tipo_moeda;?> Lote:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_total_rs_lote' title='Total <?=$tipo_moeda;?> Lote' onkeyup="verifica(this, 'moeda_especial', '', '', event)" size='15' maxlength='15' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='6'>
        <?
//$status_estoque => para saber se o estoquista esta manpulando o  produto 0-free  1-locked
//$status_estoque_item => é para saber se o item poder ser manipulado ou liberado para manipular 0-free 1-lock
            if($status_estoque == 0 && $racionado == 0) {
                if($status_estoque_item == 0) {
                    echo '<font color="blue"><b>PRODUTO LIBERADO PARA USO !</b></font>';
                }else {
                    echo '<font color="red"><b>PRODUTO BLOQUEADO !!! ESTE PRODUTO JÁ FOI MANIPULADO PELO ESTOQUISTA !</b></font>';
                }
            }else if($status_estoque == 1 && $racionado == 0) {
                echo '<font color="red"><b>PRODUTO BLOQUEADO !!! ESTÁ SENDO MANIPULADO PELO ESTOQUISTA !</b></font>';
            }else {
                echo '<font color="red"><b>PRODUTO RACIONADO !</b></font>';
            }
        ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');calcular_estoque();calculo_total_lote()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao' <?=$disabled_botao;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='6'>
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
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
<?
//Aqui printa a observação do Produto, caso existir
    if(!empty($observacao_produto)) {
?>
    <tr class='linhacabecalho'>
        <td colspan='6'>
            Observação do Produto:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='6'>
            <?=$observacao_produto;?>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='6'>
            &nbsp;
        </td>
    </tr>
<?
    }
//Aqui busca os Follow_ups registrados dos Produtos Acabados
    $sql = "SELECT l.`login`, pafu.* 
            FROM `produtos_acabados_follow_ups` pafu 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = pafu.`id_funcionario` 
            INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
            WHERE pafu.`id_produto_acabado` = '$id_produto_acabado' ORDER BY pafu.`data_sys` DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Follow-up(s) Registrado(s) do Produto Acabado
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Login
        </td>
        <td align='center'>
            Data / Hora
        </td>
        <td colspan='4'>
            Observação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['login'];?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' '.substr($campos[$i]['data_sys'], 11, 5);?>
        </td>
        <td colspan='4' align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr align='center'>
        <td colspan='6'>
            &nbsp;
        </td>
    </tr>
<?
    }
?>
</table>
<input type='hidden' name='id_pedido_venda' value='<?=$id_pedido_venda;?>'>
<input type='hidden' name='id_pedido_venda_item' value='<?=$id_pedido_venda_item;?>'>
<input type='hidden' name='posicao' value='<?=$posicao;?>'>
<input type='hidden' name='nao_atualizar'>
</form>
</body>
</html>
<?
//Se for Cliente do Tipo Internacional, divide pelo dólar do dia o Lote Mínimo de Produção
    if($id_pais != 31) $lote_min_producao_reais/= $dolar_dia;
?>
<pre>
<font color='blue'><b>Lote Mínimo em <?=$tipo_moeda;?> -> </b></font><b><?=number_format($lote_min_producao_reais, 2, ',', '.');?></b>
</pre>
<?
//Adaptei as bibliotecas de JS aqui embaixo por causa da variável $qtde_total_em_pedido só foi utilizada + abaixo na tela ...
?>
<Script Language = 'JavaScript'>
function calcular_estoque() {
    var qtde_orcamento          = eval('<?=$qtde_orcamento;?>')
    var qtde_total_em_pedido	= eval('<?=$qtde_total_pedido;?>')
    var qtde_orcamento          = qtde_orcamento - qtde_total_em_pedido
    
    if(document.form.txt_quantidade.value != '') {//Diferente de Vazio ...
        var qtde_digitada = eval(document.form.txt_quantidade.value)
    }else {
        var qtde_digitada = 0
    }
    
    document.form.txt_quantidade_disponivel.value = qtde_orcamento - eval(strtofloat(document.form.txt_quantidade.value))
    
    if(document.form.txt_quantidade_disponivel.value != 'NaN') {
        document.form.txt_quantidade_disponivel.value = arred(document.form.txt_quantidade_disponivel.value, 1, 1)
    }else {
        document.form.txt_quantidade_disponivel.value = ''
    }
}

//Esse parâmetro vem da função lá validar() que está mais acima ...
function comparar_quantidade(posicao) {//Entra nesta funcao quando submete ...
    var referencia              = '<?=$referencia;?>'
    var qtde_orcamento          = eval('<?=$qtde_orcamento;?>')
    var qtde_total_em_pedido    = eval('<?=$qtde_total_pedido;?>')
    var id_familia              = eval('<?=$id_familia;?>')
    var qtde                    = (document.form.txt_quantidade.value != '') ? eval(document.form.txt_quantidade.value) : 0
    var comparacao              = qtde_orcamento - qtde_total_em_pedido
    if(qtde > comparacao) {//Comparação entre a qtde e o restante
        alert('QUANTIDADE DIGITADA INVÁLIDA !\nQUANTIDADE DIGITADA MAIOR DO QUE A QUANTIDADE DISPONÍVEL EM ORÇAMENTO !')
        document.form.txt_quantidade.value = qtde_total_em_pedido//mesmo pq nao pode ser maior 
        document.form.txt_quantidade.select()
        return false
    }

    //Aqui nessa parte do Script compara a quantidade de peças por embalagem para os produtos normais de linha
    if(referencia != 'ESP') {
        //Verifica o Mod (Resto da Divisão)
        var resto_divisao = eval(document.form.txt_quantidade.value) % (document.form.txt_pcs_embalagem.value)
        if(resto_divisao != 0 && !isNaN(resto_divisao)) {//Não está Compatível
            var sugestao = (parseInt(document.form.txt_quantidade.value / document.form.txt_pcs_embalagem.value) + 1) * document.form.txt_pcs_embalagem.value
            /*Se a Família = 'PINOS' 
            ou Família = 'LIMAS' 
            ou Família = 'RISCADOR' 
            ou Família = 'SACA BUCHA' 
            ou Família = 'CHAVES PARA MANDRIL' 
            ou Família = 'FLUÍDOS, ÓLEOS, LUBRIFICANTES' 
            ou Família = 'CABO DE LIMA' 
            não dá opção p/ o usuário abrir a embalagem ...*/
            if(id_familia == 2 || id_familia == 3 || id_familia == 7 || id_familia == 17 || id_familia == 19 || id_familia == 26 || id_familia == 27) {
                alert('A QTDE DO     '+referencia+'     NÃO ESTÁ COMPATÍVEL COM A QTDE DE PÇS / EMBALAGEM ! \nALTERE A QUANTIDADE !!!        SUGESTÃO  =  '+sugestao+'  .')
                document.form.txt_quantidade.focus()
                document.form.txt_quantidade.select()
                return false
            }else {
                var pergunta = confirm('A QTDE DO     '+referencia+'     NÃO ESTÁ COMPATÍVEL COM A QTDE DE PÇS / EMBALAGEM ! \n DESEJA MANTER ESTÁ QUANTIDADE ?        SUGESTÃO  =  '+sugestao+'  .')
                if(pergunta == false) {//Não aceitou a qtde incompatível
//Função foi chamada através do botão Salvar
                    if(typeof(posicao) != 'undefined') {
                        document.form.txt_quantidade.focus()
                        document.form.txt_quantidade.select()
                        return false
//Função foi chamada através do evento onblur
                    }else {
                        document.form.reset()
                        calculo_preco_liq_faturado()
                        document.form.txt_quantidade.focus()
                    }
                }
            }
        }
    }
    //Desabilita a caixa para o caso de pertencer a um produto q é do tipo ESPECIAL
    document.form.txt_quantidade.disabled = false
    limpeza_moeda('form', 'txt_quantidade, ')
    //Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value = posicao
    //Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.txt_quantidade_pendente.disabled = false
    atualizar_abaixo()
    //Submetendo o Formulário
    document.form.submit()
}
calcular_estoque()
</Script>