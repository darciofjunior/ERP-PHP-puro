<?
require('../../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../../lib/menu/menu.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/genericas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/consultar.php', '../../../../');

function dados_bancarios($id_fornecedor) {
    $sql = "SELECT `id_fornecedor_propriedade`, `banco`, `agencia`, `num_cc`, `correntista` 
            FROM `fornecedores_propriedades` 
            WHERE `id_fornecedor` = '$id_fornecedor' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
        return $dados_bancarios = '&nbsp;';
    }else if($linhas == 1) {
?>
        <img src='../../../../imagem/cifrao.gif' width='20' onclick="nova_janela('../../../classes/fornecedor/dados_bancarios/pdf/dados_bancarios.php?id_fornecedor=<?=$id_fornecedor;?>&itens=1', 'RELATORIO', 'F')" height='20' alt='Visualizar Dado Bancário' border='0' style="cursor:hand">
        <a href="javascript:nova_janela('../../../classes/fornecedor/dados_bancarios/pdf/dados_bancarios.php?id_fornecedor=<?=$id_fornecedor;?>&itens=1', 'RELATORIO', 'F')"><b>DADOS BANCÁRIOS: Num. CC - <?=$campos[0]['num_cc'];?> | Agência - <?=$campos[0]['agencia'];?> | Banc. - <?=$campos[0]['banco'];?></a>
<?
    }else if ($linhas > 1) {
//Lista as várias Contas Bancárias do Fornecedor
?>
        <img src='../../../../imagem/cifrao.gif' width='20' height='20' alt='Visualizar Dados Bancários' border='0' onclick="nova_janela('../../../classes/fornecedor/dados_bancarios/pdf/dados_bancarios.php?id_fornecedor=<?=$id_fornecedor;?>&itens=1', 'RELATORIO', '', '', '', '', 500, 950, 'c', 'c', '', '', 's', 's', '', '', '')" style="cursor:hand">
        <a href="javascript:nova_janela('../../../classes/fornecedor/dados_bancarios/pdf/dados_bancarios.php?id_fornecedor=<?=$id_fornecedor;?>&itens=1', 'RELATORIO', '', '', '', '', 500, 950, 'c', 'c', '', '', 's', 's', '', '', '')">
        <b>Visualizar Dados Bancários</b></a>
<?
    }
}

//Busca o nome do Fornecedor com + detalhes alguns detalhes de dados do pedido
$sql = "SELECT e.`razaosocial` AS empresa, f.`id_fornecedor`, f.`id_pais`, f.`razaosocial`, 
        f.`optante_simples_nacional`, p.`id_tipo_moeda`, p.`vendedor`, p.`desc_ddl`, p.`desconto_especial_porc`, 
        p.`prazo_entrega`, p.`prazo_navio`, p.`tipo_nota`, p.`tipo_nota_porc`, p.`tipo_export`, 
        p.`id_funcionario_cotado`, p.`programado_descontabilizado`, p.`data_emissao`, 
        CONCAT(tm.`simbolo`, ' ') AS moeda 
        FROM `pedidos` p 
        INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = p.`id_tipo_moeda` 
        WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
