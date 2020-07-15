<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/consultar.php', '../../../../');

/****************************************************************************************/
/*******************Atualizando a NF com o N.º de Lote de PIN - Suframa******************/
/****************************************************************************************/
//Atualizo o campo da NF com o N.º de Lote de PIN - Suframa que foi gerado na Tela anterior ...
$sql = "UPDATE `nfs` SET `numero_lote_pin_suframa` = '$_GET[numero_lote]' WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
bancos::sql($sql);
/****************************************************************************************/

//Busca dos Dados da NF p/ poder gerar o XML ...
$sql = "SELECT c.insc_estadual as insc_estadual_cliente, c.id_uf, c.id_pais, 
        c.`cnpj_cpf` , REPLACE(REPLACE(c.cod_suframa, '.', ''), '-', '') AS cod_suframa, t.cnpj AS cnpj_transportadora, 
        t.ie AS insc_est_transp, 
        t.placa_identificacao, t.uf as uf_transportadora, e.cnpj as cnpj_empresa, 
        concat(cfop, num_cfop) as cfop, 
        nfs.id_empresa, nfs.id_transportadora, nfs.id_nf_num_nota, date_format(nfs.data_emissao, '%d/%m/%Y') as data_emissao, date_format(nfs.data_saida_entrada, '%d/%m/%Y') as data_saida_entrada, 
        nfs.total_icms, nfs.suframa, 
        ufs.sigla 
        FROM `nfs` 
        INNER JOIN `cfops` ON cfops.id_cfop = nfs.id_cfop 
        INNER JOIN `transportadoras` t ON t.id_transportadora = nfs.id_transportadora 
        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
        INNER JOIN `ufs` ON ufs.id_uf = c.id_uf 
        INNER JOIN `empresas` e ON e.id_empresa = nfs.id_empresa 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos = bancos::sql($sql);
//Aqui verifica o Tipo de Nota
if($campos[0]['id_empresa'] == 1 || $campos[0]['id_empresa'] == 2) {
    $nota_sgd = 'N';//var surti efeito lá embaixo
}else {
    $nota_sgd = 'S'; //var surti efeito lá embaixo
}
$id_uf          = $campos[0]['id_uf'];
$id_pais        = $campos[0]['id_pais'];
$cnpj_cpf       = $campos[0]['cnpj_cpf'];
$cnpj_transportadora = $campos[0]['cnpj_transportadora'];
$placa_veiculo  = $campos[0]['placa_identificacao'];
$uf_transportadora = $campos[0]['uf_transportadora'];
$insc_est_transp = $campos[0]['insc_est_transp'];
$suframa        = $campos[0]['suframa'];
$cod_suframa    = $campos[0]['cod_suframa'];
$uf_destino     = $campos[0]['sigla'];
$id_transportadora = $campos[0]['id_transportadora'];
$numero_nf      = faturamentos::buscar_numero_nf($campos[0]['id_nf_num_nota']);
$data_emissao   = $campos[0]['data_emissao'];
$cnpj_empresa   = $campos[0]['cnpj_empresa'];
$cfop           = $campos[0]['cfop'];
$insc_estadual_cliente = $campos[0]['insc_estadual_cliente'];
$data_saida_entrada = $campos[0]['data_saida_entrada'];
$total_icms     = number_format($campos[0]['total_icms'], 2, '.', '');
/**************************************************************************************/
/*****************Função que retorna todos os Valores referentes a NF******************/
/**************************************************************************************/
//Essa variável é utilizada lá em baixo ...
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');
/*******************************************************************************************/
//Caso exista Sufram ...
if($suframa <> 0) {
//Aqui eu busco o valor Integral do Suframa ...
    $desconto_suframa = round($calculo_total_impostos['desconto_suframa'], 2);

//Variáveis que irão me auxiliar no cálculo abaixo ...
    $pis    = genericas::variavel(20);
    $cofins = genericas::variavel(21);

/*Sempre que existir suframa, então terá que printar esse texto na Tela de Itens, pois nele 
existe um valor que também acarretará no Valor Total da NF ...*/
    if($suframa == 1) {//Área de Livre Comércio ...
        $ddAdicionais = 'Desconto de ICMS = 7 % ';
        $valor_abat_icms = number_format((7 * $calculo_total_impostos['valor_total_produtos']) / 100, 2, '.', '');
    }else if($suframa == 2) {//Zona Franca de Manaus ...
        $ddAdicionais = 'Desconto de PIS + Cofins = '.number_format((genericas::variavel(20)+genericas::variavel(21)), 2, ',', '.').' %  e ICMS = 7 % ';
//Cálculo de Suframa p/ cada Item separado  ...
        $valor_pis = round(($pis * $calculo_total_impostos['valor_total_produtos']) / 100, 2) * -1;
        $valor_cofins = round(($cofins * $calculo_total_impostos['valor_total_produtos']) / 100, 2) * -1;
        $valor_abat_icms = round((7 * $calculo_total_impostos['valor_total_produtos']) / 100, 2) * -1;
/*Aqui eu verifico se cada Item do Cálculo Isolado do Suframa, confere com o cálculo Total 
de Suframa que foi dado em NF*/
        if(($valor_pis + $valor_cofins + $valor_abat_icms) != $desconto_suframa) {
/*Caso exista alguma diferença no somatório dos Itens com o Total de Suframa, então 
eu desconto a diferença encontrada no Valor de Pis p/ que possa bater com o Total de Suframa*/
            $valor_pis-= round(($valor_pis + $valor_cofins + $valor_abat_icms) - $desconto_suframa, 2);
        }
    }
}
/**************************************************************************************/
/*************Função que retorna tudo referentes a dados de estoque na NF**************/
/**************************************************************************************/
$peso_nf = faturamentos::calculo_peso_nf($_GET['id_nf']);

