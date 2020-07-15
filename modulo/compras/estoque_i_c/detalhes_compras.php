<?
require('../../../lib/segurancas.php');
require('../../../lib/compras_new.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
require('../../../lib/estoque_acabado.php');
/*Por mais que eu não utilize a biblioteca intermodular aqui nesse arquivo diretamente, tenho que chamá-la porque esta é chamada em alguma 
das outras bibliotecas ...*/
require('../../../lib/intermodular.php');
session_start('funcionarios');
//Essa segurança é p/ que ignore essa verificação devido esse arquivo ser chamado como Iframe em outros arquivos ...
if(empty($_GET['ignorar_seguranca_url'])) segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/inventario.php', '../../../');
$mensagem[1] = "<font class='atencao'>NÃO HÁ COMPRA(S) A SER(EM) CONSULTADO(S) A SER(EM) CONSULTADA(S) NESSE PERÍODO.</font>";

if($passo == 1) {
//Esse sql é uma doideira (rsrs) ...
	$sql = "SELECT e.`nomefantasia`, f.`razaosocial`, f.`id_pais`, func.`nome`, g.`referencia`, ip.`preco_unitario`, 
                ip.`qtde`, ip.`ipi`, ip.`marca`, p.*, nfeh.`qtde_entregue`, nfeh.`valor_entregue`, nfe.`id_nfe`, nfe.`id_empresa` AS nfe_id_empresa, 
                nfe.`id_tipo_pagamento_recebimento`, nfe.`id_tipo_moeda`, nfe.`id_fornecedor_propriedade`, nfe.`num_nota`, 
                nfe.`tipo` AS nfe_tipo, nfe.`prazo_a`, nfe.`prazo_b`, nfe.`prazo_c`, nfe.`data_emissao`, nfe.`data_entrega` AS nfe_data_entrega, 
                nfe.`situacao` AS nfe_situacao, pi.`discriminacao` 
                FROM `nfe_historicos` nfeh 
                INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
                INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
                INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` 
                INNER JOIN `empresas` e ON e.`id_empresa` = p.`id_empresa` 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
                INNER JOIN `funcionarios` func ON func.`id_funcionario` = p.`id_funcionario_cotado` 
                INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                WHERE nfeh.`id_nfe_historico` = '$id_nfe_historico' LIMIT 1 ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
	$id_pais = $campos[0]['id_pais'];
//Seleção do tipo de moeda
	$sql = "SELECT CONCAT(simbolo, ' - ', moeda) AS tipo_moeda, concat(simbolo, ' ') AS moeda 
                FROM `tipos_moedas` 
                WHERE `id_tipo_moeda` = '".$campos[0]['id_tipo_moeda']."' LIMIT 1 ";
	$campos_moeda   = bancos::sql($sql);
	$tipo_moeda     = $campos_moeda[0]['tipo_moeda'];
	$moeda          = $campos_moeda[0]['moeda'];
?>
<html>
<head>
<title>.:: Consultar Compra(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<form>
<!--Significa que essa Tela foi acessada do Módulo de Vendas do Menu de Estoque-->
<input type='hidden' name='veio_vendas' value='<?=$veio_vendas;?>'>
<table width='90%' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Compra(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Dado(s) do Produto
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            <b>Referência:</b>
        </td>
        <td width='70%'>
            <?=$campos[0]['referencia'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Discriminação:</b>
            </td>
            <td>
                    <?=$campos[0]['discriminacao'];?>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Preço Unitário:</b>
            </td>
            <td>
                    <?=$moeda.number_format($campos[0]['preco_unitario'], '2', ',', '.');?>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Quantidade:</b>
            </td>
            <td>
                    <?=number_format($campos[0]['qtde'], 2, ',', '.');?>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Valor Total:</b>
            </td>
            <td>
                    <?=number_format($campos[0]['preco_unitario'] * $campos[0]['qtde'], 2, ',', '.');?>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Valor do IPI:</b>
            </td>
            <td>
                    <?=number_format($campos[0]['ipi'], 2, ',', '.');?>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    <b>Marca:</b>
            </td>
            <td>
                    <?=$campos[0]['marca'];?>
            </td>
    </tr>
<!------------------------ Dados do Pedido ---------------------->
<?
	$prazo_a = $campos[0]['prazo_pgto_a'];
	$prazo_b = $campos[0]['prazo_pgto_b'];
	$prazo_c = $campos[0]['prazo_pgto_c'];

	if($prazo_a == 0) {
		$prazo_a = 'À Vista';
	}
	if($prazo_b != 0) {
		$prazo_b = '/'.$prazo_b;
	}else {
		$prazo_b = '';
	}
	if($prazo_c != '0') {
		$prazo_c = '/'.$prazo_c;
	}else {
		$prazo_c = '';
	}
	$prazo = $prazo_a.$prazob.$prazo_c;
?>
	<tr class="linhadestaque" align='center'>
		<td colspan="2">
			Dado(s) do Pedido
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>N.º do Pedido:</b>
		</td>
		<td>
			<a href="#" onclick="nova_janela('../pedidos/itens/itens.php?id_pedido=<?=$campos[0]['id_pedido'];?>&pop_up=1', 'DETALHES', 'F', '', '', '', '', '', 'c', 'c', '', '', 's', 's', '', '', '')" class="link">
				<?=$campos[0]['id_pedido'];?>
				<img src = '../../../imagem/propriedades.png' title="Detalhes do Pedido" alt="Detalhes do Pedido" border="0">
			</a>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Fornecedor:</b>
		</td>
		<td>
			<?=$campos[0]['razaosocial'];?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Empresa:</b>
		</td>
		<td>
			<?=$campos[0]['nomefantasia'];?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Condição:</b>
		</td>
		<td>
			<?=$campos[0]['desc_ddl'];?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Tipo:</b>
		<td>
		<?
                    if($campos[0]['tipo_export'] == 'E') {
                        echo 'Exportação';
                    }else {
                        echo 'Nacional';
                    }
		?>
		</td>
	</tr>
<?
//Somente para países internacionais
	if($id_pais != 31) {
?>
	<tr class='linhanormal'>
		<td><b>Importação:</b></td>
		<td>
		<?
			$sql = "SELECT i.nome 
					FROM `importacoes` i 
					INNER JOIN `pedidos` p ON p.id_importacao = i.id_importacao 
					WHERE p.id_pedido = '".$campos[0]['id_pedido']."' LIMIT 1 ";
			$campos_importacao = bancos::sql($sql);
			echo $campos_importacao[0]['nome'];
		?>
		</td>
	</tr>
<?
	}
?>
	<tr class='linhanormal'>
		<td><b>Tipo da Moeda:</b>
		<td>
		<?
//Países Nacionais
			if($id_pais == 31) {
				$sql = "Select concat(simbolo, ' - ', moeda) as moeda from tipos_moedas where id_tipo_moeda = ".$campos[0]['id_tipo_moeda']." and ativo = '1' and id_tipo_moeda = 1";
//Países Internacionais
			}else {
				$sql = "Select concat(simbolo, ' - ', moeda) as moeda from tipos_moedas where id_tipo_moeda = ".$campos[0]['id_tipo_moeda']." and ativo='1' and id_tipo_moeda <> 1 ";
			}
			$campos2 = bancos::sql($sql);
			echo $campos2[0]['moeda'];
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Funcionário Cotador:</b>
		<td>
		<?
			$id_func_cotado = $campos[0]['id_funcionario_cotado'];
			if(!empty($id_func_cotado)) {
				$sql2 = "Select nome 
						from `funcionarios` 
						where `id_funcionario` = '$id_func_cotado' limit 1 ";
				$campos2 = bancos::sql($sql2);
				$nome = $campos2[0]['nome'];
			}
			echo $nome;
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td><b>Vendedor:</b></td>
		<td>
			<?=$campos[0]['vendedor'];?>
		</td>
	</tr>
	<?
		$data_emissao = $campos[0]['data_emissao'];
		$data_entrega = $campos[0]['prazo_entrega'];
		if($data_entrega == '0000-00-00') {
			$entrega = '';
			$data_emissao = data::datetodata($data_emissao, '/');
			$data_entrega = '';
		}else {
			$data_emissao = data::datetodata($data_emissao, '/');
			$data_emissao = data::datatodate($data_emissao, '-');
			$prazo_entrega = data::diferenca_data($data_emissao, $data_entrega);
			$entrega = $prazo_entrega[0];
			$data_emissao = data::datetodata($data_emissao, '/');
			$data_entrega = data::datetodata($data_entrega, '/');
		}
	?>
	<tr class='linhanormal'>
		<td>
		<?
			if($id_pais == 31) {
				echo '<b>Prazo de Entrega:</b>';
			}else {
				echo '<b>Prazo de Embarque:</b>';
			}
		?>
		</td>
		<td>
			<?=$entrega;?> Dias - <?=$data_entrega;?>
		</td>
	</tr>
<?
//Somente para países internacionais
	if($id_pais != 31) {
?>
	<tr class='linhanormal'>
		<td><b>Prazo de Viagem do Navio:</b>
		<td>
			<?=$campos[0]['prazo_navio'];?>
		</td>
	</tr>
<?
	}
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Financiamento que foi feitas p/ o Pedido ...
	$sql = "Select pf.*, tm.simbolo 
			from pedidos_financiamentos pf 
			inner join pedidos p on p.id_pedido = pf.id_pedido 
			inner join tipos_moedas tm on tm.id_tipo_moeda = p.id_tipo_moeda 
			where pf.id_pedido = '".$campos[0]['id_pedido']."' order by pf.dias asc ";
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
		$condicao_ddl = $linhas_financiamento.' parc. ('.$primeira_parcela.' à '.$ultima_parcela.' DDL) '.$exibir_nota.' '.$tipo_nota_porc.' %';
	}else {//Modo Antigo ...
		if($campos[$i]['prazo_pgto_c'] > 0) {
			$condicao_ddl = '/'.$campos[$i]['prazo_pgto_c'];
		}
		if($campos[$i]['prazo_pgto_b'] > 0) {
			$condicao_ddl = $campos[$i]['prazo_pgto_a'].'/'.$campos[$i]['prazo_pgto_b'].$condicao_ddl;
		}else {
			if($campos[$i]['prazo_pgto_a'] == 0) {
				$condicao_ddl = 'À vista';
			}else {
				$condicao_ddl = $campos[$i]['prazo_pgto_a'];
			}
		}
	}
?>
	<tr class='linhanormal'>
		<td><b>Prazo de Pgto: </b>
		<td>
			<?=$condicao_ddl;?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Marca:</b>
		</td>
		<td>
			<?=$campos[0]['marca'];?>
		</td>
	</tr>
	<!--****************************Follow-UPs***************************-->
        <tr class='linhanormal' align='center'>
            <td colspan='2'>
                <iframe name='detalhes' id='detalhes' src = '/erp/albafer/modulo/classes/follow_ups/detalhes.php?identificacao=<?=$campos[0]['id_pedido'];?>&origem=16' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
            </td>
        </tr>
        <!--*****************************************************************-->
<!-------------------- Dados da Nota Fiscal de Entrada -------------------------->
	<tr class="linhadestaque" align='center'>
		<td colspan="2">
			<font size="-1">
				Dado(s) da Nota de Entrada
			</font>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>N.º da Nota:</b>
		</td>
		<td>
			<a href="#" onclick="nova_janela('../pedidos/nota_entrada/itens/itens.php?id_nfe=<?=$campos[0]['id_nfe'];?>&pop_up=1', 'ITENS', 'F', '', '', '', '', '', 'c', 'c', '', '', 's', 's', '', '', '')" class="link">
				<?=$campos[0]['num_nota'];?>
				<img src = '../../../imagem/propriedades.png' title="Detalhes da Nota Fiscal" alt="Detalhes da Nota Fiscal" border="0">
			</a>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Fornecedor:</b>
		</td>
		<td>
			<?=$campos[0]['razaosocial'];?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Empresa:</b>
		</td>
		<td>
		<?
			if($campos[0]['nfe_id_empresa'] == 1) {
				echo 'ALBAFER';
			}else if($campos[0]['nfe_id_empresa'] == 2) {
				echo 'TOOL MASTER';
			}else if($campos[0]['nfe_id_empresa'] == 4) {
				echo 'GRUPO';
			}
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Tipo Moeda:</b>
		</td>
		<td>
		<?
			$sql = "Select concat(simbolo, ' - ', moeda) as tipo_moeda, concat(simbolo, ' ') as moeda 
                                from tipos_moedas 
                                where id_tipo_moeda = '".$campos[0]['nfe_id_tipo_moeda']."' limit 1";
			$campos2 = bancos::sql($sql);
			echo $tipo_moeda = $campos2[0]['tipo_moeda'];
			$moeda = $campos2[0]['moeda'];
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <b>Tipo de Pagamento:</b>
            </td>
            <td>
            <?
                //Busca dos Tipos de Pagamento cadastrados ...
                $sql = "SELECT pagamento 
                        FROM `tipos_pagamentos` 
                        WHERE `id_tipo_pagamento` = '".$campos[0]['id_tipo_pagamento_recebimento']."' LIMIT 1 ";
                $campos_tipos_pagamento = bancos::sql($sql);
                if(count($campos_tipos_pagamento) == 1) echo $campos_tipos_pagamento[0]['pagamento'];
            ?>
            </td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Conta Corrente:</b>
		</td>
		<td>
		<?
//Seleção da conta corrente
			if($campos[0]['id_fornecedor_propriedade'] > 0) {
				$sql = "SELECT num_cc 
						FROM `fornecedores_propriedades` 
						WHERE `id_fornecedor_propriedade` = '".$campos[0]['id_fornecedor_propriedade']."' LIMIT 1 ";
				$campos_propriedades = bancos::sql($sql);
				if(count($campos_propriedades) == 1) echo $campos_propriedades[0]['num_cc'];
			}
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Tipo:</b>
		</td>
		<td>
		<?
			if($campos[0]['nfe_tipo'] == 1) {
				echo 'NF';
			}else {
				echo 'SGD';
			}
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
		<?
			if($id_pais == 31) {
				echo '<b>Data de Emissão:</b>';
			}else {
				echo '<b>Data do B/L:</b>';
			}
		?>
		</td>
		<td>
			<?=data::datetodata($campos[0]['data_emissao'], '/');?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Data de Entrega:</b>
		</td>
		<td>
			<?=data::datetodata($campos[0]['nfe_data_entrega'], '/');?>
		</td>
	</tr>
<?
//Verifico se essa NF foi feita através do modo Financiamento ...
	$sql = "SELECT id_nfe_financiamento 
                FROM `nfe_financiamentos` 
                WHERE `id_nfe` = '".$campos[0]['id_nfe']."' LIMIT 1 ";
	$campos_financiamento = bancos::sql($sql);
	$modo_financiamento = count($campos_financiamento);
	if($modo_financiamento == 1) {//Foi feito pelo modo financiamento ...
//Aqui eu busco todas as Parcelas do Financiamento da NF que foi através do Pedido ...
		$sql = "Select nf.*, tm.simbolo 
                        from nfe_financiamentos nf 
                        inner join nfe on nfe.id_nfe = nf.id_nfe 
                        inner join tipos_moedas tm on tm.id_tipo_moeda = nfe.id_tipo_moeda 
                        where nf.id_nfe = '".$campos[0]['id_nfe']."' order by nf.dias asc ";
		$campos_financiamento = bancos::sql($sql);
		$linhas_financiamento = count($campos_financiamento);
//Limpo a variável p/ q não continue com os valores antigos ...
		$prazo_faturamento = '';
//Disparo do Loop ...
		for($j = 0; $j < $linhas_financiamento; $j++) {
			$prazo_faturamento.= $campos_financiamento[$j]['dias'].'/ ';
		}
		$prazo_faturamento = substr($prazo_faturamento, 0, strlen($prazo_faturamento) - 2);
	}else {
//Aqui a Empresa, mais o Tipo de Nota, mais os prazos de pagamento
		if($campos[$i]['prazo_c'] > 0) {
			$prazo_faturamento = '/'.$campos[$i]['prazo_c'];
		}
		if($campos[$i]['prazo_b'] > 0) {
			$prazo_faturamento = $campos[$i]['prazo_a'].'/'.$campos[$i]['prazo_b'].$prazo_faturamento;
		}else {
			if($campos[$i]['prazo_a'] == 0) {
				$prazo_faturamento = 'À vista';
			}else {
				$prazo_faturamento = $campos[$i]['prazo_a'];
			}
		}
	}	
?>
	<tr class='linhanormal'>
		<td><b>Prazo de Pgto: </b>
		<td>
		<?
			echo $prazo_faturamento;
			if($prazo_faturamento != 'À vista') {
				echo ' DDL';
			}
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Situação:</b>
		</td>
		<td>
		<?
			if($campos[0]['nfe_situacao'] == 0) {
				echo 'Não Liberado';
			}else {
				echo 'Liberado';
			}
		?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Quantidade Comprado:</b>
		</td>
		<td>
			<?=number_format($campos[0]['qtde_entregue'], 2, ',', '.');?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Valor Unitário:</b>
		</td>
		<td>
			<?=$moeda.number_format($campos[0]['valor_entregue'], '2', ',', '.');?>
		</td>
	</tr>
	<!--****************************Follow-UPs***************************-->
        <tr class='linhanormal' align='center'>
            <td colspan='2'>
                <iframe name='detalhes' id='detalhes' src = '/erp/albafer/modulo/classes/follow_ups/detalhes.php?identificacao=<?=$campos[0]['id_nfe'];?>&origem=17' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
            </td>
        </tr>
        <!--*****************************************************************-->
	<tr class="atencao" align='center'>
		<td colspan="2">
			&nbsp;
		</td>
	</tr>
	<tr class="atencao" align='center'>
		<td colspan="2">
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'detalhes_compras.php<?=$parametro;?>'" class="botao">
			<input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick = 'parent.close()' style="color:red" class='botao'>
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
}else {
//Esse parâmetro veio_vendas = 1, significa que essa Tela foi acessada do Módulo de Vendas do Menu de Estoque
    if($veio_vendas == 1) {
        if($id_produto_acabado > 0) {//Só irá buscar o PI do PA quando o $id_produto_acabado for passado por parâmetro ...
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `id_produto_insumo` > '0' LIMIT 1 ";
            $campos             = bancos::sql($sql);
            $id_produto_insumo  = $campos[0]['id_produto_insumo'];
        }
    }
    //Aqui eu busco o PA do PI que é "Matéria Prima" ...
    $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.discriminacao, u.sigla 
            FROM `produtos_acabados` pa 
            INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
            WHERE pa.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
    $campos_pa              = bancos::sql($sql);
    if(count($campos_pa) == 1) {//Se esse PI for realmente um PA então ...
        $id_fornecedor_default  = custos::procurar_fornecedor_default_revenda($campos_pa[0]['id_produto_acabado'], '',  1);
        
        $qtde_estoque           = estoque_acabado::qtde_estoque($campos_pa[0]['id_produto_acabado']);
        $ec_pa                  = $qtde_estoque[8];
        $total_qtde_entregue    = 0;
        
        /*Aqui eu busco todas as NF´s de Entrada desse PI que esteja liberado em Estoque até que a Qtde Recebida 
        seja < que o EC do PA ...*/
        $sql = "SELECT nfe.id_nfe, nfeh.qtde_entregue 
                FROM `nfe_historicos` nfeh 
                INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
                WHERE nfeh.`id_produto_insumo` = '$id_produto_insumo' 
                AND nfeh.`status` = '1' 
                ORDER BY nfe.data_entrega DESC ";
        $campos_nfe = bancos::sql($sql);
        $linhas_nfe = count($campos_nfe);
        if($linhas_nfe > 0) {//Se encontrou pelo menos 1 NFe ...
            if($_GET['ver_todas_compras'] == 1) {
                //Armazeno nesse vetor todas as NF´s em que constam todas as Compras desse PA desde o início do Sistema ...
                for($i = 0; $i < $linhas_nfe; $i++) $vetor_nfe[] = $campos_nfe[$i]['id_nfe'];
            }else {
                if($veio_vendas == 1) {//Em vendas o sistema faz esse controle por causa da ML Estimada ...
                    for($i = 0; $i < $linhas_nfe; $i++) {
                        //Enquanto o Somatório Total da Qtde Entregue for menor que o EC do PA, vou acumulando nessa variável $total_qtde_entregue ...
                        if($total_qtde_entregue < $ec_pa) {
                            $total_qtde_entregue+= $campos_nfe[$i]['qtde_entregue'];
                            $vetor_nfe[] = $campos_nfe[$i]['id_nfe'];
                        }
                    }
                }else {//No caso de estar acessando essa tela de outro módulo não temos esse problema ...
                    for($i = 0; $i < $linhas_nfe; $i++) {
                        $total_qtde_entregue+= $campos_nfe[$i]['qtde_entregue'];
                        $vetor_nfe[]        = $campos_nfe[$i]['id_nfe'];
                    }
                }
                if(!isset($vetor_nfe)) $vetor_nfe[] = 0;//Trato essa variável p/ não dar erro na query mais abaixo ...
            }
            $condicao_nfes = " AND nfe.`id_nfe` IN (".implode(',', $vetor_nfe).") ";
        }
    }else {//Esse PI é somente PI mesmo ...
        $id_fornecedor_default  = custos::preco_custo_pi($id_produto_insumo, '', 1);
        /*Esses parâmetros txt_data_inicial, txt_data_final vem da Tela do arquivo de 
detalhes_baixas_manipulações.php em compras mesmo, e serve para trazer todas as Notas 
Fiscais em que a Data de Emissão estejam no período passado por parâmetro ...*/
        if(!empty($_GET['txt_data_inicial'])) {
            $txt_data_inicial_usa = data::datatodate($_GET['txt_data_inicial'], '-');
            $txt_data_final_usa = data::datatodate($_GET['txt_data_final'], '-');
            $condicao_datas = " AND nfe.`data_emissao` BETWEEN '$txt_data_inicial_usa' AND '$txt_data_final_usa' ";

            $txt_data_inicial = data::datetodata($txt_data_inicial_usa, '/');
            $txt_data_final = data::datetodata($txt_data_final_usa, '/');
        }
    }

    //Trago somente itens que estão na Nota Fiscal de Entrada e que estejam liberados em Estoque ...
    $sql = "SELECT e.`nomefantasia`, f.`id_pais`, f.`razaosocial`, g.`referencia`, 
            pi.`discriminacao`, nfe.`id_nfe`, nfe.`tipo` AS tipo_nota, 
            nfe.`num_nota`, nfe.`prazo_a`, nfe.`prazo_b`, nfe.`prazo_c`, nfe.`data_emissao`, 
            DATE_FORMAT(nfe.`data_entrega`, '%d/%m/%Y') AS nfe_data_entrega, nfeh.`id_nfe_historico`, nfeh.`qtde_entregue`, 
            nfeh.`valor_entregue`, tm.`id_tipo_moeda`, CONCAT(tm.`simbolo`, ' ' ) AS tipo_moeda, u.`sigla` 
            FROM `nfe` 
            INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` AND nfeh.`status` = '1' 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = nfeh.`id_produto_insumo` AND pi.`id_produto_insumo` = '$id_produto_insumo' 
            INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = nfe.`id_tipo_moeda` 
            WHERE 1 
            $condicao_nfes 
            $condicao_datas 
            ORDER BY nfe.`data_entrega` DESC ";
    //Se essa Tela foi acessada de Vendas, não posso exibir os dados com paginação porque senão temos erros no cálculo na ML Est ...
    if(!empty($veio_vendas)) {
        $campos = bancos::sql($sql);
    }else {//Se foi acessada de Compras, então posso exibir os dados com paginação normalmente ...
        $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    }
    $linhas = count($campos);
    if($linhas == 0) {//Se não encontrou nenhuma Compra ...
?>
<html>
<head>
<title>.:: Consultar Compra(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Esse voltar é um pouquinho diferente (rsrs)
function voltar() {
    var veio_vendas = eval('<?=$veio_vendas;?>')
//Esse parâmetro veio_vendas = 1, significa que essa Tela foi acessada do Módulo de Vendas do Menu de Estoque
    if(veio_vendas == 1) {
        window.close()
        nova_janela('../../vendas/estoque_acabado/detalhes.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}
</Script>
</head>
<body>
<form>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<!--Significa que essa Tela foi acessada do Módulo de Vendas do Menu de Estoque-->
<input type="hidden" name="veio_vendas" value="<?=$veio_vendas;?>">
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='parent.close()' style="color:red" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
    }else {//Existe pela menos 1 Compra ...
?>
<html>
<head>
<title>.:: Consultar Compra(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
//Esse voltar é um pouquinho diferente (rsrs)
function voltar() {
    var veio_vendas = eval('<?=$veio_vendas;?>')
//Esse parâmetro veio_vendas = 1, significa que essa Tela foi acessada do Módulo de Vendas do Menu de Estoque
    if(veio_vendas == 1) {
        window.close()
        nova_janela('../../vendas/estoque_acabado/detalhes.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {
        window.parent.location = 'detalhes.php?id_produto_insumo=<?=$id_produto_insumo;?>'
    }
}
</Script>
</head>
<body>
<form name='form'>
<input type='hidden' name='id_produto_insumo' value='<?=$id_produto_insumo;?>'>
<!--Significa que essa Tela foi acessada do Módulo de Vendas do Menu de Estoque-->
<input type='hidden' name='veio_vendas' value='<?=$veio_vendas;?>'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <font color='yellow'>
                Consultar Compras
            </font>
            <br/>
            <?
                $vetor_forma_compra     = array('', 'FAT/NF', 'FAT/SGD', 'AV/NF', 'AV/SGD');//Para facilitar ...
                $vetor_tipo_moeda       = array('R$', 'U$', '&euro;');//Para facilitar ...
            
                //Busca do Preço na Lista de Preço do PI e do Fornecedor Default ...
                $sql = "SELECT f.`razaosocial`, IF(f.`id_pais` = '31', fpi.`preco`, fpi.`preco_exportacao`) AS preco_lista, 
                        fpi.`forma_compra`, fpi.`tp_moeda` 
                        FROM `fornecedores_x_prod_insumos` fpi 
                        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
                        WHERE fpi.`id_fornecedor` = '$id_fornecedor_default' 
                        AND fpi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
                $campos_lista = bancos::sql($sql);
                if(count($campos_pa) == 1) {//Se o PI for realmente um PA então ...
                    echo 'Ref: '.$campos_pa[0]['referencia'].' - Unidade: '.$campos_pa[0]['sigla'].' - Discriminação: '.$campos_pa[0]['discriminacao'].'<br>';
                }else {
                    echo 'Ref: '.$campos[0]['referencia'].' - Unidade: '.$campos[0]['sigla'].' - Discriminação: '.$campos[0]['discriminacao'].'<br>';
                }
                echo 'Fornecedor Default: '.$campos_lista[0]['razaosocial'].'<br>';
                echo 'Preço Fat NF: '.$vetor_tipo_moeda[$campos_lista[0]['tp_moeda']].' '.number_format($campos_lista[0]['preco_lista'], 2, ',', '.').' - Condição de Compra: '.$vetor_forma_compra[$campos_lista[0]['forma_compra']];
            ?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE' colspan='2'>
            <b>Qtde</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Preço</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Preço Corrigido</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Preço Corrigido R$</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Fornecedor</b>
        </td>
        <td bgcolor='#CECECE'>
            <font title="N.&ordm; Nota / Empresa / Tipo de Nota / Prazo de Pagamento">
                <b>N.&ordm; Nota / Emp / Tp Nota<br> / Prazo Pgto</b>
            </font>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data de <br>Emissão</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data de <br>Entrega</b>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            //Só irá contabilizar a Quantidade quando existir Preço p/ o Item de NF de Entrada ...
            if($campos[$i]['valor_entregue'] != '0.00') $qtde_compras_total+= $campos[$i]['qtde_entregue'];
            $preco_total+= $campos[$i]['qtde_entregue'] * $campos[$i]['valor_entregue'];
            $moeda = $campos[$i]['tipo_moeda'];
?>
    <tr class='linhanormal'onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'detalhes_compras.php?passo=1&id_nfe_historico=<?=$campos[$i]['id_nfe_historico'];?>&txt_data_inicial=<?=$txt_data_inicial;?>&txt_data_final=<?=$txt_data_final;?>&veio_vendas=<?=$veio_vendas;?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='center'>
            <?=number_format($campos[$i]['qtde_entregue'], '2', ',', '.');?>
        </td>
        <td align='right'>
            <?=$moeda.number_format($campos[$i]['valor_entregue'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            /*Verifico se existem Compras acima desse período capitalizaremos uma Taxa de 0,5% 
            porque a empresa nessa época não capitava dinheiro nos Bancos ...*/
            if($campos[$i]['data_emissao'] < '2009-01-01') {
                //Aqui é anterior a 2009, com meio % apenas ao mês de Taxas ...
                $taxa_financeira_compras    = 0.5;
                $fator_taxa_financeira      = pow(($taxa_financeira_compras / 100 + 1), (1 / 30));

                $retorno_data               = data::diferenca_data($campos[$i]['data_emissao'], '2008-12-31');
                $dias                       = $retorno_data[0];
                $fator_taxa_final_periodo   = pow($fator_taxa_financeira, $dias);
                $preco_corrigido_atual      = $campos[$i]['valor_entregue'] * $fator_taxa_final_periodo;

                /*Aqui já é a partir de 01 de janeiro de 2009 com Taxas a partir de 2% ...
                para esse caso será cobrado taxa em cima de taxa ...*/
                $taxa_financeira_compras    = genericas::variavel(4) - 0.5;
                $fator_taxa_financeira      = pow(($taxa_financeira_compras / 100 + 1), (1 / 30));

                $retorno_data               = data::diferenca_data('2009-01-01', date('Y-m-d'));
                $dias                       = $retorno_data[0];
                $fator_taxa_final_periodo   = pow($fator_taxa_financeira, $dias);

                $preco_corrigido_atual      = $preco_corrigido_atual * $fator_taxa_final_periodo;
            }else {//Sempre a partir de 1 de Janeiro de 2009 ...
                /*Até o dia 23/07/2013 às 16:38 era desse modo => "genericas::variavel(4) - 0.5" ..., 
                a partir daí fixamos 2% porque o Roberto acha que esse é o Valor Máximo p/ essa Taxa de 
                Estocagem, como os Juros subiram teríamos que fazer uma interpolação o que seria complicado 
                e fizemos isso p/ simplicarmos os cálculos e ganharmos tempo ...*/
                $taxa_financeira_compras    = 2;
                $fator_taxa_financeira      = pow(($taxa_financeira_compras / 100 + 1), (1 / 30));

                $retorno_data               = data::diferenca_data($campos[$i]['data_emissao'], date('Y-m-d'));
                $dias                       = $retorno_data[0];
                $fator_taxa_final_periodo   = pow($fator_taxa_financeira, $dias);

                $preco_corrigido_atual      = $campos[$i]['valor_entregue'] * $fator_taxa_final_periodo;
            }
            $valor_total_corrigido          = round($preco_corrigido_atual * $campos[$i]['qtde_entregue'], 2);
            echo $moeda.number_format($preco_corrigido_atual, 2, ',', '.');
            $valor_total_corrigido_geral+=  $valor_total_corrigido;
        ?>
        </td>
        <td align='right'>
            R$ 
            <?
                if($campos[$i]['id_tipo_moeda'] == 1) {//Real ...
                    $preco_corrigido_atual_rs = $preco_corrigido_atual;
                }else if($campos[$i]['id_tipo_moeda'] == 2) {//U$ ...
                    $preco_corrigido_atual_rs = $preco_corrigido_atual * genericas::moeda_dia('dolar');
                }else if($campos[$i]['id_tipo_moeda'] == 3) {//Euro ...
                    $preco_corrigido_atual_rs = $preco_corrigido_atual * genericas::moeda_dia('euro');
                }
                
                //Se o País do Fornecedor for Estrangeiro "fora do Brasil", também multiplico pelo "Fator Importação Numerário Valor" ...
                if($campos[$i]['id_pais'] != 31) $preco_corrigido_atual_rs*= genericas::variavel(1);
                
                $valor_total_corrigido_rs = round($preco_corrigido_atual_rs * $campos[$i]['qtde_entregue'], 2);
                echo number_format($preco_corrigido_atual_rs, 2, ',', '.');
                $valor_total_corrigido_geral_rs+= $valor_total_corrigido_rs;
            ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
                <font title="<?=$campos[$i]['nomefantasia'];?>">
                <?
                        if($campos[$i]['tipo_nota'] == 1) {//NF
                                $nota = 'NF';
                        }else {//SGD
                                $nota = 'SGD';
                        }
                        echo $campos[$i]['num_nota'].' - ('.substr($campos[$i]['nomefantasia'], 0, 1).' - '.$nota.') / ';
//Verifico se essa NF foi feita através do modo Financiamento ...
                        $sql = "SELECT id_nfe_financiamento 
                                FROM `nfe_financiamentos` 
                                WHERE `id_nfe` = '".$campos[$i]['id_nfe']."' LIMIT 1 ";
                        $campos_financiamento = bancos::sql($sql);
                        $modo_financiamento = count($campos_financiamento);

                        if($modo_financiamento == 1) {//Foi feito pelo modo financiamento ...
//Aqui eu busco todas as Parcelas do Financiamento da NF que foi através do Pedido ...
                                $sql = "SELECT nf.*, tm.simbolo 
                                        FROM `nfe_financiamentos` nf 
                                        INNER JOIN `nfe` ON nfe.`id_nfe` = nf.`id_nfe` 
                                        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = nfe.`id_tipo_moeda` 
                                        WHERE nf.`id_nfe` = '".$campos[$i]['id_nfe']."' ORDER BY nf.dias ";
                                $campos_financiamento = bancos::sql($sql);
                                $linhas_financiamento = count($campos_financiamento);
//Limpo a variável p/ q não continue com os valores antigos ...
                                $prazo_faturamento = '';
//Disparo do Loop ...
                                for($j = 0; $j < $linhas_financiamento; $j++) {
                                        $prazo_faturamento.= $campos_financiamento[$j]['dias'].'/ ';
                                }
                                echo $prazo_faturamento = substr($prazo_faturamento, 0, strlen($prazo_faturamento) - 2);
                        }else {
//Aqui a Empresa, mais o Tipo de Nota, mais os prazos de pagamento
                                if($campos[$i]['prazo_c'] > 0) {
                                        $prazo_faturamento = '/'.$campos[$i]['prazo_c'];
                                }
                                if($campos[$i]['prazo_b'] > 0) {
                                        $prazo_faturamento = $campos[$i]['prazo_a'].'/'.$campos[$i]['prazo_b'].$prazo_faturamento;
                                }else {
                                        if($campos[$i]['prazo_a'] == 0) {
                                                $prazo_faturamento = 'À vista';
                                        }else {
                                                $prazo_faturamento = $campos[$i]['prazo_a'];
                                        }
                                }
                                echo $prazo_faturamento;
                        }
                ?>
                </font>
        </td>
        <td align='center'>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td align='center'>
            <?=$campos[$i]['nfe_data_entrega'];?>
        </td>
    </tr>
<?
		}
?>
    <tr class='linhadestaque'>
        <td colspan='2' align='center'>
            <?=number_format($qtde_compras_total, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=$moeda.number_format($preco_total, 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=$moeda.number_format($valor_total_corrigido_geral, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($valor_total_corrigido_geral_rs, 2, ',', '.');?>
        </td>
        <td>
            <font color='darkblue'>
                <?$preco_compras_medio_corr_atual = ($valor_total_corrigido_geral_rs / $qtde_compras_total);?>
                P.Compra Med.Corr.Atual R$ = <?=number_format($preco_compras_medio_corr_atual, 2, ',', '.');?>
            </font>
        </td>
        <td>
            Tx.Fin.P.Corr.= <?=number_format($taxa_financeira_compras, 1, ',', '.');?> %
            <img src="../../../imagem/bloco_negro.gif" title='(Tx.Fin.Compras - 0,5% = Tx.Bancaria Aprox.)' style='cursor:help' width="6" height="6">
        </td>
        <td>
            CMM: <?=compras_new::consumo_medio_mensal($id_produto_insumo);?>
        </td>
        <td>
            EC: 
            <?
                if(isset($ec_pa)) {//Se existe PA para este PI então exibe o EC ...
                    echo number_format($ec_pa, 2, ',', '.');
                }else {
                    echo 'NÃO É PA';
                }
            ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
        <?
            /*Se existe PA p/ este PI não exibo os botões de Fechar e Voltar porém realizo um 
            cálculo da ML do Pedido / ORC caso comprássemos pelo P.Médio Corr.Atual ...*/
            if(isset($ec_pa)) {
                $custo_ml_zero_preco_venda_orc                  = $_GET['txt_preco_l_fat'] / (1 + $_GET['txt_margem_lucro'] / 100);
                $preco_compra_medio_corr_ml_est_mais_acessorio  = $preco_compras_medio_corr_atual + $_GET['acrescimo_acessorio'];
                $custo_ml_zero_compras                          = $custo_ml_zero_preco_venda_orc / ($_GET['txt_preco_compra_lista_rs'] + $_GET['acrescimo_acessorio']) * $preco_compra_medio_corr_ml_est_mais_acessorio;
                $ml_est_compras                                 = ($_GET['txt_preco_l_fat'] / $custo_ml_zero_compras - 1) * 100;
                echo '<font color="yellow" size="2">ML Estimada p/ Preço Compra Med.Corr.Atual = '.number_format($ml_est_compras, 1, ',', '.').' %</font>';
        ?>
            <Script Language = 'JavaScript'>
                /*Aqui eu mantenho os parâmetros do próprio Iframe e adiciono mais estes 2 p/ montar a fórmula de 
                Margem de Lucro Estimada para o que temos em Estoque + para o que temos a Receber desse PIPA ...*/
                parent.document.getElementById('pendencias_item').src = parent.document.getElementById('pendencias_item').src + '&indice=<?=$_GET['indice'];?>&qtde_compras_total=<?=$qtde_compras_total;?>&preco_compras_medio_corr_atual=<?=$preco_compras_medio_corr_atual;?>&id_fornecedor_prod_insumo=<?=$_GET['id_fornecedor_prod_insumo'];?>'
            </Script>
        <?
            }else {//Só exibo esses botões se realmente for PI mesmo ...
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="voltar()" class="botao">
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick='parent.window.close()' style="color:red" class="botao">
        <?
            }
        ?>    
        </td>
    </tr>
</table>
<center>
<?
    //Se foi acessada de Compras, então posso exibir os dados com paginação normalmente ...
    if(empty($_GET['id_orcamento_venda_item'])) echo paginacao::print_paginacao('sim');
?>
</center>
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b> Só exibe NF(s) que foram liberada(s) em Estoque.
</pre>
<?
    }
}
?>