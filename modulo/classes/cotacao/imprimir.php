<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/estoque_new.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/nivel_estoque/index.php', '../../../');

if(!empty($_GET['new_tipo_compra'])) {//Aqui eu mudo o Tipo de Cotação, toda vez que o usuário alterar no Link ...
    $sql = "UPDATE `cotacoes` SET `tipo_compra` = '$_GET[new_tipo_compra]' WHERE `id_cotacao` = '$_GET[id_cotacao]' LIMIT 1 ";
    bancos::sql($sql);
}

//Busca de Algumas Variáveis que serão utilizadas mais abaixo ...
$nivel_baixo 		= genericas::variavel(2);
$nivel_alto 		= genericas::variavel(3);

//O campo "qtde_metros" da Cot. no momento não está sendo utilizado para nada, talvez pode ser apagado ...

//Busca os Dados de Itens da Cotação ...
$sql = "SELECT ci.`neces_compra_prod`, ci.`cmm_mmv_total`, ci.`qtde_pedida`, ci.`qtde_producao`, 
        ci.`qtde_estoque`, ci.`mlm`, g.`referencia`, pi.`id_produto_insumo`, pi.`qtde_estoque_pi`, 
        pi.`unidade_conversao`, pi.`discriminacao`, pi.`estoque_mensal`, pi.`prazo_entrega`, 
        (((pi.`prazo_entrega` / 30) + $nivel_baixo) * pi.`estoque_mensal`) AS estoque_critico_baixo, (((pi.`prazo_entrega` / 30) + $nivel_alto) * pi.`estoque_mensal`) AS estoque_critico_alto, 
        u.`sigla` 
        FROM `cotacoes` c 
        INNER JOIN `cotacoes_itens` ci ON ci.`id_cotacao` = c.`id_cotacao` 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ci.`id_produto_insumo` AND pi.`ativo` = '1' 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        WHERE c.`id_cotacao` = '$_GET[id_cotacao]' ORDER BY pi.`discriminacao`, g.`referencia` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Cotação N.º <?=$_GET['id_cotacao'];?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='19'>
            Cotação N.º 
            <font color='#CCCCCC'>
                <?=$_GET['id_cotacao'];?>
            </font>
        </td>
    </tr>
<?
//Busca de alguns dados da Cotação ...
    $sql = "SELECT c.fator_mmv, c.qtde_mes_comprar, c.tipo_compra, c.desconto_especial_porc, c.origem, date_format(substring(c.data_sys, 1, 10), '%d/%m/%Y') as data_emissao, f.nome 
            FROM `cotacoes` c 
            INNER JOIN `funcionarios` f ON f.id_funcionario = c.id_funcionario 
            WHERE c.id_cotacao = '$_GET[id_cotacao]' LIMIT 1 ";
    $campos_cotacao = bancos::sql($sql);