//Busca de Todos os Itens da NF ...
$sql = "SELECT nfsi.id_nfs_item, nfsi.id_nf_item_devolvida, nfsi.id_classific_fiscal, nfsi.qtde, nfsi.vale as vale_ped, nfsi.ipi as ipi_perc_item_current, nfsi.valor_unitario, nfsi.valor_unitario_exp, nfsi.icms, nfsi.reducao, pa.referencia, pa.discriminacao, u.sigla 
        FROM `nfs_itens` nfsi 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
        INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
        where nfsi.`id_nf` = '$_GET[id_nf]' ORDER BY pvi.id_pedido_venda, pa.discriminacao ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);
/*Esse vetor tem a idéia de armazenas todas as Classificações Fiscais existentes em 
todos os Itens das Notas Fiscais ...*/ 
$vetor_classific_fiscal = array();
//Verificando as Classificações Fiscais referente a cada Item da NF ...
for($i = 0; $i < $linhas_itens; $i++) {
//Classificação Fiscal ...
    if(count($vetor_classific_fiscal) == 0) {//O vetor ainda está vazio ...
//Insere no vetor o Elemento corrente ...
        array_push($vetor_classific_fiscal, $campos_itens[$i]['id_classific_fiscal']);
    }else {//Já existe pelo menos 1 elemento no vetor ...
//Aqui eu sempre zero a variável p/ não herdar valores do loop anterior ...
        $achou = 0;
        for($j = 0; $j < count($vetor_classific_fiscal); $j++) {
//Comparo todas as Classificações Fiscais do Vetor, com a Classificação Corrente ...
            if($vetor_classific_fiscal[$j] == $campos_itens[$i]['id_classific_fiscal']) {//Significa que já existe ...
                $achou = 1;
            }
        }
/*Significa que depois de ter vasculhado todo o Vetor, não achou a Classificação corrente, 
sendo assim essa Classificação é incrementada no Vetor ...*/
        if($achou == 0) array_push($vetor_classific_fiscal, $campos_itens[$i]['id_classific_fiscal']);
    }
}
//Aqui eu verifico alguma das Classificações Fiscais é igual a 1 ou 2 ...
for($i = 0; $i < count($vetor_classific_fiscal); $i++) {
    if($vetor_classific_fiscal[$i] == 1 || $vetor_classific_fiscal[$i] == 2) {
        $mensagem_classific_fiscal = 1;
        $i = count($vetor_classific_fiscal);//Aqui é para sair fora do loop, assim q encontrar ...
    }
}
if($mensagem_classific_fiscal == 1) {
    $sql = "SELECT icms.reducao, cf.reducao_governo 
            FROM `icms` 
            INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = icms.id_classific_fiscal 
            WHERE icms.`id_uf` = '$id_uf' 
            AND icms.`id_classific_fiscal` = '1' 
            AND icms.`ativo` = '1' LIMIT 1 ";
    $campos_texto = bancos::sql($sql);
    $texto_classific_fiscal = ' - '.str_replace('?', number_format($campos_texto[0]['reducao'], 2, ',', '.'), $campos_texto[0]['reducao_governo']);
}

