<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');
?>
<html>
<title>.:: Recalcular Margem de Lucro ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function calcular(indice, acrescimo_acessorio) {
    //Acessórios são Cabos, SKINs, etc ...
    var preco_l_fat         = eval(strtofloat(document.getElementById('txt_preco_l_fat'+indice).value))
    var margem_lucro        = eval(strtofloat(document.getElementById('txt_margem_lucro'+indice).value))
//Se tiver digitado margem de lucro desejada preenchida, então realiza o cálculo
    if(document.getElementById('txt_margem_lucro_desejada'+indice).value != '') {
        var margem_lucro_desejada       = eval(strtofloat(document.getElementById('txt_margem_lucro_desejada'+indice).value))
        var custo_margem_lucro_zero     = preco_l_fat / (margem_lucro / 100 + 1)
        document.getElementById('txt_preco_liquido_final_rs'+indice).value = custo_margem_lucro_zero * (margem_lucro_desejada / 100 + 1)
        document.getElementById('txt_preco_liquido_final_rs'+indice).value = arred(document.getElementById('txt_preco_liquido_final_rs'+indice).value, 2, 1)
        
        if(document.getElementById('txt_preco_compra_lista_rs'+indice) != null) {
            var preco_compra_lista_rs                       = eval(strtofloat(document.getElementById('txt_preco_compra_lista_rs'+indice).value))
            var preco_compra_lista_rs_comprar_como_export   = eval(strtofloat(document.getElementById('txt_preco_compra_lista_rs_comprar_como_export'+indice).value))
            var custo_margem_lucro_zero_comprar_como_export = custo_margem_lucro_zero / preco_compra_lista_rs * preco_compra_lista_rs_comprar_como_export
            
            document.getElementById('txt_margem_lucro_comprar_como_export'+indice).value = (preco_l_fat / custo_margem_lucro_zero_comprar_como_export - 1) * 100
            document.getElementById('txt_margem_lucro_comprar_como_export'+indice).value = arred(document.getElementById('txt_margem_lucro_comprar_como_export'+indice).value, 1, 1)
        
            document.getElementById('txt_preco_liquido_final_rs_comprar_como_export'+indice).value = custo_margem_lucro_zero_comprar_como_export * (1 + margem_lucro_desejada / 100)
            document.getElementById('txt_preco_liquido_final_rs_comprar_como_export'+indice).value = arred(document.getElementById('txt_preco_liquido_final_rs_comprar_como_export'+indice).value, 2, 1)
            
            var preco_compra_ml_desejada_lista_mais_acessorios  = (eval(preco_compra_lista_rs) + eval(acrescimo_acessorio)) * preco_l_fat / eval(strtofloat(document.form.txt_preco_liquido_final_rs.value))
            document.getElementById('txt_preco_compra_ml_desejada_rs'+indice).value = preco_compra_ml_desejada_lista_mais_acessorios - acrescimo_acessorio
            document.getElementById('txt_preco_compra_ml_desejada_rs'+indice).value = arred(document.getElementById('txt_preco_compra_ml_desejada_rs'+indice).value, 2, 1)
            
            document.getElementById('txt_preco_compra_ml_desejada_rs_comprar_como_export'+indice).value = preco_l_fat / eval(strtofloat(document.getElementById('txt_preco_liquido_final_rs_comprar_como_export'+indice).value)) * preco_compra_lista_rs_comprar_como_export
            document.getElementById('txt_preco_compra_ml_desejada_rs_comprar_como_export'+indice).value = arred(document.getElementById('txt_preco_compra_ml_desejada_rs_comprar_como_export'+indice).value, 2, 1)
        }
    }else {
        if(document.getElementById('txt_preco_compra_lista_rs'+indice) != null) {
            document.getElementById('txt_preco_liquido_final_rs'+indice).value  = ''
            document.getElementById('txt_preco_compra_ml_desejada_rs'+indice).value   = ''
        }
    }
}

