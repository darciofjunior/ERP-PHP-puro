<?
require('../../../../lib/segurancas.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');

/****************************************************************************************/
/*******************Atualizando a NF com o N.º de Lote de PIN - Suframa******************/
/****************************************************************************************/
//Atualizo o campo da NF com o N.º de Lote de PIN - Suframa que foi gerado na Tela anterior ...
$sql = "UPDATE `nfs` SET `numero_lote_pin_suframa` = '$_GET[numero_lote]' WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
bancos::sql($sql);
/****************************************************************************************/

//Busca dos Dados da NF p/ poder gerar o XML ...
$sql = "SELECT c.`cnpj_cpf`, REPLACE(REPLACE(c.cod_suframa, '.', ''), '-', '') AS cod_suframa, 
        t.cnpj AS cnpj_transportadora, DATE_FORMAT(nfs.data_emissao, '%d/%m/%Y') AS data_emissao, 
        nfs.chave_acesso, ufs.sigla 
        FROM nfs 
        INNER JOIN `transportadoras` t ON t.id_transportadora = nfs.id_transportadora 
        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
        INNER JOIN `ufs` ON ufs.id_uf = c.id_uf 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$cnpj_cpf       = $campos[0]['cnpj_cpf'];
$cnpj_transportadora = $campos[0]['cnpj_transportadora'];
$cod_suframa 	= $campos[0]['cod_suframa'];
$uf_destino 	= $campos[0]['sigla'];
$data_emissao 	= $campos[0]['data_emissao'];
$chave_acesso 	= $campos[0]['chave_acesso'];

/******************************************************************************/
/***************************************XML************************************/
/******************************************************************************/
//Abaixo eu crio o conteúdo que ficará armazenado dentro do arquivo XML ...
$conteudo_xml = "<?xml version='1.0' encoding='UTF-8'?>
<lote nro='".$_GET['numero_lote']."' versao_sw='6.0' dtEmissao='".$data_emissao."' xmlns='http://www.portal.fucapi.br' 
xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.portal.fucapi.br 
http://alvaraes.suframa.gov.br:7778/PMNRecEViewController/jsp/importardados/NFe.xsd'>
	<cnpjDestinatario>$cnpj_cpf</cnpjDestinatario>
	<cnpjTransp>$cnpj_transportadora</cnpjTransp>
	<inscSufDestinatario>$cod_suframa</inscSufDestinatario>
	<ufDestino>$uf_destino</ufDestino>
	<ufOrigem>SP</ufOrigem>
	<qtdeNF>1</qtdeNF>
	<notasFiscais>
  		<notaFiscal chaveAcesso='".str_replace(' ', '', $chave_acesso)."' txZero='false'>
		</notaFiscal>
	</notasFiscais>
</lote>";

$arquivo_xml = '../../../../xml/lote'.$_GET['numero_lote'].'.sin';//Arq. que irá guardar todo o conteúdo XML gerado à cima
$ponteiro = fopen($arquivo_xml, 'w+');//Abre o arquivo, se não existir, então cria ...
fwrite($ponteiro, $conteudo_xml);//Escreve no arquivo XML o conteúdo gerado ...
fclose($ponteiro);//Fecha o Arquivo ...

//Aqui mostra uma Caixa de Diálogo, para que o usuário possa fazer dowload do Arquivo ...
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$arquivo_xml';</SCRIPT></HTML>";//JavaScript redirection