/******************************************************************************/
/***************************************XML************************************/
/******************************************************************************/
//Abaixo eu crio o conteúdo que ficará armazenado dentro do arquivo XML ...
$conteudo_xml = "<?xml version='1.0' encoding='UTF-8'?>
<lote nro='".$_GET['numero_lote']."' versao_sw='6.0' dtEmissao='".$data_emissao."' xmlns='http://www.portal.fucapi.br' 
xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.portal.fucapi.br 
http://alvaraes.suframa.gov.br:7778/PMNRecEViewController/jsp/importardados/NF.xsd'>
	<cnpjDestinatario>$cnpj_cpf</cnpjDestinatario>
	<cnpjTransp>$cnpj_transportadora</cnpjTransp>
	<inscSufDestinatario>$cod_suframa</inscSufDestinatario>
	<ufDestino>$uf_destino</ufDestino>
	<ufOrigem>SP</ufOrigem>
	<qtdeNF>1</qtdeNF>
	<notasFiscais>
  		<notaFiscal nro='$numero_nf' dtEmissao='$data_emissao' txZero='false' incent='0'>
			<cnpjRemetente>$cnpj_empresa</cnpjRemetente>
			<CFOP>$cfop</CFOP>
			<modelo>1</modelo>
			<serie>2</serie>
			<inscEstDestinatario>$insc_estadual_cliente</inscEstDestinatario>
			<dtSaidaNF>$data_saida_entrada</dtSaidaNF>
			<hrSaidaNF>".date('H:i')."</hrSaidaNF>
			<optDebito>2</optDebito>
			<ddAdicionais>$ddAdicionais".$texto_classific_fiscal."</ddAdicionais>
			<valores>
				<baseCalcICMS>".number_format($calculo_total_impostos['base_calculo_icms'], 2, '.', '')."</baseCalcICMS>
				<valICMS>$total_icms</valICMS>
				<valFT>".number_format($calculo_total_impostos['valor_frete'], 2, '.', '')."</valFT>
				<valSeguro/>
				<valTotIPI>".number_format($calculo_total_impostos['valor_ipi'], 2, '.', '')."</valTotIPI>
				<valOutrasDesp>".number_format($calculo_total_impostos['outras_despesas_acessorias'], 2, '.', '')."</valOutrasDesp>
				<valTotItens>".number_format($calculo_total_impostos['valor_total_produtos'], 2, '.', '')."</valTotItens>
				<valTotNF>".number_format($calculo_total_impostos['valor_total_nota'], 2, '.', '')."</valTotNF>
				<valPIS>".abs($valor_pis)."</valPIS>
				<valCOFINS>".abs($valor_cofins)."</valCOFINS>
				<valAbatICMS>".abs($valor_abat_icms)."</valAbatICMS>
			</valores>
			<transportador>
				<cnpjTransp>$cnpj_transportadora</cnpjTransp>";
