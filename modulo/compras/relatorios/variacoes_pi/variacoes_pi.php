<? 
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $txt_data_inicial = ($_SERVER['REQUEST_METHOD'] == 'POST') ?  $_POST['txt_data_inicial'] : $_GET['txt_data_inicial'];
    
    switch($opt_opcao) {
        case 1://Fornecedor ... 
            $sql = "SELECT f.`id_fornecedor`, pi.`id_produto_insumo`, pi.`discriminacao` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pi.`id_produto_insumo` AND fpi.`ativo` = '1' 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
                    WHERE f.`razaosocial` = '$txt_consultar' 
                    AND pi.`ativo` = '1' ";
        break;
        case 2://Referência ...
            $sql = "SELECT pi.`id_produto_insumo`, pi.`discriminacao` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE g.`referencia` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' ";
        break;
        default://Discriminação ...
            $sql = "SELECT `id_produto_insumo`, `discriminacao` 
                    FROM `produtos_insumos` 
                    WHERE `discriminacao` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'variacoes_pi.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Relatório Variações PI ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Relatório Variações de PI(s)
            &nbsp;-&nbsp;
            Data Inicial: 
            <font color='yellow'>
                <?=$txt_data_inicial;?>
            </font>
            &nbsp;-&nbsp;
            Período: 
            <font color='yellow'>
            <?
                $vetor_data             = data::diferenca_data(data::datatodate($txt_data_inicial, '-'), date('Y-m-d'));
                $prazo_variacao_anos    = $vetor_data[0] / 365;
                echo number_format($prazo_variacao_anos, 1, ',', '.').' anos';
            ?>
            </font>
            <?
                //Significa que a consulta foi feito pelo campo Fornecedor ...
                if($opt_opcao == 1) {
            ?>
            &nbsp;-&nbsp;
            Fornecedor: 
            <font color='yellow'>
                <?=$txt_consultar;?>
            </font>
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Discriminação
        </td>
        <td>
            Preço de Compra
        </td>
        <td>
            Preço da Lista
        </td>
        <td>
            Variação em % no Período
        </td>
        <td>
            Aumento Médio anual %
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <a href = '../../estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>' title='Detalhes' class='html5lightbox'>
                <?=$campos[$i]['discriminacao'];?>
            </a>
        </td>
        <td>
        <?
            //Significa que a consulta foi feita pelo campo Fornecedor ...
            if($opt_opcao == 1) $condicao_fornecedor = " AND p.`id_fornecedor` = '".$campos[$i]['id_fornecedor']."' ";//Trago a 1ª Compra do Fornecedor que foi filtrado ...
        
            //Busco a primeira Compra do id_produto_insumo do Loop posterior à Data Inicial digitada ...
            $sql = "SELECT p.`id_fornecedor`, ip.`preco_unitario`, 
                    DATE_FORMAT(p.`data_emissao`, '%d/%m/%Y') AS data_emissao 
                    FROM `itens_pedidos` ip 
                    INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`data_emissao` >= '".data::datatodate($txt_data_inicial, '-')." ' $condicao_fornecedor 
                    WHERE ip.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
            $campos_pedido = bancos::sql($sql);
            if(count($campos_pedido) == 1) {
                echo 'R$ '.number_format($campos_pedido[0]['preco_unitario'], 2, ',', '.').' - '.$campos_pedido[0]['data_emissao'];
                
                if($opt_opcao != 1) {//Significa que a consulta foi feita pelo campo Fornecedor ...
                    //Apresento aqui o nome Fantasia do Fornecedor Default ...
                    $sql = "SELECT `razaosocial` 
                            FROM `fornecedores` 
                            WHERE `id_fornecedor` = '".$campos_pedido[0]['id_fornecedor']."' LIMIT 1 ";
                    $campos_fornecedor = bancos::sql($sql);
                    echo '<font color="red"><b> ('.$campos_fornecedor[0]['razaosocial'].')</b></font>';
                }
            }
        ?>
        </td>
        <td>
        <?
            if($opt_opcao == 1) {//Significa que a consulta foi feita pelo campo Fornecedor ...
                $id_fornecedor_default = $campos[$i]['id_fornecedor'];//Nesse caso será o do próprio filtro ...
            }else {//Significa que a consulta foi feita por qualquer outro campo ...
                //Busco o "Fornecedor Default" do id_produto_insumo do Loop ...
                $id_fornecedor_default = custos::preco_custo_pi($campos[$i]['id_produto_insumo'], 0, 1);
            }

            /*Busco o Preço do id_produto_insumo do Loop e $id_fornecedor_default que foi encontrado 
            na sua respectiva Lista de Preço ...*/
            $sql = "SELECT `preco`, DATE_FORMAT(SUBSTRING(`data_sys`, 1, 10), '%d/%m/%Y') AS data_lista 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_fornecedor` = '$id_fornecedor_default' 
                    AND `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_lista = bancos::sql($sql);
            if(count($campos_lista) == 1) {
                echo 'R$ '.number_format($campos_lista[0]['preco'], 2, ',', '.').' - '.$campos_lista[0]['data_lista'];
                
                if($opt_opcao != 1) {//Significa que a consulta foi feita pelo campo Fornecedor ...
                    //Apresento aqui o nome Fantasia do Fornecedor Default ...
                    $sql = "SELECT `razaosocial` 
                            FROM `fornecedores` 
                            WHERE `id_fornecedor` = '$id_fornecedor_default' LIMIT 1 ";
                    $campos_fornecedor = bancos::sql($sql);
                    echo '<font color="red"><b> ('.$campos_fornecedor[0]['razaosocial'].')</b></font>';
                }
            }
        ?>
        </td>
        <td>
        <?
            if(!empty($campos_pedido[0]['preco_unitario']) && $campos_pedido[0]['preco_unitario'] != 0) {
                $preco_compra   = $campos_pedido[0]['preco_unitario'];
                $preco_lista    = $campos_lista[0]['preco'];
                $variacao_em_percentagem = (($preco_lista * 100) / $preco_compra) - 100;
                echo number_format($variacao_em_percentagem, 1, ',', '.').' %';
            }else {
                $variacao_em_percentagem = 0;
            }
        ?>
        </td>
        <td>
        <?
            if($variacao_em_percentagem > 0) {
                $fator_variacao             = (1 + $variacao_em_percentagem / 100);
                $aumento_anual_percentagem  = (pow($fator_variacao, 1 / $prazo_variacao_anos) - 1) * 100;
                
                echo number_format($aumento_anual_percentagem, 1, ',', '.').' %';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'variacoes_pi.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?	
    }
}else {
?>
<html>
<head>
<title>.:: Relatório Variações PI ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }else if(document.form.txt_consultar.value.length < 3 && document.form.opt_opcao[1].checked == false) {
            alert('DIGITE NO MINÍMO TRÊS CARACTERES !')
            document.form.txt_consultar.focus()
            return false
        }else if(document.form.txt_consultar.value.length < 2 && document.form.opt_opcao[1].checked == true) {
            alert('DIGITE NO MINÍMO DOIS CARACTERES !')
            document.form.txt_consultar.focus()
            return false
        }
    }
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
    
    var data_inicial    = document.form.txt_data_inicial.value
    data_inicial        = data_inicial.substr(6, 4) + data_inicial.substr(3, 2) + data_inicial.substr(0, 2)
    data_inicial        = eval(data_inicial)

    var data_hoje       = eval('<?=date('Ymd');?>')
    
    if(data_inicial > data_hoje) {
        alert('DATA INICIAL INVÁLIDA !!!\n\nA DATA INICIAL NÃO PODE SER MAIOR DO QUE A DATA DE HOJE !')
        document.form.txt_data_inicial.focus()
        document.form.txt_data_inicial.select()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório Variações de PI
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' id='txt_consultar' size='45' maxlength='45' onkeyup="if(document.form.opt_opcao[0].checked) {auto_complete('consultar_fornecedores.php', 'txt_consultar', -78, 40.5, event)}" autocomplete='off' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Fornecedor</label>
        </td>
        <td width='20%' colspan='2'>
            <b>Data Inicial</b>
            <input type='text' name='txt_data_inicial' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src='../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' onclick='document.form.txt_consultar.focus()' id='label2'>
            <label for='label2'>Referência</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='3' onclick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>Discriminação</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_consultar.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>