function carregar_iframe(indice, id_produto_insumo, id_orcamento_venda_item, id_fornecedor_prod_insumo, acrescimo_acessorio) {
    /*Esses parâmetros eu passo p/ os Detalhes de Compra, p/ calcular a ML do Pedido / ORC 
    caso comprássemos pelo P.Médio Corr.Atual ...*/
    var txt_preco_l_fat             = eval(strtofloat(document.getElementById('txt_preco_l_fat'+indice).value))
    var txt_margem_lucro            = eval(strtofloat(document.getElementById('txt_margem_lucro'+indice).value))
    var txt_margem_lucro_desejada   = eval(strtofloat(document.getElementById('txt_margem_lucro_desejada'+indice).value))
    var txt_preco_compra_lista_rs   = eval(strtofloat(document.getElementById('txt_preco_compra_lista_rs'+indice).value))
    document.getElementById('detalhes_baixas_manipulacoes'+indice).src = '../../../compras/estoque_i_c/detalhes_baixas_manipulacoes.php?indice='+indice+'&id_produto_insumo='+id_produto_insumo+'&exibir_pendencia_pedido_compras=1&txt_preco_l_fat='+txt_preco_l_fat+'&txt_margem_lucro='+txt_margem_lucro+'&txt_margem_lucro_desejada='+txt_margem_lucro_desejada+'&txt_preco_compra_lista_rs='+txt_preco_compra_lista_rs+'&id_orcamento_venda_item='+id_orcamento_venda_item+'&id_fornecedor_prod_insumo='+id_fornecedor_prod_insumo+'&acrescimo_acessorio='+acrescimo_acessorio+'&ignorar_seguranca_url=1&veio_vendas=1'
}
</Script>
</head>
<?
/****************Procedimento normal p/ o carregamento do Body*****************/
//Significa que se deseja visualizar a Margem de Lucro Mínima de Todos os Itens do $id_orcamento_venda passado por parâmetro ...
if(!empty($_GET['id_orcamento_venda'])) {
    //Aqui eu busco todos os Itens do Orçamento de Venda ...
    $sql = "SELECT id_orcamento_venda_item 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' ORDER BY id_orcamento_venda_item ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
    for($i = 0; $i < $linhas_itens; $i++) $vetor_orcamento_venda_item[] = $campos_itens[$i]['id_orcamento_venda_item'];
}else if($_GET['id_orcamento_venda_itens']) {//Significa q é desejado ver a Margem de Lucro Mínima de 1, 2 ou mais itens, menos todos ...
    $_GET['id_orcamento_venda_itens'] = substr($_GET['id_orcamento_venda_itens'], 0, strlen($_GET['id_orcamento_venda_itens']) - 2);
    $vetor_orcamento_venda_item = explode(',', $_GET['id_orcamento_venda_itens']);
}else {//Significa q é desejado ver a Margem de Lucro Mínima de um único item em específico do Orçamento que foi passado por parâmetro ...
    $vetor_orcamento_venda_item[] = $_GET['id_orcamento_venda_item'];
}
$linhas_itens = count($vetor_orcamento_venda_item);