$campos                     = bancos::sql($sql);
$id_tipo_moeda              = $campos[0]['id_tipo_moeda'];
$empresa                    = $campos[0]['empresa'];
$id_fornecedor              = $campos[0]['id_fornecedor'];
$id_pais                    = $campos[0]['id_pais'];
$optante_simples_nacional   = $campos[0]['optante_simples_nacional'];
$razao_social               = $campos[0]['razaosocial'];
$moeda                      = $campos[0]['moeda'];
$vendedor                   = $campos[0]['vendedor'];
$desc_ddl                   = $campos[0]['desc_ddl'];
$desconto_especial_porc     = number_format($campos[0]['desconto_especial_porc'], 2, ',', '.');
$prazo_entrega              = data::datetodata($campos[0]['prazo_entrega'], '/');
$prazo_navio                = $campos[0]['prazo_navio'];
$tipo_nota                  = $campos[0]['tipo_nota'];
$tipo_nota_porc             = $campos[0]['tipo_nota_porc'];
if($campos[0]['tipo_export'] == 'E') {
    $tipo_export = 'Exp';
}else if($campos[0]['tipo_export'] == 'I') {
    $tipo_export = 'Imp';
}else if($campos[0]['tipo_export'] == 'N') {
    $tipo_export = 'Nac';
}
$condicao_ddl           = $desc_ddl.' - '.$tipo_export;
$id_funcionario_cotado  = $campos[0]['id_funcionario_cotado'];
if(!empty($id_funcionario_cotado)) {
    $sql = "SELECT nome 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$id_funcionario_cotado' LIMIT 1 ";
    $campos_func 	= bancos::sql($sql);
    $comprador      = $campos_func[0]['nome'];
}
$programado_descontabilizado    = $campos[0]['programado_descontabilizado'];
$data_emissao                   = data::datetodata($campos[0]['data_emissao'], '/');
/*********************************Importação*********************************/
//Significa que esse Pedido é uma Importação ...
if($id_pais != 31) {
    $data_emissao_para_funcao   = substr($campos[0]['data_emissao'], 0, 10);
    $prazo_entrega_para_funcao  = $campos[0]['prazo_entrega'];
    $prazo_entrega_funcao       = data::diferenca_data($data_emissao_para_funcao, $prazo_entrega_para_funcao);
    $entrega                    = $prazo_entrega_funcao[0];
    //Aqui eu atualizo a importação do Pedido caso exista alguma ...
    //Aqui adiciona os 3 dias padrão + o valor do navio + o prazo de entrega
    $soma_prazo = 3 + (integer)$prazo_navio + (integer)$entrega;
    //Aqui soma a data de emissão mais a somatória de prazos
    compras_new::atualizar_importacao($id_pedido, $soma_prazo);
}
/****************************************************************************/
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function igualar(indice) {
    var controle = 0, existe = 0, liberado = '', codigo = '', cont = 0
    var elemento = '', objeto = ''
    for(i = 0; i < parent.itens.document.form.elements.length; i++) {
        if(parent.itens.document.form.elements[i].type == 'radio') cont ++
    }
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'hidden' && document.form.elements[i].name == 'opt_item') existe ++
    }
    if(cont > 1) {
        elemento = parent.itens.document.form.opt_item[indice].value
        objeto = parent.itens.document.form.opt_item[indice]
    }else {
        if(existe == 0) {
            elemento = parent.itens.document.form.opt_item.value
            objeto = parent.itens.document.form.opt_item
        }else {
            elemento = parent.itens.document.form.opt_item[indice].value
            objeto = parent.itens.document.form.opt_item[indice]
        }
    }
    if(objeto.type == 'radio') {
        for(i = 0; i < elemento.length; i ++) {
            if(elemento.charAt(i) == '|') {
                controle ++
            }else {
                if(controle == 1) {
                    liberado = liberado + elemento.charAt(i)
                }else {
                    codigo = codigo + elemento.charAt(i)
                }
            }
        }
        parent.itens.document.form.opt_item_principal.value = codigo
    }else {
        limpar_radio()
    }
}

function limpar_radio() {
    for(i = 0; i < parent.itens.document.form.elements.length; i++) {
        if(parent.itens.document.form.elements[i].type == 'radio') parent.itens.document.form.elements[i].checked = false
    }
}

