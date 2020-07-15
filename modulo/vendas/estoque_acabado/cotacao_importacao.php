<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_embarque		= $_POST['txt_data_embarque'];
    $txt_fator_correcao_mmv	= $_POST['txt_fator_correcao_mmv'];
    $txt_qtde_meses 		= $_POST['txt_qtde_meses'];
    $cmb_tipo_compra 		= $_POST['cmb_tipo_compra']; 
}else {
    $txt_data_embarque		= $_GET['txt_data_embarque'];
    $txt_fator_correcao_mmv	= $_GET['txt_fator_correcao_mmv'];
    $txt_qtde_meses 		= $_GET['txt_qtde_meses'];
    $cmb_tipo_compra 		= $_GET['cmb_tipo_compra'];
}

//Na primeira vez que carregar a Tela, o Sistema sugere Normal para o Tipo de Compra ...
if(empty($cmb_tipo_compra)) $cmb_tipo_compra = 'N';
?>
<html>
<head>
<title>.:: Cotação p/ Importação ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body onload='document.form.txt_desconto.focus()'>
<form name='form' action='' method='post'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Cotação p/ Importação - 
            <font color='yellow' size='-1'>
                Data: 
            </font>
            <?=date('d/m/Y');?>
            <br/>
            <?
                if(!empty($txt_data_embarque)) {
            ?>
            <font color='yellow'>
                Data Embarque: 
            </font>
            <?
                        echo $txt_data_embarque.'&nbsp;-&nbsp;';
                }
            ?>
            <font color='yellow'>
                Fat. MMV = 
            </font>
            <?=$txt_fator_correcao_mmv;?>&nbsp;e
            <?=$txt_qtde_meses;?> 
            <font color='yellow' size='-1'>
                Mês(es)
            </font>
            &nbsp;-&nbsp;
            <select name='cmb_tipo_compra' title='Selecione o Tipo de Compra' onchange='document.form.submit()' class='combo'>
            <?
                if($cmb_tipo_compra == 'N') {
                    $selectedn = 'selected';
                }else {
                    $selectede = 'selected';
                }
            ?>
                <option value='N' <?=$selectedn;?>>Normal</option>
                <option value='E' <?=$selectede;?>>Export</option>
            </select>
            &nbsp;-&nbsp;
            Desconto: <input type='text' name='txt_desconto' value='<?=$txt_desconto;?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size='9' maxlength='8' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            <font title='Média Mensal de Vendas' style='cursor:help'>
                M.M.V.
            </font>
        </td>
        <td rowspan='2'>
            Ref
        </td>
        <td rowspan='2'>
            Produto
        </td>
        <td rowspan='2'>
            <font title='Operação de Custo' style='cursor:help'>
                O.C.
            </font>
        </td>
        <td rowspan='2'>
            Compra<br/> Produção
        </td>
        <td colspan='5'>
            Quantidade / Estoque
        </td>
        <td rowspan='2'>
            Prazo de Entrega
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Estoque Real' style='cursor:help'>
                Real
            </font>
        </td>
        <td>
            <font title='Estoque Disponível' style='cursor:help'>
                Disp.
            </font>
        </td>
        <td>
            <font title='Pendência' style='cursor:help'>
                Pend.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido' style='cursor:help'>
                Comp.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido Programado &gt; que 30 dias' style='cursor:help'>
                Prog.
            </font>
        </td>
    </tr>
<?
    if(!empty($_POST['hdd_vetor_pas_cotacao_importacao'])) {
        $lista_pas_cotacao_importacao = substr($_POST['hdd_vetor_pas_cotacao_importacao'], 0, strlen($_POST['hdd_vetor_pas_cotacao_importacao']) - 1);
        $lista_pas_cotacao_importacao = explode(';', $lista_pas_cotacao_importacao);
    }
	
    for($i = 0; $i < count($lista_pas_cotacao_importacao); $i++) {
        $contador = 0;
        $id_produto_acabado = ''; $mmv = '';
//Aqui eu vasculho cada caractér do Item da Lista ...
        for($j = 0; $j < strlen($lista_pas_cotacao_importacao[$i]); $j++) {
            if(substr($lista_pas_cotacao_importacao[$i], $j, 1) == '|') {
                $contador++;
            }else {
                if($contador == 0) {
                    $id_produto_acabado.= substr($lista_pas_cotacao_importacao[$i], $j, 1);
                }else if($contador == 1) {
                    $mmv.= substr($lista_pas_cotacao_importacao[$i], $j, 1);
                }
            }
        }
        $vetor_produto_acabado[]    = $id_produto_acabado;
        $vetor_mmv[]                = $mmv;
    }

    foreach($vetor_produto_acabado as $i => $id_produto_acabado) {
            $sql = "SELECT operacao_custo, operacao_custo_sub, referencia, status_top 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos             = bancos::sql($sql);
            $retorno            = estoque_acabado::qtde_estoque($id_produto_acabado, 0);
            $compra             = estoque_acabado::compra_producao($id_produto_acabado);
            $producao           = $retorno[2];
            $est_comprometido   = $retorno[8];
//Se a Qtde em Compra ou Produção for < que a do Estoque Comprometido, então exibo a coluna na cor vermelha ...
            $font_compra        = ($compra < - ($est_comprometido)) ? "<font color='red'>" : "<font color='black'>";
            $font_producao      = ($producao < - ($est_comprometido)) ? "<font color='red'>" : "<font color='black'>";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=number_format($vetor_mmv[$i] * str_replace(',', '.', $txt_fator_correcao_mmv) * str_replace(',', '.', $txt_qtde_meses), 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[0]['referencia'];?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($id_produto_acabado);?>
        </td>
        <td>
        <?
            if($campos[0]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos[0]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }
            if($campos[0]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[0]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos[0]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos[0]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='right'>
        <?
            //Verifico se o PA tem relação com o PI, caso isso não acontece não apresenta o link ...
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            //Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
            if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 1) {
                if($font_compra == "<font color='black'>") {
        ?>
                <a href="javascript:nova_janela('../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Compra Produção" class="link">
        <?
                }
/****************Compra****************/
                if($font_compra == "<font color='black'>") $font_compra = "<font color='#6473D4'>";//Se link, exibe em Azul ...
                echo $font_compra.number_format($compra, 2, ',', '.');
/****************Produção****************/
                if(!empty($producao) && $producao!=0) {
                        if($font_producao == "<font color='black'>") $font_producao = "<font color='#6473D4'>";//Se link, exibe em Azul ...
                        echo ' / '.$font_producao.number_format($producao, 2, ',', '.');
                }
?>
                </a>
<?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
            }else if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 0) {//Não mostra o link
/****************Compra****************/
                echo $font_compra.number_format($compra, 2, ',', '.');
/****************Produção****************/
                if(!empty($producao) && $producao != 0) echo ' / '.$font_producao.number_format($producao, 2, ',', '.');
            }else {//Aqui o PA não tem relação com o PI
/****************Produção****************/
                echo $font_producao.number_format($producao, 2, ',', '.');
            }
?>
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td align='right'>
        <?
            if($est_comprometido < 0) {
                echo "<font color='red'>".segurancas::number_format($est_comprometido, 2, '.')."</font>";
            }else {
                echo segurancas::number_format($est_comprometido, 2, '.');
            }
        ?>
        </td>
        <td align="right">
            &nbsp;
        </td>
        <td align='center'>
            &nbsp;
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='txt_fator_correcao_mmv' value='<?=$txt_fator_correcao_mmv;?>'>
<input type='hidden' name='txt_qtde_meses' value='<?=$txt_qtde_meses;?>'>
</form>
</body>
</html>