//Frete por Conta ...
//Se for Nosso Carro ou o "Cliente for Krahenbuhl o Indústria Nardini" então ...
				if($id_transportadora == 795 || $id_cliente == 1020 || $id_cliente == 2234) {//Frete por Conta ...
					$frete_por_conta = 0;//Remetente ...
				}else {
					$frete_por_conta = 1;//Destinatário ...
				}
//Continuação com o Arquivo XML ...
				$conteudo_xml.= 
				"<ftConta>$frete_por_conta</ftConta>
				<placaVeic></placaVeic>
				<ufPlacaVeic></ufPlacaVeic>
				<inscEstTransp>$insc_est_transp</inscEstTransp>
				<qtdeVol>".number_format($peso_nf['qtde_caixas'], 0, '.', '')."</qtdeVol>
				<especie>CAIXA</especie>
				<marca>1</marca>
				<numero></numero>
				<pesoBruto>".number_format($peso_nf['peso_bruto_total'], 4, '.', '')."</pesoBruto>
				<pesoLiq>".number_format($peso_nf['peso_liq_total_nf'], 4, '.', '')."</pesoLiq>
			</transportador>
			<gnre>
				<valGNRE/>
				<dtVencGNRE/>
				<perRefGNRE/>
			</gnre>
			<refaturamento>
				<NFRefat/>
				<dtEmissaoRefat/>
				<inscSufRefat/>
			</refaturamento>
			<substTributaria>
				<baseCalcICMSSubTrib/>
				<valICMSSub/>
				<inscEstSubTrib/>
			</substTributaria>
			<itens>";
				for($i = 0; $i < $linhas_itens; $i++) {
					$ipi_item_current_rs = round(($campos_itens[$i]['ipi'] / 100) * ($campos_itens[$i]['valor_unitario'] * $campos_itens[$i]['qtde']), 2);
//Situação Tributária ...
					$sql = "SELECT REPLACE(classific_fiscal, '.', '') AS classific_fiscal 
                                                FROM `classific_fiscais` 
                                                WHERE `id_classific_fiscal` = '".$campos_itens[$i]['id_classific_fiscal']."' LIMIT 1 ";
					$campos_classific_fiscal    = bancos::sql($sql);
					$classific_fiscal           = $campos_classific_fiscal[0]['classific_fiscal'];
//Continuação com o Arquivo XML ...
					$conteudo_xml.= 
				"<item>
					<codProd>".$campos_itens[$i]['referencia']."</codProd>
					<descItem>".str_replace('Ç', 'C', $campos_itens[$i]['discriminacao'])."</descItem>
					<codNCM>".$classific_fiscal."</codNCM>
					<unidMed>".$campos_itens[$i]['sigla']."</unidMed>
					<valUnit>".number_format($campos_itens[$i]['valor_unitario'], 2, '.', '')."</valUnit>
					<qtde>".number_format($campos_itens[$i]['qtde'], 0, '.', '')."</qtde>
					<valTot>".number_format($campos_itens[$i]['valor_unitario'] * $campos_itens[$i]['qtde'], 2, '.', '')."</valTot>
					<classFiscal/>
					<sitTribut/>
					<alICMS/>
					<alIPI/>
					<valIPI/>
				</item>";
				}
//Continuação com o Arquivo XML ...
					$conteudo_xml.= 
			"</itens>
		</notaFiscal>
	</notasFiscais>
</lote>";

$arquivo_xml = '../../../../xml/lote'.$_GET['numero_lote'].'.sin';//Arq. que irá guardar todo o conteúdo XML gerado à cima
$ponteiro = fopen($arquivo_xml, 'w+');//Abre o arquivo, se não existir, então cria ...
fwrite($ponteiro, $conteudo_xml);//Escreve no arquivo XML o conteúdo gerado ...
fclose($ponteiro);//Fecha o Arquivo ...

//Aqui mostra uma Caixa de Diálogo, para que o usuário possa fazer dowload do Arquivo ...
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$arquivo_xml';</SCRIPT></HTML>";//JavaScript redirection