?>
    <tr class='linhanormal'>
        <td colspan='6'>
            <font size='-1'>
                <b>Funcionário: </b><?=$campos_cotacao[0]['nome'];?>
            </font>
        </td>
        <td colspan='12' align='center'>
            <font size='-1'>
                <b>Tipo: </b>
                <?
                    if($campos_cotacao[0]['tipo_compra'] == 'N') {
                        $tipo_atual         = 'Nacional';
                        $new_tipo_compra    = 'Export';
                    }else {
                        $tipo_atual         = 'Export';
                        $new_tipo_compra    = 'Nacional';
                    }
                ?>
                <a href="imprimir.php?id_cotacao=<?=$_GET['id_cotacao'];?>&new_tipo_compra=<?=$new_tipo_compra;?>" title="Alterar Tipo de Compra da Cotação" style="cursor:help" class="link">
                    <font color='red' size='-1'>
                        <?=$tipo_atual;?>
                    </font>
                </a>
            </font>
            &nbsp;
            <font size='-1'>
                <b>Fator MMV: </b>
                <?=number_format($campos_cotacao[0]['fator_mmv'], 1, ',', '.');?>
            </font>
            &nbsp;
            <font size='-1'>
                <b>Qtde de Mês: </b>
                <?=number_format($campos_cotacao[0]['qtde_mes_comprar'], 1, ',', '.');?>
            </font>
            &nbsp;
            <font size='-1'>
                <b>Desconto: </b>
                <?=number_format($campos_cotacao[0]['desconto_especial_porc'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='14'>
            <font size='-1'>
                <b>Data: </b><?=$campos_cotacao[0]['data_emissao'];?>
            </font>
            &nbsp;|&nbsp;
            <font size='-1'>
                <?
                    //Data de Embarque = Data de Emissao + Qtde de Meses - Prazo de Viagem do Navio - Prazo p/ Retirada do Porto - 30 dias de Estocagem ...
                    $prazo_viagem_navio 	= 32;
                    $prazo_retirada_porto 	= 15;
                    $dias_estocagem 		= 30;
                ?>
                <b>Data de Embarque: </b><?=data::adicionar_data_hora($campos_cotacao[0]['data_emissao'], ($campos_cotacao[0]['qtde_mes_comprar'] * 30) - $prazo_viagem_navio - $prazo_retirada_porto - $dias_estocagem);?>
            </font>
        </td>
        <td colspan='4' align='center'>
            <font size='-1'>
                <b>Última Compra</b>
            </font>
        </td>
    </tr>
<?
    $vetor_forma_compra = array('', 'FAT/NF', 'FAT/SGD', 'AV/NF', 'AV/SGD');
    $existe_pi_blank    = 0;
    
    for($i = 0; $i < $linhas; $i++) {
	$qtde_estoque	= $campos[$i]['qtde_estoque'];
	$qtde_producao 	= $campos[$i]['qtde_producao'];

	//Verifico se o PI é do Grupo Blank ...
	if($campos[$i]['referencia'] == 'BLANK') $existe_pi_blank++;

	$sql = "SELECT `densidade_aco` 
                FROM `produtos_insumos_vs_acos` 
                WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
	$campos_densidade_aco 	= bancos::sql($sql);
	if(count($campos_densidade_aco) > 0) {
            $title_qtde_kilos 	= $campos[$i]['discriminacao'].' => '.number_format($qtde_estoque / $campos_densidade_aco[0]['densidade_aco'], 2, ',', '.').' Mts';
	}else {
            $title_qtde_kilos 	= '';
	}
	$estoque_critico_baixo	= $campos[$i]['estoque_critico_baixo'];
	$estoque_critico_alto	= $campos[$i]['estoque_critico_alto'];
	$qtde_estoque_pi        = $campos[$i]['qtde_estoque_pi'];

	/************Dados da Lista de Preço do PI************/
	if($campos_cotacao[0]['origem'] == 'R') {//Se for do Relatório de Vendas ...
            if($campos[$i]['referencia'] == 'PRAC') {//Nesse caso eu trago a Referência do PA ...
                $sql = "SELECT id_produto_acabado, referencia 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_pa 		= bancos::sql($sql);
                $referencia		= $campos_pa[0]['referencia'];
                $id_fornecedor 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo'], 0, 1);
            }else {//Se não for PA então traz dados do PI ...
                $referencia	= $campos[$i]['referencia'];
                $id_fornecedor 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo'], 0, 1);
            }
	}else {
            $referencia         = $campos[$i]['referencia'];
            $id_fornecedor 	= custos::preco_custo_pi($campos[$i]['id_produto_insumo'], 0, 1);
	}
	//Busca desses campos na Lista de Preço do PI e do Fornecedor Default de itens "ativo" apenas ...
	$sql = "SELECT f.id_pais, fpi.preco, fpi.preco_exportacao, fpi.forma_compra, fpi.tp_moeda 
                FROM `fornecedores_x_prod_insumos` fpi 
                INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor 
                WHERE fpi.id_fornecedor = '$id_fornecedor' 
                AND fpi.id_produto_insumo = '".$campos[$i]['id_produto_insumo']."' 
                AND fpi.`ativo` = '1' LIMIT 1 ";
	$campos_lista = bancos::sql($sql);
	/*************Calculo de Nivel de Estoque*************/
	//$qtde_producao = estoque_ic::compra_producao($campos[$i]['id_produto_insumo']);//A parte lenta esta aqui ...
	$qtde_estoque_total = $qtde_estoque + $qtde_producao + $qtde_estoque_pi;
	$lista = 0;//n~ listar o registro na tela
	switch($cmb_nivel) {
		case 1://Nível Baixo
			if(($qtde_estoque_total <= $estoque_critico_baixo)) {
				$lista = 1;//sim listar o registro na tela
				$nivel_de_estoque = 'Baixo';
			}
		break;
		case 2://Nível Medio
			if(($qtde_estoque_total > $estoque_critico_baixo) && ($qtde_estoque_total <= $estoque_critico_alto)) {
				$lista = 1;//sim
				$nivel_de_estoque = 'Médio';
			}
		break;
		case 3://Nível Alto
			if(($qtde_estoque_total > $estoque_critico_alto)) {
				$lista = 1;//sim
				$nivel_de_estoque = 'Alto';
			}
		break;
		case 4://Nível medio e alto
			if(($qtde_estoque_total <= $estoque_critico_baixo)) {
				$lista = 1;//sim listar o registro na tela
				$nivel_de_estoque = 'Baixo';
			}else if(($qtde_estoque_total > $estoque_critico_baixo) && ($qtde_estoque_total <= $estoque_critico_alto)) {
				$lista = 1;//sim
				$nivel_de_estoque = 'Médio';
			}
		break;
		default://Todos os Níveis
			if(($qtde_estoque_total <= $estoque_critico_baixo)) {
				$lista = 1;//sim listar o registro na tela
				$nivel_de_estoque = 'Baixo';
			}else if(($qtde_estoque_total>$estoque_critico_baixo)&&($qtde_estoque_total<=$estoque_critico_alto)) {
				$lista = 1;//sim
				$nivel_de_estoque = 'Médio';
			}else if(($qtde_estoque_total>$estoque_critico_alto)) {
				$lista = 1;//sim
				$nivel_de_estoque = 'Alto';
			}
		break;
	}
	
	if($lista == 1) {
            $cmm		   	= $campos[$i]['estoque_mensal'];
            /*Só exibe esses Rótulos na Primeira Linha do For, tive que colocar dentro do Loop porque tive de fazer um 
            select dos Itens para pegar o Símbolo da Moeda ...*/
            if($i == 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Ref.
        </td>
        <td>
            Un.
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Neces <br>Compra / Prod
        </td>
        <td>
            MMV<br/>
            <font color='#CCCCCC'>
                CMM/CMMV
            </font>
        </td>
        <td>
            Comp.<br/>Prod.
            &nbsp;
            <font title='Campo Gravado' style='cursor:help' color='red'>
                (G)
            </font>
        </td>
        <td>
            Estoque
            <br>EC PA<br>
            <font color='#CCCCCC'>
                EST. PI
            </font>
            &nbsp;
            <font title='Campo Gravado' style='cursor:help' color='red'>
                (G)
            </font>
        </td>
        <td>
            <font title='Estoque do Fornecedor' size='-2' style='cursor:help'>
                E Forn
            </font>
        </td>
        <td>
            <font title='Estoque do Porto' size='-2' style='cursor:help'>
                E Porto
            </font>
        </td>
        <td>
            Prazo<br>Ent.
        </td>
        <td>
            Qtde p/<br>Compra
            &nbsp;
            <font title='Campo Gravado' style='cursor:help' color='red'>
                (G)
            </font>
        </td>
        <td>
            Nível<br>Est.
        </td>
        <td>
            Qtde PI<br>Usado
        </td>
        <td>
            Custo Fat. $
        </td>
        <td>
            Forma <br>Pagto
        </td>
        <td>
            Total <br>$
        </td>
        <td>
            Qtde <br>Entregue
        </td>
        <td>
            Data <br>Entrega
        </td>
        <td>
            M.L.M.
        </td>
    </tr>
<?
		}
                
                if($campos_pa[0]['id_produto_acabado'] > 0) {
                    $estoque_produto    = estoque_acabado::qtde_estoque($campos_pa[0]['id_produto_acabado'], 0);
                    $est_fornecedor     = $estoque_produto[12];
                    $est_porto          = $estoque_produto[13];
                }else {
                    $est_fornecedor     = 0;
                    $est_porto          = 0;
                }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$referencia;?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
        <?
            if($campos[$i]['neces_compra_prod'] > 0) {//Se for Rel de Vendas, lê um campo gravado ...
                echo number_format($campos[$i]['neces_compra_prod'], 2, ',', '.');
            }else {
                //Essa função só roda quando é um PI ...
                $necessidade_compra = estoque_ic::necessidade_compras($campos[$i]['id_produto_insumo']);
                echo segurancas::number_format($necessidade_compra, 2, '.');
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['cmm_mmv_total'] > 0) {
                echo number_format($campos[$i]['cmm_mmv_total'], 1, ',', '.');
            }else {
                if($campos_pa[0]['id_produto_acabado'] > 0) {
                    $valores 	= intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_pa[0]['id_produto_acabado']);
                    echo number_format($valores['total_mmv_pas_atrelados'], 2, ',', '.');
                }else {
                    echo number_format($cmm, 2, ',', '.');
                    $retorno        = estoque_ic::consumo_mensal($campos[$i]['id_produto_insumo'], $campos[$i]['unidade_conversao']);//pego a qtde de cmmv do custo
                    $mostrar_cmmv   = $retorno['mostrar_cmmv'];
                    $cmmv           = $retorno['cmmv'];
                    if($mostrar_cmmv == 1) echo '/'.number_format($cmmv, 2, ',', '.');
                }
            }
        ?>
        </td>
        <td>
            <?=number_format($qtde_producao, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($qtde_estoque, 2, ',', '.');?>
        </td>
        <td>
            <?=segurancas::number_format($est_fornecedor, 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($est_porto, 2, '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['prazo_entrega'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_pedida'], 2, ',', '.');?>
        </td>
        <td>
            <?=$nivel_de_estoque;?>
        </td>
        <td>
        <?
            if($qtde_estoque_pi != '0.00') echo number_format($qtde_estoque_pi, 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($campos_lista[0]['id_pais'] == 31) {//Brasil ...
                $moeda = 'R$ ';
            }else {//Fora do Brazil ...
                if($campos_lista[0]['tp_moeda'] == 0) {
                    $moeda = 'R$ ';
                }else if($campos_lista[0]['tp_moeda'] == 1) {
                    $moeda = 'U$ ';
                }else if($campos_lista[0]['tp_moeda'] == 2) {
                    $moeda = '&euro; ';
                }
            }
            $preco_produto = ($campos_cotacao[0]['tipo_compra'] == 'N') ? $campos_lista[0]['preco'] : $campos_lista[0]['preco_exportacao'];
            //Se existir desconto na Cotação, aplico esse Desconto em cima dos Itens ...
            if($campos_cotacao[0]['desconto_especial_porc'] > 0) {
                $preco_produto-= ($preco_produto * $campos_cotacao[0]['desconto_especial_porc'] / 100);
                $preco_produto = round($preco_produto, 2);
            }
            echo $moeda.number_format($preco_produto, 2, ',', '.');
        ?>
        </td>
        <td>
            <?=$vetor_forma_compra[$campos_lista[0]['forma_compra']];?>
        </td>
        <td align='right'>
        <?
            echo number_format($preco_produto * $campos[$i]['qtde_pedida'], 2, ',', '.');
            $total_rs_total+= $preco_produto * $campos[$i]['qtde_pedida'];
        ?>
        </td>
        <td>
        <?
//Aqui eu busco a Qtde Entregue deste item na última Compra da Nota Fiscal ...
            $sql = "SELECT nfe.`id_nfe`, nfe.`data_emissao`, nfeh.`qtde_entregue` 
                    FROM `itens_pedidos` ip 
                    INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` 
                    INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
                    WHERE ip.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    ORDER BY nfe.`id_nfe` DESC LIMIT 1 ";
            $campos_nf = bancos::sql($sql);
            if(count($campos_nf) == 1) {//Se encontrar a NF ...
                $id_nfe = $campos_nf[0]['id_nfe'];
                $data_emissao 	= data::datetodata($campos_nf[0]['data_emissao'], '/');
                $qtde_entregue 	= number_format($campos_nf[0]['qtde_entregue'], 2, ',', '.');
            }else {//Caso não encontre, então ...
                $id_nfe = '';
                $data_emissao 	= '';
                $qtde_entregue 	= '';
            }
            echo $qtde_entregue;
        ?>
        </td>
        <td>
            <?=$data_emissao;?>
        </td>
        <td>
            <?=number_format($campos[$i]['mlm'], 1, ',', '.');?>
        </td>
    </tr>
<?
            $id_produtos_insumos.= $campos[$i]['id_produto_insumo'].', ';
            
            /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
            encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
            da Biblioteca de Custos, na função pas_atrelados ...*/
            unset($vetor_pas_atrelados);
        }
    }
    $id_produtos_insumos = substr($id_produtos_insumos, 0, strlen($id_produtos_insumos) - 2);
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
<?
//Controle referente ao Menu Consultar Cotações Existentes

/*Aqui é um controle para não exibir os botões de Manipulação da Cotações, primeiro pq aki é simplesmente
só p/ visualizar Consultar Cotações Existentes e sendo assim não faz sentido exibir os botões de
Manipulação de Cotação*/
	if($nao_mostrar != 1) {
?>
            <input type='button' name='cmd_vincular_fornecedor' value='Vincular Fornecedor' title='Vincular Fornecedor' onclick="window.location = 'vincular_fornecedor.php?id_cotacao=<?=$_GET['id_cotacao'];?>&chkt_produto='+document.form.id_produtos_insumos.value" style='color:darkgreen' class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' style='color:black' class='botao'>
<?
	}
?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
        <td colspan='6'>
            <font color='#CCCCCC'>Dólar Dia:</font> - <?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?>
            <font color='#CCCCCC'>Euro Dia:</font> - <?=number_format(genericas::moeda_dia('euro'), 4, ',', '.');?>
        </td>
        <td colspan='3' align='right'>
            <font color='#CCCCCC'>Total Geral <?=$moeda;?>:</font>
        </td>
        <td colspan='4' align='left'>
            &nbsp;<?=number_format($total_rs_total, 2, ',', '.');?>
        </td>
    </tr>
<?
    if($existe_pi_blank > 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='19'>
            <font color='black' size='3'>
                <br/><blink><b>PASSAR PARA EMITIR OP(S) DO(S) BLANK(S).</b></blink>
            </font>
        </td>
    </tr>
<?
    }
?>
</table>
<input type='hidden' name='id_produtos_insumos' value='<?=$id_produtos_insumos;?>'>
<input type='hidden' name='id_cotacao' value='<?=$_GET['id_cotacao'];?>'>
</form>
</body>
</html>