if($linhas_itens == 0) {//Não existe nenhum Item p/ o Orçamento ...
?>
    <Script Language = 'JavaScript'>
        parent.window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'
    </Script>
<?
}else {
    for($i = 0; $i < $linhas_itens; $i++) {
        $prazo_faturamento = '';//Sempre limpo essa variável p/ não herdar valores do Loop anterior ...
        //Busca de alguns dados do Orçamento, estes são printados + abaixo
        $sql = "SELECT c.`id_uf`, c.`id_cliente_tipo`, c.`trading`, gpa.`id_familia`, ged.`margem_lucro_minima`, 
                ov.`id_orcamento_venda`, ov.`id_cliente`, ov.`finalidade`, ov.`artigo_isencao`, ov.`nota_sgd`, 
                ov.`prazo_a`, ov.`prazo_b`, ov.`prazo_c`, ov.`prazo_d`, ov.`comprar_como_export`, ovi.`qtde`, 
                ovi.`preco_liq_final`, pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
                pa.`referencia`, pa.`discriminacao`, pa.`status_top`, pa.`qtde_queima_estoque` 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
                INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE ovi.`id_orcamento_venda_item` = '$vetor_orcamento_venda_item[$i]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['prazo_d'] > 0) $prazo_faturamento = '/'.$campos[0]['prazo_d'];
        if($campos[0]['prazo_c'] > 0) $prazo_faturamento = '/'.$campos[0]['prazo_c'].$prazo_faturamento;
        if($campos[0]['prazo_b'] > 0) {
            $prazo_faturamento = $campos[0]['prazo_a'].'/'.$campos[0]['prazo_b'].$prazo_faturamento;
        }else {
            if($campos[0]['prazo_a'] == 0) {
                $prazo_faturamento = 'À vista';
            }else {
                $prazo_faturamento = $campos[0]['prazo_a'];
            }
        }
        //Se no Orçamento a opção de "Comprar como Export" estiver marcada não mostro as caixas abaixo da Coluna Comprar como Export ...
        if($campos[0]['comprar_como_export'] == 'S') {
            $visible_export     = "style = 'visibility:hidden'";
            $mensagem_export    = '<font color="red" title="Preço Export"><b> - Pr. EXPORT</b></font>';
        }else {
            $visible_export     = "style = 'visibility:visible'";
        }
        
        //Aqui é a verificação do Tipo de Nota
        if($campos[0]['nota_sgd'] == 'S') {
            $rotulo_sgd = ' - SGD';
        }else {
            $rotulo_sgd = ' - NF';
            //Somente quando a nota é do Tipo NF q existe existe, consequentemente verifico a Finalidade ...
            if($campos[0]['finalidade'] == 'C') {
                $finalidade = 'CONSUMO';
            }else if($campos[0]['finalidade'] == 'I') {
                $finalidade = 'INDUSTRIALIZAÇÃO';
            }else {
                $finalidade = 'REVENDA';
            }
            $rotulo_sgd.= '/'.$finalidade;
        }
        $prazo_faturamento.=$rotulo_sgd;
        /**********************************************************************************************/
        /*Se o PA for SKIN C/CABO na discriminação e o PA for da família "LIMA", sempre iremos descontar do Preço de 
        Compra Ideal em R$ : R$ 0,15 + R$ 0,52 = R$ 0,67 (Estes valores são os valores calculados nos 2 itens + abaixo) ...*/
        if(strpos($campos[0]['discriminacao'], 'SKIN C/CABO') !== false && $campos[0]['id_familia'] == 3) {
            $acrescimo_acessorio    = 0.67;
            $mensagem_extra         = ' (SKIN C/CABO Lima - R$ '.number_format($acrescimo_acessorio, 2, ',', '.').')';
            /*Se o PA tiver SKIN na discriminação e o PA for da família "LIMA", sempre iremos descontar do Preço de 
            Compra Ideal em R$ analogamente p.custo ML Zero R$ 0,17 * 0,9 * 0,97 = R$ 0,15 ...*/
        }else if(strpos($campos[0]['discriminacao'], 'SKIN') !== false && $campos[0]['id_familia'] == 3) {
            $acrescimo_acessorio    = 0.15;
            $mensagem_extra         = ' (SKIN Lima - R$ '.number_format($acrescimo_acessorio, 2, ',', '.').')';
            /*Se o PA for Com Cabo na discriminação e o PA for da família "LIMA", sempre iremos descontar do 
            Preço de Compra Ideal em R$ analogamente p.custo ML Zero (R$ 0,31 M. Obra Cabo + R$ 0,29 do Preço Médio 
            dos Cabos) * 0,9 * 0,97 = R$ 0,52 ...*/
        }else if(strpos($campos[0]['discriminacao'], 'C/CABO') !== false && $campos[0]['id_familia'] == 3) {
            $acrescimo_acessorio    = 0.52;
            $mensagem_extra         = ' (C/CABO Lima - R$ '.number_format($acrescimo_acessorio, 2, ',', '.').')';
        }else {
            $acrescimo_acessorio    = 0;
        }
        $tx_financeira = custos::calculo_taxa_financeira($campos[0]['id_orcamento_venda']);
?>
<body>
<form name='form'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
	<tr class='linhacabecalho' align='center'>
            <td colspan='3'>
                Recalcular Margem de Lucro
            </td>
	</tr>
	<tr class='linhadestaque'>
            <td>
                <font color='yellow'>
                    <b>Ref: </b>
                </font>
                <?=$campos[0]['referencia'];?>
            </td>
            <td colspan='2'>
                <font color='yellow'>
                    <b>Discriminação: </b>
                </font>
                <?=$campos[0]['discriminacao'];?>
            </td>
	</tr>
        <?
            /******************************************************************************************/
            if($campos[0]['qtde_queima_estoque'] > 0) {
        ?>
        <tr class='linhanormal'>
            <td colspan='3'>
                <img src="../../../../imagem/queima_estoque.png" title="Excesso de Estoque (Todos PAs Atrelados)" alt="Excesso de Estoque (Todos PAs Atrelados)" style='cursor:help' border='0'>
                <font color='darkblue'>
                    <b>EXCESSO DE ESTOQUE => </b>
                </font>
                <?=number_format($campos[0]['qtde_queima_estoque'], 2, ',', '.');?>
            </td>
	</tr>
        <?
            }
            /******************************************************************************************/
        ?>
        <tr class='linhanormal'>
		<td>Qtde ORC / Pedido:</td>
		<td>
                    <?=intval($campos[0]['qtde']);?>
		</td>
                <td>
                    <b>COMPRAR COMO EXPORT</b>
                </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                Pre&ccedil;o L. Final (Venda) R$:
            </td>
            <td>
                <input type='text' name="txt_preco_l_fat" id='txt_preco_l_fat<?=$i;?>' value="<?=number_format($campos[0]['preco_liq_final'], 2, ',', '.');?>" title="Digite o Preço Líquido Faturado" size="15" maxlength="20" class='textdisabled' disabled>
            </td>
            <td>
                <input type='text' name="txt_preco_l_fat_comprar_como_export" id='txt_preco_l_fat_comprar_como_export<?=$i;?>' value="<?=number_format($campos[0]['preco_liq_final'], 2, ',', '.');?>" title="Digite o Preço Líquido Faturado" size="15" maxlength="20" class='textdisabled' <?=$visible_export;?> disabled>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                Margem de Lucro:
            </td>
            <td>
                <!--Aki nessa parte eu faço um tratamento para não pegar o símbolo de percentagem-->
                <?
                    $margem                 = custos::margem_lucro($vetor_orcamento_venda_item[$i], $tx_financeira, $campos[0]['id_uf'], $campos[0]['preco_liq_final']);
                    $margem_lucro           = $margem[1];
                    if(strpos($margem_lucro, '%') !== false) $margem_lucro = substr($margem_lucro, 0, strlen($margem_lucro) - 2);
                ?>	
                <input type='text' name="txt_margem_lucro" id='txt_margem_lucro<?=$i;?>' value="<?=$margem_lucro;?>" title="Digite a Margem de Lucro" size="15" maxlength="20" class='textdisabled' disabled> %
            </td>
            <td>
                <input type='text' name="txt_margem_lucro_comprar_como_export" id='txt_margem_lucro_comprar_como_export<?=$i;?>' title="Digite a Margem de Lucro" size="15" maxlength="20" class='textdisabled' <?=$visible_export;?> disabled>
                <font <?=$visible_export;?>>%</font>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td><b>Margem de Lucro Desejada:</b></td>
            <td colspan='2'>
                <?
                    $valores                = vendas::calcular_ml_min_pa_vs_cliente($campos[0]['id_produto_acabado'], $campos[0]['id_cliente']);
                    $margem_lucro_minima    = $valores['margem_lucro_minima'];
                ?>
                <input type='text' name="txt_margem_lucro_desejada" id='txt_margem_lucro_desejada<?=$i;?>' value="<?=number_format($margem_lucro_minima, 1, ',', '.');?>" title="Digite a Margem de Lucro Desejada" onKeyUp="verifica(this, 'moeda_especial', '1', '1', event);calcular('<?=$i;?>', '<?=$acrescimo_acessorio;?>')" size="15" maxlength="20" class="caixadetexto"> %
                <b> - Margem de Lucro Mínima = <?=number_format($margem_lucro_minima, 1, ',', '.');?></b>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                Preço Líquido Final (Venda) p/ ML desejada R$:
            </td>
            <td>
                <input type='text' name="txt_preco_liquido_final_rs" id='txt_preco_liquido_final_rs<?=$i;?>' size="15" maxlength="20" class='textdisabled' disabled> - <b>P/ Fat. </b><?=$prazo_faturamento;?>
            </td>
            <td>
                <input type='text' name="txt_preco_liquido_final_rs_comprar_como_export" id='txt_preco_liquido_final_rs_comprar_como_export<?=$i;?>' size="15" maxlength="20" class='textdisabled' <?=$visible_export;?> disabled>
            </td>
	</tr>
        <?
            $id_produto_acabado = $campos[0]['id_produto_acabado'];//Mas se for I-I sempre existirá PA é claro ...
            //Se OC = 'Industrial' e OC-Sub = 'Revenda' ou OC = 'Revenda', existe PI ...
            if(($campos[0]['operacao_custo'] == 0 && $campos[0]['operacao_custo_sub']) == 1 || $campos[0]['operacao_custo'] == 1) {
                /*Apenas devemos usar o "PA da 7ª Etapa se ele for o PA 'principal' do Custo" p/ 
                cálculo da MLEst, essas famílias / Grupos colocadas na cláusula 
                não tem PA(s) principais. Porta Bits / Porta Bedame e Suporte Intercambiáveis ...*/
                if($campos[0]['operacao_custo'] == 0 && $campos[0]['id_familia'] != 16) {
                    custos::busca_primeiro_pa_revenda_atrelado_na_7etapa($id_produto_acabado, $campos[0]['operacao_custo'], $campos[0]['id_familia']);
                    $id_produto_acabado = $GLOBALS['id_produto_acabado'];
                }else {//PA de Revenda ...
                    //Aqui eu busco o PI que equivale ao PA ...
                    $sql = "SELECT id_produto_insumo 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '$id_produto_acabado' 
                            AND `id_produto_insumo` > '0' 
                            AND `ativo` = '1' LIMIT 1 ";
                    $campos_pi              = bancos::sql($sql);
                    $id_produto_insumo      = $campos_pi[0]['id_produto_insumo'];
                }
            }else {//Caso contrário, nunca existirá PI ...
                $id_produto_insumo  = 0;
            }
            //Verifico se esse PA é um PI ...
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pi              = bancos::sql($sql);
            $id_produto_insumo      = $campos_pi[0]['id_produto_insumo'];
            $id_fornecedor_default  = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1);

            //Busca do Preço na Lista de Preço do PI e do Fornecedor Default ...
            $sql = "SELECT f.id_pais, fpi.preco, fpi.preco_exportacao, fpi.forma_compra, fpi.tp_moeda 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor 
                    WHERE fpi.`id_fornecedor` = '$id_fornecedor_default' 
                    AND fpi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            $campos_lista = bancos::sql($sql);
            if($id_fornecedor_default == 146) {//Se o Fornecedor for Hispania, aentão eu busco os 2 preços da Lista, é a única exceção ...
                if($campos[0]['comprar_como_export'] == 'S' || $campos[0]['trading'] == 1) {//Hoje essa marcação só serve p/ a Hispania ...
                    $preco_compra_lista = $campos_lista[0]['preco_exportacao'];
                }else {
                    $preco_compra_lista = $campos_lista[0]['preco'];
                }
                $preco_export                   = $campos_lista[0]['preco_exportacao'];
                $campos_lista[0]['tp_moeda']    = 0;
            }else {//Se o país do Fornecedor for Brasil, então faço com que o Sistema interprete a Moeda como sendo em R$ ...
                if($campos_lista[0]['id_pais'] == 31) {
                    $preco_compra_lista = $campos_lista[0]['preco'];
                }else {//Se o País for Estrangeiro, irá utilizar o Preço Estrangeiro ...
                    $preco_compra_lista = $campos_lista[0]['preco_exportacao'];
                }
            }
            if($preco_compra_lista == 0) $id_produto_insumo = 0;
            //Só aparecerá esses campos se o PA for um PI ...
            if($id_produto_insumo > 0) {
                $vetor_forma_compra     = array('', 'FAT/NF', 'FAT/SGD', 'AV/NF', 'AV/SGD');//Para facilitar ...
                $vetor_tipo_moeda       = array('R$', 'U$', '&euro;');//Para facilitar ...
                //Aqui eu busco a referência do PA pelo qual eu estou exibindo a Lista de Preço ...
                $sql = "SELECT referencia 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                $campos_pa_da_lista = bancos::sql($sql);               
        ?>
    <tr class='linhanormal'>
        <td>
            Preço de Compra de Lista <?=$vetor_tipo_moeda[$campos_lista[0]['tp_moeda']].'<b> ('.$campos_pa_da_lista[0]['referencia'].')</b>';?>:
        </td>
        <td>
            <input type='text' name="txt_preco_compra_lista_rs" id='txt_preco_compra_lista_rs<?=$i;?>' value="<?=number_format($preco_compra_lista, 2, ',', '.');?>" size="15" maxlength="20" class='textdisabled' disabled>
            <b> - <?=$vetor_forma_compra[$campos_lista[0]['forma_compra']].$mensagem_export;?></b>
        </td>
        <td>
            <input type='text' name="txt_preco_compra_lista_rs_comprar_como_export" id='txt_preco_compra_lista_rs_comprar_como_export<?=$i;?>' value="<?=number_format($preco_export, 2, ',', '.');?>" size="15" maxlength="20" class='textdisabled' <?=$visible_export;?> disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Preço de Compra p/ ML desejada <?=$vetor_tipo_moeda[$campos_lista[0]['tp_moeda']].'<b> ('.$campos_pa_da_lista[0]['referencia'].')</b>';?>:
        </td>
        <td>
            <input type='text' name="txt_preco_compra_ml_desejada_rs" id='txt_preco_compra_ml_desejada_rs<?=$i;?>' size="15" maxlength="20" class='textdisabled' disabled>
            <b> - <?=$vetor_forma_compra[$campos_lista[0]['forma_compra']].$mensagem_extra;?></b>
        </td>
        <td>
            <input type='text' name="txt_preco_compra_ml_desejada_rs_comprar_como_export" id='txt_preco_compra_ml_desejada_rs_comprar_como_export<?=$i;?>' size="15" maxlength="20" class='textdisabled' <?=$visible_export;?> disabled>
        </td>
    </tr>
        <?
            }
        ?>
	<tr class="linhacabecalho" align='center'>
            <td colspan='3'>
                <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_margem_lucro_desejada.focus()" id="cmd_redefinir" style="color:#ff9900;" class="botao">
            </td>
	</tr>
</table>
<Script Language = 'JavaScript'>
    calcular('<?=$i;?>', '<?=$acrescimo_acessorio;?>')
</Script>    
<?
        if($id_produto_insumo > 0) {//Só aparecerá esses campos se o PA do Orçamento for um PI ...
?>
    <!--********************Detalhes de Compras********************-->
    <br>
    <!--Esse parâmetro $exibir_pendencia_pedido_compras = 1, significa que o Sistema não precisa 
    as baixa(s) / manipulação(ões) do PI, somente as Pendências que é o que interessa pra vendas ...-->
    <center>
        <iframe id='detalhes_baixas_manipulacoes<?=$i;?>' width='970' height='580' frameborder='0'></iframe>
    </center>
    <!--Criei esse JS aqui pq dependia de alguns objetos que precisavam ser carregados + acima-->
    <Script Language = 'JavaScript'>
        carregar_iframe('<?=$i;?>', '<?=$id_produto_insumo;?>', '<?=$vetor_orcamento_venda_item[$i];?>', '<?=$_GET[id_fornecedor_prod_insumo];?>', '<?=$acrescimo_acessorio;?>')
    </Script>
<?
        }
?>
</form>
<hr/>
</body>
</html>
<?
    }
}
?>