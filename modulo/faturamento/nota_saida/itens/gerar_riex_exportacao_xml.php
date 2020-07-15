<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');

//Busca dos Dados da NF p/ poder gerar o XML ...
$sql = "SELECT IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente, c.insc_estadual AS insc_estadual_cliente, c.id_uf, c.id_pais, 
        c.cidade, c.cep, CONCAT(cfop, num_cfop) AS cfop, 
        nfs.id_empresa, nfs.id_transportadora, DATE_FORMAT(nfs.data_emissao, '%d/%m/%Y') AS data_emissao, DATE_FORMAT(nfs.data_saida_entrada, '%d/%m/%Y') AS data_saida_entrada, 
        nfs.total_icms, nfs.suframa, p.pais 
        FROM `nfs` 
        INNER JOIN `cfops` ON cfops.id_cfop = nfs.id_cfop 
        INNER JOIN `transportadoras` t ON t.id_transportadora = nfs.id_transportadora 
        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
        INNER JOIN `paises` p ON p.id_pais = c.id_pais 
        INNER JOIN `empresas` e ON e.id_empresa = nfs.id_empresa 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos = bancos::sql($sql);
//Aqui verifica o Tipo de Nota
if($campos[0]['id_empresa'] == 1 || $campos[0]['id_empresa'] == 2) {
	$nota_sgd = 'N';//var surti efeito lá embaixo
}else {
	$nota_sgd = 'S'; //var surti efeito lá embaixo
}
$id_pais        = $campos[0]['id_pais'];
$suframa        = $campos[0]['suframa'];
$numero_nf      = faturamentos::buscar_numero_nf($_GET['id_nf'], 'S');
$data_emissao 	= $campos[0]['data_emissao'];
$cfop           = $campos[0]['cfop'];
$pais           = $campos[0]['pais'];
/**************************************************************************************/
/*****************Função que retorna todos os Valores referentes a NF******************/
/**************************************************************************************/
//Essa variável é utilizada lá em baixo ...
$calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');
/**************************************************************************************/
/*************Função que retorna tudo referentes a dados de estoque na NF**************/
/**************************************************************************************/
$peso_nf = faturamentos::calculo_peso_nf($_GET['id_nf']);

//Busca de Todos os Itens da NF ...
$sql = "SELECT nfsi.id_nfs_item, nfsi.id_classific_fiscal, nfsi.qtde, nfsi.valor_unitario, u.unidade 
        FROM `nfs_itens` nfsi 
        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = pvi.id_produto_acabado 
        INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
        WHERE nfsi.`id_nf` = '$_GET[id_nf]' ORDER BY pvi.id_pedido_venda, pa.discriminacao ";
$campos_itens = bancos::sql($sql);
$linhas_itens = count($campos_itens);

/******************************************************************************/
/***************************************XML************************************/
/******************************************************************************/
//Abaixo eu crio o conteúdo que ficará armazenado dentro do arquivo XML ...
$conteudo_xml = "<?xml version='1.0' encoding='ISO-8859-1'?>
<RegistroExportacao>
	<NfExportacao CFOP='$cfop' SequenciaTipoSiscomex='8' NF='$numero_nf' Serie='1' DataEmissao='$data_emissao' 
		ValorTotal='".number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '')."' PesoLiquido='".number_format($peso_nf['peso_liq_total_nf'], 4, '.', '')."' Pais='$pais'>
		<RE RE='000000000000' TipoSiscomex='RE' UFProdutor='SP'/>";
				
for($i = 0; $i < $linhas_itens; $i++) {
//Situação Tributária ...
	$sql = "SELECT REPLACE(classific_fiscal, '.', '') AS classific_fiscal 
                FROM `classific_fiscais` 
                WHERE `id_classific_fiscal` = '".$campos_itens[$i]['id_classific_fiscal']."' LIMIT 1 ";
	$campos_classific_fiscal    = bancos::sql($sql);
	$classific_fiscal           = $campos_classific_fiscal[0]['classific_fiscal'];
//Continuação com o Arquivo XML ...
	$conteudo_xml.=
	"<ItemNfExportacao NCM='$classific_fiscal' Unidade='".$campos_itens[$i]['unidade']."' Quantidade='".$campos_itens[$i]['qtde']."' ValorTotalItem='".number_format($campos_itens[$i]['valor_unitario'] * $campos_itens[$i]['qtde'], 2, ',', '')."'/>";
}
//Continuação com o Arquivo XML ...
$conteudo_xml.= 
	"</NfExportacao>
</RegistroExportacao>";

$arquivo_xml = '../../../../xml/riex/riex_exportacao'.$numero_nf.date('Y').'.xml';//Arq. que irá guardar todo o conteúdo XML gerado à cima
$ponteiro = fopen($arquivo_xml, 'w+');//Abre o arquivo, se não existir, então cria ...
fwrite($ponteiro, $conteudo_xml);//Escreve no arquivo XML o conteúdo gerado ...
fclose($ponteiro);//Fecha o Arquivo ...

//Aqui mostra uma Caixa de Diálogo, para que o usuário possa fazer dowload do Arquivo ...
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$arquivo_xml';</SCRIPT></HTML>";//JavaScript redirection