function visualizar_entrada(valor, id_nfe, id_item_pedido) {
    if(valor == 1) {//Visualização de entradas por Item de Pedido
        nova_janela('visualizar_entradas.php?id_nfe='+id_nfe+'&id_item_pedido='+id_item_pedido, 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {//Visualização de entradas por Pedido
        nova_janela('visualizar_entradas.php?passo=1&id_pedido=<?=$id_pedido;?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form name='form'>
<!--Dados de Fornecedor do Pedido-->
<table width='90%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            <font size='3'>
                Pedido N.º 
                <font color='yellow'>
                    <?=$id_pedido;?>
                </font>
                - Empresa:
                <font color='yellow'>
                    <?=$empresa;?>
                </font>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' style='cursor:pointer'>
        <td align='left'>
            <a href="javascript:nova_janela('../../../classes/fornecedor/alterar.php?passo=1&id_fornecedor=<?=$id_fornecedor;?>&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <font color='yellow' size='-1'>
                    Fornecedor:
                    <font color="#FFFFFF" size='-1'>
                        <?=$razao_social;?>
                    </font>
                </font>
                <img src = '../../../../imagem/propriedades.png' title='Detalhes de Cliente' alt='Detalhes de Cliente' style='cursor:pointer' border='0'>
            </a>
            <?
                if($optante_simples_nacional == 'S') {
            ?>
                    <font color="darkgreen" size="2"> (Optante pelo Simples Nacional)</font>
            <?
                }
            ?>
        </td>
    </tr>
</table>
<!--Pré-Cabeçalho de Pedido-->
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center'>
    <tr class='linhacabecalho'>
<?
//Retorna a Qtde Antecipação(ões) existente(s) em Pedido
        $sql = "SELECT COUNT(`id_antecipacao`) AS total_antecipacoes 
                FROM `antecipacoes` 
                WHERE `id_pedido` = '$id_pedido' ";
        $campos_antecipacoes = bancos::sql($sql);
        if($campos_antecipacoes[0]['total_antecipacoes'] > 0) {
            $total_antecipacoes = $campos_antecipacoes[0]['total_antecipacoes'];
        }else {
            $total_antecipacoes = 0;
        }
?>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
                TOTAL DE ANTECIPAÇÃO: 
                <a href="javascript:nova_janela('../antecipacoes.php?id_pedido=<?=$id_pedido;?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Antecipações de Pedidos'  style='cursor:help' class='link'>
                    <font color='yellow' size='2'>
                        <?=$total_antecipacoes;?>
                        <img src = '../../../../imagem/bloco_verde.gif' width='8' height='8'>
                    </font>
                </a>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
                <b>DATA EMISSÃO: <font color='yellow'><?=$data_emissao;?></font></b>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
                <?
//Se for Internacional
                    if($id_pais != 31) {
                        $rotulo = 'DATA EMBARQUE: ';
//Se for Nacional
                    }else {
                        $rotulo = 'DATA ENTREGA: ';
                    }
                ?>
                <b><?=$rotulo;?><font color='yellow'><?=$prazo_entrega;?></font></b>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
                <b>CONDIÇÃO: 
                <font color='yellow'>
                <?
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Financiamento que foi feitas p/ o Pedido ...
                    $sql = "Select pf.*, tm.simbolo 
                                    from pedidos_financiamentos pf 
                                    inner join pedidos p on p.id_pedido = pf.id_pedido 
                                    inner join tipos_moedas tm on tm.id_tipo_moeda = p.id_tipo_moeda 
                                    where pf.id_pedido = '$id_pedido' order by pf.dias asc ";
                    $campos_financiamento = bancos::sql($sql);
                    $linhas_financiamento = count($campos_financiamento);
                    if($linhas_financiamento > 0) {//Se foi encontrado pelo menos 1 Financiamento ...
                        for($i = 0; $i < $linhas_financiamento; $i++) {
                            if($i == 0) {//Se eu estiver na Primeira parcela
                                $primeira_parcela = $campos_financiamento[$i]['dias'];
                            }else if($i + 1 == $linhas_financiamento) {//Última Parcela
                                $ultima_parcela = $campos_financiamento[$i]['dias'];
                            }
                        }
                        if($tipo_nota == 1) {//NF
                            $exibir_nota = 'NF';
                        }else {//SGD
                            $exibir_nota = 'SGD';
                        }
                        $condicao_ddl = $linhas_financiamento.' parc. ('.$primeira_parcela.' à '.$ultima_parcela.' DDL) '.$exibir_nota.' '.$tipo_nota_porc.' % - '.$tipo_export;
                    }
                    echo $condicao_ddl;
                ?>
                </font></b>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
                COMPRADOR(A): <font color='yellow'><?=$comprador;?></font>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
                VENDEDOR: <font color='yellow'><?=$vendedor;?></font>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='3'>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
                <b>DESCONTO ESPECIAL P/ ESTE PEDIDO: <font color='yellow'><?=$desconto_especial_porc.' %';?></font></b>
            </font>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='3'>
            <iframe name='detalhes' id='detalhes' src = '/erp/albafer/modulo/classes/follow_ups/detalhes.php?identificacao=<?=$id_pedido;?>&origem=16' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
<?
/************************Somente para Países Internacionais************************/
//Se for Internacional
    if($id_pais != 31) {
?>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
                Prazo de Viagem do Navio: <font color='yellow'><?=$prazo_navio;?></font>
            </font>
        </td>
        <td colspan='2'>
<?
//Busca do Nome da Importação desse Pedido
            $sql = "SELECT nome 
                    FROM `importacoes` i 
                    INNER JOIN `pedidos` p ON p.id_importacao = i.id_importacao 
                    WHERE p.`id_pedido` = '$id_pedido' ";
            $campos_importacao = bancos::sql($sql);
?>
            <font face='Verdana, Arial, Helvetica, sans-serif' color='#FFFFFF' size='2'>
                IMPORTAÇÃO: <font color='yellow'><?=$campos_importacao[0]['nome'];?></font>
            </font>
        </td>
    </tr>
<?
    }
/************************************************************************************/
?>
</table>
<?
/**********************************************************************************************/
//Esse controle eu vou utilizar um pouco mais mostrar um descritivo + abaixo
//Se este Pedido estiver atrelado a uma OS, então eu Mostro um Descritivo a + no fim dos Itens
	$sql = "Select id_os 
                from oss 
                where id_pedido = '$id_pedido' limit 1 ";
	$campos2 = bancos::sql($sql);
	if(count($campos2) == 1) {//Está importado p/ OS
            $id_os = $campos2[0]['id_os'];
            $tem_os_importada = 1;
	}else {//Ainda não está importado p/ OS
            $tem_os_importada = 0;
	}
/**********************************************************************************************/
//Aqui começa a segunda parte em q exibe os itens de Pedido
	$sql = "SELECT g.`referencia`, ip.*, pi.id_produto_insumo, pi.discriminacao, u.sigla 
                FROM `itens_pedidos` ip 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                WHERE ip.`id_pedido` = '$id_pedido' ORDER BY pi.discriminacao, ip.id_item_pedido ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);       
//Verifica se tem pelo menos um item de Pedido
	if($linhas > 0) {
?>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
	<tr></tr>
	<tr></tr>
	<tr class='linhanormal' align='center'>
		<td bgcolor='#CECECE'><b>Itens</b></td>
		<td bgcolor='#CECECE'><b>Qtde Solic</b></td>
                <td bgcolor='#CECECE'><b>Qtde Receb</b></td>
                <td bgcolor='#CECECE'><b>Qtde Rest</b></td>
		<td bgcolor='#CECECE' style='cursor:help'>
                    <b>EC Atrel / EC Estim</b>
                    <img src='../../../../imagem/bloco_negro.gif' title='EC Est = EC Atrel - 0,5 MMV Atrel (Limite Qtde do Pedido)' style='cursor:help' width='7' height='7' border='0'>
                </td>
                <td bgcolor='#CECECE' style='cursor:help'>
                    <b>MMV Atrel</b>
                </td>
		<td bgcolor='#CECECE'><b>Un</b></td>
		<td bgcolor='#CECECE'><b>Produto</b></td>
		<td bgcolor='#CECECE'><b>Preço Unit.</b></td>
		<td bgcolor='#CECECE'><b>Valor Total</b></td>
		<td bgcolor='#CECECE'><b>Ipi %</b></td>
		<td bgcolor='#CECECE'><b>Valor c/ IPI</b></td>
		<td bgcolor='#CECECE'><b>Marca / Obs</b></td>
	</tr>
<?
//Utilizo essa variável para fazer o cálculo Total em Kilo ...
			$total_qtde             = 0;
			$valor_total_ipi        = 0;
			$achou_aco              = 0;
			$aco_trefilado          = 0;
                        $total_em_falta_item    = 0;
			for($i = 0; $i < $linhas; $i++) {
/*Verifica se tem algum produto do tipo aço, para poder saber se exibe a mensagem
mais abaixo ou não*/
				if($achou_aco != 1) {
                                    $sql = "SELECT `id_produto_insumo_vs_aco` 
                                            FROM `produtos_insumos_vs_acos` 
                                            WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                                    $campos2 = bancos::sql($sql);
                                    if(count($campos2) == 1) {
                                        $achou_aco = 1;
                                    }
				}
//Zero essa variável p/ não herdar o valor do Loop anterior ...
				$pipa = 0;
?>
	<tr class='linhanormal' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td>
<?
                                //Se o Item estiver "Em Aberto" ou Parcialmente Importado em Nota Fiscal, ainda posso alterar dados ...
				if($campos[$i]['status'] == 0 || $campos[$i]['status'] == 1) {
?>
			<input type='radio' name='opt_item' value='<?=$campos[$i]['id_item_pedido'];?>' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')">
<?
                                    if($campos[$i]['status'] == 1) echo '<font color="blue">PARCIAL</font>';
				}else if($campos[$i]['status'] == 2) {
?>
			<font color='red'>
                            TOTAL
                        </font>
                        <input type='hidden' name='opt_item'>
<?
				}
?>
		</td>
		<td align='right'>
		<?
                    echo number_format($campos[$i]['qtde'], 2, ',', '.');
                    $total_qtde+= $campos[$i]['qtde'];
		?>
		</td>
                <td align='right'>
		<?
                    //Verifica em qual Nota que está o Item de Pedido de Compras Corrente
                    $sql = "SELECT nfeh.`id_nfe` 
                            FROM `itens_pedidos` ip 
                            INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` 
                            WHERE ip.`id_item_pedido` = ".$campos[$i]['id_item_pedido']." LIMIT 1 ";
                    $campos_nfe = bancos::sql($sql);
                    $id_nfe     = $campos_nfe[0]['id_nfe'];
//Busca o Total já entregue daquele Item de Pedido de Compras Corrente
                    $sql = "SELECT SUM(`qtde_entregue`) AS total_entregue 
                            FROM `nfe_historicos` 
                            WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' ";
                    $campos_nfe     = bancos::sql($sql);
                    $total_entregue = $campos_nfe[0]['total_entregue'];
                    if($total_entregue != 0) {
		?>
                        <a href="javascript:visualizar_entrada(1, '<?=$id_nfe;?>', '<?=$campos[$i]['id_item_pedido'];?>')" title="Visualizar Entrada(s) por Item de Pedido" class="link">
                            <?=number_format($total_entregue, '2', ',', '.');?>
                        </a>
		<?
                    }else {
                        echo number_format($total_entregue, '2', ',', '.');
                    }
		?>
		</td>
                <td align='right'>
		<?
			$total_restante = $campos[$i]['qtde'] - $total_entregue;
			if($total_restante > 0) {
                            echo number_format($total_restante, '2', ',', '.');
			}else {
                            echo '&nbsp;';
			}
		?>
		</td>
		<td>
		<?
//Aki vejo se o PI tem relação com o PA ...
                    $sql = "SELECT `id_produto_acabado` 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                    $campos_pipa = bancos::sql($sql);
                    if(count($campos_pipa) == 1) {
                        $pipa = 1;//Variável utilizada mais abaixo ...
                        //Aqui eu busco a Qtde de Estoque Comprometido do PA ...
                        $vetor          = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_pipa[0]['id_produto_acabado']);
                        $vetor_valores  = vendas::preco_venda($campos_pipa[0]['id_produto_acabado']);
		?>
			<a href="javascript:nova_janela('../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos_pipa[0]['id_produto_acabado'];?>', 'DETALHES_ESTOQUE', '', '', '', '', 500, 960, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
			<?
                            /*Tudo isso é para saber se vale a pena tirar a Importação do Porto, p/ podermos 
                            faturar bastante, por isso que vai se deixando acumular as pendências, tudo por 
                            causa dos juros que se pagam aos Bancos ...*/
                            $qtde_estoque_estim = $vetor['total_ec_pas_atrelados'] - 0.5 * $vetor['total_mmv_pas_atrelados'];
                            if($qtde_estoque_estim < 0) {
                                //Se a Qtde do Estoque for menor do que a Qtde Pedido ...
                                if(-$qtde_estoque_estim > $campos[$i]['qtde']) $qtde_estoque_estim = -$campos[$i]['qtde'];//Porque a Qtde toda do Pedido está em Falta ...
                                //Aqui faz um Somatório do Total de Falta de todos os PA(s) que estão em negligência ...
                                $total_em_falta_item+= ($qtde_estoque_estim * $vetor_valores['preco_venda_medio_rs']);

                                echo '<font color="red">'.number_format($vetor['total_ec_pas_atrelados'], 1, ',', '.').' / '.number_format($qtde_estoque_estim, 1, ',', '.').'</font>';
                            }else {
                                echo number_format($vetor['total_ec_pas_atrelados'], 1, ',', '.').' / '.number_format($qtde_estoque_estim, 1, ',', '.');
                            }
			?>
			</a>
		<?
                    }else {
                        /*Quando o Fornecedor = 'Great' e a Referência = 'BLANK' seguirá o caminho 
                        para que saibamos as Faltas desse PA que utiliza esse 'BLANK' ...*/
                        if($id_fornecedor == 13 && $campos[$i]['referencia'] == 'BLANK') {
                            //Verifico se este PI que é BLANK está atrelado na 3ª Etapa de algum PA do Custo ...
                            $sql = "SELECT pac.`id_produto_acabado`, pa.`referencia` 
                                    FROM `pacs_vs_pis` pp 
                                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`ativo` = '1' 
                                    WHERE pp.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                            $campos_etapa3 = bancos::sql($sql);
                            if(count($campos_etapa3) == 1) {//Encontrou atrelamento na 3ª Etapa de um PA ...
                                //Aqui eu busco a Qtde de Estoque Comprometido do PA ...
                                $vetor          = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_etapa3[0]['id_produto_acabado']);
                                $vetor_valores  = vendas::preco_venda($campos_etapa3[0]['id_produto_acabado']);
                ?>
                            <a href="javascript:nova_janela('../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos_etapa3[0]['id_produto_acabado'];?>', 'DETALHES_ESTOQUE', '', '', '', '', 500, 960, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
                            <?
                                /*Tudo isso é para saber se vale a pena tirar a Importação do Porto, p/ podermos 
                                faturar bastante, por isso que vai se deixando acumular as pendências, tudo por 
                                causa dos juros que se pagam aos Bancos ...*/
                                $qtde_estoque_estim = $vetor['total_ec_pas_atrelados'] - 0.5 * $vetor['total_mmv_pas_atrelados'];
                                if($qtde_estoque_estim < 0) {
                                    //Se a Qtde do Estoque for menor do que a Qtde Pedido ...
                                    if(-$qtde_estoque_estim > $campos[$i]['qtde']) $qtde_estoque_estim = -$campos[$i]['qtde'];//Porque a Qtde toda do Pedido está em Falta ...
                                    //Aqui faz um Somatório do Total de Falta de todos os PA(s) que estão em negligência ...
                                    $total_em_falta_item+= ($qtde_estoque_estim * $vetor_valores['preco_venda_medio_rs']);

                                    echo '<font color="red">'.number_format($vetor['total_ec_pas_atrelados'], 1, ',', '.').' / '.number_format($qtde_estoque_estim, 1, ',', '.').'</font>';
                                }else {
                                    echo number_format($vetor['total_ec_pas_atrelados'], 1, ',', '.').' / '.number_format($qtde_estoque_estim, 1, ',', '.');
                                }
                            }
                ?>
                            </a>
                <?
                            echo '<font title="Ref. usada no Total em Falta" style="cursor:help"> ('.$campos_etapa3[0]['referencia'].')</font>';
                        }else {
//Aqui eu busco a Qtde de Estoque do PI ...
                            $sql = "SELECT `qtde` 
                                    FROM `estoques_insumos` 
                                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                            $campos_pi = bancos::sql($sql);
                ?>
			<a href="javascript:nova_janela('../../../classes/produtos_insumos/detalhes_producao.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'DETALHES_ESTOQUE', '', '', '', '', 500, 960, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Produção" class="link">
                <?
                            if($campos_pi[0]['qtde'] < 0) {
                                echo '<font color="red"><b>'.number_format($campos_pi[0]['qtde'], 2, ',', '.').'</b></font>';
                            }else {
                                echo number_format($campos_pi[0]['qtde'], 2, ',', '.');
                            }
                        }
                    }
		?>
                    </a>
		</td>
                <td>
                <?
                    /*Se 
                     
                    1) o PI tem relação com o PA ou ...;

                    2) Quando o Fornecedor = 'Great' e a Referência = 'BLANK' seguirá o caminho 
                    para que saibamos as Faltas desse PA que utiliza esse 'BLANK' ...*/
                    if(count($campos_pipa) == 1 || ($id_fornecedor == 13 && $campos[$i]['referencia'] == 'BLANK')) {
                        echo number_format($vetor['total_mmv_pas_atrelados'], 1, ',', '.');
                    }
                ?>
                </td>
		<td align='left'>
                    <?=$campos[$i]['sigla'];?>
		</td>
		<td align='left'>
		<?
				if($pipa == 1) {//Se o PI tem relação com o PA ...
		?>
                                    <a href="javascript:nova_janela('../alterar_prazo_entrega.php?id_produto_acabado=<?=$campos_pipa[0]['id_produto_acabado'];?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Prazo de Entrega' class='link'>
		<?
				}else {
                ?>
                    <!--Eu também levo o parâmetro de pop_up igual a 1, p/ q o Sistema não abra esse arquivo como sendo uma 
                    Tela Normal, evitando erro de redirecionamento da Tela, após a atualização dos dados do Produto Insumo-->
                                    <a href="javascript:nova_janela('../../produtos/alterar.php?passo=1&id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&pop_up=1', 'pop', '', '', '', '', '620', '980', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?
                                }
				$referencia = genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia'], 0);
/*Aqui eu verifico qual é o Tipo de Produto que estou utilizando, isso porque se for aço 1020 ou 1045
tenho que estar apresentando uma mensagem a mais no fim do Pedido*/
				$discriminacao_corrent = strtok(strchr($campos[$i]['discriminacao'], 'TREFILADO'), 'I');
				if($referencia == 'ACO' && $discriminacao_corrent == 'TREF') {
                                    $aco_trefilado++;
				}
//Apresentado o Produto normalmente ...
				echo $referencia.' * '.$campos[$i]['discriminacao'];
				
//Significa que é um Produto do Tipo não Estocável
				if($campos[$i]['estocar'] == 0) {
//Se eu não vou estocar, esse Produto, então significa que este vai para alguém, então busco p/ qual fornec
					if($campos[$i]['id_fornecedor_terceiro'] != 0) {
//Busca o nome do Fornecedor que deve ser cobrado
						$sql = "SELECT `razaosocial` 
                                                        FROM `fornecedores` 
                                                        WHERE `id_fornecedor` = '".$campos[$i]['id_fornecedor_terceiro']."' LIMIT 1 ";
						$campos2 = bancos::sql($sql);
					}
					echo "<font color='red' title='Não Estocar - Enviar p/: ".$campos2[0]['razaosocial']."' style='cursor:help'><b> (N.E) </b></font>";
				}
//Significa que esse Produto tem débito com Fornecedor
				if($campos[$i]['id_fornecedor'] != 0) {
//Busca o nome do Fornecedor que deve ser cobrado
					$sql = "SELECT `razaosocial` 
                                                FROM `fornecedores` 
                                                WHERE `id_fornecedor` = ".$campos[$i]['id_fornecedor']." LIMIT 1 ";
					$campos2 = bancos::sql($sql);
					echo "<font color='red' title='Debitar do(a): ".$campos2[0]['razaosocial']."' style='cursor:help'><b> (DEB) </b></font>";
				}
//Aqui eu verifico qual que é o PA referente a esse PI devido, esse Pedido ser atrelado a uma OS
				if($tem_os_importada == 1) {
					$sql = "SELECT pa.`id_produto_acabado`, pa.`referencia` 
                                                FROM `ops` 
                                                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
                                                INNER JOIN `oss_itens` oi ON oi.`id_op` = ops.`id_op` AND oi.`id_item_pedido` = ".$campos[$i]['id_item_pedido']." ";
					$campos2 = bancos::sql($sql);
//Produto Normal
					if($campos2[0]['referencia'] != 'ESP') {
		?>
				<font color="darkblue">
					<?=' / '.intermodular::pa_discriminacao($campos_pipa[0]['id_produto_acabado']);?>
				</font>
		<?
					}else {
//Quando o Produto Acabado for ESP printa em verde
		?>
				<font color="green">
					<?=' / '.intermodular::pa_discriminacao($campos_pipa[0]['id_produto_acabado']);?>
				</font>
		<?
					}
				}
			?>
			</a>
				&nbsp;
			<a href="javascript:nova_janela('../../estoque_i_c/detalhes.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'POP', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes da Última Compra" class="link">
                            <img src = '../../../../imagem/visualizar_detalhes.png' title='Detalhes da Última Compra' alt='Detalhes da Última Compra' border='0'>
			</a>
			<?
                            if($campos[$i]['desconto_especial'] == 'S') echo '<font color="red" title="Desconto Especial" style="cursor:help"><b>(D.E.)</b></font>';
                            if($pipa == 1) {//Se o PI tem relação com o PA ...
                        ?>
                            &nbsp;
                            <a href="javascript:nova_janela('../../../vendas/estoque_acabado/detalhes.php?id_produto_acabado=<?=$campos_pipa[0]['id_produto_acabado'];?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class="link">
                                <img src="../../../../imagem/detalhes.png" title="Detalhes" alt="Detalhes" width='20' height='20' border='0'>
                            </a>
                        <?
                            }
                        ?>
		</td>
		<td align='right'>
		<?
				if($campos[$i]['desconto_especial'] == 'S') {
		?>
				<font title="Preço Promocional" color="blue">
		<?
					echo $moeda.number_format($campos[$i]['preco_unitario'], 2, ',', '.');
		?>
				</font>
		<?
				}else {
					echo $moeda.number_format($campos[$i]['preco_unitario'], 2, ',', '.');
				}
		?>
		</td>
		<td align='right'>
		<?
                    $valor_total_produto_item = $campos[$i]['qtde'] * $campos[$i]['preco_unitario'];
                    echo $moeda.number_format($valor_total_produto_item, 2, ',', '.');
                    $valor_total_produtos+= $valor_total_produto_item;
		?>
		</td>
		<td align='right'>
		<?
                    if($campos[$i]['ipi_incluso'] == 'S') {
                        echo '<font color="red" title="IPI Incluso de '.number_format($campos[$i]['ipi'], 2, ',', '.').' %" style="cursor:help"><b>(Incl)</b></font>';
                    }else {
                        if(($campos[$i]['ipi'] == 0) or ($tipo_nota == 2)) {//SGD
                            echo '&nbsp;';
                        }else {//NF
                            echo $campos[$i]['ipi'];
                        }
                    }
		?>
		</td>
		<td align='right'>
		<?
                    if($tipo_nota == 2) {//SGD
                        $ipi = 0;
                    }else {//NF
                        $ipi = $campos[$i]['ipi'];
                    }
                    
                    if($campos[$i]['ipi_incluso'] == 'S') {
                        $valor_ipi_incluso_item = ($valor_total_produto_item * $ipi) / 100;
                        echo $moeda.number_format($valor_ipi_incluso_item, 2, ',', '.');
                        $valor_ipi_incluso+= $valor_ipi_incluso_item;
                    }else {
                        $valor_ipi_item = ($valor_total_produto_item * $ipi) / 100;
                        echo $moeda.number_format($valor_ipi_item, 2, ',', '.');
                        $valor_ipi+= $valor_ipi_item;
                    }
		?>
		</td>
		<td align='left'>
		<?
                    if(!empty($campos[$i]['marca'])) {
                        echo $campos[$i]['marca'];
                    }else {
                        echo '&nbsp';
                    }
		?>
		</td>
	</tr>
<?
                            /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
                            encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
                            da Biblioteca de Custos, na função pas_atrelados ...*/
                            unset($vetor_pas_atrelados);
			}
			//Sempre que o pedido possuir essa marcação no Cabeçalho ...
			if($programado_descontabilizado == 'S') {
?>
    <tr class='atencao' align='center'>
        <td class='atencao' colspan='13'>
            <font color='red' size='5'><b>PEDIDO PROGRAMADO DESCONTABILIZADO</b></font>
        </td>
    </tr>
<?
			}
/*Significa que achou algum aço 1020 ou 1045 no Pedido, sendo assim tenho que exibir essa mensagem de 
alerta para que não seje cortada errada as barras ...*/
			if($aco_trefilado > 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='13' class='linhadestaque'>
            <font size='2'>
                <b> * NÃO RECEBEMOS BARRAS FORA DO COMPRIMENTO ESPECIFICADO (2,80 À 3,10 MTS) * </b>
            </font>
        </td>
    </tr>
<?
			}
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='13' class='linhadestaque'>
<?
        if($achou_aco == 1) {
?>
            <font size='1'>
                <b>* NÃO RECEBEMOS BARRAS C/ DIAM > 69,85 mm E PESO > 150 KGs. NESTES CASOS, CORTAR AO MEIO. ACEITAMOS ATÉ +5% DA QTDE PEDIDA. *</b>
            </font>
<?
        }else {
            echo '&nbsp;';
        }
?>
        </td>
    </tr>
    <tr align='right'>
        <td colspan='6' class='linhacabecalho'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>FRETE QTD KGS P/ CÁLCULO TOTAL/FRETE -></b>
            </font>
        </td>
        <td colspan='2' class='linhacabecalho' align='center'>
            <input type='button' name='cmd_visualizar_entrada' value='Visualizar Todas as Entradas' onclick='visualizar_entrada(2)' class='botao'>
        </td>
        <td colspan='5' class='linhacabecalho'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b><?=number_format($total_qtde, 2, ',', '.');?> / KG-TOT</b>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='6' class='linhadestaque' align='right'>
            <font color='yellow'>
                <?
                    /******************************************************/
                    if($id_fornecedor == 13) {//Só será exibida essa linha p/ o Fornecedor Great ...
                        $valor_dolar_custo  = genericas::variavel(7);
                        $valor_dolar_euro   = genericas::variavel(8);
                        $total_em_falta     = $total_em_falta_item;

                        if($id_tipo_moeda == 1) {//Se a Moeda do Pedido = 'R$' ...
                            $numerario      = $valor_total_produtos * (genericas::variavel(1) - 1);
                        }else if($id_tipo_moeda == 2) {//Se a Moeda do Pedido = 'Dólar' ...
                            $numerario      = $valor_total_produtos * (genericas::variavel(1) - 1) * $valor_dolar_custo;
                        }else if($id_tipo_moeda == 3) {//Se a Moeda do Pedido = 'Euro' ...
                            $numerario      = $valor_total_produtos * (genericas::variavel(1) - 1) * $valor_euro_custo;
                        }
                ?>
                        <font title='Total em Falta = P.Unit * Qtde Falta * Fat Imp * Fator ML Venda * Cambio' style='cursor:help'>
                            TOTAL EM FALTA:
                        </font>
                        <?='R$ '.number_format($total_em_falta, 2, ',', '.');?>
                        <br/>
                        <font title='Numerário = Total * (Fat Imp - 1) * Cambio' style='cursor:help'>
                            NUMERÁRIO: 
                        </font>
                        <?='R$ '.number_format($numerario, 2, ',', '.');
                    }
                    /******************************************************/
                ?>
            </font>
        </td>
        <td colspan='7' class='linhadestaque'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
//Já está importado para OS, então mostra esse descritivo
                if($tem_os_importada == 1) {
            ?>
                    <font size='2' color='red'>
                        <b>* HÁ UMA O.S. IMPORTADA NESTE PEDIDO.</b><br>
                    </font>
            <?
                }
                echo dados_bancarios($id_fornecedor);
            ?>
            </font>
        </td>
    </tr>
</table>    
<table width='90%' border='0' cellspacing='1' cellpadding='0' align='center'>
    <tr></tr>
    <tr></tr>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color='yellow'>
                    C&Aacute;LCULO DO IMPOSTO
                </font>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color="yellow">VALOR DO IPI: </font>
                <br><?=$tipo_moeda.number_format($valor_ipi, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color="yellow">VALOR TOTAL DOS PRODUTOS: </font>
                <br/><?=$tipo_moeda.number_format($valor_total_produtos, 2, ',', '.');?></td>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <font color="yellow">VALOR TOTAL DO PEDIDO: </font>
                <br/>
                <?
                    compras_new::valor_pendencia($id_pedido);//Aproveito p/ atualizar a Pendência do Pedido ...
                    echo $moeda.number_format($valor_ipi + $valor_total_produtos, 2, ',', '.');
                    //Sempre que carregar essa tela guarda esse valor Total do Pedido ...
                    $sql = "UPDATE `pedidos` SET `valor_ped` = '".($valor_total_produtos)."' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
                    bancos::sql($sql);
                ?>
            </font>
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='0' align='center'>    
<?
    if($valor_ipi_incluso > '0') {
        $valor_ipi_incluso/= 2;//Essa divisão por 2 é porque só temos direito de creditar 50% quando o IPI é Incluso ...
?>
    <tr class='linhacabecalho'>
        <td colspan='13'>
            <b><font color='red' size='4'>TOTAL IPI INCLUSO À CREDITAR: <?=$moeda.number_format(round(round($valor_ipi_incluso, 3), 2), 2, ',', '.');?></font></b>
            <br/>
        </td>
    </tr>
<?
    }
?>
    <tr class='confirmacao' align='center'>
        <td colspan='13'>
            <font size='4'>
                Total de Registro(s): <?=$linhas;?>
            </font>
        </td>
    </tr>
</table>
<!--Não me lembro desses hiddens aki (rsrs)-->
<input type='hidden' name='opt_item'>
<input type='hidden' name='opt_item_principal'>
<!-- ******************************************** -->
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
</form>
</body>
</html>
<?
        if(!empty($valor)) {
?>
        <Script Language = 'Javascript'>
            alert('<?=$mensagem[$valor];?>')
        </Script>
<?
        }
    }else {
?>
<html>
<body>
<form name='form'>
<table width='90%' border='0' align='center'>
    <tr class='atencao'>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='#FF0000'>
                <b>Pedido
                    <font face='Verdana, Arial, Helvetica, sans-serif' size='-1' color='blue'>
                        <?=$id_pedido;?>
                    </font>
                    n&atilde;o cont&eacute;m itens cadastrado.
                </b>
            </font>
        </td>
    </tr>
</table>
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
</form>
</body>
</html>
<?
    if(!empty($valor)) {
?>
        <Script Language = 'JavaScript'>
            alert('<?=$mensagem[$valor];?>')
        </Script>
<?
    }
    compras_new::valor_pendencia($id_pedido);//Aproveito p/ atualizar a Pendência do Pedido ...
    //Como não existe valor de Pedido nesse caso, então guardo o Valor Zero p/ esses casos ...
    $sql = "UPDATE `pedidos` SET `valor_ped` = '0' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
    bancos::sql($sql);
}
?>